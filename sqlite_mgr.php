<?php
/*
  +------------------------------------------------------------------------------------+
  |                                                                                    |
  |   File:       sqlite_mgr.php                        Version:    2013.09.03         |
  |                                                                                    |
  |   Purpose:                                                                         |
  |     Single file manager for sqlite databases.                                      |
  |     - Requires: PHP 5 with sqlite3 PDO driver.                                     |
  |     - Works on SQLite 2 & 3 files.                                                 |
  |     - One click sql cmd buttons                                                    |
  |     - Auto type sql cmd from history box by clicking                               |
  |                                                                                    |
  |     For a SQLite2 database file, just change the Data Source Name (DSN) 'sqlite:'  |
  |     by 'sqlite2:' but there are a few differences with that DSN:                   |
  |       - sqlite2 Driver requires a file extention when creating a new database with |
  |         the PDO class constructor                                                  |
  |    	  - does not suppot 'IF NOT EXISTS' syntax, so change code below accordingly.  |
  |    	  - genearates larger files.                                                   |
  |    	  - SQLite2 is dead meat. So, if you can, upgrade your db files to SQLite3     |
  |                                                                                    |
  |     The original 'SQLIte DSManager' has been heavily modified by Jan Zumwalt of    |
  |     net-wrench.com for integration into BugLite (buglight.sourcforge.net)          |
  |                                                                                    |
  |   Copyright:  COPYRIGHT 2007-2008 by Jan Zumwalt, www.net-Wrence.com               |
  |               Licensed under the GNU public lincense.                              |
  |   ---------------------------------------------------------------------------------|
  |   Latest source: https://sourceforge.net/projects/sqlitephpmgr/ or Net-Wrench.com  |
  |                                                                                    |
  |   Changes:    2013.09.03  jz - added 'About' information & hide button             |
  |   Changes:    2013.10.01  jz - added sql template buttons                          |
  |                           jz - changed calls to $_SERVER['PHP_SELF']               |
  |   Changes:    2011.10.06  jz - added css theme colors                              |
  |   Changes:    2011.05.14  jz - added login check                                   |
  |   Changes:    2011.02.19  jz - added web form & debug support                      |
  |                                                                                    |
  |   original program: http://www.lumadis.be/repository/sqlite/sqliteDSManager.php    |
  +------------------------------------------------------------------------------------+  */

      
  /*	Configuration variables */
    session_start();
    error_reporting(E_ALL);
    $now           = date("Y-m-d H:i:s");       // date & time w/leading zeros, dec 1 = 2010-12-01 00:00:00  
    $today         = date("Y-m-d");             // date w/leading zeros, dec 1 = 2010-12-01   
    $year          = date("Y");                 // date w/leading zeros  
    $page_title    = "SqLite 2&thinsp;/&thinsp;3 Database Manager &nbsp;-&nbsp;";
    $page_name     = "sqlitemgr";
    $version       = "2013.09.01";
    $require_login = "true";                    // true/false
    $menu          = "<center>[ <a href='/'>Home</a> | <a href='".$_SERVER['PHP_SELF']."?action=about'>About</a> ]</center>";
    
    if (isset($_SESSION['BASEDIR'])) { 
      $dbPath  = $_SESSION['BASEDIR'] = './';   // database directory
    } else {
      $dbPath = "./";
    }  

    $cfg_dbaseDir		  = $dbPath;		            // databases directory (must be writable for www-data to create history file)
    $cfg_maxHistoList	= 10;					            //	Number of lines from history database table to be displayed
    $cfg_maxHistoRows	= 200;					            //	Max number of rows in history database tables

  /* +-------------------------------------------------------------+
     |                  check if login is needed                   |
     +-------------------------------------------------------------+  */

  if ($require_login == 'false' && $_SESSION['LOGIN']  != "true" ) { 
   echo "<h2>Sorry, you must be login to use this program</h2>";
   die;
  }     
	
  /* +-------------------------------------------------------------+
     |                         debug support                       |
     +-------------------------------------------------------------+  */
     
  // debug function, call using debug("mode"), mode= "","on",'log","both","long"
  // include './include/debug.php';  # include debug function 
  // debug("on"); die('died at view.php');  
  
  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
  	<title>SqLite 2/3 Manager</title>
  	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  	<meta name="author" content="ripat" />
  	<meta name="generator" content="Linux and vim" />
   
  	<style type="text/css">
  	   body {
        	font-size: 14px;
  	      font-family: arial;
  	      background: black;      
          color:#8d8;
          margin:50px;
        }
        
        table {
  	      font-family: arial;   
          border-collapse: collapse
        }
        
  	    td{
        	font-size: 14px;      
          padding: 0 1em 0 1em; 
          border: thin solid gray;
        }
        
        th {
          font-weight:normal;
        	font-size: 18px;      
          padding: 0 1em 0 1em; 
          border: thin solid gray;
          color:#9ae
        }
           
        a {
           color: #bdd;
           text-decoration: none;
        }      
        a:hover { 
          background-color:#555;
          color:#d99;
        }
        h2 {
        	font-family: arial;
          font-weight: normal;
        	color: #6dc;
        	padding-top: 10px;
          margin-top: 0;
        }
        
        h3 {
        	font-family: arial;
          font-size: 20px;
        	color: #8cf;
          font-weight: normal;
          margin-bottom: 3px;
        }
        
        input, textarea{
           SCROLLBAR-FACE-COLOR: #226; /* button face */
           SCROLLBAR-HIGHLIGHT-COLOR: #666; 
           SCROLLBAR-SHADOW-COLOR: #222; 
           SCROLLBAR-3DLIGHT-COLOR: #666; 
           SCROLLBAR-ARROW-COLOR: #8e8; 
           SCROLLBAR-TRACK-COLOR: #445; 
           SCROLLBAR-DARKSHADOW-COLOR: #222; 
           color:#8e8; /* font color */
           font-family: courier; 
           font-size: 14px; 
           background-color: #334;
        }
        
  	   	div {
          margin-top: 1em; 
          border: thin solid gray; 
          padding: 0 1em 1em 1em;
        }
        
  	   	#dbases a {
          padding-right: 1em;
        }
        
  	   	#histo_list {
          height: <?php echo ($cfg_maxHistoList) ?>em; 
          overflow: auto;
        }
        
        button {
          color:#999;
          background-color: #334;
          border-radius:5px;
        }
        
        button:hover {
          background-color: #934;
        }
     </style>
     
     <script>
     
       // other
       function cleartxt() {
         document.forms['command'].elements['sql'].value = "";
       }              
       
       // database sql
       function db_info() {
         document.forms['command'].elements['sql'].value = "select * from sqlite_master where 1 ";
       }
       
       // table sql
       function tbl_info() {
         document.forms['command'].elements['sql'].value = "select * from sqlite_master where tbl_name='t1' and  type='table'  ";
       }

       function tbl_create() {
         var sql = 
          'CREATE TABLE t1 ( \n\
  id INTEGER PRIMARY KEY, \n\
  active         TEXT(1)     DEFAULT y,        -- y/n \n\
  fname          TEXT(25)    DEFAULT unknown,  -- \n\
  Pay            REAL(25)    DEFAULT unknown,  -- \n\
  editby         TEXT(25)    DEFAULT unknown,  -- first name \n\
  editdate       DATETIME    DEFAULT (datetime(\'now\',\'localtime\')), \n\
  note           TEXT(255)                     -- notes \n\
)';
         document.forms['command'].elements['sql'].value = sql;
       }
       
              
       function tbl_rename() {
         document.forms['command'].elements['sql'].value = 'ALTER TABLE t1old RENAME TO t1new';
       }
       
       function tbl_copy() {
         document.forms['command'].elements['sql'].value = 'INSERT INTO t2 (f1, f2) SELECT f3, f4 FROM t1';
       }

       function tbl_delete() {
         document.forms['command'].elements['sql'].value = 'DROP TABLE if exists t1';
       }

      // column
       function col_add() {
         document.forms['command'].elements['sql'].value = 'ALTER TABLE t1 ADD COLUMN f1 TEXT(25) DEFAULT "<text>" /* comment */';
       }
       
       function col_rename() {
         var sql = 
          '   SQLite does not support renaming a column directly,\n\
   so you must create a new column, copy the right \n\
   data to it and then remove the old column';

         document.forms['command'].elements['sql'].value = sql;
       }

       
      // record sql
       function record_select() {
         document.forms['command'].elements['sql'].value = "select * from t1 where 1 limit 10 offset 0";
       }
       
       function record_insert() {
       
         var sql = 
          'insert into t1 \n\
    (f1, f2) \n\
  values \n\
    (4, \'text\')';       
         document.forms['command'].elements['sql'].value = sql;
       } 
       
       function record_alter() {
         document.forms['command'].elements['sql'].value = "update t1 set f1 = value1, f2 = value2 where [condition];";
       }
       
       function record_delete() {
         document.forms['command'].elements['sql'].value = "delete from t1 where f1=1";
       }       
       
     </script>     
     
     
  </head>
  <body>

<?php

/*	Variable Initialisation */
$exception = $tableSet = $histo = $form = $recordSet = $dbase = $count = NULL;

/*	Database files from $cfg_dbaseDir directory */
$list_databases = array();
foreach(scandir($cfg_dbaseDir) as $file){
	if (is_file($cfg_dbaseDir.$file)){
		/*	test if $file is a sqlite3 file */
		$pdo = new PDO('sqlite:'.$cfg_dbaseDir.$file);
		if(is_object($pdo) && $pdo->query('SELECT type FROM sqlite_master')){
			$list_databases[] = $file;
		}
	}
}

if  (isset($_REQUEST['db']) && !empty($_REQUEST['db'])) {
	$dbase = $_REQUEST['db'];
	
	/*	Connection to database */
	$pdo = new PDO('sqlite:'.$cfg_dbaseDir.$dbase);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	/*	If not exists: creation of a table for SQL commands history in current dir. */
	$pdo_h = new PDO('sqlite:'.$cfg_dbaseDir.'sqlite3dsmHistory');
	$pdo_h->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$histoName = preg_replace('#[. ]#', '_', $dbase);
	//echo '<br> ---> '.$histoName; exit;
	$pdo_h->exec(sprintf('CREATE TABLE IF NOT EXISTS "history_%s" ( "sql_cmd" varchar(250) )', $histoName));

	/* Imort of POST variable */
	if  (isset($_POST['sql']) && !empty($_POST['sql'])){
		
		/* if SELECT ... */
		if (preg_match('#^SELECT #i', $_POST['sql'])) {
			try { $result = $pdo->query($_POST['sql']); } catch (PDOException $e) {$exception = 'SQL error: '.$e->getMessage()."\n";}
			if (isset($result)) {
				$row = $result->fetchAll(PDO::FETCH_ASSOC);
			}
				
		/*	else: INSERT, UPDATE, CREATE etc... */
		} else {
			try { $count = $pdo->exec($_POST['sql']); }	catch (PDOException $e) {$exception = 'SQL error: '.$e->getMessage()."\n";}
		}
	
		/*	Insert of SQL command in history table */
		$pdo_h->exec(sprintf('INSERT INTO "history_%s" (sql_cmd) VALUES (%s)',	$histoName, $pdo_h->quote($_POST['sql'])));

	} else {
		$row = NULL;
	}

	/*	Extraction of database structure */
	$result = $pdo->query('SELECT type, name, tbl_name, sql FROM sqlite_master');	
	$table_list = $result->fetchAll(PDO::FETCH_ASSOC); 

	/*	List of last commands from history file */
	$result = $pdo_h->query('SELECT rowid, sql_cmd FROM "history_'.$histoName.'" ORDER BY rowid DESC LIMIT '.$cfg_maxHistoList);
	$row_h = $result->fetchAll(PDO::FETCH_ASSOC);

	/*	Clean up of history table if needed. See $cfg_maxHistoRows */
	if ($row_h){
		$qry_delete = 'DELETE FROM "history_'.$histoName.'" WHERE rowid < '.($row_h[0]['rowid'] - $cfg_maxHistoRows);
		$pdo_h->exec($qry_delete);
	}

    	/*	Preparation of form-textarea */
    		$previousSql = isset($_REQUEST['sql']) ? $_REQUEST['sql'] : NULL;
    	$form = sprintf('<form  name=command action="'.$_SERVER['PHP_SELF'].'" method="post"><p><textarea name="sql" rows="7" cols="100" >%s</textarea></p>
      <p>
      <button type="submit" style="width:100px;">Execute</button>
      <button type="button" onclick="cleartxt()" style="width:100px;">Clear</button>
    	<input type="hidden" name="db" value="%s" /></p></form>'
    	, $previousSql, $dbase);
		
	/* Preparation of history list */ 
	$histo = '';
	foreach ($row_h as $v){
		$get = str_replace("\r\n", " ", $v['sql_cmd']);
		$histo .= sprintf('<a href="?db=%s&amp;sql=%s">%s</a><br />',
			$dbase, urlencode($get), $get);
	}

	/* Preparation of RecordSet */ 
	if (isset($row) && $row){
		$recordSet = '<table>';

		/*	titres colonnes */
		$recordSet .= '<tr>';
		foreach($row[0] as $k => $v){
			$recordSet .= '<th>'.$k.'</th>';
		}
		$recordSet .= '</tr>';
		
		/*	content */
		foreach ($row as $k => $v){
			$recordSet .= '<tr>';
			foreach ($v as $vv){
				$recordSet .= '<td>'.$vv.'</td>';
			}
			$recordSet .= '</tr>';
		}
		$recordSet .='</table>';
	} else {
		$recordSet = 'No data returned';
		$recordSet .= '<br />'.$exception;
		$recordSet .= $count ? $count.' lines affected' : NULL;
	}

	/* Preparation database Structure: */ 
	if (isset($table_list) && $table_list){
		$tableSet = "<table>\n";
		
		/*	table header */
		$tableSet .= "<tr>";
		foreach($table_list[0] as $k => $v){
			$tableSet .= '<th>'.$k.'</th>';
		}
		$tableSet .= "</tr>\n";
		
		/*	table rows */
		foreach ($table_list as $k => $v){
			$tableSet .= "<tr>";
			foreach ($v as $vv){
				$tableSet .= "<td>".$vv."</td>";
			}
			$tableSet .= "</tr>\n";
		}
		$tableSet .="</table>\n";
	} else {
		$tableSet = "No table found in database file\n";
	}
}

/*	Preparation database file list */
$databases = '';
foreach ($list_databases as $db){
	$databases .= '<a href="?db='.urlencode($db).'">'.$db."</a> | ";
}

/*  +--------------------------------------------------------------------------------------+
    |                                     Display Content                                  |
	  +--------------------------------------------------------------------------------------+  */

    echo "
         <div style='background-color:#244;'>
         <br>
         $menu
         <h2>$page_title Ver $version</h2>
         <h3 style='font-size:100%;'>Available databases in <span style='color:#cc0;'>$cfg_dbaseDir $databases</h3></span>
         </div>";

    if (isset($_GET['action']) && $_GET['action']=="about") {
      echo "<pre style='font-family:courier;'>
  +------------------------------------------------------------------------------------+
  |                                                                                    |
  |   File:       sqlite_mgr.php                        Version:    2013.09.03         |
  |                                                                                    |
  |   Purpose:                                                                         |
  |     Single file manager for sqlite databases.                                      |
  |     - Requires: PHP 5 with sqlite3 PDO driver.                                     |
  |     - Works on SQLite 2 & 3 files.                                                 |
  |     - one click sql cmd buttons                                                    |
  |     - auto type sql cmd from history box by clicking                               |
  |                                                                                    |
  |     For a SQLite2 database file, just change the Data Source Name (DSN) 'sqlite:'  |
  |     by 'sqlite2:' but there are a few differences with that DSN:                   |
  |       - sqlite2 Driver requires a file extention when creating a new database with |
  |         the PDO class constructor                                                  |
  |    	  - does not suppot 'IF NOT EXISTS' syntax, so change code below accordingly.  |
  |    	  - genearates larger files.                                                   |
  |    	  - SQLite2 is dead meat. So, if you can, upgrade your db files to SQLite3     |
  |                                                                                    |
  |     The original 'SQLIte DSManager' has been heavily modified by Jan Zumwalt of    |
  |     net-wrench.com for integration into BugLite (buglight.sourcforge.net)          |
  |                                                                                    |
  |   Copyright:  COPYRIGHT 2007-2008 by Jan Zumwalt, www.net-Wrence.com               |
  |               Licensed under the GNU public lincense.                              |
  |   ---------------------------------------------------------------------------------|
  |   Latest source: https://sourceforge.net/projects/sqlitephpmgr/                    |
  |                  or Net-Wrench.com                                                 |
  |                                                                                    |
  |   Changes:    2013.09.03  jz - added 'About' information & hide button             |
  |   Changes:    2013.10.01  jz - added sql template buttons                          |
  |                           jz - changed calls to \$_SERVER['PHP_SELF']               |
  |   Changes:    2011.10.06  jz - added css theme colors                              |
  |   Changes:    2011.05.14  jz - added login check                                   |
  |   Changes:    2011.02.19  jz - added web form & debug support                      |
  |                                                                                    |
  |   original program: http://www.lumadis.be/repository/sqlite/sqliteDSManager.php    |
  +------------------------------------------------------------------------------------+ </pre>
  
  <button type='button' onClick=\"self.location='".$_SERVER['PHP_SELF']."'\" style='width:100px;'>Hide</button>
  ";
    }    
    ?>    
    
<div>
<h3>Database Structure &emsp; <?php echo "<span style='color:#acc;'>".$dbase."</span>"; ?></h3>
<?php echo $tableSet; ?>
</div>

<div id="sql">
<h3>Sql</h3>
      Database&thinsp;&thinsp;
               <button type="button" onclick="db_info()" style="width:100px;">Info</button>
               <br>
      Table &emsp;&ensp;&thinsp;
              <button type="button" onclick="tbl_info()"      style="width:100px;">Info</button>
              <button type="button" onclick="tbl_create()"    style="width:100px;">Create</button>
              <button type="button" onclick="tbl_copy()"      style="width:100px;">Copy</button>
              <button type="button" onclick="tbl_rename()"    style="width:100px;">Rename</button>
              <button type="button" onclick="tbl_delete()"    style="width:100px;">Delete</button>
              <br>                                            
      Column &ensp;&thinsp;&thinsp;                                            
              <button type="button" onclick="col_add()"       style="width:100px;">Add</button>
              <button type="button" onclick="col_rename()"    style="width:100px;">Rename</button>
              <br>
      Record &emsp; 
              <button type="button" onclick="record_select()" style="width:100px;">Select</button>
              <button type="button" onclick="record_insert()" style="width:100px;">Insert</button>
              <button type="button" onclick="record_alter()"  style="width:100px;">Alter</button>
              <button type="button" onclick="record_delete()" style="width:100px;">Delete</button><br>
<?php echo $form; ?>
</div>

<div id="recordset">
<h3>Result</h3>
<?php echo $recordSet; ?>
</div>

<div id="histo">
<h3>Command History</h3>
<div id="histo_list">
<?php echo $histo; ?>
</div>
</div>


    <!-- footer -->
    <font style="color:#666;">
      <center>
        <br>
        Copyright 2007-<?php echo $year; ?><br>
        by Jan Zumwalt, <a style="color:#a66;" href="www.Net-Wrench.com">www.Net-Wrench.com</a><br>
        Ver <?php echo $version; ?>  
      </center>      
    </font> 

</body>
</html>