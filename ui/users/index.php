<?php 
require_once('../../private/initialize.php');
require_login();
$player_set = find_players_by_user_id();
$page_title = 'Users';
include(SHARED_PATH . '/header.php');
include(SHARED_PATH . '/dataTable.html');
?>

<main>
  <div class="objects listing">
    <h1>Users</h1>
    <a href="<?php echo url_for('/users/new'); ?>">Create User</a>

  	<table class="list" id="users" data-page-length='100'>

      <thead>
        <tr id="headerRow">
          <th>Name (<?php echo $player_set->num_rows; ?>)</th>
          <th>Gender</th>
          <th>Age</th>
          <th></th>
          <th>ID</th>
        </tr>
      </thead>

      <tbody>
        <?php while($player = mysqli_fetch_assoc($player_set)) { ?>
          <tr>
            <td>
              <a class="table-action" href="<?php echo url_for('/users/edit.php?id=' . h(u($player['id']))); ?>">
                <?php echo h($player['FirstName']) . ' ' . h($player['LastName']); ?>
              </a>
            </td>
            <td><?php echo h($player['G']); ?></td>
            <td><?php echo $player['birth_year'] ? (date('Y') - (int) $player['birth_year']) : ''; ?></td>
            <td><a class="table-action" href="<?php echo url_for('/users/delete.php?id=' . h(u($player['id']))); ?>">Delete</a></td>
            <td><?php echo h($player['id']); ?></td>
          </tr>
        <?php } ?>
      </tbody>

  	</table>

    <?php mysqli_free_result($player_set); ?>

    <script>
      let table = new DataTable('#users', {
        // options
        order: [[ 4, 'desc']] // most recent users first
      });
    </script>
  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
