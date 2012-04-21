<!--- BEGIN user login -->
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
<br>
<!--- END user login -->