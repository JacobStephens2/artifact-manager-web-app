<?php
require_once('../../private/initialize.php');
require_login();

if(!isset($_GET['ID'])) {
  redirect_to(url_for('/playgroup/index.php'));
}
$ID = $_GET['ID'];

if(is_post_request()) {

  $result = delete_playgroup_player($ID);
  $_SESSION['message'] = 'The player was successfully removed from the playgroup.';
  redirect_to(url_for('/playgroup/index.php'));

} else {
  $object = find_playgroup_player_by_id($ID);
}

$page_title = 'Remove User From Group';
include(SHARED_PATH . '/header.php');

?>

<main>

  <div class="object delete">
    <h1><?php echo $page_title; ?></h1>
    <p>Are you sure you want to remove this user from the group?</p>
    <p class="item"><?php echo h($object['FirstName']) . ' ' . h($object['LastName']); ?></p>

    <form action="<?php echo url_for('/playgroup/delete.php?ID=' . h(u($object['ID']))); ?>" method="post">
      <?php echo csrf_input(); ?>
      <div ID="operations">
        <input type="submit" name="commit" value="Remove User From Group" />
      </div>
    </form>
  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
