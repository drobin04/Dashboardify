<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer library files
require '../vendor/autoload.php';

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();
    $mail->Host = '';
    $mail->SMTPAuth = true;
    $mail->Username = ''; // Your email address
    $mail->Password = ''; // Your password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    
// Set OAuth 2.0 credentials
$mail->AuthType = 'XOAUTH2';
$mail->oauthUserEmail = 'your-email@gmail.com'; // Your email address
$mail->oauthClientId = 'your-client-id'; // Your OAuth client ID
$mail->oauthClientSecret = 'your-client-secret'; // Your OAuth client secret
$mail->oauthRefreshToken = 'your-refresh-token'; // Your OAuth refresh token
    
    
    
    
    //Recipient
    $mail->setFrom('', 'Dashboardify App');
    $mail->addAddress('', '');

    //Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body = 'This is a test email sent from PHP using.';

    $mail->send();
    echo 'Email has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>