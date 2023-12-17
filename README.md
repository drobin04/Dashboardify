![Demo Photo](https://i.imgur.com/v5QUd2X.jpeg)
# About Dashboardify
This is a PHP web application for developing personal, customizeable dashboards with bookmarks, embedded frames, notes, as well as database and API connectivity.

The purpose of this application is to extend the function of your browser's Home page & New Tab Page. (To set this as your new tab page you'll need to be using a firefox-based browser such as firefox or librewolf, and the Custom New Tab Page Extension)

This provides you with the ability to construct one or multiple 'Dashboards'. 

What are dashboards? In this context, a dashboard is meant to organize everything you intend to see, use and work on in your web browser today. 

* The links / sites you're going to frequently open - You can pin as many sites as you want! And position them anywhere!
* Notes and reminders of information you want to remember or need to reference frequently
* Widgets of various types to provide functionality and interactive features to the page (See # Features below)
* Some limited server-side processing capabilities and features (to be expanded on in future updates)

# Features

Multiple different types of embeddable widgets:
* Bookmarks - simple links to websites
* IFrames - Embed webpages directly on the screen
    * Sub-Type for 'collapseable' IFrame that starts as a minimized bar and can then be opened after page load. Reduces page load / wait time. 
* Direct HTML embed - Embed HTML content directly, including embeddable widgets from other sites
* Notes - Note section that can be placed; Supports Markdown formatting
* SQL Server Scalar Query - Allows you to retrieve a scalar query value from a MS SQL Server DB (Plans in the future to implement result lists and charts as well)
* SQLiteResultsList - Specify the (relative) location of the SQLite DB file to the website, provide the query to perform (including columns to return, etc). The results will automatically be formatted into a table. You can style the table using custom CSS by using the CSS selector 'TableResults' (Some global CSS rules applied by default)

# Live Demo

A live version of this application has been released at https://dashboardify.app/Dashboardify/ . 

(Message from the developer here - Please do not do anything malicious to that site, I am certain there are live issues there, there's only one person developing on this project at the moment :) So if you discover any issues or security concerns, please feel free to report them!)

# We Need YOU

## Work We Need Contributors For
* In serious need of graphic designs for styles / UI across the board
    * Super helpful if the person who is able to contribute this also has the ability to craft the CSS to create the style, but also appreciated if only can provide design ideas
* For code contributions, see the notes I have listed on to-do items [on the to-do.md page](to-do.md)
* Testing for gaping security issues and documenting changes needed to ensure users can't access things they shouldn't be able to (There will be many!). Especially around the setup.php page / admin panel. 

## Important Missing Features

The following are important items that have not yet been implemented, either not properly or entirely missing

* User Authentication / security
    * Currently just asks for a username / email address, no authentication / PW or otherwise occurs.
        * Could make a PW auth or an email code authenticator at some point in the future but would require future code to safely store a PW hash and/or storing SMTP config for sending mail. 
* Module for SMTP / Email
    * Will want ability to email users a recovery code if lost password, in the future. No code in project yet to support SMTP config. 

# Setup
## Server / Environment Requirements

In order to host this project, you'll need a WAMP / LAMP stack - A web server and PHP. 

If you're on Windows, you can download XAMPP, which should have everything you need setup out-of-the-box after installation. 

After installing XAMPP, you need to configure the Dashboardify directory. This means either reconfiguring XAMPP to point to your dashboardify folder, or copying dashboardify into your XAMPP directory at C:\XAMPP\htdocs\ and accessing it from there. (Note, the easier of those two is just setting up your local clone under your htdocs folder rather than troubleshooting a broken php.ini file when reconfiguring XAMPP doesn't work)

### PHP Version

This app is tested and demo'd on PHP version 7.3.

There is a known issue preventing it from running on version 8.2.

I have not tested it on other versions, but know that if you're on a newer PHP version, *if you encounter a lot of 'header has already been sent' errors, this is why.* The resolution is to change the PHP version, or to rewrite a lot of the code to ensure ***no HTML / white space content is written to the page before headers / redirects***, which requires some restructuring of important code. 

### Extensions

If you have a different stack for your web server and PHP, you should know that this app uses PDO to access a SQLite database file. You may need to enable sqlite extensions. 
If this is not done, you may get a blank screen when loading the app, as almost all important data is pulled from the included Dashboardify.S3DB file.

For MS SQL Server connectivity and related widgets, the following extensions need to be enabled (uncommented) in php.ini, and the 2 included dll files in the /ext folder are needed, which contain microsoft's sql server driver for PHP.
    extension=php_sqlsrv_56_ts.dll
    extension=php_pdo_sqlsrv_56_ts.dll  

For the clone.php functionality / Code Upgrade functionality to work, you will need to enable the ZipArchive extension & class in your php.ini file.
Uncomment:
    extension=zip

## First Time Use / Configuration

1. Access the setup.php page, and click on the link to create and populate the database file for use. 
2. Return to the setup.php page
3. Click 'Return to Dashboardify', where you'll be prompted to sign in / register the first user account.
4. Enter an email address you'd like to use for your username (Will later be replaced with a username / password registration page, currently no auth method enforced)
5. Click 'New Widget' once loaded onto index.php, and begin adding widgets to the screen and using the app.

# Documentation

Documentation is a work in progress, but can be found [on the Documentation.md page](documentation.md)


# Attribution notes

## Markdown support

Markdown is used and/or supported in various places within this application. Markdown support is provided by [Verou.Me's md-block custom element.](https://md-block.verou.me/)

A copy of Verou.Me's md-block.js file is included in this distribution, to prevent any dependency issues should the copy hosted at their official URL be removed or moved to a new location. 

