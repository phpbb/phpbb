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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* mcp_warn
* Handling warning the users
*/
class mcp_warn
{
	var $p_master;
	var $u_action;

	function mcp_warn(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($id, $mode)
	{
		global $request;

		$action = $request->variable('action', array('' => ''));

		if (is_array($action))
		{
			list($action, ) = each($action);
		}

		$this->page_title = 'MCP_WARN';

		add_form_key('mcp_warn');

		switch ($mode)
		{
			case 'front':
				$this->mcp_warn_front_view();
				$this->tpl_name = 'mcp_warn_front';
			break;

			case 'list':
				$this->mcp_warn_list_view($action);
				$this->tpl_name = 'mcp_warn_list';
			break;

			case 'warn_post':
				$this->mcp_warn_post_view($action);
				$this->tpl_name = 'mcp_warn_post';
			break;

			case 'warn_user':
				$this->mcp_warn_user_view($action);
				$this->tpl_name = 'mcp_warn_user';
			break;
		}
	}

	/**
	* Generates the summary on the main page of the warning module
	*/
	function mcp_warn_front_view()
	{
		global $phpEx, $phpbb_root_path;
		global $template, $db, $user;

		$template->assign_vars(array(
			'U_FIND_USERNAME'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=mcp&amp;field=username&amp;select_single=true'),
			'U_POST_ACTION'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=warn&amp;mode=warn_user'),
		));

		// Obtain a list of the 5 naughtiest users....
		// These are the 5 users with the highest warning count
		$highest = array();
		$count = 0;

		view_warned_users($highest, $count, 5);

		foreach ($highest as $row)
		{
			$template->assign_block_vars('highest', array(
				'U_NOTES'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $row['user_id']),

				'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),

				'WARNING_TIME'	=> $user->format_date($row['user_last_warning']),
				'WARNINGS'		=> $row['user_warnings'],
			));
		}

		// And now the 5 most recent users to get in trouble
		$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_colour, u.user_warnings, w.warning_time
			FROM ' . USERS_TABLE . ' u, ' . WARNINGS_TABLE . ' w
			WHERE u.user_id = w.user_id
			ORDER BY w.warning_time DESC';
		$result = $db->sql_query_limit($sql, 5);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('latest', array(
				'U_NOTES'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $row['user_id']),

				'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),

				'WARNING_TIME'	=> $user->format_date($row['warning_time']),
				'WARNINGS'		=> $row['user_warnings'],
			));
		}
		$db->sql_freeresult($result);
	}

	/**
	* Lists all users with warnings
	*/
	function mcp_warn_list_view($action)
	{
		global $phpEx, $phpbb_root_path, $config, $phpbb_container;
		global $template, $user, $auth, $request;

		/* @var $pagination \phpbb\pagination */
		$pagination = $phpbb_container->get('pagination');
		$user->add_lang('memberlist');

		$start	= $request->variable('start', 0);
		$st		= $request->variable('st', 0);
		$sk		= $request->variable('sk', 'b');
		$sd		= $request->variable('sd', 'd');

		$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
		$sort_by_text = array('a' => $user->lang['SORT_USERNAME'], 'b' => $user->lang['SORT_DATE'], 'c' => $user->lang['SORT_WARNINGS']);
		$sort_by_sql = array('a' => 'username_clean', 'b' => 'user_last_warning', 'c' => 'user_warnings');

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $st, $sk, $sd, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		// Define where and sort sql for use in displaying logs
		$sql_where = ($st) ? (time() - ($st * 86400)) : 0;
		$sql_sort = $sort_by_sql[$sk] . ' ' . (($sd == 'd') ? 'DESC' : 'ASC');

		$users = array();
		$user_count = 0;

		view_warned_users($users, $user_count, $config['topics_per_page'], $start, $sql_where, $sql_sort);

		foreach ($users as $row)
		{
			$template->assign_block_vars('user', array(
				'U_NOTES'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $row['user_id']),

				'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),

				'WARNING_TIME'	=> $user->format_date($row['user_last_warning']),
				'WARNINGS'		=> $row['user_warnings'],
			));
		}

		$base_url = append_sid("{$phpbb_root_path}mcp.$phpEx", "i=warn&amp;mode=list&amp;st=$st&amp;sk=$sk&amp;sd=$sd");
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $user_count, $config['topics_per_page'], $start);

		$template->assign_vars(array(
			'U_POST_ACTION'			=> $this->u_action,
			'S_CLEAR_ALLOWED'		=> ($auth->acl_get('a_clearlogs')) ? true : false,
			'S_SELECT_SORT_DIR'		=> $s_sort_dir,
			'S_SELECT_SORT_KEY'		=> $s_sort_key,
			'S_SELECT_SORT_DAYS'	=> $s_limit_days,

			'TOTAL_USERS'		=> $user->lang('LIST_USERS', (int) $user_count),
		));
	}

	/**
	* Handles warning the user when the warning is for a specific post
	*/
	function mcp_warn_post_view($action)
	{
		global $phpEx, $phpbb_root_path, $config, $request;
		global $template, $db, $user, $phpbb_dispatcher;

		$post_id = $request->variable('p', 0);
		$forum_id = $request->variable('f', 0);
		$notify = (isset($_REQUEST['notify_user'])) ? true : false;
		$warning = $request->variable('warning', '', true);

		$sql = 'SELECT u.*, p.*
			FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
			WHERE p.post_id = $post_id
				AND u.user_id = p.poster_id";
		$result = $db->sql_query($sql);
		$user_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$user_row)
		{
			trigger_error('NO_POST');
		}

		// There is no point issuing a warning to ignored users (ie anonymous and bots)
		if ($user_row['user_type'] == USER_IGNORE)
		{
			trigger_error('CANNOT_WARN_ANONYMOUS');
		}

		// Prevent someone from warning themselves
		if ($user_row['user_id'] == $user->data['user_id'])
		{
			trigger_error('CANNOT_WARN_SELF');
		}

		// Check if there is already a warning for this post to prevent multiple
		// warnings for the same offence
		$sql = 'SELECT post_id
			FROM ' . WARNINGS_TABLE . "
			WHERE post_id = $post_id";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			trigger_error('ALREADY_WARNED');
		}

		$user_id = $user_row['user_id'];

		if (strpos($this->u_action, "&amp;f=$forum_id&amp;p=$post_id") === false)
		{
			$this->p_master->adjust_url("&amp;f=$forum_id&amp;p=$post_id");
			$this->u_action .= "&amp;f=$forum_id&amp;p=$post_id";
		}

		// Check if can send a notification
		if ($config['allow_privmsg'])
		{
			$auth2 = new \phpbb\auth\auth();
			$auth2->acl($user_row);
			$s_can_notify = ($auth2->acl_get('u_readpm')) ? true : false;
			unset($auth2);
		}
		else
		{
			$s_can_notify = false;
		}

		// Prevent against clever people
		if ($notify && !$s_can_notify)
		{
			$notify = false;
		}

		if ($warning && $action == 'add_warning')
		{
			if (check_form_key('mcp_warn'))
			{
				$s_mcp_warn_post = true;

				/**
				* Event for before warning a user for a post.
				*
				* @event core.mcp_warn_post_before
				* @var array	user_row		The entire user row
				* @var string	warning			The warning message
				* @var bool		notify			If true, we notify the user for the warning
				* @var int		post_id			The post id for which the warning is added
				* @var bool		s_mcp_warn_post If true, we add the warning else we omit it
				* @since 3.1.0-b4
				*/
				$vars = array(
						'user_row',
						'warning',
						'notify',
						'post_id',
						's_mcp_warn_post',
				);
				extract($phpbb_dispatcher->trigger_event('core.mcp_warn_post_before', compact($vars)));

				if ($s_mcp_warn_post)
				{
					add_warning($user_row, $warning, $notify, $post_id);
					$message = $user->lang['USER_WARNING_ADDED'];

					/**
					* Event for after warning a user for a post.
					*
					* @event core.mcp_warn_post_after
					* @var array	user_row	The entire user row
					* @var string	warning		The warning message
					* @var bool		notify		If true, the user was notified for the warning
					* @var int		post_id		The post id for which the warning is added
					* @var string	message		Message displayed to the moderator
					* @since 3.1.0-b4
					*/
					$vars = array(
							'user_row',
							'warning',
							'notify',
							'post_id',
							'message',
					);
					extract($phpbb_dispatcher->trigger_event('core.mcp_warn_post_after', compact($vars)));
				}
			}
			else
			{
				$message = $user->lang['FORM_INVALID'];
			}

			if (!empty($message))
			{
				$redirect = append_sid("{$phpbb_root_path}mcp.$phpEx", "i=notes&amp;mode=user_notes&amp;u=$user_id");
				meta_refresh(2, $redirect);
				trigger_error($message . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>'));
			}
		}

		// OK, they didn't submit a warning so lets build the page for them to do so

		// We want to make the message available here as a reminder
		// Parse the message and subject
		$parse_flags = OPTION_FLAG_SMILIES | ($user_row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0);
		$message = generate_text_for_display($user_row['post_text'], $user_row['bbcode_uid'], $user_row['bbcode_bitfield'], $parse_flags, true);

		// Generate the appropriate user information for the user we are looking at
		if (!function_exists('phpbb_get_user_rank'))
		{
			include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		}

		$user_rank_data = phpbb_get_user_rank($user_row, $user_row['user_posts']);
		$avatar_img = phpbb_get_user_avatar($user_row);

		$template->assign_vars(array(
			'U_POST_ACTION'		=> $this->u_action,

			'POST'				=> $message,
			'USERNAME'			=> $user_row['username'],
			'USER_COLOR'		=> (!empty($user_row['user_colour'])) ? $user_row['user_colour'] : '',
			'RANK_TITLE'		=> $user_rank_data['title'],
			'JOINED'			=> $user->format_date($user_row['user_regdate']),
			'POSTS'				=> ($user_row['user_posts']) ? $user_row['user_posts'] : 0,
			'WARNINGS'			=> ($user_row['user_warnings']) ? $user_row['user_warnings'] : 0,

			'AVATAR_IMG'		=> $avatar_img,
			'RANK_IMG'			=> $user_rank_data['img'],

			'L_WARNING_POST_DEFAULT'	=> sprintf($user->lang['WARNING_POST_DEFAULT'], generate_board_url() . "/viewtopic.$phpEx?f=$forum_id&amp;p=$post_id#p$post_id"),

			'S_CAN_NOTIFY'		=> $s_can_notify,
		));
	}

	/**
	* Handles warning the user
	*/
	function mcp_warn_user_view($action)
	{
		global $phpEx, $phpbb_root_path, $config, $request;
		global $template, $db, $user, $phpbb_dispatcher;

		$user_id = $request->variable('u', 0);
		$username = $request->variable('username', '', true);
		$notify = (isset($_REQUEST['notify_user'])) ? true : false;
		$warning = $request->variable('warning', '', true);

		$sql_where = ($user_id) ? "user_id = $user_id" : "username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";

		$sql = 'SELECT *
			FROM ' . USERS_TABLE . '
			WHERE ' . $sql_where;
		$result = $db->sql_query($sql);
		$user_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$user_row)
		{
			trigger_error('NO_USER');
		}

		// Prevent someone from warning themselves
		if ($user_row['user_id'] == $user->data['user_id'])
		{
			trigger_error('CANNOT_WARN_SELF');
		}

		$user_id = $user_row['user_id'];

		if (strpos($this->u_action, "&amp;u=$user_id") === false)
		{
			$this->p_master->adjust_url('&amp;u=' . $user_id);
			$this->u_action .= "&amp;u=$user_id";
		}

		// Check if can send a notification
		if ($config['allow_privmsg'])
		{
			$auth2 = new \phpbb\auth\auth();
			$auth2->acl($user_row);
			$s_can_notify = ($auth2->acl_get('u_readpm')) ? true : false;
			unset($auth2);
		}
		else
		{
			$s_can_notify = false;
		}

		// Prevent against clever people
		if ($notify && !$s_can_notify)
		{
			$notify = false;
		}

		if ($warning && $action == 'add_warning')
		{
			if (check_form_key('mcp_warn'))
			{
				$s_mcp_warn_user = true;

				/**
				* Event for before warning a user from MCP.
				*
				* @event core.mcp_warn_user_before
				* @var array	user_row		The entire user row
				* @var string	warning			The warning message
				* @var bool		notify			If true, we notify the user for the warning
				* @var bool		s_mcp_warn_user If true, we add the warning else we omit it
				* @since 3.1.0-b4
				*/
				$vars = array(
						'user_row',
						'warning',
						'notify',
						's_mcp_warn_user',
				);
				extract($phpbb_dispatcher->trigger_event('core.mcp_warn_user_before', compact($vars)));

				if ($s_mcp_warn_user)
				{
					add_warning($user_row, $warning, $notify);
					$message = $user->lang['USER_WARNING_ADDED'];

					/**
					* Event for after warning a user from MCP.
					*
					* @event core.mcp_warn_user_after
					* @var array	user_row	The entire user row
					* @var string	warning		The warning message
					* @var bool		notify		If true, the user was notified for the warning
					* @var string	message		Message displayed to the moderator
					* @since 3.1.0-b4
					*/
					$vars = array(
							'user_row',
							'warning',
							'notify',
							'message',
					);
					extract($phpbb_dispatcher->trigger_event('core.mcp_warn_user_after', compact($vars)));
				}
			}
			else
			{
				$message = $user->lang['FORM_INVALID'];
			}

			if (!empty($message))
			{
				$redirect = append_sid("{$phpbb_root_path}mcp.$phpEx", "i=notes&amp;mode=user_notes&amp;u=$user_id");
				meta_refresh(2, $redirect);
				trigger_error($message . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>'));
			}
		}

		// Generate the appropriate user information for the user we are looking at
		if (!function_exists('phpbb_get_user_rank'))
		{
			include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		}
		$user_rank_data = phpbb_get_user_rank($user_row, $user_row['user_posts']);
		$avatar_img = phpbb_get_user_avatar($user_row);

		// OK, they didn't submit a warning so lets build the page for them to do so
		$template->assign_vars(array(
			'U_POST_ACTION'		=> $this->u_action,

			'RANK_TITLE'		=> $user_rank_data['title'],
			'JOINED'			=> $user->format_date($user_row['user_regdate']),
			'POSTS'				=> ($user_row['user_posts']) ? $user_row['user_posts'] : 0,
			'WARNINGS'			=> ($user_row['user_warnings']) ? $user_row['user_warnings'] : 0,

			'USERNAME_FULL'		=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
			'USERNAME_COLOUR'	=> get_username_string('colour', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
			'USERNAME'			=> get_username_string('username', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
			'U_PROFILE'			=> get_username_string('profile', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),

			'AVATAR_IMG'		=> $avatar_img,
			'RANK_IMG'			=> $user_rank_data['img'],

			'S_CAN_NOTIFY'		=> $s_can_notify,
		));

		return $user_id;
	}
}

/**
* Insert the warning into the database
*/
function add_warning($user_row, $warning, $send_pm = true, $post_id = 0)
{
	global $phpEx, $phpbb_root_path, $config, $phpbb_log;
	global $db, $user;

	if ($send_pm)
	{
		include_once($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
		include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

		// Attempt to translate warning to language of user being warned if user's language differs from issuer's language
		if ($user_row['user_lang'] != $user->lang_name)
		{
			$lang = array();

			$user_row['user_lang'] = (file_exists($phpbb_root_path . 'language/' . basename($user_row['user_lang']) . "/mcp." . $phpEx)) ? $user_row['user_lang'] : $config['default_lang'];
			include($phpbb_root_path . 'language/' . basename($user_row['user_lang']) . "/mcp." . $phpEx);

			$warn_pm_subject = $lang['WARNING_PM_SUBJECT'];
			$warn_pm_body = sprintf($lang['WARNING_PM_BODY'], $warning);

			unset($lang);
		}
		else
		{
			$warn_pm_subject = $user->lang('WARNING_PM_SUBJECT');
			$warn_pm_body = $user->lang('WARNING_PM_BODY', $warning);
		}

		$message_parser = new parse_message();

		$message_parser->message = $warn_pm_body;
		$message_parser->parse(true, true, true, false, false, true, true);

		$pm_data = array(
			'from_user_id'			=> $user->data['user_id'],
			'from_user_ip'			=> $user->ip,
			'from_username'			=> $user->data['username'],
			'enable_sig'			=> false,
			'enable_bbcode'			=> true,
			'enable_smilies'		=> true,
			'enable_urls'			=> false,
			'icon_id'				=> 0,
			'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
			'bbcode_uid'			=> $message_parser->bbcode_uid,
			'message'				=> $message_parser->message,
			'address_list'			=> array('u' => array($user_row['user_id'] => 'to')),
		);

		submit_pm('post', $warn_pm_subject, $pm_data, false);
	}

	$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_USER_WARNING', false, array($user_row['username']));
	$log_id = $phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LOG_USER_WARNING_BODY', false, array(
		'reportee_id' => $user_row['user_id'],
		$warning
	));

	$sql_ary = array(
		'user_id'		=> $user_row['user_id'],
		'post_id'		=> $post_id,
		'log_id'		=> $log_id,
		'warning_time'	=> time(),
	);

	$db->sql_query('INSERT INTO ' . WARNINGS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

	$sql = 'UPDATE ' . USERS_TABLE . '
		SET user_warnings = user_warnings + 1,
			user_last_warning = ' . time() . '
		WHERE user_id = ' . $user_row['user_id'];
	$db->sql_query($sql);

	// We add this to the mod log too for moderators to see that a specific user got warned.
	$sql = 'SELECT forum_id, topic_id
		FROM ' . POSTS_TABLE . '
		WHERE post_id = ' . $post_id;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_USER_WARNING', false, array(
		'forum_id' => $row['forum_id'],
		'topic_id' => $row['topic_id'],
		'post_id'  => $post_id,
		$user_row['username']
	));
}
