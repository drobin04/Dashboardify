# To-Do

- Work on functionality to create and select between multiple dashboards
-- Dropdownlist next to existing buttons at top left, with list of dashboards user has (probably just by recid for now).
-- Probably javascript that handles the event when dropdownlist is changed, to load the selected dashboard. 
-- 

- Consider loading a small image from a site's root /favicon.ico above each bookmark for a more natural look, maybe this could be an optional feature.

# fix bug

For now, have added code when saving a widget to find the dashboard for the user as just looking up  their first dashboard... Later will need to update to properly receive from index.php which dashboard the user is on. 

# Future Ideas

- Ability to query widget results, show a result list. 
- For scalar values, widget becomes a link that can take you to another page that displays the results in a table format. 
- API functionality for capturing requests into a custom sqlite db, then ability to display results on dashboard. 
