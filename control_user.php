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
		'TITLE'	=>	"This page is unaviable for guests!", // Error title
		'BODY'	=>	"This page requires you to log in to view it's contents.<br><br>Please use the login box to log in to the site. After that, you can view this page.", // Error text
		'ALT'	=>	"User permissions error" // Alternate picture text
	), FALSE ); // We give an unaviable error
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
}

}
$Ctemplate->useStaticTemplate("user/cp_foot", FALSE); // Footer
DoFooter();
?>