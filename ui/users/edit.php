<?php

  require_once('../../private/initialize.php');
  require_login();

  if(!isset($_GET['id'])) {
    redirect_to(url_for('/users/index.php'));
  }
  $id = $_GET['id'];

  $user_id = $_SESSION['user_id'];

  if(is_post_request()) {

    // Handle form values sent by new.php
    $player = [];
    $player['id'] = $id ?? '';
    $player['FirstName'] = $_POST['FirstName'] ?? '';
    $player['LastName'] = $_POST['LastName'] ?? '';
    $player['G'] = $_POST['G'] ?? '';
    $player['Age'] = $_POST['Age'] ?? '';
    $player['thisPlayerIsMe'] = $_POST['thisPlayerIsMe'] ?? '';
    $player['user_id'] = $user_id ?? '';

    $result = update_player($player);
    if($result === true) {
      $_SESSION['message'] = 'The user was updated successfully.';
      redirect_to(url_for('/users/show.php?id=' . $id));
    } else {
      $errors = $result;
    }

  } else {

    $player = find_player_by_id($id);

  }

  $page_title = 'Edit User';
  include(SHARED_PATH . '/header.php');
  include(SHARED_PATH . '/dataTable.html');
?>

<main>

  <div class="object edit">
    <h1><?php echo $page_title; ?></h1>

    <?php echo display_errors($errors); ?>

    <form action="<?php echo url_for('/users/edit.php?id=' . h(u($id))); ?>" method="post">
      <?php echo csrf_input(); ?>

      <label for="FirstName">First Name</label>
      <input 
        type="text" 
        name="FirstName" 
        id="FirstName"
        value="<?php echo h($player['FirstName']); ?>" 
      />

      <label for="LastName">Last Name</label>
      <input 
        type="text" 
        id="LastName"
        name="LastName" 
        value="<?php echo h($player['LastName']); ?>" 
      />

      <label for="Gender">Gender (M, F, or Other)</label>
      <input type="text" id="Gender" name="G" value="<?php echo h($player['G']); ?>" />

      <label for="Age">Age</dt>
      <input type="text" id="Age" name="Age" value="<?php echo h($player['Age']); ?>" />

      <label for="thisPlayerIsMe">This User Is Me</label>
      <input type="hidden" name="thisPlayerIsMe" value="no">
      <input type="checkbox" name="thisPlayerIsMe" id="thisPlayerIsMe"
        value="yes"
        <?php 
          $stmt_rep = mysqli_prepare($db, "SELECT represents_user_id FROM players WHERE id = ?");
          mysqli_stmt_bind_param($stmt_rep, "i", $id);
          mysqli_stmt_execute($stmt_rep);
          $rep_result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_rep));
          mysqli_stmt_close($stmt_rep);
          $userIDThisPlayerIDRepresents = $rep_result['represents_user_id'] ?? null;
          if ($userIDThisPlayerIDRepresents == $_SESSION['user_id']) {
            echo 'checked';
          }
        ?>
      >

      <input type="submit" value="Save Edits" />

    </form>

  </div>

  <section id="uses">
    <?php 
      $player_id = (int) $_REQUEST['id'];
      $user_id_int = (int) $user_id;

      // get 1:n uses
      $stmt_up = mysqli_prepare($db, "SELECT use_id FROM uses_players WHERE user_id = ? AND player_id = ?");
      mysqli_stmt_bind_param($stmt_up, "ii", $user_id_int, $player_id);
      mysqli_stmt_execute($stmt_up);
      $results = mysqli_stmt_get_result($stmt_up);
      ?>
      <p><?php $results->num_rows; ?> 1:n interactions recorded</p>
      <table>
        <thead>
          <tr>
            <td>Entity</td>
            <td>Interaction Date</td>
          </tr>
        </thead>
        <tbody>
          <?php
            foreach ($results as $result) {
              $use_id = (int) $result['use_id'];
              $stmt_ud = mysqli_prepare($db, "SELECT games.title, DATE(use_date) AS use_date FROM uses JOIN games ON uses.artifact_id = games.id WHERE uses.id = ?");
              mysqli_stmt_bind_param($stmt_ud, "i", $use_id);
              mysqli_stmt_execute($stmt_ud);
              $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_ud));
              mysqli_stmt_close($stmt_ud);
              ?>
              <tr>
                <td><?php echo $data['title']; ?></td>
                <td><?php echo $data['use_date']; ?></td>
              </tr>
              <?php
            }
          ?>
        </tbody>
      </table>
      <?php


    
      // get 1:1 uses
      $stmt_11 = mysqli_prepare($db, "SELECT
        responses.PlayDate,
        responses.id as responseID,
        games.Title,
        games.type,
        games.id AS artifactID
        FROM responses
        JOIN games ON games.id = responses.Title
        WHERE responses.Player = ?
        ORDER BY responses.PlayDate DESC");
      mysqli_stmt_bind_param($stmt_11, "i", $player_id);
      mysqli_stmt_execute($stmt_11);
      $resultObject = mysqli_stmt_get_result($stmt_11);
    ?>
    <h2>
      <?php echo $resultObject->num_rows; ?>
      <?php echo h($player['FirstName']) . ' ' . h($player['LastName']); ?>
      1:1 interactions are recorded 
    </h2>

    <table id="useList" data-page-length='100'>

      <thead>
        <tr>
          <th>Interaction Date</th>
          <th>Artifact</th>
          <th>Type</th>
        </tr>
      </thead>

      <tbody>
        
        <?php foreach ($resultObject as $resultArray) { ?>        

          <tr>

            <td id="playDate">
              <a href="/uses/edit.php?id=<?php echo $resultArray['responseID']; ?>">
                <?php 
                  if ($resultArray['PlayDate'] == '') {
                    echo 'No date';
                  } else {
                    echo $resultArray['PlayDate'];
                  }
                ?>
              </a>
            </td>

            <td id="artifact">
              <a href="/artifacts/edit.php?id=<?php echo $resultArray['artifactID']; ?>">
                <?php echo $resultArray['Title']; ?>
              </a>
            </td>

            <td id="type">
              <?php echo $resultArray['type']; ?>
            </td>

          </tr>
          
        <?php } ?>

      </tbody>

    </table>

    <script>
      let table = new DataTable('#useList', {
        // options
        order: [[ 0, 'desc']]
      });
    </script>
  </section>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
