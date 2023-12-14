<html>
<head></head>
<body>
<?php
try {
     phpinfo();
     $serverName = "localhost\sqlexpress"; //serverName\instanceName
     $connectionInfo = array( "Database"=>"testdb", "UID"=>"sa", "PWD"=>"Password!");
     $conn = sqlsrv_connect( $serverName, $connectionInfo);

     if( $conn ) {
          echo "Connection established.<br />";
     }else{
          echo "Connection could not be established.<br />";
          die( print_r( sqlsrv_errors(), true));
     }


}
catch (Exception $ex) {
     echo $ex;
}



?>



</body>


</html>
