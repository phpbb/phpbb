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

//setlocale(LC_ALL, "en");
$lang['ENCODING'] = "iso-8859-1";
$lang['DIRECTION'] = "LTR";
$lang['LEFT'] = "LEFT";
$lang['RIGHT'] = "RIGHT";
$lang['DATE_FORMAT'] = "d M Y"; // This should be changed to the default date format for your language, php date() format

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Forum";
$lang['Category'] = "Kategori";
$lang['Topic'] = "Tema";
$lang['Topics'] = "Temat";
$lang['Replies'] = "Përgjigjet";
$lang['Views'] = "Shikime";
$lang['Post'] = "Mesazh";
$lang['Posts'] = "Mesazhe";
$lang['Posted'] = "Postuar";
$lang['Username'] = "Identifikim";
$lang['Password'] = "Fjalëkalim";
$lang['Email'] = "Email";
$lang['Poster'] = "Shkruesi";
$lang['Author'] = "Autori";
$lang['Time'] = "Ora";
$lang['Hours'] = "Orë";
$lang['Message'] = "Mesazh";

$lang['1_Day'] = "1 Ditë";
$lang['7_Days'] = "7 Ditë";
$lang['2_Weeks'] = "2 Javë";
$lang['1_Month'] = "1 Muaj";
$lang['3_Months'] = "3 Muaj";
$lang['6_Months'] = "6 Muaj";
$lang['1_Year'] = "1 Vit";

$lang['Go'] = "Shko";
$lang['Jump_to'] = "Kërce tek";
$lang['Submit'] = "Paraqit";
$lang['Reset'] = "Nga e para";
$lang['Cancel'] = "Anullo";
$lang['Preview'] = "Preview";
$lang['Confirm'] = "Konfirmo";
$lang['Spellcheck'] = "Spellcheck";
$lang['Yes'] = "Po";
$lang['No'] = "Jo";
$lang['Enabled'] = "Aktivizuar";
$lang['Disabled'] = "C'aktivizuar";
$lang['Error'] = "Problem";

$lang['Next'] = "Tjetri";
$lang['Previous'] = "I mëparshëm";
$lang['Goto_page'] = "Shko tek faqja";
$lang['Joined'] = "Anëtarësuar";
$lang['IP_Address'] = "Adresa IP";

$lang['Select_forum'] = "Zgjidh një forum";
$lang['View_latest_post'] = "Shiko mesazhin e fundit";
$lang['View_newest_post'] = "Shiko mesazhin më të ri";
$lang['Page_of'] = "Faqja <b>%d</b> e <b>%d</b>"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "Nr. i ICQ";
$lang['AIM'] = "Adresa e AIM";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "%s Indeksi i forumit";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "Posto temë të re";
$lang['Reply_to_topic'] = "Përgjigju temës";
$lang['Reply_with_quote'] = "Përgjigju me kuotë";

$lang['Click_return_topic'] = "Kliko %skëtu%s për tu kthyer tek tema"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "Kliko %skëtu%s për ta riprovuar";
$lang['Click_return_forum'] = "Kliko %skëtu%s për tu kthyer tek forumi";
$lang['Click_view_message'] = "Kliko %skëtu%s për të parë mesazhin tënd";
$lang['Click_return_modcp'] = "Kliko %skëtu%s për tu kthyer Paneli i Kontrollit për Moderatorët";
$lang['Click_return_group'] = "Kliko %skëtu%s për tu kthyer tek informacioni i grupit";

$lang['Admin_panel'] = "Shko tek Paneli i Administrimit";

$lang['Board_disable'] = "Kërkojme ndjesë po ky forum nuk është i disponueshëm";


//
// Global Header strings
//
$lang['Registered_users'] = "Anëtarët e regjistruar";
$lang['Browsing_forum'] = "Përdoruesit që po shfletojnë forumin:";
$lang['Online_users_zero_total'] = "<b>0</b> përdorues online:";
$lang['Online_users_total'] = "<b>%d</b> përdorues online:";
$lang['Online_user_total'] = "<b>%d</b> përdorues online:";
$lang['Reg_users_zero_total'] = " 0 anëtarë";
$lang['Reg_users_total'] = " %d anëtarë";
$lang['Reg_user_total'] = " %d anëtar";
$lang['Hidden_users_zero_total'] = " 0 të fshehur";
$lang['Hidden_user_total'] = " %d të fshehur";
$lang['Hidden_users_total'] = " %d të fshehur";
$lang['Guest_users_zero_total'] = " 0 vizitorë";
$lang['Guest_users_total'] = " %d vizitorë";
$lang['Guest_user_total'] = " %d vizitor";
$lang['Record_online_users'] = "Nr. Rekord i përdoruesve online ishte <b>%s</b> më %s"; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = "%sAdministrator%s";
$lang['Mod_online_color'] = "%sModerator%s";

$lang['You_last_visit'] = "Hera e fundit që vizituat %s"; // %s replaced by date/time
$lang['Current_time'] = "Ora është %s"; // %s replaced by time

$lang['Search_new'] = "Shiko mesazhet që nga vizita e fundit";
$lang['Search_your_posts'] = "Shiko mesazhet e tua";
$lang['Search_unanswered'] = "Shiko mesazhet pa përgjigje";

$lang['Register'] = "Regjistrohu";
$lang['Profile'] = "Profili";
$lang['Edit_profile'] = "Modifiko profilin";
$lang['Search'] = "Kërko";
$lang['Memberlist'] = "Lista e Anëtarëve";
$lang['FAQ'] = "FAQ";
$lang['BBCode_guide'] = "Udhëzuesi i BBCode";
$lang['Usergroups'] = "Grupet e Anëtarëve";
$lang['Last_Post'] = "Mesazhi i fundit";
$lang['Moderator'] = "Moderator";
$lang['Moderators'] = "Moderatorë";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "Anëtarët e këtij forumi kanë postuar <b>0</b> artikuj"; // Number of posts
$lang['Posted_articles_total'] = "Anëtarët e këtij forumi kanë postuar <b>%d</b> artikuj"; // Number of posts
$lang['Posted_article_total'] = "Anëtarët e këtij forumi kanë postuar <b>%d</b> artikull"; // Number of posts
$lang['Registered_users_zero_total'] = "Forumi ka <b>0</b> anëtarë të regjistruar"; // # registered users
$lang['Registered_users_total'] = "Forumi ka <b>%d</b> anëtarë të regjistruar"; // # registered users
$lang['Registered_user_total'] = "Forumi ka <b>%d</b> anëtar të regjistruar"; // # registered users
$lang['Newest_user'] = "Anëtari më i ri është <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "Asnjë mesazh i ri që nga vizita juaj e fundit";
$lang['No_new_posts'] = "Asnjë mesazh i ri";
$lang['New_posts'] = "Mesazhe të reja";
$lang['New_post'] = "Mesazh i ri";
$lang['No_new_posts_hot'] = "Asnjë mesazh i ri [ Popular ]";
$lang['New_posts_hot'] = "Mesazhe të reja [ Popular ]";
$lang['No_new_posts_locked'] = "Asnjë mesazh i ri [ Locked ]";
$lang['New_posts_locked'] = "Mesazhe të reja [ Locked ]";
$lang['Forum_is_locked'] = "Forumi është kycur";


//
// Login
//
$lang['Enter_password'] = "Ju lutem shkruani identifikimin dhe fjalkalimin për tu identifikuar";
$lang['Login'] = "Identifikohu";
$lang['Logout'] = "C'identifikohu";

$lang['Forgotten_password'] = "Harrova fjalkalimin";

$lang['Log_me_in'] = "Më identifiko automatikisht sa herë që vizitoj";

$lang['Error_login'] = "Keni specifikuar një llogari inekzistente, inaktive ose një fjalëkalim të gabuar";


//
// Index page
//
$lang['Index'] = "Indeksi";
$lang['No_Posts'] = "Asnjë mesazh";
$lang['No_forums'] = "Ky forum është bosh";

$lang['Private_Message'] = "Mesazh Privat";
$lang['Private_Messages'] = "Mesazhe Private";
$lang['Who_is_Online'] = "Kush është online";

$lang['Mark_all_forums'] = "Shënoji gjithë forumet si të vizituar";
$lang['Forums_marked_read'] = "Të gjithë forumet janë shënuar si të lexuar";


//
// Viewforum
//
$lang['View_forum'] = "Shiko forumin";

$lang['Forum_not_exist'] = "Forumi që zgjodhët nuk ekziston";
$lang['Reached_on_error'] = "Keni arritur tek kjo faqe nëpërmjet një gabimi";

$lang['Display_topics'] = "Shfaq tema nga ";
$lang['All_Topics'] = "Gjithë temat";

$lang['Topic_Announcement'] = "<b>Lajmërim:</b>";
$lang['Topic_Sticky'] = "<b>Ngjitës</b>";
$lang['Topic_Moved'] = "<b>Ka lëvizur</b>";
$lang['Topic_Poll'] = "<b>[ Sondazh ]</b>";

$lang['Mark_all_topics'] = "Shënoji gjithë temat si të lexuara";
$lang['Topics_marked_read'] = "Temat e këtij forumi u shënuan si të lexuara";

$lang['Rules_post_can'] = "Ju <b>mund</b> të krijoni tema të reja në këtë forum";
$lang['Rules_post_cannot'] = "Ju <b>nuk mund</b> të krijoni tema të reja në këtë forum";
$lang['Rules_reply_can'] = "Ju <b>mund</b> ti përgjigjeni temave të këtij forumi";
$lang['Rules_reply_cannot'] = "Ju <b>nuk mund</b> ti përgjigjeni temave të këtij forumi";
$lang['Rules_edit_can'] = "Ju <b>mund</b> të modifikoni postimet tuaja në këtë forum";
$lang['Rules_edit_cannot'] = "Ju <b>nuk mund</b> të modifikoni postimet tuaja në këtë forum";
$lang['Rules_delete_can'] = "Ju <b>mund</b> të fshini postimet tuaja në këtë forum";
$lang['Rules_delete_cannot'] = "Ju <b>nuk mund</b> të fshini postimet tuaja në këtë forum";
$lang['Rules_vote_can'] = "Ju <b>mund</b> të votoni në votimet e këtij forumi";
$lang['Rules_vote_cannot'] = "Ju <b>nuk mund</b> të votoni në votimet e këtij forumi";
$lang['Rules_moderate'] = "Ju <b>mund</b> të %smoderoni këtë forum%s"; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = "Nuk ka asnjë mesazh në këtë forum<br />Kliko tek <b>Hap temë të re</b> për të hapur një";


//
// Viewtopic
//
$lang['View_topic'] = "Shiko temën";

$lang['Guest'] = 'Guest';
$lang['Post_subject'] = "Titulli i mesazhit";
$lang['View_next_topic'] = "Shiko temën pasuese";
$lang['View_previous_topic'] = "Shiko temën e mëparshme";
$lang['Submit_vote'] = "Paraqit votën";
$lang['View_results'] = "Shiko rezultatin";

$lang['No_newer_topics'] = "Nuk ka tema më të reja në këtë forum";
$lang['No_older_topics'] = "Nuk ka tema më të vjetra në këtë forum";
$lang['Topic_post_not_exist'] = "Tema ose mesazhi që kërkuat nuk ekziston";
$lang['No_posts_topic'] = "Nuk ka asnjë mezash për këtë temë";

$lang['Display_posts'] = "Shfaq mesazhe nga";
$lang['All_Posts'] = "Të gjitha mesazhet";
$lang['Newest_First'] = "Më i riu në krye";
$lang['Oldest_First'] = "Më i vjetri në krye";

$lang['Back_to_top'] = "Mbrapsht në krye";

$lang['Read_profile'] = "Shiko profilin e anëtarit"; 
$lang['Send_email'] = "Dërgo email";
$lang['Visit_website'] = "Vizito websitin e shkruesit";
$lang['ICQ_status'] = "Statusi në ICQ";
$lang['Edit_delete_post'] = "Modifiko/fshi këtë mesazh";
$lang['View_IP'] = "Shiko IP e shkruesit";
$lang['Delete_post'] = "Fshije këtë mesazh";

$lang['wrote'] = "shkruajti"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Kuotë"; // comes before bbcode quote output.
$lang['Code'] = "Kodi"; // comes before bbcode code output.

$lang['Edited_time_total'] = "Edituar për herë të fundit nga %s në %s, edituar %d herë gjithsej"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "Edituar për herë të fundit nga %s në %s, edituar %d herë gjithsej"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Kyce këtë temë";
$lang['Unlock_topic'] = "Shkyce këtë temë";
$lang['Move_topic'] = "Zhvendose këtë temë";
$lang['Delete_topic'] = "Fshije këtë temë";
$lang['Split_topic'] = "Ndaje këtë temë";

$lang['Stop_watching_topic'] = "Ndalo së vëzhguari këtë temë";
$lang['Start_watching_topic'] = "Vëzhgo këtë temë për përgjigje";
$lang['No_longer_watching'] = "Ju nuk e vëzhgoni më këtë temë";
$lang['You_are_watching'] = "Ju jeni duke e vëzhguar këtë temë";

$lang['Total_votes'] = "Totali i votave";

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Përmbajtja e mesazhit";
$lang['Topic_review'] = "Shqyrto temën";

$lang['No_post_mode'] = "Mënyra e postimit nuk është specifikuar"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = "Hap një temë të re";
$lang['Post_a_reply'] = "Përgjigju";
$lang['Post_topic_as'] = "Hap një temë si";
$lang['Edit_Post'] = "Modifiko mesazhin";
$lang['Options'] = "Mundësitë";

$lang['Post_Announcement'] = "Lajmërim";
$lang['Post_Sticky'] = "Ngjitës";
$lang['Post_Normal'] = "Normal";

$lang['Confirm_delete'] = "Jeni i sigurtë për fshirjen e këtij mesazhi?";
$lang['Confirm_delete_poll'] = "Jeni i sigurtë për fshirjen e këtij sondazhi?";

$lang['Flood_Error'] = "Nuk mund të postoni prapë menjëherë";
$lang['Empty_subject'] = "Duhet të specifikoni një titull kur postoni një mesazh";
$lang['Empty_message'] = "Duhet të shkruani dicka kur postoni një mesazh";
$lang['Forum_locked'] = "Forumi është kycur. Postimi, modifikimi dhe fshirja e temave s'lejohet";
$lang['Topic_locked'] = "Forumi është kycur. Postimi dhe modifikimi i mesazheve nuk lejohet";
$lang['No_post_id'] = "Nuk u specifikua ID e postimit";
$lang['No_topic_id'] = "Duhet të zgjidhni një temë për tu përgjigjur";
$lang['No_valid_mode'] = "Ju vetëm mund të postoni, përgjigjeni, modifikoni ose kuotoni mesazhet...ju lutem provojeni prapë";
$lang['No_such_post'] = "Një post i tillë nuk ekziston, ju lutem provoni prapë";
$lang['Edit_own_posts'] = "Na vjen keq po ju mund të editoni vetëm mesazhet tuaja";
$lang['Delete_own_posts'] = "Na vjen keq po ju mund të fshini vetëm mesazhet tuaja";
$lang['Cannot_delete_replied'] = "Na vjen keq po ju nuk mund të fshini mesazhe të cilat kanë përgjigje";
$lang['Cannot_delete_poll'] = "Na vjen keq po ju nuk mund të fshini një sondazh aktiv";
$lang['Empty_poll_title'] = "Duhet të specifikoni një titull për sondazhin tuaj";
$lang['To_few_poll_options'] = "Duhet të specifikoni të paktën dy zgjedhje për sondazhin";
$lang['To_many_poll_options'] = "Keni vënë shumë zgjedhje për sondazhin";
$lang['Post_has_no_poll'] = "Ky mesazh nuk ka sondazh";

$lang['Add_poll'] = "Hap një sondazh";
$lang['Add_poll_explain'] = "Nqs nuk do të shtosh një sondazh tek tema, lëre fushën bosh";
$lang['Poll_question'] = "Pyetja e sondazhit";
$lang['Poll_option'] = "Zgjedhje sondazhi";
$lang['Add_option'] = "Shto mundësi";
$lang['Update'] = "Ri-fresko";
$lang['Delete'] = "Fshi";
$lang['Poll_for'] = "Vazhdo sondazhin për";
$lang['Days'] = "Ditë"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ Shkruaj 0 ose lër bosh për një sondazh që vazhdon gjithmonë ]";
$lang['Delete_poll'] = "Fshi sondazhin";

$lang['Disable_HTML_post'] = "Disaktivizo HTML në këtë mesazh";
$lang['Disable_BBCode_post'] = "Disaktivizo BBCode në këtë mesazh";
$lang['Disable_Smilies_post'] = "Disaktivizo figurinat në këtë mesazh";

$lang['HTML_is_ON'] = "HTML është e <u>aktivizuar</u>";
$lang['HTML_is_OFF'] = "HTML është e <u>disaktivizuar</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s është i <u>aktivizuar</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s është i <u>disaktivizuar</u>";
$lang['Smilies_are_ON'] = "Figurinat janë <u>aktivizuar</u>";
$lang['Smilies_are_OFF'] = "Figurinat janë <u>disaktivizuar</u>";

$lang['Attach_signature'] = "Bashkangjit firmën (firma mund të modifikohet tek profili)";
$lang['Notify'] = "Më njofto kur dikush përgjigjet";
$lang['Delete_post'] = "Fshije këtë mesazh";

$lang['Stored'] = "Mesazhi juaj u postua me sukses";
$lang['Deleted'] = "Mesazhi juaj u fshi me sukses";
$lang['Poll_delete'] = "Sondazhi juaj u fshi me sukses";
$lang['Vote_cast'] = "Vota juaj u regjistrua";

$lang['Topic_reply_notification'] = "Njoftim për përgjigje tek tema";

$lang['bbcode_b_help'] = "Bold text: [b]text[/b] (alt+b)";
$lang['bbcode_i_help'] = "Italic text: [i]text[/i] (alt+i)";
$lang['bbcode_u_help'] = "Underline text: [u]text[/u] (alt+u)";
$lang['bbcode_q_help'] = "Quote text: [quote]text[/quote] (alt+q)";
$lang['bbcode_c_help'] = "Code display: [code]code[/code] (alt+c)";
$lang['bbcode_l_help'] = "List: [list]text[/list] (alt+l)";
$lang['bbcode_o_help'] = "Ordered list: [list=]text[/list] (alt+o)";
$lang['bbcode_p_help'] = "Insert image: [img]http://image_url[/img] (alt+p)";
$lang['bbcode_w_help'] = "Insert URL: [url]http://url[/url] or [url=http://url]URL text[/url] (alt+w)";
$lang['bbcode_a_help'] = "Close all open bbCode tags";
$lang['bbcode_s_help'] = "Font color: [color=red]text[/color] Tip: you can also use color=#FF0000";
$lang['bbcode_f_help'] = "Font size: [size=x-small]small text[/size]";

$lang['Emoticons'] = "Emocionet";
$lang['More_emoticons'] = "Trego më shumë emocione";

$lang['Font_color'] = "Ngjyra e fontit";
$lang['color_default'] = "E paracaktuar";
$lang['color_dark_red'] = "E kuqe e errët";
$lang['color_red'] = "E kuqe";
$lang['color_orange'] = "Portokalli";
$lang['color_brown'] = "Kafe";
$lang['color_yellow'] = "E verdhë";
$lang['color_green'] = "Jeshile";
$lang['color_olive'] = "Ngjyrë ulliri";
$lang['color_cyan'] = "Bojëqielli";
$lang['color_blue'] = "Blu";
$lang['color_dark_blue'] = "Blu e errët";
$lang['color_indigo'] = "Lejla";
$lang['color_violet'] = "Vjollcë";
$lang['color_white'] = "E bardhë";
$lang['color_black'] = "E zezë";

$lang['Font_size'] = "Nr. i fontit";
$lang['font_tiny'] = "i vockël";
$lang['font_small'] = "i vogël";
$lang['font_normal'] = "normal";
$lang['font_large'] = "i madh";
$lang['font_huge'] = "i stërmadh";

$lang['Close_Tags'] = "Close Tags";
$lang['Styles_tip'] = "Tip: Styles can be applied quickly to selected text";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Mesazhim privat";

$lang['Login_check_pm'] = "Identifikohu për të parë mesazhet private";
$lang['New_pms'] = "Ju keni %d mesazhe të reja"; // You have 2 new messages
$lang['New_pm'] = "Ju keni %d mesazh të ri"; // You have 1 new message
$lang['No_new_pm'] = "Ju nuk keni asnjë mesazh të ri";
$lang['Unread_pms'] = "Ju keni %d mesazhe të palexuara";
$lang['Unread_pm'] = "Ju keni % mesazh të palexuar";
$lang['No_unread_pm'] = "Ju nuk keni mesazhe të palexuara";
$lang['You_new_pm'] = "Një mesazh privat i ri ka ardhur për ju tek Inbox";
$lang['You_new_pms'] = "Disa mesazhe private të reja kanë ardhur për ju tek Inbox";
$lang['You_no_new_pm'] = "Asnjë mesazh privat i ri në Inbox";

$lang['Inbox'] = "Inbox";
$lang['Outbox'] = "Outbox";
$lang['Savebox'] = "Savebox";
$lang['Sentbox'] = "Sentbox";
$lang['Flag'] = "Flag";
$lang['Subject'] = "Titulli";
$lang['From'] = "Nga";
$lang['To'] = "Për";
$lang['Date'] = "Data";
$lang['Mark'] = "Shëno";
$lang['Sent'] = "Dërguar";
$lang['Saved'] = "Ruajtur";
$lang['Delete_marked'] = "Fshi të shënuarët";
$lang['Delete_all'] = "Fshiji të gjithë";
$lang['Save_marked'] = "Ruaji të shënuarët"; 
$lang['Save_message'] = "Ruaj mesazhin";
$lang['Delete_message'] = "Fshi mesazhin";

$lang['Display_messages'] = "Shfaq mesazhe nga"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "Të gjithë mesazhet";

$lang['No_messages_folder'] = "Ju nuk keni asnjë mesazh në këtë dosje";

$lang['PM_disabled'] = "Mesazhet private nuk lejohen në këtë forum";
$lang['Cannot_send_privmsg'] = "Na vjen keq, po administratori jua ka ndaluar dërgimin e mesazheve private";
$lang['No_to_user'] = "Duhet të specifikoni një emër për të dërguar këtë mesazh";
$lang['No_such_user'] = "Na vjen keq po ky anëtar nuk ekziston";

$lang['Disable_HTML_pm'] = "C'aktivizo HTML në këtë mesazh";
$lang['Disable_BBCode_pm'] = "C'aktivizo BBCode në këtë mesazh";
$lang['Disable_Smilies_pm'] = "C'aktivizo figurinat në këtë mesazh";

$lang['Message_sent'] = "Mesazhi juaj u dërgua";

$lang['Click_return_inbox'] = "Kliko %skëtu%s për tu kthyer tek Inbox";
$lang['Click_return_index'] = "Kliko %skëtu%s për tu kthyer tek Indeksi";

$lang['Send_a_new_message'] = "Dërgo një mesazh të ri privat";
$lang['Send_a_reply'] = "Përgjigju një mesazhi privat";
$lang['Edit_message'] = "Modifiko mesazhin privat";

$lang['Notification_subject'] = "Një mesazh i ri privat ka ardhur";

$lang['Find_username'] = "Gjej një anëtar";
$lang['Find'] = "Gjej";
$lang['No_match'] = "Nuk u gjet asnjë";

$lang['No_post_id'] = "Nuk u specifikua ID e postimit";
$lang['No_such_folder'] = "Një dosje e tillë nuk ekziston";
$lang['No_folder'] = "Asnjë dosje nuk u specifikua";

$lang['Mark_all'] = "Shënoji të gjithë/a";
$lang['Unmark_all'] = "De-Shëno të gjitha :)";

$lang['Confirm_delete_pm'] = "Jeni i sigurtë që doni ta fshini këtë mesazh?";
$lang['Confirm_delete_pms'] = "Jeni i sigurtë që doni ti fshini këto mesazhe?";

$lang['Inbox_size'] = "Ju e keni Inbox %d%% plot"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Ju e keni Sentbox %d%% plot"; 
$lang['Savebox_size'] = "Ju e keni Savebox %d%% plot"; 

$lang['Click_view_privmsg'] = "Kliko %skëtu%s për të vizituar Inbox-in tuaj";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Duke parë profilin :: %s"; // %s is username 
$lang['About_user'] = "Gjithcka mbi %s"; // %s is username

$lang['Preferences'] = "Preferencat";
$lang['Items_required'] = "Artikujt e shënuar me * janë të domosdoshëm (unless stated otherwise)";
$lang['Registration_info'] = "Informacioni i regjistrimit";
$lang['Profile_info'] = "Informacioni i profilit";
$lang['Profile_info_warn'] = "Ky informacion do jetë i disponueshëm tek publiku";
$lang['Avatar_panel'] = "Paneli i kontrollit të ikonave personale";
$lang['Avatar_gallery'] = "Galeria e ikonave personale";

$lang['Website'] = "Websit";
$lang['Location'] = "Vendodhja";
$lang['Contact'] = "Kontakto";
$lang['Email_address'] = "Adresa e e-mail";
$lang['Email'] = "Email";
$lang['Send_private_message'] = "Dërgo mesazh privat";
$lang['Hidden_email'] = "[ I/e fshehur ]";
$lang['Search_user_posts'] = "Gjej gjithë mesazhet nga %s";
$lang['Interests'] = "Interesat";
$lang['Occupation'] = "Profesioni"; 
$lang['Poster_rank'] = "Grada e anëtarit";

$lang['Total_posts'] = "Nr. total i mesazheve";
$lang['User_post_pct_stats'] = "%.2f%% i totalit"; // 1.25% of total
$lang['User_post_day_stats'] = "%.2f mesazhe në ditë"; // 1.5 posts per day
$lang['Search_user_posts'] = "Gjej gjithë mesazhet nga %s"; // Find all posts by username

$lang['No_user_id_specified'] = "Na vjen keq po ai anëtar nuk ekziston";
$lang['Wrong_Profile'] = "Nuk lejohet modifiki i profilit të një tjetri";

$lang['Only_one_avatar'] = "Lejohet vetëm një ikonë personale";
$lang['File_no_data'] = "Skedari tek URL që specifikuat është i korruptuar";
$lang['No_connection_URL'] = "Lidhja me URL që specifikuat është e pamundur për momentin";
$lang['Incomplete_URL'] = "URL që specifikuat nuk ekziston";
$lang['Wrong_remote_avatar_format'] = "URL e ikonës personale nuk është e saktë";
$lang['No_send_account_inactive'] = "Na vjen keq, po fjalëkalimi juaj nuk mund të nxirret nga regjistri sepse llogaria juaj nuk është aktive. Kontaktoni administratorin ";

$lang['Always_smile'] = "Lejo figurinat gjithmonë";
$lang['Always_html'] = "Lejo HTML gjithmonë";
$lang['Always_bbcode'] = "Lejo BBCode gjithmonë";
$lang['Always_add_sig'] = "Bashkangjite firmën gjithmonë";
$lang['Always_notify'] = "Më njofto gjithmonë kur ka përgjigje";
$lang['Always_notify_explain'] = "Dërgon një email kur dikush shkruan në një temë ku keni shkruar edhe ju. Ky opsion mund të ndryshohet sa herë që poston";

$lang['Board_style'] = "Stili i forumit";
$lang['Board_lang'] = "Gjuha e forumit";
$lang['No_themes'] = "Asnjë temë në regjistër";
$lang['Timezone'] = "Brezi orar";
$lang['Date_format'] = "Formati i kohës";
$lang['Date_format_explain'] = "Sintaksa e përdorur është identike me atë të funksionit  <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> të PHP";
$lang['Signature'] = "Firma";
$lang['Signature_explain'] = "Kjo është një thënie ose grup fjalësh që i bashkangjitet cdo meszhi që shkruani. Një limit prej %d karakteresh ekziston";
$lang['Public_view_email'] = "Gjithmonë tregoje adresën time të e-mail";

$lang['Current_password'] = "Fjalëkalimi i tanishëm";
$lang['New_password'] = "Fjalëkalimi i ri";
$lang['Confirm_password'] = "Konfirmo fjalëkalimin";
$lang['Confirm_password_explain'] = "You must confirm your current password if you wish to change it or alter your email address";
$lang['password_if_changed'] = "Vendosja e një fjalëkalimi është e nevojshme vetëm nëse doni të ndryshoni fjalëkalimin e tanishëm";
$lang['password_confirm_if_changed'] = "Konfirmimi i fjalëkalimit është i nevojshëm vetëm nëse doni të ndryshoni fjalëkalimin e tanishëm";

$lang['Avatar'] = "Ikona personale";
$lang['Avatar_explain'] = "Shfaq një imazh të vogël poshtë emrit tuaj kur postoni. Vetëm një imazh është i lejuar dhe gjerësia lejohet deri në %d pixel, lartësia deri në %d pixel dhe madhësia e skedarit deri në %d kB."; 
$lang['Upload_Avatar_URL'] = "Ngarko ikonën nga interneti";
$lang['Upload_Avatar_URL_explain'] = "Shkruaj adresën e ikonës, do kopjohet këtu";
$lang['Pick_local_Avatar'] = "Zgjidh një ikonë nga galeria";
$lang['Link_remote_Avatar'] = "Link to off-site Avatar";
$lang['Link_remote_Avatar_explain'] = "Specifiko adresën e internetit (URL) të imazhit që doni të lidhni si ikonë";
$lang['Avatar_URL'] = "URL of Avatar Image";
$lang['Select_from_gallery'] = "Select Avatar from gallery";
$lang['View_avatar_gallery'] = "Show gallery";

$lang['Select_avatar'] = "Zgjidh ikonën";
$lang['Return_profile'] = "Anulloje ikonën";
$lang['Select_category'] = "Zgjidh kategorinë";

$lang['Delete_Image'] = "Fshi imazhin";
$lang['Current_Image'] = "Imazhi i tanishëm";

$lang['Notify_on_privmsg'] = "Më njofto për cdo mesazh privat";
$lang['Popup_on_privmsg'] = "Hap dritare të re kur merr mesazh privat"; 
$lang['Popup_on_privmsg_explain'] = "Some templates may open a new window to inform you when new private messages arrive"; 
$lang['Hide_user'] = "Hide your online status";

$lang['Profile_updated'] = "Profili juaj u rifreskua";
$lang['Profile_updated_inactive'] = "Your profile has been updated, however you have changed vital details thus your account is now inactive. Check your email to find out how to reactivate your account, or if admin activation is require wait for the administrator to reactivate your account";

$lang['Password_mismatch'] = "Fjalëkalimet që specifikuat janë të ndryshëm";
$lang['Current_password_mismatch'] = "Fjalëkalimi që specifikuat nuk është i njëjtë me atë në regjistrin tonë";
$lang['Password_long'] = "Fjalëkalimi juaj nuk duhet të ketë më shumë se 32 karaktere";
$lang['Username_taken'] = "Na vjen keq po ky identifikim është në përdorim";
$lang['Username_invalid'] = "Na vjen keq po ky identifikim përmban një karakter invalid si psh. \"";
$lang['Username_disallowed'] = "Na vjen keq po ky identifikim nuk është i lejueshëm";
$lang['Email_taken'] = "Na vjen keq po ajo adresë poste elektronike është përdorur më parë";
$lang['Email_banned'] = "Na vjen keq po ajo adresë poste elektronike është përjashtuar";
$lang['Email_invalid'] = "Na vjen keq po ajo adresë poste elektronike është invalide";
$lang['Signature_too_long'] = "Firma juaj është shumë e gjatë";
$lang['Fields_empty'] = "Duhet të mbushni fushat e domosdoshme";
$lang['Avatar_filetype'] = "Lloji i skedarit të ikonës personale duhet të jetë .jpg, .gif or .png";
$lang['Avatar_filesize'] = "Madhësia e skedarit të ikonës personale lejohet deri në %d kB"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "Ikona personale duhet të jetë deri në %d pixel e gjërë dhe %d pixel e lartë"; 

$lang['Welcome_subject'] = "Mirësevini tek %s Forums"; // Welcome to my.com forums
$lang['New_account_subject'] = "Llogari e re për anëtarë";
$lang['Account_activated_subject'] = "Llogaria u aktivizua";

$lang['Account_added'] = "Faleminderit për regjistrimin, llogaria juaj u hap. Ju tashti keni mundësi të identifikoheni. ";
$lang['Account_inactive'] = "Llogaria juaj u hap. Megjithatë, ky forum kërkon aktivizimin e llogarisë. Një mesazh me celësin e aktivizimit u dërgua tek adresa që dhatë. Shikoni mesazhin për më shumë informacion.";
$lang['Account_inactive_admin'] = "Llogaria juaj u hap. Megjithatë, ky forum kërkon aktivizimin e llogarisë nga administratori. Një mesazh do ju dërgohet sapo llogaria juaj të aktivizohet.";
$lang['Account_active'] = "Llogaria juaj u aktivizua, faleminderit për regjistrimin";
$lang['Account_active_admin'] = "Llogaria u aktivizua";
$lang['Reactivate'] = "Riaktivizo llogarinë";
$lang['COPPA'] = "Llogaria juaj u krijua por duhet të aprovohet nga administratori. Shikoni postën elektronike për detaje.";

$lang['Registration'] = "Kushtet e Regjistrimit";
$lang['Reg_agreement'] = "Megjithëse administratorët dhe moderatorët e këtij forumi do mundohen të fshijnë ose redaktojnë shkrime të kundërshtueshme sa më parë, është e pamundur që të rishikohet cdo mesazh. Prandaj ju duhet të kuptoni se mesazhet e postuara në këtë forum janë shprehje e opinionit të autorit dhe jo të administratorit, moderatorëve apo webmasterit (përvec rasteve kur këta të fundit janë autorë të shkrimeve) dhe ata nuk mund të mbahen përgjegjës. <br /><br />Ju pranoni të mos shkruani mesazhe abuzuese, vulgare, të neveritshme, urryese, kërcënuese, shpifëse apo cdo lloj materiali që mund të bjerë ndesh me ligjet në përdorim. Shkrime të tilla do cojnë në përjashtimin tuaj të menjëhershëm dhe të përhershëm (dhe njoftimin e ISP-së tuaj). Adresa IP për cdo mesazh regjistrohet për të bërë të mundur aplikimin e këtyre procedurave. Ju pranoni që webmasteri, administratori dhe moderatorët e këtij forumi kanë të drejtë të fshijnë, redaktojnë, zhvendosin apo kycin cdo temë sipas gjykimit të tyre. Si përdorues ju pranoni që informacioni që ju dhatë do të ruhet në një regjistër. Megjithëse ky informacion nuk do i jepet askujt pa lejen tuaj webmasteri, administratori dhe moderatorët nuk mund të mbahen përgjegjës nqs ky informacion vidhet.<br /><br />Ky forum përdor cookies për të ruajtur informacion në kompjuterin tuaj. Këto cookies nuk përmbajnë asnjë informacion personal, ato shërbejnë vetëm për përmirësimin e shërbimit që ofrohet nga ky forum. Adresa e postës elektronike përdoret vetëm për konfirmimin e regjistrimit tuaj dhe fjalëkalimit (dhe në rastet kur u dërgohet një fjalëkalimi i ri nqs harroni atë që kishit).";

$lang['Agree_under_13'] = "I pranoj këto kushte dhe jam <b>nën</b> 13 vjec";
$lang['Agree_over_13'] = "I pranoj këto kushte dhe jam <b>mbi</b> 13 vjec";
$lang['Agree_not'] = "Nuk i pranoj këto kushte";

$lang['Wrong_activation'] = "Celësi i aktivizimit që dhatë nuk përkon me asnjë celës në regjistrin tonë.";
$lang['Send_password'] = "Më dërgo një fjalëkalim të ri"; 
$lang['Password_updated'] = "Një fjalëkalim i ri u krijua, shiko postën elektronike për detajet e aktivizimit";
$lang['No_email_match'] = "Adresa e postës elektronike që dhatë nuk përkon me adresën e atij anëtari";
$lang['New_password_activation'] = "Aktivizim i fjalëkalimit të ri";
$lang['Password_activated'] = "Llogaria juaj u ri-aktivizua. Jepni fjalëkalimin e ri për tu identifikuar";

$lang['Send_email_msg'] = "Dërgo një mesazh me postë elektronike";
$lang['No_user_specified'] = "Asnjë anëtar nuk u specifikua";
$lang['User_prevent_email'] = "Ky anëtar nuk pranon mesazhe me postë elektronike. Provo ti dërgosh një mesazh privat.";
$lang['User_not_exist'] = "Ai anëtar nuk ekziston";
$lang['CC_email'] = "Dërgoi vetes një kopje të mesazhit";
$lang['Email_message_desc'] = "Ky mesazh do dërgohet si tekst i thjeshtë. Mos përdor HTML ose BBCode. Adresa e kthimit do jetë adresa juaj.";
$lang['Flood_email_limit'] = "Nuk mund të dërgosh një mesazh tashti. Provo më vonë";
$lang['Recipient'] = "Marrësi";
$lang['Email_sent'] = "Mesazhi u dërgua";
$lang['Send_email'] = "Dërgo një mesazh";
$lang['Empty_subject_email'] = "Duhet të specifikoni një subjekt për këtë mesazh";
$lang['Empty_message_email'] = "Duhet të shkruani dicka që të dërgohet ky mesazh ";


//
// Memberslist
//
$lang['Select_sort_method'] = "Zgjidh metodën e renditjes";
$lang['Sort'] = "Rendit";
$lang['Sort_Top_Ten'] = "Top Ten Posters";
$lang['Sort_Joined'] = "Data e anëtarësimit";
$lang['Sort_Username'] = "Identifikimi";
$lang['Sort_Location'] = "Vendi";
$lang['Sort_Posts'] = "Nr. total i mesazheve";
$lang['Sort_Email'] = "Adresa e postës elektronike";
$lang['Sort_Website'] = "Websiti";
$lang['Sort_Ascending'] = "Në ngjitje";
$lang['Sort_Descending'] = "Në zbritje";
$lang['Order'] = "Rregulli";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Paneli i Kontrollit të Grupeve";
$lang['Group_member_details'] = "Detajet e anëtarësisë së grupeve";
$lang['Group_member_join'] = "Futu në një grup";

$lang['Group_Information'] = "Informacioni i grupit";
$lang['Group_name'] = "Emri i grupit";
$lang['Group_description'] = "Përshkrimi i grupit";
$lang['Group_membership'] = "Anëtarësia e grupit";
$lang['Group_Members'] = "Anëtarët e grupit";
$lang['Group_Moderator'] = "Moderatori i grupit";
$lang['Pending_members'] = "Anëtarët në pritje";

$lang['Group_type'] = "Lloji i grupit";
$lang['Group_open'] = "Grup i hapur";
$lang['Group_closed'] = "Grup i mbyllur";
$lang['Group_hidden'] = "Grup i fshehtë";

$lang['Current_memberships'] = "Anëtarësia aktuale";
$lang['Non_member_groups'] = "Grupet pa anëtarë";
$lang['Memberships_pending'] = "Anëtarësitë në pritje";

$lang['No_groups_exist'] = "Asnjë grup nuk ekziston";
$lang['Group_not_exist'] = "Ai grup nuk ekziston";

$lang['Join_group'] = "Futu në grup";
$lang['No_group_members'] = "Ky grup nuk ka anëtarë";
$lang['Group_hidden_members'] = "Ky grup është i fshehtë. Nuk mund t'ja shikosh anëtarësinë";
$lang['No_pending_group_members'] = "Ky grup nuk ka anëtarë në pritje";
$lang["Group_joined"] = "Ju jeni pajtuar tek ky grup me sukses <br /> Ju do lajmëroheni me postë elektronike kur të aprovoheni nga moderatori i grupit";
$lang['Group_request'] = "Eshtë bërë një kërkesë për anëtarësim";
$lang['Group_approved'] = "Kërkesa juaj u aprovua";
$lang['Group_added'] = "Ju shtuat këtij grupi"; 
$lang['Already_member_group'] = "Ju jeni anëtar i këtij grupi tashmë!";
$lang['User_is_member_group'] = "Përdoruesi është anëtar i këtij grupi tashmë";
$lang['Group_type_updated'] = "Lloji i grupit u ri-freskua";

$lang['Could_not_add_user'] = "Përdoruesi që zgjodhët nuk ekziston";
$lang['Could_not_anon_user'] = "Nuk mund ta bësh anëtar grupi përdoruesin Anonymous";

$lang['Confirm_unsub'] = "Jeni i sigurtë për anullimin e pajtimit tek ky grup?";
$lang['Confirm_unsub_pending'] = "Pajtimi juaj tek ky grup nuk është aprovuar akoma,  jeni i sigurtë që doni ta anulloni?";

$lang['Unsub_success'] = "Pajtimi juaj tek ky grup është anulluar";

$lang['Approve_selected'] = "Aprovo të zgjedhurit";
$lang['Deny_selected'] = "Kundërshto të zgjedhurit";
$lang['Not_logged_in'] = "Duhet të jeni i identifikuar për tu futur në një grup";
$lang['Remove_selected'] = "Hiq të zgjedhurit";
$lang['Add_member'] = "Shto anëtar";
$lang['Not_group_moderator'] = "Nuk mund ta kryeni atë veprim sepse nuk jeni moderatori i këtij grupi";

$lang['Login_to_join'] = "Identifikohu për tu futur në një grup ose për të menaxhuar anëtarësitë";
$lang['This_open_group'] = "Ky është një grup i hapur, klikoni për të kërkuar anëtarësi";
$lang['This_closed_group'] = "Ky grup është mbyllur, nuk pranohen më anëtarë";
$lang['This_hidden_group'] = "Ky është grup i fshehtë, nuk lejohet aplikimi automatik";
$lang['Member_this_group'] = "Ju jeni anëtar i këtij grupi";
$lang['Pending_this_group'] = "Kërkesa juaj për anëtarësi në këtë grup nuk është konfirmuar akoma";
$lang['Are_group_moderator'] = "Ju jeni moderatori i grupit";
$lang['None'] = "Asnjë";

$lang['Subscribe'] = "Pajtohu";
$lang['Unsubscribe'] = "Anullo pajtimin";
$lang['View_Information'] = "Shiko informacionin";


//
// Search
//
$lang['Search_query'] = "Search Query";
$lang['Search_options'] = "Search Options";

$lang['Search_keywords'] = "Search for Keywords";
$lang['Search_keywords_explain'] = "You can use <u>AND</u> to define words which must be in the results, <u>OR</u> to define words which may be in the result and <u>NOT</u> to define words which should not be in the result. Use * as a wildcard for partial matches";
$lang['Search_author'] = "Kërko për autorin";
$lang['Search_author_explain'] = "Use * as a wildcard for partial matches";

$lang['Search_for_any'] = "Search for any terms or use query as entered";
$lang['Search_for_all'] = "Search for all terms";
$lang['Search_title_msg'] = "Kërko titullin dhe përmbajtjen mesazhit";
$lang['Search_msg_only'] = "Kërko vetëm përmbajtjen mesazhit";

$lang['Return_first'] = "Return first"; // followed by xxx characters in a select box
$lang['characters_posts'] = "characters of posts";

$lang['Search_previous'] = "Search previous"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "Rendit sipas";
$lang['Sort_Time'] = "Kohës së postimit";
$lang['Sort_Post_Subject'] = "Subjekit të mesazhit";
$lang['Sort_Topic_Title'] = "Titullit të temës";
$lang['Sort_Author'] = "Autorit";
$lang['Sort_Forum'] = "Forumit";

$lang['Display_results'] = "Display results as";
$lang['All_available'] = "All available";
$lang['No_searchable_forums'] = "You do not have permissions to search any forum on this site";

$lang['No_search_match'] = "No topics or posts met your search criteria";
$lang['Found_search_match'] = "Search found %d match"; // eg. Search found 1 match
$lang['Found_search_matches'] = "Search found %d matches"; // eg. Search found 24 matches

$lang['Close_window'] = "Close Window";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "Sorry but only %s can post announcements in this forum";
$lang['Sorry_auth_sticky'] = "Sorry but only %s can post sticky messages in this forum"; 
$lang['Sorry_auth_read'] = "Sorry but only %s can read topics in this forum"; 
$lang['Sorry_auth_post'] = "Sorry but only %s can post topics in this forum"; 
$lang['Sorry_auth_reply'] = "Sorry but only %s can reply to posts in this forum"; 
$lang['Sorry_auth_edit'] = "Sorry but only %s can edit posts in this forum"; 
$lang['Sorry_auth_delete'] = "Sorry but only %s can delete posts in this forum"; 
$lang['Sorry_auth_vote'] = "Sorry but only %s can vote in polls in this forum"; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>përdorues anonimë</b>";
$lang['Auth_Registered_Users'] = "<b>përdorues të regjistruar</b>";
$lang['Auth_Users_granted_access'] = "<b>users granted special access</b>";
$lang['Auth_Moderators'] = "<b>moderatorët</b>";
$lang['Auth_Administrators'] = "<b>administratorët</b>";

$lang['Not_Moderator'] = "Ju nuk jeni moderator i këtij forumi";
$lang['Not_Authorised'] = "I pa autorizuar";

$lang['You_been_banned'] = "Ju jeni përjashtuar nga ky forum <br />Kontaktoni webmasterin ose administratorin e forumit";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "Ka 0 anëtarë dhe "; // There ae 5 Registered and
$lang['Reg_users_online'] = "Ka %d anëtarë dhe "; // There ae 5 Registered and
$lang['Reg_user_online'] = "Ka %d anëtar dhe "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "0 Hidden users online"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d anëtarë sekret në linjë"; // 6 Hidden users online
$lang['Hidden_user_online'] = "%d anëtar sekret në linjë"; // 6 Hidden users online
$lang['Guest_users_online'] = "Ka %d vizitorë në linjë"; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = "Ka 0 vizitorë në linjë"; // There are 10 Guest users online
$lang['Guest_user_online'] = "Ka %d vizitor në linjë"; // There is 1 Guest user online
$lang['No_users_browsing'] = "Nuk ka asnjë përdorues në linjë";

$lang['Online_explain'] = "This data is based on users active over the past five minutes";

$lang['Forum_Location'] = "Venddodhja e forumit";
$lang['Last_updated'] = "Rifreskuar më";

$lang['Forum_index'] = "Indeksi i forumit";
$lang['Logging_on'] = "Duke u identifikuar";
$lang['Posting_message'] = "Duke shkruar një mesazh";
$lang['Searching_forums'] = "Duke kërkuar nëpër forum";
$lang['Viewing_profile'] = "Duke parë profilin";
$lang['Viewing_online'] = "Duke parë kush është në linjë";
$lang['Viewing_member_list'] = "Duke parë listën e anëtarëve";
$lang['Viewing_priv_msgs'] = "Duke parë mesazhet private";
$lang['Viewing_FAQ'] = "Duke parë FAQ";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Paneli i kontrollit për moderatorët";
$lang['Mod_CP_explain'] = "Nëpërmjet formularit të mëposhtëm mund të moderoni këtë forum. Mund të kycni, shkycni, lëvizni ose fshini cdo temë ose nr. temash.";

$lang['Select'] = "Zgjidh";
$lang['Delete'] = "Fshi";
$lang['Move'] = "Lëviz";
$lang['Lock'] = "Kyc";
$lang['Unlock'] = "Shkyc";

$lang['Topics_Removed'] = "Temat e zgjedhura u hoqën me sukses nga regjistri";
$lang['Topics_Locked'] = "Temat e zgjedhura u kycën me sukses";
$lang['Topics_Moved'] = "Temat e zgjedhura u zhvendosën me sukses";
$lang['Topics_Unlocked'] = "Temat e zgjedhura u shkycën me sukses";
$lang['No_Topics_Moved'] = "Asnjë temë nuk u zhvendos";

$lang['Confirm_delete_topic'] = "Jeni i sigurtë që doni të fshini temën/at e zgjedhur/a?";
$lang['Confirm_lock_topic'] = "Jeni i sigurtë që doni të kycni temën/at e zgjedhur/a?";
$lang['Confirm_unlock_topic'] = "Jeni i sigurtë që doni të shkycni temën/at e zgjedhur/a?";
$lang['Confirm_move_topic'] = "Jeni i sigurtë që doni të lëvizni temën/at e zgjedhur/a";

$lang['Move_to_forum'] = "Zhvendos tek forumi";
$lang['Leave_shadow_topic'] = "Lër hijen e temës tek forumi i vjetër";

$lang['Split_Topic'] = "Split Topic Control Panel";
$lang['Split_Topic_explain'] = "Using the form below you can split a topic in two, either by selecting the posts individually or by splitting at a selected post";
$lang['Split_title'] = "New topic title";
$lang['Split_forum'] = "Forum for new topic";
$lang['Split_posts'] = "Split selected posts";
$lang['Split_after'] = "Split from selected post";
$lang['Topic_split'] = "The selected topic has been split successfully";

$lang['Too_many_error'] = "You have selected too many posts. You can only select one post to split a topic after!";

$lang['None_selected'] = "You have no selected any topics to preform this operation on. Please go back and select at least one.";
$lang['New_forum'] = "New forum";

$lang['This_posts_IP'] = "IP for this post";
$lang['Other_IP_this_user'] = "Other IP's this user has posted from";
$lang['Users_this_IP'] = "Users posting from this IP";
$lang['IP_info'] = "IP Information";
$lang['Lookup_IP'] = "Look up IP";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "Ora është sipas %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 Hours";
$lang['-11'] = "GMT - 11 Hours";
$lang['-10'] = "HST (Hawaii)";
$lang['-9'] = "GMT - 9 Hours";
$lang['-8'] = "PST (U.S./Canada)";
$lang['-7'] = "MST (U.S./Canada)";
$lang['-6'] = "CST (U.S./Canada)";
$lang['-5'] = "EST (U.S./Canada)";
$lang['-4'] = "GMT - 4 Hours";
$lang['-3.5'] = "GMT - 3.5 Hours";
$lang['-3'] = "GMT - 3 Hours";
$lang['-2'] = "Mid-Atlantic";
$lang['-1'] = "GMT - 1 Hours";
$lang['0'] = "GMT";
$lang['1'] = "CET (Europe)";
$lang['2'] = "EET (Europe)";
$lang['3'] = "GMT + 3 Hours";
$lang['3.5'] = "GMT + 3.5 Hours";
$lang['4'] = "GMT + 4 Hours";
$lang['4.5'] = "GMT + 4.5 Hours";
$lang['5'] = "GMT + 5 Hours";
$lang['5.5'] = "GMT + 5.5 Hours";
$lang['6'] = "GMT + 6 Hours";
$lang['6.5'] = "GMT + 6.5 Hours";
$lang['7'] = "GMT + 7 Hours";
$lang['8'] = "WST (Australia)";
$lang['9'] = "GMT + 9 Hours";
$lang['9.5'] = "CST (Australia)";
$lang['10'] = "EST (Australia)";
$lang['11'] = "GMT + 11 Hours";
$lang['12'] = "GMT + 12 Hours";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT -12:00 hours) Eniwetok, Kwajalein";
$lang['tz']['-11'] = "(GMT -11:00 hours) Midway Island, Samoa";
$lang['tz']['-10'] = "(GMT -10:00 hours) Hawaii";
$lang['tz']['-9'] = "(GMT -9:00 hours) Alaska";
$lang['tz']['-8'] = "(GMT -8:00 hours) Pacific Time (US &amp; Canada), Tijuana";
$lang['tz']['-7'] = "(GMT -7:00 hours) Mountain Time (US &amp; Canada), Arizona";
$lang['tz']['-6'] = "(GMT -6:00 hours) Central Time (US &amp; Canada), Mexico City";
$lang['tz']['-5'] = "(GMT -5:00 hours) Eastern Time (US &amp; Canada), Bogota, Lima, Quito";
$lang['tz']['-4'] = "(GMT -4:00 hours) Atlantic Time (Canada), Caracas, La Paz";
$lang['tz']['-3.5'] = "(GMT -3:30 hours) Newfoundland";
$lang['tz']['-3'] = "(GMT -3:00 hours) Brassila, Buenos Aires, Georgetown, Falkland Is";
$lang['tz']['-2'] = "(GMT -2:00 hours) Mid-Atlantic, Ascension Is., St. Helena";
$lang['tz']['-1'] = "(GMT -1:00 hours) Azores, Cape Verde Islands";
$lang['tz']['0'] = "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia";
$lang['tz']['1'] = "(GMT +1:00 hours) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome";
$lang['tz']['2'] = "(GMT +2:00 hours) Cairo, Helsinki, Kaliningrad, South Africa, Warsaw";
$lang['tz']['3'] = "(GMT +3:00 hours) Baghdad, Riyadh, Moscow, Nairobi";
$lang['tz']['3.5'] = "(GMT +3:30 hours) Tehran";
$lang['tz']['4'] = "(GMT +4:00 hours) Abu Dhabi, Baku, Muscat, Tbilisi";
$lang['tz']['4.5'] = "(GMT +4:30 hours) Kabul";
$lang['tz']['5'] = "(GMT +5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent";
$lang['tz']['5.5'] = "(GMT +5:30 hours) Bombay, Calcutta, Madras, New Delhi";
$lang['tz']['6'] = "(GMT +6:00 hours) Almaty, Colombo, Dhaka, Novosibirsk";
$lang['tz']['6.5'] = "(GMT +6:30 hours) Rangoon";
$lang['tz']['7'] = "(GMT +7:00 hours) Bangkok, Hanoi, Jakarta";
$lang['tz']['8'] = "(GMT +8:00 hours) Beijing, Hong Kong, Perth, Singapore, Taipei";
$lang['tz']['9'] = "(GMT +9:00 hours) Osaka, Sapporo, Seoul, Tokyo, Yakutsk";
$lang['tz']['9.5'] = "(GMT +9:30 hours) Adelaide, Darwin";
$lang['tz']['10'] = "(GMT +10:00 hours) Canberra, Guam, Melbourne, Sydney, Vladivostok";
$lang['tz']['11'] = "(GMT +11:00 hours) Magadan, New Caledonia, Solomon Islands";
$lang['tz']['12'] = "(GMT +12:00 hours) Auckland, Wellington, Fiji, Marshall Island";

$lang['datetime']['Sunday'] = "e Dielë";
$lang['datetime']['Monday'] = "e Hënë";
$lang['datetime']['Tuesday'] = "e Martë";
$lang['datetime']['Wednesday'] = "e Mërkurë";
$lang['datetime']['Thursday'] = "e Enjte";
$lang['datetime']['Friday'] = "e Premte";
$lang['datetime']['Saturday'] = "e Shtunë";
$lang['datetime']['Sun'] = "Sun";
$lang['datetime']['Mon'] = "Mon";
$lang['datetime']['Tue'] = "Tue";
$lang['datetime']['Wed'] = "Wed";
$lang['datetime']['Thu'] = "Thu";
$lang['datetime']['Fri'] = "Fri";
$lang['datetime']['Sat'] = "Sat";
$lang['datetime']['January'] = "Janar";
$lang['datetime']['February'] = "Shkurt";
$lang['datetime']['March'] = "Mars";
$lang['datetime']['April'] = "Prill";
$lang['datetime']['May'] = "Maj";
$lang['datetime']['June'] = "Qershor";
$lang['datetime']['July'] = "Korrik";
$lang['datetime']['August'] = "Gusht";
$lang['datetime']['September'] = "Shtator";
$lang['datetime']['October'] = "Tetor";
$lang['datetime']['November'] = "Nëntor";
$lang['datetime']['December'] = "Dhjetor";
$lang['datetime']['Jan'] = "Jan";
$lang['datetime']['Feb'] = "Feb";
$lang['datetime']['Mar'] = "Mar";
$lang['datetime']['Apr'] = "Apr";
$lang['datetime']['May'] = "May";
$lang['datetime']['Jun'] = "Jun";
$lang['datetime']['Jul'] = "Jul";
$lang['datetime']['Aug'] = "Aug";
$lang['datetime']['Sep'] = "Sep";
$lang['datetime']['Oct'] = "Oct";
$lang['datetime']['Nov'] = "Nov";
$lang['datetime']['Dec'] = "Dec";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "Informacion";
$lang['Critical_Information'] = "Informacion kritik ";

$lang['General_Error'] = "Problem i përgjithshëm";
$lang['Critical_Error'] = "Problem kritik";
$lang['An_error_occured'] = "Pati një problem";
$lang['A_critical_error'] = "Pati një problem kritik";

//
// That's all Folks!
// -------------------------------------------------

?>