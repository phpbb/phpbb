<?php
/***************************************************************************
 *                               pagestart.php
 *                            -------------------
 *   begin                : Thursday, Aug 2, 2001
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

define('IN_ADMIN', true);
include($phpbb_root_path . 'common.'.$phpEx);

//
// Start session management
//
$userdata = $session->start($update);
$acl = new acl($userdata);
//
// End session management
//

//
// Configure style, language, etc.
//
$session->configure($userdata);

// -----------------------------
// Functions
//
function page_header($sub_title, $meta = '', $table_html = true)
{
	global $board_config, $db, $lang, $phpEx, $gzip_compress;
	global $HTTP_SERVER_VARS;

	define('HEADER_INC', true);

	//
	// gzip_compression
	//
	$gzip_compress = false;
	if ( $board_config['gzip_compress'] )
	{
		$phpver = phpversion();

		if ( $phpver >= '4.0.4pl1' && strstr($HTTP_SERVER_VARS['HTTP_USER_AGENT'], 'compatible') )
		{
			if ( extension_loaded('zlib') )
			{
				ob_start('ob_gzhandler');
			}
		}
		else if ( $phpver > '4.0' )
		{
			if ( strstr($HTTP_SERVER_VARS['HTTP_ACCEPT_ENCODING'], 'gzip') )
			{
				if ( extension_loaded('zlib') )
				{
					$gzip_compress = true;
					ob_start();
					ob_implicit_flush(0);

					header("Content-Encoding: gzip");
				}
			}
		}
	}

	header("Content-type: text/html; charset=" . $lang['ENCODING']);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $lang['ENCODING']; ?>">
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" href="subSilver.css" type="text/css">
<?php

	echo $meta;

?>
<style type="text/css">
<!--
th		{ background-image: url('images/cellpic3.gif') }
td.cat	{ background-image: url('images/cellpic1.gif') }
//-->
</style>
<title><?php echo $board_config['sitename'] . ' - ' . $page_title; ?></title>
</head>
<body>

<?php

	if ( $table_html )
	{

?>
<a name="top"></a>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td colspan="2" height="25" align="right" nowrap="nowrap"><span class="subtitle">&#0187; <i><?php echo $sub_title; ?></i></span> &nbsp;&nbsp;</td>
	</tr>
</table>

<table width="95%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td><br clear="all" />

<?php

	}

}

function page_footer($copyright_html = true)
{
	global $board_config, $db, $lang, $phpEx, $gzip_compress;

?>

		</td>
	</tr>
</table>
<?php

	if ( $copyright_html )
	{

?>

<div align="center"><span class="copyright">Powered by phpBB <?php echo $board_config['version']; ?> &copy; 2002 <a href="http://www.phpbb.com/" target="_phpbb" class="copyright">phpBB Group</a></span></div>

<br clear="all" />

</body>
</html>
<?php

	}

	//
	// Close our DB connection.
	//
	$db->sql_close();

	//
	// Compress buffered output if required
	// and send to browser
	//
	if ( $gzip_compress )
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

}

function page_message($title, $message, $show_header = false)
{
	global $phpEx, $SID, $lang;

	if ( $show_header )
	{

?>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td><a href="../index.<?php echo $phpEx . $SID; ?>"><img src="images/header_left.jpg" width="200" height="60" alt="phpBB Logo" title="phpBB Logo" border="0"/></a></td>
		<td width="100%" background="images/header_bg.jpg" height="60" align="right" nowrap="nowrap"><span class="maintitle"><?php echo $lang['Admin_title']; ?></span> &nbsp; &nbsp; &nbsp;</td>
	</tr>
</table>

<?php

	}

?>

<br /><br />

<table class="bg" width="80%" cellpadding="4" cellspacing="1" border="0" align="center">
	<tr>
		<th><?php echo $title; ?></th>
	</tr>
	<tr>
		<td class="row1" align="center"><?php echo $message; ?></td>
	</tr>
</table>

<br />

<?php

}

function add_admin_log()
{
	global $db, $userdata, $user_ip;

	$arguments = func_get_args();

	$action = array_shift($arguments);
	$data = ( !sizeof($arguments) ) ? '' : addslashes(serialize($arguments));

	$sql = "INSERT INTO " . LOG_ADMIN_TABLE . " (user_id, log_ip, log_time, log_operation, log_data)
		VALUES (" . $userdata['user_id'] . ", '$user_ip', " . time() . ", '$action', '$data')";
	$db->sql_query($sql);

	return;
}

function view_log($mode, &$log, &$log_count, $limit = 0, $offset = 0, $forum_id = 0, $limit_days = 0, $sort_by = 'l.log_time DESC')
{
	global $db, $lang, $phpEx, $SID;

	$table_sql = ( $mode == 'admin' ) ? LOG_ADMIN_TABLE : LOG_MOD_TABLE;
	$forum_sql = ( $mode == 'mod' && $forum_id ) ? "AND l.forum_id = $forum_id" : '';
	$limit_sql = ( $limit ) ? ( ( $offset ) ? "LIMIT $offset, $limit" : "LIMIT $limit" ) : '';

	$sql = "SELECT l.log_id, l.user_id, l.log_ip, l.log_time, l.log_operation, l.log_data, u.username
		FROM $table_sql l, " . USERS_TABLE . " u
		WHERE u.user_id = l.user_id
			AND l.log_time >= $limit_days
			$forum_sql
		ORDER BY $sort_by
		$limit_sql";
	$result = $db->sql_query($sql);

	$log = array();
	if ( $row = $db->sql_fetchrow($result) )
	{
		$i = 0;
		do
		{
			$log[$i]['id'] = $row['log_id'];
			$log[$i]['username'] = '<a href="admin_users.'.$phpEx . $SID . '&amp;u=' . $row['user_id'] . '">' . $row['username'] . '</a>';
			$log[$i]['ip'] = $row['log_ip'];
			$log[$i]['time'] = $row['log_time'];

			$log[$i]['action'] = ( !empty($lang[$row['log_operation']]) ) ? $lang[$row['log_operation']] : ucfirst(str_replace('_', ' ', $row['log_operation']));

			if ( !empty($row['log_data']) )
			{
				$log_data_ary = unserialize(stripslashes($row['log_data']));

				foreach ( $log_data_ary as $log_data )
				{
					$log[$i]['action'] = preg_replace('#%s#', $log_data, $log[$i]['action'], 1);
				}
			}

			$i++;
		}
		while ( $row = $db->sql_fetchrow($result) );
	}

	$db->sql_freeresult($result);

	$sql = "SELECT COUNT(*) AS total_entries
		FROM $table_sql l
		WHERE l.log_time >= $limit_days
			$forum_sql";
	$result = $db->sql_query($sql);

	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$log_count =  $row['total_entries'];

	return;
}
//
// End Functions
// -----------------------------

?>