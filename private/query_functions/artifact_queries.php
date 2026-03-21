<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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

    $interval = (int)$interval;
    $interval_double = (int)($interval * 2);

    $params = [];
    $param_types = '';

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
            DATE_ADD(MAX(responses.PlayDate), INTERVAL " . $interval_double . " DAY)
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
        games.user_id = ? ";

        $params[] = $_SESSION['user_id'];
        $param_types .= 's';

        if (strlen($sweetSpot) > 0) {
          $sql .= " AND games.ss LIKE ? ";
          $params[] = '%' . $sweetSpot . '%';
          $param_types .= 's';
          $sql .= " AND games.ss NOT LIKE ? ";
          $params[] = '%1' . $sweetSpot . '%';
          $param_types .= 's';
          $sql .= " AND games.ss NOT LIKE ? ";
          $params[] = '%2' . $sweetSpot . '%';
          $param_types .= 's';
          $sql .= " AND games.ss NOT LIKE ? ";
          $params[] = '%3' . $sweetSpot . '%';
          $param_types .= 's';
          $sql .= " AND games.ss NOT LIKE ? ";
          $params[] = '%' . $sweetSpot . '0%';
          $param_types .= 's';
          $sql .= " AND games.ss NOT LIKE ? ";
          $params[] = '%' . $sweetSpot . '1%';
          $param_types .= 's';
          $sql .= " AND games.ss NOT LIKE ? ";
          $params[] = '%' . $sweetSpot . '2%';
          $param_types .= 's';
          $sql .= " AND games.ss NOT LIKE ? ";
          $params[] = '%' . $sweetSpot . '3%';
          $param_types .= 's';
          $sql .= " AND games.ss NOT LIKE ? ";
          $params[] = '%' . $sweetSpot . '4%';
          $param_types .= 's';
        }

        if (isset($type) && $type != [] && $type != '1') {
          $placeholders = implode(', ', array_fill(0, count($type), '?'));
          $sql .= " AND games.type_id IN ( " . $placeholders . ") ";
          foreach($type as $type_name => $type_id) {
            $params[] = $type_id;
            $param_types .= 's';
          }
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
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
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

    $type_id = $object['type'];
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
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, 'ssssssssssssssss',
      $object['Title'],
      $object['Notes'],
      $object['Acq'],
      $type_id,
      $type_name,
      $object['KeptCol'],
      $object['Candidate'],
      $object['CandidateGroupDate'],
      $object['UsedRecUserCt'],
      $object['SS'],
      $object['MnT'],
      $object['MxT'],
      $object['MnP'],
      $object['MxP'],
      $_SESSION['user_id'],
      $object['interaction_frequency_days']
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

  function delete_artifact($id) {
    global $db;

    $sql = "DELETE FROM games WHERE id=? LIMIT 1";
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
    $sql = "SELECT games.id, games.Title FROM games WHERE games.Title LIKE ? ORDER BY games.Title ASC";
    $like_param = '%' . $query . '%';
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "s", $like_param);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    confirm_result_set($result);
    return $result;
  }

  function list_users_by_query($query) {
    global $db;
    $sql = "SELECT players.id, players.FirstName, players.LastName FROM players WHERE players.FirstName LIKE ? ORDER BY players.FirstName ASC, LastName ASC";
    $like_param = '%' . $query . '%';
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "s", $like_param);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    confirm_result_set($result);
    return $result;
  }

  function use_by($type, $interval, $sweetSpot, $minimumAge, $shelfSort, $user = null) {

    if ($user === null && isset($_SESSION['user_id'])) {
      $user = $_SESSION['user_id'];
    }

    global $db;

    $params = [];
    $param_types = '';

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
        games.user_id = ?
      ";

      $params[] = $user;
      $param_types .= 's';

      if ($shelfSort == 'yes') {
        $sql .= " AND (games.KeptCol = 1 OR games.InSecondaryCollection = 'yes') ";
      } else {
        $sql .= " AND games.KeptCol = 1 ";
      }

      if ($sweetSpot !== '') {
        $sql .= "AND
          (
            games.ss LIKE ?
            OR games.ss LIKE ?
            OR games.ss LIKE ?
            OR games.ss LIKE ?
            OR games.ss LIKE ?
            OR games.ss LIKE ?
            OR games.ss LIKE ?
          )
        ";
        $params[] = $sweetSpot;
        $param_types .= 's';
        $params[] = $sweetSpot . ' %';
        $param_types .= 's';
        $params[] = '%0' . $sweetSpot . '%';
        $param_types .= 's';
        $params[] = '%,' . $sweetSpot;
        $param_types .= 's';
        $params[] = '%,' . $sweetSpot . ',%';
        $param_types .= 's';
        $params[] = '%, ' . $sweetSpot;
        $param_types .= 's';
        $params[] = '%, ' . $sweetSpot . ',%';
        $param_types .= 's';
      }

      if ($minimumAge !== '' && $minimumAge !== 0 && $minimumAge !== '0') {
        $sql .= " AND games.age >= ? ";
        $params[] = $minimumAge;
        $param_types .= 's';
      }

      if (gettype($type) === 'array') {
        if (count($type) > 0) {
          $placeholders = implode(',', array_fill(0, count($type), '?'));
          $sql .= "AND games.type_id IN (" . $placeholders . ") ";
          foreach($type as $typeIndividual) {
            $params[] = $typeIndividual;
            $param_types .= 's';
          }
        } else {
          $sql .= " AND type = '' ";
        }
      } elseif ($type === '') {
        // add no type clause
      } else {
        $sql .= "AND type = ? ";
        $params[] = $type;
        $param_types .= 's';
      }

      $sql .= "
        ORDER BY MostRecentUseOrResponse ASC
      ";
      $stmt = mysqli_prepare($db, $sql);
      mysqli_stmt_bind_param($stmt, $param_types, ...$params);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
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

function email_artifact_use_notice($user_id) {

  $sweetSpot = '';
  $minimumAge = 0;
  $shelfSort = 'no';
  $type = '';

  global $db;
  $stmt = mysqli_prepare($db, "SELECT default_use_interval FROM users WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_array($result);
  mysqli_stmt_close($stmt);
  $interval = ($row !== null) ? $row[0] : DEFAULT_USE_INTERVAL;

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
      $email_stmt = mysqli_prepare($db, "SELECT email FROM users WHERE id = ?");
      mysqli_stmt_bind_param($email_stmt, "i", $user_id);
      mysqli_stmt_execute($email_stmt);
      $email_result = mysqli_stmt_get_result($email_stmt);
      $email_row = mysqli_fetch_array($email_result);
      mysqli_stmt_close($email_stmt);
      $email = ($email_row !== null) ? $email_row[0] : null;
      if ($email === null) { return 0; }

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
