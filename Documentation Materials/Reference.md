# Cookies

## Set Cookie

setcookie("SessionID", $sessionid, 2147483640, "/"); //Save session ID into cookie

## Read Cookie

if (isset($_COOKIE["SessionID"])) {

$sessionid = $_COOKIE["SessionID"];

# Database Operations

## Get DBO Object For Binding Params on DB Query

$db = getPDO_DBFile();
$stmt = $db->prepare($select);

$stmt->bindParam(1,$eml,PDO::PARAM_STR);

$stmt->bindParam(2,$pwd,PDO::PARAM_STR);

$stmt->execute();

## Get Result Set


# Hashing

$password = hash('sha256', $inputpassword);

