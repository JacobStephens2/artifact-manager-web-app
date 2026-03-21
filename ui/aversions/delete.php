<?php
require_once('../../private/initialize.php');
require_login();

if(!isset($_GET['id'])) {
  redirect_to(url_for('/uses/index.php'));
}
$id = $_GET['id'];

if(is_post_request()) {
  $result = delete_response($id);
  $_SESSION['message'] = 'The response was deleted successfully.';
  redirect_to(url_for('/uses/index.php'));

} else {
  $use = find_response_by_id($id);
}

$page_title = 'Delete Aversion';
include(SHARED_PATH . '/header.php');

?>

<main>

  <div class="response-delete">
    <h1><?php echo $page_title; ?></h1>
    <p>Are you sure you want to delete this aversion?</p>
    <p class="item">use id: <?php echo h($use['id']); ?></p>
    <p class="item">Play date: <?php echo h($use['PlayDate']); ?></p>
    <p class="item">Game: <?php echo h($use['Title']); ?></p>
    <p class="item">Player: <?php echo h($use['FirstName']) . ' ' . h($use['LastName']); ?></p>

    <form
      action="<?php echo url_for('/aversions/delete.php?id=' . h(u($use['id']))); ?>"
      method="post"
      >
      <?php echo csrf_input(); ?>
      <div id="operations">
        <input type="submit" name="commit" value="Delete Aversion" />
      </div>
    </form>
  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
