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
// Trosiad Cymraeg gan Nic Dafis a Huw Waters
//

//
// The format of this file is ---> $lang['message'] = 'text';
//
// You should also try to set a locale and a character encoding (plus direction). The encoding and direction
// will be sent to the template. The locale may or may not work, it's dependent on OS support and the syntax
// varies ... give it your best guess!
//

$lang['ENCODING'] = 'iso-8859-1';
$lang['DIRECTION'] = 'ltr';
$lang['LEFT'] = 'left';
$lang['RIGHT'] = 'right';
$lang['DATE_FORMAT'] = 'd M Y'; // This should be changed to the default date format for your language, php date() format

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.
$lang['TRANSLATION_INFO'] = 'Trosiad Cymraeg: <a href="http://maes-e.com">Criw Maes-E</a>';

//
// Common, these terms are used   ---   iawn, ond 'yes' a 'no'
// extensively on several pages
//
$lang['Forum'] = 'Seiat';
$lang['Category'] = 'Categori';
$lang['Topic'] = 'Pwnc';
$lang['Topics'] = 'Pynciau';
$lang['Replies'] = 'Ymateb';
$lang['Views'] = 'Golwg';
$lang['Post'] = 'Neges';
$lang['Posts'] = 'Negeseuon';
$lang['Posted'] = 'Postiwyd';
$lang['Username'] = 'Enw Defnyddiwr';
$lang['Password'] = 'Cyfrinair';
$lang['Email'] = 'Ebost';
$lang['Poster'] = 'Postwr';
$lang['Author'] = 'Awdur';
$lang['Time'] = 'Amser';
$lang['Hours'] = 'Awr';
$lang['Message'] = 'Neges';

$lang['1_Day'] = '1 Dydd';
$lang['7_Days'] = 'Wythnos';
$lang['2_Weeks'] = 'Pythefnos';
$lang['1_Month'] = 'Mis';
$lang['3_Months'] = '3 Mis';
$lang['6_Months'] = '6 Mis';
$lang['1_Year'] = 'Blwyddyn';

$lang['Go'] = 'Mynd';
$lang['Jump_to'] = 'Neidio i';
$lang['Submit'] = 'Anfon';
$lang['Reset'] = 'Ailosod';
$lang['Cancel'] = 'Dileu';
$lang['Preview'] = 'Rhagolwg';
$lang['Confirm'] = 'Cadarnhau';
$lang['Spellcheck'] = 'Spellcheck (Saesneg)';
$lang['Yes'] = 'Ie';
$lang['No'] = 'Nage';
$lang['Enabled'] = 'Galluogi';
$lang['Disabled'] = 'Analluogi';
$lang['Error'] = 'Gwall';

$lang['Next'] = 'Nesaf';
$lang['Previous'] = 'Blaenorol';
$lang['Goto_page'] = 'I dudalen';
$lang['Joined'] = 'Ymunwyd';
$lang['IP_Address'] = 'Cyfeiriad IP';

$lang['Select_forum'] = 'Dewis seiat';
$lang['View_latest_post'] = 'Dangos neges diweddaraf';
$lang['View_newest_post'] = 'Dangos neges mwya newydd';
$lang['Page_of'] = 'Tudalen <b>%d</b> o <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'Rhif ICQ';
$lang['AIM'] = 'Cyfeiriad AIM';
$lang['MSNM'] = 'Negesydd MSN';
$lang['YIM'] = 'Negesydd Yahoo';

$lang['Forum_Index'] = 'Hafan';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = 'Dechrau sgwrs newydd';
$lang['Reply_to_topic'] = 'Ychwanegu i\'r sgwrs hwn';
$lang['Reply_with_quote'] = 'Ymateb gyda dyfyniad';

$lang['Click_return_topic'] = 'Rhowch glec %syma%s i ddychwelyd i\'r pwnc'; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = 'Rhowch glec %syma%s i geisio eto';
$lang['Click_return_forum'] = 'Rhowch glec %syma%s i ddychwelyd i\'r seiat';
$lang['Click_view_message'] = 'Rhowch glec %syma%s i weld eich neges';
$lang['Click_return_modcp'] = 'Rhowch glec %syma%s i ddychwelyd i Banel Rheoli Cymedrolwyr';
$lang['Click_return_group'] = 'Rhowch glec %syma%s i ddychwelyd i wybodaeth y grwp';

$lang['Admin_panel'] = 'Hafan Gweinyddu';

$lang['Board_disable'] = 'Mae\'n ddrwg gen i, ond dydy\'r bwrdd hwn ddim ar gael ar hyn o bryd. Dewch yn ôl nes ymlaen.';


//
// Global Header strings - ---------------  iawn
//
$lang['Registered_users'] = 'Defnyddwyr cofrestredig:';
$lang['Browsing_forum'] = 'Defnyddwyr sy\'n pori\'r seiat:';
$lang['Online_users_zero_total'] = 'Does dim un defnyddiwr arlein :: ';
$lang['Online_users_total'] = 'Mae <b>%d</b> o ddefnyddwyr arlein :: ';
$lang['Online_user_total'] = 'Mae <b>%d</b> defnyddiwr arlein :: ';
$lang['Reg_users_zero_total'] = '0 cofrestredig, ';
$lang['Reg_users_total'] = '%d cofrestredig, ';
$lang['Reg_user_total'] = '%d cofrestredig, ';
$lang['Hidden_users_zero_total'] = '0 cuddiedig a ';
$lang['Hidden_user_total'] = '%d cuddiedig a ';
$lang['Hidden_users_total'] = '%d cuddiedig a ';
$lang['Guest_users_zero_total'] = 'dim gwesteion';
$lang['Guest_users_total'] = '%d o westeion';
$lang['Guest_user_total'] = '%d gwestai';
$lang['Record_online_users'] = 'Roedd <b>%s</b> o westeion ar %s'; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = '%sGweinyddwr%s';
$lang['Mod_online_color'] = '%sCymedrolwr%s';

$lang['You_last_visit'] = 'Eich ymweliad diwetha oedd %s'; // %s replaced by date/time
$lang['Current_time'] = 'Y dyddiad ac amser nawr yw %s'; // %s replaced by time

$lang['Search_new'] = 'Gweld negeseuon newydd';
$lang['Search_your_posts'] = 'Eich negeseuon chi';
$lang['Search_unanswered'] = 'Negeseuon heb eu hymateb';

$lang['Register'] = 'Cofrestru';
$lang['Profile'] = 'Proffeil';
$lang['Edit_profile'] = 'Golygu\'ch proffeil';
$lang['Search'] = 'Ymchwilio';
$lang['Memberlist'] = 'Aelodau';
$lang['FAQ'] = 'Help';
$lang['BBCode_guide'] = 'Canllawiau BBCode';
$lang['Usergroups'] = 'Cylchoedd';
$lang['Last_Post'] = 'Neges Diweddaraf';
$lang['Moderator'] = 'Cymedrolwr';
$lang['Moderators'] = 'Cymedrolwyr';


//
// Stats block text -------------------------  iawn
//
$lang['Posted_articles_zero_total'] = 'Postwyd <b>0</b> erthygl gan ein defnyddwyr'; // Number of posts
$lang['Posted_articles_total'] = 'Nifer o negeseuon: <b>%d</b>'; // Number of posts
$lang['Posted_article_total'] = 'Postwyd <b>%d</b> erthygl gan ein defnyddwyr'; // Number of posts
$lang['Registered_users_zero_total'] = 'Does gennym <b>0</b> defnyddiwr cofrestredig'; // # registered users
$lang['Registered_users_total'] = 'Defnyddwyr cofrestredig: <b>%d</b>'; // # registered users
$lang['Registered_user_total'] = 'Mae gennym <b>%d</b> defnyddiwr cofrestredig'; // # registered users
$lang['Newest_user'] = 'Defnyddiwr diweddaraf: <b>%s%s%s</b>'; // a href, username, /a 

$lang['No_new_posts_last_visit'] = 'Dim negeseuon newydd ers i chi ymweld diwetha';
$lang['No_new_posts'] = 'Dim negeseuon newydd';
$lang['New_posts'] = 'Negeseuon newydd';
$lang['New_post'] = 'Neges newydd';
$lang['No_new_posts_hot'] = 'Dim negeseuon newydd [ Poblogaidd ]';
$lang['New_posts_hot'] = 'Negeseuon newydd [ Poblogaidd ]';
$lang['No_new_posts_locked'] = 'Dim negeseuon newydd [ Dan glo ]';
$lang['New_posts_locked'] = 'Negeseuon newydd [ Dan glo ]';
$lang['Forum_is_locked'] = 'Seiat dan glo';


//
// Login -------------------------- iawn
//
$lang['Enter_password'] = 'Rhowch eich enw defnyddiwr a chyfrinair i fewngofnodi';
$lang['Login'] = 'Mewngofnodi';
$lang['Logout'] = 'Allgofnodi';

$lang['Forgotten_password'] = 'Dw i wedi anghofio fy nghyfrinair';

$lang['Log_me_in'] = 'Mewngofnodi fi yn awtomatig bob ymweliad';

$lang['Error_login'] = 'Dych chi wedi rhoi enw defnyddiwr neu gyfrinair anghywir';


//
// Index page --------------------- iawn
//
$lang['Index'] = 'Hafan';
$lang['No_Posts'] = 'Dim negeseuon';
$lang['No_forums'] = 'Does dim fformymau ar y bwrdd hwn';

$lang['Private_Message'] = 'Neges preifat';
$lang['Private_Messages'] = 'Negeseuon preifat';
$lang['Who_is_Online'] = 'Pwy sy arlein?';

$lang['Mark_all_forums'] = 'Marcio bob seiat \'darllenwyd\'';
$lang['Forums_marked_read'] = 'Marciwyd bob seiat \'darllenwyd\'';


//
// Viewforum --------------------  iawn
//
$lang['View_forum'] = 'Dangos Seiat';

$lang['Forum_not_exist'] = 'Dydy\'r seiat honno ddim yn bodoli';
$lang['Reached_on_error'] = 'Wps\! Dych chi wedi cyrraedd y tudalen yma gan ddamwain';

$lang['Display_topics'] = 'Dangos pynciau o\'r blaenorol';
$lang['All_Topics'] = 'Pob pwnc';

$lang['Topic_Announcement'] = '<b>Datganiad:</b>';
$lang['Topic_Sticky'] = '<b>Gludiog:</b>';
$lang['Topic_Moved'] = '<b>Wedi symud:</b>';
$lang['Topic_Poll'] = '<b>[ Pôl Piniwn ]</b>';

$lang['Mark_all_topics'] = 'Marcio pob trafodaeth \'darllenwyd\'';
$lang['Topics_marked_read'] = 'Wedi marcio pob trafodaeth \'darllenwyd\'';

$lang['Rules_post_can'] = '<b>Cewch chi</b> bostio pwnc trafod newydd yma';
$lang['Rules_post_cannot'] = '<b>Chewch chi ddim</b> postio pwnc trafod newydd yma';
$lang['Rules_reply_can'] = '<b>Cewch chi</b> ymateb i\'r pynciau yma';
$lang['Rules_reply_cannot'] = '<b>Chewch chi ddim</b> ymateb i\'r pynciau yma';
$lang['Rules_edit_can'] = '<b>Cewch chi</b> olygu\'ch negeseuon yma';
$lang['Rules_edit_cannot'] = '<b>Chewch chi ddim</b> golygu\'ch negeseuon yma';
$lang['Rules_delete_can'] = '<b>Cewch chi</b> ddileu\'ch negeseuon yma';
$lang['Rules_delete_cannot'] = '<b>Chewch chi ddim</b> dileu\'ch negeseuon yma';
$lang['Rules_vote_can'] = '<b>Cewch chi</b> bleidleisio mewn polau piniwn yma';
$lang['Rules_vote_cannot'] = '<b>Chewch chi ddim</b> pleidleisio mewn polau piniwn yma';
$lang['Rules_moderate'] = '<b>Cewch chi</b> %sgymedroli yn y seiat hon%s'; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = 'Does dim negeseuon yn y seiat hon<br />Rhowch glec ar y dolen <b>pwnc newydd</b> ar y tudalen hwn i ddechrau un';


//
// Viewtopic  -----------------  iawn
//
$lang['View_topic'] = 'Dangos pwnc';

$lang['Guest'] = 'Gwestai';
$lang['Post_subject'] = 'Pwnc y neges';
$lang['View_next_topic'] = 'Dangos pwnc nesaf';
$lang['View_previous_topic'] = 'Dangos pwnc blaenorol';
$lang['Submit_vote'] = 'Bwrw pleidlais';
$lang['View_results'] = 'Dangos canlyniadau';

$lang['No_newer_topics'] = 'Does dim pynciau diweddarach yn y seiat hon';
$lang['No_older_topics'] = 'Does dim pynciau henach yn y seiat hon';
$lang['Topic_post_not_exist'] = 'Dydy\'r pwnc neu\'r neges hwnnw ddim yn bodoli bellach';
$lang['No_posts_topic'] = 'Does dim negeseuon dan y pwnc hwn';

$lang['Display_posts'] = 'Dangos negeseuon o\'r pwnc blaenorol';
$lang['All_Posts'] = 'Pob neges';
$lang['Newest_First'] = 'Newydd yn gyntaf';
$lang['Oldest_First'] = 'Hen yn gyntaf';

$lang['Back_to_top'] = 'Nôl i\'r brig';

$lang['Read_profile'] = 'Gweld proffeil defnyddiwr'; 
$lang['Send_email'] = 'Hala ebost at ddefnyddiwr';
$lang['Visit_website'] = 'Ymweld â gwefan defnyddiwr';
$lang['ICQ_status'] = 'Statws ICQ';
$lang['Edit_delete_post'] = 'Golygu/dileu y neges hwn';
$lang['View_IP'] = 'Dangos rhif IP defnyddiwr';
$lang['Delete_post'] = 'Dileu y neges hwn';

$lang['wrote'] = ''; // [Meddai] proceeds the username and is followed by the quoted text
$lang['Quote'] = 'Dyfyniad'; // comes before bbcode quote output.
$lang['Code'] = 'Cod'; // comes before bbcode code output.

$lang['Edited_time_total'] = 'Golygwyd gan %s ar %s, golygwyd ar %d achlusur yn unig'; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = 'Golygwyd gan %s ar %s, golygwyd ar %d achlusur at ei gilydd'; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = 'Cloi\'r pwnc hwn';
$lang['Unlock_topic'] = 'Datgloi\'r pwnc hwn';
$lang['Move_topic'] = 'Symud y pwnc hwn';
$lang['Delete_topic'] = 'Dileu\'r pwnc hwn';
$lang['Split_topic'] = 'Rhannu\'r pwnc hwn';

$lang['Stop_watching_topic'] = 'Peidio gwylio\'r pwnc hwn';
$lang['Start_watching_topic'] = 'Gwylio\'r pwnc hwn am ymatebion';
$lang['No_longer_watching'] = 'Dych chi ddim yn gwylio\'r pwnc hwn bellach';
$lang['You_are_watching'] = 'Dych chi\'n gwylio\'r pwnc hwn';

$lang['Total_votes'] = 'Cyfanswm pleidleisiau';

//
// Posting/Replying (Not private messaging!) -----------  iawn
//
$lang['Message_body'] = 'Corff Neges';
$lang['Topic_review'] = 'Arolwg Trafodaeth';

$lang['No_post_mode'] = 'Rhaid nodi modd neges'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = 'Postio trafodaeth newydd';
$lang['Post_a_reply'] = 'Postio ymateb';
$lang['Post_topic_as'] = 'Postio trafodaeth fel';
$lang['Edit_Post'] = 'Golygu neges';
$lang['Options'] = 'Dewisiadau';

$lang['Post_Announcement'] = 'Datganiad';
$lang['Post_Sticky'] = 'Gludiog';
$lang['Post_Normal'] = 'Arferol';

$lang['Confirm_delete'] = 'Dych chi\'n siwr fod di am ddileu\'r neges hon?';
$lang['Confirm_delete_poll'] = 'Dych chi\'n siwr fod di am ddileu\'r pôl piniwn hwn?';

$lang['Flood_Error'] = 'Chewch chi ddim postio neges arall eto. Rhaid aros sbel bach rhwng negeseuon. Diolch am fod yn amyneddgar';
$lang['Empty_subject'] = 'Rhaid rhoi pwnc i\'r trafodaeth newydd';
$lang['Empty_message'] = 'Rhaid rhoi neges wrth bostio';
$lang['Forum_locked'] = 'Mae seiat hon dan glo, na chewch chi postio, ymateb neu olygu pynciau yma';
$lang['Topic_locked'] = 'Mae pwnc hon dan glo, na chewch chi postio, ymateb neu olygu negeseuon yma';
$lang['No_post_id'] = 'Mae rhaid dewis neges i\'w golygu';
$lang['No_topic_id'] = 'Mae rhaid dewis pwnc i\'w ymateb';
$lang['No_valid_mode'] = 'Dim ond postio, ymateb, golygu neu ddyfynu negeseuon wyt ti\' gallu\'u wneud - cerwch yn ôl a thrïal eto';
$lang['No_such_post'] = 'Dydy\'r neges honna ddim yn bodoli  - cerwch yn ôl a thrïal eto';
$lang['Edit_own_posts'] = 'Dim ond eich negeseuon eich hunan dych chi\'n gallu golygu';
$lang['Delete_own_posts'] = 'Dim ond eich negeseuon eich hunan dych chi\'n gallu dileu';
$lang['Cannot_delete_replied'] = 'Chewch chi ddim dileu neges sy wedi\'i hymateb';
$lang['Cannot_delete_poll'] = 'Chewch chi ddim dileu pôl piniwn gweithredol';
$lang['Empty_poll_title'] = 'Mae rhaid rhoi teitl i\'th bôl piniwn';
$lang['To_few_poll_options'] = 'Mae rhaid rhoi dau opsiwn o leia';
$lang['To_many_poll_options'] = 'Gormod o opsiynau, sori';
$lang['Post_has_no_poll'] = 'Does dim pôl piniwn gyda\'r pwnc hwn';
$lang['Already_voted'] = 'Dych chi wedi pleidleisio yn y pôl hwn yn barod';
$lang['No_vote_option'] = 'Rhaid dewis opsiwn wrth bleidleisio';

$lang['Add_poll'] = 'Ychwanegu Pôl Piniwn';
$lang['Add_poll_explain'] = 'Os dych chi ddim am gael pôl gyda\'r pwnc, gad y bylchau\'n wag';
$lang['Poll_question'] = 'Cwestiwn Pôl Piniwn';
$lang['Poll_option'] = 'Opsiwn';
$lang['Add_option'] = 'Ychwanegu opsiwn';
$lang['Update'] = 'Diweddaru';
$lang['Delete'] = 'Dileu';
$lang['Poll_for'] = 'Rhedeg pôl am';
$lang['Days'] = 'Dydd'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = '[ Rhowch 0 neu adael yn wag am bôl di-derfyn ]';
$lang['Delete_poll'] = 'Dileu Pôl';

$lang['Disable_HTML_post'] = 'Analluogi HTML yn y neges hon';
$lang['Disable_BBCode_post'] = 'Analluogi BBCode yn y neges hon';
$lang['Disable_Smilies_post'] = 'Analluogi gwenogluniau yn y neges hon';

$lang['HTML_is_ON'] = 'HTML: <u>ie</u>';
$lang['HTML_is_OFF'] = 'HTML: <u>nage</u>';
$lang['BBCode_is_ON'] = '%sBBCode%s: <u>ie</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = '%sBBCode%s: <u>nage</u>';
$lang['Smilies_are_ON'] = 'Gwenogluniau: <u>ie</u>';
$lang['Smilies_are_OFF'] = 'Gwenogluniau: <u>nage</u>';

$lang['Attach_signature'] = 'Atodi llofnod (newidir llofnodion ar y dudalen proffeil)';
$lang['Notify'] = 'Rhowch wybod i mi os oes ymateb';
$lang['Delete_post'] = 'Dileu\'r neges hon';

$lang['Stored'] = 'Mae\'ch neges wedi\'i hanfon yn iawn';
$lang['Deleted'] = 'Mae\'ch neges wedi\'i dileu\'n iawn';
$lang['Poll_delete'] = 'Mae\'ch pôl pibiwn wedi\'i ddileu\'n iawn';
$lang['Vote_cast'] = 'Mae\'ch pleidlais wedi\'i bwrw';

$lang['Topic_reply_notification'] = 'Hysbysebiad ymateb i bwnc';

$lang['bbcode_b_help'] = 'Wynebddu: [b]testun[/b]  (alt+b)';
$lang['bbcode_i_help'] = 'Italaidd: [i]testun[/i]  (alt+i)';
$lang['bbcode_u_help'] = 'Tanlinellu: [u]testun[/u]  (alt+u)';
$lang['bbcode_q_help'] = 'Dyfynnu: [quote]testun[/quote]  (alt+q)';
$lang['bbcode_c_help'] = 'Dangos cod: [code]cod[/code]  (alt+c)';
$lang['bbcode_l_help'] = 'Rhestr: [list]testun[/list] (alt+l)';
$lang['bbcode_o_help'] = 'Rhestr trefnwyd: [list=]testun[/list]  (alt+o)';
$lang['bbcode_p_help'] = 'Gosod llun: [img]http://url_y_llun[/img]  (alt+p)';
$lang['bbcode_w_help'] = 'Gosod URL: [url]http://url[/url] neu [url=http://url]testun URL[/url]  (alt+w)';
$lang['bbcode_a_help'] = 'Cau pob tag bbCode';
$lang['bbcode_s_help'] = 'Lliw ffont: [color=red]testun[/color]  Neu, ddefnyddio color=#FF0000';
$lang['bbcode_f_help'] = 'Maint ffont: [size=x-small]testun bach[/size]';

$lang['Emoticons'] = 'Gwenogluniau';
$lang['More_emoticons'] = 'Rhagor o Wenogluniau';

$lang['Font_color'] = 'Lliw ffont';
$lang['color_default'] = 'Arferol';
$lang['color_dark_red'] = 'Coch tywyll';
$lang['color_red'] = 'Coch';
$lang['color_orange'] = 'Oren';
$lang['color_brown'] = 'Brown';
$lang['color_yellow'] = 'Melyn';
$lang['color_green'] = 'Gwyrdd';
$lang['color_olive'] = 'Melynwyrdd';
$lang['color_cyan'] = 'Gwyrddlas';
$lang['color_blue'] = 'Glas';
$lang['color_dark_blue'] = 'Glas tywyll';
$lang['color_indigo'] = 'Indigo';
$lang['color_violet'] = 'Piws';
$lang['color_white'] = 'Gwyn';
$lang['color_black'] = 'Du';

$lang['Font_size'] = 'Maint ffont';
$lang['font_tiny'] = 'Lleiaf';
$lang['font_small'] = 'Llai';
$lang['font_normal'] = 'Arferol';
$lang['font_large'] = 'Mwy';
$lang['font_huge'] = 'Mwyaf';

$lang['Close_Tags'] = 'Cau Tagiau';
$lang['Styles_tip'] = 'Awgrym: Gall roi arddulliau cyflym ar destun wedi\'i ddethol';


//
// Private Messaging   ------------  iawn
//
$lang['Private_Messaging'] = 'Negeseuon Preifat';

$lang['Login_check_pm'] = 'Mewngofnodi i sieco\'ch negeseuon preifat';
$lang['New_pms'] = 'Mae gynnoch chi %d neges newydd'; // You have 2 new messages
$lang['New_pm'] = 'Mae gynnoch chi %d neges newydd'; // You have 1 new message
$lang['No_new_pm'] = 'Negeseuon [0]';
$lang['Unread_pms'] = 'Negeseuson [%d]';
$lang['Unread_pm'] = 'Negeseuson [%d]';
$lang['No_unread_pm'] = 'Does gynnoch chi ddim negeseuon heb eu darllen';
$lang['You_new_pm'] = 'Mae neges preifat newydd yn eich blwch derbyn';
$lang['You_new_pms'] = 'Mae negeseuon preifat newydd yn eich blwch derbyn';
$lang['You_no_new_pm'] = 'Does dim negeseuon preifat newydd yn eich blwch derbyn';

$lang['Unread_message'] = 'Neges heb ei darllen';
$lang['Read_message'] = 'Darllen neges';

$lang['Read_pm'] = 'Darllen neges';
$lang['Post_new_pm'] = 'Postio neges';
$lang['Post_reply_pm'] = 'Ymateb i neges';
$lang['Post_quote_pm'] = 'Difynnu neges';
$lang['Edit_pm'] = 'Golygu neges';

$lang['Inbox'] = 'Blwch Derbyn';
$lang['Outbox'] = 'Blwch Anfon';
$lang['Savebox'] = 'Blwch Cadw';
$lang['Sentbox'] = 'Blwch Gyrrwyd';
$lang['Flag'] = 'Fflagio';
$lang['Subject'] = 'Pwnc';
$lang['From'] = 'Oddi wrth';
$lang['To'] = 'I';
$lang['Date'] = 'Dyddiad';
$lang['Mark'] = 'Nodi';
$lang['Sent'] = 'Gyrrwyd';
$lang['Saved'] = 'Cadwyd';
$lang['Delete_marked'] = 'Dileu y rhai nodwyd';
$lang['Delete_all'] = 'Dileu Pob Un';
$lang['Save_marked'] = 'Cadw Nodwyd'; 
$lang['Save_message'] = 'Cadw Neges';
$lang['Delete_message'] = 'Dileu Neges';

$lang['Display_messages'] = 'Dangos negeseuon o\'r X diwetha:'; // Followed by number of days/weeks/months
$lang['All_Messages'] = 'Pob neges';

$lang['No_messages_folder'] = 'Does dim neges yn y ffolder hwn';

$lang['PM_disabled'] = 'Analluogir Negeseuon Preifat ar y bwrdd hwn';
$lang['Cannot_send_privmsg'] = 'Mae\'r gweinydd wedi\'ch atal rhag anfon negeseuon preifat';
$lang['No_to_user'] = 'Rhaid roi enw defnyddiwr cyn i chi anfon y neges';
$lang['No_such_user'] = 'Does dim defnyddiwr gyda\'r enw hwnna';

$lang['Disable_HTML_pm'] = 'Analluogi HTML yn y neges hon';
$lang['Disable_BBCode_pm'] = 'Analluogi BBCode yn y neges hon';
$lang['Disable_Smilies_pm'] = 'Analluogi gwenogluniau yn y neges hon';

$lang['Message_sent'] = 'Mae eich neges wedi\'i hanfon';

$lang['Click_return_inbox'] = 'Rhowch glec %syma%s i fynd yn ôl at eich blwch derbyn';
$lang['Click_return_index'] = 'Rhowch glec %syma%s i fynd yn ôl at y tudalen ffrynt';

$lang['Send_a_new_message'] = 'Anfon neges breifat newydd';
$lang['Send_a_reply'] = 'Ymateb neges breifat';
$lang['Edit_message'] = 'Golygu neges breifat';

$lang['Notification_subject'] = 'Mae neges preifat newydd wedi cyrraedd';

$lang['Find_username'] = 'Canfod enw defnyddiwr';
$lang['Find'] = 'Canfod';
$lang['No_match'] = 'Ni chanfod fatsh';

$lang['No_post_id'] = 'Ni nodwyd rhif neges';
$lang['No_such_folder'] = 'Dydy\'r ffolder hwnnw ddim yn bodoli';
$lang['No_folder'] = 'Ni nodwyd ffolder';

$lang['Mark_all'] = 'Marcio pob un';
$lang['Unmark_all'] = 'Datfarcio pob un';

$lang['Confirm_delete_pm'] = 'Dych chi\'n siwr eich bod am ddileu y neges hon?';
$lang['Confirm_delete_pms'] = 'Dych chi\'n siwr eich bod am ddileu y negeseuon hyn?';

$lang['Inbox_size'] = 'Mae\'ch blwch derbyn yn %d%% llawn'; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = 'Mae\'ch blwch gyrrwyd yn %d%% llawn'; 
$lang['Savebox_size'] = 'Mae\'ch blwch cadw yn %d%% llawn'; 

$lang['Click_view_privmsg'] = 'Rhowch glec %syma%s i ymweld â\'ch blwch derbyn';


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = 'Proffeil %s'; // %s is username 
$lang['About_user'] = 'Manylion %s'; // %s is username

$lang['Preferences'] = 'Hoffterau - newid y stwff \'ma neu gadael fe am nawr';
$lang['Items_required'] = 'Mae angen popeth gyda * oni bai dywedwyd fel arall';
$lang['Registration_info'] = 'Gwybodaeth Cofrestru - y stwff pwysig';
$lang['Profile_info'] = 'Gwybodaeth Proffeil - pethau amdanoch chi';
$lang['Profile_info_warn'] = 'Bydd y gwybodaeth hwn ar gael i ymwelwyr';
$lang['Avatar_panel'] = 'Panal rheolu rhithffurfau';
$lang['Avatar_gallery'] = 'Oriel rhithffurfau';

$lang['Website'] = 'Gwefan';
$lang['Location'] = 'Lleoliad';
$lang['Contact'] = 'Cysylltu â';
$lang['Email_address'] = 'Cyfeiriad Ebost';
$lang['Email'] = 'Ebost';
$lang['Send_private_message'] = 'Anfon Neges Breifat';
$lang['Hidden_email'] = '[ Cuddedig ]';
$lang['Search_user_posts'] = 'Ymchwilio am negeseuon gan y defnyddiwr hwn';
$lang['Interests'] = 'Diddordebau';
$lang['Occupation'] = 'Swydd'; 
$lang['Poster_rank'] = 'Gradd y defnyddiwr';

$lang['Total_posts'] = 'Cyfanswm o negeseuon';
$lang['User_post_pct_stats'] = '%.2f%% o gyfanswm'; // 1.25% of total
$lang['User_post_day_stats'] = '%.2f neges y dydd'; // 1.5 posts per day
$lang['Search_user_posts'] = 'Canfod pob neges gan %s'; // Find all posts by username

$lang['No_user_id_specified'] = 'Dydy\'r defnyddiwr hwnno ddim yn bodoli';
$lang['Wrong_Profile'] = 'Dych chi ddim yn cael newid proffeil rhywun arall. Am beth o\'ch chi\'n <i>feddwl</i>?';

$lang['Only_one_avatar'] = 'Dim ond un rhithffurf ar y tro';
$lang['File_no_data'] = 'Dydy\'r ffeil hwnna ddim yn gweithio';
$lang['No_connection_URL'] = 'Doedd hi ddim yn bosib i gysylltu â\'r URL y rhoddaist';
$lang['Incomplete_URL'] = 'Dydy\'r URL y rhoddoch ddim yn gyflawn';
$lang['Wrong_remote_avatar_format'] = 'Dydy URL yr rhithffurf o bell ddim yn gweithio';
$lang['No_send_account_inactive'] = 'Mae\'n ddrwg gennym, ond mae\'ch cyfrif wedi\'i atal. Cysylltwch â gweinyddwr y bwrdd am fwy o fanylion';

$lang['Always_smile'] = 'Galluogi gwenogluniau bob tro';
$lang['Always_html'] = 'Galluogi HTML bob tro';
$lang['Always_bbcode'] = 'Galluogi BBCode bob tro';
$lang['Always_add_sig'] = 'Atodi fy llofnod bob tro';
$lang['Always_notify'] = 'Rho wybod i mi os oes ymatebion';
$lang['Always_notify_explain'] = 'Anfon ebost atoch chi pan bydd rhywun yn ymateb mewn pwnc lle dych chi wedi cyfrannu. Gall newid y gosodiad hwn gyda phob neges';

$lang['Board_style'] = 'Arddull y Bwrdd';
$lang['Board_lang'] = 'Iaith y Bwrdd';
$lang['No_themes'] = 'Does dim themau yn y gronfa ddata';
$lang['Timezone'] = 'Ardal amser';
$lang['Date_format'] = 'Fformat dyddiad';
$lang['Date_format_explain'] = 'Defnyddir yr un cystrawen â ffwythiant <a href=\'http://www.php.net/date\' target=\'_other\'>date()</a> PHP';
$lang['Signature'] = 'Llofnod';
$lang['Signature_explain'] = 'Cewch chi ychwanegu y testun hwn at bob neges. Mae cyfyngiad o %d o nodau';
$lang['Public_view_email'] = 'Dangos fy nghyfeiriad ebost bob tro';

$lang['Current_password'] = 'Cyfrinair cyfredol';
$lang['New_password'] = 'Cyfrinair newydd';
$lang['Confirm_password'] = 'Cadarnhau cyfrinair';
$lang['Confirm_password_explain'] = 'Mae rhaid cadarnhau\'ch cyfrinair cyfredol os dych chi am ei newid neu roi cyfeiriad ebost newydd';
$lang['password_if_changed'] = 'Does dim rhaid rhoi\'ch cyfrinair oni bai\'ch bod am ei newid';
$lang['password_confirm_if_changed'] = 'Does dim rhaid cadarnhau\'ch cyfrinair oni bai\'ch bod chi wedi ei newid uchod';

$lang['Avatar'] = 'Rhithffurf';
$lang['Avatar_explain'] = 'Ymddangos delwedd bach dan eich manylion mewn negeseuon. Cewch chi ddim ond un delwedd ar y tro, ac mae rhaid iddo fod yn llai na %d picsel ar draws, %d picsel uchel a dim mwy na %dkB.'; $lang['Upload_Avatar_file'] = 'Llwytho rhithffurf o\'ch peiriant';
$lang['Upload_Avatar_URL'] = 'Llwytho rhithffurf o URL';
$lang['Upload_Avatar_URL_explain'] = 'Rhowch URL y lleoliad lle mae rhithffurf, bydd e\'n cael ei gopio i\'r safle hon.';
$lang['Pick_local_Avatar'] = 'Dewis rhithffurf o\'r oriel';
$lang['Link_remote_Avatar'] = 'Cydio wrth rhithffurf oddi wrth y safle hon';
$lang['Link_remote_Avatar_explain'] = 'Rhowch URL lleoliad yr rhithffurf dych chi eisiau ei ddefnyddio.';
$lang['Avatar_URL'] = 'URL delwedd rhithffurf';
$lang['Select_from_gallery'] = 'Dewis rhithffurf o\'r oriel';
$lang['View_avatar_gallery'] = 'Dangos oriel';

$lang['Select_avatar'] = 'Dewis rhithffurf';
$lang['Return_profile'] = 'Diddymu rhithffurf';
$lang['Select_category'] = 'Dewis categori';

$lang['Delete_Image'] = 'Dileu delwedd';
$lang['Current_Image'] = 'Delwedd cyfredol';

$lang['Notify_on_privmsg'] = 'Hysbysebu am neges breifat newydd';
$lang['Popup_on_privmsg'] = 'Ffenest sydyn ar neges breifat newydd'; 
$lang['Popup_on_privmsg_explain'] = 'Gall rhai patrymluniau agor ffenest newydd i ddweud wrthoch bod neges newydd wedi cyrraedd'; 
$lang['Hide_user'] = 'Cuddio fy statws arlein';

$lang['Profile_updated'] = 'Mae eich proffeil wedi\'i ddiweddaru';
$lang['Profile_updated_inactive'] = 'Mae\'ch proffeil wedi\'i ddiweddaru, ond dych chi wedi newid manylion anghenrheidiol felly mae\'ch cyfrif wedi ei atal. Sieco\'ch ebost am gyfarwyddiadau, neu aros am weinyddwr i ailfywiogi\'ch cyfrif';

$lang['Password_mismatch'] = 'Dydy\'r ddau gyfrinair ddim yn matsho';
$lang['Current_password_mismatch'] = 'Dydy\'r cyfrinair cyfredol y rhoddaist ddim yn matsho yr un yn ein cronfa ddata';
$lang['Password_long'] = 'Rhaid dewis cyfrinair sy\'n 32 nod neu lai';
$lang['Username_taken'] = 'Sori - defnyddir yr enw hwnna gan rywun arall';
$lang['Username_invalid'] = 'Sori - mae gan yr enw hwnna nodau annilys fel \'';
$lang['Username_disallowed'] = 'Sori - mae enw hwn wedi ei wahardd';
$lang['Email_taken'] = 'Sori - mae rhywun arall yn defnyddio\'r cyfeiriad ebost hwnna';
$lang['Email_banned'] = 'Sori - mae cyfeiriad ebost hwn wedi\'i wahardd';
$lang['Email_invalid'] = 'Sori - dydy\'r cyfeiriad hwn ddim yn ddilys';
$lang['Signature_too_long'] = 'Mae\'ch llofnod yn rhy hir';
$lang['Fields_empty'] = 'Mae rhaid i chi lenwi y blychau gorfodol';
$lang['Avatar_filetype'] = 'Rhaid defnyddio ffeil .jpg, .gif neu .png ar gyfer dy rhithffurf';
$lang['Avatar_filesize'] = 'Rhaid i maint ffeil eich rhithffurf fod yn llai na %d kB'; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = 'Rhaid i eich rithffurf fod yn llai na %d picsel o led gan %d picsel o uchder'; 

$lang['Welcome_subject'] = 'Croeso i %s'; // Welcome to my.com forums
$lang['New_account_subject'] = 'Cyfrif defnyddiwr newydd';
$lang['Account_activated_subject'] = 'Cyfrif wedi\'i fywiogi';

$lang['Account_added'] = 'Diolch am gofrestru, mae\'ch cyfrif wedi\'i greu. Gallwch fewngofnodi gyda\'ch enw defnyddiwr a chyfrinair';
$lang['Account_inactive'] = 'Mae\'ch cyfrif wedi\'i greu ac mae allwedd bywiogi wedi\'i hanfon at eich cyfeiriad ebost. Siecwch eich ebost am fanylion pellach.';
$lang['Account_inactive_admin'] = 'Mae\'ch cyfrif wedi\'i greu, ond bydd rhaid i gweinydd y safle cadarnhau\'ch aelodaeth cyn i chi ddefnyddio\'r bwrdd. Bydd y gweinydd mewn cyswllt cyn bo hir';
$lang['Account_active'] = 'Mae\'ch cyfrif wedi\'i fywiogi. Diolch am gofrestru';
$lang['Account_active_admin'] = 'Mae\'r cyfrif wedi\'i fywiogi';
$lang['Reactivate'] = 'Ailfywiogi\'ch cyfrif!';
$lang['Already_activated'] = 'Dych chi wedi ailfywiogi\'ch cyfrif yn barod';
$lang['COPPA'] = 'Mae\'ch cyfrif wedi\'i greu ond mae rhaid iddo gael ei gadarnhau. Siecwch eich ebost am fanylion.';

$lang['Registration'] = 'Termau Cytundeb Cofrestru';

$lang['Reg_agreement'] = 'Tra bydd y gweinyddion a chymedrolwyr y negesfwrdd hwn yn ceisio dileu neu olygu unrhyw negeseuon annerbyniolmor fuan ag sy\'n bosibl, nad yw\'n bosibl iddyn nhw ddarllen bob neges. Felly mae rhaid pwysleisio bod pob neges ar y negesfwrdd hwn yn mynegi barn yr unigolion a\'u postiodd, ac nid barn y gweinyddion/cymedrolwyr (ac eithrio negeseuon gan y bobl hynny eu hunain) ac nad ydyw\'r gweinyddion/cymedrolwyr yn gyfrifol am gynnwys negeseuon pobl eraill.<br /><br />Cytunwch chi i beidio postio negeseuon sy\'n ddifenwol, enllibus, sarhaus, anweddus, bygythiol, neu filain neu unrhywbeth a fyddai\'n torri unrhyw cyfreithiau perthnasol. Os dych chi\'n torri y cytundeb hwn, mae\'n bosibl y cewch chi eich gwahardd o\'r negesfwrdd yn syth ac am byth (a dwedir wrth eich darparwr gwasanaeth). Mae cyfeiriad IP bob neges yn cael ei recordio i helpu gorfodi\'r amodau hyn. Cytunwch chi bod hawl gan y wefeistr, gweinyddion a chymedrolwyr y negesfwrdd i ddileu, golygu, symud neu gau unrhyw trafodaeth unrhywbryd, lle y gwelan nhw eisiau. Fel defnyddiwr ar y negesfwrdd dych chi\'n cytuno y bydd unrhyw wybodaeth dych chi wedi\'i ddodi uchod yn cael ei gadw mewn cronfa ddata. Er na cheith y gwybodaeth hwn ei rannu gyda unrhyw drydedd person heb eich caniatâd, ni all ddal gwefeistr, gweinyddion neu gymedrolwyr yn gyfrifol am unrhyw ymdrech i hacio\'r gronfa ddata sy\'n arwain at y ddata yn cael eu peryglu.<br /><br />Defnyddia\'r gyfundrefn negesfwrdd hon \'cookies\' i gadw gwybodaeth ar eich cyfrifiadur lleol. Dydy\'r ffeiliau bach hyn ddim yn cynnwys y gwybodaeth dych chi wedi dodi uchod, dim ond i\'ch helpu cael profiad gwell wrth ddefnyddio\'r negesfwrdd. Defnyddia\'r cyfeiriad e-bost i gadarnhau eich manylion cofrestru a chyfrineiriau (ac i anfon cyfrineiriau newydd petasech chi\'n colli yr un gwreiddiol).<br /><br />Gan roi clec ar Cofrestru isod, cytunwch chi â\'r amodau hyn i gyd.';





$lang['Agree_under_13'] = 'Cytunaf â\'r termau uchod. Yr ydw i <b>dan 13</b> oed';
$lang['Agree_over_13'] = 'Cytunaf â\'r termau uchod. Yr ydw i <b>dros neu yn union 13</b> oed';
$lang['Agree_not'] = 'Na chytunaf â\'r termau hyn';

$lang['Wrong_activation'] = 'Dydy\'r allwedd bywiogi a roddoch ddim yn cyd-fynd ag unrhywbeth yn ein cronfa ddata';
$lang['Send_password'] = 'Anfon cyfrinair newydd ataf'; 
$lang['Password_updated'] = 'Wedi creu cyfrinair newydd. Mae manylion bywiogi wedi\'u hanfon atoch mewn e-bost';
$lang['No_email_match'] = 'Dydy\'r cyfeiriad e-bost a roddoch ddim yn cyd-fynd â\'r un sy gennym i\'r enw defnyddiwr hwnna';
$lang['New_password_activation'] = 'Bywiogi Cyfrinair Newydd';
$lang['Password_activated'] = 'Mae eich cyfrif wedi\'i ail-fywiogi. Cewch chi fewngofnodi gan ddefnyddio\'r cyfrinair yn yr e-bost y derbynoch.';

$lang['Send_email_msg'] = 'Anfon neges e-bost';
$lang['No_user_specified'] = 'Dim enw defnyddiwr - at bwy dych chi\'n sgwennu?';
$lang['User_prevent_email'] = 'Dydy\'r defnyddiwr hwn ddim eisiau derbyn e-byst. Ceisiwch anfon neges preifat';
$lang['User_not_exist'] = 'Dydy\'r defnyddiwr hwnna ddim yn bodoli';
$lang['CC_email'] = 'Anfon copi o\'r neges at eich hunan';
$lang['Email_message_desc'] = 'Bydd y neges hon yn cael ei hanfon fel testun plein. Peidiwch â defnyddio HTML neu BBCode. Gosodir eich cyfeiriad e-bost fel cyfeiriad dychweled.';
$lang['Flood_email_limit'] = 'Chewch chi ddim anfon e-bost arall eto. Ceisiwch eto mewn munud.';
$lang['Recipient'] = 'Derbynnydd';
$lang['Email_sent'] = 'Mae\'r e-bost wedi\'i anfon';
$lang['Send_email'] = 'Anfon e-bost';
$lang['Empty_subject_email'] = 'Rhaid rhoi pwnc i\'r e-bost';
$lang['Empty_message_email'] = 'Does dim neges! Beth dych chi am ddweud?';


//
// Memberslist - wedi'i wneud
//
$lang['Select_sort_method'] = 'Dewis modd trefnu';
$lang['Sort'] = 'Trefnu';
$lang['Sort_Top_Ten'] = 'Deg Postwr Prysuraf';
$lang['Sort_Joined'] = 'Ymunwyd';
$lang['Sort_Username'] = 'Enw';
$lang['Sort_Location'] = 'Lleoliad';
$lang['Sort_Posts'] = 'Nifer o negeseuon';
$lang['Sort_Email'] = 'Ebost';
$lang['Sort_Website'] = 'Gwefan';
$lang['Sort_Ascending'] = 'Esgynnol';
$lang['Sort_Descending'] = 'Disgynnol';
$lang['Order'] = 'Trefn';


//
// Group control panel
//
$lang['Group_Control_Panel'] = 'Panel Rheoli Cylchoedd';
$lang['Group_member_details'] = 'Manylion Aelodaeth Cylch';
$lang['Group_member_join'] = 'Ymuno â cylch';

$lang['Group_Information'] = 'Manylion y cylch';
$lang['Group_name'] = 'Enw\'r cylch';
$lang['Group_description'] = 'Disgrifiad y cylch';
$lang['Group_membership'] = 'Aelodaeth y cylch';
$lang['Group_Members'] = 'Aelodau\'r cylch';
$lang['Group_Moderator'] = 'Cymedrolwr y cylch';
$lang['Pending_members'] = 'Aelodau sy\'n aros i ymuno';

$lang['Group_type'] = 'Math o gylch';
$lang['Group_open'] = 'Cylch agored';
$lang['Group_closed'] = 'Cylch caeëdig';
$lang['Group_hidden'] = 'Cylch cuddiedig';

$lang['Current_memberships'] = 'Aelodau cyfredol';
$lang['Non_member_groups'] = 'Cylchoedd di-aelod';
$lang['Memberships_pending'] = 'Aelodau sy\'n aros i ymuno';

$lang['No_groups_exist'] = 'Does dim cylchoedd defnyddwyr hyd yn hyn';
$lang['Group_not_exist'] = 'Dydy\'r cylch defnyddwyr hwnna ddim yn bodoli';

$lang['Join_group'] = 'Ymuno â chylch';
$lang['No_group_members'] = 'Does dim aelodau yn y cylch hwn';
$lang['Group_hidden_members'] = 'Mae\'r cylch hwn yn guddiedig, na elli di weld ei restr aelodaeth';
$lang['No_pending_group_members'] = 'Does neb yn aros i ymuno â\'r cylch hwn';
$lang['Group_joined'] = 'Mae dy gais wedi\'i anfon at gymedrolwr y cylch<br />Cewch chi neges unwaith mae e/hi wedi rhoi ei sêl bendith arno';
$lang['Group_request'] = 'Dyma gais i ymuno â dy gylch defnyddwyr';
$lang['Group_approved'] = 'Mae\'ch cais wedi\'i dderbyn';
$lang['Group_added'] = 'Dych chi wedi ymuno â\'r cylch hwn'; 
$lang['Already_member_group'] = 'Dych chi\'n aelod o\'r cylch hwn yn barod';
$lang['User_is_member_group'] = 'Mae\'r defnyddiwr yn aelod o\'r cylch hwn yn barod';
$lang['Group_type_updated'] = 'Wedi diweddaru math cylch';

$lang['Could_not_add_user'] = 'Dydy\'r defnyddiwr a ddewisaist ddim yn bodoli';
$lang['Could_not_anon_user'] = 'Na Chewch chi ddebyn aoldau di-enw';

$lang['Confirm_unsub'] = 'Dych chi\'n siwr eich bod chi am adael y cylch hwn?';
$lang['Confirm_unsub_pending'] = 'Dych chi ddim wedi cael eich derbyn i\'r cylch hwn eto, dych chi\'n siwr eich bod chi am ganslo\'ch cais?';

$lang['Unsub_success'] = 'Dych chi wedi gadael y cylch hwn.';

$lang['Approve_selected'] = 'Derbyn y rhai dewisedig';
$lang['Deny_selected'] = 'Gwrthod y rhai dewisedig';
$lang['Not_logged_in'] = 'Rhaid i chi fewngofnodi i ymuno â chylch defnyddwyr.';
$lang['Remove_selected'] = 'Cael gwared â\'r rhai dewisedig';
$lang['Add_member'] = 'Ychwanegu Aelod';
$lang['Not_group_moderator'] = 'Nid chi yw cymedrolwr y cylch, felly dych chi ddim yn cael wneud hynny.';

$lang['Login_to_join'] = 'Mewngofnodi i ymuno neu reoli aelodaethau cylchoedd';
$lang['This_open_group'] = 'Mae cylch hwn yn agored i bawb, rho glec i ofyn am aelodaeth';
$lang['This_closed_group'] = 'Mae cylch hwn yn caeedig, dydy neb arall yn cael ymuno';
$lang['This_hidden_group'] = 'Mae hwn yn gylch cuddedig, dydy ychwanegu aelodau awtomatig ddim yn bosibl';
$lang['Member_this_group'] = 'Dych chi\'n aelod y cylch hwn';
$lang['Pending_this_group'] = 'Dydy\'ch aelodaeth y cylch hwn ddim wedi ei dderbyn eto';
$lang['Are_group_moderator'] = 'Chi yw cymedrolwr y cylch hwn';
$lang['None'] = 'Neb';

$lang['Subscribe'] = 'Ymuno';
$lang['Unsubscribe'] = 'Gadael';
$lang['View_Information'] = 'gweld Gwybodaeth';


//
// Search
//
$lang['Search_query'] = 'Ymchwilio y negesfwrdd';
$lang['Search_options'] = 'Dewisiadau Ymchwilio';

$lang['Search_keywords'] = 'Ymchwilio am Allweddeiriau';
$lang['Search_keywords_explain'] = 'Ceir defnyddio <u>AND</u>, <u>OR</u> a <u>NOT</u> (e.e. john AND alun; merched OR genethod; cwrw NOT lager). Defnyddio * fel \'cerdyn wyllt\' (handi gyda treigladu - bydd \'*ymru\' yn ffeindio Cymru, Gymru a Nghymru';
$lang['Search_author'] = 'Chwilio am Awdur';
$lang['Search_author_explain'] = 'Defnyddio * fel cerdyn wyllt e.e. daf*';

$lang['Search_for_any'] = 'Ymchwilio am unrhyw gair neu ddefnyddio\'r ymholiad fel y mae';
$lang['Search_for_all'] = 'Ymchwilio am bob gair';
$lang['Search_title_msg'] = 'Ymchwilio teitlau sgyrsiau a thestun negeseuon';
$lang['Search_msg_only'] = 'Ymchwilio testun negeseuon yn unig';

$lang['Return_first'] = 'Dangos y'; // followed by xxx characters in a select box
$lang['characters_posts'] = 'llythrennau cyntaf o\'r neges';

$lang['Search_previous'] = 'Ymchwilio yn ôl'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = 'Trefnu gan';
$lang['Sort_Time'] = 'Amser y Neges';
$lang['Sort_Post_Subject'] = 'Pwnc y Neges';
$lang['Sort_Topic_Title'] = 'Teitl y Sgwrs';
$lang['Sort_Author'] = 'Awdur';
$lang['Sort_Forum'] = 'Seiat';

$lang['Display_results'] = 'Dangos canlyniadau fel';
$lang['All_available'] = 'Popeth sydd ar gael';
$lang['No_searchable_forums'] = 'Does dim hawl gennych i ymchwilio seiadau ar y safle hon';

$lang['No_search_match'] = 'Does dim canlyniadau';
$lang['Found_search_match'] = 'Darganfwyd %d canlyniad'; // eg. Search found 1 match
$lang['Found_search_matches'] = 'Darganfwyd %d o ganlyniadau'; // eg. Search found 24 matches

$lang['Close_window'] = 'Cau\'r ffenestr';


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = 'Gall %s yn unig bostio datganiadau yn y seiat hwn';
$lang['Sorry_auth_sticky'] = 'Gall %s yn unig bostio negeseuon gludiog yn y seiat hwn'; 
$lang['Sorry_auth_read'] = 'Gall %s yn unig ddarllen negeseuon yn y seiat hwn'; 
$lang['Sorry_auth_post'] = 'Gall %s yn unig bostio sgyrsiau newydd yn y seiat hwn'; 
$lang['Sorry_auth_reply'] = 'Gall %s yn unig ymateb i negeseuon yn y seiat hwn'; 
$lang['Sorry_auth_edit'] = 'Gall %s yn unig olygu negeseuon yn y seiat hwn'; 
$lang['Sorry_auth_delete'] = 'Gall %s yn unig ddileu negeseuon yn y seiat hwn'; 
$lang['Sorry_auth_vote'] = 'Gall %s yn unig bleidleisio yn y seiat hwn'; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>defnyddwyr di-enw</b>';
$lang['Auth_Registered_Users'] = '<b>defnyddwyr cofrestredig</b>';
$lang['Auth_Users_granted_access'] = '<b>defnyddwyr gyda mynediad arbennig</b>';
$lang['Auth_Moderators'] = '<b>cymedrolwyr</b>';
$lang['Auth_Administrators'] = '<b>gweinyddion</b>';

$lang['Not_Moderator'] = 'Dych chi ddim yn gymedrolwr yn y seiat hwn';
$lang['Not_Authorised'] = 'Heb ganiatâd';

$lang['You_been_banned'] = 'Dych chi wedi cael eich gwahardd o\'r seiat<br />Cysylltwch â\'r gwefeistr neu weinydd y bwrdd am fwy o wybodaeth';


//
// Viewonline
//
$lang['Reg_users_zero_online'] = 'Does dim defnyddwyr cofrestredig a '; // There ae 5 Registered and
$lang['Reg_users_online'] = 'Mae %d defnyddiwr cofrestredig a '; // There ae 5 Registered and
$lang['Reg_user_online'] = 'Mae %d defnyddiwr cofrestredig a '; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = 'does dim defnyddiwr cuddiedig ar-lein'; // 6 Hidden users online
$lang['Hidden_users_online'] = '%d defnyddiwr cuddiedig ar-lein'; // 6 Hidden users online
$lang['Hidden_user_online'] = '%d defnyddiwr cuddiedig ar-lein'; // 6 Hidden users online
$lang['Guest_users_online'] = 'Mae %d gwesteion ar-lein'; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = 'Does dim gwesteion ar-lein'; // There are 10 Guest users online
$lang['Guest_user_online'] = 'Mae %d gwestai ar-lein'; // There is 1 Guest user online
$lang['No_users_browsing'] = 'Does neb yn defnyddio\'r wefan ar hyn o bryd';

$lang['Online_explain'] = 'Defnyddwyr yma yn y pum munud diwetha';

$lang['Forum_Location'] = 'Lleoliad';
$lang['Last_updated'] = 'Diweddiad diwetharaf';

$lang['Forum_index'] = 'Hafan Seiat';
$lang['Logging_on'] = 'Mewngofnodi';
$lang['Posting_message'] = 'Postio neges';
$lang['Searching_forums'] = 'Ymchwilio';
$lang['Viewing_profile'] = 'Edrych ar broffeil';
$lang['Viewing_online'] = 'Edrych ar aelodau ar-lein';
$lang['Viewing_member_list'] = 'Edrych ar rhestr aelodau';
$lang['Viewing_priv_msgs'] = 'Darllen negeseuon preifat';
$lang['Viewing_FAQ'] = 'Darllen FAQ';


//
// Moderator Control Panel
//
$lang['Mod_CP'] = 'Panel Rheoli Cymedrolwr';
$lang['Mod_CP_explain'] = 'Cewch chi ddefnyddio\'r ffurflen isod i wneud gweithgareddau crynswth ar y fforwm. Cewch chi gloi, datgloi, symud neu ddilau unrhyw nifer o drafodaethau.';

$lang['Select'] = 'Dewis';
$lang['Delete'] = 'Dileu';
$lang['Move'] = 'Symud';
$lang['Lock'] = 'Cloi';
$lang['Unlock'] = 'Datgloi';

$lang['Topics_Removed'] = 'Mae\'r trafodaethau dewisedig wedi\'u dileu o\'r gronfa ddata.';
$lang['Topics_Locked'] = 'Mae\'r trafodaethau dewisedig wedi\'u cloi';
$lang['Topics_Moved'] = 'Mae\'r trafodaethau dewisedig wedi\'u symud';
$lang['Topics_Unlocked'] = 'Mae\'r trafodaethau dewisedig wedi\'u datgloi';
$lang['No_Topics_Moved'] = 'Ni symudwyd trafodaethau';

$lang['Confirm_delete_topic'] = 'Dych chi\'n siwr eich bod chi am ddileu\'r trafodaeth(au) dewisedig?';
$lang['Confirm_lock_topic'] = 'Dych chi\'n siwr eich bod chi am gloi\'r trafodaeth(au) dewisedig?';
$lang['Confirm_unlock_topic'] = 'Dych chi\'n siwr eich bod chi am ddatgloi\'r trafodaeth(au) dewisedig?';
$lang['Confirm_move_topic'] = 'Dych chi\'n siwr eich bod chi am symud y trafodaeth(au) dewisedig?';

$lang['Move_to_forum'] = 'Symud i seiat';
$lang['Leave_shadow_topic'] = 'Gadael cysgod o\'r trafodaeth yn yr hen seiat.';

$lang['Split_Topic'] = 'Panel Rheoli Trafodaeth Holltiedig';
$lang['Split_Topic_explain'] = 'Cewch chi ddefnyddio\'r ffurflen isod i hollti trafodaeth mewn dau, naill ai gan ddewis y negeseuon fel unigolion neu gan hollti ar ôl neges penodol';
$lang['Split_title'] = 'Teitl trafodaeth newydd';
$lang['Split_forum'] = 'Seiat i\'r trafodaeth newydd';
$lang['Split_posts'] = 'Hollti negeseuon dewisedig';
$lang['Split_after'] = 'Holti o\'r neges dewisedig';
$lang['Topic_split'] = 'Hollwyd y trafodaeth dewisedig yn llwyddianus';

$lang['Too_many_error'] = 'Dych chi wedi dewis gormod o negeseuon. Cewch hollti trafodaeth ar ôl un neges yn unig!';

$lang['None_selected'] = 'Dych chi ddim wedi dewis unrhyw trafodaeth. Cerwch yn ôl a dewis o leiaf un.';
$lang['New_forum'] = 'Seiat newydd';

$lang['This_posts_IP'] = 'IP i\'r neges hon';
$lang['Other_IP_this_user'] = 'Cyfeiriadau IP eraill mae defnyddiwr wedi\'u defnyddio';
$lang['Users_this_IP'] = 'Defnyddwyr postio o\'r IP hwn';
$lang['IP_info'] = 'Gwybodaeth IP';
$lang['Lookup_IP'] = 'Canfod IP';


//
// Timezones ... for display on each page
//
$lang['All_times'] = 'Amser lleol = %s'; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = 'GMT - 12 Awr';
$lang['-11'] = 'GMT - 11 Awr';
$lang['-10'] = 'GMT - 10 Awr';
$lang['-9'] = 'GMT - 9 Awr';
$lang['-8'] = 'GMT - 8 Awr';
$lang['-7'] = 'GMT - 7 Awr';
$lang['-6'] = 'GMT - 6 Awr';
$lang['-5'] = 'GMT - 5 Awr';
$lang['-4'] = 'GMT - 4 Awr';
$lang['-3.5'] = 'GMT - 3.5 Awr';
$lang['-3'] = 'GMT - 3 Awr';
$lang['-2'] = 'GMT - 2 Awr';
$lang['-1'] = 'GMT - 1 Awr';
$lang['0'] = 'GMT';
$lang['1'] = 'GMT + 1 Awr';
$lang['2'] = 'GMT + 2 Awr';
$lang['3'] = 'GMT + 3 Awr';
$lang['3.5'] = 'GMT + 3.5 Awr';
$lang['4'] = 'GMT + 4 Awr';
$lang['4.5'] = 'GMT + 4.5 Awr';
$lang['5'] = 'GMT + 5 Awr';
$lang['5.5'] = 'GMT + 5.5 Awr';
$lang['6'] = 'GMT + 6 Awr';
$lang['6.5'] = 'GMT + 6.5 Awr';
$lang['7'] = 'GMT + 7 Awr';
$lang['8'] = 'GMT + 8 Awr';
$lang['9'] = 'GMT + 9 Awr';
$lang['9.5'] = 'GMT + 9.5 Awr';
$lang['10'] = 'GMT + 10 Awr';
$lang['11'] = 'GMT + 11 Awr';
$lang['12'] = 'GMT + 12 Awr';

// These are displayed in the timezone select box
$lang['tz']['-12'] = 'GMT - 12 Awr';
$lang['tz']['-11'] = 'GMT - 11 Awr';
$lang['tz']['-10'] = 'GMT - 10 Awr';
$lang['tz']['-9'] = 'GMT - 9 Awr';
$lang['tz']['-8'] = 'GMT - 8 Awr';
$lang['tz']['-7'] = 'GMT - 7 Awr';
$lang['tz']['-6'] = 'GMT - 6 Awr';
$lang['tz']['-5'] = 'GMT - 5 Awr';
$lang['tz']['-4'] = 'GMT - 4 Awr';
$lang['tz']['-3.5'] = 'GMT - 3.5 Awr';
$lang['tz']['-3'] = 'GMT - 3 Awr';
$lang['tz']['-2'] = 'GMT - 2 Awr';
$lang['tz']['-1'] = 'GMT - 1 Awr';
$lang['tz']['0'] = 'GMT';
$lang['tz']['1'] = 'GMT + 1 Awr';
$lang['tz']['2'] = 'GMT + 2 Awr';
$lang['tz']['3'] = 'GMT + 3 Awr';
$lang['tz']['3.5'] = 'GMT + 3.5 Awr';
$lang['tz']['4'] = 'GMT + 4 Awr';
$lang['tz']['4.5'] = 'GMT + 4.5 Awr';
$lang['tz']['5'] = 'GMT + 5 Awr';
$lang['tz']['5.5'] = 'GMT + 5.5 Awr';
$lang['tz']['6'] = 'GMT + 6 Awr';
$lang['tz']['6.5'] = 'GMT + 6.5 Awr';
$lang['tz']['7'] = 'GMT + 7 Awr';
$lang['tz']['8'] = 'GMT + 8 Awr';
$lang['tz']['9'] = 'GMT + 9 Awr';
$lang['tz']['9.5'] = 'GMT + 9.5 Awr';
$lang['tz']['10'] = 'GMT + 10 Awr';
$lang['tz']['11'] = 'GMT + 11 Awr';
$lang['tz']['12'] = 'GMT + 12 Awr';

$lang['datetime']['Sunday'] = 'Dydd Sul';
$lang['datetime']['Monday'] = 'Dydd Llun';
$lang['datetime']['Tuesday'] = 'Dydd Mawrth';
$lang['datetime']['Wednesday'] = 'Dydd Mercher';
$lang['datetime']['Thursday'] = 'Dydd Iau';
$lang['datetime']['Friday'] = 'Dydd Gwener';
$lang['datetime']['Saturday'] = 'Dydd Sadwrn';
$lang['datetime']['Sun'] = 'Sul';
$lang['datetime']['Mon'] = 'Llun';
$lang['datetime']['Tue'] = 'Maw';
$lang['datetime']['Wed'] = 'Mer';
$lang['datetime']['Thu'] = 'Iau';
$lang['datetime']['Fri'] = 'Gwe';
$lang['datetime']['Sat'] = 'Sad';
$lang['datetime']['January'] = 'Ionawr';
$lang['datetime']['February'] = 'Chwefror';
$lang['datetime']['March'] = 'Mawrth';
$lang['datetime']['April'] = 'Ebrill';
$lang['datetime']['May'] = 'Mai';
$lang['datetime']['June'] = 'Mehefin';
$lang['datetime']['July'] = 'Gorffennaf';
$lang['datetime']['August'] = 'Awst';
$lang['datetime']['September'] = 'Medi';
$lang['datetime']['October'] = 'Hydref';
$lang['datetime']['November'] = 'Tachwedd';
$lang['datetime']['December'] = 'Rhagfyr';
$lang['datetime']['Jan'] = 'Ion';
$lang['datetime']['Feb'] = 'Chw';
$lang['datetime']['Mar'] = 'Maw';
$lang['datetime']['Apr'] = 'Ebr';
$lang['datetime']['May'] = 'Mai';
$lang['datetime']['Jun'] = 'Meh';
$lang['datetime']['Jul'] = 'Gor';
$lang['datetime']['Aug'] = 'Awst';
$lang['datetime']['Sep'] = 'Medi';
$lang['datetime']['Oct'] = 'Hyd';
$lang['datetime']['Nov'] = 'Tach';
$lang['datetime']['Dec'] = 'Rhag';

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = 'Gwybodaeth';
$lang['Critical_Information'] = 'Gwybodaeth Hanfodol';

$lang['General_Error'] = 'Gwall Cyffredinol';
$lang['Critical_Error'] = 'Gwall Difrifol';
$lang['An_error_occured'] = 'Digwyddodd gwall';
$lang['A_critical_error'] = 'Digwyddodd gwall difrifol';

//
// That's all Folks! - Dyna i gyd, gyfeillion!
// -------------------------------------------------

?>