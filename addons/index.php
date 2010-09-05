<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* addons/index.php
   az /addons mappa tartalma önmagában nem nyilvános
   ezért átirányítjuk a usert a ./admin.php fájlra (az addons modul betöltésével)
*/
 
 header('Location: ../admin.php?site=addons'); // Átirányítás
?>