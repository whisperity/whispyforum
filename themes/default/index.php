<?php
header('Content-type: text/html; charset=iso-8859-2'); // Szükséges
?>

<html>

<head> 
<title>CSS bemutató oldal</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<a href="images.php">Képek >></a>
<br>
body
<div class="headerbox">
	div.headerbox
</div>

<div class="leftbox">
	div.leftbox
	<form>
		<span class="formHeader">span.formHeader</span>
		<p class="formText">p.formText: 
        <input type="text" name="text" value="input" size="20"></p>
		<input type="button" value="button">
	</form>
	<hr>
	<div class="userbox">
		div.userbox
	</div>
	<hr>
	<div class="menubox">
		<span class="menutitle">
			span.menutitle
		</span>
		<br>
		<a href="index.htm" class="menuitem">a.menuitem</a>
		<br>
		<span class="regNem">span.regNem</span><br>
		<span class="regThis">span.regThis</span><br>
		div.menubox
	</div>
</div>

<div class="centerbox">
	div.centerbox
	<div class="hibabox">
		div.hibabox
		<div class="hibakep">
			div.hibakep
			<img src="warning.png" width="64" height="64">
		</div>
		<div class="hibacim">
			div.hibacim
		</div>
		<div class="hibaszoveg">
			div.hibaszoveg
		</div>
	</div>
	<hr>
	<span class="regHeader">span.regHeader</span>
	<br>
	<span class="star">span.star *</span>
	<br>
	<a name="" class="feature-extra"><span class="hover"><span class="h3">span.feature-extra.h3</span>span.feature-extra.hover</span>a.feature-extra (move mouse here)</a>
	<hr>
	<fieldset class="submit-buttons">
		fieldset.submit-buttons
		<input type="button" value="fieldset button 1">
		<input type="button" value="fieldset button 2">
	</fieldset>
	<hr>
	<table class="forum">
		<tr>
			<th class="forumheader">th.forumheader</th>
			<th class="forumheader">th.forumheader</th>
		</tr>
		<tr>
			<td class="forumlist">td.forumlist</td>
			<td class="forumlist">td.forumlist</td>
		</tr>
	</table>
	<hr>

	<div class="post">
		<div class="postbody">
			<p class="header">p.header</p>
			<h3 class="postheader"><p class="header">h3.postheader - p.header</p></h3>
			<div class="content">
				div.content
			</div>
		</div>
		
		<dl class="postprofile">
			<dt>
				dl.postprofile
			</dt>
			<dd>dd</dd>
		</dl>
	</div>
	<hr><br><br><br><br><br>
	<textarea>textarea</textarea>
	<hr>
	<h2 class="header">h2.header</h2>
	<hr>
	<div class="messagebox">div.messagebox</div>
	<hr>
	<div class="postbox">div.postbox</div>
	<div class="postright">div.postright</div>
</div>

<div class="rightbox">
	div.rightbox
</div>

<div class="footerbox">
	div.footerbox
</div>

</body>

</html>