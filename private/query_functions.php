<?php
// Contents
  // Object Uses
  // Types
  // Objects
  // Users
  // Admins
  // Games
  // Responses
  // Players
  // Playgroup
  // Explore

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Object Uses
  function delete_use($id) {
    global $db;

    $sql = "DELETE FROM use_table ";
    $sql .= "WHERE id='" . db_escape($db, $id) . "' ";
    $sql .= "LIMIT 1";
    $result = mysqli_query($db, $sql);

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
    $sql .= "WHERE use_table.user_id = '" . db_escape($db, $_SESSION['user_id']) . "' ";
    $sql .= "ORDER BY UseDate DESC";

    $result = mysqli_query($db, $sql);
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
    $sql .= "WHERE use_table.id='" . db_escape($db, $id) . "' ";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    $subject = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $subject; // returns an assoc. array
  } 

// Types
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

// Objects
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
    $sql .= "ObjectName='" . db_escape($db, $object['ObjectName']) . "', ";
    $sql .= "Acq='" . db_escape($db, $object['Acq']) . "', ";
    $sql .= "ObjectType='" . db_escape($db, $object['ObjectType']) . "', ";
    $sql .= "KeptCol='" . db_escape($db, $object['KeptCol']) . "' ";
    $sql .= "WHERE ID='" . db_escape($db, $object['ID']) . "' ";
    $sql .= "LIMIT 1;";

    $result = mysqli_query($db, $sql);
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
    $sql .= "VALUES (";
    $sql .= "'" . db_escape($db, $object['ObjectName']) . "',";
    $sql .= "'" . db_escape($db, $object['Acq']) . "',";
    $sql .= "'" . db_escape($db, $object['ObjectType']) . "',";
    $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "',";
    $sql .= "'" . db_escape($db, $object['KeptCol']) . "'";
    $sql .= ")";
    $result = mysqli_query($db, $sql);
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

    $sql = "DELETE FROM objects ";
    $sql .= "WHERE id='" . db_escape($db, $id) . "' ";
    $sql .= "LIMIT 1";
    $result = mysqli_query($db, $sql);

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

// Users
  function find_user_by_username($username) {
    global $db;

    $sql = "SELECT * FROM users ";
    $sql .= "WHERE username='" . db_escape($db, $username) . "' ";
    $sql .= "LIMIT 1";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    $admin = mysqli_fetch_assoc($result); // find first
    mysqli_free_result($result);
    return $admin; // returns an assoc. array
  }
  function validate_user($user, $options=[]) {
    $errors = [];

    $password_required = $options['password_required'] ?? true;

    if(is_blank($user['first_name'])) {
      $errors[] = "First name cannot be blank.";
    } elseif (!has_length($user['first_name'], array('min' => 2, 'max' => 255))) {
      $errors[] = "First name must be between 2 and 255 characters.";
    }

    if(is_blank($user['last_name'])) {
      $errors[] = "Last name cannot be blank.";
    } elseif (!has_length($user['last_name'], array('min' => 2, 'max' => 255))) {
      $errors[] = "Last name must be between 2 and 255 characters.";
    }

    if(is_blank($user['email'])) {
      $errors[] = "Email cannot be blank.";
    } elseif (!has_length($user['email'], array('max' => 255))) {
      $errors[] = "Last name must be less than 255 characters.";
    } elseif (!has_valid_email_format($user['email'])) {
      $errors[] = "Email must be a valid format.";
    }

    if(is_blank($user['username'])) {
      $errors[] = "Username cannot be blank.";
    } elseif (!has_length($user['username'], array('min' => 8, 'max' => 255))) {
      $errors[] = "Username must be between 8 and 255 characters.";
    } elseif (!has_unique_username($user['username'], $user['id'] ?? 0)) {
      $errors[] = "Username not allowed. Try another.";
    }

    if($password_required) {
      if(is_blank($user['password'])) {
        $errors[] = "Password cannot be blank.";
      } elseif (!has_length($user['password'], array('min' => 12))) {
        $errors[] = "Password must contain 12 or more characters";
      } elseif (!preg_match('/[A-Z]/', $user['password'])) {
        $errors[] = "Password must contain at least 1 uppercase letter";
      } elseif (!preg_match('/[a-z]/', $user['password'])) {
        $errors[] = "Password must contain at least 1 lowercase letter";
      } elseif (!preg_match('/[0-9]/', $user['password'])) {
        $errors[] = "Password must contain at least 1 number";
      } elseif (!preg_match('/[^A-Za-z0-9\s]/', $user['password'])) {
        $errors[] = "Password must contain at least 1 symbol";
      }

      if(is_blank($user['confirm_password'])) {
        $errors[] = "Confirm password cannot be blank.";
      } elseif ($user['password'] !== $user['confirm_password']) {
        $errors[] = "Password and confirm password must match.";
      }
    }

    return $errors;
  }
  function reset_password($form_data) {
    global $db;

    $hashed_password = password_hash($form_data['password'], PASSWORD_BCRYPT);

    $sql = "UPDATE users ";
    $sql .= "SET hashed_password = '" . db_escape($db, $hashed_password) . "' ";
    $sql .= "WHERE 'email' = '" . db_escape($db, $form_data['email']) . "'";
    $result = mysqli_query($db, $sql);
    if($result) {
      return true;
    } else {
      // INSERT failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }
  }
  function insert_user($user) {
    global $db;

    $errors = validate_user($user);
    if (!empty($errors)) {
      return $errors;
    }

    $hashed_password = password_hash($user['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users ";
    $sql .= "(first_name, last_name, email, username, hashed_password, user_group) ";
    $sql .= "VALUES (";
    $sql .= "'" . db_escape($db, $user['first_name']) . "',";
    $sql .= "'" . db_escape($db, $user['last_name']) . "',";
    $sql .= "'" . db_escape($db, $user['email']) . "',";
    $sql .= "'" . db_escape($db, $user['username']) . "',";
    $sql .= "'" . db_escape($db, $hashed_password) . "',";
    $sql .= "'1'";
    $sql .= ")";
    $result = mysqli_query($db, $sql);

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

// Admins
  function find_all_admins() {
    // Find all admins, ordered last_name, first_name
    global $db;

    $sql = "SELECT * FROM admins ";
    $sql .= "ORDER BY last_name ASC, first_name ASC";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }
  function find_admin_by_id($id) {
    global $db;

    $sql = "SELECT * FROM admins ";
    $sql .= "WHERE id='" . db_escape($db, $id) . "' ";
    $sql .= "LIMIT 1";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    $admin = mysqli_fetch_assoc($result); // find first
    mysqli_free_result($result);
    return $admin; // returns an assoc. array
  }
  function validate_admin($admin, $options=[]) {

    $password_required = $options['password_required'] ?? true;

    if(is_blank($admin['first_name'])) {
      $errors[] = "First name cannot be blank.";
    } elseif (!has_length($admin['first_name'], array('min' => 2, 'max' => 255))) {
      $errors[] = "First name must be between 2 and 255 characters.";
    }

    if(is_blank($admin['last_name'])) {
      $errors[] = "Last name cannot be blank.";
    } elseif (!has_length($admin['last_name'], array('min' => 2, 'max' => 255))) {
      $errors[] = "Last name must be between 2 and 255 characters.";
    }

    if(is_blank($admin['email'])) {
      $errors[] = "Email cannot be blank.";
    } elseif (!has_length($admin['email'], array('max' => 255))) {
      $errors[] = "Last name must be less than 255 characters.";
    } elseif (!has_valid_email_format($admin['email'])) {
      $errors[] = "Email must be a valid format.";
    }

    if(is_blank($admin['username'])) {
      $errors[] = "Username cannot be blank.";
    } elseif (!has_length($admin['username'], array('min' => 8, 'max' => 255))) {
      $errors[] = "Username must be between 8 and 255 characters.";
    } elseif (!has_unique_username($admin['username'], $admin['id'] ?? 0)) {
      $errors[] = "Username not allowed. Try another.";
    }

    if($password_required) {
      if(is_blank($admin['password'])) {
        $errors[] = "Password cannot be blank.";
      } elseif (!has_length($admin['password'], array('min' => 12))) {
        $errors[] = "Password must contain 12 or more characters";
      } elseif (!preg_match('/[A-Z]/', $admin['password'])) {
        $errors[] = "Password must contain at least 1 uppercase letter";
      } elseif (!preg_match('/[a-z]/', $admin['password'])) {
        $errors[] = "Password must contain at least 1 lowercase letter";
      } elseif (!preg_match('/[0-9]/', $admin['password'])) {
        $errors[] = "Password must contain at least 1 number";
      } elseif (!preg_match('/[^A-Za-z0-9\s]/', $admin['password'])) {
        $errors[] = "Password must contain at least 1 symbol";
      }

      if(is_blank($admin['confirm_password'])) {
        $errors[] = "Confirm password cannot be blank.";
      } elseif ($admin['password'] !== $admin['confirm_password']) {
        $errors[] = "Password and confirm password must match.";
      }
    }

    return $errors;
  }
  function insert_admin($admin) {
    global $db;

    $errors = validate_admin($admin);
    if (!empty($errors)) {
      return $errors;
    }

    $hashed_password = password_hash($admin['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO admins ";
    $sql .= "(first_name, last_name, email, username, hashed_password) ";
    $sql .= "VALUES (";
    $sql .= "'" . db_escape($db, $admin['first_name']) . "',";
    $sql .= "'" . db_escape($db, $admin['last_name']) . "',";
    $sql .= "'" . db_escape($db, $admin['email']) . "',";
    $sql .= "'" . db_escape($db, $admin['username']) . "',";
    $sql .= "'" . db_escape($db, $hashed_password) . "'";
    $sql .= ")";
    $result = mysqli_query($db, $sql);

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
  function update_admin($admin) {
    global $db;

    $password_sent = !is_blank($admin['password']);

    $errors = validate_admin($admin, ['password_required' => $password_sent]);
    if (!empty($errors)) {
      return $errors;
    }

    $hashed_password = password_hash($admin['password'], PASSWORD_BCRYPT);

    $sql = "UPDATE admins SET ";
    $sql .= "first_name='" . db_escape($db, $admin['first_name']) . "', ";
    $sql .= "last_name='" . db_escape($db, $admin['last_name']) . "', ";
    $sql .= "email='" . db_escape($db, $admin['email']) . "', ";
    if($password_sent) {
      $sql .= "hashed_password='" . db_escape($db, $hashed_password) . "', ";
    }
    $sql .= "username='" . db_escape($db, $admin['username']) . "' ";
    $sql .= "WHERE id='" . db_escape($db, $admin['id']) . "' ";
    $sql .= "LIMIT 1";
    $result = mysqli_query($db, $sql);

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
  function delete_admin($admin) {
    global $db;

    $sql = "DELETE FROM admins ";
    $sql .= "WHERE id='" . db_escape($db, $admin['id']) . "' ";
    $sql .= "LIMIT 1;";
    $result = mysqli_query($db, $sql);

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

// Games
  function find_artifacts_by_user() {
    global $db;

    $sql = "SELECT ";
    $sql .= "games.id, ";
    $sql .= "games.Title, ";
    $sql .= "games.KeptCol, ";
    $sql .= "games.Acq, ";
    $sql .= "FROM games ";
    $sql .= "WHERE user_id = '" . db_escape($db, $_SESSION['user_id']) . "' ";
    $sql .= "ORDER BY objects.Acq DESC";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }
  function find_all_board_games() {
    global $db;

    $sql = "SELECT * FROM games ";
    $sql .= "WHERE type = 'board-game' ";
    $sql .= "ORDER BY KeptCol DESC, Acq DESC";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }

  function find_artifacts_by_user_id($kept, $type, $interval, $sweetSpot = '') {
    global $db;

    $sql = "SELECT
        games.Title,
        games.mnp,
        games.mxp,
        games.mnt,
        games.mxt,
        games.Candidate,
        games.UsedRecUserCt,
        games.ss,
        games.id,
        games.InSecondaryCollection,
        types.objectType AS type,
        games.user_id,
        games.type_id,
        DATE(MAX(responses.PlayDate)) AS MaxPlay,
        DATE(MAX(uses.use_date)) AS MaxUse,
        CASE 
          WHEN 
            MAX(responses.PlayDate) < games.Acq 
            THEN DATE_ADD(games.Acq, INTERVAL " . $interval . " DAY) 
          WHEN 
            MAX(responses.PlayDate) IS NULL 
            THEN DATE_ADD(games.Acq, INTERVAL " . $interval . " DAY) 
          ELSE 
            DATE_ADD(MAX(responses.PlayDate), INTERVAL " . $interval * 2 . " DAY)
          END UseBy,
        games.Acq,
        games.KeptCol
    FROM
        games
    LEFT JOIN responses ON games.id = responses.Title
    LEFT JOIN uses ON games.id = uses.artifact_id
    LEFT JOIN types ON games.type_id = types.id
    GROUP BY
        games.Acq,
        games.Title,
        games.KeptCol,
        games.mnp,
        games.mxp,
        games.ss,
        games.type,
        games.id
    HAVING
        games.user_id = " . db_escape($db, $_SESSION['user_id']) . " ";

        if (strlen($sweetSpot) > 0) {
          $sql .= " AND games.ss LIKE '%$sweetSpot%' ";
          $sql .= " AND games.ss NOT LIKE '%1$sweetSpot%' ";
          $sql .= " AND games.ss NOT LIKE '%2$sweetSpot%' ";
          $sql .= " AND games.ss NOT LIKE '%3$sweetSpot%' ";
          $sql .= " AND games.ss NOT LIKE '%$sweetSpot" . "0%' ";
          $sql .= " AND games.ss NOT LIKE '%$sweetSpot" . "1%' ";
          $sql .= " AND games.ss NOT LIKE '%$sweetSpot" . "2%' ";
          $sql .= " AND games.ss NOT LIKE '%$sweetSpot" . "3%' ";
          $sql .= " AND games.ss NOT LIKE '%$sweetSpot" . "4%' ";
        }
        
        if (isset($type) && $type != [] && $type != '1') {
          $sql .= " AND games.type_id IN ( ";
          $i = 0;
          foreach($type as $type_name => $type_id) {
            $i++;
            $sql .= "'$type_id'";
            if ($i != count($type)) {
              $sql .= ", ";
            }
          }
          $sql .= ") ";
        }

        if ( $kept == 'yes') { 
          $sql .= " AND games.KeptCol = 1 "; 
        } elseif ( $kept == 'no' ) {
          $sql .= " AND games.KeptCol = 0 "; 
        } elseif ( $kept == 'secondary_only' ) {
          $sql .= " AND games.InSecondaryCollection = 'yes' "; 
        }

    $sql .= "
        ORDER BY
        UseBy DESC,
        MaxPlay DESC,
        Acq DESC,
        games.KeptCol DESC,
        id ASC
    ";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }

  function find_sweet_spots_by_artifact_id($artifact_id) {
    global $db;

    $stmt = mysqli_prepare($db, "SELECT
      sweetspots.id AS id,
      games.Title AS Title,
      sweetspots.SwS AS SwS
      FROM sweetspots
      JOIN games ON games.id = sweetspots.Title
      WHERE sweetspots.Title = ?
      ORDER BY games.Title ASC
    ");
    mysqli_stmt_bind_param($stmt, "i", $artifact_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result;
  }

  function find_one_to_many_uses_by_artifact_id($artifact_id) {
    global $db;

    $stmt = mysqli_prepare($db, "SELECT
      id,
      use_date,
      note
      FROM uses
      WHERE artifact_id = ?
      ORDER BY use_date DESC,
      id DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $artifact_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result;
  }

  function find_one_to_one_uses_by_artifact_id($artifact_id) {
    global $db;

    $stmt = mysqli_prepare($db, "SELECT
      responses.PlayDate,
      responses.id,
      players.FirstName,
      players.LastName
      FROM responses
      JOIN players ON responses.Player = players.id
      WHERE responses.Title = ?
      ORDER BY responses.PlayDate DESC,
      responses.id DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $artifact_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result;
  }

  function find_artifact_by_id($id) {
    global $db;

    $stmt = mysqli_prepare($db,
      "SELECT types.objectType AS type_name, games.*
      FROM games
      JOIN types ON games.type_id = types.id
      WHERE games.id = ?"
    );
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $subject = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $subject; // returns an assoc. array
  }

  function update_artifact($artifact) {
    global $db;

    $errors = validate_artifact($artifact);
    if(!empty($errors)) {
      return $errors;
    }

    $type_id = (int) $artifact['type'];
    $type_name = get_type_name($type_id);

    $stmt = mysqli_prepare($db,
      "UPDATE games SET
        Title=?, KeptCol=?, Acq=?, Candidate=?, UsedRecUserCt=?,
        type_id=?, type=?, SS=?, Notes=?, CandidateGroupDate=?,
        MnT=?, MxT=?, Age=?, InSecondaryCollection=?, MnP=?, MxP=?,
        interaction_frequency_days=?
      WHERE id=?
      LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, "sssssissssssssssi",
      $artifact['Title'], $artifact['KeptCol'], $artifact['Acq'],
      $artifact['Candidate'], $artifact['UsedRecUserCt'],
      $type_id, $type_name, $artifact['SS'], $artifact['Notes'],
      $artifact['CandidateGroupDate'], $artifact['MnT'], $artifact['MxT'],
      $artifact['age'], $artifact['InSecondaryCollection'],
      $artifact['MnP'], $artifact['MxP'], $artifact['interaction_frequency_days'],
      $artifact['id']
    );
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if($result) {
      return true;
    } else {
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }

  }
  
  function validate_artifact($artifact) {
    $errors = [];

    // Title
    if(is_blank($artifact['Title'])) {
      $errors[] = "Title cannot be blank.";
    } elseif(!has_length($artifact['Title'], ['min' => 2, 'max' => 255])) {
      $errors[] = "Title must be between 2 and 255 characters.";
    }

    // KeptCol
    $visible_str = (string) $artifact['KeptCol'];
    if(!has_inclusion_of($visible_str, ["0","1"])) {
      $errors[] = "Kept must be true or false.";
    }

    // Numeric fields must be valid integers
    $numeric_fields = ['MnT' => 'Minimum Time', 'MxT' => 'Maximum Time',
                       'MnP' => 'Minimum User Count', 'MxP' => 'Maximum User Count'];
    foreach($numeric_fields as $field => $label) {
      if(isset($artifact[$field]) && $artifact[$field] !== '' && !is_numeric($artifact[$field])) {
        $errors[] = "{$label} must be a number.";
      }
    }

    // MnT <= MxT and MnP <= MxP range checks
    if(isset($artifact['MnT']) && isset($artifact['MxT'])
       && is_numeric($artifact['MnT']) && is_numeric($artifact['MxT'])
       && (int)$artifact['MnT'] > (int)$artifact['MxT']) {
      $errors[] = "Minimum Time cannot exceed Maximum Time.";
    }
    if(isset($artifact['MnP']) && isset($artifact['MxP'])
       && is_numeric($artifact['MnP']) && is_numeric($artifact['MxP'])
       && (int)$artifact['MnP'] > (int)$artifact['MxP']) {
      $errors[] = "Minimum User Count cannot exceed Maximum User Count.";
    }

    // Age must be non-negative
    if(isset($artifact['age']) && $artifact['age'] !== '' && $artifact['age'] !== 0) {
      if(!is_numeric($artifact['age']) || (int)$artifact['age'] < 0) {
        $errors[] = "Minimum Age must be a non-negative number.";
      }
    }

    // Acquisition date format
    if(isset($artifact['Acq']) && !empty($artifact['Acq'])) {
      $date = DateTime::createFromFormat('Y-m-d', $artifact['Acq']);
      if(!$date || $date->format('Y-m-d') !== $artifact['Acq']) {
        $errors[] = "Tracking Start Date must be a valid date (YYYY-MM-DD).";
      }
    }

    // Interaction frequency must be positive
    if(isset($artifact['interaction_frequency_days']) && $artifact['interaction_frequency_days'] !== '') {
      if(!is_numeric($artifact['interaction_frequency_days']) || (float)$artifact['interaction_frequency_days'] <= 0) {
        $errors[] = "Interaction Frequency must be a positive number.";
      }
    }

    // Type must be a valid integer
    if(isset($artifact['type']) && $artifact['type'] !== '') {
      if(!is_numeric($artifact['type']) || (int)$artifact['type'] < 0) {
        $errors[] = "Type must be a valid selection.";
      }
    }

    return $errors;
  }

  function insert_artifact($object) {
    global $db;

    $errors = validate_artifact($object);
    if(!empty($errors)) {
      return $errors;
    }

    $type_id = db_escape($db, $object['type']);
    $type_name = get_type_name($type_id);

    $sql = "INSERT INTO games (
        Title, 
        Notes,
        Acq, 
        type_id, 
        type,
        KeptCol, 
        Candidate, 
        CandidateGroupDate, 
        UsedRecUserCt, 
        SS,
        MnT,
        MxT,
        MnP,
        MxP,
        user_id,
        interaction_frequency_days
      ) VALUES (
        '" . db_escape($db, $object['Title']) . "',
        '" . db_escape($db, $object['Notes']) . "',
        '" . db_escape($db, $object['Acq']) . "',
        '$type_id',
        '$type_name',
        '" . db_escape($db, $object['KeptCol']) . "',
        '" . db_escape($db, $object['Candidate']) . "',
        '" . db_escape($db, $object['CandidateGroupDate']) . "',
        '" . db_escape($db, $object['UsedRecUserCt']) . "',
        '" . db_escape($db, $object['SS']) . "',
        '" . db_escape($db, $object['MnT']) . "',
        '" . db_escape($db, $object['MxT']) . "',
        '" . db_escape($db, $object['MnP']) . "',
        '" . db_escape($db, $object['MxP']) . "',
        '" . db_escape($db, $_SESSION['user_id']) . "',
        '" . db_escape($db, $object['interaction_frequency_days']) . "'
      )
    ";
    $result = mysqli_query($db, $sql);
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

  function delete_artifact($id) {
    global $db;

    $sql = "DELETE FROM games ";
    $sql .= "WHERE id='" . db_escape($db, $id) . "' ";
    $sql .= "LIMIT 1";
    $result = mysqli_query($db, $sql);

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

  function list_artifacts() {
    global $db;
    $sql = "SELECT ";
    $sql .= "games.id, ";
    $sql .= "games.Title ";
    $sql .= "FROM games ";
    $sql .= "ORDER BY games.Title ASC";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }

  function list_artifacts_by_query($query) {
    global $db;
    $sql = 
      "SELECT 
        games.id, 
        games.Title 
      FROM games 
      WHERE games.Title LIKE '%" . db_escape($db, $query) . "%'
      ORDER BY games.Title ASC
    ";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }

  function list_users_by_query($query) {
    global $db;
    $sql = 
      "SELECT 
        players.id, 
        players.FirstName,
        players.LastName 
      FROM players 
      WHERE players.FirstName LIKE '%" . db_escape($db, $query) . "%'
      ORDER BY players.FirstName ASC,
      LastName ASC
    ";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }

  function use_by($type, $interval, $sweetSpot, $minimumAge, $shelfSort, $user = null) {

    if ($user === null && isset($_SESSION['user_id'])) {
      $user = $_SESSION['user_id'];
    }

    global $db;

    $sql =
      "SELECT 
        games.Title,
        games.mnp,
        games.mxp,
        games.mnt,
        games.mxt,
        games.Candidate,
        games.UsedRecUserCt,
        games.ss,
        games.id,
        types.objectType AS type,
        games.user_id,
        games.age,
        games.type_id,
        games.InSecondaryCollection,
        MAX(uses.use_date) AS MostRecentUse,
        MAX(responses.PlayDate) AS MaxPlay,
        CASE
          WHEN MAX(uses.use_date) IS NULL THEN MAX(responses.PlayDate)
          WHEN MAX(uses.use_date) < MAX(responses.PlayDate) THEN MAX(responses.PlayDate)
          ELSE MAX(uses.use_date)
        END MostRecentUseOrResponse,
        games.Acq,
        games.KeptCol,
        games.interaction_frequency_days
      FROM games
        LEFT JOIN responses ON games.id = responses.Title
        LEFT JOIN uses ON games.id = uses.artifact_id
        LEFT JOIN types ON games.type_id = types.id
      GROUP BY games.Acq,
        games.Title,
        games.KeptCol, games.mnp, games.mxp, games.ss, games.type, games.id 
      HAVING 
        games.user_id = " . db_escape($db, $user) . "
      ";

      if ($shelfSort == 'yes') {
        $sql .= " AND (games.KeptCol = 1 OR games.InSecondaryCollection = 'yes') ";
      } else {
        $sql .= " AND games.KeptCol = 1 ";
      }

      if ($sweetSpot !== '') {
        $sql .= "AND 
          (
            games.ss LIKE '$sweetSpot'
            OR games.ss LIKE '$sweetSpot %' 
            OR games.ss LIKE '%0$sweetSpot%' 
            OR games.ss LIKE '%,$sweetSpot' 
            OR games.ss LIKE '%,$sweetSpot,%' 
            OR games.ss LIKE '%, $sweetSpot' 
            OR games.ss LIKE '%, $sweetSpot,%' 
          )
        ";
      }

      if ($minimumAge !== '' && $minimumAge !== 0 && $minimumAge !== '0') {
        $sql .= " AND games.age >= '$minimumAge' ";
      }

      if (gettype($type) === 'array') {
        if (count($type) > 0) {
          $sql .= "AND games.type_id IN (";
          $i = 1;
          foreach($type as $typeIndividual) {
            $sql .= "'" . $typeIndividual . "'";
            if (count($type) != $i) {
              $sql .= ",";
            }
            $i++;
          }
          $sql .= ") ";
        } else {
          $sql .= " AND type = '' ";
        }
      } elseif ($type === '') {
        // add no type clause
      } else {
        $sql .= "AND type = '" . $type . "' ";
      }

      $sql .= "
        ORDER BY MostRecentUseOrResponse ASC
      ";
      $result = mysqli_query($db, $sql);
      confirm_result_set($result);
      return $result;
  }

  function first_play_by() {
    global $db;

      $sql ="SELECT 
      games.Title,
      games.mnp,
      games.mxp,
      games.ss,
      games.type,
      CASE
          WHEN MAX(responses.PlayDate) < games.Acq THEN DATE_ADD(games.Acq, INTERVAL 180 DAY)
          WHEN MAX(responses.PlayDate) IS NULL THEN DATE_ADD(games.Acq, INTERVAL 180 DAY)
          ELSE DATE_ADD(MAX(responses.PlayDate), INTERVAL 360 DAY)
      END PlayBy,
      games.Acq,
      MAX(responses.PlayDate) AS MaxPlay,
      games.KeptCol
    FROM games
      LEFT JOIN responses ON games.id = responses.Title
    GROUP BY games.Acq,
      games.Title,
      games.KeptCol, games.mnp, games.mxp, games.ss, games.type

    HAVING (games.KeptCol) = 1
    and games.type = 'board-game'
    ORDER BY MostRecentUse DESC, MaxPlay DESC
    LIMIT 1
    ";

    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;

    /* Sample query
      SELECT 
          games.Title,
          games.mnp,
          games.mxp,
          games.Candidate,
          games.UsedRecUserCt,
          games.ss,
          games.id,
          games.type,
          games.user_id,
          CASE
              WHEN MAX(responses.PlayDate) < games.Acq THEN DATE_ADD(games.Acq, INTERVAL 180 DAY)
              WHEN MAX(responses.PlayDate) IS NULL THEN DATE_ADD(games.Acq, INTERVAL 180 DAY)
              ELSE DATE_ADD(MAX(responses.PlayDate),
                  INTERVAL 360 DAY)
          END PlayBy,
          MAX(responses.PlayDate) AS MaxPlay,
          games.Acq,
          games.KeptCol
      FROM
          games
              LEFT JOIN
          responses ON games.id = responses.Title
      GROUP BY games.Acq , games.Title , games.KeptCol , games.mnp , games.mxp , games.ss , games.type , games.id
      HAVING games.user_id = 8 AND games.KeptCol = 1
          AND games.ss LIKE '%3%'
          AND games.type IN ('game' , 'board-game',
          'card-game',
          'childrens-game',
          'gambling-game',
          'miniatures-game',
          'mobile-game',
          'role-playing-game',
          'sport',
          'vr-game',
          'book',
          'audiobook',
          'drink',
          'food',
          'equipment',
          'film',
          'instrument',
          'toy',
          'other')
      ORDER BY PlayBy ASC
    */
  }


// Responses
  function validate_response($use) {
    $errors = [];

    return $errors;
  }

  function insert_response_revised($postArray) {
    global $db;

    $queriesArray = [];

    $i = 0;
    foreach($postArray['user'] as $userArray) {
      $query = "INSERT INTO responses (
        Title, 
        PlayDate, 
        Player, 
        user_id,
        Note
        ) VALUES (
        '" . db_escape($db, $postArray['artifact']['id']) . "', 
        '" . db_escape($db, $postArray['useDate']) . "', 
        '" . db_escape($db, $userArray['id']) . "', 
        '" . db_escape($db, $_SESSION['user_id']) . "',
        '" . db_escape($db, $postArray['Note']) . "'
        )
      ";
      $result = mysqli_query($db, $query);
      $i++;
    }
    
    // For INSERT statements, $result is true/false
    if ($result) {
      return $result;
    } else {
      // INSERT failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }  
  }

  function insert_response_one_to_many($postArray) {

    /* Sample post request body

      $_POST: Array 
      (
        [useDate] => 2023-01-12
        [artifact] => Array
          (
              [name] => Age of Empires IV
              [id] => 2807
          )

        [user] => Array
          (
            [0] => Array
                (
                    [name] => Jacob Stephens
                    [id] => 141
                )

            [1] => Array
                (
                    [name] => Luke Boerman
                    [id] => 91
                )

          )
      )
    */

    global $db;

    $queriesArray = [];

    // table uses
    $query = "INSERT INTO uses (
      artifact_id, 
      use_date, 
      user_id,
      notesTwo,
      note
      ) VALUES (
      '" . db_escape($db, $postArray['artifact']['id']) . "', 
      '" . db_escape($db, $postArray['useDate']) . "', 
      '" . db_escape($db, $_SESSION['user_id']) . "',
      '" . db_escape($db, $postArray['NotesTwo']) . "',
      '" . db_escape($db, $postArray['Note']) . "'
      )
    ";
    $result = mysqli_query($db, $query);
    $use_id = mysqli_insert_id($db);

    $i = 0;
    foreach($postArray['user'] as $userArray) {
      
      // table uses_players
      $query = "INSERT INTO uses_players (
        use_id, 
        player_id, 
        user_id
        ) VALUES (
        '" . db_escape($db, $use_id) . "', 
        '" . db_escape($db, $userArray['id']) . "', 
        '" . db_escape($db, $_SESSION['user_id']) . "'
        )
      ";
      $result = mysqli_query($db, $query);
      $i++;
    }
    
    // For INSERT statements, $result is true/false
    if ($result) {
      return $result;
    } else {
      // INSERT failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }  
  }

  function insert_response($response, $playerCount) {
    global $db;

    $errors = validate_response($response);
    if(!empty($errors)) {
      return $errors;
    }
    
    if ($playerCount >= 1) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, Note, PlayDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['Note']) . "', ";
      $sql .= "'" . db_escape($db, $response['PlayDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player1']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 2) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, PlayDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['PlayDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player2']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 3) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, PlayDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['PlayDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player3']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 4) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, PlayDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['PlayDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player4']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 5) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, PlayDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['PlayDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player5']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 6) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, PlayDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['PlayDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player6']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 7) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, PlayDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['PlayDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player7']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 8) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, PlayDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['PlayDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player8']) . "', ";
       $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
     $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 9) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, PlayDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['PlayDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player9']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
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
  
  function insert_aversion($response, $playerCount) {
    global $db;

    $errors = validate_response($response);
    if(!empty($errors)) {
      return $errors;
    }
    
    if ($playerCount >= 1) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, AversionDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['AversionDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player1']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 2) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, AversionDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['AversionDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player2']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 3) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, AversionDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['AversionDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player3']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 4) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, AversionDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['AversionDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player4']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 5) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, AversionDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['AversionDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player5']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 6) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, AversionDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['AversionDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player6']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 7) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, AversionDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['AversionDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player7']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 8) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, AversionDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['AversionDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player8']) . "', ";
       $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
     $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
    if ($playerCount >= 9) {
      $sql = "INSERT INTO responses ";
      $sql .= "(Title, AversionDate, Player, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Title']) . "', ";
      $sql .= "'" . db_escape($db, $response['AversionDate']) . "', ";
      $sql .= "'" . db_escape($db, $response['Player9']) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
    }
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

  function find_all_responses() {
    global $db;

    $sql = "SELECT ";
    $sql .= "games.Title, ";
    $sql .= "responses.id, ";
    $sql .= "players.FirstName, ";
    $sql .= "players.LastName, ";
    $sql .= "responses.PlayDate ";
    $sql .= "FROM responses ";
    $sql .= "LEFT JOIN games ON responses.Title = games.id ";
    $sql .= "LEFT JOIN players ON responses.Player = players.id ";
    $sql .= "ORDER BY responses.PlayDate DESC, ";
    $sql .= "games.Title DESC, ";
    $sql .= "players.LastName ASC, ";
    $sql .= "players.FirstName ASC ";
    $sql .= "LIMIT 100";

    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }

  function find_uses_by_user_id($type, $minimumDate) {
    global $db;

    $sql = "SELECT
      games.Title,
      types.objectType AS type,
      games.Candidate,
      games.ss AS SwS,
      games.id AS gameID, 
      uses.id AS useID, 
      uses.note,
      uses.use_date,
      games.type_id
      FROM uses 
      LEFT JOIN games ON uses.artifact_id = games.id 
      LEFT JOIN types ON games.type_id = types.id
      WHERE uses.user_id = " . db_escape($db, $_SESSION['user_id']) . " 
      AND uses.use_date IS NOT NULL 
    ";

    if (gettype($type == 'array')) {
      if (count($type) > 0) {
        $sql .= "AND games.type_id IN (";
        $i = 1;
        foreach($type as $typeIndividual) {
          $sql .= "'" . $typeIndividual . "'";
          if (count($type) != $i) {
            $sql .= ",";
          }
          $i++;
        }
        $sql .= ") ";
      } else {
        $sql .= " AND games.type_id = '' ";
      }
    }

    if ($minimumDate != '') {
      $sql .= " AND uses.use_date >= '$minimumDate' ";
    }

    $sql .= " ORDER BY uses.use_date DESC, 
      uses.id DESC, 
      games.Title DESC
      LIMIT 9999
    ";

    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }

  function find_responses_by_user_id() {
    global $db;

    $sql = "SELECT ";
    $sql .= "games.Title, ";
    $sql .= "games.type, ";
    $sql .= "games.id AS gameID, ";
    $sql .= "responses.id AS responseID, ";
    $sql .= "players.FirstName, ";
    $sql .= "players.LastName, ";
    $sql .= "responses.PlayDate ";
    $sql .= "FROM responses ";
    $sql .= "LEFT JOIN games ON responses.Title = games.id ";
    $sql .= "LEFT JOIN players ON responses.Player = players.id ";
    $sql .= "WHERE responses.user_id = " . db_escape($db, $_SESSION['user_id']) . " ";
    $sql .= "AND responses.PlayDate IS NOT NULL ";
    $sql .= "ORDER BY responses.PlayDate DESC, ";
    $sql .= "responses.id DESC, ";
    $sql .= "games.Title DESC, ";
    $sql .= "players.LastName ASC, ";
    $sql .= "players.FirstName ASC ";
    $sql .= "LIMIT 9999";

    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
  }

  function find_aversions_by_user_id() {
  global $db;

  $sql = "SELECT ";
  $sql .= "games.Title, ";
  $sql .= "responses.id, ";
  $sql .= "players.FirstName, ";
  $sql .= "players.LastName, ";
  $sql .= "responses.AversionDate ";
  $sql .= "FROM responses ";
  $sql .= "LEFT JOIN games ON responses.Title = games.id ";
  $sql .= "LEFT JOIN players ON responses.Player = players.id ";
  $sql .= "WHERE responses.user_id = " . db_escape($db, $_SESSION['user_id']) . " ";
  $sql .= "AND responses.AversionDate > 0 ";
  $sql .= "ORDER BY responses.AversionDate DESC, ";
  $sql .= "games.Title DESC, ";
  $sql .= "players.LastName ASC, ";
  $sql .= "players.FirstName ASC ";
  $sql .= "LIMIT 9999";

  $result = mysqli_query($db, $sql);
  confirm_result_set($result);
  return $result;
}

function find_users_by_use_id($use_id) {
  global $db;
  $query = "SELECT
    players.FirstName,
    players.LastName,
    players.id
    FROM uses_players
    LEFT JOIN players ON uses_players.player_id = players.id
    WHERE uses_players.user_id='" . db_escape($db, $_SESSION['user_id']) . "'
    AND uses_players.use_id = '" . db_escape($db, $use_id) . "' 
  ";
  $result = mysqli_query($db, $query);
  confirm_result_set($result);
  return $result; // returns an assoc. array
  mysqli_free_result($result);
}

function find_use_details_by_id($id) {
  global $db;

  $sql = "SELECT 
    games.id AS game_id, 
    games.Title AS artifact,
    uses.use_date, 
    uses.note AS note, 
    uses.notesTwo AS notesTwo, 
    uses.id 
    FROM uses 
    LEFT JOIN games ON uses.artifact_id = games.id 
    WHERE uses.id='" . db_escape($db, $id) . "' 
  ";

  $result = mysqli_query($db, $sql);
  confirm_result_set($result);
  $subject = mysqli_fetch_assoc($result);
  mysqli_free_result($result);
  return $subject; // returns an assoc. array
}

function find_use_users_by_id($id) {
  global $db;

  $sql = "SELECT 
    games.Title, 
    games.id AS gameid, 
    responses.PlayDate, 
    responses.Player, 
    responses.Note AS Note, 
    players.FirstName, 
    players.LastName, 
    responses.Title AS responsetitle, 
    responses.AversionDate, 
    responses.id 
    FROM responses 
    LEFT JOIN players ON responses.Player = players.id 
    LEFT JOIN games ON responses.Title = games.id 
    WHERE responses.id='" . db_escape($db, $id) . "' 
  ";
  $result = mysqli_query($db, $sql);
  confirm_result_set($result);
  $subject = mysqli_fetch_assoc($result);
  mysqli_free_result($result);
  return $subject; // returns an assoc. array
}

function find_response_by_id($id) {
  global $db;

  $sql = "SELECT ";
  $sql .= "games.Title, ";
  $sql .= "games.id AS gameid, ";
  $sql .= "responses.PlayDate, ";
  $sql .= "responses.Player, ";
  $sql .= "responses.Note AS Note, ";
  $sql .= "players.FirstName, ";
  $sql .= "players.LastName, ";
  $sql .= "responses.Title AS responsetitle, ";
  $sql .= "responses.AversionDate, ";
  $sql .= "responses.id ";
  $sql .= "FROM responses ";
  $sql .= "LEFT JOIN players ON responses.Player = players.id ";
  $sql .= "LEFT JOIN games ON responses.Title = games.id ";
  $sql .= "WHERE responses.id='" . db_escape($db, $id) . "' ";
  $result = mysqli_query($db, $sql);
  confirm_result_set($result);
  $subject = mysqli_fetch_assoc($result);
  mysqli_free_result($result);
  return $subject; // returns an assoc. array
}

function update_response($object) {
  global $db;

  $errors = validate_response($object);
  if(!empty($errors)) {
    return $errors;
  }

  $sql = "UPDATE responses SET ";
  $sql .= "Title='" . db_escape($db, $object['Title']) . "', ";
  $sql .= "PlayDate='" . db_escape($db, $object['PlayDate']) . "', ";
  $sql .= "Note='" . db_escape($db, $object['Note']) . "', ";
  $sql .= "Player='" . db_escape($db, $object['Player']) . "' ";
  $sql .= "WHERE id='" . db_escape($db, $object['id']) . "' ";
  $sql .= "LIMIT 1;";

  $result = mysqli_query($db, $sql);
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

function update_use($useArray) {
  global $db;

  $errors = validate_response($useArray);
  if(!empty($errors)) {
    return $errors;
  }

  $sql = "UPDATE uses SET
    artifact_id='" . db_escape($db, $useArray['artifact_id']) . "', 
    use_date='" . db_escape($db, $useArray['use_date']) . "', 
    notesTwo='" . db_escape($db, $useArray['notesTwo']) . "', 
    note='" . db_escape($db, $useArray['note']) . "'
    WHERE id='" . db_escape($db, $useArray['use_id']) . "' 
    AND user_id='" . db_escape($db, $_SESSION['user_id']) . "' 
    LIMIT 1
  ";

  $result = mysqli_query($db, $sql);
  // For UPDATE statements, $result is true/false
  
  if($result) {
    // do nothing
  } else {
    // UPDATE failed
    echo mysqli_error($db);
    db_disconnect($db);
    exit;
  }

  $query = "DELETE FROM uses_players
    WHERE use_id = '" . $useArray['use_id'] . "'
    AND user_id='" . db_escape($db, $_SESSION['user_id']) . "' 
  ";
  $result = mysqli_query($db, $query);
  if($result) {
    // do nothing
  } else {
    // UPDATE failed
    echo mysqli_error($db);
    db_disconnect($db);
    exit;
  }

  foreach ($useArray['user'] as $user) {
    if ($user['name'] == '') {
      continue;
    }
    $query = "INSERT INTO uses_players (
        use_id, 
        player_id, 
        user_id
      ) VALUES (
        '" . $useArray['use_id'] . "',
        '" . $user['id'] . "',
        '" . db_escape($db, $_SESSION['user_id']) . "' 
      )
    ";
    $result = mysqli_query($db, $query);
    if($result) {
      // do nothing
    } else {
      // UPDATE failed
      echo mysqli_error($db);
      db_disconnect($db);
      exit;
    }
  }
  
  return true;
}

function delete_response($id) {
  global $db;

  $sql = "DELETE FROM responses ";
  $sql .= "WHERE id='" . db_escape($db, $id) . "' ";
  $sql .= "AND user_id='" . db_escape($db, $_SESSION['user_id']) . "' ";
  $sql .= "LIMIT 1";
  $result = mysqli_query($db, $sql);

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

function delete_one_to_many_use($use_id) {
  global $db;

  $sql = "DELETE FROM uses ";
  $sql .= "WHERE id='" . db_escape($db, $use_id) . "' ";
  $sql .= "AND user_id='" . db_escape($db, $_SESSION['user_id']) . "' ";
  $sql .= "LIMIT 1";
  $result = mysqli_query($db, $sql);

  // For DELETE statements, $result is true/false
  if($result) {
    // do nothing
  } else {
    // DELETE failed
    echo mysqli_error($db);
    db_disconnect($db);
    exit;
  }

  $sql = "DELETE FROM uses_players ";
  $sql .= "WHERE use_id='" . db_escape($db, $use_id) . "' ";
  $sql .= "AND user_id='" . db_escape($db, $_SESSION['user_id']) . "' ";
  $result = mysqli_query($db, $sql);

  if($result) {
    return true;
  } else {
    // DELETE failed
    echo mysqli_error($db);
    db_disconnect($db);
    exit;
  }
}

// Players
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

    $sql = "INSERT INTO players ";
    $sql .= "(FirstName, LastName, FullName, G, Age, user_id) ";
    $sql .= "VALUES (";
    $sql .= "'" . db_escape($db, $player['FirstName']) . "',";
    $sql .= "'" . db_escape($db, $player['LastName']) . "',";
    $sql .= "'" . db_escape($db, $player['FirstName']) . " " . db_escape($db, $player['LastName']) . "',";
    $sql .= "'" . db_escape($db, $player['G']) . "',";
    $sql .= "'" . db_escape($db, $player['Age']) . "',";
    $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
    $sql .= ")";
    $result = mysqli_query($db, $sql);
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

    $sql = "UPDATE players SET ";
    $sql .= "FirstName='" . db_escape($db, $player['FirstName']) . "', ";
    $sql .= "LastName='" . db_escape($db, $player['LastName']) . "', ";
    $sql .= "G='" . db_escape($db, $player['G']) . "', ";
    if ($player['thisPlayerIsMe'] === 'yes') {
      $sql .= "represents_user_id='" . db_escape($db, $player['user_id']) . "', ";
    } else {
      $sql .= "represents_user_id = NULL, ";
    }
    $sql .= "Age='" . db_escape($db, $player['Age']) . "' ";
    $sql .= "WHERE id='" . db_escape($db, $player['id']) . "' ";
    $sql .= "LIMIT 1";
    $result = mysqli_query($db, $sql);

    $updateUserQuery = "UPDATE users ";
    
    if ($player['thisPlayerIsMe'] === 'yes') {
      // Update user record with player_id
      $updateUserQuery .= " SET player_id = '" . db_escape($db, $player['id']) . "'";
    } else {
      $updateUserQuery .= " SET player_id = NULL";
    }

    $updateUserQuery .= " WHERE id = '" . db_escape($db, $player['user_id']) . "'
      LIMIT 1
    ";
    $updateUserResult = mysqli_query($db, $updateUserQuery);

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

    $sql = "DELETE FROM players ";
    $sql .= "WHERE id='" . db_escape($db, $id) . "' ";
    $sql .= "LIMIT 1";
    $result = mysqli_query($db, $sql);

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

// Playgroup

function count_playgroup() {
  global $db;
  $sql = "SELECT count(*) AS count FROM playgroup";
  $result = mysqli_query($db, $sql);
  confirm_result_set($result);
  $subject = mysqli_fetch_assoc($result);
  mysqli_free_result($result);
  return $subject; // returns an assoc. array
}

function choose_games_for_group($range, $typeArray, $kept = 0) {
  global $db;
  $playgroup_count = count_playgroup();

  $sql ="SELECT
  games.title,
  games.id,
  games.ss,
  games.MnP,
  games.MxP,
  games.MxT,
  games.Age,
  games.type,
  games.KeptCol,
  playgroup.FullName,
  players.id AS PlayerID,
  players.FirstName,
  players.LastName,
  players.G,
  players.Priority,
  responses.id AS ResponseID,
  Max(responses.AversionDate) AS MaxOfAversionDate,
  Max(responses.PlayDate) AS MaxOfPlayDate,
  Max(responses.PassDate) AS MaxOfPassDate,
  Max(responses.RequestDate) AS MaxOfRequestDate
  FROM (
        players
        LEFT JOIN (
            games
            LEFT JOIN responses ON games.ID = responses.Title
        ) ON players.ID = responses.Player
    )
    INNER JOIN playgroup ON players.ID = playgroup.FullName
  GROUP BY 
    games.Title,
    games.MnP,
    games.Age,
    games.MxP,
    games.id,
    games.type,
    games.user_id,
    players.FirstName,
    players.LastName,
    playgroup.FullName,
    games.FavCt,
    players.G,
    players.Priority 
  HAVING ";
  $sql .= " games.user_id = " . db_escape($db, $_SESSION['user_id']) . " ";
  $sql .= "AND ( MaxOfPlayDate IS NOT NULL OR MaxOfAversionDate IS NOT NULL ) ";
  if ($range == 'true') {
    $sql .= "AND games.MnP <= " . $playgroup_count['count'] . " ";
    $sql .= "AND games.MxP >= " . $playgroup_count['count'] . " ";
  }
  if (isset($typeArray) && $typeArray != 1 && count($typeArray) > 0) {
    $sql .= "AND games.type IN (";
    $i = 1;
    foreach($typeArray as $type) {
      $sql .= "'" . $type . "'";
      if (count($typeArray) != $i) {
        $sql .= ",";
      }
      $i++;
    } 
    $sql .= ") ";
  }


  if ($kept == 1) {
    $sql .= " AND keptcol = 1 ";
  }
  $sql .= "ORDER BY 
    players.G,
    players.Priority DESC,
    Max(responses.AversionDate) ASC,
    Max(responses.PlayDate) DESC,
    Max(responses.PassDate) ASC,
    Max(responses.RequestDate) DESC
  "; 

  $result = mysqli_query($db, $sql);
  confirm_result_set($result);
  return $result;
}

function update_playgroup_player($playgroupplayer) {
    global $db;

    $sql = "UPDATE playgroup SET ";
    $sql .= "FullName='" . db_escape($db, $playgroupplayer['FullName']) . "' ";
    $sql .= "WHERE ID='" . db_escape($db, $playgroupplayer['ID']) . "' ";
    $sql .= "LIMIT 1";
    $result = mysqli_query($db, $sql);
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

  function delete_playgroup_player($ID) {
    global $db;

    $sql = "DELETE FROM playgroup ";
    $sql .= "WHERE ID='" . db_escape($db, $ID) . "'";
    $result = mysqli_query($db, $sql);

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

  function find_playgroup_by_user_id() {
    global $db;

    $sql = "SELECT 
      playgroup.ID, 
      playgroup.FullName, 
      players.FirstName, 
      players.LastName, 
      players.id AS playerID ";
    $sql .= "FROM playgroup LEFT JOIN players ON playgroup.FullName = players.id ";
    $sql .= "WHERE playgroup.user_id = '" . db_escape($db, $_SESSION['user_id']) . "'";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result; // returns an assoc. array
  }

  function find_playgroup_player_by_id($ID) {
    global $db;

    $sql = "SELECT playgroup.ID, playgroup.FullName, players.FirstName, players.LastName FROM playgroup LEFT JOIN players ON playgroup.FullName = players.id ";
    $sql .= "WHERE playgroup.ID='" . db_escape($db, $ID) . "' ";
    $sql .= "LIMIT 1";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    $subject = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $subject; // returns an assoc. array
  }

  function insert_playgroup($response) {
    global $db;
    
    $playerCount = $response['playerCount'];
    $i = 1;
    while($playerCount >= $i) {
      $sql = "INSERT INTO playgroup ";
      $sql .= "(FullName, user_id) ";
      $sql .= "VALUES (";
      $sql .= "'" . db_escape($db, $response['Player' . $i]) . "', ";
      $sql .= "'" . db_escape($db, $_SESSION['user_id']) . "'";
      $sql .= ")";
      $result = mysqli_query($db, $sql);
      $i++;
    }
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
// Explore
function find_artifacts_by_characteristic($kept, $type, $allGames, $favCt) {
  global $db;

  $sql = "SELECT Title, id, mxt, mnt, ss, yr, wt, mnp, mxp, av, favct, age, bgg_rat, KeptCol, type ";
  $sql .= "FROM games ";
  $sql .= "WHERE ";

  if ($allGames == 'true') {
    $sql .= "user_id = " . db_escape($db, $_SESSION['user_id']) . " OR user_id = 8 ";
  } else {
    $sql .= "user_id = " . db_escape($db, $_SESSION['user_id']) . " ";
  } 
  
  $sql .= "AND ";
  
  if ($kept == 'true') {
    $sql .= "KeptCol = 1 ";
  } else {
    $sql .= '1 = 1 ';
  }

  $sql .= "AND ";

  if ($type != '1') {
    $sql .= "type = '" . $type . "' ";
  } else {
    $sql .= "1 = 1 ";
  }

  $sql .= "AND type IS NOT NULL ";
  $sql .= "AND type <> '' ";
  $sql .= "AND ss <> '' ";

  $sql .= "ORDER BY ";
  if ($favCt != '') {
    $sql .= "favct DESC, ";
    $sql .= "ss ASC, ";
    $sql .= "mxt ASC, ";
    $sql .= "mnt ASC, ";
    $sql .= "age ASC, ";
    $sql .= "bgg_rat DESC ";
  } else {
    $sql .= "ss ASC, ";
    $sql .= "mxt ASC, ";
    $sql .= "mnt ASC, ";
    $sql .= "age ASC, ";
    $sql .= "favct DESC, ";
    $sql .= "bgg_rat DESC ";
  }

  $result = mysqli_query($db, $sql);
  confirm_result_set($result);
  return $result;
}

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
  return singleValueQuery(
    "SELECT objectType
    FROM types
    WHERE id = '$type_id'"
  );
}

function email_artifact_use_notice($user_id) {

  $sweetSpot = '';
  $minimumAge = 0;
  $shelfSort = 'no';
  $type = '';

  $query = 
    "SELECT default_use_interval
    FROM users
    WHERE id = '$user_id'
  ";
  
  $interval = singleValueQuery($query);
  
  $artifact_set = use_by($type, $interval, $sweetSpot, $minimumAge, $shelfSort, $user_id);
  
  $due_today_array = array();
  $overdue_array = array();
  $due_in_coming_week = array();
  
  $i = 0;
  while($artifact = mysqli_fetch_assoc($artifact_set)) { 

      if ($artifact['interaction_frequency_days'] !== null) {
        $this_interval = $artifact['interaction_frequency_days'];
      } else {
        $this_interval = $interval;
      }
  
      date_default_timezone_set('America/New_York');
      $DateTimeNow = new DateTime(date('Y-m-d')); 
      $DateTimeMostRecentUse = new DateTime(substr($artifact['MostRecentUseOrResponse'],0,10)); 
      if ($artifact['MostRecentUseOrResponse'] === NULL) {
          $date_of_most_recent_use = 'No interactions';
      } else {
          $date_of_most_recent_use = $DateTimeMostRecentUse->format('Y-m-d');
      }
      $DateTimeAcquisition = new DateTime(substr($artifact['Acq'],0,10)); 
      $intervalInHours = $this_interval * 24;
  
      if ($DateTimeMostRecentUse < $DateTimeAcquisition || $artifact['MostRecentUseOrResponse'] === NULL) {
          $DateInterval = DateInterval::createFromDateString("$intervalInHours hour");
          $useByDate = date_add($DateTimeAcquisition, $DateInterval);
      } else {
          $doubledInterval = $intervalInHours * 2;
          $DateInterval = DateInterval::createFromDateString("$doubledInterval hour");
          $useByDate = date_add($DateTimeMostRecentUse, $DateInterval);
      }
  
      $diff_days = $useByDate->diff($DateTimeNow)->days;
  
      if ($useByDate->format('Y-m-d') === $DateTimeNow->format('Y-m-d')) { // due today
          $due_today_array[$i]['artifact'] = h($artifact['Title']);
          $due_today_array[$i]['artifact_id'] = h($artifact['id']);
          $due_today_array[$i]['most_recent_use'] = $date_of_most_recent_use;
          $due_today_array[$i]['interval'] = $this_interval;
      } elseif ($diff_days > 0 && $diff_days < 8 && $useByDate->format('Y-m-d') > $DateTimeNow->format('Y-m-d')) { // due in coming week
          $due_in_coming_week[$i]['artifact'] = h($artifact['Title']);
          $due_in_coming_week[$i]['artifact_id'] = h($artifact['id']);
          $due_in_coming_week[$i]['use_by_date'] = $useByDate->format('Y-m-d');
          $due_in_coming_week[$i]['most_recent_use'] = $date_of_most_recent_use;
          $due_in_coming_week[$i]['interval'] = $this_interval;
      } elseif ($useByDate->format('Y-m-d') < $DateTimeNow->format('Y-m-d')) { // due in past
          $overdue_array[$i]['artifact'] = h($artifact['Title']);
          $overdue_array[$i]['artifact_id'] = h($artifact['id']);
          $overdue_array[$i]['use_by_date'] = $useByDate->format('Y-m-d');
          $overdue_array[$i]['most_recent_use'] = $date_of_most_recent_use;
          $overdue_array[$i]['interval'] = $this_interval;
      } 
      $i++;
  }

  $count_to_notify_about = 
    count($due_today_array)
    + count($overdue_array)
    + count($due_in_coming_week)
  ;
  
  if($count_to_notify_about > 0) { // email this list to the user
  
      // get user email address
      $email = singleValueQuery("SELECT email FROM users WHERE id = '$user_id'");
  
      $mail = new PHPMailer(true);
  
      // Server settings
      $mail->isSMTP();                                   //Send using SMTP
      $mail->Host       = 'smtp.sendgrid.net';           //Set the SMTP server to send through
      $mail->SMTPAuth   = true;                          //Enable SMTP authentication
      $mail->Username   = 'apikey';                      //SMTP username
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;   //Enable implicit TLS encryption
      $mail->Password   = SENDGRID_API_KEY;              //SMTP password 
      $mail->Port       = 465;                           //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
      
      // Recipients
      $mail->setFrom(SENDGRID_FROM_EMAIL, APP_NAME);
      $mail->addAddress($email);
      $mail->addReplyTo(DEV_EMAIL, DEV_NAME);
  
      // Content
      $mail->isHTML(true);
  
      
      try {
          $mail->Subject = "Interactions Due";
          $body = '';

          if (count($overdue_array) > 0) {
              $body .= '
                  <h1>Interactions overdue</h1>
                  <ul>
              ';
  
              foreach($overdue_array as $overdue) {
                  $name = $overdue['artifact'];
                  $most_recent_use = $overdue['most_recent_use'];
                  $use_by_date = $overdue['use_by_date'];
                  $id = $overdue['artifact_id'];
                  $interval = $overdue['interval'];
                  if ($most_recent_use === 'No interactions') {
                      $body .= "
                          <li>
                              <a href='https://" . DOMAIN . "/artifacts/edit.php?id=$id'>$name</a>: 
                              <a href='https://" . DOMAIN . "/uses/1-n-new?artifact_id=$id'>Record Interaction</a>
                              $most_recent_use, interact by $use_by_date (" . date('l', strtotime($use_by_date)) . ", interval: $interval days)
                          </li>
                      ";
                  } else {
                      $body .= "
                          <li>
                              <a href='https://" . DOMAIN . "/artifacts/edit.php?id=$id'>$name</a>: 
                              <a href='https://" . DOMAIN . "/uses/1-n-new?artifact_id=$id'>Record Interaction</a>
                              last interacted $most_recent_use, interact by $use_by_date (" . date('l', strtotime($use_by_date)) . " interval: $interval days)
                          </li>
                      ";
                  }
              }
  
              $body .= '
                  </ul>
              ';
          } 
  
          if (count($due_today_array) > 0) {
              $body .= '
                  <h1>Interactions due today</h1>
                  <ul>
              ';
  
              foreach($due_today_array as $due_today) {
                  $name = $due_today['artifact'];
                  $most_recent_use = $due_today['most_recent_use'];
                  $id = $due_today['artifact_id'];
                  $interval = $due_today['interval'];
                  $body .= "
                      <li>
                          <a href='https://" . DOMAIN . "/artifacts/edit.php?id=$id'>$name</a>: 
                          <a href='https://" . DOMAIN . "/uses/1-n-new?artifact_id=$id'>Record Interaction</a>
                          last interacted $most_recent_use (interval: $interval days)
                      </li>
                  ";
              }
  
              $body .= '
                  </ul>
              ';
          } 
  
          if (count($due_in_coming_week) > 0) {
              $body .= '
                  <h1>Interactions due in coming week</h1>
                  <ul>
              ';
  
              foreach($due_in_coming_week as $artifact) {
                  $name = $artifact['artifact'];
                  $most_recent_use = $artifact['most_recent_use'];
                  $use_by_date = $artifact['use_by_date'];
                  $id = $artifact['artifact_id'];
                  $interval = $artifact['interval'];
                  if ($most_recent_use === 'No interactions') {
                      $body .= "
                          <li>
                              <a href='https://" . DOMAIN . "/artifacts/edit.php?id=$id'>$name</a>: 
                              <a href='https://" . DOMAIN . "/uses/1-n-new?artifact_id=$id'>Record Interaction</a>
                              $most_recent_use, interact by $use_by_date (" . date('l', strtotime($use_by_date)) . ", interval: $interval days)
                          </li>
                      ";
                  } else {
                      $body .= "
                          <li>
                              <a href='https://" . DOMAIN . "/artifacts/edit.php?id=$id'>$name</a>: 
                              <a href='https://" . DOMAIN . "/uses/1-n-new?artifact_id=$id'>Record Interaction</a>
                              last interacted $most_recent_use, interact by $use_by_date (" . date('l', strtotime($use_by_date)) . ", interval: $interval days)
                          </li>
                      ";
                  }
              }
  
              $body .= '
                  </ul>
              ';
          } 
  
          $body .= '
              <p>Record uses at <a href="https://' . DOMAIN . '/uses/1-n-new.php">' . DOMAIN . '</a></p>
          ';
  
  
          $mail->Body = $body;
  
          $mail_result = $mail->send();
  
       } catch (Exception $Exception) {

          try {
              $mail->Subject = "Error with Artifact Uses Due Today Email";
              $mail->Body = '<p>The following Exception was thrown when trying to email an interact by list:</p>
                  <pre>' . print_r($Exception, true) . '</pre>
              ';
              $mail->send();
  
          } catch (Exception $Exception) {
              file_put_contents(__FILE__ . '.log', 
                  'Email exception caught in email notification to dev of error at ' 
                  . $currentDate->format('Y-m-d H:i:s') . "\n"
                  . print_r($Exception, true) . "\n", 
                  FILE_APPEND
              );
          }
  
       }
      
  };

  return $count_to_notify_about;
}

?>
