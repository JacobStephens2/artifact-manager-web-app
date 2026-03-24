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

    <!-- Artifacts -->
    <ul>
      <li>
        <a href="<?php echo url_for('/artifacts/index.php');?>">
          Entities
        </a>
      </li>

      <ul>
        <li>
          <a class="action" href="<?php echo url_for('/artifacts/new.php'); ?>">
            Create New Entity
          </a>
        </li>
        <li>
          <a href=" <?php echo url_for('/artifacts/useby.php');?>">
            Interact with Entities by Date List
          </a>
        </li>

      </ul>
    </ul>

    <!-- Uses -->
    <ul>
      <li>
        <a href="<?php echo url_for('/uses/1-n-uses.php');?>">
          Interactions
        </a>
      </li>
    
      <ul>
        <li>
          <a href="/uses/1-n-new.php">Record Interaction</a>
        </li>
      </ul>

    </ul>

    <!-- Users -->
    <ul>
      <li><a href="<?php echo url_for('/users/index.php');?>">
        Users
        </a>
      </li>

      <ul>
        <li>
          <a class="action" href="<?php echo url_for('/users/new.php'); ?>">
            Add New User
          </a>
        </li>

      </ul>
    </ul>

    <ul>
      <li>
        <a href="<?php echo url_for('/explore/candidates.php'); ?>">
          Candidates
        </a>
      </li>
    </ul>

    <ul>
      <li class="main-menu"><a href="<?php echo url_for('/archive.php'); ?>">Archived Pages</a></li>
    </ul>
    
    <ul>
      <li>
        <a href="<?php echo url_for('/settings/edit.php'); ?>">
          Settings
        </a>
      </li>
      <li class="main-menu">
        <a href="<?php echo url_for('/reset-password/index.php'); ?>">Reset password</a>
      </li>
    </ul>

    <p>
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
