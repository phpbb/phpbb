<?php
/***************************************************************************
 *                            lang_main.php [Czech]
 *                            ---------------------
 *     characterset         : Windows-1250
 *     phpBB version        : 2.0.2
 *     copyright            : (c) 2002 The phpBB CZ Group
 *     translation          : azu@atmplus.cz
 *     www                  : http://phpbb.atmplus.cz
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
// Add your details here if wanted, e.g. Name, username, email address, website
//

//
// The format of this file is ---> $lang['message'] = 'text';
//
// You should also try to set a locale and a character encoding (plus direction). The encoding and direction
// will be sent to the template. The locale may or may not work, it's dependent on OS support and the syntax
// varies ... give it your best guess!
//

$lang['ENCODING'] = "Windows-1250";
$lang['DIRECTION'] = "LTR";
$lang['LEFT'] = "LEFT";
$lang['RIGHT'] = "RIGHT";
$lang['DATE_FORMAT'] =  "d. m. Y"; // This should be changed to the default date format for your language, php date() format

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.
// $lang['TRANSLATION'] = '';

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Fórum";
$lang['Category'] = "Kategorie";
$lang['Topic'] = "Téma";
$lang['Topics'] = "Témata";
$lang['Replies'] = "Odpovìdi";
$lang['Views'] = "Shlédnuto";
$lang['Post'] = "Pøíspìvek";
$lang['Posts'] = "Pøíspìvky";
$lang['Posted'] = "Zaslal";
$lang['Username'] = "Uživatel";
$lang['Password'] = "Heslo";
$lang['Email'] = "E-mail";
$lang['Poster'] = "Odesílatel";
$lang['Author'] = "Autor";
$lang['Time'] = "Èas";
$lang['Hours'] = "Hodin";
$lang['Message'] = "Zpráva";

$lang['1_Day'] = "1 den";
$lang['7_Days'] = "1 týden";
$lang['2_Weeks'] = "2 týdny";
$lang['1_Month'] = "1 mìsíc";
$lang['3_Months'] = "3 mìsíce";
$lang['6_Months'] = "6 mìsícù";
$lang['1_Year'] = "1 rok";

$lang['Go'] = "jdi";
$lang['Jump_to'] = "Pøejdi na";
$lang['Submit'] = "Odeslat";
$lang['Reset'] = "Pùvodní hodnoty";
$lang['Cancel'] = "Storno";
$lang['Preview'] = "Náhled";
$lang['Confirm'] = "Potvrdit";
$lang['Spellcheck'] = "Kontrola pravopisu";
$lang['Yes'] = "Ano";
$lang['No'] = "Ne";
$lang['Enabled'] = "Povoleno";
$lang['Disabled'] = "Zakázáno";
$lang['Error'] = "Chyba";

$lang['Next'] = "Další";
$lang['Previous'] = "Pøedchozí";
$lang['Goto_page'] = "Jdi na stránku";
$lang['Joined'] = "Založen";
$lang['IP_Address'] = "IP Adresa";

$lang['Select_forum'] = "Zvolte fórum";
$lang['View_latest_post'] = "Zobrazit poslední pøíspìvek";
$lang['View_newest_post'] = "Zobraz nejnovìjší pøíspìvky";
$lang['Page_of'] = "Strana <b>%d</b> z <b>%d</b>"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "ICQ";
$lang['AIM'] = "AOL Instant Messenger";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "Obsah fóra %s";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "odeslat nové téma";
$lang['Reply_to_topic'] = "Odpovìdìt na téma";
$lang['Reply_with_quote'] = "Odpovìdìt s citátem";

$lang['Click_return_topic'] = "Kliknìte %szde%s pro návrat do seznamu témat"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "Kliknìte %szde%s pro opakování volby";
$lang['Click_return_forum'] = "Kliknìte %szde%s pro návrat do na obsah fóra";
$lang['Click_view_message'] = "Kliknìte %szde%s pro zobrazení vaší zprávy";
$lang['Click_return_modcp'] = "Kliknìte %szde%s pro návrat do moderátorského ovládacího panelu";
$lang['Click_return_group'] = "Kliknìte %szde%s pro návrat do informací o skupinách";

$lang['Admin_panel'] = "Administrace fóra";

$lang['Board_disable'] = "Promiòte, ale toto fórum je momentálnì nedostupné, zkuste opakovat volbu pozìdji";


//
// Global Header strings
//
$lang['Registered_users'] = "Registrovaní uživatelé:";
$lang['Browsing_forum'] = "Uživatelé prohlížející toto fórum:";
$lang['Online_users_zero_total'] = "Celkem je zde pøítomno <b>0</b> uživatelù  : ";
$lang['Online_users_total'] = "Celkem je zde pøítomno <b>%d</b> uživatelù : ";
$lang['Online_user_total'] = "Celkem je zde pøítomen  <b>%d</b> uživatel : ";
$lang['Reg_users_zero_total'] = "0 registrovaných, ";
$lang['Reg_users_total'] = "%d registrovaných, ";
$lang['Reg_user_total'] = "%d registrovaný, ";
$lang['Hidden_users_zero_total'] = "0 skrytých a ";
$lang['Hidden_user_total'] = "%d skrytý a ";
$lang['Hidden_users_total'] = "%d skrytých a ";
$lang['Guest_users_zero_total'] = "0 anonymních";
$lang['Guest_users_total'] = "%d Anonymních";
$lang['Guest_user_total'] = "%d Anonymní";
$lang['Record_online_users'] = "Nejvíce zde bylo souèasnì pøítomno <b>%s</b> uživatelù dne %s"; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = "%sAdministrátoøi%s";
$lang['Mod_online_color'] = "%sModerátoøi%s";

$lang['You_last_visit'] = "Naposledy jste zde byl %s"; // %s replaced by date/time
$lang['Current_time'] = "Právì je %s"; // %s replaced by time

$lang['Search_new'] = "Zobrazit nové pøíspìvky od poslední návštìvy";
$lang['Search_your_posts'] = "Zobrazit vaše pøíspìvky";
$lang['Search_unanswered'] = "Zobrazit pøíspìvky bez odpovìdí";

$lang['Register'] = "Registrace";
$lang['Profile'] = "Nastavení";
$lang['Edit_profile'] = "Zmìna nastavení";
$lang['Search'] = "Hledat";
$lang['Memberlist'] = "Seznam uživatelù";
$lang['FAQ'] = "FAQ";
$lang['BBCode_guide'] = "Prùvodce znaèkami";
$lang['Usergroups'] = "Uživatelské skupiny";
$lang['Last_Post'] = "Poslední pøíspìvek";
$lang['Moderator'] = "Moderátor";
$lang['Moderators'] = "Moderátoøi";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "Uživatelé zaslali celkem <b>0</b> pøíspìvkù"; // Number of posts
$lang['Posted_articles_total'] = "Uživatelé zaslali celkem <b>%d</b> pøíspìvkù"; // Number of posts
$lang['Posted_article_total'] = "Uživatelé zaslali celkem <b>%d</b> pøíspìvek"; // Number of posts
$lang['Registered_users_zero_total'] = "Je zde <b>0</b> registrovaných uživatelù"; // # registered users
$lang['Registered_users_total'] = "Je zde <b>%d</b> registrovaných uživatelù"; // # registered users
$lang['Registered_user_total'] = "Je zde <b>%d</b> registrovaný uživatel"; // # registered users
$lang['Newest_user'] = "Nejnovìjším registrovaným uživatelem je <b>%s%s%s</b>"; // a href, username, /a

$lang['No_new_posts_last_visit'] = "Žádné nové pøíspìvky od vaší poslední návštìvy";
$lang['No_new_posts'] = "Žádné nové pøíspìvky";
$lang['New_posts'] = "Nové pøíspìvky";
$lang['New_post'] = "Nový pøíspìvek";
$lang['No_new_posts_hot'] = "Žádné nové pøíspìvky [ oblíbené ]";
$lang['New_posts_hot'] = "Nové pøíspìvky [ oblíbené ]";
$lang['No_new_posts_locked'] = "Žádné nové pøíspìvky [ zamknuto ]";
$lang['New_posts_locked'] = "Nové pøíspìvky [ zamknuto ]";
$lang['Forum_is_locked'] = "Fórum je zamknuto";


//
// Login
//
$lang['Enter_password'] = "Zadejte prosím vaše uživatelské jméno a heslo";
$lang['Login'] = "Pøihlášení";
$lang['Logout'] = "Odhlášení";

$lang['Forgotten_password'] = "Zapomìli jste svoje heslo ?";

$lang['Log_me_in'] = "Pøihlásit automaticky pøi pøíští návštìvì";

$lang['Error_login'] = "Bylo zadáno neplatné uživatelské jméno nebo heslo";


//
// Index page
//
$lang['Index'] = "Fórum";
$lang['No_Posts'] = "Žádné pøíspìvky";
$lang['No_forums'] = "Žádná fóra";

$lang['Private_Message'] = "Soukromá zpráva";
$lang['Private_Messages'] = "Soukromé zprávy";
$lang['Who_is_Online'] = "Kdo je pøítomen";

$lang['Mark_all_forums'] = "Oznaèit všechna fóra jako pøeètená";
$lang['Forums_marked_read'] = "Všechna fóra byla oznaèena jako pøeètená";


//
// Viewforum
//
$lang['View_forum'] = "Zobrazit fórum";

$lang['Forum_not_exist'] = "Zvolené fórum neexistuje";
$lang['Reached_on_error'] = "Došlo k chybì na této stránce";

$lang['Display_topics'] = "Zobrazit témata za pøedchozí";
$lang['All_Topics'] = "Všechna témata";

$lang['Topic_Announcement'] = "<b>Oznámení:</b>";
$lang['Topic_Sticky'] = "<b>Dùležité:</b>";
$lang['Topic_Moved'] = "<b>Pøesunout:</b>";
$lang['Topic_Poll'] = "<b>[ Hlasování ]</b>";

$lang['Mark_all_topics'] = "Oznaèit všechna témata jako pøeètená";
$lang['Topics_marked_read'] = "Témata tohoto fóra byla oznaèena jako pøeètená";

$lang['Rules_post_can'] = "<b>Mùžete</b> odesílat nové téma do tohoto fóra";
$lang['Rules_post_cannot'] = "<b>Nemùžete</b> odesílat nové téma do tohoto fóra";
$lang['Rules_reply_can'] = "<b>Mùžete</b> odpovídat na témata v tomto fóru";
$lang['Rules_reply_cannot'] = "<b>Nemùžete</b> odpovídat na témata v tomto fóru";
$lang['Rules_edit_can'] = "<b>Mùžete</b> upravovat své pøíspìvky v tomto fóru";
$lang['Rules_edit_cannot'] = "<b>Nemùžete</b> upravovat své pøíspìvky v tomto fóru";
$lang['Rules_delete_can'] = "<b>Mùžete</b> mazat své pøíspìvky v tomto fóru";
$lang['Rules_delete_cannot'] = "<b>Nemùžete</b> mazat své pøíspìvky v tomto fóru";
$lang['Rules_vote_can'] = "<b>Mùžete</b> hlasovat v tomto fóru";
$lang['Rules_vote_cannot'] = "<b>Nemùžete</b> hlasovat v tomto fóru";
$lang['Rules_moderate'] = "<b>Mùžete</b> %smoderovat toto fórum%s"; // %s replaced by a href links, do not remove!

$lang['No_topics_post_one'] = "Toto fórum neobsahuje žádná témata<br />Kliknìte na odkaz <b>Nové téma</b> pro pøidání nového tématu.";


//
// Viewtopic
//
$lang['View_topic'] = "Zobrazit téma";

$lang['Guest'] = 'Anonymní';
$lang['Post_subject'] = "Pøedmìt";
$lang['View_next_topic'] = "Zobrazit následující téma";
$lang['View_previous_topic'] = "Zobrazit pøedchozí téma";
$lang['Submit_vote'] = "Odeslat hlas";
$lang['View_results'] = "Zobrazit výsledek";

$lang['No_newer_topics'] = "V tomto fóru nejsou žádná novìjší témata";
$lang['No_older_topics'] = "V tomto fóru nejsou žádná starší témata";
$lang['Topic_post_not_exist'] = "Téma nebo pøíspìvek který požadujete nebyl nalezen";
$lang['No_posts_topic'] = "Pro toto téma neexistují žádné pøíspìvky";

$lang['Display_posts'] = "Zobrazit pøíspìvky z pøedchozích";
$lang['All_Posts'] = "Všechny pøíspìvky";
$lang['Newest_First'] = "Nejdøíve nejnovìjší";
$lang['Oldest_First'] = "Nejdøíve nejstarší";

$lang['Back_to_top'] = "Návrat nahoru";

$lang['Read_profile'] = "Zobrazit informace o autorovi";
$lang['Send_email'] = "Odeslat e-mail autorovi";
$lang['Visit_website'] = "Zobrazit autorovi WWW stránky";
$lang['ICQ_status'] = "ICQ stav";
$lang['Edit_delete_post'] = "Upravit/Odstranit tento pøíspìvek";
$lang['View_IP'] = "Zobrazit IP adresu odesílatele";
$lang['Delete_post'] = "Odstranit tento pøíspìvek";

$lang['wrote'] = "napsal"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "citace"; // comes before bbcode quote output.
$lang['Code'] = "kód"; // comes before bbcode code output.

$lang['Edited_time_total'] = "Naposledy upravil %s dne %s, celkovì upraveno %d krát"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "Naposledy upravil %s dne %s, celkovì upraveno %d krát"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Zamknout toto téma";
$lang['Unlock_topic'] = "Odemknout toto téma";
$lang['Move_topic'] = "Pøesunout toto téma";
$lang['Delete_topic'] = "Odstranit toto téma";
$lang['Split_topic'] = "Rozdìlit toto téma";

$lang['Stop_watching_topic'] = "Ukonèit sledování tohoto tématu";
$lang['Start_watching_topic'] = "Sledovat odpovìdi na toto téma";
$lang['No_longer_watching'] = "Pøestal(a) jste sledovat toto téma";
$lang['You_are_watching'] = "Zaèal(a) jste sledovat toto téma";

$lang['Total_votes'] = "Celkem hlasù";

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Tìlo zprávy";
$lang['Topic_review'] = "Pøehled tématu";

$lang['No_post_mode'] = "Nebyl zvolen typ odeslání"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = "Odeslat nové téma";
$lang['Post_a_reply'] = "Odeslat odpovìï";
$lang['Post_topic_as'] = "Odeslat téma jako";
$lang['Edit_Post'] = "Upravit pøíspìvek";
$lang['Options'] = "Pøedvolby";

$lang['Post_Announcement'] = "Oznámení";
$lang['Post_Sticky'] = "Dùležité";
$lang['Post_Normal'] = "Normální";

$lang['Confirm_delete'] = "Opravdu chcete odstranit tento pøíspìvek ?";
$lang['Confirm_delete_poll'] = "Opravdu chcete odstranit toto vaše hlasování ?";

$lang['Flood_Error'] = "Nemùžete odeslat nový pøíspìvek takto brzy po pøedchozím pøíspìvku, chvíli vyèkejte a zkuste to znovu";
$lang['Empty_subject'] = "Musíte zadat text pøedmìtu";
$lang['Empty_message'] = "Musíte zadat text pøíspìvku";
$lang['Forum_locked'] = "Toto fórum je zamknuto, nemùžete zde psát ani upravovat pøíspìvky";
$lang['Topic_locked'] = "Toto téma je zamknuto bez možnosti úpravy pøíspìvkù a psaní odpovìdí";
$lang['No_post_id'] = "Musíte zvolit pøíspìvek pro úpravu";
$lang['No_topic_id'] = "Musíte zvolit téma na které chcete odpovìdìt";
$lang['No_valid_mode'] = "Mùžete jen odesílat, upravovat nebo citovat pøíspìvky, proveïte návrat zpìt a zkuste to znovu";
$lang['No_such_post'] = "Takovýto pøíspìvek neexistuje, proveïte návrat zpìt a zkuste to znovu";
$lang['Edit_own_posts'] = "Promiòte, ale mùžete upravovat jen své pøíspìvky";
$lang['Delete_own_posts'] = "Promiòte, ale mùžete mazat jen své pøíspìvky";
$lang['Cannot_delete_replied'] = "Promiòte, ale nemùžete mazat pøíspìvky, na které bylo odpovìzeno";
$lang['Cannot_delete_poll'] = "Promiòte, ale nemùžete vymazat aktivní hlasování";
$lang['Empty_poll_title'] = "Musíte napsat hlasovací otázku";
$lang['To_few_poll_options'] = "Musíte napsat alespoò dvì hlasovací možnosti";
$lang['To_many_poll_options'] = "Pokoušíte se napsat pøíliš mnoho hlasovacích možností";
$lang['Post_has_no_poll'] = "Tento pøíspìvek nemá hlasování";
$lang['Already_voted'] = "V tomto hlasování jste již hlasoval(a)";
$lang['No_vote_option'] = "Pøi hlasování musíte zvolit nìkterou z možností";

$lang['Add_poll'] = "Pøidat Hlasování";
$lang['Add_poll_explain'] = "Jestliže nechcete pøidat možnost hlasování k tomuto tématu, nechte pole prázdná";
$lang['Poll_question'] = "Hlasovací otázka";
$lang['Poll_option'] = "Možné odpovìdi";
$lang['Add_option'] = "Pøidat odpovìï";
$lang['Update'] = "Aktualizovat";
$lang['Delete'] = "Odstranit";
$lang['Poll_for'] = "Délka trvání";
$lang['Days'] = "dní"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "(zadejte 0 nebo nevyplòujte pro neomezenou dobu hlasování)";
$lang['Delete_poll'] = "Delete Poll";

$lang['Disable_HTML_post'] = "Zakázat HTML v tomto pøíspìvku";
$lang['Disable_BBCode_post'] = "Zakázat znaèky v tomto pøíspìvku";
$lang['Disable_Smilies_post'] = "Zakázat Smajlíky v tomto pøíspìvku";

$lang['HTML_is_ON'] = "HTML: <u>POVOLENO</u>";
$lang['HTML_is_OFF'] = "HTML: <u>VYPNUTO</u>";
$lang['BBCode_is_ON'] = "%sZnaèky%s: <u>POVOLENY</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sZnaèky%s: <u>VYPNUTY</u>";
$lang['Smilies_are_ON'] = "Smajlíky: <u>POVOLENY</u>";
$lang['Smilies_are_OFF'] = "Smajlíky: <u>VYPNUTY</u>";

$lang['Attach_signature'] = "Pøipojit podpis (podpis mùžete zmìnit ve vašem nastavení)";
$lang['Notify'] = "Upozornit mne, pøijde-li odpovìï";
$lang['Delete_post'] = "Odstranit tento pøíspìvek";

$lang['Stored'] = "Vaše zpráva byla úspìšnì odeslána";
$lang['Deleted'] = "Vaše zpráva byla úspìšnì odstranìna";
$lang['Poll_delete'] = "Vás hlas byl úspìšnì odstranìn";
$lang['Vote_cast'] = "Váš hlas byl pøijat";

$lang['Topic_reply_notification'] = "Upozornìní na odpovìï";

$lang['bbcode_b_help'] = "Tuèné: [b]text[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Kurzíva: [i]text[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Podtržené: [u]text[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Citace: [quote]text[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Zobrazení kódu: [code]code[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Seznam: [list]text[/list] (alt+l)";
$lang['bbcode_o_help'] = "Uspoøádaný seznam: [list=]text[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Vložit obrázek: [img]http://image_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "Vložit URL: [url]http://url[/url] or [url=http://url]URL text[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Zavøe všechny otevøené znaèky";
$lang['bbcode_s_help'] = "Barva písma: [color=red]text[/color]  Typ: mùžete použít také color=#FF0000";
$lang['bbcode_f_help'] = "Velikost písma: [size=x-small]malý text[/size]";

$lang['Emoticons'] = "Smajlíky (emotikony)";
$lang['More_emoticons'] = "Zobrazit další smajlíky (emotikony)";

$lang['Font_color'] = "Barva písma";
$lang['color_default'] = "Výchozí";
$lang['color_dark_red'] = "Kaštanová";
$lang['color_red'] = "Èervená";
$lang['color_orange'] = "Oranžová";
$lang['color_brown'] = "Hnìdá";
$lang['color_yellow'] = "Žlutá";
$lang['color_green'] = "Zelená";
$lang['color_olive'] = "Olivová";
$lang['color_cyan'] = "Akvamarínová";
$lang['color_blue'] = "Modrá";
$lang['color_dark_blue'] = "Tmavì modrá";
$lang['color_indigo'] = "Fialová";
$lang['color_violet'] = "Fuchsiová";
$lang['color_white'] = "Bílá";
$lang['color_black'] = "Èerná";

$lang['Font_size'] = "Velikost písma";
$lang['font_tiny'] = "Drobné";
$lang['font_small'] = "Malé";
$lang['font_normal'] = "Výchozí";
$lang['font_large'] = "Velké";
$lang['font_huge'] = "Obrovské";

$lang['Close_Tags'] = "zavøít znaèky";
$lang['Styles_tip'] = "Typ: Styl mùžete aplikovat rychleji na oznaèeném textu";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Soukromé zprávy";

$lang['Login_check_pm'] = "Pøihlásit, pro kontrolu soukromých zpráv";
$lang['New_pms'] = "Máte %d nových zprávy"; // You have 2 new messages
$lang['New_pm'] = "Máte %d novou zprávu"; // You have 1 new message
$lang['No_new_pm'] = "Nemáte nové zprávy";
$lang['Unread_pms'] = "Máte %d nepøeètené zprávy";
$lang['Unread_pm'] = "Máte %d nepøeètenou zprávu";
$lang['No_unread_pm'] = "Nemáte nepøeètené zprávy";
$lang['You_new_pm'] = "Nová soukromá zpráva èeká na pøeètení v doruèených zprávách";
$lang['You_new_pms'] = "Nové soukromé zprávy èekají na pøeètení v doruèených zprávách";
$lang['You_no_new_pm'] = "Žádné nové soukromé zprávy neèekají";

$lang['Unread_message'] = 'Nepøeètená zpráva';
$lang['Read_message'] = 'Èíst zprávu';

$lang['Read_pm'] = 'Èíst zprávu';
$lang['Post_new_pm'] = 'Poslat zprávu';
$lang['Post_reply_pm'] = 'Odpovìdìt na zprávu';
$lang['Post_quote_pm'] = 'Citovat ze zprávy';
$lang['Edit_pm'] = 'Upravit zprávu';

$lang['Inbox'] = "Doruèené";
$lang['Outbox'] = "Nedoruèené";
$lang['Savebox'] = "Uložené";
$lang['Sentbox'] = "Odeslané";
$lang['Flag'] = "Pøíznak";
$lang['Subject'] = "Pøedmìt";
$lang['From'] = "Od";
$lang['To'] = "Komu";
$lang['Date'] = "Datum";
$lang['Mark'] = "Oznaèit";
$lang['Sent'] = "Zasláno";
$lang['Saved'] = "Uloženo";
$lang['Delete_marked'] = "Odstranit oznaèené";
$lang['Delete_all'] = "Odstranit vše";
$lang['Save_marked'] = "Uložit oznaèené";
$lang['Save_message'] = "Uložit zprávu";
$lang['Delete_message'] = "Odstranit zprávu";

$lang['Display_messages'] = "Zobrazit zprávy za pøedchozí"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "Všechny zprávy";

$lang['No_messages_folder'] = "Nemáte žádné zprávy v této složce";

$lang['PM_disabled'] = "Soukromé zprávy zde byly vypnuty";
$lang['Cannot_send_privmsg'] = "Promiòte, ale administrátor vám neumožnil zasílání soukromých zpráv";
$lang['No_to_user'] = "Musíte zadat uživatelské jméno aby jste mohl(a) odeslat tuto zprávu";
$lang['No_such_user'] = "Tento uživatel není registrován";

$lang['Disable_HTML_pm'] = "Zakázat HTML v této zprávì";
$lang['Disable_BBCode_pm'] = "Zakázat Znaèky v této zprávì";
$lang['Disable_Smilies_pm'] = "Zakázat smajlíky (emotikony) v této zprávì";

$lang['Message_sent'] = "Vaše zpráva byla odeslána";

$lang['Click_return_inbox'] = "Kliknìte %szde%s pro návrat do doruèených";
$lang['Click_return_index'] = "Kliknìte %szde%s pro návrat na obsah";

$lang['Send_a_new_message'] = "Odeslat novou soukromou zprávu";
$lang['Send_a_reply'] = "Odpovìdìt na soukromou zprávu";
$lang['Edit_message'] = "Upravit soukromou zprávu";

$lang['Notification_subject'] = "Pøišla nová soukromá zpráva";

$lang['Find_username'] = "Hledat uživatele";
$lang['Find'] = "Hledat";
$lang['No_match'] = "Žádný výsledek";

$lang['No_post_id'] = "Nebylo zvoleno ID zprávy";
$lang['No_such_folder'] = "Požadovaná složka neexistuje";
$lang['No_folder'] = "Nebyla zvolena složka";

$lang['Mark_all'] = "Oznaèit vše";
$lang['Unmark_all'] = "Odznaèit vše";

$lang['Confirm_delete_pm'] = "Opravdu chcete odstranit tuto zpráva ?";
$lang['Confirm_delete_pms'] = "Opravdu chcete odstranit tyto zprávy ?";

$lang['Inbox_size'] = "Schránka je zaplnìna z %d%%"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Schránka je zaplnìna z %d%%";
$lang['Savebox_size'] = "Schránka je zaplnìna z %d%%";

$lang['Click_view_privmsg'] = "Kliknìte %szde%s pro zobrazení obsahu pøíchozích zpráv";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Informace o uživateli: %s"; // %s is username
$lang['About_user'] = "Vše o uživateli %s"; // %s is username

$lang['Preferences'] = "Možnosti";
$lang['Items_required'] = "Pole oznaèená \"*\" jsou povinná a musí být vyplnìna";
$lang['Registration_info'] = "Registraèní údaje";
$lang['Profile_info'] = "Osobní údaje";
$lang['Profile_info_warn'] = "Tyto informace budou veøejnì zobrazitelné";
$lang['Avatar_panel'] = "Obrázky postavièek";
$lang['Avatar_gallery'] = "Galerie postavièek";

$lang['Website'] = "WWW";
$lang['Location'] = "Bydlištì";
$lang['Contact'] = "Kontakt";
$lang['Email_address'] = "E-mailová adresa";
$lang['Email'] = "E-mail";
$lang['Send_private_message'] = "Odeslat soukromou zprávu";
$lang['Hidden_email'] = "[ skrytý ]";
$lang['Search_user_posts'] = "Hledat pøíspìvky tohoto uživatele";
$lang['Interests'] = "Zájmy";
$lang['Occupation'] = "Povolání";
$lang['Poster_rank'] = "Odesilatelovo hodnocení";

$lang['Total_posts'] = "Pøíspìvkù";
$lang['User_post_pct_stats'] = "%.2f%% ze všech pøíspìvkù"; // 1.25% of total
$lang['User_post_day_stats'] = "%.2f pøíspìvkù za den"; // 1.5 posts per day
$lang['Search_user_posts'] = "Hledat všechny pøíspìvky od uživatele %s"; // Find all posts by username

$lang['No_user_id_specified'] = "Promiòte, ale tento uživatel neexistuje";
$lang['Wrong_Profile'] = "Nemùžete modifikovat toto nastavení, jelikož nejste jeho vlastníkem";

$lang['Only_one_avatar'] = "Mùže být zvolen pouze jeden obrázek postavièky";
$lang['File_no_data'] = "Soubor na zadané URL adrese neobsahuje žádná data";
$lang['No_connection_URL'] = "Nelze navázat spojení se zadanou URL adresou";
$lang['Incomplete_URL'] = "Vámi zadaná URL adresa není kompletní";
$lang['Wrong_remote_avatar_format'] = "URL adresa vzdáleného obrázku postavièky není dostupná";
$lang['No_send_account_inactive'] = "Promiòte, ale vaše heslo nemùže být nalezeno, protože je váš úèet momentálnì aktivní. Pro více informací kontaktujte administrátora tohoto fóra";

$lang['Always_smile'] = "Vždy povolit smajlíky";
$lang['Always_html'] = "Vždy povolit HTML";
$lang['Always_bbcode'] = "Vždy povolit znaèky";
$lang['Always_add_sig'] = "Vždy pøipojit mùj podpis";
$lang['Always_notify'] = "Vždy mnì upozornit na odpovìdi";
$lang['Always_notify_explain'] = "Pošle e-mail když nìkdo odpoví na vámi poslané téma. Toto mùže být zmìnìno kdykoli pøed odesláním";

$lang['Board_style'] = "Vzhled fóra";
$lang['Board_lang'] = "Jazyk fóra";
$lang['No_themes'] = "Vzhled není v databázi";
$lang['Timezone'] = "Èasové pásmo";
$lang['Date_format'] = "Formát datumu a èasu";
$lang['Date_format_explain'] = "Použitá syntaxe je shodná s PHP funkcí <a href=\"http://www.php.net/date\" target=\"_other\">date()</a>";
$lang['Signature'] = "Podpis";
$lang['Signature_explain'] = "Text, který mùže být pøidáván do vašich pøíspìvkù<br>Maximálnì %d znakù";
$lang['Public_view_email'] = "Vždy zobrazovat mou e-mailovou adresu";

$lang['Current_password'] = "Aktuální Heslo";
$lang['New_password'] = "Nové heslo";
$lang['Confirm_password'] = "Potvrzení hesla";
$lang['Confirm_password_explain'] = "Pokud chcete zmìnit heslo nebo upravit e-mailovou adresu musíte zadat vaše aktuální heslo";
$lang['password_if_changed'] = "Vyplòte pokud chcete zmìnit aktuální heslo";
$lang['password_confirm_if_changed'] = "Vyplòte pro potvrzení, pokud chcete zmìnit vaše aktuální heslo";

$lang['Avatar'] = "Obrázek postavièky";
$lang['Avatar_explain'] = "Zobrazit malý obrázek postavièky pod podrobnostmi v pøíspìvcích. Pouze jeden obrázek postavièky bude zobrazen, jeho šíøka by nemìla být vìtší než %d bodù a výška %d bodù a velikost souboru by nemìla pøesahovat %dkB.";
$lang['Upload_Avatar_file'] = "Pøihraj obrázek postavièky ze svého poèítaèe";
$lang['Upload_Avatar_URL'] = "Pøihrát obrázek postavièky z URL";
$lang['Upload_Avatar_URL_explain'] = "Zadejte URL umístìní obrázku postavièky, pro zkopírování na tento server.";
$lang['Pick_local_Avatar'] = "Zvolte obrázek postavièky z galerie";
$lang['Link_remote_Avatar'] = "Odkaz na vzdálený obrázek postavièky";
$lang['Link_remote_Avatar_explain'] = "Zadejte URL umístìní obrázku postavièky, na který chcete odkázat.";
$lang['Avatar_URL'] = "URL adresa obrázku s postavièkou";
$lang['Select_from_gallery'] = "Zvolte obrázek postavièky z galerie";
$lang['View_avatar_gallery'] = "Zobrazit galerii postavièek";

$lang['Select_avatar'] = "Zvolte obrázek postavièky";
$lang['Return_profile'] = "Návrat do nastavení";
$lang['Select_category'] = "Volba kategorie";

$lang['Delete_Image'] = "Odstranit obrázek";
$lang['Current_Image'] = "Aktuální obrázek";

$lang['Notify_on_privmsg'] = "Upozornit na pøíchod nové soukromé zprávy";
$lang['Popup_on_privmsg'] = "Otevøít nové okno pøi pøíchodu nové soukromé zprávy";
$lang['Popup_on_privmsg_explain'] = "Nìkteré šablony mohou otevøít nové okno, aby vás informovaly o novì pøíchozí soukromé zprávì";
$lang['Hide_user'] = "Skrýt vaší pøítomnost ve fóru";

$lang['Profile_updated'] = "Váše nastavení bylo aktualizováno";
$lang['Profile_updated_inactive'] = "Vaše nastavení bylo aktualizováno, ale jelikož jste zmìnil(a) dùležité informace je nyní váš úèet neaktivní. Zkontrolujte váš e-mail pro informace jak jej znovu aktivovat, nebo pokud je nutná administrátorská aktivace poèkejte až administrátor váš úèet znovu aktivuje";

$lang['Password_mismatch'] = "Zadaná hesla se neshodují";
$lang['Current_password_mismatch'] = "Vámi zadané aktuální heslo není správné";
$lang['Password_long'] = "Vaše heslo nesmí pøesahovat 32 znakù";
$lang['Username_taken'] = "Promiòte, ale tento uživatel je již registrován";
$lang['Username_invalid'] = "Promiòte, ale toto uživatelské jméno obsahuje nepovolené znaky \"";
$lang['Username_disallowed'] = "Promiòte, ale toto uživatelské jméno je zakázáno";
$lang['Email_taken'] = "Promiòte, ale tuto e-mailovou adresu má již registrována nìkterý uživatel";
$lang['Email_banned'] = "Promiòte, ale tato e-mailová adresa byla zakázána";
$lang['Email_invalid'] = "Promiòte, tato e-mailová adresa není platná";
$lang['Signature_too_long'] = "Váš podpis je pøíliš dlouhý";
$lang['Fields_empty'] = "Musíte zadat požadované údaje";
$lang['Avatar_filetype'] = "Obrázek postavièky musí být typu .jpg, .gif nebo .png";
$lang['Avatar_filesize'] = "Soubor obrázku postavièky musí být menší než %d kB"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "Obrázek postavièky musí mít šíøku maximálnì %d bodù a výšku %d bodù";

$lang['Welcome_subject'] = "Vítejte na %s fóru"; // Welcome to my.com forums
$lang['New_account_subject'] = "Nový uživatelský úèet";
$lang['Account_activated_subject'] = "Úèet aktivován";

$lang['Account_added'] = "Dìkujeme za registraci, váš úèet byl vytvoøen. Nyní se mùžete pøihlásit pod svým jménem a heslem";
$lang['Account_inactive'] = "Váš uživatelský úèet byl vytvoøen. Ovšem toto fórum vyžaduje aktivaci úètu. Aktivaèní klíèem, vám byl zaslán na vámi poskytnutou e-mailovou adresu. Bližší informace obdržíte na vaší e-mailovou adresu";
$lang['Account_inactive_admin'] = "Váš uživatelský úèet byl vytvoøen. Ovšem toto fórum vyžaduje aktivaci úètu administrátorem. Po aktivaci administrátorem, budete vyrozumìn(a) na vaší e-mailovou adresu";
$lang['Account_active'] = "Váš úèet byl aktivován. Dìkujeme za registraci";
$lang['Account_active_admin'] = "Úèet byl aktivován";
$lang['Reactivate'] = "Pøeaktivujte svùj úèet!";
$lang['Already_activated'] = 'Váš úèet jste již aktivoval';
$lang['COPPA'] = "Váš úèet byl vytvoøen ale nemusí být ještì akceptován. Pro potvrzení si pøeètìte bližší informace v zaslaném e-mailu";

$lang['Registration'] = "Registraèní podmínky";
$lang['Reg_agreement'] = "Aèkoliv se administrátoøi a moderátoøi tohoto fóra pokusí odstranit nebo upravit jakýkoliv všeobecnì nežádoucí materiál tak rychle jak jen to je možné, je nemožné prohlédnou každý pøíspìvek. Proto vemte na vìdomí, že všechny pøíspìvky do tohoto fóra vyjadøují pohledy a názory autora pøíspìvku a ne administrátorù, moderátorù a webmastera (mimo pøíspìvky od tìchto lidí) a proto za nì nemohou být zodpovìdní.<br><br>Souhlasíte s tím, že nebudete posílat žádné hanlivé, neslušné, vulgární, nenávistné, zastrašující, sexuálnì orientované nebo jiné materiály, které mohou porušovat zákony. Posílání takových materiálù vám mùže pøivodit okamžité a permanentní vyhoštìní z fóra (a váš ISP bude o vaší èinnosti informován). IP adresa všech pøíspìvkù je zaznamenávána pro pøípad potøeby vynucení tìchto podmínek. Souhlasíte, že webmaster, administrátor a moderátoøi tohoto fóra mají právo odstranit, upravit, pøesunout nebo ukonèit jakékoliv téma kdykoliv zjistí že odporuje tìmto podmínkám. Jako uživatel souhlasíte, že jakékoliv informace které vložíte budou uloženy v databázi. Dokud nebudou tyto informace prozrazeny tøetí stranì bez vašeho svolení nemohou být webmaster, administrátor a moderátoøi èinìni zodpovìdnými za jakékoliv hackerské pokusy které mohou vést k tomu, že data budou kompromitována.<br><br>Systém tohoto fóra používá cookies k ukládání informací na vašem poèítaèi. Tato cookies neobsahují žádné informace, které jste vložil, slouží jen ke zvýšení vašeho pohodlí pøi prohlížení. E-mailová adresa je používána jen pro potvrzení vašich registraèních detailù a hesla (a pro posláni nového hesla, pokud jste zapomìl aktuální).<br><br>Kliknutím na Registraci níže souhlasíte být vázaný tìmito podmínkami.";

$lang['Agree_under_13'] = "Souhlasím s tìmito podmínkami a je mi <b>ménì</b> než 13 let";
$lang['Agree_over_13'] = "Souhlasím s tìmito podmínkami a je mi <b>více</b> než 13 let";
$lang['Agree_not'] = "Nesouhlasím s tìmito podmínkami";

$lang['Wrong_activation'] = "Vámi poskytnutý aktivaèní klíè neodpovídá se neshoduje s žádným z databáze";
$lang['Send_password'] = "Zašlete mi nové heslo";
$lang['Password_updated'] = "Nové heslo bylo vytvoøeno, oèekávejte e-mail s informacemi jak jej aktivovat";
$lang['No_email_match'] = "E-mailová adresa nesouhlasí s adresou pøiøazenou k vašemu uživatelskému jménu";
$lang['New_password_activation'] = "Aktivace nového hesla";
$lang['Password_activated'] = "Váš úèet byl reaktivován. Pro pøihlášení použijte heslo, která vám bylo zasláno e-mailem";

$lang['Send_email_msg'] = "Odeslat e-mailovou zprávu";
$lang['No_user_specified'] = "Nebyl zvolen žádný uživatel";
$lang['User_prevent_email'] = "Tento uživatel si nepøeje pøijímat odpovìdi e-mailem. Zkuste mu zaslat soukromou zprávu";
$lang['User_not_exist'] = "Tento uživatel neexistuje";
$lang['CC_email'] = "Odeslat kopii tohoto e-mailu sobì";
$lang['Email_message_desc'] = "Tato zpráva bude zaslána jako prostý text, nebude obsahovat žádné HTML ani znaèky. Adresa pro odpovìï na tuto zprávu bude nastavena na vaši e-mailovou adresu.";
$lang['Flood_email_limit'] = "Nemùžete nyní odeslat další e-mail, zkuste opakovat pozdìji";
$lang['Recipient'] = "Pøíjemce";
$lang['Email_sent'] = "E-mail byl odeslán";
$lang['Send_email'] = "Odeslat e-mail";
$lang['Empty_subject_email'] = "Musíte zadat pøedmìt e-mailu";
$lang['Empty_message_email'] = "Musíte zadat text zprávy";


//
// Memberslist
//
$lang['Select_sort_method'] = "Setøídit dle";
$lang['Sort'] = "Setøídit";
$lang['Sort_Top_Ten'] = "Nejèastìjší pøispìvatelé";
$lang['Sort_Joined'] = "Data registrace";
$lang['Sort_Username'] = "Jména uživatele";
$lang['Sort_Location'] = "Bydlištì";
$lang['Sort_Posts'] = "Poètu pøíspìvkù";
$lang['Sort_Email'] = "E-mailu";
$lang['Sort_Website'] = "WWW stránky";
$lang['Sort_Ascending'] = "Vzestupnì";
$lang['Sort_Descending'] = "Sestupnì";
$lang['Order'] = "Dle poøadí";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Skupina - Ovládací panel";
$lang['Group_member_details'] = "Detaily èlenství ve skupinì";
$lang['Group_member_join'] = "Vstoupit do skupiny";

$lang['Group_Information'] = "Informace o skupinì";
$lang['Group_name'] = "Jméno skupiny";
$lang['Group_description'] = "Popis skupiny";
$lang['Group_membership'] = "Vaše èlenství";
$lang['Group_Members'] = "Èlenové Skupiny";
$lang['Group_Moderator'] = "Moderátor skupiny";
$lang['Pending_members'] = "Èekající èlenové";

$lang['Group_type'] = "Typ skupiny";
$lang['Group_open'] = "Otevøená skupina";
$lang['Group_closed'] = "Uzavøená skupina";
$lang['Group_hidden'] = "Skrytá skupina";

$lang['Current_memberships'] = "Aktuální èlenství";
$lang['Non_member_groups'] = "Skupiny bez èlenù";
$lang['Memberships_pending'] = "Èekací èlenství";

$lang['No_groups_exist'] = "Neexistuje žádná skupina";
$lang['Group_not_exist'] = "Tato skupina neexistuje";

$lang['Join_group'] = "Pøihlásit se do skupiny";
$lang['No_group_members'] = "Tato skupina nemá èleny";
$lang['Group_hidden_members'] = "Tato skupina je skrytá, nemùžete vidìt seznam jejích èlenù";
$lang['No_pending_group_members'] = "Tato skupina nemá èekající èleny";
$lang["Group_joined"] = "Úspìšnì jste vstoupil do této skupiny<br>Budete informován až bude váš vstup moderátorem této skupiny odsouhlasen";
$lang['Group_request'] = "Vaše žádost o vstup do skupiny byla odeslána";
$lang['Group_approved'] = "Vaše žádost byla odsouhlasena";
$lang['Group_added'] = "Byl jste pøijat do této skupiny";
$lang['Already_member_group'] = "Již jste èlenem této skupiny";
$lang['User_is_member_group'] = "Uživatel již je èlenem této skupiny";
$lang['Group_type_updated'] = "Typ skupiny byl úspìšnì aktualizován";

$lang['Could_not_add_user'] = "Zvolený uživatel neexistuje";
$lang['Could_not_anon_user'] = "Anonymní uživatel nemùže být èlenem skupiny";

$lang['Confirm_unsub'] = "Opravdu chcete ukonèit èlenství v této skupinì ?";
$lang['Confirm_unsub_pending'] = "Vaše èlenství v této skupinì zatím nebylo odsouhlaseno, opravdu je chcete ukonèit ?";

$lang['Unsub_success'] = "Pøestal jste být èlenem této skupiny";

$lang['Approve_selected'] = "Akceptovat zvolené";
$lang['Deny_selected'] = "Zamítnout zvolené";
$lang['Not_logged_in'] = " Pro vstup do skupiny musíte být pøihlášen.";
$lang['Remove_selected'] = "Odstranit zvolené";
$lang['Add_member'] = "Pøidat èlena";
$lang['Not_group_moderator'] = "Nejste moderátorem této skupiny, proto nemùžete provést tuto akci";

$lang['Login_to_join'] = "Pøihlásit pro vstup do skupiny nebo úpravy èlenství";
$lang['This_open_group'] = "Toto je otevøená skupina, kliknìte pro požádání o èlenství";
$lang['This_closed_group'] = "Toto je uzavøená skupina, žádní další uživatelé nejsou pøíjímáni";
$lang['This_hidden_group'] = "Toto je skrytá skupina, automatické pøidávání uživatelù není dovoleno";
$lang['Member_this_group'] = "Jste èlenem této skupiny";
$lang['Pending_this_group'] = "Vaše èlenství v této skupinì èeká na odsouhlasení";
$lang['Are_group_moderator'] = "Jste moderátorem skupiny";
$lang['None'] = "nikdo není pøítomen";

$lang['Subscribe'] = "Požádat o èlenství";
$lang['Unsubscribe'] = "Ukonèit èlenství";
$lang['View_Information'] = "Zobrazit informace";


//
// Search
//
$lang['Search_query'] = "Hledaný øetìzec";
$lang['Search_options'] = "Možnosti hledání";

$lang['Search_keywords'] = "Klíèová slova";
$lang['Search_keywords_explain'] = "Mùžete použít <u>AND</u> pro slova, která musí být ve výsledcích, <u>OR</u> pro taková, která tam mohou náležet a <u>NOT</u> pro taková, která by ve výsledcích nemìla být. Znak \"*\" nahradí èást øetìzce pøi vyhledávání";
$lang['Search_author'] = "Autora";
$lang['Search_author_explain'] = "Znak \"*\" nahradí èást øetìzce pøi vyhledávání";

$lang['Search_for_any'] = "Hledej kterékoliv slovo nebo výraz jak je zadaný";
$lang['Search_for_all'] = "Hledej všechna slova";
$lang['Search_title_msg'] = "Hledej název tématu a text zprávy";
$lang['Search_msg_only'] = "Hledat jen text zprávy";

$lang['Return_first'] = "Zobraz prvních"; // followed by xxx characters in a select box
$lang['characters_posts'] = "znakù ze pøíspìvku";

$lang['Search_previous'] = "Prohledej pøedchozí"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "Setøídit dle";
$lang['Sort_Time'] = "Èas odeslání";
$lang['Sort_Post_Subject'] = "Pøedmìtu";
$lang['Sort_Topic_Title'] = "Hlavièky tématu";
$lang['Sort_Author'] = "Autora";
$lang['Sort_Forum'] = "Fóra";

$lang['Display_results'] = "Zobrazit výsledek jako";
$lang['All_available'] = "Všechny dostupné";
$lang['No_searchable_forums'] = "Pokud nechcete povolit vyhledávání v libovolných fórech tohoto serveru";

$lang['No_search_match'] = "Žádné téma nebo pøíspìvek nebyl nalezen dle zvolených kritérií";
$lang['Found_search_match'] = "Byl nalezen %d výsledek hledání"; // eg. Search found 1 match
$lang['Found_search_matches'] = "bylo nalezeno %d výsledkù hledání"; // eg. Search found 24 matches

$lang['Close_window'] = "Zavøít okno";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "Promiòte, ale jen %s mohou posílat oznámení do tohoto fóra";
$lang['Sorry_auth_sticky'] = "Promiòte, ale jen %s mohou posílat dùležité zprávy do tohoto fóra";
$lang['Sorry_auth_read'] = "Promiòte, ale jen %s mohou èíst témata v tomto fóru";
$lang['Sorry_auth_post'] = "Promiòte, ale jen %s mohou posílat témata do tohoto fóra";
$lang['Sorry_auth_reply'] = "Promiòte, ale jen %s mohou odpovídat na pøíspìvky v tomto fóru";
$lang['Sorry_auth_edit'] = "Promiòte, ale jen %s mohou upravovat pøíspìvky v tomto fóru";
$lang['Sorry_auth_delete'] = "Promiòte, ale jen %s mohou mazat pøíspìvky v tomto fóru";
$lang['Sorry_auth_vote'] = "Promiòte, ale jen %s mohou hlasovat v hlasování tohoto fóra";

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>Anonymní uživatelé</b>";
$lang['Auth_Registered_Users'] = "<b>Registrovaní uživatelé</b>";
$lang['Auth_Users_granted_access'] = "<b>uživatelé se zvláštním oprávnìním</b>";
$lang['Auth_Moderators'] = "<b>Moderátoøi</b>";
$lang['Auth_Administrators'] = "<b>Administrátoøi</b>";

$lang['Not_Moderator'] = "Nejste moderátorem tohoto fóra";
$lang['Not_Authorised'] = "Neautorizovaný";

$lang['You_been_banned'] = "Byl jste vykázán z tohoto fóra<br>Prosím kontaktujte webmastera nebo administrátora tohoto fóra pro získání bližších informací";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "Je zde 0 registrovaných uživatelù a "; // There ae 5 Registered and
$lang['Reg_users_online'] = "Je zde %d registrovaných uživatelù a "; // There ae 5 Registered and
$lang['Reg_user_online'] = "Je zde %d registrovaný uživatel a "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "0 skrytých uživatelù"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d skrytých uživatelù"; // 6 Hidden users online
$lang['Hidden_user_online'] = "%d skrytý uživatel"; // 6 Hidden users online
$lang['Guest_users_online'] = "Je zde %d anonymních uživatelù"; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = "Je zde 0 anonymních uživatelù"; // There are 10 Guest users online
$lang['Guest_user_online'] = "Je zde %d anonymní uživatel"; // There is 1 Guest user online
$lang['No_users_browsing'] = "Momentálnì zde nejsou žádní uživatelé";

$lang['Online_explain'] = "Tato data jsou založena na aktivitì uživatelù bìhem posledních 5 minut";

$lang['Forum_Location'] = "Nachází se";
$lang['Last_updated'] = "Poslední aktualizace";

$lang['Forum_index'] = "Obsah fóra";
$lang['Logging_on'] = "Pøihlašuje se";
$lang['Posting_message'] = "Odesílá zprávu";
$lang['Searching_forums'] = "Prohledává fóra";
$lang['Viewing_profile'] = "Prohlíží nastavení";
$lang['Viewing_online'] = "Prohlíží seznam pøítomných uživatelù";
$lang['Viewing_member_list'] = "Prohlíží seznam uživatelù";
$lang['Viewing_priv_msgs'] = "Prohlíží soukromé zprávy";
$lang['Viewing_FAQ'] = "prohlíží FAQ";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Moderátor - Ovládací panel";
$lang['Mod_CP_explain'] = "Pomocí následujícího formuláøe mùžete provádìt hromadné zásahy do tohoto fóra. Mùžete zamykat, odemykat, pøesouvat a mazat jakýkoliv poèet témat";

$lang['Select'] = "Zvolit";
$lang['Delete'] = "Odstranit";
$lang['Move'] = "Pøesunout";
$lang['Lock'] = "Zamknout";
$lang['Unlock'] = "Odemknout";

$lang['Topics_Removed'] = "Zvolená témata byla úspìšnì odstranìna z databáze.";
$lang['Topics_Locked'] = "Zvolená témata byla uzamknuta";
$lang['Topics_Moved'] = "Zvolená témata byla pøesunuta";
$lang['Topics_Unlocked'] = "Zvolená témata byla odemknuta";
$lang['No_Topics_Moved'] = "Žádná témata nebyla pøesunuta";

$lang['Confirm_delete_topic'] = "Opravdu chcete odstranit zvolená témata ?";
$lang['Confirm_lock_topic'] = "Opravdu chcete zamknout zvolená témata ?";
$lang['Confirm_unlock_topic'] = "Opravdu chcete odemknout zvolená témata ?";
$lang['Confirm_move_topic'] = "Opravdu chcete pøesunout zvolená témata ?";

$lang['Move_to_forum'] = "Pøesunout do fóra";
$lang['Leave_shadow_topic'] = "Ponechat stínové téma ve starém fóru.";

$lang['Split_Topic'] = "Rozdìlení tématu - Ovládací panel";
$lang['Split_Topic_explain'] = "Pomocí následujícího formuláøe mùžete téma rozdìlit na dvì, buï vybráním pøíspìvkù jednotlivì, nebo rozdìlením od vybraného pøíspìvku";
$lang['Split_title'] = "Titulek nového tématu";
$lang['Split_forum'] = "Forum pro nové téma";
$lang['Split_posts'] = "Rozdìlit vybrané pøíspìvky";
$lang['Split_after'] = "Rozdìlit od vybraného pøíspìvku";
$lang['Topic_split'] = "Vybrané téma bylo úspìšnì rozdìleno";

$lang['Too_many_error'] = "Vybal(a) jste pøíliš mnoho pøíspìvkù. Mùžete vybrat pouze jeden pøíspìvek, od kterého chcete téma rozdìlit!";

$lang['None_selected'] = "Nebylo vybrání žádné téma pro vykonání této operace. Proveïte návrat zpìt a zvolte alespoò jedno téma";
$lang['New_forum'] = "Nové fórum";

$lang['This_posts_IP'] = "IP adresa pøíspìvku";
$lang['Other_IP_this_user'] = "Další IP adresy ze kterých uživatel odesílal pøíspìvky";
$lang['Users_this_IP'] = "Uživatelé zasílající pøíspìvek z této IP adresy";
$lang['IP_info'] = "Informace o IP adresáøe";
$lang['Lookup_IP'] = "Hledat IP adresu";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "Èasy uvádìny v %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 hodin";
$lang['-11'] = "GMT - 11 hodin";
$lang['-10'] = "GMT - 10 hodin";
$lang['-9'] = "GMT - 9 hodin";
$lang['-8'] = "GMT - 8 hodin";
$lang['-7'] = "GMT - 7 hodin";
$lang['-6'] = "GMT - 6 hodin";
$lang['-5'] = "GMT - 5 hodin";
$lang['-4'] = "GMT - 4 hodiny";
$lang['-3.5'] = "GMT - 3.5 hodiny";
$lang['-3'] = "GMT - 3 hodiny";
$lang['-2'] = "GMT - 2 hodiny";
$lang['-1'] = "GMT - 1 hodina";
$lang['0'] = "GMT";
$lang['1'] = "GMT + 1 hodina";
$lang['2'] = "GMT + 2 hodiny";
$lang['3'] = "GMT + 3 hodiny";
$lang['3.5'] = "GMT + 3.5 hodiny";
$lang['4'] = "GMT + 4 hodiny";
$lang['4.5'] = "GMT + 4.5 hodiny";
$lang['5'] = "GMT + 5 hodin";
$lang['5.5'] = "GMT + 5.5 hodiny";
$lang['6'] = "GMT + 6 hodin";
$lang['6.5'] = "GMT + 6.5 hodiny";
$lang['7'] = "GMT + 7 hodin";
$lang['8'] = "GMT + 8 hodin";
$lang['9'] = "GMT + 9 hodin";
$lang['9.5'] = "GMT + 9.5 hodin";
$lang['10'] = "GMT + 10 hodin";
$lang['11'] = "GMT + 11 hodin";
$lang['12'] = "GMT + 12 hodin";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT -12:00 hodin) Eniwetok, Kwajalein";
$lang['tz']['-11'] = "(GMT -11:00 hodin) Midway Island, Samoa";
$lang['tz']['-10'] = "(GMT -10:00 hodin) Hawaii";
$lang['tz']['-9'] = "(GMT -9:00 hodin) Alaska";
$lang['tz']['-8'] = "(GMT -8:00 hodin) Pacific Time (US &amp; Canada), Tijuana";
$lang['tz']['-7'] = "(GMT -7:00 hodin) Mountain Time (US &amp; Canada), Arizona";
$lang['tz']['-6'] = "(GMT -6:00 hodin) Central Time (US &amp; Canada), Mexico City";
$lang['tz']['-5'] = "(GMT -5:00 hodin) Eastern Time (US &amp; Canada), Bogota, Lima, Quito";
$lang['tz']['-4'] = "(GMT -4:00 hodiny) Atlantic Time (Canada), Caracas, La Paz";
$lang['tz']['-3.5'] = "(GMT -3.5 hodiny) Newfoundland";
$lang['tz']['-3'] = "(GMT -3:00 hodiny) Brassila, Buenos Aires, Georgetown, Falkland Is";
$lang['tz']['-2'] = "(GMT -2:00 hodiny) Mid-Atlantic, Ascension Is., St. Helena";
$lang['tz']['-1'] = "(GMT -1:00 hodina) Azores, Cape Verde Islands";
$lang['tz']['0'] = "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia";
$lang['tz']['1'] = "(GMT +1:00 hodina) Prague, Amsterdam, Berlin, Brussels, Madrid, Paris";
$lang['tz']['2'] = "(GMT +2:00 hodiny) Cairo, Helsinki, Kaliningrad, South Africa";
$lang['tz']['3'] = "(GMT +3:00 hodiny) Baghdad, Riyadh, Moscow, Nairobi";
$lang['tz']['3.5'] = "(GMT +3.5 hodiny) Tehran";
$lang['tz']['4'] = "(GMT +4:00 hodiny) Abu Dhabi, Baku, Muscat, Tbilisi";
$lang['tz']['4.5'] = "(GMT +4.5 hodiny) Kabul";
$lang['tz']['5'] = "(GMT +5:00 hodin) Ekaterinburg, Islamabad, Karachi, Tashkent";
$lang['tz']['5.5'] = "(GMT +5.5 hodiny) Bombay, Calcutta, Madras, New Delhi";
$lang['tz']['6'] = "(GMT +6:00 hodin) Almaty, Colombo, Dhaka, Novosibirsk";
$lang['tz']['6.5'] = "(GMT +6.5 hodiny) Rangoon";
$lang['tz']['7'] = "(GMT +7:00 hodin) Bangkok, Hanoi, Jakarta";
$lang['tz']['8'] = "(GMT +8:00 hodin) Beijing, Hong Kong, Perth, Singapore, Taipei";
$lang['tz']['9'] = "(GMT +9:00 hodin) Osaka, Sapporo, Seoul, Tokyo, Yakutsk";
$lang['tz']['9.5'] = "(GMT +9.5 hodiny) Adelaide, Darwin";
$lang['tz']['10'] = "(GMT +10:00 hodin) Canberra, Guam, Melbourne, Sydney, Vladivostok";
$lang['tz']['11'] = "(GMT +11:00 hodin) Magadan, New Caledonia, Solomon Islands";
$lang['tz']['12'] = "(GMT +12:00 hodin) Auckland, Wellington, Fiji, Marshall Island";

$lang['datetime']['Sunday'] = "nedìle";
$lang['datetime']['Monday'] = "pondìlí";
$lang['datetime']['Tuesday'] = "úterý";
$lang['datetime']['Wednesday'] = "støeda";
$lang['datetime']['Thursday'] = "ètvrtek";
$lang['datetime']['Friday'] = "pátek";
$lang['datetime']['Saturday'] = "sobota";
$lang['datetime']['Sun'] = "ne";
$lang['datetime']['Mon'] = "po";
$lang['datetime']['Tue'] = "út";
$lang['datetime']['Wed'] = "st";
$lang['datetime']['Thu'] = "èt";
$lang['datetime']['Fri'] = "pá";
$lang['datetime']['Sat'] = "so";
$lang['datetime']['January'] = "leden";
$lang['datetime']['February'] = "únor";
$lang['datetime']['March'] = "bøezen";
$lang['datetime']['April'] = "duben";
$lang['datetime']['May'] = "kvìten";
$lang['datetime']['June'] = "èerven";
$lang['datetime']['July'] = "èervenec";
$lang['datetime']['August'] = "srpen";
$lang['datetime']['September'] = "záøí";
$lang['datetime']['October'] = "øíjen";
$lang['datetime']['November'] = "listopad";
$lang['datetime']['December'] = "prosinec";
$lang['datetime']['Jan'] = "leden";
$lang['datetime']['Feb'] = "únor";
$lang['datetime']['Mar'] = "bøezen";
$lang['datetime']['Apr'] = "duben";
$lang['datetime']['May'] = "kvìten";
$lang['datetime']['Jun'] = "èerven";
$lang['datetime']['Jul'] = "èervenec";
$lang['datetime']['Aug'] = "srpen";
$lang['datetime']['Sep'] = "záøí";
$lang['datetime']['Oct'] = "øíjen";
$lang['datetime']['Nov'] = "listopad";
$lang['datetime']['Dec'] = "prosinec";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "Informace";
$lang['Critical_Information'] = "Kritická informace";

$lang['General_Error'] = "Všeobecná chyba";
$lang['Critical_Error'] = "kritická chyba";
$lang['An_error_occured'] = "Objevila se chyba";
$lang['A_critical_error'] = "Objevila se kritická chyba";


?>