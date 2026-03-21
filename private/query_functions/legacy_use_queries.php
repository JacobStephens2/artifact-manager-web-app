<?php
// Legacy query functions for the 'use_table' table

  function delete_use($id) {
    global $db;

    $sql = "DELETE FROM use_table WHERE id=? LIMIT 1";
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
  function find_all_uses() {
    global $db;

    $sql = "SELECT ";
    $sql .= "objects.ObjectName, ";
    $sql .= "use_table.UseDate, ";
    $sql .= "objects.user_id, ";
    $sql .= "use_table.ID ";
    $sql .= "FROM use_table ";
    $sql .= "LEFT JOIN objects ON objects.ID = use_table.ObjectName ";
    $sql .= "WHERE use_table.user_id = ? ";
    $sql .= "ORDER BY UseDate DESC";

    $stmt = mysqli_prepare($db, $sql);
    $user_id = $_SESSION['user_id'];
    mysqli_stmt_bind_param($stmt, "s", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    confirm_result_set($result);
    return $result;
  }
  function find_use_by_id($id) {
    global $db;

    $sql = "SELECT ";
    $sql .= "objects.ObjectName, ";
    $sql .= "use_table.ObjectName AS objectID, ";
    $sql .= "use_table.UseDate, ";
    $sql .= "use_table.ID, ";
    $sql .= "types.ObjectType ";
    $sql .= "FROM use_table ";
    $sql .= "LEFT JOIN objects ON objects.ID = use_table.ObjectName ";
    $sql .= "LEFT JOIN types ON objects.ObjectType = types.ID ";
    $sql .= "WHERE use_table.id=? ";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    confirm_result_set($result);
    $subject = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $subject; // returns an assoc. array
  }

?>
