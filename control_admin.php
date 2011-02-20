<?php
 /**
 * WhispyForum script file - control_admin.php
 * 
 * Admin control panel. Usage: help administrator set the engine's properties.
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("admin/admin_head", FALSE); // Header

// We define the $site variable
$site = "";

// Get user's level
$uDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT userLevel FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'")); // We query the user's data

if ( $uDBArray['userLevel'] < 3 )
{
	// If the user does not have rights to see the admin panel
	$Ctemplate->useTemplate("errormessage", array(
		'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"Insufficient rights", // Error title
		'BODY'	=>	"This page requires you to have Administrator or higher rights.", // Error text
		'ALT'	=>	"User permissions error" // Alternate picture text
	), FALSE ); // We give an unaviable error
} elseif ( $uDBArray['userLevel'] >= 3 )
{
// If user has the rights, the panel is accessible

if ( isset($_POST['site']) )
{
	// If site is passed by POST
	// the site variable is the POSTed value
	
	$site = $_POST['site'];
} elseif ( !isset($_POST['site']) )
{
	// If the POSTed variable is NULL
	// we see if there's site variable GET
	
	if ( isset($_GET['site']) )
	{
		// If there is, site is the GET value
		$site = $_GET['site'];
	} elseif ( !isset($_GET['site']) )
	{
		// If not, site is NULL
		$site = NULL;
	}
}

// Now, the site variable is either NULL or set from HTTP POST/GET

switch ($site) // Outputs and scripts are based on the site variable
{
	case "menus":
		// Menu and menu item editor
		
		// The menu system also relies on individual "site" settings.
		$menu_subsite = NULL; // We make the subsite variable defined (by setting it NULL)
		if ( isset($_POST['m_subsite']) )
		{
			// If we passed the subsite parameter, set it
			$menu_subsite = $_POST['m_subsite'];
		}
		
		// Outputs and scripts are based on the subsite
		switch ( $menu_subsite )
		{
			case "menus":
			default:
				// List menus
				
				// We define two variables which will contain the generated
				// boxes of the menus
				$left_menus = NULL;
				$right_menus = NULL;
				
				// LEFT MENUS
				$lMenus_query = $Cmysql->Query("SELECT * FROM menus WHERE side='left' ORDER BY align ASC"); // Query left menus
				
				while ( $lMenus_row = mysql_fetch_assoc($lMenus_query) )
				{
					// Generate left menubar table cell content
					
					// Get the number of menu items of the current menu
					$ml_NumOfItems = mysql_fetch_row($Cmysql->Query("SELECT COUNT(*) FROM menu_entries WHERE menu_id=" .$lMenus_row['id']));
					
					
					$left_menus .= $Ctemplate->useTemplate("admin/menus_listmenus_form", array(
						'M_HEADER'	=>	$lMenus_row['header'], // Menu header
						'M_NUM_ITEMS'	=>	$ml_NumOfItems[0], // Number of items
						'M_ID'	=>	$lMenus_row['id'] // Menu ID
					), TRUE); // Append the current table into the stack variable
				}
				
				// RIGHT MENUS
				$rMenus_query = $Cmysql->Query("SELECT * FROM menus WHERE side='right' ORDER BY align ASC"); // Query left menus
				
				while ( $rMenus_row = mysql_fetch_assoc($rMenus_query) )
				{
					// Generate right menubar table cell content
					
					// Get the number of menu items of the current menu
					$mr_NumOfItems = mysql_fetch_row($Cmysql->Query("SELECT COUNT(*) FROM menu_entries WHERE menu_id=" .$rMenus_row['id']));
					
					
					$right_menus .= $Ctemplate->useTemplate("admin/menus_listmenus_form", array(
						'M_HEADER'	=>	$rMenus_row['header'], // Menu header
						'M_NUM_ITEMS'	=>	$mr_NumOfItems[0], // Number of items
						'M_ID'	=>	$rMenus_row['id'] // Menu ID
					), TRUE); // Append the current table into the stack variable
				}
				
				$Ctemplate->useTemplate("admin/menus_listmenus", array(
					'LEFT_MENUS'	=>	$left_menus, // Left menubar contents
					'RIGHT_MENUS'	=>	$right_menus // Right menubar contents
				), FALSE); // List out the menus (using the two created stack variables)
				
				$Ctemplate->useStaticTemplate("admin/menus_listmenus_create", FALSE); // Create new menu button
				break;
			/* MENU DELETION */
			case "delete_menu":
				// Menu deletion
				
				// First, we check if there's a present menu ID variable
				if ( isset($_POST['menu_id']) )
				{
					// Make the administrator confirm the deletion
					
					$menuName = mysql_fetch_row($Cmysql->Query("SELECT header FROM menus WHERE id=" .$Cmysql->EscapeString($_POST['menu_id']))); // Query the menu's name
					
					$Ctemplate->useTemplate("admin/menus_delmenu_confirm", array(
						'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
						'M_NAME'	=>	$menuName[0], // Name of the menu
						'MENU_ID'	=>	$_POST['menu_id'] // Menu ID
					), FALSE);
				} else {
					// Give error
					$Ctemplate->useTemplate("errormessage", array(
						'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
						'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
						'TITLE'	=>	"Missing parameters", // Error title
						'BODY'	=>	"One or more of the required parameters hadn't been passed.", // Error text
						'ALT'	=>	"Missing parameters" // Alternate picture text
					), FALSE ); // We give an unaviable error
				}
				break;
			case "delete_menu_confirmed":
				// Menu deletion (after administrator confirm, do the nasty work)
				
				// First, we check if there's a present menu ID variable
				if ( isset($_POST['menu_id']) )
				{
					// Delete the menu
					$deleteMenu = $Cmysql->Query("DELETE FROM menus WHERE id=" .$Cmysql->EscapeString($_POST['menu_id'])); // $deleteMenu is TRUE if the query was executed, FALSE if there were errors
					
					if ( $deleteMenu == FALSE )
					{
						// If there were errors deleting the menu
						$Ctemplate->useTemplate("errormessage", array(
							'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
							'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
							'TITLE'	=>	"The menu could not be deleted", // Error title
							'BODY'	=>	"", // Error text
							'ALT'	=>	"Query execution error" // Alternate picture text
						), FALSE ); // We give an error
					} elseif ( $deleteMenu == TRUE )
					{
						// If the menu could be deleted, we delete the entries.
						$deleteItems = $Cmysql->Query("DELETE FROM menu_entries WHERE menu_id=" .$Cmysql->EscapeString($_POST['menu_id'])); // $deleteItems is TRUE if the query was executed, FALSE if there were errors
						
						$Ctemplate->useTemplate("successbox", array(
							'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
							'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
							'TITLE'	=>	"Menu deleted", // Error title
							'BODY'	=>	"The menu was deleted successfully.", // Error text
							'ALT'	=>	"Query execution success" // Alternate picture text
						), FALSE ); // We give a success message
						
						if ( $deleteItems == FALSE )
						{
							// If we failed to delete the items
							$Ctemplate->useTemplate("messagebox", array(
								'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
								'TITLE'	=>	"The menu items could not be deleted", // Error title
								'BODY'	=>	"The menu was deleted, but the items failed to do so. This isn't really an issue, becuase when cleanupping, these orphan entries will be cleaned up.", // Error text
								'ALT'	=>	"Query execution error" // Alternate picture text
							), FALSE ); // We give a message (orange box)
						} elseif ( $deleteItems == TRUE )
						{
							// If we succeeded deleting the items
							$Ctemplate->useTemplate("successbox", array(
								'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
								'TITLE'	=>	"Menu items deleted", // Error title
								'BODY'	=>	"The menu's items were deleted successfully.", // Error text
								'ALT'	=>	"Query execution success" // Alternate picture text
							), FALSE ); // We give a success message
						}
						
						// Back form
						$Ctemplate->useStaticTemplate("admin/menus_delmenu_goback", FALSE);
					}
				} else {
					// Give error
					$Ctemplate->useTemplate("errormessage", array(
						'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
						'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
						'TITLE'	=>	"Missing parameters", // Error title
						'BODY'	=>	"One or more of the required parameters hadn't been passed.", // Error text
						'ALT'	=>	"Missing parameters" // Alternate picture text
					), FALSE ); // We give an unaviable error
				}
				break;
			/* MENU DELETION */
			/* ------------------- */
			/* MENU CREATION */
			case "create_menu":
				// Menu creation (giving form)
				
				if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
				{
					// We output the form with data returned (user doesn't have to enter it again)
					$Ctemplate->useTemplate("admin/menus_create_form", array(
						'TITLE'	=>	$_POST['title'], // Menu header
						'ALIGN_POS'	=>	$_POST['align_pos'], // Vertical align position
						'LEFT_SIDE'	=>	($_POST['side']=="left" ? " checked" : NULL), // Check left side if we were set to left side, otherwise send NULL
						'RIGHT_SIDE'	=>	($_POST['side']=="right" ? " checked" : NULL) // Check right side if we were set to right side, otherwise send NULL
					), FALSE);
				} else {
					// We output general form
					$Ctemplate->useTemplate("admin/menus_create_form", array(
						'TITLE'	=>	"", // Menu header
						'ALIGN_POS'	=>	"", // Vertical align position
						'LEFT_SIDE'	=>	" checked", // Left side radio button (checked)
						'RIGHT_SIDE'	=>	"" // Right side radio button (unchecked)
					), FALSE);
				}
				break;
			case "create_menu_do":
				// Create the new menu (SQL)
				
				// First, we check whether every required variables were entered
				if ( $_POST['title'] == NULL ) // Menu header
				{
					$Ctemplate->useTemplate("admin/menus_create_variable_error", array(
						'VARIABLE'	=>	"Title", // Errornous variable name
						'TITLE'	=>	$_POST['title'], // Header (should be empty)
						'ALIGN_POS'	=>	$_POST['align_pos'], // Align position
						'SIDE'	=>	$_POST['side'], // Menu side
					), FALSE);
					
					// We terminate the script
					$Ctemplate->useStaticTemplate("admin/admin_foot", FALSE); // Footer
					DoFooter();
					exit;
				}
				
				if ( $_POST['align_pos'] == NULL ) // Align position
				{
					$Ctemplate->useTemplate("admin/menus_create_variable_error", array(
						'VARIABLE'	=>	"Align position", // Errornous variable name
						'TITLE'	=>	$_POST['title'], // Header
						'ALIGN_POS'	=>	$_POST['align_pos'], // Align position (should be empty)
						'SIDE'	=>	$_POST['side'] // Menu side
					), FALSE);
					
					// We terminate the script
					$Ctemplate->useStaticTemplate("admin/admin_foot", FALSE); // Footer
					DoFooter();
					exit;
				}
				
				// We don't have to check the side variable.
				// Left side is automatically checked on loading the form
				// and $_POST hacker admins deserve what they did...
				
				// Every variable has value, do the SQL query.
				$mCreate = $Cmysql->Query("INSERT INTO menus(header, align, side) VALUES('" .
					$Cmysql->EscapeString($_POST['title'])."', '".
					$Cmysql->EscapeString($_POST['align_pos'])."', '".
					$Cmysql->EscapeString($_POST['side']). "')");
				
				// $mCreate is TRUE if we succeeded
				// $mCreate is FALSE if we failed
				
				if ( $mCreate == FALSE )
				{
					// Failed to create the menu
					$Ctemplate->useTemplate("admin/menus_create_error", array(
						'TITLE'	=>	$_POST['title'], // Header
						'ALIGN_POS'	=>	$_POST['align_pos'], // Align position
						'SIDE'	=>	$_POST['side'] // Menu side
					), FALSE); // Output a retry form
				} elseif ( $mCreate == TRUE )
				{
					// Created the menu
					$Ctemplate->useTemplate("admin/menus_create_success", array(
						'TITLE'	=>	$_POST['title'] // Menu title
					), FALSE); // Output a success form
				}
				break;
			/* MENU CREATION */
			/* ------------------- */
			/* MENU EDITION */
			case "edit_menu":
				// Menu edition (giving form)
				
				// First, we check if there's a present menu ID variable
				if ( isset($_POST['menu_id']) )
				{
					// Do forms
					
					$menuProperties = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM menus WHERE id=" .
						$Cmysql->EscapeString($_POST['menu_id']))); // Query down menu properties
					
					$m_NumOfItems = mysql_fetch_row($Cmysql->Query("SELECT COUNT(*) FROM menu_entries WHERE menu_id=" .$menuProperties['id'])); // Count menu items
					
					if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
					{
						// We output the form with data returned (user doesn't have to enter it again)
						$Ctemplate->useTemplate("admin/menus_edit_form", array(
							'MENU_ID'	=>	$_POST['menu_id'], // Menu ID
							'TITLE'	=>	$_POST['title'], // Menu header
							'ALIGN_POS'	=>	$_POST['align_pos'], // Vertical align position
							'LEFT_SIDE'	=>	($_POST['side']=="left" ? " checked" : NULL), // Check left side if we were set to left side, otherwise send NULL
							'RIGHT_SIDE'	=>	($_POST['side']=="right" ? " checked" : NULL), // Check right side if we were set to right side, otherwise send NULL
							'M_NUM_ITEMS'	=>	$m_NumOfItems[0] // Number of items
						), FALSE);
					} else {
						// We output general form
						$Ctemplate->useTemplate("admin/menus_edit_form", array(
							'MENU_ID'	=>	$menuProperties['id'], // Menu ID
							'TITLE'	=>	$menuProperties['header'], // Menu header
							'ALIGN_POS'	=>	$menuProperties['align'], // Vertical align position
							'LEFT_SIDE'	=>	($menuProperties['side']=="left" ? " checked" : NULL), // Check left side if menu was set to left side, otherwise send NULL
							'RIGHT_SIDE'	=>	($menuProperties['side']=="right" ? " checked" : NULL), // Check right side if menu was set to right side, otherwise send NULL
							'M_NUM_ITEMS'	=>	$m_NumOfItems[0] // Number of items
						), FALSE);
					}
				} else {
					// Give error
					$Ctemplate->useTemplate("errormessage", array(
						'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
						'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
						'TITLE'	=>	"Missing parameters", // Error title
						'BODY'	=>	"One or more of the required parameters hadn't been passed.", // Error text
						'ALT'	=>	"Missing parameters" // Alternate picture text
					), FALSE ); // We give an unaviable error
				}
				break;
			case "edit_menu_do":
				// Do menu edition (SQL)
				
				// Check if we passed the menu ID
				if ( isset($_POST['menu_id']) )
				{
					// Check whether every required variables were entered (and wasn't deleted while editing)
					if ( $_POST['title'] == NULL ) // Menu header
					{
						$Ctemplate->useTemplate("admin/menus_edit_variable_error", array(
							'MENU_ID'	=>	$_POST['menu_id'], // Menu ID
							'VARIABLE'	=>	"Title", // Errornous variable name
							'TITLE'	=>	$_POST['title'], // Header (should be empty)
							'ALIGN_POS'	=>	$_POST['align_pos'], // Align position
							'SIDE'	=>	$_POST['side'], // Menu side
						), FALSE);
						
						// We terminate the script
						$Ctemplate->useStaticTemplate("admin/admin_foot", FALSE); // Footer
						DoFooter();
						exit;
					}
					
					if ( $_POST['align_pos'] == NULL ) // Align position
					{
						$Ctemplate->useTemplate("admin/menus_edit_variable_error", array(
							'MENU_ID'	=>	$_POST['menu_id'], // Menu ID
							'VARIABLE'	=>	"Align position", // Errornous variable name
							'TITLE'	=>	$_POST['title'], // Header
							'ALIGN_POS'	=>	$_POST['align_pos'], // Align position (should be empty)
							'SIDE'	=>	$_POST['side'] // Menu side
						), FALSE);
						
						// We terminate the script
						$Ctemplate->useStaticTemplate("admin/admin_foot", FALSE); // Footer
						DoFooter();
						exit;
					}
					
					// We don't have to check the side variable.
					// Left side is automatically checked on loading the form
					// and $_POST hacker admins deserve what they did...
					
					// Every variable has value, do the SQL query.
					$mEdit = $Cmysql->Query("UPDATE menus SET header='" .
						$Cmysql->EscapeString($_POST['title'])."', align='".
						$Cmysql->EscapeString($_POST['align_pos'])."', side='".
						$Cmysql->EscapeString($_POST['side']). "' WHERE id='" .
						$Cmysql->EscapeString($_POST['menu_id']). "'");
					
					// $mEdit is TRUE if we succeeded
					// $mEdit is FALSE if we failed
					
					if ( $mEdit == FALSE )
					{
						// Failed to create the menu
						$Ctemplate->useTemplate("admin/menus_edit_error", array(
							'MENU_ID'	=>	$_POST['menu_id'], // Menu ID
							'TITLE'	=>	$_POST['title'], // Header
							'ALIGN_POS'	=>	$_POST['align_pos'], // Align position
							'SIDE'	=>	$_POST['side'] // Menu side
						), FALSE); // Output a retry form
					} elseif ( $mEdit == TRUE )
					{
						// Created the menu
						$Ctemplate->useTemplate("admin/menus_edit_success", array(
							'TITLE'	=>	$_POST['title'] // Menu title
						), FALSE); // Output a success form
					}
				} else {
					// Give error
					$Ctemplate->useTemplate("errormessage", array(
						'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
						'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
						'TITLE'	=>	"Missing parameters", // Error title
						'BODY'	=>	"One or more of the required parameters hadn't been passed.", // Error text
						'ALT'	=>	"Missing parameters" // Alternate picture text
					), FALSE ); // We give an unaviable error
				}
				break;
			/* MENU EDITION */
			/* ------------------- */
			/* MENU SOMETHING */
		}
		break;
}
}
$Ctemplate->useStaticTemplate("admin/admin_foot", FALSE); // Footer
DoFooter();
?>