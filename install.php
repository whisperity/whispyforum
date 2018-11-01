<?php
 /**
 * WhispyForum script file - install.php
 * 
 * Install file in / directory.
 * 
 * Helps the webadmin installing the engine.
 * 
 * WhispyForum
 */
 
// We do not use the /includes/load.php load script, because 
// it will result in errors due ot preinstallation state.

// Rather, we use copied bits from there.
echo '<link rel="stylesheet" type="text/css" href="themes/winky/style.css">'."\n"; // We load the default stylesheet

/* Libraries */
// Template conductor (we load it before everything because templates are needed to get error messages)
require("includes/templates.class.php");
$Ctemplate = new class_template;

// mySQL database layer
require("includes/mysql.class.php");
$Cmysql = new class_mysql;

// general functions
require("includes/functions.php");
/* Libraries */

/* DEVELOPEMENT 
// PH, workaround: output HTTP POST and GET arrays
print "<h4>GET</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_GET,true))."<br>"; 
print "<h4>POST</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_POST,true))."<br>"; 
echo "\n\n\n";
*/
// Set install poistion
if (!isset($_POST['instPos']))
{
	$instPos = 0;
} else {
	$instPos = $_POST['instPos'];
}

// Including templates based on install position
switch ($instPos)
{
	case NULL:
	case 0:
		// Introduction
		
		// We check this file existence now, because if we check it in general (before swich() clause)
		// after the third step (generating config.php) the installation hangs
		if ( file_exists("config.php") )
		{
			// If config.php already exists, give error message
			$Ctemplate->useStaticTemplate("install/ins_start_already", FALSE);
		} else {
			// If not, give standard starting screen
			$Ctemplate->useStaticTemplate("install/ins_start", FALSE); // Use install introduction
		}
		break;
	case 1:
		// Configuration file generator - getting data
		
		if ( @$_POST['error_goback'] == "yes" ) // If user is redirected from step 2 because of an error
		{
			// We output the form with data returned (user doesn't have to enter it again)
			$Ctemplate->useTemplate("install/ins_config", array(
				'DBHOST'	=>	$_POST['dbhost'], // Database host
				'DBUSER'	=>	$_POST['dbuser'], // Database user
				'DBPASS'	=>	$_POST['dbpass'], // Database password
				'DBNAME'	=>	$_POST['dbname'] // Database name
				), FALSE);
		} else {
			// We output general form
			$Ctemplate->useTemplate("install/ins_config", array(
				'DBHOST'	=>	"localhost", // Database host (default)
				'DBUSER'	=>	"", // Database user
				'DBPASS'	=>	"", // Database password
				'DBNAME'	=>	"winky_db" // Database name (default)
				), FALSE); // Config file generator
		}
		break;
	case 2:
		// Configuration file generator
		
		// First, we do a check whether any of the mandatory variables are NULL
		if ( $_POST['dbhost'] == NULL ) // Database host
		{
			$Ctemplate->useTemplate("install/ins_config_variable_error", array(
				'VARIABLE'	=>	"Database host", // Errornous variable name
				'DBHOST'	=>	$_POST['dbhost'], // Database host (should be empty)
				'DBUSER'	=>	$_POST['dbuser'], // Database user
				'DBPASS'	=>	$_POST['dbpass'], // Database password
				'DBNAME'	=>	$_POST['dbname'] // Database name
				), FALSE);
			exit; // We terminate the script
		}
		
		if ( $_POST['dbuser'] == NULL ) // Database user
		{
			$Ctemplate->useTemplate("install/ins_config_variable_error", array(
				'VARIABLE'	=>	"Database user", // Errornous variable name
				'DBHOST'	=>	$_POST['dbhost'], // Database host
				'DBUSER'	=>	$_POST['dbuser'], // Database user (should be empty)
				'DBPASS'	=>	$_POST['dbpass'], // Database password
				'DBNAME'	=>	$_POST['dbname'] // Database name
				), FALSE);
			exit; // We terminate the script
		}
		
		if ( $_POST['dbpass'] == NULL ) // Database password
		{
			$Ctemplate->useTemplate("install/ins_config_variable_error", array(
				'VARIABLE'	=>	"Database password", // Errornous variable name
				'DBHOST'	=>	$_POST['dbhost'], // Database host
				'DBUSER'	=>	$_POST['dbuser'], // Database user
				'DBPASS'	=>	$_POST['dbpass'], // Database password (should be empty)
				'DBNAME'	=>	$_POST['dbname'] // Database name
				), FALSE);
			exit; // We terminate the script
		}
		
		if ( $_POST['dbname'] == NULL ) // Database name
		{
			$Ctemplate->useTemplate("install/ins_config_variable_error", array(
				'VARIABLE'	=>	"Database name", // Errornous variable name
				'DBHOST'	=>	$_POST['dbhost'], // Database host
				'DBUSER'	=>	$_POST['dbuser'], // Database user
				'DBPASS'	=>	$_POST['dbpass'], // Database password
				'DBNAME'	=>	$_POST['dbname'] // Database name (should be empty)
				), FALSE);
			exit; // We terminate the script
		}
		
		// At this point, every mandatory fields are set.
		// Now begin writing config file.
		
		$configfile = $Ctemplate->useTemplate("install/config.php", array(
			'DBHOST'	=>	$_POST['dbhost'], // Database host
			'DBUSER'	=>	$_POST['dbuser'], // Database user
			'DBPASS'	=>	$_POST['dbpass'], // Database password
			'DBNAME'	=>	$_POST['dbname'], // Database name
			'UUID'	=>	generateHexToken(), // Random token
			'GDATE'	=>	date('l jS \of F Y H:i:s') // Generation date
		), TRUE); // Generating the file from template
		
		// Writing file. If write error occurs, give output.
		$wrSuccess = @file_put_contents("config.php", $configfile); // wrSuccess is undefined if there's error
		
		if (!$wrSuccess) // Checking whether a writing error occured.
		{
			$Ctemplate->useTemplate("install/ins_config_write_error", array(
				// We need to pass these variables for a working return form.
				'DBHOST'	=>	$_POST['dbhost'], // Database host
				'DBUSER'	=>	$_POST['dbuser'], // Database user
				'DBPASS'	=>	$_POST['dbpass'], // Database password
				'DBNAME'	=>	$_POST['dbname'] // Database name
			), FALSE); // We give error output
		} else { // If there isn't any writing errors, 
			$Ctemplate->useStaticTemplate("install/ins_config_write_success", FALSE);
		}
		break;
	case 3:
		// Testing database connection
		require('config.php'); // We initialize the config array (need to do this for database connection)
		
		$dbconnection = FALSE; // We cannot connect to the DB host first
		
		$dbconnection = $Cmysql->TestConnection(); // We make a test database connection. (Will be true if we succeed)
		
		// $dbconnection is TRUE if test connection is successful
		// $dbconnection is FALSE if test connection is unsuccessful
		
		if ( $dbconnection == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtest_error", array(
				'DBHOST'	=>	$cfg['dbhost'], // Database host
				'DBUSER'	=>	$cfg['dbuser'], // Database user
				'USE_PASS'	=>	( ($cfg['dbpass'] != NULL) ? 'yes' : 'no' ) // Whether there's a password set.
			), FALSE);
		} elseif ( $dbconnection == TRUE )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtest_success", array(
				'DBHOST'	=>	$cfg['dbhost'], // Database host
				'DBUSER'	=>	$cfg['dbuser'], // Database user
				'USE_PASS'	=>	( ($cfg['dbpass'] != NULL) ? 'yes' : 'no' ) // Whether there's a password set.
			), FALSE);
		}
		
		$Cmysql->Disconnect(); // Close the active connection
		break;
	case 4:
		// Creating database
		require('config.php'); // We initialize the config array (need to do this for database connection)
		$Cmysql->TestConnection(); // We do a reconnect (without DB selecting, so we use TestConnection)
		
		$dbcreate = FALSE; // We failed creating the database first
		
		// $dbcreate isn't FALSE if the database was created
		// $dbcreate is FALSE if the database creation failed
		
		$dbcreate = $Cmysql->Query("CREATE DATABASE IF NOT EXISTS " .$cfg['dbname']. " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci"); // Will be true if we succeed
		
		if ( $dbcreate == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbcreate_error", array(
				'DBNAME'	=>	$cfg['dbname'] // Database name
			), FALSE);
		} elseif ( $dbcreate != FALSE )
		{
			// Give success and proceed
			$Ctemplate->useTemplate("install/ins_dbcreate_success", array(
				'DBNAME'	=>	$cfg['dbname'] // Database name
			), FALSE);
		}
		
		$Cmysql->Disconnect(); // Close connection
		break;
	case 5:
		// Creating database tables
		require('config.php'); // Recall config array (it is unloaded)
		$Cmysql->Connect(); // Now we can use the generic connect
		
		$Ctemplate->useStaticTemplate("install/ins_fw_dbtables_head", FALSE); // We use a more-complex templating here
		
		/**
		 * Here are two variables:
		  ** $tablecreation: true by default, becomes false if there were any errors
		  ** $dbtables: one variable for each creation script. FALSE by default, becomes NOT FALSE after query
		 */
		
		$tablecreation = TRUE; // By default, we can create the tables
		$tablelist = ""; // Uncreated tables' name list
		
		/* Users table */
		// Stores the users' data
		$dbtables_user = FALSE; // We failed creating the table first
		$dbtables_user = $Cmysql->Query("CREATE TABLE IF NOT EXISTS users (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
			`username` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'user loginname',
			`pwd` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'user password (md5 hashed)',
			`email` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'user e-mail address',
			`curr_ip` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0.0.0.0' COMMENT 'current session IP address',
			`curr_sessid` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'current session ID',
			`regdate` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'registration date',
			`loggedin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if user is currently logged in, 0 if not',
			`userLevel` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'clearance level',
			`avatar_filename` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'avatar picture filename',
			`osztaly` VARCHAR(10) NOT NULL COMMENT 'class of the user',
			`hour1` INT(10) NULL COMMENT 'lecture id for hour #1 (fu2_lectures.id)',
			`hour2` INT(10) NULL COMMENT 'lecture id for hour #2 (fu2_lectures.id)',
			`hour3` INT(10) NULL COMMENT 'lecture id for hour #3 (fu2_lectures.id)',
			`hour4` INT(10) NULL COMMENT 'lecture id for hour #4 (fu2_lectures.id)',
			PRIMARY KEY (`id`),
			UNIQUE KEY `username` (`username`),
			UNIQUE KEY `email` (`email`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'userdata'"); // $dbtables_user sets to true if we succeeded creating a table
		
		// We check table creation
		if ( $dbtables_user == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"users" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist .= "users"; // Append users table name to fail-list
		} elseif ( $dbtables_user != FALSE )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"users" // Table name
			), FALSE);
		}
		/* Users table */
		
		/* Menus table */
		// Stores the menus' data
		$dbtables_menu = FALSE; // We failed creating the table first
		$dbtables_menu = $Cmysql->Query("CREATE TABLE IF NOT EXISTS menus (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
			`header` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'menu header',
			`align` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'sidebar vertical align',
			`side` ENUM('left', 'right') NOT NULL DEFAULT 'left' COMMENT 'sidebar choice',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'menu information'"); // $dbtables_menu sets to true if we succeeded creating a table
		
		$dbtables_menu_data = FALSE; // We failed adding the default data first
		$dbtables_menu_data = $Cmysql->Query("INSERT INTO menus(header, align, side) VALUES ('Main menu', 0, 'left')"); // $dbtables_menu_data sets to true if we succeeded adding default data
		
		// We check table creation
		if ( ( $dbtables_menu == FALSE) || ( $dbtables_menu_data == FALSE ) )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"menu" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist .= ", menu"; // Append menu table name to fail-list
		} elseif ( ( $dbtables_menu != FALSE )  && ( $dbtables_menu_data != FALSE ) )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"menus" // Table name
			), FALSE);
		}
		/* Menus table */
		
		/* Menu entries table */
		// Stores the menu entries' data
		$dbtables_menuEntries = FALSE; // We failed creating the table first
		$dbtables_menuEntries = $Cmysql->Query("CREATE TABLE IF NOT EXISTS menu_entries (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
			`menu_id` int(10) NOT NULL COMMENT 'menu id (menus.id)',
			`label` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'text to show',
			`href` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'link data',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'menu entry information'"); // $dbtables_menuEntries sets to true if we succeeded creating a table
		
		$dbtables_menuEntries_data = FALSE; // We failed adding the default data first
		$dbtables_menuEntries_data = $Cmysql->Query("INSERT INTO menu_entries(menu_id, label, href) VALUES (1, 'Homepage', 'index.php')"); // $dbtables_menuEntries_data sets to true if we succeeded adding default data
		
		// We check table creation
		if ( ( $dbtables_menuEntries == FALSE) || ( $dbtables_menuEntries_data == FALSE ) )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"menu_entries" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist .= ", menu_entries"; // Append menu table name to fail-list
		} elseif ( ( $dbtables_menuEntries != FALSE ) && ( $dbtables_menuEntries_data != FALSE ) )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"menu_entries" // Table name
			), FALSE);
		}
		/* Menu entries table */
		
		/* Free university performers table */
		// Stores the performers' data
		$dbtables_fu_performers = FALSE; // We failed creating the table first
		$dbtables_fu_performers = $Cmysql->Query("CREATE TABLE IF NOT EXISTS fu_performers (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
			`pName` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'name of performer',
			`email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'contact (e-mail address)',
			`telephone` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'contact (telephone number)',
			`comments` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
			`status` enum('unallocated', 'pending', 'agreed', 'refused') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'unallocated' COMMENT 'performer status',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'data for performers'"); // $dbtables_fu_performers sets to true if we succeeded creating a table
		
		// We check table creation
		if ( $dbtables_fu_performers == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"fu_performers" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist .= "fu_performers"; // Append table name to fail-list
		} elseif ( $dbtables_fu_performers != FALSE )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"fu_performers" // Table name
			), FALSE);
		}
		/* Free university performers table */
		
		/* Free university performers <-> users relational table */
		// Make relational links between fu_performers.id (performer_id) and users.id (user_id)
		$dbtables_fu_perf_user_relation = FALSE; // We failed creating the table first
		$dbtables_fu_perf_user_relation = $Cmysql->Query("CREATE TABLE IF NOT EXISTS fu_perf_user_relation (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing relation ID',
			`user_id` int(10) NOT NULL COMMENT 'user id (users.id)',
			`performer_id` int(10) NOT NULL COMMENT 'performer id (fu_performers.id)',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'relational table between users and performers'"); // $dbtables_fu_perf_user_relation sets to true if we succeeded creating a table
		
		// We check table creation
		if ( $dbtables_fu_perf_user_relation == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"fu_perf_user_relation" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist .= "fu_perf_user_relation"; // Append table name to fail-list
		} elseif ( $dbtables_fu_perf_user_relation != FALSE )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"fu_perf_user_relation" // Table name
			), FALSE);
		}
		/* Free university performers <-> users relational table */
		
		/* Free university 2 lectures table */
		// Stores information about lectures
		$dbtables_fu2_lectures = FALSE; // We failed creating the table first
		$dbtables_fu2_lectures = $Cmysql->Query("CREATE TABLE IF NOT EXISTS fu2_lectures (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
			`lecture_name` varchar(255) NOT NULL COMMENT 'name of the lecture',
			`lecturer` varchar(255) NOT NULL COMMENT 'name of the lecturer',
			`hour1` enum('yes', 'no') NOT NULL DEFAULT 'no' COMMENT 'lecture takes place in hour #1',
			`hour2` enum('yes', 'no') NOT NULL DEFAULT 'no' COMMENT 'lecture takes place in hour #2',
			`hour3` enum('yes', 'no') NOT NULL DEFAULT 'no' COMMENT 'lecture takes place in hour #3',
			`hour4` enum('yes', 'no') NOT NULL DEFAULT 'no' COMMENT 'lecture takes place in hour #4',
			`limit1` int(3) NULL COMMENT 'student limit for hour #1',
			`limit2` int(3) NULL COMMENT 'student limit for hour #2',
			`limit3` int(3) NULL COMMENT 'student limit for hour #3',
			`limit4` int(3) NULL COMMENT 'student limit for hour #4',
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'lecture information'"); // $dbtables_fu2_lectures sets to true if we succeeded creating a table
		
		// We check table creation
		if ( $dbtables_fu2_lectures == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"fu2_lectures" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist .= "fu2_lectures"; // Append table name to fail-list
		} elseif ( $dbtables_fu2_lectures != FALSE )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"fu2_lectures" // Table name
			), FALSE);
		}
		/* Free university 2 lectures table */
		
		/* Free university 3 survey table */
		// Stores information about the ending survey
		$dbtables_fu3_survey = FALSE; // We failed creating the table first
		$dbtables_fu3_survey = $Cmysql->Query("CREATE TABLE IF NOT EXISTS fu3_survey (
			`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
			`userid` int(10) NOT NULL COMMENT 'id of the user who took the survey',
			`tetszes` enum('1', '2', '3', '4','5') NOT NULL DEFAULT '1' COMMENT 'how much do you like FU',
			`ujra` enum('0', '1') NOT NULL DEFAULT '0' COMMENT 'do you want it next year',
			`hasznos` enum('1', '2', '3', '4','5') NOT NULL DEFAULT '1' COMMENT 'how much worthwhile was it for you',
			`liked` int(10) NULL COMMENT 'your most liked lecture (fu2_lectures.id)',
			`not_liked` int(10) NULL COMMENT 'your most not liked lecture (fu2_lectures.id)',
			`moreprograms` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'wether you want more programs - describing',
			`audience` enum('0', '1', '2', '3', '4', '5') NOT NULL DEFAULT '0' COMMENT 'rating of audience - 0 no lecture 1-5 rating',
			`tip` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'tip for the organizers',
			`boring` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'description if you were bored',
			`aim` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'description about what is the aim of the free university',
			`activia` enum('1', '2', '3') NOT NULL DEFAULT '1' COMMENT 'joke question :P',
			PRIMARY KEY (`id`),
			UNIQUE KEY `userid` (`userid`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'phase three survey information';"); // $dbtables_fu3_survey sets to true if we succeeded creating a table
		
		// We check table creation
		if ( $dbtables_fu3_survey == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_error", array(
				'TABLENAME'	=>	"fu3_survey" // Table name
			), FALSE);
			
			// We set the creation global error variable to false
			$tablecreation = FALSE;
			
			$tablelist .= "fu3_survey"; // Append table name to fail-list
		} elseif ( $dbtables_fu3_survey != FALSE )
		{
			// Give success
			$Ctemplate->useTemplate("install/ins_dbtables_success", array(
				'TABLENAME'	=>	"fu3_survey" // Table name
			), FALSE);
		}
		/* Free university 3 survey table */
		
		// Check global variable status
		if ( $tablecreation == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_dbtables_global_error", array(
				'TABLE_LIST'	=>	$tablelist // Tables list
			), FALSE);
		} elseif ( $tablecreation == TRUE )
		{
			// Give success and proceed form
			$Ctemplate->useStaticTemplate("install/ins_dbtables_global_success", FALSE);
		}
		
		$Ctemplate->useStaticTemplate("install/ins_fw_dbtables_foot", FALSE); // Frame footer
		break;
	case 6:
		// Administrator user generator - getting data
		
		
		
		if ( @$_POST['error_goback'] == "yes" ) // If user is redirected from step 2 because of an error
		{
			// We output the form with data returned (user doesn't have to enter it again)
			$Ctemplate->useTemplate("install/ins_adminusr", array(
				'ROOT_NAME'	=>	$_POST['root_name'], // Root username
				'ROOT_PASS'	=>	$_POST['root_pass'], // Password
				'ROOT_EMAIL'	=>	$_POST['root_email']  // E-mail address
				), FALSE);
		} else {
			// We output general form
			$Ctemplate->useTemplate("install/ins_adminusr", array(
				'ROOT_NAME'	=>	"root", // Root username (default)
				'ROOT_PASS'	=>	"", // Root password
				'ROOT_EMAIL'	=>	$_SERVER['SERVER_ADMIN'], // Root e-mail address (default)
				), FALSE); // Config file generator
		}
		break;
	case 7:
		// Registering admin user
		
		// First, we do a check whether any of the mandatory variables are NULL
		if ( $_POST['root_name'] == NULL ) // Database host
		{
			$Ctemplate->useTemplate("install/ins_adminusr_variable_error", array(
				'VARIABLE'	=>	"Root username", // Errornous variable name
				'ROOT_NAME'	=>	$_POST['root_name'], // Username (should be empty)
				'ROOT_PASS'	=>	$_POST['root_pass'], // Password
				'ROOT_EMAIL'	=>	$_POST['root_email'], // E-mail address
				), FALSE);
			exit; // We terminate the script
		}
		
		if ( $_POST['root_pass'] == NULL ) // Database user
		{
			$Ctemplate->useTemplate("install/ins_adminusr_variable_error", array(
				'VARIABLE'	=>	"Password", // Errornous variable name
				'ROOT_NAME'	=>	$_POST['root_name'], // Username
				'ROOT_PASS'	=>	$_POST['root_pass'], // Password (should be empty)
				'ROOT_EMAIL'	=>	$_POST['root_email'], // E-mail address
				), FALSE);
			exit; // We terminate the script
		}
		
		if ( $_POST['root_email'] == NULL ) // Database password
		{
			$Ctemplate->useTemplate("install/ins_adminusr_variable_error", array(
				'VARIABLE'	=>	"E-mail address", // Errornous variable name
				'ROOT_NAME'	=>	$_POST['root_name'], // Username
				'ROOT_PASS'	=>	$_POST['root_pass'], // Password
				'ROOT_EMAIL'	=>	$_POST['root_email'], // E-mail address (should be empty)
				), FALSE);
			exit; // We terminate the script
		}
		
		// At this point, every mandatory fields are set.
		// Registering admin user
		require('config.php'); // We initialize the config array (need to do this for database connection)
		$Cmysql->Connect(); // Now we can use the generic connect
		
		$adminreg = FALSE; // We failed registering the root first
		
		// $adminreg isn't FALSE if the admin user was registered
		// $adminreg is FALSE if the admin user registration failed
		
		$adminreg = $Cmysql->Query("INSERT INTO users(username, pwd, email, regdate, userLevel) VALUES ('" .
			$Cmysql->EscapeString($_POST['root_name']). "'," .
			"'" .md5($Cmysql->EscapeString($_POST['root_pass'])). "'," .
			"'" .$Cmysql->EscapeString($_POST['root_email']). "', " .time(). ", 4)"); // Will be true if we succeed
		
		if ( $adminreg == FALSE )
		{
			// Give error
			$Ctemplate->useTemplate("install/ins_adminusr_reg_error", array(
				'ROOT_NAME'	=>	$_POST['root_name'], // Username
				'ROOT_PASS'	=>	$_POST['root_pass'], // Password
				'ROOT_EMAIL'	=>	$_POST['root_email'] // E-mail address
			), FALSE);
		} elseif ( $adminreg != FALSE )
		{
			// Give success and proceed
			$Ctemplate->useTemplate("install/ins_adminusr_reg_success", array(
				'ROOT_NAME'	=>	$_POST['root_name'] // Username
			), FALSE);
		}
		
		$Cmysql->Disconnect(); // Close connection
		break;
	case 8:
		// Finish
		$Ctemplate->useStaticTemplate("install/ins_finish", FALSE); // Use install finish template
		break;
 }
?>