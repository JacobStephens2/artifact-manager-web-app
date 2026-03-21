<?php

require_once('../../private/initialize.php');
require_login();

if(is_post_request()) {

  $use = [];
  $use['ObjectName'] = $_POST['ObjectName'] ?? '';
  $use['UseDate'] = $_POST['UseDate'] ?? '';

  $result = insert_use($use);
  if($result === true) {
    $new_id = mysqli_insert_id($db);
    $_SESSION['message'] = 'The use was created successfully.';
    redirect_to(url_for('/object_uses/show.php?id=' . $new_id));
  } else {
    $errors = $result;
  }

  } else {
  // display the blank form
  $use = [];
  $use["ObjectName"] = '';
  $use["UseDate"] = '';
}

?>

<?php $page_title = 'Record Use'; ?>
<?php include(SHARED_PATH . '/header.php'); ?>

<main>

  <li><a class="back-link" href="<?php echo url_for('/object_uses/index.php'); ?>">&laquo; Objects</a><li>
  <li><a class="back-link" href="<?php echo url_for('/objects/useby.php'); ?>">&laquo; Use objects by list</a><li>

  <div class="use new">

    <h1>Record use</h1>
    <form action="<?php echo url_for('/object_uses/new.php'); ?>" method="post">
      <?php echo csrf_input(); ?>
      <dl>
        <dt>Object Name</dt>
        <dd>

          <!-- selecting object name -->
          <?php 
              $interval = 180;
              $limit = 1024;
              $first_set = use_objects_by_user($interval, $limit); 
              while($first = mysqli_fetch_assoc($first_set)) {
                $title = h($first['ObjectName']);
              }
              mysqli_free_result($first_set);
            ?>

          <select name="ObjectName">
          <?php
            $type_set = list_objects_by_user();
            while($type = mysqli_fetch_assoc($type_set)) {
              echo "<option value=\"" . h($type['ID']) . "\"";
              if($title == $type['ObjectName']) {
                echo " selected";
              }
              echo ">" . h($type['ObjectName']) . ", " . h($type['ObjectType']) . "</option>";
            }
            mysqli_free_result($type_set);
          ?>
          </select>
        </dd>
      </dl>
      <dl>
        <dt>Interaction Date</dt>
        <dd><input type="date" name="UseDate" value="<?php echo date('Y') . '-' . date('m') . '-' . date('d'); ?>" /></dd>
      </dl>
      <div id="operations">
        <input type="submit" value="Create use" />
      </div>
    </form>

  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>