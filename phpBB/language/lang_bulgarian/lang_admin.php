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
*     last update          : Fri Jan 15 2001  
*     by                   : Boby Dimitrov (Боби Димитров) 
*     email                : boby@azholding.com 
****************************************************************************/ 
//
// Format is same as lang_main
//

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
$lang['Prune'] = "Зачистване";
$lang['Mass_Email'] = "Масов мейл";
$lang['Ranks'] = "Рангове";
$lang['Smilies'] = "Smilies";
$lang['Ban_Management'] = "Бан Контрол";
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
$lang['Restore_Error_decompress'] = "Gzip-файла не може да бъде разкомпресиран, моля качете некомпресирана версия на файла.";
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

$lang['Conflict_warning'] = "Предупреждение за конфликт в правата";
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
$lang['Click_return_userauth'] = "Кликнете %sтук%s за да се върнете в Контрол на правата на Потребителите";
$lang['Click_return_groupauth'] = "Кликнете %sтук%s за да се върнете в Контрол на правата на Групите";
$lang['Click_return_forumauth'] = "Кликнете %sтук%s за да се върнете в Контрол на правата във Форумите";


//
// Banning
//
$lang['Ban_control'] = "Бан Контрол";
$lang['Ban_explain'] = "Here you can control the banning of users. You can achieve this by banning either or both of a specific user or an individual or range of IP addresses or hostnames. These methods prevent a user from even reaching the index page of your board. To prevent a user from registering under a different username you can also specify a banned email address. Please note that banning an email address alone will not prevent that user from being able to logon or post to your board, you should use one of the first two methods to achieve this.";
$lang['Ban_explain_warn'] = "Please note that entering a range of IP addresses results in all the addresses between the start and end being added to the banlist. Attempts will be made to minimise the number of addresses added to the database by introducing wildcards automatically where appropriate. If you really must enter a range try to keep it small or better yet state specific addresses.";

$lang['Select_username'] = "Изберете Потребител";
$lang['Select_ip'] = "Изберете IP";
$lang['Select_email'] = "Изберете мейл адрес";

$lang['Ban_username'] = "Бан по потребителско име";
$lang['Ban_username_explain'] = "Можете да изгоните няколко потребителя едновременно като ги селектирате от списъка.";

$lang['Ban_IP'] = "Бан по IP адрес или домейн";
$lang['IP_hostname'] = "IP адрес или домейн";
$lang['Ban_IP_explain'] = "За да изберете няколко различни IPта или домейна, разделете ги с запетаи. За да изберете набор от IPта, разделете началния и крайния с тире (-). Можете да ползвате и * за избор на цяла подмрежа.";

$lang['Ban_email'] = "Бан по мейл адрес";
$lang['Ban_email_explain'] = "За да изберете повече от един мейл адрес, разделете ги с запетаи. Използвайте *, за да изберете набор от адреси, например *@hotmail.com или johnsmith@*.com";

$lang['Unban_username'] = "Ън-бан по потребителско име";
$lang['Unban_username_explain'] = "Можете да ън-баннете няколко потребителя едновременно като ги селектирате от списъка.";

$lang['Unban_IP'] = "Ън-бан по IP адрес или домейн";
$lang['Unban_IP_explain'] = "Можете да ън-баннете няколко потребителя едновременно като ги селектирате от списъка.";

$lang['Unban_email'] = "Ън-бан по мейл адрес";
$lang['Unban_email_explain'] = "Можете ън-баннете няколко потребителя едновременно като ги селектирате от списъка.";

$lang['No_banned_users'] = "Няма изгонени Потребители";
$lang['No_banned_ip'] = "Няма забранени IPта";
$lang['No_banned_email'] = "Няма забранени мейл адреси";

$lang['Ban_update_sucessful'] = "Списъка с изгонените е обновен успешно";
$lang['Click_return_banadmin'] = "Кликнете %sтук%s за да се върнете в Бан Контрола";


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
$lang['Enable_prune'] = "Включенo зачистване";
$lang['Allow_HTML'] = "Разрешен HTML";
$lang['Allow_BBCode'] = "Разрешен BBCode";
$lang['Allowed_tags'] = "Разрешени HTML тагове";
$lang['Allowed_tags_explain'] = "Разделете таговете с запетаи";
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
$lang['Sentbox_limits'] = "Максимум съобщения в Получени";
$lang['Savebox_limits'] = "Максимум съобщения в Съхранени";

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
$lang['Edit_forum'] = "Промяна на Форум";
$lang['Create_forum'] = "Създаване на нов форум";
$lang['Create_category'] = "Създаване на нова категория";
$lang['Remove'] = "Премахване";
$lang['Action'] = "Действие";
$lang['Update_order'] = "Обновяване на реда";
$lang['Config_updated'] = "Настройките на Форума са обновени успешно";
$lang['Edit'] = "Промяна";
$lang['Delete'] = "Изтриване";
$lang['Move_up'] = "Нагоре";
$lang['Move_down'] = "Надолу";
$lang['Resync'] = "Синхронизация";
$lang['No_mode'] = "Не е избран режим";
$lang['Forum_edit_delete_explain'] = "The form below will allow you to customize all the general board options. For User and Forum configurations use the related links on the left hand side";

$lang['Move_contents'] = "Преместване на съдържанието";
$lang['Forum_delete'] = "Изтриване на Форум";
$lang['Forum_delete_explain'] = "The form below will allow you to delete a forum (or category) and decide where you want to put all topics (or forums) it contained.";

$lang['Forum_settings'] = "Общи настойки за Форум";
$lang['Forum_name'] = "Име на Форума";
$lang['Forum_desc'] = "Описание";
$lang['Forum_status'] = "Статус";
$lang['Forum_pruning'] = "Самозачистване";

$lang['prune_freq'] = "Проверка за възрастта на темата на всеки";
$lang['prune_days'] = "Премахни теми, в които не е било писано от";
$lang['Set_prune_data'] = "You have turned on auto-prune for this forum but did not set a frequency or number of days to prune. Please go back and do so";

$lang['Move_and_Delete'] = "Местене и изтриване";

$lang['Delete_all_posts'] = "Изтриване на всички мнения";
$lang['Nowhere_to_move'] = "Не сте указали къде да се преместят темите";

$lang['Edit_Category'] = "Настройки на категория";
$lang['Edit_Category_explain'] = "Тук можете да промените името на категорията.";

$lang['Forums_updated'] = "Информацията за Форума и Категорията е обновена успешно";

$lang['Must_delete_forums'] = "Трябва да изтриете всички форуми, преди да можете да премахнете категорията";

$lang['Click_return_forumadmin'] = "Кликнете %sтук%s за да се върнете в Администриране на Форумите";


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
$lang['User_admin'] = "Администриране на Потребителите";
$lang['User_admin_explain'] = "Here you can change your user's information and certain specific options. To modify the users permissions please use the user and group permissions system.";

$lang['Look_up_user'] = "Вижте потребителя";

$lang['Admin_user_fail'] = "Профила на потребителя не беше обновен.";
$lang['Admin_user_updated'] = "Профила на потребителя е обновен успешно.";
$lang['Click_return_useradmin'] = "Кликнете %sтук%s за да се върнете в Администриране на Потребителите";

$lang['User_delete'] = "Изтриване на потребителя";
$lang['User_delete_explain'] = "Кликнете тук, за да изтриете този потребител. Това действие не е обратимо!";
$lang['User_deleted'] = "Потребителя беше изтрит успешно.";

$lang['User_status'] = "Потребителя е активен";
$lang['User_allowpm'] = "Може да праща Лични съобщения";
$lang['User_allowavatar'] = "Може да има Аватар";

$lang['Admin_avatar_explain'] = "Тук можете да видите и изтриете Аватара на потребителя.";

$lang['User_special'] = "Специални полета, досъпни само на Администратора";
$lang['User_special_explain'] = "These fields are not able to be modified by the users.  Here you can set their status and other options that are not given to users.";


//
// Group Management
//
$lang['Group_administration'] = "Администриране на Групите";
$lang['Group_admin_explain'] = "From this panel you can administer all your usergroups, you can; delete, create and edit existing groups. You may choose moderators, toggle open/closed group status and set the group name and description";
$lang['Error_updating_groups'] = "There was an error while updating the groups";
$lang['Updated_group'] = "Групата е обновена успешно";
$lang['Added_new_group'] = "Новата група е създадена успешно";
$lang['Deleted_group'] = "Групата е изтрита успешно";
$lang['New_group'] = "Създаване на нова група";
$lang['Edit_group'] = "Промяна";
$lang['group_name'] = "Име на групата";
$lang['group_description'] = "Описание на групата";
$lang['group_moderator'] = "Модератор на групата";
$lang['group_status'] = "Статус на групата";
$lang['group_open'] = "Отворена";
$lang['group_closed'] = "Затворена";
$lang['group_hidden'] = "Скрита";
$lang['group_delete'] = "Изтриване";
$lang['group_delete_check'] = "Изтрий тази група";
$lang['submit_group_changes'] = "Прати промените";
$lang['reset_group_changes'] = "Изчисти промените";
$lang['No_group_name'] = "Трябва да въведете име за групата";
$lang['No_group_moderator'] = "Трябва да изберете модератор за групата";
$lang['No_group_mode'] = "Трябва да изберете статус за групата";
$lang['delete_group_moderator'] = "Изтриване на модератора";
$lang['delete_moderator_explain'] = "If you're changing the group moderator, check this box to remove the old moderator from the group.  Otherwise, do not check it, and the user will become a regular member of the group.";
$lang['Click_return_groupsadmin'] = "Кликнете %sтук%s за да се върнете в Администриране на Групите";
$lang['Select_group'] = "Изберете група";
$lang['Look_up_group'] = "Виж групата";


//
// Prune Administration
//
$lang['Forum_Prune'] = "Зачистване на Форум";
$lang['Forum_Prune_explain'] = "This will delete any topic which has not been posted to within the number of days you select. If you do not enter a number then all topics will be deleted. It will not remove topics in which polls are still running nor will it remove announcements. You will need to remove these topics manually.";
$lang['Do_Prune'] = "Зачисти";
$lang['All_Forums'] = "Всички Форуми";
$lang['Prune_topics_not_posted'] = "Зачисти теми без отговор за последните";
$lang['Topics_pruned'] = "Темите са зачистени";
$lang['Posts_pruned'] = "Мненията са зачистени";
$lang['Prune_success'] = "Зачистванено на форумите е успешно";


//
// Word censor
//
$lang['Words_title'] = "Цензурирани Думи";
$lang['Words_explain'] = "From this control panel you can add, edit, and remove words that will be automatically censored on your forums. In addition people will not be allowed to register with usernames containing these words. Wildcards (*) are accepted in the word field, eg. *test* will match detestable, test* would match testing, *test would match detest.";
$lang['Word'] = "Дума";
$lang['Edit_word_censor'] = "Промяна на цензурираната дума";
$lang['Replacement'] = "Заместител";
$lang['Add_new_word'] = "Добавяне на нова дума";
$lang['Update_word'] = "Обновяване на Цензурираните Думи";

$lang['Must_enter_word'] = "Трябва да въведете дума и заместител";
$lang['No_word_selected'] = "Не е избрана дума за промяна";

$lang['Word_updated'] = "Цензурираната дума е обновена успешно";
$lang['Word_added'] = "Новата цензурирана дума е добавена успешно";
$lang['Word_removed'] = "Цензурираната дума е премахната успешно";

$lang['Click_return_wordadmin'] = "Кликнете %sтук%s за да се върнете в Цензурираните Думи";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Here you can email a message to either all of your users, or all users of a specific group.  To do this, an email will be sent out to the administrative email address supplied, with a blind carbon copy sent to all recipients. If you are emailing a large group of people please be patient after submitting and do not stop the page halfway through. It is normal for amass emailing to take a long time, you will be notified when the script has completed";
$lang['Compose'] = "Писане на мейла"; 

$lang['Recipients'] = "Получатели"; 
$lang['All_users'] = "Всички потребители";

$lang['Email_successfull'] = "Съобщенитето ви е изпратено успешно";
$lang['Click_return_massemail'] = "Кликнете %sтук%s за да се върнете в Масовия Мейл";


//
// Ranks admin
//
$lang['Ranks_title'] = "Администриране на Ранговете";
$lang['Ranks_explain'] = "Using this form you can add, edit, view and delete ranks. You can also create custom ranks which can be applied to a user via the user management facility";

$lang['Add_new_rank'] = "Добавяне на нов ранг";

$lang['Rank_title'] = "Рангова титла";
$lang['Rank_special'] = "Статут на Специален Ранг";
$lang['Rank_minimum'] = "Минимум мнения";
$lang['Rank_maximum'] = "Максимум мнения";
$lang['Rank_image'] = "Рангово изображение (Път, спрямо основната папка на phpBB)";
$lang['Rank_image_explain'] = "Това е малко изображение, свързано с съответния ранг";

$lang['Must_select_rank'] = "Трябва да изберете ранг";
$lang['No_assigned_rank'] = "Няма прикачен специален ранк";

$lang['Rank_updated'] = "Рангът е обновен успешно";
$lang['Rank_added'] = "Рангът е добавен успешно";
$lang['Rank_removed'] = "Рангът е изтрит успешно";

$lang['Click_return_rankadmin'] = "Кликнете %sтук%s за да се върнете в Администриране на Ранговете";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Забрана на Потребителски имена";
$lang['Disallow_explain'] = "Here you can control usernames which will not be allowed to be used.  Disallowed usernames are allowed to contain a wildcard character of *.  Please note that you will not be allowed to specify any username that has already been registered, you must first delete that name then disallow it";

$lang['Delete_disallow'] = "Изтрий";
$lang['Delete_disallow_title'] = "Премахване на забранено потребителско име";
$lang['Delete_disallow_explain'] = "Можете да премахнете забранено потребителско име като го селектирате в списъка и кликнете Изтриване";

$lang['Add_disallow'] = "Добави";
$lang['Add_disallow_title'] = "Добавяне на забранено потребителско име";
$lang['Add_disallow_explain'] = "Можете да използвате * като маска, за да забраните множество потребителски имена едновременно";

$lang['No_disallowed'] = "Няма забранени потребителски имена";

$lang['Disallowed_deleted'] = "Забраненото потребителско име е премахнато успешно";
$lang['Disallow_successful'] = "Забраненото потребителско име е добавено успешно";
$lang['Disallowed_already'] = "Името, което сте въвели, неможе да бъде забранено. То вече е забранено, или е цензурирана дума, или е регистрирано.";

$lang['Click_return_disallowadmin'] = "Кликнете %sтук%s за да се върнете в Забрана на Потребителски имена";


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
$lang['Welcome_install'] = "Добре дошли в инсталацията на phpBB 2";
$lang['Initial_config'] = "Обща конфигурация";
$lang['DB_config'] = "Конфигурация на базата данни";
$lang['Admin_config'] = "Конфигурация на администратора";
$lang['continue_upgrade'] = "Once you have downloaded your config file to your local machine you may\"Continue Upgrade\" button below to move forward with the upgrade process.  Please wait to upload the config file until the upgrade process is complete.";
$lang['upgrade_submit'] = "Продължи Ъпгрейда";

$lang['Installer_Error'] = "An error has occurred during installation";
$lang['Previous_Install'] = "A previous installation has been detected";
$lang['Install_db_error'] = "An error occurred trying to update the database";

$lang['Re_install'] = "Your previous installation is still active. <br /><br />If you would like to re-install phpBB 2 you should click the Yes button below. Please be aware that doing so will destroy all existing data, no backups will be made! The administrator username and password you have used to login in to the board will be re-created after the re-installation, no other settings will be retained. <br /><br />Think carefully before pressing Yes!";

$lang['Inst_Step_0'] = "Thank you for choosing phpBB 2. In order to complete this install please fill out the details requested below. Please note that the database you install into should already exist. If you are installing to a database that uses ODBC, e.g. MS Access you should first create a DSN for it before proceeding.";

$lang['Start_Install'] = "Започни Инсталирането";
$lang['Finish_Install'] = "Завърши Инсталирането";

$lang['Default_lang'] = "Основен език на Форумие";
$lang['DB_Host'] = "Сървър на базата данни / DSN";
$lang['DB_Name'] = "Име на базата данни";
$lang['DB_Username'] = "Потребителско име за базата данни"; 
$lang['DB_Password'] = "Парола за базата данни"; 
$lang['Database'] = "Вашата база данни";
$lang['Install_lang'] = "Изберете език за инсталацията";
$lang['dbms'] = "Вид на базата данни";
$lang['Table_Prefix'] = "Представка за таблиците в базата данни";
$lang['Admin_Username'] = "Потребителко име на Администратора";
$lang['Admin_Password'] = "Парола на Администратора";
$lang['Admin_Password_confirm'] = "Парола на Администратора (Потвърдете)";

$lang['Inst_Step_2'] = "Потребителя Администратор е създаден. До тук основната инсталация е завършена. Сега ще трябва да настроите новоинсталираните си Форуми. Моля не забравяйте да погледнете в Обща Конфигурация и да нанесете необходимите промени. Благодарим ви, че избрахте phpBB 2.";

$lang['Unwriteable_config'] = "Config-файла ви е недостъпен за писане. Копие на config-файла ще бъде свален на вашата машина, след като кликнете бутона долу. Трябва да качите този файл в папката на phpBB 2. След като направите това, трябва да влезете с администраторското име и парола, които въведохте на предишния екран и да посетите Администраторския Панел (линк натам има в дъното на всички страници на Форумите), за да настроите инсталацията. Благодарим ви, че избрахте phpBB 2.";
$lang['Download_config'] = "Свалете Config-файл";

$lang['ftp_choose'] = "Изберете метод за сваляне";
$lang['ftp_option'] = "<br />Since FTP extensions are enabled in this version of PHP you may also be given the option of first trying to automatically ftp the config file into place.";
$lang['ftp_instructs'] = "You have chosen to ftp the file to the account containing phpBB 2 automatically.  Please enter the information below to facilitate this process. Note that the FTP path should be the exact path via ftp to your phpBB2 installation as if you were ftping to it using any normal client.";
$lang['ftp_info'] = "Въведете вашата FTP информация";
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