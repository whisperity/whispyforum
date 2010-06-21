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
 
 $forms->StartForm("GET", "exp.php");
 $forms->UrlapElem("submit", " ", "Exportálás megkezdése");
 $forms->EndForm();
 
 $forms->StartForm("GET", "imp.php");
 $forms->UrlapElem("submit", " ", "Importálás megkezdése");
 $forms->EndForm();
?>