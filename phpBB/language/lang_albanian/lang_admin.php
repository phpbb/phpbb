<?php

/***************************************************************************
 *                            lang_admin.php [English]
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
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "Administrim i Përgjithshëm";
$lang['Users'] = "Administrim i Anëtarëve";
$lang['Groups'] = "Administrim i Grupeve";
$lang['Forums'] = "Administrim i Forumeve";
$lang['Styles'] = "Administrim i Paraqitjes";

$lang['Configuration'] = "Konfigurimi";
$lang['Permissions'] = "Autorizimet";
$lang['Manage'] = "Menaxhimi";
$lang['Disallow'] = "Mos lejo emrat";
$lang['Prune'] = "Shkurtimi";
$lang['Mass_Email'] = "Email Masiv";
$lang['Ranks'] = "Gradimi";
$lang['Smilies'] = "Figurinat";
$lang['Ban_Management'] = "Përjashtimet";
$lang['Word_Censor'] = "Fjalët e Censuruara";
$lang['Export'] = "Eksporto";
$lang['Create_new'] = "Krijo";
$lang['Add_new'] = "Shto";
$lang['Backup_DB'] = "Krijo një kopje të Regjistrit";
$lang['Restore_DB'] = "Rivendos Regjistrin";



//
// Index
//
$lang['Admin'] = "Administrim";
$lang['Not_admin'] = "Ju nuk keni autorizim për të modifikuar këtë forum";
$lang['Welcome_phpBB'] = "Mirësevini tek phpBB";
$lang['Admin_intro'] = "Ju falënderojmë që zgjodhët phpBB për forumin tuaj. Kjo faqe jep një përmbledhje të statistikave kryesore për forumin tuaj. Kthehuni tek kjo faqe duke klikuar mbi butonin <u>Indeksi i Administrimit</u> në anën e majtë të faqes. Klikoni ikonën e phpBB për të shkuar tek indeksi kryesor i forumeve. Lidhjet e tjera në anën e majte të faqes mundësojnë kontrollin e plotë të forumit dhe përmbajnë udhëzime mbi përdorimin e cdo kontrolli.";
$lang['Main_index'] = "Indeksi i Forumit";
$lang['Forum_stats'] = "Statistikat e Forumit";
$lang['Admin_Index'] = "Indeksi i Administrimit";
$lang['Preview_forum'] = "Shqyrto Forumin";

$lang['Click_return_admin_index'] = "Kliko %skëtu%s për të shkuar tek indeksi i administrimit";

$lang['Statistic'] = "Statistika";
$lang['Value'] = "Vlera";
$lang['Number_posts'] = "Numri i postimeve";
$lang['Posts_per_day'] = "Postime në ditë";
$lang['Number_topics'] = "Numri i temave";
$lang['Topics_per_day'] = "Tema në ditë";
$lang['Number_users'] = "Numri i anëtarëve";
$lang['Users_per_day'] = "Anëtarë në ditë";
$lang['Board_started'] = "Forumi filloi";
$lang['Avatar_dir_size'] = "Madhësia e direktorisë së fotos personale";
$lang['Database_size'] = "Madhësia e regjistrit";
$lang['Gzip_compression'] ="Kompresimi me Gzip";
$lang['Not_available'] = "Nuk ofrohet";

$lang['ON'] = "Aktiv"; // This is for GZip compression
$lang['OFF'] = "Jo-aktiv"; 


//
// DB Utils
//
$lang['Database_Utilities'] = "Vegla të dobishme për përpunimin e regjistrit";

$lang['Restore'] = "Rivendos";
$lang['Backup'] = "Krijo kopje";
$lang['Restore_explain'] = "Ky veprim do kryejë një rivendosje të plotë të të gjitha tabelave të phpBB nga një skedar. Nqs serveri juaj e lejon, ju mund të ngarkoni një skedar të kompresuar me gzip. <b>KUJDES</b> Ky veprim do rishkruajë të gjitha të dhënat e forumit. Procesi i rivendosjes mund të kërkojë shumë kohë, ju lutem mos ikni nga kjo faqe deri në përfundim të procesit!";
$lang['Backup_explain'] = "Këtu mund të krijoni një kopje të plotë të phpBB. Nqs keni tabela speciale në të njëjtin regjistër me phpBB dhe doni ti kopjoni në të njëjtin skedar, specifikoni emrat e tyre duke i ndarë me presje tek kutia e Tabelave Shtesë. Nqs serveri juaj e lejon, ju mund të kompresoni skedarin me gzip para se ta shkarkoni.";

$lang['Backup_options'] = "Mundësitë për Kopjen";
$lang['Start_backup'] = "Fillo kopjimin";
$lang['Full_backup'] = "Kopjim i plotë";
$lang['Structure_backup'] = "Vetëm - Kopjim i strukturës";
$lang['Data_backup'] = "Vetëm - Kopjim i të dhënave";
$lang['Additional_tables'] = "Tabela Shtesë";
$lang['Gzip_compress'] = "Kompreso skedarin me gzip";
$lang['Select_file'] = "Zgjidh një skedar";
$lang['Start_Restore'] = "Fillo rivendosjen";

$lang['Restore_success'] = "Regjistri u rivendos në mënyrë të suksesshme.<br /><br />Forumi juaj duhet të kthehet në gjendjen që kishte kur u kopjua.";
$lang['Backup_download'] = "Shkarkimi do filloje së shpejti, prisni deri sa të fillojë";
$lang['Backups_not_supported'] = "Na vjen keq, por kopjimi nuk mbështetet për këtë lloj regjistri";

$lang['Restore_Error_uploading'] = "Problem me ngarkimin e skedarit (kopja e regjistrit)";
$lang['Restore_Error_filename'] = "Problem me emrin e skedarit, provo një skedar tjetër";
$lang['Restore_Error_decompress'] = "Nuk dekompreson dot skedarin me gzip, ngarkoni një text-file";
$lang['Restore_Error_no_file'] = "Asnjë skedar nuk u ngarkua";


//
// Auth pages
//
$lang['Select_a_User'] = "Zgjidh një anëtar";
$lang['Select_a_Group'] = "Zgjidh një grup";
$lang['Select_a_Forum'] = "Zgjidh një forum";
$lang['Auth_Control_User'] = "Kontrolli i autorizimeve personale"; 
$lang['Auth_Control_Group'] = "Kontrolli i autorizimeve të grupeve"; 
$lang['Auth_Control_Forum'] = "Kontrolli i autorizimeve për forumet"; 
$lang['Look_up_User'] = "Analizo anëtarin"; 
$lang['Look_up_Group'] = "Analizo grupin"; 
$lang['Look_up_Forum'] = "Analizo forumin"; 

$lang['Group_auth_explain'] = "Këtu mund të ndryshoni autorizimet dhe statusin e moderatorit që i janë caktuar cdo grupi anëtarësh. Kujdes, mos harroni që ndryshimi i autorizimeve për grupin mund të mos ndikojë autorizimet personale, etj. Ju do paralajmëroheni në këto raste.";
$lang['User_auth_explain'] = "Këtu mund të ndryshoni autorizimet dhe statusin e moderatorit që i janë caktuar cdo përdoruesi. Kujdes, mos harroni që ndryshimi i autorizimeve për përdoruesin mund të mos ndikojë autorizimet për grupin, etj. Ju do paralajmëroheni në këto raste.";
$lang['Forum_auth_explain'] = "Këtu mund të ndryshoni autorizimet për cdo forum. Ka dy mënyra për ta bërë këtë, mënyra e thjeshtë dhe mënyra e avancuar. Mënyra e avancuar ofron kontroll më të përpiktë për cdo veprim. Mos harroni që ndryshimi i nivelit të autorizimit në një forum do ndikojë përdorimin e tij nga përdoruesët e ndryshëm.";

$lang['Simple_mode'] = "Mënyra e thjeshtë";
$lang['Advanced_mode'] = "Mënyra e avancuar";
$lang['Moderator_status'] = "Status moderatori";

$lang['Allowed_Access'] = "Lejohet hyrja";
$lang['Disallowed_Access'] = "Ndalohet hyrja";
$lang['Is_Moderator'] = "Eshtë moderator";
$lang['Not_Moderator'] = "Nuk është moderator";

$lang['Conflict_warning'] = "Paralajmërim: Konflikt në autorizim";
$lang['Conflict_access_userauth'] = "Ky përdorues ka akoma te drejta për hyrje në këtë forum nëpërmjet anëtarësisë në grup. You duhet të ndryshoni autorizimet e grupit, ose ta hiqni këtë përdorues nga ky grup në mënyrë që ta ndaloni. Grupet që i japin të drejta (dhe forumet që ndikohen) janë renditur më poshtë.";
$lang['Conflict_mod_userauth'] = "Ky përdorues ka akoma të drejta Moderatori për këtë forum nëpërmjet anëtarësisë në grup. You duhet të ndryshoni autorizimet e grupit, ose ta hiqni këtë përdorues nga ky grup në mënyrë që ta ndaloni. Grupet që i japin të drejta (dhe forumet që ndikohen) janë renditur më poshtë.";

$lang['Conflict_access_groupauth'] = "Ky përdorues/ë kanë akoma te drejta për hyrje në këtë forum nëpërmjet autorizimeve individuale. You duhet të ndryshoni autorizimet individuale  që ta/i ndaloni. Përdoruesët me të drejta (dhe forumet që ndikohen) janë renditur më poshtë.";
$lang['Conflict_mod_groupauth'] = "Ky përdorues ka akoma të drejta Moderatori për këtë forum nëpërmjet autorizimeve personale. You duhet të ndryshoni autorizimet individuale që ta ndaloni. Përdoruesët me të drejta (dhe forumet që ndikohen) janë renditur më poshtë.";

$lang['Public'] = "Publik";
$lang['Private'] = "Privat";
$lang['Registered'] = "I regjistruar";
$lang['Administrators'] = "Administratorët";
$lang['Hidden'] = "I fshehur";

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = "ALL";
$lang['Forum_REG'] = "REG";
$lang['Forum_PRIVATE'] = "PRIVATE";
$lang['Forum_MOD'] = "MOD";
$lang['Forum_ADMIN'] = "ADMIN";

$lang['View'] = "Shiko";
$lang['Read'] = "Lexo";
$lang['Post'] = "Shkruaj";
$lang['Reply'] = "Përgjigju";
$lang['Edit'] = "Modifiko";
$lang['Delete'] = "Fshi";
$lang['Sticky'] = "Ngjitës";
$lang['Announce'] = "Lajmëro"; 
$lang['Vote'] = "Voto";
$lang['Pollcreate'] = "Krijo votim";

$lang['Permissions'] = "Autorizimet";
$lang['Simple_Permission'] = "Autorizim i thjeshtësuar";

$lang['User_Level'] = "Nivel përdoruesi"; 
$lang['Auth_User'] = "Përdorues";
$lang['Auth_Admin'] = "Administrator";
$lang['Group_memberships'] = "Anëtarësia e grupit";
$lang['Usergroup_members'] = "Ky grup ka keta anëtarë";

$lang['Forum_auth_updated'] = "Autorizimet e forumit u ri-freskuan";
$lang['User_auth_updated'] = "Autorizimet e përdoruesit u ri-freskuan";
$lang['Group_auth_updated'] = "Autorizimet e grupit u ri-freskuan";

$lang['Auth_updated'] = "Autorizimet u ri-freskuan";
$lang['Click_return_userauth'] = "Kliko %sketu%s për ty kthyer tek Autorizimet e Përdoruesve";
$lang['Click_return_groupauth'] = "Kliko %sketu%s për ty kthyer tek Autorizimet e Grupeve";
$lang['Click_return_forumauth'] = "Kliko %sketu%s për ty kthyer tek Autorizimet e Forumeve";


//
// Banning
//
$lang['Ban_control'] = "Menaxhimi i përjashtimeve";
$lang['Ban_explain'] = "Këtu bëhet përjashtimi i përdoruesve/anëtarëve. Kjo arrihet duke përjashtuar një anëtar specifik, një IP/hostname ose grup IP/hostname, ose të dyja bashkë. Këto metoda pengojnë një përdorues madje dhe të shikojnë faqen kryesore të forumit. Nqs doni të pengoni dikë të përjashtuar më parë dhe që tenton të regjistrohet me një emër të ri, mund ta ndaloni atë duke përjashtuar adresën e email. Kini parasysh, përjashtimi i email-it nuk pengon dikë që të shkruajë apo shikojë forumin. Për këtë  përdorni një ose të dyja metodat e mësipërme.";
$lang['Ban_explain_warn'] = "Kujdes, përjashtimi i një serie IP-sh përjashton cdo IP midis fillimit dhe fundit të serisë. Nqs ju duhet të përjashtoni një seri, mundohuni ta minimizoni serinë. ";

$lang['Select_username'] = "Zgjidh identifikimin";
$lang['Select_ip'] = "Zgjidh IP";
$lang['Select_email'] = "Zgjidh adresën e e-mail";

$lang['Ban_username'] = "Përjashto një ose më shumë anëtarë";
$lang['Ban_username_explain'] = "Përjashtimi i një ose më shumë anëtarëve njëkohësisht është i mundur me kombinimin e duhur të butonave";

$lang['Ban_IP'] = "Përjashto një ose më shumë IP ose hostname";
$lang['IP_hostname'] = "IP ose hostnames";
$lang['Ban_IP_explain'] = "Per të specifikuar më shumë se një IP ose hostname, ndajini me presje. To specify a range of IP addresses separate the start and end with a hyphen (-), to specify a wildcard use *";

$lang['Ban_email'] = "Pëjashto një ose më shumë adresa e-mail";
$lang['Ban_email_explain'] = "Per të specifikuar më shumë se një adresë e-maili, ndajini me presje. Për të specifikuar një -wildcard username- përdor *, për shembull *@hotmail.com";

$lang['Unban_username'] = "Riprano një ose më shumë anëtarë";
$lang['Unban_username_explain'] = "Ripranimi i një ose më shumë anëtarëve njëkohësisht është i mundur me kombinimin e duhur të butonave";

$lang['Unban_IP'] = "Riprano një ose më shumë IP";
$lang['Unban_IP_explain'] = "Ripranimi i një ose më shumë IP njëkohësisht është i mundur me kombinimin e duhur të butonave";

$lang['Unban_email'] = "Riprano një ose më shumë adresa e-maili";
$lang['Unban_email_explain'] = "Ripranimi i një ose më shumë adresave njëkohësisht është i mundur me kombinimin e duhur të butonave";

$lang['No_banned_users'] = "Asnjë anëtar i përjashtuar";
$lang['No_banned_ip'] = "Asnjë IP e përjashtuar";
$lang['No_banned_email'] = "Asnjë adresë e-maili e përjashtuar";

$lang['Ban_update_sucessful'] = "Lista e përjashtimeve u refreskua në mënyrë të suksesshme";
$lang['Click_return_banadmin'] = "Kliko %skëtu%s për tu kthyer tek menaxhimi i përjashtimeve";


//
// Configuration
//
$lang['General_Config'] = "Konfigurim i përgjithshëm";
$lang['Config_explain'] = "Formulari i mëposhtëm ju jep mundësine e konfigurimit të opsioneve të përgjithshme. Për administrimin dhe konfigurimin e anëtarëve dhe forumeve, përdorni tabelat në krahun e majtë.";

$lang['Click_return_config'] = "Kliko %skëtu%s për tu kthyer tek konfigurimi i përgjithshëm";

$lang['General_settings'] = "Vetitë e përgjithshme të forumit(site)";
$lang['Server_name'] = "Domain Name";
$lang['Server_name_explain'] = "The domain name this board runs from";
$lang['Script_path'] = "Script path";
$lang['Script_path_explain'] = "The path where phpBB2 is located relative to the domain name";
$lang['Server_port'] = "Server Port";
$lang['Server_port_explain'] = "The port your server is running on, usually 80, only change if different";
$lang['Site_name'] = "Emri i websitit";
$lang['Site_desc'] = "Përshkrimi i websitit";
$lang['Board_disable'] = "Disaktivizoje websitin";
$lang['Board_disable_explain'] = "KUJDES!!!! Ky veprim do e bëjë forumin jofunksional. Nqs bëni logout pas disaktivizimit,nuk do keni mundësi që të bëni login!";
$lang['Acct_activation'] = "Mundëso aktivizimin e llogarisë nga";
$lang['Acc_None'] = "Askush"; // Këto janë 3 llojet e aktivizimit
$lang['Acc_User'] = "Anëtari";
$lang['Acc_Admin'] = "Administratori";

$lang['Abilities_settings'] = "Veti Elementare të Anëtarëve dhe Forumeve";
$lang['Max_poll_options'] = "Nr. maksimal i mundësive për një votim";
$lang['Flood_Interval'] = "Flood Interval";
$lang['Flood_Interval_explain'] = "Numri i sekondave që një anëtar duhet të presë midis postimeve"; 
$lang['Board_email_form'] = "Përdorimi për e-mail";
$lang['Board_email_form_explain'] = "Anëtarët mund të dërgojnë e-mail nëpërmjet këtij serveri";
$lang['Topics_per_page'] = "Diskutime për faqe";
$lang['Posts_per_page'] = "Poste për faqe";
$lang['Hot_threshold'] = "Posts for Popular Threshold";
$lang['Default_style'] = "Paraqitja e paracaktuar";
$lang['Override_style'] = "Zëvendëso preferencën e përdoruesve?";
$lang['Override_style_explain'] = "Zëvendëson paraqitjen e preferuar të përdoruesve me paraqitjen e paracaktuar";
$lang['Default_language'] = "Gjuha e paracaktuar";
$lang['Date_format'] = "Formatimi i Datës";
$lang['System_timezone'] = "Brezi orar i sistemit";
$lang['Enable_gzip'] = "Mundëso kompresimin me GZip";
$lang['Enable_prune'] = "Mundëso shkurtimin/shartimin e forumeve";
$lang['Allow_HTML'] = "Lejo HTML";
$lang['Allow_BBCode'] = "Lejo BBCode";
$lang['Allowed_tags'] = "Shënjat HTML të lejuara";
$lang['Allowed_tags_explain'] = "Ndaji shënjat me presje";
$lang['Allow_smilies'] = "Lejo figurinat";
$lang['Smilies_path'] = "Shtegu i direktorisë së figurinave";
$lang['Smilies_path_explain'] = "Path under your phpBB root dir, e.g. images/smilies";
$lang['Allow_sig'] = "Lejo firmat";
$lang['Max_sig_length'] = "Madhësia maksimale e firmave";
$lang['Max_sig_length_explain'] = "Nr. maksimal i shkronjave të lejuara në një firmë";
$lang['Allow_name_change'] = "Lejo ndërrimin e username";

$lang['Avatar_settings'] = "Vetitë e Ikonave Personale";
$lang['Allow_local'] = "Mundëso galerinë e ikonave personale";
$lang['Allow_remote'] = "Mundëso ikona personale nga servera të tjerë";
$lang['Allow_remote_explain'] = "Ikona personale që ruhen në një websit tjetër";
$lang['Allow_upload'] = "Mundëso ngarkimin e ikonave personale";
$lang['Max_filesize'] = "Madhësia maksimale e ikonës personale";
$lang['Max_filesize_explain'] = "Vetëm për ikonat e ngarkuara ne këtë server";
$lang['Max_avatar_size'] = "Dimensionet maksimale të ikonave personale";
$lang['Max_avatar_size_explain'] = "(Gjatësi x Gjerësi në piksel)";
$lang['Avatar_storage_path'] = "Shtegu i magazinimit të ikonave personale";
$lang['Avatar_storage_path_explain'] = "Path under your phpBB root dir, e.g. images/avatars";
$lang['Avatar_gallery_path'] = "Shtegu i galerisë së ikonave personale";
$lang['Avatar_gallery_path_explain'] = "Path under your phpBB root dir for pre-loaded images, e.g. images/avatars/gallery";

$lang['COPPA_settings'] = "COPPA Settings";
$lang['COPPA_fax'] = "COPPA Fax Number";
$lang['COPPA_mail'] = "COPPA Mailing Address";
$lang['COPPA_mail_explain'] = "This is the mailing address where parents will send COPPA registration forms";

$lang['Email_settings'] = "Vetitë e E-mail";
$lang['Admin_email'] = "Adresa e email të administratorit";
$lang['Email_sig'] = "Firma e email-it";
$lang['Email_sig_explain'] = "Kjo firmë do u bashkangjitet në fund të gjithë mesazheve të derguara nga ky server";
$lang['Use_SMTP'] = "Përdor server SMTP per dërgimin e email-ave";
$lang['Use_SMTP_explain'] = "Përcaktoje këtë veti nqs doni/jeni i detyruar të mos përdorni programin mail te serverit";
$lang['SMTP_server'] = "Adresa e serverit SMTP";
$lang['SMTP_username'] = "SMTP Username";
$lang['SMTP_username_explain'] = "Only enter a username if your smtp server requires it";
$lang['SMTP_password'] = "SMTP Password";
$lang['SMTP_password_explain'] = "Only enter a password if your smtp server requires it";

$lang['Disable_privmsg'] = "Private Messaging";
$lang['Inbox_limits'] = "Maksimumi i posteve në Inbox";
$lang['Sentbox_limits'] = "Maksimumi i posteve në Sentbox";
$lang['Savebox_limits'] = "Maksimumi i posteve në Savebox";

$lang['Cookie_settings'] = "Vetitë e Cookie-s "; 
$lang['Cookie_settings_explain'] = "Këto të dhëna kontrollojnë se si cooki i dërgohet browser-it. Në shumicën e rasteve, vlerat e paracaktuara jane të mjaftueshme. Nqs keni nevojë ti ndryshoni, kini kujdes se të dhëna jokorrekte krijojne probleme me indentifikimin e anëtarëve.";
$lang['Cookie_name'] = "Emri i Cookie";
$lang['Cookie_domain'] = "Domain i Cookie";
$lang['Cookie_path'] = "Shtegu i Cookie";
$lang['Session_length'] = "Zgjatja e sesionit [ në sekonda ]";
$lang['Cookie_secure'] = "Cookie e sigurtë [ https ]";
$lang['Session_length'] = "Session length [ seconds ]";


//
// Forum Management
//
$lang['Forum_admin'] = "Administrim Forumi";
$lang['Forum_admin_explain'] = "Nga ky panel bëhet krijimi, fshirja, modifikimi, ri-renditja, dhe ri-sinkronizimi i kategorive dhe forumeve";
$lang['Edit_forum'] = "Modifiko forumin";
$lang['Create_forum'] = "Krijo forum";
$lang['Create_category'] = "Krijo kategori";
$lang['Remove'] = "Hiq";
$lang['Action'] = "Veprimi";
$lang['Update_order'] = "Rifresko renditjen";
$lang['Config_updated'] = "Rifreskimi i konfigurimit të forumit u bë në mënyrë të suksesshme";
$lang['Edit'] = "Modifiko";
$lang['Delete'] = "Fshi";
$lang['Move_up'] = "Lëvize sipër";
$lang['Move_down'] = "Lëvize poshtë";
$lang['Resync'] = "Ri-sinkronizo";
$lang['No_mode'] = "No mode was set";
$lang['Forum_edit_delete_explain'] = "Formulari i mëposhtëm ju jep mundësine e konfigurimit të opsioneve të përgjithshme. Për administrimin dhe konfigurimin e anëtarëve dhe forumeve, përdorni tabelat në krahun e majtë.";

$lang['Move_contents'] = "Zhvendos gjithë përmbajtjen";
$lang['Forum_delete'] = "Fshije këtë forum";
$lang['Forum_delete_explain'] = "Formulari i mëposhtëm ju jep mundësine e fshirjes së një forumi (apo kategorie) dhe ruajtjen e gjithë mesazheve (forumeve) që përmban.";

$lang['Forum_settings'] = "Vetitë e përgjithshme të forumit";
$lang['Forum_name'] = "Emri i forumit";
$lang['Forum_desc'] = "Përshkrimi";
$lang['Forum_status'] = "Statusi i forumit";
$lang['Forum_pruning'] = "Auto-shkurtim";

$lang['prune_freq'] = 'Kontrollo vjetërsinë e diskutimit cdo';
$lang['prune_days'] = "Hiq diskutimet ku nuk postohet prej";
$lang['Set_prune_data'] = "Ju zgjodhët auto-shkurtim për këtë forum por nuk specifikuat një frekuencë ose nr. e ditëve për shkurtim. Ju lutem shkoni mbrapsht dhe specifikoni këto veti.";

$lang['Move_and_Delete'] = "Zhvendos dhe Fshi";

$lang['Delete_all_posts'] = "Fshi gjithë postet";
$lang['Nowhere_to_move'] = "S'ke ku e con";

$lang['Edit_Category'] = "Modifiko kategorinë";
$lang['Edit_Category_explain'] = "Përdor këtë formular për ndërrimin e emrit të kategorisë";

$lang['Forums_updated'] = "Informacioni rreth forumit dhe kategorisë u freskua në menyrë të suksesshme";

$lang['Must_delete_forums'] = "Fshi gjithë forumet në këtë kategori para se të fshish kategorinë vetë";

$lang['Click_return_forumadmin'] = "Kliko %skëtu%s për tu kthyer tek administrimi i forumeve";


//
// Smiley Management
//
$lang['smiley_title'] = "Vegël për menaxhimin e figurinave";
$lang['smile_desc'] = "Nga ky panel ju mund të shtoni, hiqni dhe editoni figurinat që mund të përdoren nga përdoruesit.";

$lang['smiley_config'] = "Konfigurimi i figurinave";
$lang['smiley_code'] = "Kodi i figurinave";
$lang['smiley_url'] = "Adresa e figurinës";
$lang['smiley_emot'] = "Emocioni i figurinës";
$lang['smile_add'] = "Shto një figurinë";
$lang['Smile'] = "Figurina";
$lang['Emotion'] = "Emocioni";

$lang['Select_pak'] = "Zgjidh skedarin paketë (.pak)";
$lang['replace_existing'] = "Zëvendëso figurinën egzistuese";
$lang['keep_existing'] = "Mbaje figurinën egzistuese";
$lang['smiley_import_inst'] = "You duhet të dekompresoni skedarin me figurina dhe vendosni figurinat në direktorinë e duhur. Pastaj zgjidhni informacionin e duhur në këtë formular që të importoni skedarin e figurinave (Smiley Pack)";
$lang['smiley_import'] = "Importo skedarin e figurinave (Smiley Pack)";
$lang['choose_smile_pak'] = "Zgjidh një nga skedarët e figurinave ( .pak)";
$lang['import'] = "Importo figurina";
$lang['smile_conflicts'] = "Cfarë duhet bërë në rast konflikti";
$lang['del_existing_smileys'] = "Fshi figurinat ekzistuese përpara se të importosh";
$lang['import_smile_pack'] = "Importo skedarin e figurinave";
$lang['export_smile_pack'] = "Krijo një skedar figurinash";
$lang['export_smiles'] = "Për të krijuar një skedar figurinash prej figurinave ekzistuese, kliko %skëtu%s për të shkarkuar skedarin smiles.pak Ndryshojini emrin skedarin nqs doni, po mos i ndryshoni -file extension-. Pastaj krijoni një skedar .zip që përmban të gjitha imazhet e figurinave plus skedarin .pak.";

$lang['smiley_add_success'] = "Figurina u shtua në mënyrë të suksesshme.";
$lang['smiley_edit_success'] = "Figurina u ri-freskua në mënyrë të suksesshme.";
$lang['smiley_import_success'] = "Skedari i figurinave (Smiley Pack) u importua në mënyrë të suksesshme.";
$lang['smiley_del_success'] = "Figurina u hoq në mënyrë të suksesshme.";
$lang['Click_return_smileadmin'] = "Kliko %skëtu%s për ty kthyer tek Administrimi i Figurinave";


//
// User Management
//
$lang['User_admin'] = "Administrimi i Anëtarëve";
$lang['User_admin_explain'] = "Këtu mund të ndryshoni informacionin mbi anëtarët dhe disa opcione specifike. Bëni modifikimin e autorizimeve me anë të panelit të përshtatshëm në krahun e majtë të panelit.";

$lang['Look_up_user'] = "Analizo anëtarin";

$lang['Admin_user_fail'] = "Nuk u arrit të ri-freskohej profili i anëtarit.";
$lang['Admin_user_updated'] = "Profili i këtij anëtari u ri-freskua në mënyrë të suksesshme.";
$lang['Click_return_useradmin'] = "Kliko %skëtu%s për tu kthyer tek Administrimi i Anëtarëve";

$lang['User_delete'] = "Fshije këtë përdorues";
$lang['User_delete_explain'] = "Kliko këtu për ta fshirë këtë anëtar, ky veprim është i pakthyeshëm.";
$lang['User_deleted'] = "Anëtari u fshi në mënyrë të suksesshme.";

$lang['User_status'] = "Anëtari është aktiv";
$lang['User_allowpm'] = "Mund të dërgojë Mesazhe Private";
$lang['User_allowavatar'] = "Mund të shfaqë ikonën personale";

$lang['Admin_avatar_explain'] = "Këtu mund të shikoni dhe fshini ikonën aktuale personale të anëtarit.";

$lang['User_special'] = "Fusha speciale vetëm për administratorët.";
$lang['User_special_explain'] = "Këto fusha nuk mund të modifikohen nga anëtarët. Ju mund të vendosni statusin dhe veti të tjera që nuk i jepen anëtarëve.";


//
// Group Management
//
$lang['Group_administration'] = "Administrim i Grupeve";
$lang['Group_admin_explain'] = "Tek ky panel ju mund të administroni të gjithë grupet e përdoruesve. Fshi, krijo dhe modifiko grupet ekzistuese. Zgjidh moderatorët, hap/mbyll dhe vendos emrin dhe përshkrimin e grupit.";
$lang['Error_updating_groups'] = "Pati një problem gjatë ri-freskimit të grupeve";
$lang['Updated_group'] = "Grupi u ri-freskua me sukses";
$lang['Added_new_group'] = "Grupi u krijua me sukses";
$lang['Deleted_group'] = "Grupi u fshi me sukses";
$lang['New_group'] = "Krijo grup të ri";
$lang['Edit_group'] = "Modifiko grupin";
$lang['group_name'] = "Emri i grupit";
$lang['group_description'] = "Përshkrimi i grupit";
$lang['group_moderator'] = "Moderatori i grupit";
$lang['group_status'] = "Statusi i grupit";
$lang['group_open'] = "Grup i hapur";
$lang['group_closed'] = "Grup i mbyllur";
$lang['group_hidden'] = "Grup i fshehte";
$lang['group_delete'] = "Fshi grupin";
$lang['group_delete_check'] = "Fshi këtë grup";
$lang['submit_group_changes'] = "Paraqit ndryshimet";
$lang['reset_group_changes'] = "Pastro ndryshimet";
$lang['No_group_name'] = "Specifiko emrin e këtij grupi";
$lang['No_group_moderator'] = "Specifiko moderatorin e këtij grupi";
$lang['No_group_mode'] = "Specifiko një mënyrë për këtë grup, hapur ose mbyllur";
$lang['delete_group_moderator'] = "Fshi moderatorin e vjetër të grupit?";
$lang['delete_moderator_explain'] = "Nqs jeni duke ndryshuar moderatorin e grupit, vër një kryq tek kjo kuti për të hequr moderatorin e vjetër nga grupi. Ndryshe, lëre bosh dhe moderatori i vjetër do bëhet një anëtar i thjeshtë i grupit.";
$lang['Click_return_groupsadmin'] = "Kliko %skëtu%s për ty kthyer tek Administrimi i Grupeve";
$lang['Select_group'] = "Zgjidh një grup";
$lang['Look_up_group'] = "Analizo një grup";


//
// Prune Administration
//
$lang['Forum_Prune'] = "Shkurto forumin";
$lang['Forum_Prune_explain'] = "Ky veprim do fshijë cdo temë ku nuk është postuar brënda afatit që ju keni përcaktuar. Nqs nuk përcaktoni një numër, atëherë gjithë temat do fshihen. Megjithatë, temat ku ka votime të hapura dhe lajmërimet duhen fshirë mekanikisht.";
$lang['Do_Prune'] = "Bëje shkurtimin";
$lang['All_Forums'] = "Gjithë forumet";
$lang['Prune_topics_not_posted'] = "Shkurto temat pa pergjigje brënda";
$lang['Topics_pruned'] = "Temat e shkurtuara";
$lang['Posts_pruned'] = "Mesazhet e shkurtuara";
$lang['Prune_success'] = "Shkurtimi i forumeve u bë me sukses";


//
// Word censor
//
$lang['Words_title'] = "Censurimi i fjalëve";
$lang['Words_explain'] = "Nga ky panel ju mund të shtoni, modifikoni, dhe hiqni fjalë që do censurohen automatikisht. Gjithashtu, emrat e anëtarëve nuk do mund të përmbajnë fjalë të tilla. Wildcards (*) are accepted in the word field, eg. *test* will match detestable, test* would match testing, *test would match detest.";
$lang['Word'] = "Fjalë";
$lang['Edit_word_censor'] = "Modifiko censurën";
$lang['Replacement'] = "Zëvendësimi";
$lang['Add_new_word'] = "Shto një fjalë";
$lang['Update_word'] = "Ri-fresko censurën";

$lang['Must_enter_word'] = "You duhet të specifikoni një fjalë dhe zëvendësimin e saj";
$lang['No_word_selected'] = "Asnjë fjalë nuk është zgjedhur për modifikim";

$lang['Word_updated'] = "Censura e zgjedhur u ri-freskua me sukses";
$lang['Word_added'] = "Censura u shtua me sukses";
$lang['Word_removed'] = "Censura e zgjedhur u hoq me sukses";

$lang['Click_return_wordadmin'] = "Kliko %skëtu%s për tu kthyer tek Administrimi i Censurës";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Nga ky panel ju mund të dërgoni një e-mail tek të gjithë anëtarët e forumit, ose nje grupi specifik. Për të kryer këtë, një e-mail do i dërgohet adresës administrative të specifikuar, dhe një kopje karboni do u dërgohet gjithë marrësve. Kini parasysh se nqs i dërgoni e-mail nje grupi të madh personash, ky proces do kohë, kështu që prisni deri sa të njoftoheni se procesi mbaroi.";
$lang['Compose'] = "Harto"; 

$lang['Recipients'] = "Marrësit"; 
$lang['All_users'] = "Të gjithë anëtarët";

$lang['Email_successfull'] = "Mesazhi u dërgua";
$lang['Click_return_massemail'] = "Kliko %skëtu%s për tu kthyer tek paneli i E-mail në Masë";


//
// Ranks admin
//
$lang['Ranks_title'] = "Administrimi i Gradave";
$lang['Ranks_explain'] = "Ky formular mundëson shtimin, modifikimin dhe fshirjen e gradave. Gjithashtu, ju mund të krijoni grada të personalizuara të cilat i aplikohen përdoruesve nëpërmjet panelit të administrimit të përdoruesve.";

$lang['Add_new_rank'] = "Shto një gradë";

$lang['Rank_title'] = "Titulli i gradës";
$lang['Rank_special'] = "Cakto si gradë speciale";
$lang['Rank_minimum'] = "Minimumi i mesazheve";
$lang['Rank_maximum'] = "Maksimumi i mesazheve";
$lang['Rank_image'] = "Ikona e gradës";
$lang['Rank_image_explain'] = "Përdore këtë për të specifikuar ikonën që shoqëron gradën";

$lang['Must_select_rank'] = "Zgjidh një gradë";
$lang['No_assigned_rank'] = "Nuk është dhënë ndonjë gradë speciale";

$lang['Rank_updated'] = "Grada u ri-freskua me sukses";
$lang['Rank_added'] = "Grada u shtua me sukses";
$lang['Rank_removed'] = "Grada u fshi me sukses";
$lang['No_update_ranks'] = "The rank was successfully deleted, however, user accounts using this rank were not updated.  You will need to manually reset the rank on these accounts";

$lang['Click_return_rankadmin'] = "Kliko %skëtu%s për tu kthyer tek Administrimi i Gradave";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Kontrolli i emrave të ndaluar";
$lang['Disallow_explain'] = "Këtu mund të specifikoni emrat që nuk mund të përdoren. Disallowed usernames are allowed to contain a wildcard character of *. Kini parasysh se nuk mund të specifikoni një emër që është regjistruar tashmë. Fshini atë anëtar dhe pastaj ndalojeni atë fjalë.";

$lang['Delete_disallow'] = "Fshi";
$lang['Delete_disallow_title'] = "Hiq një nga emrat e ndaluar";
$lang['Delete_disallow_explain'] = "Heqja e një emri nga lista e ndaluar bëhet duke e zgjedhur atë tek kjo listë dhe klikuar paraqit.";

$lang['Add_disallow'] = "Shto";
$lang['Add_disallow_title'] = "Shto një emër të ndaluar";
$lang['Add_disallow_explain'] = "Ju mund të ndaloni një emër duke përdorur * në vend të cdo karakteri në atë pozicion.";

$lang['No_disallowed'] = "Asnjë emër i ndaluar";

$lang['Disallowed_deleted'] = "Emri i ndaluar u fshi me sukses";
$lang['Disallow_successful'] = "Emri i ndaluar u shtua me sukses";
$lang['Disallowed_already'] = "Emri që shtuat nuk mund të ndalohet. Ky emër ose ekziston në listën e emrave të ndaluar, ose ekziston në listën e censurës ose është në përdorim nga një anëtar.";

$lang['Click_return_disallowadmin'] = "Kliko %skëtu%s për tu kthyer tek Administrimi i emrave të ndaluar";


//
// Styles Admin
//
$lang['Styles_admin'] = "Administrimi i stileve";
$lang['Styles_explain'] = "Me anë të këtij paneli mund të shtoni, hiqni dhe menaxhoni (shabllonet dhe motivet) në dispozicion të anëtarëve.";
$lang['Styles_addnew_explain'] = "Lista e mëposhtme përmban të gjithë motivet që janë të mundshëm për shabllonet që keni. Artikujt në këtë listë nuk janë instaluar akoma në regjistrin e phpBB. Kliko butonin instalo për të instaluar një nga këto artikuj në databazë.";

$lang['Select_template'] = "Zgjidh një shabllon";

$lang['Style'] = "Stili";
$lang['Template'] = "Shabllon";
$lang['Install'] = "Instalo";
$lang['Download'] = "Shkarko";

$lang['Edit_theme'] = "Modifiko motivin";
$lang['Edit_theme_explain'] = "Në formularin më poshtë modifiko vetitë e motivit që keni zgjedhur.";

$lang['Create_theme'] = "Krijo motiv";
$lang['Create_theme_explain'] = "Përdor formularin e mëposhtëm për të krijuar një motiv për shabllonin e zgjedhur. Kur zgjidhni ngjyrat (për të cilat duhen përdorur numrat me bazë 16 --hexadecimal--) mos vendosni shenjën #. psh. #CC00BB është gabim.";

$lang['Export_themes'] = "Eksporto motivet";
$lang['Export_explain'] = "Ky panel mundëson eksportimin e të dhënave të motivit për shabllonin e zgjedhur. Zgjidh shabllonin nga lista e mëposhtme dhe phpBB do krijojë automatikisht skedarin për konfigurimin e motivit dhe do provojë ta ruajë në direktorinë ku shablloni i zgjedhur rri. Nqs nuk mund ta ruani skedarin atje, do keni mundësinë për ta shkarkuar në kompjuterin tuaj. Në mënyrë që skedari të vendoset në direktorinë e shabllonit, webserveri duhet të ketë autorizim për të shkruar në atë direktori. Për më shumë info, shiko manualin e përdorimit.";

$lang['Theme_installed'] = "Motivi i zgjedhur u instalua me sukses";
$lang['Style_removed'] = "Stili i zgjedhur u hoq nga regjistri. Për ta hequr këtë stil përfundimisht nga sistemi juaj, fshijeni këtë stil nga direktoria e shablloneve.";
$lang['Theme_info_saved'] = "Informacioni i motivit për shabllonin e zgjedhur u ruajt. Rivendosni autorizimet në nivelin lexim-vetëm (read-only) për theme_info.cfg ";
$lang['Theme_updated'] = "Motivi i zgjedhur u ri-freskua. Tashti duhet të eksportoni vetitë e reja të motivit";
$lang['Theme_created'] = "Motivi u krijua. Tashti duhet të eskportoni motivin e ri tek skedari i konfigurimit për ruajtje.";

$lang['Confirm_delete_style'] = "Jeni i sigurtë për fshirjen e këtij stili";

$lang['Download_theme_cfg'] = "Eksportuesi nuk mundi të shkruajë skedarin e informacionit të motivit. Kliko butonin e meposhtëm për ta shkarkuar këtë skedar. Pasi ta keni shkarkuar, transferojeni tek direktoria që përmban skedarët e shablloneve. You mund ti arkivoni skedarët dhe ti shpërndani për përdorim diku tjetër.";
$lang['No_themes'] = "Shablloni që zgjodhët nuk ka asnjë motiv të bashkangjitur. Për të krijuar një motiv të ri kliko butonin Krijo në anën e majtë.";
$lang['No_template_dir'] = "Direktoria e shablloneve nuk hapet. Webserveri nuk mund ta lexojë ose direktoria nuk ekziston.";
$lang['Cannot_remove_style'] = "Stili i zgjdhur nuk mund të fshihet sepse është stili i paracaktuar i forumi. Ndryshoni stilin e paracaktuar dh pastaj provoni nga e para";
$lang['Style_exists'] = "Emri i zgjedhur për këtë stil ekziston. Zgjidhni një emër tjetër";

$lang['Click_return_styleadmin'] = "Kliko %skëtu%s për tu kthyer tek Administratori i Stileve";

$lang['Theme_settings'] = "Vetitë e motivit";
$lang['Theme_element'] = "Elementi i motivit";
$lang['Simple_name'] = "Emër i thjeshtë";
$lang['Value'] = "Vlera";
$lang['Save_Settings'] = "Regjistro vetitë";

$lang['Stylesheet'] = "CSS Stylesheet";
$lang['Background_image'] = "Background Image";
$lang['Background_color'] = "Background Colour";
$lang['Theme_name'] = "Emri i Motivit";
$lang['Link_color'] = "Link Colour";
$lang['Text_color'] = "Ngjyra e tekstit";
$lang['VLink_color'] = "Visited Link Colour";
$lang['ALink_color'] = "Active Link Colour";
$lang['HLink_color'] = "Hover Link Colour";
$lang['Tr_color1'] = "Table Row Colour 1";
$lang['Tr_color2'] = "Table Row Colour 2";
$lang['Tr_color3'] = "Table Row Colour 3";
$lang['Tr_class1'] = "Table Row Class 1";
$lang['Tr_class2'] = "Table Row Class 2";
$lang['Tr_class3'] = "Table Row Class 3";
$lang['Th_color1'] = "Table Header Colour 1";
$lang['Th_color2'] = "Table Header Colour 2";
$lang['Th_color3'] = "Table Header Colour 3";
$lang['Th_class1'] = "Table Header Class 1";
$lang['Th_class2'] = "Table Header Class 2";
$lang['Th_class3'] = "Table Header Class 3";
$lang['Td_color1'] = "Table Cell Colour 1";
$lang['Td_color2'] = "Table Cell Colour 2";
$lang['Td_color3'] = "Table Cell Colour 3";
$lang['Td_class1'] = "Table Cell Class 1";
$lang['Td_class2'] = "Table Cell Class 2";
$lang['Td_class3'] = "Table Cell Class 3";
$lang['fontface1'] = "Font Face 1";
$lang['fontface2'] = "Font Face 2";
$lang['fontface3'] = "Font Face 3";
$lang['fontsize1'] = "Font Size 1";
$lang['fontsize2'] = "Font Size 2";
$lang['fontsize3'] = "Font Size 3";
$lang['fontcolor1'] = "Font Colour 1";
$lang['fontcolor2'] = "Font Colour 2";
$lang['fontcolor3'] = "Font Colour 3";
$lang['span_class1'] = "Span Class 1";
$lang['span_class2'] = "Span Class 2";
$lang['span_class3'] = "Span Class 3";
$lang['img_poll_size'] = "Polling Image Size [px]";
$lang['img_pm_size'] = "Private Message Status size [px]";


//
// Install Process
//
$lang['Welcome_install'] = "Mirësevini tek instaluesi i phpBB v.2";
$lang['Initial_config'] = "Konfigurimi elementar";
$lang['DB_config'] = "Konfigurimi i regjistrit";
$lang['Admin_config'] = "Konfigurimi i administratorit";
$lang['continue_upgrade'] = "Once you have downloaded your config file to your local machine you may\"Continue Upgrade\" button below to move forward with the upgrade process. Please wait to upload the config file until the upgrade process is complete.";
$lang['upgrade_submit'] = "Continue Upgrade";

$lang['Installer_Error'] = "An error has occurred during installation";
$lang['Previous_Install'] = "A previous installation has been detected";
$lang['Install_db_error'] = "An error occurred trying to update the database";

$lang['Re_install'] = "Your previous installation is still active. <br /><br />If you would like to re-install phpBB 2 you should click the Yes button below. Please be aware that doing so will destroy all existing data, no backups will be made! The administrator username and password you have used to login in to the board will be re-created after the re-installation, no other settings will be retained. <br /><br />Think carefully before pressing Yes!";

$lang['Inst_Step_0'] = "Thank you for choosing phpBB 2. In order to complete this install please fill out the details requested below. Please note that the database you install into should already exist. If you are installing to a database that uses ODBC, e.g. MS Access you should first create a DSN for it before proceeding.";

$lang['Start_Install'] = "Start Install";
$lang['Finish_Install'] = "Finish Installation";

$lang['Default_lang'] = "Default board language";
$lang['DB_Host'] = "Database Server Hostname / DSN";
$lang['DB_Name'] = "Your Database Name";
$lang['DB_Username'] = "Database Username";
$lang['DB_Password'] = "Database Password";
$lang['Database'] = "Your Database";
$lang['Install_lang'] = "Choose Language for Installation";
$lang['dbms'] = "Database Type";
$lang['Table_Prefix'] = "Prefix for tables in database";
$lang['Admin_Username'] = "Administrator Username";
$lang['Admin_Password'] = "Administrator Password";
$lang['Admin_Password_confirm'] = "Administrator Password [ Confirm ]";

$lang['Inst_Step_2'] = "Your admin username has been created. At this point your basic installation is complete. You will now be taken to a screen which will allow you to administer your new installation. Please be sure to check the General Configuration details and make any required changes. Thank you for choosing phpBB 2.";

$lang['Unwriteable_config'] = "Your config file is un-writeable at present. A copy of the config file will be downloaded to your when you click the button below. You should upload this file to the same directory as phpBB 2. Once this is done you should log in using the administrator name and password you provided on the previous form and visit the admin control centre (a link will appear at the bottom of each screen once logged in) to check the general configuration. Thank you for choosing phpBB 2.";
$lang['Download_config'] = "Download Config";

$lang['ftp_choose'] = "Choose Download Method";
$lang['ftp_option'] = "<br />Since FTP extensions are enabled in this version of PHP you may also be given the option of first trying to automatically ftp the config file into place.";
$lang['ftp_instructs'] = "You have chosen to ftp the file to the account containing phpBB 2 automatically. Please enter the information below to facilitate this process. Note that the FTP path should be the exact path via ftp to your phpBB2 installation as if you were ftping to it using any normal client.";
$lang['ftp_info'] = "Enter Your FTP Information";
$lang['Attempt_ftp'] = "Attempt to ftp config file into place";
$lang['Send_file'] = "Just send the file to me and I'll ftp it manually";
$lang['ftp_path'] = "FTP path to phpBB 2";
$lang['ftp_username'] = "Your FTP Username";
$lang['ftp_password'] = "Your FTP Password";
$lang['Transfer_config'] = "Start Transfer";
$lang['NoFTP_config'] = "The attempt to ftp the config file into place failed. Please download the config file and ftp it into place manually.";

$lang['Install'] = "Install";
$lang['Upgrade'] = "Upgrade";


$lang['Install_Method'] = "Choose your installation method";

$lang['Install_No_Ext'] = "The php configuration on your server doesn't support the database type that you choose";

$lang['Install_No_PCRE'] = "phpBB2 Requires the Perl-Compatible Regular Expressions Module for php which your php configuration doesn't appear to support!";

//
// That's all Folks!
// -------------------------------------------------

?>