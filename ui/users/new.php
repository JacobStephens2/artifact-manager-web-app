<?php
require_once('../../private/initialize.php');

require_login();

if(is_post_request()) {

  $player = [];
  $player['FirstName'] = $_POST['FirstName'] ?? '';
  $player['LastName'] = $_POST['LastName'] ?? '';
  $player['G'] = $_POST['G'] ?? '';
  if (isset($_POST['G']) && $_POST['G'] == '') {
    $player['G'] = 'other';
  }
  $player['Age'] = $_POST['Age'] ?? '';
  if (isset($_POST['Age']) && $_POST['Age'] == '') {
    $player['Age'] = '30';
  }


  $result = insert_player($player);
  if($result === true) {
    $new_id = mysqli_insert_id($db);
    $_SESSION['message'] = 'The player record was created successfully.';
    redirect_to(url_for('/users/show.php?id=' . $new_id));
  } else {
    $errors = $result;
  }

} else {
  // display the blank form
  $player = [];
  $player["FirstName"] = '';
  $player["LastName"] = '';
  $player["G"] = '';
  $player["Age"] = '';
}

?>

<?php $page_title = 'Add User'; ?>
<?php include(SHARED_PATH . '/header.php'); ?>

<main>


  <div class="object new">
    <h1>Create User Record</h1>

    <?php echo display_errors($errors); ?>

    <form action="<?php echo url_for('/users/new.php'); ?>" method="post">
      <?php echo csrf_input(); ?>
      <dl>
        <dt>First Name</dt>
        <dd><input type="text" name="FirstName" value="<?php echo h($player['FirstName']); ?>" /></dd>
      </dl>
      <dl>
        <dt>Last Name</dt>
        <dd><input type="text" name="LastName" value="<?php echo h($player['LastName']); ?>" /></dd>
      </dl>
      <dl>
        <dt>Gender (M, F, or Other)</dt>
        <dd><input type="text" name="G" value="<?php echo h($player['G']); ?>" /></dd>
      </dl>
      <dl>
        <dt>Age</dt>
        <dd><input type="text" name="Age" value="<?php echo h($player['Age']); ?>" /></dd>
      </dl>
      <div id="operations">
        <input type="submit" value="Add player" />
      </div>
    </form>

  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
