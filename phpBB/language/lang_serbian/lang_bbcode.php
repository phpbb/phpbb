<?php
/***************************************************************************
 *                         lang_bbcode.php [Serbian]
 *                            -------------------
 *     begin                : Monday Sep 30 2002
 *     copyright            : (C) 2002 Simic Vladan
 *     email                : vlada@extremecomputers.co.yu
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
$faq[] = array("Šta je BBKod?", "BBKod je specijalna implementacija HTML-a. Da li možete koristiti BBKod u vašim porukama na forumu odreðuje administrator. Možete onemoguæiti BBKod na formi za slanje poruka. BBKod sam po sebi je slièan stilu u HTML, tagovi su umetnuti u vitièastim zagradama [ i ] radije nego &lt; i &gt; i nudi veæu kontrolu kako je nešto prikazano. U zavisnosti od podloge koju koristite videæete da je dodavanje BBKoda vašim porukama mnogo lakške kliktanjem miša na polja iznad poruke na formi za unos poruka. Èak i sa tim možda æe vam ovaj vodiè biti od koristi.");

$faq[] = array("--","Formatiranje teksta");
$faq[] = array("Kako napisati podebljan, kosi i podruèeni tekst", "BBKod sadrži tagove  koji vam omoguæuju da brzo promenite osnovni stil vašeg teksta. Ovo se postiže na više naèina: <ul><li>Da bi ste napisali tekst podebljano umetnite ga u <b>[b][/b]</b>, npr. <br /><br /><b>[b]</b>Zdravo<b>[/b]</b><br /><br />æe postati <b>Zdravo</b></li><li>Za podvlaèenje koristite <b>[u][/u]</b>, na primer:<br /><br /><b>[u]</b>Dobro jutro<b>[/u]</b><br /><br />postaje <u>Dobro jutro</u></li><li>Da iskosite tekst koristite <b>[i][/i]</b>, npr.<br /><br />Ovo je <b>[i]</b>sjajno!<b>[/i]</b><br /><br />æe dati Ovo je <i>sjajno!</i></li></ul>");
$faq[] = array("Kako da promenim boju teksta ili velièinu", "Da bi ste izmenili boju ili velièinu teksta možete koristiti sledeæe tagove. Zapamtite da æe krajnji rezujtat zavisiti od browsera èitaèa i sistema: <ul><li>Menjanje boje teksta moguæe je tako što æete ga umetnuti u <b>[color=][/color]</b>. Možete odrediti prepoznatljiv naziv boje (npr. crvena, plava, žuta, itd.) ili u heksadecimalnom obliku, npr. #FFFFFF, #000000. Na primer, za crveni tekst koristite:<br /><br /><b>[color=red]</b>Zdravo!<b>[/color]</b><br /><br />ili<br /><br /><b>[color=#FF0000]</b>Zdravo!<b>[/color]</b><br /><br />æe u oba sluèaja dati <span style=\"color:red\">Zdravo!</span></li><li>Menjanje velièine teksta je slièno koristeæi <b>[size=][/size]</b>. Ovaj tag zavisi od podloge koju koristite ali preporuèeni format je numerièka vrednost koja predstavlja velièinu teksta u pikselima, poèevši od 1 (toliko malo da ga neæete ni videti) pa sve do 29 (veoma veliko). Na primer:<br /><br /><b>[size=9]</b>MALO<b>[/size]</b><br /><br />æe generalno biti <span style=\"font-size:9px\">MALO</span><br /><br />dok æe:<br /><br /><b>[size=24]</b>OGROMNO!<b>[/size]</b><br /><br />biti <span style=\"font-size:24px\">OGROMNO!</span></li></ul>");
$faq[] = array("Da li mogu da kombinujem tagove za formatiranje?", "Da, naravno da možete, na primer da biste privukli pažnju možete pisati:<br /><br /><b>[size=18][color=red][b]</b>POGLEDAJ ME!<b>[/b][/color][/size]</b><br /><br />ovo æe dati <span style=\"color:red;font-size:18px\"><b>POGLEDAJ ME!</b></span><br /><br />Ne preporuèujemo da pišete puno teksta koji izgleda ovako! Zapamtite da je na vama, tj, piscu da se pobrine da su tagovi pravilno zatvoreni. Na primer ovo je netaèno:<br /><br /><b>[b][u]</b>Ovo je netaèno<b>[/b][/u]</b>");

$faq[] = array("--","Citiranje i dobijanje teksta fiksne širine");
$faq[] = array("Citiranje teksta u odgovorima", "Postoje dva naèina kojima možete citirati tekst, sa ili bez reference.<ul><li>Kada koristite Quote funkciju za odgovor na poruku primetiæete da je tekst poruke dodat u prozoru poruke umetnut u <b>[quote=\"\"][/quote]</b> bloku. Ovaj metod vam omoguæava da citirate sa referencom na osobu ili bilo šta drugo što želite da stavite! Na primer da biste citirali deo tekst Mr. Bloby upišite:<br /><br /><b>[quote=\"Mr. Blobby\"]</b>Tekst Mr. Blobby koji ste napisali æe otiæi ovde<b>[/quote]</b><br /><br />Rezultujuæa poruka æe automatski dodati, Mr. Blobby wrote: pre samog teksta. Zapamtite da <b>morate</b> ubaciti zagrade \"\" oko imena koga citirate, jer nisu opcione.</li><li>Drugi metod vam omoguæava da slepo citirate nešto. Da biste ovo iskoristili umetnite tekst u <b>[quote][/quote]</b> tagove. Kada pogledate poruku jednostavno æe se prikazati, Quote: pre samog teksta.</li></ul>");
$faq[] = array("Dobijanje koda ili podatke fiksne širine", "Ako želite da prikažete deo koda ili u stvari bilo šta što zahteva fiksnu širinu, npr. Courier font - treba umetnuti tekst u <b>[code][/code]</b> tagove, npr.<br /><br /><b>[code]</b>echo \"Ovo je neki kod\";<b>[/code]</b><br /><br />Sva formatiranja korišæena izmeðu <b>[code][/code]</b> tagova su zapamæena kada ih kasnije pogledate.");

$faq[] = array("--","Generisanje lista");
$faq[] = array("Pravljenje nesreðene liste", "BBKod podržava dva tipa lista, nesreðene i sreðene. One su bitne isto koliko i njihova HTML zamena. Nesreðena lista daje svaku stavku dosledno jednu za drugom drugom uvlaèeæi svaku stavku. Da biste napravili nesreðenu lisu koristite <b>[list][/list]</b> i definišite svaku stavku liste koristeæi <b>[*]</b>. Na primer da biste izlistali vaše omiljene boje koristite:<br /><br /><b>[list]</b><br /><b>[*]</b>Crvena<br /><b>[*]</b>Plava<br /><b>[*]</b>Žuta<br /><b>[/list]</b><br /><br />Ovim se dobija sledeæa lista:<ul><li>Crvena</li><li>Plava</li><li>Žuta</li></ul>");
$faq[] = array("Pravljenje sreðene liste", "Drugi tip liste, sreðena lista daje vam kontrolu kakav æe biti rezultat pre svake stavke. Da biste napravili sreðenu listu koristite <b>[list=1][/list]</b> da napravite listu brojeva ili alternativno <b>[list=a][/list]</b> za abecednu listu. Kao i kod nesreðene liste stavke se oznaèavaju sa <b>[*]</b>. Na primer:<br /><br /><b>[list=1]</b><br /><b>[*]</b>Idite u prodavnicu<br /><b>[*]</b>Kupite nov kompjuter<br /><b>[*]</b>Zakunite se pred kompjuterom da kada se razbije<br /><b>[/list]</b><br /><br />æe dati sledeæe:<ol type=\"1\"><li>Idite u prodavnicu</li><li>Kupite nov kompjuter</li><li>Zakunite se pred kompjuterom da kada se razbije</li></ol>Dok za abecednu listukoristite:<br /><br /><b>[list=a]</b><br /><b>[*]</b>Prvi moguæi odgovor<br /><b>[*]</b>Drugi moguæi odgovor<br /><b>[*]</b>Treæi moguæi odgovor<br /><b>[/list]</b><br /><br />daje<ol type=\"a\"><li>Prvi moguæi odgovor</li><li>Drugi moguæi odgovor</li><li>Treæi moguæi odgovor</li></ol>");

$faq[] = array("--", "Pravljenje linkova");
$faq[] = array("Link na drugi sajt", "phpBB BBKod više naèina da napravite URI-e, Uniform Resource Indicators poznatije kao URLs.<ul><li>Prvi od njih koristi <b>[url=][/url]</b> tag, šta god ukucali posle = znaka æe prouzrokovati da se sadržaj taga ponaša kao URL. Na primer da bi ste linkovali na phpBB.com koristite:<br /><br /><b>[url=http://www.phpbb.com/]</b>Posetite phpBB!<b>[/url]</b><br /><br />Ovo æe generisati sledeæi link, <a href=\"http://www.phpbb.com/\" target=\"_blank\">Posetite phpBB!</a> Primetiæete da se link otvara u novom prozoru pa korisnik može nastaviti rad na forumu ako želi.</li><li>Ako želite da se URL prikaže kao link možete to jednostavno izvesti koristeæi:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />Ovo æe generisati sledeæi link, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>Dodatne phpBB moguænosti zvane <i>Magièni linkovi</i>, æe pretvoriti svaki sintaksno taèan URL u link bez potrebe da definišete bilo kakav tag ili èak i prefiks http://. Na primer kucanjem www.phpbb.com u vašoj poruci automatski dobijate <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> kada pogledate poruku.</li><li>Isto se dešava i sa email adresama, možete ili naznaèiti adresu na primer:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />što æe rezultovati <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> ili možete jednostavno uneti no.one@domain.adr u vašoj poruci i biæe automatski konvertovano kada pogledate poruku.</li></ul>Kao što sa svim BBKod tagovima možete umotati URLs oko bilo kojeg taga kao što je <b>[img][/img]</b> (Vidi sledeæi pasus), <b>[b][/b]</b>, itd. tako i sa tagovima za formatiranje morate paziti da se pravilno zatvore, na primer:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br /><u>nije</u> taèno što može dovesti da se vaša poruka izbriše pa zato pazite.");

$faq[] = array("--", "Prikazivanje slika u porukama");
$faq[] = array("Dodavanje slike u poruku", "phpBB BBKod sadrži tag sa ubacivanje slika u vaše poruke. Dve vrlo važne stvari koje trebate da upamtite prilikom korišæenja ovog taga su; mnogi korisnici ne cene puno slika u porukama i drugo slika koju prikazujete mora veæ biti dostupna na internetu (ne može postojati na vašek kompjuteru na primer, osim ako nemate web server!). Trenutno ne postoji naèin èuvanja slika lokalno sa phpBB (sva ova ogranièenja bi trebalo da budu ugraðena u sledeæu verziju phpBB). Da biste prikazali sliku morate okružiti URL koji vodi do slike sa <b>[img][/img]</b> tagovima. Na primer:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />Kao što ste primetili u URL delu iznad možete okružiti sliku u <b>[url][/url]</b> tag ako želite , npr.<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />æe dati:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"templates/subSilver/images/logo_phpBB_med.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Ostalo");
$faq[] = array("Mogu li da dodam sopstvene tagove?", "Ne, bar ne direktno u verziji phpBB 2.0. Gledaæemo da ponudimo proizvoljne tagove u sledeæoj verziji");

//
// This ends the BBCode guide entries
//

?>