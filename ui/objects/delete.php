<?php

require_once('../../private/initialize.php');
require_login();

if(!isset($_GET['id'])) {
  redirect_to(url_for('/objects/index.php'));
}
$id = $_GET['id'];

if(is_post_request()) {

  $result = delete_object($id);
  $_SESSION['message'] = 'The object was deleted successfully.';
  redirect_to(url_for('/objects/index.php'));

} else {
  $object = find_object_by_id($id);
}

?>

<?php $page_title = 'Delete object'; ?>
<?php include(SHARED_PATH . '/header.php'); ?>

<main>

  <a class="back-link" href="<?php echo url_for('/objects/index.php'); ?>">&laquo; Back to List</a>

  <div class="object delete">
    <h1>Delete object</h1>
    <p>Are you sure you want to delete this object?</p>
    <p class="item"><?php echo h($object['ObjectName']); ?></p>

    <form action="<?php echo url_for('/objects/delete.php?id=' . h(u($object['ID']))); ?>" method="post">
      <?php echo csrf_input(); ?>
      <div id="operations">
        <input type="submit" name="commit" value="Delete object" />
      </div>
    </form>
  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
