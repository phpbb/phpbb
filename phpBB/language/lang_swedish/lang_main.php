<?php
/***************************************************************************
 *                            lang_main.php [English]
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

//  *************************************
//  Original swedish Translation by:
//	
//  Marcus Svensson
//  admin@world-of-war.com
//  http://www.world-of-war.com
//	-------------------------------------
// 	Janåke Rönnblom
//	jan-ake.ronnblom@skeria.skelleftea.se
//	-------------------------------------
//	Bruce
//	bruce@webway.se
//  *************************************

//  *************************************
//  Maintained and kept up-to-date by:
//  
//  Fredrik Poller
//  fredrik.poller@bonetmail.com
//  http://fredrik.bamze.net/
//  *************************************

//setlocale(LC_ALL, "se");
$lang['ENCODING'] = "iso-8859-1";
$lang['DIRECTION'] = "ltr";
$lang['LEFT'] = "left";
$lang['RIGHT'] = "right";
$lang['DATE_FORMAT'] =  "d M Y"; // This should be changed to the default date format for your language, php date() format

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Forum";
$lang['Category'] = "Kategori";
$lang['Topic'] = "Ämne";
$lang['Topics'] = "Ämnen";
$lang['Replies'] = "Svar";
$lang['Views'] = "Visningar";
$lang['Post'] = "Inlägg";
$lang['Posts'] = "Inlägg";
$lang['Posted'] = "Skrivet";
$lang['Username'] = "Användarnamn";
$lang['Password'] = "Lösenord";
$lang['Email'] = "Epost";
$lang['Poster'] = "Avsändare";
$lang['Author'] = "Författare";
$lang['Time'] = "Tid";
$lang['Hours'] = "Timmar";
$lang['Message'] = "Meddelande";

$lang['1_Day'] = "1 Dag";
$lang['7_Days'] = "7 Dagar";
$lang['2_Weeks'] = "2 Veckor";
$lang['1_Month'] = "1 Månad";
$lang['3_Months'] = "3 Månader";
$lang['6_Months'] = "6 Månader";
$lang['1_Year'] = "1 År";

$lang['Go'] = "Gå";
$lang['Jump_to'] = "Hoppa till";
$lang['Submit'] = "Skicka";
$lang['Reset'] = "Återställ";
$lang['Cancel'] = "Avbryt";
$lang['Preview'] = "Förhandsgranska";
$lang['Confirm'] = "Bekräfta";
$lang['Spellcheck'] = "Stavningskontroll";
$lang['Yes'] = "Ja";
$lang['No'] = "Nej";
$lang['Enabled'] = "Aktiverad";
$lang['Disabled'] = "Avaktiverad";
$lang['Error'] = "Fel";

$lang['Next'] = "Nästa";
$lang['Previous'] = "Föregående";
$lang['Goto_page'] = "Gå till sida";
$lang['Joined'] = "Blev Medlem";
$lang['IP_Address'] = "IP-Adress";

$lang['Select_forum'] = "Välj forum";
$lang['View_latest_post'] = "Visa senaste inlägg";
$lang['View_newest_post'] = "Visa nyaste inlägg";
$lang['Page_of'] = "Sida <b>%d</b> av <b>%d</b>"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "ICQ-Nummer";
$lang['AIM'] = "AIM-Adress";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

// $lang['Forum_Index'] = "%s Forumindex";  // eg. sitename Forum Index, %s can be removed if you prefer
$lang['Forum_Index'] = "Forumindex";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "Skapa nytt inlägg";
$lang['Reply_to_topic'] = "Svara på inlägget";
$lang['Reply_with_quote'] = "Citera";

$lang['Click_return_topic'] = "Klicka %sHär%s för att återvända till ämnet"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "Klicka %sHär%s för att försöka igen";
$lang['Click_return_forum'] = "Klicka %sHär%s för att återvända till forumet";
$lang['Click_view_message'] = "Klicka %sHär%s för att visa ditt meddelande";
$lang['Click_return_modcp'] = "Klicka %sHär%s för att återvända till moderatorkontollpanelen";
$lang['Click_return_group'] = "Klicka %sHär%s för att återvända till gruppinformationen";

$lang['Admin_panel'] = "Administrationspanel";

$lang['Board_disable'] = "Det här forumet är tyvärr otillgängligt för tillfället, var vänlig försök senare.";


//
// Global Header strings
//
$lang['Registered_users'] = "Registrerade användare:";
$lang['Browsing_forum'] = "Användare som är på forumet just nu:";
$lang['Online_users_zero_total'] = "Det är totalt <b>0</b> användare online :: "; 
$lang['Online_users_total'] = "Det är totalt <b>%d</b> användare online :: ";
$lang['Online_user_total'] = "Det är totalt <b>%d</b> användare online :: ";
$lang['Reg_users_zero_total'] = "0 Rregistrerade, "; 
$lang['Reg_users_total'] = "%d registrerade, ";
$lang['Reg_user_total'] = "%d registrerad, ";
$lang['Hidden_users_zero_total'] = "0 dolda och "; 
$lang['Hidden_users_total'] = "%d dolda and ";
$lang['Hidden_user_total'] = "%d dold and ";
$lang['Guest_users_zero_total'] = "0 gäster";
$lang['Guest_users_total'] = "%d gäster";
$lang['Guest_user_total'] = "%d gäst";
$lang['Record_online_users'] = "Flest användare samtidigt var <b>%s</b> den %s"; // first %s = number of users, second %s is the date. 

$lang['Admin_online_color'] = "%sAdministratör%s";
$lang['Mod_online_color'] = "%sModerator%s";

$lang['You_last_visit'] = "Du gjorde ditt senaste besök %s"; // %s replaced by date/time
$lang['Current_time'] = "Lokal tid (för forumet) %s"; // %s replaced by time

$lang['Search_new'] = "Visa nya inlägg sedan ditt senaste besök";
$lang['Search_your_posts'] = "Visa dina inlägg";
$lang['Search_unanswered'] = "Visa obesvarade huvudinlägg";

$lang['Register'] = "Registrera";
$lang['Profile'] = "Profil";
$lang['Edit_profile'] = "Ändra dina inställningar";
$lang['Search'] = "Sök";
$lang['Memberlist'] = "Användarlista";
$lang['FAQ'] = "FAQ";
$lang['BBCode_guide'] = "BBCode-guide";
$lang['Usergroups'] = "Användargrupper";
$lang['Last_Post'] = "Senaste inlägg";
$lang['Moderator'] = "Moderator";
$lang['Moderators'] = "Moderatorer";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "Våra användare har skrivit totalt <b>0</b> inlägg"; // Number of posts 
$lang['Posted_article_total'] = "Våra användare har skrivit totalt <b>%d</b> inlägg"; // Number of posts
$lang['Posted_articles_total'] = "Våra användare har skrivit totalt <b>%d</b> inlägg"; // Number of posts
$lang['Registered_users_zero_total'] = "Vi har <b>0</b> registrerade användare"; // # registered users 
$lang['Registered_user_total'] = "Vi har <b>%d</b> registrerad användare"; // # registered users
$lang['Registered_users_total'] = "Vi har <b>%d</b> registrerade användare"; // # registered users
$lang['Newest_user'] = "Den senast registrerade användaren är <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "Inga nya inlägg sedan ditt senaste besök";
$lang['No_new_posts'] = "Inga nya inlägg";
$lang['New_posts'] = "Nya inlägg";
$lang['New_post'] = "Nya inlägg";
$lang['No_new_posts_hot'] = "Inga nya inlägg [ Populär ]";
$lang['New_posts_hot'] = "Nya inlägg [ Populär ]";
$lang['No_new_posts_locked'] = "Inga nya inlägg [ Låst ]";
$lang['New_posts_locked'] = "Nya inlägg [ Låst ]";
$lang['Forum_is_locked'] = "Forumet är låst";


//
// Login
//
$lang['Enter_password'] = "Skriv ditt användarnamn och lösenord för att logga in";
$lang['Login'] = "Logga in";
$lang['Logout'] = "Logga ut";

$lang['Forgotten_password'] = "Jag har glömt mitt lösenord";

$lang['Log_me_in'] = "Logga in mig automatiskt";

$lang['Error_login'] = "Du har skrivit in antingen ett felaktigt eller inaktivt användarnamn eller ett felaktigt lösenord";


//
// Index page
//
$lang['Index'] = "Index";
$lang['No_Posts'] = "Inga inlägg";
$lang['No_forums'] = "Inga forum är skapade än";

$lang['Private_Message'] = "Privata Meddelanden";
$lang['Private_Messages'] = "Privata Meddelanden";
$lang['Who_is_Online'] = "Vem är online?";

$lang['Mark_all_forums'] = "Markera alla forum som lästa";
$lang['Forums_marked_read'] = "Alla forum har nu blivit markerade som lästa";


//
// Viewforum
//
$lang['View_forum'] = "Visa forum";

$lang['Forum_not_exist'] = "Det forum som du valt finns inte";
$lang['Reached_on_error'] = "Du har nått den här sidan på fel sätt";

$lang['Display_topics'] = "Visa tidigare ämnen";
$lang['All_Topics'] = "Alla ämnen";

$lang['Topic_Announcement'] = "<b>Viktigt meddelande:</b>";
$lang['Topic_Sticky'] = "<b>Klistrad:</b>";
$lang['Topic_Moved'] = "<b>Flyttad:</b>";
$lang['Topic_Poll'] = "<b>Omröstning:</b>";

$lang['Mark_all_topics'] = "Markera alla ämnen som lästa";
$lang['Topics_marked_read'] = "Alla ämnen i det här forumet har nu blivit markerade som lästa";

$lang['Rules_post_can'] = "Du <b>kan</b> skapa nya inlägg i det här forumet";
$lang['Rules_post_cannot'] = "Du <b>kan inte</b> skapa nya inlägg i det här forumet";
$lang['Rules_reply_can'] = "Du <b>kan</b> svara på inlägg i det här forumet";
$lang['Rules_reply_cannot'] = "Du <b>kan inte</b> svara på inlägg i det här forumet";
$lang['Rules_edit_can'] = "Du <b>kan</b> ändra dina inlägg i det här forumet";
$lang['Rules_edit_cannot'] = "Du <b>kan inte</b> ändra dina inlägg i det här forumet";
$lang['Rules_delete_can'] = "Du <b>kan</b> ta bort dina inlägg i det här forumet";
$lang['Rules_delete_cannot'] = "Du <b>kan inte</b> ta bort dina inlägg i det här forumet";
$lang['Rules_vote_can'] = "Du <b>kan</b> rösta i det här forumet";
$lang['Rules_vote_cannot'] = "Du <b>kan inte</b> rösta i det här forumet";
$lang['Rules_moderate'] = "Du <b>är</b> %smoderator i det här forumet%s"; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = "Det finns inga ämnen i det här forumet<br />Klicka på<b>Skapa Nytt Inlägg</b> länken på denna sidan för att skapa ett";


//
// Viewtopic
//
$lang['View_topic'] = "Visa ämne";

$lang['Guest'] = 'Gäst';
$lang['Post_subject'] = "Ämne";
$lang['View_next_topic'] = "Visa nästa ämne";
$lang['View_previous_topic'] = "Visa föregående ämne";
$lang['Submit_vote'] = "Skicka in röst";
$lang['View_results'] = "Visa resultat";

$lang['No_newer_topics'] = "Det finns inga nyare ämnen i det här forumet";
$lang['No_older_topics'] = "Det finns inga äldre ämnen i det här forumet";
$lang['Topic_post_not_exist'] = "Det sökta inlägget existerar inte";
$lang['No_posts_topic'] = "Det finns inga svar till det här ämnet";

$lang['Display_posts'] = "Visa tidigare inlägg";
$lang['All_Posts'] = "Alla inlägg";
$lang['Newest_First'] = "Nyaste först";
$lang['Oldest_First'] = "Äldsta först";

$lang['Back_to_top'] = "Till överst på sidan";

$lang['Read_profile'] = "Visa användar profil"; 
$lang['Send_email'] = "Skicka email till användaren";
$lang['Visit_website'] = "Besök användarens hemsida";
$lang['ICQ_status'] = "ICQ-status";
$lang['Edit_delete_post'] = "Ändra/Ta bort det här inlägget";
$lang['View_IP'] = "Visa författarens IP";
$lang['Delete_post'] = "Ta bort det här inlägget";

$lang['wrote'] = "skrev"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Citat"; // comes before bbcode quote output.
$lang['Code'] = "Kod"; // comes before bbcode code output.

$lang['Edited_time_total'] = "Senast ändrad av %s den %s, ändrad totalt %d gång"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "Senast ändrad av %s den %s, ändrad totalt %d gånger"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Lås det här ämnet";
$lang['Unlock_topic'] = "Lås upp det här ämnet";
$lang['Move_topic'] = "Flytta det här ämnet";
$lang['Delete_topic'] = "Ta bort det här ämnet";
$lang['Split_topic'] = "Dela det här ämnet";

$lang['Stop_watching_topic'] = "Sluta bevaka det här ämnet";
$lang['Start_watching_topic'] = "Bevaka det här ämnet för svar";
$lang['No_longer_watching'] = "Du bevakar inte längre det här ämnet";
$lang['You_are_watching'] = "Du bevakar nu det här ämnet";

$lang['Total_votes'] = "Totalt antal röster";

//
// Posting/Replying (Not private messaging!)
//

$lang['Already_voted'] = 'Du har redan deltagit i den här omröstningen'; 
$lang['No_vote_option'] = 'Du måste markera ett alternativ när du röstar'; 

$lang['Message_body'] = "Meddelande";
$lang['Topic_review'] = "Ämnes historia";

$lang['No_post_mode'] = "Inget postningsval specificerat"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = "Skapa nytt ämne";
$lang['Post_a_reply'] = "Svara på ämne";
$lang['Post_topic_as'] = "Skapa inlägg som";
$lang['Edit_Post'] = "Ändra inlägg";
$lang['Options'] = "Inställnignar";

$lang['Post_Announcement'] = "Viktigt meddelande";
$lang['Post_Sticky'] = "Klistrad";
$lang['Post_Normal'] = "Normal";

$lang['Confirm_delete'] = "Är du säker på att du vill ta bort det här inlägget?";
$lang['Confirm_delete_poll'] = "Är du säker på att du vill ta bort den här omröstnignen?";

$lang['Flood_Error'] = "Du måste vänta lite innan du kan posta ett nytt inlägg";
$lang['Empty_subject'] = "Du måste skriva en rubrik om du skapar ett nytt inlägg";
$lang['Empty_message'] = "Du måste skriva ett meddelande när du postar";
$lang['Forum_locked'] = "Det här forumet är låst så du kan varken skapa, svara på eller ändra inlägg";
$lang['Topic_locked'] = "Det här inlägget är låst så du kan varken svara på eller ändra det";
$lang['No_post_id'] = "Du måste välja ett inlägg att ändra";
$lang['No_topic_id'] = "Du måste välja ett inlägg att svara på";
$lang['No_valid_mode'] = "Du kan bara skapa, svara på eller ändra inlägg, gå tillbaka och försök igen";
$lang['No_such_post'] = "Posten du letade efter finns inte, gå tillbaka och försök igen";
$lang['Edit_own_posts'] = "Du kan bara ändra dina egna poster";
$lang['Delete_own_posts'] = "Du kan bara ta bort dina egna poster";
$lang['Cannot_delete_replied'] = "Du inte ta bort inlägg som fått svar";
$lang['Cannot_delete_poll'] = "Du kan tyvärr inte ta bort en aktiv omröstning";
$lang['Empty_poll_title'] = "Du måste skriva in ett namn på omröstningen";
$lang['To_few_poll_options'] = "Du måste lägga till minst 2 val i omröstningen";
$lang['To_many_poll_options'] = "Du försökte lägga till för många val i omröstningen";
$lang['Post_has_no_poll'] = "Detta inlägget har ingen omröstning";

$lang['Add_poll'] = "Lägg till omröstning";
$lang['Add_poll_explain'] = "Om du inte vill lägga till någon omröstning till ditt inlägg, lämna fälten blanka";
$lang['Poll_question'] = "Omröstningsfråga";
$lang['Poll_option'] = "Omröstningsval";
$lang['Add_option'] = "Lägg till val";
$lang['Update'] = "Uppdatera";
$lang['Delete'] = "Ta bort";
$lang['Poll_for'] = "Antal dagar att hålla igång omröstningen i";
$lang['Days'] = "Dagar"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ Sätt 0 för att skapa en omröstning som aldrig slutar  ]";
$lang['Delete_poll'] = "Ta bort omröstning";

$lang['Disable_HTML_post'] = "Avaktivera HTML i den här posten";
$lang['Disable_BBCode_post'] = "Avaktivera BBCode i den här posten";
$lang['Disable_Smilies_post'] = "Avaktivera Smilies i den här posten";

$lang['HTML_is_ON'] = "HTML är <u>PÅ</u>";
$lang['HTML_is_OFF'] = "HTML är <u>AV</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s är <u>PÅ</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s är <u>AV</u>";
$lang['Smilies_are_ON'] = "Smilies är <u>PÅ</u>";
$lang['Smilies_are_OFF'] = "Smilies är <u>AV</u>";

$lang['Attach_signature'] = "Använd signatur (signaturen kan ändras i profilen)";
$lang['Notify'] = "Kontakta mig vid svar";
$lang['Delete_post'] = "Ta bort den här posten";

$lang['Stored'] = "Ditt meddelande har sparats";
$lang['Deleted'] = "Ditt meddelnade har tagits bort";
$lang['Poll_delete'] = "Din omröstning har tagits bort";
$lang['Vote_cast'] = "Din röst har räknats";

$lang['Topic_reply_notification'] = "Meddelande om svar på inlägg";

$lang['bbcode_b_help'] = "Fetstilad text: [b]text[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Kursiv text: [i]text[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Understruken text: [u]text[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Citerad text: [quote]text[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Visning av kod: [code]code[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Lista: [list]text[/list] (alt+l)";
$lang['bbcode_o_help'] = "Ordnad lista: [list=]text[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Lägg till bild: [img]http://image_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "Lägg till länk: [url]http://url[/url] eller [url=http://url]URL text[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Stäng alla öppna bbCode taggar";
$lang['bbcode_s_help'] = "Tecken färg: [color=red]text[/color]  Tips: du kan även använda color=#FF0000";
$lang['bbcode_f_help'] = "Tecken storlek: [size=x-small]liten text[/size]";

$lang['Emoticons'] = "Smilies";
$lang['More_emoticons'] = "Visa fler Smilies";

$lang['Font_color'] = "Teckenfärg";
$lang['color_default'] = "Standard";
$lang['color_dark_red'] = "Mörkröd";
$lang['color_red'] = "Röd";
$lang['color_orange'] = "Orange";
$lang['color_brown'] = "Brun";
$lang['color_yellow'] = "Gul";
$lang['color_green'] = "Grön";
$lang['color_olive'] = "Oliv";
$lang['color_cyan'] = "Cyan";
$lang['color_blue'] = "Blå";
$lang['color_dark_blue'] = "Mörkblå";
$lang['color_indigo'] = "Lila";
$lang['color_violet'] = "Rosa";
$lang['color_white'] = "Vit";
$lang['color_black'] = "Svart";

$lang['Font_size'] = "Teckenstorlek";
$lang['font_tiny'] = "Pytteliten";
$lang['font_small'] = "Liten";
$lang['font_normal'] = "Normal";
$lang['font_large'] = "Stor";
$lang['font_huge'] = "Enorm";

$lang['Close_Tags'] = "Stäng taggar";
$lang['Styles_tip'] = "Tips: Stilar kan snabbt användas på markerad text";


//
// Private Messaging
//

$lang['Read_pm'] = 'Läs meddelande'; 
$lang['Post_new_pm'] = 'Skicka meddelande'; 
$lang['Post_reply_pm'] = 'Svara på meddelande'; 
$lang['Post_quote_pm'] = 'Citera meddelande'; 
$lang['Edit_pm'] = 'Ändra meddelande'; 

$lang['Unread_message'] = 'Oläst meddelande'; 
$lang['Read_message'] = 'Läst meddelande'; 


$lang['Private_Messaging'] = "Privata Meddelanden";

$lang['Login_check_pm'] = "Logga in för att se dina privata meddelanden";
$lang['New_pms'] = "Du har %d nya privata meddelanden"; // You have 2 new messages
$lang['New_pm'] = "Du har %d nytt privat meddelande"; // You have 1 new message
$lang['No_new_pm'] = "Du har inga nya privata meddelanden";
$lang['Unread_pms'] = "Du har %d olästa privata meddelanden";
$lang['Unread_pm'] = "Du har %d oläst privat meddelande";
$lang['No_unread_pm'] = "Du har inga olästa privata meddelanden";
$lang['You_new_pm'] = "Ett nytt privat meddelande väntar på dig i din inkorg";
$lang['You_new_pms'] = "Nya privata meddelanden väntar på dig i din inkorg";
$lang['You_no_new_pm'] = "Inga nya privata meddelanden väntar på dig";

$lang['Inbox'] = "Inkorg";
$lang['Outbox'] = "Utkorg";
$lang['Savebox'] = "Sparat";
$lang['Sentbox'] = "Skickat";
$lang['Flag'] = "Flagga";
$lang['Subject'] = "Ämne";
$lang['From'] = "Från";
$lang['To'] = "Till";
$lang['Date'] = "Datum";
$lang['Mark'] = "Markera";
$lang['Sent'] = "Skickad";
$lang['Saved'] = "Sparat";
$lang['Delete_marked'] = "Ta bort markerade";
$lang['Delete_all'] = "Ta bort alla";
$lang['Save_marked'] = "Spara markerade"; 
$lang['Save_message'] = "Spara meddelande";
$lang['Delete_message'] = "Ta bort meddelande";

$lang['Display_messages'] = "Visa tidigare meddelanden"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "Alla meddelanden";

$lang['No_messages_folder'] = "Du har inga meddelanden i denna mappen";

$lang['PM_disabled'] = "Privata meddelanden har blivit avaktiverade på det här forumet";
$lang['Cannot_send_privmsg'] = "Administratören har tyvärr hindrat dig från att skicka privata meddelanden";
$lang['No_to_user'] = "Du måste skriva in ett användarnamn för att skicka meddelandet";
$lang['No_such_user'] = "Användaren finns inte, var god försök igen";

$lang['Disable_HTML_pm'] = "Avaktivera HTML i det här meddelandet";
$lang['Disable_BBCode_pm'] = "Avaktivera BBCode i det här meddelandet";
$lang['Disable_Smilies_pm'] = "Avaktivera Smilies i det här meddelandet";

$lang['Message_sent'] = "Ditt meddelande har blivit skickat";

$lang['Click_return_inbox'] = "Klicka %sHär%s för att återgå till din inkorg";
$lang['Click_return_index'] = "Klicka %sHär%s för att återgå till index";

$lang['Send_a_new_message'] = "Skicka ett nytt privat meddelande";
$lang['Send_a_reply'] = "Svara på ett privat meddelande";
$lang['Edit_message'] = "Ändra ett privat meddelande";

$lang['Notification_subject'] = "Du har fått ett privat meddelande";

$lang['Find_username'] = "Sök efter användarnamn";
$lang['Find'] = "Sök";
$lang['No_match'] = "Inga matchande träffar hittades";

$lang['No_post_id'] = "Inget inläggs-ID specificerat";
$lang['No_such_folder'] = "Den katalogen finns inte";
$lang['No_folder'] = "Ingen katalog specificerad";

$lang['Mark_all'] = "Markera alla";
$lang['Unmark_all'] = "Avmarkera alla";

$lang['Confirm_delete_pm'] = "Är du säker på att du vill ta bort det här meddelandet?";
$lang['Confirm_delete_pms'] = "Är du säker på att du vill ta bort de här meddelandena?";

$lang['Inbox_size'] = "Din Inkorg mapp är %d%% full"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Din Utkorg mapp är %d%% full"; 
$lang['Savebox_size'] = "Din Sparat mapp är %d%% full"; 

$lang['Click_view_privmsg'] = "Klicka %sHär%s för att gå till din Inkorg";


//
// Profiles/Registration
//
$lang['Already_activated'] = "Ditt konto har redan aktiverats";

$lang['Viewing_user_profile'] = "Visar profil :: %s"; // %s is username 
$lang['About_user'] = "Allt om %s"; // %s is username

$lang['Preferences'] = "Inställningar";
$lang['Items_required'] = "Allt som är markerat med * måste fyllas i om inte annat angivs";
$lang['Registration_info'] = "Registreringsinformation";
$lang['Profile_info'] = "Profilinformation";
$lang['Profile_info_warn'] = "Följande information kommer att vara synligt för andra";
$lang['Avatar_panel'] = "Avatarkontrollpanel";
$lang['Avatar_gallery'] = "Avatargalleri";

$lang['Website'] = "Hemsida";
$lang['Location'] = "Från";
$lang['Contact'] = "Kontakta";
$lang['Email_address'] = "Emailadress";
$lang['Email'] = "Email";
$lang['Send_private_message'] = "Skicka privat meddelande";
$lang['Hidden_email'] = "[ Dold ]";
$lang['Search_user_posts'] = "Sök efter inlägg av den här personen";
$lang['Interests'] = "Intressen";
$lang['Occupation'] = "Yrke/sysselsättning"; 
$lang['Poster_rank'] = "Rank";

$lang['Total_posts'] = "Totalt antal inlägg";
$lang['User_post_pct_stats'] = "%.2f%% av totala"; // 1.25% of total
$lang['User_post_day_stats'] = "%.2f poster per dag"; // 1.5 posts per day
$lang['Search_user_posts'] = "Hitta alla inlägg av %s"; // Find all posts by username

$lang['No_user_id_specified'] = "Den användaren finns tyvärr inte";
$lang['Wrong_Profile'] = "Du kan inte ändra en profil som inte är din egen.";

$lang['Only_one_avatar'] = "Endast en typ av avatar kan väljas";
$lang['File_no_data'] = "Filen på adressen du angav innehöll ingen data";
$lang['No_connection_URL'] = "En anslutning kunde ej göras till adressen du angav";
$lang['Incomplete_URL'] = "Adressen du angav är inte komplett";
$lang['Wrong_remote_avatar_format'] = "Adressen till avataren du angav är inte giltig";
$lang['No_send_account_inactive'] = "Tyvärr kan inte ditt lösenord skickas eftersom att ditt konto är inaktivt. Kontakta forum administratören för mer information";

$lang['Always_smile'] = "Aktivera alltid Smilies";
$lang['Always_html'] = "Tillåt alltid HTML";
$lang['Always_bbcode'] = "Tillåt alltid BBCode";
$lang['Always_add_sig'] = "Inkludera alltid min signatur";
$lang['Always_notify'] = "Kontakta alltid mig vid svar";
$lang['Always_notify_explain'] = "Skickar ett email till dig när någon svarar på ett huvudinlägg där du har skrivit/svarat. Det här kan ändras när du skapar ett inlägg.";

$lang['Board_style'] = "Forumstil";
$lang['Board_lang'] = "Forumspråk";
$lang['No_themes'] = "Inga teman i databasen";
$lang['Timezone'] = "Tidszon";
$lang['Date_format'] = "Datumformat";
$lang['Date_format_explain'] = "Det här syntaxet är identiskt med PHP-funktionen <a href=\"http://www.php.net/date\" target=\"_other\">date()</a>";
$lang['Signature'] = "Signatur";
$lang['Signature_explain'] = "Det här är ett stycke text som kan läggas till i inlägg du skapar. Det finns en gräns på %d tecken.";
$lang['Public_view_email'] = "Visa alltid min email-adress";

$lang['Current_password'] = "Nuvarande lösenord";
$lang['New_password'] = "Nytt lösenord";
$lang['Confirm_password'] = "Bekräfta nytt lösenord";
$lang['password_if_changed'] = "Du behöver bara fylla i ett lösenord om du skall ändra det";
$lang['password_confirm_if_changed'] = "Du behöver bara bekräfta ditt lösenord om du skall ändra det";

$lang['Avatar'] = "Avatar";
$lang['Avatar_explain'] = "Visar en liten bild precis under dina detaljer i dina inlägg. Endast en bild kan visas åt gången, bredden får inte vara större än %d pixlar, höjden får inte vara större än %d pixlar och filstoleken får inte vara större än %dkB."; 
$lang['Upload_Avatar_file'] = "Ladda upp en Avatar från din dator";
$lang['Upload_Avatar_URL'] = "Ladda upp en Avatar från en webadress";
$lang['Upload_Avatar_URL_explain'] = "Skriv in webadressen där Avatar bilden finns, den kommer att kopieras till det här forumet.";
$lang['Pick_local_Avatar'] = "Välj en Avatar från galleriet";
$lang['Link_remote_Avatar'] = "Länka till en Avatar på en annan sida";
$lang['Link_remote_Avatar_explain'] = "Skriv in webadressen där Avatarbilden som du vill länka till finns";
$lang['Avatar_URL'] = "Adress till Avatarbild";
$lang['Select_from_gallery'] = "Välj en Avatar från galleriet";
$lang['View_avatar_gallery'] = "Visa galleriet";

$lang['Select_avatar'] = "Välj avatar";
$lang['Return_profile'] = "Avbryt";
$lang['Select_category'] = "Välj kategori";

$lang['Delete_Image'] = "Ta bort Bild";
$lang['Current_Image'] = "Nuvarande Bild";

$lang['Notify_on_privmsg'] = "Kontakta mig vid nytt privat meddelande";
$lang['Popup_on_privmsg'] = "Poppa upp fönster vid nytt privat meddelande"; 
$lang['Popup_on_privmsg_explain'] = "Vissa forumtemplates kan poppa upp ett fönster som meddelar dig om att du fått ett nytt privat meddelande."; 
$lang['Hide_user'] = "Visa inte om jag är online";

$lang['Profile_updated'] = "Din profil har blivit uppdaterad";
$lang['Profile_updated_inactive'] = "Din profil har uppdaterats, men eftersom att du ändrade viktiga detaljer har ditt konto nu avaktiverats. Kontollera din email för att få reda på hur du skall gå till väga för att aktivera ditt konto igen. Eller om adminaktivering är nödvändig, var god vänta tills administratören aktiverat ditt konto igen.";

$lang['Password_mismatch'] = "Lösenorden du skrev in matchade inte";
$lang['Current_password_mismatch'] = "Ditt nuvarande lösenord matchade inte med vad du skrev in";
$lang['Password_long'] = "Ditt lösenord får inte vara längre än 32 tecken";
$lang['Username_taken'] = "Tyvärr var det här användarnamnet redan upptaget";
$lang['Username_invalid'] = "Ditt användarnamn innehöll ett ogiltigt tecken som t.ex. \"";
$lang['Username_disallowed'] = "Tyvärr är inte det här användarnamnet godkänt";
$lang['Email_taken'] = "Den email-adressen är redan registrerad hos oss";
$lang['Email_banned'] = "Tyvärr är den email-adressen avstängd (bannad)";
$lang['Email_invalid'] = "Email-adressen är felaktig";
$lang['Signature_too_long'] = "Din signatur är för lång";
$lang['Fields_empty'] = "Du måste fylla i alla fält som är markerade med *";
$lang['Avatar_filetype'] = "Avatarfiltypen påste vara .jpg, .gif eller .png";
$lang['Avatar_filesize'] = "Avatarfilstorleken måste vara mindre än %d kB"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "Avataren måste vara mindre än %d pixlar bred och %d pixlar hög"; 

$lang['Welcome_subject'] = "Välkommen till %s"; // Welcome to my.com forums
$lang['New_account_subject'] = "Nytt användarkonto";
$lang['Account_activated_subject'] = "Konto Aktiverat";

$lang['Account_added'] = "Tack för att du registrerade dig, ditt konto har nu blivit skapat. Du kan nu logga in med ditt användarnamn och lösenord";
$lang['Account_inactive'] = "Ditt konto har skapats. Men, det här forumet kräver konto aktivering, en aktiveringskod har skickats till email-adressen du angav. Var god kontrollera din email för mer information";
$lang['Account_inactive_admin'] = "Ditt konto har skapats. Men, det här forumet kräver konto aktivering av administratören. Ett email har skickats till dem och du kommer att bli informerad om när ditt konto blivit aktiverat";
$lang['Account_active'] = "Ditt konto har nu aktiverats. Tack för att du registrerade dig";
$lang['Account_active_admin'] = "Kontot har nu aktiverats";
$lang['Reactivate'] = "Återaktivera ditt konto!";
$lang['COPPA'] = "Ditt konto har skapats men måste bli godkänt, kontrollera din email för mer information.";

$lang['Registration'] = "Registreringsavtal";
$lang['Reg_agreement'] = "Fastän administratörer och moderatorer på det här forumet försöker att ta bort eller ändra allt störande eller stötande material så fort som möjligt, är det omöjligt att gå igenom alla meddelanden. Vi vill därför meddela dig om att alla inlägg som skrivits på de här forumet uttrycker vad författaren tänker och tycker, och administratörer eller moderatorer skall inte stå till ansvar för det (förrutom då för de meddelanden som de själva skrivit).<br /><br /> Du går med på att inte posta något störande, stötande, vulgärt, hatiskt, hotande, sexuellt anspelande eller något annat material som kan tänkas bryta mot någon tillämplig lag. Om du bryter mot det här kan det leda till att du blir permanent avstängd från forumen (och din Internetleverantör blir kontaktad). IP-adressen av alla meddelanden sparas för att stärka de här vilkoren. Du går med på att webmaster, administratör och moderatorer har rätt att ta bort, ändra, flytta eller stänga vilka inlägg som helst när som helst. Som en användare går du med på att all information som du skrivit in sparas i databasen. Den informationen kommer inte att distruberas till någon 3:e part utan ditt samtycke. Webmastern, administratören eller moderatorer kan inte hållas ansvariga vid hackningsförsök som kan leda till att data stjäls. <br /><br />Det här forumssystemet använder cookies till att spara information på din dator. De här cookiesen innehåller inte något av den information du skrivit in ovan, utan de används endast för att göra ditt användande av forumet bättre. Email-adressen är använd bara för att aktivera din registrering (och för att skicka nytt lösenord till dig om du råkar glömma det).<br /><br /> Genom att klicka på Registrera nedan går du med på att bindas till de här vilkoren.";

$lang['Agree_under_13'] = "Jag accepterar villkoren och är under 13 år.";
$lang['Agree_over_13'] = "Registrera";
// $lang['Agree_over_13'] = "Jag accepterar villkoren och är över 13 år.";
$lang['Agree_not'] = "Index";

$lang['Wrong_activation'] = "Aktiveringskoden du angav matchar inte med den i databasen";
$lang['Send_password'] = "Skicka ett nytt lösenord till mig"; 
$lang['Password_updated'] = "Ett nytt lösenord har skapats, kontrollera din email för mer information om hur du skall aktivera det";
$lang['No_email_match'] = "Email-adressen som du angav matchar inte med den som är listad med det användarnamnet";
$lang['New_password_activation'] = "Lösenordsaktivering";
$lang['Password_activated'] = "Ditt konto har återaktiverats. För att logga in använd lösenordet som du hittar i emailet du fick";

$lang['Send_email_msg'] = "Skicka ett email";
$lang['No_user_specified'] = "Ingen användare specificerad";
$lang['User_prevent_email'] = "Den här användaren vill inte ta emot email. Försök att skicka ett privat meddelande istället";
$lang['User_not_exist'] = "Den användaren finns inte";
$lang['CC_email'] = "Skicka en kopia av det här emailet till dig själv";
$lang['Email_message_desc'] = "Det här meddelandet kommer att skickas som ren text, inkludera inte någon HTML eller BBCode. Svarsadressen för det här meddelandet kommer att vara din email-adress.";
$lang['Flood_email_limit'] = "Du kan inte skicka ett till email just nu, försök igen senare";
$lang['Recipient'] = "Mottagare";
$lang['Email_sent'] = "Emailet har skickats";
$lang['Send_email'] = "Skicka email";
$lang['Empty_subject_email'] = "Du måste skriva in ett ämne på emailet";
$lang['Empty_message_email'] = "Du måste skriva in ett meddelande som skall bli skickat";


//
// Memberslist
//
$lang['Select_sort_method'] = "Välj sorteringssätt";
$lang['Sort'] = "Sortera";
$lang['Sort_Top_Ten'] = "Top Tio Författare";
$lang['Sort_Joined'] = "Blev Medlem";
$lang['Sort_Username'] = "Användarnamn";
$lang['Sort_Location'] = "Område";
$lang['Sort_Posts'] = "Antal Inlägg";
$lang['Sort_Email'] = "Email";
$lang['Sort_Website'] = "Hemsida";
$lang['Sort_Ascending'] = "Stigande ordning";
$lang['Sort_Descending'] = "Fallande ordning";
$lang['Order'] = "Ordning";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Gruppkontrollpanel";
$lang['Group_member_details'] = "Gruppmedlemskapsdetaljer";
$lang['Group_member_join'] = "Gå med i en Grupp";

$lang['Group_Information'] = "Gruppinformation";
$lang['Group_name'] = "Gruppnamn";
$lang['Group_description'] = "Gruppbeskrivning";
$lang['Group_membership'] = "Gruppmedlemskap";
$lang['Group_Members'] = "Gruppmedlemmar";
$lang['Group_Moderator'] = "Gruppmoderator";
$lang['Pending_members'] = "Medlemskapsförfrågningar";

$lang['Group_type'] = "Grupptyp";
$lang['Group_open'] = "Öppen grupp";
$lang['Group_closed'] = "Stängd grupp";
$lang['Group_hidden'] = "Dold grupp";

$lang['Current_memberships'] = "Nuvarande medlemskap";
$lang['Non_member_groups'] = "Icke-medlemsgrupper";
$lang['Memberships_pending'] = "Medlemskapsförfrågningar";

$lang['No_groups_exist'] = "Det finns inga Grupper";
$lang['Group_not_exist'] = "Den användargruppen finns inte";

$lang['Join_group'] = "Gå med i Grupp";
$lang['No_group_members'] = "Den här gruppen har inga medlemmar";
$lang['Group_hidden_members'] = "Den här gruppen är dold, du kan inte se dess medlemmar";
$lang['No_pending_group_members'] = "Den här gruppen har inga medlemskaps förfrågningar";
$lang["Group_joined"] = "Du har nu ansökt om att bli medlem i den här gruppen<br />Du kommer att bli meddelad om du blir godkänd som medlem eller inte av gruppmoderatorn";
$lang['Group_request'] = "En förfrågan att om att bli medlem i din grupp har gjorts";
$lang['Group_approved'] = "Din förfrågan har blivit godkänd";
$lang['Group_added'] = "Du har lagts till i den här användargruppen"; 
$lang['Already_member_group'] = "Du är redan medlem av den här gruppen";
$lang['User_is_member_group'] = "Användaren är redan medlem i den här gruppen";
$lang['Group_type_updated'] = "Uppdaterade grupptypen";

$lang['Could_not_add_user'] = "Användaren du valde existerar inte";
$lang['Could_not_anon_user'] = "Du kan inte göra en anonym till medlem i gruppen";

$lang['Confirm_unsub'] = "Är du säker på att du vill lämna den här gruppen?";
$lang['Confirm_unsub_pending'] = "Ditt medlemskap i den här gruppen har inte än blivit godkänt, är du säker på att du vill avbryta ansökan?";

$lang['Unsub_success'] = "Ditt medlemskap i den här gruppen har avbrutits.";

$lang['Approve_selected'] = "Godkänn valda";
$lang['Deny_selected'] = "Avslå valda";
$lang['Not_logged_in'] = "Du måste logga in för att gå med i en grupp.";
$lang['Remove_selected'] = "Ta bort valda";
$lang['Add_member'] = "Lägg till medlem";
$lang['Not_group_moderator'] = "Du är inte moderator av den här gruppen och därför kan du inte göra det här.";

$lang['Login_to_join'] = "Logga in för att kontollera gruppmedlemskap";
$lang['This_open_group'] = "Det här är en öppen grupp, klicka för att begära medlemskap";
$lang['This_closed_group'] = "Det här är en stängd grupp, inga fler medlemmar accepteras";
$lang['This_hidden_group'] = "Det här är en dold grupp, fler medlemmar kan inte läggas till automatiskt";
$lang['Member_this_group'] = "Du är medlem i den här gruppen";
$lang['Pending_this_group'] = "Ditt medlemskap i den här gruppen är under behandling";
$lang['Are_group_moderator'] = "Du är moderator i den här gruppen";
$lang['None'] = "Inga";

$lang['Subscribe'] = "Ansök om medlemskap";
$lang['Unsubscribe'] = "Avbryt medlemskap";
$lang['View_Information'] = "Visa Information";


//
// Search
//
$lang['Search_query'] = "Sökfråga";
$lang['Search_options'] = "Sökalternativ";

$lang['Search_keywords'] = "Sök efter nyckelord";
$lang['Search_keywords_explain'] = "Du kan använda <u>AND</u> för att bestämma vilka ord som måste finnas i sökresultatet, <u>OR</u> för att bestämma vilka ord som kan finnas i sökresultatet och <u>NOT</u> för att bestämma ord som inte får finnas i sökresultatet. Använd * som \"wildcard\" för ofullständiga ord";
$lang['Search_author'] = "Sök efter författare";
$lang['Search_author_explain'] = "Använd * som \"wildcard\" för ofullständiga ord";

$lang['Search_for_any'] = "Sök efter alla termer eller använd den specificerade frågan";
$lang['Search_for_all'] = "Sök efter alla termer";
$lang['Search_title_msg'] = "Sök i ämnes rubrik och i meddelandetext";
$lang['Search_msg_only'] = "Sök endast i meddelandetext";

$lang['Return_first'] = "Skriv ut första"; // followed by xxx characters in a select box
$lang['characters_posts'] = "tecknen av inlägget";

$lang['Search_previous'] = "Sök tidigare"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "Sortera efter";
$lang['Sort_Time'] = "Tid skapad";
$lang['Sort_Post_Subject'] = "Inläggsrubrik";
$lang['Sort_Topic_Title'] = "Ämnesrubrik";
$lang['Sort_Author'] = "Författare";
$lang['Sort_Forum'] = "Forum";

$lang['Display_results'] = "Visa resultat som";
$lang['All_available'] = "Alla tillgängliga";
$lang['No_searchable_forums'] = "Du har inte tillstånd att söka på något forum på den här sajten";

$lang['No_search_match'] = "Inga ämnen eller inlägg matchade dina sök kriterier";
$lang['Found_search_match'] = "Sökningen hittade %d matchande resultat"; // eg. Search found 1 match
$lang['Found_search_matches'] = "Sökningen hittade %d matchande resultat"; // eg. Search found 24 matches

$lang['Close_window'] = "Stäng fönster";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "Tyvärr kan endast %s skapa viktiga meddelanden i det här forumet";
$lang['Sorry_auth_sticky'] = "Tyvärr kan endast %s skapa klistrade meddelanden i det här forumet"; 
$lang['Sorry_auth_read'] = "Tyvärr kan endast %s läsa ämnen i det här forumet"; 
$lang['Sorry_auth_post'] = "Tyvärr kan endast %s skapa ämnen i det här forumet"; 
$lang['Sorry_auth_reply'] = "Tyvärr kan endast %s skapa inlägg i det här forumet"; 
$lang['Sorry_auth_edit'] = "Tyvärr kan endast %s ändra inlägg i det här forumet"; 
$lang['Sorry_auth_delete'] = "Tyvärr kan endast %s ta bort inlägg från det här forumet"; 
$lang['Sorry_auth_vote'] = "Tyvärr kan endast %s vara med i omröstningar på det här forumet"; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>anonyma användare</b>";
$lang['Auth_Registered_Users'] = "<b>registrerade användare</b>";
$lang['Auth_Users_granted_access'] = "<b>användare beviljade speciell åtkomst</b>";
$lang['Auth_Moderators'] = "<b>moderatorer</b>";
$lang['Auth_Administrators'] = "<b>administratörer</b>";

$lang['Not_Moderator'] = "Du är inte en moderator av det här forumet";
$lang['Not_Authorised'] = "Inte godkänd";

$lang['You_been_banned'] = "Du har blivit avstängd (bannad) från det här forumet<br />Var god kontakta webmasterna eller forumsadministratören för mer information";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "Det är 0 Registrerade användare och "; // There ae 5 Registered and 
$lang['Reg_users_online'] = "Det är %d Registrerade användare och "; // There ae 5 Registered and
$lang['Reg_user_online'] = "Det är %d Registrerad användare och "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "0 Dolda användare online"; // 6 Hidden users online 
$lang['Hidden_users_online'] = "%d Dolda användare online"; // 6 Hidden users online
$lang['Hidden_user_online'] = "%d Dold användare online"; // 6 Hidden users online
$lang['Guest_users_online'] = "Det är %d Gäster online"; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = "Det är 0 Gäster online"; // There are 10 Guest users online 
$lang['Guest_user_online'] = "Det är %d Gäst online"; // There is 1 Guest user online
$lang['No_users_browsing'] = "Det är inga användare på forumet just nu";

$lang['Online_explain'] = "Den här datan är baserad på aktiva användare under de senaste 5 minuterna";

$lang['Forum_Location'] = "Forumplats";
$lang['Last_updated'] = "Senast uppdaterad";

$lang['Forum_index'] = "Forumindex";
$lang['Logging_on'] = "Loggar in";
$lang['Posting_message'] = "Skriver ett inlägg";
$lang['Searching_forums'] = "Söker i forumet";
$lang['Viewing_profile'] = "Kollar på profil";
$lang['Viewing_online'] = "Kollar vilka som är online";
$lang['Viewing_member_list'] = "Kollar på användarlistan";
$lang['Viewing_priv_msgs'] = "Kollar Privata Meddelanden";
$lang['Viewing_FAQ'] = "Kollar på FAQ";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Moderatorkontrollpanel";
$lang['Mod_CP_explain'] = "Genom att använda formuläret nedan kan du utföra mass modereringsoperationer på det här forumet. Du kan låsa, låsa upp, flytta eller ta bort hur många ämnen som helst.";

$lang['Select'] = "Välj";
$lang['Delete'] = "Ta bort";
$lang['Move'] = "Flytta";
$lang['Lock'] = "Lås";
$lang['Unlock'] = "Lås upp";

$lang['Topics_Removed'] = "De valda ämnena har tagits bort från databasen";
$lang['Topics_Locked'] = "De valda ämnena har låsts";
$lang['Topics_Moved'] = "De valda ämnena har flyttats";
$lang['Topics_Unlocked'] = "De valda ämnena har låsts upp";
$lang['No_Topics_Moved'] = "Inga ämnen flyttades";

$lang['Confirm_delete_topic'] = "Är du säker på att du vill ta bort de valda ämnena?";
$lang['Confirm_lock_topic'] = "Är du säker på att du vill låsa de valda ämnena?";
$lang['Confirm_unlock_topic'] = "Är du säker på att du vill låsa upp de valda ämnena?";
$lang['Confirm_move_topic'] = "Är du säker på att du vill flytta de valda ämnena?";

$lang['Move_to_forum'] = "Flytta till forum";
$lang['Leave_shadow_topic'] = "Lämna skugga av ämnet i det gamla forumet.";

$lang['Split_Topic'] = "Dela ämnekontrollpanel";
$lang['Split_Topic_explain'] = "Genom att använda formuläret nedan kan du dela ett ämne i 2 delar, antingen genom att välja inläggen individuellt eller genom att dela vid ett valt inlägg";
$lang['Split_title'] = "Ny ämnesrubrik";
$lang['Split_forum'] = "Forum för nytt ämne";
$lang['Split_posts'] = "Dela valda inlägg";
$lang['Split_after'] = "Dela från valt inlägg";
$lang['Topic_split'] = "Det valda ämnet har blivit delat";

$lang['Too_many_error'] = "Du har valt för många inlägg. Du kan endast välja ett inlägg att dela ämnet efter!";

$lang['None_selected'] = "Du har inte valt några ämnen att utföra operationen på. Gå tillbaka och välj minst en.";
$lang['New_forum'] = "Nytt forum";

$lang['This_posts_IP'] = "IP för den här posten";
$lang['Other_IP_this_user'] = "Andra IP-adresser som den här användaren har postat från";
$lang['Users_this_IP'] = "Användare som postar från den här IP-adressen";
$lang['IP_info'] = "IP-information";
$lang['Lookup_IP'] = "Slå upp IP";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "Alla tider är %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 Timmar";
$lang['-11'] = "GMT - 11 Timmar";
$lang['-10'] = "GMT - 10 Timmar";
$lang['-9'] = "GMT - 9 Timmar";
$lang['-8'] = "GMT - 8 Timmar";
$lang['-7'] = "GMT - 7 Timmar";
$lang['-6'] = "GMT - 6 Timmar";
$lang['-5'] = "GMT - 5 Timmar";
$lang['-4'] = "GMT - 4 Timmar";
$lang['-3.5'] = "GMT - 3.5 Timmar";
$lang['-3'] = "GMT - 3 Timmar";
$lang['-2'] = "GMT - 2 Timmar";
$lang['-1'] = "GMT - 1 Timme";
$lang['0'] = "GMT";
$lang['1'] = "GMT + 1 Timme";
$lang['2'] = "GMT + 2 Timmar";
$lang['3'] = "GMT + 3 Timmar";
$lang['3.5'] = "GMT + 3.5 Timmar";
$lang['4'] = "GMT + 4 Timmar";
$lang['4.5'] = "GMT + 4.5 Timmar";
$lang['5'] = "GMT + 5 Timmar";
$lang['5.5'] = "GMT + 5.5 Timmar";
$lang['6'] = "GMT + 6 Timmar";
$lang['7'] = "GMT + 7 Timmar";
$lang['8'] = "GMT + 8 Timmar";
$lang['9'] = "GMT + 9 Timmar";
$lang['9.5'] = "GMT + 9.5 Timmar";
$lang['10'] = "GMT + 10 Timmar";
$lang['11'] = "GMT + 11 Timmar";
$lang['12'] = "GMT + 12 Timmar";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "GMT - 12 Timmar";
$lang['tz']['-11'] = "GMT - 11 Timmar";
$lang['tz']['-10'] = "GMT - 10 Timmar";
$lang['tz']['-9'] = "GMT - 9 Timmar";
$lang['tz']['-8'] = "GMT - 8 Timmar";
$lang['tz']['-7'] = "GMT - 7 Timmar";
$lang['tz']['-6'] = "GMT - 6 Timmar";
$lang['tz']['-5'] = "GMT - 5 Timmar";
$lang['tz']['-4'] = "GMT - 4 Timmar";
$lang['tz']['-3.5'] = "GMT - 3.5 Timmar";
$lang['tz']['-3'] = "GMT - 3 Timmar";
$lang['tz']['-2'] = "GMT - 2 Timmar";
$lang['tz']['-1'] = "GMT - 1 Timme";
$lang['tz']['0'] = "GMT";
$lang['tz']['1'] = "GMT + 1 Timme";
$lang['tz']['2'] = "GMT + 2 Timmar";
$lang['tz']['3'] = "GMT + 3 Timmar";
$lang['tz']['3.5'] = "GMT + 3.5 Timmar";
$lang['tz']['4'] = "GMT + 4 Timmar";
$lang['tz']['4.5'] = "GMT + 4.5 Timmar";
$lang['tz']['5'] = "GMT + 5 Timmar";
$lang['tz']['5.5'] = "GMT + 5.5 Timmar";
$lang['tz']['6'] = "GMT + 6 Timmar";
$lang['tz']['7'] = "GMT + 7 Timmar";
$lang['tz']['8'] = "GMT + 8 Timmar";
$lang['tz']['9'] = "GMT + 9 Timmar";
$lang['tz']['9.5'] = "GMT + 9.5 Timmar";
$lang['tz']['10'] = "GMT + 10 Timmar";
$lang['tz']['11'] = "GMT + 11 Timmar";
$lang['tz']['12'] = "GMT + 12 Timmar";

$lang['datetime']['Sunday'] = "Söndag";
$lang['datetime']['Monday'] = "Måndag";
$lang['datetime']['Tuesday'] = "Tisdag";
$lang['datetime']['Wednesday'] = "Onsdag";
$lang['datetime']['Thursday'] = "Torsdag";
$lang['datetime']['Friday'] = "Fredag";
$lang['datetime']['Saturday'] = "Lördag";
$lang['datetime']['Sun'] = "Sön";
$lang['datetime']['Mon'] = "Mån";
$lang['datetime']['Tue'] = "Tis";
$lang['datetime']['Wed'] = "Ons";
$lang['datetime']['Thu'] = "Tor";
$lang['datetime']['Fri'] = "Fre";
$lang['datetime']['Sat'] = "Lör";
$lang['datetime']['January'] = "Januari";
$lang['datetime']['February'] = "Februari";
$lang['datetime']['March'] = "Mars";
$lang['datetime']['April'] = "April";
$lang['datetime']['May'] = "Maj";
$lang['datetime']['June'] = "Juni";
$lang['datetime']['July'] = "Juli";
$lang['datetime']['August'] = "Augusti";
$lang['datetime']['September'] = "September";
$lang['datetime']['October'] = "Oktober";
$lang['datetime']['November'] = "November";
$lang['datetime']['December'] = "December";
$lang['datetime']['Jan'] = "Jan";
$lang['datetime']['Feb'] = "Feb";
$lang['datetime']['Mar'] = "Mar";
$lang['datetime']['Apr'] = "Apr";
$lang['datetime']['May'] = "Maj";
$lang['datetime']['Jun'] = "Jun";
$lang['datetime']['Jul'] = "Jul";
$lang['datetime']['Aug'] = "Aug";
$lang['datetime']['Sep'] = "Sep";
$lang['datetime']['Oct'] = "Okt";
$lang['datetime']['Nov'] = "Nov";
$lang['datetime']['Dec'] = "Dec";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "Information";
$lang['Critical_Information'] = "Kritisk Information";

$lang['General_Error'] = "Fel";
$lang['Critical_Error'] = "Kritiskt Fel";
$lang['An_error_occured'] = "Ett Fel Inträffade";
$lang['A_critical_error'] = "Ett Kritiskt Fel Inträffade";

//
// That's all Folks!
// -------------------------------------------------

?>
