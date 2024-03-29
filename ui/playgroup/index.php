<?php
require_once('../../private/initialize.php');
require_login();
$object_set = find_playgroup_by_user_id();
$page_title = 'User Group';
include(SHARED_PATH . '/header.php');
?>

<main>
  <div class="objects listing">
    <h1><?php echo $page_title; ?></h1>

    <div class="actions">
      <li><a class="action" href="<?php echo url_for('/playgroup/new.php'); ?>">Add to User Group</a></li>
      <li><a class="action" href="<?php echo url_for('/playgroup/choose.php'); ?>">Choose Games for User Group</a></li>
    </div>

  	<table class="list">
  	  <tr>
        <th>Name (<?php echo $object_set->num_rows; ?>)</th>
        <th>User Group ID&ensp;</th>
        <th></th>
  	  </tr>

      <?php while($object = mysqli_fetch_assoc($object_set)) { ?>
        <tr>
          <td>
            <a 
              class="table-action" 
              href="<?php echo url_for('/users/edit.php?id=' . h(u($object['playerID']))); ?>"
              >
              <?php echo h($object['FirstName']) . ' ' . h($object['LastName']); ?>
            </a>
          </td>
          <td>
            <a class="table-action" href="<?php echo url_for('/playgroup/edit.php?ID=' . h(u($object['ID']))); ?>">
              <?php echo h($object['ID']); ?>
            </a>
          </td>
          <td>    
            <a class="table-action" href="<?php echo url_for('/playgroup/delete.php?ID=' . h(u($object['ID']))); ?>">
              Remove
            </a>
          </td>
    	  </tr>
      <?php } ?>
  	</table>

    <?php mysqli_free_result($object_set); ?>
  </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
