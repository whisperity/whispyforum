<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* addons/clock/index.php
   az /addons/clock (óra) mappa tartalma ne jelenjen meg más felhasználóknak
   ezért átirányítjuk a usert a ./admin.php fájlra (az addons modul betöltésével)
*/
 header('Location: ../admin.php?site=addons'); // Átirányítás
?>