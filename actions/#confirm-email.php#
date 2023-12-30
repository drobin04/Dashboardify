<?php

//PURPOSE

// User should be logged in when reaching this screen

// Check if they're logged in, and if not, redirect them back to the start login screen with a url string of ?Not logged in

// When saving this screen, we can check if the code matches what's entered v in database, and can 

// todo -






    include_once('../shared_functions.php');
    
    $sessionid = "";
    $userid = "";

    // Request comes in. 
    //$authmode = getAuthMode(); //record auth mode
    $userfound = false; // prep variable

    //$Email = $_POST["email"];
    //$password = "";
    $successful_prior_auth_cookie = "";
    $prior_auth_cookie_matches = false;
    if (isset($_COOKIE["successful_auth_for_id"])) {
        $successful_prior_auth_cookie = $_COOKIE["successful_auth_for_id"];
        
    }

    

    
function failed_auth() {
    redirect('start-login.php?msg=Failed_Auth_On_Verification');
    // Log failed login attempt in DB

    // Check if we've exceeded number of login attempts needed for lockout

    // if so, mark account for lockout for x minutes (We should make this configurable in settings!)

}

?>