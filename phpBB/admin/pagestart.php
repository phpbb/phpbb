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
$acl = new auth('admin', $userdata);
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
function page_header($sub_title)
{
	global $db, $lang, $phpEx;

	include('page_header_admin.'.$phpEx);

?>

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

function page_footer($ignore_copyright = false)
{
	global $db, $lang, $phpEx;

?>

		</td>
	</tr>
</table>
			
<?php

	include('page_footer_admin.'.$phpEx);

}

function page_message($title, $message)
{
	global $lang;

?>

<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td><a href="../"><img src="images/header_left.jpg" width="200" height="60" alt="phpBB Logo" title="phpBB Logo" border="0"/></a></td>
		<td width="100%" background="images/header_bg.jpg" height="60" align="right" nowrap="nowrap"><span class="maintitle"><?php echo $lang['Admin_title']; ?></span> &nbsp; &nbsp; &nbsp;</td>
	</tr>
</table>

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
	$data = ( !sizeof($arguments) ) ? '' : serialize($arguments);

	$sql = "INSERT INTO " . LOG_ADMIN_TABLE . " (user_id, log_ip, log_time, log_operation, log_data) 
		VALUES (" . $userdata['user_id'] . ", '$user_ip', " . time() . ", '$action', '$data')";
	$db->sql_query($sql);

	return;
}

function view_admin_log($limit = 0, $offset = 0, $limit_days = 0, $sort_by = 'l.log_time DESC')
{
	global $db, $lang, $phpEx, $SID;

	$limit_sql = ( $limit ) ? ( ( $offset ) ? "LIMIT $offset, $limit" : "LIMIT $limit" ) : '';
	$sql = "SELECT l.log_id, l.user_id, l.log_ip, l.log_time, l.log_operation, l.log_data, u.username 
		FROM " . LOG_ADMIN_TABLE . " l, " . USERS_TABLE . " u 
		WHERE u.user_id = l.user_id 
			AND l.log_time >= $limit_days 
		ORDER BY $sort_by 
		$limit_sql";
	$result = $db->sql_query($sql);

	$admin_log = array();
	if ( $row = $db->sql_fetchrow($result) )
	{
		$i = 0;
		do
		{
			$admin_log[$i]['id'] = $row['log_id'];
			$admin_log[$i]['username'] = '<a href="admin_users.'.$phpEx . $SID . '&amp;u=' . $row['user_id'] . '">' . $row['username'] . '</a>';
			$admin_log[$i]['ip'] = $row['log_ip'];
			$admin_log[$i]['time'] = $row['log_time'];

			$admin_log[$i]['action'] = ( !empty($lang[$row['log_operation']]) ) ? $lang[$row['log_operation']] : ucfirst(str_replace('_', ' ', $row['log_operation']));

			if ( !empty($row['log_data']) )
			{
				$log_data_ary = unserialize($row['log_data']);

				foreach ( $log_data_ary as $log_data )
				{
					$admin_log[$i]['action'] = preg_replace('#%s#', $log_data, $admin_log[$i]['action'], 1);
				}
			}

			$i++;
		}
		while ( $row = $db->sql_fetchrow($result) );
	}

	$db->sql_freeresult($result);

	return $admin_log;
}
//
// End Functions
// -----------------------------

?>