<?php

/***************************************************************************
 *                            lang_admin.php [Serbian]
 *                              -------------------
 *     begin                : Monday Sep 30 2002 
 *     copyright            : (C) 2002 Simic Vladan
 *     email                : vlada@extremecomputers.co.yu
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
$lang['General'] = 'Generalno';
$lang['Users'] = 'Korisnici';
$lang['Groups'] = 'Grupe';
$lang['Forums'] = 'Forumi';
$lang['Styles'] = 'Stilovi';

$lang['Configuration'] = 'Konfiguracija';
$lang['Permissions'] = 'Dozvole';
$lang['Manage'] = 'Menadžment';
$lang['Disallow'] = 'Zabranjena imena';
$lang['Prune'] = 'Uprošæavanje';
$lang['Mass_Email'] = 'Masovni Email';
$lang['Ranks'] = 'Pozicije';
$lang['Smilies'] = 'Smajliji';
$lang['Ban_Management'] = 'Kontrola zabrana';
$lang['Word_Censor'] = 'Cenzurisane reèi';
$lang['Export'] = 'Izvoz';
$lang['Create_new'] = 'Napravi';
$lang['Add_new'] = 'Dodaj';
$lang['Backup_DB'] = 'Bakapuj bazu';
$lang['Restore_DB'] = 'Povrati bazu';


//
// Index
//
$lang['Admin'] = 'Administracija';
$lang['Not_admin'] = 'Nemate ovlašæenja da administrirate board';
$lang['Welcome_phpBB'] = 'Dobrodošli na phpBB';
$lang['Admin_intro'] = 'Hvala vam što ste izabrali phpBB kao rešenje za vaš forum. Na ovom ekranu imate brz pregled raznih statistika vašeg boarda. Na ovu stranicu se možete vratiti klikom na <u>Admin Indeks</u> link na levom panelu. Za povratak na indeks boarda, kliknite na phpBB logo takoðe na levom panelu. Ostali linkovi na levom panelu dozvoliæe vam da kontrolišete svaki aspekt vašeg foruma, a svaki ekran ima uputstvo kako da koristite alatke.';
$lang['Main_index'] = 'Indeks foruma';
$lang['Forum_stats'] = 'Statistike foruma';
$lang['Admin_Index'] = 'Admin index';
$lang['Preview_forum'] = 'Pregled foruma';

$lang['Click_return_admin_index'] = 'Kliknite %sovde%s za povratak na Admin Indeks';

$lang['Statistic'] = 'Statistika';
$lang['Value'] = 'Vrednost';
$lang['Number_posts'] = 'Broj poruka';
$lang['Posts_per_day'] = 'Broj poruka dnevno';
$lang['Number_topics'] = 'Broj tema';
$lang['Topics_per_day'] = 'Broj tema dnevno';
$lang['Number_users'] = 'Broj korisnika';
$lang['Users_per_day'] = 'Broj korisnika dnevno';
$lang['Board_started'] = 'Board je poèeo';
$lang['Avatar_dir_size'] = 'Velièina direktorijuma Avatar';
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
$lang['Backup'] = 'Bekapuj';
$lang['Restore_explain'] = 'Ovim æete izvršiti potpun povraæaj svih phpBB tabela iz snimljnog fajla. Ako to vaš server podržava možete poslati gzip kompesovani tekst fajl koji æe biti automatski dekompresovan. <b>UPOZORENJE:</b> Ovim æete prepisati postojeæe podatke. Proces može potrajati dugo pa vas molimo da ostanete na ovoj stranici dok se operacija ne završi.';
$lang['Backup_explain'] = 'Ovde možete bekapovati sve vaše phpBB podatke. Ako imate bilo kakve dodatne tabele u istoj bazi sa phpBB koje biste želeli da bekapujete, molimo vas da unesete njihova imena odvojena zarezima u polje Dodatne tabele ispod. Ako to vaš server podržava možete koristiti gzip kompresiju da biste smanjili velièinu fajla pre downloada.';

$lang['Backup_options'] = 'Opcije za bekapovanje';
$lang['Start_backup'] = 'Startuj Bekap';
$lang['Full_backup'] = 'Potpun bekap';
$lang['Structure_backup'] = 'Bekapuj samo strukturu';
$lang['Data_backup'] = 'Bekapuj samo podatke';
$lang['Additional_tables'] = 'Dodatne tabele';
$lang['Gzip_compress'] = 'Gzip kopresovan fajl';
$lang['Select_file'] = 'Izaberite fajl';
$lang['Start_Restore'] = 'Startuj povratak';

$lang['Restore_success'] = 'Baza je uspešno povraæena.<br /><br />Vaš board bi trebalo da je u stanju u kakvom je bio kada je napravljen bekap.';
$lang['Backup_download'] = 'Download æe poèeti brzo - molimo vas da saèekate da poène';
$lang['Backups_not_supported'] = 'Izvinjavamo se ali bekap baze trenutno nije podržan za vaš sistem baze.';

$lang['Restore_Error_uploading'] = 'Greška pri slanju bekap fajla';
$lang['Restore_Error_filename'] = 'Problem sa imenom fajla, probajte neki drugi';
$lang['Restore_Error_decompress'] = 'Ne mogu da dekompresujem gzip fajl, molim vas da pošaljete klasiènu tekst verziju';
$lang['Restore_Error_no_file'] = 'Nijedan fajl nije poslat';


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

$lang['Group_auth_explain'] = 'Ovde možete izmeniti dozvole i ureðivati status dodeljen svakoj grupi. Ne zaboravite da kad menjate dozvole grupama individualne dozvole korisnika još uvek omoguæuju korisniku ulaz na forume itd. Ako se to desi biæete upozoreni u svakom sluèaju.';
$lang['User_auth_explain'] = 'Ovde možete izmeniti dozvole i ureðivati status dodeljen svakom korisniku. Ne zaboravite da kad menjate dozvole korisnicima individualne dozvole grupama još uvek omoguæuju korisniku ulaz na forume itd. U tom sluèaju biæete upozoreni.';
$lang['Forum_auth_explain'] = 'Ovde možete izmeniti nivoe pristupa svakom forumu. Imate i uprošæen i prošireni metod, s tim da prošireni metod nudi veæu kontrolu svake operacije na forumu. Zapamtite da æete promenom livoa pristupa forumima odrediti koji korisnici mogu da izvrše razne operacije sa njima.';

$lang['Simple_mode'] = 'Uprošæen mod';
$lang['Advanced_mode'] = 'Prošireni mod';
$lang['Moderator_status'] = 'Status urednika';

$lang['Allowed_Access'] = 'Dozvoljen pristup';
$lang['Disallowed_Access'] = 'Zabranjen pristup';
$lang['Is_Moderator'] = 'Urednik';
$lang['Not_Moderator'] = 'Nije urednik';

$lang['Conflict_warning'] = 'Upozorenje o konfliktu dozvola';
$lang['Conflict_access_userauth'] = 'Ovaj korisnik ima pristupna prava u ovom forumu preko èlanstva u grupi. Možda æete želeti da izmenite dozvole grupa ili da izbacite korisnika iz grupe da bi mu u potpunosti ukinuli prava na pristup. Garantovana prava grupe (ukljuèujuæi i umešane forume) su prikazana ispod.';
$lang['Conflict_mod_userauth'] = 'Ovaj korisnik ima pristupna prava urednika u ovom forumu preko èlanstva u grupi. Možda æete želeti da izmenite dozvole grupa ili da izbacite korisnika iz grupe da bi mu u potpunosti ukinuli prava na urednièki pristup. Garantovana prava grupe (ukljuèujuæi i umešane forume) su prikazana ispod.';

$lang['Conflict_access_groupauth'] = 'Sledeæi korisnik (ili korisnici) još uvek imaju pravo pristupa na ovaj forum putem korisnièkih dozvola. Možda æete želeti da izmenite dozvole korisnika da bi mu u potpunosti ukinuli prava na pristup. Garantovana prava korisnika (ukljuèujuæi i umešane forume) su prikazana ispod.';
$lang['Conflict_mod_groupauth'] = 'Sledeæi korisnik (ili korisnici) još uvek imaju pravo urednièkog pristupa na ovaj forum putem korisnièkih dozvola. Možda æete želeti da izmenite dozvole korisnika da bi mu u potpunosti ukinuli urednièka prava. Garantovana prava korisnika (ukljuèujuæi i umešane forume) su prikazana ispod.';

$lang['Public'] = 'Javni';
$lang['Private'] = 'Privatni';
$lang['Registered'] = 'Registrovani';
$lang['Administrators'] = 'Administratori';
$lang['Hidden'] = 'Skriveni';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = 'SVI';
$lang['Forum_REG'] = 'REGIISTROVANI';
$lang['Forum_PRIVATE'] = 'PRIVATNI';
$lang['Forum_MOD'] = 'MODERATORI';
$lang['Forum_ADMIN'] = 'ADMINISTRATORI';

$lang['View'] = 'Pogledaj';
$lang['Read'] = 'Proèitaj';
$lang['Post'] = 'Poèalji';
$lang['Reply'] = 'Odgovori';
$lang['Edit'] = 'Izmeni';
$lang['Delete'] = 'Obriši';
$lang['Sticky'] = 'Lepljiva';
$lang['Announce'] = 'Obaveštenje'; 
$lang['Vote'] = 'Glasaj';
$lang['Pollcreate'] = 'Napravi glasanje';

$lang['Permissions'] = 'Dozvole';
$lang['Simple_Permission'] = 'Prosta dozvola';

$lang['User_Level'] = 'Nivo korisnika'; 
$lang['Auth_User'] = 'Korisnik';
$lang['Auth_Admin'] = 'Administrator';
$lang['Group_memberships'] = 'Èlanstvo u grupi';
$lang['Usergroup_members'] = 'Ova grupa ima sledeæe èlanove';

$lang['Forum_auth_updated'] = 'Dozvole foruma su izmenjene';
$lang['User_auth_updated'] = 'Dozvole korisnika su izmenjene';
$lang['Group_auth_updated'] = 'Dozvole grupa su izmenjene';

$lang['Auth_updated'] = 'Dozvole su izmenjene';
$lang['Click_return_userauth'] = 'Klikite %sovde%s za povratak na Dozvole korisnika';
$lang['Click_return_groupauth'] = 'Kliknite %sOvde%s za povratak na Dozvole grupa';
$lang['Click_return_forumauth'] = 'Kliknite %sovde%s za povratak na Dozvole foruma';


//
// Banning
//
$lang['Ban_control'] = 'Kontrola zabrane';
$lang['Ban_explain'] = 'Odavde možete kontrolisati zabrane korisnicima. Možete zabraniti pojedinaèno ili zajedno pojedinaènog ili specifiènog korisnika ili opseg IP adresa ili imena hostova. Ovi metodi spreèavaju korisnika da pristupi index stranici vašeg boarda. Da biste spreèili korisnika da se registruje koristeæi drugo korisnièko ime možete odrediti zabranu putem email adrese. Znajte da blokiranjem email adrese neæe spreèiti korisnika da se loguje ili šalje poruke na vaš board, tako da bi trebalo da koristite jedno od prva dva metoda.';
$lang['Ban_explain_warn'] = 'Znajte da unošenjem opsega IP adresa sve odrese od poèetne do krajnje æe biti dodate na listu blokiranih adresa. Pokušajte da minimizujete broj dodatih adresa u bazu unoseæi džokera (*) gde god je to moguæe. Ako stvarno morate blokirati opseg adresa gledajte da bude što manja.';

$lang['Select_username'] = 'Izaberite korisnièko imeme';
$lang['Select_ip'] = 'Izaberite IP';
$lang['Select_email'] = 'Izaberite email adresu';

$lang['Ban_username'] = 'Zabrani jednog ili više korisnika';
$lang['Ban_username_explain'] = 'Možete zabraniti više korisnika u jednom prolazu koristeæi adekvatnu kombinaciju miša i tastature za vaš kompjuter i browser.';

$lang['Ban_IP'] = 'Zabrani jednu ili više IP adresa ili imena hostova';
$lang['IP_hostname'] = 'IP adrese ili ime hostova';
$lang['Ban_IP_explain'] = 'Da biste izabrali više razlièitih IP-a ili imena hostova odvojite ih zarezima. Da bi ste odredili opseg IP adresa ovojite poèetnu i krajnju sa crticom (-), a za džokera koristite *';

$lang['Ban_email'] = 'Zabrani jednu ili više email adresa';
$lang['Ban_email_explain'] = 'Da biste iybrali više od jedne email adrese odvojite ih zarezom. Za džokera koristite *, na primer *@hotmail.com';

$lang['Unban_username'] = 'Ukloni zabranu za jednog ili više korisnika';
$lang['Unban_username_explain'] = 'Možete ukloniti zabranu više korisnika u jednom prolazu koristeæi adekvatnu kombinaciju miša i tastature za vaš kompjuter i browser';

$lang['Unban_IP'] = 'Ukloni zabranu za jednu ili više IP adresa';
$lang['Unban_IP_explain'] = 'Možete ukloniti zabranu više IP adresa u jednom prolazu koristeæi adekvatnu kombinaciju miša i tastature za vaš kompjuter i browser';

$lang['Unban_email'] = 'Ukloni zabranu za jednu ili više email adresa';
$lang['Unban_email_explain'] = 'Možete ukloniti zabranu više email adresa u jednom prolazu koristeæi adekvatnu kombinaciju miša i tastature za vaš kompjuter i browser';

$lang['No_banned_users'] = 'Nema zabranjenih korisnika';
$lang['No_banned_ip'] = 'Nema zabranjenih  IP adresa';
$lang['No_banned_email'] = 'Nema zabranjenih email adresa';

$lang['Ban_update_sucessful'] = 'Lista zabrana je uspešno osvežena';
$lang['Click_return_banadmin'] = 'Kliknite %sovde%s za povratak na Kontrolu zabrana';


//
// Configuration
//
$lang['General_Config'] = 'Generalna konfiguracija';
$lang['Config_explain'] = 'Forma ispod omoguæiæe vam da menjate sve generalne opcije boarda. Za konfiguracije korisnika i foruma koristite linkove na panelu sa leve strane.';

$lang['Click_return_config'] = 'Kliknite %sovde%s za povratak na Generalnu konfiguraciju';

$lang['General_settings'] = 'Generalna podešavanja boarda';
$lang['Server_name'] = 'Naziv domena';
$lang['Server_name_explain'] = 'Naziv domena sa koga se pokreæe ovaj board';
$lang['Script_path'] = 'Putanja do skripta';
$lang['Script_path_explain'] = 'Putanja gde je phpBB2 lociran relativno od naziva domena';
$lang['Server_port'] = 'Port servera';
$lang['Server_port_explain'] = 'Port na kome radi vaš server, obièno 80, promenite samo ako je drugaèije';
$lang['Site_name'] = 'Naziv sajta';
$lang['Site_desc'] = 'Opis sajta';
$lang['Board_disable'] = 'Iskljuèi board';
$lang['Board_disable_explain'] = 'Board neæe biti dostupan korisnicima. Nemojte se odjavljivati kada iskljuèujete board, jer neæete biti u moguænosti da se ponovo prijavite!';
$lang['Acct_activation'] = 'Omoguæi aktivaciju naloga';
$lang['Acc_None'] = 'Bez aktivacije'; // These three entries are the type of activation
$lang['Acc_User'] = 'Korisnik';
$lang['Acc_Admin'] = 'Administrator';

$lang['Abilities_settings'] = 'Osnovna podešavanja za korisnika i forum';
$lang['Max_poll_options'] = 'Maksimalni broj opcija za glasanje';
$lang['Flood_Interval'] = 'Interval flodovanja';
$lang['Flood_Interval_explain'] = 'Broj sekundi koje korisnik mora saèekati izmeðu dve poruke'; 
$lang['Board_email_form'] = 'Korisnik piše putem boarda';
$lang['Board_email_form_explain'] = 'Moguænost da korisnici jedni drugima šalju email putem boarda';
$lang['Topics_per_page'] = 'Tema po stranici';
$lang['Posts_per_page'] = 'Poruka po stranici';
$lang['Hot_threshold'] = 'koliko poruka èuvati kao poularne';
$lang['Default_style'] = 'Podrazumevani stil';
$lang['Override_style'] = 'Pregazi korisnièki stil';
$lang['Override_style_explain'] = 'Zamenjuje korisnièki definisan stil sa podrazumevanim';
$lang['Default_language'] = 'Podrazumevani jezik';
$lang['Date_format'] = 'Format datuma';
$lang['System_timezone'] = 'Sistemska vremenska zona';
$lang['Enable_gzip'] = 'Omoguæi GZip kompresiju';
$lang['Enable_prune'] = 'Omoguæi smanjivanje foruma';
$lang['Allow_HTML'] = 'Dozvoli HTML';
$lang['Allow_BBCode'] = 'Dozvoli BBCode';
$lang['Allowed_tags'] = 'Dozvoljeni HTML tagovi';
$lang['Allowed_tags_explain'] = 'Odvojite tagove zarezima';
$lang['Allow_smilies'] = 'Dozvoli smajlije';
$lang['Smilies_path'] = 'Putanja za smeštanje smajlija';
$lang['Smilies_path_explain'] = 'Putanja ispod vašeg phpBB root direktorijuma, npr. images/smiles';
$lang['Allow_sig'] = 'Dozvoli potpise';
$lang['Max_sig_length'] = 'Maksimalna dužina potpisa';
$lang['Max_sig_length_explain'] = 'Maksimalni broj karaktera i u potisu korisnika';
$lang['Allow_name_change'] = 'Dozvoli promene korisnièkog imena';

$lang['Avatar_settings'] = 'Podešavanje avatara';
$lang['Allow_local'] = 'Omoguæi galeriju avatara';
$lang['Allow_remote'] = 'Omoguæi udaljene avatare';
$lang['Allow_remote_explain'] = 'Avatari linkovani sa drugog web sajta';
$lang['Allow_upload'] = 'Omoguæi slanje avatara';
$lang['Max_filesize'] = 'Maksimalna velièina fajla za avatar';
$lang['Max_filesize_explain'] = 'Za poslate avatare';
$lang['Max_avatar_size'] = 'Maksimalne dimenzije avatara';
$lang['Max_avatar_size_explain'] = '(Visina x širina u pikselima)';
$lang['Avatar_storage_path'] = 'Putanja za smeštanje avatara';
$lang['Avatar_storage_path_explain'] = 'Putanja ispod vašeg phpBB root direktorijuma, npr. images/avatars';
$lang['Avatar_gallery_path'] = 'Putanja do avatar galerije';
$lang['Avatar_gallery_path_explain'] = 'Putanja ispod vašeg phpBB root direktorijuma za unapred uèitane slike, npr. images/avatars/gallery';

$lang['COPPA_settings'] = 'COPPA podešavanja';
$lang['COPPA_fax'] = 'COPPA Broj faksa';
$lang['COPPA_mail'] = 'COPPA poštanska adresa';
$lang['COPPA_mail_explain'] = 'Ovo je poštanska adresa gde æe roditelji slati COPPA registracione formulare';

$lang['Email_settings'] = 'Email podešavanja';
$lang['Admin_email'] = 'Administratorova email adresa';
$lang['Email_sig'] = 'Email potpis';
$lang['Email_sig_explain'] = 'Ovaj tekst æe biti prikaæen na sve emailove koje board pošalje';
$lang['Use_SMTP'] = 'Koristi SMTP server za email';
$lang['Use_SMTP_explain'] = 'Izaberite Da ukoliko želite da šaljete poruke putem odreðenog servera umesto lokalne funkcije';
$lang['SMTP_server'] = 'Adresa SMTP servera';
$lang['SMTP_username'] = 'SMTP korisnièko ime';
$lang['SMTP_username_explain'] = 'Korisnièko ime unesite samo ako to vaš SMTP server zahteva';
$lang['SMTP_password'] = 'SMTP šifra';
$lang['SMTP_password_explain'] = 'Šifru unesite samo ako to vaš SMTP server zahteva';

$lang['Disable_privmsg'] = 'Privatne poruke';
$lang['Inbox_limits'] = 'Maksimalo poruka u Sanduèetu';
$lang['Sentbox_limits'] = 'Maksimalno poruka u Sanduèetu za slanje';
$lang['Savebox_limits'] = 'Maksimalno poruka u Snimljeno';

$lang['Cookie_settings'] = 'Podešavanje kolaèiæa'; 
$lang['Cookie_settings_explain'] = 'Ovi detalji definišu kako se kolaèiæi šalju vašim korisnicima. Najèešæe je podrazumevana vrednost dovoljna ali ako trebate da nešto menjate radite to pažljivo, jer netaèno podešavanje može spreèiti korisnike da se prijave';
$lang['Cookie_domain'] = 'Domen kolaèiæa';
$lang['Cookie_name'] = 'Ime kolaèiæa';
$lang['Cookie_path'] = 'Putanja kolaèiæa';
$lang['Cookie_secure'] = 'Bezbednost kolaèiæa';
$lang['Cookie_secure_explain'] = 'Ako vaš server radi preko SSL-a podesite ovu opciju na Dozvoljeno a u suprotnom ostavite kao zabranjeno';
$lang['Session_length'] = 'Dužina sesije [ sekunde ]';


//
// Forum Management
//
$lang['Forum_admin'] = 'Administracija foruma';
$lang['Forum_admin_explain'] = 'Odavde možete dodati, brisati, izmeniti, preurediti i resinhronizovati kategorije i forume';
$lang['Edit_forum'] = 'Izmeni forum';
$lang['Create_forum'] = 'Napravi nov forum';
$lang['Create_category'] = 'Napravi novu kategoriju';
$lang['Remove'] = 'Ukloni';
$lang['Action'] = 'Akcija';
$lang['Update_order'] = 'Osveži redosled';
$lang['Config_updated'] = 'Konfiguracija foruma je uspešno osvežena';
$lang['Edit'] = 'Izmeni';
$lang['Delete'] = 'Izbriši';
$lang['Move_up'] = 'Pomeri gore';
$lang['Move_down'] = 'Pomeri dole';
$lang['Resync'] = 'Ponovo sinhronizuj';
$lang['No_mode'] = 'Nije podešen mod';
$lang['Forum_edit_delete_explain'] = 'Forma ispod æe vam omoguæiti da izmenite sve generalne opcije foruma. Za konfiguracije korisnika i foruma koristite linkove na levom panelu';

$lang['Move_contents'] = 'Pomeri sav sadržaj';
$lang['Forum_delete'] = 'Izbriši forum';
$lang['Forum_delete_explain'] = 'Forma ispod æe vam omoguæiti da izbrišete forum (ili kategoriju) i odluèite gde hoæete da smestite sve teme (ili forume) koji su sadržani.';

$lang['Status_locked'] = 'Zakljuèan';
$lang['Status_unlocked'] = 'Otkljuèan';
$lang['Forum_settings'] = 'Generalna podešavanja foruma';
$lang['Forum_name'] = 'Naziv foruma';
$lang['Forum_desc'] = 'Opis';
$lang['Forum_status'] = 'Status foruma';
$lang['Forum_pruning'] = 'Automatsko smanjivanje';

$lang['prune_freq'] = 'Proveri starost teme svakih';
$lang['prune_days'] = 'Pomeri teme u kojima nije pisano';
$lang['Set_prune_data'] = 'Ukljuèili ste automatsko smanjivanje za ovaj forum ali niste podesili uèestalost ili broj dana do smanjivanja. Molim vas da se vratite i to uèinite';

$lang['Move_and_Delete'] = 'Pomeri i izbriši';

$lang['Delete_all_posts'] = 'Izbriši sve poruke';
$lang['Nowhere_to_move'] = 'Takoðe nemam kuda da pomerim';

$lang['Edit_Category'] = 'Izmeni kategoriju';
$lang['Edit_Category_explain'] = 'Koristite ovu formu da biste izmenili naziv kategorije.';

$lang['Forums_updated'] = 'Informacije o forumu i kategoriji su uspešno osvežene';

$lang['Must_delete_forums'] = 'Moraæete da obrišete sve forume pre nogo što obrišete kategoriju';

$lang['Click_return_forumadmin'] = 'Kliknite %sovde%s za povratak na Administraciju foruma';


//
// Smiley Management
//
$lang['smiley_title'] = 'Editovanje smajlija';
$lang['smile_desc'] = 'Odavde možete dodati, izbrisati i editovati emotivne ikonice ili smajlije koje vaši korisnici mogu da koriste u porukama kao i privatnim porukama.';

$lang['smiley_config'] = 'Konfiguracija smajlija';
$lang['smiley_code'] = 'Kod smajlija';
$lang['smiley_url'] = 'Smajli fajl';
$lang['smiley_emot'] = 'Smajli emocija';
$lang['smile_add'] = 'Dodaj nov smajli';
$lang['Smile'] = 'Smajli';
$lang['Emotion'] = 'Emocija';

$lang['Select_pak'] = 'Izaberite paket (.pak) fajl';
$lang['replace_existing'] = 'Zameni postojeæi smajli';
$lang['keep_existing'] = 'Saèuvaj postojeæi smajli';
$lang['smiley_import_inst'] = 'Trebalo bi da raspakujete pakovanje smajlija i pošaljete sve fajlove u adekvatni smajli direktorijum za vašu instalaciju.  Onda izaberite taènu informaciju u ovoj formi da bi ste ubacili pakovanje.';
$lang['smiley_import'] = 'Uvezi pakovanje smajlija';
$lang['choose_smile_pak'] = 'Izaberite smajli paket .pak fajl';
$lang['import'] = 'Uvezi smajlije';
$lang['smile_conflicts'] = 'Šta bi trebalo uraditi u sluèaju konflikta';
$lang['del_existing_smileys'] = 'Obriši postojeæe smajlije pre uvoza';
$lang['import_smile_pack'] = 'Uvezi pakovanje smajlija';
$lang['export_smile_pack'] = 'Napravi paket smajlija';
$lang['export_smiles'] = 'Da bi ste napravili paket smajlija od trenutno instaliranih, kliknite %sovde%s za download smiles.pak fajla. Nazovite adekvatno ovaj fajl pazeæi da saèuvate .pak fajl ekstenziju.  Onda napravite zip fajl koji sadrži sve vaše smajlije plus ovaj .pak konfiguracioni fajl.';

$lang['smiley_add_success'] = 'Smajli je uspešno dodat';
$lang['smiley_edit_success'] = 'Smajli je uspešno osvežen';
$lang['smiley_import_success'] = 'Smajli pakovanje je uspešno uvezeno!';
$lang['smiley_del_success'] = 'Smajli je uspešno izbrisan';
$lang['Click_return_smileadmin'] = 'Kliknite %sovde%s za povratak na Administraciju smajlija';


//
// User Management
//
$lang['User_admin'] = 'Administracija korisnika';
$lang['User_admin_explain'] = 'Ovde možete izmeniti informacije o korisnicima i odrežene specifiène opcije. Da biste izmenili dozvole korisnika i grupa koristite sistem dozvola za korisnike i grupe.';

$lang['Look_up_user'] = 'Pronaði korisnika';

$lang['Admin_user_fail'] = 'Ne mogu osvežiti korisnikov-e profile.';
$lang['Admin_user_updated'] = 'Profil korisnika je uspešno osvežen.';
$lang['Click_return_useradmin'] = 'Kliknite %sovde%s za povratak na Administraciju korisnika';

$lang['User_delete'] = 'Obriši ovog korisnika';
$lang['User_delete_explain'] = 'Kliknite ovde da obrišete ovog korisnika, ova operacija se ne može povratiti.';
$lang['User_deleted'] = 'Korisnik je uspešno obrisan.';

$lang['User_status'] = 'Korisnik je aktivan';
$lang['User_allowpm'] = 'Može slati privattne poruke';
$lang['User_allowavatar'] = 'Može prikazati avatar';

$lang['Admin_avatar_explain'] = 'Ovde možete pogledati i obrisati korisnikov-e trenutni avatar.';

$lang['User_special'] = 'Specijalna polja samo za administratore';
$lang['User_special_explain'] = 'Ova polja ne mogu menjati korisnici. Ovde možete podesiti njihov status i druge opcije kojima korisnici nemaju pristup.';


//
// Group Management
//
$lang['Group_administration'] = 'Administracija grupa';
$lang['Group_admin_explain'] = 'Sa ovog panela možete administrirati sve vaše korisnièke grupe, možete: brisati, praviti i menjati postojeæe grupe. Možete izabrati urednike, menjati status grupe (otvorena/zatvorena) i podesiti ime grupe i opis';
$lang['Error_updating_groups'] = 'Greška pri osvežavanju grupa';
$lang['Updated_group'] = 'Grupa je uspešno osvežena';
$lang['Added_new_group'] = 'Nova grupa je uspešno kreirana';
$lang['Deleted_group'] = 'Grupa je uspešno obrisana';
$lang['New_group'] = 'Napravi novu grupu';
$lang['Edit_group'] = 'Izmeni grupu';
$lang['group_name'] = 'Naziv grupe';
$lang['group_description'] = 'Opis grupe';
$lang['group_moderator'] = 'Urednik grupe';
$lang['group_status'] = 'Status grupe';
$lang['group_open'] = 'Otvori grupu';
$lang['group_closed'] = 'Zatvorena grupa';
$lang['group_hidden'] = 'Skrivena grupa';
$lang['group_delete'] = 'Obriši grupu';
$lang['group_delete_check'] = 'Obriši ovu grupu';
$lang['submit_group_changes'] = 'Pošalji izmene';
$lang['reset_group_changes'] = 'Resetuj izmene';
$lang['No_group_name'] = 'Morate odrediti ime za ovu grupu';
$lang['No_group_moderator'] = 'Morate odrediti urednika za ovu grupu';
$lang['No_group_mode'] = 'Morate odrediti mod za ovu grupu, otvorena ili zatvorena';
$lang['No_group_action'] = 'Nije odreðena akcija';
$lang['delete_group_moderator'] = 'Obrisati starog moderatora grupe?';
$lang['delete_moderator_explain'] = 'Ukoliko menjate urednika grupe, štiklirajte ovu kutijicu da biste izbrisali starog moderatora iz grupe. U suprotnom, nemojte štiklirati, i korisnik æe postati regularni èlan grupe.';
$lang['Click_return_groupsadmin'] = 'Kliknite %sovde%s za povratak na Administraciju grupa.';
$lang['Select_group'] = 'Izaberite grupu';
$lang['Look_up_group'] = 'Potraži grupu';


//
// Prune Administration
//
$lang['Forum_Prune'] = 'Uprošæavanje foruma';
$lang['Forum_Prune_explain'] = 'Ovim æete izbrisati sve teme na koje nije odgovoreno za vreme (broj dana) koje ste izabrali. Ako ne izaberete broj sve teme æe biti izbrisane. Neæe biti obrisane teme u kojima se glasa niti æe biti obrisana obaveštenja. Ove teme æete morati da izbrišete ruèno.';
$lang['Do_Prune'] = 'Pokreni uprošæavanje';
$lang['All_Forums'] = 'Svi forumi';
$lang['Prune_topics_not_posted'] = 'Uprosti teme na koje nije odgovoreno u ovoliko dana';
$lang['Topics_pruned'] = 'Uprošæene teme';
$lang['Posts_pruned'] = 'Uprošæene poruke';
$lang['Prune_success'] = 'Uprošæavanje foruma je uspešno izvršeno';


//
// Word censor
//
$lang['Words_title'] = 'Cenzurisane reèi';
$lang['Words_explain'] = 'Odavde možete dodati, izmeniti i izbrisati reèi koje æe biti automatski cenzurisane na vašim forumima. Takoðe korisnici neæe moæi da se registruju sa korisnièkim imenom koje sadrži ove reèi. Džokeri (*) su prihvatljivi u polju reèi, npr. *test* æe se poklopiti sa atestirano, test* æe se poklopiti sa testirano, *test æe se poklopiti sa atest.';
$lang['Word'] = 'Reè';
$lang['Edit_word_censor'] = 'Izmeni cenzuru reèi';
$lang['Replacement'] = 'Zamena';
$lang['Add_new_word'] = 'Dodaj novu reè';
$lang['Update_word'] = 'Osveži cenzuru reèi';

$lang['Must_enter_word'] = 'Morate uneti reè i njenu zamenu';
$lang['No_word_selected'] = 'Nije izabrana reè za izmenu';

$lang['Word_updated'] = 'Cenzura za izabranu reè je uspešno osvežena';
$lang['Word_added'] = 'Cenzura reèu je uspeèno dodata';
$lang['Word_removed'] = 'Cenzura za izabranu reè je uspešno izbrisana';

$lang['Click_return_wordadmin'] = 'Kliknite %sovde%s za povratak na Cenzurisane reèi';


//
// Mass Email
//
$lang['Mass_email_explain'] = 'Odavde možete poslati email svim korisnicima, ili korisnicima iz specifiène grupe.  Da biste ovo uradili, email æe biti poslat na priloženu administrativnu adresu, sa blind carbon copijom poslatom svim primaocima. Ako šaljete email velikoj grupi ljudi molimo vas da budete strpljivi posle pritiska na dugme pošalji i nemojte zaustavljati stranicu na pola operacije. Normalono je da pri masovnom slanju emaila operacija traje dugo, i biæete obavešteni kada se izvrši kompletan skript';
$lang['Compose'] = 'Napiši'; 

$lang['Recipients'] = 'Primaoci'; 
$lang['All_users'] = 'Svi korisnici';

$lang['Email_successfull'] = 'Vaša poruka je poslata';
$lang['Click_return_massemail'] = 'Kliknite %sovde%s za povratak na Masovni email';


//
// Ranks admin
//
$lang['Ranks_title'] = 'Administracija pozicija';
$lang['Ranks_explain'] = 'Koristeæi ovu formu možete dodati, izmeniti, pregledati i brisati pozicije. Takoðe možete kreirati proizvoljne pozicije koje mogu biti primenjene na korisnika preko Administracije korisnika';

$lang['Add_new_rank'] = 'Dodaj novu poziciju';

$lang['Rank_title'] = 'Naziv pozicije';
$lang['Rank_special'] = 'Podesi specijalnu poziciju';
$lang['Rank_minimum'] = 'Minimum poruka';
$lang['Rank_maximum'] = 'Maksimum poruka';
$lang['Rank_image'] = 'Slika pozicije (relativna putanja od phpBB2 root-a)';
$lang['Rank_image_explain'] = 'Ovo koristite da biste definisali slièicu koja asocira na poziciju';

$lang['Must_select_rank'] = 'Morate izabrati poziciju';
$lang['No_assigned_rank'] = 'Nije dodeljena specijalna pozicija';

$lang['Rank_updated'] = 'Pozicija je uspešno osvežena';
$lang['Rank_added'] = 'Pozicija je uspešno dodata';
$lang['Rank_removed'] = 'Pozicija je uspešno izbrisana';
$lang['No_update_ranks'] = 'Pozicija je uspešno obrisana, mada korisnièki nalozi koji koriste ovu poziciju nisu osveženi. Moraæete da ruèno resetujete poziciju takvih naloga';

$lang['Click_return_rankadmin'] = 'Kliknite %sovde%s za povratak na Administraciju pozicija';


//
// Disallow Username Admin
//
$lang['Disallow_control'] = 'Kontrola zabranjenih imena';
$lang['Disallow_explain'] = 'Odavde možete kontrolisati korisnièka imena koja se ne mogu koristiti. Zabranjena korisnièka imena mogu da sadrže džokere *. Znajte da vam neæe biti dozvoljeno da odredite bilo koje korisnièko ime koje je veæ registrovano, pa æete morati prvo da obrišete to ime a tek onda ga zabranite';

$lang['Delete_disallow'] = 'Izbriši';
$lang['Delete_disallow_title'] = 'Izbriši zabranjeno korisnièko ime';
$lang['Delete_disallow_explain'] = 'Možete izbrisati zabranjeno korisnièko ime tako što æete izabrati korisnièko ime sa ove liste i kliknuti na dugme Izbriši';

$lang['Add_disallow'] = 'Dodaj';
$lang['Add_disallow_title'] = 'Dodaj zabranjeno korisnièko ime';
$lang['Add_disallow_explain'] = 'Možete zabraniti korisnièko ime koristeæi džokera * kao zamenu za bilo koji karakter';

$lang['No_disallowed'] = 'Nema zabranjenih korisnièkih imena';

$lang['Disallowed_deleted'] = 'Zabranjeno korisnièko ime je uspešno izbrisano';
$lang['Disallow_successful'] = 'Zabranjeno korisnièko ime je uspešno dodato';
$lang['Disallowed_already'] = 'Ime koje ste uneli ne može biti zabranjeno. Veæ postoji u listi, postoji u listi cenzurisanih reèi, ili to korisnièko ime veæ postoji';

$lang['Click_return_disallowadmin'] = 'Kliknite %sovde%s za povratak na Kontrolu zabranjenih imena';


//
// Styles Admin
//
$lang['Styles_admin'] = 'Administracija stilova';
$lang['Styles_explain'] = 'Možete dodati, izbrisati i upravljati stilovima (podlogama i temama) dostupnim vašim korisnicima';
$lang['Styles_addnew_explain'] = 'Sledeæa lista sadrži sve teme koje su dostupne za podloge koje trenutno imate. Stavke u listi još uvek nisu instalirane u phpBB bazu. Da biste instalirali temu jednostavno kliknite na link Install pored stavke';

$lang['Select_template'] = 'Izaberite podlogu';

$lang['Style'] = 'Stil';
$lang['Template'] = 'Podloga';
$lang['Install'] = 'Instiraj';
$lang['Download'] = 'Preuzmi';

$lang['Edit_theme'] = 'Izmeni temu';
$lang['Edit_theme_explain'] = 'U formi ispod možete izmeniti podešavanja za izabranu temu';

$lang['Create_theme'] = 'Napravi temu';
$lang['Create_theme_explain'] = 'Koristite donju formu da napravite novu temu za izabranu podlogu. Kada unosite boje (za koje koristite heksadecimalni oblik) ne smete uneti prefiks #, npr. CCCCCC je ispravno, a #CCCCCC nije';

$lang['Export_themes'] = 'Izvrzi teme';
$lang['Export_explain'] = 'U ovom panelu moæi æete da izvezete podatke za selektovanu podlogu. Izaberite podlogu iz liste ispod i skript æe napraviti konfiguracioni fajl za temu i pokušati da ga snimi u izabrani direktorijum podloge. Ukoliko nije u moguænosti da snimi fajl ponudiæe vam opciju da ga preuzmete. Da bi skript bio u moguænosti da snimi fajl morate podesiti dozvoli za pisanje webserveru za izabrani direktorijum sa podlogama. Za više informacija o ovome pogledajte phpBB 2 users guide.';

$lang['Theme_installed'] = 'Izabrana tema je uspešno instalirana';
$lang['Style_removed'] = 'Izabrani stil je izbrisan iz baze. Da biste u potpunosti izbrisali stil sa vašeg sistema morate izbrisati odgovarajuæi stil iz vašeg direktorijuma sa podlogama.';
$lang['Theme_info_saved'] = 'Informacija o temi koju ste izabrali je snimljena. Sada bi trebalo da vratite dozvolu fajlu theme_info.cfg (i ako je to moguæe i izabranom direktorijumu sa podlogama) na read-only';
$lang['Theme_updated'] = 'Izabrana tema je osvežena. Sada bi trebalo da izvezete podešavanja za novu temu';
$lang['Theme_created'] = 'Tema je napravljena. Trebalo bi da izvezete temu u konfiguracioni fajl teme zbog bezbednog èuvanja ili upotrebe na nekom drugom mestu';

$lang['Confirm_delete_style'] = 'Da li ste sigurni da želite da obrišete ovaj stil';

$lang['Download_theme_cfg'] = 'Izvoznik nije mogao da snimi informacioni fajl teme. Kliknite na dugme ispod da bi ste preuzeli fajl sa vašim browserom. Kada ga budete preuzeli možetee ga prebaciti u direktorijum koji sadrži fajlove podloge. Tada možete spakovati fajlove za distribuciju ili koristiti gde god poželite';
$lang['No_themes'] = 'Podloga koju ste izabrali nema prikaèenih tema. Da napravite novu temu kliknite na link Napravi na panelu sa leve strane';
$lang['No_template_dir'] = 'Ne mogu da otvorim direktorijum da temema. Možda je neèitljiv web serveru ili ne postoji';
$lang['Cannot_remove_style'] = 'Ne možete izbrisati izabrani stil jer je trenutno podrazumevan za forum. Molim vas da promenite podrazumevani stil i pokušate ponovo.';
$lang['Style_exists'] = 'Ime stila koga ste izabrali veæ postoji, molim vas da se vratite i izaberete drugo ime.';

$lang['Click_return_styleadmin'] = 'Kliknite %sovde%s za povratak na Administraciju stilova';

$lang['Theme_settings'] = 'Podešavanje teme';
$lang['Theme_element'] = 'Element teme';
$lang['Simple_name'] = 'Jednostavan naziv';
$lang['Value'] = 'Vrednost';
$lang['Save_Settings'] = 'Snimi podešavanja';

$lang['Stylesheet'] = 'CSS Stylesheet';
$lang['Background_image'] = 'Pozadinska slika';
$lang['Background_color'] = 'Boja pozadine';
$lang['Theme_name'] = 'Naziv teme';
$lang['Link_color'] = 'Boja linka';
$lang['Text_color'] = 'Boja teksta';
$lang['VLink_color'] = 'Boja poseæenog linka';
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
$lang['fontface1'] = 'Lik fonta 1';
$lang['fontface2'] = 'Lik fonta 2';
$lang['fontface3'] = 'Lik fonta 3';
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
$lang['continue_upgrade'] = 'Kada preuzmete vaš konfiguracioni fajl na vaš raèunar možete kliknuti na \'Nastavi nadogradnju\' dugme da biste nastavili sa procesom nadogradnje. Molimo vas da saèekate dok se ne završi proces slanja konfiguracionog fajl i ne završi nadogradnja.';
$lang['upgrade_submit'] = 'Nastavi nadogradnju';

$lang['Installer_Error'] = 'Javila se greška prilikom instalacije';
$lang['Previous_Install'] = 'Dekektovana je prethodna instalacija';
$lang['Install_db_error'] = 'Javila se greška pri pokušaju osvežavanja baze';

$lang['Re_install'] = 'Vaša prethodna instalacija je još uvek aktivna. <br /><br />Ukoliko želite da reinstalirate phpBB 2 kliknite na Yes dugme ispod. Znajte da æete time uništiti sve postojeæe podatke, i neæe biti napravljen povraæaj! Korisnièko ime i šifra administratora koje ste koristili za prijavljivanje na board biæe ponovo kreirani posle reinstalacije, nijedna druga podešavanja neæe biti saèuvana. <br /><br /> Pažljivo razmislite pre nogo kliknete na Yes!';

$lang['Inst_Step_0'] = 'Hvala vam što ste izabrali phpBB 2. Da biste završili instalaciju molimo vas da popunite detalje ispod koji su obavezni. Znajte da bi baza koju hoæete da instalirate trebalo da postoji. Ako instalirate u bazu koja koristi ODBC, npr. MS Access trebalo bi da prvo kreirate DSN za nju pre nego što nastavite dalje.';

$lang['Start_Install'] = 'Poèni instalaciju';
$lang['Finish_Install'] = 'Završi instalaciju';

$lang['Default_lang'] = 'Podrazumevani jezik na boardu';
$lang['DB_Host'] = 'Ime hosta servera sa bazom / DSN';
$lang['DB_Name'] = 'Ime vaše baze';
$lang['DB_Username'] = 'Korisnièko ime baze';
$lang['DB_Password'] = 'Šifra baze';
$lang['Database'] = 'Vaša baza';
$lang['Install_lang'] = 'Izaberite jezik za instalaciju';
$lang['dbms'] = 'Tip baze';
$lang['Table_Prefix'] = 'Prefiks za tabele u bazi';
$lang['Admin_Username'] = 'Korisnièko ime administratora';
$lang['Admin_Password'] = 'Šifra administratora';
$lang['Admin_Password_confirm'] = 'Potvrdite šifru administratora [ Potvrdi ]';

$lang['Inst_Step_2'] = 'Korisnièko ime administratora je napravljeno. U ovoj taèki vaša osnovna instalacija je završena. Sada æemo vas odvesti na ekran koji æe vam omoguæiti administraciju vaše nove instalacije. Obavzno proverite detalje u Generalnoj konfiguraciji i izvršite obavezne izmene. Hvala vam što se izabrali phpBB 2.';

$lang['Unwriteable_config'] = 'Vaš konfiguracioni fajl ne mogu da presnimim preko postojeæeg. Kopija konfiguracionog fajla æe biti preuzeta kada kliknete na dugme ispod. Pošaljite ovaj fajl u isti direktorijug gde se nalazi phpBB 2. Kada to uradite prijavite se koristeæi korisnièko ime i šifru administratora koje ste priložili u prethodnom formularu i posetite kontrolni centar (pojaviæe se link na dnu svakog ekrana kada se budete prijavili) da biste proverili Generalnu konfiguraciju. Hvala vam što ste izabrali phpBB 2.';
$lang['Download_config'] = 'Preuzmi konfiguraciju';

$lang['ftp_choose'] = 'Izaberite metod preuzimanja';
$lang['ftp_option'] = '<br />Obzirom da su FTP ekstenzije podržane u ovoj verziji PHP biæe vam data opcija da prvo probam da automatski putem ftp-a smestim konfiguracioni fajl na svoje mesto.';
$lang['ftp_instructs'] = 'Izabrali ste da pošaljete fajl putem ftp-a na vaš nalog na kome je phpBB 2 automatski. Molimo vas da unesete informacije ispod da biste olakšali proces. Znajte da bi FTP putanja trebalo da bude ista kao i putanja preko ftp-a do vaše phpBB2 instalacije kao da pristupate ftp-u koristeæi bilo koji normalni klijent.';
$lang['ftp_info'] = 'Unesite vaše FTP informacije';
$lang['Attempt_ftp'] = 'Pokušaj da preko ftp-a smestiš konfiguracioni fajl na svoje mesto';
$lang['Send_file'] = 'Samo pošaljite fajl meni i ja æu ga ruèno poslati putem ftp-a';
$lang['ftp_path'] = 'FTP putanja do phpBB 2';
$lang['ftp_username'] = 'Vaše korisnièko ime za FTP';
$lang['ftp_password'] = 'Vaša šifra za FTP';
$lang['Transfer_config'] = 'Poèni prenos';
$lang['NoFTP_config'] = 'Pokušaj postavljanja konfiguracionog fajla putem ftp-a na svoje mesto nije bio uspešan. Molimo vas da preuzmete konfiguracioni fajl i putem ftp-a ga ruèno pošaljete i postavite na pravo mesto.';

$lang['Install'] = 'Instaliraj';
$lang['Upgrade'] = 'Nadogradi';


$lang['Install_Method'] = 'Izaberite metod instalacije';

$lang['Install_No_Ext'] = 'php konfiguracija na vašem serveru ne podržava tip baze koji ste izabrali';

$lang['Install_No_PCRE'] = 'phpBB2 zahteva Perl-kompatibilan modul regularnih ekstenzija za php koju vaša php konfiguracija izgleda ne podržava!';

//
// To je sve narode!
// -------------------------------------------------

?>