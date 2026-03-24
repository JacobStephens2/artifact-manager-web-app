<?php
  require_once('../../private/initialize.php');
  $page_title = 'Reset Password';
  include(SHARED_PATH . '/header.php');
?>

<style>
  form {
    padding-left: 3rem;
  }
  div {
    padding-left: 0;
  }
  label {
    font-weight: bold;
    font-size: 1.7rem;
  }
  input {
    display: block;
    margin-top: 0.5rem;
  }
  .update-message {
    padding-left: 3rem;
  }
</style>

<?php
if (
    isset($_GET["key"]) &&
    isset($_GET["email"]) &&
    isset($_GET["action"]) &&
    ($_GET["action"]=="reset") &&
    !isset($_POST["action"])
    ) {
      $key = $_GET["key"];
      $email = $_GET["email"];
      $curDate = date("Y-m-d H:i:s");
      $error = "";

      $stmt = mysqli_prepare($db, "SELECT * FROM password_reset_temp WHERE `key` = ? AND email = ?");
      mysqli_stmt_bind_param($stmt, "ss", $key, $email);
      mysqli_stmt_execute($stmt);
      $query = mysqli_stmt_get_result($stmt);
      $row_count = mysqli_num_rows($query);

      if ($row_count == 0) {
        $error = '
          <h2>Invalid Link</h2>
          <p>
            The link is invalid/expired. Either you did not copy the correct link from the email, or you have already used the key in which case it is deactivated.
          </p>
          <p>
            <a href="https://' . DOMAIN . '/reset-password/index.php">Click here</a> to reset password.
          </p>';
      } else {
        $row = mysqli_fetch_assoc($query);
        $expDate = $row['expDate'];
        if ($expDate >= $curDate) {
        ?>
          <br />
          <form method="post" action="reset-password.php" name="update">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="action" value="update" />
            <div>
              <label for="new_password">Enter New Password</label>
              <input type="password" name="new_password" id="new_password" minlength="12" required />
            </div>
            <div>
              <label for="new_password_check">Re-Enter New Password</label>
              <input type="password" name="new_password_check" id="new_password_check" minlength="12" required/>
            </div>
            <input type="hidden" name="email" value="<?php echo h($email); ?>"/>
            <input type="submit" value="Reset Password" />
          </form>
        <?php
        } else {
          $error = "
            <h2>Link Expired</h2>
            <p>The link is expired. You are trying to use the expired link which was valid only 24 hours after request.</p>
          ";
        }
      }
      mysqli_stmt_close($stmt);
      if ($error != "") {
        echo "<div class='error'>".$error."</div>";
      }
    } // isset email key validate end


if(isset($_POST["email"]) && isset($_POST["action"]) && ($_POST["action"]=="update")) {
  $error = "";
  $new_password = $_POST["new_password"];
  $new_password_check = $_POST["new_password_check"];
  $email = $_POST["email"];
  if ($new_password !== $new_password_check) {
    $error = "<p>Passwords do not match, both passwords should be the same.<br /><br /></p>";
  }
  if ($error !== "") {
    echo "<div class='error'>".$error."</div><br />";
  } else {
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    $stmt = mysqli_prepare($db, "UPDATE users SET hashed_password = ? WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $email);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Delete used reset token
    $stmt = mysqli_prepare($db, "DELETE FROM password_reset_temp WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($result) {
      ?>
      <div class="error update-message">
        <p>Your password has been updated successfully.</p>
        <p>
          <a href="https://<?php echo DOMAIN; ?>/login.php">Click here</a> to Login.
        </p>
      </div>
      <?php
    } else {
      echo "<div class='error'><p>Password reset failed. Please try again.</p></div>";
    }
  }
}

