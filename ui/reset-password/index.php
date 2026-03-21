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

   if(isset($_POST["email"]) && (!empty($_POST["email"]))){
      $email = $_POST["email"];
      $email = filter_var($email, FILTER_SANITIZE_EMAIL);
      $email = filter_var($email, FILTER_VALIDATE_EMAIL);

      if (!$email) {
         $error .="
            <p>
               Invalid email address please type a valid email address!
            </p>
         ";

      } else {
         $sql = "SELECT * FROM `users` WHERE email='".$email."'";
         $results = mysqli_query($db, $sql);
         $row = mysqli_num_rows($results);
         if ($row==""){
            $error .= "
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

         $expFormat = mktime(
            date("H"), date("i"), date("s"), date("m") ,date("d")+1, date("Y")
         );
         $expDate = date("Y-m-d H:i:s",$expFormat);
         $key = md5(2418*2); // original
         $addKey = substr(md5(uniqid(rand(),1)),3,10);
         $key = $key . $addKey;

         // Insert Temp Table
         mysqli_query($db,
            "INSERT INTO 
               `password_reset_temp` 
               (`email`, `key`, `expDate`)
            VALUES 
               ('".$email."', '".$key."', '".$expDate."');
         ");
         
         $output='<p>Dear user,</p>';
         $output.='<p>Please click on the following link to reset your password.</p>';
         $output.='<p>-------------------------------------------------------------</p>';
         $output.='<p><a href="https://' . DOMAIN . '/reset-password/reset-password.php?key='.$key.'&email='.$email.'&action=reset" 
            target="_blank">https://' . DOMAIN . '/reset-password/reset-password.php?key='.$key.'&email='.$email.'&action=reset</a></p>'; 
         $output.='<p>-------------------------------------------------------------</p>';
         $output.='<p>Copy the link to your browser. The link will expire after 1 day.</p>';
         $output.='<p>If you did not request this reset password email, no action is needed. Your password will not be reset.</p>';   
         $output.='<p>Thanks,</p>';
         $output.='<p>Steward Goods</p>';
         
         $mail = new PHPMailer(true);

         try {
            // Server settings
            $mail->isSMTP();                                   //Send using SMTP
            $mail->Host       = 'smtp.sendgrid.net';           //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                          //Enable SMTP authentication
            $mail->Username   = 'apikey';                      //SMTP username
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;   //Enable implicit TLS encryption
            $mail->Password   = SENDGRID_API_KEY;              //SMTP password 
            $mail->Port       = 465;                           //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            
            // Recipients
            $mail->setFrom('jacob@stewardgoods.com', 'Jacob');
            $mail->addAddress($email);
            $mail->addReplyTo('jacob@stewardgoods.com', 'Jacob');
   
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