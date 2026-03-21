<?php // Initialize file

  $page_title = 'Record Use';

  require_once('../../private/initialize.php');
  require_login();

  $formProcessingFile = '1-n-new.php';

  if(is_post_request()) {

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

    if ($_POST['artifact']['name'] == '') {
      
      $_SESSION['message'] = "Please choose an artifact.";
      
      redirect_to(url_for('/uses/' . $formProcessingFile));

    } else {

      $insertResult = insert_response_one_to_many($_POST);

      if($insertResult === true) {
        $new_id = mysqli_insert_id($db);
        $user_count = count($_POST['user']);
        if ($user_count === 1) {
          $user_count_word = 'person';
        } else {
          $user_count_word = 'people';
        }
        $_SESSION['message'] = "The interaction with " . $_POST['artifact']['name'] 
          . " with $user_count $user_count_word was recorded."
        ;
        redirect_to(url_for('/uses/' . $formProcessingFile));
      } else {
        $errors = $insertResult;
      }
    }


  }

  if (isset($_GET['artifact_id'])) {
    $artifact_id = $_GET['artifact_id'];
    $artifact_name = singleValueQuery(
      "SELECT Title FROM games WHERE id = '$artifact_id' "
    );
  } else {
    $artifact_id = null;
    $artifact_name = null;
  }

  include(SHARED_PATH . '/header.php'); 
?>

<script type="module" src="modules/searchArtifactsList.js"></script>
<script type="module" src="modules/searchUsersList.js"></script>
<script type="module" src="modules/getUsers.js"></script>
<script defer src="1-n-new.js"></script>

<main>

  <h1>
    <?php echo $page_title; ?>
  </h1>

  <form action="<?php echo $formProcessingFile; ?>" method="post">
    <?php echo csrf_input(); ?>

    <label for="SearchTitles">Search Entities</label>
    <input type="search" 
      id="SearchTitles" 
      name="artifact[name]" 
      value="<?php echo $artifact_name; ?>"
      data-userid="<?php echo $_SESSION['user_id']; ?>"
    >
    <input type="hidden" id="SearchTitleSubmission" name="artifact[id]" 
      value="<?php echo $artifact_id; ?>"
    >
    <div class="searchResults" style="display: none;">
      <ul class="searchResults" style="margin-top: 0;">
        <li></li>
      </ul>
    </div>

    <label for="users">Interactors</label>
    <section id="users">
      <input 
        type="search" 
        class="user" 
        id="user0name" 
        name="user[0][name]" 
        value="<?php echo $_SESSION['FullName']; ?>"
        data-userid="<?php echo $_SESSION['user_id']; ?>"
        data-playerid="<?php echo $_SESSION['player_id']; ?>"
        data-listposition="0"
      >
      <input 
        type="hidden" 
        id="user0id" 
        name="user[0][id]" 
        value="<?php echo $_SESSION['player_id']; ?>"
        data-listposition="0"
      >
      <div id="userResultsDiv0" class="userResults user" style="display: none;">
        <ul id="userResults0" class="userResults user" style="margin-top: 0;">
          <li></li>
        </ul>
      </div>
    </section>

    <button 
      id="addUser"
      class="user"
      style="display: block;"
      >
      +
    </button>

    <label for="date">Date</label>
    <input type="date" name="useDate" id="date" 
      value="<?php
        $tz = 'America/New_York';
        $timestamp = time();
        $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
        $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
        echo $dt->format('Y') . '-' . $dt->format('m') . '-' . $dt->format('d'); ?>"  
    >

    <label for="Note">Setting</label>
    <?php 
      $most_recent_setting = singleValueQuery(
        "SELECT note 
        FROM uses
        WHERE user_id = '" . $_SESSION['user_id'] . "'
        ORDER BY id DESC
        LIMIT 1
      ");
      if ($most_recent_setting === null) {
        $default_setting = singleValueQuery(
          "SELECT default_setting
          FROM users
          WHERE id = '" . $_SESSION['user_id'] . "'
        ");
      }
    ?>
    <input type="text" 
      name="Note" 
      id="Note"
      value="<?php echo $most_recent_setting; ?>"
    >

    <label for="NotesTwo">Notes</label>
    <textarea 
      cols="30" 
      rows="5"
      name="NotesTwo" 
      id="NotesTwo"
    ></textarea>

    <input type="submit" value="Submit">

  </form>

  <script>
    document.addEventListener('keypress', function(event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        document.querySelector('form').submit();
      }
    })
  </script>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>