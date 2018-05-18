<?php
// Romanian phpBB online community - Versiune actualizata pentru PhpBB 2.0.20
/***************************************************************************
 *                            lang_admin.php [română]
 *                              -------------------
 *     begin                : Sep 7 2002
 *     last update          : Jun 11, 2005
 *     language version     : 8.0
 *     copyright            : Romanian phpBB online community
 *     website              : http://www.phpbb.ro
 *     copyright 1          : (C) Daniel Tănasie
 *     email     1          : danielt@phpbb.ro
 *     copyright 2          : (C) Bogdan Toma
 *     email     2          : bogdan@phpbb.ro
 *
 *     $Id: lang_admin.php,v 1.1 2010/04/02 11:17:59 orynider Exp $
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


$lang['ENCODING'] = 'UTF-8';
$lang['DIRECTION'] = 'ltr';
$lang['LEFT'] = 'stânga';
$lang['RIGHT'] = 'dreapta';
$lang['DATE_FORMAT'] =  'd/M/Y'; // This should be changed to the default date format for your language, php date() format

$lang['General'] = 'Administrare generală';
$lang['Users'] = 'Administrare utilizatori';
$lang['Groups'] = 'Administrare grupuri';
$lang['Forums'] = 'Administrare forumuri';
$lang['Styles'] = 'Administrare stiluri';

$lang['Configuration'] = 'Configurare generală';
$lang['Permissions'] = 'Permisiuni';
$lang['Manage'] = 'Management';
$lang['Disallow'] = 'Dezactivare nume';
$lang['Prune'] = 'Curăţire';
$lang['Mass_Email'] = 'Expediere mesaje în bloc';
$lang['Ranks'] = 'Ranguri';
$lang['Smilies'] = 'Zâmbete';
$lang['Ban_Management'] = 'Control restricţii';
$lang['Word_Censor'] = 'Cuvinte cenzurate';
$lang['Export'] = 'Exportă';
$lang['Create_new'] = 'Creează';
$lang['Add_new'] = 'Adaugă';
$lang['Backup_DB'] = 'Salvează baza de date';
$lang['Restore_DB'] = 'Restaurează baza de date';


//
// Index
//
$lang['Admin'] = 'Administrare';
$lang['Not_admin'] = 'Nu sunteţi autorizat să administraţi acest forum';
$lang['Welcome_phpBB'] = 'Bine aţi venit la centrul de control al forumului phpBB';
$lang['Admin_intro'] = 'Vă mulţumim pentru că aţi ales phpBB ca soluţie pentru forumul dumneavoastră. Acest ecran vă oferă o privire de ansamblu a diverselor statistici ale forumului dumneavoastră. Puteţi reveni la această pagină folosind legătura <i>Pagina de start a administratorului</i> din partea stângă. Pentru a reveni la pagina de start a forumului dumneavoastră, apăsaţi pe logo-ul phpBB-ului aflat, de asemenea, în partea stângă. Celelalte legături din partea stângă vă permit să controlaţi orice aspect al forumului, fiecare ecran va avea instrucţiuni care dau explicaţii despre cum se folosesc instrumentele.';
$lang['Main_index'] = 'Pagina de start a forumului';
$lang['Forum_stats'] = 'Statisticile forumului';
$lang['Admin_Index'] = 'Pagina de start a administratorului';
$lang['Preview_forum'] = 'Previzualizare forum';

$lang['Click_return_admin_index'] = 'Apăsaţi %saici%s pentru a reveni la secţiunea Pagina de start a administratorului';

$lang['Statistic'] = 'Statistica';
$lang['Value'] = 'Valoarea';
$lang['Number_posts'] = 'Numărul mesajelor scrise';
$lang['Posts_per_day'] = 'Mesaje scrise pe zi';
$lang['Number_topics'] = 'Numărul subiectelor';
$lang['Topics_per_day'] = 'Subiecte pe zi';
$lang['Number_users'] = 'Numărul utilizatorilor';
$lang['Users_per_day'] = 'Utilizatori pe zi';
$lang['Board_started'] = 'Data lansării forumului';
$lang['Avatar_dir_size'] = 'Dimensiunea directorului cu imagini asociate (Avatar)';
$lang['Database_size'] = 'Dimensiunea bazei de date';
$lang['Gzip_compression'] ='Compresia Gzip';
$lang['Not_available'] = 'Nu este disponibil(ă)';

$lang['ON'] = 'Activă'; // This is for GZip compression
$lang['OFF'] = 'Inactivă';


//
// DB Utils
//
$lang['Database_Utilities'] = 'Instrumentele bazei de date';

$lang['Restore'] = 'Restaurare';
$lang['Backup'] = 'Salvare (Backup)';
$lang['Restore_explain'] = 'Aceasta va efectua o restaurare completă a tuturor tabelelor phpBB dintr-in fişier salvat. Dacă serverul dumneavoastră suportă, puteţi publica un fişier text compresat cu gzip şi aceasta va fi decomprimat automat. <b>ATENŢIE:</b> Această procedură va rescrie orice informaţie deja existentă. Procesul de restaurare poate dura un timp îndelungat; vă rugăm nu părăsiţi această pagină până când restaurarea nu se termină.';
$lang['Backup_explain'] = 'Aici puteţi face copii de rezervă ale tuturor datelor ce ţin de phpBB. Dacă aveţi şi tabele adiţionale în aceeaşi bază de date cu phpBB-ul pe care doriţi să le păstraţi, vă rugăm să introduceţi numele lor separate prin virgulă în căsuţa <i>Tabele Suplimentare</i> de mai jos. Dacă serverul dumneavoastră suportă, puteţi comprima fişierul cu gzip pentru a reduce dimensiunea sa înainte de a efectua operaţiunea de descărcare.';

$lang['Backup_options'] = 'Opţiunile de salvare (backup)';
$lang['Start_backup'] = 'Porneşte operaţiunea de salvare (backup)';
$lang['Full_backup'] = 'Salvare (Backup) totală';
$lang['Structure_backup'] = 'Salvează (copie de siguranţă) doar structura';
$lang['Data_backup'] = 'Salvează (copie de siguranţă) doar datele';
$lang['Additional_tables'] = 'Tabele suplimentare';
$lang['Gzip_compress'] = 'Fişier comprimat cu Gzip';
$lang['Select_file'] = 'Selectaţi un fişier';
$lang['Start_Restore'] = 'Porneşte operaţiunea de restaurare';

$lang['Restore_success'] = 'Baza de date a fost restaurată cu succes.<br /><br />Forumul dumneavoastră ar trebui să revină la starea lui înainte ca salvarea să se fi realizat.';
$lang['Backup_download'] = 'Operaţiunea de descărcare va începe în curând; vă rugăm să aşteptaţi până aceasta va începe';
$lang['Backups_not_supported'] = 'Scuzaţi, dar efectuarea salvării (backup-ului) nu este în prezent realizabilă pentru sistemul dumneavoastră de baze de date';

$lang['Restore_Error_uploading'] = 'Eroare la publicarea fişierului de salvare (backup)';
$lang['Restore_Error_filename'] = 'Problemă cu numele fişierului; vă rugăm, încercaţi cu un alt fişier';
$lang['Restore_Error_decompress'] = 'Nu pot decomprima un fişier gzip; vă rugăm, publicaţi o versiune text întreg (plain text)';
$lang['Restore_Error_no_file'] = 'Nici un fişier nu a fost publicat/încărcat';


//
// Auth pages
//
$lang['Select_a_User'] = 'Selectaţi un utilizator';
$lang['Select_a_Group'] = 'Selectaţi un grup';
$lang['Select_a_Forum'] = 'Selectaţi un forum';
$lang['Auth_Control_User'] = 'Controlul permisiunilor utilizatorului';
$lang['Auth_Control_Group'] = 'Controlul permisiunilor grupului';
$lang['Auth_Control_Forum'] = 'Controlul permisiunilor forumului';
$lang['Look_up_User'] = 'Selectează utilizatorul';
$lang['Look_up_Group'] = 'Selectează grupul';
$lang['Look_up_Forum'] = 'Selectează forumul';

$lang['Group_auth_explain'] = 'Aici puteţi modifica permisiunile şi starea moderatorului asociat la fiecare grup de utilizatori. Nu uitaţi când schimbaţi permisiunile grupului că permisiunile individuale ale utilizatorului pot să permită accesul utilizatorului la forumuri, etc. Veţi fi atenţionat dacă va apărea această situaţie.';
$lang['User_auth_explain'] = 'Aici puteţi modifica permisiunile şi starea moderatorului asociat la fiecare utilizator individual. Nu uitaţi când schimbaţi permisiunile utilizatorului că permisiunile individuale ale grupului pot să permită accesul utilizatorului la forumuri, etc. Veţi fi atenţionat dacă va apărea această situaţie.';
$lang['Forum_auth_explain'] = 'Aici puteţi modifica nivelurile de autorizare ale fiecărui forum. Pentru a realiza acest lucru aveţi la dispoziţie atât o metodă simplă cât şi una avansată, metoda avansată oferind un control mai mare al fiecăriei operaţii din forum. Amintiţi-vă că schimbarea nivelului de permisiuni ale forumurilor va afecta modul de realizare(finalizare) al diverselor operaţiuni solicitate de către utilizatori.';

$lang['Simple_mode'] = 'Modul simplu';
$lang['Advanced_mode'] = 'Modul avansat';
$lang['Moderator_status'] = 'Starea moderatorului';

$lang['Allowed_Access'] = 'Acces permis';
$lang['Disallowed_Access'] = 'Acces interzis';
$lang['Is_Moderator'] = 'este moderator';
$lang['Not_Moderator'] = 'nu este moderator';

$lang['Conflict_warning'] = 'Avertizare - Conflict de autorizare';
$lang['Conflict_access_userauth'] = 'Acest utilizator are încă drepturi de acces la acest forum datorate apartenenţei acestuia la grup. Puteţi să modificaţi permisiunile grupului sau să înlăturaţi acest utilizator din grup pentru a nu mai avea depturi de acces. Grupurile care dau drepturi (şi forumurile implicate) sunt afişate mai jos.';
$lang['Conflict_mod_userauth'] = 'Acest utilizator are încă drepturi de moderator la acest forum datorate apartenenţei acestuia la grup. Puteţi să modificaţi permisiunile grupului sau să înlăturaţi acest utilizator din grup pentru a nu mai avea depturi de moderator. Grupurile care dau drepturi (şi forumurile implicate) sunt afişate mai jos.';

$lang['Conflict_access_groupauth'] = 'Utilizatorul(i) următor(i) are(au) încă drepturi de acces la acest forum datorate setărilor lui(lor) de permisiuni. Puteţi să modificaţi permisiunile utilizatorului pentru a nu mai avea drepturi de acces. Utilizatorii care dau drepturi (şi forumurile implicate) sunt afişaţi mai jos.';
$lang['Conflict_mod_groupauth'] = 'Utilizatorul(i) următor(i) are(au) încă drepturi de acces la acest forum datorate setărilor lui(lor) de permisiuni. Puteţi să modificaţi permisiunile utilizatorului pentru a nu mai avea drepturi de moderator. Utilizatorii care dau drepturi (şi forumurile implicate) sunt afişaţi mai jos.';

$lang['Public'] = 'Public';
$lang['Private'] = 'Privat';
$lang['Registered'] = 'Înregistrat';
$lang['Administrators'] = 'Administratori';
$lang['Hidden'] = 'Ascuns';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = 'TOŢI';
$lang['Forum_REG'] = 'ÎNREG';
$lang['Forum_PRIVATE'] = 'PRIVAT';
$lang['Forum_MOD'] = 'MOD';
$lang['Forum_ADMIN'] = 'ADMIN';

$lang['View'] = 'Vizualizare';
$lang['Read'] = 'Citire';
$lang['Post'] = 'Scriere';
$lang['Reply'] = 'Răspunde';
$lang['Edit'] = 'Modifică';
$lang['Delete'] = 'Şterge';
$lang['Sticky'] = 'Important';
$lang['Announce'] = 'Anunţ';
$lang['Vote'] = 'Vot';
$lang['Pollcreate'] = 'Creare sondaj';

$lang['Permissions'] = 'Permisiuni';
$lang['Simple_Permission'] = 'Permisiune simplă';

$lang['User_Level'] = 'Nivelul utilizatorului';
$lang['Auth_User'] = 'Utilizator';
$lang['Auth_Admin'] = 'Administrator';
$lang['Group_memberships'] = 'Membru al grupurilor';
$lang['Usergroup_members'] = 'Acest grup conţine următorii membrii';

$lang['Forum_auth_updated'] = 'Permisiunile forumului au fost actualizate';
$lang['User_auth_updated'] = 'Permisiunile utilizatorului au fost actualizate';
$lang['Group_auth_updated'] = 'Permisiunile grupului au fost actualizate';

$lang['Auth_updated'] = 'Permisiunile au fost actualizate';
$lang['Click_return_userauth'] = 'Apăsaţi %saici%s pentru a reveni la secţiunea Controlul permisiunilor utilizatorului';
$lang['Click_return_groupauth'] = 'Apăsaţi %saici%s pentru a reveni la secţiunea Controlul permisiunilor grupului';
$lang['Click_return_forumauth'] = 'Apăsaţi %saici%s pentru a reveni la secţiunea Controlul permisiunilor forumului';


//
// Banning
//
$lang['Ban_control'] = 'Controlul interdicţiilor';
$lang['Ban_explain'] = 'Aici puteţi să controlaţi interdicţiile utilizatorilor. Puteţi obţine acest lucru interzicând una sau mai multe din elementele caracteristice unui utilizator: denumire utilizator, mulţimea adreselor IP sau numele host-urilor. Aceste metode împiedică un utilizator să nu ajungă în pagina de început a forumului. Pentru a împiedica un utilizator să se înregistreze sub un alt nume de utilizator puteţi specifica o adresă de mail interzisă. Reţineţi că o singură adresă de mail interzisă nu-l va împiedeca pe utilizatorul în cauză să intre sau să scrie în forumul dumneavoastră; ar trebui să folosiţi prima din cele două metode.';
$lang['Ban_explain_warn'] = 'Reţineţi că introducerea unei mulţimi de adrese IP înseamnă că toate adresele dintre începutul şi sfârşitul mulţimii au fost adăugate la lista interzisă. Pentru a reduce numărul de adrese adăugate la baza de date se pot folosi <i>wildcard</i>-urile unde este cazul. Dacă chiar trebuie să introduceţi o plajă de valori, încercaţi să o păstraţi cât mai mică sau mai bine reţineţi doar adresele specifice.';

$lang['Select_username'] = 'Selectaţi un nume de utilizator';
$lang['Select_ip'] = 'Selectaţi un IP';
$lang['Select_email'] = 'Selectaţi o adresă de email';

$lang['Ban_username'] = 'Interziceţi unul sau mai mulţi utilizatori';
$lang['Ban_username_explain'] = 'Puteţi interzice mai mulţi utilizatori într-un singur pas folosind combinaţii potrivite ale mouse-ului (în browser) şi tastaturii calculatorului dumneavoastră';

$lang['Ban_IP'] = 'Interziceţi una sau mai multe adrese IP sau nume de host-uri';
$lang['IP_hostname'] = 'Adrese IP sau nume de host-uri';
$lang['Ban_IP_explain'] = 'Pentru a specifica mai multe IP-uri diferite sau nume de host-uri trebuie să le separaţi prin virgulă. Pentru a specifica o mulţime de adrese IP, separaţi începutul şi sfârşitul mulţimii cu o liniuţă de unire (-); ca să specificaţi caracterul <i>wildcard</i> folosiţi *';

$lang['Ban_email'] = 'Interziceţi una sau mai multe adrese de email';
$lang['Ban_email_explain'] = 'Pentru a specifica mai multe adrese de email folosiţi separatorul virgulă. Ca să specificaţi un utilizator cu ajutorul <i>wildcard</i>-ului folosiţi *, de exemplu *@hotmail.com';

$lang['Unban_username'] = 'Deblocarea utilizatorilor';
$lang['Unban_username_explain'] = 'Puteţi să deblocaţi mai mulţi utilizatori într-un singur pas folosind combinaţii potrivite ale mouse-ului (în browser) şi tastaturii calculatorului dumneavoastră';

$lang['Unban_IP'] = 'Deblocarea adreselor IP';
$lang['Unban_IP_explain'] = 'Puteţi să deblocaţi mai multe adrese IP într-un singur pas folosind combinaţii potrivite ale mouse-ului (în browser) şi tastaturii calculatorului dumneavoastră';

$lang['Unban_email'] = 'Deblocarea adreselor email';
$lang['Unban_email_explain'] = 'Puteţi să deblocaţi mai multe adrese email într-un singur pas folosind combinaţii potrivite ale mouse-ului (în browser) şi tastaturii calculatorului dumneavoastră';

$lang['No_banned_users'] = 'Nu este nici un utilizator interzis';
$lang['No_banned_ip'] = 'Nu este nici o adresă IP interzisă';
$lang['No_banned_email'] = 'Nu este nici o adresă de email interzisă';

$lang['Ban_update_sucessful'] = 'Lista restricţiilor a fost actualizată cu succes';
$lang['Click_return_banadmin'] = 'Apăsaţi %saici%s pentru a reveni la secţiunea Control Restricţii';


//
// Configuration
//
$lang['General_Config'] = 'Configurare generală';
$lang['Config_explain'] = 'Formularul de mai jos vă permite să personalizaţi toate opţiunile generale ale forumului. Pentru configurarea utilizatorilor şi forumurilor folosiţi legăturile specifice aflate în partea stângă.';

$lang['Click_return_config'] = 'Apăsaţi %saici%s pentru a reveni la secţiunea Configurare generală';

$lang['General_settings'] = 'Setările generale ale forumului';
$lang['Server_name'] = 'Numele domeniului';
$lang['Server_name_explain'] = 'Numele domeniului acestui forum rulează din';
$lang['Script_path'] = 'Calea script-ului';
$lang['Script_path_explain'] = 'Calea unde phpBB2 este localizat relativ la numele domeniului';
$lang['Server_port'] = 'Port-ul serverului';
$lang['Server_port_explain'] = 'Port-ul pe care serverul dumneavoastră rulează este de obicei 80 (numai dacă nu a fost schimbat)';
$lang['Site_name'] = 'Numele site-ului';
$lang['Site_desc'] = 'Descrierea site-ului';
$lang['Board_disable'] = 'Forum dezactivat';
$lang['Board_disable_explain'] = 'Această acţiune va face forumul indisponibil utilizatorilor. Nu închideţi sesiunea curentă când dezactivaţi forumul, altfel nu veţi mai fi capabil să vă autentificaţi din nou!';
$lang['Acct_activation'] = 'Validarea contului activată de';
$lang['Acc_None'] = 'Nimeni'; // These three entries are the type of activation
$lang['Acc_User'] = 'Utilizator';
$lang['Acc_Admin'] = 'Administrator';

$lang['Abilities_settings'] = 'Configurările de bază ale utilizatorilor şi forumurilor';
$lang['Max_poll_options'] = 'Numărul maxim al opţiunilor chestionarului';
$lang['Flood_Interval'] = 'Interval de flood';
$lang['Flood_Interval_explain'] = 'Numărul de secunde pe care un utilzator trebuie să-l aştepte între publicări';
$lang['Board_email_form'] = 'Trimite mesaj la utilizator via forum';
$lang['Board_email_form_explain'] = 'Utilizatorii pot trimit mesaje unii la alţi prin acest forum';
$lang['Topics_per_page'] = 'Subiecte pe pagină';
$lang['Posts_per_page'] = 'Mesaje pe pagină';
$lang['Hot_threshold'] = 'Mesaje pentru statutul popular';
$lang['Default_style'] = 'Stilul standard';
$lang['Override_style'] = 'Suprascrie stilul utilizatorului';
$lang['Override_style_explain'] = 'Înlocuirea sitului utilizatorilor cu cel standard';
$lang['Default_language'] = 'Limba standard';
$lang['Date_format'] = 'Formatul datei';
$lang['System_timezone'] = 'Timpul zonal al sistemului';
$lang['Enable_gzip'] = 'Activare compresie GZip';
$lang['Enable_prune'] = 'Activare curăţire forum';
$lang['Allow_HTML'] = 'Permite HTML';
$lang['Allow_BBCode'] = 'Permite cod BB';
$lang['Allowed_tags'] = 'Permite balize (tag-uri) HTML';
$lang['Allowed_tags_explain'] = 'Separă balizele (tag-urile) cu virgule';
$lang['Allow_smilies'] = 'Permite zâmbete';
$lang['Smilies_path'] = 'Calea unde se păstrează zâmbetele';
$lang['Smilies_path_explain'] = 'Calea aflată în directorul dumneavoastră phpBB , de exemplu. imagini/zâmbete';
$lang['Allow_sig'] = 'Permite semnături';
$lang['Max_sig_length'] = 'Lungimea maximă a semnăturii';
$lang['Max_sig_length_explain'] = 'Numărul maxim de caractere aflate în semnătura utilizatorului';
$lang['Allow_name_change'] = 'Permite schimbarea numelui de utilizator';

$lang['Avatar_settings'] = 'Configurări pentru imagini asociate (Avatar)';
$lang['Allow_local'] = 'Permite galerie de imagini asociate';
$lang['Allow_remote'] = 'Permite imagini asociate la distanţă';
$lang['Allow_remote_explain'] = 'Imaginile asociate sunt specificate cu o legătură la alt site web';
$lang['Allow_upload'] = 'Permite încărcarea imaginii asociate';
$lang['Max_filesize'] = 'Dimensiunea maximă a fişierului ce conţine imaginea asociată';
$lang['Max_filesize_explain'] = 'Pentru fişierele ce conţin imaginile asociate încărcate';
$lang['Max_avatar_size'] = 'Dimensiunea maximă a imaginii asociate';
$lang['Max_avatar_size_explain'] = '(Înălţime x Lăţime în pixeli)';
$lang['Avatar_storage_path'] = 'Calea de păstrare a imaginilor asociate';
$lang['Avatar_storage_path_explain'] = 'Calea aflată în directorul dumneavoastră phpBB, de exemplu. imagini/avatar';
$lang['Avatar_gallery_path'] = 'Calea de păstrare a galeriilor cu imagini asociate';
$lang['Avatar_gallery_path_explain'] = 'Calea aflată în directorul dumneavoastră phpBB, de exemplu. imagini/avatar/galerie';

$lang['COPPA_settings'] = 'Configurările COPPA';
$lang['COPPA_fax'] = 'Numărul de fax';
$lang['COPPA_mail'] = 'Adresa poştală COPPA';
$lang['COPPA_mail_explain'] = 'Aceasta este adresa poştală unde părinţii vor trimite formularele de înregistrare COPPA';

$lang['Email_settings'] = 'Configurările de email';
$lang['Admin_email'] = 'Adresa de email a administratorului';
$lang['Email_sig'] = 'Semnătura din email';
$lang['Email_sig_explain'] = 'Acest text va fi ataşat la toate mesajele pe care forumul le trimite';
$lang['Use_SMTP'] = 'Folosiţi serverul SMTP pentru email';
$lang['Use_SMTP_explain'] = 'Specificaţi da dacă doriţi sau aveţi nevoie să trimiteţi mesaje printr-un alt server în loc să folosiţi funcţia locală de mesagerie';
$lang['SMTP_server'] = 'Adresa serverului SMTP';
$lang['SMTP_username'] = 'Numele de utilizator SMTP';
$lang['SMTP_username_explain'] = 'Introduceţi numele de utilizator doar dacă serverul dumneavoastră SMTP necesită această specificare';
$lang['SMTP_password'] = 'Parola SMTP';
$lang['SMTP_password_explain'] = 'Introduceţi parola doar dacă serverul dumneavoastră SMTP necesită această specificare';

$lang['Disable_privmsg'] = 'Mesagerie privată';
$lang['Inbox_limits'] = 'Numărul maxim al mesajelor în Cutia cu mesaje (Inbox)';
$lang['Sentbox_limits'] = 'Numărul maxim al mesajelor în Cutia cu mesaje trimise (Sentbox)';
$lang['Savebox_limits'] = 'Numărul maxim al mesajelor în Cutia cu mesaje salvate (Savebox)';

$lang['Cookie_settings'] = 'Configurările pentru cookie';
$lang['Cookie_settings_explain'] = 'Aceste detalii definesc cum sunt cookie-urile trimise către browser-ele utilizatorilor. În cele mai multe cazuri valorile standard pentru setările cookie-urilor ar trebui să fie suficiente dar dacă trebuie să le schimbaţi aveţi mare grijă, setările incorecte pot împiedica utilizatorii să se autentifice';
$lang['Cookie_domain'] = 'Domeniul pentru cookie';
$lang['Cookie_name'] = 'Numele pentru cookie';
$lang['Cookie_path'] = 'Calea pentru cookie';
$lang['Cookie_secure'] = 'Securizare cookie';
$lang['Cookie_secure_explain'] = 'Dacă serverul dumneavoastră rulează via SSL, selectaţi <i>Activat</i> altfel selectaţi <i>Dezactivat</i>';
$lang['Session_length'] = 'Durata sesiunii [ secunde ]';


// Visual Confirmation
$lang['Visual_confirm'] = 'Activează Confirmarea Vizuală';
$lang['Visual_confirm_explain'] = 'Necesită introducerea unui cod vizual definit ca o imagine la înregistrare.';

// Autologin Keys - added 2.0.18
$lang['Allow_autologin'] = 'Permite autentificări automate';
$lang['Allow_autologin_explain'] = 'Determină dacă utilizatorii au voie să selecteze să fie autentificaţi automat când vizitează forumul.';
$lang['Autologin_time'] = 'Expirarea cheii de autentificare automată.';
$lang['Autologin_time_explain'] = 'Câte zile este validă o cheie de autentificare automată dacă utilizatorul nu vizitează forumul. Setează 0 pentru a dezactiva expirarea.';

// Intervalul limita pentru cautari - adaugat la 2.0.20
$lang['Search_Flood_Interval'] = 'Intervalul limită pentru căutări';
$lang['Search_Flood_Interval_explain'] = 'Numărul de secunde pe care un utilizator trebuie să-l aştepte între căutari'; 

//
// Forum Management
//
$lang['Forum_admin'] = 'Administrare forumuri';
$lang['Forum_admin_explain'] = 'În această secţiune puteţi adăuga, şterge, modifica, reordona şi resincroniza categoriile şi forumurile.';
$lang['Edit_forum'] = 'Modificare forum';
$lang['Create_forum'] = 'Crează un forum nou';
$lang['Create_category'] = 'Crează o categorie nouă';
$lang['Remove'] = 'Şterge';
$lang['Action'] = 'Acţiune';
$lang['Update_order'] = 'Actualizează ordinea';
$lang['Config_updated'] = 'Configurările la forum au fost actualizate cu succes';
$lang['Edit'] = 'Modifică';
$lang['Delete'] = 'Şterge';
$lang['Move_up'] = 'Mută mai sus';
$lang['Move_down'] = 'Mută mai jos';
$lang['Resync'] = 'Resincronizare';
$lang['No_mode'] = 'Nici un mod nu a fost specificat';
$lang['Forum_edit_delete_explain'] = 'Formularul de mai jos vă permite să personalizaţi toate opţiunile generale ale forumului. Pentru configurarea utilizatorilor şi forumurilor folosiţi legăturile specifice aflate în partea stângă.';

$lang['Move_contents'] = 'Mută tot conţinutul';
$lang['Forum_delete'] = 'Ştergere forum';
$lang['Forum_delete_explain'] = 'Formularul de mai jos vă permite să ştergeţi un forum (sau o categorie) şi să decideţi unde doriţi să plasaţi toate subiectele (sau forumurile) pe care le conţine.';

$lang['Status_locked'] = 'Închis';
$lang['Status_unlocked'] = 'Deschis';
$lang['Forum_settings'] = 'Configurările generale ale forumului';
$lang['Forum_name'] = 'Numele forumului';
$lang['Forum_desc'] = 'Descriere';
$lang['Forum_status'] = 'Starea forumului';
$lang['Forum_pruning'] = 'Autocurăţare';

$lang['Forum_postcount'] = 'Count user\'s posts';

$lang['prune_freq'] = 'Verifică vârsta subiectelor la fiecare';
$lang['prune_days'] = 'Şterge subiectele la care nu s-au scris răspunsuri de';
$lang['Set_prune_data'] = 'Aţi selectat opţiunea autocurăţire pentru acest forum dar nu aţi specificat o frecvenţă sau un număr de zile al intervalului pentru acest proces. Vă rugăm reveniţi şi specificaţi aceste valori';

$lang['Move_and_Delete'] = 'Mută şi şterge';

$lang['Delete_all_posts'] = 'Şterge toate mesajele';
$lang['Nowhere_to_move'] = 'Nu muta mesajele';

$lang['Edit_Category'] = 'Modificare categorie';
$lang['Edit_Category_explain'] = 'Puteţi folosi acest forumlar pentru a modifica numele categoriilor.';

$lang['Forums_updated'] = 'Informaţiile despre forumuri şi categorii au fost actualizate cu succes';

$lang['Must_delete_forums'] = 'Trebuie să ştergeţi toate forumurile înainte ca să ştergeţi această categorie';

$lang['Click_return_forumadmin'] = 'Apăsaţi %saici%s pentru a reveni la secţiunea Administrare forumuri';


//
// Smiley Management
//
$lang['smiley_title'] = 'Administrare zâmbete';
$lang['smile_desc'] = 'Din această pagină puteţi adăuga, şterge şi modifica zâmbetele sau emoţiile asociate pe care utilizatorii dumneavoastră le pot folosi când scriu mesaje sau când trimit mesaje private.';

$lang['smiley_config'] = 'Configurare zâmbete';
$lang['smiley_code'] = 'Cod zâmbet';
$lang['smiley_url'] = 'Fişierul imagine al zâmbetului';
$lang['smiley_emot'] = 'Emoţia asociată';
$lang['smile_add'] = 'Adăugaţi un zâmbet nou';
$lang['Smile'] = 'Zâmbet';
$lang['Emotion'] = 'Emoţia asociată';

$lang['Select_pak'] = 'Selectaţi un fişier de tip Pack (.pak)';
$lang['replace_existing'] = 'Înlocuiţi zâmbetele existente';
$lang['keep_existing'] = 'Păstraţi zâmbetele existente';
$lang['smiley_import_inst'] = 'Ar trebui să decomprimaţi pachetul cu iconiţe şi să încărcaţi toate fişierele în directorul cu zâmbete specificat la instalare. Apoi selectaţi informaţiile corecte în acest formular ca să importaţi pachetul cu zâmbete.';
$lang['smiley_import'] = 'Importaţi zâmbetele';
$lang['choose_smile_pak'] = 'Selectaţi un fişier pachet cu zâmbete de tip .pak';
$lang['import'] = 'Importaţi zâmbete';
$lang['smile_conflicts'] = 'Ce ar trebui să fie făcut în caz de conflicte';
$lang['del_existing_smileys'] = 'Ştergeţi zâmbetele existente înainte de import';
$lang['import_smile_pack'] = 'Importaţi pachetul cu zâmbete';
$lang['export_smile_pack'] = 'Creaţi pachetul cu zâmbete';
$lang['export_smiles'] = 'Ca să creaţi un pachet cu zâmbete din zâmbetele instalate, apăsaţi %saici%s ca să descărcaţi fişierul cu zâmbete .pak. Numiţi acest fişier cum doriţi dar asiguraţi-vă că aţi păstrat fişierului extensia .pak. Apoi creaţi un fieşier arhivat conţinând toate imaginile zâmbete ale dumneavoastră plus acest fişier .pak.';

$lang['smiley_add_success'] = 'Zâmbetul a fost adăugat cu succes';
$lang['smiley_edit_success'] = 'Zâmbetul a fost actualizat cu succes';
$lang['smiley_import_success'] = 'Pachetul cu zâmbete a fost importat cu succes!';
$lang['smiley_del_success'] = 'Zâmbetul a fost şters cu succes';
$lang['Click_return_smileadmin'] = 'Apăsaţi %saici%s pentru a reveni la secţiunea Administrare zâmbete';

$lang['Confirm_delete_smiley'] = 'Sunteţi sigur că doriţi să ştergeţi acest zâmbet ?';

//
// User Management
//
$lang['User_admin'] = 'Administrare utilizatori';
$lang['User_admin_explain'] = 'Aici puteţi schimba informaţiile despre utilizatorii dumneavoastră şi opţiunile specifice. Ca să modificaţi drepturile utilizatorilor, folosiţi drepturile din sistem ale utilizatorilor şi grupurilor.';

$lang['Look_up_user'] = 'Selectează utilizatorul';

$lang['Admin_user_fail'] = 'Nu se poate actualiza profilul utilizatorului.';
$lang['Admin_user_updated'] = 'Profilul utilizatorului a fost actualizat cu succes.';
$lang['Click_return_useradmin'] = 'Apăsaţi %saici%s pentru a reveni la secţiunea Administrare utilizatori';

$lang['User_delete'] = 'Ştergeţi acest utilizator';
$lang['User_delete_explain'] = 'Apăsaţi aici pentru a şterge acest utilizator, această operaţie este ireversibilă.';
$lang['User_deleted'] = 'Utilizatorul a fost şters cu succes.';

$lang['User_status'] = 'Utilizatorul este activ';
$lang['User_allowpm'] = 'Poate trimite mesaje private';
$lang['User_allowavatar'] = 'Poate folosi imagini asociate';

$lang['Admin_avatar_explain'] = 'Aici puteţi vizualiza şi şterge imaginea asociată a utilizatorului.';

$lang['User_special'] = 'Câmpuri speciale doar pentru administrator';
$lang['User_special_explain'] = 'Aceste câmpuri nu pot fi modificate de către utilizatori. Aici puteţi să specificaţi stadiul lor şi alte opţiuni care nu sunt oferite utilizatorilor.';


//
// Group Management
//
$lang['Group_administration'] = 'Administrarea grupurilor';
$lang['Group_admin_explain'] = 'Din această secţiune puteţi administra toate grupurile cu utilizatori ale dumneavoastră, puteţi şterge, crea şi modifica grupurile existente. Puteţi alege moderatorii, schimba în deschis/închis statutul grupului şi specifica numele şi descrierea grupului';
$lang['Error_updating_groups'] = 'A fost o eroare în timpul actualizării grupurilor';
$lang['Updated_group'] = 'Grupul a fost actualizat cu succes';
$lang['Added_new_group'] = 'Noul grup a fost creat cu succes';
$lang['Deleted_group'] = 'Grupul a fost şters cu succes';
$lang['New_group'] = 'Crează un grup nou';
$lang['Edit_group'] = 'Modifică grupul';
$lang['group_name'] = 'Numele grupului';
$lang['group_description'] = 'Descrierea grupului';
$lang['group_moderator'] = 'Moderatorul grupului';
$lang['group_status'] = 'Statutul grupului';
$lang['group_open'] = 'Grup deschis';
$lang['group_closed'] = 'Grup închis';
$lang['group_hidden'] = 'Grup ascuns';
$lang['group_delete'] = 'Şterg grupul';
$lang['group_delete_check'] = 'Vreau să şterg acest grup';
$lang['submit_group_changes'] = 'Efectuează modificările';
$lang['reset_group_changes'] = 'Resetează modificările';
$lang['No_group_name'] = 'Trebuie să specificaţi un nume pentru acest grup';
$lang['No_group_moderator'] = 'Trebuie să specificaţi un moderator pentru acest grup';
$lang['No_group_mode'] = 'Trebuie să specificaţi un mod (deschis/închis) pentru acest grup';
$lang['No_group_action'] = 'Nici o acţiune nu a fost specificată';
$lang['delete_group_moderator'] = 'Doriţi să ştergeţi moderatorul vechi al grupului?';
$lang['delete_moderator_explain'] = 'Dacă schimbaţi moderatorul grupului, bifaţi această căsuţă ca să ştergeţi vechiul moderator al grupului din grup. Altfel, nu o bifaţi şi utilizatorul va deveni un membru normal al grupului.';
$lang['Click_return_groupsadmin'] = 'Apăsaţi %saici%s pentru a reveni la secţiunea Administrarea grupurilor.';
$lang['Select_group'] = 'Selectează un grup';
$lang['Look_up_group'] = 'Selectează grupul';


//
// Prune Administration
//
$lang['Forum_Prune'] = 'Curăţirea forumurilor';
$lang['Forum_Prune_explain'] = 'Această acţiune va şterge orice subiect care nu a fost completat într-un număr de zile egal cu cel pe care l-aţi specificat. Dacă nu aţi introdus un număr atunci toate subiectele vor fi şterse. Nu vor fi şterse subiecte în care sondajele încă rulează şi nici anunţurile. Aceste subiecte trebuie să le ştergeţi manual.';
$lang['Do_Prune'] = 'Efectuează curăţirea';
$lang['All_Forums'] = 'Toate forumurile';
$lang['Prune_topics_not_posted'] = 'Curăţirea subiectelor fără răspunsuri în multe zile';
$lang['Topics_pruned'] = 'Subiecte curăţite';
$lang['Posts_pruned'] = 'Mesaje curăţite';
$lang['Prune_success'] = 'Curăţirea mesajelor s-a efectuat cu succes';


//
// Word censor
//
$lang['Words_title'] = 'Administrarea cuvintelor cenzurate';
$lang['Words_explain'] = 'Din această secţiune puteţi adăuga, modifica şi şterge cuvinte care vor fi automat cenzurate în forumurile dumneavoastră. În plus, persoanelor nu le va fi permis să se înregistreze cu nume de utilizator ce conţin aceste cuvinte. Wildcard-urile (*) sunt acceptate în câmpul pentru cuvinte, de exemplu *test* se va potrivi cu detestabil, test* se va potrivi cu testare, *test se va potrivi cu detest.';
$lang['Word'] = 'Cuvânt';
$lang['Edit_word_censor'] = 'Modific cuvântul cenzurat';
$lang['Replacement'] = 'Înlocuire';
$lang['Add_new_word'] = 'Adaugă un cuvânt nou';
$lang['Update_word'] = 'Actualizează cuvântul cenzurat';

$lang['Must_enter_word'] = 'Trebuie să introduceţi un cuvânt şi înlocuirile acestuia';
$lang['No_word_selected'] = 'Nici un cuvânt nu a fost selectat pentru modificare';

$lang['Word_updated'] = 'Cuvântul cenzurat selectat a fost actualizat cu succes';
$lang['Word_added'] = 'Cuvântul cenzurat a fost adăugat cu succes';
$lang['Word_removed'] = 'Cuvântul cenzurat selectat a fost şters cu succes';

$lang['Click_return_wordadmin'] = 'Apăsaţi %saici%s pentru a reveni la secţiunea Administrarea cuvintelor cenzurate';

$lang['Confirm_delete_word'] = 'Sunteţi sigur că doriţi să ştergeţi acest acest cuvânt cenzurat ?';


//
// Mass Email
//
$lang['Mass_email_explain'] = 'Aici puteţi trimite un email la toţi utilizatorii dumneavoastră sau la utilizatorii dintr-un grup specific. Pentru a realiza acest lucru, un email va fi trimis la adresa de email a administratorulu cu toţi destinatarii specificaţi în câmpul BCC. Dacă trimiteţi email la un grup mare de oameni, vă rugăm să fiţi atent după trimitere şi nu vă opriţi la jumătatea paginii. Este normal ca pentru o corespondenţă masivă să fie nevoie de un timp mai lung astfel că veţi fi notificat când acţiunea s-a terminat';
$lang['Compose'] = 'Compune';

$lang['Recipients'] = 'Destinatari';
$lang['All_users'] = 'Toţi utilizatorii';

$lang['Email_successfull'] = 'Mesajul dumneavoastră a fost trimis';
$lang['Click_return_massemail'] = 'Apăsaţi %saici%s pentru a reveni la secţiunea Corespondenţă masivă';


//
// Ranks admin
//
$lang['Ranks_title'] = 'Administrarea rangurilor';
$lang['Ranks_explain'] = 'Folosind acest formular puteţi adăuga, modifica, vizualiza şi şterge ranguri. De asemenea, puteţi crea ranguri personalizate care pot fi aplicate unui utilizator via facilităţii date de managementul utilizatorilor';

$lang['Add_new_rank'] = 'Adaugă un rang nou';

$lang['Rank_title'] = 'Titlul rangului';
$lang['Rank_special'] = 'Setează ca rang special';
$lang['Rank_minimum'] = 'Număr minim de mesaje';
$lang['Rank_maximum'] = 'Număr maxim de mesaje';
$lang['Rank_image'] = 'Imaginea rangului (relativ la calea phpBB2-ului)';
$lang['Rank_image_explain'] = 'Aceasta este folosită pentru a defini o imagine mică asociată cu rangul';

$lang['Must_select_rank'] = 'Trebuie să selectaţi un rang';
$lang['No_assigned_rank'] = 'Nici un rang special nu a fost repartizat';

$lang['Rank_updated'] = 'Rangul a fost actualizat cu succes';
$lang['Rank_added'] = 'Rangul a fost adăugat cu succes';
$lang['Rank_removed'] = 'Rangul a fost şters cu succes';
$lang['No_update_ranks'] = 'Rangul a fost şters cu succes, conturile utilizatorilor care folosesc acest rang nu au fost actualizate. Trebuie să resetaţi manual rangul pentru aceste conturi';

$lang['Click_return_rankadmin'] = 'Apăsaţi %saici%s pentru a reveni la secţiunea Administrarea rangurilor';

$lang['Confirm_delete_rank'] = 'Sunteti sigur ca doriti sa stergeti acest rang ?';

//
// Disallow Username Admin
//
$lang['Disallow_control'] = 'Administrarea numelor de utilizator nepremise';
$lang['Disallow_explain'] = 'Aici puteţi controla numele de utilizator care nu sunt permise să fie folosite. Numele de utilizator care nu sunt permise pot conţine caracterul *. Reţineţi că nu aveţi posibilitatea să specificaţi orice nume de utilizator care a fost deja înregistrat; trebuie mai întâi să ştergeţi acel nume şi apoi să-l interziceţi';

$lang['Delete_disallow'] = 'Şterge';
$lang['Delete_disallow_title'] = 'Şterge un nume de utilizator nepermis';
$lang['Delete_disallow_explain'] = 'Puteţi şterge un nume de utilizator nepermis selectând numele de utilizator din această listă şi apăsând butonul <i>Şterge</i>';

$lang['Add_disallow'] = 'Adaugă';
$lang['Add_disallow_title'] = 'Adaugă un nume de utilizator nepermis';
$lang['Add_disallow_explain'] = 'Puteţi interzice un nume de utilizator folosind caracterul wildcard * care se potriveşte la orice caracter';

$lang['No_disallowed'] = 'Nici un nume de utilizator nu a fost interzis';

$lang['Disallowed_deleted'] = 'Numele de utilizator nepermis a fost şters cu succes';
$lang['Disallow_successful'] = 'Numele de utilizator nepermis a fost adăugat cu succes';
$lang['Disallowed_already'] = 'Numele pe care l-aţi introdus nu poate fi interzis. Ori există deja în listă, există în lista cuvintelor cenzurate sau există un nume de utilizator similar';

$lang['Click_return_disallowadmin'] = 'Apăsaţi %saici%s pentru a reveni la secţiunea Administrarea numelor de utilizator nepremise';


//
// Styles Admin
//
$lang['Styles_admin'] = 'Administrarea stilurilor';
$lang['Styles_explain'] = 'Folosind această facilitate puteţi adăuga, şterge şi administra stilurile (şabloanele şi temele) disponibile utilizatorilor dumneavoastră';
$lang['Styles_addnew_explain'] = 'Lista următoare conţine toate temele care sunt disponibile pentru şabloanele pe care le aveţi. Elementele din această listă nu au fost instalate în baza de date a phpBB-ului. Ca să instalaţi o temă apăsaţi pe legătura <i>Instalează</i> de lângă denumirea temei';

$lang['Select_template'] = 'Selectaţi un şablon';

$lang['Style'] = 'Stilul';
$lang['Template'] = 'Şablonul';
$lang['Install'] = 'Instalează';
$lang['Download'] = 'Descarcă';

$lang['Edit_theme'] = 'Modifică tema';
$lang['Edit_theme_explain'] = 'În formularul de mai jos puteţi modifica configurările pentru tema selectată';

$lang['Create_theme'] = 'Crează temă';
$lang['Create_theme_explain'] = 'Folosiţi formularul de mai jos ca să creaţi o temă nouă pentru un şablon selectat. Când introduceţi culori (pentru care trebuie să folosiţi notaţie hexazecimală) nu trebuie să includeţi iniţiala #, de exemplu CCCCCC este validă, #CCCCCC nu este validă';

$lang['Export_themes'] = 'Exportă teme';
$lang['Export_explain'] = 'În această secţiune puteţi exporta teme dintr-un şablon selectat. Selectaţi şablonul din lista de mai jos şi programul va crea un fişier de configurare a temei şi încercaţi să-l salvaţi în directorul şablonului selectat. Dacă fişierul nu poate fi salvat vi se va da posibilitatea să-l descărcaţi. Pentru ca programul să salveze fişierul trebuie să daţi drepturi de scriere pentru serverul web pe directorul şablonului selectat. Pentru mai multe informaţii consultaţi pagina 2 din ghidul utilizatorilor phpBB.';

$lang['Theme_installed'] = 'Tema selectată a fost instalată cu succes';
$lang['Style_removed'] = 'Stilul selectat a fost şters din baza de date. Pentru a şterge definitiv acest stil din sistem, trebuie să-l ştergeţi din directorul dumneavoastră cu şabloane.';
$lang['Theme_info_saved'] = 'Informaţiile temei pentru şablonul curent au fost salvate. Acum trebuie să specificaţi permisiunile în fişierul theme_info.cfg (şi dacă se poate directorul şablonului selectat) la acces doar de citire';
$lang['Theme_updated'] = 'Tema selectată a fost actualizată. Acum ar trebui să exportaţi setările temei noi';
$lang['Theme_created'] = 'Temă a fost creată. Acum ar trebui să exportaţi tema în fişierul de configurare al temei pentru păstrarea în siguranţă a acesteia sau s-o folosiţi altundeva';

$lang['Confirm_delete_style'] = 'Sunteţi sigur că doriţi să ştergeţi acest stil?';

$lang['Download_theme_cfg'] = 'Procedura de export nu poate scrie fişierul cu informaţiile temei. Apăsaţi butonul de mai jos ca să descărcaţi acest fişier. Odată ce l-aţi descărcat puteţi să-l transferaţi în directorul care conţine fişierele cu şabloane. Puteţi împacheta fişierele pentru distribuţie sau să le folosiţi unde doriţi';
$lang['No_themes'] = 'Şablonul pe care l-aţi selectat nu are teme ataşate. Ca să creaţi o temă nouă apăsaţi legătura <i>Crează temă</i> din partea stângă';
$lang['No_template_dir'] = 'Nu se poate deschide directorul cu şabloane. Acesta ori nu poate fi citit de către serverul web ori nu există';
$lang['Cannot_remove_style'] = 'Nu puteţi şterge stilul selectat în timp ce este acesta este stilul standard pentru forum. Schimbaţi stilul standard şi încercaţi din nou.';
$lang['Style_exists'] = 'Numele stilului pe care l-aţi selectat există deja, vă rugăm reveniţi şi alegeţi un nume diferit.';

$lang['Click_return_styleadmin'] = 'Apăsaţi %saici%s ca să reveniţi la secţiunea Administrarea stilurilor';

$lang['Theme_settings'] = 'Configurările temei';
$lang['Theme_element'] = 'Elementul temei';
$lang['Simple_name'] = 'Numele simplu';
$lang['Value'] = 'Valoarea';
$lang['Save_Settings'] = 'Salvează configurările';

$lang['Stylesheet'] = 'Stilul CSS';
$lang['Stylesheet_explain'] = 'Numele fişierului pentru stilul CSS folosit în această temă.';
$lang['Background_image'] = 'Imaginea fundalului';
$lang['Background_color'] = 'Culoarea fundalului';
$lang['Theme_name'] = 'Numele temei';
$lang['Link_color'] = 'Culoarea legăturii';
$lang['Text_color'] = 'Culoarea textului';
$lang['VLink_color'] = 'Culoarea legăturii vizitate';
$lang['ALink_color'] = 'Culoarea legăturii active';
$lang['HLink_color'] = 'Culoarea legăturii acoperite';
$lang['Tr_color1'] = 'Culoarea 1 a rândului din tabel';
$lang['Tr_color2'] = 'Culoarea 2 a rândului din tabel';
$lang['Tr_color3'] = 'Culoarea 3 a rândului din tabel';
$lang['Tr_class1'] = 'Clasa 1 a rândului din tabel';
$lang['Tr_class2'] = 'Clasa 2 a rândului din tabel';
$lang['Tr_class3'] = 'Clasa 3 a rândului din tabel';
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
$lang['fontface1'] = 'Fontul de faţă 1';
$lang['fontface2'] = 'Fontul de faţă 2';
$lang['fontface3'] = 'Fontul de faţă 3';
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
$lang['Welcome_install'] = 'Bine aţi venit la procedura de instalare a forumului phpBB2';
$lang['Initial_config'] = 'Configuraţia de bază';
$lang['DB_config'] = 'Configuraţia bazei de date';
$lang['Admin_config'] = 'Configuraţia administratorului';
$lang['continue_upgrade'] = 'Odată ce aţi descărcat fişierul dumneavoastră de configurare pe calculatorul local puteţi folosi butonul <i>Continuă actualizarea</i> de mai jos ca să treceţi la următorul pas din actualizare. Vă rugăm aşteptaţi să se încarce fişierul de configurare până ce actualizarea  este completă.';
$lang['upgrade_submit'] = 'Continuă actualizarea';

$lang['Installer_Error'] = 'O eroare a apărut în timpul instalării';
$lang['Previous_Install'] = 'O instalare anterioară a fost detectată';
$lang['Install_db_error'] = 'O eroare a apărut în timpul actualizării bazei de date';

$lang['Re_install'] = 'Instalarea anterioară este încă activă. <br /><br />Dacă doriţi să reinstalaţi phpBB2-ul ar trebui să apăsaţi pe butonul Da de mai jos. Vă rugăm să aveţi grijă ca să nu distrugeţi toate datele existente, nici o copie de siguranţă nu va fi făcută! Numele de utilizator şi parola administratorului pe care le-aţi folosit să vă autentificaţi în forum vor fi recreate după reinstalare, nici o altă setare nu va fi păstrată. <br /><br />Gândiţi-vă atent înainte de a apăsa butonul <i>Porneşte instalarea</i>!';

$lang['Inst_Step_0'] = 'Vă mulţumim că aţi ales phpBB2. Pentru a completa această instalare vă rugăm să completaţi detaliile de mai jos. Reţineţi că baza de date pe care o folosiţi trebuie să existe deja. Dacă instalaţi într-o bază de date care foloseşte ODBC, de exemplu MS Access ar trebui mai întâi să creaţi un DSN pentru aceasta înainte de a continua.';

$lang['Start_Install'] = 'Porneşte instalarea';
$lang['Finish_Install'] = 'Termină instalarea';

$lang['Default_lang'] = 'Limba standard pentru forum';
$lang['DB_Host'] = 'Numele serverului gazdă pentru baza de date / DSN';
$lang['DB_Name'] = 'Numele bazei dumneavoastră de date';
$lang['DB_Username'] = 'Numele de utilizator al bazei de date';
$lang['DB_Password'] = 'Parola de utilizator al bazei de date';
$lang['Database'] = 'Baza dumneavoastră de date';
$lang['Install_lang'] = 'Alegeţi limba pentru instalare';
$lang['dbms'] = 'Tipul bazei de date';
$lang['Table_Prefix'] = 'Prefixul pentru tabelele din baza de date';
$lang['Admin_Username'] = 'Numele de utilizator al administratorului';
$lang['Admin_Password'] = 'Parola administratorului';
$lang['Admin_Password_confirm'] = 'Parola administratorului [ Confirmaţi ]';

$lang['Inst_Step_2'] = 'Numele de utilizator pentru administrator a fost creat. Acum instalarea de bază este completă. Va apărea un ecran care vă va permite să administraţi noua dumneavoastră instalare. Asiguraţi-vă că aţi verificat detaliile secţiunii Configurare generală şi aţi efectuat orice schimbare necesară. Vă mulţumim că aţi ales phpBB2.';

$lang['Unwriteable_config'] = 'Fişierul dumneavoastră de configurare în acest moment este protejat la scriere. O copie a fişierului de configurare va fi descărcată când apăsaţi butonul de mai jos. At trebui să încărcaţi acest fişier în acelaşi director ca şi phpBB2. Odată ce această operaţiune este terminată ar trebui să vă autentificaţi folosind numele de utilizator şi parola administratorului pe care le-aţi specificat în formularul anterior şi să vizitaţi centrul de control al administratorului (o legătură va apărea la capătul fiecărei pagini odată ce v-aţi autentificat) ca să verificaţi configuraţia generală. Vă mulţumim că aţi ales phpBB2.';
$lang['Download_config'] = 'Descarcă fişierul de configurare';

$lang['ftp_choose'] = 'Alegeţi metoda de descărcare';
$lang['ftp_option'] = '<br />Întrucât extensiile FTP sunt activate în această versiune a PHP-ului, aveţi posibilitatea de a încerca să plasaţi prin ftp fişierul de configurare la locul lui.';
$lang['ftp_instructs'] = 'Aţi ales să transmiteţi fişierul automat prin ftp în contul care conţine phpBB2-ul. Vă rugăm introduceţi informaţiile cerute mai jos ca să facilitaţi aceast proces. Calea unde este situat FTP-ul trebuie să fie calea exactă via ftp la instalarea phpBB2-ului dumneavoastră ca şi cum aţi transmite folosind un client normal de ftp.';
$lang['ftp_info'] = 'Introduceţi informaţiile dumneavoastră despre FTP';
$lang['Attempt_ftp'] = 'Încercare de a transfera la locul specificat fişierul de configurare prin ftp';
$lang['Send_file'] = 'Trimite doar fişierul la mine şi eu voi îl voi trimite manual prin ftp';
$lang['ftp_path'] = 'Calea FTP la phpBB2';
$lang['ftp_username'] = 'Numele dumneavoastră de utilizator pentru FTP';
$lang['ftp_password'] = 'Parola dumneavoastră de utilizator pentru FTP';
$lang['Transfer_config'] = 'Porneşte transferul';
$lang['NoFTP_config'] = 'Încercarea de a transfera la locul specificat fişierul de configurare prin ftp a eşuat. Vă rugăm să descărcaţi fişierul de configurare şi să-l transmiteţi manual prin ftp la locul specificat.';

$lang['Install'] = 'Instalează';
$lang['Upgrade'] = 'Actualizează';


$lang['Install_Method'] = 'Alegeţi metoda de instalare';

$lang['Install_No_Ext'] = 'Configurarea php-ului pe serverul dumneavoastră nu suportă tipul de bază de date pe care l-aţi ales';

$lang['Install_No_PCRE'] = 'phpBB2 necesită modulul de expresii regulate compatibil Perl pentru php pe care configuraţia dumneavoastră de php se pare că nu-l suportă!';

//
// Version Check
//
$lang['Version_up_to_date'] = 'Forumul dumneavoastră foloseşte ultima versiune phpBB. Nu sunt actualizări disponibile pentru versiunea dumneavoastră.';
$lang['Version_not_up_to_date'] = 'Forumul dumneavoastră pare să <b>nu</b> fie actualizat. Noile versiuni sunt disponibile la adresa <a href="http://www.phpbb.com/downloads.php" target="_new">http://www.phpbb.com/downloads.php</a>.';
$lang['Latest_version_info'] = 'Cea mai nouă versiune este <b>phpBB %s</b>.';
$lang['Current_version_info'] = 'Folosiţi <b>phpBB %s</b>.';
$lang['Connect_socket_error'] = 'Nu am putut deschide conexiunea cu serverul phpBB, eroarea raportată este:<br />%s';
$lang['Socket_functions_disabled'] = 'Nu am putut folosi funcţiile socket.';
$lang['Mailing_list_subscribe_reminder'] = 'Pentru cele mai noi informaţii despre phpBB, <a href="http://www.phpbb.com/support/" target="_new">vă puteţi înscrie la serviciul de ştiri</a>.';
$lang['Version_information'] = 'Informaţii despre versiuni';

//
// Login attempts configuration
//
$lang['Max_login_attempts'] = 'Permite încercări de autentificare';
$lang['Max_login_attempts_explain'] = 'Numărul de încercări de autentificare permise.';
$lang['Login_reset_time'] = 'Timpul necesar reautentificării';
$lang['Login_reset_time_explain'] = 'Numărul de minute pe care un user trebuie să-l aştepte pentru a i se permite să se autentifice din nou, după depăşirea numărului de încercări de autentificare permise.';

// Start add - Bin Mod
$lang['Bin_forum'] = 'Bin forum';
$lang['Bin_forum_explain'] = 'Fill with the forum ID where topics moved to bin, a value of 0 will disable this feature. You should edit this forum permissions to allow or not view/post/reply... by users or forbid access to this forum.';
// End add - Bin Mod

$lang['Draft_allow']='Allow users to make their posts a draft';

//
// That's all Folks!
// -------------------------------------------------

?>
