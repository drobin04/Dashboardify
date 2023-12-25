<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer library files
require '../vendor/autoload.php';

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
	include('../config/mail_functions.php');
	
	// Retrieve email settings
	$emailSettings = retrievemailsettings();
	if ($emailSettings) {
		$username = $emailSettings['username'];
		$password = $emailSettings['password'];
	} else {
		echo "Email settings not found.\n";
	}
	
	
	
    //Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.yandex.com';
    $mail->SMTPAuth = true;
    $mail->Username = $username; // Your email address
    $mail->Password = $password; // Your password
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    
// Set OAuth 2.0 credentials
//$mail->AuthType = 'XOAUTH2';
//$mail->oauthUserEmail = 'your-email@gmail.com'; // Your email address
//$mail->oauthClientId = 'your-client-id'; // Your OAuth client ID
//$mail->oauthClientSecret = 'your-client-secret'; // Your OAuth client secret
//$mail->oauthRefreshToken = 'your-refresh-token'; // Your OAuth refresh token
        
    //Recipient
    $mail->setFrom($username, 'Dashboardify App');
    $mail->addAddress('sendingaddressHere', 'Addressee here');

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