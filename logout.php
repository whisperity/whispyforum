<?php
/**
 * WhispyForum
 * 
 * User logout transition page. 
 * 
 * /logout.php
*/
define('REQUIRE_SAFEMODE', TRUE);
require("includes/load.php");

if ( $_SESSION['id'] === 0 && $user->userid === 0 )
	exit( ambox('ERROR', lang_key("LOGOUT NOT LOGGED IN"), NULL, "user.png", NULL) );

if ( count($_POST) === 0 )
	exit( ambox('CRITICAL', lang_key("DIRECT"), lang_key("DIRECT TITLE"), "code.png", NULL) );

if ( @$_POST['logout'] !== "do_user_logout" )
{
	exit( ambox('CRITICAL', lang_key("DIRECT"), lang_key("DIRECT TITLE"), "code.png", NULL) );
}

if ( @$_POST['logout'] === "do_user_logout" )
{
	// Destroy the session's data.
	// When the user loads the next page, user::_startup_session() will create a new, guest session.
	$user->logout();
	
	print ambox('SUCCESS', lang_key("LOGOUT SUCCESS"), NULL, "user.png", NULL);
	print $template->parse_template("redirect", array(
		'RETURNTO'	=>	@$_POST['returnto'],
		'REDIRECT'	=>	lang_key("REDIRECT", array(
			'LINK'	=>	@$_POST['returnto']
		) )
	) );
	
	exit;
}
?>