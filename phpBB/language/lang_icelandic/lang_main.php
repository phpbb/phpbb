<?php
/***************************************************************************
 *
 *	language/lang_icelandic/lang_main.php   [icelandic]
 *	------------------------------------------------------------------------
 *
 *	Created     Thu, 29 Aug 2002 18:49:56 +0200
 *
 *	Copyright   (c) 2002 The phpBB Group
 *	Email       support@phpbb.com
 *
 *	Created by  C.O.L.T. v1.4.4 - The Cool Online Language Translation Tool
 *	            Fast like a bullet and available online!
 *	            (c) 2002 Matthias C. Hormann <matthias@hormann-online.net>
 *
 *	Visit       http://www.phpbb.kicks-ass.net/ to find out more!
 *
 ***************************************************************************/

/***************************************************************************
 *
 *	This program is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 *
 ***************************************************************************/

//
// Add your details here if wanted, e.g. Name, username, email address, website
// www.oreind.is/spjall - Baldur Þór Sveinsson: baldur@oreind.is
// Þýðing útgáfa 0.4 - 11 sept. 2002.

//
// The format of this file is ---> $lang['message'] = 'text';
//
// You should also try to set a locale and a character encoding (plus direction). The encoding and direction
// will be sent to the template. The locale may or may not work, it's dependent on OS support and the syntax
// varies ... give it your best guess!
//

// setlocale(LC_ALL, 'English');

$lang['ENCODING'] = 'iso-8859-1';	// put the correct character set here!
$lang['DIRECTION'] = 'ltr';	// {ltr|rtl} -- DO NOT TRANSLATE!
$lang['LEFT'] = 'left';	// DO NOT TRANSLATE!
$lang['RIGHT'] = 'right';	// DO NOT TRANSLATE!
$lang['DATE_FORMAT'] = 'd M Y'; // This should be changed to the default date format for your language, php date() format

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.
$lang['TRANSLATION_INFO'] = 'Þýðing gerð af <a href="mailto:baldur@oreind.is">Baldur Þór Sveinsson</a> © 2002';

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = 'Umræður';
$lang['Category'] = 'Umræðuefni';
$lang['Topic'] = 'Spjallþráður';
$lang['Topics'] = 'Spjallþræðir';
$lang['Replies'] = 'Svör';
$lang['Views'] = 'Skoðað';
$lang['Post'] = 'Innlegg';
$lang['Posts'] = 'Innlegg';
$lang['Posted'] = 'Innlegg';
$lang['Username'] = 'Notendanafn';
$lang['Password'] = 'Aðgangsorð';
$lang['Email'] = 'E-mail/netfang';
$lang['Poster'] = 'Poster';
$lang['Author'] = 'Höfundur';
$lang['Time'] = 'Tími';
$lang['Hours'] = 'Klukkustundir';
$lang['Message'] = 'Skilaboð';

$lang['1_Day'] = '1 Dag';
$lang['7_Days'] = '7 Daga';
$lang['2_Weeks'] = '2 Vikur';
$lang['1_Month'] = '1 Mánuð';
$lang['3_Months'] = '3 Mánuði';
$lang['6_Months'] = '6 Mánuði';
$lang['1_Year'] = '1 Ár';

$lang['Go'] = 'Fara';
$lang['Jump_to'] = 'Fara til';
$lang['Submit'] = 'Senda';
$lang['Reset'] = 'Hreinsa';
$lang['Cancel'] = 'Hætta við';
$lang['Preview'] = 'Skoða';
$lang['Confirm'] = 'Staðfesta';
$lang['Spellcheck'] = 'Stafsetning';
$lang['Yes'] = 'Já';
$lang['No'] = 'Nei';
$lang['Enabled'] = 'Virkjað';
$lang['Disabled'] = 'Óvirkt';
$lang['Error'] = 'Villa';

$lang['Next'] = 'Næsta';
$lang['Previous'] = 'Fyrra';
$lang['Goto_page'] = 'Fara á blaðsíðu';
$lang['Joined'] = 'Skráður þann';
$lang['IP_Address'] = 'IP Address';

$lang['Select_forum'] = 'Veldu umræður';
$lang['View_latest_post'] = 'Skoða síðustu innlegg';
$lang['View_newest_post'] = 'Skoða nýjustu innlegg';
$lang['Page_of'] = 'Blaðsíða <b>%d</b> af <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'ICQ Númer';
$lang['AIM'] = 'AIM Address';
$lang['MSNM'] = 'MSN Skilaboð';
$lang['YIM'] = 'Yahoo Skilaboð';

$lang['Forum_Index'] = '%s umræðu hópur';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = 'Senda inn nýjan spjallþráð';
$lang['Reply_to_topic'] = 'Senda svar á spjallþráð';
$lang['Reply_with_quote'] = 'Svara með tilvísun';

$lang['Click_return_topic'] = 'Ýtið %shér%s til að fara til baka á umræður'; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = 'Ýtið %shér%s til að reyna aftur';
$lang['Click_return_forum'] = 'Ýtið %shér%s til að fara aftur á umræður';
$lang['Click_view_message'] = 'Ýtið %shér%s til að sjá þitt innlegg';
$lang['Click_return_modcp'] = 'Ýtið %shér%s til að fara aftur til að umræðu stjóra stjórnborð';
$lang['Click_return_group'] = 'Ýtið %shér%s til að fara á upplýsingar um hóp';

$lang['Admin_panel'] = 'Fara á stjórnborð fyrir umræðuborðsstjóra';

$lang['Board_disable'] = 'Því miður er umræðuborðið óvirkt sem stendur, reyndu seinna';

//
// Global Header strings
//
$lang['Registered_users'] = 'Innskráðir notendur:';
$lang['Browsing_forum'] = 'Virkir notendur:';
$lang['Online_users_zero_total'] = 'Það eru samtals <b>0</b> notendur tengdir núna :: ';
$lang['Online_users_total'] = 'Það eru samtals <b>%d</b> notendur tengdir núna :: ';
$lang['Online_user_total'] = 'Það er <b>%d</b> notandi tengdur núna :: ';
$lang['Reg_users_zero_total'] = '0 <>Skráðir, ';
$lang['Reg_users_total'] = '%d Skráðir, ';
$lang['Reg_user_total'] = '%d Skráðir, ';
$lang['Hidden_users_zero_total'] = '0 Faldir og ';
$lang['Hidden_user_total'] = '%d Faldir og ';
$lang['Hidden_users_total'] = '%d Faldir og ';
$lang['Guest_users_zero_total'] = '0 Gestir';
$lang['Guest_users_total'] = '%d Gestir';
$lang['Guest_user_total'] = '%d Gestur';
$lang['Record_online_users'] = 'Þegar flestir notendur voru þá voru <b>%s</b> tengdir þann %s'; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = '%sUmsjónarmaður%s';
$lang['Mod_online_color'] = '%sStjórnandi%s';

$lang['You_last_visit'] = 'Þú heimsóttir okkur síðast þann %s'; // %s replaced by date/time
$lang['Current_time'] = 'Það er núna %s'; // %s replaced by time

$lang['Search_new'] = 'Skoða innlegg síðan síðast';
$lang['Search_your_posts'] = 'Skoða þín innlegg';
$lang['Search_unanswered'] = 'Skoða ósvöruðum innleggjum';

$lang['Register'] = 'Nýskráning';
$lang['Profile'] = 'Þín uppsetning';
$lang['Edit_profile'] = 'Breyta þinni uppsetningu';
$lang['Search'] = 'Leita';
$lang['Memberlist'] = 'Listi yfir meðlimi';
$lang['FAQ'] = 'Algengar spurningar (FAQ)';
$lang['BBCode_guide'] = 'BB kóða leiðbeiningar';
$lang['Usergroups'] = 'Notendahópar';
$lang['Last_Post'] = 'Síðasta innlegg';
$lang['Moderator'] = 'Stjórnandi';
$lang['Moderators'] = 'Stjórnendur';

//
// Stats block text
//
$lang['Posted_articles_zero_total'] = 'Notendur okkar hafa sent inn samtals <b>0</b> innlegg'; // Number of posts
$lang['Posted_articles_total'] = 'Notendur okkar hafa sent inn samtals <b>%d</b> innlegg'; // Number of posts
$lang['Posted_article_total'] = 'Notendur okkar hafa sent inn samtals <b>%d</b> innlegg'; // Number of posts
$lang['Registered_users_zero_total'] = 'Við höfum <b>0</b> skráða notendur'; // # registered users
$lang['Registered_users_total'] = 'Við höfum <b>%d</b> skráða notendur'; // # registered users
$lang['Registered_user_total'] = 'Við höfum <b>%d</b> skráða notendur'; // # registered users
$lang['Newest_user'] = 'Okkar nýjasti skráði notandi er <b>%s%s%s</b>'; // a href, username, /a 

$lang['No_new_posts_last_visit'] = 'Engin ný innlegg eru síðan þú kíktir inn síðast';
$lang['No_new_posts'] = 'Engin ný innlegg';
$lang['New_posts'] = 'Ný innlegg';
$lang['New_post'] = 'Ný innlegg';
$lang['No_new_posts_hot'] = 'Engin ný innlegg [ Vinsælast ]';
$lang['New_posts_hot'] = 'Ný innlegg [ Vinsælast ]';
$lang['No_new_posts_locked'] = 'Engin ný innlegg [ Lokað ]';
$lang['New_posts_locked'] = 'Ný innlegg [ Lokað ]';
$lang['Forum_is_locked'] = 'Umræðurnar eru lokaðar';

//
// Login
//
$lang['Enter_password'] = 'Sláðu inn notendanafn og aðgangsorð til að skrá þig inn';
$lang['Login'] = 'Innskráning';
$lang['Logout'] = 'Aftengjast';

$lang['Forgotten_password'] = 'Ég hef gleymt aðgangsorði mínu';

$lang['Log_me_in'] = 'Skráðu mig inn sjálfvirkt í hvert skipti sem ég kem inn';

$lang['Error_login'] = 'Þú hefur slegið inn rangt eða óvirkt notendanafn eða aðgangsorð';

//
// Index page
//
$lang['Index'] = 'Forsíða';
$lang['No_Posts'] = 'Engin innlegg';
$lang['No_forums'] = 'Þetta umræðuborð hefur engar spjallþræði';

$lang['Private_Message'] = 'Einka skilaboð';
$lang['Private_Messages'] = 'Einka skilaboð';
$lang['Who_is_Online'] = 'Hverjir er tengdir';

$lang['Mark_all_forums'] = 'Merkja allar umræður lesnar';
$lang['Forums_marked_read'] = 'Allar umræður hafa verið merktar lesnar';

//
// Viewforum
//
$lang['View_forum'] = 'Skoða umræður';

$lang['Forum_not_exist'] = 'Umræður sem þú valdir er ekki til';
$lang['Reached_on_error'] = 'Þú hefur komið hingað vegna villu';

$lang['Display_topics'] = 'Sýna spjallþræði frá síðustu/síðasta';
$lang['All_Topics'] = 'Allir spjallþræðir';

$lang['Topic_Announcement'] = '<b>Tilkynning:</b>';
$lang['Topic_Sticky'] = '<b>Líma:</b>';
$lang['Topic_Moved'] = '<b>Fært:</b>';
$lang['Topic_Poll'] = '<b>[ Könnun ]</b>';

$lang['Mark_all_topics'] = 'Merkja alla spjallþræði lesna';
$lang['Topics_marked_read'] = 'Umræður á þessum spjallþræði hafa allar verið merktar lesnar';

$lang['Rules_post_can'] = 'Þú <b>getur</b> sent inn nýja spjallþræði á þessar umræður';
$lang['Rules_post_cannot'] = 'Þú <b>getur ekki</b> sent inn nýja spjallþræði á þessar umræður';
$lang['Rules_reply_can'] = 'Þú <b>getur</b> svarað spjallþráðum á þessum umræðum';
$lang['Rules_reply_cannot'] = 'Þú <b>getur ekki</b> svarað spjallþráðum á þessum umræðum';
$lang['Rules_edit_can'] = 'Þú <b>getur</b> breytt innleggi þínu á þessum umræðum';
$lang['Rules_edit_cannot'] = 'Þú <b>getur ekki</b> breytt innleggi þínu á þessum umræðum';
$lang['Rules_delete_can'] = 'Þú <b>getur</b> eytt þínum innleggjum í þessum umræðum';
$lang['Rules_delete_cannot'] = 'Þú <b>getur ekki</b> eytt innleggjum þínum á þessum umræðum';
$lang['Rules_vote_can'] = 'Þú <b>getur</b> tekið þátt í kosningum á þessum umræðum';
$lang['Rules_vote_cannot'] = 'Þú <b>getur ekki</b> tekið þátt í kosningum á þessum umræðum';
$lang['Rules_moderate'] = 'Þú <b>getur</b> %sstjórnað þessum umræðum%s'; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = 'Það eru engin innlegg á þessar umræður<br />Ýttu á <b>Nýjan spjallþráð/newtopic</b> hnapp á þessari síðu til að senda inn';

//
// Viewtopic
//
$lang['View_topic'] = 'Sjá spjallþráð';

$lang['Guest'] = 'Gestur';
$lang['Post_subject'] = 'Efni innleggs';
$lang['View_next_topic'] = 'Sjá næstu spjallþræði';
$lang['View_previous_topic'] = 'Sjá síðustu spjallþræði';
$lang['Submit_vote'] = 'Senda inn kosningu';
$lang['View_results'] = 'Sjá úrslit kosninga';

$lang['No_newer_topics'] = 'Það eru engir nýjir spjallþræðir á þessum umræðum';
$lang['No_older_topics'] = 'Það eru engir eldri spjallþræðir á þessum umræðum';
$lang['Topic_post_not_exist'] = 'Spjallþráður eða innlegg sem þú leitar að er ekki til';
$lang['No_posts_topic'] = 'Það eru engin innlegg á þessum spjallþráð';

$lang['Display_posts'] = 'Sýna innlegg frá síðasta';
$lang['All_Posts'] = 'Öll innlegg';
$lang['Newest_First'] = 'Nýjasta fyrst';
$lang['Oldest_First'] = 'Elsta fyrst';

$lang['Back_to_top'] = 'Til baka efst á síðu';

$lang['Read_profile'] = 'Sjá uppsetningu notanda'; 
$lang['Send_email'] = 'Senda póst';
$lang['Visit_website'] = 'Heimsækja heimasíðu sendanda';
$lang['ICQ_status'] = 'ICQ Status';
$lang['Edit_delete_post'] = 'Breyta/Eyða þessu innleggi';
$lang['View_IP'] = 'Sjá IP-tölu sendanda';
$lang['Delete_post'] = 'Eyða þessu innleggi';

$lang['wrote'] = 'skrifaði'; // proceeds the username and is followed by the quoted text
$lang['Quote'] = 'Tilvitnun'; // comes before bbcode quote output.
$lang['Code'] = 'Kóði'; // comes before bbcode code output.

$lang['Edited_time_total'] = 'Síðast breytt af %s þann %s, breytt %d sinni samtals'; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = 'Síðast breytt af %s þann %s, breytt %d sinnum samtals'; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = 'Loka þessum spjallþræði';
$lang['Unlock_topic'] = 'Opna þennan spjallþráð';
$lang['Move_topic'] = 'Færa þennan spjallþráð';
$lang['Delete_topic'] = 'Eyða þessum spjallþræði';
$lang['Split_topic'] = 'Skifta þessum spjallþræði';

$lang['Stop_watching_topic'] = 'Hætta að fylgjast með þessum spjallþræði';
$lang['Start_watching_topic'] = 'Fylgjast með hvort komi svör við þessum spjallþræði';
$lang['No_longer_watching'] = 'Þú ert ekki lengur að fylgjast með þessu spjallþræði';
$lang['You_are_watching'] = 'Þú ert að núna að fylgjast með þessum spjallþræði';

$lang['Total_votes'] = 'Samtals atkvæði';

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = 'Meginatriði innleggs';
$lang['Topic_review'] = 'Spjallþráða yfirlit';

$lang['No_post_mode'] = 'Engin aðferð valin við innsendingu'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = 'Senda inn nýjan spjallþráð';
$lang['Post_a_reply'] = 'Senda inn svar';
$lang['Post_topic_as'] = 'Senda spjallþráð inn sem';
$lang['Edit_Post'] = 'Breyta innleggi';
$lang['Options'] = 'Valkostir';

$lang['Post_Announcement'] = 'Tilkynning';
$lang['Post_Sticky'] = 'Líma';
$lang['Post_Normal'] = 'Venjuleg';

$lang['Confirm_delete'] = 'Ertu viss um að þú viljir eyða þessu innleggi?';
$lang['Confirm_delete_poll'] = 'Ertu viss um að þú viljir eyða þessari kosningu?';

$lang['Flood_Error'] = 'Þú getur ekki sent inn annað innlegg svo fljótt eftir síðasta, reyndu aftur seinna';
$lang['Empty_subject'] = 'Þú verður að slá inn efni þegar þú ert að senda inn nýjan spjallþráð';
$lang['Empty_message'] = 'Þú verður að skrifa inn skilaboð þegar þú ert að senda inn innlegg';
$lang['Forum_locked'] = 'Þessar umræður eru lokaðar, þú getur ekki sent inn, svarað eða breytt innleggi';
$lang['Topic_locked'] = 'Þessi spjallþráður er lokaður, þú getur ekki breytt, eða svarað innleggi';
$lang['No_post_id'] = 'Enginn póstur merktur';
$lang['No_topic_id'] = 'Þú verður að velja spjallþráð til að svara';
$lang['No_valid_mode'] = 'Þú getur bara sent inn innlegg, svarað, breytt eða vísað í innlegg. Vinsamlega farið til baka og reynið aftur';
$lang['No_such_post'] = 'Það er ekkert svona innlegg, farið til baka og reynið aftur';
$lang['Edit_own_posts'] = 'Því miður þá getur þú bara breytt þínu eigin innleggi';
$lang['Delete_own_posts'] = 'Því miður getur þú bara eytt þínu eigin innleggi';
$lang['Cannot_delete_replied'] = 'Því miður mátt þú bara eyða innleggi sem búið er að svara';
$lang['Cannot_delete_poll'] = 'Því miður getur þú ekki eytt skoðanakönnun sem er í gangi';
$lang['Empty_poll_title'] = 'Þú verður að setja inn nafn á skoðanakönnunina';
$lang['To_few_poll_options'] = 'Þú verður allavega að setja inn tvo möguleika';
$lang['To_many_poll_options'] = 'Þú hefur reynt að setja inn of marga möguleika';
$lang['Post_has_no_poll'] = 'Þetta innlegg er ekki með skoðanakönnun';
$lang['Already_voted'] = 'Þú hefur þegar tekið þátt í þessari skoðanakönnun';
$lang['No_vote_option'] = 'Þú verður að velja einn möguleika þegar þú ert að kjósa';

$lang['Add_poll'] = 'Bæta við skoðanakönnun';
$lang['Add_poll_explain'] = 'Ef þú vilt ekki hafa skoðanakönnun með innleggi þínu, hafðu þá reitina tóma';
$lang['Poll_question'] = 'Spurning í könnun';
$lang['Poll_option'] = 'Möguleikar í könnun';
$lang['Add_option'] = 'Bæta við möguleika';
$lang['Update'] = 'Uppfæra';
$lang['Delete'] = 'Eyða';
$lang['Poll_for'] = 'Keyra könnun í';
$lang['Days'] = 'Daga'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = '[ Settu 0 eða skildu reit eftir tóman fyrir endalausa skoðanakönnun ]';
$lang['Delete_poll'] = 'Eyða skoðanakönnun';

$lang['Disable_HTML_post'] = 'Gera HTML óvirkt í þessu innleggi';
$lang['Disable_BBCode_post'] = 'Gera BB kóða óvirkan í þessu innleggi';
$lang['Disable_Smilies_post'] = 'Gera Broskalla óvirka í þessu innleggi';

$lang['HTML_is_ON'] = 'HTML er <u>virkt</u>';
$lang['HTML_is_OFF'] = 'HTML er <u>óvirkt</u>';
$lang['BBCode_is_ON'] = '%sBB kóði%s er <u>virkur</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = '%sBB kóði%s er <u>óvirkur</u>';
$lang['Smilies_are_ON'] = 'Broskallar eru <u>virkir</u>';
$lang['Smilies_are_OFF'] = 'Broskallar eru <u>óvirkir</u>';

$lang['Attach_signature'] = 'Bæta við undirskrift (undirskrift er hægt að breyta í þinni uppsetningu)';
$lang['Notify'] = 'Láttu mig vita þegar svar er sent inn';
$lang['Delete_post'] = 'Eyða þessu innleggi';

$lang['Stored'] = 'Innlegg þitt er komið inn';
$lang['Deleted'] = 'Innleggi þínu hefur verið eytt';
$lang['Poll_delete'] = 'Skoðanakönnun þinni hefur verið eytt';
$lang['Vote_cast'] = 'Atkvæði þitt hefur verið móttekið';

$lang['Topic_reply_notification'] = 'Staðfesting Spjallþráða';

$lang['bbcode_b_help'] = 'Breiður texti: [b]texti[/b]  (alt+b)';
$lang['bbcode_i_help'] = 'Italic texti: [i]texti[/i]  (alt+i)';
$lang['bbcode_u_help'] = 'Skrifa undir texta: [u]texti[/u]  (alt+u)';
$lang['bbcode_q_help'] = 'Vitna í texta: [quote]texti[/quote]  (alt+q)';
$lang['bbcode_c_help'] = 'Kóða skjár: [code]kóði[/code]  (alt+c)';
$lang['bbcode_l_help'] = 'Listi: [list]texti[/list] (alt+l)';
$lang['bbcode_o_help'] = 'Raðaður listi: [list=]texti[/list]  (alt+o)';
$lang['bbcode_p_help'] = 'Setja inn mynd: [img]http://image_url[/img]  (alt+p)';
$lang['bbcode_w_help'] = 'Setja inn URL: [url]http://url[/url] eða [url=http://url]URL text[/url]  (alt+w)';
$lang['bbcode_a_help'] = 'Loka öllum opnum BB kóða merkjum';
$lang['bbcode_s_help'] = 'Litur á stöfum: [color=red]texti[/color]  Tip: þú getur líka notað color=#FF0000';
$lang['bbcode_f_help'] = 'Stafastærð: [size=x-small]smáir stafir[/size]';

$lang['Emoticons'] = 'Broskallar';
$lang['More_emoticons'] = 'Sjá fleiri Broskalla';

$lang['Font_color'] = 'Litur á stöfum';
$lang['color_default'] = 'Venjulegur';
$lang['color_dark_red'] = 'Dökk rauður';
$lang['color_red'] = 'Rauður';
$lang['color_orange'] = 'Rauðgulur';
$lang['color_brown'] = 'Brúnn';
$lang['color_yellow'] = 'Gulur';
$lang['color_green'] = 'Grænn';
$lang['color_olive'] = 'Ólívu';
$lang['color_cyan'] = 'Cyan';
$lang['color_blue'] = 'Blár';
$lang['color_dark_blue'] = 'Dökk Blár';
$lang['color_indigo'] = 'Indigo';
$lang['color_violet'] = 'Fjólublár';
$lang['color_white'] = 'Hvítur';
$lang['color_black'] = 'Svartur';

$lang['Font_size'] = 'Stafastærð';
$lang['font_tiny'] = 'Mjög litlir';
$lang['font_small'] = 'Litlir';
$lang['font_normal'] = 'Venjulegir';
$lang['font_large'] = 'Stórir';
$lang['font_huge'] = 'Mjög stórir';

$lang['Close_Tags'] = 'Loka merki';
$lang['Styles_tip'] = 'Ath.: Það er hægt að setja útlit auðveldlega á merktan texta';

//
// Private Messaging
//
$lang['Private_Messaging'] = 'Einkapóstur';

$lang['Login_check_pm'] = 'Skráðu þig inn til að athuga með einkapóst';
$lang['New_pms'] = 'Þú hefur fengið %d nýjan póst'; // You have 2 new messages
$lang['New_pm'] = 'Þú hefur fengið %d nýjan póst'; // You have 1 new message
$lang['No_new_pm'] = 'Þú hefur engan nýjan póst';
$lang['Unread_pms'] = 'Þú átt eftir %d ólesinn póst';
$lang['Unread_pm'] = 'Þú átt eftir %d ólesinn póst';
$lang['No_unread_pm'] = 'Þú átt engin ólesinn póst';
$lang['You_new_pm'] = 'Það bíður þín einkapóstur í innhólfinu þínu';
$lang['You_new_pms'] = 'Það bíða þín einkapóstur í innhólfinu þínu';
$lang['You_no_new_pm'] = 'Engin einkapóstur bíður þín';

$lang['Unread_message'] = 'Ólesinn póstur';
$lang['Read_message'] = 'Lesa póst';

$lang['Read_pm'] = 'Lesa póst';
$lang['Post_new_pm'] = 'Senda inn póst';
$lang['Post_reply_pm'] = 'Svara póst';
$lang['Post_quote_pm'] = 'Tilvísun í póst';
$lang['Edit_pm'] = 'Breyta pósti';

$lang['Inbox'] = 'Innhólf';
$lang['Outbox'] = 'Sendur póstur';
$lang['Savebox'] = 'Vistaður póstur';
$lang['Sentbox'] = 'Sendi póst';
$lang['Flag'] = 'Auðkenni';
$lang['Subject'] = 'Efni';
$lang['From'] = 'Frá';
$lang['To'] = 'Til';
$lang['Date'] = 'Dagsetning';
$lang['Mark'] = 'Merkja';
$lang['Sent'] = 'Sendur';
$lang['Saved'] = 'Vistað';
$lang['Delete_marked'] = 'Eyða merktum';
$lang['Delete_all'] = 'Eyða öllum';
$lang['Save_marked'] = 'Vista merkta'; 
$lang['Save_message'] = 'Vista póst';
$lang['Delete_message'] = 'Eyða pósti';

$lang['Display_messages'] = 'Sýna póst frá síðustu'; // Followed by number of days/weeks/months
$lang['All_Messages'] = 'Allur póstur';

$lang['No_messages_folder'] = 'Þú átt engan póst í þessari möppu';

$lang['PM_disabled'] = 'Einkapóstur er óvirkur á þessum umræðum';
$lang['Cannot_send_privmsg'] = 'Því miður hefur stjórnandi útilokað þig í að senda einkapóst á þessum umræðum';
$lang['No_to_user'] = 'Þú verður að slá inn notandanafn til að senda þennan póst';
$lang['No_such_user'] = 'Því miður þá er enginn notandi með þetta auðkenni';

$lang['Disable_HTML_pm'] = 'Gera HTML óvirkt í þessum pósti';
$lang['Disable_BBCode_pm'] = 'Gera BB kóða óvirkan í þessum pósti';
$lang['Disable_Smilies_pm'] = 'Gera Broskalla óvirka í þessum pósti';

$lang['Message_sent'] = 'Pósturinn hefur verið sendur';

$lang['Click_return_inbox'] = 'Ýtið %sHér%s til að fara til baka í innhólfið þitt';
$lang['Click_return_index'] = 'Ýtið %sHér%s til að fara til baka á yfirlit';

$lang['Send_a_new_message'] = 'Senda nýjan einka póst';
$lang['Send_a_reply'] = 'Svara einkapósti';
$lang['Edit_message'] = 'Breyta einkapósti';

$lang['Notification_subject'] = 'Nýr einkapóstur er kominn til þín';

$lang['Find_username'] = 'Finna notandanafn';
$lang['Find'] = 'Finna';
$lang['No_match'] = 'Ekkert fannst';

$lang['No_post_id'] = 'Enginn póstur merktur';
$lang['No_such_folder'] = 'Engin mappa til með þessu nafni';
$lang['No_folder'] = 'Engin mappa tilgreind';

$lang['Mark_all'] = 'Merkja alla';
$lang['Unmark_all'] = 'Taka merkingu af öllum';

$lang['Confirm_delete_pm'] = 'Ertu viss um að þú vilt eyða þessum pósti?';
$lang['Confirm_delete_pms'] = 'Ertu viss um að þú vilt eyða öllum þessum pósti?';

$lang['Inbox_size'] = 'Pósthólf þitt er %d%% fullt'; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = 'Pósthólf með sendum pósti er %d%% fullt'; 
$lang['Savebox_size'] = 'Pósthólf með Vistuðum pósti er %d%% fullt'; 

$lang['Click_view_privmsg'] = 'Ýtið %sHér%s til að fara í Innhólfið';

//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = 'Skoða uppsetningu :: %s'; // %s is username 
$lang['About_user'] = 'Allt um %s'; // %s is username

$lang['Preferences'] = 'Uppsetning';
$lang['Items_required'] = 'Reitir merktir með * verður að fylla út';
$lang['Registration_info'] = 'Upplýsingar um skráningu';
$lang['Profile_info'] = 'Upplýsingar um uppsetningu';
$lang['Profile_info_warn'] = 'Þessar upplýsingar sjá allir';
$lang['Avatar_panel'] = 'Stjórnborð fyrir myndir';
$lang['Avatar_gallery'] = 'Myndasafn';

$lang['Website'] = 'Vefsíða';
$lang['Location'] = 'Staðsetning';
$lang['Contact'] = 'Viltu samband við';
$lang['Email_address'] = 'E-mail/netfang';
$lang['Email'] = 'E-mail/netfang';
$lang['Send_private_message'] = 'Senda einkapóst';
$lang['Hidden_email'] = '[ Falið ]';
$lang['Search_user_posts'] = 'Finna öll innlegg eftir %s';
$lang['Interests'] = 'Áhugasvið';
$lang['Occupation'] = 'Atvinna'; 
$lang['Poster_rank'] = 'Punkta flokkur';

$lang['Total_posts'] = 'Samtals innlegg';
$lang['User_post_pct_stats'] = '%.2f%% af öllum'; // 1.25% of total
$lang['User_post_day_stats'] = '%.2f innlegg á dag'; // 1.5 posts per day
$lang['Search_user_posts'] = 'Finna öll innlegg eftir %s'; // Find all posts by username

$lang['No_user_id_specified'] = 'Því miður þá er þessi notandi ekki til';
$lang['Wrong_Profile'] = 'Þú getur ekki breytt uppsetningu annarra en þinni eigin.';

$lang['Only_one_avatar'] = 'Bara ein gerð af mynd er möguleg';
$lang['File_no_data'] = 'URLið fyrir skrána sem þú settir inniheldur engin gögn';
$lang['No_connection_URL'] = 'Það er ekkert samband við URLið sem þú settir inn';
$lang['Incomplete_URL'] = 'URLið sem þú settir inn er ekki rétt';
$lang['Wrong_remote_avatar_format'] = 'URLið sem þú settir inn vísar á ranga skrá';
$lang['No_send_account_inactive'] = 'Því miður er ekki hægt að nálgast aðgangsorðið þitt vegna þess að aðgangur þinn er óvirkur. Hafðu samband við umræðuborðsstjóra ef þú vilt meiri upplýsingar';

$lang['Always_smile'] = 'Hafa broskalla alltaf virka';
$lang['Always_html'] = 'Hafa HTML alltaf virkt';
$lang['Always_bbcode'] = 'Hafa BB kóða alltaf virkan';
$lang['Always_add_sig'] = 'Bæta undiskrift alltaf við skilaboð';
$lang['Always_notify'] = 'Láta alltaf vita ef svör berast';
$lang['Always_notify_explain'] = 'Sendir E-mail ef einhver svarar spjallþræði sem þú hefur sent inn á. Þessu er hægt að breyta er þú sendir inn innlegg';

$lang['Board_style'] = 'Þema borðs';
$lang['Board_lang'] = 'Tungumál borðs';
$lang['No_themes'] = 'Engir þemar í gagnagrunni';
$lang['Timezone'] = 'Tímabelti';
$lang['Date_format'] = 'Form á dagsetingu';
$lang['Date_format_explain'] = 'Formið er eins og PHP notar: <a href=\'http://www.php.net/date\' target=\'_other\'>date()</a> ';
$lang['Signature'] = 'Undirskrift';
$lang['Signature_explain'] = 'Þetta er texti sem þú getur látið bæta sjálfvirkt við innlegg sem þú sendir inn. Það er hámark %d stafir.';
$lang['Public_view_email'] = 'Sýna alltaf E-mail/netfang';

$lang['Current_password'] = 'Núverandi aðgangsorð';
$lang['New_password'] = 'Nýtt aðgangsorð';
$lang['Confirm_password'] = 'Staðfesta aðgangsorð';
$lang['Confirm_password_explain'] = 'Þú verður að staðfesta núverandi aðgangsorð ef þú vilt breyta því eða ef þú vilt breyta E-mail/netfangi þínu';
$lang['password_if_changed'] = 'Þú þarft að slá inn aðgangsorð ef þú vilt breyta því';
$lang['password_confirm_if_changed'] = 'Þú þarft að staðfesta nýja aðgangsorðið þitt ef ert að breyta því ofan við';

$lang['Avatar'] = 'Mynd';
$lang['Avatar_explain'] = 'Þetta er til að sýna litla mynd neðan við upplýsingar um þig í innleggjum þínum. Bara er hægt að sýna eina mynd í einu sem er ekki stærri en %d punktar að breidd, og ekki hærri er %d punktar og skráin má ekki vera stærri er %dkB.';
$lang['Upload_Avatar_file'] = 'Ná í mynd frá þinni tölvu';
$lang['Upload_Avatar_URL'] = 'Ná í mynd frá URLi';
$lang['Upload_Avatar_URL_explain'] = 'Slá inn URL þar sem myndin er staðsett og þaðan er hún þá afrituð og sett á okkar vefþjón.';
$lang['Pick_local_Avatar'] = 'Velja mynd úr myndasafni';
$lang['Link_remote_Avatar'] = 'Tengja í mynd á öðrum vefþjón';
$lang['Link_remote_Avatar_explain'] = 'Slá inn URL á mynd þar sem hún er staðsett.';
$lang['Avatar_URL'] = 'URL myndar';
$lang['Select_from_gallery'] = 'Velja mynd úr myndasafni';
$lang['View_avatar_gallery'] = 'Sýna myndasafn';

$lang['Select_avatar'] = 'Velja mynd';
$lang['Return_profile'] = 'Hætta með mynd';
$lang['Select_category'] = 'Velja hóp';

$lang['Delete_Image'] = 'Eyða mynd';
$lang['Current_Image'] = 'Núverandi mynd';

$lang['Notify_on_privmsg'] = 'Láta vita um einkapóst';
$lang['Popup_on_privmsg'] = 'Opna lítinn glugga þegar einkapóstur kemur'; 
$lang['Popup_on_privmsg_explain'] = 'Það gæti opnast gluggi til að láta þig vita þegar þú færð einkapóst'; 
$lang['Hide_user'] = 'Láta engan vita að þú sért tengdur';

$lang['Profile_updated'] = 'Uppsetning þín hefur verið uppfærð';
$lang['Profile_updated_inactive'] = 'Uppsetning þín hefur verið uppfærð en þú hefur breytt mikilvægum atriðum þannig að þú hefur ekki lengur aðgang. Athugaðu póstinn þinn til að sjá hvernig þú átt að virkja aðgang þinn aftur eða hvort þú þarft að bíða eftir að umræðustjóri lagar uppsetningu þína.';

$lang['Password_mismatch'] = 'Aðgangsorðin sem þú slóst inn eru ekki eins';
$lang['Current_password_mismatch'] = 'Núverandi aðgangsorð sem þú slóst inn passar ekki við það sem er í gagnagrunni okkar.';
$lang['Password_long'] = 'Aðgangsorð þitt má ekki vera lengra en 32 stafir';
$lang['Username_taken'] = 'Því miður þá er þetta notandanafn þegar notað hjá okkur';
$lang['Username_invalid'] = 'Því miður þá inniheldur þetta notandanafn stafi sem eru ekki leyfilegir, svo sem \'';
$lang['Username_disallowed'] = 'Því miður þá er þetta notandanafn ekki leyft';
$lang['Email_taken'] = 'Því miður þá er þetta netfang þegar skráð hjá okkur';
$lang['Email_banned'] = 'Því miður er þetta netfang í banni ';
$lang['Email_invalid'] = 'Því  miður er þetta netfang ekki rétt';
$lang['Signature_too_long'] = 'Undiskrift þín er of löng';
$lang['Fields_empty'] = 'Þú verður að slá inn í reitina sem eru merktir';
$lang['Avatar_filetype'] = 'Myndirnar verða að vera .jpg, .gif eða .png';
$lang['Avatar_filesize'] = 'Myndirnar verða að vera minni en %d kB'; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = 'Myndin verður að vera minni en %d puntar að breidd og %d punkta há'; 

$lang['Welcome_subject'] = 'Velkomin til umræðuborðs hjá %s'; // Welcome to my.com forums
$lang['New_account_subject'] = 'Nýr notandi';
$lang['Account_activated_subject'] = 'Aðgangur virkur';

$lang['Account_added'] = 'Takk fyrir að skrá þig, aðgangur þinn er nú virkur. Þú getur skráð þig inn með notandanafni og aðgangsorði';
$lang['Account_inactive'] = 'Nú hefur þú fengið reikning skráðan. Þú þarft að gera hann virkan og það hefur verið sent E-mail til þín á netfangið sem þú slóst inn og er það með lykil til að virkja hann. Skoðaðu póstinn þinn til að fá nánari upplýsingar.';
$lang['Account_inactive_admin'] = 'Reikningur þinn hefur verið útbúinn. Hinsvegar þarf umsjónarmaður hópsins að gera hann virkann. Honum hefur nú verið sendur E-mail og þú verður látinn vita um leið og reikningurinn er virkur.';
$lang['Account_active'] = 'Aðgangur þinn er nú virkur. Takk fyrir að skrá þig.';
$lang['Account_active_admin'] = 'Aðgangur þinn er nú virkur.';
$lang['Reactivate'] = 'Gerðu aðgang þinn aftur virkan!';
$lang['Already_activated'] = 'Þú hefur nú þegar gert aðgang þinn virkan';
$lang['COPPA'] = 'Aðgangur þinn hefur verið samþykktur. Þú færð nánari upplýsingar í E-mail.';

$lang['Registration'] = 'Skilmálar fyrir skráningu';
$lang['Reg_agreement'] = 'Þó svo að umsjónarmaður og stjórnendur þessa spjallrása munu reyna að fjarlægja eða breyta öllum innleggjum sem eru óviðeigandi efni eins fljótt og mögulegt er þá er ómögulegt að yfirfara hvert einasta innlegg frá notendum. Þess vegna samþykkir þú að öll innlegg sýnir skoðun höfundar en ekki umsjónarmanns, stjórnanda spjallrásanna eða vefstjóra(nema innlegg frá þeim sjálfum) og þar af leiðandi er ekki hægt að gera þá ábyrga fyrir þeim.<br /><br />Þú samþykkir að senda ekki inn nein innlegg þar sem kemur fram móðgandi, særandi, dónaleg, hótanir, hatursfull, kynferðisleg eða annað efni sem getur verið bannað samkvæmt lögum. Ef slíkt kemur fyrir þá verður viðkomandi útilokaður frá öllum samskiptum á þessum umræðum (þjónustuaðili verður látinn vita). IP tala allra innleggja er skráð til að sporna við svona misnotkun. Þú samþykkir að vefstjóri, umsjónarmaður eða stjórnandi þessara spjallrása hafa fullan rétt til að fjarlægja, breyta, færa eða loka einhverjum spjallþræði hvenær sem er ef þeir sjá ástæðu til. Sem notandi þá samþykkir þú að allar þær upplýsingar sem þú hefur slærð inn verði Vistaðar á gagnagrunni okkar. Þó svo að þessar upplýsingar verði ekki sendar til þriðja aðila án þíns samþykkis þá geta vefstjóri, umsjónarmaður eða stjórnandi þessara spjallrása ekki verið saksóttir ef einhver brýst inn í gagnagrunninn sem verður þess valdandi að upplýsingar eru teknar.<br /><br />Þetta umræðu kerfi notar vefkökur til að geyma upplýsingar á þinni tölvu. Þessar vefkökur innihalda ekki neinar þær upplýsingar sem þú slærð inn hér, þær eru einungis til að auðvelda notkum hópsins. Netfangið er einungis notað til að senda staðfestingu um aðgangsorð og upplýsingar um þína skráningu (og til að senda þér nýtt aðgangsorð ef þú gleymir því).<br /><br />Með því að ýta á samþykkt hér neðanvið þá samþykkir þú þetta.';

$lang['Agree_under_13'] = 'Ég samþykki þessa skilmála og er <b>yngri</b> en 13 ára';
$lang['Agree_over_13'] = 'Ég samþykki þessa skilmála og er <b>eldri</b> en 13 ára';
$lang['Agree_not'] = 'Ég samþykki ekki þessa skilmála';

$lang['Wrong_activation'] = 'Virkjunar lykill sem þú settir inn passar ekki við þá sem við höfum í gagnagrunni okkar';
$lang['Send_password'] = 'Sendið mér nýtt aðgangsorð'; 
$lang['Password_updated'] = 'Nýtt aðgangsorð hefur verið útbúið, athugaðu í E-mailið þitt til að fá upplýsingar um hvernig á að virkja það';
$lang['No_email_match'] = 'Netfangið sem þú gafst upp passar ekki við það sem er skráð við þetta notandanafn';
$lang['New_password_activation'] = 'Virkja nýtt aðgangsorð';
$lang['Password_activated'] = 'Aðgangur þinn hefur verið gerður aftur virkur. Til að skrá þig skaltu nota aðgangsorðið sem þú fékkst í pósti til þín';

$lang['Send_email_msg'] = 'Senda E-mail/póst';
$lang['No_user_specified'] = 'Enginn notandi var valinn';
$lang['User_prevent_email'] = 'Þessi notandi vill ekki móttaka E-mail/póst. Reyndu að senda honum einkapóst';
$lang['User_not_exist'] = 'Þessi notandi er ekki til';
$lang['CC_email'] = 'Senda afrit af af pósti til þín líka';
$lang['Email_message_desc'] = 'Þessi póstur verður sendur sem venjulegur texti, ekki setja inn neinn HTML eða BB Kóða. Sem svar netfang er þitt netfang sett.';
$lang['Flood_email_limit'] = 'Þú getur ekki sent annan póst núna, reyndu aftur seinna';
$lang['Recipient'] = 'Mótttakandi';
$lang['Email_sent'] = 'Pósturinn hefur verið sendur';
$lang['Send_email'] = 'Senda póst';
$lang['Empty_subject_email'] = 'Þú verður að setja inn efni fyrir þennan E-mail/póst';
$lang['Empty_message_email'] = 'Þú verður að setja inn einhver skilaboð sem hægt er að senda';

//
// Memberslist
//
$lang['Select_sort_method'] = 'Veldu aðferð við röðun';
$lang['Sort'] = 'Raða';
$lang['Sort_Top_Ten'] = 'Topp tíu meðlimir';
$lang['Sort_Joined'] = 'Skráningar dagsetning';
$lang['Sort_Username'] = 'Notandanafn';
$lang['Sort_Location'] = 'Staðsetning';
$lang['Sort_Posts'] = 'Samtals innlegg';
$lang['Sort_Email'] = 'E-mail';
$lang['Sort_Website'] = 'Vefsíða';
$lang['Sort_Ascending'] = 'Frá A til Ö';
$lang['Sort_Descending'] = 'Frá Ö til A';
$lang['Order'] = 'Röð';

//
// Group control panel
//
$lang['Group_Control_Panel'] = 'Hópa stjórnborð';
$lang['Group_member_details'] = 'Upplýsingar um hóp';
$lang['Group_member_join'] = 'Taka þátt í hóp';

$lang['Group_Information'] = 'Hóp upplýsingar';
$lang['Group_name'] = 'Nafn hóps';
$lang['Group_description'] = 'Lýsing á hópi';
$lang['Group_membership'] = 'Taka þátt í hóp';
$lang['Group_Members'] = 'Þátttakendur hóps';
$lang['Group_Moderator'] = 'Stjórnandi hóps';
$lang['Pending_members'] = 'Viðkomandi þátttakendur';

$lang['Group_type'] = 'Gerð hóps';
$lang['Group_open'] = 'Opinn hópur';
$lang['Group_closed'] = 'Lokaður hópur';
$lang['Group_hidden'] = 'Falinn hópur';

$lang['Current_memberships'] = 'Hópar sem þú tekur þátt í:';
$lang['Non_member_groups'] = 'Hópar án þinnar þátttöku:';
$lang['Memberships_pending'] = 'Þátttakendur óskast';

$lang['No_groups_exist'] = 'Engir hópar eru til';
$lang['Group_not_exist'] = 'Þessi hópur er ekki til';

$lang['Join_group'] = 'Taka þátt í hóp';
$lang['No_group_members'] = 'Þessi hópur hefur enga þátttakendur';
$lang['Group_hidden_members'] = 'Þessi hópur er falinn, þú getur ekki séð þátttakendur';
$lang['No_pending_group_members'] = 'Þessi hópur hefur enga þátttakendur';
$lang['Group_joined'] = 'Þú hefur skráð þig á þennan hóp<br />Þú verður látin/n vita hvort umsóknin verði samþykkt af stjórnanda hópsins';
$lang['Group_request'] = 'Beiðni um þátttöku í hópnum hefur verið gerð';
$lang['Group_approved'] = 'Beiðni þín hefur verið samþykkt';
$lang['Group_added'] = 'Þér hefur verið bætt við þennan notendahóp'; 
$lang['Already_member_group'] = 'Þú ert þegar þátttakandi í þessum hóp';
$lang['User_is_member_group'] = 'Notandi er þegar þátttakandi í þessum hóp';
$lang['Group_type_updated'] = 'Gerð hóps var uppfærð';

$lang['Could_not_add_user'] = 'Notandi sem þú valdir er ekki til';
$lang['Could_not_anon_user'] = 'Þú getur ekki verið óskráð/ur sem þátttakandi í hóp';

$lang['Confirm_unsub'] = 'Ertu viss um að þú viljir skrá þig úr þessum hóp?';
$lang['Confirm_unsub_pending'] = 'Umsókn þín um þátttöku í hennan hóp er í vinnslu, ertu viss um að þú viljir skrá þig úr honum?';

$lang['Unsub_success'] = 'Þú hefur verið skráð/ur úr þessum hóp.';

$lang['Approve_selected'] = 'Samþykkt valið';
$lang['Deny_selected'] = 'Ekki samþykkt valið';
$lang['Not_logged_in'] = 'Þú verður að vera skráður inn til að skrá þig á hóp.';
$lang['Remove_selected'] = 'Fjarlægja merkta notendur';
$lang['Add_member'] = 'Bæta við þátttakanda';
$lang['Not_group_moderator'] = 'Þú ert ekki stjórnandi þessa hóps þannig að þá getur þú ekki gert þetta.';

$lang['Login_to_join'] = 'Skrá þig inn til að sækja um aðild eða stjórna hóp';
$lang['This_open_group'] = 'Þetta er opinn hópur, ýtið til að sækja um þátttöku';
$lang['This_closed_group'] = 'Þetta er lokaður hópur, það eru ekki tekið við fleiri þátttakendum';
$lang['This_hidden_group'] = 'Þetta er lokaður hópur, sjálfvirk mótttaka nýrra þátttakanda er ekki leyfð';
$lang['Member_this_group'] = 'Þú ert meðlimur þessa hóps';
$lang['Pending_this_group'] = 'Umsókn um þátttöku í þessum hóp er ekki lokið';
$lang['Are_group_moderator'] = 'Þú ert stjórnandi þessa hóps';
$lang['None'] = 'Enginn';

$lang['Subscribe'] = 'Gerast áskrifandi';
$lang['Unsubscribe'] = 'Hætta áskrift';
$lang['View_Information'] = 'Skoða upplýsingar';

//
// Search
//
$lang['Search_query'] = 'Leita';
$lang['Search_options'] = 'Leitar skilyrði';

$lang['Search_keywords'] = 'Leita að orðum';
$lang['Search_keywords_explain'] = 'Þú getur notað <u>AND</u> til að hafa fleiri en eitt orð í útkomunni, <u>OR</u> til að hafa annað hvort og <u>NOT</u> til að útiloka ákveðin orð í leit. Notið * til að hluti úr orði passi';
$lang['Search_author'] = 'Leita að höfundi';
$lang['Search_author_explain'] = 'Notið * til að leita að hluta úr orði';

$lang['Search_for_any'] = 'Leita að setningu eða í sömu röð og slegið inn';
$lang['Search_for_all'] = 'Leita að öllum orðum';
$lang['Search_title_msg'] = 'Leita að nafni að spjallþráð og texta í innleggi';
$lang['Search_msg_only'] = 'Leita í bara í texta innleggs';

$lang['Return_first'] = 'Sýna fyrstu'; // followed by xxx characters in a select box
$lang['characters_posts'] = 'Stafir í innleggjum';

$lang['Search_previous'] = 'Leita síðustu'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = 'Raða eftir';
$lang['Sort_Time'] = 'Dagsetning innleggs';
$lang['Sort_Post_Subject'] = 'Efni innleggs';
$lang['Sort_Topic_Title'] = 'Titill spjallþráðs';
$lang['Sort_Author'] = 'Höfundur';
$lang['Sort_Forum'] = 'Umræður';

$lang['Display_results'] = 'Sýna útkomu sem';
$lang['All_available'] = 'Allt sem til er';
$lang['No_searchable_forums'] = 'Þú hefur ekki réttindi til að leita á neinum umræðum á þessari síðu';

$lang['No_search_match'] = 'Engir spjallþræðir eða innlegg pössuðu við leitarskilyrði';
$lang['Found_search_match'] = 'Leit skilaði %d útkomu'; // eg. Search found 1 match
$lang['Found_search_matches'] = 'Leit skilaði %d útkomum'; // eg. Search found 24 matches

$lang['Close_window'] = 'Loka glugga';

//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = 'Því miður þá getur bara %s sett inn tilkynningar á þessar umræður';
$lang['Sorry_auth_sticky'] = 'Því miður þá getur bara %s límt inn á þessar umræður'; 
$lang['Sorry_auth_read'] = 'Því miður þá getur bara %s lesið spjallþræði á þessum umræðum'; 
$lang['Sorry_auth_post'] = 'Því miður þá getur bara %s sett inn spjallþræði á þessar umræður'; 
$lang['Sorry_auth_reply'] = 'Því miður þá getur bara %s svarað innleggjum á þessum umræðum'; 
$lang['Sorry_auth_edit'] = 'Því miður þá getur bara %s breytt innleggjum á þessum umræðum'; 
$lang['Sorry_auth_delete'] = 'Því miður þá getur bara %s eytt innleggjum á þessum umræðum'; 
$lang['Sorry_auth_vote'] = 'Því miður þá getur bara %s tekið þátt í skoðanakönnunum á þessum umræðum'; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>óskráður notandi</b>';
$lang['Auth_Registered_Users'] = '<b>skráður notandi</b>';
$lang['Auth_Users_granted_access'] = '<b>notandi með sérstakan aðgang</b>';
$lang['Auth_Moderators'] = '<b>stjórnandi</b>';
$lang['Auth_Administrators'] = '<b>umræðuborðsstjóri</b>';

$lang['Not_Moderator'] = 'Þú ert ekki stjórnandi á þessum umræðum';
$lang['Not_Authorised'] = 'Ekki heimilað';

$lang['You_been_banned'] = 'Þú hefur verið bannaður/bönnuð frá þessum umræðum.<br />Hafðu samband við vefstjóra eða umræðuborðsstjóra til að fá meiri upplýsingar';

//
// Viewonline
//
$lang['Reg_users_zero_online'] = 'Það eru enginn skráður notandi og '; // There ae 5 Registered and
$lang['Reg_users_online'] = 'Það eru %d skráðir notendur og '; // There ae 5 Registered and
$lang['Reg_user_online'] = 'Það eru %d skráðir notendur og '; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = 'enginn falinn notandi tengdur núna'; // 6 Hidden users online
$lang['Hidden_users_online'] = '%d faldir notendur tengdir núna'; // 6 Hidden users online
$lang['Hidden_user_online'] = '%d faldir notendur tengdir núna'; // 6 Hidden users online
$lang['Guest_users_online'] = 'Það eru %d gestir tengdir núna'; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = 'Það eru engir gestir tengdir núna'; // There are 10 Guest users online
$lang['Guest_user_online'] = 'Það er %d gestur tengdur núna'; // There is 1 Guest user online
$lang['No_users_browsing'] = 'Það er enginn notandi að skoða umræðurnar núna';

$lang['Online_explain'] = 'Þessar upplýsingar eru byggðar á síðustu fimm mínútum';

$lang['Forum_Location'] = 'Staðsetning umræða';
$lang['Last_updated'] = 'Síðast uppfært';

$lang['Forum_index'] = 'Efnisyfirlit umræða';
$lang['Logging_on'] = 'Tengi núna';
$lang['Posting_message'] = 'Senda inn innlegg';
$lang['Searching_forums'] = 'Leita í umræðum';
$lang['Viewing_profile'] = 'Skoða uppsetningu';
$lang['Viewing_online'] = 'Sjá hverjur eru tengdir núna';
$lang['Viewing_member_list'] = 'Skoða skráða notendur';
$lang['Viewing_priv_msgs'] = 'Skoða einkapóst';
$lang['Viewing_FAQ'] = 'Skoða algengar spurningar';

//
// Moderator Control Panel
//
$lang['Mod_CP'] = 'Stjórnborð umræðu stjórnanda';
$lang['Mod_CP_explain'] = 'Með því að nota formið hér neðanvið þá getur þú stjórnað miklu samtímis á þessum umræðum. Þú getur lokað, opnað, fært eða eytt spjallþráðum.';

$lang['Select'] = 'Merktu';
$lang['Delete'] = 'Eyða';
$lang['Move'] = 'Færa';
$lang['Lock'] = 'Læsa';
$lang['Unlock'] = 'Opna';

$lang['Topics_Removed'] = 'Merktir spjallþræðir hafa verið fjarlægðir úr gagnagrunninum.';
$lang['Topics_Locked'] = 'Merktum spjallþráðum hefur verið lokað';
$lang['Topics_Moved'] = 'Merktir spjallþræðir hafa verið færðir';
$lang['Topics_Unlocked'] = 'Merktir spjallþræðir hafa verið opnaðir';
$lang['No_Topics_Moved'] = 'Engir spjallþræðir hafa verið færðir';

$lang['Confirm_delete_topic'] = 'Ertu viss um að þú vilt eyða merktum spjallþræði?';
$lang['Confirm_lock_topic'] = 'Ertu viss um að þú vilt loka merktum spjallþráðum?';
$lang['Confirm_unlock_topic'] = 'Ertu viss um að þú vilt opna merkta spjallþræði?';
$lang['Confirm_move_topic'] = 'Ertu viss um að þú viljir færa merkta spjallþræði?';

$lang['Move_to_forum'] = 'Færa til umræða';
$lang['Leave_shadow_topic'] = 'Skilja eftir skyggða spjallþræði í gömlu umræðum.';

$lang['Split_Topic'] = 'Stjórnborð til að skipta spjallþráðum';
$lang['Split_Topic_explain'] = 'Með því að nota formið hér neðanvið þá getur þú skipt spjallþráðum í tvennt með því annaðhvort að velja hvert og eitt innlegg eða skipta við ákveðið innlegg';
$lang['Split_title'] = 'Titill nýs spjallþráðs';
$lang['Split_forum'] = 'Umræður fyrir nýjan spjallþráð';
$lang['Split_posts'] = 'Skipta merktum innleggjum';
$lang['Split_after'] = 'Skipta frá völdu innleggi';
$lang['Topic_split'] = 'Völdum spjallþráðum hefur verið skipt';

$lang['Too_many_error'] = 'Þú hefur valið of mörg innlegg. Þú getur bara valið eitt innlegg til að skipta spjallþráðum eftir!';

$lang['None_selected'] = 'Þú hefur ekki valið neinn spjallþráð til að gera þessa aðgerð nú. Farðu aftur og veldu allavega einn.';
$lang['New_forum'] = 'Nýjar umræður';

$lang['This_posts_IP'] = 'IP fyrir þetta innlegg';
$lang['Other_IP_this_user'] = 'Aðrar IP tölur sem þessi notandi hefur sent innlegg frá';
$lang['Users_this_IP'] = 'Notendur sem senda frá þessari IP tölu';
$lang['IP_info'] = 'IP upplýsingar';
$lang['Lookup_IP'] = 'Leita að IP tölu';

//
// Timezones ... for display on each page
//
$lang['All_times'] = 'Allir tímar eru %s'; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = 'GMT - 12 Klst.';
$lang['-11'] = 'GMT - 11 Klst.';
$lang['-10'] = 'GMT - 10 Klst.';
$lang['-9'] = 'GMT - 9 Klst.';
$lang['-8'] = 'GMT - 8 Klst.';
$lang['-7'] = 'GMT - 7 Klst.';
$lang['-6'] = 'GMT - 6 Klst.';
$lang['-5'] = 'GMT - 5 Klst.';
$lang['-4'] = 'GMT - 4 Klst.';
$lang['-3.5'] = 'GMT - 3.5 Hours';
$lang['-3'] = 'GMT - 3 Klst.';
$lang['-2'] = 'GMT - 2 Klst.';
$lang['-1'] = 'GMT - 1 Klst.';
$lang['0'] = 'GMT';
$lang['1'] = 'GMT + 1 Klst.';
$lang['2'] = 'GMT + 2 Klst.';
$lang['3'] = 'GMT + 3 Klst.';
$lang['3.5'] = 'GMT + 3.5 Hours';
$lang['4'] = 'GMT + 4 Klst.';
$lang['4.5'] = 'GMT + 4.5 Hours';
$lang['5'] = 'GMT + 5 Klst.';
$lang['5.5'] = 'GMT + 5.5 Hours';
$lang['6'] = 'GMT + 6 Klst.';
$lang['6.5'] = 'GMT + 6.5 Hours';
$lang['7'] = 'GMT + 7 Klst.';
$lang['8'] = 'GMT + 8 Klst.';
$lang['9'] = 'GMT + 9 Klst.';
$lang['9.5'] = 'GMT + 9.5 Hours';
$lang['10'] = 'GMT + 10 Klst.';
$lang['11'] = 'GMT + 11 Klst.';
$lang['12'] = 'GMT + 12 Klst.';

// These are displayed in the timezone select box
$lang['tz']['-12'] = 'GMT - 12 Klst.';
$lang['tz']['-11'] = 'GMT - 11 Klst.';
$lang['tz']['-10'] = 'GMT - 10 Klst.';
$lang['tz']['-9'] = 'GMT - 9 Klst.';
$lang['tz']['-8'] = 'GMT - 8 Klst.';
$lang['tz']['-7'] = 'GMT - 7 Klst.';
$lang['tz']['-6'] = 'GMT - 6 Klst.';
$lang['tz']['-5'] = 'GMT - 5 Klst.';
$lang['tz']['-4'] = 'GMT - 4 Klst.';
$lang['tz']['-3.5'] = 'GMT - 3.5 Hours';
$lang['tz']['-3'] = 'GMT - 3 Klst.';
$lang['tz']['-2'] = 'GMT - 2 Klst.';
$lang['tz']['-1'] = 'GMT - 1 Klst.';
$lang['tz']['0'] = 'GMT';
$lang['tz']['1'] = 'GMT + 1 Klst.';
$lang['tz']['2'] = 'GMT + 2 Klst.';
$lang['tz']['3'] = 'GMT + 3.5 Klst.';
$lang['tz']['3.5'] = 'GMT + 3.5 Hours';
$lang['tz']['4'] = 'GMT + 4.5 Klst.';
$lang['tz']['4.5'] = 'GMT + 4.5 Hours';
$lang['tz']['5'] = 'GMT + 5.5 Klst.';
$lang['tz']['5.5'] = 'GMT + 5.5 Hours';
$lang['tz']['6'] = 'GMT + 6.5 Klst.';
$lang['tz']['6.5'] = 'GMT + 6.5 Hours';
$lang['tz']['7'] = 'GMT + 7 Klst.';
$lang['tz']['8'] = 'GMT + 8 Klst.';
$lang['tz']['9'] = 'GMT + 9.5 Klst.';
$lang['tz']['9.5'] = 'GMT + 9.5 Hours';
$lang['tz']['10'] = 'GMT + 10 Klst.';
$lang['tz']['11'] = 'GMT + 11 Klst.';
$lang['tz']['12'] = 'GMT + 12 Klst.';

$lang['datetime']['Sunday'] = 'Sunnudagur';
$lang['datetime']['Monday'] = 'Mánudagur';
$lang['datetime']['Tuesday'] = 'Þriðjudagur';
$lang['datetime']['Wednesday'] = 'Miðvikudagur';
$lang['datetime']['Thursday'] = 'Fimmtudagur';
$lang['datetime']['Friday'] = 'Föstudagur';
$lang['datetime']['Saturday'] = 'Laugardagur';
$lang['datetime']['Sun'] = 'Sun';
$lang['datetime']['Mon'] = 'Mán';
$lang['datetime']['Tue'] = 'Þri';
$lang['datetime']['Wed'] = 'Mið';
$lang['datetime']['Thu'] = 'Fim';
$lang['datetime']['Fri'] = 'Fös';
$lang['datetime']['Sat'] = 'Lau';
$lang['datetime']['January'] = 'Janúar';
$lang['datetime']['February'] = 'Febrúar';
$lang['datetime']['March'] = 'Mars';
$lang['datetime']['April'] = 'Apríl';
$lang['datetime']['May'] = 'Maí';
$lang['datetime']['June'] = 'Júní';
$lang['datetime']['July'] = 'Júlí';
$lang['datetime']['August'] = 'Ágúst';
$lang['datetime']['September'] = 'September';
$lang['datetime']['October'] = 'Október';
$lang['datetime']['November'] = 'Nóvember';
$lang['datetime']['December'] = 'Desember';
$lang['datetime']['Jan'] = 'Jan';
$lang['datetime']['Feb'] = 'Feb';
$lang['datetime']['Mar'] = 'Mar';
$lang['datetime']['Apr'] = 'Apr';
$lang['datetime']['May'] = 'Maí';
$lang['datetime']['Jun'] = 'Jún';
$lang['datetime']['Jul'] = 'Júl';
$lang['datetime']['Aug'] = 'Ágú';
$lang['datetime']['Sep'] = 'Sep';
$lang['datetime']['Oct'] = 'Okt';
$lang['datetime']['Nov'] = 'Nóv';
$lang['datetime']['Dec'] = 'Des';

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = 'Upplýsingar';
$lang['Critical_Information'] = 'Mikilvægar upplýsingar';

$lang['General_Error'] = 'Það varð villa';
$lang['Critical_Error'] = 'Alvarleg villa';
$lang['An_error_occured'] = 'Það varð Villa';
$lang['A_critical_error'] = 'Það varð alvarleg villa';

//Add on for Gender Mod 
$lang['Gender'] = 'Kyn';	//used in users profile to display witch gender he/she is 
$lang['Male'] = 'Karl'; 
$lang['Female'] = 'Kona'; 
$lang['No_gender_specify'] = 'Ekki tilgreint'; 

//
// MOD: Show Ranks in FAQ v1.0.2
//
$lang['RankFAQ_Block_Title'] = 'Stig';
$lang['RankFAQ_Link_Title'] = 'Stig á umræðum';
$lang['RankFAQ_Title'] = 'Stig';
$lang['RankFAQ_Min'] = 'Minnsti fjöldi innleggja';
$lang['RankFAQ_Image'] = 'Mynd stigs';
$lang['RankFAQ_None'] = 'Ekki til';
//
// MOD: -END-
//

//
// MOD: Admin Link in User Profile v1.0.2
//
$lang['AdminLink_Label'] = 'Notenda umsjón';
$lang['AdminLink_Manage'] = 'Stjórnun';
$lang['AdminLink_Permissions'] = 'Heimildir';
//
// MOD: -END-
//

//
// MOD: Enlarge Subject v1.0.3
//
$lang['No_Subject'] = '(Ekki efni)';
//
// MOD: -END-
//

//
// MOD: View Recent Posts v1.0.2
//
$lang['View_posts_of_last'] = 'Skoða innlegg frá síðasta';
//
// MOD: -END-
//

//
// MOD: Birthday/Zodiac v1.0.2
//
$lang['birth_date'] = 'Fæðingardagur';
$lang['years_of_age'] = '(%s ára)';
$lang['zodiac_sign'] = 'Stjörnumerki';
$lang['no_birth_date'] = 'Ekki tilgreint';
$lang['Aries'] = 'Hrútur';
$lang['Taurus'] = 'Naut';
$lang['Gemini'] = 'Tvíburar';
$lang['Cancer'] = 'Krabbi';
$lang['Leo'] = 'Ljón';
$lang['Virgo'] = 'Meyja';
$lang['Libra'] = 'Vogin';
$lang['Scorpio'] = 'Sporðdreki';
$lang['Sagittarius'] = 'Bogamaður';
$lang['Capricorn'] = 'Steingeit';
$lang['Aquarius'] = 'Vatnsberi';
$lang['Pisces'] = 'Fiskur';
$lang['DATE_INPUT_FORMAT'] = 'd/m/Y';	// only use m, n, d, j, and Y (or y) here!
$lang['birth_date_explain'] = 'Sláðu inn fæðingardag þinn svona: <d>%s</b>. Fyrir daginn í dag er það <b>%s</b>.';	// Enter your birth date as <b>m/d/Y</b>. For today, that would be <b>06/18/2002</b>.
$lang['Invalid_birth_date'] = 'Þú hefur tilgreint ógildan fæðingardag.';
$lang['birth_date_required'] = 'Þú verður að slá inn fæðingardag þinn til að fá aðgang.';
$lang['You_are_too_young'] = 'Þú verður að vera allavega %d ára til að fá aðgang að þessu umræðuborði.';
$lang['You_are_too_old'] = 'Við getum ekki trúað því að þú sért raunverulega %d ára! Við erum með hámark upp í %d ára aldur, því miður!';
//
// MOD: -END-
//

//
// MOD: Prune Inactive Users v1.2.0
//
$lang['User_prune_this_user'] = 'Eyða notanda %s'; // ALT/TITLE text for memberlist delete button
//
// MOD: -END-
//

//
// MOD: Admin Shadow Topics v1.1.0
//
//$lang['Shadow_Topics'] = 'Shadow Topics';
//
// MOD: -END-
//

//
// MOD: Board Statistics v1.2.4
//
$lang['Statistics'] = 'Tölfræði';	// for menu button
//
// MOD: -END-
//

//
// MOD: Simple User Last Visit v1.0.0
//
$lang['Last_visit'] = 'Síðasta innskráning';
$lang['Never'] = 'Aldrei';
$lang['Sort_Last_visit'] = 'Síðast skráð inn þann';
//
// MOD: -END-
//

//
// MOD: Reduce Categories v1.1.0
//
$lang['Expand_this_category'] = 'Stækka þennan flokk';
$lang['Reduce_this_category'] = 'Minnka þennan flokk';
//
// MOD: -END-
//

//
// MOD: MyCalendar v2.2.4
//
// DATE_INPUT_FORMAT already defined in Birthday & Zodiac MOD!
//$lang['DATE_INPUT_FORMAT'] = 'd/m/Y';	// only use m, n, d, j, and Y (or y) here!
$lang['interval']['day'] = 'daga';
$lang['interval']['days'] = 'dagar';
$lang['interval']['week'] = 'vika';
$lang['interval']['weeks'] = 'vikur';
$lang['interval']['month'] = 'mánuð';
$lang['interval']['months'] = 'mánuði';
$lang['interval']['year'] = 'ár';
$lang['interval']['years'] = 'ár';
$lang['Event_Start'] = 'Einu sinni eða byrjun dags.';
$lang['Event_End'] = 'Enda dagsetning og millibil';
$lang['Calendar_advanced_form'] = 'yfirgripsmikil';
$lang['Calendar_repeat_forever'] = 'endurtaka alltaf';
$lang['Clear_Date'] = 'Eyða dags';
$lang['Date_not_specified'] = 'Veldu -->';
$lang['Select_start_date'] = 'Veldu byrjunar dagsetningu'; // must escape ' as \\\' for javascript
$lang['Calendar_event_title'] = 'Hátíðisdagar';
$lang['View_calendar'] = 'Dagatal';
$lang['View_previous_month'] = 'Skoða síðasta mánuð';
$lang['View_next_month'] = 'Skoða næsta mánuð';
$lang['View_previous_year'] = 'Skoða síðast ár';
$lang['View_next_year'] = 'Skoða næsta ár';
$lang['Calendar_interval'] = 'Millibil';
$lang['Calendar_repeat'] = 'Endurtaka';
$lang['Calendar_start_monday'] = '{Calendar_start_monday}';	// FALSE
$lang['Date_selector'] = 'Val dagsetninga';   // title/header for Date Selector Window
//
// MOD: -END-
//

//
// MOD: Gallery - phpBB2 Integration v1.3.1
//
$lang['Gallery'] = 'Myndasafn';
//
// MOD: -END-
//

//
// That's all Folks!
// -------------------------------------------------

?>