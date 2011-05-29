<?php
 /**
 * WhispyForum language file - bootlocal.php
 * 
 * Boot-time (English) localization
 * 
 * WhispyForum
 */

 /**
 *  *********************************************************
 *  * IMPORTANT! PLEASE READ BEFORE TRANSLATING! IMPORTANT! *
 *  *********************************************************
 * 
 * DO NOT TRANSLATE THIS FILE!
 *
 *  *********************************************************
 *  * IMPORTANT! PLEASE READ BEFORE TRANSLATING! IMPORTANT! *
 *  *********************************************************
 */

global $wf_lang; // Language global array

/* Boot-time (English) localization */
$wf_lang = array(
	/* General */
	'{LANG_YES}'	=>	"Yes",
	'{LANG_NO}'	=>	"No",
	'{LANG_USING_LOWERCASE}'	=>	"using",
	'{LANG_USERNAME_LOWERCASE}'	=>	"username",
	'{LANG_PASSWORD_LOWERCASE}'	=>	"password",
	'{LANG_FILE_UNAVAILABLE}'	=>	"File unavailable",
	'{LANG_COULD_NOT_BE_MADE}'	=>	"could not be made.",
	
	/* SQL */
	'{LANG_SQL_ERROR}'	=>	"mySQL error",
	'{LANG_SQL_EXEC_ERROR}'	=>	"Query execution error",
	'{LANG_SQL_THEQUERY}'	=>	"The specified query",
	'{LANG_SQL_EXEC_SUCCESS}'	=>	"Query execution success",
	'{LANG_EXECUTED_SUCCESSFULLY}'	=>	"executed successfully.",
	'{LANG_SQL_NOCONNECTION}'	=>	"Unable to connect!",
	'{LANG_SQL_DBCONN_TO}'	=>	"Database connection to",
	'{LANG_SQL_DBSELECT_ERROR}'	=>	"Unable to select database!",
	'{LANG_SQL_THEDATABASE}'	=>	"The specified database",
	'{LANG_SQL_COULD_NOT_BE_SELECTED}'	=>	"could not be selected.",
	'{LANG_SQL_COULD_NOT_BE_PROCESSED}'	=>	"could not be processed.",
	'{LANG_SQL_ERROR_MSG_WAS}'	=>	"The mySQL error message was",
	
	/* Loader script */
	'{LANG_LOAD_CORRUPTION}'	=>	"Corruption!",
	'{LANG_LOAD_CORRUPTION_BODY}'	=>	"WhispyForum appears to be installed, however, the configuration file is corrupted or incosistent. It's advised to reinstall the system. ".'You can install it by clicking <a href="install.php" alt="Install WhispyForum">here</a> and running the install script.',
	'{LANG_LOAD_CORRUPTION_ALT}'	=>	"Corrupt configuration",
	'{LANG_LOAD_NOCFG}'	=>	"Configuration file not found!",
	'{LANG_LOAD_NOCFG_BODY}'	=>	"The site's configuration file is missing. It usally means that the engine isn't installed properly. Without configuration, the engine cannot be used, because it can't connect to the database. ".'You can install it by clicking <a href="install.php" alt="Install WhispyForum">here</a> and running the install script.',
	
	/* Template system */
	'{LANG_TEMPLATESYS_TEMP_MISSING}'	=>	"Template missing",
	'{LANG_TEMPLATESYS_TEMP_MISSING_BODY}'	=>	"The specified template file does not exist. This template cannot be displayed.",
);
?>
