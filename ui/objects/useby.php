<?php
require_once('../../private/initialize.php');
require_login_or_guest();
$page_title = 'Use By';
include(SHARED_PATH . '/header.php');
if(is_post_request()) {
  $_SESSION['interval'] = $_POST['interval'] ?? '90';
}
$interval = $_SESSION['interval'] ?? '90';
$limit = '';
$object_set = use_objects_by_user($interval, $limit); 
?>

<main>
  <li><a class="back-link" href="<?php echo url_for('/objects/index.php'); ?>">&laquo; Objects</a></li>
  <li><a class="back-link" href="<?php echo url_for('/object_uses/new.php'); ?>">&laquo; Record use</a></li>
  <li><a class="back-link" href="<?php echo url_for('/object_uses/index.php'); ?>">&laquo; Uses</a></li>
  <div class="objects listing">
    <h1>Use kept objects by date</h1>
    <p><a class="back-link" href="<?php echo url_for('/objects/about-useby.php'); ?>">Learn about use-by date generation</a></p>

    <form action="<?php echo url_for('/objects/useby.php'); ?>" method="post">
      <?php echo csrf_input(); ?>
      <dt>Interval from latest or to soonest play</dt>
          <input type="number" name="interval" value="<?php echo $interval ?>">
      <div id="operations">
        <input type="submit" value="Submit" />
      </div>
    </form>

  	<table class="list" >
        <tr id="header">
          <th>Name (<?php echo $object_set->num_rows; ?>)</th>
          <th>Type</th>
          <th>Use By</th>
          <th>Recent</th>
          <th>Kept</th>
          <th>Overdue</th>
        </tr>

      <tbody>
      <?php while($object = mysqli_fetch_assoc($object_set)) { ?>
        <tr>
          <td class="name">
            <a class="action" href="<?php echo url_for('/objects/edit.php?id=' . h(u($object['ID']))); ?>">
              <?php echo h($object['ObjectName']); ?>
            </a>
          </td>
    	    <td><?php echo h($object['ObjectType']); ?></td>
          <td><?php echo h($object['UseBy']); ?></td>
          <td><?php echo h($object['MaxUse']); ?></td>
          <td><?php 
            if(h($object['KeptCol']) == 1) {
              echo 'True';
            } else {
              echo "False";
            }
          ?>
          </td>
          <td 
            <?php 
                if ($object['UseBy'] < date('Y-m-d')) {
                  echo 'style="color: red;"';
                }
            ?>
            >
            <?php 
                if ($object['UseBy'] < date('Y-m-d')) {
                  echo 'Overdue';
                } else {
                  echo 'No';
                }
            ?>
          </td>
    	  </tr>
      <?php } ?>
      </tbody>
    
  	</table>

    <?php mysqli_free_result($object_set); ?>
  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
