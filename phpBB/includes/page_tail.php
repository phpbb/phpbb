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
// Output page creation time
//
if ( defined('DEBUG') )
{
	$mtime = microtime();
	$mtime = explode(' ', $mtime);
	$totaltime = ( $mtime[1] + $mtime[0] ) - $starttime;

	$debug_output = sprintf('<br /><br />[ Time : %.3fs | ' . $db->sql_num_queries() . ' Queries | GZIP : ' .  ( ( $board_config['gzip_compress'] ) ? 'On' : 'Off' ) . ' | Load : '  . (( $session->load ) ? $session->load : 'N/A') . ' ]', $totaltime);
}

$template->assign_vars(array(
	'PHPBB_VERSION' => $board_config['version'], 
	'ADMIN_LINK' => ( $acl->get_acl_admin() ) ? '<a href="' . "admin/index.$phpEx$SID" . '">' . $lang['Admin_panel'] . '</a><br /><br />' : '', 
	'DEBUG_OUTPUT' => ( defined('DEBUG') ) ? $debug_output : '')
);

$template->display('body');

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