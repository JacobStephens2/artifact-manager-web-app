<?php 
  require_once('../../private/initialize.php');
  require_login_or_guest();
  $use_set = find_responses_by_user_id();
  $page_title = 'Artifact Uses';
  include(SHARED_PATH . '/header.php');
  include(SHARED_PATH . '/dataTable.html');
?>

<main>
    <h1>Artifact Uses</h1>

  	<table class="list" id="uses" data-page-length='100'>
      <thead>
        <tr id="headerRow">
          <th>Interaction Date (<?php echo $use_set->num_rows; ?>)</th>
          <th>Title</th>
          <th>Person</th>
          <th>Type</th>
        </tr>
      </thead>

      <tbody>
        <?php while($use = mysqli_fetch_assoc($use_set)) { ?>
          <tr>
            <td class="date">
              <?php if (!is_guest()) { ?>
              <a
                class="action"
                href="<?php echo url_for('/uses/edit.php?id=' . h(u($use['responseID']))); ?>"
                >
                <?php echo h($use['PlayDate']); ?>
              </a>
              <?php } else { echo h($use['PlayDate']); } ?>
            </td>

            <td>
              <?php if (!is_guest()) { ?>
              <a
                class="action"
                href="<?php echo url_for('/uses/edit.php?id=' . h(u($use['responseID']))); ?>"
                >
                <?php echo h($use['Title']); ?>
              </a>
              <?php } else { echo h($use['Title']); } ?>
            </td>
            
            <td class="playerName">
              <?php echo h($use['FirstName']) . ' ' . h($use['LastName']); ?>
            </td>
            
            <td class="type">
              <?php echo h($use['type']); ?>
            </td>
          </tr>
        <?php } ?>
      </tbody>
  	</table>

    <?php mysqli_free_result($use_set); ?>

    <script>
      let table = new DataTable('#uses', {
        // options
        order: [[ 0, 'desc']]
      });
    </script>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
