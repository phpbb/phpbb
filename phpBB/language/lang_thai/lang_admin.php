<?php

/***************************************************************************
 *                            lang_admin.php [Thai]
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
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = 'General Admin';
$lang['Users'] = 'User Admin';
$lang['Groups'] = 'Group Admin';
$lang['Forums'] = 'Forum Admin';
$lang['Styles'] = 'Styles Admin';

$lang['Configuration'] = 'การตั้งค่า';
$lang['Permissions'] = 'การอนุญาต';
$lang['Manage'] = 'การจัดการ';
$lang['Disallow'] = 'ชื่อที่ไม่อนุญาต';
$lang['Prune'] = 'ลบหัวข้ออัตโนมัติ';
$lang['Mass_Email'] = 'ส่ง Email เป็นกลุ่ม';
$lang['Ranks'] = 'ระดับขั้น';
$lang['Smilies'] = 'รูปรอยยิ้ม';
$lang['Ban_Management'] = 'ควบคุมการหวงห้าม';
$lang['Word_Censor'] = 'คำหวงห้าม';
$lang['Export'] = 'ส่งออก';
$lang['Create_new'] = 'สร้าง';
$lang['Add_new'] = 'เพิ่ม';
$lang['Backup_DB'] = 'สำรองข้อมูล';
$lang['Restore_DB'] = 'กู้คืนข้อมูล';


//
// Index
//
$lang['Admin'] = 'Administration';
$lang['Not_admin'] = 'คุณไม่ได้เป็น administer ของบอร์ดนี้';
$lang['Welcome_phpBB'] = 'ยินดีต้อนรับสู่ phpBB';
$lang['Admin_intro'] = 'ขอบคุณที่ใช้ phpBB. หน้านี้จะแสดงค่าสถิติต่างๆของบอร์ด. คุณสามารถกลับมาที่หน้านี้ได้โดยคลิก <u>Admin Index</u> ที่ฝั่งซ้าย. ถ้าต้องการกลับไปที่บอร์ด ให้คลิกโลโก้ phpBB ที่ฝั่งซ้าย. ส่วนลิงค์อื่นๆที่ฝั่งซ้าย จะนำคุณไปสู่การควบคุมในส่วนต่างๆ แต่ละหน้าจะมีคำแนะนำการใช้บอกเอาไว้.';
$lang['Main_index'] = 'Forum Index';
$lang['Forum_stats'] = 'สถิติของ Forum';
$lang['Admin_Index'] = 'Admin Index';
$lang['Preview_forum'] = 'Preview Forum';

$lang['Click_return_admin_index'] = 'คลิก %sที่นี่%s เพื่อกลับไปหน้า Admin Index';

$lang['Statistic'] = 'สถิติ';
$lang['Value'] = 'ค่า';
$lang['Number_posts'] = 'จำนวน post';
$lang['Posts_per_day'] = 'Post ต่อวัน';
$lang['Number_topics'] = 'จำนวนหัวข้อ';
$lang['Topics_per_day'] = 'หัวข้อต่อวัน';
$lang['Number_users'] = 'จำนวนผู้ใช้';
$lang['Users_per_day'] = 'ผู้ใช้ต่อวัน';
$lang['Board_started'] = 'เริ่มใช้บอร์ด';
$lang['Avatar_dir_size'] = 'ขนาดของ directory รูปประจำตัว';
$lang['Database_size'] = 'ขนาดของฐานข้อมูล';
$lang['Gzip_compression'] ='การบีบอัดแบบ Gzip';
$lang['Not_available'] = '(ไม่ทราบ)';

$lang['ON'] = 'ใช้'; // This is for GZip compression
$lang['OFF'] = 'ไม่ใช้'; 


//
// DB Utils
//
$lang['Database_Utilities'] = 'เครื่องมือสำหรับฐานข้อมูล';

$lang['Restore'] = 'กู้คืน';
$lang['Backup'] = 'สำรอง';
$lang['Restore_explain'] = 'กู้คืนตาราง phpBB ทั้งหมดจากไฟล์ที่บันทึกไว้. ถ้า server สนับสนุนการบีบอัดแบบ gzip ก็จะถูกขยายโดยอัตโนมัติ. <b>คำเตือน</b> ข้อมูลเก่าจะถูกเขียนทับหมด. การกู้คืนจะใช้เวลาสักครู่ กรุณาอย่าเปลี่ยนหน้าจนกว่าจะทำงานเสร็จ';
$lang['Backup_explain'] = 'คุณสามารถสำรองข้อมูลของ phpBB. ถ้าคุณมีตารางส่วนตัวในฐานข้อมูล phpBB ที่คุณต้องการสำรองด้วย กรุณาป้อนชื่อและคั่นด้วยลูกน้ำ ลงในตารางเพิ่มเติมด้านล่าง. ถ้า server รองรับการบีบอัดแบบ gzip จะทำให้จำนวนข้อมูลที่คุณต้อง download น้อยลง<br>ตารางส่วนตัวที่ต้องเพิ่มคือ <b>attach_quota,attachments,attachments_config,attachments_desc,extension_groups,extensions,forbidden_extensions,quota_limits</b>';

$lang['Backup_options'] = 'ตัวเลือกการสำรอง';
$lang['Start_backup'] = 'เริ่มทำการสำรอง';
$lang['Full_backup'] = 'สำรองแบบเต็มที่';
$lang['Structure_backup'] = 'สำรองเฉพาะโครงสร้าง';
$lang['Data_backup'] = 'สำรองเฉพาะข้อมูล';
$lang['Additional_tables'] = 'ตารางเพิ่มเติม';
$lang['Gzip_compress'] = 'การบีบอัดไฟล์แบบ Gzip';
$lang['Select_file'] = 'เลือกไฟล์';
$lang['Start_Restore'] = 'เริ่มทำการกู้คืน';

$lang['Restore_success'] = 'กู้คืนฐานข้อมูลเรียบร้อยแล้ว<br /><br />บอร์ดจะกลับไปสู่สถานะสุดท้ายที่ทำการสำรอง';
$lang['Backup_download'] = 'การ download จะเริ่มขึ้นเดี๋ยวนี้ กรุณารอสักครู่';
$lang['Backups_not_supported'] = 'ขออภัย. ฐานข้อมูลนี้ไม่รองรับการสำรองข้อมูล';

$lang['Restore_Error_uploading'] = 'การ upload ไฟล์สำรอง เกิดการผิดพลาด';
$lang['Restore_Error_filename'] = 'ชื่อไฟล์มีปัญหา, กรุณาลองเปลี่ยนชื่อไฟล์ใหม่';
$lang['Restore_Error_decompress'] = 'ไม่สามารถขยายไฟล์บีบอัดแบบ gzip, กรุณา upload ไฟล์ชนิดตัวอักษรธรรมดา';
$lang['Restore_Error_no_file'] = 'ไม่มีไฟล์ที่ถูก upload';


//
// Auth pages
//
$lang['Select_a_User'] = 'เลือกผู้ใช้';
$lang['Select_a_Group'] = 'เลือกกลุ่ม';
$lang['Select_a_Forum'] = 'เลือก forum';
$lang['Auth_Control_User'] = 'ควบคุมการอนุญาตให้แก่ผู้ใช้'; 
$lang['Auth_Control_Group'] = 'ควบคุมการอนุญาตกลุ่ม'; 
$lang['Auth_Control_Forum'] = 'ควบคุมการอนุญาต forum'; 
$lang['Look_up_User'] = 'ตกลง'; 
$lang['Look_up_Group'] = 'ตกลง'; 
$lang['Look_up_Forum'] = 'ตกลง'; 

$lang['Group_auth_explain'] = 'คุณสามารถเปลี่ยนแปลงการอนุญาต และสถานะ moderator ของแต่ละกลุ่มผู้ใช้. อย่าลืมว่า เมื่อเปลี่ยนแปลงการอนุญาตของกลุ่มแล้ว แต่การอนุญาตสำหรับผู้ใช้รายบุคคลอาจยังอยู่ ซึ่งอาจทำให้ผู้ใช้นั้นยังสามารถเข้าไปสู่ forum ฯลฯ. ในกรณีนี้ คุณจะได้รับการแจ้งเตือน.';
$lang['User_auth_explain'] = 'คุณสามารถเปลี่ยนแปลงการอนุญาต และสถานะ moderator ของผู้ใช้แต่ละคน. อย่าลืมว่า เมื่อเปลี่ยนแปลงการอนุญาตของผู้ใช้แล้ว แต่การอนุญาตสำหรับกลุ่มผู้ใช้อาจยังอยู่ ซึ่งอาจทำให้ผู้ใช้นั้นยังสามารถเข้าไปสู่ forum ฯลฯ. ในกรณีนี้ คุณจะได้รับการแจ้งเตือน.';
$lang['Forum_auth_explain'] = 'คุณสามารถเปลี่ยนแปลงระดับการอนุญาตของแต่ละ forum. คุณสามารถเลือกแบบอย่างง่าย หรือแบบชั้นสูงก็ได้, วิธีชั้นสูงจะให้การควบคุม forum ในแต่ละการทำงานได้ดีกว่า. การเปลี่ยนแปลงระดับการอนุญาตของ forum จะมีผลกับผู้ใช้ที่สามารถกระทำการต่างๆนี้ได้.';

$lang['Simple_mode'] = 'แบบธรรมดา';
$lang['Advanced_mode'] = 'แบบชั้นสูง';
$lang['Moderator_status'] = 'สถานะ Moderator';

$lang['Allowed_Access'] = 'อนุญาตให้เข้าถึงได้';
$lang['Disallowed_Access'] = 'ไม่อนุญาตให้เข้าถึง';
$lang['Is_Moderator'] = 'เป็น Moderator';
$lang['Not_Moderator'] = 'ไม่ใช่ Moderator';

$lang['Conflict_warning'] = 'การอนุญาตเกิดการขัดแย้งกัน';
$lang['Conflict_access_userauth'] = 'ผู้ใช้นี้จะยังคงมีสิทธิ์เข้าไปยัง forum ผ่านทางสมาชิกกลุ่ม. คุณอาจต้องเปลี่ยนการอนุญาตกลุ่ม หรือลบผู้ใช้นี้ออกจากกลุ่ม. การอนุญาตของกลุ่ม (และของ forum ที่เกี่ยวข้อง) ได้อธิบายไว้ข้างล่างนี้';
$lang['Conflict_mod_userauth'] = 'ผู้ใช้นี้ยังคงมีสิทธิ์ของ moderator เข้าไปยัง forum. คุณอาจต้องเปลี่ยนการอนุญาตกลุ่ม หรือลบผู้ใช้นี้ออกจากกลุ่ม เพื่อป้องกันสิทธิ์การเป็น moderator. การอนุญาตของกลุ่ม (และของ forum ที่เกี่ยวข้อง) ได้อธิบายไว้ข้างล่างนี้';

$lang['Conflict_access_groupauth'] = 'ผู้ใช้(เหล่า)นี้ยังคงมีสิทธิ์เข้าไปยัง forum ผ่านทางสมาชิกกลุ่ม. คุณอาจต้องเปลี่ยนการอนุญาตสำหรับผู้ใช้. การอนุญาตของกลุ่ม (และของ forum ที่เกี่ยวข้อง) ได้แสดงไว้ด้านล่างนี้';
$lang['Conflict_mod_groupauth'] = 'ผู้ใช้(เหล่า)นี้ยังคงมีสิทธิ์ของ moderator เข้าไปยัง forum.  คุณอาจต้องเปลี่ยนการอนุญาตสำหรับผู้ใช้ เพื่อป้องกันสิทธิ์การเป็น moderator. การอนุญาตของกลุ่ม (และของ forum ที่เกี่ยวข้อง) ได้แสดงไว้ด้านล่างนี้';

$lang['Public'] = 'สาธารณะ';
$lang['Private'] = 'ส่วนตัว';
$lang['Registered'] = 'ลงทะเบียนแล้ว';
$lang['Administrators'] = 'Administrators';
$lang['Hidden'] = 'ถูกซ่อน';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = 'ทั้งหมด';
$lang['Forum_REG'] = 'ลงทะเบียนแล้ว';
$lang['Forum_PRIVATE'] = 'ส่วนตัว';
$lang['Forum_MOD'] = 'MOD';
$lang['Forum_ADMIN'] = 'ADMIN';

$lang['View'] = 'ดู';
$lang['Read'] = 'อ่าน';
$lang['Post'] = 'Post';
$lang['Reply'] = 'ตอบ';
$lang['Edit'] = 'แก้ไข';
$lang['Delete'] = 'ลบ';
$lang['Sticky'] = 'Sticky';
$lang['Announce'] = 'ประกาศ'; 
$lang['Vote'] = 'ลงคะแนน';
$lang['Pollcreate'] = 'สร้างแบบสอบถาม';

$lang['Permissions'] = 'การอนุญาต';
$lang['Simple_Permission'] = 'การอนุญาตพื้นฐาน';

$lang['User_Level'] = 'ระดับผู้ใช้'; 
$lang['Auth_User'] = 'ผู้ใช้';
$lang['Auth_Admin'] = 'Administrator';
$lang['Group_memberships'] = 'สถานะการเป็นสมาชิกกลุ่ม';
$lang['Usergroup_members'] = 'ในกลุ่มมีรายชื่อผู้ใช้ดังนี้';

$lang['Forum_auth_updated'] = 'ปรับปรุงการอนุญาตสำหรับ forum แล้ว';
$lang['User_auth_updated'] = 'ปรับปรุงการอนุญาตสำหรับผู้ใช้แล้ว';
$lang['Group_auth_updated'] = 'ปรับปรุงการอนุญาตสำหรับกลุ่มแล้ว';

$lang['Auth_updated'] = 'ปรับปรุงการอนุญาตแล้ว';
$lang['Click_return_userauth'] = 'คลิก %sที่นี่%s เพื่อกลับไปหน้าการอนุญาตสำหรับผู้ใช้';
$lang['Click_return_groupauth'] = 'คลิก %sที่นี่%s เพื่อกลับไปหน้าการอนุญาตสำหรับกลุ่ม';
$lang['Click_return_forumauth'] = 'คลิก %sที่นี่%s เพื่อกลับไปหน้าการอนุญาตสำหรับ forum';


//
// Banning
//
$lang['Ban_control'] = 'ควบคุมการหวงห้าม';
$lang['Ban_explain'] = 'คุณสามารถควบคุมการหวงห้ามผู้ใช้ได้ที่นี่. คุณสามารถหวงห้ามผู้ใช้รายบุคคล และ/หรือ กลุ่มของ IP address หรือ hostnames. วิธีนี้จะป้องกันผู้ใช้ไม่ให้เข้าถึงหน้าแรกของบอร์ด. ถ้าจะป้องกันผู้ใช้จากการสมัครสมาชิกโดยใช้ชื่อต่างๆ คุณสามารถระบุ e-mail ที่ต้องการหวงห้าม. แต่การหวงห้าม e-mail เพียงอย่างเดียวจะไม่สามารถป้องกันผู้ใช้นั้นจากการเข้าสู่ระบบ หรือโพสต์ในบอร์ด คุณควรใช้หนึ่งในสองวิธีแรกสำหรับแก้ไขปัญหานี้.';
$lang['Ban_explain_warn'] = 'โปรดระวังว่า การระบุกลุ่มของ IP address จะมีผลกับทุก IP ที่อยู่ระหว่าง IP เริ่มต้นและสิ้นสุด. หมายเลข IP ที่จะบันทึกลงฐานข้อมูลจะใช้ wildcards โดยอัตโนมัติเพื่อลดขนาดของ IP. ถ้าคุณต้องการระบุช่วงของ IP จริงๆ กรุณาระบุช่วงให้แคบที่สุดเท่าที่จะทำได้.';

$lang['Select_username'] = 'เลือก username';
$lang['Select_ip'] = 'เลือก IP';
$lang['Select_email'] = 'เลือก Email';

$lang['Ban_username'] = 'หวงห้ามผู้ใช้ 1 คนหรือมากกว่า';
$lang['Ban_username_explain'] = 'คุณสามารถหวงห้ามผู้ใช้หลายคนในครั้งเดียว โดยใช้เมาส์และคีย์บอร์ดร่วมกัน';

$lang['Ban_IP'] = 'หวงห้าม IP 1 หมายเลขหรือมากกว่า หรือชื่อ host';
$lang['IP_hostname'] = 'หมายเลข IP หรือชือ host';
$lang['Ban_IP_explain'] = 'เพื่อระบุหลายหมายเลข IP หรือหลาย hostname ให้คั่นแต่ละชื่อด้วยลูกน้ำ. ถ้าจะระบุช่วงของ IP ให้แยกหมายเลขเริ่มต้นและสิ้นสุดด้วยขีด (-), ถ้าจะระบุ wildcard ให้ใช้ *';

$lang['Ban_email'] = 'หวงห้าม 1 Email หรือมากกว่า';
$lang['Ban_email_explain'] = 'ถ้าจะระบุมากกว่า 1 email ให้คั่นด้วยลูกน้ำ. ถ้าจะระบุโดยใช้ wildcard ให้ใช้ * เช่น *@hotmail.com';

$lang['Unban_username'] = 'ยกเลิกการหวงห้ามผู้ใช้ 1 คนหรือมากกว่า';
$lang['Unban_username_explain'] = 'คุณสามารถยกเลิกการหวงห้ามหลายชื่อในครั้งเดียว โดยใช้เมาส์และคีย์บอร์ดร่วมกัน';

$lang['Unban_IP'] = 'ยกเลิกการหวงห้าม IP 1หมายเลขหรือมากกว่า';
$lang['Unban_IP_explain'] = 'คุณสามารถยกเลิกการหวงห้ามหลาย IP ในครั้งเดียว โดยใช้เมาส์และคีย์บอร์ดร่วมกัน';

$lang['Unban_email'] = 'ยกเลิกการหวงห้าม 1 Email หรือมากกว่า';
$lang['Unban_email_explain'] = 'คุณสามารถยกเลิกการหวงห้ามหลาย email ในครั้งเดียว โดยใช้เมาส์และคีย์บอร์ดร่วมกัน';

$lang['No_banned_users'] = 'ไม่มีการหวงห้ามผู้ใช้';
$lang['No_banned_ip'] = 'ไม่มีการหวงห้ามหมายเลข IP';
$lang['No_banned_email'] = 'ไม่มีการหวงห้าม Email';

$lang['Ban_update_sucessful'] = 'ปรับปรุงรายชื่อการหวงห้ามแล้ว';
$lang['Click_return_banadmin'] = 'คลิก %sที่นี่%s เพื่อกลับไปหน้าควบคุมการหวงห้าม';


//
// Configuration
//
$lang['General_Config'] = 'การตั้งค่าทั่วไป';
$lang['Config_explain'] = 'แบบฟอร์มด้านล่างนี้ จะยอมให้คุณปรับแต่งค่าต่างๆของบอร์ด. ให้ใช้ลิงค์อื่นๆที่เกี่ยวข้องที่ฝั่งซ้าย เพื่อการตั้งค่าสำหรับผู้ใช้และ forum .';

$lang['Click_return_config'] = 'คลิก %sที่นี่%s เพื่อกลับไปหน้าการตั้งค่าทั่วไป';

$lang['General_settings'] = 'ตั้งค่าทั่วไปของบอร์ด';
$lang['Server_name'] = 'Domain Name';
$lang['Server_name_explain'] = 'บอร์ดนี้ถูกเรียกจาก Domain Name';
$lang['Script_path'] = 'ตำแหน่งโปรแกรม';
$lang['Script_path_explain'] = 'ตำแหน่งของ phpBB2 จะสัมพันธ์กับ Domain Name';
$lang['Server_port'] = 'Server Port';
$lang['Server_port_explain'] = 'หมายเลข port ของ server, ปรกติคือ 80';
$lang['Site_name'] = 'ชื่อเว็บ';
$lang['Site_desc'] = 'รายละเอียดของเว็บ';
$lang['Board_disable'] = 'ยกเลิกบอร์ด';
$lang['Board_disable_explain'] = 'ทำให้บอร์ดใช้งานไม่ได้. อย่าออกจากระบบเมื่อคุณยกเลิกบอร์ด เพราะคุณจะไม่สามารถเข้าสู่ระบบได้อีก!';
$lang['Acct_activation'] = 'ต้องยืนยันบัญชีด้วย';
$lang['Acc_None'] = 'ไม่ต้อง'; // These three entries are the type of activation
$lang['Acc_User'] = 'User';
$lang['Acc_Admin'] = 'Admin';

$lang['Abilities_settings'] = 'การตั้งค่าพื้นฐานของผู้ใช้และ forum';
$lang['Max_poll_options'] = 'จำนวนตัวเลือกสำหรับแบบสอบถาม ไม่เกิน';
$lang['Flood_Interval'] = 'ระยะเวลาป้องกันการส่งข้อความซ้ำกัน';
$lang['Flood_Interval_explain'] = 'จำนวนวินาที ที่ผู้ใช้ต้องรอระหว่างการ post'; 
$lang['Board_email_form'] = 'ส่ง Email ระหว่างผู้ใช้ผ่านบอร์ด';
$lang['Board_email_form_explain'] = 'อนุญาตให้ผู้ใช้ส่ง Email ถึงกัน ผ่านโปรแกรมของบอร์ดนี้';
$lang['Topics_per_page'] = 'จำนวนหัวข้อต่อหน้า';
$lang['Posts_per_page'] = 'จำนวนโพสต์ต่อหน้า';
$lang['Hot_threshold'] = 'จำนวนโพสต์ ที่ถือว่าได้รับความนิยม';
$lang['Default_style'] = 'รูปแบบมาตรฐาน';
$lang['Override_style'] = 'บังคับใช้กับผู้ใช้ทุกคน';
$lang['Override_style_explain'] = 'แทนที่รูปแบบที่ผู้ใช้เลือกเอง ด้วยรูปแบบมาตรฐาน';
$lang['Default_language'] = 'ภาษามาตรฐาน';
$lang['Date_format'] = 'รูปแบบวันที่';
$lang['System_timezone'] = 'การปรับเวลา timezone';
$lang['Enable_gzip'] = 'ใช้การบีบอัดแบบ GZip';
$lang['Enable_prune'] = 'ยอมให้ลบอัตโนมัติใน forum';
$lang['Allow_HTML'] = 'ยอมให้ใช้ HTML';
$lang['Allow_BBCode'] = 'ยอมให้ใช้ BBCode';
$lang['Allowed_tags'] = 'ยอมให้ใช้คำสั่ง HTML';
$lang['Allowed_tags_explain'] = 'คั่นแต่ละคำสั่งด้วยลูกน้ำ';
$lang['Allow_smilies'] = 'ยอมให้ใช้รูปรอยยิ้ม';
$lang['Smilies_path'] = 'ตำแหน่งที่ใช้เก็บรูปรอยยิ้ม';
$lang['Smilies_path_explain'] = 'อยู่ใน rood directory ของ phpBB เช่น images/smiles';
$lang['Allow_sig'] = 'ยอมให้ใช้ลายเซ็น';
$lang['Max_sig_length'] = 'ความยาวลายเซ็น ไม่เกิน';
$lang['Max_sig_length_explain'] = 'จำนวนตัวอักษรสูงสุดของลายเซ็น';
$lang['Allow_name_change'] = 'ยอมให้เปลี่ยน username';

$lang['Avatar_settings'] = 'การตั้งค่ารูปประจำตัว';
$lang['Allow_local'] = 'ใช้รูปที่มีให้';
$lang['Allow_remote'] = 'ใช้รูปจากที่อื่น';
$lang['Allow_remote_explain'] = 'รูปประจำตัวที่อยู่บนเว็บอื่น';
$lang['Allow_upload'] = 'สามารถ upload รูปประจำตัวได้';
$lang['Max_filesize'] = 'ขนาดไฟล์รูปประจำตัว ไม่เกิน';
$lang['Max_filesize_explain'] = 'สำหรับการ upload ไฟล์รูปประจำตัว';
$lang['Max_avatar_size'] = 'ขนาดรูปประจำตัว ไม่เกิน';
$lang['Max_avatar_size_explain'] = '(สูง x กว้าง ในหน่วย pixels)';
$lang['Avatar_storage_path'] = 'ตำแหน่งเก็บรูปประจำตัว';
$lang['Avatar_storage_path_explain'] = 'อยู่ใน root directory ของ phpBB เช่น images/avatars';
$lang['Avatar_gallery_path'] = 'ตำแหน่งรูปประจำตัวที่มีให้';
$lang['Avatar_gallery_path_explain'] = 'อยู่ใน directory ของข้างบนนี้ เช่น images/avatars/gallery';

$lang['COPPA_settings'] = 'COPPA Settings';
$lang['COPPA_fax'] = 'COPPA Fax Number';
$lang['COPPA_mail'] = 'COPPA Mailing Address';
$lang['COPPA_mail_explain'] = 'This is the mailing address where parents will send COPPA registration forms';

$lang['Email_settings'] = 'การตั้งค่า Email';
$lang['Admin_email'] = 'Email ของ Admin';
$lang['Email_sig'] = 'ลายเซ็นของ Email';
$lang['Email_sig_explain'] = 'ข้อความนี้จะแนบไปกับ Email ทุกฉบับ เมื่อส่งจากบอร์ด';
$lang['Use_SMTP'] = 'ใช้ SMTP Server ในการส่ง Email';
$lang['Use_SMTP_explain'] = 'ตอบ ใช่ เมื่อคุณต้องการหรือจำเป็นต้องส่ง Email ผ่าน SMTP server แทนที่จะใช้ฟังก์ชันส่ง Email ของตัวโปรแกรมเอง';
$lang['SMTP_server'] = 'ที่อยู่ของ SMTP Server';
$lang['SMTP_username'] = 'SMTP Username';
$lang['SMTP_username_explain'] = 'ให้ป้อน username เมื่อจำเป็นต้องใช้เท่านั้น';
$lang['SMTP_password'] = 'SMTP Password';
$lang['SMTP_password_explain'] = 'ให้ป้อน password เมื่อจำเป็นต้องใช้เท่านั้น';

$lang['Disable_privmsg'] = 'ข้อความส่วนตัว';
$lang['Inbox_limits'] = 'ข้อความใน Inbox ไม่เกิน';
$lang['Sentbox_limits'] = 'ข้อความใน Sentbox ไม่เกิน';
$lang['Savebox_limits'] = 'ข้อความใน Savebox ไม่เกิน';

$lang['Cookie_settings'] = 'การตั้งค่า Cookie'; 
$lang['Cookie_settings_explain'] = 'รายละเอียดนี้จะใช้กับ cookies ที่ถูกส่งไปที่ browsers ของคุณ. โดยทั่วไปค่ามาตรฐานก็เพียงพอแล้ว แต่ถ้าคุณต้องการเปลี่ยนแปลง กรุณาทำด้วยความระมัดระวัง การตั้งค่าที่ผิดพลาด จะทำให้ผู้ใช้ไม่สามารถเข้าสู่ระบบได้';
$lang['Cookie_domain'] = 'Cookie Domain';
$lang['Cookie_name'] = 'ชื่อ Cookie';
$lang['Cookie_path'] = 'ตำแหน่งของ Cookie';
$lang['Cookie_secure'] = 'ความปลอดภัยของ Cookie';
$lang['Cookie_secure_explain'] = 'ถ้า server ของคุณใช้ SSL ก็ให้เปิดใช้งาน';
$lang['Session_length'] = 'ระยะเวลา [ วินาที ]';


//
// Forum Management
//
$lang['Forum_admin'] = 'Forum Administration';
$lang['Forum_admin_explain'] = 'ในส่วนนี้คุณสามาระเพิ่ม, ลบ, แก้ไข, เรียงลำดับ, จัดเรียงกลุ่มและ forum ใหม่';
$lang['Edit_forum'] = 'แก้ไข forum';
$lang['Create_forum'] = 'สร้าง forum ใหม่';
$lang['Create_category'] = 'สร้างกลุ่มใหม่';
$lang['Remove'] = 'ลบ';
$lang['Action'] = 'กระทำ';
$lang['Update_order'] = 'ปรับปรุงลำดับ';
$lang['Config_updated'] = 'ปรับปรุงการตั้งค่าของ forum แล้ว';
$lang['Edit'] = 'แก้ไข';
$lang['Delete'] = 'ลบ';
$lang['Move_up'] = 'เลื่อนขึ้น';
$lang['Move_down'] = 'เลื่อนลง';
$lang['Resync'] = 'เรียงลำดับใหม่';
$lang['No_mode'] = 'ไม่มีการตั้ง mode';
$lang['Forum_edit_delete_explain'] = 'แบบฟอร์มด้านล่างนี้ จะยอมให้คุณปรับแต่งการตั้งค่าทั่วไปของบอร์ด. ให้ใช้ลิงค์ที่ฝั่งซ้าย เพื่อการตั้งค่าสำหรับผู้ใช้และ forum';

$lang['Move_contents'] = 'ย้ายข้อความทั้งหมด';
$lang['Forum_delete'] = 'ลบ forum';
$lang['Forum_delete_explain'] = 'แบบฟอร์มด้านล่างนี้ จะยอมให้คุณลบ forum (หรือกลุ่ม) และให้คุณเลือกว่า จะนำหัวข้อ (หรือ forum) ไปเก็บไว้ที่ใด.';

$lang['Status_locked'] = 'ถูกล็อก';
$lang['Status_unlocked'] = 'ถูกปลดล็อก';
$lang['Forum_settings'] = 'การตั้งค่าพื้นฐานของ forum';
$lang['Forum_name'] = 'ชื่อ forum';
$lang['Forum_desc'] = 'รายละเอียด';
$lang['Forum_status'] = 'สถานะของ forum';
$lang['Forum_pruning'] = 'ลบอัตโนมัติ';

$lang['prune_freq'] = 'ตรวจสอบอายุของหัวข้อทุกๆ';
$lang['prune_days'] = 'ถอดหัวข้อออก เมื่อไม่มีการ post เกิน';
$lang['Set_prune_data'] = 'คุณได้เปิดระบบลบหัวข้ออัตโนมัติ ให้กับ forum นี้ แต่ไม่ได้ตั้งระยะเวลาหรือจำนวนวันเพื่อตรวจสอบ. กรุณาคลิก back เพื่อกลับไปแก้ไข';

$lang['Move_and_Delete'] = 'ย้ายและลบ';

$lang['Delete_all_posts'] = 'ลบข้อความทั้งหมด';
$lang['Nowhere_to_move'] = 'ไม่ได้ระบุปลายทางที่จะย้าย';

$lang['Edit_Category'] = 'แก้ไขกลุ่ม';
$lang['Edit_Category_explain'] = 'ใช้แบบฟอร์มนี้เพื่อแก้ไขชื่อกลุ่ม.';

$lang['Forums_updated'] = 'ปรับปรุงข้อมูลของ forum และกลุ่มแล้ว';

$lang['Must_delete_forums'] = 'คุณจะต้องลบทุก forum ก่อนที่จะลบกลุ่มนี้ได้';

$lang['Click_return_forumadmin'] = 'คลิก %sที่นี่%s เพื่อกลับไปหน้า Forum Administration';


//
// Smiley Management
//
$lang['smiley_title'] = 'เครื่องมือแก้ไขรูปรอยยิ้ม';
$lang['smile_desc'] = 'ที่หน้านี้ คุณสามารถเพิ่ม, ลบ และแก้ไข รูปแสดงอารมณ์ หรือรูปรอยยิ้ม ที่ผู้ใช้สามารถใช้ในข้อความที่โพสต์และข้อความส่วนตัว.';

$lang['smiley_config'] = 'การตั้งค่ารูปรอยยิ้ม';
$lang['smiley_code'] = 'รหัสรูปรอยยิ้ม';
$lang['smiley_url'] = 'รูปภาพรอยยิ้ม';
$lang['smiley_emot'] = 'รูปภาพแสดงอารมณ์';
$lang['smile_add'] = 'เพิ่มรูปรอยยิ้ม';
$lang['Smile'] = 'รูปรอยยิ้ม';
$lang['Emotion'] = 'รูปแสดงอารมณ์';

$lang['Select_pak'] = 'เลือกไฟล์ Pack (.pak)';
$lang['replace_existing'] = 'แทนที่รูปรอยยิ้มที่มีอยู่';
$lang['keep_existing'] = 'เก็บรูปรอยยิ้มที่มีอยู่';
$lang['smiley_import_inst'] = 'คุณควร unzip แพ็คของรูปรอยยิ้ม แล้วส่งทุกไฟล์ขึ้นไปที่ directory สำหรับรูปรอยยิ้มเพื่อการติดตั้ง.  จากนั้นให้เลือกข้อมูลที่ถูกต้องในแบบฟอร์มนี้ เพื่อนำเข้ารูปรอยยิ้ม.';
$lang['smiley_import'] = 'นำเข้าแพ็คของรูปรอยยิ้ม';
$lang['choose_smile_pak'] = 'เลือกไฟล์แพ็ค (.pak) ของรูปรอยยิ้ม';
$lang['import'] = 'นำเข้ารูปรอยยิ้ม';
$lang['smile_conflicts'] = 'ควรจะทำอย่างไรเมื่อพบรูปรอยยิ้มเก่า';
$lang['del_existing_smileys'] = 'ลบรูปรอยยิ้มเก่าก่อนนำเข้า';
$lang['import_smile_pack'] = 'นำเข้าแพ็คของรูปรอยยิ้ม';
$lang['export_smile_pack'] = 'สร้างแพ็คของรูปรอยยิ้ม';
$lang['export_smiles'] = 'การสร้างแพ็คของรูปรอยยิ้มจากรูปรอยยิ้มที่ได้ติดตั้งไว้ ให้คลิก %sที่นี่%s เพื่อ download ไฟล์ smiles.pak ตรวจดูให้แน่นอนว่านามสุกลของไฟล์เป็น .pak จากนั้นให้สร้างไฟล์ zip ที่บรรจุรูปภาพรอยยิ้มทั้งหมด รวมไว้กับไฟล์การตั้งค่า .pak ไฟล์นี้';

$lang['smiley_add_success'] = 'เพิ่มรูปรอยยิ้มเรียบร้อยแล้ว';
$lang['smiley_edit_success'] = 'ปรับปรุงรูปรอยยิ้มเรียบร้อยแล้ว';
$lang['smiley_import_success'] = 'นำเข้าแพ็คของรูปรอยยิ้มเรียบร้อยแล้ว';
$lang['smiley_del_success'] = 'ลบรูปรอยยิ้มเรียบร้อยแล้ว';
$lang['Click_return_smileadmin'] = 'คลิก %sที่นี่%s เพื่อกลับไป Smiley Administration';


//
// User Management
//
$lang['User_admin'] = 'User Administration';
$lang['User_admin_explain'] = 'คุณสามารถเปลี่ยนแปลงรายละเอียดของผู้ใช้ และตัวเลือกต่างๆ. ถ้าจะแก้ไขการอนุญาต ให้ใช้ระบบการอนุญาตสำหรับผู้ใช้และกลุ่ม';

$lang['Look_up_user'] = 'ตกลง';

$lang['Admin_user_fail'] = 'ไม่สามารถปรับปรุงข้อมูลส่วนตัวของผู้ใช้ได้';
$lang['Admin_user_updated'] = 'ปรับปรุงข้อมูลส่วนตัวของผู้ใช้แล้ว';
$lang['Click_return_useradmin'] = 'คลิก %sที่นี่%s เพื่อกลับไปหน้า User Administration';

$lang['User_delete'] = 'ลบผู้ใช้นี้';
$lang['User_delete_explain'] = 'คลิกที่นี่เพื่อลบผู้ใช้นี้, ไม่สามารถยกเลิกได้';
$lang['User_deleted'] = 'ลบผู้ใช้นี้แล้ว';

$lang['User_status'] = 'เปิดใช้งานผู้ใช้นี้';
$lang['User_allowpm'] = 'สามารถส่งข้อความส่วนตัวไปหาได้';
$lang['User_allowavatar'] = 'สามารถใช้รูปประจำตัวได้';

$lang['Admin_avatar_explain'] = 'คุณสามารถดูและลบรูปประจำตัวของผู้ใช้ได้ที่นี่';

$lang['User_special'] = 'ส่วนพิเศษ สำหรับ admin เท่านั้น';
$lang['User_special_explain'] = 'ส่วนนี้ไม่สามารถแก้ไขได้โดยผู้ใช้. คุณสามารถตั้งสถานะและตัวเลือกอื่นๆ ที่ผู้ใช้ไม่สามารถทำได้';


//
// Group Management
//
$lang['Group_administration'] = 'Group Administration';
$lang['Group_admin_explain'] = 'คุณสามารถจัดการกลุ่มผู้ใช้ได้ที่นี่ คุณสามารถ ลบ, สร้าง และแก้ไขกลุ่มผู้ใช้ที่มีอยู่ คุณอาจเลือก moderator, สลับสถานะเปิด/ปิดกลุ่ม และตั้งชื่อกลุ่มและคำอธิบาย';
$lang['Error_updating_groups'] = 'เกิดข้อผิดพลาดระหว่างปรับปรุงกลุ่ม';
$lang['Updated_group'] = 'ปรับปรุงกลุ่มเรียบร้อยแล้ว';
$lang['Added_new_group'] = 'สร้างกลุ่มใหม่เรียบร้อยแล้ว';
$lang['Deleted_group'] = 'ลบกลุ่มเรียบร้อยแล้ว';
$lang['New_group'] = 'สร้างกลุ่มใหม่';
$lang['Edit_group'] = 'แก้ไขกลุ่ม';
$lang['group_name'] = 'ชื่อกลุ่ม';
$lang['group_description'] = 'คำอธิบายกลุ่ม';
$lang['group_moderator'] = 'moderator ของกลุ่ม';
$lang['group_status'] = 'สถานะกลุ่ม';
$lang['group_open'] = 'กลุ่มเปิด';
$lang['group_closed'] = 'กลุ่มถูกปิด';
$lang['group_hidden'] = 'กลุ่มถูกซ่อน';
$lang['group_delete'] = 'ลบกลุ่ม';
$lang['group_delete_check'] = 'ลบกลุ่มนี้';
$lang['submit_group_changes'] = 'บันทึกการเปลี่ยนแปลง';
$lang['reset_group_changes'] = 'ยกเลิกการเปลี่ยนแปลง';
$lang['No_group_name'] = 'คุณต้องระบุชื่อกลุ่ม';
$lang['No_group_moderator'] = 'คุณต้องระบุผู้ที่จะเป็น moderator ของกลุ่ม';
$lang['No_group_mode'] = 'คุณต้องระบุสถานะของกลุ่ม ว่าเปิด หรือถูกปิด';
$lang['No_group_action'] = 'ไม่ได้ระบุการกระทำ';
$lang['delete_group_moderator'] = 'ลบ moderator เก่าของกลุ่ม?';
$lang['delete_moderator_explain'] = 'ถ้าคุณกำลังเปลี่ยนแปลง moderator ของกลุ่ม ให้เลือกกล่องนี้เพื่อลบ moderator เก่าออกจากกลุ่มด้วย หรือถ้าไม่เลือก ผู้ใช้นี้จะกลายเป็นสมาชิกธรรมดาของกลุ่ม';
$lang['Click_return_groupsadmin'] = 'คลิก %sที่นี่%s เพื่อกลับไป Group Administration.';
$lang['Select_group'] = 'เลือกกลุ่ม';
$lang['Look_up_group'] = 'ตกลง';


//
// Prune Administration
//
$lang['Forum_Prune'] = 'การลบอัตโนมัติใน forum';
$lang['Forum_Prune_explain'] = 'ลบหัวข้อที่ไม่มีผู้ post ตามจำนวนวันที่กำหนด. ถ้าคุณไม่ระบุจำนวนวัน, ทุกหัวข้อจะถูกลบทิ้ง. แต่จะไม่ถอดหัวข้อที่แบบสอบถามกำลังทำงานอยู่ และจะไม่ถอดข้อความประกาศ. คุณจะต้องถอดหัวข้อเหล่านี้ด้วยตัวเอง.';
$lang['Do_Prune'] = 'ทำการลบอัตโนมัติ';
$lang['All_Forums'] = 'ทุก forum';
$lang['Prune_topics_not_posted'] = 'ลบหัวข้อที่ไม่มีการตอบเป็นจำนวนวันตามนี้';
$lang['Topics_pruned'] = 'หัวข้อที่ถูกลบอัตโนมัติ';
$lang['Posts_pruned'] = 'Post ที่ถูกลบอัตโนมัติ';
$lang['Prune_success'] = 'ทำการลบอัตโนมัติใน forum แล้ว';


//
// Word censor
//
$lang['Words_title'] = 'คำหวงห้าม';
$lang['Words_explain'] = 'คุณสามารถเพิ่ม, แก้ไข, และลบคำ ที่ห้ามใช้ใน forums. และผู้ใช้จะไม่สามารถลงทะเบียนโดยใช้ชื่อที่มีคำเหล่านี้อยู่. คุณสามารถใช้ Wildcards (*) ผสมในคำได้ เช่น *test* จะหวงห้าม detestable, test* จะหวงห้าม testing, *test จะหวงห้าม detest.';
$lang['Word'] = 'คำหวงห้าม';
$lang['Edit_word_censor'] = 'แก้ไขคำหวงห้าม';
$lang['Replacement'] = 'คำที่จะถูกแทนที่ลงไป';
$lang['Add_new_word'] = 'เพิ่มคำใหม่';
$lang['Update_word'] = 'ปรับปรุงคำหวงห้าม';

$lang['Must_enter_word'] = 'คุณต้องระบุคำหวงห้าม และคำที่จะถูกแทนที่ลงไปแทน';
$lang['No_word_selected'] = 'ไม่ได้เลือกคำที่ต้องการแก้ไข';

$lang['Word_updated'] = 'ปรับปรุงคำหวงห้ามที่ถูกเลือกเรียบร้อยแล้ว';
$lang['Word_added'] = 'เพิ่มคำหวงห้ามเรียบร้อยแล้ว';
$lang['Word_removed'] = 'ลบคำหวงห้ามที่ถูกเลือกเรียบร้อยแล้ว';

$lang['Click_return_wordadmin'] = 'คลิก %sที่นี่%s เพื่อกลับไปหน้า Word Censor Administration';


//
// Mass Email
//
$lang['Mass_email_explain'] = 'คุณสามารถส่ง e-mail ไปให้ผู้ใช้ทุกคน หรือผู้ใช้ทุกคนภายในกลุ่มที่ระบุ. Email นี้จะถูกส่งไปที่ email address ของ admin ที่ระบุไว้ และส่งเป็น BCC (blind carbon copy) แก่ผู้รับทุกคน. ถ้าคุณส่ง email ให้แก่ผู้ใช้กลุ่มใหญ่ กรุณาอดทนรอหลังกดส่ง และอย่ากดปุ่มหยุดระหว่างการทำงาน. เป็นเหตุการณ์ปรกติที่ต้องใช้เวลานาน คุณจะได้รับการแจ้งเมื่อระบบทำงานเสร็จเรียบร้อยแล้ว';
$lang['Compose'] = 'เขียน email'; 

$lang['Recipients'] = 'ผู้รับ'; 
$lang['All_users'] = 'ผู้ใช้ทุกคน';

$lang['Email_successfull'] = 'ข้อความถูกส่งแล้ว';
$lang['Click_return_massemail'] = 'คลิก %sที่นี่%s เพื่อกลับไปแบบฟอร์ม Mass Email';


//
// Ranks admin
//
$lang['Ranks_title'] = 'Rank Administration';
$lang['Ranks_explain'] = 'คุณสามารถเพิ่ม, แก้ไข, ดู และลบระดับขั้น. และคุณสามารถสร้างระดับขั้นเพิ่มเติมเพื่อใช้กับผู้ใช้อื่นๆ ผ่านทางการจัดการผู้ใช้';

$lang['Add_new_rank'] = 'เพิ่มระดับขั้นใหม่';

$lang['Rank_title'] = 'ชื่อระดับขั้น';
$lang['Rank_special'] = 'ตั้งให้เป็นระดับขั้นพิเศษ';
$lang['Rank_minimum'] = 'จำนวน post น้อยสุด';
$lang['Rank_maximum'] = 'จำนวน post มากสุด';
$lang['Rank_image'] = 'รูปภาพของระดับขั้นนี้ (ในตำแหน่ง root ของ phpBB2)';
$lang['Rank_image_explain'] = 'คำอธิบายรูปเล็กๆ สำหรับแต่ละระดับขั้น';

$lang['Must_select_rank'] = 'คุณต้องเลือกระดับขั้น';
$lang['No_assigned_rank'] = 'ไม่ได้กำหนดระดับขั้น';

$lang['Rank_updated'] = 'ปรับปรุงระดับขั้นเรียบร้อยแล้ว';
$lang['Rank_added'] = 'เพิ่มระดับขั้นเรียบร้อยแล้ว';
$lang['Rank_removed'] = 'ลบระดับขั้นเรียบร้อยแล้ว';
$lang['No_update_ranks'] = 'ลบระดับขั้นเรียบร้อยแล้ว แต่ว่าผู้ใช้ที่กำลังใช้ระดับขั้นนี้อยู่จะยังไม่ได้รับการปรับปรุง. คุณจะต้องแก้ไขแต่ละชื่อบัญชีด้วยตัวเอง';

$lang['Click_return_rankadmin'] = 'คลิก %sที่นี่%s เพื่อกลับไป Rank Administration';


//
// Disallow Username Admin
//
$lang['Disallow_control'] = 'การควบคุม username ที่ไม่อนุญาต';
$lang['Disallow_explain'] = 'คุณสามารถควบคุมผู้ใช้ที่ไม่อนุญาตให้ใช้งาน. การไม่อนุญาตผู้ใช้ อาจใช้ตัวอักษร wildcard คือ *. คุณไม่สามารถไม่อนุญาตชื่อผู้ใช้ ที่ได้ทำการลงทะเบียนไว้ก่อนแล้ว คุณจะต้องลบผู้ใช้นั้น ก่อนจะทำการไม่อนุญาต';

$lang['Delete_disallow'] = 'ลบ';
$lang['Delete_disallow_title'] = 'ลบ username ที่ไม่อนุญาต';
$lang['Delete_disallow_explain'] = 'คุณสามารถลบ username ที่ไม่อนุญาต โดยเลือก username จากรายการ แล้วคลิกปุ่ม ลบ';

$lang['Add_disallow'] = 'เพิ่ม';
$lang['Add_disallow_title'] = 'เพิ่ม username ที่ไม่อนุญาต';
$lang['Add_disallow_explain'] = 'คุณสามารถระบุโดยใช้เครื่องหมาย * แทนที่ตัวอักษรใดๆก็ได้';

$lang['No_disallowed'] = 'ไม่มีรายชื่อ username ที่ไม่อนุญาต';

$lang['Disallowed_deleted'] = 'ลบ username ที่ไม่อนุญาตแล้ว';
$lang['Disallow_successful'] = 'เพิ่ม username ที่ไม่อนุญาตแล้ว';
$lang['Disallowed_already'] = 'ไม่สามารถเพิ่ม username ได้. อาจจะมี username นี้ในรายการ, อยู่ในกลุ่มคำหยาบ, หรือไม่มี username นี้อยู่';

$lang['Click_return_disallowadmin'] = 'คลิก %sที่นี่%s เพื่อกลับไปหน้า Disallow Username Administration';


//
// Styles Admin
//
$lang['Styles_admin'] = 'Styles Administration';
$lang['Styles_explain'] = 'คุณสามารถเพิ่ม, ลบ และจัดการรูปแบบ (template และ theme) สำหรับผู้ใช้อื่นๆ';
$lang['Styles_addnew_explain'] = 'รายการนี้จะเป็นรูปแบบทั้งหมดที่คุณมี. บางรูปแบบอาจยังไม่ได้รับการติดตั้งลงฐานข้อมูล phpBB. ให้คลิก ติดตั้ง ที่ด้านข้างของแต่ละรูปแบบ';

$lang['Select_template'] = 'เลือก template';

$lang['Style'] = 'รูปแบบ';
$lang['Template'] = 'Template';
$lang['Install'] = 'ติดตั้ง';
$lang['Download'] = 'Download';

$lang['Edit_theme'] = 'แก้ไข theme';
$lang['Edit_theme_explain'] = 'ด้านล่างนี้คุณสามารถแก้ไขการตั้งค่าต่างๆ ของรูปแบบที่ถูกเลือก';

$lang['Create_theme'] = 'สร้าง theme ใหม่';
$lang['Create_theme_explain'] = 'ด้านล่างนี้คุณสามารถสร้างรูปแบบใหม่จากรูปแบบที่ถูกเลือก. การระบุค่าสี (ซึ่งคุณควรใช้เป็นเลขฐาน 6) คุณต้องไม่ใส่เครื่องหมาย # เช่น.. CCCCCC นั้นถูกต้อง, แต่ #CCCCCC นั้นไม่ถูกต้อง';

$lang['Export_themes'] = 'ส่งออก themes';
$lang['Export_explain'] = 'คุณสามารถส่งออกข้อมูลรูปแบบ ของรูปแบบที่ถูกเลือก. ให้เลือกรูปแบบจากรายการด้านล่างนี้ แล้วระบบจะสร้างไฟล์การตั้งค่าและรูปแบบ สำหรับบันทึกเก็บไว้ใน directory ของรูปแบบ. ถ้าโปรแกรมไม่สามารถบันทึกด้วยตัวเอง โปรแกรมจะให้คุณทำการ download เอง. ซึ่งถ้าคุณต้องการให้โปรแกรมบันทึกไฟล์ให้คุณ คุณต้องกำหนดสิทธิ์การเขียนบน webserver ให้กับ directory ของรูปแบบที่คุณเลือก. ดูข้อมูลเพิ่มเติมใน phpBB 2 users guide.';

$lang['Theme_installed'] = 'ติดตั้ง theme ที่เลือกแล้ว';
$lang['Style_removed'] = 'ลบรูปแบบที่เลือกออกจากฐานข้อมูลแล้ว. ถ้าจะลบอย่างสมบูรณ์ คุณจะต้องลบไฟล์รูปแบบทั้งหมด ออกจาก template directory ด้วย.';
$lang['Theme_info_saved'] = 'บันทึกรายละเอียดของ theme แล้ว. คุณควรกลับไปเปลี่ยนค่าการอนุญาตของไฟล์ theme_info.cfg (และ template directory ที่คุณเลือก) ให้เป็น read-only';
$lang['Theme_updated'] = 'ปรับปรุง theme ที่ถูกเลือกแล้ว. คุณควรส่งออกการตั้งค่า theme ใหม่';
$lang['Theme_created'] = 'สร้าง theme ใหม่แล้ว. คุณควรส่งออกการตั้งค่า theme ใหม่เพื่อความปลอดภัย หรือเก็บไว้ใช้ที่อื่น';

$lang['Confirm_delete_style'] = 'คุณต้องการลบรูปแบบนี้หรือไม่?';

$lang['Download_theme_cfg'] = 'ตัวส่งออกไม่สามารถสร้างไฟล์รายละเอียดของ theme. คลิกปุ่มด้านล่างเพื่อ download ไฟล์ด้วย browser. เมื่อคุณ download เสร็จแล้ว คุณสามารถเก็บไว้ใน directory สำหรับเก็บไฟล์ template. คุณสามารถส่งต่อให้ผู้อื่นหรือเก็บไว้ใช้ตามต้องการ';
$lang['No_themes'] = 'ไม่มี theme อยู่ใน template ที่คุณเลือก. โปรดคลิกปุ่ม สร้างใหม่ บนแถบด้านซ้าย';
$lang['No_template_dir'] = 'ไม่สามารถเปิด template directory. อาจจะถูกสั่งไม่ให้อ่านโดย webserver หรืออาจไม่มีอยู่จริง';
$lang['Cannot_remove_style'] = 'คุณไม่สามารถลบรูปแบบที่เลือก เพราะมันถูกใช้เป็นรูปแบบมาตรฐาน. กรุณาเปลี่ยนรูปแบบมาตรฐานแล้วลองใหม่.';
$lang['Style_exists'] = 'มีชื่อของรูปแบบนี้อยู่แล้ว กรุณากลับไปเปลี่ยนชื่อใหม่.';

$lang['Click_return_styleadmin'] = 'คลิก %sที่นี่%s เพื่อกลับไปหน้า Style Administration';

$lang['Theme_settings'] = 'ตั้งค่า theme';
$lang['Theme_element'] = 'ส่วนประกอบของ theme';
$lang['Simple_name'] = 'ชื่อพื้นฐาน';
$lang['Value'] = 'ค่า';
$lang['Save_Settings'] = 'บันทึกค่า';

$lang['Stylesheet'] = 'CSS Stylesheet';
$lang['Background_image'] = 'รูปพื้นหลัง';
$lang['Background_color'] = 'สีพื้นหลัง';
$lang['Theme_name'] = 'ชื่อ theme';
$lang['Link_color'] = 'สีลิงค์';
$lang['Text_color'] = 'สีตัวอักษร';
$lang['VLink_color'] = 'สีลิงค์ที่เคยไปมาแล้ว';
$lang['ALink_color'] = 'สีลิงค์ที่กำลังอ่าน';
$lang['HLink_color'] = 'สีลิงค์ขณะเมาส์วางทับ';
$lang['Tr_color1'] = 'สีตารางที่ 1';
$lang['Tr_color2'] = 'สีตารางที่ 2';
$lang['Tr_color3'] = 'สีตารางที่ 3';
$lang['Tr_class1'] = 'Class แถวตารางที่ 1';
$lang['Tr_class2'] = 'Class แถวตารางที่ 2';
$lang['Tr_class3'] = 'Class แถวตารางที่ 3';
$lang['Th_color1'] = 'สีของหัวตารางที่ 1';
$lang['Th_color2'] = 'สีของหัวตารางที่ 2';
$lang['Th_color3'] = 'สีของหัวตารางที่ 3';
$lang['Th_class1'] = 'Class หัวตารางที่ 1';
$lang['Th_class2'] = 'Class หัวตารางที่ 2';
$lang['Th_class3'] = 'Class หัวตารางที่ 3';
$lang['Td_color1'] = 'สีของเซลล์ตารางที่ 1';
$lang['Td_color2'] = 'สีของเซลล์ตารางที่ 2';
$lang['Td_color3'] = 'สีของเซลล์ตารางที่ 3';
$lang['Td_class1'] = 'สีของเซลล์ตารางที่ 1';
$lang['Td_class2'] = 'สีของเซลล์ตารางที่ 2';
$lang['Td_class3'] = 'สีของเซลล์ตารางที่ 3';
$lang['fontface1'] = 'แบบตัวอักษรที่ 1';
$lang['fontface2'] = 'แบบตัวอักษรที่ 2';
$lang['fontface3'] = 'แบบตัวอักษรที่ 3';
$lang['fontsize1'] = 'ขนาดตัวอักษรที่ 1';
$lang['fontsize2'] = 'ขนาดตัวอักษรที่ 2';
$lang['fontsize3'] = 'ขนาดตัวอักษรที่ 3';
$lang['fontcolor1'] = 'สีตัวอักษรที่ 1';
$lang['fontcolor2'] = 'สีตัวอักษรที่ 2';
$lang['fontcolor3'] = 'สีตัวอักษรที่ 3';
$lang['span_class1'] = 'Span Class 1';
$lang['span_class2'] = 'Span Class 2';
$lang['span_class3'] = 'Span Class 3';
$lang['img_poll_size'] = 'ขนาดรูปภาพแบบสำรวจ [px]';
$lang['img_pm_size'] = 'ขนาดสถานะข้อความส่วนตัว [px]';


//
// Install Process
//
$lang['Welcome_install'] = 'ยินดีต้อนรับสู่การติดตั้ง phpBB 2';
$lang['Initial_config'] = 'การตั้งค่าพื้นฐาน';
$lang['DB_config'] = 'การตั้งค่าฐานข้อมูล';
$lang['Admin_config'] = 'การตั้งค่า Admin';
$lang['continue_upgrade'] = 'เมื่อคุณ download ไฟล์การตั้งค่าลงเครื่องของคุณ ให้คลิกปุ่ม ทำการอัปเกรดต่อไป ที่ด้านล่างนี้ เพื่อไปยังกระบวนการอัปเกรด. กรุณารอขณะทำการส่งไฟล์การตั้งค่า จนกว่ากระบวนการอัปเกรดจะเสร็จสิ้น.';
$lang['upgrade_submit'] = 'ทำการอัปเกรดต่อไป';

$lang['Installer_Error'] = 'เกิดข้อผิดพลาดขึ้นระหว่างการติดตั้ง';
$lang['Previous_Install'] = 'ตรวจพบว่าเคยมีการติดตั้งมาก่อน';
$lang['Install_db_error'] = 'เกิดข้อผิดพลาดขึ้นในการพยายามปรับปรุงฐานข้อมูล';

$lang['Re_install'] = 'การติดตั้งครั้งก่อนยังไม่เสร็จ. <br /><br />ถ้าคุณต้องการติดตั้ง phpBB 2 ใหม่ คุณควรคลิกปุ่ม Yes ด้านล่างนี้. โปรดระวังว่าการทำเช่นนี้ จะทำลายข้อมูลที่มีอยู่ทั้งหมด ไม่มีการสำรองข้อมูลใดๆทั้งสิ้น! ชื่อและรหัสผ่านของ administrator ที่คุณเคยใช้จะถูกสร้างใหม่ จะไม่มีการตั้งค่าอื่นๆหลงเหลืออยู่อีก<br /><br />กรุณาคิดให้รอบคอบก่อนคลิก Yes!';

$lang['Inst_Step_0'] = 'ขอบคุณที่ใช้ phpBB 2. เพื่อให้การติดตั้งสมบูรณ์ กรุณากรอกรายละเอียดด้านล่างนี้. กรุณาตรวจสอบก่อนว่าชื่อฐานข้อมูลที่คุณจะใช้นั้นได้ถูกสร้างไว้ก่อนแล้ว. ถ้าคุณกำลังติดตั้งลงฐานข้อมูล ODBC เช่น MS Access คุณควรจะสร้าง DSN เสียก่อน';

$lang['Start_Install'] = 'เริ่มติดตั้ง';
$lang['Finish_Install'] = 'การติดตั้งเสร็จแล้ว';

$lang['Default_lang'] = 'ภาษามาตรฐานของบอร์ด';
$lang['DB_Host'] = 'Database Server Hostname / DSN';
$lang['DB_Name'] = 'ชื่อฐานข้อมูลของคุณ';
$lang['DB_Username'] = 'Database Username';
$lang['DB_Password'] = 'Database Password';
$lang['Database'] = 'ฐานข้อมูลของคุณ';
$lang['Install_lang'] = 'เลือกภาษาเพื่อใช้ในการติดตั้ง';
$lang['dbms'] = 'ชนิดฐานข้อมูล';
$lang['Table_Prefix'] = 'ขึ้นต้นชื่อตารางในฐานข้อมูลด้วยคำว่า';
$lang['Admin_Username'] = 'Administrator Username';
$lang['Admin_Password'] = 'Administrator Password';
$lang['Admin_Password_confirm'] = 'Administrator Password [ยืนยัน]';

$lang['Inst_Step_2'] = 'สร้างบัญชี admin ของคุณแล้ว. ขณะนี้การติดตั้งพื้นฐานได้เสร็จสมบูรณ์. คุณจะถูกพาไปยังหน้าสำหรับการตั้งค่าทั่วไป. กรุณาตรวจสอบรายละเอียดของ การตั้งค่าทั่วไป แล้วแก้ไขในส่วนที่ต้องการ. ขอบคุณที่ใช้ phpBB 2.';

$lang['Unwriteable_config'] = 'ไฟล์การตั้งค่าของคุณ ถูกกำหนดสิทธิ์ให้ไม่สามารถแก้ไขได้. เมื่อคุณคลิกปุ่มด้านล่างนี้ ไฟล์การตั้งค่าจะถูกส่งมาให้คุณ download. คุณควร upload ไฟล์นี้ไปเก็บไว้ใน directory เดียวกันกับ phpBB 2. เสร็จแล้วให้เข้าสู่ระบบด้วยบัญชีของ administrator ที่กรอกไว้ จากนั้นให้ไปที่ Admin Control Panel (เป็นลิงค์ที่ด้านล่างของแต่ละหน้า เมื่อเข้าสู่ระบบแล้ว) เพื่อตรวจสอบการตั้งค่าทั่วไป. ขอบคุณที่ใช้ phpBB 2.';
$lang['Download_config'] = 'Download ไฟล์การตั้งค่า';

$lang['ftp_choose'] = 'เลือกวิธีการ Download';
$lang['ftp_option'] = '<br />มีการติดตั้งระบบ FTP ลงในภาษา PHP รุ่นนี้ คุณอาจใช้ระบบ ftp เพื่อส่งไฟล์ไปเก็บไว้โดยอัตโนมัติ';
$lang['ftp_instructs'] = 'คุณได้เลือกใช้ระบบ ftp เพื่อส่งไฟล์โดยอัตโนมัติ. กรุณากรอกข้อมูลด้านล่างนี้. ตำแหน่ง FTP ควรจะตรงกับตำแหน่ง ftp ที่ทำการติดตั้ง phpBB2 เหมือนอย่างที่คุณใช้โปรแกรม ftp ทั่วๆไป';
$lang['ftp_info'] = 'กรุณากรอกข้อมูล FTP ของคุณ';
$lang['Attempt_ftp'] = 'พยายามตั้งค่าการ ftp เพื่อส่งไฟล์ไปเก็บไว้';
$lang['Send_file'] = 'แค่ส่งไฟล์มาให้ฉัน แล้วฉันจะใช้โปรแกรม ftp อื่นๆ ส่งไปด้วยตัวเอง';
$lang['ftp_path'] = 'ตำแหน่ง FTP ของ phpBB 2';
$lang['ftp_username'] = 'FTP Username';
$lang['ftp_password'] = 'FTP Password';
$lang['Transfer_config'] = 'เริ่มส่งไฟล์';
$lang['NoFTP_config'] = 'การพยายามส่งไฟล์การตั้งค่าทาง ftp ล้มเหลว. กรุณา download ไฟล์การตั้งค่า แล้วส่งมาทาง ftp ด้วยตัวคุณเอง';

$lang['Install'] = 'ติดตั้ง';
$lang['Upgrade'] = 'อัปเกรด';


$lang['Install_Method'] = 'กรุณาเลือกวิธีการติดตั้ง';

$lang['Install_No_Ext'] = 'การตั้งค่าในภาษา php บน server ของคุณ ไม่สนับสนุนฐานข้อมูลที่คุณเลือก';

$lang['Install_No_PCRE'] = 'phpBB2 ต้องการ Perl-Compatible Regular Expressions Module สำหรับภาษา php. ซึ่งปรากฏว่าการตั้งค่าในภาษา php ของคุณนั้นไม่สนับสนุน!';

//
// That's all Folks!
// -------------------------------------------------

?>