<!--- BEGIN login -->
<div id="menucontainer">
	<div id="header"><div id="header_left"></div>
	<div id="header_main">{[LANG_LOGIN]}</div><div id="header_right"></div></div>
    <div id="content">
        <form method="POST" action="login.php">
			{[LANG_USERNAME]}: <input type="text" name="user_loginname" size="25"><br>
			{[LANG_PASSWORD]}: <input type="password" name="user_password" size="25"><br>
			<a href="registration.php">{[LANG_REGISTER]}</a><br>
			<a href="pwd_recover_begin.php">{[LANG_PWDRECOVER_LINK]}</a><br>
			<input type="hidden" name="returnto" value="{[RETURN_TO]}">
			<input type="submit" value="{[LANG_LOGIN]}">
		</form>
	</div>
	<div id="footer"></div>
</div>
<!--- END login -->

<!--- BEGIN userbox -->
<div id="menucontainer">
	<div id="header"><div id="header_left"></div>
	<div id="header_main">{[WELCOME]}</div><div id="header_right"></div></div>
    <div id="content">
		<img src="{[AVATAR_FILENAME]}" alt="{AVATAR_ALT}"><br>
		<a href="profile.php?id={[USER_ID]}">{[LANG_PROFILE]}</a><br>
		<a href="control_user.php">{[LANG_USER_CONTROL_PANEL]}</a>
		{[ADMIN_CONTROL_PANEL]}
		<form method="POST" action="logout.php">
			<input type="hidden" name="returnto" value="{[RETURN_TO]}">
			<input type="hidden" name="logout" value="do_user_logout">
			<input type="submit" value="{[LANG_LOGOUT]}">
		</form>
	</div>
</div>
<!--- END userbox -->

<!--- BEGIN userbox admincp -->
<br><a href="control_admin.php">{[LANG_ADMIN_CONTROL_PANEL]}</a>
<!--- END userbox admincp -->