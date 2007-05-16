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
*
* @param array &$user_id_ary The user ids to check or empty if usernames used
* @param array &$username_ary The usernames to check or empty if user ids used
* @param mixed $user_type Array of user types to check, false if not restricting by user type
*/
function user_get_id_name(&$user_id_ary, &$username_ary, $user_type = false)
{
	global $db;

	// Are both arrays already filled? Yep, return else
	// are neither array filled?
	if ($user_id_ary && $username_ary)
	{
		return false;
	}
	else if (!$user_id_ary && !$username_ary)
	{
		return 'NO_USERS';
	}

	$which_ary = ($user_id_ary) ? 'user_id_ary' : 'username_ary';

	if ($$which_ary && !is_array($$which_ary))
	{
		$$which_ary = array($$which_ary);
	}

	$sql_in = ($which_ary == 'user_id_ary') ? array_map('intval', $$which_ary) : array_map('utf8_clean_string', $$which_ary);
	unset($$which_ary);

	$user_id_ary = $username_ary = array();

	// Grab the user id/username records
	$sql_where = ($which_ary == 'user_id_ary') ? 'user_id' : 'username_clean';
	$sql = 'SELECT user_id, username
		FROM ' . USERS_TABLE . '
		WHERE ' . $db->sql_in_set($sql_where, $sql_in);

	if ($user_type !== false && !empty($user_type))
	{
		$sql .= ' AND ' . $db->sql_in_set('user_type', $user_type);
	}

	$result = $db->sql_query($sql);

	if (!($row = $db->sql_fetchrow($result)))
	{
		$db->sql_freeresult($result);
		return 'NO_USERS';
	}

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
* Get latest registered username and update database to reflect it
*/
function update_last_username()
{
	global $db;

	// Get latest username
	$sql = 'SELECT user_id, username, user_colour
		FROM ' . USERS_TABLE . '
		WHERE user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')
		ORDER BY user_id DESC';
	$result = $db->sql_query_limit($sql, 1);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if ($row)
	{
		set_config('newest_user_id', $row['user_id'], true);
		set_config('newest_username', $row['username'], true);
		set_config('newest_user_colour', $row['user_colour'], true);
	}
}

/**
* Updates a username across all relevant tables/fields
*
* @param string $old_name the old/current username
* @param string $new_name the new username
*/
function user_update_name($old_name, $new_name)
{
	global $config, $db, $cache;

	$update_ary = array(
		FORUMS_TABLE			=> array('forum_last_poster_name'),
		MODERATOR_CACHE_TABLE	=> array('username'),
		POSTS_TABLE				=> array('post_username'),
		TOPICS_TABLE			=> array('topic_first_poster_name', 'topic_last_poster_name'),
	);

	foreach ($update_ary as $table => $field_ary)
	{
		foreach ($field_ary as $field)
		{
			$sql = "UPDATE $table
				SET $field = '" . $db->sql_escape($new_name) . "'
				WHERE $field = '" . $db->sql_escape($old_name) . "'";
			$db->sql_query($sql);
		}
	}

	if ($config['newest_username'] == $old_name)
	{
		set_config('newest_username', $new_name, true);
	}
}

/**
* Add User
*/
function user_add($user_row, $cp_data = false)
{
	global $db, $user, $auth, $config, $phpbb_root_path, $phpEx;

	if (empty($user_row['username']) || !isset($user_row['group_id']) || !isset($user_row['user_email']) || !isset($user_row['user_type']))
	{
		return false;
	}

	$sql_ary = array(
		'username'			=> $user_row['username'],
		'username_clean'	=> utf8_clean_string($user_row['username']),
		'user_password'		=> (isset($user_row['user_password'])) ? $user_row['user_password'] : '',
		'user_pass_convert'	=> 0,
		'user_email'		=> strtolower($user_row['user_email']),
		'user_email_hash'	=> crc32(strtolower($user_row['user_email'])) . strlen($user_row['user_email']),
		'group_id'			=> $user_row['group_id'],
		'user_type'			=> $user_row['user_type'],
	);

	// These are the additional vars able to be specified
	$additional_vars = array(
		'user_permissions'	=> '',
		'user_timezone'		=> $config['board_timezone'],
		'user_dateformat'	=> $config['default_dateformat'],
		'user_lang'			=> $config['default_lang'],
		'user_style'		=> $config['default_style'],
		'user_allow_pm'		=> 1,
		'user_actkey'		=> '',
		'user_ip'			=> '',
		'user_regdate'		=> time(),
		'user_passchg'		=> time(),

		'user_inactive_reason'	=> 0,
		'user_inactive_time'	=> 0,
		'user_lastmark'			=> time(),
		'user_lastvisit'		=> 0,
		'user_lastpost_time'	=> 0,
		'user_lastpage'			=> '',
		'user_posts'			=> 0,
		'user_dst'				=> 0,
		'user_colour'			=> '',
		'user_occ'				=> '',
		'user_interests'		=> '',
		'user_avatar'			=> '',
		'user_avatar_type'		=> 0,
		'user_avatar_width'		=> 0,
		'user_avatar_height'	=> 0,
		'user_new_privmsg'		=> 0,
		'user_unread_privmsg'	=> 0,
		'user_last_privmsg'		=> 0,
		'user_message_rules'	=> 0,
		'user_full_folder'		=> PRIVMSGS_NO_BOX,
		'user_emailtime'		=> 0,

		'user_notify'			=> 0,
		'user_notify_pm'		=> 1,
		'user_notify_type'		=> NOTIFY_EMAIL,
		'user_allow_pm'			=> 1,
		'user_allow_viewonline'	=> 1,
		'user_allow_viewemail'	=> 1,
		'user_allow_massemail'	=> 1,

		'user_sig'					=> '',
		'user_sig_bbcode_uid'		=> '',
		'user_sig_bbcode_bitfield'	=> '',
	);

	// Now fill the sql array with not required variables
	foreach ($additional_vars as $key => $default_value)
	{
		$sql_ary[$key] = (isset($user_row[$key])) ? $user_row[$key] : $default_value;
	}

	// Any additional variables in $user_row not covered above?
	$remaining_vars = array_diff(array_keys($user_row), array_keys($sql_ary));

	// Now fill our sql array with the remaining vars
	if (sizeof($remaining_vars))
	{
		foreach ($remaining_vars as $key)
		{
			$sql_ary[$key] = $user_row[$key];
		}
	}

	$sql = 'INSERT INTO ' . USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
	$db->sql_query($sql);

	$user_id = $db->sql_nextid();

	// Insert Custom Profile Fields
	if ($cp_data !== false && sizeof($cp_data))
	{
		$cp_data['user_id'] = (int) $user_id;

		if (!class_exists('custom_profile'))
		{
			include_once($phpbb_root_path . 'includes/functions_profile_fields.' . $phpEx);
		}

		$sql = 'INSERT INTO ' . PROFILE_FIELDS_DATA_TABLE . ' ' . 
			$db->sql_build_array('INSERT', custom_profile::build_insert_sql_array($cp_data));
		$db->sql_query($sql);
	}

	// Place into appropriate group...
	$sql = 'INSERT INTO ' . USER_GROUP_TABLE . ' ' . $db->sql_build_array('INSERT', array(
		'user_id'		=> (int) $user_id,
		'group_id'		=> (int) $user_row['group_id'],
		'user_pending'	=> 0)
	);
	$db->sql_query($sql);

	// Now make it the users default group...
	group_set_user_default($user_row['group_id'], array($user_id), false);

	// set the newest user and adjust the user count if the user is a normal user and no activation mail is sent
	if ($user_row['user_type'] == USER_NORMAL)
	{
		set_config('newest_user_id', $user_id, true);
		set_config('newest_username', $user_row['username'], true);
		set_config('num_users', $config['num_users'] + 1, true);

		$sql = 'SELECT group_colour
			FROM ' . GROUPS_TABLE . '
			WHERE group_id = ' . $user_row['group_id'];
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		set_config('newest_user_colour', $row['group_colour'], true);
	}

	return $user_id;
}

/**
* Remove User
*/
function user_delete($mode, $user_id, $post_username = false)
{
	global $cache, $config, $db, $user, $auth;
	global $phpbb_root_path, $phpEx;

	$sql = 'SELECT *
		FROM ' . USERS_TABLE . '
		WHERE user_id = ' . $user_id;
	$result = $db->sql_query($sql);
	$user_row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$user_row)
	{
		return false;
	}

	$db->sql_transaction('begin');

	// Before we begin, we will remove the reports the user issued.
	$sql = 'SELECT r.post_id, p.topic_id
		FROM ' . REPORTS_TABLE . ' r, ' . POSTS_TABLE . ' p
		WHERE r.user_id = ' . $user_id . '
			AND p.post_id = r.post_id';
	$result = $db->sql_query($sql);

	$report_posts = $report_topics = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$report_posts[] = $row['post_id'];
		$report_topics[] = $row['topic_id'];
	}
	$db->sql_freeresult($result);

	if (sizeof($report_posts))
	{
		$report_posts = array_unique($report_posts);
		$report_topics = array_unique($report_topics);

		// Get a list of topics that still contain reported posts
		$sql = 'SELECT DISTINCT topic_id
			FROM ' . POSTS_TABLE . '
			WHERE ' . $db->sql_in_set('topic_id', $report_topics) . '
				AND post_reported = 1
				AND ' . $db->sql_in_set('post_id', $report_posts, true);
		$result = $db->sql_query($sql);

		$keep_report_topics = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$keep_report_topics[] = $row['topic_id'];
		}
		$db->sql_freeresult($result);

		if (sizeof($keep_report_topics))
		{
			$report_topics = array_diff($report_topics, $keep_report_topics);
		}
		unset($keep_report_topics);

		// Now set the flags back
		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET post_reported = 0
			WHERE ' . $db->sql_in_set('post_id', $report_posts);
		$db->sql_query($sql);

		if (sizeof($report_topics))
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_reported = 0
				WHERE ' . $db->sql_in_set('topic_id', $report_topics);
			$db->sql_query($sql);
		}
	}

	// Remove reports
	$db->sql_query('DELETE FROM ' . REPORTS_TABLE . ' WHERE user_id = ' . $user_id);

	switch ($mode)
	{
		case 'retain':

			if ($post_username === false)
			{
				$post_username = $user->lang['GUEST'];
			}

			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET forum_last_poster_id = ' . ANONYMOUS . ", forum_last_poster_name = '" . $db->sql_escape($post_username) . "', forum_last_poster_colour = ''
				WHERE forum_last_poster_id = $user_id";
			$db->sql_query($sql);

			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET poster_id = ' . ANONYMOUS . ", post_username = '" . $db->sql_escape($post_username) . "'
				WHERE poster_id = $user_id";
			$db->sql_query($sql);

			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET post_edit_user = ' . ANONYMOUS . "
				WHERE post_edit_user = $user_id";
			$db->sql_query($sql);

			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_poster = ' . ANONYMOUS . ", topic_first_poster_name = '" . $db->sql_escape($post_username) . "', topic_first_poster_colour = ''
				WHERE topic_poster = $user_id";
			$db->sql_query($sql);

			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_last_poster_id = ' . ANONYMOUS . ", topic_last_poster_name = '" . $db->sql_escape($post_username) . "', topic_last_poster_colour = ''
				WHERE topic_last_poster_id = $user_id";
			$db->sql_query($sql);

			// Since we change every post by this author, we need to count this amount towards the anonymous user

			// Update the post count for the anonymous user
			if ($user_row['user_posts'])
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_posts = user_posts + ' . $user_row['user_posts'] . '
					WHERE user_id = ' . ANONYMOUS;
				$db->sql_query($sql);
			}
		break;

		case 'remove':

			if (!function_exists('delete_posts'))
			{
				include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
			}

			$sql = 'SELECT topic_id, COUNT(post_id) AS total_posts
				FROM ' . POSTS_TABLE . "
				WHERE poster_id = $user_id
				GROUP BY topic_id";
			$result = $db->sql_query($sql);

			$topic_id_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$topic_id_ary[$row['topic_id']] = $row['total_posts'];
			}
			$db->sql_freeresult($result);

			if (sizeof($topic_id_ary))
			{
				$sql = 'SELECT topic_id, topic_replies, topic_replies_real
					FROM ' . TOPICS_TABLE . '
					WHERE ' . $db->sql_in_set('topic_id', array_keys($topic_id_ary));
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
						WHERE ' . $db->sql_in_set('topic_id', $del_topic_ary);
					$db->sql_query($sql);
				}
			}

			// Delete posts, attachments, etc.
			delete_posts('poster_id', $user_id);

		break;
	}

	$table_ary = array(USERS_TABLE, USER_GROUP_TABLE, TOPICS_WATCH_TABLE, FORUMS_WATCH_TABLE, ACL_USERS_TABLE, TOPICS_TRACK_TABLE, TOPICS_POSTED_TABLE, FORUMS_TRACK_TABLE, PROFILE_FIELDS_DATA_TABLE, MODERATOR_CACHE_TABLE);

	foreach ($table_ary as $table)
	{
		$sql = "DELETE FROM $table
			WHERE user_id = $user_id";
		$db->sql_query($sql);
	}

	$cache->destroy('sql', MODERATOR_CACHE_TABLE);

	// Remove any undelivered mails...
	$sql = 'SELECT msg_id, user_id
		FROM ' . PRIVMSGS_TO_TABLE . '
		WHERE author_id = ' . $user_id . '
			AND folder_id = ' . PRIVMSGS_NO_BOX;
	$result = $db->sql_query($sql);

	$undelivered_msg = $undelivered_user = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$undelivered_msg[] = $row['msg_id'];
		$undelivered_user[$row['user_id']][] = true;
	}
	$db->sql_freeresult($result);

	if (sizeof($undelivered_msg))
	{
		$sql = 'DELETE FROM ' . PRIVMSGS_TABLE . '
			WHERE ' . $db->sql_in_set('msg_id', $undelivered_msg);
		$db->sql_query($sql);
	}

	$sql = 'DELETE FROM ' . PRIVMSGS_TO_TABLE . '
		WHERE author_id = ' . $user_id . '
			AND folder_id = ' . PRIVMSGS_NO_BOX;
	$db->sql_query($sql);

	// Delete all to-information
	$sql = 'DELETE FROM ' . PRIVMSGS_TO_TABLE . '
		WHERE user_id = ' . $user_id;
	$db->sql_query($sql);

	// Set the remaining author id to anonymous - this way users are still able to read messages from users being removed
	$sql = 'UPDATE ' . PRIVMSGS_TO_TABLE . '
		SET author_id = ' . ANONYMOUS . '
		WHERE author_id = ' . $user_id;
	$db->sql_query($sql);

	$sql = 'UPDATE ' . PRIVMSGS_TABLE . '
		SET author_id = ' . ANONYMOUS . '
		WHERE author_id = ' . $user_id;
	$db->sql_query($sql);

	foreach ($undelivered_user as $_user_id => $ary)
	{
		if ($_user_id == $user_id)
		{
			continue;
		}

		$sql = 'UPDATE ' . USERS_TABLE . ' 
			SET user_new_privmsg = user_new_privmsg - ' . sizeof($ary) . ',
				user_unread_privmsg = user_unread_privmsg - ' . sizeof($ary) . '
			WHERE user_id = ' . $_user_id;
		$db->sql_query($sql);
	}

	// Reset newest user info if appropriate
	if ($config['newest_user_id'] == $user_id)
	{
		update_last_username();
	}

	// Decrement number of users if this user is active
	if ($user_row['user_type'] != USER_INACTIVE && $user_row['user_type'] != USER_IGNORE)
	{
		set_config('num_users', $config['num_users'] - 1, true);
	}

	$db->sql_transaction('commit');

	return false;
}

/**
* Flips user_type from active to inactive and vice versa, handles group membership updates
* 
* @param string $mode can be flip for flipping from active/inactive, activate or deactivate
*/
function user_active_flip($mode, $user_id_ary, $reason = INACTIVE_MANUAL)
{
	global $config, $db, $user, $auth;

	$deactivated = $activated = 0;
	$sql_statements = array();

	if (!is_array($user_id_ary))
	{
		$user_id_ary = array($user_id_ary);
	}

	if (!sizeof($user_id_ary))
	{
		return;
	}

	$sql = 'SELECT user_id, group_id, user_type, user_inactive_reason
		FROM ' . USERS_TABLE . '
		WHERE ' . $db->sql_in_set('user_id', $user_id_ary);
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$sql_ary = array();

		if ($row['user_type'] == USER_IGNORE || $row['user_type'] == USER_FOUNDER || 
			($mode == 'activate' && $row['user_type'] != USER_INACTIVE) || 
			($mode == 'deactivate' && $row['user_type'] == USER_INACTIVE))
		{
			continue;
		}

		if ($row['user_type'] == USER_INACTIVE)
		{
			$activated++;
		}
		else
		{
			$deactivated++;

			// Remove the users session key...
			$user->reset_login_keys($row['user_id']);
		}

		$sql_ary += array(
			'user_type'				=> ($row['user_type'] == USER_NORMAL) ? USER_INACTIVE : USER_NORMAL,
			'user_inactive_time'	=> ($row['user_type'] == USER_NORMAL) ? time() : 0,
			'user_inactive_reason'	=> ($row['user_type'] == USER_NORMAL) ? $reason : 0,
		);

		$sql_statements[$row['user_id']] = $sql_ary;
	}
	$db->sql_freeresult($result);

	if (sizeof($sql_statements))
	{
		foreach ($sql_statements as $user_id => $sql_ary)
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE user_id = ' . $user_id;
			$db->sql_query($sql);
		}

		$auth->acl_clear_prefetch(array_keys($sql_statements));
	}

	if ($deactivated)
	{
		set_config('num_users', $config['num_users'] - $deactivated, true);
	}

	if ($activated)
	{
		set_config('num_users', $config['num_users'] + $activated, true);
	}

	// Update latest username
	update_last_username();
}

/**
* Add a ban or ban exclusion to the banlist. Bans either a user, an IP or an email address
*
* @param string $mode Type of ban. One of the following: user, ip, email
* @param mixed $ban Banned entity. Either string or array with usernames, ips or email addresses
* @param int $ban_len Ban length in minutes
* @param string $ban_len_other Ban length as a date (YYYY-MM-DD)
* @param boolean $ban_exclude Exclude these entities from banning?
* @param string $ban_reason String describing the reason for this ban
* @return boolean
*/
function user_ban($mode, $ban, $ban_len, $ban_len_other, $ban_exclude, $ban_reason, $ban_give_reason = '')
{
	global $db, $user, $auth;

	// Delete stale bans
	$sql = 'DELETE FROM ' . BANLIST_TABLE . '
		WHERE ban_end < ' . time() . '
			AND ban_end <> 0';
	$db->sql_query($sql);

	$ban_list = (!is_array($ban)) ? array_unique(explode("\n", $ban)) : $ban;
	$ban_list_log = implode(', ', $ban_list);

	$current_time = time();

	// Set $ban_end to the unix time when the ban should end. 0 is a permanent ban.
	if ($ban_len)
	{
		if ($ban_len != -1 || !$ban_len_other)
		{
			$ban_end = max($current_time, $current_time + ($ban_len) * 60);
		}
		else
		{
			$ban_other = explode('-', $ban_len_other);
			if (sizeof($ban_other) == 3 && ((int)$ban_other[0] < 9999) && 
				(strlen($ban_other[0]) == 4) && (strlen($ban_other[1]) == 2) && (strlen($ban_other[2]) == 2))
			{
				$ban_end = max($current_time, gmmktime(0, 0, 0, (int)$ban_other[1], (int)$ban_other[2], (int)$ban_other[0]));
			}
			else
			{
				trigger_error($user->lang['LENGTH_BAN_INVALID']);
			}
		}
	}
	else
	{
		$ban_end = 0;
	}

	$founder = $founder_names = array();

	if (!$ban_exclude)
	{
		// Create a list of founder...
		$sql = 'SELECT user_id, user_email, username_clean
			FROM ' . USERS_TABLE . '
			WHERE user_type = ' . USER_FOUNDER;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$founder[$row['user_id']] = $row['user_email'];
			$founder_names[$row['user_id']] = $row['username_clean'];
		}
		$db->sql_freeresult($result);
	}

	$banlist_ary = array();

	switch ($mode)
	{
		case 'user':
			$type = 'ban_userid';

			if (in_array('*', $ban_list))
			{
				// Ban all users (it's a good thing that you can exclude people)
				$banlist_ary[] = '*';
			}
			else
			{
				// Select the relevant user_ids.
				$sql_usernames = array();

				foreach ($ban_list as $username)
				{
					$username = trim($username);
					if ($username != '')
					{
						$clean_name = utf8_clean_string($username);
						if ($clean_name == $user->data['username_clean'])
						{
							trigger_error($user->lang['CANNOT_BAN_YOURSELF']);
						}
						if (in_array($clean_name, $founder_names))
						{
							trigger_error($user->lang['CANNOT_BAN_FOUNDER']);
						}
						$sql_usernames[] = $clean_name;
					}
				}

				// Make sure we have been given someone to ban
				if (!sizeof($sql_usernames))
				{
					trigger_error($user->lang['NO_USER_SPECIFIED']);
				}

				$sql = 'SELECT user_id
					FROM ' . USERS_TABLE . '
					WHERE ' . $db->sql_in_set('username_clean', $sql_usernames);

				// Do not allow banning yourself
				if (sizeof($founder))
				{
					$sql .= ' AND ' . $db->sql_in_set('user_id', array_merge(array_keys($founder), array($user->data['user_id'])), true);
				}
				else
				{
					$sql .= ' AND user_id <> ' . $user->data['user_id'];
				}

				$result = $db->sql_query($sql);

				if ($row = $db->sql_fetchrow($result))
				{
					do
					{
						$banlist_ary[] = $row['user_id'];
					}
					while ($row = $db->sql_fetchrow($result));
				}
				else
				{
					trigger_error($user->lang['NO_USERS']);
				}
				$db->sql_freeresult($result);
			}
		break;

		case 'ip':
			$type = 'ban_ip';

			foreach ($ban_list as $ban_item)
			{
				if (preg_match('#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})[ ]*\-[ ]*([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#', trim($ban_item), $ip_range_explode))
				{
					// This is an IP range
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

							$banlist_ary[] = "$ip_1_counter.*";
						}

						while ($ip_2_counter <= $ip_2_end)
						{
							$ip_3_counter = ($ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[3] : 0;
							$ip_3_end = ($ip_2_counter < $ip_2_end || $ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[7];

							if ($ip_3_counter == 0 && $ip_3_end == 254)
							{
								$ip_3_counter = 256;
								$ip_3_fragment = 256;

								$banlist_ary[] = "$ip_1_counter.$ip_2_counter.*";
							}

							while ($ip_3_counter <= $ip_3_end)
							{
								$ip_4_counter = ($ip_3_counter == $ip_range_explode[3] && $ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[4] : 0;
								$ip_4_end = ($ip_3_counter < $ip_3_end || $ip_2_counter < $ip_2_end) ? 254 : $ip_range_explode[8];

								if ($ip_4_counter == 0 && $ip_4_end == 254)
								{
									$ip_4_counter = 256;
									$ip_4_fragment = 256;

									$banlist_ary[] = "$ip_1_counter.$ip_2_counter.$ip_3_counter.*";
								}

								while ($ip_4_counter <= $ip_4_end)
								{
									$banlist_ary[] = "$ip_1_counter.$ip_2_counter.$ip_3_counter.$ip_4_counter";
									$ip_4_counter++;
								}
								$ip_3_counter++;
							}
							$ip_2_counter++;
						}
						$ip_1_counter++;
					}
				}
				else if (preg_match('#^([0-9]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})$#', trim($ban_item)) || preg_match('#^[a-f0-9:]+\*?$#i', trim($ban_item)))
				{
					// Normal IP address
					$banlist_ary[] = trim($ban_item);
				}
				else if (preg_match('#^\*$#', trim($ban_item)))
				{
					// Ban all IPs
					$banlist_ary[] = "*";
				}
				else if (preg_match('#^([\w\-_]\.?){2,}$#is', trim($ban_item)))
				{
					// hostname
					$ip_ary = gethostbynamel(trim($ban_item));

					if (!empty($ip_ary))
					{
						foreach ($ip_ary as $ip)
						{
							if ($ip)
							{
								if (strlen($ip) > 40)
								{
									continue;
								}

								$banlist_ary[] = $ip;
							}
						}
					}
				}
				else
				{
					trigger_error('NO_IPS_DEFINED');
				}
			}
		break;

		case 'email':
			$type = 'ban_email';

			foreach ($ban_list as $ban_item)
			{
				$ban_item = trim($ban_item);

				if (preg_match('#^.*?@*|(([a-z0-9\-]+\.)+([a-z]{2,3}))$#i', $ban_item))
				{
					if (strlen($ban_item) > 100)
					{
						continue;
					}

					if (!sizeof($founder) || !in_array($ban_item, $founder))
					{
						$banlist_ary[] = $ban_item;
					}
				}
			}

			if (sizeof($ban_list) == 0)
			{
				trigger_error('NO_EMAILS_DEFINED');
			}
		break;

		default:
			trigger_error('NO_MODE');
		break;
	}

	// Fetch currently set bans of the specified type and exclude state. Prevent duplicate bans.
	$sql_where = ($type == 'ban_userid') ? 'ban_userid <> 0' : "$type <> ''";

	$sql = "SELECT $type
		FROM " . BANLIST_TABLE . "
		WHERE $sql_where
			AND ban_exclude = $ban_exclude";
	$result = $db->sql_query($sql);

	// Reset $sql_where, because we use it later...
	$sql_where = '';

	if ($row = $db->sql_fetchrow($result))
	{
		$banlist_ary_tmp = array();
		do
		{
			switch ($mode)
			{
				case 'user':
					$banlist_ary_tmp[] = $row['ban_userid'];
				break;

				case 'ip':
					$banlist_ary_tmp[] = $row['ban_ip'];
				break;

				case 'email':
					$banlist_ary_tmp[] = $row['ban_email'];
				break;
			}
		}
		while ($row = $db->sql_fetchrow($result));

		$banlist_ary = array_unique(array_diff($banlist_ary, $banlist_ary_tmp));
		unset($banlist_ary_tmp);
	}
	$db->sql_freeresult($result);

	// We have some entities to ban
	if (sizeof($banlist_ary))
	{
		$sql_ary = array();

		foreach ($banlist_ary as $ban_entry)
		{
			$sql_ary[] = array(
				$type				=> $ban_entry,
				'ban_start'			=> $current_time,
				'ban_end'			=> $ban_end,
				'ban_exclude'		=> $ban_exclude,
				'ban_reason'		=> $ban_reason,
				'ban_give_reason'	=> $ban_give_reason,
			);
		}
		
		$db->sql_multi_insert(BANLIST_TABLE, $sql_ary);

		// If we are banning we want to logout anyone matching the ban
		if (!$ban_exclude)
		{
			switch ($mode)
			{
				case 'user':
					$sql_where = (in_array('*', $banlist_ary)) ? '' : 'WHERE ' . $db->sql_in_set('session_user_id', $banlist_ary);
				break;

				case 'ip':
					$sql_where = 'WHERE ' . $db->sql_in_set('session_ip', $banlist_ary);
				break;

				case 'email':
					$banlist_ary_sql = array();

					foreach ($banlist_ary as $ban_entry)
					{
						$banlist_ary_sql[] = (string) str_replace('*', '%', $ban_entry);
					}

					$sql = 'SELECT user_id
						FROM ' . USERS_TABLE . '
						WHERE ' . $db->sql_in_set('user_email', $banlist_ary_sql);
					$result = $db->sql_query($sql);

					$sql_in = array();

					if ($row = $db->sql_fetchrow($result))
					{
						do
						{
							$sql_in[] = $row['user_id'];
						}
						while ($row = $db->sql_fetchrow($result));

						$sql_where = 'WHERE ' . $db->sql_in_set('session_user_id', $sql_in);
					}
					$db->sql_freeresult($result);
				break;
			}

			if (isset($sql_where) && $sql_where)
			{
				$sql = 'DELETE FROM ' . SESSIONS_TABLE . "
					$sql_where";
				$db->sql_query($sql);

				if ($mode == 'user')
				{
					$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . ' ' . ((in_array('*', $banlist_ary)) ? '' : 'WHERE ' . $db->sql_in_set('user_id', $banlist_ary));
					$db->sql_query($sql);
				}
			}
		}

		// Update log
		$log_entry = ($ban_exclude) ? 'LOG_BAN_EXCLUDE_' : 'LOG_BAN_';

		// Add to moderator and admin log
		add_log('admin', $log_entry . strtoupper($mode), $ban_reason, $ban_list_log);
		add_log('mod', 0, 0, $log_entry . strtoupper($mode), $ban_reason, $ban_list_log);

		return true;
	}

	// There was nothing to ban/exclude
	return false;
}

/**
* Unban User
*/
function user_unban($mode, $ban)
{
	global $db, $user, $auth;

	// Delete stale bans
	$sql = 'DELETE FROM ' . BANLIST_TABLE . '
		WHERE ban_end < ' . time() . '
			AND ban_end <> 0';
	$db->sql_query($sql);

	if (!is_array($ban))
	{
		$ban = array($ban);
	}

	$unban_sql = array_map('intval', $ban);

	if (sizeof($unban_sql))
	{
		// Grab details of bans for logging information later
		switch ($mode)
		{
			case 'user':
				$sql = 'SELECT u.username AS unban_info
					FROM ' . USERS_TABLE . ' u, ' . BANLIST_TABLE . ' b
					WHERE ' . $db->sql_in_set('b.ban_id', $unban_sql) . '
						AND u.user_id = b.ban_userid';
			break;

			case 'email':
				$sql = 'SELECT ban_email AS unban_info
					FROM ' . BANLIST_TABLE . '
					WHERE ' . $db->sql_in_set('ban_id', $unban_sql);
			break;

			case 'ip':
				$sql = 'SELECT ban_ip AS unban_info
					FROM ' . BANLIST_TABLE . '
					WHERE ' . $db->sql_in_set('ban_id', $unban_sql);
			break;
		}
		$result = $db->sql_query($sql);

		$l_unban_list = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$l_unban_list .= (($l_unban_list != '') ? ', ' : '') . $row['unban_info'];
		}
		$db->sql_freeresult($result);

		$sql = 'DELETE FROM ' . BANLIST_TABLE . '
			WHERE ' . $db->sql_in_set('ban_id', $unban_sql);
		$db->sql_query($sql);

		// Add to moderator and admin log
		add_log('admin', 'LOG_UNBAN_' . strtoupper($mode), $l_unban_list);
		add_log('mod', 0, 0, 'LOG_UNBAN_' . strtoupper($mode), $l_unban_list);
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
* Data validation ... used primarily but not exclusively by ucp modules
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
*
* @return	boolean|string	Either false if validation succeeded or a string which will be used as the error message (with the variable name appended)
*/
function validate_string($string, $optional = false, $min = 0, $max = 0)
{
	if (empty($string) && $optional)
	{
		return false;
	}

	if ($min && utf8_strlen(htmlspecialchars_decode($string)) < $min)
	{
		return 'TOO_SHORT';
	}
	else if ($max && utf8_strlen(htmlspecialchars_decode($string)) > $max)
	{
		return 'TOO_LONG';
	}

	return false;
}

/**
* Validate Number
*
* @return	boolean|string	Either false if validation succeeded or a string which will be used as the error message (with the variable name appended)
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
*
* @return	boolean|string	Either false if validation succeeded or a string which will be used as the error message (with the variable name appended)
*/
function validate_match($string, $optional = false, $match = '')
{
	if (empty($string) && $optional)
	{
		return false;
	}

	if (empty($match))
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
*
* @param string $username The username to check
* @param string $allowed_username An allowed username, default being $user->data['username']
*
* @return	mixed	Either false if validation succeeded or a string which will be used as the error message (with the variable name appended)
*/
function validate_username($username, $allowed_username = false)
{
	global $config, $db, $user, $cache;

	$clean_username = utf8_clean_string($username);
	$allowed_username = ($allowed_username === false) ? $user->data['username_clean'] : utf8_clean_string($allowed_username);

	if ($allowed_username == $clean_username)
	{
		return false;
	}

	// ... fast checks first.
	if (strpos($username, '&quot;') !== false || strpos($username, '"') !== false)
	{
		return 'INVALID_CHARS';
	}

	$mbstring = $pcre = false;

	// generic UTF-8 character types supported?
	if (version_compare(PHP_VERSION, '5.1.0', '>=') || (version_compare(PHP_VERSION, '5.0.0-dev', '<=') && version_compare(PHP_VERSION, '4.4.0', '>=')))
	{
		$pcre = true;
	}
	else if (function_exists('mb_ereg_match'))
	{
		mb_regex_encoding('UTF-8');
		$mbstring = true;
	}

	switch ($config['allow_name_chars'])
	{
		case 'USERNAME_CHARS_ANY':
			$pcre = true;
			$regex = '.+';
		break;

		case 'USERNAME_ALPHA_ONLY':
			$pcre = true;
			$regex = '[A-Za-z]+';
		break;

		case 'USERNAME_ALPHA_SPACERS':
			$pcre = true;
			$regex = '[A-Za-z-\]_+ ]+';
		break;

		case 'USERNAME_LETTER_NUM':
			if ($pcre)
			{
				$regex = '[\p{Lu}\p{Ll}\p{N}]+';
			}
			else if ($mbstring)
			{
				$regex = '[[:upper:][:lower:][:digit:]]+';
			}
			else
			{
				$pcre = true;
				$regex = '[a-zA-Z0-9]+';
			}
		break;

		case 'USERNAME_LETTER_NUM_SPACERS':
			if ($pcre)
			{
				$regex = '[-\]_+ [\p{Lu}\p{Ll}\p{N}]+';
			}
			else if ($mbstring)
			{
				$regex = '[-\]_+ [[:upper:][:lower:][:digit:]]+';
			}
			else
			{
				$pcre = true;
				$regex = '[-\]_+ [a-zA-Z0-9]+';
			}
		break;

		case 'USERNAME_ASCII':
		default:
			$pcre = true;
			$regex = '[\x01-\x7F]+';
		break;
	}

	if ($pcre)
	{
		if (!preg_match('#^' . $regex . '$#u', $username))
		{
			return 'INVALID_CHARS';
		}
	}
	else if ($mbstring)
	{
		$matches = array();
		mb_ereg_search_init('^' . $username . '$', $regex, $matches);
		if (!mb_ereg_search())
		{
			return 'INVALID_CHARS';
		}
	}

	$sql = 'SELECT username
		FROM ' . USERS_TABLE . "
		WHERE username_clean = '" . $db->sql_escape($clean_username) . "'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if ($row)
	{
		return 'USERNAME_TAKEN';
	}

	$sql = 'SELECT group_name
		FROM ' . GROUPS_TABLE . "
		WHERE LOWER(group_name) = '" . $db->sql_escape(utf8_strtolower($username)) . "'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if ($row)
	{
		return 'USERNAME_TAKEN';
	}

	$bad_usernames = $cache->obtain_disallowed_usernames();

	foreach ($bad_usernames as $bad_username)
	{
		if (preg_match('#^' . $bad_username . '#', $clean_username))
		{
			return 'USERNAME_DISALLOWED';
		}
	}

	$sql = 'SELECT word
		FROM ' . WORDS_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if (preg_match('#(' . str_replace('\*', '.*?', preg_quote($row['word'], '#')) . ')#i', $username))
		{
			$db->sql_freeresult($result);
			return 'USERNAME_DISALLOWED';
		}
	}
	$db->sql_freeresult($result);

	return false;
}

/**
* Check to see if the password meets the complexity settings
*
* @return	boolean|string	Either false if validation succeeded or a string which will be used as the error message (with the variable name appended)
*/
function validate_password($password)
{
	global $config, $db, $user;

	if (!$password)
	{
		return false;
	}

	$pcre = $mbstring = false;

	// generic UTF-8 character types supported?
	if (version_compare(PHP_VERSION, '5.1.0', '>=') || (version_compare(PHP_VERSION, '5.0.0-dev', '<=') && version_compare(PHP_VERSION, '4.4.0', '>=')))
	{
		$upp = '\p{Lu}';
		$low = '\p{Ll}';
		$let = '\p{L}';
		$num = '\p{N}';
		$sym = '[^\p{Lu}\p{Ll}\p{N}]';
		$pcre = true;
	}
	else if (function_exists('mb_ereg_match'))
	{
		mb_regex_encoding('UTF-8');
		$upp = '[[:upper:]]';
		$low = '[[:lower:]]';
		$let = '[[:lower:][:upper:]]';
		$num = '[[:digit:]]';
		$sym = '[^[:upper:][:lower:][:digit:]]';
		$mbstring = true;
	}
	else
	{
		$upp = '[A-Z]';
		$low = '[a-z]';
		$let = '[a-zA-Z]';
		$num = '[0-9]';
		$sym = '[^A-Za-z0-9]';
		$pcre = true;
	}

	$chars = array();

	switch ($config['pass_complex'])
	{
		case 'PASS_TYPE_CASE':
			$chars[] = $low;
			$chars[] = $upp;
		break;

		case 'PASS_TYPE_ALPHA':
			$chars[] = $let;
			$chars[] = $num;
		break;

		case 'PASS_TYPE_SYMBOL':
			$chars[] = $low;
			$chars[] = $upp;
			$chars[] = $num;
			$chars[] = $sym;
		break;
	}

	if ($pcre)
	{
		foreach ($chars as $char)
		{
			if (!preg_match('#' . $char . '#u', $password))
			{
				return 'INVALID_CHARS';
			}
		}
	}
	else if ($mbstring)
	{
		foreach ($chars as $char)
		{
			if (!mb_ereg_match($char, $password))
			{
				return 'INVALID_CHARS';
			}
		}
	}

	return false;
}

/**
* Check to see if email address is banned or already present in the DB
*
* @param string $email The email to check
* @param string $allowed_email An allowed email, default being $user->data['user_email']
*
* @return mixed Either false if validation succeeded or a string which will be used as the error message (with the variable name appended)
*/
function validate_email($email, $allowed_email = false)
{
	global $config, $db, $user;

	$email = strtolower($email);
	$allowed_email = ($allowed_email === false) ? strtolower($user->data['user_email']) : strtolower($allowed_email);

	if ($allowed_email == $email)
	{
		return false;
	}

	if (!preg_match('/^' . get_preg_expression('email') . '$/i', $email))
	{
		return 'EMAIL_INVALID';
	}

	// Check MX record.
	// The idea for this is from reading the UseBB blog/announcement. :)
	if ($config['email_check_mx'])
	{
		list(, $domain) = explode('@', $email);

		if (phpbb_checkdnsrr($domain, 'A') === false && phpbb_checkdnsrr($domain, 'MX') === false)
		{
			return 'DOMAIN_NO_MX_RECORD';
		}
	}

	if ($user->check_ban(false, false, $email, true) == true)
	{
		return 'EMAIL_BANNED';
	}

	if (!$config['allow_emailreuse'])
	{
		$sql = 'SELECT user_email_hash
			FROM ' . USERS_TABLE . "
			WHERE user_email_hash = " . (crc32($email) . strlen($email));
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			return 'EMAIL_TAKEN';
		}
	}

	return false;
}



/**
* Remove avatar
*/
function avatar_delete($mode, $row)
{
	global $phpbb_root_path, $config, $db, $user;

	// Check if the users avatar is actually *not* a group avatar
	if ($mode == 'user')
	{
		if (strpos($row['user_avatar'], 'g') === 0 || (((int)$row['user_avatar'] !== 0) && ((int)$row['user_avatar'] !== (int)$row['user_id'])))
		{
			return false;
		}
	}
	
	$filename = get_avatar_filename($row[$mode . '_avatar']);
	if (file_exists($phpbb_root_path . $config['avatar_path'] . '/' . $filename))
	{
		@unlink($phpbb_root_path . $config['avatar_path'] . '/' . $filename);
		return true;
	}

	return false;
}

/**
* Remote avatar linkage
*/
function avatar_remote($data, &$error)
{
	global $config, $db, $user, $phpbb_root_path, $phpEx;

	if (!preg_match('#^(http|https|ftp)://#i', $data['remotelink']))
	{
		$data['remotelink'] = 'http://' . $data['remotelink'];
	}

	if (!preg_match('#^(http|https|ftp)://(.*?\.)*?[a-z0-9\-]+?\.[a-z]{2,4}:?([0-9]*?).*?\.(gif|jpg|jpeg|png)$#i', $data['remotelink']))
	{
		$error[] = $user->lang['AVATAR_URL_INVALID'];
		return false;
	}

	// Make sure getimagesize works...
	if (($image_data = getimagesize($data['remotelink'])) === false)
	{
		$error[] = $user->lang['UNABLE_GET_IMAGE_SIZE'];
		return false;
	}

	if ($image_data[0] < 2 || $image_data[1] < 2)
	{
		$error[] = $user->lang['AVATAR_NO_SIZE'];
		return false;
	}

	$width = ($data['width'] && $data['height']) ? $data['width'] : $image_data[0];
	$height = ($data['width'] && $data['height']) ? $data['height'] : $image_data[1];

	if ($width < 2 || $height < 2)
	{
		$error[] = $user->lang['AVATAR_NO_SIZE'];
		return false;
	}

	// Check image type
	include_once($phpbb_root_path . 'includes/functions_upload.' . $phpEx);
	$types = fileupload::image_types();
	$extension = strtolower(filespec::get_extension($data['remotelink']));

	if (!isset($types[$image_data[2]]) || !in_array($extension, $types[$image_data[2]]))
	{
		if (!isset($types[$image_data[2]]))
		{
			$error[] = $user->lang['UNABLE_GET_IMAGE_SIZE'];
		}
		else
		{
			$error[] = sprintf($user->lang['IMAGE_FILETYPE_MISMATCH'], $types[$image_data[2]][0], $extension);
		}
		return false;
	}

	if ($config['avatar_max_width'] || $config['avatar_max_height'])
	{
		if ($width > $config['avatar_max_width'] || $height > $config['avatar_max_height'])
		{
			$error[] = sprintf($user->lang['AVATAR_WRONG_SIZE'], $config['avatar_min_width'], $config['avatar_min_height'], $config['avatar_max_width'], $config['avatar_max_height'], $width, $height);
			return false;
		}
	}

	if ($config['avatar_min_width'] || $config['avatar_min_height'])
	{
		if ($width < $config['avatar_min_width'] || $height < $config['avatar_min_height'])
		{
			$error[] = sprintf($user->lang['AVATAR_WRONG_SIZE'], $config['avatar_min_width'], $config['avatar_min_height'], $config['avatar_max_width'], $config['avatar_max_height'], $width, $height);
			return false;
		}
	}

	return array(AVATAR_REMOTE, $data['remotelink'], $width, $height);
}

/**
* Avatar upload using the upload class
*/
function avatar_upload($data, &$error)
{
	global $phpbb_root_path, $config, $db, $user, $phpEx;

	// Init upload class
	include_once($phpbb_root_path . 'includes/functions_upload.' . $phpEx);
	$upload = new fileupload('AVATAR_', array('jpg', 'jpeg', 'gif', 'png'), $config['avatar_filesize'], $config['avatar_min_width'], $config['avatar_min_height'], $config['avatar_max_width'], $config['avatar_max_height']);

	if (!empty($_FILES['uploadfile']['name']))
	{
		$file = $upload->form_upload('uploadfile');
	}
	else
	{
		$file = $upload->remote_upload($data['uploadurl']);
	}
	
	$prefix = $config['avatar_salt'] . '_';
	$file->clean_filename('avatar', $prefix, $data['user_id']);

	$destination = $config['avatar_path'];

	// Adjust destination path (no trailing slash)
	if (substr($destination, -1, 1) == '/' || substr($destination, -1, 1) == '\\')
	{
		$destination = substr($destination, 0, -1);
	}

	$destination = str_replace(array('../', '..\\', './', '.\\'), '', $destination);
	if ($destination && ($destination[0] == '/' || $destination[0] == "\\"))
	{
		$destination = '';
	}

	// Move file and overwrite any existing image
	$file->move_file($destination, true);

	if (sizeof($file->error))
	{
		$file->remove();
		$error = array_merge($error, $file->error);
	}

	return array(AVATAR_UPLOAD, $data['user_id'] . '_' . time() . '.' . $file->get('extension'), $file->get('width'), $file->get('height'));
}

/**
* Generates avatar filename from the database entry
*/
function get_avatar_filename($avatar_entry)
{
	global $config;

	
	if ($avatar_entry[0] === 'g')
	{
		$avatar_group = true;
		$avatar_entry = substr($avatar_entry, 1);
	}
	else
	{
		$avatar_group = false;
	}
	$ext 			= substr(strrchr($avatar_entry, '.'), 1);
	$avatar_entry	= intval($avatar_entry);
	return $config['avatar_salt'] . '_' . (($avatar_group) ? 'g' : '') . $avatar_entry . '.' . $ext;
}

/**
* Avatar Gallery
*/
function avatar_gallery($category, $avatar_select, $items_per_column, $block_var = 'avatar_row')
{
	global $user, $cache, $template;
	global $config, $phpbb_root_path;

	$avatar_list = array();

	$path = $phpbb_root_path . $config['avatar_gallery_path'];

	if (!file_exists($path) || !is_dir($path))
	{
		$avatar_list = array($user->lang['NO_AVATAR_CATEGORY'] => array());
	}
	else
	{
		// Collect images
		$dp = @opendir($path);

		while (($file = readdir($dp)) !== false)
		{
			if ($file[0] != '.' && is_dir("$path/$file"))
			{
				$avatar_row_count = $avatar_col_count = 0;
	
				$dp2 = @opendir("$path/$file");
				while (($sub_file = readdir($dp2)) !== false)
				{
					if (preg_match('#\.(?:gif|png|jpe?g)$#i', $sub_file))
					{
						$avatar_list[$file][$avatar_row_count][$avatar_col_count] = array(
							'file'		=> "$file/$sub_file",
							'filename'	=> $sub_file,
							'name'		=> ucfirst(str_replace('_', ' ', preg_replace('#^(.*)\..*$#', '\1', $sub_file))),
						);

						$avatar_col_count++;
						if ($avatar_col_count == $items_per_column)
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
	}

	if (!sizeof($avatar_list))
	{
		$avatar_list = array($user->lang['NO_AVATAR_CATEGORY'] => array());
	}

	@ksort($avatar_list);

	$category = (!$category) ? key($avatar_list) : $category;
	$avatar_categories = array_keys($avatar_list);

	$s_category_options = '';
	foreach ($avatar_categories as $cat)
	{
		$s_category_options .= '<option value="' . $cat . '"' . (($cat == $category) ? ' selected="selected"' : '') . '>' . $cat . '</option>';
	}

	$template->assign_vars(array(
		'S_AVATARS_ENABLED'		=> true,
		'S_IN_AVATAR_GALLERY'	=> true,
		'S_CAT_OPTIONS'			=> $s_category_options)
	);

	$avatar_list = $avatar_list[$category];

	foreach ($avatar_list as $avatar_row_ary)
	{
		$template->assign_block_vars($block_var, array());

		foreach ($avatar_row_ary as $avatar_col_ary)
		{
			$template->assign_block_vars($block_var . '.avatar_column', array(
				'AVATAR_IMAGE'	=> $phpbb_root_path . $config['avatar_gallery_path'] . '/' . $avatar_col_ary['file'],
				'AVATAR_NAME'	=> $avatar_col_ary['name'],
				'AVATAR_FILE'	=> $avatar_col_ary['filename'])
			);

			$template->assign_block_vars($block_var . '.avatar_option_column', array(
				'AVATAR_IMAGE'	=> $phpbb_root_path . $config['avatar_gallery_path'] . '/' . $avatar_col_ary['file'],
				'S_OPTIONS_AVATAR'	=> $avatar_col_ary['filename'])
			);
		}
	}

	return $avatar_list;
}


/**
* Tries to (re-)establish avatar dimensions
*/
function avatar_get_dimensions($avatar, $avatar_type, &$error, $current_x = 0, $current_y = 0)
{
	global $config, $phpbb_root_path, $user;
	
	switch ($avatar_type)
	{
		case AVATAR_REMOTE :
			break;

		case AVATAR_UPLOAD :
			$avatar = $phpbb_root_path . $config['avatar_path'] . '/' . get_avatar_filename($avatar);
			break;
		
		case AVATAR_GALLERY :
			$avatar = $phpbb_root_path . $config['avatar_gallery_path'] . '/' . $avatar ;
			break;
	}
	// Make sure getimagesize works...
	if (($image_data = @getimagesize($avatar)) === false)
	{
		$error[] = $user->lang['UNABLE_GET_IMAGE_SIZE'];
		return false;
	}

	if ($image_data[0] < 2 || $image_data[1] < 2)
	{
		$error[] = $user->lang['AVATAR_NO_SIZE'];
		return false;
	}
	
	// try to maintain ratio
	if (!(empty($current_x) && empty($current_y)))
	{
		if ($current_x != 0)
		{
			$image_data[1] = (int) floor(($current_x / $image_data[0]) * $image_data[1]);
			$image_data[1] = min($config['avatar_max_height'], $image_data[1]);
			$image_data[1] = max($config['avatar_min_height'], $image_data[1]);
		}
		if ($current_y != 0)
		{
			$image_data[0] = (int) floor(($current_y / $image_data[1]) * $image_data[0]);
			$image_data[0] = min($config['avatar_max_width'], $image_data[1]);
			$image_data[0] = max($config['avatar_min_width'], $image_data[1]);
		}
	}
	return array($image_data[0], $image_data[1]);
}

/**
* Uploading/Changing user avatar
*/
function avatar_process_user(&$error, $custom_userdata = false)
{
	global $config, $phpbb_root_path, $auth, $user, $db;

	$data = array(
		'uploadurl'		=> request_var('uploadurl', ''),
		'remotelink'	=> request_var('remotelink', ''),
		'width'			=> request_var('width', 0),
		'height'		=> request_var('height', 0),
	);

	$error = validate_data($data, array(
		'uploadurl'		=> array('string', true, 5, 255),
		'remotelink'	=> array('string', true, 5, 255),
		'width'			=> array('string', true, 1, 3),
		'height'		=> array('string', true, 1, 3),
	));

	if (sizeof($error))
	{
		return false;
	}

	$sql_ary = array();

	if ($custom_userdata === false)
	{
		$userdata = &$user->data;
	}
	else
	{
		$userdata = &$custom_userdata;
	}

	$data['user_id'] = $userdata['user_id'];
	$change_avatar = ($custom_userdata === false) ? $auth->acl_get('u_chgavatar') : true;
	$avatar_select = basename(request_var('avatar_select', ''));

	// Can we upload?
	$can_upload = ($config['allow_avatar_upload'] && file_exists($phpbb_root_path . $config['avatar_path']) && @is_writable($phpbb_root_path . $config['avatar_path']) && $change_avatar && (@ini_get('file_uploads') || strtolower(@ini_get('file_uploads')) == 'on')) ? true : false;

	if ((!empty($_FILES['uploadfile']['name']) || $data['uploadurl']) && $can_upload)
	{
		list($sql_ary['user_avatar_type'], $sql_ary['user_avatar'], $sql_ary['user_avatar_width'], $sql_ary['user_avatar_height']) = avatar_upload($data, $error);
	}
	else if ($data['remotelink'] && $change_avatar && $config['allow_avatar_remote'])
	{
		list($sql_ary['user_avatar_type'], $sql_ary['user_avatar'], $sql_ary['user_avatar_width'], $sql_ary['user_avatar_height']) = avatar_remote($data, $error);
	}
	else if ($avatar_select && $change_avatar && $config['allow_avatar_local'])
	{
		$category = basename(request_var('category', ''));

		$sql_ary['user_avatar_type'] = AVATAR_GALLERY;
		$sql_ary['user_avatar'] = $avatar_select;

		// check avatar gallery
		if (!is_dir($phpbb_root_path . $config['avatar_gallery_path'] . '/' . $category))
		{
			$sql_ary['user_avatar'] = '';
			$sql_ary['user_avatar_type'] = $sql_ary['user_avatar_width'] = $sql_ary['user_avatar_height'] = 0;
		}
		else
		{
			list($sql_ary['user_avatar_width'], $sql_ary['user_avatar_height']) = getimagesize($phpbb_root_path . $config['avatar_gallery_path'] . '/' . $category . '/' . $sql_ary['user_avatar']);
			$sql_ary['user_avatar'] = $category . '/' . $sql_ary['user_avatar'];
		}
	}
	else if (isset($_POST['delete']) && $change_avatar)
	{
		$sql_ary['user_avatar'] = '';
		$sql_ary['user_avatar_type'] = $sql_ary['user_avatar_width'] = $sql_ary['user_avatar_height'] = 0;
	}
	elseif (!empty($userdata['user_avatar']))
	{
		// Only update the dimensions
		
		if (empty($data['width']) || empty($data['height']))
		{
			if ($dims = avatar_get_dimensions($userdata['user_avatar'], $userdata['user_avatar_type'], $error, $data['width'], $data['height']))
			{
				list($guessed_x, $guessed_y) = $dims;
				if (empty($data['width']))
				{
					$data['width'] = $guessed_x;
				}
				if (empty($data['height']))
				{
					$data['height'] = $guessed_y;
				}
			}
		}
		if (($config['avatar_max_width'] || $config['avatar_max_height']) && 
			(($data['width'] != $userdata['user_avatar_width']) || $data['height'] != $userdata['user_avatar_height']))
		{
			if ($data['width'] > $config['avatar_max_width'] || $data['height'] > $config['avatar_max_height'])
			{
				$error[] = sprintf($user->lang['AVATAR_WRONG_SIZE'], $config['avatar_min_width'], $config['avatar_min_height'], $config['avatar_max_width'], $config['avatar_max_height'], $data['width'], $data['height']);
			}
		}

		if (!sizeof($error))
		{
			if ($config['avatar_min_width'] || $config['avatar_min_height'])
			{
				if ($data['width'] < $config['avatar_min_width'] || $data['height'] < $config['avatar_min_height'])
				{
					$error[] = sprintf($user->lang['AVATAR_WRONG_SIZE'], $config['avatar_min_width'], $config['avatar_min_height'], $config['avatar_max_width'], $config['avatar_max_height'], $data['width'], $data['height']);
				}
			}
		}

		if (!sizeof($error))
		{
			$sql_ary['user_avatar_width'] = $data['width'];
			$sql_ary['user_avatar_height'] = $data['height'];
		}
	}

	if (!sizeof($error))
	{
		// Do we actually have any data to update?
		if (sizeof($sql_ary))
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE user_id = ' . (($custom_userdata === false) ? $user->data['user_id'] : $custom_userdata['user_id']);
			$db->sql_query($sql);

			if (isset($sql_ary['user_avatar']))
			{
				$userdata = ($custom_userdata === false) ? $user->data : $custom_userdata;

				// Delete old avatar if present
				if ($userdata['user_avatar'] && empty($sql_ary['user_avatar']) && $userdata['user_avatar_type'] != AVATAR_GALLERY)
				{
					avatar_delete('user', $userdata);
				}
			}
		}
	}

	return (sizeof($error)) ? false : true;
}

//
// Usergroup functions
//

/**
* Add or edit a group. If we're editing a group we only update user
* parameters such as rank, etc. if they are changed
*/
function group_create(&$group_id, $type, $name, $desc, $group_attributes, $allow_desc_bbcode = false, $allow_desc_urls = false, $allow_desc_smilies = false)
{
	global $phpbb_root_path, $config, $db, $user, $file_upload;

	$error = array();
	$attribute_ary = array(
		'group_colour'			=> 'string',
		'group_rank'			=> 'int',
		'group_avatar'			=> 'string',
		'group_avatar_type'		=> 'int',
		'group_avatar_width'	=> 'int',
		'group_avatar_height'	=> 'int',

		'group_receive_pm'		=> 'int',
		'group_legend'			=> 'int',
		'group_message_limit'	=> 'int',

		'group_founder_manage'	=> 'int',
	);

	// Those are group-only attributes
	$group_only_ary = array('group_receive_pm', 'group_legend', 'group_message_limit', 'group_founder_manage');

	// Check data. Limit group name length.
	if (!utf8_strlen($name) || utf8_strlen($name) > 60)
	{
		$error[] = (!utf8_strlen($name)) ? $user->lang['GROUP_ERR_USERNAME'] : $user->lang['GROUP_ERR_USER_LONG'];
	}
	
	$err = group_validate_groupname($group_id, $name);
	if (!empty($err))
	{
		$error[] = $user->lang[$err];
	}
	 
	if (!in_array($type, array(GROUP_OPEN, GROUP_CLOSED, GROUP_HIDDEN, GROUP_SPECIAL, GROUP_FREE)))
	{
		$error[] = $user->lang['GROUP_ERR_TYPE'];
	}

	if (!sizeof($error))
	{
		$sql_ary = array(
			'group_name'			=> (string) $name,
			'group_desc'			=> (string) $desc,
			'group_desc_uid'		=> '',
			'group_desc_bitfield'	=> '',
			'group_type'			=> (int) $type,
		);

		// Parse description
		if ($desc)
		{
			generate_text_for_storage($sql_ary['group_desc'], $sql_ary['group_desc_uid'], $sql_ary['group_desc_bitfield'], $sql_ary['group_desc_options'], $allow_desc_bbcode, $allow_desc_urls, $allow_desc_smilies);
		}

		if (sizeof($group_attributes))
		{
			foreach ($attribute_ary as $attribute => $_type)
			{
				if (isset($group_attributes[$attribute]))
				{
					settype($group_attributes[$attribute], $_type);
					$sql_ary[$attribute] = $group_attributes[$attribute];
				}
			}
		}

		// Setting the log message before we set the group id (if group gets added)
		$log = ($group_id) ? 'LOG_GROUP_UPDATED' : 'LOG_GROUP_CREATED';

		$query = '';

		if ($group_id)
		{
			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE group_id = $group_id";
			$db->sql_query($sql);

			// Since we may update the name too, we need to do this on other tables too...
			$sql = 'UPDATE ' . MODERATOR_CACHE_TABLE . "
				SET group_name = '" . $db->sql_escape($sql_ary['group_name']) . "'
				WHERE group_id = $group_id";
			$db->sql_query($sql);
		}
		else
		{
			$sql = 'INSERT INTO ' . GROUPS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}

		if (!$group_id)
		{
			$group_id = $db->sql_nextid();
			if (isset($sql_ary['group_avatar_type']) && $sql_ary['group_avatar_type'] == AVATAR_UPLOAD)
			{
				group_correct_avatar($group_id, $sql_ary['group_avatar']);
			}
		}

		// Set user attributes
		$sql_ary = array();
		if (sizeof($group_attributes))
		{
			foreach ($attribute_ary as $attribute => $_type)
			{
				if (isset($group_attributes[$attribute]) && !in_array($attribute, $group_only_ary))
				{
					// If we are about to set an avatar, we will not overwrite user avatars if no group avatar is set...
					if (strpos($attribute, 'group_avatar') === 0 && !$group_attributes[$attribute])
					{
						continue;
					}

					$sql_ary[$attribute] = $group_attributes[$attribute];
				}
			}
		}

		if (sizeof($sql_ary))
		{
			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . '
				WHERE group_id = ' . $group_id;
			$result = $db->sql_query($sql);

			$user_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$user_ary[] = $row['user_id'];
			}
			$db->sql_freeresult($result);

			if (sizeof($user_ary))
			{
				group_set_user_default($group_id, $user_ary, $sql_ary);
			}
		}

		$name = ($type == GROUP_SPECIAL) ? $user->lang['G_' . $name] : $name;
		add_log('admin', $log, $name);

		group_update_listings($group_id);
	}

	return (sizeof($error)) ? $error : false;
}


/**
* Changes a group avatar's filename to conform to the naming scheme
*/
function group_correct_avatar($group_id, $old_entry)
{
	global $config, $db, $phpbb_root_path;

	$group_id		= (int)$group_id;
	$ext 			= substr(strrchr($old_entry, '.'), 1);
	$old_filename 	= get_avatar_filename($old_entry);
	$new_filename 	= $config['avatar_salt'] . "_g$group_id.$ext";
	$new_entry 		= 'g' . $group_id . '_' . substr(time(), -5) . ".$ext";
	
	$avatar_path = $phpbb_root_path . $config['avatar_path'];
	if (@rename($avatar_path . '/'. $old_filename, $avatar_path . '/' . $new_filename))
	{
		$sql = 'UPDATE ' . GROUPS_TABLE . '
			SET group_avatar = \'' . $db->sql_escape($new_entry) . "'
			WHERE group_id = $group_id";
		$db->sql_query($sql);
	}
}

/**
* Group Delete
*/
function group_delete($group_id, $group_name = false)
{
	global $db, $phpbb_root_path, $phpEx;

	if (!$group_name)
	{
		$group_name = get_group_name($group_id);
	}

	$start = 0;

	do
	{
		$user_id_ary = $username_ary = array();

		// Batch query for group members, call group_user_del
		$sql = 'SELECT u.user_id, u.username
			FROM ' . USER_GROUP_TABLE . ' ug, ' . USERS_TABLE . " u
			WHERE ug.group_id = $group_id
				AND u.user_id = ug.user_id";
		$result = $db->sql_query_limit($sql, 200, $start);

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

	// Delete auth entries from the groups table
	$sql = 'DELETE FROM ' . ACL_GROUPS_TABLE . "
		WHERE group_id = $group_id";
	$db->sql_query($sql);

	// Re-cache moderators
	if (!function_exists('cache_moderators'))
	{
		include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
	}

	cache_moderators();

	add_log('admin', 'LOG_GROUP_DELETE', $group_name);

	// Return false - no error
	return false;
}

/**
* Add user(s) to group
*
* @return mixed false if no errors occurred, else the user lang string for the relevant error, for example 'NO_USER'
*/
function group_user_add($group_id, $user_id_ary = false, $username_ary = false, $group_name = false, $default = false, $leader = 0, $pending = 0, $group_attributes = false)
{
	global $db, $auth;

	// We need both username and user_id info
	$result = user_get_id_name($user_id_ary, $username_ary);

	if (!sizeof($user_id_ary) || $result !== false)
	{
		return 'NO_USER';
	}

	// Remove users who are already members of this group
	$sql = 'SELECT user_id, group_leader
		FROM ' . USER_GROUP_TABLE . '
		WHERE ' . $db->sql_in_set('user_id', $user_id_ary) . "
			AND group_id = $group_id";
	$result = $db->sql_query($sql);

	$add_id_ary = $update_id_ary = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$add_id_ary[] = (int) $row['user_id'];

		if ($leader && !$row['group_leader'])
		{
			$update_id_ary[] = (int) $row['user_id'];
		}
	}
	$db->sql_freeresult($result);

	// Do all the users exist in this group?
	$add_id_ary = array_diff($user_id_ary, $add_id_ary);

	// If we have no users
	if (!sizeof($add_id_ary) && !sizeof($update_id_ary))
	{
		return 'GROUP_USERS_EXIST';
	}

	$db->sql_transaction('begin');

	// Insert the new users
	if (sizeof($add_id_ary))
	{
		$sql_ary = array();

		foreach ($add_id_ary as $user_id)
		{
			$sql_ary[] = array(
				'user_id'		=> $user_id,
				'group_id'		=> $group_id,
				'group_leader'	=> $leader,
				'user_pending'	=> $pending,
			);
		}

		$db->sql_multi_insert(USER_GROUP_TABLE, $sql_ary);
	}

	if (sizeof($update_id_ary))
	{
		$sql = 'UPDATE ' . USER_GROUP_TABLE . '
			SET group_leader = 1
			WHERE ' . $db->sql_in_set('user_id', $update_id_ary) . "
				AND group_id = $group_id";
		$db->sql_query($sql);
	}

	if ($default)
	{
		group_set_user_default($group_id, $user_id_ary, $group_attributes);
	}

	$db->sql_transaction('commit');

	// Clear permissions cache of relevant users
	$auth->acl_clear_prefetch($user_id_ary);

	if (!$group_name)
	{
		$group_name = get_group_name($group_id);
	}

	$log = ($leader) ? 'LOG_MODS_ADDED' : 'LOG_USERS_ADDED';

	add_log('admin', $log, $group_name, implode(', ', $username_ary));

	group_update_listings($group_id);

	// Return false - no error
	return false;
}

/**
* Remove a user/s from a given group. When we remove users we update their
* default group_id. We do this by examining which "special" groups they belong
* to. The selection is made based on a reasonable priority system
*
* @return false if no errors occurred, else the user lang string for the relevant error, for example 'NO_USER'
*/
function group_user_del($group_id, $user_id_ary = false, $username_ary = false, $group_name = false)
{
	global $db, $auth;

	$group_order = array('ADMINISTRATORS', 'GLOBAL_MODERATORS', 'REGISTERED_COPPA', 'REGISTERED', 'BOTS', 'GUESTS');

	// We need both username and user_id info
	$result = user_get_id_name($user_id_ary, $username_ary);

	if (!sizeof($user_id_ary) || $result !== false)
	{
		return 'NO_USER';
	}

	$sql = 'SELECT *
		FROM ' . GROUPS_TABLE . '
		WHERE ' . $db->sql_in_set('group_name', $group_order);
	$result = $db->sql_query($sql);

	$group_order_id = $special_group_data = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$group_order_id[$row['group_name']] = $row['group_id'];

		$special_group_data[$row['group_id']] = array(
			'group_colour'			=> $row['group_colour'],
			'group_rank'				=> $row['group_rank'],
		);

		// Only set the group avatar if one is defined...
		if ($row['group_avatar'])
		{
			$special_group_data[$row['group_id']] = array_merge($special_group_data[$row['group_id']], array(
				'group_avatar'			=> $row['group_avatar'],
				'group_avatar_type'		=> $row['group_avatar_type'],
				'group_avatar_width'		=> $row['group_avatar_width'],
				'group_avatar_height'	=> $row['group_avatar_height'])
			);
		}
	}
	$db->sql_freeresult($result);

	// Get users default groups - we only need to reset default group membership if the group from which the user gets removed is set as default
	$sql = 'SELECT user_id, group_id
		FROM ' . USERS_TABLE . '
		WHERE ' . $db->sql_in_set('user_id', $user_id_ary);
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
		WHERE ' . $db->sql_in_set('ug.user_id', $user_id_ary) . "
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
		if (isset($sql_where_ary[$gid]) && sizeof($sql_where_ary[$gid]))
		{
			group_set_user_default($gid, $sql_where_ary[$gid], $special_group_data[$gid]);
		}
	}
	unset($special_group_data);

	$sql = 'DELETE FROM ' . USER_GROUP_TABLE . "
		WHERE group_id = $group_id
			AND " . $db->sql_in_set('user_id', $user_id_ary);
	$db->sql_query($sql);

	// Clear permissions cache of relevant users
	$auth->acl_clear_prefetch($user_id_ary);

	if (!$group_name)
	{
		$group_name = get_group_name($group_id);
	}

	$log = 'LOG_GROUP_REMOVE';

	add_log('admin', $log, $group_name, implode(', ', $username_ary));

	group_update_listings($group_id);

	// Return false - no error
	return false;
}

/**
* This is used to promote (to leader), demote or set as default a member/s
*/
function group_user_attributes($action, $group_id, $user_id_ary = false, $username_ary = false, $group_name = false, $group_attributes = false)
{
	global $db, $auth, $phpbb_root_path, $phpEx, $config;

	// We need both username and user_id info
	$result = user_get_id_name($user_id_ary, $username_ary);

	if (!sizeof($user_id_ary) || $result !== false)
	{
		return false;
	}

	if (!$group_name)
	{
		$group_name = get_group_name($group_id);
	}

	switch ($action)
	{
		case 'demote':
		case 'promote':
			$sql = 'UPDATE ' . USER_GROUP_TABLE . '
				SET group_leader = ' . (($action == 'promote') ? 1 : 0) . "
				WHERE group_id = $group_id
					AND " . $db->sql_in_set('user_id', $user_id_ary);
			$db->sql_query($sql);

			$log = ($action == 'promote') ? 'LOG_GROUP_PROMOTED' : 'LOG_GROUP_DEMOTED';
		break;

		case 'approve':
			// Make sure we only approve those which are pending ;)
			$sql = 'SELECT u.user_id, u.user_email, u.username, u.username_clean, u.user_notify_type, u.user_jabber, u.user_lang
				FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . ' ug
				WHERE ug.group_id = ' . $group_id . '
					AND ug.user_pending = 1
					AND ug.user_id = u.user_id
					AND ' . $db->sql_in_set('ug.user_id', $user_id_ary);
			$result = $db->sql_query($sql);

			$user_id_ary = $email_users = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$user_id_ary[] = $row['user_id'];
				$email_users[] = $row;
			}
			$db->sql_freeresult($result);

			if (!sizeof($user_id_ary))
			{
				return false;
			}

			$sql = 'UPDATE ' . USER_GROUP_TABLE . "
				SET user_pending = 0
				WHERE group_id = $group_id
					AND " . $db->sql_in_set('user_id', $user_id_ary);
			$db->sql_query($sql);

			// Send approved email to users...
			include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
			$messenger = new messenger();

			foreach ($email_users as $row)
			{
				$messenger->template('group_approved', $row['user_lang']);

				$messenger->to($row['user_email'], $row['username']);
				$messenger->im($row['user_jabber'], $row['username']);

				$messenger->assign_vars(array(
					'USERNAME'		=> htmlspecialchars_decode($row['username']),
					'GROUP_NAME'	=> htmlspecialchars_decode($group_name),
					'U_GROUP'		=> generate_board_url() . "/ucp.$phpEx?i=groups&mode=membership")
				);

				$messenger->send($row['user_notify_type']);
			}

			$messenger->save_queue();

			$log = 'LOG_USERS_APPROVED';
		break;

		case 'default':
			group_set_user_default($group_id, $user_id_ary, $group_attributes);
			$log = 'LOG_GROUP_DEFAULTS';
		break;
	}

	// Clear permissions cache of relevant users
	$auth->acl_clear_prefetch($user_id_ary);

	add_log('admin', $log, $group_name, implode(', ', $username_ary));

	group_update_listings($group_id);

	return true;
}

/**
* A small version of validate_username to check for a group name's existence. To be called directly.
*/
function group_validate_groupname($group_id, $group_name)
{
	global $config, $db;

	$group_name =  utf8_clean_string($group_name); 

	if (!empty($group_id))
	{
		$sql = 'SELECT group_name
			FROM ' . GROUPS_TABLE . '
			WHERE group_id = ' . (int) $group_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			return false;
		}

		$allowed_groupname = utf8_clean_string($row['group_name']);

		if ($allowed_groupname == $group_name)
		{
			return false;
		}
	}

	$sql = 'SELECT group_name
		FROM ' . GROUPS_TABLE . "
		WHERE LOWER(group_name) = '" . $db->sql_escape(utf8_strtolower($group_name)) . "'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	
	if ($row)
	{
		return 'GROUP_NAME_TAKEN';
	}

	return false;
}

/**
* Set users default group
*
* @private
*/
function group_set_user_default($group_id, $user_id_ary, $group_attributes = false, $update_listing = false)
{
	global $db;

	if (empty($user_id_ary))
	{
		return;
	}

	$attribute_ary = array(
		'group_colour'			=> 'string',
		'group_rank'			=> 'int',
		'group_avatar'			=> 'string',
		'group_avatar_type'		=> 'int',
		'group_avatar_width'	=> 'int',
		'group_avatar_height'	=> 'int',
	);

	$sql_ary = array(
		'group_id'		=> $group_id
	);

	// Were group attributes passed to the function? If not we need to obtain them
	if ($group_attributes === false)
	{
		$sql = 'SELECT ' . implode(', ', array_keys($attribute_ary)) . '
			FROM ' . GROUPS_TABLE . "
			WHERE group_id = $group_id";
		$result = $db->sql_query($sql);
		$group_attributes = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
	}

	foreach ($attribute_ary as $attribute => $type)
	{
		if (isset($group_attributes[$attribute]))
		{
			// If we are about to set an avatar, we will not overwrite user avatars if no group avatar is set...
			if (strpos($attribute, 'group_avatar') === 0 && !$group_attributes[$attribute])
			{
				continue;
			}

			// Do not update the rank if it is set to "user default"
			if (strpos($attribute, 'group_rank') === 0 && !$group_attributes[$attribute])
			{
				continue;
			}

			settype($group_attributes[$attribute], $type);
			$sql_ary[str_replace('group_', 'user_', $attribute)] = $group_attributes[$attribute];
		}
	}

	// Before we update the user attributes, we will make a list of those having now the group avatar assigned
	if (in_array('user_avatar', array_keys($sql_ary)))
	{
		// Ok, get the original avatar data from users having an uploaded one (we need to remove these from the filesystem)
		$sql = 'SELECT user_id, group_id, user_avatar
			FROM ' . USERS_TABLE . '
			WHERE ' . $db->sql_in_set('user_id', $user_id_ary) . '
				AND user_avatar_type = ' . AVATAR_UPLOAD;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			avatar_delete('user', $row);
		}
		$db->sql_freeresult($result);
	}

	$sql = 'UPDATE ' . USERS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
		WHERE ' . $db->sql_in_set('user_id', $user_id_ary);
	$db->sql_query($sql);

	if (in_array('user_colour', array_keys($sql_ary)))
	{
		// Update any cached colour information for these users
		$sql = 'UPDATE ' . FORUMS_TABLE . " SET forum_last_poster_colour = '" . $db->sql_escape($sql_ary['user_colour']) . "'
			WHERE " . $db->sql_in_set('forum_last_poster_id', $user_id_ary);
		$db->sql_query($sql);

		$sql = 'UPDATE ' . TOPICS_TABLE . " SET topic_first_poster_colour = '" . $db->sql_escape($sql_ary['user_colour']) . "'
			WHERE " . $db->sql_in_set('topic_poster', $user_id_ary);
		$db->sql_query($sql);

		$sql = 'UPDATE ' . TOPICS_TABLE . " SET topic_last_poster_colour = '" . $db->sql_escape($sql_ary['user_colour']) . "'
			WHERE " . $db->sql_in_set('topic_last_poster_id', $user_id_ary);
		$db->sql_query($sql);

		global $config;

		if (in_array($config['newest_user_id'], $user_id_ary))
		{
			set_config('newest_user_colour', $sql_ary['user_colour'], true);
		}
	}

	if ($update_listing)
	{
		group_update_listings($group_id);
	}
}

/**
* Get group name
*/
function get_group_name($group_id)
{
	global $db, $user;

	$sql = 'SELECT group_name, group_type
		FROM ' . GROUPS_TABLE . '
		WHERE group_id = ' . (int) $group_id;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$row)
	{
		return '';
	}

	return ($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name'];
}

/**
* Obtain either the members of a specified group, the groups the specified user is subscribed to
* or checking if a specified user is in a specified group. This function does not return pending memberships.
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

	if ($user_id_ary)
	{
		$user_id_ary = (!is_array($user_id_ary)) ? array($user_id_ary) : $user_id_ary;
	}

	if ($group_id_ary)
	{
		$group_id_ary = (!is_array($group_id_ary)) ? array($group_id_ary) : $group_id_ary;
	}

	$sql = 'SELECT ug.*, u.username, u.username_clean, u.user_email
		FROM ' . USER_GROUP_TABLE . ' ug, ' . USERS_TABLE . ' u
		WHERE ug.user_id = u.user_id
			AND ug.user_pending = 0 AND ';

	if ($group_id_ary)
	{
		$sql .= ' ' . $db->sql_in_set('ug.group_id', $group_id_ary);
	}

	if ($user_id_ary)
	{
		$sql .= ($group_id_ary) ? ' AND ' : ' ';
		$sql .= $db->sql_in_set('ug.user_id', $user_id_ary);
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

/**
* Re-cache moderators and foes if group has a_ or m_ permissions
*/
function group_update_listings($group_id)
{
	global $auth;

	$hold_ary = $auth->acl_group_raw_data($group_id, array('a_', 'm_'));

	if (!sizeof($hold_ary))
	{
		return;
	}

	$mod_permissions = $admin_permissions = false;

	foreach ($hold_ary as $g_id => $forum_ary)
	{
		foreach ($forum_ary as $forum_id => $auth_ary)
		{
			foreach ($auth_ary as $auth_option => $setting)
			{
				if ($mod_permissions && $admin_permissions)
				{
					break 3;
				}

				if ($setting != ACL_YES)
				{
					continue;
				}

				if ($auth_option == 'm_')
				{
					$mod_permissions = true;
				}

				if ($auth_option == 'a_')
				{
					$admin_permissions = true;
				}
			}
		}
	}

	if ($mod_permissions)
	{
		if (!function_exists('cache_moderators'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		}
		cache_moderators();
	}

	if ($mod_permissions || $admin_permissions)
	{
		if (!function_exists('update_foes'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		}
		update_foes(array($group_id));
	}
}

?>