<?php
/***************************************************************************
 *                         lang_bbcode.php [Hungarian]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: lang_bbcode.php,v 1.3 2001/12/18 01:53:26 psotfx Exp $
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/
 
//
// Translation by Gergely EGERVARY 
// mauzi@expertlan.hu
//


$faq[] = array("--","Bevezetés");
$faq[] = array("Mi az a BBCode?", "A BBCode egy speciális HTML változat. Az adminisztrátor határozza meg, használhat-e a hozzászólásaiban BBCode tag-eket. (a BBCode hozzászólásonként bárki által letiltható) Szintaktikailag hasonló a HTML kódhoz, a tag-ek szögletes zárójelben vannak: [ és ] a &lt; és &gt; helyett, és nagyobb felügyeletet biztosít a megjelenítés felett.");

$faq[] = array("--","Szöveg formázása");
$faq[] = array("Hogyan írhatok félkövér, dõlt, aláhúzott szöveget?", "A BBCode lehetõséget biztosít arra, hogy gyorsan és egyszerûen megváltoztassa a szöveg stílusát. Az alábbi lehetõségek vannak: <ul><li>Félkövér megjelenítéséhez írja a szöveget <b>[b][/b]</b> tag-ek közé, pl. <br /><br /><b>[b]</b>Helló<b>[/b]</b><br /><br />eredménye <b>Helló</b></li><li>Aláhúzáshoz írja a szöveget a<b>[u][/u]</b> tag-ek közé, pl. <br /><br /><b>[u]</b>Jó reggelt!<b>[/u]</b><br /><br />eredménye <u>Jó reggelt!</u></li><li>Dõlt megjelenítéshez írja a szöveget <b>[i][/i]</b> tag-ek közé, pl.<br /><br />Ez <b>[i]</b>nagyszerû!<b>[/i]</b><br /><br />eredménye: Ez <i>nagyszerû!</i></li></ul>");
$faq[] = array("Hogyan változtathatom meg a betû méretét és színét?", "Az alábbiakban bemutatjuk a betûszín és méret megváltoztatásához szükséges tag-eket. Tartsa szem elõtt, hogy a formázás eredménye függ a böngészõ szoftvertõl és operációs rendszertõl. <ul><li>Betûszín megváltoztatása: <b>[color=][/color]</b>. Megadhatja a színt szöveges formában, (pl. red, blue, yellow, stb.) vagy hexadecimális kóddal, pl. #FFFFFF, #000000. Például, a piros betûszín kiválasztásához az alábbit használja:<br /><br /><b>[color=red]</b>Helló!<b>[/color]</b><br /><br />vagy<br /><br /><b>[color=#FF0000]</b>Helló!<b>[/color]</b><br /><br />mindkettõ eredménye <span style=\"color:red\">Helló!</span></li><li>A szöveg mérete hasonlóképpen módosítható a <b>[size=][/size]</b> segítségével. Az ajánlott szövegméret pixelben 1-tõl (egészen apró, olvashatatlan) 29-ig (nagyon nagy). Például:<br /><br /><b>[size=9]</b>KICSI<b>[/size]</b><br /><br />eredménye <span style=\"font-size:9px\">KICSI</span><br /><br />amíg <br /><br /><b>[size=24]</b>ÓRIÁSI!<b>[/size]</b><br /><br />eredménye <span style=\"font-size:24px\">ÓRIÁSI!</span></li></ul>");
$faq[] = array("Használhatok többféle formázást egyszerre?", "Természetesen! Például figyelem felhívásra használhatja az alábbit: <br /><br /><b>[size=18][color=red][b]</b>Ide figyelj!<b>[/b][/color][/size]</b><br /><br />az eredménye <span style=\"color:red;font-size:18px\"><b>Ide figyelj!</b></span><br /><br />Mindemellett nem javasoljuk, hogy hosszú szövegeket formázzon meg ehhez hasonlóan! Ügyeljen a tag-ek helyes lezárására. Például a következõ egy hibás formázás: <br /><br /><b>[b][u]</b>Ez így helytelen<b>[/b][/u]</b>");

$faq[] = array("--","Idézetek és kódok megjelenítése");
$faq[] = array("Idézetek hozzászólásokban", "Kétféleképpen idézhet szöveget: hivatkozással vagy anélkül. <ul><li>Ha hozzászólásnál az Idézet gombra kattint, észre fogja venni, hogy az idézet szövege automatikusan megjelenik a <b>[quote=\"\"][/quote]</b> tag-ek között. Ezzel a módszerrel idézhet egy elõzõ hozzászólást. Például Bozzi Úr hozzászólását a következõképpen idézheti:<br /><br /><b>[quote=\"Bozzi Úr\"]</b>Ezt írta Bozzi Úr<b>[/quote]</b><br /><br />Az eredmény elõtt automatikusan megjelenik a Bozzi Úr írta: sor. Ügyeljen arra, hogy az idézett személy nevét idézõjelbe <b>kell</b> zárnia.</li><li>A második módszerrel tetszõleges szöveget idézhet. Használja a <b>[quote][/quote]</b> tag-eket. A hozzászólás megjelenítésénél megjelenik az Idézet: sor a szöveg elõtt.</li></ul>");
$faq[] = array("Kódok megjelenítése", "Ha programkódot, vagy hasonló, fix betûszélességet (pl. Courier betûtípust) igénylõ szöveget kíván megjeleníteni, használja a <b>[code][/code]</b> tag-eket, pl.<br /><br /><b>[code]</b>echo \"Ez itt egy programsor\";<b>[/code]</b><br /><br />Az összes formázó tag érvényét veszti, ha a <b>[code][/code]</b> tag-eken belül használja.");

$faq[] = array("--","Listák készítése");
$faq[] = array("Számozatlan lista készítése", "A BBCode kétféle lista készítését teszi lehetõvé: számozott és számozatlant. Egy számozatlan lista nem más, mint egy felsorolás, minden sor elõtt egy bekezdésjellel. Létrehozásához használja a <b>[list][/list]</b> tag-eket, és a lista elemeinek megadásához a <b>[*]</b> tag-et. Például a kedvenc színei felsorolásához az alábbit használhatja:<br /><br /><b>[list]</b><br /><b>[*]</b>Piros<br /><b>[*]</b>Kék<br /><b>[*]</b>Sárga<br /><b>[/list]</b><br /><br />Eredménye a következõ lista:<ul><li>Piros</li><li>Kék</li><li>Sárga</li></ul>");
$faq[] = array("Számozott lista készítése", "A második típus, a számozott lista lehetõséget biztosít tetszõleges szám vagy jel megadására a lista elemei elõtt. Létrehozásához használja a <b>[list=1][/list]</b> tag-eket, vagy a <b>[list=a][/list]</b> tag-eket alfabetikus listához. Akár csak a számozatlan listánál, a lista elemeit a <b>[*]</b> tag segítségével adhatja meg. Például:<br /><br /><b>[list=1]</b><br /><b>[*]</b>Elmenni a boltba<br /><b>[*]</b>Venni egy új számítógépet<br /><b>[*]</b>Belerúgni, ha nem mûködik<br /><b>[/list]</b><br /><br />eredménye a következõ:<ol type=\"1\"><li>Elmenni a boltba</li><li>Venni egy új számítógépet</li><li>Belerúgni, ha nem mûködik</li></ol>Alfabetikus listához:<br /><br /><b>[list=a]</b><br /><b>[*]</b>Az elsõ választási lehetõség<br /><b>[*]</b>A második választási lehetõség<br /><b>[*]</b>A harmadik választási lehetõség<br /><b>[/list]</b><br /><br />eredménye<ol type=\"a\"><li>Az elsõ választási lehetõség</li><li>A második választási lehetõség</li><li>A harmadik választási lehetõség</li></ol>");

$faq[] = array("--", "Hivatkozások készítése");
$faq[] = array("Hivatkozás másik oldalra", "A BBCode többféle lehetõséget biztosít URI (Uniform Resource Indicator) ismertebb nevén URL hivatkozások létrehozására.<ul><li>Az egyik lehetõség az <b>[url=][/url]</b> tag használata, amit az = jel után ír, az lesz a hivatkozás tárgya. Például a phpBB.com weboldalra így hivatkozhat:<br /><br /><b>[url=http://www.phpbb.com/]</b>Itt lakik a phpBB!<b>[/url]</b><br /><br />Eredménye a következõ hivatkozás: <a href=\"http://www.phpbb.com/\" target=\"_blank\">Itt lakik a phpBB!</a> A hivatkozások új böngészõablakban nyílnak meg, hogy az olvasó zavartalanul folytathassa a fórum böngészését. </li><li>Ha magát az URL címet szeretné megjeleníteni a hivatkozásban, egyszerûen megteheti:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />Eredménye a következõ hivatkozás: <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>Mindemellett a phpBB tartogat egy remek lehetõséget: bármilyen, szintaktikailag helyes URL automatikusan hivatkozássá alakul, anélkül, hogy bármilyen tag-et használna. Például a hozzászólásba írt www.phpbb.com szöveg automatikusan átalakul hivatkozássá, amikor megtekinti a hozzászólást: <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a></li><li>Ugyanez érvényes az email címekre is, megadhat egy email címet, például:<br /><br /><b>[email]</b>valaki@valahol.hu<b>[/email]</b><br /><br />eredménye <a href=\"mailto:valaki@valahol.hu\">valaki@valahol.hu</a> vagy csak egyszerûen írja be a hozzászólásba a valaki@valahol.hu címet, és automatikusan hivatkozássá alakul olvasásnál.</li></ul>Tetszõlegesen egymásba ágyazhat többféle BBCode tag-et, például <b>[img][/img]</b> (lásd: következõ példa), <b>[b][/b]</b>, stb. Akár csak a formázásnál, ügyeljen a tag-ek helyes lezárására, például:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />egy <u>helytelen</u> hivatkozás, amiért a moderátorok akár el is távolíthatják a hozzászólását.");

$faq[] = array("--", "Képek megjelenítése");
$faq[] = array("Kép megjelenítése a hozzászólásokban", "A BBCode lehetõséget biztosít képek beszúrására. Két dolgot tartson szem elõtt, ha használja ezt a lehetõséget: a legtöbb felhasználót zavarja a sok kép, másrészrõl a megjelenítendõ képnek már elérhetõnek kell lennie az Interneten (nem elegendõ az, ha a saját számítógépén elérhetõ, kivétel ha webszervert futtat a saját gépén!). Jelenleg nincs lehetõség arra, hogy közvetlenül képeket töltsön fel a fórumra. (Ezt a szoftver késõbbi verzióiban tervezzük megvalósítani.) Kép beszúrásához adja meg a kép URL címét az <b>[img][/img]</b> tag-ek között. Például:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />Amint azt a hivatkozások témakörben említettük, lehetõség van a tag-ek egymásba ágyazására, például: <br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />eredménye:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"http://www.phpbb.com/images/phplogo.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Egyéb");
$faq[] = array("Készíthetek saját BBCode tag-eket?", "Nem, sajnos erre nincs lehetõség a phpBB 2.0 verziójában. Késõbbiekben tervezzük egyéni, testreszabható BBCode tag-ek bevezetését.");

//
// This ends the BBCode guide entries
//

?>
