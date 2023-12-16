<!DOCTYPE html>
<html>
    <head>
    <script type="module" src="js/md-block.js"></script>

</head>
<body>
<?php
include("shared_functions.php");


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
    )";

$usersSQL = "CREATE TABLE [Users] (
	[RecID]	INTEGER PRIMARY KEY AUTOINCREMENT,
	[Email]	TEXT NOT NULL,
    [Admin] BOOLEAN  NULL
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
    , Name NVARCHAR(50) NULL)";

execquery($widgetsSQL);
execquery($usersSQL);
execquery($userCSSsql);
execquery($SessionsSQL);
execquery($EventsSQL);
execquery($DashboardsSQL);

    echo "Database creation completed - If you're seeing this, no error has occurred yet.";
?>

<md-block>
If you're finished setting up the database, feel free to continue to [the main page](./index.php) or to [go back to the setup page](setup.php) to continue setup.
</md-block>
</body>
</html>
