<?php

/***************************************************************************
 *                            lang_admin.php [chinese simplified]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_admin.php,v 1.27 2001/12/30 13:49:37 psotfx Exp $
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
 
//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "普通管理";
$lang['Users'] = "会员管理";
$lang['Groups'] = "团队管理";
$lang['Forums'] = "版面管理";
$lang['Styles'] = "风格管理";

$lang['Configuration'] = "配置选项";
$lang['Permissions'] = "权限管理";
$lang['Manage'] = "管理选项";
$lang['Disallow'] = "禁用帐号";
$lang['Prune'] = "删帖管理";
$lang['Mass_Email'] = "群体信件";
$lang['Ranks'] = "等级管理";
$lang['Smilies'] = "表情符号";
$lang['Ban_Management'] = "封锁管理";
$lang['Word_Censor'] = "文字过滤";
$lang['Export'] = "输出";
$lang['Create_new'] = "新建";
$lang['Add_new'] = "增加";
$lang['Backup_DB'] = "备份数据库";
$lang['Restore_DB'] = "恢复数据库";


//
// Index
//
$lang['Admin'] = "系统管理";
$lang['Not_admin'] = "您没有权限进入管理员控制面板";
$lang['Welcome_phpBB'] = "欢迎进入 phpBB 2 管理员控制面板";
$lang['Admin_intro'] = "感谢您选择 phpBB 2 作为您的论坛系统. 在这个版面里包含您论坛的各项统计资料. 任何时候您都可以通过点击控制面板左上方的<u>控制面板首页</u>返回到这一页. 而点击在控制面板左上方的 phpBB 标志图示可以回到您的论坛首页.在这个画面左方的其他链接,允许您控制论坛的所有管理选项.每个版面里有各项功能的使用详解.";
$lang['Main_index'] = "您的论坛首页";
$lang['Forum_stats'] = "论坛统计资料";
$lang['Admin_Index'] = "控制面板首页";
$lang['Preview_forum'] = "预览您的论坛";

$lang['Click_return_admin_index'] = "点击 %s这里%s 回到控制面板首页";

$lang['Statistic'] = "统计资料";
$lang['Value'] = "数值";
$lang['Number_posts'] = "文章总计";
$lang['Posts_per_day'] = "平均每天发表的文章";
$lang['Number_topics'] = "主题总计";
$lang['Topics_per_day'] = "平均每天发表的主题";
$lang['Number_users'] = "注册会员总计";
$lang['Users_per_day'] = "平均每天注册的会员";
$lang['Board_started'] = "论坛启用日期";
$lang['Avatar_dir_size'] = "头像资料夹文件大小";
$lang['Database_size'] = "数据库文件大小";
$lang['Gzip_compression'] ="Gzip 文件压缩格式";
$lang['Not_available'] = "无";

$lang['ON'] = "开启"; // This is for GZip compression
$lang['OFF'] = "关闭"; 


//
// DB Utils
//
$lang['Database_Utilities'] = "数据库工具管理";

$lang['Restore'] = "恢复";
$lang['Backup'] = "备份";
$lang['Restore_explain'] = "在这个选项中您可以恢复 phpBB 2 所使用的数据库表格. 如果您的服务器支持 GZIP 压缩的文件, 服务器将会自动解压您所上传的压缩文件. <b>注意！</b> 恢复过程中将会完全覆盖所有现存的资料. 数据库恢复过程可能会花费较长的时间, 在恢复完成前请不要关闭或离开这个页面.";
$lang['Backup_explain'] = "在这个选项中,您可以备份 phpBB 2 论坛的所有资料数据. 如果您有其它自行定义的表格放在 phpBB 2 论坛所使用的数据库内, 而且您也想备份这些的表格, 请在下方的 <b>附加的表格</b> 栏内输入它们的名字并用逗号区别开 (例如: abc, cde). 如果您的服务器有支持 GZIP 压缩格式, 您可以在下载前使用 GZIP 压缩来减小文件的大小.";

$lang['Backup_options'] = "备份选项";
$lang['Start_backup'] = "开始备份";
$lang['Full_backup'] = "完整备份";
$lang['Structure_backup'] = "结构备份";
$lang['Data_backup'] = "数据备份";
$lang['Additional_tables'] = "附加的表格";
$lang['Gzip_compress'] = "Gzip 压缩格式";
$lang['Select_file'] = "选择文件";
$lang['Start_Restore'] = "开始恢复";

$lang['Restore_success'] = "数据库成功恢复.<br /><br />论坛已被恢复成备份时的状态.";
$lang['Backup_download'] = "请等待. 您的备份文件将被下载!";
$lang['Backups_not_supported'] = "对不起! 备份数据不支持您的数据库系统";

$lang['Restore_Error_uploading'] = "上传的备份文件错误";
$lang['Restore_Error_filename'] = "文件名称错误, 请重新选择文件";
$lang['Restore_Error_decompress'] = "无法解压 Gzip 文件, 请以纯文字格式上传";
$lang['Restore_Error_no_file'] = "没有文件被上传";


//
// Auth pages
//
$lang['Select_a_User'] = "选择一个用户";
$lang['Select_a_Group'] = "选择一个团队";
$lang['Select_a_Forum'] = "选择一个版面";
$lang['Auth_Control_User'] = "会员权限设定"; 
$lang['Auth_Control_Group'] = "团队权限设定"; 
$lang['Auth_Control_Forum'] = "版面权限设定"; 
$lang['Look_up_User'] = "查询会员"; 
$lang['Look_up_Group'] = "查询团队"; 
$lang['Look_up_Forum'] = "查询版面"; 

$lang['Group_auth_explain'] = "在这个选项中您可以更改团队的权限设定及指定管理员资格. 请注意, 修改团队权限设定后, 独立的会员权限可能仍然可以使会员进入限制版面. 如果发生这种情况将会显示权限冲突的警告.";
$lang['User_auth_explain'] = "在这个选项中您可以更改会员的权限设定及指定管理员资格. 请注意, 修改会员权限设定后, 独立的会员权限可能仍然可以使会员进入限制版面. 如果发生这种情况将会显示权限冲突的警告.";
$lang['Forum_auth_explain'] = "在这个选项中您可以更改版面的使用权限. 您可以选择使用简单或是高级两种模式, 高级模式能提供您完整的权限设定控制. 请注意, 所有的改变都将会影响到会员的版面使用权限.";

$lang['Simple_mode'] = "简单模式";
$lang['Advanced_mode'] = "高级模式";
$lang['Moderator_status'] = "管理员资格";

$lang['Allowed_Access'] = "允许进入";
$lang['Disallowed_Access'] = "禁止进入";
$lang['Is_Moderator'] = "拥有版面管理权限";
$lang['Not_Moderator'] = "没有版面管理权限";

$lang['Conflict_warning'] = "权限冲突警告";
$lang['Conflict_access_userauth'] = "这个会员仍然可以通过团队成员的资格进入特定的版面. 您可以更改团队权限或是取消这个会员的团队资格来禁止该会员进入限制的版面.团队权限如下:";
$lang['Conflict_mod_userauth'] = "这个会员仍然可以通过团队成员的资格拥有版面管理的权限. 您可以更改团队权限或是取消这个会员的权限来禁止该会员进行版面管理.版面管理权限如下:";

$lang['Conflict_access_groupauth'] = "下列会员仍然可以通过会员权限设定进入这个特定的版面. 您可以更改会员权限来取消他们进入限制的版面. 会员权限如下: ";
$lang['Conflict_mod_groupauth'] = "下列会员依然可以通过他们的会员权限拥有版面管理的权限. 您可以更改会员权限来取消他们的版面管理权限. 会员权限如下: ";

$lang['Public'] = "公开";
$lang['Private'] = "非公开";
$lang['Registered'] = "注册会员";
$lang['Administrators'] = "论坛管理员";
$lang['Hidden'] = "隐藏";

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = "全部";
$lang['Forum_REG'] = "REG";
$lang['Forum_PRIVATE'] = "PRIVATE";
$lang['Forum_MOD'] = "MOD";
$lang['Forum_ADMIN'] = "管理";

$lang['View'] = "浏览";
$lang['Read'] = "阅读";
$lang['Post'] = "发表";
$lang['Reply'] = "回复";
$lang['Edit'] = "编辑";
$lang['Delete'] = "删除";
$lang['Sticky'] = "置顶";
$lang['Announce'] = "公告"; 
$lang['Vote'] = "投票";
$lang['Pollcreate'] = "建立投票";

$lang['Permissions'] = "权限设定";
$lang['Simple_Permission'] = "基本权限";

$lang['User_Level'] = "会员等级"; 
$lang['Auth_User'] = "会员";
$lang['Auth_Admin'] = "论坛管理员";
$lang['Group_memberships'] = "会员团队列表";
$lang['Usergroup_members'] = "团队成员列表";

$lang['Forum_auth_updated'] = "版面权限设定更新";
$lang['User_auth_updated'] = "会员权限设定更新";
$lang['Group_auth_updated'] = "团队权限设定更新";

$lang['Auth_updated'] = "权限设定已经更新";
$lang['Click_return_userauth'] = "点击 %s这里%s 返回会员权限设定";
$lang['Click_return_groupauth'] = "点击 %s这里%s 返回团队权限设定";
$lang['Click_return_forumauth'] = "点击 %s这里%s 返回版面权限设定";


//
// Banning
//
$lang['Ban_control'] = "封锁控制";
$lang['Ban_explain'] = "在这个选项中您可以设定会员的封锁. 您可以封锁一个指定的会员，一个指定范围的 IP 地址或是计算机主机名称, 这些方法禁止被封锁的会员进入论坛首页. 您也可以指定封锁电子邮件地址来防止注册会员使用不同的帐号重复注册. 请注意当您只是封锁一个电子邮件地址时将不会影响到会员在您论坛的登陆或是发表文章, 您应该使用前面两种方式其中之一或是两种一起来建立封锁.";
$lang['Ban_explain_warn'] = "当您输入一个IP地址范围时, 这个范围内所有的IP地址都将会被封锁. 您可以使用统配符 * 定义要封锁的ip地址来降低被攻击的可能. 如果您一定要输入一个范围请尽量保持精简和适当以免影响正常的使用.";

$lang['Select_username'] = "选择一个会员名称";
$lang['Select_ip'] = "选择一个 IP 地址";
$lang['Select_email'] = "选择一个电子邮件地址";

$lang['Ban_username'] = "封锁一个或多个指定的会员名称";
$lang['Ban_username_explain'] = "您可以使用鼠标和组合键 (如: Ctrl 或 Shift)一次封锁多个会员名称";
$lang['Ban_IP'] = "封锁一个或多个 IP 地址或是计算机主机名称";
$lang['IP_hostname'] = "IP 地址或是计算机主机名称";
$lang['Ban_IP_explain'] = "要指定多个不同的 IP 地址或是主机名称, 请使用逗号 ',' 来分隔它们. 要指定 IP 地址的范围, 请使用 '-' 来分隔起始地址及结束地址, 或是使用统配符 '*'";
$lang['Ban_email'] = "封锁一个或多个电子邮件地址";
$lang['Ban_email_explain'] = "要指定多个不同的电子邮件地址, 请使用逗号 ',' 来分隔它们, 或是使用通配符 '*', 例如: *@hotmail.com";
$lang['Unban_username'] = "解除一个或多个封锁的会员名称";
$lang['Unban_username_explain'] = "您可以使用鼠标及组合键 (如: Ctrl 或 Shift)一次解除多个封锁的会员名称";

$lang['Unban_IP'] = "解除一个或多个封锁的 IP 地址";
$lang['Unban_IP_explain'] = "您可以使用鼠标及组合键 (例如: Ctrl 或 Shift), 一次解除多个封锁的 IP 地址";

$lang['Unban_email'] = "解除一个或多个封锁的电子邮件地址";
$lang['Unban_email_explain'] = "您可以使用鼠标及组合键 (例如: Ctrl 或 Shift), 一次解除多个封锁的电子邮件地址";

$lang['No_banned_users'] = "没有被封锁的会员名称";
$lang['No_banned_ip'] = "没有被封锁的 IP 地址";
$lang['No_banned_email'] = "没有被封锁的电子邮件地址";

$lang['Ban_update_sucessful'] = "封锁列表已经成功更新";
$lang['Click_return_banadmin'] = "点击 %s这里%s 返回封锁设定";


//
// Configuration
//
$lang['General_Config'] = "基本配置";
$lang['Config_explain'] = "您可以使用下列表格来调整一般的设定选项. 会员及版面设定请使用画面左方 (论坛管理) 的相关链接.";

$lang['Click_return_config'] = "点击 %s这里%s 返回基本配置";

$lang['General_settings'] = "版面基本设置";
$lang['Server_name'] = "域名";
$lang['Server_name_explain'] = "您的论坛所运行位置的域名The domain name this board runs from";
$lang['Script_path'] = "脚本路径";
$lang['Script_path_explain'] = "您的论坛对应在域名的路径";
$lang['Server_port'] = "服务端口";
$lang['Server_port_explain'] = "您的服务器所运行的端口,默认值是80,只有在非默认值时改变这个选项";
$lang['General_settings'] = "论坛基本设定";
$lang['Site_name'] = "论坛名称";
$lang['Site_desc'] = "论坛描述";
$lang['Board_disable'] = "关闭论坛";
$lang['Board_disable_explain'] = "这将会关闭论坛. 当您执行这个设定时请勿登出,您将无法重新登陆!";
$lang['Acct_activation'] = "启用帐号激活";
$lang['Acc_None'] = "关闭"; // These three entries are the type of activation
$lang['Acc_User'] = "由会员激活";
$lang['Acc_Admin'] = "由管理员激活";

$lang['Abilities_settings'] = "会员及版面基本设定";
$lang['Max_poll_options'] = "投票项目的最大数目";
$lang['Flood_Interval'] = "灌水判断";
$lang['Flood_Interval_explain'] = "文章发表的间隔时间 (秒)"; 
$lang['Board_email_form'] = "会员电子邮件列表";
$lang['Board_email_form_explain'] = "会员可以互相发送电子邮件在这个论坛";
$lang['Topics_per_page'] = "每页显示主题数";
$lang['Posts_per_page'] = "每页显示发表数";
$lang['Hot_threshold'] = "热门话题设定数";
$lang['Default_style'] = "预设风格";
$lang['Override_style'] = "忽视会员选择的风格";
$lang['Override_style_explain'] = "将会员所选的风格改为预设风格";
$lang['Default_language'] = "预设语言";
$lang['Date_format'] = "日期格式";
$lang['System_timezone'] = "系统时区";
$lang['Enable_gzip'] = "开启 GZip 文件压缩格式";
$lang['Enable_prune'] = "开启计划删文模式";
$lang['Allow_HTML'] = "允许使用 HTML 语法";
$lang['Allow_BBCode'] = "允许使用 BBCode 代码";
$lang['Allowed_tags'] = "允许使用 HTML 标签";
$lang['Allowed_tags_explain'] = "以逗号分隔 HTML 标签";
$lang['Allow_smilies'] = "允许使用表情符号";
$lang['Smilies_path'] = "表情符号储存路径";
$lang['Smilies_path_explain'] = "在您 phpBB 2 根目录底下的路径, 例如: images/smilies";
$lang['Allow_sig'] = "允许使用签名档";
$lang['Max_sig_length'] = "签名档长度限定";
$lang['Max_sig_length_explain'] = "用户个人签名最多可使用字数";
$lang['Allow_name_change'] = "允许更改登陆名称";

$lang['Avatar_settings'] = "个人头像设定";
$lang['Allow_local'] = "使用系统相册";
$lang['Allow_remote'] = "允许链接头像图片";
$lang['Allow_remote_explain'] = "从其他网址链接头像图片";
$lang['Allow_upload'] = "允许用户上传头像";
$lang['Max_filesize'] = "头像文件大小设定";
$lang['Max_filesize_explain'] = "由用户上传头像图片";
$lang['Max_avatar_size'] = "图片大小不可大於";
$lang['Max_avatar_size_explain'] = "(高 x 宽 像素)";
$lang['Avatar_storage_path'] = "个人头像储存路径";
$lang['Avatar_storage_path_explain'] = "在您 phpBB 2 根目录底下的路径, 例如: images/avatars";
$lang['Avatar_gallery_path'] = "系统相册储存路径";
$lang['Avatar_gallery_path_explain'] = "在您 phpBB 2 根目录底下的路径, 例如: images/avatars/gallery";

$lang['COPPA_settings'] = "COPPA (美国儿童网路隐私保护法) 设定";
$lang['COPPA_fax'] = "COPPA 传真号码";
$lang['COPPA_mail'] = "COPPA 邮递地址";
$lang['COPPA_mail_explain'] = "这是供家长寄送 COPPA 会员注册申请书的邮递地址";
$lang['Email_settings'] = "电子邮件设定";
$lang['Admin_email'] = "论坛管理员电子邮件信箱";
$lang['Email_sig'] = "电子邮件签名档";
$lang['Email_sig_explain'] = "这个签名档将会被附加在所有由论坛系统送出的电子邮件中";
$lang['Use_SMTP'] = "使用 SMTP 服务器发送电子邮件";
$lang['Use_SMTP_explain'] = "如果您想要使用 SMTP 服务器发送电子邮件请选择 '是'";
$lang['SMTP_server'] = "SMTP 服务器名称";
$lang['SMTP_username'] = "SMTP 用户名";
$lang['SMTP_username_explain'] = "只有您的smtp服务器要求用户时才填写这个选项";
$lang['SMTP_password'] = "SMTP 密码";
$lang['SMTP_password_explain'] = "只有您的smtp服务器要求密码时才填写这个选项";

$lang['Disable_privmsg'] = "私人消息";
$lang['Inbox_limits'] = "收件夹最大容量";
$lang['Sentbox_limits'] = "寄件夹最大容量";
$lang['Savebox_limits'] = "储存夹最大容量";

$lang['Cookie_settings'] = "Cookie 设定"; 
$lang['Cookie_settings_explain'] = "这些设定控制著 Cookie 的定义, 就一般的情况, 使用系统预设值就可以了. 如果您要更改这些设定, 请谨慎设定, 不当的设定将影响会员的登陆";

$lang['Cookie_name'] = "Cookie 名称";
$lang['Cookie_domain'] = "Cookie 域名";
$lang['Cookie_path'] = "Cookie 路径";
$lang['Session_length'] = "Session 存活时间 [ 秒 ]";
$lang['Cookie_secure'] = "Cookie 加密 [ https ]";


//
// Forum Management
//
$lang['Forum_admin'] = "论坛版面管理";
$lang['Forum_admin_explain'] = "在这个控制面板里您可以增加, 删除, 编辑和重新排列分区和版面, 以及设定版面内的相应资料.";


$lang['Edit_forum'] = "编辑版面";
$lang['Create_forum'] = "建立新版面";
$lang['Create_category'] = "建立新分区";
$lang['Remove'] = "删除";
$lang['Action'] = "执行";
$lang['Update_order'] = "更新命令";
$lang['Config_updated'] = "论坛配置成功更新";
$lang['Edit'] = "编辑";
$lang['Delete'] = "删除";
$lang['Move_up'] = "往上移动";
$lang['Move_down'] = "往下移动";
$lang['Resync'] = "重整对应数据";
$lang['No_mode'] = "没有设定模式";
$lang['Forum_edit_delete_explain'] = "您可以使用下列表格来调整一般的设定选项. 会员及版面设定请使用画面左方 (系统管理) 的相关链接.";
$lang['Move_contents'] = "移动所有内容";
$lang['Forum_delete'] = "删除版面";
$lang['Forum_delete_explain'] = "您可以使用下列表格来删除版面 (或分区), 并可移动包含在版面内的所有内容内容.";
$lang['Forum_settings'] = "版面基本设定";
$lang['Forum_name'] = "版面名称";
$lang['Forum_desc'] = "版面描述";
$lang['Forum_status'] = "版面状态";
$lang['Forum_pruning'] = "计划删文";

$lang['prune_freq'] = '定期检查周期';
$lang['prune_days'] = "删除在几天内没有文章回覆的主题";
$lang['Set_prune_data'] = "您已经开启版面计划删文的功能, 但并未完成相关设定. 请回到上一步设定相关的项目";

$lang['Move_and_Delete'] = "移动/删除";

$lang['Delete_all_posts'] = "删除所有文章";
$lang['Nowhere_to_move'] = "没有移动的位置";

$lang['Edit_Category'] = "编辑分区名称";
$lang['Edit_Category_explain'] = "使用以下表格修改分区名称";

$lang['Forums_updated'] = "版面及分区资料成功更新";

$lang['Must_delete_forums'] = "在删除这个分区之前, 您必须先删除分区底下的所有版面";

$lang['Click_return_forumadmin'] = "点击 %s这里%s 返回版面管理";


//
// Smiley Management
//
$lang['smiley_title'] = "表情符号编辑";
$lang['smile_desc'] = "在这个选项中, 您可以增加, 删除或是编辑表情符号或表情符号包以便会员在文章发表或是个人消息中使用.";
$lang['smiley_config'] = "表情符号设定";
$lang['smiley_code'] = "表情符号代码";
$lang['smiley_url'] = "表情图片";
$lang['smiley_emot'] = "表情情绪";
$lang['smile_add'] = "增加一个新表情";
$lang['Smile'] = "表情";
$lang['Emotion'] = "代表情绪";

$lang['Select_pak'] = "选择的表情符号包 (.pak) 文件";
$lang['replace_existing'] = "替换现有的表情符号";
$lang['keep_existing'] = "保留现有的表情符号";
$lang['smiley_import_inst'] = "您应将表情符号包解压并上传至适当的表情符号目录.  然後选择正确的项目载入表情符号.";
$lang['smiley_import'] = "载入表情符号包";
$lang['choose_smile_pak'] = "选择一个表情符号包 .pak 文件";
$lang['import'] = "载入表情符号";
$lang['smile_conflicts'] = "在冲突的情况下所应做出的选择";
$lang['del_existing_smileys'] = "载入前先删除旧的表情符号";
$lang['import_smile_pack'] = "载入表情符号包";
$lang['export_smile_pack'] = "建立表情符号包";
$lang['export_smiles'] = "如您希望将现有的表情符号制作成表情符号包, 请点击 %s这里%s 下载 smiles.pak 文件, 并确定其后缀为.pak.";

$lang['smiley_add_success'] = "新的表情符号已经成功加入";
$lang['smiley_edit_success'] = "表情符号已经成功更新";
$lang['smiley_import_success'] = "表情符号包已经成功载入!";
$lang['smiley_del_success'] = "表情符号已经成功删除";
$lang['Click_return_smileadmin'] = "点击 %s这里%s 返回表情符号编辑";


//
// User Management
//
$lang['User_admin'] = "会员管理";
$lang['User_admin_explain'] = "在这个控制面板里, 您可以变更会员的个人资料以及现存的特殊选项. 如果您要修改会员的权限, 请使用会员及团队管理的权限设定功能.";
$lang['Look_up_user'] = "查询会员";

$lang['Admin_user_fail'] = "无法更新会员的个人资料";
$lang['Admin_user_updated'] = "会员的个人资料已经成功更新";
$lang['Click_return_useradmin'] = "点击 %s这里%s 返回会员管理";

$lang['User_delete'] = "删除会员";
$lang['User_delete_explain'] = "点击这里将会删除会员, 这个选择将无法恢复";
$lang['User_deleted'] = "会员被成功删除.";

$lang['User_status'] = "会员帐号已激活";
$lang['User_allowpm'] = "允许使用私人讯息";
$lang['User_allowavatar'] = "允许使用个人头像";

$lang['Admin_avatar_explain'] = "在这个选项您可以浏览或删除会员现存的个人头像";

$lang['User_special'] = "管理员专区";
$lang['User_special_explain'] = "您可以变更会员的帐号激活状态及其它未授权会员的选项设定, 普通会员无法自行改变这些设定";


//
// Group Management
//
$lang['Group_administration'] = "团队管理";
$lang['Group_admin_explain'] = "在这个控制面板里您可以管理所有的会员团队, 您可以建立, 删除以及编辑现存的会员团队. 您可以指定团队管理员, 设定团队模式 (开放/封闭/隐藏) 以及团队的名称和描述.";
$lang['Error_updating_groups'] = "团队更新时发生错误";
$lang['Updated_group'] = "团队已经成功更新";
$lang['Added_new_group'] = "新的团队已经成功加入";
$lang['Deleted_group'] = "团队已被顺利删除";
$lang['New_group'] = "建立新团队";
$lang['Edit_group'] = "编辑团队";
$lang['group_name'] = "团队名称";
$lang['group_description'] = "团队描述";
$lang['group_moderator'] = "团队管理员";
$lang['group_status'] = "团队状态";
$lang['group_open'] = "开放团队";
$lang['group_closed'] = "关闭团队";
$lang['group_hidden'] = "隐藏团队";
$lang['group_delete'] = "删除团队";
$lang['group_delete_check'] = "删除这个团队";
$lang['submit_group_changes'] = "提交更新";
$lang['reset_group_changes'] = "清除重设";
$lang['No_group_name'] = "您必许指定团队名称";
$lang['No_group_moderator'] = "您必许指定团队的管理员";
$lang['No_group_mode'] = "您必须指定团队状态 (开放/封闭/隐藏)";
$lang['delete_group_moderator'] = "删除原有的团队管理员?";
$lang['delete_moderator_explain'] = "如果您变更了团队管理员而且勾选这个选项会将原有的团队管理员从团队中移除, 如不勾选, 这个会员将成为团队的普通成员.";
$lang['Click_return_groupsadmin'] = "点击 %s这里%s 返回团队管理.";
$lang['Select_group'] = "选择团队";
$lang['Look_up_group'] = "查询团队";


//
// Prune Administration
//
$lang['Forum_Prune'] = "版面计划删文";
$lang['Forum_Prune_explain'] = "这将删除所有在限定时间内没有回覆的主题. 如果您没有指定时限 (日数), 所有的主题都将会被删除. 但是无法删除正在进行中的投票主题或是公告. 您必须手动移除这些主题.";
$lang['Do_Prune'] = "执行计划删除";
$lang['All_Forums'] = "所有版面";
$lang['Prune_topics_not_posted'] = "删除在几天内没有文章回覆的主题";
$lang['Topics_pruned'] = "计划删除的主题";
$lang['Posts_pruned'] = "计划删除的文章";
$lang['Prune_success'] = "成功完成版面文章删除";


//
// Word censor
//
$lang['Words_title'] = "文字过滤";
$lang['Words_explain'] = "在这个控制面板里您可以建立, 编辑及删除过滤文字, 这些指定的文字将会被过滤并以替换文字显示. 另外会员也将无法使用含有这些限定文字的名称来注册. 限定的名称允许使用统配符 (*), 例如: *test* 代表包括 detestable等, test* 包括 testing等, *test 包括 detest等";
$lang['Word'] = "过滤文字";
$lang['Edit_word_censor'] = "编辑过滤文字";
$lang['Replacement'] = "替换文字";
$lang['Add_new_word'] = "增加过滤文字";
$lang['Update_word'] = "更新过滤文字";

$lang['Must_enter_word'] = "您必须输入要过滤的文字及其替换文字";
$lang['No_word_selected'] = "您没有选择要编辑的过滤文字";

$lang['Word_updated'] = "您所选择的过滤文字已经成功更新";
$lang['Word_added'] = "新的过滤文字已经成功加入";
$lang['Word_removed'] = "您所选择的过滤文字已被成功移除";

$lang['Click_return_wordadmin'] = "点击 %s这里%s 返回文字过滤";


//
// Mass Email
//
$lang['Mass_email_explain'] = "在这个选项里您可以发送电子邮件讯息给所有的会员或是特定的团队的成员. 这封电子邮件将被寄送至系统管理员提供的电子邮件信箱, 并以密件副本的方式寄送给所有收件人. 如果收件人数过多, 系统需要较长的时间来执行, 请在提交送出后耐心等候, <b>切勿</b>在程序完成之前停止网页动作.当发送完成时将显示提示.";
$lang['Compose'] = "写邮件"; 

$lang['Recipients'] = "收件人"; 
$lang['All_users'] = "所有会员";

$lang['Email_successfull'] = "讯息已经寄出";
$lang['Click_return_massemail'] = "点击 %s这里%s 返回电子邮件通知";


//
// Ranks admin
//
$lang['Ranks_title'] = "等级管理";
$lang['Ranks_explain'] = "在这个控制面板里, 您可以在增加, 编辑, 浏览以及删除等级. 您也可以使用等级应用于会员管理功能.";
$lang['Add_new_rank'] = "加入新的等级";

$lang['Rank_title'] = "等级名称";
$lang['Rank_special'] = "特殊等级";
$lang['Rank_minimum'] = "文章的最小数量";
$lang['Rank_maximum'] = "文章的最大数量";
$lang['Rank_image'] = "等级图片";
$lang['Rank_image_explain'] = "使用这个来定义等级图片的路径";

$lang['Must_select_rank'] = "您必须选择一个等级";
$lang['No_assigned_rank'] = "没有指定的等级";

$lang['Rank_updated'] = "等级已经成功更新";
$lang['Rank_added'] = "新的等级已经成功加入";
$lang['Rank_removed'] = "等级名称已成功被移除";

$lang['Click_return_rankadmin'] = "点击 %s这里%s 返回等级管理";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "禁用帐号控制";
$lang['Disallow_explain'] = "在这个选项中, 您可以控制禁用的会员帐号名称 (可使用通配符 '*'). 请注意, 您无法禁用已经注册使用的会员名称, 您必须先删除这个会员帐号, 才能使用禁用帐号的功能.";
$lang['Delete_disallow'] = "删除";
$lang['Delete_disallow_title'] = "删除禁用帐的号名称";
$lang['Delete_disallow_explain'] = "您可以从列表中选择要移除的禁用帐号德名称";
$lang['Add_disallow'] = "增加";
$lang['Add_disallow_title'] = "增加禁用的帐号名称";
$lang['Add_disallow_explain'] = "您可以使用通配符 '*'来禁用范围较大的会员名称";
$lang['No_disallowed'] = "没有禁用的帐号名称";

$lang['Disallowed_deleted'] = "您所选择的禁用帐号名称已成功被移除";
$lang['Disallow_successful'] = "新的禁用帐号名称已经成功加入";
$lang['Disallowed_already'] = "无法禁用您所输入的帐号名称. 该帐号名称可能已在禁用列表内或已被注册使用";

$lang['Click_return_disallowadmin'] = "点击 %s这里%s 返回禁用帐号控制";


//
// Styles Admin
//
$lang['Styles_admin'] = "版面风格管理";
$lang['Styles_explain'] = "使用这个功能您可以增加, 移除及管理各种不同的版面风格 (范本和主题) 提供会员选择使用.";
$lang['Styles_addnew_explain'] = "以下列表包含所有可使用的主题. 这份列表上的主题均尚未安装到 phpBB 2 的数据库内. 要安装新的主题请直接按下右方的执行链接.";

$lang['Select_template'] = "选择范本名称";

$lang['Style'] = "风格";
$lang['Template'] = "范本";
$lang['Install'] = "安装";
$lang['Download'] = "下载";

$lang['Edit_theme'] = "编辑主题";
$lang['Edit_theme_explain'] = "您可以使用下列表格编辑主题设定.";

$lang['Create_theme'] = "增加主题";
$lang['Create_theme_explain'] = "您可以使用下列表格来为指定的范本增加新的主题. 当设定颜色时 (您必须使用十六进位码, 例如: FFFFFF) 不包含起始字元 #, 举例如下: CCCCCC 为正确的表示法, #CCCCCC 则是错误的.";

$lang['Export_themes'] = "输出主题";
$lang['Export_explain'] = "在这个版面里, 您可以输出指定范本的主题资料. 由列表中选择指定的范本后, 系统将会建立主题的配置数据文件并储存到指定的范本目录. 如果资料无法储存, 您可以下载这个资料文件. 如果您希望系统能直接储存这些文件数据, 您必须确定指定范本目录可写. 如果您需要更多这方面的资料, 请参考 phpBB 2 使用说明.";
$lang['Theme_installed'] = "指定的主题已经安装完成";
$lang['Style_removed'] = "指定的版面风格已从数据库中移除. 要从您的系统中完全的移除这个版面风格, 您必须从 /templates 中移除对应的范本目录";
$lang['Theme_info_saved'] = "指定的主题资料已经成功储存. 您必须立即修改 theme_info.cfg 成唯读属性 (如果适用於指定的范本目录)";
$lang['Theme_updated'] = "指定的主题已被更新. 您必须输出新的主题设定值";
$lang['Theme_created'] = "主题已被建立. 您必须输出主题设定文件, 以维持正常的操作及资料安全";

$lang['Confirm_delete_style'] = "您确定要删除这个版面风格吗?";

$lang['Download_theme_cfg'] = "系统无法写入主题的设定文件. 您可以点击以下的按钮下载这个文件. 当您下载完这个文件后, 您即可将文件移到包含此范本的目录之下. 重新包装这个文件用以发行或是在其它地方使用.";
$lang['No_themes'] = "您指定的范本并没有包含任何的主题. 要建立新的主题, 请按下左方控制面板的 '建立' 链接";
$lang['No_template_dir'] = "无法打开范本目录. 这有可能是因为此目录设定为不可读取的属性或是文件根本不存在";
$lang['Cannot_remove_style'] = "您无法移除预设的版面风格. 请先变更版面的预设风格后再重试一次";
$lang['Style_exists'] = "指定的版面风格名称已经存在, 请回到上一步并选择一个不同的名称";

$lang['Click_return_styleadmin'] = "点击 %s这里%s 返回版面风格管理";

$lang['Theme_settings'] = "主题设定";
$lang['Theme_element'] = "主题元件";
$lang['Simple_name'] = "简易名称";
$lang['Value'] = "数值";
$lang['Save_Settings'] = "储存设定";

$lang['Stylesheet'] = "CSS 风格表";
$lang['Background_image'] = "背景图案";
$lang['Background_color'] = "背景颜色";
$lang['Theme_name'] = "主题名称";
$lang['Link_color'] = "正常的链接颜色";
$lang['Text_color'] = "文字颜色";
$lang['VLink_color'] = "参观过的链接颜色 (visited)";
$lang['ALink_color'] = "鼠标按下的链接颜色 (active)";
$lang['HLink_color'] = "鼠标移过的链接颜色 (hover)";
$lang['Tr_color1'] = "表格列颜色一";
$lang['Tr_color2'] = "表格列颜色二";
$lang['Tr_color3'] = "表格列颜色三";
$lang['Tr_class1'] = "表格列属性类别一";
$lang['Tr_class2'] = "表格列属性类别二";
$lang['Tr_class3'] = "表格列属性类别三";
$lang['Th_color1'] = "项目标题颜色一";
$lang['Th_color2'] = "项目标题颜色二";
$lang['Th_color3'] = "项目标题颜色三";
$lang['Th_class1'] = "项目标题属性类别一";
$lang['Th_class2'] = "项目标题属性类别二";
$lang['Th_class3'] = "项目标题属性类别三";
$lang['Td_color1'] = "资料格颜色一";
$lang['Td_color2'] = "资料格颜色二";
$lang['Td_color3'] = "资料格颜色三";
$lang['Td_class1'] = "资料格属性类别一";
$lang['Td_class2'] = "资料格属性类别二";
$lang['Td_class3'] = "资料格属性类别三";
$lang['fontface1'] = "字型种类一";
$lang['fontface2'] = "字型种类二";
$lang['fontface3'] = "字型种类三";
$lang['fontsize1'] = "字型大小一";
$lang['fontsize2'] = "字型大小二";
$lang['fontsize3'] = "字型大小三";
$lang['fontcolor1'] = "字型颜色一";
$lang['fontcolor2'] = "字型颜色二";
$lang['fontcolor3'] = "字型颜色三";
$lang['span_class1'] = "Span 属性类别一";
$lang['span_class2'] = "Span 属性类别二";
$lang['span_class3'] = "Span 属性类别三";
$lang['img_poll_size'] = "投票统计量图示大小 [px]";
$lang['img_pm_size'] = "个人消息使用量图示大小 [px]";


//
// Install Process
//
$lang['Welcome_install'] = "欢迎安装 phpBB 2 论坛系统";
$lang['Initial_config'] = "基本设定";
$lang['DB_config'] = "数据库设定";
$lang['Admin_config'] = "系统管理员设定";
$lang['continue_upgrade'] = "在您下载完系统设定文件 (config.php) 之後, 您可以按下 '继续升级' 的按钮继续下一步. 请在所有升级程序完成后再上传设定档.";
$lang['upgrade_submit'] = "继续升级";

$lang['Installer_Error'] = "安装过程中发生错误";
$lang['Previous_Install'] = "您已完成安装程序";
$lang['Install_db_error'] = "在更新数据库时发生错误";

$lang['Re_install'] = "您先前安装的 phpBB 2 论坛系统仍在使用中. <br /><br />如果您希望重新安装 phpBB 2 论坛系统请选择 '是' 的按钮.  请注意, 执行后将会移除所有的现存资料, 而且不会有任何备份! 系统管理员帐号及密码将被重新建立, 所有设定也将不会被保留. <br /><br />请在您按下 '是' 的按钮前谨慎考虑!";
$lang['Inst_Step_0'] = "感谢您选择 phpBB 2 论坛系统. 您必须填写下列资料以完成安装程序. 在安装前, 请先确定您所要使用的数据库已经建立.";

$lang['Start_Install'] = "开始安装";
$lang['Finish_Install'] = "完成安装";

$lang['Default_lang'] = "预设论坛语言";
$lang['DB_Host'] = "数据库服务器主机名称";
$lang['DB_Name'] = "您的数据库名称";
$lang['DB_Username'] = "数据库用户帐号";
$lang['DB_Password'] = "数据库密码";
$lang['Database'] = "您的数据库";
$lang['Install_lang'] = "选择要安装的语言";
$lang['dbms'] = "数据库格式";
$lang['Table_Prefix'] = "数据库的表格字首 (Prefix)";
$lang['Admin_Username'] = "系统管理员帐号名称";
$lang['Admin_Password'] = "系统管理员密码";
$lang['Admin_Password_confirm'] = "系统管理员密码 [ 确认 ]";

$lang['Inst_Step_2'] = "您的系统管理员帐号已被建立, 论坛的基本安装已经完成, 稍后您将抵达论坛的管理页面.  请确认您已检查基本配置的设定并做适当的修改. 再一次感谢您选择使用 phpBB 2 论坛系统.";

$lang['Unwriteable_config'] = "您的系统设定档无法写入, 您可以点击下方按钮下载设定文件, 再将这个文件上传至 phpBB 2 论坛的资料夹. 在完成后您必须使用管理员帐号跟密码登陆并进入系统管理控制面板 (在您登陆后, 下方将出现一个进入\"系统管理控制面板\"的链接) 检查您的基本配置设定. 最后感谢您选择使用安装 phpBB 2 论坛系统.";
$lang['Download_config'] = "下载设定文件";

$lang['ftp_choose'] = "选择下载方式";
$lang['ftp_option'] = "<br />在 FTP 设定完成后, 您可以使用自动上传的功能.";
$lang['ftp_instructs'] = "您已经选择使用 FTP 去自动安装您的 phpBB 2 论坛.  请输入下列资料来简化这个过程. 请注意: FTP 路径须跟您安装 phpBB 2 的 FTP 路径完全相同.";
$lang['ftp_info'] = "输入您的 FTP 信息";
$lang['Attempt_ftp'] = "使用 FTP 上传设定文件:";
$lang['Send_file'] = "自行上传设定文件";
$lang['ftp_path'] = "安装 phpBB 2 的 FTP 路径:";
$lang['ftp_username'] = "您的 FTP 登陆名称:";
$lang['ftp_password'] = "您的 FTP 登陆密码:";
$lang['Transfer_config'] = "开始传输";
$lang['NoFTP_config'] = "FTP 上传设定文件失败. 请下载设定文件并使用手动上传.";

$lang['Install'] = "完整安装";
$lang['Upgrade'] = "系统升级";

$lang['Install_Method'] = '请选择安装模式';

$lang['Install_No_Ext'] = "您服务器上的php配置不支持您所选择的数据库类型";

$lang['Install_No_PCRE'] = "您的php配置不支持安装phpBB2所需要的Perl语言标准表达模式的兼容性";



//
// That's all Folks!
// -------------------------------------------------

?>