# To-Do
* Bug - dashboard image field seems to always populate its value from first dashboard (Or perhaps that's showing the configured default?), not from current. if viewing a secondary dashboard, the value in the field may not match what's configured. 
	* But saving the settings does populate the field properly on the DB side. 
* Add flag to Dashboard table (And step in createdb.php to get this field created) that will mark as Embeddable
* Add security check on embed_database.php to filter only dashboards marked as embeddable - currently any dashboard can be referenced without logon by tagging embed_dashboard.php and the proper dashboard ID
* Update setup page to move siteurlconfig to database settings table, to prevent annoying error when trying to update the file using querystring parameters
* Setup option to remove dashboards where no valid existing user association can be found
* Add password authentication method
* Add 'default' site-wide settings for new dashboards
	* Background photo
* Update usercss.php to pull css from dashboard insteal of globally for user. Or perhaps break it out to either / both distinct options.
* Setup option to remove dashboards where no valid existing user association can be found
* Add password authentication method

* Interface on setup page to list default / global widgets
* After submitting, on newwidget.php, check if user is actually an admin before accepting / processing / saving the flag for global / default. 

* Add authentication methods config section to setup page where the method to be used (None, password, email auth code, keyfile, LDAP, etc) can be selected.
* Add configuration to setup page for a message (And CSS, maybe?) to display to let users know that they failed to authenticate.
	* Future wish: Render a preview of this message into a DIV element on the screen to the side. 
* Add widget type for querying API and displaying a table from results using client side JS
	* Probably need a text box for filtering path of JSON results to the object to be displayed... 
* Update NewWidget dialog on index.php to make different fields visible / not visible based on which item in the dropdown was selected
	* If not recognized in list of programmed widget types, make them all visible
- Have an interface somewhere where you can mark your default dashboard or set the current one as default. 
	* /userconfig/manage_dashboards.php
	* Provides list of dashboards for current user
	* Links next to each one that allow you to mark as default or delete.....
		* OR, should these just appear as menu bar buttons at the top of index.php? Would be easier. 
* Need a less goofy way to show edit / delete icons for the widgets on the screen
* Ability to embed a copy of a particular dashboard on an external page. URL gets called that generates the dashboard and widgets, minus the buttons at the top and the add/edit dialog boxes. Could be an interesting feature!
* Module for SMTP / Email
    * Will want ability to email users a recovery code if lost password, in the future. No code in project yet to support SMTP config. 

## New-User Experience

### Setup widgets that appear on the page by default for new users. 
^ This can be turned off or the individual widgets to be created for new users / SQL statements can be altered in the Setup page... Maybe a feature to copy existing widget ID's under each new user, or a direct SQL insert (less preferred).

Could use javascript on the client side to handle sending each NewWidget request on a new user event, then reloading the dashboard after each request was submitted. 

* these could be defined on the setup page
* copy the new widget dialog from index.php
* add a flag to the copied dialog when on setup.php that marks it as a global widget
* add column to widgets table to make this a global widget
* add code to the index.php page so that when a new dashboard is created, it creates new widgets for the new DB automatically. 
* At this point is where we should figure out how to trigger the URL for a new widget in the background or something, so that we don't have to reload the page several times. Dunno. 


### User settings page

* could have things like User CSS moved here
* background image setup
* FUTURE BACKGROUND IMAGE / SLIDESHOW SETTINGS
* Some GUI selectors or buttons to fill in user CSS for bookmark CSS or background color
* label somewhere telling someone they can write in something like 'lightgrey' for the background color, or pick from a dropdown?
	* ideally a color splotch appears that can update in realtime (future luxury)

### Initial background image

* Maybe setup a background image or individual widgets located under the main menu buttons, explaining what they are for, etc. 
* User can use the 'Edit Widgets' button to delete these. 
* Maybe a checkbox or button somewhere that acknowledges all new-user-experience items and makes them all go away at once
	* This could be a boolean flag on their user in the DB, maybe? 


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



## Update readme.md with proper setup documentation, and/or set up a separate document for it.

Need to get this app into proper position for someone else to be able to install it properly. There's no reason this should sit unusable in a file drawer somewhere.

## Compartmentalize the 'Widget Type' dropdown code to support easier mods / plugins

Breakout the code that populates and handles the 'Widget Type' dropdown code so that it can be easily modified to add new types. 

Consider pulling the code to run for the widget type from a DB entry, so that future mods / updates can just populate their code into the core DB and don't need to rely on actual code / file updates on the server / could be implemented more easily such as via an interface. 

# fix bugs

# Future Ideas

- Ability to drag and drop an existing widget to a new position. When 'dropping' the item, record the new position and we change the X/Y coords for the widget with a background request over javascript to a PHP page that acts as an API, while also changing it locally on the client so that we don't have to reload the page. 
- Ability to query widget results from a local DB file, show a result list. 
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
