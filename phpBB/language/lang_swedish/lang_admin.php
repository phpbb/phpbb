<?php

/***************************************************************************
 *                            lang_admin.php [Swedish]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group and (C) 2003 Jonathan Gulbrandsen
 *     email                : support@phpbb.com (translator:virtuality@carlssonplanet.com)
 *
 *     $Id: lang_admin.php,v 1.1 2010/10/10 15:09:43 orynider Exp $
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

//  *************************************
//  First, original Swedish translation by:
//
//	Marcus Svensson
//      admin@world-of-war.com
//      http://www.world-of-war.com
//	-------------------------------------
// 	Janĺke Rönnblom
//	jan-ake.ronnblom@skeria.skelleftea.se
//	-------------------------------------
//	Bruce
//	bruce@webway.se
//	-------------------------------------
//      Jakob Persson
//      jakob.persson@iname.com
//      http://www.jakob-persson.com
//
//  *************************************
//  Maintained and kept up-to-date by:
//
//  Jonathan Gulbrandsen (Virtuality)
//  virtuality@carlssonplanet.com
//  http://www.carlssonplanet.com
//  *************************************
//

/* CONTRIBUTORS
	XXXX-XX-XX
                Orginal translation to Swedish by Marcus Svensson, Janĺke Rönnblom, Bruce and Jakob Persson

        2003-07-11 Virtuality aka Jonathan Gulbrandsen (virtuality@carlssonplanet.com)
                Updated the language file to phpBB2.0.5

        2003-08-13 Virtuality aka Jonathan Gulbrandsen (virtuality@carlssonplanet.com)
                Updated to 2.0.6, no changes. Lots of "swinglish", grammars and mispellings fixed
*/

//
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "Generell admin";
$lang['Users'] = "Användaradmin";
$lang['Groups'] = "Gruppadmin";
$lang['Forums'] = "Forumadmin";
$lang['Styles'] = "Stiladmin";

$lang['Configuration'] = "Konfiguration";
$lang['Permissions'] = "Rättigheter";
$lang['Manage'] = "Hantering";
$lang['Disallow'] = "Förbjuda namn";
$lang['Prune'] = "Reducering";
$lang['Mass_Email'] = "Mass email";
$lang['Ranks'] = "Ranker";
$lang['Smilies'] = "Smilies";
$lang['Ban_Management'] = "Bannlys";
$lang['Word_Censor'] = "Ordcensur";
$lang['Export'] = "Exportera";
$lang['Create_new'] = "Skapa";
$lang['Add_new'] = "Lägg till";
$lang['Backup_DB'] = "Backup av databas";
$lang['Restore_DB'] = "Ĺterställ databas";


//
// Index
//
$lang['Admin'] = "Administration";
$lang['Not_admin'] = "Du har inte rättighet att administrera detta forum";
$lang['Welcome_phpBB'] = "Välkommen till phpBB";
$lang['Admin_intro'] = "Tack för att du har valt phpBB som din forumlösning. Den här sidan ger dig en snabb överblick över all möjlig statistik om ditt forum. Du kan komma tillbaka till den här sidan genom att klicka pĺ <u>Admin index</u> länken pĺ den vänstra sidan. För att komma tillbaka till indexet till forumet tryck pĺ phpBB logon, som finns i den vänstra panelen. De övriga länkarna pĺ vänster hand lĺter dig kontrollera alla aspekter pĺ hur ditt forum presenteras, varje sidan har intruktioner pĺ hur du använder verktygen.";
$lang['Main_index'] = "Forumindex";
$lang['Forum_stats'] = "Forumstatistik";
$lang['Admin_Index'] = "Admin index";
$lang['Preview_forum'] = "Förhandsgranska forum";

$lang['Click_return_admin_index'] = "Klicka %sHär%s för att ĺtervända till Admin index";

$lang['Statistic'] = "Statistik";
$lang['Value'] = "Värde";
$lang['Number_posts'] = "Antal inlägg";
$lang['Posts_per_day'] = "Inlägg per dag";
$lang['Number_topics'] = "Antal ämnen";
$lang['Topics_per_day'] = "Ämnen per dag";
$lang['Number_users'] = "Antal användare";
$lang['Users_per_day'] = "Användare per dag";
$lang['Board_started'] = "Start av forum";
$lang['Avatar_dir_size'] = "Avatarkatalogens storlek";
$lang['Database_size'] = "Databasstorlek";
$lang['Gzip_compression'] ="Gzip komprimering";
$lang['Not_available'] = "Inte tillgänglig";

$lang['ON'] = "PĹ"; // This is for GZip compression
$lang['OFF'] = "AV";


//
// DB Utils
//
$lang['Database_Utilities'] = "Databasverktyg";

$lang['Restore'] = "Ĺterställ";
$lang['Backup'] = "Backup";
$lang['Restore_explain'] = "Detta kommer att utföra en fullständig ĺterställning av alla phpBB tabeller frĺn en sparad fil. Om din server stödjer det kan du ladda upp en gzip komprimerad text fil vilken kommer att dekomprimeras. <b>VARNING</b>Detta kommer att skriva över all existerande data. Ĺterställningen kan ta en lĺng tid att utföra men lämna inte denna sida förrän den är färdig.";
$lang['Backup_explain'] = "Här kan du ta backup pĺ alla dina phpBB relaterade data. Om du har andra egna tabeller i samma databas som phpBB som du ocksĺ vill säkerhetskopiera sĺ ange deras namn separerad med komman i \"Övriga tabeller\"-rutan nedanför Om din server stöjder det kan du ocksĺ gzip komprimera filen för att minska storleken innan du laddar ner den.";

$lang['Backup_options'] = "Backup alternativ";
$lang['Start_backup'] = "Starta backup";
$lang['Full_backup'] = "Fullständig backup";
$lang['Structure_backup'] = "Enbart backup av strukturen";
$lang['Data_backup'] = "Backup av endast data";
$lang['Additional_tables'] = "Övriga tabeller";
$lang['Gzip_compress'] = "Gzip komprimera filen";
$lang['Select_file'] = "Välj en fil";
$lang['Start_Restore'] = "Starta ĺterställningen";

$lang['Restore_success'] = "Databasen är ĺterställd utan problem.<br /><br />Ditt forum bör vara tillbaka i samma skick som när du gjorde backupen.";
$lang['Backup_download'] = "Din nedladdning kommer att starta snart, var god vänta tills den startar";
$lang['Backups_not_supported'] = "Tyvärr sĺ stöds inte backup än av ditt databassystem";

$lang['Restore_Error_uploading'] = "Fel när filen skulle laddas upp.";
$lang['Restore_Error_filename'] = "Problem med filnamnet, försök med en annan fil";
$lang['Restore_Error_decompress'] = "Kunde inte dekomprimera gzip fil, försök ladda upp en textversion";
$lang['Restore_Error_no_file'] = "Ingen fil är uppladdad";


//
// Auth pages
//
$lang['Select_a_User'] = "Välj en användare";
$lang['Select_a_Group'] = "Välj en grupp";
$lang['Select_a_Forum'] = "Välj ett forum";
$lang['Auth_Control_User'] = "Användarrättigheter";
$lang['Auth_Control_Group'] = "Grupprättigheter";
$lang['Auth_Control_Forum'] = "Forumrättigheter";
$lang['Look_up_User'] = "Slĺ upp en användare";
$lang['Look_up_Group'] = "Slĺ upp en grupp";
$lang['Look_up_Forum'] = "Slĺ upp ett forum";

$lang['Group_auth_explain'] = "Här kan du ändra rättigheter och moderatorstatus för varje grupp. Glöm inte att fastän du ändrar grupprättigheten att användarens egna rättigheter fortfarande kan ge dom access till forum, m.m. Du kommer att fĺ en varning i sĺ fall.";
$lang['User_auth_explain'] = "Här kan du ändra rättigheter och moderator status för varje enskild användare. Glöm inte att fastän du ändrar grupp rättigheten att användarens egna rättigheter fortfarande kan ge dom access till forum, m.m. Du kommer att fĺ en varning i sĺ fall.";
$lang['Forum_auth_explain'] = "Här kan du ändra auktorisionsnivĺer för varje forum. Du har bĺde en enkel och en avancerad metod för att göra detta, avancerad ger dig större kontroll över varje forums funktioner. Kom ihĺg att när du ändrar rättigheterna till forumet sĺ pĺverkar du vilka användare som kan utföra olika funktioner i forumet.";

$lang['Simple_mode'] = "Enkelt läge";
$lang['Advanced_mode'] = "Avancerat läge";
$lang['Moderator_status'] = "Moderator status";

$lang['Allowed_Access'] = "Tillĺt tillträde";
$lang['Disallowed_Access'] = "Neka tillträde";
$lang['Is_Moderator'] = "Är Moderator";
$lang['Not_Moderator'] = "Är inte moderator";

$lang['Conflict_warning'] = "Varning! Auktorisationskonflikt";
$lang['Conflict_access_userauth'] = "Denna användare har fortfarande tillgĺng till detta forum via gruppmedlemskap. Du kanske vill ändra grupprättigheterna eller ta bort denna användare frĺn gruppen för att förhindra att de har tillträde. Gruppens rättigheter (och berörda forum) listas nedan.";
$lang['Conflict_mod_userauth'] = "Användaren har fortfarande moderatorrättigheter till forumet via gruppmedlemskap. Du kan antingen ändra grupprättigheterna eller ta bort denna användare frĺn gruppen för att förhindra att de har moderatorrättigheter. Gruppens rättigheter (och berörda forum) listas nedan.";

$lang['Conflict_access_groupauth'] = "Följande användare har fortfarande ĺtkomsträttigheter till detta forum via deras användarrättigheter. Du kanske vill ändra användarrättigheterna för att förhindra dem frĺn att ha ĺtkomst till forumet. Användarens rättigheter (och berörda forum) listas nedan.";
$lang['Conflict_mod_groupauth'] = "Följande användare har fortfarande moderatorrättigheter till forumet via användarrättigheter. Du kanske vill ändra användarrättigheterna för att förhindra dem frĺn att ha ĺtkomst till forumet. Användarens rättigheter (och berörda forum) listas nedan.";

$lang['Public'] = "Publik";
$lang['Private'] = "Privat";
$lang['Registered'] = "Registrerad";
$lang['Administrators'] = "Administratörer";
$lang['Hidden'] = "Dold";

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = "ALLA";
$lang['Forum_REG'] = "REG";
$lang['Forum_PRIVATE'] = "PRIVAT";
$lang['Forum_MOD'] = "MOD";
$lang['Forum_ADMIN'] = "ADMIN";

$lang['View'] = "Visa";
$lang['Read'] = "Läs";
$lang['Post'] = "Inlägg";
$lang['Reply'] = "Svara";
$lang['Edit'] = "Ändra";
$lang['Delete'] = "Radera";
$lang['Sticky'] = "Klibbig";
$lang['Announce'] = "Viktigt meddelande";
$lang['Vote'] = "Rösta";
$lang['Pollcreate'] = "Skapa omröstning";

$lang['Permissions'] = "Rättigheter";
$lang['Simple_Permission'] = "Enkla rättigheter";

$lang['User_Level'] = "Användarnivĺ";
$lang['Auth_User'] = "Användare";
$lang['Auth_Admin'] = "Administratör";
$lang['Group_memberships'] = "Gruppmedlemskap";
$lang['Usergroup_members'] = "Den här gruppen har följande medlemmar";

$lang['Forum_auth_updated'] = "Forumrättigeheterna är uppdaterade";
$lang['User_auth_updated'] = "Användarrättigeheterna är uppdaterade";
$lang['Group_auth_updated'] = "Grupprättigeheterna är uppdaterade";

$lang['Auth_updated'] = "Rättigheterna är uppdaterade";
$lang['Click_return_userauth'] = "Klicka %shär%s för att ĺtergĺ till användarrättigheter";
$lang['Click_return_groupauth'] = "Klicka %shär%s för att ĺtergĺ till grupprättigheter";
$lang['Click_return_forumauth'] = "Klicka %shär%s  för att ĺtergĺ till forumrättigheter";


//
// Banning
//
$lang['Ban_control'] = "Bannlysningskontroll";
$lang['Ban_explain'] = "Här sköter du bannlysningen av användare. Du kan uppnĺ detta genom att bannlysa vilket som helst eller alla av en användare eller en särskild eller ett omrĺde av IP adresser eller värdnamn. Dessa metoder förhindrar en användare frĺn att nĺ index sidan pĺ ditt forum. För att förhindra en användare att registrera under ett annat användarnamn kan du ocksĺ ange en bannlyst epostadress. Notera att bannlysa enbart en epostadress inte kommer att förhindra användaren frĺn att logga pĺ eller skriva ett inlägg pĺ ditt forum, du bör använda nĺgon av de tvĺ första metoderna för att uppnĺ det.";
$lang['Ban_explain_warn'] = "Notera att genom att ange ett omrĺde av IP adresser sĺ resulterar det i att alla adresser mellan start och slut läggs till i banlysningslistan. En ansträngning kommer att göras för att minska antalet adresser som läggs in i databasen genom att introducera jokertecken automatiskt där det är lämpligt. Om du verkligen mĺste ange ett omrĺde av adresser sĺ försök hĺlla det litet eller ännu bättre försöka att explicit ange enstaka adresserna.";

$lang['Select_username'] = "Välj ett användarnamn";
$lang['Select_ip'] = "Välj en IP adress";
$lang['Select_email'] = "Välj en e-post adress";

$lang['Ban_username'] = "Bannlys en eller flera användare";
$lang['Ban_username_explain'] = 'Du kan banna flera användare pĺ en gĺng genom att använda den rätta kombinationen mellan mus och tangentbord';

$lang['Ban_IP'] = "Bannlys en eller flera IP adresser eller värdnamn";
$lang['IP_hostname'] = "IP adresser eller värdnamn";
$lang['Ban_IP_explain'] = "För att specifiera flera olika IP adresser eller värdnamn, skilj dem ĺt med kommatecken. För att specifiera en rad olika IP adresser separera början och slutet med ett bindesstreck(-), för att specifiera ett wildcard (vad som helst) använd *";

$lang['Ban_email'] = "Bannlys en eller flera epost adresser";
$lang['Ban_email_explain'] = "För att specificera mer än en e-post adress, skilj dem ĺt med kommatecken. För att specifiera ett wildcard (vad som helst) namn använd *, till exempel *@hotmail.com";

$lang['Unban_username'] = "Häv en eller flera bannlysta användare";
$lang['Unban_username_explain'] = "Du kan ta bort flera bannlysningar samtidigt genom att använda den ändamĺlsenliga kombinationen av mus och tangenbord för din dator och webläsare.";

$lang['Unban_IP'] = "Häv en eller flera bannlysta IP adresser";
$lang['Unban_IP_explain'] = "Du kan ta bort flera bannlysningar av IP adresser samtidigt genom att använda den ändamĺlsenliga kombinationen av mus och tangenbord för din dator och webläsare.";

$lang['Unban_email'] = "Häv en eller flera bannlysta e-post adresser";
$lang['Unban_email_explain'] = "Du kan ta bort flera bannlysningar av e-post adresser samtidigt genom att använda den ändamĺlsenliga kombinationen av mus och tangenbord för din dator och webläsare.";

$lang['No_banned_users'] = "Inga bannlysta användarnamn";
$lang['No_banned_ip'] = "Inga bannlysta IP adresser";
$lang['No_banned_email'] = "Inga bannlysta e-post adresser";

$lang['Ban_update_sucessful'] = "Banlistan har blivit uppdaterad.";
$lang['Click_return_banadmin'] = "Klicka %shär%s för att ĺtervända till bannlysningskontrollen";


//
// Configuration
//
$lang['General_Config'] = "Generell Konfiguration";
$lang['Config_explain'] = "Formuläret här ger dig möjlighet att ändra alla allmänna foruminställningar. För användar och forumkonfiguration sĺ änvänd de relaterade länkarna pĺ vänster sida.";

$lang['Click_return_config'] = "Klicka %shär%s för att ĺtervända till Generell Konfiguration";

$lang['General_settings'] = "Generella foruminställningar";
$lang['Server_name'] = "Domännamn";
$lang['Server_name_explain'] = "Domännamnet som forumet körs frĺn";
$lang['Script_path'] = "Skriptsökväg";
$lang['Script_path_explain'] = "Sökvägen där phpBB2 är placerat under domännamnet (domännamn.com/sökväg)";
$lang['Server_port'] = "Serverport";
$lang['Server_port_explain'] = "Porten som servern körs pĺ, vanligtvis 80, ändra bara om porten är annorlunda";
$lang['Site_name'] = "Sitenamn";
$lang['Site_desc'] = "Sitebeskrivning";
$lang['Board_disable'] = "Stäng av forumet";
$lang['Board_disable_explain'] = "Detta gör forumet otillgängligt för användarna. Logga inte ut när du har deaktiverat forumet, du kommer inte att kunna logga in igen!";
$lang['Acct_activation'] = "Aktivera kontoaktivering";
$lang['Acc_None'] = "Ingen"; // These three entries are the type of activation
$lang['Acc_User'] = "Användare";
$lang['Acc_Admin'] = "Administratör";

$lang['Abilities_settings'] = "Användar och foruminställningar";
$lang['Max_poll_options'] = "Maximalt antal val för omröstningar";
$lang['Flood_Interval'] = "Tid mellan inlägg";
$lang['Flood_Interval_explain'] = "Antal sekunder en användare mĺste vänta mellan inläggen";
$lang['Board_email_form'] = "E-posta användare via forumet";
$lang['Board_email_form_explain'] = "Användare kan skicka e-post till varandra via forumet";
$lang['Topics_per_page'] = "Ämnen per sida";
$lang['Posts_per_page'] = "Inlägg per sida";
$lang['Hot_threshold'] = "Antal inlägg för populäritet";
$lang['Default_style'] = "Standardstil";
$lang['Override_style'] = "Ĺsidosätt användarstil";
$lang['Override_style_explain'] = "Ersätter användarens stil med standard stilen";
$lang['Default_language'] = "Standardsprĺk";
$lang['Date_format'] = "Datumformat";
$lang['System_timezone'] = "Systemets tidszon";
$lang['Enable_gzip'] = "Aktivera GZip Kompression";
$lang['Enable_prune'] = "Aktivera forum reducering";
$lang['Allow_HTML'] = "Tillĺt HTML";
$lang['Allow_BBCode'] = "Tillĺt BBCode";
$lang['Allowed_tags'] = "Tillĺtna HTML taggar";
$lang['Allowed_tags_explain'] = "Separera taggarna med komma";
$lang['Allow_smilies'] = "Tillĺt smilies";
$lang['Smilies_path'] = "Smilies sökväg";
$lang['Smilies_path_explain'] = "Sökväg under din phpBB root katalog, t.ex images/smilies";
$lang['Allow_sig'] = "Tillĺt signaturer";
$lang['Max_sig_length'] = "Maximal längd pĺ signaturen";
$lang['Max_sig_length_explain'] = "Maximalt antal tecken i användarens signatur";
$lang['Allow_name_change'] = "Tillĺt ändring av användarnamn";

$lang['Avatar_settings'] = "Avatarinställningar";
$lang['Allow_local'] = "Aktivera galleriavatarer";
$lang['Allow_remote'] = "Aktivera fjärravatarer";
$lang['Allow_remote_explain'] = "Gör det möjligt att länka till avatarer pĺ andra websiter";
$lang['Allow_upload'] = "Aktivera Avataruppladdning";
$lang['Max_filesize'] = "Maximal Avatar filstorlek";
$lang['Max_filesize_explain'] = "För avatarer som laddas upp";
$lang['Max_avatar_size'] = "Maximal Avatar storlek";
$lang['Max_avatar_size_explain'] = "(Höjd x Bredd i pixelar)";
$lang['Avatar_storage_path'] = "Avatar sökväg";
$lang['Avatar_storage_path_explain'] = "Sökväg under din phpBB root katalog, t.ex. images/avatars";
$lang['Avatar_gallery_path'] = "Avatar galleriets sökväg";
$lang['Avatar_gallery_path_explain'] = "Sökväg under din phpBB root katalog för för-laddade bilder, t.ex. images/avatars/gallery";

$lang['COPPA_settings'] = "COPPA inställningar";
$lang['COPPA_fax'] = "COPPA fax nummer";
$lang['COPPA_mail'] = "COPPA adress";
$lang['COPPA_mail_explain'] = "Detta är adressen dit föräldrar ska skicka registreringsforumlären för COPPA";

$lang['Email_settings'] = "E-post inställningar";
$lang['Admin_email'] = "Admin e-post adress";
$lang['Email_sig'] = "E-post signatur";
$lang['Email_sig_explain'] = "Denna text kommer att bifogas i all e-post som forumet skickar.";
$lang['Use_SMTP'] = "Använd SMTP server för epost";
$lang['Use_SMTP_explain'] = "Säg ja om du vill eller mĺste skicka e-post via en angiven server istället för via den lokala e-post funktionen";
$lang['SMTP_server'] = "SMTP server Adress";
$lang['SMTP_username'] = "SMTP Användarnamn";
$lang['SMTP_username_explain'] = "Skriv endast in ett användarnamn om din smtp server behöver det";
$lang['SMTP_password'] = "SMTP Lösenord";
$lang['SMTP_password_explain'] = "Skriv endast in ett lösenord om din smtp server behöver det";

$lang['Disable_privmsg'] = "Personliga Meddelandehantering";
$lang['Inbox_limits'] = "Max inlägg i Inlĺdan";
$lang['Sentbox_limits'] = "Max inlägg i Skickade brev";
$lang['Savebox_limits'] = "Max inlägg i Sparade brev";

$lang['Cookie_settings'] = "Cookie/session inställningar";
$lang['Cookie_settings_explain'] = "Detta styr hur cookien som skickas till webläsaren är definerad. I de flesta fall sĺ är standard inställningarna tillräckliga. Om du behöver ändra dessa sĺ gör det med varsamhet, felaktiga inställningar kan hindra användare frĺn att logga in.";
$lang['Cookie_settings_explain_mxp'] = "Observera: Om du använder phpBB-sessioner, används ej dessa interna inställningar.";
$lang['Cookie_domain'] = "Cookie domän";
$lang['Cookie_name'] = "Cookie namn";
$lang['Cookie_path'] = "Cookie sökväg";
$lang['Cookie_secure'] = "Cookie säkerhet [ https ]";
$lang['Cookie_secure_explain'] = "Om servern körs via SSL aktivera det här, annars lĺt det vara inaktiverat";
$lang['Session_length'] = "Sessionslängd [ sekunder ]";

// Visual Confirmation
$lang['Visual_confirm'] = 'Aktivera Visuell Bekräftning';
$lang['Visual_confirm_explain'] = 'Tvingar användare att ange en kod som visas genom bilder vid registrering.';

// Autologin Keys - added 2.0.18
$lang['Allow_autologin'] = 'Tillĺt automatisk inloggning';
$lang['Allow_autologin_explain'] = 'Bestämmer om användare kan använda automatisk inloggning';
$lang['Autologin_time'] = 'Automatisk inloggningsvaliditiet';
$lang['Autologin_time_explain'] = 'Hur länge den automatiska inloggningsnyckeln är aktuell (i dagar). Sätt till noll för att inaktivera tidsbegränsning.';

// Search Flood Control - added 2.0.20
$lang['Search_Flood_Interval'] = 'Search Flood intervall';
$lang['Search_Flood_Interval_explain'] = 'Antal sekunder användaren mĺste vänta mellan sökningar';

//
// Forum Management
//
$lang['Forum_admin'] = "Forum Administration";
$lang['Forum_admin_explain'] = "Frĺn denna panel kan du lägga till, radera, ändra, sortera och synkronisera katagorier och forum";
$lang['Edit_forum'] = "Ändra forum";
$lang['Create_forum'] = "Skapa nytt forum";
$lang['Create_category'] = "Skapa ny kategori";
$lang['Remove'] = "Radera";
$lang['Action'] = "Handling";
$lang['Update_order'] = "Uppdatera sorteringsordning";
$lang['Config_updated'] = "Forumkonfigurationen är uppdaterad";
$lang['Edit'] = "Ändra";
$lang['Delete'] = "Radera";
$lang['Move_up'] = "Flytta upp";
$lang['Move_down'] = "Flytta ner";
$lang['Resync'] = "Synkronisera";
$lang['No_mode'] = "Inget mode angavs";
$lang['Forum_edit_delete_explain'] = "Forumläret under lĺter dig skräddarsy alla allmänna foruminställningar. Använd relaterad länkar pĺ vänster sida för användar och forum konfiguraration";

$lang['Move_contents'] = "Flytta allt innehĺll";
$lang['Forum_delete'] = "Radera forum";
$lang['Forum_delete_explain'] = "Forumläret under lĺter dig radera ett forum (eller kategori) och tala om var du vill flytta alla ämnen (eller forum) som det innehöll.";

$lang['Status_locked'] = 'Lĺst';
$lang['Status_unlocked'] = 'Öppen';
$lang['Forum_settings'] = "Generella foruminställningar";
$lang['Forum_name'] = "Forumnamm";
$lang['Forum_desc'] = "Beskrivning";
$lang['Forum_status'] = "Forum status";
$lang['Forum_pruning'] = "Autoreducering";

$lang['prune_freq'] = 'Sök efter gamla ämnen varje';
$lang['prune_days'] = "Ta bort ämnen som inte har svarats pĺ efter";
$lang['Set_prune_data'] = "Du har aktiverat autoreducering för detta forum men har inte satt en frekvens eller antal dagar för reducering. Gĺ tillbaka och sätt detta";

$lang['Move_and_Delete'] = "Flytta och radera";

$lang['Delete_all_posts'] = "Radera alla inlägg";
$lang['Nowhere_to_move'] = "Ingenstans att flytta till";

$lang['Edit_Category'] = "Ändra kategori";
$lang['Edit_Category_explain'] = "Använda detta forumlär för att modifiera kategorinamnet.";

$lang['Forums_updated'] = "Forum och kategori-information är uppdaterad";

$lang['Must_delete_forums'] = "Du mĺste radera alla forum innan du kan radera denna kategori";

$lang['Click_return_forumadmin'] = "Klicka %shär%s för att ĺtergĺ till Forum Administrationen";


//
// Smiley Management
//
$lang['smiley_title'] = "Smiles redigering";
$lang['smile_desc'] = "Pĺ denna sida kan du lägga till, radera och redigera emoticons eller smileys som dina användare kan använda i inlägg och personliga meddelanden.";

$lang['smiley_config'] = "Smiley konfiguration";
$lang['smiley_code'] = "Smiley kod";
$lang['smiley_url'] = "Smiley bildfil";
$lang['smiley_emot'] = "Smiley Emotion";
$lang['smile_add'] = "Lägg till en ny Smiley";
$lang['Smile'] = "Smile";
$lang['Emotion'] = "Emotion";

$lang['Select_pak'] = "Välj paket (.pak) fil";
$lang['replace_existing'] = "Ersätt befintlig Smiley";
$lang['keep_existing'] = "Behĺll befintlig Smiley";
$lang['smiley_import_inst'] = "Du bör packa upp (unzip) smiley paketet och ladda upp alla filer till avsedd smiley katalog för din installation. Sen sätter du rätt information i detta formulär och importerar smiley paketet.";
$lang['smiley_import'] = "Smiley paket import";
$lang['choose_smile_pak'] = "Välj en Smile Pack .pak fil";
$lang['import'] = "Importera Smileys";
$lang['smile_conflicts'] = "Vad ska göras om det finns konflikter";
$lang['del_existing_smileys'] = "Radera befintlig smileys före import";
$lang['import_smile_pack'] = "Importera Smiley paket";
$lang['export_smile_pack'] = "Skapa Smiley paket";
$lang['export_smiles'] = "För att skapa ett smiley paket frĺn dina installerade smileys, klicka %shär%s för att ladda ner smiles.pak filen. Ge filen ett passande namn och se till att behĺlla .pak tillägget.  Skapa sen en zip fil som innehĺller alla dina smileys bilder plus din .pak konfigurations fil.";

$lang['smiley_add_success'] = "Smileyn adderades.";
$lang['smiley_edit_success'] = "Smileyn uppdaterades";
$lang['smiley_import_success'] = "Smiley paketet är importerat!";
$lang['smiley_del_success'] = "Smileyn togs bort";
$lang['Click_return_smileadmin'] = "Klicka %shär%s för att ĺtergĺ Smiley Administrationen";

//
// Group control panel
//
$lang['Group_Control_Panel'] = 'Gruppkontrollpanel';
$lang['Group_member_details'] = 'Gruppmedlemskapsdetaljer';
$lang['Group_member_join'] = 'Gĺ med i en grupp';

$lang['Group_Information'] = 'Gruppinformation';
$lang['Group_name'] = 'Gruppnamn';
$lang['Group_description'] = 'Gruppbeskrivning';
$lang['Group_membership'] = 'Gruppmedlemskap';
$lang['Group_Members'] = 'Gruppmedlemmar';
$lang['Group_Moderator'] = 'Gruppmoderator';
$lang['Pending_members'] = 'Medlemskapsförfrĺgningar';

$lang['Group_type'] = 'Grupptyp';
$lang['Group_open'] = 'Öppen grupp';
$lang['Group_closed'] = 'Stängd grupp';
$lang['Group_hidden'] = 'Dold grupp';

$lang['Current_memberships'] = 'Grupper du är med i';
$lang['Non_member_groups'] = 'Grupper du ej är med i';
$lang['Memberships_pending'] = 'Medlemskapsförfrĺgningar';

$lang['No_groups_exist'] = 'Det finns inga grupper';
$lang['Group_not_exist'] = 'Den användargruppen finns inte';

$lang['Join_group'] = 'Gĺ med i grupp';
$lang['No_group_members'] = 'Den här gruppen har inga medlemmar';
$lang['Group_hidden_members'] = 'Den här gruppen är dold, du kan inte se dess medlemmar';
$lang['No_pending_group_members'] = 'Den här gruppen har inga medlemskapsförfrĺgningar';
$lang['Group_joined'] = 'Du har nu ansökt om att bli medlem i den här gruppen<br />Du kommer att bli meddelad om du blir godkänd som medlem eller inte av gruppmoderatorn.';
$lang['Group_request'] = 'En förfrĺgan att om att bli medlem i din grupp har gjorts.';
$lang['Group_approved'] = 'Din förfrĺgan har godkännts.';
$lang['Group_added'] = 'Du har lagts till i den här användargruppen.';
$lang['Already_member_group'] = 'Du är redan medlem av den här gruppen.';
$lang['User_is_member_group'] = 'Användaren är redan medlem i den här gruppen.';
$lang['Group_type_updated'] = 'Uppdaterade grupptypen.';

$lang['Could_not_add_user'] = 'Användaren du valde existerar inte.';
$lang['Could_not_anon_user'] = 'Du kan inte göra en Anonym till medlem i gruppen.';

$lang['Confirm_unsub'] = 'Är du säker pĺ att du vill lämna den här gruppen?';
$lang['Confirm_unsub_pending'] = 'Ditt medlemskap i den här gruppen har inte än blivit godkänt, är du säker pĺ att du vill avbryta ansökan?';

$lang['Unsub_success'] = 'Ditt medlemskap i den här gruppen har avbrutits.';

$lang['Approve_selected'] = 'Godkänn valda';
$lang['Deny_selected'] = 'Avslĺ valda';
$lang['Not_logged_in'] = 'Du mĺste logga in för att gĺ med i en grupp.';
$lang['Remove_selected'] = 'Ta bort valda';
$lang['Add_member'] = 'Lägg till Medlem';
$lang['Not_group_moderator'] = 'Du är inte moderator av den här gruppen och därför kan du inte göra det här.';

$lang['Login_to_join'] = 'Logga in för att kontollera gruppmedlemskap';
$lang['This_open_group'] = 'Det här är en öppen grupp, klicka för att begära medlemskap';
$lang['This_closed_group'] = 'Det här är en stängd grupp, inga fler medlemmar accepteras';
$lang['This_hidden_group'] = 'Det här är en dold grupp, fler medlemmar kan inte läggas till automatiskt';
$lang['Member_this_group'] = 'Du är medlem i den här gruppen';
$lang['Pending_this_group'] = 'Ditt medlemskap i den här gruppen är under behandling';
$lang['Are_group_moderator'] = 'Du är moderator i den här gruppen';
$lang['None'] = 'Inga';

$lang['Subscribe'] = 'Ansök om medlemskap';
$lang['Unsubscribe'] = 'Avbryt medlemskap';
$lang['View_Information'] = 'Visa Information';

//
// Prune Administration
//
$lang['Forum_Prune'] = "Forumreducering";
$lang['Forum_Prune_explain'] = "Detta kommer att radera alla ämnen där inga nya inlägg har skrivits inom det antal dagar du angett. Om du inte anger ett nummer sĺ kommer alla ämnen att raderas. Det kommer inte att radera ämnen inom vilka omröstningar fortfarande är aktiva och det kommer inte heller att ta bort tillkännagivelser. Du behöver radera dessa ämnen manuellt";
$lang['Do_Prune'] = "Reducera";
$lang['All_Forums'] = "Alla forum";
$lang['Prune_topics_not_posted'] = "Radera ämnen med inga svar i efter detta antal dagar";
$lang['Topics_pruned'] = "ämnen reducerade";
$lang['Posts_pruned'] = "Inlägg reducerade";
$lang['Prune_success'] = "Reduceringen gick bra";


//
// Word censor
//
$lang['Words_title'] = "Censurering av ord";
$lang['Words_explain'] = "Frĺn denna kontrollpanel kan du lägga till, redigera och radera ord som automatiskt kommer at bli censurerade i dina forum. Dessutom kommer man inte att tillĺtas att registera användarnamn som innehĺller dessa ord. Wildcards (*) accepteras i ord fältet, eg. *test* matchar omtestning, test* matchar testning, *test matchar sluttest.";
$lang['Word'] = "Ord";
$lang['Edit_word_censor'] = "Redigera ordcensur";
$lang['Replacement'] = "Ersättning";
$lang['Add_new_word'] = "Lägg till nytt ord";
$lang['Update_word'] = "Uppdatera ordcensur";

$lang['Must_enter_word'] = "Du mĺste skriva ett ord och dess ersättning";
$lang['No_word_selected'] = "Inget ord är valt för redigering";

$lang['Word_updated'] = "Censuren är uppdaterad";
$lang['Word_added'] = "Ordet har lagts till censuren";
$lang['Word_removed'] = "Ordet har tagits bort frĺn censuren";

$lang['Click_return_wordadmin'] = "Klicka %shär% för att ĺtergĺ till censurering av ord";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Här kan du skicka ett e-post meddelande till antingen alla dina användare eller till användare i en specifik grupp.  För att kunna göra detta, kommer ett email att skickas till den administrativa epost adressen som du angett, med en bcc till alla mottagare. Ha lite tĺlamod om du mailar en stor grupp av människor efter att ha skickat meddelandet och avbryt inte sidan halvvägs igenom. Det är normalt för mass epost (spam) att ta en längre tid, du kommer att meddelas när skriptet är klart.";
$lang['Compose'] = "Komponerna";

$lang['Recipients'] = "Mottagare";
$lang['All_users'] = "Alla användare";

$lang['Email_successfull'] = "Ditt meddelande har skickats";
$lang['Click_return_massemail'] = "Klicka %shär%s för att ĺtergĺ till mass e-post formuläret";


//
// Ranks admin
//
$lang['Ranks_title'] = "Titel Administration";
$lang['Ranks_explain'] = "Via detta forumlär kan du skapa nya, redigera, visa och ta bort titlar. Du kan ocksĺ skapa speciella titlar som kan tilldelas till en användare via användaradministration.";

$lang['Add_new_rank'] = "Lägg till en ny titel";

$lang['Rank_title'] = "Namn pĺ titel";
$lang['Rank_special'] = "sätt som speciell titel";
$lang['Rank_minimum'] = "Minimum antal inlägg";
$lang['Rank_maximum'] = "Maximum antal inlägg";
$lang['Rank_image'] = "Titel bild (relativt till phpBB2 root katalogen)";
$lang['Rank_image_explain'] = "Använda denna för att tala om vilken bild som ska associeras med titeln";

$lang['Must_select_rank'] = "Du mĺste välja en titel";
$lang['No_assigned_rank'] = "Ingen speciell titel tilldelad";

$lang['Rank_updated'] = "Titeln är uppdaterad";
$lang['Rank_added'] = "Titeln las till";
$lang['Rank_removed'] = "Titeln raderades";
$lang['No_update_ranks'] = 'Titeln raderades. Hursomhelst, användar konton som använder denna titel blev inte uppdaterade.  Du mĺste manuellt ĺterställa titeln pĺ dessa konton.';

$lang['Click_return_rankadmin'] = "Klicka %shär%s för att ĺtergĺ till Titel administration";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Förbjuda användarnamn";
$lang['Disallow_explain'] = "Här kan du styra vilka användarnamn som inte fĺr användas.  Förbjudna användarnamn fĺr innehĺlla wildcard (*).  Notera att du inte kan förbjuda redan registrerade användarnamn, du mĺste först radera användaren för att sedan förbjuda den";

$lang['Delete_disallow'] = "Radera";
$lang['Delete_disallow_title'] = "Radera ett förbjudet namn";
$lang['Delete_disallow_explain'] = "Du kan radera ett förbjudet användarnamn genom att välja namnet frĺn listan och klicka pĺ skicka";

$lang['Add_disallow'] = "Lägg till";
$lang['Add_disallow_title'] = "Lägg till ett förbjudet namn";
$lang['Add_disallow_explain'] = "Du kan förbjuda ett användarnamn med hjälp av jokertecknet * för att matcha vilket tecken som helst";

$lang['No_disallowed'] = "Inga förbjudna användarnamn";

$lang['Disallowed_deleted'] = "Användarnamnet är giltigt igen";
$lang['Disallow_successful'] = "Användarnamnet har förbjudits";
$lang['Disallowed_already'] = "Namnet som du angav kan inte förbjudas. Antingen finns det redan i listan, eller i ordcensur listan, eller sĺ finns användaren redan.";

$lang['Click_return_disallowadmin'] = "Klicka %shär%s för att ĺtergĺ till Förbjuda användarnamn";


//
// Styles Admin
//
$lang['Styles_admin'] = "Stil Administration";
$lang['Styles_explain'] = "Genom denna kontrollpanel kan du lägga till, radera och hantera stilar (mallar och teman) som är tillgängliga för dina användare";
$lang['Styles_addnew_explain'] = "Följande lista innehĺller alla teman som är tillgängliga för de mallar som du har. Artiklarna pĺ denna lista har ännu inte blivit installerade i phpBB databasen. För att installera ett tema klicka pĺ install länken brevid en post.";

$lang['Select_template'] = "Välj en mall";

$lang['Style'] = "Stil";
$lang['Template'] = "Mall";
$lang['Install'] = "Installera";
$lang['Download'] = "Ladda ner";

$lang['Edit_theme'] = "Redigera tema";
$lang['Edit_theme_explain'] = "I forumläret här under kan du redigera inställningarna för valt tema";

$lang['Create_theme'] = "Skapa tema";
$lang['Create_theme_explain'] = "Använd forumläret här för att skapa ett nytt tema för vald mall. När du anger färger (vilka bör anges i hexadecimal form) fĺr du inte inkludera #, i.e.. CCCCCC är giltigt, #CCCCCC är inte det.";

$lang['Export_themes'] = "Exportera teman";
$lang['Export_explain'] = "I denna kontrollpanel har du möjlighet att exportera tema data för en mall. Välj en mall frĺn listan och skriptet kommer att försöka skapa en tema konfigurationsfil samt spara den till mall katalogen. Om skriptet inte kan spara filen själv kommer du att ges möjlighet att ladda hem den. För att skriptet ska kunna spara filen mĺste du ge skriv rättigheter till webserver i malla katalogen. För mer information om detta se phpBB 2 användarguide.";

$lang['Theme_installed'] = "Det valda temat har installerats.";
$lang['Style_removed'] = "Den valda stilen har tagits bort frĺn databasen. För att fullständigt ta bort denna stil frĺn ditt system mĺste du radera de stilen frĺn din mall katalog.";
$lang['Theme_info_saved'] = "Tema information för vald mall har sparats. Du bör nu ĺterställa rättigheterna pĺ theme_info.cfg (och pĺ mall katalogen) till läs rättigheter.";
$lang['Theme_updated'] = "Det valda temat har uppdaterats. Du bör nu exportera de nya tema inställningarna";
$lang['Theme_created'] = "Temat har skapas. Du bör nu exportera temat till tema konfigurationsfilen för säkerhets skull och för användning pĺ andra forum";

$lang['Confirm_delete_style'] = "Är du säker pĺ att du vill radera denna stil";

$lang['Download_theme_cfg'] = "Exporeraren kunde inte spara tema informationsfilen. Klicka pĺ kanppen nedan för att ladda ner denna fil med din webläsare. När du har laddat hem den kan du överföra den till katalogen som innehĺller mall filerna. Du kan därefter paketera filerna för distribution eller för användning nĺgon annanstans om du sĺ önskar";
$lang['No_themes'] = "Mallen du har valt har inga teman knuta till den. Skapa ett nytt tema genom att klicka pĺ Skapa Ny länken pĺ vänster sida om panelen.";
$lang['No_template_dir'] = "Kan inte öppna mall katalogen. Den kan vara oläsbar av webservern (kontrollera rättigheterna) eller saknas.";
$lang['Cannot_remove_style'] = "Du kan inte ta bort den valda stilen dĺ den just nu är de forumets standard stil. Ändra standard stil och försök igen.";
$lang['Style_exists'] = "Stilen finns redan, gĺ tillbaka ovh välj ett annat namn.";

$lang['Click_return_styleadmin'] = "Klicka %sHär%s för att ĺtergĺ till Stiladministrationen";

$lang['Theme_settings'] = "Temainställningar";
$lang['Theme_element'] = "Tema Element";
$lang['Simple_name'] = "Enkelt namn";
$lang['Value'] = "Värde";
$lang['Save_Settings'] = "Spara inställningar";

$lang['Stylesheet'] = "CSS Stylesheet";
$lang['Stylesheet_explain'] = 'Filnamn för detta CSS Stylesheet.';
$lang['Background_image'] = "Bakgrundsbild";
$lang['Background_color'] = "Bakgrundsfärg";
$lang['Theme_name'] = "Tema namn";
$lang['Link_color'] = "Länkfärg";
$lang['Text_color'] = "Textfärg";
$lang['VLink_color'] = "Besökt länkfärg";
$lang['ALink_color'] = "Aktiv länkfärg";
$lang['HLink_color'] = "Hover länkfärg";
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
$lang['fontface1'] = "Textstil 1";
$lang['fontface2'] = "Textstil 2";
$lang['fontface3'] = "Textstil 3";
$lang['fontsize1'] = "Textstil 1";
$lang['fontsize2'] = "Textstil 2";
$lang['fontsize3'] = "Textstil 3";
$lang['fontcolor1'] = "Textfärg 1";
$lang['fontcolor2'] = "Textfärg 2";
$lang['fontcolor3'] = "Textfärg 3";
$lang['span_class1'] = "Span Class 1";
$lang['span_class2'] = "Span Class 2";
$lang['span_class3'] = "Span Class 3";
$lang['img_poll_size'] = "Omröstning bild storlek [px]";
$lang['img_pm_size'] = "Personligt meddelande status storlek [px]";


//
// Install Process
//
$lang['Welcome_install'] = "Välkommen till phpBB 2 Installationen";
$lang['Initial_config'] = "Grundläggande  konfiguration";
$lang['DB_config'] = "Databas konfiguration";
$lang['Admin_config'] = "Admin konfiguration";
$lang['continue_upgrade'] = "När du har laddat ner din config fil till din lokala maskin kan du välja \"Fortsätta uppgraderingen\" knappen nedan för att fortsätta uppgraderingsprocessen. Vänta med att ladda upp config filen tills uppgraderingsprocessen är färdig.";
$lang['upgrade_submit'] = "Fortsätta uppgraderingen";

$lang['Installer_Error'] = "Ett fel har uppstĺtt under installationen";
$lang['Previous_Install'] = "En föregĺende installation har upptäckts";
$lang['Install_db_error'] = "Ett fel uppstod vid uppdateringen av databasen";

$lang['Re_install'] = "Din föregĺende installation är fortfarande aktiv. <br /><br />Om du vill ominstallera phpBB 2 bör du klicka pĺ Ja-knappen nedan. Var medveten om att detta förstör all befintlig data, ingen säkerhetskopiering kommer att ske! Administratörs användarnamnet och lösenord som du har använt för att logga in till forumet kommer att ĺterskapas efter ominstallation, inga andra inställningar kommer att behĺllas. <br /><br />Tänk igenom det noga innan du trycker pĺ Ja!";

$lang['Inst_Step_0'] = "Tack för att du valt phpBB 2. För att fullborda installation fyll i information som efterfrĺgas nedan. Notera att databasen som du vill installera till mĺste finnas. Om du installerar till en databas som använder ODBC, e.g. MS Access sĺ bör du först skapa en DSN för den innan du gĺr vidare.";

$lang['Start_Install'] = "Starta installationen";
$lang['Finish_Install'] = "Avsluta installationen";

$lang['Default_lang'] = "Standardsprĺk i forumet";
$lang['DB_Host'] = "Databasserver värdnamn / DSN";
$lang['DB_Name'] = "Ditt databasnamn";
$lang['DB_Username'] = "Databas användarnamn";
$lang['DB_Password'] = "Databas lösenord";
$lang['Database'] = "Din databas";
$lang['Install_lang'] = "Välj sprĺk för installation";
$lang['dbms'] = "Databastyp";
$lang['Table_Prefix'] = "Prefix för tabeller i databasen";
$lang['Admin_Username'] = "Administratör användarnamn";
$lang['Admin_Password'] = "Administratör lösenord";
$lang['Admin_Password_confirm'] = "Administratör lösenord [ bekräfta ]";

$lang['Inst_Step_2'] = "Din administratörsanvändare har skapats. Vid detta tillfälle är din grundinstallation färdig. Du kommer nu att skickas till en sida där du har möjlighet att administrera din nya installation. Var god kontrollera dina Allmäna inställningar och gör nödvändiga ändringar. Tack för att du valt phpBB 2.";

$lang['Unwriteable_config'] = "Din config-fil är icke skrivbar för tillfället. En kopia av config filen kommer att skickas till dig när du klickar pĺ kanppen nedan. Du bör ladda upp denna fil till samma katalog som phpBB 2. När detta är gjort bör du logga in med ditt administratör användarnamn och lösenord (som du angav i ett tidigare formulär) och besöka administratörskontrollpanelen (en länk kommer att finns längst ner pĺ varje sida när du väl har logga int) för att kontrollera den allmänna konfigurationen. Tack för att du valt phpBB 2.";
$lang['Download_config'] = "Ladda ner konfiguration";

$lang['ftp_choose'] = "Välj nedladdningsmetod";
$lang['ftp_option'] = "<br />Eftersom FTP tillägg är aktiverat i denna version av PHP ges du ocksĺ möjlighet att försöka ftp:a config filen till servern helt automatiskt.";
$lang['ftp_instructs'] = "Du har valt att ftp:a filen till kontot som har phpBB 2 helt automatiskt. Ange informationen som saknas nedan. Notera att FTP sökvägen ska vara exakt samma sökväg till din phpBB 2 installation som du skulle använda om du använder en vanlig ftp klient.";
$lang['ftp_info'] = "Ange din FTP information";
$lang['Attempt_ftp'] = "Försöker skriva config-filen till rätt ställe via ftp";
$lang['Send_file'] = "Skicka filen till mig sĺ fixar jag det manuellt via ftp";
$lang['ftp_path'] = "FTP sökväg till phpBB 2";
$lang['ftp_username'] = "Ditt FTP användarnamn";
$lang['ftp_password'] = "Ditt FTP lösenord";
$lang['Transfer_config'] = "Starta överföring";
$lang['NoFTP_config'] = "Försöket att ftp:a config-filen misslyckades. Ladda hem filen och ftp:a upp filen manuellt.";

$lang['Install'] = "Installera";
$lang['Upgrade'] = "Uppgradera";


$lang['Install_Method'] = "Välj installationsmetod";

$lang['Install_No_Ext'] = "PHP konfigurationen pĺ din server stödjer inte den databas typ du har valt";

$lang['Install_No_PCRE'] = "phpBB2 kräver den \"Perl-Compatible Regular Expressions Module for php\" vilket din php konfiguration inte stödjer";

// Version Check
//
$lang['Version_up_to_date'] = 'Din installation använder senaste phpBB versionen, inga nya uppdateringar finns tillgängliga.';
$lang['Version_not_up_to_date'] = 'Din phpBB installation är <b>inte</b> uppdaterad. Det finns nya uppdateringar tillgängliga, vänligen besök <a href="http://www.phpbb.com/downloads.php" target="_new">http://www.phpbb.com/downloads.php</a> för att tillgĺ senaste versionen.';
$lang['Latest_version_info'] = 'Senaste versionen är <b>phpBB %s</b>.';
$lang['Current_version_info'] = 'Du använder <b>phpBB %s</b>.';
$lang['Connect_socket_error'] = 'Tyvärr, lyckas inte ansluta till phpBB servern, rapporterat fel är:<br />%s';
$lang['Socket_functions_disabled'] = 'Lyckas inte öppna socketfunktion.';
$lang['Mailing_list_subscribe_reminder'] = 'För information om senaste phpBB version, anslut dig till <a href="http://www.phpbb.com/support/" target="_new">phpBB mailing list</a>.';
$lang['Version_information'] = 'Versioninformation';

//
// Login attempts configuration
//
$lang['Max_login_attempts'] = 'Allowed login attempts';
$lang['Max_login_attempts_explain'] = 'The number of allowed board login attempts.';
$lang['Login_reset_time'] = 'Login lock time';
$lang['Login_reset_time_explain'] = 'Time in minutes the user have to wait until he is allowed to login again after exceeding the number of allowed login attempts.';

//
// That's all Folks!
// -------------------------------------------------

?>