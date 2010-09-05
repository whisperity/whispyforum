<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* analytics.php
   google analytics kódja
*/
	// A Google Analytics követés bekapcsolásához állítsd ezt a változót 1, kikapcsolásához 0 értékbe
	define('GOOGLE_ANALYTICS', 0);
	
	// A Google Analytics követőkód (UA-12345678-1)
	define('GOOGLE_ANALYTICS_ID', "UA-12345678-1");
	
	function GenerateGoogleAnalyticsJS() // A kód generálása
	{
		echo "<!--- Google Analytics Követőkód --->
<script type='text/javascript'>
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '" .GOOGLE_ANALYTICS_ID. "']);
	_gaq.push(['_trackPageview']);
	
	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();

</script>
	<!--- Google Analytics Követőkód VÉGE --->";
	}
?>