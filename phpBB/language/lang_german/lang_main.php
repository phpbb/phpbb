<?php
/***************************************************************************
*
* Translation by:
* Joel Ricardo Zick Rici) webmaster@forcena-inn.de || http://www.forcena-inn.de
* Hannes Minimair (Thunder) phpbb2@xinfo.net || http://www.breakzone.cc
*
* For questions use: :webmaster@forcena-inn.de
*
***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

//
// The format of this file is:
//
// ---> $lang['message'] = "text";
//
// You should also try to set a locale and a character
// encoding (plus direction). The encoding and direction
// will be sent to the template. The locale may or may
// not work, it's dependent on OS support and the syntax
// varies ... give it your best guess!
//

setlocale(LC_ALL, "de_AT");
$lang['DATE_FORMAT'] =  "d.m.Y"; // This should be changed to the default date format for your language, php date() format
$lang['ENCODING'] = "iso-8859-1";
$lang['DIRECTION'] = "LTR";
$lang['LEFT'] = "LEFT";
$lang['RIGHT'] = "RIGHT";


//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Forum";
$lang['Category'] = "Kategorie";
$lang['Topic'] = "Thema";
$lang['Topics'] = "Themen";
$lang['Reply'] = "Antwort";
$lang['Replies'] = "Antworten";
$lang['sp_Replies'] = "Antwort(en)";
$lang['Views'] = "Aufrufe";
$lang['sp_Views'] = "Aufruf(e)";
$lang['Post'] = "Beitrag";
$lang['Posts'] = "Beiträge";
$lang['Posted'] = "Geschrieben";
$lang['Username'] = "Username";
$lang['Username_display'] = "angezeigter Username";
$lang['Password'] = "Passwort";
$lang['Email'] = "E-Mail";
$lang['Poster'] = "Poster";
$lang['Author'] = "Autor";
$lang['Time'] = "Zeit";
$lang['Hours'] = "Stunden";
$lang['Message'] = "Nachricht";
$lang['Info'] = "Info";

$lang['1_Day'] = "1 Tag";
$lang['7_Days'] = "7 Tage";
$lang['2_Weeks'] = "2 Wochen";
$lang['1_Month'] = "1 Monat";
$lang['3_Months'] = "3 Monate";
$lang['6_Months'] = "6 Monate";
$lang['1_Year'] = "1 Jahr";

$lang['Go'] = "Go";
$lang['Jump_to'] = "Gehe zu";
$lang['Submit'] = "Absenden";
$lang['Reset'] = "Zurücksetzen";
$lang['Cancel'] = "Abbrechen";
$lang['Preview'] = "Vorschau"; 
$lang['Confirm'] = "bestätigen"; 
$lang['Spellcheck'] = "Rechtschreibprüfung";
$lang['Yes'] = "Ja";
$lang['No'] = "Nein";
$lang['Enabled'] = "Aktiviert";
$lang['Disabled'] = "Deaktiviert";
$lang['Error'] = "Fehler";
$lang['Success'] = "Erfolg";

$lang['Next'] = "Weiter";
$lang['Previous'] = "Zurück";
$lang['Goto_page'] = "Gehe zu Seite";
$lang['Joined'] = "Hier seit";
$lang['IP_Address'] = "IP-Adresse";

$lang['Select_forum'] = "Forum auswählen";
$lang['View_latest_post'] = "Letzten Beitrag anzeigen";
$lang['Page_of'] = "Seite <b>%d</b> von <b>%d</b>"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "ICQ-Nummer";
$lang['AIM'] = "AIM-Name";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "%s Foren-Übersicht";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "Neuen Beitrag schreiben";
$lang['Reply_to_topic'] = "Antworten zum Beitrag";
$lang['Reply_with_quote'] = "Antworten mit Zitat";

$lang['Click_return_topic'] = "%sHier klicken%s, um zum Thema zurückzukehren"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "%sHier klicken%s, um es noch einmal zu versuchen";
$lang['Click_return_forum'] = "%sHier klicken%s, um zum Forum zurückzukehren";
$lang['Click_view_message'] = "%sHier klicken%s, um Deine Nachricht anzuzeigen";
$lang['Click_return_modcp'] = "%sHier klicken%s, um zur Moderatorenkontrolle zurückzukehren";
$lang['Click_return_group'] = "%sHier klicken%s, um zur Gruppeninfo zurückzukehren";

$lang['Admin_panel'] = "Foren-Administration (Admins)";
$lang['Board_disable'] = "Sorry, aber dieses Board ist im Moment nicht verfügbar! Probier es bitte später wieder!";

//
// Global Header strings
//
$lang['Registered_users'] = "Registrierte User:";
$lang['Online_users_total'] = "Insgesamt sind %d User online : ";
$lang['Online_user_total'] = "Insgesamt ist %d User online : ";
$lang['Reg_users_total'] = "%d Registrierte, ";
$lang['Reg_user_total'] = "%d Registrierter, ";
$lang['Hidden_users_total'] = "%d Versteckte und ";
$lang['Hidden_user_total'] = "%d Versteckter und ";
$lang['Guest_users_total'] = "%d Gäste";
$lang['Guest_user_total'] = "%d Gast";

$lang['You_last_visit'] = "Du warst das letzte Mal hier am %s"; // %s replaced by date/time
$lang['Search_new'] = "Beiträge seit dem letzten Besuch anzeigen";
$lang['Search_your_posts'] = "Eigene Beiträge anzeigen";
$lang['Search_unanswered'] = "Unbeantwortete Beiträge anzeigen";
$lang['Register'] = "Registrieren";
$lang['Profile'] = "Profil";
$lang['Edit_profile'] = "Profil bearbeiten";
$lang['Search'] = "Suchen";
$lang['Memberlist'] = "Mitgliederliste";
$lang['FAQ'] = "FAQ";
$lang['Usergroups'] = "Usergroups";
$lang['Last_Post'] = "Letzter&nbsp;Beitrag";
$lang['Moderator'] = "<b>Moderatoren</b>";


//
// Stats block text
//
$lang['Posted_article_total'] = "Unsere User haben insgesamt <b>%d</b> Artikel geschrieben"; // Number of posts
$lang['Posted_articles_total'] = "Unsere User haben insgesamt <b>%d</b> Artikel geschrieben"; // Number of posts
$lang['Registered_user_total'] = "Wir haben <b>%d</b> registrierten User"; // # registered users
$lang['Registered_users_total'] = "Wir haben <b>%d</b> registrierte User"; // # registered users
$lang['Newest_user'] = "Der neueste User ist <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "Keine neuen Beiträge seit Deinem letzten Besuch";
$lang['No_new_posts'] = "Keine neuen Beiträge";
$lang['New_posts'] = "Neue Beiträge";
$lang['New_post'] = "Neuer Beitrag";
$lang['No_new_posts_hot'] = "Keine neuen Beiträge [ Popular ]";
$lang['New_posts_hot'] = "Neue Beiträge [ Popular ]";
$lang['No_new_posts_locked'] = "Keine neuen Beiträge [ Gesperrt ]";
$lang['New_posts_locked'] = "Neue Beiträge [ Gesperrt ]";
$lang['Forum_is_locked'] = "Forum ist gesperrt";


//
// Login
//
$lang['Enter_password'] = "Gib bitte deinen Usernamen und dein Passwort ein, um dich einzuloggen";
$lang['Login'] = "Login";
$lang['Logout'] = "Logout";

$lang['Forgotten_password'] = "Ich habe mein Passwort vergessen";

$lang['Log_me_in'] = "Bei jedem Besuch automatisch einloggen";


//
// Index page
//
$lang['Index'] = "Index";
$lang['No_Posts'] = "Keine Beiträge";
$lang['No_forums'] = "Dieses Board hat keine Foren";

$lang['Private_Message'] = "Private Nachricht";
$lang['Private_Messages'] = "Private Nachrichten";
$lang['Who_is_Online'] = "Wer ist online";

$lang['Mark_all_forums'] = "Alle Foren als gelesen markieren";
$lang['Forums_marked_read'] = "Alle Foren wurden als gelesen markiert";


//
// Viewforum
//
$lang['View_forum'] = "Forum anzeigen";

$lang['Forum_not_exist'] = "Das ausgewählte Forum existiert nicht";
$lang['Reached_on_error'] = "Fehler auf dieser Seite!";

$lang['Display_topics'] = "Siehe Beiträge der letzten:";
$lang['All_Topics'] = "Alle Themen anzeigen";

$lang['Topic_Announcement'] = "<b>Ankündigungen:</b>";
$lang['Topic_Sticky'] = "<b>Wichtig:</b>";
$lang['Topic_Moved'] = "<b>Verschoben:</b>";
$lang['Topic_Poll'] = "<b>[ Umfrage ]</b>";

$lang['Mark_all_topics'] = "Alle Themen als gelesen markieren";
$lang['Topics_marked_read'] = "Alle Themen wurden als gelesen markiert";

$lang['Rules_post_can'] = "Du <b>kannst</b> Beiträge in dieses Forum schreiben";
$lang['Rules_post_cannot'] = "Du <b>kannst keine</b> Beiträge in dieses Forum schreiben";
$lang['Rules_reply_can'] = "Du <b>kannst</b> auf Beiträge in diesem Forum antworten";
$lang['Rules_reply_cannot'] = "Du <b>kannst</b> auf Beiträge in diesem Forum <b>nicht</b> antworten";
$lang['Rules_edit_can'] = "Du <b>kannst</b> deine Beiträge in diesem Forum bearbeiten";
$lang['Rules_edit_cannot'] = "Du <b>kannst</b> deine Beiträge in diesem Forum <b>nicht</b> bearbeiten";
$lang['Rules_delete_can'] = "Du <b>kannst</b> deine Beiträge in diesem Forum löschen";
$lang['Rules_delete_cannot'] = "Du <b>kannst</b> deine Beiträge in diesem Forum <b>nicht</b> löschen";
$lang['Rules_vote_can'] = "Du <b>kannst</b> an Umfragen in diesem Forum mitmachen";
$lang['Rules_vote_cannot'] = "Du <b>kannst</b> an Umfragen in diesem Forum <b>nicht</b> mitmachen";
$lang['Rules_moderate'] = "Du <b>kannst</b> %sdieses Forum moderieren%s"; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = "In diesem Forum sind keine Beiträge vorhanden.<br />Clicke auf <b>Neuen Beitrag erstellen</b>-Link um einen neuen für diese Seite zu erstellen.";

//
// Viewtopic
//
$lang['View_topic'] = "Thema anzeigen";

$lang['Guest'] = 'Gast';
$lang['Post_subject'] = "Titel";
$lang['View_next_topic'] = "Nächstes Thema anzeigen";
$lang['View_previous_topic'] = "Vorheriges Thema anzeigen";
$lang['Submit_vote'] = "Stimme absenden";
$lang['View_results'] = "Ergebnis anzeigen";

$lang['No_newer_topics'] = "Es hat keine neueren Themen in diesem Forum";
$lang['No_older_topics'] = "Es hat keine älteren Themen in diesem Forum";
$lang['Topic_post_not_exist'] = "Das gewählte Thema oder der Beitrag existiert nicht";
$lang['No_posts_topic'] = "Es existieren keine Beiträge zu diesem Thema";

$lang['Display_posts'] = "Beiträge vom vorherigen Thema anzeigen";
$lang['All_Posts'] = "Alle Beiträge";
$lang['Newest_First'] = "Die neusten zuerst";
$lang['Oldest_First'] = "Die ältesten zuerst";

$lang['Return_to_top'] = "Nach oben";

$lang['Read_profile'] = "User-Profile anzeigen"; 
$lang['Send_email'] = "E-Mail an diesen User senden";
$lang['Visit_website'] = "Website dieses Users besuchen";
$lang['ICQ_status'] = "ICQ-Status";
$lang['Edit_delete_post'] = "Beitrag bearbeiten oder löschen";
$lang['View_IP'] = "IP zeigen";
$lang['Delete_post'] = "Beitrag löschen";

$lang['wrote'] = "hat folgendes geschrieben:"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Zitat"; // comes before bbcode quote output.
$lang['Code'] = "Code"; // comes before bbcode code output.

$lang['Edited_time_total'] = "Letztmals bearbeitet von %s am %s, insgesamt %d-mal bearbeitet"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "etztmals bearbeitet von %s am %s, insgesamt %d-mal bearbeitet"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Thema sperren";
$lang['Unlock_topic'] = "Thema entsperren";
$lang['Move_topic'] = "Thema verschieben";
$lang['Delete_topic'] = "Thema löschen";
$lang['Split_topic'] = "Thema teilen";

$lang['Stop_watching_topic'] = "Bei Antworten zu diesem Thema nicht mehr benachrichtigen";
$lang['Start_watching_topic'] = "Bei Antworten zu diesem Thema benachrichtigen";
$lang['No_longer_watching'] = "You are no longer watching this topic";
$lang['You_are_watching'] = "You are now watching this topic";


//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Nachrichtentext";
$lang['Topic_review'] = "Thema-Überblick";

$lang['No_post_mode'] = "No post mode specified";

$lang['Post_a_new_topic'] = "Neues Thema schreiben";

$lang['Post_a_reply'] = "Antwort schreiben";
$lang['Post_topic_as'] = "Thema schreiben als";
$lang['Edit_Post'] = "Beitrag editieren";
$lang['Options'] = "Optionen";

$lang['Post_Announcement'] = "Ankündigung";
$lang['Post_Sticky'] = "Wichtig";
$lang['Post_Normal'] = "Normal";

$lang['Confirm_delete'] = "Sicher, dass dieses Thema gelöscht werden soll?";
$lang['Confirm_delete_poll'] = "Sicher, dass diese Umfrage gelöschte werden soll?";

$lang['Flood_Error'] = "Du kannst einen Beitrag nicht so schnell nach Deinem letzten absenden, bitte warte einen Augenblick";
$lang['Empty_subject'] = "Bei einem neuen Thema musst du einen Titel angeben";
$lang['Empty_message'] = "Du musst zu Deinem Beitrag einen Text eingeben";

$lang['Announce_and_Sticky'] = "Ein Beitrag kann nicht gleichzeit eine Ankündigung und wichtig sein";
$lang['Forum_locked'] = "Dieses Forum ist gesperrt, du kannst keine Beiträge editieren, schreiben oder beantworten";
$lang['Topic_locked'] = "Dieses Thema ist gesperrt, du kannst keine Beiträge editieren oder beantworten";
$lang['No_post_id'] = "Du musst einen Beitrag zum editieren auswählen";
$lang['No_topic_id'] = "Du musst ein Thema für diene Antwort angeben";
$lang['No_valid_mode'] = "Du kannst nur Beiträge schreiben, bearbeiten, beantworten und zitieren. Versuch es noch einmal";
$lang['No_such_post'] = "Es existiert kein solcher Beitrag. Versuch es noch einmal";
$lang['Edit_own_posts'] = "Du kannst nur deine eigenen Beiträge bearbeiten";
$lang['Delete_own_posts'] = "Du kannst nur deine eigenen Beiträge löschen";
$lang['Cannot_delete_replied'] = "Du kannst keine Beiträge löschen, die schon beantwortet wurden";
$lang['Cannot_delete_poll'] = "Du kannst keine aktiven Umfrage löschen";
$lang['Empty_poll_title'] = "Du musst einen Titel für die Umfrage eingeben";
$lang['To_few_poll_options'] = "Du musst mindestens zwei Möglichkeiten für die Umfrage angeben";
$lang['To_many_poll_options'] = "Du hast zu viele Möglichkeiten für die Umfrage angegeben";
$lang['Post_has_no_poll'] = "Dieser Beitrag hat keine Umfrage";

$lang['Add_poll'] = "Umfrage hinzufügen";
$lang['Add_poll_explain'] = "Wenn du keine Umfrage zum Thema hinzufügen willst, lass die Felder leer";
$lang['Poll_question'] = "Frage";
$lang['Poll_option'] = "Möglichkeit";
$lang['Add_option'] = "Möglichkeit hinzufügen";
$lang['Update'] = "Aktualisieren";
$lang['Delete'] = "Löschen";
$lang['Poll_for'] = "Umfrage durchführen bis";
$lang['Poll_for_explain'] = "[ Gib 0 ein oder lass dieses Feld leer, um die Umfrage auf unbeschränkte Zeit durchzuführen ]";
$lang['Delete_poll'] = "Umfrage löschen";

$lang['Disable_HTML_post'] = "HTML in diesem Beitrag deaktivieren";
$lang['Disable_BBCode_post'] = "BBCode in diesem Beitrag deaktivieren";
$lang['Disable_Smilies_post'] = "Smilies in diesem Beitrag deaktivieren";

$lang['HTML_is_ON'] = "HTML ist <u>ein</u>";
$lang['HTML_is_OFF'] = "HTML ist <u>aus</u>";
$lang['BBCode_is_ON'] = "BBCode ist <u>ein</u>";
$lang['BBCode_is_OFF'] = "BBCode ist <u>aus</u>";
$lang['Smilies_are_ON'] = "Smilies sind <u>ein</u>";
$lang['Smilies_are_OFF'] = "Smilies sind <u>aus</u>";

$lang['Attach_signature'] = "Signatur anhängen (Signatur kann im Profil geändert werden)";
$lang['Notify'] = "Benachrichtigt mich, wenn eine Antwort geschrieben wurde";
$lang['Delete_post'] = "Diesen Beitrag löschen";

$lang['Stored'] = "Deine Nachricht wurde erfolgreich eingetragen";
$lang['Deleted'] = "Deine Nachricht wurde erfolgreich gelöscht";
$lang['Poll_delete'] = "Deine Umfrage wurde erfolgrich gelöscht";
$lang['Vote_cast'] = "Deine Stimme wurde gezählt";

$lang['Topic_reply_notification'] = "Benachrichtigen bei Antworten";

$lang['bbcode_b_help'] = "Text in fett: [b]Text[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Text in kursiv: [i]Text[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Unterstrichener Text: [u]Text[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Zitat: [quote]Text[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Code anzeigen: [code]Code[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Liste: [list]Text[/list] (alt+l)";
$lang['bbcode_o_help'] = "Geordnete Liste: [list=]Text[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Bild einfügen: [img]http://URL_des_Bildes[/img]  (alt+p)";
$lang['bbcode_w_help'] = "URL einfügen: [url]http://URL[/url] oder [url=http://url]URL Text[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Alle offenen BBCodes schließen";
$lang['bbcode_s_help'] = "Schriftfarbe: [color=red]Text[/color]  Tip: Du kannst ebenfalls color=#FF0000 benutzen";
$lang['bbcode_f_help'] = "Schriftgröße: [size=x-small]Kleiner Text[/size]";

$lang['Emoticons'] = "Smilies";
$lang['More_emoticons'] = "Weitere Smilies ansehen";

$lang['Font_color'] = "Schriftfarbe";
$lang['color_default'] = "Standard";
$lang['color_dark_red'] = "Dunkelrot";
$lang['color_red'] = "Rot";
$lang['color_orange'] = "Orange";
$lang['color_brown'] = "Braun";
$lang['color_yellow'] = "Gelb";
$lang['color_green'] = "Grün";
$lang['color_olive'] = "Oliv";
$lang['color_cyan'] = "Cyan";
$lang['color_blue'] = "Blau";
$lang['color_dark_blue'] = "Dunkelblau";
$lang['color_indigo'] = "Indigo";
$lang['color_violet'] = "Violett";
$lang['color_white'] = "Weiß";
$lang['color_black'] = "Schwarz";

$lang['Font_size'] = "Schriftgröße";
$lang['font_tiny'] = "Winzig";
$lang['font_small'] = "Klein";
$lang['font_normal'] = "Normal";
$lang['font_large'] = "Groß";
$lang['font_huge'] = "Riesig";

$lang['Close_Tags'] = "Tags schließen";
$lang['Styles_tip'] = "Tip: Styles können schnell markiertem Text hinzugefügt werden";

//
// Private Messaging
//
$lang['Private_Messaging'] = "Privat-Nachrichten";

$lang['Login_check_pm'] = "Log dich ein, um die Privat-Nachrichten zu lesen";
$lang['New_pms'] = "Du hast %d neue Nachrichten"; // You have 2 new messages
$lang['New_pm'] = "Du hast %d neue Nachricht"; // You have 1 new message
$lang['No_new_pm'] = "Du hast keine neuen Nachrichten";
$lang['Unread_pms'] = "Du hast %d ungelesene Nachrichten";
$lang['Unread_pm'] = "Du hast %d ungelesene Nachricht";
$lang['No_unread_pm'] = "Du hst keine ungelesenen Nachrichten";
$lang['You_new_pm'] = "Eine Privat-Nachricht befindet sich in deiner Inbox";
$lang['You_new_pms'] = "Es befinden sich keine Privat-Nachrichten in deiner Inbox";
$lang['You_no_new_pm'] = "Es sind keine neuen Privat-Nachrichten vorhanden";

$lang['Inbox'] = "Inbox";
$lang['Outbox'] = "Outbox";
$lang['Savebox'] = "Archiv";
$lang['Sentbox'] = "Gesendete Nachrichten";
$lang['Flag'] = "Flag";
$lang['Subject'] = "Titel";
$lang['From'] = "Von";
$lang['To'] = "An";
$lang['Date'] = "Datum";
$lang['Mark'] = "Markiert";
$lang['Sent'] = "Gesendet";
$lang['Saved'] = "Gespeichert";
$lang['Delete_marked'] = "Markierte löschen";
$lang['Delete_all'] = "Alle löschen";
$lang['Save_marked'] = "Markierte speichern"; 
$lang['Save_message'] = "Nachrichten speichern";
$lang['Delete_message'] = "Nachrichten löschen";

$lang['Display_messages'] = "Nachrichten anzeigen der letzten"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "Alle Nachrichten";

$lang['No_messages_folder'] = "Es sind keine weiteren Nachrichten in diesem Ordner";

$lang['PM_disabled'] = "Privat-Nachrichten wurden in diesem Board deaktiviert";
$lang['Cannot_send_privmsg'] = "Der Administrator hat Privat-Nachrichten bei Ihnen gesperrt";
$lang['No_to_user'] = "Du musst einen Usernamen angeben, um diese Nachricht zu senden";
$lang['No_such_user'] = "Es existiert kein User mit diesem Namen";

$lang['Message_sent'] = "Deine Nachricht wurde gesendet";

$lang['Click_return_inbox'] = "Klick %shier%s um zur Inbox zurückzukehren";
$lang['Click_return_index'] = "Klick %shier%s um zum Index zurückzukehren";

$lang['Re'] = "Re"; // Re as in 'Response to'

$lang['Send_a_new_message'] = "Neue Nachricht senden";
$lang['Send_a_reply'] = "Auf Privat-Nachricht antworten";
$lang['Edit_message'] = "Privat-Nachricht bearbeiten";

$lang['Notification_subject'] = "Eine neue Privat-Nachricht ist eingetroffen";

$lang['Find_username'] = "Usernamen finden";
$lang['Find'] = "Finden";
$lang['No_match'] = "Keine Ergebnisse gefunden";

$lang['No_post_id'] = "Es wurde keine Beitrags-ID angegeben";
$lang['No_such_folder'] = "Es existiert kein solcher Ordner";
$lang['No_folder'] = "Kein Ordnerr ausgewählt";

$lang['Mark_all'] = "Alle Markieren";
$lang['Unmark_all'] = "Markierungen aufheben";

$lang['Confirm_delete_pm'] = "Diese Nachricht wirklich löschen?";
$lang['Confirm_delete_pms'] = "Diese Nachrichten wirklich löschen?";

$lang['Inbox_size'] = "Deine Inbox ist %d%% voll"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Deine Gesendeten Nachrichten sind %d%% voll"; 
$lang['Savebox_size'] = "Dein Archiv ist %d%% voll"; 

$lang['Click_view_privmsg'] = "Klick %shier%s, um deine Inbox aufzurufen";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Profil anzeigen :: %s"; // %s is username 
$lang['About_user'] = "Alles über %s";

$lang['Preferences'] = "Einstellungen";
$lang['Items_required'] = "Mit * markierte Felder sind erforderlich";
$lang['Registration_info'] = "Registrations-Information";
$lang['Profile_info'] = "Profil-Information";
$lang['Profile_info_warn'] = "Diese Information ist öffentlich abrufbar";
$lang['Avatar_panel'] = "Avatar-Steuerung";
$lang['Avatar_gallery'] = "Avatar-Galerie";

$lang['Website'] = "Website";
$lang['Location'] = "Ort";
$lang['Contact'] = "Kontakt";
$lang['Email_address'] = "E-Mail-Adresse";
$lang['Email'] = "E-Mail";
$lang['Send_private_message'] = "Privat-Nachricht senden";
$lang['Hidden_email'] = "[ Versteckt ]";
$lang['Search_user_posts'] = "Nachrichten von diesem User anzeigen";
$lang['Interests'] = "Interessen";
$lang['Occupation'] = "Geschlecht"; 
$lang['Poster_rank'] = "Rang";

$lang['Total_posts'] = "Beiträge insgesamt";
$lang['User_post_pct_stats'] = "%.2f%% aller Beiträge"; // 1.25% of total
$lang['User_post_day_stats'] = "%.2f Beiträge pro Tag"; // 1.5 posts per day
$lang['Search_user_posts'] = "Alle Beiträge von %s anzeigen"; // Find all posts by username

$lang['No_user_id_specified'] = "Dieser User existiert nicht";
$lang['Wrong_Profile'] = "Du kannst nur dein eigenes Profil bearbeiten";
$lang['Bad_username'] = "Der Username ist leider schon vergeben oder gesperrt";
$lang['Sorry_banned_or_taken_email'] = "Die E-Mail-Adresse, die du eingegeben hast, wurde entweder gesperrt, ist ungültig, oder wurde schon von einem anderen User registriert. Bitte versuch es mit einer anderen Adresse";
$lang['Only_one_avatar'] = "Es kann nur ein Avatar ausgewählt werden";
$lang['File_no_data'] = "Die angegebene Date enthält keine Daten";
$lang['No_connection_URL'] = "Es konnte keine Verbindung zur angegebenen Datei hergestellt werden";
$lang['Incomplete_URL'] = "Die angegebene URL ist unvollständig";
$lang['Wrong_remote_avatar_format'] = "Das Format des Avatars ist nicht gültig";
$lang['No_send_account_inactive'] = "Sorry, aber ein neues Passwort kann im Moment nicht zu gesendet werden, da dein Account derzeit noch inaktiv ist. Bitte kontaktiere den Administrator für weitere Informationen.";

$lang['Always_smile'] = "Smilies immer aktivieren";
$lang['Always_html'] = "HTML immer aktivieren";
$lang['Always_bbcode'] = "BBCode immer aktivieren";
$lang['Always_add_sig'] = "Signatur immer anhängen";
$lang['Always_notify'] = "Bei Antworten immer benachrichtigen";
$lang['Always_notify_explain'] = "Sendet dir ein Mail, wenn jemand auf einen deiner Beiträge antwortet. Kann bei jedem Beitrag geändert werden";

$lang['Board_style'] = "Board-Style";
$lang['Board_lang'] = "Board-Sprache";
$lang['No_themes'] = "Keine Themes in der Datenbank";
$lang['Timezone'] = "Zeitzone";
$lang['Date_format'] = "Datums-Format";
$lang['Date_format_explain'] = "Die Syntax ist identisch mit der PHP-Funktion <a href=\"http://www.php.net/date\" target=\"_other\">date()</a>";
$lang['Signature'] = "Signatur";
$lang['Signature_explain'] = "Dies ist ein Text, der an jeden Beitrag von dir angehängt werden kann. Es besteht eine Limite von %d Buchstaben";
$lang['Public_view_email'] = "Zeige meine E-Mail-Adresse immer an";

$lang['Current_password'] = "Altes Passwort";
$lang['New_password'] = "Neues Passwort";
$lang['Confirm_password'] = "Neues Passwort bestätigen";
$lang['password_if_changed'] = "Du musst nur ein neues Passwort angeben, wenn du es ändern willst";
$lang['password_confirm_if_changed'] = "Du musst dein neues Passwort bestätigen, wenn oben auch eins eingegeben hast";

$lang['Avatar'] = "Avatar";
$lang['Avatar_explain'] = "Zeigt eine kleine Grafik neben deinen Details zu jedem deiner Beiträge an. Es kann immer nur ein Avatar angezeigt werden, seine Breite darf nicht grösser als %d Pixel sein, die Höhe nicht grösser als %d  Pixel, und die Dateigrösse darf maximal %d KB betragen."; $lang['Upload_Avatar_file'] = "Avatar von Deinem Computer hochladen";
$lang['Upload_Avatar_URL'] = "Avatar von URL hochladen";
$lang['Upload_Avatar_URL_explain'] = "Gib die URL des gewünschten Avatars an, dieser wird dann kopiert";
$lang['Pick_local_Avatar'] = "Avatar aus der Galerie auswählen";
$lang['Link_remote_Avatar'] = "Zu einem externen Avatar linken";
$lang['Link_remote_Avatar_explain'] = "Gib die URL des Avatars ein, der gelinkt werden soll";
$lang['Avatar_URL'] = "URL des Avatars";
$lang['Select_from_gallery'] = "Avatar aus der Galerie auswählen";
$lang['View_avatar_gallery'] = "Galerie anzeigen";

$lang['Select_avatar'] = "Avatar auswählen";
$lang['Return_profile'] = "Avatar abbrechen";
$lang['Select_category'] = "Kategorie auswählen";

$lang['Delete_Image'] = "Bild löschen";
$lang['Current_Image'] = "Aktuelles Bild";

$lang['Notify_on_privmsg'] = "Bei neuen Privat-Nachrichten benachrichtigen";
$lang['Popup_on_privmsg'] = "PopUp-Fenster bei neuen Privat-Nachrichten"; 
$lang['Popup_on_privmsg_explain'] = "Einige Templates öffnen neue Fenste, um dich über neue Privat-Nachrichten zu benachrichtigen"; 
$lang['Hide_user'] = "Online-Status verstecken";

$lang['Profile_updated'] = "Dein Profil wurde aktualisiert";
$lang['Profile_updated_inactive'] = "Your profile has been updated, however you have changed vital details thus your account is now inactive. Check your email to find out how to reactivate your account, or if admin activation is require wait for the administrator to reactivate your account";

$lang['Password_mismatch'] = "Das angegebene Passwort ist nicht korrekt";
$lang['Current_password_mismatch'] = "Das aktuelle Passwort stimmt nicht mit dem in der Datenbank überein";
$lang['Invalid_username'] = "Der angeforderte Username ist besetzt oder gesperrt oder er enthält verbotene Zeichen wie das \"-Zeichen.";
$lang['Signature_too_long'] = "Deine Signatur ist zu lang";
$lang['Fields_empty'] = "Du musst alle benötigten Felder ausfüllen";
$lang['Avatar_filetype'] = "Der Avatar muss im GIF-, JPG- oder PNG-Format sein";
$lang['Avatar_filesize'] = "Die Dateigrösse muss kleiner als %d kB sein."; // followed by xx kB, xx being the size
$lang['Avatar_imagesize'] = "Der Avatar muss weniger als " . $board_config['avatar_max_width'] . " Pixel breit und " . $board_config['avatar_max_height'] . " Pixel hoch sein"; 

$lang['Welcome_subject'] = "Willkommen auf %s";
$lang['New_account_subject'] = "Neuer User-Account";
$lang['Account_activated_subject'] = "Account aktiviert";
$lang['Account_added'] = "Danke für die Registration, dien Account wurde erstellt. Du kannst dich jetzt mit Deinem Usernamen und Deinem Passwort einloggen";
$lang['Account_inactive'] = "Dein Account wurde erstellt. Dieses Forum benötigt aber eine Aktivierungs-Bestätigung, daher wurde ein Activation-Key an deine E-Mail-Adresse gesendet. Bitte überprüfe jetzt deine Mailbox für weitere Informationen";
$lang['Account_inactive_admin'] = "Dein Account wurde erstellt. Dieser muss noch durch den Administrator freigeschaltet werden. Du wirst benachrichtigt, wenn dies geschehen ist";
$lang['Account_active'] = "Dein Account wurde aktiviert. Danke für die Registration";
$lang['Account_active_admin'] = "Dein Account wurde jetzt aktiviert";
$lang['Reactivate'] = "Account wieder aktivieren!";
$lang['COPPA'] = "Dein Account wurde erstellt, muss aber zuerst überprüft werden. Mehr Details dazu wurden dir per E-Mail gesendet";

$lang['Wrong_activation'] = "Der Activation-Key stimmt nicht mit dem in der Datenbank überein";
$lang['Send_password'] = "Schickt mir ein neues Passwort"; 
$lang['Password_updated'] = "Ein neues Passwort wurde erstellt, es wurde ein E-Mail mit weiteren Anweisungen verschickt.";
$lang['No_email_match'] = "Die angegebene E-Mail-Adresse stimmt nicht mit dem Usernamen überein";
$lang['New_password_activation'] = "New password activation";
$lang['Password_activated'] = "Dein Account wurde wieder aktiviert. Um dich wieder einzuloggen, benutze das Passwort, das du per E-Mail bekommen hast";

$lang['Send_email_msg'] = "E-Mail senden";
$lang['No_user_specified'] = "Es wurde kein User ausgewählt";
$lang['User_prevent_email'] = "Dieser User hat den E-Mail-Empfang deaktiviert. Bitte versuche s mit einer Privat-Nachricht";
$lang['User_not_exist'] = "Dieser User existiert nicht";
$lang['CC_email'] = "Eine Kopie dieser E-Mail an dich senden";
$lang['Email_message_desc'] = "Diese Nachricht wird als Nur-Text gesendet, ohne HTML und BBCode. Als Antwort-Adresse wird deine angegeben.";
$lang['Flood_email_limit'] = "Im Moment kannst du keine weiteren E-Mails versenden. Versuch es später noch einmal";
$lang['Recipient'] = "Empfänger";
$lang['Email_sent'] = "E-Mail wurde gesendet";
$lang['Send_email'] = "E-Mail senden";
$lang['Empty_subject_email'] = "Du musst einen Titel für diese E-Mail angeben";
$lang['Empty_message_email'] = "Du musst einen Text zur E-Mail angeben";

$lang['Registration'] = "Einverstädniserklärung";
$lang['Reg_agreement'] = "Ich bin mir bewusst, dass ich mich für sämtliche Inhalte, die ich geschriebe habe, verantworten muss! Vereinbarungen, die auf diesem Wege statt fanden, sind bindend. Ich bin mir bewusst, dass der Administrator das Recht hat, mich zu sperren oder dauerhaft aus dem Forum zu bannen, sofern von mir geschriebene Beiträge dies rechtfertigen. Ich weiß, dass die Moderatoren und der Administrator das Recht haben, meine Beiträge zu editieren oder zu löschen. Schließlich bin ich mir ebenfalls bewusst, dass rechtliche Verstöße (zum Beispiel Links zu Porno-, Warez- oder anderen illegalen Seiten) vom Administrator oder anderen Usern gemeldet werden müssen und dass ich die vollen rechtlichen Konsequenzen zu tragen habe!";

$lang['Agree_under_13'] = "Ich bin mit diesen Konditionen dieses Forums einverstanden und <b>unter</b> 12 Jahre alt";
$lang['Agree_over_13'] = "Ich bin mit diesen Konditionen dieses Forums einverstanden und <b>über</b> 12 Jahre alt";
$lang['Agree_not'] = "Ich bin mit diesen Konditionen nicht einverstanden";


//
// Memberslist
//
$lang['Select_sort_method'] = "Sortierungs-Methode auswählen";
$lang['Sort'] = "Sortieren";
$lang['Sort_Top_Ten'] = "Top-Ten-Autoren";
$lang['Sort_Joined'] = "Anmeldungsdatum";
$lang['Sort_Username'] = "Username";
$lang['Sort_Location'] = "Ort";
$lang['Sort_Posts'] = "Beiträge total";
$lang['Sort_Email'] = "E-Mail";
$lang['Sort_Website'] = "Website";
$lang['Sort_Ascending'] = "Aufsteigend";
$lang['Sort_Descending'] = "Absteigend";
$lang['Order'] = "Ordnung";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Gruppen-Kontrolle";
$lang['Group_member_details'] = "Details zur Gruppen-Mitgliederschaft";
$lang['Group_member_join'] = "Gruppe beitreten";

$lang['Group_Information'] = "Information";
$lang['Group_name'] = "Name";
$lang['Group_description'] = "Beschreibung";
$lang['Group_membership'] = "Gruppen-Mitgliedschaft";
$lang['Group_Members'] = "Gruppen-Mitglieder";
$lang['Group_Moderator'] = "Gruppen-Moderatoren";
$lang['Pending_members'] = "Wartende Mitglieder";

$lang['Group_type'] = "Gruppentyp";
$lang['Group_open'] = "Offene Gruppe";
$lang['Group_closed'] = "Geschlossene Gruppe";
$lang['Group_hidden'] = "Versteckte Gruppe";

$lang['Current_memberships'] = "Aktuelle Mitgliedschaften";
$lang['Non_member_groups'] = "Gruppen ohne deine Mitgliedschaft";
$lang['Memberships_pending'] = "Warten auf Mitgliedschaft";

$lang['No_groups_exist'] = "Es existieren keine Gruppen";
$lang['Group_not_exist'] = "Diese Gruppe existiert nicht";

$lang['Join_group'] = "Gruppe beitreten";
$lang['No_group_members'] = "Diese Gruppe hat keine Mitglieder";
$lang['Group_hidden_members'] = "Diese Gruppe ist versteckt, du kannst keine Mitgliedschaften anzeigen";
$lang['No_pending_group_members'] = "Diese Gruppe hat keine wartenden Mitglieder";
$lang["Group_joined"] = "Du wurdest erfolgreich bei dieser Gruppe angemeldet<br />Du wirst benachrichtigt, wenn der Gruppenmoderator deine Mitgliedschaft akzeptiert hat";
$lang['Group_request'] = "Eine Anfrage zum Beitritt in diese Gruppe wurde gemacht";
$lang['Group_approved'] = "Deine Anfrage wurde akzeptiert";
$lang['Group_added'] = "Du bist dieser Gruppe beigetreten"; 
$lang['Already_member_group'] = "Du bist bereits ein Mitglied dieser Gruppe";
$lang['User_is_member_group'] = "Dieser User ist bereits ein Mitglied dieser Gruppe";
$lang['Group_type_updated'] = "Gruppentyp wurde erfolgreich aktualisiert";

$lang['Could_not_add_user'] = "Die gewählte Gruppe existiert nicht";

$lang['Confirm_unsub'] = "Bist du sicher, dass du die Mitgliedschaft in dieser Gruppe beenden möchtest?";
$lang['Confirm_unsub_pending'] = "Deine Anmeldung bei der Gruppe wurde noch nicht bestätigt, möchtest du wirklich austreten?";

$lang['Unsub_success'] = "Du wurdest abgemeldet von dieser Gruppe.";

$lang['Approve_selected'] = "Akzeptierte ausgewählt";
$lang['Deny_selected'] = "Gewählte löschen";
$lang['Not_logged_in'] = "Du musst eingeloggt sein, um einer Gruppe beizutreten.";
$lang['Remove_selected'] = "Ausgewählte entfernen";
$lang['Add_member'] = "Mitglied hinzufügen";
$lang['Not_group_moderator'] = "Du bist nicht der Gruppenmoderator, daher kannst du diese Aktion auch nicht durchführen.";

$lang['Login_to_join'] = "Einloggen, um Gruppe zu managen";
$lang['This_open_group'] = "Dies ist eine offene Gruppe. Klick, um eine Mitgliedschaft zu beantragen";
$lang['This_closed_group'] = "Dies ist eine geschlossene Gruppe, keine weiteren Mitglieder werden akzeptiert";
$lang['This_hidden_group'] = "Dies ist eine geschlossene Gruppe, automatische Anmeldungen werden nicht akzeptiert";
$lang['Member_this_group'] = "Du bist ein Mitglied dieser Gruppe";
$lang['Pending_this_group'] = "Du wartest auf eine Mitgliedschaft in dieser Gruppe";
$lang['Are_group_moderator'] = "Du bist der Moderator dieser Gruppe";
$lang['None'] = "Keine";

$lang['Subscribe'] = "Anmelden";
$lang['Unsubscribe'] = "Abmelden";
$lang['View_Information'] = "Information anzeigen";


//
// Search
//
$lang['Search_query'] = "Suchabfrage";
$lang['Search_options'] = "Suchoptionen";

$lang['Search_keywords'] = "Nach Schlüsselwörtern suchen";
$lang['Search_keywords_explain'] = "Du kannst <u>AND</u> benutzen, um Wörter zu definieren, die vorkommen müssen, <u>OR</u> kannst du benutzen für Wörter, die im Resultat sein können und <u>NOT</u> verbietet das nachfolgende Wort im Resultat. Das *-Zeichen kannst du als Joker benutzen.";
$lang['Search_author'] = "Nach Autor suchen";
$lang['Search_author_explain'] = "Benutze das *-Zeichen als Joker";

$lang['Search_for_any'] = "Nach irgendeinem Wort suchen";
$lang['Search_for_all'] = "Nach allen Wörtern suchen";

$lang['Search_author'] = "Nach Autor suchen";

$lang['Return_first'] = "Die ersten "; // followed by xxx characters in a select box
$lang['characters_posts'] = "Zeichen des Beitrags anzeigen";

$lang['Search_previous'] = "Durchsuchen"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "Sortieren nach";
$lang['Sort_Time'] = "Zeit";
$lang['Sort_Post_Subject'] = "Titel des Beitrags";
$lang['Sort_Topic_Title'] = "Titel des Themas";
$lang['Sort_Author'] = "Autor";
$lang['Sort_Forum'] = "Forum";

$lang['Display_results'] = "Ergebnis anzeigen als";
$lang['All'] = "Alle";

$lang['No_search_match'] = "Keine Beiträge entsprechen deinen Kriterien";
$lang['Found_search_match'] = "Die Suche hat %d Ergebnis ergeben"; // eg. Search found 1 match
$lang['Found_search_matches'] = "Die Suche hat %d Ergebnisse ergeben"; // eg. Search found 24 matches

$lang['Close_window'] = "Fenster schliessen";

//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays 
$lang['Sorry_auth_announce'] = "Sorry, aber es können nur %s Ankündigungen in diesem Forum posten.";
$lang['Sorry_auth_Sticky'] = "Sorry, aber es können nur %s Wichtige Nachrichten in diesem Forum erstellen.";
$lang['Sorry_auth_read'] = "Sorry, aber es können nur %s Beiträge in diesem Forum lesen.";
$lang['Sorry_auth_post'] = "Sorry, aber es können nur %s Beiträge in diesem Forum erstellen.";
$lang['Sorry_auth_reply'] = "Sorry, aber es können nur %s auf Beiträge in diesem Forum antworten.";
$lang['Sorry_auth_edit'] = "Sorry, aber es können nur %s Beiträge in diesem Forum editieren.";
$lang['Sorry_auth_delete'] = "Sorry, aber es können nur %s Beiträge in diesem Forum löschen.";
$lang['Sorry_auth_vote'] = "Sorry, aber es können sich nur %s für Abstimmungen in diesem Forum beteiligen.";

$lang['Sorry_auth'] = "Sorry, aber nur "; // This is followed by the auth type, eg. Registered and then one or more of the following entries

$lang['Anonymous_Users'] = "<b>anonyme User</b>";
$lang['Registered_Users'] = "<b>registrierte User</b>";
$lang['Users_granted_access'] = "<b>User mit speziellen Rechten</b>";
$lang['Moderators'] = "<b>Moderatoren</b>";
$lang['Administrators'] = "<b>Administratoren</b>";

$lang['can_read'] = " können";
$lang['can_post_announcements'] = " Ankündigungen in";
$lang['can_post_Sticky_topics'] = " Wichtige Themen in";
$lang['can_post_new_topics'] = " neue Themen in";
$lang['can_reply_to_topics'] = " antworten in";
$lang['can_edit_topics'] = " können bearbeitete Beiträge in";
$lang['can_delete_topics'] = " können Themen löschen oder in";

$lang['this_forum'] = " dieses Forum schreiben";


//
// Viewonline
//
$lang['Who_is_online'] = "Wer ist online";
$lang['Reg_users_online'] = "Es sind %d registrierte ";
$lang['Hidden_users_online'] = "und %d versteckte User online";
$lang['Guest_users_online'] = "Es sind %d Gäste online";
$lang['Guest_user_online'] = "Es ist %d Gast online";
$lang['No_users_browsing'] = "Im Moment sind keine User auf breakzone.cc";

$lang['Online_explain'] = "Diese Daten zeigen an, wer in den letzten 5 Minuten online war";

$lang['Forum_Location'] = "Ort des Forums";
$lang['Last_updated'] = "Letztmals aktualisiert";

$lang['Forum_index'] = "Forum-Index";
$lang['Logging_on'] = "Einloggen";
$lang['Posting_message'] = "Nachricht schreiben";
$lang['Searching_forums'] = "Foren durchsuchen";
$lang['Viewing_profile'] = "Profil anzeigen";
$lang['Viewing_online'] = "Anzeigen, wer online ist";
$lang['Viewing_member_list'] = "Mitgliederliste anzeigen";
$lang['Viewing_priv_msgs'] = "Privat-Nachrichten anzeigen";
$lang['Viewing_FAQ'] = "FAQ anzeigen";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Moderator Control Panel";
$lang['Mod_CP_explain'] = "Using the form below you can perform mass moderation operations on this forum. You can lock, unlock, move or delete any number of topics.";

$lang['Select'] = "Auswählen";
$lang['Delete'] = "löschen";
$lang['Move'] = "Verschieben";
$lang['Lock'] = "Sperren";
$lang['Unlock'] = "Entsperren";

$lang['Topics_Removed'] = "Das ausgewählte Thema wurde erfolgreich gelöscht";
$lang['Topics_Locked'] = "Die ausgewählten Themen wurden erfolgreich gelöscht";
$lang['Topics_Moved'] = "Die gewählten Themen wurden verschoben";
$lang['Topics_Unlocked'] = "Die gewählten Themen wurden entsperrt";

$lang['Confirm_delete_topic'] = "Bist du sicher, dass die gewählten Themen entfernt werden sollen?";
$lang['Confirm_lock_topic'] = "Bist du sicher, dass die gewählten Themen gesperrt werden sollen?";
$lang['Confirm_unlock_topic'] = "Bist du sicher, dass die gewählten Themen entsperrt werden sollen?";
$lang['Confirm_move_topic'] = "Bist du sicher, dass die gewählten Themen verschoben werden sollen?";

$lang['Move_to_forum'] = "Verschieben nach";
$lang['Leave_shadow_topic'] = "Shadow Topic im alten Forum lassen";

$lang['Split_Topic'] = "Split Topic Control Panel";
$lang['Split_Topic_explain'] = "Mit den Eingabefeldern unten kannst du ein Thema in mehrere teilen";
$lang['Split_title'] = "Titel des neuen Themas";
$lang['Split_forum'] = "Forum des neuen Themas";
$lang['Split_posts'] = "Gewählte Beiträge teilen";
$lang['Split_after'] = "Von gewähltem Beitrag teilen";
$lang['Topic_split'] = "Das gewählte Thema wurde erfolgreich geteilt";

$lang['Too_many_error'] = "Du hast zu viele Beiträge gewählt. Du kannst nur einen Beitrag auswählen, nahc dem geteilt werden soll!";

$lang['None_selected'] = "Du hast keine Themen ausgewählt, auf die diese Aktion ausgeführt werden soll. Bitte wähle mindestens eins aus.";
$lang['New_forum'] = "Neues Forum";

$lang['This_posts_IP'] = "IP-Adresse für diesen Beitrag";
$lang['Other_IP_this_user'] = "Andere IP-Adressen, von denen der User geschrieben hat";
$lang['Users_this_IP'] = "Beiträge von dieser IP-Adresse";
$lang['IP_info'] = "IP-Information";
$lang['Lookup_IP'] = "IP nachschlagen";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "Alle Zeiten sind %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 Stunden";
$lang['-11'] = "GMT - 11 Stunden";
$lang['-10'] = "HST (Hawaii)";
$lang['-9'] = "GMT - 9 Stunden";
$lang['-8'] = "PST (USA/Kanada)";
$lang['-7'] = "MST (USA/Kanada)";
$lang['-6'] = "CST (USA/Kanada)";
$lang['-5'] = "EST (USA/Kanada)";
$lang['-4'] = "GMT - 4 Stunden";
$lang['-3.5'] = "GMT - 3.5 Stunden";
$lang['-3'] = "GMT - 3 Stunden";
$lang['-2'] = "Mittelatlantisch";
$lang['-1'] = "GMT - 1 Stunde";
$lang['0'] = "GMT";
$lang['1'] = "MESZ (Westeuropa)";
$lang['2'] = "EET (Osteuropa)";
$lang['3'] = "GMT + 3 Stunden";
$lang['3.5'] = "GMT + 3.5 Stunden";
$lang['4'] = "GMT + 4 Stunden";
$lang['4.5'] = "GMT + 4.5 Stunden";
$lang['5'] = "GMT + 5 Stunden";
$lang['5.5'] = "GMT + 5.5 Stunden";
$lang['6'] = "GMT + 6 Stunden";
$lang['7'] = "GMT + 7 Stunden";
$lang['8'] = "WST (Australien)";
$lang['9'] = "GMT + 9 Stunden";
$lang['9.5'] = "CST (Australien)";
$lang['10'] = "EST (Australien)";
$lang['11'] = "GMT + 11 Stunden";
$lang['12'] = "GMT + 12 Stunden";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT -12:00 hours) Eniwetok, Kwajalein";
$lang['tz']['-11'] = "(GMT -11:00 hours) Midway Island, Samoa";
$lang['tz']['10'] = "(GMT -10:00 hours) Hawaii";
$lang['tz']['-9'] = "(GMT -9:00 hours) Alaska";
$lang['tz']['-8'] = "(GMT -8:00 hours) Pacific Time (US &amp; Canada)";
$lang['tz']['-7'] = "(GMT -7:00 hours) Mountain Time (US &amp; Canada)";
$lang['tz']['-6'] = "(GMT -6:00 hours) Central Time (US &amp; Canada), Mexico City";
$lang['tz']['-5'] = "(GMT -5:00 hours) Eastern Time (US &amp; Canada), Bogota, Lima, Quito";
$lang['tz']['-4'] = "(GMT -4:00 hours) Atlantic Time (Canada), Caracas, La Paz";
$lang['tz']['-3.5'] = "(GMT -3:30 hours) Newfoundland";
$lang['tz']['-3'] = "(GMT -3:00 hours) Brazil, Buenos Aires, Georgetown, Falkland Is";
$lang['tz']['-2'] = "(GMT -2:00 hours) Mid-Atlantic, Ascension Is., St. Helena";
$lang['tz']['-1'] = "(GMT -1:00 hours) Azores, Cape Verde Islands";
$lang['tz']['0'] = "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia";
$lang['tz']['1'] = "(GMT +1:00 hours) Berlin, Brussels, Copenhagen, Madrid, Paris, Rome";
$lang['tz']['2'] = "(GMT +2:00 hours) Kaliningrad, South Africa, Warsaw";
$lang['tz']['3'] = "(GMT +3:00 hours) Baghdad, Riyadh, Moscow, Nairobi";
$lang['tz']['3.5'] = "(GMT +3:30 hours) Tehran";
$lang['tz']['4'] = "(GMT +4:00 hours) Abu Dhabi, Baku, Muscat, Tbilisi";
$lang['tz']['4.5'] = "(GMT +4:30 hours) Kabul";
$lang['tz']['5'] = "(GMT +5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent";
$lang['tz']['5.5'] = "(GMT +5:30 hours) Bombay, Calcutta, Madras, New Delhi";
$lang['tz']['6'] = "(GMT +6:00 hours) Almaty, Colombo, Dhaka";
$lang['tz']['7'] = "(GMT +7:00 hours) Bangkok, Hanoi, Jakarta";
$lang['tz']['8'] = "(GMT +8:00 hours) Beijing, Hong Kong, Perth, Singapore, Taipei";
$lang['tz']['9'] = "(GMT +9:00 hours) Osaka, Sapporo, Seoul, Tokyo, Yakutsk";
$lang['tz']['9.5'] = "(GMT +9:30 hours) Adelaide, Darwin";
$lang['tz']['10'] = "(GMT +10:00 hours) Melbourne, Papua New Guinea, Sydney, Vladivostok";
$lang['tz']['11'] = "(GMT +11:00 hours) Magadan, New Caledonia, Solomon Islands";
$lang['tz']['12'] = "(GMT +12:00 hours) Auckland, Wellington, Fiji, Marshall Island";

$lang['days_long'][0] = "Sonntag"; 
$lang['days_long'][1] = "Montag"; 
$lang['days_long'][2] = "Dienstag"; 
$lang['days_long'][3] = "Mittwoch"; 
$lang['days_long'][4] = "Donnerstag"; 
$lang['days_long'][5] = "Freitag"; 
$lang['days_long'][6] = "Samstag"; 
 
$lang['days_short'][0] = "So"; 
$lang['days_short'][1] = "Mo"; 
$lang['days_short'][2] = "Di"; 
$lang['days_short'][3] = "Mi"; 
$lang['days_short'][4] = "Do"; 
$lang['days_short'][5] = "Fr"; 
$lang['days_short'][6] = "Sa"; 
 
$lang['months_long'][0] = "Jänner"; 
$lang['months_long'][1] = "Februar"; 
$lang['months_long'][2] = "März"; 
$lang['months_long'][3] = "April"; 
$lang['months_long'][4] = "Mai"; 
$lang['months_long'][5] = "Juni"; 
$lang['months_long'][6] = "Juli"; 
$lang['months_long'][7] = "August"; 
$lang['months_long'][8] = "September"; 
$lang['months_long'][9] = "Oktober"; 
$lang['months_long'][10] = "November"; 
$lang['months_long'][11] = "Dezember"; 
 
$lang['months_short'][0] = "Jän."; 
$lang['months_short'][1] = "Feb."; 
$lang['months_short'][2] = "März"; 
$lang['months_short'][3] = "Apr."; 
$lang['months_short'][4] = "Mai"; 
$lang['months_short'][5] = "Jun."; 
$lang['months_short'][6] = "Jul."; 
$lang['months_short'][7] = "Aug."; 
$lang['months_short'][8] = "Sep."; 
$lang['months_short'][9] = "Okt."; 
$lang['months_short'][10] = "Nov."; 
$lang['months_short'][11] = "Dez."; 

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "Information";
$lang['Critical_Information'] = "Kritische Information";

$lang['You_been_banned'] = "Du wurdest von diesem Forum verbannt<br />Kontaktiere den Administrator, um mehr Informationen zu erhalten";
$lang['No_topics_post_one'] = "In diesem Forum sind noch keine Beiträge<br />Klick auf <b>Neuen Beitrag schreiben</b>, um das zu ändern";
$lang['Board_disable'] = "Dieses Board ist im Moment nicht verfügbar, versuch es später noch einmal";

$lang['General_Error'] = "Allgemeiner Fehler";
$lang['Critical_Error'] = "Kritischer Fehler";
$lang['An_error_occured'] = "Ein Fehler ist passiert";
$lang['A_critical_error'] = "Ein kritischer Fehler ist passiert";

$lang['Error_login'] = "Du hast einen ungültigen Usernamen eingegeben";

$lang['Not_Moderator'] = "Du bist nicht Moderator dieses Forums";
$lang['Not_Authorised'] = "Nicht berechtigt";

//
// That's all Folks!
// -------------------------------------------------

?>
