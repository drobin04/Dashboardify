<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">
<head >
	<script src="https://accounts.google.com/gsi/client" async defer></script>
	<meta name="google-signin-client_id" content="814465180043-ir2l2aejp965j0eug05kfi51clid8f7a.apps.googleusercontent.com">
    <title>Dashboardify</title>
	<link rel="stylesheet" href="../css/start-login.css">
    <style>
    	
    </style>
    <script>
    	function handleCredentialResponse(response) {
    		// Extract the ID token from the response
		    const id_token = response.credential;
	
		     // Set the cookie with the ID token
			document.cookie = "g_idtoken=" + encodeURIComponent(id_token) + "; path=/";
		
			// Redirect to the new PHP page
			window.location.href = "verify-login.php?action=login-with-google";
		}
    </script>
</head>
<body>
   
	<div class="screen">
		<div class="screen__content">
			<form id="loginForm" class="login" method="POST" action="verify-login.php">
				<div class="login__field">
					<i class="login__icon fas fa-user"></i>
					<input type="text" class="login__input" placeholder="Email / Username" name="email"></input>
					<?php
					include('../shared_functions.php');
					if (doesDatabaseExist()){
					$authmode = scalarquery("Select Value From Settings Where Name = 'AuthMode'", "Value");
					if ($authmode == "Password") {
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
			</script>";}
					} else {
						redirect('../setup.php');
					} // End of if-db-exists block
					?>
				</div>
				
				<button class="button login__submit">
					<span class="button__text">Log In Now</span>
					<i class="button__icon fas fa-chevron-right"></i>
				</button>				
			</form>
			<div style="text-align: right; color: white;"><a href="reset_password.php" style="text-color: white;text-decoration: none;color: white !important;padding-right: 30px;">Reset Password</a></div>
			<div style="text-align: right; color: white;"><a href="../register_user.php" style="text-color: white;text-decoration: none;color: white !important;padding-right: 30px;">Register</a></div>
			<br />
			<div id="g_id_onload"
				data-client_id="814465180043-ir2l2aejp965j0eug05kfi51clid8f7a.apps.googleusercontent.com"
				data-callback="handleCredentialResponse">
			</div>
				<div class="g_id_signin" data-type="standard"></div>
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
