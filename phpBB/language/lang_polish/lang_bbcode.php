<?php
/***************************************************************************
 *                         lang_bbcode.php [polish]
 *                            -------------------
 *   begin                : Wednesday Oct 3, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   Translation by: Mike Paluchowski, Radek Kmiecicki
 *   See website: www.phpbb.pl
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
 
  
$faq[] = array("--","Wprowadzenie");
$faq[] = array("Czym jest BBCode?", "BBCode jest specjaln± implementacj± HTML'a, a mo¿liwo¶æ jego u¿ywania jest uzale¿niona od ustawieñ dokonanych przez administratora (mo¿esz tak¿e wy³±czaæ go dla ka¿dego postu osobno w formularzu wysy³ania). Sam BBCode jest podobny stylowo do HTML'a, znaczniki s± zawarte w nawiasach kwadratowych [ i ] a nie &lt; i &gt; oraz oferuje wiêksz± kontrolê nad tym co i jak bêdzie wy¶wietlane. Zale¿nie od szablonu, którego u¿ywasz mo¿esz w bardzo ³atwy sposób dodawaæ znaczniki BBCode do postów poprzez odpowiednie przyciski na stronie wysy³ania postu. Mimo to ten przewodnik powinien byæ przydatny.");

$faq[] = array("--","Formatowanie Tekstu");
$faq[] = array("Jak wpisaæ pogrubiony, pochylony lub podkre¶lony tekst", "BBCode zawiera znaczniki pozwalaj±ce na szybk± zmianê podstawowego wygl±du tekstu. Mo¿na to uzyskaæ na poni¿sze sposoby:<ul><li>Aby pogrubiæ jaki¶ tekst wstaw go pomiêdzy <b>[b][/b]</b>, np. <br /><br /><b>[b]</b>Cze¶æ<b>[/b]</b><br /><br />stanie siê <b>Cze¶æ</b></li><li>Do podkre¶leñ u¿yj <b>[u][/u]</b>, na przyk³ad:<br /><br /><b>[u]</b>Dzieñ Dobry<b>[/u]</b><br /><br />stanie siê <u>Dzieñ Dobry</u></li><li>Aby wpisaæ tekst kursyw± u¿yj <b>[i][/i]</b>, np.<br /><br />To jest <b>[i]</b>¦wietne!<b>[/i]</b><br /><br />co zmieni siê na To jest <i>¦wietne!</i></li></ul>");
$faq[] = array("Jak zmieniæ kolor lub rozmiar tekstu", "Aby zmieniæ kolor lub rozmiar tekstu mo¿na u¿yæ nastêpuj±cych znaczników. Pamiêtaj, ¿e to jaki bêdzie rezultat po wy¶wietleniu zale¿y od przegl±darki i systemu u¿ytkownika:<ul><li>Zmianê koloru tekstu mo¿na osi±gn±æ przez otoczenie go <b>[color=][/color]</b>. Mo¿esz podaæ albo nazwê koloru (np. red, blue, yellow, itp.) lub szesnastkow± warto¶æ, np. #FFFFFF, #000000. Na przyk³ad aby stworzyæ czerwony tekst mo¿esz u¿yæ<br /><br /><b>[color=red]</b>Cze¶æ!<b>[/color]</b><br /><br />albo<br /><br /><b>[color=#FF0000]</b>Cze¶æ!<b>[/color]</b><br /><br />oba wy¶wietl± te same <span style=\"color:red\">Cze¶æ!</span></li><li>Zmiana rozmiaru tekstu jest osi±gana w podobny sposób uzywaj±c <b>[size=][/size]</b>. Ten znacznik jest zale¿ny od szablonu, którego u¿ywasz ale rekomendowanym formatem jest numeryczna warto¶æ reprezentuj±ca rozmiar tekstu w pikselach, zaczynaj±c od 1 (tak ma³y, ¿e go nie widaæ) a¿ do 26 (bardzo du¿y). Na przyk³ad:<br /><br /><b>[size=9]</b>MA£Y<b>[/size]</b><br /><br /> bêdzie generalnie <span style=\"font-size:9px\">MA£Y</span><br /><br />podczas gdy:<br /><br /><b>[size=24]</b>WIELKI!<b>[/size]</b><br /><br />bêdzie<span style=\"font-size:24px\">WIELKI!</span></li></ul>");
$faq[] = array("Czy mogê ³±czyæ znaczniki formatuj±ce?", "Tak, naturalnie ¿e mo¿esz, na przyk³ad aby zwróciæ czyj±æ uwagê mo¿esz napisaæ:<br /><br /><b>[size=18][color=red][b]</b>POPATRZ NA MNIE!<b>[/b][/color][/size]</b><br /><br />co zmieni siê w <span style=\"color:red;font-size:18px\"><b>POPATRZ NA MNIE!</b></span><br /><br />Nie radzimy jednak wpisywaæ du¿ych ilosci tekstu o takim wygl±dzie! Pamiêtaj, ¿e od ciebie zale¿y zachowanie poprawnej kolejno¶ci pocz±tkowych i koñcowych znaczników. Na przyk³ad poni¿sze nie jest prawid³owe:<br /><br /><b>[b][u]</b>Tak jest ¼le<b>[/b][/u]</b>");

$faq[] = array("--","Cytowanie i wpisywanie tekstu o sta³ej szeroko¶ci");
$faq[] = array("Cytowanie tekstu w odpowiedziach", "S± dwa sosoby na cytowanie tekstu, z podaniem ¼ród³a lub bez.<ul><li>Kiedy wykorzystujesz funkcjê cytowania odpowiadaj±c na post na forum powiniene¶ zauwa¿yæ, ¿e tekst jest dodawany do wiadomo¶ci otoczony blokiem <b>[quote=\"\"][/quote]</b>. Ta metoda pozwala cytowaæ z podaniem ¼ród³a czyli osoby lub czegokolwiek innego, co zechcesz podaæ. Na przyk³ad aby zacytowaæ kawa³ek tekstu napisanego przez Mr. Blobby mo¿esz wpisaæ:<br /><br /><b>[quote=\"Mr. Blobby\"]</b>Tekst Mr. Blobby zostanie wstawiony tutaj<b>[/quote]</b><br /><br />Wynikiem czego bêdzie automatyczne dodanie Mr. Blobby napisa³: przed w³a¶ciwym tekstem. Pamiêtaj, <b>musisz</b> wstawiæ znaki \"\" wokó³ nazwy ¼ród³a, nie s± one jedynie opcj±.</li><li>Druga metoda pozwala cytowaæ co¶ nie podaj±c ¼ród³a. Aby jej u¿yæ wstaw tekst miêdzy znaczniki <b>[quote][/quote]</b>. Kiedy bêdziesz przegl±da³ wiadomo¶ci, zobaczysz po prostu s³owo Cytat: przed samym tekstem.</li></ul>");
$faq[] = array("Wstawianie kodu lub danych o sta³ej szeroko¶ci", "Je¶li chcesz wstawiæ kawa³ek kodu lub cokolwiek wymagaj±cego sta³ej szeroko¶ci znaków, jak w czcionce Courier powiniene¶ zamkn±æ tekst wewn±trz znaczników <b>[code][/code]</b>, np:<br /><br /><b>[code]</b>echo \"Trochê kodu\";<b>[/code]</b><br /><br />Ca³e formatowanie u¿yte wewn±trz znaczników <b>[code][/code]</b> jest zachowywane przy przegl±daniu.");

$faq[] = array("--","Tworzenie list");
$faq[] = array("Tworzenie listy Nieuporz±dkowanej", "BBCode umo¿liwia wstawianie dwóch rodzajów list, nieuporz±dkowan± i uporz±dkowan±. S± w zasadzie takie same jak ich ekwiwalenty w HTML. Lista nieuporz±dkowana prezentuje kolejne pozycje jedna po drugiej, oznaczaj±c je graficznymi znakami. Aby utworzyæ listê nieuporz±dkowan± u¿yj znacznika <b>[list][/list]</b> i oznacz ka¿d± pozycjê u¿ywaj±c <b>[*]</b>. Na przyk³ad aby zrobiæ listê twoich ulubionych kolorów mo¿esz u¿yæ:<br /><br /><b>[list]</b><br /><b>[*]</b>Czerwony<br /><b>[*]</b>Niebieski<br /><b>[*]</b>¯ó³ty<br /><b>[/list]</b><br /><br />Zmieni siê to w listê:<ul><li>Czerwony</li><li>Niebieski</li><li>¯ó³ty</li></ul>");
$faq[] = array("Tworzenie listy Uporz±dkowanej", "Drugi typ list, uporz±dkowany daje kontrolê nad tym, co jest wy¶wietlane przed ka¿dym elementem. Aby utworzyæ listê uporz±dkowan± u¿yj <b>[list=1][/list]</b> dla listy numerowanej lub alterntywnie <b>[list=a][/list]</b> dla listy alfabetycznej. Podobnie jak w li¶cie nieuporz±dkowanej elementy s± wyznaczane przez <b>[*]</b>. Na przyk³ad<br /><br /><b>[list=1]</b><br /><b>[*]</b>Id¼ do sklepu<br /><b>[*]</b>Kup nowy komputer<br /><b>[*]</b>Przeklnij komputer kiedy siê zawiesi<br /><b>[/list]</b><br /><br />co zamieni siê w nastêpuj±ce:<ol type=\"1\"><li>Id¼ do sklepu</li><li>Kup nowy komputer</li><li>Przeklnij komputer kiedy siê zawiesi</li></ol>Podczas gdy dla alfabetycznej listy u¿y³by¶:<br /><br /><b>[list=a]</b><br /><b>[*]</b>Pierwsza mo¿liwa odpowied¼<br /><b>[*]</b>Druga mo¿liwa odpowied¼<br /><b>[*]</b>Trzecia mo¿liwa odpowied¼<br /><b>[/list]</b><br /><br />co da<ol type=\"a\"><li>Pierwsza mo¿liwa odpowied¼</li><li>Druga mo¿liwa odpowied¼</li><li>Trzecia mo¿liwa odpowied¼</li></ol>");

$faq[] = array("--", "Tworzenie linków");
$faq[] = array("Odno¶niki do innych stron", "BBCode phpBB umo¿liwia na ró¿ne sposoby tworzenie URI, Uniform Resource Indicators znanych jako URL'e.<ul><li>Pierwsza wykorzystuje znacznik <b>[url=][/url]</b>, cokolwiek wpiszesz po znaku = zostanie zmienione na cel odno¶nika. Na przyk³ad aby wstawiæ link do phpBB.com mo¿esz u¿yæ:<br /><br /><b>[url=http://www.phpbb.com/]</b>Odwied¼ phpBB!<b>[/url]</b><br /><br />Co zmieni siê w odno¶nik <a href=\"http://www.phpbb.com/\" target=\"_blank\">Odwied¼ phpBB!</a>. Zauwa¿, ¿e odno¶nik otwiera siê w nowym oknie, tak wiêc u¿ytkownik mo¿e kontynuowaæ forum je¶li chce.</li><li>Je¿eli chcesz aby sam URL by³ wy¶wietlany jako link mo¿esz to zrobiæ u¿ywaj±c zwyczajnie:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />Co utworzy link <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>Dodatkowo phpBB umo¿lwia wykorzystanie tzw. <i>Magicznych Linków</i>, które zmieniaj± prawid³owo wpisany URL w odno¶nik bez potrzeby dodawania jakichkolwiek znacznikó lub nawet dopisywania na pocz±tku http://. Na przyk³ad wpisanie www.phpbb.com w wiadomo¶ci zmieni siê automatycznie w <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> przy wy¶wietlaniu wiadomo¶ci.</li><li>Podobnie jest z adresami email, mo¿esz albo podaæ adres wyra¼nie, np:<br /><br /><b>[email]</b>nikt@domena.adr<b>[/email]</b><br /><br />co zamieni siê na <a href=\"emailto:nikt@domena.adr\">nbikt@domena.adr</a> albo wpisaæ jedynie nikt@domena.adr w wiadomo¶ci i zostanie to automatycznie zamienione podczas wy¶wietlania wiadomo¶ci.</li></ul>Podobnie jak ze wszystkimi znacznikami BBCode mo¿esz otaczaæ adresy URL jakimikolwiek innymi znacznikami, jak <b>[img][/img]</b> (zobacz kolejny punkt), <b>[b][/b]</b>, itp. Je¶li chodzi o znaczniki formatuj±ce, do ciebie nale¿y dba³o¶æ o poprawn± kolejno¶æ otwietania i zamykania, na przyk³ad:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br />jest <u>nieprawid³owe</u> przez co twój post mo¿e zostaæ usuniêty.");

$faq[] = array("--", "Wstawianie obrazków do postów");
$faq[] = array("Dodawanie obrazka do postu", "BBCode phpBB zawiera znacznik umo¿liwiaj±cy wstawianie obrazków do postów. Nale¿y jednak pamiêtaæ o dwóch istotnych rzeczach: wielu u¿ytkowników nie lubi du¿ych ilo¶ci obrazków w postach oraz wstawiany obrazek musi byæ ju¿ dostêpny w internecie (nie mo¿e na przyk³ad istnieæ tylko na twoim komputerze, chyba ¿e masz u siebie serwer!). Nie ma obecnie mo¿liwo¶ci przechowywania obrazków lokalnie wraz z phpBB (problemy te zostan± prawdopodobnie rozwi±zane w nastêpnej wersji phpBB). Aby wstawiæ obrazek musisz otoczyæ jego adres URL znacznikami <b>[img][/img]</b>. Na przyk³ad:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />Jak zaznaczono w sekcji URL powy¿ej mo¿esz otoczyæ obrazek znacznikami <b>[url][/url]</b> je¶li chcesz, np.<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />zmieni siê w:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"http://www.phpbb.com/images/phplogo.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Inne sprawy");
$faq[] = array("Czy mogê dodaæ w³asne znaczniki?", "Nie, obawiam siê ¿e nie bezpo¶rednio w phpBB 2.0. Planujemy wprowadzenie modyfikowalnej listy znaczników BBCode w nastêpnej wersji forum.");

//
// This ends the BBCode guide entries
//

?>