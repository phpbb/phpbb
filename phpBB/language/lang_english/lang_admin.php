<?php
/***************************************************************************
 *                           lang_admin.php [ English ]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
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
$lang['Admin_title'] = 'Administration Panel';
$lang['No_admin'] = 'You are not authorised to administer this board';
$lang['No_frames'] = 'Sorry, your browser does not support frames';

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['Return_to'] = 'Return to ...';
$lang['General_cat'] = 'General Admin';
$lang['DB_cat'] = 'Database Admin';
$lang['Users_cat'] = 'User Admin';
$lang['Groups_cat'] = 'Group Admin';
$lang['Forums_cat'] = 'Forum Admin';
$lang['Styles_cat'] = 'Styles Admin';
$lang['Log_cat'] = 'Log Admin';

$lang['Avatar_settings'] = 'Avatar Settings';
$lang['Cookie_settings'] = 'Cookie Settings';
$lang['Board_defaults'] = 'Board Defaults';
$lang['Board_settings'] = 'Board Settings';
$lang['Email_settings'] = 'Email Settings';
$lang['Server_settings'] = 'Server Settings';
$lang['Auth_settings'] = 'Authentication';
$lang['Permissions'] = 'Permissions';
$lang['Manage'] = 'Manage';
$lang['Disallow'] = 'Disallow names';
$lang['Prune'] = 'Pruning';
$lang['Mass_Email'] = 'Mass Email';
$lang['Ranks'] = 'Ranks';
$lang['Smilies'] = 'Smilies';
$lang['Ban_users'] = 'Ban Usernames';
$lang['Ban_emails'] = 'Ban Emails';
$lang['Ban_ips'] = 'Ban IPs';
$lang['Word_Censor'] = 'Word Censors';
$lang['Export'] = 'Export';
$lang['Create_new'] = 'Create';
$lang['Add_new'] = 'Add';
$lang['DB_Backup'] = 'DB Backup';
$lang['DB_Restore'] = 'DB Restore';
$lang['Basic_Config'] = 'Basic Configuration';
$lang['Administrators'] = 'Administrators';
$lang['Admin_logs'] = 'Admin Log';
$lang['Mod_logs'] = 'Moderator Log';

$lang['Users'] = 'Users';
$lang['Groups'] = 'Groups';

$lang['Look_up_Forum'] = 'Select a Forum';

//
// Logging
//
$lang['log_index_activate'] = '<b>Activated inactive users</b> => %s users';
$lang['log_index_delete'] = '<b>Deleted inactive users</b> => %s';
$lang['log_index_remind'] = '<b>Sent reminder emails to inactive users</b> => %s users';

$lang['log_mass_email'] = '<b>Sent mass email</b> => %s';
$lang['log_delete_word'] = '<b>Deleted word censor</b>';
$lang['log_edit_word'] = '<b>Edited word censor</b> => %s';
$lang['log_add_word'] = '<b>Added word censor</b> => %s';

$lang['log_template_edit'] = '<b>Edited template</b> => %s / %s';
$lang['log_imageset_edit'] = '<b>Edited imageset</b> => %s';
$lang['log_style_edit'] = '<b>Edited style</b> => %s';
$lang['log_theme_edit'] = '<b>Edited theme</b> => %s';

$lang['log_db_backup'] = '<b>Database backup</b>';
$lang['log_db_restore'] = '<b>Database restore</b>';
$lang['log_search_index'] = '<b>Re-indexed search system</b> => %s';

$lang['log_disallow_add'] = '<b>Added disallowed username</b> => %s';
$lang['log_disallow_delete'] = '<b>Deleted disallowed username</b>';

$lang['log_prune'] = '<b>Pruned forum</b> => %s';

$lang['log_admin_clear'] = '<b>Cleared admin log</b>';

$lang['log_ban_user'] = '<b>Banned User</b> [ %s ] => %s ';
$lang['log_ban_ip'] = '<b>Banned ip</b> [ %s ] => %s';
$lang['log_ban_email'] = '<b>Banned email</b> [ %s ] => %s';
$lang['log_unban_user'] = '<b>Unbanned username</b> => %s total';
$lang['log_unban_ip'] = '<b>Unbanned ip</b> => %s total';
$lang['log_unban_email'] = '<b>Unbanned email</b> => %s total';

$lang['log_server_config'] = '<b>Altered server settings</b>';
$lang['log_default_config'] = '<b>Altered board defaults</b>';
$lang['log_setting_config'] = '<b>Altered board settings</b>';
$lang['log_cookie_config'] = '<b>Altered cookie settings</b>';
$lang['log_email_config'] = '<b>Altered email settings</b>';
$lang['log_avatar_config'] = '<b>Altered avatar settings</b>';
$lang['log_auth_config'] = '<b>Altered authentication settings</b>';

$lang['log_prune_user_deac'] = '<b>Users Deactivated</b> => %s';
$lang['log_prune_user_del_del'] = '<b>Users Pruned and Posts Deleted</b> => %s';
$lang['log_prune_user_del_anon'] = '<b>Users Pruned and Posts Retained</b> => %s';

//
// View log
//
$lang['Admin_logs_explain'] = 'This lists all the actions carried out by board administrators. You can sort by username, date, IP or action. If you have appropriate permissions you can also clear individual operations or the log as a whole.';
$lang['Mod_logs_explain'] = 'This lists the actions carried out by board moderators, select a forum from the drop down list. You can sort by username, date, IP or action. If you have appropriate permissions you can also clear individual operations or the log as a whole.';
$lang['Display_log'] = 'Display entries from previous';
$lang['All_Entries'] = 'All entries';
$lang['Sort_ip'] = 'IP address';
$lang['Sort_date'] = 'Date';
$lang['Sort_action'] = 'Log action';
$lang['No_entries'] = 'No log entries for this period';

//
// Index
//
$lang['Admin'] = 'Administration';
$lang['Not_admin'] = 'You are not authorised to administer this board';
$lang['Welcome_phpBB'] = 'Welcome to phpBB';
$lang['Admin_intro'] = 'Thank you for choosing phpBB as your forum solution. This screen will give you a quick overview of all the various statistics of your board. The links on the left hand side of this screen allow you to control every aspect of your forum experience. Each page will have instructions on how to use the tools.';
$lang['Main_index'] = 'Forum Index';
$lang['Forum_stats'] = 'Forum Statistics';
$lang['Admin_Index'] = 'Admin Index';
$lang['Preview_forum'] = 'Preview Forum';

$lang['Click_return_admin_index'] = 'Click %sHere%s to return to the Admin Index';

$lang['Statistic'] = 'Statistic';
$lang['Value'] = 'Value';
$lang['Number_posts'] = 'Number of posts';
$lang['Posts_per_day'] = 'Posts per day';
$lang['Number_topics'] = 'Number of topics';
$lang['Topics_per_day'] = 'Topics per day';
$lang['Number_users'] = 'Number of users';
$lang['Users_per_day'] = 'Users per day';
$lang['Board_started'] = 'Board started';
$lang['Avatar_dir_size'] = 'Avatar directory size';
$lang['Database_size'] = 'Database size';
$lang['Gzip_compression'] ='Gzip compression';
$lang['Not_available'] = 'Not available';

$lang['ON'] = 'ON'; // This is for GZip compression
$lang['OFF'] = 'OFF';

$lang['Inactive_users'] = 'Inactive Users';
$lang['Inactive_users_explain'] = 'This is a list of users who have registered but whos accounts are inactive. You can activate, delete or remind (by sending an email) these users if you wish.';
$lang['No_inactive_users'] = 'No inactive users';

$lang['Admin_log'] = 'Logged administrator actions';
$lang['Admin_log_index_explain'] = 'This gives an overview of the last few actions carried out by board administrators. The username, IP, time and action are all listed. A full copy of the log can be viewed from the appropriate menu item to the left';
$lang['IP'] = 'User IP';
$lang['Action'] = 'Logged action';

//
// DB Utils
//
$lang['Database_Utilities'] = 'Database Utilities';

$lang['Restore'] = 'Restore';
$lang['Backup'] = 'Backup';
$lang['Restore_explain'] = 'This will perform a full restore of all phpBB tables from a saved file. You can <u>either</u> upload the backup file via this form or upload it manually to a location on the server. If your server supports it you may use a gzip compressed text file and it will automatically be decompressed. <b>WARNING</b> This will overwrite any existing data. The restore may take a long time to process please do not move from this page till it is complete.';
$lang['Backup_explain'] = 'Here you can backup all your phpBB related data. If you have any additional custom tables in the same database with phpBB that you would like to back up as well please enter their names separated by commas in the Additional Tables textbox below. You may also store the resulting archive on the server rather than download it. Please note that this option <u>requires</u> the specified directory be writeable by the webserver. Finally, if your server supports it you may also compress the file in a number of formats.';

$lang['Backup_options'] = 'Backup options';
$lang['Backup_type'] = 'Backup type';
$lang['Start_backup'] = 'Start Backup';
$lang['Full_backup'] = 'Full';
$lang['Structure_only'] = 'Structure Only';
$lang['Data_only'] = 'Data only';
$lang['Include_search_index'] = 'Include Search Index tables';
$lang['Include_search_index_explain'] = 'Disabling this will exclude the <i>search</i> tables in full or data only backups, reducing the backup size but requiring a Search Index upon restore.';
$lang['Additional_tables'] = 'Additional tables';
$lang['Additional_tables_explain'] = 'Include any other tables you wish to backup here each seperated by a comma.';
$lang['Compress_file'] = 'Compress file';
$lang['Store_local'] = 'Store file locally';
$lang['Store_local_explain'] = 'To store the file on the server rather than download it specify a path here relative to the phpBB2 root.';

$lang['Upload_file'] = 'Upload backup file';
$lang['Select_file'] = 'Select a file';
$lang['Local_backup_file'] = 'Location of backup file';
$lang['Local_backup_file_explain'] = 'Location on the server where backup file is stored relative to the phpBB root, e.g. ../tmp/backup.sql';
$lang['Supported_extensions'] = 'Supported extensions';
$lang['Start_Restore'] = 'Start Restore';

$lang['Restore_success'] = 'The Database has been successfully restored.<br /><br />Your board should be back to the state it was when the backup was made.';
$lang['Backup_download'] = 'Your download will start shortly please wait till it begins';
$lang['Backup_writing'] = 'The backup file is being generated please wait till it completes';
$lang['Backup_success'] = 'The backup file has been created successfully in the location you specified';
$lang['Backups_not_supported'] = 'Sorry but database backups are not currently supported for your database system';

$lang['Restore_Error_filename'] = 'The file you uploaded had an unsupported extension.';
$lang['Compress_unsupported'] = 'The version of PHP installed on this server does not support the type of compression used for your backup. Please use a compression method listed on the previous page.';
$lang['Restore_Error_no_file'] = 'No file was uploaded';


//
// Auth pages
//
$lang['Permissions'] = 'Permissions';
$lang['Permissions_explain'] = 'Here you can alter which users and groups can access which forums. Permissions can be set for individual operations such as; reading, posting, voting, etc via the <i>Advanced</i> form. This page only applies to forum permissions. To assign moderators or define administrators please use the appropriate page (see left hand side menu).';

$lang['Permissions_extra_explain'] = 'Permissions are based on a; PERMIT, ALLOW, DENY, PREVENT system. By default users and groups are set to DENY access to all operations, to do anything users or groups have to be granted ALLOW access. When conflicts exist, e.g. a user having ALLOW permissions to a function belongs to a group that is set to DENY such a function the user setting takes precidence, i.e. in this case the user would be ALLOWed access to this function. Similarly a user denied access to a function will be denied even if they belong to a group that grants them access. If a user belongs to two groups one of which grants an ALLOW while another is set to DENY the user will be denied access.';
$lang['Permissions_extra2_explain'] = 'There may be times when you want to deny (or allow) access to a group no matter what their individual user settings are, this is what PERMIT and PREVENT are for. By setting a user (or more likely a group) to one of these will PERMIT (ALLOW) or PREVENT (DENY) access to a function no matter what their user settings are. You may find this useful for things such as "banned" groups, etc. doing away with any need to check for individual user permissions.';

$lang['Moderators'] = 'Moderators';
$lang['Moderators_explain'] = 'Here you can assign users and groups as forum moderators. You can give users or groups individual access to certain moderator functions as you set fit via the <i>Advanced</i> form. Moderators have additional power in a given forum and by default can post and reply even when a forum or topic is locked.';

$lang['Super_Moderators'] = 'Super Moderators';
$lang['Super_Moderators_explain'] = 'Here you can assign users and groups as super moderators. Super Moderators are like ordinary moderators accept they have access to every forum on your board. You can give users or groups individual access to certain moderator functions as you set fit via the <i>Advanced</i> form. As with moderators, super moderators have additional power in a given forum and by default can post and reply even when a forum or topic is locked.';

$lang['Administrators_explain'] = 'Here you can assign administrator rights to users or groups. All users with admin permissions can view the administration panel. However you can limit selected users or groups to only certain sections if you wish by clicking <i>Advanced</i>.';

$lang['Manage_users'] = 'Manage Users';
$lang['Add_users'] = 'Add Users';
$lang['Manage_groups'] = 'Manage Groups';
$lang['Add_groups'] = 'Add Groups';

$lang['Admin_group'] = 'Administrators';
$lang['Reg_group'] = 'All registered';

$lang['Allowed_users'] = 'Allowed users';
$lang['Disallowed_users'] = 'Disallowed users';
$lang['Allowed_groups'] = 'Allowed groups';
$lang['Disallowed_groups'] = 'Disallowed groups';

$lang['Remove_selected'] = 'Remove selected';

$lang['Advanced'] = 'Advanced';

$lang['Applies_to_User'] = 'Applies to User ...';
$lang['Applies_to_Group'] = 'Applies to Group ...';

$lang['User_can'] = 'User can ... ';
$lang['Group_can'] = 'Group can ... ';
$lang['User_can_admin'] = 'User can admin ... ';
$lang['Group_can_admin'] = 'Group can admin ... ';

$lang['Allow'] = 'Allow';
$lang['Permit'] = 'Permit';
$lang['Deny'] = 'Deny';
$lang['Prevent'] = 'Prevent';

$lang['acl_admin_general'] = 'General Settings';
$lang['acl_admin_user'] = 'Users';
$lang['acl_admin_group'] = 'Groups';
$lang['acl_admin_forum'] = 'Forums';
$lang['acl_admin_post'] = 'Posts';
$lang['acl_admin_ban'] = 'Banning';
$lang['acl_admin_auth'] = 'Permissions';
$lang['acl_admin_email'] = 'Email';
$lang['acl_admin_styles'] = 'Styles';
$lang['acl_admin_backup'] = 'Backups';
$lang['acl_admin_clearlogs'] = 'Clear Admin Log';

$lang['acl_mod_edit'] = 'Edit posts';
$lang['acl_mod_delete'] = 'Delete posts';
$lang['acl_mod_move'] = 'Move posts';
$lang['acl_mod_lock'] = 'Lock topics';
$lang['acl_mod_split'] = 'Split topics';
$lang['acl_mod_merge'] = 'Merge topics';
$lang['acl_mod_approve'] = 'Approve posts';
$lang['acl_mod_unrate'] = 'Un-rate topics';
$lang['acl_mod_auth'] = 'Set permissions';

$lang['acl_forum_list'] = 'See forum';
$lang['acl_forum_read'] = 'Read forum';
$lang['acl_forum_post'] = 'Post in forum';
$lang['acl_forum_reply'] = 'Reply to posts';
$lang['acl_forum_edit'] = 'Edit own posts';
$lang['acl_forum_delete'] = 'Delete own posts';
$lang['acl_forum_poll'] = 'Create polls';
$lang['acl_forum_vote'] = 'Vote in polls';
$lang['acl_forum_announce'] = 'Post announcements';
$lang['acl_forum_sticky'] = 'Post stickies';
$lang['acl_forum_attach'] = 'Attach files';
$lang['acl_forum_download'] = 'Download files';
$lang['acl_forum_html'] = 'Post HTML';
$lang['acl_forum_bbcode'] = 'Post BBCode';
$lang['acl_forum_smilies'] = 'Post smilies';
$lang['acl_forum_img'] = 'Post images';
$lang['acl_forum_flash'] = 'Post Flash';
$lang['acl_forum_sigs'] = 'Use signatures';
$lang['acl_forum_search'] = 'Search the forum';
$lang['acl_forum_email'] = 'Email topics';
$lang['acl_forum_rate'] = 'Rate topics';
$lang['acl_forum_print'] = 'Print topics';
$lang['acl_forum_ignoreflood'] = 'Ignore flood limit';
$lang['acl_forum_ignorequeue'] = 'Ignore mod queue';

$lang['Auth_updated'] = 'Permissions have been updated';

//
// Prune users
//
$lang['Prune_users'] = 'Prune Users';
$lang['Prune_users_explain'] = 'Here you can delete (or deactivate) users from you board. This can be done in a variety of ways; by post count, last activity, etc. Each of these criteria can be combined, i.e. you can prune users last active before 2002-01-01 with fewer than 10 posts. Alternatively you can enter a list of users directly into the text box, any criteria entered will be ignored. Take care with this facility! Once a user is deleted there is no way back.';
$lang['Select_users_explain'] = 'If you want to prune specifc users rather than use the criteria above you can enter their usernames here, one per line. Use the find username facility if you wish.';
$lang['Last_active_explain'] = 'Enter a date in yyyy-mm-dd format.';
$lang['Joined_explain'] = 'Enter a date in yyyy-mm-dd format.';

$lang['Deactivate'] = 'Deactivate';
$lang['Delete_user_posts'] = 'Delete pruned user posts';
$lang['Delete_user_posts_explain'] = 'Setting this to yes will remove all posts made by the pruned users.';

$lang['Confirm_prune_users'] = 'Are you sure you wish to prune the selected users?';
$lang['Success_user_prune'] = 'The selected users have been pruned successfully';


//
// Banning
//
$lang['Ban_explain'] = 'Here you can control the banning of users by name, IP or email address. These methods prevent a user reaching any part of the board. You can give a short (255 character) reason for the ban if you wish. This will be displayed in the admin log. The length of a ban can also be specified. If you want the ban to end on a specific date rather than after a set time period select <u>Other</u> for the ban length and enter a date in yyyy-mm-dd format.';

$lang['Ban_length'] = 'Length of ban';
$lang['Permanent'] = 'Permanent';
$lang['30_Mins'] = '30 Minutes';
$lang['1_Hour'] = '1 Hour';
$lang['6_Hours'] = '6 Hours';
$lang['Other'] = 'Other -&gt;';
$lang['Ban_reason'] = 'Reason for ban';

$lang['Ban_username_explain'] = 'You can ban multiple users in one go by entering each name on a new line. Use the <u>Find a Username</u> facility to look up and add one or more users automatically.';
$lang['Unban_username'] = 'Un-ban usernames';
$lang['Unban_username_explain'] = 'You can unban multiple users in one go using the appropriate combination of mouse and keyboard for your computer and browser';

$lang['IP_hostname'] = 'IP addresses or hostnames';
$lang['Ban_IP_explain'] = 'To specify several different IP\'s or hostnames enter each on a new line. To specify a range of IP addresses separate the start and end with a hyphen (-), to specify a wildcard use *';
$lang['Unban_IP'] = 'Un-ban IPs';
$lang['Unban_IP_explain'] = 'You can unban multiple IP addresses in one go using the appropriate combination of mouse and keyboard for your computer and browser';

$lang['Ban_email'] = 'Ban one or more email addresses';
$lang['Ban_email_explain'] = 'To specify more than one email address enter each on a new line. To match partial addresses use * as the wildcard, e.g. *@hotmail.com, *@*.domain.tld, etc.';
$lang['Unban_email'] = 'Un-ban Emails';
$lang['Unban_email_explain'] = 'You can unban multiple email addresses in one go using the appropriate combination of mouse and keyboard for your computer and browser';

$lang['No_banned_users'] = 'No banned usernames';
$lang['No_banned_ip'] = 'No banned IP addresses';
$lang['No_banned_email'] = 'No banned email addresses';

$lang['Ban_update_sucessful'] = 'The banlist has been updated successfully';


//
// Cookies
//
$lang['Cookie_settings_explain'] = 'These details define the data used to send cookies to your users browsers. In most cases the default values for the cookie settings should be sufficient. If you do need to change any do so with care, incorrect settings can prevent users logging in.';
$lang['Cookie_domain'] = 'Cookie domain';
$lang['Cookie_name'] = 'Cookie name';
$lang['Cookie_path'] = 'Cookie path';
$lang['Cookie_secure'] = 'Cookie secure';
$lang['Cookie_secure_explain'] = 'If your server is running via SSL set this to enabled else leave as disabled';
$lang['Session_length'] = 'Session length [ seconds ]';


//
// Avatars
//
$lang['Avatar_settings_explain'] = 'Avatars are generally small, unique images a user can associate with themselves. Depending on the style they are usually displayed below the username when viewing topics. Here you can determine how users can define their avatars. Please note that in order to upload avatars you need to have created the directory you name below and ensure it can be written to by the web server. Please also note that filesize limits are only imposed on uploaded avatars, they do not apply to remotely linked images.';

$lang['Allow_local'] = 'Enable gallery avatars';
$lang['Allow_remote'] = 'Enable remote avatars';
$lang['Allow_remote_explain'] = 'Avatars linked to from another website';
$lang['Allow_upload'] = 'Enable avatar uploading';
$lang['Max_filesize'] = 'Maximum Avatar File Size';
$lang['Max_filesize_explain'] = 'For uploaded avatar files';
$lang['Max_avatar_size'] = 'Maximum Avatar Dimensions';
$lang['Max_avatar_size_explain'] = '(Height x Width in pixels)';
$lang['Avatar_storage_path'] = 'Avatar Storage Path';
$lang['Avatar_storage_path_explain'] = 'Path under your phpBB root dir, e.g. images/avatars';
$lang['Avatar_gallery_path'] = 'Avatar Gallery Path';
$lang['Avatar_gallery_path_explain'] = 'Path under your phpBB root dir for pre-loaded images, e.g. images/avatars/gallery';


//
// Server
//
$lang['Server_settings_explain'] = 'Here you define server and domain dependant settings. Please ensure the data you enter is accurate, errors will result in emails containing incorrect information. When entering the domain name remember it does include http:// or other protocol term. Only alter the port number if you know your server uses a different value, port 80 is correct in most cases.';

$lang['Server_name'] = 'Domain Name';
$lang['Server_name_explain'] = 'The domain name this board runs from';
$lang['Script_path'] = 'Script path';
$lang['Script_path_explain'] = 'The path where phpBB2 is located relative to the domain name';
$lang['Server_port'] = 'Server Port';
$lang['Server_port_explain'] = 'The port your server is running on, usually 80, only change if different';


//
// Email
//
$lang['Email_settings_explain'] = 'This information is used when the board sends emails to your users. Please ensure the email address you specify is valid, any bounced or undeliverable messages will likely be sent to that address. If your host does not provide a native (PHP based) email service you can instead send messages directly using SMTP. This requires the address of an appropriate server (ask your provider if necessary), do not specify any old name here! If the server requires authentication (and only if it does) enter the necessary username and password. Please note only basic authentication is offered, different authentication implementations are not currently supported.';
$lang['Enable_email'] = 'Enable board-wide emails';
$lang['Enable_email_explain'] = 'If this is set to disabled no emails will be sent by the board at all.';
$lang['Board_email_form'] = 'Users send email via board';
$lang['Board_email_form_explain'] = 'This function keeps email addresses completely private.';
$lang['Admin_email'] = 'Admin Email Address';
$lang['Email_sig'] = 'Email Signature';
$lang['Email_sig_explain'] = 'This text will be attached to all emails the board sends';
$lang['Use_SMTP'] = 'Use SMTP Server for email';
$lang['Use_SMTP_explain'] = 'Say yes if you want or have to send email via a named server instead of the local mail function';
$lang['SMTP_server'] = 'SMTP Server Address';
$lang['SMTP_username'] = 'SMTP Username';
$lang['SMTP_username_explain'] = 'Only enter a username if your smtp server requires it';
$lang['SMTP_password'] = 'SMTP Password';
$lang['SMTP_password_explain'] = 'Only enter a password if your smtp server requires it';


//
// Board settings
//
$lang['Click_return_config'] = 'Click %sHere%s to return to General Configuration';
$lang['Board_settings_explain'] = 'Here you can determine the basic operation of your board, from the site name through user registration to private messaging.';

$lang['Site_name'] = 'Site name';
$lang['Site_desc'] = 'Site description';
$lang['Board_disable'] = 'Disable board';
$lang['Board_disable_explain'] = 'This will make the board unavailable to users. You can also enter a short (255 character) message to display if you wish.';
$lang['Limit_load'] = 'Limit system load';
$lang['Limit_load_explain'] = 'If the 1 minute system load exceeds this value the board will go offline, 1.0 equals ~100% utilisation of one processor. This only functions on UNIX based servers.';
$lang['Limit_sessions'] = 'Limit sessions';
$lang['Limit_sessions_explain'] = 'If the number of sessions exceeds this value within a one minute period the board will go offline. Set to 0 for unlimited sessions.';

$lang['Acct_activation'] = 'Enable account activation';
$lang['Acct_activation_explain'] = 'This determines whether users have immediate access to the board or if confirmation is required. You can also completely disable new registrations.';
$lang['Acc_None'] = 'None'; // These three entries are the type of activation
$lang['Acc_User'] = 'User';
$lang['Acc_Admin'] = 'Admin';
$lang['Acc_Disable'] = 'Disable';
$lang['Enable_gzip'] = 'Enable GZip Compression';
$lang['Enable_prune'] = 'Enable Forum Pruning';
$lang['Enable_COPPA'] = 'Enable COPPA';
$lang['Enable_COPPA_explain'] = 'This requires users to declare whether they are 13 or over for compliance with the U.S. COPPA act.';
$lang['COPPA_fax'] = 'COPPA Fax Number';
$lang['COPPA_mail'] = 'COPPA Mailing Address';
$lang['COPPA_mail_explain'] = 'This is the mailing address where parents will send COPPA registration forms';
$lang['Boxes_max'] = 'Max number of message boxes';
$lang['Boxes_max_explain'] = 'Users can create this many private messaging boxes.';
$lang['Boxes_limit'] = 'Max messages per box';
$lang['Boxes_limit_explain'] = 'Users are limited to no more than this many messages in each of their private message boxes.';
$lang['Flood_Interval'] = 'Flood Interval';
$lang['Flood_Interval_explain'] = 'Number of seconds a user must wait between posting new messages. To enable users to ignore this alter their permissions.';
$lang['Search_Interval'] = 'Search Flood Interval';
$lang['Search_Interval_explain'] = 'Number of seconds users must wait between searches.';
$lang['Min_search_chars'] = 'Min characters indexed by search';
$lang['Min_search_chars_explain'] = 'Words with at least this many characters will be indexed for searching.';
$lang['Max_search_chars'] = 'Max characters indexed by search';
$lang['Max_search_chars_explain'] = 'Words with no more than this many characters will be indexed for searching.';


//
// Authentication methods
//
$lang['Auth_settings_explain'] = 'phpBB2 supports authentication plug-ins, or modules. These allow you determine how users are authenticated when they log into the board. By default three plug-ins are provided; DB, LDAP and Apache. Not all methods require additional information so only fill out fields if they are relevant to the selected method.';

$lang['Auth_method'] = 'Select an authentication method';
$lang['LDAP_server'] = 'LDAP server name';
$lang['LDAP_server_explain'] = 'If using LDAP this is the name or IP address of the server.';
$lang['LDAP_dn'] = 'LDAP base dn';
$lang['LDAP_dn_explain'] = 'This is the <i>distinguished name</i>, locating the user information, e.g. o=My Company,c=US';
$lang['LDAP_uid'] = 'LDAP uid';
$lang['LDAP_uid_explain'] = 'This is the key under which to search for a given login identify, e.g. uid, sn, etc.';

//
// Board defaults
//
$lang['Board_defaults_explain'] = 'These settings allow you to define a number of default or global settings used by the board. For example, to disable the use of HTML across the entire board alter the relevant setting below. This data is also used for new user registrations and (where relevant) guest users.';

$lang['Max_poll_options'] = 'Max number of poll options';
$lang['Topics_per_page'] = 'Topics Per Page';
$lang['Posts_per_page'] = 'Posts Per Page';
$lang['Hot_threshold'] = 'Posts for Popular Threshold';
$lang['Default_style'] = 'Default Style';
$lang['Override_style'] = 'Override user style';
$lang['Override_style_explain'] = 'Replaces users style with the default';
$lang['Default_language'] = 'Default Language';
$lang['Date_format'] = 'Date Format';
$lang['System_timezone'] = 'System Timezone';

$lang['Char_limit'] = 'Max characters per post';
$lang['Char_limit_explain'] = 'Set to 0 for unlimited characters.';
$lang['Allow_topic_notify'] = 'Allow Topic Watching';
$lang['Allow_forum_notify'] = 'Allow Forum Watching';
$lang['Allow_HTML'] = 'Allow HTML';
$lang['Allow_BBCode'] = 'Allow BBCode';
$lang['Allowed_tags'] = 'Allowed HTML tags';
$lang['Allowed_tags_explain'] = 'Separate tags with commas';
$lang['Allow_smilies'] = 'Allow Smilies';
$lang['Smilies_path'] = 'Smilies storage path';
$lang['Smilies_path_explain'] = 'Path under your phpBB root dir, e.g. images/smilies';
$lang['Smilies_limit'] = 'Max smilies per post';
$lang['Smilies_limit_explain'] = 'Set to 0 for unlimited smilies.';
$lang['Icons_path'] = 'Post icons storage path';
$lang['Icons_path_explain'] = 'Path under your phpBB root dir, e.g. images/icons';
$lang['Allow_sig'] = 'Allow Signatures';
$lang['Max_sig_length'] = 'Maximum signature length';
$lang['Max_sig_length_explain'] = 'Maximum number of characters in user signatures';
$lang['Allow_name_change'] = 'Allow Username changes';


//
// Forum Management
//
$lang['Forum_admin'] = 'Forum Administration';
$lang['Forum_admin_explain'] = 'From this panel you can add, delete, edit, re-order and re-synchronise categories and forums';
$lang['Edit_forum'] = 'Edit forum';
$lang['Edit_category'] = 'Edit category';
$lang['Create_forum'] = 'Create new forum';
$lang['Remove'] = 'Remove';
$lang['Action'] = 'Action';
$lang['Config_updated'] = 'Forum configuration updated successfully';
$lang['Edit'] = 'Edit';
$lang['Delete'] = 'Delete';
$lang['Move_up'] = 'Move up';
$lang['Move_down'] = 'Move down';
$lang['Resync'] = 'Resync';

$lang['Category_name'] = 'Category name';
$lang['Forum_type'] = 'Forum type';

$lang['Parent'] = 'Parent';
$lang['Locked'] = 'Locked';
$lang['Unlocked'] = 'Unlocked';

$lang['General_settings'] = 'General settings';
$lang['Forum_settings'] = 'Forum settings';
$lang['Disable_post_count'] = 'Disable post count';

$lang['Forum_edit_delete_explain'] = 'The form below will allow you to customize all the general board options. For User and Forum configurations use the related links on the left hand side';

$lang['Forum_general'] = 'General Forum Settings';
$lang['Forum_name'] = 'Forum name';
$lang['Forum_desc'] = 'Description';
$lang['Forum_status'] = 'Forum status';
$lang['Forum_pruning'] = 'Auto-pruning';

$lang['prune_freq'] = 'Check for topic age every';
$lang['prune_days'] = 'Remove topics that have not been posted to in';

$lang['Set_as_category'] = 'Set this forum as a category and'; // followed by a list of actions

$lang['Forum_delete'] = 'Delete Forum';
$lang['Forum_delete_explain'] = 'The form below will allow you to delete a forum (or category) and decide where you want to put all topics (or forums) it contained.';

$lang['Move_and_Delete'] = 'Move and Delete';
$lang['Move_posts_to'] = 'Move posts to';
$lang['Move_subforums_to'] = 'Move subforums to';
$lang['Delete_all_posts'] = 'Delete all posts';
$lang['Delete_subforums'] = 'Delete subforums and associated posts';

$lang['Forums_updated'] = 'Forum and Category information updated successfully';
$lang['Click_return_forumadmin'] = 'Click %sHere%s to return to Forum Administration';


//
// Smiley Management
//
$lang['Emoticons_explain'] = 'From this page you can add, remove and edit the emoticons or smileys your users may use in their posts and private messages.';

$lang['smiley_config'] = 'Smiley Configuration';
$lang['smiley_code'] = 'Smiley Code';
$lang['smiley_url'] = 'Smiley Image File';
$lang['smiley_emot'] = 'Smiley Emotion';
$lang['smile_add'] = 'Add a new Smiley';
$lang['Smile'] = 'Smile';
$lang['Emotion'] = 'Emotion';

$lang['Select_pak'] = 'Select Pack (.pak) File';
$lang['replace_existing'] = 'Replace Existing Smiley';
$lang['keep_existing'] = 'Keep Existing Smiley';
$lang['smiley_import_inst'] = 'You should unzip the smiley package and upload all files to the appropriate Smiley directory for your installation.  Then select the correct information in this form to import the smiley pack.';
$lang['smiley_import'] = 'Smiley Pack Import';
$lang['choose_smile_pak'] = 'Choose a Smile Pack .pak file';
$lang['import'] = 'Import Smileys';
$lang['smile_conflicts'] = 'What should be done in case of conflicts';
$lang['del_existing_smileys'] = 'Delete existing smileys before import';
$lang['import_smile_pack'] = 'Import Smiley Pack';
$lang['export_smile_pack'] = 'Create Smiley Pack';
$lang['export_smiles'] = 'To create a smiley pack from your currently installed smileys, click %sHere%s to download the smiles.pak file. Name this file appropriately making sure to keep the .pak file extension.  Then create a zip file containing all of your smiley images plus this .pak configuration file.';

$lang['smiley_add_success'] = 'The Smiley was successfully added';
$lang['smiley_edit_success'] = 'The Smiley was successfully updated';
$lang['smiley_import_success'] = 'The Smiley Pack was imported successfully!';
$lang['smiley_del_success'] = 'The Smiley was successfully removed';
$lang['Click_return_smileadmin'] = 'Click %sHere%s to return to Smiley Administration';


//
// User Management
//
$lang['User_admin'] = 'User Administration';
$lang['User_admin_explain'] = 'Here you can change your user\'s information and certain specific options. To modify the users permissions please use the user and group permissions system.';

$lang['Look_up_user'] = 'Look up user';

$lang['Admin_user_updated'] = 'The users profile was successfully updated.';
$lang['Click_return_useradmin'] = 'Click %sHere%s to return to User Administration';

$lang['User_delete'] = 'Delete this user';
$lang['User_delete_explain'] = 'Click here to delete this user, this cannot be undone.';
$lang['User_deleted'] = 'User was successfully deleted.';

$lang['User_status'] = 'User is active';
$lang['User_allowpm'] = 'Can send Private Messages';
$lang['User_allowavatar'] = 'Can display avatar';

$lang['Admin_avatar_explain'] = 'Here you can see and delete the users current avatar.';

$lang['User_special'] = 'Special admin-only fields';
$lang['User_special_explain'] = 'These fields are not able to be modified by the users.  Here you can set their status and other options that are not given to users.';


//
// Group Management
//
$lang['Group_administration'] = 'Group Administration';
$lang['Group_admin_explain'] = 'From this panel you can administer all your usergroups, you can; delete, create and edit existing groups. You may choose moderators, toggle open/closed group status and set the group name and description';
$lang['Error_updating_groups'] = 'There was an error while updating the groups';
$lang['Updated_group'] = 'The group was successfully updated';
$lang['Added_new_group'] = 'The new group was successfully created';
$lang['Deleted_group'] = 'The group was successfully deleted';
$lang['New_group'] = 'Create new group';
$lang['Edit_group'] = 'Edit group';
$lang['group_name'] = 'Group name';
$lang['group_description'] = 'Group description';
$lang['group_moderator'] = 'Group moderator';
$lang['group_status'] = 'Group status';
$lang['group_open'] = 'Open group';
$lang['group_closed'] = 'Closed group';
$lang['group_hidden'] = 'Hidden group';
$lang['group_delete'] = 'Delete group';
$lang['group_delete_check'] = 'Delete this group';
$lang['submit_group_changes'] = 'Submit Changes';
$lang['reset_group_changes'] = 'Reset Changes';
$lang['No_group_name'] = 'You must specify a name for this group';
$lang['No_group_moderator'] = 'You must specify a moderator for this group';
$lang['No_group_mode'] = 'You must specify a mode for this group, open or closed';
$lang['delete_group_moderator'] = 'Delete the old group moderator?';
$lang['delete_moderator_explain'] = 'If you are changing the group moderator, check this box to remove the old moderator from the group.  Otherwise, do not check it, and the user will become a regular member of the group.';
$lang['Click_return_groupsadmin'] = 'Click %sHere%s to return to Group Administration.';
$lang['Select_group'] = 'Select a group';
$lang['Look_up_group'] = 'Look up group';


//
// Prune Administration
//
$lang['Forum_Prune'] = 'Forum Prune';
$lang['Forum_Prune_explain'] = 'This will delete any topic which has not been posted to within the number of days you select. If you do not enter a number then all topics will be deleted. It will not remove topics in which polls are still running nor will it remove announcements. You will need to remove these topics manually.';
$lang['Do_Prune'] = 'Do Prune';
$lang['All_Forums'] = 'All Forums';
$lang['Prune_topics_not_posted'] = 'Prune topics with no replies in this many days';
$lang['Topics_pruned'] = 'Topics pruned';
$lang['Posts_pruned'] = 'Posts pruned';
$lang['Prune_success'] = 'Pruning of forums was successful';


//
// Word censor
//
$lang['Words_title'] = 'Word Censoring';
$lang['Words_explain'] = 'From this control panel you can add, edit, and remove words that will be automatically censored on your forums. In addition people will not be allowed to register with usernames containing these words. Wildcards (*) are accepted in the word field, eg. *test* will match detestable, test* would match testing, *test would match detest.';
$lang['Word'] = 'Word';
$lang['Edit_word_censor'] = 'Edit word censor';
$lang['Replacement'] = 'Replacement';
$lang['Add_new_word'] = 'Add new word';
$lang['Update_word'] = 'Update word censor';

$lang['Must_enter_word'] = 'You must enter a word and its replacement';
$lang['No_word_selected'] = 'No word selected for editing';

$lang['Word_updated'] = 'The selected word censor has been successfully updated';
$lang['Word_added'] = 'The word censor has been successfully added';
$lang['Word_removed'] = 'The selected word censor has been successfully removed';


//
// Mass Email
//
$lang['Mass_email_explain'] = 'Here you can email a message to either all of your users, or all users of a specific group.  To do this, an email will be sent out to the administrative email address supplied, with a blind carbon copy sent to all recipients. If you are emailing a large group of people please be patient after submitting and do not stop the page halfway through. It is normal for a mass emailing to take a long time, you will be notified when the script has completed';
$lang['Compose'] = 'Compose';

$lang['Recipients'] = 'Recipients';
$lang['All_users'] = 'All Users';

$lang['Email_successfull'] = 'Your message has been sent';


//
// Ranks admin
//
$lang['Ranks_explain'] = 'Using this form you can add, edit, view and delete ranks. You can also create custom ranks which can be applied to a user via the user management facility';

$lang['Add_new_rank'] = 'Add new rank';

$lang['Rank_title'] = 'Rank Title';
$lang['Rank_special'] = 'Set as Special Rank';
$lang['Rank_minimum'] = 'Minimum Posts';
$lang['Rank_image'] = 'Rank Image';
$lang['Rank_image_explain'] = 'Use this to define a small image associated with the rank. The path is relative to the main phpBB2 directory.';

$lang['Must_select_rank'] = 'You must select a rank';
$lang['No_assigned_rank'] = 'No special rank assigned';

$lang['Rank_updated'] = 'The rank was successfully updated';
$lang['Rank_added'] = 'The rank was successfully added';
$lang['Rank_removed'] = 'The rank was successfully deleted';
$lang['No_update_ranks'] = 'The rank was successfully deleted, however, user accounts using this rank were not updated.  You will need to manually reset the rank on these accounts';


//
// Disallow Username Admin
//
$lang['Disallow_control'] = 'Username Disallow Control';
$lang['Disallow_explain'] = 'Here you can control usernames which will not be allowed to be used.  Disallowed usernames are allowed to contain a wildcard character of *.  Please note that you will not be allowed to specify any username that has already been registered, you must first delete that name then disallow it';

$lang['Delete_disallow'] = 'Delete';
$lang['Delete_disallow_title'] = 'Remove a Disallowed Username';
$lang['Delete_disallow_explain'] = 'You can remove a disallowed username by selecting the username from this list and clicking submit';

$lang['Add_disallow'] = 'Add';
$lang['Add_disallow_title'] = 'Add a disallowed username';
$lang['Add_disallow_explain'] = 'You can disallow a username using the wildcard character * to match any character';

$lang['No_disallowed'] = 'No Disallowed Usernames';

$lang['Disallowed_deleted'] = 'The disallowed username has been successfully removed';
$lang['Disallow_successful'] = 'The disallowed username has been successfully added';
$lang['Disallowed_already'] = 'The name you entered could not be disallowed. It either already exists in the list, exists in the word censor list, or a matching username is present';


//
// Styles Admin
//
$lang['Edit_style'] = 'Edit Styles';
$lang['Style'] = 'Style';
$lang['Styles_admin'] = 'Styles Administration';
$lang['Styles_explain'] = 'Using this facility you can add, remove and manage styles. Styles are a combination of a template, theme (CSS) and imageset.';

$lang['Edit_template'] = 'Edit Template';
$lang['Edit_template_explain'] = 'Use this panel to edit an existing compiled template set. When you have made the required changes you can recompile the template and (or) download it. Please remember that the existing HTML templates are <b>not</b> altered, only the compiled versions are affected. Therefore you should download any altered files if you wish to keep them for future use and for archival purposes.';
$lang['Select_template'] = 'Select template';
$lang['Template'] = 'Select template';
$lang['Download'] = 'Download';

$lang['Edit_theme'] = 'Edit Theme';
$lang['Edit_theme_explain'] = 'Use this panel to edit an existing theme. You can modify (or add) both CSS to be included within each page output by the forum (subject to the template including it) and an externally linked stylesheet. Remember, the location of the stylesheet is relative to the phpBB root directory.';
$lang['Select_theme'] = 'Select theme';
$lang['CSS_data'] = 'CSS Data';
$lang['CSS_data_explain'] = 'This CSS is output to the template and may be included within the header of each page.';
$lang['CSS_sheet'] = 'CSS Stylesheet';
$lang['Success_theme_update'] = 'The theme has been successfully updated.';

$lang['Edit_imageset'] = 'Edit Imageset';
$lang['Edit_imageset_explain'] = '';




$lang['Create_theme'] = 'Create Theme';
$lang['Create_theme_explain'] = 'Use the form below to create a new theme for a selected template. When entering colours (for which you should use hexadecimal notation) you must not include the initial #, i.e.. CCCCCC is valid, #CCCCCC is not';

$lang['Export_themes'] = 'Export Themes';
$lang['Export_explain'] = 'In this panel you will be able to export the theme data for a selected template. Select the template from the list below and the script will create the theme configuration file and attempt to save it to the selected template directory. If it cannot save the file itself it will give you the option to download it. In order for the script to save the file you must give write access to the webserver for the selected template dir. For more information on this see the phpBB 2 users guide.';

$lang['Theme_installed'] = 'The selected theme has been installed successfully';
$lang['Style_removed'] = 'The selected style has been removed from the database. To fully remove this style from your system you must delete the appropriate style from your templates directory.';
$lang['Theme_info_saved'] = 'The theme information for the selected template has been saved. You should now return the permissions on the theme_info.cfg (and if applicable the selected template directory) to read-only';
$lang['Theme_updated'] = 'The selected theme has been updated. You should now export the new theme settings';
$lang['Theme_created'] = 'Theme created. You should now export the theme to the theme configuration file for safe keeping or use elsewhere';

$lang['Confirm_delete_style'] = 'Are you sure you want to delete this style';

$lang['Download_theme_cfg'] = 'The exporter could not write the theme information file. Click the button below to download this file with your browser. Once you have downloaded it you can transfer it to the directory containing the template files. You can then package the files for distribution or use elsewhere if you desire';
$lang['No_themes'] = 'The template you selected has no themes attached to it. To create a new theme click the Create New link on the left hand panel';
$lang['No_template_dir'] = 'Could not open the template directory. It may be unreadable by the webserver or may not exist';
$lang['Cannot_remove_style'] = 'You cannot remove the style selected since it is currently the forum default. Please change the default style and try again.';
$lang['Style_exists'] = 'The style name to selected already exists, please go back and choose a different name.';

$lang['Click_return_styleadmin'] = 'Click %sHere%s to return to Style Administration';

$lang['Value'] = 'Value';
$lang['Save_Settings'] = 'Save Settings';

//
// Install Process
//
$lang['Welcome_install'] = 'Welcome to phpBB 2 Installation';
$lang['Initial_config'] = 'Basic Configuration';
$lang['DB_config'] = 'Database Configuration';
$lang['Admin_config'] = 'Admin Configuration';
$lang['continue_upgrade'] = 'Once you have downloaded your config file to your local machine you may\'Continue Upgrade\' button below to move forward with the upgrade process.  Please wait to upload the config file until the upgrade process is complete.';
$lang['upgrade_submit'] = 'Continue Upgrade';

$lang['Installer_Error'] = 'An error has occurred during installation';
$lang['Previous_Install'] = 'A previous installation has been detected';
$lang['Install_db_error'] = 'An error occurred trying to update the database';

$lang['Re_install'] = 'Your previous installation is still active. <br /><br />If you would like to re-install phpBB 2 you should click the Yes button below. Please be aware that doing so will destroy all existing data, no backups will be made! The administrator username and password you have used to login in to the board will be re-created after the re-installation, no other settings will be retained. <br /><br />Think carefully before pressing Yes!';

$lang['Inst_Step_0'] = 'Thank you for choosing phpBB 2. In order to complete this install please fill out the details requested below. Please note that the database you install into should already exist. If you are installing to a database that uses ODBC, e.g. MS Access you should first create a DSN for it before proceeding.';

$lang['Start_Install'] = 'Start Install';
$lang['Finish_Install'] = 'Finish Installation';

$lang['Default_lang'] = 'Default board language';
$lang['DB_Host'] = 'Database Server Hostname / DSN';
$lang['DB_Name'] = 'Your Database Name';
$lang['DB_Username'] = 'Database Username';
$lang['DB_Password'] = 'Database Password';
$lang['Database'] = 'Your Database';
$lang['Install_lang'] = 'Choose Language for Installation';
$lang['dbms'] = 'Database Type';
$lang['Table_Prefix'] = 'Prefix for tables in database';
$lang['Admin_Username'] = 'Administrator Username';
$lang['Admin_Password'] = 'Administrator Password';
$lang['Admin_Password_confirm'] = 'Administrator Password [ Confirm ]';

$lang['Inst_Step_2'] = 'Your admin username has been created.  At this point your basic installation is complete. You will now be taken to a screen which will allow you to administer your new installation. Please be sure to check the General Configuration details and make any required changes. Thank you for choosing phpBB 2.';

$lang['Unwriteable_config'] = 'Your config file is un-writeable at present. A copy of the config file will be downloaded to your when you click the button below. You should upload this file to the same directory as phpBB 2. Once this is done you should log in using the administrator name and password you provided on the previous form and visit the admin control centre (a link will appear at the bottom of each screen once logged in) to check the general configuration. Thank you for choosing phpBB 2.';
$lang['Download_config'] = 'Download Config';

$lang['ftp_choose'] = 'Choose Download Method';
$lang['ftp_option'] = '<br />Since FTP extensions are enabled in this version of PHP you may also be given the option of first trying to automatically ftp the config file into place.';
$lang['ftp_instructs'] = 'You have chosen to ftp the file to the account containing phpBB 2 automatically.  Please enter the information below to facilitate this process. Note that the FTP path should be the exact path via ftp to your phpBB2 installation as if you were ftping to it using any normal client.';
$lang['ftp_info'] = 'Enter Your FTP Information';
$lang['Attempt_ftp'] = 'Attempt to ftp config file into place';
$lang['Send_file'] = 'Just send the file to me and I will ftp it manually';
$lang['ftp_path'] = 'FTP path to phpBB 2';
$lang['ftp_username'] = 'Your FTP Username';
$lang['ftp_password'] = 'Your FTP Password';
$lang['Transfer_config'] = 'Start Transfer';
$lang['NoFTP_config'] = 'The attempt to ftp the config file into place failed.  Please download the config file and ftp it into place manually.';

$lang['Install'] = 'Install';
$lang['Upgrade'] = 'Upgrade';

$lang['Install_Method'] = 'Choose your installation method';
$lang['Install_No_PHP4'] = 'phpBB2 requires you have at least PHP 4.0.4 installed<br /><br />Contact your hosting provider or see <a href="http://www.php.net/">www.php.net</a> for more information';
$lang['Install_No_Ext'] = 'The PHP configuration on your server does not support the database type that you choose<br /><br />Contact your hosting provider or see <a href="http://www.php.net/">www.php.net</a> for more information';
$lang['Install_No_PCRE'] = 'phpBB2 requires the Perl-Compatible Regular Expressions module for PHP to be available<br /><br />Contact your hosting provider or see <a href="http://www.php.net/">www.php.net</a> for more information';


//
// Search re-indexing
//
$lang['Search_indexing'] = 'Search Indexing';
$lang['Search_indexing_explain'] = 'phpBB2 uses a fulltext search system. This breaks down each post into seperate words and then, if the word does not already exist it stores those words in a table. In turn the post is linked to each word it contains in this table. This allows quick searching of large databases and helps reduce load on the server compared to most other methods.</p><p>However, if the tables get out of sync for some reason or you change the minimum, maximum or disallowed list of words the tables need updating. This facility allows you to do just that.</p><p>Please be aware this procedure can take a long time, particularly on large databases. During this period your forum will be automatically shut down to prevent people posting. You can cancel the procedure at any time. Please remember this is an intensive operation and should only be carried out when absolutely necessarily. Do not run this script too often!</p>';
$lang['Search_indexing_cancel'] = 'Re-indexing of search system has been cancelled. Please note this will result in searches returning incomplete results. You can re-index the posts again at any stage.';
$lang['Search_indexing_complete'] = 'Re-indexing of search system has been completed. You can re-index the posts again at any stage.';

$lang['Start'] = 'Start';
$lang['Stop'] = 'Stop';

//
// That's all Folks!
// -------------------------------------------------

?>