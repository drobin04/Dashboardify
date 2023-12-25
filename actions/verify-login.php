<?php
    include_once('../shared_functions.php');
    $sessionid = "";
    $userid = "";

    // Request comes in. 
    $authmode = getAuthMode(); //record auth mode
    $userfound = false; // prep variable

    $Email = $_POST["email"];
    $password = "";
    $successful_prior_auth_cookie = "";
    $prior_auth_cookie_matches = false;
    if (isset($_COOKIE["successful_auth_for_id"])) {
        $successful_prior_auth_cookie = $_COOKIE["successful_auth_for_id"];
        
    }

    if (isset($_POST["password"])) {
        $inputpassword = $_POST["password"];
        $password = hash('sha256', $inputpassword);
        //setcookie("inputpw", $inputpassword, 2147483640, "/"); //DEBUG ONLY, DO NOT ENABLE THIS ON A LIVE SERVER FOR ANY PURPOSE
        //setcookie("hashed", $password, 2147483640, "/"); //DEBUG ONLY, DO NOT ENABLE THIS ON A LIVE SERVER FOR ANY PURPOSE
    }
    $action = "";
    if (isset($_GET["action"])) { // This gets used when passing from 'Register'. 
        // TODO - I feel like this section could be spoofed... Double check that the username doesn't exist yet, and that the email/password aren't empty

        $action = $_GET["action"];

        CreateUserIDForEmail($Email, $password);
        $userid = GetUserIDFromEmail($Email);
        if ($successful_prior_auth_cookie == hash('sha256', $userid)) {
            // COOKIE MATCHED FOR PRIOR SUCCESSFUL AUTHENTICATION;
            // WE CAN GRANT A VERY SMALL AMOUNT OF TRUST
            // THUS WE CAN DECIDE NOT TO RATE LIMIT FAILED LOGINS AS HARD FOR THIS CLIENT
            $prior_auth_cookie_matches = true;
        }
        complete_login($userid);


    } else { // Try to log in for existing user
        if (scalarquery("Select Count(*) as matches from users where Email = '" . $Email . "'", "matches") == 1) {
            // If we find a match for username in system
            $userid = GetUserIDFromEmail($Email); // get the user ID.
            debuglog("Results of GetUserIDFromEmail: " . $userid);
    
            If (is_scalar($userid)) {
                $userfound = true; // found match for username
                // See if pw matches
                authenticate($Email, $userid, $password, $userfound); 
            }
            // else {
            //     if ($authmode == "None") {
            //             // Create User record, re-run statement
            //             complete_login($userid);

                    
                
            //     } else {
            //         // failed to authenticate!
            //         failed_auth();
            //     }
    
    
            // }
        } else {
                // Create User record, re-run statement
                debuglog("User ID Not Found, Creating one.");
                CreateUserIDForEmail($Email, $password);
                $userid = GetUserIDFromEmail($Email);
                complete_login($userid);
            
        }
    }
exit();
    
function failed_auth() {
    redirect('start-login.php?msg=Failed_Auth_On_Verification');
    // Log failed login attempt in DB

    // Check if we've exceeded number of login attempts needed for lockout

    // if so, mark account for lockout for x minutes (We should make this configurable in settings!)

}






    function GetUserIDFromEmail($eml) {
        $select = "SELECT RecID FROM Users Where Email = '" . $eml . "'";
        debuglog($select,"SQL Query Searching For User By Email");
        $results = selectquery($select);
        debuglog($results,"Results from SQL Query search");
        $user = $results[0]["RecID"];
        debuglog($user,"User info selected");
        return $user;
    }
    
    function CreateUserIDForEmail($eml, $pwd) {
        $select = "Insert Into Users (Email, password) VALUES (?,?)";
        
        $db = getPDO_DBFile();
        $stmt = $db->prepare($select);
        $stmt->bindParam(1,$eml,PDO::PARAM_STR);
        $stmt->bindParam(2,$pwd,PDO::PARAM_STR);
        $stmt->execute();

        //execquery($select);
    }

    function CreateSessionForID($id, $sessid) {
        $insert = "Insert Into Sessions (UserID, SessionID) VALUES ('". $id . "', '" . $sessid . "')";
        execquery($insert);
    }   

    
    
    //Create new session for user, IF password matches OR if new user. 
    function authenticate($eml, $userid, $password, $userfound) {
        if (getAuthMode() == "None") {
            complete_login($userid);
        } elseif (getAuthMode() == "Password") {
            if ($userfound) {
                $userpw = scalarquery("Select Password From Users Where Email = '" . $eml . "'", "password");
                if ($userpw == $password) {
                    complete_login($userid);
                } else {
                    failed_auth();
                    
                }
            }



        }
    }

    function complete_login($uid) {
            $sessionid = GUID();
            CreateSessionForID($uid, $sessionid);
            setcookie("SessionID", $sessionid, 2147483640, "/"); //Save session ID into cookie
            // HASH THE USERID BEFORE SENDING IT OUT - HASH IS IMPORTANT SO WE DONT REVEAL THE USERID TO THE END USER.
            $hasheduid = hash('sha256',$uid);
            setcookie("successful_auth_for_id", $hasheduid, 2147483640, "/");
            // To rate limit logins for users that haven't signed in before, 
            // We gonna set a cookie holding a hash of the last successfully auth'd username. 

            //setcookie("succeeded_login_hash", )
            
            //
            
            redirect("../index.php");

        }
?>