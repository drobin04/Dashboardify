# Dashboardify
Web-app for developing personal, customizeable dashboards with bookmarks, embedded frames, as well as database and API connectivity.

# Features

Multiple different types of embeddable widgets:
* Bookmarks - simple links to websites
* IFrames - Embed webpages directly on the screen
    * Sub-Type for 'collapseable' IFrame that starts as a minimized bar and can then be opened after page load. Reduces page load / wait time. 
* Direct HTML embed - Embed HTML content directly, including embeddable widgets from other sites
* Notes - Note section that can be placed; Supports Markdown formatting
* SQL Server Scalar Query - Allows you to retrieve a scalar query value from a MS SQL Server DB (Plans in the future to implement result lists and charts as well)
* SQLiteResultsList - Specify the (relative) location of the SQLite DB file to the website, provide the query to perform (including columns to return, etc). The results will automatically be formatted into a table. You can style the table using custom CSS by using the CSS selector 'TableResults' (Some global CSS rules applied by default)


## Cached Homepage for faster loading / reduced server bandwidth / CPU strain
In addition to the base functionality on index.php, there is also an HTML page, 'cachedpage.html', which loads the content of the last loaded dashboard for the current user, from LocalStorage (Gets populated via javascript into localstorage on every index.php load). 

This enables much faster loads of the page, as it's mostly static content that doesn't change frequently, and greatly reduces the number of times PHP needs to run on the server side for a page that may frequently be called as a new tab page / home page etc. 

## SQL Server Connectivity

Note - When adding SQL Server connections, server names may need to be entered in serveraddress\instance format, if an instance other than the default is needed. 

## File / DB Structure

The UI consists of a Dashboard made up of multiple user-defined Widgets.

Dashboards are stored in the Dashboardify sqlite database table 'Dashboards'.

Widgets are stored in the table 'Widgets', and have a foreign key linking dashboards, 'DashboardRecID', which mathes Dashboards.RecID .

Dashboards table has a foreign key linking Users table, User ID.

When a user logs in, the interface queries for the current user ID from the session ID, then searches for Dashboards for the current user ID, and then selects the first one. 

Later feature can be to add a way of tracking default dashboard for user (for now just load first one).

# Setup
## Server / Environment Requirements

- Need to set up PHP to allow sqlite(3) / PDO extensions.
If this is not done, you may get a blank screen when loading the app, as almost all important data is pulled from the included Dashboardify.S3DB file.

- For SQL Server connectivity, the following extensions need to be enabled (uncommented) in php.ini, and the 2 included dll files in the /ext folder are needed, which contain microsoft's sql server driver for PHP.
    extension=php_sqlsrv_56_ts.dll
    extension=php_pdo_sqlsrv_56_ts.dll  

## First Time Use / Configuration

1. Access the setup.php page, and click on the link to create and populate the database file for use. 
2. Return to the setup.php page
3. Click 'Return to Dashboardify'
4. Enter an email address you'd like to use for your username (Will later be replaced with a username / password registration page, currently no auth method enforced)
5. Click 'New Widget' once loaded onto index.php, and begin adding widgets to the screen and using the app.

# We Need YOU

## Work We Need Contributors For
* In serious need of graphic designs for styles / UI across the board
    * Super helpful if the person who is able to contribute this also has the ability to craft the CSS to create the style, but also appreciated if only can provide design ideas

## Important Missing Features

The following are important items that have not yet been implemented, either not properly or entirely missing

* User Authentication / security
    * Currently just asks for a username / email address, no authentication / PW or otherwise occurs.
        * Could make a PW auth or an email code authenticator at some point in the future but would require future code to safely store a PW hash and/or storing SMTP config for sending mail. 
    
* Admin / User permissions gate on setup.php page
    * Currently anyone could access setup.php and perform destructive changes on the DB file. 
    * To solve this issue, need to setup a functional user management and permissions system so that we can delineate normal users from privileged users. 


# Attribution notes

## Markdown support

Markdown is used and/or supported in various places within this application. Markdown support is provided by [Verou.Me's md-block custom element.](https://md-block.verou.me/)

A copy of Verou.Me's md-block.js file is included in this distribution, to prevent any dependency issues should the copy hosted at their official URL be removed or moved to a new location. 

