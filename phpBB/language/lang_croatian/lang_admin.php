<?php

/***************************************************************************
 *                            lang_admin.php [Croatian]
 *                              -------------------
 *     begin                : Monday Dec 01 2002 
 *     copyright            : (C) 2002 Hrvoje Stankov
 *     email                : hrvoje@spirit.hr
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
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = 'Opæenito';
$lang['Users'] = 'Korisnici';
$lang['Groups'] = 'Grupe';
$lang['Forums'] = 'Forumi';
$lang['Styles'] = 'Stilovi';

$lang['Configuration'] = 'Konfiguriranje';
$lang['Permissions'] = 'Dozvole';
$lang['Manage'] = 'Upravljanje';
$lang['Disallow'] = 'Zabranjena imena';
$lang['Prune'] = 'Pojednostavljenje';
$lang['Mass_Email'] = 'Masovni Email';
$lang['Ranks'] = 'Pozicije';
$lang['Smilies'] = 'Smiley';
$lang['Ban_Management'] = 'Kontrola zabrana';
$lang['Word_Censor'] = 'Cenzurirane rijeèi';
$lang['Export'] = 'Iznesi';
$lang['Create_new'] = 'Napravi';
$lang['Add_new'] = 'Dodaj';
$lang['Backup_DB'] = 'Backup baze';
$lang['Restore_DB'] = 'Povrati bazu';


//
// Index
//
$lang['Admin'] = 'Admin';
$lang['Not_admin'] = 'Nemate ovlaštenja da administrirate forume';
$lang['Welcome_phpBB'] = 'Dobrodošli na phpBB';
$lang['Admin_intro'] = 'Hvala vam što ste izabrali phpBB kao rješenje za vaš forum. Na ovom ekranu imate brz pregled raznih statistika vaših foruma. Na ovu stranicu se možete vratiti klikom na <u>Admin Indeks</u> link na lijevom panelu. Za povratak na indeks foruma, kliknite na phpBB logo takoðer na lijevom panelu. Ostali linkovi na lijevom panelu dozvoliæe vam da kontrolirate svako obilježje vašeg foruma, a svaki ekran ima uputstvo kako da koristite alate.';
$lang['Main_index'] = 'Indeks foruma';
$lang['Forum_stats'] = 'Statistike foruma';
$lang['Admin_Index'] = 'Admin index';
$lang['Preview_forum'] = 'Pregled foruma';

$lang['Click_return_admin_index'] = 'Kliknite %sovdje%s za povratak na Admin Indeks';

$lang['Statistic'] = 'Statistika';
$lang['Value'] = 'Vrijednost';
$lang['Number_posts'] = 'Broj poruka';
$lang['Posts_per_day'] = 'Broj poruka dnevno';
$lang['Number_topics'] = 'Broj tema';
$lang['Topics_per_day'] = 'Broj tema dnevno';
$lang['Number_users'] = 'Broj korisnika';
$lang['Users_per_day'] = 'Broj korisnika dnevno';
$lang['Board_started'] = 'Forum je poèeo';
$lang['Avatar_dir_size'] = 'Velièina direktorija Avatar';
$lang['Database_size'] = 'Velièina baze';
$lang['Gzip_compression'] ='Gzip kompresija';
$lang['Not_available'] = 'Nije dostupno';

$lang['ON'] = 'Ukljuèeno'; // This is for GZip compression
$lang['OFF'] = 'Iskljuèeno'; 


//
// DB Utils
//
$lang['Database_Utilities'] = 'Alati za bazu';

$lang['Restore'] = 'Povrati';
$lang['Backup'] = 'Backup';
$lang['Restore_explain'] = 'Ovim æete izvršiti potpuno vraæanje svih phpBB tabela iz snimljne datoteke. Ako to vaš server podržava možete poslati gzip zapakiranu tekst datoteku koja æe biti automatski otpakirana. <b>UPOZORENJE:</b> Ovim æete prepisati postojeæe podatke. Proces može potrajati dugo pa vas molimo da ostanete na ovoj stranici dok se operacija ne završi.';
$lang['Backup_explain'] = 'Ovdje možete napraviti backup za sve vaše phpBB podatke. Ako imate bilo kakve dodatne tabele u istoj bazi sa phpBB za koje biste željeli napraviti backup, molimo vas da unesete njihova imena odvojena zarezima u polje Dodatne tabele ispod. Ako to vaš server podržava možete koristiti gzip kompresiju da biste smanjili velièinu datoteke prije downloada.';

$lang['Backup_options'] = 'Backup opcije';
$lang['Start_backup'] = 'Pokreni backup';
$lang['Full_backup'] = 'Potpuni backup';
$lang['Structure_backup'] = 'Backup samo strukture';
$lang['Data_backup'] = 'Backup svih podataka';
$lang['Additional_tables'] = 'Dodatne tabele';
$lang['Gzip_compress'] = 'Gzip zapakirana datoteka';
$lang['Select_file'] = 'Izaberite datoteku';
$lang['Start_Restore'] = 'Startaj povratak';

$lang['Restore_success'] = 'Baza je uspješno vraæena.<br /><br />Vaš forum bi trebao biti u stanju u kakvom je bio kada je napravljen backup.';
$lang['Backup_download'] = 'Download æe poèeti brzo - molimo vas da saèekate poèetak';
$lang['Backups_not_supported'] = 'Isprièavamo se ali backup baze trenutno nije podržan za vaš sistem baze.';

$lang['Restore_Error_uploading'] = 'Greška pri slanju backup datoteke';
$lang['Restore_Error_filename'] = 'Problem sa imenom datoteke, probajte neku drugu';
$lang['Restore_Error_decompress'] = 'Ne mogu raspakirati gzip datoteku, molim vas pošaljite klasiènu tekst verziju';
$lang['Restore_Error_no_file'] = 'Nijedna datoteka nije poslana';


//
// Auth pages
//
$lang['Select_a_User'] = 'Izaberi korisnika';
$lang['Select_a_Group'] = 'Izaberi grupu';
$lang['Select_a_Forum'] = 'Izaberi forum';
$lang['Auth_Control_User'] = 'Kontrola dozvola korisnika'; 
$lang['Auth_Control_Group'] = 'Kontrola dozvola grupa'; 
$lang['Auth_Control_Forum'] = 'Kontrola dozvola foruma'; 
$lang['Look_up_User'] = 'Potraži korisnika'; 
$lang['Look_up_Group'] = 'Potraži grupu'; 
$lang['Look_up_Forum'] = 'Potraži forum'; 

$lang['Group_auth_explain'] = 'Ovdje možete izmijeniti dozvole i ureðivati status dodjeljen svakoj grupi. Ne zaboravite da kad mijenjate dozvole grupama individualne dozvole korisnika još uvijek omoguæavaju korisniku ulaz na forume itd. Ako se to desi biti æete upozoreni u svakom sluèaju.';
$lang['User_auth_explain'] = 'Ovdje možete izmijeniti dozvole i ureðivati status dodjeljen svakom korisniku. Ne zaboravite da kad mijenjate dozvole korisnicima individualne dozvole grupama još uvijek omoguæavaju korisniku ulaz na forume itd. U tom sluèaju biti æete upozoreni.';
$lang['Forum_auth_explain'] = 'Ovdje možete izmijeniti nivoe pristupa svakom forumu. Imate i jednostavanu i proširenu metodu, s tim da proširena metoda nudi veæu kontrolu svake operacije na forumu. Zapamtite da æete promjenom nivoa pristupa forumima odrediti koji korisnici mogu izvršavati razne operacije na njima.';

$lang['Simple_mode'] = 'Jednostavan naèin';
$lang['Advanced_mode'] = 'Napredni naèin';
$lang['Moderator_status'] = 'Status urednika';

$lang['Allowed_Access'] = 'Dozvoljen pristup';
$lang['Disallowed_Access'] = 'Zabranjen pristup';
$lang['Is_Moderator'] = 'Urednik';
$lang['Not_Moderator'] = 'Nije urednik';

$lang['Conflict_warning'] = 'Upozorenje o sukobu dozvola';
$lang['Conflict_access_userauth'] = 'Ovaj korisnik ima pristupna prava u ovom forumu preko èlanstva u grupi. Možda æete željeti izmijeniti dozvole grupa ili izbaciti korisnika iz grupe da bi mu u potpunosti ukinuli prava na pristup. Garantirana prava grupe (ukljuèujuæi i umiješane forume) su prikazana ispod.';
$lang['Conflict_mod_userauth'] = 'Ovaj korisnik ima pristupna prava urednika u ovom forumu preko èlanstva u grupi. Možda æete željeti izmijeniti dozvole grupa ili izbaciti korisnika iz grupe da bi mu u potpunosti ukinuli prava na urednièki pristup. Garantirana prava grupe (ukljuèujuæi i umiješane forume) su prikazana ispod.';

$lang['Conflict_access_groupauth'] = 'Sljedeæi korisnik (ili korisnici) još uvijek imaju pravo pristupa na ovaj forum putem korisnièkih dozvola. Možda æete željeti izmijeniti dozvole korisnika da bi mu u potpunosti ukinuli prava na pristup. Garantirana prava korisnika (ukljuèujuæi i umiješane forume) su prikazana ispod.';
$lang['Conflict_mod_groupauth'] = 'Sljedeæi korisnik (ili korisnici) još uvijek imaju pravo urednièkog pristupa na ovaj forum putem korisnièkih dozvola. Možda æete želeti izmijeniti dozvole korisnika da bi mu u potpunosti ukinuli urednièka prava. Garantovana prava korisnika (ukljuèujuæi i umiješane forume) su prikazana ispod.';

$lang['Public'] = 'Javni';
$lang['Private'] = 'Privatni';
$lang['Registered'] = 'Registrirani';
$lang['Administrators'] = 'Administratori';
$lang['Hidden'] = 'Skriveni';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = 'SVI';
$lang['Forum_REG'] = 'REGISTRIRANI';
$lang['Forum_PRIVATE'] = 'PRIVATNI';
$lang['Forum_MOD'] = 'MODERATORI';
$lang['Forum_ADMIN'] = 'ADMINISTRATORI';

$lang['View'] = 'Pogledaj';
$lang['Read'] = 'Proèitaj';
$lang['Post'] = 'Pošalji';
$lang['Reply'] = 'Odgovori';
$lang['Edit'] = 'Izmijeni';
$lang['Delete'] = 'Obriši';
$lang['Sticky'] = 'Ljepljiva';
$lang['Announce'] = 'Obavijesti'; 
$lang['Vote'] = 'Glasaj';
$lang['Pollcreate'] = 'Napravi glasanje';

$lang['Permissions'] = 'Dozvole';
$lang['Simple_Permission'] = 'Jednostavna dozvola';

$lang['User_Level'] = 'Nivo korisnika'; 
$lang['Auth_User'] = 'Korisnik';
$lang['Auth_Admin'] = 'Administrator';
$lang['Group_memberships'] = 'Èlanstvo u grupi';
$lang['Usergroup_members'] = 'Ova grupa ima sljedeæe èlanove';

$lang['Forum_auth_updated'] = 'Dozvole foruma su izmijenjene';
$lang['User_auth_updated'] = 'Dozvole korisnika su izmijenjene';
$lang['Group_auth_updated'] = 'Dozvole grupa su izmijenjene';

$lang['Auth_updated'] = 'Dozvole su izmijenjene';
$lang['Click_return_userauth'] = 'Kliknite %sovdje%s za povratak na Dozvole korisnika';
$lang['Click_return_groupauth'] = 'Kliknite %sOvdje%s za povratak na Dozvole grupa';
$lang['Click_return_forumauth'] = 'Kliknite %sovdje%s za povratak na Dozvole foruma';


//
// Banning
//
$lang['Ban_control'] = 'Kontrola zabrane';
$lang['Ban_explain'] = 'Odavde možete kontrolirati zabrane korisnicima. Možete zabraniti pojedinaèno ili zajedno pojedinaènog ili specifiènog korisnika ili opseg IP adresa ili imena hostova. Ovi naèini sprjeèavaju korisnika da pristupi index stranici vašeg foruma. Da biste sprjeèili korisnika da se registrira koristeæi drugo korisnièko ime možete odrediti zabranu putem email adrese. Znajte da blokiranjem email adrese neæe sprjeèiti korisnika da se logira ili šalje poruke na vaš forum, tako da bi trebali koristiti jednu od prve dvije metode.';
$lang['Ban_explain_warn'] = 'Znajte da æe unošenjem opsega IP adresa sve adrese od poèetne do krajnje biti dodane na listu blokiranih adresa. Pokušajte minimalizirati broj dodanih adresa u bazu unoseæi jokera (*) gdje god je to moguæe. Ako stvarno morate blokirati opseg adresa gledajte da bude što manji.';

$lang['Select_username'] = 'Izaberite korisnièko ime';
$lang['Select_ip'] = 'Izaberite IP';
$lang['Select_email'] = 'Izaberite email adresu';

$lang['Ban_username'] = 'Zabrani jednog ili više korisnika';
$lang['Ban_username_explain'] = 'Možete zabraniti više korisnika u jednom prolazu koristeæi odgovarajuæu kombinaciju miša i tastature za vaše raèunalo i internet preglednik.';

$lang['Ban_IP'] = 'Zabrani jednu ili više IP adresa ili imena hostova';
$lang['IP_hostname'] = 'IP adrese ili ime hostova';
$lang['Ban_IP_explain'] = 'Da biste izabrali više razlièitih IP-a ili imena hostova odvojite ih zarezima. Da bi ste odredili opseg IP adresa odvojite poèetnu i krajnju sa crticom (-), a za jokera koristite *';

$lang['Ban_email'] = 'Zabrani jednu ili više email adresa';
$lang['Ban_email_explain'] = 'Da biste izbrali više od jedne email adrese odvojite ih zarezom. Za jokera koristite *, na primjer *@hotmail.com';

$lang['Unban_username'] = 'Ukloni zabranu za jednog ili više korisnika';
$lang['Unban_username_explain'] = 'Možete ukloniti zabranu više korisnika u jednom prolazu koristeæi odgovarajuæu kombinaciju miša i tastature za vaše raèunalo i internet preglednik';

$lang['Unban_IP'] = 'Ukloni zabranu za jednu ili više IP adresa';
$lang['Unban_IP_explain'] = 'Možete ukloniti zabranu više IP adresa u jednom prolazu koristeæi odgovarajuæu kombinaciju miša i tastature za vaše raèunalo i internet preglednik';

$lang['Unban_email'] = 'Ukloni zabranu za jednu ili više email adresa';
$lang['Unban_email_explain'] = 'Možete ukloniti zabranu više email adresa u jednom prolazu koristeæi odgovarajuæu kombinaciju miša i tastature za vaše raèunalo i internet preglednik';

$lang['No_banned_users'] = 'Nema zabranjenih korisnika';
$lang['No_banned_ip'] = 'Nema zabranjenih  IP adresa';
$lang['No_banned_email'] = 'Nema zabranjenih email adresa';

$lang['Ban_update_sucessful'] = 'Lista zabrana je uspješno osvježena';
$lang['Click_return_banadmin'] = 'Kliknite %sovdje%s za povratak na Kontrolu zabrana';


//
// Configuration
//
$lang['General_Config'] = 'Generalna konfiguracija';
$lang['Config_explain'] = 'Formular ispod omoguæit æe vam mijenjanje svih generalnih opcija foruma. Za konfiguracije korisnika i foruma koristite linkove na panelu sa lijeve strane.';

$lang['Click_return_config'] = 'Kliknite %sovdje%s za povratak na Generalnu konfiguraciju';

$lang['General_settings'] = 'Generalna podešavanja foruma';
$lang['Server_name'] = 'Ime servera';
$lang['Server_name_explain'] = 'Ime servera(naziv domene) sa koje se forumi pokreæu';
$lang['Script_path'] = 'Put do skripte';
$lang['Script_path_explain'] = 'Put gdje je phpBB2 lociran u odnosu na ime servera';
$lang['Server_port'] = 'Port servera';
$lang['Server_port_explain'] = 'Port na kojemu radi vaš server, obièno 80, promjenite samo ako je drugaèije';
$lang['Site_name'] = 'Naziv site-a';
$lang['Site_desc'] = 'Opis site-a';
$lang['Board_disable'] = 'Iskljuèi forum';
$lang['Board_disable_explain'] = 'Forum neæe biti dostupan korisnicima. Nemojte se odjavljivati kada iskljuèujete forum, jer neæete biti u moguænosti da se ponovo prijavite!';
$lang['Acct_activation'] = 'Omoguæi aktivaciju naloga';
$lang['Acc_None'] = 'Bez aktivacije'; // These three entries are the type of activation
$lang['Acc_User'] = 'Korisnik';
$lang['Acc_Admin'] = 'Administrator';

$lang['Abilities_settings'] = 'Osnovna podešavanja za korisnika i forum';
$lang['Max_poll_options'] = 'Maksimalni broj opcija za glasanje';
$lang['Flood_Interval'] = 'Interval za flood';
$lang['Flood_Interval_explain'] = 'Broj sekundi koje korisnik mora prièekati izmeðu dvije poruke'; 
$lang['Board_email_form'] = 'Korisnik piše putem foruma';
$lang['Board_email_form_explain'] = 'Moguænost da korisnici jedni drugima šalju email putem foruma';
$lang['Topics_per_page'] = 'Tema po stranici';
$lang['Posts_per_page'] = 'Poruka po stranici';
$lang['Hot_threshold'] = 'Koliko poruka èuvati kao popularne';
$lang['Default_style'] = 'Podrazumijevani stil';
$lang['Override_style'] = 'Pregazi korisnièki stil';
$lang['Override_style_explain'] = 'Zamjenjuje korisnièki definiran stil sa podrazumijevanim';
$lang['Default_language'] = 'Podrazumijevani jezik';
$lang['Date_format'] = 'Format datuma';
$lang['System_timezone'] = 'Sistemska vremenska zona';
$lang['Enable_gzip'] = 'Omoguæi Gzip kompresiju';
$lang['Enable_prune'] = 'Omoguæi pojednostavljenje foruma';
$lang['Allow_HTML'] = 'Dozvoli HTML';
$lang['Allow_BBCode'] = 'Dozvoli BBCode';
$lang['Allowed_tags'] = 'Dozvoljeni HTML tagovi';
$lang['Allowed_tags_explain'] = 'Odvojite tagove zarezima';
$lang['Allow_smilies'] = 'Dozvoli smiley-e';
$lang['Smilies_path'] = 'Putanja za smještanje smiley-e';
$lang['Smilies_path_explain'] = 'Putanja ispod vašeg phpBB root direktorija, npr. images/smiles';
$lang['Allow_sig'] = 'Dozvoli potpise';
$lang['Max_sig_length'] = 'Maksimalna dužina potpisa';
$lang['Max_sig_length_explain'] = 'Maksimalni broj slova u potpisu korisnika';
$lang['Allow_name_change'] = 'Dozvoli promjene korisnièkog imena';

$lang['Avatar_settings'] = 'Podešavanje avatara';
$lang['Allow_local'] = 'Omoguæi galeriju avatara';
$lang['Allow_remote'] = 'Omoguæi udaljene avatare';
$lang['Allow_remote_explain'] = 'Avatari linkani na druge web stranice';
$lang['Allow_upload'] = 'Omoguæi slanje avatara';
$lang['Max_filesize'] = 'Maksimalna velièina datoteke za avatar';
$lang['Max_filesize_explain'] = 'Za poslane avatare';
$lang['Max_avatar_size'] = 'Maksimalne dimenzije avatara';
$lang['Max_avatar_size_explain'] = '(Visina x širina u pikselima)';
$lang['Avatar_storage_path'] = 'Put za smještanje avatara';
$lang['Avatar_storage_path_explain'] = 'Put ispod vašeg phpBB root direktorija, npr. images/avatars';
$lang['Avatar_gallery_path'] = 'Put do avatar galerije';
$lang['Avatar_gallery_path_explain'] = 'Put ispod vašeg phpBB root direktorija za unaprijed uèitane slike, npr. images/avatars/gallery';

$lang['COPPA_settings'] = 'COPPA podešavanja';
$lang['COPPA_fax'] = 'COPPA Broj faksa';
$lang['COPPA_mail'] = 'COPPA poštanska adresa';
$lang['COPPA_mail_explain'] = 'Ovo je poštanska adresa gde æe roditelji slati COPPA registracijske formulare';

$lang['Email_settings'] = 'Email podešavanja';
$lang['Admin_email'] = 'Administratorova email adresa';
$lang['Email_sig'] = 'Email potpis';
$lang['Email_sig_explain'] = 'Ovaj tekst æe biti ukljuèen u sve emailove koje forum pošalje';
$lang['Use_SMTP'] = 'Koristi SMTP server za email';
$lang['Use_SMTP_explain'] = 'Izaberite Da ukoliko želite slati poruke putem odreðenog servera umjesto lokalne funkcije';
$lang['SMTP_server'] = 'Adresa SMTP servera';
$lang['SMTP_username'] = 'SMTP korisnièko ime';
$lang['SMTP_username_explain'] = 'Korisnièko ime unesite samo ako to vaš SMTP server zahtjeva';
$lang['SMTP_password'] = 'SMTP lozinka';
$lang['SMTP_password_explain'] = 'Lozinku unesite samo ako to vaš SMTP server zahtjeva';

$lang['Disable_privmsg'] = 'Privatne poruke';
$lang['Inbox_limits'] = 'Maksimalo poruka u Sanduèiæu';
$lang['Sentbox_limits'] = 'Maksimalno poruka u Sanduku za slanje';
$lang['Savebox_limits'] = 'Maksimalno poruka u Snimljeno';

$lang['Cookie_settings'] = 'Podešavanje kolaèiæa'; 
$lang['Cookie_settings_explain'] = 'Ovi detalji definiraju kako se kolaèiæi šalju vašim korisnicima. Najèešæe je podrazumjevana vrijednost dovoljna ali ako trebate nešto mijenjati radite to pažljivo, jer pogrešno podešavanje može sprjeèiti korisnike da se prijave';
$lang['Cookie_domain'] = 'Domena kolaèiæa';
$lang['Cookie_name'] = 'Ime kolaèiæa';
$lang['Cookie_path'] = 'Put kolaèiæa';
$lang['Cookie_secure'] = 'Sigurnost kolaèiæa';
$lang['Cookie_secure_explain'] = 'Ako vaš server radi preko SSL-a podesite ovu opciju na Dozvoljeno a u suprotnom ostavite kao zabranjeno';
$lang['Session_length'] = 'Dužina sessiona [ sekunde ]';


//
// Forum Management
//
$lang['Forum_admin'] = 'Administracija foruma';
$lang['Forum_admin_explain'] = 'Odavde možete dodati, brisati, izmjeniti, preurediti i resinhronizirati kategorije i forume';
$lang['Edit_forum'] = 'Izmijeni forum';
$lang['Create_forum'] = 'Napravi nov forum';
$lang['Create_category'] = 'Napravi novu kategoriju';
$lang['Remove'] = 'Ukloni';
$lang['Action'] = 'Akcija';
$lang['Update_order'] = 'Osvježi redosljed';
$lang['Config_updated'] = 'Konfiguracija foruma je uspješno osvježena';
$lang['Edit'] = 'Izmijeni';
$lang['Delete'] = 'Izbriši';
$lang['Move_up'] = 'Pomakni gore';
$lang['Move_down'] = 'Pomakni dole';
$lang['Resync'] = 'Ponovo sinhroniziraj';
$lang['No_mode'] = 'Nije odreðen naèin';
$lang['Forum_edit_delete_explain'] = 'Formular ispod æe vam omoguæiti da izmijenite sve generalne opcije foruma. Za konfiguracije korisnika i foruma koristite linkove na lijevom panelu';

$lang['Move_contents'] = 'Pomakni sav sadržaj';
$lang['Forum_delete'] = 'Izbriši forum';
$lang['Forum_delete_explain'] = 'Formular ispod æe vam omoguæiti da izbrišete forum (ili kategoriju) i odluèite gdje hoæete smjestiti sve teme (ili forume) koji su sadržani.';

$lang['Status_locked'] = 'Zakljuèan';
$lang['Status_unlocked'] = 'Otkljuèan';
$lang['Forum_settings'] = 'Generalna podešavanja foruma';
$lang['Forum_name'] = 'Naziv foruma';
$lang['Forum_desc'] = 'Opis';
$lang['Forum_status'] = 'Status foruma';
$lang['Forum_pruning'] = 'Automatsko smanjivanje';

$lang['prune_freq'] = 'Provjeri starost teme svakih';
$lang['prune_days'] = 'Pomakni teme u kojima nije pisano';
$lang['Set_prune_data'] = 'Ukljuèili ste automatsko smanjivanje za ovaj forum ali niste odredili uèestalost ili broj dana do smanjivanja. Molim vas da se vratite i to uèinite';

$lang['Move_and_Delete'] = 'Pomakni i izbriši';

$lang['Delete_all_posts'] = 'Izbriši sve poruke';
$lang['Nowhere_to_move'] = 'Takoðer se nemam kamo pomaknuti';

$lang['Edit_Category'] = 'Izmijeni kategoriju';
$lang['Edit_Category_explain'] = 'Koristite ovaj formular da biste izmjenili naziv kategorije.';

$lang['Forums_updated'] = 'Informacije o forumu i kategoriji su uspešno osvježene';

$lang['Must_delete_forums'] = 'Morat æete obrisati sve forume pre nego što obrišete kategoriju';

$lang['Click_return_forumadmin'] = 'Kliknite %sovdje%s za povratak na Administraciju foruma';


//
// Smiley Management
//
$lang['smiley_title'] = 'Ureðivanje smailey-a';
$lang['smile_desc'] = 'Odavde možete dodati, izbrisati i ureðivati emotivne ikonice ili smajlije koje vaši korisnici mogu koristiti u porukama kao i privatnim porukama.';

$lang['smiley_config'] = 'Konfiguracija smiley-a';
$lang['smiley_code'] = 'Kod smiley-a';
$lang['smiley_url'] = 'Smiley datoteka';
$lang['smiley_emot'] = 'Smiley emocija';
$lang['smile_add'] = 'Dodaj novi smiley';
$lang['Smile'] = 'Smiley';
$lang['Emotion'] = 'Emocija';

$lang['Select_pak'] = 'Izaberite paket (.pak) datoteku';
$lang['replace_existing'] = 'Zamjeni postojeæi smiley';
$lang['keep_existing'] = 'Saèuvaj postojeæi smiley';
$lang['smiley_import_inst'] = 'Trebalo bi raspakirati zapakirane smiley-e i poslati sve datoteke u odgovarajuæi smiley direktorij za vašu instalaciju. Onda izaberite toènu informaciju u ovom formularu da bi ste ubacili pakiranje.';
$lang['smiley_import'] = 'Uvezi pakirane smiley-e';
$lang['choose_smile_pak'] = 'Izaberite smiley paket .pak datoteku';
$lang['import'] = 'Uvezi smiley-e';
$lang['smile_conflicts'] = 'Šta bi trebalo uèiniti u sluèaju sukoba';
$lang['del_existing_smileys'] = 'Obriši postojeæe smiley-e prije uvoza';
$lang['import_smile_pack'] = 'Uvezi pakiranje smiley-a';
$lang['export_smile_pack'] = 'Napravi paket smiley-a';
$lang['export_smiles'] = 'Da bi ste napravili paket smiley-a od trenutno instaliranih, kliknite %sovdje%s za download smiles.pak datoteke. Ovoj datoteci dajte odgovarajuæe ime pazeæi da saèuvate .pak datoteènu ekstenziju.  Onda napravite zip datoteku koja sadrži sve vaše smiley-e plus ovu .pak konfiguracijsku datoteku.';

$lang['smiley_add_success'] = 'Smiley je uspješno dodan';
$lang['smiley_edit_success'] = 'Smiley je uspješno osvježen';
$lang['smiley_import_success'] = 'Smiley pakiranje je uspješno uvezeno!';
$lang['smiley_del_success'] = 'Smiley je uspješno izbrisan';
$lang['Click_return_smileadmin'] = 'Kliknite %sovdje%s za povratak na Administraciju smiley-a';


//
// User Management
//
$lang['User_admin'] = 'Administracija korisnika';
$lang['User_admin_explain'] = 'Ovde možete izmjeniti informacije o korisnicima i odreðene specifiène opcije. Da biste izmjenili dozvole korisnika i grupa koristite sistem dozvola za korisnike i grupe.';

$lang['Look_up_user'] = 'Pronaði korisnika';

$lang['Admin_user_fail'] = 'Ne mogu osvježiti korisnikov-e profile.';
$lang['Admin_user_updated'] = 'Profil korisnika je uspješno osvježen.';
$lang['Click_return_useradmin'] = 'Kliknite %sovdje%s za povratak na Administraciju korisnika';

$lang['User_delete'] = 'Obriši ovog korisnika';
$lang['User_delete_explain'] = 'Kliknite ovdje da obrišete ovog korisnika, ova operacija je nepovratna.';
$lang['User_deleted'] = 'Korisnik je uspješno obrisan.';

$lang['User_status'] = 'Korisnik je aktivan';
$lang['User_allowpm'] = 'Može slati privatne poruke';
$lang['User_allowavatar'] = 'Može prikazati avatar';

$lang['Admin_avatar_explain'] = 'Ovdje možete pogledati i obrisati korisnikov trenutni avatar ili avatare.';

$lang['User_special'] = 'Specijalna polja samo za administratore';
$lang['User_special_explain'] = 'Ova polja ne mogu mijenjati korisnici. Ovdje možete podesiti njihov status i druge opcije kojima korisnici nemaju pristup.';


//
// Group Management
//
$lang['Group_administration'] = 'Administracija grupa';
$lang['Group_admin_explain'] = 'Sa ovog panela možete administrirati sve vaše korisnièke grupe, možete: brisati, dodavati i mijenjati postojeæe grupe. Možete izabrati urednike, mijenjati status grupe (otvorena/zatvorena) i podesiti ime grupe i opis';
$lang['Error_updating_groups'] = 'Greška pri osvježivanju grupa';
$lang['Updated_group'] = 'Grupa je uspješno osvježena';
$lang['Added_new_group'] = 'Nova grupa je uspješno kreirana';
$lang['Deleted_group'] = 'Grupa je uspješno obrisana';
$lang['New_group'] = 'Napravi novu grupu';
$lang['Edit_group'] = 'Izmijeni grupu';
$lang['group_name'] = 'Naziv grupe';
$lang['group_description'] = 'Opis grupe';
$lang['group_moderator'] = 'Urednik grupe';
$lang['group_status'] = 'Status grupe';
$lang['group_open'] = 'Otvori grupu';
$lang['group_closed'] = 'Zatvorena grupa';
$lang['group_hidden'] = 'Skrivena grupa';
$lang['group_delete'] = 'Obriši grupu';
$lang['group_delete_check'] = 'Obriši ovu grupu';
$lang['submit_group_changes'] = 'Pošalji izmijene';
$lang['reset_group_changes'] = 'Resetiraj izmijene';
$lang['No_group_name'] = 'Morate odrediti ime za ovu grupu';
$lang['No_group_moderator'] = 'Morate odrediti urednika za ovu grupu';
$lang['No_group_mode'] = 'Morate odrediti mod za ovu grupu, otvorena ili zatvorena';
$lang['No_group_action'] = 'Nije odreðena akcija';
$lang['delete_group_moderator'] = 'Obrisati starog moderatora grupe?';
$lang['delete_moderator_explain'] = 'Ukoliko mijenjate urednika grupe, oznaèite ovdje da izbrišete starog moderatora iz grupe. U suprotnom, nemojte oznaèiti, i korisnik æe postati regularni èlan grupe.';
$lang['Click_return_groupsadmin'] = 'Kliknite %sovdje%s za povratak na Administraciju grupa.';
$lang['Select_group'] = 'Izaberite grupu';
$lang['Look_up_group'] = 'Potraži grupu';


//
// Prune Administration
//
$lang['Forum_Prune'] = 'Pojednostavljenje foruma';
$lang['Forum_Prune_explain'] = 'Ovim æete izbrisati sve teme na koje nije odgovoreno za vrijeme (broj dana) koje ste izabrali. Ako ne izaberete broj sve teme æe biti izbrisane. Neæe biti obrisane teme u kojima se glasa niti æe biti obrisane obavijesti. Ove teme æete morati izbrisati ruèno.';
$lang['Do_Prune'] = 'Pokreni pojednostavljenje';
$lang['All_Forums'] = 'Svi forumi';
$lang['Prune_topics_not_posted'] = 'Pojednostavi teme na koje nije odgovoreno u ovoliko dana';
$lang['Topics_pruned'] = 'Pojednostavljene teme';
$lang['Posts_pruned'] = 'Pojednostavljene poruke';
$lang['Prune_success'] = 'Pojednostavljenje foruma je uspješno izvršeno';


//
// Word censor
//
$lang['Words_title'] = 'Cenzurirane rijeèi';
$lang['Words_explain'] = 'Odavde možete dodati, izmijeniti i izbrisati rijeèi koje æe biti automatski cenzurirane na vašim forumima. Takoðer korisnici se neæe moæi registrirati sa korisnièkim imenom koje sadrži ove rijeèi. Jokeri (*) su prihvatljivi u polju reèi, npr. *test* æe se poklopiti sa atestirano, test* æe se poklopiti sa testirano, *test æe se poklopiti sa atest.';
$lang['Word'] = 'Rijeè';
$lang['Edit_word_censor'] = 'Izmijeni cenzuru rijeèi';
$lang['Replacement'] = 'Zamjena';
$lang['Add_new_word'] = 'Dodaj novu rijeè';
$lang['Update_word'] = 'Osvježi cenzuru rijeèi';

$lang['Must_enter_word'] = 'Morate unjeti rijeè i njenu zamijenu';
$lang['No_word_selected'] = 'Nije izabrana rijeè za izmijenu';

$lang['Word_updated'] = 'Cenzura za izabranu rijeè je uspješno osvježena';
$lang['Word_added'] = 'Cenzura rijeèi je uspješno dodana';
$lang['Word_removed'] = 'Cenzura za izabranu rijeè je uspješno izbrisana';

$lang['Click_return_wordadmin'] = 'Kliknite %sovdje%s za povratak na Cenzurirane rijeèi';


//
// Mass Email
//
$lang['Mass_email_explain'] = 'Odavde možete poslati email svim korisnicima, ili korisnicima iz odreðene grupe.  Da to uèinite, email æe biti poslan na priloženu administrativnu adresu, sa blind carbon copijom poslatom svim primateljima. Ako šaljete email velikoj grupi ljudi molimo vas da budete strpljivi poslje pritiska na gumb pošalji i nemojte zaustavljati stranicu na pola operacije. Normalno je da pri masovnom slanju emaila operacija traje dugo, i bit æete obaviješteni kada se izvrši kompletana skripta';
$lang['Compose'] = 'Napiši'; 

$lang['Recipients'] = 'Primatelji'; 
$lang['All_users'] = 'Svi korisnici';

$lang['Email_successfull'] = 'Vaša poruka je poslana';
$lang['Click_return_massemail'] = 'Kliknite %sovdje%s za povratak na Masovni email';


//
// Ranks admin
//
$lang['Ranks_title'] = 'Administracija pozicija';
$lang['Ranks_explain'] = 'Koristeæi ovaj formular možete dodati, izmijeniti, pregledati i brisati pozicije. Takoðer možete kreirati proizvoljne pozicije koje mogu biti primjenjene na korisnika preko Administracije korisnika';

$lang['Add_new_rank'] = 'Dodaj novu poziciju';

$lang['Rank_title'] = 'Naziv pozicije';
$lang['Rank_special'] = 'Podesi specijalnu poziciju';
$lang['Rank_minimum'] = 'Minimum poruka';
$lang['Rank_maximum'] = 'Maksimum poruka';
$lang['Rank_image'] = 'Slika pozicije (relativna putanja od phpBB2 root-a)';
$lang['Rank_image_explain'] = 'Ovo koristite za definiranje slièice koja podsjeæa na poziciju';

$lang['Must_select_rank'] = 'Morate izabrati poziciju';
$lang['No_assigned_rank'] = 'Nije dodjeljena specijalna pozicija';

$lang['Rank_updated'] = 'Pozicija je uspješno osvježena';
$lang['Rank_added'] = 'Pozicija je uspješno dodana';
$lang['Rank_removed'] = 'Pozicija je uspješno izbrisana';
$lang['No_update_ranks'] = 'Pozicija je uspješno obrisana, mada korisnièki nalozi koji koriste ovu poziciju nisu osvježeni. Morat æete ruèno resetirati poziciju takvih naloga';

$lang['Click_return_rankadmin'] = 'Kliknite %sovdje%s za povratak na Administraciju pozicija';


//
// Disallow Username Admin
//
$lang['Disallow_control'] = 'Kontrola zabranjenih imena';
$lang['Disallow_explain'] = 'Odavde možete kontrolirati korisnièka imena koja se ne mogu koristiti. Zabranjena korisnièka imena mogu da sadrže djokere *. Znajte da vam neæe biti dozvoljeno da odredite bilo koje korisnièko ime koje je veæ registrirano, pa æete morati prvo obrisati to ime a tek onda ga zabranite';

$lang['Delete_disallow'] = 'Izbriši';
$lang['Delete_disallow_title'] = 'Izbriši zabranjeno korisnièko ime';
$lang['Delete_disallow_explain'] = 'Možete izbrisati zabranjeno korisnièko ime tako što æete izabrati korisnièko ime sa ove liste i kliknuti na gumb Izbriši';

$lang['Add_disallow'] = 'Dodaj';
$lang['Add_disallow_title'] = 'Dodaj zabranjeno korisnièko ime';
$lang['Add_disallow_explain'] = 'Možete zabraniti korisnièko ime koristeæi jokera * kao zamjenu bilo kojeg slova';

$lang['No_disallowed'] = 'Nema zabranjenih korisnièkih imena';

$lang['Disallowed_deleted'] = 'Zabranjeno korisnièko ime je uspješno izbrisano';
$lang['Disallow_successful'] = 'Zabranjeno korisnièko ime je uspješno dodano';
$lang['Disallowed_already'] = 'Ime koje ste unijeli ne može biti zabranjeno. Veæ postoji u listi, postoji u listi cenzuriranih rijeèi, ili to korisnièko ime veæ postoji';

$lang['Click_return_disallowadmin'] = 'Kliknite %sovdje%s za povratak na Kontrolu zabranjenih imena';


//
// Styles Admin
//
$lang['Styles_admin'] = 'Administracija stilova';
$lang['Styles_explain'] = 'Možete dodati, izbrisati i upravljati stilovima (podlogama i temama) dostupnim vašim korisnicima';
$lang['Styles_addnew_explain'] = 'Sljedeæa lista sadrži sve teme koje su dostupne za podloge koje trenutno imate. Stavke u listi još uvijek nisu instalirane u phpBB bazu. Da biste instalirali temu jednostavno kliknite na link Install pored stavke';

$lang['Select_template'] = 'Izaberite podlogu';

$lang['Style'] = 'Stil';
$lang['Template'] = 'Podloga';
$lang['Install'] = 'Instaliraj';
$lang['Download'] = 'Preuzmi';

$lang['Edit_theme'] = 'Izmijeni temu';
$lang['Edit_theme_explain'] = 'U formularu ispod možete izmijeniti podešavanja za izabranu temu';

$lang['Create_theme'] = 'Napravi temu';
$lang['Create_theme_explain'] = 'Koristite donji formular da napravite novu temu za izabranu podlogu. Kada unosite boje (za koje koristite heksadecimalni oblik) ne smijete unijeti prefiks #, npr. CCCCCC je ispravno, a #CCCCCC nije';

$lang['Export_themes'] = 'Izvezi teme';
$lang['Export_explain'] = 'U ovom panelu moæi æete izvoziti podatke za izabranu podlogu. Izaberite podlogu iz liste ispod i skripta æe napraviti konfiguracijsku datoteku za temu i pokušati ju snimiti u izabrani direktorij podloge. Ukoliko nije u moguænosti snimiti datoteku ponudit æe vam opciju da ga preuzmete. Da bi skript bio u moguænosti da snimi datoteku morate podesiti dozvole za pisanje webserveru za izabrani direktorij sa podlogama. Za više informacija o ovome pogledajte phpBB 2 users guide.';

$lang['Theme_installed'] = 'Izabrana tema je uspješno instalirana';
$lang['Style_removed'] = 'Izabrani stil je izbrisan iz baze. Da biste u potpunosti izbrisali stil sa vašeg sistema morate izbrisati odgovarajuæi stil iz vašeg direktorija sa podlogama.';
$lang['Theme_info_saved'] = 'Informacija o temi koju ste izabrali je snimljena. Sada bi trebali vratiti dozvolu datoteci theme_info.cfg (i ako je to moguæe i izabranom direktoriju sa podlogama) na read-only';
$lang['Theme_updated'] = 'Izabrana tema je osvježena. Sada bi trebali izvesti podešavanja za novu temu';
$lang['Theme_created'] = 'Tema je napravljena. Trebali bi izvesti temu u konfiguracijsku datoteku teme zbog sigurnog èuvanja ili upotrebe na nekom drugom mjestu';

$lang['Confirm_delete_style'] = 'Da li ste sigurni da želite obrisati ovaj stil';

$lang['Download_theme_cfg'] = 'Izvoznik nije mogao snimiti informacijsku datoteku teme. Kliknite na gumb ispod da bi ste preuzeli datoteku sa vašim browserom. Kada ju preuzmete možete ju prebaciti u direktorij koji sadrži datoteke podloge. Tada možete spakirati datoteke za distribuciju ili koristiti gdje god poželite';
$lang['No_themes'] = 'Podloga koju ste izabrali nema prikvaèenih tema. Da napravite novu temu kliknite na link Napravi na panelu sa lijeve strane';
$lang['No_template_dir'] = 'Ne mogu otvoriti direktorij sa temema. Možda je neèitljiv web serveru ili ne postoji';
$lang['Cannot_remove_style'] = 'Ne možete izbrisati izabrani stil jer je trenutno odreðen za forum. Molim vas da promijenite podrazumijevani stil i pokušate ponovo.';
$lang['Style_exists'] = 'Ime stila koga ste izabrali veæ postoji, molim vas da se vratite i izaberete drugo ime.';

$lang['Click_return_styleadmin'] = 'Kliknite %sovdje%s za povratak na Administraciju stilova';

$lang['Theme_settings'] = 'Podešavanje teme';
$lang['Theme_element'] = 'Element teme';
$lang['Simple_name'] = 'Jednostavan naziv';
$lang['Value'] = 'Vrijednost';
$lang['Save_Settings'] = 'Snimi podešavanja';

$lang['Stylesheet'] = 'CSS Stylesheet';
$lang['Background_image'] = 'Pozadinska slika';
$lang['Background_color'] = 'Boja pozadine';
$lang['Theme_name'] = 'Naziv teme';
$lang['Link_color'] = 'Boja linka';
$lang['Text_color'] = 'Boja teksta';
$lang['VLink_color'] = 'Boja posjeæenog linka';
$lang['ALink_color'] = 'Boja aktivnog linka';
$lang['HLink_color'] = 'Boja linka kada je miš iznad';
$lang['Tr_color1'] = 'Prva boja reda tabele';
$lang['Tr_color2'] = 'Druga boja reda tabele';
$lang['Tr_color3'] = 'Treæa boja reda tabele';
$lang['Tr_class1'] = 'Prva klasa reda tabele';
$lang['Tr_class2'] = 'Druga klasa reda tabele';
$lang['Tr_class3'] = 'Treæa klasa reda tabele';
$lang['Th_color1'] = 'Prva boja zaglavlja tabele';
$lang['Th_color2'] = 'Druga boja zaglavlja tabele';
$lang['Th_color3'] = 'Treæa boja zaglavlja tabele';
$lang['Th_class1'] = 'Prva klasa zaglavlja tabele';
$lang['Th_class2'] = 'Druga klasa zaglavlja tabele';
$lang['Th_class3'] = 'Treæa klasa zaglavlja tabele';
$lang['Td_color1'] = 'Prva boja æelije tabele';
$lang['Td_color2'] = 'Druga boja æelije tabele';
$lang['Td_color3'] = 'Treæa boja æelije tabele';
$lang['Td_class1'] = 'Prva klasa æelije tabele';
$lang['Td_class2'] = 'Druga klasa æelije tabele';
$lang['Td_class3'] = 'Treæa klasa æelije tabele';
$lang['fontface1'] = 'Oblik fonta 1';
$lang['fontface2'] = 'Oblik fonta 2';
$lang['fontface3'] = 'Oblik fonta 3';
$lang['fontsize1'] = 'Velièina fonta 1';
$lang['fontsize2'] = 'Velièina fonta 2';
$lang['fontsize3'] = 'Velièina fonta 3';
$lang['fontcolor1'] = 'Boja fonta 1';
$lang['fontcolor2'] = 'Boja fonta 2';
$lang['fontcolor3'] = 'Boja fonta 3';
$lang['span_class1'] = 'Širina klase 1';
$lang['span_class2'] = 'Širina klase 2';
$lang['span_class3'] = 'Širina klase 3';
$lang['img_poll_size'] = 'Velièina slike za glasanje [px]';
$lang['img_pm_size'] = 'Velièina statusa privatne poruke [px]';


//
// Install Process
//
$lang['Welcome_install'] = 'Dobrodošli na phpBB 2 instalaciju';
$lang['Initial_config'] = 'Osnovna konfiguracija';
$lang['DB_config'] = 'Konfiguracija baze';
$lang['Admin_config'] = 'Konfiguracija administratora';
$lang['continue_upgrade'] = 'Kada preuzmete vašu konfiguracijsku datoteku na vaše raèunalo možete kliknuti na gumb \'Nastavi nadogradnju\' da biste nastavili procesom nadogradnje. Molimo vas da prièekate dok se ne završi proces slanja konfiguracijske datoteke i ne završi nadogradnja.';
$lang['upgrade_submit'] = 'Nastavi nadogradnju';

$lang['Installer_Error'] = 'Pojavila se greška prilikom instalacije';
$lang['Previous_Install'] = 'Otkrivena je prethodna instalacija';
$lang['Install_db_error'] = 'Javila se greška pri pokušaju osvježavanja baze';

$lang['Re_install'] = 'Vaša prethodna instalacija je još uvijek aktivna. <br /><br />Ukoliko želite reinstalirati phpBB 2 kliknite na Yes gumb ispod. Znajte da æete time uništiti sve postojeæe podatke, i neæe biti napravljen povrat! Korisnièko ime i šifra administratora koje ste koristili za prijavljivanje na forum bit æe ponovno napravljeni poslje reinstalacije, nijedna druga podešavanja neæe biti saèuvana. <br /><br /> Pažljivo razmislite prije nego kliknete na Yes!';

$lang['Inst_Step_0'] = 'Hvala vam što ste izabrali phpBB 2. Da biste završili instalaciju molimo vas da popunite detalje ispod koji su obavezni. Znajte da bi baza koju hoæete instalirati trebala postojati. Ako instalirate u bazu koja koristi ODBC, npr. MS Access trebalo bi prvo kreirate DSN za nju prije nego što nastavite dalje.';

$lang['Start_Install'] = 'Poèni instalaciju';
$lang['Finish_Install'] = 'Završi instalaciju';

$lang['Default_lang'] = 'Podrazumijevani jezik na forumu';
$lang['DB_Host'] = 'Ime host servera sa bazom / DSN';
$lang['DB_Name'] = 'Ime vaše baze';
$lang['DB_Username'] = 'Korisnièko ime baze';
$lang['DB_Password'] = 'Lozinka baze';
$lang['Database'] = 'Vaša baza';
$lang['Install_lang'] = 'Izaberite jezik za instalaciju';
$lang['dbms'] = 'Vrsta baze';
$lang['Table_Prefix'] = 'Prefiks za tabele u bazi';
$lang['Admin_Username'] = 'Korisnièko ime administratora';
$lang['Admin_Password'] = 'Šifra administratora';
$lang['Admin_Password_confirm'] = 'Potvrdite šifru administratora [ Potvrdi ]';

$lang['Inst_Step_2'] = 'Korisnièko ime administratora je napravljeno. U ovoj toèki vaša osnovna instalacija je završena. Sada æemo vas odvesti na ekran koji æe vam omoguæiti administraciju vaše nove instalacije. Obavezno provjerite detalje u Generalnoj konfiguraciji i izvršite obavezne izmjene. Hvala vam što se izabrali phpBB 2.';

$lang['Unwriteable_config'] = 'Vašu konfiguracijsku datoteku ne mogu presnimiti preko postojeæe. Kopija konfiguracijske datoteke æe biti preuzeta kada kliknete na gumb ispod. Pošaljite ovu datoteku u isti direktorij gdje se nalazi phpBB 2. Kada to napravit prijavite se koristeæi korisnièko ime i šifru administratora koje ste priložili u prethodnom formularu i posjetite kontrolni centar (pojavit æe se link na dnu svakog ekrana kada se budete prijavili) da biste provjerili Generalnu konfiguraciju. Hvala vam što ste izabrali phpBB 2.';
$lang['Download_config'] = 'Preuzmi konfiguraciju';

$lang['ftp_choose'] = 'Izaberite metodu preuzimanja';
$lang['ftp_option'] = '<br />Obzirom da su FTP ekstenzije podržane u ovoj verziji PHP biæe vam dana opcija da prvo probam automatski putem ftp-a smjestit konfiguracijsku datoteku na svoje mjesto.';
$lang['ftp_instructs'] = 'Izabrali ste da pošaljete datoteku putem ftp-a na vaš nalog na kome je phpBB 2 automatski. Molimo vas da unesete informacije ispod da biste olakšali proces. Znajte da bi FTP putanja trebalo biti ista kao i putanja preko ftp-a do vaše phpBB2 instalacije kao da pristupate ftp-u koristeæi bilo koji normalni klijent.';
$lang['ftp_info'] = 'Unesite vaše FTP informacije';
$lang['Attempt_ftp'] = 'Pokušaj da preko ftp-a smjestiš konfiguracijsku datoteku na svoje mjesto';
$lang['Send_file'] = 'Samo pošaljite datoteku meni i ja æu ga ruèno poslati putem ftp-a';
$lang['ftp_path'] = 'FTP putanja do phpBB 2';
$lang['ftp_username'] = 'Vaše korisnièko ime za FTP';
$lang['ftp_password'] = 'Vaša šifra za FTP';
$lang['Transfer_config'] = 'Poèni prenos';
$lang['NoFTP_config'] = 'Pokušaj postavljanja konfiguracijske datoteke putem ftp-a na svoje mjesto nije bio uspješan. Molimo vas da preuzmete konfiguracijsku datoteku i putem ftp-a ju ruèno pošaljete i postavite na pravo mjesto.';

$lang['Install'] = 'Instaliraj';
$lang['Upgrade'] = 'Nadogradi';


$lang['Install_Method'] = 'Izaberite metodu instalacije';

$lang['Install_No_Ext'] = 'php konfiguracija na vašem serveru ne podržava tip baze koji ste izabrali';

$lang['Install_No_PCRE'] = 'phpBB2 zahtjeva Perl-kompatibilan modul regularnih ekstenzija za php koju vaša php konfiguracija izgleda ne podržava!';

//
// Toliko za sada ;=)
// -------------------------------------------------

?>