# To-Do

## New-User Experience

### Initial background image

* Maybe setup a background image or individual widgets located under the main menu buttons, explaining what they are for, etc. 
* User can use the 'Edit Widgets' button to delete these. 
* Maybe a checkbox or button somewhere that acknowledges all new-user-experience items and makes them all go away at once
	* This could be a boolean flag on their user in the DB, maybe? 


## Add a default page theme to the app, and have it customizeable from the Setup page


## Check when loading Dashboardify/index.php - Does database exist? 
If database doesn't exist(yet?), direct to the setup / admin page to run a first time setup. 

Can use a url parameter for that if needed.

## Bugfix - New User Experience / First widget creation bugged
Upon brand new DB creation, some weird GUID appears in the URL bar for 'SelectDashboardID', and when creating a first widget, it seemingly doesn't get saved to this dashboard. 
If I strip out the weird Dashboard ID from the URL bar, it will save the widget and load the widget properly the next time the page is loaded. 

## Add / Continue developing Setup.php / Admin.php Page
This page should guide a new user through first steps to deploy the app, as well as perhaps serve as a place to update any environment-wide variables that don't need to be seen / managed on the end-user screen. 

Things that may belong here: 

* Configuring 'siteurlconfig.php'
* Configuring site-wide / default CSS
* Management interface for users? 
	* Display a table format list of users in the DB, User ID, Email.
* Defining session timeout options (Is timeout needed? If so, how long?)
    * Would need to add some code to support this, but is a logical feature request.
		* Code for this should ideally be located in the logoutredirect.php page, OR could be implemented as a timeout on the session cookie!




## Update Widget Entry Box

### Needs to accept multiline Entry for Notes / HTML embed
-Done


## Update readme.md with proper setup documentation, and/or set up a separate document for it.

Need to get this app into proper position for someone else to be able to install it properly. There's no reason this should sit unusable in a file drawer somewhere.

## Compartmentalize the 'Widget Type' dropdown code to support easier mods / plugins

Breakout the code that populates and handles the 'Widget Type' dropdown code so that it can be easily modified to add new types. 

Consider pulling the code to run for the widget type from a DB entry, so that future mods / updates can just populate their code into the core DB and don't need to rely on actual code / file updates on the server / could be implemented more easily such as via an interface. 

# fix bugs

- If no 'default' dashboard is found, load the first one. Ran into a bug when deploying updated code due to this and had to manually update DB.
^ Test the above, as of 12/2023 not sure if still exists.


- Have an interface somewhere where you can mark your default dashboard or set the current one as default. 



## Bug - Can't change dashboard from indexattempt.html page. 

After changing the dropdown, nothing seems to happen.

Expected behavior: Reloads the page with the selected dashboard shown based on the dropdown item chosen. 

# Future Ideas

- Ability to drag and drop an existing widget to a new position. When 'dropping' the item, record the new position and we change the X/Y coords for the widget with a background request over javascript to a PHP page that acts as an API, while also changing it locally on the client so that we don't have to reload the page. 
- Ability to query widget results from a DB file, show a result list. 
- For scalar values, widget becomes a link that can take you to another page that displays the results in a table format. 
- API functionality for capturing requests into a custom sqlite db, then ability to display results on dashboard. 

for sqlite code - build api page to query for SQLite widget values.
Check for widget details where a dashboard id / user id matches so you can't plug in random person's user ID. 
Have javascript load the data from the API in all cases so that it works either on the php page OR on the html cached copy.



# User-Defineable API's
- Widget type for custom API
- Writes out a custom php file in the background to host the API. 
- Renders a note on the screen , or perhaps a collapseable iframe, with the api details.
- I imagine it'll hook requests and save them into a sqlite db, but could, in the end, just have a flat user-specified php script. Think more on this.

# todo / Events API / db

- pre-built API that can receive webhooks and stores the content and headers into fields in a row in a sqlite db. This way someone can quickly ping it, then query the events db for results. 

- Consider loading a small image from a site's root /favicon.ico above each bookmark for a more natural look, maybe this could be an optional feature.

## 12/9/23 - Bug Fixed - when submitting first widget as a completely new user 
Upon first submission, error occurs 
"Notice: Undefined index: BackgroundPhotoURL in /home/www/douglasrobinson.me/Dashboardify/index.php on line 28

Notice: Undefined index: CustomCSS in /home/www/douglasrobinson.me/Dashboardify/index.php on line 28" 

Issue was due to the logic when loading a dashboard for a user with only one dashboard, there was no 'row' variable on line 28. 

Updated code to the following: 

foreach($dashboards as $row) {
							debuglog($dashboards, "Dashboards Array - Single Result"); 
							$dashboardid = $dashboards[0]["RecID"]; 
							debuglog($dashboardid, "Selected Dashboard ID."); 
							$dashboardphotourl = $row["BackgroundPhotoURL"]; 
							$usercss = $row["CustomCSS"];
						}

The 'foreach' wrapper was not there, therefore the $dashboardphotourl and $usercss assignments were failing because they were referencing an array that did not exist. 


# Changes
* Updated logic for loading the new/edit widget dialog / form, to not loop through every widget in the DB when trying to find the widget being edited and THEN checking if RecID matches. Instead, now filters the query on where recid = value. 
* Added markdown support to Notes widgets
* Moved the initial 'form' element from the very beginning of the document, down to the 'light' box being used for the New Widget box. For some reason this was left wrapping nearly ALL of the content into the first form. Now it's in the right spot, which also makes it easier to recognize that that box is using a form element when reading the code. 
* Updates logoutredirect.php logic to check if the Session ID being used actually matches a user. If it doesn't match any users, redirect to the start-login.php page. This was done when testing deleting the DB entirely and creating a new one, where it became apparent that holding an old SessionID made it appear as if you were logged into a user, even though one wouldn't exist yet. 
* Removed code related to an e-book viewing widget. Was not able to get this into a finished position, and it was introducing large scope creep, so have removed this code for now. It may be an idea for the future, but is impeding progress in other, more important areas for now. 
* Ability to place bookmarks in custom locations on the screen as placeable widgets
	* Was relatively simple in concept; had to duplicate and modify some shared variables to support this one. One extra spaghetti noodle on the plate. 



# Scrapped Ideas

## E-book viewing widget

Idea for this one was to have the ability to point to a specific PDF or EPUB document hosted somewhere on the internet, (Or perhaps upload your own), and have it opened onto the home page, so the user could periodically make progress reading it while they were glancing at their home / new tab page. 

For E Book Viewer: 
https://github.com/futurepress/epub.js/