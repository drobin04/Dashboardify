<?php 
				function selectquery($sql) {
					debuglog($sql,"about to execute query");
					$rootPath = $_SERVER['DOCUMENT_ROOT'];
					$dbpath = 'sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db';
					$db_file = new PDO($dbpath);
					//$db_file = new PDO('sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db');
					//$db_file = new PDO('sqlite:Dashboardify.s3db');
					//debuglog($localdb,"DB file");
					$stmt1 = $db_file->prepare($sql);
					$stmt1->execute();
					$results = $stmt1->fetchAll(PDO::FETCH_ASSOC);
					//debuglog($results,"Query results");
					return $results;
				}

				function getCurrentUserID() {
					$sessionid = $_COOKIE["SessionID"]; 
					debuglog($sessionid, "SessionID"); //Get User for Session ID
					$userid = selectquery("Select UserID From Sessions Where SessionID = '" . $sessionid . "'")[0]["UserID"]; 
					debuglog($userid, "User ID found for user");
					return $userid;
				}
				function execquery($sql) {
					//$localdb = new PDO('sqlite:Dashboardify.s3db');
					$rootPath = $_SERVER['DOCUMENT_ROOT'];
					$localdb = new PDO('sqlite:' . $rootPath . '/Dashboardify/Dashboardify.s3db');
					$stmt1 = $localdb->prepare($sql);
					$stmt1->execute();
					
				}
				function GUID()
				{
					if (function_exists('com_create_guid') === true)
					{
						return trim(com_create_guid(), '{}');
					}
				
					return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
				}
				function debuglog( $object=null, $label=null ){
					$message = json_encode($object, JSON_PRETTY_PRINT);
					$label = "Debug" . ($label ? " ($label): " : ': ');
					echo "<script>console.log(\"$label\", $message);</script>";
				}
?>