# To-Do

* Ability to reset a lost password
* Need a way to store config for how a user will authenticate / what their backup auth options are to support their password reset method
* Because if we just have a button that resets a password to a temp one and emails it to someone, a spammer could just bot the button to infinite usernames

* Ability to reset a lost password - Send a random password via email that they can log in with so they can then change their PW.


* Email Config on setup page 

	* Need to get to point where you can actually setup an OAuth login, for example for gmail.
	* Email confirmation code for new users
	* Setting added controlling whether email confirmation code is required
	* field added to users table for whether confirmation code has been entered
	* check on verify-login to check if confirmation code has been activated yet, and if not, fail auth / do not proceed to complete login.
* Security Changes
* Make it to where widgets can be 'Stored' , re-usable widgets that can be shared between users
* SQLite widgets ***MUST BE CREATED BY ADMIN, AND CAN ONLY BE ADDED BY NON USERS THRU FUTURE SHARED WIDGET INTERFACE
	This is important because there are security implications if standard user could ever run a sql query to select things like user ID's or other data.  


[Migrating to-do section to Issues page]

* bug - setup - auth mode dropdown currently repeats a value. Need to make it to where the static values get added maybe only if they're not the one already selected by the db setting?

* Module for SMTP / Email
    * Will want ability to email users a recovery code if lost password, in the future. No code in project yet to support SMTP config. 

### User settings page

* could have things like User CSS moved here
* background image setup
* FUTURE BACKGROUND IMAGE / SLIDESHOW SETTINGS
* Some GUI selectors or buttons to fill in user CSS for bookmark CSS or background color
* label somewhere telling someone they can write in something like 'lightgrey' for the background color, or pick from a dropdown?
	* ideally a color splotch appears that can update in realtime (future luxury)


## Add a default page theme to the app, and have it customizeable from the Setup page

## Add / Continue developing Setup.php / Admin.php Page
This page should guide a new user through first steps to deploy the app, as well as perhaps serve as a place to update any environment-wide variables that don't need to be seen / managed on the end-user screen. 

Things that may belong here: 

* Configuring the various implemented authentication methods
	* None currently in place
	* Potential future ones might be:
		* Password
		* Email / SMS Authentication Code
		* Key file based
* Management interface for users? 
* Defining session timeout options (Is timeout needed? If so, how long?)
    * Would need to add some code to support this, but is a logical feature request.
		* Code for this should ideally be located in the logoutredirect.php page, OR could be implemented as a timeout on the session cookie!


# fix bugs

# Future Ideas
- For scalar values, widget becomes a link that can take you to another page that displays the results in a table format. 
- API functionality for capturing requests into a custom sqlite db, then ability to display results on dashboard. 

for sqlite code - build api page to query for SQLite widget values.
Check for widget details where a dashboard id / user id matches so you can't plug in random person's user ID. 
Have javascript load the data from the API in all cases so that it works either on the php page OR on the html cached copy.

## Charts

# User-Defineable API's
- Widget type for custom API
- Writes out a custom php file in the background to host the API. 
- Renders a note on the screen , or perhaps a collapseable iframe, with the api details.
- I imagine it'll hook requests and save them into a sqlite db, but could, in the end, just have a flat user-specified php script. Think more on this.

# / Events API / db

- pre-built API that can receive webhooks and stores the content and headers into fields in a row in a sqlite db. This way someone can quickly ping it, then query the events db for results. 

- Consider loading a small image from a site's root /favicon.ico above each bookmark for a more natural look, maybe this could be an optional feature.

# Changes History
* Updated logic for loading the new/edit widget dialog / form, to not loop through every widget in the DB when trying to find the widget being edited and THEN checking if RecID matches. Instead, now filters the query on where recid = value. 
* Added markdown support to Notes widgets
* Moved the initial 'form' element from the very beginning of the document, down to the 'light' box being used for the New Widget box. For some reason this was left wrapping nearly ALL of the content into the first form. Now it's in the right spot, which also makes it easier to recognize that that box is using a form element when reading the code. 
* Updates logoutredirect.php logic to check if the Session ID being used actually matches a user. If it doesn't match any users, redirect to the start-login.php page. This was done when testing deleting the DB entirely and creating a new one, where it became apparent that holding an old SessionID made it appear as if you were logged into a user, even though one wouldn't exist yet. 
* Removed code related to an e-book viewing widget. Was not able to get this into a finished position, and it was introducing large scope creep, so have removed this code for now. It may be an idea for the future, but is impeding progress in other, more important areas for now. 
* Ability to place bookmarks in custom locations on the screen as placeable widgets
	* Was relatively simple in concept; had to duplicate and modify some shared variables to support this one. One extra spaghetti noodle on the plate. 
* 12/14/23 - Added a class tag to the paragraph element on notes, 'note', to support custom user styling. Also added a default padding-left style of 15px. 
* 12/14/23 - Added logic to detect DB file not existing, direct user to Setup page, functions on Setup page to re-create/prepare and/or delete the database. This is done to support getting the project into a state suitable for github cloning / reproduction. 
* 12/14/23 - Added Setup for siteurlconfig, and redirect to force entry if empty.
* 12/14/23 - Added Setup button to home page
* 12/15/23 - Added Admin flag to users table, and config to mark this flag via Setup
* 12/15/23 - Added module that can check for whether the current user is an Admin
* 12/15/23 - Added shared function for populating HTML table from db results
* 12/15/23 - Added widget type for SQLiteResultsList, which displays a table from a given query to a DB (Sqlite DB must be hosted with this app and accessible by this app)
* 12/15/23 - Fixed bug with existing SQL Server widget and new SQLiteresultslist widget where the fields on the Edit Widget Dialog don't fully re-populate all of the fields with existing data. 
* 12/16/23 - Add delete_user.php
* 12/16/23 - Fixed bug on dashboard select dropdown, where you couldn't change the dashboard when viewing the cachedpage.html. Now works properly (Redirects to the dashboard on index.php, necessary for functionality / to load the data)
* 12/16/23 - Added ability to remove admin rights in setup page
* 12/16/23 - Database SQL Update area for Setup.php
	* Form area with textarea element
	* Radio buttons for whether we want to execute as a select (with results) or just an executequery with no results
	* For results, uses the shared_function to generate an HTML table of results from the dataset
* 12/16/23 - Setup.php layout changes 
	- Moved 'Database file found!!' under the 'Delete Database' heading, renamed to 'Create/Delete Database'.
	- CSS Styles added to page to address some wonky display issues
	- savecss.php adjusted to automate the redirection back to setup.php instead of requiring a link to be clicked.
* 12/16/23 - Add 'Global' or 'Default' flag to widget table
* 12/16/23 - Solved issue on index.php where a new user that's just logged in for the first time, does not get the default background image loaded until the next page refresh
* 12/16/23 - Updated SiteUrlConfig setting to be stored in the sqlite DB, several bugfixes around referencing this & what if DB doesn't exist
* 12/16/23 - SEVERAL bugfixes around checking for admin permissions, logging out users who no longer hold a valid session key or whos user account no longer exists, etc
* 12/16/23 - Added useful 'scalarquery' function to shared_functions.php
* 12/16/23 - https://dashboardify.app is live!
* 12/16/23 - New user experience is functional on live site!
* 12/16/23:
	* Added logic on code that creates first dashboard, to find & loop through all default / global widgets, and for each one, insert a new copy with the new dash's recID. 
	* If user is an admin, display a checkbox on the NewWidget dialog for global / default that will get sent. 
	* default dashboard image setting added to setup.php page, influences default dashboard image for first-time users.
	* Added logic on code that creates first dashboard, to find & loop through all default / global widgets, and for each one, insert a new copy with the new dash's recID. 
* 12/17/23:
	 * Added 'Global' or 'Default' flag to widget table
	 * Updated setup page to move siteurlconfig to database settings table, to prevent annoying error when trying to update the file using querystring parameters
	 * Widget Ownership is now checked when editing / deleting widgets!
	 * Added flag to Dashboard table (And step in createdb.php to get this field created) that will mark as Embeddable
	 * Added security check on embed_database.php to filter only dashboards marked as embeddable - before, any dashboard can be referenced without logon by tagging embed_dashboard.php and the proper dashboard ID
	 * Added 'Edit Dashboard' button with ability to edit dashboard Name, its Embeddable status, and which displays the URL to embed the current dashboard, if needed. 


## 12/13 Bugfix Finished - New User Experience / First widget creation bugged
Upon brand new DB creation, some weird GUID appears in the URL bar for 'SelectDashboardID', and when creating a first widget, it seemingly doesn't get saved to this dashboard. 
It will save the widget and load the widget properly the next time the page is loaded. 

Notes:
* It's the DashboardRecID being messed up here....
* When dashboard is created, it gets a RecID, but then a DashboardID. RecID is an autonumber but DashboardID is a GUID. 
* When saving the WIDGET, it tries to save the widget under the new Dashboard GUID, which doesn't get stored into the widget.dashboard ID field because it's an INTEGER ONLY field.
* WHEN LOADING THE DASHBOARD, Dashboard ID is getting populated as the RecID autonumber and NOT the dashboardID GUID, possibly as a result of issues with this bug in the past that couldn't be solved at the time.
 
* Changed Widget.DashboardID from Integer to TEXT
* Changed index.php to load dashboard from the user and set dashboard ID to the GUID value / Dashboard ID field, NOT the recID autonumber field. 
* Changed index.php to populate the NewWidget.php form with the GUID 
* Verified index.php properly loads widgets where dashboard ID = GUID (should happen automatically after other steps done)
* Tested afterwards that NewDashboard functionality works

# Scrapped Ideas

## E-book viewing widget

Idea for this one was to have the ability to point to a specific PDF or EPUB document hosted somewhere on the internet, (Or perhaps upload your own), and have it opened onto the home page, so the user could periodically make progress reading it while they were glancing at their home / new tab page. 

For E Book Viewer: 
https://github.com/futurepress/epub.js/
