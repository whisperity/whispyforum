<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/index.php
   az /admin mappa tartalma önmagában nem nyilvános
   ezért átirányítjuk a usert a ./admin.php fájlra
*/
 
 header('Location: ../admin.php'); // Átirányítás
?>