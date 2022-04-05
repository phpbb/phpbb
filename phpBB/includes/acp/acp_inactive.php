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

class acp_inactive
{
	var $u_action;
	var $p_master;

	function __construct($p_master)
	{
		$this->p_master = $p_master;
	}

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $phpbb_container, $phpbb_log, $request;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx;

		if (!function_exists('user_active_flip'))
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}

		$user->add_lang('memberlist');

		$action = $request->variable('action', '');
		$mark	= (isset($_REQUEST['mark'])) ? $request->variable('mark', array(0)) : array();
		$start	= $request->variable('start', 0);
		$submit = isset($_POST['submit']);

		// Sort keys
		$sort_days	= $request->variable('st', 0);
		$sort_key	= $request->variable('sk', 'i');
		$sort_dir	= $request->variable('sd', 'd');

		$form_key = 'acp_inactive';
		add_form_key($form_key);

		/* @var $pagination \phpbb\pagination */
		$pagination = $phpbb_container->get('pagination');

		// We build the sort key and per page settings here, because they may be needed later

		// Number of entries to display
		$per_page = $request->variable('users_per_page', (int) $config['topics_per_page']);

		// Sorting
		$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
		$sort_by_text = array('i' => $user->lang['SORT_INACTIVE'], 'j' => $user->lang['SORT_REG_DATE'], 'l' => $user->lang['SORT_LAST_VISIT'], 'd' => $user->lang['SORT_LAST_REMINDER'], 'r' => $user->lang['SORT_REASON'], 'u' => $user->lang['SORT_USERNAME'], 'p' => $user->lang['SORT_POSTS'], 'e' => $user->lang['SORT_REMINDER']);
		$sort_by_sql = array('i' => 'user_inactive_time', 'j' => 'user_regdate', 'l' => 'user_lastvisit', 'd' => 'user_reminded_time', 'r' => 'user_inactive_reason', 'u' => 'username_clean', 'p' => 'user_posts', 'e' => 'user_reminded');

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		if ($submit && count($mark))
		{
			if ($action !== 'delete' && !check_form_key($form_key))
			{
				trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			switch ($action)
			{
				case 'activate':
				case 'delete':

					$sql = 'SELECT user_id, username
						FROM ' . USERS_TABLE . '
						WHERE ' . $db->sql_in_set('user_id', $mark);
					$result = $db->sql_query($sql);

					$user_affected = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$user_affected[$row['user_id']] = $row['username'];
					}
					$db->sql_freeresult($result);

					if ($action == 'activate')
					{
						// Get those 'being activated'...
						$sql = 'SELECT user_id, username' . (($config['require_activation'] == USER_ACTIVATION_ADMIN) ? ', user_email, user_lang' : '') . '
							FROM ' . USERS_TABLE . '
							WHERE ' . $db->sql_in_set('user_id', $mark) . '
								AND user_type = ' . USER_INACTIVE;
						$result = $db->sql_query($sql);

						$inactive_users = array();
						while ($row = $db->sql_fetchrow($result))
						{
							$inactive_users[] = $row;
						}
						$db->sql_freeresult($result);

						user_active_flip('activate', $mark);

						if ($config['require_activation'] == USER_ACTIVATION_ADMIN && !empty($inactive_users))
						{
							if (!class_exists('messenger'))
							{
								include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
							}

							$messenger = new messenger(false);

							foreach ($inactive_users as $row)
							{
								$messenger->template('admin_welcome_activated', $row['user_lang']);

								$messenger->set_addresses($row);

								$messenger->anti_abuse_headers($config, $user);

								$messenger->assign_vars(array(
									'USERNAME'	=> html_entity_decode($row['username'], ENT_COMPAT))
								);

								$messenger->send(NOTIFY_EMAIL);
							}

							$messenger->save_queue();
						}

						if (!empty($inactive_users))
						{
							foreach ($inactive_users as $row)
							{
								$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_USER_ACTIVE', false, array($row['username']));
								$phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LOG_USER_ACTIVE_USER', false, array(
									'reportee_id' => $row['user_id']
								));
							}

							trigger_error(sprintf($user->lang['LOG_INACTIVE_ACTIVATE'], implode($user->lang['COMMA_SEPARATOR'], $user_affected) . ' ' . adm_back_link($this->u_action)));
						}

						// For activate we really need to redirect, else a refresh can result in users being deactivated again
						$u_action = $this->u_action . "&amp;$u_sort_param&amp;start=$start";
						$u_action .= ($per_page != $config['topics_per_page']) ? "&amp;users_per_page=$per_page" : '';

						redirect($u_action);
					}
					else if ($action == 'delete')
					{
						if (confirm_box(true))
						{
							if (!$auth->acl_get('a_userdel'))
							{
								send_status_line(403, 'Forbidden');
								trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
							}

							user_delete('retain', $mark, true);

							$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_INACTIVE_' . strtoupper($action), false, array(implode(', ', $user_affected)));

							trigger_error(sprintf($user->lang['LOG_INACTIVE_DELETE'], implode($user->lang['COMMA_SEPARATOR'], $user_affected) . ' ' . adm_back_link($this->u_action)));
						}
						else
						{
							$s_hidden_fields = array(
								'mode'			=> $mode,
								'action'		=> $action,
								'mark'			=> $mark,
								'submit'		=> 1,
								'start'			=> $start,
							);
							confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields($s_hidden_fields));
						}
					}

				break;

				case 'remind':
					if (empty($config['email_enable']))
					{
						trigger_error($user->lang['EMAIL_DISABLED'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type, user_regdate, user_actkey
						FROM ' . USERS_TABLE . '
						WHERE ' . $db->sql_in_set('user_id', $mark) . '
							AND user_inactive_reason';

					$sql .= ($config['require_activation'] == USER_ACTIVATION_ADMIN) ? ' = ' . INACTIVE_REMIND : ' <> ' . INACTIVE_MANUAL;

					$result = $db->sql_query($sql);

					if ($row = $db->sql_fetchrow($result))
					{
						// Send the messages
						if (!class_exists('messenger'))
						{
							include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
						}

						$messenger = new messenger();
						$usernames = $user_ids = array();

						do
						{
							$messenger->template('user_remind_inactive', $row['user_lang']);

							$messenger->set_addresses($row);

							$messenger->anti_abuse_headers($config, $user);

							$messenger->assign_vars(array(
								'USERNAME'		=> html_entity_decode($row['username'], ENT_COMPAT),
								'REGISTER_DATE'	=> $user->format_date($row['user_regdate'], false, true),
								'U_ACTIVATE'	=> generate_board_url() . "/ucp.$phpEx?mode=activate&u=" . $row['user_id'] . '&k=' . $row['user_actkey'])
							);

							$messenger->send($row['user_notify_type']);

							$usernames[] = $row['username'];
							$user_ids[] = (int) $row['user_id'];
						}
						while ($row = $db->sql_fetchrow($result));

						$messenger->save_queue();

						// Add the remind state to the database
						$sql = 'UPDATE ' . USERS_TABLE . '
							SET user_reminded = user_reminded + 1,
								user_reminded_time = ' . time() . '
							WHERE ' . $db->sql_in_set('user_id', $user_ids);
						$db->sql_query($sql);

						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_INACTIVE_REMIND', false, array(implode(', ', $usernames)));

						trigger_error(sprintf($user->lang['LOG_INACTIVE_REMIND'], implode($user->lang['COMMA_SEPARATOR'], $usernames) . ' ' . adm_back_link($this->u_action)));
					}
					$db->sql_freeresult($result);

					// For remind we really need to redirect, else a refresh can result in more than one reminder
					$u_action = $this->u_action . "&amp;$u_sort_param&amp;start=$start";
					$u_action .= ($per_page != $config['topics_per_page']) ? "&amp;users_per_page=$per_page" : '';

					redirect($u_action);

				break;
			}
		}

		// Define where and sort sql for use in displaying logs
		$sql_where = ($sort_days) ? (time() - ($sort_days * 86400)) : 0;
		$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

		$inactive = array();
		$inactive_count = 0;

		$start = view_inactive_users($inactive, $inactive_count, $per_page, $start, $sql_where, $sql_sort);

		foreach ($inactive as $row)
		{
			$template->assign_block_vars('inactive', array(
				'INACTIVE_DATE'	=> $user->format_date($row['user_inactive_time']),
				'REMINDED_DATE'	=> $user->format_date($row['user_reminded_time']),
				'JOINED'		=> $user->format_date($row['user_regdate']),
				'LAST_VISIT'	=> (!$row['user_lastvisit']) ? ' - ' : $user->format_date($row['user_lastvisit']),

				'REASON'		=> $row['inactive_reason'],
				'USER_ID'		=> $row['user_id'],
				'POSTS'			=> ($row['user_posts']) ? $row['user_posts'] : 0,
				'REMINDED'		=> $row['user_reminded'],

				'REMINDED_EXPLAIN'	=> $user->lang('USER_LAST_REMINDED', (int) $row['user_reminded'], $user->format_date($row['user_reminded_time'])),

				'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], false, append_sid("{$phpbb_admin_path}index.$phpEx", 'i=users&amp;mode=overview&amp;redirect=acp_inactive')),
				'USERNAME'			=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
				'USER_COLOR'		=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),
				'USER_EMAIL'		=> $row['user_email'],

				'U_USER_ADMIN'	=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=users&amp;mode=overview&amp;u={$row['user_id']}"),
				'U_SEARCH_USER'	=> ($auth->acl_get('u_search')) ? append_sid("{$phpbb_root_path}search.$phpEx", "author_id={$row['user_id']}&amp;sr=posts") : '',
			));
		}

		$option_ary = array('activate' => 'ACTIVATE', 'delete' => 'DELETE');
		if ($config['email_enable'])
		{
			$option_ary += array('remind' => 'REMIND');
		}

		$base_url = $this->u_action . "&amp;$u_sort_param&amp;users_per_page=$per_page";
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $inactive_count, $per_page, $start);

		$template->assign_vars(array(
			'S_INACTIVE_USERS'		=> true,
			'S_INACTIVE_OPTIONS'	=> build_select($option_ary),

			'S_LIMIT_DAYS'	=> $s_limit_days,
			'S_SORT_KEY'	=> $s_sort_key,
			'S_SORT_DIR'	=> $s_sort_dir,
			'USERS_PER_PAGE'	=> $per_page,

			'U_ACTION'		=> $this->u_action . "&amp;$u_sort_param&amp;users_per_page=$per_page&amp;start=$start",
		));

		$this->tpl_name = 'acp_inactive';
		$this->page_title = 'ACP_INACTIVE_USERS';
	}
}
