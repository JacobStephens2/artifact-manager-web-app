<?php // Initialize file
  require_once('../../private/initialize.php');
  require_login();

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

            [2] => Array
                (
                    [name] => Jarren Horrocks
                    [id] => 26
                )
          )
      )
    */

    $insertResult = insert_response_revised($_POST);

    if($insertResult === true) {
      $new_id = mysqli_insert_id($db);
      $_SESSION['message'] = "The response was recorded successfully.";
      redirect_to(url_for('/uses/new.php'));
    } else {
      $errors = $insertResult;
    }

  }

  $page_title = 'Record Use';
  include(SHARED_PATH . '/header.php'); 
?>

<script type="module" src="modules/searchArtifactsList.js"></script>
<script type="module" src="modules/searchUsersList.js"></script>
<script type="module" src="modules/getUsers.js"></script>

<main>

  <h1>
    <?php echo $page_title; ?>
  </h1>

  <form action="new.php" method="post">
    <?php echo csrf_input(); ?>

    <label for="date">Date</label>
    <input type="date" name="useDate" id="date" 
      value="<?php
        $tz = 'America/New_York';
        $timestamp = time();
        $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
        $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
        echo $dt->format('Y') . '-' . $dt->format('m') . '-' . $dt->format('d'); ?>"  
    >

    <label for="SearchTitles">Search Entities</label>
    <input type="search" 
      id="SearchTitles" 
      name="artifact[name]" 
      value=""
      data-userid="<?php echo $_SESSION['user_id']; ?>"
    >
    <input type="hidden" id="SearchTitleSubmission" name="artifact[id]" value="">
    <div class="searchResults" style="display: none;">
      <ul class="searchResults" style="margin-top: 0;">
        <li></li>
      </ul>
    </div>

    <label for="users">Users List</label>
    <section id="users">
      <input type="search" class="user" id="user0name" name="user[0][name]" 
        value="<?php echo $_SESSION['FullName']; ?>"
        data-userid="<?php echo $_SESSION['user_id']; ?>"
        data-playerid="<?php echo $_SESSION['player_id']; ?>"
      >
      <input type="hidden" id="user0id" name="user[0][id]" 
        value="<?php echo $_SESSION['player_id']; ?>"
      >
      <div class="userResults user" style="display: none;">
        <ul class="userResults user" style="margin-top: 0;">
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

    <label for="Note">Note</label>
    <textarea 
      cols="30" 
      rows="5"
      name="Note" 
      id="Note"
    ></textarea>

    <input type="submit" value="Submit">

  </form>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>