<?php
include("shared_functions.php");


$widgetsSQL = "CREATE TABLE [Widgets] (
    [RecID] INTEGER  NOT NULL PRIMARY KEY AUTOINCREMENT,
    [DashboardRecID] INTEGER  NULL,
    [WidgetType] NVARCHAR(50)  NULL,
    [BookmarkDisplayText] NVARCHAR(100)  NULL,
    [PositionX] INTEGER  NULL,
    [PositionY] IntEGER  NULL,
    [SizeX] InTEGER  NULL,
    [SizeY] iNTEGER  NULL,
    [WidgetURL] NVARCHAR(1000)  NULL,
    [WidgetCSSClass] NVARCHAR(150)  NULL
    , Notes TEXT, sqlserveraddress TEXT(75), sqldbname TEXT(75), sqluser TEXT(75), sqlpass TEXT(75), sqlquery TEXT(2000))";

$usersSQL = "CREATE TABLE [Users] (
	[RecID]	INTEGER PRIMARY KEY AUTOINCREMENT,
	[Email]	TEXT NOT NULL
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
