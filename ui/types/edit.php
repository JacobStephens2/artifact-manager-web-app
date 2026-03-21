<?php
require_once('../../private/initialize.php');
global $db;
$page_title = 'Edit Type';

require_login();

$id = db_escape($db, $_GET['id']);
$type = singleValueQuery("SELECT ObjectType FROM types WHERE id = '$id'");

if(is_post_request()) {

  $user_id = $_SESSION['user_id'];

  $type = db_escape($db, $_POST['type']);

  $query = 
    "UPDATE types
    SET ObjectType = '$type'
    WHERE user_id = '$user_id'
    AND id = '$id'
    LIMIT 1
  ";
  $result = query($query);

  if ($result === true) {
    $message = "Update of type $type succeeded!";
  } else {
    $message = "Update of type $type failed.";
  }

}

include(SHARED_PATH . '/header.php'); 

?>

<main>

  <div class="object new">
    <h1><?php echo $page_title; ?></h1>

    <?php 
      echo display_errors($errors); 
      if (isset($message)) {
        echo "<p>$message</p>";
      }
    ?>

    <form method="post">
      <?php echo csrf_input(); ?>
      <dl>
        <dt>Type</dt>
        <dd><input type="text" name="type" value="<?php echo h($type); ?>" /></dd>
      </dl>
      <div>
        <input type="submit" value="Edit" />
      </div>
    </form>

    <a href="/types/delete?id=<?php echo $id; ?>">
      <p>Delete</p>
    </a>

  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
