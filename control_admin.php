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
$uLvl = $Cuser->getLevel();

if ( $uLvl < 3 )
{
	// If the user does not have rights to see the admin panel
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
		'BODY'	=>	"{LANG_REQUIRES_ADMIN}", // Error text
		'ALT'	=>	"{LANG_PERMISSIONS_ERROR}" // Alternate picture text
	), FALSE ); // We give an unavailable error
} elseif ( $uLvl >= 3 )
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
	/* * CONFIGURATION * */
	case "config":
		// Setting site preferences (theme, language)
		// Parsing form input
		if ( isset($_POST['set_type']) )
		{
			if ( ( $_POST['set_type'] == "language" ) && ( isset($_POST['new_lang']) ) )
			{
				// Change the language in the database
				$Lmod = $Cmysql->Query("UPDATE config SET value='" .$Cmysql->EscapeString($_POST['new_lang']). "' WHERE variable='language'");
				
				// $Lmod is TRUE if we succeed and FALSE if we fail
				if ( $Lmod == FALSE )
				{
					// If we failed
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
						'TITLE'	=>	"{LANG_ADMINCFG_MODIFY_LANGUAGE_ERROR}", // Error title
						'BODY'	=>	"", // Error text
						'ALT'	=>	"{LANG_SQL_EXEC_ERROR}" // Alternate picture text
					), FALSE ); // We give an unavailable error
					
					$Ctemplate->useStaticTemplate("admin/siteprefs_back", FALSE); // Back button
				} elseif ( $Lmod == TRUE )
				{
					// If we succeeded
					$Ctemplate->useTemplate("successbox", array(
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_home.png", // House (user CP header)
						'TITLE'	=>	"{LANG_ADMINCFG_MODIFY_LANGUAGE_SUCCESS}", // Success title
						'BODY'	=>	"{LANG_ADMINCFG_MODIFY_LANGUAGE_SUCCESS_1}", // Success text
						'ALT'	=>	"{LANG_SQL_EXEC_SUCCESS}" // Alternate picture text
					), FALSE ); // We give a success message
					
					$Ctemplate->useStaticTemplate("admin/siteprefs_back", FALSE); // Back button
				}
			}
			
			if ( ( $_POST['set_type'] == "theme" ) && ( isset($_POST['new_theme']) ) )
			{
				// Change the theme in the database
				$Tmod = $Cmysql->Query("UPDATE config SET value='" .$Cmysql->EscapeString($_POST['new_theme']). "' WHERE variable='theme'");
				
				// $Tmod is TRUE if we succeed and FALSE if we fail
				if ( $Tmod == FALSE )
				{
					// If we failed
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
						'TITLE'	=>	"{LANG_ADMINCFG_MODIFY_THEME_ERROR}", // Error title
						'BODY'	=>	"", // Error text
						'ALT'	=>	"{LANG_SQL_EXEC_ERROR}" // Alternate picture text
					), FALSE ); // We give an unavailable error
					
					$Ctemplate->useStaticTemplate("admin/siteprefs_back", FALSE); // Back button
				} elseif ( $Tmod == TRUE )
				{
					// If we succeeded
					$Ctemplate->useTemplate("successbox", array(
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_home.png", // House (user CP header)
						'TITLE'	=>	"{LANG_ADMINCFG_MODIFY_THEME_SUCCESS}", // Success title
						'BODY'	=>	"{LANG_ADMINCFG_MODIFY_THEME_SUCCESS_1}", // Success text
						'ALT'	=>	"{LANG_SQL_EXEC_SUCCESS}" // Alternate picture text
					), FALSE ); // We give a success message
					
					$Ctemplate->useStaticTemplate("admin/siteprefs_back", FALSE); // Back button
				}
			}
			
			if ( $_POST['set_type'] == "other" )
			{
				// If we are setting the rest of the option
				
				// Check for missing but mandatory variables
				if ( $_POST['global_title'] == NULL )
				{
					$Ctemplate->useTemplate("admin/siteprefs_variable_error", array(
						'VARIABLE'	=>	"{LANG_ADMINCFG_TITLE}",
						'GLOBAL_TITLE'	=>	$_POST['global_title'],
						'SITE_HOST'	=>	$_POST['site_host'],
						'REGISTRATION'	=>	(@$_POST['registration'] == "on" ? "on" : "off"),
						'MODEL_FORUM'	=>	(@$_POST['module_forum'] == "on" ? "on" : "off")
					), FALSE); // Output error box
					
					// Terminate the script
					$Ctemplate->useStaticTemplate("admin/admin_foot", FALSE); // Footer
					DoFooter();
					exit;
				}
				
				if ( $_POST['site_host'] == NULL )
				{
					$Ctemplate->useTemplate("admin/siteprefs_variable_error", array(
						'VARIABLE'	=>	"{LANG_ADMINCFG_HOST}",
						'GLOBAL_TITLE'	=>	$_POST['global_title'],
						'SITE_HOST'	=>	$_POST['site_host'],
						'REGISTRATION'	=>	(@$_POST['registration'] == "on" ? "on" : "off"),
						'MODEL_FORUM'	=>	(@$_POST['module_forum'] == "on" ? "on" : "off")
					), FALSE); // Output error box
					
					// Terminate the script
					$Ctemplate->useStaticTemplate("admin/admin_foot", FALSE); // Footer
					DoFooter();
					exit;
				}
				
				// Every variable is entered, do update
				// All update query takes a return value of TRUE if it was successful or FALSE if it failed
				$scUpdate_global_title = $Cmysql->Query("UPDATE config SET value='" .$Cmysql->EscapeString($_POST['global_title']). "' WHERE variable='global_title'");
				$scUpdate_site_host = $Cmysql->Query("UPDATE config SET value='" .$Cmysql->EscapeString($_POST['site_host']). "' WHERE variable='site_host'");
				$scUpdate_module_forum = $Cmysql->Query("UPDATE config SET value='" .(@$_POST['module_forum'] == "on" ? "on" : "off"). "' WHERE variable='module_forum'");
				$scUpdate_registration = $Cmysql->Query("UPDATE config SET value='" .(@$_POST['registration'] == "on" ? "on" : "off"). "' WHERE variable='registration'");
				
				if ( ( $scUpdate_global_title == TRUE ) && ( $scUpdate_site_host == TRUE ) && ( $scUpdate_module_forum == TRUE ) && ( $scUpdate_registration == TRUE ) )
				{
					// If we succeeded, output success message, return form
					$Ctemplate->useStaticTemplate("admin/siteprefs_success", FALSE);
				} elseif ( ( $scUpdate_global_title == FALSE ) || ( $scUpdate_site_host == FALSE ) || ( $scUpdate_module_forum == FALSE ) || ( $scUpdate_registration == FALSE ) )
				{
					// If we failed, output return form and error message
					$Ctemplate->useTemplate("admin/siteprefs_error", array(
						'VARIABLE'	=>	"{LANG_ADMINCFG_TITLE}",
						'GLOBAL_TITLE'	=>	$_POST['global_title'],
						'SITE_HOST'	=>	$_POST['site_host'],
						'REGISTRATION'	=>	(@$_POST['registration'] == "on" ? "on" : "off"),
						'MODEL_FORUM'	=>	(@$_POST['module_forum'] == "on" ? "on" : "off")
					), FALSE); // Output error box
				}
			}
		} else {
			/* Language settings */
			$Ldir = "./language/"; // Language home dir
			$Lexempt = array('.', '..', '.svn', '_svn'); // Do not query these directories
			
			$current_language = mysql_fetch_row($Cmysql->Query("SELECT value FROM config WHERE variable='language'"));
			
			$Ctemplate->useStaticTemplate("admin/siteprefs_lang_form", FALSE); // Opening the form
			
			if (is_dir($Ldir)) 
			{
				if ($Ldh = opendir($Ldir))
				{
					while (($Lfile = readdir($Ldh)) !== false)
					{
						if(!in_array(strtolower($Lfile),$Lexempt))
						{
							if ( filetype($Ldir . $Lfile) == "dir" )
							{
								// We're now querying all language directories
								if ( ( file_exists($Ldir . $Lfile . "/language.php") ) && ( file_exists($Ldir . $Lfile . "/definition.php") ) )
								{
									// We only list directories containing the language AND the definition file
									include($Ldir.$Lfile."/definition.php"); // This will load in $wf_lang_def (containing the definition)
									
									$Ctemplate->useTemplate("admin/siteprefs_lang_option", array(
										'SELECTED'	=>	($Lfile == $current_language[0] ? " selected " : " "), // Selected is ' ' if it's another language, ' selected ' if it's the current. It makes the current language automatically re-selected
										'DIR_NAME'	=>	$Lfile, // Name of the language's directory
										'LOCALIZED_NAME'	=>	$wf_lang_def['LOCALIZED_NAME'], // The language's own, localized name (so it's Deutch for German)
										'SHORT_NAME'	=>	$wf_lang_def['SHORT_NAME'], // The language's English name (so it's German for German)
										'L_CODE'	=>	$wf_lang_def['LANG_CODE'] // Language code (it's de for German)
									), FALSE);
									
									unset ($wf_lang_def);
								}
							}
						}
					}
					closedir($Ldh);
				}
			}
			
			$Ctemplate->useStaticTemplate("admin/siteprefs_lang_foot", FALSE); // Closing the form
			/* Language settings */
			
			/* Theme settings */
			$Tdir = "./themes/"; // Language home dir
			$Texempt = array('.', '..', '.svn', '_svn'); // Do not query these directories
			
			$i = 0; // Define a counter on zero
			$embedder = ""; // Define a container
			
			$current_theme = mysql_fetch_row($Cmysql->Query("SELECT value FROM config WHERE variable='theme'"));
			
			if (is_dir($Tdir)) 
			{
				if ($Tdh = opendir($Tdir))
				{
					while (($Tfile = readdir($Tdh)) !== false)
					{
						if(!in_array(strtolower($Tfile),$Texempt))
						{
							if ( filetype($Tdir . $Tfile) == "dir" )
							{
								// We're now querying all language directories
								if ( ( file_exists($Tdir . $Tfile . "/style.css") ) &&  ( file_exists($Tdir . $Tfile . "/theme.php") ) )
								{
									// We only list directories containing the stylesheet file
									include($Tdir.$Tfile."/theme.php"); // Load the theme definition array ($theme_def)
									
									if ( $i === 0 )
									{
										// If the counter is zero, we need to create a new row.
										$embedder .= "<tr>";
									}
									
									if ( file_exists($Tdir . $Tfile . "/preview.png") )
									{
										// If there is a precreated preview image, use it as a preview
										$preview = $Ctemplate->useTemplate("admin/siteprefs_theme_preview", array(
											'IMAGE'	=>	$Tdir.$Tfile."/preview.png"
										), TRUE);
									} elseif ( !file_exists($Tdir. $Tfile . "/preview.png") )
									{
										// If there isn't a preview image, use a generated error message as preview
										$preview = $Ctemplate->useTemplate("errormessage", array(
											'PICTURE_NAME'	=>	"Nuvola_apps_error.png", // Error cross icon
											'TITLE'	=>	"{LANG_ADMINCFG_THEME_PREVIEW_NO}", // Error title
											'BODY'	=>	"", // Error text
											'ALT'	=>	"{LANG_ERROR_EXCLAMATION}" // Alternate picture text
										), TRUE);
									}
									
									if ( $current_theme[0] == $Tfile )
									{
										// If the current theme is the one we want to output button for
										// Disable the theme button
										
										$themeSetButton = $Ctemplate->useTemplate("admin/siteprefs_theme_button", array(
											'THEME_FILE'	=>	$Tfile, // Name of theme
											'SUBMIT_CAPTION'	=>	"{LANG_ADMINCFG_MODIFY_THEME_CURRENT}", // Button caption
											'DISABLED'	=>	" disabled" // Make the button unclickable
										), TRUE);
									} elseif ( $current_theme[0] != $Tfile )
									{
										// If the current theme is NOT the one we want to output button for
										// Make the set button
										
										$themeSetButton = $Ctemplate->useTemplate("admin/siteprefs_theme_button", array(
											'THEME_FILE'	=>	$Tfile, // Name of theme
											'SUBMIT_CAPTION'	=>	"{LANG_ADMINCFG_MODIFY_THEME}", // Button caption
											'DISABLED'	=>	"" // Don't make the button unclickable
										), TRUE);
									}
									
									// Output one table cell for the theme
									$embedder .= $Ctemplate->useTemplate("admin/siteprefs_theme_embed", array(
										'PREVIEW'	=>	$preview, // Embed the preview image
										'SHORT_NAME'	=>	$theme_def['SHORT_NAME'], // Short name of theme
										'DESCRIPTION'	=>	$theme_def['DESCRIPTION'], // Long description
										'BUTTON'	=>	$themeSetButton
									), TRUE); // Add it to the embedder
									$i++;
									
									if ( $i === 2 )
									{
										// If the counter is 2, we need to close the opened row
										$embedder .= '</tr>';
										$i = 0; // And we reset the counter
									}
								}
							}
						}
					}
					closedir($Tdh);
				}
			}
			
			if ( $i != 2 )
			{
				// After embedding the themes, 
				// if we haven't filled up a complete row with two entries
				// we need to close the unclosed row to prevent output errors
				$embedder .= '</tr>';
			}
			
			$Ctemplate->useTemplate("admin/siteprefs_theme_wrapper", array(
				'EMBED'	=>	$embedder // The previously filled container
			), FALSE); // Output the table
			/* Theme settings */
			
			/* General */
			if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
			{
				// We output the form with data returned (user doesn't have to enter it again)
				$Ctemplate->useTemplate("admin/siteprefs", array(
					'GLOBAL_TITLE'	=>	$_POST['global_title'],
					'SITE_HOST'	=>	$_POST['site_host'],
					'REGISTRATION_CHECK'	=>	(@$_POST['registration'] == "on" ? " checked" : ""),
					'MODULE_FORUM_CHECK'	=>	(@$_POST['module_forum'] == "on" ? " checked" : "")
				), FALSE);
			} else {
				// We output general form
				$Ctemplate->useTemplate("admin/siteprefs", array(
					'GLOBAL_TITLE'	=>	config('global_title'),
					'SITE_HOST'	=>	config('site_host'),
					'REGISTRATION_CHECK'	=>	(config('registration') == "on" ? " checked" : ""),
					'MODULE_FORUM_CHECK'	=>	(config('module_forum') == "on" ? " checked" : "")
				), FALSE);
			}
		}
		break;
	/* * CONFIGURATION * */
	/* --------------------------- */
	/* * FORUM SETTINGS * */
	case "forum":
		// If the FORUM module is disabled, prevent execution
		if ( config("module_forum") == "off" )
		{
			$Ctemplate->useStaticTemplate("admin/admin_foot", FALSE); // Output footer
			dieOnModule("forum"); // The DoFooter and the rest is issued by dieOnModule
		}
		
		if ( isset($_POST['set_do']) )
		{
			// Change the preferences
			$updateTopicPerPage = $Cmysql->Query("UPDATE config SET ".
				"value='" .$Cmysql->EscapeString($_POST['new_topic_switch_value']). "' WHERE variable='forum_topic_count_per_page'");
			$updatePostPerPage = $Cmysql->Query("UPDATE config SET ".
				"value='" .$Cmysql->EscapeString($_POST['new_post_switch_value']). "' WHERE variable='forum_post_count_per_page'");
			
			// The return values are TRUE if we succeed and FALSE if we fail
			if ( ( $updateTopicPerPage == FALSE ) || ( $updatePostPerPage == FALSE ) )
			{
				// If we failed
				$Ctemplate->useTemplate("errormessage", array(
					'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
					'TITLE'	=>	"{LANG_UCP_FORUM_FAIL}", // Error title
					'BODY'	=>	"", // Error text
					'ALT'	=>	"{LANG_SQL_EXEC_ERROR}" // Alternate picture text
				), FALSE ); // We give an unavailable error
				
				$Ctemplate->useStaticTemplate("admin/forum_back", FALSE); // Back button
			} elseif ( ( $updateTopicPerPage == TRUE ) && ( $updatePostPerPage == TRUE ) )
			{
				// If we succeeded
				$Ctemplate->useTemplate("successbox", array(
					'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_home.png", // House (user CP header)
					'TITLE'	=>	"{LANG_UCP_FORUM_SUCCESS}", // Success title
					'BODY'	=>	"{LANG_ACP_FORUM_SUCCESS_1}", // Success text
					'ALT'	=>	"{LANG_SQL_EXEC_SUCCESS}" // Alternate picture text
				), FALSE ); // We give a success message
				
				$Ctemplate->useStaticTemplate("admin/forum_back", FALSE); // Back button
			}
		} else {
			// Get the two values into a variable
			$topicPerPage = config("forum_topic_count_per_page");
			$postPerPage = config("forum_post_count_per_page");
			
			$Ctemplate->useTemplate("admin/forum", array(
				// Topic switch
				'T_5_SELECT'	=>	($topicPerPage == 5 ? " selected" : ""),
				'T_15_SELECT'	=>	($topicPerPage == 15 ? " selected" : ""),
				'T_30_SELECT'	=>	($topicPerPage == 30 ? " selected" : ""),
				'T_50_SELECT'	=>	($topicPerPage == 50 ? " selected" : ""),
				'T_100_SELECT'	=>	($topicPerPage == 100 ? " selected" : ""),
				
				// Post switch
				'P_5_SELECT'	=>	($postPerPage == 5 ? " selected" : ""),
				'P_15_SELECT'	=>	($postPerPage == 15 ? " selected" : ""),
				'P_30_SELECT'	=>	($postPerPage == 30 ? " selected" : ""),
				'P_50_SELECT'	=>	($postPerPage == 50 ? " selected" : ""),
				'P_100_SELECT'	=>	($postPerPage == 100 ? " selected" : "")
			), FALSE); // Output panel
		}
		break;
	/* * FORUM SETTINGS * */
	/* --------------------------- */
}
}
$Ctemplate->useStaticTemplate("admin/admin_foot", FALSE); // Footer
DoFooter();
?>
