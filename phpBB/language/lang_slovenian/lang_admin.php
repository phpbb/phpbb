<?php

/***************************************************************************
 *                            lang_admin.php [Slovenian]
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

/* CONTRIBUTORS
	2002-12-15	Philip M. White (pwhite@mailhaven.com)
		Fixed many minor grammatical mistakes
	Prevod: Nataša Holy, natasa.holy@guest.arnes.si, www.uciteljska.net; Ladislav Golouh, info@razmerje.com, www.razmerje.com

*/

//
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = 'Splošna Administracija';
$lang['Users'] = 'Administracija uporabnikov';
$lang['Groups'] = 'Administracija skupine';
$lang['Forums'] = 'Administracija forumov';
$lang['Styles'] = 'Administracija za izgled';

$lang['Configuration'] = 'Nastavitve';
$lang['Permissions'] = 'Dovoljenja';
$lang['Manage'] = 'Upravljanje';
$lang['Disallow'] = 'Nedovoljena uporabniška imena';
$lang['Prune'] = 'Èišèenje';
$lang['Mass_Email'] = 'Množièna pošta';
$lang['Ranks'] = 'Stopnje';
$lang['Smilies'] = 'Smeški';
$lang['Ban_Management'] = 'Nadzor izloèanja'; 
$lang['Word_Censor'] = 'Cenzura besed'; 
$lang['Export'] = 'Izvozi';
$lang['Create_new'] = 'Ustvari'; 
$lang['Add_new'] = 'Dodaj';
$lang['Backup_DB'] = 'Varnostno kopiraj bazo podatkov';
$lang['Restore_DB'] = 'Obnovi bazo podatkov';
 

//
// Index
//
$lang['Admin'] = 'Administracija';
$lang['Not_admin'] = 'Niste administrator tega foruma';
$lang['Welcome_phpBB'] = 'Dobrodošli v PhpBB forum';
$lang['Admin_intro'] = 'Hvala, da ste izbrali PhpBB za svoj forum. Ta stran vam prikaže hitri pregled razno raznih statistik na tej plošèi. Na to stran se lahko vrnete s klikom na <u>Administratorjev seznam</u>, povezava je na levi strani zgoraj. Za vrnitev nazaj na zaèetno stran, klikni seznam forumov, ali klikni logo PhpBB (ali svoj logo, èe si ga zamenjal-a) levo zgoraj. Z drugimi povezavami (linki) na levi strani se povežeš na vse strani za nadzor foruma. Vsako okno zgoraj prikaže navodila za uporabo orodij.'; 
$lang['Main_index'] = 'Seznam Forumov';
$lang['Forum_stats'] = 'Statistika';
$lang['Admin_Index'] = 'Administratorjev seznam'; 
$lang['Preview_forum'] = 'Predogled Forumov'; 

$lang['Click_return_admin_index'] = 'Klikni %sTukaj%s za vrnitev v Administratorjev seznam';

$lang['Statistic'] = 'Statistika';
$lang['Value'] = 'Vrednost';
$lang['Number_posts'] = 'Število prispevkov';
$lang['Posts_per_day'] = 'Število prispevkov na dan';
$lang['Number_topics'] = 'Število tem';
$lang['Topics_per_day'] = 'Število tem na dan';
$lang['Number_users'] = 'Število uporabnikov';
$lang['Users_per_day'] = 'Število obiskovalcev na dan';
$lang['Board_started'] = 'Forum odprt od'; 
$lang['Avatar_dir_size'] = 'Velikost direktorija podob (slièic)'; 
$lang['Database_size'] = 'Velikost baze';
$lang['Gzip_compression'] ='Gzip stiskanje'; 
$lang['Not_available'] = 'Ni dostopno';

$lang['ON'] = 'Da'; // This is for GZip compression 
$lang['OFF'] = 'Ne'; 


//
// DB Utils
// 
$lang['Database_Utilities'] = 'Upravljanje baze podatkov'; 

$lang['Restore'] = 'Obnovi bazo'; 
$lang['Backup'] = 'Varnostna kopija baze'; 
$lang['Restore_explain'] = 'Tabele PhpBB bodo popolnoma obnovljene iz shranjene datoteke. Èe vaš server to podpira, lahko naložite gzip-stisnjeno besedilno datoteko (text file), ki bo avtomatièno razširjena (dekompresirana). <b>OPOZORILO</b>: To dejanje bo prepisalo vse obstojeèe podatke. Obnovitev lahko traja dolgo èasa, zato se, prosimo, ne umikajte s te strani, dokler se ne zakljuèi.'; 
$lang['Backup_explain'] = 'Tu lahko varnostno shranite (back up) vse vaše podatke, ki se nanašajo na PhpBB. Èe imate še kakšno dodatno tabelo v isti podatkovni bazi s PhpBB-jem, ki bi jo tudi radi varnostno shranili, prosim, vnesite imena tabel, loèena z vejicami, v okenca za vnos Dodatne tabele spodaj.  Èe vaš server podpira stiskanje (gzip-compression), lahko zmanjšate velikost datoteke pred nalaganjem (download).'; 

$lang['Backup_options'] = 'Možnosti pri varnostnem kopiranju'; 
$lang['Start_backup'] = 'Zaèni varnostno kopirati (backup)'; 
$lang['Full_backup'] = 'Varnostno kopiraj VSE (full backup)'; 
$lang['Structure_backup'] = 'Varnostno kopiraj SAMO ogrodje (strukturo)'; 
$lang['Data_backup'] = 'Varnostno kopiraj SAMO podatke (vsebino)'; 
$lang['Additional_tables'] = 'Dodatne tabele';
$lang['Gzip_compress'] = 'Gzip stisnjenje datoteke'; 
$lang['Select_file'] = 'Izberi datoteko';
$lang['Start_Restore'] = 'Zaèni obnavljati (Restore)';

$lang['Restore_success'] = 'Datoteka je uspešno obnovljena.<br /><br />Vaša plošèa (board) bo v enakem stanju kot je bila pred izvedbo varnostnega kopiranja.'; 
$lang['Backup_download'] = 'Nalaganje se bo zaèelo; prosim, poèakajte, da se zaène.'; 
$lang['Backups_not_supported'] = 'Žal, toda varnostno kopiranje baze podatkov trenutno ne podpira vašega sistema baze.'; 

$lang['Restore_Error_uploading'] = 'Napaka pri nalaganju varnostne datoteke';
$lang['Restore_Error_filename'] = 'Težava z imenom datoteke; poskusite, prosim, z drugo datoteko.'; 
$lang['Restore_Error_decompress'] = 'Ne morem razširiti (decompress) gzip datoteke; naložite, prosim, tekstno razlièico (plain text version).'; 
$lang['Restore_Error_no_file'] = 'Nobena datoteka ni naložena'; 


//
// Auth pages
//
$lang['Select_a_User'] = 'Izberi uporabnika';
$lang['Select_a_Group'] = 'Izberi skupino';
$lang['Select_a_Forum'] = 'Izberi forum';
$lang['Auth_Control_User'] = 'Doloèanje pravic uporabnika'; 
$lang['Auth_Control_Group'] = 'Doloèanje pravic skupine'; 
$lang['Auth_Control_Forum'] = 'Uravnavanje dovoljenj na forumu'; 
$lang['Look_up_User'] = 'Pokaži uporabnika'; 
$lang['Look_up_Group'] = 'Prikaži skupino'; 
$lang['Look_up_Forum'] = 'Prikaži forum'; 

$lang['Group_auth_explain'] = 'Tukaj lahko spreminjate dovoljenja in status moderatorja za vsako uporabniško skupino. Pri tem ne pozabite, da spreminjate pravice skupini, dovoljenja posameznega uporabnika lahko ostanejo drugaèna, da (npr. odstranjeni moderator) še vedno npr. lahko vstopa v forume, ipd. V primeru neskladnosti boste opozorjeni.'; 
$lang['User_auth_explain'] = 'Tukaj lahko spreminjate pravice uporabnika in status moderatorja lahko doloèite za kateregakoli uporabnika. Ne pozabite, ko spreminjate uporabnikove pravice, lahko pravice skupine še vedno dovolijo uporabniku vstop v forume. V tem primeru boste opozorjeni.'; 
$lang['Forum_auth_explain'] = 'Tukaj lahko spreminjate nivo dovoljenj (avtorizacije) za posamezni forum. Na razpolago imate preprosto in napredno metodo, napredna vam nudi veèjo kontrolo nad vsako operacijo foruma. Ne pozabite, da spreminjanje nivoja dovoljenj na forumih (po razdelkih) odloèa o tem, kateri uporabniki lahko uporabljajo razliène funkcije znotraj foruma.'; 

$lang['Simple_mode'] = 'Preprosti naèin'; 
$lang['Advanced_mode'] = 'Napredni naèin'; 
$lang['Moderator_status'] = 'Status moderatorja';

$lang['Allowed_Access'] = 'Dostop dovoljen';
$lang['Disallowed_Access'] = 'Dostop zavrnjen';
$lang['Is_Moderator'] = 'Je Moderator';
$lang['Not_Moderator'] = 'Ni Moderator';

$lang['Conflict_warning'] = 'Opozorilo: neusklajenost dovoljenj (avtorizacij)'; 
$lang['Conflict_access_userauth'] = 'Ta uporabnik ima še vedno pravice do dostopa za ta forum preko èlanstva v skupini. Lahko spremenite pravice skupine ali pa odstranite tega uporabnika iz skupine, tako mu popolnoma prepreèite dostop in odvzamete pravice.  Pravice, ki se nanašajo na skupine (vkljuèeni so tudi forumi), so napisane spodaj.'; 
$lang['Conflict_mod_userauth'] = 'Ta uporabnik ima še vedno moderatorske pravice za ta forum preko èlanstva v skupini. Lahko spremenite pravice skupine ali pa odstranite tega uporabnika iz skupine, tako mu popolnoma prepreèite dostop in odvzamete pravice. Pravice, ki se nanašajo na skupine (vkljuèeni so tudi forumi), so napisane spodaj.'; 

$lang['Conflict_access_groupauth'] = 'Ta uporabnik (ali uporabniki) imajo še vedno pravice za dostop na ta forum zaradi nastavljenih dovoljenj za uporabnika. Lahko spremenite dovoljenja za uporabnika, da mu (jim) popolnoma prepreèite pravice do dostopa. Pravice, ki se nanašajo na uporabnika (vkljuèeni so forumi), so napisane spodaj.'; 
$lang['Conflict_mod_groupauth'] = 'Ta uporabnik (ali uporabniki) imajo še vedno moderatorske pravice za ta forum zaradi nastavljenih dovoljenj za uporabnika. Lahko spremenite dovoljenja za uporabnika, da mu (jim) popolnoma prepreèite moderatorske pravice. Pravice, ki se nanašajo na uporabnika (vkljuèeni so forumi), so napisane spodaj.'; 

$lang['Public'] = 'Javno';
$lang['Private'] = 'Zasebno';
$lang['Registered'] = 'Registriran';
$lang['Administrators'] = 'Administratorji';
$lang['Hidden'] = 'Skrito';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = 'VSI'; 
$lang['Forum_REG'] = 'REG';
$lang['Forum_PRIVATE'] = 'ZASEBNO';
$lang['Forum_MOD'] = 'MOD';
$lang['Forum_ADMIN'] = 'ADMIN';

$lang['View'] = 'Vidi'; 
$lang['Read'] = 'Bere'; 
$lang['Post'] = 'Objavlja'; 
$lang['Reply'] = 'Odgovarja'; 
$lang['Edit'] = 'Ureja'; 
$lang['Delete'] = 'Briše'; 
$lang['Sticky'] = 'Ne prezri'; 
$lang['Announce'] = 'Obvestilo'; 
$lang['Vote'] = 'Glasuje';
$lang['Pollcreate'] = 'Ustvari anketo'; 

$lang['Permissions'] = 'Dovoljenja'; 
$lang['Simple_Permission'] = 'Osnovna dovoljenja'; 

$lang['User_Level'] = 'Uporabnikov nivo'; 
$lang['Auth_User'] = 'Uporabnik';
$lang['Auth_Admin'] = 'Administrator';
$lang['Group_memberships'] = 'Èlanstvo skupin/e'; 
$lang['Usergroup_members'] = 'V tej skupini so èlani'; 

$lang['Forum_auth_updated'] = 'Dovoljenja Forumu so posodobljena'; 
$lang['User_auth_updated'] = 'Uporabnikove pravice so posodobljene';
$lang['Group_auth_updated'] = 'Pravice skupino so posodobljene'; 

$lang['Auth_updated'] = 'Dovoljenja so posodobljena'; 
$lang['Click_return_userauth'] = 'Klikni %sTukaj%s za vrnitev k dodeljevanju pravic uporabnika';
$lang['Click_return_groupauth'] = 'Klikni %sTukaj%s za vrnitev k dodeljevanju pravic skupine';
$lang['Click_return_forumauth'] = 'Klikni %sTukaj%s za vrnitev k dodeljevanju pravic za Forum';


//
// Banning
//
$lang['Ban_control'] = 'Nadzor prepovedi';
$lang['Ban_explain'] = 'Tukaj nadzorujete izloèanje uporabnikov. Uporabnika lahko izloèite na enega ali veè od naslednjih naèinov: da prepoveste doloèenega uporabnika posamezno ali pa da prepoveste niz IP naslovov oz. ime(na) ponudnika gostitelja. Na tak naèin lahko prepreèite uporabniku celo že prihod na glavno stran (seznam forumov) vaše plošèe in ogled. Da bi prepreèili uporabniku registracijo pod drugim uporabniškim imenom, lahko doloèite prepovedan e-naslov. Vendar SAMO izloèitev doloèenega e-naslova ne prepreèi uporabniku prijavljanja in objavljanja. Uporabiti morate enega od prvih dveh naèinov.';
$lang['Ban_explain_warn'] = 'Upoštevajte, da vnos niza IP naslovov od - do povzroèi pri vseh naslovih med zaèetkom in koncem niza dodajanje na prepovedan seznam. Poskusi prikljuèevanja bodo avtomatièno oblikovali iskalne kartice (wildcards), da se zmanjša število naslovov, ki bodo dodani v bazo podatkov. Èe morate zares vnesti niz od - do, poskusite s kratkim, še bolje, posamezne naslove.';
                                                                 
$lang['Select_username'] = 'Izberi uporabnika';
$lang['Select_ip'] = 'Izberi IP naslov';
$lang['Select_email'] = 'Izberi elektronski naslov';

$lang['Ban_username'] = 'Prepovej enega ali veè doloèenih uporabnikov';
$lang['Ban_username_explain'] = 'Veè uporabnikov naenkrat lahko prepoveste naenkrat tako, da uporabite kombinacijo miške in tipkovnice, držite pritisnjeno tipko SHIFT ali CTRL, ter hkrati z miško oznaèite uporabnike, za katere želite, da so izobèeni.';

$lang['Ban_IP'] = 'Prepovej IP naslove ali imena_raèunalnikov';
$lang['IP_hostname'] = 'IP naslovi ali imena_raèunalnikov';
$lang['Ban_IP_explain'] = 'Èe želite navesti veè razliènih IP naslovov ali imen_raèunalnikov, jih loèite z vejicami. Èe želite navesti niz IP naslovov, loèite zaèetek in konec s pomišljajem (-); èe želite doloèiti iskalno kartico (wildcard), uporabite zvezdico, (*). Primer: 192.168.1.*.';

$lang['Ban_email'] = 'Prepovej enega ali veè elektronskih naslovov';
$lang['Ban_email_explain'] = 'Èe želite navesti veè razliènih elektronskih naslovov, jih loèite z vejicami. Èe želite doloèiti iskalno kartico (wildcard), uporabite *. Primer: *@hotmail.com';

$lang['Unban_username'] = 'Preklièi prepoved enega ali veè izbranih uporabnikov';
$lang['Unban_username_explain'] = 'Veè uporabnikom preklièete prepoved naenkrat tako, da držite pritisnjeno tipko SHIFT ali CTRL, ter hkrati z miško oznaèite uporabnike, za katere želite, da so spet dovoljeni.';

$lang['Unban_IP'] = 'Preklièi prepoved enega ali veè IP naslovov';
$lang['Unban_IP_explain'] = 'Veè IP naslovom preklièete prepoved naenkrat tako, da držite pritisnjeno tipko SHIFT ali CTRL ter hkrati z miško oznaèite naslove, za katere želite, da so spet dovoljeni';

$lang['Unban_email'] = 'Preklièi prepoved enega ali veè elektronskih naslovov';
$lang['Unban_email_explain'] = 'Veè elektronskim naslovom preklièete prepoved naenkrat tako, da držite pritisnjeno tipko SHIFT ali CTRL ter hkrati z miško oznaèite naslove, za katere želite, da so spet dovoljeni';

$lang['No_banned_users'] = 'Ni izkljuèenih uporabnikov';
$lang['No_banned_ip'] = 'Ni izkljuèenih IP naslovov';
$lang['No_banned_email'] = 'Ni izkljuèenih elektronskih naslovov';

$lang['Ban_update_sucessful'] = 'Seznam izkljuèitev je uspešno posodobljen';
$lang['Click_return_banadmin'] = 'Klikni %sTukaj%s za vrnitev na Nadzor prepovedi';


//
// Configuration
//
$lang['General_Config'] = 'Splošno oblikovanje';
$lang['Config_explain'] = 'Spodnji obrazec vam omogoèa prilagoditi splošne nastavitve vašega foruma. Za uporabnikove in forumove nastavitve uporabite povezave na levi strani zaslona.'; 

$lang['Click_return_config'] = 'Klikni %sTukaj%s za vrnitev na Splošno oblikovanje';

$lang['General_settings'] = 'Splošne nastavitve nadzorne plošèe';
$lang['Server_name'] = 'Domena_ime';
$lang['Server_name_explain'] = 'Ime Domene, na kateri ta forum teèe';
$lang['Script_path'] = 'Pot do skript (datotek)';
$lang['Script_path_explain'] = 'Pot, kjer so datoteke PhpBB2 od domene naprej';
$lang['Server_port'] = 'Vrata na strežniku';
$lang['Server_port_explain'] = 'Vrata, skozi katera prenaša vaš strežnik, obièajno 80. Spremeni samo, èe je drugaèe';
$lang['Site_name'] = 'Ime strani';
$lang['Site_desc'] = 'Opis strani';
$lang['Board_disable'] = 'Izkljuèi dostop';
$lang['Board_disable_explain'] = 'Tukaj forum za uporabnike zaprete. Ne odjavite se, dokler je nedostopen, ker se ne bi mogli spet prijaviti!';
$lang['Acct_activation'] = 'Raèun uporabnika posebej aktivira';
$lang['Acc_None'] = 'Nihèe'; // These three entries are the type of activation
$lang['Acc_User'] = 'Uporabnik';
$lang['Acc_Admin'] = 'Administrator';

$lang['Abilities_settings'] = 'Osnovne nastavitve za uporabnika in za forum';
$lang['Max_poll_options'] = 'Najveèje število izbir za anketo';
$lang['Flood_Interval'] = 'Presledek pošiljanja';
$lang['Flood_Interval_explain'] = 'Število sekund, kolikor naj uporabnik poèaka med dvema objavama'; 
$lang['Board_email_form'] = 'E-pošta uporabnikov preko plošèe';
$lang['Board_email_form_explain'] = 'Uporabniki si lahko pošiljajo elektronska sporoèila (email) preko plošèe foruma';
$lang['Topics_per_page'] = 'Število tem na prikazani strani';
$lang['Posts_per_page'] = 'Število prispevkov na prikazani strani';
$lang['Hot_threshold'] = 'Število objav za prikaz priljubljena tema';
$lang['Default_style'] = 'Privzeta grafièna podoba';
$lang['Override_style'] = 'Prekrij uporabnikovo nastavitev';
$lang['Override_style_explain'] = 'Zamenjava uporabnikove nastavitve s privzeto';
$lang['Default_language'] = 'Privzeti jezik';
$lang['Date_format'] = 'Oblika datuma';
$lang['System_timezone'] = 'Èasovni pas';
$lang['Enable_gzip'] = 'Omogoèi Gzip stiskanje';
$lang['Enable_prune'] = 'Omogoèi samodejno èišèenje foruma';
$lang['Allow_HTML'] = 'Dovoli HTML';
$lang['Allow_BBCode'] = 'Dovoli BBCode';
$lang['Allowed_tags'] = 'Dovoljene HTML oznake';
$lang['Allowed_tags_explain'] = 'Loèite oznake z vejicami';
$lang['Allow_smilies'] = 'Dovoli Smeške';
$lang['Smilies_path'] = 'Pot do Smeškov';
$lang['Smilies_path_explain'] = 'Pot iz vašega PhpBB korenskega imenika, npr. images/smiles';
$lang['Allow_sig'] = 'Dovoli podpise';
$lang['Max_sig_length'] = 'Najdaljša dolžina podpisa';
$lang['Max_sig_length_explain'] = 'Najveèje število èrk v podpisu';
$lang['Allow_name_change'] = 'Dovoli uporabnikove spremembe';

$lang['Avatar_settings'] = 'Nastavitve podob';
$lang['Allow_local'] = 'Omogoèi galerijo podob';
$lang['Allow_remote'] = 'Omogoèi oddaljene podobe';
$lang['Allow_remote_explain'] = 'Podobe so povezane z drugo spletno stranjo';
$lang['Allow_upload'] = 'Omogoèi nalaganje podob';
$lang['Max_filesize'] = 'Najveèja velikost datoteke podobe';
$lang['Max_filesize_explain'] = 'Za naložene datoteke podob';
$lang['Max_avatar_size'] = 'Najveèja dimenzija podob';
$lang['Max_avatar_size_explain'] = '(Višina x Širina v pikslih)';
$lang['Avatar_storage_path'] = 'Pot do shrambe podob';
$lang['Avatar_storage_path_explain'] = 'Pot od korenskega imenika PhpBB do shranjenih//////////////// podob, npr. images/avatars';
$lang['Avatar_gallery_path'] = 'Pot do galerije podob';
$lang['Avatar_gallery_path_explain'] = 'Pot od korenskega imenika PhpBB do prednaložene galerije podob, npr. images/avatars/gallery';

$lang['COPPA_settings'] = 'COPPA Nastavitve';
$lang['COPPA_fax'] = 'COPPA št. faksa';
$lang['COPPA_mail'] = 'COPPA poštni naslov';
$lang['COPPA_mail_explain'] = 'To je poštni naslov, na katerega bodo starši poslali COPPA obrazec za registracijo';

$lang['Email_settings'] = 'Nastavitve za elektronsko pošto';
$lang['Admin_email'] = 'Administratorjev elektronski naslov';
$lang['Email_sig'] = 'E-poštni podpis';
$lang['Email_sig_explain'] = 'Besedilo bo pripeto na vsa poslana elektronska sporoèila iz tega foruma';
$lang['Use_SMTP'] = 'Uporabi SMTP strežnik za elektronsko pošto';
$lang['Use_SMTP_explain'] = 'Oznaèi Da, èe želiš ali moraš poslati pošto preko imenovanega strežnika namesto lokalne poštne funkcije';
$lang['SMTP_server'] = 'Naslov SMTP strežnika';
$lang['SMTP_username'] = 'SMTP Uporabniško ime';
$lang['SMTP_username_explain'] = 'Samo èe vaš SMTP strežnik to zahteva, vnesite uporabniško ime';
$lang['SMTP_password'] = 'SMTP geslo';
$lang['SMTP_password_explain'] = 'Samo èe vaš SMTP strežnik to zahteva, vnesite geslo';

$lang['Disable_privmsg'] = 'Zasebna sporoèila';
$lang['Inbox_limits'] = 'Najveè sporoèil v mapi Prejeto';
$lang['Sentbox_limits'] = 'Najveè sporoèil v mapi Poslano';
$lang['Savebox_limits'] = 'Najveè sporoèil v mapi Shranjeno';

$lang['Cookie_settings'] = 'Nastavitve za piškotke'; 
$lang['Cookie_settings_explain'] = 'Te podrobnosti doloèajo, kako bodo piškotki poslani uporabnikovemu brskalniku. V veèini primerov bodo privzete vrednosti nastavitve za piškotke zadostovale, èe pa jih morate spreminjati, opravite to previdno -- nepravilne nastavitve prepreèijo uporabnikom prijavljanje.';
$lang['Cookie_domain'] = 'Domena za piškotek';
$lang['Cookie_name'] = 'Ime piškotka';
$lang['Cookie_path'] = 'Pot piškotka';
$lang['Cookie_secure'] = 'Zašèita piškotka';
$lang['Cookie_secure_explain'] = 'Èe vaš server teèe preko SSL, nastavi na Omogoèeno, drugaèe pusti kot Ni omogoèeno';
$lang['Session_length'] = 'Dolžina seje [ sekunde ]';


//
// Forum Management
//
$lang['Forum_admin'] = 'Administracija foruma';
$lang['Forum_admin_explain'] = 'S te plošèe lahko dodajate, brišete, urejate, preurejate in ponovno usklajujete zvrsti (kategorije) in forume';
$lang['Edit_forum'] = 'Uredi forum';
$lang['Create_forum'] = 'Ustvari nov forum';
$lang['Create_category'] = 'Ustvari novo zvrst (kategorijo)';
$lang['Remove'] = 'Odstrani';
$lang['Action'] = 'Dejanje';
$lang['Update_order'] = 'Posodobi zaporedje';
$lang['Config_updated'] = 'Oblika foruma je uspešno posodobljena';
$lang['Edit'] = 'Uredi';
$lang['Delete'] = 'Izbriši';
$lang['Move_up'] = 'Premakni GOR';
$lang['Move_down'] = 'Premakni DOL';
$lang['Resync'] = 'Znova uskladi';
$lang['No_mode'] = 'Noben naèin ni nastavljen';
$lang['Forum_edit_delete_explain'] = 'Obrazec spodaj vam omogoèa, da si priredite vse splošne možnosti plošèe. Za uporabnika in oblikovanje foruma uporabite ustrezne povezave na levi strani.';

$lang['Move_contents'] = 'Premakni vse vsebine';
$lang['Forum_delete'] = 'Izbriši forum';
$lang['Forum_delete_explain'] = 'Obrazec spodaj vam omogoèa brisanje foruma (ali kategorije) in izberite, kam boste odložili vse teme (ali forume), ki jih vsebujejo.';

$lang['Status_locked'] = 'Zaklenjeno';
$lang['Status_unlocked'] = 'Odklenjeno';
$lang['Forum_settings'] = 'Splošne nastavitve Foruma';
$lang['Forum_name'] = 'Ime Foruma';
$lang['Forum_desc'] = 'Opis';
$lang['Forum_status'] = 'Stanje Foruma';
$lang['Forum_pruning'] = 'Samodejno èišèenje, brisanje';

$lang['prune_freq'] = 'Preglej starost tem vsakih';
$lang['prune_days'] = 'Odstrani teme, v katere ni bilo še niè objavljeno v èasu';
$lang['Set_prune_data'] = 'Nastavili ste samodejno èišèenje za ta forum, vendar niste nastavili frekvence (pogostnosti) ali števila dni za èišèenje. Prosim, vrnite se in to naredite.';

$lang['Move_and_Delete'] = 'Premakni in odstrani';

$lang['Delete_all_posts'] = 'Izbriši vse prispevke';
$lang['Nowhere_to_move'] = 'Nimam kam premakniti'; 

$lang['Edit_Category'] = 'Uredi kategorijo';
$lang['Edit_Category_explain'] = 'Uporabi ta obrazec za spremembo imena kategorije.';

$lang['Forums_updated'] = 'Forum in prispevki so bili uspešno posodobljeni';

$lang['Must_delete_forums'] = 'Prej je treba odstraniti vse forume, šele  potem lahko izbrišete to kategorijo';

$lang['Click_return_forumadmin'] = 'Klikni %sTukaj%s za vrnitev na Administracijo foruma';


//
// Smiley Management
//
$lang['smiley_title'] = 'Pripomoèki za urejanje Smeškov';
$lang['smile_desc'] = 'Na tej strani lahko dodajate, odstranjujete ali urejate èustvene ikone in Smeške, ki jih lahko vaši uporabniki uporabljajo v prispevkih in zasebnih sporoèilih.';

$lang['smiley_config'] = 'Nastavitev Smeškov';
$lang['smiley_code'] = 'Smeškova koda';
$lang['smiley_url'] = 'Smeškova datoteka';
$lang['smiley_emot'] = 'Smeškovo èustvo';
$lang['smile_add'] = 'Dodaj novega Smeška';
$lang['Smile'] = 'Nasmeh';
$lang['Emotion'] = 'Èustvo';

$lang['Select_pak'] = 'Izberi paket (.pak) datoteko';
$lang['replace_existing'] = 'Zamenjaj tega Smeška';
$lang['keep_existing'] = 'Obdrži tega Smeška';
$lang['smiley_import_inst'] = 'Odzipati morate datoteko in naložiti vse datoteke v ustrezni imenik Smeškov, da jih boste namestili. Na tem obrazcu doloèite pravilne podatke, da boste uvozili paket Smeškov.';
$lang['smiley_import'] = 'Uvoz paketa Smeškov';
$lang['choose_smile_pak'] = 'Izberite paket Smeškov v .pak datoteki';
$lang['import'] = 'Uvozi Smeške';
$lang['smile_conflicts'] = 'Kaj storiti v primeru spora?';
$lang['del_existing_smileys'] = 'Odstrani obstojeèe Smeške pred uvozom';
$lang['import_smile_pack'] = 'Uvozi paket Smeškov';
$lang['export_smile_pack'] = 'Ustvari paket Smeškov';
$lang['export_smiles'] = 'Da bi ustvarili paket Smeškov iz trenutno namešèenih smeškov, kliknite %sTukaj%s za prenos .pak datoteke smeškov. Tako ustvarjena datoteka zagotovo ustvari konènico .pak datoteke.  Nato ustvarite Zip datoteko, ki naj vsebuje vse vaše slièice Smeškov plus to .pak oblikovano datoteko.';

$lang['smiley_add_success'] = 'Smeško je uspešno dodan';
$lang['smiley_edit_success'] = 'Smeško je uspešno posodobljen';
$lang['smiley_import_success'] = 'Paket Smeškov je bil uspešno uvožen!';
$lang['smiley_del_success'] = 'Smeško je uspešno odstranjen';
$lang['Click_return_smileadmin'] = 'Klikni %sTukaj%s za vrnitev na Nastavitev Smeškov';


//
// User Management
//
$lang['User_admin'] = 'Administracija uporabnikov';
$lang['User_admin_explain'] = 'Tu lahko spreminjate podatke in nekatere možnosti o uporabnikih. Za spreminjanje uporabnikovih pravic uporabite sistem za dovoljenja uporabnika in skupine.';

$lang['Look_up_user'] = 'Prikaži uporabnika';

$lang['Admin_user_fail'] = 'Uporabnikov profil ni posodobljen.';
$lang['Admin_user_updated'] = 'Uporabnikov profil je uspešno posodobljen.';
$lang['Click_return_useradmin'] = 'Klikni %sTukaj%s za vrnitev v Administracijo uporabnikov';

$lang['User_delete'] = 'Izbriši tega uporabnika';
$lang['User_delete_explain'] = 'Klikni tukaj za odstranitev tega uporabnika; tega ne boste mogli preklicati.';
$lang['User_deleted'] = 'Uporabnik je uspešno izbrisan.';

$lang['User_status'] = 'Je aktiven Uporabnik';
$lang['User_allowpm'] = 'Lahko pošilja zasebna sporoèila';
$lang['User_allowavatar'] = 'Lahko prikaže podobo';

$lang['Admin_avatar_explain'] = 'Tu lahko vidite in izbrišete uporabnikovo trenutno podobo.';

$lang['User_special'] = 'Posebna - samo adminstratorjeva polja';
$lang['User_special_explain'] = 'Teh polj uporabniki ne morejo prirejati. Tu lahko nastavite njihov položaj in druge možnosti, ki jih ne morejo sami.';


//
// Group Management
//
$lang['Group_administration'] = 'Administracija skupin';
$lang['Group_admin_explain'] = 'Od tu lahko skrbite za vse uporabniške skupine. Lahko odstranjujete, ustvarjate in urejate obstojeèe skupine. Izbirate lahko moderatorje, povezujete status odprtih/zaprtih skupin in doloèite ime skupine ter opis.';
$lang['Error_updating_groups'] = 'Napaka med posodabljanjem skupin';
$lang['Updated_group'] = 'Skupina je uspešno posodobljena';
$lang['Added_new_group'] = 'Nova skupina je uspešno ustvarjena';
$lang['Deleted_group'] = 'Skupina je uspešno izbrisana';
$lang['New_group'] = 'Ustvari novo skupino';
$lang['Edit_group'] = 'Uredi skupino';
$lang['group_name'] = 'Ime skupine';
$lang['group_description'] = 'Opis skupine';
$lang['group_moderator'] = 'Moderator skupine';
$lang['group_status'] = 'Status skupine';
$lang['group_open'] = 'Odprta skupina';
$lang['group_closed'] = 'Zaprta skupina';
$lang['group_hidden'] = 'Skrita skupina';
$lang['group_delete'] = 'Odstrani skupino';
$lang['group_delete_check'] = 'Odstrani to skupino';
$lang['submit_group_changes'] = 'Pošlji spremembe';
$lang['reset_group_changes'] = 'Razveljavi spremembe';
$lang['No_group_name'] = 'Za to skupino morate doloèiti ime';
$lang['No_group_moderator'] = 'Za to skupino morate doloèiti moderatorja';
$lang['No_group_mode'] = 'Doloèite naèin: naj bo odprta ali zaprta skupina?';
$lang['No_group_action'] = 'Nièesar niste izbrali';
$lang['delete_group_moderator'] = 'Naj prejšnjega moderatorja odstranim?';                                                                  
$lang['delete_moderator_explain'] = 'Èe spreminjate moderatorja skupine, v tem okencu oznaèite odstranitev prejšnjega moderatorja <b>iz skupine</b>.  Èe ne oznaèite, bo uporabnik postal obièajen èlan skupine.';
$lang['Click_return_groupsadmin'] = 'Klikni %sTukaj%s za vrnitev na Administracijo skupin.';
$lang['Select_group'] = 'Izberi skupino';
$lang['Look_up_group'] = 'Prikaži skupino';


//
// Prune Administration
//
$lang['Forum_Prune'] = 'Èišèenje foruma';
$lang['Forum_Prune_explain'] = 'S èišèenjem se bo odstranila katerakoli tema, za katero ni bilo nobenih prispevkov v številu dni, ki jih doloèite. Èe ne boste vstavili številke, <b>bodo izbrisane vse teme</b>. Ne bodo pa odstranjene teme, v katerih teèe anketa; prav tako ne bodo odstranjena Obvestila. Te teme boste morali odstraniti roèno.';
$lang['Do_Prune'] = 'Oèisti';
$lang['All_Forums'] = 'Vse forume';
$lang['Prune_topics_not_posted'] = 'Oèisti teme brez odgovorov v toliko dneh:';
$lang['Topics_pruned'] = 'Teme odstranjene';
$lang['Posts_pruned'] = 'Prispevki odstranjeni';
$lang['Prune_success'] = 'Forumi so oèišèeni';


//
// Word censor
//
$lang['Words_title'] = 'Cenzuriranje besed';
$lang['Words_explain'] = 'S te plošèe lahko dodajate, urejate in brišete besede, ki bodo avtomatièno nadzorovane na vaših forumih. Poleg tega se ljudje ne bodo mogli registrirati z uporabniškim imenom, ki bi vsebovalo te besede. Iskalne kartice (wildcards) (*) išèejo tudi v besednem polju. Na primer: *mest* bo zadeval tudi neumesten, mest* prepove tudi mesten, *mest bo izloèil pomest.';
$lang['Word'] = 'Beseda';
$lang['Edit_word_censor'] = 'Uredi cenzurirane besede';
$lang['Replacement'] = 'Zamenjaj';
$lang['Add_new_word'] = 'Dodaj novo besedo';
$lang['Update_word'] = 'Posodobi cenzurirane besede';

$lang['Must_enter_word'] = 'Vnesti morate besedo in njeno zamenjavo';
$lang['No_word_selected'] = 'Nobena beseda ni izbrana za urejanje';

$lang['Word_updated'] = 'Izbrana cenzurirana beseda je uspešno posodobljena.';
$lang['Word_added'] = 'Cenzurirana beseda je uspešno dodana';
$lang['Word_removed'] = 'Cenzurirana beseda je uspešno odstranjena';

$lang['Click_return_wordadmin'] = 'Klini %sTukaj%s za vrnitev na Administracijo cenzure';


//
// Mass Email
//
$lang['Mass_email_explain'] = 'Tukaj lahko pošljete sporoèilo bodisi vsem uporabnikom, ali pa samo doloèeni skupini uporabnikov. Sporoèilo bo odposlano na shranjene elektronske naslove kot enaka kopija za vse prejemnike. Èe pošiljate sporoèilo veliki skupini ljudi, bodite prosim potrpežljivi potem, ko pošljete. Nikar ne ustavljajte (ne spreminjajte) strani na pol poti. Normalno za množièno pošto je, da traja daljši èas. Obvešèeni boste, ko bo proces zakljuèen.';
$lang['Compose'] = 'Sestavi sporoèilo'; 

$lang['Recipients'] = 'Prejemniki'; 
$lang['All_users'] = 'Vsi uporabniki';

$lang['Email_successfull'] = 'Vaše sporoèilo je bilo poslano';
$lang['Click_return_massemail'] = 'Kliknite %sTukaj%s za vrnitev na obrazec za Množièno pošto';


//
// Ranks admin
//
$lang['Ranks_title'] = 'Administracija zaslužnosti';
$lang['Ranks_explain'] = 'S pomoèjo tega obrazca lahko dodajate, urejate, vidite ali brišete uporabniške stopnje. Lahko ustvarite poljubne stopnje, ki jih posvetite uporabniku in z lahkoto upravljate preko Administracije uporabnikov.';

$lang['Add_new_rank'] = 'Dodaj novo stopnjo';

$lang['Rank_title'] = 'Naziv stopnje';
$lang['Rank_special'] = 'Postavi kot posebno stopnjo';
$lang['Rank_minimum'] = 'Najmanj objav';
$lang['Rank_maximum'] = 'Najveè objav';
$lang['Rank_image'] = 'Slika za stopnjo (pot od PhpBB2 korenskega direktorija naprej)';
$lang['Rank_image_explain'] = 'Uporabi to, da doloèiš majhno slièico, ki bo povezana za oznaèevanje stopnje';

$lang['Must_select_rank'] = 'Izbrati morate stopnjo';
$lang['No_assigned_rank'] = 'Nobena posebna stopnja ni oznaèena';

$lang['Rank_updated'] = 'Stopnja je bila uspešno posodobljena';
$lang['Rank_added'] = 'Stopnja je bila uspešno dodana';
$lang['Rank_removed'] = 'Stopnja je bila uspešno izbrisana';
$lang['No_update_ranks'] = 'Stopnja je bila uspešno izbrisana. Pri uporabnikih, ki so to stopnjo imeli, se sprememba ne pozna. Tem uporabnikom morate stopnjo popraviti roèno na njihovih raèunih!';

$lang['Click_return_rankadmin'] = 'Klikni %sTukaj%s za vrnitev v Urejanje zaslužnosti';

              
//
// Disallow Username Admin
//
$lang['Disallow_control'] = 'Nadzor nedovoljenih uporabniških imen';
$lang['Disallow_explain'] = 'Tukaj lahko preverjate uporabniška imena, ki ne bodo dovoljena. Nedovoljena imena lahko navedete kot iskalno kartico (wildcard), ki vsebuje zvezdico (*). Vedite, da že registriranih uporabniških imen ne morete doloèiti za prepoved. V takem primeru morate najprej izbrisati to ime uporabnika, šele nato lahko ime prepoveste.';

$lang['Delete_disallow'] = 'Izbriši';
$lang['Delete_disallow_title'] = 'Odstrani nedovoljeno uporabniško ime';
$lang['Delete_disallow_explain'] = 'Nezaželeno uporabniško ime lahko odstranite tako, da ga izberete iz seznama in kliknete Pošlji';

$lang['Add_disallow'] = 'Dodaj';
$lang['Add_disallow_title'] = 'Dodaj nedovoljeno uporabniško ime';
$lang['Add_disallow_explain'] = 'Uporabniško ime lahko prepoveste z uporabo iskalne kartice (wildcard) z zvezdico, ki nadomesti katerokoli èrko';

$lang['No_disallowed'] = 'Ni nedovoljenih uporabniških imen';

$lang['Disallowed_deleted'] = 'Nedovoljeno uporabniško ime je bilo uspešno odstranjeno';
$lang['Disallow_successful'] = 'Nedovoljeno uporabniško ime je bilo uspešno dodano';
$lang['Disallowed_already'] = 'Uporabniškega imena, ki ste ga vnesli, ne morete prepovedati. Bodisi ime že obstaja na tem seznamu, bodisi je že navedeno na seznamu cenzuriranih besed ali pa je prisotno kot obstojeèe uporabniško ime.';

$lang['Click_return_disallowadmin'] = 'Klikni %sTukaj%s za vrnitev na urejanje Nedovoljenih Uporabniških imen';
                                                  

//
// Styles Admin
//
$lang['Styles_admin'] = 'Administracija izgleda';
$lang['Styles_explain'] = 'Na tej strani lahko dodajate, odstranjujete in urejate sloge (predloge in teme), ki so na voljo vašim uporabnikom.';
$lang['Styles_addnew_explain'] = 'Naslednji seznam vsebuje vse teme, ki ustrezajo kot predloge in jih imate trenutno na razpolago. Postavke na tem seznamu še niso bile namešèene v PhpBB bazo podatkov. Za namestitev teme preprosto kliknite instalacijsko povezavo poleg vnosa.';

$lang['Select_template'] = 'Izberite Predlogo';

$lang['Style'] = 'Slog';
$lang['Template'] = 'Predloga';
$lang['Install'] = 'Namesti';
$lang['Download'] = 'Prenesi';

$lang['Edit_theme'] = 'Uredi temo';
$lang['Edit_theme_explain'] = 'V obrazcu spodaj lahko urejate nastavitve za izbrano temo';

$lang['Create_theme'] = 'Ustvari temo';
$lang['Create_theme_explain'] = 'Uporabi spodnji obrazec za ustvarjanje nove teme v izbrani predlogi. Ko vnašate barve (vnos mora biti v šestmestnem zapisu), ne smete vkljuèevati zaèetnega znaka #, npr. CCCCCC je veljavno, #CCCCCC ni veljavno';

$lang['Export_themes'] = 'Izvozi Teme';
$lang['Export_explain'] = 'V tej plošèi lahko izvozite podatke za teme za neko izbrano predlogo. Izberite predlogo iz spodnjega seznama in skripte bodo ustvarile datoteko oblikovanja teme in poskusile shraniti v izbrani imenik za predloge. Èe se datoteka tako ne shrani sama, se vam bo prikazala možnost, da jo prenesete (download). V zaporedju skript za shranjevanje datoteke morate napisati dostop na spletni strežnik za izbrano predlogo dir. Za veè informacij si poglejte v PhpBB2 uporabniških navodilih (phpBB 2 Users guide).';

$lang['Theme_installed'] = 'Izbrana tema je bila uspešno namešèena';
$lang['Style_removed'] = 'Izbrani slog je bil odstranjen iz baze podatkov. Za popolno odstranitev tega sloga iz vašega sistema morate izbrisati ustrezni slog iz vašega direktorija predlog.';
$lang['Theme_info_saved'] = 'Informacije za izbrano temo kot predlogo so bile shranjene. Sedaj morate vrniti dovoljenja na samo-za-branje v datoteki theme__info.cfg (seveda morate pravilno izbrati imenik predlog, template directory)';
$lang['Theme_updated'] = 'Izbrana tema je bila posodobljena. Sedaj še izvozite nove nastavitve'; 
$lang['Theme_created'] = 'Tema je bila ustvarjena. Sedaj še izvozite temo in datoteko oblikovanja teme za varno shranjevanje ali za uporabo še kje drugje.';  

$lang['Confirm_delete_style'] = 'Ali ste preprièani, da želite izbrisati ta stil?'; 

$lang['Download_theme_cfg'] = 'Izvozni filter ne more ustvariti datoteke za informacijo teme. Kliknite na spodnji gumb, da prenesete datoteko z brskalnikom. Ko ste jo enkrat prenesli, jo lahko premaknete v imenik, ki vsebuje datoteke predloge. Potem lahko datoteke pakirate za distribucijo za uporabo kje drugje, èe boste to hoteli.';
$lang['No_themes'] = 'Predloga, ki ste jo izbrali, nima nobene prilepljene teme. Da bi ustvarili novo temo, kliknite na povezavo Ustvari novo na levi strani plošèe'; 
$lang['No_template_dir'] = 'Ne morem odpreti imenika s predlogami. Mogoèe ne obstaja, ali ni dosegljiv s spletnim strežnikom'; 
$lang['Cannot_remove_style'] = 'Ne morete odstraniti izbranega sloga, èe je trenutno oznaèen kot privzeti slog foruma. Prosim, zamenjajte privzeti slog in poskusite znova.'; 
$lang['Style_exists'] = 'Ime sloga, ki ste ga izbrali, že obstaja. Prosim, vrnite se nazaj in izberite drugo ime.'; 

$lang['Click_return_styleadmin'] = 'Kliknite %sTukaj% za vrnitev v Administracijo izgleda'; 

$lang['Theme_settings'] = 'Nastavitve tem'; 
$lang['Theme_element'] = 'Element teme';  
$lang['Simple_name'] = 'Preprosto ime'; 
$lang['Value'] = 'Vrednost'; 
$lang['Save_Settings'] = 'Shrani nastavitve';  

$lang['Stylesheet'] = 'CSS oblika sloga'; 
$lang['Background_image'] = 'Slika za ozadje'; 
$lang['Background_color'] = 'Barva ozadja'; 
$lang['Theme_name'] = 'Ime teme'; 
$lang['Link_color'] = 'Barva povezav (linkov)';  
$lang['Text_color'] = 'Barva pisave';  
$lang['VLink_color'] = 'Barva obiskanih povezav';  
$lang['ALink_color'] = 'Barva aktivne povezave';  
$lang['HLink_color'] = 'Barva lebdeèe povezave';  
$lang['Tr_color1'] = 'Barva Vrstice tabele 1';  
$lang['Tr_color2'] = 'Barva Vrstice tabele 2';  
$lang['Tr_color3'] = 'Barva Vrstice tabele 3';  
$lang['Tr_class1'] = 'Razred Vrstice tabele 1'; 
$lang['Tr_class2'] = 'Razred Vrstice tabele 2'; 
$lang['Tr_class3'] = 'Razred Vrstice tabele 3';  
$lang['Th_color1'] = 'Barva glave tabele 1';  
$lang['Th_color2'] = 'Barva glave tabele 2';  
$lang['Th_color3'] = 'Barva glave tabele 3';  
$lang['Th_class1'] = 'Razred glave tabele 1';  
$lang['Th_class2'] = 'Razred glave tabele 2'; 
$lang['Th_class3'] = 'Razred glave tabele 3'; 
$lang['Td_color1'] = 'Barva celice tabele 1'; 
$lang['Td_color2'] = 'Barva celice tabele 2'; 
$lang['Td_color3'] = 'Barva celice tabele 3'; 
$lang['Td_class1'] = 'Razred celice tabele 1'; 
$lang['Td_class2'] = 'Razred celice tabele 2'; 
$lang['Td_class3'] = 'Razred celice tabele 3'; 
$lang['fontface1'] = 'Videz pisave 1';   
$lang['fontface2'] = 'Videz pisave 2';   
$lang['fontface3'] = 'Videz pisave 3';   
$lang['fontsize1'] = 'Velikost pisave 1'; 
$lang['fontsize2'] = 'Velikost pisave 2'; 
$lang['fontsize3'] = 'Velikost pisave 3'; 
$lang['fontcolor1'] = 'Barva pisave 1'; 
$lang['fontcolor2'] = 'Barva pisave 2'; 
$lang['fontcolor3'] = 'Barva pisave 3'; 
$lang['span_class1'] = 'Razmik razred 1';  
$lang['span_class2'] = 'Razmik razred 2';  
$lang['span_class3'] = 'Razmik razred 3';  
$lang['img_poll_size'] = 'Velikost glasovalne slièice [px]'; 
$lang['img_pm_size'] = 'Velikost stanja zasebnega sporoèila [px]';  


//
// Install Process
//
$lang['Welcome_install'] = 'Dobrodošli pri PhpBB 2 namestitvi';
$lang['Initial_config'] = 'Osnovna konfiguracija';
$lang['DB_config'] = 'Konfiguracija baze podatkov';
$lang['Admin_config'] = 'Konfiguracija glavnega administratorja';
$lang['continue_upgrade'] = 'Potem ko ste prenesli vašo config datoteko na vaš lokalni raèunalnik, lahko\'Nadaljuj nadgradnjo\' gumb spodaj za premik naprej procesa posodobitve.  Prosim poèakaj, da se naloži config datoteka, dokler ne bo proces zakljuèen.';
$lang['upgrade_submit'] = 'Nadaljuj nadgradnjo';

$lang['Installer_Error'] = 'Med namešèanjem je prišlo do napake';
$lang['Previous_Install'] = 'Najdena je bila prejšnja namestitev';
$lang['Install_db_error'] = 'Pri poskusu posodobitve baze podatkov je prišlo do napake';

$lang['Re_install'] = 'Vaša prejšnja namestitev je še vedno aktivna.<br /><br />Èe želite ponovno namestiti PhpBB 2, kliknite Da gumb spodaj. Prosim, bodite pozorni: to lahko unièi vse obstojeèe podatke in varnostna kopija (backup) ne bo ustvarjena! Uporabniško ime administratorja in geslo, ki ste ga uporabljali za prijavo na plošèo, bo na novo ustvarjeno po ponovni namestitvi. Nobena druga nastavitev ne bo ostala.<br /><br />Skrbno premislite preden pritisnete Da!';

$lang['Inst_Step_0'] = 'Hvala da ste izbrali PhpBB 2. Med zakljuèevanjem namestitve je zdaj na vrsti, da, prosim, izpolnite vse podrobnosti, ki jih zahtevamo spodaj. Upoštevajte, prosim, da mora že obstajati baza podatkov, v katero namešèate. Èe namešèate v bazo podatkov, ki uporablja ODBC, npr. MS Access, morate, predno nadaljujete, zanjo najprej ustvariti DSN.';

$lang['Start_Install'] = 'Zaženi namestitev';
$lang['Finish_Install'] = 'Zakljuèi namestitev';

$lang['Default_lang'] = 'Privzeti jezik plošèe';
$lang['DB_Host'] = 'Ime gostitelja strežnika Baze podatkov / DSN';
$lang['DB_Name'] = 'Ime vaše Baze podatkov';
$lang['DB_Username'] = 'Uporabniško ime Baze podatkov';
$lang['DB_Password'] = 'Geslo Baze podatkov';
$lang['Database'] = 'Vaša Baza podatkov';
$lang['Install_lang'] = 'Izberite jezik za namestitev';
$lang['dbms'] = 'Tip baze podatkov';
$lang['Table_Prefix'] = 'Prefiks za tabele v bazi podatkov';
$lang['Admin_Username'] = 'Administratorjevo Uporabniško ime';
$lang['Admin_Password'] = 'Administratorjevo Geslo';
$lang['Admin_Password_confirm'] = 'Administratorjevo Geslo [ Potrdi ]';

$lang['Inst_Step_2'] = 'Vaše admin uporabniško ime je bilo ustvarjeno.  Pri tej toèki je vaša osnovna namestitev zakljuèena. Sedaj boste prestavljeni na zaslon, ki vam bo omogoèil urediti vašo novo namestitev. Prosim, skrbno preverite podrobnosti splošne nastavitve in spremenite, karkoli bi bilo potrebno. Hvala vam za izbiro PhpBB 2.';

$lang['Unwriteable_config'] = 'Vaša datoteka config je sedaj neprepisljiva. Kopija config datoteke bo prenešena na vaš raèunalnik, ko boste kliknili spodnji gumb. To datoteko morate naložiti v isti imenik, kot je PhpBB 2. Ko je to enkrat narejeno, se morate prijaviti z uporabo administratorjevega imena in gesla, ki ste ju doloèili v prejšnjem obrazcu. Obišèite administratorjev nadzorni center (Administrativni kotièek - povezava je vidna na dnu vsake strani potem ko ste prijavljeni), da preverite splošne nastavitve. Hvala, da ste izbrali PhpBB 2.';
$lang['Download_config'] = 'Prenos (Download) Config';

$lang['ftp_choose'] = 'Izberite naèin prenosa';
$lang['ftp_option'] = '<br />Odkar so FTP podaljški omogoèeni v tej verziji PHP, ste deležni tudi možnosti, da najprej poskusite avtomatièno FTP-prenesti config datoteko na svoje mesto.';
$lang['ftp_instructs'] = 'Izbrali ste  avtomatièni FTP prenos datoteke na raèun, ki vsebuje PhpBB 2. Prosim, vnesite podatke spodaj, da omogoèite ta postopek. Pazite da bo FTP pot natanèna pot preko FTP do namešèenega PhpBB2, kot ko ste tja obièajno FTP-irali kot odjemalec.';
$lang['ftp_info'] = 'Vnesite svoje FTP podatke';
$lang['Attempt_ftp'] = 'Poskus za FTP prenos config datoteko na svoje mesto';
$lang['Send_file'] = 'Samo pošlji datoteko meni in jaz jo (bom) FTP prenesel roèno';
$lang['ftp_path'] = 'FTP pot do PhpBB 2';
$lang['ftp_username'] = 'Vaše FTP Uporabniško ime';
$lang['ftp_password'] = 'Vaše FTP Geslo';
$lang['Transfer_config'] = 'Zaèni prenos';
$lang['NoFTP_config'] = 'Poskus za FTP prenos config datoteke na svoje mesto je spodletel.  Prosim, prenesi (download) config datoteko in FTP-iraj jo na svoje mesto roèno.';

$lang['Install'] = 'Namesti';
$lang['Upgrade'] = 'Posodobi';


$lang['Install_Method'] = 'Izberi namestitveni naèin';

$lang['Install_No_Ext'] = 'PHP konfiguracija na vašem strežniku ne podpira tipa baze podatkov, ki ste jo izbrali';

$lang['Install_No_PCRE'] = 'PhpBB2 zahteva Perl-Compatible Regular Expressions Module za PHP, ki ga vaša PHP konfiguracija ne pokaže kot podprto!';

//
// That's all Folks!
// -------------------------------------------------

?>