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
$con = $db;
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
      $query = mysqli_query(
        $con,
        "SELECT * FROM `password_reset_temp` WHERE `key`='".$key."' and `email`='".$email."';"
      );
      $row = mysqli_num_rows($query);
      if ($row=="") {
        $error .= '
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
              <label>Enter New Password</label>
              <input type="password" name="new_password" maxlength="15" required />
            </div>
            <div>
              <label>Re-Enter New Password</label>
              <input type="password" name="new_password_check" maxlength="15" required/>
            </div>
            <input type="hidden" name="email" value="<?php echo $email;?>"/>
            <input type="submit" value="Reset Password" />
          </form>
        <?php
        } else {
          $error .= "
            <h2>Link Expired</h2>
            <p>The link is expired. You are trying to use the expired link which as valid only 24 hours (1 days after request).</p>
          ";
        }
      }
      if ($error!="") {
        echo "<div class='error'>".$error."</div>";
      } 
    } // isset email key validate end
 

if(isset($_POST["email"]) && isset($_POST["action"]) && ($_POST["action"]=="update")) {
  $error="";
  $new_password = mysqli_real_escape_string($con,$_POST["new_password"]);
  $new_password_check = mysqli_real_escape_string($con,$_POST["new_password_check"]);
  $email = $_POST["email"];
  if ($new_password!=$new_password_check){
    $error.= "<p>Password do not match, both password should be same.<br /><br /></p>";
  }
  if($error!=""){
    echo "<div class='error'>".$error."</div><br />";
  } else {
    $form_data = [];
    $form_data['password'] = $new_password;
    $form_data['email'] = $email;
    $result = reset_password($form_data);
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    $sql = "UPDATE users SET hashed_password = '" . $hashed_password . "' WHERE email = '$email'";
    $result = mysqli_query($con, $sql);
    if($result) {
      true;
    } else {
      echo mysqli_error($db);
      db_disconnect($db);
    }
    mysqli_query($con,"DELETE FROM `password_reset_temp` WHERE `email`='".$email."';");

    $new_password = md5($new_password);
    $sql = "UPDATE 'users'";
    $sql .= "SET 'hashed_password' = '" . $new_password . "', ";
    $sql .= "'trn_date' = '" . $curDate . "' ";
    $sql .= "WHERE 'email' = '" . $email . "'";
    mysqli_query($con, $sql);
      if($result === true) {
        ?>
        <div class="error update-message">
          <p>Congratulations! Your password has been updated successfully.</p>
          <p>
            <a href="https:\/\/<?php echo DOMAIN; ?>/login.php">Click here</a> to Login.
          </p>
        </div>
        <?php
      }
    } 
  }

