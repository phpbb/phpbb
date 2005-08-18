<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* Obtain user_ids from usernames or vice versa. Returns false on
* success else the error string
*/
function user_get_id_name(&$user_id_ary, &$username_ary)
{
	global $db;

	// Are both arrays already filled? Yep, return else
	// are neither array filled? 
	if ($user_id_ary && $username_ary)
	{
		return;
	}
	else if (!$user_id_ary && !$username_ary)
	{
		return 'NO_USERS';
	}

	$which_ary = ($user_id_ary) ? 'user_id_ary' : 'username_ary';

	if ($$which_ary  && !is_array($$which_ary))
	{
		$$which_ary = array($$which_ary);
	}

	$sql_in = ($which_ary == 'user_id_ary') ? array_map('intval', $$which_ary) : preg_replace('#^[\s]*(.*?)[\s]*$#e', "\"'\" . \$db->sql_escape('\\1') . \"'\"", $$which_ary);
	unset($$which_ary);

	// Grab the user id/username records
	$sql_where = ($which_ary == 'user_id_ary') ? 'user_id' : 'username';
	$sql = 'SELECT user_id, username 
		FROM ' . USERS_TABLE . " 
		WHERE $sql_where IN (" . implode(', ', $sql_in) . ')';
	$result = $db->sql_query($sql);

	if (!($row = $db->sql_fetchrow($result)))
	{
		return 'NO_USERS';
	}

	$id_ary = $username_ary = array();
	do
	{
		$username_ary[$row['user_id']] = $row['username'];
		$user_id_ary[] = $row['user_id'];
	}
	while ($row = $db->sql_fetchrow($result));
	$db->sql_freeresult($result);

	return false;
}

/**
* Updates a username across all relevant tables/fields
*/
function user_update_name($old_name, $new_name)
{
	global $config, $db;

	$update_ary = array(
		FORUMS_TABLE	=> array('forum_last_poster_name'), 
		MODERATOR_TABLE	=> array('username'), 
		POSTS_TABLE		=> array('post_username'), 
		TOPICS_TABLE	=> array('topic_first_poster_name', 'topic_last_poster_name'),
	);

	foreach ($update_ary as $table => $field_ary)
	{
		foreach ($field_ary as $field)
		{
			$sql = "UPDATE $table 
				SET $field = '$new_name' 
				WHERE $field = '$old_name'";
			$db->sql_query($sql);
		}
	}

	if ($config['newest_username'] == $old_name)
	{
		set_config('newest_username', $new_name);
	}
}

/**
* Remove User
*/
function user_delete($mode, $user_id)
{
	global $config, $db, $user, $auth;

	$db->sql_transaction();

	switch ($mode)
	{
		case 'retain':
			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET forum_last_poster_id = ' . ANONYMOUS . " 
				WHERE forum_last_poster_id = $user_id";
			$db->sql_query($sql);

			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET poster_id = ' . ANONYMOUS . " 
				WHERE poster_id = $user_id";
			$db->sql_query($sql);

			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_poster = ' . ANONYMOUS . "
				WHERE topic_poster = $user_id";
			$db->sql_query($sql);

			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_last_poster_id = ' . ANONYMOUS . "
				WHERE topic_last_poster_id = $user_id";
			$db->sql_query($sql);
			break;

		case 'remove':

			if (!function_exists('delete_posts'))
			{
				global $phpbb_root_path, $phpEx;
				include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
			}

			$sql = 'SELECT topic_id, COUNT(post_id) AS total_posts 
				FROM ' . POSTS_TABLE . " 
				WHERE poster_id = $user_id
				GROUP BY topic_id";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$topic_id_ary[$row['topic_id']] = $row['total_posts'];
			}
			$db->sql_freeresult($result);

			$sql = 'SELECT topic_id, topic_replies, topic_replies_real 
				FROM ' . TOPICS_TABLE . ' 
				WHERE topic_id IN (' . implode(', ', array_keys($topic_id_ary)) . ')';
			$result = $db->sql_query($sql);

			$del_topic_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				if (max($row['topic_replies'], $row['topic_replies_real']) + 1 == $topic_id_ary[$row['topic_id']])
				{
					$del_topic_ary[] = $row['topic_id'];
				}
			}
			$db->sql_freeresult($result);

			if (sizeof($del_topic_ary))
			{
				$sql = 'DELETE FROM ' . TOPICS_TABLE . ' 
					WHERE topic_id IN (' . implode(', ', $del_topic_ary) . ')';
				$db->sql_query($sql);
			}

			// Delete posts, attachments, etc.
			delete_posts('poster_id', $user_id);

			break;
	}

	$table_ary = array(USERS_TABLE, USER_GROUP_TABLE, TOPICS_WATCH_TABLE, FORUMS_WATCH_TABLE, ACL_USERS_TABLE, TOPICS_TRACK_TABLE, FORUMS_TRACK_TABLE);

	foreach ($table_ary as $table)
	{
		$sql = "DELETE FROM $table 
			WHERE user_id = $user_id";
		$db->sql_query($sql);
	}

	// Reset newest user info if appropriate
	if ($config['newest_user_id'] == $user_id)
	{
		$sql = 'SELECT user_id, username 
			FROM ' . USERS_TABLE . ' 
			WHERE user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')
			ORDER BY user_id DESC
			LIMIT 1';
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			set_config('newest_user_id', $row['user_id']);
			set_config('newest_username', $row['username']);
		}
		$db->freeresult($result);
	}

	set_config('num_users', $config['num_users'] - 1, TRUE);

	$db->sql_transaction('commit');

	return false;
}

/**
* Flips user_type from active to inactive and vice versa, handles
* group membership updates
*/
function user_active_flip($user_id, $user_type, $user_actkey = false, $username = false)
{
	global $db, $user, $auth;

	$sql = 'SELECT group_id, group_name 
		FROM ' . GROUPS_TABLE . " 
		WHERE group_name IN ('REGISTERED', 'REGISTERED_COPPA', 'INACTIVE', 'INACTIVE_COPPA')";
	$result = $db->sql_query($sql);

	$group_id_ary = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$group_id_ary[$row['group_name']] = $row['group_id'];
	}
	$db->sql_freeresult($result);

	$sql = 'SELECT group_id 
		FROM ' . USER_GROUP_TABLE . " 
		WHERE user_id = $user_id";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if ($group_name = array_search($row['group_id'], $group_id_ary))
		{
			break;
		}
	}
	$db->sql_freeresult($result);

	$current_group = ($user_type == USER_NORMAL) ? 'REGISTERED' : 'INACTIVE';
	$switch_group = ($user_type == USER_NORMAL) ? 'INACTIVE' : 'REGISTERED';

	$new_group_id = $group_id_ary[str_replace($current_group, $switch_group, $group_name)];

	$sql = 'UPDATE ' . USER_GROUP_TABLE . " 
		SET group_id = $new_group_id 
		WHERE user_id = $user_id
			AND group_id = " . $group_id_ary[$group_name];
	$db->sql_query($sql);

	$sql_ary = array(
		'user_type'		=> ($user_type == USER_NORMAL) ? USER_INACTIVE : USER_NORMAL
	);

	if ($new_group_id == $group_id_ary[$group_name])
	{
		$sql_ary['group_id'] = $new_group_id;
	}

	if ($user_actkey !== false)
	{
		$sql_ary['user_actkey'] = $user_actkey;
	}

	$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
		WHERE user_id = $user_id";
	$db->sql_query($sql);

	$auth->acl_clear_prefetch($user_id);

	if (!function_exists('add_log'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
	}

	if (!$username)
	{
		$sql = 'SELECT username
			FROM ' . USERS_TABLE . " 
			WHERE user_id = $user_id";
		$result = $db->sql_query($sql);
		
		extract($db->sql_fetchrow($result));
		$db->sql_freeresult($result);
	}

	$log = ($user_type == USER_NORMAL) ? 'LOG_USER_INACTIVE' : 'LOG_USER_ACTIVE';
	add_log('admin', $log, $username);

	return false;
}

/**
* Ban User
*/
function user_ban($mode, $ban, $ban_len, $ban_len_other, $ban_exclude, $ban_reason)
{
	global $db, $user, $auth;

	// Delete stale bans
	$sql = "DELETE FROM " . BANLIST_TABLE . "
		WHERE ban_end < " . time() . "
			AND ban_end <> 0";
	$db->sql_query($sql);

	$ban_list = (!is_array($ban)) ? array_unique(explode("\n", $ban)) : $ban;
	$ban_list_log = implode(', ', $ban_list);

	$current_time = time();

	if ($ban_len)
	{
		if ($ban_len != -1 || !$ban_len_other)
		{
			$ban_end = max($current_time, $current_time + ($ban_len) * 60);
		}
		else
		{
			$ban_other = explode('-', $ban_len_other);
			$ban_end = max($current_time, gmmktime(0, 0, 0, $ban_other[1], $ban_other[2], $ban_other[0]));
		}
	}
	else
	{
		$ban_end = 0;
	}

	$banlist = array();

	switch ($mode)
	{
		case 'user':
			$type = 'ban_userid';

			if (in_array('*', $ban_list))
			{
				$banlist[] = '*';
			}
			else
			{
				$sql = 'SELECT user_id
					FROM ' . USERS_TABLE . '
					WHERE username IN (' . implode(', ', array_diff(preg_replace('#^[\s]*(.*?)[\s]*$#', "'\\1'", $ban_list), array("''"))) . ')';
				$result = $db->sql_query($sql);

				if ($row = $db->sql_fetchrow($result))
				{
					do
					{
						$banlist[] = $row['user_id'];
					}
					while ($row = $db->sql_fetchrow($result));
				}
			}
			break;

		case 'ip':
			$type = 'ban_ip';

			foreach ($ban_list as $ban_item)
			{
				if (preg_match('#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})[ ]*\-[ ]*([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#', trim($ban_item), $ip_range_explode))
				{
					// Don't ask about all this, just don't ask ... !
					$ip_1_counter = $ip_range_explode[1];
					$ip_1_end = $ip_range_explode[5];

					while ($ip_1_counter <= $ip_1_end)
					{
						$ip_2_counter = ($ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[2] : 0;
						$ip_2_end = ($ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[6];

						if ($ip_2_counter == 0 && $ip_2_end == 254)
						{
							$ip_2_counter = 256;
							$ip_2_fragment = 256;

							$banlist[] = "'$ip_1_counter.*'";
						}

						while ($ip_2_counter <= $ip_2_end)
						{
							$ip_3_counter = ($ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[3] : 0;
							$ip_3_end = ($ip_2_counter < $ip_2_end || $ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[7];

							if ($ip_3_counter == 0 && $ip_3_end == 254)
							{
								$ip_3_counter = 256;
								$ip_3_fragment = 256;

								$banlist[] = "'$ip_1_counter.$ip_2_counter.*'";
							}

							while ($ip_3_counter <= $ip_3_end)
							{
								$ip_4_counter = ($ip_3_counter == $ip_range_explode[3] && $ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[4] : 0;
								$ip_4_end = ($ip_3_counter < $ip_3_end || $ip_2_counter < $ip_2_end) ? 254 : $ip_range_explode[8];

								if ($ip_4_counter == 0 && $ip_4_end == 254)
								{
									$ip_4_counter = 256;
									$ip_4_fragment = 256;

									$banlist[] = "'$ip_1_counter.$ip_2_counter.$ip_3_counter.*'";
								}

								while ($ip_4_counter <= $ip_4_end)
								{
									$banlist[] = "'$ip_1_counter.$ip_2_counter.$ip_3_counter.$ip_4_counter'";
									$ip_4_counter++;
								}
								$ip_3_counter++;
							}
							$ip_2_counter++;
						}
						$ip_1_counter++;
					}
				}
				else if (preg_match('#^([\w\-_]\.?){2,}$#is', trim($ban_item)))
				{
					$ip_ary = gethostbynamel(trim($ban_item));

					foreach ($ip_ary as $ip)
					{
						if (!empty($ip))
						{
							$banlist[] = "'" . $ip . "'";
						}
					}
				}
				else if (preg_match('#^([0-9]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})$#', trim($ban_item)) || preg_match('#^[a-f0-9:]+\*?$#i', trim($ban_item)))
				{
					$banlist[] = "'" . trim($ban_item) . "'";
				}
				else if (preg_match('#^\*$#', trim($ban_item)))
				{
					$banlist[] = "'*'";
				}
			}
			break;

		case 'email':
			$type = 'ban_email';

			foreach ($ban_list as $ban_item)
			{
				if (preg_match('#^.*?@*|(([a-z0-9\-]+\.)+([a-z]{2,3}))$#i', trim($ban_item)))
				{
					$banlist[] = "'" . trim($ban_item) . "'";
				}
			}
			break;
	}

	$sql = "SELECT $type
		FROM " . BANLIST_TABLE . "
		WHERE $type <> '' 
			AND ban_exclude = $ban_exclude";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$banlist_tmp = array();
		do
		{
			switch ($mode)
			{
				case 'user':
					$banlist_tmp[] = $row['ban_userid'];
					break;

				case 'ip':
					$banlist_tmp[] = "'" . $row['ban_ip'] . "'";
					break;

				case 'email':
					$banlist_tmp[] = "'" . $row['ban_email'] . "'";
					break;
			}
		}
		while ($row = $db->sql_fetchrow($result));

		$banlist = array_unique(array_diff($banlist, $banlist_tmp));
		unset($banlist_tmp);
	}

	if (sizeof($banlist))
	{
		$sql = '';
		foreach ($banlist as $ban_entry)
		{
			switch (SQL_LAYER)
			{
				case 'mysql':
					$sql .= (($sql != '') ? ', ' : '') . "($ban_entry, $current_time, $ban_end, $ban_exclude, '$ban_reason')";
					break;

				case 'mysql4':
				case 'mysqli':
				case 'mssql':
				case 'sqlite':
					$sql .= (($sql != '') ? ' UNION ALL ' : '') . " SELECT $ban_entry, $current_time, $ban_end, $ban_exclude, '$ban_reason'";
					break;

				default:
					$sql = 'INSERT INTO ' . BANLIST_TABLE . " ($type, ban_start, ban_end, ban_exclude, ban_reason)
						VALUES ($ban_entry, $current_time, $ban_end, $ban_exclude, '$ban_reason')";
					$db->sql_query($sql);
			}
		}

		if ($sql)
		{
			$sql = 'INSERT INTO ' . BANLIST_TABLE . " ($type, ban_start, ban_end, ban_exclude, ban_reason)
				VALUES $sql";
			$db->sql_query($sql);
		}

		if (!$ban_exclude)
		{
			$sql = '';
			switch ($mode)
			{
				case 'user':
					$sql = 'WHERE session_user_id IN (' . implode(', ', $banlist) . ')';
					break;

				case 'ip':
					$sql = 'WHERE session_ip IN (' . implode(', ', $banlist) . ')';
					break;

				case 'email':
					$sql = 'SELECT user_id
						FROM ' . USERS_TABLE . '
						WHERE user_email IN (' . implode(', ', $banlist) . ')';
					$result = $db->sql_query($sql);

					$sql_in = array();
					if ($row = $db->sql_fetchrow($result))
					{
						do
						{
							$sql_in[] = $row['user_id'];
						}
						while ($row = $db->sql_fetchrow($result));

						$sql = 'WHERE session_user_id IN (' . str_replace('*', '%', implode(', ', $sql_in)) . ")";
					}
					break;
			}

			if ($sql)
			{
				$sql = 'DELETE FROM ' . SESSIONS_TABLE . "
					$sql";
				$db->sql_query($sql);
			}
		}

		if (!function_exists('add_log'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
		}

		// Update log
		$log_entry = ($ban_exclude) ? 'LOG_BAN_EXCLUDE_' : 'LOG_BAN_';
		add_log('admin', $log_entry . strtoupper($mode), $ban_reason, $ban_list_log);
	}

	return false;
}

/**
* Unban User
*/
function user_unban($mode, $ban)
{
	global $db, $user, $auth;

	// Delete stale bans
	$sql = "DELETE FROM " . BANLIST_TABLE . "
		WHERE ban_end < " . time() . "
			AND ban_end <> 0";
	$db->sql_query($sql);

	$unban_sql = implode(', ', $ban);

	if ($unban_sql)
	{
		$l_unban_list = '';
		// Grab details of bans for logging information later
		switch ($mode)
		{
			case 'user':
				$sql = 'SELECT u.username AS unban_info
					FROM ' . USERS_TABLE . ' u, ' . BANLIST_TABLE . " b 
					WHERE b.ban_id IN ($unban_sql) 
						AND u.user_id = b.ban_userid";
				break;

			case 'email':
				$sql = 'SELECT ban_email AS unban_info 
					FROM ' . BANLIST_TABLE . "
					WHERE ban_id IN ($unban_sql)";
				break;

			case 'ip':
				$sql = 'SELECT ban_ip AS unban_info 
					FROM ' . BANLIST_TABLE . "
					WHERE ban_id IN ($unban_sql)";
				break;
		}
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$l_unban_list .= (($l_unban_list != '') ? ', ' : '') . $row['unban_info'];
		}

		$sql = 'DELETE FROM ' . BANLIST_TABLE . "
			WHERE ban_id IN ($unban_sql)";
		$db->sql_query($sql);

		if (!function_exists('add_log'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
		}

		add_log('admin', 'LOG_UNBAN_' . strtoupper($mode), $l_unban_list);
	}

	return false;

}

/**
* Whois facility
*/
function user_ipwhois($ip)
{
	$ipwhois = '';

	$match = array(
		'#RIPE\.NET#is'				=> 'whois.ripe.net',
		'#whois\.apnic\.net#is'		=> 'whois.apnic.net',
		'#nic\.ad\.jp#is'			=> 'whois.nic.ad.jp',
		'#whois\.registro\.br#is'	=> 'whois.registro.br'
	);

	if (($fsk = @fsockopen('whois.arin.net', 43)))
	{
		fputs($fsk, "$ip\n");
		while (!feof($fsk))
		{
			$ipwhois .= fgets($fsk, 1024);
		}
		@fclose($fsk);
	}

	foreach (array_keys($match) as $server)
	{
		if (preg_match($server, $ipwhois))
		{
			$ipwhois = '';
			if (($fsk = @fsockopen($match[$server], 43)))
			{
				fputs($fsk, "$ip\n");
				while (!feof($fsk))
				{
					$ipwhois .= fgets($fsk, 1024);
				}
				@fclose($fsk);
			}
			break;
		}
	}

	return $ipwhois;
}

/**
* Data validation ... used primarily but not exclusively by
* ucp modules
*
* "Master" function for validating a range of data types
*/
function validate_data($data, $val_ary)
{
	$error = array();

	foreach ($val_ary as $var => $val_seq)
	{
		if (!is_array($val_seq[0]))
		{
			$val_seq = array($val_seq);
		}

		foreach ($val_seq as $validate)
		{
			$function = array_shift($validate);
			array_unshift($validate, $data[$var]);

			if ($result = call_user_func_array('validate_' . $function, $validate))
			{
				$error[] = $result . '_' . strtoupper($var);
			}
		}
	}

	return $error;
}

/**
* Validate String
*/
function validate_string($string, $optional = false, $min = 0, $max = 0)
{
	if (empty($string) && $optional)
	{
		return false;
	}

	if ($min && strlen($string) < $min)
	{
		return 'TOO_SHORT';
	}
	else if ($max && strlen($string) > $max)
	{
		return 'TOO_LONG';
	}

	return false;
}

/**
* Validate Number
*/
function validate_num($num, $optional = false, $min = 0, $max = 1E99)
{
	if (empty($num) && $optional)
	{
		return false;
	}

	if ($num < $min)
	{
		return 'TOO_SMALL';
	}
	else if ($num > $max) 
	{
		return 'TOO_LARGE';
	}

	return false;
}

/**
* Validate Match
*/
function validate_match($string, $optional = false, $match)
{
	if (empty($string) && $optional)
	{
		return false;
	}

	if (!preg_match($match, $string))
	{
		return 'WRONG_DATA';
	}
	return false;
}

/**
* Check to see if the username has been taken, or if it is disallowed.
* Also checks if it includes the " character, which we don't allow in usernames.
* Used for registering, changing names, and posting anonymously with a username
*/
function validate_username($username)
{
	global $config, $db, $user;

	if (strtolower($user->data['username']) == strtolower($username))
	{
		return false;
	}

	if (!preg_match('#^' . str_replace('\\\\', '\\', $config['allow_name_chars']) . '$#i', $username))
	{
		return 'INVALID_CHARS';
	}

	$sql = 'SELECT username
		FROM ' . USERS_TABLE . "
		WHERE LOWER(username) = '" . strtolower($db->sql_escape($username)) . "'";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		return 'USERNAME_TAKEN';
	}
	$db->sql_freeresult($result);

	$sql = 'SELECT group_name
		FROM ' . GROUPS_TABLE . "
		WHERE LOWER(group_name) = '" . strtolower($db->sql_escape($username)) . "'";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		return 'USERNAME_TAKEN';
	}
	$db->sql_freeresult($result);

	$sql = 'SELECT disallow_username
		FROM ' . DISALLOW_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if (preg_match('#' . str_replace('*', '.*?', preg_quote($row['disallow_username'], '#')) . '#i', $username))
		{
			return 'USERNAME_DISALLOWED';
		}
	}
	$db->sql_freeresult($result);

	$sql = 'SELECT word
		FROM  ' . WORDS_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if (preg_match('#(' . str_replace('\*', '.*?', preg_quote($row['word'], '#')) . ')#i', $username))
		{
			return 'USERNAME_DISALLOWED';
		}
	}
	$db->sql_freeresult($result);

	return false;
}

/**
* Check to see if email address is banned or already present in the DB
*/
function validate_email($email)
{
	global $config, $db, $user;

	if (strtolower($user->data['user_email']) == strtolower($email))
	{
		return false;
	}

	if (!preg_match('#^[a-z0-9\.\-_\+]+?@(.*?\.)*?[a-z0-9\-_]+?\.[a-z]{2,4}$#i', $email))
	{
		return 'EMAIL_INVALID';
	}

	$sql = 'SELECT ban_email
		FROM ' . BANLIST_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if (preg_match('#^' . str_replace('*', '.*?', $row['ban_email']) . '$#i', $email))
		{
			return 'EMAIL_BANNED';
		}
	}
	$db->sql_freeresult($result);

	if (!$config['allow_emailreuse'])
	{
		$sql = 'SELECT user_email_hash
			FROM ' . USERS_TABLE . "
			WHERE user_email_hash = " . crc32(strtolower($email)) . strlen($email);
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			return 'EMAIL_TAKEN';
		}
		$db->sql_freeresult($result);
	}

	return false;
}

/**
* Remove avatar
*/
function avatar_delete($id)
{
	global $phpbb_root_path, $config, $db, $user;

	if (file_exists($phpbb_root_path . $config['avatar_path'] . '/' . $id))
	{
		@unlink($phpbb_root_path . $config['avatar_path'] . '/' . $id);
	}

	return false;
 }

/**
* Remote avatar linkage
*/
function avatar_remote($data, &$error)
{
	global $config, $db, $user, $phpbb_root_path;

	if (!preg_match('#^(http|https|ftp)://#i', $data['remotelink']))
	{
		$data['remotelink'] = 'http://' . $data['remotelink'];
	}

	if (!preg_match('#^(http|https|ftp)://(.*?\.)*?[a-z0-9\-]+?\.[a-z]{2,4}:?([0-9]*?).*?\.(gif|jpg|jpeg|png)$#i', $data['remotelink']))
	{
		$error[] = $user->lang['AVATAR_URL_INVALID'];
		return false;
	}

	if ((!($data['width'] || $data['height']) || $data['remotelink'] != $user->data['user_avatar']) && ($config['avatar_max_width'] || $config['avatar_max_height']))
	{
		list($width, $height) = @getimagesize($data['remotelink']);

		if (!$width || !$height)
		{
			$error[] = $user->lang['AVATAR_NO_SIZE'];
			return false;
		}
		else if ($width > $config['avatar_max_width'] || $height > $config['avatar_max_height'])
		{
			$error[] = sprintf($user->lang['AVATAR_WRONG_SIZE'], $config['avatar_max_width'], $config['avatar_max_height']);
			return false;
		}
	}
	else if ($data['width'] > $config['avatar_max_width'] || $data['height'] > $config['avatar_max_height'])
	{
		$error[] = sprintf($user->lang['AVATAR_WRONG_SIZE'], $config['avatar_max_width'], $config['avatar_max_height']);
		return false;
	}

	return array(AVATAR_REMOTE, $data['remotelink'], $width, $height);
}

/**
* Avatar upload using the upload class
*/
function avatar_upload($data, &$error)
{
	global $phpbb_root_path, $config, $db, $user;

	// Init upload class
	include_once($phpbb_root_path . 'includes/functions_upload.php');
	$upload = new fileupload('AVATAR_', array('jpg', 'jpeg', 'gif', 'png'), $config['avatar_filesize'], $config['avatar_min_width'], $config['avatar_min_height'], $config['avatar_max_width'], $config['avatar_max_height']);
							
	if (!empty($_FILES['uploadfile']['name']))
	{
		$file = $upload->form_upload('uploadfile');
	}
	else
	{
		$file = $upload->remote_upload($data['uploadurl']);
	}

	$file->clean_filename('real', $user->data['user_id'] . '_');
	$file->move_file($config['avatar_path']);

	if (sizeof($file->error))
	{
		$file->remove();
		$error = array_merge($error, $file->error);
	}
	
	return array(AVATAR_UPLOAD, $file->get('realname'), $file->get('width'), $file->get('height'));
}

/**
* Avatar Gallery
*/
function avatar_gallery($category, &$error)
{
	global $config, $phpbb_root_path, $user;

	$path = $phpbb_root_path . $config['avatar_gallery_path'];

	if (!file_exists($path) || !is_dir($path))
	{
		return array($user->lang['NONE'] => array());
	}

	// To be replaced with SQL ... before M3 completion
	$dp = @opendir($path);

	$data = array();
	$avatar_row_count = $avatar_col_count = 0;
	while ($file = readdir($dp))
	{
		if ($file{0} != '.' && is_dir("$path/$file"))
		{
			$dp2 = @opendir("$path/$file");

			while ($sub_file = readdir($dp2))
			{
				if (preg_match('#\.(gif$|png$|jpg|jpeg)$#i', $sub_file))
				{
					$data[$file][$avatar_row_count][$avatar_col_count]['file'] = "$file/$sub_file"; 
					$data[$file][$avatar_row_count][$avatar_col_count]['name'] = ucfirst(str_replace('_', ' ', preg_replace('#^(.*)\..*$#', '\1', $sub_file)));

					$avatar_col_count++;
					if ($avatar_col_count == 4)
					{
						$avatar_row_count++;
						$avatar_col_count = 0;
					}
				}
			}
			closedir($dp2);
		}
	}
	closedir($dp);

	if (!sizeof($data))
	{
		return array($user->lang['NONE'] => array());
	}
	
	@ksort($data);

	return $data;
}

//
// Usergroup functions
//

/**
* Add or edit a group. If we're editing a group we only update user
* parameters such as rank, etc. if they are changed
*/
function group_create($group_id, $type, $name, $desc)
{
	global $phpbb_root_path, $config, $db, $user, $file_upload;

	$error = array();

	// Check data
	if (!strlen($name) || strlen($name) > 40)
	{
		$error[] = (!strlen($name)) ? $user->lang['GROUP_ERR_USERNAME'] : $user->lang['GROUP_ERR_USER_LONG'];
	}

	if (strlen($desc) > 255)
	{
		$error[] = $user->lang['GROUP_ERR_DESC_LONG'];
	}

	if (!in_array($type, array(GROUP_OPEN, GROUP_CLOSED, GROUP_HIDDEN, GROUP_SPECIAL, GROUP_FREE)))
	{
		$error[] = $user->lang['GROUP_ERR_TYPE'];
	}

	if (!sizeof($error))
	{
		$sql_ary = array(
			'group_name'			=> (string) $name,
			'group_description'		=> (string) $desc,
			'group_type'			=> (int) $type,
		);

		$attribute_ary = array('group_colour' => 'string', 'group_rank' => 'int', 'group_avatar' => 'string', 'group_avatar_type' => 'int', 'group_avatar_width' => 'int', 'group_avatar_height' => 'int');

		$i = 4;
		foreach ($attribute_ary as $attribute => $type)
		{
			if (func_num_args() > $i && ($value = func_get_arg($i)) !== false)
			{
				settype($value, $type);

				$sql_ary[$attribute] = $$attribute = $value;
			}
			$i++;
		}

		$group_only_ary = array('group_receive_pm' => 'int', 'group_message_limit' => 'int');

		foreach ($group_only_ary as $attribute => $type)
		{
			if (func_num_args() > $i && ($value = func_get_arg($i)) !== false)
			{
				settype($value, $type);

				$sql_ary[$attribute] = $value;
			}
			$i++;
		}

		$sql = ($group_id) ? 'UPDATE ' . GROUPS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "	WHERE group_id = $group_id" : 'INSERT INTO ' . GROUPS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		$sql_ary = array();
		foreach ($attribute_ary as $attribute => $type)
		{
			if (isset($$attribute))
			{
				$sql_ary[str_replace('group', 'user', $attribute)] = $$attribute;
			}
		}

		if (sizeof($sql_ary))
		{
			$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE group_id = $group_id";
			$db->sql_query($sql);
		}

		if (!function_exists('add_log'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
		}

		$log = ($group_id) ? 'LOG_GROUP_UPDATED' : 'LOG_GROUP_CREATED';
		add_log('admin', $log, $name);
	}

	return (sizeof($error)) ? $error : false;
}

/**
* Group Delete
*/
function group_delete($group_id, $group_name = false)
{
	global $db;

	if (!$group_name)
	{
		$sql = 'SELECT group_name
			FROM ' . GROUPS_TABLE . " 
			WHERE group_id = $group_id";
		$result = $db->sql_query($sql);

		if (!extract($db->sql_fetchrow($result)))
		{
			trigger_error("Could not obtain name of group $group_id", E_USER_ERROR);
		}
		$db->sql_freeresult($result);
	}

	$start = 0;

	do
	{
		$user_id_ary = $username_ary = array();

		// Batch query for group members, call group_user_del
		$sql = 'SELECT u.user_id, u.username
			FROM ' . USER_GROUP_TABLE . ' ug, ' . USERS_TABLE . " u
			WHERE ug.group_id = $group_id
				AND u.user_id = ug.user_id 
			LIMIT $start, 200";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$user_id_ary[] = $row['user_id'];
				$username_ary[] = $row['username'];

				$start++;
			}
			while ($row = $db->sql_fetchrow($result));

			group_user_del($group_id, $user_id_ary, $username_ary, $group_name);
		}
		else
		{
			$start = 0;
		}
		$db->sql_freeresult($result);
	}
	while ($start);
	
	// Delete group
	$sql = 'DELETE FROM ' . GROUPS_TABLE . " 
		WHERE group_id = $group_id";
	$db->sql_query($sql);

	if (!function_exists('add_log'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
	}

	add_log('admin', 'LOG_GROUP_DELETE', $group_name);

	return false;
}

/**
* Add user(s) to group
*/
function group_user_add($group_id, $user_id_ary = false, $username_ary = false, $group_name = false, $default = false, $leader = 0, $pending = 0)
{
	global $db, $auth;

	// We need both username and user_id info
	user_get_id_name($user_id_ary, $username_ary);

	// Remove users who are already members of this group
	$sql = 'SELECT user_id, group_leader  
		FROM ' . USER_GROUP_TABLE . '   
		WHERE user_id IN (' . implode(', ', $user_id_ary) . ") 
			AND group_id = $group_id";
	$result = $db->sql_query($sql);

	$add_id_ary = $update_id_ary = array();
	if ($row = $db->sql_fetchrow($result))
	{
		do
		{
			$add_id_ary[] = $row['user_id'];

			if ($leader && !$row['group_leader'])
			{
				$update_id_ary[] = $row['user_id'];
			}
		}
		while ($row = $db->sql_fetchrow($result));
	}
	$db->sql_freeresult($result);

	// Do all the users exist in this group?
	$add_id_ary = array_diff($user_id_ary, $add_id_ary);

	// If we have no users 
	if (!sizeof($add_id_ary) && !sizeof($update_id_ary))
	{
		return 'GROUP_USERS_EXIST';
	}

	if (sizeof($add_id_ary))
	{
		// Insert the new users 
		switch (SQL_LAYER)
		{
			case 'mysql':
			case 'mysql4':
			case 'mysqli':
			case 'mssql':
			case 'sqlite':
				$sql = 'INSERT INTO ' . USER_GROUP_TABLE . " (user_id, group_id, group_leader, user_pending) 
					VALUES " . implode(', ', preg_replace('#^([0-9]+)$#', "(\\1, $group_id, $leader, $pending)",  $add_id_ary));
				$db->sql_query($sql);
				break;

			default:
				foreach ($add_id_ary as $user_id)
				{
					$sql = 'INSERT INTO ' . USER_GROUP_TABLE . " (user_id, group_id, group_leader, user_pending)
						VALUES ($user_id, $group_id, $leader, $pending)";
					$db->sql_query($sql);
				}
				break;
		}
	}

	$usernames = array();
	if (sizeof($update_id_ary))
	{
		$sql = 'UPDATE ' . USER_GROUP_TABLE . ' 
			SET group_leader = 1 
			WHERE user_id IN (' . implode(', ', $update_id_ary) . ")
				AND group_id = $group_id";
		$db->sql_query($sql);

		foreach ($update_id_ary as $id)
		{
			$usernames[] = $username_ary[$id];
		}
	}
	else
	{
		foreach ($add_id_ary as $id)
		{
			$usernames[] = $username_ary[$id];
		}
	}

	if ($default)
	{
		$attribute_ary = array('group_colour' => 'string', 'group_rank' => 'int', 'group_avatar' => 'string', 'group_avatar_type' => 'int', 'group_avatar_width' => 'int', 'group_avatar_height' => 'int');

		// Were group attributes passed to the function? If not we need to obtain them
		if (func_num_args() > 6)
		{
			$i = 6;
			foreach ($attribute_ary as $attribute => $type)
			{
				if (func_num_args() > $i && ($value = func_get_arg($i)) !== false)
				{
					settype($value, $type);

					$sql_ary[$attribute] = $$attribute = $value;
				}
				$i++;
			}
		}
		else
		{
			$sql = 'SELECT group_colour, group_rank, group_avatar, group_avatar_type, group_avatar_width, group_avatar_height  
				FROM ' . GROUPS_TABLE . " 
				WHERE group_id = $group_id";
			$result = $db->sql_query($sql);

			if (!extract($db->sql_fetchrow($result)))
			{
				trigger_error("Could not obtain group attributes for group_id $group_id", E_USER_ERROR);
			}
			$db->sql_freeresult($result);

			if (!$group_avatar_width)
			{
				unset($group_avatar_width);
			}
			if (!$group_avatar_height)
			{
				unset($group_avatar_height);
			}
		}

		$sql_set = '';
		foreach ($attribute_ary as $attribute => $type)
		{
			if (isset($$attribute))
			{
				$field = str_replace('group_', 'user_', $attribute);

				switch ($type)
				{
					case 'int':
						$sql_set .= ", $field = " . (int) $$attribute;
						break;
					case 'double':
						$sql_set .= ", $field = " . (double) $$attribute;
						break;
					case 'string':
						$sql_set .= ", $field = '" . (string) $db->sql_escape($$attribute) . "'";
						break;
				}
			}
		}

		$sql = 'UPDATE ' . USERS_TABLE . "
			SET group_id = $group_id$sql_set  
			WHERE user_id IN (" . implode(', ', $user_id_ary) . ')';
		$db->sql_query($sql);
	}

	// Clear permissions cache of relevant users
	$auth->acl_clear_prefetch($user_id_ary);

	if (!$group_name)
	{
		$sql = 'SELECT group_name
			FROM ' . GROUPS_TABLE . " 
			WHERE group_id = $group_id";
		$result = $db->sql_query($sql);

		if (!extract($db->sql_fetchrow($result)))
		{
			trigger_error("Could not obtain name of group $group_id", E_USER_ERROR);
		}

		$db->sql_freeresult($result);
	}

	if (!function_exists('add_log'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
	}

	$log = ($leader) ? 'LOG_MODS_ADDED' : 'LOG_USERS_ADDED';

	add_log('admin', $log, $group_name, implode(', ', $username_ary));

	unset($username_ary, $user_id_ary);

	return false;
}

/**
* Remove a user/s from a given group. When we remove users we update their
* default group_id. We do this by examining which "special" groups they belong
* to. The selection is made based on a reasonable priority system
*/
function group_user_del($group_id, $user_id_ary = false, $username_ary = false, $group_name = false)
{
	global $db, $auth;

	$group_order = array('ADMINISTRATORS', 'SUPER_MODERATORS', 'REGISTERED_COPPA', 'REGISTERED', 'BOTS', 'GUESTS');

	$attribute_ary = array('group_colour' => 'string', 'group_rank' => 'int', 'group_avatar' => 'string', 'group_avatar_type' => 'int', 'group_avatar_width' => 'int', 'group_avatar_height' => 'int');

	// We need both username and user_id info
	user_get_id_name($user_id_ary, $username_ary);

	$sql = 'SELECT * 
		FROM ' . GROUPS_TABLE . ' 
		WHERE group_name IN (' . implode(', ', preg_replace('#^(.*)$#', "'\\1'", $group_order)) . ')';
	$result = $db->sql_query($sql);

	$group_order_id = $special_group_data = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$group_order_id[$row['group_name']] = $row['group_id'];

		$special_group_data[$row['group_id']]['group_colour']			= $row['group_colour'];
		$special_group_data[$row['group_id']]['group_rank']				= $row['group_rank'];
		$special_group_data[$row['group_id']]['group_avatar']			= $row['group_avatar'];
		$special_group_data[$row['group_id']]['group_avatar_type']		= $row['group_avatar_type'];
		$special_group_data[$row['group_id']]['group_avatar_width']		= $row['group_avatar_width'];
		$special_group_data[$row['group_id']]['group_avatar_height']	= $row['group_avatar_height'];
	}
	$db->sql_freeresult($result);

	// Get users default groups - we only need to reset default group membership if the group from which the user gets removed is set as default
	$sql = 'SELECT user_id, group_id
		FROM ' . USERS_TABLE . '
		WHERE user_id IN (' . implode(', ', $user_id_ary) . ")";
	$result = $db->sql_query($sql);
	
	$default_groups = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$default_groups[$row['user_id']] = $row['group_id'];
	}
	$db->sql_freeresult($result);
	
	// What special group memberships exist for these users?
	$sql = 'SELECT g.group_id, g.group_name, ug.user_id
		FROM ' . USER_GROUP_TABLE . ' ug, ' . GROUPS_TABLE . ' g 
		WHERE ug.user_id IN (' . implode(', ', $user_id_ary) . ") 
			AND g.group_id = ug.group_id
			AND g.group_id <> $group_id 
			AND g.group_type = " . GROUP_SPECIAL . '
		ORDER BY ug.user_id, g.group_id';
	$result = $db->sql_query($sql);

	$temp_ary = array();
	while ($row = $db->sql_fetchrow($result))
	{
		if ($default_groups[$row['user_id']] == $group_id && (!isset($temp_ary[$row['user_id']]) || array_search($row['group_name'], $group_order) < $temp_ary[$row['user_id']]))
		{
			$temp_ary[$row['user_id']] = $row['group_id'];
		}
	}
	$db->sql_freeresult($result);

	$sql_where_ary = array();
	foreach ($temp_ary as $uid => $gid)
	{
		$sql_where_ary[$gid][] = $uid;
	}
	unset($temp_ary);

	foreach ($special_group_data as $gid => $default_data_ary)
	{
		if (isset($sql_where_ary[$gid]) && $sql_where = implode(', ', $sql_where_ary[$gid]))
		{
			$sql_set = '';
			foreach ($special_group_data[$gid] as $attribute => $value)
			{
				$field = str_replace('group_', 'user_', $attribute);

				switch ($attribute_ary[$attribute])
				{
					case 'int':
						$sql_set .= ", $field = " . (int) $value;
						break;
					case 'double':
						$sql_set .= ", $field = " . (double) $value;
						break;
					case 'string':
						$sql_set .= ", $field = '" . $db->sql_escape($value) . "'";
						break;
				}
			}

			// Set new default
			$sql = 'UPDATE ' . USERS_TABLE . " 
				SET group_id = $gid$sql_set 
				WHERE user_id IN (" . implode(', ', $sql_where_ary[$gid]) . ')';
			$db->sql_query($sql);
		}
	}
	unset($special_group_data);

	$sql = 'DELETE FROM ' . USER_GROUP_TABLE . " 
		WHERE group_id = $group_id
			AND user_id IN (" . implode(', ', $user_id_ary) . ')';
	$db->sql_query($sql);
	unset($default_ary);

	// Clear permissions cache of relevant users
	$auth->acl_clear_prefetch($user_id_ary);

	if (!$group_name)
	{
		$sql = 'SELECT group_name
			FROM ' . GROUPS_TABLE . " 
			WHERE group_id = $group_id";
		$result = $db->sql_query($sql);

		if (!extract($db->sql_fetchrow($result)))
		{
			trigger_error("Could not obtain name of group $group_id", E_USER_ERROR);
		}
	}

	if (!function_exists('add_log'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
	}

	$log = 'LOG_GROUP_REMOVE';

	add_log('admin', $log, $group_name, implode(', ', $username_ary));

	unset($username_ary);
	unset($user_id_ary);

	return false;
}

/**
* This is used to promote (to leader), demote or set as default a member/s
*/
function group_user_attributes($action, $group_id, $user_id_ary = false, $username_ary = false, $group_name = false)
{
	global $db, $auth;

	// We need both username and user_id info
	user_get_id_name($user_id_ary, $username_ary);

	switch ($action)
	{
		case 'demote':
		case 'promote':
			$sql = 'UPDATE ' . USER_GROUP_TABLE . '
				SET group_leader = ' . (($action == 'promote') ? 1 : 0) . "  
				WHERE group_id = $group_id
					AND user_id IN (" . implode(', ', $user_id_ary) . ')';
			$db->sql_query($sql);

			$log = ($action == 'promote') ? 'LOG_GROUP_PROMOTED' : 'LOG_GROUP_DEMOTED';
			break;

		case 'approve':
			$sql = 'UPDATE ' . USER_GROUP_TABLE . " 
				SET user_pending = 0 
				WHERE group_id = $group_id 
					AND user_id IN (" . implode(', ', $user_id_ary) . ')';
			$db->sql_query($sql);

			$log = 'LOG_GROUP_APPROVE';
			break;

		case 'default':
			$attribute_ary = array('group_colour' => 'string', 'group_rank' => 'int', 'group_avatar' => 'string', 'group_avatar_type' => 'int', 'group_avatar_width' => 'int', 'group_avatar_height' => 'int');

			// Were group attributes passed to the function? If not we need
			// to obtain them
			if (func_num_args() > 5)
			{
				$i = 5;
				foreach ($attribute_ary as $attribute => $type)
				{
					if (func_num_args() > $i && ($value = func_get_arg($i)) !== false)
					{
						settype($value, $type);

						$sql_ary[$attribute] = $$attribute = $value;
					}
					$i++;
				}
			}
			else
			{
				$sql = 'SELECT group_colour, group_rank, group_avatar, group_avatar_type, group_avatar_width, group_avatar_height 
					FROM ' . GROUPS_TABLE . " 
					WHERE group_id = $group_id";
				$result = $db->sql_query($sql);

				if (!extract($db->sql_fetchrow($result)))
				{
					return 'NO_GROUP';
				}
				$db->sql_freeresult($result);

				if (!$group_avatar_width)
				{
					unset($group_avatar_width);
				}
				if (!$group_avatar_height)
				{
					unset($group_avatar_height);
				}
			}

			// FAILURE HERE when grabbing data from DB and checking "isset" ... will
			// be true for all similar functionality

			$sql_set = '';
			foreach ($attribute_ary as $attribute => $type)
			{
				if (isset($$attribute))
				{
					$field = str_replace('group_', 'user_', $attribute);

					switch ($type)
					{
						case 'int':
							$sql_set .= ", $field = " . (int) $$attribute;
							break;
						case 'double':
							$sql_set .= ", $field = " . (double) $$attribute;
							break;
						case 'string':
							$sql_set .= ", $field = '" . (string) $db->sql_escape($$attribute) . "'";
							break;
					}
				}
			}

			$sql = 'UPDATE ' . USERS_TABLE . "
				SET group_id = $group_id$sql_set  
				WHERE user_id IN (" . implode(', ', $user_id_ary) . ')';
			$db->sql_query($sql);

			$log = 'LOG_GROUP_DEFAULTS';
			break;
	}

	if (!function_exists('add_log'))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
	}

	// Clear permissions cache of relevant users
	$auth->acl_clear_prefetch($user_id_ary);

	if (!$group_name)
	{
		$sql = 'SELECT group_name
			FROM ' . GROUPS_TABLE . " 
			WHERE group_id = $group_id";
		$result = $db->sql_query($sql);

		if (!extract($db->sql_fetchrow($result)))
		{
			trigger_error("Could not obtain name of group $group_id", E_USER_ERROR);
		}
	}

	add_log('admin', $log, $group_name, implode(', ', $username_ary));

	unset($username_ary);
	unset($user_id_ary);

	return false;
}

/**
* Obtain either the members of a specified group, the groups the specified user is subscribed to
* or checking if a specified user is in a specified group
*
* Note: Never use this more than once... first group your users/groups
*/
function group_memberships($group_id_ary = false, $user_id_ary = false, $return_bool = false)
{
	global $db;

	if (!$group_id_ary && !$user_id_ary)
	{
		return true;
	}

	$sql = 'SELECT ug.*, u.username, u.user_email
		FROM ' . USER_GROUP_TABLE . ' ug, ' . USERS_TABLE . ' u
		WHERE ug.user_id = u.user_id AND ';

	if ($group_id_ary && $user_id_ary)
	{
		$sql .= " ug.group_id " . ((is_array($group_id_ary)) ? ' IN (' . implode(', ', $group_id_ary) . ')' : " = $group_id_ary") . "
				AND ug.user_id " . ((is_array($user_id_ary)) ? ' IN (' . implode(', ', $user_id_ary) . ')' : " = $user_id_ary");
	}
	else if ($group_id)
	{
		$sql .= " ug.group_id " . ((is_array($group_id_ary)) ? ' IN (' . implode(', ', $group_id_ary) . ')' : " = $group_id_ary");
	}
	else if ($user_id_ary)
	{
		$sql .= " ug.user_id " . ((is_array($user_id_ary)) ? ' IN (' . implode(', ', $user_id_ary) . ')' : " = $user_id_ary");
	}
	
	$result = ($return_bool) ? $db->sql_query_limit($sql, 1) : $db->sql_query($sql);

	$row = $db->sql_fetchrow($result);

	if ($return_bool)
	{
		$db->sql_freeresult($result);
		return ($row) ? true : false;
	}

	if (!$row)
	{
		return false;
	}

	$return = array();

	do
	{
		$return[] = $row;
	}
	while ($row = $db->sql_fetchrow($result));

	$db->sql_freeresult($result);

	return $return;
}

?>