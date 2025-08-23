<?php



?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">
<head >
    <title>Dashboardify - Change Password</title>
	
    <style>
body {
    background-color: lightblue;
    }
.MainPanel {
	max-width: 800px !important;
	margin: 40px auto;
	border-color: black;
	border: 1px solid black;
	background-color: white;
	padding: 30px;
	border-radius: 25px;
	overflow: auto;
	height: auto;
	width: auto;
}
        

.MainPanel input, .MainPanel textarea, .MainPanel select {
	border-left-width: inherit;
}
    </style>
</head>
<body>
		<div class="MainPanel">
			<form id="loginForm" class="login" method="POST" action="capture_new_password_for_user.php">
				<div class="login__field">
					<input id='password' type='password' class='login__input' placeholder='Password' name='password'></input>
					<script src='https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js'></script>
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
					</script>
				</div>
				
				<button class="button login__submit">Submit New Password</button>				
			</form>			
		</div>
	</div>      
</body>
</html>
