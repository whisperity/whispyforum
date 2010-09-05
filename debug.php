<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* debug.php
   hibakeresési fájl
*/

/* a hibakeresési script a
   /includes/debug.php fájlban található.
*/

	/* Ez a fájl lényegében csak annyit csinál, hogy
	   átállítja egy DEFINE konstans értékét.
	   
	   A hibakeresési információk megjelenítéséhez
	   a következő kódban a második paraméter értékhez írj 1-et,
	   kikapcsolásához írj 0-t.
	*/
	
	define('SHOWDEBUG', 1);
?>