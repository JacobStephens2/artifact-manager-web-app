<?php 

require_once('../../private/initialize.php');
global $db;

require_login();

$page_title = 'Edit User Settings';

if(is_post_request()) {
  $daily_email = isset($_POST['daily_email']) ? 1 : 0;
  $user_id = (int) $_SESSION['user_id'];

  $stmt = mysqli_prepare($db, "UPDATE users
    SET first_name = ?, last_name = ?, email = ?, username = ?,
        default_setting = ?, default_use_interval = ?, daily_email = ?
    WHERE id = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "sssssiii",
    $_POST['first_name'],
    $_POST['last_name'],
    $_POST['email'],
    $_POST['username'],
    $_POST['default_setting'],
    $_POST['default_use_interval'],
    $daily_email,
    $user_id
  );
  $update_result = mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
}

$user_id = (int) $_SESSION['user_id'];
$stmt = mysqli_prepare($db, "SELECT
  first_name, last_name, email, username,
  default_use_interval, default_setting, daily_email
  FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$userResult = mysqli_stmt_get_result($stmt);
$userArray = mysqli_fetch_assoc($userResult);
mysqli_stmt_close($stmt);

?>

<?php include(SHARED_PATH . '/header.php'); ?>

<main>
  
  <h1><?php echo $page_title; ?></h1>

  <?php
      if (isset($update_result) && $update_result === false) {
        echo '<p>Update failed, please contact support</p>';
      } elseif (isset($update_result) && $update_result === true) {
        echo '<p>Update successful</p>';
      }
  ?>

  <form method='POST'>
    <label for="first_name">First Name</label>
    <input 
      type="text" 
      name="first_name" 
      id="first_name" 
      value="<?php echo $userArray['first_name']; ?>"
    >

    <label for="last_name">Last Name</label>
    <input 
      type="text" 
      name="last_name" 
      id="last_name"
      value="<?php echo $userArray['last_name']; ?>"
    >

    <label for="email">Email</label>
    <input 
      type="email" 
      name="email" 
      id="email"
      value="<?php echo $userArray['email']; ?>"
    >

    <label for="username">Username</label>
    <input 
      type="text" 
      name="username" 
      id="username"
      value="<?php echo $userArray['username']; ?>"
    >
    
    <label for="default_use_interval">Default Use Interval</label>
    <input 
      type="number" 
      step="0.1"
      name="default_use_interval" 
      id="default_use_interval"
      value="<?php echo $userArray['default_use_interval']; ?>"
    >
    
    <label for="default_setting">Default Setting</label>
    <input
      type="text"
      name="default_setting"
      id="default_setting"
      value="<?php echo $userArray['default_setting']; ?>"
    >

    <label for="daily_email">
      <input
        type="checkbox"
        name="daily_email"
        id="daily_email"
        value="1"
        <?php if ($userArray['daily_email']) echo 'checked'; ?>
      >
      Receive daily use-by email
    </label>

    <input type="submit" value="Update Settings">
  </form>

  <a href="<?php echo url_for('/reset-password/index.php'); ?>">
    <p>Reset password</p>
  </a>


</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
