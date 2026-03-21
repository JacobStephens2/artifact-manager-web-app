<?php

  function list_players() {
    global $db;

    $sql = "SELECT ";
    $sql .= "id, ";
    $sql .= "FirstName, ";
    $sql .= "LastName ";
    $sql .= "FROM players ";
    $sql .= "WHERE user_id='" . db_escape($db, $_SESSION['user_id']) . "' ";
    $sql .= "ORDER BY FirstName ASC, ";
    $sql .= "LastName ASC";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }

  function find_player_by_id($id) {
    global $db;

    $sql = "SELECT *";
    $sql .= "FROM players ";
    $sql .= "WHERE players.id='" . db_escape($db, $id) . "' ";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    $subject = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $subject; // returns an assoc. array
  }

  function find_players_by_user_id() {
    global $db;
    $sql = "SELECT * FROM players ";
    $sql .= "WHERE user_id='" . db_escape($db, $_SESSION['user_id']) . "' ";
    $sql .= "ORDER BY id";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }

  function insert_player($player) {
    global $db;

    $sql = "INSERT INTO players (FirstName, LastName, FullName, G, Age, user_id) VALUES (?, ?, ?, ?, ?, ?)";
    $fullName = $player['FirstName'] . ' ' . $player['LastName'];
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "sssssi", $player['FirstName'], $player['LastName'], $fullName, $player['G'], $player['Age'], $_SESSION['user_id']);
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
  function update_player($player) {
    global $db;

    if ($player['thisPlayerIsMe'] === 'yes') {
      $sql = "UPDATE players SET FirstName=?, LastName=?, G=?, represents_user_id=?, Age=? WHERE id=? LIMIT 1";
      $stmt = mysqli_prepare($db, $sql);
      mysqli_stmt_bind_param($stmt, "ssssss", $player['FirstName'], $player['LastName'], $player['G'], $player['user_id'], $player['Age'], $player['id']);
    } else {
      $sql = "UPDATE players SET FirstName=?, LastName=?, G=?, represents_user_id = NULL, Age=? WHERE id=? LIMIT 1";
      $stmt = mysqli_prepare($db, $sql);
      mysqli_stmt_bind_param($stmt, "sssss", $player['FirstName'], $player['LastName'], $player['G'], $player['Age'], $player['id']);
    }
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($player['thisPlayerIsMe'] === 'yes') {
      // Update user record with player_id
      $updateUserQuery = "UPDATE users SET player_id = ? WHERE id = ? LIMIT 1";
      $stmt2 = mysqli_prepare($db, $updateUserQuery);
      mysqli_stmt_bind_param($stmt2, "ss", $player['id'], $player['user_id']);
    } else {
      $updateUserQuery = "UPDATE users SET player_id = NULL WHERE id = ? LIMIT 1";
      $stmt2 = mysqli_prepare($db, $updateUserQuery);
      mysqli_stmt_bind_param($stmt2, "s", $player['user_id']);
    }
    $updateUserResult = mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);

    // For UPDATE statements, $result is true/false
    if($result) {
      if (isset($updateUserResult)) {
        if ($updateUserResult) {
          return true;
        } else {
          echo mysqli_error($db);
          db_disconnect($db);
          exit;
        }
      } else {
        return true;
      }
    } else {
      // UPDATE failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }
  }
  function delete_player($id) {
    global $db;

    $sql = "DELETE FROM players WHERE id=? LIMIT 1";
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
