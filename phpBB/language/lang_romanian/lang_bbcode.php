<?php
/***************************************************************************
 *                         lang_bbcode.php [romana fara diacritice]
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
$faq[] = array("Ce este codul BB?", "Codul BB este o implementare speciala a HTML-ului. Daca puteti folosi codul BB sau nu in mesajele dumneavoastra este alegerea administratorului. In plus, puteti dezactiva codul BB de la mesaj la mesaj din formularul de publicare. Codul BB este similar cu HTML-ul, balizele (tag-urile) sunt scrise intre paranteze patrate [ si ] decat intre &lt; si &gt; si ofera un control mai bun asupra ce si cum este afisat. In functie de sablonul pe care il folositi puteti descoperi ca adaugarea de cod BB la mesajele dumneavoastra este mai usoara printr-un set de butoane. Chiar si asa probabil ca veti gasi acest ghid folositor.");


$faq[] = array("--","Formatarea textului");
$faq[] = array("Cum sa creati text ingrosat, cursiv si subliniat", "Codul BB include balize pentru a va permite sa schimbati rapid stilul textului dumneavoastra. Acest lucru poate fi obtinut in urmatoarele moduri: <ul><li>Pentru a face o bucata de text ingrosata (bold), includeti-o intre <b>[b][/b]</b> , spre exemplu <br /><br /><b>[b]</b>Salut<b>[/b]</b><br /><br /> va deveni <b>Salut</b></li><li>Pentru subliniere folositi <b>[u][/u]</b>, spre exemplu <br /><br /><b>[u]</b>Buna dimineata<b>[/u]</b><br /><br />devine <u>Buna dimineata</u></li><li>Pentru a scrie cu font cursiv (italic) folositi <b>[i][/i]</b> , spre exemplu <br /><br /><b>[i]</b>Super!<b>[/i]</b><br /><br />va deveni <i>Super!</i></li></ul>");
$faq[] = array("Cum sa schimbati culoarea textului sau marimea", "Pentru a schimba culoarea sau marimea textului dumneavoastra puteti folosi mai multe balize. Tineti minte ca felul cum apare mesajul depinde de browser-ul si sistemul clientului :<ul><li> Schimbarea culorii textului se face prin trecerea intre <b>[color=][/color]</b>. Puteti specifica fie o culoare cunoscuta, in limba engleza, (<i>red</i> pentru rosu), <i>blue</i> pentru albastru, <i>yellow</i> pentru galben) sau un triplet hexazecimal (#FFFFFF, #000000). Spre exemplu, pentru a scrie cu rosu veti folosi :<br /><br /><b>[color=red]</b>Salut!<b>[/color]</b><br /><br />sau<br /><br /><b>[color=#FF0000]</b>Salut!<b>[/color]</b><br /><br /> Amblele vor avea ca rezultat <span style=\"color:red\">Salut!</span></li><li>Schimbarea marimii textului este facuta in acelasi fel folosind <b>[size=][/size]</b>. Aceasta baliza depinde de sablonul pe care il folositi dar formatul recomandat este o valoare numerica reprezentand marimea textului in pixeli, pornind de la 1 (extrem de mic) si ajungand pana la 29 (foarte mare). Spre exemplu: <br /><br /><b>[size=9]</b>MIC<b>[/size]</b><br /><br /> in general va avea ca rezultat <span style=\"font-size:9px\">MIC</span><br /><br /> in vreme ce <br /><br /><b>[size=24]</b>ENORM!<b>[/size]</b><br /><br />va fi <span style=\"font-size:24px\">ENORM!</span></li></ul>");
$faq[] = array("Pot combina balizele (tag-urile) de formatare?", "Desigur. Spre exemplu, pentru a atrage atentia cuiva ati putea sa scrieti <br /><br /><b>[size=18][color=red][b]</b>PRIVESTE-MA!<b>[/b][/color][/size]</b><br /><br />si rezultatul va fi <span style=\"color:red;font-size:18px\"><b>PRIVESTE-MA!</b></span><br /><br /> Totusi, nu va recomandam sa scrieti prea mult text astfel ! Tineti minte ca depinde de dumneavoastra sa va asigurati ca balizele sunt inchise corect. Spre exemplu, urmatoarea secventa este incorecta: <br /><br /><b>[b][u]</b>Asa este gresit<b>[/b][/u]</b>");

$faq[] = array("--","Citate si text cu latime fixa");
$faq[] = array("Citarea textului in raspunsuri", "Exista doua modalitati de a cita textul, cu referinta si fara.<ul><li>Cand utilizati functia de raspuns inclusiv mesajul, ar trebui sa observati ca mesajul respectiv este adagat in fereastra de publicare inclus intr-un bloc <b>[quote=\"\"][/quote]</b>. Aceasta metoda va permite sa il citati cu referinta la o persoana sau orice altceva doriti sa scrieti ! Spre exemplu, pentru a cita o bucata de text scrisa de Dl. Ionescu ati scrie :<br /><br /><b>[quote=\"Dl. Ionescu\"]</b> Textul scris de Dl. Ionescu <b>[/quote]</b><br /><br /> Rezultatul va fi ca Dl. Ionescu a scris: va fi adaugat inainte de textul citat. Tine-ti minte ca <b>trebuie</b> sa includeti ghilimelele \"\" in jurul numelui pe care il citati. Acestea nu sunt optionale.</li><li> A doua metoda va permite sa citati fara un autor. Pentru a folosi acest lucru introduceti textul intre balizele <b>[quote][/quote]</b>. Cand il citati, mesajul va arata pur si simplu Citat: inainte de textul propriu-zis.</li></ul>");
$faq[] = array("Generarea de cod sau de text cu marime fixa", "Daca doriti sa scrieti o bucata de cod sau - de fapt - orice altceva care are nevoie de o latime fixa, cum ar fi un font de tip Courier, ar trebui sa introduceti textul intre balize <b>[code][/code]</b> , spre exemplu: <br /><br /><b>[code]</b>echo \"O linie de cod\";<b>[/code]</b><br /><br />Toate formatarile folosite intre balizele <b>[code][/code]</b> sunt retinute cand cititi mesajul.");


$faq[] = array("--","Generarea listelor");
$faq[] = array("Crearea unei liste neordonate", "Codul BB include doua tipuri de liste, neordonate si ordonate. In mare sunt la fel cu echivalentele lor HTML. O lista neordonata scrie fiecare obiect din lista secvential adaugandu-le un alineat si un caracter <i>bullet</i>. Pentru a crea o lista neordonata folositi <b>[list][/list]</b> si definiti fiecare obiect din lista folosind <b>[*]</b>. Spre exemplu, pentru a va scrie culorile preferate ati putea folosi : <br /><br /><b>[list]</b><br /><b>[*]</b>rosu<br /><b>[*]</b>albastru<br /><b>[*]</b>galben<br /><b>[/list]</b><br /><br />Aceasta ar genera urmatoarea lista: <ul><li>rosu</li><li>albastru</li><li>galben</li></ul>");
$faq[] = array("Crearea unei liste ordonate", "Al doilea tip de lista, lista ordonata va ofera controlul asupra ceea ce este afisat inaintea fiecarui obiect. Pentru a crea o lista ordonata folositi <b>[list=1][/list]</b> pentru o lista numerica sau <b>[list=a][/list]</b> pentru o lista alfabetica. Ca si la listele neordonate, obiectele sunt indicate folosind <b>[*]</b>. Spre exemplu: <br /><br /><b>[list=1]</b><br /><b>[*]</b>Mergi la magazin<br /><b>[*]</b>Cumpara un calculator<br /><b>[*]</b>Tipa la el cand crapa<br /><b>[/list]</b><br /><br /> va genera urmatoarele:<ol type=\"1\"><li>Mergi la magazin</li><li>Cumpara un calculator</li><li>Tipa la el cand crapa</li></ol> pe cand pentru o lista alfabetica ati folosi :<br /><br /><b>[list=a]</b><br /><b>[*]</b>Primul raspuns<br /><b>[*]</b>Al doilea raspuns<br /><b>[*]</b>Al treilea raspuns<br /><b>[/list]</b><br /><br /> avand ca rezultat: <ol type=\"a\"><li>Primul raspuns</li><li>Al doilea raspuns</li><li>Al treilea raspuns</li></ol>");


$faq[] = array("--", "Crearea legaturilor");
$faq[] = array("Legaturi catre alte site-uri", "Codul BB ofera multe resurse de creare a legaturilor, cunoscute mai bine ca URL-uri. <ul><li>Prima din acestea foloseste baliza <b>[url=][/url]</b>, si orice veti scrie dupa semnul egal va determina continutul acelei balize sa se comporte ca un URL. Spre exemplu, o legatura catre phpBB ar fi: <br /><br /><b>[url=http://www.phpbb.com/]</b>Vizitati phpBB!<b>[/url]</b><br /><br />Rezultatul ar fi urmatorea legatura: <a href=\"http://www.phpbb.com/\" target=\"_blank\">Vizitati phpBB!</a>. Veti observa ca legatura se va deschide intr-o fereastra noua pentru ca utilizatorul sa poata continua sa utilizeze forumul daca doreste.</li></li> Daca doriti sa fie afisat chiar URL-ul atunci puteti sa scrieti: <br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br /> Acesta va genera urmatoarea legatura: <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li> Alte facilitati phpBB includ si ceva numit <i>legaturi magice</i>, care va transforma un URL corect din punct de vedere sintactic intr-un URL fara ca dumneavoastra sa specificati vreo baliza sau sa incepeti cu <i>http://</i>. Spre exemplu, daca veti scrie www.phpbb.com aceasta va deveni direct <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a>. Acelasi lucru se intampla si cu adresele de mail. Puteti folosi o adresa explicit spre exemplu: <br /><br /><b>[email]</b>cineva@domeniu.adr<b>[/email]</b><br /><br />care va rezulta in <a href=\"mailto:cineva@domeniu.adr\">cineva@domeniu.adr</a> sau puteti sa scrieti direct cineva@domeniu.adr si mesajul dumneavoastra va fi automat convertit cand il veti vizualiza. </li></ul> La fel ca tag-urile codului BB puteti folosi pentru URL-uri orice tip de tag, ca si <b>[img][/img]</b> (cititi punctul urmator), <b>[b][/b]</b> etc. Ca si in cazul balizelor de formatare depinde de dumneavoastra sa va asigurati de ordinea corecta de deschidere si inchidere. Spre exemplu: <br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br /> <u>nu</u> este corect, lucru care ar putea duce la stergerea mesajului, asa ca aveti mare grija.");


$faq[] = array("--", "Afisarea imaginilor in mesaje");
$faq[] = array("Adaugarea unei imagini in mesaj", "Codul BB include o baliza pentru includerea imaginilor in mesajele dumneavoastra. Doua lucruri foarte importante trebuie tinute minte: multi utilizatori nu apreciaza afisarea multor imagini intr-un mesaj si imaginea trebuie sa fie deja disponibila pe internet (nu poate exista doar pe calculatorul dumneavoastra, doar daca nu rulati un server de web). Nu exista in prezent nici o modalitate de stocare a imaginilor local cu phpBB (toate aceste probleme vor fi luate in discutie la urmatoarea versiune). Pentru a afisa o imagine trebuie sa inchideti URL-ul imaginii in balize <b>[img][/img]</b>. Spre exemplu: <br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b>.<br /><br /> Asa cum s-a vazut in sectiunea anterioara despre URL-uri, puteti include o imagine intr-o baliza <b>[url][/url]</b> daca doriti, spre exemplu :<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br /> ar genera: <br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"templates/subSilver/images/logo_phpBB_med.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Diverse");
$faq[] = array("Pot sa imi adaug propriile balize (tag-uri)?", "Nu, din nefericire; nu direct in phpBB 2.0 . Cautam modalitati de a oferi balize modificabile pentru urmatoarea versiune majora.");

//
// This ends the codul BB guide entries
//

?>
