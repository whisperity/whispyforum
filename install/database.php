<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* install/database.php
   adatbázis tábla létrehozó rendszer
*/
 $sql->Connect();
 file_put_contents('install.log', "Táblák létrehozása megkezdve: " .Datum("normal","nagybetu","dL","H","i","s"). " ( " .time(). " )", FILE_APPEND); // Naplófájl létrehozása
 function WOut( $tipus, $tabla, $sor = '' )
 {
	global $cfg;
	// Sikeresség kiírása
	print("<div class='messagebox'>"); // Üzenetdoboz
	
	/* Típus alapján megfelelő üzenet kiírása */
	switch ($tipus)
	{
		case "tabla":
			print("<small>A tábla</small> <b>`" .$cfg['dbname']. "`.`" .$cfg['tbprf'].$tabla."`</b> <small>sikeresen létrehozva.</small>"); // Kiírás
			file_put_contents('install.log', "\r\nA tábla `" .$cfg['dbname']. "`.`" .$cfg['tbprf'].$tabla."` sikeresen létrehozva.", FILE_APPEND); // Napló
			break;
		case "sor":
			print("<small>Új sor hozzáadva a táblához:</small> <b>`" .$cfg['dbname']. "`.`" .$cfg['tbprf'].$tabla."`</b><br><small>A sor adatai:</small> " .$sor); // Kiírás
			file_put_contents('install.log', "\r\nÚj sor hozzáadva a táblához: `" .$cfg['dbname']. "`.`" .$cfg['tbprf'].$tabla."`\r\nA sor adatai: " .$sor, FILE_APPEND); // Napló
			break;
	}
	print("</div>"); // Dobozzárás, újsor
 }
 
 /* Adattáblák létrehozása */
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."user (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `pwd` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `realName` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `regsessid` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `regip` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0.0.0.0',
  `lastip` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0.0.0.0',
  `lastsessid` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `curip` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0.0.0.0',
  `cursessid` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `regdate` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `loggedin` tinyint(1) NOT NULL DEFAULT '0',
  `activated` tinyint(1) NOT NULL DEFAULT '0',
  `activatedate` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `userLevel` tinyint(1) NOT NULL DEFAULT '0',
  `activateToken` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `postCount` int(10) NOT NULL DEFAULT '0',
  `lastlogintime` VARCHAR(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `theme` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  `bemutatkozas` text COLLATE utf8_unicode_ci NOT NULL,
  `thely` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Felhasználók
 WOut('tabla', 'user');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."modules (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `side` tinyint(1) NOT NULL DEFAULT '1',
  `hOrder` INT(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Modulok
 WOut('tabla', 'modules');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."menuitems (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `menuId` int(10) NOT NULL,
  `text` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `href` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `hOrder` INT(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Menüelemek
 WOut('tabla', 'menuitems');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."forum (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `topics` int(10) NOT NULL DEFAULT '0',
  `posts` int(10) NOT NULL DEFAULT '0',
  `lastpostdate` varchar(24) COLLATE utf8_unicode_ci NOT NULL,
  `lastuser` int(10) NOT NULL DEFAULT '0',
  `lpTopic` int(10) NOT NULL DEFAULT '0',
  `lpId` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Fórumok
 WOut('tabla', 'forum');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."topics (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `fId` INT(10) NOT NULL,
  `name` VARCHAR(128) COLLATE utf8_unicode_ci NOT NULL,
  `type` TINYINT(1) NOT NULL DEFAULT '1',
  `startuser` INT(10) NOT NULL,
  `startdate` VARCHAR(24) COLLATE utf8_unicode_ci NOT NULL,
  `lastuser` INT(10) NOT NULL,
  `lastpostdate` VARCHAR(24) COLLATE utf8_unicode_ci NOT NULL,
  `replies` INT(10) NOT NULL,
  `opens` INT(10) NOT NULL,
  `locked` TINYINT(1) NOT NULL DEFAULT '0',
  `lpId` INT(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Fórumtopicok
 WOut('tabla', 'topics');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."posts (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tId` int(10) NOT NULL,
  `uId` int(10) NOT NULL,
  `pTitle` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `pText` text COLLATE utf8_unicode_ci NOT NULL,
  `pDate` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `edited` TINYINT(1) NOT NULL DEFAULT '0',
  `euId` INT(10) DEFAULT '0',
  `eDate` VARCHAR(32) COLLATE utf8_unicode_ci DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Postok
 WOut('tabla', 'posts');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."news (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(64) COLLATE utf8_unicode_ci NOT NULL,
  `text` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `postDate` VARCHAR(32) COLLATE utf8_unicode_ci NOT NULL,
  `uId` INT(10) NOT NULL,
  `commentable` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Hírek
 WOut('tabla', 'news');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."news_comments (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `nId` INT(10) NOT NULL,
  `uId` INT(10) NOT NULL,
  `text` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `postDate` VARCHAR(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Hírhozzászólások
 WOut('tabla', 'news_comments');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."bannedips (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0.0.0.0',
  `bandate` VARCHAR(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `uId` INT(10) NOT NULL DEFAULT '0',
  `comment` VARCHAR(512) COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // IP kitiltások
 WOut('tabla', 'bannedips');

 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."addons (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `subdir` VARCHAR(128) COLLATE utf8_unicode_ci NOT NULL,
  `name` VARCHAR(256) COLLATE utf8_unicode_ci NOT NULL,
  `descr` TEXT COLLATE utf8_unicode_ci NOT NULL,
  `author` VARCHAR(128) COLLATE utf8_unicode_ci,
  `authoremail` VARCHAR(128) COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Addonok
 WOut('tabla', 'addons');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."version (
  `RELEASE_TYPE` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `VERSION` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `RELEASE_DATE` varchar(128) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Verzióadatok
 WOut('tabla', 'version');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."siteconfig (
  `variable` VARCHAR(64) COLLATE utf8_unicode_ci NOT NULL,
  `value` VARCHAR(1024) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Portál-beállítások
 WOut('tabla', 'version');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."plain (
`id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
`title` VARCHAR(512) COLLATE utf8_unicode_ci NOT NULL,
`content` TEXT COLLATE utf8_unicode_ci NOT NULL
) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Statikus tartalom
 WOut('tabla', 'plain');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."downloads (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cid` int(10) NOT NULL DEFAULT '0',
  `uid` int(10) NOT NULL DEFAULT '0',
  `href` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `descr` text COLLATE utf8_unicode_ci NOT NULL,
  `download_count` int(11) NOT NULL DEFAULT '0',
  `upload_date` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Letöltések
 WOut('tabla', 'downloads');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."download_categ (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `descr` text COLLATE utf8_unicode_ci NOT NULL,
  `files` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Letöltés kategóriák
 WOut('tabla', 'download_categ');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."polls (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `opcount` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Szavazások
 WOut('tabla', 'polls');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."poll_opinions (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `pollid` int(10) NOT NULL DEFAULT '0',
  `opinion` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `opinionid` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Szavazás lehetőségek
 WOut('tabla', 'poll_opinions');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."votes_cast (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL DEFAULT '0',
  `pollid` int(10) NOT NULL DEFAULT '0',
  `opinionid` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Beküldött szavazatok
 WOut('tabla', 'votes_cast');
 
 $sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."statistics (
  `ip` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0.0.0.0',
  `year` int(10) NOT NULL DEFAULT '0',
  `month` int(10) NOT NULL DEFAULT '0',
  `day` int(10) NOT NULL DEFAULT '0',
  `hour` int(10) NOT NULL DEFAULT '0',
  `minute` int(10) NOT NULL DEFAULT '0',
  `second` int(10) NOT NULL DEFAULT '0',
  `epoch` VARCHAR(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); // Látogatottsági adatok
 WOut('tabla', 'statistics');
 
 if ( $exampledata == 'yes' )
 {
 /* Kezdeti adatok */
 // Létrehozás csak akkor, ha ezt az opciót választottuk
 /* Modulok */
 $sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."modules(name,type,side, hOrder) VALUES
	('Főmenü','menu','1', 0),
	('voteModule','coremodule','2', 0)"); // Főmenü
 WOut('sor', 'modules', 'Főmenü');
 WOut('sor', 'modules', 'voteModule - Szavazás');
 
 /* Menüelemek */
 $sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."menuitems(menuId, text, href, hOrder) VALUES
	('1','Kezdőlap','index.php', 1),
	('1','Hírek','news.php', 2),
	('1','Fórum','viewforum.php', 3),
	('1','Letöltések','download.php', 4),
	('1','Statikus tartalom','plain.php?id=1', 5),
	('1','Google keresés','http://google.hu', 6)"); // Főmenü elemei
 WOut('sor', 'menuitems', 'Főmenü/Kezdőlap');
 WOut('sor', 'menuitems', 'Főmenü/Fórum');
 WOut('sor', 'menuitems', 'Főmenü/Hírek');
 WOut('sor', 'menuitems', 'Főmenü/Letöltések');
 WOut('sor', 'menuitems', 'Főmenü/Statikus tartalom');
 WOut('sor', 'menuitems', 'Főmenü/Google keresés (google.hu)');
 
 /* Fórumok */
 $sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."forum(name, description, topics, posts, lastpostdate, lastuser, lpTopic, lpId) VALUES
	('Első fórumod', 'Ez az első fórumod', '1', '1', '" .time(). "', '1', '1', '1')"); // Első fórumod
 WOut('sor', 'forum', 'Első fórumod');
 
 /* Témák */
 $sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."topics(fId, name, type, startuser, startdate, lastuser, lastpostdate, replies, opens, locked)
	VALUES ('1', 'Első témád, használd örömmel', '1', '1', '" .time(). "', '1', '" .time(). "', '1', '0', '0')"); // Első témád
 WOut('sor', 'topics', 'Első fórumod/Első témád');
 
 /* Postok */
 $sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."posts(tId, uId, pTitle, pText, pDate) VALUES
	('1', '1', 'Próbahozzászólás', 'Üdvözlünk az új fórumodban!\nEz egy próbahozzászólás, mely [b]bemutatja[/b] a BB-kódokat, és a többi ügyes dolgot, amit a fejlesztők létrehoztak\n:) ;) :wned: :spam: :banhammer: :wonder:\n\nJó szórakozást!', '" .time(). "')"); // Próbahozzászólás
 WOut('sor', 'posts', 'Első fórumod/Első témád/Próbahozzászólás');
 
 /* Hírek */
 $sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."news(title, text, postDate, uId) VALUES
	('Első híred!', 'Üdvözlünk! Ez egy próba hír, mely bemutatja, hogy itt is [b]használhatóak[/b] [i]a[/i] [u]BB-kódok[/u] és a :jawohl: :banhammer: hangulatjelek :D', " .time(). ", 1)"); // Első híred!
 WOut('sor', 'news', 'Első híred!');
 
 /* Első hírhozzászólás */
 $sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."news_comments(nId, uId, text, postDate) VALUES
	(1, 1, 'Első hírhozzászólás', " .time(). ")"); // Első hírhozzászólás
 WOut('sor', 'news_comments', 'Első hírhozzászólás');
 
 /* Statikus tartalom */
 $sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."plain(title, content) VALUES
	('Statikus tartalom', 'Üdvözöllek!\nEz egy statikus tartalom.\n\nA tartalmat az [url]admin.php?site=plain[/url]admin menü[/a]ből tudod módosítani.')"); // Statikus tartalom példa
	
 /* Szavazások */
 $sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."polls (title, type, opcount) VALUES
	('Mennyire tetszik a fórummotor?', '1', 5)"); // Alapszavazás
 
 /* Szavazás lehetőségek */
 $sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."poll_opinions(pollid, opinion, opinionid) VALUES
	(1, 'Nagyon jó (5)', 1),
	(1, 'Jó (4)', 2),
	(1, 'Középszintű (3)', 3),
	(1, 'Rossz (2)', 4),
	(1, 'Semmitmondó! (1)', 5)"); // Alapszavazás lehetőségei
 }
 
 // Van pár adat amit mindenképpen hozzá kell adnunk az adatbázishoz!!
 /* Verzióadatok */
 $sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."version (RELEASE_TYPE, VERSION, RELEASE_DATE) VALUES ('" .RELEASE_TYPE. "', '" .VERSION. "', '" .RELEASE_DATE. "')"); // Verzióadatok
 WOut('sor', 'version', 'Verzióadatok: ' .RELEASE_TYPE. " " .VERSION. " (" .RELEASE_DATE. ")");
 
 /* Portál beállítások */
 $sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."siteconfig(variable, value) VALUES
	('allow_registration', '1'),
	('facebook_like', '0'),
	('download_minlvl', '0'),
	('under_construct', '0'),
	('const_msg', ''),
	('const_msg_uid', '0'),
	('db_lastoptimize', '0'),
	('db_lastbackup', '0')");
 WOut('sor', 'siteconfig', 'Regisztráció engedélyezve: 1');
 WOut('sor', 'siteconfig', 'Facebook like gomb: 0');
 WOut('sor', 'siteconfig', 'Letöltések min. szint: 0');
 WOut('sor', 'siteconfig', 'Karbantartási mód: 0');
 WOut('sor', 'siteconfig', 'Karbantartási üzenet: ""');
 WOut('sor', 'siteconfig', 'Karbantartási üzenet felhasználó#: 0');
 WOut('sor', 'siteconfig', 'Adatbázis utolsó optimalizáció: 0');
 WOut('sor', 'siteconfig', 'Adatbázis utolsó biztonsági mentés: 0');
 
 file_put_contents('install.log', "\r\nTáblák létrehozása befejezve: " .Datum("normal", "nagybetu", "dL", "H", "i", "s"). " ( " .time(). " )", FILE_APPEND); // Napló zárása
 $sql->Disconnect();
?>