<?php

require_once('../../private/initialize.php');
require_login();

if(!isset($_GET['id'])) {
  redirect_to(url_for('/objects/index.php'));
}
$id = $_GET['id'];

if(is_post_request()) {

  // Handle form values sent by new.php

  $object = [];
  $object['ID'] = $id ?? '';
  $object['ObjectName'] = $_POST['ObjectName'] ?? '';
  $object['Acq'] = $_POST['Acq'] ?? '';
  $object['ObjectType'] = $_POST['ObjectType'] ?? '';
  $object['KeptCol'] = $_POST['KeptCol'] ?? '';

  $result = update_object($object);
  if($result === true) {
    $_SESSION['message'] = 'The object was updated successfully.';
    redirect_to(url_for('/objects/show.php?id=' . $id));
  } else {
    $errors = $result;
    //var_dump($errors);
  }

} else {

  $object = find_object_by_id($id);

}

$object_set = find_all_objects();
$object_count = mysqli_num_rows($object_set);
mysqli_free_result($object_set);

?>

<?php $page_title = 'Edit object'; ?>
<?php include(SHARED_PATH . '/header.php'); ?>

<main>

  <a class="back-link" href="<?php echo url_for('/objects/index.php'); ?>">&laquo; Back to List</a>

  <div class="object edit">
    <h1>Edit object</h1>

    <?php echo display_errors($errors); ?>

    <form action="<?php echo url_for('/staff/objects/edit.php?id=' . h(u($id))); ?>" method="post">
      <?php echo csrf_input(); ?>
      <dl>
        <dt>ObjectName</dt>
        <dd><input type="text" name="ObjectName" value="<?php echo h($object['ObjectName']); ?>" /></dd>
      </dl>
      <dl>
        <dt>ObjectType</dt>
        <dd>
          <select name="ObjectType">
          <?php
            $type_set = find_all_types();
            while($type = mysqli_fetch_assoc($type_set)) {
              echo "<option value=\"" . h($type['ID']) . "\"";
              if($object["ObjectType"] == $type['ID']) {
                echo " selected";
              }
              echo ">" . h($type['ObjectType']) . "</option>";
            }
            mysqli_free_result($type_set);
          ?>
          </select>
        </dd>
      </dl>
        <dt>Acq</dt>
        <dd><input type="date" name="Acq" value="<?php echo h($object['Acq']); ?>" /></dd>
      </dl>
      <dl>
        <dt>KeptCol</dt>
        <dd>
          <input type="hidden" name="KeptCol" value="0" />
          <input type="checkbox" name="KeptCol" value="1"<?php if($object['KeptCol'] == "1") { echo " checked"; } ?> />
        </dd>
      </dl>
      <div id="operations">
        <input type="submit" value="Edit object" />
      </div>
    </form>

  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
