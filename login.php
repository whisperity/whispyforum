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

if ( $_SESSION['id'] !== 0 && $user->userid !== 0 )
	exit( ambox('ERROR', lang_key("LOGIN ALREADY"), NULL, "user.png", NULL) );

if ( count($_POST) === 0 )
{
	// Load a template of the login box module and execute the direct open part.
	// It will generate us a login box.
	
	$loginbox = new module(0, "userbox");
	echo $loginbox->execute("login direct open");
	exit;
}

if ( @$_POST['user_loginname'] === "" || @$_POST['user_password'] === "" )
{
	print ambox('CRITICAL', lang_key("LOGIN EMPTY"), NULL, "code.png", NULL);
	print $template->parse_template("redirect", array(
		'RETURNTO'	=>	@$_POST['returnto'],
		'REDIRECT'	=>	lang_key("REDIRECT", array(
			'LINK'	=>	@$_POST['returnto']
		) )
	) );
	exit();
}

if ( @$_POST['user_loginname'] !== "" && @$_POST['user_password'] !== "" )
{
	$sql->query("SELECT `id`, `activated` FROM `users` WHERE `username`='" .$sql->escape($_POST['user_loginname']). "' AND `pwd`='" .$sql->escape($_POST['user_password']). "' LIMIT 1;");
	
	if ( $sql->num_rows() === 0 )
	{
		print ambox('ERROR', lang_key("LOGIN AUTH FAIL"), NULL, "user.png", NULL);
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
			print ambox('WARNING', lang_key("LOGIN NOT ACTIVATED"), NULL, "user.png", NULL);
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
			
			print ambox('SUCCESS', lang_key("LOGIN SUCCESS"), NULL, "user.png", NULL);
			print $template->parse_template("redirect", array(
				'RETURNTO'	=>	@$_POST['returnto'],
				'REDIRECT'	=>	lang_key("REDIRECT", array(
					'LINK'	=>	@$_POST['returnto']
				) )
			) );
		}
	}
	
	exit;
}
?>