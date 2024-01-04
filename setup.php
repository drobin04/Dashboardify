<?php
include_once('config/check_admin.php');
include_once('shared_functions.php');
include('actions/logoutredirect.php');
?>
<html>
    <head>
        <link type="text/css" rel="stylesheet" href="css/settings_page.css">
        <style>
            #siteurlconfig {}
            #SQLUpdate {width: 100%;}
            .TableResults, .TableResults tr, .TableResults th, .TableResults td {border: 1px black solid; text-align: center;}
        
            .custom_widget_providers textarea, .custom_widget_providers input {min-width: 500px;}
            
            
            .tab {
            	border: 1px solid black;	
            	padding-left: 10px;
            	display: none;
            }
            
            .tabPanel {
            	border: 1px solid black;	
            }
            
            .tabs_nav a {
            	/* margin: 5px; */
            	padding: 5px;
            	border: 1px solid black;

            }
            .tabs_nav a:hover {
            	background-color: yellow;	
            }
            .tabs_row {
            	padding-bottom: 5px;	
            }
            
            div:target {display:block;}
            
        </style>
        <!-- Google Config Start -->
        <script src="https://apis.google.com/js/platform.js" async defer></script>
        <meta name="google-signin-client_id" content="814465180043-ir2l2aejp965j0eug05kfi51clid8f7a.apps.googleusercontent.com">
        <!-- Google Config End ^^^ For Google signin for Email Configuration. Yuck, I know. Google. I don't know why people use it. -->
        </head>
    <body>
<!-- Everything should be below MainPanel for now -->
        <div class="MainPanel">
        
        
        	<h1> Setup / Admin page for Dashboardify</h1>

			<a href="index.php">Click Here To Return To Dashboardify</a>
    
			<!--Beginning of initial PHP setup Segment -->
			<?php
				$css = "";
				$urlvalue = "";
				$widgetproviderlist = "";
				
				if (isset($_GET["action"])) {
				
					$action = $_GET["action"];
					//echo "Action to run: " . $action ;
					switch ($action) {
						case "SubmitWidgetProvider":	
							$d = $_POST;
				
							// Get data from submit widgetprovider
							$widgetprovidername = $d["WidgetProviderName"];
							$widgetprovidercss = $d["WidgetProviderCSS"];
							$widgetproviderhtml = $d["WidgetProviderHTML"];
							$widgetproviderphp = $d["WidgetProviderPHP"];
							
							$select = "INSERT INTO CustomWidgetProviders (WidgetProviderName
							,CSS_Styling, HTML_Content
							,PHP_To_Run) VALUES (?, ?, ?, ?)";
							$localdb = getPDO_DBFile();
							//$rootPath = $_SERVER['DOCUMENT_ROOT'];
							//$localdb = new PDO('sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db');
							$stmt = $localdb->prepare($select);
							$stmt->bindParam(1,$widgetprovidername,PDO::PARAM_STR);
							$stmt->bindParam(2,$widgetprovidercss,PDO::PARAM_STR);
							$stmt->bindParam(3,$widgetproviderhtml,PDO::PARAM_STR);
							$stmt->bindParam(4,$widgetproviderphp,PDO::PARAM_STR);
							debuglog($stmt);
							
							$stmt->execute();
							echo "Widget Provider Registered Successfully.";
							break;
						case "UpdateNewUserSettings":
							$a_AuthType = $_POST["AuthType"];
							
							$settings_require_confirmation_code = '0';
							if (isset($_POST["requireconfirmationcode"])) {$settings_require_confirmation_code = '1';};
							if ($settings_require_confirmation_code == 1) {
								execquery("Delete From Settings Where Name = 'RequireConfirmationCode'");
								execquery("INSERT INTO Settings (Name,Value) VALUES ('RequireConfirmationCode', '1')");
							} else {
								execquery("Delete From Settings Where Name = 'RequireConfirmationCode'");
								execquery("INSERT INTO Settings (Name,Value) VALUES ('RequireConfirmationCode', '0')");    			
							}
				
							$select = "Update Settings
							
							Set Value = ?
							
							Where Name = 'AuthMode'";
					
							$localdb = getPDO_DBFile();
							//$rootPath = $_SERVER['DOCUMENT_ROOT'];
							//$localdb = new PDO('sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db');
							$stmt = $localdb->prepare($select);
							$stmt->bindParam(1,$a_AuthType,PDO::PARAM_STR);
							debuglog($stmt, "AuthMode query");
							
							$stmt->execute();
					
									
							$defaultdashimg = $_POST["defaultdashimage"];
					
							// Open the file in write mode
							$file = fopen('defaultdashboardurl.txt', 'w');
					
							// Write the value to the file
							fwrite($file, $defaultdashimg);
					
							// Close the file
							fclose($file);
							//redirect('../setup.php');
							//echo "Settings Registered Successfully.";
							break;
						case "testEmail":
							// include mailfunctions / mailer
							// I think the mailer.php will load the settings and send the mail on its own,
							// i just need to feed it currentuser email and a random confirmation code
							include('mailer.php');
							$email = getCurrentUserEmail();
							$confirmationcode = '000000';
							echo "Ran test email.";
							mailAuthenticationCode($email, $confirmationcode);
							break;
					}
				
				
				
				}
				
				$filename = 'Dashboardify.s3db';
				$Exists = False;
				If (doesDatabaseExist() && filesize($filename) > 0) {
					$Exists = True;
					if (!AmIAdmin()) { redirect('index.php'); } // Redirect back to index if they don't have permissions to be here.
				
					$dbfound = "<div style='border: 1px black solid; display: inline; padding: 3px;'>Database file found. No Need To Create.</div> ";  // DB Found! Yay.
					
					if (scalarquery("Select Count(*) as Matches From Settings Where Name = 'SiteUrlConfig'", "Matches") == 0) {
						$urlvalue = "";
					} else {
						$urlvalue = scalarquery("Select Value From Settings Where Name = 'SiteUrlConfig'", "Value");
					
					}
				} else {
					// Run this if db doesn't exist yet!!
					$dbfound = "The database file is either missing or has not been created yet. <br/>
					Would you like to try creating the database now? <br/>
					<a href='createDB.php'>Yes - Create the Dashboardify database.</a>";
				
					$urlvalue = ""; // Blank out SiteURLConfig as it's referenced from DB.
				
				
				
				}
				
				if (file_exists('config/defaultdashboardurl.txt')) {
					$defaultdashimage = file_get_contents('config/defaultdashboardurl.txt');
				} else { $defaultdashimage = ""; }
				if (file_exists('config/globalcss.css')) {
					$css = file_get_contents('config/globalcss.css');
				}
				
				if (isset($_GET["SQLUpdate"])) {
					$sqlqueryresults = "";
				
					$sql = $_GET["SQLUpdate"];
					$operationtype = $_GET["SQLOperationType"];
				
					if ($operationtype == "Exec") {
						execquery($sql);
						$sqlqueryresults = "Query Executed.";
					} elseif ($operationtype == "Select") {
						$results = selectquery($sql);
						$sqlqueryresults = generateTableFromObjects($results);
					}
				
				
				
				} else { $sqlqueryresults = "";}
				
				//Loading page... set remaining variables for form
				
				$form_require_confirmation_code_state = "";
				if (scalarquery("Select Value From Settings Where Name = 'RequireConfirmationCode'", "Value") == "1") {
					$form_require_confirmation_code_state = "checked";
				}
			
			?> <!--End of initial PHP setup Segment -->
			
			<br />
			<br />
			
			<!-- Tab Links -->
			<br />
			<h4>Settings Panels (Click to View)</h4>
			<div class="tabs_nav">
				<div class="tabs_row">
					<a href="#siteurlconfig">Site URL Config</a>
					<a href="#createdeletedbconfig">Create / Delete DB</a>
					<a href="#GlobalCSSConfig">Global CSS Config</a>
					<a href="#usersmanagement">Users Management</a>
				</div>
				<br />
				<div class="tabs_row">
					<a href="#NewUserSetupExperience">New User Setup Experience</a>
					<a href="#ManualSQLUpdates">Manual SQL Updates</a>
					<a href="#CustomWidgetProviders">Custom Widget Providers</a>
					
				</div>
				<br />
				<div class="tabs_row">
					<a href="#EmailConfig">Email Config</a>
					<a href="#UpdateSiteCodeFromGithub">Update Site Code From Github</a>
				</div>
			</div>
			<br />
			<!-- Tab Links -->
			
			
			<div class="tabPanel"><!-- Beginning of Tabbed Panel -->
        
        
        
				<div class="tab" id="siteurlconfig"> <!-- start of siteurlconfig tab -->
					<h2> Site URL config (required)</h2>
					<p>There are some items that will need to reference the base URL for this webpage. 
					<br/>Please configure the box below with the site URL, in the format of ' https://example.com/this_site_directory/'
					</p>
					<form method="POST" action="config/storesiteurlconfig.php">
						<input id="siteurlconfig" name="siteurlconfig" value="<?php echo $urlvalue ?>" ></input><button>Submit</button>
					</form>
				</div> <!-- end of siteurlconfig tab -->
			
				
				<div class="tab" id="createdeletedbconfig"> <!-- Start of Create / Delete DB Section -->
					<h2>Create/Delete Database</h2>
					<?php echo $dbfound ?> <br />
					
					<p>WARNING: There is no confirmation after clicking this link!!!
					<a href="delete-dashboardify-db.php">Delete Dashboardify DB</a></p>
				</div><!-- end of Create/Delete DB Section -->
				
				
				<div class="tab" id="GlobalCSSConfig"><!-- start of Global DB CSS Section -->
					<h2> Global Dashboard CSS - Default For All Users</h2>
					
					<p>This is the default CSS that will be loaded for everyone's dashboards, underneath any user-supplied custom CSS for their dashboards. 
					</p>
					
					<form action="config/savecss.php">
						<textarea cols="50" rows="5" name="CSS"><?php echo $css ?></textarea><br />
						<button>Submit</button>
					</form>
				</div><!-- end of Global DB CSS Section -->
				
				<div class="tab" id="usersmanagement"><!-- start of Users Management Section -->
					<div id="userstable">
						<h2>Users</h2>
					
						<table>
						<tr><th>RecID</th><th>Username/Email</th><th>Admin</th><th>Actions</th></tr>
						<?php
						if ($Exists) {
						$userslist = selectquery("Select RecID, Email, Admin FROM Users");
						}
						if (isset($userslist)) {
							foreach ($userslist as $user) {
								if (($user["Admin"] != "1") Or ($user["Admin"] = "")) {
									$user["Admin"] = "N";
								}
								else { $user["Admin"] = "Y";}
								echo "<tr><td>" . $user["RecID"] . "</td><td>" . $user["Email"] . "</td><td>" . $user["Admin"] . "</td><td><a href='config/delete_user.php?recID=" . $user["RecID"] 
								. "'>Delete?</a> " . "<a href='config/make_admin.php?recID=" . $user["RecID"] 
								. "'>Make Admin?</a> <a href='config/remove_admin.php?recID=" . $user["RecID"] . "'>Remove Admin?</a></tr>";
							
					
							}
							
							//echo "<br /><br />" . generateTableFromObjects($userslist);
						}
					
						?>
						</table>
					</div>
					<br />
				</div><!-- end of Users Management Section -->
				
				<div class="tab" id="NewUserSetupExperience">
					<br />
					<!-- New User Setup Experience -->
					<div id="newuserexperience">
						<form id="NewUserSettings" method="POST" action="setup.php?action=UpdateNewUserSettings">
						<h2>New User Experience</h3>
						<p>Below are settings affecting new users on the system.</p>
						<label>Authentication Type: </label>
						<?php
						if ($Exists) {
						$authmode = scalarquery("Select Value From Settings Where Name = 'AuthMode'", "Value");
						} else {$authmode = "None";}
						?>
						<select ID="ddlAuthType" name="AuthType">
							<option value='<?php echo $authmode?>' selected='selected'><?php echo $authmode?></option><!--default value in line above-->
							<option value="Password">Password</option>
							<option value="None">None</option>
						</select><br />
					
						<label>Default Dashboard Background Image For First Dashboard: </label>
						<input id='defaultdashimage' name='defaultdashimage' value='<?php echo $defaultdashimage ?>'></input>
						<br />
						<br />
						<input type='checkbox' <?php echo $form_require_confirmation_code_state ?> name='requireconfirmationcode' id='requireconfirmationcode'></input><label for='requireconfirmationcode'>Should new users be required to confirm their email addresses when registering?</label>
						<br /><br />
						<button>Submit</button>
					
					</form>
					</div>
				</div>
				
				<div class="tab" id="ManualSQLUpdates">
					<div id="SQLUpdates">
						<form action="setup.php">
							<h2>Manual SQL Updates</h2>
					
							<input type="radio" id="exec" name="SQLOperationType" value="Exec">
							<label for="exec">Execute (No Results)</label><br>
							<input type="radio" id="select" name="SQLOperationType" value="Select">
							<label for="select">Select (With Results)</label><br>
							<br />
							<textarea id="SQLUpdate" name="SQLUpdate"></textarea><br />
							<button>Submit</button>
						</form>
					
					
						<?php echo $sqlqueryresults ?>
					</div>
				</div>
				
				<div class="tab" id="CustomWidgetProviders">
					<div id="CustomWidgetProviders" class="custom_widget_providers">
						<h2>Custom Widget Providers</h2>
						<!-- Table of results here -->
						<?php echo $widgetproviderlist ?>
					
						<form id="submitNewWidgetProvider" method="POST" action="setup.php?action=SubmitWidgetProvider">
						<label>Name for Custom Widget Provider: </label><br /><input id="WidgetProviderName" name="WidgetProviderName"><br />
						<label>CSS Styling For Widget:</label><br /><textarea id="CSS_For_Widget_Provider" name="WidgetProviderCSS"></textarea><br />
						<label>HTML Content To Display In Widget:</label><br /><textarea id="HTML_Content_For_Widget_Provider" name="WidgetProviderHTML"></textarea><br />
						<label>PHP To Run Before Loading Widget (To populate variables, if needed):</label><br /><textarea id="PHP_To_Run_For_Widget_Provider" name="WidgetProviderPHP"></textarea><br />
						<button>Submit</button>
						<!-- Note on how this should work
					PHP code gets run BEFORE HTML content gets placed on the PHP side, 
					That way PHP statements can be run that populate variables that will then be placed into the HTML content.
					
					Also i want to find a way to embed the Widget data , raw, somewhere into the content of the widget, so that it might be accessed by javascript or something.
					
					-->
						</form>
						<h3>
					
					</div>
				</div>
				
				<div class="tab" id="EmailConfig">
					<!--Email Config-->
					<div>
						<form id="mail" method="POST" action="config/storemailcreds.php">
							<div style="">
								<h3>Email Config</h3>
								<p>This section configures the mail account used, and if it should be used to lock new user accounts until they submit an email confirmation code.
								The purpose of this is to prevent bots from creating accounts and attempting to spam various functions on the site. </p>
							</div>
							<div style="">
							<label>Username</label><br />
							<input id ="username" name="username"></input>
							<br />
							<div>
								<label>Password</label><br />
								<input id="pw" type="password" name="password"></input><br />
								<label>SMTP Security Method</label><br />
								<input id="smtpSecureMethod" name="smtpSecure"></input><br />
								<label>Port</label><br />
								<input id="port" name="smtpPort"></input><br />
							</div>
							
							<button>Submit</button>
							</form>
							<br /><br />
							<form id="testemail" method="POST" action="setup.php?action=testEmail">
							<button>Test Email (Send to myself)</button>
						</form>
						<!-- GOOGLE SIGNIN BUTTON -->
						<br />
						<br />
						<div class="g-signin2" 
						   data-onsuccess="onSignIn"
						   data-scope="https://www.googleapis.com/auth/plus.login"
						   data-accesstype="offline"
						   data-redirecturi="https://dashboardify.app/Dashboardify/storegoogleconfig.php">
					   </div>
						<!-- END OF GOOGLE SIGNIN BUTTON -->

					</div>
					<!--END OF EMAIL CONFIG-->
					
				
				</div>
				</div>
				
				<div class="tab" id="UpdateSiteCodeFromGithub">
					
					<div style="">
						
						<h3>Update Code</h3>
						<p>The following button will allow you to update your app's code from the main Dashboardify repo.
						Backup your files first!! 
						It's possible that changes may have occurred in newer versions of the repo, which expect database columns which might not exist yet on your version, and which would cause problems. 
						
						Future update planned to support checking DB for missing pieces.</p>
						<br />
						<a href="clone.php">Update Site From Dashboardify Repo</a>
					</div><!--End of Update Code section-->
					<br />
				</div><!-- End of Update Code Tab -->				
				
				
			</div><!-- end of Tabbed Panel-->



	
</div><!-- End of Main Panel -->
    </body>

</html>
