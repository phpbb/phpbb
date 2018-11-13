<?php
/**
* This file is part of the phpBB Forum Software package.
* @package users
* @version $Id$
* @copyright (c) 2008 phpBB Group <https://www.phpbb.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}

/*
* Generate Ranks
*/
function generate_ranks($user_row, $ranks_array)
{
	$user_fields_array = array(
		'user_rank',
		'user_rank2',
		'user_rank3',
		'user_rank4',
		'user_rank5'
	);

	$user_ranks_array = array(
		'rank_01',
		'rank_02',
		'rank_03',
		'rank_04',
		'rank_05',
	);

	$user_ranks = array();

	$is_banned = false;
	$is_guest = false;
	$rank_sw = false;

	for($j = 0; $j < sizeof($user_ranks_array); $j++)
	{
		$user_ranks[$user_ranks_array[$j]] = '';
		$user_ranks[$user_ranks_array[$j] . '_img'] = '';
		$user_ranks[$user_ranks_array[$j] . '_html'] = '';
		$user_ranks[$user_ranks_array[$j] . '_img_html'] = '';
	}

	if ($user_row['user_id'] == ANONYMOUS)
	{
		$is_guest = true;
	}

	if (!$is_guest && !empty($ranks_array['bannedrow']))
	{
		$is_banned = (isset($ranks_array['bannedrow'][$user_row['user_id']])) ? true : false;
	}

	foreach ($ranks_array['ranksrow'] as $rank_key => $rank_data)
	{
		$rank_tmp = $rank_data['rank_title'];
		$rank_img_tmp = ($rank_data['rank_image']) ? '<img src="' . $rank_data['rank_image'] . '" alt="' . $rank_tmp . '" title="' . $rank_tmp . '" />' : '';
		$rank_tmp = (empty($rank_data['rank_show_title']) && !empty($rank_img_tmp)) ? '' : $rank_tmp;
		if (!empty($is_guest))
		{
			if ($rank_data['rank_special'] == '2')
			{
				$user_ranks['rank_01'] = $rank_tmp;
				$user_ranks['rank_01_img'] = $rank_img_tmp;
				$user_ranks['rank_01_html'] = !empty($rank_tmp) ? ($rank_tmp . '<br />') : '';
				$user_ranks['rank_01_img_html'] = !empty($rank_img_tmp) ? ($rank_img_tmp . '<br />') : '';
				break;
			}
		}
		elseif (!empty($is_banned))
		{
			if ($rank_data['rank_special'] == '3')
			{
				$user_ranks['rank_01'] = $rank_tmp;
				$user_ranks['rank_01_img'] = $rank_img_tmp;
				$user_ranks['rank_01_html'] = !empty($rank_tmp) ? ($rank_tmp . '<br />') : '';
				$user_ranks['rank_01_img_html'] = !empty($rank_img_tmp) ? ($rank_img_tmp . '<br />') : '';
				break;
			}
		}
		else
		{
			$day_diff = intval((time() - $user_row['user_regdate']) / 86400);

			for($k = 0; $k < sizeof($user_fields_array); $k++)
			{
				switch ($rank_data['rank_special'])
				{
					case '1':
						if ($user_row[$user_fields_array[$k]] == $rank_data['rank_id'])
						{
							$rank_sw = true;
						}
						break;
					case '0':
						if (($user_row[$user_fields_array[$k]] == '0') && ($user_row['user_posts'] >= $rank_data['rank_min']))
						{
							$rank_sw = true;
						}
						break;
					case '-1':
						if (($user_row[$user_fields_array[$k]] == '-1') && ($day_diff >= $rank_data['rank_min']))
						{
							$rank_sw = true;
						}
						break;
					default:
						break;
				}

				if (!empty($rank_sw))
				{
					$user_ranks[$user_ranks_array[$k]] = $rank_tmp;
					$user_ranks[$user_ranks_array[$k] . '_img'] = $rank_img_tmp;
					$user_ranks[$user_ranks_array[$k] . '_html'] = !empty($rank_tmp) ? ($rank_tmp . '<br />') : '';
					$user_ranks[$user_ranks_array[$k] . '_img_html'] = !empty($rank_img_tmp) ? ($rank_img_tmp . '<br />') : '';
					$rank_sw = false;
				}
			}

		}
	}

	return $user_ranks;
}
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

	if (${$which_ary} && !is_array(${$which_ary}))
	{
		${$which_ary} = array(${$which_ary});
	}

	$sql_in = ($which_ary == 'user_id_ary') ? array_map('intval', ${$which_ary}) : array_map('utf8_clean_string', ${$which_ary});
	unset(${$which_ary});

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
	global $board_config, $config, $db;

	// Get latest username
	$sql = 'SELECT user_id, username, user_colour
		FROM ' . USERS_TABLE . '
		WHERE user_type IN (' . USER . ', ' . ADMIN . ', ' . MOD . ', ' . GLOBAL_MOD . ')
		ORDER BY user_id DESC';
	$result = $db->sql_query_limit($sql, 1);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if ($row)
	{
		$config->set('newest_user_id', $row['user_id'], false);
		$config->set('newest_username', $row['username'], false);
		$config->set('newest_user_colour', $row['user_colour'], false);
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
	global $board_config, $db, $cache;

	$update_ary = array(
		FORUMS_TABLE			=> array(
			'forum_last_poster_id'	=> 'forum_last_poster_name',
		),
		MODERATOR_CACHE_TABLE	=> array(
			'user_id'	=> 'username',
		),
		POSTS_TABLE				=> array(
			'poster_id'	=> 'post_username',
		),
		TOPICS_TABLE			=> array(
			'topic_poster'			=> 'topic_first_poster_name',
			'topic_last_poster_id'	=> 'topic_last_poster_name',
		),
	);

	foreach ($update_ary as $table => $field_ary)
	{
		foreach ($field_ary as $id_field => $name_field)
		{
			$sql = "UPDATE $table
				SET $name_field = '" . $db->sql_escape($new_name) . "'
				WHERE $name_field = '" . $db->sql_escape($old_name) . "'
					AND $id_field <> " . ANONYMOUS;
			$db->sql_query($sql);
		}
	}

	if ($board_config['newest_username'] == $old_name)
	{
		//$config->set('newest_username', $new_name, false);
		set_config('newest_username', $new_name, true);
	}

	// Because some tables/caches use username-specific data we need to purge this here.
	//$cache->destroy('sql', MODERATOR_CACHE_TABLE);
}



/**
* Adds an user
*
* @param mixed $user_row An array containing the following keys (and the appropriate values): username, group_id (the group to place the user in), user_email and the user_type(usually 0). Additional entries not overridden by defaults will be forwarded.
* @param array $cp_data custom profile fields, see custom_profile::build_insert_sql_array
* @param array $notifications_data The notifications settings for the new user
* @return the new user's ID.
*/
function user_add($user_row, $cp_data = false, $notifications_data = null)
{
	global $db, $board_config;
	global $cache, $config;
	
	$cache = new cache();
	
	if (empty($user_row['username']) || !isset($user_row['group_id']) || !isset($user_row['user_email']) || !isset($user_row['user_type']))
	{
		return false;
	}
	
	$username_clean = utf8_clean_string($user_row['username']);
	
	if (empty($username_clean))
	{
		return false;
	}
	
	// newest user
	//$sql_active_users $sql_active_users = ' AND user_active = 1 ';
	$sql = "SELECT user_id, username
		FROM " . USERS_TABLE . "
		WHERE user_id <> " . ANONYMOUS . "
		
		ORDER BY user_id DESC
		LIMIT 1";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	$newest_user_id = (int) $row['user_id'];

	if ($board_config['last_user_id'] != $newest_user_id)
	{
		set_config('last_user_id', $newest_user_id);
		$cache->destroy('newest_user');
		$newest_user = $cache->obtain_newest_user();
	}
	
	$sql_ary = array(
		'user_id'				=> ($newest_user_id +1),
		'username'			=> $user_row['username'],
		'username_clean'	=> $username_clean,
		'user_password'		=> (isset($user_row['user_password'])) ? $user_row['user_password'] : '',
		'user_email'		=> strtolower($user_row['user_email']),
		'user_email_hash'	=> phpbb_email_hash($user_row['user_email']),
		'group_id'			=> $user_row['group_id'],
		'user_type'			=> $user_row['user_type'],
	);

	// These are the additional vars able to be specified
	$additional_vars = array(
		'user_permissions'	=> '',
		'user_timezone'		=> $board_config['board_timezone'],
		'user_dateformat'	=> $board_config['default_dateformat'],
		'user_lang'			=> $board_config['default_lang'],
		'user_style'		=> (int) $board_config['default_style'],
		'user_actkey'		=> '',
		'user_ip'			=> $user_row['user_ip'],
		'user_regdate'		=> time(),
		'user_passchg'		=> time(),
		'user_options'		=> 230271,
		// We do not set the new flag here - registration scripts need to specify it
		'user_new'			=> 0,

		'user_inactive_reason'	=> 0,
		'user_inactive_time'	=> 0,
		'user_lastmark'			=> time(),
		'user_lastvisit'		=> 0,
		//'user_lastpost_time'	=> 0,
		'user_lastpage'			=> '',
		'user_posts'			=> 0,
		'user_colour'			=> '',
		'user_avatar'			=> '',
		'user_avatar_type'		=> AVATAR_UPLOAD,
		'user_avatar_width'		=> 0,
		'user_avatar_height'	=> 0,
		'user_new_privmsg'		=> 0,
		'user_unread_privmsg'	=> 0,
		'user_last_privmsg'		=> 0,
		//'user_message_rules'	=> 0,
		//'user_full_folder'		=> PRIVMSGS_NO_BOX,
		'user_emailtime'		=> 0,

		'user_notify'			=> 0,
		'user_notify_pm'		=> 1,
		//'user_notify_type'		=> NOTIFY_EMAIL,
		'user_allow_pm'			=> 1,
		'user_allow_viewonline'	=> 1,
		'user_allow_viewemail'	=> 1,
		'user_allow_massemail'	=> 1,

		'user_sig'					=> '',
		'user_sig_bbcode_uid'		=> '',
		'user_sig_bbcode_bitfield'	=> '1111111111111',

		'user_form_salt'			=> unique_id(),
	);

	// Now fill the sql array with not required variables
	foreach ($additional_vars as $key => $default_value)
	{
		$sql_ary[$key] = (isset($user_row[$key])) ? $user_row[$key] : $default_value;
	}

	// Any additional variables in $user_row not covered above?
	$remaining_vars = array_diff(array_keys($user_row), array_keys($sql_ary));

	// Now fill our sql array with the remaining vars
	if (count($remaining_vars))
	{
		foreach ($remaining_vars as $key)
		{
			$sql_ary[$key] = $user_row[$key];
		}
	}

	$sql = 'INSERT INTO ' . USERS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(CRITICAL_ERROR, 'Could not update user info', '<br /><br />SQL Error : ' . $db->sql_error('')['code'] . ' ' . $db->sql_error('')['message'] . ' <br />' . $sql, __LINE__, __FILE__, $sql);
	}

	$user_id = $db->sql_nextid();

	// Insert Custom Profile Fields
	if ($cp_data !== false && count($cp_data))
	{
		$cp_data['user_id'] = (int) $user_id;

		/* @var $cp \phpbb\profilefields\manager */
		$cp = $phpbb_container->get('profilefields.manager');
		$sql = 'INSERT INTO ' . PROFILE_FIELDS_DATA_TABLE . ' ' .
		$db->sql_build_array('INSERT', $cp->build_insert_sql_array($cp_data));
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(CRITICAL_ERROR, 'Could not update user info', '', __LINE__, __FILE__, $sql);
		}
	}

	// Place into appropriate group...
	$sql = 'INSERT INTO ' . USER_GROUP_TABLE . ' ' . $db->sql_build_array('INSERT', array(
		'user_id'		=> (int) $user_id,
		'group_id'		=> (int) $user_row['group_id'],
		'user_pending'	=> 0)
	);
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(CRITICAL_ERROR, 'Could not update user info', '', __LINE__, __FILE__, $sql);
	}

	// Now make it the users default group...
	group_set_user_default($user_row['group_id'], array($user_id), false);

	// Add to newly registered users group if user_new is 1
	if ($board_config['new_member_post_limit'] && $sql_ary['user_new'])
	{
		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . "
			WHERE group_name = 'NEWLY_REGISTERED'
				AND group_type = " . GROUP_SPECIAL;
		if ( !($result = $db->sql_query($sql)) )
		{
				message_die(CRITICAL_ERROR, 'Could not update user info');
		}
		$add_group_id = (int) $db->sql_fetchfield('group_id');
		$db->sql_freeresult($result);

		if ($add_group_id)
		{
			global $phpbb_log;

			// Because these actions only fill the log unnecessarily, we disable it
			$phpbb_log->disable('admin');

			// Add user to "newly registered users" group and set to default group if admin specified so.
			if ($board_config['new_member_group_default'])
			{
				group_user_add($add_group_id, $user_id, false, false, true);
				$user_row['group_id'] = $add_group_id;
			}
			else
			{
				group_user_add($add_group_id, $user_id);
			}

			$phpbb_log->enable('admin');
		}
	}

	// set the newest user and adjust the user count if the user is a normal user and no activation mail is sent
	if ($user_row['user_type'] == USER_NORMAL || $user_row['user_type'] == USER_FOUNDER)
	{
		$config->set('newest_user_id', $user_id, false);
		$config->set('newest_username', $user_row['username'], false);
		$config->increment('num_users', 1, false);

		$sql = 'SELECT group_colour
			FROM ' . GROUPS_TABLE . '
			WHERE group_id = ' . (int) $user_row['group_id'];
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$config->set('newest_user_colour', $row['group_colour'], false);
	}
	
	return $user_id;
}

/**
* Adds an username_clean
*
* @param mixed $user_row An array containing the following keys (and the appropriate values): username, group_id (the group to place the user in), user_email and the user_type(usually 0). Additional entries not overridden by defaults will be forwarded.
* @param array $cp_data custom profile fields, see custom_profile::build_insert_sql_array
* @param array $notifications_data The notifications settings for the new user
* @return the new user's ID.
*/
function username_clean_add($user_row = array(), $cp_data = false, $notifications_data = null)
{
	global $db;
	global $cache, $board_config;
	
	$cache = is_object($cache) ? $cache : new cache(false, defined('DEBUG')); //In sessions
	
	print('<p><span style="color: red;"></span></p><i><p>Refreshing the users table!</p></i>');
	
	/* uncomet to remove and add colomn * /
	//$db_tools = $phpbb_container->get('dbal.tools');
	// Setup $this->db_tools
	if (!class_exists('phpbb_db_tools') && !class_exists('tools'))
	{
		include_once($phpbb_root_path . 'includes/db/tools.' . $phpEx);
	}
	if (class_exists('phpbb_db_tools'))
	{
		$this->db_tools = new phpbb_db_tools($this->db);
	}
	elseif (class_exists('tools'))
	{
		$this->db_tools = new tools($this->db);
	}
	$db_tools->sql_column_remove(USERS_TABLE, 'username_clean', false);
	$db_tools->sql_column_add(USERS_TABLE, 'username_clean', array('column_type_sql' => 'varchar(255)', 'null' => 'NOT NULL', 'default' => '', 'after' => 'username'), false);
	/* */
	
	$serch_field = '';
	
	$sql = "SELECT user_id, username
		FROM " . USERS_TABLE . " 
		ORDER BY user_id ASC";
	$result = $db->sql_query($sql);

	$echos = 0;

	$user_row = $user_row ? $user_row : $db->sql_fetchrowset($result);
	$newest_user_id = count($user_row);
	print('<p><span style="color: red;"></span></p><i><p>Users '. $newest_user_id .'</p></i>');
	
	for($i = 0; $i < $newest_user_id; $i++)
	{
		$username_clean = utf8_clean_string($user_row[$i]['username']);
		$user_id = (int) $user_row[$i]['user_id'];
		
		if (empty($username_clean))
		{
			print('<p><span style="color: red;"></span></p><i><p>Username Empty</p></i>');
		}
		
		print('<p><span style="color: red;"></span></p><i><p>Refresh for '. $username_clean .'</p></i>');
		
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET username_clean = '" . $db->sql_escape($username_clean) . "'
			WHERE user_id = " . $user_id;
		$db->sql_query($sql);

		if ($echos > 200)
		{
			echo '<br />' . "\n";
			$echos = 0;
		}

		echo '.';
		$echos++;

		flush();
	}
	
	/* * /
	if ($board_config['last_user_id'] != $newest_user_id)
	{
		set_config('last_user_id', $newest_user_id);
		$cache->destroy('newest_user');
		$newest_user = $cache->obtain_newest_user();
	}
	/**/
	$db->sql_freeresult($result);
	
	print('<p><span style="color: red;"></span></p><i><p>Refresh FINISHED!</p></i>');
}

/**
 * Remove User
 *
 * @param string	$mode		Either 'retain' or 'remove'
 * @param mixed		$user_ids	Either an array of integers or an integer
 * @param bool		$retain_username
 * @return bool
 */
function user_delete($mode, $user_ids, $retain_username = true)
{
	global $cache, $board_config, $db, $user, $phpbb_dispatcher, $phpbb_container;
	global $phpbb_root_path, $phpEx;

	$db->sql_transaction('begin');

	$user_rows = array();
	if (!is_array($user_ids))
	{
		$user_ids = array($user_ids);
	}

	$user_id_sql = $db->sql_in_set('user_id', $user_ids);

	$sql = 'SELECT *
		FROM ' . USERS_TABLE . '
		WHERE ' . $user_id_sql;
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(CRITICAL_ERROR, 'Could not update user info');
	}
	while ($row = $db->sql_fetchrow($result))
	{
		$user_rows[(int) $row['user_id']] = $row;
	}
	$db->sql_freeresult($result);

	if (empty($user_rows))
	{
		return false;
	}
	
	// Before we begin, we will remove the reports the user issued.
	$sql = 'SELECT r.post_id, p.topic_id
		FROM ' . REPORTS_TABLE . ' r, ' . POSTS_TABLE . ' p
		WHERE ' . $db->sql_in_set('r.user_id', $user_ids) . '
			AND p.post_id = r.post_id';
	$result = $db->sql_query($sql);

	$report_posts = $report_topics = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$report_posts[] = $row['post_id'];
		$report_topics[] = $row['topic_id'];
	}
	$db->sql_freeresult($result);

	if (count($report_posts))
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

		if (count($keep_report_topics))
		{
			$report_topics = array_diff($report_topics, $keep_report_topics);
		}
		unset($keep_report_topics);

		// Now set the flags back
		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET post_reported = 0
			WHERE ' . $db->sql_in_set('post_id', $report_posts);
		$db->sql_query($sql);

		if (count($report_topics))
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_reported = 0
				WHERE ' . $db->sql_in_set('topic_id', $report_topics);
			if ( !($result = $db->sql_query($sql)) )
			{
					message_die(CRITICAL_ERROR, 'Could not update topic info');
			}
		}
	}

	// Remove reports
	$db->sql_query('DELETE FROM ' . REPORTS_TABLE . ' WHERE ' . $user_id_sql);

	$num_users_delta = 0;

	// Get auth provider collection in case accounts might need to be unlinked
	$provider_collection = $phpbb_container->get('auth.provider_collection');

	// Some things need to be done in the loop (if the query changes based
	// on which user is currently being deleted)
	$added_guest_posts = 0;
	foreach ($user_rows as $user_id => $user_row)
	{
		if ($user_row['user_avatar'] && $user_row['user_avatar_type'] == 'avatar.driver.upload')
		{
			avatar_delete('user', $user_row);
		}

		// Unlink accounts
		foreach ($provider_collection as $provider_name => $auth_provider)
		{
			$provider_data = $auth_provider->get_auth_link_data($user_id);

			if ($provider_data !== null)
			{
				$link_data = array(
					'user_id' => $user_id,
					'link_method' => 'user_delete',
				);

				// BLOCK_VARS might contain hidden fields necessary for unlinking accounts
				if (isset($provider_data['BLOCK_VARS']) && is_array($provider_data['BLOCK_VARS']))
				{
					foreach ($provider_data['BLOCK_VARS'] as $provider_service)
					{
						if (!array_key_exists('HIDDEN_FIELDS', $provider_service))
						{
							$provider_service['HIDDEN_FIELDS'] = array();
						}

						$auth_provider->unlink_account(array_merge($link_data, $provider_service['HIDDEN_FIELDS']));
					}
				}
				else
				{
					$auth_provider->unlink_account($link_data);
				}
			}
		}

		// Decrement number of users if this user is active
		if ($user_row['user_type'] != USER_INACTIVE && $user_row['user_type'] != USER_IGNORE)
		{
			--$num_users_delta;
		}

		switch ($mode)
		{
			case 'retain':
				if ($retain_username === false)
				{
					$post_username = $user->lang['GUEST'];
				}
				else
				{
					$post_username = $user_row['username'];
				}

				// If the user is inactive and newly registered
				// we assume no posts from the user, and save
				// the queries
				if ($user_row['user_type'] != USER_INACTIVE || $user_row['user_inactive_reason'] != INACTIVE_REGISTER || $user_row['user_posts'])
				{
					// When we delete these users and retain the posts, we must assign all the data to the guest user
					$sql = 'UPDATE ' . FORUMS_TABLE . '
						SET forum_last_poster_id = ' . ANONYMOUS . ", forum_last_poster_name = '" . $db->sql_escape($post_username) . "', forum_last_poster_colour = ''
						WHERE forum_last_poster_id = $user_id";
					$db->sql_query($sql);

					$sql = 'UPDATE ' . POSTS_TABLE . '
						SET poster_id = ' . ANONYMOUS . ", post_username = '" . $db->sql_escape($post_username) . "'
						WHERE poster_id = $user_id";
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

					if ($user_row['user_posts'])
					{
						$added_guest_posts += $user_row['user_posts'];
					}
				}
			break;

			case 'remove':
				// there is nothing variant specific to deleting posts
			break;
		}
	}

	if ($num_users_delta != 0)
	{
		$config->increment('num_users', $num_users_delta, false);
	}

	// Now do the invariant tasks
	// all queries performed in one call of this function are in a single transaction
	// so this is kosher
	if ($mode == 'retain')
	{
		// Assign more data to the Anonymous user
		$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
			SET poster_id = ' . ANONYMOUS . '
			WHERE ' . $db->sql_in_set('poster_id', $user_ids);
		$db->sql_query($sql);

		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_posts = user_posts + ' . $added_guest_posts . '
			WHERE user_id = ' . ANONYMOUS;
		$db->sql_query($sql);
	}
	else if ($mode == 'remove')
	{
		if (!function_exists('delete_posts'))
		{
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		}

		// Delete posts, attachments, etc.
		// delete_posts can handle any number of IDs in its second argument
		delete_posts('poster_id', $user_ids);
	}

	$table_ary = array(USERS_TABLE, USER_GROUP_TABLE, TOPICS_WATCH_TABLE, FORUMS_WATCH_TABLE, ACL_USERS_TABLE, TOPICS_TRACK_TABLE, TOPICS_POSTED_TABLE, FORUMS_TRACK_TABLE, PROFILE_FIELDS_DATA_TABLE, MODERATOR_CACHE_TABLE, DRAFTS_TABLE, BOOKMARKS_TABLE, SESSIONS_KEYS_TABLE, PRIVMSGS_FOLDER_TABLE, PRIVMSGS_RULES_TABLE);

	// Delete the miscellaneous (non-post) data for the user
	foreach ($table_ary as $table)
	{
		$sql = "DELETE FROM $table
			WHERE " . $user_id_sql;
		$db->sql_query($sql);
	}

	$cache->destroy('sql', MODERATOR_CACHE_TABLE);

	// Change user_id to anonymous for posts edited by this user
	$sql = 'UPDATE ' . POSTS_TABLE . '
		SET post_edit_user = ' . ANONYMOUS . '
		WHERE ' . $db->sql_in_set('post_edit_user', $user_ids);
	$db->sql_query($sql);

	// Change user_id to anonymous for pms edited by this user
	$sql = 'UPDATE ' . PRIVMSGS_TABLE . '
		SET message_edit_user = ' . ANONYMOUS . '
		WHERE ' . $db->sql_in_set('message_edit_user', $user_ids);
	$db->sql_query($sql);

	// Change user_id to anonymous for posts deleted by this user
	$sql = 'UPDATE ' . POSTS_TABLE . '
		SET post_delete_user = ' . ANONYMOUS . '
		WHERE ' . $db->sql_in_set('post_delete_user', $user_ids);
	$db->sql_query($sql);

	// Change user_id to anonymous for topics deleted by this user
	$sql = 'UPDATE ' . TOPICS_TABLE . '
		SET topic_delete_user = ' . ANONYMOUS . '
		WHERE ' . $db->sql_in_set('topic_delete_user', $user_ids);
	$db->sql_query($sql);

	// Delete user log entries about this user
	$sql = 'DELETE FROM ' . LOG_TABLE . '
		WHERE ' . $db->sql_in_set('reportee_id', $user_ids);
	$db->sql_query($sql);

	// Change user_id to anonymous for this users triggered events
	$sql = 'UPDATE ' . LOG_TABLE . '
		SET user_id = ' . ANONYMOUS . '
		WHERE ' . $user_id_sql;
	$db->sql_query($sql);

	// Delete the user_id from the zebra table
	$sql = 'DELETE FROM ' . ZEBRA_TABLE . '
		WHERE ' . $user_id_sql . '
			OR ' . $db->sql_in_set('zebra_id', $user_ids);
	$db->sql_query($sql);

	// Delete the user_id from the banlist
	$sql = 'DELETE FROM ' . BANLIST_TABLE . '
		WHERE ' . $db->sql_in_set('ban_userid', $user_ids);
	$db->sql_query($sql);

	// Delete the user_id from the session table
	$sql = 'DELETE FROM ' . SESSIONS_TABLE . '
		WHERE ' . $db->sql_in_set('session_user_id', $user_ids);
	$db->sql_query($sql);

	// Clean the private messages tables from the user
	if (!function_exists('phpbb_delete_user_pms'))
	{
		include($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
	}
	phpbb_delete_users_pms($user_ids);

	$phpbb_notifications = $phpbb_container->get('notification_manager');
	$phpbb_notifications->delete_notifications('notification.type.admin_activate_user', $user_ids);

	$db->sql_transaction('commit');

	// Reset newest user info if appropriate
	if (in_array($board_config['newest_user_id'], $user_ids))
	{
		update_last_username();
	}

	return false;
}

/*
* Fake User Profile
*/
function user_profile_mask(&$user_data)
{
	global $board_config, $lang;

	$user_data['user_id'] = ANONYMOUS;
	$user_data['username'] = $lang['INACTIVE_USER'];
	$user_data['user_first_name'] = '';
	$user_data['user_last_name'] = '';
	$user_data['post_username'] = $user_data['username'];
	$user_data['user_color'] = '';
	$user_data['user_level'] = USER;
	$user_data['user_regdate'] = $board_config['board_startdate'];
	$user_data['user_from'] = '';
	$user_data['user_from_flag'] = '';
	$user_data['user_birthday'] = 999999;
	$user_data['user_posts'] = 0;
	$user_data['user_personal_pics_count'] = 0;
	$user_data['user_avatar'] = '';
	$user_data['user_avatar_type'] = 0;
	$user_data['user_allowavatar'] = 0;
	$user_data['user_lang'] = $board_config['default_lang'];
	$user_data['user_style'] = $board_config['default_style'];
	$user_data['user_rank'] = '-2';
	$user_data['user_rank_2'] = '-2';
	$user_data['user_rank_3'] = '-2';
	$user_data['user_rank_4'] = '-2';
	$user_data['user_rank_5'] = '-2';
	$user_data['user_allow_viewemail'] = 0;
	$user_data['user_website'] = '';
	$user_data['user_gender'] = 0;
	$user_data['user_allow_viewonline'] = 0;
	$user_data['user_session_time'] = 0;
	$user_data['poster_ip'] = '';
	$user_data['user_warnings'] = 0;
	$user_data['user_sig'] = '';

	$user_sn_im_array = get_user_sn_im_array();
	foreach ($user_sn_im_array as $k => $v)
	{
		$user_data[$v['field']] = '';
	}

	return true;
}

/**
* Create the sql needed to query the color... this is used also to precisely locate the cache file!
*/
function user_color_sql($user_id)
{
	$sql = "SELECT u.username, u.user_active, u.user_mask, u.user_color, u.group_id
		FROM " . USERS_TABLE . " u
		WHERE u.user_id = '" . $user_id . "'
			LIMIT 1";
	return $sql;
}

/**
* Create a profile link for the user with his own color
*/
function colorize_username($user_id, $username = '', $user_color = '', $user_active = true, $no_profile = false, $get_only_color_style = false, $from_db = false, $force_cache = false, $alt_link_url = '')
{
	global $db, $config, $lang;

	$user_id = empty($user_id) ? ANONYMOUS : $user_id;
	$is_guest = ($user_id == ANONYMOUS) ? true : false;

	if ((!$is_guest && $from_db) || (!$is_guest && empty($username) && empty($user_color)))
	{
		// Get the user info and see if they are assigned a color_group
		$sql = user_color_sql($user_id);
		$cache_cleared = (CACHE_COLORIZE && defined('IN_ADMIN')) ? clear_user_color_cache($user_id) : false;
		$result = ((CACHE_COLORIZE || $force_cache) && !defined('IN_ADMIN')) ? $db->sql_query($sql, 0, POST_USERS_URL . '_', USERS_CACHE_FOLDER) : $db->sql_query($sql);
		$sql_row = array();
		$row = array();
		while ($sql_row = $db->sql_fetchrow($result))
		{
			$row = $sql_row;
		}
		$db->sql_freeresult($result);
		$user_mask = (empty($row['user_active']) && !empty($row['user_mask'])) ? true : false;
		if (!empty($user_mask))
		{
			global $user;
			$user_mask = ($user->data['user_level'] == ADMIN) ? false : true;
		}
		$user_id = $user_mask ? ANONYMOUS : $user_id;
		$username = $user_mask ? $lang['INACTIVE_USER'] : $row['username'];
		$user_color = $user_mask ? '' : $row['user_color'];
		$user_active = $row['user_active'];
	}

	$username = (($user_id == ANONYMOUS) || empty($username)) ? $lang['Guest'] : str_replace('&amp;amp;', '&amp;', htmlspecialchars($username));
	$user_link_url = !empty($alt_link_url) ? str_replace('$USER_ID', $user_id, $alt_link_url) : ((defined('USER_LINK_URL_OVERRIDE')) ? str_replace('$USER_ID', $user_id, USER_LINK_URL_OVERRIDE) : (CMS_PAGE_PROFILE . '?mode=viewprofile&amp;' . POST_USERS_URL . '=' . $user_id));
	$user_link_style = '';
	$user_link_begin = '<a href="' . append_sid(IP_ROOT_PATH . $user_link_url) . '"';
	$user_link_end = '>' . $username . '</a>';

	if (!$user_active || $is_guest)
	{
		$user_link = $user_link_begin . $user_link_style . $user_link_end;
		$user_link = ($no_profile || $is_guest) ? $username : $user_link;
		$user_link = ($get_only_color_style) ? '' : $user_link;
	}
	else
	{
		$user_color = check_valid_color($user_color);
		$user_color = ($user_color != false) ? $user_color : $config['active_users_color'];
		$user_link_style = ' style="font-weight: bold; text-decoration: none; color: ' . $user_color . ';"';

		if ($no_profile)
		{
			$user_link = '<span' . $user_link_style . '>' . $username . '</span>';
		}
		else
		{
			$user_link = $user_link_begin . $user_link_style . $user_link_end;
		}

		$user_link = ($get_only_color_style) ? $user_link_style : $user_link;
	}

	return $user_link;
}

/*
* Top X Posters
*/
function top_posters($user_limit, $show_admins = true, $show_mods = true, $only_array = false)
{
	global $db;
	$sql_level = ($show_admins && $show_mods) ? '' : ("AND u.user_level IN (" . USER . ($show_mods ? (", " . MOD) : '') . ($show_admins ? (", " . JUNIOR_ADMIN . ", " . ADMIN) : '') . ")");

	$sql = "SELECT u.username, u.user_id, u.user_active, u.user_color, u.user_level, u.user_posts, u.user_avatar, u.user_avatar_type, u.user_allowavatar
	FROM " . USERS_TABLE . " u
	WHERE (u.user_id <> " . ANONYMOUS . ")
		AND u.user_active = 1
		" . $sql_level . "
	ORDER BY u.user_posts DESC
	LIMIT " . $user_limit;
	$result = $db->sql_query($sql, 0, 'posts_top_posters_', POSTS_CACHE_FOLDER);

	$top_posters = '';
	$top_posters_array = array();
	while($row = $db->sql_fetchrow($result))
	{
		$top_posters .= (($top_posters == '') ? '' : ', ') . colorize_username($row['user_id'], $row['username'], $row['user_color'], $row['user_active']) . ' (' . $row['user_posts'] . ')';
		$top_posters_array[] = $row;
	}
	$db->sql_freeresult($result);

	$return_value = ($only_array == true) ? $top_posters_array : $top_posters;
	return $return_value;
}

/**
* Sends a birthday PM
*/
function birthday_pm_send()
{
	global $db, $cache, $board_config, $user, $lang, $phpEx;

	// Birthday - BEGIN
	// Check if the user has or have had birthday, also see if greetings are enabled
	if (($user->data['user_birthday'] != 999999) && !empty($board_config['birthday_greeting']) && (create_date('Ymd', time(), $board_config['board_timezone']) >= $user->data['user_next_birthday_greeting'] . realdate('md', $user->data['user_birthday'])))
	{
		// If a user had a birthday more than one week before we will not send the PM...
		if ((time() - gmmktime(0, 0, 0, $user->data['user_birthday_m'], $user->data['user_birthday_d'], $user->data['user_next_birthday_greeting'])) <= (86400 * 8))
		{
			// Birthday PM - BEGIN
			$pm_subject = $lang['Greeting_Messaging'];
			$pm_date = gmdate('U');

			$year = create_date('Y', time(), $board_config['board_timezone']);
			$date_today = create_date('Ymd', time(), $board_config['board_timezone']);
			$user_birthday = realdate('md', $user->data['user_birthday']);
			$user_birthday2 = (($year . $user_birthday < $date_today) ? ($year + 1) : $year) . $user_birthday;

			$user_age = create_date('Y', time(), $board_config['board_timezone']) - realdate('Y', $user->data['user_birthday']);
			if (create_date('md', time(), $board_config['board_timezone']) < realdate('md', $user->data['user_birthday']))
			{
				$user_age--;
			}

			$pm_text = ($user_birthday2 == $date_today) ? sprintf($lang['Birthday_greeting_today'], $user_age) : sprintf($lang['Birthday_greeting_prev'], $user_age, realdate(str_replace('Y', '', $lang['DATE_FORMAT_BIRTHDAY']), $user->data['user_birthday']) . ((!empty($user->data['user_next_birthday_greeting']) ? ($user->data['user_next_birthday_greeting']) : '')));

			$founder_id = (defined('FOUNDER_ID') ? FOUNDER_ID : get_founder_id());

			include_once(IP_ROOT_PATH . 'includes/class_pm.' . $phpEx);
			$privmsg_subject = sprintf($pm_subject, $board_config['sitename']);
			$privmsg_message = sprintf($pm_text, $board_config['sitename'], $board_config['sitename']);
			$privmsg_sender = $founder_id;
			$privmsg_recipient = $user->data['user_id'];

			$privmsg = new class_pm();
			$privmsg->delete_older_message('PM_INBOX', $privmsg_recipient);
			$privmsg->send($privmsg_sender, $privmsg_recipient, $privmsg_subject, $privmsg_message);
			unset($privmsg);
			// Birthday PM - END
		}

		// Update next greetings year
		$sql = "UPDATE " . USERS_TABLE . "
			SET user_next_birthday_greeting = " . (create_date('Y', time(), $board_config['board_timezone']) + 1) . "
			WHERE user_id = " . $user->data['user_id'];
		$status = $db->sql_query($sql);
	} //Sorry user shall not have a greeting this year
	// Birthday - END

}

/**
* Sends a birthday Email
*/
function birthday_email_send()
{
	global $db, $cache, $board_config, $lang, $phpEx;

	if (!class_exists('emailer'))
	{
		@include(IP_ROOT_PATH . 'includes/emailer.' . $phpEx);
	}
	$server_url = create_server_url();

	$birthdays_list = get_birthdays_list_email();
	foreach ($birthdays_list as $k => $v)
	{
		// Birthday - BEGIN
		// Check if the user has or have had birthday, also see if greetings are enabled
		if (!empty($board_config['birthday_greeting']))
		{
			// Birthday Email - BEGIN
			setup_extra_lang(array('lang_cron_vars'), '', $v['user_lang']);

			$year = create_date('Y', time(), $v['user_timezone']);
			$date_today = create_date('Ymd', time(), $v['user_timezone']);
			$user_birthday = realdate('md', $v['user_birthday']);
			$user_birthday2 = (($year . $user_birthday < $date_today) ? ($year + 1) : $year) . $user_birthday;

			$user_age = create_date('Y', time(), $v['user_timezone']) - realdate('Y', $v['user_birthday']);
			if (create_date('md', time(), $v['user_timezone']) < realdate('md', $v['user_birthday']))
			{
				$user_age--;
			}

			$email_subject = sprintf($lang['BIRTHDAY_GREETING_EMAIL_SUBJECT'], $board_config['sitename']);
			//$email_text = sprintf($lang['BIRTHDAY_GREETING_EMAIL_CONTENT_AGE'], $user_age);
			$email_text = sprintf($lang['BIRTHDAY_GREETING_EMAIL_CONTENT'], $board_config['sitename']);

			// Send the email!
			$emailer = new emailer();

			$emailer->use_template('birthday_greeting', $v['user_lang']);
			$emailer->to($v['user_email']);

			// If for some reason the mail template subject cannot be read... note it will not necessarily be in the posters own language!
			$emailer->set_subject($email_subject);

			$v['username'] = !empty($v['user_first_name']) ? $v['user_first_name'] : $v['username'];

			// This is a nasty kludge to remove the username var ... till (if?) translators update their templates
			$emailer->msg = preg_replace('#[ ]?{USERNAME}#', $v['username'], $emailer->msg);

			$email_sig = create_signature($board_config['board_email_sig']);
			$emailer->assign_vars(array(
				'USERNAME' => !empty($board_config['html_email']) ? htmlspecialchars($v['username']) : $v['username'],
				'USER_AGE' => $user_age,
				'EMAIL_SIG' => $email_sig,
				'SITENAME' => $board_config['sitename'],
				'SITE_URL' => $server_url
				)
			);

			$emailer->send();
			$emailer->reset();
			// Birthday Email - END

			$sql = "UPDATE " . USERS_TABLE . "
				SET user_next_birthday_greeting = " . (create_date('Y', time(), $v['user_timezone']) + 1) . "
				WHERE user_id = " . $v['user_id'];
			$status = $db->sql_query($sql);
		}
		// Birthday - END
	}
	// We reset the lang again for default lang...
	setup_extra_lang(array('lang_cron_vars'));
}

/**
* Get the birthdays list to send greetings email.
*/
function get_birthdays_list_email()
{
	global $db, $cache;

	// Since the highest timezone is +12, we start twelve hours later... we also need to keep into account that -12 and +12 have one day delay!
	$time_now = time();
	$time_now_12 = $time_now + (60 * 60 * 12);
	$b_h = gmdate('G', $time_now);
	$timezone_delta = ($b_h == 0) ? 0 : (($b_h < 12) ? -$bh : (24 - $b_h));
	$b_y = gmdate('Y', $time_now_12);
	$b_m = gmdate('n', $time_now_12);
	$b_d = gmdate('j', $time_now_12);

	$sql_where = ' ((u.user_birthday_y <= ' . $b_y . ') AND (u.user_birthday_m = ' . $b_m . ') AND (u.user_birthday_d = ' . $b_d . ')) ';

	if ((gmdate('L', $time_now_12) == 0) && ($b_m == 3) && ($b_d == 1))
	{
		$sql_where .= ' OR ((u.user_birthday_y <= ' . $b_y . ') AND (u.user_birthday_m = 2) AND (u.user_birthday_d = 29)) ';
	}

	$sql_timezone = '(user_timezone LIKE "' . $timezone_delta . '.%")';
	if ($timezone_delta == 12)
	{
		$sql_timezone = ' AND (' . $sql_timezone . ' OR (user_timezone LIKE "-' . $timezone_delta . '.%")) ';
	}
	else
	{
		$sql_timezone = ' AND ' . $sql_timezone . ' ';
	}

	$sql_where = ' AND (u.user_birthday <> 999999) AND (user_active = 1) AND (user_allow_mass_email = 1) ' . $sql_timezone . ' AND (' . $sql_where . ')';

	// Changed sorting by username_clean instead of username
	$sql = "SELECT u.user_id, u.username, u.user_first_name, u.user_active, u.user_color, u.user_email, u.user_timezone, u.user_lang, u.user_birthday, u.user_birthday_y, u.user_birthday_m, u.user_birthday_d, u.user_next_birthday_greeting
				FROM " . USERS_TABLE . " AS u
				WHERE u.user_id <> " . ANONYMOUS . "
				" . $sql_where . "
				ORDER BY username_clean";
	$result = $db->sql_query($sql);
	$birthdays_list = $db->sql_fetchrowset($result);
	$db->sql_freeresult($result);

	return $birthdays_list;
}
/**
* Flips user_type from active to inactive and vice versa, handles group membership updates
*
* @param string $mode can be flip for flipping from active/inactive, activate or deactivate
*/
function user_active_flip($mode, $user_id_ary, $reason = INACTIVE_MANUAL)
{
	global $board_config, $db, $user, $auth, $phpbb_dispatcher;

	$deactivated = $activated = 0;
	$sql_statements = array();

	if (!is_array($user_id_ary))
	{
		$user_id_ary = array($user_id_ary);
	}

	if (!count($user_id_ary))
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

	if (count($sql_statements))
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
		$board_config->increment('num_users', $deactivated * (-1), false);
	}

	if ($activated)
	{
		$board_config->increment('num_users', $activated, false);
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
	global $db, $user, $cache, $phpbb_log;

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
			if (count($ban_other) == 3 && ((int) $ban_other[0] < 9999) &&
				(strlen($ban_other[0]) == 4) && (strlen($ban_other[1]) == 2) && (strlen($ban_other[2]) == 2))
			{
				$ban_end = max($current_time, $user->create_datetime()
					->setDate((int) $ban_other[0], (int) $ban_other[1], (int) $ban_other[2])
					->setTime(0, 0, 0)
					->getTimestamp() + $user->timezone->getOffset(new DateTime('UTC')));
			}
			else
			{
				trigger_error('LENGTH_BAN_INVALID', E_USER_WARNING);
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

			// At the moment we do not support wildcard username banning

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
						trigger_error('CANNOT_BAN_YOURSELF', E_USER_WARNING);
					}
					if (in_array($clean_name, $founder_names))
					{
						trigger_error('CANNOT_BAN_FOUNDER', E_USER_WARNING);
					}
					$sql_usernames[] = $clean_name;
				}
			}

			// Make sure we have been given someone to ban
			if (!count($sql_usernames))
			{
				trigger_error('NO_USER_SPECIFIED', E_USER_WARNING);
			}

			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . '
				WHERE ' . $db->sql_in_set('username_clean', $sql_usernames);

			// Do not allow banning yourself, the guest account, or founders.
			$non_bannable = array($user->data['user_id'], ANONYMOUS);
			if (count($founder))
			{
				$sql .= ' AND ' . $db->sql_in_set('user_id', array_merge(array_keys($founder), $non_bannable), true);
			}
			else
			{
				$sql .= ' AND ' . $db->sql_in_set('user_id', $non_bannable, true);
			}

			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				do
				{
					$banlist_ary[] = (int) $row['user_id'];
				}
				while ($row = $db->sql_fetchrow($result));
			}
			else
			{
				$db->sql_freeresult($result);
				trigger_error('NO_USERS', E_USER_WARNING);
			}
			$db->sql_freeresult($result);
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

							$banlist_ary[] = "$ip_1_counter.*";
						}

						while ($ip_2_counter <= $ip_2_end)
						{
							$ip_3_counter = ($ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[3] : 0;
							$ip_3_end = ($ip_2_counter < $ip_2_end || $ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[7];

							if ($ip_3_counter == 0 && $ip_3_end == 254)
							{
								$ip_3_counter = 256;

								$banlist_ary[] = "$ip_1_counter.$ip_2_counter.*";
							}

							while ($ip_3_counter <= $ip_3_end)
							{
								$ip_4_counter = ($ip_3_counter == $ip_range_explode[3] && $ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[4] : 0;
								$ip_4_end = ($ip_3_counter < $ip_3_end || $ip_2_counter < $ip_2_end) ? 254 : $ip_range_explode[8];

								if ($ip_4_counter == 0 && $ip_4_end == 254)
								{
									$ip_4_counter = 256;

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
					$banlist_ary[] = '*';
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

				if (empty($banlist_ary))
				{
					trigger_error('NO_IPS_DEFINED', E_USER_WARNING);
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

					if (!count($founder) || !in_array($ban_item, $founder))
					{
						$banlist_ary[] = $ban_item;
					}
				}
			}

			if (count($ban_list) == 0)
			{
				trigger_error('NO_EMAILS_DEFINED', E_USER_WARNING);
			}
		break;

		default:
			trigger_error('NO_MODE', E_USER_WARNING);
		break;
	}

	// Fetch currently set bans of the specified type and exclude state. Prevent duplicate bans.
	$sql_where = ($type == 'ban_userid') ? 'ban_userid <> 0' : "$type <> ''";

	$sql = "SELECT $type
		FROM " . BANLIST_TABLE . "
		WHERE $sql_where
			AND ban_exclude = " . (int) $ban_exclude;
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

		$banlist_ary_tmp = array_intersect($banlist_ary, $banlist_ary_tmp);

		if (count($banlist_ary_tmp))
		{
			// One or more entities are already banned/excluded, delete the existing bans, so they can be re-inserted with the given new length
			$sql = 'DELETE FROM ' . BANLIST_TABLE . '
				WHERE ' . $db->sql_in_set($type, $banlist_ary_tmp) . '
					AND ban_exclude = ' . (int) $ban_exclude;
			$db->sql_query($sql);
		}

		unset($banlist_ary_tmp);
	}
	$db->sql_freeresult($result);

	// We have some entities to ban
	if (count($banlist_ary))
	{
		$sql_ary = array();

		foreach ($banlist_ary as $ban_entry)
		{
			$sql_ary[] = array(
				$type				=> $ban_entry,
				'ban_start'			=> (int) $current_time,
				'ban_end'			=> (int) $ban_end,
				'ban_exclude'		=> (int) $ban_exclude,
				'ban_reason'		=> (string) $ban_reason,
				'ban_give_reason'	=> (string) $ban_give_reason,
			);
		}

		$db->sql_multi_insert(BANLIST_TABLE, $sql_ary);

		// If we are banning we want to logout anyone matching the ban
		if (!$ban_exclude)
		{
			switch ($mode)
			{
				case 'user':
					$sql_where = 'WHERE ' . $db->sql_in_set('session_user_id', $banlist_ary);
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

		// Add to admin log, moderator log and user notes
		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, $log_entry . strtoupper($mode), false, array($ban_reason, $ban_list_log));
		$phpbb_log->add('mod', $user->data['user_id'], $user->ip, $log_entry . strtoupper($mode), false, array(
			'forum_id' => 0,
			'topic_id' => 0,
			$ban_reason,
			$ban_list_log
		));
		if ($mode == 'user')
		{
			foreach ($banlist_ary as $user_id)
			{
				$phpbb_log->add('user', $user->data['user_id'], $user->ip, $log_entry . strtoupper($mode), false, array(
					'reportee_id' => $user_id,
					$ban_reason,
					$ban_list_log
				));
			}
		}

		$cache->destroy('sql', BANLIST_TABLE);

		return true;
	}

	// There was nothing to ban/exclude. But destroying the cache because of the removal of stale bans.
	$cache->destroy('sql', BANLIST_TABLE);

	return false;
}

/**
* Unban User
*/
function user_unban($mode, $ban)
{
	global $db, $user, $cache, $phpbb_log, $phpbb_dispatcher;

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

	if (count($unban_sql))
	{
		// Grab details of bans for logging information later
		switch ($mode)
		{
			case 'user':
				$sql = 'SELECT u.username AS unban_info, u.user_id
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
		$user_ids_ary = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$l_unban_list .= (($l_unban_list != '') ? ', ' : '') . $row['unban_info'];
			if ($mode == 'user')
			{
				$user_ids_ary[] = $row['user_id'];
			}
		}
		$db->sql_freeresult($result);

		$sql = 'DELETE FROM ' . BANLIST_TABLE . '
			WHERE ' . $db->sql_in_set('ban_id', $unban_sql);
		$db->sql_query($sql);

		// Add to moderator log, admin log and user notes
		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_UNBAN_' . strtoupper($mode), false, array($l_unban_list));
		$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_UNBAN_' . strtoupper($mode), false, array(
			'forum_id' => 0,
			'topic_id' => 0,
			$l_unban_list
		));
		if ($mode == 'user')
		{
			foreach ($user_ids_ary as $user_id)
			{
				$phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LOG_UNBAN_' . strtoupper($mode), false, array(
					'reportee_id' => $user_id,
					$l_unban_list
				));
			}
		}

	$cache->destroy('sql', BANLIST_TABLE);

	return false;
	}
}

/**
* Internet Protocol Address Whois
* RFC3912: WHOIS Protocol Specification
*
* @param string $ip		Ip address, either IPv4 or IPv6.
*
* @return string		Empty string if not a valid ip address.
*						Otherwise make_clickable()'ed whois result.
*/
function user_ipwhois($ip)
{
	if (empty($ip))
	{
		return '';
	}

	if (preg_match(get_preg_expression('ipv4'), $ip))
	{
		// IPv4 address
		$whois_host = 'whois.arin.net.';
	}
	else if (preg_match(get_preg_expression('ipv6'), $ip))
	{
		// IPv6 address
		$whois_host = 'whois.sixxs.net.';
	}
	else
	{
		return '';
	}

	$ipwhois = '';

	if (($fsk = @fsockopen($whois_host, 43)))
	{
		// CRLF as per RFC3912
		fputs($fsk, "$ip\r\n");
		while (!feof($fsk))
		{
			$ipwhois .= fgets($fsk, 1024);
		}
		@fclose($fsk);
	}

	$match = array();

	// Test for referrals from $whois_host to other whois databases, roll on rwhois
	if (preg_match('#ReferralServer:[\x20]*whois://(.+)#im', $ipwhois, $match))
	{
		if (strpos($match[1], ':') !== false)
		{
			$pos	= strrpos($match[1], ':');
			$server	= substr($match[1], 0, $pos);
			$port	= (int) substr($match[1], $pos + 1);
			unset($pos);
		}
		else
		{
			$server	= $match[1];
			$port	= 43;
		}

		$buffer = '';

		if (($fsk = @fsockopen($server, $port)))
		{
			fputs($fsk, "$ip\r\n");
			while (!feof($fsk))
			{
				$buffer .= fgets($fsk, 1024);
			}
			@fclose($fsk);
		}

		// Use the result from $whois_host if we don't get any result here
		$ipwhois = (empty($buffer)) ? $ipwhois : $buffer;
	}

	$ipwhois = htmlspecialchars($ipwhois);

	// Magic URL ;)
	return trim(make_clickable($ipwhois, false, ''));
}

/**
* Data validation ... used primarily but not exclusively by ucp modules
*
* "Master" function for validating a range of data types
*/
function validate_data($data, $val_ary)
{
	global $user;

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

			if (is_array($function))
			{
				$result = call_user_func_array(array($function[0], 'validate_' . $function[1]), $validate);
			}
			else
			{
				$function_prefix = (function_exists('phpbb_validate_' . $function)) ? 'phpbb_validate_' : 'validate_';
				$result = call_user_func_array($function_prefix . $function, $validate);
			}

			if ($result)
			{
				// Since errors are checked later for their language file existence, we need to make sure custom errors are not adjusted.
				$error[] = (empty($user->lang[$result . '_' . strtoupper($var)])) ? $result : $result . '_' . strtoupper($var);
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
* Validate Date
* @param String $string a date in the dd-mm-yyyy format
* @return	boolean
*/
function validate_date($date_string, $optional = false)
{
	$date = explode('-', $date_string);
	if ((empty($date) || count($date) != 3) && $optional)
	{
		return false;
	}
	else if ($optional)
	{
		for ($field = 0; $field <= 1; $field++)
		{
			$date[$field] = (int) $date[$field];
			if (empty($date[$field]))
			{
				$date[$field] = 1;
			}
		}
		$date[2] = (int) $date[2];
		// assume an arbitrary leap year
		if (empty($date[2]))
		{
			$date[2] = 1980;
		}
	}

	if (count($date) != 3 || !checkdate($date[1], $date[0], $date[2]))
	{
		return 'INVALID';
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
* Validate Language Pack ISO Name
*
* Tests whether a language name is valid and installed
*
* @param string $lang_iso	The language string to test
*
* @return bool|string		Either false if validation succeeded or
*							a string which will be used as the error message
*							(with the variable name appended)
*/
function validate_language_iso_name($lang_iso)
{
	global $db;

	$sql = 'SELECT lang_id
		FROM ' . LANG_TABLE . "
		WHERE lang_iso = '" . $db->sql_escape($lang_iso) . "'";
	$result = $db->sql_query($sql);
	$lang_id = (int) $db->sql_fetchfield('lang_id');
	$db->sql_freeresult($result);

	return ($lang_id) ? false : 'WRONG_DATA';
}

/**
* Validate Timezone Name
*
* Tests whether a timezone name is valid
*
* @param string $timezone	The timezone string to test
*
* @return bool|string		Either false if validation succeeded or
*							a string which will be used as the error message
*							(with the variable name appended)
*/
function phpbb_validate_timezone($timezone)
{
	return (in_array($timezone, phpbb_get_timezone_identifiers($timezone))) ? false : 'TIMEZONE_INVALID';
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
	global $board_config, $db, $user, $cache;

	$clean_username = utf8_clean_string($username);
	$allowed_username = ($allowed_username === false) ? $user->data['username_clean'] : utf8_clean_string($allowed_username);

	if ($allowed_username == $clean_username)
	{
		return false;
	}

	// ... fast checks first.
	if (strpos($username, '&quot;') !== false || strpos($username, '"') !== false || empty($clean_username))
	{
		return 'INVALID_CHARS';
	}

	switch ($board_config['allow_name_chars'])
	{
		case 'USERNAME_CHARS_ANY':
			$regex = '.+';
		break;

		case 'USERNAME_ALPHA_ONLY':
			$regex = '[A-Za-z0-9]+';
		break;

		case 'USERNAME_ALPHA_SPACERS':
			$regex = '[A-Za-z0-9-[\]_+ ]+';
		break;

		case 'USERNAME_LETTER_NUM':
			$regex = '[\p{Lu}\p{Ll}\p{N}]+';
		break;

		case 'USERNAME_LETTER_NUM_SPACERS':
			$regex = '[-\]_+ [\p{Lu}\p{Ll}\p{N}]+';
		break;

		case 'USERNAME_ASCII':
		default:
			$regex = '[\x01-\x7F]+';
		break;
	}

	if (!preg_match('#^' . $regex . '$#u', $username))
	{
		return 'INVALID_CHARS';
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
		if (preg_match('#^' . $bad_username . '$#', $clean_username))
		{
			return 'USERNAME_DISALLOWED';
		}
	}

	return false;
}

/**
* Check to see if the password meets the complexity settings
*
* @return	boolean|string	Either false if validation succeeded or a string which will be used as the error message (with the variable name appended)
*/
function validate_password($password)
{
	global $board_config;

	if ($password === '' || $board_config['pass_complex'] === 'PASS_TYPE_ANY')
	{
		// Password empty or no password complexity required.
		return false;
	}

	$upp = '\p{Lu}';
	$low = '\p{Ll}';
	$num = '\p{N}';
	$sym = '[^\p{Lu}\p{Ll}\p{N}]';
	$chars = array();

	switch ($board_config['pass_complex'])
	{
		// No break statements below ...
		// We require strong passwords in case pass_complex is not set or is invalid
		default:

		// Require mixed case letters, numbers and symbols
		case 'PASS_TYPE_SYMBOL':
			$chars[] = $sym;

		// Require mixed case letters and numbers
		case 'PASS_TYPE_ALPHA':
			$chars[] = $num;

		// Require mixed case letters
		case 'PASS_TYPE_CASE':
			$chars[] = $low;
			$chars[] = $upp;
	}

	foreach ($chars as $char)
	{
		if (!preg_match('#' . $char . '#u', $password))
		{
			return 'INVALID_CHARS';
		}
	}

	return false;
}

/**
* Check to see if email address is a valid address and contains a MX record
*
* @param string $email The email to check
*
* @return mixed Either false if validation succeeded or a string which will be used as the error message (with the variable name appended)
*/
function phpbb_validate_email($email, $board_config = null)
{
	if ($board_config === null)
	{
		global $board_config;
	}

	$email = strtolower($email);

	if (!preg_match('/^' . get_preg_expression('email') . '$/i', $email))
	{
		return 'EMAIL_INVALID';
	}

	// Check MX record.
	// The idea for this is from reading the UseBB blog/announcement. :)
	if ($board_config['email_check_mx'])
	{
		list(, $domain) = explode('@', $email);

		if (phpbb_checkdnsrr($domain, 'A') === false && phpbb_checkdnsrr($domain, 'MX') === false)
		{
			return 'DOMAIN_NO_MX_RECORD';
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
function validate_user_email($email, $allowed_email = false)
{
	global $board_config, $db, $user;

	$email = strtolower($email);
	$allowed_email = ($allowed_email === false) ? strtolower($user->data['user_email']) : strtolower($allowed_email);

	if ($allowed_email == $email)
	{
		return false;
	}

	$validate_email = phpbb_validate_email($email, $board_config);
	if ($validate_email)
	{
		return $validate_email;
	}

	if (($ban_reason = $user->check_ban(false, false, $email, true)) !== false)
	{
		return ($ban_reason === true) ? 'EMAIL_BANNED' : $ban_reason;
	}

	if (!$board_config['allow_emailreuse'])
	{
		$sql = 'SELECT user_email_hash
			FROM ' . USERS_TABLE . "
			WHERE user_email_hash = " . $db->sql_escape(phpbb_email_hash($email));
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
* Validate jabber address
* Taken from the jabber class within flyspray (see author notes)
*
* @author flyspray.org
*/
function validate_jabber($jid)
{
	if (!$jid)
	{
		return false;
	}

	$separator_pos = strpos($jid, '@');

	if ($separator_pos === false)
	{
		return 'WRONG_DATA';
	}

	$username = substr($jid, 0, $separator_pos);
	$realm = substr($jid, $separator_pos + 1);

	if (strlen($username) == 0 || strlen($realm) < 3)
	{
		return 'WRONG_DATA';
	}

	$arr = explode('.', $realm);

	if (count($arr) == 0)
	{
		return 'WRONG_DATA';
	}

	foreach ($arr as $part)
	{
		if (substr($part, 0, 1) == '-' || substr($part, -1, 1) == '-')
		{
			return 'WRONG_DATA';
		}

		if (!preg_match("@^[a-zA-Z0-9-.]+$@", $part))
		{
			return 'WRONG_DATA';
		}
	}

	$boundary = array(array(0, 127), array(192, 223), array(224, 239), array(240, 247), array(248, 251), array(252, 253));

	// Prohibited Characters RFC3454 + RFC3920
	$prohibited = array(
		// Table C.1.1
		array(0x0020, 0x0020),		// SPACE
		// Table C.1.2
		array(0x00A0, 0x00A0),		// NO-BREAK SPACE
		array(0x1680, 0x1680),		// OGHAM SPACE MARK
		array(0x2000, 0x2001),		// EN QUAD
		array(0x2001, 0x2001),		// EM QUAD
		array(0x2002, 0x2002),		// EN SPACE
		array(0x2003, 0x2003),		// EM SPACE
		array(0x2004, 0x2004),		// THREE-PER-EM SPACE
		array(0x2005, 0x2005),		// FOUR-PER-EM SPACE
		array(0x2006, 0x2006),		// SIX-PER-EM SPACE
		array(0x2007, 0x2007),		// FIGURE SPACE
		array(0x2008, 0x2008),		// PUNCTUATION SPACE
		array(0x2009, 0x2009),		// THIN SPACE
		array(0x200A, 0x200A),		// HAIR SPACE
		array(0x200B, 0x200B),		// ZERO WIDTH SPACE
		array(0x202F, 0x202F),		// NARROW NO-BREAK SPACE
		array(0x205F, 0x205F),		// MEDIUM MATHEMATICAL SPACE
		array(0x3000, 0x3000),		// IDEOGRAPHIC SPACE
		// Table C.2.1
		array(0x0000, 0x001F),		// [CONTROL CHARACTERS]
		array(0x007F, 0x007F),		// DELETE
		// Table C.2.2
		array(0x0080, 0x009F),		// [CONTROL CHARACTERS]
		array(0x06DD, 0x06DD),		// ARABIC END OF AYAH
		array(0x070F, 0x070F),		// SYRIAC ABBREVIATION MARK
		array(0x180E, 0x180E),		// MONGOLIAN VOWEL SEPARATOR
		array(0x200C, 0x200C), 		// ZERO WIDTH NON-JOINER
		array(0x200D, 0x200D),		// ZERO WIDTH JOINER
		array(0x2028, 0x2028),		// LINE SEPARATOR
		array(0x2029, 0x2029),		// PARAGRAPH SEPARATOR
		array(0x2060, 0x2060),		// WORD JOINER
		array(0x2061, 0x2061),		// FUNCTION APPLICATION
		array(0x2062, 0x2062),		// INVISIBLE TIMES
		array(0x2063, 0x2063),		// INVISIBLE SEPARATOR
		array(0x206A, 0x206F),		// [CONTROL CHARACTERS]
		array(0xFEFF, 0xFEFF),		// ZERO WIDTH NO-BREAK SPACE
		array(0xFFF9, 0xFFFC),		// [CONTROL CHARACTERS]
		array(0x1D173, 0x1D17A),	// [MUSICAL CONTROL CHARACTERS]
		// Table C.3
		array(0xE000, 0xF8FF),		// [PRIVATE USE, PLANE 0]
		array(0xF0000, 0xFFFFD),	// [PRIVATE USE, PLANE 15]
		array(0x100000, 0x10FFFD),	// [PRIVATE USE, PLANE 16]
		// Table C.4
		array(0xFDD0, 0xFDEF),		// [NONCHARACTER CODE POINTS]
		array(0xFFFE, 0xFFFF),		// [NONCHARACTER CODE POINTS]
		array(0x1FFFE, 0x1FFFF),	// [NONCHARACTER CODE POINTS]
		array(0x2FFFE, 0x2FFFF),	// [NONCHARACTER CODE POINTS]
		array(0x3FFFE, 0x3FFFF),	// [NONCHARACTER CODE POINTS]
		array(0x4FFFE, 0x4FFFF),	// [NONCHARACTER CODE POINTS]
		array(0x5FFFE, 0x5FFFF),	// [NONCHARACTER CODE POINTS]
		array(0x6FFFE, 0x6FFFF),	// [NONCHARACTER CODE POINTS]
		array(0x7FFFE, 0x7FFFF),	// [NONCHARACTER CODE POINTS]
		array(0x8FFFE, 0x8FFFF),	// [NONCHARACTER CODE POINTS]
		array(0x9FFFE, 0x9FFFF),	// [NONCHARACTER CODE POINTS]
		array(0xAFFFE, 0xAFFFF),	// [NONCHARACTER CODE POINTS]
		array(0xBFFFE, 0xBFFFF),	// [NONCHARACTER CODE POINTS]
		array(0xCFFFE, 0xCFFFF),	// [NONCHARACTER CODE POINTS]
		array(0xDFFFE, 0xDFFFF),	// [NONCHARACTER CODE POINTS]
		array(0xEFFFE, 0xEFFFF),	// [NONCHARACTER CODE POINTS]
		array(0xFFFFE, 0xFFFFF),	// [NONCHARACTER CODE POINTS]
		array(0x10FFFE, 0x10FFFF),	// [NONCHARACTER CODE POINTS]
		// Table C.5
		array(0xD800, 0xDFFF),		// [SURROGATE CODES]
		// Table C.6
		array(0xFFF9, 0xFFF9),		// INTERLINEAR ANNOTATION ANCHOR
		array(0xFFFA, 0xFFFA),		// INTERLINEAR ANNOTATION SEPARATOR
		array(0xFFFB, 0xFFFB),		// INTERLINEAR ANNOTATION TERMINATOR
		array(0xFFFC, 0xFFFC),		// OBJECT REPLACEMENT CHARACTER
		array(0xFFFD, 0xFFFD),		// REPLACEMENT CHARACTER
		// Table C.7
		array(0x2FF0, 0x2FFB),		// [IDEOGRAPHIC DESCRIPTION CHARACTERS]
		// Table C.8
		array(0x0340, 0x0340),		// COMBINING GRAVE TONE MARK
		array(0x0341, 0x0341),		// COMBINING ACUTE TONE MARK
		array(0x200E, 0x200E),		// LEFT-TO-RIGHT MARK
		array(0x200F, 0x200F),		// RIGHT-TO-LEFT MARK
		array(0x202A, 0x202A),		// LEFT-TO-RIGHT EMBEDDING
		array(0x202B, 0x202B),		// RIGHT-TO-LEFT EMBEDDING
		array(0x202C, 0x202C),		// POP DIRECTIONAL FORMATTING
		array(0x202D, 0x202D),		// LEFT-TO-RIGHT OVERRIDE
		array(0x202E, 0x202E),		// RIGHT-TO-LEFT OVERRIDE
		array(0x206A, 0x206A),		// INHIBIT SYMMETRIC SWAPPING
		array(0x206B, 0x206B),		// ACTIVATE SYMMETRIC SWAPPING
		array(0x206C, 0x206C),		// INHIBIT ARABIC FORM SHAPING
		array(0x206D, 0x206D),		// ACTIVATE ARABIC FORM SHAPING
		array(0x206E, 0x206E),		// NATIONAL DIGIT SHAPES
		array(0x206F, 0x206F),		// NOMINAL DIGIT SHAPES
		// Table C.9
		array(0xE0001, 0xE0001),	// LANGUAGE TAG
		array(0xE0020, 0xE007F),	// [TAGGING CHARACTERS]
		// RFC3920
		array(0x22, 0x22),			// "
		array(0x26, 0x26),			// &
		array(0x27, 0x27),			// '
		array(0x2F, 0x2F),			// /
		array(0x3A, 0x3A),			// :
		array(0x3C, 0x3C),			// <
		array(0x3E, 0x3E),			// >
		array(0x40, 0x40)			// @
	);

	$pos = 0;
	$result = true;

	while ($pos < strlen($username))
	{
		$len = $uni = 0;
		for ($i = 0; $i <= 5; $i++)
		{
			if (ord($username[$pos]) >= $boundary[$i][0] && ord($username[$pos]) <= $boundary[$i][1])
			{
				$len = $i + 1;
				$uni = (ord($username[$pos]) - $boundary[$i][0]) * pow(2, $i * 6);

				for ($k = 1; $k < $len; $k++)
				{
					$uni += (ord($username[$pos + $k]) - 128) * pow(2, ($i - $k) * 6);
				}

				break;
			}
		}

		if ($len == 0)
		{
			return 'WRONG_DATA';
		}

		foreach ($prohibited as $pval)
		{
			if ($uni >= $pval[0] && $uni <= $pval[1])
			{
				$result = false;
				break 2;
			}
		}

		$pos = $pos + $len;
	}

	if (!$result)
	{
		return 'WRONG_DATA';
	}

	return false;
}

/**
* Validate hex colour value
*
* @param string $colour The hex colour value
* @param bool $optional Whether the colour value is optional. True if an empty
*			string will be accepted as correct input, false if not.
* @return bool|string Error message if colour value is incorrect, false if it
*			fits the hex colour code
*/
function phpbb_validate_hex_colour($colour, $optional = false)
{
	if ($colour === '')
	{
		return (($optional) ? false : 'WRONG_DATA');
	}

	if (!preg_match('/^([0-9a-fA-F]{6}|[0-9a-fA-F]{3})$/', $colour))
	{
		return 'WRONG_DATA';
	}

	return false;
}

/**
* Verifies whether a style ID corresponds to an active style.
*
* @param int $style_id The style_id of a style which should be checked if activated or not.
* @return boolean
*/
function phpbb_style_is_active($style_id)
{
	global $db;

	$sql = 'SELECT style_active
		FROM ' . STYLES_TABLE . '
		WHERE style_id = '. (int) $style_id;
	$result = $db->sql_query($sql);

	$style_is_active = (bool) $db->sql_fetchfield('style_active');
	$db->sql_freeresult($result);

	return $style_is_active;
}

/**
* Remove avatar
*/
function avatar_delete($mode, $row, $clean_db = false)
{
	global $board_config, $phpbb_container;

	$storage = $phpbb_container->get('storage.avatar');

	// Check if the users avatar is actually *not* a group avatar
	if ($mode == 'user')
	{
		if (strpos($row['user_avatar'], 'g') === 0 || (((int) $row['user_avatar'] !== 0) && ((int) $row['user_avatar'] !== (int) $row['user_id'])))
		{
			return false;
		}
	}

	if ($clean_db)
	{
		avatar_remove_db($row[$mode . '_avatar']);
	}
	$filename = get_avatar_filename($row[$mode . '_avatar']);

	try
	{
		$storage->delete($filename);

		return true;
	}
	catch (\phpbb\storage\exception\exception $e)
	{
		// Fail is covered by return statement below
	}

	return false;
}

/**
* Generates avatar filename from the database entry
*/
function get_avatar_filename($avatar_entry)
{
	global $board_config;

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
	return $board_config['avatar_salt'] . '_' . (($avatar_group) ? 'g' : '') . $avatar_entry . '.' . $ext;
}

/**
* Returns an explanation string with maximum avatar settings
*
* @return string
*/
function phpbb_avatar_explanation_string()
{
	global $board_config, $user;

	return $user->lang(($board_config['avatar_filesize'] == 0) ? 'AVATAR_EXPLAIN_NO_FILESIZE' : 'AVATAR_EXPLAIN',
		$user->lang('PIXELS', (int) $board_config['avatar_max_width']),
		$user->lang('PIXELS', (int) $board_config['avatar_max_height']),
		round($board_config['avatar_filesize'] / 1024));
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
	global $db, $user, $phpbb_container, $phpbb_log;

	/** @var \phpbb\group\helper $group_helper */
	$group_helper = $phpbb_container->get('group_helper');

	$error = array();

	// Attributes which also affect the users table
	$user_attribute_ary = array('group_colour', 'group_rank', 'group_avatar', 'group_avatar_type', 'group_avatar_width', 'group_avatar_height');

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

	$group_teampage = !empty($group_attributes['group_teampage']);
	unset($group_attributes['group_teampage']);

	if (!count($error))
	{
		$current_legend = \phpbb\groupposition\legend::GROUP_DISABLED;
		$current_teampage = \phpbb\groupposition\teampage::GROUP_DISABLED;

		/* @var $legend \phpbb\groupposition\legend */
		$legend = $phpbb_container->get('groupposition.legend');

		/* @var $teampage \phpbb\groupposition\teampage */
		$teampage = $phpbb_container->get('groupposition.teampage');

		if ($group_id)
		{
			try
			{
				$current_legend = $legend->get_group_value($group_id);
				$current_teampage = $teampage->get_group_value($group_id);
			}
			catch (\phpbb\groupposition\exception $exception)
			{
				trigger_error($user->lang($exception->getMessage()));
			}
		}

		if (!empty($group_attributes['group_legend']))
		{
			if (($group_id && ($current_legend == \phpbb\groupposition\legend::GROUP_DISABLED)) || !$group_id)
			{
				// Old group currently not in the legend or new group, add at the end.
				$group_attributes['group_legend'] = 1 + $legend->get_group_count();
			}
			else
			{
				// Group stayes in the legend
				$group_attributes['group_legend'] = $current_legend;
			}
		}
		else if ($group_id && ($current_legend != \phpbb\groupposition\legend::GROUP_DISABLED))
		{
			// Group is removed from the legend
			try
			{
				$legend->delete_group($group_id, true);
			}
			catch (\phpbb\groupposition\exception $exception)
			{
				trigger_error($user->lang($exception->getMessage()));
			}
			$group_attributes['group_legend'] = \phpbb\groupposition\legend::GROUP_DISABLED;
		}
		else
		{
			$group_attributes['group_legend'] = \phpbb\groupposition\legend::GROUP_DISABLED;
		}

		// Unset the objects, we don't need them anymore.
		unset($legend);

		$user_ary = array();
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

		if (count($group_attributes))
		{
			// Merge them with $sql_ary to properly update the group
			$sql_ary = array_merge($sql_ary, $group_attributes);
		}

		// Setting the log message before we set the group id (if group gets added)
		$log = ($group_id) ? 'LOG_GROUP_UPDATED' : 'LOG_GROUP_CREATED';

		if ($group_id)
		{
			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . '
				WHERE group_id = ' . $group_id;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$user_ary[] = $row['user_id'];
			}
			$db->sql_freeresult($result);

			if (isset($sql_ary['group_avatar']))
			{
				remove_default_avatar($group_id, $user_ary);
			}

			if (isset($sql_ary['group_rank']))
			{
				remove_default_rank($group_id, $user_ary);
			}

			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
				WHERE group_id = $group_id";
			$db->sql_query($sql);

			// Since we may update the name too, we need to do this on other tables too...
			$sql = 'UPDATE ' . MODERATOR_CACHE_TABLE . "
				SET group_name = '" . $db->sql_escape($sql_ary['group_name']) . "'
				WHERE group_id = $group_id";
			$db->sql_query($sql);

			// One special case is the group skip auth setting. If this was changed we need to purge permissions for this group
			if (isset($group_attributes['group_skip_auth']))
			{
				// Get users within this group...
				$sql = 'SELECT user_id
					FROM ' . USER_GROUP_TABLE . '
					WHERE group_id = ' . $group_id . '
						AND user_pending = 0';
				$result = $db->sql_query($sql);

				$user_id_ary = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$user_id_ary[] = $row['user_id'];
				}
				$db->sql_freeresult($result);

				if (!empty($user_id_ary))
				{
					global $auth;

					// Clear permissions cache of relevant users
					$auth->acl_clear_prefetch($user_id_ary);
				}
			}
		}
		else
		{
			$sql = 'INSERT INTO ' . GROUPS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
		}

		// Remove the group from the teampage, only if unselected and we are editing a group,
		// which is currently displayed.
		if (!$group_teampage && $group_id && $current_teampage != \phpbb\groupposition\teampage::GROUP_DISABLED)
		{
			try
			{
				$teampage->delete_group($group_id);
			}
			catch (\phpbb\groupposition\exception $exception)
			{
				trigger_error($user->lang($exception->getMessage()));
			}
		}

		if (!$group_id)
		{
			$group_id = $db->sql_nextid();

			if (isset($sql_ary['group_avatar_type']) && $sql_ary['group_avatar_type'] == 'avatar.driver.upload')
			{
				group_correct_avatar($group_id, $sql_ary['group_avatar']);
			}
		}

		try
		{
			if ($group_teampage && $current_teampage == \phpbb\groupposition\teampage::GROUP_DISABLED)
			{
				$teampage->add_group($group_id);
			}

			if ($group_teampage)
			{
				if ($current_teampage == \phpbb\groupposition\teampage::GROUP_DISABLED)
				{
					$teampage->add_group($group_id);
				}
			}
			else if ($group_id && ($current_teampage != \phpbb\groupposition\teampage::GROUP_DISABLED))
			{
				$teampage->delete_group($group_id);
			}
		}
		catch (\phpbb\groupposition\exception $exception)
		{
			trigger_error($user->lang($exception->getMessage()));
		}
		unset($teampage);

		// Set user attributes
		$sql_ary = array();
		if (count($group_attributes))
		{
			// Go through the user attributes array, check if a group attribute matches it and then set it. ;)
			foreach ($user_attribute_ary as $attribute)
			{
				if (!isset($group_attributes[$attribute]))
				{
					continue;
				}

				// If we are about to set an avatar, we will not overwrite user avatars if no group avatar is set...
				if (strpos($attribute, 'group_avatar') === 0 && !$group_attributes[$attribute])
				{
					continue;
				}

				$sql_ary[$attribute] = $group_attributes[$attribute];
			}
		}

		if (count($sql_ary) && count($user_ary))
		{
			group_set_user_default($group_id, $user_ary, $sql_ary);
		}

		$name = $group_helper->get_name($name);
		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, $log, false, array($name));

		group_update_listings($group_id);
	}

	return (count($error)) ? $error : false;
}


/**
* Changes a group avatar's filename to conform to the naming scheme
*/
function group_correct_avatar($group_id, $old_entry)
{
	global $board_config, $db, $phpbb_container;

	$storage = $phpbb_container->get('storage.avatar');

	$group_id		= (int) $group_id;
	$ext 			= substr(strrchr($old_entry, '.'), 1);
	$old_filename 	= get_avatar_filename($old_entry);
	$new_filename 	= $board_config['avatar_salt'] . "_g$group_id.$ext";
	$new_entry 		= 'g' . $group_id . '_' . substr(time(), -5) . ".$ext";

	try
	{
		$this->storage->rename($old_filename, $new_filename);

		$sql = 'UPDATE ' . GROUPS_TABLE . '
			SET group_avatar = \'' . $db->sql_escape($new_entry) . "'
			WHERE group_id = $group_id";
		$db->sql_query($sql);
	}
	catch (\phpbb\storage\exception\exception $e)
	{
		// If rename fail, dont execute the query
	}
}


/**
* Remove avatar also for users not having the group as default
*/
function avatar_remove_db($avatar_name)
{
	global $db;

	$sql = 'UPDATE ' . USERS_TABLE . "
		SET user_avatar = '',
		user_avatar_type = ''
		WHERE user_avatar = '" . $db->sql_escape($avatar_name) . '\'';
	$db->sql_query($sql);
}


/**
* Group Delete
*/
function group_delete($group_id, $group_name = false)
{
	global $db, $cache, $auth, $user, $phpbb_root_path, $phpEx, $phpbb_dispatcher, $phpbb_container, $phpbb_log;

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

	// Delete group from legend and teampage
	try
	{
		/* @var $legend \phpbb\groupposition\legend */
		$legend = $phpbb_container->get('groupposition.legend');
		$legend->delete_group($group_id);
		unset($legend);
	}
	catch (\phpbb\groupposition\exception $exception)
	{
		// The group we want to delete does not exist.
		// No reason to worry, we just continue the deleting process.
		//trigger_error($user->lang($exception->getMessage()));
	}

	try
	{
		/* @var $teampage \phpbb\groupposition\teampage */
		$teampage = $phpbb_container->get('groupposition.teampage');
		$teampage->delete_group($group_id);
		unset($teampage);
	}
	catch (\phpbb\groupposition\exception $exception)
	{
		// The group we want to delete does not exist.
		// No reason to worry, we just continue the deleting process.
		//trigger_error($user->lang($exception->getMessage()));
	}

	// Delete group
	$sql = 'DELETE FROM ' . GROUPS_TABLE . "
		WHERE group_id = $group_id";
	$db->sql_query($sql);

	// Delete auth entries from the groups table
	$sql = 'DELETE FROM ' . ACL_GROUPS_TABLE . "
		WHERE group_id = $group_id";
	$db->sql_query($sql);

	// Re-cache moderators
	if (!function_exists('phpbb_cache_moderators'))
	{
		include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
	}

	phpbb_cache_moderators($db, $cache, $auth);

	$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_GROUP_DELETE', false, array($group_name));

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
	global $db, $auth, $user, $phpbb_container, $phpbb_log, $phpbb_dispatcher;

	// We need both username and user_id info
	$result = user_get_id_name($user_id_ary, $username_ary);

	if (empty($user_id_ary) || $result !== false)
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
	if (!count($add_id_ary) && !count($update_id_ary))
	{
		return 'GROUP_USERS_EXIST';
	}

	$db->sql_transaction('begin');

	// Insert the new users
	if (count($add_id_ary))
	{
		$sql_ary = array();

		foreach ($add_id_ary as $user_id)
		{
			$sql_ary[] = array(
				'user_id'		=> (int) $user_id,
				'group_id'		=> (int) $group_id,
				'group_leader'	=> (int) $leader,
				'user_pending'	=> (int) $pending,
			);
		}

		$db->sql_multi_insert(USER_GROUP_TABLE, $sql_ary);
	}

	if (count($update_id_ary))
	{
		$sql = 'UPDATE ' . USER_GROUP_TABLE . '
			SET group_leader = 1
			WHERE ' . $db->sql_in_set('user_id', $update_id_ary) . "
				AND group_id = $group_id";
		$db->sql_query($sql);
	}

	if ($default)
	{
		group_user_attributes('default', $group_id, $user_id_ary, false, $group_name, $group_attributes);
	}

	$db->sql_transaction('commit');

	// Clear permissions cache of relevant users
	$auth->acl_clear_prefetch($user_id_ary);

	/**
	* Event after users are added to a group
	*
	* @event core.group_add_user_after
	* @var	int	group_id		ID of the group to which users are added
	* @var	string group_name		Name of the group
	* @var	array	user_id_ary		IDs of the users which are added
	* @var	array	username_ary	names of the users which are added
	* @var	int		pending			Pending setting, 1 if user(s) added are pending
	* @since 3.1.7-RC1
	*/
	$vars = array(
		'group_id',
		'group_name',
		'user_id_ary',
		'username_ary',
		'pending',
	);
	extract($phpbb_dispatcher->trigger_event('core.group_add_user_after', compact($vars)));

	if (!$group_name)
	{
		$group_name = get_group_name($group_id);
	}

	$log = ($leader) ? 'LOG_MODS_ADDED' : (($pending) ? 'LOG_USERS_PENDING' : 'LOG_USERS_ADDED');

	$phpbb_log->add('admin', $user->data['user_id'], $user->ip, $log, false, array($group_name, implode(', ', $username_ary)));

	group_update_listings($group_id);

	if ($pending)
	{
		/* @var $phpbb_notifications \phpbb\notification\manager */
		$phpbb_notifications = $phpbb_container->get('notification_manager');

		foreach ($add_id_ary as $user_id)
		{
			$phpbb_notifications->add_notifications('notification.type.group_request', array(
				'group_id'		=> $group_id,
				'user_id'		=> $user_id,
				'group_name'	=> $group_name,
			));
		}
	}

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
function group_user_del($group_id, $user_id_ary = false, $username_ary = false, $group_name = false, $log_action = true)
{
	global $db, $auth, $board_config, $user, $phpbb_dispatcher, $phpbb_container, $phpbb_log;

	if ($board_config['coppa_enable'])
	{
		$group_order = array('ADMINISTRATORS', 'GLOBAL_MODERATORS', 'NEWLY_REGISTERED', 'REGISTERED_COPPA', 'REGISTERED', 'BOTS', 'GUESTS');
	}
	else
	{
		$group_order = array('ADMINISTRATORS', 'GLOBAL_MODERATORS', 'NEWLY_REGISTERED', 'REGISTERED', 'BOTS', 'GUESTS');
	}

	// We need both username and user_id info
	$result = user_get_id_name($user_id_ary, $username_ary);

	if (empty($user_id_ary) || $result !== false)
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
		if ($default_groups[$row['user_id']] == $group_id && (!isset($temp_ary[$row['user_id']]) || $group_order_id[$row['group_name']] < $temp_ary[$row['user_id']]))
		{
			$temp_ary[$row['user_id']] = $row['group_id'];
		}
	}
	$db->sql_freeresult($result);

	// sql_where_ary holds the new default groups and their users
	$sql_where_ary = array();
	foreach ($temp_ary as $uid => $gid)
	{
		$sql_where_ary[$gid][] = $uid;
	}
	unset($temp_ary);

	foreach ($special_group_data as $gid => $default_data_ary)
	{
		if (isset($sql_where_ary[$gid]) && count($sql_where_ary[$gid]))
		{
			remove_default_rank($group_id, $sql_where_ary[$gid]);
			remove_default_avatar($group_id, $sql_where_ary[$gid]);
			group_set_user_default($gid, $sql_where_ary[$gid], $default_data_ary);
		}
	}
	unset($special_group_data);

	/**
	* Event before users are removed from a group
	*
	* @event core.group_delete_user_before
	* @var	int		group_id		ID of the group from which users are deleted
	* @var	string	group_name		Name of the group
	* @var	array	user_id_ary		IDs of the users which are removed
	* @var	array	username_ary	names of the users which are removed
	* @since 3.1.0-a1
	*/
	$vars = array('group_id', 'group_name', 'user_id_ary', 'username_ary');
	extract($phpbb_dispatcher->trigger_event('core.group_delete_user_before', compact($vars)));

	$sql = 'DELETE FROM ' . USER_GROUP_TABLE . "
		WHERE group_id = $group_id
			AND " . $db->sql_in_set('user_id', $user_id_ary);
	$db->sql_query($sql);

	// Clear permissions cache of relevant users
	$auth->acl_clear_prefetch($user_id_ary);

	/**
	* Event after users are removed from a group
	*
	* @event core.group_delete_user_after
	* @var	int		group_id		ID of the group from which users are deleted
	* @var	string	group_name		Name of the group
	* @var	array	user_id_ary		IDs of the users which are removed
	* @var	array	username_ary	names of the users which are removed
	* @since 3.1.7-RC1
	*/
	$vars = array('group_id', 'group_name', 'user_id_ary', 'username_ary');
	extract($phpbb_dispatcher->trigger_event('core.group_delete_user_after', compact($vars)));

	if ($log_action)
	{
		if (!$group_name)
		{
			$group_name = get_group_name($group_id);
		}

		$log = 'LOG_GROUP_REMOVE';

		if ($group_name)
		{
			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, $log, false, array($group_name, implode(', ', $username_ary)));
		}
	}

	group_update_listings($group_id);

	/* @var $phpbb_notifications \phpbb\notification\manager */
	$phpbb_notifications = $phpbb_container->get('notification_manager');

	$phpbb_notifications->delete_notifications('notification.type.group_request', $user_id_ary, $group_id);

	// Return false - no error
	return false;
}


/**
* Removes the group avatar of the default group from the users in user_ids who have that group as default.
*/
function remove_default_avatar($group_id, $user_ids)
{
	global $db;

	if (!is_array($user_ids))
	{
		$user_ids = array($user_ids);
	}
	if (empty($user_ids))
	{
		return false;
	}

	$user_ids = array_map('intval', $user_ids);

	$sql = 'SELECT *
		FROM ' . GROUPS_TABLE . '
		WHERE group_id = ' . (int) $group_id;
	$result = $db->sql_query($sql);
	if (!$row = $db->sql_fetchrow($result))
	{
		$db->sql_freeresult($result);
		return false;
	}
	$db->sql_freeresult($result);

	$sql = 'UPDATE ' . USERS_TABLE . "
		SET user_avatar = '',
			user_avatar_type = '',
			user_avatar_width = 0,
			user_avatar_height = 0
		WHERE group_id = " . (int) $group_id . "
			AND user_avatar = '" . $db->sql_escape($row['group_avatar']) . "'
			AND " . $db->sql_in_set('user_id', $user_ids);

	$db->sql_query($sql);
}

/**
* Removes the group rank of the default group from the users in user_ids who have that group as default.
*/
function remove_default_rank($group_id, $user_ids)
{
	global $db;

	if (!is_array($user_ids))
	{
		$user_ids = array($user_ids);
	}
	if (empty($user_ids))
	{
		return false;
	}

	$user_ids = array_map('intval', $user_ids);

	$sql = 'SELECT *
		FROM ' . GROUPS_TABLE . '
		WHERE group_id = ' . (int) $group_id;
	$result = $db->sql_query($sql);
	if (!$row = $db->sql_fetchrow($result))
	{
		$db->sql_freeresult($result);
		return false;
	}
	$db->sql_freeresult($result);

	$sql = 'UPDATE ' . USERS_TABLE . '
		SET user_rank = 0
		WHERE group_id = ' . (int) $group_id . '
			AND user_rank <> 0
			AND user_rank = ' . (int) $row['group_rank'] . '
			AND ' . $db->sql_in_set('user_id', $user_ids);
	$db->sql_query($sql);
}

/**
* This is used to promote (to leader), demote or set as default a member/s
*/
function group_user_attributes($action, $group_id, $user_id_ary = false, $username_ary = false, $group_name = false, $group_attributes = false)
{
	global $db, $auth, $user, $phpbb_container, $phpbb_log, $phpbb_dispatcher;

	// We need both username and user_id info
	$result = user_get_id_name($user_id_ary, $username_ary);

	if (empty($user_id_ary) || $result !== false)
	{
		return 'NO_USERS';
	}

	if (!$group_name)
	{
		$group_name = get_group_name($group_id);
	}

	switch ($action)
	{
		case 'demote':
		case 'promote':

			$sql = 'SELECT user_id
				FROM ' . USER_GROUP_TABLE . "
				WHERE group_id = $group_id
					AND user_pending = 1
					AND " . $db->sql_in_set('user_id', $user_id_ary);
			$result = $db->sql_query_limit($sql, 1);
			$not_empty = ($db->sql_fetchrow($result));
			$db->sql_freeresult($result);
			if ($not_empty)
			{
				return 'NO_VALID_USERS';
			}

			$sql = 'UPDATE ' . USER_GROUP_TABLE . '
				SET group_leader = ' . (($action == 'promote') ? 1 : 0) . "
				WHERE group_id = $group_id
					AND user_pending = 0
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

			$user_id_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$user_id_ary[] = $row['user_id'];
			}
			$db->sql_freeresult($result);

			if (!count($user_id_ary))
			{
				return false;
			}

			$sql = 'UPDATE ' . USER_GROUP_TABLE . "
				SET user_pending = 0
				WHERE group_id = $group_id
					AND " . $db->sql_in_set('user_id', $user_id_ary);
			$db->sql_query($sql);

			$log = 'LOG_USERS_APPROVED';
		break;

		case 'default':
			// We only set default group for approved members of the group
			$sql = 'SELECT user_id
				FROM ' . USER_GROUP_TABLE . "
				WHERE group_id = $group_id
					AND user_pending = 0
					AND " . $db->sql_in_set('user_id', $user_id_ary);
			$result = $db->sql_query($sql);

			$user_id_ary = $username_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$user_id_ary[] = $row['user_id'];
			}
			$db->sql_freeresult($result);

			$result = user_get_id_name($user_id_ary, $username_ary);
			if (!count($user_id_ary) || $result !== false)
			{
				return 'NO_USERS';
			}

			$sql = 'SELECT user_id, group_id
				FROM ' . USERS_TABLE . '
				WHERE ' . $db->sql_in_set('user_id', $user_id_ary, false, true);
			$result = $db->sql_query($sql);

			$groups = array();
			while ($row = $db->sql_fetchrow($result))
			{
				if (!isset($groups[$row['group_id']]))
				{
					$groups[$row['group_id']] = array();
				}
				$groups[$row['group_id']][] = $row['user_id'];
			}
			$db->sql_freeresult($result);

			foreach ($groups as $gid => $uids)
			{
				remove_default_rank($gid, $uids);
				remove_default_avatar($gid, $uids);
			}
			group_set_user_default($group_id, $user_id_ary, $group_attributes);
			$log = 'LOG_GROUP_DEFAULTS';
		break;
	}

	// Clear permissions cache of relevant users
	$auth->acl_clear_prefetch($user_id_ary);

	$phpbb_log->add('admin', $user->data['user_id'], $user->ip, $log, false, array($group_name, implode(', ', $username_ary)));

	group_update_listings($group_id);

	return false;
}

/**
* A small version of validate_username to check for a group name's existence. To be called directly.
*/
function group_validate_groupname($group_id, $group_name)
{
	global $db;

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
* Clear user color cache.
*
* @param => user_id
* @return => true on success
*/
function clear_user_color_cache($user_id)
{
	global $phpEx;
	$dir = ((@file_exists(USERS_CACHE_FOLDER)) ? USERS_CACHE_FOLDER : @phpbb_realpath(USERS_CACHE_FOLDER));
	@unlink($dir . 'sql_' . POST_USERS_URL . '_' . md5(user_color_sql($user_id)) . '.' . $phpEx);
	return true;
}

/**
* Set users default group
*
* @access private
*/
function group_set_user_default($group_id, $user_id_ary, $group_attributes = false, $update_listing = false)
{
	global $board_config, $phpbb_container, $db, $phpbb_dispatcher;
	$cache = new cache();
	if (empty($user_id_ary))
	{
		return;
	}

	$attribute_ary = array(
		'group_colour'			=> 'string',
		'group_rank'			=> 'int',
		'group_avatar'			=> 'string',
		'group_avatar_type'		=> 'string',
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
			// If we are about to set an avatar or rank, we will not overwrite with empty, unless we are not actually changing the default group
			if ((strpos($attribute, 'group_avatar') === 0 || strpos($attribute, 'group_rank') === 0) && !$group_attributes[$attribute])
			{
				continue;
			}

			settype($group_attributes[$attribute], $type);
			$sql_ary[str_replace('group_', 'user_', $attribute)] = $group_attributes[$attribute];
		}
	}

	$updated_sql_ary = $sql_ary;

	// Before we update the user attributes, we will update the rank for users that don't have a custom rank
	if (isset($sql_ary['user_rank']))
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', array('user_rank' => $sql_ary['user_rank'])) . '
			WHERE user_rank = 0
				AND ' . $db->sql_in_set('user_id', $user_id_ary);
		$db->sql_query($sql);
		unset($sql_ary['user_rank']);
	}

	// Before we update the user attributes, we will update the avatar for users that don't have a custom avatar
	$avatar_options = array('user_avatar', 'user_avatar_type', 'user_avatar_height', 'user_avatar_width');

	if (isset($sql_ary['user_avatar']))
	{
		$avatar_sql_ary = array();
		foreach ($avatar_options as $avatar_option)
		{
			if (isset($sql_ary[$avatar_option]))
			{
				$avatar_sql_ary[$avatar_option] = $sql_ary[$avatar_option];
			}
		}

		$sql = 'UPDATE ' . USERS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $avatar_sql_ary) . "
			WHERE user_avatar = ''
				AND " . $db->sql_in_set('user_id', $user_id_ary);
		$db->sql_query($sql);
	}

	// Remove the avatar options, as we already updated them
	foreach ($avatar_options as $avatar_option)
	{
		unset($sql_ary[$avatar_option]);
	}

	if (!empty($sql_ary))
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE ' . $db->sql_in_set('user_id', $user_id_ary);
		$db->sql_query($sql);
	}

	if (isset($sql_ary['user_colour']))
	{
		// Update any cached colour information for these users
		$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET forum_last_poster_colour = '" . $db->sql_escape($sql_ary['user_colour']) . "'
			WHERE " . $db->sql_in_set('forum_last_poster_id', $user_id_ary);
		$db->sql_query($sql);

		$sql = 'UPDATE ' . TOPICS_TABLE . "
			SET topic_first_poster_colour = '" . $db->sql_escape($sql_ary['user_colour']) . "'
			WHERE " . $db->sql_in_set('topic_poster', $user_id_ary);
		$db->sql_query($sql);

		$sql = 'UPDATE ' . TOPICS_TABLE . "
			SET topic_last_poster_colour = '" . $db->sql_escape($sql_ary['user_colour']) . "'
			WHERE " . $db->sql_in_set('topic_last_poster_id', $user_id_ary);
		$db->sql_query($sql);

		if (in_array($board_config['newest_user_id'], $user_id_ary))
		{
			$board_config->set('newest_user_colour', $sql_ary['user_colour'], false);
		}
	}

	// Make all values available for the event
	$sql_ary = $updated_sql_ary;

	if ($update_listing)
	{
		group_update_listings($group_id);
	}

	// Because some tables/caches use usercolour-specific data we need to purge this here.
	$cache->get('cache.driver');
	$cache->destroy('sql', MODERATOR_CACHE_TABLE);
}

/**
* Get group name
*/
function get_group_name($group_id)
{
	global $db, $phpbb_container;

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

	/** @var \phpbb\group\helper $group_helper */
	$group_helper = $phpbb_container->get('group_helper');

	return $group_helper->get_name($row['group_name']);
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
	global $db, $cache, $auth;

	$hold_ary = $auth->acl_group_raw_data($group_id, array('a_', 'm_'));

	if (empty($hold_ary))
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
		if (!function_exists('phpbb_cache_moderators'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		}
		phpbb_cache_moderators($db, $cache, $auth);
	}

	if ($mod_permissions || $admin_permissions)
	{
		if (!function_exists('phpbb_update_foes'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		}
		phpbb_update_foes($db, $auth, array($group_id));
	}
}



/**
* Funtion to make a user leave the NEWLY_REGISTERED system group.
* @access public
* @param $user_id The id of the user to remove from the group
*/
function remove_newly_registered($user_id, $user_data = false)
{
	global $db;

	if ($user_data === false)
	{
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
		else
		{
			$user_data  = $user_row;
		}
	}

	if (empty($user_data['user_new']))
	{
		return false;
	}

	$sql = 'SELECT group_id
		FROM ' . GROUPS_TABLE . "
		WHERE group_name = 'NEWLY_REGISTERED'
			AND group_type = " . GROUP_SPECIAL;
	$result = $db->sql_query($sql);
	$group_id = (int) $db->sql_fetchfield('group_id');
	$db->sql_freeresult($result);

	if (!$group_id)
	{
		return false;
	}

	// We need to call group_user_del here, because this function makes sure everything is correctly changed.
	// Force function to not log the removal of users from newly registered users group
	group_user_del($group_id, $user_id, false, false, false);

	// Set user_new to 0 to let this not be triggered again
	$sql = 'UPDATE ' . USERS_TABLE . '
		SET user_new = 0
		WHERE user_id = ' . $user_id;
	$db->sql_query($sql);

	// The new users group was the users default group?
	if ($user_data['group_id'] == $group_id)
	{
		// Which group is now the users default one?
		$sql = 'SELECT group_id
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . $user_id;
		$result = $db->sql_query($sql);
		$user_data['group_id'] = $db->sql_fetchfield('group_id');
		$db->sql_freeresult($result);
	}

	return $user_data['group_id'];
}

/**
* Gets user ids of currently banned registered users.
*
* @param array $user_ids Array of users' ids to check for banning,
*						leave empty to get complete list of banned ids
* @param bool|int $ban_end Bool True to get users currently banned
* 						Bool False to only get permanently banned users
* 						Int Unix timestamp to get users banned until that time
* @return array	Array of banned users' ids if any, empty array otherwise
*/
function phpbb_get_banned_user_ids($user_ids = array(), $ban_end = true)
{
	global $db;

	$sql_user_ids = (!empty($user_ids)) ? $db->sql_in_set('ban_userid', $user_ids) : 'ban_userid <> 0';

	// Get banned User ID's
	// Ignore stale bans which were not wiped yet
	$banned_ids_list = array();
	$sql = 'SELECT ban_userid
		FROM ' . BANLIST_TABLE . "
		WHERE $sql_user_ids
			AND ban_exclude <> 1";

	if ($ban_end === true)
	{
		// Banned currently
		$sql .= " AND (ban_end > " . time() . '
				OR ban_end = 0)';
	}
	else if ($ban_end === false)
	{
		// Permanently banned
		$sql .= " AND ban_end = 0";
	}
	else
	{
		// Banned until a specified time
		$sql .= " AND (ban_end > " . (int) $ban_end . '
				OR ban_end = 0)';
	}

	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$user_id = (int) $row['ban_userid'];
		$banned_ids_list[$user_id] = $user_id;
	}
	$db->sql_freeresult($result);

	return $banned_ids_list;
}

/**
* Function for assigning a template var if the zebra module got included
*/
function phpbb_module_zebra($mode, &$module_row)
{
	global $template;

	$template->assign_var('S_ZEBRA_ENABLED', true);

	if ($mode == 'friends')
	{
		$template->assign_var('S_ZEBRA_FRIENDS_ENABLED', true);
	}

	if ($mode == 'foes')
	{
		$template->assign_var('S_ZEBRA_FOES_ENABLED', true);
	}
}
