<?php

/***************************************************************************
 *                            lang_admin.php [English]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_admin.php,v 1.35.2.2 2002/05/12 15:33:28 psotfx Exp $
 *
 ****************************************************************************/

/***************************************************************************
 *                            Traditional Chinese Translation [繁體中文語系]
 *                              -------------------
 *     begin                : Thu Nov 26 2001
 *     by                   : 小竹子, OOHOO, 皇家騎士, 思
 *     email                : kyo.yoshika@msa.hinet.net
 *                            webdev@hotmail.com
 *                            sjwu1@ms12.hinet.net
 *                            f8806077@mail.dyu.edu.tw
 *
 *     last modify          : Sun Jun 9 2002
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
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = '一般管理';
$lang['Users'] = '會員管理';
$lang['Groups'] = '群組管理';
$lang['Forums'] = '版面管理';
$lang['Styles'] = '風格管理';

$lang['Configuration'] = '基本組態';
$lang['Permissions'] = '權限設定';
$lang['Manage'] = '管理選項';
$lang['Disallow'] = '禁用帳號';
$lang['Prune'] = '快速刪文';
$lang['Mass_Email'] = '電子郵件通知';
$lang['Ranks'] = '等級管理';
$lang['Smilies'] = '表情符號';
$lang['Ban_Management'] = '封鎖控制';
$lang['Word_Censor'] = '文字過濾';
$lang['Export'] = '輸出';
$lang['Create_new'] = '建立';
$lang['Add_new'] = '新增';
$lang['Backup_DB'] = '備份資料庫';
$lang['Restore_DB'] = '還原資料庫';


//
// Index
//
$lang['Admin'] = '系統管理';
$lang['Not_admin'] = '您未授權進入系統管理控制台';
$lang['Welcome_phpBB'] = '歡迎光臨 phpBB 2 系統管理控制台';
$lang['Admin_intro'] = '感謝您選擇 phpBB 2 作為您的討論區系統. 在這個版面裡, 您可以透過一些統計資料快速的檢視您的討論區系統. 您可以藉由點選控制台左方的<u>控制台首頁</u>連結回到這一頁. 要回到您的討論區首頁, 請點選在控制台左上方的 phpBB 標誌圖示. 在這個畫面左方的其他連結, 將允許您控制討論區系統的所有管理選項, 而每個版面裡也會有各項功能的使用解說.';
$lang['Main_index'] = '討論區首頁';
$lang['Forum_stats'] = '討論區統計資料';
$lang['Admin_Index'] = '控制台首頁';
$lang['Preview_forum'] = '預覽討論區';

$lang['Click_return_admin_index'] = '點選 %s這裡%s 回到控制台首頁';

$lang['Statistic'] = '統計資料';
$lang['Value'] = '數值';
$lang['Number_posts'] = '文章總數';
$lang['Posts_per_day'] = '平均每天發表的文章總數';
$lang['Number_topics'] = '主題總數';
$lang['Topics_per_day'] = '平均每天發表的主題總數';
$lang['Number_users'] = '註冊會員總數';
$lang['Users_per_day'] = '平均每天註冊的會員總數';
$lang['Board_started'] = '討論區啟用日期';
$lang['Avatar_dir_size'] = '頭像資料夾檔案大小';
$lang['Database_size'] = '資料庫檔案大小';
$lang['Gzip_compression'] ='Gzip compression';
$lang['Not_available'] = '無';

$lang['ON'] = '開啟'; // This is for GZip compression
$lang['OFF'] = '關閉'; 


//
// DB Utils
//
$lang['Database_Utilities'] = '資料庫工具管理';

$lang['Restore'] = '還原';
$lang['Backup'] = '備份';
$lang['Restore_explain'] = '在這個選項中, 您可以使用備份的檔案, 完整地還原 phpBB 2 所使用的資料庫表格. 如果您的伺服器支援 GZIP 壓縮的文字檔, 系統將會自行解壓您所上傳的壓縮檔. <b>警告</b> 還原動作將會完全覆蓋所有現存的資料. 系統還原動作可能會花費一段時間去完成, 直到系統完成前請不要離開這個頁面.';
$lang['Backup_explain'] = '在這個選項中, 您可以備份所有 phpBB 2 討論區的相關資料. 如果您有其它自行定義的表格放在 phpBB 2 討論區所使用的資料庫內, 而且您也想一併備份這些額外的表格, 請在下方的 <b>附加的表格</b> 欄內輸入他們的名字並用逗號區別開 (例如: abc, cde). 假如您的伺服器有支援 GZIP 壓縮格式, 您可以在下載前使用 GZIP 壓縮來減少檔案的大小.';

$lang['Backup_options'] = '備份選項';
$lang['Start_backup'] = '開始備份';
$lang['Full_backup'] = '完整備份';
$lang['Structure_backup'] = '只有備份架構';
$lang['Data_backup'] = '只有備份資料';
$lang['Additional_tables'] = '附加的表格';
$lang['Gzip_compress'] = 'Gzip 壓縮檔案';
$lang['Select_file'] = '選擇檔案';
$lang['Start_Restore'] = '開始還原';

$lang['Restore_success'] = '資料庫已經順利的被系統還原.<br /><br />討論區已被還原至備份時的狀態.';
$lang['Backup_download'] = '請耐心等待. 您的備份檔案下載要求即將開始!';
$lang['Backups_not_supported'] = '抱歉! 您所執行的系統還原動作沒有正確的被執行';

$lang['Restore_Error_uploading'] = '上傳的備份檔案錯誤';
$lang['Restore_Error_filename'] = '檔案名稱有問題, 請重新選取檔案';
$lang['Restore_Error_decompress'] = '無法解壓 Gzip 檔案, 請以純文字模式上傳';
$lang['Restore_Error_no_file'] = '沒有上傳的檔案';


//
// Auth pages
//
$lang['Select_a_User'] = '選擇一個使用者';
$lang['Select_a_Group'] = '選擇一個群組';
$lang['Select_a_Forum'] = '選擇一個版面';
$lang['Auth_Control_User'] = '會員權限設定'; 
$lang['Auth_Control_Group'] = '群組權限設定'; 
$lang['Auth_Control_Forum'] = '版面權限設定'; 
$lang['Look_up_User'] = '查詢會員'; 
$lang['Look_up_Group'] = '查詢群組'; 
$lang['Look_up_Forum'] = '查詢版面'; 

$lang['Group_auth_explain'] = '在這個選項中, 您可以更改群組的權限設定及指定管理員資格. 請注意, 即使修改群組權限設定, 會員可能仍然擁有進入限制版面的會員權限. 如果發生上述情形, 系統會顯示權限衝突的警告.';
$lang['User_auth_explain'] = '在這個選項中, 您可以更改會員的權限設定及指定管理員資格. 請注意, 即使修改會員權限設定, 會員可能仍然擁有進入限制版面的群組權限. 如果發生上述情形, 系統會顯示權限衝突的警告.';
$lang['Forum_auth_explain'] = '在這個選項中, 您可以更改版面的使用權限設定. 您可以選擇使用簡易或是進階模式設定, 進階模式能提供您完整的權限設定控制. 請記得所有的改變都將會影響到會員們的版面使用權限.';

$lang['Simple_mode'] = '簡易模式';
$lang['Advanced_mode'] = '進階模式';
$lang['Moderator_status'] = '管理員資格';

$lang['Allowed_Access'] = '允許進入';
$lang['Disallowed_Access'] = '禁止進入';
$lang['Is_Moderator'] = '具版面管理員資格';
$lang['Not_Moderator'] = '不具版面管理員資格';

$lang['Conflict_warning'] = '權限衝突警告';
$lang['Conflict_access_userauth'] = '這個會員仍然可以透過群組成員的資格進入特定的版面. 您可以藉由變更群組權限或是移除這個會員的群組資格, 來防止該會員進入限制的版面.';
$lang['Conflict_mod_userauth'] = '這個會員仍然可以透過群組成員的資格擁有版面管理的權限. 您可以藉由變更群組權限或是移除這個會員的權限, 來防止該會員進入限制的版面.';

$lang['Conflict_access_groupauth'] = '下列會員仍然可以透過他們的會員權限進入特定的版面. 您可以更改會員權限來防止他們進入限制的版面. 這些會員的權限如下: ';
$lang['Conflict_mod_groupauth'] = '下列會員依然可以透過他們的會員權限擁有版面管理的權限. 您可以更改會員權限來除去他們的版面管理權限. 這些會員的權限如下: ';

$lang['Public'] = '公開';
$lang['Private'] = '非公開';
$lang['Registered'] = '註冊會員';
$lang['Administrators'] = '系統管理員';
$lang['Hidden'] = '隱藏';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = '所有會員';
$lang['Forum_REG'] = '註冊會員';
$lang['Forum_PRIVATE'] = '群組成員';
$lang['Forum_MOD'] = '版面管理員';
$lang['Forum_ADMIN'] = '系統管理員';

$lang['View'] = '檢視';
$lang['Read'] = '閱讀';
$lang['Post'] = '發表';
$lang['Reply'] = '回覆';
$lang['Edit'] = '編輯';
$lang['Delete'] = '刪除';
$lang['Sticky'] = '置頂';
$lang['Announce'] = '公告'; 
$lang['Vote'] = '投票';
$lang['Pollcreate'] = '票選活動';

$lang['Permissions'] = '權限設定';
$lang['Simple_Permission'] = '基本權限';

$lang['User_Level'] = '會員等級'; 
$lang['Auth_User'] = '會員';
$lang['Auth_Admin'] = '系統管理員';
$lang['Group_memberships'] = '會員群組清單';
$lang['Usergroup_members'] = '這個群組擁有以下成員';

$lang['Forum_auth_updated'] = '版面權限設定完成';
$lang['User_auth_updated'] = '會員權限設定完成';
$lang['Group_auth_updated'] = '群組權限設定完成';

$lang['Auth_updated'] = '權限設定已經完成更新';
$lang['Click_return_userauth'] = '點選 %s這裡%s 返回會員權限設定';
$lang['Click_return_groupauth'] = '點選 %s這裡%s 返回群組權限設定';
$lang['Click_return_forumauth'] = '點選 %s這裡%s 返回版面權限設定';


//
// Banning
//
$lang['Ban_control'] = '封鎖控制';
$lang['Ban_explain'] = '在這個選項中, 您可以控制會員的封鎖. 您可以封鎖一個指定範圍的 IP 位址或是電腦主機名稱, 這些方法都是用來避免被封鎖的會員進入討論區首頁. 您也可以指定封鎖電子郵件位址來防止註冊會員使用不同的帳號重複註冊. 請注意當您封鎖一個電子郵件位址時, 將不影響到會員在您討論區的登入或是發表動作, 您應該使用前面兩種方式其中之一來建立封鎖.';
$lang['Ban_explain_warn'] = '當您輸入一個範圍的 IP 位址, 將會造成整個區段的 IP 位址被封鎖. 試著使用萬用字元 (*) 來縮短電子郵件位址以避免佔用資料庫空間. 假如您一定要輸入一個範圍, 請保持精簡或是適當的位址狀態.';

$lang['Select_username'] = '選擇會員名稱';
$lang['Select_ip'] = '選擇 IP 位址';
$lang['Select_email'] = '選擇電子郵件位址';

$lang['Ban_username'] = '封鎖一個或多個指定的會員名稱';
$lang['Ban_username_explain'] = '您可以使用滑鼠及按鍵組合 (例如: Ctrl 或 Shift), 一次封鎖多個會員名稱';

$lang['Ban_IP'] = '封鎖一個或多個 IP 位址或是電腦主機名稱';
$lang['IP_hostname'] = 'IP 位址或是電腦主機名稱';
$lang['Ban_IP_explain'] = '要指定多個不同的 IP 位址或是主機名稱, 請使用逗點 \',\' 來區隔它們. 要指定 IP 位址的範圍, 請使用 \'-\' 來區隔起始位址及結束位址, 或是使用萬用字元 \'*\'';

$lang['Ban_email'] = '封鎖一個或多個電子郵件位址';
$lang['Ban_email_explain'] = '要指定多個不同的電子郵件位址, 請使用逗點 \',\' 來區隔它們, 或是使用萬用字元 \'*\', 例如: *@hotmail.com';

$lang['Unban_username'] = '解除一個或多個封鎖的會員名稱';
$lang['Unban_username_explain'] = '您可以使用滑鼠及按鍵組合 (例如: Ctrl 或 Shift), 一次解除多個封鎖的會員名稱';

$lang['Unban_IP'] = '解除一個或多個封鎖的 IP 位址';
$lang['Unban_IP_explain'] = '您可以使用滑鼠及按鍵組合 (例如: Ctrl 或 Shift), 一次解除多個封鎖的 IP 位址';

$lang['Unban_email'] = '解除一個或多個封鎖的電子郵件位址';
$lang['Unban_email_explain'] = '您可以使用滑鼠及按鍵組合 (例如: Ctrl 或 Shift), 一次解除多個封鎖的電子郵件位址';

$lang['No_banned_users'] = '沒有被封鎖的會員名稱';
$lang['No_banned_ip'] = '沒有被封鎖的 IP 位址';
$lang['No_banned_email'] = '沒有被封鎖的電子郵件位址';

$lang['Ban_update_sucessful'] = '封鎖清單已經完成更新';
$lang['Click_return_banadmin'] = '點選 %s這裡%s 返回封鎖控制';


//
// Configuration
//
$lang['General_Config'] = '基本組態';
$lang['Config_explain'] = '您可以使用下列表格來調整一般的設定選項. 會員及版面設定請使用畫面左方 (系統管理) 的相關連結.';

$lang['Click_return_config'] = '點選 %s這裡%s 返回基本組態';

$lang['General_settings'] = '討論區基本設定';
$lang['Server_name'] = '網域名稱';
$lang['Server_name_explain'] = '討論區使用網域';
$lang['Script_path'] = '系統程式存放路徑';
$lang['Script_path_explain'] = '討論區對應網域的路徑';
$lang['Server_port'] = '主機連接埠';
$lang['Server_port_explain'] = '主機通常使用 80 來作為連接埠, 除非您使用不同的連接埠, 否則這項設定是不需更改的';
$lang['Site_name'] = '討論區名稱';
$lang['Site_desc'] = '討論區描述';
$lang['Board_disable'] = '暫時關閉';
$lang['Board_disable_explain'] = '這個動作將會暫時關閉討論區. 當您執行這個動作時, 請勿登出, 因為您將無法重新登入!';
$lang['Acct_activation'] = '帳號啟用動作';
$lang['Acc_None'] = '關閉'; // These three entries are the type of activation
$lang['Acc_User'] = '由會員自行啟用';
$lang['Acc_Admin'] = '由系統管理員開啟';

$lang['Abilities_settings'] = '會員及版面基本設定';
$lang['Max_poll_options'] = '票選項目的最高限制數目';
$lang['Flood_Interval'] = '灌水機制';
$lang['Flood_Interval_explain'] = '文章發表的間隔時間 (秒)'; 
$lang['Board_email_form'] = '會員聯絡簿';
$lang['Board_email_form_explain'] = '會員可以發送電子郵件給討論區的其他會員';
$lang['Topics_per_page'] = '每頁顯示主題數';
$lang['Posts_per_page'] = '每頁顯示發表數';
$lang['Hot_threshold'] = '熱門話題顯示數';
$lang['Default_style'] = '預設樣式';
$lang['Override_style'] = '推翻會員選擇樣式';
$lang['Override_style_explain'] = '將會員所選的樣式改為預設樣式';
$lang['Default_language'] = '預設語系';
$lang['Date_format'] = '時間格式';
$lang['System_timezone'] = '系統時間';
$lang['Enable_gzip'] = '開啟 GZip 檔案壓縮格式';
$lang['Enable_prune'] = '開啟版面刪文模式';
$lang['Allow_HTML'] = '允許使用 HTML 語法';
$lang['Allow_BBCode'] = '允許使用 BBCode 代碼';
$lang['Allowed_tags'] = '允許使用的 HTML 標籤';
$lang['Allowed_tags_explain'] = '以逗點區隔 HTML 標籤';
$lang['Allow_smilies'] = '允許使用表情符號';
$lang['Smilies_path'] = '表情符號儲存路徑';
$lang['Smilies_path_explain'] = '在您 phpBB 2 根目錄底下的路徑, 例如: images/smilies';
$lang['Allow_sig'] = '允許簽名檔';
$lang['Max_sig_length'] = '簽名檔長度';
$lang['Max_sig_length_explain'] = '使用者個性簽名最多可使用字數';
$lang['Allow_name_change'] = '允許更改登入名稱';

$lang['Avatar_settings'] = '個人頭像設定';
$lang['Allow_local'] = '使用系統相簿';
$lang['Allow_remote'] = '允許連結頭像';
$lang['Allow_remote_explain'] = '從外部網址連結個人頭像';
$lang['Allow_upload'] = '允許上傳頭像';
$lang['Max_filesize'] = '頭像檔案不可超過';
$lang['Max_filesize_explain'] = '由使用者上傳頭像檔案';
$lang['Max_avatar_size'] = '頭像尺寸不可大於';
$lang['Max_avatar_size_explain'] = '(高 x 寬 像素單位)';
$lang['Avatar_storage_path'] = '個人頭像儲存路徑';
$lang['Avatar_storage_path_explain'] = '在您 phpBB 2 根目錄底下的路徑, 例如: images/avatars';
$lang['Avatar_gallery_path'] = '系統相簿儲存路徑';
$lang['Avatar_gallery_path_explain'] = '在您 phpBB 2 根目錄底下的路徑, 例如: images/avatars/gallery';

$lang['COPPA_settings'] = 'COPPA (美國兒童網路隱私保護法) 設定';
$lang['COPPA_fax'] = 'COPPA 傳真號碼';
$lang['COPPA_mail'] = 'COPPA 郵遞地址';
$lang['COPPA_mail_explain'] = '這是供家長寄送 COPPA 會員註冊申請書的郵遞地址';

$lang['Email_settings'] = '電子郵件設定';
$lang['Admin_email'] = '系統管理員電子郵件信箱';
$lang['Email_sig'] = '電子郵件簽名檔';
$lang['Email_sig_explain'] = '這個簽名檔將會被附加在所有由討論區系統送出的電子郵件中';
$lang['Use_SMTP'] = '使用 SMTP 伺服器傳送電子郵件';
$lang['Use_SMTP_explain'] = '假如您想要使用 SMTP 伺服器發送電子郵件請選擇 \'是\'';
$lang['SMTP_server'] = 'SMTP 伺服器網域名稱';
$lang['SMTP_username'] = 'SMTP 使用者帳號';
$lang['SMTP_username_explain'] = '只有在主機有要求的情況下才需要輸入';
$lang['SMTP_password'] = 'SMTP 密碼';
$lang['SMTP_password_explain'] = '只有在主機有要求的情況下才需要輸入';

$lang['Disable_privmsg'] = '私人訊息';
$lang['Inbox_limits'] = '收件夾最大容量';
$lang['Sentbox_limits'] = '寄件夾最大容量';
$lang['Savebox_limits'] = '儲存夾最大容量';

$lang['Cookie_settings'] = 'Cookie 設定'; 
$lang['Cookie_settings_explain'] = '這些設定控制著 Cookie 的定義, 就一般的情況, 使用系統預設值就可以了. 如果您要更改這些設定, 請小心處理, 不當的設定將導致會員需重複登入';
$lang['Cookie_domain'] = 'Cookie 指定網域 [ 可讀取 Cookie 資料的網域 ]';
$lang['Cookie_name'] = 'Cookie 名稱';
$lang['Cookie_path'] = 'Cookie 路徑';
$lang['Cookie_secure'] = 'Cookie 加密 [ https ]';
$lang['Cookie_secure_explain'] = '如果您的主機使用 SSL 通訊協定, 請開啟這項設定, 否則請保持關閉的狀態';
$lang['Session_length'] = 'Session 存活時間 [ 秒 ]';


//
// Forum Management
//
$lang['Forum_admin'] = '版面管理';
$lang['Forum_admin_explain'] = '在這個控制面板裡, 您可以新增, 刪除, 編輯及重新排列分區和版面, 以及重整版面內的對應資料.';
$lang['Edit_forum'] = '編輯版面';
$lang['Create_forum'] = '建立新版面';
$lang['Create_category'] = '建立新分區';
$lang['Remove'] = '移除';
$lang['Action'] = '執行';
$lang['Update_order'] = '更新舊有';
$lang['Config_updated'] = '基本組態已經完成更新';
$lang['Edit'] = '編輯';
$lang['Delete'] = '刪除';
$lang['Move_up'] = '往上移動';
$lang['Move_down'] = '往下移動';
$lang['Resync'] = '重整對應資料';
$lang['No_mode'] = '沒有設定模式';
$lang['Forum_edit_delete_explain'] = '您可以使用下列表格來調整一般的設定選項. 會員及版面設定請使用畫面左方 (系統管理) 的相關連結.';

$lang['Move_contents'] = '移動/刪除所有內容';
$lang['Forum_delete'] = '刪除版面';
$lang['Forum_delete_explain'] = '您可以使用下列表格來刪除版面 (或分區), 並可移動包含在版面內的所有文章.';

$lang['Forum_settings'] = '版面基本設定';
$lang['Forum_name'] = '版面名稱';
$lang['Forum_desc'] = '版面描述';
$lang['Forum_status'] = '版面狀態';
$lang['Forum_pruning'] = '定期刪文';

$lang['prune_freq'] = '定期 (每隔幾天) 檢查主題狀態';
$lang['prune_days'] = '刪除 (在幾天內) 沒有文章回覆的主題';
$lang['Set_prune_data'] = '您已經開啟版面定期刪文的功能, 但並未完成相關設定. 請回到上一步設定相關的項目';

$lang['Move_and_Delete'] = '移動/刪除';

$lang['Delete_all_posts'] = '刪除所有文章';
$lang['Nowhere_to_move'] = '無法移動';

$lang['Edit_Category'] = '編輯分區名稱';
$lang['Edit_Category_explain'] = '使用以下表格修改分區名稱';

$lang['Forums_updated'] = '版面及分區資料已經完成更新';

$lang['Must_delete_forums'] = '在刪除這個分區之前, 您必須先刪除分區底下的所有版面';

$lang['Click_return_forumadmin'] = '點選 %s這裡%s 返回版面管理';


//
// Smiley Management
//
$lang['smiley_title'] = '表情符號編輯';
$lang['smile_desc'] = '在這個選項中, 您可以新增, 刪除或是編輯表情符號和笑臉包包, 以供會員在文章發表或是私人訊息中使用.';

$lang['smiley_config'] = '表情符號設定';
$lang['smiley_code'] = '表情符號代碼';
$lang['smiley_url'] = '表情圖檔';
$lang['smiley_emot'] = '表情情緒';
$lang['smile_add'] = '增加一個新表情';
$lang['Smile'] = '表情';
$lang['Emotion'] = '代表情緒';

$lang['Select_pak'] = '選擇的笑臉包包 (.pak) 檔案';
$lang['replace_existing'] = '替換現有的表情符號';
$lang['keep_existing'] = '保留現有的表情符號';
$lang['smiley_import_inst'] = '您應將笑臉包包解壓並上傳至適當的表情符號目錄.  然後選擇正確的項目載入那個笑臉包包.';
$lang['smiley_import'] = '載入笑臉包包 ';
$lang['choose_smile_pak'] = '選擇一個笑臉包包 .pak 檔案';
$lang['import'] = '載入表情符號';
$lang['smile_conflicts'] = '在衝突的情況下所應做出的決定';
$lang['del_existing_smileys'] = '載入前先刪除舊的表情符號';
$lang['import_smile_pack'] = '載入笑臉包包';
$lang['export_smile_pack'] = '建立笑臉包包';
$lang['export_smiles'] = '如您希望將現有的表情符號製作成笑臉包包, 請點選 %s這裡%s 下載 smiles.pak 檔案, 並確定其副檔名為.pak.';

$lang['smiley_add_success'] = '新的表情符號已經成功加入';
$lang['smiley_edit_success'] = '表情符號已經完成更新';
$lang['smiley_import_success'] = '笑臉包包已被順利載入!';
$lang['smiley_del_success'] = '表情符號已被順利移除';
$lang['Click_return_smileadmin'] = '點選 %s這裡%s 返回表情符號編輯';


//
// User Management
//
$lang['User_admin'] = '會員管理';
$lang['User_admin_explain'] = '在這個控制面板裡, 您可以變更會員的個人資料以及特殊選項. 假如您要修改會員的權限, 請使用會員及群組管理的權限設定功能.';

$lang['Look_up_user'] = '查詢會員';

$lang['Admin_user_fail'] = '無法更新會員的個人資料';
$lang['Admin_user_updated'] = '會員的個人資料已經完成更新';
$lang['Click_return_useradmin'] = '點選 %s這裡%s 返回會員管理';

$lang['User_delete'] = '刪除會員';
$lang['User_delete_explain'] = '勾選這裡將會刪除會員, 這個動作將無法還原';
$lang['User_deleted'] = '會員被順利刪除.';

$lang['User_status'] = '會員帳號已啟用';
$lang['User_allowpm'] = '允許使用私人訊息';
$lang['User_allowavatar'] = '允許使用個人頭像';

$lang['Admin_avatar_explain'] = '您可以刪除會員的個人頭像';

$lang['User_special'] = '管理員專區';
$lang['User_special_explain'] = '您可以變更會員的帳號啟用狀態及其它未授權會員的選項設定, 一般會員無法自行變更這些設定';


//
// Group Management
//
$lang['Group_administration'] = '群組管理';
$lang['Group_admin_explain'] = '在這個控制面板裡, 您可以管理所有的會員群組, 您可以建立, 刪除以及編輯現有的會員群組. 您還可以指定群組組長, 設定群組模式 (開放/封閉/隱藏) 以及群組的命名和描述.';
$lang['Error_updating_groups'] = '群組更新時發生錯誤';
$lang['Updated_group'] = '群組已經完成更新';
$lang['Added_new_group'] = '新的群組已經成功加入';
$lang['Deleted_group'] = '群組已被順利刪除';
$lang['New_group'] = '建立新群組';
$lang['Edit_group'] = '編輯群組';
$lang['group_name'] = '群組名稱';
$lang['group_description'] = '群組描述';
$lang['group_moderator'] = '群組組長';
$lang['group_status'] = '群組模式';
$lang['group_open'] = '開放群組';
$lang['group_closed'] = '封閉群組';
$lang['group_hidden'] = '隱藏群組';
$lang['group_delete'] = '刪除群組';
$lang['group_delete_check'] = '刪除這個群組';
$lang['submit_group_changes'] = '送出更新';
$lang['reset_group_changes'] = '清除重設';
$lang['No_group_name'] = '您必許指定一個名稱給這個群組';
$lang['No_group_moderator'] = '您必許指定群組的組長';
$lang['No_group_mode'] = '您必須指定群組模式 (開放/封閉/隱藏)';
$lang['No_group_action'] = '沒有指定的動作';
$lang['delete_group_moderator'] = '刪除原有的群組組長?';
$lang['delete_moderator_explain'] = '如果您變更了群組組長, 勾選這個選項會將原有的群組組長從群組中移除, 否則, 請不要勾選, 這個會員將降級為群組的普通成員.';
$lang['Click_return_groupsadmin'] = '點選 %s這裡%s 返回群組管理.';
$lang['Select_group'] = '選擇群組';
$lang['Look_up_group'] = '查詢群組';


//
// Prune Administration
//
$lang['Forum_Prune'] = '版面快速刪文';
$lang['Forum_Prune_explain'] = '這個動作將刪除所有在限定時間內沒有回覆的主題. 如果您沒有指定時限 (日數), 所有的主題都將會被刪除. 但是無法刪除正在進行中的投票主題或是公告. 您必須手動移除這些主題.';
$lang['Do_Prune'] = '執行刪除';
$lang['All_Forums'] = '所有版面';
$lang['Prune_topics_not_posted'] = '刪除在幾天內沒有文章回覆的主題';
$lang['Topics_pruned'] = '主題刪除';
$lang['Posts_pruned'] = '文章刪除';
$lang['Prune_success'] = '完成版面文章刪除';


//
// Word censor
//
$lang['Words_title'] = '文字過濾';
$lang['Words_explain'] = '在這個控制面板裡, 您可以建立, 編輯及刪除過濾文字, 這些指定的文字將會被過濾並以替換文字顯示. 此外, 會員也將無法使用含有這些限定文字的名稱來註冊. 限定的名稱允許使用萬用字元 (*), 例如: *test*  包括 detestable, test* 包括 testing, *test 包括 detest.';
$lang['Word'] = '過濾文字';
$lang['Edit_word_censor'] = '編輯過濾文字';
$lang['Replacement'] = '替換文字';
$lang['Add_new_word'] = '新增過濾文字';
$lang['Update_word'] = '更新過濾文字';

$lang['Must_enter_word'] = '您必須輸入需要過濾的文字及其替換文字';
$lang['No_word_selected'] = '您沒有選擇要編輯的過濾文字';

$lang['Word_updated'] = '您所選擇的過濾文字已經完成更新';
$lang['Word_added'] = '新的過濾文字已經成功加入';
$lang['Word_removed'] = '您所選擇的過濾文字已被順利移除';

$lang['Click_return_wordadmin'] = '點選 %s這裡%s 返回文字過濾';


//
// Mass Email
//
$lang['Mass_email_explain'] = '在這個選項中, 您可以發送電子郵件訊息給所有的會員或是特定的群組. 這封電子郵件將被寄送至系統管理員的電子郵件信箱, 並以密件副本的方式寄送給所有收件人. 如果收件人數過多, 系統需要較長的時間來執行這個動作, 請在訊息送出之後耐心等候, <b>切勿</b>在程序完成之前停止網頁動作.';
$lang['Compose'] = '通知訊息'; 

$lang['Recipients'] = '收件人'; 
$lang['All_users'] = '所有會員';

$lang['Email_successfull'] = '通知訊息已經寄出';
$lang['Click_return_massemail'] = '點選 %s這裡%s 返回電子郵件通知';


//
// Ranks admin
//
$lang['Ranks_title'] = '等級管理';
$lang['Ranks_explain'] = '在這個控制面板裡, 您可以在新增, 編輯, 檢視以及刪除等級名稱. 這些等級將會被用於會員管理的功能.';

$lang['Add_new_rank'] = '加入新的等級';

$lang['Rank_title'] = '等級名稱';
$lang['Rank_special'] = '特殊等級';
$lang['Rank_minimum'] = '文章數量最少需求';
$lang['Rank_maximum'] = '文章數量最多需求';
$lang['Rank_image'] = '等級圖示';
$lang['Rank_image_explain'] = '使用這個欄位來定義等級圖示的路徑';

$lang['Must_select_rank'] = '您必須選擇一個等級名稱';
$lang['No_assigned_rank'] = '沒有指定的等級';

$lang['Rank_updated'] = '等級名稱已經完成更新';
$lang['Rank_added'] = '新的等級名稱已經成功加入';
$lang['Rank_removed'] = '等級名稱已被順利移除';
$lang['No_update_ranks'] = '等級名稱已經順利移除了, 但是原先使用這項等級的會員資料並未更新. 您必須重新設定這些會員的等級.';

$lang['Click_return_rankadmin'] = '點選 %s這裡%s 返回等級管理';


//
// Disallow Username Admin
//
$lang['Disallow_control'] = '禁用帳號控制';
$lang['Disallow_explain'] = '在這個選項中, 您可以控制禁用的會員帳號名稱 (可使用萬用字元 \'*\'). 請注意, 您無法禁用已經註冊使用的會員名稱, 您必須先刪除這個會員帳號, 才能使用禁用帳號的功能.';

$lang['Delete_disallow'] = '刪除';
$lang['Delete_disallow_title'] = '刪除禁用的帳號名稱';
$lang['Delete_disallow_explain'] = '您可以從清單中選取要移除的禁用帳號名稱';

$lang['Add_disallow'] = '新增';
$lang['Add_disallow_title'] = '新增禁用的帳號名稱';
$lang['Add_disallow_explain'] = '您可以使用萬用字元 \'*\'來禁用範圍較大的會員名稱';

$lang['No_disallowed'] = '沒有禁用的帳號名稱';

$lang['Disallowed_deleted'] = '您所選取的禁用帳號名稱已被順利移除';
$lang['Disallow_successful'] = '新的禁用帳號名稱已經成功加入';
$lang['Disallowed_already'] = '無法禁用您所輸入的帳號名稱. 該帳號名稱可能已在禁用清單內或已被註冊使用';

$lang['Click_return_disallowadmin'] = '點選 %s這裡%s 返回禁用帳號控制';


//
// Styles Admin
//
$lang['Styles_admin'] = '版面風格管理';
$lang['Styles_explain'] = '使用這個功能您可以增加, 移除及管理各種不同的版面風格 (範本及佈景主題) 提供會員選擇使用.';
$lang['Styles_addnew_explain'] = '以下清單包含所有可使用的佈景主題. 這份清單上的佈景主題均尚未安裝到 phpBB 2 的資料庫內. 要安裝新的佈景主題請直接按下右方的執行連結.';

$lang['Select_template'] = '選擇範本名稱';

$lang['Style'] = '風格';
$lang['Template'] = '範本';
$lang['Install'] = '完整安裝';
$lang['Download'] = '下載';

$lang['Edit_theme'] = '編輯佈景主題';
$lang['Edit_theme_explain'] = '您可以使用下列表格編輯佈景主題設定.';

$lang['Create_theme'] = '新增佈景主題';
$lang['Create_theme_explain'] = '您可以使用下列表格來為指定的範本增加新的佈景主題. 當設定顏色時 (您必須使用十六進位碼, 例如: FFFFFF) 您不能包含起始字元 #, 舉例如下.. CCCCCC 為正確的表示法, #CCCCCC 則是錯誤的.';

$lang['Export_themes'] = '輸出佈景主題';
$lang['Export_explain'] = '在這個版面裡, 您可以輸出指定範本的佈景主題資料. 由清單中選擇指定的範本後, 系統將會建立佈景主題的組態資料檔案並試圖儲存到指定的範本目錄. 如果資料無法儲存, 系統將允許您下載這個資料檔案. 如果您希望系統能直接儲存這些檔案資料, 您必須開放指定範本目錄的寫入權限. 如果您需要更多這方面的資訊, 請參考 phpBB 2 使用說明.';

$lang['Theme_installed'] = '指定的佈景主題已經安裝完成';
$lang['Style_removed'] = '指定的版面風格已從資料庫中移除. 要從您的系統中完全的移除這個版面風格, 您必須從 /templates 中移除對應的範本目錄';
$lang['Theme_info_saved'] = '指定的佈景主題資料已經成功儲存. 您必須立即修改 theme_info.cfg 成唯讀屬性 (如果適用於指定的範本目錄)';
$lang['Theme_updated'] = '指定的佈景主題已被更新. 您必須輸出新的佈景主題設定值';
$lang['Theme_created'] = '佈景主題已被建立. 您必須輸出佈景主題設定檔案, 以維持正常的操作及資料安全';

$lang['Confirm_delete_style'] = '您確定要刪除這個版面風格?';

$lang['Download_theme_cfg'] = '系統無法寫入佈景主題的設定檔案. 請按下按鈕由您的瀏覽器中下載這個檔案. 當您下載完這個檔案以後, 您即可將檔案移到包含此範本的目錄之下. 您可以重新包裝這個檔案用以分配或是其它您想要的處理方式';
$lang['No_themes'] = '您指定的範本並沒有包含任何的佈景主題. 要建立新的佈景主題, 請按下左方控制台的 \'建立\' 連結';
$lang['No_template_dir'] = '無法開啟範本目錄. 這有可能是因為此目錄設定為不可讀取的屬性或是檔案根本不存在';
$lang['Cannot_remove_style'] = '您無法移除預設的版面風格. 請先變更版面的預設風格後再重試一次';
$lang['Style_exists'] = '指定的版面風格名稱已經存在, 請回到上一步並選擇一個不同的名稱';

$lang['Click_return_styleadmin'] = '點選 %s這裡%s 返回版面風格管理';

$lang['Theme_settings'] = '佈景主題設定';
$lang['Theme_element'] = '佈景主題元件';
$lang['Simple_name'] = '簡易名稱';
$lang['Value'] = '數值';
$lang['Save_Settings'] = '儲存設定';

$lang['Stylesheet'] = 'CSS 樣式表';
$lang['Background_image'] = '背景圖案';
$lang['Background_color'] = '背景顏色';
$lang['Theme_name'] = '佈景主題名稱';
$lang['Link_color'] = '正常的連結顏色';
$lang['Text_color'] = '文字顏色';
$lang['VLink_color'] = '參觀過的連結顏色 (visited)';
$lang['ALink_color'] = '滑鼠按下的連結顏色 (active)';
$lang['HLink_color'] = '滑鼠移過的連結顏色 (hover)';
$lang['Tr_color1'] = '表格列顏色一';
$lang['Tr_color2'] = '表格列顏色二';
$lang['Tr_color3'] = '表格列顏色三';
$lang['Tr_class1'] = '表格列屬性類別一';
$lang['Tr_class2'] = '表格列屬性類別二';
$lang['Tr_class3'] = '表格列屬性類別三';
$lang['Th_color1'] = '項目標題顏色一';
$lang['Th_color2'] = '項目標題顏色二';
$lang['Th_color3'] = '項目標題顏色三';
$lang['Th_class1'] = '項目標題屬性類別一';
$lang['Th_class2'] = '項目標題屬性類別二';
$lang['Th_class3'] = '項目標題屬性類別三';
$lang['Td_color1'] = '資料格顏色一';
$lang['Td_color2'] = '資料格顏色二';
$lang['Td_color3'] = '資料格顏色三';
$lang['Td_class1'] = '資料格屬性類別一';
$lang['Td_class2'] = '資料格屬性類別二';
$lang['Td_class3'] = '資料格屬性類別三';
$lang['fontface1'] = '字型種類一';
$lang['fontface2'] = '字型種類二';
$lang['fontface3'] = '字型種類三';
$lang['fontsize1'] = '字型大小一';
$lang['fontsize2'] = '字型大小二';
$lang['fontsize3'] = '字型大小三';
$lang['fontcolor1'] = '字型顏色一';
$lang['fontcolor2'] = '字型顏色二';
$lang['fontcolor3'] = '字型顏色三';
$lang['span_class1'] = 'Span 屬性類別一';
$lang['span_class2'] = 'Span 屬性類別二';
$lang['span_class3'] = 'Span 屬性類別三';
$lang['img_poll_size'] = '票選統計量圖示大小 [px]';
$lang['img_pm_size'] = '私人訊息使用量圖示大小 [px]';


//
// Install Process
//
$lang['Welcome_install'] = '歡迎安裝 phpBB 2 討論區系統';
$lang['Initial_config'] = '基本設定';
$lang['DB_config'] = '資料庫設定';
$lang['Admin_config'] = '系統管理員設定';
$lang['continue_upgrade'] = '在您下載完系統設定檔 (config.php) 之後, 您可以按下 \'繼續升級\' 的按鈕繼續下一步. 請在所有升級程序完成後再上傳設定檔.';
$lang['upgrade_submit'] = '繼續升級';

$lang['Installer_Error'] = '安裝過程中發生錯誤';
$lang['Previous_Install'] = '您已完成安裝程序';
$lang['Install_db_error'] = '在嘗試更新資料庫時發生錯誤';

$lang['Re_install'] = '您先前安裝的 phpBB 2 討論區系統仍在使用中. <br /><br />如果您希望重新安裝 phpBB 2 討論區系統請選擇 \'是\' 的按鈕.  請注意, 執行這個動作將會移除所有的現存資料, 而且不會作任何的備份! 系統管理員帳號及密碼將被重新建立, 所有設定也將不會被保留. <br /><br />請在您按下 \'是\' 的按鈕前謹慎考慮!';

$lang['Inst_Step_0'] = '感謝您選擇 phpBB 2 討論區系統. 您必須填寫下列資料以完成安裝程序. 在安裝前, 請先確定您所要使用的資料庫已經建立.';

$lang['Start_Install'] = '開始安裝';
$lang['Finish_Install'] = '完成安裝';

$lang['Default_lang'] = '預設討論區語系';
$lang['DB_Host'] = '資料庫伺服器主機名稱';
$lang['DB_Name'] = '您的資料庫名稱';
$lang['DB_Username'] = '資料庫使用者帳號';
$lang['DB_Password'] = '資料庫密碼';
$lang['Database'] = '您的資料庫';
$lang['Install_lang'] = '選擇要安裝的語系';
$lang['dbms'] = '資料庫格式';
$lang['Table_Prefix'] = '資料庫的表格字首 (Prefix)';
$lang['Admin_Username'] = '系統管理員帳號名稱';
$lang['Admin_Password'] = '系統管理員密碼';
$lang['Admin_Password_confirm'] = '系統管理員密碼 [ 再確認 ]';

$lang['Inst_Step_2'] = '您的系統管理員帳號已被建立, 討論區的基本安裝已經完成, 稍後 您將被指引至討論區的管理頁面.  請確認您已檢查基本組態的設定並做適當的修改. 再一次感謝您選擇使用 phpBB 2 討論區.';

$lang['Unwriteable_config'] = '您的系統設定檔無法寫入, 您可以利用下方按鈕下載設定檔, 再將這個檔案上傳至 phpBB 2 討論區的資料夾. 在完成這些動作之後您必須使用系統管理員帳號跟密碼登入並進入系統管理控制台 (在您登入後, 下方將出現一個進入\"系統管理控制台\"的連結) 檢查您的基本組態設定. 最後感謝您選擇使用安裝 phpBB 2 討論區系統.';
$lang['Download_config'] = '下載設定檔';

$lang['ftp_choose'] = '選擇下載方式';
$lang['ftp_option'] = '<br />在 FTP 設定完成後, 您可以嘗試自動上傳的功能.';
$lang['ftp_instructs'] = '您已經選擇嘗試使用 FTP 去自動安裝您的 phpBB 2 討論區.  請輸入下列資料來簡化這個過程. 請注意: FTP 路徑須跟您安裝 phpBB 2 的 FTP 路徑完全相同.';
$lang['ftp_info'] = '輸入您的 FTP 資訊';
$lang['Attempt_ftp'] = '嘗試使用 FTP 上傳設定檔:';
$lang['Send_file'] = '自行上傳設定檔';
$lang['ftp_path'] = '安裝 phpBB 2 的 FTP 路徑:';
$lang['ftp_username'] = '您的 FTP 登入名稱:';
$lang['ftp_password'] = '您的 FTP 登入密碼:';
$lang['Transfer_config'] = '開始傳輸';
$lang['NoFTP_config'] = 'FTP 上傳設定檔失敗. 請下載設定檔並嘗試手動上傳.';

$lang['Install'] = '完整安裝';
$lang['Upgrade'] = '系統升級';


$lang['Install_Method'] = '請選擇安裝模式';

$lang['Install_No_Ext'] = '您主機上的 PHP 設定並不支援您所選擇的資料庫型態';

$lang['Install_No_PCRE'] = 'phpBB2 需要使用到 Perl-Compatible Regular Expressions Module, 而您的 PHP 設定並不支援這項功能';

//
// That's all Folks!
// -------------------------------------------------

?>