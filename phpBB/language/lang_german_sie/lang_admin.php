<?php

/***************************************************************************
 *                            lang_admin.php [German]
 *                              -------------------
 *     begin                : Sun May 19 2002
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
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
 *
 * German Translation familiar (Du) version by:
 * Joel Ricardo Zick (Rici) webmaster@forcena-inn.de || http://www.sdc-forum.de
 * Modification formal (Sie) version by:
 * Christian Bachmann bachmann@easy-site.ch || http://www.easy-site.ch
 *
 ***************************************************************************/

//
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "Allgemeines";
$lang['Users'] = "Benutzer";
$lang['Groups'] = "Gruppen";
$lang['Forums'] = "Forum";
$lang['Styles'] = "Styles/Themes";

$lang['Configuration'] = "Konfiguration";
$lang['Permissions'] = "Befugnisse";
$lang['Manage'] = "Einstellungen";
$lang['Disallow'] = "Benutzernamen verbieten";
$lang['Prune'] = "Autom. Löschen";
$lang['Mass_Email'] = "Massen-Email versenden";
$lang['Ranks'] = "Ränge";
$lang['Smilies'] = "Smilies";
$lang['Ban_Management'] = "Bannen";
$lang['Word_Censor'] = "Wortzensur";
$lang['Export'] = "Exportieren";
$lang['Create_new'] = "Erstellen";
$lang['Add_new'] = "Hinzufügen";
$lang['Backup_DB'] = "Datenbank-Backup";
$lang['Restore_DB'] = "Datenbank wieder herstellen";


//
// Index
//
$lang['Admin'] = "Administration";
$lang['Not_admin'] = "Sie haben keine Administrator-Rechte";
$lang['Welcome_phpBB'] = "Willkommen bei phpBB";
$lang['Admin_intro'] = "Danke, dass Sie sich für phpBB entschieden haben. Auf diesem Bildschirm erhalten Sie einen Überblick über die Statistiken Ihres Forums. Wenn Sie auf diese Seite zurück kehren möchten, klicken Sie auf den <u>Admin Index</u>-Link im linken Bedienfeld. Um zu Ihrem Forum zurück zu kehren, klicken Sie auf das phpBB-Logo. Die anderen Links auf der linken Seite erlauben es Ihnen, alle wichtigen Bereiche Ihres Forums zu kontrollieren. In jedem Bereich wird beschrieben, wie er richtig genutzt wird.";
$lang['Main_index'] = "Forum Index";
$lang['Forum_stats'] = "Forum Statistiken";
$lang['Admin_Index'] = "Admin Index";
$lang['Preview_forum'] = "Forumsvorschau";

$lang['Click_return_admin_index'] = "Klicken Sie %shier%s, um zum Admin Index zurück zu kehren";

$lang['Statistic'] = "Statistik";
$lang['Value'] = "Wert";
$lang['Number_posts'] = "Anzahl der Beiträge";
$lang['Posts_per_day'] = "Beiträge pro Tag";
$lang['Number_topics'] = "Anzahl der Themen";
$lang['Topics_per_day'] = "Themen pro Tag";
$lang['Number_users'] = "Anzahl der Benutzer";
$lang['Users_per_day'] = "Benutzer pro Tag";
$lang['Board_started'] = "Board startete am";
$lang['Avatar_dir_size'] = "Größe des Avatarordners";
$lang['Database_size'] = "Datenbankgröße";
$lang['Gzip_compression'] ="GZip-Kompression";
$lang['Not_available'] = "Nicht verfügbar";

$lang['ON'] = "Aktiv"; // This is for GZip compression
$lang['OFF'] = "Inaktiv"; 


//
// DB Utils
//
$lang['Database_Utilities'] = "Datenbankfunktionen";

$lang['Restore'] = "Wieder herstellen";
$lang['Backup'] = "Backup";
$lang['Restore_explain'] = "Hiermit werden alle phpBB Tabellen aus einer Datei wieder hergestellt. Falls es Ihr Server unterstützt, können Sie auch einen GZip-komprimierten Text hochladen - er wird automatisch dekomprimiert! <b>ACHTUNG</b> Es werden alle existierenden Daten überschrieben. Der Vorgang wird einige Zeit dauern, bitte verlassen Sie diese Seite nicht, bis er abgeschlossen wurde.";
$lang['Backup_explain'] = "Hier können Sie alle phpBB-Tabellen abspeichern. Sollten Sie noch weitere, eigene Tabellen in der selben Datenbank wie die phpBB-Tabellen haben, die auch gespeichert werden sollen, geben Sie ihre Namen in der 'Zusätzliche Tabllen'-Textbox an (getrennt mit Kommata). Sollte Ihr Server es unterstützen, können Sie die Datei(en) auch mit GZip komprimieren, bevor Sie sie runterladen.";

$lang['Backup_options'] = "Backup Optionen";
$lang['Start_backup'] = "Backup beginnen";
$lang['Full_backup'] = "Vollständiges Backup";
$lang['Structure_backup'] = "Nur-Struktur-Backup";
$lang['Data_backup'] = "Nur-Daten-Backup";
$lang['Additional_tables'] = "Zusätzliche Tabellen";
$lang['Gzip_compress'] = "GZip-Komprimierungs Datei";
$lang['Select_file'] = "Wählen Sie eine Datei";
$lang['Start_Restore'] = "Wiederherstellung beginnen";

$lang['Restore_success'] = "Die Datenbank wurde wieder hergestellt.<br /><br />Ihr Board sollte sich jetzt wieder auf dem Zeitpunkt des Backups befinden.";
$lang['Backup_download'] = "Ihr Download wird in Kürze beginnen - bitte etwas Geduld";
$lang['Backups_not_supported'] = "Fehler: Ihr Datenbanksystem unterstützt Datenbank-Backups nicht!";

$lang['Restore_Error_uploading'] = "Fehler beim Hochladen der Backup-Datei";
$lang['Restore_Error_filename'] = "Probleme mit dem Dateinamen, probieren Sie einen anderen";
$lang['Restore_Error_decompress'] = "Die GZip-Version kann nicht dekomprimiert werden, nutzen Sie bitte eine Nur-Text-Datei";
$lang['Restore_Error_no_file'] = "Es wurde keine Datei hochgeladen";


//
// Auth pages
//
$lang['Select_a_User'] = "Wählen Sie einen Benutzer";
$lang['Select_a_Group'] = "Wählen Sie eine Gruppe";
$lang['Select_a_Forum'] = "Wählen Sie ein Forum";
$lang['Auth_Control_User'] = "Benutzerbefugniskontrolle"; 
$lang['Auth_Control_Group'] = "Gruppenbefugniskontrolle"; 
$lang['Auth_Control_Forum'] = "Forenzugangskontrolle"; 
$lang['Look_up_User'] = "Benutzer auswählen"; 
$lang['Look_up_Group'] = "Gruppe auswählen"; 
$lang['Look_up_Forum'] = "Forum auswählen"; 

$lang['Group_auth_explain'] = "Sie können hier die Befugnisse und den Moderator Status für jede Gruppe einstellen. Vergessen Sie nicht, dass einzelne Benutzerbefugnisse immer noch gültig sind, wenn Sie die Gruppenbefugnisse ändern (z.B. Zugang zu Foren u.ä.). Sollte dies der Fall sein, werden Sie informiert.";
$lang['User_auth_explain'] = "Sie können hier die Befugnisse und den Moderator Status für jeden einzelnen Benutzer einstellen. Vergessen Sie nicht, dass Gruppenbefugnisse immer noch gültig sind, wenn Sie die Benutzerbefugnisse ändern (z.B. Zugang zu Foren u.ä.). Sollte dies der Fall sein, werden Sie informiert.";
$lang['Forum_auth_explain'] = "Sie können hier die Zugangsebenen für jedes Forum bestimmen. Es gibt eine einfache und eine fortgeschrittene Methode, dies zu tun. Bei der fortgeschrittenen Möglichkeit haben Sie eine bessere Kontrolle über jedes Forum. Bedenken Sie, dass das Ändern der Zugangsebene beeinflusst, welche Benutzer welche Aktionen im Forum durchführen können.";

$lang['Simple_mode'] = "Einfache Methode";
$lang['Advanced_mode'] = "Fortgeschrittene Methode";
$lang['Moderator_status'] = "Moderatorenstatus";

$lang['Allowed_Access'] = "Zugang gestattet";
$lang['Disallowed_Access'] = "Zugang verwehrt";
$lang['Is_Moderator'] = "ist hier Moderator";
$lang['Not_Moderator'] = "ist hier kein Moderator";

$lang['Conflict_warning'] = "Warnung: Authorisationskonflikt";
$lang['Conflict_access_userauth'] = "Der Benutzer hat auf Grund seiner Gruppenmitgliedschaft immer noch Rechte in diesem Forum. Vielleicht sollten Sie die Gruppenrechte ändern oder den Benutzer komplett aus der Benutzergruppe entfernen. Die Gruppen mit Rechten (und die dazugehörigen Foren) stehen unten.";
$lang['Conflict_mod_userauth'] = "Der Benutzer hat immer noch Moderatorenrechte in diesem Forum. Vielleicht sollten Sie die Gruppenrechte ändern oder den Benutzer komplett aus der Benutzergruppe entfernen. Die Gruppen mit Rechten (und die dazugehörigen Foren) stehen unten.";

$lang['Conflict_access_groupauth'] = "Der oder die folgenden Benutzer haben immer noch das Zugangsrecht zu diesem Forum, auf Grund ihrer Benutzereinstellungen. Vielleicht sollten Sie diese Einstellungen ändern, um dem Benutzer komplett den Zugang zu verweigern. Die Benutzer mit Rechten (und die dazugehörigen Foren) stehen unten.";
$lang['Conflict_mod_groupauth'] = "Der oder die folgenden Benutzer haben immer Moderatorenrechte in diesem Forum, auf Grund ihrer Benutzereinstellungen. Vielleicht sollten Sie diese Einstellungen ändern, um dem Benutzer komplett die Rechte zu nehmen. Die Benutzer mit Rechten (und die dazugehörigen Foren) stehen unten.";

$lang['Public'] = "Öffentlich";
$lang['Private'] = "Privat";
$lang['Registered'] = "Registriert";
$lang['Administrators'] = "Administratoren";
$lang['Hidden'] = "Versteckt";

$lang['Forum_ALL'] = "Alle";
$lang['Forum_REG'] = "Reg";
$lang['Forum_PRIVATE'] = "Privat";
$lang['Forum_MOD'] = "Mods";
$lang['Forum_ADMIN'] = "Admin";

$lang['View'] = "Ansicht";
$lang['Read'] = "Lesen";
$lang['Post'] = "Posten";
$lang['Reply'] = "Antworten";
$lang['Edit'] = "Editieren";
$lang['Delete'] = "Löschen";
$lang['Sticky'] = "Wichtig";
$lang['Announce'] = "Ankündigung"; 
$lang['Vote'] = "Umfrage";
$lang['Pollcreate'] = "Umfrage erstellen";

$lang['Permissions'] = "Befugnisse";
$lang['Simple_Permission'] = "Einfache Befugnis";

$lang['User_Level'] = "Benutzerebene"; 
$lang['Auth_User'] = "Benutzer";
$lang['Auth_Admin'] = "Administrator";
$lang['Group_memberships'] = "Benutzergruppenmitglieder";
$lang['Usergroup_members'] = "Diese Gruppe hat die folgenden Mitglieder";

$lang['Forum_auth_updated'] = "Forumsberechtigungen aktualisert";
$lang['User_auth_updated'] = "Benutzerberechtigungen aktualisiert";
$lang['Group_auth_updated'] = "Gruppenberechtigungen aktualisiert";

$lang['Auth_updated'] = "Befugnisse wurden aktualisiert";
$lang['Click_return_userauth'] = "Klicken Sie %shier%s, um zu den Benutzerrechten zurück zu kehren";
$lang['Click_return_groupauth'] = "Klicken Sie %shier%s, um zu den Gruppenrechten zurück zu kehren";
$lang['Click_return_forumauth'] = "Klicken Sie %shier%s, um zu den Forenberechtigungen zurück zu kehren";


//
// Banning
//
$lang['Ban_control'] = "Sperren";
$lang['Ban_explain'] = "Hier können Sie Benutzer bannen. Sie können entweder einen bestimmten User, eine IP-Adresse oder Hostnamen sperren. Durch diese Methode kann der Benutzer die Startseite des Forums nicht aufrufen. Um den Benutzer daran zu hindern, sich unter einem anderen Namen anzumelden, können Sie auch bestimmte E-Mail-Adressen sperren. Eine E-Mail-Sperre verhindert nur das Registrieren, nicht das Posten eines Benutzers!";
$lang['Ban_explain_warn'] = "Bitte beachte, dass, wenn Sie mehrere IP Adressen eingeben, alle Adressen zwischen dem Anfang und dem Ende der Sperrliste hinzugefügt werden. Versuchen Sie die Anzahl der Adressen klein zu halten, indem Sie Wildcards einsetzen, wo es möglich ist. Am besten wäre es, eine konkrete IP-Adresse anzugeben.";

$lang['Select_username'] = "Wählen Sie einen Benutzernamen";
$lang['Select_ip'] = "Wählen Sie eine IP";
$lang['Select_email'] = "Wählen Sie eine E-Mail Adresse";

$lang['Ban_username'] = "Einen oder mehrere Benutzer bannen";

$lang['Ban_IP'] = "Eine(n) oder mehrere IPs/Hostnamen bannen";
$lang['IP_hostname'] = "IP Adressen oder Hostname";
$lang['Ban_IP_explain'] = "Um mehrere verschiedene IPs oder Hostnamen anzugeben, trennen Sie sie mit Kommata von einander. Um eine Spanne von IP Adressen anzugeben, trennen Sie den Anfang und das Ende mit einem Bindestrich (-), benutzen Sie * für eine Wildcard";

$lang['Ban_email'] = "Eine oder mehrere E-Mail Adressen bannen";
$lang['Ban_email_explain'] = "Um mehrere verschiedene E-Mail Adressen anzugeben, trennen Sie sie mit Kommata von einander. Für einen allgmeinen Benutzernamen, benutzen Sie ein * (z.B. *@hotmail.de)";

$lang['Unban_username'] = "Einen oder mehrere Benutzer entsperren";
$lang['Unban_username_explain'] = "Mit einer Kombination aus Tastatur und Maus können Sie auch mehrere Benutzer auf einmal entsperren";

$lang['Unban_IP'] = "Eine oder mehrere IP-Adressen entsperren";
$lang['Unban_IP_explain'] = "Mit einer Kombination aus Tastatur und Maus können Sie auch mehrere IP-Adressen auf einmal entsperren";

$lang['Unban_email'] = "Eine oder mehrere E-Mail Adressen entsperren";
$lang['Unban_email_explain'] = "Mit einer Kombination aus Tastatur und Maus können Sie auch mehrere E-Mail Adressen auf einmal entsperren";

$lang['No_banned_users'] = "Keine gesperrten Benutzernamen";
$lang['No_banned_ip'] = "Keine gebannten IP-Adressen";
$lang['No_banned_email'] = "Keine gebannten E-Mail Adressen";

$lang['Ban_update_sucessful'] = "Die Banliste wurde aktualisiert";
$lang['Click_return_banadmin'] = "Klicken Sie %shier%s, um zur Sperr-Kontrolle zurück zu kehren";


//
// Configuration
//
$lang['General_Config'] = "Allgemeine Konfiguration";
$lang['Config_explain'] = "Hier können Sie die allgemeinen Einstellungen Ihres Forums ändern. Für Benutzer- und Foreneinstellungen nutzen Sie bitte die Links auf der linken Seite.";

$lang['Click_return_config'] = "Klicken Sie %shier%s, um zur allgemeinen Konfiguration zurück zu kehren";

$lang['General_settings'] = "Allgemeine Boardeinstellungen";
$lang['Server_name'] = "Domainname";
$lang['Server_name_explain'] = "Der Name der Domain, auf der das Board läuft";
$lang['Script_path'] = "Scriptpfad";
$lang['Script_path_explain'] = "Der Pfad zum phpBB2, relativ zum Domainnamen";
$lang['Server_port'] = "Server Port";
$lang['Server_port_explain'] = "Der Port, unter dem Ihr Server läuft, normalerweise 80, ändern Sie das nur, falls es wirklich anders ist";
$lang['Site_name'] = "Name der Seite";
$lang['Site_desc'] = "Beschreibung der Seite";
$lang['Board_disable'] = "Board deaktivieren";
$lang['Board_disable_explain'] = "Hiermit sperren Sie das Forum für alle Benutzer. <b>Loggen Sie sich nach dem Deaktivieren nicht aus oder Sie können das Forum nicht reaktivieren!</b>";
$lang['Acct_activation'] = "Account-Freischaltung aktivieren";
$lang['Acc_None'] = "Keine"; // These three entries are the type of activation
$lang['Acc_User'] = "Per E-Mail";
$lang['Acc_Admin'] = "Durch den Admin";

$lang['Abilities_settings'] = "Standard Benutzer- und Foreneinstellungen";
$lang['Max_poll_options'] = "Maximale Anzahl der Umfrageoptionen";
$lang['Flood_Interval'] = "Flood-Intervall";
$lang['Flood_Interval_explain'] = "Anzahl der Sekunden, die ein Benutzer warten muss, bevor er einen neuen Beitrag schreiben kann"; 
$lang['Board_email_form'] = "Benutzer E-Mails per Board";
$lang['Board_email_form_explain'] = "Ihre Benutzer können sich über das Board E-Mails schreiben";
$lang['Topics_per_page'] = "Themen pro Seite";
$lang['Posts_per_page'] = "Beiträge pro Seite";
$lang['Hot_threshold'] = "Beiträge, die ein Thema braucht, um ein 'Hot Topic' zu werden";
$lang['Default_style'] = "Standard-Style";
$lang['Override_style'] = "Style überschreiben";
$lang['Override_style_explain'] = "Vom Benutzer gewähltes Style überschreiben";
$lang['Default_language'] = "Standard-Sprache";
$lang['Date_format'] = "Datumsformat";
$lang['System_timezone'] = "Zeitzone";
$lang['Enable_gzip'] = "GZip Komprimierung aktivieren";
$lang['Enable_prune'] = "Forumspruning aktivieren";
$lang['Allow_HTML'] = "HTML erlauben";
$lang['Allow_BBCode'] = "BBCode erlauben";
$lang['Allowed_tags'] = "Erlaubte HTML-Tags";
$lang['Allowed_tags_explain'] = "Trennen Sie die Tags mit Kommata";
$lang['Allow_smilies'] = "Smilies erlauben";
$lang['Smilies_path'] = "Speicherort für Smilies";
$lang['Smilies_path_explain'] = "Der Pfad in Ihrem phpBB-Verzeichnis, in dem die Smilies liegen (z.B. images/smilies)";
$lang['Allow_sig'] = "Signaturen erlauben";
$lang['Max_sig_length'] = "Maximale Signaturlänge";
$lang['Max_sig_length_explain'] = "Die maximale Anzahl an Zeichen, die ein Benutzer in seiner Signatur nutzen darf";
$lang['Allow_name_change'] = "Namenswechsel erlauben";
$lang['Allow_displayname_change'] = "Änderung des gezeigten Namens erlauben";
$lang['page_creationtime_status'] = "Erstellungszeit dieser Seite";

$lang['Avatar_settings'] = "Avatareinstellungen";
$lang['Allow_local'] = "Galerieavatare erlauben";
$lang['Allow_remote'] = "Avatarremote erlauben";
$lang['Allow_remote_explain'] = "Avatare, die von einer anderen Site verlinkt wurden";
$lang['Allow_upload'] = "Hochladen von Avataren erlauben";
$lang['Max_filesize'] = "Maximale Größe";
$lang['Max_filesize_explain'] = "Für hochgeladene Avatare";
$lang['Max_avatar_size'] = "Maximale Abmessungen des Avatars";
$lang['Max_avatar_size_explain'] = "(Höhe x Breite in Pixel)";
$lang['Avatar_storage_path'] = "Avatar Speicherpfad";
$lang['Avatar_storage_path_explain'] = "Der Pfad in Ihrem phpBB-Verzeichnis, in dem die Avatare liegen (z.B. images/avatars)";
$lang['Avatar_gallery_path'] = "Avatar Galeriepfad";
$lang['Avatar_gallery_path_explain'] = "Der Pfad in Ihrem phpBB-Verzeichnis, in dem die Galerie-Avatare liegen (z.B. images/avatars/gallery)";

$lang['COPPA_settings'] = "COPPA Einstellungen";
$lang['COPPA_fax'] = "COPPA Fax Nummer";
$lang['COPPA_mail'] = "COPPA E-Mail Adresse";
$lang['COPPA_mail_explain'] = "Zu dieser E-Mail Adresse schicken die Eltern die COPPA Einverständniserklärung";

$lang['Email_settings'] = "E-Mail Einstellungen";
$lang['Admin_email'] = "E-Mail Adresse des Administrators";
$lang['Email_sig'] = "E-Mail Signatur";
$lang['Email_sig_explain'] = "Diese Signatur wird an alle E-Mails des Administrators angehängt";
$lang['Use_SMTP'] = "Nutzen Sie einen SMTP Server zum Mailen";
$lang['Use_SMTP_explain'] = "Wählen Sie 'Ja', wenn Sie möchten, dass Ihre E-Mails durch einen Server gesendet werden";
$lang['SMTP_server'] = "SMTP Server Addresse";
$lang['SMTP_username'] = "SMTP Benutzername";
$lang['SMTP_username_explain'] = "Geben Sie nur dann einen Benutzernamen an, wenn der SMTP Server dies benötigt";
$lang['SMTP_password'] = "SMTP Passwort";
$lang['SMTP_password_explain'] = "Geben Sie nur dann ein Passwort an, wenn der SMTP Server dies benötigt";


$lang['Disable_privmsg'] = "Private Nachrichten";
$lang['Inbox_limits'] = "Maximale Nachrichten im Eingang";
$lang['Sentbox_limits'] = "Maximale Nachrichten im Ausgang";
$lang['Savebox_limits'] = "Maximale Anzahl gespeicherter Nachrichten";

$lang['Cookie_settings'] = "Cookie Einstellungen"; 
$lang['Cookie_settings_explain'] = "Hier können Sie einstellen, was für Cookies zum Browser gesendet werden. Meistens stimmen die Standardeinstellungen. Sollten Sie sie ändern müssen, tun Sie es mit Bedacht, ansonsten kann sich niemand mehr im Forum einloggen.";
$lang['Cookie_domain'] = "Cookie Domain";
$lang['Cookie_name'] = "Cookie Name";
$lang['Cookie_path'] = "Cookie Pfad";
$lang['Cookie_secure'] = "Cookie Secure";
$lang['Cookie_secure_explain'] = "Falls Ihr Server auf SSL läuft, aktivieren Sie diese Funktion, ansonsten lassen Sie sie deaktiviert";
$lang['Session_length'] = "Sessionlänge [ Sekunden ]";

//
// Forum Management
//
$lang['Forum_admin'] = "Forum Administration";
$lang['Forum_admin_explain'] = "Hier können Sie Kategorien und Foren hinzufügen, löschen, bearbeiten und neu anordnen.";
$lang['Edit_forum'] = "Forum bearbeiten";
$lang['Create_forum'] = "Neues Forum erstellen";
$lang['Create_category'] = "Neue Kategorie erstellen";
$lang['Remove'] = "Entfernen";
$lang['Action'] = "Aktion";
$lang['Update_order'] = "Reihenfolge ändern";
$lang['Config_updated'] = "Forumskonfiguration geändert";
$lang['Edit'] = "Bearbeiten";
$lang['Delete'] = "Löschen";
$lang['Move_up'] = "Nach oben";
$lang['Move_down'] = "Nach unten";
$lang['Resync'] = "Resync";
$lang['No_mode'] = "Kein Modus ausgewählt";
$lang['Forum_edit_delete_explain'] = "Hier können Sie alle allgemeinen Boardeinstellungen anpassen. Zur Benutzer- und Forenkonfiguration benutzen Sie bitte die entsprechenden Links auf der linken Seite";

$lang['Move_contents'] = "Alle Inhalte verschieben";
$lang['Forum_delete'] = "Forum löschen";
$lang['Forum_delete_explain'] = "Hier können Sie ein Forum oder eine Kategorie löschen und entscheiden, wohin die enthaltenen Themen oder Foren verschoben werden sollen.";

$lang['Forum_settings'] = "Allgemeine Foreneinstellungen";
$lang['Forum_name'] = "Forumsname";
$lang['Forum_desc'] = "Beschreibung";
$lang['Forum_status'] = "Forumsstatus";
$lang['Forum_pruning'] = "Automatisches Pruning";

$lang['prune_freq'] = 'Überprüfen Sie das Themenalter alle';
$lang['prune_days'] = "Entfernen Sie Themen, in denen nichts mehr geschrieben wurde, seit";
$lang['Set_prune_data'] = "Sie haben das Automatische Pruning für dieses Forum aktiviert, aber kein Intervall noch eine Anzahl an Tagen angegeben.";

$lang['Move_and_Delete'] = "Verschieben und Löschen";

$lang['Delete_all_posts'] = "Alle Beiträge löschen";
$lang['Nowhere_to_move'] = "Kein Ziel zum Verschieben";

$lang['Edit_Category'] = "Kategorie editieren";
$lang['Edit_Category_explain'] = "Hier können Sie den Kategoriennamen bestimmen";

$lang['Forums_updated'] = "Forums- und Kategorieninformationen wurden geändert";
$lang['Must_delete_forums'] = "Sie müssen erst alle Foren löschen, bevor Sie diese Kategorie löschen können";

$lang['Click_return_forumadmin'] = "Klicken Sie %shier%s, um zur Forumsadministration zurück zu kehren";


//
// Smiley Management
//
$lang['smiley_title'] = "Smiley-Bearbeitung";
$lang['smile_desc'] = "Hier können Sie die Smilies, die die Benutzer in ihren Beiträgen und Privaten Nachrichten einfügen können, hinzufügen, löschen oder bearbeiten.";

$lang['smiley_config'] = "Smiley Konfiguration";
$lang['smiley_code'] = "Smiley Code";
$lang['smiley_url'] = "Smiley Bilddatei";
$lang['smiley_emot'] = "Smiley Beschreibung";
$lang['smile_add'] = "Einen neuen Smiley hinzufügen";
$lang['Smile'] = "Smiley";
$lang['Emotion'] = "Beschreibung";

$lang['Select_pak'] = "Wählen Sie Paketdatei (.pak)";
$lang['replace_existing'] = "Aktuelle Smilies überschreiben";
$lang['keep_existing'] = "Aktuelle Smilies behalten";
$lang['smiley_import_inst'] = "Sie sollten das Smiley-Paket entzippen und alle Dateien ins jeweilige Smilies-Verzeichnis hochladen. Wählen Sie dann die korrekten Angaben, um das Paket zu installieren.";
$lang['smiley_import'] = "Smiley-Paketimport";
$lang['choose_smile_pak'] = "Wählen Sie ein Smiley-Paket (.pak)";
$lang['import'] = "Smilies importieren";
$lang['smile_conflicts'] = "Was tun, wenn Konflikte auftreten?";
$lang['del_existing_smileys'] = "Aktuelle Smilies vor dem Import löschen";
$lang['import_smile_pack'] = "Smiley-Paket importieren";
$lang['export_smile_pack'] = "Smiley-Paket erstellen";
$lang['export_smiles'] = "Um aus Ihren jetztigen Smilies ein Paket zu erstellen, klicken Sie %shier%s, um das Paket gezippt herunterzuladen. Achten Sie darauf, die .pak-Erweiterung am Ende beizubehalten. Dann erstellen Sie eine Zip-Datei mit allen benutzten Smilies und der .pak-Datei.";

$lang['smiley_add_success'] = "Der Smiley wurde hinzugefügt";
$lang['smiley_edit_success'] = "Der Smiley wurde geändert";
$lang['smiley_import_success'] = "Das Smiley-Paket wurde installiert";
$lang['smiley_del_success'] = "Der Smiley wurde gelöscht";
$lang['Click_return_smileadmin'] = "Klicken Sie %shier%s, um zur Smiley-Verwaltung zurück zu kehren";


//
// User Management
//
$lang['User_admin'] = "Benutzer-Administration";
$lang['User_admin_explain'] = "Hier können Sie die Daten und spezielle Optionen eines Nutzers ändern. Um die Befugnisse eines Benutzers zu ändern, benutzen Sie bitte die Benutzer- und Gruppenkontrolle.";

$lang['Look_up_user'] = "Benutzer auswählen";

$lang['Admin_user_fail'] = "Benutzerprofil konnte nicht geändert werden";
$lang['Admin_user_updated'] = "Benutzerprofil geändert";
$lang['Click_return_useradmin'] = "Klicken Sie %shier%s, um zur Benutzeradministration zurück zu kehren";

$lang['User_delete'] = "Diesen Benutzer löschen";
$lang['User_delete_explain'] = "Klicken Sie hier, um den Benutzer zu löschen - diese Aktion kann nicht rückgängig gemacht werden.";
$lang['User_deleted'] = "Benutzer wurde gelöscht";

$lang['User_status'] = "Benutzer ist atkiv";
$lang['User_allowpm'] = "Darf Private Nachrichten verschicken";
$lang['User_allowavatar'] = "Darf einen Avatar benutzen";

$lang['Admin_avatar_explain'] = "Hier können Sie den Avatar des Benutzers ansehen und löschen";

$lang['User_special'] = "Spezielle Optionen (nur für Administratoren)";
$lang['User_special_explain'] = "Diese Optionen könnten nicht von den Benutzern geändert werden. Sie können hier ihren Status und bestimmte Optionen festlegen.";


//
// Group Management
//
$lang['Group_administration'] = "Gruppenadministration";
$lang['Group_admin_explain'] = "Hier können Sie die Benutzergruppen Ihres Forum überwachen. Sie können bestehende Gruppen löschen oder editieren oder neue anlegen. Ebenso können Sie Gruppenleiter wählen, den Gruppenstatus auf offen/geschlossen ändern und den Gruppennamen bzw. die Gruppenbeschreibung ändern";
$lang['Error_updating_groups'] = "Fehler beim Aktualisieren der Gruppe";
$lang['Updated_group'] = "Die Gruppe wurde abgeändert";
$lang['Added_new_group'] = "Die Gruppe wurde hinzugefügt";
$lang['Deleted_group'] = "Die Gruppe wurde gelöscht";
$lang['New_group'] = "Neue Gruppe erstellen";
$lang['Edit_group'] = "Gruppe bearbeiten";
$lang['group_name'] = "Gruppenname";
$lang['group_description'] = "Gruppenbeschreibung";
$lang['group_moderator'] = "Gruppenleiter";
$lang['group_status'] = "Gruppenstatus";
$lang['group_open'] = "Offene Gruppe";
$lang['group_closed'] = "Geschlossene Gruppe";
$lang['group_hidden'] = "Versteckte Gruppe";
$lang['group_delete'] = "Gelöschte Gruppe";
$lang['group_delete_check'] = "Diese Gruppe löschen";
$lang['submit_group_changes'] = "Änderungen übernehmen";
$lang['reset_group_changes'] = "Reset";
$lang['No_group_name'] = "Bitte geben Sie einen Gruppennamen an";
$lang['No_group_moderator'] = "Bitte geben Sie einen Gruppenleiter an";
$lang['No_group_mode'] = "Sie müssen einen Status für diese Gruppe angeben (offen/geschlossen)";
$lang['delete_group_moderator'] = "Alten Gruppenleiter entfernen?";
$lang['delete_moderator_explain'] = "Wenn Sie den Gruppenleiter wechseln möchten, wählen Sie die entsprechende Option, um den alten Leiter zu entfernen. Ansonsten wählen Sie die Option nicht und der Benutzer wird ein reguläres Mitglied der Gruppe.";
$lang['Click_return_groupsadmin'] = "Klicken Sie %shier%s, um zur Gruppenadministration zurück zu kehren.";
$lang['return_group_admin'] = "Klicken Sie %shier%s, um zur Gruppenadministration zurück zu kehren.";
$lang['Select_group'] = "Gruppe wählen";
$lang['Look_up_group'] = "Gruppe finden";

$lang['No_group_action'] = 'Sie haben keine Aktion ausgwählt';

//
// Prune Administration
//
$lang['Forum_Prune'] = "Forum Prune";
$lang['Forum_Prune_explain'] = "Sie können angeben, dass alle Themen, in denen seit einer gewissen Zeit nichts gepostet wurde, gelöscht werden. Sollten Sie keine Zahl angeben, werden alle Themen gelöscht. Laufende Umfragen und Ankündigungen sind davon nicht betroffen. Diese Themen müssen manuell entfernt werden.";
$lang['Do_Prune'] = "Prune einetzen";
$lang['All_Forums'] = "Alle Foren";
$lang['Prune_topics_not_posted'] = "Prune Themen, in denen es keine Antworten gab";
$lang['Topics_pruned'] = "Prune-Themen";
$lang['Posts_pruned'] = "Prune-Beiträge";
$lang['Prune_success'] = "Das Prunen des Forums wurde aktiviert";


//
// Word censor
//
$lang['Words_title'] = "Wortzensur";
$lang['Words_explain'] = "Hier können Sie Wörter bestimmen, die automatisch aus den Beiträgen zensiert werden. Außerdem kann kein Benutzer einen Namen wählen, in dem diese Wörter vorkommen. Sie können * einsetzen, um bestimmte Formulierungen zu entfernen. <i>Beispiel: Fisch* entfernt Wörter wie Fischbesteck, Fischfang usw., *Fisch entfernt Backfisch, Stockfisch usw.</i>";
$lang['Word'] = "Wort";
$lang['Edit_word_censor'] = "Wordzensur ändern";
$lang['Replacement'] = "Ersatz";
$lang['Add_new_word'] = "Neues Wort hinzufügen";
$lang['Update_word'] = "Zensur Aktualisieren";

$lang['Must_enter_word'] = "Ein Wort und ein entsprechender Einsatz sind nötig";
$lang['No_word_selected'] = "Kein Wort zum Editieren ausgewählt";

$lang['Word_updated'] = "Die Wortzensur wurde aktualisiert";
$lang['Word_added'] = "Die Wortzensur wurde eingerichtet";
$lang['Word_removed'] = "Die Wortzensur wurde entfernt";

$lang['Click_return_wordadmin'] = "Klicken Sie %shier%s, um zur Wortzensur-Administration zurück zu kehren";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Hier können Sie entweder allen registrierten Benutzern oder einer bestimmten Gruppe eine Nachricht schicken. Diese Nachricht wird an das Postfach des Administrators geschickt und von dort anonym weiter geleitet. Sollten Sie einer großen Gruppe eine Mail schicken, haben Sie etwas Geduld und brechen Sie den Vorgang nicht ab. Es ist völlig normal, dass der Vorgang länger dauert und Sie erhalten auf jeden Fall eine Rückmeldung!";
$lang['Compose'] = "Erstellen"; 

$lang['Recipients'] = "Empfänger";
$lang['All_users'] = "Alle Benutzer";

$lang['Email_successfull'] = "Die Nachricht wurde gesendet";
$lang['Click_return_massemail'] = "Klicken Sie %shier%s, um zur Massen E-Mail zurück zu kehren";


//
// Install Process
//

$lang['Welcome_install'] = "Willommen bei der phpBB2-Installation";
$lang['Initial_config'] = "Grundeinstellungen";
$lang['DB_config'] = "Datenbankkonfiguration";
$lang['Admin_config'] = "Administratorkonfiguration";

$lang['Installer_Error'] = "Während der Installation trat ein Fehler auf";
$lang['Previous_Install'] = "Eine vorherige Installation wurde entdeckt";
$lang['Install_db_error'] = "Beim Update der Datenbank trat ein Fehler auf";

$lang['Re_install'] = "Ihre vorherige Installation ist noch aktiv. <br /><br />Falls Sie phpBB2 reinstallieren möchten, aktivieren Sie den unten stehenden Ja-Knopf. Beachten Sie jedoch, dass dieser Vorgang sämtliche existierenden Daten zerstören wird und keine Sicherungen gemacht werden. Der Administrator-Benutzername und das Passwort, die Sie benutzt haben, um sich im Board einzuloggen, werden nach der Neuinstallation erneut erstellt. Es bleiben sonst keine Einstellungen zurück.<br /><br />Überlegen Sie es sich gut, bevor Sie auf Ja klicken";

$lang['Inst_Step_0'] = "Danke, dass Sie sich für phpBB2 entschieden haben. Um die Installation abzuschließen, geben Sie bitte die unten geforderten Daten ein. Beachten Sie, dass die Datenbank, in welche Sie installieren, bereits vorhanden sein sollte. Sollten Sie in eine ODBC nutzende Datenbank installieren, z.B. MS Access, sollten Sie erst ein DSN für das Board erstellen, bevor Sie fort fahren.";

$lang['Start_Install'] = "Installation beginnen";
$lang['Finish_Install'] = "Installation abschließen";

$lang['Default_lang'] = "Standardsprache";
$lang['DB_Host'] = "Datenbank: Host / DSN";
$lang['DB_Name'] = "Name der Datenbank";
$lang['Database'] = "Datenbank";
$lang['Install_lang'] = "Wählen Sie Sprache für die Installation";
$lang['dbms'] = "Datenbanktyp";
$lang['Table_Prefix'] = "Prefix für die Tabellen in der Datenbank";
$lang['Admin_Username'] = "Administrator Benutzername";
$lang['Admin_Password'] = "Administrator Passwort";
$lang['Admin_Password_confirm'] = "Administrator Passwort [ Bestätigung ]";

$lang['Inst_Step_2'] = "Ihr Administrator Benutzername wurde erstellt. Ihre Installation ist nun komplett. Sie werden jetzt auf eine Seite geführt, wo Sie Ihr neues Board Ihren Bedürfnissen anpassen können. Überprüfen Sie am besten gleich die Allgemeine Konfiguration und machen Sie eventuell nötige Änderungen. Danke, dass Sie sich für phpBB2 entschieden haben.";

$lang['Unwriteable_config'] = "Momentan ist Ihre config-Datei nicht beschreibbar. Sie können sich eine Kopie der Datei runterladen, wenn Sie auf den entsprechenden Link unten klicken. Sie sollten diese Datei ins selbe Verzeichnis wie phpBB2 hochladen. Wenn dies getan ist, sollten Sie sich mit Ihrem Administrator-Benutzernamen und Passwort, die Sie auf der letzten Seite angegeben haben, einloggen und den Administrationsbereich betreten, um die Allgemeinen Einstellungen zu prüfen. Ein entsprechender Link ist am Ende jeder Seite Ihres Forums. Danke, dass Sie sich für phpBB2 entschiden haben.";
$lang['Download_config'] = "Config herunterladen";

$lang['ftp_choose'] = "Wählen Sie Downloadmethode";
$lang['ftp_option'] = "<br />Da FTP Erweiterungen in dieser Version von php aktiviert sind, könnten Sie die Möglichkeit haben, die config Datei automatisch per FTP vor Ort zu ändern.";
$lang['ftp_instructs'] = "Sie haben sich dazu entschieden, die Datei automatisch und vor Ort zu ändern. Bitte geben Sie die unten geforderten Informationen an, um den Prozess zu starten. Beachten Sie, dass der FTP-Pfad der exakte Pfad zu Ihrem phpBB2-Ordner sein muss..";
$lang['ftp_info'] = "Eingabe der FTP Informationen";
$lang['Attempt_ftp'] = "Die config Datei vor Ort umschreiben";
$lang['Send_file'] = "Ich möchte, dass mir die Datei geschickt wird. Ich werde sie manuell hochladen.";
$lang['ftp_path'] = "FTP Pfad zum phpBB2";
$lang['ftp_username'] = "Ihr FTP Benutzername";
$lang['ftp_password'] = "Ihr FTP Passwort";
$lang['Transfer_config'] = "Transfer beginnen";

$lang['Install'] = "Installation";
$lang['Upgrade'] = "Upgrade";
$lang['Install_Method'] = "Wählen Sie eine Methode aus";

$lang['Install_No_Ext'] = "Die php-Konfiguration auf Ihrem Server unterstützt nicht den gewählten Datenbank-Typ";
$lang['Install_No_PCRE'] = "phpBB2 benötigt das Perl-Compatible Regular Expressions Module für php, was von Ihrer php-Konfiguration nicht unterstützt zu werden scheint";


//
// Ranks admin
//
$lang['Ranks_title'] = "Rank Administration";
$lang['Ranks_explain'] = "Hier können Sie Ränge hinzufügen, editieren, anschauen und löschen. Sie können ebenfalls eigene Ränge erstellen, die Sie per Benutzeradministration an spezielle Benutzer vergeben können.";

$lang['Add_new_rank'] = "Neuen Rang anlegen";

$lang['Rank_title'] = "Rankname";
$lang['Rank_special'] = "Spezialrang";
$lang['Rank_minimum'] = "Minimum-Beiträge";
$lang['Rank_maximum'] = "Maximum-Beiträge";
$lang['Rank_image'] = "Bild zum Rang";
$lang['Rank_image_explain'] = "Sie können hier ein Bild bestimmen, dass dem jeweiligen Rang zugeordnet ist";

$lang['Must_select_rank'] = "Wählen Sie einen Rang aus";
$lang['No_assigned_rank'] = "Kein Spezialrang vergeben";

$lang['Rank_updated'] = "Die Ranginformationen wurden aktualisiert";
$lang['Rank_added'] = "Der Rang wurde hinzugefügt";
$lang['Rank_removed'] = "Der Rang wurde gelöscht";

$lang['Click_return_rankadmin'] = "Klicken Sie %shier%s, um zur Rank Administration zurück zu kehren";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Verbot von Benutzernamen";
$lang['Disallow_explain'] = "Hier können Sie Benutzernamen überwachen, die nicht genutzt werden dürfen. Sie können eine Wildcard setzen, ein * . Beachten Sie, dass Sie, wenn Sie einen bereits vergebenen Benutzernamen wählen, müssen Sie den jeweiligen Benutzer zuerst löschen";

$lang['Delete_disallow'] = "Löschen";
$lang['Delete_disallow_title'] = "Einen verbotenen Namen entfernen";
$lang['Delete_disallow_explain'] = "Sie können einen verbotenen Namen entfernen, indem Sie de Namen aus der Liste auswählen und Abschicken anklicken";

$lang['Add_disallow'] = "Hinzufügen";
$lang['Add_disallow_title'] = "Einen verbotenen Namen hinzufügen";
$lang['Add_disallow_explain'] = "Sie können ein * benutzen, um jegliche Benutzernamen zu verbieten";

$lang['No_disallowed'] = "Keine verbotenen Benutzernamen";

$lang['Disallowed_deleted'] = "Der verbotene Benutzername ist nun wieder gestattet";
$lang['Disallow_successful'] = "Der verbotene Benutzername wurde hinzugefügt";
$lang['Disallowed_already'] = "Der angebene Benuztername kann nicht verboten werden. Er existiert entweder schon oder stimmt mit einem existierenden überein";

$lang['Click_return_disallowadmin'] = "Klicken Sie %shier%s, um zum Verbot der Benutzernamen zurück zu kehren";


//
// Styles Admin
//
$lang['Styles_admin'] = "Styles Administration";
$lang['Styles_explain'] = "Hier können Sie Styles (Templates und Themes) hinzufügen, löschen und überwachen.";
$lang['Styles_addnew_explain'] = "In der folgenden Liste sind alle für dieses Template verfügbaren Themes aufgeführt. Die in der Liste aufgeführten Objekte wurden der Datenbank noch nicht zugefügt. Um ein Theme zu installieren, klicken Sie einfach auf den Installieren-Link neben einem Eintrag";

$lang['Select_template'] = "Wählen Sie ein Template";

$lang['Style'] = "Style";
$lang['Template'] = "Template";
$lang['Install'] = "Installieren";
$lang['Download'] = "Runterladen";

$lang['Edit_theme'] = "Theme editieren";
$lang['Edit_theme_explain'] = "Hier können Sie die Einstellungen für das gewählte Theme ändern";

$lang['Create_theme'] = "Theme erstellen";
$lang['Create_theme_explain'] = "Hier können Sie ein neues Theme für das gewählte Template erstellen. Wenn Sie Farben eingeben (für die Sie Hexdezimalzahlen nutzen sollten), dürfen Sie das # nicht mit angeben - CCCCCC ist z.B. korrekt, #CCCCCC nicht";

$lang['Export_themes'] = "Theme exportieren";
$lang['Export_explain'] = "Hier können Sie die Themedaten für ein ein bestimmtes Template exportieren. Wählen Sie das Template aus der unteren Liste und das Script wird die Themekonfigurationsdatei erstellen und versuchen, sie in den Templatesordner zu speichern. Falls es die Datei nicht selbst speichern kann, können Sie sie runterladen. Um dem Skript das Schreiben der Datei zu ermöglichen, müssen Sie dem gewählten Templateordner Schreibrechte gewähren. Für weitere Informationen siehe den phpBB2 Benutzerguide.";

$lang['Theme_installed'] = "Das gewählte Theme wurde installiert";
$lang['Style_removed'] = "Der gewählte Style wurde aus der Datenbank entfernt. Um den Style völlig vom System zu entfernen, müssen Sie es aus Ihrem Templates-Ordner löschen.";
$lang['Theme_info_saved'] = "Die Themeinformationen für das gewählte Template wurden gespeichert. Sie sollten jetzt die Erlaubnis der theme_info.cfg (und eventueller Verzeichnisse) auf Nur-Lesen zurück stellen";
$lang['Theme_updated'] = "Das gewählte Theme wurde aktualisiert. Sie sollten die neuen Themeeinstellungen jetzt exportieren.";
$lang['Theme_created'] = "Theme erstellt. Sie sollten das Theme jetzt in die Themekonfiguration exportieren, damit es nicht verloren geht oder Sie es wo anders einsetzen können.";

$lang['Confirm_delete_style'] = "Diesen Style wirklich löschen?";

$lang['Download_theme_cfg'] = "Der Exporter konnte nicht in der Themeinformationsdatei schreiben. Klicken Sie auf den unteren Knopf, um die Datei per Browser runterzuladen. Haben Sie sie runtergeladen, können Sie sie in Ihren Ordner mit den Templatendateien transferieren. Schließlich können Sie die Dateien zu einem Paket zusammenschließen.";
$lang['No_themes'] = "Das gewählte Template hat keine verfügbaren Themes. Um ein neues Theme zu erstellen, klicken Sie auf den Theme erstellen-Link auf der linken Seite";
$lang['No_template_dir'] = "Konnte das Template-Verzeichnis nicht öffnen. Es ist eventuell unlesbar oder existiert nicht (mehr).";
$lang['Cannot_remove_style'] = "Sie können den gewählten Style nicht entfernen, da er zum Forumsstandard gehört. Sie können jedoch einen anderen Forumsstandard wählen und es erneut versuchen.";
$lang['Style_exists'] = "Der gewählte Stylename existiert bereits, bitte gehen Sie zurück und wählen Sie einen anderen Namen.";

$lang['Click_return_styleadmin'] = "Klicken Sie %shier%s, um zur Styles Administration zurück zu kehren";

$lang['Theme_settings'] = "Theme Einstellungen";
$lang['Theme_element'] = "Theme Element";
$lang['Simple_name'] = "Einfacher Name";
$lang['Value'] = "Wert";
$lang['Save_Settings'] = "Einstellungen übernehmen";

$lang['Stylesheet'] = "CSS Stylesheet";
$lang['Background_image'] = "Hintergrundbild";
$lang['Background_color'] = "Hintergrundfarbe";
$lang['Theme_name'] = "Themename";
$lang['Text_color'] = "Textfarbe";
$lang['Link_color'] = "Linkfarbe";
$lang['VLink_color'] = "Farbe für gesehener Link";
$lang['ALink_color'] = "Farbe für aktiver Link";
$lang['HLink_color'] = "Farbe für gewählter Link";
$lang['Tr_color1'] = "Farbe für Tabellenreihe 1";
$lang['Tr_color2'] = "Farbe für Tabellenreihe 2";
$lang['Tr_color3'] = "Farbe für Tabellenreihe 3";
$lang['Tr_class1'] = "Tabellenreihe Klasse 1";
$lang['Tr_class2'] = "Tabellenreihe Klasse 2";
$lang['Tr_class3'] = "Tabellenreihe Klasse 3";
$lang['Th_color1'] = "Farbe für Tabellenkopf 1";
$lang['Th_color2'] = "Farbe für Tabellenkopf 2";
$lang['Th_color3'] = "Farbe für Tabellenkopf 3";
$lang['Th_class1'] = "Tabellenkopf Klasse 1";
$lang['Th_class2'] = "Tabellenkopf Klasse 2";
$lang['Th_class3'] = "Tabellenkopf Klasse 3";
$lang['Td_color1'] = "Farbe für Tabllenzelle 1";
$lang['Td_color2'] = "Farbe für Tabllenzelle 2";
$lang['Td_color3'] = "Farbe für Tabllenzelle 3";
$lang['Td_class1'] = "Tabellenzelle Klasse 1";
$lang['Td_class2'] = "Tabellenzelle Klasse 2";
$lang['Td_class3'] = "Tabellenzelle Klasse 3";
$lang['fontface1'] = "Schriftart 1";
$lang['fontface2'] = "Schriftart 2";
$lang['fontface3'] = "Schriftart 3";
$lang['fontsize1'] = "Schriftgrösse 1";
$lang['fontsize2'] = "Schriftgrösse 2";
$lang['fontsize3'] = "Schriftgrösse 3";
$lang['fontcolor1'] = "Schriftfarbe 1";
$lang['fontcolor2'] = "Schriftfarbe 2";
$lang['fontcolor3'] = "Schriftfarbe 3";
$lang['span_class1'] = "Abstand Klasse 1";
$lang['span_class2'] = "Abstand Klasse 2";
$lang['span_class3'] = "Abstand Klasse 3";
$lang['img_poll_size'] = "Umfragen-Symbolgröße [px]";
$lang['img_pm_size'] = "Private Nachrichten-Statussymbolgröße [px]";

//
// That's all Folks!
// Na Gott sei Dank!
// -------------------------------------------------

?>
