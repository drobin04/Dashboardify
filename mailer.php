<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Include PHPMailer library files

require 'vendor/autoload.php';

function mailAuthenticationCode($userEmail, $confirmationCode) {
	
	
	
	
	// Create a new PHPMailer instance
	$mail = new PHPMailer(true);
	
	try {
		include('config/mail_functions.php');
		
		// Retrieve email settings
		$emailSettings = retrievemailsettings(__DIR__ . '/config/emailconfig.ini');
		if ($emailSettings) {
			$username = $emailSettings['username'];
			$password = $emailSettings['password'];
			$smtpSecure = $emailSettings['smtpSecure'];
			$smtpPort = $emailSettings['smtpPort'];
		} else {
			echo "Email settings not found.\n";
		}
		
		
		
		//Server settings
		$mail->isSMTP();
		$mail->Host = 'smtp.yandex.com';
		$mail->SMTPAuth = true;
		$mail->Username = $username; // Your email address
		$mail->Password = $password; // Your password
		$mail->SMTPSecure = $smtpSecure;
		$mail->Port = $smtpPort;
		
		
		// Set OAuth 2.0 credentials
		//$mail->AuthType = 'XOAUTH2';
		//$mail->oauthUserEmail = 'your-email@gmail.com'; // Your email address
		//$mail->oauthClientId = 'your-client-id'; // Your OAuth client ID
		//$mail->oauthClientSecret = 'your-client-secret'; // Your OAuth client secret
		//$mail->oauthRefreshToken = 'your-refresh-token'; // Your OAuth refresh token
			
		//Recipient
		$mail->setFrom($username, '');
		$mail->addAddress($userEmail, '');
		//echo " Email: " . $username . " , PW: " . $password . " , SMTPSecure: " . $smtpSecure . " , Port: " . $smtpPort; // DONT EVER UNCOMMENT THIS ON A LIVE SYSTEM
		//Content
		$mail->isHTML(true);
		$mail->Subject = 'New User Confirmation Code for Dashboardify.App: ' . $confirmationCode;
		$mail->Body = "Your email confirmation code is: " 
		. $confirmationCode . " . 
		
This code is used to confirm a new email registration at the URL https://dashboardify.app/Dashboardify/ 

This email is being sent to the email address: " . $userEmail . " because an account was created for this address at https://dashboardify.app .
To unsubscribe, simply respond with 'unsubscribe'.";
	
		$mail->send();
		echo 'Email has been sent';
	} catch (Exception $e) {
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}
}

function mailAuthenticationCode_resetpw($userEmail, $confirmationCode) {
	
	
	
	
	// Create a new PHPMailer instance
	$mail = new PHPMailer(true);
	
	try {
		include('config/mail_functions.php');
		
		// Retrieve email settings
		$emailSettings = retrievemailsettings(__DIR__ . '/config/emailconfig.ini');
		if ($emailSettings) {
			$username = $emailSettings['username'];
			$password = $emailSettings['password'];
			$smtpSecure = $emailSettings['smtpSecure'];
			$smtpPort = $emailSettings['smtpPort'];
		} else {
			echo "Email settings not found.\n";
		}
		
		
		
		//Server settings
		$mail->isSMTP();
		$mail->Host = 'smtp.yandex.com';
		$mail->SMTPAuth = true;
		$mail->Username = $username; // Your email address
		$mail->Password = $password; // Your password
		$mail->SMTPSecure = $smtpSecure;
		$mail->Port = $smtpPort;
		
		
		// Set OAuth 2.0 credentials
		//$mail->AuthType = 'XOAUTH2';
		//$mail->oauthUserEmail = 'your-email@gmail.com'; // Your email address
		//$mail->oauthClientId = 'your-client-id'; // Your OAuth client ID
		//$mail->oauthClientSecret = 'your-client-secret'; // Your OAuth client secret
		//$mail->oauthRefreshToken = 'your-refresh-token'; // Your OAuth refresh token
			
		//Recipient
		$mail->setFrom($username, '');
		$mail->addAddress($userEmail, '');
		//echo " Email: " . $username . " , PW: " . $password . " , SMTPSecure: " . $smtpSecure . " , Port: " . $smtpPort; // DONT EVER UNCOMMENT THIS ON A LIVE SYSTEM
		//Content
		$mail->isHTML(true);
		$mail->Subject = 'New User Confirmation Code for Dashboardify.App: ' . $confirmationCode;
		$mail->Body = "Your email confirmation code is: " 
		. $confirmationCode . " . 
		
This code is used to confirm a password reset at the URL https://dashboardify.app/Dashboardify/ 

This email is being sent to the email address: " . $userEmail . " because a password reset request was submitted for this address at https://dashboardify.app .
To unsubscribe, simply respond with 'unsubscribe'. 
If you didn't submit this request, please contact us.";
	
		$mail->send();
		echo 'Email has been sent';
	} catch (Exception $e) {
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
	}
}
?>