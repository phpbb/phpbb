<?php
/***************************************************************************
 *                            lang_admin.php [Bulgarian]
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

/*************************************************************************** 
*                     Bulgarian translation (Български превод)
*                              ------------------- 
*     begin                : Thu Dec 06 2001
*     last update          : Fri Jan 11 2001  
*     by                   : Boby Dimitrov (Боби Димитров) 
*     email                : boby@azholding.com 
****************************************************************************/ 


//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "Системни";
$lang['Users'] = "Потребители";
$lang['Groups'] = "Групи";
$lang['Forums'] = "Форуми";
$lang['Styles'] = "Стилове";

$lang['Configuration'] = "Обща Конфигурация";
$lang['Permissions'] = "Права";
$lang['Manage'] = "Настройки";
$lang['Disallow'] = "Забрана на имена";
$lang['Prune'] = "Изтриване";
$lang['Mass_Email'] = "Масов мейл";
$lang['Ranks'] = "Рангове";
$lang['Smilies'] = "Smilies";
$lang['Ban_Management'] = "Ban Control";
$lang['Word_Censor'] = "Цензурирани Думи";
$lang['Export'] = "Експортиране";
$lang['Create_new'] = "Създаване";
$lang['Add_new'] = "Добавяне";
$lang['Backup_DB'] = "Съхраняване на БД";
$lang['Restore_DB'] = "Възстановяване на БД";


//
// Index
//
$lang['Admin'] = "Администрация";
$lang['Not_admin'] = "Нямате право да администрирате тези форуми";
$lang['Welcome_phpBB'] = "Добре дошли в phpBB";
$lang['Admin_intro'] = "Thank you for choosing phpBB as your forum solution. This screen will give you a quick overview of all the various statistics of your board. You can get back to this page by clicking on the <u>Admin Index</u> link in the left pane. To return to the index of your board, click the phpBB logo also in the left pane. The other links on the left hand side of this screen will allow you to control every aspect of your forum experience, each screen will have instructions on how to use the tools.";
$lang['Main_index'] = "Индекс на Форумите";
$lang['Forum_stats'] = "Форум Статистики";
$lang['Admin_Index'] = "Админ-панел";
$lang['Preview_forum'] = "Преглед на форума";

$lang['Click_return_admin_index'] = "Кликнете %sтук%s за да се върнете в Админ-панела";

$lang['Statistic'] = "Статистика";
$lang['Value'] = "Стойност";
$lang['Number_posts'] = "Брой мнения";
$lang['Posts_per_day'] = "Мнения на ден";
$lang['Number_topics'] = "Брой теми";
$lang['Topics_per_day'] = "Теми на ден";
$lang['Number_users'] = "Брой потребители";
$lang['Users_per_day'] = "Потребители на ден";
$lang['Board_started'] = "Старт на борда";
$lang['Avatar_dir_size'] = "Размер на папката с Аватарите";
$lang['Database_size'] = "Размер на базата данни";
$lang['Gzip_compression'] ="Gzip компресия";
$lang['Not_available'] = "Няма данни";

$lang['ON'] = "Включена"; // This is for GZip compression
$lang['OFF'] = "Изключена"; 


//
// DB Utils
//
$lang['Database_Utilities'] = "Работа с базата данни";

$lang['Restore'] = "Възстановяване";
$lang['Backup'] = "Съхраняване";
$lang['Restore_explain'] = "This will perform a full restore of all phpBB tables from a saved file. If your server supports it you may upload a gzip compressed text file and it will automatically be decompressed. <b>WARNING</b> This will overwrite any existing data. The restore may take a long time to process please do not move from this page till it is complete.";
$lang['Backup_explain'] = "Here you can backup all your phpBB related data. If you have any additional custom tables in the same database with phpBB that you would like to back up as well please enter their names separated by commas in the Additional Tables textbox below. If your server supports it you may also gzip compress the file to reduce its size before download.";

$lang['Backup_options'] = "Опции за Съхраняване";
$lang['Start_backup'] = "Започни Съхраняването";
$lang['Full_backup'] = "Пълно Съхраняване";
$lang['Structure_backup'] = "Съхраняване само на структурата";
$lang['Data_backup'] = "Съхраняване само на данните";
$lang['Additional_tables'] = "Допълнителни таблици";
$lang['Gzip_compress'] = "Компресиране на файла с Gzip";
$lang['Select_file'] = "Изберете файл";
$lang['Start_Restore'] = "Започни Възстановяването";

$lang['Restore_success'] = "Базата данни беше Възстановена успешно.<br /><br />Форумите са върнати в състоянието, в което са били при последното Съхраняване.";
$lang['Backup_download'] = "Свалянето ще започне след малко, моля изчакайте!";
$lang['Backups_not_supported'] = "Съхраняването не е възможно поради липса на поддръжка във вашата БД-система.";

$lang['Restore_Error_uploading'] = "Грешка при качването на файла с данните за Възстановяването.";
$lang['Restore_Error_filename'] = "Проблем с името на файла, моля опитайте с друг файл.";
$lang['Restore_Error_decompress'] = "Gzip-файла неможе да бъде разкомпресиран, моля качете некомпресирана версия на файла.";
$lang['Restore_Error_no_file'] = "Няма такъв качен файл";


//
// Auth pages
//
$lang['Select_a_User'] = "Изберете Потребител";
$lang['Select_a_Group'] = "Изберете Група";
$lang['Select_a_Forum'] = "Изберете Форум";
$lang['Auth_Control_User'] = "Контрол на правата на Потребителите"; 
$lang['Auth_Control_Group'] = "Контрол на правата на Групите"; 
$lang['Auth_Control_Forum'] = "Контрол на правата във Форумите"; 
$lang['Look_up_User'] = "Вижте Потребителя"; 
$lang['Look_up_Group'] = "Вижте Групата"; 
$lang['Look_up_Forum'] = "Вижте Форума"; 

$lang['Group_auth_explain'] = "Here you can alter the permissions and moderator status assigned to each user group. Do not forget when changing group permissions that individual user permissions may still allow the user entry to forums, etc. You will be warned if this is the case.";
$lang['User_auth_explain'] = "Here you can alter the permissions and moderator status assigned to each individual user. Do not forget when changing user permissions that group permissions may still allow the user entry to forums, etc. You will be warned if this is the case.";
$lang['Forum_auth_explain'] = "Here you can alter the authorisation levels of each forum. You will have both a simple and advanced method for doing this, advanced offers greater control of each forum operation. Remember that changing the permission level of forums will affect which users can carry out the various operations within them.";

$lang['Simple_mode'] = "Прости настройки";
$lang['Advanced_mode'] = "Сложни настройки";
$lang['Moderator_status'] = "Статут на Модератор";

$lang['Allowed_Access'] = "Достъп разрешен";
$lang['Disallowed_Access'] = "Достъп забранен";
$lang['Is_Moderator'] = "Е Модератор";
$lang['Not_Moderator'] = "Не е Модератор";

$lang['Conflict_warning'] = "Authorisation Conflict Warning";
$lang['Conflict_access_userauth'] = "This user still has access rights to this forum via group membership. You may want to alter the group permissions or remove this user the group to fully prevent them having access rights. The groups granting rights (and the forums involved) are noted below.";
$lang['Conflict_mod_userauth'] = "This user still has moderator rights to this forum via group membership. You may want to alter the group permissions or remove this user the group to fully prevent them having moderator rights. The groups granting rights (and the forums involved) are noted below.";

$lang['Conflict_access_groupauth'] = "The following user (or users) still have access rights to this forum via their user permission settings. You may want to alter the user permissions to fully prevent them having access rights. The users granted rights (and the forums involved) are noted below.";
$lang['Conflict_mod_groupauth'] = "The following user (or users) still have moderator rights to this forum via their user permissions settings. You may want to alter the user permissions to fully prevent them having moderator rights. The users granted rights (and the forums involved) are noted below.";

$lang['Public'] = "Публичен";
$lang['Private'] = "Частен";
$lang['Registered'] = "Регистриран";
$lang['Administrators'] = "Администратори";
$lang['Hidden'] = "Скрит";

$lang['View'] = "Виждане";
$lang['Read'] = "Четене";
$lang['Post'] = "Писане";
$lang['Reply'] = "Отговаряне";
$lang['Edit'] = "Промяна";
$lang['Delete'] = "Изтриване";
$lang['Sticky'] = "Важна тема";
$lang['Announce'] = "СЪОБЩЕНИЕ"; 
$lang['Vote'] = "Гласуване";
$lang['Pollcreate'] = "Анкета";

$lang['Permissions'] = "Права";
$lang['Simple_Permission'] = "Прости Права";

$lang['User_Level'] = "Потребителско ниво"; 
$lang['Auth_User'] = "Потребител";
$lang['Auth_Admin'] = "Администратор";
$lang['Group_memberships'] = "Членство в потребителски групи";
$lang['Usergroup_members'] = "Тази група има следните членове";

$lang['Forum_auth_updated'] = "Правата във Форума са обновени";
$lang['User_auth_updated'] = "Правата на Потребителя са обновени";
$lang['Group_auth_updated'] = "Правата на Групата са обновени";

$lang['Auth_updated'] = "Правата са обновени";
$lang['Click_return_userauth'] = "Кликнете %sтук%s за да се върнете на Контрол на правата на Потребителите";
$lang['Click_return_groupauth'] = "Кликнете %sтук%s за да се върнете на Контрол на правата на Групите";
$lang['Click_return_forumauth'] = "Кликнете %sтук%s за да се върнете на Контрол на правата във Форумите";


//
// Banning
//
$lang['Ban_control'] = "Ban Control";
$lang['Ban_explain'] = "Here you can control the banning of users. You can achieve this by banning either or both of a specific user or an individual or range of IP addresses or hostnames. These methods prevent a user from even reaching the index page of your board. To prevent a user from registering under a different username you can also specify a banned email address. Please note that banning an email address alone will not prevent that user from being able to logon or post to your board, you should use one of the first two methods to achieve this.";
$lang['Ban_explain_warn'] = "Please note that entering a range of IP addresses results in all the addresses between the start and end being added to the banlist. Attempts will be made to minimise the number of addresses added to the database by introducing wildcards automatically where appropriate. If you really must enter a range try to keep it small or better yet state specific addresses.";

$lang['Select_username'] = "Изберете Потребител";
$lang['Select_ip'] = "Изберете IP";
$lang['Select_email'] = "Изберете мейл адрес";

$lang['Ban_username'] = "Изгонване по потребителско име";
$lang['Ban_username_explain'] = "Можете да изгоните няколко потребителя едновременно като ги селектирате от списъка.";

$lang['Ban_IP'] = "Изгонване по IP адрес или домейн";
$lang['IP_hostname'] = "IP адрес или домейн";
$lang['Ban_IP_explain'] = "За да изберете няколко различни IPта или домейна, разделете ги с запетаи. За да изберете набор от IPта, разделете началния и крайния с тире (-). Можете да ползвате и * за избор на цяла подмрежа.";

$lang['Ban_email'] = "Изгонване по мейл адрес";
$lang['Ban_email_explain'] = "За да изберете повече от един мейл адрес, разделете ги с запетаи. Използвайте *, за да изберете набор от адреси, например *@hotmail.com или johnsmith@*.com";

$lang['Unban_username'] = "Un-ban one more specific users";
$lang['Unban_username_explain'] = "Можете да ??? няколко потребителя едновременно като ги селектирате от списъка.";

$lang['Unban_IP'] = "Un-ban one or more IP addresses";
$lang['Unban_IP_explain'] = "Можете да ??? няколко потребителя едновременно като ги селектирате от списъка.";

$lang['Unban_email'] = "Un-ban one or more email addresses";
$lang['Unban_email_explain'] = "Можете ??? изгоните няколко потребителя едновременно като ги селектирате от списъка.";

$lang['No_banned_users'] = "Няма изгонени Потребители";
$lang['No_banned_ip'] = "Няма забранени IPта";
$lang['No_banned_email'] = "Няма забранени мейл адреси";

$lang['Ban_update_sucessful'] = "Списъка с изгонените е обновен успешно";
$lang['Click_return_banadmin'] = "Кликнете %sтук%s за да се върнете на Ban Control";


//
// Configuration
//
$lang['General_Config'] = "Обща конфигурация";
$lang['Config_explain'] = "The form below will allow you to customize all the general board options. For User and Forum configurations use the related links on the left hand side.";

$lang['Click_return_config'] = "Кликнете %sтук%s за да се върнете в Общата Конфигурация";

$lang['General_settings'] = "Общи настройки на Форумите";
$lang['Site_name'] = "Име на сайта";
$lang['Site_desc'] = "Описание на сайта";
$lang['Board_disable'] = "Изключете Форумите";
$lang['Board_disable_explain'] = "Това ще направи Форумите недостъпни за потребителите. Не излизайта от Форумите след като сте ги изключили, няма да можете да влезете обратно!";
$lang['Acct_activation'] = "Активиране на Потребителите";
$lang['Acc_None'] = "Не е нужно"; // These three entries are the type of activation
$lang['Acc_User'] = "От Потребител";
$lang['Acc_Admin'] = "От Администратор";

$lang['Abilities_settings'] = "Основни настройки на Потребител и Форум";
$lang['Max_poll_options'] = "Максимум възможни отговори за анкета";
$lang['Flood_Interval'] = "Интервал за flood";
$lang['Flood_Interval_explain'] = "Брой секунди, които потребителя трябва да изчака между отделните мнения."; 
$lang['Board_email_form'] = "Пращане на мейл чрез Форумите";
$lang['Board_email_form_explain'] = "Потребителите могат да си пращат мейл чрез Форумите";
$lang['Topics_per_page'] = "Теми на страница";
$lang['Posts_per_page'] = "Мнения на страница";
$lang['Hot_threshold'] = "Брой мнения за Популярна тема";
$lang['Default_style'] = "Основен стил";
$lang['Override_style'] = "Заменяне на потребителския стил";
$lang['Override_style_explain'] = "Заменя избрания от потребителя с основния";
$lang['Default_language'] = "Основен език";
$lang['Date_format'] = "Формат на датата";
$lang['System_timezone'] = "Часова зона на системата";
$lang['Enable_gzip'] = "Включена GZip компресия";
$lang['Enable_prune'] = "Включен Forum Pruning";
$lang['Allow_HTML'] = "Разрешен HTML";
$lang['Allow_BBCode'] = "Разрешен BBCode";
$lang['Allowed_tags'] = "Разрешени HTML тагове";
$lang['Allowed_tags_explain'] = "Separate tags with commas";
$lang['Allow_smilies'] = "Разрешени Smilies";
$lang['Smilies_path'] = "Път към папката със Smilies";
$lang['Smilies_path_explain'] = "Път, спрямо основната папка на phpBB, напр. images/smilies";
$lang['Allow_sig'] = "Позволени подписи";
$lang['Max_sig_length'] = "Максимум символи";
$lang['Max_sig_length_explain'] = "Максимален брой на символите в сигнатурата";
$lang['Allow_name_change'] = "Разрешени смени на Потребителското име";

$lang['Avatar_settings'] = "Настройки на Аватарите";
$lang['Allow_local'] = "Разрешени Аватари от Галерията";
$lang['Allow_remote'] = "Разрешени външни Аватари";
$lang['Allow_remote_explain'] = "Аватари, качени на друг сайт и поставени тук с връзка";
$lang['Allow_upload'] = "Разрешено качването на Аватари";
$lang['Max_filesize'] = "Максимум обем на Аватара";
$lang['Max_filesize_explain'] = "Отнася се за качените файлове";
$lang['Max_avatar_size'] = "Максимум размер на Аватара";
$lang['Max_avatar_size_explain'] = "(Височина x Ширина в пиксели)";
$lang['Avatar_storage_path'] = "Папка за съхранение на Аватарите";
$lang['Avatar_storage_path_explain'] = "Път, спрямо основната папка на phpBB, напр. images/avatars";
$lang['Avatar_gallery_path'] = "Папка за Аватари от Галерията";
$lang['Avatar_gallery_path_explain'] = "Път, спрямо основната папка на phpBB, към папка с изображения за Галерията с Аватари";

$lang['COPPA_settings'] = "Настройки на COPPA";
$lang['COPPA_fax'] = "COPPA Факс номер";
$lang['COPPA_mail'] = "COPPA Пощенски адрес";
$lang['COPPA_mail_explain'] = "Това е пощенския адрес, на който родителите ще изпращат COPPA регистрационни";
$lang['Email_settings'] = "Настройки на Мейла";
$lang['Admin_email'] = "Мейл на администратора";
$lang['Email_sig'] = "Мейл подпис";
$lang['Email_sig_explain'] = "Този подпис ще бъде прикачен към всички мейли, изпращани от Форумите";
$lang['Use_SMTP'] = "Използване на SMTP-сървър";
$lang['Use_SMTP_explain'] = "Ако искате да пращате мейла през избран SMTP-сървър, а не през локалната мейл-функция.";
$lang['SMTP_server'] = "Адрес на SMTP-сървъра";

$lang['Disable_privmsg'] = "Система за Лични съобщения";
$lang['Inbox_limits'] = "Максимум съобщения във Входящи";
$lang['Sentbox_limits'] = "Max posts in Sentbox";
$lang['Savebox_limits'] = "Max posts in Savebox";

$lang['Cookie_settings'] = "Настройки на Cookies"; 
$lang['Cookie_settings_explain'] = "These control how the cookie sent to browsers is defined. In most cases the default should be sufficient. If you need to change these do so with care, incorrect settings can prevent users logging in.";
$lang['Cookie_name'] = "Име на Cookie";
$lang['Cookie_domain'] = "Домейн на Cookie";
$lang['Cookie_path'] = "Път на Cookie";
$lang['Session_length'] = "Дължина на сесията (в секунди)";
$lang['Cookie_secure'] = "Secure Cookie (по https)";


//
// Forum Management
//
$lang['Forum_admin'] = "Администриране на Форумите";
$lang['Forum_admin_explain'] = "From this panel you can add, delete, edit, re-order and re-synchronise categories and forums";
$lang['Edit_forum'] = "Промяна по Форум";
$lang['Create_forum'] = "Създаване на нов форум";
$lang['Create_category'] = "Създаване на нова категория";
$lang['Remove'] = "Премахване";
$lang['Action'] = "Действие";
$lang['Update_order'] = "Update Order";
$lang['Config_updated'] = "Forum Configuration Updated Successfully";
$lang['Edit'] = "Edit";
$lang['Delete'] = "Delete";
$lang['Move_up'] = "Move up";
$lang['Move_down'] = "Move down";
$lang['Resync'] = "Resync";
$lang['No_mode'] = "No mode was set";
$lang['Forum_edit_delete_explain'] = "The form below will allow you to customize all the general board options. For User and Forum configurations use the related links on the left hand side";

$lang['Move_contents'] = "Move all contents";
$lang['Forum_delete'] = "Delete Forum";
$lang['Forum_delete_explain'] = "The form below will allow you to delete a forum (or category) and decide where you want to put all topics (or forums) it contained.";

$lang['Forum_settings'] = "General Forum Settings";
$lang['Forum_name'] = "Forum name";
$lang['Forum_desc'] = "Description";
$lang['Forum_status'] = "Forum status";
$lang['Forum_pruning'] = "Auto-pruning";

$lang['prune_freq'] = 'Check for topic age every';
$lang['prune_days'] = "Remove topics that have not been posted to in";
$lang['Set_prune_data'] = "You have turned on auto-prune for this forum but did not set a frequency or number of days to prune. Please go back and do so";

$lang['Move_and_Delete'] = "Move and Delete";

$lang['Delete_all_posts'] = "Delete all posts";
$lang['Nowhere_to_move'] = "Nowhere to move too";

$lang['Edit_Category'] = "Edit Category";
$lang['Edit_Category_explain'] = "Use this form to modify a categories name.";

$lang['Forums_updated'] = "Forum and Category information updated successfully";

$lang['Must_delete_forums'] = "You need to delete all forums before you can delete this category";

$lang['Click_return_forumadmin'] = "Click %sHere%s to return to Forum Administration";


//
// Smiley Management
//
$lang['smiley_title'] = "Smiles Editing Utility";
$lang['smile_desc'] = "From this page you can add, remove and edit the emoticons or smileys your users can use in their posts and private messages.";

$lang['smiley_config'] = "Smiley Configuration";
$lang['smiley_code'] = "Smiley Code";
$lang['smiley_url'] = "Smiley Image File";
$lang['smiley_emot'] = "Smiley Emotion";
$lang['smile_add'] = "Add a new Smiley";
$lang['Smile'] = "Smile";
$lang['Emotion'] = "Emotion";

$lang['Select_pak'] = "Select Pack (.pak) File";
$lang['replace_existing'] = "Replace Existing Smiley";
$lang['keep_existing'] = "Keep Existing Smiley";
$lang['smiley_import_inst'] = "You should unzip the smiley package and upload all files to the appropriate Smiley directory for your installation.  Then select the correct information in this form to import the smiley pack.";
$lang['smiley_import'] = "Smiley Pack Import";
$lang['choose_smile_pak'] = "Choose a Smile Pack .pak file";
$lang['import'] = "Import Smileys";
$lang['smile_conflicts'] = "What should be done in case of conflicts";
$lang['del_existing_smileys'] = "Delete existing smileys before import";
$lang['import_smile_pack'] = "Import Smiley Pack";
$lang['export_smile_pack'] = "Create Smiley Pack";
$lang['export_smiles'] = "To create a smiley pack from your currently installed smileys, click %sHere%s to download the smiles.pak file. Name this file appropriately making sure to keep the .pak file extension.  Then create a zip file containing all of your smiley images plus this .pak configuration file.";

$lang['smiley_add_success'] = "The Smiley was successfully added";
$lang['smiley_edit_success'] = "The Smiley was successfully updated";
$lang['smiley_import_success'] = "The Smiley Pack was imported successfully!";
$lang['smiley_del_success'] = "The Smiley was successfully removed";
$lang['Click_return_smileadmin'] = "Click %sHere%s to return to Smiley Administration";


//
// User Management
//
$lang['User_admin'] = "User Administration";
$lang['User_admin_explain'] = "Here you can change your user's information and certain specific options. To modify the users permissions please use the user and group permissions system.";

$lang['Look_up_user'] = "Look up user";

$lang['Admin_user_fail'] = "Couldn't update the users profile.";
$lang['Admin_user_updated'] = "The user's profile was successfully updated.";
$lang['Click_return_useradmin'] = "Click %sHere%s to return to User Administration";

$lang['User_delete'] = "Delete this user";
$lang['User_delete_explain'] = "Click here to delete this user, this cannot be undone.";
$lang['User_deleted'] = "User was successfully deleted.";

$lang['User_status'] = "User is active";
$lang['User_allowpm'] = "Can send Private Messages";
$lang['User_allowavatar'] = "Can display avatar";

$lang['Admin_avatar_explain'] = "Here you can see and delete the user's current avatar.";

$lang['User_special'] = "Special admin-only fields";
$lang['User_special_explain'] = "These fields are not able to be modified by the users.  Here you can set their status and other options that are not given to users.";


//
// Group Management
//
$lang['Group_administration'] = "Group Administration";
$lang['Group_admin_explain'] = "From this panel you can administer all your usergroups, you can; delete, create and edit existing groups. You may choose moderators, toggle open/closed group status and set the group name and description";
$lang['Error_updating_groups'] = "There was an error while updating the groups";
$lang['Updated_group'] = "The group was successfully updated";
$lang['Added_new_group'] = "The new group was successfully created";
$lang['Deleted_group'] = "The group was successfully deleted";
$lang['New_group'] = "Create new group";
$lang['Edit_group'] = "Edit group";
$lang['group_name'] = "Group name";
$lang['group_description'] = "Group description";
$lang['group_moderator'] = "Group moderator";
$lang['group_status'] = "Group status";
$lang['group_open'] = "Open group";
$lang['group_closed'] = "Closed group";
$lang['group_hidden'] = "Hidden group";
$lang['group_delete'] = "Delete group";
$lang['group_delete_check'] = "Delete this group";
$lang['submit_group_changes'] = "Submit Changes";
$lang['reset_group_changes'] = "Reset Changes";
$lang['No_group_name'] = "You must specify a name for this group";
$lang['No_group_moderator'] = "You must specify a moderator for this group";
$lang['No_group_mode'] = "You must specify a mode for this group, open or closed";
$lang['delete_group_moderator'] = "Delete the old group moderator?";
$lang['delete_moderator_explain'] = "If you're changing the group moderator, check this box to remove the old moderator from the group.  Otherwise, do not check it, and the user will become a regular member of the group.";
$lang['Click_return_groupsadmin'] = "Click %sHere%s to return to Group Administration.";
$lang['Select_group'] = "Select a group";
$lang['Look_up_group'] = "Look up group";


//
// Prune Administration
//
$lang['Forum_Prune'] = "Forum Prune";
$lang['Forum_Prune_explain'] = "This will delete any topic which has not been posted to within the number of days you select. If you do not enter a number then all topics will be deleted. It will not remove topics in which polls are still running nor will it remove announcements. You will need to remove these topics manually.";
$lang['Do_Prune'] = "Do Prune";
$lang['All_Forums'] = "All Forums";
$lang['Prune_topics_not_posted'] = "Prune topics with no replies in this many days";
$lang['Topics_pruned'] = "Topics pruned";
$lang['Posts_pruned'] = "Posts pruned";
$lang['Prune_success'] = "Pruning of forums was successful";


//
// Word censor
//
$lang['Words_title'] = "Word Censoring";
$lang['Words_explain'] = "From this control panel you can add, edit, and remove words that will be automatically censored on your forums. In addition people will not be allowed to register with usernames containing these words. Wildcards (*) are accepted in the word field, eg. *test* will match detestable, test* would match testing, *test would match detest.";
$lang['Word'] = "Word";
$lang['Edit_word_censor'] = "Edit word censor";
$lang['Replacement'] = "Replacement";
$lang['Add_new_word'] = "Add new word";
$lang['Update_word'] = "Update word censor";

$lang['Must_enter_word'] = "You must enter a word and its replacement";
$lang['No_word_selected'] = "No word selected for editing";

$lang['Word_updated'] = "The selected word censor has been successfully updated";
$lang['Word_added'] = "The word censor has been successfully added";
$lang['Word_removed'] = "The selected word censor has been successfully removed";

$lang['Click_return_wordadmin'] = "Click %sHere%s to return to Word Censor Administration";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Here you can email a message to either all of your users, or all users of a specific group.  To do this, an email will be sent out to the administrative email address supplied, with a blind carbon copy sent to all recipients. If you are emailing a large group of people please be patient after submitting and do not stop the page halfway through. It is normal for amass emailing to take a long time, you will be notified when the script has completed";
$lang['Compose'] = "Compose"; 

$lang['Recipients'] = "Recipients"; 
$lang['All_users'] = "All Users";

$lang['Email_successfull'] = "Your message has been sent";
$lang['Click_return_massemail'] = "Click %sHere%s to return to the Mass Email form";


//
// Ranks admin
//
$lang['Ranks_title'] = "Rank Administration";
$lang['Ranks_explain'] = "Using this form you can add, edit, view and delete ranks. You can also create custom ranks which can be applied to a user via the user management facility";

$lang['Add_new_rank'] = "Add new rank";

$lang['Rank_title'] = "Rank Title";
$lang['Rank_special'] = "Set as Special Rank";
$lang['Rank_minimum'] = "Minimum Posts";
$lang['Rank_maximum'] = "Maximum Posts";
$lang['Rank_image'] = "Rank Image (Relative to phpBB2 root path)";
$lang['Rank_image_explain'] = "Use this to define a small image associated with the rank";

$lang['Must_select_rank'] = "You must select a rank";
$lang['No_assigned_rank'] = "No special rank assigned";

$lang['Rank_updated'] = "The rank was successfully updated";
$lang['Rank_added'] = "The rank was successfully added";
$lang['Rank_removed'] = "The rank was successfully deleted";

$lang['Click_return_rankadmin'] = "Click %sHere%s to return to Rank Administration";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Username Disallow Control";
$lang['Disallow_explain'] = "Here you can control usernames which will not be allowed to be used.  Disallowed usernames are allowed to contain a wildcard character of *.  Please note that you will not be allowed to specify any username that has already been registered, you must first delete that name then disallow it";

$lang['Delete_disallow'] = "Delete";
$lang['Delete_disallow_title'] = "Remove a Disallowed Username";
$lang['Delete_disallow_explain'] = "You can remove a disallowed username by selecting the username from this list and clicking submit";

$lang['Add_disallow'] = "Add";
$lang['Add_disallow_title'] = "Add a disallowed username";
$lang['Add_disallow_explain'] = "You can disallow a username using the wildcard character * to match any character";

$lang['No_disallowed'] = "No Disallowed Usernames";

$lang['Disallowed_deleted'] = "The disallowed username has been successfully removed";
$lang['Disallow_successful'] = "The disallowed username has ben successfully added";
$lang['Disallowed_already'] = "The name you entered could not be disallowed. It either already exists in the list, exists in the word censor list, or a matching username is present";

$lang['Click_return_disallowadmin'] = "Click %sHere%s to return to Disallow Username Administration";


//
// Styles Admin
//
$lang['Styles_admin'] = "Styles Administration";
$lang['Styles_explain'] = "Using this facility you can add, remove and manage styles (templates and themes) available to your users";
$lang['Styles_addnew_explain'] = "The following list contains all the themes that are available for the templates you currently have. The items on this list have not yet been installed into the phpBB database. To install a theme simply click the install link beside an entry";

$lang['Select_template'] = "Select a Template";

$lang['Style'] = "Style";
$lang['Template'] = "Template";
$lang['Install'] = "Install";
$lang['Download'] = "Download";

$lang['Edit_theme'] = "Edit Theme";
$lang['Edit_theme_explain'] = "In the form below you can edit the settings for the selected theme";

$lang['Create_theme'] = "Create Theme";
$lang['Create_theme_explain'] = "Use the form below to create a new theme for a selected template. When entering colours (for which you should use hexadecimal notation) you must not include the initial #, i.e.. CCCCCC is valid, #CCCCCC is not";

$lang['Export_themes'] = "Export Themes";
$lang['Export_explain'] = "In this panel you will be able to export the theme data for a selected template. Select the template from the list below and the script will create the theme configuration file and attempt to save it to the selected template directory. If it cannot save the file itself it will give you the option to download it. In order for the script to save the file you must give write access to the webserver for the selected template dir. For more information on this see the phpBB 2 users guide.";

$lang['Theme_installed'] = "The selected theme has been installed successfully";
$lang['Style_removed'] = "The selected style has been removed from the database. To fully remove this style from your system you must delete the appropriate style from your templates directory.";
$lang['Theme_info_saved'] = "The theme information for the selected template has been saved. You should now return the permissions on the theme_info.cfg (and if applicable the selected template directory) to read-only";
$lang['Theme_updated'] = "The selected theme has been updated. You should now export the new theme settings";
$lang['Theme_created'] = "Theme created. You should now export the theme to the theme configuration file for safe keeping or use elsewhere";

$lang['Confirm_delete_style'] = "Are you sure you want to delete this style";

$lang['Download_theme_cfg'] = "The exporter could not write the theme information file. Click the button below to download this file with your browser. Once you have downloaded it you can transfer it to the directory containing the template files. You can then package the files for distribution or use elsewhere if you desire";
$lang['No_themes'] = "The template you selected has no themes attached to it. To create a new theme click the Create New link on the left hand panel";
$lang['No_template_dir'] = "Could not open the template directory. It may be unreadable by the webserver or may not exist";
$lang['Cannot_remove_style'] = "You cannot remove the style selected since it is currently the forum default. Please change the default style and try again.";
$lang['Style_exists'] = "The style name to selected already exists, please go back and choose a different name.";

$lang['Click_return_styleadmin'] = "Click %sHere%s to return to Style Administration";

$lang['Theme_settings'] = "Настройки на Темата";
$lang['Theme_element'] = "Елемент от Темата";
$lang['Simple_name'] = "Просто име";
$lang['Value'] = "Стойност";
$lang['Save_Settings'] = "Запази настойките";

$lang['Stylesheet'] = "CSS Стилове";
$lang['Background_image'] = "Фонова картинка";
$lang['Background_color'] = "Фонов цвят";
$lang['Theme_name'] = "Име на Темата";
$lang['Link_color'] = "Цвят на връзките";
$lang['Text_color'] = "Цвят на текста";
$lang['VLink_color'] = "Цвят на посетените връзки";
$lang['ALink_color'] = "Цвят на активните връзки";
$lang['HLink_color'] = "Цвят на посочените връзки";
$lang['Tr_color1'] = "Цвят на табличен ред 1";
$lang['Tr_color2'] = "Цвят на табличен ред 2";
$lang['Tr_color3'] = "Цвят на табличен ред 3";
$lang['Tr_class1'] = "Клас на табличен ред 1";
$lang['Tr_class2'] = "Клас на табличен ред 2";
$lang['Tr_class3'] = "Клас на табличен ред 3";
$lang['Th_color1'] = "Цвят на табличен хедър 1";
$lang['Th_color2'] = "Цвят на табличен хедър 2";
$lang['Th_color3'] = "Цвят на табличен хедър 3";
$lang['Th_class1'] = "Клас на табличен хедър 1";
$lang['Th_class2'] = "Клас на табличен хедър 2";
$lang['Th_class3'] = "Клас на табличен хедър 3";
$lang['Td_color1'] = "Цвят на таблична клетка 1";
$lang['Td_color2'] = "Цвят на таблична клетка 2";
$lang['Td_color3'] = "Цвят на таблична клетка 3";
$lang['Td_class1'] = "Клас на таблична клетка 1";
$lang['Td_class2'] = "Клас на таблична клетка 2";
$lang['Td_class3'] = "Клас на таблична клетка 3";
$lang['fontface1'] = "Шрифт 1";
$lang['fontface2'] = "Шрифт 2";
$lang['fontface3'] = "Шрифт 3";
$lang['fontsize1'] = "Размер на шрифта 1";
$lang['fontsize2'] = "Размер на шрифта 2";
$lang['fontsize3'] = "Размер на шрифта 3";
$lang['fontcolor1'] = "Цвят на шрифта 1";
$lang['fontcolor2'] = "Цвят на шрифта 2";
$lang['fontcolor3'] = "Цвят на шрифта 3";
$lang['span_class1'] = "Клас <span> 1";
$lang['span_class2'] = "Клас <span> 2";
$lang['span_class3'] = "Клас <span> 3";
$lang['img_poll_size'] = "Размер на изображението за резултати от гласуването в пиксели";
$lang['img_pm_size'] = "Размер на статус-бара на Личните Съобщения в пиксели";


//
// Install Process
//
$lang['Welcome_install'] = "Welcome to phpBB 2 Installation";
$lang['Initial_config'] = "Basic Configuration";
$lang['DB_config'] = "Database Configuration";
$lang['Admin_config'] = "Admin Configuration";
$lang['continue_upgrade'] = "Once you have downloaded your config file to your local machine you may\"Continue Upgrade\" button below to move forward with the upgrade process.  Please wait to upload the config file until the upgrade process is complete.";
$lang['upgrade_submit'] = "Continue Upgrade";

$lang['Installer_Error'] = "An error has occurred during installation";
$lang['Previous_Install'] = "A previous installation has been detected";
$lang['Install_db_error'] = "An error occurred trying to update the database";

$lang['Re_install'] = "Your previous installation is still active. <br /><br />If you would like to re-install phpBB 2 you should click the Yes button below. Please be aware that doing so will destroy all existing data, no backups will be made! The administrator username and password you have used to login in to the board will be re-created after the re-installation, no other settings will be retained. <br /><br />Think carefully before pressing Yes!";

$lang['Inst_Step_0'] = "Thank you for choosing phpBB 2. In order to complete this install please fill out the details requested below. Please note that the database you install into should already exist. If you are installing to a database that uses ODBC, e.g. MS Access you should first create a DSN for it before proceeding.";

$lang['Start_Install'] = "Start Install";
$lang['Finish_Install'] = "Finish Installation";

$lang['Default_lang'] = "Default board language";
$lang['DB_Host'] = "Database Server Hostname / DSN";
$lang['DB_Name'] = "Your Database Name";
$lang['DB_Username'] = "Database Username"; 
$lang['DB_Password'] = "Database Password"; 
$lang['Database'] = "Your Database";
$lang['Install_lang'] = "Choose Language for Installation";
$lang['dbms'] = "Database Type";
$lang['Table_Prefix'] = "Prefix for tables in database";
$lang['Admin_Username'] = "Administrator Username";
$lang['Admin_Password'] = "Administrator Password";
$lang['Admin_Password_confirm'] = "Administrator Password [ Confirm ]";

$lang['Inst_Step_2'] = "Your admin username has been created.  At this point your basic installation is complete. You will now be taken to a screen which will allow you to administer your new installation. Please be sure to check the General Configuration details and make any required changes. Thank you for choosing phpBB 2.";

$lang['Unwriteable_config'] = "Your config file is un-writeable at present. A copy of the config file will be downloaded to your when you click the button below. You should upload this file to the same directory as phpBB 2. Once this is done you should log in using the administrator name and password you provided on the previous form and visit the admin control centre (a link will appear at the bottom of each screen once logged in) to check the general configuration. Thank you for choosing phpBB 2.";
$lang['Download_config'] = "Download Config";

$lang['ftp_choose'] = "Choose Download Method";
$lang['ftp_option'] = "<br />Since FTP extensions are enabled in this version of PHP you may also be given the option of first trying to automatically ftp the config file into place.";
$lang['ftp_instructs'] = "You have chosen to ftp the file to the account containing phpBB 2 automatically.  Please enter the information below to facilitate this process. Note that the FTP path should be the exact path via ftp to your phpBB2 installation as if you were ftping to it using any normal client.";
$lang['ftp_info'] = "Enter Your FTP Information";
$lang['Attempt_ftp'] = "Attempt to ftp config file into place";
$lang['Send_file'] = "Just send the file to me and I'll ftp it manually";
$lang['ftp_path'] = "FTP path to phpBB 2";
$lang['ftp_username'] = "Your FTP Username";
$lang['ftp_password'] = "Your FTP Password";
$lang['Transfer_config'] = "Start Transfer";
$lang['NoFTP_config'] = "The attempt to ftp the config file into place failed.  Please download the config file and ftp it into place manually.";

$lang['Install'] = "Инталиране";
$lang['Upgrade'] = "Ъпгрейд";

$lang['Install_Method'] = "Изберете метода на инсталиране";

//
// That's all Folks!
// -------------------------------------------------

?>