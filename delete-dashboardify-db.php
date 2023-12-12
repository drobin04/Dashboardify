<html>
    <head>
    <script type="module" src="js/md-block.js"></script>

</head>
<body>
<html>
    <head>
    <script type="module" src="js/md-block.js"></script>

</head>
<body>
<?php
$file_pointer = "Dashboardify.s3db";
if (!unlink($file_pointer)) {
    echo("$file_pointer cannot be deleted due to an error");
} else {
    echo("$file_pointer has been deleted");
}
?>
<md-block>
Click to return to [the Setup page](setup.php) to continue setup.</md-block>
</body>
</html>
