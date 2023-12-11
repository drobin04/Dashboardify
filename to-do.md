# To-Do

## Check when loading Dashboardify/index.php - Does database exist? 
If database doesn't exist(yet?), direct to the setup / admin page to run a first time setup. 

Can use a url parameter for that if needed.

## Add Setup.php / Admin.php Page
This page should guide a new user through first steps to deploy the app, as well as perhaps serve as a place to update any environment-wide variables that don't need to be seen / managed on the end-user screen. 

Things that may belong here: 

* Configuring 'siteurlconfig.php'
* Configuring site-wide / default CSS
* Management interface for users? 
* Defining session timeout options (Is timeout needed? If so, how long?)
    * Would need to add some code to support this, but is a logical feature request.


## Add Markdown support using md-block to the Notes widget
Uses  
"<script type="module" src="https://md-block.verou.me/md-block.js"/script

To do the job.

* Added the script
    * Need to update Notes box to maybe accept multiline. Can't test this properly. When testing with a BR tag for a line break after a header, it seems to not accept the BR as moving to a new line and just makes everything count as the header. 

## Update Widget Entry Box

### Needs to accept multiline Entry for Notes / HTML embed



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

For E Book Viewer: 
https://github.com/futurepress/epub.js/

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
* 