<?php
/***************************************************************************
 *                          lang_faq.php [finnish]
 *                            -------------------
 *   begin                : Sunday Dec 24, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: lang_faq.php,v 1.4 2001/12/15 16:42:08 psotfx Exp $
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
// To add an entry to your FAQ simply add a line to this file in this format:
// $faq[] = array("question", "answer");
// If you want to separate a section enter $faq[] = array("--","Block heading goes here if wanted");
// Links will be created automatically
//
// DO NOT forget the ; at the end of the line.
// Do NOT put double quotes (") in your FAQ entries, if you absolutely must then escape them ie. \"something\"
//
// The FAQ items will appear on the FAQ page in the same order they are listed in this file
//
 
  
$faq[] = array("--","Kirjautuminen ja rekisteröityminen");
$faq[] = array("Miksi en voi kirjautua sisään?", "Oletko rekisteröitynyt? Ihan tosi, sinun täytyy rekisteröityä voidaksesi kirjautua sisään. Onko sinulla porttikielto järjestelmään(mikäli on, tästä näytetään viesti sinulle)? Mikäli näin on, sinun täytyy ottaa yhteyttä järjestelmän ylläpitoon tai  webmasteriin selvittääksesi syyn. Jos olet rekisteröitynyt, etkä ole porttikiellossa, etkä silti voi kirjautua sisään tarkista vielä kerran käyttäjätunnus ja salasana. Yleensä ongelma on näissä. Mikäli ei, niin ota yhteyttä järjestelmän ylläpitoon, heillä voi olla väärät asetukset järjestelmässä.");
$faq[] = array("Miksi en voi rekisteröityä?", "Ehkä sinun ei tarvitsekaan. Järjestelmän ylläpitäjä määrittelee täytyykö rekisteröityä voidakseen kirjoittaa viestejä. Rekisteröinti antaa kuitenkin käyttöösi lisäominaisuuksa, kuten määriteltävät avatar kuvat, yksityisviestit, sähköpostin lähetys muille käyttäjille, käyttäjäryhmiin liittyminen, jne. Rekisteröityminen vie vain hetken ja se on suositeltavaa.");
$faq[] = array("Miksi kirjaudun ulos automaattisesti?", "Jos et laita rastia <i>Kirjaa minut sisään automaattisesti</i> laatikkoon kirjautuessasi sisään, pysyt kirjautuneena vain ennalta säädetyn ajan. Näin estetään käyttäjätunnuksesi luvaton käyttö. Pysyäksesi kirjautuneena rastita laatikko kirjautumisen yhteydessä. Tämä ei ole suositeltavaa jos samaa yhteyttä/tietokonetta käyttävät muutkin käyttäjät esim. kirjasto, internet kahvila, oppilaitoksen koneet, jne.");
$faq[] = array("Kuinka voin estää käyttäjätunnukseni näkymisen sen hetkisten käyttäjien listalla?", "Käyttäjätiedoissasi on optio <i>Piilota online status</i>, jos käytät tässä <i>kyllä</i> näyt vain järjestelmän ylläpitäjälle ja itsellesi. Sinut lasketaan piilossa olevaksi käyttäjäksi.");
$faq[] = array("Unohdin salasanani!", "Ei paniikkia! Vaikka salasanaasi ei voi selvittää, se voidaan resetoida. Tehdäksesi tämän siirry kirjautumissivulle ja klikkaa <u>Unohdin salasanani</u>, seuraa ohjeita ja ongelma on tuota pikaa hoidettu.");
$faq[] = array("Rekisteröidyin mutta en voi kirjautua!", "Tarkista ensin, että käytät oikeaa käyttäjätunnusta ja salasanaa. Jos ne ovat kunnossa, niin on kaksi todennäköistä vaihtoehtoa. Jos COPPA tuki on päällä ja klikkasit<u>Olen alle 18 vuotta</u> linkkiä rekisteröityessäsi joudut seuraamaan saamiasi ohjeita. Jos kyse ei ole  tästä, niin tarvitseeko käyttäjätunnuksesi aktivoinnin? Jotkin järjestlmät vaativat jokaisen uuden käyttäjän erillisen aktivoinnin joko käyttäjän toimesta tai ylläpitäjän toimesta. Rekisteröityessäsi sait tiedon siitä vaaditaanko aktivointi. Mikäli sait sähköpostia, noudata sen ohjeita. Jos et saanut sähköpostia, oletko varma, että annoit oikean sähköpostiosoitteen? Yksi syy, miksi erillistä aktivointia käytetään on  <i>törkeiden</i> käyttäjien karsiminen estämällä anonyymi käyttö. Jos olet varma, että antamasi sähkööostiosoite oli oikein, ota yhteys järjestelmän ylläpitoon.");
$faq[] = array("Rekisteröidyin kauan sitten mutta en pääse enää kirjautumaan?!", "Yleisimmät syyt tähän ovat; annoit väärän käyttäjätunnuksen tai salasanan (tarkista sähköpostistasi tiedot jotka sait kun rekisteröidyit) tai ylläpito on poistanut käyttäjätunnuksesi jostain syystä. Jälkimmäisessä tapauksessa et ehkä ole kirjoittanut mitään? On yleistä, että järjestelmät poistavat käyttäjiä, jotka eivät ole kirjoittaneet mitään. Näin pidetään tietokantaa siistimpänä. Yritä rekisteröityä uudestaan ja ota osaa keskusteluihin.");


$faq[] = array("--","Käyttäjätiedot ja asetukset");
$faq[] = array("Kuinka muutan asetuksiani?", "Kaikki asetuksesi (jos olet rekisteröitynyt) ovat tietokannassa. Muuttaaksesi tietojasi klikkaa  <u>Käyttäjätiedot</u> linkkiä (yleensä sivun yläreunassa mutta ei välttämättä aina). Näin pääset muuttamaan kaikkia asetuksiasi.");
$faq[] = array("Ajat eivät ole oikein!", "Ajat ovat melko varmasti oikein, saatat kuitenkin nähdä ajat eri aikavyöhykkeeltä kuin missä olet. Mikäli kyse on tästä täytyy sinun muuttaa käyttäjätiedoissasi aikavyöhykettä vastaamaan oikeaa aluetta. Huomaa, että aikavyöhykkeen muuttaminen, kuten useimmat asetukset, on mahdollista vain rekisteröidyille käyttäjille. Joten jos et ole rekisteröitynyt, nyt on hyvä hetki rekisteröityä.");
$faq[] = array("Muutin aikavyöhykettä ja ajat ovat silti väärin!", "Jos olet varma, että aikavyöhyke on oikein ja aika on silti eri, niin todennäköisin vaihtoehto on kesäajan käyttö. Järjestelmää ei ole suunniteltu ottamaan huomioon talvi- ja kesäajan vaihteluita joten kesällä aika voi olla tunnin eri kuin normaali paikallinen aika.");
$faq[] = array("Kieleni ei ole listalla!", "Todennäköisesti ylläpito ei ole asentanut kielivaihtoehtoasi tai kukaan ei ole tehnyt tarvittavaa käännöstä. Kysy ylläpidolta voivatko he asentaa tarvittavan kielipaketin. Jos sellaista ei ole, voit vapaasti luoda uuden käännöksen. Lisätietoja löytyy phpBB ryhmän web sivuilta (Linkki sivujen alareunassa)");
$faq[] = array("Kuinka saan kuvan käyttäjätunnukseni alle?", "Käyttäjätunnuksen alla voi olla kaksi kuvaa katsottaessa viestejä. Ensimmäinen on kuva joka on liitetty titteliisi, yleensä rivi tähtiä kuvaamassa kuinka monta viestiä olet lähettänyt tai mikä on statuksesi järjestelmässä. Tämän alla voi olla isompi kuva, avatar, tämä on yleensä henkilökohtainen jokaisella käyttäjällä. Järjestelmän ylläpitäjästä riippuu, ovatko avatarit sallittuja ja kuinka avatarit tuodaan nähtäväksi. Jos et voi käyttää avatarta, niin kyse on ylläpitäjän ratkaisusta. Voit kysyä heiltä syytä tähän (Syyt ovat varmasti hyvät!)");
$faq[] = array("Kuinka muutan titteliäni?", "Yleensä et voi muuttaa mitään titteliä (titteli näkyy käyttäjätunnuksesi alapuolella viesteissä ja käyttäjätiedoissasi käytetystä tyylistä riippuen). Useimmat järjestelmät rankkaavat käyttäjät lähetettyjen viestien määrän mukaan tai osoittamaan tiettyä käyttäjätyyppiä, esim. moderaattoreilla ja ylläpitäjillä voi olla omat erikoistittelit. Ole hyvä äläkä turhaan rasita järjestelmää kirjoittamalla tarpeettomia viestejä vain korottaaksesi titteliäsi, todennäköisesti tällöin ylläpito tai moderaattori pienentää viestiesi määrää.");
$faq[] = array("Kun klikkaan käyttäjän sähköpostilinkkiä, pyydetään kirjautumaan sisään?", "Valitettavasti vain rekisteröidyt käyttäjät voivat lähettää sähköpostia sisäänrakennetulla sähköpostilomakkeella (Jos ylläpito on sallinut tämän ominaisuuden). Näin estetään asiattomien käyttäjien anonyymi järjestelmän hyväksikäyttö.");


$faq[] = array("--","Viestien kirjoitukseen liittyvät aiheet");
$faq[] = array("Kuinka luon aiheen foorumiin?", "Helppoa, klikkaa vastaavaa painiketta joko foorumin tai aiheen ikkunassa. Voi olla, että sinun täytyy rekisteröityä ennen kuin voit kirjoittaa viestin. Toimintamahdollisuutesi on lueteltu foorumin ja aiheen sivuilla (Eli <i>Voit luoda uusia aiheita, Voit äänestää äänestyksissä, jne.<i> lista)");
$faq[] = array("Kuinka muokkaan tai poistan viestin?", "Jollet ole ylläpitäjä tai foorumin moderaattori voi muokata ja poistaa vain omia viestejäsi. Voit muokata viestiä (joskus vain rajoitetun ajan luonnin jälkeen) klikkaamalla <i>muokkaa</i> painiketta kyseisellä viestillä.  Jos joku on jo vastannut viestiin näet pienen palan tekstiä viestin alapuolella kun palaat aiheeseen, näin listataan kuinka monta kertaa olet muokannut viestiä. Tämä ei näy jos kukaan ei ole vastannut, se ei näy myöskään jos moderaattori tai ylläpito on muokannut viestiä (heidän tulisi jättää tieto, mitä he ovat muokanneet ja miksi). Huomaa, että tavalliset käyttäjät eivät voi poistaa viestiä jos siihen on vastattu.");
$faq[] = array("Kuinka lisään allekirjoituksen viestiini?", "Voidaksesi lisätä allekirjoituksen, on sinun ensin luotava sellainen. Tämä tapahtuu käyttäjätietojesi kautta. Luotuasi allekirjoituksen voit laittaa rastin <i>Lisää allekirjoitus</i> laatikkoon postituslomakkeella lisätäksesi allekirjoituksen. Voit myös määritellä allekirjoituksen tulevan oletusarvoisesti kaikkiin viesteihisi laittamalla raksin asianomaiseen valintalaatikkoon käyttäjätiedoissasi (voit silti estää allekirjoituksen yksittäisistä viesteistä poistamalla raksin lisää allekirjoitus laatikosta postituslomakkeella)");
$faq[] = array("Kuinka luon äänestyksen?", "Äänestyksen luonti on helppoa. Kun luot uuden aiheen (tai muokkaat aiheen ensimmäistä viestiä, jos sinulla on oikeus) sinun pitäisi nähdä <i>Lisää äänestys</i> lomake postituslomakkeen alapuolella (jos tätä ei tule näkyviin, sinulla ei todennäköisesti ole oikeutta luoda äänestyksiä). Anna äänestykselle otsikko ja vähintään kaksi vaihtoehtoa (aseta vaihtoehto klikkaamlla <i>Lisää vaihtoehto</i> painiketta. Voit myös asettaa aikarajoituksen äänestykselle, 0 on ikuinen äänestys. Järjestelmän ylläpitäjä on määritellyt äänestyksen vaihtoehdoille ylärajan.");
$faq[] = array("Kuinka muokkaan tai poistan äänestyksen?", "Kuten viesteissäkin, äänestyksiä voi muokata vain alkuperäinen postittaja, moderaattori tai järjestelmän ylläpitäjä. Muokataksesi äänestystä klikkaa aiheen ensimmäistä viestiä (äänestys on aina liitetty tähän viestiin). Jos kukaan ei ole vielä äänestänyt voi käyttäjä poistaa äänestyksen tai muokata äänestyksen vaihtoehtoja. Jos ääniä on jo annettu vain moderaattori tai järjestelmän ylläpitäjä voivat muokata tai poistaa äänestyksen. Näin estetään käyttäjiä manipuloimasta äänestyksiä vaihtamalla vaihtoehtoja kesken äänestyksen");
$faq[] = array("Miksi en pääse foorumiin?", "Jotkin foorumit voivat olla rajoitettuja tietyille käyttäjille tai käyttäjäryhmille. Voidaksesi nähdä, lukea, kirjoittaa jne. saatat tarvita erikoisluvan. Vain foorumin moderaattori ja järjestelmän ylläpitäjä voivat myöntää luvan, ota yhteys heihin.");
$faq[] = array("Miksi en voi äänestää?", "Vain rekisteröidyt käyttäjä voivat äänestää (näin estetään tulosten vedätys). Jos olet rekisteröitynyt, etkä silti voi äänestää sinulla todennäköisesti ei ole riittäviä oikeuksia.");


$faq[] = array("--","Muotoilut ja aihetyypit");
$faq[] = array("Mitä on BBCode?", "BBCode erityinen variaatio HTML:stä. Ylläpito on määritellyt voitko käyttää BBCode:a (voit myös itse estää sen viestikohtaisesti viestilomakkeella). BBCode muistuttaa HTML:ää. Tagit ympäröidään hakasuluilla [ ja ] ja tyyli antaa paremman mahdollisuuden määrittää miten jokin asia esitetään. Saadaksesi lisätietoa BBCode:sta katso opasta, johon pääsee viestilomakkeelta.");
$faq[] = array("Voinko käyttää HTML:ää?", "Tämä riippuu siitä salliiko ylläpito tämän. Ylläpito määrää tästä täysin. Jos sinun sallitaan käyttää sitä, huomaat todennäköisesti vain osan tageista toimvan. Tämä johtuu <i>turvallisuussyistä</i>. Näin estetään käyttäjiä sotkemasta järjestelmää käyttämällä tageja, jotka tuhoavat asettelun tai aiheuttavat muita ongelmia. Jos HTML on sallittua, voit estää sen käytön viestikohtaisesti viestilomakkeella.");
$faq[] = array("Mitä ovat hymiöt?", "Hymiöt tai  Emoticonit ovat pieniä kuvakkeita joilla voidaan viestittää tuntemuksia käyttämällä lyhyttä koodia, esim. :) tarkoittaa iloista, :( tarkoittaa surullista. Koko listan emoticoneista näet viestilomakkeen kautta. Älä ylikäytä hymiöitä, ne voivat muuttaa viestisi huonosti luettavaksi ja moderaattori voi muuttaa niitä tai poistaa kokonaan.");
$faq[] = array("Voinko lisätä kuvia viesteihin?", "Kuvia voidaan lisätä viesteihin. Tällä hetkellä ei kuitenkaan ole menetelmää siirtää kuvia suoraan järjestelmään. Siksi joudut käyttämään linkkiä kuvatiedostoon yleisesti käytössä olevan serverin kautta, esim. http://www.jokin-outo-paikka.net/omakuva.gif. Et voi linkittää kuvia, jotka ovat omalla PC:lläsi (ellei se ole julkisesti käytössä oleva serveri) eikä kuva voi olla kirjautumismekanismin takana, esim. hotmail tai yahoo sähköpostit, salasanalla suojatut sivut, jne. Näyttääksesi kuvan käytä joko BBCodea [img] tagi tai vastaava HTML tagi (jos sallittu).");
$faq[] = array("Mitä ovat ilmoitukset?", "Ilmoitus sisältää usein tärkeää tietoa ja sinun tulisi lukea ne niin pian kuin mahdollista. Ilmoitukset näkyvät asianomaisten foorumien joka sivun yläreunassa. Se voitko luoda ilmoituksia riippuu tarvittavista oikeuksista, jotka määrittelee ylläpito.");
$faq[] = array("Mitä ovat tiedotukset?", "Tiedotukset näkyvät Ilmoitusten alapuolella käytössä olevalla foorumilla ja vain foorumin ensimmäisellä sivulla. Ne ovat usein erittäin tärkeitä, joten sinun tulisi lukea ne aina kun mahdollista. Kuten ilmoituksissakin ylläpito määrittelee millaisilla oikeuksilla voidaan luoda tiedotteita foorumikohtaisesti.");
$faq[] = array("Mitä ovat lukitut aiheet?", "Aiheen lukitsee joko moderaattori tai ylläpito. Lukittuun aiheeseen ei voi vastata ja mahdollinen äänestys on automaattisesti päättynyt. Aiheita voidaan lukita monista eri syistä.");


$faq[] = array("--","Käyttäjätasot ja ryhmät");
$faq[] = array("Keitä ovat ylläpitäjät?", "Ylläpitäjät ovat henkilöitä, joilla on korkeimmat käyttöoikeudet koko järjestelmässä. Nämä käyttäjät voivat kontrolloida kaikkia järjestelmän tapahtumia, mukaan lukien oikeuksien asettaminen, porttikiellot, käyttäjäryhmien ja moderaattorien luonti, jne. Heillä on myös täydet moderointioikeudet kaikissa foorumeissa.");
$faq[] = array("Keitä ovat moderaattorit?", "Moderaattorit ovat henkilöitä (tai ryhmä henkilöitä) joiden tehtävänä on valvoa foorumeita päivittäin. Heillä on oikeus muokata tai poistaa viestejä, lukita, vapauttaa, siirtää, poistaa ja jakaa aiheita siinä foorumissa, jossa he ovat moderaattorina. Yleensä moderaattorin tehtävänä on hillitä kirjoittajia jotka  <i>eivät pysy aiheessa<i> tai käyttävät paheksuttavaa materiaalia.");
$faq[] = array("Mitä ovat käyttäjäryhmät?", "Käyttäjäryhmillä ylläpito järjestelee käyttäjiä. Jokainen käyttäjä voi kuulua useaan ryhmään (tämä poikkeaa useimmista muista vastaavista järjestelmistä) ja jokaiselle ryhmälle voidaan myöntää omat oikeutensa. Näin ylläpidon on helppo määrittää usea henkilö yhdellä kertaa foorumin moderaattoriksi tai antaa heille oikeus päästä yksityiseen foorumiin jne.");
$faq[] = array("Kuinka liityn käyttäjäryhmään?", "Liittyäksesi käyttäjäryhmään klikkaa käyttäjäryhmän linkkiä sivun yläreunassa (paikka riippuvainen käytettävästä tyylistä), sivulla voit tarkastella kaikkia käyttäjäryhmiä. Kaikkiin ryhmiin ei ole <i>vapaata pääsyä</i>, jotkin ryhmät ovat suljettuja ja osa myös piilotettuja. Jos ryhmä on avoin, voit pyytää ryhmän jäsenyyttä klikkaamalla vastaavaa painiketta. Käyttäjäryhmän moderaattorin tulee hyväksyä hakemuksesi. Hän saattaa kysyä, miksi haluat liittyä ryhmään. Ole hyvä äläkä häiriköi moderaattoria jos hän ei hyväksy hakemustasi. Heillä on syynsä.");
$faq[] = array("Kuinka pääsen käyttäjäryhmän moderaattoriksi?", "Käyttäjäryhmän luo alunperin ylläpito, joka myös määrää ryhmän moderaattorin. Jos olet halukas perustamaan käyttäjäryhmän ota ensin yhteyttä ylläpitoon. Yritä jättää heille yksityisviesti.");


$faq[] = array("--","Yksityiset viestit");
$faq[] = array("En voi lähettää yksityisiä viestejä!", "Tähän on kolme syytä; et ole rekisteröitynyt/kirjautunut sisään, ylläpito on poistanut yksityisviestien käytön koko järjestelmästä tai ylläpito on estänyt sinua lähettämästä viestejä. Jos kyse on jälkimmäisestä tapauksesta, ota yhteys ylläpitoon selvittääksesi syy.");
$faq[] = array("Saan jatkuvasti ei toivottuja yksityisiä viestejä!", "Tulevaisuudessa tulemme lisäämään estolistan yksityisviesteihin. Tällä hetkellä jos saat ei toivottuja viestejä, ilmoita järjestelmän ylläpidolle, he voivat estää käyttäjää lähettämästä yksityisviestejä.");
$faq[] = array("Olen saanut roskapostia tai halventavaa sähköpostia joltakin tästä järjestelmästä!", "Olemme pahoillame jos näin on käynyt. Järjestelmän sähköpostilomake sisältää varotoimia, joilla voidaan yrittää jäljittää viestin lähettäjä. Sinun tulee lähettää sähköposti ylläpidolle sisällyttäen täydellinen kopio saamastasi sähköpostista. Erityisen tärkeää on sisällyttää sähköpostin ominaisuuksista otsikkotiedot (näissä on yksityiskohtaiset tiedot lähettäjästä). Ylläpito voi tämän jälkeen ryhtyä toimenpiteisiin.");

//
// These entries should remain in all languages and for all modifications
//
$faq[] = array("--","phpBB 2 aiheet");
$faq[] = array("Kuka on tehnyt tämän järjestelmän (ilmoitustaulun)?", "Tämä ohjelmiston (muokkaamattomassa muodossa) on tuottanut ja julkaissut ja copyrightin hallitsija on <a href=\"http://www.phpbb.com/\" target=\"_blank\">phpBB Group</a>. Se on jaettu GNU General Public Licence ehdoin ja on vapaasti levitettävissä");
$faq[] = array("Miksi ominaisuutta x ei ole?", "Tämän ohjelmiston on tuottanut ja lisensoinut phpBB Group. Jos mielestäsi ominaisuutta tarvitaan, käy phpbb.com web sivuilla ja tarkista mitä mieltä phpBB Group on. Ole hyvä äläkä postita pyyntöjä foorumeihin osoitteessa phpbb.com. Ryhmä käyttää sourceforge sivustoja uusien ominaisuuksien tekoon. Ole hyvä ja lue läpi foorumit nähdäksesi olemmeko jo ottaneet kantaa ominaisuuteen ja noudata sitten sivuilla annettuja ohjeita.");
$faq[] = array("Keneen otan yhteyttä loukkaavista ja/tai laittomista asioista tässä järjestelmässä?", "Sinun tulee ottaa yhteyttä järjestelmän ylläpitoon. Jos et saa selville kuka tämä on, tulee sinun ottaa yhteyttä jonkin foorumin moderaattoriin ja kysyä häneltä kenen puoleen tulee kääntyä. Jos et vieläkään saa vastausta sinun tulee ottaa yhteyttä web osoitteen (domainin) omistajaan (tämä selviää whois kyselyllä) tai jos kyseessä on vapaa järjestelmä (esim. yahoo, free.fr, f2s.com, jne.), hallintoon tai turva tms. osastoon ko. palvelussa. Pyydämme ottamaan huomioon ettei phpBB Group voi millään lailla kontrolloida, eikä sitä voida mitenkään pitää vastuullisena siitä kuinka, missä ja kuka tätä järjestelmää käyttää. On täysin turhaa ottaa yhteyttä phpBB Group:iin missään lakiasioissa (vastuu yms.), asioissa jotka eivät suoraan liity phpbb.com web sivustoon tai itse phpBB ohjelmaan sellaisenaan. Jos lähetät sähköpostia phpBB Group:lle mistään kolmannen osapuolen ohjelmiston käytöstä, voit odottaa lyhyttä vastausta tai jäädä ilman vastausta kokonaan.");

//
// This ends the FAQ entries
//

?>