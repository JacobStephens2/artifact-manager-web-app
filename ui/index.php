<?php
require_once('../private/initialize.php');
require_login_or_guest();
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
  GROUP BY games.id, games.Title, games.Acq, games.interaction_frequency_days, types.objectType, games.KeptCol, games.user_id, games.to_get_rid_of
  HAVING games.user_id = ? AND games.KeptCol = 1 AND (games.to_get_rid_of = 0 OR games.to_get_rid_of IS NULL)
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
$tracked_count = count($overdue_items);
$overdue_count = 0;
$due_soon_count = 0;
$type_names = [];

foreach ($overdue_items as $item) {
  if ($item['days_diff'] < 0) {
    $overdue_count++;
  }

  if ($item['days_diff'] >= 0 && $item['days_diff'] <= 14) {
    $due_soon_count++;
  }

  if (!empty($item['type'])) {
    $type_names[$item['type']] = true;
  }
}

$type_count = count($type_names);

include(SHARED_PATH . '/header.php');
?>

<main>
  <div id="main-menu" class="dashboard">
    <section class="dashboard-hero">
      <div class="dashboard-hero-copy">
        <p class="section-label">Collection Command</p>
        <h1>Main Menu</h1>
        <p class="dashboard-intro">
          Track the rhythm of your collection, surface the pieces that need attention, and move from review to recording without leaving the dashboard.
        </p>

        <div class="dashboard-actions">
          <a class="prominent-link" href="<?php echo url_for('/artifacts/useby.php'); ?>">
            Review interaction queue
          </a>
          <a class="secondary-link" href="<?php echo url_for('/artifacts/index.php'); ?>">
            Browse all entities
          </a>
        </div>
      </div>

      <div class="dashboard-hero-aside">
        <div class="metric-card">
          <span class="metric-label">Tracked</span>
          <strong><?php echo h((string) $tracked_count); ?></strong>
        </div>
        <div class="metric-card">
          <span class="metric-label">Overdue</span>
          <strong><?php echo h((string) $overdue_count); ?></strong>
        </div>
        <div class="metric-card">
          <span class="metric-label">Due In 14 Days</span>
          <strong><?php echo h((string) $due_soon_count); ?></strong>
        </div>
        <div class="metric-card">
          <span class="metric-label">Types In Rotation</span>
          <strong><?php echo h((string) $type_count); ?></strong>
        </div>
      </div>
    </section>

    <div class="dashboard-grid">
      <?php if (!empty($top_overdue)) { ?>
      <section class="menu-card overdue-card">
        <p class="section-label">Priority Queue</p>
        <h2 class="menu-card-title">Most past due</h2>
        <table class="overdue-table">
          <?php foreach ($top_overdue as $item) {
            $overdue = $item['days_diff'] < 0;
          ?>
            <tr>
              <td class="overdue-name">
                <a href="<?php echo url_for('/artifacts/' . (is_guest() ? 'show' : 'edit') . '.php?id=' . h(u($item['id']))); ?>">
                  <?php echo h($item['title']); ?>
                </a>
                <?php if (!empty($item['type'])) { ?>
                  <span class="status-chip"><?php echo h($item['type']); ?></span>
                <?php } ?>
              </td>
              <td class="overdue-date<?php if ($overdue) echo ' overdue-past'; ?>">
                <?php echo h($item['use_by']); ?>
              </td>
              <?php if (!is_guest()) { ?>
              <td class="overdue-action">
                <a href="/uses/1-n-new?artifact_id=<?php echo h(u($item['id'])); ?>">Record</a>
              </td>
              <?php } ?>
            </tr>
          <?php } ?>
        </table>
        <a class="menu-link" href="<?php echo url_for('/artifacts/useby.php'); ?>">View full queue</a>
      </section>
      <?php } ?>
    </div>

    <div class="menu-grid">
      <div class="menu-card">
        <p class="section-label">Library</p>
        <h2 class="menu-card-title">Entities</h2>
        <p class="menu-support">Audit what is active, what is archived, and what needs to leave the shelf.</p>
        <a class="menu-link" href="<?php echo url_for('/artifacts/index.php'); ?>">All entities</a>
        <?php if (!is_guest()) { ?><a class="menu-link" href="<?php echo url_for('/artifacts/new.php'); ?>">Create new entity</a><?php } ?>
        <a class="menu-link" href="<?php echo url_for('/artifacts/useby.php'); ?>">Interact by date list</a>
        <a class="menu-link" href="<?php echo url_for('/artifacts/to-get-rid-of.php'); ?>">To get rid of</a>
      </div>

      <div class="menu-card">
        <p class="section-label">Activity</p>
        <h2 class="menu-card-title">Interactions</h2>
        <p class="menu-support">Record new activity and review the full history across the collection.</p>
        <a class="menu-link" href="<?php echo url_for('/uses/interactions.php'); ?>">All interactions</a>
        <?php if (!is_guest()) { ?><a class="menu-link" href="/uses/1-n-new.php">Record interaction</a><?php } ?>
      </div>

      <?php if (!is_guest()) { ?>
      <div class="menu-card">
        <p class="section-label">People</p>
        <h2 class="menu-card-title">Users</h2>
        <p class="menu-support">Manage participant records and identify new candidates worth tracking.</p>
        <a class="menu-link" href="<?php echo url_for('/users/index.php'); ?>">Users</a>
        <a class="menu-link" href="<?php echo url_for('/users/new.php'); ?>">Add new user</a>
        <a class="menu-link" href="<?php echo url_for('/explore/candidates.php'); ?>">Candidates</a>
      </div>

      <div class="menu-card">
        <p class="section-label">Account</p>
        <h2 class="menu-card-title">Settings</h2>
        <p class="menu-support">Tune defaults, notifications, and account-level maintenance tools.</p>
        <a class="menu-link" href="<?php echo url_for('/settings/edit.php'); ?>">Settings</a>
        <a class="menu-link" href="<?php echo url_for('/reset-password/index.php'); ?>">Reset password</a>
        <a class="menu-link" href="<?php echo url_for('/archive.php'); ?>">Archived pages</a>
      </div>
      <?php } ?>
    </div>

    <p class="menu-about">
      Artifact generates use-by dates for the objects you want to keep in circulation. The workflow was shaped by
      <a href="https://www.theminimalists.com/ninety/" target="_blank">The Minimalists' 90/90 Rule</a>
      and extended into a more deliberate collection practice by
      <a href="https://jacobstephens.net" target="_blank">Jacob Stephens</a>.
    </p>
  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
