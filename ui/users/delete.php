<?php

require_once('../../private/initialize.php');
require_login();

if(!isset($_GET['id'])) {
  redirect_to(url_for('/users/index.php'));
}
$id = $_GET['id'];

if(is_post_request()) {

  $result = delete_player($id);
  $_SESSION['message'] = 'The player was deleted successfully.';
  redirect_to(url_for('/users/index.php'));

} else {
  $player = find_player_by_id($id);
}

?>

<?php $page_title = 'Delete player'; ?>
<?php include(SHARED_PATH . '/header.php'); ?>

<main>

  <a class="back-link" href="<?php echo url_for('/users/index.php'); ?>">&laquo; Back to List</a>

  <div class="object delete">
    <h1>Delete player</h1>
    <p>Are you sure you want to delete this player?</p>
    <p class="item"><?php echo h($player['FirstName']) . ' ' . h($player['LastName']); ?></p>

    <form action="<?php echo url_for('/users/delete.php?id=' . h(u($player['id']))); ?>" method="post">
      <?php echo csrf_input(); ?>
      <div id="operations">
        <input type="submit" name="commit" value="Delete player" />
      </div>
    </form>
  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
