<?php
/***************************************************************************
 *                            lang_main.php [chinese simplified]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_main.php,v 1.73 2001/12/30 13:39:42 psotfx Exp $
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
// Translation by:
//      inker    :: http://www.byink.com
//
//      For questions and comments use: support@byink.com
//      last modify   : 2002/3/1                      
//


//setlocale(LC_ALL, "en");
$lang['ENCODING'] = "gb2312";
$lang['DIRECTION'] = "LTR";
$lang['LEFT'] = "LEFT";
$lang['RIGHT'] = "RIGHT";
$lang['DATE_FORMAT'] =  "Y-m-d"; // This should be changed to the default date format for your language, php date() format

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "ÂÛÌ³";
$lang['Category'] = "ÌÖÂÛÇø";
$lang['Topic'] = "Ö÷Ìâ";
$lang['Topics'] = "Ö÷Ìâ";
$lang['Replies'] = "»Ø¸´";
$lang['Views'] = "ÔÄ¶Á";
$lang['Post'] = "ÎÄÕÂ";
$lang['Posts'] = "ÎÄÕÂ";
$lang['Posted'] = "·¢±íÓÚ";
$lang['Username'] = "»áÔ±Ãû³Æ";
$lang['Password'] = "ÃÜÂë";
$lang['Email'] = "Email";
$lang['Poster'] = "·¢±íÕß";
$lang['Author'] = "×÷Õß";
$lang['Time'] = "Ê±¼ä";
$lang['Hours'] = "Ğ¡Ê±ÄÚ";
$lang['Message'] = "ÁôÑÔ";

$lang['1_Day'] = "1 ÌìÄÚ";
$lang['7_Days'] = "7 ÌìÄÚ";
$lang['2_Weeks'] = "2 ¸öĞÇÆÚÄÚ";
$lang['1_Month'] = "1 ¸öÔÂÄÚ";
$lang['3_Months'] = "3 ¸öÔÂÄÚ";
$lang['6_Months'] = "6 ¸öÔÂÄÚ";
$lang['1_Year'] = "1 ÄêÄÚ";

$lang['Go'] = "Go";
$lang['Jump_to'] = "×ªÌøµ½";
$lang['Submit'] = "·¢ËÍ";
$lang['Reset'] = "ÖØÉè";
$lang['Cancel'] = "È¡Ïû";
$lang['Preview'] = "Ô¤ÀÀ";
$lang['Confirm'] = "È·¶¨";
$lang['Spellcheck'] = "¼ì²éÓï·¨";
$lang['Yes'] = "ÊÇ";
$lang['No'] = "·ñ";
$lang['Enabled'] = "¿ªÆô";
$lang['Disabled'] = "¹Ø±Õ";
$lang['Error'] = "´íÎó";

$lang['Next'] = "ÏÂÒ»¸ö";
$lang['Previous'] = "ÉÏÒ»¸ö";
$lang['Goto_page'] = "Ç°ÍùÒ³Ãæ";
$lang['Joined'] = "¼ÓÈëÓÚ";
$lang['IP_Address'] = "IP µØÖ·";

$lang['Select_forum'] = "Ñ¡ÔñÒ»¸ö°æÃæ";
$lang['View_latest_post'] = "ä¯ÀÀ×î¾ÉµÄÌû×Ó";
$lang['View_newest_post'] = "ä¯ÀÀ×îĞÂµÄÌû×Ó";
$lang['Page_of'] = "µÚ<b>%d</b>Ò³/¹²<b>%d</b>Ò³"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "ICQ Number";
$lang['AIM'] = "AIM Address";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "%s Ê×Ò³";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "·¢±íĞÂÌû";
$lang['Reply_to_topic'] = "»Ø¸´Ìû×Ó";
$lang['Reply_with_quote'] = "ÒıÓÃ²¢»Ø¸´";

$lang['Click_return_topic'] = "µã»÷ %sÕâÀï%s ·µ»ØÖ÷Ìâ"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "µã»÷ %sÕâÀï%s ÔÙÊÔÒ»±é";
$lang['Click_return_forum'] = "µã»÷ %sÕâÀï%s ·µ»ØÂÛÌ³";
$lang['Click_view_message'] = "µã»÷ %sÕâÀï%s ÔÄ¶ÁÄúµÄÌû×Ó";
$lang['Click_return_modcp'] = "µã»÷ %sÕâÀï%s ·µ»Ø°ßÖñ¹ÜÀíÇø";
$lang['Click_return_group'] = "µã»÷ %sÕâÀï%s ·µ»ØÍÅ¶ÓĞÅÏ¢Çø(to return to group information)";

$lang['Admin_panel'] = "ÂÛÌ³¹ÜÀíÔ±¿ØÖÆÃæ°å";

$lang['Board_disable'] = "¶Ô²»Æğ,±¾ÂÛÌ³ÔİÊ±²»ÄÜ·ÃÎÊ,Çë´ı»áÔÚÊÔ.";


//
// Global Header strings
//
$lang['Registered_users'] = "×¢²á»áÔ±:";
$lang['Browsing_forum'] = "ÕıÔÚä¯ÀÀÕâ¸ö°æÃæµÄ»áÔ±:";
$lang['Online_users_zero_total'] = "×Ü¼ÆÓĞ <b>0</b> Î»ÅóÓÑÔÚÏß :: ";
$lang['Online_users_total'] = "×Ü¼ÆÓĞ <b>%d</b> Î»ÅóÓÑÔÚÏß :: ";
$lang['Online_user_total'] = "×Ü¼ÆÓĞ <b>%d</b> Î»ÅóÓÑÔÚÏß :: ";
$lang['Reg_users_zero_total'] = "0 Î»»áÔ±, ";
$lang['Reg_users_total'] = "%d Î»»áÔ±, ";
$lang['Reg_user_total'] = "%d Î»»áÔ±, ";
$lang['Hidden_users_zero_total'] = "0 Î»ÒşÉíºÍ ";
$lang['Hidden_user_total'] = "%d Î»ÒşÉíºÍ ";
$lang['Hidden_user_total'] = "%d Î»ÒşÉíºÍ ";
$lang['Guest_users_zero_total'] = "0 Î»ÓÎ¿Í";
$lang['Guest_users_total'] = "%d Î»ÓÎ¿Í";
$lang['Guest_user_total'] = "%d Î»ÓÎ¿Í";
$lang['Record_online_users'] = "×î¸ßÔÚÏß¼ÍÂ¼ÊÇ <b>%s</b> ÈË %s"; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = "%sÂÛÌ³¹ÜÀíÔ±%s";
$lang['Mod_online_color'] = "%s°ßÖñ%s";

$lang['You_last_visit'] = "ÄúÉÏ´Î·ÃÎÊÊ±¼äÊÇ %s"; // %s replaced by date/time
$lang['Current_time'] = "ÏÖÔÚµÄÊ±¼äÊÇ %s"; // %s replaced by time

$lang['Search_new'] = "ÔÄ¶ÁÉÏ´Î·ÃÎÊºóµÄÌû×Ó";
$lang['Search_your_posts'] = "ÔÄ¶ÁÄú·¢±íµÄÌû×Ó";
$lang['Search_unanswered'] = "ÔÄ¶ÁÉĞÎ´»Ø´ğµÄÌû×Ó";

$lang['Register'] = "×¢²á";
$lang['Profile'] = "¸öÈË×ÊÁÏ";
$lang['Edit_profile'] = "±à¼­ÄúµÄ¸öÈË×ÊÁÏ";
$lang['Search'] = "ËÑË÷";
$lang['Memberlist'] = "»áÔ±ÁĞ±í";
$lang['FAQ'] = "³£¼ûÎÊÌâ";
$lang['BBCode_guide'] = "BBCode Ö¸ÄÏ";
$lang['Usergroups'] = "ÍÅ¶Ó";
$lang['Last_Post'] = "×îºó·¢±í";
$lang['Moderator'] = "°ßÖñ";
$lang['Moderators'] = "°ßÖñ";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "ÂÛÌ³¹²»¹Ã»ÓĞÌû×Ó"; // Number of posts
$lang['Posted_articles_total'] = "ÂÛÌ³¹²ÓĞ <b>%d</b> ¸öÌû×Ó"; // Number of posts
$lang['Posted_article_total'] = "ÂÛÌ³¹²ÓĞ <b>%d</b> ¸öÌû×Ó"; // Number of posts
$lang['Registered_users_zero_total'] = "ÂÛÌ³¹²»¹Ã»ÓĞ×¢²á»áÔ±"; // # registered users
$lang['Registered_users_total'] = "ÂÛÌ³¹²ÓĞ <b>%d</b> Î»×¢²á»áÔ±"; // # registered users
$lang['Registered_user_total'] = "ÂÛÌ³¹²ÓĞ <b>%d</b> Î»×¢²á»áÔ±"; // # registered users
$lang['Newest_user'] = "×îĞÂ×¢²áµÄ»áÔ±ÊÇ <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "ÉÏ´Î·ÃÎÊºóÃ»ÓĞĞÂÌû";
$lang['No_new_posts'] = "Ã»ÓĞĞÂÌû";
$lang['New_posts'] = "ÓĞĞÂÌù";
$lang['New_post'] = "ÓĞĞÂÌù";
$lang['No_new_posts_hot'] = "Ã»ÓĞĞÂÌû [ ÈÈÃÅ ]";
$lang['New_posts_hot'] = "ÓĞĞÂÌù [ ÈÈÃÅ ]";
$lang['No_new_posts_locked'] = "Ã»ÓĞĞÂÌû [ Ëø¶¨ ]";
$lang['New_posts_locked'] = "ÓĞĞÂÌù [ ½âËø ]";
$lang['Forum_is_locked'] = "¹Ø±ÕµÄÂÛÌ³";


//
// Login
//
$lang['Enter_password'] = "ÇëÊäÈëÄúµÄÓÃ»§ÃûºÍÃÜÂëµÇÂ½";
$lang['Login'] = "µÇÂ½";
$lang['Logout'] = "×¢Ïú";

$lang['Forgotten_password'] = "ÎÒÍü¼ÇÁËÃÜÂë!";

$lang['Log_me_in'] = "ä¯ÀÀÊ±×Ô¶¯µÇÂ½";

$lang['Error_login'] = "ÄúÌá¹©µÄÓÃ»§Ãû»òÃÜÂë²»ÕıÈ·";


//
// Index page
//
$lang['Index'] = "Ê×Ò³";
$lang['No_Posts'] = "Ã»ÓĞÌû×Ó";
$lang['No_forums'] = "Õâ¸ö°æÃæ»¹Ã»ÓĞÌû×Ó";

$lang['Private_Message'] = "Õ¾ÄÚĞÅ¼ş";
$lang['Private_Messages'] = "Õ¾ÄÚĞÅ¼ş";
$lang['Who_is_Online'] = "µ±Ç°ÔÚÏß×´Ì¬";

$lang['Mark_all_forums'] = "±ê¼ÇËùÓĞÂÛÌ³ÎªÒÑ¶Á";
$lang['Forums_marked_read'] = "ËùÓĞÂÛÌ³ÒÑ±í¼ÇÎªÒÑ¶Á";


//
// Viewforum
//
$lang['View_forum'] = "ä¯ÀÀÂÛÌ³";

$lang['Forum_not_exist'] = "ÄúÑ¡ÔñµÄÂÛÌ³²»´æÔÚ";
$lang['Reached_on_error'] = "ÄúÑ¡ÔñµÄÂÛÌ³³ö´íÁË";

$lang['Display_topics'] = "ÏÔÊ¾ÒÔÇ°µÄÌû×Ó";
$lang['All_Topics'] = "ËùÓĞµÄÌû×Ó";

$lang['Topic_Announcement'] = "<b>¹«¸æ:</b>";
$lang['Topic_Sticky'] = "<b>ÖÃ¶¥:</b>";
$lang['Topic_Moved'] = "<b>ÒÆ¶¯:</b>";
$lang['Topic_Poll'] = "<b>[ Í¶Æ± ]</b>";

$lang['Mark_all_topics'] = "±ê¼ÇËùÓĞÌû×ÓÎªÒÑ¶Á";
$lang['Topics_marked_read'] = "Õâ¸öÂÛÌ³µÄËùÓĞÌû×ÓÒÑ±ê¼ÇÎªÒÑ¶Á";

$lang['Rules_post_can'] = "Äú<b>¿ÉÒÔ</b>·¢²¼ĞÂÖ÷Ìâ";
$lang['Rules_post_cannot'] = "Äú<b>²»ÄÜ</b>·¢²¼ĞÂÖ÷Ìâ";
$lang['Rules_reply_can'] = "Äú<b>¿ÉÒÔ</b>ÔÚÕâ¸öÂÛÌ³»Ø¸´Ö÷Ìâ";
$lang['Rules_reply_cannot'] = "Äú<b>²»ÄÜ</b>ÔÚÕâ¸öÂÛÌ³»Ø¸´Ö÷Ìâ";
$lang['Rules_edit_can'] = "Äú<b>¿ÉÒÔ</b>ÔÚÕâ¸öÂÛÌ³±à¼­×Ô¼ºµÄÌû×Ó";
$lang['Rules_edit_cannot'] = "Äú<b>²»ÄÜ</b>ÔÚÕâ¸öÂÛÌ³±à¼­×Ô¼ºµÄÌû×Ó";
$lang['Rules_delete_can'] = "Äú<b>¿ÉÒÔ</b>ÔÚÕâ¸öÂÛÌ³É¾³ı×Ô¼ºµÄÌû×Ó";
$lang['Rules_delete_cannot'] = "Äú<b>²»ÄÜ</b>ÔÚÕâ¸öÂÛÌ³É¾³ı×Ô¼ºµÄÌû×Ó";
$lang['Rules_vote_can'] = "Äú<b>¿ÉÒÔ</b>ÔÚÕâ¸öÂÛÌ³·¢±íÍ¶Æ±";
$lang['Rules_vote_cannot'] = "Äú<b>²»ÄÜ</b>ÔÚÕâ¸öÂÛÌ³·¢±íÍ¶Æ±";
$lang['Rules_moderate'] = "Äú<b>¿ÉÒÔ</b>%s¹ÜÀíÕâ¸öÂÛÌ³%s"; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = "Õâ¸öÂÛÌ³Àï»¹Ã»ÓĞÌû×Ó<br />µã»÷<b>·¢±íÖ÷Ìâ</b>·¢±íÒ»¸öÌû×Ó";


//
// Viewtopic
//
$lang['View_topic'] = "ÔÄ¶ÁÖ÷Ìâ";

$lang['Guest'] = 'ÓÎ¿Í';
$lang['Post_subject'] = "·¢±íÖ÷Ìâ";
$lang['View_next_topic'] = "ÔÄ¶ÁÏÂÒ»¸öÖ÷Ìâ";
$lang['View_previous_topic'] = "ÔÄ¶ÁÉÏÒ»¸öÖ÷Ìâ";
$lang['Submit_vote'] = "·¢±íÍ¶Æ±";
$lang['View_results'] = "ä¯ÀÀ½á¹û";

$lang['No_newer_topics'] = "Õâ¸öÂÛÌ³Ã»ÓĞ¸üĞÂµÄÖ÷Ìâ";
$lang['No_older_topics'] = "Õâ¸öÂÛÌ³Ã»ÓĞ¸ü¾ÉµÄÖ÷Ìâ";
$lang['Topic_post_not_exist'] = "ÄúÑ¡ÔñµÄÖ÷Ìâ²»´æÔÚ";
$lang['No_posts_topic'] = "Õâ¸öÖ÷ÌâÀïÃ»ÓĞÌû×Ó";

$lang['Display_posts'] = "ÏÔÊ¾ÒÔÇ°µÄÖ÷Ìâ";
$lang['All_Posts'] = "ËùÓĞÖ÷Ìâ";
$lang['Newest_First'] = "×îĞÂµÄÖ÷Ìâ";
$lang['Oldest_First'] = "×î¾ÉµÄÖ÷Ìâ";

$lang['Back_to_top'] = "·µ»ØÒ³Ê×";

$lang['Read_profile'] = "ÔÄÀÀ»áÔ±×ÊÁÏ"; 
$lang['Send_email'] = "¸ø»áÔ±·¢µç×ÓÓÊ¼ş";
$lang['Visit_website'] = "ä¯ÀÀ·¢±íÕßµÄÖ÷Ò³";
$lang['ICQ_status'] = "ICQ ×´Ì¬";
$lang['Edit_delete_post'] = "±à¼­/É¾³ıÌû×Ó";
$lang['View_IP'] = "ä¯ÀÀ·¢±íÕßµÄIPµØÖ·";
$lang['Delete_post'] = "É¾³ıÕâ¸öÌû×Ó";

$lang['wrote'] = "Ğ´µ½"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "ÒıÓÃ"; // comes before bbcode quote output.
$lang['Code'] = "´úÂë"; // comes before bbcode code output.

$lang['Edited_time_total'] = "×îºó½øĞĞ±à¼­µÄÊÇ %s on %s, ×Ü¼ÆµÚ %d ´Î±à¼­"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "×îºó½øĞĞ±à¼­µÄÊÇ %s on %s, ×Ü¼ÆµÚ %d ´Î±à¼­"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Ëø¶¨±¾Ìù";
$lang['Unlock_topic'] = "½âËø±¾Ìù";
$lang['Move_topic'] = "ÒÆ¶¯±¾Ìù";
$lang['Delete_topic'] = "É¾³ı±¾Ìù";
$lang['Split_topic'] = "·Ö¸î±¾Ìù";

$lang['Stop_watching_topic'] = "Í£Ö¹¶©ÔÄ±¾Ö÷Ìâ";
$lang['Start_watching_topic'] = "¶©ÔÄ±¾Ö÷Ìâ";
$lang['No_longer_watching'] = "Äú²»ÔÙ¶©ÔÄ±¾Ö÷Ìâ";
$lang['You_are_watching'] = "ÄúÒÑ¶©ÔÄÁË±¾Ö÷Ìâ";

$lang['Total_votes'] = "Í¶Æ±¹²¼Æ";

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "ÎÄÕÂÄÚÈİ";
$lang['Topic_review'] = "Ô¤ÀÀÖ÷Ìâ";

$lang['No_post_mode'] = "No post mode specified"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = "·¢±íĞÂÌù";
$lang['Post_a_reply'] = "·¢±í»Ø¸´";
$lang['Post_topic_as'] = "Post topic as";
$lang['Edit_Post'] = "±à¼­ÎÄÕÂ";
$lang['Options'] = "Ñ¡Ïî";

$lang['Post_Announcement'] = "¹«¸æ";
$lang['Post_Sticky'] = "ÖÃ¶¥";
$lang['Post_Normal'] = "ÆÕÍ¨";

$lang['Confirm_delete'] = "ÄúÈ·¶¨ÒªÉ¾³ıÕâ¸öÖ÷ÌâÂğ?";
$lang['Confirm_delete_poll'] = "ÄúÈ·¶¨ÒªÉ¾³ıÕâ¸öÍ¶Æ±Âğ?";

$lang['Flood_Error'] = "Äú²»ÄÜÔÚ·¢ÌùºóÂíÉÏ·¢±íĞÂÌù£¬Çë¹ıÒ»»áÔÙÊÔ.";
$lang['Empty_subject'] = "Äú·¢±íµÄÌû×Ó±ØĞëÓĞÒ»¸öÖ÷Ìâ.";
$lang['Empty_message'] = "Äú·¢±íµÄÌû×Ó±ØĞëÓĞÄÚÈİ.";
$lang['Forum_locked'] = "Õâ¸öÂÛÌ³ÒÑ¾­±»Ëø¶¨,Äú²»ÄÜ·¢±í,»Ø¸´»òÕß±à¼­Ìû×Ó.";
$lang['Topic_locked'] = "Õâ¸öÂÛÌâÒÑ¾­±»Ëø¶¨,Äú²»ÄÜ·¢±í,»Ø¸´»òÕß±à¼­Ìû×Ó.";
$lang['No_post_id'] = "ÇëÑ¡ÔñÄúÒª±à¼­µÄÖ÷Ìâ";
$lang['No_topic_id'] = "ÇëÑ¡ÔñÄúÒª»Ø¸´µÄÖ÷Ìâ";
$lang['No_valid_mode'] = "ÄúÖ»¿ÉÒÔÑ¡Ôñ·¢±í,»Ø¸´»òÕßÒıÓÃÌû×Ó,ÇëºóÍËÖØÊÔ.";
$lang['No_such_post'] = "Ã»ÓĞÕâ¸öÌû×Ó,ÇëºóÍËÖØÊÔ.";
$lang['Edit_own_posts'] = "¶Ô²»ÆğÄúÖ»¿ÉÒÔ±à¼­×Ô¼ºµÄÌû×Ó.";
$lang['Delete_own_posts'] = "¶Ô²»ÆğÄúÖ»¿ÉÒÔÉ¾³ı×Ô¼ºµÄÌû×Ó.";
$lang['Cannot_delete_replied'] = "¶Ô²»ÆğÄú¿ÉÄÜ²»¿ÉÒÔÉ¾³ıÒÑ¾­±»»Ø¸´µÄÌû×Ó.";
$lang['Cannot_delete_poll'] = "¶Ô²»ÆğÄú²»¿ÉÒÔÉ¾³ıÕı´¦ÓÚ»î¶¯×´Ì¬µÄÍ¶Æ±.";
$lang['Empty_poll_title'] = "Äú±ØĞë¸øÄú·¢±íµÄÍ¶Æ±½¨Á¢Ò»¸öÖ÷Ìâ.";
$lang['To_few_poll_options'] = "Äú±ØĞëÒª½¨Á¢ÖÁÉÙÁ½¸öÍ¶Æ±µÄÑ¡Ïî.";
$lang['To_many_poll_options'] = "ÄúÑ¡Ôñ½¨Á¢Ì«¶àµÄÍ¶Æ±µÄÑ¡Ïî";
$lang['Post_has_no_poll'] = "Õâ¸öÖ÷ÌâÃ»ÓĞ½¨Á¢Í¶Æ±";

$lang['Add_poll'] = "½¨Á¢Ò»¸öÍ¶Æ±";
$lang['Add_poll_explain'] = "Èç¹ûÄú²»Ïë½¨Á¢Í¶Æ±Çë²»ÒªÌîĞ´Õâ¸öÑ¡Ïî.";
$lang['Poll_question'] = "Í¶Æ±ÎÊÌâ";
$lang['Poll_option'] = "Í¶Æ±Ñ¡Ïî";
$lang['Add_option'] = "½¨Á¢Ñ¡Ïî";
$lang['Update'] = "¸üĞÂ";
$lang['Delete'] = "É¾³ı";
$lang['Poll_for'] = "ÔËĞĞÕâ¸öÍ¶Æ±ÔÚ";
$lang['Days'] = "ÌìÄÚ"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ Ñ¡Ôñ 0 »òÕß²»Ñ¡ÔñÕâ¸öÑ¡Ïî´ú±íÓÀÔ¶ÔËĞĞÍ¶Æ± ]";
$lang['Delete_poll'] = "É¾³ıÍ¶Æ±";

$lang['Disable_HTML_post'] = "ÔÚÕâ¸öÌû×ÓÀï½ûÖ¹HTMLÓïÑÔ";
$lang['Disable_BBCode_post'] = "ÔÚÕâ¸öÌû×ÓÀï½ûÖ¹BBCode";
$lang['Disable_Smilies_post'] = "ÔÚÕâ¸öÌû×ÓÀï½ûÖ¹±íÇé·ûºÅ";

$lang['HTML_is_ON'] = "HTML is <u>ON</u>";
$lang['HTML_is_OFF'] = "HTML is <u>OFF</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s is <u>ON</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s is <u>OFF</u>";
$lang['Smilies_are_ON'] = "Smilies are <u>ON</u>";
$lang['Smilies_are_OFF'] = "Smilies are <u>OFF</u>";

$lang['Attach_signature'] = "¸öĞÔÇ©Ãû (ÄúµÄ¸öĞÔÇ©Ãû¿ÉÒÔÔÚ¸öÈË×ÊÁÏÀï¸ü¸Ä)";
$lang['Notify'] = "·¢ÌùÊ±ÌáĞÑÎÒ";
$lang['Delete_post'] = "É¾³ıÕâ¸öÖ÷Ìâ";

$lang['Stored'] = "ÄúµÄÌû×ÓÒÑ¾­³É¹¦µÄ´¢´æ";
$lang['Deleted'] = "ÄúµÄÌû×ÓÒÑ¾­³É¹¦µÄ±»É¾³ı";
$lang['Poll_delete'] = "Äú½¨Á¢µÄÍ¶Æ±ÒÑ¾­³É¹¦µÄ±»É¾³ı";
$lang['Vote_cast'] = "ÄúµÄÑ¡Æ±ÒÑ¾­Í¶³ö";

$lang['Topic_reply_notification'] = "»ØÌûÍ¨Öª";

$lang['bbcode_b_help'] = "´ÖÌå: [b]text[/b]  (alt+b)";
$lang['bbcode_i_help'] = "´óĞ´: [i]text[/i]  (alt+i)";
$lang['bbcode_u_help'] = "ÏÂ»®Ïß: [u]text[/u]  (alt+u)";
$lang['bbcode_q_help'] = "ÒıÓÃÎÄ±¾: [quote]text[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "ÏÔÊ¾´úÂë : [code]code[/code]  (alt+c)";
$lang['bbcode_l_help'] = "ÁĞ±í: [list]text[/list] (alt+l)";
$lang['bbcode_o_help'] = "°´ĞòÁĞ±í: [list=]text[/list]  (alt+o)";
$lang['bbcode_p_help'] = "²åÈëÍ¼Ïñ: [img]http://image_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "²åÈëÁ´½ÓÍøÖ·: [url]http://url[/url] or [url=http://url]URL text[/url]  (alt+w)";
$lang['bbcode_a_help'] = "¹Ø±ÕËùÓĞ¿ªÆôµÄbbCode±êÇ©";
$lang['bbcode_s_help'] = "×ÖÌåÑÕÉ«: [color=red]text[/color]  ÌáÊ¾: ÄúÒ²¿ÉÒÔÊ¹ÓÃÈç color=#FF0000 ÕâÑùµÄhtmlÓï¾ä";
$lang['bbcode_f_help'] = "×ÖÌå´óĞ¡: [size=x-small]small text[/size]";

$lang['Emoticons'] = "±íÇéÍ¼°¸";
$lang['More_emoticons'] = "ä¯ÀÀ¸ü¶àµÄ±íÇéÍ¼°¸";

$lang['Font_color'] = "×ÖÌåÑÕÉ«";
$lang['color_default'] = "±ê×¼";
$lang['color_dark_red'] = "Éîºì";
$lang['color_red'] = "ºìÉ«";
$lang['color_orange'] = "³ÈÉ«";
$lang['color_brown'] = "×ØÉ«";
$lang['color_yellow'] = "»ÆÉ«";
$lang['color_green'] = "ÂÌÉ«";
$lang['color_olive'] = "éÏé­";
$lang['color_cyan'] = "ÇàÉ«";
$lang['color_blue'] = "À¶É«";
$lang['color_dark_blue'] = "ÉîÀ¶";
$lang['color_indigo'] = "µåÀ¶";
$lang['color_violet'] = "×ÏÉ«";
$lang['color_white'] = "°×É«";
$lang['color_black'] = "ºÚÉ«";

$lang['Font_size'] = "×ÖÌå´óĞ¡";
$lang['font_tiny'] = "×îĞ¡";
$lang['font_small'] = "Ğ¡";
$lang['font_normal'] = "Õı³£";
$lang['font_large'] = "´ó";
$lang['font_huge'] = "×î´ó";

$lang['Close_Tags'] = "Íê³É±êÇ©";
$lang['Styles_tip'] = "ÌáÊ¾: ÎÄ×Ö·ç¸ñ¿ÉÒÔ¿ìËÙÊ¹ÓÃÔÚÑ¡ÔñµÄÎÄ×ÖÉÏ";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Õ¾ÄÚĞÅ¼ş";

$lang['Login_check_pm'] = "µÇÂ½²é¿´ÄúµÄÕ¾ÄÚĞÅ¼ş";
$lang['New_pms'] = "ÄúÓĞ %d ·âĞÂµÄÕ¾ÄÚĞÅ¼ş"; // You have 2 new messages
$lang['New_pm'] = "ÄúÓĞ %d ·âĞÂµÄÕ¾ÄÚĞÅ¼ş"; // You have 1 new message
$lang['No_new_pm'] = "ÄúÃ»ÓĞĞÂµÄÕ¾ÄÚĞÅ¼ş";
$lang['Unread_pms'] = "ÄúÓĞ %d ·âÎ´¶ÁµÄÕ¾ÄÚĞÅ¼ş";
$lang['Unread_pm'] = "ÄúÓĞ %d ·âÎ´¶ÁµÄÕ¾ÄÚĞÅ¼ş";
$lang['No_unread_pm'] = "ÄúÃ»ÓĞÎ´¶ÁµÄÕ¾ÄÚĞÅ¼ş";
$lang['You_new_pm'] = "Ò»·âĞÂµÄÕ¾ÄÚĞÅ¼şÔÚÄúµÄÊÕ¼şÏäÀï";
$lang['You_new_pms'] = "¼¸·âĞÂµÄÕ¾ÄÚĞÅ¼şÔÚÄúµÄÊÕ¼şÏäÀï";
$lang['You_no_new_pm'] = "Ã»ÓĞĞÂµÄÕ¾ÄÚĞÅ¼ş";

$lang['Inbox'] = "ÊÕ¼şÏä";
$lang['Outbox'] = "ÒÑ·¢ËÍµÄĞÅ¼şÏä";
$lang['Savebox'] = "²İ¸åÏä";
$lang['Sentbox'] = "·¢¼şÏä";
$lang['Flag'] = "±ê¼Ç";
$lang['Subject'] = "Ö÷Ìâ";
$lang['From'] = "À´×Ô";
$lang['To'] = "·¢ËÍÖÁ";
$lang['Date'] = "ÈÕÆÚ";
$lang['Mark'] = "Ñ¡Ôñ";
$lang['Sent'] = "·¢ËÍ";
$lang['Saved'] = "±£´æ";
$lang['Delete_marked'] = "É¾³ıÒÑÑ¡ÔñµÄÕ¾ÄÚĞÅ¼ş";
$lang['Delete_all'] = "É¾³ıËùÓĞµÄÕ¾ÄÚĞÅ¼ş";
$lang['Save_marked'] = "±£´æÒÑÑ¡ÔñµÄÕ¾ÄÚĞÅ¼ş"; 
$lang['Save_message'] = "±£´æÕ¾ÄÚĞÅ¼ş";
$lang['Delete_message'] = "É¾³ıÕ¾ÄÚĞÅ¼ş";

$lang['Display_messages'] = "ÏÔÊ¾ÒÔÇ°µÄÌû×Ó"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "ËùÓĞµÄÕ¾ÄÚĞÅ¼ş";

$lang['No_messages_folder'] = "Õâ¸öÎÄ¼ş¼ĞÀïÃ»ÓĞĞÅ¼ş";

$lang['PM_disabled'] = "Õâ¸öÂÛÌ³µÄÕ¾ÄÚĞÅ¼şÒÑ¾­±»½ûÓÃ";
$lang['Cannot_send_privmsg'] = "¶Ô²»ÆğÂÛÌ³¹ÜÀíÔ±ÒÑ¾­½ûÖ¹Äú·¢ËÍÕ¾ÄÚĞÅ¼ş";
$lang['No_to_user'] = "Äú±ØĞëÖ¸¶¨Õ¾ÄÚĞÅ¼ş·¢ËÍµÄ¶ÔÏó";
$lang['No_such_user'] = "¶Ô²»ÆğÕâ¸öÓÃ»§²»´æÔÚ";

$lang['Disable_HTML_pm'] = "ÔÚÕâ¸öĞÅ¼şÀï½ûÖ¹HTMLÓïÑÔ";
$lang['Disable_BBCode_pm'] = "ÔÚÕâ¸öĞÅ¼şÀï½ûÖ¹BBCode";
$lang['Disable_Smilies_pm'] = "ÔÚÕâ¸öĞÅ¼şÀï½ûÖ¹±íÇé·ûºÅ";

$lang['Message_sent'] = "ÄúµÄÕ¾ÄÚĞÅ¼ş·¢ËÍ³É¹¦";

$lang['Click_return_inbox'] = "µã»÷ %sÕâÀï%s ·µ»ØÄúµÄÊÕ¼şÏä";
$lang['Click_return_index'] = "µã»÷ %sÕâÀï%s ·µ»ØÊ×Ò³";

$lang['Send_a_new_message'] = "·¢ËÍÒ»¸öĞÂµÄÕ¾ÄÚĞÅ¼ş";
$lang['Send_a_reply'] = "»Ø¸´Õ¾ÄÚĞÅ¼ş";
$lang['Edit_message'] = "±à¼­Õ¾ÄÚĞÅ¼ş";

$lang['Notification_subject'] = "ĞÂµÄÕ¾ÄÚĞÅ¼ş";

$lang['Find_username'] = "²éÕÒÒ»¸öÓÃ»§";
$lang['Find'] = "²éÕÒ";
$lang['No_match'] = "ÕÒ²»µ½Æ¥ÅäµÄÓÃ»§";

$lang['No_post_id'] = "Ã»ÓĞÖ¸¶¨Ö÷Ìâ";
$lang['No_such_folder'] = "Ã»ÓĞÕâÑùµÄÎÄ¼ş¼Ğ´æÔÚ";
$lang['No_folder'] = "Ã»ÓĞÖ¸¶¨ÎÄ¼ş¼Ğ";

$lang['Mark_all'] = "Ñ¡ÔñËùÓĞĞÅ¼ş";
$lang['Unmark_all'] = "È¡ÏûËùÓĞÑ¡Ôñ";

$lang['Confirm_delete_pm'] = "ÄúÈ·¶¨ÒªÉ¾³ıÕâ·âÕ¾ÄÚĞÅ¼şÂğ?";
$lang['Confirm_delete_pms'] = "ÄúÈ·¶¨ÒªÉ¾³ıÕâĞ©Õ¾ÄÚĞÅ¼şÂğ?";

$lang['Inbox_size'] = "ÄúµÄÊÕ¼şÏäÒÑÊ¹ÓÃ %d%%"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "ÄúµÄ·¢¼şÏäÒÑÊ¹ÓÃ %d%%"; 
$lang['Savebox_size'] = "ÄúµÄ²İ¸åÏäÒÑÊ¹ÓÃ %d%%"; 

$lang['Click_view_privmsg'] = "µã»÷%sÕâÀï%sä¯ÀÀÄúµÄÊÕ¼şÏä";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "ä¯ÀÀ¸öÈË×ÊÁÏ :: %s"; // %s is username 
$lang['About_user'] = "¹ØÓÚ %s"; // %s is username

$lang['Preferences'] = "Ñ¡Ïî";
$lang['Items_required'] = "´ø*µÄÏîÄ¿ÊÇ±ØĞëÌîĞ´µÄ";
$lang['Registration_info'] = "×¢²áĞÅÏ¢";
$lang['Profile_info'] = "¸öÈË×ÊÁÏ";
$lang['Profile_info_warn'] = "ÒÔÏÂĞÅÏ¢½«±»¹«¿ª";
$lang['Avatar_panel'] = "Í·Ïñ¿ØÖÆÃæ°å";
$lang['Avatar_gallery'] = "Í·Ïñ»­¼¯";

$lang['Website'] = "Ö÷Ò³";
$lang['Location'] = "Î»ÖÃ";
$lang['Contact'] = "ÁªÂç";
$lang['Email_address'] = "Email µØÖ·";
$lang['Email'] = "Email";
$lang['Send_private_message'] = "·¢ËÍÕ¾ÄÚĞÅ¼ş";
$lang['Hidden_email'] = "[ Òş²Ø ]";
$lang['Search_user_posts'] = "²éÕÒÕâÎ»ÓÃ»§·¢±íµÄÌû×Ó";
$lang['Interests'] = "ĞËÈ¤";
$lang['Occupation'] = "Ö°Òµ"; 
$lang['Poster_rank'] = "ÓÃ»§¼¶±ğ";

$lang['Total_posts'] = "·¢Ìù×Ü¼Æ";
$lang['User_post_pct_stats'] = "%.2f%% of total"; // 1.25% of total
$lang['User_post_day_stats'] = "Æ½¾ù %.2f ·âÌû×ÓÃ¿Ìì"; // 1.5 posts per day
$lang['Search_user_posts'] = "²éÕÒ%s·¢±íµÄËùÓĞÌû×Ó"; // Find all posts by username

$lang['No_user_id_specified'] = "¶Ô²»ÆğÕâ¸öÓÃ»§²»´æÔÚ";
$lang['Wrong_Profile'] = "Äú²»¿ÉÒÔ±à¼­È¥ËûÓÃ»§µÄ¸öÈË×ÊÁÏ";

$lang['Only_one_avatar'] = "ÄúÖ»ÄÜÑ¡ÔñÒ»¸öÍ·Ïñ";
$lang['File_no_data'] = "ÄúÌá¹©µÄÁ¬½ÓµØÖ·²»´æÔÚÊı¾İ";
$lang['No_connection_URL'] = "ÎŞ·¨Á¬½ÓÄúÌá¹©µÄÁ¬½ÓµØÖ·";
$lang['Incomplete_URL'] = "ÄúÌá¹©µÄÁ¬½ÓµØÖ·²»ÍêÕû";
$lang['Wrong_remote_avatar_format'] = "ÄúÌá¹©µÄÍ·ÏñÁ¬½ÓµØÖ·ÎŞĞ§";
$lang['No_send_account_inactive'] = "¶Ô²»ÆğÎŞ·¨ÕÒ»ØÄúµÄÃÜÂëÒòÎªÄúµÄÕË»§ÏÖÔÚ²»ÔÚ»î¶¯×´Ì¬,ÇëÁªÂçÂÛÌ³¹ÜÀíÔ±µÃµ½¸ü¶àµÄĞÅÏ¢.";

$lang['Always_smile'] = "×ÜÊÇ¿ªÆô²åÍ¼¹¦ÄÜ";
$lang['Always_html'] = "×ÜÊÇ¿ªÆô HTML";
$lang['Always_bbcode'] = "×ÜÊÇ¿ªÆô BBCode";
$lang['Always_add_sig'] = "×ÜÊÇ·¢±íÎÒµÄ¸öÈËÇ©Ãû";
$lang['Always_notify'] = "×ÜÊÇÌáĞÑÎÒµ±ÓĞÈË»Ø¸´ÎÒµÄÌû×Ó";
$lang['Always_notify_explain'] = "µ±ÓĞÈË»Ø¸´ÎÒµÄÌû×ÓÊ±·¢ËÍÒ»·âµç×ÓÓÊ¼şÌáĞÑÎÒ.Õâ¸öÑ¡Ïî¿ÉÒÔÔÚÄú·¢±íÖ÷ÌâÊ±¸ü¸Ä";

$lang['Board_style'] = "ÂÛÌ³·ç¸ñ";
$lang['Board_lang'] = "ÂÛÌ³ÓïÑÔ";
$lang['No_themes'] = "Êı¾İ¿âÀïÃ»ÓĞ×°ÊÎÖ÷Ìâ";
$lang['Timezone'] = "Ê±Çø";
$lang['Date_format'] = "ÈÕÆÚ¸ñÊ½";
$lang['Date_format_explain'] = "ÈÕÆÚ¸ñÊ½µÄÓï·¨ºÍ PHP <a href=\"http://www.php.net/date\" target=\"_other\">date() Óï¾ä</a>ÍêÈ«ÏàÍ¬";
$lang['Signature'] = "¸öÈËÇ©Ãû";
$lang['Signature_explain'] = "ÄúÌîĞ´µÄ¸öÈËÇ©Ãû¿ÉÒÔ·¢±íÔÚÄúµÄÌû×ÓÏÂ·½.¸öÈËÇ©ÃûÓĞ%d¸ö×Ö·ûµÄÏŞÖÆ";
$lang['Public_view_email'] = "×ÜÊÇÏÔÊ¾ÎÒµÄµç×ÓÓÊ¼şµØÖ·";

$lang['Current_password'] = "ÏÖÔÚµÄÃÜÂë";
$lang['New_password'] = "ĞÂµÄÃÜÂë";
$lang['Confirm_password'] = "È·ÈÏĞÂÃÜÂë";
$lang['Confirm_password_explain'] = "µ±ÄúÏ£Íû¸Ä±äÃÜÂë»òÊÇÄúµÄµç×ÓÓÊ¼şµØÖ·Ê±Äú±ØĞëÈ·ÈÏÏÖÔÚÕıÔÚÊ¹ÓÃµÄÃÜÂë";
$lang['password_if_changed'] = "Ö»ÓĞµ±ÄúÏ£Íû¸ü¸ÄÃÜÂëÊ±²ÅĞèÒªÌá¹©ĞÂµÄÃÜÂë";
$lang['password_confirm_if_changed'] = "Ö»ÓĞµ±ÄúÏ£Íû¸ü¸ÄÃÜÂëÊ±²ÅĞèÒªÈ·ÈÏĞÂµÄÃÜÂë";

$lang['Avatar'] = "Í·Ïñ";
$lang['Avatar_explain'] = "ÏÔÊ¾Ò»¸öĞ¡Í¼Æ¬ÔÚÄú·¢±íµÄÌû×ÓÅÔ,Í¬Ò»Ê±¼äÖ»ÄÜÏÔÊ¾Ò»¸öÍ¼Æ¬.Í¼Æ¬¿í¶È²»ÄÜ³¬¹ı%d pixels, ¸ß¶È²»ÄÜ³¬¹ı%d pixels,Í¼Æ¬´óĞ¡²»ÄÜ³¬¹ı%dkB."; $lang['Upload_Avatar_file'] = "´ÓÄúµÄ¼ÆËã»úÉÏ´«Í¼Æ¬";
$lang['Upload_Avatar_URL'] = "´ÓÒ»¸öÁ¬½ÓÉÏ´«Í¼Æ¬";
$lang['Upload_Avatar_URL_explain'] = "Ìá¹©Ò»¸öÍ¼Æ¬µÄÁ´½ÓµØÖ·,Í¼Æ¬½«±»¸´ÖÆµ½±¾ÂÛÌ³.";
$lang['Pick_local_Avatar'] = "´Ó»­²á¼¯ÀïÑ¡ÔñÒ»¸öÍ·Ïñ";
$lang['Link_remote_Avatar'] = "Á´½ÓÆäËûÎ»ÖÃµÄÍ·Ïñ";
$lang['Link_remote_Avatar_explain'] = "Ìá¹©ÄúÏëÁ´½ÓÍ·ÏñµÄµØÖ·";
$lang['Avatar_URL'] = "Í¼Æ¬Á´½ÓµØÖ·";
$lang['Select_from_gallery'] = "´Ó»­²á¼¯ÀïÑ¡ÔñÒ»¸öÍ·Ïñ";
$lang['View_avatar_gallery'] = "ÏÔÊ¾»­²á¼¯";

$lang['Select_avatar'] = "Ñ¡ÔñÍ·Ïñ";
$lang['Return_profile'] = "È¡ÏûÑ¡ÔñÍ·Ïñ";
$lang['Select_category'] = "Ñ¡ÔñÒ»¸ö»­²á";

$lang['Delete_Image'] = "É¾³ıÍ¼Æ¬";
$lang['Current_Image'] = "ÏÖÔÚÊ¹ÓÃµÄÍ¼Æ¬";

$lang['Notify_on_privmsg'] = "ÌáĞÑÎÒµ±ÓĞĞÂµÄÕ¾ÄÚĞÅ¼ş";
$lang['Popup_on_privmsg'] = "µ¯³öÒ»¸ö´°¿Úµ±ÓĞĞÂµÄÕ¾ÄÚĞÅ¼ş"; 
$lang['Popup_on_privmsg_explain'] = "µ±ÄúÓĞĞÂµÄÕ¾ÄÚĞÅ¼şÊ±½«µ¯³öÒ»¸öĞÂµÄĞ¡´°¿ÚÀ´ÌáĞÑÄú"; 
$lang['Hide_user'] = "Òş²ØÄúµÄÔÚÏß×´Ì¬";

$lang['Profile_updated'] = "ÄúµÄ¸öÈË×ÊÁÏÒÑ¾­¸üĞÂ";
$lang['Profile_updated_inactive'] = "ÄúµÄ¸öÈË×ÊÁÏÒÑ¾­¸üĞÂ,È»¶ø,Äú¸ü¸ÄÁËÕË»§×´Ì¬.ÄúµÄÕË»§ÏÖÔÚ´¦ÓÚÀä¶³×´Ì¬.²ì¿´ÄúµÄµç×ÓÓÊ¼şÀí½âÈçºÎ»Ö¸´ÄúµÄÕË»§,»òÕßÄúĞèµÈ´ıÂÛÌ³¹ÜÀíÔ±»Ö¸´ÄúµÄÕË»§»î¶¯×´Ì¬.(however you have changed vital details thus your account is now inactive. or if admin activation is require wait for the administrator to reactivate your account)";

$lang['Password_mismatch'] = "ÄúÌá¹©µÄÃÜÂë²»Æ¥Åä";
$lang['Current_password_mismatch'] = "ÄúÏÖÔÚÊ¹ÓÃµÄÃÜÂëÓë×¢²áÊ±Ìá¹©µÄ²»Æ¥Åä";
$lang['Password_long'] = "ÃÜÂë²»ÄÜ¶àÓÚ32¸ö×Ó·û";
$lang['Username_taken'] = "¶Ô²»ÆğÄúÑ¡ÔñµÄÓÃ»§ÃûÒÑ¾­ÓĞÈËÊ¹ÓÃÁË";
$lang['Username_invalid'] = "ÄúÑ¡ÔñµÄÓÃ»§Ãû°üº¬ÁËÎŞĞ§µÄ×Ö·û,Ïñ \"";
$lang['Username_disallowed'] = "¶Ô²»ÆğÄúÑ¡ÔñµÄÓÃ»§ÃûÒÑ¾­±»½ûÓÃ";
$lang['Email_taken'] = "¶Ô²»ÆğÄúÌá¹©µÄµç×ÓÓÊ¼şµØÖ·ÒÑ¾­±»Ä³¸öÓÃ»§×¢²áÁË";
$lang['Email_banned'] = "¶Ô²»ÆğÄúÌá¹©µÄµç×ÓÓÊ¼şµØÖ·ÒÑ¾­±»½ûÓÃ";
$lang['Email_invalid'] = "¶Ô²»ÆğÄúÌá¹©µÄµç×ÓÓÊ¼şµØÖ·²»ÕıÈ·";
$lang['Signature_too_long'] = "ÄúµÄ¸öÈËÇ©ÃûÌ«³¤ÁË";
$lang['Fields_empty'] = "Äú±ØĞëÌîĞ´±ØĞëÌîĞ´µÄÏîÄ¿(*)";
$lang['Avatar_filetype'] = "Í·ÏñÍ¼Æ¬µÄÀàĞÍ±ØĞëÊÇ .jpg, .gif or .png";
$lang['Avatar_filesize'] = "Í·ÏñÍ¼Æ¬µÄ´óĞ¡±ØĞëĞ¡ÓÚ %d kB"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "Í·ÏñÍ¼Æ¬µÄ¿í¶È±ØĞëĞ¡ÓÚ %d pixels ¶øÇÒ¸ß¶È±ØĞëĞ¡ÓÚ %d pixels"; 

$lang['Welcome_subject'] = "»¶Ó­Äú·ÃÎÊ %s ÂÛÌ³"; // Welcome to my.com forums
$lang['New_account_subject'] = "ĞÂÓÃ»§ÕÊ»§";
$lang['Account_activated_subject'] = "ÕË»§¼¤»î";

$lang['Account_added'] = "¸ĞĞ»ÄúµÄ×¢²á,ÄúµÄÕË»§ÒÑ¾­±»½¨Á¢.ÄúÏÖÔÚ¾Í¿ÉÒÔÊ¹ÓÃÄúµÄÓÃ»§ÃûºÍÃÜÂëµÇÂ½";
$lang['Account_inactive'] = "¸ĞĞ»ÄúµÄ×¢²á,ÄúµÄÕË»§ÒÑ¾­±»½¨Á¢.±¾ÂÛÌ³ĞèÒª¼¤»îÕË»§.Çë²é¿´ÄúµÄµç×ÓÓÊ¼şÁË½â¼¤»îµÄĞÅÏ¢.";
$lang['Account_inactive_admin'] = "¸ĞĞ»ÄúµÄ×¢²á,ÄúµÄÕË»§ÒÑ¾­±»½¨Á¢.µ«ÊÇ±¾ÂÛÌ³ĞèÒªÂÛÌ³¹ÜÀíÔ±¼¤»îÕË»§. Ò»·âµç×ÓÓÊ¼şÒÑ¾­±»·¢ËÍµ½¹ÜÀíÔ±,ÄúµÄÕË»§±»¼¤»îÊ±Äú½«ÊÕµ½Í¨Öª.";
$lang['Account_active'] = "¸ĞĞ»ÄúµÄ×¢²á,ÄúµÄÕË»§ÒÑ¾­±»½¨Á¢.";
$lang['Account_active_admin'] = "ÕË»§ÏÖÔÚÒÑ¾­±»³É¹¦¼¤»î";
$lang['Reactivate'] = "ÖØĞÂ¼¤»îÄúµÄÕË»§!";
$lang['COPPA'] = "ÄúµÄÕË»§ÒÑ¾­±»½¨Á¢µ«ÊÇĞèÒª±»Åú×¼,Çë²é¿´ÄúµÄµç×ÓÓÊ¼şÁË½âÏ¸½Ú.";

$lang['Registration'] = "×¢²á·şÎñÌõ¿î";
$lang['Reg_agreement'] = "¾¡¹ÜÂÛÌ³¹ÜÀí³ÉÔ±»á¾¡¿ÉÄÜ¾¡¿ìÉ¾³ı»ò±à¼­ÓĞÕùÒé»òÊÇ²»½¡¿µµÄÌû×Ó,µ«ÊÇËûÃÇ²»¿ÉÄÜÔÄ¶ÁËùÓĞµÄÌû×ÓÄÚÈİ.Òò´ËÄúÒò¸Ã³ĞÈÏÕâ¸öÂÛÌ³ÉÏËùÓĞµÄÖ÷ÌâÖ»ÓÉËüµÄ·¢±íÕß³Ğµ£ÔğÈÎ,¶ø²»ÊÇÂÛÌ³µÄ¹ÜÀí³ÉÔ±ÃÇ(³ı·ÇÊÇÓÉËûÃÇ·¢±íµÄ).<br /><br />Äú±ØĞèÍ¬Òâ²»·¢±í´øÓĞÈèÂî,Òù»à,´ÖË×,·Ì°ù,´øÓĞ³ğºŞĞÔ,¿ÖÏÅµÄ,²»½¡¿µµÄ»òÊÇÈÎºÎÎ¥·´·¨ÂÉµÄÄÚÈİ. Èç¹ûÄúÕâÑù×ö½«µ¼ÖÂÄúµÄÕË»§½«Á¢¼´ºÍÓÀ¾ÃĞÔµÄ±»·âËø.(ÄúµÄÍøÂç·şÎñÌá¹©ÉÌÒ²»á±»Í¨Öª). ÔÚÕâ¸öÇé¿öÏÂ,Õâ¸öIPµØÖ·µÄËùÓĞÓÃ»§¶¼½«±»¼ÇÂ¼.Äú±ØĞëÍ¬ÒâÏµÍ³¹ÜÀí³ÉÔ±ÃÇÓĞÔÚÈÎºÎÊ±¼äÉ¾³ı,ĞŞ¸Ä,ÒÆ¶¯»ò¹Ø±ÕÈÎºÎÖ÷ÌâµÄÈ¨Á¦. ×÷ÎªÒ»¸öÊ¹ÓÃÕß, Äú±ØĞëÍ¬ÒâÄúËùÌá¹©µÄÈÎºÎ×ÊÁÏ¶¼½«±»´æÈëÊı¾İ¿âÖĞ,ÕâĞ©×ÊÁÏ³ı·ÇÓĞÄúµÄÍ¬Òâ,ÏµÍ³¹ÜÀíÔ±ÃÇ¾ø²»»á¶ÔµÚÈı·½¹«¿ª,È»¶øÎÒÃÇ²»ÄÜ±£Ö¤ÈÎºÎ¿ÉÄÜµ¼ÖÂ×ÊÁÏĞ¹Â¶µÄº§¿ÍÈëÇÖĞĞÎª.<br /><br />Õâ¸öÌÖÂÛÇøÏµÍ³Ê¹ÓÃcookieÀ´´¢´æÄúµÄ¸öÈËĞÅÏ¢(ÔÚÄúÊ¹ÓÃµÄ±¾µØ¼ÆËã»ú), ÕâĞ©cookie²»°üº¬ÈÎºÎÄúÔø¾­ÊäÈë¹ıµÄĞÅÏ¢,ËüÃÇÖ»ÎªÁË·½±ãÄúÄÜ¸ü·½±ãµÄä¯ÀÀ. µç×ÓÓÊ¼şµØÖ·Ö»ÓÃÀ´È·ÈÏÄúµÄ×¢²áºÍ·¢ËÍÃÜÂëÊ¹ÓÃ.(Èç¹ûÄúÍü¼ÇÁËÃÜÂë,½«»á·¢ËÍĞÂÃÜÂëµÄµØÖ·)<br /><br />µã»÷ÏÂÃæµÄÁ´½Ó´ú±íÄúÍ¬ÒâÊÜµ½ÕâĞ©·şÎñÌõ¿îµÄÔ¼Êø.";

$lang['Agree_under_13'] = "ÎÒÍ¬Òâ²¢ÇÒÎÒ<b>Ğ¡ÓÚ</b>13Ëê";
$lang['Agree_over_13'] = "ÎÒÍ¬Òâ²¢ÇÒÎÒ<b>´óÓÚ</b>13Ëê";
$lang['Agree_not'] = "ÎÒ²»Í¬Òâ";

$lang['Wrong_activation'] = "ÄúÌá¹©µÄ¼¤»îÃÜÂëºÍÊı¾İ¿âÖĞµÄ²»Æ¥Åä";
$lang['Send_password'] = "·¢ËÍÒ»¸öĞÂµÄ¼¤»îÃÜÂë¸øÎÒ"; 
$lang['Password_updated'] = "Äú¸öĞÂµÄ¼¤»îÃÜÂëÒÑ¾­±»½¨Á¢,Çë²é¿´ÄúµÄµç×ÓÓÊ¼şÁË½â¼¤»îÏ¸½Ú";
$lang['No_email_match'] = "ÄúÌá¹©µÄµç×ÓÓÊ¼şµØÖ·ºÍÊı¾İ¿âÖĞµÄ²»Æ¥Åä";
$lang['New_password_activation'] = "ĞÂÃÜÂë¼¤»î";
$lang['Password_activated'] = "ÄúµÄÕË»§ÒÑ¾­±»ÖØĞÂ¼¤»î.ÇëÊ¹ÓÃÄúÊÕµ½µÄµç×ÓÓÊ¼şÖĞµÄÃÜÂëµÇÂ½";

$lang['Send_email_msg'] = "·¢ËÍÒ»·âµç×ÓÓÊ¼ş";
$lang['No_user_specified'] = "Ã»ÓĞÑ¡ÔñÓÃ»§";
$lang['User_prevent_email'] = "ÕâÃûÓÃ»§²»Ï£ÍûÊÕµ½µç×ÓÓÊ¼ş,Äú¿ÉÒÔ·¢ËÍÕ¾ÄÚĞÅ¼ş¸øÕâÃûÓÃ»§";
$lang['User_not_exist'] = "ÓĞ»§²»´æÔÚ";
$lang['CC_email'] = "¸´ÖÆÕâ·âµç×ÓÓÊ¼ş·¢ËÍ¸ø×Ô¼º";
$lang['Email_message_desc'] = "Õâ·âÓÊ¼ş½«±»ÒÔ´¿ÎÄ±¾¸ñÊ½·¢ËÍ,Çë²»Òª°üº¬ÈÎºÎ HTML »òÕß BBCode.ÕâÆªÓÊ¼şµÄ»Ø¸´µØÖ·½«Ö¸ÏòÄúµÄµç×ÓÓÊ¼şµØÖ·.";
$lang['Flood_email_limit'] = "Äú²»ÄÜÏÖÔÚ·¢ËÍÆäËûµÄµç×ÓÓÊ¼ş,Çë¹ıÒ»»áÔÙÊÔ.";
$lang['Recipient'] = "ÊÕĞÅÈË";
$lang['Email_sent'] = "ÓÊ¼şÒÑ¾­±»·¢ËÍ";
$lang['Send_email'] = "·¢ËÍµç×ÓÓÊ¼ş";
$lang['Empty_subject_email'] = "Äú±ØĞë¸øµç×ÓÓÊ¼ş½¨Á¢Ò»¸öÖ÷Ìâ";
$lang['Empty_message_email'] = "Äú±ØĞë¸øµç×ÓÓÊ¼şÌîĞ´ÄÚÈİ";


//
// Memberslist
//
$lang['Select_sort_method'] = "ÇëÑ¡ÔñÒ»ÖÖÅÅĞò·½·¨";
$lang['Sort'] = "ÅÅÁĞ";
$lang['Sort_Top_Ten'] = "»îÔ¾Ç°Ê®";
$lang['Sort_Joined'] = "×¢²áÈÕÆÚ";
$lang['Sort_Username'] = "ÓÃ»§Ãû³Æ";
$lang['Sort_Location'] = "À´×ÔµØÇø";
$lang['Sort_Posts'] = "·¢Ìû×ÜÊı";
$lang['Sort_Email'] = "µç×ÓÓÊ¼ş";
$lang['Sort_Website'] = "¸öÈËÖ÷Ò³";
$lang['Sort_Ascending'] = "ÉıĞò";
$lang['Sort_Descending'] = "½µĞò";
$lang['Order'] = "Ë³Ğò";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "ÍÅ¶Ó¿ØÖÆÃæ°å";
$lang['Group_member_details'] = "ÍÅ¶Ó³ÉÔ±Ï¸½Ú";
$lang['Group_member_join'] = "¼ÓÈëÒ»¸öÍÅ¶Ó";

$lang['Group_Information'] = "ÍÅ¶ÓĞÅÏ¢";
$lang['Group_name'] = "ÍÅ¶ÓÃû³Æ";
$lang['Group_description'] = "ÍÅ¶ÓÃèÊö";
$lang['Group_membership'] = "ÍÅ¶Ó³ÉÔ±";
$lang['Group_Members'] = "ÍÅ¶Ó³ÉÔ±";
$lang['Group_Moderator'] = "ÍÅ¶ÓÖ÷Ï¯";
$lang['Pending_members'] = "ÉóºËÖĞµÄ³ÉÔ±";

$lang['Group_type'] = "ÍÅ¶ÓÀàĞÍ";
$lang['Group_open'] = "¿ªÆôÍÅ¶Ó";
$lang['Group_closed'] = "¹Ø±ÕÍÅ¶Ó";
$lang['Group_hidden'] = "Òş²ØÍÅ¶Ó";

$lang['Current_memberships'] = "Ä¿Ç°ÄúËùÔÚµÄÍÅ¶Ó";
$lang['Non_member_groups'] = "Ã»ÓĞ³ÉÔ±µÄÍÅ¶Ó";
$lang['Memberships_pending'] = "ÄúÕıÔÚ±»ÉóºËÖĞµÄÍÅ¶Ó";

$lang['No_groups_exist'] = "Ã»ÓĞÍÅ¶Ó´æÔÚ";
$lang['Group_not_exist'] = "²»´æÔÚÕâ¸öÍÅ¶Ó";

$lang['Join_group'] = "¼ÓÈëÍÅ¶Ó";
$lang['No_group_members'] = "Õâ¸öÍÅ¶ÓÃ»ÓĞ³ÉÔ±";
$lang['Group_hidden_members'] = "Õâ¸öÍÅ¶Ó´¦ÓÚÒş²Ø×´Ì¬,Äú²»ÄÜ²é¿´ËüµÄ³ÉÔ±";
$lang['No_pending_group_members'] = "Õâ¸öÍÅ¶Ó²»´æÔÚÉóºËÖĞ³ÉÔ±";
$lang["Group_joined"] = "ÄúÒÑ¾­ÉêÇë¼ÓÈëÕâ¸öÍÅ¶Ó,<br />µ±ÄúµÄÉêÇëÍ¨¹ıÉóºËÄú½«ÊÜµ½ÌáĞÑ";
$lang['Group_request'] = "¼ÓÈëÕâ¸öÍÅ¶ÓµÄÉêÇëÒÑ¾­Ìá½»";
$lang['Group_approved'] = "ÄúµÄÉêÇëÒÑ¾­±»Åú×¼ÁË";
$lang['Group_added'] = "ÄúÒÑ¾­±»¼ÓÈëÕâ¸öÍÅ¶Ó"; 
$lang['Already_member_group'] = "ÄúÒÑ¾­ÊÇÕâ¸öÍÅ¶ÓµÄ³ÉÔ±";
$lang['User_is_member_group'] = "ÓÃ»§ÒÑ¾­ÊÇÕâ¸öÍÅ¶ÓµÄ³ÉÔ±";
$lang['Group_type_updated'] = "³É¹¦¸üĞÂÍÅ¶ÓÀàĞÍ";

$lang['Could_not_add_user'] = "ÄúÑ¡ÔñµÄÓÃ»§²»´æÔÚ";
$lang['Could_not_anon_user'] = "Äú²»ÄÜ½«ÄäÃûÓÎ¿ÍÁĞÎªÍÅ¶Ó³ÉÔ±";

$lang['Confirm_unsub'] = "ÄúÈ·¶¨Òª´ÓÕâ¸öÍÅ¶Ó½â³ıÉêÇëÂğ?";
$lang['Confirm_unsub_pending'] = "ÄúµÄÍÅ¶ÓÉêÇë»¹Ã»ÓĞ±»Åú×¼,ÄúÈ·¶¨Òª½â³ıÉêÇëÂğ?";

$lang['Unsub_success'] = "ÄúÒÑ¾­´ÓÕâ¸öÍÅ¶Ó½â³ıÁËÉêÇë.";

$lang['Approve_selected'] = "Ñ¡ÔñÅú×¼";
$lang['Deny_selected'] = "Ñ¡Ôñ¾Ü¾ø";
$lang['Not_logged_in'] = "¼ÓÈëÍÅ¶ÓÇ°Äú±ØĞëÊ×ÏÈµÇÂ½.";
$lang['Remove_selected'] = "Ñ¡ÔñÒÆ³ı";
$lang['Add_member'] = "Ôö¼Ó³ÉÔ±";
$lang['Not_group_moderator'] = "Äú²»ÊÇÕâ¸öÍÅ¶ÓµÄ¹ÜÀíÔ±,ÄúÎŞ·¨Ö´ĞĞÍÅ¶ÓµÄ¹ÜÀí¹¦ÄÜ.";

$lang['Login_to_join'] = "ÇëµÇÂ½¼ÓÈë»òÕß¹ÜÀíÍÅ¶Ó³ÉÔ±";
$lang['This_open_group'] = "ÕâÊÇÒ»¸ö¿ª·ÅµÄÍÅ¶Ó,µã»÷ÉêÇë³ÉÔ±";
$lang['This_closed_group'] = "ÕâÊÇÒ»¸ö¹Ø±ÕµÄÍÅ¶Ó,²»½ÓÊÜĞÂµÄ³ÉÔ±";
$lang['This_hidden_group'] = "ÕâÊÇÒ»¸öÒş²ØµÄÍÅ¶Ó,²»ÈİĞí×Ô¶¯Ôö¼Ó³ÉÔ±";
$lang['Member_this_group'] = "ÄúÊÇÕâ¸öÍÅ¶ÓµÄ³ÉÔ±";
$lang['Pending_this_group'] = "ÄúµÄÉêÇëÕıÔÚÉóºËÖĞ";
$lang['Are_group_moderator'] = "ÄúÊÇÍÅ¶Ó¹ÜÀíÔ±";
$lang['None'] = "Ã»ÓĞ";

$lang['Subscribe'] = "ÉêÇë";
$lang['Unsubscribe'] = "½â³ıÉêÇë";
$lang['View_Information'] = "ÔÄÀÀÏ¸½Ú";


//
// Search
//
$lang['Search_query'] = "ËÑË÷Ä¿±ê";
$lang['Search_options'] = "ËÑË÷Ñ¡Ïî";

$lang['Search_keywords'] = "ËÑË÷¹Ø¼ü×Ö";
$lang['Search_keywords_explain'] = "Äú¿ÉÒÔÊ¹ÓÃ<u>AND</u>À´±ê¼ÇÄúÏ£Íû½á¹ûÀï±ØĞë³öÏÖµÄ¹Ø¼ü×Ö,»òÕßÊ¹ÓÃ<u>OR</u>À´±ê¼ÇÄúÏ£Íû½á¹ûÀï¿ÉÄÜ³öÏÖµÄ¹Ø¼ü×ÖºÍ<u>NOT</u>À´±ê¼ÇÄú²»Ï£Íû½á¹ûÀï³öÏÖµÄ¹Ø¼ü×Ö.Äú¿ÉÒÔÊ¹ÓÃÍ¨Åä·û*±ê¼ÇÅúÁ¿·ûºÏµÄ½á¹û";
$lang['Search_author'] = "ËÑË÷×÷Õß";
$lang['Search_author_explain'] = "Äú¿ÉÒÔÊ¹ÓÃÍ¨Åä·û*±ê¼ÇÅúÁ¿·ûºÏµÄ½á¹û";

$lang['Search_for_any'] = "ËÑË÷ÈÎÒâµÄÄÚÈİ»òÕßÄúÌá¹©µÄËÑË÷Ä¿±ê";
$lang['Search_for_all'] = "ËÑË÷ËùÓĞµÄÄÚÈİ";

$lang['Return_first'] = "ÏÔÊ¾×îÏÈµÄ"; // followed by xxx characters in a select box
$lang['characters_posts'] = "¸ö·ûºÏµÄÏîÄ¿";

$lang['Search_previous'] = "ËÑË÷ÒÔÇ°µÄÌû×Ó"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "ÅÅĞò·½·¨";
$lang['Sort_Time'] = "·¢±íÊ±¼ä";
$lang['Sort_Post_Subject'] = "·¢±íÖ÷Ìâ";
$lang['Sort_Topic_Title'] = "Ìû×Ó±êÌâ";
$lang['Sort_Author'] = "×÷Õß";
$lang['Sort_Forum'] = "ÂÛÌ³";

$lang['Display_results'] = "ÏÔÊ¾½á¹ûµÄ";
$lang['All_available'] = "ËùÓĞÂÛÌ³";
$lang['No_searchable_forums'] = "ÄúÃ»ÓĞËÑË÷ËùÓĞËùÓĞÂÛÌ³µÄÈ¨ÏŞ";

$lang['No_search_match'] = "Ã»ÓĞ·ûºÏÄúÒªÇóµÄÖ÷Ìâ»òÌû×Ó";
$lang['Found_search_match'] = "ËÑË÷µ½ %d ¸ö·ûºÏµÄÄÚÈİ"; // eg. Search found 1 match
$lang['Found_search_matches'] = "ËÑË÷µ½ %d ¸ö·ûºÏµÄÄÚÈİ"; // eg. Search found 24 matches

$lang['Close_window'] = "¹Ø±Õ´°¿Ú";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "¶Ô²»ÆğÖ»ÓĞ %s ¿ÉÒÔÔÚÕâ¸öÂÛÌ³·¢±í¹«¸æ";
$lang['Sorry_auth_sticky'] = "¶Ô²»ÆğÖ»ÓĞ %s ¿ÉÒÔÔÚÕâ¸öÂÛÌ³·¢±íÖÃ¶¥"; 
$lang['Sorry_auth_read'] = "¶Ô²»ÆğÖ»ÓĞ %s ¿ÉÒÔÔÚÕâ¸öÂÛÌ³ä¯ÀÀÖ÷Ìâ"; 
$lang['Sorry_auth_post'] = "¶Ô²»ÆğÖ»ÓĞ %s ¿ÉÒÔÔÚÕâ¸öÂÛÌ³·¢±íÖ÷Ìâ"; 
$lang['Sorry_auth_reply'] = "¶Ô²»ÆğÖ»ÓĞ %s ¿ÉÒÔÔÚÕâ¸öÂÛÌ³»Ø¸´Ö÷Ìâ"; 
$lang['Sorry_auth_edit'] = "¶Ô²»ÆğÖ»ÓĞ %s ¿ÉÒÔÔÚÕâ¸öÂÛÌ³±à¼­Ö÷Ìâ"; 
$lang['Sorry_auth_delete'] = "¶Ô²»ÆğÖ»ÓĞ %s ¿ÉÒÔÔÚÕâ¸öÂÛÌ³É¾³ıÖ÷Ìâ"; 
$lang['Sorry_auth_vote'] = "¶Ô²»ÆğÖ»ÓĞ %s ¿ÉÒÔÔÚÕâ¸öÂÛÌ³·¢±íÍ¶Æ±"; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>ÄäÃûÓÎ¿Í</b>";
$lang['Auth_Registered_Users'] = "<b>×¢²áÓÃ»§</b>";
$lang['Auth_Users_granted_access'] = "<b>ÌØÈ¨ÓÃ»§</b>";
$lang['Auth_Moderators'] = "<b>°ßÖñ</b>";
$lang['Auth_Administrators'] = "<b>¹ÜÀíÔ±</b>";

$lang['Not_Moderator'] = "Äú²»ÊÇÕâ¸öÂÛÌ³µÄ°ßÖñ";
$lang['Not_Authorised'] = "Ã»ÓĞÊÚÈ¨";

$lang['You_been_banned'] = "Õâ¸öÂÛÌ³ÒÑ¾­½ûÖ¹Äú·ÃÎÊ<br />ÇëÁªÂçÂÛÌ³¹ÜÀíÔ±ÁË½âÏ¸½Ú";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "ÏÖÔÚÓĞ 0 Î»×¢²áÓĞ»§ºÍ "; // There ae 5 Registered and
$lang['Reg_users_online'] = "ÏÖÔÚÓĞ %d Î»×¢²áÓĞ»§ºÍ "; // There ae 5 Registered and
$lang['Reg_user_online'] = "ÏÖÔÚÓĞ %d Î»×¢²áÓĞ»§ºÍ "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "0 Î»ÒşÉíÓÃ»§ºÍ"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d Î»ÒşÉíÓÃ»§ÔÚÏß"; // 6 Hidden users online
$lang['Hidden_user_online'] = "%d Î»ÒşÉíÓÃ»§ÔÚÏß"; // 6 Hidden users online
$lang['Guest_users_online'] = "ÏÖÔÚÓĞ %d Î»ÓÎ¿ÍÔÚÏß"; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = "ÏÖÔÚÓĞ 0 Î»×¢²áÓÃ»§ÔÚÏß"; // There are 10 Guest users online
$lang['Guest_user_online'] = "ÏÖÔÚÓĞ %d Î»ÓÎ¿ÍÔÚÏß"; // There is 1 Guest user online
$lang['No_users_browsing'] = "ÏÖÔÚÃ»ÓĞÓÃ»§ÔÚÕâ¸öÂÛÌ³ä¯ÀÀ";

$lang['Online_explain'] = "ÕâÊÇ5·ÖÖÓÖ®ÄÚµÄÂÛÌ³ÔÚÏßÇé¿ö";

$lang['Forum_Location'] = "ÂÛÌ³Î»ÖÃ";
$lang['Last_updated'] = "×î½ü¸üĞÂ";

$lang['Forum_index'] = "ÂÛÌ³Ê×Ò³";
$lang['Logging_on'] = "µÇÂ½";
$lang['Posting_message'] = "·¢±íÌû×Ó";
$lang['Searching_forums'] = "ËÑË÷ÂÛÌ³";
$lang['Viewing_profile'] = "ä¯ÀÀ¸öÈË×ÊÁÏ";
$lang['Viewing_online'] = "ä¯ÀÀÔÚÏßÇé¿ö";
$lang['Viewing_member_list'] = "ä¯ÀÀ³ÉÔ±ÁĞ±í";
$lang['Viewing_priv_msgs'] = "ä¯ÀÀÕ¾ÄÚĞÅ¼ş";
$lang['Viewing_FAQ'] = "ä¯ÀÀ³£¼ûÎÊÌâ´ğ¼¯";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "°ßÖñ¿ØÖÆÃæ°å";
$lang['Mod_CP_explain'] = "Ê¹ÓÃÒÔÏÂµÄÑ¡ÏîÄú¿ÉÒÔÔÚÕâ¸öÂÛÌ³ÔËĞĞ´ó²¿·ÖÊÊÁ¿µÄ²Ù×÷. Äú¿ÉÒÔËø¶¨,½âËø, ÒÆ¶¯»òÕßÉ¾³ıÈÎÒâÊıÁ¿µÄÖ÷Ìâ.";

$lang['Select'] = "Ñ¡Ôñ";
$lang['Delete'] = "É¾³ı";
$lang['Move'] = "ÒÆ¶¯";
$lang['Lock'] = "Ëø¶¨";
$lang['Unlock'] = "½âËø";

$lang['Topics_Removed'] = "Ñ¡ÔñµÄÖ÷ÌâÒÑ¾­³É¹¦µØ´ÓÊı¾İ¿âÖĞÉ¾³ı.";
$lang['Topics_Locked'] = "Ñ¡ÔñµÄÖ÷ÌâÒÑ¾­³É¹¦µÄ±»Ëø¶¨";
$lang['Topics_Moved'] = "Ñ¡ÔñµÄÖ÷ÌâÒÑ¾­³É¹¦µÄ±»ÒÆ¶¯";
$lang['Topics_Unlocked'] = "Ñ¡ÔñµÄÖ÷ÌâÒÑ¾­³É¹¦µÄ±»½âËø";
$lang['No_Topics_Moved'] = "Ã»ÓĞÖ÷Ìâ±»ÒÆ¶¯";

$lang['Confirm_delete_topic'] = "ÄúÈ·¶¨ÒªÉ¾³ıÑ¡ÔñµÄÖ÷ÌâÂğ?";
$lang['Confirm_lock_topic'] = "ÄúÈ·¶¨ÒªËø¶¨Ñ¡ÔñµÄÖ÷ÌâÂğ?";
$lang['Confirm_unlock_topic'] = "ÄúÈ·¶¨Òª½âËøÑ¡ÔñµÄÖ÷ÌâÂğ?";
$lang['Confirm_move_topic'] = "ÄúÈ·¶¨ÒªÒÆ¶¯Ñ¡ÔñµÄÖ÷ÌâÂğ?";

$lang['Move_to_forum'] = "ÒÆ¶¯µ½ÁíÒ»¸öÂÛÌ³";
$lang['Leave_shadow_topic'] = "¸´ÖÆÖ÷Ìâ±£ÁôÔÚ¾ÉÂÛÌ³";

$lang['Split_Topic'] = "·Ö¸ôÖ÷Ìâ¿ØÖÆÃæ°å";
$lang['Split_Topic_explain'] = "Ê¹ÓÃÒÔÏÂµÄÑ¡ÏîÄú¿ÉÒÔ·Ö¸îÌû×Ó±ä³ÉÁ½¸ö,Äú¿ÉÒÔÑ¡Ôñ·Ö¸îÒ»¸ö»ò¶à¸öÌû×Ó";
$lang['Split_title'] = "ĞÂÖ÷ÌâÃû";
$lang['Split_forum'] = "Òª·Ö¸îÖ÷Ìâµ½ĞÂµÄÂÛÌ³";
$lang['Split_posts'] = "·Ö¸îÑ¡ÔñµÄÌû×Ó";
$lang['Split_after'] = "·Ö¸î×ÔÑ¡ÔñÒÔÏÂµÄÌû×Ó(°üº¬Ñ¡ÔñµÄÌû×Ó)";
$lang['Topic_split'] = "Ñ¡ÔñµÄÌû×ÓÒÑ¾­³É¹¦µØ±»·Ö¸î";

$lang['Too_many_error'] = "ÄúÑ¡ÔñÁËÌ«¶àµÄÌû×Ó.ÄúÖ»ÄÜÑ¡ÔñÒ»¸öÌû×ÓÀ´·Ö¸îÒÔÏÂµÄÌû×Ó!";

$lang['None_selected'] = "ÄúÃ»ÓĞÑ¡ÔñÈÎºÎµÄÌû×ÓÀ´ÔËĞĞÕâ¸ö²Ù×÷.ÇëºóÍËÑ¡ÔñÖÁÉÙÒ»¸öÌû×Ó.";
$lang['New_forum'] = "ĞÂÂÛÌ³";

$lang['This_posts_IP'] = "Õâ¸öÌû×ÓµÄIPµØÖ·";
$lang['Other_IP_this_user'] = "Õâ¸ö×÷ÕßµÄÆäËûµÄµØÖ·";
$lang['Users_this_IP'] = "À´´ÓÕâ¸öIPµÄÓÃ»§";
$lang['IP_info'] = "IPµØÖ·ĞÅÏ¢";
$lang['Lookup_IP'] = "ËÑË÷IPµØÖ·";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "ÂÛÌ³Ê±¼äÎª %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 Hours";
$lang['-11'] = "GMT - 11 Hours";
$lang['-10'] = "HST (ÏÄÍşÒÄ)";
$lang['-9'] = "GMT - 9 Hours";
$lang['-8'] = "PST (ÃÀ¹ú/¼ÓÄÃ´ó)";
$lang['-7'] = "MST (ÃÀ¹ú/¼ÓÄÃ´ó)";
$lang['-6'] = "CST (ÃÀ¹ú/¼ÓÄÃ´ó)";
$lang['-5'] = "EST (ÃÀ¹ú/¼ÓÄÃ´ó)";
$lang['-4'] = "GMT - 4 Hours";
$lang['-3.5'] = "GMT - 3.5 Hours";
$lang['-3'] = "GMT - 3 Hours";
$lang['-2'] = "ÖĞ´óÎ÷Ñó";
$lang['-1'] = "GMT - 1 Hours";
$lang['0'] = "GMT";
$lang['1'] = "CET (Å·ÖŞ)";
$lang['2'] = "EET (Å·ÖŞ)";
$lang['3'] = "GMT + 3 Hours";
$lang['3.5'] = "GMT + 3.5 Hours";
$lang['4'] = "GMT + 4 Hours";
$lang['4.5'] = "GMT + 4.5 Hours";
$lang['5'] = "GMT + 5 Hours";
$lang['5.5'] = "GMT + 5.5 Hours";
$lang['6'] = "GMT + 6 Hours";
$lang['7'] = "GMT + 7 Hours";
$lang['8'] = "±±¾©Ê±¼ä";
$lang['9'] = "GMT + 9 Hours";
$lang['9.5'] = "CST (°Ä´óÀûÑÇ)";
$lang['10'] = "EST (°Ä´óÀûÑÇ)";
$lang['11'] = "GMT + 11 Hours";
$lang['12'] = "GMT + 12 Hours";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT -12:00 hours) °£ÄáÍşÍĞ¿Ëµº, ¿ä¼ÖÁÖµº";
$lang['tz']['-11'] = "(GMT -11:00 hours) ÖĞÍ¾µº, ÈøÄ¦ÑÇÈºµº";
$lang['tz']['-10'] = "(GMT -10:00 hours) ÏÄÍşÒÄÖİ";
$lang['tz']['-9'] = "(GMT -9:00 hours) °¢À­Ë¹¼ÓÖİ";
$lang['tz']['-8'] = "(GMT -8:00 hours) Ì«Æ½ÑóÊ±¼ä (ÃÀ¹ú &amp; ¼ÓÄÃ´ó), Ìá»ªÄÉ";
$lang['tz']['-7'] = "(GMT -7:00 hours) É½µØ±ê×¼Ê±¼ä (ÃÀ¹ú &amp; ¼ÓÄÃ´ó), ÑÇÀûÉ£ÄÇÖİ";
$lang['tz']['-6'] = "(GMT -6:00 hours) ÖĞÇøÊ± (ÃÀ¹ú &amp; ¼ÓÄÃ´ó), Ä«Î÷¸ç³Ç";
$lang['tz']['-5'] = "(GMT -5:00 hours) ¶«²¿Ê±¼ä (ÃÀ¹ú &amp; ¼ÓÄÃ´ó), ²¨¸ç´ó, ÀûÂí, »ù¶à";
$lang['tz']['-4'] = "(GMT -4:00 hours) ´óÎ÷ÑóÊ±¼ä (¼ÓÄÃ´ó), ¼ÓÀ­¼ÓË¹, À­°ÍË¹";
$lang['tz']['-3.5'] = "(GMT -3:30 hours) Å¦·ÒÀ¼";
$lang['tz']['-3'] = "(GMT -3:00 hours) °ÍÎ÷, ²¼ÒËÅµË¹°¬ÀûË¹, ÇÇÖÎ¶Ù, ¸£¿ËÀ¼Èºµº";
$lang['tz']['-2'] = "(GMT -2:00 hours) ÖĞ´óÎ÷Ñó, ÑÇÉ­ËÉµº, Ê¥ºÕÀÕÄú";
$lang['tz']['-1'] = "(GMT -1:00 hours) ÑÇËÙ¶ûÈºµº, Î¬µÂ½Çµº";
$lang['tz']['0'] = "(GMT) ¿¨Èø²¼À¼¿¨, ¶¼°ØÁÖ, °®¶¡±¤, Â×¶Ø, ÀïË¹±¾, ÃÉÂŞÎ¬ÑÇ";
$lang['tz']['1'] = "(GMT +1:00 hours) °¢Ä·Ë¹ÌØµ¤, °ØÁÖ, ²¼Â³Èû¶û, ¸ç±¾¹ş¸ù, ÂíµÂÀï, °ÍÀè, ÂŞÂí";
$lang['tz']['2'] = "(GMT +2:00 hours) ¿ªÂŞ, ºÕ¶ûĞÁ»ù, ¼ÓÀïÄş¸ñÀÕ, ÄÏ·Ç";
$lang['tz']['3'] = "(GMT +3:00 hours) °Í¸ñ´ï, ÀûÑÅµÂ, ÄªË¹¿Æ, ÄÚÂŞ±Ï";
$lang['tz']['3.5'] = "(GMT +3:30 hours) µÂºÚÀ¼";
$lang['tz']['4'] = "(GMT +4:00 hours) °¢²¼Ôú±È, °Í¿â, ÂíË¹¿¦ÌØ, µÚ±ÈÀûË¹";
$lang['tz']['4.5'] = "(GMT +4:30 hours) ¿¦²¼¶û";
$lang['tz']['5'] = "(GMT +5:00 hours) ÒÁ¿¨ÌØÁÕ±¤, ÒÁË¹À¼±¤, ¿¨À­Ææ, ËşÊ²¸É";
$lang['tz']['5.5'] = "(GMT +5:30 hours) ÃÏÂò, ¼Ó¶û¸÷´ğ, ÂíµÂÀ­Ë¹, ĞÂµÂÀï";
$lang['tz']['6'] = "(GMT +6:00 hours) °¢ÃÉÌá, ¿ÆÂ×ÆÂ, ´ï¿¨£¬ĞÂÎ÷²®ÀûÑÇ";
$lang['tz']['6.5'] = "(GMT +6:30 hours) Ñö¹â";
$lang['tz']['7'] = "(GMT +7:00 hours) Âü¹È, ºÓÄÚ, ÑÅ¼Ó´ï";
$lang['tz']['8'] = "(GMT +8:00 hours) ±±¾©, Ïã¸Û, ÅåË¼, ĞÂ¼ÓÆÂ, Ì¨±±";
$lang['tz']['9'] = "(GMT +9:00 hours) ´óÚæ, Ôı»Ï, ºº³Ç, ¶«¾©, ÑÅ¿â´Ä¿Ë";
$lang['tz']['9.5'] = "(GMT +9:30 hours) °¢µÃÀ×µÂ, ´ï¶ûÎÄ";
$lang['tz']['10'] = "(GMT +10:00 hours) ¿°ÅàÀ­£¬¹Øµº£¬Äª¶û±¾, Ï¤Äá, ·ûÀ­µÏÎÖË¹ÍĞ¿Ë";
$lang['tz']['11'] = "(GMT +11:00 hours) Âí¼Óµ¤, ĞÂ¿¨Àï¶àÄáÑÇ, ËùÂŞÃÅÈºµº";
$lang['tz']['12'] = "(GMT +12:00 hours) °Â¿ËÀ¼, ÍşÁé¶Ù, ì³¼Ã, ÂíĞª¶ûÈºµº";

$lang['datetime']['Sunday'] = "ĞÇÆÚÈÕ";
$lang['datetime']['Monday'] = "ĞÇÆÚÒ»";
$lang['datetime']['Tuesday'] = "ĞÇÆÚ¶ş";
$lang['datetime']['Wednesday'] = "ĞÇÆÚÈı";
$lang['datetime']['Thursday'] = "ĞÇÆÚËÄ";
$lang['datetime']['Friday'] = "ĞÇÆÚÎå";
$lang['datetime']['Saturday'] = "ĞÇÆÚÁù";
$lang['datetime']['Sun'] = "ĞÇÆÚÈÕ";
$lang['datetime']['Mon'] = "ĞÇÆÚÒ»";
$lang['datetime']['Tue'] = "ĞÇÆÚ¶ş";
$lang['datetime']['Wed'] = "ĞÇÆÚÈı";
$lang['datetime']['Thu'] = "ĞÇÆÚËÄ";
$lang['datetime']['Fri'] = "ĞÇÆÚÎå";
$lang['datetime']['Sat'] = "ĞÇÆÚÁù";
$lang['datetime']['January'] = "Ò»ÔÂ";
$lang['datetime']['February'] = "¶şÔÂ";
$lang['datetime']['March'] = "ÈıÔÂ";
$lang['datetime']['April'] = "ËÄÔÂ";
$lang['datetime']['May'] = "ÎåÔÂ";
$lang['datetime']['June'] = "ÁùÔÂ";
$lang['datetime']['July'] = "ÆßÔÂ";
$lang['datetime']['August'] = "°ËÔÂ";
$lang['datetime']['September'] = "¾ÅÔÂ";
$lang['datetime']['October'] = "Ê®ÔÂ";
$lang['datetime']['November'] = "Íîÿáğü";
$lang['datetime']['December'] = "Ê®¶şÔÂ";
$lang['datetime']['Jan'] = "Ò»ÔÂ";
$lang['datetime']['Feb'] = "¶şÔÂ";
$lang['datetime']['Mar'] = "ÈıÔÂ";
$lang['datetime']['Apr'] = "ËÄÔÂ";
$lang['datetime']['May'] = "ÎåÔÂ";
$lang['datetime']['Jun'] = "ÁùÔÂ";
$lang['datetime']['Jul'] = "ÆßÔÂ";
$lang['datetime']['Aug'] = "°ËÔÂ";
$lang['datetime']['Sep'] = "¾ÅÔÂ";
$lang['datetime']['Oct'] = "Ê®ÔÂ";
$lang['datetime']['Nov'] = "Ê®Ò»ÔÂ";
$lang['datetime']['Dec'] = "Ê®¶şÔÂ";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "ÏûÏ¢ÌáÊ¾";
$lang['Critical_Information'] = "¹Ø¼üĞÅÏ¢";

$lang['General_Error'] = "ÆÕÍ¨´íÎó";
$lang['Critical_Error'] = "¹Ø¼ü´íÎó";
$lang['An_error_occured'] = "·¢ÉúÁËÒ»¸ö´íÎó";
$lang['A_critical_error'] = "·¢ÉúÁËÒ»¸ö¹Ø¼üĞÔ´íÎó";

//
// That's all Folks!
// -------------------------------------------------

?>