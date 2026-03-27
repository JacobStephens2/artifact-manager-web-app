<?php 
require_once('../../private/initialize.php');
require_login_or_guest();
$use_set = find_all_uses();
$page_title = 'uses';
include(SHARED_PATH . '/header.php');
?>

<main>
  <li><a class="back-link" href="<?php echo url_for('/objects/index.php'); ?>">&laquo; Objects</a></li>
  <li><a class="back-link" href="<?php echo url_for('/objects/useby.php'); ?>">&laquo; Use objects by list</a></li>
  <div class="uses listing">
    <h1>Object Uses</h1>

    <div class="actions">
      <li><a class="action" href="<?php echo url_for('/object_uses/create.php'); ?>">Record New Use</a></li>
    </div>

  	<table class="list">
  	  <tr>
        <th>UseDate (<?php echo $use_set->num_rows; ?>)</th>
        <th>ObjectName</th>
        <th></th>
  	  </tr>

      <?php while($use = mysqli_fetch_assoc($use_set)) { ?>
        <tr>
          <td>
            <a class="action" href="<?php echo url_for('/object_uses/edit.php?id=' . h(u($use['ID']))); ?>">
              <?php echo h($use['UseDate']); ?>
            </a>
          </td>
    	    <td>
              <?php echo h($use['ObjectName']); ?>
          </td>
          <td><a class="action" href="<?php echo url_for('/object_uses/delete.php?id=' . h(u($use['ID']))); ?>">Delete</a></td>
    	  </tr>
      <?php } ?>
  	</table>

  </div>

</main>

<?php 
mysqli_free_result($use_set);
include(SHARED_PATH . '/footer.php'); 
?>
