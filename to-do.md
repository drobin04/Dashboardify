# To-Do

* Spam protection on email confirmation code / signup page: 
* In case someone sets up a bot for spamming out the email confirmation code to random addresses,
* Setup a setting on the DB side that allows for a rate-limit? 
* This would make it easy, unfortunately, for someone to DDOS the site by preventing new user signins...
* Perhaps we could allow them to sign up by sending US an email?


* Ability to browse existing stored widgets from index.php new widget screen



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
* Defining session timeout options (Is timeout needed? If so, how long?)
    * Would need to add some code to support this, but is a logical feature request.
		* Code for this should ideally be located in the logoutredirect.php page, OR could be implemented as a timeout on the session cookie!

* Button to delete all open sessions, as well as delete all sessions for a particular user


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
