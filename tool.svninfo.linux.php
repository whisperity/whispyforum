<?php
/**
 * WhispyForum svn info script (linux)
 * 
 * This file uses Subversion binaries
 * to generate an SVN info file.
 */

// This script can be called with ?repo=[TARGET]
// $_GET parameter.
// This parameter defines the repository
// If omitted, it's "." (working copy)

// Set the <div> header (based on REPO)
if ( isset($_GET['repo']) && ($_GET['repo'] != ".") )
{
	// If we set repo and it isn't "."
	$divhead = "Repository (" .$_GET['repo']. ")";
} elseif (!isset($_GET['repo']) || ($_GET['repo'] == ".") )
{
	// If we didn't set repo or it is "."
	$divhead = "Working copy";
}

// Begin HTML output
?>

<html>
<head>
<title>SVN information</title>
</head>

<body>

<link rel="stylesheet" type="text/css" href="themes/winky/style.css">

<div id="menucontainer" style="width: 95%">
	<div id="header"><div id="header_left"></div>
	<div id="header_main"><?php echo $divhead ?> subversion information</div><div id="header_right"></div></div>
    <div id="content">
    	<table border="0" style="width: 94%">
    	<tr>
    	<td><pre>
<?php
		// Generate 'info' (based on repo setting)
		if ( isset($_GET['repo']) && ($_GET['repo'] != ".") )
		{
			// If we set repo and it isn't "."
			exec("svn info " .$_GET['repo'], $svninfo); // Get the output of 'svn info' into an array
		} elseif (!isset($_GET['repo']) || ($_GET['repo'] == ".") )
		{
			// If we didn't set repo or it is "."
			exec("svn info", $svninfo); // Get the output of 'svn info' into an array
		}
		// The array contains each lines
		
		foreach ($svninfo as &$infoline)
		{
			// Output each line with breakline at end
			echo str_replace(array("<", ">"), array("&lt;", "&gt;"), $infoline) ."<br>";
		}
?>
</pre>
	</tr>
	</td>
	</table>
    </div>
    <div id="footer">Generated using Subversion binaries</div>
</div>
<br style="clear: both">
<?php
	// Get the number of revision from 'svn info' output ($svninfo[4] is the line we have to trim)
	$rev = str_replace("Revision: ", "", $svninfo[4]);
?>
<div id="menucontainer" style="width: 95%">
	<div id="header"><div id="header_left"></div>
	<div id="header_main"><?php echo $divhead ?> subversion log (last 50 commits)</div><div id="header_right"></div></div>
    <div id="content">
    	<table border="0" style="width: 94%">
    	<tr>
    	<td><pre>
<?php
		// Generate 'log' (using the latest 50 revisions, based on repo setting)
		if ( isset($_GET['repo']) && ($_GET['repo'] != ".") )
		{
			// If we set repo and it isn't "."
			exec("svn log " .$_GET['repo']. " -r " .$rev. ":" .($rev-50), $svnlog); // Get the output of 'svn log' into an array
		} elseif (!isset($_GET['repo']) || ($_GET['repo'] == ".") )
		{
			// If we didn't set repo or it is "."
			exec("svn log -r " .$rev. ":" .($rev-50), $svnlog); // Get the output of 'svn log' into an array
		}
		
		// The array contains each lines
		
		foreach ($svnlog as &$logline)
		{
			// Output each line with breakline at end
			echo str_replace(array("<", ">"), array("&lt;", "&gt;"), $logline) ."<br>";
		}
?>
</pre>
	</tr>
	</td>
	</table>
    </div>
    <div id="footer">Generated using Subversion binaries</div>
</div>
<br style="clear: both">
<div id="menucontainer" style="width: 95%">
	<div id="header"><div id="header_left"></div>
	<div id="header_main"><?php echo $divhead ?> subversion diff</div><div id="header_right"></div></div>
    <div id="content">
    	<table border="0" style="width: 94%">
    	<tr>
    	<td><pre>
<?php
		// Generate 'diff' (based on REPO setting)
		if ( isset($_GET['repo']) && ($_GET['repo'] != ".") )
		{
			// If we set repo and it isn't "."
			exec("svn diff " .$_GET['repo'], $svndiff); // Get the output of 'svn diff' into an array
		} elseif (!isset($_GET['repo']) || ($_GET['repo'] == ".") )
		{
			// If we didn't set repo or it is "."
			exec("svn diff", $svndiff); // Get the output of 'svn diff' into an array
		}
		
		// The array contains each lines
		
		foreach ($svndiff as &$diffline)
		{
			// Output each line with breakline at end
			echo str_replace(array("<", ">"), array("&lt;", "&gt;"), $diffline) ."<br>";
		}
?>
</pre>
	</tr>
	</td>
	</table>
    </div>
    <div id="footer">Generated using Subversion binaries</div>
</div>
</body>
</html>

