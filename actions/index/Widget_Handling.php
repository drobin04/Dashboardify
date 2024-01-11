<?php

$editbuttonscss = "<a class='editbuttons' style='display:none;height:24px; width:24px;' href='";

$deletebuttonOnClick = "deleteWidget(" . $row["RecID"] . ", '" . $siteurl . "')";
$deletebuttoncss = "<a class='editbuttons' style='display:none;height:24px; width:24px;' onclick='" . $deletebuttonOnClick . "' href='";

$imgstylecss = "<img style='height:24px; width:24px;' src='";
$PositionAndCSSClass = "left: " . $row["PositionX"] . "px; top: " . $row["PositionY"] . "px; width: " . $row["SizeX"] . "px; height: " . $row["SizeY"] . "px; width: " . $row["SizeX"] . "px;' class='widget resize " . $row["WidgetCSSClass"] . "'>";
$combined = "<div id='" . $row["RecID"] . "' style='margin:15px; position:absolute; background-color: white;  border: 1px solid black;" . $PositionAndCSSClass . $editbuttonscss . $siteurl . "?EditRecID=" . $row["RecID"] . "&SelectDashboardID=" . $dashboardid . "'>" . $imgstylecss . $siteurl . "icons/edit.png'></img></a>" . $deletebuttoncss . $siteurl . "actions/DeleteWidget.php?RecID=" . $row["RecID"] . "'>" . $imgstylecss . $siteurl . "icons/cancel.png'></img></a>";
$floatingbookmarkPositionAndCSSClass = "left: " . $row["PositionX"] . "px; top: " . $row["PositionY"] . "px;' class='widget resize " . $row["WidgetCSSClass"] . "'>";
$floatingbookmark = "<div id='" . $row["RecID"] . "' class='widget bookmark' style='position: absolute; width:100px; background-color: lightgrey;  border: 1px solid black; " 
. $floatingbookmarkPositionAndCSSClass . $editbuttonscss 
. $siteurl . "?EditRecID=" . $row["RecID"] . "&SelectDashboardID=" 
. $dashboardid . "'>" . $imgstylecss . $siteurl . "icons/edit.png'></img></a>" . $editbuttonscss 
. $siteurl . "actions/DeleteWidget.php?RecID=" . $row["RecID"] . "'>" . $imgstylecss . $siteurl . "icons/cancel.png'></img></a>";
Switch ($row["WidgetType"]) {
	case "SQLServerScalarQuery":
		If ($row["WidgetType"] == "SQLServerScalarQuery") {
		$sqlservaddress = $row["sqlserveraddress"];
		$sqldbname = $row["sqldbname"];
		$sqlserveruser = $row["sqluser"];
		$sqlserverpass = $row["sqlpass"];
		$sqlquery = $row["sqlquery"];
		debuglog($sqlquery,"sql query about to be executed");
		$result = "";
		try { // reference for sql query from PHP - https://social.technet.microsoft.com/wiki/contents/articles/1258.accessing-sql-server-databases-from-php.aspx
			$connectionInfo = array( "Database"=>$sqldbname, "UID"=>$sqlserveruser, "PWD"=>$sqlserverpass);
			//debuglog($connectionInfo,"debug - connection info");
			$conn = sqlsrv_connect( $sqlservaddress, $connectionInfo);
			if( $conn ) {
					debuglog("Connection established.");
				$tempresult = sqlsrv_query($conn,$sqlquery);
				if ( sqlsrv_fetch( $tempresult ) )
					{$result = sqlsrv_get_field( $tempresult, 0);debuglog($result, "Results of SQL query");}
			}else{debuglog("Connection could not be established."); debuglog(sqlsrv_errors());}
		}
		catch (Exception $ex) {
			debuglog($ex, "error during SQL server connection");
		}
		echo $combined . "<p>" . $row["BookmarkDisplayText"] . ": " . $result ."</p></div>";
	}
	break;
	case "Bookmark":
		If (($row["WidgetType"] == "Bookmark") and ($row["PositionX"] != 0 and $row["PositionX"] != "")) { // For Bookmarks with custom positions
		echo $floatingbookmark 
		." <div style='padding: 5px; width: 100%; class='widget bookmark "
		. $row["WidgetCSSClass"] 
		. "'><a target='_blank' href='" 
		. $row["WidgetURL"] 
		. "'>" 
		. $row["BookmarkDisplayText"] 
		."</a></div></div>";
		}
		elseIf ($row["WidgetType"] == "Bookmark" and ($row["PositionX"] == 0 or $row["PositionX"] == "")) {
		echo "<div id='" . $row["RecID"] . "' style='padding: 5px; margin: 5px; width:100px; background-color: lightgrey;  border: 1px solid black;' class='widget bookmark" . $row["WidgetCSSClass"] . "'><a target='_blank' href='". $row["WidgetURL"] ."'>". $row["BookmarkDisplayText"] ."</a>" . $editbuttonscss . $siteurl . "?EditRecID=" . $row["RecID"] . "&SelectDashboardID=" . $dashboardid . "'>" . $imgstylecss . $siteurl . "icons/edit.png'></img></a>" . $deletebuttoncss . $siteurl . "actions/DeleteWidget.php?RecID=" . $row["RecID"] . "'>" . $imgstylecss . $siteurl . "icons/cancel.png'></img></a></div>";
		}

	
	break;
	
case "IFrame":
    echo $combined . "<iframe style='height:100%;width:100%' src='". $row["WidgetURL"] ."'></iframe></a></div>";
break;
	
case "Collapseable IFrame":
	$combined2 = "<div id='" . $row["RecID"] . "' style='display:none; position:absolute; background-color: white;  border: 1px solid black;" . "width: " . $row["SizeX"] . "px; height: " . $row["SizeY"] . "px; width: " . $row["SizeX"] . "px;' class='widget resize " . $row["WidgetCSSClass"] . "'>" . $editbuttonscss . $siteurl . "?EditRecID=" . $row["RecID"] . "&SelectDashboardID=" . $dashboardid . "'>" . $imgstylecss . $siteurl . "icons/edit.png'></img></a>" . $editbuttonscss . $siteurl . "actions/DeleteWidget.php?RecID=" . $row["RecID"] . "'>" . $imgstylecss . $siteurl . "icons/cancel.png'></img></a>";
    
	$hidden = "<div id='' class='collapse' style='" . "left: " . $row["PositionX"] . "px; top: " . $row["PositionY"] . "px; width: " . $row["SizeX"] . "px; height: 20px; width: " . $row["SizeX"] . "px;'" . "'>";
	echo $hidden . "<a style='border: none !important;' class='collapse' onclick='opencollapsediframe(&quot;" . $row["RecID"] . "&quot;)'>" . $row["BookmarkDisplayText"] . "</a>";
	echo $combined2 . "<iframe style='height:100%;width:100%;' id='" . $row["RecID"] . "/iframe' src2='". $row["WidgetURL"] ."'></iframe></a></div>";
	echo "</div>"; //this wraps combined variable, into a surrounding div.
	
break;

case "Notes":
	echo $combined . "<p class='note' style='padding-left: 15px; padding-right: 15px;'><md-block>". $row["Notes"] ."</md-block></p></div>";
	break;

case "HTMLEmbed":
	echo $combined . $row["Notes"] ."</div>";
	
	break;
	
case "SQLiteResultsList":
	// Wrap in a try statement!! Throw the error into debug + the widget. 
	try {
	// Get variables
	If ($row["sqlquery"] == "") {$row["sqlquery"] = "Select 'Query Not Present' as Error";}
	$sqlitedbname = $row["sqldbname"];
	$sqlitequery = $row["sqlquery"];
	debuglog($sqlitequery,"about to execute query");
	$dbpath = 'sqlite:' . $sqlitedbname; // setup proper sqlite DSN pathname from supplied input
	$db_file = new PDO($dbpath);
	$stmt9 = $db_file->prepare($sqlitequery);
	$stmt9->execute();
	$localresults = $stmt9->fetchAll(PDO::FETCH_ASSOC);
	debuglog($localresults,"Query results");
	echo $combined . generateTableFromObjects($localresults) ."</div>";
	} catch (exception $e) {
		
	} finally {
	}
	break; // End of SQLite DB 'If' block
	
case "Javascript API Request":
	
	break;
	
case "SQLite Chart (PHPGD)":
	$charturl = "actions/generate_chart.php?WidgetID=" . $row["RecID"] . "&Width=" . $row["SizeX"] . "&Height=" . $row["SizeY"];
	
	echo $combined . "<iframe style='height:100%;width:100%' src='". $charturl ."'></iframe></a></div>";
	
	
	break;
	
	
default: // CUSTOM WIDGET PROVIDERS.
	
	// Look for and handle custom widget providers defined in DB!
		// WidgetProviderName
		// CSS_Styling
		// HTML_Content
		// PHP_To_Run
		// CREATE TABLE CustomWidgetProviders (
		// WidgetProviderName TEXT,
		// CSS_Styling TEXT  NULL,
		// HTML_Content TEXT  NULL,
		// PHP_To_Run TEXT  NULL,
	// OK... PHP eval handler test... lets go...
	// At this point we have a 'widget' defined that didn't match any other pre-defined widget
	// 	Types... So we'll see if there's a matching widget handler with this name in the DB.
	
	
	// THE FOLLOWING CODE USES EVAL() WHICH IS DANGEROUS.
	// ONLY ALLOW RUNNING THE PHP PART OF THIS IF USER IS ADMIN?
	$w_provider = $row["WidgetType"];
	$prov = selectquery("Select WidgetProviderName, CSS_Styling, HTML_Content, PHP_To_Run From 
	CustomWidgetProviders Where WidgetProviderName = '" . $w_provider . "'");
	if (isset($prov)) {
		debuglog($prov, "Custom Widget Provider");
		$p = $prov[0];
		$widget_css = $p["CSS_Styling"];
		$w_HTML_Content = $p["HTML_Content"];
		$w_PHP_To_Run = $p["PHP_To_Run"];
		
		
		// Draw the widget first i guess?
		echo $combined;
		if (getAuthMode() == "None") {
			// Bad. 
		}
		if (AmIAdmin()) {
		eval($w_PHP_To_Run);
		} // ONLY RUN EVAL IF I AM ADMIN.
		echo $w_HTML_Content ."</div>";
	}

//https://enplnnfh4ifmp.x.pipedream.net/
	
	break;
// End of Widget Loading for-each list
} // END OF SWITCH STATEMENT
    

?>