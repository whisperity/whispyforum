<?php
/**
 * WhispyForum
 * 
 * Framework loader for the environment of WhispyForum.
 * 
 * How to load the environment from a frontend script?
 * 		Just call require("includes/load.php"); and the framework will be loaded.
 * 
 * /includes/load.php
*/

// Scripts can load in normal or safe mode.
// To do it, you have to state
// define('REQUIRE_SAFEMODE', TRUE);
// BEFORE including load.php in the source of your frontent script.
if ( !defined('REQUIRE_SAFEMODE') )
	define('REQUIRE_SAFEMODE', FALSE);

// Define that the system is loaded.
define('WHISPYFORUM', TRUE);

if ( file_exists("config.php") === TRUE ) 
{
	require("config.php");
} elseif ( file_exists("config.php") === FALSE )
{
	die("Missing configuration file.");
}

// Load the required libraries.
require("includes/functions.php");
require("includes/language.php");
require("includes/module.php");
require("includes/mysql.php");
require("includes/template.php");
require("includes/user.php");

global $template, $sql, $user;
$template = new template;
$sql = new mysql( @$cfg['dbhost'], @$cfg['dbuser'], @$cfg['dbpass'], @$cfg['dbname'] );
$user = new user(0, FALSE);

// Load the core localization.
load_lang("core");

/* DEVELOPEMENT */
// PH, workaround: output HTTP POST and GET arrays
print "<h4>GET</h4>";
prettyVar($_GET, true);
print "<h4>POST</h4>";
prettyVar($_POST, true);
print "<h4>FILES</h4>";
prettyVar($_FILES, true);

print "<h4>SESSION</h4>";
prettyVar($_SESSION, true);
// print "<h4>SERVER</h4>";
// prettyVar($_SERVER, true);
// print "<h4>REQUEST</h4>";
// prettyVar($_REQUEST, true);
// print "<h4>ENVIRONMENT</h4>";
// prettyVar($_ENV, true);
// print "<h4>COOKIES</h4>";
// prettyVar($_COOKIE, true);
// print "<h4>Configuration</h4>";
// prettyVar($cfg, true);
// print "<h4>Localization</h4>";
// prettyVar($wf_lang, true);
/* DEVELOPEMENT */
$template->load_template("framework", TRUE);

print $template->parse_template("header", array(
	'HEADER'	=>	NULL,
	'GLOBAL_TITLE'	=>	config("global_title"),
	'THEME_NAME'	=>	( $user->get_value("theme") === USER_NO_KEY ? config("theme") : $user->get_value("theme") ) ));

// Create a stack for the left menubar.
$template->create_stack("left");
$left_bar = array();

// Check whether there is a userbox module somewhere in the module table.
// If there isn't, we forcedly load such module (to prevent locking out users) to the top of the left menubar.
$sql->query("SELECT `id` FROM `modules` WHERE `module`='userbox';");
if ( $sql->num_rows() === 0 )
{
	echo "There is no userbox found. Added forced userbox to prevent lockout.";
	$left_bar[] = array('id'	=>	0,	'module'	=>	'userbox');
}

// Load all the modules in align order from the database and then execute them.
$sql->query("SELECT `id`, `module` FROM `modules` WHERE `side`='left' ORDER BY `align` ASC;");
while ( $module = $sql->fetch_array() )
	$left_bar[] = $module;

if ( REQUIRE_SAFEMODE )
	$left_bar = array();

foreach ( $left_bar as &$bar_entry )
{
	$current_module = new module($bar_entry['id'], $bar_entry['module']);
	$mod_output = $current_module->execute();
	
	// Add the module output into the left stack.
	$template->add_to_stack($mod_output, "left");
	
	unset($current_module);
}

// Output the left menubar and set the pointer to the center part.
print $template->parse_template("left", array(
	'LEFT'	=>	( !REQUIRE_SAFEMODE ? $template->get_stack("left") : NULL ) ));
print $template->parse_template("center", NULL);

function footer()
{
	/**
	 * The footer() function generates the footer of the output
	 * after the frontend code generated the center.
	*/
	
	global $template, $sql, $user, $localization;
	
	// Just as we did the left menubar earlier, we do the right menubar.
	$template->create_stack("right");
	$right_bar = array();
	
	$sql->query("SELECT `id`, `module` FROM `modules` WHERE `side`='right' ORDER BY `align` ASC;");
	while ( $module = $sql->fetch_array() )
		$right_bar[] = $module;
	
	if ( REQUIRE_SAFEMODE )
		$right_bar = array();
	
	foreach ( $right_bar as &$bar_entry )
	{
		$current_module = new module($bar_entry['id'], $bar_entry['module']);
		$mod_output = $current_module->execute();
		
		// Add the module output into the left stack.
		$template->add_to_stack($mod_output, "right");
		
		unset($current_module);
	}
	
	// Outputting the right menubar's template to the browser automatically closes the center cell.
	print $template->parse_template("right", array(
		'RIGHT'	=>	( !REQUIRE_SAFEMODE ? $template->get_stack("right") : NULL ) ));
	
	// Generate the footer.
	print $template->parse_template("footer", array(
		'FOOTER'	=>	NULL ));
	
	prettyVar($user);
	prettyVar($sql);
	prettyVar($template);
	prettyVar($localization);
	// Unset the global classes and finalize execution.
	unset($user);
	unset($sql);
	unset($template);
	unset($localization);
	exit;
}

// Footer will always be executed when shutting down.
register_shutdown_function('footer');
?>