<?php
/***************************************************************************
 *                         lang_bbcode.php [english]
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
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

/* CONTRIBUTORS
	2002-12-15	Philip M. White (pwhite@mailhaven.com)
Prevedel: Ladislav Golouh, www.Razmerje.com, Ladislavg@razmerje.com
Dodal sem alineji: Kaj so oznaèbe in kako lahko napišem oznaèbe.
		Fixed many minor grammatical problems.
*/
 
// 
// To add an entry to your BBCode guide simply add a line to this file in this format:
// $faq[] = array("question", "answer");
// If you want to separate a section enter $faq[] = array("--","Block heading goes here if wanted");
// Links will be created automatically
//
// DO NOT forget the ; at the end of the line.
// Do NOT put double quotes (") in your BBCode guide entries, if you absolutely must then escape them ie. \"something\";
//
// The BBCode guide items will appear on the BBCode guide page in the same order they are listed in this file
//
// If just translating this file please do not alter the actual HTML unless absolutely necessary, thanks :)
//
// In addition please do not translate the colours referenced in relation to BBCode any section, if you do
// users browsing in your language may be confused to find they're BBCode doesn't work :D You can change
// references which are 'in-line' within the text though.
//
  
$faq[] = array("--","Navodila");
$faq[] = array("Kaj so kode BBCode?", "BBCode kode so oblika HTML jezika. Ali lahko uporabljaš jezik BBCode v svojih objavah na forumu, odloèi administrator. Poleg tega lahko sam onemogoèiš BBCode kode v doloèeni objavi v obrazcu za pošiljanje sporoèila. BBCode kode so preprostejša oblika HTML: oznaèbe (ukazi) so obdani z oglatimi oklepaji [ in ], kar je bolje kot &lt; in &gt; ob tem ponuja boljši nadzor nad tem, kaj in kako nekdo želi nekaj prikazati. Odvisno od predloge, ki jo uporabljate, lahko najdete dodatke za BBCode kode, ki vam še olajšajo oblikovanje vašega sporoèila preko gumbov za klikanje z miško nad poljem za vpis sporoèila v obrazcu za pošiljanje. Prièujoèi vodnik vam bo v pomoè tudi pri tem.");

$faq[] = array("Kaj so oznaèbe", "Oznaèba ali tag je ukaz, ki spremeni del besedila v želeno obliko. Vsaka oznaèba ima zaèetek in zakljuèek, ki se od zaèetne oznaèbe praviloma razlikuje s tem, da ima na zaèetku znotraj oklepaja še poševnico /. Znotraj oklepajev ni presledkov! Z oznaèbami (tags) torej programirate besedilo za prikaz.");
$faq[] = array("Kako lahko napišem oznaèbe", " Podrobnosti preberite spodaj. Tukaj na splošno. Lahko preprosto odtipkate oglati oklepaj (èe prestavite na angleško tipkovnico, npr. s ctrl+shift, ali si ga kopirate) [, sledi ukaz, npr. b, oglati zaklepaj ]. In zakljuèni ukaz, ki vsebuje za prvim oklepajem [ poševnico / pred ukazom b]. Znotraj oklepajev ne sme biti nobenega presledka! Najlažji naèin je, da oznaèite tekst in potem kliknete ustrezno oznaèbo, ki omeji tekst z zaèetno in s konèno oznaèbo. Ko pišete, se na koncu teksta doda oznaka, ki jo dobite s tipko alt in prvo èrko oznake (alt+b za krepko, alt+q za citiram). Ko naslednjiè pritisnete alt+ prvo èrko oznake, se izpiše zakljuèni ukaz. Ali ko pritisnite vrstico z besedilom <u>Zakljuène oznake odprtih ukazov</u>, da se zakljuèijo odprti ukazi na koncu teksta. Namig: oznaèbo lahko oznaèite in jo z miško prestavite ali kopirate na želeno mesto v besedilu. Odveène oznaèbe izbrišite.");

$faq[] = array("--","Oblikovanje besedila");
$faq[] = array("Kako oblikujemo krepko, poševno in podèrtano besedilo", "BBCode kode vsebujejo oznaèbe (ukaze), ki omogoèajo hitre spremembe osnovnega sloga v vašem besedilu. To lahko dosežete na naslednje naèine: <ul><li>Da bi del besedila oznaèili krepko,ga zaprete med oznaèbi <b>[b][/b]</b>, npr. <br /><br /><b>[b]</b>Zivijo<b>[/b]</b><br /><br />postane <b>Zivijo</b></li><li>Za podèrtano besedilo uporabite <b>[u][/u]</b>, na primer:<br /><br /><b>[u]</b>Dobro jutro<b>[/u]</b><br /><br />postane <u>Dobro jutro</u></li><li>Za poštevno besedilo uporabite <b>[i][/i]</b>, npr.<br /><br />Tukaj je <b>[i]</b>Èudovito!<b>[/i]</b><br /><br /> in dobite Tukaj je <i>Èudovito!</i></li></ul>");
$faq[] = array("Kako spremenimo barvo in velikost besedila", "Za spremembo barve ali velikosti besedila uporabite naslednje oznaèbe. Vedite, da je odvisno od gledalèevega brskalnika in sistema to, kako bo potem izgledalo: <ul><li>Spremembo barve besedila dosežemo tako, da besedilo ogradimo, damo med oznaki <b>[color=][/color]</b>. Lahko doloèite bodisi znano ime barve (npr. red za rdeèe, blue za modro, yellow za rumeno, itd.) bodisi vstavote heksadecimalni trojèek za barve, npr. #FFFFFF, #000000. Za primer, da bi dobili rdeèe besedilo, lahko uporabite:<br /><br /><b>[color=red]</b>Zivijo!<b>[/color]</b><br /><br />ali<br /><br /><b>[color=#FF0000]</b>Zivijo!<b>[/color]</b><br /><br />, oboje bo dalo <span style=\"color:red\">Zivijo!</span></li><li>Sprememba velikosti besedila dosežemo na preprost naèin z uporabo oznak <b>[size=][/size]</b>. Te oznaèbe so odvisne od predloge, ki jo uporabaljate, toda zahtevani format je številèna oblika, ki predstavlja velikost pisave v pikslih, zaène se pri 1 (je tako drobna, da je ne boste videli) do 29 (zelo velika). Za primer:<br /><br /><b>[size=9]</b>MAJHNO<b>[/size]</b><br /><br />bo obièajno <span style=\"font-size:9px\">MAJHNO</span><br /><br />medtem ko:<br /><br /><b>[size=24]</b>OGROMNO!<b>[/size]</b><br /><br />bo <span style=\"font-size:24px\">OGROMNO!</span></li></ul>");
$faq[] = array("Ali lahko kombiniram oblikovne oznaèbe?", "Ja, seveda lahko; na primer, da bi pritegnili pozornost nekoga, lahko napišete:<br /><br /><b>[size=18][color=red][b]</b>POGLEJ ME!<b>[/b][/color][/size]</b><br /><br />to se prikaže kot <span style=\"color:red;font-size:18px\"><b>POGLEJ ME!</b></span><br /><br />Kljub temu ne priporoèamo, da prikazani deli izgledajo kot to! Vedite, da je odvisno od vas, ki objavljate, da so oznaèbe pravilno zakljuèene. Na primer, tole ni pravilno:<br /><br /><b>[b][u]</b>Tole manjka<b>[/b][/u]</b>");

$faq[] = array("--","Citiranje in prikazovanje nespremenjenega besedila");
$faq[] = array("Citiranje besedila v odgovorih", "Dva naèina sta, da lahko citirate besedilo: z imenom ali brez.<ul><li>Kadar uporabite funkcijo Citiraj za odgovor na sporoèilo na plošèi, upoštevajte, da je besedilo objave, na katero odgovarjate, v besedilnem oknu že ograjeno z <b>[quote=\"\"][/quote]</b> oznaèbama. Ta naèin vam omogoèi, da citirate z imenom osebe ali karkoli želite vnesti kot ime. Na primer, da bi citirali del besedila Gd. Debeljak je napisal, lahko vnesete:<br /><br /><b>[quote=\"Gd. Debeljak\"]</b>Besedilo Gd. Debeljaka bo tukaj<b>[/quote]</b><br /><br />V prikazenem besedilu bo avtomatièno dodano: Gd. Debeljak je napisal/-a: pred citiranim besedilom. Pomnite, da <b>morate</b> dati med narekovaje \"\" ime, ki ga citirate -- to ni samo možnost.</li><li>Drugi naèin omogoèa slepo citiranje neèesa. Ko npr. ponavljate citate istega avtorja. Da bi uporabili ta naèin, ogradite besedilo med <b>[quote][/quote]</b> oznaèbi. Prikazano besedilo bo preprosto dodalo: Citiram: pred samim oznaèenim besedilom.</li></ul>");
$faq[] = array("Prikazovanje kode ali nespremenjenih podatkov", "Èe želite prikazati del kot kodo, pravzaprav karkoli želite ohraniti kot Courier-tip pisave, morate ograditi besedilo med <b>[code][/code]</b> oznaèbi, npr.<br /><br /><b>[code]</b>echo \"To je del kode\";<b>[/code]</b><br /><br />Vse oblikovanje znotraj <b>[code][/code]</b> oznaèb ostane nespremenjeno, ko ga pozneje prikažete.");

$faq[] = array("--","Ustvarjanje seznamov");
$faq[] = array("Ustvarjanje neurejenega seznama", "BBCode kode podpirajo dva tipa seznamov, neurejenega in urejenega. Oba sta v osnovi enaka kot HTML razlièica. Neurejen seznam prikaže vsako toèko na vašem seznamu zaporedno enega za drugim znakom krogca. Da ustvarili neurejen seznam, uporabite <b>[list][/list]</b> in doloèite vsako toèko znotraj seznama z uporabo <b>[*]</b>. Na primer, za seznam vaših najljubših barv, lahko uporabite:<br /><br /><b>[list]</b><br /><b>[*]</b>Rdeèa<br /><b>[*]</b>Modra<br /><b>[*]</b>Rumena<br /><b>[/list]</b><br /><br />Tako boste izoblikovali naslednji seznam:<ul><li>Rdeèa</li><li>Modra</li><li>Rumena</li></ul>");
$faq[] = array("Ustvarjanje urejenega seznama", "Drugi tip seznama, urejen seznam, vam omogoèa nadzor nad prikazom pred vsako toèko. Da bi ustvarili urejen seznam uporabite <b>[list=1][/list]</b> , da bi dobili oštevilèen seznam ali druga možnost <b>[list=a][/list]</b> za alfabetièni (èrkovni) seznam. Kot pri neurjenem seznamo toèke doloèite z uporabo <b>[*]</b>. Na primer:<br /><br /><b>[list=1]</b><br /><b>[*]</b>Pojdi v trgovine<br /><b>[*]</b>Kupi nov raèunalnik<br /><b>[*]</b>Preklinjaj raèunalnik, ko se raztrešèi<br /><b>[/list]</b><br /><br /> bo prikazano kot:<ol type=\"1\"><li>Pojdi v trgovine</li><li>Kupi nov raèunalnik</li><li>Preklinjaj raèunalnik, ko se raztrešèi</li></ol>Medtem ko pri alfabetiènem (èrkovnem) seznamu uporabite:<br /><br /><b>[list=a]</b><br /><b>[*]</b>Prvi možni odgovor<br /><b>[*]</b>Drugi možni odgovor<br /><b>[*]</b>Tretji možni odgovor<br /><b>[/list]</b><br /><br />daje<ol type=\"a\"><li>Prvi možni odgovor</li><li>Drugi možni odgovor</li><li>Tretji možni odgovor</li></ol>");

$faq[] = array("--", "Ustvarjanje povezav");
$faq[] = array("Povezava na neko drugo stran", "phpBB BBCode kode podpirajo številne naèine ustvarjanja URI-jev, Uniform Resource Indicators bolje znanih kot URL-ji.<ul><li>Prva možna uporaba je <b>[url=][/url]</b> oznaèba; karkoli natipkate za znakom = bo prikazano kot vsebina te oznaèbe kot URL. Na primer, povezavo do phpBB.com lahko uporabite za:<br /><br /><b>[url=http://www.phpbb.com/]</b>Obišèi phpBB!<b>[/url]</b><br /><br />To bo ustvarilo naslednjo povezavo, <a href=\"http://www.phpbb.com/\" target=\"_blank\">Obišèi phpBB!</a> Opazili boste, da povezava odpre novo okno, tako da uporabniki lahko nadaljujejo brskanje po forumu, èe želijo.</li><li>Èe želite prikazati samoo URL kot povezavo, lahko preprosto uporabite:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />To bo ustvarilo naslednjo povezavo: <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>Poleg tega phpBB nekaj oblik naziva <i>Magiène Povezave</i>, ki bodo katerokoli sintaktièno pravilno URL v povezavo, ki ji ni treba dodati nobene posebne oznaèbe ali celo zaèetni http://. Na primer, ko natipkate www.phpbb.com v vaše sporoèilo, se avtomatièno oblikuje v <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> ko prikažete sporoèilo.</li><li>Enako se zgodi pri E-poštnjih naslovih; lahko doloèite naslov posebej, kot:<br /><br /><b>[email]</b>no.one@domain.adr<b>[/email]</b><br /><br />, ki prikaže <a href=\"emailto:no.one@domain.adr\">no.one@domain.adr</a> ali samo odtipkate no.one@domain.adr v vaše sporoèilo in avtomatièno se bo preoblikovalo med prikazom.</li></ul>Tako kot vse BBCode oznaèbe lahko ogradite URL-je s katerokoli drugo oznaèbo, kot npr. <b>[img][/img]</b> (Poglej nadaljnji vnos), <b>[b][/b]</b>, ipd. Kot pri oblikovnih oznaèbah, morate tudi tukaj sami zagotoviti pravilno zakljuèevanje odprtih oznaèb. Na primer:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br /> <u>ni</u> pravilno, in lahko vodi do tega, da se vaše sporoèio izbriše, torej bodite pazljivi.");

$faq[] = array("--", "Prikazovanje slik v sporoèilih");
$faq[] = array("Dodajanje slik v objavo", "phpBB BBCode kode imajo vgrajeno oznaèbo za vkljuèevanje slik v  vaše objave, sporoèila. Dve zelo pomembni stvari si zapomnite,kadar uporabljate to oznaèbo: veèina uporanikov ne upošteva omejitve kolièine slik, ki naj bodo prikazane v prispevkih, in drugiè, prikazana slika, ki jo prikazujete na strani, mora biti že nekje na internetu (ne more se nahajati samo v vašem raèunalniku, na primer, razen èe je hkrati to spletni strežnik!). Trenutno ni možno shranjevati slik lokalno s phpBB (vsa dosedanja prièakovanja prelagajo to možnost na naslednjo izdano razlièico phpBB). Za prikaz slike,morate obdati URL tako, da prikaže sliko, z <b>[img][/img]</b> oznaèbama. Na primer:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />Kot smo opozorili pri URL razdelku zgoraj, lahko ogradite sliko z <b>[url][/url]</b> oznaèbo, èe želite, npr.<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />bo izoblikovalo:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"templates/subSilver/images/logo_phpBB_med.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Druge zadeve");
$faq[] = array("Ali lahko dodam svoje lastne oznaèbe?", "Ne, bojimo se, da ne direktno v phpBB 2.0. Išèemo naèin, ki bi omogoèal prilagodljive BBCode oznaèbe pri naslednji veèji razlièici izdaje programa phpBB.");

//
// This ends the BBCode guide entries
//

?>