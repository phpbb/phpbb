<?php
/***************************************************************************
 *                           lang_admin.php [Hungarian]
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

/***************************************************************************
 * Hungarian translation    : (C) 2002 Gergely EGERVARY
 * Email                    : mauzi@expertlan.hu
 *
 * COMMON TERMS USED:
 *
 * Permission -> Jogosultság
 * Smiley -> Emotikon, Smiley
 * Theme -> Séma
 * Style -> Stílus
 *
 * grep "XXX mauzi" for TODO's
 *
 ***************************************************************************/

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "Általános";
$lang['Users'] = "Felhasználók";
$lang['Groups'] = "Csoportok";
$lang['Forums'] = "Fórumok";
$lang['Styles'] = "Stílusok";

$lang['Configuration'] = "Beállítások";
$lang['Permissions'] = "Jogosultságok";
$lang['Manage'] = "Menedzsment";
$lang['Disallow'] = "Foglalt nevek";
$lang['Prune'] = "Karbantartás";
$lang['Mass_Email'] = "Körlevél";
$lang['Ranks'] = "Rangok";
$lang['Smilies'] = "Emotikonok";
$lang['Ban_Management'] = "Letiltás";
$lang['Word_Censor'] = "Szó cenzorok";
$lang['Export'] = "Exportálás";
$lang['Create_new'] = "Létrehozás";
$lang['Add_new'] = "Új";
$lang['Backup_DB'] = "Adatbázis archiválása";
$lang['Restore_DB'] = "Adatbázis helyreállítása";


//
// Index
//
$lang['Admin'] = "Adminisztráció";
$lang['Not_admin'] = "Nincs joga adminisztrálni ezt a fórumot";
$lang['Welcome_phpBB'] = "Üdvözli a phpBB!";
$lang['Admin_intro'] = "Köszönjük, hogy a phpBB-t választotta fórum szoftverének. Ezen az oldalon megtekintheti a fórumának különféle statisztikáit. Bármikor visszatérhet erre az oldalra, ha az <u>Admin Index</u> linkre kattint a bal panelon. A fórumhoz való visszatéréshet kattintson a phpBB logóra, szintén a bal panelon. A képernyõ bal oldalán található linkek segítségével könnyedén beállíthatja a fórumot, minden egyes képernyõn talál utasításokat a használathoz.";
$lang['Main_index'] = "Fórum Tartalomjegyzék";
$lang['Forum_stats'] = "Fórum Statisztikák";
$lang['Admin_Index'] = "Admin Tartalomjegyzék";
$lang['Preview_forum'] = "Fórum Elõnézet";

$lang['Click_return_admin_index'] = "Kattintson %side%s az Admin Tartalomjegyzékhez való visszatéréshez";

$lang['Statistic'] = "Statisztikák";
$lang['Value'] = "Érték";
$lang['Number_posts'] = "Hozzászólások száma";
$lang['Posts_per_day'] = "Hozzászólások naponta";
$lang['Number_topics'] = "Témák száma";
$lang['Topics_per_day'] = "Témák naponta";
$lang['Number_users'] = "Felhasználók száma";
$lang['Users_per_day'] = "Felhasználók naponta";
$lang['Board_started'] = "Fórum elindítva";
$lang['Avatar_dir_size'] = "Avatar könyvtár mérete";
$lang['Database_size'] = "Adatbázis mérete";
$lang['Gzip_compression'] ="Gzip tömörítés";
$lang['Not_available'] = "Nem elérhetõ";

$lang['ON'] = "BE"; // This is for GZip compression
$lang['OFF'] = "KI"; 


//
// DB Utils
//
$lang['Database_Utilities'] = "Adatbázis menedzsment";

$lang['Restore'] = "Helyreállítás";
$lang['Backup'] = "Archiválás";
$lang['Restore_explain'] = "Helyreállíthatja az összes phpBB adattáblát egy fileból. Ha a szerver támogatja, feltölthet egy gzip tömörített filet, és a rendszer automatikusan kicsomagolja. <b>FIGYELEM</b> Ezzel felülírja az összes meglévõ adatot. A helyreállítás hosszú idõt vehet igénybe, amíg a folyamat el nem készül ne böngésszen más oldalakat.";
$lang['Backup_explain'] = "Archiválhatja az összes phpBB adattáblát. Ha vannak egyéni adattáblák a phpBB adatbázisában, egyúttal azokat is archiválhatja, adja az egyéni adattáblák nevét az alábbi mezõben. Ha a szerver támogatja, tömörítheti az adatokat, hogy kevesebb adatot kelljen letöltenie.";

$lang['Backup_options'] = "Archiválás beállításai";
$lang['Start_backup'] = "Archiválás indítása";
$lang['Full_backup'] = "Teljes archiválás";
$lang['Structure_backup'] = "Csak a struktúra archiválása";
$lang['Data_backup'] = "Csak az adatok archiválása";
$lang['Additional_tables'] = "Egyéb adattáblák";
$lang['Gzip_compress'] = "Gzip tömörítés";
$lang['Select_file'] = "Válasszon file-t";
$lang['Start_Restore'] = "Helyreállítás indítása";

$lang['Restore_success'] = "Az adatbázis sikeresen helyreállítva.<br /><br />A fórum az archiválás elõtti állapotába került.";
$lang['Backup_download'] = "A letöltés hamarosan elkezdõdik. Várjon türelemmel";
$lang['Backups_not_supported'] = "Pardon, az adatbázis archiválás jelenleg nem támogatott az adatbázis rendszerén";

$lang['Restore_Error_uploading'] = "Hiba a file feltöltése során";
$lang['Restore_Error_filename'] = "Filenév hiba, próbálja másik néven";
$lang['Restore_Error_decompress'] = "Nem lehet kitömöríteni a gzip file-t, próbálja tömörítés nélkül";
$lang['Restore_Error_no_file'] = "Nem lett file feltöltve";


//
// Auth pages
//
$lang['Select_a_User'] = "Válasszon felhasználót";
$lang['Select_a_Group'] = "Válasszon csoportot";
$lang['Select_a_Forum'] = "Válasszon fórumot";
$lang['Auth_Control_User'] = "Felhasználói jogosultságok beállítása"; 
$lang['Auth_Control_Group'] = "Csoport jogosultságok beállítása"; 
$lang['Auth_Control_Forum'] = "Fórum jogosultságok beállítása"; 
$lang['Look_up_User'] = "Felhasználó megtekintése"; 
$lang['Look_up_Group'] = "Csoport megtekintése"; 
$lang['Look_up_Forum'] = "Fórum megtekintése"; 

$lang['Group_auth_explain'] = "Beállíthatja a csoporthoz rendelt jogosultságokat. Ne felejtse el, hogy a csoport beállításoktól függetlenül a felhasználók egyéni jogosultságai is érvényben maradnak.";
$lang['User_auth_explain'] = "Beállíthatja a felhasználókhoz rendelt jogosultságokat. Ne felejtse el, hogy a felhasználó egyéni beállításaitól függetlenül a csoportok jogosultságai is érvényben maradnak.";
$lang['Forum_auth_explain'] = "Beállíthatja az egyes fórumok hozzáférési lehetõségeit. Kétféle beállítási lehetõség közül választhat. Az Egyszerû módban sablonok alapján választhat, a Haladó módban részletesen beállíthatja a hozzáférési lehetõségeket.";

$lang['Simple_mode'] = "Egyszerû mód";
$lang['Advanced_mode'] = "Haladó mód";
$lang['Moderator_status'] = "Moderátor státusz";

$lang['Allowed_Access'] = "Hozzáférés engedélyezve";
$lang['Disallowed_Access'] = "Hozzáférés tiltva";
$lang['Is_Moderator'] = "Moderátor";
$lang['Not_Moderator'] = "Nem Moderátor";

$lang['Conflict_warning'] = "Hozzáférési jog ütközés";
$lang['Conflict_access_userauth'] = "A felhasználónak a továbbiakban is van hozzáférési joga a fórumhoz a csoport tagsága miatt. Módosítsa a csoport jogait vagy a felhasználó csoport tagságát ha meg akarja vonni a hozzáférést. Az érintett csoportok (és fórumok) az alábbiak:";
$lang['Conflict_mod_userauth'] = "A felhasználónak a továbbiakban is van moderátori joga a fórumhoz a csoport tagsága miatt. Módosítsa a csoport jogait vagy a felhasználó csoport tagságát ha meg akarja vonni moderátori jogot. Az érintett csoportok (és fórumok) az alábbiak:";

$lang['Conflict_access_groupauth'] = "Az alábbi felhasználónak (vagy felhasználóknak) a továbbiakban is van hozzáférési joga a fórumhoz az egyéni jogosultságaik miatt. Módosítsa a felhasználó (vagy felhasználók) jogosultságait, ha meg akarja vonni a hozzáférést. Az érintett felhasználók (és fórumok) az alábbiak:";
$lang['Conflict_mod_groupauth'] = "Az alábbi felhasználónak (vagy felhasználóknak) a továbbiakban is van moderátori joga a fórumhoz az egyéni jogosultságaik miatt. Módosítsa a felhasználó (vagy felhasználók) jogosultságait, ha meg akarja vonni a moderátori jogokat. Az érintett felhasználók (és fórumok) az alábbiak:";

$lang['Public'] = "Publikus";
$lang['Private'] = "Privát";
$lang['Registered'] = "Regisztrált";
$lang['Administrators'] = "Adminisztrátorok";
$lang['Hidden'] = "Rejtett";

$lang['View'] = "Megtekintés";
$lang['Read'] = "Olvasás";
$lang['Post'] = "Hozzászólás";
$lang['Reply'] = "Válaszolás";
$lang['Edit'] = "Szerkesztés";
$lang['Delete'] = "Törlés";
$lang['Sticky'] = "Fontos";
$lang['Announce'] = "Hirdetmény"; 
$lang['Vote'] = "Szavazás";
$lang['Pollcreate'] = "Szavazás nyitása";

$lang['Permissions'] = "Jogosultságok";
$lang['Simple_Permission'] = "Egyszerû jogosultság";

$lang['User_Level'] = "Felhasználói szint"; 
$lang['Auth_User'] = "Felhasználó";
$lang['Auth_Admin'] = "Adminisztrátor";
$lang['Group_memberships'] = "Csoport tagság";
$lang['Usergroup_members'] = "A csoport tagjai:";

$lang['Forum_auth_updated'] = "Fórum jogosultságok frissítve";
$lang['User_auth_updated'] = "Felhasználó jogosultságok frissítve";
$lang['Group_auth_updated'] = "Csoport jogosultságok frissítve";

$lang['Auth_updated'] = "Jogosultságok frissítve";
$lang['Click_return_userauth'] = "Kattintson %side%s a Felhasználói Jogosultságok való visszatéréshez";
$lang['Click_return_groupauth'] = "Kattintson %side%s a Csoport Jogosultságok való visszatéréshez";
$lang['Click_return_forumauth'] = "Kattintson %side%s a Fórum Jogosultságok való visszatéréshez";


//
// Banning
//
$lang['Ban_control'] = "Letiltások Beállítása";
$lang['Ban_explain'] = "Letilthat felhasználói azonosítókat, IP cím tartományokat, gépneveket. A letiltott gépek a fórum tartalomjegyzékét sem tudják elérni. Ha meg akarja akadályozni, hogy a felhasználó másik azonosítót regisztráljon, tiltsa le az email címét. Ha csak az email címet tiltja le, a felhasználó a továbbiakban is tudja olvasni a fórumot, valamint tud hozzászólásokat írni.";
$lang['Ban_explain_warn'] = "Fontos: Lecsökkentheti az adatbázisba kerülõ IP címek mennyiségét, ha használja a Joker karaktereket. Ha mindenképpen fel kell sorolnia több egyedi címet, ügyeljen a lista egyszerûségére és átláthatóságára.";

$lang['Select_username'] = "Válasszon felhasználónevet";
$lang['Select_ip'] = "Válasszon IP címet";
$lang['Select_email'] = "Válasszon Email címet";

$lang['Ban_username'] = "Egy vagy több felhasználó letiltása";
$lang['Ban_username_explain'] = "Több felhasználót is letilthat egyszerre, ha több nevet kijelöl a böngészõjében";

$lang['Ban_IP'] = "Egy vagy több IP cím vagy gépnév letiltása";
$lang['IP_hostname'] = "IP címek vagy gépnevek";
$lang['Ban_IP_explain'] = "Több IP cím vagy gépnév megadásakor használja a vesszõt (,) elválasztásra. IP címtartomány megadásához használja a kötõjelet (-) az elsõ és az utolsó cím elválasztásához. Használja a csillagot (*) mint Joker";

$lang['Ban_email'] = "Egy vagy több Email cím letiltása";
$lang['Ban_email_explain'] = "Több email cím megadásakor használja a vesszõt (,) elválasztásra. Használja a csillagot (*) mint Joker. Például: *@hotmail.com";

$lang['Unban_username'] = "Egy vagy több felhasználó letiltásának feloldása";
$lang['Unban_username_explain'] = "Több felhasználó letiltását is feloldhatja egyszerre, ha több nevet kijelöl a böngészõjében";

$lang['Unban_IP'] = "Egy vagy több IP cím vagy gépnév letiltásának feloldása";
$lang['Unban_IP_explain'] = "Több IP cím vagy gépnév letiltását is feloldhatja egyszerre, ha több IP címet vagy gépnevet kijelöl a böngészõjében";

$lang['Unban_email'] = "Egy vagy több Email cím letiltásának feloldása";
$lang['Unban_email_explain'] = "Több email cím letiltását is feloldhatja egyszerre, ha több email címet kijelöl a böngészõjében ";

$lang['No_banned_users'] = "Nincsenek letiltott felhasználók";
$lang['No_banned_ip'] = "Nincsenek letiltott IP címek";
$lang['No_banned_email'] = "Nincsenek letiltott Email címek";

$lang['Ban_update_sucessful'] = "A letiltások sikeresen frissítve";
$lang['Click_return_banadmin'] = "Kattintson %side%s a Letiltások Beállításához való visszatéréshez";


//
// Configuration
//
$lang['General_Config'] = "Általános Beállítások";
$lang['Config_explain'] = "Beállíthatja a fórum alapvetõ tulajdonságait. A felhasználók, csoportok, és a fórum további adminisztrálásához használja a linkeket a bal panelon.";

$lang['Click_return_config'] = "Kattintson %side%s az Általános Beállításokhoz való visszatéréshez";

$lang['General_settings'] = "Általános fórum beállítások";
$lang['Site_name'] = "Fórum neve";
$lang['Site_desc'] = "Fórum leírása";
$lang['Board_disable'] = "Fórum letiltása";
$lang['Board_disable_explain'] = "A felhasználók nem érik el a fórumot. Ne jelentkezzen ki, amíg a fórum le van tiltva, mert nem fog tudni vissza bejelentkezni!";
$lang['Acct_activation'] = "Azonosító aktiválás";
$lang['Acc_None'] = "Nincs"; // These three entries are the type of activation
$lang['Acc_User'] = "Felhasználó";
$lang['Acc_Admin'] = "Adminisztrátor";

$lang['Abilities_settings'] = "Felhasználó és Fórum alapbeállítások";
$lang['Max_poll_options'] = "Választási lehetõségek maximális száma szavazásnál";
$lang['Flood_Interval'] = "Flood Periódus";
$lang['Flood_Interval_explain'] = "Idõtartam, aminek el kell telnie egy felhasználó hozzászólásai között"; 
$lang['Board_email_form'] = "Levelezés a fórumon keresztül";
$lang['Board_email_form_explain'] = "A felhasználók levelezhetnek a fórumon keresztül";
$lang['Topics_per_page'] = "Témák oldalanként";
$lang['Posts_per_page'] = "Hozzászólások oldalanként";
$lang['Hot_threshold'] = "Posts for Popular Threshold"; // XXX mauzi TODO
$lang['Default_style'] = "Alapértelmezett stílus";
$lang['Override_style'] = "Stílusának felülbírálása";
$lang['Override_style_explain'] = "Felülbírálja a felhasználók stílus beállításait";
$lang['Default_language'] = "Alapértelmezett nyelv";
$lang['Date_format'] = "Dátum formátum";
$lang['System_timezone'] = "Rendszer idõzóna";
$lang['Enable_gzip'] = "GZip tömörítés engedélyezése";
$lang['Enable_prune'] = "Fórum karbantartás engedélyezése";
$lang['Allow_HTML'] = "HTML Engedélyezése";
$lang['Allow_BBCode'] = "BBCode Engedélyezése";
$lang['Allowed_tags'] = "Engedélyezett HTML tag-ek";
$lang['Allowed_tags_explain'] = "Használja a vesszõt elválasztásra";
$lang['Allow_smilies'] = "Emotikonok engedélyezése";
$lang['Smilies_path'] = "Emotikonok elérési útja";
$lang['Smilies_path_explain'] = "Elérési út a phpBB fõkönyvtára alatt, pl. images/smilies";
$lang['Allow_sig'] = "Aláírások engedélyezése";
$lang['Max_sig_length'] = "Aláírások maximális hossza";
$lang['Max_sig_length_explain'] = "Maximum engedélyezett karakterek az aláírásban";
$lang['Allow_name_change'] = "Felhasználónév módosítás engedélyezése";

$lang['Avatar_settings'] = "Avatar Beállítások";
$lang['Allow_local'] = "Avatar galéria engedélyezése";
$lang['Allow_remote'] = "Avatar belinkelése távoli géprõl";
$lang['Allow_remote_explain'] = "Más Weboldalakra feltöltött képek engedélyezése";
$lang['Allow_upload'] = "Avatar feltöltés engedélyezése";
$lang['Max_filesize'] = "Avatar file maximális mérete";
$lang['Max_filesize_explain'] = "A feltöltött avatar fileokra";
$lang['Max_avatar_size'] = "Avatar kép maximális mérete";
$lang['Max_avatar_size_explain'] = "(Magasság x Szélesség pixelben)";
$lang['Avatar_storage_path'] = "Avatar elérési út";
$lang['Avatar_storage_path_explain'] = "Elérési út a phpBB fõkönyvtára alatt, pl. images/avatars";
$lang['Avatar_gallery_path'] = "Avatar galéria elérési út";
$lang['Avatar_gallery_path_explain'] = " Elérési út a phpBB fõkönyvtára alatt, pl. images/avatars/gallery";

$lang['COPPA_settings'] = "COPPA Beállítások";
$lang['COPPA_fax'] = "COPPA Fax szám";
$lang['COPPA_mail'] = "COPPA Postacím";
$lang['COPPA_mail_explain'] = "Erre a postacímre kell elküldeni a szülõknek a COPPA regisztrációs kérdõívet";

$lang['Email_settings'] = "Email Beállítások";
$lang['Admin_email'] = "Adminisztrátor Email címe";
$lang['Email_sig'] = "Email Aláírás";
$lang['Email_sig_explain'] = "Ez a szövegrészlet minden levélhez csatolható, amit a fórum küld a felhasználóknak";
$lang['Use_SMTP'] = "SMTP szerver használata a levelezéshez";
$lang['Use_SMTP_explain'] = "Válassza ezt a lehetõséget, ha egy SMTP szerveren keresztül akarja küldeni a leveleket a helyi sendmail helyett";
$lang['SMTP_server'] = "SMTP szerver címe";

$lang['Disable_privmsg'] = "Privát Üzenetek";
$lang['Inbox_limits'] = "Maximális üzenetek száma a Beérkezett Üzenetek mappában";
$lang['Sentbox_limits'] = "Maximális üzenetek száma az Elküldött Üzenetek mappában";
$lang['Savebox_limits'] = "Maximális üzenetek száma az Elmentett Üzenetek mappában";

$lang['Cookie_settings'] = "Cookie Beállítások"; 
$lang['Cookie_settings_explain'] = "Beállíthatja a böngészõknek küldött cookie-kat. A legtöbb esetben az alapbeállítások megfelelõek. Legyen körültekintõ, mert egy helytelen beállítás megakadályozhatja a felhasználók belépését.";
$lang['Cookie_name'] = "Cookie neve";
$lang['Cookie_domain'] = "Cookie domain";
$lang['Cookie_path'] = "Cookie elérési út";
$lang['Session_length'] = "Cookie érvényessége [ másodperc ]";
$lang['Cookie_secure'] = "Cookie kódolása [ https ]";


//
// Forum Management
//
$lang['Forum_admin'] = "Fórum Adminisztráció";
$lang['Forum_admin_explain'] = "Hozzáadhat, törölhet, szerkeszthet, átrendezhet fórumokat és témaköröket";
$lang['Edit_forum'] = "Fórum szerkesztése";
$lang['Create_forum'] = "Új fórum létrehozása";
$lang['Create_category'] = "Új témakör létrehozása";
$lang['Remove'] = "Törlés";
$lang['Action'] = "Action"; // XXX mauzi innetol
$lang['Update_order'] = "Update Order";
$lang['Config_updated'] = "Fórum beállítások sikeresen frissítve";
$lang['Edit'] = "Szerkesztés";
$lang['Delete'] = "Törlés";
$lang['Move_up'] = "Feljebb";
$lang['Move_down'] = "Lejjebb";
$lang['Resync'] = "Szinkronizálás";
$lang['No_mode'] = "No mode was set";
$lang['Forum_edit_delete_explain'] = "The form below will allow you to customize all the general board options. For User and Forum configurations use the related links on the left hand side";

$lang['Move_contents'] = "Move all contents";
$lang['Forum_delete'] = "Fórum törlése";
$lang['Forum_delete_explain'] = "The form below will allow you to delete a forum (or category) and decide where you want to put all topics (or forums) it contained.";

$lang['Forum_settings'] = "Fórum általános beállításai";
$lang['Forum_name'] = "Fórum neve";
$lang['Forum_desc'] = "Leírása";
$lang['Forum_status'] = "Fórum státusza";
$lang['Forum_pruning'] = "Automatikus karbantartás";

$lang['prune_freq'] = 'Check for topic age every';
$lang['prune_days'] = "Törli azokat a témákat, amelyekhez nem szóltak hozzá";
$lang['Set_prune_data'] = "You have turned on auto-prune for this forum but did not set a frequency or number of days to prune. Please go back and do so";

$lang['Move_and_Delete'] = "Mozgatás és Törlés";

$lang['Delete_all_posts'] = "Összes hozzászólás törlése";
$lang['Nowhere_to_move'] = "Nincs hova mozgatni";

$lang['Edit_Category'] = "Témakör szerkesztése";
$lang['Edit_Category_explain'] = "Használja ezt a mezõt a témakör átnevezéséhez";

$lang['Forums_updated'] = "Fórum és Témakör információk sikeresen frissítve";

$lang['Must_delete_forums'] = "Törölnie kell az összes fórumot a témakör törlése elõtt";

$lang['Click_return_forumadmin'] = "Kattintson %side%s a Fórum Adminisztrációhoz való visszatéréshez";


//
// Smiley Management
//
$lang['smiley_title'] = "Emotikonok szerkesztése";
$lang['smile_desc'] = "Az alábbiakban megadhatja az emotikonokat, amit a felhasználók alkalmazhatnak a hozzászólásaikban és a Privát Üzeneteikben.";

$lang['smiley_config'] = "Smiley Beállítások";
$lang['smiley_code'] = "Smiley Kód";
$lang['smiley_url'] = "Smiley Image File";
$lang['smiley_emot'] = "Smiley Emotion";
$lang['smile_add'] = "Új emotikon";
$lang['Smile'] = "Smile";
$lang['Emotion'] = "Jelentés";

$lang['Select_pak'] = "Select Pack (.pak) File";
$lang['replace_existing'] = "Jelenlegi Smiley lecserélése";
$lang['keep_existing'] = "Jelenlegi Smiley megtartása";
$lang['smiley_import_inst'] = "You should unzip the smiley package and upload all files to the appropriate Smiley directory for your installation.  Then select the correct information in this form to import the smiley pack.";
$lang['smiley_import'] = "Smiley Pack Import";
$lang['choose_smile_pak'] = "Choose a Smile Pack .pak file";
$lang['import'] = "Import Smileys";
$lang['smile_conflicts'] = "What should be done in case of conflicts";
$lang['del_existing_smileys'] = "Delete existing smileys before import";
$lang['import_smile_pack'] = "Smiley Csomag importálása";
$lang['export_smile_pack'] = "Smiley Csomag exportálása";
$lang['export_smiles'] = "To create a smiley pack from your currently installed smileys, click %sHere%s to download the smiles.pak file. Name this file appropriately making sure to keep the .pak file extension.  Then create a zip file containing all of your smiley images plus this .pak configuration file.";

$lang['smiley_add_success'] = "A Smiley sikeresen hozzáadva";
$lang['smiley_edit_success'] = "A Smiley sikeresen frissítve";
$lang['smiley_import_success'] = "A Smiley Csomag sikeresen importálva!";
$lang['smiley_del_success'] = "A Smiley sikeresen eltávolítva";
$lang['Click_return_smileadmin'] = "Kattintson %side%s az Emotikonok szerkesztéséhez való visszatéréshez";


//
// User Management
//
$lang['User_admin'] = "Felhasználó Adminisztráció";
$lang['User_admin_explain'] = "Az alábbiakban megváltoztathatja a felhasználók beállításait. A jogosultságok módosításához használja a Felhasználó és Csoport Jogosultságok rendszerét.";

$lang['Look_up_user'] = "Felhasználó megtekintése";

$lang['Admin_user_fail'] = "Nem lehet frissíteni a felhasználó profilját.";
$lang['Admin_user_updated'] = "A felhasználó profilja sikeresen frissítve.";
$lang['Click_return_useradmin'] = "Kattintson %side%s a Felhasználó Adminisztrációhoz való visszatéréshez";

$lang['User_delete'] = "Felhasználó törlése";
$lang['User_delete_explain'] = "Kattintson ide a felhasználó törléséhez. Ezt nem lehet visszaállítani.";
$lang['User_deleted'] = "Felhasználó sikeresen törölve";

$lang['User_status'] = "A felhasználó aktív";
$lang['User_allowpm'] = "Küldhet Privát Üzenetet";
$lang['User_allowavatar'] = "Beállíthat Avatar képet";

$lang['Admin_avatar_explain'] = "Megtekintheti és törölheti a felhasználó jelenlegi avatarját.";

$lang['User_special'] = "Speciális adminisztrátori mezõk";
$lang['User_special_explain'] = "Ezeket a beállításokat a felhasználók nem tudják megváltoztatni.";


//
// Group Management
//
$lang['Group_administration'] = "Csoport Adminisztráció";
$lang['Group_admin_explain'] = "Az alábbiakban adminisztrálhatja a felhasználó csoportokat, létrehozhat, szerkeszthet, törölhet csoportokat. Megadhatja a csoport moderátorát, megváltoztathatja a csoport hozzáférési módját.";
$lang['Error_updating_groups'] = "Hiba történt a csoport frissítése közben";
$lang['Updated_group'] = "A csoport sikeresen frissítve";
$lang['Added_new_group'] = "A csoport sikeresen létrehozva";
$lang['Deleted_group'] = "A csoport sikeresen törölve";
$lang['New_group'] = "Új csoport létrehozása";
$lang['Edit_group'] = "Csoport szerkesztése";
$lang['group_name'] = "Csoport neve";
$lang['group_description'] = "Csoport leírása";
$lang['group_moderator'] = "Csoport moderátor";
$lang['group_status'] = "Csoport státusz";
$lang['group_open'] = "Nyílt csoport";
$lang['group_closed'] = "Zárt csoport";
$lang['group_hidden'] = "Rejtett csoport";
$lang['group_delete'] = "Csoport törlése";
$lang['group_delete_check'] = "Törli ezt a csoportot?";
$lang['submit_group_changes'] = "Változtatások érvényesítése";
$lang['reset_group_changes'] = "Változtatások visszavonása";
$lang['No_group_name'] = "Meg kell adnia egy csoportnevet";
$lang['No_group_moderator'] = "Meg kell adnia egy moderátort ennek a csoportnak";
$lang['No_group_mode'] = "Meg kell adnia a csoport módját";
$lang['delete_group_moderator'] = "Régi csoport moderátor törlése?";
$lang['delete_moderator_explain'] = "Ha megváltoztatja a csoport moderátorát, válassza ezt a lehetõséget a régi moderátor csoporttagságának megszûntetéséhez. Egyébként a régi moderátor tagja marad a csoportnak.";
$lang['Click_return_groupsadmin'] = "Kattintson %side%s a Csoport Adminisztrációhoz való visszatéréshez.";
$lang['Select_group'] = "Válasszon csoportot";
$lang['Look_up_group'] = "Csoport megtekintése";


//
// Prune Administration
//
$lang['Forum_Prune'] = "Fórum karbantartás";
$lang['Forum_Prune_explain'] = "Törölheti azokat a témákat, amelyekre egy megadott ideje nem érkezett hozzászólás. Ha nem ad meg idõtartamot, az összes téma törlõdik. A rendszer nem törli azokat a témákat, amelyekben még aktív szavazások folynak, valamint nem törli a hírdetményeket. Ezeket csak kézzel lehet eltávolítani.";
$lang['Do_Prune'] = "Kezdõdhet a törlés!";
$lang['All_Forums'] = "Összes fórum";
$lang['Prune_topics_not_posted'] = "Téma törlése, ha nem érkezett hozzászólás ennyi ideig:";
$lang['Topics_pruned'] = "Témák törölve";
$lang['Posts_pruned'] = "Hozzászólások törölve";
$lang['Prune_success'] = "A fórum karbantartása sikeresen elkészült";


//
// Word censor
//
$lang['Words_title'] = "Szó cenzúrázás";
$lang['Words_explain'] = "Az alábbiakban megadhatja azokat a szavakat, amelyek automatikusan cenzúrázva lesznek a fórumon. Használja a Joker karaktert (*) szótöredékek megadásához. Például *próbál* megfelelhet a kipróbálható, próbál* megfelelhet a próbálható, *próbál megfelelhet a kipróbál szavaknak.";
$lang['Word'] = "Szó";
$lang['Edit_word_censor'] = "Szerkesztés";
$lang['Replacement'] = "Helyettesítõ";
$lang['Add_new_word'] = "Új szó hozzáadása";
$lang['Update_word'] = "Frissítés";

$lang['Must_enter_word'] = "Meg kell adnia egy szót, és egy helyettesítõ szót.";
$lang['No_word_selected'] = "Nem adott meg szót a szerkesztéshez";

$lang['Word_updated'] = "A szó sikeresen frissítve";
$lang['Word_added'] = "A szó sikeresen hozzáadva";
$lang['Word_removed'] = "A szó sikeresen eltávolítva";

$lang['Click_return_wordadmin'] = "Kattintson %side%s a Szó cenzúrázáshoz való visszatéréshez";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Az alábbiakban levelet küldhet az összes felhasználónak, vagy egy csoport összes tagjának. Egy levél fog érkezni a megadott adminisztrátori email címre, ahol a Bcc: mezõben fog szerepelni a felhasználók címe. Ha sok felhasználónak küld levelet, legyen türelemmel a küldésnél. A körlevél küldése hosszú idõt vehet igénybe, ne állítsa le a folyamatot, a végén kap értesítést, ha a rendszer elkészült.";
$lang['Compose'] = "Levél írása"; 

$lang['Recipients'] = "Címzettek"; 
$lang['All_users'] = "Összes felhasználó";

$lang['Email_successfull'] = "Az üzenet elküldve";
$lang['Click_return_massemail'] = "Kattintson %side%s a Körlevél küldéshez való visszatéréshez";


//
// Ranks admin
//
$lang['Ranks_title'] = "Rang Adminisztráció";
$lang['Ranks_explain'] = "Az alábbiakban megadhat felhasználói rangokat. A speciális rangok beállításához használja a Felhasználó Menedzsment lehetõséget";

$lang['Add_new_rank'] = "Új rang hozzáadása";

$lang['Rank_title'] = "Rang címe";
$lang['Rank_special'] = "Speciális rangként beállítás";
$lang['Rank_minimum'] = "Minimum Hozzászólások";
$lang['Rank_maximum'] = "Maximum Hozzászólások";
$lang['Rank_image'] = "Rangjelzõ kép (Elérési út a phpBB fõkönyvtára alatt)";
$lang['Rank_image_explain'] = "Képet társíthat a ranghoz";

$lang['Must_select_rank'] = "You must select a rank";
$lang['No_assigned_rank'] = "No special rank assigned";

$lang['Rank_updated'] = "Rang sikeresen frissítve";
$lang['Rank_added'] = "Rang sikeresen hozzáadva";
$lang['Rank_removed'] = "Rang sikeresen törölve";

$lang['Click_return_rankadmin'] = "Kattintson %side%s a Rang Adminisztrációhoz való visszatéréshez";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Foglalt felhasználónevek Beállítása";
$lang['Disallow_explain'] = "Here you can control usernames which will not be allowed to be used.  Disallowed usernames are allowed to contain a wildcard character of *.  Please note that you will not be allowed to specify any username that has already been registered, you must first delete that name then disallow it";

$lang['Delete_disallow'] = "Törlés";
$lang['Delete_disallow_title'] = "Foglalt felhasználónév törlése";
$lang['Delete_disallow_explain'] = "You can remove a disallowed username by selecting the username from this list and clicking submit";

$lang['Add_disallow'] = "Hozzáadás";
$lang['Add_disallow_title'] = "Foglalt felhasználónév hozzáadása";
$lang['Add_disallow_explain'] = "Használja a * karaktert, mint Joker";

$lang['No_disallowed'] = "Nincsenek foglalt nevek";

$lang['Disallowed_deleted'] = "A foglalt felhasználónév sikeresen törölve";
$lang['Disallow_successful'] = "A foglalt felhasználónév sikeresen hozzáadva";
$lang['Disallowed_already'] = "The name you entered could not be disallowed. It either already exists in the list, exists in the word censor list, or a matching username is present";

$lang['Click_return_disallowadmin'] = "Kattintson %side%s a Foglalt felhasználónevek adminisztrációjához való visszatéréshez";


//
// Styles Admin
//
$lang['Styles_admin'] = "Stílus Adminisztráció";
$lang['Styles_explain'] = "Using this facility you can add, remove and manage styles (templates and themes) available to your users";
$lang['Styles_addnew_explain'] = "The following list contains all the themes that are available for the templates you currently have. The items on this list have not yet been installed into the phpBB database. To install a theme simply click the install link beside an entry";

$lang['Select_template'] = "Válasszon Sablont";

$lang['Style'] = "Stílus";
$lang['Template'] = "Sablon";
$lang['Install'] = "Installálás";
$lang['Download'] = "Letöltés";

$lang['Edit_theme'] = "Séma Szerkesztése";
$lang['Edit_theme_explain'] = "Az alábbiakban szerkesztheti a séma beállításait";

$lang['Create_theme'] = "Séma Létrehozása";
$lang['Create_theme_explain'] = "Az alábbiakban létrehozhat egy új sémát. Ha színeket definiál (és hexadecimálisan adja meg az értékeket) ne használja a # elõtagot, pl. CCCCCC egy érvényes, #CCCCCC egy érvénytelen érték";

$lang['Export_themes'] = "Séma Exportálása";
$lang['Export_explain'] = "In this panel you will be able to export the theme data for a selected template. Select the template from the list below and the script will create the theme configuration file and attempt to save it to the selected template directory. If it cannot save the file itself it will give you the option to download it. In order for the script to save the file you must give write access to the webserver for the selected template dir. For more information on this see the phpBB 2 users guide.";

$lang['Theme_installed'] = "The selected theme has been installed successfully";
$lang['Style_removed'] = "The selected style has been removed from the database. To fully remove this style from your system you must delete the appropriate style from your templates directory.";
$lang['Theme_info_saved'] = "The theme information for the selected template has been saved. You should now return the permissions on the theme_info.cfg (and if applicable the selected template directory) to read-only";
$lang['Theme_updated'] = "The selected theme has been updated. You should now export the new theme settings";
$lang['Theme_created'] = "Theme created. You should now export the theme to the theme configuration file for safe keeping or use elsewhere"; // XXX mauzi idaig

$lang['Confirm_delete_style'] = "Biztos benne, hogy törölni akarja ezt a stílust?";

$lang['Download_theme_cfg'] = "Nem lehet exportálni a séma információs filet. Kattintson a lenti gombra a file letöltéséhez. A letöltés után bemásolhatja a megfelelõ könyvtárba, vagy felhasználhatja más fórumokon";
$lang['No_themes'] = "A megadott sablonhoz nem tartozik séma. Új séma létrehozásához kattintson az Új Stílus linkre a bal panelon";
$lang['No_template_dir'] = "Nem lehet megnyitni a sablon könyvtárat. Lehet, hogy nem létezik, vagy a Web szervernek nincs hozzáférési joga";
$lang['Cannot_remove_style'] = "Nem lehet eltávolítani a megadott stílust, mert jelenleg ez a fórum alapértelmezett stílusa. Állítsa át az alapértelmezett stílust, és próbálja újra.";
$lang['Style_exists'] = "A megadott stílusnév már létezik, válasszon másik nevet.";

$lang['Click_return_styleadmin'] = "Kattintson %side%s a Stílus Adminisztrációhoz való visszatéréshez";

$lang['Theme_settings'] = "Séma beállításai";
$lang['Theme_element'] = "Séma elem";
$lang['Simple_name'] = "Egyszerû név";
$lang['Value'] = "Érték";
$lang['Save_Settings'] = "Beállítások mentése";

$lang['Stylesheet'] = "CSS sablon";
$lang['Background_image'] = "Háttér kép";
$lang['Background_color'] = "Háttér szín";
$lang['Theme_name'] = "Séma neve";
$lang['Link_color'] = "Link szín";
$lang['Text_color'] = "Szöveg szín";
$lang['VLink_color'] = "Látogatott link szín";
$lang['ALink_color'] = "Aktív link szín";
$lang['HLink_color'] = "Hover link szín";
$lang['Tr_color1'] = "Táblázat sor szín 1";
$lang['Tr_color2'] = "Táblázat sor szín 2";
$lang['Tr_color3'] = "Táblázat sor szín 3";
$lang['Tr_class1'] = "Táblázat sor csoport 1";
$lang['Tr_class2'] = "Táblázat sor csoport 2";
$lang['Tr_class3'] = "Táblázat sor csoport 3";
$lang['Th_color1'] = "Táblázat fejléc szín 1";
$lang['Th_color2'] = "Táblázat fejléc szín 2";
$lang['Th_color3'] = "Táblázat fejléc szín 3";
$lang['Th_class1'] = "Táblázat fejléc csoport 1";
$lang['Th_class2'] = "Táblázat fejléc csoport 2";
$lang['Th_class3'] = "Táblázat fejléc csoport 3";
$lang['Td_color1'] = "Táblázat cella szín 1";
$lang['Td_color2'] = "Táblázat cella szín 2";
$lang['Td_color3'] = "Táblázat cella szín 3";
$lang['Td_class1'] = "Táblázat cella csoport 1";
$lang['Td_class2'] = "Táblázat cella csoport 2";
$lang['Td_class3'] = "Táblázat cella csoport 3";
$lang['fontface1'] = "Betûtípus 1";
$lang['fontface2'] = "Betûtípus 2";
$lang['fontface3'] = "Betûtípus 3";
$lang['fontsize1'] = "Betûtípus 1";
$lang['fontsize2'] = "Betûtípus 2";
$lang['fontsize3'] = "Betûtípus 3";
$lang['fontcolor1'] = "Betûszín 1";
$lang['fontcolor2'] = "Betûszín 2";
$lang['fontcolor3'] = "Betûszín 3";
$lang['span_class1'] = "Betûszín 1";
$lang['span_class2'] = "Betûszín 2";
$lang['span_class3'] = "Betûszín 3";
$lang['img_poll_size'] = "Szavazás kép mérete [pixel]";
$lang['img_pm_size'] = "Privát Üzenet Státusz kép mérete [pixel]";


//
// Install Process
//
$lang['Welcome_install'] = "Üdvözli a phpBB2 telepítõ!";
$lang['Initial_config'] = "Általános Beállítások";
$lang['DB_config'] = "Adatbázis Beállítások";
$lang['Admin_config'] = "Adminisztrátor Beállítások";
$lang['continue_upgrade'] = "Miután letöltötte a konfigurációs filet a gépére, kattintson a \"Frissítés\" gombra a folyamat elindításához. Várjon a konfigurációs file feltöltésével, amíg a frissítési folyamat befejezõdik.";
$lang['upgrade_submit'] = "Frissítés";

$lang['Installer_Error'] = "Hiba történt a telepítés során";
$lang['Previous_Install'] = "Korábbi telepítés";
$lang['Install_db_error'] = "Hiba történt az adatbázis frissítése során";

$lang['Re_install'] = "Az elõzõ telepítés még aktív! <br /><br />Amennyiben újra szeretné telepíteni a fórumot, kattintson az Igen gombra az alábbiakban. Tartsa szem elõtt, hogy ezzel véglegesen és visszavonhatatlanul felülírja az összes meglévõ adatot! Az adminisztrátori azonosító amit eddig használt újra létrejön az újratelepítés után, az összes többi adat elvész. <br /><br />Kétszer gondolja meg, mielõtt az Igen gombra kattint!";

$lang['Inst_Step_0'] = "Köszönjük, hogy a phpBB2 szoftvert választotta. A telepítéshez töltse ki az alábbi mezõket. Fontos: a megadott cél-adatbázisnak már léteznie kell. Amennyiben olyan adatbázist használ, ami ODBC illesztõt használ, (pl. MS Access) létre kell hoznia egy DSN-t, mielõtt továbblépne.";

$lang['Start_Install'] = "Telepítés Kezdése";
$lang['Finish_Install'] = "Telepítés Befejezése";

$lang['Default_lang'] = "Alapértelmezett nyelv";
$lang['DB_Host'] = "Adatbázis szerver neve / DSN";
$lang['DB_Name'] = "Adatbázis neve";
$lang['DB_Username'] = "Adatbázis Felhasználónév";
$lang['DB_Password'] = "Adatbázis Jelszó";
$lang['Database'] = "Az Adatbázis adatai";
$lang['Install_lang'] = "Válassza ki a telepítés nyelvét";
$lang['dbms'] = "Adatbázis típusa";
$lang['Table_Prefix'] = "Adattábla elõtag";
$lang['Admin_Username'] = "Adminisztrátor Felhasználónév";
$lang['Admin_Password'] = "Adminisztrátor Jelszó";
$lang['Admin_Password_confirm'] = "Adminisztrátor Jelszó [ Újra ]";

$lang['Inst_Step_2'] = "Az adminisztrátori azonosítója elkészült. Ezzel a telepítés elsõ lépése befejezõdött. A következõkben eljut az Adminisztrátori felületre, ahol megváltoztathatja a fórum összes beállítását. Ne felejtse el leellenõrizni az Általános Beállítások menüpont beállításait, és eszközölni a szükséges változtatásokat. Köszönjük, hogy a phpBB2 szoftvert választotta.";

$lang['Unwriteable_config'] = "A konfigurációs file jelenleg nem írható. A file másolatát letöltheti, ha a lenti gombra kattint. Végezze el benne a szükséges beállításokat, majd töltse fel abba a könyvtárba, ahova a phpBB-t telepítette. Amint ezzel elkészült, bejelentkezhet az elõbbiekben megadott adminisztrátori azonosítóval, és beléphet az Adminisztrátori felületre. (a linket keresse bejelentkezés után a lap alján) Köszönjük, hogy a phpBB2 szoftvert választotta.";
$lang['Download_config'] = "Beállítások Letöltése";

$lang['ftp_choose'] = "Válasszon konfigurálási módot";
$lang['ftp_option'] = "<br />Mivel a PHP verziója támogatja a beépített FTP-t, lehetõsége van a konfigurációs filet automatikusan a megfelelõ helyre feltölteni.";
$lang['ftp_instructs'] = "Az automatikus feltöltést választotta. Kérem adja meg a szükséges információkat az alábbiakban. Fontos: a teljes elérési utat meg kell adnia, mintha egy tetszõleges FTP klienssel próbálkozna.";
$lang['ftp_info'] = "Az FTP kapcsolat beállításai";
$lang['Attempt_ftp'] = "A konfigurációs file automatikus feltöltése";
$lang['Send_file'] = "Csak letöltés, a feltöltést majd kézzel csinálja";
$lang['ftp_path'] = "FTP elérési út";
$lang['ftp_username'] = "FTP felhasználónév";
$lang['ftp_password'] = "FTP jelszó";
$lang['Transfer_config'] = "Start";
$lang['NoFTP_config'] = "Az FTP átvitel nem sikerült. Kérem töltse le a konfigurációs filet, módosítsa, majd töltse fel a megfelelõ helyre kézzel.";

$lang['Install'] = "Installálás";
$lang['Upgrade'] = "Frissítés";


$lang['Install_Method'] = "Válasszon telepítési módot";

//
// That's all Folks!
// -------------------------------------------------

?>