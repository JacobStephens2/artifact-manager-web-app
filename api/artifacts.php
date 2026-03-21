<?php

  require_once('private/initialize.php');
  require_once('../private/rate_limiter.php');
  require_once('../private/app_logger.php');
  header('Content-Type: application/json');

  $logger = new AppLogger();
  $logger->logApiRequest('artifacts', ['method' => $_SERVER['REQUEST_METHOD']]);

  $response = new stdClass;

  // Rate limit: 60 requests per minute per IP
  $rate_limiter = new RateLimiter($database);
  if (!$rate_limiter->checkAndRecord('api', 60, 60)) {
    http_response_code(429);
    $response->message = 'Rate limit exceeded. Please try again later.';
    echo json_encode($response);
    exit;
  }

  $authentication_response = authenticate();
  if ($authentication_response->authenticated != true) {
    echo json_encode($authentication_response);
    exit;
  }
  $response->authentication_response = $authentication_response;

  $requestBody = json_decode(
    file_get_contents('php://input')
  );
    
  $page = isset($requestBody->page) ? (int) $requestBody->page : 1;
  $per_page = isset($requestBody->per_page) ? (int) $requestBody->per_page : 50;

  if (isset($requestBody->query) && $requestBody->query != '') {
    $artifacts = Artifact::list_artifacts_by_query(
      $requestBody->query,
      $requestBody->userid,
      $page,
      $per_page
    );
  } else {
    $artifacts = Artifact::list_artifacts($page, $per_page);
  }
  $response->artifacts = $artifacts;
  $response->page = $page;
  $response->per_page = $per_page;

  echo json_encode($response);

?>