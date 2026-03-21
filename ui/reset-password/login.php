<?php
require_once('../private/initialize.php');

$errors = [];
$username = '';
$password = '';

if(is_post_request()) {

  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  // Validations
  if(is_blank($username)) {
    $errors[] = "Username cannot be blank.";
  }
  if(is_blank($password)) {
    $errors[] = "Password cannot be blank.";
  }

  // if there were no errors, try to login.
  if(empty($errors)) {
    // Using one variable ensures that msg is the same
    $login_failure_msg = "Log in was unsuccessful.";

    $user = find_user_by_username($username);
    if($user) {

      if(password_verify($password, $user['hashed_password'])) { // original
        // password matches
        log_in_user($user);
        redirect_to(url_for('/index.php'));      
      } else {
        // username found, but password does not match
        $errors[] = $login_failure_msg;
      }

    } else {
      // no username found
      $errors[] = $login_failure_msg;
    }

  }
  
}

?>

<?php $page_title = 'Log in'; ?>
<?php include(SHARED_PATH . '/header.php'); ?>
  
    <main>

      <p>You can use this site to generate a list of use-by dates for objects. <a href="https://jacobcstephens.com" target="_blank">Jacob Stephens</a> uses this tool to track usage of their books, ensuring they use each book either in the next or previous 180 days. <a href="https://www.theminimalists.com/ninety/" target="_blank">The Minimalists' 90/90 Rule</a> inspired Jacob to create this&nbsp;tool.</p>

      <a href="<?php echo url_for('/register.php'); ?>"><button type="button">Create an account</button></a>

      <h1>Log in</h1>

      <?php echo display_errors($errors); ?>

      <form action="login.php" method="post">
        <?php echo csrf_input(); ?>
        <h2>Username:</h2>
        <input class="input-box" type="text" name="username" value=""/>
        <h2>Password:</h2>
        <input class="input-box" type="password" name="password" value=""/><br>
        <input class="submit" type="submit" name="submit" value="Submit"  />
      </form>

      <a href="<?php echo url_for('/reset-password/index.php'); ?>"><button class="reset-password-button">Reset Password</button></a>
    </main>

<?php include(SHARED_PATH . '/footer.php'); ?>
<div class="white-space"></div>
