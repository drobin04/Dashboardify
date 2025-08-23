<?php

if (!extension_loaded('sqlite3')) {
    echo "The SQLite3 extension is missing. <br /> Please install it and then return here. <br /> If you're on an Ubuntu-based server, you can run ' sudo apt install php-sqlite3 '.";
    exit();
} else {
    // SQLite extension is installed
}

include_once('config/check_admin.php');
include_once('shared_functions.php');
include('actions/logoutredirect.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Restore settings variable logic ---
$css = "";
$urlvalue = "";
$widgetproviderlist = "";
$sessionlength = "";
$authmode = "None";
$defaultdashimage = "";
$form_require_confirmation_code_state = "";
$sqlqueryresults = "";
$Exists = false;
$dbfound = "";

$filename = 'Dashboardify.s3db';
if (doesDatabaseExist() && filesize($filename) > 0) {
    $Exists = true;
    if (!AmIAdmin()) { redirect('index.php'); }
    $dbfound = "<div style='border: 1px black solid; display: inline; padding: 3px;'>Database file found. No Need To Create.</div>";
    if (scalarquery("Select Count(*) as Matches From Settings Where Name = 'SiteUrlConfig'", "Matches") == 0) {
        $urlvalue = "";
    } else {
        $urlvalue = scalarquery("Select Value From Settings Where Name = 'SiteUrlConfig'", "Value");
    }
    if (scalarquery("Select count(*) as countval from settings where Name = 'sessionlength'", "countval") == 0) {
        $sessionlength = "Infinite";
        execquery_bind1("Insert Into settings (Name, Value) Values ('sessionlength', ?)", $sessionlength);
    } else {
        $sessionlength = scalarquery("select Value from settings where Name = 'sessionlength'", "Value");
    }
    $form_require_confirmation_code_state = "";
    if (scalarquery("Select Value From Settings Where Name = 'RequireConfirmationCode'", "Value") == "1") {
        $form_require_confirmation_code_state = "checked";
    }
} else {
    $dbfound = "The database file is either missing or has not been created yet. <br/>
    Would you like to try creating the database now? <br/>
    <a href='createDB.php'>Yes - Create the Dashboardify database.</a>";
    $urlvalue = "";
}
if (file_exists('config/defaultdashboardurl.txt')) {
    $defaultdashimage = file_get_contents('config/defaultdashboardurl.txt');
} else { $defaultdashimage = ""; }
if (file_exists('config/globalcss.css')) {
    $css = file_get_contents('config/globalcss.css');
}
// --- End settings variable logic ---
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <link type="text/css" rel="stylesheet" href="css/settings_page.css">
        <style>
            body { background: #f8f9fa; }
            .sidebar { min-height: 100vh; border-right: 1px solid #dee2e6; }
            .settings-panel { display: none; }
            .settings-panel.active { display: block; }
            .sidebar .nav-link.active { background: #e9ecef; font-weight: bold; }
            .sidebar .nav-link { color: #333; }
            .sidebar .nav-link:hover { background: #f1f3f5; }
            .main-content { padding-top: 2rem; }
        </style>
        <!-- Google Config Start -->
        <!--<script src="https://apis.google.com/js/platform.js" async defer></script>-->
        <meta name="google-signin-client_id" content="814465180043-ir2l2aejp965j0eug05kfi51clid8f7a.apps.googleusercontent.com">
        <!-- Google Config End ^^^ For Google signin for Email Configuration. Yuck, I know. Google. I don't know why people use it. -->
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <nav class="col-md-2 d-none d-md-block bg-light sidebar py-3">
                    <div class="fw-bold mb-3" style="font-size:1.2em;">Setup</div>
                    <ul class="nav flex-column" id="settingsSidebar">
                        <li class="nav-item"><a class="nav-link active" href="#" data-panel="siteurl">Site URL Config</a></li>
                        <li class="nav-item"><a class="nav-link" href="#" data-panel="db">Create/Delete DB</a></li>
                        <li class="nav-item"><a class="nav-link" href="#" data-panel="css">Global CSS Config</a></li>
                        <li class="nav-item"><a class="nav-link" href="#" data-panel="users">Users Management</a></li>
                        <li class="nav-item"><a class="nav-link" href="#" data-panel="newuser">New User Setup</a></li>
                        <li class="nav-item"><a class="nav-link" href="#" data-panel="sql">Manual SQL</a></li>
                        <li class="nav-item"><a class="nav-link" href="#" data-panel="widgets">Custom Widget Providers</a></li>
                        <li class="nav-item"><a class="nav-link" href="#" data-panel="email">Email Config</a></li>
                        <li class="nav-item"><a class="nav-link" href="#" data-panel="maintenance">Maintenance</a></li>
                        <li class="nav-item"><a class="nav-link" href="#" data-panel="viewsettings">View Settings</a></li>
                        <li class="nav-item"><a class="nav-link" href="#" data-panel="testtoken">Test Login Token</a></li>
                        <li class="nav-item mt-3"><a class="nav-link text-danger fw-bold" href="/Dashboardify/index.php">Exit</a></li>
                    </ul>
                </nav>
                <main class="col-md-10 ms-sm-auto px-4 main-content" id="settingsMain">
                    <!-- Panels -->
                    <div id="panel-siteurl" class="settings-panel active">
                        <!-- Site URL Config content -->
                        <div class="tabPanel">
                            <h2> Site URL config (required)</h2>
                            <p>There are some items that will need to reference the base URL for this webpage. 
                            <br/>Please configure the box below with the site URL, in the format of ' https://example.com/this_site_directory/'
                            </p>
                            <form method="POST" action="config/storesiteurlconfig.php">
                                <input id="siteurlconfig" name="siteurlconfig" value="<?php echo $urlvalue ?>" class="form-control mb-2"></input>
                                <button class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                    <div id="panel-db" class="settings-panel">
                        <!-- Create/Delete DB content -->
                        <div class="tabPanel">
                            <h2>Create/Delete Database</h2>
                            <?php echo $dbfound ?> <br />
                            <p>WARNING: There is no confirmation after clicking this link!!!
                            <a href="delete-dashboardify-db.php">Delete Dashboardify DB</a></p>
                        </div>
                    </div>
                    <div id="panel-css" class="settings-panel">
                        <!-- Global CSS Config content -->
                        <div class="tabPanel">
                            <h2> Global Dashboard CSS - Default For All Users</h2>
                            <p>This is the default CSS that will be loaded for everyone's dashboards, underneath any user-supplied custom CSS for their dashboards. </p>
                            <form action="config/savecss.php">
                                <textarea cols="50" rows="5" name="CSS" style="width: 99%; height: 420px;" class="form-control"><?php echo $css ?></textarea><br />
                                <button class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                    <div id="panel-users" class="settings-panel">
                        <!-- Users Management content -->
                        <div class="tabPanel">
                            <h2>Users</h2>
                            <div id="userstable">
                                <table class="table table-bordered table-sm">
                                    <tr><th>RecID</th><th>Username/Email</th><th>Admin</th><th>Actions</th></tr>
                                    <?php
                                    if ($Exists) {
                                        $userslist = selectquery("Select RecID, Email, Admin FROM Users");
                                    }
                                    if (isset($userslist)) {
                                        foreach ($userslist as $user) {
                                            if (($user["Admin"] != "1") Or ($user["Admin"] = "")) {
                                                $user["Admin"] = "N";
                                            } else { $user["Admin"] = "Y";}
                                            echo "<tr><td>" . $user["RecID"] . "</td><td>" . $user["Email"] . "</td><td>" . $user["Admin"] . "</td><td><a href='config/delete_user.php?recID=" . $user["RecID"] . "'>Delete?</a> " . "<a href='config/make_admin.php?recID=" . $user["RecID"] . "'>Make Admin?</a> <a href='config/remove_admin.php?recID=" . $user["RecID"] . "'>Remove Admin?</a></tr>";
                                        }
                                    }
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="panel-newuser" class="settings-panel">
                        <!-- New User Setup content -->
                        <div class="tabPanel">
                            <form id="NewUserSettings" method="POST" action="setup.php?action=UpdateNewUserSettings">
                                <h2>New User Experience</h2>
                                <p>Below are settings affecting new users on the system.</p>
                                <label>Authentication Type: </label>
                                <?php
                                if ($Exists) {
                                    $authmode = scalarquery("Select Value From Settings Where Name = 'AuthMode'", "Value");
                                } else {$authmode = "None";}
                                ?>
                                <select ID="ddlAuthType" name="AuthType" class="form-select w-auto d-inline-block mb-2">
                                    <option value='<?php echo $authmode?>' selected='selected'><?php echo $authmode?></option>
                                    <option value="Password">Password</option>
                                    <option value="None">None</option>
                                </select><br />
                                <label>Default Dashboard Background Image For First Dashboard: </label>
                                <input id='defaultdashimage' name='defaultdashimage' value='<?php echo $defaultdashimage ?>' class="form-control w-auto d-inline-block mb-2"></input>
                                <br />
                                <input type='checkbox' <?php echo $form_require_confirmation_code_state ?> name='requireconfirmationcode' id='requireconfirmationcode'></input><label for='requireconfirmationcode'>Should new users be required to confirm their email addresses when registering?</label>
                                <br />
                                <label>Time Period For Sessions</label>
                                <select id="sessionlength" name="sessionlength" class="form-select w-auto d-inline-block mb-2">
                                    <option value='<?php echo $sessionlength ?>'><?php echo $sessionlength?></option>
                                    <option value="30 Days">30 Days</option>
                                    <option value="7 Days">7 Days</option>
                                    <option value="Infinite">Infinite</option>
                                </select>
                                <br />
                                <button class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                    <div id="panel-sql" class="settings-panel">
                        <!-- Manual SQL content -->
                        <div class="tabPanel">
                            <form action="setup.php">
                                <h2>Manual SQL Updates</h2>
                                <input type="radio" id="exec" name="SQLOperationType" value="Exec">
                                <label for="exec">Execute (No Results)</label><br>
                                <input type="radio" id="select" name="SQLOperationType" value="Select">
                                <label for="select">Select (With Results)</label><br>
                                <textarea id="SQLUpdate" name="SQLUpdate" class="form-control mb-2"></textarea><br />
                                <button class="btn btn-primary">Submit</button>
                            </form>
                            <?php echo $sqlqueryresults ?>
                            <br />
                            <?php echo generateTableFromObjects(selectquery("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';")); ?>
                        </div>
                    </div>
                    <div id="panel-widgets" class="settings-panel">
                        <!-- Custom Widget Providers content -->
                        <div class="tabPanel">
                            <h2>Custom Widget Providers</h2>
                            <?php echo $widgetproviderlist ?>
                            <form id="submitNewWidgetProvider" method="POST" action="setup.php?action=SubmitWidgetProvider">
                                <label>Name for Custom Widget Provider: </label><br /><input id="WidgetProviderName" name="WidgetProviderName" class="form-control mb-2"><br />
                                <label>CSS Styling For Widget:</label><br /><textarea id="CSS_For_Widget_Provider" name="WidgetProviderCSS" class="form-control mb-2"></textarea><br />
                                <label>HTML Content To Display In Widget:</label><br /><textarea id="HTML_Content_For_Widget_Provider" name="WidgetProviderHTML" class="form-control mb-2"></textarea><br />
                                <label>PHP To Run Before Loading Widget (To populate variables, if needed):</label><br /><textarea id="PHP_To_Run_For_Widget_Provider" name="WidgetProviderPHP" class="form-control mb-2"></textarea><br />
                                <button class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                    <div id="panel-email" class="settings-panel">
                        <!-- Email Config content -->
                        <div class="tabPanel">
                            <form id="mail" method="POST" action="config/storemailcreds.php">
                                <h3>Email Config</h3>
                                <p>This section configures the mail account used, and if it should be used to lock new user accounts until they submit an email confirmation code.
                                The purpose of this is to prevent bots from creating accounts and attempting to spam various functions on the site. </p>
                                <label>Username</label><br />
                                <input id ="username" name="username" class="form-control mb-2"></input>
                                <label>Password</label><br />
                                <input id="pw" type="password" name="password" class="form-control mb-2"></input><br />
                                <label>SMTP Security Method</label><br />
                                <input id="smtpSecureMethod" name="smtpSecure" class="form-control mb-2"></input><br />
                                <label>Port</label><br />
                                <input id="port" name="smtpPort" class="form-control mb-2"></input><br />
                                <button class="btn btn-primary">Submit</button>
                            </form>
                            <form id="testemail" method="POST" action="setup.php?action=testEmail" class="mt-2">
                                <button class="btn btn-secondary">Test Email (Send to myself)</button>
                            </form>
                        </div>
                    </div>
                    <div id="panel-maintenance" class="settings-panel">
                        <!-- Maintenance content -->
                        <div class="tabPanel">
                            <h3>Remove Unused Dashboards and Widgets</h3>
                            <p>This link will search for dashboards linked to any users that don't exist, and remove them. It will then remove any widgets that belong to dashboards that don't exist.
                            (Future-proofing - need to ensure this doesn't affed ' stored widget ' functionality when built)</p>
                            <a href="config/remove_unused_dashboards_and_widgets.php">Remove Unused Dashboards and Widgets</a>
                            <h3 class="mt-4">Clear Existing Session Tokens / Log Everyone Out</h3>
                            <p>This will log out everyone from the system and force them to get new session tokens.</p>
                            <a href="setup.php?action=ClearExistingSessionTokens">Clear Existing Session Tokens</a>
                            <h3 class="mt-4">Clear Unused User Accounts</h3>
                            <p>This will remove user accounts that were 'registered' but never finished the sign-up process.
                            If a lot of user accounts are being generated as a result of website scraping, this feature will remove them.</p>
                            <a href="setup.php?action=RemoveUnusedUserAccounts">Remove Unused User Accounts</a>
                            <h3 class="mt-4">Download Database File</h3>
                            <a href="config/download_db.php">Click here to Download Database File</a>
                        </div>
                    </div>
                    <div id="panel-viewsettings" class="settings-panel">
                        <!-- View Settings content -->
                        <div class="tabPanel">
                            <?php echo generateTableFromObjects(selectquery("Select * From Settings")); ?>
                        </div>
                    </div>
                    <div id="panel-testtoken" class="settings-panel">
                        <!-- Test Login Token File content -->
                        <div class="tabPanel">
                            <h2>Test Login Token File</h2>
                            <form method="POST" enctype="multipart/form-data">
                                <label for="testLoginTokenFile">Upload a login token file (.dashify):</label>
                                <input type="file" name="testLoginTokenFile" id="testLoginTokenFile" accept=".dashify" class="form-control w-auto d-inline-block mb-2" />
                                <button type="submit" name="testTokenBtn" class="btn btn-primary">Test Token</button>
                            </form>
                            <?php
                            if (isset($_POST['testTokenBtn']) && isset($_FILES['testLoginTokenFile']) && $_FILES['testLoginTokenFile']['error'] === UPLOAD_ERR_OK) {
                                $key = 'hardcoded_super_secret_key_32bytes!'; // 32 bytes for AES-256
                                $tokenData = file_get_contents($_FILES['testLoginTokenFile']['tmp_name']);
                                echo '<div style="margin-top:10px; padding:10px; border:1px solid #888; background:#f8f8f8;">';
                                echo '<b>Token file contents:</b><br><pre>' . htmlspecialchars($tokenData) . '</pre>';
                                if (strpos($tokenData, ':') !== false) {
                                    list($b64iv, $b64cipher) = explode(':', $tokenData);
                                    $iv = base64_decode($b64iv);
                                    $ciphertext = base64_decode($b64cipher);
                                    $userid = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
                                    echo '<b>Decrypted User ID:</b> ' . htmlspecialchars($userid) . '<br>';
                                    if ($userid && is_numeric($userid)) {
                                        $rootPath = $_SERVER['DOCUMENT_ROOT'];
                                        $dbpath = 'sqlite:' . $rootPath . '/Dashboardify/data/Dashboardify.s3db';
                                        $db = new PDO($dbpath);
                                        $stmt = $db->prepare('SELECT * FROM Users WHERE RecID = ?');
                                        $stmt->bindParam(1, $userid, PDO::PARAM_INT);
                                        $stmt->execute();
                                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                                        if ($user) {
                                            echo '<b>User found:</b><br><pre>' . htmlspecialchars(print_r($user, true)) . '</pre>';
                                        } else {
                                            echo '<b style="color:red;">No user found for this ID.</b>';
                                        }
                                    } else {
                                        echo '<b style="color:red;">Invalid or corrupted login token (decryption failed or not numeric).</b>';
                                    }
                                } else {
                                    echo '<b style="color:red;">Invalid token file format (missing colon).</b>';
                                }
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <script>
        // Sidebar navigation logic
        const sidebarLinks = document.querySelectorAll('#settingsSidebar .nav-link:not([href="/Dashboardify/index.php"])');
        const panels = document.querySelectorAll('.settings-panel');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                sidebarLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                panels.forEach(panel => panel.classList.remove('active'));
                const panelId = 'panel-' + this.getAttribute('data-panel');
                document.getElementById(panelId).classList.add('active');
            });
        });
        </script>
    </body>
</html>
