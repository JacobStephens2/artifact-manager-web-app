<?php
require_once('../private/initialize.php');
require_login();
$page_title = 'Menu';
include(SHARED_PATH . '/header.php');
?>

<main>
  <div id="main-menu">

    <h1>Main Menu</h1>

    <a class="prominent-link" href="<?php echo url_for('/artifacts/useby.php'); ?>">
      Interact by Date
    </a>

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
