<?php
/***************************************************************************
 *                         lang_bbcode.php [Slovak]
 *                         -----------------------
 *     characterset         : Windows-1250
 *     begin                : 08-08-2002
 *     copyright            : (c) 2002 The phpBB SK Group
 *     email                : kolenkas@stonline.sk
 *     convert2iso          : Kukymann
 *     www                  : 
 *
 *     $Id$
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
// To add an entry to your BBCode guide simply add a line to this file in this format:
// $faq[] = array("question", "answer");
// If you want to separate a section enter $faq[] = array("--","Block heading goes here if wanted");
// Links will be created automatically
//
// DO NOT forget the ; at the end of the line.
// Do NOT put double quotes (") in your BBCode guide entries, if you absolutely must then escape them ie. \"something\"
//
// The BBCode guide items will appear on the BBCode guide page in the same order they are listed in this file
//
// If just translating this file please do not alter the actual HTML unless absolutely necessary, thanks :)
//
// In addition please do not translate the colours referenced in relation to BBCode any section, if you do
// users browsing in your language may be confused to find they're BBCode doesn't work :D You can change
// references which are 'in-line' within the text though.
//
  
$faq[] = array("--","Úvod");
$faq[] = array("Èo sú znaèky ?", "Znaèky sú zvláštne príkazy vloené do HTML. Pouívanie znaèiek vo vašich príspevkoch vo fóre povo¾uje administrátor. Môete si dodatoènì zakáza pouívanie znaèiek v jednotlivıch príspevkoch vo formulári k zaslaniu príspìvku. Znaèky sú ve¾mi podobné štılu HTML, príkazy sú zapísané v zloenıch zátvorkách [] a uzavierajú vdy nejakı text, ktorı sa následne chová pod¾a tıchto príkazov. Znaèky vám umonia rıchle formátovanie písaného textu. Sami sa teda môete rozhodnú, èi budete chcie pouíva tieto znaèky, ktoré sú zahrnuté vo formulári pre odoslanie príspevku, èi budete pouíva HTML.");

$faq[] = array("--","Formátovanie textu");
$faq[] = array("Ako vytvori text písanı tuène, kurzívou èi podèiarknutı", "Znaèky obsahujú príkazy pre rıchlu zmenu štılu vášho textu. Môete sa pozrie ako ¾ahko dosiahnu poadovanı vısledok.<ul><li>Pre vytvorenie tuène písaného textu, obklopíte text medzi <b>[b][/b]</b><br /><br /><p>príklad: <b>[b]</b>Ahoj<b>[/b]</b><br />Vısledkum je <b>Ahoj</b></li></p><p><li>Pre podèiarknutie textu, obklopíte text medzi <b>[u][/u]</b><br /><br />príklad: <b>[u]</b>Dobrı deò<b>[/u]</b><br />Vısledkom je <u>Dobrı deò</u></li></p><p><li>Pre text písanı kurzívou, obklopíte text medzi <b>[i][/i]</b><br /><br />príklad: Toto je <b>[i]</b>ukáka<b>[/i]</b><br />Vısledkom je Toto je <i>ukáka</i></li></p></ul>");
$faq[] = array("Ako zmeni farbu a ve¾kos písma", "Pre zmenu farby alebo ve¾kosti textu je urèenıch nieko¾ko príkazov. Dajte si pozor na to ako bude vıstup zobrazenı v závislosti na vašom prehliadaèi a systéme:<ul><li>pre zmenu farby textu, obklopíte poadovanı text medzi <b>[color=][/color]</b>. Môete poui buï názvy farieb (napr. red, blue, yellow, atï.) alebo odpovedajúce hexadecimálne kódy farby, napr. #FFFFFF, #000000. Na príklade si ukáeme ako vytvori èervenı text:<br /><br /><b>[color=red]</b>Ahoj!<b>[/color]</b><br /><br />alebo<br /><br /><b>[color=#FF0000]</b>Ahoj!<b>[/color]</b><br /><br />Vısledkom bude <span style=\"color:red\">Ahoj!</span></li><li>Zmenu ve¾kosti textu vykonáme podobne pouitím <b>[size=][/size]</b>. Tento príkaz má pøeddefinované èíselné hodnoty, které mají pøiøazenu odpovídající velikosti písma v bodoch, zaèínajúc od 1 (ve¾mi malé písmo, nejmenšie vidite¾né) a po 29 (ve¾mi ve¾ké). Pre ukáku:<br /><br /><b>[size=9]</b>MALÉ<b>[/size]</b><br /><br />Vısledkom je <span style=\"font-size:9px\">MALÉ</span><br /><br />zatia¾ èo:<br /><br /><b>[size=24]</b>VE¼KÉ<b>[/size]</b><br /><br />zobrazí <span style=\"font-size:24px\">VE¼KÉ</span></li></ul>");
$faq[] = array("Je moné spája formátovacie znaèky ?", "Áno, toto je moné, na nasledujúcom príklade si ukáeme ako správne tieto znaèky zapísa. Je ve¾mi dôleité dodra aj ich postupnos.<br /><br /><b>[size=18][color=red][b]</b>Pozri sa<b>[/b][/color][/size]</b><br /><br />Vısledkom je <span style=\"color:red;font-size:18px\"><b>Pozri sa</b></span><br /><br />Pokia¾ nedodríte postupnos ukonèení znaèiek v poradí v akom boli vkladané, bude text zobrazenı chybne! Vdy je potrebné uzaviera znaèky v postupnosti v akej boli zadané. Pozrite sa na nasledujúcu ukáku, kde sú znaèky nekorektne uzavreté:<br /><br /><p><b>[b][u]</b>Toto je chyba!<b>[/b][/u]</b></p>");

$faq[] = array("--","Citácia a pevná šírka textu pri odoslaní");
$faq[] = array("Citácia textu v odpovedi", "Sú dva spôsoby zadania citovaného textu, s poukázaním a bez neho.<ul><li>Keï je to vhodné môete poui citát k príspevku, ktorı pridá poukázanie a text do zvláštneho boxu v príspevku. Text citácie uzavrite medzi <b>[quote=\"\"][/quote]</b>. Tento zpôsob pridá k citácii vaše poukázanie koho citujete alebo komu je urèená. V nasledujúcom príklade si ukáeme ako zadáme text, ktorı vyslovil Karol Novák:<br /><br /><b>[quote=\"Karol Novák\"]</b>Toto je text, ktorı vyslovil tento pán.<b>[/quote]</b><br /><br /> Vısledkom bude automatické pridanie poukázania Karol Novák napísal: a text citácie. Pokia¾ chcete zada text ako svoj vlastnı citát, prípadne nikoho neurèova, zadáte len zátvorky \"\". Táto vo¾ba nie je povinná.</li><p><li>Druhım spôsobom je citova text bez poukázania. Poadovanı text, ktorı chcete citova uzavrite medzi <b>[quote][/quote]</b>. Keï si zobrazíte vısledok takejto správy, bude tu nejprv namiesto poukázánia len napísal: a text citátu.</li></p></ul>");
 
$faq[] = array("Vıstup kódu alebo pevná šírka dát", "Ak chcete vloi kus kódu alebo èoko¾vek èo vyaduje pevnú šírku (font typu Courier), obklopte text medzi <b>[code][/code]</b><br /><br /><p>napríklad: <b>[code]</b>echo \"Toto je kód\";<b>[/code]</b></p>");

$faq[] = array("--","Generovanie zoznamu");
$faq[] = array("Vytváranie jednoduchého zoznamu", "Znaèky obsahujú aj príkazy pre vytváranie zoznamov. Podporované sú dva druhy zoznamov, jednoduchı a štrukturovanı. Jednoduchı zoznam zobrazí jednotlivé poloky zoznamu postupne pod sebou oddelené odrákou. Pre vytvorenie zoznamu pouite <b>[list][/list]</b> a definujte jednotlivé poloky pomocou <b>[*]</b>. Pozrite sa na nasledujúcu ukáku jednoduchého zoznamu:<br /><br /><b>[list]</b><br /><b>[*]</b>èervená<br /><b>[*]</b>modrá<br /><b>[*]</b>zelená<br /><b>[/list]</b><br /><br />Vısledkom by bolo:<ul><li>èervená</li><li>modrá</li><li>zelená</li></ul>");
$faq[] = array("Vytváranie štrukturovaného zoznamu", "Druhım spôsobom je vytváranie štrukturovanıch zoznamov. Od predchádzajúceho typu sa líši znakom pred textom jednotlivıch poloiek, namiesto bodky je tu pouitı niektorı z dvoch spôsobov vzostupného oznaèenia poloiek zoznamu. Pre vytvorenie èíslovaného zoznamu pouite <b>[list=1][/list]</b> a pre abecednı zoznam <b>[list=a][/list]</b>. Jednotlivé poloky zoznamu definujete pomocou <b>[*]</b>. Pozrite sa na nasledujúcu ukáku:<br /><br /><b>[list=1]</b><br /><b>[*]</b>èervená<br /><b>[*]</b>modrá<br /><b>[*]</b>zelená<br /><b>[/list]</b><br /><br />Vısledkom bude:<ol type=\"1\"><li>èervená</li><li>modrá</li><li>zelená</li></ol>Pre vytvorenie abecedného zoznamu pouite:<br /><br /><b>[list=a]</b><br /><b>[*]</b>prvá moná odpoveï<br /><b>[*]</b>druhá moná odpoveï<br /><b>[*]</b>tretia moná odpoveï<br /><b>[/list]</b><br /><br />Vısledok:<ol type=\"a\"><li>prvá moná odpoveï</li><li>druhá moná odpoveï</li><li>tretia moná odpoveï</li></ol>");

$faq[] = array("--", "Vytvorenie odkazu");
$faq[] = array("Odkaz na iné webové stránky", "phpBB znaèky podporujú vytvorenie URL odkazov odkazujúcich sa na iné internetové stránky èi emailové adresy.<ul><li>Prvım spôsobom je poui <b>[url=][/url]</b> znaèky, za znak = p doplníte URL adresu, na ktorú chcete odkazova. Text medzi obomi znaèkami bude zvıraznenı a zároveò bude slúi ako odkaz na uvedenú URL adresu. Pozrite sa na nasledujúcí príklad odkazujúci na server phpbb.com:<br /><br /><b>[url=http://www.phpbb.com/]</b>Stránky phpBB<b>[/url]</b><br /><br />Tımto sa vygeneruje odkaz, <a href=\"http://www.phpbb.com/\" target=\"_blank\">Stránky phpBB</a> Pokia¾ kliknete na tento vytvorenı odkaz, otvorí sa vám v novom okne prehliadaèa odkaz na ktorı smerujete.</li><li>Ak chcete zobrazi URL priamo ako odkaz pouite nasledujúci postup:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />Tımto sa vygeneruje odkaz, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>V prípade zadania syntakticky správneho URL aj bez zaèiatoèného http:// do textu príspevku automaticky odkaz na zadanú URL adresu. Pre ukáku si môete skúsi napísa do príspevku www.phpbb.com a uvidíte, e sa vám text vo vısledku zobrazí automaticky jako odkaz <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a>.</li><li>Obdobnım zpôsobom sa dajú vytvára aj odkazy na emailové adresy, zadajte poadovanú emailovú adresu pod¾a príkladu:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />Vısledok potom bude <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> alebo môete zada v texte prípspevku no.one@domain.adr a adresa sa opä automaticky premení na odkaz.</li></ul>URL odkaz môete zada medzi ktoréko¾vek ïalšie znaèky: Ak uzavriete URL medzi <b>[img][/img]</b> (viï nasledujúca kapitola) môe by odkazom aj obrázok. Len potom nezabudnite na správnu postupnos uzatvárania znaèiek.");

$faq[] = array("--", "Zobrazenie obrázkov v príspevkoch");
$faq[] = array("Pridanie obrázku do príspevku", "phpBB znaèky ïalej umoòujú vkladanie obrázkov do textu príspevku èi správy. Toto je ve¾mi uitoèná vlastnos, vïaka ktorej nemusíte odkazova na súbory obrázkov o ktorıch napríklad píšete, ale všetci uívatelia ich hneï vidia vo vašom príspevku. Ako bolo uvedené vyššie, môete vyui obrázok k vytvoreniu URL odkazu na váš server alebo napríklad pre zväèšeninu malého obrázku tu v príspevku. Obrázok sa musí však vdy nachádza na internete a by tak dostupnı pre všetkıch uívate¾ov, nie je moné sa teda odkazova na súbory, ktoré máte napríklad na lokálnom disku vášho poèítaèa, pretoe k nim by uívatelia internetu nemali prístup. Pre zobrazenie obrázku musíte uzavøie URL obrázku medzi <b>[img][/img]</b>.<br /><br />príklad: <b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />Ak zadáte URL adresu obrázku medzi <b>[url][/url]</b>, môe by odkazom obrázok.<br /><br />príklad: <b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />Vısledkom bude:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"http://www.phpbb.com/images/phplogo.gif\" border=\"0\" alt=\"\" /></a><br />");

//
// This ends the BBCode guide entries
//

?>