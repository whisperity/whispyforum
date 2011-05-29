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
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
		'BODY'	=>	"{LANG_REQUIRES_ADMIN}", // Error text
		'ALT'	=>	"{LANG_PERMISSIONS_ERROR}" // Alternate picture text
	), FALSE ); // We give an unavailable error
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
	/* * MENU MANAGING * */
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
					
					// Query the number of menu entries in the menu
					$m_NumOfItems = mysql_fetch_row($Cmysql->Query("SELECT COUNT(*) FROM menu_entries WHERE menu_id=" .$Cmysql->EscapeString($_POST['menu_id'])));
					
					if ( $m_NumOfItems[0] == 0 )
					{
						// If there isn't any menu entries
						
						// We delete the menu immediately (don't need confirmation)
						$deleteMenu = $Cmysql->Query("DELETE FROM menus WHERE id=" .$Cmysql->EscapeString($_POST['menu_id'])); // $deleteMenu is TRUE if the query was executed, FALSE if there were errors
						
						if ( $deleteMenu == FALSE )
						{
							// If there were errors deleting the menu
							$Ctemplate->useTemplate("errormessage", array(
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
								'TITLE'	=>	"{LANG_MENUS_MENU_DELETE_ERROR}", // Error title
								'BODY'	=>	"", // Error text
								'ALT'	=>	"{LANG_SQL_EXEC_ERROR}" // Alternate picture text
							), FALSE ); // We give an error
						} elseif ( $deleteMenu == TRUE )
						{
							// If we succeeded deleting the menu
							$Ctemplate->useTemplate("successbox", array(
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
								'TITLE'	=>	"{LANG_MENUS_MENU_DELETE_SUCESS}", // Success title
								'BODY'	=>	"{LANG_MENUS_MENU_DELETE_1}", // Success text
								'ALT'	=>	"{LANG_SQL_EXEC_SUCCESS}" // Alternate picture text
							), FALSE ); // We give a success message
							
							// Back form
							$Ctemplate->useStaticTemplate("admin/menus_delmenu_goback", FALSE);
						}
					} elseif ( $m_NumOfItems[0] > 0 )
					{
						// If there is at least one menu entry,
						// we prompt for confirmation
						
						$Ctemplate->useTemplate("admin/menus_delmenu_confirm", array(
							'M_NAME'	=>	$menuName[0], // Name of the menu
							'MENU_ID'	=>	$_POST['menu_id'] // Menu ID
						), FALSE);
					}
				} else {
					// Give error
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
						'TITLE'	=>	"{LANG_MISSING_PARAMETERS}", // Error title
						'BODY'	=>	"{LANG_MISSING_PARAMETERS_BODY}", // Error text
						'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
					), FALSE ); // We give an error
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
							'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
							'TITLE'	=>	"{LANG_MENUS_MENU_DELETE_ERROR}", // Error title
							'BODY'	=>	"", // Error text
							'ALT'	=>	"{LANG_SQL_EXEC_ERROR}" // Alternate picture text
						), FALSE ); // We give an error
					} elseif ( $deleteMenu == TRUE )
					{
						// If the menu could be deleted, we delete the entries.
						$deleteItems = $Cmysql->Query("DELETE FROM menu_entries WHERE menu_id=" .$Cmysql->EscapeString($_POST['menu_id'])); // $deleteItems is TRUE if the query was executed, FALSE if there were errors
						
						$Ctemplate->useTemplate("successbox", array(
							'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
							'TITLE'	=>	"{LANG_MENUS_MENU_DELETE_SUCESS}", // Success title
							'BODY'	=>	"{LANG_MENUS_MENU_DELETE_1}", // Success text
							'ALT'	=>	"{LANG_SQL_EXEC_SUCCESS}" // Alternate picture text
						), FALSE ); // We give a success message
						
						if ( $deleteItems == FALSE )
						{
							// If we failed to delete the items
							$Ctemplate->useTemplate("messagebox", array(
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
								'TITLE'	=>	"{LANG_MENUS_MENU_DELETE_ITEMSNOTDELETED}", // Error title
								'BODY'	=>	"{LANG_MENUS_MENU_DELETE_ITEMSNOTDELETED_BODY}", // Error text
								'ALT'	=>	"{LANG_SQL_EXEC_ERROR}" // Alternate picture text
							), FALSE ); // We give a message (orange box)
						} elseif ( $deleteItems == TRUE )
						{
							// If we succeeded deleting the items
							$Ctemplate->useTemplate("successbox", array(
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
								'TITLE'	=>	"{LANG_MENUS_MENU_DELETE_ITEMSDELETED}", // Success title
								'BODY'	=>	"{LANG_MENUS_MENU_DELETE_ITEMSDELETED_BODY}", // Success text
								'ALT'	=>	"{LANG_SQL_EXEC_SUCCESS}" // Alternate picture text
							), FALSE ); // We give a success message
						}
						
						// Back form
						$Ctemplate->useStaticTemplate("admin/menus_delmenu_goback", FALSE);
					}
				} else {
					// Give error
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
						'TITLE'	=>	"{LANG_MISSING_PARAMETERS}", // Error title
						'BODY'	=>	"{LANG_MISSING_PARAMETERS_BODY}", // Error text
						'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
					), FALSE ); // We give an error
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
						'VARIABLE'	=>	"{LANG_MENUS_TITLE}", // Errornous variable name
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
						'VARIABLE'	=>	"{LANG_MENUS_ALIGN_POSITION}", // Errornous variable name
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
						'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
						'TITLE'	=>	"{LANG_MISSING_PARAMETERS}", // Error title
						'BODY'	=>	"{LANG_MISSING_PARAMETERS_BODY}", // Error text
						'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
					), FALSE ); // We give an error
				}
				break;
			case "edit_menu_do":
				// Do menu edition (SQL)
				
				// Check if we were passed with the menu ID
				if ( isset($_POST['menu_id']) )
				{
					// Check whether every required variables were entered (and wasn't deleted while editing)
					if ( $_POST['title'] == NULL ) // Menu header
					{
						$Ctemplate->useTemplate("admin/menus_edit_variable_error", array(
							'MENU_ID'	=>	$_POST['menu_id'], // Menu ID
							'VARIABLE'	=>	"{LANG_MENUS_TITLE}", // Errornous variable name
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
							'VARIABLE'	=>	"{LANG_MENUS_ALIGN_POSITION}", // Errornous variable name
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
						// Failed to edit the menu
						$Ctemplate->useTemplate("admin/menus_edit_error", array(
							'MENU_ID'	=>	$_POST['menu_id'], // Menu ID
							'TITLE'	=>	$_POST['title'], // Header
							'ALIGN_POS'	=>	$_POST['align_pos'], // Align position
							'SIDE'	=>	$_POST['side'] // Menu side
						), FALSE); // Output a retry form
					} elseif ( $mEdit == TRUE )
					{
						// Edited the menu
						$Ctemplate->useTemplate("admin/menus_edit_success", array(
							'TITLE'	=>	$_POST['title'] // Menu title
						), FALSE); // Output a success form
					}
				} else {
					// Give error
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
						'TITLE'	=>	"{LANG_MISSING_PARAMETERS}", // Error title
						'BODY'	=>	"{LANG_MISSING_PARAMETERS_BODY}", // Error text
						'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
					), FALSE ); // We give an error
				}
				break;
			/* MENU EDITION */
			/* ------------------- */
			/* ITEM LISIING */
			case "list_items":
				// List items of a selected menu
				
				// Check if we were passed with the menu ID
				if ( isset($_POST['menu_id']) )
				{
					// Query down the menus (we're not fetching here)
					$menuEntries = $Cmysql->Query("SELECT * FROM menu_entries WHERE menu_id=" .
						$Cmysql->EscapeString($_POST['menu_id']));
					
					// Query the title of the menu
					$menuData = mysql_fetch_assoc($Cmysql->Query("SELECT id, header FROM menus WHERE id=" .
						$Cmysql->EscapeString($_POST['menu_id'])));
					
					// Count menu items
					$m_NumOfItems = mysql_fetch_row($Cmysql->Query("SELECT COUNT(*) FROM menu_entries WHERE menu_id=" . $Cmysql->EscapeString($_POST['menu_id'])));
					
					$Ctemplate->useTemplate("admin/menus_listentries_header", array(
						'M_ID'	=>	$menuData['id'], // Menu ID
						'M_HEADER'	=>	$menuData['header'], // Menu title
						'M_NUM_ITEMS'	=>	$m_NumOfItems[0] // Number of entries
					), FALSE); // Header
					
					while ( $eRow = mysql_fetch_assoc($menuEntries) )
					{
						// We output every menu entry
						
						// Declare whether the link is external or internal
						// First, we explode the href by the / characters
						$hrExploded = explode('/', $eRow['href']);
						
						// Define whether the link is internal or external
						$hrefType = '{LANG_MENUS_INTERNAL}'; // The link is internal by default
						
						// Check for HTTP links
						if ( in_array('http:', $hrExploded) )
						{
							$hrefType = '{LANG_MENUS_EXTERNAL}'; // If it has HTTP in it, the link is external
						}
						
						// Now, $hrefType is 'INTERNAL' or 'EXTERNAL'
						
						$Ctemplate->useTemplate("admin/menus_listentries_entry", array(
							'E_LABEL'	=>	$eRow['label'], // Entry label
							'E_HREF'	=>	$eRow['href'], // Entry link target
							'E_LINK_TYPE'	=>	$hrefType, // Type of the link (formatted to be 'Internal' or 'External')
							'E_ID'	=>	$eRow['id'] // ID of menu entry
						), FALSE); // Generate table row of one entry
					}
					
					$Ctemplate->useStaticTemplate("admin/menus_listentries_footer", FALSE); // Footer
				} else {
					// Give error
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
						'TITLE'	=>	"{LANG_MISSING_PARAMETERS}", // Error title
						'BODY'	=>	"{LANG_MISSING_PARAMETERS_BODY}", // Error text
						'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
					), FALSE ); // We give an error
				}
				break;
			/* ITEM LISTING */
			/* ------------------- */
			/* ITEM DELETION */
			case "delete_entry":
				// Delete entry
				
				// First, we check if there's a present entry ID variable
				if ( isset($_POST['entry_id']) )
				{
					// Query menu id
					$menuID = mysql_fetch_assoc($Cmysql->Query("SELECT menu_id FROM menu_entries WHERE id=" .$Cmysql->EscapeString($_POST['entry_id'])));
					
					// Delete the entry
					$deleteEntry = $Cmysql->Query("DELETE FROM menu_entries WHERE id=" .$Cmysql->EscapeString($_POST['entry_id'])); // $deleteEntry is TRUE if the query was executed, FALSE if there were errors
					
					if ( $deleteEntry == FALSE )
					{
						// If there were errors deleting the menu
						$Ctemplate->useTemplate("errormessage", array(
							'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
							'TITLE'	=>	"{LANG_MENUS_ENTRY_DELETE_ERROR}", // Error title
							'BODY'	=>	"", // Error text
							'ALT'	=>	"{LANG_SQL_EXEC_ERROR}" // Alternate picture text
						), FALSE ); // We give an error
					} elseif ( $deleteEntry == TRUE )
					{
						$Ctemplate->useTemplate("successbox", array(
							'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
							'TITLE'	=>	"{LANG_MENUS_ENTRY_DELETE_SUCESS}", // Success title
							'BODY'	=>	"{LANG_MENUS_ENTRY_DELETE_1}", // Success text
							'ALT'	=>	"{LANG_SQL_EXEC_SUCCESS}" // Alternate picture text
						), FALSE ); // We give a success message
						
						// Back form
						$Ctemplate->useTemplate("admin/menus_delentry_goback", array(
							'MENU_ID'	=>	$menuID['menu_id'] // Menu ID
						), FALSE);
					}
				} else {
					// Give error
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
						'TITLE'	=>	"{LANG_MISSING_PARAMETERS}", // Error title
						'BODY'	=>	"{LANG_MISSING_PARAMETERS_BODY}", // Error text
						'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
					), FALSE ); // We give an error
				}
				break;
			/* ITEM DELETION */
			/* ------------------- */
			/* ITEM CREATION */
			case "create_entry":
				// Entry creation (giving form)
				
				if ( isset($_POST['menu_id']) )
				{
					$menuHeader = mysql_fetch_row($Cmysql->Query("SELECT header FROM menus WHERE id=" .
						$Cmysql->EscapeString($_POST['menu_id'])));
					
					if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
					{
						// We output the form with data returned (user doesn't have to enter it again)
						$Ctemplate->useTemplate("admin/menus_createentry_form", array(
							'LABEL'	=>	$_POST['label'], // Entry label
							'HREF'	=>	$_POST['href'], // Link target
							'M_HEADER'	=>	$menuHeader[0], // Menu header
							'MENU_ID'	=>	$_POST['menu_id'] // Menu ID
						), FALSE);
					} else {
						// We output general form
						$Ctemplate->useTemplate("admin/menus_createentry_form", array(
							'LABEL'	=>	"", // Entry label
							'HREF'	=>	"", // Link target
							'M_HEADER'	=>	$menuHeader[0], // Menu header
							'MENU_ID'	=>	$_POST['menu_id'] // Menu ID
						), FALSE);
					}
				} else {
					// Give error
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
						'TITLE'	=>	"{LANG_MISSING_PARAMETERS}", // Error title
						'BODY'	=>	"{LANG_MISSING_PARAMETERS_BODY}", // Error text
						'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
					), FALSE ); // We give an error
				}
				
				break;
			case "create_entry_do":
				// Create the new entry (SQL)
				
				// First, we check whether every required variables were entered
				if ( $_POST['label'] == NULL ) // Entry label
				{
					$Ctemplate->useTemplate("admin/menus_createentry_variable_error", array(
						'VARIABLE'	=>	"{LANG_MENUS_LABEL}", // Missing variable's name
						'LABEL'	=>	$_POST['label'], // Entry label (should be empty)
						'HREF'	=>	$_POST['href'], // Link target
						'MENU_ID'	=>	$_POST['menu_id'] // Menu ID
					), FALSE);
					
					// We terminate the script
					$Ctemplate->useStaticTemplate("admin/admin_foot", FALSE); // Footer
					DoFooter();
					exit;
				}
				
				if ( $_POST['href'] == NULL ) // URL
				{
					$Ctemplate->useTemplate("admin/menus_createentry_variable_error", array(
						'VARIABLE'	=>	"{LANG_URL}", // Missing variable's name
						'LABEL'	=>	$_POST['label'], // Entry label
						'HREF'	=>	$_POST['href'], // Link target (should be empty)
						'MENU_ID'	=>	$_POST['menu_id'] // Menu ID
					), FALSE);
					
					// We terminate the script
					$Ctemplate->useStaticTemplate("admin/admin_foot", FALSE); // Footer
					DoFooter();
					exit;
				}
				
				// Every variable has value, do the SQL query.
				$eCreate = $Cmysql->Query("INSERT INTO menu_entries(menu_id, label, href) VALUES('" .
					$Cmysql->EscapeString($_POST['menu_id'])."', '".
					$Cmysql->EscapeString($_POST['label'])."', '".
					$Cmysql->EscapeString($_POST['href']). "')");
				
				// $eCreate is TRUE if we succeeded
				// $eCreate is FALSE if we failed
				
				if ( $eCreate == FALSE )
				{
					// Failed to create the entry
					$Ctemplate->useTemplate("admin/menus_createentry_error", array(
						'LABEL'	=>	$_POST['label'], // Entry label
						'HREF'	=>	$_POST['href'], // Link target
						'MENU_ID'	=>	$_POST['menu_id'] // Menu ID
					), FALSE); // Output a retry form
				} elseif ( $eCreate == TRUE )
				{
					// Created the entry
					$Ctemplate->useTemplate("admin/menus_createentry_success", array(
						'LABEL'	=>	$_POST['label'], // Entry label
						'M_ID'	=>	$_POST['menu_id'] // Menu ID
					), FALSE); // Output a success form
				}
				break;
			/* ITEM CREATION */
			/* ------------------- */
			/* ITEM EDITION */
			case "edit_entry":
				// Entry edition (giving form)
				
				// First, we check if there's a present menu ID variable
				if ( isset($_POST['entry_id']) )
				{
					// Do forms
					$entryProperties = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM menu_entries WHERE id=" .$Cmysql->EscapeString($_POST['entry_id'])));
					
					$menuHeader = mysql_fetch_row($Cmysql->Query("SELECT header FROM menus WHERE id=" .
						$Cmysql->EscapeString($entryProperties['menu_id'])));
					
					if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
					{
						// We output the form with data returned (user doesn't have to enter it again)
						$Ctemplate->useTemplate("admin/menus_editentry_form", array(
							'M_HEADER'	=>	$menuHeader[0], // Menu header
							'ENTRY_ID'	=>	$_POST['entry_id'], // Entry ID
							'LABEL'	=>	$_POST['label'], // Entry label
							'HREF'	=>	$_POST['href'] // Link target
						), FALSE);
					} else {
						// We output general form
						$Ctemplate->useTemplate("admin/menus_editentry_form", array(
							'M_HEADER'	=>	$menuHeader[0], // Menu header
							'ENTRY_ID'	=>	$_POST['entry_id'], // Entry ID
							'LABEL'	=>	$entryProperties['label'], // Entry label
							'HREF'	=>	$entryProperties['href'] // URL
						), FALSE);
					}
				} else {
					// Give error
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
						'TITLE'	=>	"{LANG_MISSING_PARAMETERS}", // Error title
						'BODY'	=>	"{LANG_MISSING_PARAMETERS_BODY}", // Error text
						'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
					), FALSE ); // We give an error
				}
				break;
			case "edit_entry_do":
				// Do entry edition (SQL)
				
				// Check if we were passed with the menu ID
				if ( isset($_POST['entry_id']) )
				{
					// Check whether every required variables were entered (and wasn't deleted while editing)
					if ( $_POST['label'] == NULL ) // Entry label
					{
						$Ctemplate->useTemplate("admin/menus_editentry_variable_error", array(
							'VARIABLE'	=>	"{LANG_MENUS_LABEL}", // Missing variable's name
							'LABEL'	=>	$_POST['label'], // Entry label (should be empty)
							'HREF'	=>	$_POST['href'], // Link target
							'ENTRY_ID'	=>	$_POST['entry_id'] // Entry ID
						), FALSE);
						
						// We terminate the script
						$Ctemplate->useStaticTemplate("admin/admin_foot", FALSE); // Footer
						DoFooter();
						exit;
					}
					
					if ( $_POST['href'] == NULL ) // URL
					{
						$Ctemplate->useTemplate("admin/menus_editentry_variable_error", array(
							'VARIABLE'	=>	"{LANG_URL}", // Missing variable's name
							'LABEL'	=>	$_POST['label'], // Entry label
							'HREF'	=>	$_POST['href'], // Link target (should be empty)
							'ENTRY_ID'	=>	$_POST['entry_id'] // Entry ID
						), FALSE);
						
						// We terminate the script
						$Ctemplate->useStaticTemplate("admin/admin_foot", FALSE); // Footer
						DoFooter();
						exit;
					}
					
					
					// Every variable has value, do the SQL query.
					$eEdit = $Cmysql->Query("UPDATE menu_entries SET label='" .
						$Cmysql->EscapeString($_POST['label'])."', href='".
						$Cmysql->EscapeString($_POST['href'])."' WHERE id='" .
						$Cmysql->EscapeString($_POST['entry_id']). "'");
					
					// $eEdit is TRUE if we succeeded
					// $eEdit is FALSE if we failed
					$eEdit = TRUE;
					if ( $eEdit == FALSE )
					{
						// Failed to modify the entry
						$Ctemplate->useTemplate("admin/menus_editentry_error", array(
							'LABEL'	=>	$_POST['label'], // Entry label
							'HREF'	=>	$_POST['href'], // Link target
							'ENTRY_ID'	=>	$_POST['entry_id'] // Entry ID
						), FALSE); // Output a retry form
					} elseif ( $eEdit == TRUE )
					{
						// Get the menu's ID
						$menuID = mysql_fetch_row($Cmysql->Query("SELECT menu_id FROM menu_entries WHERE id=" .$Cmysql->EscapeString($_POST['entry_id'])));
						
						// Modified the entry
						$Ctemplate->useTemplate("admin/menus_editentry_success", array(
							'LABEL'	=>	$_POST['label'], // Entry label
							'MENU_ID'	=>	$menuID[0] // Menu ID
						), FALSE); // Output a success form
					}
				} else {
					// Give error
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
						'TITLE'	=>	"{LANG_MISSING_PARAMETERS}", // Error title
						'BODY'	=>	"{LANG_MISSING_PARAMETERS_BODY}", // Error text
						'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
					), FALSE ); // We give an error
				}
				break;
			/* ITEM EDITION */
		}
		break;
	/* * MENU MANAGING * */
	/* --------------------------- */
}
}
$Ctemplate->useStaticTemplate("admin/admin_foot", FALSE); // Footer
DoFooter();
?>
