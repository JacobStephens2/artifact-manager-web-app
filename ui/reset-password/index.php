<?php
require_once('../../private/initialize.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$currentDate = new DateTime();

$page_title = 'Reset';
include(SHARED_PATH . '/header.php');
?>

<main>
   <style>
      .update-message {
         padding-left: 0;
      }
   </style>

   <?php

   require_once(PRIVATE_PATH . '/rate_limiter.php');
   $rate_limiter = new RateLimiter($db);

   if(isset($_POST["email"]) && (!empty($_POST["email"]))){

      if (!$rate_limiter->checkAndRecord('password_reset', 3, 900)) {
         echo "<div class='error'><p>Too many reset attempts. Please try again in 15 minutes.</p></div>";
      } else {

      $email = $_POST["email"];
      $email = filter_var($email, FILTER_SANITIZE_EMAIL);
      $email = filter_var($email, FILTER_VALIDATE_EMAIL);

      if (!$email) {
         $error = "
            <p>
               Invalid email address please type a valid email address!
            </p>
         ";

      } else {
         $stmt = mysqli_prepare($db, "SELECT id FROM users WHERE email = ?");
         mysqli_stmt_bind_param($stmt, "s", $email);
         mysqli_stmt_execute($stmt);
         $results = mysqli_stmt_get_result($stmt);
         $row = mysqli_num_rows($results);
         mysqli_stmt_close($stmt);
         if ($row == 0){
            $error = "
               <p>
                  No user is registered with this email address!
               </p>
            ";
         }
      }

      if(isset($error)) {
         echo "
            <div class='error'>".$error."</div>
            <a href='javascript:history.go(-1)'>Go Back</a>";

      } else {

         $expDate = date("Y-m-d H:i:s", strtotime('+1 day'));
         $key = bin2hex(random_bytes(32));

         // Insert Temp Table
         $stmt = mysqli_prepare($db,
            "INSERT INTO password_reset_temp (email, `key`, expDate) VALUES (?, ?, ?)"
         );
         mysqli_stmt_bind_param($stmt, "sss", $email, $key, $expDate);
         mysqli_stmt_execute($stmt);
         mysqli_stmt_close($stmt);
         
         $output='<p>Dear user,</p>';
         $output.='<p>Please click on the following link to reset your password.</p>';
         $output.='<p>-------------------------------------------------------------</p>';
         $output.='<p><a href="https://' . DOMAIN . '/reset-password/reset-password.php?key='.$key.'&email='.$email.'&action=reset" 
            target="_blank">https://' . DOMAIN . '/reset-password/reset-password.php?key='.$key.'&email='.$email.'&action=reset</a></p>'; 
         $output.='<p>-------------------------------------------------------------</p>';
         $output.='<p>Copy the link to your browser. The link will expire after 1 day.</p>';
         $output.='<p>If you did not request this reset password email, no action is needed. Your password will not be reset.</p>';   
         $output.='<p>Thanks,</p>';
         $output.='<p>' . APP_NAME . '</p>';
         
         $mail = new PHPMailer(true);

         try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Password   = SMTP_PASS;
            $mail->Port       = SMTP_PORT;

            // Recipients
            $mail->setFrom(SMTP_FROM_EMAIL, APP_NAME);
            $mail->addAddress($email);
            $mail->addReplyTo(DEV_EMAIL, DEV_NAME);
   
            // Content
            $mail->isHTML(true);
            $mail->Subject = "Password Reset - " . DOMAIN;
            $mail->Body = $output;

            $mail->send();
            $message = array('message'=> 'Message has been sent');
            echo 'Email reset ran at ' . $currentDate->format('Y-m-d H:i:s');
            echo 
               "
               <div class='error update-message'>
                  <p>
                     An email has been sent to you with instructions 
                     on how to reset your password.
                  </p>
               </div>
               ";
         } catch (Exception $e) {
            echo 'Email exception caught at ' . $currentDate->format('Y-m-d H:i:s') . "<br/>";
            echo 'Caught exception: '. $e->getMessage() ."</br>";
         }
      }
      } // end rate limit else
   } else { ?>

      <form method="post" action="" name="reset">
         <?php echo csrf_input(); ?>
         <label for="email">
            Enter Your Email Address
         </label>
         <input type="email" name="email" id="email" placeholder="name@domain.com" />
         <input type="submit" value="Reset Password"/>
      </form>

   <?php } ?>

</main>

<?php include(SHARED_PATH . '/footer.php'); ?>