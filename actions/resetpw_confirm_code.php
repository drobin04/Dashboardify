<?php
// Accept username from POST
//include
include_once('../shared_functions.php');
$username = "";

if (isset($_POST["email"])) {
	//todo - come back and so some sort of verification to prevent abuse and spamming of this function....
	
	$username = $_POST["email"];
	$userid = getUserIDFromEmail($username);
	//Generate code for confirmation
	$code = mt_rand(100000, 999999);
	
	
	// Store confirmation code for user
	execquery("Update Users
	Set ConfirmationCode = '" . $code . "' 
	Where Email = '" . $username . "'");
	
	// Mail Confirmation Code for user on page load. 
	// Mail confirmationcode to user!
	include("../mailer.php");
	mailAuthenticationCode_resetpw($username,$code);
	
	
	
	
} else {
	// Return to login, something went wrong?
	redirect('start-login.php');
	
}



// Present form for user to enter the code.

// Form should submit to verify-login with a custom action for 
// resetpw_confirm_code , which will check if the code matches,
// if the code does match, it should give the user a session cookie, and redirect them
// to the change password screen. 


?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">
<head >
    <title>Dashboardify - Reset Password</title>
	
    <style>
        html, body {
			height: 100%;
		}
		.screen {
			margin: auto auto auto auto;
			margin-top: 100px !important;
		}
			@import url('https://fonts.googleapis.com/css?family=Raleway:400,700');

* {
	box-sizing: border-box;
	margin: 0;
	padding: 0;	
	font-family: Raleway, sans-serif;
}

body {
	background: linear-gradient(90deg, #C7C5F4, #776BCC);		
}

.container {
	display: flex;
	align-items: center;
	justify-content: center;
	min-height: 100vh;
}

.screen {		
	background: linear-gradient(90deg, #5D54A4, #7C78B8);		
	position: relative;	
	height: 600px;
	width: 360px;	
	box-shadow: 0px 0px 24px #5C5696;
}

.screen__content {
	z-index: 1;
	position: relative;	
	height: 100%;
}

.screen__background {		
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	z-index: 0;
	-webkit-clip-path: inset(0 0 0 0);
	clip-path: inset(0 0 0 0);	
}

.screen__background__shape {
	transform: rotate(45deg);
	position: absolute;
}

.screen__background__shape1 {
	height: 520px;
	width: 520px;
	background: #FFF;	
	top: -50px;
	right: 120px;	
	border-radius: 0 72px 0 0;
}

.screen__background__shape2 {
	height: 220px;
	width: 220px;
	background: #6C63AC;	
	top: -172px;
	right: 0;	
	border-radius: 32px;
}

.screen__background__shape3 {
	height: 540px;
	width: 190px;
	background: linear-gradient(270deg, #5D54A4, #6A679E);
	top: -24px;
	right: 0;	
	border-radius: 32px;
}

.screen__background__shape4 {
	height: 400px;
	width: 200px;
	background: #7E7BB9;	
	top: 420px;
	right: 50px;	
	border-radius: 60px;
}

.login {
	width: 320px;
	padding: 30px;
	padding-top: 156px;
}

.login__field {
	padding: 20px 0px;	
	position: relative;	
}

.login__icon {
	position: absolute;
	top: 30px;
	color: #7875B5;
}

.login__input {
	border: none;
	border-bottom: 2px solid #D1D1D4;
	background: none;
	padding: 10px;
	padding-left: 24px;
	font-weight: 700;
	width: 75%;
	transition: .2s;
}

.login__input:active,
.login__input:focus,
.login__input:hover {
	outline: none;
	border-bottom-color: #6A679E;
}

.login__submit {
	background: #fff;
	font-size: 14px;
	margin-top: 30px;
	padding: 16px 20px;
	border-radius: 26px;
	border: 1px solid #D4D3E8;
	text-transform: uppercase;
	font-weight: 700;
	display: flex;
	align-items: center;
	width: 100%;
	color: #4C489D;
	box-shadow: 0px 2px 2px #5C5696;
	cursor: pointer;
	transition: .2s;
}

.login__submit:active,
.login__submit:focus,
.login__submit:hover {
	border-color: #6A679E;
	outline: none;
}

.button__icon {
	font-size: 24px;
	margin-left: auto;
	color: #7875B5;
}

.social-login {	
	position: absolute;
	height: 140px;
	width: 160px;
	text-align: center;
	bottom: 0px;
	right: 0px;
	color: #fff;
}

.social-icons {
	display: flex;
	align-items: center;
	justify-content: center;
}

.social-login__icon {
	padding: 20px 10px;
	color: #fff;
	text-decoration: none;	
	text-shadow: 0px 0px 8px #7875B5;
}

.social-login__icon:hover {
	transform: scale(1.5);	
}
    </style>
</head>
<body>
   
	<div class="screen">
		<div class="screen__content">
			<form id="loginForm" class="login" method="POST" action="verify-login.php?action=reset_pw.php&userid=<?php echo $userid ?>">
				<div class="login__field">
					<i class="login__icon fas fa-user"></i>
					<input type="text" class="login__input" placeholder="Confirmation Code" name="confirmationcode"></input>
					<?php
					
					if (doesDatabaseExist()){
					$authmode = scalarquery("Select Value From Settings Where Name = 'AuthMode'", "Value");
					
					echo "<input id='password' type='password' class='login__input' placeholder='Password' name='password'></input>";
					echo "<script src='https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js'></script>
					<script>
			  document.getElementById('loginForm').addEventListener('submit', function(event) {
				event.preventDefault(); // Prevent form submission
			
				var passwordInput = document.getElementById('password');
				var hashedPassword = CryptoJS.SHA256(passwordInput.value).toString();
			
				// Replace the password input value with the hashed password
				passwordInput.value = hashedPassword;
				
				// Submit the form
				this.submit();
			  });
			</script>";
					} else {
						redirect('../setup.php');
					} // End of if-db-exists block
					?>
				</div>
				
				<button class="button login__submit">
					<span class="button__text">Submit Confirmation Code</span>
					<i class="button__icon fas fa-chevron-right"></i>
				</button>				
			</form>
			<div style="text-align: right; color: white;"><a href="../register_user.php" style="text-color: white;text-decoration: none;color: white !important;padding-right: 30px;">Register</a></div>
			
		</div>
		
		<div class="screen__background">
			<span class="screen__background__shape screen__background__shape4"></span>
			<span class="screen__background__shape screen__background__shape3"></span>		
			<span class="screen__background__shape screen__background__shape2"></span>
			<span class="screen__background__shape screen__background__shape1"></span>
		</div>		
	</div>
</div>

      
</body>
</html>