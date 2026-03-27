<?php 

require_once('../../private/initialize.php');

require_login_or_guest();

$id = $_GET['id'] ?? '1'; // PHP > 7.0

$object = find_object_by_id($id);

$page_title = 'Show object';

include(SHARED_PATH . '/header.php'); 

?>

<main>

  <li><a class="back-link" href="<?php echo url_for('/objects/index.php'); ?>">&laquo; Objects</a></li>
  <li><a class="back-link" href="<?php echo url_for('/object_uses/new.php'); ?>">&laquo; Use by</a></li>
  <li><a class="back-link" href="<?php echo url_for('/uses/create.php'); ?>">&laquo; Record interaction</a></li>
  <li><a class="back-link" href="<?php echo url_for('/objects/new.php'); ?>">&laquo; Add new object</a></li>

  <div class="object show">

    <h1>Object: <?php echo h($object['ObjectName']); ?></h1>

    <div class="attributes">
      <dl>
        <dt>Object name</dt>
        <dd><?php echo h($object['ObjectName']); ?></dd>
      </dl>
      <dl>
        <dt>Acquisition date</dt>
        <dd><?php echo h($object['Acq']); ?></dd>
      </dl>
      <dl>
        <dt>Tracked</dt>
        <dd><?php echo $object['KeptCol'] == '1' ? 'true' : 'false'; ?></dd>
      </dl>
      <dl>
        <dt>Object type</dt>
        <dd><?php echo h($object['ObjectType']); ?></dd>
      </dl>
    </div>

    <li><a class="action" href="<?php echo url_for('/objects/edit.php?id=' . h(u($object['ID']))); ?>">Edit</a></li>

    <hr />

  </div>


  </div>

</main>
