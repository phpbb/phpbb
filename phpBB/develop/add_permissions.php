<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");


// This script adds missing permissions
$db = $dbhost = $dbuser = $dbpasswd = $dbport = $dbname = '';

define('IN_PHPBB', 1);
define('ANONYMOUS', 1);
$phpEx = substr(strrchr(__FILE__, '.'), 1);
$phpbb_root_path='./../';
include($phpbb_root_path . 'config.'.$phpEx);
require($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.'.$phpEx);
require($phpbb_root_path . 'includes/db/' . $dbms . '.'.$phpEx);
include($phpbb_root_path . 'includes/functions.'.$phpEx);

define('ACL_NEVER', 0);
define('ACL_YES', 1);
define('ACL_NO', -1);

define('ACL_GROUPS_TABLE', $table_prefix.'acl_groups');
define('ACL_OPTIONS_TABLE', $table_prefix.'acl_options');
define('ACL_USERS_TABLE', $table_prefix.'acl_users');
define('GROUPS_TABLE', $table_prefix.'groups');
define('USERS_TABLE', $table_prefix.'users');

$cache		= new acm();
$db			= new sql_db();

// Connect to DB
$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false);

// auth => is_local, is_global
$f_permissions = array(
	'f_'		=> array(1, 0),
	'f_list'	=> array(1, 0),
	'f_read'	=> array(1, 0),
	'f_post'	=> array(1, 0),
	'f_reply'	=> array(1, 0),
	'f_edit'	=> array(1, 0),
	'f_user_lock'	=> array(1, 0),
	'f_delete'	=> array(1, 0),
	'f_bump'	=> array(1, 0),
	'f_poll'	=> array(1, 0),
	'f_vote'	=> array(1, 0),
	'f_votechg'	=> array(1, 0),
	'f_announce'=> array(1, 0),
	'f_announce_global'	=> array(1, 0),
	'f_sticky'	=> array(1, 0),
	'f_attach'	=> array(1, 0),
	'f_download'=> array(1, 0),
	'f_icons'	=> array(1, 0),
	'f_bbcode'	=> array(1, 0),
	'f_smilies'	=> array(1, 0),
	'f_img'		=> array(1, 0),
	'f_flash'	=> array(1, 0),
	'f_sigs'	=> array(1, 0),
	'f_search'	=> array(1, 0),
	'f_email'	=> array(1, 0),
	'f_print'	=> array(1, 0),
	'f_ignoreflood'	=> array(1, 0),
	'f_postcount'	=> array(1, 0),
	'f_noapprove'=> array(1, 0),
	'f_report'	=> array(1, 0),
	'f_subscribe'	=> array(1, 0),
);

$m_permissions = array(
	'm_'		=> array(1, 1),
	'm_edit'	=> array(1, 1),
	'm_delete'	=> array(1, 1),
	'm_move'	=> array(1, 1),
	'm_lock'	=> array(1, 1),
	'm_split'	=> array(1, 1),
	'm_merge'	=> array(1, 1),
	'm_approve'	=> array(1, 1),
	'm_unrate'	=> array(1, 1),
	'm_auth'	=> array(1, 1),
	'm_ip'		=> array(1, 1),
	'm_info'	=> array(1, 1),
);

$a_permissions = array(
	'a_'		=> array(0, 1),
	'a_server'	=> array(0, 1),
	'a_board'	=> array(0, 1),
	'a_clearlogs'	=> array(0, 1),
	'a_words'	=> array(0, 1),
	'a_icons'	=> array(0, 1),
	'a_bbcode'	=> array(0, 1),
	'a_attach'	=> array(0, 1),
	'a_email'	=> array(0, 1),
	'a_styles'	=> array(0, 1),
	'a_user'	=> array(0, 1),
	'a_useradd'	=> array(0, 1),
	'a_userdel'	=> array(0, 1),
	'a_ranks'	=> array(0, 1),
	'a_ban'		=> array(0, 1),
	'a_names'	=> array(0, 1),
	'a_group'	=> array(0, 1),
	'a_groupadd'=> array(0, 1),
	'a_groupdel'=> array(0, 1),
	'a_forum'	=> array(0, 1),
	'a_forumadd'=> array(0, 1),
	'a_forumdel'=> array(0, 1),
	'a_prune'	=> array(0, 1),
	'a_auth'	=> array(0, 1),
	'a_authmods'=> array(0, 1),
	'a_authadmins'	=> array(0, 1),
	'a_authusers'	=> array(0, 1),
	'a_authgroups'	=> array(0, 1),
	'a_authdeps'=> array(0, 1),
	'a_backup'	=> array(0, 1),
	'a_restore'	=> array(0, 1),
	'a_search'	=> array(0, 1),
	'a_events'	=> array(0, 1),
	'a_cron'	=> array(0, 1),
);

$u_permissions = array(
	'u_'			=> array(0, 1),
	'u_sendemail'	=> array(0, 1),
	'u_readpm'		=> array(0, 1),
	'u_sendpm'		=> array(0, 1),
	'u_sendim'		=> array(0, 1),
	'u_hideonline'	=> array(0, 1),
	'u_viewonline'	=> array(0, 1),
	'u_viewprofile'	=> array(0, 1),
	'u_chgavatar'	=> array(0, 1),
	'u_chggrp'		=> array(0, 1),
	'u_chgemail'	=> array(0, 1),
	'u_chgname'		=> array(0, 1),
	'u_chgpasswd'	=> array(0, 1),
	'u_chgcensors'	=> array(0, 1),
	'u_search'		=> array(0, 1),
	'u_savedrafts'	=> array(0, 1),
	'u_download'	=> array(0, 1),
	'u_attach'		=> array(0, 1),
	'u_sig'			=> array(0, 1),
	'u_pm_attach'	=> array(0, 1),
	'u_pm_bbcode'	=> array(0, 1),
	'u_pm_smilies'	=> array(0, 1),
	'u_pm_download'	=> array(0, 1),
	'u_pm_edit'		=> array(0, 1),
	'u_pm_printpm'	=> array(0, 1),
	'u_pm_emailpm'	=> array(0, 1),
	'u_pm_forward'	=> array(0, 1),
	'u_pm_delete'	=> array(0, 1),
	'u_pm_img'		=> array(0, 1),
	'u_pm_flash'	=> array(0, 1),
);

echo "<p><b>Determining existing permissions</b></p>\n";

$sql = 'SELECT auth_option_id, auth_option FROM ' . ACL_OPTIONS_TABLE;
$result = $db->sql_query($sql);

$remove_auth_options = array();
while ($row = $db->sql_fetchrow($result))
{
	if (!in_array($row['auth_option'], array_keys(${substr($row['auth_option'], 0, 2) . 'permissions'})))
	{
		$remove_auth_options[$row['auth_option']] = $row['auth_option_id'];
	}
	unset(${substr($row['auth_option'], 0, 2) . 'permissions'}[$row['auth_option']]);
}
$db->sql_freeresult($result);

if (sizeof($remove_auth_options))
{
	$db->sql_query('DELETE FROM ' . ACL_USERS_TABLE . ' WHERE auth_option_id IN (' . implode(', ', $remove_auth_options) . ')');
	$db->sql_query('DELETE FROM ' . ACL_GROUPS_TABLE . ' WHERE auth_option_id IN (' . implode(', ', $remove_auth_options) . ')');
	$db->sql_query('DELETE FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option_id IN (' . implode(', ', $remove_auth_options) . ')');

	echo '<p><b>Removed the following auth options... [<i>' . implode(', ', array_keys($remove_auth_options)) . "</i>]</b></p>\n\n";
}

$prefixes = array('f_', 'a_', 'm_', 'u_');

foreach ($prefixes as $prefix)
{
	$var = $prefix . 'permissions';
	if (sizeof(${$var}))
	{
		foreach (${$var} as $auth_option => $l_ary)
		{
			$sql_ary = array(
				'auth_option'	=> $auth_option,
				'is_local'		=> $l_ary[0],
				'is_global'		=> $l_ary[1]
			);

			$db->sql_query('INSERT INTO ' . ACL_OPTIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
		
			echo "<p><b>Adding $auth_option...</b></p>\n";

			mass_auth('group', 0, 'guests', $auth_option, ACL_NEVER);
			mass_auth('group', 0, 'inactive', $auth_option, ACL_NEVER);
			mass_auth('group', 0, 'inactive_coppa', $auth_option, ACL_NEVER);
			mass_auth('group', 0, 'registered_coppa', $auth_option, ACL_NEVER);
			mass_auth('group', 0, 'registered', $auth_option, (($prefix != 'm_' && $prefix != 'a_') ? ACL_YES : ACL_NEVER));
			mass_auth('group', 0, 'global_moderators', $auth_option, (($prefix != 'a_') ? ACL_YES : ACL_NEVER));
			mass_auth('group', 0, 'administrators', $auth_option, ACL_YES);
			mass_auth('group', 0, 'bots', $auth_option, (($prefix != 'm_' && $prefix != 'a_') ? ACL_YES : ACL_NEVER));
		}
	}
}

$sql = 'UPDATE ' . USERS_TABLE . " SET user_permissions = ''";
$db->sql_query($sql);

$cache->destroy('_acl_options');

echo "<p><b>Done</b></p>\n";
 
/*
	$ug_type = user|group
	$forum_id = forum ids (array|int|0) -> 0 == all forums
	$ug_id = [int] user_id|group_id : [string] usergroup name
	$acl_list = [string] acl entry : [array] acl entries
	$setting = ACL_YES|ACL_NEVER|ACL_NO
*/
function mass_auth($ug_type, $forum_id, $ug_id, $acl_list, $setting)
{
	global $db;
	static $acl_option_ids, $group_ids;

	if ($ug_type == 'group' && is_string($ug_id))
	{
		if (!isset($group_ids[$ug_id]))
		{
			$sql = 'SELECT group_id FROM ' . GROUPS_TABLE . " 
				WHERE group_name = '" . strtoupper($ug_id) . "'";
			$result = $db->sql_query_limit($sql, 1);
			$id = (int) $db->sql_fetchfield('group_id', 0, $result);
			$db->sql_freeresult($result);

			if (!$id)
			{
				return;
			}

			$group_ids[$ug_id] = $id;
		}

		$ug_id = (int) $group_ids[$ug_id];
	}

	// Build correct parameters
	$auth = array();

	if (!is_array($acl_list))
	{
		$auth = array($acl_list => $setting);
	}
	else
	{
		foreach ($acl_list as $auth_option)
		{
			$auth[$auth_option] = $setting;
		}
	}
	unset($acl_list);

	if (!is_array($forum_id))
	{
		$forum_id = array($forum_id);
	}

	// Set any flags as required
	foreach ($auth as $auth_option => $acl_setting)
	{
		$flag = substr($auth_option, 0, strpos($auth_option, '_') + 1);
		if (empty($auth[$flag]))
		{
			$auth[$flag] = $acl_setting;
		}
	}

	if (!is_array($acl_option_ids) || empty($acl_option_ids))
	{
		$sql = 'SELECT auth_option_id, auth_option
			FROM ' . ACL_OPTIONS_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$acl_option_ids[$row['auth_option']] = $row['auth_option_id'];
		}
		$db->sql_freeresult($result);
	}

	$sql_forum = 'AND a.forum_id IN (' . implode(', ', array_map('intval', $forum_id)) . ')';

	$sql = ($ug_type == 'user') ? 'SELECT o.auth_option_id, o.auth_option, a.forum_id, a.auth_setting FROM ' . ACL_USERS_TABLE . ' a, ' . ACL_OPTIONS_TABLE . " o WHERE a.auth_option_id = o.auth_option_id $sql_forum AND a.user_id = $ug_id" : 'SELECT o.auth_option_id, o.auth_option, a.forum_id, a.auth_setting FROM ' . ACL_GROUPS_TABLE . ' a, ' . ACL_OPTIONS_TABLE . " o WHERE a.auth_option_id = o.auth_option_id $sql_forum AND a.group_id = $ug_id";
	$result = $db->sql_query($sql);

	$cur_auth = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$cur_auth[$row['forum_id']][$row['auth_option_id']] = $row['auth_setting'];
	}
	$db->sql_freeresult($result);

	$table = ($ug_type == 'user') ? ACL_USERS_TABLE : ACL_GROUPS_TABLE;
	$id_field  = $ug_type . '_id';

	$sql_ary = array();
	foreach ($forum_id as $forum)
	{
		foreach ($auth as $auth_option => $setting)
		{
			$auth_option_id = $acl_option_ids[$auth_option];

			if (!$auth_option_id)
			{
				continue;
			}

			switch ($setting)
			{
				case ACL_NO:
					if (isset($cur_auth[$forum][$auth_option_id]))
					{
						$sql_ary['delete'][] = "DELETE FROM $table 
							WHERE forum_id = $forum
								AND auth_option_id = $auth_option_id
								AND $id_field = $ug_id";
					}
					break;

				default:
					if (!isset($cur_auth[$forum][$auth_option_id]))
					{
						$sql_ary['insert'][] = "$ug_id, $forum, $auth_option_id, $setting";
					}
					else if ($cur_auth[$forum][$auth_option_id] != $setting)
					{
						$sql_ary['update'][] = "UPDATE " . $table . " 
							SET auth_setting = $setting 
							WHERE $id_field = $ug_id 
								AND forum_id = $forum 
								AND auth_option_id = $auth_option_id";
					}
			}
		}
	}
	unset($cur_auth);

	$sql = '';
	foreach ($sql_ary as $sql_type => $sql_subary)
	{
		switch ($sql_type)
		{
			case 'insert':
				switch ($db->get_sql_layer())
				{
					case 'mysql':
					case 'mysql4':
						$sql = 'VALUES ' . implode(', ', preg_replace('#^(.*?)$#', '(\1)', $sql_subary));
						break;

					case 'mssql':
					case 'sqlite':
					case 'sqlite3':
						$sql = implode(' UNION ALL ', preg_replace('#^(.*?)$#', 'SELECT \1', $sql_subary));
						break;

					default:
						foreach ($sql_subary as $sql)
						{
							$sql = "INSERT INTO $table ($id_field, forum_id, auth_option_id, auth_setting) VALUES ($sql)";
							$result = $db->sql_query($sql);
							$sql = '';
						}
				}

				if ($sql != '')
				{
					$sql = "INSERT INTO $table ($id_field, forum_id, auth_option_id, auth_setting) $sql";
					$result = $db->sql_query($sql);
				}
				break;

			case 'update':
			case 'delete':
				foreach ($sql_subary as $sql)
				{
					$result = $db->sql_query($sql);
					$sql = '';
				}
				break;
		}
		unset($sql_ary[$sql_type]);
	}
	unset($sql_ary);

}
