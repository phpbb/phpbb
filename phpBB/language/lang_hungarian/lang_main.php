<?php
/***************************************************************************
 *                            lang_main.php [Hungarian]
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
 * forum -> fórum
 * category -> témakör
 * topic -> téma
 * post (new topic) -> új téma nyitása
 * post (a reply) -> hozzászólás
 * reply (in the forum) -> hozzászólás
 * reply (in the pm) -> válasz
 * message (in the forum) -> hozzászólás
 * message (in the pm) -> üzenet
 * theme -> séma
 * style -> stílus
 *
 * grep "XXX mauzi" for TODO's
 *
 ***************************************************************************/


//setlocale(LC_ALL, "en");
$lang['TRANSLATION_INFO'] = 'Magyar fordítás &copy; 2002 <a class="copyright" href="mailto:mauzi@expertlan.hu">Egerváry Gergely</a>';
$lang['ENCODING'] = "iso-8859-2";
$lang['DIRECTION'] = "ltr";
$lang['LEFT'] = "left";
$lang['RIGHT'] = "right";
$lang['DATE_FORMAT'] =  "Y M d"; // This should be changed to the default date format for your language, php date() format

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Fórum";
$lang['Category'] = "Témakör";
$lang['Topic'] = "Téma";
$lang['Topics'] = "Témák";
$lang['Replies'] = "Válaszok";
$lang['Views'] = "Megtekintve";
$lang['Post'] = "Hozzászólás";
$lang['Posts'] = "Hozzászólások";
$lang['Posted'] = "Elküldve";
$lang['Username'] = "Felhasználónév";
$lang['Password'] = "Jelszó";
$lang['Email'] = "Email";
$lang['Poster'] = "Hozzászóló";
$lang['Author'] = "Szerzõ";
$lang['Time'] = "Idõ";
$lang['Hours'] = "Óra";
$lang['Message'] = "Üzenet";

$lang['1_Day'] = "1 Nap";
$lang['7_Days'] = "7 Nap";
$lang['2_Weeks'] = "2 Hét";
$lang['1_Month'] = "1 Hónap";
$lang['3_Months'] = "3 Hónap";
$lang['6_Months'] = "6 Hónap";
$lang['1_Year'] = "1 Év";

$lang['Go'] = "Mehet";
$lang['Jump_to'] = "Ugrás";
$lang['Submit'] = "Mehet";
$lang['Reset'] = "Reset";
$lang['Cancel'] = "Mégse";
$lang['Preview'] = "Megtekint";
$lang['Confirm'] = "Megerõsítés";
$lang['Spellcheck'] = "Helyesírás";
$lang['Yes'] = "Igen";
$lang['No'] = "Nem";
$lang['Enabled'] = "Enabled";
$lang['Disabled'] = "Disabled";
$lang['Error'] = "Hiba";

$lang['Next'] = "Következõ";
$lang['Previous'] = "Elõzõ";
$lang['Goto_page'] = "Ugrás oldalra";
$lang['Joined'] = "Csatlakozott";
$lang['IP_Address'] = "IP cím";

$lang['Select_forum'] = "Válasszon fórumot";
$lang['View_latest_post'] = "Legfrissebb hozzászólás megtekintése";
$lang['View_newest_post'] = "Legújabb hozzászólás megtekintése ";
$lang['Page_of'] = "<b>%d</b> / <b>%d</b> oldal"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "ICQ";
$lang['AIM'] = "AIM";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "%s Tartalomjegyzék";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "Új téma nyitása";
$lang['Reply_to_topic'] = "Hozzászólás a témához";
$lang['Reply_with_quote'] = "Hozzászólás az elõzmények idézésével";

$lang['Click_return_topic'] = "Kattintson %side%s a témához való visszatéréshez"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "Kattintson %side%s az újabb próbálkozáshoz";
$lang['Click_return_forum'] = "Kattintson %side%s a fórumba való visszatéréshez";
$lang['Click_view_message'] = "Kattintson %side%s az üzenete megtekintéséhez";
$lang['Click_return_modcp'] = "Kattintson %side%s a Moderátor Vezérlõpulthoz való visszatéréshez";
$lang['Click_return_group'] = "Kattintson %side%s a csoport információkhoz való visszatéréshez";

$lang['Admin_panel'] = "Belépés az adminisztrációs felületre";

$lang['Board_disable'] = "Pardon, ez a fórum most nem hozzáférhetõ, próbálja késõbb";


//
// Global Header strings
//
$lang['Registered_users'] = "Regisztrált felhasználók:";
$lang['Browsing_forum'] = "A fórumot éppen böngészõ felhasználó:";
$lang['Online_users_zero_total'] = "Összesen <b>0</b> felhasználó van online :: ";
$lang['Online_users_total'] = "Összesen <b>%d</b> felhasználó van online - ";
$lang['Online_user_total'] = "Összesen <b>%d</b> felhasználó van online - ";
$lang['Reg_users_zero_total'] = "0 Regisztrált, ";
$lang['Reg_users_total'] = "%d Regisztrált, ";
$lang['Reg_user_total'] = "%d Regisztrált, ";
$lang['Hidden_users_zero_total'] = "0 Rejtett, ";
$lang['Hidden_user_total'] = "%d Rejtett, ";
$lang['Hidden_users_total'] = "%d Rejtett, ";
$lang['Guest_users_zero_total'] = "0 Vendég";
$lang['Guest_users_total'] = "%d Vendég";
$lang['Guest_user_total'] = "%d Vendég";
$lang['Record_online_users'] = "A legtöbb felhasználó egyszerre <b>%s</b> volt (%s)";

$lang['Admin_online_color'] = "%sAdminisztrátor%s";
$lang['Mod_online_color'] = "%sModerátor%s";

$lang['You_last_visit'] = "Utolsó látogatás ideje: %s"; // %s replaced by date/time
$lang['Current_time'] = "A pontos idõ: %s"; // %s replaced by time

$lang['Search_new'] = "Új hozzászólások";
$lang['Search_your_posts'] = "Saját hozzászólások";
$lang['Search_unanswered'] = "Megválaszolatlan hozzászólások";

$lang['Register'] = "Regisztráció";
$lang['Profile'] = "Profil";
$lang['Edit_profile'] = "Profil szerkesztése";
$lang['Search'] = "Keresés";
$lang['Memberlist'] = "Taglista";
$lang['FAQ'] = "GYIK";
$lang['BBCode_guide'] = "BBCode Kalauz";
$lang['Usergroups'] = "Csoportok";
$lang['Last_Post'] = "Utolsó hozzászólás";
$lang['Moderator'] = "Moderátor:";
$lang['Moderators'] = "Moderátorok:";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = " Felhasználóink összesen <b>%d</b> hozzászólást írtak."; // Number of posts
$lang['Posted_articles_total'] = "Felhasználóink összesen <b>%d</b> hozzászólást írtak."; // Number of posts
$lang['Posted_article_total'] = "Felhasználóink összesen <b>%d</b> hozzászólást írtak."; // Number of posts
$lang['Registered_users_zero_total'] = " Összesen <b>%d</b> regisztrált felhasználónk van."; // # registered users
$lang['Registered_users_total'] = "Összesen <b>%d</b> regisztrált felhasználónk van."; // # registered users
$lang['Registered_user_total'] = "Összesen <b>%d</b> regisztrált felhasználónk van."; // # registered users
$lang['Newest_user'] = "A legújabb felhasználónk: <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "Nincs új hozzászólás a legutolsó belépés óta.";
$lang['No_new_posts'] = "Nincs új hozzászólás";
$lang['New_posts'] = "Van új hozzászólás";
$lang['New_post'] = "Új hozzászólás";
$lang['No_new_posts_hot'] = "Nincs új hozzászólás [ Népszerû ]";
$lang['New_posts_hot'] = "Van új hozzászólás [ Népszerû ]";
$lang['No_new_posts_locked'] = "Nincs új hozzászólás [ Lezárva ]";
$lang['New_posts_locked'] = "Van új hozzászólás [ Lezárva ]";
$lang['Forum_is_locked'] = "Fórum lezárva";


//
// Login
//
$lang['Enter_password'] = "Írjon be egy felhasználónevet és egy jelszót a belépéshez";
$lang['Login'] = "Belépés";
$lang['Logout'] = "Kilépés";

$lang['Forgotten_password'] = "Elfelejtettem a jelszavam";

$lang['Log_me_in'] = "Automatikus belépés minden látogatásnál";

$lang['Error_login'] = "Rossz felhasználónév vagy jelszó";


//
// Index page
//
$lang['Index'] = "Tartalomjegyzék";
$lang['No_Posts'] = "Nincsenek hozzászólások";
$lang['No_forums'] = "Nincsenek fórumok";

$lang['Private_Message'] = "Privát üzenet";
$lang['Private_Messages'] = "Privát üzenetek";
$lang['Who_is_Online'] = "Online felhasználók";

$lang['Mark_all_forums'] = "Összes fórum olvasottá tétele";
$lang['Forums_marked_read'] = "Az összes fórum olvasottá téve";


//
// Viewforum
//
$lang['View_forum'] = "Fórum megtekintése";

$lang['Forum_not_exist'] = "A kért fórum nem létezik";
$lang['Reached_on_error'] = "Hiba: ezt az oldalt nem kellene elérnie";

$lang['Display_topics'] = "Témák megjelenítése";
$lang['All_Topics'] = "Összes téma";

$lang['Topic_Announcement'] = "<b>Hirdetmény:</b>";
$lang['Topic_Sticky'] = "<b>Fontos:</b>";
$lang['Topic_Moved'] = "<b>Áthelyezve:</b>";
$lang['Topic_Poll'] = "<b>[ Szavazás ]</b>";

$lang['Mark_all_topics'] = "Összes téma olvasottá tétele";
$lang['Topics_marked_read'] = "Az összes téma olvasottá téve";

$lang['Rules_post_can'] = "<b>Tud</b> új témát nyitni ebben a fórumban";
$lang['Rules_post_cannot'] = "<b>Nem tud</b> új témát nyitni ebben a fórumban";
$lang['Rules_reply_can'] = "<b>Tud</b> hozzászólni a témához ebben a fórumban";
$lang['Rules_reply_cannot'] = "<b>Nem tud</b> hozzászólni a témához ebben a fórumban";
$lang['Rules_edit_can'] = "<b>Tudja</b> szerkeszteni a saját hozzászólásait ebben a fórumban";
$lang['Rules_edit_cannot'] = "<b>Nem tudja</b> szerkeszteni a saját hozzászólásait ebben a fórumban";
$lang['Rules_delete_can'] = "<b>Tudja</b> törölni a saját hozzászólásait ebben a fórumban";
$lang['Rules_delete_cannot'] = "<b>Nem tudja</b> törölni a saját hozzászólásait ebben a fórumban";
$lang['Rules_vote_can'] = "<b>Tud</b> szavazni ebben a fórumban";
$lang['Rules_vote_cannot'] = "<b>Nem tud</b> szavazni ebben a fórumban";
$lang['Rules_moderate'] = "<b>Tudja</b> %smoderálni ezt a fórumot%s"; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = "Nincsenek témák ebben a fórumban. <br>Kattintson az <b>új téma nyitása</b> linkre egy új téma nyitásához";


//
// Viewtopic
//
$lang['View_topic'] = "Téma megtekintése";

$lang['Guest'] = 'Vendég';
$lang['Post_subject'] = "Hozzászólás tárgya";
$lang['View_next_topic'] = "Következõ téma";
$lang['View_previous_topic'] = "Elõzõ téma";
$lang['Submit_vote'] = "Szavazás";
$lang['View_results'] = "Eredmények";

$lang['No_newer_topics'] = "Nincsenek újabb témák ebben a fórumban";
$lang['No_older_topics'] = "Nincsenek régebbi témák ebben a fórumban";
$lang['Topic_post_not_exist'] = "A kért téma vagy hozzászólás nem létezik";
$lang['No_posts_topic'] = "Nincsenek hozzászólások ebben a témában";

$lang['Display_posts'] = "Hozzászólások megjelenítése"; 
$lang['All_Posts'] = "Összes hozzászólás";
$lang['Newest_First'] = "Újak elöl";
$lang['Oldest_First'] = "Régiek elöl";

$lang['Back_to_top'] = "Vissza a tetejére";

$lang['Read_profile'] = "Felhasználó profilja";
$lang['Send_email'] = "Email küldése";
$lang['Visit_website'] = "Weboldal megtekintése";
$lang['ICQ_status'] = "ICQ Státusz";
$lang['Edit_delete_post'] = "Hozzászólás szerkesztése/törlése";
$lang['View_IP'] = "Hozzászóló IP címe";
$lang['Delete_post'] = "Hozzászólás törlése";

$lang['wrote'] = "írta"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Idézet"; // comes before bbcode quote output.
$lang['Code'] = "Kód"; // comes before bbcode code output.

$lang['Edited_time_total'] = "Utoljára szerkesztette %s, %s, összesen %d alkalommal"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = " Utoljára szerkesztette %s, %s, összesen %d alkalommal"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Téma lezárása";
$lang['Unlock_topic'] = "Téma lezárás feloldása";
$lang['Move_topic'] = "Téma áthelyezése";
$lang['Delete_topic'] = "Téma törlése";
$lang['Split_topic'] = "Téma felosztása";

$lang['Stop_watching_topic'] = "Téma figyelés megszûntetése";
$lang['Start_watching_topic'] = "Téma figyelése új hozzászólásokról";
$lang['No_longer_watching'] = "Mostantól nem figyeli a téma hozzászólásait"; 
$lang['You_are_watching'] = "Mostantól figyeli a téma hozzászólásait";

$lang['Total_votes'] = "Szavazatok száma";

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Hozzászólás";
$lang['Topic_review'] = "Téma áttekintése";

$lang['No_post_mode'] = "Nem lett hozzászólási mód megadva"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)
 
$lang['Post_a_new_topic'] = "Új téma nyitása";
$lang['Post_a_reply'] = "Hozzászólás a témához";
$lang['Post_topic_as'] = "Téma típusa";
$lang['Edit_Post'] = "Hozzászólás szerkesztése";
$lang['Options'] = "Beállítások";

$lang['Post_Announcement'] = "Hirdetmény";
$lang['Post_Sticky'] = "Fontos";
$lang['Post_Normal'] = "Normál";

$lang['Confirm_delete'] = "Biztos benne, hogy törölni akarja ezt a hozzászólást?";
$lang['Confirm_delete_poll'] = "Biztos benne, hogy törölni akarja ezt a szavazást?";

$lang['Flood_Error'] = "Nem tud ilyen sûrûn hozzászólást írni. Próbálja újra egy rövid idõ múlva";
$lang['Empty_subject'] = "Meg kell adnia az üzenet tárgyát a hozzászóláshoz";
$lang['Empty_message'] = "Meg kell adnia az üzenetet a hozzászóláshoz";
$lang['Forum_locked'] = "Ez egy lezárt fórum, nem tud hozzászólni, vagy szerkeszteni a hozzászólásokat";
$lang['Topic_locked'] = "Ez egy lezárt téma, nem tud válaszolni, vagy szerkeszteni a hozzászólásokat";
$lang['No_post_id'] = "Ki kell választania egy hozzászólást a szerkesztéshez";
$lang['No_topic_id'] = "Ki kell választania egy témát a hozzászóláshoz";
$lang['No_valid_mode'] = "Érvényes hozzászólási módok: hozzászólás, válaszolás, szerkesztés, idézés. Próbálja újra";
$lang['No_such_post'] = "Nincs ilyen hozzászólás, próbálja újra";
$lang['Edit_own_posts'] = "Pardon, csak a saját hozzászólásait tudja szerkeszteni";
$lang['Delete_own_posts'] = "Pardon, csak a saját hozzászólásait tudja törölni";
$lang['Cannot_delete_replied'] = "Pardon, nem tud törölni olyan hozzászólást, amire válaszoltak";
$lang['Cannot_delete_poll'] = "Pardon, nem tud törölni aktív szavazást";
$lang['Empty_poll_title'] = "Meg kell adnia egy címet a szavazásnak";
$lang['To_few_poll_options'] = "Legalább két választási lehetõséget kell megadnia";
$lang['To_many_poll_options'] = "Túl sok választási lehetõséget adott meg";
$lang['Post_has_no_poll'] = "Ehhez a hozzászóláshoz nincs szavazat";

$lang['Add_poll'] = "Új szavazás";
$lang['Add_poll_explain'] = "Ha nem akar szavazást nyitni a témában, hagyja ezeket a mezõket üresen";
$lang['Poll_question'] = "Szavazás kérdése";
$lang['Poll_option'] = "Választási lehetõség";
$lang['Add_option'] = "Hozzáadás";
$lang['Update'] = "Frissítés";
$lang['Delete'] = "Törlés";
$lang['Poll_for'] = "Szavazás idõtartama";
$lang['Days'] = "nap"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ Írjon be nullát a végtelen szavazáshoz ]";
$lang['Delete_poll'] = "Szavazás törlése";

$lang['Disable_HTML_post'] = "HTML letiltása ebben a hozzászólásban";
$lang['Disable_BBCode_post'] = "BBCode letiltása ebben a hozzászólásban";
$lang['Disable_Smilies_post'] = "Emotikonok letiltása ebben a hozzászólásban";

$lang['HTML_is_ON'] = "HTML <u>engedélyezve</u>";
$lang['HTML_is_OFF'] = "HTML <u>tiltva</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s <u>engedélyezve</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s <u>tiltva</u>";
$lang['Smilies_are_ON'] = "Emotikonok <u>engedélyezve</u>";
$lang['Smilies_are_OFF'] = "Emotikonok <u>tiltva</u>";

$lang['Attach_signature'] = "Aláírás csatolása (az aláírás megváltoztatható a \"profil\" menüben)";
$lang['Notify'] = "Értesítés, ha válasz érkezik";
$lang['Delete_post'] = "Üzenet törlése";

$lang['Stored'] = "Köszönjük a hozzászólást";
$lang['Deleted'] = "A hozzászólás sikeresen törölve";
$lang['Poll_delete'] = "A szavazás sikeresen törölve";
$lang['Vote_cast'] = "Köszönjük a szavazást";

$lang['Topic_reply_notification'] = "Téma hozzászólás-értesítés";

$lang['bbcode_b_help'] = "Félkövér szöveg: [b]szöveg[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Dõlt szöveg: [i]szöveg[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Aláhúzott szöveg: [u]szöveg[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Idézett szöveg: [quote]idézet[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Kód megjelenítése: [code]kód[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Lista: [list]szöveg[/list] (alt+l)";
$lang['bbcode_o_help'] = "Számozott lista: [list=]szöveg[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Kép beszúrása: [img]http://kép_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "URL beszúrása: [url]http://url[/url] vagy [url=http://url]URL szöveg[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Összes BBCode tag bezárása";
$lang['bbcode_s_help'] = "Betûszín: [color=red]szöveg[/color]  Tipp: Így is lehet: color=#FF0000";
$lang['bbcode_f_help'] = "Betûméret: [size=x-small]apróbetûs szöveg[/size]";

$lang['Emoticons'] = "Emotikonok";
$lang['More_emoticons'] = "Még több Emotikon";

$lang['Font_color'] = "Betûszín";
$lang['color_default'] = "Alap";
$lang['color_dark_red'] = "Sötétpiros";
$lang['color_red'] = "Piros";
$lang['color_orange'] = "Narancs";
$lang['color_brown'] = "Barna";
$lang['color_yellow'] = "Sárga";
$lang['color_green'] = "Zöld";
$lang['color_olive'] = "Oliva";
$lang['color_cyan'] = "Cián";
$lang['color_blue'] = "Kék";
$lang['color_dark_blue'] = "Sötétkék";
$lang['color_indigo'] = "Indigó";
$lang['color_violet'] = "Lila";
$lang['color_white'] = "Fehér";
$lang['color_black'] = "Fekete";

$lang['Font_size'] = "Betûméret";
$lang['font_tiny'] = "Apró";
$lang['font_small'] = "Kicsi";
$lang['font_normal'] = "Normál";
$lang['font_large'] = "Nagy";
$lang['font_huge'] = "Óriási";

$lang['Close_Tags'] = "Tag-ek bezárása";
$lang['Styles_tip'] = "Tipp: A szöveg kijelölésével gyorsan módosíthatja a formátumát";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Privát üzenetek";

$lang['Login_check_pm'] = "Belépés a privát üzenetek megtekintéséhez";
$lang['New_pms'] = "Van %d új üzenet"; // You have 2 new messages
$lang['New_pm'] = "Van %d új üzenet"; // You have 1 new message
$lang['No_new_pm'] = "Nincs új üzenet";
$lang['Unread_pms'] = "Van %d olvasatlan üzenet";
$lang['Unread_pm'] = "Van %d olvasatlan üzenet";
$lang['No_unread_pm'] = "Nincs olvasatlan üzenet";
$lang['You_new_pm'] = "Egy új privát üzenet várja a Beérkezett Üzenetei között";
$lang['You_new_pms'] = "Új privát üzenetek várják a Beérkezett Üzenetei között";
$lang['You_no_new_pm'] = "Nincs új privát üzenete";

$lang['Inbox'] = "Beérkezett üzenetek";
$lang['Outbox'] = "Kimenõ üzenetek";
$lang['Savebox'] = "Elmentett üzenetek ";
$lang['Sentbox'] = "Elküldött üzenetek";
$lang['Flag'] = "Jel";
$lang['Subject'] = "Tárgy";
$lang['From'] = "Feladó";
$lang['To'] = "Címzett";
$lang['Date'] = "Dátum";
$lang['Mark'] = "Kijelölve";
$lang['Sent'] = "Elküldött";
$lang['Saved'] = "Elmentett";
$lang['Delete_marked'] = "Kijelöltek törlése";
$lang['Delete_all'] = "Összes törlése";
$lang['Save_marked'] = "Kijelöltek mentése"; 
$lang['Save_message'] = "Üzenet mentése";
$lang['Delete_message'] = "Üzenet törlése";

$lang['Display_messages'] = "Üzenetek megjelenítése"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "Összes üzenet";

$lang['No_messages_folder'] = "Nincs új üzenet ebben a mappában";

$lang['PM_disabled'] = "Privát üzenetek letiltva ezen a fórumon";
$lang['Cannot_send_privmsg'] = "Pardon, az adminisztrátor letiltotta a privát üzenetküldés lehetõségét";
$lang['No_to_user'] = "Meg kell adnia a címzett felhasználó azonosítóját";
$lang['No_such_user'] = "Pardon, ilyen felhasználó nem létezik";

$lang['Disable_HTML_pm'] = "HTML letiltása ebben az üzenetben";
$lang['Disable_BBCode_pm'] = "BBCode letiltása ebben az üzenetben";
$lang['Disable_Smilies_pm'] = "Emotikonok letiltása ebben az üzenetben";

$lang['Message_sent'] = "Az üzenet elküldve.";

$lang['Click_return_inbox'] = "Kattintson %side%s a Bejövõ Üzenetekhez való visszatéréshez";
$lang['Click_return_index'] = "Kattintson %side%s a Tartalomjegyzékhez való visszatéréshez";

$lang['Send_a_new_message'] = "Új privát üzenet küldése";
$lang['Send_a_reply'] = "Válaszolás privát üzenetre";
$lang['Edit_message'] = "Privát üzenet szerkesztése";

$lang['Notification_subject'] = "Új privát üzenete érkezett";

$lang['Find_username'] = "Felhasználó keresése";
$lang['Find'] = "Keres";
$lang['No_match'] = "Nincs találat";

$lang['No_post_id'] = "Nem lett üzenet ID megadva";
$lang['No_such_folder'] = "Ilyen mappa nem létezik";
$lang['No_folder'] = "Nem lett mappa megadva";

$lang['Mark_all'] = "Kijelöli mindet";
$lang['Unmark_all'] = "Egyiket sem";

$lang['Confirm_delete_pm'] = "Biztos benne, hogy le akarja törölni ezt az üzenetet?";
$lang['Confirm_delete_pms'] = "Biztos benne, hogy le akarja törölni ezeket az üzeneteket?";

$lang['Inbox_size'] = "A Beérkezett Üzenetek mappa %d%%-ban van tele"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Az Elküldött Üzenetek mappa %d%%-ban van tele"; 
$lang['Savebox_size'] = "Az Elmentett Üzenetek mappa %d%%-ban van tele"; 

$lang['Click_view_privmsg'] = "Kattintson %side%s a beérkezett üzenetek megtekintéséhez";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Profil megtekintése - %s"; // %s is username 
$lang['About_user'] = "Információk: %s"; // %s is username

$lang['Preferences'] = "Beállítások";
$lang['Items_required'] = "A csillaggal jelölt mezõket feltétlenül ki kell tölteni.";
$lang['Registration_info'] = "Regisztráció Információ";
$lang['Profile_info'] = "Profil Információ";
$lang['Profile_info_warn'] = "Az alábbi információk publikusan megtekinthetõek";
$lang['Avatar_panel'] = "Avatar Vezérlõpult";
$lang['Avatar_gallery'] = "Avatar galéria";

$lang['Website'] = "Weboldal";
$lang['Location'] = "Hely";
$lang['Contact'] = "Kapcsolatfelvétel:";
$lang['Email_address'] = "Email cím";
$lang['Email'] = "Email";
$lang['Send_private_message'] = "Privát üzenet küldése";
$lang['Hidden_email'] = "[ Rejtett ]";
$lang['Search_user_posts'] = "Keresés: a felhasználó hozzászólásai";
$lang['Interests'] = "Érdeklõdési kör";
$lang['Occupation'] = "Foglalkozás"; 
$lang['Poster_rank'] = "Rang";

$lang['Total_posts'] = "Összes hozzászólások";
$lang['User_post_pct_stats'] = "%.2f%% összesen"; // 1.25% of total
$lang['User_post_day_stats'] = "%.2f hozzászólás naponta"; // 1.5 posts per day
$lang['Search_user_posts'] = "Keresés: %s összes hozzászólása"; // Find all posts by username

$lang['No_user_id_specified'] = "Pardon, ilyen felhasználó nem létezik";
$lang['Wrong_Profile'] = "Nem lehet más profilját módosítani.";

$lang['Only_one_avatar'] = "Egyszerre csak egy avatar választható";
$lang['File_no_data'] = "A megadott URL nem tartalmaz adatot";
$lang['No_connection_URL'] = "A megadott URL nem érthetõ el";
$lang['Incomplete_URL'] = "A megadott URL hiányos";
$lang['Wrong_remote_avatar_format'] = "A megadott URL érvénytelen";
$lang['No_send_account_inactive'] = "Pardon, a jelszót nem lehet elküldeni, mert az azonosító jelenleg inaktív. Vegye fel a kapcsolatot a fórum adminisztrátorával további információért";

$lang['Always_smile'] = "Emotikonok engedélyezése";
$lang['Always_html'] = "HTML engedélyezése";
$lang['Always_bbcode'] = "BBCode engedélyezése";
$lang['Always_add_sig'] = "Aláírás csatolása";
$lang['Always_notify'] = "Értesítés a hozzászólásokról";
$lang['Always_notify_explain'] = "Küld egy email-t, amennyiben valaki válaszol valamelyik témában a hozzászólására. Ez minden hozzászólásnál beállítható";

$lang['Board_style'] = "Fórum stílusa";
$lang['Board_lang'] = "Fórum nyelve";
$lang['No_themes'] = "Nincsenek sémák az adatbázisban";
$lang['Timezone'] = "Idõzóna";
$lang['Date_format'] = "Dátum formátum";
$lang['Date_format_explain'] = "A szintakszis megegyezik a PHP <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> funkciójáéval";
$lang['Signature'] = "Aláírás";
$lang['Signature_explain'] = "Ez a szövegrészlet minden hozzászóláshoz csatolható. A maximális hossza %d karakter";
$lang['Public_view_email'] = "Az email cím mások által megtekinthetõ";

$lang['Current_password'] = "Jelenlegi jelszó";
$lang['New_password'] = "Új jelszó";
$lang['Confirm_password'] = "Új jelszó mégegyszer";
$lang['Confirm_password_explain'] = "Meg kell erõsíteni a jelszót, ha meg akarja változtatni, vagy megváltoztatta az email címét";
$lang['password_if_changed'] = "Csak akkor kell megadni új jelszót, ha meg akarja változtatni";
$lang['password_confirm_if_changed'] = "Csak akkor kell megerõsíteni az új jelszót, ha meg akarja változtatni";

$lang['Avatar'] = "Avatar"; 
$lang['Avatar_explain'] = "Megjelenít egy kis képet vagy grafikát a hozzászólásainál. Egyszerre csak egy kép jeleníthetõ meg, amelynek a maximális mérete 80x80 pixel, %dkB.";
$lang['Upload_Avatar_URL'] = "Avatar feltöltése megadott URL címrõl";
$lang['Upload_Avatar_URL_explain'] = "Adja meg a feltölteni kívánt avatar URL címét";
$lang['Pick_local_Avatar'] = "Válasszon avatart a galériából";
$lang['Link_remote_Avatar'] = "Adja meg a használni kívánt avatar URL címét";
$lang['Link_remote_Avatar_explain'] = "Írja be a használni kívánt avatar URL címét.";
$lang['Avatar_URL'] = "Avatar URL címe";
$lang['Select_from_gallery'] = "Avatar választása a galériából";
$lang['View_avatar_gallery'] = "Avatar galéria megtekintése";

$lang['Select_avatar'] = "Avatar kiválasztása";
$lang['Return_profile'] = "Mégsem";
$lang['Select_category'] = "Válasszon kategóriát";

$lang['Delete_Image'] = "Kép törlése";
$lang['Current_Image'] = "Jelenlegi kép";

$lang['Notify_on_privmsg'] = "Értesítsen, ha új privát üzenet érkezik";
$lang['Popup_on_privmsg'] = "Értesítõ ablak nyitása, ha új privát üzenet érkezik";
$lang['Popup_on_privmsg_explain'] = "Néhány séma új ablakot nyit, ha új privát üzenet érkezik"; 
$lang['Hide_user'] = "Online státusz elrejtése";

$lang['Profile_updated'] = "A profil frissítve lett.";
$lang['Profile_updated_inactive'] = "A profil frissítve lett, de olyan fontos adatok lettek benne megváltoztatva, hogy az azonosító inaktív, és újra kell aktiválni. Tekintse meg a leveleit a további információkért, vagy várja meg, hogy az adminisztrátor aktiválja az azonosítóját, amennyiben az szükséges";

$lang['Password_mismatch'] = "A beírt új jelszavak nem egyeznek meg";
$lang['Current_password_mismatch'] = "A jelenlegi jelszó nem egyezik meg az adatbázisban lévõvel";
$lang['Password_long'] = "A jelszó nem lehet hosszabb 32 karakternél";
$lang['Username_taken'] = "Pardon, ez a felhasználónév már foglalt";
$lang['Username_invalid'] = "Pardon, ez a felhasználónév érvénytelen karaktert tartalmaz";
$lang['Username_disallowed'] = "Pardon, ez a felhasználónév le van tiltva";
$lang['Email_taken'] = "Pardon, ezt az email címet már egy másik felhasználó használja";
$lang['Email_banned'] = "Pardon, ez az email cím le van tiltva";
$lang['Email_invalid'] = "Pardon, ez az email cím érvénytelen";
$lang['Signature_too_long'] = "Az aláírás túl hosszú";
$lang['Fields_empty'] = "Ki kell tölteni a szükséges mezõket";
$lang['Avatar_filetype'] = "Az avatar fájlnak .jpg, .gif or .png formátumúnak kell lennie";
$lang['Avatar_filesize'] = "Az avatar fájlnak kisebbnek kell lennie, mint %d kB"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "Az avatar képnek kisebbnek kell lennie %dx%d pixelnél";

$lang['Welcome_subject'] = "Üdvözöljük a %s fórumán"; // Welcome to my.com forums
$lang['New_account_subject'] = "Új felhasználói azonosító";
$lang['Account_activated_subject'] = "Azonosító aktiválva";

$lang['Account_added'] = "Köszönjük a regisztrálást, az azonosító elkészült.";
$lang['Account_inactive'] = "Az azonosító elkészült. A fórumba való belépés elõtt azonban az új azonosítót aktiválni kell. Az aktiváláshoz szükséges kulcsot elküldtük emailben. Kérjük, tekintse meg a leveleit a további információkért.";
$lang['Account_inactive_admin'] = " Az azonosító elkészült. A fórumba való belépés elõtt azonban az új azonosítót aktiválnia kell egy adminisztrátornak. Az aktiválásról emailben kap majd értesítést.";
$lang['Account_active'] = "Az azonosító aktiválva lett. Köszönjük a regisztrálást.";
$lang['Account_active_admin'] = " Az azonosító aktiválva lett.";
$lang['Reactivate'] = "Aktiválja újra az azonosítóját!";
$lang['COPPA'] = "Az azonosító elkészült, de engedélyeztetni kell, tekintse meg a leveleit a további információkért."; 

$lang['Registration'] = "Regisztrációs feltételek";
$lang['Reg_agreement'] = "Bár az adminisztrátorok és moderátorok mindent megtesznek, hogy eltávolítsák a kifogásolható hozzászólásokat amilyen gyorsan csak lehetséges, teljességgel lehetetlen minden egyes hozzászólást ellenõrizni. Ezért felhívjuk a figyelmét, hogy a fórumon olvasható hozzászólások a fórumon hozzászóló nézetét és véleményét tükrözik, nem pedig az adminisztrátorokét, moderátorokét, valamint a webmesterét, (természetesen kivételt képeznek ez alól az elõbb megnevezett személyek saját hozzászólásai) ezért a fórum üzemeltetõi a fórumon olvasható tartalomért semmilyen felelõsséget nem vállalnak.<br /><br />A regisztrálással egyetért azzal, hogy nem postáz sértõ, obszcén, vulgáris, rágalmazó, gyûlöletkeltõ valamint bármilyen más módon jogsértõ hozzászólásokat. A szabály megsértése esetén a fórum üzemeltetõi azonnal és véglegesen kitiltják a fórumról, valamint megteszik a szükséges lépéseket a felelõsségrevonáshoz. Minden hozzászóló IP címét rögzítjük. Egyetért azzal, hogy a fórum üzemeltetõi fenntartják maguknak a jogot, hogy töröljék, módosítsák, vagy lezárják azokat a témákat, amelyeket erre szükségesnek tartanak. Mint felhasználó, egyetért azzal, hogy a beírt személyes adatait az adatbázisunkban tároljuk. Az adatait a beleegyezése nélkül nem szolgáltatjuk ki harmadik félnek. A fórum üzemeltetõi a rendszert érõ támadásból származó adatbázis sérülésért és az adatok esetleges nyilvánosságra kerüléséért felelõsséget nem vállalnak.<br /><br />Ez a fórum cookie-kat használ információk tárolására. Ezek a cookie-k nem tartalmaznak semmi információt a fennt leírtakból, kizárólag a böngészést segítik elõ. A megadott email cím a regisztráció ellenõrzésére szolgál (és az új jelszó elküldésére, ha elfelejtené a jelenlegit).<br /><br />Amennyiben folytatni kívánja a regisztrációt, el kell fogadnia ezeket a feltételeket.";

$lang['Agree_under_13'] = "Egyetértek a feltételekkel, és 13 év <b>alatt</b> vagyok";
$lang['Agree_over_13'] = "Egyetértek a feltételekkel";
$lang['Agree_not'] = "Nem értek egyet a feltételekkel";

$lang['Wrong_activation'] = "A megadott aktiváló kulcs nem egyezik az adatbázisban szereplõvel";
$lang['Send_password'] = "Új jelszót kérek"; 
$lang['Password_updated'] = "Az új jelszó létrehozva, tekintse meg a leveleit további információkért";
$lang['No_email_match'] = "A megadott email cím nem egyezik meg az adatbázisban szereplõvel";
$lang['New_password_activation'] = "Új jelszó aktiválása";
$lang['Password_activated'] = "Az azonosító újra lett aktiválva. Bejelentkezhet azzal a felhasználónévvel és jelszóval, amit a korábbiakban kapott levélben.";

$lang['Send_email_msg'] = "Email üzenet küldése";
$lang['No_user_specified'] = "Nem lett felhasználó meghatározva";
$lang['User_prevent_email'] = "Ez a felhasználó nem fogad emailt. Küldjön inkább privát üzenetet";
$lang['User_not_exist'] = "Ilyen felhasználó nem létezik";
$lang['CC_email'] = "Másolat küldése saját címre"; 
$lang['Email_message_desc'] = "Az üzenet sima szövegként lesz elkülde, ne írjon bele HTML vagy BBCode tag-eket. A válaszcím a megadott email címére lesz beállítva.";
$lang['Flood_email_limit'] = "Nem tud most levelet küldeni. Próbálja késõbb";
$lang['Recipient'] = "Címzett";
$lang['Email_sent'] = "Az email elküldve";
$lang['Send_email'] = "Email küldése";
$lang['Empty_subject_email'] = "Meg kell adnia az üzenet tárgyát a levélküldéshez";
$lang['Empty_message_email'] = "Meg kell adnia az üzenetek a levélküldéshez";


//
// Memberslist
//
$lang['Select_sort_method'] = "Rendezés módja";
$lang['Sort'] = "Rendezés";
$lang['Sort_Top_Ten'] = "A tíz legaktívabb felhasználó";
$lang['Sort_Joined'] = "Csatlakozás ideje";
$lang['Sort_Username'] = "Felhasználónév";
$lang['Sort_Location'] = "Hely";
$lang['Sort_Posts'] = "Üzenetek száma";
$lang['Sort_Email'] = "Email";
$lang['Sort_Website'] = "Weboldal";
$lang['Sort_Ascending'] = "Növekvõ";
$lang['Sort_Descending'] = "Csökkenõ";
$lang['Order'] = "Sorrend";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Csoport Vezérlõpult";
$lang['Group_member_details'] = "Csoport tagság részletei";
$lang['Group_member_join'] = "Csatlakozás a csoporthoz";

$lang['Group_Information'] = "Csoport Információ";
$lang['Group_name'] = "Csoportnév";
$lang['Group_description'] = "Csoport leírása";
$lang['Group_membership'] = "Csoport tagság";
$lang['Group_Members'] = "Csoport Tag";
$lang['Group_Moderator'] = "Csoport Moderátor";
$lang['Pending_members'] = "Függõben lévõ Tag";

$lang['Group_type'] = "Csoport típusa";
$lang['Group_open'] = "Nyílt csoport";
$lang['Group_closed'] = "Zárt csoport";
$lang['Group_hidden'] = "Rejtett csoport";

$lang['Current_memberships'] = "Jelenlegi tagság";
$lang['Non_member_groups'] = "Nincs tagság";
$lang['Memberships_pending'] = "Függõben lévõ tagság";

$lang['No_groups_exist'] = "Nem léteznek csoportok";
$lang['Group_not_exist'] = "Ilyen csoport nem létezik";

$lang['Join_group'] = "Csatlakozás csoporthoz";
$lang['No_group_members'] = "Ennek a csoportnak nincsenek tagjai";
$lang['Group_hidden_members'] = "Ez egy rejtett csoport, nem lehet megtekinteni a tagjait";
$lang['No_pending_group_members'] = "Ennek a csoportnak nincsenek függõben lévõ tagjai";
$lang["Group_joined"] = "Sikeresen felíratkozott a csoportba.<br />A késõbbiekben kap értesítést arról, ha a csoport moderátor jóváhagyja a feliratkozását";
$lang['Group_request'] = "A request to join your group has been made"; // XXX mauzi
$lang['Group_approved'] = "A kérése elfogadva";
$lang['Group_added'] = "Sikeresen csatlakozott a csoporthoz"; 
$lang['Already_member_group'] = "Már tagja ennek a csoportnak";
$lang['User_is_member_group'] = "A felhasználó már tagja ennek a csoportnak";
$lang['Group_type_updated'] = "Csoport típus sikeresen frissítve";

$lang['Could_not_add_user'] = "A kért felhasználó nem létezik";
$lang['Could_not_anon_user'] = "Vendég nem csatlakozhat csoporthoz";

$lang['Confirm_unsub'] = "Biztos benne, hogy le akar iratkozni ebbõl a csoportból?";
$lang['Confirm_unsub_pending'] = "A felíratkozása még nem lett jóváhagyva. Biztos, hogy leíratkozik?";

$lang['Unsub_success'] = "Sikeresen leiratkozott ebbõl a csoportból";

$lang['Approve_selected'] = "Kijelöltek jóváhagyása";
$lang['Deny_selected'] = "Kijelöltek tiltása";
$lang['Not_logged_in'] = "Elõször be kell jelentkeznie, hogy csatlakozni tudjon ehhez a csoporthoz.";
$lang['Remove_selected'] = "Kijelöltek törlése";
$lang['Add_member'] = "Tag hozzáadása";
$lang['Not_group_moderator'] = "Nem moderátora ennek a csoportnak, ezért nem tudja végrehajtani ezt a tevékenységet.";

$lang['Login_to_join'] = "Bejelentkezés a csoport tagságok módosításához";
$lang['This_open_group'] = "Ez egy nyitott csoport, kattintson a tagságért";
$lang['This_closed_group'] = "Ez egy zárt csoport, több tag nem íratkozhat fel";
$lang['This_hidden_group'] = "Ez egy rejtett csoport, az felíratkozás nem engedélyezett";
$lang['Member_this_group'] = "Tagja ennek a csoportnak";
$lang['Pending_this_group'] = "A csoport tagsága függõben van";
$lang['Are_group_moderator'] = "Csoport moderátor";
$lang['None'] = "Nincs";

$lang['Subscribe'] = "Felíratkozás";
$lang['Unsubscribe'] = "Leíratkozás";
$lang['View_Information'] = "Információ megtekintése";


//
// Search
//
$lang['Search_query'] = "Keresés";
$lang['Search_options'] = "Keresés beállításai";

$lang['Search_keywords'] = "Keresés kulcsszóra";
$lang['Search_keywords_explain'] = "Használja az <u>AND</u> jelet azoknál a szavaknál, amelyeknek benne kell lenni, az <u>OR</u> jelet azoknál, amelyek benne lehetnek, és a <u>NOT</u> jelet azoknál, amelyek nem lehetnek benne a találatok között. Használja a  * karaktert, mint Joker";
$lang['Search_author'] = "Keresés szerzõre";
$lang['Search_author_explain'] = " Használja a  * karaktert, mint Joker";

$lang['Search_for_any'] = "Keresés bármelyik kulcsszóra, vagy a megadott feltételek alapján";
$lang['Search_for_all'] = "Keresés az összes kulcsszóra";
$lang['Search_title_msg'] = "Téma címekben és hozzászólások szövegében";
$lang['Search_msg_only'] = "Csak a hozzászólások szövegében";

$lang['Return_first'] = "Hozzászólások elsõ"; // followed by xxx characters in a select box
$lang['characters_posts'] = "karakterének megjelenítése";

$lang['Search_previous'] = "Keresés"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "Rendezés";
$lang['Sort_Time'] = "Üzenetküldés ideje";
$lang['Sort_Post_Subject'] = "Üzenet tárgya";
$lang['Sort_Topic_Title'] = "Téma cím";
$lang['Sort_Author'] = "Szerzõ";
$lang['Sort_Forum'] = "Fórum";

$lang['Display_results'] = "Eredmények megjelenítése";
$lang['All_available'] = "Összes elérhetõ";
$lang['No_searchable_forums'] = "Nincs joga a fórumokban keresni ezen az oldalon";

$lang['No_search_match'] = "Nincs téma vagy hozzászólás, amely megfelelne a keresési feltételeknek";
$lang['Found_search_match'] = "A keresés eredménye %d találat"; // eg. Search found 1 match
$lang['Found_search_matches'] = "A keresés eredménye %d találat"; // eg. Search found 24 matches

$lang['Close_window'] = "Ablak bezárása";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "Pardon, csak %s tud hirdetményeket küldeni ezen a fórumon";
$lang['Sorry_auth_sticky'] = "Pardon, csak %s tud fontos hozzászólásokat küldeni ezen a fórumon"; 
$lang['Sorry_auth_read'] = "Pardon, csak %s tud beleolvasni a témákba ezen a fórumon"; 
$lang['Sorry_auth_post'] = "Pardon, csak %s tud új témát nyitni ezen a fórumon"; 
$lang['Sorry_auth_reply'] = "Pardon, csak %s tud válaszolni a hozzászólásokra ezen a fórumon"; 
$lang['Sorry_auth_edit'] = "Pardon, csak %s tudja szerkeszteni a hozzászólásokat ezen a fórumon"; 
$lang['Sorry_auth_delete'] = "Pardon, csak %s tud hozzászólásokat törölni ezen a fórumon"; 
$lang['Sorry_auth_vote'] = "Pardon, csak %s tud szavazni ezen a fórumon"; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>névtelen felhasználó</b>";
$lang['Auth_Registered_Users'] = "<b>regisztrált felhasználó</b>";
$lang['Auth_Users_granted_access'] = "<b>kiemelt felhasználó</b>";
$lang['Auth_Moderators'] = "<b>moderátor</b>";
$lang['Auth_Administrators'] = "<b>adminisztrátor</b>";

$lang['Not_Moderator'] = "Nem moderátora ennek a fórumnak";
$lang['Not_Authorised'] = "Nincs azonosítva";

$lang['You_been_banned'] = "Ki lett tiltva errõl a fórumról.<br />Vegye fel a kapcsolatot a fórum adminisztrátorával további információkért";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "Nincs Regisztrált, és "; // There ae 5 Registered and
$lang['Reg_users_online'] = "Van %d Regisztrált, és "; // There ae 5 Registered and
$lang['Reg_user_online'] = "Van %d Regisztrált, és "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "nincs Rejtett felhasználó online"; // 6 Hidden users online
$lang['Hidden_users_online'] = "van %d Rejtett felhasználó online"; // 6 Hidden users online
$lang['Hidden_user_online'] = "van %d Rejtett felhasználó online"; // 6 Hidden users online
$lang['Guest_users_online'] = "Van %d Vendég felhasználó online"; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = "Nincs Vendég felhasználó online"; // There are 10 Guest users online
$lang['Guest_user_online'] = "Van %d Vendég felhasználó online"; // There is 1 Guest user online
$lang['No_users_browsing'] = "Jelenleg senki sem böngészi a fórumot";

$lang['Online_explain'] = "Az adatok az elmúlt 5 perc aktív felhasználóit tükrözik";

$lang['Forum_Location'] = "Böngészés Helye";
$lang['Last_updated'] = "Utoljára frissítve";

$lang['Forum_index'] = "Fórum tartalomjegyzék";
$lang['Logging_on'] = "Bejelentkezik";
$lang['Posting_message'] = "Üzenetet ír";
$lang['Searching_forums'] = "Keres a fórumokban";
$lang['Viewing_profile'] = "Profilt állít";
$lang['Viewing_online'] = "Megtekinti, hogy ki van online";
$lang['Viewing_member_list'] = "Megtekinti a taglistát";
$lang['Viewing_priv_msgs'] = "Privát üzeneteket olvas";
$lang['Viewing_FAQ'] = "GYIK-ot olvas";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Moderátor Vezérlõpult";
$lang['Mod_CP_explain'] = "Az alábbiakban moderálhatja a fórumot. Lezárhat témákat, feloldhatja témák lezárását, törölhet témákat, stb. ";

$lang['Select'] = "Kijelölés";
$lang['Delete'] = "Törlés";
$lang['Move'] = "Áthelyezés";
$lang['Lock'] = "Lezárás";
$lang['Unlock'] = "Lezárás feloldása";

$lang['Topics_Removed'] = "A kért témák sikeresen el lettek távolítva az adatbázisból.";
$lang['Topics_Locked'] = "A kért témák le lettek zárva";
$lang['Topics_Moved'] = "A kért témák át lettek helyezve";
$lang['Topics_Unlocked'] = "A kért témák lezárása fel lett oldva";
$lang['No_Topics_Moved'] = "Nem lett téma áthelyezve";

$lang['Confirm_delete_topic'] = "Biztos benne, hogy le akarja törölni a kijelölt témát/témákat?";
$lang['Confirm_lock_topic'] = " Biztos benne, hogy le akarja zárni a kijelölt témát/témákat?";
$lang['Confirm_unlock_topic'] = "Biztos benne, hogy fel akarja oldani a kijelölt téma/témák lezárását?";
$lang['Confirm_move_topic'] = " Biztos benne, hogy át akarja helyezni a kijelölt témát/témákat?";

$lang['Move_to_forum'] = "Áthelyezés";
$lang['Leave_shadow_topic'] = "A téma az eredeti helyén is megmarad";

$lang['Split_Topic'] = "Téma felosztása Vezérlõpult";
$lang['Split_Topic_explain'] = "Az alábbiakban feloszthat egy témát két új témára, vagy a hozzászólások kiválasztásával, vagy egy kiválasztott hozzászólásnál";
$lang['Split_title'] = "Új téma címe";
$lang['Split_forum'] = "Új téma helye";
$lang['Split_posts'] = "Felosztás kiválasztott hozzászólások alapján";
$lang['Split_after'] = "Felosztás egy kiválasztott hozzászólásnál";
$lang['Topic_split'] = "A kiválasztott téma sikeresen fel lett osztva";

$lang['Too_many_error'] = "Túl sok hozzászólást választott. Egy hozzászólást kell kiválasztania, amelyik után akarja felosztani a témát!";

$lang['None_selected'] = "Nem válaszott ki témát, amin végrehajthatná a kívánt mûveletet. Válasszon ki legalább egyet.";
$lang['New_forum'] = "Új fórum";

$lang['This_posts_IP'] = "Hozzászóló IP címe";
$lang['Other_IP_this_user'] = "Hozzászóló eddig használt IP címei";
$lang['Users_this_IP'] = "Felhasználók errõl az IP címrõl"; 
$lang['IP_info'] = "IP Információ";
$lang['Lookup_IP'] = "IP felderítése";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "Idõzóna: %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 óra";
$lang['-11'] = "GMT - 11 óra";
$lang['-10'] = "HST (Hawaii)";
$lang['-9'] = "GMT - 9 óra";
$lang['-8'] = "PST (U.S./Canada)";
$lang['-7'] = "MST (U.S./Canada)";
$lang['-6'] = "CST (U.S./Canada)";
$lang['-5'] = "EST (U.S./Canada)";
$lang['-4'] = "GMT - 4 óra";
$lang['-3.5'] = "GMT - 3.5 óra";
$lang['-3'] = "GMT - 3 óra";
$lang['-2'] = "Mid-Atlantic";
$lang['-1'] = "GMT - 1 óra";
$lang['0'] = "GMT";
$lang['1'] = "CET (Európa)";
$lang['2'] = "EET (Európa)";
$lang['3'] = "GMT + 3 óra";
$lang['3.5'] = "GMT + 3.5 óra";
$lang['4'] = "GMT + 4 óra";
$lang['4.5'] = "GMT + 4.5 óra";
$lang['5'] = "GMT + 5 óra";
$lang['5.5'] = "GMT + 5.5 óra";
$lang['6'] = "GMT + 6 óra";
$lang['7'] = "GMT + 7 óra";
$lang['8'] = "WST (Ausztrália)";
$lang['9'] = "GMT + 9 óra";
$lang['9.5'] = "CST (Ausztrália)";
$lang['10'] = "EST (Ausztrália)";
$lang['11'] = "GMT + 11 óra";
$lang['12'] = "GMT + 12 óra";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT -12:00 óra) Eniwetok, Kwajalein";
$lang['tz']['-11'] = "(GMT -11:00 óra) Midway Island, Samoa";
$lang['tz']['-10'] = "(GMT -10:00 óra) Hawaii";
$lang['tz']['-9'] = "(GMT -9:00 óra) Alaska";
$lang['tz']['-8'] = "(GMT -8:00 óra) Pacific Time (US &amp; Canada), Tijuana";
$lang['tz']['-7'] = "(GMT -7:00 óra) Mountain Time (US &amp; Canada), Arizona";
$lang['tz']['-6'] = "(GMT -6:00 óra) Central Time (US &amp; Canada), Mexico City";
$lang['tz']['-5'] = "(GMT -5:00 óra) Eastern Time (US &amp; Canada), Bogota, Lima, Quito";
$lang['tz']['-4'] = "(GMT -4:00 óra) Atlantic Time (Canada), Caracas, La Paz";
$lang['tz']['-3.5'] = "(GMT -3:30 óra) Newfoundland";
$lang['tz']['-3'] = "(GMT -3:00 óra) Brassila, Buenos Aires, Georgetown, Falkland Is";
$lang['tz']['-2'] = "(GMT -2:00 óra) Mid-Atlantic, Ascension Is., St. Helena";
$lang['tz']['-1'] = "(GMT -1:00 óra) Azores, Cape Verde Islands";
$lang['tz']['0'] = "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia";
$lang['tz']['1'] = "(GMT +1:00 óra) Amsterdam, Berlin, Brussels, Budapest, Madrid, Paris, Rome";
$lang['tz']['2'] = "(GMT +2:00 óra) Cairo, Helsinki, Kaliningrad, South Africa";
$lang['tz']['3'] = "(GMT +3:00 óra) Baghdad, Riyadh, Moscow, Nairobi";
$lang['tz']['3.5'] = "(GMT +3:30 óra) Tehran";
$lang['tz']['4'] = "(GMT +4:00 óra) Abu Dhabi, Baku, Muscat, Tbilisi";
$lang['tz']['4.5'] = "(GMT +4:30 óra) Kabul";
$lang['tz']['5'] = "(GMT +5:00 óra) Ekaterinburg, Islamabad, Karachi, Tashkent";
$lang['tz']['5.5'] = "(GMT +5:30 óra) Bombay, Calcutta, Madras, New Delhi";
$lang['tz']['6'] = "(GMT +6:00 óra) Almaty, Colombo, Dhaka, Novosibirsk";
$lang['tz']['6.5'] = "(GMT +6:30 óra) Rangoon";
$lang['tz']['7'] = "(GMT +7:00 óra) Bangkok, Hanoi, Jakarta";
$lang['tz']['8'] = "(GMT +8:00 óra) Beijing, Hong Kong, Perth, Singapore, Taipei";
$lang['tz']['9'] = "(GMT +9:00 óra) Osaka, Sapporo, Seoul, Tokyo, Yakutsk";
$lang['tz']['9.5'] = "(GMT +9:30 óra) Adelaide, Darwin";
$lang['tz']['10'] = "(GMT +10:00 óra) Canberra, Guam, Melbourne, Sydney, Vladivostok";
$lang['tz']['11'] = "(GMT +11:00 óra) Magadan, New Caledonia, Solomon Islands";
$lang['tz']['12'] = "(GMT +12:00 óra) Auckland, Wellington, Fiji, Marshall Island";

$lang['days_long'][0] = "Vasárnap";
$lang['days_long'][1] = "Hétfõ";
$lang['days_long'][2] = "Kedd";
$lang['days_long'][3] = "Szerda";
$lang['days_long'][4] = "Csütörtök";
$lang['days_long'][5] = "Péntek";
$lang['days_long'][6] = "Szombat";

$lang['days_short'][0] = "V";
$lang['days_short'][1] = "H";
$lang['days_short'][2] = "K";
$lang['days_short'][3] = "Sze";
$lang['days_short'][4] = "Cs";
$lang['days_short'][5] = "P";
$lang['days_short'][6] = "Szo";

$lang['months_long'][0] = "Január";
$lang['months_long'][1] = "Február";
$lang['months_long'][2] = "Március";
$lang['months_long'][3] = "Április";
$lang['months_long'][4] = "Május";
$lang['months_long'][5] = "Június";
$lang['months_long'][6] = "Július";
$lang['months_long'][7] = "Augusztus";
$lang['months_long'][8] = "Szeptember";
$lang['months_long'][9] = "Október";
$lang['months_long'][10] = "November";
$lang['months_long'][11] = "December";

$lang['months_short'][0] = "Jan";
$lang['months_short'][1] = "Feb";
$lang['months_short'][2] = "Már";
$lang['months_short'][3] = "Ápr";
$lang['months_short'][4] = "Máj";
$lang['months_short'][5] = "Jún";
$lang['months_short'][6] = "Júl";
$lang['months_short'][7] = "Aug";
$lang['months_short'][8] = "Sze";
$lang['months_short'][9] = "Okt";
$lang['months_short'][10] = "Nov";
$lang['months_short'][11] = "Dec";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "Információ";
$lang['Critical_Information'] = "Kritikus Információ";

$lang['General_Error'] = "Általános Hiba";
$lang['Critical_Error'] = "Kritikus Hiba";
$lang['An_error_occured'] = "Hiba történt";
$lang['A_critical_error'] = "Kritikus hiba történt";

//
// That's all Folks!
// -------------------------------------------------

?>
