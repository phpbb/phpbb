<?php
/***************************************************************************
 *                              page_tail.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
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

//
// Show the overall footer.
//
$admin_link = ( $userdata['user_level'] == ADMIN ) ? '<a href="' . append_sid("admin/index.$phpEx") . '">' . $lang['Admin_panel'] . '</a><br /><br />' : '';

$template->set_filenames(array(
	'overall_footer' => ( empty($gen_simple_header) ) ? 'overall_footer.tpl' : 'simple_footer.tpl')
);

//
// Output page creation time
//
if ( DEBUG )
{
	$mtime = microtime();
	$mtime = explode(' ', $mtime);
	$totaltime = ( $mtime[1] + $mtime[0] ) - $starttime;
	$gzip_text = ( $board_config['gzip_compress'] ) ? 'GZIP compression enabled' : 'GZIP compression disabled';
}

$template->assign_vars(array(
	'PHPBB_VERSION' => '2' . $board_config['version'],
	'TRANSLATION_INFO' => ( isset($lang['TRANSLATION_INFO']) ) ? $lang['TRANSLATION_INFO'] : '', 
	'ADMIN_LINK' => $admin_link, 
	'DEBUG_OUTPUT' => ( DEBUG ) ? sprintf('<br /><br />phpBB Created this page in %f seconds : ' . $db->sql_num_queries() . ' queries executed : ' .  $gzip_text, $totaltime) : '')
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
	echo pack("V", $gzip_crc);
	echo pack("V", $gzip_size);
}

exit;

?>