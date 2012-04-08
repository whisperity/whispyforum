<?php
/**
 * WhispyForum
 * 
 * This is the module of menus shown at the sidebars of the framework.
 * Also, this script serves as a platform for us to understand modules.
 * 
 * What are modules?
 *		Modules are includable snippets of PHP code we load runtime.
 * 		Modules are valid, executable scripts just like this example menu.
 * 
 * How are modules called?
 * 		Modules are connected to the frontend via the module class. (-> /includes/module.php)
 * 		This class provides the frontend with a transparent layer to interact with modules.
 * 		
 * 		The frontend files load the modules with referencing a new instance of the module class (the 'new' construct).
 * 		Then, the class loads everything related to the module. Please read the documentation of that file too.
 * 		
 * 		Module snippets are executed with the $module->execute($part); command from the frontend.
 * 
 * Special variables in the module code
 * 		Because modules are executed within the namespace of the command above, there are some variables used by
 * 		the core code and thus having a "magical" meaning.
 * 		
 * 		$this:	the reference variable $this always refers to the instance from which the module was called
 * 		$part:	frontend codes can specify a $part variable (-> module::execute()) which helps us to put different
 * 					aspects of a module into one file
 * 		$ret:	because the module is loaded from a function, the concurrent execution can have a returned value
 * 					which can be further parsed by the frontend
 * 
 * Are global classes like $template, $sql and $user available in this context?
 * 		Yes, the global classes are available here too.
 * 
 * /modules/menu.php
*/

// Some dieout statements to prevent loading the module without appropriate framework.
if ( !defined("WHISPYFORUM") )
	die("Direct opening.");

if ( !isset($this) || !is_object($this) )
	die("Module loaded without module context.");

// The $part variable is available from the caller function.
switch ( $part )
{
	// The default $part statement should never contain any direct code.
	// We return TRUE so that we can check the module is present.
	case NULL:
	default:
		$ret = TRUE;
		break;
}
?>
