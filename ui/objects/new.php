<?php

require_once('../../private/initialize.php');
require_login();


if(is_post_request()) {

  $object = [];
  $object['ObjectName'] = $_POST['ObjectName'] ?? '';
  $object['Acq'] = $_POST['Acq'] ?? '';
  $object['ObjectType'] = $_POST['ObjectType'] ?? '';
  $object['KeptCol'] = $_POST['KeptCol'] ?? '';


  $result = insert_object_by_user($object);
  if($result === true) {
    $new_id = mysqli_insert_id($db);
    $_SESSION['message'] = 'The entity was created successfully.';
    redirect_to(url_for('/objects/show.php?id=' . $new_id));
  } else {
    $errors = $result;
  }

} else {
  // display the blank form
  $object = [];
  $object["ObjectName"] = '';
  $object["ObjectType"] = '';
  $object["Acq"] = '';
  $object["KeptCol"] = '';
}

?>

<?php $page_title = 'Create object'; ?>
<?php include(SHARED_PATH . '/header.php'); ?>

<main>

  <a class="back-link" href="<?php echo url_for('/objects/index.php'); ?>">&laquo; Back to List</a>

  <div class="object new">
    <h1>Create object</h1>

    <?php echo display_errors($errors); ?>

    <form action="<?php echo url_for('/objects/new.php'); ?>" method="post">
      <?php echo csrf_input(); ?>
      <dl>
        <dt>Object Name</dt>
        <dd><input type="text" name="ObjectName" value="<?php echo h($object['ObjectName']); ?>" /></dd>
      </dl>
      <dl>
        <dt>Object Type</dt>
        <dd>
          <select name="ObjectType">
          <?php
            $type_set = find_all_types();
            while($type = mysqli_fetch_assoc($type_set)) {
              echo "<option value=\"" . h($type['ID']) . "\"";
              if (h($type['ObjectType'] == 'book')) {
                echo 'selected';
              }
              echo ">" . h($type['ObjectType']) . "</option>";
            }
            mysqli_free_result($type_set);
          ?>
          </select>
        </dd>
      </dl>
      <dl>
        <dt>Acquisition date</dt>
        <dd><input type="date" name="Acq" value="<?php echo date('Y') . '-' . date('m') . '-' . date('d'); ?>"/></dd>
      </dl>
      <dl>
        <dt>Kept?</dt>
        <dd>
          <input type="hidden" name="KeptCol" value="0" />
          <input type="checkbox" name="KeptCol" value="1" checked />
        </dd>
      </dl>
      <div id="operations">
        <input type="submit" value="Create object" />
      </div>
    </form>

  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
