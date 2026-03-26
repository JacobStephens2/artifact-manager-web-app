<?php
  require_once('../../private/initialize.php');
  require_login();

  $user_id = $_SESSION['user_id'];
  $stmt = mysqli_prepare($db, "SELECT default_use_interval FROM users WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);
  $interval_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
  mysqli_stmt_close($stmt);
  $default_interval = (float) ($interval_row['default_use_interval'] ?? 90);

  $artifact_set = find_artifacts_to_get_rid_of();

  $page_title = 'To Get Rid Of';
  include(SHARED_PATH . '/header.php');
?>

<main>

  <h1>To Get Rid Of</h1>

  <p>These are artifacts you have decided to get rid of. They no longer appear in your Interact By list.</p>

  <?php if ($artifact_set->num_rows === 0) { ?>
    <p>You have no artifacts marked to get rid of.</p>
  <?php } else { ?>

    <table class="list">
      <thead>
        <tr id="headerRow">
          <th>Name (<?php echo $artifact_set->num_rows; ?>)</th>
          <th>Type</th>
          <th>Last Interaction</th>
          <th>Tracking Start</th>
          <th>Restore</th>
          <th>Delete</th>
        </tr>
      </thead>

      <tbody>
        <?php while ($artifact = mysqli_fetch_assoc($artifact_set)) {
          $id = h(u($artifact['id']));
        ?>
          <tr>
            <td class="name">
              <a href="<?php echo url_for('/artifacts/edit.php?id=' . $id); ?>">
                <?php echo h($artifact['Title']); ?>
              </a>
            </td>

            <td class="type"><?php echo h($artifact['type']); ?></td>

            <td class="date">
              <?php echo h(substr($artifact['MostRecentUseOrResponse'], 0, 10)); ?>
            </td>

            <td class="date"><?php echo h($artifact['Acq']); ?></td>

            <td>
              <form method="post" action="<?php echo url_for('/artifacts/mark-get-rid-of.php'); ?>" style="display:inline; margin:0;">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="artifact_id" value="<?php echo $id; ?>">
                <input type="hidden" name="artifact_name" value="<?php echo h($artifact['Title']); ?>">
                <input type="hidden" name="value" value="0">
                <input type="hidden" name="return_to" value="to-get-rid-of">
                <button type="submit" class="restore-btn">Restore</button>
              </form>
            </td>

            <td>
              <a class="action" href="<?php echo url_for('/artifacts/delete.php?id=' . $id); ?>">
                Delete
              </a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>

  <?php } ?>

  <?php mysqli_free_result($artifact_set); ?>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
