<?php
//to-do - Fix bug where it seems like people entering user/pw for account doesn't exist are getting logged in. It's acting as if 'none' auth is still allowed,
// even when it's not.

    include_once('../shared_functions.php');
    $sessionid = "";
    $userid = "";

    // Request comes in. 
    $authmode = getAuthMode(); //record auth mode
    $userfound = false; // prep variable

    $password = "";
    $successful_prior_auth_cookie = "";
    $prior_auth_cookie_matches = false;
    $Email = "";
    
    if (isset($_POST["email"])) {
    $Email = $_POST["email"];
    }
    
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
        
        switch ($action) {
        	
        case "register":
        	// Need to check if user already exists before creating new user account. 
        	if (!DoesUserExist($Email)) {
        		CreateUserIDForEmail($Email, $password);
			$userid = GetUserIDFromEmail($Email);
			if ($successful_prior_auth_cookie == hash('sha256', $userid)) {
				// COOKIE MATCHED FOR PRIOR SUCCESSFUL AUTHENTICATION;
				// WE CAN GRANT A VERY SMALL AMOUNT OF TRUST
				// THUS WE CAN DECIDE NOT TO RATE LIMIT FAILED LOGINS AS HARD FOR THIS CLIENT
				$prior_auth_cookie_matches = true;
				}
				
				// Check if system setting for RequireConfirmationCode is set, and if so, if it's set to 1 indicating that users should b required
				// if set, do redirect. 
				// if not set or is set to 0, just complete login from this point. 
				
				$setting_for_requiredconfirmationcode = scalarquery("select Value from Settings Where Name = 'RequireConfirmationCode'", "Value");
				
				if ($setting_for_requiredconfirmationcode == "1") {
				// Confirmation code is required then				
					
				//Generate code for confirmation
				$code = mt_rand(100000, 999999);
				
				
				// Store confirmation code for user
				execquery_bind1("Update Users
				Set ConfirmationCode = '" . $code . "' 
				Where Email = ?", $Email);
				
				// Mail confirmationcode to user!
				include("../mailer.php");
				mailAuthenticationCode($Email,$code);
				
				
				
				//enforce confirmation code requirement, redirect to conf code page
				registration_login_and_forward_for_email_confirmation($userid);
				} else {
				//Confirmation code is not required, so,
				//Complete login
				complete_login($userid);
				} // end of 'if require confirmation code setting = true' block
        	}// end of check whether email already exists / skip past if not true
			else {
			// email already exists
			echo "User is already registered.";
			}
			break;
			
		case "reset_pw.php";
			// Get confirmation code
			$conf_code_for_reset_pw = "";
			
			if (isset($_POST["confirmationcode"])) {
				$conf_code_for_reset_pw = $_POST["confirmationcode"];
				$user_id_for_reset_pw = $_GET["userid"];
				
				$properconfcodeforuser = scalarquery("Select ConfirmationCode From Users Where RecID = '" . $user_id_for_reset_pw . "'", "ConfirmationCode");
				if ($properconfcodeforuser == $conf_code_for_reset_pw) {
				// Save into DB
				$sql = "update Users Set ConfirmationCode = '', Password = ? where RecID = ?";
				$localdb = getPDO_DBFile();
				$stmt1 = $localdb->prepare($select);
				$stmt1->bindParam(1, $password, PDO::PARAM_STR);
				$stmt1->bindParam(2, $user_id_for_reset_pw, PDO::PARAM_STR);
				$stmt1->execute();
				
				
					redirect("start-login.php");
				} else {
					//failed. 
				 redirect("start-login.php?msg=Failed_Confirmation_code");
				 
				}
				
				
				
			}
			
		
			break;
		case "confirm_email_via_code":
			$confirmationcode = $_POST["confirmationcode"];
			// get user ID from current session,
			// assumption being someone logged in on this page once before, a session ID may have been captured,
			// and they were directed to the confirmation code screen before being forwarded here.
			// Need to validate that the confirmation code matches
			// if it matches, mark their user account as confirmation code being validated, remove the existing confirmation code value to '',
			// and then proceed to login / index.php
			$userid = getCurrentUserID();
			$confCodeForThisUser = scalarquery("Select ConfirmationCode From Users Where RecID = '" . $userid . "'", "ConfirmationCode");
			
			if ($confirmationcode == $confCodeForThisUser) {
				//Update user account
				// Mark emailconf confirmed
				// remove confirmationcode value
				// THIS EXECQUERY IS SAFE, NO USER SUPPLIED CONTENT MAKES IT INTO THE QUERY.
				execquery("Update Users 
					Set EmailConfirmed = 1, ConfirmationCode = '' 
					where RecID = '" . $userid . "'");
				
				redirect("start-login.php?msg=Confirmation_Code_Confirmed_Log_in_Now");
				
				
			} else {
				echo "Confirmation code didn't match. You entered: " . $confirmationcode . "Real code was: " . $confCodeForThisUser . ". Recid: " . $userid ;
				redirect("confirm-email.php");
			
			}
			
			break;
		case "reset_password":
			$email = $_POST["username"];
			
			break;
		}
	
    } else { // Action not sent via URL, this is a normal authentication, so Try to log in for existing user
        if (scalarquery_bind1("Select Count(*) as matches from users where Email = ?",$Email, "matches") == 1) { // existing user found
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
        } else { // User not found. 
        		// IF 'None' authentication is enabled, then create user account, this is intended for a dev use case (Deprecated, may remove later).
        		// Otherwise, fail auth attempt. 
                // Create User record, re-run statement
                if ($authmode == "None") {
                debuglog("User ID Not Found, Creating one.");
                CreateUserIDForEmail($Email, $password);
                $userid = GetUserIDFromEmail($Email);
                complete_login($userid);
                } else {
                	failed_auth();
                }
        }
    }
exit();
    
function failed_auth() {
    redirect('start-login.php?msg=Failed_Auth_On_Verification');
    $Email = "";
    
    
    // Log failed login attempt in DB
    logLoginAttempt(0);


    // Check if we've exceeded number of login attempts needed for lockout

    // if so, mark account for lockout for x minutes (We should make this configurable in settings!)

}

    function logLoginAttempt($successful) {
		if (isset($_POST["email"])) {
			$Email = $_POST["email"];
		}
		$dbfile = getPDO_DBFile();
		$sql = "INSERT INTO login_attempts (dt,Email,Successful) VALUES (?,?,?)";    
		
		$stmt1 = $dbfile->prepare($sql);
		$date = date('Y-m-d H:i:s');
		$stmt1->bindParam(1,$date,PDO::PARAM_STR);
		$stmt1->bindParam(2,$Email,PDO::PARAM_STR);
		$stmt1->bindParam(3,$successful,PDO::PARAM_BOOL);
		$stmt1->execute();	
    	
    }
    function CreateUserIDForEmail($eml, $pwd) {
        $select = "Insert Into Users (Email, password) VALUES (?,?)";
        
        $db = getPDO_DBFile();
        $stmt = $db->prepare($select);
        $stmt->bindParam(1,$eml,PDO::PARAM_STR);
        $stmt->bindParam(2,$pwd,PDO::PARAM_STR);
        $stmt->execute();

    }

    function CreateSessionForID($id, $sessid) {
        $insert = "Insert Into Sessions (UserID, SessionID) VALUES (?, ?)";
        
        $localdb = getPDO_DBFile();
		$stmt1 = $localdb->prepare($insert);
		$stmt1->bindParam(1, $id, PDO::PARAM_STR);
		$stmt1->bindParam(2, $sessid, PDO::PARAM_STR);
		$stmt1->execute();
        
    }   

    
    
    //Create new session for user, IF password matches OR if new user. 
    function authenticate($eml, $userid, $password, $userfound) {
        if (getAuthMode() == "None") {
            complete_login($userid);
        } elseif (getAuthMode() == "Password") {
            if ($userfound) {
                $userpw = scalarquery_bind1("Select Password From Users Where Email = ?",$eml, "password");
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
			SendCookieToUser($sessionid); //Save session ID into cookie
            // HASH THE USERID BEFORE SENDING IT OUT - HASH IS IMPORTANT SO WE DONT REVEAL THE USERID TO THE END USER.
            $hasheduid = hash('sha256',$uid);
            setcookie("successful_auth_for_id", $hasheduid, 2147483640, "/");
            // To rate limit logins for users that haven't signed in before, 
            // We gonna set a cookie holding a hash of the last successfully auth'd username. 

            //setcookie("succeeded_login_hash", )
            
            //
            logLoginAttempt(1);
            redirect("../index.php");

        }
   function registration_login_and_forward_for_email_confirmation($uid) {
            $sessionid = GUID();
            CreateSessionForID($uid, $sessionid);
            SendCookieToUser($sessionid); //Save session ID into cookie
            // HASH THE USERID BEFORE SENDING IT OUT - HASH IS IMPORTANT SO WE DONT REVEAL THE USERID TO THE END USER.
                        
            redirect("confirm-email.php");

   }
   
   function SendCookieToUser($sessionid) {
   	   //First, need to get setting from database on what the appropriate session length should be for cookie expiration
   	   $sessionlength = scalarquery("Select Value From Settings Where Name = 'sessionlength'", "Value");
   	   //Initialize variable for session length, start with infinite
   	   $cookie_expiry = 2147483640;
   	   
   	   switch ($sessionlength) {
   	   	case "30 Days":
   	   		$cookie_expiry = time() + (30 * 24 * 60 * 60); // 30 days in seconds
   	   		break;
   	   	case "7 Days":
			$cookie_expiry = time() + (7 * 24 * 60 * 60); // 30 days in seconds
			break;
		case "Infinite":
			$cookie_expiry = 2147483640;
			break;
		default:
			$cookie_expiry = 2147483640;
			break;
   	   }
   	   
   	   
   	   
   	   setcookie("SessionID", $sessionid, $cookie_expiry, "/");
   }
?>
