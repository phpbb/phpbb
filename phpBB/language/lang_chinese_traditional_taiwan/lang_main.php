<?php
/***************************************************************************
 *                            lang_main.php [English]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_main.php,v 1.82 2002/02/03 18:17:08 thefinn Exp $
 *
 ****************************************************************************/

/***************************************************************************
 *                            Traditional Chinese[繁體中文語系] Translation
 *                              -------------------
 *     begin                : Thu Nov 26 2001
 *     by                   : 小竹子, OOHOO, 皇家騎士, 思
 *     email                : kyo.yoshika@msa.hinet.net
 *                            mchiang@bigpond.net.au
 *                            sjwu1@ms12.hinet.net
 *                            f8806077@mail.dyu.edu.tw
 *
 *     last modify          : Wed Feb 27 2002
 *
 ***************************************************************************/

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

// Translator credit
$lang['TRANSLATION_INFO'] = '繁體中文化由&nbsp;<a href="http://heaven.wusdsl.net/phpbb/viewtopic.php?p=2811#2811"><font color="#FF6633">竹貓星球PBB2中文強化開發小組</font></a>&nbsp;製作';

//setlocale(LC_ALL, "en");
$lang['ENCODING'] = "big5";
$lang['DIRECTION'] = "LTR";
$lang['LEFT'] = "LEFT";
$lang['RIGHT'] = "RIGHT";
$lang['DATE_FORMAT'] = "Y-m-d"; // This should be changed to the default date format for your language, php date() format

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "版面";
$lang['Category'] = "分區";
$lang['Topic'] = "主題";
$lang['Topics'] = "主題";
$lang['Replies'] = "回覆";
$lang['Views'] = "觀看";
$lang['Post'] = "發表";
$lang['Posts'] = "文章";
$lang['Posted'] = "發表於";
$lang['Username'] = "會員名稱";
$lang['Password'] = "登入密碼";
$lang['Email'] = "電子郵件";
$lang['Poster'] = "回覆人";
$lang['Author'] = "發表人";
$lang['Time'] = "時間";
$lang['Hours'] = "小時內";
$lang['Message'] = "內容";

$lang['1_Day'] = "1 天內";
$lang['7_Days'] = "7 天內";
$lang['2_Weeks'] = "2 星期內";
$lang['1_Month'] = "1 個月內";
$lang['3_Months'] = "3 個月內";
$lang['6_Months'] = "6 個月內";
$lang['1_Year'] = "1 年內";

$lang['Go'] = "Go";
$lang['Jump_to'] = "前往";
$lang['Submit'] = "送出";
$lang['Reset'] = "重設";
$lang['Cancel'] = "清除";
$lang['Preview'] = "預覽";
$lang['Confirm'] = "確認";
$lang['Spellcheck'] = "拼音檢查";
$lang['Yes'] = "是";
$lang['No'] = "否";
$lang['Enabled'] = "開啟";
$lang['Disabled'] = "關閉";
$lang['Error'] = "錯誤";

$lang['Next'] = "下一頁";
$lang['Previous'] = "上一頁";
$lang['Goto_page'] = "前往頁面";
$lang['Joined'] = "註冊時間";
$lang['IP_Address'] = "IP 位址";

$lang['Select_forum'] = "選擇一個版面";
$lang['View_latest_post'] = "檢視最後發表的文章";
$lang['View_newest_post'] = "檢視最新發表的文章";
$lang['Page_of'] = "第<b>%d</b>頁(共<b>%d</b>頁)"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "ICQ 號碼";
$lang['AIM'] = "AIM Address";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "雅虎訊息通";

$lang['Forum_Index'] = "%s 首頁";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "發表新主題";
$lang['Reply_to_topic'] = "回覆主題";
$lang['Reply_with_quote'] = "引言回覆";

$lang['Click_return_topic'] = "點選 %s這裡%s 返回主題"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "點選 %s這裡%s 返回重試";
$lang['Click_return_forum'] = "點選 %s這裡%s 返回版面";
$lang['Click_view_message'] = "點選 %s這裡%s 檢視您的文章";
$lang['Click_return_modcp'] = "點選 %s這裡%s 返回版面管理控制台";
$lang['Click_return_group'] = "點選 %s這裡%s 返回群組資訊介紹";

$lang['Admin_panel'] = "進入系統管理控制台";

$lang['Board_disable'] = "系統目前暫時停止服務, 請稍後再試";


//
// Global Header strings
//
$lang['Registered_users'] = "目前線上註冊會員:";
$lang['Browsing_forum'] = "目前觀看人數:";
$lang['Online_users_zero_total'] = "目前沒有使用者在線上 :: ";
$lang['Online_users_total'] = "目前總共有 %d 位使用者在線上 :: ";
$lang['Online_user_total'] = "目前總共有 %d 位使用者在線上 :: ";
$lang['Reg_users_zero_total'] = "0 位會員, ";
$lang['Reg_users_total'] = "%d 位會員, ";
$lang['Reg_user_total'] = "%d 位會員, ";
$lang['Hidden_users_zero_total'] = "0 位隱形及 ";
$lang['Hidden_user_total'] = "%d 位隱形及 ";
$lang['Hidden_users_total'] = "%d 位隱形及 ";
$lang['Guest_users_zero_total'] = "0 位訪客";
$lang['Guest_users_total'] = "%d 位訪客";
$lang['Guest_user_total'] = "%d 位訪客";
$lang['Record_online_users'] = "最高線上人數記錄 <b>%s</b> 在 %s 創下"; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = "%s系統管理員%s";
$lang['Mod_online_color'] = "%s版面管理員%s";

$lang['You_last_visit'] = "您最後訪問於 %s"; // %s replaced by date/time
$lang['Current_time'] = "現在的時間是 %s"; // %s replaced by time

$lang['Search_new'] = "檢視新發表的文章";
$lang['Search_your_posts'] = "檢視您發表的文章";
$lang['Search_unanswered'] = "檢視未回覆的主題";

$lang['Register'] = "會員註冊";
$lang['Profile'] = "個人資料";
$lang['Edit_profile'] = "編輯您的個人資料";
$lang['Search'] = "搜尋";
$lang['Memberlist'] = "會員列表";
$lang['FAQ'] = "常見問題";
$lang['BBCode_guide'] = "BBCode 代碼說明";
$lang['Usergroups'] = "會員群組";
$lang['Last_Post'] = "最後發表";
$lang['Moderator'] = "版面管理員";
$lang['Moderators'] = "版面管理員";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "目前沒有發表的文章"; // Number of posts
$lang['Posted_articles_total'] = "目前總共發表了 <b>%d</b> 篇文章"; // Number of posts
$lang['Posted_article_total'] = "目前總共發表了 <b>%d</b> 篇文章"; // Number of posts
$lang['Registered_users_zero_total'] = "目前沒有註冊會員"; // # registered users
$lang['Registered_users_total'] = "目前總共有 <b>%d</b> 位註冊會員"; // # registered users
$lang['Registered_user_total'] = "目前有 <b>%d</b> 位註冊會員"; // # registered users
$lang['Newest_user'] = "最新註冊的會員: <b>%s%s%s</b>"; // a href, username, /a

$lang['No_new_posts_last_visit'] = "從您上次光臨後沒有新文章";
$lang['No_new_posts'] = "沒有新文章";
$lang['New_posts'] = "新文章";
$lang['New_post'] = "新文章";
$lang['No_new_posts_hot'] = "沒有新文章 [ 熱門 ]";
$lang['New_posts_hot'] = "新文章 [ 熱門 ]";
$lang['No_new_posts_locked'] = "沒有新文章 [ 鎖定 ]";
$lang['New_posts_locked'] = "新文章 [ 鎖定 ]";
$lang['Forum_is_locked'] = "版面已被鎖定";


//
// Login
//
$lang['Enter_password'] = "請輸入您的登入名稱及密碼";
$lang['Login'] = "登入";
$lang['Logout'] = "登出";

$lang['Forgotten_password'] = "忘記密碼";

$lang['Log_me_in'] = "自動登入";

$lang['Error_login'] = "您輸入了無效的登入名稱或錯誤的密碼";


//
// Index page
//
$lang['Index'] = "首頁";
$lang['No_Posts'] = "沒有文章";
$lang['No_forums'] = "這個討論區沒有分區版面";

$lang['Private_Message'] = "私人訊息";
$lang['Private_Messages'] = "私人訊息";
$lang['Who_is_Online'] = "查看誰在線上";

$lang['Mark_all_forums'] = "將所有版面標示為已閱讀";
$lang['Forums_marked_read'] = "所有版面已被標示為已閱讀";


//
// Viewforum
//
$lang['View_forum'] = "檢視版面";

$lang['Forum_not_exist'] = "您選擇的版面不存在";
$lang['Reached_on_error'] = "頁面可能已被移除或不存在";

$lang['Display_topics'] = "文章排序時間";
$lang['All_Topics'] = "所有主題";

$lang['Topic_Announcement'] = "<b>公告:</b>";
$lang['Topic_Sticky'] = "<b>置頂:</b>";
$lang['Topic_Moved'] = "<b>移動:</b>";
$lang['Topic_Poll'] = "<b>[ 票選 ]</b>";

$lang['Mark_all_topics'] = "將所有主題標示為已閱讀";
$lang['Topics_marked_read'] = "這個版面的主題已被標示為已閱讀";

$lang['Rules_post_can'] = "您 <b>可以</b> 在這個版面發表文章";
$lang['Rules_post_cannot'] = "您 <b>無法</b> 在這個版面發表文章";
$lang['Rules_reply_can'] = "您 <b>可以</b> 在這個版面回覆文章";
$lang['Rules_reply_cannot'] = "您 <b>無法</b> 在這個版面回覆文章";
$lang['Rules_edit_can'] = "您 <b>可以</b> 在這個版面編輯文章";
$lang['Rules_edit_cannot'] = "您 <b>無法</b> 在這個版面編輯文章";
$lang['Rules_delete_can'] = "您 <b>可以</b> 在這個版面刪除文章";
$lang['Rules_delete_cannot'] = "您 <b>無法</b> 在這個版面刪除文章";
$lang['Rules_vote_can'] = "您 <b>可以</b> 在這個版面進行投票";
$lang['Rules_vote_cannot'] = "您 <b>無法</b> 在這個版面進行投票";
$lang['Rules_moderate'] = "您 <b>可以</b> %s執行版面管理功能%s"; // %s replaced by a href links, do not remove!

$lang['No_topics_post_one'] = "這個版面目前沒有文章<br />請按下<b>發表新主題</b>的按鈕發表新的文章主題";


//
// Viewtopic
//
$lang['View_topic'] = "觀看文章";

$lang['Guest'] = 'Guest';
$lang['Post_subject'] = "文章標題";
$lang['View_next_topic'] = "下一篇文章";
$lang['View_previous_topic'] = "上一篇文章";
$lang['Submit_vote'] = "送出投票";
$lang['View_results'] = "觀看目前投票結果";

$lang['No_newer_topics'] = "這個版面沒有新的主題";
$lang['No_older_topics'] = "這個版面沒有舊的主題";
$lang['Topic_post_not_exist'] = "您所查看的主題或文章不存在";
$lang['No_posts_topic'] = "這個主題沒有回覆文章";

$lang['Display_posts'] = "從之前的文章開始顯示";
$lang['All_Posts'] = "所有文章";
$lang['Newest_First'] = "最新的";
$lang['Oldest_First'] = "最舊的";

$lang['Back_to_top'] = "回頂端";

$lang['Read_profile'] = "檢視會員個人資料";
$lang['Send_email'] = "發送電子郵件";
$lang['Visit_website'] = "參觀發表人的個人網站";
$lang['ICQ_status'] = "ICQ 狀態";
$lang['Edit_delete_post'] = "編輯/刪除這篇文章";
$lang['View_IP'] = "檢視發表人的 IP 位址";
$lang['Delete_post'] = "刪除文章";

$lang['wrote'] = "寫到"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "引言回覆"; // comes before bbcode quote output.
$lang['Code'] = "代碼"; // comes before bbcode code output.

$lang['Edited_time_total'] = " %s 在  %s 作了最後編輯, 共編輯過 %d 次"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "%s 在  %s 作了最後編輯, 共編輯過 %d 次"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "鎖定主題";
$lang['Unlock_topic'] = "解除鎖定";
$lang['Move_topic'] = "移動主題";
$lang['Delete_topic'] = "刪除主題";
$lang['Split_topic'] = "分割主題";

$lang['Stop_watching_topic'] = "取消訂閱這個主題 (回覆通知)";
$lang['Start_watching_topic'] = "訂閱這個主題 (回覆通知)";
$lang['No_longer_watching'] = "您已經取消訂閱這個主題 (回覆通知)";
$lang['You_are_watching'] = "您已經訂閱了這個主題 (回覆通知)";

$lang['Total_votes'] = "總投票數";

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "文章內容";
$lang['Topic_review'] = "檢視主題";

$lang['No_post_mode'] = "沒有指定的發表模式"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = "發表新主題";
$lang['Post_a_reply'] = "發表回覆";
$lang['Post_topic_as'] = "發表主題為";
$lang['Edit_Post'] = "編輯文章";
$lang['Options'] = "選項";

$lang['Post_Announcement'] = "公告";
$lang['Post_Sticky'] = "置頂";
$lang['Post_Normal'] = "正常";

$lang['Confirm_delete'] = "您確定要刪除這篇文章嗎?";
$lang['Confirm_delete_poll'] = "您確定要刪除這個票選活動嗎?";

$lang['Flood_Error'] = "嚴禁惡意的快速發文攻擊, 請稍後再試";
$lang['Empty_subject'] = "發表新主題必須要有文章標題";
$lang['Empty_message'] = "發表文章必須要有文章內容";
$lang['Forum_locked'] = "這個版面已經被鎖定了, 您無法在這個版面發表, 回覆或是編輯主題";
$lang['Topic_locked'] = "這個主題已經被鎖定了, 您無法在這個主題編輯文章或是回覆";
$lang['No_post_id'] = "沒有指定對象";
$lang['No_topic_id'] = "您必須選擇要回覆的主題";
$lang['No_valid_mode'] = "您只能發表, 回覆編輯或是引言回覆訊息, 請返回重試";
$lang['No_such_post'] = "沒有符合的文章, 請返回重試";
$lang['Edit_own_posts'] = "很抱歉! 您沒有權力編輯其他會員的文章";
$lang['Delete_own_posts'] = "很抱歉! 您沒有權力刪除其他會員的文章";
$lang['Cannot_delete_replied'] = "很抱歉! 您不能刪除已有回覆文章的主題";
$lang['Cannot_delete_poll'] = "很抱歉! 您無法刪除進行中的票選活動";
$lang['Empty_poll_title'] = "您必須輸入票選的主題";
$lang['To_few_poll_options'] = "您至少需要輸入兩個票選的項目";
$lang['To_many_poll_options'] = "您的票選項目太多了";
$lang['Post_has_no_poll'] = "這篇文章沒有票選活動";

$lang['Add_poll'] = "票選活動";
$lang['Add_poll_explain'] = "如果您不想設置票選功能, 請將此處留白";
$lang['Poll_question'] = "票選主題";
$lang['Poll_option'] = "票選項目";
$lang['Add_option'] = "新增項目";
$lang['Update'] = "更新";
$lang['Delete'] = "刪除";
$lang['Poll_for'] = "票選期限";
$lang['Days'] = "天"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ 輸入 0 或是空白為沒有限期的票選活動 ]";
$lang['Delete_poll'] = "刪除票選活動";

$lang['Disable_HTML_post'] = "關閉這篇文章的 HTML 語法功能";
$lang['Disable_BBCode_post'] = "關閉這篇文章的 BBCode 代碼功能";
$lang['Disable_Smilies_post'] = "關閉這篇文章的表情符號功能";

$lang['HTML_is_ON'] = "HTML 語法 <u>開啟</u>";
$lang['HTML_is_OFF'] = "HTML 語法 <u>關閉</u>";
$lang['BBCode_is_ON'] = "%sBBCode 代碼%s <u>開啟</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode 代碼%s <u>關閉</u>";
$lang['Smilies_are_ON'] = "表情符號 <u>開啟</u>";
$lang['Smilies_are_OFF'] = "表情符號 <u>關閉</u>";

$lang['Attach_signature'] = "附上簽名 (簽名檔可以在個人資料裡面更改)";
$lang['Notify'] = "當有人回覆文章時通知我";
$lang['Delete_post'] = "刪除文章";

$lang['Stored'] = "您的訊息已經成功發送";
$lang['Deleted'] = "您的訊息已經成功刪除";
$lang['Poll_delete'] = "您的票選活動已經成功刪除";
$lang['Vote_cast'] = "感謝您參與投票";

$lang['Topic_reply_notification'] = "主題回覆通知";

$lang['bbcode_b_help'] = "粗體: [b]text[/b]  (alt+b)";
$lang['bbcode_i_help'] = "斜體: [i]text[/i]  (alt+i)";
$lang['bbcode_u_help'] = "底線: [u]text[/u]  (alt+u)";
$lang['bbcode_q_help'] = "引言回覆: [quote]text[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "顯示程式代碼: [code]code[/code]  (alt+c)";
$lang['bbcode_l_help'] = "列表: [list]text[/list] (alt+l)";
$lang['bbcode_o_help'] = "依序排列: [list=]text[/list]  (alt+o)";
$lang['bbcode_p_help'] = "插入圖片: [img]http://image_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "插入 URL: [url]http://url[/url] or [url=http://url]URL text[/url]  (alt+w)";
$lang['bbcode_a_help'] = "關閉所有開啟的 BBCode 標籤";
$lang['bbcode_s_help'] = "字型顏色: [color=red]text[/color]  您也可以使用顏色編碼, 例如: #FF0000";
$lang['bbcode_f_help'] = "字型大小: [size=x-small]small text[/size]";

$lang['Emoticons'] = "表情符號";
$lang['More_emoticons'] = "更多表情符號";

$lang['Font_color'] = "字型顏色";
$lang['color_default'] = "預設值";
$lang['color_dark_red'] = "深紅色";
$lang['color_red'] = "紅色";
$lang['color_orange'] = "橘色";
$lang['color_brown'] = "棕色";
$lang['color_yellow'] = "黃色";
$lang['color_green'] = "綠色";
$lang['color_olive'] = "橄欖色";
$lang['color_cyan'] = "青綠色";
$lang['color_blue'] = "藍色";
$lang['color_dark_blue'] = "深藍色";
$lang['color_indigo'] = "靛色";
$lang['color_violet'] = "紫色";
$lang['color_white'] = "白色";
$lang['color_black'] = "黑色";

$lang['Font_size'] = "字型大小";
$lang['font_tiny'] = "極小";
$lang['font_small'] = "小";
$lang['font_normal'] = "正常";
$lang['font_large'] = "大";
$lang['font_huge'] = "巨大";

$lang['Close_Tags'] = "關閉標籤";
$lang['Styles_tip'] = "提示: 格式可以快速套用在選擇的文字上";


//
// Private Messaging
//
$lang['Private_Messaging'] = "私人訊息";

$lang['Login_check_pm'] = "登入檢查您的私人訊息";
$lang['New_pms'] = "您有 <b>%d</b> 個新的私人訊息"; // You have 2 new messages
$lang['New_pm'] = "您有 <b>%d</b> 個新的私人訊息"; // You have 1 new message
$lang['No_new_pm'] = "您沒有新的私人訊息";
$lang['Unread_pms'] = "您有 <b>%d</b> 個未讀的私人訊息";
$lang['Unread_pm'] = "您有 <b>%d</b> 個未讀的私人訊息";
$lang['No_unread_pm'] = "您的私人訊息都看過了";
$lang['You_new_pm'] = "您收到了新的私人訊息";
$lang['You_new_pms'] = "有新的私人訊息在您的收件夾";
$lang['You_no_new_pm'] = "您沒有新的私人訊息";

$lang['Inbox'] = "收件夾";
$lang['Outbox'] = "寄件夾";
$lang['Savebox'] = "儲存夾";
$lang['Sentbox'] = "寄件備份";
$lang['Flag'] = "狀態";
$lang['Subject'] = "主題";
$lang['From'] = "來自";
$lang['To'] = "收件人";
$lang['Date'] = "日期";
$lang['Mark'] = "選取";
$lang['Sent'] = "發送";
$lang['Saved'] = "儲存";
$lang['Delete_marked'] = "刪除選取";
$lang['Delete_all'] = "刪除全部";
$lang['Save_marked'] = "儲存選取";
$lang['Save_message'] = "儲存訊息";
$lang['Delete_message'] = "刪除訊息";

$lang['Display_messages'] = "顯示之前的私人訊息"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "所有訊息";

$lang['No_messages_folder'] = "這個檔案夾沒有私人訊息";

$lang['PM_disabled'] = "討論區的私人訊息功能已經被關閉";
$lang['Cannot_send_privmsg'] = "很抱歉! 但是系統管理員禁止您發送私人訊息給別人";
$lang['No_to_user'] = "您必須選擇發送對象才能送出私人訊息";
$lang['No_such_user'] = "很抱歉! 但是沒有這個人";

$lang['Disable_HTML_pm'] = "關閉這篇訊息的 HTML 語法功能";
$lang['Disable_BBCode_pm'] = "關閉這篇訊息的 BBCode 代碼功能";
$lang['Disable_Smilies_pm'] = "關閉這篇訊息的表情符號功能";

$lang['Message_sent'] = "您的私人訊息已經送出";

$lang['Click_return_inbox'] = "點選 %s這裡%s 返回收件夾";
$lang['Click_return_index'] = "點選 %s這裡%s 返回首頁";

$lang['Send_a_new_message'] = "發送新的私人訊息";
$lang['Send_a_reply'] = "回覆私人訊息";
$lang['Edit_message'] = "編輯私人訊息";

$lang['Notification_subject'] = "您有新的私人訊息";

$lang['Find_username'] = "尋找會員名稱";
$lang['Find'] = "尋找";
$lang['No_match'] = "沒有搜尋到符合的";

$lang['No_post_id'] = "沒有指定對象";
$lang['No_such_folder'] = "沒有符合的文件夾";
$lang['No_folder'] = "沒有指定文件夾";

$lang['Mark_all'] = "選擇全部";
$lang['Unmark_all'] = "取消全選";

$lang['Confirm_delete_pm'] = "您確定要刪除這篇私人訊息嗎?";
$lang['Confirm_delete_pms'] = "您確定要刪除這些私人訊息嗎?";

$lang['Inbox_size'] = "您的收件夾已經使用了 %d%% "; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "您的寄件夾已經使用了 %d%% ";
$lang['Savebox_size'] = "您的儲存夾已經使用了 %d%% ";

$lang['Click_view_privmsg'] = "點選 %s這裡%s 進入私人訊息收件夾";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "檢視 :: %s 的個人資料"; // %s is username
$lang['About_user'] = "關於 %s"; // %s is username

$lang['Preferences'] = "偏好設定";
$lang['Items_required'] = "標示有 * 的項目須確實填寫";
$lang['Registration_info'] = "會員註冊資料";
$lang['Profile_info'] = "個人資料";
$lang['Profile_info_warn'] = "這些資訊將對外公開";
$lang['Avatar_panel'] = "頭像控制面板";
$lang['Avatar_gallery'] = "系統相簿";

$lang['Website'] = "個人網站";
$lang['Location'] = "來自";
$lang['Contact'] = "聯絡";
$lang['Email_address'] = "電子郵件信箱";
$lang['Email'] = "電子郵件";
$lang['Send_private_message'] = "發送私人訊息";
$lang['Hidden_email'] = "[ 隱形 ]";
$lang['Search_user_posts'] = "查詢 %s 發表的所有文章";
$lang['Interests'] = "興趣";
$lang['Occupation'] = "職業";
$lang['Poster_rank'] = "等級";

$lang['Total_posts'] = "總發表數";
$lang['User_post_pct_stats'] = "討論區文章總數的 %d%% "; // 1.25% of total
$lang['User_post_day_stats'] = "平均每天發表 %.2f "; // 1.5 posts per day
$lang['Search_user_posts'] = "查詢 %s 發表的所有文章"; // Find all posts by username

$lang['No_user_id_specified'] = "您所選擇的會員名稱不存在";
$lang['Wrong_Profile'] = "您沒有權力修改別人的個人資料.";

$lang['Only_one_avatar'] = "只能指定一個頭像";
$lang['File_no_data'] = "您所提供的 URL 並沒有資料";
$lang['No_connection_URL'] = "您所提供的 URL 無法連結";
$lang['Incomplete_URL'] = "您所提供的 URL 不完全";
$lang['Wrong_remote_avatar_format'] = "從這個 URL 所連接的個人圖檔是無效的檔案格式";
$lang['No_send_account_inactive'] = "很抱歉!! 由於您的帳號目前處於停用狀態, 因此您無法取得新的密碼. 請跟系統管理員聯絡詢問相關資訊.";

$lang['Always_smile'] = "使用表情符號";
$lang['Always_html'] = "使用 HTML 語法";
$lang['Always_bbcode'] = "使用 BBCode 代碼";
$lang['Always_add_sig'] = "在文章內附加個性簽名";
$lang['Always_notify'] = "主題回覆通知";
$lang['Always_notify_explain'] = "當有人回覆您所發表的主題時, 系統會寄送電子郵件通知您. 這項設定也可以直接在您發表文章時變更";

$lang['Board_style'] = "版面風格";
$lang['Board_lang'] = "語系設定";
$lang['No_themes'] = "資料庫裡沒有佈景主題";
$lang['Timezone'] = "時區設定";
$lang['Date_format'] = "時間格式";
$lang['Date_format_explain'] = "排列語法使用 PHP <a href=\"http://www.php.net/date\" target=\"_other\">date()</a>  函數 ";
$lang['Signature'] = "個性簽名";
$lang['Signature_explain'] = "在文字區內的文字將附加在您發表的文章上, 以 %d 個字為限";
$lang['Public_view_email'] = "顯示電子郵件信箱";

$lang['Current_password'] = "目前密碼";
$lang['New_password'] = "輸入新密碼";
$lang['Confirm_password'] = "確認新密碼";
$lang['Confirm_password_explain'] = "如果您要變更電子郵件位址, 您必須輸入目前使用的密碼";
$lang['password_if_changed'] = "如果您想更換密碼的話, 請輸入您要替換的密碼";
$lang['password_confirm_if_changed'] = "請再輸入一次您要替換的密碼";

$lang['Avatar'] = "個人頭像";
$lang['Avatar_explain'] = "您的個人頭像將會顯示在您所發表的文章旁邊. 一次只能只用一個圖檔, 寬度不可超過  %d 像素, 高度不可超過  %d 像素而且檔案大小不可超過 %dkB";
$lang['Upload_Avatar_URL'] = "從連結複製圖檔";
$lang['Upload_Avatar_URL_explain'] = "輸入頭像連結, 系統將會把圖檔複製到系統裡面";
$lang['Pick_local_Avatar'] = "由相簿中選取圖檔";
$lang['Link_remote_Avatar'] = "由網址連結頭像圖檔";
$lang['Link_remote_Avatar_explain'] = "輸入頭像圖檔連結網址, 系統將會自動連結到您想要的網址";
$lang['Avatar_URL'] = "頭像圖檔的網址";
$lang['Select_from_gallery'] = "從系統相簿裡選擇圖檔";
$lang['View_avatar_gallery'] = "系統相簿";

$lang['Select_avatar'] = "選擇頭像";
$lang['Return_profile'] = "放棄選擇";
$lang['Select_category'] = "選擇種類";

$lang['Delete_Image'] = "刪除圖檔";
$lang['Current_Image'] = "目前使用的圖檔";

$lang['Notify_on_privmsg'] = "當有新的私人訊息時以電子郵件通知";
$lang['Popup_on_privmsg'] = "當有新的私人訊息時跳出小視窗通知";
$lang['Popup_on_privmsg_explain'] = "當有人發送私人訊息給您時會跳出一個小視窗通知";
$lang['Hide_user'] = "隱藏您的上線狀態";

$lang['Profile_updated'] = "您的個人資料已經完成更新";
$lang['Profile_updated_inactive'] = "您的個人資料已經完成更新, 然而您已修改過重要資料, 所以您的帳號已被暫停. 請先檢查您的電子郵件信箱, 找出如何重新開啟帳號, 若需要通過系統管理員審核, 請耐心等候";

$lang['Password_mismatch'] = "您輸入的密碼錯誤";
$lang['Current_password_mismatch'] = "您所提供的這個密碼與資料庫不符";
$lang['Password_long'] = "您所輸入的密碼長度超過 32 個字元";
$lang['Username_taken'] = "很抱歉!! 您所選擇的會員名稱已被註冊使用";
$lang['Username_invalid'] = "很抱歉!! 會員名稱內不得包含非法字元, 例如: ''";
$lang['Username_disallowed'] = "很抱歉!! 您所選擇的會員名稱已被封鎖";
$lang['Email_taken'] = "很抱歉!! 您所輸入的電子郵件位址已被註冊使用";
$lang['Email_banned'] = "很抱歉!! 您所輸入的電子郵件位址已被封鎖";
$lang['Email_invalid'] = "很抱歉!! 您輸入的不是合法的電子郵件位址";
$lang['Signature_too_long'] = "您的個性簽名太長";
$lang['Fields_empty'] = "您必須確實填寫標示有*的項目";
$lang['Avatar_filetype'] = "頭像圖檔格式必須為 .jpg, .gif 或是 .png";
$lang['Avatar_filesize'] = "頭像檔案大小必須大於 0 kB 並且小於"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "頭像圖檔尺寸必須小於  pixels(像素)寬 和  pixels(像素)高";

$lang['Welcome_subject'] = "歡迎來到  討論區"; // Welcome to my.com forums
$lang['New_account_subject'] = "新的會員帳號";
$lang['Account_activated_subject'] = "帳號已經啟用";

$lang['Account_added'] = "感謝您的註冊, 您的帳號已被建立. 您現在可以輸入會員帳號及密碼登入討論區";
$lang['Account_inactive'] = "您的帳號已被建立, 然而您需要完成帳號啟用程序後才能登入討論區. 系統已經將您的帳號啟用序號寄送到您的電子郵件信箱, 請檢查您的電子郵件信箱以取得相關的資訊";
$lang['Account_inactive_admin'] = "您的帳號已被建立, 然而這個討論區的會員資格必須獲得系統管理員的批准, 會員帳號才會被啟用. 系統已經把您的帳號申請進度相關資訊寄送到您的電子郵件信箱, 請隨時檢查您的電子郵件信箱注意申請進度";
$lang['Account_active'] = "您的帳號已經啟用, 非常感謝您的註冊!";
$lang['Account_active_admin'] = "這個帳號已經被啟用";
$lang['Reactivate'] = "您的帳號已經恢復啟用!";
$lang['COPPA'] = "您的帳號被建立, 但是需要管理員批准. 請檢查您的電子郵件信箱獲得詳細訊息.";

$lang['Registration'] = "會員註冊同意聲明";
$lang['Reg_agreement'] = "這個討論區的系統管理員和版面管理員會儘可能在第一時間內修改或移除任何有爭議性的文章, 然而管理人員不可能閱讀所有的文章, 因此討論區的文章內容不代表站方的言論或意見, 管理團隊不對網友所發表的文章內容負任何的責任.<br /><br />您必須同意不發表任何辱罵, 猥褻, 粗俗, 毀謗, 怨恨, 恐嚇以及有關性別歧視或任何有可能造成違法行為的相關文章, 如果您觸犯了以上的規定, 站方將會立即限制您的進入並且永不開放 (您的網路服務提供商也將會被發函通知). 所有文章發表人的 IP 位址都將被儲存以防止任何的違法情節發生.<br /><br />您必須同意站方, 系統管理員以及版面管理員擁有在任何時間刪除, 修改, 移動或關閉任何主題的權力. 作為一個使用者, 您必須同意您所提供的任何資訊都將被存入資料庫中, 這些資訊除了站方, 系統管理員及版面管理員之外不會對外公開, 但不保證任何可能導致資料暴露的駭客入侵行為.<br /><br />這個討論區系統使用cookie來儲存您的個人資訊, 這些cookie不包含任何您曾經輸入過的資訊, 它們只為方便您能更便捷的瀏覽. 而電子郵件位址只用來做為您同意以上條文後, 確認您的註冊資訊使用.";

$lang['Agree_under_13'] = "我同意以上條文(但是我<b>未滿13歲</b>)";
$lang['Agree_over_13'] = "我同意以上條文(而且我<b>已滿13歲</b>)";
$lang['Agree_not'] = "我不同意以上條文";

$lang['Wrong_activation'] = "您所輸入的帳號啟用序號與資料庫不符";
$lang['Send_password'] = "發送新的密碼給我";
$lang['Password_updated'] = "新的密碼已建立, 請檢查您的電子郵件信箱以取得帳號啟用的相關資訊";
$lang['No_email_match'] = "您所提供的電子郵件位址與使用者名稱不符";
$lang['New_password_activation'] = "新的密碼啟用";
$lang['Password_activated'] = "您的帳號已被重新啟用, 請檢查您的電子郵件信箱, 並使用您所收到的新密碼重新登入";

$lang['Send_email_msg'] = "發送電子郵件訊息";
$lang['No_user_specified'] = "不存在的會員";
$lang['User_prevent_email'] = "這個會員不希望收到電子郵件, 請嘗試發送私人訊息";
$lang['User_not_exist'] = "不存在的會員";
$lang['CC_email'] = "發送一個郵件備份給自己";
$lang['Email_message_desc'] = "這個訊息必須是純文字格式, 請不要加入任何的 HTML 語法或是 BBCode 代碼. 請返回並輸入您的電子郵件位址.";
$lang['Flood_email_limit'] = "您無法同時發送電子郵件給其他會員, 請稍後再試";
$lang['Recipient'] = "接收郵件";
$lang['Email_sent'] = "電子郵件已經發送";
$lang['Send_email'] = "發送電子郵件";
$lang['Empty_subject_email'] = "這個電子郵件必須要有主題";
$lang['Empty_message_email'] = "您必須輸入電子郵件內容";


//
// Memberslist
//
$lang['Select_sort_method'] = "選擇排列方式";
$lang['Sort'] = "依序排列";
$lang['Sort_Top_Ten'] = "十大排行";
$lang['Sort_Joined'] = "註冊時間";
$lang['Sort_Username'] = "會員名稱";
$lang['Sort_Location'] = "來自地區";
$lang['Sort_Posts'] = "文章總數";
$lang['Sort_Email'] = "電子郵件";
$lang['Sort_Website'] = "個人網站";
$lang['Sort_Ascending'] = "由小而大";
$lang['Sort_Descending'] = "由大而小";
$lang['Order'] = "順序";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "會員群組控制台";
$lang['Group_member_details'] = "會員群組清單";
$lang['Group_member_join'] = "加入群組";

$lang['Group_Information'] = "群組訊息";
$lang['Group_name'] = "群組名稱";
$lang['Group_description'] = "群組描述";
$lang['Group_membership'] = "群組身分";
$lang['Group_Members'] = "群組成員";
$lang['Group_Moderator'] = "群組組長";
$lang['Pending_members'] = "審核會員";

$lang['Group_type'] = "群組形態";
$lang['Group_open'] = "開放群組";
$lang['Group_closed'] = "封閉群組";
$lang['Group_hidden'] = "隱形群組";

$lang['Current_memberships'] = "目前會員";
$lang['Non_member_groups'] = "沒有會員的群組";
$lang['Memberships_pending'] = "會員身分審核中";

$lang['No_groups_exist'] = "群組不存在";
$lang['Group_not_exist'] = "這個會員群組不存在";

$lang['Join_group'] = "加入群組";
$lang['No_group_members'] = "這個群組目前沒有成員";
$lang['Group_hidden_members'] = "這個群組是隱形的, 所以您無法檢視它的成員";
$lang['No_pending_group_members'] = "這個群組沒有審核中的會員";
$lang["Group_joined"] = "You have successfully subscribed to this group<br />You will be notified when your subscription is approved by the group moderator";
$lang['Group_request'] = "有一個會員申請加入您的群組";
$lang['Group_approved'] = "您的請求已經獲得批准";
$lang['Group_added'] = "您已經被加入這個會員群組";
$lang['Already_member_group'] = "您已經是這個群組的成員";
$lang['User_is_member_group'] = "該會員已經是這個群組的成員";
$lang['Group_type_updated'] = "群組形態已經完成更新";

$lang['Could_not_add_user'] = "您所選擇的會員不存在";
$lang['Could_not_anon_user'] = "您不能將訪客列為群組成員";

$lang['Confirm_unsub'] = "您確定您要取消加入這個群組的申請嗎?";
$lang['Confirm_unsub_pending'] = "您申請加入這個群組尚未獲得批准, 您確定要取消申請嗎?";

$lang['Unsub_success'] = "您已經取消申請加入這個群組.";

$lang['Approve_selected'] = "批准選擇";
$lang['Deny_selected'] = "駁回選擇";
$lang['Not_logged_in'] = "您必須先登入才能加入群組.";
$lang['Remove_selected'] = "移除選擇";
$lang['Add_member'] = "增加成員";
$lang['Not_group_moderator'] = "由於您不屬於管理團隊成員, 因此沒有權利執行這個動作!";

$lang['Login_to_join'] = "登入管理或加入群組身分";
$lang['This_open_group'] = "這是一個開放群組, 點選申請加入";
$lang['This_closed_group'] = "這是一個封閉的群組, 不接受申請加入";
$lang['This_hidden_group'] = "這是一個隱形群組, 無法主動加入";
$lang['Member_this_group'] = "您是這個群組的成員";
$lang['Pending_this_group'] = "您在這個群組的身分正在審核中";
$lang['Are_group_moderator'] = "您是這個群組的組長";
$lang['None'] = "沒有";

$lang['Subscribe'] = "申請加入";
$lang['Unsubscribe'] = "取消申請";
$lang['View_Information'] = "檢視相關訊息";


//
// Search
//
$lang['Search_query'] = "文章搜尋系統";
$lang['Search_options'] = "搜尋選項";

$lang['Search_keywords'] = "搜尋關鍵字";
$lang['Search_keywords_explain'] = "您可以使用'布林運算法'的方式來搜尋. <u>AND</u> 代表包含. <u>OR</u> 代表可包含. <u>NOT</u> 代表不包含.";
$lang['Search_author'] = "搜尋發表人";
$lang['Search_author_explain'] = "您可以使用 * 萬用字元搜尋";

$lang['Search_for_any'] = "搜尋符合以上任一關鍵字的資料";
$lang['Search_for_all'] = "搜尋符合以上所有關鍵字的資料";
$lang['Search_title_msg'] = "搜尋文章主題及內容";
$lang['Search_msg_only'] = "只搜尋文章內容";

$lang['Return_first'] = "搜尋結果顯示"; // followed by xxx characters in a select box
$lang['characters_posts'] = "筆資料";

$lang['Search_previous'] = "時間範圍"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "排列順序";
$lang['Sort_Time'] = "發表時間";
$lang['Sort_Post_Subject'] = "文章標題";
$lang['Sort_Topic_Title'] = "主題";
$lang['Sort_Author'] = "發表人";
$lang['Sort_Forum'] = "版面";

$lang['Display_results'] = "顯示模式";
$lang['All_available'] = "所有的";
$lang['No_searchable_forums'] = "您沒有搜尋文章的權限";

$lang['No_search_match'] = "沒有相關主題或文章符合您要搜尋的條件";
$lang['Found_search_match'] = "有 %d 筆資料符合您搜尋的條件"; // eg. Search found 1 match
$lang['Found_search_matches'] = "有 %d 筆資料符合您搜尋的條件"; // eg. Search found 24 matches

$lang['Close_window'] = "關閉視窗";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "抱歉! 只有 %s 可以在這個版面發表公告";
$lang['Sorry_auth_sticky'] = "抱歉! 只有 %s 可以在這個版面發表置頂文章";
$lang['Sorry_auth_read'] = "抱歉! 只有 %s 可以閱讀這個版面的主題";
$lang['Sorry_auth_post'] = "抱歉! 只有 %s 可以在這個版面發表新主題";
$lang['Sorry_auth_reply'] = "抱歉! 只有 %s 可以回覆這個版面的文章";
$lang['Sorry_auth_edit'] = "抱歉! 只有 %s 可以編輯這個版面的文章";
$lang['Sorry_auth_delete'] = "抱歉! 只有 %s 可以刪除這個版面的文章";
$lang['Sorry_auth_vote'] = "抱歉! 只有 %s 可以在這個版面發起投票";

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>匿名訪客</b>";
$lang['Auth_Registered_Users'] = "<b>註冊會員</b>";
$lang['Auth_Users_granted_access'] = "<b>特殊會員</b>";
$lang['Auth_Moderators'] = "<b>版面管理員</b>";
$lang['Auth_Administrators'] = "<b>系統管理員</b>";

$lang['Not_Moderator'] = "您沒有管理這個版面的權力";
$lang['Not_Authorised'] = "未獲授權";

$lang['You_been_banned'] = "您已被停止會員資格<br />請跟版面管理員, 群組管理員或是系統管理員聯絡詢問相關資訊";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "0 位會員及 "; // There ae 5 Registered and
$lang['Reg_users_online'] = "%d 位會員及 "; // There ae 5 Registered and
$lang['Reg_user_online'] = "%d 位會員及 "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "0 位隱形在線上"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d 位隱形在線上"; // 6 Hidden users online
$lang['Hidden_user_online'] = "%d 位隱形在線上"; // 6 Hidden users online
$lang['Guest_users_online'] = "%d 位訪客在線上"; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = "0 位訪客在線上"; // There are 10 Guest users online
$lang['Guest_user_online'] = "%d 位訪客在線上"; // There is 1 Guest user online
$lang['No_users_browsing'] = "目前沒有使用者瀏覽這個討論區";

$lang['Online_explain'] = "這些資料根據的是最近 5 分鐘內會員的活動記錄";

$lang['Forum_Location'] = "版面位置";
$lang['Last_updated'] = "最後更新於";

$lang['Forum_index'] = "討論區首頁";
$lang['Logging_on'] = "正在登入";
$lang['Posting_message'] = "正在發表文章";
$lang['Searching_forums'] = "搜尋討論區文章";
$lang['Viewing_profile'] = "檢視個人資料";
$lang['Viewing_online'] = "檢視誰在線上";
$lang['Viewing_member_list'] = "檢視會員清單";
$lang['Viewing_priv_msgs'] = "檢視私人訊息";
$lang['Viewing_FAQ'] = "檢視常見問題";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "版面管理控制台";
$lang['Mod_CP_explain'] = "在這個控制面板裡, 您可以執行多項版面管理功能. 您可以鎖定, 解除鎖定, 移動或刪除任何數量的文章主題";

$lang['Select'] = "選擇";
$lang['Delete'] = "刪除";
$lang['Move'] = "移動";
$lang['Lock'] = "鎖定";
$lang['Unlock'] = "解除";

$lang['Topics_Removed'] = "選擇的主題已從資料庫移除";
$lang['Topics_Locked'] = "選擇的主題已鎖定";
$lang['Topics_Moved'] = "選擇的主題已移動";
$lang['Topics_Unlocked'] = "選擇的主題已解除鎖定";
$lang['No_Topics_Moved'] = "沒有主題被移動";

$lang['Confirm_delete_topic'] = "您確定您要移除所選擇的主題嗎?";
$lang['Confirm_lock_topic'] = "您確定您要鎖定所選擇的主題嗎?";
$lang['Confirm_unlock_topic'] = "您確定您要解除鎖定所選擇的主題嗎?";
$lang['Confirm_move_topic'] = "您確定您要移動所選擇的主題嗎?";

$lang['Move_to_forum'] = "移動到";
$lang['Leave_shadow_topic'] = "在舊的版面上留下被移動的主題";

$lang['Split_Topic'] = "主題分割控制台";
$lang['Split_Topic_explain'] = "您可以使用下列表格將一個主題分割成二, 您可以選擇分割個別的文章或是從指定的文章分隔";
$lang['Split_title'] = "新的主題名稱";
$lang['Split_forum'] = "置放新主題的版面";
$lang['Split_posts'] = "分割選擇的文章";
$lang['Split_after'] = "從指定的文章分隔";
$lang['Topic_split'] = "您選擇的主題已經完成分割";

$lang['Too_many_error'] = "您指定了過多的文章. 您只可以選擇一個指定的文章來分割主題!";

$lang['None_selected'] = "您沒有選擇任何主題來執行這個動作, 請返回並至少選擇一個主題.";
$lang['New_forum'] = "新版面";

$lang['This_posts_IP'] = "發表人的 IP 位址";
$lang['Other_IP_this_user'] = "這個使用者回覆時用過的其它 IP 位址";
$lang['Users_this_IP'] = "使用者發表時來自這個 IP 位址";
$lang['IP_info'] = "IP 位址報告";
$lang['Lookup_IP'] = "尋找 IP 位址";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "所有的時間均為 %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 小時";
$lang['-11'] = "GMT - 11 小時";
$lang['-10'] = "HST (夏威夷)";
$lang['-9'] = "GMT - 9 小時";
$lang['-8'] = "PST (美國/加拿大)";
$lang['-7'] = "MST (美國/加拿大)";
$lang['-6'] = "CST (美國/加拿大)";
$lang['-5'] = "EST (美國/加拿大)";
$lang['-4'] = "GMT - 4 小時";
$lang['-3.5'] = "GMT - 3.5 Hours";
$lang['-3'] = "GMT - 3 小時";
$lang['-2'] = "中大西洋";
$lang['-1'] = "GMT - 1 小時";
$lang['0'] = "GMT";
$lang['1'] = "CET (歐洲)";
$lang['2'] = "EET (歐洲)";
$lang['3'] = "GMT + 3 小時";
$lang['3.5'] = "GMT + 3.5 Hours";
$lang['4'] = "GMT + 4 小時";
$lang['4.5'] = "GMT + 4.5 Hours";
$lang['5'] = "GMT + 5 小時";
$lang['5.5'] = "GMT + 5.5 Hours";
$lang['6'] = "GMT + 6 小時";
$lang['7'] = "GMT + 7 小時";
$lang['8'] = "台北時間 (GMT + 8 小時)";
$lang['9'] = "GMT + 9 小時";
$lang['9.5'] = "CST (Australia)";
$lang['10'] = "EST (澳洲)";
$lang['11'] = "GMT + 11 小時";
$lang['12'] = "GMT + 12 小時";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT - 12 小時) 埃尼威托克島, 瓜加林島";
$lang['tz']['-11'] = "(GMT - 11 小時) 中途島, 薩摩亞";
$lang['tz']['-10'] = "(GMT - 10 小時) 夏威夷";
$lang['tz']['-9'] = "(GMT - 9 小時) 阿拉斯加";
$lang['tz']['-8'] = "(GMT - 8 小時) 太平洋標準時間 (美國 & 加拿大)";
$lang['tz']['-7'] = "(GMT - 7 小時) 山區標準時間 (美國 & 加拿大)";
$lang['tz']['-6'] = "(GMT - 6 小時) 中央標準時間 (美國 & 加拿大), 墨西哥城";
$lang['tz']['-5'] = "(GMT - 5 小時) 東部標準時間 (美國 & 加拿大), 波哥大, 利馬, 基多";
$lang['tz']['-4'] = "(GMT - 4 小時) 大西洋標準時間 (加拿大), 卡拉卡斯, 拉巴斯";
$lang['tz']['-3.5'] = "(GMT -3:30 hours) Newfoundland";
$lang['tz']['-3'] = "(GMT - 3 小時) 巴西, 布宜諾斯艾利斯, 喬治城, 福克蘭群島";
$lang['tz']['-2'] = "(GMT - 2 小時) 中大西洋, 亞森松島, 聖赫勒拿島";
$lang['tz']['-1'] = "(GMT - 1 小時) 亞速爾群島, 維德角";
$lang['tz']['0'] = "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia";
$lang['tz']['1'] = "(GMT +1:00 hours) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome";
$lang['tz']['2'] = "(GMT +2:00 hours) Cairo, Helsinki, Kaliningrad, South Africa";
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

$lang['days_long'][0] = "星期日";
$lang['days_long'][1] = "星期一";
$lang['days_long'][2] = "星期二";
$lang['days_long'][3] = "星期三";
$lang['days_long'][4] = "星期四";
$lang['days_long'][5] = "星期五";
$lang['days_long'][6] = "星期六";

$lang['days_short'][0] = "Sun";
$lang['days_short'][1] = "Mon";
$lang['days_short'][2] = "Tue";
$lang['days_short'][3] = "Wed";
$lang['days_short'][4] = "Thu";
$lang['days_short'][5] = "Fri";
$lang['days_short'][6] = "Sat";

$lang['months_long'][0] = "一月";
$lang['months_long'][1] = "二月";
$lang['months_long'][2] = "三月";
$lang['months_long'][3] = "四月";
$lang['months_long'][4] = "五月";
$lang['months_long'][5] = "六月";
$lang['months_long'][6] = "七月";
$lang['months_long'][7] = "八月";
$lang['months_long'][8] = "九月";
$lang['months_long'][9] = "十月";
$lang['months_long'][10] = "十一月";
$lang['months_long'][11] = "十二月";

$lang['months_short'][0] = "Jan";
$lang['months_short'][1] = "Feb";
$lang['months_short'][2] = "Mar";
$lang['months_short'][3] = "Apr";
$lang['months_short'][4] = "May";
$lang['months_short'][5] = "Jun";
$lang['months_short'][6] = "Jul";
$lang['months_short'][7] = "Aug";
$lang['months_short'][8] = "Sep";
$lang['months_short'][9] = "Oct";
$lang['months_short'][10] = "Nov";
$lang['months_short'][11] = "Dec";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "系統訊息";
$lang['Critical_Information'] = "重大訊息";

$lang['General_Error'] = "一般錯誤";
$lang['Critical_Error'] = "重大錯誤";
$lang['An_error_occured'] = "發生錯誤";
$lang['A_critical_error'] = "發生重大錯誤";

//
// That's all Folks!
// -------------------------------------------------

?>