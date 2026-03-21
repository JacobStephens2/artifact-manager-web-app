<?php

  function find_all_types() {
    global $db;

    $sql = "SELECT * FROM types";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }
  function find_type_by_id($id) {
    global $db;

    $sql = "SELECT * FROM types ";
    $sql .= "WHERE id='" . db_escape($db, $id) . "' ";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    $type = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $type; // returns an assoc. array
  }

?>
