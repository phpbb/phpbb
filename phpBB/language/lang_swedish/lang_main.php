<?php
/***************************************************************************
 *                            lang_main.php [Swedish]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group and (C) 2003 Jonathan Gulbrandsen
 *     email                : support@phpbb.com (translator:virtuality@carlssonplanet.com)
 *
 *     $Id: lang_main.php,v 1.1 2010/10/10 15:09:43 orynider Exp $
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

// CONTRIBUTORS:
//	 Add your details here if wanted, e.g. Name, username, email address, website
// XXXX-XX-XX  Orginal translation to Swedish by Marcus Svensson, Janĺke Rönnblom, Bruce and Jakob Persson
// 2003-07-31  Virtuality aka Jonathan Gulbrandsen (virtuality@carlssonplanet.com)        - updated to 2.0.5, also fixed loads of grammar problems
// 2003-08-12  Virtuality aka Jonathan Gulbrandsen (virtuality@carlssonplanet.com)        - updated to 2.0.6, from 2.0.5. COMPLETE survey of the file. Loads of stuff fixed.
// 2005-03-01  "_Haplo" Jon Ohlsson (jonohlsson@hotmail.com)        - updated to 2.0.13, from 2.0.6. COMPLETE survey of the file. Loads of stuff fixed.

//
// The format of this file is ---> $lang['message'] = 'text';
//
// You should also try to set a locale and a character encoding (plus direction). The encoding and direction
// will be sent to the template. The locale may or may not work, it's dependent on OS support and the syntax
// varies ... give it your best guess!
//

// Specify your language character encoding... [optional]
//
setlocale(LC_ALL, "sv");

$lang['USER_LANG'] = 'sv';
$lang['ENCODING'] = 'UTF-8';
$lang['DIRECTION'] = 'ltr';
$lang['LEFT'] = 'left';
$lang['RIGHT'] = 'right';
$lang['DATE_FORMAT'] = 'd F Y'; // This should be changed to the default date format for your language, php date() format

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.
$lang['TRANSLATION_INFO'] = '<a href="http://www.mx-publisher.com" target="_blank" class="gensmall">Swedish</a> translation by <a href="mailto:jon.ohlsson@mx-publisher.com" title="Jon Ohlsson" class="gensmall">Jon Ohlsson</a> &copy; 2004-2008';

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = 'Forum';
$lang['Category'] = 'Kategori';
$lang['Topic'] = 'Ämne';
$lang['Topics'] = 'Ämnen';
$lang['Replies'] = 'Svar';
$lang['Views'] = 'Visningar';
$lang['Post'] = 'Inlägg';
$lang['Posts'] = 'Inlägg';
$lang['Posted'] = 'Skrivet';
$lang['Username'] = 'Användarnamn';
$lang['Password'] = 'Lösenord';
$lang['Email'] = 'E-post';
$lang['Poster'] = 'Avsändare';
$lang['Author'] = 'Författare';
$lang['Time'] = 'Tid';
$lang['Hours'] = 'Timmar';
$lang['Message'] = 'Meddelande';

$lang['1_Day'] = '1 Dag';
$lang['7_Days'] = '7 Dagar';
$lang['2_Weeks'] = '2 Veckor';
$lang['1_Month'] = '1 Mĺnad';
$lang['3_Months'] = '3 Mĺnader';
$lang['6_Months'] = '6 Mĺnader';
$lang['1_Year'] = '1 Ĺr';

$lang['Go'] = 'Gĺ';
$lang['Jump_to'] = 'Hoppa till';
$lang['Submit'] = 'Skicka';
$lang['Reset'] = 'Ĺterställ';
$lang['Cancel'] = 'Avbryt';
$lang['Preview'] = 'Förhandsgranska';
$lang['Confirm'] = 'Bekräfta';
$lang['Spellcheck'] = 'Stavningskontroll';
$lang['Yes'] = 'Ja';
$lang['No'] = 'Nej';
$lang['Enabled'] = 'Aktiverad';
$lang['Disabled'] = 'Inaktiverad';
$lang['Error'] = 'Fel';

$lang['Next'] = 'Nästa';
$lang['Previous'] = 'Föregĺende';
$lang['Goto_page'] = 'Gĺ till sida';
$lang['Joined'] = 'Blev medlem';
$lang['IP_Address'] = 'IP Adress';

$lang['Select_forum'] = 'Välj forum';
$lang['View_latest_post'] = 'Visa senaste inlägg';
$lang['View_newest_post'] = 'Visa nyaste inlägg';
$lang['Page_of'] = 'Sida <b>%d</b> av <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'ICQ-nummer';
$lang['AIM'] = 'AIM-adress';
$lang['MSNM'] = 'MSN Messenger';
$lang['YIM'] = 'Yahoo Messenger';

$lang['Forum_Index'] = 'Forumindex';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = 'Skapa nytt inlägg';
$lang['Reply_to_topic'] = 'Svara pĺ inlägget';
$lang['Reply_with_quote'] = 'Citera';

$lang['Click_return_topic'] = 'Klicka %sHär%s för att ĺtervända till ämnet'; // %s's here are for urls, do not remove!
$lang['Click_return_login'] = 'Klicka %sHär%s för att försöka igen';
$lang['Click_return_forum'] = 'Klicka %sHär%s för att ĺtervända till forumet';
$lang['Click_view_message'] = 'Klicka %sHär%s för att visa ditt meddelande';
$lang['Click_return_modcp'] = 'Klicka %sHär%s för att ĺtervända till Moderatorkontollpanel';
$lang['Click_return_group'] = 'Klicka %sHär%s för att ĺtervända till gruppinformationen';

$lang['Admin_panel'] = 'G&aring; till Administrationspanelen';

$lang['Board_disable'] = 'Det här forumet är tyvärr otillgängligt för tillfället, var vänlig försök senare.';


//
// Global Header strings
//
$lang['Registered_users'] = 'Registrerade användare:';
$lang['Browsing_forum'] = 'Användare som är pĺ forumet just nu:';
$lang['Online_users_zero_total'] = 'Det är totalt <b>0</b> användare online :: ';
$lang['Online_users_total'] = 'Det är totalt <b>%d</b> användare online :: ';
$lang['Online_user_total'] = 'Det är totalt <b>%d</b> användare online :: ';
$lang['Reg_users_zero_total'] = '0 Registrerade, ';
$lang['Reg_users_total'] = '%d Registrerade, ';
$lang['Reg_user_total'] = '%d Registrerad, ';
$lang['Hidden_users_zero_total'] = '0 Dolda och ';
$lang['Hidden_user_total'] = '%d Dold and ';
$lang['Hidden_users_total'] = '%d Dolda and ';
$lang['Guest_users_zero_total'] = '0 Gäster';
$lang['Guest_users_total'] = '%d Gäster';
$lang['Guest_user_total'] = '%d Gäst';
$lang['Record_online_users'] = 'Flest användare samtidigt var <b>%s</b> den %s'; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = '%sAdministratör%s';
$lang['Mod_online_color'] = '%sModerator%s';

$lang['You_last_visit'] = 'Du gjorde ditt senaste besök den %s'; // %s replaced by date/time
$lang['Current_time'] = 'Lokal tid (för forumet) %s'; // %s replaced by time

$lang['Search_new'] = 'Visa nya inlägg sedan ditt senaste besök';
$lang['Search_your_posts'] = 'Visa dina inlägg';
$lang['Search_unanswered'] = 'Visa obesvarade ämnen';

$lang['Register'] = 'Bli medlem';
$lang['Profile'] = 'Min profil';
$lang['Edit_profile'] = 'Ändra dina inställningar';
$lang['Search'] = 'S&ouml;k';
$lang['Memberlist'] = 'Medlemmar';
$lang['FAQ'] = 'Vanliga frĺgor';
$lang['BBCode_guide'] = 'BBCode-guide';
$lang['Usergroups'] = 'Användargrupper';
$lang['Last_Post'] = 'Senaste inlägg';
$lang['Moderator'] = '<b>Moderator</b>';
$lang['Moderators'] = '<b>Moderatorer</b>';


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = 'Vĺra användare har skrivit totalt <b>0</b> inlägg'; // Number of posts
$lang['Posted_articles_total'] = 'Vĺra användare har skrivit totalt <b>%d</b> inlägg'; // Number of posts
$lang['Posted_article_total'] = 'Vĺra användare har skrivit totalt <b>%d</b> inlägg'; // Number of posts
$lang['Registered_users_zero_total'] = 'Vi har <b>0</b> registrerade användare'; // # registered users
$lang['Registered_users_total'] = 'Vi har <b>%d</b> registrerade användare'; // # registered users
$lang['Registered_user_total'] = 'Vi har <b>%d</b> registrerad användare'; // # registered users
$lang['Newest_user'] = 'Den senast registrerade användaren är <b>%s%s%s</b>'; // a href, username, /a

$lang['No_new_posts_last_visit'] = 'Inga nya inlägg sedan ditt senaste besök';
$lang['No_new_posts'] = 'Inga nya inlägg';
$lang['New_posts'] = 'Nya inlägg';
$lang['New_post'] = 'Nya inlägg';
$lang['No_new_posts_hot'] = 'Inga nya inlägg [ Populär ]';
$lang['New_posts_hot'] = 'Nya inlägg [ Populär ]';
$lang['No_new_posts_locked'] = 'Inga nya inlägg [ Lĺst ]';
$lang['New_posts_locked'] = 'Nya inlägg [ Lĺst ]';
$lang['Forum_is_locked'] = 'Forumet är lĺst';


//
// Login
//
$lang['Enter_password'] = 'Skriv in ditt användarnamn och lösenord för att logga in';
$lang['Login'] = 'Logga in';
$lang['Logout'] = 'Logga ut';

$lang['Forgotten_password'] = 'Jag har glömt mitt lösenord';

$lang['Log_me_in'] = 'Logga in mig automatiskt';

$lang['Error_login'] = 'Du har skrivit in antingen ett felaktigt eller inaktivt användarnamn eller ett felaktigt lösenord';


//
// Index page
//
$lang['Index'] = 'Index';
$lang['No_Posts'] = 'Inga inlägg';
$lang['No_forums'] = 'Inga forum är skapade än';

$lang['Private_Message'] = 'Meddelande';
$lang['Private_Messages'] = 'Mina mess';
$lang['Who_is_Online'] = 'Vem är Online';

$lang['Mark_all_forums'] = 'Markera alla forum som lästa';
$lang['Forums_marked_read'] = 'Alla forum har nu markerats som lästa';


//
// Viewforum
//
$lang['View_forum'] = 'Visa forum';

$lang['Forum_not_exist'] = 'Det forum som du valt finns inte';
$lang['Reached_on_error'] = 'Du har nĺtt den här sidan pĺ fel sätt';

$lang['Display_topics'] = 'Visa tidigare ämnen';
$lang['All_Topics'] = 'Alla ämnen';

$lang['Topic_Announcement'] = '<b>Viktigt meddelande:</b>';
$lang['Topic_Sticky'] = '<b>Klistrad:</b>';
$lang['Topic_Moved'] = '<b>Flyttad:</b>';
$lang['Topic_Poll'] = '<b>[ Omröstning ]</b>';

$lang['Mark_all_topics'] = 'Markera alla ämnen som lästa';
$lang['Topics_marked_read'] = 'Alla ämnen i det här forumet har markerats som lästa';

$lang['Rules_post_can'] = 'Du <b>kan</b> skapa nya inlägg i det här forumet';
$lang['Rules_post_cannot'] = 'Du <b>kan inte</b> skapa nya inlägg i det här forumet';
$lang['Rules_reply_can'] = 'Du <b>kan</b> svara pĺ inlägg i det här forumet';
$lang['Rules_reply_cannot'] = 'Du <b>kan inte</b> svara pĺ inlägg i det här forumet';
$lang['Rules_edit_can'] = 'Du <b>kan</b> ändra dina inlägg i det här forumet';
$lang['Rules_edit_cannot'] = 'Du <b>kan inte</b> ändra dina inlägg i det här forumet';
$lang['Rules_delete_can'] = 'Du <b>kan</b> ta bort dina inlägg i det här forumet';
$lang['Rules_delete_cannot'] = 'Du <b>kan inte</b> ta bort dina inlägg i det här forumet';
$lang['Rules_vote_can'] = 'Du <b>kan</b> rösta i det här forumet';
$lang['Rules_vote_cannot'] = 'Du <b>kan inte</b> rösta i det här forumet';
$lang['Rules_moderate'] = 'Du <b>är</b> %smoderator i det här forumet%s'; // %s replaced by a href links, do not remove!

$lang['No_topics_post_one'] = 'Det finns inga ämnen i det här forumet<br />Klicka pĺ <b>Skapa Nytt Ämne</b>-länken pĺ denna sidan för att skapa ett.';


//
// Viewtopic
//
$lang['View_topic'] = 'Visa ämne';

$lang['Guest'] = 'Gäst';
$lang['Post_subject'] = 'Rubrik';
$lang['View_next_topic'] = 'Visa nästa ämne';
$lang['View_previous_topic'] = 'Visa föregĺende ämne';
$lang['Submit_vote'] = 'Skicka in röst';
$lang['View_results'] = 'Visa resultat';

$lang['No_newer_topics'] = 'Det finns inga nyare ämnen i det här forumet';
$lang['No_older_topics'] = 'Det finns inga äldre ämnen i det här forumet';
$lang['Topic_post_not_exist'] = 'Det sökta inlägget existerar inte';
$lang['No_posts_topic'] = 'Det finns inga svar till det här ämnet';

$lang['Display_posts'] = 'Visa tidigare inlägg';
$lang['All_Posts'] = 'Alla inlägg';
$lang['Newest_First'] = 'Nyaste först';
$lang['Oldest_First'] = 'Äldsta först';

$lang['Back_to_top'] = 'Till överst pĺ sidan';

$lang['Read_profile'] = 'Visa användarens profil';
$lang['Send_email'] = 'Skicka e-post';
$lang['Visit_website'] = 'Besök användarens hemsida';
$lang['ICQ_status'] = 'ICQ Status';
$lang['Edit_delete_post'] = 'Ändra/Ta bort det här inlägget';
$lang['View_IP'] = 'Visa författarens IP';
$lang['Delete_post'] = 'Ta bort det här inlägget';

$lang['wrote'] = 'skrev'; // proceeds the username and is followed by the quoted text
$lang['Quote'] = 'Citat'; // comes before bbcode quote output.
$lang['Code'] = 'Kod'; // comes before bbcode code output.

$lang['Edited_time_total'] = 'Senast ändrad av %s den %s, ändrad totalt %d gĺng'; // Last edited by me on 12 Oct 2001; edited 1 time in total
$lang['Edited_times_total'] = 'Senast ändrad av %s den %s, ändrad totalt %d gĺnger'; // Last edited by me on 12 Oct 2001; edited 2 times in total

$lang['Lock_topic'] = 'Lĺs det här ämnet';
$lang['Unlock_topic'] = 'Lĺs upp det här ämnet';
$lang['Move_topic'] = 'Flytta det här ämnet';
$lang['Delete_topic'] = 'Ta bort det här ämnet';
$lang['Split_topic'] = 'Dela det här ämnet';

$lang['Stop_watching_topic'] = 'Sluta bevaka det här ämnet';
$lang['Start_watching_topic'] = 'Bevaka det här ämnet för svar';
$lang['No_longer_watching'] = 'Du bevakar inte längre det här ämnet';
$lang['You_are_watching'] = 'Du bevakar nu det här ämnet';

$lang['Total_votes'] = 'Totalt antal röster';

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = 'Meddelande';
$lang['Topic_review'] = 'Ämneshistorik';

$lang['No_post_mode'] = 'Inget postningsval specificerat'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = 'Skapa nytt ämne';
$lang['Post_a_reply'] = 'Svara pĺ ämne';
$lang['Post_topic_as'] = 'Skapa ämne som';
$lang['Edit_Post'] = 'Ändra inlägg';
$lang['Options'] = 'Alternativ';

$lang['Post_Announcement'] = 'Viktigt meddelande';
$lang['Post_Sticky'] = 'Klistrad';
$lang['Post_Normal'] = 'Normal';

$lang['Confirm_delete'] = 'Är du säker pĺ att du vill ta bort det här inlägget?';
$lang['Confirm_delete_poll'] = 'Är du säker pĺ att du vill ta bort den här omröstnignen?';

$lang['Flood_Error'] = 'Du mĺste vänta lite innan du kan posta ett nytt inlägg.';
$lang['Empty_subject'] = 'Du mĺste skriva en rubrik om du skapar ett nytt inlägg.';
$lang['Empty_message'] = 'Du mĺste skriva ett meddelande när du postar.';
$lang['Forum_locked'] = 'Det här forumet är lĺst sĺ du kan varken skapa, svara pĺ eller ändra inlägg.';
$lang['Topic_locked'] = 'Det här ämnet är lĺst sĺ du kan varken svara pĺ eller ändra det.';
$lang['No_post_id'] = 'Inget inläggs ID specificerat';
$lang['No_topic_id'] = 'Du mĺste välja ett inlägg att svara pĺ';
$lang['No_valid_mode'] = 'Du kan bara skapa, svara pĺ eller ändra inlägg, gĺ tillbaka och försök igen.';
$lang['No_such_post'] = 'Inlägget du letade efter finns inte, gĺ tillbaka och försök igen.';
$lang['Edit_own_posts'] = 'Du kan bara ändra dina egna inlägg.';
$lang['Delete_own_posts'] = 'Du kan bara ta bort dina egna inlägg.';
$lang['Cannot_delete_replied'] = 'Du inte ta bort inlägg som svarats pĺ.';
$lang['Cannot_delete_poll'] = 'Du kan tyvärr inte ta bort en aktiv omröstning.';
$lang['Empty_poll_title'] = 'Du mĺste skriva in ett namn pĺ omröstningen.';
$lang['To_few_poll_options'] = 'Du mĺste lägga till minst 2 val i omröstningen.';
$lang['To_many_poll_options'] = 'Du försökte lägga till för mĺnga val i omröstningen.';
$lang['Post_has_no_poll'] = 'Detta inlägge har ingen omröstning.';
$lang['Already_voted'] = 'Du har redan deltagit i den här omröstningen.';
$lang['No_vote_option'] = 'Du mĺste markera ett alternativ när du röstar.';

$lang['Add_poll'] = 'Lägg till omröstning';
$lang['Add_poll_explain'] = 'Om du inte vill lägga till nĺgon omröstning till ditt inlägg, lämna fälten tomma.';
$lang['Poll_question'] = 'Omröstningsfrĺga';
$lang['Poll_option'] = 'Svarsalternativ';
$lang['Add_option'] = 'Lägg till svarsalternativ';
$lang['Update'] = 'Uppdatera';
$lang['Delete'] = 'Ta bort';
$lang['Poll_for'] = 'Antal dagar omröstningen löper';
$lang['Days'] = 'Dagar'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = '[ Ange 0 för att skapa en omröstning som aldrig slutar ]';
$lang['Delete_poll'] = 'Ta bort omröstning';

$lang['Disable_HTML_post'] = 'Inaktivera HTML i det här inlägget';
$lang['Disable_BBCode_post'] = 'Inaktivera BBCode i det här inlägget';
$lang['Disable_Smilies_post'] = 'Inaktivera Smilies i det här inlägget';

$lang['HTML_is_ON'] = 'HTML är <u>PĹ</u>';
$lang['HTML_is_OFF'] = 'HTML är <u>AV</u>';
$lang['BBCode_is_ON'] = '%sBBCode%s är <u>PĹ</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = '%sBBCode%s är <u>AV</u>';
$lang['Smilies_are_ON'] = 'Smilies är <u>PĹ</u>';
$lang['Smilies_are_OFF'] = 'Smilies är <u>AV</u>';

$lang['Attach_signature'] = 'Lägg till signatur (signaturen kan ändras i profilen)';
$lang['Notify'] = 'Kontakta mig vid svar';
$lang['Delete_post'] = 'Ta bort det här inlägget';

$lang['Stored'] = 'Ditt meddelande har sparats.';
$lang['Deleted'] = 'Ditt meddelande har tagits bort.';
$lang['Poll_delete'] = 'Din omröstning har tagits bort.';
$lang['Vote_cast'] = 'Din röst har räknats.';

$lang['Topic_reply_notification'] = 'Meddelande om svar pĺ inlägg';

$lang['bbcode_b_help'] = 'Fetstilad text: [b]text[/b]  (alt+b)';
$lang['bbcode_i_help'] = 'Kursiv text: [i]text[/i]  (alt+i)';
$lang['bbcode_u_help'] = 'Understruken text: [u]text[/u]  (alt+u)';
$lang['bbcode_q_help'] = 'Citerad text: [quote]text[/quote]  (alt+q)';
$lang['bbcode_c_help'] = 'Visning av kod: [code]kod[/code]  (alt+c)';
$lang['bbcode_l_help'] = 'Lista: [list]text[/list] (alt+l)';
$lang['bbcode_o_help'] = 'Ordnad lista: [list=]text[/list]  (alt+o)';
$lang['bbcode_p_help'] = 'Lägg till bild: [img]http://image_url[/img]  (alt+p)';
$lang['bbcode_w_help'] = 'Lägg till länk: [url]http://url[/url] eller [url=http://url]URL text[/url]  (alt+w)';
$lang['bbcode_a_help'] = 'Stäng alla öppna bbCode taggar';
$lang['bbcode_s_help'] = 'Teckenfärg: [color=red]text[/color]  Tips: du kan även använda color=#FF0000';
$lang['bbcode_f_help'] = 'Teckenstorlek: [size=x-small]liten text[/size]';

$lang['Emoticons'] = 'Smilies';
$lang['More_emoticons'] = 'Visa fler Smilies';

$lang['Font_color'] = 'Teckenfärg';
$lang['color_default'] = 'Standard';
$lang['color_dark_red'] = 'Mörkröd';
$lang['color_red'] = 'Röd';
$lang['color_orange'] = 'Orange';
$lang['color_brown'] = 'Brun';
$lang['color_yellow'] = 'Gul';
$lang['color_green'] = 'Grön';
$lang['color_olive'] = 'Oliv';
$lang['color_cyan'] = 'Cyan';
$lang['color_blue'] = 'Blĺ';
$lang['color_dark_blue'] = 'Mörkblĺ';
$lang['color_indigo'] = 'Lila';
$lang['color_violet'] = 'Rosa';
$lang['color_white'] = 'Vit';
$lang['color_black'] = 'Svart';

$lang['Font_size'] = 'Teckenstorlek';
$lang['font_tiny'] = 'Pytteliten';
$lang['font_small'] = 'Liten';
$lang['font_normal'] = 'Normal';
$lang['font_large'] = 'Stor';
$lang['font_huge'] = 'Enorm';

$lang['Close_Tags'] = 'Stäng taggar';
$lang['Styles_tip'] = 'Tips: Stilar kan snabbt användas pĺ markerad text';


//
// Private Messaging
//
$lang['Private_Messaging'] = 'Personliga Meddelanden';

$lang['Login_check_pm'] = 'Logga in för att läsa dina meddelanden';
$lang['New_pms'] = 'Du har %d nya meddelanden'; // You have 2 new messages
$lang['New_pm'] = 'Du har %d nytt meddelande'; // You have 1 new message
$lang['No_new_pm'] = 'Du har inga nya meddelanden';
$lang['Unread_pms'] = 'Du har %d olästa meddelanden';
$lang['Unread_pm'] = 'Du har %d oläst meddelande';
$lang['No_unread_pm'] = 'Du har inga olästa meddelanden';
$lang['You_new_pm'] = 'Ett nytt meddelande väntar pĺ dig i din Inkorg';
$lang['You_new_pms'] = 'Nya meddelanden väntar pĺ dig i din Inkorg';
$lang['You_no_new_pm'] = 'Inga nya meddelanden väntar pĺ dig';

$lang['Unread_message'] = 'Oläst meddelande';
$lang['Read_message'] = 'Läst meddelande';

$lang['Read_pm'] = 'Läs meddelande';
$lang['Post_new_pm'] = 'Skicka meddelande';
$lang['Post_reply_pm'] = 'Svara pĺ meddelande';
$lang['Post_quote_pm'] = 'Citera meddelande';
$lang['Edit_pm'] = 'Ändra meddelande';

$lang['Inbox'] = 'Inkorg';
$lang['Outbox'] = 'Utkorg';
$lang['Savebox'] = 'Sparat';
$lang['Sentbox'] = 'Skickat';
$lang['Flag'] = 'Flagga';
$lang['Subject'] = 'Rubrik';
$lang['From'] = 'Frĺn';
$lang['To'] = 'Till';
$lang['Date'] = 'Datum';
$lang['Mark'] = 'Markera';
$lang['Sent'] = 'Skickad';
$lang['Saved'] = 'Sparat';
$lang['Delete_marked'] = 'Ta bort markerade';
$lang['Delete_all'] = 'Ta bort alla';
$lang['Save_marked'] = 'Spara markerade';
$lang['Save_message'] = 'Spara meddelande';
$lang['Delete_message'] = 'Ta bort meddelande';

$lang['Display_messages'] = 'Visa tidigare meddelanden'; // Followed by number of days/weeks/months
$lang['All_Messages'] = 'Alla meddelanden';

$lang['No_messages_folder'] = 'Du har inga meddelanden i denna mappen';

$lang['PM_disabled'] = 'Personliga meddelanden har inaktiverats pĺ detta forum.';
$lang['Cannot_send_privmsg'] = 'Administratören har tyvärr hindrat dig frĺn att skicka personliga meddelanden';
$lang['No_to_user'] = 'Du mĺste skriva in ett användarnamn att skicka meddelandet till.';
$lang['No_such_user'] = 'Användaren finns inte, var god försök igen.';

$lang['Disable_HTML_pm'] = 'Inaktivera HTML i det här meddelandet';
$lang['Disable_BBCode_pm'] = 'Inaktivera BBCode i det här meddelandet';
$lang['Disable_Smilies_pm'] = 'Inaktivera Smilies i detta meddelande';

$lang['Message_sent'] = 'Ditt meddelande har skickats.';

$lang['Click_return_inbox'] = 'Klicka %sHär%s för att ĺtergĺ till din Inkorg';
$lang['Click_return_index'] = 'Klicka %sHär%s för att ĺtergĺ till index';

$lang['Send_a_new_message'] = 'Skicka ett nytt personligt meddelande';
$lang['Send_a_reply'] = 'Svara pĺ ett personligt meddelande';
$lang['Edit_message'] = 'Ändra ett personligt meddelande';

$lang['Notification_subject'] = 'Du har fĺtt ett Personligt Meddelande!';

$lang['Find_username'] = 'Sök efter användarnamn';
$lang['Find'] = 'Sök';
$lang['No_match'] = 'Inga matchande träffar hittades.';

$lang['No_post_id'] = 'Inget inläggs ID specificerat';
$lang['No_such_folder'] = 'Den mappen finns inte';
$lang['No_folder'] = 'Ingen mapp specificerad';

$lang['Mark_all'] = 'Markera alla';
$lang['Unmark_all'] = 'Avmarkera alla';

$lang['Confirm_delete_pm'] = 'Är du säker pĺ att du vill ta bort det här meddelandet?';
$lang['Confirm_delete_pms'] = 'Är du säker pĺ att du vill ta bort de här meddelandena?';

$lang['Inbox_size'] = 'Din Inkorg är %d%% full'; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = 'Din Utkorg är %d%% full';
$lang['Savebox_size'] = 'Din Sparat mapp är %d%% full';

$lang['Click_view_privmsg'] = 'Klicka %sHär%s för att gĺ till din Inkorg';


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = 'Visar Profil :: %s'; // %s is username
$lang['About_user'] = 'Allt om %s'; // %s is username

$lang['Preferences'] = 'Inställningar';
$lang['Items_required'] = 'Allt som är markerat med * mĺste fyllas i om inte annat angivs';
$lang['Registration_info'] = 'Registreringsinformation';
$lang['Profile_info'] = 'Profilinformation';
$lang['Profile_info_warn'] = 'Följande information kommer vara synlig för andra';
$lang['Avatar_panel'] = 'Avatarkontrollpanel';
$lang['Avatar_gallery'] = 'Avatargalleri';

$lang['Website'] = 'Hemsida';
$lang['Location'] = 'Frĺn';
$lang['Contact'] = 'Kontakt';
$lang['Email_address'] = 'E-post adress';
$lang['Email'] = 'E-post';
$lang['Send_private_message'] = 'Skicka personligt meddelande';
$lang['Hidden_email'] = '[ Dold ]';
$lang['Search_user_posts'] = 'Hitta alla inlägg av %s';
$lang['Interests'] = 'Intressen';
$lang['Occupation'] = 'Yrke/sysselsättning';
$lang['Poster_rank'] = 'Rank';

$lang['Total_posts'] = 'Totalt antal inlägg';
$lang['User_post_pct_stats'] = '%.2f%% av totala'; // 1.25% of total
$lang['User_post_day_stats'] = '%.2f inlägg per dag'; // 1.5 posts per day
$lang['Search_user_posts'] = 'Hitta alla inlägg av %s'; // Find all posts by username

$lang['No_user_id_specified'] = 'Den användaren finns tyvärr inte.';
$lang['Wrong_Profile'] = 'Du kan inte ändra en profil som inte är din egen.';

$lang['Only_one_avatar'] = 'Endast en typ av avatar kan väljas';
$lang['File_no_data'] = 'Filen pĺ adressen du angav innehöll ingen data';
$lang['No_connection_URL'] = 'En anslutning kunde ej göras till adressen du angav';
$lang['Incomplete_URL'] = 'Adressen du angav är inte komplett';
$lang['Wrong_remote_avatar_format'] = 'Adressen till avataren du angav är inte giltig';
$lang['No_send_account_inactive'] = 'Tyvärr kan inte ditt lösenord skickas eftersom ditt konto är inaktivt. Kontakta forumadministratören för mer information.';

$lang['Always_smile'] = 'Aktivera alltid Smilies';
$lang['Always_html'] = 'Tillĺt alltid HTML';
$lang['Always_bbcode'] = 'Tillĺt alltid BBCode';
$lang['Always_add_sig'] = 'Inkludera alltid min signatur';
$lang['Always_notify'] = 'Kontakta alltid mig vid svar';
$lang['Always_notify_explain'] = 'Skickar ett e-post meddelande till dig när nĺgon svarar pĺ ett ämne där du har skrivit/svarat. Det här kan ändras när du skapar ett inlägg.';

$lang['Board_style'] = 'Forumstil';
$lang['Board_lang'] = 'Forumsprĺk';
$lang['No_themes'] = 'Inga teman i databasen';
$lang['Timezone'] = 'Tidszon';
$lang['Date_format'] = 'Datumformat';
$lang['Date_format_explain'] = 'Det här syntaxet är identiskt med PHP <a href=\\"http://www.php.net/date\\" target=\\"_other\\">date()</a> funktionen';
$lang['Signature'] = 'Signatur';
$lang['Signature_explain'] = 'Det här är ett stycke text som kan läggas till i inlägg du skapar. Det finns en gräns pĺ %d tecken';
$lang['Public_view_email'] = 'Visa alltid min e-post adress';

$lang['Current_password'] = 'Nuvarande lösenord';
$lang['New_password'] = 'Nytt lösenord';
$lang['Confirm_password'] = 'Bekräfta nytt lösenord';
$lang['Confirm_password_explain'] = 'Du mĺste ange ditt nuvarande lösenord om du vill ändra lösenord eller e-post adress';
$lang['password_if_changed'] = 'Du behöver bara fylla i ett lösenord om du skall ändra det';
$lang['password_confirm_if_changed'] = 'Du behöver bara bekräfta ditt lösenord om du skall ändra det';

$lang['Avatar'] = 'Avatar';
$lang['Avatar_explain'] = 'Visar en liten bild precis under dina detaljer i dina inlägg. Endast en bild kan visas ĺt gĺngen, bredden fĺr inte vara större än %d pixlar, höjden fĺr inte vara större än %d pixlar och filstoleken fĺr inte vara större än %d KB.';
$lang['Upload_Avatar_file'] = 'Ladda upp en avatar frĺn din dator';
$lang['Upload_Avatar_URL'] = 'Ladda upp en avatar frĺn en webbadress';
$lang['Upload_Avatar_URL_explain'] = 'Skriv in webbadressen där avatarbilden finns, den kommer att kopieras till det här forumet.';
$lang['Pick_local_Avatar'] = 'Välj en avatar frĺn galleriet';
$lang['Link_remote_Avatar'] = 'Länka till en avatar pĺ en annan sida';
$lang['Link_remote_Avatar_explain'] = 'Skriv in webbadressen där avatarbilden som du vill länka till finns';
$lang['Avatar_URL'] = 'Adress till avatarbild';
$lang['Select_from_gallery'] = 'Välj en avatar frĺn galleriet';
$lang['View_avatar_gallery'] = 'Visa galleriet';

$lang['Select_avatar'] = 'Välj avatar';
$lang['Return_profile'] = 'Avbryt';
$lang['Select_category'] = 'Välj kategori';

$lang['Delete_Image'] = 'Ta bort bild';
$lang['Current_Image'] = 'Nuvarande bild';

$lang['Notify_on_privmsg'] = 'Kontakta mig vid nytt Personligt Meddelande';
$lang['Popup_on_privmsg'] = 'Poppa upp fönster vid nytt Personligt Meddelande';
$lang['Popup_on_privmsg_explain'] = 'Vissa forumstilar kan poppa upp ett fönster som meddelar dig om att du fĺtt ett nytt Personligt Meddelande.';
$lang['Hide_user'] = 'Visa inte om jag är online';

$lang['Profile_updated'] = 'Din profil har blivit uppdaterad';
$lang['Profile_updated_inactive'] = 'Din profil har uppdaterats, men eftersom att du ändrade viktiga detaljer har ditt konto nu inaktiverats. Kontollera din e-post för att fĺ reda pĺ hur du skall gĺ till väga för att aktivera ditt konto igen. Eller om adminaktivering är nödvändig, var god vänta tills administratören aktiverat ditt konto igen.';

$lang['Password_mismatch'] = 'Lösenorden du skrev in matchade inte.';
$lang['Current_password_mismatch'] = 'Ditt nuvarande lösenord matchade inte med vad du skrev in.';
$lang['Password_long'] = 'Ditt lösenord fĺr inte vara längre än 32 tecken.';
$lang['Too_many_registers'] = 'Du har överskridit antalet tillĺtna registreringsförsök för denna session. Försök igen senare.';
$lang['Username_taken'] = 'Tyvärr var det här användarnamnet redan upptaget.';
$lang['Username_invalid'] = 'Ditt användarnamn innehöll ett ogiltigt tecken som t.ex. \"';
$lang['Username_disallowed'] = 'Tyvärr är inte det här användarnamnet tillĺtet.';
$lang['Email_taken'] = 'Den e-post adressen är redan registrerad hos oss.';
$lang['Email_banned'] = 'Tyvärr är den e-post adressen avstängd (bannad).';
$lang['Email_invalid'] = 'E-post adressen är felaktig.';
$lang['Signature_too_long'] = 'Din signatur är för lĺng.';
$lang['Fields_empty'] = 'Du mĺste fylla i alla fält som är markerade med *.';
$lang['Avatar_filetype'] = 'Avatar filtypen pĺste vara .jpg, .gif eller .png';
$lang['Avatar_filesize'] = 'Avatar filstorleken mĺste vara mindre än %d kB'; // The avatar image file size must be less than 6 KB
$lang['Avatar_imagesize'] = 'Avataren mĺste vara mindre än %d pixlar bred och %d pixlar hög';

$lang['Welcome_subject'] = 'Välkommen till %s Forum'; // Welcome to my.com forums
$lang['New_account_subject'] = 'Nytt användarkonto';
$lang['Account_activated_subject'] = 'Konto aktiverat';

$lang['Account_added'] = 'Tack för att du registrerade dig, ditt konto har nu blivit skapat. Du kan nu logga in med ditt användarnamn och lösenord';
$lang['Account_inactive'] = 'Ditt konto har skapats. Men, det här forumet kräver kontoaktivering, en aktiveringskod har skickats till e-post adressen du angav. Var god kontrollera din e-post för mer information';
$lang['Account_inactive_admin'] = 'Ditt konto har skapats. Men, det här forumet kräver kontoaktivering av administratören. Ett e-postmeddelande har skickats till dem och du kommer att bli informerad om när ditt konto blivit aktiverat';
$lang['Account_active'] = 'Ditt konto har nu aktiverats. Tack för att du registrerade dig';
$lang['Account_active_admin'] = 'Kontot har nu aktiverats';
$lang['Reactivate'] = 'Ĺteraktivera ditt konto!';
$lang['Already_activated'] = 'Du har redan aktiverat ditt konto!';
$lang['COPPA'] = 'Ditt konto har skapats men mĺste bli godkänt, kontrollera din e-post för mer information.';

$lang['Registration'] = 'Registreringsavtal';
$lang['Reg_agreement'] = 'Fastän administratörer och moderatorer pĺ det här forumet försöker att ta bort eller ändra allt störande eller stötande material sĺ fort som möjligt, är det omöjligt att gĺ igenom alla meddelanden. Vi vill därför meddela dig om att alla inlägg som skrivits pĺ de här forumet uttrycker vad författaren tänker och tycker, och administratörer eller moderatorer skall inte stĺ till ansvar för det (förrutom dĺ för de meddelanden som de själva skrivit).<br /><br /> Du gĺr med pĺ att inte posta nĺgot störande, stötande, vulgärt, hatiskt, hotande, sexuellt anspelande eller nĺgot annat material som kan tänkas bryta mot nĺgon tillämplig lag. Om du bryter mot det här kan det leda till att du blir permanent avstängd frĺn forumen (och din Internet Leverantör blir kontaktad). Ip adressen av alla meddelanden sparas för att stärka de här vilkoren. Du gĺr med pĺ att webmaster, administratör och moderatorer har rätt att ta bort, ändra, flytta eller stänga vilka inlägg som helst när som helst. Som en användare gĺr du med pĺ att all information som du skrivit in sparas i databasen. Den informationen kommer inte att distruberas till nĺgon tredje part utan ditt samtycke. Webmastern, administratören eller moderatorer kan inte hĺllas ansvariga vid hackningsförsök som kan leda till att data stjäls. <br /><br />Det här forums systemet använder cookies till att spara information pĺ din dator. De här cookiesen innehĺller inte nĺgot av den information du skrivit in ovan, utan de används endast för att göra ditt använda av forumet bättre. Email adressen är använd bara för att aktivera din registrering (och för att skicka nytt lösenord till dig om du rĺkar glömma det).<br /><br /> Genom att klicka pĺ Registrera nedan gĺr du med pĺ att bindas till de här vilkoren.';

$lang['Agree_under_13'] = 'Jag accepterar villkoren och är <b>under</b> 13 ĺr';
$lang['Agree_over_13'] = 'Jag accepterar villkoren och är <b>över</b> eller <b>exakt</b> 13 ĺr';
$lang['Agree_not'] = 'Jag gĺr inte med pĺ de här vilkoren';

$lang['Wrong_activation'] = 'Aktiveringskoden du angav matchar inte med den i databasen';
$lang['Send_password'] = 'Skicka ett nytt lösenord till mig';
$lang['Password_updated'] = 'Ett nytt lösenord har skapats, kontrollera din e-post för mer information om hur du skall aktivera det';
$lang['No_email_match'] = 'E-post adressen som du angav matchar inte med den som är listad med det användarnamnet';
$lang['New_password_activation'] = 'Ny lösenordsaktivering';
$lang['Password_activated'] = 'Ditt konto har ĺteraktiverats. För att logga in använd lösenordet som du hittar i emailet du fick';

$lang['Send_email_msg'] = 'Skicka ett e-postmeddelande';
$lang['No_user_specified'] = 'Ingen användare specificerad';
$lang['User_prevent_email'] = 'Den här användaren vill inte ta emot e-post. Försök att skicka ett Personligt Meddelande istället';
$lang['User_not_exist'] = 'Den användaren finns inte';
$lang['CC_email'] = 'Skicka en kopia av det här meddelandet till dig själv';
$lang['Email_message_desc'] = 'Det här meddelandet kommer att skickas som oformaterad text, inkludera inte nĺgon HTML eller BBCode. Svarsadressen för det här meddelandet kommer att vara din e-post adress.';
$lang['Flood_email_limit'] = 'Du kan inte skicka ett till email just nu, försök igen senare.';
$lang['Recipient'] = 'Mottagare';
$lang['Email_sent'] = 'E-postmeddelandet har skickats.';
$lang['Send_email'] = 'Skicka e-post';
$lang['Empty_subject_email'] = 'Du mĺste skriva in en rubrik pĺ e-postmeddelandet.';
$lang['Empty_message_email'] = 'Du mĺste skriva in ett meddelande som skall skickas.';


//
// Visual confirmation system strings
//
$lang['Confirm_code_wrong'] = 'Bekräftelsekoden du angav var felaktig.';
$lang['Too_many_registers'] = 'Du har överskridit antalet tillĺtna registreringsförsök för denna session. Försök igen senare.';
$lang['Confirm_code_impaired'] = 'Om du har dĺlig syn eller pĺ annat sätt inte kan läsa denna kod, var god kontakta %sAdministratören%s för hjälp.';
$lang['Confirm_code'] = 'Bekräftelsekod';
$lang['Confirm_code_explain'] = 'Ange koden exakt sĺ som du ser den. Koden är känslig för stora/smĺ bokstäver och noll har ett diagonalt streck genom sig.';



//
// Memberslist
//
$lang['Select_sort_method'] = 'Välj sorteringssätt';
$lang['Sort'] = 'Sortera';
$lang['Sort_Top_Ten'] = 'Top Tio Författare';
$lang['Sort_Joined'] = 'Blev medlem';
$lang['Sort_Username'] = 'Användarnamn';
$lang['Sort_Location'] = 'Frĺn';
$lang['Sort_Posts'] = 'Antal inlägg';
$lang['Sort_Email'] = 'E-post';
$lang['Sort_Website'] = 'Hemsida';
$lang['Sort_Ascending'] = 'Stigande ordning';
$lang['Sort_Descending'] = 'Fallande ordning';
$lang['Order'] = 'Ordning';

//
// Search
//
$lang['Search_query'] = 'Sökfrĺga';
$lang['Search_options'] = 'Sökalternativ';

$lang['Search_keywords'] = 'Sök efter nyckelord';
$lang['Search_keywords_explain'] = 'Du kan använda <u>AND</u> för att bestämma vilka ord som mĺste finnas i sökresultatet, <u>OR</u> för att bestämma vilka ord som kan finnas i sökresultatet och <u>NOT</u> för att bestämma ord som inte fĺr finnas i sökresultatet. Använd * som "wildcard" (vad som helst) för ofullständiga ord.';
$lang['Search_author'] = 'Sök efter författare';
$lang['Search_author_explain'] = 'Använd * som "wildcard" (vad som helst) för ofullständiga ord.';

$lang['Search_for_any'] = 'Sök efter alla termer eller använd den specificerade frĺgan';
$lang['Search_for_all'] = 'Sök efter alla termer';
$lang['Search_title_msg'] = 'Sök i ämnesrubrik och i meddelandetext';
$lang['Search_msg_only'] = 'Sök endast i meddelandetext';

$lang['Return_first'] = 'Skriv ut första'; // followed by xxx characters in a select box
$lang['characters_posts'] = 'tecknen frĺn inlägget';

$lang['Search_previous'] = 'Sök tidigare'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = 'Sortera efter';
$lang['Sort_Time'] = 'Tid skapad';
$lang['Sort_Post_Subject'] = 'Inläggsrubrik';
$lang['Sort_Topic_Title'] = 'Ämnestitel';
$lang['Sort_Author'] = 'Författare';
$lang['Sort_Forum'] = 'Forum';

$lang['Display_results'] = 'Visa resultat som';
$lang['All_available'] = 'Alla tillgängliga';
$lang['No_searchable_forums'] = 'Du har inte tillstĺnd att söka pĺ nĺgot forum pĺ den här sajten.';

$lang['No_search_match'] = 'Inga ämnen eller inlägg matchade dina sökkriterier';
$lang['Found_search_match'] = 'Sökningen hittade %d matchande resultat'; // eg. Search found 1 match
$lang['Found_search_matches'] = 'Sökningen hittade %d matchande resultat'; // eg. Search found 24 matches
$lang['Search_Flood_Error'] = 'Du kan inte söka sĺ snart inpĺ förra sökningen. Vänligen, försök igen om en stund.';

$lang['Close_window'] = 'Stäng fönster';


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = 'Tyvärr kan endast %s skapa viktiga meddelanden i det här forumet';
$lang['Sorry_auth_sticky'] = 'Tyvärr kan endast %s skapa klistrade meddelanden i det här forumet';
$lang['Sorry_auth_read'] = 'Tyvärr kan endast %s läsa ämnen i det här forumet';
$lang['Sorry_auth_post'] = 'Tyvärr kan endast %s skapa ämnen i det här forumet';
$lang['Sorry_auth_reply'] = 'Tyvärr kan endast %s skapa inlägg i det här forumet';
$lang['Sorry_auth_edit'] = 'Tyvärr kan endast %s ändra inlägg i det här forumet';
$lang['Sorry_auth_delete'] = 'Tyvärr kan endast %s ta bort inlägg frĺn det här forumet';
$lang['Sorry_auth_vote'] = 'Tyvärr kan endast %s vara med i omröstningar pĺ det här forumet';

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>anonyma användare</b>';
$lang['Auth_Registered_Users'] = '<b>registrerade användare</b>';
$lang['Auth_Users_granted_access'] = '<b>användare beviljade speciell ĺtkomst</b>';
$lang['Auth_Moderators'] = '<b>moderatorer</b>';
$lang['Auth_Administrators'] = '<b>administratörer</b>';

$lang['Not_Moderator'] = 'Du är inte en moderator i det här forumet.';
$lang['Not_Authorised'] = 'Inte legitimerad';

$lang['You_been_banned'] = 'Du har blivit avstängd (bannad) frĺn det här forumet<br />Var god kontakta webmastern eller forumadministratören för mer information.';


//
// Viewonline
//
$lang['Reg_users_zero_online'] = 'Det är 0 registrerade användare och '; // There are 5 Registered and
$lang['Reg_users_online'] = 'Det är %d registrerade användare och '; // There are 5 Registered and
$lang['Reg_user_online'] = 'Det är %d registrerad användare och '; // There is 1 Registered and
$lang['Hidden_users_zero_online'] = '0 dolda användare online'; // 6 Hidden users online
$lang['Hidden_users_online'] = '%d dolda användare online'; // 6 Hidden users online
$lang['Hidden_user_online'] = '%d dold användare online'; // 6 Hidden users online
$lang['Guest_users_online'] = 'Det är %d gäster online'; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = 'Det är 0 gäster online'; // There are 10 Guest users online
$lang['Guest_user_online'] = 'Det är %d gäst online'; // There is 1 Guest user online
$lang['No_users_browsing'] = 'Det är inga användare pĺ forumet just nu';

$lang['Online_explain'] = 'Informationen ovan är baserad pĺ aktiva användare under de senaste 5 minuterna';

$lang['Forum_Location'] = 'Forum plats';
$lang['Last_updated'] = 'Senast aktiv';

$lang['Forum_index'] = 'Forumindex';
$lang['Logging_on'] = 'Loggar in';
$lang['Posting_message'] = 'Skriver ett inlägg';
$lang['Searching_forums'] = 'Söker pĺ forumen';
$lang['Viewing_profile'] = 'Kollar pĺ profil';
$lang['Viewing_online'] = 'Kollar vilka som är online';
$lang['Viewing_member_list'] = 'Kollar pĺ medlemslistan';
$lang['Viewing_priv_msgs'] = 'Kollar Personliga Meddelanden';
$lang['Viewing_FAQ'] = 'Kollar pĺ FAQ';


//
// Moderator Control Panel
//
$lang['Mod_CP'] = 'Moderatorkontrollpanel';
$lang['Mod_CP_explain'] = 'Genom att använda formuläret nedan kan du utföra massmodererings operationer pĺ det här forumet. Du kan lĺsa, lĺsa upp, flytta eller ta bort hur mĺnga ämnen som helst.';

$lang['Select'] = 'Välj';
$lang['Delete'] = 'Ta bort';
$lang['Move'] = 'Flytta';
$lang['Lock'] = 'Lĺs';
$lang['Unlock'] = 'Lĺs upp';

$lang['Topics_Removed'] = 'De valda ämnena har tagits bort frĺn databasen.';
$lang['Topics_Locked'] = 'De valda ämnena har lĺsts.';
$lang['Topics_Moved'] = 'De valda ämnena har flyttats.';
$lang['Topics_Unlocked'] = 'De valda ämnena har lĺsts upp.';
$lang['No_Topics_Moved'] = 'Inga ämnen flyttades.';

$lang['Confirm_delete_topic'] = 'Är du säker pĺ att du vill ta bort de valda ämnena?';
$lang['Confirm_lock_topic'] = 'Är du säker pĺ att du vill lĺsa de valda ämnena?';
$lang['Confirm_unlock_topic'] = 'Är du säker pĺ att du vill lĺsa upp de valda ämnena?';
$lang['Confirm_move_topic'] = 'Är du säker pĺ att du vill flytta de valda ämnena?';

$lang['Move_to_forum'] = 'Flytta till forum';
$lang['Leave_shadow_topic'] = 'Lämna skugga av ämnet i det gamla forumet.';

$lang['Split_Topic'] = 'Dela Ämneskontrollpanel';
$lang['Split_Topic_explain'] = 'Genom att använda formuläret nedan kan du dela ett ämne i 2 delar, antingen genom att välja inläggen individuellt eller genom att dela vid ett valt inlägg';
$lang['Split_title'] = 'Ny ämnestitel';
$lang['Split_forum'] = 'Forum för nytt ämne';
$lang['Split_posts'] = 'Dela valda inlägg';
$lang['Split_after'] = 'Dela frĺn valt inlägg';
$lang['Topic_split'] = 'Det valda ämnet har blivit delat';

$lang['Too_many_error'] = 'DU har valt för mĺnga inlägg. Du kan endast välja ett inlägg att dela ämnet efter!';

$lang['None_selected'] = 'Du har inte valt nĺgra ämnen att utföra operationen pĺ. Gĺ tillbaka och välj minst en.';
$lang['New_forum'] = 'Nytt forum';

$lang['This_posts_IP'] = 'IP för det här inlägget';
$lang['Other_IP_this_user'] = 'Andra IP adresser som den här användaren har postat frĺn';
$lang['Users_this_IP'] = 'Användare som postar frĺn den här IP adressen';
$lang['IP_info'] = 'IP Information';
$lang['Lookup_IP'] = 'Slĺ upp IP';


//
// Timezones ... for display on each page
//
$lang['All_times'] = 'Alla tider är %s'; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = 'GMT - 12 timmar';
$lang['-11'] = 'GMT - 11 timmar';
$lang['-10'] = 'GMT - 10 timmar';
$lang['-9'] = 'GMT - 9 timmar';
$lang['-8'] = 'GMT - 8 timmar';
$lang['-7'] = 'GMT - 7 timmar';
$lang['-6'] = 'GMT - 6 timmar';
$lang['-5'] = 'GMT - 5 timmar';
$lang['-4'] = 'GMT - 4 timmar';
$lang['-3.5'] = 'GMT - 3.5 timmar';
$lang['-3'] = 'GMT - 3 timmar';
$lang['-2'] = 'GMT - 2 timmar';
$lang['-1'] = 'GMT - 1 timme';
$lang['0'] = 'GMT';
$lang['1'] = 'GMT + 1 timme (svensk vintertid)';
$lang['2'] = 'GMT + 2 timmar (svensk sommartid)';
$lang['3'] = 'GMT + 3 timmar';
$lang['3.5'] = 'GMT + 3.5 timmar';
$lang['4'] = 'GMT + 4 timmar';
$lang['4.5'] = 'GMT + 4.5 timmar';
$lang['5'] = 'GMT + 5 timmar';
$lang['5.5'] = 'GMT + 5.5 timmar';
$lang['6'] = 'GMT + 6 Timmar';
$lang['6.5'] = 'GMT + 6.5 timmar';
$lang['7'] = 'GMT + 7 timmar';
$lang['8'] = 'GMT + 8 timmar';
$lang['9'] = 'GMT + 9 timmar';
$lang['9.5'] = 'GMT + 9.5 timmar';
$lang['10'] = 'GMT + 10 timmar';
$lang['11'] = 'GMT + 11 timmar';
$lang['12'] = 'GMT + 12 timmar';
$lang['13'] = 'GMT + 13 timmar';

// These are displayed in the timezone select box
$lang['tz']['-12'] = 'GMT - 12 timmar';
$lang['tz']['-11'] = 'GMT - 11 timmar';
$lang['tz']['-10'] = 'GMT - 10 timmar';
$lang['tz']['-9'] = 'GMT - 9 timmar';
$lang['tz']['-8'] = 'GMT - 8 timmar';
$lang['tz']['-7'] = 'GMT - 7 timmar';
$lang['tz']['-6'] = 'GMT - 6 timmar';
$lang['tz']['-5'] = 'GMT - 5 timmar';
$lang['tz']['-4'] = 'GMT - 4 timmar';
$lang['tz']['-3.5'] = 'GMT - 3.5 timmar';
$lang['tz']['-3'] = 'GMT - 3 timmar';
$lang['tz']['-2'] = 'GMT - 2 timmar';
$lang['tz']['-1'] = 'GMT - 1 timme';
$lang['tz']['0'] = 'GMT';
$lang['tz']['1'] = 'GMT + 1 timme';
$lang['tz']['2'] = 'GMT + 2 timmar';
$lang['tz']['3'] = 'GMT + 3 timmar';
$lang['tz']['3.5'] = 'GMT + 3.5 timmar';
$lang['tz']['4'] = 'GMT + 4 timmar';
$lang['tz']['4.5'] = 'GMT + 4.5 timmar';
$lang['tz']['5'] = 'GMT + 5 timmar';
$lang['tz']['5.5'] = 'GMT + 5.5 timmar';
$lang['tz']['6'] = 'GMT + 6 timmar';
$lang['tz']['6.5'] = 'GMT + 6.5 timmar';
$lang['tz']['7'] = 'GMT + 7 timmar';
$lang['tz']['8'] = 'GMT + 8 timmar';
$lang['tz']['9'] = 'GMT + 9 timmar';
$lang['tz']['9.5'] = 'GMT + 9.5 timmar';
$lang['tz']['10'] = 'GMT + 10 timmar';
$lang['tz']['11'] = 'GMT + 11 timmar';
$lang['tz']['12'] = 'GMT + 12 timmar';
$lang['tz']['13'] = 'GMT + 13 timmar';

$lang['datetime']['Sunday'] = 'Söndag';
$lang['datetime']['Monday'] = 'Mĺndag';
$lang['datetime']['Tuesday'] = 'Tisdag';
$lang['datetime']['Wednesday'] = 'Onsdag';
$lang['datetime']['Thursday'] = 'Torsdag';
$lang['datetime']['Friday'] = 'Fredag';
$lang['datetime']['Saturday'] = 'Lördag';
$lang['datetime']['Sun'] = 'Sön';
$lang['datetime']['Mon'] = 'Mĺn';
$lang['datetime']['Tue'] = 'Tis';
$lang['datetime']['Wed'] = 'Ons';
$lang['datetime']['Thu'] = 'Tor';
$lang['datetime']['Fri'] = 'Fre';
$lang['datetime']['Sat'] = 'Lör';
$lang['datetime']['January'] = 'Januari';
$lang['datetime']['February'] = 'Februari';
$lang['datetime']['March'] = 'Mars';
$lang['datetime']['April'] = 'April';
$lang['datetime']['May'] = 'Maj';
$lang['datetime']['June'] = 'Juni';
$lang['datetime']['July'] = 'Juli';
$lang['datetime']['August'] = 'Augusti';
$lang['datetime']['September'] = 'September';
$lang['datetime']['October'] = 'Oktober';
$lang['datetime']['November'] = 'November';
$lang['datetime']['December'] = 'December';
$lang['datetime']['Jan'] = 'Jan';
$lang['datetime']['Feb'] = 'Feb';
$lang['datetime']['Mar'] = 'Mar';
$lang['datetime']['Apr'] = 'Apr';
$lang['datetime']['May'] = 'Maj';
$lang['datetime']['Jun'] = 'Jun';
$lang['datetime']['Jul'] = 'Jul';
$lang['datetime']['Aug'] = 'Aug';
$lang['datetime']['Sep'] = 'Sep';
$lang['datetime']['Oct'] = 'Okt';
$lang['datetime']['Nov'] = 'Nov';
$lang['datetime']['Dec'] = 'Dec';

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = 'Information';
$lang['Critical_Information'] = 'Kritisk Information';

$lang['General_Error'] = 'Fel';
$lang['Critical_Error'] = 'Kritiskt Fel';
$lang['An_error_occured'] = 'Ett Fel Inträffade';
$lang['A_critical_error'] = 'Ett Kritiskt Fel Inträffade';
// Login
$lang['Enter_password'] = 'Please enter your username and password to log in.';
$lang['Login'] = 'Log in';
$lang['Logout'] = 'Log out';
$lang['Forgotten_password'] = 'I forgot my password';
$lang['AUTOLOGIN'] = 'Log me on automatically each visit';
$lang['Error_login'] = 'You have specified an incorrect or inactive username, or an invalid password.'; 
// Login
$lang['Enter_password'] = 'Please enter your username and password to log in.';
$lang['Login'] = 'Log in';
$lang['Logout'] = 'Log out';
$lang['Forgotten_password'] = 'I forgot my password';
$lang['AUTOLOGIN'] = 'Log me on automatically each visit';
$lang['Error_login'] = 'You have specified an incorrect or inactive username, or an invalid password.'; 

$lang['Admin_reauthenticate'] = 'To administer the board you must re-authenticate yourself.';
$lang['Login_attempts_exceeded'] = 'The maximum number of %s login attempts has been exceeded. You are not allowed to login for the next %s minutes.';
$lang['Please_remove_install_contrib'] = 'Please ensure both the install/ and contrib/ directories are deleted';

//
// That's all, Folks!
// -------------------------------------------------

?>