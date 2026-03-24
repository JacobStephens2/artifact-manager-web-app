<?php 

require_once('../../private/initialize.php');
global $db;

require_login();

$page_title = 'Edit User Settings';

if(is_post_request()) {
  $daily_email = isset($_POST['daily_email']) ? 1 : 0;
  $daily_email_hour = (int) ($_POST['daily_email_hour'] ?? 8);
  if ($daily_email_hour < 0 || $daily_email_hour > 23) {
    $daily_email_hour = 8;
  }
  $user_id = (int) $_SESSION['user_id'];

  $stmt = mysqli_prepare($db, "UPDATE users
    SET first_name = ?, last_name = ?, email = ?, username = ?,
        default_setting = ?, default_use_interval = ?, daily_email = ?, daily_email_hour = ?
    WHERE id = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, "sssssiiii",
    $_POST['first_name'],
    $_POST['last_name'],
    $_POST['email'],
    $_POST['username'],
    $_POST['default_setting'],
    $_POST['default_use_interval'],
    $daily_email,
    $daily_email_hour,
    $user_id
  );
  $update_result = mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);
}

$user_id = (int) $_SESSION['user_id'];
$stmt = mysqli_prepare($db, "SELECT
  first_name, last_name, email, username,
  default_use_interval, default_setting, daily_email, daily_email_hour
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
    <?php echo csrf_input(); ?>
    <label for="first_name">First Name</label>
    <input
      type="text"
      name="first_name"
      id="first_name"
      value="<?php echo h($userArray['first_name']); ?>"
      required minlength="2" maxlength="255"
    >

    <label for="last_name">Last Name</label>
    <input
      type="text"
      name="last_name"
      id="last_name"
      value="<?php echo h($userArray['last_name']); ?>"
      required minlength="2" maxlength="255"
    >

    <label for="email">Email</label>
    <input
      type="email"
      name="email"
      id="email"
      value="<?php echo h($userArray['email']); ?>"
      required maxlength="255"
    >

    <label for="username">Username</label>
    <input
      type="text"
      name="username"
      id="username"
      value="<?php echo h($userArray['username']); ?>"
      required minlength="8" maxlength="255"
    >

    <label for="default_use_interval">Default Use Interval</label>
    <input
      type="number"
      step="0.1"
      name="default_use_interval"
      id="default_use_interval"
      value="<?php echo h($userArray['default_use_interval']); ?>"
      required min="1"
    >
    
    <label for="default_setting">Default Setting</label>
    <input
      type="text"
      name="default_setting"
      id="default_setting"
      value="<?php echo h($userArray['default_setting']); ?>"
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

    <label for="daily_email_hour">Preferred email time (Eastern Time)</label>
    <select name="daily_email_hour" id="daily_email_hour">
      <?php
        for ($h = 0; $h <= 23; $h++) {
          $label = ($h === 0) ? '12:00 AM (midnight)' :
                   (($h < 12) ? $h . ':00 AM' :
                   (($h === 12) ? '12:00 PM (noon)' :
                   ($h - 12) . ':00 PM'));
          $selected = ((int)$userArray['daily_email_hour'] === $h) ? 'selected' : '';
          echo "<option value=\"$h\" $selected>$label</option>";
        }
      ?>
    </select>

    <input type="submit" value="Update Settings">
  </form>

  <a href="<?php echo url_for('/reset-password/index.php'); ?>">
    <p>Reset password</p>
  </a>


</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
