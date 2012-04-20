<!--- BEGIN header -->
<html>

<head>
	<title>{[GLOBAL_TITLE]}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" type="text/css" href="themes/{[THEME_NAME]}/style.css">
</head>
<body>

<div class="headerbox">
{[HEADER]}
</div>
<!--- END header -->

<!--- BEGIN left -->
<table border="0" class="centerdiv">
<tr>
<td valign="top" class="left">
{[LEFT]}
</td>
<!--- END left -->

<!--- BEGIN center -->
<td valign="top" class="center">
<!--- END center -->

<!--- BEGIN right -->
</td>
<td valign="top" class="right">
{[RIGHT]}
</td>
<!--- END right -->

<!--- BEGIN footer -->
</tr></table>
<div class="footerbox">
{[FOOTER]}
</div>
</body>
</html>
<!--- END footer -->

<!--- BEGIN ambox -->
<table class="ambox" id="ambox-{[TYPE]}">
<tr>
	<td rowspan="2"><img src="themes/{[THEME_NAME]}/{[IMAGE]}" alt="{[IMAGE_ALT]}"></td>
	<td class="title">{[TITLE]}</td>
</tr>
<tr>
	<td class="body">{[BODY]}</td>
</tr>
</table>
<!--- END ambox -->