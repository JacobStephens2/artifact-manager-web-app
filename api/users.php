<?php

  require_once('private/initialize.php');
  header('Content-Type: application/json');

  $response = new stdClass;

  $authentication_response = authenticate();
  if ($authentication_response->authenticated != true) {
    echo json_encode($authentication_response);
    exit;
  }
  $response->authentication_response = $authentication_response;

  $requestBody = json_decode(
    file_get_contents('php://input')
  );
    
  if (isset($requestBody->query) && $requestBody->query != '') {
    $result = User::list_users_by_query($requestBody->query, $requestBody->userid);
  } else {
    $result = User::list_users();
    
  }
  $response->users = $result;

  echo json_encode($response);

?>