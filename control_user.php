<?php
 /**
 * WhispyForum script file - control_user.php
 * 
 * User control panel. Usage: help individuals set user-specific properties.
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("user/cp_head", FALSE); // Header

// We define the $site variable
$site = "";

if ( $_SESSION['log_bool'] == FALSE )
{
	// If the user is a guest
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"{LANG_NO_GUESTS}", // Error title
		'BODY'	=>	"{LANG_REQUIRES_LOGGEDIN}", // Error text
		'ALT'	=>	"{LANG_PERMISSIONS_ERROR}" // Alternate picture text
	), FALSE ); // We give an unavailable error
} elseif ( $_SESSION['log_bool'] == TRUE)
{
// If user is logged in, the control panel is accessible

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

switch ($site)
{
	case "avatar_upload":
		// Avatar uploading
		if ( isset($_POST['av_upload']) ) // If there's uploading
		{
			if ( $_FILES['pic_file']['size'] > 2097152 )
			{
				// Big size (larger than 2 MBs)
				$Ctemplate->useTemplate("user/cp_avatar_upload_toobigfile_error", array(
					'FILE_SIZE'	=>	DecodeSize($_FILES['pic_file']['size'])
				), FALSE); // Give error
			} else {
				if ( in_array($_FILES['pic_file']['type'], array("image/gif", "image/jpeg", "image/png")) )
				{
					if ( @move_uploaded_file($_FILES['pic_file']['tmp_name'], "upload/usr_avatar/cached." .$_SESSION['username']. ".ptmp") ) // Move the file to the temp location
					{
						// Uploaded successfully
						
						$fnToken = generateHexTokenNoDC(); // Generate a filename token
						
						if ( $_FILES['pic_file']['type'] == "image/jpeg" )
						{
							// If the file is a JPEG file
							
							saveThumbnailJPEG("upload/usr_avatar/cached." .$_SESSION['username']. ".ptmp", 150, "upload/usr_avatar/".$fnToken); // Save the thumbnail
							
							unlink("upload/usr_avatar/cached." .$_SESSION['username']. ".ptmp"); // Delete the original uploaded file
							
							$fExt = ".jpg"; // Set the file extension
						}
						
						if ( $_FILES['pic_file']['type'] == "image/png" )
						{
							// If the file is a PNG file
							
							saveThumbnailPNG("upload/usr_avatar/cached." .$_SESSION['username']. ".ptmp", 150, "upload/usr_avatar/".$fnToken); // Save the thumbnail
							
							unlink("upload/usr_avatar/cached." .$_SESSION['username']. ".ptmp"); // Delete the original uploaded file
							
							$fExt = ".png"; // Set the file extension
						}
						
						if ( $_FILES['pic_file']['type'] == "image/gif" )
						{
							// If the file is a GIF file
							
							saveThumbnailGIF("upload/usr_avatar/cached." .$_SESSION['username']. ".ptmp", 150, "upload/usr_avatar/".$fnToken); // Save the thumbnail
							
							unlink("upload/usr_avatar/cached." .$_SESSION['username']. ".ptmp"); // Delete the original uploaded file
							
							$fExt = ".gif"; // Set the file extension
						}
						
						$Cmysql->Query("UPDATE users SET avatar_filename='" .$fnToken.$fExt. "' WHERE id='" .$_SESSION['uid']. "'"); // Update database
						
						@unlink("upload/usr_avatar/" .$_SESSION['avatar_filename']); // Remove the old avatar file
						
						$_SESSION['avatar_filename'] = $fnToken.$fExt; // Update session with new avatar filename (refreshing avatar does not need user relog)
						
						$Cbadges->GrantBadge("AVATAR"); // Give badge for avatar upload
						
						// Successful upload
						$Ctemplate->useTemplate("user/cp_avatar_upload_success", array(
							'AVATAR_FILENAME'	=>	$fnToken.$fExt
						), FALSE); // Give success
					} else {
						// Error during upload
						$Ctemplate->useStaticTemplate("user/cp_avatar_upload_error", FALSE); // Give error
					}
				} else {
					// Wrong filetype
					$Ctemplate->useTemplate("user/cp_avatar_upload_filetype_error", array(
						'FILE_TYPE'	=>	$_FILES['pic_file']['type']
					), FALSE); // Give error
				}
			}
		} else {
			// If there's no upload request
			$Ctemplate->useTemplate("user/cp_avatar_upload", array(
				'AVATAR_FILENAME'	=>	$_SESSION['avatar_filename'], // Current avatar filename (needs implementation)
			), FALSE); // We output the upload form
		}
		break;
	case "site_preferences":
		// Setting site preferences (theme, language)
		// Parsing form input
		if ( isset($_POST['set_type']) )
		{
			if ( ( $_POST['set_type'] == "language" ) && ( isset($_POST['new_lang']) ) )
			{
				// Change the language in the database
				$Lmod = $Cmysql->Query("UPDATE users SET language='" .$Cmysql->EscapeString($_POST['new_lang']). "' WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'");
				
				// $Lmod is TRUE if we succeed and FALSE if we fail
				if ( $Lmod == FALSE )
				{
					// If we failed
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
						'TITLE'	=>	"{LANG_SITEPREF_MODIFY_LANGUAGE_ERROR}", // Error title
						'BODY'	=>	"", // Error text
						'ALT'	=>	"{LANG_SQL_EXEC_ERROR}" // Alternate picture text
					), FALSE ); // We give an unavailable error
					
					$Ctemplate->useStaticTemplate("user/cp_siteprefs_back", FALSE); // Back button
				} elseif ( $Lmod == TRUE )
				{
					// If we succeeded
					$Ctemplate->useTemplate("successbox", array(
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_home.png", // House (user CP header)
						'TITLE'	=>	"{LANG_SITEPREF_MODIFY_LANGUAGE_SUCCESS}", // Success title
						'BODY'	=>	"{LANG_SITEPREF_MODIFY_LANGUAGE_SUCCESS_1}", // Success text
						'ALT'	=>	"{LANG_SQL_EXEC_SUCCESS}" // Alternate picture text
					), FALSE ); // We give a success message
					
					$Ctemplate->useStaticTemplate("user/cp_siteprefs_back", FALSE); // Back button
					
					// Modify the session so the next page load
					// will load the new language
					$_SESSION['usr_language'] = $_POST['new_lang'];
				}
			}
			
			if ( ( $_POST['set_type'] == "theme" ) && ( isset($_POST['new_theme']) ) )
			{
				// Change the theme in the database
				$Tmod = $Cmysql->Query("UPDATE users SET theme='" .$Cmysql->EscapeString($_POST['new_theme']). "' WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'");
				
				// $Tmod is TRUE if we succeed and FALSE if we fail
				if ( $Tmod == FALSE )
				{
					// If we failed
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
						'TITLE'	=>	"{LANG_SITEPREF_MODIFY_THEME_ERROR}", // Error title
						'BODY'	=>	"", // Error text
						'ALT'	=>	"{LANG_SQL_EXEC_ERROR}" // Alternate picture text
					), FALSE ); // We give an unavailable error
					
					$Ctemplate->useStaticTemplate("user/cp_siteprefs_back", FALSE); // Back button
				} elseif ( $Tmod == TRUE )
				{
					// If we succeeded
					$Ctemplate->useTemplate("successbox", array(
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_home.png", // House (user CP header)
						'TITLE'	=>	"{LANG_SITEPREF_MODIFY_THEME_SUCCESS}", // Success title
						'BODY'	=>	"{LANG_SITEPREF_MODIFY_THEME_SUCCESS_1}", // Success text
						'ALT'	=>	"{LANG_SQL_EXEC_SUCCESS}" // Alternate picture text
					), FALSE ); // We give a success message
					
					$Ctemplate->useStaticTemplate("user/cp_siteprefs_back", FALSE); // Back button
					
					// Modify the session so the next page load
					// will load the new theme
					$_SESSION['theme_name'] = $_POST['new_theme'];
				}
			}
		} else {
			$Ctemplate->useStaticTemplate("user/cp_siteprefs", FALSE);
			
			/* Language settings */
			$Ldir = "./language/"; // Language home dir
			$Lexempt = array('.', '..', '.svn', '_svn'); // Do not query these directories
			
			$Ctemplate->useStaticTemplate("user/cp_siteprefs_lang_form", FALSE); // Opening the form
			
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
									
									$Ctemplate->useTemplate("user/cp_siteprefs_lang_option", array(
										'SELECTED'	=>	($Lfile == $_SESSION['usr_language'] ? " selected " : " "), // Selected is ' ' if it's another language, ' selected ' if it's the current. It makes the current language automatically re-selected
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
			
			$Ctemplate->useStaticTemplate("user/cp_siteprefs_lang_foot", FALSE); // Closing the form
			/* Language settings */
			
			/* Theme settings */
			$Tdir = "./themes/"; // Language home dir
			$Texempt = array('.', '..', '.svn', '_svn'); // Do not query these directories
			
			$i = 0; // Define a counter on zero
			$embedder = ""; // Define a container
			
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
										$preview = $Ctemplate->useTemplate("user/cp_siteprefs_theme_preview", array(
											'IMAGE'	=>	$Tdir.$Tfile."/preview.png"
										), TRUE);
									} elseif ( !file_exists($Tdir. $Tfile . "/preview.png") )
									{
										// If there isn't a preview image, use a generated error message as preview
										$preview = $Ctemplate->useTemplate("errormessage", array(
											'PICTURE_NAME'	=>	"Nuvola_apps_error.png", // Error cross icon
											'TITLE'	=>	"{LANG_SITEPREF_THEME_PREVIEW_NO}", // Error title
											'BODY'	=>	"", // Error text
											'ALT'	=>	"{LANG_ERROR_EXCLAMATION}" // Alternate picture text
										), TRUE);
									}
									
									if ( $_SESSION['theme_name'] == $Tfile )
									{
										// If the current theme is the one we want to output button for
										// Disable the theme button
										
										$themeSetButton = $Ctemplate->useTemplate("user/cp_siteprefs_theme_button", array(
											'THEME_FILE'	=>	$Tfile, // Name of theme
											'SUBMIT_CAPTION'	=>	"{LANG_SITEPREF_MODIFY_THEME_CURRENT}", // Button caption
											'DISABLED'	=>	" disabled" // Make the button unclickable
										), TRUE);
									} elseif ( $_SESSION['theme_name'] != $Tfile )
									{
										// If the current theme is NOT the one we want to output button for
										// Make the set button
										
										$themeSetButton = $Ctemplate->useTemplate("user/cp_siteprefs_theme_button", array(
											'THEME_FILE'	=>	$Tfile, // Name of theme
											'SUBMIT_CAPTION'	=>	"{LANG_SITEPREF_MODIFY_THEME}", // Button caption
											'DISABLED'	=>	"" // Don't make the button unclickable
										), TRUE);
									}
									
									// Output one table cell for the theme
									$embedder .= $Ctemplate->useTemplate("user/cp_siteprefs_theme_embed", array(
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
			
			$Ctemplate->useTemplate("user/cp_siteprefs_theme_wrapper", array(
				'EMBED'	=>	$embedder // The previously filled container
			), FALSE); // Output the table
			/* Theme settings */
		}
		break;
	case "forum":
		// Forum settings
		
		// If the FORUM module is disabled, prevent execution
		if ( config("module_forum") == "off" )
		{
			$Ctemplate->useStaticTemplate("user/cp_foot", FALSE); // Output footer
			dieOnModule("forum"); // The DoFooter and the rest is issued by dieOnModule
		}
		
		if ( isset($_POST['set_do']) )
		{
			// Change the preference
			$mod = $Cmysql->Query("UPDATE users SET ".
				"forum_topic_count_per_page='" .$Cmysql->EscapeString($_POST['new_topic_switch_value']). "', ".
				"forum_post_count_per_page='" .$Cmysql->EscapeString($_POST['new_post_switch_value']). "' ".
				"WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'");
			
			// $mod is TRUE if we succeed and FALSE if we fail
			if ( $mod == FALSE )
			{
				// If we failed
				$Ctemplate->useTemplate("errormessage", array(
					'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
					'TITLE'	=>	"{LANG_UCP_FORUM_FAIL}", // Error title
					'BODY'	=>	"", // Error text
					'ALT'	=>	"{LANG_SQL_EXEC_ERROR}" // Alternate picture text
				), FALSE ); // We give an unavailable error
				
				$Ctemplate->useStaticTemplate("user/cp_forum_back", FALSE); // Back button
			} elseif ( $mod == TRUE )
			{
				// If we succeeded
				$Ctemplate->useTemplate("successbox", array(
					'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_home.png", // House (user CP header)
					'TITLE'	=>	"{LANG_UCP_FORUM_SUCCESS}", // Success title
					'BODY'	=>	"{LANG_UCP_FORUM_SUCCESS_1}", // Success text
					'ALT'	=>	"{LANG_SQL_EXEC_SUCCESS}" // Alternate picture text
				), FALSE ); // We give a success message
				
				$Ctemplate->useStaticTemplate("user/cp_forum_back", FALSE); // Back button
				
				// Modify the session so the next page load
				// will use the new value
				$_SESSION['forum_topic_count_per_page'] = $_POST['new_topic_switch_value'];
				$_SESSION['forum_post_count_per_page'] = $_POST['new_post_switch_value'];
			}
		} else {
			$Ctemplate->useStaticTemplate("user/cp_forum", FALSE);
			
			$Ctemplate->useTemplate("user/cp_forum_form", array(
				// Topic switch
				'T_5_SELECT'	=>	($_SESSION['forum_topic_count_per_page'] == 5 ? " selected" : ""),
				'T_15_SELECT'	=>	($_SESSION['forum_topic_count_per_page'] == 15 ? " selected" : ""),
				'T_30_SELECT'	=>	($_SESSION['forum_topic_count_per_page'] == 30 ? " selected" : ""),
				'T_50_SELECT'	=>	($_SESSION['forum_topic_count_per_page'] == 50 ? " selected" : ""),
				'T_100_SELECT'	=>	($_SESSION['forum_topic_count_per_page'] == 100 ? " selected" : ""),
				
				// Post switch
				'P_5_SELECT'	=>	($_SESSION['forum_post_count_per_page'] == 5 ? " selected" : ""),
				'P_15_SELECT'	=>	($_SESSION['forum_post_count_per_page'] == 15 ? " selected" : ""),
				'P_30_SELECT'	=>	($_SESSION['forum_post_count_per_page'] == 30 ? " selected" : ""),
				'P_50_SELECT'	=>	($_SESSION['forum_post_count_per_page'] == 50 ? " selected" : ""),
				'P_100_SELECT'	=>	($_SESSION['forum_post_count_per_page'] == 100 ? " selected" : "")
			), FALSE); // Output panel
		}
		break;
}

}
$Ctemplate->useStaticTemplate("user/cp_foot", FALSE); // Footer
DoFooter();
?>
