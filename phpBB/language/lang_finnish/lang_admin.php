<?php

/***************************************************************************
 *                            lang_admin.php [Finnish]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id$
 *
 ****************************************************************************/

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
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "Yleinen ylläpito";
$lang['Users'] = "Käyttäjien ylläpito";
$lang['Groups'] = "Ryhmien ylläpito";
$lang['Forums'] = "Foorumien ylläpito";
$lang['Styles'] = "Tyylien ylläpito";

$lang['Configuration'] = "Konfigurointi";
$lang['Permissions'] = "Suojaukset";
$lang['Manage'] = "Hallinta";
$lang['Disallow'] = "Kielletyt nimet";
$lang['Prune'] = "Siivous";
$lang['Mass_Email'] = "Ryhmäsähköposti";
$lang['Ranks'] = "Tittelit";
$lang['Smilies'] = "Hymiöt";
$lang['Ban_Management'] = "Kieltojen hallinta";
$lang['Word_Censor'] = "Sanasensuuri";
$lang['Export'] = "Vie";
$lang['Create_new'] = "Luo";
$lang['Add_new'] = "Lisää";
$lang['Backup_DB'] = "Tietokannan varmuuskopiointi";
$lang['Restore_DB'] = "Varmuuskopion palautus";


//
// Index
//
$lang['Admin'] = "Ylläpito";
$lang['Not_admin'] = "Sinulla ei ole oikeutta hallinnoida tätä sivustoa";
$lang['Welcome_phpBB'] = "Tervetuloa phpBB:hen";
$lang['Admin_intro'] = "Kiitoksia, että valitsit phpBB:n foorumiratkaisuksi. Tällä ruudulla näet pikaisen silmäyksen foorumien erilaisista tilastoinneista. Pääset takaisin tälle sivulle klikkaamalla <u>Hallinnan päävalikko</u> linkkiä vasemmalla reunalla. Palataksesi foorumien päävalikkoon, klikkaa phpBB logoa, joka on myöskin vasemmalla reunalla. Muut linkit tämän sivun vasemmassa reunassa päästävät sinut ylläpitämään jokaista osa-aluetta foorumien toiminnassa. Jokaisella sivulla on ohjeet työkalujen käyttöön.";
$lang['Main_index'] = "Foorumien päävalikko";
$lang['Forum_stats'] = "Foorumien tilastointi";
$lang['Admin_Index'] = "Hallinnan päävalikko";
$lang['Preview_forum'] = "Esikatsele Foorumi";

$lang['Click_return_admin_index'] = "Klikkaa %stästä%s palataksesi hallinnan päävalikkoon";

$lang['Statistic'] = "Tilastointi";
$lang['Value'] = "Arvo";
$lang['Number_posts'] = "Viestien lukumäärä";
$lang['Posts_per_day'] = "Viestejä päivässä";
$lang['Number_topics'] = "Aiheiden lukumäärä";
$lang['Topics_per_day'] = "Aiheita päivässä";
$lang['Number_users'] = "Käyttäjien lukumäärä";
$lang['Users_per_day'] = "Käyttäjiä päivässä";
$lang['Board_started'] = "Sivusto aloitti";
$lang['Avatar_dir_size'] = "Avatar hakemiston koko";
$lang['Database_size'] = "Tietokannan koko";
$lang['Gzip_compression'] ="Gzip pakkaus";
$lang['Not_available'] = "Ei käytettävissä";

$lang['ON'] = "ON"; // This is for GZip compression
$lang['OFF'] = "OFF"; 


//
// DB Utils
//
$lang['Database_Utilities'] = "Tietokantatyökalut";

$lang['Restore'] = "Palauta";
$lang['Backup'] = "Varmista";
$lang['Restore_explain'] = "Tällä toimenpiteellä suoritetaan phpBB tietokannan täydellinen palautus. Jos palvelimesi tukee, voit  siirtää gzip pakatun tekstitiedoston ja se puretaan automaattisesti. <b>VAROITUS</b> Kaikki olemassa oleva tieto korvataan. Palautus voi kestää pitkään, älä poistu tältä sivulta ennen kuin toiminto on valmis.";
$lang['Backup_explain'] = "Tässä voit varmuuskopioida kaiken phpBB liittyvän tiedon. Jos sinulla on ylimääräisiä lisättyjä tauluja samassa tietokannassa phpBB:n kanssa ja haluaisit kopioida myös ne, ole hyvä ja anna taulujen nimet pilkuilla eroteltuna Lisäkentät teksti-ikkunaan alapuolella. Jos palvelimesi tukee gzip pakkausta voit pakata tiedostot pienempään tilaan ennen siirtoa.";

$lang['Backup_options'] = "Varmuuskopion valinnat";
$lang['Start_backup'] = "Aloita varmuuskopiointi";
$lang['Full_backup'] = "Täysi varmuuskopio";
$lang['Structure_backup'] = "Vain rakenteen varmuuskopio";
$lang['Data_backup'] = "Vain tietojen varmuuskopio";
$lang['Additional_tables'] = "Lisäkentät";
$lang['Gzip_compress'] = "Gzip pakattu tiedosto";
$lang['Select_file'] = "Valitse tiedosto";
$lang['Start_Restore'] = "Aloita palautus";

$lang['Restore_success'] = "Tietokanta on onnistuneesti palautettu.<br /><br />Foorumisi on jälleen siinä tilassa, jossa se oli kun varmuuskopio otettiin.";
$lang['Backup_download'] = "Tiedoston siirto alkaa hetken kuluttua, ole hyvä ja odota";
$lang['Backups_not_supported'] = "Valitettavasti tietokantajärjestelmäsi ei tällä hetkellä tue varmuuskopiointia";

$lang['Restore_Error_uploading'] = "Virhe siirrettäessä varmuuskopiotiedostoa";
$lang['Restore_Error_filename'] = "Tiedoston nimeämisongelma, ole hyvä ja yritä toista nimeä";
$lang['Restore_Error_decompress'] = "Gzip pakatun tiedoston purku ei onnistu, ole hyvä ja siirrä pakkaamaton tiedosto";
$lang['Restore_Error_no_file'] = "Tiedostoa ei siirretty";


//
// Auth pages
//
$lang['Select_a_User'] = "Valitse käyttäjä";
$lang['Select_a_Group'] = "Valitse ryhmä";
$lang['Select_a_Forum'] = "Valitse foorumi";
$lang['Auth_Control_User'] = "Käyttäjän oikeuksien määrittely"; 
$lang['Auth_Control_Group'] = "Ryhmän oikeuksien määrittely"; 
$lang['Auth_Control_Forum'] = "Foorumin oikeuksien määrittely"; 
$lang['Look_up_User'] = "Näytä käyttäjä"; 
$lang['Look_up_Group'] = "Näytä ryhmä"; 
$lang['Look_up_Forum'] = "Näytä foorumi"; 

$lang['Group_auth_explain'] = "Tässä voit muuttaa oikeuksia ja moderaattoristatusta jokaiselle käyttäjäryhmälle. Älä unohda muuttaessasi ryhmän oikeuksia, että jokin käyttäjä voi oikeuksiensa perusteella silti päästä foorumiin jne. Saat tällaisessa tilanteessa varoituksen ko. mahdollisuudesta.";
$lang['User_auth_explain'] = "Tässä voit muuttaa oikeuksia ja moderaattoristatusta jokaiselle käyttäjälle. Älä unohda muuttaessasi käyttäjän oikeuksia, että ryhmän oikeudet saattavat silti sallia käyttäjän päästä foorumiin jne. Saat tällaisessa tilanteessa varoituksen ko. mahdollisuudesta.";
$lang['Forum_auth_explain'] = "Tässä voi muuttaa ylläpito-oikeuksia kaikille foorumeille. Sinulla on sekä yksinkertainen, että yksityiskohtaisempi mahdollisuus. Yksityiskohtaisempi antaa enemmän mahdollisuuksia määritellä foorumin toimintaa. Muista, että foorumin oikeustason muuttaminen vaikuttaa siihen ketkä käyttäjät voivat tehdä tiettyjä toimenpiteitä niissä.";

$lang['Simple_mode'] = "Yksinkertainen";
$lang['Advanced_mode'] = "Yksityiskohtainen";
$lang['Moderator_status'] = "Moderaattori status";

$lang['Allowed_Access'] = "Käyttö sallittu";
$lang['Disallowed_Access'] = "Käyttö estetty";
$lang['Is_Moderator'] = "On moderaattori";
$lang['Not_Moderator'] = "Ei ole moderaattori";

$lang['Conflict_warning'] = "Varoitus oikeustasojen ristiriidasta";
$lang['Conflict_access_userauth'] = "Tällä käyttäjällä on yhä oikeus tähän foorumiin ryhmänsä kautta. Voit muuttaa ryhmän oikeuksia tai poistaa käyttäjän/ryhmän estääksesi heiltä pääsyn. Ryhmän oikeudet (ja foorumit joihin vaikuttavat) on mainittu alapuolella.";
$lang['Conflict_mod_userauth'] = "Tällä käyttäjällä on yhä moderaattorin oikeudet tähän foorumiin ryhmänsä kautta. VOit muuttaa ryhmän oikeuksia tai poistaa käyttäjän/ryhmän estääksesi heiltä moderaattorioikeudet. Ryhmän oikeudet (ja foorumit joihin vaikuttavat) on mainittu alapuolella.";

$lang['Conflict_access_groupauth'] = "Seuraavalla käyttäjällä (käyttäjillä) on yhä oikeus tähän foorumiin käyttäjäoikeuksien kautta. VOit muuttaa käyttäjän oikeuksia estääksesi häneltä pääsyn foorumiin. Käyttäjän oikeudet (ja foorumit joihin vaikuttavat) on mainittu alapuolella.";
$lang['Conflict_mod_groupauth'] = "Seuraavalla käyttäjällä (käyttäjillä) on yhä moderaattorin oikeudet tähän foorumiin käyttäjäoikeuksien kautta. VOit muuttaa käyttäjän oikeuksia estääksesi häneltä moderaattorioikeudet foorumiin. Käyttäjän oikeudet (ja foorumit joihin vaikuttavat) on mainittu alapuolella.";

$lang['Public'] = "Yleinen";
$lang['Private'] = "Yksityinen";
$lang['Registered'] = "Rekisteröity";
$lang['Administrators'] = "Ylläpitäjät";
$lang['Hidden'] = "Piilotettu";

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = "KAIKKI";
$lang['Forum_REG'] = "REKIST.";
$lang['Forum_PRIVATE'] = "YKSITYINEN";
$lang['Forum_MOD'] = "MODER.";
$lang['Forum_ADMIN'] = "YLLÄPITO";

$lang['View'] = "Näytä";
$lang['Read'] = "Lue";
$lang['Post'] = "Kirjoita";
$lang['Reply'] = "Vastaa";
$lang['Edit'] = "Muokkaa";
$lang['Delete'] = "Poista";
$lang['Sticky'] = "Tiedote";
$lang['Announce'] = "Ilmoitus"; 
$lang['Vote'] = "Äänestä";
$lang['Pollcreate'] = "Luo äänestys";

$lang['Permissions'] = "Oikeudet";
$lang['Simple_Permission'] = "Yksinkertainen lupa";

$lang['User_Level'] = "Käyttäjätaso"; 
$lang['Auth_User'] = "Käyttäjä";
$lang['Auth_Admin'] = "Ylläpitäjä";
$lang['Group_memberships'] = "Käyttäjäryhmien jäsenyydet";
$lang['Usergroup_members'] = "Tällä ryhmällä on seuraavat jäsenet";

$lang['Forum_auth_updated'] = "Foorumin oikeudet päivitetty";
$lang['User_auth_updated'] = "Käyttäjäoikeudet päivitetty";
$lang['Group_auth_updated'] = "Ryhmän oikeudet päivitetty";

$lang['Auth_updated'] = "Oikeudet on päivitetty";
$lang['Click_return_userauth'] = "Klikkaa %stästä%s palataksesi käyttäjäoikeuksiin";
$lang['Click_return_groupauth'] = "Klikkaa %stästä%s palataksesi ryhmäoikeuksiin";
$lang['Click_return_forumauth'] = "Klikkaa %stästä%s palataksesi foorumin oikeuksiin";


//
// Banning
//
$lang['Ban_control'] = "Porttikieltojen hallinnointi";
$lang['Ban_explain'] = "Täällä voit ylläpitää porttikieltoja. Voit antaa porttikiellon yksittäiselle käyttäjälle ja/tai ketjulle IP osoitteita tai koneita. Näillä toimilla estetään käyttäjän pääsy edes foorumien pääsivulle. Estääksesi käyttäjää rekisteröitymästä toisella nimellä voit määritellä porttikiellon myös sähköpostiosoitteelle. Huomaa kuitenkin, että pelkkä sähköpostiosoitteen porttikielto ei estä käyttäjää kirjautumasta tai kirjoittamasta foorumeihin, tämä estetään käyttämällä jompaa kumpaa kahdesta ensimmäisestä tavasta.";
$lang['Ban_explain_warn'] = "Huomaa, että antamalla IP osoitteiden sarjan, kaikki alku- ja loppuosoitteen välillä olevat IP osoitteet asetetaan porttikieltoon. Osoitteiden lukumäärää tietokannassa pyritään vähentämään käyttämällä jokerimerkkejä automaattisesti missä vain mahdollista. Jos todella täytyy antaa sarja IP osoitteita, pyri pitämään sarja mahdollisimman pienenä tai jos vain mahdollista käytä yksittäisiä osoitteita";

$lang['Select_username'] = "Valitse käyttäjätunnus";
$lang['Select_ip'] = "Valitse IP";
$lang['Select_email'] = "Valitse sähköpostiosoite";

$lang['Ban_username'] = "Anna porttikielto yhdelle tai useammalle käyttäjälle";
$lang['Ban_username_explain'] = "Voit antaa porttikiellon samalla kertaa usealle käyttäjälle käyttäen sopivasti yhdistellen tietokoneesi hiirtä ja näppäimistöä";

$lang['Ban_IP'] = "Anna porttikielto yhdelle tai useammalle IP:lle tai koneelle";
$lang['IP_hostname'] = "IP osoite tai koneen nimi";
$lang['Ban_IP_explain'] = "Määrittele useampi IP osoite tai kone erittelemällä ne pilkuilla. Määrittele sarja IP osoitteita syöttämällä alku- ja loppuosoitteiden väliin miinusmerkki (-), jokerina käytetään *";

$lang['Ban_email'] = "Anna porttikielto yhdelle tai useammalle sähköpostiosoitteelle";
$lang['Ban_email_explain'] = "Määrittääksesi monta sähköpostiosoitetta, erittele ne pilkuilla. jokerina käytetään*, esimerkiksi *@hotmail.com";

$lang['Unban_username'] = "Poista porttikielto yhdeltä tai useammalta käyttäjältä";
$lang['Unban_username_explain'] = "Voit poistaa porttikiellon samalla kertaa useammalta käyttäjältä käyttäen sopivasti yhdistellen tietokoneesi hiirtä ja näppäimistöä";

$lang['Unban_IP'] = "Poista porttikielto yhdeltä tai useammalta IP osoitteelta";
$lang['Unban_IP_explain'] = "Voit poistaa porttikiellon samalla kertaa useammalta IP osoitteelta käyttäen sopivasti yhdistellen tietokoneesi hiirtä ja näppäimistöä";

$lang['Unban_email'] = "Poista porttikielto yhdeltä tai useammalta sähköpostiosoitteelta";
$lang['Unban_email_explain'] = "Voit poistaa porttikiellon samalla kertaa useammalta sähköpostiosoitteelta käyttäen sopivasti yhdistellen tietokoneesi hiirtä ja näppäimistöä";

$lang['No_banned_users'] = "Ei porttikiellossa olevia käyttäjätunnuksia";
$lang['No_banned_ip'] = "Ei porttikiellossa olevia IP osoitteita";
$lang['No_banned_email'] = "Ei porttikiellossa olevia sähköpostiosoitteita";

$lang['Ban_update_sucessful'] = "Porttikieltolista on päivitetty onnistuneesti";
$lang['Click_return_banadmin'] = "Klikkaa %stästä%s palataksesi porttikieltojen ylläpitoon";


//
// Configuration
//
$lang['General_Config'] = "Yleinen konfigurointi";
$lang['Config_explain'] = "Alla olevalla lomakkeella voit ylläpitää sivuston yleisiä toimintoja. Käyttäjien ja foorumien ylläpitoon on linkit sivun vasemmassa reunassa.";

$lang['Click_return_config'] = "Klikkaa %stästä%s palataksesi yleistietojen konfigurointiin";

$lang['General_settings'] = "Sivuston yleisasetukset";
$lang['Server_name'] = "Domain nimi";
$lang['Server_name_explain'] = "Domain nimi jolla sivusto toimii";
$lang['Script_path'] = "Skriptien polku";
$lang['Script_path_explain'] = "Polku, jossa phpBB2 sijaitsee suhteessa domain nimeen";
$lang['Server_port'] = "Palvelimen portti";
$lang['Server_port_explain'] = "Portti, jossa palvelin toimii, yleensä 80, muuta jos jokin muu";
$lang['Site_name'] = "Sivuston nimi";
$lang['Site_desc'] = "Sivuston kuvaus";
$lang['Board_disable'] = "Passivoi sivusto";
$lang['Board_disable_explain'] = "Tämä toimenpide estää sivuston käytön. Älä kirjaudu ulos kun sivusto on passivoituna, et pääse tällöin takaisin!";
$lang['Acct_activation'] = "Käyttäjätunnukset aktivoi";
$lang['Acc_None'] = "Ei kukaan"; // These three entries are the type of activation
$lang['Acc_User'] = "Käyttäjä";
$lang['Acc_Admin'] = "Ylläpito";

$lang['Abilities_settings'] = "Käyttäjien ja foorumien perusasetukset";
$lang['Max_poll_options'] = "Äänestysvaihtoehtojen maksimi lukumäärä";
$lang['Flood_Interval'] = "Ylivuoto (flood) tauko";
$lang['Flood_Interval_explain'] = "Kuinka monta sekuntia käyttäjän pitää odottaa viestien lähetysten välillä"; 
$lang['Board_email_form'] = "Sähköpostin käyttö sivuston välityksellä";
$lang['Board_email_form_explain'] = "Käyttäjät lähettävät toisilleen sähköpostia sivuston välityksellä";
$lang['Topics_per_page'] = "Aiheita sivulla";
$lang['Posts_per_page'] = "Viestejä sivulla";
$lang['Hot_threshold'] = "Suositun viestin raja";
$lang['Default_style'] = "Oletustyyli";
$lang['Override_style'] = "Ohita käyttäjän määrittelemä tyyli";
$lang['Override_style_explain'] = "Korvaa käyttäjän tyyli oletustyylillä";
$lang['Default_language'] = "Oletuskieli";
$lang['Date_format'] = "Päiväyksen muoto";
$lang['System_timezone'] = "Järjestelmän aikavyöhyke";
$lang['Enable_gzip'] = "Salli GZip pakkaus";
$lang['Enable_prune'] = "Salli Foorumien siivous";
$lang['Allow_HTML'] = "Salli HTML";
$lang['Allow_BBCode'] = "Salli BBCode";
$lang['Allowed_tags'] = "Sallitut HTML tagit";
$lang['Allowed_tags_explain'] = "Erota tagit pilkuilla";
$lang['Allow_smilies'] = "Salli hymiöt";
$lang['Smilies_path'] = "Tallennuspolku hymiöille";
$lang['Smilies_path_explain'] = "Polku phpBB juurihakemiston alla, esim. images/smilies";
$lang['Allow_sig'] = "Salli allekirjoitukset";
$lang['Max_sig_length'] = "Allekirjoituksen maksimipituus";
$lang['Max_sig_length_explain'] = "Käyttäjän allekirjoituksen maksimi merkkimäärä";
$lang['Allow_name_change'] = "Salli käyttäjätunnuksen vaihto";

$lang['Avatar_settings'] = "Avatar asetukset";
$lang['Allow_local'] = "Salli avatar galleria";
$lang['Allow_remote'] = "Salli etä-avatarit";
$lang['Allow_remote_explain'] = "Avatarit, jotka on linkattu toiselta sivustolta";
$lang['Allow_upload'] = "Salli avatarin lataus";
$lang['Max_filesize'] = "Avatar tiedoston maksimi koko";
$lang['Max_filesize_explain'] = "Ladatuille avatar tiedostoille";
$lang['Max_avatar_size'] = "Avatarin maksimikoko";
$lang['Max_avatar_size_explain'] = "(Korkeus x leveys pikseleinä)";
$lang['Avatar_storage_path'] = "Avatarien tallennuspolku";
$lang['Avatar_storage_path_explain'] = "Polku phpBB juurihakemiston alla, esim. images/avatars";
$lang['Avatar_gallery_path'] = "Avatar Gallerian polku";
$lang['Avatar_gallery_path_explain'] = " Polku phpBB juurihakemiston alle valmiiksi tallennetuille kuville, esim. images/avatars/gallery";

$lang['COPPA_settings'] = "COPPA asetukset";
$lang['COPPA_fax'] = "COPPA Faksi numero";
$lang['COPPA_mail'] = "COPPA postiosoite";
$lang['COPPA_mail_explain'] = "Tähän osoitteeseen huoltajat lähettävät COPPA rekisteröinti-ilmoitukset";

$lang['Email_settings'] = "Sähköposti asetukset";
$lang['Admin_email'] = "Hallinnon sähköpostiosoite";
$lang['Email_sig'] = "Sähköposti allekirjoitus";
$lang['Email_sig_explain'] = "Tämä teksti liitetään kaikkiin tämän sivuston lähettämiin sähköposteihin";
$lang['Use_SMTP'] = "Käytä SMTP serveriä sähköpostiin";
$lang['Use_SMTP_explain'] = "Vastaa kyllä, jos haluat lähettää sähköpostin nimetyn palvelimen kautta, paikallisen mail toiminnon sijasta";
$lang['SMTP_server'] = "SMTP serverin osoite";
$lang['SMTP_username'] = "SMTP käyttäjätunnus";
$lang['SMTP_username_explain'] = "Anna käyttäjätunnus vain jos smtp palvelin vaatii sitä";
$lang['SMTP_password'] = "SMTP salasana";
$lang['SMTP_password_explain'] = "Anna salasana vain jos smtp palvelin vaatii sitä";

$lang['Disable_privmsg'] = "Yksityiset viestit";
$lang['Inbox_limits'] = "Maks. viestit Saapunut kansiossa";
$lang['Sentbox_limits'] = "Maks. viestit Lähetetyt kansiossa";
$lang['Savebox_limits'] = "Maks. viestit Tallennetut kansiossa";

$lang['Cookie_settings'] = "Cookie asetukset"; 
$lang['Cookie_settings_explain'] = "Kuinka selaimelle lähetettävät cookiet määritellään. Yleensä oletusarvot ovat riittäviä. Jos näitä on tarvetta muuttaa, tee se huolella. Väärät asetukset voivat estää käyttäjien kirjautumisen.";
$lang['Cookie_domain'] = "Cookie domain";
$lang['Cookie_name'] = "Cookie nimi";
$lang['Cookie_path'] = "Cookie polku";
$lang['Cookie_secure'] = "Suojattu cookie  [ https ]";
$lang['Cookie_secure_explain'] = "Jos palvelimesi toimii SSL:n kautta aktivoi tämä, muussa tapauksessa jätä pois käytöstä";
$lang['Session_length'] = "Istunnon pituus [ sekunteja ]";


//
// Forum Management
//
$lang['Forum_admin'] = "Foorumien hallinta";
$lang['Forum_admin_explain'] = "Tällä sivulla voit lisätä, poistaa, muokata, järjestellä ja synkronoida kategorioita ja foorumeita";
$lang['Edit_forum'] = "Muokkaa foorumia";
$lang['Create_forum'] = "Luo uusi foorumi";
$lang['Create_category'] = "Luo uusi kategoria";
$lang['Remove'] = "Poista";
$lang['Action'] = "Toiminta";
$lang['Update_order'] = "Päivitä järjestys";
$lang['Config_updated'] = "Foorumien konfigurointitiedot päivitetty onnistuneesti";
$lang['Edit'] = "Muokkaa";
$lang['Delete'] = "Poista";
$lang['Move_up'] = "Siirrä ylöspäin";
$lang['Move_down'] = "Siirrä alaspäin";
$lang['Resync'] = "Synkronoi";
$lang['No_mode'] = "Toimintoa ei asetettu";
$lang['Forum_edit_delete_explain'] = "Alapuolella olevalla lomakkeella voit muokata kaikkia foorumien yleisiä toimintoja. Muihin konfigurointitietoihin pääset sivun vasemman reunan linkeistä";

$lang['Move_contents'] = "Siirrä kaikki sisältö";
$lang['Forum_delete'] = "Poista foorumi";
$lang['Forum_delete_explain'] = "Alapuolella olevalla lomakkeella voit poistaa foorumin (tai kategorian) ja voit määritellä mihin haluat siirtää foorumin kaikki aiheet (tai foorumit).";

$lang['Forum_settings'] = "Yleiset foorumin asetukset";
$lang['Forum_name'] = "Foorumin nimi";
$lang['Forum_desc'] = "Kuvaus";
$lang['Forum_status'] = "Foorumin status";
$lang['Forum_pruning'] = "Autosiivous";

$lang['prune_freq'] = 'Tarkista aiheiden ikä joka';
$lang['prune_days'] = "Poista aiheet, joihin ei ole kirjoitettu";
$lang['Set_prune_data'] = "Olet määritellyt automaattisen siivouksen tälle foorumille mutta et ole antanut siivoustiheyttä tai päivien lukumäärää. Ole hyvä ja anna tiedot";

$lang['Move_and_Delete'] = "Siirrä ja poista";

$lang['Delete_all_posts'] = "Poista kaikki viestit";
$lang['Nowhere_to_move'] = "Ei ole paikkaa johon siirtää";

$lang['Edit_Category'] = "Muokkaa kategoriaa";
$lang['Edit_Category_explain'] = "Tällä lomakkeella määritellään kategorian nimi.";

$lang['Forums_updated'] = "Foorumi- ja kategoriatiedot päivitetty onnistuneesti";

$lang['Must_delete_forums'] = "Kaikki foorumit on poistettava ennen kuin tämä kategoria voidaan poistaa";

$lang['Click_return_forumadmin'] = "Klikkaa %stästä%s palataksesi foorumien hallintaan";


//
// Smiley Management
//
$lang['smiley_title'] = "Hymiöiden hallinta";
$lang['smile_desc'] = "Tällä sivulla voit lisätä, poistaa ja muokata hymiöitä, joita käyttäjät voivat käyttää tavallisissa ja yksityisissä viesteissä.";

$lang['smiley_config'] = "Hymiöiden konfigurointi";
$lang['smiley_code'] = "Hymiön koodi";
$lang['smiley_url'] = "Hymiön kuvatiedosto";
$lang['smiley_emot'] = "Hymiön Emotio";
$lang['smile_add'] = "Lisää uusi hymiö";
$lang['Smile'] = "Hymiö";
$lang['Emotion'] = "Emotio";

$lang['Select_pak'] = "Valitse kokoelma (.pak) tiedosto";
$lang['replace_existing'] = "Korvaa olemassa oleva hymiö";
$lang['keep_existing'] = "Säilytä olemassa oleva hymiö";
$lang['smiley_import_inst'] = "Sinun pitää purkaa hymiö kokoelma ja ladata kaikki tiedostot oikeaan hymiö-hakemistoon. Valitse sitten oikeat tiedot tällä lomakkeella lukeaksesi sisään hymiökokoelman.";
$lang['smiley_import'] = "Hymiökokoelman sisäänluku";
$lang['choose_smile_pak'] = "Valitse hymiökokoelman .pak tiedosto";
$lang['import'] = "Lue sisään hymiöt";
$lang['smile_conflicts'] = "Mitä pitää tehdä mahdollisissa päällekkäisyyksissä";
$lang['del_existing_smileys'] = "Poista olemassa olevat hymiöt ennen sisään lukua";
$lang['import_smile_pack'] = "Lue sisään hymiökokoelma";
$lang['export_smile_pack'] = "Luo hymiökokoelma";
$lang['export_smiles'] = "Luodaksesi hymiökokoelman nykyisistä hymiöistä, klikkaa %stästä%s siirtääksesi hymiö (smiles.pak) tiedoston. Nimeä tiedosto säilyttäen .pak tarkenne. Luo zip tiedosto joka sisältää kaikki hymiötiedostot ja tämän .pak konfigurointi tiedoston.";

$lang['smiley_add_success'] = "Hymiön lisäys onnistui";
$lang['smiley_edit_success'] = "Hymiön päivitys onnistui";
$lang['smiley_import_success'] = "Hymiökokoelman sisäänluku onnistui!";
$lang['smiley_del_success'] = "Hymiön poisto onnistui";
$lang['Click_return_smileadmin'] = "Klikkaa %stästä%s palataksesi hymiöiden hallintaan";


//
// User Management
//
$lang['User_admin'] = "Käyttäjien hallinta";
$lang['User_admin_explain'] = "Tässä voit muuttaa käyttäjän tietoja ja joitain tiettyjä asetuksia. Muokataksesi käyttäjän oikeuksia, käytä käyttäjien ja ryhmien hallintaan tarkoitettua työkalua.";

$lang['Look_up_user'] = "Näytä käyttäjä";

$lang['Admin_user_fail'] = "Käyttäjätietoja ei voitu päivittää.";
$lang['Admin_user_updated'] = "Käyttäjätietojen päivitys onnistui.";
$lang['Click_return_useradmin'] = "Klikkaa %stästä%s palataksesi käyttäjien hallintaan";

$lang['User_delete'] = "Poista tämä käyttäjä";
$lang['User_delete_explain'] = "Klikkaa tästä poistaaksesi tämä käyttäjä, toimintoa ei voi peruuttaa.";
$lang['User_deleted'] = "Käyttäjän poisto onnistui.";

$lang['User_status'] = "Käyttäjä on aktiivinen";
$lang['User_allowpm'] = "Yksityiset viestit";
$lang['User_allowavatar'] = "Avatarin käyttö";

$lang['Admin_avatar_explain'] = "Tästä näet ja voit poistaa käyttäjän nykyisen avatarin.";

$lang['User_special'] = "Ylläpidon erikoiskentät";
$lang['User_special_explain'] = "Näitä kenttiä ei tavallinen käyttäjä voi muuttaa. Tässä voit määritellä käyttäjän statuksen ja muita asetuksia, joita ei sallita tavallisille käyttäjille.";


//
// Group Management
//
$lang['Group_administration'] = "Ryhmien hallinta";
$lang['Group_admin_explain'] = "Tällä lomakkeella voit hallinnoida kaikkia käyttäjäryhmiä. Voit poistaa, luoda ja muokata ryhmiä. Voit valita moderaattorit, muuttaa avoin/suljettu statusta ja määritellä ryhmän nimen sekä kuvauksen";
$lang['Error_updating_groups'] = "Ryhmien päivityksessä tapahtui virhe";
$lang['Updated_group'] = "Ryhmän päivitys onnistui";
$lang['Added_new_group'] = "Uuden ryhmän luonti onnistui";
$lang['Deleted_group'] = "Ryhmän poisto onnistui";
$lang['New_group'] = "Luo uusi ryhmä";
$lang['Edit_group'] = "Muokkaa ryhmää";
$lang['group_name'] = "Ryhmän nimi";
$lang['group_description'] = "Ryhmän kuvaus";
$lang['group_moderator'] = "Ryhmän moderaattori";
$lang['group_status'] = "Ryhmän status";
$lang['group_open'] = "Avoin ryhmä";
$lang['group_closed'] = "Suljettu ryhmä";
$lang['group_hidden'] = "Piilotettu ryhmä";
$lang['group_delete'] = "Poista ryhmä";
$lang['group_delete_check'] = "Poista tämä ryhmä";
$lang['submit_group_changes'] = "Tallenna muutokset";
$lang['reset_group_changes'] = "Resetoi muutokset";
$lang['No_group_name'] = "Ryhmälle on annettava nimi";
$lang['No_group_moderator'] = "Ryhmälle on määritettävä moderaattori";
$lang['No_group_mode'] = "Ryhmälle on määriteltävä onko se avoin vai suljettu";
$lang['delete_group_moderator'] = "Poistetaanko ryhmän entinen moderaattori?";
$lang['delete_moderator_explain'] = "Jos muutat ryhmän moderaattoria laita rasti tähän ruutuun poistaaksesi vanhan moderaattoritiedon. Muussa tapauksessa älä laita raksia ja käyttäjästä tulee tavallinen ryhmän jäsen.";
$lang['Click_return_groupsadmin'] = "Klikkaa %stästä%s palataksesi ryhmän hallintaan.";
$lang['Select_group'] = "Valitse ryhmä";
$lang['Look_up_group'] = "Näytä ryhmä";


//
// Prune Administration
//
$lang['Forum_Prune'] = "Foorumin siivous";
$lang['Forum_Prune_explain'] = "Tällä poistetaan kaikki aiheet, joihin ei ole kirjoitettu antamasi päivärajauksen sisällä. Jos et anna rajausta, niin kaikki aiheet poistetaan. Aiheita, joissa on aktiivinen äänestys ja ilmoitustyyppisiä aiheita ei poisteta. Nämä aiheet on poistettava käsin.";
$lang['Do_Prune'] = "Suorita siivous";
$lang['All_Forums'] = "Kaikki foorumit";
$lang['Prune_topics_not_posted'] = "Siivoa aiheet, joissa ei ole vastauksia annetun ajan sisällä";
$lang['Topics_pruned'] = "Siivottuja aiheita";
$lang['Posts_pruned'] = "Siivottuja viestejä";
$lang['Prune_success'] = "Foorumien siivous onnistui";


//
// Word censor
//
$lang['Words_title'] = "Sanojen sensurointi";
$lang['Words_explain'] = "Tästä hallintapaneelista voi lisätä, muokata ja poistaa sanoja jotka automaattisesti sensuroidaan foorumeissa. Lisäksi käyttäjätunnuksissa ei sallita näitä sanoja. Jokerit (*) ovat hyväksyttyjä sana kentässä, esim. *testi* täsmää kestotestin kanssa, test* täsmää testaus kanssa, *testi täsmää epotesti kanssa.";
$lang['Word'] = "Sana";
$lang['Edit_word_censor'] = "Muokkaa sanan sensuuria";
$lang['Replacement'] = "Korvaus";
$lang['Add_new_word'] = "Lisää uusi sana";
$lang['Update_word'] = "Päivitä sanasensuuri";

$lang['Must_enter_word'] = "Sinun täytyy antaa sana ja sen korvike";
$lang['No_word_selected'] = "Sanaa ei ole valittu muokattavaksi";

$lang['Word_updated'] = "Valittu sanasensuuri on päivitetty";
$lang['Word_added'] = "Sanasensuuri on lisätty";
$lang['Word_removed'] = "Valittu sanasensuuri on poistettu";

$lang['Click_return_wordadmin'] = "Klikkaa %stästä%s palataksesi Sanojen sensurointiin";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Tästä voit lähettää sähköpostia joko kaikille käyttäjille tai tietyn ryhmän käyttäjille. Ylläpidon sähköpostiosoitteeseen lähetetään sähköposti ja piilokopio kaikille vastaanottajille. Jos lähetät isolle ryhmälle vastaanottajia ole kärsivällinen äläkä keskeytä toimintoa. On normaalia, että joukkopostitus kestää pitkään. Saat ilmoituksen kun komento on suoritettu.";
$lang['Compose'] = "Sähköpostin luonti"; 

$lang['Recipients'] = "Vastaanottajat"; 
$lang['All_users'] = "Kaikki käyttäjät";

$lang['Email_successfull'] = "Viestisi on lähetetty";
$lang['Click_return_massemail'] = "Klikkaa %stästä%s palataksesi sähköpostin lähetykseen";


//
// Ranks admin
//
$lang['Ranks_title'] = "Tittelien hallinta";
$lang['Ranks_explain'] = "Tällä lomakkeella voit muokata, tarkastaa ja poistaa titteleitä. Voit myös muodostaa erityisiä titteleitä joita voidaan liittää käyttäjiin käyttäjätietojen ylläpidon kautta";

$lang['Add_new_rank'] = "Lisää uusi titteli";

$lang['Rank_title'] = "Tittelin nimi";
$lang['Rank_special'] = "Määritä erikoistitteliksi";
$lang['Rank_minimum'] = "Minimi viestien määrä";
$lang['Rank_maximum'] = "Maksimi viestien määrä";
$lang['Rank_image'] = "Tittelin kuvake (Suhteessa phpBB2 juurihakemistoon)";
$lang['Rank_image_explain'] = "Tällä voit määritellä pienen kuvakkeen tittelille";

$lang['Must_select_rank'] = "Sinun täytyy valita titteli";
$lang['No_assigned_rank'] = "Erikoistitteleitä ei asetettu";

$lang['Rank_updated'] = "Tittelin päivitys onnistui";
$lang['Rank_added'] = "Tittelin lisäys onnistui";
$lang['Rank_removed'] = "Tittelin poisto onnistui";

$lang['Click_return_rankadmin'] = "Klikkaa %stästä%s palataksesi Tittelien hallintaan";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Kiellettyjen käyttäjätunnusten hallinta";
$lang['Disallow_explain'] = "Tässä voit määritellä käyttäjätunnukset, joita ei sallita. Voit käyttää käyttäjätunnuksen määrittelyssä jokerimerkkiä *.  Huomaa, että et voi kieltää käyttäjänimeä, joka on jo rekisteröity, vaan tällainen käyttäjätunnus täytyy ensin poistaa ja lisätä sitten kiellettyjen listalle";

$lang['Delete_disallow'] = "Poista";
$lang['Delete_disallow_title'] = "Poista kielletty käyttäjätunnus";
$lang['Delete_disallow_explain'] = "Voit poistaa kielletyn käyttäjätunnuksen valitsemalla käyttäjätunnus listalta ja klikkaamalla poista";

$lang['Add_disallow'] = "Lisää";
$lang['Add_disallow_title'] = "Lisää kielletty käyttäjätunnus";
$lang['Add_disallow_explain'] = "Voit kieltää käyttäjätunnuksen ja käyttää jokerimerkkiä * korvaamaan minkä tahansa merkin";

$lang['No_disallowed'] = "Ei kiellettyjä käyttäjätunnuksia";

$lang['Disallowed_deleted'] = "Kielletty käyttäjätunnus on poistettu";
$lang['Disallow_successful'] = "Kielletty käyttäjätunnus on lisätty";
$lang['Disallowed_already'] = "Antamaasi tunnusta ei voida kieltää. Se joko on jo listalla, sisältyy sensuroituihin sanoihin tai käyttäjätunnus on jo olemassa";

$lang['Click_return_disallowadmin'] = "Klikkaa %stästä%s palataksesi Kiellettyjen käyttäjätunnusten hallintaan";


//
// Styles Admin
//
$lang['Styles_admin'] = "Tyylien hallinta";
$lang['Styles_explain'] = "Tässä voit lisätä, poistaa ja muokata tyylejä (malleja ja teemoja) joita käyttäjät voivat käyttää";
$lang['Styles_addnew_explain'] = "Seuraava listaus sisältää kaikki teemat, jotka ovat käytettävissä malleille (templates) joita sinulla on. Tämän listan kohteita ei ole asennettu vielä phpBB tietokantaan. Lisätäksesi teeman klikkaa vain asenna linkkiä kohteen vieressä";

$lang['Select_template'] = "Valitse malli";

$lang['Style'] = "Tyyli";
$lang['Template'] = "Malli";
$lang['Install'] = "Asenna";
$lang['Download'] = "Lataa";

$lang['Edit_theme'] = "Muokkaa teemaa";
$lang['Edit_theme_explain'] = "Alapuolella olevalla lomakkeella voit muokata valitun teeman asetuksia.";

$lang['Create_theme'] = "Luo teema";
$lang['Create_theme_explain'] = "Käytä alla olevaa lomaketta luodaksesi uuden teeman valitulle mallille. Kun määrittelet värejä (jotka pitää antaa heksalukuina) et saa käyttää alussa #, Esim. CCCCCC on oikein, #CCCCCC on väärin";

$lang['Export_themes'] = "Siirrä teemat";
$lang['Export_explain'] = "Tällä lomakkeella voit siirtää teematiedot valitusta mallista. Valitse malli alla olevasta listasta ja komentojono luo teeman konfigurointitiedoston ja pyrkii tallentamaan sen valitun mallin hakemistoon. Jos tiedostoa ei voi tallentaa annetaan sinulle mahdollisuus ladata tiedosto. Jotta komentojono voisi tallentaa tiedoston on sinun annettava kirjoitusoikeus web palvelimella valittuun mallin hakemistoon. Lisätietoja phpBB 2 käyttäjän ohjeessa.";

$lang['Theme_installed'] = "Valittu teema on asennettu onnistuneesti";
$lang['Style_removed'] = "Valittu tyyli on poistettu tietokannasta. Poistaaksesi kokonaan tyylin järjestelmästä täytyy sinun poistaa tyylitiedosto mallien hakemistosta.";
$lang['Theme_info_saved'] = "Teeman tiedot valitussa mallissa on tallennettu. Sinun täytyy nyt palauttaa käyttöoikeudeksi pelkkä luku (read-only) tiedostoon theme_info.cfg (ja tarvittaessa valittuun mallihakemistoon)";
$lang['Theme_updated'] = "Valittu teema on päivitetty. Siirrä nyt uudet teeman asetukset";
$lang['Theme_created'] = "Teema luotu. Siirrä nyt teema konfigurointitiedostoon varmuuden vuoksi, tai käytettäväksi myös muualla";

$lang['Confirm_delete_style'] = "Oletko varma, että haluat poistaa tämän tyylin";

$lang['Download_theme_cfg'] = "Siirrossa ei voitu kirjoittaa teeman määrittelytiedostoon. Klikkaa kuvaketta alapuolella ladataksesi tämän tiedoston selaimesi avulla. Kun olet ladannut tiedoston voit siirtää sen hakemistoon joka sisältää mallitiedostot. Voit sen jälkeen pakata tiedostot jakeluun tai käyttöön muuaalla halutessasi";
$lang['No_themes'] = "Valitsemassasi mallissa ei ole teemoja. Luodaksesi uuden teeman klikkaa Luo Uusi linkkiä sivun vasemmassa reunassa";
$lang['No_template_dir'] = "Mallihakemistoa ei saatu avattua. Sen luku voi olla estetty selaimelta tai hakemisto voi puuttua";
$lang['Cannot_remove_style'] = "Et voi poistaa tyyliä, koska se on tällä hetkellä oletustyyli. Ole hyvä ja vaihda oletustyyli ja yritä uudestaan.";
$lang['Style_exists'] = "Annetun niminen tyyli on jo olemassa, ole hyvä ja anna toinen nimi.";

$lang['Click_return_styleadmin'] = "Klikkaa %stästä%s palataksesi Tyylien hallintaan";

$lang['Theme_settings'] = "Teemojen asetukset";
$lang['Theme_element'] = "Teeman elementti";
$lang['Simple_name'] = "Yksinkertainen nimi";
$lang['Value'] = "Arvo";
$lang['Save_Settings'] = "Tallenna asetukset";

$lang['Stylesheet'] = "CSS tyylisivu";
$lang['Background_image'] = "Taustakuva";
$lang['Background_color'] = "Taustan väri";
$lang['Theme_name'] = "Teeman nimi";
$lang['Link_color'] = "Linkin väri";
$lang['Text_color'] = "Tekstin väri";
$lang['VLink_color'] = "Käytetyn linkin väri";
$lang['ALink_color'] = "Aktiivisen linkin väri";
$lang['HLink_color'] = "Hover linkin väri";
$lang['Tr_color1'] = "Taulukon rivin väri 1";
$lang['Tr_color2'] = "Taulukon rivin väri 2";
$lang['Tr_color3'] = "Taulukon rivin väri 3";
$lang['Tr_class1'] = "Taulukon rivin luokka 1";
$lang['Tr_class2'] = "Taulukon rivin luokka 2";
$lang['Tr_class3'] = "Taulukon rivin luokka 3";
$lang['Th_color1'] = "Taulukon otsikon väri 1";
$lang['Th_color2'] = "Taulukon otsikon väri 2";
$lang['Th_color3'] = "Taulukon otsikon väri 3";
$lang['Th_class1'] = "Taulukon otsikon luokka 1";
$lang['Th_class2'] = "Taulukon otsikon luokka 2";
$lang['Th_class3'] = "Taulukon otsikon luokka 3";
$lang['Td_color1'] = "Taulukon solun väri 1";
$lang['Td_color2'] = "Taulukon solun väri 2";
$lang['Td_color3'] = "Taulukon solun väri 3";
$lang['Td_class1'] = "Taulukon solun luokka 1";
$lang['Td_class2'] = "Taulukon solun luokka 2";
$lang['Td_class3'] = "Taulukon solun luokka 3";
$lang['fontface1'] = "Fontti 1";
$lang['fontface2'] = "Fontti 2";
$lang['fontface3'] = "Fontti 3";
$lang['fontsize1'] = "Fontin koko 1";
$lang['fontsize2'] = "Fontin koko 2";
$lang['fontsize3'] = "Fontin koko 3";
$lang['fontcolor1'] = "Font väri 1";
$lang['fontcolor2'] = "Font väri 2";
$lang['fontcolor3'] = "Font väri 3";
$lang['span_class1'] = "Välistys 1";
$lang['span_class2'] = "Välistys 2";
$lang['span_class3'] = "Välistys 3";
$lang['img_poll_size'] = "Äänestyksen kuvakkeen koko [px]";
$lang['img_pm_size'] = "Yksityisviestin statuksen koko [px]";


//
// Install Process
//
$lang['Welcome_install'] = "Tervetuloa phpBB 2 asennukseen";
$lang['Initial_config'] = "Perus konfiguraatio";
$lang['DB_config'] = "Tietokanta konfiguraatio";
$lang['Admin_config'] = "Ylläpidon konfiguraatio";
$lang['continue_upgrade'] = "Kun olet ladannut konfigurointitiedoston paikalliselle koneelle voit klikata\"jatka päivitystä\" painiketta alapuolella jatkaaksesi päivitystä.  Ole hyvä ja odota konfigurointitiedoston lataamista kunnes päivitys on valmis.";
$lang['upgrade_submit'] = "Jatka päivitystä";

$lang['Installer_Error'] = "Asennuksen yhteydessä tapahtui virhe";
$lang['Previous_Install'] = "Aikaisempi asennus löydetty";
$lang['Install_db_error'] = "Tietokannan päivityksessä tapahtui virhe";

$lang['Re_install'] = "Aikaisempi asennus on yhä aktiivinen. <br /><br />Jos haluat asentaa uudestaan phpBB 2:n, klikkaa Yes nappulaa alapuolella. Huomaa kuitenkin, että näin menetät kaiken olemassa olevan datan, varmuuskopiointia ei suoriteta! Ylläpitäjän käyttäjätunnus ja salasana joita olet käyttänyt sisään kirjautumiseen luodaan uudestaan asennuksen jälkeen, muita asetuksia ei säilytetä. <br /><br />Harkitse tarkoin ennen kuin klikkaat Yes!";

$lang['Inst_Step_0'] = "Kiitos, että valintasi on phpBB 2. Jotta asennus voidaan suorittaa loppuun, täytä alla olevat yksityiskohdat. Huomaa, että tietokannan johon asennus tehdään,  tulee olla jo olemassa. Jos asennat tietokantaan joka käyttää ODBC:ta, esim. MS Access sinun täytyy ensin luoda DSN sille.";

$lang['Start_Install'] = "Aloita asennus";
$lang['Finish_Install'] = "Lopeta asennus";

$lang['Default_lang'] = "Sivuston oletuskieli";
$lang['DB_Host'] = "Tietokantapalvelimen nimi / DSN";
$lang['DB_Name'] = "Tietokannan nimi";
$lang['DB_Username'] = "Tietokannan käyttäjätunnus";
$lang['DB_Password'] = "Tietokannan salasana";
$lang['Database'] = "Tietokanta";
$lang['Install_lang'] = "Valitse asennuskieli";
$lang['dbms'] = "Tietokannan tyyppi";
$lang['Table_Prefix'] = "Etuliite tauluille tietokannassa";
$lang['Admin_Username'] = "Ylläpitäjän käyttäjätunnus";
$lang['Admin_Password'] = "Ylläpitäjän salasana";
$lang['Admin_Password_confirm'] = "Ylläpitäjän salasana [ Vahvista ]";

$lang['Inst_Step_2'] = "Ylläpitäjän käyttäjätunnus on luotu. Tässä vaiheessa perusasennus on valmis. Nyt saat seuraavan sivun jolla voit hallinnoida uutta asennustasi. Ole hyvä ja varmista peruskonfiguraation tiedot ja tee tarvittavat muutokset. Kiitoksia, että valitsit phpBB 2:n.";

$lang['Unwriteable_config'] = "Konfigurointitiedostosi ei ole kirjoituskelpoinen tällä hetkellä. Kopio konfigurointitiedostosta ladataan sinulle kun klikkaat painiketta alapuolella. Sinun tulee siirtää tämä tiedosto samaan hakemistoon kuin phpBB 2. Kun tämä on tehty kirjaudu sisään ylläpitäjän käyttäjätunnuksella ja salasanalla ja käy ylläpidon hallintasivuilla  (Linkki ilmestyy sivun alareunaan sisään kirjautumisen jälkeen) tarkistaaksesi yleiset asetukset. Kiitos kun valitsit phpBB 2:n.";
$lang['Download_config'] = "Lataa konfigurointitiedosto";

$lang['ftp_choose'] = "Valitse tiedonsiirtotapa";
$lang['ftp_option'] = "<br />Koska myös FTP on mahdollista tässä PHP versiossa sinulle voidaan antaa mahdollisuus automaattisesti siirtää ftp:llä tiedosto oikeaan paikkaan.";
$lang['ftp_instructs'] = "Olet valinnut tiedoston siirrettäväksi automaattisesti ftp:llä. Ole hyvä ja anna alla kysytyt tiedot, jotta tiedonsiirto onnistuu.  Huomioi, että FTP polun tulee olla tarkka polku ftp:llä phpBB2 asennushakemistoon kuten siirtäisit mitä tahansa tietoa ftp:llä.";
$lang['ftp_info'] = "Anna FTP tiedot ";
$lang['Attempt_ftp'] = "Yritetään siirtää konfigurointitiedosto ftp:llä";
$lang['Send_file'] = "Lähetä tiedosto minulle ja siirrän sen ftp:llä manuaalisesti";
$lang['ftp_path'] = "FTP polku phpBB 2:lle";
$lang['ftp_username'] = "FTP käyttäjätunnus";
$lang['ftp_password'] = "FTP salasana";
$lang['Transfer_config'] = "Aloita siirto";
$lang['NoFTP_config'] = "Konfigurointitiedoston ftp siirto ei onnistunut. Ole hyvä ja lataa konfigurointitiedosto ja käytä ftp:tä manuaalisesti.";

$lang['Install'] = "Asennus";
$lang['Upgrade'] = "Päivitys";


$lang['Install_Method'] = "Valitse asennustapa";

$lang['Install_No_Ext'] = "Palvelimen php asetukset eivät tue valitsemaasi tietokantaa.";

$lang['Install_No_PCRE'] = "PhpBB2 tarvitsee moduulin (Perl-Compatible Regular Expressions Module) php:lle jota php asetuksesi eivät näytä tukevan!";

//
// That's all Folks!
// -------------------------------------------------

?>