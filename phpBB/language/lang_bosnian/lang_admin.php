<?php

/***************************************************************************
 *               lang_admin.php [Bosnia-Croatia-Serbia-Montenegro]
 *                              -------------------
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
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


/***************************************************************************
 *     Prevod s italijanske verzije phpBB Foruma: Golac Željko, Alen Ruvic *
 *     pocetak prevoda      : Subota, 26. oktobar 2002                     *
 *     web                  : http://www.maglaj.info                       *
 *     e-mail               : mrmot@vizzavi.it, ruval@gmx.ch               *
 ***************************************************************************/
 
 
//
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "Osnovna podešavanja";
$lang['Users'] = "Korisnici";
$lang['Groups'] = "Korisnièke grupe";
$lang['Forums'] = "Forumi";
$lang['Styles'] = "Stilovi foruma";

$lang['Configuration'] = "<span style=\"color:#FF0000\">Osnovno podešavanje</span>";
$lang['Permissions'] = "Dozvole";
$lang['Manage'] = "Administracija";
$lang['Disallow'] = "Rezervisanje nadimaka";
$lang['Prune'] = "Brisanje Forumâ";
$lang['Mass_Email'] = "Masovni e-mail";
$lang['Ranks'] = "Rangovi korisnika";
$lang['Smilies'] = "Smjehuljci";
$lang['Ban_Management'] = "Ban kontrola";
$lang['Word_Censor'] = "Cenzura rijeèi";
$lang['Export'] = "Eksportacija";
$lang['Create_new'] = "Kreiraj";
$lang['Add_new'] = "Dodaj";
$lang['Backup_DB'] = "Sigurnosne kopije (Backup)";
$lang['Restore_DB'] = "Povratak u prijašnje stanje";


//
// Index
//
$lang['Admin'] = "Administracija";
$lang['Not_admin'] = "Niste ovlašteni za administriranje foruma";
$lang['Welcome_phpBB'] = "Dobro došli na phpBB Forum!";
$lang['Admin_intro'] = "Zahvaljujemo Vam na povjerenju koje ste nam ukazali izborom <b>phpBB foruma</b> kao naèina komuniciranja vaših posjetilaca.<br> U ovom dijelu administracijskog panela su prikazani razlièiti statistièki podaci Vašeg Foruma. Na ovaj dio se možete prebaciti klikajuæi na link   <u>Statistika/Prisutnost</u> u lijevom meniju stranice. Klikanjem na logo phpBB (lijevi menu stranice; vrh) æete biti prebaèeni na index Foruma. U lijevom menuu stranice prikazani su linkovi koji æe Vas odvesti u razlièite sekcije pomoæu kojih kontrolišete Forum. <br>Nadamo se da æe sve proteæi bez mnogo stresova. Uživajte i ne zaboravite: <span style=\"color:#CC3300\"><i>Sve ovo je samo jedna igra! <b>Nema tog Foruma koji je vrijedniji od vaših živaca!</b></span></i>.";
$lang['Main_index'] = "Index";
$lang['Forum_stats'] = "Statistika";
$lang['Admin_Index'] = "Statistika/Prisutnost";
$lang['Preview_forum'] = "Izgled foruma";

$lang['Click_return_admin_index'] = "Klikni %sovdje%s za povratak na <i>Statistika/Prisutnost</i>";

$lang['Statistic'] = "Statistika foruma";
$lang['Value'] = "Vrijednost";
$lang['Number_posts'] = "Broj komentara";
$lang['Posts_per_day'] = "Poruke po danu";
$lang['Number_topics'] = "Broj tema";
$lang['Topics_per_day'] = "Teme po danu";
$lang['Number_users'] = "Broj korisnika";
$lang['Users_per_day'] = "Korisnici po danu";
$lang['Board_started'] = "Forum aktiviran";
$lang['Avatar_dir_size'] = "Dimenzija directory Avatar";
$lang['Database_size'] = "Dimenzija Database";
$lang['Gzip_compression'] = "Kompresija Gzip";
$lang['Not_available'] = "Nedostupno";

$lang['ON'] = "AKTIVIRANO"; // Ovo je za  GZip kompresiju
$lang['OFF'] = "ISKLJUÈENO"; 


//
// korisni instrumenti za databazu
//
$lang['Database_Utilities'] = "Korisni instrumenti databaze";

$lang['Restore'] = "Povrati prvobitno stanje";
$lang['Backup'] = "Sigurnosna kopija";
$lang['Restore_explain'] = "Posredstvom ovog instrumenta moguæe je povratiti prvobitne tabele foruma phpBB (pod uslovom da ste ranije napravili jednu kopiju). Ukoliko server na kojem se nalazi vaš phpBB forum podržava ovu opciju moguæe je upload-ovat jedan tekstualni file (kompresovan u Gzip-u koji æe zatim automatski biti dekompresovan na serveru. <b>OPREZ!</b> Ovom operacijom æete izbrisati sve podatke do tada prisutne u databazi foruma. <u>Može se desiti da èitava operacija traje malo duže te je zbog toga neophodno saèekati i ne izlaziti iz ove sekcije dok sve ne bude završeno</u>.";
$lang['Backup_explain'] = "Posredstvom ovog instrumenta moguæe je kreirati jednu kopiju kako strukture tabela koje èine phpBB forum tako i podataka sadržanih u njima. Ukoliko ste nakon instaliranja originalnog phpBB foruma dodali i neke druge elemente koji proširuju moguænosti foruma (npr.chat), neophodno je unijeti i nazive tabela koje èine strukturu tih elemenata (upisati ih i <b>Dodatne tabele</b>; odvojiti zarezom). Ukoliko server na kojem je instaliran phpBB forum dozvoljava, moguæe je kompresovati file-ove korištenjem Gzip-a kako bi se smanjile njihove dimenzije prije dowload-ovanja.";

$lang['Backup_options'] = "Opcije sigurnosne kopije (<b>Backup</b>)";
$lang['Start_backup'] = "Zapoèni Backup";
$lang['Full_backup'] = "Kompletan Backup";
$lang['Structure_backup'] = "Backup strukture foruma (samo struktura tabela)";
$lang['Data_backup'] = "Backup podataka (samo podaci)";
$lang['Additional_tables'] = "Dodatne tabele";
$lang['Gzip_compress'] = "Gzip kompresija file-ova";
$lang['Select_file'] = "Izaberi file";
$lang['Start_Restore'] = "Zapoèni povratak u prijašnje stanje";

$lang['Restore_success'] = "Prijašnje stanje databaze je uspješno povraæeno.<br /><br />Forum bi sada treba da bude kakav je bio u momentu kreiranja sigurnosne kopije (Backup).";
$lang['Backup_download'] = "Download æe ubrzo zapoèeti. Molimo Vas za malo strpljenja.";
$lang['Backups_not_supported'] = "Na žalost Backup databaze nije moguæ jer ga sistem ne podržava.";

$lang['Restore_Error_uploading'] = "Pojavila se greška prilikom upload-ovnja sigurnosne kopije.";
$lang['Restore_Error_filename'] = "Ime file-a nije taèno. Pokušaj sa alternativnim imenom.";
$lang['Restore_Error_decompress'] = "Nije moguæe dekompresovati Gzip file. MolimoVasda za upload tekstualnog file-a.";
$lang['Restore_Error_no_file'] = "Nijedan file nije upload-ovan.";


//
// dozvole i ovlastenja
//
$lang['Select_a_User'] = "Izaberi korisnika";
$lang['Select_a_Group'] = "Izaberi grupu korisnika";
$lang['Select_a_Forum'] = "Izaberi Forum";
$lang['Auth_Control_User'] = "Dozvole i ovlaštenja korisnika"; 
$lang['Auth_Control_Group'] = "Dozvole i ovlaštenja grupe korisnika"; 
$lang['Auth_Control_Forum'] = "Dozvole i ovlaštenja Foruma"; 
$lang['Look_up_User'] = "Naði korisnika"; 
$lang['Look_up_Group'] = "Naði grupu"; 
$lang['Look_up_Forum'] = "Idi na Forum (temu)"; 

$lang['Group_auth_explain'] = "Ovdje je moguæe definisati dozvole i ovlaštenja moderatorima pojedinih grupa. Ne zaboravite da kada modifikujete dozvole jedne grupe, korisnik može svakako pristupiti forumu zahvaljujuæi njegovim individualnim ovlaštenjima. U ovom sluèaju æete biti svakako obaviješteni.";
$lang['User_auth_explain'] = "Ovdje je moguæe modifikovati dozvole i ovlaštenja koje je administrator dodijelio svakom pojedinom korisniku.Ne zaboravite da kada to uèinite korisnik æe moæi uæi na forum ukoliko mu to dozvoljavaju pravila (dozvole) pojedine grupe. U ovom sluèaju æete biti svakako obaviješteni.";
$lang['Forum_auth_explain'] = "Ovdje je moguæe definisati razlièite vrste dozvola i ovlaštenja za svaki pojedinaèni forum (temu foruma). Pod <b>dozvolama</b> se podrazumijevaju razne operacije koje korisnici foruma mogu izvršavati kao što su: <i>upis komentara, brisanje ili modifikovanje <u>vlastitih</u> komentara</i> i slièno. <b>Ovlaštenjima</b> raspolažu moderatori i to su uglavnom <u>pojedine</u> funkcije <b>kontrole</b> foruma (<i>razne vrste zabrana korisnicima, eventualna prepravka i brisanje <u>tudjih</u> komentara</i> itd.). <b>Administrator foruma</b> (kao najodgovornija osoba zbog svih kontrolnih funkcija koje mu dopušta phpBB forum) bi zbog toga trebao biti obazriv pri izboru osoba koje æe obavljati funkciju <b>Moderatora</b>.  Treba imati na umu da funkcije <b>Moderatora</b> zavise od toga koliko æe mu ovlasti dodijeliti <b>Administrator</b>, što znaèi da jedan moderator ne mora imati kontrolu nad svim kategorijama ili temama Foruma.<br>  Na raspolaganju su dvije moguænosti kontrole foruma (ili neke njegove sekcije): <b>jednostavna</b> i <b>napredna</b>. <b>Napredni</b> naèin kontrole Vam nudi veæe moguænosti podešavanja kontrolnih nivoa unutar svake pojedinaène sekcije foruma. Mijenjanjem dozvola i ovlaštenja biæe Vam prikazano koja vrsta korisnika može obavljati razlièite operacije na forumu.<br><br>Kod jednostavne kontrole opcije su sljedeæe:<br><br><b>Javan</b> - svi mogu uèestvovati u radu Foruma.<br><b>Registrovan</b> - samo registrovani èlanovi mogu uèestvovati u radu Foruma.<br><b>Registrovan [skriven]</b> - obiènim posjetiocima takav Forum neæe biti vidljiv<br><b>Privatan</b> - u radu Foruma mogu uèestvovati samo registrovani korisnici sa specijalnim pristupom (vidi menu <i>Korisnici - dozvole i ovlaštenja</i>).<br><b>Privatan [skriven]</b> - Forum je vidljiv samo registrovanim èlanovima sa specijalnim pristupom.<br><b>Moderatori</b> - Forum je dostupan samo korisnicima sa moderatorskim statusom.<br><b>Moderatori [skriven]</b> - Pristup je dozvoljen samo korisnicima sa moderatorskim statusom i nevidljiv je za ostale korisnike.";

$lang['Simple_mode'] = "Jednostavna kontrola";
$lang['Advanced_mode'] = "Napredna kontrola";
$lang['Moderator_status'] = "Moderatorski status";

$lang['Allowed_Access'] = "Dozvoljen pristup";
$lang['Disallowed_Access'] = "Nedozvoljen pristup";
$lang['Is_Moderator'] = "Jeste moderator";
$lang['Not_Moderator'] = "Nije moderator";

$lang['Conflict_warning'] = "Pažnja, konflikt ovlaštenja";
$lang['Conflict_access_userauth'] = "Ovaj korisnik još uvijek ima pristup ovom Forumu posredstvom </i>grupe korisnika</i> èiji je èlan. Ukoliko želiš u potpunosti eliminirati sva prava na pristup ovom korisniku, moguæe je <li></li>modifikovati dozvole i ovlaštenja èitave grupe kojoj pripada<li></li>iskljuèiti korisnika iz grupe te zatim ogranièiti njegova pristupna prava. Prava grupe (i Foruma u kojima uèestvuje) su navedeni ovdje:";
$lang['Conflict_mod_userauth'] = "Ovaj korisnik još uvijek ima moderatorski status u Forumu posredstvom <i>grupe korisnika</i> èiji je èlan. Ukoliko želiš oduzeti moderatorski status ovom korisniku, moguæe je <li></li>modifikovati dozvole i ovlaštenja èitave grupe kojoj pripada<li></li>iskljuèiti korisnika iz grupe te zatim oduzeti njegov moderatorski status. Prava grupe (i Foruma u kojima uèestvuje) su navedeni ovdje:";

$lang['Conflict_access_groupauth'] = "Sljedeæi korisnici još uvijek imaju pravo pristupa Forumu posredstvom dozvola koje imaju. Ukoliko želiš u potpunosti eliminirati sva prava na pristup ovim korisnicima, moguæe je <li></li>modifikovati dozvole i ovlaštenja  grupe kojoj pripada<li></li>iskljuèiti korisnike iz grupe te zatim ogranièiti njihova pristupna prava. Prava grupe (i Foruma u kojima uèestvuju) su navedeni ovdje:";
$lang['Conflict_mod_groupauth'] = "Sljedeæi korisnici još uvijek imaju moderatorski status u Forumu posredstvom dozvola koje imaju. Ukoliko želiš oduzeti moderatorski status ovim korisnicima, moguæe je <li></li>modifikovati dozvole i ovlaštenja grupe kojoj pripadaju<li></li>iskljuèiti korisnike iz grupe te zatim oduzeti moderatorski status. Prava grupe (i Foruma u kojima uèestvuju) su navedeni ovdje:";

$lang['Public'] = "Javan";
$lang['Private'] = "Privatan";
$lang['Registered'] = "Registrovan";
$lang['Administrators'] = "Administratori";
$lang['Hidden'] = "skriven";

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = "SVI";
$lang['Forum_REG'] = "ÈLANOVI";
$lang['Forum_PRIVATE'] = "PRIVATNO";
$lang['Forum_MOD'] = "MODERATOR";
$lang['Forum_ADMIN'] = "ADMIN";

$lang['View'] = "Vidjeti";
$lang['Read'] = "Èitanje";
$lang['Post'] = "Pisanje poruka";
$lang['Reply'] = "Odgovoranje";
$lang['Edit'] = "Prepravka poruka";
$lang['Delete'] = "Brisanje poruka";
$lang['Sticky'] = "Pokretanje važnih tema";
$lang['Announce'] = "Pisanje obavještenja"; 
$lang['Vote'] = "Glasanje";
$lang['Pollcreate'] = "Kreiranje anketa";

$lang['Permissions'] = "Dozvole i ovlaštenja";
$lang['Simple_Permission'] = "Permesso semplice";

$lang['User_Level'] = "Rang korisnika"; 
$lang['Auth_User'] = "Korisnik";
$lang['Auth_Admin'] = "Administrator";
$lang['Group_memberships'] = "Èlanstvo grupe korisnika";
$lang['Usergroup_members'] = "Ovu grupu èine sljedeæi èlanovi";

$lang['Forum_auth_updated'] = "Dozvole Foruma Ažurirane";
$lang['User_auth_updated'] = "Korisnièke dozvole ažurirane";
$lang['Group_auth_updated'] = "Dozvole grupe ažurirane";

$lang['Auth_updated'] = "Dozvole su ažurirane";
$lang['Click_return_userauth'] = "Klikni %sovdje%s za povratak na panel <i>Dozvole i Ovlaštenja korisnika</i>";
$lang['Click_return_groupauth'] = "Klikni %sovdje%s za povratak na panel <i>Dozvole i Ovlaštenja grupe korisnika</i>";
$lang['Click_return_forumauth'] = "Klikni %sovdje%s za povratak na panel <i>Dozvole i Ovlaštenja Foruma</i>";


//
// Banovanje korisnika
//
$lang['Ban_control'] = "Kontrolni centar za blokiranje korisnika";
$lang['Ban_explain'] = "Pošto se na svakom forumu prije ili kasnije pojavi neko ko ne poštuje pravila Foruma, administrator, kao lice odgovorno za ono što se dešava na forumu, ima potrebu za instrumentima koji æe mu omoguæiti da takve korisnike na neki naèin drži pod konrolom. U ovom odjeljku moguæe je, posredstvom razlièitih instrumenata, zabraniti pristup <em>'nevaljalcima'</em> èak i samom indexu foruma blokiranjem èitavog IP ili jednog njegovog segmenta. Treba, medjutim, voditi raèuna da ukoliko se radi o dinamièkim IP (tj.da provider pri svakoj novoj koneksiji na Internet odredi razlièit IP) biæe vrlo teško sprijeèiti ulazak korisniku. Zbog toga je potrebno biti pažljiv prilikom uvodjenja ovakvih mjera jer se može desiti da neko dugi, kome je igrom sluèaja dodijeljen blokirani IP, neæe moæi uæi na Forum.<br>Takodje, blokiranjem e-mail može se sprijeèiti ponovna registracija korisnika pod nekim drugim nadimkom (naravno, pod uslovom da koristi isti e-mail kada se želi ponovno registrovati). <br>Napomena:<br><u>Nemojte se zavaravati da na ovaj naèin možete zaista zabraniti nekome da dodje na Forum, pogotovo ne onima koji poznaju principe funkcionisanja Interneta jer takvi æe sigurno naæi naèin da zaobiðu sve vaše zabrane.</u> Ovdje vrijedi pravilo da što je neko manje vièan svoj ovoj tehnologiji lakše ga je blokirati. Takodje imajte na umu da svaka zloupotreba ili svojevoljno korištenje ovih instrumenata za posljedicu obicno ima revolt i napuštanje foruma od strane njegovih korisnika.";
$lang['Ban_explain_warn'] = "Molimo Vas da obratite pažnju prilikom blokiranja intervala IP adresa. Prilikom upisivanja jednog intervala IP adresa, biæe blokirani svi IP izmedju poèetka i kraja intervala. Biæe uèinjeni pokušaji minimiziranja broja IP adresa ubaèenih u data-bazu automatskim dodavanjem odgovarajuæih jolly-a na odgovarajuæi naèin. Ukoliko je zaista neophodno blokiranje korisnika posredstvom IP nastojte biti što precizniji u definisanju <i>'inkriminiranog'</i> IP.";

$lang['Select_username'] = "Izaberi korisnika";
$lang['Select_ip'] = "Izaberi IP";
$lang['Select_email'] = "Izaberi e-mail";

$lang['Ban_username'] = "Ogranièi pristup jednom ili više uèesnika Foruma";
$lang['Ban_username_explain'] = "Moguæe je ogranièiti pristup jednom ili više uèesnika. Navesti nadimak (Username) korisnika i potvrditi.";

$lang['Ban_IP'] = "Blokiranje IP ili Hostname";
$lang['IP_hostname'] = "Adresa IP ili hostname";
$lang['Ban_IP_explain'] = "Navesti IP ili Hostname koji se žele blokirati i odvojiti ih zarezom (,). Poèetak i kraj jednog intervala IP adresa ogranièiti sa crticom (-); koristiti simbol * kao jolly";

$lang['Ban_email'] = "Zabrana pristupa posredstvom blokiranja e-mail";
$lang['Ban_email_explain'] = "Navesti jedan ili više e-mail koji se žele blokirati i odvojiti ih zarezom (,). Koristiti simbol * kao jolly  npr. <i>*@hotmail.com</i>";

$lang['Unban_username'] = "Skidanje zabrane jednom ili više korisnika";
$lang['Unban_username_explain'] = "Moguæe je jednom operacijom izabrati više korisnika kojima se želi skinuti zabrana (kombinacija taster CTRL tastature i miša).";

$lang['Unban_IP'] = "Skidanje zabrane jednoj ili više IP adresa";
$lang['Unban_IP_explain'] = "Moguæe je jednom operacijom izabrati više IP adresa kojima se želi skinuti zabrana (kombinacija taster CTRL tastature i miša).";

$lang['Unban_email'] = "Deblokiranje e-mail adresa";
$lang['Unban_email_explain'] = "Moguæe je jednom operacijom izabrati više e-mail adresa koje se žele deblokirati (kombinacija taster CTRL tastature i miša).";

$lang['No_banned_users'] = "Nema blokiranih korisnika";
$lang['No_banned_ip'] = "Nema blokiranih IP";
$lang['No_banned_email'] = "Nema blokiranih e-mail adresa";

$lang['Ban_update_sucessful'] = "Lista blokiranih korisnika je uspješno ažurirana.";
$lang['Click_return_banadmin'] = "Klikni %sovdje%s za povratak na <i>Kontrolni centar za blokiranje korisnika</i>";


//
// osnovno podesavanje
//
$lang['General_Config'] = "Osnovno podešavanje";
$lang['Config_explain'] = "Pomoæu ovog modula moguæe je konfigurirati osnovnu postavku Foruma. Za konfiguraciju parametara vezanih za pojedine sekcije Foruma ili njegovih uèesnika koristiti odgovarajuæi link u lijevom menuu.";

$lang['Click_return_config'] = "Klikni %sovdje%s za povratak na Osnovno podešavanje";

$lang['General_settings'] = "Glavna konfiguracija Foruma";
$lang['Server_name'] = "Ime Domain-a";
$lang['Server_name_explain'] = "Ime domain-a na kojem se nalazi Forum";
$lang['Script_path'] = "Pozicija Foruma";
$lang['Script_path_explain'] = "Pozicija na kojoj je instaliran phpBB2 u odnosu na ime domin-a";
$lang['Server_port'] = "Vrata Servera";
$lang['Server_port_explain'] = "Vrijednost je obièno 80; promijeniti u sluèaju potrebe nakon konsultovanja administratora servera";
$lang['Site_name'] = "Ime Foruma (ili site-a)";
$lang['Site_desc'] = "Opis Foruma (ili site-a)";
$lang['Board_disable'] = "Blokiraj Forum";
$lang['Board_disable_explain'] = "OPREZ!! Ovom opcijom Forum postaje nedostupan korisnicima! Ne izlazi nakon blokiranja Foruma jer neæeš moæi uæi ponovo!!!!";
$lang['Acct_activation'] = "Osposobi aktivaciju account-a";
$lang['Acc_None'] = "Niko"; // These three entries are the type of activation
$lang['Acc_User'] = "Korisnik";
$lang['Acc_Admin'] = "Administrator";

$lang['Abilities_settings'] = "Osnovne postavke vezane za Forum i korisnike";
$lang['Max_poll_options'] = "Ankete - maksimalan broj moguæih odgovora";
$lang['Flood_Interval'] = "Flood-period";
$lang['Flood_Interval_explain'] = "Najmanja pauza neophodna izmedju upisivanja dva komentara istog autora (vrijeme u sekundama)";
$lang['Board_email_form'] = "Slanje e-mailova posredstvom Foruma";
$lang['Board_email_form_explain'] = "Korisnici mogu meðusobno razmijenjivati e-mailove posredstvom Foruma.";
$lang['Topics_per_page'] = "Broj argumenata po stranici";
$lang['Posts_per_page'] = "Broj poruka po stranici";
$lang['Hot_threshold'] = "Broj neophodnih poruka kako bi jedna tema bila klasifikovana kao <b>popularna</b>.";
$lang['Default_style'] = "Zvanièan stil Foruma";
$lang['Override_style'] = "Poništi stil Foruma kojeg koristi èlan posredstvom vlastitog profila.";
$lang['Override_style_explain'] = "Korisnik je prisiljen koristiti stil Foruma kojeg odredi Administrator.";
$lang['Default_language'] = "Glavni jezik Foruma";
$lang['Date_format'] = "Format datuma";
$lang['System_timezone'] = "Vremenska zona Foruma";
$lang['Enable_gzip'] = "Omoguæena GZIP kompresija";
$lang['Enable_prune'] = "Omoguæi brisanje Forumâ";
$lang['Enable_prune_explain'] = "Automatsko brisanje tema (foruma) ukoliko nije bilo odgovora nakon nekog odreðenog vremenskog perioda.";
$lang['Allow_HTML'] = "Dozvoli HTML";
$lang['Allow_BBCode'] = "Dozvoli BBCode";
$lang['Allowed_tags'] = "Dozvoljeni HTML tag-ovi";
$lang['Allowed_tags_explain'] = "Odvoji tag-ove zarezom";
$lang['Allow_smilies'] = "Dozvoli smjehuljke";
$lang['Smilies_path'] = "Pozicija za pohranjivanje smjehuljaka";
$lang['Smilies_path_explain'] = "Pozicija gdje se nalaze pohranjeni smjehuljci u odnosu na root direktorij phpBB, npr. <i>images/smilies</i>";
$lang['Allow_sig'] = "Dozvoli potpis";
$lang['Max_sig_length'] = "Maksimalna dužina potpisa";
$lang['Max_sig_length_explain'] = "Najveæi broj karaktera dopušten za kreiranje potpisa korisnika";
$lang['Allow_name_change'] = "Dozvoli zamjenu nadimka (<i>username</i>)";

$lang['Avatar_settings'] = "Konfiguracija Avatara";
$lang['Allow_local'] = "Osposobi galeriju avatara";
$lang['Allow_remote'] = "Dozvoli vanjske avatare";
$lang['Allow_remote_explain'] = "Koriste se avatari koji se nalaze na nekoj drugoj internet stranici (<i>linkovanje</i>)";
$lang['Allow_upload'] = "Dozvoli upload avatara";
$lang['Allow_upload_explain'] = "Omoguæava se korisnicima da u data-bazu Vašeg foruma ubace sliku po želji. Ovo može dovesti do poveæanja dimenzija data-baze";
$lang['Max_filesize'] = "Maksimalna velièina file-a avatar";
$lang['Max_filesize_explain'] = "Maksimalna velièina pojedinaènog file-a kojeg korisnik može ubaciti u data-bazu foruma";
$lang['Max_avatar_size'] = "Maksimalne dimenzije avatara";
$lang['Max_avatar_size_explain'] = "(visina x širina u pixelima)";
$lang['Avatar_storage_path'] = "Pozicija pohranjivanja avatara";
$lang['Avatar_storage_path_explain'] = "Pozicija gdje se nalaze pohranjeni avatari u odnosu na root direktorij phpBB, npr. <i>images/avatars</i>";
$lang['Avatar_gallery_path'] = "Pozicija galerije avatara";
$lang['Avatar_gallery_path_explain'] = "Pozicija u kojoj administrator pohranjuje  avatare u odnosu na root direktorija phpBB, npr. images/avatars/gallery";

$lang['COPPA_settings'] = "Konfiguracija COPPA - saglasnost roditelja za uèešæe maloljetnika na forumu";
$lang['COPPA_fax'] = "Broj FAX-a na koji poslati saglasnost";
$lang['COPPA_mail'] = "Adresa na koju poslati saglasnost";
$lang['COPPA_mail_explain'] = "Poštanska adresa administratora na koju se može poslati saglasnost";

$lang['Email_settings'] = "Konfiguracija E-mail";
$lang['Admin_email'] = "E-mail Admnistratora";
$lang['Email_sig'] = "E-mail potpis";
$lang['Email_sig_explain'] = "Ovo æe biti potpis u svakom e-mail poslanom sa Foruma";
$lang['Use_SMTP'] = "Koristi Server SMTP za e-mail";
$lang['Use_SMTP_explain'] = "Odgovori <i>da</i> ukoliko želiš ili moraš poslati e-mail posredstvom jednog posebnog servera umjesto lokalnog e-mail sistema";
$lang['SMTP_server'] = "Adresa Server SMTP";
$lang['SMTP_username'] = "SMTP Username";
$lang['SMTP_username_explain'] = "Unijeti SMTP username ukoliko to Vaš smtp server zahtijeva";
$lang['SMTP_password'] = "SMTP Password";
$lang['SMTP_password_explain'] = "Unijeti SMTP password ukoliko to Vaš smtp server zahtijeva";

$lang['Disable_privmsg'] = "Privatne poruke";
$lang['Inbox_limits'] = "Maksimalan broj primljenih poruka";
$lang['Sentbox_limits'] = "Maksimalan broj poslanih poruka";
$lang['Savebox_limits'] = "Maksimalan broj spašenih poruka";

$lang['Cookie_settings'] = "Konfiguracija Cookie"; 
$lang['Cookie_settings_explain'] = "Ovdje je moguæe konfigurirati cookie koji se šalju browseru korisnika. U mnogim sluèajevima originalna  instalacijska konfiguracija je dovoljna. U sluèaju da želite izvršiti prepravke, uèinite to pažljivo pošto pogrešna konfiguracija može sprijeèiti pristup korisnika forumu.";
$lang['Cookie_name'] = "Naziv Cookie";
$lang['Cookie_domain'] = "Domain Cookie";
$lang['Cookie_path'] = "Pozicija Cookie (<i>path</i>)";
$lang['Session_length'] = "Dužina trajanja pojedinaène sesije [ u sekundama ]";
$lang['Cookie_secure'] = "Cookie secure [ http ]";
$lang['Cookie_secure_explain'] = "Ukoliko Vaš server koristi SSL, osposobite ovu fukciju. U protivnom, ostavite neaktivirano.";


//
// Kontrola Foruma
//
$lang['Forum_admin'] = "Administracija Foruma";
$lang['Forum_admin_explain'] = "Ovdje možete organizovati (dodati, modifikovati, obrisati, preurediti, sinhronizovati) Forum po kategorijama i glavnim temama rasprave";
$lang['Edit_forum'] = "Modifikuj temu";
$lang['Create_forum'] = "Kreiraj novu temu (Forum)";
$lang['Create_category'] = "Kreiraj novu kategoriju Foruma";
$lang['Remove'] = "Rimuovi";
$lang['Action'] = "Akcija";
$lang['Update_order'] = "Ažuriraj red";
$lang['Config_updated'] = "Konfiguracija Foruma uspješno ažurirana.";
$lang['Edit'] = "Ispraviti";
$lang['Delete'] = "Obrisati";
$lang['Move_up'] = "Pomjeri gore";
$lang['Move_down'] = "Pomjeri dole";
$lang['Resync'] = "Sinhronizovati";
$lang['No_mode'] = "Nikakav naèin odreðen";
$lang['Forum_edit_delete_explain'] = "Pomoæu ovog formulara moguæe je personalizirati glavne opcije Foruma.";

$lang['Move_contents'] = "Pomjeri sve komentare u:";
$lang['Forum_delete'] = "Obriši Forum";
$lang['Forum_delete_explain'] = "Pomoæu ovog instrumenta moguæe je obrisati bilo koji Forum (temu) ili kategoriju te odluèiti gdje pomjeriti njegov sadržaj.";

$lang['Forum_settings'] = "Generalno podešavanje Foruma";
$lang['Forum_name'] = "Ime Foruma";
$lang['Forum_desc'] = "Opis Foruma";
$lang['Forum_status'] = "Status Foruma";
$lang['Forum_pruning'] = "Automatsko brisanje";

$lang['prune_freq'] = "Prekontroliši starost argumenata svakih";
$lang['prune_days'] = "Obriši argumente na koje nije odgovoreno u roku od";
$lang['Set_prune_data'] = "Aktivirna je opcija za automatsko brisanje argumenata ali nije odreðeno koliko èesto. MolimVasda se vratiš nazad i da odrediš broj dana za kontrolu ili brisanje argumenta.";

$lang['Move_and_Delete'] = "Pomjeranje i brisanje";

$lang['Delete_all_posts'] = "Obriši sve komentare";
$lang['Nowhere_to_move'] = "Ne pomjeriti nigdje";

$lang['Edit_Category'] = "Prepravka kategorije";
$lang['Edit_Category_explain'] = "Ovdje je moguæe prepraviti naziv pojedine kategorije Foruma.";

$lang['Forums_updated'] = "Informacije vezane za kategorije i Forume su uspješno ažurirane.";

$lang['Must_delete_forums'] = "Potrebno je obrisati sve Forume ove kategorije kako bi i sama kategorija mogla biti obrisana.";

$lang['Click_return_forumadmin'] = "Klikni %sovdje%s za povratak na panel <i>Administracija Foruma</i>";


//
// administracija smjehuljaka
//
$lang['smiley_title'] = "Smjehuljci";
$lang['smile_desc'] = "Ovdje je moguæe dodati, obrisati i modifikovati tzv. <i>emoticons</i> pomoæu kojih se mogu ilustrovati razna raspoloženja u komentarima i privatnim porukama.";

$lang['smiley_config'] = "Konfiguracija smjehuljaka";
$lang['smiley_code'] = "Kod smjehuljka";
$lang['smiley_url'] = "Slika smjehuljka (File)";
$lang['smiley_emot'] = "Emocija smjehuljka";
$lang['smile_add'] = "Dodaj jedan novi smjehuljak";
$lang['Smile'] = "Smjehuljak";
$lang['Emotion'] = "Emocija";

$lang['Select_pak'] = "Izaberi paket Smjehuljaka (.pak)";
$lang['replace_existing'] = "Zamijeni postojeæe Smjehuljke.";
$lang['keep_existing'] = "Zadrži postojeæe Smjehuljke.";
$lang['smiley_import_inst'] = "Potrebno je dekompresovati paket (.pak) Smjehuljaka i pohraniti file-ove u odgovarajuæi direktorij za instalaciju. Zatim posredstvom ovog formulara izaberi paket koji želite dodati.";
$lang['smiley_import'] = "Instaliranje novog paketa Smjehuljaka";
$lang['choose_smile_pak'] = "Izaberi paket smjehuljaka (file.<i><b>pak</b></i>)";
$lang['import'] = "Dodaj Smjeuljke";
$lang['smile_conflicts'] = "Šta uèiniti u sluèaju konflikta?";
$lang['del_existing_smileys'] = "Obriši postojeæe Smjehuljke prije dodavanja novih";
$lang['import_smile_pack'] = "Dodaj novi paket Smjehuljaka";
$lang['export_smile_pack'] = "Kreiraj paket Smjehuljaka";
$lang['export_smiles'] = "Za kreiranje novog paketa Smjehuljaka klikni %sovdje%s za downloada file-a <b>.pak</b> smjehuljaka. Imenuj file kako želiš zadržavajuæi sufiks <i><b>.pak</b></i>.Zatim kreiraj jedan zip file koji æe sadržavati slièice smjehuljaka i kreirani konfiguracijski <b><i>.pak</i></b> file.";

$lang['smiley_add_success'] = "Smjehuljci su uspješno dodani.";
$lang['smiley_edit_success'] = "Smjehuljci su uspješno ažurirani.";
$lang['smiley_import_success'] = "Paket Smjehuljaka je uspješno instaliran!";
$lang['smiley_del_success'] = "Smjehuljci su uspješno obrisani.";
$lang['Click_return_smileadmin'] = "Klikni %sovdje%s za povratak na <i>Administracija Smjehuljaka</i>";


//
// administracija korisnika
//
$lang['User_admin'] = "Administracija korisnika";
$lang['User_admin_explain'] = "Pomoæu ovog kontrolnog panela moguæe je kontrolisati i eventualno modifikovati postavke koje je svaki korisnik odredio u momentu svoje registracije na Forum. <i>Za odreðivanje dozvola korisnicima molimo Vas da koristite panele za Administraciju dozvola i ovlaštenja korisnika i grupa korisnika.</i>";

$lang['Look_up_user'] = "Izaberi korisnika";

$lang['Admin_user_fail'] = "Ažuriranje profila korisnika nije bilo moguæe obaviti.";
$lang['Admin_user_updated'] = "Profil korisnika je uspješno ažuriran.";
$lang['Click_return_useradmin'] = "Klikni %sovdje%s za povratak na Administracija korisnika";

$lang['User_delete'] = "Obriši ovog korisnika";
$lang['User_delete_explain'] = "Klikni ovdje za brisanje korisnika. <b><span style=\"color:#FF0000\">PAŽNJA! Ova operacija nije reverzibilna!</span></b> ";
$lang['User_deleted'] = "Korisnik je uspješno obrisan";

$lang['User_status'] = "Korisnik je aktivan";
$lang['User_allowpm'] = "Može slati privatne poruke";
$lang['User_allowavatar'] = "Može koristiti Avatare";

$lang['Admin_avatar_explain'] = "Ovdje je moguæe modifikovati ili obrisati aktuelni avatar korisnika.";

$lang['User_special'] = "Specijalne opcije - samo za administratora";
$lang['User_special_explain'] = "Ove opcije korisnik ne može modifikovati. Ovdje možete odrediti njihov status i ostale opcije kojima korisnici nemaju pristupa.";


//
// administracija grupa korisnika
//
$lang['Group_administration'] = "Administracija grupa korisnika";
$lang['Group_admin_explain'] = "Posredstvom ovog panela moguæe je administrirati sve grupe korisnika prisutne na Forumu. Moguæe je kreirati,  modifikovati ili obrisati postojeæe grupe, odrediti njihove Moderatore, regulisati status grupe (otvoren tip/zatvoren tip), odrediti ime i opis.";
$lang['Error_updating_groups'] = "Pojavila se greška prilikom ažuriranja korisnièkih grupa.";
$lang['Updated_group'] = "Grupa korisnika je uspješno ažurirana.";
$lang['Added_new_group'] = "Nova grupa korisnika je uspješno kreirana.";
$lang['Deleted_group'] = "Grupa korisnika je uspješno obrisana.";
$lang['New_group'] = "Kreiraj novu grupu";
$lang['Edit_group'] = "Modifikuj grupu";
$lang['group_name'] = "Ime grupe";
$lang['group_description'] = "Opis grupe";
$lang['group_moderator'] = "Moderator grupe";
$lang['group_status'] = "Status grupe";
$lang['group_open'] = "Otvorena grupa";
$lang['group_closed'] = "Zatvorena grupa";
$lang['group_hidden'] = "Skrivena grupa";
$lang['group_delete'] = "Obriši grupu";
$lang['group_delete_check'] = "Obriši ovu grupu";
$lang['submit_group_changes'] = "Potvrdi modifike";
$lang['reset_group_changes'] = "Anuliraj modifike";
$lang['No_group_name'] = "Potrebno je odrediti ime za ovu grupu.";
$lang['No_group_moderator'] = "Porebno je odrediti moderatora ove grupe.";
$lang['No_group_mode'] = "Potrebno je odrediti status grupe (<i>otvoren/zatvoren</i>)";
$lang['delete_group_moderator'] = "Obriši aktuelnog moderatora grupe?";
$lang['delete_moderator_explain'] = "Potvrdi ovaj box ukoliko želiš zamijeniti moderatora grupe. U suprotnom (ukoliko ne potvrdiš) korisnik æe postati normalan èlan grupe.";
$lang['Click_return_groupsadmin'] = "Klikni %sovdje%s za povratak na <i>Administracija grupa korisnik</i>a";
$lang['Select_group'] = "Izaberi grupu";
$lang['Look_up_group'] = "Prekontroliši grupu";


//
// Administracija autoamtskog brisanja (PRUNE)
//
$lang['Forum_Prune'] = "Brisanje Forumâ";
$lang['Forum_Prune_explain'] = "Posredstvom ovog instrumenta moguæe je automatsko brisanje tema ukoliko na njih nije odgovoreno u toku nekog vremenskog roka. Ukoliko ne upišete nikakav broj, a opcija <i><b>Omoguæi brisanje tema</b></i> u <b><i>Osnovnim podešavanjima</i></b> je aktivirana, biæe eliminirane sve teme. Neæe biti obrisane teme s aktivnom anketom, kao ni <i>Obavještenja</i>. Eliminaciju ovih tema je potrebno izvesti manualno. Preporuèujemo da prije ove operacije, za svaki slucaj, obavite jedan Backup podataka data-baze pošto je eliminacija definitivna. ";
$lang['Do_Prune'] = "Obriši";
$lang['All_Forums'] = "Sve Forume";
$lang['Prune_topics_not_posted'] = "Obriši teme na koje nije odgovorenu u roku od ";
$lang['Topics_pruned'] = "Obrisane teme";
$lang['Posts_pruned'] = "Obrisani komentari";
$lang['Prune_success'] = "Brisanje Forumâ je uspješno obavljeno.";


//
//Cenzura rijeèi
//
$lang['Words_title'] = "Cenzura rijeèi";
$lang['Words_explain'] = "Posredstvom ovog instrumenta moguæe je kreirati listu cenzurisanih rijeèi koje se u svakom momentu mogu prepraviti ili obrisati. Forum æe automatski rijeè zamijeniti onim što Vi odredite. Pored toga, nije moguæa registracija nadimka (<i>username</i>) ukoliko je rijeè cenzurisana. Moguæe je koristiti jolly (*) u polju gdje se upisuje rijeè, npr. <b>dan*</b> æe cenzurisati rijeèi sa prefiksom <i><b>dan</b></i> kao <b>dan</b>as, <b>dan</b>ašnji, <b>dan</b>ašnja i slièno." ;
$lang['Word'] = "Rijeè";
$lang['Edit_word_censor'] = "Modifikuj listu";
$lang['Replacement'] = "Zamjena";
$lang['Add_new_word'] = "Dodaj novu rijeè";
$lang['Update_word'] = "Ažuriraj listu";

$lang['Must_enter_word'] = "Moraš ubaciti rijeè i njenu zamjenu";
$lang['No_word_selected'] = "Nijedna rijeè nije izabrana za modifiku";

$lang['Word_updated'] = "Izabrana rijeè je uspješno ažurirana";
$lang['Word_added'] = "Rijeè je uspješno upisana u listu";
$lang['Word_removed'] = "Izabrana rijeè je uspješno izbrisana";

$lang['Click_return_wordadmin'] = "Klikni %sovdje%s za povratak na Cenzura rijeèi";


//
// Masovni e-mail
//
$lang['Mass_email_explain'] = "Posredstvom ovog formulara moguæe je poslati e-mail svim èlanovima Foruma ili èlanovima pojedinih grupa korisnika. Biæe poslana jedna kopija na e-mail adresu administratora i <b>BCC</b> (<i>Blind Carbon Copy</i>) svim ostalim primaocima. Ukoliko šaljete e-mail jednoj velikoj grupi korisnika molimo Vas da budete strpljivi i saèekate potvrdu da je e-mail poslan. Ovaj proces je malo duži i to je normalno kod slanja masovnog e-maila.";
$lang['Compose'] = "Napiši e-mail"; 

$lang['Recipients'] = "Primaoci"; 
$lang['All_users'] = "Svim èlanovima";

$lang['Email_successfull'] = "E-mail je uspješno poslan ";
$lang['Click_return_massemail'] = "Klikni %sovdje%s za povratak na E-mail Generali";


//
// administracija rangova korisnika
//
$lang['Ranks_title'] = "Administracija rangova";
$lang['Ranks_explain'] = "Pomoæu ovog instrumenta moguæe je odrediti, modifikovati, obrisati, kontrolisati rangove uèesnika. Moguæe je i kreiranje personaliziranih rangova koji se kasnije mogu aplicirati posredstvom instrumenta <i>Administracija korisnika</i>.";

$lang['Add_new_rank'] = "Dodaj novi rang";

$lang['Rank_title'] = "Rang";
$lang['Rank_special'] = "Specijalan status";
$lang['Rank_minimum'] = "Najmanji broj komentara";
$lang['Rank_maximum'] = "Najveæi broj komentara";
$lang['Rank_image'] = "Slika koja predstavlja rang (pozicija u odnosu na Forum)";
$lang['Rank_image_explain'] = "Upisati poziciju i naziv file-a dodijeljenog rangu.";

$lang['Must_select_rank'] = "Potrebno je odabrati jedan rang";
$lang['No_assigned_rank'] = "Nije dodan nikakav specijalan status";

$lang['Rank_updated'] = "Rang je uspješno ažuriran";
$lang['Rank_added'] = "Rang je uspješno kreiran";
$lang['Rank_removed'] = "Rang je uspješno obrisan";

$lang['Click_return_rankadmin'] = "Klikni %sovdje%s za povratak na <i>Administracija rangova</i>";


//
// kontrola nick-ova
//
$lang['Disallow_control'] = "Rezervisanje nadimaka";
$lang['Disallow_explain'] = "Ovdje je moguæe odrediti <i>nadimke</i> (username) pod kojima se niko neæe moæi registrovati. Moguæe je koristiti jolly (*). Imajte na umu da nadimci koji su veæ registrovani ne mogu biti ubaèeni u ovu listu. Ukoliko to želite, potrebno je prvo izbrisati tog korisnika pa tek onda onda onemoguæiti korištenje dotiènog nadimka.";

$lang['Delete_disallow'] = "Obriši";
$lang['Delete_disallow_title'] = "Omoguæi korištenje nadimka";
$lang['Delete_disallow_explain'] = "Moguæe je omoguæiti korištenje nadimka birajuæi ga sa liste i potvrditi.";

$lang['Add_disallow'] = "Dodaj";
$lang['Add_disallow_title'] = "Rezerviši jedan nadimak";
$lang['Add_disallow_explain'] = "Moguæe je rezervisati jedan nadimak koristeæi jolly (*) umjesto karaktera.";

$lang['No_disallowed'] = "Nema rezervisanih nadimaka";

$lang['Disallowed_deleted'] = "Rezervisani nadimak se može ponovo koristiti";
$lang['Disallow_successful'] = "Uspješno je registrovan rezervisani nadimak";
$lang['Disallowed_already'] = "Ne možete rezervisati željeni nadimak jer: <li></li>veæ je na listi<li></li> nalazi se u listi cenzurisanih rijeèi <li></li> veæ je u upotrebi.";

$lang['Click_return_disallowadmin'] = "Klikni %sovdje%s za povratak na <i>Rezervisanje nadimaka</i>";


//
// Administracija stilova foruma
//
$lang['Styles_admin'] = "Administracija tema foruma";
$lang['Styles_explain'] = "Koristeæi ove opcije moguæe je dodati, eliminisati, modifikovati razlièite teme Foruma (grafièki prikaz).";
$lang['Styles_addnew_explain'] = "Na listi se nalaze sve teme Foruma koje imate na raspolaganju. Obratite pažnju da se teme samo nalaze u svom folderu unutar Foruma (<i>template</i>) ali da nisu instalirane u data-bazu. Ukoliko želite omoguæiti njihovo korištenje korisnicima Foruma neophodno je izvršiti instalaciju.";

$lang['Select_template'] = "Izaberi temu";

$lang['Style'] = "Stil";
$lang['Template'] = "Model";
$lang['Install'] = "Instaliraj";
$lang['Download'] = "Download";

$lang['Edit_theme'] = "Modifikuj Temu";
$lang['Edit_theme_explain'] = "Pomoæu donjeg formulara moguæe je modifikovati opcije izabrane teme. Prilikom upisivanja heksadecimalnih vrijednosti za boje ne treba ubacivati simbol <b>#</b>, npr. <b>CCCCCC</b> je OK, dok <b>#CCCCCC</b> nije.
";

$lang['Create_theme'] = "Kreiraj Temu";
$lang['Create_theme_explain'] = "Pomoæu ovog formulara moguæe je kreirati novu temu za izabrani model. Prilikom upisivanja heksadecimalnih vrijednosti za boje ne treba ubacivati simbol <b>#</b>, npr. <b>CCCCCC</b> je OK, dok <b>#CCCCCC</b> nije.";

$lang['Export_themes'] = "Eksportuj Temu";
$lang['Export_explain'] = "Posredstvom ovog kontrolnog panela moguæe je eksportovati podatke teme za izabrani model. Izaberi model iz donje liste i skripta foruma æe kreirati konfiguracijski file teme te ga pohraniti u direktorij sa ostalim temama (<i>template</i>). Ukoliko ne uspije sa pohranjivanjem program æe Vam ponuditi moguænost da ga download-ujete. Da bi pohranjivanje file-a bilo moguæe neophodno je omoguæiti dozvolu za pisanje (na serveru) direktoriju <i>template</i>. Više informacija moguæe je naæi na zvaniènom site-u phpBB grupe.";

$lang['Theme_installed'] = "Izabrana tema je uspješno instalirana";
$lang['Style_removed'] = "Izabrani stil je izbrisan iz data-baze. Za potpuno eliminsanje stila potrebno je obrisati i istoimeni direktorij koji se nalazi u direktoriju <i>template</i>.";
$lang['Theme_info_saved'] = "Podaci teme za izabrani model su uspješno pohranjenje. Sada je potrebno podesiti dozvole za file <i>theme_info.cfg</i> (na serveru) samo za èitanje";
$lang['Theme_updated'] = "Izabrana tema je ažurirana. Sada je otrebno eksportovati podatke nove teme.";
$lang['Theme_created'] = "Tema je kreirana. Sada je potrebno eksportovati temu u konfiguracijski file teme kako bi je neko drugi mogao koristiti.";

$lang['Confirm_delete_style'] = "Sigurno želite obrisati ovaj stil?";

$lang['Download_theme_cfg'] = "Proces eksporatcije ne uspijeva kreirati konfiguracijski file teme. Klikni na donje dugme za download file pomoæu tvog browser-a. Nakon download-a prebaci ga u direktorij koji sadrži fileo-ove modela. Naknadno je moguæe kompresovati file ukoliko želiš da ga distribuiraš ili ponovo koristiš.";
$lang['No_themes'] = "Model koji ste izabrali nema temu. Za kreiranje nove teme klikni na link <i>Kreiraj temu</i>";
$lang['No_template_dir'] = "Nije moguæe otvoriti direktorij modela. Možda server nije u stanju da ga proèita ili možda ne postoji.";
$lang['Cannot_remove_style'] = "Nije moguæe eliminisati stil pošto je u upotrebi kao zvanièan stil Foruma. Potrebo je promijeniti zvanièan stil Foruma (<i>Osnovna podešavanja</i>) i tek onda eliminisati stil.";
$lang['Style_exists'] = "Potrebno je dati neko drugo ime stilu pošto veæ postoji.";

$lang['Click_return_styleadmin'] = "Klikni %sovdje%s za povratak na <i>Administracija stilova foruma</i>";

$lang['Theme_settings'] = "Podešavanje Teme";
$lang['Theme_element'] = "Element Teme";
$lang['Simple_name'] = "Ime (pojednostavljno)";
$lang['Value'] = "Vrijednost";
$lang['Save_Settings'] = "Spasi";

$lang['Stylesheet'] = "CSS stil";
$lang['Background_image'] = "Slika pozadine";
$lang['Background_color'] = "Boja pozadine";
$lang['Theme_name'] = "Naziv Teme";
$lang['Link_color'] = "Link - boja";
$lang['Text_color'] = "Tekst - boja";
$lang['VLink_color'] = "Posjeæen link - boja";
$lang['ALink_color'] = "Aktivan link - boja";
$lang['HLink_color'] = "Hover link - boja";
$lang['Tr_color1'] = "Boja kolone tabele 1";
$lang['Tr_color2'] = "Boja kolone tabele 2";
$lang['Tr_color3'] = "Boja kolone tabele 3";
$lang['Tr_class1'] = "Kolona tabele - klasa (class) 1";
$lang['Tr_class2'] = "Kolona tabele - klasa (class) 2";
$lang['Tr_class3'] = "Kolona tabele - klasa (class) 3";
$lang['Th_color1'] = "Boja naslova tabele 1";
$lang['Th_color2'] = "Boja naslova tabele 2";
$lang['Th_color3'] = "Boja naslova tabele 3";
$lang['Th_class1'] = "Naslov tabele - klasa (class) 1";
$lang['Th_class2'] = "Naslov tabele - klasa (class) 2";
$lang['Th_class3'] = "Naslov tabele - klasa (class) 3";
$lang['Td_color1'] = "Boja æelije tabele 1";
$lang['Td_color2'] = "Boja æelije tabele 2";
$lang['Td_color3'] = "Boja æelije tabele 3";
$lang['Td_class1'] = "Æelija tabele - klasa (class) 1";
$lang['Td_class2'] = "Æelija tabele - klasa (class) 2";
$lang['Td_class3'] = "Æelija tabele - klasa (class) 3";
$lang['fontface1'] = "Naziv karaktera (font face) 1";
$lang['fontface2'] = "Naziv karaktera (font face) 2";
$lang['fontface3'] = "Naziv karaktera (font face) 3";
$lang['fontsize1'] = "Velièina karaktera 1";
$lang['fontsize2'] = "Velièina karaktera 2";
$lang['fontsize3'] = "Velièina karaktera 3";
$lang['fontcolor1'] = "Boja karaktera 1";
$lang['fontcolor2'] = "Boja karaktera 2";
$lang['fontcolor3'] = "Boja karaktera 3";
$lang['span_class1'] = "Klasa Span (span class) 1";
$lang['span_class2'] = "Klasa Span (span class) 2";
$lang['span_class3'] = "Klasa Span (span class) 3";
$lang['img_poll_size'] = "Velièina slike za glasanje [px]";
$lang['img_pm_size'] = "Velièina slike za status privatnih poruka [px]";


//
//Instalacijski proces
//
$lang['Welcome_install'] = "Instalacija phpBB Foruma - Dobro nam došli!";
$lang['Initial_config'] = "Osnovna konfiguracija";
$lang['DB_config'] = "Konfiguracija Data-baze";
$lang['Admin_config'] = "Konfiguracija administracije";
$lang['continue_upgrade'] = "Nakon što ste download-ovali i pohranili Vaš konfiguracijski file u kompjuter, možete kliknuti na dugme \"Nastavi Ažuriranje verzije\" ovdje dole za ažuriranje verzije Foruma. Molimo Vas da saèekate sa upload-om konfiguracijskog file-a dok ne bude završen proces ažuriranja verzije foruma.";
$lang['upgrade_submit'] = "Nastavi Ažuriranje verzije";

$lang['Installer_Error'] = "Pojavila se greška prilikom instalacije";
$lang['Previous_Install'] = "Pronaæena je jedna prethodna instalacija ";
$lang['Install_db_error'] = "Pojavila se greška prilikom ažuriranja Data-baze";

$lang['Re_install'] = "Jedan prethodni instalacijski proces je još uvijek aktivan.<br /><br />Ukoliko želite ponovo instalirati phpBB potrebno je to potvrditi pomoæu donjeg dugmeta. <b>Imajte na umu da æe ova operacija automatski obrisati sve postojeæe podatke te da nikakav automatski backup neæe biti obavljen!</b>. Username i password administratora koji je prethodno korišten æe biti ponovo rikreiran dok nijedna prethodna opcija raznih podešavanja neæe biti saèuvana. <br /><br />Razmislite dobro prije nego što potvrdite ovaj proces i za svaki sluèaj napravite sami jedan backup podataka! ";

$lang['Inst_Step_0'] = "Zahvaljujemo Vam na povjerenju koje ste nam ukazali izborom našeg software-a phpBB kao naèina komuniciranja posjetilaca Vaše stranice. Za uspješno izvoðenjee instalacijskog procesa neophodno je popuniti donji formular.Molimo Vas da obratite pažnju kako je za funkcionisanje Foruma neophodno na raspolaganju imati jednu Data-bazu. Ukoliko instalirate Forum najednu Data-bazu koja koristi ODBC (npr. MS Access), potrebno je prethodno kreirati joj jedan DSN prije instalacije.";

$lang['Start_Install'] = "Zapoèni instalaciju";
$lang['Finish_Install'] = "Završi instalaciju";

$lang['Default_lang'] = "Zvanièni jezik Foruma";
$lang['DB_Host'] = "Database Server Hostname / DSN";
$lang['DB_Name'] = "Ime databaze";
$lang['DB_Username'] = "Username databaze";
$lang['DB_Password'] = "Password databaze";
$lang['Database'] = "Data-baza";
$lang['Install_lang'] = "Izaberi jezik za instalaciju";
$lang['dbms'] = "Tip Databaze";
$lang['Table_Prefix'] = "Prefiks za tabele Foruma unutar databaze";
$lang['Admin_Username'] = "Username Administratora";
$lang['Admin_Password'] = "Password Administratora";
$lang['Admin_Password_confirm'] = "Password Administratora [ potvrdi ]";

$lang['Inst_Step_2'] = "Izabrani administracijski Username je kreiran. U ovom momentu je obavljena bazna instalacija. Sada æe se pojaviti  kontorolni panel pomoæu kojeg je moguæe administrirati novu instalaciju. Molimo Vas da provjerite detalje glavne konfiguracije i da izmjenite ono što bude neophodno. Još jednom, zahvaljujemo na izboru phpBB Foruma.";

$lang['Unwriteable_config'] = "Momentalno nije moguæe pisati na Vaš konfiguracijski file. Moguæe je download-ovati jednu kopiju konfiguracijskog file-a klikajuæi na donje dugme. Potrebno je zatim upload-ovati ovaj file u isti direktorij gdje je lociran phpBB. Nakon toga je potrebno otvoriti Administracijski panel koristeæi prethodno kreirani Username i Password Administratora (pojaviæe se jedan link pri dnu stranice - <i>Administracija Foruma</i>). Pomoæu administracijskog panela moguæe je vršiti razna podešavanja Foruma. Još jednom, zahvaljujemo na izboru phpBB Foruma.";
$lang['Download_config'] = "Download konfiguracijskog file-a";

$lang['ftp_choose'] = "Izaberi metod download-a";
$lang['ftp_option'] = "<br />Pošto su FTP ekstenzije su dostupne u ovoj verziji PHP, može Vam takodje prvo biti data moguænost direktnog download-a konfiguracjskog file putem ftp.</i></b>  ";
$lang['ftp_instructs'] = "Izabrali ste automatski transfer file-a posredstvom FTP na account koji sadrži phpBB 2. Molimo Vas da upišete neophodne informacije kako bi se proces obavio. FTP path mora biti taèan za pristup poziciji phpBB 2.<br /> (Napomena autora: kako italijanska verzija phpBB Foruma (koja je korištena kao glavni tekst za ovaj prevod) odudara od originalne engleske verzije, navodimo i originalan tekst: <i><b>You have chosen to ftp the file to the account containing phpBB 2 automatically.  Please enter the information below to facilitate this process. Note that the FTP path should be the exact path via ftp to your phpBB2 installation as if you were ftping to it using any normal client.</b></i> ";
$lang['ftp_info'] = "Unesi podatke za FTP";
$lang['Attempt_ftp'] = "Pokušaj transfera konfiguracijskog file-a putem FTP.";
$lang['Send_file'] = "Pošaljite mi konfiguracijski file i prebaciæu ga manualno putem FTP.";
$lang['ftp_path'] = "Path FTP za phpBB 2";
$lang['ftp_username'] = "Vaš Username FTP";
$lang['ftp_password'] = "Vaša Password FTP";
$lang['Transfer_config'] = "Poèetak transfera";

$lang['Install'] = "Instaliraj";
$lang['Upgrade'] = "Ažuriraj";
$lang['Install_Method'] = "Izaberi metod instalacije";
$lang['Install_No_Ext'] = "PHP konfiguracija Vašeg servera ne podržava tip data-baze koji ste izabrali.";
$lang['Install_No_PCRE'] = "phpBB2 zahtijeva <i>Perl-Compatible Regular Expressions Module</i> za php a koji Vaša PHP konfiguracija servera, èini se, ne podržava!"; 

$lang['Status_locked'] = "Zatvoreno";
$lang['Status_unlocked'] = "Otvoreno";

$lang['No_group_action'] = "Nije izabrana nikava akcija";
$lang['No_update_ranks'] = "Rang korisnika je uspjesno obrisan. Imajte na umu da korisnici sa ovim rangom nisu azurirani te je potrebno manualno podesiti njihove korisnicke account";
$lang['NoFTP_config'] = "Pokusaj FTP transfera konfiguracijskog  file-a nije uspio. Molimo Vas da download-ujete konfiguracijski file ta da ga manualno (FTP) dignete ponovo na server." ;


//
// To bi bilo sve.
// Za sada.
// -------------------------------------------------

?>