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
$lang['Forum'] = "论坛";
$lang['Category'] = "讨论区";
$lang['Topic'] = "主题";
$lang['Topics'] = "主题";
$lang['Replies'] = "回复";
$lang['Views'] = "阅读";
$lang['Post'] = "文章";
$lang['Posts'] = "文章";
$lang['Posted'] = "发表于";
$lang['Username'] = "会员名称";
$lang['Password'] = "密码";
$lang['Email'] = "Email";
$lang['Poster'] = "发表者";
$lang['Author'] = "作者";
$lang['Time'] = "时间";
$lang['Hours'] = "小时内";
$lang['Message'] = "留言";

$lang['1_Day'] = "1 天内";
$lang['7_Days'] = "7 天内";
$lang['2_Weeks'] = "2 个星期内";
$lang['1_Month'] = "1 个月内";
$lang['3_Months'] = "3 个月内";
$lang['6_Months'] = "6 个月内";
$lang['1_Year'] = "1 年内";

$lang['Go'] = "Go";
$lang['Jump_to'] = "转跳到";
$lang['Submit'] = "发送";
$lang['Reset'] = "重设";
$lang['Cancel'] = "取消";
$lang['Preview'] = "预览";
$lang['Confirm'] = "确定";
$lang['Spellcheck'] = "检查语法";
$lang['Yes'] = "是";
$lang['No'] = "否";
$lang['Enabled'] = "开启";
$lang['Disabled'] = "关闭";
$lang['Error'] = "错误";

$lang['Next'] = "下一个";
$lang['Previous'] = "上一个";
$lang['Goto_page'] = "前往页面";
$lang['Joined'] = "加入于";
$lang['IP_Address'] = "IP 地址";

$lang['Select_forum'] = "选择一个版面";
$lang['View_latest_post'] = "浏览最旧的帖子";
$lang['View_newest_post'] = "浏览最新的帖子";
$lang['Page_of'] = "第<b>%d</b>页/共<b>%d</b>页"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "ICQ Number";
$lang['AIM'] = "AIM Address";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "%s 首页";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "发表新帖";
$lang['Reply_to_topic'] = "回复帖子";
$lang['Reply_with_quote'] = "引用并回复";

$lang['Click_return_topic'] = "点击 %s这里%s 返回主题"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "点击 %s这里%s 再试一遍";
$lang['Click_return_forum'] = "点击 %s这里%s 返回论坛";
$lang['Click_view_message'] = "点击 %s这里%s 阅读您的帖子";
$lang['Click_return_modcp'] = "点击 %s这里%s 返回斑竹管理区";
$lang['Click_return_group'] = "点击 %s这里%s 返回团队信息区(to return to group information)";

$lang['Admin_panel'] = "论坛管理员控制面板";

$lang['Board_disable'] = "对不起,本论坛暂时不能访问,请待会在试.";


//
// Global Header strings
//
$lang['Registered_users'] = "注册会员:";
$lang['Browsing_forum'] = "正在浏览这个版面的会员:";
$lang['Online_users_zero_total'] = "总计有 <b>0</b> 位朋友在线 :: ";
$lang['Online_users_total'] = "总计有 <b>%d</b> 位朋友在线 :: ";
$lang['Online_user_total'] = "总计有 <b>%d</b> 位朋友在线 :: ";
$lang['Reg_users_zero_total'] = "0 位会员, ";
$lang['Reg_users_total'] = "%d 位会员, ";
$lang['Reg_user_total'] = "%d 位会员, ";
$lang['Hidden_users_zero_total'] = "0 位隐身和 ";
$lang['Hidden_user_total'] = "%d 位隐身和 ";
$lang['Hidden_user_total'] = "%d 位隐身和 ";
$lang['Guest_users_zero_total'] = "0 位游客";
$lang['Guest_users_total'] = "%d 位游客";
$lang['Guest_user_total'] = "%d 位游客";
$lang['Record_online_users'] = "最高在线纪录是 <b>%s</b> 人 %s"; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = "%s论坛管理员%s";
$lang['Mod_online_color'] = "%s斑竹%s";

$lang['You_last_visit'] = "您上次访问时间是 %s"; // %s replaced by date/time
$lang['Current_time'] = "现在的时间是 %s"; // %s replaced by time

$lang['Search_new'] = "阅读上次访问后的帖子";
$lang['Search_your_posts'] = "阅读您发表的帖子";
$lang['Search_unanswered'] = "阅读尚未回答的帖子";

$lang['Register'] = "注册";
$lang['Profile'] = "个人资料";
$lang['Edit_profile'] = "编辑您的个人资料";
$lang['Search'] = "搜索";
$lang['Memberlist'] = "会员列表";
$lang['FAQ'] = "常见问题";
$lang['BBCode_guide'] = "BBCode 指南";
$lang['Usergroups'] = "团队";
$lang['Last_Post'] = "最后发表";
$lang['Moderator'] = "斑竹";
$lang['Moderators'] = "斑竹";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "论坛共还没有帖子"; // Number of posts
$lang['Posted_articles_total'] = "论坛共有 <b>%d</b> 个帖子"; // Number of posts
$lang['Posted_article_total'] = "论坛共有 <b>%d</b> 个帖子"; // Number of posts
$lang['Registered_users_zero_total'] = "论坛共还没有注册会员"; // # registered users
$lang['Registered_users_total'] = "论坛共有 <b>%d</b> 位注册会员"; // # registered users
$lang['Registered_user_total'] = "论坛共有 <b>%d</b> 位注册会员"; // # registered users
$lang['Newest_user'] = "最新注册的会员是 <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "上次访问后没有新帖";
$lang['No_new_posts'] = "没有新帖";
$lang['New_posts'] = "有新贴";
$lang['New_post'] = "有新贴";
$lang['No_new_posts_hot'] = "没有新帖 [ 热门 ]";
$lang['New_posts_hot'] = "有新贴 [ 热门 ]";
$lang['No_new_posts_locked'] = "没有新帖 [ 锁定 ]";
$lang['New_posts_locked'] = "有新贴 [ 解锁 ]";
$lang['Forum_is_locked'] = "关闭的论坛";


//
// Login
//
$lang['Enter_password'] = "请输入您的用户名和密码登陆";
$lang['Login'] = "登陆";
$lang['Logout'] = "注销";

$lang['Forgotten_password'] = "我忘记了密码!";

$lang['Log_me_in'] = "浏览时自动登陆";

$lang['Error_login'] = "您提供的用户名或密码不正确";


//
// Index page
//
$lang['Index'] = "首页";
$lang['No_Posts'] = "没有帖子";
$lang['No_forums'] = "这个版面还没有帖子";

$lang['Private_Message'] = "站内信件";
$lang['Private_Messages'] = "站内信件";
$lang['Who_is_Online'] = "当前在线状态";

$lang['Mark_all_forums'] = "标记所有论坛为已读";
$lang['Forums_marked_read'] = "所有论坛已表记为已读";


//
// Viewforum
//
$lang['View_forum'] = "浏览论坛";

$lang['Forum_not_exist'] = "您选择的论坛不存在";
$lang['Reached_on_error'] = "您选择的论坛出错了";

$lang['Display_topics'] = "显示以前的帖子";
$lang['All_Topics'] = "所有的帖子";

$lang['Topic_Announcement'] = "<b>公告:</b>";
$lang['Topic_Sticky'] = "<b>置顶:</b>";
$lang['Topic_Moved'] = "<b>移动:</b>";
$lang['Topic_Poll'] = "<b>[ 投票 ]</b>";

$lang['Mark_all_topics'] = "标记所有帖子为已读";
$lang['Topics_marked_read'] = "这个论坛的所有帖子已标记为已读";

$lang['Rules_post_can'] = "您<b>可以</b>发布新主题";
$lang['Rules_post_cannot'] = "您<b>不能</b>发布新主题";
$lang['Rules_reply_can'] = "您<b>可以</b>在这个论坛回复主题";
$lang['Rules_reply_cannot'] = "您<b>不能</b>在这个论坛回复主题";
$lang['Rules_edit_can'] = "您<b>可以</b>在这个论坛编辑自己的帖子";
$lang['Rules_edit_cannot'] = "您<b>不能</b>在这个论坛编辑自己的帖子";
$lang['Rules_delete_can'] = "您<b>可以</b>在这个论坛删除自己的帖子";
$lang['Rules_delete_cannot'] = "您<b>不能</b>在这个论坛删除自己的帖子";
$lang['Rules_vote_can'] = "您<b>可以</b>在这个论坛发表投票";
$lang['Rules_vote_cannot'] = "您<b>不能</b>在这个论坛发表投票";
$lang['Rules_moderate'] = "您<b>可以</b>%s管理这个论坛%s"; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = "这个论坛里还没有帖子<br />点击<b>发表主题</b>发表一个帖子";


//
// Viewtopic
//
$lang['View_topic'] = "阅读主题";

$lang['Guest'] = '游客';
$lang['Post_subject'] = "发表主题";
$lang['View_next_topic'] = "阅读下一个主题";
$lang['View_previous_topic'] = "阅读上一个主题";
$lang['Submit_vote'] = "发表投票";
$lang['View_results'] = "浏览结果";

$lang['No_newer_topics'] = "这个论坛没有更新的主题";
$lang['No_older_topics'] = "这个论坛没有更旧的主题";
$lang['Topic_post_not_exist'] = "您选择的主题不存在";
$lang['No_posts_topic'] = "这个主题里没有帖子";

$lang['Display_posts'] = "显示以前的主题";
$lang['All_Posts'] = "所有主题";
$lang['Newest_First'] = "最新的主题";
$lang['Oldest_First'] = "最旧的主题";

$lang['Back_to_top'] = "返回页首";

$lang['Read_profile'] = "阅览会员资料"; 
$lang['Send_email'] = "给会员发电子邮件";
$lang['Visit_website'] = "浏览发表者的主页";
$lang['ICQ_status'] = "ICQ 状态";
$lang['Edit_delete_post'] = "编辑/删除帖子";
$lang['View_IP'] = "浏览发表者的IP地址";
$lang['Delete_post'] = "删除这个帖子";

$lang['wrote'] = "写到"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "引用"; // comes before bbcode quote output.
$lang['Code'] = "代码"; // comes before bbcode code output.

$lang['Edited_time_total'] = "最后进行编辑的是 %s on %s, 总计第 %d 次编辑"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "最后进行编辑的是 %s on %s, 总计第 %d 次编辑"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "锁定本贴";
$lang['Unlock_topic'] = "解锁本贴";
$lang['Move_topic'] = "移动本贴";
$lang['Delete_topic'] = "删除本贴";
$lang['Split_topic'] = "分割本贴";

$lang['Stop_watching_topic'] = "停止订阅本主题";
$lang['Start_watching_topic'] = "订阅本主题";
$lang['No_longer_watching'] = "您不再订阅本主题";
$lang['You_are_watching'] = "您已订阅了本主题";

$lang['Total_votes'] = "投票共计";

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "文章内容";
$lang['Topic_review'] = "预览主题";

$lang['No_post_mode'] = "No post mode specified"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = "发表新贴";
$lang['Post_a_reply'] = "发表回复";
$lang['Post_topic_as'] = "Post topic as";
$lang['Edit_Post'] = "编辑文章";
$lang['Options'] = "选项";

$lang['Post_Announcement'] = "公告";
$lang['Post_Sticky'] = "置顶";
$lang['Post_Normal'] = "普通";

$lang['Confirm_delete'] = "您确定要删除这个主题吗?";
$lang['Confirm_delete_poll'] = "您确定要删除这个投票吗?";

$lang['Flood_Error'] = "您不能在发贴后马上发表新贴，请过一会再试.";
$lang['Empty_subject'] = "您发表的帖子必须有一个主题.";
$lang['Empty_message'] = "您发表的帖子必须有内容.";
$lang['Forum_locked'] = "这个论坛已经被锁定,您不能发表,回复或者编辑帖子.";
$lang['Topic_locked'] = "这个论题已经被锁定,您不能发表,回复或者编辑帖子.";
$lang['No_post_id'] = "请选择您要编辑的主题";
$lang['No_topic_id'] = "请选择您要回复的主题";
$lang['No_valid_mode'] = "您只可以选择发表,回复或者引用帖子,请后退重试.";
$lang['No_such_post'] = "没有这个帖子,请后退重试.";
$lang['Edit_own_posts'] = "对不起您只可以编辑自己的帖子.";
$lang['Delete_own_posts'] = "对不起您只可以删除自己的帖子.";
$lang['Cannot_delete_replied'] = "对不起您可能不可以删除已经被回复的帖子.";
$lang['Cannot_delete_poll'] = "对不起您不可以删除正处于活动状态的投票.";
$lang['Empty_poll_title'] = "您必须给您发表的投票建立一个主题.";
$lang['To_few_poll_options'] = "您必须要建立至少两个投票的选项.";
$lang['To_many_poll_options'] = "您选择建立太多的投票的选项";
$lang['Post_has_no_poll'] = "这个主题没有建立投票";

$lang['Add_poll'] = "建立一个投票";
$lang['Add_poll_explain'] = "如果您不想建立投票请不要填写这个选项.";
$lang['Poll_question'] = "投票问题";
$lang['Poll_option'] = "投票选项";
$lang['Add_option'] = "建立选项";
$lang['Update'] = "更新";
$lang['Delete'] = "删除";
$lang['Poll_for'] = "运行这个投票在";
$lang['Days'] = "天内"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ 选择 0 或者不选择这个选项代表永远运行投票 ]";
$lang['Delete_poll'] = "删除投票";

$lang['Disable_HTML_post'] = "在这个帖子里禁止HTML语言";
$lang['Disable_BBCode_post'] = "在这个帖子里禁止BBCode";
$lang['Disable_Smilies_post'] = "在这个帖子里禁止表情符号";

$lang['HTML_is_ON'] = "HTML is <u>ON</u>";
$lang['HTML_is_OFF'] = "HTML is <u>OFF</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s is <u>ON</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s is <u>OFF</u>";
$lang['Smilies_are_ON'] = "Smilies are <u>ON</u>";
$lang['Smilies_are_OFF'] = "Smilies are <u>OFF</u>";

$lang['Attach_signature'] = "个性签名 (您的个性签名可以在个人资料里更改)";
$lang['Notify'] = "发贴时提醒我";
$lang['Delete_post'] = "删除这个主题";

$lang['Stored'] = "您的帖子已经成功的储存";
$lang['Deleted'] = "您的帖子已经成功的被删除";
$lang['Poll_delete'] = "您建立的投票已经成功的被删除";
$lang['Vote_cast'] = "您的选票已经投出";

$lang['Topic_reply_notification'] = "回帖通知";

$lang['bbcode_b_help'] = "粗体: [b]text[/b]  (alt+b)";
$lang['bbcode_i_help'] = "大写: [i]text[/i]  (alt+i)";
$lang['bbcode_u_help'] = "下划线: [u]text[/u]  (alt+u)";
$lang['bbcode_q_help'] = "引用文本: [quote]text[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "显示代码 : [code]code[/code]  (alt+c)";
$lang['bbcode_l_help'] = "列表: [list]text[/list] (alt+l)";
$lang['bbcode_o_help'] = "按序列表: [list=]text[/list]  (alt+o)";
$lang['bbcode_p_help'] = "插入图像: [img]http://image_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "插入链接网址: [url]http://url[/url] or [url=http://url]URL text[/url]  (alt+w)";
$lang['bbcode_a_help'] = "关闭所有开启的bbCode标签";
$lang['bbcode_s_help'] = "字体颜色: [color=red]text[/color]  提示: 您也可以使用如 color=#FF0000 这样的html语句";
$lang['bbcode_f_help'] = "字体大小: [size=x-small]small text[/size]";

$lang['Emoticons'] = "表情图案";
$lang['More_emoticons'] = "浏览更多的表情图案";

$lang['Font_color'] = "字体颜色";
$lang['color_default'] = "标准";
$lang['color_dark_red'] = "深红";
$lang['color_red'] = "红色";
$lang['color_orange'] = "橙色";
$lang['color_brown'] = "棕色";
$lang['color_yellow'] = "黄色";
$lang['color_green'] = "绿色";
$lang['color_olive'] = "橄榄";
$lang['color_cyan'] = "青色";
$lang['color_blue'] = "蓝色";
$lang['color_dark_blue'] = "深蓝";
$lang['color_indigo'] = "靛蓝";
$lang['color_violet'] = "紫色";
$lang['color_white'] = "白色";
$lang['color_black'] = "黑色";

$lang['Font_size'] = "字体大小";
$lang['font_tiny'] = "最小";
$lang['font_small'] = "小";
$lang['font_normal'] = "正常";
$lang['font_large'] = "大";
$lang['font_huge'] = "最大";

$lang['Close_Tags'] = "完成标签";
$lang['Styles_tip'] = "提示: 文字风格可以快速使用在选择的文字上";


//
// Private Messaging
//
$lang['Private_Messaging'] = "站内信件";

$lang['Login_check_pm'] = "登陆查看您的站内信件";
$lang['New_pms'] = "您有 %d 封新的站内信件"; // You have 2 new messages
$lang['New_pm'] = "您有 %d 封新的站内信件"; // You have 1 new message
$lang['No_new_pm'] = "您没有新的站内信件";
$lang['Unread_pms'] = "您有 %d 封未读的站内信件";
$lang['Unread_pm'] = "您有 %d 封未读的站内信件";
$lang['No_unread_pm'] = "您没有未读的站内信件";
$lang['You_new_pm'] = "一封新的站内信件在您的收件箱里";
$lang['You_new_pms'] = "几封新的站内信件在您的收件箱里";
$lang['You_no_new_pm'] = "没有新的站内信件";

$lang['Inbox'] = "收件箱";
$lang['Outbox'] = "已发送的信件箱";
$lang['Savebox'] = "草稿箱";
$lang['Sentbox'] = "发件箱";
$lang['Flag'] = "标记";
$lang['Subject'] = "主题";
$lang['From'] = "来自";
$lang['To'] = "发送至";
$lang['Date'] = "日期";
$lang['Mark'] = "选择";
$lang['Sent'] = "发送";
$lang['Saved'] = "保存";
$lang['Delete_marked'] = "删除已选择的站内信件";
$lang['Delete_all'] = "删除所有的站内信件";
$lang['Save_marked'] = "保存已选择的站内信件"; 
$lang['Save_message'] = "保存站内信件";
$lang['Delete_message'] = "删除站内信件";

$lang['Display_messages'] = "显示以前的帖子"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "所有的站内信件";

$lang['No_messages_folder'] = "这个文件夹里没有信件";

$lang['PM_disabled'] = "这个论坛的站内信件已经被禁用";
$lang['Cannot_send_privmsg'] = "对不起论坛管理员已经禁止您发送站内信件";
$lang['No_to_user'] = "您必须指定站内信件发送的对象";
$lang['No_such_user'] = "对不起这个用户不存在";

$lang['Disable_HTML_pm'] = "在这个信件里禁止HTML语言";
$lang['Disable_BBCode_pm'] = "在这个信件里禁止BBCode";
$lang['Disable_Smilies_pm'] = "在这个信件里禁止表情符号";

$lang['Message_sent'] = "您的站内信件发送成功";

$lang['Click_return_inbox'] = "点击 %s这里%s 返回您的收件箱";
$lang['Click_return_index'] = "点击 %s这里%s 返回首页";

$lang['Send_a_new_message'] = "发送一个新的站内信件";
$lang['Send_a_reply'] = "回复站内信件";
$lang['Edit_message'] = "编辑站内信件";

$lang['Notification_subject'] = "新的站内信件";

$lang['Find_username'] = "查找一个用户";
$lang['Find'] = "查找";
$lang['No_match'] = "找不到匹配的用户";

$lang['No_post_id'] = "没有指定主题";
$lang['No_such_folder'] = "没有这样的文件夹存在";
$lang['No_folder'] = "没有指定文件夹";

$lang['Mark_all'] = "选择所有信件";
$lang['Unmark_all'] = "取消所有选择";

$lang['Confirm_delete_pm'] = "您确定要删除这封站内信件吗?";
$lang['Confirm_delete_pms'] = "您确定要删除这些站内信件吗?";

$lang['Inbox_size'] = "您的收件箱已使用 %d%%"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "您的发件箱已使用 %d%%"; 
$lang['Savebox_size'] = "您的草稿箱已使用 %d%%"; 

$lang['Click_view_privmsg'] = "点击%s这里%s浏览您的收件箱";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "浏览个人资料 :: %s"; // %s is username 
$lang['About_user'] = "关于 %s"; // %s is username

$lang['Preferences'] = "选项";
$lang['Items_required'] = "带*的项目是必须填写的";
$lang['Registration_info'] = "注册信息";
$lang['Profile_info'] = "个人资料";
$lang['Profile_info_warn'] = "以下信息将被公开";
$lang['Avatar_panel'] = "头像控制面板";
$lang['Avatar_gallery'] = "头像画集";

$lang['Website'] = "主页";
$lang['Location'] = "位置";
$lang['Contact'] = "联络";
$lang['Email_address'] = "Email 地址";
$lang['Email'] = "Email";
$lang['Send_private_message'] = "发送站内信件";
$lang['Hidden_email'] = "[ 隐藏 ]";
$lang['Search_user_posts'] = "查找这位用户发表的帖子";
$lang['Interests'] = "兴趣";
$lang['Occupation'] = "职业"; 
$lang['Poster_rank'] = "用户级别";

$lang['Total_posts'] = "发贴总计";
$lang['User_post_pct_stats'] = "%.2f%% of total"; // 1.25% of total
$lang['User_post_day_stats'] = "平均 %.2f 封帖子每天"; // 1.5 posts per day
$lang['Search_user_posts'] = "查找%s发表的所有帖子"; // Find all posts by username

$lang['No_user_id_specified'] = "对不起这个用户不存在";
$lang['Wrong_Profile'] = "您不可以编辑去他用户的个人资料";

$lang['Only_one_avatar'] = "您只能选择一个头像";
$lang['File_no_data'] = "您提供的连接地址不存在数据";
$lang['No_connection_URL'] = "无法连接您提供的连接地址";
$lang['Incomplete_URL'] = "您提供的连接地址不完整";
$lang['Wrong_remote_avatar_format'] = "您提供的头像连接地址无效";
$lang['No_send_account_inactive'] = "对不起无法找回您的密码因为您的账户现在不在活动状态,请联络论坛管理员得到更多的信息.";

$lang['Always_smile'] = "总是开启插图功能";
$lang['Always_html'] = "总是开启 HTML";
$lang['Always_bbcode'] = "总是开启 BBCode";
$lang['Always_add_sig'] = "总是发表我的个人签名";
$lang['Always_notify'] = "总是提醒我当有人回复我的帖子";
$lang['Always_notify_explain'] = "当有人回复我的帖子时发送一封电子邮件提醒我.这个选项可以在您发表主题时更改";

$lang['Board_style'] = "论坛风格";
$lang['Board_lang'] = "论坛语言";
$lang['No_themes'] = "数据库里没有装饰主题";
$lang['Timezone'] = "时区";
$lang['Date_format'] = "日期格式";
$lang['Date_format_explain'] = "日期格式的语法和 PHP <a href=\"http://www.php.net/date\" target=\"_other\">date() 语句</a>完全相同";
$lang['Signature'] = "个人签名";
$lang['Signature_explain'] = "您填写的个人签名可以发表在您的帖子下方.个人签名有%d个字符的限制";
$lang['Public_view_email'] = "总是显示我的电子邮件地址";

$lang['Current_password'] = "现在的密码";
$lang['New_password'] = "新的密码";
$lang['Confirm_password'] = "确认新密码";
$lang['Confirm_password_explain'] = "当您希望改变密码或是您的电子邮件地址时您必须确认现在正在使用的密码";
$lang['password_if_changed'] = "只有当您希望更改密码时才需要提供新的密码";
$lang['password_confirm_if_changed'] = "只有当您希望更改密码时才需要确认新的密码";

$lang['Avatar'] = "头像";
$lang['Avatar_explain'] = "显示一个小图片在您发表的帖子旁,同一时间只能显示一个图片.图片宽度不能超过%d pixels, 高度不能超过%d pixels,图片大小不能超过%dkB."; $lang['Upload_Avatar_file'] = "从您的计算机上传图片";
$lang['Upload_Avatar_URL'] = "从一个连接上传图片";
$lang['Upload_Avatar_URL_explain'] = "提供一个图片的链接地址,图片将被复制到本论坛.";
$lang['Pick_local_Avatar'] = "从画册集里选择一个头像";
$lang['Link_remote_Avatar'] = "链接其他位置的头像";
$lang['Link_remote_Avatar_explain'] = "提供您想链接头像的地址";
$lang['Avatar_URL'] = "图片链接地址";
$lang['Select_from_gallery'] = "从画册集里选择一个头像";
$lang['View_avatar_gallery'] = "显示画册集";

$lang['Select_avatar'] = "选择头像";
$lang['Return_profile'] = "取消选择头像";
$lang['Select_category'] = "选择一个画册";

$lang['Delete_Image'] = "删除图片";
$lang['Current_Image'] = "现在使用的图片";

$lang['Notify_on_privmsg'] = "提醒我当有新的站内信件";
$lang['Popup_on_privmsg'] = "弹出一个窗口当有新的站内信件"; 
$lang['Popup_on_privmsg_explain'] = "当您有新的站内信件时将弹出一个新的小窗口来提醒您"; 
$lang['Hide_user'] = "隐藏您的在线状态";

$lang['Profile_updated'] = "您的个人资料已经更新";
$lang['Profile_updated_inactive'] = "您的个人资料已经更新,然而,您更改了账户状态.您的账户现在处于冷冻状态.察看您的电子邮件理解如何恢复您的账户,或者您需等待论坛管理员恢复您的账户活动状态.(however you have changed vital details thus your account is now inactive. or if admin activation is require wait for the administrator to reactivate your account)";

$lang['Password_mismatch'] = "您提供的密码不匹配";
$lang['Current_password_mismatch'] = "您现在使用的密码与注册时提供的不匹配";
$lang['Password_long'] = "密码不能多于32个子符";
$lang['Username_taken'] = "对不起您选择的用户名已经有人使用了";
$lang['Username_invalid'] = "您选择的用户名包含了无效的字符,像 \"";
$lang['Username_disallowed'] = "对不起您选择的用户名已经被禁用";
$lang['Email_taken'] = "对不起您提供的电子邮件地址已经被某个用户注册了";
$lang['Email_banned'] = "对不起您提供的电子邮件地址已经被禁用";
$lang['Email_invalid'] = "对不起您提供的电子邮件地址不正确";
$lang['Signature_too_long'] = "您的个人签名太长了";
$lang['Fields_empty'] = "您必须填写必须填写的项目(*)";
$lang['Avatar_filetype'] = "头像图片的类型必须是 .jpg, .gif or .png";
$lang['Avatar_filesize'] = "头像图片的大小必须小于 %d kB"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "头像图片的宽度必须小于 %d pixels 而且高度必须小于 %d pixels"; 

$lang['Welcome_subject'] = "欢迎您访问 %s 论坛"; // Welcome to my.com forums
$lang['New_account_subject'] = "新用户帐户";
$lang['Account_activated_subject'] = "账户激活";

$lang['Account_added'] = "感谢您的注册,您的账户已经被建立.您现在就可以使用您的用户名和密码登陆";
$lang['Account_inactive'] = "感谢您的注册,您的账户已经被建立.本论坛需要激活账户.请查看您的电子邮件了解激活的信息.";
$lang['Account_inactive_admin'] = "感谢您的注册,您的账户已经被建立.但是本论坛需要论坛管理员激活账户. 一封电子邮件已经被发送到管理员,您的账户被激活时您将收到通知.";
$lang['Account_active'] = "感谢您的注册,您的账户已经被建立.";
$lang['Account_active_admin'] = "账户现在已经被成功激活";
$lang['Reactivate'] = "重新激活您的账户!";
$lang['COPPA'] = "您的账户已经被建立但是需要被批准,请查看您的电子邮件了解细节.";

$lang['Registration'] = "注册服务条款";
$lang['Reg_agreement'] = "尽管论坛管理成员会尽可能尽快删除或编辑有争议或是不健康的帖子,但是他们不可能阅读所有的帖子内容.因此您因该承认这个论坛上所有的主题只由它的发表者承担责任,而不是论坛的管理成员们(除非是由他们发表的).<br /><br />您必需同意不发表带有辱骂,淫秽,粗俗,诽谤,带有仇恨性,恐吓的,不健康的或是任何违反法律的内容. 如果您这样做将导致您的账户将立即和永久性的被封锁.(您的网络服务提供商也会被通知). 在这个情况下,这个IP地址的所有用户都将被记录.您必须同意系统管理成员们有在任何时间删除,修改,移动或关闭任何主题的权力. 作为一个使用者, 您必须同意您所提供的任何资料都将被存入数据库中,这些资料除非有您的同意,系统管理员们绝不会对第三方公开,然而我们不能保证任何可能导致资料泄露的骇客入侵行为.<br /><br />这个讨论区系统使用cookie来储存您的个人信息(在您使用的本地计算机), 这些cookie不包含任何您曾经输入过的信息,它们只为了方便您能更方便的浏览. 电子邮件地址只用来确认您的注册和发送密码使用.(如果您忘记了密码,将会发送新密码的地址)<br /><br />点击下面的链接代表您同意受到这些服务条款的约束.";

$lang['Agree_under_13'] = "我同意并且我<b>小于</b>13岁";
$lang['Agree_over_13'] = "我同意并且我<b>大于</b>13岁";
$lang['Agree_not'] = "我不同意";

$lang['Wrong_activation'] = "您提供的激活密码和数据库中的不匹配";
$lang['Send_password'] = "发送一个新的激活密码给我"; 
$lang['Password_updated'] = "您个新的激活密码已经被建立,请查看您的电子邮件了解激活细节";
$lang['No_email_match'] = "您提供的电子邮件地址和数据库中的不匹配";
$lang['New_password_activation'] = "新密码激活";
$lang['Password_activated'] = "您的账户已经被重新激活.请使用您收到的电子邮件中的密码登陆";

$lang['Send_email_msg'] = "发送一封电子邮件";
$lang['No_user_specified'] = "没有选择用户";
$lang['User_prevent_email'] = "这名用户不希望收到电子邮件,您可以发送站内信件给这名用户";
$lang['User_not_exist'] = "有户不存在";
$lang['CC_email'] = "复制这封电子邮件发送给自己";
$lang['Email_message_desc'] = "这封邮件将被以纯文本格式发送,请不要包含任何 HTML 或者 BBCode.这篇邮件的回复地址将指向您的电子邮件地址.";
$lang['Flood_email_limit'] = "您不能现在发送其他的电子邮件,请过一会再试.";
$lang['Recipient'] = "收信人";
$lang['Email_sent'] = "邮件已经被发送";
$lang['Send_email'] = "发送电子邮件";
$lang['Empty_subject_email'] = "您必须给电子邮件建立一个主题";
$lang['Empty_message_email'] = "您必须给电子邮件填写内容";


//
// Memberslist
//
$lang['Select_sort_method'] = "请选择一种排序方法";
$lang['Sort'] = "排列";
$lang['Sort_Top_Ten'] = "活跃前十";
$lang['Sort_Joined'] = "注册日期";
$lang['Sort_Username'] = "用户名称";
$lang['Sort_Location'] = "来自地区";
$lang['Sort_Posts'] = "发帖总数";
$lang['Sort_Email'] = "电子邮件";
$lang['Sort_Website'] = "个人主页";
$lang['Sort_Ascending'] = "升序";
$lang['Sort_Descending'] = "降序";
$lang['Order'] = "顺序";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "团队控制面板";
$lang['Group_member_details'] = "团队成员细节";
$lang['Group_member_join'] = "加入一个团队";

$lang['Group_Information'] = "团队信息";
$lang['Group_name'] = "团队名称";
$lang['Group_description'] = "团队描述";
$lang['Group_membership'] = "团队成员";
$lang['Group_Members'] = "团队成员";
$lang['Group_Moderator'] = "团队主席";
$lang['Pending_members'] = "审核中的成员";

$lang['Group_type'] = "团队类型";
$lang['Group_open'] = "开启团队";
$lang['Group_closed'] = "关闭团队";
$lang['Group_hidden'] = "隐藏团队";

$lang['Current_memberships'] = "目前您所在的团队";
$lang['Non_member_groups'] = "没有成员的团队";
$lang['Memberships_pending'] = "您正在被审核中的团队";

$lang['No_groups_exist'] = "没有团队存在";
$lang['Group_not_exist'] = "不存在这个团队";

$lang['Join_group'] = "加入团队";
$lang['No_group_members'] = "这个团队没有成员";
$lang['Group_hidden_members'] = "这个团队处于隐藏状态,您不能查看它的成员";
$lang['No_pending_group_members'] = "这个团队不存在审核中成员";
$lang["Group_joined"] = "您已经申请加入这个团队,<br />当您的申请通过审核您将受到提醒";
$lang['Group_request'] = "加入这个团队的申请已经提交";
$lang['Group_approved'] = "您的申请已经被批准了";
$lang['Group_added'] = "您已经被加入这个团队"; 
$lang['Already_member_group'] = "您已经是这个团队的成员";
$lang['User_is_member_group'] = "用户已经是这个团队的成员";
$lang['Group_type_updated'] = "成功更新团队类型";

$lang['Could_not_add_user'] = "您选择的用户不存在";
$lang['Could_not_anon_user'] = "您不能将匿名游客列为团队成员";

$lang['Confirm_unsub'] = "您确定要从这个团队解除申请吗?";
$lang['Confirm_unsub_pending'] = "您的团队申请还没有被批准,您确定要解除申请吗?";

$lang['Unsub_success'] = "您已经从这个团队解除了申请.";

$lang['Approve_selected'] = "选择批准";
$lang['Deny_selected'] = "选择拒绝";
$lang['Not_logged_in'] = "加入团队前您必须首先登陆.";
$lang['Remove_selected'] = "选择移除";
$lang['Add_member'] = "增加成员";
$lang['Not_group_moderator'] = "您不是这个团队的管理员,您无法执行团队的管理功能.";

$lang['Login_to_join'] = "请登陆加入或者管理团队成员";
$lang['This_open_group'] = "这是一个开放的团队,点击申请成员";
$lang['This_closed_group'] = "这是一个关闭的团队,不接受新的成员";
$lang['This_hidden_group'] = "这是一个隐藏的团队,不容许自动增加成员";
$lang['Member_this_group'] = "您是这个团队的成员";
$lang['Pending_this_group'] = "您的申请正在审核中";
$lang['Are_group_moderator'] = "您是团队管理员";
$lang['None'] = "没有";

$lang['Subscribe'] = "申请";
$lang['Unsubscribe'] = "解除申请";
$lang['View_Information'] = "阅览细节";


//
// Search
//
$lang['Search_query'] = "搜索目标";
$lang['Search_options'] = "搜索选项";

$lang['Search_keywords'] = "搜索关键字";
$lang['Search_keywords_explain'] = "您可以使用<u>AND</u>来标记您希望结果里必须出现的关键字,或者使用<u>OR</u>来标记您希望结果里可能出现的关键字和<u>NOT</u>来标记您不希望结果里出现的关键字.您可以使用通配符*标记批量符合的结果";
$lang['Search_author'] = "搜索作者";
$lang['Search_author_explain'] = "您可以使用通配符*标记批量符合的结果";

$lang['Search_for_any'] = "搜索任意的内容或者您提供的搜索目标";
$lang['Search_for_all'] = "搜索所有的内容";

$lang['Return_first'] = "显示最先的"; // followed by xxx characters in a select box
$lang['characters_posts'] = "个符合的项目";

$lang['Search_previous'] = "搜索以前的帖子"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "排序方法";
$lang['Sort_Time'] = "发表时间";
$lang['Sort_Post_Subject'] = "发表主题";
$lang['Sort_Topic_Title'] = "帖子标题";
$lang['Sort_Author'] = "作者";
$lang['Sort_Forum'] = "论坛";

$lang['Display_results'] = "显示结果的";
$lang['All_available'] = "所有论坛";
$lang['No_searchable_forums'] = "您没有搜索所有所有论坛的权限";

$lang['No_search_match'] = "没有符合您要求的主题或帖子";
$lang['Found_search_match'] = "搜索到 %d 个符合的内容"; // eg. Search found 1 match
$lang['Found_search_matches'] = "搜索到 %d 个符合的内容"; // eg. Search found 24 matches

$lang['Close_window'] = "关闭窗口";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "对不起只有 %s 可以在这个论坛发表公告";
$lang['Sorry_auth_sticky'] = "对不起只有 %s 可以在这个论坛发表置顶"; 
$lang['Sorry_auth_read'] = "对不起只有 %s 可以在这个论坛浏览主题"; 
$lang['Sorry_auth_post'] = "对不起只有 %s 可以在这个论坛发表主题"; 
$lang['Sorry_auth_reply'] = "对不起只有 %s 可以在这个论坛回复主题"; 
$lang['Sorry_auth_edit'] = "对不起只有 %s 可以在这个论坛编辑主题"; 
$lang['Sorry_auth_delete'] = "对不起只有 %s 可以在这个论坛删除主题"; 
$lang['Sorry_auth_vote'] = "对不起只有 %s 可以在这个论坛发表投票"; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>匿名游客</b>";
$lang['Auth_Registered_Users'] = "<b>注册用户</b>";
$lang['Auth_Users_granted_access'] = "<b>特权用户</b>";
$lang['Auth_Moderators'] = "<b>斑竹</b>";
$lang['Auth_Administrators'] = "<b>管理员</b>";

$lang['Not_Moderator'] = "您不是这个论坛的斑竹";
$lang['Not_Authorised'] = "没有授权";

$lang['You_been_banned'] = "这个论坛已经禁止您访问<br />请联络论坛管理员了解细节";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "现在有 0 位注册有户和 "; // There ae 5 Registered and
$lang['Reg_users_online'] = "现在有 %d 位注册有户和 "; // There ae 5 Registered and
$lang['Reg_user_online'] = "现在有 %d 位注册有户和 "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "0 位隐身用户和"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d 位隐身用户在线"; // 6 Hidden users online
$lang['Hidden_user_online'] = "%d 位隐身用户在线"; // 6 Hidden users online
$lang['Guest_users_online'] = "现在有 %d 位游客在线"; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = "现在有 0 位注册用户在线"; // There are 10 Guest users online
$lang['Guest_user_online'] = "现在有 %d 位游客在线"; // There is 1 Guest user online
$lang['No_users_browsing'] = "现在没有用户在这个论坛浏览";

$lang['Online_explain'] = "这是5分钟之内的论坛在线情况";

$lang['Forum_Location'] = "论坛位置";
$lang['Last_updated'] = "最近更新";

$lang['Forum_index'] = "论坛首页";
$lang['Logging_on'] = "登陆";
$lang['Posting_message'] = "发表帖子";
$lang['Searching_forums'] = "搜索论坛";
$lang['Viewing_profile'] = "浏览个人资料";
$lang['Viewing_online'] = "浏览在线情况";
$lang['Viewing_member_list'] = "浏览成员列表";
$lang['Viewing_priv_msgs'] = "浏览站内信件";
$lang['Viewing_FAQ'] = "浏览常见问题答集";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "斑竹控制面板";
$lang['Mod_CP_explain'] = "使用以下的选项您可以在这个论坛运行大部分适量的操作. 您可以锁定,解锁, 移动或者删除任意数量的主题.";

$lang['Select'] = "选择";
$lang['Delete'] = "删除";
$lang['Move'] = "移动";
$lang['Lock'] = "锁定";
$lang['Unlock'] = "解锁";

$lang['Topics_Removed'] = "选择的主题已经成功地从数据库中删除.";
$lang['Topics_Locked'] = "选择的主题已经成功的被锁定";
$lang['Topics_Moved'] = "选择的主题已经成功的被移动";
$lang['Topics_Unlocked'] = "选择的主题已经成功的被解锁";
$lang['No_Topics_Moved'] = "没有主题被移动";

$lang['Confirm_delete_topic'] = "您确定要删除选择的主题吗?";
$lang['Confirm_lock_topic'] = "您确定要锁定选择的主题吗?";
$lang['Confirm_unlock_topic'] = "您确定要解锁选择的主题吗?";
$lang['Confirm_move_topic'] = "您确定要移动选择的主题吗?";

$lang['Move_to_forum'] = "移动到另一个论坛";
$lang['Leave_shadow_topic'] = "复制主题保留在旧论坛";

$lang['Split_Topic'] = "分隔主题控制面板";
$lang['Split_Topic_explain'] = "使用以下的选项您可以分割帖子变成两个,您可以选择分割一个或多个帖子";
$lang['Split_title'] = "新主题名";
$lang['Split_forum'] = "要分割主题到新的论坛";
$lang['Split_posts'] = "分割选择的帖子";
$lang['Split_after'] = "分割自选择以下的帖子(包含选择的帖子)";
$lang['Topic_split'] = "选择的帖子已经成功地被分割";

$lang['Too_many_error'] = "您选择了太多的帖子.您只能选择一个帖子来分割以下的帖子!";

$lang['None_selected'] = "您没有选择任何的帖子来运行这个操作.请后退选择至少一个帖子.";
$lang['New_forum'] = "新论坛";

$lang['This_posts_IP'] = "这个帖子的IP地址";
$lang['Other_IP_this_user'] = "这个作者的其他的地址";
$lang['Users_this_IP'] = "来从这个IP的用户";
$lang['IP_info'] = "IP地址信息";
$lang['Lookup_IP'] = "搜索IP地址";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "论坛时间为 %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 Hours";
$lang['-11'] = "GMT - 11 Hours";
$lang['-10'] = "HST (夏威夷)";
$lang['-9'] = "GMT - 9 Hours";
$lang['-8'] = "PST (美国/加拿大)";
$lang['-7'] = "MST (美国/加拿大)";
$lang['-6'] = "CST (美国/加拿大)";
$lang['-5'] = "EST (美国/加拿大)";
$lang['-4'] = "GMT - 4 Hours";
$lang['-3.5'] = "GMT - 3.5 Hours";
$lang['-3'] = "GMT - 3 Hours";
$lang['-2'] = "中大西洋";
$lang['-1'] = "GMT - 1 Hours";
$lang['0'] = "GMT";
$lang['1'] = "CET (欧洲)";
$lang['2'] = "EET (欧洲)";
$lang['3'] = "GMT + 3 Hours";
$lang['3.5'] = "GMT + 3.5 Hours";
$lang['4'] = "GMT + 4 Hours";
$lang['4.5'] = "GMT + 4.5 Hours";
$lang['5'] = "GMT + 5 Hours";
$lang['5.5'] = "GMT + 5.5 Hours";
$lang['6'] = "GMT + 6 Hours";
$lang['7'] = "GMT + 7 Hours";
$lang['8'] = "北京时间";
$lang['9'] = "GMT + 9 Hours";
$lang['9.5'] = "CST (澳大利亚)";
$lang['10'] = "EST (澳大利亚)";
$lang['11'] = "GMT + 11 Hours";
$lang['12'] = "GMT + 12 Hours";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT -12:00 hours) 埃尼威托克岛, 夸贾林岛";
$lang['tz']['-11'] = "(GMT -11:00 hours) 中途岛, 萨摩亚群岛";
$lang['tz']['-10'] = "(GMT -10:00 hours) 夏威夷州";
$lang['tz']['-9'] = "(GMT -9:00 hours) 阿拉斯加州";
$lang['tz']['-8'] = "(GMT -8:00 hours) 太平洋时间 (美国 &amp; 加拿大), 提华纳";
$lang['tz']['-7'] = "(GMT -7:00 hours) 山地标准时间 (美国 &amp; 加拿大), 亚利桑那州";
$lang['tz']['-6'] = "(GMT -6:00 hours) 中区时 (美国 &amp; 加拿大), 墨西哥城";
$lang['tz']['-5'] = "(GMT -5:00 hours) 东部时间 (美国 &amp; 加拿大), 波哥大, 利马, 基多";
$lang['tz']['-4'] = "(GMT -4:00 hours) 大西洋时间 (加拿大), 加拉加斯, 拉巴斯";
$lang['tz']['-3.5'] = "(GMT -3:30 hours) 纽芬兰";
$lang['tz']['-3'] = "(GMT -3:00 hours) 巴西, 布宜诺斯艾利斯, 乔治顿, 福克兰群岛";
$lang['tz']['-2'] = "(GMT -2:00 hours) 中大西洋, 亚森松岛, 圣赫勒您";
$lang['tz']['-1'] = "(GMT -1:00 hours) 亚速尔群岛, 维德角岛";
$lang['tz']['0'] = "(GMT) 卡萨布兰卡, 都柏林, 爱丁堡, 伦敦, 里斯本, 蒙罗维亚";
$lang['tz']['1'] = "(GMT +1:00 hours) 阿姆斯特丹, 柏林, 布鲁塞尔, 哥本哈根, 马德里, 巴黎, 罗马";
$lang['tz']['2'] = "(GMT +2:00 hours) 开罗, 赫尔辛基, 加里宁格勒, 南非";
$lang['tz']['3'] = "(GMT +3:00 hours) 巴格达, 利雅德, 莫斯科, 内罗毕";
$lang['tz']['3.5'] = "(GMT +3:30 hours) 德黑兰";
$lang['tz']['4'] = "(GMT +4:00 hours) 阿布扎比, 巴库, 马斯喀特, 第比利斯";
$lang['tz']['4.5'] = "(GMT +4:30 hours) 喀布尔";
$lang['tz']['5'] = "(GMT +5:00 hours) 伊卡特琳堡, 伊斯兰堡, 卡拉奇, 塔什干";
$lang['tz']['5.5'] = "(GMT +5:30 hours) 孟买, 加尔各答, 马德拉斯, 新德里";
$lang['tz']['6'] = "(GMT +6:00 hours) 阿蒙提, 科伦坡, 达卡，新西伯利亚";
$lang['tz']['6.5'] = "(GMT +6:30 hours) 仰光";
$lang['tz']['7'] = "(GMT +7:00 hours) 曼谷, 河内, 雅加达";
$lang['tz']['8'] = "(GMT +8:00 hours) 北京, 香港, 佩思, 新加坡, 台北";
$lang['tz']['9'] = "(GMT +9:00 hours) 大阪, 札幌, 汉城, 东京, 雅库茨克";
$lang['tz']['9.5'] = "(GMT +9:30 hours) 阿得雷德, 达尔文";
$lang['tz']['10'] = "(GMT +10:00 hours) 堪培拉，关岛，莫尔本, 悉尼, 符拉迪沃斯托克";
$lang['tz']['11'] = "(GMT +11:00 hours) 马加丹, 新卡里多尼亚, 所罗门群岛";
$lang['tz']['12'] = "(GMT +12:00 hours) 奥克兰, 威灵顿, 斐济, 马歇尔群岛";

$lang['days_long'][0] = "星期日";
$lang['days_long'][1] = "星期一";
$lang['days_long'][2] = "星期二";
$lang['days_long'][3] = "星期三";
$lang['days_long'][4] = "星期四";
$lang['days_long'][5] = "星期五";
$lang['days_long'][6] = "星期六";

$lang['days_short'][0] = "星期日";
$lang['days_short'][1] = "星期一";
$lang['days_short'][2] = "星期二";
$lang['days_short'][3] = "星期三";
$lang['days_short'][4] = "星期四";
$lang['days_short'][5] = "星期五";
$lang['days_short'][6] = "星期六";

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

$lang['months_short'][0] = "一月";
$lang['months_short'][1] = "二月";
$lang['months_short'][2] = "三月";
$lang['months_short'][3] = "四月";
$lang['months_short'][4] = "五月";
$lang['months_short'][5] = "六月";
$lang['months_short'][6] = "七月";
$lang['months_short'][7] = "八月";
$lang['months_short'][8] = "九月";
$lang['months_short'][9] = "十月";
$lang['months_short'][10] = "十一月";
$lang['months_short'][11] = "十二月";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "消息提示";
$lang['Critical_Information'] = "关键信息";

$lang['General_Error'] = "普通错误";
$lang['Critical_Error'] = "关键错误";
$lang['An_error_occured'] = "发生了一个错误";
$lang['A_critical_error'] = "发生了一个关键性错误";

//
// That's all Folks!
// -------------------------------------------------

?>