<?php
/***************************************************************************
 *                         lang_bbcode.php [Croatian]
 *                            -------------------
 *     begin                : Monday Dec 01 2002
 *     copyright            : (C) 2002 Hrvoje Stankov
 *     email                : hrvoje@spirit.hr
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

$faq[] = array("--","Uvod");
$faq[] = array("Što je BBCode?", "BBCode je posebna implementacija HTML-a. Da li možete koristiti BBCode u vašim porukama na forumu odreðuje administrator. Možete onemoguæiti BBCode na formularu za slanje poruka. BBCode sam po sebi je slièan stilu u HTML, tagovi su umetnuti u vitièastim zagradama [ i ] raðe nego &lt; i &gt; i nudi veæu kontrolu kako je nešto prikazano. U zavisnosti od podloge koju koristite vidjet æete da je dodavanje BBCoda vašim porukama mnogo lakše klikanjem miša na polja iznad poruke na formularu za unos poruka. Èak i sa tim možda æe vam ovaj vodiè biti od koristi.");

$faq[] = array("--","Formatiranje teksta");
$faq[] = array("Kako napisati podebljani, kosi i podvuèeni tekst", "BBCode sadrži tagove koji vam omoguæavaju brzo mijenjanje osnovnog stila vašeg teksta. Ovo se postiže na više naèina: <ul><li>Da bi ste napisali tekst podebljano umetnite ga u <b>[b][/b]</b>, npr. <br /><br /><b>[b]</b>Zdravo<b>[/b]</b><br /><br />æe postati <b>Zdravo</b></li><li>Za podvlaèenje koristite <b>[u][/u]</b>, npr :<br /><br /><b>[u]</b>Dobro jutro<b>[/u]</b><br /><br />postaje <u>Dobro jutro</u></li><li>Da ukosite tekst koristite <b>[i][/i]</b>, npr.<br /><br />Ovo je <b>[i]</b>sjajno!<b>[/i]</b><br /><br />æe dati Ovo je <i>sjajno!</i></li></ul>");
$faq[] = array("Kako da promjenim boju teksta ili velièinu", "Da bi ste izmjenili boju ili velièinu teksta možete koristiti sljedeæe tagove. Zapamtite da æe krajnji rezultat zavisiti od browsera èitaèa i sistema: <ul><li>Mijenjanje boje teksta moguæe je tako što æete ga umetnuti u <b>[color=][/color]</b>. Možete odrediti prepoznatljiv naziv boje (npr. crvena, plava, žuta, itd.) ili u heksadecimalnom obliku, npr. #FFFFFF, #000000. Npr, za crveni tekst koristite:<br /><br /><b>[color=red]</b>Zdravo!<b>[/color]</b><br /><br />ili<br /><br /><b>[color=#FF0000]</b>Zdravo!<b>[/color]</b><br /><br />æe u oba sluèaja dati <span style=\"color:red\">Zdravo!</span></li><li>Mijenjanje velièine teksta je slièno koristeæi <b>[size=][/size]</b>. Ovaj tag zavisi od podloge koju koristite ali preporuèeni format je numerièka vrijednost koja predstavlja velièinu teksta u pikselima, poèevši od 1 (toliko malo da ga neæete ni vidjeti) pa sve do 29 (vrlo,vrlo veliko). Npr :<br /><br /><b>[size=9]</b>MALO<b>[/size]</b><br /><br />æe generalno biti <span style=\"font-size:9px\">MALO</span><br /><br />dok æe:<br /><br /><b>[size=24]</b>OGROMNO!<b>[/size]</b><br /><br />biti <span style=\"font-size:24px\">OGROMNO!</span></li></ul>");
$faq[] = array("Da li mogu kombinirati tagove za formatiranje?", "Da, naravno da možete, na primjer da biste privukli pažnju možete pisati:<br /><br /><b>[size=18][color=red][b]</b>POGLEDAJ ME!<b>[/b][/color][/size]</b><br /><br />ovo æe dati <span style=\"color:red;font-size:18px\"><b>POGLEDAJ ME!</b></span><br /><br />Ne preporuèujemo da pišete puno teksta koji izgleda ovako! Zapamtite da je na vama, tj, piscu da se pobrine da su tagovi pravilno zatvoreni. Na primjer ovo je netoèno:<br /><br /><b>[b][u]</b>Ovo je netaèno<b>[/b][/u]</b>");

$faq[] = array("--","Citiranje i dobijanje teksta fiksne širine");
$faq[] = array("Citiranje teksta u odgovorima", "Postoje dva naèina kojima možete citirati tekst, sa ili bez reference.<ul><li>Kada koristite Quote funkciju za odgovor na poruku primjetit æete da je tekst poruke dodan u prozoru poruke umetnut u <b>[quote=\"\"][/quote]</b> bloku. Ova metoda vam omoguæava da citirate sa referencom na osobu ili bilo šta drugo što želite stavite! Na primjer da biste citirali dio tekst Mr. Bloby upišite:<br /><br /><b>[quote=\"Mr. Blobby\"]</b>Tekst Mr. Blobby koji ste napisali æe otiæi ovdje<b>[/quote]</b><br /><br />Rezultirajuæa poruka æe automatski dodati, Mr. Blobby wrote: prije samog teksta. Zapamtite da <b>morate</b> ubaciti zagrade \"\" oko imena koga citirate, jer nisu opcijske.</li><li>Druga metoda vam omoguæava da toèno citirate nešto. Da biste ovo iskoristili umetnite tekst u <b>[quote][/quote]</b> tagove. Kada pogledate poruku jednostavno æe se prikazati, Quote: prije samog teksta.</li></ul>");
$faq[] = array("Dobijanje koda ili podatke fiksne širine", "Ako želite prikazati dio koda ili u stvari bilo što što zahtjeva fiksnu širinu, npr. Courier font - treba umetnuti tekst u <b>[code][/code]</b> tagove, npr.<br /><br /><b>[code]</b>echo \"Ovo je neki kod\";<b>[/code]</b><br /><br />Sva formatiranja korištena izmeðu <b>[code][/code]</b> tagova su zapamæena kada ih kasnije pogledate.");

$faq[] = array("--","Generiranje lista");
$faq[] = array("Izrada nesreðene liste", "BBCode podržava dva tipa lista, nesreðene i sreðene. One su bitne isto koliko i njihova HTML zamjena. Nesreðena lista daje svaku stavku dosljedno jednu za drugom uvlaèeæi svaku stavku. Da napravite nesreðenu lisu koristite <b>[list][/list]</b> i definirajte svaku stavku liste koristeæi <b>[*]</b>. Na primjer da izlistate vaše omiljene boje koristite:<br /><br /><b>[list]</b><br /><b>[*]</b>Crvena<br /><b>[*]</b>Plava<br /><b>[*]</b>Žuta<br /><b>[/list]</b><br /><br />Ovim se dobija sljedeæa lista:<ul><li>Crvena</li><li>Plava</li><li>Žuta</li></ul>");
$faq[] = array("Izrada sreðene liste", "Drugi tip liste, sreðena lista daje vam kontrolu kakav æe biti rezultat prije svake stavke. Da biste napravili sreðenu listu koristite <b>[list=1][/list]</b> da napravite listu brojeva ili alternativno <b>[list=a][/list]</b> za abecednu listu. Kao i kod nesreðene liste stavke se oznaèavaju sa <b>[*]</b>. Na prijmer:<br /><br /><b>[list=1]</b><br /><b>[*]</b>Otiðite u duæan<br /><b>[*]</b>Kupite novo raèunalo<br /><b>[*]</b>Zakunite se pred raèunalom da kada se razbije<br /><b>[/list]</b><br /><br />æe dati sljedeæe:<ol type=\"1\"><li>Otiðite u duæan</li><li>Kupite novo raèunalo</li><li>Zakunite se pred raèunalom da kada se razbije</li></ol>Dok za abecednu listu koristite:<br /><br /><b>[list=a]</b><br /><b>[*]</b>Prvi moguæi odgovor<br /><b>[*]</b>Drugi moguæi odgovor<br /><b>[*]</b>Treæi moguæi odgovor<br /><b>[/list]</b><br /><br />daje<ol type=\"a\"><li>Prvi moguæi odgovor</li><li>Drugi moguæi odgovor</li><li>Treæi moguæi odgovor</li></ol>");

$faq[] = array("--", "Izrada linkova");
$faq[] = array("Link na drugi site", "phpBB BBCode ima više naèina da napravite URI-e, Uniform Resource Indicators poznatije kao URLs.<ul><li>Prvi od njih koristi <b>[url=][/url]</b> tag, šta god ukucali poslje = znaka æe uzroèiti da se sadržaj taga ponaša kao URL. Na primjer da linkate na phpBB.com koristite:<br /><br /><b>[url=http://www.phpbb.com/]</b>Posjetite phpBB!<b>[/url]</b><br /><br />Ovo æe generirati sljedeæi link, <a href=\"http://www.phpbb.com/\" target=\"_blank\">Posjetite phpBB!</a> Primetit æete da se link otvara u novom prozoru pa korisnik može nastaviti rad na forumu ako želi.</li><li>Ako želite da se URL prikaže kao link možete to jednostavno izvesti koristeæi:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />Ovo æe generirati sljedeæi link, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>Dodatne phpBB moguænosti zvane <i>Magièni linkovi</i>, æe pretvoriti svaki sintaksno toèan URL u link bez potrebe da definirate bilo kakav tag ili èak i prefiks http://. Na primjer utipkavanjem www.phpbb.com u vašoj poruci automatski dobijate <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> kada pogledate poruku.</li><li>Isto se dešava i sa email adresama, možete ili odrediti adresu na primjer:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />što æe rezultirati <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> ili možete jednostavno unjeti no.one@domain.adr u vašoj poruci i bit æe automatski pretvoreno kada pogledate poruku.</li></ul>Kao što sa svim BBCode tagovima možete umotati URLs oko bilo kojeg taga kao što je <b>[img][/img]</b> (Vidi sljedeæi odlomak), <b>[b][/b]</b>, itd. tako i sa tagovima za formatiranje morate paziti da se pravilno zatvore, na primjer:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br /><u>nije</u> toèno što može dovesti da se vaša poruka izbriše pa zato pazite.");

$faq[] = array("--", "Prikazivanje slika u porukama");
$faq[] = array("Dodavanje slike u poruku", "phpBB BBCode sadrži tag za ubacivanje slika u vaše poruke. Dvije vrlo važne stvari koje trebate upamtiti prilikom korištenja ovog taga su; mnogi korisnici ne cijene puno slika u porukama i drugo slika koju prikazujete mora veæ biti dostupna na internetu (ne može postojati na vašem raèunalu na primjer, osim ako nemate web server!). Trenutno ne postoji naèin èuvanja slika lokalno sa phpBB (sva ova ogranièenja bi trebalo biti ugraðena u sljedeæu verziju phpBB). Da biste prikazali sliku morate omotati URL koji vodi do slike sa <b>[img][/img]</b> tagovima. Na primer:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />Kao što ste primjetili u URL dijelu iznad možete okružiti sliku u <b>[url][/url]</b> tag ako želite , npr.<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />æe dati:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"templates/subSilver/images/logo_phpBB_med.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Ostalo");
$faq[] = array("Mogu li da dodati vlastite tagove?", "Ne, bar ne direktno u verziji phpBB 2.0. Pokušat æemo ponuditi vlastite tagove u sledeæoj verziji");

//
// This ends the BBCode guide entries
//

?>