<?php
include('../shared_functions.php');

if (isset($_GET["action"])) {
    switch ($_GET["action"]) {
        case "movewidget":
            $x_position = abs($_GET["x"]);
            $y_position = abs($_GET["y"]);
            $recid = $_GET["RecID"];
            if (DoIOwnThisWidget($recid)) { 
                execquery("Update Widgets Set PositionX = '" . $x_position . "', PositionY = '" . $y_position . "' Where RecID = '" . $recid . "'");
            } else {
                echo "you don't have rights to update this widget!";
            }
                // check that user owns this widget
            break;
        case "resize":
            $x = abs($_GET["width"]);
            $y = abs($_GET["height"]);
            $recid = $_GET["id"];
            if (DoIOwnThisWidget($recid)) {
                execquery("update widgets set SizeX = '" . $x . "', SizeY = '" . $y ."' Where RecID = '" . $recid ."'");

            }
            break;

        default :
            echo "action unrecognized.";
            break;
    } 

} else {
    echo "No Action Specified.";
}


?>
