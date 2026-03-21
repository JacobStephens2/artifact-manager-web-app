<?php

  function find_all_types() {
    global $db, $cache;

    return $cache->remember('find_all_types', 3600, function() use ($db) {
      $sql = "SELECT * FROM types";
      $result = mysqli_query($db, $sql);
      confirm_result_set($result);
      $types = [];
      while ($row = mysqli_fetch_assoc($result)) {
        $types[] = $row;
      }
      mysqli_free_result($result);
      return $types;
    });
  }

  function find_type_by_id($id) {
    global $db, $cache;

    $cache_key = 'find_type_by_id_' . $id;
    return $cache->remember($cache_key, 3600, function() use ($db, $id) {
      $sql = "SELECT * FROM types ";
      $sql .= "WHERE id='" . db_escape($db, $id) . "' ";
      $result = mysqli_query($db, $sql);
      confirm_result_set($result);
      $type = mysqli_fetch_assoc($result);
      mysqli_free_result($result);
      return $type; // returns an assoc. array
    });
  }

?>
