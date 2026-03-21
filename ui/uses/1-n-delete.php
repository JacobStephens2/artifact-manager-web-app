<?php

  require_once('../../private/initialize.php');
  require_login();

  if(!isset($_GET['id'])) {
    redirect_to(url_for('/uses/1-n-uses.php'));
  }
  $id = $_GET['id'];

  if(is_post_request()) {

    $result = delete_one_to_many_use($id);
    $_SESSION['message'] = 'The use was deleted successfully.';
    redirect_to(url_for('/uses/1-n-uses.php'));

  } else {
    $use = find_use_details_by_id($id);
}

?>

<?php $page_title = 'Delete Interaction'; ?>
<?php include(SHARED_PATH . '/header.php'); ?>

<main>

  <div class="response-delete">
    <h1><?php echo $page_title; ?></h1>
    <p>Are you sure you want to delete this use?</p>
    <p class="item">Interaction id: <?php echo h($use['id']); ?></p>
    <p class="item">Interaction date: <?php echo h(substr($use['use_date'], 0, 10)); ?></p>
    <p class="item">Artifact: <?php echo h($use['artifact']); ?></p>
    <p class="item">People: 
    <?php
      $usersResultObject = find_users_by_use_id($use['id']);
      $i = 0;
      foreach ($usersResultObject as $user) {
        $i++;
        echo h($user['FirstName']) . ' ' . h($user['LastName']);
        if ($i != $usersResultObject->num_rows) {
          echo ', ';
        }
      }
    ?>
    </p>

    <form action="<?php echo url_for('/uses/1-n-delete.php?id=' . h(u($use['id']))); ?>" method="post">
      <?php echo csrf_input(); ?>
      <div id="operations">
        <input type="submit" name="commit" value="Delete Interaction" />
      </div>
    </form>
  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
