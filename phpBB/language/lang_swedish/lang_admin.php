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
//  Swedish Translation by:
//	
//	Marcus Svensson
//  xsvennemanx@hotmail.com
//	
// 	Janåke Rönnblom
//	jan-ake.ronnblom@skeria.skelleftea.se
//	
//	Bruce
//	bruce@webway.se
// 

//
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "Generell administration";
$lang['Users'] = "Användaradministration";
$lang['Groups'] = "Gruppadministration";
$lang['Forums'] = "Forumadministration";
$lang['Styles'] = "Stiladministration";

$lang['Configuration'] = "Konfiguration";
$lang['Permissions'] = "Rättigheter";
$lang['Manage'] = "Hantering";
$lang['Disallow'] = "Förbjuda namn";
$lang['Prune'] = "Reducera";
$lang['Mass_Email'] = "Mass Email";
$lang['Ranks'] = "Ranker";
$lang['Smilies'] = "Smilies";
$lang['Ban_Management'] = "Bannlys";
$lang['Word_Censor'] = "Ord Censur";
$lang['Export'] = "Exportera";
$lang['Create_new'] = "Skapa";
$lang['Add_new'] = "Lägg till";
$lang['Backup_DB'] = "Backup av databas";
$lang['Restore_DB'] = "Återställ databas";


//
// Index
//
$lang['Admin'] = "Administration";
$lang['Not_admin'] = "Du har inte rättighet att administrera detta forum";
$lang['Welcome_phpBB'] = "Välkommen till phpBB";
$lang['Admin_intro'] = "Tack för att du har valt phpBB som din forumlösning. Den här sidan ger dig en snabb överblick över all möjlig statistik om ditt forum. Du kan komma tillbaka till den här sidan genom att klicka på <u>Administrations Index</u> länken på den vänstra sidan. För att komma tillbaka till indexet till forumet tryck på phpBB logon, som finns i den vänstra panelen. De övriga länkarna på vänster hand låter dig kontrollera alla aspekter på hur ditt forum presenteras, varje sidan har intruktioner på hur du använder verktygen.";
$lang['Main_index'] = "Forum index";
$lang['Forum_stats'] = "Forum statistik";
$lang['Admin_Index'] = "Admin index";
$lang['Preview_forum'] = "Förhandsgranska forum";

$lang['Click_return_admin_index'] = "Klicka %shär%s för att återvända till Admin Index";

$lang['Statistic'] = "Statistik";
$lang['Value'] = "Värde";
$lang['Number_posts'] = "Antal inlägg";
$lang['Posts_per_day'] = "Inlägg per dag";
$lang['Number_topics'] = "Antal ämnen";
$lang['Topics_per_day'] = "Ämnen per dag";
$lang['Number_users'] = "Antal användare";
$lang['Users_per_day'] = "Användare per dag";
$lang['Board_started'] = "Forumstart";
$lang['Avatar_dir_size'] = "Avatarkatalogens storlek";
$lang['Database_size'] = "Databasstorlek";
$lang['Gzip_compression'] ="Gzip komprimering";
$lang['Not_available'] = "Inte tillgänglig";

$lang['ON'] = "PÅ"; // This is for GZip compression
$lang['OFF'] = "AV"; 


//
// DB Utils
//
$lang['Database_Utilities'] = "Databas Verktyg";

$lang['Restore'] = "Återställ";
$lang['Backup'] = "Backup";
$lang['Restore_explain'] = "Detta kommer att utföra en fullständig återställning av alla phpBB tabeller från en sparad fil. Om din server stödjer det kan du ladda upp en gzip komprimerad text fil vilken kommer att dekomprimeras. <b>WARNING</b>Detta kommer att skriva över all existerande data. Återställningen kan ta en lång tid att utföra men lämna inte denna sida förrän den är färdig.";
$lang['Backup_explain'] = "Här kan du ta backup på alla din phpBB relaterade data. Om du har andra egna tabeller i samma databas som phpBB som du också vill säkerhetskopiera så ange deras namn separerad med komman i \"Övriga tabeller\" textrutan nedanför Om din server stöjder det kan du också gzip komprimera filen för att minska storleken innan du laddar ner den.";

$lang['Backup_options'] = "Backup alternativ";
$lang['Start_backup'] = "Starta backup";
$lang['Full_backup'] = "Fullständig backup";
$lang['Structure_backup'] = "Enbart backup av strukturen";
$lang['Data_backup'] = "Backup av endast data";
$lang['Additional_tables'] = "Övriga tabeller";
$lang['Gzip_compress'] = "Gzip komprimera filen";
$lang['Select_file'] = "Välj en fil";
$lang['Start_Restore'] = "Starta återställningen";

$lang['Restore_success'] = "Databasen är återställd utan problem.<br /><br />Ditt forum bör vara tillbaka i samma skick som när du gjorde backupen.";
$lang['Backup_download'] = "Din nedladdning kommer att starta snart var god vänta tills den startar";
$lang['Backups_not_supported'] = "Tyvärr så stöds inte backup än av ditt databassystem";

$lang['Restore_Error_uploading'] = "Fel när filen skulle laddas upp.";
$lang['Restore_Error_filename'] = "Problem med filnamnet, försök med en annan fil";
$lang['Restore_Error_decompress'] = "Kan inte dekomprimera en gzip fil, försök ladda upp en text version";
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
$lang['Look_up_User'] = "Slå upp en användare"; 
$lang['Look_up_Group'] = "Slå upp en grupp"; 
$lang['Look_up_Forum'] = "Slå upp ett forum"; 

$lang['Group_auth_explain'] = "Här kan du ändra rättigheter och moderator status för varje grupp. Glöm inte att fastän du ändrar grupp rättigheten att användarens egna rättigheter fortfarande kan ge dom access till forum, m.m. Du kommer att få en varning i så fall.";
$lang['User_auth_explain'] = "Här kan du ändra rättigheter och moderator status för varje enskild användare. Glöm inte att fastän du ändrar grupp rättigheten att användarens egna rättigheter fortfarande kan ge dom access till forum, m.m. Du kommer att få en varning i så fall.";
$lang['Forum_auth_explain'] = "Här kan du ändra auktorisionsnivåer för varje forum. Du har både en enkel och en avancerad metod för att göra detta, avancerad ger dig större kontroll över varje forums funktioner. Kom ihåg att när du ändrar rättigheterna till forumet så påverkar du vilka användare som kan utföra olika funktioner i forumet.";

$lang['Simple_mode'] = "Enkelt läge";
$lang['Advanced_mode'] = "Avancerat läge";
$lang['Moderator_status'] = "Moderator status";

$lang['Allowed_Access'] = "Tillåt Access";
$lang['Disallowed_Access'] = "Neka Access";
$lang['Is_Moderator'] = "Är Moderator";
$lang['Not_Moderator'] = "Är inte Moderator";

$lang['Conflict_warning'] = "Varning! Auktorisationskonflikt";
$lang['Conflict_access_userauth'] = "Denna användare har fortfarande tillgång till detta forum via gruppmedlemskap. Du kanske vill ändra grupp rättigheter eller ta bort denna användare från gruppen för att förhindra att de har tillgång. Gruppens rättigheter (och berörda forum) listas nedan.";
$lang['Conflict_mod_userauth'] = "Användaren har fortfarande moderator rättigheter till forumet via grupp. Du kan antingen ändra grupp rättigheterna eller ta bort denna användare från gruppen för att förhindra att de har moderator rättigheter. Gruppens rättigheter (och berörda forum) listas nedan.";

$lang['Conflict_access_groupauth'] = "Följande användare har fortfarande åtkomst rättigheter till detta forum via deras användarrättigheter. Du kanske vill ändra användarrättigheterna för att förhindra dem från att ha åtkomst till forumet. Användarens rättigheter (och berörda forum) listas nedan.";
$lang['Conflict_mod_groupauth'] = "Följande användare har fortfarande moderator rättigheter till forumet via användarrättigheter. Du kanske vill ändra användarrättigheterna för att förhindra dem från att ha åtkomst till forumet. Användarens rättigheter (och berörda forum) listas nedan.";

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
$lang['Announce'] = "Tillkännagivelse"; 
$lang['Vote'] = "Rösta";
$lang['Pollcreate'] = "Skapa omröstning";

$lang['Permissions'] = "Rättigheter";
$lang['Simple_Permission'] = "Enkla Rättigheter";

$lang['User_Level'] = "Användarnivå"; 
$lang['Auth_User'] = "Användare";
$lang['Auth_Admin'] = "Administratör";
$lang['Group_memberships'] = "Grupp medlemskap";
$lang['Usergroup_members'] = "Den här gruppen har följande medlemmar";

$lang['Forum_auth_updated'] = "Forumrättigeheter är uppdaterade";
$lang['User_auth_updated'] = "Användarrättigeheter är uppdaterade";
$lang['Group_auth_updated'] = "Grupprättigeheter är uppdaterade";

$lang['Auth_updated'] = "Rättigheterna är uppdaterade";
$lang['Click_return_userauth'] = "Klicka %shär%s för att återgå till användarrättigheter";
$lang['Click_return_groupauth'] = "Klicka %shär%s för att återgå till grupprättigheter";
$lang['Click_return_forumauth'] = "Klicka %shär%s  för att återgå till forumrättigheter";


//
// Banning
//
$lang['Ban_control'] = "Bannlysningskontroll";
$lang['Ban_explain'] = "Här sköter du bannlysningen av användare. Du kan uppnå detta genom att bannlysa vilket som helst eller alla av en användare eller en särskild eller ett område av IP adresser eller värdnamn. Dessa metoder förhindrar en användare från att nå index sidan på ditt forum. För att förhindra en användare att registrera under ett annat användarnamn kan du också ange en bannlyst epostadress. Notera att bannlysa enbart en epostadress inte kommer att förhindra användaren från att logga på eller skriva ett inlägg på ditt forum, du bör använda någon av de två första metoderna för att uppnå det.";
$lang['Ban_explain_warn'] = "Notera att genom att ange ett område av IP adresser så resulterar det i att alla adresser mellan start och slut läggs till i banlysningslistan. En ansträngning kommer att göras för att minska antalet adresser som läggs in i databasen genom att introducera jokertecken automatiskt där det är lämpligt. Om du verkligen måste ange ett område av adresser så försök hålla det litet eller ännu bättre försöka att explicit ange enstaka adresserna.";

$lang['Select_username'] = "Välj ett användarnamn";
$lang['Select_ip'] = "Välj en IP adress";
$lang['Select_email'] = "Välj en epost adress";

$lang['Ban_username'] = "Bannlys en eller flera användare";
$lang['Ban_username_explain'] = "Du kan bannlysa flera användare samtidigt genom att använda den ändamålsenliga kombinationen av mus och tangenbord för din dator och webläsare.";

$lang['Ban_IP'] = "Bannlys en eller flera IP adresser eller värdnamn";
$lang['IP_hostname'] = "IP adresser eller värdnamn";
$lang['Ban_IP_explain'] = "För att specifiera flera olika IP adresser eller värdnamn skilj dem åt med kommatecken. För att specifiera en rad olika IP adresser separera början och slutet med ett bindesstreck(-), för att specifiera ett wildcard använd *";

$lang['Ban_email'] = "Bannlys en eller flera epost adresser";
$lang['Ban_email_explain'] = "För att specificera mer än en epostadress, skilj dem åt med kommatecken. För att specifiera ett wildcard namn använd *, till exempel *@hotmail.com";

$lang['Unban_username'] = "Häv en eller flera bannlysta användare";
$lang['Unban_username_explain'] = "Du kan ta bort flera bannlysningar samtidigt genom att använda den ändamålsenliga kombinationen av mus och tangenbord för din dator och webläsare.";

$lang['Unban_IP'] = "Häv en eller flera bannlysta IP adresser";
$lang['Unban_IP_explain'] = "Du kan ta bort flera bannlysningar av IP adresser samtidigt genom att använda den ändamålsenliga kombinationen av mus och tangenbord för din dator och webläsare.";

$lang['Unban_email'] = "Häv en eller flera bannlysta epost adresser";
$lang['Unban_email_explain'] = "Du kan ta bort flera bannlysningar av epostadresser samtidigt genom att använda den ändamålsenliga kombinationen av mus och tangenbord för din dator och webläsare.";

$lang['No_banned_users'] = "Inga bannlysta användarnamn";
$lang['No_banned_ip'] = "Inga bannlysta IP adresser";
$lang['No_banned_email'] = "Inga bannlysta epost adresser";

$lang['Ban_update_sucessful'] = "Banlistan har blivit uppdaterad.";
$lang['Click_return_banadmin'] = "Klicka %shär%s för att återvända till bannlysningskontrollen";


//
// Configuration
//
$lang['General_Config'] = "Generell Konfiguration";
$lang['Config_explain'] = "Formuläret här ger dig möjlighet att ändra alla allmänna forum inställningar. För användar och forum konfiguration så änvänd de relaterade länkarna på vänster sida.";

$lang['Click_return_config'] = "Klicka %shär%s för att återvända till Generell Konfiguration";

$lang['General_settings'] = "Generella Forum Inställningar";
$lang['Server_name'] = "Domän Namn";
$lang['Server_name_explain'] = "Domännamnet som forumet körs från";
$lang['Script_path'] = "Skript sökväg";
$lang['Script_path_explain'] = "Sökvägen där phpBB2 är placerat under domännamnet (domännamn.com/sökväg)";
$lang['Server_port'] = "Server Port";
$lang['Server_port_explain'] = "Porten som servern körs på, vanligtvis 80, ändra bara om porten är annorlunda";
$lang['Site_name'] = "Site namn";
$lang['Site_desc'] = "Site beskrivning";
$lang['Board_disable'] = "Stäng av forumet";
$lang['Board_disable_explain'] = "Detta gör forumet otillgängligt för användarna. Logga inte ut när du har deaktiverat forumet, du kommer inte att kunna logga in igen!";
$lang['Acct_activation'] = "Aktivera konto aktivation";
$lang['Acc_None'] = "Ingen"; // These three entries are the type of activation
$lang['Acc_User'] = "Användare";
$lang['Acc_Admin'] = "Administratör";

$lang['Abilities_settings'] = "Användar och forum grund inställningar";
$lang['Max_poll_options'] = "Maximalt antal av val för omröstningar";
$lang['Flood_Interval'] = "Flood Interval";
$lang['Flood_Interval_explain'] = "Antal sekunder en användare måste vänta mellan inläggen"; 
$lang['Board_email_form'] = "Eposta användare via forumet";
$lang['Board_email_form_explain'] = "Användare skickar epost till varandra via forumet";
$lang['Topics_per_page'] = "Ämnen per sida";
$lang['Posts_per_page'] = "Inlägg per sida";
$lang['Hot_threshold'] = "Antal inlägg för populäritet";
$lang['Default_style'] = "Standard stil";
$lang['Override_style'] = "Åsidosätt användarstil";
$lang['Override_style_explain'] = "Ersätter användarens stil med standard stilen";
$lang['Default_language'] = "Standard språk";
$lang['Date_format'] = "Datum format";
$lang['System_timezone'] = "Systemets tidszon";
$lang['Enable_gzip'] = "Aktivera GZip Kompression";
$lang['Enable_prune'] = "Aktivera forum reducering";
$lang['Allow_HTML'] = "Tillåt HTML";
$lang['Allow_BBCode'] = "Tillåt BBCode";
$lang['Allowed_tags'] = "Tillåtna HTML taggar";
$lang['Allowed_tags_explain'] = "Separera taggarna med komma";
$lang['Allow_smilies'] = "Tillåt smilies";
$lang['Smilies_path'] = "Smilies sökväg";
$lang['Smilies_path_explain'] = "Sökväg under din phpBB root katalog, e.g. images/smilies";
$lang['Allow_sig'] = "Tillåt signaturer";
$lang['Max_sig_length'] = "Maximal längd på  signaturen";
$lang['Max_sig_length_explain'] = "Maximalt antal tecken i användarens signatur";
$lang['Allow_name_change'] = "Tillåt ändring av användarnamn";

$lang['Avatar_settings'] = "Avatar inställningar";
$lang['Allow_local'] = "Aktivera galleri avatars";
$lang['Allow_remote'] = "Aktivera fjärr avatars";
$lang['Allow_remote_explain'] = "Gör det möjligt att länka till avatarer på andra websiter";
$lang['Allow_upload'] = "Aktivera Avatar uppladdning";
$lang['Max_filesize'] = "Maximal Avatar filstorlek";
$lang['Max_filesize_explain'] = "För avatarer som laddas upp";
$lang['Max_avatar_size'] = "Maximal Avatar storlek";
$lang['Max_avatar_size_explain'] = "(Höjd x Bredd i pixels)";
$lang['Avatar_storage_path'] = "Avatar sökväg";
$lang['Avatar_storage_path_explain'] = "Sökväg under din phpBB root katalog, e.g. images/avatars";
$lang['Avatar_gallery_path'] = "Avatar galleriets sökväg";
$lang['Avatar_gallery_path_explain'] = "Sökväg under din phpBB root katalog för för-laddade bilder, e.g. images/avatars/gallery";

$lang['COPPA_settings'] = "COPPA inställningar";
$lang['COPPA_fax'] = "COPPA fax nummer";
$lang['COPPA_mail'] = "COPPA postadress";
$lang['COPPA_mail_explain'] = "Detta är postadress dit föräldrar ska skicka registreringsforumlären för COPPA";

$lang['Email_settings'] = "Epost inställningar";
$lang['Admin_email'] = "Admin epost adress";
$lang['Email_sig'] = "Epost signatur";
$lang['Email_sig_explain'] = "Denna text kommer att bifogas i all epost som forumet skickar.";
$lang['Use_SMTP'] = "Använd SMTP server för epost";
$lang['Use_SMTP_explain'] = "Säg ja om du vill eller måste skicka epost via en angiven server istället för via den lokala epost funktionen";
$lang['SMTP_server'] = "SMTP server Adress";
$lang['SMTP_username'] = "SMTP Användarnamn";
$lang['SMTP_username_explain'] = "Skriv endast in ett användarnamn om din smtp server behöver det";
$lang['SMTP_password'] = "SMTP Lösenord";
$lang['SMTP_password_explain'] = "Skriv endast in ett lösenord om din smtp server behöver det";

$lang['Disable_privmsg'] = "Privat Meddelandehantering";
$lang['Inbox_limits'] = "Max inlägg i Inlådan";
$lang['Sentbox_limits'] = "Max inlägg i Skickade brev";
$lang['Savebox_limits'] = "Max inlägg i Sparade brev";

$lang['Cookie_settings'] = "Cookie inställningar"; 
$lang['Cookie_settings_explain'] = "Detta styr hur cookien som skickas till webläsaren är definerad. I de flesta fall så är standard inställningarna tillräckliga. Om du behöver ändra dessa så gör det med varsamhet, felaktiga inställningar kan hindra användare från att logga in";
$lang['Cookie_domain'] = "Cookie domän";
$lang['Cookie_name'] = "Cookie namn";
$lang['Cookie_path'] = "Cookie sökväg";
$lang['Cookie_secure'] = "Cookie säkerhet [ https ]";
$lang['Cookie_secure_explain'] = "Om servern körs via SSL aktivera det här, annars låt det vara avaktiverat";
$lang['Session_length'] = "Sessionslängd [ sekunder ]";


//
// Forum Management
//
$lang['Forum_admin'] = "Forum Administration";
$lang['Forum_admin_explain'] = "Från denna panel kan du  addera, radera, editera, sortera  och omsynkronisera katagorier och forum";
$lang['Edit_forum'] = "Editera forum";
$lang['Create_forum'] = "Skapa nytt forum";
$lang['Create_category'] = "Skapa ny kategori";
$lang['Remove'] = "Radera";
$lang['Action'] = "Action";
$lang['Update_order'] = "Uppdatera sorteringsordning";
$lang['Config_updated'] = "Forum konfigurationen är uppdaterad";
$lang['Edit'] = "Editera";
$lang['Delete'] = "Radera";
$lang['Move_up'] = "Flytta upp";
$lang['Move_down'] = "Flytta ner";
$lang['Resync'] = "Omsynkronisera";
$lang['No_mode'] = "No mode was set";
$lang['Forum_edit_delete_explain'] = "Forumläret under låter dig skräddarsy alla allmänna forum inställningar. Använd relaterad länkar på vänster sida för användar och forum konfiguraration";

$lang['Move_contents'] = "Flytta allt innehåll";
$lang['Forum_delete'] = "Radera forum";
$lang['Forum_delete_explain'] = "Forumläret under låter dig radera ett forum (eller kategori) och tala om var du vill flytta alla ämnen (eller forum) som det innehöll.";

$lang['Forum_settings'] = "Generella forum inställningar";
$lang['Forum_name'] = "Forum namm";
$lang['Forum_desc'] = "Beskrivning";
$lang['Forum_status'] = "Forum status";
$lang['Forum_pruning'] = "Auto-reducering";

$lang['prune_freq'] = 'Sök efter gamla ämnen varje';
$lang['prune_days'] = "Ta bort ämnen som inte har blivit postade till inom";
$lang['Set_prune_data'] = "Du har aktiverar auto-reducering för detta forum men har inte satt en frekvens eller antal dagar för reducering. Gå tillbaka och sätt detta";

$lang['Move_and_Delete'] = "Flytta och radera";

$lang['Delete_all_posts'] = "Radera alla inlägg";
$lang['Nowhere_to_move'] = "Ingenstans att flytta till";

$lang['Edit_Category'] = "Editera kategori";
$lang['Edit_Category_explain'] = "Använda detta forumlär för att modifiera kategorinamnet.";

$lang['Forums_updated'] = "Forum och kategori-information är uppdaterad";

$lang['Must_delete_forums'] = "Du måste radera alla forum innan du kan radera denna kategori";

$lang['Click_return_forumadmin'] = "Klicka %shär%s för att återgå till Forum Administration";


//
// Smiley Management
//
$lang['smiley_title'] = "Smiles editering";
$lang['smile_desc'] = "På denna sida kan du lägga till, radera och redigera emoticons eller smileys som dina användare kan använda i inlägg och privata meddelanden.";

$lang['smiley_config'] = "Smiley konfiguration";
$lang['smiley_code'] = "Smiley kod";
$lang['smiley_url'] = "Smiley bildfil";
$lang['smiley_emot'] = "Smiley Emotion";
$lang['smile_add'] = "Lägg till en ny Smiley";
$lang['Smile'] = "Smile";
$lang['Emotion'] = "Emotion";

$lang['Select_pak'] = "Välj paket (.pak) fil";
$lang['replace_existing'] = "Ersätt befintlig Smiley";
$lang['keep_existing'] = "Behåll befintlig Smiley";
$lang['smiley_import_inst'] = "Du bör packa upp (unzip) smiley paketet och ladda upp alla filer till avsedd smiley katalog för din installation.  Sen sätter du rätt information i detta formulär och importerar smiley paketet.";
$lang['smiley_import'] = "Smiley paket import";
$lang['choose_smile_pak'] = "Välj en Smile Pack .pak fil";
$lang['import'] = "Importera Smileys";
$lang['smile_conflicts'] = "Vad ska göras om det finns konflikter";
$lang['del_existing_smileys'] = "Radera befintlig smileys före import";
$lang['import_smile_pack'] = "Importera Smiley paket";
$lang['export_smile_pack'] = "Skapa Smiley paket";
$lang['export_smiles'] = "För att skapa ett smiley paket från dina installerade smileys, klicka %shär%s för att ladda ner smiles.pak filen. Ge filen ett passande namn och se till att behålla .pak tillägget.  Skapa sen en zip fil som innehåller alla dina smileys bilder plus din .pak konfigurations fil.";

$lang['smiley_add_success'] = "Smileyn adderades.";
$lang['smiley_edit_success'] = "Smileyn uppdaterades";
$lang['smiley_import_success'] = "Smiley paketet är importerat!";
$lang['smiley_del_success'] = "Smileyn togs bort";
$lang['Click_return_smileadmin'] = "Klicka %shär%s för att återgå Smiley Administration";


//
// User Management
//
$lang['User_admin'] = "Användaradministration";
$lang['User_admin_explain'] = "Här kan du ändra dina användares information och vissa specifika tillval. För att ändra användarens rättigheter så använd i användare och grupp rättigheter.";

$lang['Look_up_user'] = "Slå upp användare";

$lang['Admin_user_fail'] = "Kan inte uppdatera användarens profil.";
$lang['Admin_user_updated'] = "Användarens profil uppdaterades.";
$lang['Click_return_useradmin'] = "Klicka %shär%s för att återgå till användaradministration";

$lang['User_delete'] = "Radera denna användare";
$lang['User_delete_explain'] = "Klicka här för att radera denna användare, detta går inte att ångra.";
$lang['User_deleted'] = "Användaren togs bort.";

$lang['User_status'] = "Användaren är aktiv";
$lang['User_allowpm'] = "Kan skicka privata meddelanden";
$lang['User_allowavatar'] = "Kan visa avatar";

$lang['Admin_avatar_explain'] = "Här kan du visa och radera användarens aktuella avatar.";

$lang['User_special'] = "Speciella admin-enbart fält";
$lang['User_special_explain'] = "Dessa fält kan inte ändras av användarna.  Här kan du sätta status och andra tillägg (val?) som inte finns tillgängliga för användaren.";


//
// Group Management
//
$lang['Group_administration'] = "Grupp administration";
$lang['Group_admin_explain'] = "Via denna panel kan du administrera alla dina användaregrupper, du kan; radera, skapa nya och redigera existerande grupper. Du kan välja moderatorer, ändra öppen/stängd status och sätta gruppnamn och beskrivning";
$lang['Error_updating_groups'] = "Det uppstod ett fel när grupperna skulle uppdateras";
$lang['Updated_group'] = "Gruppen är uppdaterad";
$lang['Added_new_group'] = "Den nya gruppen är skapad";
$lang['Deleted_group'] = "Gruppen är raderad";
$lang['New_group'] = "Skapa ny grupp";
$lang['Edit_group'] = "Redigera grupp";
$lang['group_name'] = "Grupp namn";
$lang['group_description'] = "Grupp beskrivning";
$lang['group_moderator'] = "Grupp moderator";
$lang['group_status'] = "Grupp status";
$lang['group_open'] = "Öppen grupp";
$lang['group_closed'] = "Stängd grupp";
$lang['group_hidden'] = "Dold grupp";
$lang['group_delete'] = "Radera grupp";
$lang['group_delete_check'] = "Radera denna grupp";
$lang['submit_group_changes'] = "Skicka ändringar";
$lang['reset_group_changes'] = "Återställ ändringar";
$lang['No_group_name'] = "Du måste ange ett namn på gruppen";
$lang['No_group_moderator'] = "Du åste ange en moderator för gruppen";
$lang['No_group_mode'] = "Du måste ange ett läge för gruppen, öppen eller stängd";
$lang['delete_group_moderator'] = "Radera den gamla grupp moderatorn?";
$lang['delete_moderator_explain'] = "Om du ändrar grupp moderator, klicka i rutan för att radera den gamla moderatorn från gruppen.  Annars klicka inte i rutan så kommer den gamla moderatorn att bli en normal medlem i gruppen";
$lang['Click_return_groupsadmin'] = "Klicka %shär%s för att återgå till grupp administration.";
$lang['Select_group'] = "Välj en grupp";
$lang['Look_up_group'] = "Slå upp en grupp";


//
// Prune Administration
//
$lang['Forum_Prune'] = "Forum reducering";
$lang['Forum_Prune_explain'] = "Detta kommer att radera alla ämnen där inga nya inlägg har skrivits inom det antal dagar du angett. Om du inte anger ett nummer så kommer alla ämnen att raderas. Det kommer inte att radera ämnen inom vilka omröstningar fortfarande är aktiva och det kommer inte heller att ta bort tillkännagivelser. Du behöver radera dessa ämnen manuellt";
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
$lang['Words_explain'] = "Från denna kontrollpanel kan du lägga till, redigera och radera ord som automatiskt kommer at bli censurerade i dina forum. Dessutom kommer man inte att tillåtas att registera användarnamn som innehåller dessa ord. Wildcards (*) accepteras i ord fältet, eg. *test* matchar omtestning, test* matchar testning, *test matchar sluttest.";
$lang['Word'] = "Ord";
$lang['Edit_word_censor'] = "Redigera ordcensur";
$lang['Replacement'] = "Ersättning";
$lang['Add_new_word'] = "Lägg till nytt ord";
$lang['Update_word'] = "Uppdatera ordcensur";

$lang['Must_enter_word'] = "Du måste skriva ett ord och dess ersättning";
$lang['No_word_selected'] = "Inget ord är valt för redigering";

$lang['Word_updated'] = "Censuren är uppdaterad";
$lang['Word_added'] = "Ordet har lagts till censuren";
$lang['Word_removed'] = "Ordet har tagits bort från censuren";

$lang['Click_return_wordadmin'] = "Klicka %shär% för att återgå till censurering av ord";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Här kan du skicka ett epost meddelande till antingen alla dina användare eller till användare i en specifik grupp.  För att kunna göra detta, kommer ett email att skickas till den administrativa epost adressen som du angett, med en bcc till alla mottagare. Ha lite tålamod om du mailar en stor grupp av människor efter att ha skickat meddelandet och avbryt inte sidan halvvägs igenom. Det är normalt för mass epost (spam) att ta en längre tid, du kommer att meddelas när skriptet är klart.";
$lang['Compose'] = "Komponerna"; 

$lang['Recipients'] = "Mottagare"; 
$lang['All_users'] = "Alla användare";

$lang['Email_successfull'] = "Ditt meddelande har skickats";
$lang['Click_return_massemail'] = "Klicka %shär%s för att återgå till mass epost formuläret";


//
// Ranks admin
//
$lang['Ranks_title'] = "Titel Administration";
$lang['Ranks_explain'] = "Via detta forumlär kan du skapa nya, redigera, visa och ta bort titlar. Du kan också skapa speciella titlar som kan tilldelas till en användare via användaradministration.";

$lang['Add_new_rank'] = "Lägg till en ny titel";

$lang['Rank_title'] = "Namn på titel";
$lang['Rank_special'] = "sätt som speciell titel";
$lang['Rank_minimum'] = "Minimum antal inlägg";
$lang['Rank_maximum'] = "Maximum antal inlägg";
$lang['Rank_image'] = "Titel bild (relativt till phpBB2 root katalogen)";
$lang['Rank_image_explain'] = "Använda denna för att tala om vilken bild som ska associeras med titeln";

$lang['Must_select_rank'] = "Du måste välja en titel";
$lang['No_assigned_rank'] = "Ingen speciell titel tilldelad";

$lang['Rank_updated'] = "Titeln är uppdaterad";
$lang['Rank_added'] = "Titeln las till";
$lang['Rank_removed'] = "Titeln raderades";

$lang['Click_return_rankadmin'] = "Klicka %shär%s för att återgå till Titel administration";


//
// Disallow Username Admin
//

$lang['Disallow_control'] = "Förbjuda användarnamn";
$lang['Disallow_explain'] = "Här kan du styra vilka användarnamn som inte får användas.  Förbjudna användarnamn får innehålla wildcard (*).  Notera att du inte kan förbjuda redan registrerade användarnamn, du måste först radera användaren för att sedan förbjuda den";
$lang['Delete_disallow'] = "Radera";
$lang['Delete_disallow_title'] = "Radera ett förbjudet namn";
$lang['Delete_disallow_explain'] = "Du kan radera ett förbjudet användarnamn genom att välja namnet från listan och klicka på skicka";

$lang['Add_disallow'] = "Lägg till";
$lang['Add_disallow_title'] = "Lägg till ett förbjudet namn";
$lang['Add_disallow_explain'] = "Du kan förbjuda ett användarnamn med hjälp av jokertecknet * för att matcha vilket tecken som helst";

$lang['No_disallowed'] = "Inga förbjudna användarnamn";

$lang['Disallowed_deleted'] = "Användarnamnet är giltigt igen";
$lang['Disallow_successful'] = "Användarnamnet har förbjudits";
$lang['Disallowed_already'] = "Namnet som du angav kan inte förbjudas. Antingen finns det redan i listan, eller i ordcensur listan, eller så finns användaren redan.";

$lang['Click_return_disallowadmin'] = "Klicka %shär%s för att återgå till Förbjuda användarnamn";


//
// Styles Admin
//
$lang['Styles_admin'] = "Stil Administration";
$lang['Styles_explain'] = "Genom denna kontrollpanel kan du lägga till, radera och hantera stilar (mallar och teman) som är tillgängliga för dina användare";
$lang['Styles_addnew_explain'] = "Följande lista innehåller alla teman som är tillgängliga för de mallar som du har. Artiklarna på denna lista har ännu inte blivit installerade i phpBB databasen. För att installera ett tema klicka på install länken brevid en post.";

$lang['Select_template'] = "Välj en mall";

$lang['Style'] = "Stil";
$lang['Template'] = "Mall";
$lang['Install'] = "Installera";
$lang['Download'] = "Ladda ner";

$lang['Edit_theme'] = "Redigera tema";
$lang['Edit_theme_explain'] = "I forumläret här under kan du redigera inställningarna för valt tema";

$lang['Create_theme'] = "Skapa tema";
$lang['Create_theme_explain'] = "Använd forumläret här för att skapa ett nytt tema för vald mall. När du anger färger (vilka bör anges i hexadecimal form) får du inte inkludera #, i.e.. CCCCCC är giltigt, #CCCCCC är inte det.";

$lang['Export_themes'] = "Exportera teman";
$lang['Export_explain'] = "I denna kontrollpanel har du möjlighet att exportera tema data för en mall. Välj en mall från listan och skriptet kommer att försöka skapa en tema konfigurationsfil samt spara den till mall katalogen. Om skriptet inte kan spara filen själv kommer du att ges möjlighet att ladda hem den. För att skriptet ska kunna spara filen måste du ge skriv rättigheter till webserver i malla katalogen. För mer information om detta se phpBB 2 användarguide.";

$lang['Theme_installed'] = "Det valda temat har installerats.";
$lang['Style_removed'] = "Den valda stilen har tagits bort från databasen. För att fullständigt ta bort denna stil från ditt system måste du radera de stilen från din mall katalog.";
$lang['Theme_info_saved'] = "Tema information för vald mall har sparats. Du bör nu återställa rättigheterna på theme_info.cfg (och på mall katalogen) till läs rättigheter.";
$lang['Theme_updated'] = "Det valda temat har uppdaterats. Du bör nu exportera de nya tema inställningarna";
$lang['Theme_created'] = "Temat har skapas. Du bör nu exportera temat till tema konfigurationsfilen för säkerhets skull och för användning på andra forum";

$lang['Confirm_delete_style'] = "Är du säker på att du vill radera denna stil";

$lang['Download_theme_cfg'] = "Exporeraren kunde inte spara tema informationsfilen. Klicka på kanppen nedan för att ladda ner denna fil med din webläsare. När du har laddat hem den kan du överföra den till katalogen som innehåller mall filerna. Du kan därefter paketera filerna för distribution eller för användning någon annanstans om du så önskar";
$lang['No_themes'] = "Mallen du har valt har inga teman knuta till den. Skapa ett nytt tema genom att klicka på Skapa Ny länken på vänster sida om panelen.";
$lang['No_template_dir'] = "Kan inte öppna mall katalogen. Den kan vara oläsbar av webservern (kontrollera rättigheterna) eller saknas.";
$lang['Cannot_remove_style'] = "Du kan inte ta bort den valda stilen då den just nu är de forumets standard stil. Ändra standard stil och försök igen.";
$lang['Style_exists'] = "Stilen finns redan, gå tillbaka ovh välj ett annat namn.";

$lang['Click_return_styleadmin'] = "Klicka %shär%s för att återgå till Stiladministration";

$lang['Theme_settings'] = "Tema inställningar";
$lang['Theme_element'] = "Tema Element";
$lang['Simple_name'] = "Enkelt namn";
$lang['Value'] = "Värde";
$lang['Save_Settings'] = "Spara inställningar";

$lang['Stylesheet'] = "CSS Stylesheet";
$lang['Background_image'] = "Background Image";
$lang['Background_color'] = "Background Colour";
$lang['Theme_name'] = "Tema namn";
$lang['Link_color'] = "Link Colour";
$lang['Text_color'] = "Text Colour";
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
$lang['img_poll_size'] = "Omröstning bild storlek [px]";
$lang['img_pm_size'] = "Privat meddelande status storlek [px]";


//
// Install Process
//
$lang['Welcome_install'] = "Välkommen till phpBB 2 installationen";
$lang['Initial_config'] = "Grundläggande  konfiguration";
$lang['DB_config'] = "Databas konfiguration";
$lang['Admin_config'] = "Admin konfiguration";
$lang['continue_upgrade'] = "När du har laddat ner din config fil till din lokala maskin kan du välja \"Fortsätta uppgraderingen\" knappen nedan för att fortsätta uppgraderingsprocessen. Vänta med att ladda upp config filen tills uppgraderingsprocessen är färdig.";
$lang['upgrade_submit'] = "Fortsätta uppgraderingen";

$lang['Installer_Error'] = "Ett fel har uppstått under installationen";
$lang['Previous_Install'] = "En föregående installation har upptäckts";
$lang['Install_db_error'] = "Ett fel uppstod vid uppdateringen av databasen";

$lang['Re_install'] = "Din föregående installation är fortfarande aktiv. <br /><br />Om du vill ominstallera phpBB 2 bör du klicka på Ja knappen nedan. Var medveten om att detta förstör all befintlig data, ingen säkerhetskopiering kommer att ske! Administratörs användarnamnet och lösenord som du har använt för att logga in till forumet kommer att återskapas efter ominstallation, inga andra inställningar kommer att behållas. <br /><br />Tänk igenom det noga innan du trycker på Ja!";

$lang['Inst_Step_0'] = "Tack för att du valt phpBB 2. För att fullborda installation fyll i information som efterfrågas nedan. Notera att databasen som du vill installera till måste finnas. Om du installerar till en databas som använder ODBC, e.g. MS Access så bör du först skapa en DSN för den innan du går vidare.";

$lang['Start_Install'] = "Starta installationen";
$lang['Finish_Install'] = "Avsluta installationen";

$lang['Default_lang'] = "Standard språk i forumet";
$lang['DB_Host'] = "Databas server värdname / DSN";
$lang['DB_Name'] = "Ditt databas namn";
$lang['DB_Username'] = "Databas användarnamn";
$lang['DB_Password'] = "Databas lösenord";
$lang['Database'] = "Din Databas";
$lang['Install_lang'] = "Välj språk för installation";
$lang['dbms'] = "Databas Typ";
$lang['Table_Prefix'] = "Prefix för tabler i databasen";
$lang['Admin_Username'] = "Administratör användarnamn";
$lang['Admin_Password'] = "Administratör lösenord";
$lang['Admin_Password_confirm'] = "Administratör lösenord [ bekräfta ]";

$lang['Inst_Step_2'] = "Din administratörs användare har skapats. Vid detta tillfälle är din grundinstallation färdig. Du kommer nu att skickas till en sida där du har möjlighet att administrera din nya installation. Var god kontrollera dina Allmäna inställningar och gör nödvändiga ändringar. Tack för att du valt phpBB 2.";

$lang['Unwriteable_config'] = "Din config fil är icke skrivbar för tillfället. En kopia av config filen kommer att skickas till dig när du klickar på kanppen nedan. Du bör ladda upp denna fil till samma katalog som phpBB 2. När detta är gjort bör du logga in med ditt administratör användarnamn och lösenord (som du angav i ett tidigare formulär) och besöka admin kontrollpanelen (en länk kommer att finns längst ner på varje sida när du väl har logga int) för att kontrollera den allmänna konfigurationen. Tack för att du valt phpBB 2.";
$lang['Download_config'] = "Ladda ner konfiguration";

$lang['ftp_choose'] = "Välj nedladdningsmetod";
$lang['ftp_option'] = "<br />Eftersom FTP tillägg är aktiverat i denna version av PHP ges du också möjlighet att försöka ftp:a config filen till servern helt automatiskt.";
$lang['ftp_instructs'] = "Du har valt att ftp:a filen till kontot som har phpBB 2 helt automatiskt. Ange informationen som saknas nedan. Notera att FTP sökvägen ska vara exakt samma sökväg till din phpBB 2 installation som du skulle använda om du använder en vanlig ftp klient.";
$lang['ftp_info'] = "Ange din FTP information";
$lang['Attempt_ftp'] = "Försöker skriva config filen till rätt ställe via ftp";
$lang['Send_file'] = "Skicka filen till mig så fixar jag det manuellt via ftp";
$lang['ftp_path'] = "FTP sökväg till phpBB 2";
$lang['ftp_username'] = "Ditt FTP användarnamn";
$lang['ftp_password'] = "Ditt FTP lösenord";
$lang['Transfer_config'] = "Starta överföring";
$lang['NoFTP_config'] = "Försöket att ftp:a config filen misslyckades. Ladda hem filen och ftp:a upp filen manuellt.";

$lang['Install'] = "Installera";
$lang['Upgrade'] = "Uppgradera";


$lang['Install_Method'] = "Välj din installationsmetod";

$lang['Install_No_Ext'] = "PHP konfigurationen på din server stödjer inte den databas typ du har valt";

$lang['Install_No_PCRE'] = "phpBB2 kräver den \"Perl-Compatible Regular Expressions Module for php\" vilket din php konfiguration inte stödjer";

//
// That's all Folks!
// -------------------------------------------------

?>
