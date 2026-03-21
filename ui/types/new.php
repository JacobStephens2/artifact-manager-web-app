<?php
require_once('../../private/initialize.php');
global $db;

require_login();

if(is_post_request()) {

  $type = db_escape($db, $_POST['type']);
  $user_id = $_SESSION['user_id'];
  $id = singleValueQuery(
    "SELECT id FROM types
    WHERE ObjectType = '$type'
    AND user_id = '$user_id'
  ");
  if ($id === "No results") {
    $query = 
      "INSERT INTO types (
        ObjectType, user_id
      ) VALUES (
        '$type', '$user_id'
      )
    ";
    $result = query($query);
    if ($result === true) {
      $message = "Creation of type $type succeeded!";
    } else {
      $message = "Creation of type $type failed.";
    }
  } else {
    $message = "You already have a type with this name. A duplicate was not created.";
  }

}

?>

<?php $page_title = 'Add Type'; ?>
<?php include(SHARED_PATH . '/header.php'); ?>

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
        <dd><input type="text" name="type"/></dd>
      </dl>
      <div>
        <input type="submit" value="Add" />
      </div>
    </form>

    <a href="/types">
      <p>List of types</p>
    </a>

  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
