<?php
/***************************************************************************
 *                            lang_main.php [Arabic]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_main.php,v 1.77 2002/01/13 15:39:36 psotfx Exp $
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
// Translation by waheed
//

//setlocale(LC_ALL, "ar");
$lang['ENCODING'] = "windows-1256";
$lang['DIRECTION'] = "RTL";
$lang['LEFT'] = "LEFT";
$lang['RIGHT'] = "RIGHT";
$lang['DATE_FORMAT'] =  "d M Y"; // This should be changed to the default date format for your language, php date() format

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "ãäÊÏì";
$lang['Category'] = "ÊÕäíİ";
$lang['Topic'] = "ãŞÇáÉ";
$lang['Topics'] = "ãŞÇáÇÊ";
$lang['Replies'] = "ÑÏæÏ";
$lang['Views'] = "ÔæåÏ";
$lang['Post'] = "äÔÑÉ";
$lang['Posts'] = "äÔÑÇÊ";
$lang['Posted'] = "ÇÑÓá";
$lang['Username'] = "ÇÓã ãÔÊÑß";
$lang['Password'] = "ßáãÉ ÇáÓÑ";
$lang['Email'] = "ÈÑíÏ ÇáßÊÑæäí";
$lang['Poster'] = "ãáÕŞ";
$lang['Author'] = "ãÄáİ";
$lang['Time'] = "æŞÊ";
$lang['Hours'] = "ÓÇÚÉ";
$lang['Message'] = "ÑÓÇáÉ";

$lang['1_Day'] = "1 íæã";
$lang['7_Days'] = "7 ÇíÇã";
$lang['2_Weeks'] = "ÇÓÈæÚíä";
$lang['1_Month'] = "ÔåÑ";
$lang['3_Months'] = "3 ÇÔåÑ";
$lang['6_Months'] = "6 ÇÔåÑ";
$lang['1_Year'] = "ÓäÉ";

$lang['Go'] = "ÇäÊŞá";
$lang['Jump_to'] = "ÇäÊŞá Çáì";
$lang['Submit'] = "ŞÏã";
$lang['Reset'] = "ÇÚÇÏÉ";
$lang['Cancel'] = "ÊÑÇÌÚ";
$lang['Preview'] = "ÇÓÊÚÑÖ";
$lang['Confirm'] = "ÇßÏ";
$lang['Spellcheck'] = "ÊÏŞíŞ ÇãáÇÆí";
$lang['Yes'] = "äÚã";
$lang['No'] = "áÇ";
$lang['Enabled'] = "äÔØ";
$lang['Disabled'] = "ãÚØá";
$lang['Error'] = "ÎØÃ";

$lang['Next'] = "ÇáÊÇáí";
$lang['Previous'] = "ÇáÓÇÈŞ";
$lang['Goto_page'] = "ÇäÊŞá Çáì ÕİÍÉ";
$lang['Joined'] = "ÔÇÑßÊ";
$lang['IP_Address'] = "ÑŞã ÇáÇäÊÑäÊ";

$lang['Select_forum'] = "ÇÎÊÑ ãäÊÏì";
$lang['View_latest_post'] = "ÇØáÚ Úáì ÇÎÑ äÔÑÉ";
$lang['View_newest_post'] = "ÇØáÚ Úáì ÇÌÏÏ äÔÑÉ";
$lang['Page_of'] = "ÕİÍÉ <b>%d</b> ãä <b>%d</b>"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "ICQ ÑŞã";
$lang['AIM'] = "AIM ÚäæÇä";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "%s İåÑÓ ÇáãäÊÏì";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "ÇäÔÑ ãæÖæÚ ÌÏíÏ";
$lang['Reply_to_topic'] = "ÑÏ Úáì ãæÖæÚ";
$lang['Reply_with_quote'] = "ÑÏ ãÚ ÇÔÇÑÉ Çáì ÇáãæÖæÚ";

$lang['Click_return_topic'] = "ÇÖÛØ %såäÇ%s ááÑÌæÚ Çáì ÇáãæÖæÚ"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "ÇÖÛØ %såäÇ%s ááãÍÇæáÉ ãÑÉ ÇÎÑì";
$lang['Click_return_forum'] = "ÇÖÛØ %såäÇ%s ááÑÌæÚ Çáì ÇáãäÊÏì";
$lang['Click_view_message'] = "ÇÖÛØ %såäÇ%s áÇÓÊÚÑÇÖ ÇáÑÓÇáÉ";
$lang['Click_return_modcp'] = "ÇÖÛØ %såäÇ%s ááÑÌæÚ Çáì áæÍÉ ÊÍßã ÑÆíÓ ÇáãäÊÏì";
$lang['Click_return_group'] = "ÇÖÛØ %såäÇ%s ááÑÌæÚ Çáì ãÚáæãÇÊ ÇáãÌãæÚÉ";

$lang['Admin_panel'] = "ÇáÇäÊŞÇá Çáì áæÍÉ ÇáÊÍßã ÇáÑÆíÓíÉ";

$lang['Board_disable'] = "äÃÓİ Çä ÇáãäÊÏì ãÚØá ÇáÂä¡ ÇÑÌæ ÇáÑÌæÚ İí æŞÊ áÇÍŞ";


//
// Global Header strings
//
$lang['Registered_users'] = "ãÔÊÑßíä ãÓÌáíä:";
$lang['Online_users_zero_total'] = "ßßá åäÇß <b>0</b> ãÓÊÎÏã Úáì ÇáÎØ :: ";
$lang['Online_users_total'] = "ßßá åäÇß <b>%d</b> ãÓÊÎÏãíä Úáì ÇáÎØ :: ";
$lang['Online_user_total'] = "ßßá åäÇß <b>%d</b> ãÓÊÎÏã Úáì ÇáÎØ :: ";
$lang['Reg_users_zero_total'] = "0 ãÔÊÑß, ";
$lang['Reg_users_total'] = "%d ãÔÊÑßíä, ";
$lang['Reg_user_total'] = "%d ãÔÊÑß, ";
$lang['Hidden_users_zero_total'] = "0 ãÎÊİí æ ";
$lang['Hidden_user_total'] = "%d ãÎÊİí æ ";
$lang['Hidden_users_total'] = "%d ãÎÊİíä æ ";
$lang['Guest_users_zero_total'] = "0 ÒÇÆÑ";
$lang['Guest_users_total'] = "%d ÒæÇÑ";
$lang['Guest_user_total'] = "%d ÒÇÆÑ";
$lang['Record_online_users'] = "ÇßÈÑ ÚÏÏ ãä ÇáãÓÊÎÏãä ÊæÇÌÏæÇ İí äİÓ ÇáæŞÊ ßÇäæÇ <b>%s</b> İí %s"; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = "%sãÊÍßã ÑÆíÓí%s";
$lang['Mod_online_color'] = "%sÑÆíÓ ãäÊÏì%s";

$lang['You_last_visit'] = "ÂÎÑ ÒíÇÑÉ áß İí %s"; // %s replaced by date/time
$lang['Current_time'] = "ÇáæŞÊ ÇáÂä åæ %s"; // %s replaced by time

$lang['Search_new'] = "ÇÓÊÚÑÖ ÇáãæÇÖíÚ ãäĞ ÂÎÑ ÒíÇÑÉ";
$lang['Search_your_posts'] = "ÇÓÊÚÑÖ ÇáãæÇÖíÚ";
$lang['Search_unanswered'] = "ÇáãæÇÖíÚ ÇáÊí áã íÑÏ ÚáíåÇ";

$lang['Register'] = "ÔÇÑß";
$lang['Profile'] = "äÈĞÉ Úä";
$lang['Edit_profile'] = "ÊÚÏíá ÇáäÈĞÉ ÇáÔÎÕíÉ";
$lang['Search'] = "ÇÈÍÜË";
$lang['Memberlist'] = "ŞÇÆãÉ ÇáÇÚÖÇÁ";
$lang['FAQ'] = "Ó æ Ì";
$lang['BBCode_guide'] = "BBCode Ïáíá";
$lang['Usergroups'] = "ÇáãÌãæÚÇÊ";
$lang['Last_Post'] = "ÂÎÑ ÇÑÓÇá";
$lang['Moderator'] = "ÑÆíÓ ãäÊÏì";
$lang['Moderators'] = "ÑÆÓÇÁ ãäÊÏì";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "ãÔÇÑßíäÇ ŞÏãæÇ ÚÏÏ <b>0</b> ãæÖæÚ"; // Number of posts
$lang['Posted_articles_total'] = "ãÔÇÑßíäÇ ŞÏãæÇ ÚÏÏ <b>%d</b> ãæÖæÚ"; // Number of posts
$lang['Posted_article_total'] = "ãÔÇÑßíäÇ ŞÏãæÇ ÚÏÏ <b>%d</b> ãæÖæÚ"; // Number of posts
$lang['Registered_users_zero_total'] = "áÏíäÇ ÚÏÏ <b>0</b> ãÔÇÑß"; // # registered users
$lang['Registered_users_total'] = "áÏíäÇ ÚÏÏ <b>%d</b> ãÔÇÑßíä ãÓÌáíä"; // # registered users
$lang['Registered_user_total'] = "áÏíäÇ ÚÏÏ <b>%d</b> ãÔÇÑßíä ãÓÌáíä"; // # registered users
$lang['Newest_user'] = "ÂÎÑ ãÔÇÑß ãÓÌá åæ <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "áÇ ÊæÌÏ ãæÇÖíÚ ÌÏíÏ ãäĞ ÂÎÑ ÒíÇÑÉ";
$lang['No_new_posts'] = "áÇ ãæÇÖíÚ ÌÏíÏÉ";
$lang['New_posts'] = "ãæÇÖíÚ ÌÏíÏÉ";
$lang['New_post'] = "ãæÖæÚ ÌÏíÏ";
$lang['No_new_posts_hot'] = "áÇ ãæÇÖíÚ ÌÏíÏÉ [ ÔÚÈí ]";
$lang['New_posts_hot'] = "ãæÇÖíÚ ÌÏíÏÉ [ ÔÚÈí ]";
$lang['No_new_posts_locked'] = "áÇ ãæÇÖíÚ ÌÏíÏÉ [ ãÛáŞ ]";
$lang['New_posts_locked'] = "ãæÇÖíÚ ÌÏíÏÉ [ ãÛáŞ ]";
$lang['Forum_is_locked'] = "ÇáãäÊÏì ãÛáŞ";


//
// Login
//
$lang['Enter_password'] = "ÇáÑÌÇÁ ÇÏÎÇá ÇÓã ÇáÇÔÊÑÇß æßáãÉ ÇáÓÑ ááÏÎæá";
$lang['Login'] = "ÏÎæá";
$lang['Logout'] = "ÎÑæÌ";

$lang['Forgotten_password'] = "áŞÏ äÓíÊ ßáãÉ ÇáÓÑ";

$lang['Log_me_in'] = "ÇÏÎáäí ÈÔßá Âáí ÚäÏ ÒíÇÑÊí ãÑÉ ÇÎÑì";

$lang['Error_login'] = "áŞÏ ÇÏÎáÊ ÈíÇäÇÊ ÎÇØÆÉ áÇÓã ÇáãÔÊÑß Ãæ ßáãÉ ÇáÓÑ";


//
// Index page
//
$lang['Index'] = "İåÑÓ";
$lang['No_Posts'] = "áÇ ãæÇÖíÚ";
$lang['No_forums'] = "áÇ ÊæÌÏ ãäÊÏíÇÊ åäÇ";

$lang['Private_Message'] = "ÑÓÇáÉ ÎÇÕÉ";
$lang['Private_Messages'] = "ÑÓÇÆá ÎÇÕÉ";
$lang['Who_is_Online'] = "ãä Úáì ÇáÎØ";

$lang['Mark_all_forums'] = "ÇÚÊÈÑäí ŞÑÃÊ ÌãíÚ ÇáãæÇÖíÚ";
$lang['Forums_marked_read'] = "áŞÏ Êã ÇÚÊÈÇÑ ÌãíÚ ÇáãæÇÖíÚ ãŞÑæÁÉ";


//
// Viewforum
//
$lang['View_forum'] = "ÇÓÊÚÑÖ ãäÊÏì";

$lang['Forum_not_exist'] = "ÇáãäÊÏì ÇáãÎÊÇÑ ÛíÑ ãæÌæÏ";
$lang['Reached_on_error'] = "áŞÏ æÕáÊ åäÇ ÈÇáÎØÃ";

$lang['Display_topics'] = "ÇÓÊÚÑÖ ãæÇÖíÚ ÓÇÈŞÉ ãä";
$lang['All_Topics'] = "ÌãíÚ ÇáãæÇÖíÚ";

$lang['Topic_Announcement'] = "<b>ÇÚÜÜÜÜáÇä:</b>";
$lang['Topic_Sticky'] = "<b>áÇÕŞ:</b>";
$lang['Topic_Moved'] = "<b>ÇäÊŞá:</b>";
$lang['Topic_Poll'] = "<b>[ ÇÓÊİÊÇÁ ]</b>";

$lang['Mark_all_topics'] = "ÇÚÊÈÑ ÌãíÚ ÇáãæÇÖíÚ ŞÑÃÊ";
$lang['Topics_marked_read'] = "ÌãíÚ ãæÖíÚ åĞÇ ÇáãäÊÏì ŞÏ ÇÚÊÈÑÊ ãŞÑæÁÉ";

$lang['Rules_post_can'] = "<b>ÊÓÊØíÚ</b> æÖÚ ãæÇÖíÚ ÌÏíÏÉ İí åĞÇ ÇáãäÊÏì";
$lang['Rules_post_cannot'] = "<b>áÇÊÓÊØíÚ</b> æÖÚ ãæÇÖíÚ ÌÏíÏÉ İí åĞÇ ÇáãäÊÏì";
$lang['Rules_reply_can'] = "<b>ÊÓÊØíÚ</b> ÇáÑÏ Úáì ÇáãæÇÖíÚ İí åĞÇ ÇáãäÊÏì";
$lang['Rules_reply_cannot'] = "<b>áÇÊÓÊØíÚ</b> ÇáÑÏ Úáì ÇáãæÇÖíÚ İí åĞÇ ÇáãäÊÏì";
$lang['Rules_edit_can'] = "<b>ÊÓÊØíÚ</b> ÊÚÏíá ãæÇÖíÚß İí åĞÇ ÇáãäÊÏì";
$lang['Rules_edit_cannot'] = "<b>áÇ ÊÓÊØíÚ</b> ÊÚÏíá ãæÇÖíÚß İí åĞÇ ÇáãäÊÏì";
$lang['Rules_delete_can'] = "<b>ÊÓÊØíÚ</b> ÇáÛÇÁ ãæÇÖíÚß İí åĞÇ ÇáãäÊÏì";
$lang['Rules_delete_cannot'] = "<b>áÇÊÓÊØíÚ</b> ÇáÛÇÁ ãæÇÖíÚß İí åĞÇ ÇáãäÊÏì";
$lang['Rules_vote_can'] = "<b>ÊÓÊØíÚ</b> ÇáÊÕæíÊ İí åĞÇ ÇáãäÊÏì";
$lang['Rules_vote_cannot'] = "<b>áÇÊÓÊØíÚ</b> ÇáÊÕæíÊ İí åĞÇ ÇáãäÊÏì";
$lang['Rules_moderate'] = "<b>ÊÓÊØíÚ</b> %sÑÆÇÓÉ åĞÇ ÇáãäÊÏì%s"; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = "áÇ ãæÇÖíÚ İí åĞÇ ÇáãäÊÏì<br />ÇÖÛØ Úáì <b>ÇÑÓá ãæÖæÚ ÌÏíÏ</b> áÇÑÓÇá ãæÖæÚ ÌÏíÏ ááãäÊÏì";


//
// Viewtopic
//
$lang['View_topic'] = "View topic";

$lang['Guest'] = "ÒÇÆÑ";
$lang['Post_subject'] = "ãæÖæÚ ÇáÑÓÇáÉ";
$lang['View_next_topic'] = "ÇÓÊÚÑÖ ÇáãæÖæÚ ÇáÊÇáí";
$lang['View_previous_topic'] = "ÇÓÊÚÑÖ ÇáãæÖæÚ ÇáÓÇÈŞ";
$lang['Submit_vote'] = "ÇÑÓá ÊÕæíÊ";
$lang['View_results'] = "ÇÓÊÚÑÖ ÇáäÊÇÆÌ";

$lang['No_newer_topics'] = "áÇ ãæÇÖíÚ ÌÏíÏÉ İí ÇáãäÊÏì";
$lang['No_older_topics'] = "áÇ ãæÇÖíÚ ŞÏíãÉ İí ÇáãäÊÏì";
$lang['Topic_post_not_exist'] = "ÇáãæÖæÚ ÇáãØáæÈ ÛíÑ ãæÌæÏ";
$lang['No_posts_topic'] = "áÇ ÑÓÇÆá áåĞÇ ÇáãæÖæÚ";

$lang['Display_posts'] = "ÇÓÊÚÑÖ ãæÇÖíÚ ÓÇÈŞÉ";
$lang['All_Posts'] = "ÌãíÚ ÇáãæÇÖíÚ";
$lang['Newest_First'] = "ÇáÌÏíÏ ÃæáÇ";
$lang['Oldest_First'] = "ÇáŞÏíã ÃæáÇ";

$lang['Back_to_top'] = "ÇáÑÌæÚ Çáì ÇáãŞÏãÉ";

$lang['Read_profile'] = "ÇÓÊÚÑÖ äÈĞÉ Úä ÇáãÓÊÎÏãíä"; 
$lang['Send_email'] = "ÇÈÚË ÑÓÇáÉ Çáì ÇáãÔÊÑß";
$lang['Visit_website'] = "ÇäÊŞá Çáì ÕİÍÉ ÇáãÑÓá";
$lang['ICQ_status'] = "ICQ æÖÚ";
$lang['Edit_delete_post'] = "ÊÚÏíá Ãæ ÇáÛÇÁ ÇáÑÏ";
$lang['View_IP'] = "ÇÓÊÚÑÖ IP ÇáãÑÓá";
$lang['Delete_post'] = "ÅáÛ ÇáÑÏ";

$lang['wrote'] = "ßÊÈ"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "ÇŞÊÈÇÓ"; // comes before bbcode quote output.
$lang['Code'] = "ÈÑäÇãÌ"; // comes before bbcode code output.

$lang['Edited_time_total'] = "ÚÏá ÓÇÈŞÇ ãä ŞÈá %s İí %s, ÚÏá %d ãÑÉ"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "ÚÏá ÓÇÈŞÇ ãä ŞÈá %s İí %s, ÚÏá %d ãÑÇÊ"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "ÇŞİá åĞÇ ÇáãæÖæÚ";
$lang['Unlock_topic'] = "ÇİÊÍ åĞÇ ÇáãæÖæÚ";
$lang['Move_topic'] = "ÇäŞá åĞÇ ÇáãæÖæÚ";
$lang['Delete_topic'] = "ÇáÛ åĞÇ ÇáãæÖæÚ";
$lang['Split_topic'] = "ÇŞÓã åĞÇ ÇáãæÖæÚ";

$lang['Stop_watching_topic'] = "ÊæŞİ Úä ãÊÇÈÚÉ åĞÇ ÇáãæÖæÚ";
$lang['Start_watching_topic'] = "ÊÇÈÚ ÑÏæÏ Úáì åĞÇ ÇáãæÖæÚ";
$lang['No_longer_watching'] = "áŞÏ ÊæŞİÊ Úä ãÊÇÈÚÉ åĞÇ ÇáãæÖæÚ";
$lang['You_are_watching'] = "ÇäÊ ÇáÂä ÊÊÇÈÚ åĞÇ ÇáãæÖæÚ";


//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "ÌÓÜã ÇáÑÓÇáÉ";
$lang['Topic_review'] = "ãÑÇÌÚÉ ÇáãæÖæÚ";

$lang['No_post_mode'] = "áã ÊÍÏÏ ØÑíŞÉ ÇáÑÏ"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = "ÇÑÓá ãæÖæÚ ÌÏíÏ";
$lang['Post_a_reply'] = "ÇÑÓá ÑÏ";
$lang['Post_topic_as'] = "ÇÑÓá ãæÖæÚ ßÜ";
$lang['Edit_Post'] = "ÊÚÏíá ÇáÑÏ";
$lang['Options'] = "ÎíÇÑÇÊ";

$lang['Post_Announcement'] = "ÇÚáÇä";
$lang['Post_Sticky'] = "ãáÕŞ";
$lang['Post_Normal'] = "ÚÇÏí";

$lang['Confirm_delete'] = "åá ÇäÊ ãÊÃßÏ ãä ÇáÛÇÁ ÇáãæÖæÚ?";
$lang['Confirm_delete_poll'] = "åá ÃäÊ ãÊÃßÏ ãä ÇáÛÇÁ ÇáÊÕæíÊ?";

$lang['Flood_Error'] = "áÇ ÊÓÊØíÚ ÇÑÓÇá ÑÏ Ãæ ãæÖæÚ ÈåĞå ÇáÓÑÚÉ ÈÚÏ ÇáÇÑÓÇá ÇáÓÇÈŞ, ÇáÑÌÇÁ ÇáãÍÇæáÉ ÈÚÏ æŞÊ";
$lang['Empty_subject'] = "Úáíß ĞßÑ ÚäæÇä ÇáãæÖæÚ ÚäÏ æÖÚ ãæÖæÚ ÌÏíÏ";
$lang['Empty_message'] = "Úáíß ÇÏÎÇá äÕ ááÑÓÇáÉ ÚäÏ ÇÏÎÇá ãæÖæÚ ÌÏíÏ";
$lang['Forum_locked'] = "åĞÇ ÇáãäÊÏì ãÛáŞ İáÇ ÊÓÊØíÚ äÔÑ¡ ÇáÑÏ Úáì¡ Ãæ ÊÚÏíá ÇáãæÇÖíÚ";
$lang['Topic_locked'] = "åĞÇ ÇáãæÖæÚ ãÛáŞ İáÇ ÊÓÊØíÚ ÇáÑÏ Úáì Ãæ ÊÚÏíá ÇáãæÖæÚ";
$lang['No_post_id'] = "Úáíß ÇÎÊíÇÑ ãæÖæÚ ááÊÚÏíá";
$lang['No_topic_id'] = "Úáíß ÇÎÊíÇÑ ãæÖæÚ ááÑÏ Úáíå";
$lang['No_valid_mode'] = "ÊÓÊØíÚ İŞØ ÇáÑÏ Úáì¡ Ãæ ÊÚÏíá æäÔÑ ÇáãæÇÖíÚ, ÍÇæá ãÑÉ ÇÎÑì";
$lang['No_such_post'] = "åĞÇ ÇáÑÏ ÛíÑ ãæÌæÏ, ÃÑÌæ ÇáÚæÏÉ æÇáÇÎÊíÇÑ ãÑÉ ÇÎÑì";
$lang['Edit_own_posts'] = "äÃÓİ áßäß ÊÓÊØíÚ ÊÚÏíá äÔÑÇÊß İŞØ";
$lang['Delete_own_posts'] = "äÃÓİ áßäß ÊÓÊØíÚ ÅáÛÇÁ äÔÑÇÊß İŞØ";
$lang['Cannot_delete_replied'] = "äÃÓİ áßä áÇ ÊÓÊØíÚ ÅáÛÇÁ äÔÑÉ ŞÏ Êã ÇáÑÏ ÚáíåÇ";
$lang['Cannot_delete_poll'] = "äÃÓİ áßäß áÇ ÊÓÊØíÚ ÅáÛÇÁ ÊÕæíÊ İÚÇá";
$lang['Empty_poll_title'] = "Úáíß ÇÏÎÇá ÚäæÇä áÊÕæíÊß";
$lang['To_few_poll_options'] = "Úáíß ÇÏÎÇá ÇÎÊíÇÑíä áÊÕæíÊß";
$lang['To_many_poll_options'] = "Çäß ÊÍÇæá ÇÏÎÇá ÇáßËíÑ ãä ÇáÇÎÊíÇÑÇÊ ááÊÕæíÊ";
$lang['Post_has_no_poll'] = "åĞå ÇáäÔÑÉ áÇ ÊÕæíÊ áåÇ";

$lang['Add_poll'] = "ÇÖİ ÊÕæíÊ";
$lang['Add_poll_explain'] = "ÅĞÇ áã ÊÑÏ æÖÚ ÊÕæíÊ áÑÏß ÇÊÑß ÇáãÑÈÚÇÊ/ÇáÍŞæá İÇÑÛÉ";
$lang['Poll_question'] = "ÓÄÇá ÇáÊÕæíÊ";
$lang['Poll_option'] = "ÇÌæÈÉ ÇáÊÕæíÊ";
$lang['Add_option'] = "ÇÖİ ÇÌÇÈÉ";
$lang['Update'] = "ÊÍÏíË";
$lang['Delete'] = "ÇáÛÇÁ";
$lang['Poll_for'] = "ÕæÊ áÜ";
$lang['Days'] = "íæã"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ ÇÏÎá 0 Ãæ ÇÊÑßå İÇÑÛ áÊßæíä ÊÕæíÊ áÇ íäÊåí ]";
$lang['Delete_poll'] = "ÅáÛÇÁ ÊÕæíÊ";

$lang['Disable_HTML_post'] = "ÚØá HTML İí åĞÇ ÇáÇÑÓÇá";
$lang['Disable_BBCode_post'] = "ÚØá BBCode İí åĞÇ ÇáÇÑÓÇá";
$lang['Disable_Smilies_post'] = "ÚØá ÇáæÌæå ÇáÖÇÍßÉ İí åĞÇ ÇáÇÑÓÇá";

$lang['HTML_is_ON'] = "HTML <u>äÔØ</u>";
$lang['HTML_is_OFF'] = "HTML is <u>ãÚØá</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s <u>äÔØ</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s <u>ãÚØá</u>";
$lang['Smilies_are_ON'] = "Smilies <u>äÔØ</u>";
$lang['Smilies_are_OFF'] = "Smilies <u>ãÚØá</u>";

$lang['Attach_signature'] = "ÇÑİŞ ÇáÊæŞíÚ (ÊÓÊØíÚ ÊÛííÑ ÇáÊæŞíÚ İí ÇÚÏÇÏÇÊ ÇáãÔÊÑß)";
$lang['Notify'] = "ÇÎÈÑäí ÈæÌæÏ ÑÏæÏ";
$lang['Delete_post'] = "ÇãÓÍ åĞÇ ÇáÇÑÓÇá";

$lang['Stored'] = "Êã ÇÑÓÇá ÇáÑÓÇáÉ ÈäÌÇÍ";
$lang['Deleted'] = "Êã ãÓÍ ÑÓÇáÊß ÈäÌÇÍ";
$lang['Poll_delete'] = "Êã ãÓÍ ÊÕæíÊß ÈäÌÇÍ";
$lang['Vote_cast'] = "Êã ÊÓÌíá ÊÕæíÊß";

$lang['Topic_reply_notification'] = "ÇáÊĞßíÑ ÈÇáÑÏæÏ Úáì ÇáãæÇÖíÚ";

$lang['bbcode_b_help'] = "äÕ Óãíß: [b]äÕ[/b]  (alt+b)";
$lang['bbcode_i_help'] = "äÕ ãÇÆá: [i]äÕ[/i]  (alt+i)";
$lang['bbcode_u_help'] = "äÕ ãÓØÑ: [u]äÕ[/u]  (alt+u)";
$lang['bbcode_q_help'] = "äÕ ãŞÊÈÓ: [quote]äÕ[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "äÕ ÈÑãÌí: [code]ÈÑäÇãÌ[/code]  (alt+c)";
$lang['bbcode_l_help'] = "ŞÇÆãÉ: [list]äÕ[/list] (alt+l)";
$lang['bbcode_o_help'] = "ŞÇÆãÉ ÚÏÏíÉ: [list=]äÕ[/list]  (alt+o)";
$lang['bbcode_p_help'] = "ÇÖİ ÕæÑÉ: [img]http://image_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "ÇÖİ ãæŞÚ ÕİÍÉ: [url]http://url[/url] Ãæ [url=http://url]äÕ ÚäæÇä ÕİÍÉ[/url]  (alt+w)";
$lang['bbcode_a_help'] = "ÇÛáŞ ÌãíÚ ÇÔÇÑÇÊ bbCode ÇáãİÊæÍÉ";
$lang['bbcode_s_help'] = "áæä ÇáÎØ: [color=red]äÕ[/color]  Tip: you can also use color=#FF0000";
$lang['bbcode_f_help'] = "ÍÌã ÇáÎØ: [size=x-small]ÎØ ÕÛíÑ[/size]";

$lang['Emoticons'] = "ÇäİÚÇáÒ";
$lang['More_emoticons'] = "ÇØáÇÚ Úáì ÇáãÒíÏ ãä ÇäİÚÇáÒ";

$lang['Font_color'] = "áæä ÇáÍÑİ";
$lang['color_default'] = "ÇáØÈíÚí";
$lang['color_dark_red'] = "ÇÍãÑ ŞÇÊã";
$lang['color_red'] = "ÇÍãÑ";
$lang['color_orange'] = "ÈÑÊŞÇáí";
$lang['color_brown'] = "Èäí";
$lang['color_yellow'] = "ÇÕİÑ";
$lang['color_green'] = "ÇÎÖÑ";
$lang['color_olive'] = "ÒíÊæäí";
$lang['color_cyan'] = "ÇÒÑŞ ÓãÇæí";
$lang['color_blue'] = "ÇÒÑŞ";
$lang['color_dark_blue'] = "ÇÒÑŞ ŞÇÊã";
$lang['color_indigo'] = "Indigo";
$lang['color_violet'] = "ÈäİÓÌí";
$lang['color_white'] = "ÇÈíÖ";
$lang['color_black'] = "ÇÓæÏ";

$lang['Font_size'] = "ÍÌã ÇáÎØ";
$lang['font_tiny'] = "ÖÆíá";
$lang['font_small'] = "ÕÛíÑ";
$lang['font_normal'] = "ÚÇÏí";
$lang['font_large'] = "ßÈíÑ";
$lang['font_huge'] = "ÖÎã";

$lang['Close_Tags'] = "ÇÛáŞ ÇáÇÔÇÑÇÊ";
$lang['Styles_tip'] = "ãáÍæÙÉ: ÊÓÊØíÚ ÊØÈíŞ ÇáÊäÓíŞ ÈÓÑÚÉ Úáì ÇáäÕæÕ";


//
// Private Messaging
//
$lang['Private_Messaging'] = "ÑÓÇáÉ ÎÇÕÉ";

$lang['Login_check_pm'] = "ÇÏÎá áŞÑÇÁÉ ÑÓÇÆáß ÇáÎÇÕÉ";
$lang['New_pms'] = "áÏíß %d ÑÓÇÆá ÎÇÕÉ"; // You have 2 new messages
$lang['New_pm'] = "áÏíß %d ÑÓÇáÉ ÎÇÕÉ"; // You have 1 new message
$lang['No_new_pm'] = "áíÓ áÏíß ÇíÉ ÑÓÇáÉ ÎÇÕÉ";
$lang['Unread_pms'] = "áÏíß %d ÑÓÇáÉ ÛíÑ ãŞÑæÉ";
$lang['Unread_pm'] = "áÏíß %d ÑÓÇÆá ÛíÑ ãŞÑæÁÉ";
$lang['No_unread_pm'] = "áíÓ áÏíß ÑÓÇÆá ÛíÑ ãŞÑæÁÉ";
$lang['You_new_pm'] = "áÏíß ÑÓÇáÉ ÎÇÕÉ ÊäÊÙÑß İí ÕäÏæŞ ÈÑíÏß";
$lang['You_new_pms'] = "áÏíß ÚÏÉ ÑÓÇÆá ÊäÊÙÑß İí ÕäÏæŞ ÈÑíÏß";
$lang['You_no_new_pm'] = "áÇ ÊæÌÏ áÏíß ÑÓÇÆá ÎÇÕÉ ÊäÊÙÑß";

$lang['Inbox'] = "ÕäÏæŞ ÇáæÇÑÏ";
$lang['Outbox'] = "ÕäÏæŞ ÇáÕÇÏÑ";
$lang['Savebox'] = "ÕäÏæŞ ÇáÍİÙ";
$lang['Sentbox'] = "ÕäÏæŞ ÇáãÑÓá";
$lang['Flag'] = "Úáã";
$lang['Subject'] = "ãæÖæÚ";
$lang['From'] = "ãä";
$lang['To'] = "Åáì";
$lang['Date'] = "ÊÇÑíÎ";
$lang['Mark'] = "ÚáÇãÉ";
$lang['Sent'] = "ãÑÓá";
$lang['Saved'] = "ãÍİæÙ";
$lang['Delete_marked'] = "ÃáÛ ÇáãÄÔÑ Úáíå";
$lang['Delete_all'] = "ÃáÛ Çáßá";
$lang['Save_marked'] = "ÇÍİÙ ÇáãÄÔÑ Úáíå"; 
$lang['Save_message'] = "ÇÍİÙ ÇáÑÓÇáÉ";
$lang['Delete_message'] = "ÇáÛ ÇáÑÓÇáÉ";

$lang['Display_messages'] = "ÇÓÊÚÑÖ ÇáÑÓÇÆá ãäĞ"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "ÌãíÚ ÇáÑÓÇÆá";

$lang['No_messages_folder'] = "áíÓ áÏíß ÑÓÇÆá İí åĞÇ ÇáãÌáÏ";

$lang['PM_disabled'] = "ÇáÑÓÇÆá ÇáÎÇÕÉ ŞÏ ÇÛáŞÊ áåĞÇ ÇáãäÊÏì";
$lang['Cannot_send_privmsg'] = "äÃÓİ áßä ÇáãÏíÑ ŞÏ ãäÚß ãä ÇÑÓÇá ÑÓÇÆá ÎÇÕÉ";
$lang['No_to_user'] = "Úáíß ĞßÑ ÇÓã ÇáãÔÊÑß áÇÑÓÇá ÇáÑÓÇáÉ";
$lang['No_such_user'] = "äÃÓİ áßä áÇ íæÌÏ ãÔÊÑß ÈåĞÇ ÇáÇÓã";

$lang['Message_sent'] = "áŞÏ ÇÑÓáÊ ÑÓÇáÊß";

$lang['Click_return_inbox'] = "ÇÖÛØ %såäÇ%s ááÑÌæÚ Çáì ÕäÏæŞ ÇáæÇÑÏ";
$lang['Click_return_index'] = "ÇÖÛØ %såäÇ%s ááÑÌæÚ Çáì ÇáİåÑÓ";

$lang['Send_a_new_message'] = "ÇÑÓá ÑÓÇáÉ ÎÇÕÉ ÌÏíÏÉ";
$lang['Send_a_reply'] = "ÑÏ Úáì ÑÓÇáÉ ÎÇÕÉ";
$lang['Edit_message'] = "ÊÚÏíá ÑÓÇáÉ ÎÇÕÉ";

$lang['Notification_subject'] = "áŞÏ æÕáÊ ÑÓÇáÉ ÎÇÕÉ";

$lang['Find_username'] = "ÇÈÍË Úä ÇÓã ãÔÊÑß";
$lang['Find'] = "ÇÈÍË";
$lang['No_match'] = "áã íÊã ÇíÌÇÏå";

$lang['No_post_id'] = "áã ÊÍÏÏ ÑŞã ÇáÑÓÇáÉ";
$lang['No_such_folder'] = "áÇ íæÌÏ ãÌáÏ ÈåĞÇ ÇáÇÓã";
$lang['No_folder'] = "áã ÊÍÏÏ ÇáãÌáÏ";

$lang['Mark_all'] = "ÖÚ ÇÔÇÑÉ Úáì Çáßá";
$lang['Unmark_all'] = "ÇÒá ÌãíÚ ÇáÇÔÇÑÇÊ";

$lang['Confirm_delete_pm'] = "åá ÇäÊ ãÊÃßÏ ãä ÇáÛÇÁ åĞå ÇáÑÓÇáÉ?";
$lang['Confirm_delete_pms'] = "åá ÇäÊ ãÊÃßÏ ãä ÇáÛÇÁ åĞå ÇáÑÓÇÆá?";

$lang['Inbox_size'] = "ÕäÏæŞ ÈÑíÏß ÇáæÇÑÏ ãáíÁ ÈäÓÈÉ %d%%"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "ÕäÏæŞ ÈÑíÏß ÇáÕÇÏÑ ãáíÁ ÈäÓÈÉ %d%%"; 
$lang['Savebox_size'] = "ÕäÏæŞ ÇáÍİÙ ãáíÁ ÈäÓÈÉ %d%%"; 

$lang['Click_view_privmsg'] = "ÇÖÛØ %såäÇ%s ááÇäÊŞÇá Çáì ÕäÏæŞ ÇáæÇÑÏ";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "ÇáÇØáÇÚ Úáì ÇáÈíÇäÇÊ ÇáÔÎÕíÉ áÜ :: %s"; // %s is username 
$lang['About_user'] = "ßá ãÇ åäÇáß Úä %s"; // %s is username

$lang['Preferences'] = "ÊİÖíáÇÊ";
$lang['Items_required'] = "ÇáÇÌÒÇÁ ÇáãÔÇÑ ÇáíåÇ ÈÜ * ãØáæÈÉ ÇáÇ ÇĞÇ ĞßÑ ÛíÑ Ğáß";
$lang['Registration_info'] = "ãÚáæãÇÊ ÇáÊÓÌíá";
$lang['Profile_info'] = "ÇáãÚáæãÇÊ ÇáÔÎÕíÉ";
$lang['Profile_info_warn'] = "åĞå ÇáãÚáæãÇÊ Óæİ Êßæä ãÚáäÉ æÚÇãÉ";
$lang['Avatar_panel'] = "áæÍÉ ÊÍßã ÕæÑÉ ÇáÔÎÕíÉ";
$lang['Avatar_gallery'] = "ãÚÑÖ ÕæÑ ÇáÔÎÕíÇÊ";

$lang['Website'] = "ÕİÍÉ ÇáÇäÊÑäÊ";
$lang['Location'] = "ÇáãßÇä";
$lang['Contact'] = "ÇáÇÊÕÇá";
$lang['Email_address'] = "ÇáÈÑíÏ ÇáÇáßÊÑæäí";
$lang['Email'] = "ÇáÈÑíÏ ÇáÇáßÊÑæäí";
$lang['Send_private_message'] = "ÇÑÓá ÑÓÇáÉ ÎÇÕÉ";
$lang['Hidden_email'] = "[ ãÎÊÈíÁ ]";
$lang['Search_user_posts'] = "ÇÈÍË Úä ÇÓåÇãÇÊ áåĞÇ ÇáãÔÊÑß";
$lang['Interests'] = "ÇåÊãÇãÇÊ";
$lang['Occupation'] = "ÇáãåäÉ"; 
$lang['Poster_rank'] = "ÑÊÈÉ ÇáãÔÊÑß";

$lang['Total_posts'] = "ãÌãæÚ ÇáÇÓåÇãÇÊ";
$lang['User_post_pct_stats'] = "%.2f%% äÓÈÉ ãä Çáßá"; // 1.25% of total
$lang['User_post_day_stats'] = "%.2f ÇÓåÇãÇÊ ÈÇáíæã"; // 1.5 posts per day
$lang['Search_user_posts'] = "ÇæÌÏ ÌãíÚ ÇáÇÓåÇãÇÊ ãä ÇáãÔÊÑß %s"; // Find all posts by username

$lang['No_user_id_specified'] = "äÃÓİ áßä åĞÇ ÇáãÔÊÑß ÛíÑ ãæÌæÏ";
$lang['Wrong_Profile'] = "áÇ ÊÓÊØíÚ ÊÚÏíá ÇáäÈĞÉ ÇáÔÎÕíÉ æÇáÊí áíÓÊ áß.";
$lang['Sorry_banned_or_taken_email'] = "äÃÓİ áßä ÇáÈÑíÏ ÇáÇáßÊÑæäí ÇáãĞßæÑ ÇãÇ ŞÏ Êã ãäÚå, Ãæ ŞÏ ÇÚØí áãÔÊÑß ÂÎÑ Ãæ ÛíÑ ÕÍíÍ. ÇáÑÌÇÁ ÇÚØÇÁ ÈÑíÏ ÂÎÑ, Çä ßÇä åæ ÇáÂÎÑ ããäæÚ İÊÓÊØíÚ ÇáÇÊÕÇá ÈãÏíÑ ÇáãæŞÚ";
$lang['Only_one_avatar'] = "íÓãÍ ÈÊÍÏíÏ ÔÎÕíÉ æÇÍÏÉ İŞØ";
$lang['File_no_data'] = "Çáãáİ ÇáãÓÌá ÚÈÑ ÚäæÇä ÇáÇäÊÑäÊ áÇ íÍÊæí Úáì ÔíÁ";
$lang['No_connection_URL'] = "áã äÊãßä ÇáÇÊÕÇá ÈÚäæÇä ÇáÇäÊÑäÊ ÇáãÓÌá";
$lang['Incomplete_URL'] = "ÚäæÇä ÇáÇäÊÑäÊ ÇáãÓÌá ÛíÑ ßÇãá";
$lang['Wrong_remote_avatar_format'] = "ÚäæÇä ÇáÇäÊÑäÊ ÇáãÓÌá ááÔÎÕíÉ ÛíÑ ÕÍíÍ";
$lang['No_send_account_inactive'] = "äÃÓİ áßä ÇÔÊÑÇßß ãÚØá ÇáÂä . ÇáÑÌÇÁ ÇáÇÊÕÇá ÈÇáãÏíÑ ááãÓÇÚÏÉ";

$lang['Always_smile'] = "ÏÇÆãÇ ÔÛá ÇáæÌæå ÇáÖÇÍßÉ";
$lang['Always_html'] = "ÏÇÆãÇ ÔÛá ÈÜ HTML";
$lang['Always_bbcode'] = "ÏÇÆãÇ ÇÓãÍ ÈÜ BBCode";
$lang['Always_add_sig'] = "ÏÇÆãÇ ÇÑİŞ ÇáÊæŞíÚ";
$lang['Always_notify'] = "ÏÇÆãÇ ÇÎÈÑäí ÈÇáÑÏæÏ";
$lang['Always_notify_explain'] = "ÇÑÓá ÈÑíÏÇ ÇáßÊÑæäíÇ ÚäÏãÇ íÑÏ ãÔÊÑß İí ãæÖæÚ ŞÏ ÓÌáÊå. ÊÓÊØíÚ ÊÛííÑ Ğáß ÚäÏãÇ ÊÖÚ ÇäÊ ÑÏÇ";

$lang['Board_style'] = "äãØ ÇááæÍÉ";
$lang['Board_lang'] = "áÛÉ ÇáÕİÍÉ";
$lang['No_themes'] = "áÇ ÊæÌÏ ÓãÇÊ İí ŞÇÚÏÉ ÇáÈíÇäÇÊ";
$lang['Timezone'] = "ÎØ ÇáÒãä";
$lang['Date_format'] = "Ôßá ÇáÊÇÑíÎ";
$lang['Date_format_explain'] = "ÇááÛÉ ÇáãÓÊÚãáÉ ÔÈíå ÈÜ PHP <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> function";
$lang['Signature'] = "ÇáÊæŞíÚ";
$lang['Signature_explain'] = "åĞå ÇáİŞÑÉ íãßä ÇÖÇİÊåÇ áÃí ÇÓåÇãÇÊ ÊäÔÑåÇ. åäÇß %d ÍÑİ ßÍÏ ÇŞÕì";
$lang['Public_view_email'] = "ÏÇÆãÇ ÇÙåÑ ÈÑíÏí ÇáÇáßÊÑæäí";

$lang['Current_password'] = "ßáãÉ ÇáÓÑ ÇáÍÇáíÉ";
$lang['New_password'] = "ßáãÉ ÇáÓÑ ÇáÌÏíÏÉ";
$lang['Confirm_password'] = "ÊÃßíÏ ßáãÉ ÇáÓÑ";
$lang['password_if_changed'] = "ÇĞÇ ÇÑÏÊ ÇáÊÛííÑ Úáíß İŞØ ÊÒæíÏäÇ ÈßáãÉ ÇáÓÑ";
$lang['password_confirm_if_changed'] = "Úáíß ÊÒæíÏäÇ ÈÊÃßíÏ áßãÉ ÇáÓÑ ÇĞÇ Êã ÊÛííÑåÇ İí ÇáÇÚáì";

$lang['Avatar'] = "ÔÎÕíÉ";
$lang['Avatar_explain'] = "ÊÙåÑ ÕæÑÉ ãÇ ÇÓİá ÇÓãß İí ÇáäÔÑÉ. íãßä ÇÙåÇÑ ÕæÑÉ æÇÍÏÉ ááãÔÊÑß, ÚÑÖåÇ áÇ íÒíÏ Úä %d äŞØÉ, æÇÑÊİÇÚåÇ áÇ íÒíÏ Úä %d äŞØÉ æÍÌã Çáãáİ áÇ íÒíÏ Úä %dkB."; 
$lang['Upload_Avatar_file'] = "ÇÑÓá ÇáÕæÑÉ ãä ÌåÇÒß";
$lang['Upload_Avatar_URL'] = "ÇÓÍÈ ÕæÑÉ ÇáÔÎÕíÉ ãä ÇáãæŞÚ";
$lang['Upload_Avatar_URL_explain'] = "ÇÏÎá ÚäæÇä ÇáÇäÊÑäÊ ááÍÕæá Úáì ÇáÕæÑÉ ãäåÇ, Óæİ íÊã ÊÎÒíäåÇ İí åĞÇ ÇáãæŞÚ.";
$lang['Pick_local_Avatar'] = "ÇÎÊÑ ÇáÔÎÕíÉ ãä ãßÊÈÉ ÇáÕæÑ";
$lang['Link_remote_Avatar'] = "ÇæÕá ãÚ ÔÎÕíÉ ÎÇÑÌ ÇáãæŞÚ";
$lang['Link_remote_Avatar_explain'] = "ÇÏÎá ãæŞÚ ÇáÇäÊÑäÊ ááÍÕæá Úáì ÇáÕæÑÉ ãäå.";
$lang['Avatar_URL'] = "ãæŞÚ ÕæÑÉ ÇáÔÎÕíÉ İí ÇáÇäÊÑäÊ";
$lang['Select_from_gallery'] = "ÇÎÊÑ ÕæÑÉ ÇáÔÎÕíÉ ãä ãßÊÈÉ ÇáÕæÑ";
$lang['View_avatar_gallery'] = "ÇÙåÑ ãßÊÈÉ ÇáÕæÑ";

$lang['Select_avatar'] = "ÇÎÊÑ ÇáÔÎÕíÉ";
$lang['Return_profile'] = "ÊÑÇÌÚ Úáì ÇÎÊíÇÑ ÇáÔÎÕíÉ";
$lang['Select_category'] = "ÇÎÊÑ ÇáÊÕäíİ";

$lang['Delete_Image'] = "ÇáÛ ÇáÕæÑÉ";
$lang['Current_Image'] = "ÇáÕæÑÉ ÇáÍÇáíÉ";

$lang['Notify_on_privmsg'] = "ÇÎÈÑäí ÈæÕæá ÑÓÇáÉ ÔÎÕíÉ";
$lang['Popup_on_privmsg'] = "ÇİÊÍ äÇİĞÉ ãÓÊŞáÉ ÚäÏ æÌæÏ ÑÓÇáÉ ÔÎÕíÉ"; 
$lang['Popup_on_privmsg_explain'] = "ÈÚÖ ÇáŞæÇáÈ ŞÏ ÊİÊÍ äÇİĞÉ ãÓÊŞáÉ ááÇØáÇÚ Úáì ÇáÑÓÇÆá ÇáÎÇÕÉ"; 
$lang['Hide_user'] = "ÇÎİ ÍÇáÉ æÌæÏß İí ÇáãäÊÏì";

$lang['Profile_updated'] = "Êã ÊÚÏíá ÈíÇäÇÊ ÇáÔÎÕíÉ";
$lang['Profile_updated_inactive'] = "Êã ÊÚÏíá ÈíÇäÇÊ ÇáÔÎÕíÉ, áßäß ÛíÑÊ ÈíÇäÇÊ ãåãÉ áĞÇ İŞÏ Êã ÇíŞÇİ ÇÔÊÑÇßß. ÇÑÌÚ Çáì ÈÑíÏß ÇáÇáßÊÑæäí áÇäÊÙÇÑ ÑÓÇáÉ ÇáÊÔÛíá, Ãæ ÇĞÇ ßÇä ÇáÇÔÊÑÇß íÊã ÊÔÛíáå ãä ŞÈá ÇáãÏíÑ İÇäÊÙÑ ÇáãÏíÑ ÍÊì íŞæã ÈÊÔÛíáå";

$lang['Password_mismatch'] = "ßáãÇÊ ÇáÓÑ ÇáÊí Êã ÇÏÎÇáåÇ ÛíÑ ãÊØÇÈŞÉ";
$lang['Current_password_mismatch'] = "ßáãÉ ÇáÓÑ ÇáãÚØÇÉ áÇ ÊØÇÈŞ Êáß ÇáãÎÒäÉ İí ŞÇÚÏÉ ÇáÈíÇäÇÊ";
$lang['Invalid_username'] = "ÇÓã ÇáãÔÊÑß ÇáãÚØì ÇãÇ ãÃÎæĞ Ãæ ããäæÚ ãä ÇáÏÎæá, Ãæ íÍÊæí Úáì ÍÑæİ ããäæÚÉ ãËá \"";
$lang['Signature_too_long'] = "ÍÌã ÊæŞíÚß ßÈíÑ ÌÏÇ";
$lang['Fields_empty'] = "Úáíß ãáÁ ÇáÍŞæá/ÇáãÑÈÚÇÊ ÇáİÇÑÛÉ";
$lang['Avatar_filetype'] = "äæÚ ãáİ ÇáÔÎÕíÉ íÌÈ Çä íßæä  .jpg, .gif Ãæ .png";
$lang['Avatar_filesize'] = "íÌÈ Çä íßæä ÍÌã ãáİ ÕæÑÉ ÇáÔÎÕíÉ  %d kB"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "ÍÌã ãáİ ÕæÑÉ ÇáÔÎÕíÉ íÌÈ Çä íßæä ÈÇáÇÈÚÇÏ %d äŞØÉ ÈÇáÚÑÖ æ %d äŞØÉ ÈÇáÇÑÊİÇÚ"; 

$lang['Welcome_subject'] = "ÃåáÇ æÓåáÇ Èßã İí ãäÊÏì %s"; // Welcome to my.com forums
$lang['New_account_subject'] = "ãÔÊÑß ÌÏíÏ";
$lang['Account_activated_subject'] = "Êã ÊÔÛíá ÇáÇÔÊÑÇß";

$lang['Account_added'] = "äÔßÑß Úáì ÊÓÌíáß,Êã Êßæíä ÍÓÇÈß áÏíäÇ. ÊÓÊØíÚ ÇáÂä ÇÓÊÚãÇá ÇÓãß æßáãÉ ÇáÓÑ ááÏÎæá";
$lang['Account_inactive'] = "Êã Êßæíä ÇÔÊÑÇßß. áßä, åĞÇ ÇáãäÊÏì íÊØáÈ ÊÔÛíá ÇáÇÔÊÑÇß, Êã ÇÑÓÇá ãİÊÇÍ ÇáÊÔÛíá Çáì ÚäæÇä ÈÑíÏß ÇáĞí ÒæÏÊäÇ Èå. ÇáÑÌÇÁ ÇáÑÌæÚ Çáì ÈÑíÏß ÇáÇáÇßÊÑæäí áÇäÊÙÇÑ ÇáÑÓÇáÉ";
$lang['Account_inactive_admin'] = "Êã Êßæíä ÍÓÇÈß. áßä, åĞÇ ÇáãäÊÏì íÊØáÈ ÊÔÛíá ÍÓÇÈß ÈæÇÓØÉ ÇáãÏíÑ. Êã ÇÑÓÇá ÑÓÇáÉ áåã æÓæİ íÚáãæäß İæÑ ãæÇİŞÊåã Úáì ÇÔÊÑÇßß";
$lang['Account_active'] = "Êã ÊÔÛíá ÇÔÊÑÇßß. ÔßÑÇ Úáì ÊÓÌíáß";
$lang['Account_active_admin'] = "Êã ÊÔÛíá ÇÔÊÑÇßß";
$lang['Reactivate'] = "ÇÚÏ ÊÔÛíá ÇÔÊÑÇßß!";
$lang['COPPA'] = "áŞÏ Êã Êßæíä ÇÔÊÑÇßß¡ áßä áÊÃßíÏå Úáì ÇáÑÌæÚ Çáì ÈÑíÏß ÇáÇáßÊÑæäí ÇáÎÇÕ Èß áÇäÊÙÇÑ ÑÓÇáÉ ÇáÊÔÛíá.";

$lang['Registration'] = "ÔÑæØ ÊÔÛíá ÇáÇÔÊÑÇß";
$lang['Reg_agreement'] = "ÍíË Ãä ÇáãÏÑÇÁ æÑÃÓÇÁ ÇáãäÊÏì ÓíŞæãæä ÈÅáÛÇÁ æÇíŞÇİ Ãí ãæÇÏ ãÔßæß ÈåÇ, İÅäå ãä ÇáãÓÊÍíá ãÑÇŞÈÉ ÌãíÚ ÇáÑÓÇÆá. áĞÇ İÅä Úáãß ÈÃä ÌãíÚ ÇáÇÓåÇãÇÊ åí æÌåÉ äÙÑ ÇáäÇÔÑ íÚäí Ãä, ÇáãÏÑÇÁ æÑÄÓÇÁ ÇáãäÊÏì æÇáÕİÍÉ (ÚÏÇ ãÔÇÑßÊåã ÇáÔÎÕíÉ) áä íÍãáæÇ Ãí ãÓÄæáíÉ áĞáß.<br /><br />Ãä ÊæÇİŞ Ãäß áä ÊäÔÑ ãæÇÏ ãåíäÉ, İÇÍÔ, ÓæŞí, ÈÔßá ŞĞİ, ßÑÇåí, ãåÏÏ, ÌäÓí Ãæ Ãí äæÚ íÎÇáİ ÇáŞÇäæä ÇáãÊÈÚ. İÚá Ãí ããÇ ÓÈŞ Óæİ íÄÏí Çáì æŞİß æÇÒÇáÊß ÈÔßá ÏÇÆã ãä ÇáãäÊÏì (æÇÎÈÇÑ ãÒæÏ ÎÏãÉ ÇáÇäÊÑäÊ áÏíß). æÓæİ íÊã ÑÕÏ ÌãíÚ ÇÑŞÇã ÇáÇäÊÑäÊ áİÑÖ åĞå ÇáŞæÇäíä. ÇäÊ ÊæÇİŞ Ãä ãÏíÑ ÇáÕİÍÉ, æãÓÄæá ÇáãäÊÏì æãæÌåíä ÇáãäÊÏì áåã ÇáÍŞ ÈÇÒÇáÉ, ÊÚÏíá, äŞá Ãæ ÇÛáÇŞ Ãí ãæÖæÚ ÍÓÈ ÑÃíåã. æÇäÊ ßãÔÊÑß Ãæ ãÓÊÎÏã ÊæÇİŞ Ãä ÊÎÒä ÌãíÚ ÇáãÚáæãÇÊ ÇáãÏÎáÉ ÓÇÈŞÇ İí ŞÇÚÏÉ ÈíÇäÇÊ. æÍíË Ãä åĞå ÇáãÚáæãÇÊ Óæİ áä íÊã ÚÑÖåÇ Çáì Ãí ÌåÉ ËÇáËÉ Ïæä Úáãß İÃä ãÏíÑ ÇáÕİÍÉ æÑÄÓÇÁ ÇáãäÊÏì æãæÌåíä ÇáãäÊÏìáä íÊÍãáæÇ ÇáãÓÄæáíÉ áÃí ãÍÇæáÉ ÇáÏÎæá ÚäæÉ æÇáÊí ŞÏ ÊÄÏí Çáì ÊİÔí ÇáãÚáæãÇÊ.<br /><br />åĞÇ ÇáãäÊÏì íÓÊÚãá ÇáÈÓßæÊ áÊÎÒíä ãÚáæãÇÊ Úáì ÌåÇÒß. åĞÇ ÇáÈÓßæÊ áÇ íÍãá Ãí ãÚáæãÇÊ Êã ÇÏÎÇáåÇ İí ÇáÇÚáì, ÇäãÇ İÇÆÏÊåÇ İŞØ áÊÍÓíä ãÊÚÉ ÇáÊÕİÍ İí ÇáãäÊÏì. íÓÊÚãá ÈÑíÏß ÇáÇáßÊÑæäí áÊÃßíÏ ÚãáíÉ ÊÓÌíáß İí ÇáãäÊÏì İŞØ (æáÇÑÓÇá ßáãÉ ÇáÓÑ ÇáÎÇÕÉ Èß İÍÇáÉ äÓíÇäåÇ).<br /><br />ÚäÏ ÇáÖÛØ Úáì ÒÑ ÇáÊÓÌíá İí ÇáÇÓİá İÇäß ÊæÇİŞ ÈÃä ÊáÒã ÈÇáÇÊİÇŞíÉ.";

$lang['Agree_under_13'] = "ÃæÇİŞ Úáì åĞå ÇáÔÑæØ æÚãÑí <b>ÃŞá ãä</b> 13 ÓäÉ";
$lang['Agree_over_13'] = "ÃæÇİŞ Úáì åĞå ÇáÔÑæØ æÚãÑí <b>ÃßÈÑ ãä</b> 13 ÓäÉ";
$lang['Agree_not'] = "áÇ ÇæÇİŞ Úáì Êáß ÇáÔÑæØ";

$lang['Wrong_activation'] = "ãİÊÇÍ ÇáÊÔÛíá ÇáĞí Êã ÊÒæíÏå áÇ íÊİŞ ãÚ ÇáãİÊÇÍ ÇáĞí İí ŞÇÚÏÉ ÇáÈíÇäÇÊ";
$lang['Send_password'] = "ÇÑÓá áí ßáãÉ ÓÑ ÌÏíÏÉ"; 
$lang['Password_updated'] = "Êã Êßæíä ßáãÉ ÓÑ ÌÏíÏÉ, ÇáÑÌÇÁ ÇáÑÌæÚ Çáì ÈÑíÏß ÇáÇáßÊÑæäí áãÚÑİÉ ßíİíÉ ÊÔÛíá ÇáÍÓÇÈ";
$lang['No_email_match'] = "ÇáÈÑíÏ ÇáÇáßÊÑæäí ÇáĞí ÒæÏÊäÇ Èå áÇ íØÈÇŞ ÇáãæÌæÏ áÏíäÇ";
$lang['New_password_activation'] = "ÊÔÛíá ÌÏíÏ";
$lang['Password_activated'] = "Êã ÇÚÇÏÉ ÊÔÛíá ÍÓÇÈß. ááÏÎæá ÇáÑÌÇÁ ÇÓÊÚãÇá ßáãÉ ÇáÓÑ ÇáÊí Êã ÇÑÓÇáåÇ Çáì ÈÑíÏß ÇáÇáßÊÑæäí æÇÊÈÇÚ ÇáÊÚáíãÇÊ ÇáãĞßæÑÉ";

$lang['Send_email_msg'] = "ÇÑÓá ÑÓÇáÉ ÈÑíÏ ÇáßÊÑæäí";
$lang['No_user_specified'] = "áã íÊã ÊÍÏíÏ ÇáãÔÊÑß";
$lang['User_prevent_email'] = "åĞÇ ÇáãÔÊÑß áÇ íæÏ ÇÓÊŞÈÇá ÈÑíÏ ÇáßÊÑæäí. ÍÇæá ÇÑÓÇá ÑÓÇáÉ ÎÇÕÉ";
$lang['User_not_exist'] = "åĞÇ ÇáãÔÊÑß ÛíÑ ãæÌæÏ";
$lang['CC_email'] = "ÇÑÓá äÓÎÉ ãä ÇáÑÓÇáÉ Çáì ÈÑíÏí ÇáÇßÊÑæäí";
$lang['Email_message_desc'] = "ÓíÊã ÇÑÓÇá ÇáÑÓÇáÉ ßãÇ åí ßäÕ, áÇ ÊßÊÈ Ãí ÇÔÇÑÇÊ HTML Ãæ BBCode. ÚäæÇä ÇáÑÏ Óíßæä ÈÑíÏß ÇáÇáßÊÑæäí.";
$lang['Flood_email_limit'] = "áÇ ÊÓÊØíÚ ÇÑÓÇá ÑÓÇáÉ ÃÎÑì ÇáÂä¡ ÍÇæá áÇÍŞÇ";
$lang['Recipient'] = "ÇáãÓÊáã";
$lang['Email_sent'] = "Êã ÇÑÓÇá ÇáÑÓÇáÉ";
$lang['Send_email'] = "ÇÑÓá ÇáÑÓÇáÉ";
$lang['Empty_subject_email'] = "Úáíß ÊÍÏíÏ ÚäæÇä ÇáÑÓÇáÉ";
$lang['Empty_message_email'] = "Úáíß ÊÍÏíÏ ÇáÑÓÇáÉ";


//
// Memberslist
//
$lang['Select_sort_method'] = "ÇÎÊÑ ØÑíŞÉ ÇáİÑÒ";
$lang['Sort'] = "ÇİÑÒ";
$lang['Sort_Top_Ten'] = "ÇÚáì ÚÔÑÉ äÇÔÑíä";
$lang['Sort_Joined'] = "ÊÇÑíÎ ÇáÇÔÊÑÇß";
$lang['Sort_Username'] = "ÇÓã ÇáãÓÊÎÏã";
$lang['Sort_Location'] = "ÇáÚäæÇä";
$lang['Sort_Posts'] = "ãÌãæÚ ÇáÇÓåÇãÇÊ";
$lang['Sort_Email'] = "ÇáÈÑíÏ ÇáÇáßÊÑæäí";
$lang['Sort_Website'] = "ÕİÍÉ ÇáÇäÊÑäÊ";
$lang['Sort_Ascending'] = "ÊÕÇÚÏíÇ";
$lang['Sort_Descending'] = "ÊäÇÒáíÇ";
$lang['Order'] = "ÑÊÈ";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "áæÍÉ ÊÍßã ÇáãÌãæÚÉ";
$lang['Group_member_details'] = "ÊİÇÕíá ÇÔÊÑÇßÇÊ ÇáãÌãæÚÉ";
$lang['Group_member_join'] = "ÇäÙã Çáì ÇáãÌãæÚÉ";

$lang['Group_Information'] = "ãÚáæãÇÊ Úä ÇáãÌãæÚÉ";
$lang['Group_name'] = "ÇÓã ÇáãÌãæÚÉ";
$lang['Group_description'] = "æÕİ ÇáãÌãæÚÉ";
$lang['Group_membership'] = "ÚÖæíÉ ÇáãÌãæÚÉ";
$lang['Group_Members'] = "ÇÚÖÇÁ ÇáãÌãæÚÉ";
$lang['Group_Moderator'] = "ÑÆÓÇÁ ÇáãÌãæÚÉ";
$lang['Pending_members'] = "ÇÚÖÇÁ Úáì ŞÇÆãÉ ÇáÇäÊÙÇÑ";

$lang['Group_type'] = "äæÚ ÇáãÌãæÚÉ";
$lang['Group_open'] = "ãÌãæÚÉ ãİÊæÍÉ";
$lang['Group_closed'] = "ãÌãæÚÉ ãÛáŞÉ";
$lang['Group_hidden'] = "ãÌãæÚÉ ÎİíÉ";

$lang['Current_memberships'] = "ÇáÇÚÖÇÁ ÇáÍÇáííä";
$lang['Non_member_groups'] = "ãÌãæÚÇÊ áÛíÑ ÇáÇÚÖÇÁ";
$lang['Memberships_pending'] = "ÇáÇÔÊÑÇßÇÊ ÇáãÚáŞÉ";

$lang['No_groups_exist'] = "áÇ ÊæÌÏ ãÌãæÚÇÊ";
$lang['Group_not_exist'] = "ãÌãæÚÉ ÇáãÔÊÑß áÇ ÊæÌÏ";

$lang['Join_group'] = "ÔÇÑß İí ÇáãÌãæÚÉ";
$lang['No_group_members'] = "áÇ ÇÚÖÇÁ İí åĞå ÇáãÌãæÚÉ";
$lang['Group_hidden_members'] = "åĞå ÇáãÌãæÚÉ ãÎÊİíÉ, áÇ ÊÓÊØíÚ ÑÄíÉ ÇÚÖÇÁåÇ";
$lang['No_pending_group_members'] = "áÇ ÚÖæíÇÊ ãÚáŞÉ áåĞå ÇáãÌãæÚÉ";
$lang["Group_joined"] = "áŞÏ Êã ÊÓÌíá ÇÔÊÑÇßß İí åĞå ÇáãÌãæÚÉ<br />Óæİ íÊã ÇÎÈÇÑß ÈãæÇİŞÉ ÑÆíÓ ÇáãÌãæÚÉ";
$lang['Group_request'] = "Êã ÊŞÏíã ØáÈß ááÇÔÊÑÇß İí ÇáãÌãæÚÉ";
$lang['Group_approved'] = "ÊãÊ ÇáãæÇİŞÉ Úáì ØáÈß";
$lang['Group_added'] = "Êã ÇÖÇİÊß ááãÌãæÚÉ"; 
$lang['Already_member_group'] = "ÇäÊ ÚÖæ İí ÇáãÌãæÚÉ";
$lang['User_is_member_group'] = "ÇáãÔÊÑß ÚÖæ İí ÇáãÌãæÚÉ";
$lang['Group_type_updated'] = "Êã ÊÍÏíË äæÚ ÇáãÌãæÚÉ ÈäÌÇÍ";

$lang['Could_not_add_user'] = "ÇáÔÎÕ ÇáãÎÊÇÑ ÛíÑ ãæÌæÏ";
$lang['Could_not_anon_user'] = "áÇ ÊÓÊØíÚ Çä ÊÌÚá ãÌåæá ÚÖæ İí ÇáãÌãæÚÉ";

$lang['Confirm_unsub'] = "åá ÃäÊ ãÊÃßÏ ãä ÎÑæÌß ãä åĞå ÇáãÌãæÚÉ?";
$lang['Confirm_unsub_pending'] = "áã íÊã ÇáãæÇİŞÉ Úáì ãÔÇÑßÊß İí ÇáãÌãæÚÉ, åá ÃäÊ ãÊÃßÏ ãä ÇÒÇáÊß ãä ÇáãÌãæÚÉ?";

$lang['Unsub_success'] = "Êã ÇÒÇáÊß ãä ÇáãÌãæÚÉ.";

$lang['Approve_selected'] = "ÇŞÈá ÇáãÎÊÇÑ";
$lang['Deny_selected'] = "ÇÈÚÏ ÇáãÎÊÇÑ";
$lang['Not_logged_in'] = "Úáíß ÇáÏÎæá ááÇÔÊÑÇß İí ÇáãÌãæÚÉ.";
$lang['Remove_selected'] = "ÇÒÇáÉ ÇáãÎÊÇÑ";
$lang['Add_member'] = "ÇÖİ ÚÖæ";
$lang['Not_group_moderator'] = "ÇäÊ áíÓ ÑÆíÓ ÇáãÌãæÚÉ¡ áĞÇ áíÓ áß ÕáÇÍíÉ åĞÇ ÇáÇãÑ.";

$lang['Login_to_join'] = "ÇÏÎá ááÇÔÊÑÇß Ãæ ÇÏÇÑÉ ÇÚÖÇÁ ÇáãÌãæÚÉ";
$lang['This_open_group'] = "åĞå ãÌãæÚÉ ãİÊæÍÉ, ÇÖÛØ åäÇ áØáÈ ÇáÚÖæíÉ";
$lang['This_closed_group'] = "åĞå ãÌãæÚÉ ãÛáŞÉ, áä íÊã ŞÈæá ÇÚÖÇÁ ÂÎÑíä";
$lang['This_hidden_group'] = "åĞå ãÌãæÚÉ ÎİíÉ, áä íÊã ÇÖÇİÉ ÇÚÖÇÁ ÂáíÇ";
$lang['Member_this_group'] = "ÇäÊ ÚÖæ İí ÇáãÌãæÚÉ";
$lang['Pending_this_group'] = "ÇÔÊÑÇßß İí ÇáãÌãæÚÉ ãÚáŞ";
$lang['Are_group_moderator'] = "ÇäÊ ãÏíÑ ÇáãÌãæÚÉ";
$lang['None'] = "áÇ ÃÍÏ";

$lang['Subscribe'] = "ÇÔÊÑß";
$lang['Unsubscribe'] = "ÇáÛ ÇÔÊÑÇß";
$lang['View_Information'] = "ÇÚÑÖ ãÚáæãÇÊ";


//
// Search
//
$lang['Search_query'] = "ÓÄÇá ÇáÈÍË";
$lang['Search_options'] = "ÇÎÊíÇÑÇÊ ÇáÈÍË";

$lang['Search_keywords'] = "ÇáÈÍË Úä ßáãÇÊ";
$lang['Search_keywords_explain'] = "ÊÓÊØíÚ ÇÓÊÚãÇá <u>AND</u> áÊÚííä ßáãÇÊ íÌÈ Ãä Êßæä İí ÇáÈÍË, <u>OR</u> áÊÚííä ßáãÇÊ ŞÏ Êßæä İí ÇáÈÍË <u>NOT</u> áÊÚííä ßáãÇÊ áÇ Êßæä İí ÇáÈÍË. ÇÓÊÚãá * ßÍÑİ ÚÔæÇÆí ááÈÍË";
$lang['Search_author'] = "ÇÈÍË Úä ãÄáİ";
$lang['Search_author_explain'] = "ÇÓÊÚãá * ßÈÍË ÚÔæÇÆí ááßáãÇÊ";

$lang['Search_for_any'] = "ÇÈÍË Úä Çí ßáãÉ Çæ ÇÓÊÚãá ÇáÓÄÇá ßãÇ åæ";
$lang['Search_for_all'] = "ÇÈÍË Úä ÌãíÚ ÇáßáãÇÊ";
$lang['Search_title_msg'] = "ÇÈÍË Úä ÚäæÇä ÇáÑÓÇáÉ æäÕåÇ";
$lang['Search_msg_only'] = "ÇÈÍË Úä äÕ ÇáÑÓÇáÉ İŞØ";

$lang['Return_first'] = "ÇÑÌÚ Çáì Ãæá"; // followed by xxx characters in a select box
$lang['characters_posts'] = "ÍÑİ ãä ÇáÑÓÇáÉ";

$lang['Search_previous'] = "ÇÈÍË Úä "; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "ÇİÑÒ ÈÜ";
$lang['Sort_Time'] = "æŞÊ ÇáÇÑÓÇá";
$lang['Sort_Post_Subject'] = "ãæÖæÚ ÇáÇÑÓÇá";
$lang['Sort_Topic_Title'] = "ÚäæÇä ÇáãæÖæÚ";
$lang['Sort_Author'] = "ÇáßÇÊÈ";
$lang['Sort_Forum'] = "ÇáãäÊÏì";

$lang['Display_results'] = "ÇÓÊÚÑÖ ÇáäÊÇÆÌ ßÜ";
$lang['All_available'] = "ÌãíÚ ÇáãæÌæÏ";
$lang['No_searchable_forums'] = "áíÓ áÏíß ÕáÇÍíÉ ÇáÈÍË İí Çí ãäÊÏì İí åĞå ÇáÕİÍÉ";

$lang['No_search_match'] = "áÇíæÌÏ Çí ãæÖæÚ íæÇİŞ ÔÑæØ ÇáÈÍË ÇáãÏÎáÉ";
$lang['Found_search_match'] = "áŞÏ æÌÏ ÇáÈÍË %d ÑÓÇáÉ"; // eg. Search found 1 match
$lang['Found_search_matches'] = "áŞÏ æÌÏ ÇáÈÍË %d ÑÓÇÆá"; // eg. Search found 24 matches

$lang['Close_window'] = "ÇÛáŞ ÇáäÇİĞÉ";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "äÃÓİ áßä İŞØ Çá%s íÓÊØíÚ æÖÚ ÇÚáÇäÇÊ İí åĞÇ ÇáãäÊÏì";
$lang['Sorry_auth_sticky'] = "äÃÓİ áßä İŞØ Çá%s íÓÊØíÚ æÖÚ ãæÖæÚ áÇÕŞ İí åĞÇ ÇáãäÊÏì"; 
$lang['Sorry_auth_read'] = "äÃÓİ áßä İŞØ Çá%s íÓÊØíÚ ŞÑÇÁÉ ÇáãæÇÖíÚ İí åĞÇ ÇáãäÊÏì"; 
$lang['Sorry_auth_post'] = "äÃÓİ áßä İŞØ Çá%s íÓÊØíÚ æÖÚ ÇáÑÏæÏ æÇáãæÇÖíÚ İí åĞÇ ÇáãäÊÏì"; 
$lang['Sorry_auth_reply'] = "äÃÓİ áßä İŞØ Çá%s íÓÊØíÚ ÇáÑÏ Úáì ÇáãæÇÖíÚ İí åĞÇ ÇáãäÊÏì"; 
$lang['Sorry_auth_edit'] = "äÃÓİ áßä İŞØ Çá%s íÓÊØíÚ ÊÚÏíá ÇáãæÇÖíÚ İí åĞÇ ÇáãäÊÏì"; 
$lang['Sorry_auth_delete'] = "äÃÓİ áßä İŞØ Çá%s íÓÊØíÚ ÇáÛÇÁ ÇáãæÇÖíÚ æÇáÑÏæÏİí åĞÇ ÇáãäÊÏì"; 
$lang['Sorry_auth_vote'] = "äÃÓİ áßä İŞØ Çá%s íÓÊØíÚæä ÇáÊÕæíÊ İí åĞÇ ÇáãäÊÏì"; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>ãÔÊÑßíä ãÌåæáíä</b>";
$lang['Auth_Registered_Users'] = "<b>ãÔÊÑßíä ãÓÌáíä</b>";
$lang['Auth_Users_granted_access'] = "<b>ãÓÊÎÏãíä áåã ÓáØÇÊ ÎÇÕÉ</b>";
$lang['Auth_Moderators'] = "<b>ÑÆíÓ ãäÊÏì</b>";
$lang['Auth_Administrators'] = "<b>ãÏíÑ</b>";

$lang['Not_Moderator'] = "ÇäÊ áÓÊ ÑÆíÓ ááãäÊÏì";
$lang['Not_Authorised'] = "ÛíÑ ãÓãæÍ";

$lang['You_been_banned'] = "áŞÏ Êã ãäÚß ãä åĞÇ ÇáãäÊÏì<br />ÇáÑÌÇÁ ÇáÇÊÕÇá ÈãÏíÑ ÇáãæŞÚ ";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "áÇ íæÌÏ ãÔÇÑßíä æ "; // There ae 5 Registered and
$lang['Reg_users_online'] = "åäÇß %d ãÔÊÑßíä æ "; // There ae 5 Registered and
$lang['Reg_user_online'] = "åäÇß %d ãÔÊÑß æ "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "áÇ íæÌÏ ãÓÊÎÏãíä ãÎÊİíä"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d ãÓÊÎÏã ãÎÊİí Úáì ÇáÎØ"; // 6 Hidden users online
$lang['Hidden_user_online'] = "%d ãÔÊÑßãÎÊİíä Úáì ÇáÎØ"; // 6 Hidden users online
$lang['Guest_users_online'] = "åäÇß %d Öíİ Úáì ÇáÎØ"; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = "áÇ íæÌÏ Ãí ãÔÊÑß Úáì ÇáÎØ"; // There are 10 Guest users online
$lang['Guest_user_online'] = "åäÇß %d ãÔÊÑß/ãÔÊÑßíä Úáì ÇáÎØ"; // There is 1 Guest user online
$lang['No_users_browsing'] = "áÇ íæÌÏ ãÔÊÑßíä íÓÊÚÑÖæä åĞÇ ÇáãäÊÏì";

$lang['Online_explain'] = "åĞå ÇáÈíÇäÇÊ ÊÚÊãÏ Úáì ÇáãÔÊÑßíä ÇáãÔÇÑßíä İí ÂÎÑ ÎãÓÉ ÏŞÇÆŞ";

$lang['Forum_Location'] = "ãæŞÚ ÇáãäÊÏì";
$lang['Last_updated'] = "ÂÎÑ ÊÍÏíË";

$lang['Forum_index'] = "İåÑÓ ÇáãäÊÏì";
$lang['Logging_on'] = "ÇáÏÎæá";
$lang['Posting_message'] = "æÖÚ ÑÓÇáÉ";
$lang['Searching_forums'] = "ÈÍË ÇáãäÊÏíÇÊ";
$lang['Viewing_profile'] = "ÚÑÖ áãÍÉ";
$lang['Viewing_online'] = "ÚÑÖ ãä Úáì ÇáÎØ";
$lang['Viewing_member_list'] = "ÚÑÖ ÇÓãÇÁ ÇáãÔÊÑßíä";
$lang['Viewing_priv_msgs'] = "ÚÑÖ ÑÓÇÆá ÎÇÕÉ";
$lang['Viewing_FAQ'] = "ÚÑÖ Ó¡Ì";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "áæÍÉ ÊÍßã ãÏíÑ ÇáãäÊ";
$lang['Mod_CP_explain'] = "ÈÇÓÊÚãÇá ÇáäãæĞÌ ÇáÊÇáí ÊÓÊØíÚ ÇáŞíÇã ÈÇÏÇÑÉ ÌãÇÚíÉ ááãäÊÏì. ÊÓÊØíÚ Şİá Ãæ İÊÍ äŞá Ãæ ÇáÛÇÁ Ãí ãæÖæÚ.";

$lang['Select'] = "ÇÎÊÇÑ";
$lang['Delete'] = "ÇÒÇáÉ";
$lang['Move'] = "äŞá";
$lang['Lock'] = "Şİá";
$lang['Unlock'] = "İÊÍ Şİá";

$lang['Topics_Removed'] = "Êã ÇÒÇáÉ ÇáãæÇÖíÚ ãä ŞÇÚÏÉ ÇáÈíÇäÇÊ.";
$lang['Topics_Locked'] = "Êã Şİá ÇáãæÇÖíÚ";
$lang['Topics_Moved'] = "Êã äŞá ÇáãæÇÖíÚ";
$lang['Topics_Unlocked'] = "Êã İÊÍ Şİá ÇáãæÇÖíÚ";
$lang['No_Topics_Moved'] = "áã íÊã ÇÒÇáÉ ÇáãæÇÖíÚ";

$lang['Confirm_delete_topic'] = "åá ÇäÊ ãÊÃßÏ ãä ÇÒÇáÉ ÇáãæÇÖíÚ ÇáãÎÊÇÑÉ?";
$lang['Confirm_lock_topic'] = "åá ÇäÊ ãÊÃßÏ ãä Şİá ÇáãæÇÖíÚ ÇáãÎÊÇÑÉ?";
$lang['Confirm_unlock_topic'] = "åá ÇäÊ ãäÊÃßÏ ãä İÊÍ Şİá ÇáãæÇÖíÚ ÇáãÎÊÇÑÉ?";
$lang['Confirm_move_topic'] = "åá ÇäÊ ãÊÃßÏ ãä äŞá ÇáãæÖæÚ/ÇáãæÇÖíÚ ÇáãÎÊÇÑÉ?";

$lang['Move_to_forum'] = "ÇäÊŞá Çáì ãäÊÏì";
$lang['Leave_shadow_topic'] = "ÇÊÑß Ùá ÇáãæÖæÚ İí ÇáãäÊÏì ÇáÓÇÈŞ.";

$lang['Split_Topic'] = "áæÍÉ ÊÍßã ÊŞÓíã ÇáãæÖæÚ";
$lang['Split_Topic_explain'] = "ÈÇÓÊÚãÇá ÇáäãæĞÌ ÇáÊÇáí ÊÓÊØíÚ İÕá ÇáãæÖæÚ Çáì ÌÒÆíä, ÅãÇ ÈÇÎÊíÇÑ ÇáÑÏæÏ ßá Úáì ÍÏÉ Ãæ ÇÎÊíÇÑ ÇáÑÏ ÇáİÇÕá";
$lang['Split_title'] = "ÚäæÇä ãæÖæÚ ÌÏíÏ";
$lang['Split_forum'] = "ãæÖæÚ ÌÏíÏ İí ÇáãäÊÏì";
$lang['Split_posts'] = "ÊŞÓíã ÇáãæÇÖíÚ ÇáãÎÊÇÑÉ";
$lang['Split_after'] = "ÇİÕá Úä ÇáãæÖæÚ ÇáãÎÊÇÑ";
$lang['Topic_split'] = "Êã ÊŞÓíã ÇáãæÖæÚ ÈäÌÇÍ";

$lang['Too_many_error'] = "áŞÏ ÇÎÊÑÊ ãæÇÖíÚ ßËíÑÉ. ÊÓÊØíÚ ÇÎÊíÇÑ ÑÏ æÇÍÏ áİÕá ÇáãæÖæÚ ÚäÏå!";

$lang['None_selected'] = "áã ÊÎÊÑ Çí ãæÖæÚ áÊØÈíŞ Êáß ÇáÚãáíÉ. ÇáÑÌÇÁ ÇáÑÌæÚ æÇÎÊíÇÑ Çí ãæÖæÚ.";
$lang['New_forum'] = "ãäÊÏì ÌÏíÏ";

$lang['This_posts_IP'] = "ÑŞã ÇáÇäÊÑäÊ áåĞÇ ÇáÇÑÓÇá";
$lang['Other_IP_this_user'] = "ÇÑŞÇã ÇäÊÑäÊ Êã ÇáÇÑÓÇá ãäåÇ";
$lang['Users_this_IP'] = "ÇáãÔÊÑßíä ÇáãÑÓáíä ãä ÑŞã ÇáÇäÊÑäÊ";
$lang['IP_info'] = "ãÚáæãÇÊ ÑŞã ÇáÇäÊÑäÊ";
$lang['Lookup_IP'] = "ÇÈÍË Úä ÑŞã ÇáÇäÊÑäÊ";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "ÌãíÚ ÇáÇæŞÇÊ ÊÓÊÚãá äÙÇã %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 ÓÇÚÉ";
$lang['-11'] = "GMT - 11 ÓÇÚÉ";
$lang['-10'] = "HST (Hawaii)";
$lang['-9'] = "GMT - 9 ÓÇÚÉ";
$lang['-8'] = "PST (U.S./Canada)";
$lang['-7'] = "MST (U.S./Canada)";
$lang['-6'] = "CST (U.S./Canada)";
$lang['-5'] = "EST (U.S./Canada)";
$lang['-4'] = "GMT - 4 ÓÇÚÉ";
$lang['-3.5'] = "GMT - 3.5 ÓÇÚÉ";
$lang['-3'] = "GMT - 3 ÓÇÚÉ";
$lang['-2'] = "Mid-Atlantic";
$lang['-1'] = "GMT - 1 ÓÇÚÉ";
$lang['0'] = "GMT";
$lang['1'] = "CET (Europe)";
$lang['2'] = "EET (Europe)";
$lang['3'] = "GMT + 3 ÓÇÚÉ";
$lang['3.5'] = "GMT + 3.5 ÓÇÚÉ";
$lang['4'] = "GMT + 4 ÓÇÚÉ";
$lang['4.5'] = "GMT + 4.5 ÓÇÚÉ";
$lang['5'] = "GMT + 5 ÓÇÚÉ";
$lang['5.5'] = "GMT + 5.5 ÓÇÚÉ";
$lang['6'] = "GMT + 6 ÓÇÚÉ";
$lang['7'] = "GMT + 7 ÓÇÚÉ";
$lang['8'] = "WST (Australia)";
$lang['9'] = "GMT + 9 ÓÇÚÉ";
$lang['9.5'] = "CST (Australia)";
$lang['10'] = "EST (Australia)";
$lang['11'] = "GMT + 11 ÓÇÚÉ";
$lang['12'] = "GMT + 12 ÓÇÚÉ";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT -12:00 ÓÇÚÉ) Eniwetok, Kwajalein";
$lang['tz']['-11'] = "(GMT -11:00 ÓÇÚÉ) Midway Island, Samoa";
$lang['tz']['-10'] = "(GMT -10:00 ÓÇÚÉ) Hawaii";
$lang['tz']['-9'] = "(GMT -9:00 ÓÇÚÉ) Alaska";
$lang['tz']['-8'] = "(GMT -8:00 ÓÇÚÉ) Pacific Time (US &amp; Canada), Tijuana";
$lang['tz']['-7'] = "(GMT -7:00 ÓÇÚÉ) Mountain Time (US &amp; Canada), Arizona";
$lang['tz']['-6'] = "(GMT -6:00 ÓÇÚÉ) Central Time (US &amp; Canada), Mexico City";
$lang['tz']['-5'] = "(GMT -5:00 ÓÇÚÉ) Eastern Time (US &amp; Canada), Bogota, Lima, Quito";
$lang['tz']['-4'] = "(GMT -4:00 ÓÇÚÉ) Atlantic Time (Canada), Caracas, La Paz";
$lang['tz']['-3.5'] = "(GMT -3:30 ÓÇÚÉ) Newfoundland";
$lang['tz']['-3'] = "(GMT -3:00 ÓÇÚÉ) Brassila, Buenos Aires, Georgetown, Falkland Is";
$lang['tz']['-2'] = "(GMT -2:00 ÓÇÚÉ) Mid-Atlantic, Ascension Is., St. Helena";
$lang['tz']['-1'] = "(GMT -1:00 ÓÇÚÉ) Azores, Cape Verde Islands";
$lang['tz']['0'] = "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia";
$lang['tz']['1'] = "(GMT +1:00 ÓÇÚÉ) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome";
$lang['tz']['2'] = "(GMT +2:00 ÓÇÚÉ) Cairo, Helsinki, Kaliningrad, South Africa, Warsaw";
$lang['tz']['3'] = "(GMT +3:00 ÓÇÚÉ) Baghdad, Riyadh, Moscow, Nairobi";
$lang['tz']['3.5'] = "(GMT +3:30 ÓÇÚÉ) Tehran";
$lang['tz']['4'] = "(GMT +4:00 ÓÇÚÉ) Abu Dhabi, Baku, Muscat, Tbilisi";
$lang['tz']['4.5'] = "(GMT +4:30 ÓÇÚÉ) Kabul";
$lang['tz']['5'] = "(GMT +5:00 ÓÇÚÉ) Ekaterinburg, Islamabad, Karachi, Tashkent";
$lang['tz']['5.5'] = "(GMT +5:30 ÓÇÚÉ) Bombay, Calcutta, Madras, New Delhi";
$lang['tz']['6'] = "(GMT +6:00 ÓÇÚÉ) Almaty, Colombo, Dhaka, Novosibirsk";
$lang['tz']['6.5'] = "(GMT +6:30 ÓÇÚÉ) Rangoon";
$lang['tz']['7'] = "(GMT +7:00 ÓÇÚÉ) Bangkok, Hanoi, Jakarta";
$lang['tz']['8'] = "(GMT +8:00 ÓÇÚÉ) Beijing, Hong Kong, Perth, Singapore, Taipei";
$lang['tz']['9'] = "(GMT +9:00 ÓÇÚÉ) Osaka, Sapporo, Seoul, Tokyo, Yakutsk";
$lang['tz']['9.5'] = "(GMT +9:30 ÓÇÚÉ) Adelaide, Darwin";
$lang['tz']['10'] = "(GMT +10:00 ÓÇÚÉ) Canberra, Guam, Melbourne, Sydney, Vladivostok";
$lang['tz']['11'] = "(GMT +11:00 ÓÇÚÉ) Magadan, New Caledonia, Solomon Islands";
$lang['tz']['12'] = "(GMT +12:00 ÓÇÚÉ) Auckland, Wellington, Fiji, Marshall Island";

$lang['days_long'][0] = "ÇáÇÍÏ";
$lang['days_long'][1] = "ÇáÇËäíä";
$lang['days_long'][2] = "ÇáËáÇËÇÁ";
$lang['days_long'][3] = "ÇáÇÑÈÚÇÁ";
$lang['days_long'][4] = "ÇáÎãíÓ";
$lang['days_long'][5] = "ÇáÌãÚÉ";
$lang['days_long'][6] = "ÇáÓÈÊ";

$lang['days_short'][0] = "ÇáÇÍÏ";
$lang['days_short'][1] = "ÇáÇËäíä";
$lang['days_short'][2] = "ÇáËáÇËÇÁ";
$lang['days_short'][3] = "ÇáÇÑÈÚÇÁ";
$lang['days_short'][4] = "ÇáÎãíÓ";
$lang['days_short'][5] = "ÇáÌãÚÉ";
$lang['days_short'][6] = "ÇáÓÈÊ";

$lang['months_long'][0] = "íäÇíÑ";
$lang['months_long'][1] = "İÈÑÇíÑ";
$lang['months_long'][2] = "ãÇÑÓ";
$lang['months_long'][3] = "ÇÈÑíá";
$lang['months_long'][4] = "ãÇíæ";
$lang['months_long'][5] = "íæäíæ";
$lang['months_long'][6] = "íæáíæ";
$lang['months_long'][7] = "ÇÛÓØÓ";
$lang['months_long'][8] = "ÓÈÊãÈÑ";
$lang['months_long'][9] = "ÇßÊæÈÑ";
$lang['months_long'][10] = "äæİãÈÑ";
$lang['months_long'][11] = "ÏíÓãÈÑ";

$lang['months_short'][0] = "íäÇíÑ";
$lang['months_short'][1] = "İÈÑÇíÑ";
$lang['months_short'][2] = "ãÇÑÓ";
$lang['months_short'][3] = "ÇÈÑíá";
$lang['months_short'][4] = "ãÇíæ";
$lang['months_short'][5] = "íæäíæ";
$lang['months_short'][6] = "íæáíæ";
$lang['months_short'][7] = "ÇÛÓØÓ";
$lang['months_short'][8] = "ÓÈÊãÈÑ";
$lang['months_short'][9] = "ÇßÊæÈÑ";
$lang['months_short'][10] = "äæİãÈÑ";
$lang['months_short'][11] = "ÏíÓãÈÑ";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "ãÚáæãÇÊ";
$lang['Critical_Information'] = "ãÚáæãÇÊ åÇãÉ";

$lang['General_Error'] = "ÚØá ÚÇã";
$lang['Critical_Error'] = "ÚØá åÇã";
$lang['An_error_occured'] = "ÍÕá ÚØá";
$lang['A_critical_error'] = "ÍÕá ÚØá åÇã";

//
// That's all Folks!
// -------------------------------------------------

?>
