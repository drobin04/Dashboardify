<!DOCTYPE html>
<html>
    <head>
    <script type="module" src="js/md-block.js"></script>

</head>
<body>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("shared_functions.php");

if (doesDatabaseExist()) {
	echo "The database already exists and is non-empty; please <a href='delete-dashboardify-db.php'>Delete the database file first.</a>";
	exit();
}

$widgetsSQL = "CREATE TABLE [Widgets] (
    [RecID] INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT,
    [DashboardRecID] TEXT  NULL,
    [WidgetType] NVARCHAR(50)  NULL,
    [BookmarkDisplayText] NVARCHAR(100)  NULL,
    [PositionX] INTEGER  NULL,
    [PositionY] IntEGER  NULL,
    [SizeX] InTEGER  NULL,
    [SizeY] iNTEGER  NULL,
    [WidgetURL] NVARCHAR(1000)  NULL,
    [WidgetCSSClass] NVARCHAR(150)  NULL
    , Notes TEXT NULL, sqlserveraddress TEXT(75) NULL, sqldbname TEXT(75) NULL, sqluser TEXT(75) NULL, sqlpass TEXT(75) NULL, sqlquery TEXT(2000) NULL
    , Global BOOLEAN NULL
    , UserRecID TEXT NULL
    )";
    
$StoredWidgets = "CREATE TABLE StoredWidgets (
	RecID INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	[DashboardRecID] TEXT  NULL,
	[WidgetType] NVARCHAR(50)  NULL,
	[BookmarkDisplayText] NVARCHAR(100)  NULL,
	[PositionX] INTEGER  NULL,
	[PositionY] IntEGER  NULL,
	[SizeX] InTEGER  NULL,
	[SizeY] iNTEGER  NULL,
	[WidgetURL] NVARCHAR(1000)  NULL,
	[WidgetCSSClass] NVARCHAR(150)  NULL
	, Notes TEXT NULL, 
	sqlserveraddress TEXT(75) NULL, 
	sqldbname TEXT(75) NULL, 
	sqluser TEXT(75) NULL, sqlpass TEXT(75) NULL, 
	sqlquery TEXT(2000) NULL
	, Global BOOLEAN NULL
	)
";
$login_attempts = "
CREATE TABLE login_attempts (
dt DATE,
Email TEXT NULL,
Successful BOOLEAN NULL,
IPAddress TEXT NULL
)
";

$settingsSQL = "CREATE TABLE [Settings] (
    [Name] TEXT NOT NULL,
    [Value] TEXT NOT NULL)";
$populatefirstauthmodesetting = "
INSERT INTO Settings (Name, Value) VALUES ('AuthMode','Password');";

$usersSQL = "CREATE TABLE [Users] (
	[RecID]	INTEGER PRIMARY KEY AUTOINCREMENT,
	[Email]	TEXT NOT NULL,
    [Admin] BOOLEAN  NULL,
    password TEXT NULL,
    EmailConfirmed BOOLEAN NULL,
    ConfirmationCode TEXT NULL
)";

$userCSSsql = "CREATE TABLE [UserCSS] (
	[ID]	INTEGER PRIMARY KEY AUTOINCREMENT,
	[CSS]	TEXT,
	[UserID]	TEXT
)";

$SessionsSQL = "CREATE TABLE [Sessions] (
	[RecID]	INTEGER PRIMARY KEY AUTOINCREMENT,
	[SessionID]	TEXT NOT NULL,
	[UserID]	INTEGER
)";

$EventsSQL = "CREATE TABLE Events( RecID INTEGER PRIMARY KEY AUTOINCREMENT, dt datetime default current_timestamp, path TEXT NULL, body TEXT NULL, headers TEXT NULL )";

$DashboardsSQL = "CREATE TABLE [Dashboards] (
    [RecID] INTEGER  PRIMARY KEY AUTOINCREMENT NOT NULL,
    [DefaultDB] BOOLEAN  NULL,
    [CustomCSS] NVARCHAR(8000)  NULL,
    [BackgroundPhotoURL] NVARCHAR(400)  NULL,
    [UserID] INTEGER  NULL,
    [DashboardID] NVARCHAR(50)  NULL
    , Name NVARCHAR(50) NULL
    , Embeddable BOOLEAN NULL)";

$CustomwidgetProvidersSQL = "CREATE TABLE CustomWidgetProviders (
    WidgetProviderName TEXT,
    CSS_Styling TEXT  NULL,
    HTML_Content TEXT  NULL,
    PHP_To_Run TEXT  NULL)";
    
$SessionLengthSetting = "Insert into Settings (Name, Value) VALUES ('sessionlength', 'Infinite')";
$RequireConfirmationCodeSetting = "Insert into Settings (Name, Value) VALUES ('RequireConfirmationCode', 0)";

$WidgetStylesTable = "Create Table WidgetStyles (
Name TEXT,
WidgetType TEXT,
ClassName TEXT,
CSS TEXT NULL,
Description TEXT NULL
)";

$widgets_users_view = "
Create View v_Widgets As 
Select u.Email, d.DashboardID, u.RecID As UserRecID, w.* From Widgets w
	Left Join Dashboards d On w.DashboardRecID = d.DashboardID
	Left Join Users u on d.UserID = u.RecID
	";

execquery($widgetsSQL);
execquery($usersSQL);
execquery($userCSSsql);
execquery($SessionsSQL);
execquery($EventsSQL);
execquery($DashboardsSQL);
execquery($settingsSQL);
execquery($populatefirstauthmodesetting);
execquery("INSERT INTO Settings (Name, Value) VALUES ('SiteUrlConfig', 'https://localhost/Dashboardify/');");
execquery($CustomwidgetProvidersSQL);
execquery($login_attempts);
execquery($SessionLengthSetting);
execquery($RequireConfirmationCodeSetting);
execquery($WidgetStylesTable);
execquery($widgets_users_view);

    echo "Database creation completed - If you're seeing this, no error has occurred yet.";
    redirect('register_user.php');
?>

<md-block>
If you're finished setting up the database, feel free to continue to [the main page](./index.php) or to [go back to the setup page](setup.php) to continue setup.
</md-block>
</body>
</html>
