<?php

function singleValueQuery($query) {
  global $db;
  $result = mysqli_query($db, $query);
  if ($result !== false) {
    $resultArray = mysqli_fetch_array($result);
    if ($resultArray !== null) {
      return $resultArray[0];
    } else {
      return 'No results';
    }
  } else {
    return 'Possible query error';
  }
}

function singleRowQuery($query) {
  global $db;
  $result = mysqli_query($db, $query);
  $resultArray = mysqli_fetch_array($result);
  return $resultArray;
}

function query($query) {
  global $db;
  return mysqli_query($db, $query);
}

function get_type_name($type_id) {
  global $db;
  $stmt = mysqli_prepare($db, "SELECT objectType FROM types WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "i", $type_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_array($result);
  mysqli_stmt_close($stmt);
  if ($row !== null) {
    return $row[0];
  }
  return 'No results';
}

?>
