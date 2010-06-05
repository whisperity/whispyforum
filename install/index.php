<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* install/index.php
   az /install mappa tartalma önmagában nem nyilvános
   ezért átirányítjuk a usert a ./install.php fájlra
*/
 
 header('Location: ../install.php'); // Átirányítás
?>