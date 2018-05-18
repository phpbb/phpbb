<?php
/***************************************************************************
 *                              page_tail.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: page_tail.php,v 1.1 2010/10/10 15:05:27 orynider Exp $
 *
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

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
}

global $do_gzip_compress;

//
// Show the overall footer.
//
$admin_link = ($user->data['user_level'] == ADMIN) ? '<a href="admin/index.' . $phpEx . '?sid=' . $user->data['session_id'] . '">' . $lang['Admin_panel'] . '</a><br /><br />' : '';

$template->set_filenames(array(
	'overall_footer' => ( empty($gen_simple_header) ) ? 'overall_footer.tpl' : 'simple_footer.tpl')
);
//Temp fix for page tail
$lang['POWERED_BY'] = !empty($lang['POWERED_BY']) ? $lang['POWERED_BY'] : 'Powered by %s';
$template->assign_vars(array(
	'TRANSLATION_INFO' => (isset($lang['TRANSLATION_INFO'])) ? $lang['TRANSLATION_INFO'] : ((isset($lang['TRANSLATION'])) ? $lang['TRANSLATION'] : ''),
	'DEBUG_OUTPUT'			=> phpbb_generate_debug_output($db, $board_config, $auth, $user, $cache),
	'CREDIT_LINE'			=> $user->lang('POWERED_BY', ' <a href="https://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Limited'),
	
	'ADMIN_LINK' => $admin_link,
	'L_ACP' => $lang['Admin_panel'],
	'U_ACP' => ($user->data['user_level'] == ADMIN) ? "{$phpbb_root_path}admin/index.$phpEx?sid=" . $user->session_id : $admin_link)
);
	
$template->pparse('overall_footer');

//
// Close our DB connection.
//
$db->sql_close();

//
// Compress buffered output if required and send to browser
//
if ( $do_gzip_compress )
{
	//
	// Borrowed from php.net!
	//
	$gzip_contents = ob_get_contents();
	ob_end_clean();

	$gzip_size = strlen($gzip_contents);
	$gzip_crc = crc32($gzip_contents);

	$gzip_contents = gzcompress($gzip_contents, 9);
	$gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);

	echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
	echo $gzip_contents;
	echo pack('V', $gzip_crc);
	echo pack('V', $gzip_size);
}

exit;

?>