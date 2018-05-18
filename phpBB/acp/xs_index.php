<?php

/***************************************************************************
 *                               xs_index.php
 *                               ------------
 *   copyright            : (C) 2003 - 2007 Vjacheslav Trushkin
 *   support              : http://www.stsoftware.biz/forum
 *
 *   version              : 2.4.0
 *
 *   file revision        : 80
 *   project revision     : 83
 *   last modified        : 12 Mar 2007  10:28:52
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

define('IN_PHPBB', 1);
$phpbb_root_path = "./../";
$no_page_header = true;
require($phpbb_root_path . 'extension.inc');
require('./pagestart.' . $phpEx);

// check if mod is installed
if(empty($template->xs_version) || $template->xs_version !== 8)
{
	message_die(GENERAL_ERROR, isset($lang['xs_error_not_installed']) ? $lang['xs_error_not_installed'] : 'eXtreme Styles mod is not installed. You forgot to upload includes/template.php');
}

define('IN_XS', true);
include_once('xs_include.' . $phpEx);

if(isset($_GET['showwarning']))
{
	$msg = str_replace('{URL}', append_sid('xs_index.'.$phpEx), $lang['xs_main_comment3']);
	xs_message($lang['Information'], $msg);
}

$template->assign_vars(array(
	'U_CONFIG'				=> append_sid('xs_config.'.$phpEx),
	'U_DEFAULT_STYLE'		=> append_sid('xs_styles.'.$phpEx),
	'U_MANAGE_CACHE'		=> append_sid('xs_cache.'.$phpEx),
	'U_IMPORT_STYLES'		=> append_sid('xs_import.'.$phpEx),
	'U_EXPORT_STYLES'		=> append_sid('xs_export.'.$phpEx),
	'U_CLONE_STYLE'			=> append_sid('xs_clone.'.$phpEx),
	'U_DOWNLOAD_STYLES'		=> append_sid('xs_download.'.$phpEx),
	'U_INSTALL_STYLES'		=> append_sid('xs_install.'.$phpEx),
	'U_UNINSTALL_STYLES'	=> append_sid('xs_uninstall.'.$phpEx),
	'U_EDIT_STYLES'			=> append_sid('xs_edit.'.$phpEx),
	'U_EDIT_STYLES_DATA'	=> append_sid('xs_edit_data.'.$phpEx),
	'U_EXPORT_DATA'			=> append_sid('xs_export_data.'.$phpEx),
	'U_UPDATES'				=> append_sid('xs_update.'.$phpEx),
	'S_SHOW_UPDATES'		=> defined('XS_ENABLE_UPDATES') ? 1 : 0,
	));

$template->set_filenames(array('body' => XS_TPL_PATH . 'index.tpl'));
$template->pparse('body');
xs_exit();

?>