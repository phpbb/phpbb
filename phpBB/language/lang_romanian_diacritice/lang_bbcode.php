<?php
/***************************************************************************
 *                         lang_bbcode.php [românã cu diacritice]
 *                            -------------------
 *   begin                : Wednesday Aug 7, 2002
 *   copyright 1          : (C) Robert Munteanu
 *   copyright 2          : (C) Bogdan Toma
 *   email     1          : rombert@go.ro
 *   email     2          : bog_tom@yahoo.com
 *
 *   $Id$
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
// To add an entry to your codul BB guide simply add a line to this file in this format:
// $faq[] = array("question", "answer");
// If you want to separate a section enter $faq[] = array("--","Block heading goes here if wanted");
// Links will be created automatically
//
// DO NOT forget the ; at the end of the line.
// Do NOT put double quotes (") in your codul BB guide entries, if you absolutely must then escape them ie. \"something\"
//
// The codul BB guide items will appear on the codul BB guide page in the same order they are listed in this file
//
// If just translating this file please do not alter the actual HTML unless absolutely necessary, thanks :)
//
// In addition please do not translate the colours referenced in relation to codul BB any section, if you do
// users browsing in your language may be confused to find they're codul BB doesn't work :D You can change
// references which are 'in-line' within the text though.
//

$faq[] = array("--","Introducere");
$faq[] = array("Ce este codul BB?", "Codul BB este o implementare specialã a HTML-ului. Dacã puteþi folosi codul BB sau nu în mesajele dumneavoastrã este alegerea administratorului. În plus, puteþi dezactiva codul BB de la mesaj la mesaj din formularul de publicare. Codul BB este similar cu HTML-ul, balizele (tag-urile) sunt scrise între paranteze pãtrate [ ºi ] decât între &lt; ºi &gt; ºi oferã un control mai bun asupra ce ºi cum este afiºat. În funcþie de ºablonul pe care îl folosiþi puteþi descoperi cã adãugarea de cod BB la mesajele dumneavoastrã este mai uºoarã printr-un set de butoane. Chiar ºi aºa probabil cã veþi gãsi acest ghid folositor.");


$faq[] = array("--","Formatarea textului");
$faq[] = array("Cum sã creaþi text îngroºat, cursiv ºi subliniat", "Codul BB include balize pentru a vã permite sã schimbaþi rapid stilul textului dumneavoastrã. Acest lucru poate fi obþinut în urmatoarele moduri: <ul><li>Pentru a face o bucatã de text îngroºatã (bold), includeþi-o între <b>[b][/b]</b> , spre exemplu <br /><br /><b>[b]</b>Salut<b>[/b]</b><br /><br /> va deveni <b>Salut</b></li><li>Pentru subliniere folosiþi <b>[u][/u]</b>, spre exemplu <br /><br /><b>[u]</b>Bunã dimineaþa<b>[/u]</b><br /><br />devine <u>Buna dimineata</u></li><li>Pentru a scrie cu font cursiv (italic) folosiþi <b>[i][/i]</b> , spre exemplu <br /><br /><b>[i]</b>Super!<b>[/i]</b><br /><br />va deveni <i>Super!</i></li></ul>");
$faq[] = array("Cum sã schimbaþi culoarea textului sau mãrimea", "Pentru a schimba culoarea sau marimea textului dumneavoastrã puteþi folosi mai multe balize. Þineþi minte cã felul cum apare mesajul depinde de browser-ul ºi sistemul clientului :<ul><li> Schimbarea culorii textului se face prin trecerea între <b>[color=][/color]</b>. Puteþi specifica fie o culoare cunoscutã, în limba englezã, (<i>red</i> pentru roºu), <i>blue</i> pentru albastru, <i>yellow</i> pentru galben) sau un triplet hexazecimal (#FFFFFF, #000000). Spre exemplu, pentru a scrie cu roºu veþi folosi :<br /><br /><b>[color=red]</b>Salut!<b>[/color]</b><br /><br />sau<br /><br /><b>[color=#FF0000]</b>Salut!<b>[/color]</b><br /><br /> Amblele vor avea ca rezultat <span style=\"color:red\">Salut!</span></li><li>Schimbarea mãrimii textului este facutã în acelaºi fel folosind <b>[size=][/size]</b>. Aceastã balizã depinde de ºablonul pe care îl folosiþi dar formatul recomandat este o valoare numericã reprezentând mãrimea textului în pixeli, pornind de la 1 (extrem de mic) ºi ajungând pânã la 29 (foarte mare). Spre exemplu: <br /><br /><b>[size=9]</b>MIC<b>[/size]</b><br /><br /> în general va avea ca rezultat <span style=\"font-size:9px\">MIC</span><br /><br /> în vreme ce <br /><br /><b>[size=24]</b>ENORM!<b>[/size]</b><br /><br />va fi <span style=\"font-size:24px\">ENORM!</span></li></ul>");
$faq[] = array("Pot combina balizele (tag-urile) de formatare?", "Desigur. Spre exemplu, pentru a atrage atenþia cuiva aþi putea sã scrieþi <br /><br /><b>[size=18][color=red][b]</b>PRIVEªTE-MÃ!<b>[/b][/color][/size]</b><br /><br />ºi rezultatul va fi <span style=\"color:red;font-size:18px\"><b>PRIVEªTE-MÃ!</b></span><br /><br /> Totuºi, nu vã recomandãm sã scrieþi prea mult text astfel ! Tineþi minte cã depinde de dumneavoastrã sã vã asiguraþi cã balizele sunt închise corect. Spre exemplu, urmatoarea secvenþã este incorectã: <br /><br /><b>[b][u]</b>Aºa este greºit<b>[/b][/u]</b>");

$faq[] = array("--","Citate ºi text cu lãþime fixã");
$faq[] = array("Citarea textului în rãspunsuri", "Existã douã modalitãþi de a cita textul, cu referinþã ºi fãrã.<ul><li>Când utilizaþi funcþia de rãspuns inclusiv mesajul, ar trebui sã observaþi cã mesajul respectiv este adãgat în fereastra de publicare inclus într-un bloc <b>[quote=\"\"][/quote]</b>. Aceastã metodã vã permite sã îl citaþi cu referinþã la o persoanã sau orice altceva doriþi sã scrieþi ! Spre exemplu, pentru a cita o bucatã de text scrisã de Dl. Ionescu aþi scrie :<br /><br /><b>[quote=\"Dl. Ionescu\"]</b> Textul scris de Dl. Ionescu <b>[/quote]</b><br /><br /> Rezultatul va fi cã Dl. Ionescu a scris: va fi adãugat înainte de textul citat. Þine-þi minte cã <b>trebuie</b> sã includeþi ghilimelele \"\" în jurul numelui pe care îl citaþi. Acestea nu sunt opþionale.</li><li> A doua metodã vã permite sã citaþi fãrã un autor. Pentru a folosi acest lucru introduceþi textul între balizele <b>[quote][/quote]</b>. Când îl citaþi, mesajul va arãta pur ºi simplu Citat: înainte de textul propriu-zis.</li></ul>");
$faq[] = array("Generarea de cod sau de text cu mãrime fixã", "Dacã doriþi sã scrieþi o bucatã de cod sau - de fapt - orice altceva care are nevoie de o lãþime fixã, cum ar fi un font de tip Courier, ar trebui sã introduceþi textul între balize <b>[code][/code]</b> , spre exemplu: <br /><br /><b>[code]</b>echo \"O linie de cod\";<b>[/code]</b><br /><br />Toate formatãrile folosite între balizele <b>[code][/code]</b> sunt reþinute când citiþi mesajul.");


$faq[] = array("--","Generarea listelor");
$faq[] = array("Crearea unei liste neordonate", "Codul BB include douã tipuri de liste, neordonate ºi ordonate. În mare sunt la fel cu echivalentele lor HTML. O listã neordonatã scrie fiecare obiect din listã secvenþial adãugându-le un alineat ºi un caracter <i>bullet</i>. Pentru a crea o listã neordonatã folosiþi <b>[list][/list]</b> ºi definiþi fiecare obiect din lista folosind <b>[*]</b>. Spre exemplu, pentru a vã scrie culorile preferate aþi putea folosi : <br /><br /><b>[list]</b><br /><b>[*]</b>roºu<br /><b>[*]</b>albastru<br /><b>[*]</b>galben<br /><b>[/list]</b><br /><br />Aceasta ar genera urmatoarea listã: <ul><li>roºu</li><li>albastru</li><li>galben</li></ul>");
$faq[] = array("Crearea unei liste ordonate", "Al doilea tip de listã, lista ordonatã vã oferã controlul asupra ceea ce este afiºat înaintea fiecãrui obiect. Pentru a crea o listã ordonatã folosiþi <b>[list=1][/list]</b> pentru o listã numericã sau <b>[list=a][/list]</b> pentru o listã alfabeticã. Ca ºi la listele neordonate, obiectele sunt indicate folosind <b>[*]</b>. Spre exemplu: <br /><br /><b>[list=1]</b><br /><b>[*]</b>Mergi la magazin<br /><b>[*]</b>Cumparã un calculator<br /><b>[*]</b>Tipã la el când crapã<br /><b>[/list]</b><br /><br /> va genera urmatoarele:<ol type=\"1\"><li>Mergi la magazin</li><li>Cumparã un calculator</li><li>Tipã la el când crapã</li></ol> pe când pentru o lista alfabeticã aþi folosi :<br /><br /><b>[list=a]</b><br /><b>[*]</b>Primul rãspuns<br /><b>[*]</b>Al doilea rãspuns<br /><b>[*]</b>Al treilea rãspuns<br /><b>[/list]</b><br /><br /> având ca rezultat: <ol type=\"a\"><li>Primul rãspuns</li><li>Al doilea rãspuns</li><li>Al treilea rãspuns</li></ol>");


$faq[] = array("--", "Crearea legãturilor");
$faq[] = array("Legãturi cãtre alte site-uri", "Codul BB oferã multe resurse de creare a legãturilor, cunoscute mai bine ca URL-uri. <ul><li>Prima din acestea foloseºte baliza <b>[url=][/url]</b>, ºi orice veþi scrie dupã semnul egal va determina conþinutul acelei balize sã se comporte ca un URL. Spre exemplu, o legãturã cãtre phpBB ar fi: <br /><br /><b>[url=http://www.phpbb.com/]</b>Vizitaþi phpBB!<b>[/url]</b><br /><br />Rezultatul ar fi urmãtorea legãturã: <a href=\"http://www.phpbb.com/\" target=\"_blank\">Vizitaþi phpBB!</a>. Veþi observa cã legãtura se va deschide într-o fereastrã nouã pentru ca utilizatorul sã poatã continua sã utilizeze forumul dacã doreºte.</li></li> Dacã doriþi sã fie afiºat chiar URL-ul atunci puteþi sã scrieþi: <br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br /> Acesta va genera urmatoarea legaturã: <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li> Alte facilitãþi phpBB includ ºi ceva numit <i>legãturi magice</i>, care vã transformã un URL corect din punct de vedere sintactic într-un URL fãrã ca dumneavoastrã sã specificaþi vreo baliza sau sã începeþi cu <i>http://</i>. Spre exemplu, dacã veþi scrie www.phpbb.com aceasta va deveni direct <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a>. Acelaºi lucru se întâmplã ºi cu adresele de mail. Puteþi folosi o adresã explicit spre exemplu: <br /><br /><b>[email]</b>cineva@domeniu.adr<b>[/email]</b><br /><br />care va rezulta în <a href=\"mailto:cineva@domeniu.adr\">cineva@domeniu.adr</a> sau puteþi sã scrieþi direct cineva@domeniu.adr ºi mesajul dumneavoastrã va fi automat convertit când îl veþi vizualiza. </li></ul> La fel ca tag-urile codului BB puteþi folosi pentru URL-uri orice tip de tag, ca ºi <b>[img][/img]</b> (citiþi punctul urmãtor), <b>[b][/b]</b> etc. Ca ºi în cazul balizelor de formatare depinde de dumneavoastrã sã vã asiguraþi de ordinea corectã de deschidere ºi închidere. Spre exemplu: <br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br /> <u>nu</u> este corect, lucru care ar putea duce la ºtergerea mesajului, aºa cã aveþi mare grijã.");


$faq[] = array("--", "Afiºarea imaginilor în mesaje");
$faq[] = array("Adãugarea unei imagini în mesaj", "Codul BB include o balizã pentru includerea imaginilor în mesajele dumneavoastrã. Doua lucruri foarte importante trebuie þinute minte: mulþi utilizatori nu apreciazã afiºarea multor imagini într-un mesaj ºi imaginea trebuie sã fie deja disponibilã pe internet (nu poate exista doar pe calculatorul dumneavoastrã, doar dacã nu rulaþi un server de web). Nu existã în prezent nici o modalitate de stocare a imaginilor local cu phpBB (toate aceste probleme vor fi luate în discuþie la urmatoarea versiune). Pentru a afiºa o imagine trebuie sa închideþi URL-ul imaginii în balize <b>[img][/img]</b>. Spre exemplu: <br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b>.<br /><br /> Aºa cum s-a vãzut în secþiunea anterioarã despre URL-uri, puteþi include o imagine într-o balizã <b>[url][/url]</b> dacã doriþi, spre exemplu :<br /><br /><b>[url=http://www.php.net/][img]</b>http://www.phpbb.com/images/logo.gif<b>[/img][/url]</b><br /><br /> ar genera: <br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"templates/subSilver/images/logo_phpBB_med.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Diverse");
$faq[] = array("Pot sã îmi adaug propriile balize (tag-uri)?", "Nu, din nefericire; nu direct în phpBB 2.0 . Cãutãm modalitãþi de a oferi balize modificabile pentru urmatoarea versiune majorã.");

//
// This ends the codul BB guide entries
//

?>
