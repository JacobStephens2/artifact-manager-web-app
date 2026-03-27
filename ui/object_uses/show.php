<?php require_once('../../private/initialize.php'); ?>

<?php require_login_or_guest();

$id = $_GET['id'] ?? '1'; // PHP > 7.0

$use = find_use_by_id($id);

?>

<?php $page_title = 'Show use'; ?>
<?php include(SHARED_PATH . '/header.php'); ?>

<main>

 <li><a class="back-link" href="<?php echo url_for('/object_uses/index.php'); ?>">&laquo; Uses</a><li>
 <li><a class="back-link" href="<?php echo url_for('/objects/useby.php'); ?>">&laquo; Use by</a><li>
 <li><a class="back-link" href="<?php echo url_for('/object_uses/new.php'); ?>">&laquo; New use</a><li>
 <li><a class="action" href="<?php echo url_for('/object_uses/edit.php?id=' . h(u($use['ID']))); ?>">Edit</a><li>

  <div class="use show">

    <h1>Object: <?php echo h($use['ObjectName']); ?></h1>

    <div class="attributes">
      <dl>
        <dt>Object type</dt>
        <dd><?php echo h($use['ObjectType']); ?></dd>
      </dl>
      <dl>
        <dt>Use</dt>
        <dd><?php echo h($use['UseDate']); ?></dd>
      </dl>
        <dt>ID</dt>
        <dd><?php echo h($use['ID']); ?></dd>
      </dl>
    </div>
    <br />
    <hr />

  </div>


  </div>

</main>
