<?php

/***************************************************************************
 *                            lang_admin.php [românã cu diacritice]
 *                              -------------------
 *     begin                : Sat Sep 7 2002
 *     copyright 1          : (C) Daniel Tãnasie
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
$lang['LEFT'] = 'stânga';
$lang['RIGHT'] = 'dreapta';
$lang['DATE_FORMAT'] =  'd M Y'; // This should be changed to the default date format for your language, php date() format

$lang['General'] = 'Administrare generalã';
$lang['Users'] = 'Administrare utilizatori';
$lang['Groups'] = 'Administrare grupuri';
$lang['Forums'] = 'Administrare forumuri';
$lang['Styles'] = 'Administrare stiluri';

$lang['Configuration'] = 'Configurare generalã';
$lang['Permissions'] = 'Permisiuni';
$lang['Manage'] = 'Management';
$lang['Disallow'] = 'Dezactivare nume';
$lang['Prune'] = 'Curãþire';
$lang['Mass_Email'] = 'Expediere mesaje în bloc';
$lang['Ranks'] = 'Ranguri';
$lang['Smilies'] = 'Zâmbete';
$lang['Ban_Management'] = 'Control restricþii';
$lang['Word_Censor'] = 'Cuvinte cenzurate';
$lang['Export'] = 'Exportã';
$lang['Create_new'] = 'Creeazã';
$lang['Add_new'] = 'Adaugã';
$lang['Backup_DB'] = 'Salveazã baza de date';
$lang['Restore_DB'] = 'Restaureazã baza de date';



//
// Index
//
$lang['Admin'] = 'Administrare';
$lang['Not_admin'] = 'Nu sunteþi autorizat sã administraþi acest forum';
$lang['Welcome_phpBB'] = 'Bine aþi venit la centrul de control al forumului phpBB';
$lang['Admin_intro'] = 'Vã mulþumim pentru cã aþi ales phpBB ca soluþie pentru forumul dumneavoastrã. Acest ecran vã oferã o privire de ansamblu a diverselor statistici ale forumului dumneavoastrã. Puteþi reveni la aceastã paginã folosind legãtura <i>Pagina de start a administratorului</i> din partea stângã. Pentru a reveni la pagina de start a forumului dumneavoastrã, apãsaþi pe logo-ul phpBB-ului aflat, de asemenea, în partea stângã. Celelalte legãturi din partea stângã vã permit sã controlaþi orice aspect al forumului, fiecare ecran va avea instrucþiuni care dau explicaþii despre cum se folosesc instrumentele.';
$lang['Main_index'] = 'Pagina de start a forumului';
$lang['Forum_stats'] = 'Statisticile forumului';
$lang['Admin_Index'] = 'Pagina de start a administratorului';
$lang['Preview_forum'] = 'Previzualizare forum';

$lang['Click_return_admin_index'] = 'Apãsaþi %saici%s pentru a reveni la secþiunea Pagina de start a administratorului';

$lang['Statistic'] = 'Statistica';
$lang['Value'] = 'Valoarea';
$lang['Number_posts'] = 'Numãrul mesajelor scrise';
$lang['Posts_per_day'] = 'Mesaje scrise pe zi';
$lang['Number_topics'] = 'Numãrul subiectelor';
$lang['Topics_per_day'] = 'Subiecte pe zi';
$lang['Number_users'] = 'Numãrul utilizatorilor';
$lang['Users_per_day'] = 'Utilizatori pe zi';
$lang['Board_started'] = 'Data pornirii forumului';
$lang['Avatar_dir_size'] = 'Dimensiunea directorului cu imagini asociate (Avatar)';
$lang['Database_size'] = 'Dimensiunea bazei de date';
$lang['Gzip_compression'] ='Compresia Gzip';
$lang['Not_available'] = 'Nu este disponibil(ã)';

$lang['ON'] = 'Activã'; // This is for GZip compression
$lang['OFF'] = 'Inactivã';


//
// DB Utils
//
$lang['Database_Utilities'] = 'Instrumentele bazei de date';

$lang['Restore'] = 'Restaurare';
$lang['Backup'] = 'Salvare(Backup)';
$lang['Restore_explain'] = 'Aceasta va efectua o resturare completã a tuturor tabelelor phpBB dintr-in fiºier salvat. Dacã serverul dumneavoastrã suportã, puteþi publica un fiºier text compresat cu gzip ºi aceasta va fi decomprimat automat. <b>ATENÞIE:</b> Aceastã procedurã va rescrie orice informaþie deja existentã. Procesul de restaurare poate dura un timp îndelungat; vã rugãm nu pãrãsiþi aceastã paginã pânã când restaurarea nu se terminã.';
$lang['Backup_explain'] = 'Aici puteþi face copii de rezervã ale tuturor datelor ce þin de phpBB. Dacã aveþi ºi tabele adiþionale în aceeaºi bazã de date cu phpBB-ul pe care doriþi sã le pãstraþi, vã rugãm sã introduceþi numele lor separate prin virgulã în cãsuþa <i>Tabele Suplimentare</i> de mai jos. Dacã serverul dumneavoastrã suportã, puteþi comprima fiºierul cu gzip pentru a reduce dimensiunea sa înainte de a efectua operaþiunea de descãrcare.';

$lang['Backup_options'] = 'Opþiunile de backup';
$lang['Start_backup'] = 'Porneºte operaþiunea de backup';
$lang['Full_backup'] = 'Backup total';
$lang['Structure_backup'] = 'Salveazã (copie de siguranþã) doar structura';
$lang['Data_backup'] = 'Salveazã (copie de siguranþã) doar datele';
$lang['Additional_tables'] = 'Tabele suplimentare';
$lang['Gzip_compress'] = 'Fiºier comprimat cu Gzip';
$lang['Select_file'] = 'Selectaþi un fiºier';
$lang['Start_Restore'] = 'Porneºte operaþiunea de restaurare';

$lang['Restore_success'] = 'Baza de date a fost restauratã cu succes.<br /><br />Forumul dumneavoastrã ar trebui sã revinã la starea lui înainte ca salvarea sã se fi realizat.';
$lang['Backup_download'] = 'Operaþiunea de descãrcare va începe în curând; vã rugãm sã aºteptaþi pânã aceasta va începe';
$lang['Backups_not_supported'] = 'Scuze, dar efectuarea backup-ului nu este în prezent realizabilã pentru sistemul dumneavoastrã de baze de date';

$lang['Restore_Error_uploading'] = 'Eroare la publicarea fiºierului de backup';
$lang['Restore_Error_filename'] = 'Problemã cu numele fiºierului; vã rugãm, încercaþi cu un alt fiºier';
$lang['Restore_Error_decompress'] = 'Nu pot decomprima un fiºier gzip; vã rugãm, publicaþi o versiune text întreg (plain text)';
$lang['Restore_Error_no_file'] = 'Nici un fiºier nu a fost publicat/încãrcat';


//
// Auth pages
//
$lang['Select_a_User'] = 'Selectaþi un utilizator';
$lang['Select_a_Group'] = 'Selectaþi un grup';
$lang['Select_a_Forum'] = 'Selectaþi un forum';
$lang['Auth_Control_User'] = 'Controlul permisiunilor utilizatorului';
$lang['Auth_Control_Group'] = 'Controlul permisiunilor grupului';
$lang['Auth_Control_Forum'] = 'Controlul permisiunilor forumului';
$lang['Look_up_User'] = 'Selecteazã utilizatorul';
$lang['Look_up_Group'] = 'Selecteazã grupul';
$lang['Look_up_Forum'] = 'Selecteazã forumul';

$lang['Group_auth_explain'] = 'Aici puteþi modifica permisiunile ºi starea moderatorului asociat la fiecare grup de utilizatori. Nu uitaþi când schimbaþi permisiunile grupului cã permisiunile individuale ale utilizatorului pot sã permitã accesul utilizatorului la forumuri, etc. Veþi fi atenþionat dacã va apãrea aceastã situaþie.';
$lang['User_auth_explain'] = 'Aici puteþi modifica permisiunile ºi starea moderatorului asociat la fiecare utilizator individual. Nu uitaþi când schimbaþi permisiunile utilizatorului cã permisiunile individuale ale grupului pot sã permitã accesul utilizatorului la forumuri, etc. Veþi fi atenþionat dacã va apãrea aceastã situaþie.';
$lang['Forum_auth_explain'] = 'Aici puteþi modifica nivelele de autorizare ale fiecãrui forum. Pentru a realiza acest lucru aveþi la dispoziþie atât o metodã simplã cât ºi una avansatã, metoda avansatã oferind un control mai mare al fiecãriei operaþii din forum. Amintiþi-vã cã schimbarea nivelului de permisiuni ale forumurilor va afecta modul de realizare(finalizare) al diverselor operaþiuni solicitate de cãtre utilizatori.';

$lang['Simple_mode'] = 'Modul simplu';
$lang['Advanced_mode'] = 'Modul avansat';
$lang['Moderator_status'] = 'Starea moderatorului';

$lang['Allowed_Access'] = 'Acces permis';
$lang['Disallowed_Access'] = 'Acces interzis';
$lang['Is_Moderator'] = 'este moderator';
$lang['Not_Moderator'] = 'nu este moderator';

$lang['Conflict_warning'] = 'Avertizare - Conflict de autorizare';
$lang['Conflict_access_userauth'] = 'Acest utilizator are încã drepturi de acces la acest forum datorate apartenenþei acestuia la grup. Puteþi sã modificaþi permisiunile grupului sau sã înlãturaþi acest utilizator din grup pentru a nu mai avea depturi de acces. Grupurile care dau drepturi (ºi forumurile implicate) sunt afiºate mai jos.';
$lang['Conflict_mod_userauth'] = 'Acest utilizator are încã drepturi de moderator la acest forum datorate apartenenþei acestuia la grup. Puteþi sã modificaþi permisiunile grupului sau sã înlãturaþi acest utilizator din grup pentru a nu mai avea depturi de moderator. Grupurile care dau drepturi (ºi forumurile implicate) sunt afiºate mai jos.';
$lang['Conflict_access_groupauth'] = 'Utilizatorul(i) urmãtor(i) are(au) încã drepturi de acces la acest forum datorate setãrilor lui(lor) de permisiuni. Puteþi sã modificaþi permisiunile utilizatorului pentru a nu mai avea drepturi de acces. Utilizatorii care dau drepturi (ºi forumurile implicate) sunt afiºaþi mai jos.';
$lang['Conflict_mod_groupauth'] = 'Utilizatorul(i) urmãtor(i) are(au) încã drepturi de acces la acest forum datorate setãrilor lui(lor) de permisiuni. Puteþi sã modificaþi permisiunile utilizatorului pentru a nu mai avea drepturi de moderator. Utilizatorii care dau drepturi (ºi forumurile implicate) sunt afiºaþi mai jos.';

$lang['Public'] = 'Public';
$lang['Private'] = 'Privat';
$lang['Registered'] = 'Înregistrat';
$lang['Administrators'] = 'Administratori';
$lang['Hidden'] = 'Ascuns';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = 'TOÞI';
$lang['Forum_REG'] = 'ÎNREG';
$lang['Forum_PRIVATE'] = 'PRIVAT';
$lang['Forum_MOD'] = 'MOD';
$lang['Forum_ADMIN'] = 'ADMIN';

$lang['View'] = 'Vizualizare';
$lang['Read'] = 'Citire';
$lang['Post'] = 'Scriere';
$lang['Reply'] = 'Rãspunde';
$lang['Edit'] = 'Modificã';
$lang['Delete'] = 'ªterge';
$lang['Sticky'] = 'Lipicios (Sticky)';
$lang['Announce'] = 'Anunþ';
$lang['Vote'] = 'Vot';
$lang['Pollcreate'] = 'Creare sondaj';

$lang['Permissions'] = 'Permisiuni';
$lang['Simple_Permission'] = 'Premisiune simplã';

$lang['User_Level'] = 'Nivelul utilizatorului';
$lang['Auth_User'] = 'Utilizator';
$lang['Auth_Admin'] = 'Administrator';
$lang['Group_memberships'] = 'Membru al grupurilor';
$lang['Usergroup_members'] = 'Acest grup conþine urmãtorii membrii';

$lang['Forum_auth_updated'] = 'Permisiunile forumului au fost actualizate';
$lang['User_auth_updated'] = 'Permisiunile utilizatorului au fost actualizate';
$lang['Group_auth_updated'] = 'Permisiunile grupului au fost actualizate';

$lang['Auth_updated'] = 'Permisiunile au fost actualizate';
$lang['Click_return_userauth'] = 'Apãsaþi %saici%s pentru a reveni la secþiunea Controlul permisiunilor utilizatorului';
$lang['Click_return_groupauth'] = 'Apãsaþi %saici%s pentru a reveni la secþiunea Controlul permisiunilor grupului';
$lang['Click_return_forumauth'] = 'Apãsaþi %saici%s pentru a reveni la secþiunea Controlul permisiunilor forumului';


//
// Banning
//
$lang['Ban_control'] = 'Controlul interdicþiilor';
$lang['Ban_explain'] = 'Aici puteþi sã controlaþi interdicþiile utilizatorilor. Puteþi obþine acest lucru interzicând una sau mai multe din elementele caracteristice unui utilizator: denumire utilizator, mulþimea adreselor IP sau numele host-urilor. Aceste metode împiedicã un utilizator sã nu ajungã în pagina de început a forumului. Pentru a împiedica un utilizator sã se înregistreze sub un alt nume de utilizator puteþi specifica o adresã de mail interzisã. Reþineþi cã o singurã adresã de mail interzisã nu-l va împiedeca pe utilizatorul în cauzã sã intre sau sã scrie în forumul dumneavoastrã; ar trebui sã folosiþi prima din cele douã metode.';
$lang['Ban_explain_warn'] = 'Reþineþi cã introducerea unei mulþimi de adrese IP înseamnã cã toate adresele dintre începutul ºi sfârºitul mulþimii au fost adãugate la lista interzisã. Pentru a reduce numãrul de adrese adãugate la baza de date se pot folosi <i>wildcard</i>-urile unde este cazul. Dacã chiar trebuie sã introduceþi o plajã de valori, încercaþi sã o pãstraþi cât mai micã sau mai bine reþineþi doar adresele specifice.';

$lang['Select_username'] = 'Selectaþi un nume de utilizator';
$lang['Select_ip'] = 'Selectaþi un IP';
$lang['Select_email'] = 'Selectaþi o adresã de email';

$lang['Ban_username'] = 'Interziceþi unul sau mai mulþi utilizatori';
$lang['Ban_username_explain'] = 'Puteþi interzice mai mulþi utilizatori într-un singur pas folosind combinaþii potrivite ale mouse-ului (în browser) ºi tastaturii calculatorului dumneavoastrã';

$lang['Ban_IP'] = 'Interziceþi una sau mai multe adrese IP sau nume de host-uri';
$lang['IP_hostname'] = 'Adrese IP sau nume de host-uri';
$lang['Ban_IP_explain'] = 'Pentru a specifica mai multe IP-uri diferite sau nume de host-uri trebuie sã le separaþi prin virgulã. Pentru a specifica o mulþime de adrese IP, separaþi începutul ºi sfârºitul mulþimii cu o liniuþã de unire (-); ca sã specificaþi caracterul <i>wildcard</i> folosiþi *';

$lang['Ban_email'] = 'Interziceþi una sau mai multe adrese de email';
$lang['Ban_email_explain'] = 'Pentru a specifica mai multe adrese de email folosiþi separatorul virgulã. Ca sã specificaþi un utilizator cu ajutorul <i>wildcard</i>-ului folosiþi *, de exemplu *@hotmail.com';

$lang['Unban_username'] = 'Deblocarea utilizatorilor';
$lang['Unban_username_explain'] = 'Puteþi sã deblocaþi mai mulþi utilizatori într-un singur pas folosind combinaþii potrivite ale mouse-ului (în browser) ºi tastaturii calculatorului dumneavoastrã';

$lang['Unban_IP'] = 'Deblocarea adreselor IP';
$lang['Unban_IP_explain'] = 'Puteþi sã deblocaþi mai multe adrese IP într-un singur pas folosind combinaþii potrivite ale mouse-ului (în browser) ºi tastaturii calculatorului dumneavoastrã';

$lang['Unban_email'] = 'Deblocarea adreselor email';
$lang['Unban_email_explain'] = 'Puteþi sã deblocaþi mai multe adrese email într-un singur pas folosind combinaþii potrivite ale mouse-ului (în browser) ºi tastaturii calculatorului dumneavoastrã';

$lang['No_banned_users'] = 'Nu este nici un utilizator interzis';
$lang['No_banned_ip'] = 'Nu este nici o adresã IP interzisã';
$lang['No_banned_email'] = 'Nu este nici o adresã de email interzisã';

$lang['Ban_update_sucessful'] = 'Lista restricþiilor a fost actualizatã cu succes';
$lang['Click_return_banadmin'] = 'Apãsaþi %saici%s pentru a reveni la secþiunea Control Restricþii';


//
// Configuration
//
$lang['General_Config'] = 'Configurare generalã';
$lang['Config_explain'] = 'Formularul de mai jos vã permite sã personalizaþi toate opþiunile generale ale forumului. Pentru configurarea utilizatorilor ºi forumurilor folosiþi legãturile specifice aflate în partea stângã.';

$lang['Click_return_config'] = 'Apãsaþi %saici%s pentru a reveni la secþiunea Configurare generalã';

$lang['General_settings'] = 'Setãrile generale ale forumului';
$lang['Server_name'] = 'Numele domeniului';
$lang['Server_name_explain'] = 'Numele domeniului acestui forum ruleazã din';
$lang['Script_path'] = 'Calea script-ului';
$lang['Script_path_explain'] = 'Calea unde phpBB2 este localizat relativ la numele domeniului';
$lang['Server_port'] = 'Port-ul serverului';
$lang['Server_port_explain'] = 'Port-ul pe care serverul dumneavoastrã ruleazã este de obicei 80 (numai dacã nu a fost schimbat)';
$lang['Site_name'] = 'Numele site-ului';
$lang['Site_desc'] = 'Descrierea site-ului';
$lang['Board_disable'] = 'Forum dezactivat';
$lang['Board_disable_explain'] = 'Aceastã acþiune va face forumul indisponibil utilizatorilor. Nu închideþi sesiunea curentã când dezactivaþi forumul, altfel nu veþi mai fi capabil sã vã autentificaþi din nou!';
$lang['Acct_activation'] = 'Validarea contului activatã de';
$lang['Acc_None'] = 'Nimeni'; // These three entries are the type of activation
$lang['Acc_User'] = 'Utilizator';
$lang['Acc_Admin'] = 'Administrator';

$lang['Abilities_settings'] = 'Configurãrile de bazã ale utilizatorilor ºi forumurilor';
$lang['Max_poll_options'] = 'Numãrul maxim al opþiunilor chestionarului';
$lang['Flood_Interval'] = 'Interval de flood';
$lang['Flood_Interval_explain'] = 'Numãrul de secunde pe care un utilzator trebuie sã-l aºtepte între publicãri';
$lang['Board_email_form'] = 'Trimite mesaj la utilizator via forum';
$lang['Board_email_form_explain'] = 'Utilizatorii pot trimit mesaje unii la alþi prin acest forum';
$lang['Topics_per_page'] = 'Subiecte pe paginã';
$lang['Posts_per_page'] = 'Mesaje pe paginã';
$lang['Hot_threshold'] = 'Mesaje pentru statutul popular';
$lang['Default_style'] = 'Stilul standard';
$lang['Override_style'] = 'Suprascrie stilul utilizatorului';
$lang['Override_style_explain'] = 'Înlocuirea sitului utilizatorilor cu cel standard';
$lang['Default_language'] = 'Limba standard';
$lang['Date_format'] = 'Formatul datei';
$lang['System_timezone'] = 'Timpul zonal al sistemului';
$lang['Enable_gzip'] = 'Activare compresie GZip';
$lang['Enable_prune'] = 'Activare curãþire forum';
$lang['Allow_HTML'] = 'Permite HTML';
$lang['Allow_BBCode'] = 'Permite cod BB';
$lang['Allowed_tags'] = 'Permite balize (tag-uri) HTML';
$lang['Allowed_tags_explain'] = 'Separã balizele (tag-urile) cu virgule';
$lang['Allow_smilies'] = 'Permite zâmbete';
$lang['Smilies_path'] = 'Calea unde se pãstreazã zâmbetele';
$lang['Smilies_path_explain'] = 'Calea aflatã în directorul dumneavoastrã phpBB , de exemplu. imagini/zâmbete';
$lang['Allow_sig'] = 'Permite semnãturi';
$lang['Max_sig_length'] = 'Lungimea maximã a semnãturii';
$lang['Max_sig_length_explain'] = 'Numãrul maxim de caractere aflate în semnãtura utilizatorului';
$lang['Allow_name_change'] = 'Permite schimbarea numelui de utilizator';

$lang['Avatar_settings'] = 'Configurãri pentru imagini asociate (Avatar)';
$lang['Allow_local'] = 'Permite galerie de imagini asociate';
$lang['Allow_remote'] = 'Permite imagini asociate la distanþã';
$lang['Allow_remote_explain'] = 'Imaginile asociate sunt specificate cu o legãturã la alt site web';
$lang['Allow_upload'] = 'Permite încãrcarea imaginii asociate';
$lang['Max_filesize'] = 'Dimensiunea maximã a fiºierului ce conþine imaginea asociatã';
$lang['Max_filesize_explain'] = 'Pentru fiºierele ce conþin imaginile asociate încãrcate';
$lang['Max_avatar_size'] = 'Dimensiunea maximã a imaginii asociate';
$lang['Max_avatar_size_explain'] = '(Înãlþime x Lãþime în pixeli)';
$lang['Avatar_storage_path'] = 'Calea de pãstrare a imaginilor asociate';
$lang['Avatar_storage_path_explain'] = 'Calea aflatã în directorul dumneavoastrã phpBB, de exemplu. imagini/avatar';
$lang['Avatar_gallery_path'] = 'Calea de pãstrare a galeriilor cu imagini asociate';
$lang['Avatar_gallery_path_explain'] = 'Calea aflatã în directorul dumneavoastrã phpBB, de exemplu. imagini/avatar/galerie';

$lang['COPPA_settings'] = 'Configurãrile COPPA';
$lang['COPPA_fax'] = 'Numãrul de fax Fax Number';
$lang['COPPA_mail'] = 'Adresa poºtalã COPPA';
$lang['COPPA_mail_explain'] = 'Aceasta este adresa poºtalã unde pãrinþii vor trimite formularele de înregistrare COPPA';

$lang['Email_settings'] = 'Configurãrile de email';
$lang['Admin_email'] = 'Adresa de email a administratorului';
$lang['Email_sig'] = 'Semnãtura din email';
$lang['Email_sig_explain'] = 'Acest text va fi ataºat la toate mesajele pe care forumul le trimite';
$lang['Use_SMTP'] = 'Folosiþi serverul SMTP Server pentru email';
$lang['Use_SMTP_explain'] = 'Specificaþi da dacã doriþi sau aveþi nevoie sã trimiteþi mesaje printr-un alt server în loc sã folosiþi funcþia localã de mesagerie';
$lang['SMTP_server'] = 'Adresa serverului SMTP';
$lang['SMTP_username'] = 'Numele de utilizator SMTP';
$lang['SMTP_username_explain'] = 'Introduceþi numele de utilizator doar dacã serverul dumneavoastrã SMTP necesitã aceastã specificare';
$lang['SMTP_password'] = 'Parola SMTP';
$lang['SMTP_password_explain'] = 'Introduceþi parola doar dacã serverul dumneavoastrã SMTP necesitã aceastã specificare';

$lang['Disable_privmsg'] = 'Mesagerie privatã';
$lang['Inbox_limits'] = 'Numãrul maxim al mesajelor în Dosarul cu mesaje (Inbox)';
$lang['Sentbox_limits'] = 'Numãrul maxim al mesajelor în Dosarul cu mesaje trimise (Sentbox)';
$lang['Savebox_limits'] = 'Numãrul maxim al mesajelor în Dosarul cu mesaje salvate (Savebox)';

$lang['Cookie_settings'] = 'Configurãrile pentru cookie';
$lang['Cookie_settings_explain'] = 'Aceste detalii definesc cum sunt cookie-urile trimise cãtre browser-ele utilizatorilor. În cele mai multe cazuri valorile standard pentru setãrile cookie-urilor ar trebui sã fie suficiente dar dacã trebuie sã le schimbaþi aveþi mare grijã, setãrile incorecte pot împiedica utilizatorii sã se autentifice';
$lang['Cookie_domain'] = 'Domeniul pentru cookie';
$lang['Cookie_name'] = 'Numele pentru cookie';
$lang['Cookie_path'] = 'Calea pentru cookie';
$lang['Cookie_secure'] = 'Securizare cookie';
$lang['Cookie_secure_explain'] = 'Dacã serverul dumneavoastrã ruleazã via SSL, selectaþi <i>Activat</i> altfel selectaþi <i>Dezactivat</i>';
$lang['Session_length'] = 'Durata sesiunii [ secunde ]';


//
// Forum Management
//
$lang['Forum_admin'] = 'Administrare forumuri';
$lang['Forum_admin_explain'] = 'În aceastã secþiune puteþi adãuga, ºterge, modifica, reordona ºi resincroniza categoriile ºi forumurile.';
$lang['Edit_forum'] = 'Modificare forum';
$lang['Create_forum'] = 'Creazã un forum nou';
$lang['Create_category'] = 'Creazã o categorie nouã';
$lang['Remove'] = 'ªterge';
$lang['Action'] = 'Acþiune';
$lang['Update_order'] = 'Actualizeazã ordinea';
$lang['Config_updated'] = 'Configurãrile la forum au fost actualizate cu succes';
$lang['Edit'] = 'Modificã';
$lang['Delete'] = 'ªterge';
$lang['Move_up'] = 'Mutã mai sus';
$lang['Move_down'] = 'Mutã mai jos';
$lang['Resync'] = 'Resincronizare';
$lang['No_mode'] = 'Nici un mod nu a fost specificat';
$lang['Forum_edit_delete_explain'] = 'Formularul de mai jos vã permite sã personalizaþi toate opþiunile generale ale forumului. Pentru configurarea utilizatorilor ºi forumurilor folosiþi legãturile specifice aflate în partea stângã.';

$lang['Move_contents'] = 'Mutã tot conþinutul';
$lang['Forum_delete'] = 'ªtergere forum';
$lang['Forum_delete_explain'] = 'Formularul de mai jos vã permite sã ºtergeþi un forum (sau o categorie) ºi sã decideþi unde doriþi sã plasaþi toate subiectele (sau forumurile) pe care le conþine.';

$lang['Forum_settings'] = 'Configurãrile generale ale forumului';
$lang['Forum_name'] = 'Numele forumului';
$lang['Forum_desc'] = 'Descriere';
$lang['Forum_status'] = 'Starea forumului';
$lang['Forum_pruning'] = 'Autocurãþare';

$lang['prune_freq'] = 'Verificã vârsta subiectelor la fiecare';
$lang['prune_days'] = 'ªterge subiectele la care nu s-au scris rãspunsuri de';
$lang['Set_prune_data'] = 'Aþi selectat opþiunea autocurãþire pentru acest forum dar nu aþi specificat o frecvenþã sau un numãr de zile al intervalului pentru acest proces. Vã rugãm reveniþi ºi specificaþi aceste valori';

$lang['Move_and_Delete'] = 'Mutã ºi ºterge';

$lang['Delete_all_posts'] = 'ªterge toate mesajele';
$lang['Nowhere_to_move'] = 'Nu muta mesajele';

$lang['Edit_Category'] = 'Modificare categorie';
$lang['Edit_Category_explain'] = 'Puteþi folosi acest forumlar pentru a modifica numele categoriilor.';

$lang['Forums_updated'] = 'Informaþiile despre forumuri ºi categorii au fost actualizate cu succes';

$lang['Must_delete_forums'] = 'Trebuie sã ºtergeþi toate forumurile înainte ca sã ºtergeþi aceastã categorie';

$lang['Click_return_forumadmin'] = 'Apãsaþi %saici%s pentru a reveni la secþiunea Administrare forumuri';


//
// Smiley Management
//
$lang['smiley_title'] = 'Administrare zâmbete';
$lang['smile_desc'] = 'Din aceastã paginã puteþi adãuga, ºterge ºi modifica zâmbetele sau emoþiile asociate pe care utilizatorii dumneavoastrã le pot folosi când scriu mesaje sau când trimit mesaje private.';

$lang['smiley_config'] = 'Configurare zâmbete';
$lang['smiley_code'] = 'Cod zâmbet';
$lang['smiley_url'] = 'Fiºierul imagine al zâmbetului';
$lang['smiley_emot'] = 'Emoþia asociatã';
$lang['smile_add'] = 'Adãugaþi un zâmbet nou';
$lang['Smile'] = 'Zâmbet';
$lang['Emotion'] = 'Emoþia asociatã';

$lang['Select_pak'] = 'Selectaþi un fiºier de tip Pack (.pak)';
$lang['replace_existing'] = 'Înlocuiþi zâmbetele existente';
$lang['keep_existing'] = 'Pãstraþi zâmbetele existente';
$lang['smiley_import_inst'] = 'Ar trebui sã decomprimaþi pachetul cu iconiþe ºi sã încãrcaþi toate fiºierele în directorul cu zâmbete specificat la instalare. Apoi selectaþi informaþiile corecte în acest formular ca sã importaþi pachetul cu zâmbete.';
$lang['smiley_import'] = 'Importaþi zâmbetele';
$lang['choose_smile_pak'] = 'Selectaþi un fiºier pachet cu zâmbete de tip .pak';
$lang['import'] = 'Importaþi zâmbete';
$lang['smile_conflicts'] = 'Ce ar trebui sã fie fãcut în caz de conflicte';
$lang['del_existing_smileys'] = 'ªtergeþi zâmbetele existente înainte de import';
$lang['import_smile_pack'] = 'Importaþi pachetul cu zâmbete';
$lang['export_smile_pack'] = 'Creaþi pachetul cu zâmbete';
$lang['export_smiles'] = 'Ca sã creaþi un pachet cu zâmbete din zâmbetele instalate, apãsaþi %saici%s ca sã descãrcaþi fiºierul cu zâmbete .pak. Numiþi acest fiºier cum doriþi dar asiguraþi-vã cã aþi pãstrat fiºierului extensia .pak. Apoi creaþi un fieºier arhivat conþinând toate imaginile zâmbete ale dumneavoastrã plus acest fiºier .pak.';

$lang['smiley_add_success'] = 'Zâmbetul a fost adãugat cu succes';
$lang['smiley_edit_success'] = 'Zâmbetul a fost actualizat cu succes';
$lang['smiley_import_success'] = 'Pachetul cu zâmbete a fost importat cu succes!';
$lang['smiley_del_success'] = 'Zâmbetul a fost ºters cu succes';
$lang['Click_return_smileadmin'] = 'Apãsaþi %saici%s pentru a reveni la secþiunea Administrare zâmbete';


//
// User Management
//
$lang['User_admin'] = 'Administrare utilizatori';
$lang['User_admin_explain'] = 'Aici puteþi schimba informaþiile despre utilizatorii dumneavoastrã ºi opþiunile specifice. Ca sã modificaþi drepturile utilizatorilor, folosiþi drepturile din sistem ale utilizatorilor ºi grupurilor.';

$lang['Look_up_user'] = 'Selecteazã utilizatorul';

$lang['Admin_user_fail'] = 'Nu se poate actualiza profilul utilizatorului.';
$lang['Admin_user_updated'] = 'Profilul utilizatorului a fost actualizat cu succes.';
$lang['Click_return_useradmin'] = 'Apãsaþi %saici%s pentru a reveni la secþiunea Administrare utilizatori';

$lang['User_delete'] = 'ªtergeþi acest utilizator';
$lang['User_delete_explain'] = 'Apãsaþi aici pentru a ºterge acest utilizator, aceastã operaþie este ireversibilã.';
$lang['User_deleted'] = 'Utilizatorul a fost ºters cu succes.';

$lang['User_status'] = 'Utilizatorul este activ';
$lang['User_allowpm'] = 'Poate trimite mesaje private';
$lang['User_allowavatar'] = 'Poate folosi imagini asociate';

$lang['Admin_avatar_explain'] = 'Aici puteþi vizualiza ºi ºterge imaginea asociatã a utilizatorului.';

$lang['User_special'] = 'Câmpuri speciale doar pentru administrator';
$lang['User_special_explain'] = 'Aceste câmpuri nu pot fi modificate de cãtre utilizatori. Aici puteþi sã specificaþi stadiul lor ºi alte opþiuni care nu sunt oferite utilizatorilor.';


//
// Group Management
//
$lang['Group_administration'] = 'Administrarea grupurilor';
$lang['Group_admin_explain'] = 'Din aceastã secþiune puteþi administra toate grupurile cu utilizatori ale dumneavoastrã, puteþi ºterge, crea ºi modifica grupurile existente. Puteþi alege moderatorii, schimba în deschis/închis statutul grupului ºi specifica numele ºi descrierea grupului';
$lang['Error_updating_groups'] = 'A fost o eroare în timpul actualizãrii grupurilor';
$lang['Updated_group'] = 'Grupul a fost actualizat cu succes';
$lang['Added_new_group'] = 'Noul grup a fost creat cu succes';
$lang['Deleted_group'] = 'Grupul a fost ºters cu succes';
$lang['New_group'] = 'Creazã un grup nou';
$lang['Edit_group'] = 'Modificã grupul';
$lang['group_name'] = 'Numele grupului';
$lang['group_description'] = 'Descrierea grupului';
$lang['group_moderator'] = 'Moderatorul grupului';
$lang['group_status'] = 'Statutul grupului';
$lang['group_open'] = 'Grup deschis';
$lang['group_closed'] = 'Grup închis';
$lang['group_hidden'] = 'Grup ascuns';
$lang['group_delete'] = 'ªterg grupul';
$lang['group_delete_check'] = 'Vreau sã ºterg acest grup';
$lang['submit_group_changes'] = 'Efectueazã modificãrile';
$lang['reset_group_changes'] = 'Reseteazã modificãrile';
$lang['No_group_name'] = 'Trebuie sã specificaþi un nume pentru acest grup';
$lang['No_group_moderator'] = 'Trebuie sã specificaþi un moderator pentru acest grup';
$lang['No_group_mode'] = 'Trebuie sã specificaþi un mod (deschis/închis) pentru acest grup';
$lang['No_group_action'] = 'Nici o acþiune nu a fost specificatã';
$lang['delete_group_moderator'] = 'Doriþi sã ºtergeþi moderatorul vechi al grupului?';
$lang['delete_moderator_explain'] = 'Dacã schimbaþi moderatorul grupului, bifaþi aceastã cãsuþã ca sã ºtergeþi vechiul moderator al grupului din grup. Altfel, nu o bifaþi ºi utilizatorul va deveni un membru normal al grupului.';
$lang['Click_return_groupsadmin'] = 'Apãsaþi %saici%s pentru a reveni la secþiunea Administrarea grupurilor.';
$lang['Select_group'] = 'Selecteazã un grup';
$lang['Look_up_group'] = 'Selecteazã grupul';


//
// Prune Administration
//
$lang['Forum_Prune'] = 'Curãþirea forumurilor';
$lang['Forum_Prune_explain'] = 'Aceastã acþiune va ºterge orice subiect care nu a fost completat într-un numãr de zile egal cu cel pe care l-aþi specificat. Dacã nu aþi introdus un numãr atunci toate subiectele vor fi ºterse. Nu vor fi ºterse subiecte în care sondajele încã ruleazã ºi nici anunþurile. Aceste subiecte trebuie sã le ºtergeþi manual.';
$lang['Do_Prune'] = 'Efectueazã curãþirea';
$lang['All_Forums'] = 'Toate forumurile';
$lang['Prune_topics_not_posted'] = 'Curãþirea subiectelor fãrã rãspunsuri în multe zile';
$lang['Topics_pruned'] = 'Subiecte curãþite';
$lang['Posts_pruned'] = 'Mesaje curãþite';
$lang['Prune_success'] = 'Curãþirea mesajelor s-a efectuat cu succes';


//
// Word censor
//
$lang['Words_title'] = 'Administrarea cuvintelor cenzurate';
$lang['Words_explain'] = 'Din aceastã secþiune puteþi adãuga, modifica ºi ºterge cuvinte care vor fi automat cenzurate în forumurile dumneavoastrã. În plus, persoanelor nu le va fi permis sã se înregistreze cu nume de utilizator ce conþin aceste cuvinte. Wildcard-urile (*) sunt acceptate în câmpul pentru cuvinte, de exemplu *test* se va potrivi cu detestabil, test* se va potrivi cu testare, *test se va potrivi cu detest.';
$lang['Word'] = 'Cuvânt';
$lang['Edit_word_censor'] = 'Modific cuvântul cenzurat';
$lang['Replacement'] = 'Înlocuire';
$lang['Add_new_word'] = 'Adaugã un cuvânt nou';
$lang['Update_word'] = 'Actualizeazã cuvântul cenzurat';

$lang['Must_enter_word'] = 'Trebuie sã introduceþi un cuvânt ºi înlocuirile acestuia';
$lang['No_word_selected'] = 'Nici un cuvânt nu a fost selectat pentru modificare';

$lang['Word_updated'] = 'Cuvântul cenzurat selectat a fost actualizat cu succes';
$lang['Word_added'] = 'Cuvântul cenzurat a fost adãugat cu succes';
$lang['Word_removed'] = 'Cuvântul cenzurat selectat a fost ºters cu succes';

$lang['Click_return_wordadmin'] = 'Apãsaþi %saici%s pentru a reveni la secþiunea Administrarea cuvintelor cenzurate';


//
// Mass Email
//
$lang['Mass_email_explain'] = 'Aici puteþi trimite un email la toþi utilizatorii dumneavoastrã sau la utilizatorii dintr-un grup specific. Pentru a realiza acest lucru, un email va fi trimis la adresa de email a administratorulu cu toþi destinatarii specificaþi în câmpul BCC. Dacã trimiteþi email la un grup mare de oameni, vã rugãm sã fiþi atent dupã trimitere ºi nu vã opriþi la jumãtatea paginii. Este normal ca pentru o corespondenþã masivã sã fie nevoie de un timp mai lung astfel cã veþi fi notificat când acþiunea s-a terminat';
$lang['Compose'] = 'Compune';

$lang['Recipients'] = 'Destinatari';
$lang['All_users'] = 'Toþi utilizatorii';

$lang['Email_successfull'] = 'Mesajul dumneavoastrã a fost trimis';
$lang['Click_return_massemail'] = 'Apãsaþi %saici%s pentru a reveni la secþiunea Corespondenþã masivã';


//
// Ranks admin
//
$lang['Ranks_title'] = 'Administrarea rangurilor';
$lang['Ranks_explain'] = 'Folosind acest formular puteþi adãuga, modifica, vizualiza ºi ºterge ranguri. De asemenea, puteþi crea ranguri personalizate care pot fi aplicate unui utilizator via facilitãþii date de managementul utilizatorilor';

$lang['Add_new_rank'] = 'Adaugã un rang nou';

$lang['Rank_title'] = 'Titlul rangului';
$lang['Rank_special'] = 'Seteazã ca rang special';
$lang['Rank_minimum'] = 'Numãr minim de mesaje';
$lang['Rank_maximum'] = 'Numãr maxim de mesaje';
$lang['Rank_image'] = 'Imaginea rangului (relativ la calea phpBB2-ului)';
$lang['Rank_image_explain'] = 'Aceasta este folositã pentru a defini o imagine micã asociatã cu rangul';

$lang['Must_select_rank'] = 'Trebuie sã selectaþi un rang';
$lang['No_assigned_rank'] = 'Nici un rang special nu a fost repartizat';

$lang['Rank_updated'] = 'Rangul a fost actualizat cu succes';
$lang['Rank_added'] = 'Rangul a fost adãugat cu succes';
$lang['Rank_removed'] = 'Rangul a fost ºters cu succes';
$lang['No_update_ranks'] = 'Rangul a fost ºters cu succes, conturile utilizatorilor care folosesc acest rang nu au fost actualizate. Trebuie sã resetaþi manual rangul pentru aceste conturi';

$lang['Click_return_rankadmin'] = 'Apãsaþi %saici%s pentru a reveni la secþiunea Administrarea rangurilor';


//
// Disallow Username Admin
//
$lang['Disallow_control'] = 'Administrarea numelor de utilizator nepremise';
$lang['Disallow_explain'] = 'Aici puteþi controla numele de utilizator care nu sunt permise sã fie folosite. Numele de utilizator care nu sunt permise pot conþine caracterul *. Reþineþi cã nu aveþi posibilitatea sã specificaþi orice nume de utilizator care a fost deja înregistrat; trebuie mai întâi sã ºtergeþi acel nume ºi apoi sã-l interziceþi';

$lang['Delete_disallow'] = 'ªterge';
$lang['Delete_disallow_title'] = 'ªterge un nume de utilizator nepermis';
$lang['Delete_disallow_explain'] = 'Puteþi ºterge un nume de utilizator nepermis selectând numele de utilizator din aceastã listã ºi apãsând butonul <i>ªterge</i>';

$lang['Add_disallow'] = 'Adaugã';
$lang['Add_disallow_title'] = 'Adaugã un nume de utilizator nepermis';
$lang['Add_disallow_explain'] = 'Puteþi interzice un nume de utilizator folosind caracterul wildcard * care se potriveºte la orice caracter';

$lang['No_disallowed'] = 'Nici un nume de utilizator nu a fost interzis';

$lang['Disallowed_deleted'] = 'Numele de utilizator nepermis a fost ºters cu succes';
$lang['Disallow_successful'] = 'Numele de utilizator nepermis a fost adãugat cu succes';
$lang['Disallowed_already'] = 'Numele pe care l-aþi introdus nu poate fi interzis. Ori existã deja în listã, existã în lista cuvintelor cenzurate sau existã un nume de utilizator similar';

$lang['Click_return_disallowadmin'] = 'Apãsaþi %saici%s pentru a reveni la secþiunea Administrarea numelor de utilizator nepremise';


//
// Styles Admin
//
$lang['Styles_admin'] = 'Administrarea stilurilor';
$lang['Styles_explain'] = 'Folosind aceastã facilitate puteþi adãuga, ºterge ºi administra stilurile (ºabloanele ºi temele) disponibile utilizatorilor dumneavoastrã';
$lang['Styles_addnew_explain'] = 'Lista urmãtoare conþine toate temele care sunt disponibile pentru ºabloanele pe care le aveþi. Elementele din aceastã listã nu au fost instalate în baza de date a phpBB-ului. Ca sã instalaþi o temã apãsaþi pe legãtura <i>Instaleazã</i> de lângã denumirea temei';

$lang['Select_template'] = 'Selectaþi un ºablon';

$lang['Style'] = 'Stilul';
$lang['Template'] = 'ªablonul';
$lang['Install'] = 'Instaleazã';
$lang['Download'] = 'Descarcã';

$lang['Edit_theme'] = 'Modificã tema';
$lang['Edit_theme_explain'] = 'În formularul de mai jos puteþi modifica configurãrile pentru tema selectatã';

$lang['Create_theme'] = 'Creazã temã';
$lang['Create_theme_explain'] = 'Folosiþi formularul de mai jos ca sã creaþi o temã nouã pentru un ºablon selectat. Când introduceþi culori (pentru care trebuie sã folosiþi notaþie hexazecimalã) nu trebuie sã includeþi iniþiala #, de exemplu CCCCCC este validã, #CCCCCC nu este validã';

$lang['Export_themes'] = 'Exportã teme';
$lang['Export_explain'] = 'În aceastã secþiune puteþi exporta teme dintr-un ºablon selectat. Selectaþi ºablonul din lista de mai jos ºi programul va crea un fiºier de configurare a temei ºi încercaþi sã-l salvaþi în directorul ºablonului selectat. Dacã fiºierul nu poate fi salvat vi se va da posibilitatea sã-l descãrcaþi. Pentru ca programul sã salveze fiºierul trebuie sã daþi drepturi de scriere pentru serverul web pe directorul ºablonului selectat. Pentru mai multe informaþii consultaþi pagina 2 din ghidul utilizatorilor phpBB.';

$lang['Theme_installed'] = 'Tema selectatã a fost instalatã cu succes';
$lang['Style_removed'] = 'Stilul selectat a fost ºters din baza de date. Pentru a ºterge definitiv acest stil din sistem, trebuie sã-l ºtergeþi din directorul dumneavoastrã cu ºabloane.';
$lang['Theme_info_saved'] = 'Informaþiile temei pentru ºablonul curent au fost salvate. Acum trebuie sã specificaþi permisiunile în fiºierul theme_info.cfg (ºi dacã se poate directorul ºablonului selectat) la acces doar de citire';
$lang['Theme_updated'] = 'Tema selectatã a fost actualizatã. Acum ar trebui sã exportaþi setãrile temei noi';
$lang['Theme_created'] = 'Temã a fost creatã. Acum ar trebui sã exportaþi tema în fiºierul de configurare al temei pentru pãstrarea în siguranþã a acesteia sau s-o folosiþi altundeva';

$lang['Confirm_delete_style'] = 'Sunteþi sigur cã doriþi sã ºtergeþi acest stil?';

$lang['Download_theme_cfg'] = 'Procedura de export nu poate scrie fiºierul cu informaþiile temei. Apãsaþi butonul de mai jos ca sã descãrcaþi acest fiºier. Odatã ce l-aþi descãrcat puteþi sã-l transferaþi în directorul care conþine fiºierele cu ºabloane. Puteþi împacheta fiºierele pentru distribuþie sau sã le folosiþi unde doriþi';
$lang['No_themes'] = 'ªablonul pe care l-aþi selectat nu are teme ataºate. Ca sã creaþi o temã nouã apãsaþi legãtura <i>Creazã temã</i> din partea stângã';
$lang['No_template_dir'] = 'Nu se poate deschide directorul cu ºabloane. Acesta ori nu poate fi citit de cãtre serverul web ori nu existã';
$lang['Cannot_remove_style'] = 'Nu puteþi ºterge stilul selectat în timp ce este acesta este stilul standard pentru forum. Schimbaþi stilul standard ºi încercaþi din nou.';
$lang['Style_exists'] = 'Numele stilului pe care l-aþi selectat existã deja, vã rugãm reveniþi ºi alegeþi un nume diferit.';

$lang['Click_return_styleadmin'] = 'Apãsaþi %saici%s ca sã reveniþi la secþiunea Administrarea stilurilor';

$lang['Theme_settings'] = 'Configurãrile temei';
$lang['Theme_element'] = 'Elementul temei';
$lang['Simple_name'] = 'Numele simplu';
$lang['Value'] = 'Valoarea';
$lang['Save_Settings'] = 'Salveazã configurãrile';

$lang['Stylesheet'] = 'Stilul CSS';
$lang['Background_image'] = 'Imaginea fundalului';
$lang['Background_color'] = 'Culoarea fundalului';
$lang['Theme_name'] = 'Numele temei';
$lang['Link_color'] = 'Culoarea legãturii';
$lang['Text_color'] = 'Culoarea textului';
$lang['VLink_color'] = 'Culoarea legãturii vizitate';
$lang['ALink_color'] = 'Culoarea legãturii active';
$lang['HLink_color'] = 'Culoarea legãturii acoperite';
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
$lang['fontface1'] = 'Fontul de faþã 1';
$lang['fontface2'] = 'Fontul de faþã 2';
$lang['fontface3'] = 'Fontul de faþã 3';
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
$lang['Welcome_install'] = 'Bine aþi venit la procedura de instalare a formumului phpBB2';
$lang['Initial_config'] = 'Configuraþia de bazã';
$lang['DB_config'] = 'Configuraþia bazei de date';
$lang['Admin_config'] = 'Configuraþia administratorului';
$lang['continue_upgrade'] = 'Odatã ce aþi descãrcat fiºierul dumneavoastrã de configurare pe calculatorul local puteþi folosi butonul <i>Continuã actualizarea</i> de mai jos ca sã treceþi la urmãtorul pas din actualizare. Vã rugãm aºteptaþi sã se încarce fiºierul de configurare pânã ce actualizarea  este completã.';
$lang['upgrade_submit'] = 'Continuã actualizarea';

$lang['Installer_Error'] = 'O eroare a apãrut în timpul instalãrii';
$lang['Previous_Install'] = 'O instalare anterioarã a fost detectatã';
$lang['Install_db_error'] = 'O eroare a apãrut în timpul actualizãrii bazei de date';

$lang['Re_install'] = 'Instalarea anterioarã este încã activã. <br /><br />Dacã doriþi sã reinstalaþi phpBB2-ul ar trebui sã apãsaþi pe butonul Da de mai jos. Vã rugãm sã aveþi grijã ca sã nu distrugeþi toate datele existente, nici o copie de siguranþã nu va fi fãcutã! Numele de utilizator ºi parola administratorului pe care le-aþi folosit sã vã autentificaþi în forum vor fi recreate dupã reinstalare, nici o altã setare nu va fi pãstratã. <br /><br />Gândiþi-vã atent înainte de a apãsa butonul <i>Poneºte instalarea</i>!';

$lang['Inst_Step_0'] = 'Vã mulþumim cã aþi ales phpBB2. Pentru a completa aceastã instalare vã rugãm sã completaþi detaliile de mai jos. Reþineþi cã baza de date pe care o folosiþi trebuie sã existe deja. Dacã instalaþi într-o bazã de date care foloseºte ODBC, de exemplu MS Access ar trebui mai întâi sã creaþi un DSN pentru aceasta înainte de a continua.';

$lang['Start_Install'] = 'Poneºte instalarea';
$lang['Finish_Install'] = 'Terminã instalarea';

$lang['Default_lang'] = 'Limba standard pentru forum';
$lang['DB_Host'] = 'Numele serverului gazdã pentru baza de date / DSN';
$lang['DB_Name'] = 'Numele bazei dumneavoastrã de date';
$lang['DB_Username'] = 'Numele de utilizator al bazei de date';
$lang['DB_Password'] = 'Parola de utilizator al bazei de date';
$lang['Database'] = 'Baza dumneavoastrã de date';
$lang['Install_lang'] = 'Alegeþi limba pentru instalare';
$lang['dbms'] = 'Tipul bazei de date';
$lang['Table_Prefix'] = 'Prefixul pentru tabelele din baza de date';
$lang['Admin_Username'] = 'Numele de utilizator al administratorului';
$lang['Admin_Password'] = 'Parola administratorului';
$lang['Admin_Password_confirm'] = 'Parola administratorului [ Confirmaþi ]';

$lang['Inst_Step_2'] = 'Numele de utilizator pentru administrator a fost creat. Acum instalarea de bazã este completã. Va apãrea un ecran care vã va permite sã administraþi noua dumneavoastrã instalare. Asiguraþi-vã cã aþi verificat detaliile secþiunii Configurare generalã ºi aþi efectuat orice schimbare necesarã. Vã mulþumim cã aþi ales phpBB2.';

$lang['Unwriteable_config'] = 'Fiºierul dumneavoastrã de configurare în acest moment este protejat la scriere. O copie a fiºierului de configurare va fi descãrcatã când apãsaþi butonul de mai jos. At trebui sã încãrcaþi acest fiºier în acelaºi director ca ºi phpBB2. Odatã ce aceastã operaþiune este terminatã ar trebui sã vã autentificaþi folosind numele de utilizator ºi parola administratorului pe care le-aþi specificat în formularul anterior ºi sã vizitaþi centrul de control al administratorului (o legãturã va apãrea la capãtul fiecãrei pagini odatã ce v-aþi autentificat) ca sã verificaþi configuraþia generalã. Vã mulþumim cã aþi ales phpBB2.';
$lang['Download_config'] = 'Descarcã configurarea';

$lang['ftp_choose'] = 'Alegeþi metoda de descãrcare';
$lang['ftp_option'] = '<br />Întrucât extensiile FTP sunt activate în aceastã versiune a PHP-ului, aveþi posibilitatea de a încerca sã plasaþi prin ftp fiºierul de configurare la locul lui.';
$lang['ftp_instructs'] = 'Aþi ales sã transmiteþi fiºierul automat prin ftp în contul care conþine phpBB2-ul. Vã rugãm introduceþi informaþiile cerute mai jos ca sã facilitaþi aceast proces. Calea unde este situat FTP-ul trebuie sã fie calea exactã via ftp la instalarea phpBB2-ului dumneavoastrã ca ºi cum aþi transmite folosind un client normal de ftp.';
$lang['ftp_info'] = 'Introduceþi informaþiile dumneavoastrã despre FTP';
$lang['Attempt_ftp'] = 'Încercare de a transfera la locul specificat fiºierul de configurare prin ftp';
$lang['Send_file'] = 'Trimite doar fiºierul la mine ºi eu voi îl voi trimite manual prin ftp';
$lang['ftp_path'] = 'Calea FTP la phpBB2';
$lang['ftp_username'] = 'Numele dumneavoastrã de utilizator pentru FTP';
$lang['ftp_password'] = 'Parola dumneavoastrã de utilizator pentru FTP';
$lang['Transfer_config'] = 'Porneºte transferul';
$lang['NoFTP_config'] = 'Încercarea de a transfera la locul specificat fiºierul de configurare prin ftp a eºuat. Vã rugãm sã descãrcaþi fiºierul de configurare ºi sã-l transmiteþi manual prin ftp la locul specificat.';

$lang['Install'] = 'Instaleazã';
$lang['Upgrade'] = 'Actualizeazã';


$lang['Install_Method'] = 'Alegeþi metoda de instalare';

$lang['Install_No_Ext'] = 'Configurarea php-ului pe serverul dumneavoastrã nu suportã tipul de bazã de date pe care l-aþi ales';

$lang['Install_No_PCRE'] = 'phpBB2 necesitã modulul de expresii regulate compatibil Perl pentru php pe care configuraþia dumneavoastrã de php se pare cã nu-l suportã!';

//
// That's all Folks!
// -------------------------------------------------

?>
