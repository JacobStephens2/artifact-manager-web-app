<?php
  require_once('../../private/initialize.php');
  require_login();
  $page_title = 'Choose for group';
  include(SHARED_PATH . '/header.php');
  if(is_post_request()) {
    $_SESSION['range'] = $_POST['range'] ?? 'false';
    $_SESSION['type'] = $_POST['type'] ?? '1';
    $_SESSION['kept'] = $_POST['kept'] ?? 0;
  }
  $range = $_SESSION['range'] ?? 'false';
  $typeArray = $_SESSION['type'] ?? [];
  $type = $_SESSION['type'] ?? [];
  $kept = $_SESSION['kept'] ?? [];
  $game_set = choose_games_for_group($range, $typeArray, $kept);
  $usergroup = find_playgroup_by_user_id();
?>

<main>
  <div class="objects listing">
    <h1>Choose Artifacts for Group of <?php echo $usergroup->num_rows; ?> Users</h1>
    <p>
      The dates represent the most recent instance of the type of response indicated by the column header. SS = sweet spot, Mnp = minimum player count, Mxp = maximum player count.
    </p>
    <!-- Parameters form -->
    <form action="<?php echo url_for('/playgroup/choose.php'); ?>" method="post">
      <?php echo csrf_input(); ?>

        <div style="display: flex">
          <label for="range">Show only artifacts matching count of user group</label>
          <input type="hidden" name="range" value="false" />
          <input type="checkbox" id="range" name="range" value="true" <?php if($range == 'true') { echo " checked"; } ?> />
        </div>

        <div style="display: flex">
          <label for="kept">Show only artifacts kept</label>
          <input type="hidden" name="kept" value="0" />
          <input type="checkbox" id="kept" name="kept" value="1" <?php if($kept == 1) { echo " checked"; } ?> />
        </div>

        <label for="type">Artifact type</label>
        <section id="type">
          <?php require_once '../../private/shared/artifact_type_checkboxes.php'; ?>
        </section>

        <input type="submit" value="Submit" />
    </form>

    <p><?php echo $game_set->num_rows; ?> results</p>

  	<table class="list">
  	  <tr class="header-row">
        <th class="table-header">Artifact</th>
  	    <th class="table-header">User</th>
        <th class="table-header">SS</th>
        <th class="table-header">MnP</th>
        <th class="table-header">MxP</th>
        <th class="table-header">MxT</th>
  	    <th class="table-header">Use</th>
  	    <th class="table-header">Aversion</th>
  	    <th class="table-header">Type</th>
  	  </tr>

      <?php while($game = mysqli_fetch_assoc($game_set)) { ?>
        <tr>
          <td class="edit">
            <a class="table-action" href="<?php echo url_for('/artifacts/edit.php?id=' . h(u($game['id']))); ?>">
              <?php echo h($game['title']); ?>
            </a>
          </td>
    	    <td class="edit name">
            <a class="table-action" href="<?php echo url_for('/users/edit.php?id=' . h(u($game['PlayerID']))); ?>">
              <?php echo h($game['FirstName']) . ' ' . h($game['LastName']); ?>
            </a>
          </td>
    	    <td class="edit"><?php echo ltrim(h($game['ss']), '0'); ?></td>
          <td class="edit"><?php echo h($game['MnP']); ?></td>
          <td class="edit"><?php echo h($game['MxP']); ?></td>
          <td class="edit"><?php echo h($game['MxT']); ?></td>
          <td class="edit date">
            <a class="table-action" href="<?php echo url_for('/uses/edit.php?id=' . h(u($game['ResponseID']))); ?>">
              <?php echo h($game['MaxOfPlayDate']); ?>
            </a>
          </td>
          <td class="edit">
            <a class="table-action" href="<?php echo url_for('/aversions/edit.php?id=' . h(u($game['ResponseID']))); ?>">
              <?php echo h($game['MaxOfAversionDate']); ?></td>
            </a>
          <td class="edit"><?php echo h($game['type']); ?></td>
    	  </tr>
      <?php } ?>
  	</table>

    <?php mysqli_free_result($game_set); ?>
  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
