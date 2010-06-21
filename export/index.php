<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* export/index.php
   kód exportáló, kezdőoldal
*/
 include('../config.php');
 print("<link rel='stylesheet' type='text/css' href='../themes/" .THEME_NAME. "/style.css'>
");
 include('../includes/forms.php');
 global $forms;
 
 $forms->StartForm("POST", "exp.php");
 $forms->UrlapElem("submit", "submit", "Exportálás megkezdése", 25, "Az fórumrendszer az előre beállított lista alapján a következő mappába kerül exportálásra: export_" .time(). "\ ", FALSE, time(), "A fenti szám UNIX-timestamp formátumú, a fájlok az exportálás megkezdésekori timestampre mentődnek le");
 $forms->EndForm();
?>