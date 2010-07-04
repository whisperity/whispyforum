<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* install/createdb.php
   adatbázis létrehozó kód
*/
 file_put_contents('logs/install.log', "Adatbázis létrehozása: " .Datum("normal","nagybetu","dL","H","i","s"). " ( " .time(). " )", FILE_APPEND); // Naplófájl létrehozása
 
 global $cfg, $sql;
 $sql->Connect();
 $sql->Lekerdezes("CREATE DATABASE IF NOT EXISTS " .$cfg['dbname']. " DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci", 'INSTALL');
 $sql->Disconnect();
 
 file_put_contents('logs/install.log', "\r\nAdatbázis létrehozva: " .Datum("normal", "nagybetu", "dL", "H", "i", "s"). " ( " .time(). " )", FILE_APPEND); // Napló zárása
?>