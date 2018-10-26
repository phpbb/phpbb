<?php
/**
*
* @package profile
* @version $Id$
* @copyright (c) 2008 Icy Phoenix
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}

/*
* Check whether a user is a friend or a foe
*/
function user_check_friend_foe($target_id, $friend = true)
{
	global $db, $cache, $board_config, $user;

	$sql_check = !empty($friend) ? (" AND friend = '1' ") : (" AND foe = '1' ");
	$sql = "SELECT * FROM " . ZEBRA_TABLE . "
			WHERE user_id = '" . $user->data['user_id'] . "'
				AND zebra_id = '" . $target_id . "'
				" . $sql_check . "
			LIMIT 1";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$db->sql_freeresult($result);
		return true;
	}

	return false;
}

/*
* Get the list of friends or foes
*/
function user_get_zebra_list($ftype = 'friends')
{
	global $db, $cache, $board_config, $user;

	if ($ftype == 'foes')
	{
		$sql_f_check = 'foe';
	}
	else
	{
		$sql_f_check = 'friend';
	}
	$zebra_list = array();
	$sql = "SELECT z.zebra_id, u.username, u.user_active, u.user_color
			FROM " . ZEBRA_TABLE . " z, " . USERS_TABLE . " u
			WHERE z.user_id = '" . $user->data['user_id'] . "'
				AND " . $sql_f_check . " = '1'
				AND u.user_id = z.zebra_id
			ORDER BY u.username ASC";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$zebra_list[] = $row;
	}
	$db->sql_freeresult($result);
	if (empty($zebra_list))
	{
		return false;
	}
	else
	{
		return $zebra_list;
	}
}

/*
* Get the list of friends online
*/
function user_get_friends_online_list()
{
	global $db, $cache, $board_config, $user;

	$friends_online_list = array();
	$sql = "SELECT u.user_id, u.username, u.user_active, u.user_color, u.user_allow_viewonline, s.session_logged_in, s.session_time
					FROM " . ZEBRA_TABLE . " z, " . USERS_TABLE . " u, " . SESSIONS_TABLE . " s
					WHERE z.user_id = '" . $user->data['user_id'] . "'
						AND z.friend = '1'
						AND u.user_id = z.zebra_id
						AND u.user_id = s.session_user_id
						AND s.session_time >= " . (time() - ONLINE_REFRESH) . "";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$friends_online_list[$row['user_id']]['username'] = $row['username'];
		$friends_online_list[$row['user_id']]['user_active'] = $row['user_active'];
		$friends_online_list[$row['user_id']]['user_color'] = $row['user_color'];
		$friends_online_list[$row['user_id']]['user_level'] = $row['user_level'];
		$friends_online_list[$row['user_id']]['user_allow_viewonline'] = $row['user_allow_viewonline'];
	}
	$db->sql_freeresult($result);
	if (empty($friends_online_list))
	{
		return false;
	}
	else
	{
		return $friends_online_list;
	}
}

/*
* Adds a friend or a foe
*/
function user_friend_foe_add($target_ids, $friend = true)
{
	global $db, $cache, $board_config, $user;

	if (empty($target_ids))
	{
		return false;
	}

	if (!is_array($target_ids))
	{
		$target_ids = array($target_ids);
	}

	$sql_values = !empty($friend) ? "'1', '0'" : "'0', '1'";
	foreach ($target_ids as $target_id)
	{
		$is_friend_foe = user_check_friend_foe($target_id, $friend);
		if (empty($is_friend_foe))
		{
			user_friend_foe_remove(array($target_id), !$friend);
			$sql = "INSERT INTO " . ZEBRA_TABLE . " (`user_id` , `zebra_id` , `friend` , `foe`)
							VALUES ('" . $user->data['user_id'] . "', '" . $target_id . "', " . $sql_values . ")";
			$result = $db->sql_query($sql);
		}
	}

	return true;
}

/*
* Removes a friend or a foe
*/
function user_friend_foe_remove($target_ids, $friend = true)
{
	global $db, $cache, $board_config, $user;

	if (empty($target_ids))
	{
		return false;
	}

	if (!is_array($target_ids))
	{
		$target_ids = array($target_ids);
	}

	$sql_check = !empty($friend) ? (" AND friend = '1' ") : (" AND foe = '1' ");

	$users_to_del = implode("','", $target_ids);
	$sql = "DELETE FROM " . ZEBRA_TABLE . "
		WHERE user_id = " . $user->data['user_id'] . "
			AND zebra_id IN ('" . $users_to_del . "')
			" . $sql_check;
	$result = $db->sql_query($sql);

	return true;
}

/*
* Checks whether PM are allowed
*/
function user_check_pm_in_allowed($target_id)
{
	global $user, $db;
	$sql = "SELECT * FROM " . USERS_TABLE . "
			WHERE user_id = '" . $target_id . "'
				AND user_allow_pm_in = 1
			LIMIT 1";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$db->sql_freeresult($result);
		return true;
	}
	return false;
}

?>