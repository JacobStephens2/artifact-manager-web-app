<?php
// Legacy query functions for the 'objects' table

  function use_objects_by_user($interval, $limit) {
    global $db;

    /* A formatted version of the following query:
    SELECT
        objects.ID,
        objects.user_id,
        objects.ObjectName,
        TYPES.ObjectType,
        CASE WHEN MAX(use_table.UseDate) > objects.Acq THEN DATE_ADD(
            MAX(use_table.UseDate),
            INTERVAL 180 DAY
        ) ELSE DATE_ADD(objects.Acq, INTERVAL 90 DAY)
    END UseBy,
    objects.KeptCol,
    objects.Acq,
    MAX(use_table.UseDate) AS MaxUse
    FROM
        objects
    LEFT JOIN use_table ON objects.ID = use_table.ObjectName
    LEFT JOIN TYPES ON objects.ObjectType = TYPES.ID
    GROUP BY
        objects.ObjectName,
        objects.Acq,
        objects.KeptCol,
        objects.ID,
        TYPES.ObjectType
    HAVING
        objects.KeptCol = 1 AND objects.user_id = '8'
    ORDER BY
        UseBy ASC
    LIMIT 1024
    */

    $interval = (int) $interval;
    $interval_double = $interval * 2;
    $row_limit = ($limit == 1) ? 1 : 1024;
    $user_id = $_SESSION['user_id'];

    $stmt = mysqli_prepare($db,
      "SELECT
        objects.ID,
        objects.user_id,
        objects.ObjectName,
        types.ObjectType,
        CASE
          WHEN Max(use_table.UseDate) > objects.Acq THEN DATE_ADD(Max(use_table.UseDate), INTERVAL ? DAY)
          ELSE DATE_ADD(objects.Acq, INTERVAL ? DAY)
        END UseBy,
        objects.KeptCol,
        objects.Acq,
        Max(use_table.UseDate) AS MaxUse
      FROM objects
      LEFT JOIN use_table ON objects.ID = use_table.ObjectName
      LEFT JOIN types ON objects.ObjectType = types.ID
      GROUP BY
        objects.ObjectName,
        objects.Acq,
        objects.KeptCol,
        objects.ID,
        types.ObjectType
      HAVING objects.KeptCol = 1
        AND objects.user_id = ?
      ORDER BY UseBy ASC
      LIMIT ?"
    );
    mysqli_stmt_bind_param($stmt, "iiii", $interval_double, $interval, $user_id, $row_limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result;
  }
  function find_object_names_by_user() {
    global $db;

    $sql = "SELECT ";
    $sql .= "ObjectName ";
    $sql .= "FROM objects ";
    $sql .= "WHERE user_id = '" . db_escape($db, $_SESSION['user_id']) . "'";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }
  function find_object_names() {
    global $db;

    $sql = "SELECT ";
    $sql .= "ObjectName ";
    $sql .= "FROM objects ";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }
  function list_objects_by_user() {
    global $db;

    $sql = "SELECT ";
    $sql .= "objects.ID, ";
    $sql .= "objects.ObjectName, ";
    $sql .= "objects.KeptCol, ";
    $sql .= "objects.Acq, ";
    $sql .= "types.ObjectType ";
    $sql .= "FROM objects ";
    $sql .= "LEFT JOIN types ON objects.ObjectType = types.ID ";
    $sql .= "WHERE user_id = '" . db_escape($db, $_SESSION['user_id']) . "' ";
    $sql .= "ORDER BY objects.ObjectName ASC";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }
  function find_objects_by_user() {
    global $db;

    $sql = "SELECT ";
    $sql .= "objects.ID, ";
    $sql .= "objects.ObjectName, ";
    $sql .= "objects.KeptCol, ";
    $sql .= "objects.Acq, ";
    $sql .= "types.ObjectType ";
    $sql .= "FROM objects ";
    $sql .= "LEFT JOIN types ON objects.ObjectType = types.ID ";
    $sql .= "WHERE user_id = '" . db_escape($db, $_SESSION['user_id']) . "' ";
    $sql .= "ORDER BY objects.Acq DESC";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }
  function find_all_objects() {
    global $db;

    $sql = "SELECT ";
    $sql .= "objects.ID, ";
    $sql .= "objects.ObjectName, ";
    $sql .= "objects.KeptCol, ";
    $sql .= "objects.Acq, ";
    $sql .= "types.ObjectType ";
    $sql .= "FROM objects ";
    $sql .= "LEFT JOIN types ON objects.ObjectType = types.ID ";
    $sql .= "ORDER BY objects.Acq DESC";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }
  function find_object_by_id($id) {
    global $db;

    $sql = "SELECT ";
    $sql .= "objects.ID, ";
    $sql .= "objects.ObjectName, ";
    $sql .= "objects.KeptCol, ";
    $sql .= "objects.Acq, ";
    $sql .= "types.ObjectType ";
    $sql .= "FROM objects ";
    $sql .= "LEFT JOIN types ON objects.ObjectType = types.ID ";
    $sql .= "WHERE objects.id='" . db_escape($db, $id) . "' ";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    $subject = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $subject; // returns an assoc. array
  }
  function update_object($object) {
    global $db;

    $errors = validate_object($object);
    if(!empty($errors)) {
      return $errors;
    }

    $sql = "UPDATE objects SET ";
    $sql .= "ObjectName=?, ";
    $sql .= "Acq=?, ";
    $sql .= "ObjectType=?, ";
    $sql .= "KeptCol=? ";
    $sql .= "WHERE ID=? ";
    $sql .= "LIMIT 1;";

    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "sssss",
      $object['ObjectName'],
      $object['Acq'],
      $object['ObjectType'],
      $object['KeptCol'],
      $object['ID']
    );
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    // For UPDATE statements, $result is true/false
    if($result) {
      return true;
    } else {
      // UPDATE failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }
  }
  function validate_object($object) {
    $errors = [];

    // Title
    if(is_blank($object['ObjectName'])) {
      $errors[] = "Title cannot be blank.";
    } elseif(!has_length($object['ObjectName'], ['min' => 2, 'max' => 255])) {
      $errors[] = "Title must be between 2 and 255 characters.";
    }

    // KeptCol
    // Make sure we are working with a string
    $visible_str = (string) $object['KeptCol'];
    if(!has_inclusion_of($visible_str, ["0","1"])) {
      $errors[] = "Visible must be true or false.";
    }

    return $errors;
  }
  function insert_object_by_user($object) {
    global $db;

    $errors = validate_object($object);
    if(!empty($errors)) {
      return $errors;
    }

    $sql = "INSERT INTO objects ";
    // below did have 'user_id' in list after ObjectType
    $sql .= "(ObjectName, Acq, ObjectType, user_id, KeptCol) ";
    $sql .= "VALUES (?, ?, ?, ?, ?)";
    $user_id = $_SESSION['user_id'];
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "sssss",
      $object['ObjectName'],
      $object['Acq'],
      $object['ObjectType'],
      $user_id,
      $object['KeptCol']
    );
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    // For INSERT statements, $result is true/false
    if($result) {
      return true;
    } else {
      // INSERT failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }
  }
  function delete_object($id) {
    global $db;

    $sql = "DELETE FROM objects WHERE id=? LIMIT 1";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "s", $id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // For DELETE statements, $result is true/false
    if($result) {
      return true;
    } else {
      // DELETE failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }
  }

?>
