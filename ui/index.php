<?php
require_once('../private/initialize.php');
require_login();
$page_title = 'Menu';

// Fetch user's default interval
$user_id = (int) $_SESSION['user_id'];
$stmt = mysqli_prepare($db, "SELECT default_use_interval FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$interval_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
$default_interval = (float) ($interval_row['default_use_interval'] ?? 90);

// Fetch tracked artifacts with most recent use dates (same query as use_by())
$stmt = mysqli_prepare($db, "SELECT
    games.id,
    games.Title,
    games.Acq,
    games.interaction_frequency_days,
    types.objectType AS type,
    CASE
      WHEN MAX(uses.use_date) IS NULL THEN MAX(responses.PlayDate)
      WHEN MAX(uses.use_date) < MAX(responses.PlayDate) THEN MAX(responses.PlayDate)
      ELSE MAX(uses.use_date)
    END AS MostRecentUseOrResponse
  FROM games
    LEFT JOIN responses ON games.id = responses.Title
    LEFT JOIN uses ON games.id = uses.artifact_id
    LEFT JOIN types ON games.type_id = types.id
  GROUP BY games.id, games.Title, games.Acq, games.interaction_frequency_days, types.objectType, games.KeptCol, games.user_id
  HAVING games.user_id = ? AND games.KeptCol = 1
  ORDER BY MostRecentUseOrResponse ASC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$artifact_result = mysqli_stmt_get_result($stmt);

// Calculate use-by dates and find top 5 most overdue
date_default_timezone_set('America/New_York');
$now = new DateTime(date('Y-m-d'));
$overdue_items = [];

while ($artifact = mysqli_fetch_assoc($artifact_result)) {
  $this_interval = $artifact['interaction_frequency_days'] !== null
    ? (float) $artifact['interaction_frequency_days']
    : $default_interval;

  $acq = new DateTime(substr($artifact['Acq'], 0, 10));

  if ($artifact['MostRecentUseOrResponse'] === null) {
    $base = clone $acq;
    $hours = (int)($this_interval * 24);
  } else {
    $recent = new DateTime(substr($artifact['MostRecentUseOrResponse'], 0, 10));
    if ($recent < $acq) {
      $base = clone $acq;
      $hours = (int)($this_interval * 24);
    } else {
      $base = clone $recent;
      $hours = (int)($this_interval * 2 * 24);
    }
  }

  $use_by = $base->add(DateInterval::createFromDateString("$hours hours"));
  $diff = (int) $now->diff($use_by)->format('%r%a'); // negative = overdue

  $overdue_items[] = [
    'id' => $artifact['id'],
    'title' => $artifact['Title'],
    'type' => $artifact['type'],
    'use_by' => $use_by->format('Y-m-d'),
    'days_diff' => $diff,
  ];
}
mysqli_stmt_close($stmt);

// Sort by use-by date ascending (most overdue first)
usort($overdue_items, fn($a, $b) => $a['days_diff'] <=> $b['days_diff']);
$top_overdue = array_slice($overdue_items, 0, 5);

include(SHARED_PATH . '/header.php');
?>

<main>
  <div id="main-menu">

    <h1>Main Menu</h1>

    <a class="prominent-link" href="<?php echo url_for('/artifacts/useby.php'); ?>">
      Interact by Date
    </a>

    <?php if (!empty($top_overdue)) { ?>
    <div class="menu-card overdue-card">
      <h2 class="menu-card-title">Most Past Due</h2>
      <table class="overdue-table">
        <?php foreach ($top_overdue as $item) {
          $overdue = $item['days_diff'] < 0;
        ?>
          <tr>
            <td class="overdue-name">
              <a href="<?php echo url_for('/artifacts/edit.php?id=' . h(u($item['id']))); ?>">
                <?php echo h($item['title']); ?>
              </a>
            </td>
            <td class="overdue-date<?php if ($overdue) echo ' overdue-past'; ?>">
              <?php echo h($item['use_by']); ?>
            </td>
            <td class="overdue-action">
              <a href="/uses/1-n-new?artifact_id=<?php echo h(u($item['id'])); ?>">Record</a>
            </td>
          </tr>
        <?php } ?>
      </table>
      <a class="menu-link" href="<?php echo url_for('/artifacts/useby.php'); ?>">View all &rarr;</a>
    </div>
    <?php } ?>

    <div class="menu-grid">

      <div class="menu-card">
        <h2 class="menu-card-title">Entities</h2>
        <a class="menu-link" href="<?php echo url_for('/artifacts/index.php'); ?>">All Entities</a>
        <a class="menu-link" href="<?php echo url_for('/artifacts/new.php'); ?>">Create New Entity</a>
        <a class="menu-link" href="<?php echo url_for('/artifacts/useby.php'); ?>">Interact by Date List</a>
      </div>

      <div class="menu-card">
        <h2 class="menu-card-title">Interactions</h2>
        <a class="menu-link" href="<?php echo url_for('/uses/1-n-uses.php'); ?>">All Interactions</a>
        <a class="menu-link" href="/uses/1-n-new.php">Record Interaction</a>
      </div>

      <div class="menu-card">
        <h2 class="menu-card-title">People</h2>
        <a class="menu-link" href="<?php echo url_for('/users/index.php'); ?>">Users</a>
        <a class="menu-link" href="<?php echo url_for('/users/new.php'); ?>">Add New User</a>
        <a class="menu-link" href="<?php echo url_for('/explore/candidates.php'); ?>">Candidates</a>
      </div>

      <div class="menu-card">
        <h2 class="menu-card-title">Account</h2>
        <a class="menu-link" href="<?php echo url_for('/settings/edit.php'); ?>">Settings</a>
        <a class="menu-link" href="<?php echo url_for('/reset-password/index.php'); ?>">Reset Password</a>
        <a class="menu-link" href="<?php echo url_for('/archive.php'); ?>">Archived Pages</a>
      </div>

    </div>

    <p class="menu-about">
      You can use this site to generate a list of use-by dates for objects.
      <a href="https://jacobstephens.net" target="_blank">Jacob Stephens</a>
      uses this tool to track usage of books, games, movies, equipment, and more, ensuring use for each
      either in the next or previous x days.
      <a href="https://www.theminimalists.com/ninety/" target="_blank">The Minimalists' 90/90 Rule</a>
      inspired Jacob to create this&nbsp;tool.
    </p>

  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
