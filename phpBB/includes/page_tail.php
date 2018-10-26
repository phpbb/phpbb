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

global $auth, $user, $cache, $db, $do_gzip_compress;

//
// Show the overall footer.
//
$u_acp = PHPBB_URL . 'admin/index.' . $phpEx;
$l_acp = $lang['Admin_panel'];

$admin_link = ($user->data['user_level'] == ADMIN) ? '<a href="admin/index.' . $phpEx . '?sid=' . $user->data['session_id'] . '">' . $lang['Admin_panel'] . '</a><br /><br />' : '';

$template->set_filenames(array(
	'overall_footer' => ( empty($gen_simple_header) ) ? 'overall_footer.tpl' : 'simple_footer.tpl')
);

//Temp fix for page tail
$lang['POWERED_BY'] = !empty($lang['POWERED_BY']) ? $user->lang['POWERED_BY'] : 'Powered by %s';

$footer_text = $user->lang('about_title');
$footer_text_url = PHPBB_URL . 'index.' . $phpEx . '?sid=' . $user->data['session_id'] . '&mx_copy=true';

// Generate debug stats
// - from Olympus
$debug_output = '<div align="center"><span class="copyright">';
if (defined('DEBUG') && $userdata['user_level'] == ADMIN)
{
	$mtime = explode(' ', microtime());
	$totaltime = $mtime[0] + $mtime[1] - $starttime;

	if (!empty($_REQUEST['explain']) && method_exists($db, 'sql_report'))
	{
		$db->sql_report('display');
	}

	$debug_output .= sprintf('Time : %.3fs | ' . @$db->sql_num_queries() . ' Queries | GZIP : ' .  (($board_config['gzip_compress']) ? 'On' : 'Off' ) . ' | Load : '  . (($user->load) ? $user->load : 'N/A'), $totaltime);

	if (defined('DEBUG_EXTRA'))
	{
		if (function_exists('memory_get_usage'))
		{
			if ($memory_usage = memory_get_usage())
			{
				global $base_memory_usage;
				$memory_usage -= $base_memory_usage;
				$memory_usage = ($memory_usage >= 1048576) ? round((round($memory_usage / 1048576 * 100) / 100), 2) . ' ' . 'MB' : (($memory_usage >= 1024) ? round((round($memory_usage / 1024 * 100) / 100), 2) . ' ' . 'kB' : $memory_usage . ' ' . 'bytes');
					$debug_output .= ' | Memory Usage: ' . $memory_usage;
			}
		}
		$debug_output .= ' | <a href="' . (($_SERVER['REQUEST_URI']) ? htmlspecialchars($_SERVER['REQUEST_URI']) : "index.$phpEx$SID") . ((strpos($_SERVER['REQUEST_URI'], '?') !== false) ? '&amp;' : '?') . 'explain=1">Explain</a>';
	}
}
$debug_output .= '</span></div>';
//
// Generate additional footer code (defined by modules)
//
$addional_footer_text = '';
if (isset($page->mxbb_footer_addup) && (count($page->mxbb_footer_addup) > 0))
{
	foreach($page->mxbb_footer_addup as $key => $footer_text)
	{
		$addional_footer_text .= "\n"."\n".$footer_text;
	}
}

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