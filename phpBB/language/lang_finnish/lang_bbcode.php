<?php
/***************************************************************************
 *                         lang_bbcode.php [finnish]
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

//
//	Translation produced by Jorma Aaltonen (bullitt)
//	http://www.pitro.com/
//


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

$faq[] = array("--","Esittely");
$faq[] = array("Mitä on BBCode?", "BBcode on erityinen muoto HTML:stä. Se, voitko käyttää BBCodea viesteissä riippuu ylläpidosta. Lisäksi voit poistaa BBCoden viestikohtaisesti viestin ylläpitoruudulla. BBCode itsessään muistuttaa HTML:ää, tagit suljetaan hakasuluilla [ ja ] eikä &lt; ja &gt; ja se antaa paremmaan mahdollisuuden määritellä mitä ja miten asiat näytetään. Käytettävästä mallisivusta riippuen  voit huomata, että BBCoden käyttö viesteissä on helpompaa hiirellä toimivan liittymän kautta viestialueen yläpuolella viestiruudulla. Tästä huolimatta seuraava opas voi olla hyödyllinen.");

$faq[] = array("--","Tekstin muotoilu");
$faq[] = array("Kuinka tehdään lihavoitu, kursivoitu ja alleviivattu teksti", "BBCode sisältää tagit, joilla voi nopeasti muuttaa tekstisi perustyylin. Tämä saavutetaan seuraavin keinoin: <ul><li>Lihavoidaksesi tekstin sulje se tagein <b>[b][/b]</b>, esim. <br /><br /><b>[b]</b>Tervehdys<b>[/b]</b><br /><br />näkyy <b>Tervehdys</b></li><li>Alleviivaukseen käytät <b>[u][/u]</b>, esimerkiksi:<br /><br /><b>[u]</b>Hyvää huomenta<b>[/u]</b><br /><br />näkyy <u>Hyvää huomenta</u></li><li>Kursivoituun tekstiin käytät <b>[i][/i]</b>, esim.<br /><br />Tämä on <b>[i]</b>Hienoa!<b>[/i]</b><br /><br />Näkyy, Tämä on <i>Hienoa!</i></li></ul>");
$faq[] = array("Kuinka muutetaan tekstin väriä tai kokoa", "Seuraavilla tageilla voit muuttaa tekstin väriä tai kokoa. Muista, että lopputulos riippuu lukijan selaimesta ja järjestelmästä: <ul><li>Muutat tekstin värin käyttämällä ympärillä <b>[color=][/color]</b>. Voit käyttää joko tavallista värin nimeä (esim. red, blue, yellow, jne.) tai vastaavaa heksadesimaaliarvoa, esim. #FFFFFF, #000000. Jos haluat esim. punaista tekstiä voit käyttää:<br /><br /><b>[color=red]</b>Tervehdys!<b>[/color]</b><br /><br />tai<br /><br /><b>[color=#FF0000]</b>Tervehdys!<b>[/color]</b><br /><br />näyttävät molemmat <span style=\"color:red\">Tervehdys!</span></li><li>Tekstin kokoa muutetaan samalla tavalla käyttämällä <b>[size=][/size]</b>. Tämän tagin toiminta riippuu käytössä olevasta mallisivusta, suositeltu tapa on kuitenkin käyttää numeerista arvoa esittämään tekstin koko pikseleinä, aloittaen 1:stä (niin pientä, että sitä ei voi lukea) päättyen 29:ään (erittäin iso). Esimerkiksi:<br /><br /><b>[size=9]</b>PIENI<b>[/size]</b><br /><br />on normaalisti <span style=\"font-size:9px\">PIENI</span><br /><br />kun taas:<br /><br /><b>[size=24]</b>ISO!<b>[/size]</b><br /><br />näkyy <span style=\"font-size:24px\">ISO!</span></li></ul>");
$faq[] = array("Voinko yhdistellä muotoilutageja?", "Tottakai voit. Esim. jos haluat herättää jonkun huomion voit kirjoittaa:<br /><br /><b>[size=18][color=red][b]</b>LUE MINUT!<b>[/b][/color][/size]</b><br /><br />tämä näkyy <span style=\"color:red;font-size:18px\"><b>LUE MINUT!</b></span><br /><br />Emme suosittele, että käytät paljoa näin muotoiltua tekstiä! Muista, että sinä viestin kirjoittana vastaat siitä, että tagit on suljettu kuten pitää. Esimerkiksi seuraava on väärin:<br /><br /><b>[b][u]</b>Tämä on väärin<b>[/b][/u]</b>");

$faq[] = array("--","Lainaaminen ja asettelultaan kiinteän tekstin käyttö");
$faq[] = array("Tekstin lainaaminen vastauksissa", "Tekstiä voi lainata kahdella tavalla, viittauksella ja ilman.<ul><li>Kun käytät Lainaa toimintoa vastatessasi viestiin foorumissa huomaa, että teksti lisätään viesti-ikkunaan suljettuna tageihin <b>[quote=\"\"][/quote]</b> . Tämä menetelmä antaa sinulle mahdollisuuden lainata viitaten henkilöön tai mihin tahansa haluat! Esim. lainataksesi viestiä Herra Virtaselta kirjoitat:<br /><br /><b>[quote=\"Herra Virtanen\"]</b>Herra Virtasen teksti tulisi tähän<b>[/quote]</b><br /><br />Lopputuloksena lisättäisiin automaattisesti, Herra Virtanen kirjoitti: ennen varsinaista tekstiä. Muista, että sinun <b>täytyy</b> lisätä lainausmerkit \"\" lainattavan nimen ympärille, ne ovat pakolliset.</li><li>Toinen tapa sallii sinun lainata jotain sokkona. Tätä varten suljet tekstin tageihin <b>[quote][/quote]</b> . Viestissä näkyy vain, Lainaus: ennen varsinaista tekstiä.</li></ul>");
$faq[] = array("Koodin tai kiinteämittaisen tekstin näyttö", "Jos haluat näyttää pätkän koodia tai jotain, joka vaatii kiinteän asettelun, esim. Courier tyyppinen fontti, sinun tulee ympäröidä teksti tagein <b>[code][/code]</b> , esim.<br /><br /><b>[code]</b>echo \"Tämä on pätkä koodia\";<b>[/code]</b><br /><br />Kaikki muotoilu tagien <b>[code][/code]</b> sisällä on säilytetty kun viestiä katsotaan.");

$faq[] = array("--","Luetteloiden luonti");
$faq[] = array("Järjestämättömän luettelon luonti", "BBCode tukee kahden tyyppisiä luetteloita, järjestämättömiä ja järjestettyjä. Ne ovat pääosin samat kuin vastaavat HTML:ssä. Järjestämättömässä luettelossa jokainen alkio näytetään peräkkäin luettelomerkillä sisennettynä. Järjestämätön lista luodaan käyttämällä <b>[list][/list]</b> ja määrittelemällä jokainen luettelon alkio käyttämällä <b>[*]</b>. Esimerkiksi luettelon lempiväreistäsi voisit tehdä:<br /><br /><b>[list]</b><br /><b>[*]</b>Punainen<br /><b>[*]</b>Sininen<br /><b>[*]</b>Keltainen<br /><b>[/list]</b><br /><br />Tämä luo seuraavanlaisen luettelon:<ul><li>Punainen</li><li>Sininen</li><li>Keltainen</li></ul>");
$faq[] = array("Järjestetyn luettelon luonti", "Toinen luettelotyyppi, järjestetty luettelo, antaa sinulle mahdollisuuden määritellä jokaisen alkion esitysmuoto. Luot järjestetyn luettelon käyttämällä <b>[list=1][/list]</b> numeroituun luetteloon ja vastaavasti <b>[list=a][/list]</b> aakkostettuun luetteloon. Kuten järjestämättömässäkin luettelossa kaikki alkiot määritellään käyttämällä <b>[*]</b>. Esimerkiksi:<br /><br /><b>[list=1]</b><br /><b>[*]</b>Käy kaupassa<br /><b>[*]</b>Osta uusi tietokone<br /><b>[*]</b>Kiroile koneelle kun se kaatuu<br /><b>[/list]</b><br /><br />luo seuraavanlaisen luettelon:<ol type=\"1\"><li>Käy kaupassa</li><li>Osta uusi tietokone</li><li>Kiroile koneelle kun se kaatuu</li></ol>Kun taas aakkoselliseen listaan käyttäisit:<br /><br /><b>[list=a]</b><br /><b>[*]</b>Ensimmäinen vastaus<br /><b>[*]</b>Toinen vastaus<br /><b>[*]</b>Kolmas vastaus<br /><b>[/list]</b><br /><br />joka näyttäisi<ol type=\"a\"><li>Ensimmäinen vastaus</li><li>Toinen vastaus</li><li>Kolmas vastaus</li></ol>");

$faq[] = array("--", "Linkkien luonti");
$faq[] = array("Linkkaaminen toiseen sivustoon", "phpBB BBCode tukee useaa tapaa luoda URI, Uniform Resource Indicators paremmin tunnettu URL.<ul><li>Ensimmäinen niistä käyttää <b>[url=][/url]</b> tagia, mitä tahansa kirjoitat = merkin jälkeen tulkitaan URL:ksi. Esimerkiksi linkki phpBB.com sivuille tehdään:<br /><br /><b>[url=http://www.phpbb.com/]</b>Vieraile phpBB sivuilla!<b>[/url]</b><br /><br />Tämä loisi seuraavanlaisen linkin, <a href=\"http://www.phpbb.com/\" target=\"_blank\">Vieraile phpBB sivuilla!</a> Huomaat, että linkki aukeaa uuteen ikkunaa joten käyttäjä voi jatkaa foorumien selaamista niin halutessaan.</li><li>Jos haluat että URL itse näkyy linkkinä voit käyttää:<br /><br /><b>[url]</b>http://www.phpbb.com/<b>[/url]</b><br /><br />Tämä luo seuraavanlaisen linkin, <a href=\"http://www.phpbb.com/\" target=\"_blank\">http://www.phpbb.com/</a></li><li>Lisäksi phpBB:ssä on ominaisuus jota kutsutaan nimellä <i>Magic Links</i>, tämä muuntaa kaikki muodollisesti oikein kirjoitetut URL:t linkiksi  ilman, että sinun täytyy määrittää tageja tai edes alkua http://. Esim. kirjoittamalla www.phpbb.com viestiisi luodaan automaattisesti <a href=\"http://www.phpbb.com/\" target=\"_blank\">www.phpbb.com</a> kun viestiä katsotaan.</li><li>Sama pätee myös sähköpostiosoitteisiin. Voit joko määritellä osoitteen esim:<br /><br /><b>[email]</b>ei.kukaan@domain.adr<b>[/email]</b><br /><br />joka näyttää <a href=\"emailto:ei.kukaan@domain.adr\">ei.kukaan@domain.adr</a> tai voit kirjoittaa pelkästään ei.kukaan@domain.adr viestiisi ja se muutetaan automaattisesti viestiä katsottaessa.</li></ul>Kuten kaikissa BBCode tageissa voi ympäröidä URL:n millä tahansa muilla tageilla, kuten <b>[img][/img]</b> (katso seuraava kohta), <b>[b][/b]</b>, jne. Kuten muotoilutageissa muista, että sinun tehtäväsi on varmistaa, että tagien alku ja sulkujärjestys ovat oikein. Esim:<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/url][/img]</b><br /><br /> <u>ei</u> ole oikein, mikä voi johtaa viestisi poistamiseen. Joten ole tarkkana.");

$faq[] = array("--", "Kuvien näyttö viesteissä");
$faq[] = array("Kuvan lisääminen viestiin", "phpBB BBCode sisältää tagin kuvien lisäämiseen viestiin. Tämän tagin käyttöön liittyy kaksi erittäin tärkeää asiaa; useat käyttäjät eivät pidä liiallisesta kuvien käytöstä ja toiseksi käytettävien kuvien on jo oltava internetissä saatavilla (kuvatiedosto ei voi olla omalla koneellasi, ellet pidä webserveriä!). Tällä hetkellä ei ole mahdollista säilyttää kuvia paikallisesti phpBB:llä (tämä pyritään muuttamaan seuraavassa phpBB versiossa). Näyttääksesi kuvan sinun täytyy ympäröidä kuvan URL <b>[img][/img]</b> tageilla. Esim:<br /><br /><b>[img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img]</b><br /><br />Kuten yläpuolella URL osiossa selostettiin voit ympäröidä kuvan <b>[url][/url]</b> tageilla halutessasi, esim.<br /><br /><b>[url=http://www.phpbb.com/][img]</b>http://www.phpbb.com/images/phplogo.gif<b>[/img][/url]</b><br /><br />näyttäisi:<br /><br /><a href=\"http://www.phpbb.com/\" target=\"_blank\"><img src=\"templates/subSilver/images/logo_phpBB_med.gif\" border=\"0\" alt=\"\" /></a><br />");

$faq[] = array("--", "Muut aiheet");
$faq[] = array("Voinko lisätä omia tageja?", "Et, ainakaan suoraan phpBB 2.0:ssa. Harkitsemme muunneltavien BBCode tagien lisäämistä seuraavassa isommassa versiopäivityksessä");

//
// This ends the BBCode guide entries
//

?>