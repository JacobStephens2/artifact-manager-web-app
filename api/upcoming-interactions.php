<?php

  require_once('private/initialize.php');
  require_once('../private/rate_limiter.php');
  require_once('../private/app_logger.php');
  header('Content-Type: application/json');

  $logger = new AppLogger();
  $logger->logApiRequest('upcoming-interactions', ['method' => $_SERVER['REQUEST_METHOD']]);

  $response = new stdClass;

  $rate_limiter = new RateLimiter($database);
  if (!$rate_limiter->checkAndRecord('api', 60, 60)) {
    http_response_code(429);
    $response->message = 'Rate limit exceeded. Please try again later.';
    echo json_encode($response);
    exit;
  }

  $authentication_response = authenticate();
  if ($authentication_response->authenticated != true) {
    http_response_code(401);
    echo json_encode($authentication_response);
    exit;
  }

  $user_id = isset($authentication_response->user_id) ? (int) $authentication_response->user_id : null;
  if (!$user_id) {
    http_response_code(400);
    $response->message = 'This endpoint requires user-scoped authentication (JWT cookie).';
    echo json_encode($response);
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    $response->message = 'Method not allowed. Supported methods: GET';
    echo json_encode($response);
    exit;
  }

  date_default_timezone_set('America/New_York');

  $user_stmt = mysqli_prepare($database, "SELECT default_use_interval, native_notify_enabled, native_notify_hour, native_notify_lead_days, native_notify_past_due FROM users WHERE id = ?");
  mysqli_stmt_bind_param($user_stmt, "i", $user_id);
  mysqli_stmt_execute($user_stmt);
  $user_result = mysqli_stmt_get_result($user_stmt);
  $user_row = mysqli_fetch_assoc($user_result);
  mysqli_stmt_close($user_stmt);
  $default_interval = ($user_row !== null && $user_row['default_use_interval'] !== null)
    ? (float) $user_row['default_use_interval']
    : DEFAULT_USE_INTERVAL;
  $prefs = [
    'enabled' => (int) ($user_row['native_notify_enabled'] ?? 1) === 1,
    'hour' => (int) ($user_row['native_notify_hour'] ?? 9),
    'lead_days' => (int) ($user_row['native_notify_lead_days'] ?? 3),
    'past_due' => (int) ($user_row['native_notify_past_due'] ?? 1) === 1,
  ];

  $artifact_set = use_by('', $default_interval, '', 0, 'no', $user_id);

  $today = new DateTime(date('Y-m-d'));
  $horizon = (new DateTime(date('Y-m-d')))->modify('+60 days');

  $items = [];
  while ($artifact = mysqli_fetch_assoc($artifact_set)) {
    $this_interval = ($artifact['interaction_frequency_days'] !== null)
      ? (float) $artifact['interaction_frequency_days']
      : $default_interval;

    $most_recent_use_or_response = $artifact['MostRecentUseOrResponse'];
    $most_recent_dt = ($most_recent_use_or_response !== null)
      ? new DateTime(substr($most_recent_use_or_response, 0, 10))
      : new DateTime('1970-01-01');
    $acq_dt = new DateTime(substr($artifact['Acq'], 0, 10));
    $interval_hours = $this_interval * 24;

    if ($most_recent_use_or_response === null || $most_recent_dt < $acq_dt) {
      $use_by_dt = (clone $acq_dt)->add(DateInterval::createFromDateString("$interval_hours hour"));
    } else {
      $doubled = $interval_hours * 2;
      $use_by_dt = (clone $most_recent_dt)->add(DateInterval::createFromDateString("$doubled hour"));
    }

    if ($use_by_dt > $horizon) {
      continue;
    }

    if ($use_by_dt < $today) {
      $status = 'past_due';
    } elseif ($use_by_dt->format('Y-m-d') === $today->format('Y-m-d')) {
      $status = 'due_today';
    } else {
      $diff = $today->diff($use_by_dt)->days;
      $status = ($diff <= 7) ? 'due_soon' : 'upcoming';
    }

    $items[] = [
      'id' => (int) $artifact['id'],
      'title' => $artifact['Title'],
      'use_by_date' => $use_by_dt->format('Y-m-d'),
      'most_recent_interaction' => ($most_recent_use_or_response !== null)
        ? $most_recent_dt->format('Y-m-d')
        : null,
      'interval_days' => $this_interval,
      'status' => $status,
    ];
  }

  usort($items, function ($a, $b) {
    return strcmp($a['use_by_date'], $b['use_by_date']);
  });

  $response->authenticated = true;
  $response->today = $today->format('Y-m-d');
  $response->timezone = 'America/New_York';
  $response->horizon_days = 60;
  $response->default_interval_days = $default_interval;
  $response->notification_prefs = $prefs;
  $response->items = $items;

  echo json_encode($response);
