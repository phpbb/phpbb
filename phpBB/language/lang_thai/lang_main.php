<?php
/***************************************************************************
 *                            lang_main.php [Thai]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     Translation: Mr.LonelyWinter < mr_lonely_winter@yahoo.com > 
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

$lang['ENCODING'] = 'windows-874';
$lang['DIRECTION'] = 'ltr';
$lang['LEFT'] = 'left';
$lang['RIGHT'] = 'right';
$lang['DATE_FORMAT'] =  'd M Y'; // This should be changed to the default date format for your language, php date() format

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.
// $lang['TRANSLATION'] = '';

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = 'Forum';
$lang['Category'] = 'กลุ่ม';
$lang['Topic'] = 'หัวข้อ';
$lang['Topics'] = 'หัวข้อ';
$lang['Replies'] = 'ตอบ';
$lang['Views'] = 'อ่าน';
$lang['Post'] = 'ตอบ';
$lang['Posts'] = 'ตอบ';
$lang['Posted'] = 'ตอบเมื่อ';
$lang['Username'] = 'Username';
$lang['Password'] = 'Password';
$lang['Email'] = 'Email';
$lang['Poster'] = 'ผู้ตอบ';
$lang['Author'] = 'ผู้ตั้ง';
$lang['Time'] = 'เวลา';
$lang['Hours'] = 'ชั่วโมง';
$lang['Message'] = 'ข้อความ';

$lang['1_Day'] = '1 วัน';
$lang['7_Days'] = '7 วัน';
$lang['2_Weeks'] = '2 อาทิตย์';
$lang['1_Month'] = '1 เดือน';
$lang['3_Months'] = '3 เดือน';
$lang['6_Months'] = '6 เดือน';
$lang['1_Year'] = '1 ปี';

$lang['Go'] = 'ไป';
$lang['Jump_to'] = 'ไปที่';
$lang['Submit'] = 'ส่ง(Submit)';
$lang['Reset'] = 'ล้าง(Reset)';
$lang['Cancel'] = 'ยกเลิก';
$lang['Preview'] = 'แสดงตัวอย่าง';
$lang['Confirm'] = 'ยืนยัน';
$lang['Spellcheck'] = 'ตรวจการสะกด';
$lang['Yes'] = 'ใช่';
$lang['No'] = 'ไม่ใช่';
$lang['Enabled'] = 'ใช้ได้';
$lang['Disabled'] = 'ใช้ไม่ได้';
$lang['Error'] = 'ผิดพลาด';

$lang['Next'] = 'ถัดไป';
$lang['Previous'] = 'ก่อนหน้า';
$lang['Goto_page'] = 'ไปที่หน้า';
$lang['Joined'] = 'เข้าร่วม';
$lang['IP_Address'] = 'หมายเลข IP';

$lang['Select_forum'] = 'เลือก forum';
$lang['View_latest_post'] = 'ดูข้อความล่าสุด';
$lang['View_newest_post'] = 'ดูข้อความใหม่สุด';
$lang['Page_of'] = 'หน้า <b>%d</b> จาก <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'หมายเลข ICQ';
$lang['AIM'] = 'ตำแหน่ง AIM';
$lang['MSNM'] = 'MSN Messenger';
$lang['YIM'] = 'Yahoo Messenger';

$lang['Forum_Index'] = '%s';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = 'สร้างหัวข้อใหม่';
$lang['Reply_to_topic'] = 'ตอบ';
$lang['Reply_with_quote'] = 'ตอบโดยอ้างข้อความ';

$lang['Click_return_topic'] = 'คลิก %sที่นี่%s เพื่อกลับไปที่หัวข้อ'; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = 'คลิก %sที่นี่%s เพื่อกลับไปลองใหม่';
$lang['Click_return_forum'] = 'คลิก %sที่นี่%s เพื่อกลับไป forum';
$lang['Click_view_message'] = 'คลิก %sที่นี่%s เพื่อดูข้อความของคุณ';
$lang['Click_return_modcp'] = 'คลิก %sที่นี่%s เพื่อกลับไป Moderator Control Panel';
$lang['Click_return_group'] = 'คลิก %sที่นี่%s เพื่อกลับไปดูข้อมูลกลุ่ม';

$lang['Admin_panel'] = 'ไป Administration Panel';

$lang['Board_disable'] = 'ขออภัย. บอร์ดนี้ใช้งานไม่ได้ชั่วคราว กรุณาลองใหม่อีกครั้ง';


//
// Global Header strings
//
$lang['Registered_users'] = 'ผู้ใช้ที่ลงทะเบียน:';
$lang['Browsing_forum'] = 'ผู้ที่กำลังอ่าน forum นี้:';
$lang['Online_users_zero_total'] = 'ผู้ที่ online ทั้งหมด <b>0</b> คน :: ';
$lang['Online_users_total'] = 'ผู้ที่ online ทั้งหมด <b>%d</b> คน :: ';
$lang['Online_user_total'] = 'ผู้ที่ online ทั้งหมด <b>%d</b> คน :: ';
$lang['Reg_users_zero_total'] = 'ลงทะเบียน 0 คน, ';
$lang['Reg_users_total'] = 'ลงทะเบียน %d คน, ';
$lang['Reg_user_total'] = 'ลงทะเบียน %d คน, ';
$lang['Hidden_users_zero_total'] = 'ซ่อน 0 คน และ ';
$lang['Hidden_user_total'] = 'ซ่อน %d คน และ ';
$lang['Hidden_users_total'] = 'ซ่อน %d คน และ ';
$lang['Guest_users_zero_total'] = '%d ผู้เยี่ยมชม';
$lang['Guest_users_total'] = '%d ผู้เยี่ยมชม';
$lang['Guest_user_total'] = '%d ผู้เยี่ยมชม';
$lang['Record_online_users'] = 'ผู้ใช้ online ล่าสุด <b>%s</b>คน เมื่อ %s'; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = '%sAdministrator%s';
$lang['Mod_online_color'] = '%sModerator%s';

$lang['You_last_visit'] = 'ทำงานล่าสุด %s'; // %s replaced by date/time
$lang['Current_time'] = 'เวลาขณะนี้ %s'; // %s replaced by time

$lang['Search_new'] = 'ดูข้อความล่าสุดจากครั้งก่อน';
$lang['Search_your_posts'] = 'ดูข้อความของคุณ';
$lang['Search_unanswered'] = 'ดูข้อความที่ยังไม่ได้ตอบ';

$lang['Register'] = 'สมัครสมาชิก(Register)';
$lang['Profile'] = 'ข้อมูลส่วนตัว(Profile)';
$lang['Edit_profile'] = 'แก้ไขข้อมูลส่วนตัว';
$lang['Search'] = 'ค้นหา';
$lang['Memberlist'] = 'รายชื่อสมาชิก';
$lang['FAQ'] = 'ช่วยเหลือ';
$lang['BBCode_guide'] = 'วิธีใช้ BBCode';
$lang['Usergroups'] = 'กลุ่มผู้ใช้';
$lang['Last_Post'] = 'ตอบล่าสุด';
$lang['Moderator'] = 'Moderator';
$lang['Moderators'] = 'Moderators';


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = '<b>ไม่มี</b>การ post หัวข้อ'; // Number of posts
$lang['Posted_articles_total'] = 'Post ทั้งหมด <b>%d</b> หัวข้อ'; // Number of posts
$lang['Posted_article_total'] = 'Post ทั้งหมด <b>%d</b> หัวข้อ'; // Number of posts
$lang['Registered_users_zero_total'] = 'ยังไม่มีสมาชิก'; // # registered users
$lang['Registered_users_total'] = 'สมาชิกทั้งหมด <b>%d</b> คน'; // # registered users
$lang['Registered_user_total'] = 'สมาชิกทั้งหมด <b>%d</b> คน'; // # registered users
$lang['Newest_user'] = 'สมาชิกล่าสุดคือ <b>%s%s%s</b>'; // a href, username, /a 

$lang['No_new_posts_last_visit'] = 'ไม่มีข้อความใหม่จากครั้งก่อน';
$lang['No_new_posts'] = 'ไม่มีข้อความใหม่';
$lang['New_posts'] = 'ข้อความใหม่';
$lang['New_post'] = 'ข้อความใหม่';
$lang['No_new_posts_hot'] = 'ไม่มีข้อความใหม่ [ Popular ]';
$lang['New_posts_hot'] = 'ข้อความใหม่ [ Popular ]';
$lang['No_new_posts_locked'] = 'ไม่มีข้อความใหม่ [ Locked ]';
$lang['New_posts_locked'] = 'ข้อความใหม่ [ Locked ]';
$lang['Forum_is_locked'] = 'Forum นี้ถูกล็อก';


//
// Login
//
$lang['Enter_password'] = 'กรุณาป้อน username และ password เพื่อเข้าสู่ระบบ';
$lang['Login'] = 'เข้าสู่ระบบ(Log in)';
$lang['Logout'] = 'ออกจากระบบ';

$lang['Forgotten_password'] = 'ลืม(forget) password';

$lang['Log_me_in'] = 'เข้าสู่ระบบโดยอัตโนมัติทุกครั้ง(Log in automatically)';

$lang['Error_login'] = 'คุณกรอก username และ/หรือ password ผิด(Invalid username and/or password.)';


//
// Index page
//
$lang['Index'] = 'Index';
$lang['No_Posts'] = 'ไม่มีข้อความ post';
$lang['No_forums'] = 'บอร์ดนี้ไม่มี forum';

$lang['Private_Message'] = 'ข้อความส่วนตัว';
$lang['Private_Messages'] = 'ข้อความส่วนตัว';
$lang['Who_is_Online'] = 'ผู้ที่กำลัง online';

$lang['Mark_all_forums'] = 'บันทึกว่าอ่านทุก forum หมดแล้ว';
$lang['Forums_marked_read'] = 'ทุก forum ถูกบันทึกว่าอ่านหมดแล้ว';


//
// Viewforum
//
$lang['View_forum'] = 'ดู forum';

$lang['Forum_not_exist'] = 'ไม่พบ forum ที่คุณเลือก';
$lang['Reached_on_error'] = 'You have reached this page in error';

$lang['Display_topics'] = 'เรียงลำดับก่อนหน้า';
$lang['All_Topics'] = 'ทุกหัวข้อ';

$lang['Topic_Announcement'] = '<b>ประกาศ::</b>';
$lang['Topic_Sticky'] = '<b>Sticky:</b>';
$lang['Topic_Moved'] = '<b>ย้ายแล้ว::</b>';
$lang['Topic_Poll'] = '<b>[ สำรวจ ]</b>';

$lang['Mark_all_topics'] = 'บันทึกว่าอ่านทุกหัวข้อแล้ว';
$lang['Topics_marked_read'] = 'ทุกหัวข้อถูกบันทึกว่าอ่านแล้ว';

$lang['Rules_post_can'] = 'คุณ<b>สามารถ</b>สร้างหัวข้อใหม่ได้';
$lang['Rules_post_cannot'] = 'คุณ<b>ไม่สามารถ</b>สร้างหัวข้อใหม่';
$lang['Rules_reply_can'] = 'คุณ<b>สามารถ</b>พิมพ์ตอบได้';
$lang['Rules_reply_cannot'] = 'คุณ<b>ไม่สามารถ</b>พิมพ์ตอบ';
$lang['Rules_edit_can'] = 'คุณ<b>สามารถ</b>แก้ไขข้อความของคุณได้';
$lang['Rules_edit_cannot'] = 'คุณ<b>ไม่สามารถ</b>แก้ไขข้อความของคุณ';
$lang['Rules_delete_can'] = 'คุณ<b>สามารถ</b>ลบข้อความของคุณได้';
$lang['Rules_delete_cannot'] = 'คุณ<b>ไม่สามารถ</b>ลบข้อความของคุณ';
$lang['Rules_vote_can'] = 'คุณ<b>สามารถ</b>ลงคะแนนได้';
$lang['Rules_vote_cannot'] = 'คุณ<b>ไม่สามารถ</b>ลงคะแนน';
$lang['Rules_moderate'] = 'คุณ<b>สามารถ</b> %sจัดการ forum นี้%s ได้'; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = 'ไม่มีข้อความใน forum นี้<br />คลิกที่ <b>สร้างหัวข้อใหม่</b>';


//
// Viewtopic
//
$lang['View_topic'] = 'อ่าน';

$lang['Guest'] = 'ผู้เยี่ยมชม';
$lang['Post_subject'] = 'เรื่อง';
$lang['View_next_topic'] = 'อ่านหัวข้อถัดไป';
$lang['View_previous_topic'] = 'อ่านหัวข้อก่อนหน้า';
$lang['Submit_vote'] = 'ส่งคะแนน';
$lang['View_results'] = 'ดูผล';

$lang['No_newer_topics'] = 'ไม่มีหัวข้อใหม่';
$lang['No_older_topics'] = 'ไม่มีหัวข้อเก่า';
$lang['Topic_post_not_exist'] = 'ไม่พบหัวข้อหรือข้อความที่คุณต้องการ';
$lang['No_posts_topic'] = 'ไม่มีข้อความตอบในหัวข้อนี้';

$lang['Display_posts'] = 'เรียงลำดับข้อความตอบจากก่อนหน้า';
$lang['All_Posts'] = 'ข้อความทั้งหมด';
$lang['Newest_First'] = 'ใหม่-เก่า';
$lang['Oldest_First'] = 'เก่า-ใหม่';

$lang['Back_to_top'] = 'ขึ้นไปข้างบน';

$lang['Read_profile'] = 'ดูข้อมูลส่วนตัว'; 
$lang['Send_email'] = 'ส่ง Email';
$lang['Visit_website'] = 'ชมเว็บส่วนตัว';
$lang['ICQ_status'] = 'สถานะ ICQ';
$lang['Edit_delete_post'] = 'แก้ไข/ลบข้อความ';
$lang['View_IP'] = 'ดูหมายเลข IP';
$lang['Delete_post'] = 'ลบข้อความ';

$lang['wrote'] = 'พิมพ์ว่า'; // proceeds the username and is followed by the quoted text
$lang['Quote'] = 'อ้างอิงจาก'; // comes before bbcode quote output.
$lang['Code'] = 'Code'; // comes before bbcode code output.

$lang['Edited_time_total'] = 'แก้ไขล่าสุดโดย %s เมื่อ %s, ทั้งหมด %d ครั้ง'; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = 'แก้ไขล่าสุดโดย %s เมื่อ %s, ทั้งหมด %d ครั้ง'; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = 'ล็อกหัวข้อ';
$lang['Unlock_topic'] = 'ปลดล็อกหัวข้อ';
$lang['Move_topic'] = 'ย้ายหัวข้อ';
$lang['Delete_topic'] = 'ลบหัวข้อ';
$lang['Split_topic'] = 'แยกหัวข้อ';

$lang['Stop_watching_topic'] = 'หยุดการเฝ้าดูหัวข้อนี้';
$lang['Start_watching_topic'] = 'เฝ้าดูการตอบในหัวข้อนี้';
$lang['No_longer_watching'] = 'คุณไม่ได้เฝ้าดูหัวข้อนี้อีกต่อไป';
$lang['You_are_watching'] = 'เริ่มการเฝ้าดูหัวข้อนี้';

$lang['Total_votes'] = 'คะแนนทั้งหมด';

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = 'ส่วนพิมพ์ข้อความ';
$lang['Topic_review'] = 'ข้อความเก่า';

$lang['No_post_mode'] = 'ไม่ได้ระบุวิธีการ post'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = 'สร้างหัวข้อใหม่';
$lang['Post_a_reply'] = 'ตอบ';
$lang['Post_topic_as'] = 'ส่งหัวข้อนี้เป็น';
$lang['Edit_Post'] = 'แก้ไขข้อความ';
$lang['Options'] = 'ตัวเลือก';

$lang['Post_Announcement'] = 'ประกาศ';
$lang['Post_Sticky'] = 'Sticky';
$lang['Post_Normal'] = 'ปรกติ';

$lang['Confirm_delete'] = 'คุณต้องการลบข้อความนี้หรือไม่?';
$lang['Confirm_delete_poll'] = 'คุณต้องการลบแบบสำรวจนี้หรือไม่?';

$lang['Flood_Error'] = 'คุณเพิ่งส่งข้อความไป กรุณารอสักครู่ แล้วค่อยส่งใหม่';
$lang['Empty_subject'] = 'คุณต้องกรอกชื่อเรื่องด้วย';
$lang['Empty_message'] = 'คุณต้องพิมพ์ข้อความด้วย';
$lang['Forum_locked'] = 'Forum นี้ถูกล็อก คุณไม่สามารถตอบ หรือแก้ไขได้';
$lang['Topic_locked'] = 'หัวข้อนี้ถูกล็อก คุณไม่สามารถแก้ไข หรือตอบได้';
$lang['No_post_id'] = 'คุณต้องเลือกข้อความที่จะแก้ไข';
$lang['No_topic_id'] = 'คุณต้องเลือกหัวข้อที่จะตอบ';
$lang['No_valid_mode'] = 'คุณสามารถตอบ, แก้ไข หรืออ้างอิงข้อความ กรุณากลับไปลองใหม่';
$lang['No_such_post'] = 'ไม่พบข้อความนี้ กรุณากลับไปลองใหม่';
$lang['Edit_own_posts'] = 'ขออภัย. คุณสามารถแก้ไขข้อความของตัวเองเท่านั้น';
$lang['Delete_own_posts'] = 'ขออภัย. คุณสามารถลบข้อความของตัวเองเท่านั้น';
$lang['Cannot_delete_replied'] = 'ขออภัย. คุณไม่สามารถลบข้อความที่ถูกตอบไปแล้ว';
$lang['Cannot_delete_poll'] = 'ขออภัย. คุณไม่สามารถลบแบบสำรวจที่ยังทำงานอยู่';
$lang['Empty_poll_title'] = 'คุณต้องใส่หัวข้อแบบสำรวจ';
$lang['To_few_poll_options'] = 'คุณต้องใส่อย่างน้อย 2 ตัวเลือก';
$lang['To_many_poll_options'] = 'คุณใส่ตัวเลือกแบบสำรวจมากเกินไป';
$lang['Post_has_no_poll'] = 'ข้อความนี้ไม่มีแบบสำรวจ';
$lang['Already_voted'] = 'คุณได้ลงคะแนนแบบสำรวจนี้แล้ว';
$lang['No_vote_option'] = 'ต้องเลือกตัวเลือกเมื่อลงคะแนน';

$lang['Add_poll'] = 'เพิ่มแบบสำรวจ';
$lang['Add_poll_explain'] = 'ถ้าคุณไม่ต้องการใช้แบบสำรวจ ให้ปล่อยส่วนนี้ว่างไว้';
$lang['Poll_question'] = 'คำถามแบบสำรวจ';
$lang['Poll_option'] = 'ตัวเลือกแบบสำรวจ';
$lang['Add_option'] = 'เพิ่มตัวเลือก';
$lang['Update'] = 'ปรับปรุง';
$lang['Delete'] = 'ลบ';
$lang['Poll_for'] = 'ใช้แบบสำรวจเป็นเวลา';
$lang['Days'] = 'วัน'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = '[ใส่ 0 หรือปล่อยว่างไว้ เมื่อไม่ต้องการกำหนดระยะเวลา]';
$lang['Delete_poll'] = 'ลบแบบสำรวจ';

$lang['Disable_HTML_post'] = 'ไม่ใช้รหัส HTML ในข้อความนี้';
$lang['Disable_BBCode_post'] = 'ไม่ใช้ BBCode ในข้อความนี้';
$lang['Disable_Smilies_post'] = 'ไม่ใช้รูปรอยยิ้มในข้อความนี้';

$lang['HTML_is_ON'] = 'ใช้รหัส HTML <u>ได้</u>';
$lang['HTML_is_OFF'] = 'ใช้รหัส HTML <u>ไม่ได้</u>';
$lang['BBCode_is_ON'] = 'ใช้ %sBBCode%s <u>ได้</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = 'ใช้ %sBBCode%s <u>ไม่ได้</u>';
$lang['Smilies_are_ON'] = 'ใช้รูปรอยยิ้ม <u>ได้</u>';
$lang['Smilies_are_OFF'] = 'ใช้รูปรอยยิ้ม <u>ไม่ได้</u>';

$lang['Attach_signature'] = 'แนบลายเซ็น (ลายเซ็น สามารถแก้ไขได้ในข้อมูลส่วนตัว)';
$lang['Notify'] = 'เตือนเมื่อมีผู้ตอบ';
$lang['Delete_post'] = 'ลบข้อความนี้';

$lang['Stored'] = 'ได้รับข้อความของคุณแล้ว';
$lang['Deleted'] = 'ลบข้อความของคุณแล้ว';
$lang['Poll_delete'] = 'ลบแบบสำรวจของคุณแล้ว';
$lang['Vote_cast'] = 'ได้รับการลงคะแนนของคุณแล้ว';

$lang['Topic_reply_notification'] = 'การเตือนเมื่อมีผู้ตอบ';

$lang['bbcode_b_help'] = 'ตัวหนา: [b]ข้อความ[/b]  (alt+b)';
$lang['bbcode_i_help'] = 'ตัวเอียง: [i]ข้อความ[/i]  (alt+i)';
$lang['bbcode_u_help'] = 'ขีดเส้นใต้: [u]ข้อความ[/u]  (alt+u)';
$lang['bbcode_q_help'] = 'อ้างอิงข้อความ: [quote]ข้อความ[/quote]  (alt+q)';
$lang['bbcode_c_help'] = 'Code: [code]ข้อความ[/code]  (alt+c)';
$lang['bbcode_l_help'] = 'รายการ: [list]ข้อความ[/list]  (alt+l)';
$lang['bbcode_o_help'] = 'ลำดับรายการ: [list=]ข้อความ[/list]  (alt+o)';
$lang['bbcode_p_help'] = 'แทรกรูปภาพ: [img]http://image_url[/img]  (alt+p)';
$lang['bbcode_w_help'] = 'แทรก URL: [url]http://url[/url] หรือ [url=http://url]ข้อความ[/url]  (alt+w)';
$lang['bbcode_a_help'] = 'ปิดท้ายทุกคำสั่ง BBCode โดยอัตโนมัติ';
$lang['bbcode_s_help'] = 'สีตัวอักษร: [color=red]ข้อความ[/color]  เพิ่มเติม: หรือจะใช้ color=#FF0000 ก็ได้';
$lang['bbcode_f_help'] = 'ขนาดตัวอักษร: [size=x-small]ข้อความ[/size]';

$lang['Emoticons'] = 'แสดงอารมณ์';
$lang['More_emoticons'] = 'อารมณ์อื่นๆ';

$lang['Font_color'] = 'สีตัวอักษร';
$lang['color_default'] = 'มาตรฐาน';
$lang['color_dark_red'] = 'แดงเข้ม';
$lang['color_red'] = 'แดง';
$lang['color_orange'] = 'ส้ม';
$lang['color_brown'] = 'น้ำตาล';
$lang['color_yellow'] = 'เหลือง';
$lang['color_green'] = 'เขียว';
$lang['color_olive'] = 'มะกอก';
$lang['color_cyan'] = 'ฟ้า';
$lang['color_blue'] = 'น้ำเงิน';
$lang['color_dark_blue'] = 'น้ำเงินเข้ม';
$lang['color_indigo'] = 'คราม';
$lang['color_violet'] = 'ม่วง';
$lang['color_white'] = 'ขาว';
$lang['color_black'] = 'ดำ';

$lang['Font_size'] = 'ขนาดตัวอักษร';
$lang['font_tiny'] = 'เล็กมาก';
$lang['font_small'] = 'เล็ก';
$lang['font_normal'] = 'ปรกติ';
$lang['font_large'] = 'ใหญ่';
$lang['font_huge'] = 'ใหญ่มาก';

$lang['Close_Tags'] = 'ปิดคำสั่ง';
$lang['Styles_tip'] = 'เพิ่มเติม: สามารถใส่รูปแบบให้ตัวอักษรที่ถูกเลือกได้ทันที';


//
// Private Messaging
//
$lang['Private_Messaging'] = 'ข้อความส่วนตัว';

$lang['Login_check_pm'] = 'เข้าสู่ระบบเพื่อเช็คข้อความส่วนตัว';
$lang['New_pms'] = 'คุณมี %d ข้อความใหม่'; // You have 2 new messages
$lang['New_pm'] = 'คุณมี %d ข้อความใหม่'; // You have 1 new message
$lang['No_new_pm'] = 'ไม่มีข้อความใหม่';
$lang['Unread_pms'] = 'คุณมี %d ข้อความที่ยังไม่ได้อ่าน';
$lang['Unread_pm'] = 'คุณมี %d ข้อความที่ยังไม่ได้อ่าน';
$lang['No_unread_pm'] = 'คุณไม่มีข้อความที่ยังไม่ได้อ่าน';
$lang['You_new_pm'] = 'มี 1 ข้อความใหม่ใน Inbox';
$lang['You_new_pms'] = 'มีหลายข้อความใหม่ใน Inbox';
$lang['You_no_new_pm'] = 'ไม่มีข้อความใหม่';

$lang['Unread_message'] = 'ยังไม่ได้อ่าน';
$lang['Read_message'] = 'อ่านแล้ว';

$lang['Read_pm'] = 'อ่าน';
$lang['Post_new_pm'] = 'ส่งข้อความส่วนตัว';
$lang['Post_reply_pm'] = 'ตอบกลับ';
$lang['Post_quote_pm'] = 'ตอบโดยอ้างอิงข้อความ';
$lang['Edit_pm'] = 'แก้ไข';

$lang['Inbox'] = 'Inbox';
$lang['Outbox'] = 'Outbox';
$lang['Savebox'] = 'Savebox';
$lang['Sentbox'] = 'Sentbox';
$lang['Flag'] = 'Flag';
$lang['Subject'] = 'เรื่อง';
$lang['From'] = 'จาก';
$lang['To'] = 'ถึง';
$lang['Date'] = 'วันที่';
$lang['Mark'] = 'ทำเครื่องหมาย';
$lang['Sent'] = 'ส่ง';
$lang['Saved'] = 'บันทึก';
$lang['Delete_marked'] = 'ลบเฉพาะที่ทำเครื่องหมาย';
$lang['Delete_all'] = 'ลบทั้งหมด';
$lang['Save_marked'] = 'เก็บเฉพาะที่ทำเครื่องหมาย'; 
$lang['Save_message'] = 'เก็บ';
$lang['Delete_message'] = 'ลบ';

$lang['Display_messages'] = 'แสดงข้อความภายในเวลา'; // Followed by number of days/weeks/months
$lang['All_Messages'] = 'ข้อความทั้งหมด';

$lang['No_messages_folder'] = 'ไม่มีข้อความ';

$lang['PM_disabled'] = 'บอร์ดนี้ถูกงดการใช้ข้อความส่วนตัว';
$lang['Cannot_send_privmsg'] = 'ขออภัย. Admin ไม่อนุญาตให้คุณใช้ข้อความส่วนตัว';
$lang['No_to_user'] = 'คุณต้องระบุ username เพื่อส่งข้อความ';
$lang['No_such_user'] = 'ขออภัย. ไม่พบ username นี้';

$lang['Disable_HTML_pm'] = 'ไม่ใช้ HTML ในข้อความนี้';
$lang['Disable_BBCode_pm'] = 'ไม่ใช้ BBCode ในข้อความนี้';
$lang['Disable_Smilies_pm'] = 'ไม่ใช้รูปรอยยิ้ม ในข้อความนี้';

$lang['Message_sent'] = 'ส่งข้อความแล้ว';

$lang['Click_return_inbox'] = 'คลิก %sที่นี่%s เพื่อกลับไป Inbox';
$lang['Click_return_index'] = 'คลิก %sที่นี่%s เพื่อกลับไป Index';

$lang['Send_a_new_message'] = 'ส่งข้อความส่วนตัว';
$lang['Send_a_reply'] = 'ตอบข้อความส่วนตัว';
$lang['Edit_message'] = 'แก้ไขข้อความส่วนตัว';

$lang['Notification_subject'] = 'มีข้อความใหม่ส่งถึงคุณ';

$lang['Find_username'] = 'ค้นหา username';
$lang['Find'] = 'ค้นหา';
$lang['No_match'] = 'ไม่พบ username นี้';

$lang['No_post_id'] = 'ไม่ได้ระบุหมายเลขผู้ post';
$lang['No_such_folder'] = 'ไม่พบ folder นี้';
$lang['No_folder'] = 'ไม่ได้ระบุ folder';

$lang['Mark_all'] = 'ทำเครื่องหมายทั้งหมด';
$lang['Unmark_all'] = 'ยกเลิกเครื่องหมายทั้งหมด';

$lang['Confirm_delete_pm'] = 'คุณต้องการลบข้อความนี้หรือไม่?';
$lang['Confirm_delete_pms'] = 'คุณต้องการลบข้อความเหล่านี้หรือไม่?';

$lang['Inbox_size'] = 'คุณใช้ Inbox ไปแล้ว %d%%'; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = 'คุณใช้ Sentbox ไปแล้ว %d%%'; 
$lang['Savebox_size'] = 'คุณใช้ Savebox ไปแล้ว %d%%'; 

$lang['Click_view_privmsg'] = 'คลิก %sที่นี่%s เพื่อไป Inbox ของคุณ';


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = 'ข้อมูลส่วนตัวของ %s'; // %s is username 
$lang['About_user'] = 'เกี่ยวกับ %s'; // %s is username

$lang['Preferences'] = 'การตั้งค่าต่างๆ';
$lang['Items_required'] = 'ต้องกรอกทุกช่องที่มีเครื่องหมาย *. Items marked with a * are required unless stated otherwise.';
$lang['Registration_info'] = 'ข้อมูลการลงทะเบียน. Registration Information.';
$lang['Profile_info'] = 'ข้อมูลส่วนตัว. Profile Information.';
$lang['Profile_info_warn'] = 'ข้อมูลส่วนนี้จะถูกแสดงสู่สาธารณชน. This information will be publicly viewable.';
$lang['Avatar_panel'] = 'กำหนดรูปประจำตัว. Avatar control panel.';
$lang['Avatar_gallery'] = 'รูปประจำตัวที่มีให้. Avatar gallery.';

$lang['Website'] = 'Website';
$lang['Location'] = 'ที่อยู่';
$lang['LocationWarn'] = 'จะถูกแสดงสู่สาธารณะด้วย';
$lang['Contact'] = 'ติดต่อ';
$lang['Email_address'] = 'Email address';
$lang['Email'] = 'Email';
$lang['Send_private_message'] = 'ส่งข้อความส่วนตัว';
$lang['Hidden_email'] = '[ ซ่อน ]';
$lang['Search_user_posts'] = 'ค้นหาข้อความที่ผู้นี้ได้ตอบ';
$lang['Interests'] = 'ความสนใจ';
$lang['Occupation'] = 'อาชีพ';
$lang['Poster_rank'] = 'ระดับการตอบ';

$lang['Total_posts'] = 'ตอบทั้งหมด';
$lang['User_post_pct_stats'] = '%.2f%% จากทั้งหมด'; // 1.25% of total
$lang['User_post_day_stats'] = '%.2f ข้อความต่อวัน'; // 1.5 posts per day
$lang['Search_user_posts'] = 'ค้นหาข้อความที่ตอบโดย %s'; // Find all posts by username

$lang['No_user_id_specified'] = 'ขออภัย. ไม่พบ username นี้';
$lang['Wrong_Profile'] = 'คุณไม่สามารถแก้ไขข้อมูลของผู้อื่นได้';

$lang['Only_one_avatar'] = 'ระบุชนิดของรูปประจำตัวได้ทีละอย่างเท่านั้น';
$lang['File_no_data'] = 'ไม่พบข้อมูลในไฟล์จาก URL ที่คุณระบุ';
$lang['No_connection_URL'] = 'ติดต่อ URL ที่คุณระบุไม่ได้';
$lang['Incomplete_URL'] = 'URL ที่คุณระบุไม่ถูกต้อง';
$lang['Wrong_remote_avatar_format'] = 'URL รูปภาพของที่อื่นไม่ถูกต้อง';
$lang['No_send_account_inactive'] = 'ขออภัย. ไม่สามารถส่ง password ได้ เพราะบัญชีของคุณถูกระงับ. กรุณาติดต่อ administrator';

$lang['Always_smile'] = 'ใช้รูปรอยยิ้มเสมอ';
$lang['Always_html'] = 'ใช้ HTML เสมอ';
$lang['Always_bbcode'] = 'ใช้ BBCode เสมอ';
$lang['Always_add_sig'] = 'แนบลายเซ็นด้วยเสมอ';
$lang['Always_notify'] = 'เตือนเมื่อมีผู้ตอบเสมอ';
$lang['Always_notify_explain'] = 'ส่ง Email เตือนเมื่อมีผู้ตอบ สามารถเปลี่ยนได้ทุกครั้งที่คุณพิมพ์ข้อความ';

$lang['Board_style'] = 'รูปแบบ';
$lang['Board_lang'] = 'ภาษา(language)';
$lang['No_themes'] = 'ไม่มี theme ในฐานข้อมูล';
$lang['Timezone'] = 'ปรับเวลา';
$lang['Date_format'] = 'รูปแบบวันที่';
$lang['Date_format_explain'] = 'ใช้รหัสตามหลักของภาษา PHP ในฟังก์ชัน <a href=\'http://www.php.net/date\' target=\'_other\'>date()</a>';
$lang['Signature'] = 'ลายเซ็น';
$lang['Signature_explain'] = 'ข้อความต่อท้าย ไม่เกิน %d ตัวอักษร';
$lang['Public_view_email'] = 'แสดง Email เสมอ';

$lang['Current_password'] = 'Password ปัจจุบัน';
$lang['New_password'] = 'Password ใหม่';
$lang['Confirm_password'] = 'Password ใหม่อีกครั้ง(Again)';
$lang['Confirm_password_explain'] = 'คุณต้องยืนยัน password ถ้าคุณต้องการจะเปลี่ยน password หรือเปลี่ยน Email';
$lang['password_if_changed'] = 'ให้กรอก password ใหม่ เมื่อต้องการเปลี่ยนเท่านั้น';
$lang['password_confirm_if_changed'] = 'ให้กรอก password ใหม่อีกครั้ง เมื่อต้องการเปลี่ยนเท่านั้น';

$lang['Avatar'] = 'รูปประจำตัว';
$lang['Avatar_explain'] = 'แสดงรูปภาพเล็กๆข้างชื่อของคุณ รูปกว้างไม่เกิน %d pixels สูงไม่เกิน %d pixels และขนาดไม่เกิน %dkB.'; $lang['Upload_Avatar_file'] = 'ส่งรูปจากเครื่องของคุณ';
$lang['Upload_Avatar_URL'] = 'ส่งรูปจาก URL';
$lang['Upload_Avatar_URL_explain'] = 'ป้อน URL ของรูปภาพ แล้วระบบจะก๊อปปี้มาเก็บไว้ที่นี่เอง';
$lang['Pick_local_Avatar'] = 'เลือกจากที่มีให้';
$lang['Link_remote_Avatar'] = 'เชื่อมโยงไปนอกเว็บนี้';
$lang['Link_remote_Avatar_explain'] = 'ป้อน URL ของรูปภาพที่คุณต้องการ';
$lang['Avatar_URL'] = 'URL ของรูปภาพ';
$lang['Select_from_gallery'] = 'เลือกจากที่มีให้';
$lang['View_avatar_gallery'] = 'แสดงรูปภาพที่มีให้';

$lang['Select_avatar'] = 'เลือก';
$lang['Return_profile'] = 'ยกเลิก';
$lang['Select_category'] = 'เลือกกลุ่ม';

$lang['Delete_Image'] = 'ลบรูปภาพ';
$lang['Current_Image'] = 'รูปภาพปัจจุบัน';

$lang['Notify_on_privmsg'] = 'ส่ง Email เตือนเมื่อมีข้อความส่วนตัวใหม่เข้ามา';
$lang['Popup_on_privmsg'] = 'เปิดหน้าต่างใหม่อัตโนมัติ เมื่อมีข้อความส่วนตัวใหม่เข้ามา';
$lang['Popup_on_privmsg_explain'] = 'บาง template จะเปิดหน้าต่างใหม่เพื่อแจ้งเมื่อมีข้อความส่วนตัวใหม่เข้ามา'; 
$lang['Hide_user'] = 'ซ่อนสถานะการ online ของคุณ';

$lang['Profile_updated'] = 'ข้อมูลส่วนตัวของคุณถูกบันทึกแล้ว';
$lang['Profile_updated_inactive'] = 'ข้อมูลส่วนตัวของคุณถูกบันทึกแล้ว แต่คุณต้องทำการยืนยันชื่อบัญชีใหม่ กรุณากลับไปตรวจสอบ email ของคุณเพื่ออ่านวิธียืนยันชื่อบัญชี หรือให้คุณรอ ถ้า admin เป็นผู้ทำการยืนยันชื่อบัญชี';

$lang['Password_mismatch'] = 'Password ที่คุณป้อน ไม่ตรงกัน';
$lang['Current_password_mismatch'] = 'Password ปัจจุบันไม่ถูกต้อง';
$lang['Password_long'] = 'Password ยาวไม่เกิน 32 ตัวอักษร';
$lang['Username_taken'] = 'ขออภัย. Username นี้ถูกใช้งานแล้ว';
$lang['Username_invalid'] = 'ขออภัย. Username นี้มีตัวอักษรต้องห้ามอยู่ เช่นเครื่องหมาย \'';
$lang['Username_disallowed'] = 'ขออภัย. ไม่อนุญาตให้ใช้ username นี้';
$lang['Email_taken'] = 'ขออภัย. Email นี้ถูกใช้ไปแล้ว';
$lang['Email_banned'] = 'ขออภัย. Email นี้ถูกห้ามใช้';
$lang['Email_invalid'] = 'ขออภัย. Email ไม่ถูกต้อง';
$lang['Signature_too_long'] = 'ลายเซ็นยาวเกินไป';
$lang['Fields_empty'] = 'คุณต้องกรอกช่องที่จำเป็นให้ครบ';
$lang['Avatar_filetype'] = 'ชนิดของรูปภาพต้องเป็น .jpg, .gif หรือ .png';
$lang['Avatar_filesize'] = 'ขนาดของรูปภาพต้องน้อยกว่า %d kB'; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = 'ขนาดของรูปภาพต้องกว้างไม่เกิน %d pixels และสูงไม่เกิน %d pixels';

$lang['Welcome_subject'] = 'ขอต้อนรับสู่ Forum %s'; // Welcome to my.com forums
$lang['New_account_subject'] = 'บัญชีผู้ใช้ใหม่';
$lang['Account_activated_subject'] = 'บัญชีถูกยืนยันแล้ว';

$lang['Account_added'] = 'ขอบคุณที่สมัคร บัญชีของคุณถูกสร้างแล้ว คุณสามารถเข้าสู่ระบบได้ด้วย username และ password ที่คุณสมัคร';
$lang['Account_inactive'] = 'ขอบคุณที่สมัคร บัญชีของคุณถูกสร้างแล้ว แต่คุณต้องทำการยืนยันก่อน กรุณากลับไปเช็ค Email ที่คุณใช้สมัคร';
$lang['Account_inactive_admin'] = 'ขอบคุณที่สมัคร บัญชีของคุณถูกสร้างแล้ว คุณจะได้รับ Email แจ้ง เมื่อ Admin ได้อนุญาตแล้ว';
$lang['Account_active'] = 'บัญชีของคุณได้ถูกยืนยันแล้ว ขอบคุณที่สมัคร';
$lang['Account_active_admin'] = 'บัญชีของคุณได้ถูกยืนยันแล้ว';
$lang['Reactivate'] = 'กรุณายืนยันบัญชีของคุณใหม่!';
$lang['Already_activated'] = 'คุณได้ยืนยันบัญชีของคุณไปแล้ว';
$lang['COPPA'] = 'บัญชีของคุณถูกสร้างแล้ว แต่ต้องได้รับการยืนยันเสียก่อน กรุณากลับไปเช็ค Email เพื่อดูรายละเอียด';

$lang['Registration'] = 'เงื่อนไข(Registration Agreement Terms)';
$lang['Reg_agreement'] = 'คุณยอมรับว่า Administrator และ moderator ของ forum นี้ มีสิทธิ์อ่าน, ลบ หรือแก้ไขทุกข้อความ. และ administrator, moderator หรือ webmaster ไม่สามารถรับผิดชอบต่อข้อความที่คุณได้แสดงความคิดเห็น (ยกเว้นว่าพวกเขาจะเป็นผู้โพสต์เอง).<br /><br />คุณตกลงว่าจะไม่โพสต์ข้อความที่หยาบคาย, ลามก, ไม่แสดงความเคารพ, หมิ่นประมาท, เป็นที่รังเกียจ, ขู่เข็ญ, ส่อไปในทางเพศ หรืออื่นๆที่ขัดต่อกฎหมาย. การกระทำเช่นนั้นอาจทำให้คุณถูกหวงห้ามทันที และอย่างถาวร (และผู้ให้บริการของคุณก็จะได้รับการแจ้งเตือนด้วย). หมายเลข IP ของทุกโพสต์จะถูกบันทึกเพื่อใช้เป็นหลักฐาน. คุณยินยอมให้ webmaster, administrator และ moderators ของ forum นี้มีสิทธิ์ลบ, แก้ไข, ย้าย หรือปิดหัวข้อใดๆได้ตลอดเวลาที่สมควร. คุณยินยอมให้ข้อมูลทุกอย่างของคุณถูกเก็บไว้ในฐานข้อมูล. ซึ่งข้อมูลเหล่านี้จะไม่ถูกเปิดเผยต่อผู้อื่นโดยไม่ได้รับการยินยอมจากคุณ .Webmaster, administrator และ moderator ไม่สามารถรับผิดชอบต่อการถูกเจาะข้อมูล แล้วนำไปสร้างความเดือดร้อนต่างๆ.<br /><br />Forum นี้ใช้ระบบ cookies เพื่อเก็บข้อมูลไว้ในคอมพิวเตอร์ของคุณ. Cookies เหล่านี้จะไม่มีข้อมูลที่คุณได้กรอกไว้เหมือนด้านบน; แต่เพื่อช่วยให้คุณใช้งานได้ง่ายขึ้น. E-mail จะถูกใช้เพื่อยืนยันข้อมูลการสมัครสมาชิกและ password ของคุณเท่านั้น (และใช้สำหรับส่ง password อันใหม่เมื่อคุณลืม password เก่า).<br /><br />ถ้าคุณคลิกสมัครสมาชิกที่ด้านล่างนี้ ถือว่าคุณได้ยอมรับทุกเงื่อนไขที่กล่าวมาแล้ว.<br /><br /><br/>While the administrators and moderators of this forum will attempt to remove or edit any generally objectionable material as quickly as possible, it is impossible to review every message. Therefore you acknowledge that all posts made to these forums express the views and opinions of the author and not the administrators, moderators or webmaster (except for posts by these people) and hence will not be held liable.<br /><br />You agree not to post any abusive, obscene, vulgar, slanderous, hateful, threatening, sexually-oriented or any other material that may violate any applicable laws. Doing so may lead to you being immediately and permanently banned (and your service provider being informed). The IP address of all posts is recorded to aid in enforcing these conditions. You agree that the webmaster, administrator and moderators of this forum have the right to remove, edit, move or close any topic at any time should they see fit. As a user you agree to any information you have entered above being stored in a database. While this information will not be disclosed to any third party without your consent the webmaster, administrator and moderators cannot be held responsible for any hacking attempt that may lead to the data being compromised.<br /><br />This forum system uses cookies to store information on your local computer. These cookies do not contain any of the information you have entered above; they serve only to improve your viewing pleasure. The e-mail address is used only for confirming your registration details and password (and for sending new passwords should you forget your current one).<br /><br />By clicking Register below you agree to be bound by these conditions.';

$lang['Agree_under_13'] = 'ยอมรับเงื่อนไข และฉันอายุ<b>ต่ำกว่า</b> 13 ปี. I Agree to these terms and am <b>under</b> 13 years of age.';
$lang['Agree_over_13'] = 'ยอมรับเงื่อนไข และฉันอายุ<b>มากกว่า</b>หรือ<b>เท่ากับ</b> 13 ปี. I Agree to these terms and am <b>over</b> or <b>exactly</b> 13 years of age.';
$lang['Agree_not'] = 'ไม่ยอมรับเงื่อนไข. I do not agree to these terms.';

$lang['Wrong_activation'] = 'รหัสยืนยันที่คุณกรอกไม่ถูกต้อง. The activation key you supplied does not match any in the database.';
$lang['Send_password'] = 'ขอ password อันใหม่. Send me a new password.';
$lang['Password_updated'] = 'สร้าง password อันใหม่แล้ว, กรุณากลับไปเช็ค Email เพื่อทำการยืนยัน. A new password has been created; please check your e-mail for details on how to activate it.';
$lang['No_email_match'] = 'E-mail ที่คุณกรอกไม่ตรงกับที่เคยให้ไว้. The e-mail address you supplied does not match the one listed for that username.';
$lang['New_password_activation'] = 'ยืนยัน password ใหม่แล้ว. New password activation';
$lang['Password_activated'] = 'บัญชีของคุณได้ถูกยืนยันใหม่. กรุณาใช้ password ที่ได้รับใน Email เพื่อเข้าสู่ระบบ. Your account has been re-activated. To log in, please use the password supplied in the e-mail you received.';

$lang['Send_email_msg'] = 'ส่ง Email';
$lang['No_user_specified'] = 'ไม่ได้ระบุ username';
$lang['User_prevent_email'] = 'Username นี้ไม่ต้องการรับ Email กรุณาเปลี่ยนเป็นส่งข้อความส่วนตัวแทน';
$lang['User_not_exist'] = 'ไม่พบ username นี้';
$lang['CC_email'] = 'คัดลอก Email นี้ส่งกลับมาถึงคุณด้วย';
$lang['Email_message_desc'] = 'ข้อความจะถูกส่งไปแบบตัวอักษรธรรมดา, กรุณาอย่าใช้รหัส HTML หรือ BBCode. ข้อความที่ส่งกลับจะถูกส่งมาที่ Email ของคุณ';
$lang['Flood_email_limit'] = 'คุณยังไม่สามารถส่ง Email อื่นได้ตอนนี้ กรุณารอสักครู่ แล้วค่อยลองใหม่';
$lang['Recipient'] = 'ผู้รับ';
$lang['Email_sent'] = 'Email ถูกส่งแล้ว';
$lang['Send_email'] = 'ส่ง Email';
$lang['Empty_subject_email'] = 'คุณต้องกรอกชื่อเรื่องด้วย';
$lang['Empty_message_email'] = 'คุณต้องพิมพ์ข้อความ';


//
// Memberslist
//
$lang['Select_sort_method'] = 'เลือกวิธีเรียงลำดับ';
$lang['Sort'] = 'เรียงลำดับ';
$lang['Sort_Top_Ten'] = 'ติดอันดับ 10 คน';
$lang['Sort_Joined'] = 'วันที่เข้าร่วม';
$lang['Sort_Username'] = 'Username';
$lang['Sort_Location'] = 'ที่อยู่';
$lang['Sort_Posts'] = 'จำนวนการตอบ';
$lang['Sort_Email'] = 'Email';
$lang['Sort_Website'] = 'Website';
$lang['Sort_Ascending'] = 'น้อย-มาก';
$lang['Sort_Descending'] = 'มาก-น้อย';
$lang['Order'] = 'ตามลำดับ';


//
// Group control panel
//
$lang['Group_Control_Panel'] = 'Group Control Panel';
$lang['Group_member_details'] = 'รายละเอียดการเป็นสมาชิกกลุ่ม';
$lang['Group_member_join'] = 'เข้าร่วมกลุ่ม';

$lang['Group_Information'] = 'รายละเอียดกลุ่ม';
$lang['Group_name'] = 'ชื่อกลุ่ม';
$lang['Group_description'] = 'คำอธิบายกลุ่ม';
$lang['Group_membership'] = 'สถานะการเป็นสมาชิกกลุ่ม';
$lang['Group_Members'] = 'สมาชิกกลุ่ม';
$lang['Group_Moderator'] = 'Group Moderator';
$lang['Pending_members'] = 'สมาชิกที่รอการพิจารณา';

$lang['Group_type'] = 'ชนิดกลุ่ม';
$lang['Group_open'] = 'กลุ่มเปิด';
$lang['Group_closed'] = 'กลุ่มถูกปิด';
$lang['Group_hidden'] = 'กลุ่มถูกซ่อน';

$lang['Current_memberships'] = 'เป็นสมาชิก';
$lang['Non_member_groups'] = 'ไม่ได้เป็นสมาชิก';
$lang['Memberships_pending'] = 'รอการพิจารณา';

$lang['No_groups_exist'] = 'ไม่มีกลุ่มผู้ใช้';
$lang['Group_not_exist'] = 'ไม่พบกลุ่มผู้ใช้นี้';

$lang['Join_group'] = 'เข้าร่วมกลุ่ม';
$lang['No_group_members'] = 'ไม่มีสมาชิกในกลุ่มนี้';
$lang['Group_hidden_members'] = 'กลุ่มนี้ถูกซ่อน คุณจึงไม่สามารถดูรายชื่อสมาชิกกลุ่ม';
$lang['No_pending_group_members'] = 'ไม่มีสมาชิกที่รอการพิจารณาในกลุ่มนี้';
$lang['Group_joined'] = 'ได้รับการสมัครสมาชิกกลุ่มนี้ของคุณแล้ว<br />คุณจะได้รับการแจ้งเตือนเมื่อ moderator ของกลุ่ม ทำการอนุญาตให้คุณเป็นสมาชิก';
$lang['Group_request'] = 'ได้รับใบสมัครร่วมกลุ่มของคุณแล้ว';
$lang['Group_approved'] = 'คุณได้รับการอนุญาตแล้ว';
$lang['Group_added'] = 'เพิ่มชื่อของคุณเข้ากลุ่มแล้ว'; 
$lang['Already_member_group'] = 'คุณเป็นสมาชิกของกลุ่มนี้อยู่แล้ว';
$lang['User_is_member_group'] = 'ผู้ใช้นี้เป็นสมาชิกของกลุ่มนี้อยู่แล้ว';
$lang['Group_type_updated'] = 'ปรับปรุงชนิดกลุ่มเรียบร้อยแล้ว';

$lang['Could_not_add_user'] = 'ไม่พบชื่อที่คุณเลือก';
$lang['Could_not_anon_user'] = 'คุณไม่สามารถเพิ่มชื่อ Anonymous เป็นสมาชิก';

$lang['Confirm_unsub'] = 'คุณแน่ใจหรือที่จะยกเลิกการเป็นสมาชิกกลุ่มนี้?';
$lang['Confirm_unsub_pending'] = 'การสมัครสมาชิกกลุ่มของคุณยังไม่ได้รับการอนุญาต คุณแน่ใจหรือที่จะยกเลิกการสมัคร?';

$lang['Unsub_success'] = 'คุณได้ยกเลิกการเป็นสมาชิกกลุ่มแล้ว';

$lang['Approve_selected'] = 'อนุญาตชื่อที่เลือกไว้';
$lang['Deny_selected'] = 'ปฏิเสธชื่อที่เลือกไว้';
$lang['Not_logged_in'] = 'คุณต้องเข้าสู่ระบบ เพื่อเข้าร่วมกลุ่ม';
$lang['Remove_selected'] = 'ลบชื่อที่เลือกไว้';
$lang['Add_member'] = 'เพิ่มสมาชิกกลุ่ม';
$lang['Not_group_moderator'] = 'คุณไม่ได้เป็น moderator ของกลุ่มคุณจึงไม่มีสิทธิ์แก้ไขได้';

$lang['Login_to_join'] = 'เข้าสู่ระบบ เพื่อร่วมหรือจัดการสมาชิกกลุ่ม';
$lang['This_open_group'] = 'กลุ่มนี้เป็นกลุ่มเปิด คลิกปุ่มสมัครสมาชิกกลุ่ม';
$lang['This_closed_group'] = 'กลุ่มนี้ถูกปิด จึงปิดรับสมัครสมาชิกกลุ่ม';
$lang['This_hidden_group'] = 'กลุ่มนี้ถูกซ่อน จึงไม่อนุญาตให้สมัครสมาชิกกลุ่มแบบอัตโนมัติ';
$lang['Member_this_group'] = 'คุณเป็นสมาชิกของกลุ่มนี้';
$lang['Pending_this_group'] = 'การสมัครของคุณ กำลังอยู่ในระหว่างการพิจารณา';
$lang['Are_group_moderator'] = 'คุณเป็น moderator ของกลุ่ม';
$lang['None'] = '(ไม่มี)';

$lang['Subscribe'] = 'สมัครสมาชิก';
$lang['Unsubscribe'] = 'ยกเลิกการเป็นสมาชิก';
$lang['View_Information'] = 'ดูรายละเอียด';


//
// Search
//
$lang['Search_query'] = 'Search Query';
$lang['Search_options'] = 'ตัวเลือก';

$lang['Search_keywords'] = 'ค้นหาจากคำว่า';
$lang['Search_keywords_explain'] = 'คุณสามารถใช้ <u>AND</u> เพื่อระบุคำที่ต้องมีในผลลัพธ์, <u>OR</u> อาจมีหรือไม่มีคำนี้ก็ได้ และ <u>NOT</u> จะต้องไม่มีคำนี้อยู่. ใช้ * เพื่อค้นหาจากบางส่วนของคำ';
$lang['Search_author'] = 'ค้นหาจากผู้แต่ง';
$lang['Search_author_explain'] = 'ใช้ * เพื่อค้นหาจากบางส่วนของคำ';

$lang['Search_for_any'] = 'ค้นหาจากทุกส่วน หรือใช้ข้อความที่ระบุ';
$lang['Search_for_all'] = 'ค้นหาจากทุกส่วน';
$lang['Search_title_msg'] = 'ค้นหาจากชื่อหัวข้อ และส่วนของข้อความ';
$lang['Search_msg_only'] = 'ค้นหาจากส่วนของข้อความเท่านั้น';

$lang['Return_first'] = 'Return first'; // followed by xxx characters in a select box
$lang['characters_posts'] = 'characters of posts';

$lang['Search_previous'] = 'ค้นหาก่อนวันที่'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = 'เรียงลำดับจาก';
$lang['Sort_Time'] = 'เวลาที่ post';
$lang['Sort_Post_Subject'] = 'เรื่องที่ post';
$lang['Sort_Topic_Title'] = 'Title ของหัวข้อ';
$lang['Sort_Author'] = 'ผู้แต่ง';
$lang['Sort_Forum'] = 'Forum';

$lang['Display_results'] = 'แสดงผล';
$lang['All_available'] = 'ที่หาได้ทั้งหมด';
$lang['No_searchable_forums'] = 'คุณไม่มีสิทธิ์ค้นหา forum ใดๆในเว็บนี้';

$lang['No_search_match'] = 'ไม่พบหัวข้อหรือข้อความที่ต้องการ';
$lang['Found_search_match'] = 'พบ %d อัน'; // eg. Search found 1 match
$lang['Found_search_matches'] = 'พบ %d อัน'; // eg. Search found 24 matches

$lang['Close_window'] = 'ปิดหน้าต่างนี้';


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = 'ขออภัย. เฉพาะ %s เท่านั้น ที่สามารถ post ข้อความประกาศใน forum นี้';
$lang['Sorry_auth_sticky'] = 'ขออภัย. เฉพาะ %s เท่านั้น ที่สามารถ post ข้อความ sticky ใน forum นี้'; 
$lang['Sorry_auth_read'] = 'ขออภัย. เฉพาะ %s เท่านั้น ที่สามารถอ่านหัวข้อใน forum นี้'; 
$lang['Sorry_auth_post'] = 'ขออภัย. เฉพาะ %s เท่านั้น ที่สามารถ post หัวข้อใน forum นี้'; 
$lang['Sorry_auth_reply'] = 'ขออภัย. เฉพาะ %s เท่านั้น ที่สามารถตอบใน forum นี้'; 
$lang['Sorry_auth_edit'] = 'ขออภัย. เฉพาะ %s เท่านั้น ที่สามารถแก้ไข post ใน forum นี้'; 
$lang['Sorry_auth_delete'] = 'ขออภัย. เฉพาะ %s เท่านั้น ที่สามารถลบ post ใน forum นี้'; 
$lang['Sorry_auth_vote'] = 'ขออภัย. เฉพาะ %s เท่านั้น ที่สามารถลงคะแนนแบบสำรวจใน forum นี้'; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>anonymous users</b>';
$lang['Auth_Registered_Users'] = '<b>ผู้ใช้ที่ลงทะเบียน</b>';
$lang['Auth_Users_granted_access'] = '<b>ผู้ได้รับสิทธิ์พิเศษ</b>';
$lang['Auth_Moderators'] = '<b>moderators</b>';
$lang['Auth_Administrators'] = '<b>administrators</b>';

$lang['Not_Moderator'] = 'คุณไม่ได้เป็น moderator ของ forum นี้';
$lang['Not_Authorised'] = 'ไม่อนุญาต';

$lang['You_been_banned'] = 'คุณถูกห้ามเข้า forum นี้<br />กรุณาติดต่อ webmaster หรือ administrator ของบอร์ด';


//
// Viewonline
//
$lang['Reg_users_zero_online'] = 'ไม่มีผู้ใช้ที่ลงทะเบียน และ '; // There are 5 Registered and
$lang['Reg_users_online'] = 'มี %d ผู้ใช้ที่ลงทะเบียน และ '; // There are 5 Registered and
$lang['Reg_user_online'] = 'มี %d ผู้ใช้ที่ลงทะเบียน และ '; // There are 5 Registered and
$lang['Hidden_users_zero_online'] = 'ไม่มีผู้ใช้ที่ซ่อน กำลัง online'; // 6 Hidden users online
$lang['Hidden_users_online'] = '%d ผู้ใช้ที่ซ่อน กำลัง online'; // 6 Hidden users online
$lang['Hidden_user_online'] = '%d ผู้ใช้ที่ซ่อน กำลัง online'; // 6 Hidden users online
$lang['Guest_users_online'] = 'มี %d ผู้เยี่ยมชมที่ online'; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = 'ไม่มีผู้เยี่ยมชมที่ online'; // There are 10 Guest users online
$lang['Guest_user_online'] = 'มี %d ผู้เยี่ยมชมที่ online'; // There is 1 Guest user online
$lang['No_users_browsing'] = 'ไม่มีผู้ที่กำลังอ่าน forum นี้';

$lang['Online_explain'] = 'ข้อมูลนี้เป็นการทำงานล่าสุดเมื่อ 5 นาทีก่อน';

$lang['Forum_Location'] = 'ตำแหน่ง forum';
$lang['Last_updated'] = 'ปรับปรุงล่าสุด';

$lang['Forum_index'] = 'รายการ forum';
$lang['Logging_on'] = 'กำลังเข้าสู่ระบบ';
$lang['Posting_message'] = 'กำลัง post ข้อความ';
$lang['Searching_forums'] = 'กำลังค้นหา forum';
$lang['Viewing_profile'] = 'กำลังดูข้อมูลส่วนตัว';
$lang['Viewing_online'] = 'กำลังดูรายชื่อผู้ที่ online';
$lang['Viewing_member_list'] = 'กำลังดูรายชื่อสมาชิก';
$lang['Viewing_priv_msgs'] = 'กำลังอ่านข้อความส่วนตัว';
$lang['Viewing_FAQ'] = 'กำลังอ่านวิธีใช้';


//
// Moderator Control Panel
//
$lang['Mod_CP'] = 'Moderator Control Panel';
$lang['Mod_CP_explain'] = 'แบบฟอร์มข้างล่างใช้จัดการ forum นี้. คุณสามารถล็อก, ปลดล็อก, ย้าย หรือลบ หัวข้อหมายเลขใดก็ได้';

$lang['Select'] = 'เลือก';
$lang['Delete'] = 'ลบ';
$lang['Move'] = 'ย้าย';
$lang['Lock'] = 'ล็อก';
$lang['Unlock'] = 'ปลดล็อก';

$lang['Topics_Removed'] = 'ลบหัวข้อที่ถูกเลือกแล้ว';
$lang['Topics_Locked'] = 'ล็อกหัวข้อที่ถูกเลือกแล้ว';
$lang['Topics_Moved'] = 'ย้ายหัวข้อที่ถูกเลือกแล้ว';
$lang['Topics_Unlocked'] = 'ปลดล็อกหัวข้อที่ถูกเลือกแล้ว';
$lang['No_Topics_Moved'] = 'ไม่มีหัวข้อใดที่ถูกย้าย';

$lang['Confirm_delete_topic'] = 'คุณต้องการลบหัวข้อ(เหล่า)นี้หรือไม่?';
$lang['Confirm_lock_topic'] = 'คุณต้องการล็อกหัวข้อ(เหล่า)นี้หรือไม่?';
$lang['Confirm_unlock_topic'] = 'คุณต้องการปลดล็อกหัวข้อ(เหล่า)นี้หรือไม่?';
$lang['Confirm_move_topic'] = 'คุณต้องการย้ายหัวข้อ(เหล่า)นี้หรือไม่?';

$lang['Move_to_forum'] = 'ย้ายไปที่ forum';
$lang['Leave_shadow_topic'] = 'ทิ้งข้อความบอก ไว้ใน forum เก่าด้วย';

$lang['Split_Topic'] = 'Control Panel สำหรับแยกหัวข้อ';
$lang['Split_Topic_explain'] = 'แบบฟอร์มด้านล่างใช้สำหรับแยกหัวข้อออกเป็น 2 ส่วน, โดยการเลือกทีละข้อความหรือแบ่งจากข้อความ';
$lang['Split_title'] = 'ชื่อหัวข้อใหม่';
$lang['Split_forum'] = 'Forum สำหรับหัวข้อใหม่';
$lang['Split_posts'] = 'แบ่งข้อความที่เลือก';
$lang['Split_after'] = 'แบ่งจากข้อความที่เลือก';
$lang['Topic_split'] = 'แบ่งหัวข้อที่ถูกเลือกเรียบร้อยแล้ว';

$lang['Too_many_error'] = 'คุณเลือกข้อความมากเกินไป. คุณสามารถเลือกเพียง 1 ข้อความเพื่อแบ่งหัวข้อเท่านั้น!';

$lang['None_selected'] = 'คุณไม่ได้เลือกหัวข้อ. กรุณากลับไปเลือกอย่างน้อย 1 หัวข้อ';
$lang['New_forum'] = 'Forum ใหม่';

$lang['This_posts_IP'] = 'หมายเลข IP ของผู้ตอบ';
$lang['Other_IP_this_user'] = 'หมายเลข IP อื่นๆที่ post โดยผู้ใช้นี้';
$lang['Users_this_IP'] = 'Post จากหมายเลข IP';
$lang['IP_info'] = 'ข้อมูลของหมายเลข IP';
$lang['Lookup_IP'] = 'ดูหมายเลข IP';


//
// Timezones ... for display on each page
//
$lang['All_times'] = 'ปรับเวลา %s'; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = 'GMT - 12 ชั่วโมง';
$lang['-11'] = 'GMT - 11 ชั่วโมง';
$lang['-10'] = 'GMT - 10 ชั่วโมง';
$lang['-9'] = 'GMT - 9 ชั่วโมง';
$lang['-8'] = 'GMT - 8 ชั่วโมง';
$lang['-7'] = 'GMT - 7 ชั่วโมง';
$lang['-6'] = 'GMT - 6 ชั่วโมง';
$lang['-5'] = 'GMT - 5 ชั่วโมง';
$lang['-4'] = 'GMT - 4 ชั่วโมง';
$lang['-3.5'] = 'GMT - 3.5 ชั่วโมง';
$lang['-3'] = 'GMT - 3 ชั่วโมง';
$lang['-2'] = 'GMT - 2 ชั่วโมง';
$lang['-1'] = 'GMT - 1 ชั่วโมง';
$lang['0'] = 'GMT';
$lang['1'] = 'GMT + 1 ชั่วโมง';
$lang['2'] = 'GMT + 2 ชั่วโมง';
$lang['3'] = 'GMT + 3 ชั่วโมง';
$lang['3.5'] = 'GMT + 3.5 ชั่วโมง';
$lang['4'] = 'GMT + 4 ชั่วโมง';
$lang['4.5'] = 'GMT + 4.5 ชั่วโมง';
$lang['5'] = 'GMT + 5 ชั่วโมง';
$lang['5.5'] = 'GMT + 5.5 ชั่วโมง';
$lang['6'] = 'GMT + 6 ชั่วโมง';
$lang['6.5'] = 'GMT + 6.5 ชั่วโมง';
$lang['7'] = 'GMT + 7 ชั่วโมง';
$lang['8'] = 'GMT + 8 ชั่วโมง';
$lang['9'] = 'GMT + 9 ชั่วโมง';
$lang['9.5'] = 'GMT + 9.5 ชั่วโมง';
$lang['10'] = 'GMT + 10 ชั่วโมง';
$lang['11'] = 'GMT + 11 ชั่วโมง';
$lang['12'] = 'GMT + 12 ชั่วโมง';

// These are displayed in the timezone select box
$lang['tz']['-12'] = 'GMT - 12 ชั่วโมง (Eniwetok, Kwajalein)';
$lang['tz']['-11'] = 'GMT - 11 ชั่วโมง (Midway Island, Samoa)';
$lang['tz']['-10'] = 'GMT - 10 ชั่วโมง (Hawaii)';
$lang['tz']['-9'] = 'GMT - 9 ชั่วโมง (Alaska)';
$lang['tz']['-8'] = 'GMT - 8 ชั่วโมง (Pacific Time (US & Canada); Tijuana)';
$lang['tz']['-7'] = 'GMT - 7 ชั่วโมง (Arizona)';
$lang['tz']['-6'] = 'GMT - 6 ชั่วโมง (Central America, Mexico City, Saskatchewan)';
$lang['tz']['-5'] = 'GMT - 5 ชั่วโมง (Bogota, Lima, Quito, Idiana (East))';
$lang['tz']['-4'] = 'GMT - 4 ชั่วโมง (Atlantic Time (Canada), Caracas, La Paz, Santiago)';
$lang['tz']['-3.5'] = 'GMT - 3.5 ชั่วโมง (Newfoundland)';
$lang['tz']['-3'] = 'GMT - 3 ชั่วโมง (Brasilia, Beunos Aires, Georgetown, Greenland)';
$lang['tz']['-2'] = 'GMT - 2 ชั่วโมง (Mid-Atlantic)';
$lang['tz']['-1'] = 'GMT - 1 ชั่วโมง (Azores, Cape Verde Is.)';
$lang['tz']['0'] = 'GMT (Greenwich Mean Time : Dublin, Edinburgh, Lisbon, London, Casablanca, Monrovia)';
$lang['tz']['1'] = 'GMT + 1 ชั่วโมง (Amsterdam, Berlin, Rome, Stockholm, Vienna, Budapest, Madris, Paris)';
$lang['tz']['2'] = 'GMT + 2 ชั่วโมง (Athens, Istanbul, Minsk, Bucharest, Cairo, Jerusalem)';
$lang['tz']['3'] = 'GMT + 3 ชั่วโมง (Baghdad, Kuwait, Moscow)';
$lang['tz']['3.5'] = 'GMT + 3.5 ชั่วโมง (Tehran)';
$lang['tz']['4'] = 'GMT + 4 ชั่วโมง (Abu Dhabi, Muscat)';
$lang['tz']['4.5'] = 'GMT + 4.5 ชั่วโมง (Kabul)';
$lang['tz']['5'] = 'GMT + 5 ชั่วโมง (Islamabad, Karachi)';
$lang['tz']['5.5'] = 'GMT + 5.5 ชั่วโมง (New Delhi)';
$lang['tz']['6'] = 'GMT + 6 ชั่วโมง (Dhaka)';
$lang['tz']['6.5'] = 'GMT + 6.5 ชั่วโมง (Rangoon)';
$lang['tz']['7'] = 'GMT + 7 ชั่วโมง (Bangkok, Hanoi, Jakarta)';
$lang['tz']['8'] = 'GMT + 8 ชั่วโมง (Hong Kong, Kuala Lumpur, Singapore, Taipei)';
$lang['tz']['9'] = 'GMT + 9 ชั่วโมง (Osaka, Sapporo, Tokyo)';
$lang['tz']['9.5'] = 'GMT + 9.5 ชั่วโมง (Adelaide, Darwin)';
$lang['tz']['10'] = 'GMT + 10 ชั่วโมง (Melbourne, Sydney)';
$lang['tz']['11'] = 'GMT + 11 ชั่วโมง (Magadan, Solomon Is., New Caledonia)';
$lang['tz']['12'] = 'GMT + 12 ชั่วโมง (Auckland, Wellington, Fiji)';
$lang['tz']['13'] = 'GMT + 13 ชั่วโมง';

$lang['datetime']['Sunday'] = 'Sunday';
$lang['datetime']['Monday'] = 'Monday';
$lang['datetime']['Tuesday'] = 'Tuesday';
$lang['datetime']['Wednesday'] = 'Wednesday';
$lang['datetime']['Thursday'] = 'Thursday';
$lang['datetime']['Friday'] = 'Friday';
$lang['datetime']['Saturday'] = 'Saturday';
$lang['datetime']['Sun'] = 'Sun';
$lang['datetime']['Mon'] = 'Mon';
$lang['datetime']['Tue'] = 'Tue';
$lang['datetime']['Wed'] = 'Wed';
$lang['datetime']['Thu'] = 'Thu';
$lang['datetime']['Fri'] = 'Fri';
$lang['datetime']['Sat'] = 'Sat';
$lang['datetime']['January'] = 'January';
$lang['datetime']['February'] = 'February';
$lang['datetime']['March'] = 'March';
$lang['datetime']['April'] = 'April';
$lang['datetime']['May'] = 'May';
$lang['datetime']['June'] = 'June';
$lang['datetime']['July'] = 'July';
$lang['datetime']['August'] = 'August';
$lang['datetime']['September'] = 'September';
$lang['datetime']['October'] = 'October';
$lang['datetime']['November'] = 'November';
$lang['datetime']['December'] = 'December';
$lang['datetime']['Jan'] = 'Jan';
$lang['datetime']['Feb'] = 'Feb';
$lang['datetime']['Mar'] = 'Mar';
$lang['datetime']['Apr'] = 'Apr';
$lang['datetime']['May'] = 'May';
$lang['datetime']['Jun'] = 'Jun';
$lang['datetime']['Jul'] = 'Jul';
$lang['datetime']['Aug'] = 'Aug';
$lang['datetime']['Sep'] = 'Sep';
$lang['datetime']['Oct'] = 'Oct';
$lang['datetime']['Nov'] = 'Nov';
$lang['datetime']['Dec'] = 'Dec';

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = 'รายละเอียด';
$lang['Critical_Information'] = 'ข้อมูลฉุกเฉิน';

$lang['General_Error'] = 'ข้อผิดพลาดทั่วไป';
$lang['Critical_Error'] = 'ข้อผิดพลาดฉุกเฉิน';
$lang['An_error_occured'] = 'เกิดข้อผิดพลาด';
$lang['A_critical_error'] = 'เกิดข้อผิดพลาดฉุกเฉิน';

//
// That's all Folks!
// -------------------------------------------------

?>