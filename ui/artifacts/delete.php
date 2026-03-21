<?php

require_once('../../private/initialize.php');
require_login();

if(!isset($_GET['id'])) {
  redirect_to(url_for('/artifacts/index.php'));
}
$id = $_GET['id'];

if(is_post_request()) {

  $result = delete_artifact($id);
  $_SESSION['message'] = 'The artifact was deleted successfully.';
  redirect_to(url_for('/artifacts/index.php'));

} else {
  $object = find_artifact_by_id($id);
}

?>

<?php $page_title = 'Delete Artifact'; ?>
<?php include(SHARED_PATH . '/header.php'); ?>

<main>

  <a class="back-link" href="<?php echo url_for('/artifacts/index.php'); ?>">&laquo; Artifacts</a>

  <div class="object delete">
    <h1>Delete artifact</h1>
    <p>Are you sure you want to delete this artifact?</p>
    <p class="item"><?php echo h($object['Title']); ?></p>

    <form action="<?php echo url_for('/artifacts/delete.php?id=' . h(u($object['id']))); ?>" method="post">
      <?php echo csrf_input(); ?>
      <div id="operations">
        <input type="submit" name="commit" value="Delete Artifact" />
      </div>
    </form>
  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
