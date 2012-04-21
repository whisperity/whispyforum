<?php
/**
 * WhispyForum
 * 
 * User login transition page. 
 * 
 * /login.php
*/
define('REQUIRE_SAFEMODE', TRUE);
require("includes/load.php");

$template->load_template("userbox", TRUE);

if ( $_SESSION['id'] !== 0 && $user->userid !== 0 )
	print ambox('ERROR', lang_key("LOGIN ALREADY"), NULL, "Nuvola_apps_kgpg.png", NULL);

if ( count($_POST) === 0 )
	exit( ambox('CRITICAL', lang_key("DIRECT"), lang_key("DIRECT TITLE"), "Nuvola_apps_terminal.png", NULL) );

if ( @$_POST['user_loginname'] === "" || @$_POST['user_password'] === "" )
{
	print ambox('CRITICAL', lang_key("LOGIN EMPTY"), NULL, "Nuvola_apps_terminal.png", NULL);
	print $template->parse_template("redirect", array(
		'RETURNTO'	=>	@$_POST['returnto'],
		'REDIRECT'	=>	lang_key("REDIRECT", array(
			'LINK'	=>	@$_POST['returnto']
		) )
	) );
}

if ( @$_POST['user_loginname'] !== "" && @$_POST['user_password'] !== "" )
{
	$sql->query("SELECT `id`, `activated` FROM `users` WHERE `username`='" .$sql->escape($_POST['user_loginname']). "' AND `pwd`='" .$sql->escape($_POST['user_password']). "' LIMIT 1;");
	
	if ( $sql->num_rows() === 0 )
	{
		print ambox('ERROR', lang_key("LOGIN AUTH FAIL"), NULL, "Nuvola_apps_kgpg.png", NULL);
		print $template->parse_template("redirect", array(
			'RETURNTO'	=>	@$_POST['returnto'],
			'REDIRECT'	=>	lang_key("REDIRECT", array(
				'LINK'	=>	@$_POST['returnto']
			) )
		) );
	} elseif ( $sql->num_rows() === 1 )
	{
		$row = $sql->fetch_array();
		
		if ( $row['activated'] === "0" )
		{
			print ambox('WARNING', lang_key("LOGIN NOT ACTIVATED"), NULL, "Nuvola_apps_kgpg.png", NULL);
			print $template->parse_template("redirect", array(
				'RETURNTO'	=>	@$_POST['returnto'],
				'REDIRECT'	=>	lang_key("REDIRECT", array(
					'LINK'	=>	@$_POST['returnto']
				) )
			) );
		} elseif ( $row['activated'] === "1" )
		{
			$_SESSION['id'] = $row['id'];
			$_SESSION['username'] = $_POST['user_loginname'];
			$_SESSION['password'] = $_POST['user_password'];
			
			print ambox('SUCCESS', lang_key("LOGIN SUCCESS"), NULL, "Nuvola_apps_kgpg.png", NULL);
			print $template->parse_template("redirect", array(
				'RETURNTO'	=>	@$_POST['returnto'],
				'REDIRECT'	=>	lang_key("REDIRECT", array(
					'LINK'	=>	@$_POST['returnto']
				) )
			) );
		}
	}
}
?>