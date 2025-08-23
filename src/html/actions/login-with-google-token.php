<?php
include_once("../shared_functions.php");
// Check if the cookie exists
if (isset($_COOKIE['g_idtoken'])) {
    // Read the cookie value into a variable
    $id_token = $_COOKIE['g_idtoken'];

    // Display the ID token
    echo "ID Token: " . htmlspecialchars($id_token);
    
    // 1. See if DB exists.
    
    // 2. Check if user is already present in users table
    // Select Email From Users Where Email = ''
    $sql1 = "Select Count(Email) As Number From Users Where Email = '" . $id_token . "'";
    $UserAlreadyExistsBool = scalarquery($sql1, "Number");
    if ($UserAlreadyExistsBool == "1") {
    	// We do nothing
    	echo "<br />User already exists!";
    }
    if ($UserAlreadyExistsBool == "0") {
    	// We create the user. 
		echo "<br />User does not exist yet!";
    }
    
    // 3. If user not present, create them.
    
    // 4. After user is verified or created, redirect to the main page.
    
    
    
    
    
} else {
    // Display a message if the cookie does not exist
    echo "Cookie 'g_idtoken' not found.";
}

?>