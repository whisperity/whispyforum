<?php
 /**
 * WhispyForum script file - freeuni_manage.php
 * 
 * Helps users to managed performers allocated for their user.
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("freeuni/manage_head", FALSE); // Header

if ( $_SESSION['log_bool'] == FALSE )
{
	// If the user is a guest
	$Ctemplate->useTemplate("errormessage", array(
		'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"This page is unaviable for guests!", // Error title
		'BODY'	=>	"This page requires you to log in to view it's contents.<br><br>Please use the login box to log in to the site. After that, you can view this page.", // Error text
		'ALT'	=>	"User permissions error" // Alternate picture text
	), FALSE ); // We give an unaviable error
} elseif ( $_SESSION['log_bool'] == TRUE)
{
	if ( ( isset($_POST['new_status']) ) && ( isset($_POST['performer_id']) ) )
	{
		// If we were passed with a new status and the performer's ID
		// We modify the performers status
		
		switch ( $_POST['new_status'] )
		{
			case "deallocate":
				// Deallocating the performer (deleting relation, making it unallocated)
				$perf_rel_uid = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id=" .$_POST['performer_id'])); // Query the related user's ID
				
				if ( $perf_rel_uid[0] == $_SESSION['uid'] )
				{
					// Modifying only if the user id is matching with the allocated user's ID
					$back_to_unallocated = $Cmysql->Query("UPDATE fu_performers SET status='unallocated' WHERE id=" .$Cmysql->EscapeString($_POST['performer_id']));
					
					// $back_to_unallocated is TRUE or FALSE based on success
					
					if ( $back_to_unallocated == FALSE )
					{
						// If there were errors unallocating the performer
						$Ctemplate->useTemplate("errormessage", array(
							'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
							'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
							'TITLE'	=>	"Az előadót nem lehet szabaddá tenni", // Error title
							'BODY'	=>	"A lekérdezést nem sikerült lefuttatni.", // Error text
							'ALT'	=>	"SQL hiba" // Alternate picture text
						), FALSE ); // We give an error
					} elseif ( $back_to_unallocated == TRUE )
					{
						// If there wasn't errors, give success
						$Ctemplate->useTemplate("successbox", array(
							'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
							'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
							'TITLE'	=>	"Előadó felszabadítva", // Success title
							'BODY'	=>	"Az előadó visszakerült a szabad tárolóba.<br>Mások a lefoglalás oldalon foglalhatják le maguknak.", // Success text
							'ALT'	=>	"Sikeres lekérdezés" // Alternate picture text
						), FALSE ); // We give a success message
						
						// Remove relation
						$relation_remove = $Cmysql->Query("DELETE FROM fu_perf_user_relation WHERE performer_id='" .$Cmysql->EscapeString($_POST['performer_id']). "' AND user_id='" .$Cmysql->EscapeString($_SESSION['uid']). "'");
						
						// $relation_remove is TRUE or FALSE based on success
						if ( $relation_remove == FALSE )
						{
							// If we failed removing the relation
							$Ctemplate->useTemplate("messagebox", array(
								'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
								'TITLE'	=>	"A foglalás nem törölhető", // Error title
								'BODY'	=>	"Az előadó vissza lett állítva szabad státuszúba, ám még mindig a felhasználódhoz van rendelve. Az inkozisztens állapot helyreáll... (idővel)", // Error text
								'ALT'	=>	"SQL hiba" // Alternate picture text
							), FALSE ); // We give a message (orange box)
						} elseif ( $relation_remove == TRUE )
						{
							// If we succeeded removing the relation
							$Ctemplate->useTemplate("successbox", array(
								'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
								'TITLE'	=>	"A foglalásod törölve", // Success title
								'BODY'	=>	"Az előadó és a felhasználód közötti reláció megszűnt.<br>Az előadó státuszát mostantól nem tudod módosítani.", // Success text
								'ALT'	=>	"Sikeres lekérdezés" // Alternate picture text
							), FALSE ); // We give a success message
							
							$Ctemplate->useStaticTemplate("freeuni/manage_return_to_list", FALSE); // Give return link
						}
					}
				} else {
					$Ctemplate->useTemplate("errormessage", array(
						'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
						'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
						'TITLE'	=>	"Hozzárendelési hiba!", // Error title
						'BODY'	=>	"A módosítani kívánt előadó nem hozzád van lefoglalva. A nem általad lefoglalt előadókat nem tudod módosítani.", // Error text
						'ALT'	=>	"Hozzáférési hiba!" // Alternate picture text
					), FALSE ); // We give an unaviable error
					
					$Ctemplate->useStaticTemplate("freeuni/manage_return_to_list", FALSE); // Give return link
				}
				break;
			case "pending":
				// Put performer in pending status.
				$perf_rel_uid = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id=" .$_POST['performer_id'])); // Query the related user's ID
				
				if ( $perf_rel_uid[0] == $_SESSION['uid'] )
				{
					// Modifying only if the user id is matching with the allocated user's ID
					$make_pending = $Cmysql->Query("UPDATE fu_performers SET status='pending' WHERE id=" .$Cmysql->EscapeString($_POST['performer_id']));
					
					// $make_pending is TRUE or FALSE based on success
					
					if ( $make_pending == FALSE )
					{
						// If there were errors unallocating the performer
						$Ctemplate->useTemplate("errormessage", array(
							'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
							'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
							'TITLE'	=>	"Az előadót nem lehet Függő státuszba tenni", // Error title
							'BODY'	=>	"A lekérdezést nem sikerült lefuttatni.", // Error text
							'ALT'	=>	"SQL hiba" // Alternate picture text
						), FALSE ); // We give an error
					} elseif ( $make_pending == TRUE )
					{
						// If there wasn't errors, give success
						$Ctemplate->useTemplate("successbox", array(
							'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
							'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
							'TITLE'	=>	"A státusz módosítása sikeresen befejeződött", // Success title
							'BODY'	=>	"Az előadó függő státuszba került", // Success text
							'ALT'	=>	"Sikeres lekérdezés" // Alternate picture text
						), FALSE ); // We give a success message
						
						$Ctemplate->useStaticTemplate("freeuni/manage_return_to_list", FALSE); // Give return link
					}
				} else {
					$Ctemplate->useTemplate("errormessage", array(
						'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
						'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
						'TITLE'	=>	"Hozzárendelési hiba!", // Error title
						'BODY'	=>	"A módosítani kívánt előadó nem hozzád van lefoglalva. A nem általad lefoglalt előadókat nem tudod módosítani.", // Error text
						'ALT'	=>	"Hozzáférési hiba!" // Alternate picture text
					), FALSE ); // We give an unaviable error
					
					$Ctemplate->useStaticTemplate("freeuni/manage_return_to_list", FALSE); // Give return link
				}
				break;
			case "agreed":
				// Put performer in agreed status.
				$perf_rel_uid = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id=" .$_POST['performer_id'])); // Query the related user's ID
				
				if ( $perf_rel_uid[0] == $_SESSION['uid'] )
				{
					// Modifying only if the user id is matching with the allocated user's ID
					$make_agreed = $Cmysql->Query("UPDATE fu_performers SET status='agreed' WHERE id=" .$Cmysql->EscapeString($_POST['performer_id']));
					
					// $make_agreed is TRUE or FALSE based on success
					
					if ( $make_agreed == FALSE )
					{
						// If there were errors unallocating the performer
						$Ctemplate->useTemplate("errormessage", array(
							'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
							'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
							'TITLE'	=>	"Az előadót nem lehet Vállalja státuszba tenni", // Error title
							'BODY'	=>	"A lekérdezést nem sikerült lefuttatni.", // Error text
							'ALT'	=>	"SQL hiba" // Alternate picture text
						), FALSE ); // We give an error
					} elseif ( $make_agreed == TRUE )
					{
						// If there wasn't errors, give success
						$Ctemplate->useTemplate("successbox", array(
							'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
							'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
							'TITLE'	=>	"A státusz módosítása sikeresen befejeződött", // Success title
							'BODY'	=>	"Az előadó vállalja státuszba került", // Success text
							'ALT'	=>	"Sikeres lekérdezés" // Alternate picture text
						), FALSE ); // We give a success message
						
						$Ctemplate->useStaticTemplate("freeuni/manage_return_to_list", FALSE); // Give return link
					}
				} else {
					$Ctemplate->useTemplate("errormessage", array(
						'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
						'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
						'TITLE'	=>	"Hozzárendelési hiba!", // Error title
						'BODY'	=>	"A módosítani kívánt előadó nem hozzád van lefoglalva. A nem általad lefoglalt előadókat nem tudod módosítani.", // Error text
						'ALT'	=>	"Hozzáférési hiba!" // Alternate picture text
					), FALSE ); // We give an unaviable error
					
					$Ctemplate->useStaticTemplate("freeuni/manage_return_to_list", FALSE); // Give return link
				}
				break;
			case "refused":
				// Put performer in refused status.
				$perf_rel_uid = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id=" .$_POST['performer_id'])); // Query the related user's ID
				
				if ( $perf_rel_uid[0] == $_SESSION['uid'] )
				{
					// Modifying only if the user id is matching with the allocated user's ID
					$make_refused = $Cmysql->Query("UPDATE fu_performers SET status='refused' WHERE id=" .$Cmysql->EscapeString($_POST['performer_id']));
					
					// $make_refused is TRUE or FALSE based on success
					
					if ( $make_refused == FALSE )
					{
						// If there were errors unallocating the performer
						$Ctemplate->useTemplate("errormessage", array(
							'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
							'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
							'TITLE'	=>	"Az előadót nem lehet Nem vállalja státuszba tenni", // Error title
							'BODY'	=>	"A lekérdezést nem sikerült lefuttatni.", // Error text
							'ALT'	=>	"SQL hiba" // Alternate picture text
						), FALSE ); // We give an error
					} elseif ( $make_refused == TRUE )
					{
						// If there wasn't errors, give success
						$Ctemplate->useTemplate("successbox", array(
							'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
							'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
							'TITLE'	=>	"A státusz módosítása sikeresen befejeződött", // Success title
							'BODY'	=>	"Az előadó nem vállalja státuszba került", // Success text
							'ALT'	=>	"Sikeres lekérdezés" // Alternate picture text
						), FALSE ); // We give a success message
						
						$Ctemplate->useStaticTemplate("freeuni/manage_return_to_list", FALSE); // Give return link
					}
				} else {
					$Ctemplate->useTemplate("errormessage", array(
						'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
						'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
						'TITLE'	=>	"Hozzárendelési hiba!", // Error title
						'BODY'	=>	"A módosítani kívánt előadó nem hozzád van lefoglalva. A nem általad lefoglalt előadókat nem tudod módosítani.", // Error text
						'ALT'	=>	"Hozzáférési hiba!" // Alternate picture text
					), FALSE ); // We give an unaviable error
					
					$Ctemplate->useStaticTemplate("freeuni/manage_return_to_list", FALSE); // Give return link
				}
				break;
		}
	} else {
		// Normal opening (without parameters)
		
		$Ctemplate->useStaticTemplate("freeuni/index_perf_status_definition", FALSE); // Give definitions about different performer statuses
		
		$Ctemplate->useStaticTemplate("freeuni/manage_table_open", FALSE); // Open list table
		
		// Query down performers allocated to this user
		
		/* Pending performers */
		$pPending = $Cmysql->Query("SELECT * FROM fu_performers WHERE status='pending'");
		
		while ( $row_p = mysql_fetch_assoc($pPending) )
		{
			// Query down allocated user for the performer
			$perf_rel_uid_p = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id=" .$row_p['id']));
			
			if ( $perf_rel_uid_p[0] == $_SESSION['uid'] )
			{
				// If the previously queried relation links the
				// current performer to the current user
				// generate the row
				
				$Ctemplate->useTemplate("freeuni/manage_tablerow_pending", array(
					'PERFORMER_NAME'	=>	$row_p['pName'],
					'EMAIL'	=>	$row_p['email'],
					'TELEPHONE'	=>	$row_p['telephone'],
					'COMMENTS'	=>	substr($row_p['comments'], 0, 64), // First 64 character of comments
					'PERFORMER_ID'	=>	$row_p['id']
				), FALSE);
			}
		}
		/* Pending performers */
		
		/* Agreed performers */
		$pWillCome = $Cmysql->Query("SELECT * FROM fu_performers WHERE status='agreed'");
		
		while ( $row_wc = mysql_fetch_assoc($pWillCome) )
		{
			// Query down allocated user for the performer
			$perf_rel_uid_wc = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id=" .$row_wc['id']));
			
			if ( $perf_rel_uid_wc[0] == $_SESSION['uid'] )
			{
			// If the previously queried relation links the
				// current performer to the current user
				// generate the row
				
				$Ctemplate->useTemplate("freeuni/manage_tablerow_agreed", array(
					'PERFORMER_NAME'	=>	$row_wc['pName'],
					'EMAIL'	=>	$row_wc['email'],
					'TELEPHONE'	=>	$row_wc['telephone'],
					'COMMENTS'	=>	substr($row_wc['comments'], 0, 64), // First 64 character of comments
					'PERFORMER_ID'	=>	$row_wc['id']
				), FALSE);
			}
		}
		/* Agreed performers */
		
		/* Refused performers */
		$pWillCome = $Cmysql->Query("SELECT * FROM fu_performers WHERE status='refused'");
		
		while ( $row_r = mysql_fetch_assoc($pWillCome) )
		{
			// Query down allocated user for the performer
			$perf_rel_uid_r = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id=" .$row_r['id']));
			
			if ( $perf_rel_uid_r[0] == $_SESSION['uid'] )
			{
				// If the previously queried relation links the
				// current performer to the current user
				// generate the row
				
				$Ctemplate->useTemplate("freeuni/manage_tablerow_refused", array(
					'PERFORMER_NAME'	=>	$row_r['pName'],
					'EMAIL'	=>	$row_r['email'],
					'TELEPHONE'	=>	$row_r['telephone'],
					'COMMENTS'	=>	substr($row_r['comments'], 0, 64), // First 64 character of comments
					'PERFORMER_ID'	=>	$row_r['id']
				), FALSE);
			}
		}
		/* Refused performers */
		
		$Ctemplate->useStaticTemplate("freeuni/manage_table_close", FALSE); // Close list table
	}
}

$Ctemplate->useStaticTemplate("freeuni/manage_foot", FALSE); // Footer
DoFooter();
?>