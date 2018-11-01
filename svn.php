<?php
/**
 * svn log output
 */

// Generate start microtime
$mtime = microtime();
$mtime = explode(' ',$mtime);
$start_time = $mtime[1] + $mtime[0];

unset($mtime); // Remove the array
?>
<html>
<head>
<title>SVN</title>
</head>

<body>
<pre>
<?php
		
// If we didn't set repo or it is "."
exec("svn log -r HEAD:726", $svnlog); // Get the output of 'svn log' into an array

// The array contains each line
foreach ($svnlog as &$logline)
{
	// Output each line with breakline at end
	echo str_replace(array("<", ">"), array("&lt;", "&gt;"), $logline)."\n";
}

?>
</pre>
<?php

// Generate end microtime
$mtime = microtime();
$mtime = explode(' ',$mtime);
$end_time = $mtime[1] + $mtime[0];

unset($mtime); // Remove the array
echo "<hr>
" .round($end_time - $start_time, 3). " seconds";

?>

</body>
</html>

