<?php
  require_once('../../private/initialize.php');
  require_login();

  if (!is_post_request()) {
    redirect_to(url_for('/artifacts/useby.php'));
  }

  $artifact_id = $_POST['artifact_id'] ?? null;
  $value = isset($_POST['value']) ? (int) $_POST['value'] : 1;
  $return_to = $_POST['return_to'] ?? 'useby';
  $artifact_name = $_POST['artifact_name'] ?? 'Artifact';

  if ($artifact_id === null) {
    $_SESSION['message'] = 'No artifact specified.';
    redirect_to(url_for('/artifacts/useby.php'));
  }

  $result = set_artifact_to_get_rid_of($artifact_id, $value);

  if ($result) {
    if ($value === 1) {
      $_SESSION['message'] = h($artifact_name) . ' marked to get rid of.';
    } else {
      $_SESSION['message'] = h($artifact_name) . ' restored to collection.';
    }
  } else {
    $_SESSION['message'] = 'Failed to update artifact.';
  }

  if ($return_to === 'to-get-rid-of') {
    redirect_to(url_for('/artifacts/to-get-rid-of.php'));
  } else {
    redirect_to(url_for('/artifacts/useby.php'));
  }
?>
