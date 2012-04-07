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

function opFormat($line)
{
	/*
	 * This function formats the output lines
	 * @inputs: $line -- input line
	 * @outputs: returns formatted line
	 */
	
	$wrong = array(
		"<",
		">"
	); // Array containing strings that must be formatted
	
	$right = array(
		"&lt;",
		"&gt;"
	); // Array containing the proper output format 
	
	return str_replace($wrong, $right, $line);

}

function statusButt_UNKNOWN($file)
{
	/*
	 * Output buttons for unknown files
	 * 
	 * Buttons:
	 *  - add (to version control)
	 *  - delete (the file)
	 * 
	 * @inputs: $file -- filename
	 * @outputs: returns formatted line
	 */
	
	print("?       " .$file. " " . '<a href="tool.svninfo.linux.php?repo=.&filename=' .$file. '&command=add">Add</a> <a href="tool.svninfo.linux.php?repo=.&filename=' .$file. '&command=delete">Delete file</a>');
}

function statusButt_MODIFIED($file)
{
	/*
	 * Output buttons for modified files
	 * 
	 * Buttons:
	 *  - revert (to BASE, losing all modifications since update)
	 * 
	 * @inputs: $file -- filename
	 * @outputs: returns formatted line
	 */
	
	print("M       " .$file. " " . '<a href="tool.svninfo.linux.php?repo=.&filename=' .$file. '&command=revert">Revert (lose local changes)</a> <a href="tool.svninfo.linux.php?repo=.&filename=' .$file. '&command=diff">Diff</a>');
}

function statusButt_ADDED($file)
{
	/*
	 * Output buttons for added files
	 * 
	 * Buttons:
	 *  - revert (to BASE, removing the file from version control)
	 * 
	 * @inputs: $file -- filename
	 * @outputs: returns formatted line
	 */
	
	print("A       " .$file. " " . '<a href="tool.svninfo.linux.php?repo=.&filename=' .$file. '&command=revert">Remove from version control</a>');
}

function statusButtons($line)
{
	/*
	 * This function formats the output lines of the STATUS box (giving
	 * add/delete/revert/etc. boxes)
	 * 
	 * @inputs: $line -- input line
	 * @outputs: returns formatted line
	 */
	
	$statuses = explode("       ", $line);
	// Explode the line to two variables
	// $statuses[0] : file status (?, M, A, D, U, !, etc)
	// $statuses[1] : file name (relative to repo directory)
	
	switch ($statuses[0])
	{
		case "?":
			echo statusButt_UNKNOWN($statuses[1]);
			break;
		case "M":
			echo statusButt_MODIFIED($statuses[1]);
			break;
		case "A":
			echo statusButt_ADDED($statuses[1]);
			break;
		default:
			echo $statuses[0]. "       ".$statuses[1]; // Upload general line
			break;
	}
}

// Begin HTML output
?>

<html>
<head>
<title>SVN information</title>
</head>

<body>

<link rel="stylesheet" type="text/css" href="themes/tuvia/style.css">
<?php
	if ( isset($_GET['command']) )
	{
		// If we specified a command
		switch ($_GET['command'])
		{
			// Do something based on command
			case "update":
				?>
<div id="menucontainer" style="width: 95%">
	<div id="header"><div id="header_left"></div>
	<div id="header_main"><?php echo $divhead ?> subversion update</div><div id="header_right"></div></div>
    <div id="content">
    	<table border="0" style="width: 94%">
    	<tr>
    	<td>
<?php
	if ( isset($_GET['repo']) && ($_GET['repo'] != ".") )
	{
?>
	<span class="red-star">Remote repositories could not be updated.</span>
<?php
	} elseif (!isset($_GET['repo']) || ($_GET['repo'] == ".") )
	{
		exec("svn update", $svnupdate); // Get the output of 'svn update' into an array
		// The array contains each lines
		
		echo "<pre>";
		foreach ($svnupdate as &$updateline)
		{
			// Output each line with breakline at end
			echo opFormat($updateline)."<br>";
		}
		echo "</pre>";
	}
?>
	<form method="GET" action="tool.svninfo.linux.php">
<input type="hidden" name="repo" value=".">
<input type="submit" value="Back">
</form>
	</tr>
	</td>
	</table>
    </div>
    <div id="footer"><a href="#top">Top</a></div>
</div>
<?php
				exit;
				break;
			case "add":
				?>
<div id="menucontainer" style="width: 95%">
	<div id="header"><div id="header_left"></div>
	<div id="header_main">Subversion file (<?php echo @$_GET['filename'] ?>) ADD</div><div id="header_right"></div></div>
    <div id="content">
    	<table border="0" style="width: 94%">
    	<tr>
    	<td>
<?php
	if ( !isset($_GET['filename']) )
	{
		print('<span class="red-star">Filename was not specified.</span>');
	} elseif ( isset($_GET['filename']) )
	{
	
	if ( isset($_GET['repo']) && ($_GET['repo'] != ".") )
	{
?>
	<span class="red-star">Remote repositories could not be administered.</span>
<?php
	} elseif (!isset($_GET['repo']) || ($_GET['repo'] == ".") )
	{
		exec("svn add " .@$_GET['filename'], $svnadd); // Get the output of 'svn add' into an array
		// The array contains each lines
		echo "<pre>";
		foreach ($svnadd as &$addline)
		{
			// Output each line with breakline at end
			echo opFormat($addline)."<br>";
		}
		echo "</pre>";
	}
	}
?>
	<form method="GET" action="tool.svninfo.linux.php">
<input type="hidden" name="repo" value=".">
<input type="submit" value="Back">
</form>
	</tr>
	</td>
	</table>
    </div>
    <div id="footer"><a href="#top">Top</a></div>
</div>
<?php
				exit;
				break;
			case "delete":
				?>
<div id="menucontainer" style="width: 95%">
	<div id="header"><div id="header_left"></div>
	<div id="header_main">File (<?php echo @$_GET['filename'] ?>) DELETE</div><div id="header_right"></div></div>
    <div id="content">
    	<table border="0" style="width: 94%">
    	<tr>
    	<td>
<?php
	if ( !isset($_GET['filename']) )
	{
		print('<span class="red-star">Filename was not specified.</span>');
	} elseif ( isset($_GET['filename']) )
	{
	
	if ( isset($_GET['repo']) && ($_GET['repo'] != ".") )
	{
?>
	<span class="red-star">Remote repositories could not be administered.</span>
<?php
	} elseif (!isset($_GET['repo']) || ($_GET['repo'] == ".") )
	{
		exec("rm " .@$_GET['filename'], $removefile); // Get the output of 'rm' into an array
		// The array contains each lines
		
		foreach ($removefile as &$removeline)
		{
			// Output each line with breakline at end
			echo opFormat($removeline)."<br>";
		}
	}
	}
?>
	<form method="GET" action="tool.svninfo.linux.php">
<input type="hidden" name="repo" value=".">
<input type="submit" value="Back">
</form>
	</tr>
	</td>
	</table>
    </div>
    <div id="footer"><a href="#top">Top</a></div>
</div>
<?php
				exit;
				break;
			case "revert":
				?>
<div id="menucontainer" style="width: 95%">
	<div id="header"><div id="header_left"></div>
	<div id="header_main">Subversion file (<?php echo @$_GET['filename'] ?>) REVERT</div><div id="header_right"></div></div>
    <div id="content">
    	<table border="0" style="width: 94%">
    	<tr>
    	<td>
<?php
	if ( !isset($_GET['filename']) )
	{
		print('<span class="red-star">Filename was not specified.</span>');
	} elseif ( isset($_GET['filename']) )
	{
	
	if ( isset($_GET['repo']) && ($_GET['repo'] != ".") )
	{
?>
	<span class="red-star">Remote repositories could not be administered.</span>
<?php
	} elseif (!isset($_GET['repo']) || ($_GET['repo'] == ".") )
	{
		exec("svn revert " .@$_GET['filename'], $svnrevert); // Get the output of 'svn revert' into an array
		// The array contains each lines
		
		echo "<pre>";
		foreach ($svnrevert as &$revertline)
		{
			// Output each line with breakline at end
			echo opFormat($revertline)."<br>";
		}
		echo "</pre>";
	}
	}
?>
	<form method="GET" action="tool.svninfo.linux.php">
<input type="hidden" name="repo" value=".">
<input type="submit" value="Back">
</form>
	</tr>
	</td>
	</table>
    </div>
    <div id="footer"><a href="#top">Top</a></div>
</div>
<?php
				exit;
				break;
			case "commit":
				?>
<div id="menucontainer" style="width: 95%">
	<div id="header"><div id="header_left"></div>
	<div id="header_main"><?php echo $divhead ?> subversion commit</div><div id="header_right"></div></div>
    <div id="content">
    	<table border="0" style="width: 94%">
    	<tr>
    	<td>
<?php
	if ( !isset($_GET['message']) )
	{
		print('<span class="red-star">Commit message was not specified.</span>');
	} elseif ( isset($_GET['message']) )
	{
	
	if ( isset($_GET['repo']) && ($_GET['repo'] != ".") )
	{
?>
	<span class="red-star">Remote repositories could not be commited.</span>
<?php
	} elseif (!isset($_GET['repo']) || ($_GET['repo'] == ".") )
	{
		echo '<font class="emphasis">Commit message:</font><br>'.
		str_replace("\n", "<br>", opFormat($_GET['message']));
		
		// Put the commit message into a file
		file_put_contents("commit.log", $_GET['message']."\n(via tool.svninfo.linux.php)");
		
		// * COMMIT * //
		if ( (isset($_GET['username'])) && (isset($_GET['password'])) )
		{
			// If we set a username and a password
			exec('svn commit --file commit.log --username ' .$_GET['username']. ' --password ' .$_GET['password'], $svncommit); // Get the output of 'svn commit' into an array
		} else {
			// If we commit anonymously
			exec('svn commit --file commit.log', $svncommit); // Get the output of 'svn commit' into an array
		}
		// The array contains each lines
		
		echo "<pre>";
		foreach ($svncommit as &$commitline)
		{
			// Output each line with breakline at end
			echo opFormat($commitline)."<br>";
		}
		echo "</pre>";
		// * COMMIT * //
		
		// Remove temporary file
		exec("rm commit.log");
	}
	}
?>
	<form method="GET" action="tool.svninfo.linux.php">
<input type="hidden" name="repo" value=".">
<input type="submit" value="Back">
</form>
	</tr>
	</td>
	</table>
    </div>
    <div id="footer"><a href="#top">Top</a></div>
</div>
<?php
				exit;
				break;
			case "diff":
				?>
<div id="menucontainer" style="width: 95%">
	<div id="header"><div id="header_left"></div>
	<div id="header_main"><?php echo @$_GET['filename'] ?> diff</div><div id="header_right"></div></div>
    <div id="content">
    	<table border="0" style="width: 94%">
    	<tr>
    	<td>
<?php
	if ( !isset($_GET['filename']) )
	{
		print('<span class="red-star">Filename was not specified</span>');
	} elseif ( isset($_GET['filename']) )
	{
	
	if ( isset($_GET['repo']) && ($_GET['repo'] != ".") )
	{
?>
	<span class="red-star">Remote repositories could not be diff'd.</span>
<?php
	} elseif (!isset($_GET['repo']) || ($_GET['repo'] == ".") )
	{
		exec("svn diff " .@$_GET['filename'], $svnFdiff); // Get the output of 'svn diff' into an array
		// The array contains each lines
		
		echo "<pre>";
		foreach ($svnFdiff as &$Fdiffline)
		{
			// Output each line with breakline at end
			echo opFormat($Fdiffline)."<br>";
		}
		echo "</pre>";
	}
	}
?>
	<form method="GET" action="tool.svninfo.linux.php">
<input type="hidden" name="repo" value=".">
<input type="submit" value="Back">
</form>
	</tr>
	</td>
	</table>
    </div>
    <div id="footer"><a href="#top">Top</a></div>
</div>
<?php
				exit;
				break;
		}
	}
?>
<a name="top">
<h4>Table of contents</h3>
<ul>
	<li><a href="#info">Information <tt>info</tt></a></li>
	<li><a href="#log">Log (25 latest commits) <tt>log</tt></a></li>
	<li><a href="#diff">Differences <tt>diff</tt></a></li>
	<li><a href="#status">Status <tt>status</tt>, <tt>stat</tt>, <tt>st</tt> (and control)</a></li>
	<li><a href="#update">Update <tt>update</tt>, <tt>up</tt></a></li>
	<li><a href="#commit">Commit <tt>commit</tt>, <tt>ci</tt></a></li>
</ul>
<a name="info"><div id="menucontainer" style="width: 95%">
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
			echo opFormat($infoline)."<br>";
		}
?>
</pre>
	</tr>
	</td>
	</table>
    </div>
    <div id="footer"><a href="#top">Top</a></div>
</div>
<br style="clear: both">
<a name="log"><div id="menucontainer" style="width: 95%">
	<div id="header"><div id="header_left"></div>
	<div id="header_main"><?php echo $divhead ?> subversion log (last 25 commits)</div><div id="header_right"></div></div>
    <div id="content">
    	<table border="0" style="width: 94%">
    	<tr>
    	<td><pre>
<?php
		// Generate 'log' (using the latest 25 revisions, based on repo setting)
		if ( isset($_GET['repo']) && ($_GET['repo'] != ".") )
		{
			// If we set repo and it isn't "."
			exec("svn log " .$_GET['repo']. " -l 25", $svnlog); // Get the output of 'svn log' into an array
		} elseif (!isset($_GET['repo']) || ($_GET['repo'] == ".") )
		{
			// If we didn't set repo or it is "."
			exec("svn log -l 25", $svnlog); // Get the output of 'svn log' into an array
		}
		
		// The array contains each lines
		
		foreach ($svnlog as &$logline)
		{
			// Output each line with breakline at end
			echo opFormat($logline) ."<br>";
		}
?>
</pre>
	</tr>
	</td>
	</table>
    </div>
    <div id="footer"><a href="#top">Top</a></div>
</div>
<br style="clear: both">
<a name="diff"><div id="menucontainer" style="width: 95%">
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
			echo opFormat($diffline) ."<br>";
		}
?>
</pre>
	</tr>
	</td>
	</table>
    </div>
    <div id="footer"><a href="#top">Top</a></div>
</div>
<br style="clear: both">
<a name="status"><div id="menucontainer" style="width: 95%">
	<div id="header"><div id="header_left"></div>
	<div id="header_main"><?php echo $divhead ?> subversion status and control</div><div id="header_right"></div></div>
    <div id="content">
    	<table border="0" style="width: 94%">
    	<tr>
    	<td><pre>
<?php
		// Generate 'diff' (based on REPO setting)
		if ( isset($_GET['repo']) && ($_GET['repo'] != ".") )
		{
			// If we set repo and it isn't "."
			exec("svn status " .$_GET['repo'], $svnstatus); // Get the output of 'svn status' into an array
		} elseif (!isset($_GET['repo']) || ($_GET['repo'] == ".") )
		{
			// If we didn't set repo or it is "."
			exec("svn status", $svnstatus); // Get the output of 'svn status' into an array
		}
		
		// The array contains each lines
		
		foreach ($svnstatus as &$statusline)
		{
			// Output each line with breakline at end
			
			
			if ( isset($_GET['repo']) && ($_GET['repo'] != ".") )
			{
				// If we set repo and it isn't "."
				echo opFormat($statusline) ."<br>"; // Give only the list
			} elseif (!isset($_GET['repo']) || ($_GET['repo'] == ".") )
			{
				// If we didn't set repo or it is "."
				echo statusButtons(opFormat($statusline)) ."<br>"; // Give the list and the buttons
			}
		}
?>
</pre>
	</tr>
	</td>
	</table>
    </div>
    <div id="footer"><a href="#top">Top</a></div>
</div>
<br style="clear: both">
<a name="update"><div id="menucontainer" style="width: 95%">
	<div id="header"><div id="header_left"></div>
	<div id="header_main"><?php echo $divhead ?> subversion update</div><div id="header_right"></div></div>
    <div id="content">
    	<table border="0" style="width: 94%">
    	<tr>
    	<td>If you think your repository need updating, press this button. <font class="emphasis">The web server deamon needs to have <i>write</i> rights to the repository directory.</font>
<?php
	if ( isset($_GET['repo']) && ($_GET['repo'] != ".") )
	{
?>
	<br>
	<span class="red-star">Remote repositories could not be updated.</span>
<?php
	} elseif (!isset($_GET['repo']) || ($_GET['repo'] == ".") )
	{
?>
<form method="GET" action="tool.svninfo.linux.php">
<input type="hidden" name="repo" value=".">
<input type="hidden" name="command" value="update">
<input type="submit" value="Update">
</form>
<?php
	}
?>
	</tr>
	</td>
	</table>
    </div>
    <div id="footer"><a href="#top">Top</a></div>
</div>
<br style="clear: both">
<a name="commit"><div id="menucontainer" style="width: 95%">
	<div id="header"><div id="header_left"></div>
	<div id="header_main"><?php echo $divhead ?> subversion commit (remote repository: <?php echo $svninfo[1]; ?>)</div><div id="header_right"></div></div>
    <div id="content">
    	<table border="0" style="width: 94%">
    	<tr>
    	<td>If you think your repository needs commiting, press this button. Alternatively, you can enter a commit message. <font class="emphasis">The web server deamon needs to have <i>read</i> rights to the repository directory. The local <tt>subversion</tt> has to be configured, so you can write the remote reposity.</font><br>
<?php
	if ( isset($_GET['repo']) && ($_GET['repo'] != ".") )
	{
?>
	<br>
	<span class="red-star">Remote repositories could not be commited.</span>
<?php
	} elseif (!isset($_GET['repo']) || ($_GET['repo'] == ".") )
	{
?>
<form method="GET" action="tool.svninfo.linux.php">
<textarea name="message" cols="55" rows="18">Commited some changes via tool.svninfo.linux.php</textarea><br>
Username: <input type="text" name="username" value="" size="35"><br>
Password: <input type="password" name="password" value="" size="35"><br>
<input type="hidden" name="repo" value=".">
<input type="hidden" name="command" value="commit">
<input type="submit" value="Commit">
</form>
<?php
	}
?>
	</tr>
	</td>
	</table>
    </div>
    <div id="footer"><a href="#top">Top</a></div>
</div>
</body>
</html>

