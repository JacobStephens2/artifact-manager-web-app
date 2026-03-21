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
    $artifacts = Artifact::list_games_by_query(
      $requestBody->query, 
      $requestBody->userid
    );
  } else {
    $artifacts = Artifact::list_games();
  }
  $response->artifacts = $artifacts;

  echo json_encode($response);

?>