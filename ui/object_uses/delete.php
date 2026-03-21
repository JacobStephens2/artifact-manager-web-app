<?php

require_once('../../private/initialize.php');
require_login();

if(!isset($_GET['id'])) {
  redirect_to(url_for('/staff/object_uses/index.php'));
}
$id = $_GET['id'];

if(is_post_request()) {

  $result = delete_use($id);
  $_SESSION['message'] = 'The object_use was deleted successfully.';
  redirect_to(url_for('/object_uses/index.php'));

} else {
  $use = find_use_by_id($id);
}

?>

<?php $page_title = 'Delete Interaction'; ?>
<?php include(SHARED_PATH . '/header.php'); ?>

<main>

  <a class="back-link" href="<?php echo url_for('/object_uses/index.php'); ?>">&laquo; Back to List</a>

  <div class="object_use delete">
    <h1>Delete Interaction</h1>
    <p>Are you sure you want to delete this interaction?</p>
    <p class="item"><?php echo h($use['ObjectName']); ?></p>
    <p class="item"><?php echo h($use['UseDate']); ?></p>

    <form action="<?php echo url_for('/object_uses/delete.php?id=' . h(u($use['ID']))); ?>" method="post">
      <?php echo csrf_input(); ?>
      <div id="operations">
        <input type="submit" name="commit" value="Delete Interaction" />
      </div>
    </form>
  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
