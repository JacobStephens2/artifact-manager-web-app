<?php
require_once('../private/initialize.php');

$errors = [];
$username = '';
$password = '';

if (is_post_request()) {
  require_once(PRIVATE_PATH . '/rate_limiter.php');
  $rate_limiter = new RateLimiter($db);

  if (!$rate_limiter->checkAndRecord('register', 5, 3600)) {
    $errors[] = "Too many registration attempts. Please try again in an hour.";
  }

  $subject = [];
  $user['first_name'] = $_POST['first_name'] ?? '';
  $user['last_name'] = $_POST['last_name'] ?? '';
  $user['email'] = $_POST['email'] ?? '';
  $user['username'] = $_POST['username'] ?? '';
  $user['password'] = $_POST['password'] ?? '';
  $user['confirm_password'] = $_POST['confirm_password'] ?? '';

  if (empty($errors)) {
    $result = insert_user($user);
  }

  if(empty($errors) && $result === true) {
    $new_id = mysqli_insert_id($db);
    $_SESSION['message'] = 'User registered';
    $user['user_group'] = 1;
    try {
      log_in_user($user);
      redirect_to(url_for('/index.php'));
    } catch (Exception $e) {
      redirect_to(url_for('/index.php'));
    }
  } else {
    $errors = $result;
  }

} else {
  // display the blank form
  $user = [];
  $user["first_name"] = '';
  $user["last_name"] = '';
  $user["email"] = '';
  $user["username"] = '';
  $user['password'] = '';
  $user['confirm_password'] = '';
}
?>

<?php $page_title = 'Register'; ?>
<?php include(SHARED_PATH . '/header.php'); ?>

<main>

  <a class="back-link" href="<?php echo url_for('/login.php'); ?>">&laquo; Back to Login</a>

  <div class="user new">
    <h1>Register as new user</h1>

    <?php echo display_errors($errors); ?>

    <form action="<?php echo url_for('/register.php'); ?>" method="post">
      <?php echo csrf_input(); ?>
      <dl>
        <dt>First name</dt>
        <dd><input type="text" name="first_name" value="<?php echo h($user['first_name']); ?>" /></dd>
      </dl>

      <dl>
        <dt>Last name</dt>
        <dd><input type="text" name="last_name" value="<?php echo h($user['last_name']); ?>" /></dd>
      </dl>

      <dl>
        <dt>Username</dt>
        <dd><input type="text" name="username" value="<?php echo h($user['username']); ?>" /></dd>
      </dl>

      <dl>
        <dt>Email </dt>
        <dd><input type="text" name="email" value="<?php echo h($user['email']); ?>" /><br /></dd>
      </dl>

      <dl>
        <dt>Password</dt>
        <dd><input type="password" name="password" value="" /></dd>
      </dl>

      <dl>
        <dt>Confirm Password</dt>
        <dd><input type="password" name="confirm_password" value="" /></dd>
      </dl>
      <p>
        Passwords should be at least 12 characters and include at least one uppercase letter, lowercase letter, number, and symbol.
      </p>
      <br />

      <div id="operations">
        <input type="submit" value="Create user" />
      </div>
    </form>

  </div>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
