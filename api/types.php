<?php

  require_once('private/initialize.php');
  require_once('../private/rate_limiter.php');
  require_once('../private/app_logger.php');
  header('Content-Type: application/json');

  $logger = new AppLogger();
  $logger->logApiRequest('types', ['method' => $_SERVER['REQUEST_METHOD']]);

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
    http_response_code(401);
    echo json_encode($authentication_response);
    exit;
  }
  $response->authentication_response = $authentication_response;

  $method = $_SERVER['REQUEST_METHOD'];

  switch ($method) {

    case 'GET':
      // List all artifact types, optionally filtered by user_id
      $user_id = isset($authentication_response->user_id) ? (int) $authentication_response->user_id : null;

      if ($user_id) {
        $stmt = $database->prepare(
          "SELECT id, ObjectType AS type FROM types WHERE user_id = ? ORDER BY ObjectType ASC"
        );
        $stmt->bind_param("i", $user_id);
      } else {
        $stmt = $database->prepare(
          "SELECT id, ObjectType AS type FROM types ORDER BY ObjectType ASC"
        );
      }

      $stmt->execute();
      $result = $stmt->get_result();
      $types = [];
      while ($record = $result->fetch_assoc()) {
        $types[] = $record;
      }
      $stmt->close();

      $response->types = $types;
      echo json_encode($response);
      break;

    default:
      http_response_code(405);
      $response->message = 'Method not allowed. Supported methods: GET';
      echo json_encode($response);
      break;
  }

?>
