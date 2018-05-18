<?php

/***************************************************************************
 *                                xs_chmod.php
 *                                ------------
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

$template->assign_block_vars('nav_left',array('ITEM' => '&raquo; <a href="' . append_sid('xs_config.'.$phpEx) . '">' . $lang['xs_configuration'] . '</a>'));
$template->assign_block_vars('nav_left',array('ITEM' => '&raquo; <a href="' . append_sid('xs_chmod.'.$phpEx) . '">' . $lang['xs_chmod'] . '</a>'));

$lang['xs_chmod_return'] = str_replace('{URL}', append_sid('xs_config.'.$phpEx), $lang['xs_chmod_return']);
$lang['xs_chmod_message1'] .= $lang['xs_chmod_return'];
$lang['xs_chmod_error1'] .= $lang['xs_chmod_return'];

if(defined('DEMO_MODE'))
{
	xs_error($lang['xs_permission_denied']);
}

if(!get_ftp_config(append_sid('xs_chmod.'.$phpEx), array(), false))
{
	exit;
}
xs_ftp_connect(append_sid('xs_chmod.'.$phpEx), array(), true);

if($ftp === XS_FTP_LOCAL)
{
	@mkdir('../cache', 0777);
	@chmod('../cache', 0777);
	if(xs_dir_writable('../cache'))
	{
		xs_message($lang['Information'], $lang['xs_chmod_message1']);
	}
	xs_error($lang['xs_chmod_error1']);
}

$str = ftp_pwd($ftp);

if(strlen($str) && substr($str, strlen($str) - 1) !== '/')
{
	$str .= '/';
}
$res = @ftp_site($ftp, "CHMOD 0777 {$str}cache");
if(!$res)
{
	@ftp_mkdir($ftp, 'cache');
	$res = @ftp_site($ftp, "CHMOD 0777 {$str}cache");
}
@ftp_quit($ftp);
if($res)
{
	xs_message($lang['Information'], $lang['xs_chmod_message1']);
}
else
{
	xs_error($lang['xs_chmod_error1']);
}

?>