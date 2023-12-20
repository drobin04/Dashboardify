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
            }
                // check that user owns this widget
            break;


    }

}


?>
