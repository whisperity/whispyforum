<?php
/**
 * WhispyForum svn info script (windows)
 * 
 * This file uses TortoiseSVN SubWCRev application
 * to generate an SVN info file.
 */

// chr(92) generates a \ (backslash) without escaping strings
define('WC_PATH', "C:" .chr(92). "xampp" .chr(92). "htdocs"); // Working copy path
define('svnInfoFN', "cached.swcr-info.php"); // Subversion info file name (output file)
define('TSVNpath', "C:" .chr(92). "Program Files" .chr(92). "TortoiseSVN"); // Tortoise SVN Windows shell extension SVN client path
define('tplFN', "cached.swcr-template.php"); // Template file name

// First, we put the template information into the template file

file_put_contents(tplFN, '<?php' ."\r\n".
'$svnInfo = array(' ."\r\n".
'"title" => "$WCURL$ at revision $WCREV$",' ."\r\n".
'"revision" => "$WCREV$",' ."\r\n".
'"modified" => "$WCMODS?Modified:Not modified$",' ."\r\n".
'"date"     => "$WCDATE$",' ."\r\n".
'"range"    => "$WCRANGE$",' ."\r\n".
'"mixed"    => "$WCMIXED?Mixed revision WC:Not mixed$",' ."\r\n".
'"URL"      => "$WCURL$"'. "\r\n".
');' ."\r\n".
'?>');

// Run the TSvn SubWCRev command to generate the svn file
$cmd = '"' .TSVNpath.'\bin\SubWCRev.exe" "' .WC_PATH. '" "' .WC_PATH.chr(92).tplFN. '" "' .WC_PATH.chr(92).svnInfoFN. '"'; // This is the command.

exec($cmd);

// Load the generated SVN info file
// it's a PHP script and it gives us an array
include(svnInfoFN);

// Begin HTML output
?>

<html>
<head>
<title>SVN information</title>
</head>

<body>

<link rel="stylesheet" type="text/css" href="themes/tuvia/style.css">

<div id="menucontainer" style="width: 640px">
	<div id="header"><div id="header_left"></div>
	<div id="header_main">Working copy (<? echo WC_PATH; ?>) subversion information</div><div id="header_right"></div></div>
    <div id="content">
	<table border="0" style="width: 630px">
	<tr>
		<th colspan="2"><? echo $svnInfo['URL']; ?> at revision <? echo $svnInfo['revision']; ?></th>
	</tr>
	
	<tr>
		<td>URL</td>
		<td><? echo $svnInfo['URL']; ?></td>
	</tr>
	
	<tr>
		<td>Revision</td>
		<td><? echo $svnInfo['revision']; ?></td>
	</tr>
	
	<tr>
		<td>Date</td>
		<td><? echo $svnInfo['date']; ?></td>
	</tr>
	
	<tr>
		<td>Rev. range</td>
		<td><? echo $svnInfo['range']; ?></td>
	</tr>
	
	<tr>
		<td>Modified</td>
		<td><? echo $svnInfo['modified']; ?></td>
	</tr>
	
	<tr>
		<td>Mixed</td>
		<td><? echo $svnInfo['mixed']; ?></td>
	</tr>
	
	<tr>
		<td>Local WC path</td>
		<td><? echo WC_PATH; ?></td>
	</tr>
	
	
	<tr>
		<td>Tortoise SVN path</td>
		<td><? echo TSVNpath; ?></td>
	</tr>
	
	<tr>
		<td>Template filename</td>
		<td><? echo tplFN; ?></td>
	</tr>
	
	<tr>
		<td>SVN-info filename</td>
		<td><? echo svnInfoFN; ?></td>
	</tr>
	
	</table>
    </div>
    <div id="footer">Generated using <a href="http://tortoisesvn.tigris.org" target="_blank" alt="TortoiseSVN's homepage">TortoiseSVN</a>'s SubWCRev application</div>
</div>
<br>
<?php
// chr(92) generates a \ (backslash) without escaping strings
define('WC_PATH_S', "C:" .chr(92). "xampp" .chr(92). "wiki"); // Second working copy path
define('svnInfoFN_S', "cached.swcr-info-second.php"); // Second subversion info file name (output file)
define('tplFN_S', "cached.swcr-template-second.php"); // Second template file name

// First, we put the template information into the template file

file_put_contents(tplFN_S, '<?php' ."\r\n".
'$svnInfo2 = array(' ."\r\n".
'"title" => "$WCURL$ at revision $WCREV$",' ."\r\n".
'"revision" => "$WCREV$",' ."\r\n".
'"modified" => "$WCMODS?Modified:Not modified$",' ."\r\n".
'"date"     => "$WCDATE$",' ."\r\n".
'"range"    => "$WCRANGE$",' ."\r\n".
'"mixed"    => "$WCMIXED?Mixed revision WC:Not mixed$",' ."\r\n".
'"URL"      => "$WCURL$"'. "\r\n".
');' ."\r\n".
'?>');

// Run the TSvn SubWCRev command to generate the svn file
$cmd2 = '"' .TSVNpath.'\bin\SubWCRev.exe" "' .WC_PATH_S. '" "' .WC_PATH.chr(92).tplFN_S. '" "' .WC_PATH.chr(92).svnInfoFN_S. '"'; // This is the command.

exec($cmd2);

// Load the generated SVN info file
// it's a PHP script and it gives us an array
include(svnInfoFN_S);

// Begin second HTML output
?>
<br>
<div id="menucontainer" style="width: 640px">
	<div id="header"><div id="header_left"></div>
	<div id="header_main">Working copy (<? echo WC_PATH_S; ?>) subversion information</div><div id="header_right"></div></div>
    <div id="content">
	<table border="0" style="width: 630px">
	<tr>
		<th colspan="2"><? echo $svnInfo2['URL']; ?> at revision <? echo $svnInfo2['revision']; ?></th>
	</tr>
	
	<tr>
		<td>URL</td>
		<td><? echo $svnInfo2['URL']; ?></td>
	</tr>
	
	<tr>
		<td>Revision</td>
		<td><? echo $svnInfo2['revision']; ?></td>
	</tr>
	
	<tr>
		<td>Date</td>
		<td><? echo $svnInfo2['date']; ?></td>
	</tr>
	
	<tr>
		<td>Rev. range</td>
		<td><? echo $svnInfo2['range']; ?></td>
	</tr>
	
	<tr>
		<td>Modified</td>
		<td><? echo $svnInfo2['modified']; ?></td>
	</tr>
	
	<tr>
		<td>Mixed</td>
		<td><? echo $svnInfo2['mixed']; ?></td>
	</tr>
	
	<tr>
		<td>Local WC path</td>
		<td><? echo WC_PATH_S; ?></td>
	</tr>
	
	
	<tr>
		<td>Tortoise SVN path</td>
		<td><? echo TSVNpath; ?></td>
	</tr>
	
	<tr>
		<td>Template filename</td>
		<td><? echo tplFN_S; ?></td>
	</tr>
	
	<tr>
		<td>SVN-info filename</td>
		<td><? echo svnInfoFN_S; ?></td>
	</tr>
	
	</table>
    </div>
    <div id="footer">Generated using <a href="http://tortoisesvn.tigris.org" target="_blank" alt="TortoiseSVN's homepage">TortoiseSVN</a>'s SubWCRev application</div>
</div>

</body>
</html>

<?php
// Unlink all files
unlink(tplFN);
unlink(tplFN_S);
unlink(svnInfoFN);
unlink(svnInfoFN_S);
?>
