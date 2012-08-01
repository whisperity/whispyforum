<!--- BEGIN install -->
<table class="install-header" border="0">
<tr>
<td class="install-picture"><img src="themes/{[THEME_NAME]}/{[PICTURE]}" alt="{[ALT]}"></td>
<td><h1 class="install-header" align="center">{[TITLE]}</h1></td>
</tr>
</table>

<table border="0" class="install-center">
<tr>
<td class="install-block-left">
{[LEFT_CONTENT]}
</td>
<td class="install-block-right" valign="top">
{[RIGHT_CONTENT]}
</td>
</tr>
</table>
<!--- END install -->

<!--- BEGIN install menu -->
<div id="menucontainer">
	<div id="header"><div id="header_left"></div>
	<div id="header_main">{[HEADER]}</div><div id="header_right"></div></div>
    <div id="content">
		<ul>
			{[CONTENT]}
		</ul>
    </div>
</div>
<!--- END install menu -->

<!--- BEGIN install menu element -->
<li class="install-menu-{[TYPE]}">{[CAPTION]}</li>
<!--- END install menu element -->

<!--- BEGIN introduction form -->
{[TRY_OUT_SETTINGS]}

<form method="POST" action="install.php">
{[INTRODUCTION_LANGUAGE]} <select size="1" name="new_language">
{[LANGUAGES_EMBED]}
</select><br>
{[INTRODUCTION_THEME]} <select size="1" name="new_theme">
{[THEMES_EMBED]}
</select><br>
<input type="submit" value="{[INTRODUCTION_MODIFY_SETTINGS]}">
</form>
<!--- END introduction form -->

<!--- BEGIN introduction language option -->
<option{[SELECTED]}value="{[DIR_NAME]}">{[LOCALIZED_NAME]} ({[SHORT_NAME]}) [{[LANG_CODE]}]</option>
<!--- END introduction language option -->

<!--- BEGIN introduction theme option -->
<option{[SELECTED]}value="{[DIR_NAME]}">{[NAME]}</option>
<!--- END introduction theme option -->

<!--- BEGIN introduction -->
{[INTRODUCTION_BODY]}
<br>
{[ENVIRONMENT_CHECKS]}
<!--- Until developer state is resolved, this should stay -->
{[DEVELOPER_STATE_BOX]}
<!--- Until developer state is resolved, this should stay -->
<!--- END introduction -->

<!--- BEGIN introduction envcheck header -->
<h3 class="subtitle">{[ENVCHECK_HEADER]}</h3>
{[ENVCHECK_DESCRIPTION]}
<!--- END introduction envcheck header -->

<!--- BEGIN introduction envcheck -->
<div id="ambox-{[TYPE]}" style="margin-bottom: 2em; padding: 3px; border-top: 1px solid #565656; border-right: 1px solid #565656; border-bottom: 1px solid #565656; background-color: white; line-height: 125%; margin: 1em auto;">
	
	<img src="themes/{[THEME_NAME]}/{[IMAGE]}" height="32" width="32" alt="{[STATUS]}" align="left" />
	<h4 style="border-bottom: 1px solid gray; margin: 0px 0px 0px 36px;">{[TITLE]}</h4>
		<div align="right">
			<b>{[STATUS]}</b>	
		</div>
	
	<p>{[MESSAGE]}</p>
</div>
<!--- END introduction envcheck -->

<!--- BEGIN introduction superfail -->
{[SUPERFAIL_NOTICE]}<br>
<input type="submit" value="{[SUBMIT_CAPTION]}" disabled>
<!--- END introduction superfail -->

<!--- BEGIN introduction forward form -->
<form method="POST" action="install.php">
	<input type="hidden" name="step" value="2">
	<input type="submit" value="{[SUBMIT_CAPTION]}">
</form>
<!--- END introduction forward form -->

<!--- BEGIN config -->
{[CONFIG_INTRO]}<br>
<font class="emphasis">{[MANDATORY_VARIABLES]}</font>

<form method="POST" action="install.php">
	<span class="form-header">{[DATABASE_CONFIG_DATA]}</span><br>
	{[DATABASE_TYPE]}<span class="red-star">*</span>: <select size="1" name="dbtype">
		{[DBTYPE_OPTIONS]}
	</select><br>
	{[DATABASE_HOST]}<span class="red-star">*</span>: <input type="text" name="dbhost" value="{[DBHOST]}" size="35"><br>
	{[DATABASE_USER]}<span class="red-star">*</span>: <input type="text" name="dbuser" value="{[DBUSER]}" size="35"><br>
	{[DATABASE_PASS]}<span class="red-star">*</span>: <input type="password" name="dbpass" value="{[DBPASS]}" size="35"><br>
	{[DATABASE_NAME]}<span class="red-star">*</span>: <input type="text" name="dbname" value="{[DBNAME]}" size="35"><br>
	<input type="submit" value="{[NEXT_CAPTION]}">
	<input type="hidden" name="step" value="3">
</form>
<!--- END config -->

<!--- BEGIN config dbtype option -->
	<option value="{[VALUE]}"{[SELECTED]}>{[CAPTION]}</option>
<!--- END config dbtype option -->

<!--- BEGIN config error return -->
{[MESSAGE]}
<form method="POST" action="install.php">
	<input type="hidden" name="dbtype" value="{[DBTYPE]}">
	<input type="hidden" name="dbhost" value="{[DBHOST]}">
	<input type="hidden" name="dbuser" value="{[DBUSER]}">
	<input type="hidden" name="dbpass" value="{[DBPASS]}">
	<input type="hidden" name="dbname" value="{[DBNAME]}">
	<input type="hidden" name="step" value="2">
	<input type="submit" name="error_return" value="{[SUBMIT_CAPTION]}">
</form>
<!--- END config error return -->

<!--- BEGIN config forward form -->
<form method="POST" action="install.php">
	<input type="hidden" name="step" value="4">
	<input type="submit" value="{[SUBMIT_CAPTION]}">
</form>
<!--- END config forward form -->

<!--- BEGIN dbcreate forward form -->
<form method="POST" action="install.php">
	<input type="hidden" name="step" value="5">
	<input type="submit" value="{[SUBMIT_CAPTION]}">
</form>
<!--- END dbcreate forward form -->

<!--- BEGIN dbtables forward form -->
<form method="POST" action="install.php">
	<input type="hidden" name="step" value="6">
	<input type="submit" value="{[SUBMIT_CAPTION]}">
</form>
<!--- END dbtables forward form -->