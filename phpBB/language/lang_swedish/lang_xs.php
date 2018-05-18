<?php

/***************************************************************************
 *                                lang_xs.php
 *                                -----------
 *   copyright            : (C) 2003 - 2005 CyberAlien
 *   support              : http://www.phpbbstyles.com
 *
 *   version              : 2.3.1
 *
 *   file revision        : 75
 *   project revision     : 78
 *   last modified        : 05 Dec 2005  13:54:54
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


$lang['Extreme_Styles'] = 'eXtreme Styles';
$lang['xs_title'] = 'eXtreme Styles mod';

$lang['xs_file'] = 'File';
$lang['xs_template'] = 'Template';
$lang['xs_id'] = 'ID';
$lang['xs_style'] = 'Style';
$lang['xs_styles'] = 'Styles';
$lang['xs_users'] = 'Users';
$lang['xs_options'] = 'Options';
$lang['xs_comment'] = 'Comment';
$lang['xs_upload_time'] = 'Upload Time';
$lang['xs_select'] = 'Select';

$lang['xs_continue'] = 'Continue';	// button

$lang['xs_click_here_lc'] = 'click here';
$lang['xs_edit_lc'] = 'edit';

/*
* navigation
*/
$lang['xs_config_shownav'] = array(
	'Configuration',
	'Install Styles',
	'Uninstall Styles',
	'Default Style',
	'Manage Cache',
	'Import Styles',
	'Export Styles',
	'Clone Styles',
	'Download Styles',
	'Edit Templates',
	'Edit Styles',
	'Export Database',
	'Check Updates',
	);

/*
* frame_top.tpl
*/
$lang['xs_menu_lc'] = 'extreme styles mod menu';
$lang['xs_support_forum_lc'] = 'support forum';
$lang['xs_download_styles_lc'] = 'download styles';
$lang['xs_install_styles_lc'] = 'install styles';

/*
* index.tpl
*/

$lang['xs_main_comment1'] = 'This is the eXtreme Styles mod main menu. There are quite a few functions within this interface, so this page is here as a guide. There is a short explanation of every function below the function name.<br /><br />Note: This mod replaces the phpBB styles management. You will find the default phpBB functions in this list, but these functions are now optimized and have extra features.<br /><br />If you have any questions please visit <a href="http://www.phpbbstyles.com" target="_blank">support forum</a> where you can get assistance for this mod.';
$lang['xs_main_comment2'] = 'The eXtreme Styles mod allows an admin to store entire styles in .style files. Styles are stored in a small compressed file and by doing so this saves the trouble of downloading/uploading many files. Style files are compressed so download/upload is much more efficient than downloading/uploading usual style files.';
$lang['xs_main_comment3'] = 'All functions of phpBB styles management are replaced with eXtreme Styles mod.<br /><br /><a href="{URL}">Click here</a> to see menu.';
$lang['xs_main_title'] = 'eXtreme Styles Navigation Menu';
$lang['xs_menu'] = 'eXtreme Styles Menu';

$lang['xs_manage_styles'] = 'Manage Styles';
$lang['xs_import_export_styles'] = 'Import/Export Styles';
$lang['xs_install_uninstall_styles'] = 'Install/Uninstall Styles';
$lang['xs_edit_templates'] = 'Edit Templates';
$lang['xs_other_functions'] = 'Other Functions';

$lang['xs_configuration'] = 'Configuration';
$lang['xs_configuration_explain'] = 'This feature allows you to change the eXtreme Styles configuration.';
$lang['xs_default_style'] = 'Default Style';
$lang['xs_default_style_explain'] = 'This feature allows you to change the default forum style and switch users from one style to another.';
$lang['xs_manage_cache'] = 'Manage Cache';
$lang['xs_manage_cache_explain'] = 'This feature allows you to manage cached files.';
$lang['xs_import_styles'] = 'Import Styles';
$lang['xs_import_styles_explain'] = 'This feature allows you to download and install .style files.';
$lang['xs_export_styles'] = 'Export Styles';
$lang['xs_export_styles_explain'] = 'This feature allows you to save a style from your forum as a .style file and then easily transfer it to another forum or another website.';
$lang['xs_clone_styles'] = 'Clone Styles';
$lang['xs_clone_styles_explain'] = 'This feature allows you to quickly clone styles or a whole template.';
$lang['xs_download_styles'] = 'Download Styles';
$lang['xs_download_styles_explain'] = 'This feature allows you to quickly download and install styles from websites. You can configure a list of websites yourself.';
$lang['xs_install_styles'] = 'Install Styles';
$lang['xs_install_styles_explain'] = 'This feature allows you to install styles that are already uploaded to your forum.';
$lang['xs_uninstall_styles'] = 'Uninstall Styles';
$lang['xs_uninstall_styles_explain'] = 'This feature allows you to remove styles from your forum.';
$lang['xs_edit_templates_explain'] = 'This feature allows you to edit tpl files online.';
$lang['xs_edit_styles_data'] = 'Edit Styles Data';
$lang['xs_edit_styles_data_explain'] = 'This feature allows you to edit style variables. It is used by some styles, but most styles don\'t use it and use a css file instead.';
$lang['xs_export_styles_data'] = 'Export Styles Data';
$lang['xs_export_styles_data_explain'] = 'This feature allows you to save style variables to theme_info.cfg.';
$lang['xs_check_for_updates'] = 'Check for Updates';
$lang['xs_check_for_updates_explain'] = 'This feature allows you to check for updated versions of styles and mods installed on your forum.';

$lang['xs_set_configuration_lc'] = 'set configuration';
$lang['xs_set_default_style_lc'] = 'set default style';
$lang['xs_manage_cache_lc'] = 'manage cache';
$lang['xs_import_styles_lc'] = 'import styles';
$lang['xs_export_styles_lc'] = 'export styles';
$lang['xs_clone_styles_lc'] = 'clone styles';
$lang['xs_uninstall_styles_lc'] = 'uninstall styles';
$lang['xs_edit_templates_lc'] = 'edit templates';
$lang['xs_edit_styles_data_lc'] = 'edit styles data';
$lang['xs_export_styles_data_lc'] = 'export styles data';
$lang['xs_check_for_updates_lc'] = 'check for updates';

/*
* ftp.tpl, ftp functions
*/

$lang['xs_ftp_comment1'] = 'To use this feature you must select the file upload method. If you select FTP, then a password will not be stored and eXtreme Styles will ask you for a password every time you select functions that requires FTP access. If you select local file system then make sure all required directories are writeable.';
$lang['xs_ftp_comment2'] = 'To use this feature you must set FTP settings. A password will not be stored and eXtreme Styles will ask you for a password every time you select functions that requires FTP access.';
$lang['xs_ftp_comment3'] = 'Warning: FTP functions are disabled on this server. You will not be able to use eXtreme Styles functionality that require FTP access.';

$lang['xs_ftp_title'] = 'FTP Configuration';

$lang['xs_ftp_explain'] = 'FTP is used to upload new styles. If you want to use the import styles feature then you should configure FTP settings accordingly. eXtreme Styles tries to auto-detect settings if and when possible.';

$lang['xs_ftp_error_fatal'] = 'FTP functions are disabled on this server. Cannot continue.';
$lang['xs_ftp_error_connect'] = 'FTP error: cannot connect to {HOST}';
$lang['xs_ftp_error_login'] = 'FTP error: cannot login';
$lang['xs_ftp_error_chdir'] = 'FTP error: cannot change directory to {DIR}';
$lang['xs_ftp_error_nonphpbbdir'] = 'FTP error: you have set invalid directory. There are no phpBB files in that directory';
$lang['xs_ftp_error_noconnect'] = 'Cannot connect to ftp server';
$lang['xs_ftp_error_login2'] = 'Invalid ftp login or password';

$lang['xs_ftp_log_disabled'] = 'ftp functions are disabled on this server. script cannot continue.';
$lang['xs_ftp_log_connecting'] = 'connecting to {HOST}';
$lang['xs_ftp_log_noconnect'] = 'cannot connect to {HOST}';
$lang['xs_ftp_log_connected'] = 'connected. loggin in...';
$lang['xs_ftp_log_nologin'] = 'cannot login as {USER}';
$lang['xs_ftp_log_loggedin'] = 'logged in';
$lang['xs_ftp_log_end'] = 'finished executing script';
$lang['xs_ftp_log_nopwd'] = 'error: cannot retrieve current directory';
$lang['xs_ftp_log_nomkdir'] = 'error: cannot create directory {DIR}';
$lang['xs_ftp_log_mkdir'] = 'created directory {DIR}';
$lang['xs_ftp_log_nochdir'] = 'error: cannot change directory to {DIR}';
$lang['xs_ftp_log_normdir'] = 'error: cannot remove directory {DIR}';
$lang['xs_ftp_log_rmdir'] = 'removed directory {DIR}';
$lang['xs_ftp_log_chdir'] = 'changed directory to {DIR}';
$lang['xs_ftp_log_noupload'] = 'error: cannot upload file {FILE}';
$lang['xs_ftp_log_upload'] = 'uploaded file {FILE}';
$lang['xs_ftp_log_nochmod'] = 'warning: cannot chmod file {FILE}';
$lang['xs_ftp_log_chmod'] = 'chmod file {FILE} to {MODE}';
$lang['xs_ftp_log_invalidcommand'] = 'error: unknown command: {COMMAND}';
$lang['xs_ftp_log_chdir2'] = 'changing current directory back to {DIR}';
$lang['xs_ftp_log_nochdir2'] = 'cannot change directory to {DIR}';

$lang['xs_ftp_config'] = 'FTP Configuration';
$lang['xs_ftp_select_method'] = 'Select upload method';
$lang['xs_ftp_select_local'] = 'Use local file system (no configuration required)';
$lang['xs_ftp_select_ftp'] = 'Use FTP (set ftp settings below)';

$lang['xs_ftp_settings'] = 'FTP Settings';
$lang['xs_ftp_host'] = 'FTP Host';
$lang['xs_ftp_login'] = 'FTP Login';
$lang['xs_ftp_path'] = 'FTP Path to phpBB';
$lang['xs_ftp_pass'] = 'FTP Password';
$lang['xs_ftp_remotedir'] = 'Remote Directory';

$lang['xs_ftp_host_guess'] = ' (probably "{HOST}" [<a href="javascript: void(0)" onclick="{CLICK}">set host</a>])';
$lang['xs_ftp_login_guess'] = ' (probably "{LOGIN}" [<a href="javascript: void(0)" onclick="{CLICK}">set host</a>])';
$lang['xs_ftp_path_guess'] = ' (probably "{PATH}" [<a href="javascript: void(0)" onclick="{CLICK}">set path</a>])';


/*
* config.tpl
*/

$lang['xs_config_updated'] = 'Configuration updated.';
$lang['xs_config_updated_explain'] = 'You need to refresh this page before the new configuration can take effect. <a href="{URL}">Click here</a> to refresh page.';
$lang['xs_config_warning'] = 'Warning: cache cannot be written.';
$lang['xs_config_warning_explain'] = 'Cache directory is not writeable. eXtreme Styles can attempt to fix this problem.<br /><a href="{URL}">Click here</a> to try to change access mode to cache directory.<br /><br />If cache doesn\'t work on your server for some reason don\'t worry - eXtreme Styles<br />increases forum speed many times even without cache.';

$lang['xs_config_maintitle'] = 'eXtreme Styles mod Configuration';
$lang['xs_config_subtitle'] = 'This is the configuration for eXtreme Styles. If you don\'t understand what certain variables do then don\'t change it.';
$lang['xs_config_title'] = 'eXtreme Styles mod v{VERSION} settings';
$lang['xs_config_cache'] = 'Cache configuration';

$lang['xs_config_navbar'] = 'Show on left frame:';
$lang['xs_config_navbar_explain'] = 'You can select what items to show on left frame in admin control panel.';

$lang['xs_config_def_template'] = 'Default template directory';
$lang['xs_config_def_template_explain'] = 'If a required tpl file is not found in current template directory (that might happen if you modded phpBB incorrectly) then template system will look for same file in a related directory (like if current template is "myTemplate" and script requires file "myTemplate/myfile.tpl" and that file isn\'t there template system will look for that file as "subSilver/myfile.tpl"). Set to empty to disable this feature.';

$lang['xs_config_check_switches'] = 'Check switches while compiling';
$lang['xs_config_check_switches_explain'] = 'This feature checks for errors in templates. Turning it off will speed up compilation, but the compiler might skip some errors in templates if it contains errors.<br /><br />Smart check will check templates for errors and automatically fix all known errors (there are few known typos in different mods). Works little bit slower than simple check.<br /><br />But sometimes template looks proper only when error check is disabled; this happens because of bad html coding - contact whoever wrote the tpl file if you want to fix errors.<br /><br />If cache feature is disabled, then turn this off for faster compilation.';
$lang['xs_config_check_switches_0'] = 'Off';
$lang['xs_config_check_switches_1'] = 'Smart check';
$lang['xs_config_check_switches_2'] = 'Simple check';

$lang['xs_config_show_errors'] = 'Shows errors when files are incorrectly included in tpl files';
$lang['xs_config_show_error_explain'] = 'This feature enables/disables errors in tpl files that the user used incorrectly &lt;!-- INCLUDE filename --&gt;';

$lang['xs_config_tpl_comments'] = 'Add tpl filenames in html';
$lang['xs_config_tpl_comments_explain'] = 'This feature adds comments to html code that allow style designers to detect which tpl file is displayed.';

$lang['xs_config_use_cache'] = 'Use cache';
$lang['xs_config_use_cache_explain'] = 'Cache is saved to disk and it will accelerate templates system because there would be no need to compile template every time it is shown.';

$lang['xs_config_auto_compile'] = 'Automatically save cache';
$lang['xs_config_auto_compile_explain'] = 'This will automatically compile templates that are not cached and save to cache directory.';

$lang['xs_config_auto_recompile'] = 'Automatically re-compile cache';
$lang['xs_config_auto_recompile_explain'] = 'This will automatically re-compile templates if a template was changed.';

$lang['xs_config_php'] = 'Extension of cache filenames';
$lang['xs_config_php_explain'] = 'This is extension of cached files. Files are stored in php format so default extension is "php". Do not include dot';

$lang['xs_config_back'] = '<a href="{URL}">Click here</a> to return to configuration.';
$lang['xs_config_sql_error'] = 'Failed to update general configuration for {VAR}';

// Debug info
$lang['xs_debug_header'] = 'Debug info';
$lang['xs_debug_explain'] = 'This is debug info. Used to find/fix problems when configuring cache.';
$lang['xs_debug_vars'] = 'Template variables';
$lang['xs_debug_tpl_name'] = 'Template filename:';
$lang['xs_debug_cache_filename'] = 'Cache filename:';
$lang['xs_debug_data'] = 'Debug data:';

$lang['xs_check_hdr'] = 'Checking cache for %s';
$lang['xs_check_filename'] = 'Error: invalid filename';
$lang['xs_check_openfile1'] = 'Error: cannot open file "%s". Will try to create directories...';
$lang['xs_check_openfile2'] = 'Error: cannot open file "%s" for the second time. Giving up...';
$lang['xs_check_nodir'] = 'Checking "%s" - no such directory.';
$lang['xs_check_nodir2'] = 'Error: cannot create directory "%s" - you might need to check permissions.';
$lang['xs_check_createddir'] = 'Created directory "%s"';
$lang['xs_check_dir'] = 'Checking "%s" - directory exists.';
$lang['xs_check_ok'] = 'Opened file "%s" for writing. Everything seems to be ok.';
$lang['xs_error_demo_edit'] = 'you cannot edit file in demo mode';
$lang['xs_error_not_installed'] = 'eXtreme Styles mod is not installed. You forgot to upload includes/template.php';

/*
* chmod
*/

$lang['xs_chmod'] = 'CHMOD';
$lang['xs_chmod_return'] = '<br /><br /><a href="{URL}">Click here</a> to return to configuration.';
$lang['xs_chmod_message1'] = 'Configuration changed.';
$lang['xs_chmod_error1'] = 'Cannot change access mode to cache directory';


/*
* default style
*/

$lang['xs_def_title'] = 'Set Default Style';
$lang['xs_def_explain'] = 'This feature allows you to quickly change default forum style and also switch users from one style to another.';

$lang['xs_styles_set_default'] = 'set default';
$lang['xs_styles_no_override'] = 'do not override user settings';
$lang['xs_styles_do_override'] = 'override user settings';
$lang['xs_styles_switch_all'] = 'switch all users to this style';
$lang['xs_styles_switch_all2'] = 'switch all users to:';
$lang['xs_styles_defstyle'] = 'default style';
$lang['xs_styles_available'] = 'Available styles';
$lang['xs_styles_make_public'] = 'make style public';
$lang['xs_styles_make_admin'] = 'make style admin-only';
$lang['xs_styles_users'] = 'Users List';


/*
* cache management
*/

$lang['xs_manage_cache_explain2'] = 'This feature allows you to compile or remove cached files for styles.';
$lang['xs_clear_all_lc'] = 'clear all';
$lang['xs_compile_all_lc'] = 'compile all';
$lang['xs_clear_cache_lc'] = 'clear cache';
$lang['xs_compile_cache_lc'] = 'compile cache';
$lang['xs_cache_confirm'] = 'If you have many styles it might cause huge server load. Are you sure you want to continue?';

$lang['xs_cache_nowrite'] = 'Error: cannot access cache directory';
$lang['xs_cache_log_deleted'] = 'Deleted {FILE}';
$lang['xs_cache_log_nodelete'] = 'Error: cannot delete file {FILE}';
$lang['xs_cache_log_nothing'] = 'Nothing to delete for template {TPL}';
$lang['xs_cache_log_nothing2'] = 'Nothing to delete in cache directory';
$lang['xs_cache_log_count'] = 'Successfully deleted {NUM} files';
$lang['xs_cache_log_count2'] = 'Error deleting {NUM} files';
$lang['xs_cache_log_compiled'] = 'Compiled: {NUM} files';
$lang['xs_cache_log_errors'] = 'Errors: {NUM}';
$lang['xs_cache_log_noaccess'] = 'Error: cannot access directory {DIR}';
$lang['xs_cache_log_compiled2'] = 'Compiled: {FILE}';
$lang['xs_cache_log_nocompile'] = 'Error compiling: {FILE}';

/*
* export/import/download/clone
*/

$lang['xs_import_explain'] = 'This feature allows you to import styles. It can also automatically install and update styles.<br /><br />Note: If you have added any mods (except for eXtreme Styles mod) on this forum then you should be careful when importing styles because styles might not be compatible with your forum. You can only install styles that have the same modifications as the other styles that you\'ve configured on your forums.';

$lang['xs_import_lc'] = 'import';
$lang['xs_list_files_lc'] = 'list files';
$lang['xs_delete_file_lc'] = 'delete file';
$lang['xs_export_style_lc'] = 'export style';

$lang['xs_import_no_cached'] = 'There are no cached styles to import';
$lang['xs_add_styles'] = 'Add Styles';
$lang['xs_add_styles_web'] = 'Download from web';
$lang['xs_add_styles_web_get'] = 'Get it';
$lang['xs_add_styles_copy'] = 'Copy from local file';
$lang['xs_add_styles_copy_get'] = 'Copy';
$lang['xs_add_styles_upload'] = 'Upload from computer';
$lang['xs_add_styles_upload_get'] = 'Upload';

$lang['xs_export_style'] = 'Export Style';
$lang['xs_export_style_explain'] = 'This feature allows you to export a style as a single file. This single file is very small - smaller than a .zip file (because it is compressed with gzip, which works better than zip) and all styles inside is a single file. In turn, it is very easy to transfer styles from one forum to another.<br /><br />This feature also allows you to upload exported styles using ftp to a server. This system allows you to transfer a style to another forum quickly without manually copying it.';

$lang['xs_export_style_title'] = 'Export Template "{TPL}"';
$lang['xs_export_tpl_name'] = 'Export as (template name)';
$lang['xs_export_style_names'] = 'Select style(s) to export';
$lang['xs_export_style_name'] = 'Style to export (style name)';
$lang['xs_export_style_comment'] = 'Comment';
$lang['xs_export_where'] = 'Where to export';
$lang['xs_export_where_download'] = 'Download as file';
$lang['xs_export_where_store'] = 'Store as file on server';
$lang['xs_export_where_store_dir'] = 'Directory';
$lang['xs_export_where_ftp'] = 'Upload via FTP';
$lang['xs_export_filename'] = 'Export filename';

$lang['xs_download_explain2'] = 'This feature allows you to quickly download and install styles directly from different websites. Click on the link near the website name and you will be redirected to a style downloads page.<br /><br />You can also manage the list of websites.';

$lang['xs_download_locations'] = 'Download Locations';
$lang['xs_edit_link'] = 'Edit Link';
$lang['xs_add_link'] = 'Add Link';
$lang['xs_link_title'] = 'Link Title';
$lang['xs_link_url'] = 'Link URL';
$lang['xs_delete'] = 'Delete';

$lang['xs_style_header_error_file'] = 'Cannot open local file';
$lang['xs_style_header_error_server'] = 'Error on server: ';
$lang['xs_style_header_error_invalid'] = 'Invalid file header';
$lang['xs_style_header_error_reason'] = 'Error reading file header: ';
$lang['xs_style_header_error_incomplete'] = 'File is incomplete';
$lang['xs_style_header_error_incomplete2'] = 'Invalid file size. Probably file is incomplete.';
$lang['xs_style_header_error_invalid2'] = 'Invalid file. Presumeably, the file is not an eXtreme Styles mod-compatible style or invalid version.';
$lang['xs_error_cannot_open'] = 'Cannot open file.';
$lang['xs_error_decompress_style'] = 'Error decompressing file. Probably file is corrupted.';
$lang['xs_error_cannot_create_file'] = 'Cannot create file "{FILE}"';
$lang['xs_error_cannot_create_tmp'] = 'Cannot create temporary file "{FILE}"';
$lang['xs_import_invalid_file'] = 'Invalid file';
$lang['xs_import_incomplete_file'] = 'Incomplete file';
$lang['xs_import_uploaded'] = 'Style uploaded.';
$lang['xs_import_installed'] = 'Style uploaded and installed.';
$lang['xs_import_notinstall'] = 'Style uploaded, but error installing style (sql error).';
$lang['xs_import_notinstall2'] = 'Style uploaded, but error installing style: no styles found in theme_info.cfg';
$lang['xs_import_notinstall3'] = 'Style uploaded, but error installing style: no entry for "{STYLE}" found in theme_info.cfg';
$lang['xs_import_notinstall4'] = 'Style uploaded, but error installing style: could not obtain next themes_id information';
$lang['xs_import_notinstall5'] = 'Style uploaded, but error installing style: could not update styles table';
$lang['xs_import_nodownload'] = 'Cannot download style from {URL}';
$lang['xs_import_nodownload2'] = 'Cannot copy style from {URL}';
$lang['xs_import_nodownload3'] = 'File not uploaded.';
$lang['xs_import_uploaded2'] = 'Style downloaded. You can now import it.<br /><br /><a href="{URL}">Click here</a> to import style.';
$lang['xs_import_uploaded3'] = 'Style copied. You can now import it.<br /><br /><a href="{URL}">Click here</a> to import style.';
$lang['xs_import_uploaded4'] = 'Style uploaded. You can now import it.<br /><br /><a href="{URL}">Click here</a> to import style.';
$lang['xs_export_no_open_dir'] = 'Cannot open directory {DIR}';
$lang['xs_export_no_open_file'] = 'Cannot open file {FILE}';
$lang['xs_export_no_read_file'] = 'Error reading file {FILE}';
$lang['xs_no_theme_data'] = 'Could not get style data for selected template';
$lang['xs_no_style_info'] = 'Could not get style information';
$lang['xs_export_noselect_themes'] = 'You should select at least one style';
$lang['xs_export_error'] = 'Cannot export template "{TPL}": ';
$lang['xs_export_error2'] = 'Cannot export template "{TPL}": style is empty';
$lang['xs_export_saved'] = 'Style is saved as "{FILE}"';
$lang['xs_export_error_uploading'] = 'Error uploading file';
$lang['xs_export_uploaded'] = 'File uploaded.';
$lang['xs_clone_taken'] = 'This style name is already used.';
$lang['xs_error_new_row'] = 'Could not insert new row in table.';
$lang['xs_theme_cloned'] = 'Style cloned.';
$lang['xs_invalid_style_name'] = 'Invalid style name.';
$lang['xs_clone_style_exists'] = 'That template already exists';
$lang['xs_clone_no_select'] = 'You should select at least one style to clone.';
$lang['xs_no_themes'] = 'Style not found in database.';

$lang['xs_import_back'] = '<a href="{URL}">Click here</a> to return to import styles page.';
$lang['xs_import_back_download'] = '<a href="{URL}" target="main">Click here</a> to return to downloads.';
$lang['xs_export_back'] = '<a href="{URL}">Click here</a> to return to export styles page.';
$lang['xs_clone_back'] = '<a href="{URL}">Click here</a> to return to clone styles page.';
$lang['xs_download_back'] = '<a href="{URL}">Click here</a> to return to downloads page.';

$lang['xs_import_tpl'] = 'Import Template "{TPL}"';
$lang['xs_import_tpl_comment'] = 'This feature will upload template to your forum. If template with this name already exists on your forum this feature will automatically overwrite old files so it can also be used to update styles.<br /><br />This feature can also automatically install styles. If you want to install style after importing it then select one or more styles below.';
$lang['xs_import_tpl_filename'] = 'Filename:';
$lang['xs_import_tpl_tplname'] = 'Template name:';
$lang['xs_import_tpl_comment2'] = 'Comment:';
$lang['xs_import_select_styles'] = 'Select style(s) to install:';
$lang['xs_import_install_def_lc'] = 'make default forum style';
$lang['xs_import_install_style'] = 'Install style:';
$lang['xs_import'] = 'Import';

$lang['xs_import_list_contents'] = 'Contents of file: ';
$lang['xs_import_list_filename'] = 'Filename: ';
$lang['xs_import_list_template'] = 'Template: ';
$lang['xs_import_list_comment'] = 'Comment: ';
$lang['xs_import_list_styles'] = 'Style(s): ';
$lang['xs_import_list_files'] = 'Files ({NUM}):';
$lang['xs_import_download_lc'] = 'download file';
$lang['xs_import_view_lc'] = 'view file';
$lang['xs_import_file_size'] = '({NUM} bytes)';

$lang['xs_import_nogzip'] = 'This function requires gz compression, and apparently that isn\'t supported on this server.';
$lang['xs_import_nowrite_cache'] = 'Cannot write to cache. This function requires cache to be writable. Check mod configuration.<br /><br /><a href="{URL1}">Click here</a> to make cache writable.<br /><br /><a href="{URL2}">Click here</a> to return to import page.';

$lang['xs_import_download_warning'] = 'This will take you to an external website where you can quickly download styles with a few simple clicks using the eXtreme Styles import feature.';

$lang['xs_clone_style'] = 'Clone Style';
$lang['xs_clone_style_explain'] = 'This feature allows you to quickly clone style or whole template.<br /><br />Warning: If you are copying template make sure author of original template allows you to do this (unless it is subSilver - you can do whatever you want with subSilver). Usually authors allow to modify their styles, but modified style should not be distributed.';
$lang['xs_clone_style_explain2'] = 'This feature allows you to create new style for a template. This feature will not copy any files - it will add entry in database for your new style. Both old and new style will share same templates.';
$lang['xs_clone_style_explain3'] = 'Enter name for new style that you are going to create and click "clone" button.';
$lang['xs_clone_style_explain4'] = 'This feature allows you to clone template. You can also copy all styles associated with that template. Later you can safely edit tpl files for new template and old template will not be affected.';

$lang['xs_clone_style_lc'] = 'clone style';
$lang['xs_clone_style2'] = 'Clone style "{STYLE}":';
$lang['xs_clone_style3'] = 'Clone Template "{STYLE}"';
$lang['xs_clone_newdir_name'] = 'New template (directory) name:';
$lang['xs_clone_select'] = 'Select style(s) to clone:';
$lang['xs_clone_select_explain'] = 'You should select at least one style.';
$lang['xs_clone_newname'] = 'New style name:';


/*
* install/uninstall
*/
$lang['xs_install_styles_explain2'] = 'This is a list of styles that are uploaded on your forum, but aren\'t installed. Click on the "install" link for the style that you want to install, or select several styles and click submit button.';
$lang['xs_uninstall_styles_explain2'] = 'This is a list of styles that are installed on your forum. Click on the "uninstall" link to remove some styles from the forum. Uninstalling is safe - all users who employ the style that is being uninstalled will be switched to the default forum style. Also, uninstalling will automatically delete cache for that style.';

$lang['xs_install'] = 'Install';
$lang['xs_install_lc'] = 'install';
$lang['xs_uninstall'] = 'Uninstall';
$lang['xs_remove_files'] = 'Remove Files';
$lang['xs_style_removed'] = 'Style removed.';
$lang['xs_uninstall_lc'] = 'uninstall';
$lang['xs_uninstall2_lc'] = 'uninstall and delete files';

$lang['xs_install_back'] = '<a href="{URL}">Click here</a> to return to styles installation.';
$lang['xs_uninstall_back'] = '<a href="{URL}">Click here</a> to return to styles uninstallation.';
$lang['xs_goto_default'] = '<a href="{URL}">Click here</a> to change default style.';

$lang['xs_install_installed'] = 'Style(s) installed.';
$lang['xs_install_error'] = 'Error installing style.';
$lang['xs_install_none'] = 'There are no new styles to install. All available styles are already installed.';

$lang['xs_uninstall_default'] = 'You cannot remove default style. To change default style <a href="{URL}">click here</a>.';

/*
* export theme_info.cfg
*/
$lang['xs_export_styles_data_explain2'] = 'This feature saves style data in theme_info.cfg. It can be used to save database information before transferring styles from one forum to another.<br /><br />Note: If you are using the eXtreme Styles export feature to move a style to another forum you don\'t need to save theme_info.cfg - it is done automatically by the style export feature.';
$lang['xs_export_styles_data_explain3'] = 'Select styles that you want to export.';

$lang['xs_export_data_back'] = '<a href="{URL}">Click here</a> to return to export style data page.';
$lang['xs_export_style_data_lc'] = 'export style data';

$lang['xs_export_data_saved'] = 'Data exported.';

/*
* edit templates (file manager)
*/
$lang['xs_edit_template_comment1'] = 'This feature allows you to edit templates. File browser shows only editable files.';
$lang['xs_edit_template_comment2'] = 'This feature allows you to edit templates.';
$lang['xs_edit_file_saved'] = 'File is saved.';
$lang['xs_edit_not_found'] = 'File not found.';
$lang['xs_edittpl_back_dir'] = '<a href="{URL}">Click here</a> to return to file manager.';

$lang['xs_fileman_browser'] = 'File Browser';
$lang['xs_fileman_directory'] = 'Directory:';
$lang['xs_fileman_dircount'] = 'Directories ({COUNT}):';
$lang['xs_fileman_filter'] = 'Filter';
$lang['xs_fileman_filter_ext'] = 'Show only files with extension:';
$lang['xs_fileman_filter_content'] = 'Show only files that contain:';
$lang['xs_fileman_filter_clear'] = 'Clear Filter';
$lang['xs_fileman_filename'] = 'Filename';
$lang['xs_fileman_filesize'] = 'Size';
$lang['xs_fileman_filetime'] = 'Modification';
$lang['xs_fileman_options'] = 'Options';
$lang['xs_fileman_time_today'] = '(today)';
$lang['xs_fileman_edit_lc'] = 'edit';

$lang['xs_fileedit_search_nomatch'] = 'Match not found';
$lang['xs_fileedit_search_match1'] = 'Replaced 1 match';
$lang['xs_fileedit_search_matches'] = "Replaced ' + count + ' matches";
$lang['xs_fileedit_noundo'] = 'There is nothing to undo';
$lang['xs_fileedit_undo_complete'] = 'Old content restored';
$lang['xs_fileedit_edit_name'] = 'Edit file:';
$lang['xs_fileedit_location'] = 'Location:';
$lang['xs_fileedit_reload_lc'] = 'reload file';
$lang['xs_fileedit_download_lc'] = 'download file';
$lang['xs_fileedit_trim'] = 'Automatically trim spaces at beginning and end of file.';
$lang['xs_fileedit_functions'] = 'Edit Functions';
$lang['xs_fileedit_replace1'] = 'Replace ';
$lang['xs_fileedit_replace2'] = ' with ';
$lang['xs_fileedit_replace_first_lc'] = 'replace first match';
$lang['xs_fileedit_replace_all_lc'] = 'replace all matches';
$lang['xs_fileedit_replace_undo_lc'] = 'undo replacement';
$lang['xs_fileedit_backups'] = 'Backups';
$lang['xs_fileedit_backups_save_lc'] = 'save backup';
$lang['xs_fileedit_backups_show_lc'] = 'show contents';
$lang['xs_fileedit_backups_restore_lc'] = 'restore';
$lang['xs_fileedit_backups_download_lc'] = 'download';
$lang['xs_fileedit_backups_delete_lc'] = 'delete';
$lang['xs_fileedit_upload'] = 'Upload';
$lang['xs_fileedit_upload_file'] = 'Upload file:';

/*
* edit styles data (theme_info)
*/
$lang['xs_data_head_stylesheet'] = 'CSS Stylesheet';
$lang['xs_data_body_background'] = 'Background Image';
$lang['xs_data_body_bgcolor'] = 'Background Colour';
$lang['xs_data_style_name'] = 'Style Name';
$lang['xs_data_body_link'] = 'Link Colour';
$lang['xs_data_body_text'] = 'Text Colour';
$lang['xs_data_body_vlink'] = 'Visited Link Colour';
$lang['xs_data_body_alink'] = 'Active Link Colour';
$lang['xs_data_body_hlink'] = 'Hover Link Colour';
$lang['xs_data_tr_color'] = 'Table Row Colour %s';
$lang['xs_data_tr_class'] = 'Table Row Class %s';
$lang['xs_data_th_color'] = 'Table Header Colour %s';
$lang['xs_data_th_class'] = 'Table Header Class %s';
$lang['xs_data_td_color'] = 'Table Cell Colour %s';
$lang['xs_data_td_class'] = 'Table Cell Class %s';
$lang['xs_data_fontface'] = 'Font Face %s';
$lang['xs_data_fontsize'] = 'Font Size %s';
$lang['xs_data_fontcolor'] = 'Font Colour %s';
$lang['xs_data_span_class'] = 'Span Class %s';
$lang['xs_data_img_size_poll'] = 'Polling Image Size [px]';
$lang['xs_data_img_size_privmsg'] = 'Private Message Status size [px]';
$lang['xs_data_theme_public'] = 'Public Style (1 or 0)';
$lang['xs_data_unknown'] = 'Description is not available (%s)';

$lang['xs_edittpl_error_updating'] = 'Error updating style.';
$lang['xs_edittpl_style_updated'] = 'Style updated.';
$lang['xs_invalid_style_id'] = 'Invalid style id.';

$lang['xs_edittpl_back_edit'] = '<a href="{URL}">Click here</a> to return to editing.';
$lang['xs_edittpl_back_list'] = '<a href="{URL}">Click here</a> to return to styles list.';

$lang['xs_editdata_explain'] = 'This feature allows you to edit database data for installed styles. Some styles ignore database values and use css files instead, and some styles use only some of database values.';
$lang['xs_editdata_var'] = 'Variable';
$lang['xs_editdata_value'] = 'Value';
$lang['xs_editdata_comment'] = 'Comment';

/*
* updates
*/

$lang['xs_updates'] = 'Updates';
$lang['xs_updates_comment'] = 'This feature checks for updates of some styles and mods. It works only with items that have relevant update information.';
$lang['xs_updates_comment2'] = 'This is result of version check.';
$lang['xs_update_total1'] = 'Total: {NUM} items';
$lang['xs_update_info1'] = 'This administrator feature will check for available updates of phpBB, certain mods, and some styles installed on your forum. When it finds available updates it shows you the link where you can download the updated file.<br /><br />This function requires sockets to be enabled. Most free web hosts do not have this feature so if this forum is on free host (like lycos) then you cannot use update feature, but if this forum is on normal server then everything should be okay.<br /><br />When you click "continue", the script will check all software installed on forum. If your website is slow it might take some time. Be patient and don\'t click "stop" in your browser if process is delayed. If this server is slow or update website is slow then script might timeout - if this happens you should increase timeout value.';
$lang['xs_update_name'] = 'Name';
$lang['xs_update_type'] = 'Type';
$lang['xs_update_current_version'] = 'Your version';
$lang['xs_update_latest_version'] = 'Latest version';
$lang['xs_update_downloadinfo'] = 'Download URL';
$lang['xs_update_timeout'] = 'Update script timeout (seconds):';
$lang['xs_update_continue'] = 'Continue';


$lang['xs_update_total2'] = 'Errors: {NUM}';
$lang['xs_update_total3'] = 'Updates available: {NUM} items';
$lang['xs_update_select1'] = 'Select items to update';
$lang['xs_update_types'] = array(
		0 => 'Unknown',
		1 => 'Style',
		2 => 'Mod',
		3 => 'phpBB'
		);
$lang['xs_update_fileinfo'] = 'More info';
$lang['xs_update_nothing'] = 'There is nothing to update.';
$lang['xs_update_noupdate'] = 'You are using the latest version.';

$lang['xs_update_error_url'] = 'Error: cannot retrieve url %s';
$lang['xs_update_error_noitem'] = 'Error: No update information available';
$lang['xs_update_error_noconnect'] = 'Error: Cannot connect to update server';

$lang['xs_update_download'] = 'download';
$lang['xs_update_downloadinfo2'] = 'download/info';
$lang['xs_update_info'] = 'website';

$lang['xs_permission_denied'] = 'Permission Denied';

$lang['xs_download_lc'] = 'download';
$lang['xs_info_lc'] = 'info';

/*
* style configuration
*/
$lang['Template_Config'] = 'Template Config';
$lang['xs_style_configuration'] = 'Template Configuration';

?>