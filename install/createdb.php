<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* install/createdb.php
   adatbázis létrehozó kód
*/
 file_put_contents('logs/install.log', "Adatbázis létrehozása: " .Datum("normal","nagybetu","dL","H","i","s"). " ( " .time(). " )", FILE_APPEND); // Naplófájl létrehozása
 
 /* A hibaüzenetek elkerülése érdekében itt a szabványos parancsokat használjuk
    Nem az SQL-osztályt */
 global $cfg;
 $link = mysql_connect($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass']); // Az $sql->Connect; helyett most ezt használjuk
 mysql_query("CREATE DATABASE IF NOT EXISTS " .$cfg['dbname']. " DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci", $link);
 mysql_close($link);
 
 file_put_contents('logs/install.log', "\r\nAdatbázis létrehozva: " .Datum("normal", "nagybetu", "dL", "H", "i", "s"). " ( " .time(). " )", FILE_APPEND); // Napló zárása
?>