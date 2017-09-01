<?php
// This example uses the PHPMailer library:
// https://github.com/PHPMailer/PHPMailer
require 'PHPMailer-master/PHPMailerAutoload.php';
$mail = new PHPMailer;
$mail->SMTPDebug = 3;
$mail->isSMTP();
$mail->Host = 'smtp.sparkpostmail.com';
$mail->Port = 587;
$mail->SMTPSecure = 'tls';
$mail->SMTPAuth = true;
$mail->Username = 'SMTP_Injection';
// You will need an API Key with 'Send via SMTP' permissions.
// Create one here: https://app.sparkpost.com/account/credentials
$mail->Password = '<your SparkPost api key with SMTP permissions>';
// sparkpostbox.com is a sending domain used for testing
// purposes and is limited to 5 messages per account.
// Visit https://app.sparkpost.com/account/sending-domains
// to register and verify your own sending domain.
$mail->setFrom('return@mail.geekwithapersonality.com');
$mail->addAddress('jeff.goldstein@sparkpost.com');
$mail->Subject = 'Testing SparkPost SMTP from PHP!';
$mail->Body    = '<html><body>https:www.sparkpost.com <p>Please log into your National Income Life Inbox to review our new Privacy Rules for 2015!  Your privacy is an important priority at National Income Life. Our Privacy Policy (available at http://www.nationalincomelife.com/policy) informs you about information we collect and how we use it. This notice provides information on how National Income Life uses the information described below for (1) certain business and marketing reports and (2) making ads you see more relevant. If you do not want us to use this information for these purposes, you can let us know by using one of the options described in the Your Choices section of this notice. This supplements our Privacy Policy.</body></html>';
$mail->msgHTML = '<html><body>https:www.sparkpost.com <p>Please log into your National Income Life Inbox to review our new Privacy Rules for 2015!  Your privacy is an important priority at National Income Life. Our Privacy Policy (available at http://www.nationalincomelife.com/policy) informs you about information we collect and how we use it. This notice provides information on how National Income Life uses the information described below for (1) certain business and marketing reports and (2) making ads you see more relevant. If you do not want us to use this information for these purposes, you can let us know by using one of the options described in the Your Choices section of this notice. This supplements our Privacy Policy.</body></html>';
$mail->AltBody = 'Please log into your National Income Life Inbox to review our new Privacy Rules for 2015!  Your privacy is an important priority at National Income Life. Our Privacy Policy (available at http://www.nationalincomelife.com/policy) informs you about information we collect and how we use it. This notice provides information on how National Income Life uses the information described below for (1) certain business and marketing reports and (2) making ads you see more relevant. If you do not want us to use this information for these purposes, you can let us know by using one of the options described in the Your Choices section of this notice. This supplements our Privacy Policy.';

$mail->addCustomHeader('X-MSYS-API', '{"campaign_id" : "PHPExample", "archive" : [ "jeff@geekwithapersonality.com" ],
   "options" : {"open_tracking" : true, "click_tracking" : true},}');
if (!$mail->send()) {
  echo "Message could not be sent\n";
  echo "Mailer Error: " . $mail->ErrorInfo . "\n";
} else {
  echo "Message has been sent\n";
}
?>
