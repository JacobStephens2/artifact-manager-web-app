<?php 
  require_once('../../private/initialize.php');
  require_login();
  $id = $_GET['id'] ?? '1';
  $object = find_game_by_id($id);
  $page_title = 'Show Artifact';
  include(SHARED_PATH . '/header.php');
?>

<main>

  <li><a class="back-link" href="<?php echo url_for('/artifacts/index.php'); ?>">&laquo; Artifacts</a></li>
  <li><a class="back-link" href="<?php echo url_for('/artifacts/useby.php'); ?>">&laquo; Interact By List</a></li>
  <li><a class="back-link" href="<?php echo url_for('/artifacts/new.php'); ?>">&laquo; Create Entity</a></li>
  <li><a class="back-link" href="<?php echo url_for('/uses/1-n-new.php?artifact_id=' . h(u($object['id']))); ?>">&laquo; Record Interaction</a></li>
  
  <h1>Title: <?php echo h($object['Title']); ?></h1>
  
  <dl>
    <dt>Acquisition Date</dt>
    <dd><?php echo h($object['Acq']); ?></dd>
  </dl>
  
  <dl>
    <dt>Tracked?</dt>
    <dd><?php echo $object['KeptCol'] == '1' ? 'true' : 'false'; ?></dd>
  </dl>
  
  <dl>
    <dt>Type</dt>
    <dd><?php echo h($object['type']); ?></dd>
  </dl>

  <li><a class="back-link" href="<?php echo url_for('/artifacts/edit.php?id=' . h(u($object['id']))); ?>">Edit</a></li>
  
</main>
