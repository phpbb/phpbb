<?php

/***************************************************************************
 *                            lang_admin.php [romana fara diacritice]
 *                              -------------------
 *     begin                : Sat Sep 7 2002
 *     copyright 1          : (C) Daniel Tanasie
 *     copyright 2          : (C) Bogdan Toma
 *     email     1          : danielt@mgbd.ro
 *     email     2          : bog_tom@yahoo.com
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

$lang['ENCODING'] = 'iso-8859-2';
$lang['DIRECTION'] = 'ltr';
$lang['LEFT'] = 'stanga';
$lang['RIGHT'] = 'dreapta';
$lang['DATE_FORMAT'] =  'd M Y'; // This should be changed to the default date format for your language, php date() format

$lang['General'] = 'Administrare generala';
$lang['Users'] = 'Administrare utilizatori';
$lang['Groups'] = 'Administrare grupuri';
$lang['Forums'] = 'Administrare forumuri';
$lang['Styles'] = 'Administrare stiluri';

$lang['Configuration'] = 'Configurare generala';
$lang['Permissions'] = 'Permisiuni';
$lang['Manage'] = 'Management';
$lang['Disallow'] = 'Dezactivare nume';
$lang['Prune'] = 'Curatire';
$lang['Mass_Email'] = 'Expediere mesaje in bloc';
$lang['Ranks'] = 'Ranguri';
$lang['Smilies'] = 'Zambete';
$lang['Ban_Management'] = 'Control restrictii';
$lang['Word_Censor'] = 'Cuvinte cenzurate';
$lang['Export'] = 'Exporta';
$lang['Create_new'] = 'Creeaza';
$lang['Add_new'] = 'Adauga';
$lang['Backup_DB'] = 'Salveaza baza de date';
$lang['Restore_DB'] = 'Restaureaza baza de date';



//
// Index
//
$lang['Admin'] = 'Administrare';
$lang['Not_admin'] = 'Nu sunteti autorizat sa administrati acest forum';
$lang['Welcome_phpBB'] = 'Bine ati venit la centrul de control al forumului phpBB';
$lang['Admin_intro'] = 'Va multumim pentru ca ati ales phpBB ca solutie pentru forumul dumneavoastra. Acest ecran va ofera o privire de ansamblu a diverselor statistici ale forumului dumneavoastra. Puteti reveni la aceasta pagina folosind legatura <i>Pagina de start a administratorului</i> din partea stanga. Pentru a reveni la pagina de start a forumului dumneavoastra, apasati pe logo-ul phpBB-ului aflat, de asemenea, in partea stanga. Celelalte legaturi din partea stanga va permit sa controlati orice aspect al forumului, fiecare ecran va avea instructiuni care dau explicatii despre cum se folosesc instrumentele.';
$lang['Main_index'] = 'Pagina de start a forumului';
$lang['Forum_stats'] = 'Statisticile forumului';
$lang['Admin_Index'] = 'Pagina de start a administratorului';
$lang['Preview_forum'] = 'Previzualizare forum';

$lang['Click_return_admin_index'] = 'Apasati %saici%s pentru a reveni la sectiunea Pagina de start a administratorului';

$lang['Statistic'] = 'Statistica';
$lang['Value'] = 'Valoarea';
$lang['Number_posts'] = 'Numarul mesajelor scrise';
$lang['Posts_per_day'] = 'Mesaje scrise pe zi';
$lang['Number_topics'] = 'Numarul subiectelor';
$lang['Topics_per_day'] = 'Subiecte pe zi';
$lang['Number_users'] = 'Numarul utilizatorilor';
$lang['Users_per_day'] = 'Utilizatori pe zi';
$lang['Board_started'] = 'Data pornirii forumului';
$lang['Avatar_dir_size'] = 'Dimensiunea directorului cu imagini asociate (Avatar)';
$lang['Database_size'] = 'Dimensiunea bazei de date';
$lang['Gzip_compression'] ='Compresia Gzip';
$lang['Not_available'] = 'Nu este disponibil(a)';

$lang['ON'] = 'Activa'; // This is for GZip compression
$lang['OFF'] = 'Inactiva';


//
// DB Utils
//
$lang['Database_Utilities'] = 'Instrumentele bazei de date';

$lang['Restore'] = 'Restaurare';
$lang['Backup'] = 'Salvare(Backup)';
$lang['Restore_explain'] = 'Aceasta va efectua o resturare completa a tuturor tabelelor phpBB dintr-in fisier salvat. Daca serverul dumneavoastra suporta, puteti publica un fisier text compresat cu gzip si aceasta va fi decomprimat automat. <b>ATENTIE:</b> Aceasta procedura va rescrie orice informatie deja existenta. Procesul de restaurare poate dura un timp indelungat; va rugam nu parasiti aceasta pagina pana cand restaurarea nu se termina.';
$lang['Backup_explain'] = 'Aici puteti face copii de rezerva ale tuturor datelor ce tin de phpBB. Daca aveti si tabele aditionale in aceeasi baza de date cu phpBB-ul pe care doriti sa le pastrati, va rugam sa introduceti numele lor separate prin virgula in casuta <i>Tabele Suplimentare</i> de mai jos. Daca serverul dumneavoastra suporta, puteti comprima fisierul cu gzip pentru a reduce dimensiunea sa inainte de a efectua operatiunea de descarcare.';

$lang['Backup_options'] = 'Optiunile de backup';
$lang['Start_backup'] = 'Porneste operatiunea de backup';
$lang['Full_backup'] = 'Backup total';
$lang['Structure_backup'] = 'Salveaza (copie de siguranta) doar structura';
$lang['Data_backup'] = 'Salveaza (copie de siguranta) doar datele';
$lang['Additional_tables'] = 'Tabele suplimentare';
$lang['Gzip_compress'] = 'Fisier comprimat cu Gzip';
$lang['Select_file'] = 'Selectati un fisier';
$lang['Start_Restore'] = 'Porneste operatiunea de restaurare';

$lang['Restore_success'] = 'Baza de date a fost restaurata cu succes.<br /><br />Forumul dumneavoastra ar trebui sa revina la starea lui inainte ca salvarea sa se fi realizat.';
$lang['Backup_download'] = 'Operatiunea de descarcare va incepe in curand; va rugam sa asteptati pana aceasta va incepe';
$lang['Backups_not_supported'] = 'Scuze, dar efectuarea backup-ului nu este in prezent realizabila pentru sistemul dumneavoastra de baze de date';

$lang['Restore_Error_uploading'] = 'Eroare la publicarea fisierului de backup';
$lang['Restore_Error_filename'] = 'Problema cu numele fisierului; va rugam, incercati cu un alt fisier';
$lang['Restore_Error_decompress'] = 'Nu pot decomprima un fisier gzip; va rugam, publicati o versiune text intreg (plain text)';
$lang['Restore_Error_no_file'] = 'Nici un fisier nu a fost publicat/incarcat';


//
// Auth pages
//
$lang['Select_a_User'] = 'Selectati un utilizator';
$lang['Select_a_Group'] = 'Selectati un grup';
$lang['Select_a_Forum'] = 'Selectati un forum';
$lang['Auth_Control_User'] = 'Controlul permisiunilor utilizatorului';
$lang['Auth_Control_Group'] = 'Controlul permisiunilor grupului';
$lang['Auth_Control_Forum'] = 'Controlul permisiunilor forumului';
$lang['Look_up_User'] = 'Selecteaza utilizatorul';
$lang['Look_up_Group'] = 'Selecteaza grupul';
$lang['Look_up_Forum'] = 'Selecteaza forumul';

$lang['Group_auth_explain'] = 'Aici puteti modifica permisiunile si starea moderatorului asociat la fiecare grup de utilizatori. Nu uitati cand schimbati permisiunile grupului ca permisiunile individuale ale utilizatorului pot sa permita accesul utilizatorului la forumuri, etc. Veti fi atentionat daca va aparea aceasta situatie.';
$lang['User_auth_explain'] = 'Aici puteti modifica permisiunile si starea moderatorului asociat la fiecare utilizator individual. Nu uitati cand schimbati permisiunile utilizatorului ca permisiunile individuale ale grupului pot sa permita accesul utilizatorului la forumuri, etc. Veti fi atentionat daca va aparea aceasta situatie.';
$lang['Forum_auth_explain'] = 'Aici puteti modifica nivelele de autorizare ale fiecarui forum. Pentru a realiza acest lucru aveti la dispozitie atat o metoda simpla cat si una avansata, metoda avansata oferind un control mai mare al fiecariei operatii din forum. Amintiti-va ca schimbarea nivelului de permisiuni ale forumurilor va afecta modul de realizare(finalizare) al diverselor operatiuni solicitate de catre utilizatori.';

$lang['Simple_mode'] = 'Modul simplu';
$lang['Advanced_mode'] = 'Modul avansat';
$lang['Moderator_status'] = 'Starea moderatorului';

$lang['Allowed_Access'] = 'Acces permis';
$lang['Disallowed_Access'] = 'Acces interzis';
$lang['Is_Moderator'] = 'este moderator';
$lang['Not_Moderator'] = 'nu este moderator';

$lang['Conflict_warning'] = 'Avertizare - Conflict de autorizare';
$lang['Conflict_access_userauth'] = 'Acest utilizator are inca drepturi de acces la acest forum datorate apartenentei acestuia la grup. Puteti sa modificati permisiunile grupului sau sa inlaturati acest utilizator din grup pentru a nu mai avea depturi de acces. Grupurile care dau drepturi (si forumurile implicate) sunt afisate mai jos.';
$lang['Conflict_mod_userauth'] = 'Acest utilizator are inca drepturi de moderator la acest forum datorate apartenentei acestuia la grup. Puteti sa modificati permisiunile grupului sau sa inlaturati acest utilizator din grup pentru a nu mai avea depturi de moderator. Grupurile care dau drepturi (si forumurile implicate) sunt afisate mai jos.';
$lang['Conflict_access_groupauth'] = 'Utilizatorul(i) urmator(i) are(au) inca drepturi de acces la acest forum datorate setarilor lui(lor) de permisiuni. Puteti sa modificati permisiunile utilizatorului pentru a nu mai avea drepturi de acces. Utilizatorii care dau drepturi (si forumurile implicate) sunt afisati mai jos.';
$lang['Conflict_mod_groupauth'] = 'Utilizatorul(i) urmator(i) are(au) inca drepturi de acces la acest forum datorate setarilor lui(lor) de permisiuni. Puteti sa modificati permisiunile utilizatorului pentru a nu mai avea drepturi de moderator. Utilizatorii care dau drepturi (si forumurile implicate) sunt afisati mai jos.';

$lang['Public'] = 'Public';
$lang['Private'] = 'Privat';
$lang['Registered'] = 'Inregistrat';
$lang['Administrators'] = 'Administratori';
$lang['Hidden'] = 'Ascuns';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = 'TOTI';
$lang['Forum_REG'] = 'INREG';
$lang['Forum_PRIVATE'] = 'PRIVAT';
$lang['Forum_MOD'] = 'MOD';
$lang['Forum_ADMIN'] = 'ADMIN';

$lang['View'] = 'Vizualizare';
$lang['Read'] = 'Citire';
$lang['Post'] = 'Scriere';
$lang['Reply'] = 'Raspunde';
$lang['Edit'] = 'Modifica';
$lang['Delete'] = 'Sterge';
$lang['Sticky'] = 'Lipicios (Sticky)';
$lang['Announce'] = 'Anunt';
$lang['Vote'] = 'Vot';
$lang['Pollcreate'] = 'Creare sondaj';

$lang['Permissions'] = 'Permisiuni';
$lang['Simple_Permission'] = 'Premisiune simpla';

$lang['User_Level'] = 'Nivelul utilizatorului';
$lang['Auth_User'] = 'Utilizator';
$lang['Auth_Admin'] = 'Administrator';
$lang['Group_memberships'] = 'Membru al grupurilor';
$lang['Usergroup_members'] = 'Acest grup contine urmatorii membrii';

$lang['Forum_auth_updated'] = 'Permisiunile forumului au fost actualizate';
$lang['User_auth_updated'] = 'Permisiunile utilizatorului au fost actualizate';
$lang['Group_auth_updated'] = 'Permisiunile grupului au fost actualizate';

$lang['Auth_updated'] = 'Permisiunile au fost actualizate';
$lang['Click_return_userauth'] = 'Apasati %saici%s pentru a reveni la sectiunea Controlul permisiunilor utilizatorului';
$lang['Click_return_groupauth'] = 'Apasati %saici%s pentru a reveni la sectiunea Controlul permisiunilor grupului';
$lang['Click_return_forumauth'] = 'Apasati %saici%s pentru a reveni la sectiunea Controlul permisiunilor forumului';


//
// Banning
//
$lang['Ban_control'] = 'Controlul interdictiilor';
$lang['Ban_explain'] = 'Aici puteti sa controlati interdictiile utilizatorilor. Puteti obtine acest lucru interzicand una sau mai multe din elementele caracteristice unui utilizator: denumire utilizator, multimea adreselor IP sau numele host-urilor. Aceste metode impiedica un utilizator sa nu ajunga in pagina de inceput a forumului. Pentru a impiedica un utilizator sa se inregistreze sub un alt nume de utilizator puteti specifica o adresa de mail interzisa. Retineti ca o singura adresa de mail interzisa nu-l va impiedeca pe utilizatorul in cauza sa intre sau sa scrie in forumul dumneavoastra; ar trebui sa folositi prima din cele doua metode.';
$lang['Ban_explain_warn'] = 'Retineti ca introducerea unei multimi de adrese IP inseamna ca toate adresele dintre inceputul si sfarsitul multimii au fost adaugate la lista interzisa. Pentru a reduce numarul de adrese adaugate la baza de date se pot folosi <i>wildcard</i>-urile unde este cazul. Daca chiar trebuie sa introduceti o plaja de valori, incercati sa o pastrati cat mai mica sau mai bine retineti doar adresele specifice.';

$lang['Select_username'] = 'Selectati un nume de utilizator';
$lang['Select_ip'] = 'Selectati un IP';
$lang['Select_email'] = 'Selectati o adresa de email';

$lang['Ban_username'] = 'Interziceti unul sau mai multi utilizatori';
$lang['Ban_username_explain'] = 'Puteti interzice mai multi utilizatori intr-un singur pas folosind combinatii potrivite ale mouse-ului (in browser) si tastaturii calculatorului dumneavoastra';

$lang['Ban_IP'] = 'Interziceti una sau mai multe adrese IP sau nume de host-uri';
$lang['IP_hostname'] = 'Adrese IP sau nume de host-uri';
$lang['Ban_IP_explain'] = 'Pentru a specifica mai multe IP-uri diferite sau nume de host-uri trebuie sa le separati prin virgula. Pentru a specifica o multime de adrese IP, separati inceputul si sfarsitul multimii cu o liniuta de unire (-); ca sa specificati caracterul <i>wildcard</i> folositi *';

$lang['Ban_email'] = 'Interziceti una sau mai multe adrese de email';
$lang['Ban_email_explain'] = 'Pentru a specifica mai multe adrese de email folositi separatorul virgula. Ca sa specificati un utilizator cu ajutorul <i>wildcard</i>-ului folositi *, de exemplu *@hotmail.com';

$lang['Unban_username'] = 'Deblocarea utilizatorilor';
$lang['Unban_username_explain'] = 'Puteti sa deblocati mai multi utilizatori intr-un singur pas folosind combinatii potrivite ale mouse-ului (in browser) si tastaturii calculatorului dumneavoastra';

$lang['Unban_IP'] = 'Deblocarea adreselor IP';
$lang['Unban_IP_explain'] = 'Puteti sa deblocati mai multe adrese IP intr-un singur pas folosind combinatii potrivite ale mouse-ului (in browser) si tastaturii calculatorului dumneavoastra';

$lang['Unban_email'] = 'Deblocarea adreselor email';
$lang['Unban_email_explain'] = 'Puteti sa deblocati mai multe adrese email intr-un singur pas folosind combinatii potrivite ale mouse-ului (in browser) si tastaturii calculatorului dumneavoastra';

$lang['No_banned_users'] = 'Nu este nici un utilizator interzis';
$lang['No_banned_ip'] = 'Nu este nici o adresa IP interzisa';
$lang['No_banned_email'] = 'Nu este nici o adresa de email interzisa';

$lang['Ban_update_sucessful'] = 'Lista restrictiilor a fost actualizata cu succes';
$lang['Click_return_banadmin'] = 'Apasati %saici%s pentru a reveni la sectiunea Control Restrictii';


//
// Configuration
//
$lang['General_Config'] = 'Configurare generala';
$lang['Config_explain'] = 'Formularul de mai jos va permite sa personalizati toate optiunile generale ale forumului. Pentru configurarea utilizatorilor si forumurilor folositi legaturile specifice aflate in partea stanga.';

$lang['Click_return_config'] = 'Apasati %saici%s pentru a reveni la sectiunea Configurare generala';

$lang['General_settings'] = 'Setarile generale ale forumului';
$lang['Server_name'] = 'Numele domeniului';
$lang['Server_name_explain'] = 'Numele domeniului acestui forum ruleaza din';
$lang['Script_path'] = 'Calea script-ului';
$lang['Script_path_explain'] = 'Calea unde phpBB2 este localizat relativ la numele domeniului';
$lang['Server_port'] = 'Port-ul serverului';
$lang['Server_port_explain'] = 'Port-ul pe care serverul dumneavoastra ruleaza este de obicei 80 (numai daca nu a fost schimbat)';
$lang['Site_name'] = 'Numele site-ului';
$lang['Site_desc'] = 'Descrierea site-ului';
$lang['Board_disable'] = 'Forum dezactivat';
$lang['Board_disable_explain'] = 'Aceasta actiune va face forumul indisponibil utilizatorilor. Nu inchideti sesiunea curenta cand dezactivati forumul, altfel nu veti mai fi capabil sa va autentificati din nou!';
$lang['Acct_activation'] = 'Validarea contului activata de';
$lang['Acc_None'] = 'Nimeni'; // These three entries are the type of activation
$lang['Acc_User'] = 'Utilizator';
$lang['Acc_Admin'] = 'Administrator';

$lang['Abilities_settings'] = 'Configurarile de baza ale utilizatorilor si forumurilor';
$lang['Max_poll_options'] = 'Numarul maxim al optiunilor chestionarului';
$lang['Flood_Interval'] = 'Interval de flood';
$lang['Flood_Interval_explain'] = 'Numarul de secunde pe care un utilzator trebuie sa-l astepte intre publicari';
$lang['Board_email_form'] = 'Trimite mesaj la utilizator via forum';
$lang['Board_email_form_explain'] = 'Utilizatorii pot trimit mesaje unii la alti prin acest forum';
$lang['Topics_per_page'] = 'Subiecte pe pagina';
$lang['Posts_per_page'] = 'Mesaje pe pagina';
$lang['Hot_threshold'] = 'Mesaje pentru statutul popular';
$lang['Default_style'] = 'Stilul standard';
$lang['Override_style'] = 'Suprascrie stilul utilizatorului';
$lang['Override_style_explain'] = 'Inlocuirea sitului utilizatorilor cu cel standard';
$lang['Default_language'] = 'Limba standard';
$lang['Date_format'] = 'Formatul datei';
$lang['System_timezone'] = 'Timpul zonal al sistemului';
$lang['Enable_gzip'] = 'Activare compresie GZip';
$lang['Enable_prune'] = 'Activare curatire forum';
$lang['Allow_HTML'] = 'Permite HTML';
$lang['Allow_BBCode'] = 'Permite cod BB';
$lang['Allowed_tags'] = 'Permite balize (tag-uri) HTML';
$lang['Allowed_tags_explain'] = 'Separa balizele (tag-urile) cu virgule';
$lang['Allow_smilies'] = 'Permite zambete';
$lang['Smilies_path'] = 'Calea unde se pastreaza zambetele';
$lang['Smilies_path_explain'] = 'Calea aflata in directorul dumneavoastra phpBB , de exemplu. imagini/zambete';
$lang['Allow_sig'] = 'Permite semnaturi';
$lang['Max_sig_length'] = 'Lungimea maxima a semnaturii';
$lang['Max_sig_length_explain'] = 'Numarul maxim de caractere aflate in semnatura utilizatorului';
$lang['Allow_name_change'] = 'Permite schimbarea numelui de utilizator';

$lang['Avatar_settings'] = 'Configurari pentru imagini asociate (Avatar)';
$lang['Allow_local'] = 'Permite galerie de imagini asociate';
$lang['Allow_remote'] = 'Permite imagini asociate la distanta';
$lang['Allow_remote_explain'] = 'Imaginile asociate sunt specificate cu o legatura la alt site web';
$lang['Allow_upload'] = 'Permite incarcarea imaginii asociate';
$lang['Max_filesize'] = 'Dimensiunea maxima a fisierului ce contine imaginea asociata';
$lang['Max_filesize_explain'] = 'Pentru fisierele ce contin imaginile asociate incarcate';
$lang['Max_avatar_size'] = 'Dimensiunea maxima a imaginii asociate';
$lang['Max_avatar_size_explain'] = '(Inaltime x Latime in pixeli)';
$lang['Avatar_storage_path'] = 'Calea de pastrare a imaginilor asociate';
$lang['Avatar_storage_path_explain'] = 'Calea aflata in directorul dumneavoastra phpBB, de exemplu. imagini/avatar';
$lang['Avatar_gallery_path'] = 'Calea de pastrare a galeriilor cu imagini asociate';
$lang['Avatar_gallery_path_explain'] = 'Calea aflata in directorul dumneavoastra phpBB, de exemplu. imagini/avatar/galerie';

$lang['COPPA_settings'] = 'Configurarile COPPA';
$lang['COPPA_fax'] = 'Numarul de fax Fax Number';
$lang['COPPA_mail'] = 'Adresa postala COPPA';
$lang['COPPA_mail_explain'] = 'Aceasta este adresa postala unde parintii vor trimite formularele de inregistrare COPPA';

$lang['Email_settings'] = 'Configurarile de email';
$lang['Admin_email'] = 'Adresa de email a administratorului';
$lang['Email_sig'] = 'Semnatura din email';
$lang['Email_sig_explain'] = 'Acest text va fi atasat la toate mesajele pe care forumul le trimite';
$lang['Use_SMTP'] = 'Folositi serverul SMTP Server pentru email';
$lang['Use_SMTP_explain'] = 'Specificati da daca doriti sau aveti nevoie sa trimiteti mesaje printr-un alt server in loc sa folositi functia locala de mesagerie';
$lang['SMTP_server'] = 'Adresa serverului SMTP';
$lang['SMTP_username'] = 'Numele de utilizator SMTP';
$lang['SMTP_username_explain'] = 'Introduceti numele de utilizator doar daca serverul dumneavoastra SMTP necesita aceasta specificare';
$lang['SMTP_password'] = 'Parola SMTP';
$lang['SMTP_password_explain'] = 'Introduceti parola doar daca serverul dumneavoastra SMTP necesita aceasta specificare';

$lang['Disable_privmsg'] = 'Mesagerie privata';
$lang['Inbox_limits'] = 'Numarul maxim al mesajelor in Dosarul cu mesaje (Inbox)';
$lang['Sentbox_limits'] = 'Numarul maxim al mesajelor in Dosarul cu mesaje trimise (Sentbox)';
$lang['Savebox_limits'] = 'Numarul maxim al mesajelor in Dosarul cu mesaje salvate (Savebox)';

$lang['Cookie_settings'] = 'Configurarile pentru cookie';
$lang['Cookie_settings_explain'] = 'Aceste detalii definesc cum sunt cookie-urile trimise catre browser-ele utilizatorilor. In cele mai multe cazuri valorile standard pentru setarile cookie-urilor ar trebui sa fie suficiente dar daca trebuie sa le schimbati aveti mare grija, setarile incorecte pot impiedica utilizatorii sa se autentifice';
$lang['Cookie_domain'] = 'Domeniul pentru cookie';
$lang['Cookie_name'] = 'Numele pentru cookie';
$lang['Cookie_path'] = 'Calea pentru cookie';
$lang['Cookie_secure'] = 'Securizare cookie';
$lang['Cookie_secure_explain'] = 'Daca serverul dumneavoastra ruleaza via SSL, selectati <i>Activat</i> altfel selectati <i>Dezactivat</i>';
$lang['Session_length'] = 'Durata sesiunii [ secunde ]';


//
// Forum Management
//
$lang['Forum_admin'] = 'Administrare forumuri';
$lang['Forum_admin_explain'] = 'In aceasta sectiune puteti adauga, sterge, modifica, reordona si resincroniza categoriile si forumurile.';
$lang['Edit_forum'] = 'Modificare forum';
$lang['Create_forum'] = 'Creaza un forum nou';
$lang['Create_category'] = 'Creaza o categorie noua';
$lang['Remove'] = 'Sterge';
$lang['Action'] = 'Actiune';
$lang['Update_order'] = 'Actualizeaza ordinea';
$lang['Config_updated'] = 'Configurarile la forum au fost actualizate cu succes';
$lang['Edit'] = 'Modifica';
$lang['Delete'] = 'Sterge';
$lang['Move_up'] = 'Muta mai sus';
$lang['Move_down'] = 'Muta mai jos';
$lang['Resync'] = 'Resincronizare';
$lang['No_mode'] = 'Nici un mod nu a fost specificat';
$lang['Forum_edit_delete_explain'] = 'Formularul de mai jos va permite sa personalizati toate optiunile generale ale forumului. Pentru configurarea utilizatorilor si forumurilor folositi legaturile specifice aflate in partea stanga.';

$lang['Move_contents'] = 'Muta tot continutul';
$lang['Forum_delete'] = 'Stergere forum';
$lang['Forum_delete_explain'] = 'Formularul de mai jos va permite sa stergeti un forum (sau o categorie) si sa decideti unde doriti sa plasati toate subiectele (sau forumurile) pe care le contine.';

$lang['Forum_settings'] = 'Configurarile generale ale forumului';
$lang['Forum_name'] = 'Numele forumului';
$lang['Forum_desc'] = 'Descriere';
$lang['Forum_status'] = 'Starea forumului';
$lang['Forum_pruning'] = 'Autocuratare';

$lang['prune_freq'] = 'Verifica varsta subiectelor la fiecare';
$lang['prune_days'] = 'Sterge subiectele la care nu s-au scris raspunsuri de';
$lang['Set_prune_data'] = 'Ati selectat optiunea autocuratire pentru acest forum dar nu ati specificat o frecventa sau un numar de zile al intervalului pentru acest proces. Va rugam reveniti si specificati aceste valori';

$lang['Move_and_Delete'] = 'Muta si sterge';

$lang['Delete_all_posts'] = 'Sterge toate mesajele';
$lang['Nowhere_to_move'] = 'Nu muta mesajele';

$lang['Edit_Category'] = 'Modificare categorie';
$lang['Edit_Category_explain'] = 'Puteti folosi acest forumlar pentru a modifica numele categoriilor.';

$lang['Forums_updated'] = 'Informatiile despre forumuri si categorii au fost actualizate cu succes';

$lang['Must_delete_forums'] = 'Trebuie sa stergeti toate forumurile inainte ca sa stergeti aceasta categorie';

$lang['Click_return_forumadmin'] = 'Apasati %saici%s pentru a reveni la sectiunea Administrare forumuri';


//
// Smiley Management
//
$lang['smiley_title'] = 'Administrare zambete';
$lang['smile_desc'] = 'Din aceasta pagina puteti adauga, sterge si modifica zambetele sau emotiile asociate pe care utilizatorii dumneavoastra le pot folosi cand scriu mesaje sau cand trimit mesaje private.';

$lang['smiley_config'] = 'Configurare zambete';
$lang['smiley_code'] = 'Cod zambet';
$lang['smiley_url'] = 'Fisierul imagine al zambetului';
$lang['smiley_emot'] = 'Emotia asociata';
$lang['smile_add'] = 'Adaugati un zambet nou';
$lang['Smile'] = 'Zambet';
$lang['Emotion'] = 'Emotia asociata';

$lang['Select_pak'] = 'Selectati un fisier de tip Pack (.pak)';
$lang['replace_existing'] = 'Inlocuiti zambetele existente';
$lang['keep_existing'] = 'Pastrati zambetele existente';
$lang['smiley_import_inst'] = 'Ar trebui sa decomprimati pachetul cu iconite si sa incarcati toate fisierele in directorul cu zambete specificat la instalare. Apoi selectati informatiile corecte in acest formular ca sa importati pachetul cu zambete.';
$lang['smiley_import'] = 'Importati zambetele';
$lang['choose_smile_pak'] = 'Selectati un fisier pachet cu zambete de tip .pak';
$lang['import'] = 'Importati zambete';
$lang['smile_conflicts'] = 'Ce ar trebui sa fie facut in caz de conflicte';
$lang['del_existing_smileys'] = 'Stergeti zambetele existente inainte de import';
$lang['import_smile_pack'] = 'Importati pachetul cu zambete';
$lang['export_smile_pack'] = 'Creati pachetul cu zambete';
$lang['export_smiles'] = 'Ca sa creati un pachet cu zambete din zambetele instalate, apasati %saici%s ca sa descarcati fisierul cu zambete .pak. Numiti acest fisier cum doriti dar asigurati-va ca ati pastrat fisierului extensia .pak. Apoi creati un fiesier arhivat continand toate imaginile zambete ale dumneavoastra plus acest fisier .pak.';

$lang['smiley_add_success'] = 'Zambetul a fost adaugat cu succes';
$lang['smiley_edit_success'] = 'Zambetul a fost actualizat cu succes';
$lang['smiley_import_success'] = 'Pachetul cu zambete a fost importat cu succes!';
$lang['smiley_del_success'] = 'Zambetul a fost sters cu succes';
$lang['Click_return_smileadmin'] = 'Apasati %saici%s pentru a reveni la sectiunea Administrare zambete';


//
// User Management
//
$lang['User_admin'] = 'Administrare utilizatori';
$lang['User_admin_explain'] = 'Aici puteti schimba informatiile despre utilizatorii dumneavoastra si optiunile specifice. Ca sa modificati drepturile utilizatorilor, folositi drepturile din sistem ale utilizatorilor si grupurilor.';

$lang['Look_up_user'] = 'Selecteaza utilizatorul';

$lang['Admin_user_fail'] = 'Nu se poate actualiza profilul utilizatorului.';
$lang['Admin_user_updated'] = 'Profilul utilizatorului a fost actualizat cu succes.';
$lang['Click_return_useradmin'] = 'Apasati %saici%s pentru a reveni la sectiunea Administrare utilizatori';

$lang['User_delete'] = 'Stergeti acest utilizator';
$lang['User_delete_explain'] = 'Apasati aici pentru a sterge acest utilizator, aceasta operatie este ireversibila.';
$lang['User_deleted'] = 'Utilizatorul a fost sters cu succes.';

$lang['User_status'] = 'Utilizatorul este activ';
$lang['User_allowpm'] = 'Poate trimite mesaje private';
$lang['User_allowavatar'] = 'Poate folosi imagini asociate';

$lang['Admin_avatar_explain'] = 'Aici puteti vizualiza si sterge imaginea asociata a utilizatorului.';

$lang['User_special'] = 'Campuri speciale doar pentru administrator';
$lang['User_special_explain'] = 'Aceste campuri nu pot fi modificate de catre utilizatori. Aici puteti sa specificati stadiul lor si alte optiuni care nu sunt oferite utilizatorilor.';


//
// Group Management
//
$lang['Group_administration'] = 'Administrarea grupurilor';
$lang['Group_admin_explain'] = 'Din aceasta sectiune puteti administra toate grupurile cu utilizatori ale dumneavoastra, puteti sterge, crea si modifica grupurile existente. Puteti alege moderatorii, schimba in deschis/inchis statutul grupului si specifica numele si descrierea grupului';
$lang['Error_updating_groups'] = 'A fost o eroare in timpul actualizarii grupurilor';
$lang['Updated_group'] = 'Grupul a fost actualizat cu succes';
$lang['Added_new_group'] = 'Noul grup a fost creat cu succes';
$lang['Deleted_group'] = 'Grupul a fost sters cu succes';
$lang['New_group'] = 'Creaza un grup nou';
$lang['Edit_group'] = 'Modifica grupul';
$lang['group_name'] = 'Numele grupului';
$lang['group_description'] = 'Descrierea grupului';
$lang['group_moderator'] = 'Moderatorul grupului';
$lang['group_status'] = 'Statutul grupului';
$lang['group_open'] = 'Grup deschis';
$lang['group_closed'] = 'Grup inchis';
$lang['group_hidden'] = 'Grup ascuns';
$lang['group_delete'] = 'Sterg grupul';
$lang['group_delete_check'] = 'Vreau sa sterg acest grup';
$lang['submit_group_changes'] = 'Efectueaza modificarile';
$lang['reset_group_changes'] = 'Reseteaza modificarile';
$lang['No_group_name'] = 'Trebuie sa specificati un nume pentru acest grup';
$lang['No_group_moderator'] = 'Trebuie sa specificati un moderator pentru acest grup';
$lang['No_group_mode'] = 'Trebuie sa specificati un mod (deschis/inchis) pentru acest grup';
$lang['No_group_action'] = 'Nici o actiune nu a fost specificata';
$lang['delete_group_moderator'] = 'Doriti sa stergeti moderatorul vechi al grupului?';
$lang['delete_moderator_explain'] = 'Daca schimbati moderatorul grupului, bifati aceasta casuta ca sa stergeti vechiul moderator al grupului din grup. Altfel, nu o bifati si utilizatorul va deveni un membru normal al grupului.';
$lang['Click_return_groupsadmin'] = 'Apasati %saici%s pentru a reveni la sectiunea Administrarea grupurilor.';
$lang['Select_group'] = 'Selecteaza un grup';
$lang['Look_up_group'] = 'Selecteaza grupul';


//
// Prune Administration
//
$lang['Forum_Prune'] = 'Curatirea forumurilor';
$lang['Forum_Prune_explain'] = 'Aceasta actiune va sterge orice subiect care nu a fost completat intr-un numar de zile egal cu cel pe care l-ati specificat. Daca nu ati introdus un numar atunci toate subiectele vor fi sterse. Nu vor fi sterse subiecte in care sondajele inca ruleaza si nici anunturile. Aceste subiecte trebuie sa le stergeti manual.';
$lang['Do_Prune'] = 'Efectueaza curatirea';
$lang['All_Forums'] = 'Toate forumurile';
$lang['Prune_topics_not_posted'] = 'Curatirea subiectelor fara raspunsuri in multe zile';
$lang['Topics_pruned'] = 'Subiecte curatite';
$lang['Posts_pruned'] = 'Mesaje curatite';
$lang['Prune_success'] = 'Curatirea mesajelor s-a efectuat cu succes';


//
// Word censor
//
$lang['Words_title'] = 'Administrarea cuvintelor cenzurate';
$lang['Words_explain'] = 'Din aceasta sectiune puteti adauga, modifica si sterge cuvinte care vor fi automat cenzurate in forumurile dumneavoastra. In plus, persoanelor nu le va fi permis sa se inregistreze cu nume de utilizator ce contin aceste cuvinte. Wildcard-urile (*) sunt acceptate in campul pentru cuvinte, de exemplu *test* se va potrivi cu detestabil, test* se va potrivi cu testare, *test se va potrivi cu detest.';
$lang['Word'] = 'Cuvant';
$lang['Edit_word_censor'] = 'Modific cuvantul cenzurat';
$lang['Replacement'] = 'Inlocuire';
$lang['Add_new_word'] = 'Adauga un cuvant nou';
$lang['Update_word'] = 'Actualizeaza cuvantul cenzurat';

$lang['Must_enter_word'] = 'Trebuie sa introduceti un cuvant si inlocuirile acestuia';
$lang['No_word_selected'] = 'Nici un cuvant nu a fost selectat pentru modificare';

$lang['Word_updated'] = 'Cuvantul cenzurat selectat a fost actualizat cu succes';
$lang['Word_added'] = 'Cuvantul cenzurat a fost adaugat cu succes';
$lang['Word_removed'] = 'Cuvantul cenzurat selectat a fost sters cu succes';

$lang['Click_return_wordadmin'] = 'Apasati %saici%s pentru a reveni la sectiunea Administrarea cuvintelor cenzurate';


//
// Mass Email
//
$lang['Mass_email_explain'] = 'Aici puteti trimite un email la toti utilizatorii dumneavoastra sau la utilizatorii dintr-un grup specific. Pentru a realiza acest lucru, un email va fi trimis la adresa de email a administratorulu cu toti destinatarii specificati in campul BCC. Daca trimiteti email la un grup mare de oameni, va rugam sa fiti atent dupa trimitere si nu va opriti la jumatatea paginii. Este normal ca pentru o corespondenta masiva sa fie nevoie de un timp mai lung astfel ca veti fi notificat cand actiunea s-a terminat';
$lang['Compose'] = 'Compune';

$lang['Recipients'] = 'Destinatari';
$lang['All_users'] = 'Toti utilizatorii';

$lang['Email_successfull'] = 'Mesajul dumneavoastra a fost trimis';
$lang['Click_return_massemail'] = 'Apasati %saici%s pentru a reveni la sectiunea Corespondenta masiva';


//
// Ranks admin
//
$lang['Ranks_title'] = 'Administrarea rangurilor';
$lang['Ranks_explain'] = 'Folosind acest formular puteti adauga, modifica, vizualiza si sterge ranguri. De asemenea, puteti crea ranguri personalizate care pot fi aplicate unui utilizator via facilitatii date de managementul utilizatorilor';

$lang['Add_new_rank'] = 'Adauga un rang nou';

$lang['Rank_title'] = 'Titlul rangului';
$lang['Rank_special'] = 'Seteaza ca rang special';
$lang['Rank_minimum'] = 'Numar minim de mesaje';
$lang['Rank_maximum'] = 'Numar maxim de mesaje';
$lang['Rank_image'] = 'Imaginea rangului (relativ la calea phpBB2-ului)';
$lang['Rank_image_explain'] = 'Aceasta este folosita pentru a defini o imagine mica asociata cu rangul';

$lang['Must_select_rank'] = 'Trebuie sa selectati un rang';
$lang['No_assigned_rank'] = 'Nici un rang special nu a fost repartizat';

$lang['Rank_updated'] = 'Rangul a fost actualizat cu succes';
$lang['Rank_added'] = 'Rangul a fost adaugat cu succes';
$lang['Rank_removed'] = 'Rangul a fost sters cu succes';
$lang['No_update_ranks'] = 'Rangul a fost sters cu succes, conturile utilizatorilor care folosesc acest rang nu au fost actualizate. Trebuie sa resetati manual rangul pentru aceste conturi';

$lang['Click_return_rankadmin'] = 'Apasati %saici%s pentru a reveni la sectiunea Administrarea rangurilor';


//
// Disallow Username Admin
//
$lang['Disallow_control'] = 'Administrarea numelor de utilizator nepremise';
$lang['Disallow_explain'] = 'Aici puteti controla numele de utilizator care nu sunt permise sa fie folosite. Numele de utilizator care nu sunt permise pot contine caracterul *. Retineti ca nu aveti posibilitatea sa specificati orice nume de utilizator care a fost deja inregistrat; trebuie mai intai sa stergeti acel nume si apoi sa-l interziceti';

$lang['Delete_disallow'] = 'Sterge';
$lang['Delete_disallow_title'] = 'Sterge un nume de utilizator nepermis';
$lang['Delete_disallow_explain'] = 'Puteti sterge un nume de utilizator nepermis selectand numele de utilizator din aceasta lista si apasand butonul <i>Sterge</i>';

$lang['Add_disallow'] = 'Adauga';
$lang['Add_disallow_title'] = 'Adauga un nume de utilizator nepermis';
$lang['Add_disallow_explain'] = 'Puteti interzice un nume de utilizator folosind caracterul wildcard * care se potriveste la orice caracter';

$lang['No_disallowed'] = 'Nici un nume de utilizator nu a fost interzis';

$lang['Disallowed_deleted'] = 'Numele de utilizator nepermis a fost sters cu succes';
$lang['Disallow_successful'] = 'Numele de utilizator nepermis a fost adaugat cu succes';
$lang['Disallowed_already'] = 'Numele pe care l-ati introdus nu poate fi interzis. Ori exista deja in lista, exista in lista cuvintelor cenzurate sau exista un nume de utilizator similar';

$lang['Click_return_disallowadmin'] = 'Apasati %saici%s pentru a reveni la sectiunea Administrarea numelor de utilizator nepremise';


//
// Styles Admin
//
$lang['Styles_admin'] = 'Administrarea stilurilor';
$lang['Styles_explain'] = 'Folosind aceasta facilitate puteti adauga, sterge si administra stilurile (sabloanele si temele) disponibile utilizatorilor dumneavoastra';
$lang['Styles_addnew_explain'] = 'Lista urmatoare contine toate temele care sunt disponibile pentru sabloanele pe care le aveti. Elementele din aceasta lista nu au fost instalate in baza de date a phpBB-ului. Ca sa instalati o tema apasati pe legatura <i>Instaleaza</i> de langa denumirea temei';

$lang['Select_template'] = 'Selectati un sablon';

$lang['Style'] = 'Stilul';
$lang['Template'] = 'Sablonul';
$lang['Install'] = 'Instaleaza';
$lang['Download'] = 'Descarca';

$lang['Edit_theme'] = 'Modifica tema';
$lang['Edit_theme_explain'] = 'In formularul de mai jos puteti modifica configurarile pentru tema selectata';

$lang['Create_theme'] = 'Creaza tema';
$lang['Create_theme_explain'] = 'Folositi formularul de mai jos ca sa creati o tema noua pentru un sablon selectat. Cand introduceti culori (pentru care trebuie sa folositi notatie hexazecimala) nu trebuie sa includeti initiala #, de exemplu CCCCCC este valida, #CCCCCC nu este valida';

$lang['Export_themes'] = 'Exporta teme';
$lang['Export_explain'] = 'In aceasta sectiune puteti exporta teme dintr-un sablon selectat. Selectati sablonul din lista de mai jos si programul va crea un fisier de configurare a temei si incercati sa-l salvati in directorul sablonului selectat. Daca fisierul nu poate fi salvat vi se va da posibilitatea sa-l descarcati. Pentru ca programul sa salveze fisierul trebuie sa dati drepturi de scriere pentru serverul web pe directorul sablonului selectat. Pentru mai multe informatii consultati pagina 2 din ghidul utilizatorilor phpBB.';

$lang['Theme_installed'] = 'Tema selectata a fost instalata cu succes';
$lang['Style_removed'] = 'Stilul selectat a fost sters din baza de date. Pentru a sterge definitiv acest stil din sistem, trebuie sa-l stergeti din directorul dumneavoastra cu sabloane.';
$lang['Theme_info_saved'] = 'Informatiile temei pentru sablonul curent au fost salvate. Acum trebuie sa specificati permisiunile in fisierul theme_info.cfg (si daca se poate directorul sablonului selectat) la acces doar de citire';
$lang['Theme_updated'] = 'Tema selectata a fost actualizata. Acum ar trebui sa exportati setarile temei noi';
$lang['Theme_created'] = 'Tema a fost creata. Acum ar trebui sa exportati tema in fisierul de configurare al temei pentru pastrarea in siguranta a acesteia sau s-o folositi altundeva';

$lang['Confirm_delete_style'] = 'Sunteti sigur ca doriti sa stergeti acest stil?';

$lang['Download_theme_cfg'] = 'Procedura de export nu poate scrie fisierul cu informatiile temei. Apasati butonul de mai jos ca sa descarcati acest fisier. Odata ce l-ati descarcat puteti sa-l transferati in directorul care contine fisierele cu sabloane. Puteti impacheta fisierele pentru distributie sau sa le folositi unde doriti';
$lang['No_themes'] = 'Sablonul pe care l-ati selectat nu are teme atasate. Ca sa creati o tema noua apasati legatura <i>Creaza tema</i> din partea stanga';
$lang['No_template_dir'] = 'Nu se poate deschide directorul cu sabloane. Acesta ori nu poate fi citit de catre serverul web ori nu exista';
$lang['Cannot_remove_style'] = 'Nu puteti sterge stilul selectat in timp ce este acesta este stilul standard pentru forum. Schimbati stilul standard si incercati din nou.';
$lang['Style_exists'] = 'Numele stilului pe care l-ati selectat exista deja, va rugam reveniti si alegeti un nume diferit.';

$lang['Click_return_styleadmin'] = 'Apasati %saici%s ca sa reveniti la sectiunea Administrarea stilurilor';

$lang['Theme_settings'] = 'Configurarile temei';
$lang['Theme_element'] = 'Elementul temei';
$lang['Simple_name'] = 'Numele simplu';
$lang['Value'] = 'Valoarea';
$lang['Save_Settings'] = 'Salveaza configurarile';

$lang['Stylesheet'] = 'Stilul CSS';
$lang['Background_image'] = 'Imaginea fundalului';
$lang['Background_color'] = 'Culoarea fundalului';
$lang['Theme_name'] = 'Numele temei';
$lang['Link_color'] = 'Culoarea legaturii';
$lang['Text_color'] = 'Culoarea textului';
$lang['VLink_color'] = 'Culoarea legaturii vizitate';
$lang['ALink_color'] = 'Culoarea legaturii active';
$lang['HLink_color'] = 'Culoarea legaturii acoperite';
$lang['Tr_color1'] = 'Culoarea 1 a randului din tabel';
$lang['Tr_color2'] = 'Culoarea 2 a randului din tabel';
$lang['Tr_color3'] = 'Culoarea 3 a randului din tabel';
$lang['Tr_class1'] = 'Clasa 1 a randului din tabel';
$lang['Tr_class2'] = 'Clasa 2 a randului din tabel';
$lang['Tr_class3'] = 'Clasa 3 a randului din tabel';
$lang['Th_color1'] = 'Culoarea 1 a antetului din tabel';
$lang['Th_color2'] = 'Culoarea 2 a antetului din tabel';
$lang['Th_color3'] = 'Culoarea 3 a antetului din tabel';
$lang['Th_class1'] = 'Clasa 1 a antetului din tabel';
$lang['Th_class2'] = 'Clasa 2 a antetului din tabel';
$lang['Th_class3'] = 'Clasa 3 a antetului din tabel';
$lang['Td_color1'] = 'Culoarea 1 a celulei din tabel';
$lang['Td_color2'] = 'Culoarea 2 a celulei din tabel';
$lang['Td_color3'] = 'Culoarea 3 a celulei din tabel';
$lang['Td_class1'] = 'Clasa 1 a celulei din tabel';
$lang['Td_class2'] = 'Clasa 2 a celulei din tabel';
$lang['Td_class3'] = 'Clasa 3 a celulei din tabel';
$lang['fontface1'] = 'Fontul de fata 1';
$lang['fontface2'] = 'Fontul de fata 2';
$lang['fontface3'] = 'Fontul de fata 3';
$lang['fontsize1'] = 'Dimensiunea 1 a fontului';
$lang['fontsize2'] = 'Dimensiunea 2 a fontului';
$lang['fontsize3'] = 'Dimensiunea 3 a fontului';
$lang['fontcolor1'] = 'Culoarea 1 a fontului';
$lang['fontcolor2'] = 'Culoarea 2 a fontului';
$lang['fontcolor3'] = 'Culoarea 3 a fontului';
$lang['span_class1'] = 'Clasa 1 a separatorului';
$lang['span_class2'] = 'Clasa 2 a separatorului';
$lang['span_class3'] = 'Clasa 3 a separatorului';
$lang['img_poll_size'] = 'Dimensiunea imaginii sondajului [px]';
$lang['img_pm_size'] = 'Dimensiunea statutului de mesaj privat [px]';


//
// Install Process
//
$lang['Welcome_install'] = 'Bine ati venit la procedura de instalare a formumului phpBB2';
$lang['Initial_config'] = 'Configuratia de baza';
$lang['DB_config'] = 'Configuratia bazei de date';
$lang['Admin_config'] = 'Configuratia administratorului';
$lang['continue_upgrade'] = 'Odata ce ati descarcat fisierul dumneavoastra de configurare pe calculatorul local puteti folosi butonul <i>Continua actualizarea</i> de mai jos ca sa treceti la urmatorul pas din actualizare. Va rugam asteptati sa se incarce fisierul de configurare pana ce actualizarea  este completa.';
$lang['upgrade_submit'] = 'Continua actualizarea';

$lang['Installer_Error'] = 'O eroare a aparut in timpul instalarii';
$lang['Previous_Install'] = 'O instalare anterioara a fost detectata';
$lang['Install_db_error'] = 'O eroare a aparut in timpul actualizarii bazei de date';

$lang['Re_install'] = 'Instalarea anterioara este inca activa. <br /><br />Daca doriti sa reinstalati phpBB2-ul ar trebui sa apasati pe butonul Da de mai jos. Va rugam sa aveti grija ca sa nu distrugeti toate datele existente, nici o copie de siguranta nu va fi facuta! Numele de utilizator si parola administratorului pe care le-ati folosit sa va autentificati in forum vor fi recreate dupa reinstalare, nici o alta setare nu va fi pastrata. <br /><br />Ganditi-va atent inainte de a apasa butonul <i>Poneste instalarea</i>!';

$lang['Inst_Step_0'] = 'Va multumim ca ati ales phpBB2. Pentru a completa aceasta instalare va rugam sa completati detaliile de mai jos. Retineti ca baza de date pe care o folositi trebuie sa existe deja. Daca instalati intr-o baza de date care foloseste ODBC, de exemplu MS Access ar trebui mai intai sa creati un DSN pentru aceasta inainte de a continua.';

$lang['Start_Install'] = 'Poneste instalarea';
$lang['Finish_Install'] = 'Termina instalarea';

$lang['Default_lang'] = 'Limba standard pentru forum';
$lang['DB_Host'] = 'Numele serverului gazda pentru baza de date / DSN';
$lang['DB_Name'] = 'Numele bazei dumneavoastra de date';
$lang['DB_Username'] = 'Numele de utilizator al bazei de date';
$lang['DB_Password'] = 'Parola de utilizator al bazei de date';
$lang['Database'] = 'Baza dumneavoastra de date';
$lang['Install_lang'] = 'Alegeti limba pentru instalare';
$lang['dbms'] = 'Tipul bazei de date';
$lang['Table_Prefix'] = 'Prefixul pentru tabelele din baza de date';
$lang['Admin_Username'] = 'Numele de utilizator al administratorului';
$lang['Admin_Password'] = 'Parola administratorului';
$lang['Admin_Password_confirm'] = 'Parola administratorului [ Confirmati ]';

$lang['Inst_Step_2'] = 'Numele de utilizator pentru administrator a fost creat. Acum instalarea de baza este completa. Va aparea un ecran care va va permite sa administrati noua dumneavoastra instalare. Asigurati-va ca ati verificat detaliile sectiunii Configurare generala si ati efectuat orice schimbare necesara. Va multumim ca ati ales phpBB2.';

$lang['Unwriteable_config'] = 'Fisierul dumneavoastra de configurare in acest moment este protejat la scriere. O copie a fisierului de configurare va fi descarcata cand apasati butonul de mai jos. At trebui sa incarcati acest fisier in acelasi director ca si phpBB2. Odata ce aceasta operatiune este terminata ar trebui sa va autentificati folosind numele de utilizator si parola administratorului pe care le-ati specificat in formularul anterior si sa vizitati centrul de control al administratorului (o legatura va aparea la capatul fiecarei pagini odata ce v-ati autentificat) ca sa verificati configuratia generala. Va multumim ca ati ales phpBB2.';
$lang['Download_config'] = 'Descarca configurarea';

$lang['ftp_choose'] = 'Alegeti metoda de descarcare';
$lang['ftp_option'] = '<br />Intrucat extensiile FTP sunt activate in aceasta versiune a PHP-ului, aveti posibilitatea de a incerca sa plasati prin ftp fisierul de configurare la locul lui.';
$lang['ftp_instructs'] = 'Ati ales sa transmiteti fisierul automat prin ftp in contul care contine phpBB2-ul. Va rugam introduceti informatiile cerute mai jos ca sa facilitati aceast proces. Calea unde este situat FTP-ul trebuie sa fie calea exacta via ftp la instalarea phpBB2-ului dumneavoastra ca si cum ati transmite folosind un client normal de ftp.';
$lang['ftp_info'] = 'Introduceti informatiile dumneavoastra despre FTP';
$lang['Attempt_ftp'] = 'Incercare de a transfera la locul specificat fisierul de configurare prin ftp';
$lang['Send_file'] = 'Trimite doar fisierul la mine si eu voi il voi trimite manual prin ftp';
$lang['ftp_path'] = 'Calea FTP la phpBB2';
$lang['ftp_username'] = 'Numele dumneavoastra de utilizator pentru FTP';
$lang['ftp_password'] = 'Parola dumneavoastra de utilizator pentru FTP';
$lang['Transfer_config'] = 'Porneste transferul';
$lang['NoFTP_config'] = 'Incercarea de a transfera la locul specificat fisierul de configurare prin ftp a esuat. Va rugam sa descarcati fisierul de configurare si sa-l transmiteti manual prin ftp la locul specificat.';

$lang['Install'] = 'Instaleaza';
$lang['Upgrade'] = 'Actualizeaza';


$lang['Install_Method'] = 'Alegeti metoda de instalare';

$lang['Install_No_Ext'] = 'Configurarea php-ului pe serverul dumneavoastra nu suporta tipul de baza de date pe care l-ati ales';

$lang['Install_No_PCRE'] = 'phpBB2 necesita modulul de expresii regulate compatibil Perl pentru php pe care configuratia dumneavoastra de php se pare ca nu-l suporta!';

//
// That's all Folks!
// -------------------------------------------------

?>
