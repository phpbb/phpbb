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

namespace phpbb\privatemessage;

class view
{
	/**
	 * @var \phpbb\controller\helper
	 */
	protected $helper;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\auth\auth
	 */
	protected $auth;

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @var \phpbb\request\request
	 */
	protected $request;

	/**
	 * @var \phpbb\user_loader
	 */
	protected $user_loader;

	/**
	 * @var \phpbb\group\helper 
	 */
	protected $group_helper;

	/**
	 * @var string
	 */
	protected $privmsgs_table;

	/**
	 * @var string
	 */
	protected $privmsgs_to_table;

	/**
	 * @var string
	 */
	protected $privmsgs_folder_table;

	/**
	 * @var string
	 */
	protected $users_table;

	/**
	 * @var string
	 */
	protected $groups_table;

	/**
	 * @var string
	 */
	protected $root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	public function __construct(\phpbb\controller\helper $helper, \phpbb\user $user, \phpbb\config\config $config, \phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\request\request $request, \phpbb\user_loader $user_loader, \phpbb\group\helper  $group_helper, $privmsgs_table, $privmsgs_to_table, $privmsgs_folder_table, $users_table, $groups_table, $root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->user = $user;
		$this->config = $config;
		$this->auth = $auth;
		$this->db = $db;
		$this->language = $language;
		$this->template = $template;
		$this->request = $request;
		$this->user_loader = $user_loader;
		$this->group_helper = $group_helper;
		$this->privmsgs_table = $privmsgs_table;
		$this->privmsgs_to_table = $privmsgs_to_table;
		$this->privmsgs_folder_table = $privmsgs_folder_table;
		$this->users_table = $users_table;
		$this->groups_table = $groups_table;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function conversation($id)
	{
		$this->check_permissions();

		// select message folder and double-check it's root message of the conversation
		$sql = 'SELECT pt.folder_id, p.root_level
			FROM ' . $this->privmsgs_to_table . ' pt
			LEFT JOIN ' . $this->privmsgs_table . ' p
				ON (p.msg_id = pt.msg_id)
			WHERE pt.msg_id = ' . (int) $id . '
				AND pt.folder_id <> ' . PRIVMSGS_NO_BOX . '
				AND pt.user_id = ' . $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			return $this->helper->error('NO_MESSAGE', 404);
		}

		$folder_id = (int) $row['folder_id'];
		$root_msg_id = $row['root_level'] ?: $id;

		add_form_key('ucp_pm_compose');
		$this->template->assign_vars(array(
			'S_PM_CONVERSATION'		=> true,
			'CONVERSATION_SUBJECT'	=> $this->get_message_subject($root_msg_id),
			'ROOT_MSG_ID'			=> $root_msg_id,
			'CURRENT_TIME'			=> time(),
			'U_MARK_IMPORTANT'		=> $this->helper->route('phpbb_privatemessage_conversation_action_important', array('id' => $root_msg_id)),
			'U_EDIT_TITLE'			=> $this->helper->route('phpbb_privatemessage_conversation_action_edit_title', array('id' => $root_msg_id)),
			'U_DELETE'				=> $this->helper->route('phpbb_privatemessage_conversation_action_delete', array('id' => $root_msg_id)),
			'U_COMPOSE'				=> $this->helper->route('phpbb_privatemessage_compose'),
		));

		$this->update_unread_status($root_msg_id);
		$this->get_conversations($folder_id, true, $id);
		$this->get_messages($root_msg_id);

		return $this->helper->render('ucp_pm_view.html', '');
	}

	public function folder($id)
	{
		$this->check_permissions();

		$this->set_user_message_limit();

		$this->template->assign_vars(array(
			'U_COMPOSE'				=> $this->helper->route('phpbb_privatemessage_compose'),
		));

		$this->get_conversations($id);

		return $this->helper->render('ucp_pm_view.html', '');
	}

	/**
	* Set correct users max messages in PM folder.
	* If several group memberships define different amount of messages, the highest will be chosen.
	*/
	public function set_user_message_limit()
	{
		// Get maximum number of allowed recipients
		$sql = 'SELECT MAX(g.group_message_limit) as max_setting
			FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
			WHERE ug.user_id = ' . (int) $this->user->data['user_id'] . '
				AND ug.user_pending = 0
				AND ug.group_id = g.group_id';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		$message_limit = (int) $row['max_setting'];

		// If it is 0, there is no limit set and we use the maximum value within the config.
		$this->user->data['message_limit'] = (!$message_limit) ? $this->config['pm_max_msgs'] : $message_limit;
	}

	public function get_conversations($folder_id, $in_conversation = false, $msg_id = null)
	{
		if (!function_exists('rebuild_header'))
		{
			include($this->root_path . 'includes/functions_privmsgs.' . $this->php_ext);
		}

		$start = $this->request->variable('cstart', 0);
		$mstart = $this->request->variable('mstart', 0);

		if ($folder_id > 0)
		{
			$where_folder_id = 'AND t.folder_id = ' . (int) $folder_id;
		}
		else
		{
			// combine inbox, outbox and sent messages
			$where_folder_id = 'AND ' . $this->db->sql_in_set('t.folder_id', array(PRIVMSGS_INBOX, PRIVMSGS_OUTBOX, PRIVMSGS_SENTBOX));
		}

		$sql = 'SELECT COUNT(msg_id) as num_conversations
			FROM (
				SELECT t.msg_id
				FROM ' . $this->privmsgs_to_table . ' t
				LEFT JOIN ' . $this->privmsgs_table . ' p
					ON (p.msg_id = t.msg_id)
				WHERE t.user_id = ' . $this->user->data['user_id'] . '
					' . $where_folder_id . '
					AND p.root_level = 0
				GROUP BY t.msg_id
			) nt';
		$result = $this->db->sql_query_limit($sql, 1);
		$num_conversations = $this->db->sql_fetchfield('num_conversations', $result);
		$this->db->sql_freeresult($result);

		$sql_ary = array(
			'SELECT'	=> 't.*, p.root_level, p.message_time, p.message_subject, p.icon_id, p.to_address, p.message_attachment, p.bcc_address, u.username, u.username_clean, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, p.message_reported, (
				SELECT SUM(tu.pm_unread)
				FROM ' . $this->privmsgs_to_table . ' tu
				LEFT JOIN ' . $this->privmsgs_table . ' pu
					ON (tu.msg_id = pu.msg_id)
				WHERE pu.root_level = p.msg_id
					OR pu.msg_id = p.msg_id
			) AS pm_unread, (
				SELECT MAX(msg_id)
				FROM ' . $this->privmsgs_table . ' pn
				WHERE pn.msg_id = p.msg_id
					OR pn.root_level = p.msg_id
			) AS newest_message',
			'FROM'		=> array(
				$this->privmsgs_to_table	=> 't',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->privmsgs_table => 'p'),
					'ON'	=> 't.msg_id = p.msg_id',
				),
				array(
					'FROM'	=> array($this->users_table => 'u'),
					'ON'	=> 'p.author_id = u.user_id',
				),
			),
			'WHERE'		=> 't.user_id = ' . $this->user->data['user_id'] . '
				' . $where_folder_id . '
				AND p.root_level = 0',
			'GROUP_BY'	=> 't.msg_id',
			'ORDER_BY'	=> 'newest_message DESC',
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page'], $start);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		// grab all the addresses
		$addresses = array(
			'u'	=> array(),
			'g'	=> array(),
		);
		foreach ($rowset as &$item)
		{
			$item['addresses'] = \rebuild_header(array('to' => $item['to_address']));

			if (!empty($item['addresses']['u']))
			{
				$addresses['u'] = $addresses['u'] + $item['addresses']['u'];
			}

			if (!empty($item['addresses']['g']))
			{
				$addresses['g'] = $addresses['g'] + $item['addresses']['g'];
			}
		}

		// get user info
		if (!empty($addresses['u']))
		{
			$this->user_loader->load_users(array_keys($addresses['u']));
		}

		// get group info
		$to_groups = array();
		if (!empty($addresses['g']))
		{
			$sql = 'SELECT *
				FROM ' . $this->groups_table . '
				WHERE ' . $this->db->sql_in_set('group_id', array_keys($addresses['g']));
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$to_groups[$row['group_id']] = $row;
			}
			$this->db->sql_freeresult($result);	
		}

		foreach ($rowset as $row)
		{
			$this->template->assign_block_vars('conversations', array(
				'MESSAGE_AVATAR'			=> phpbb_get_user_avatar($row),
				'S_PM_UNREAD'				=> $row['pm_unread'] > 0,
				'PM_UNREAD_COUNT'			=> $row['pm_unread'],
				'S_PM_MARKED'				=> $row['pm_marked'],
				'U_VIEW_PM'					=> $this->helper->route('phpbb_privatemessage_conversation', array('id' => $row['msg_id'])),
				'SUBJECT'					=> censor_text($row['message_subject']),
				'S_AUTHOR_FOE'				=> false, // TODO: calculate this
				'MESSAGE_AUTHOR'			=> get_username_string('username', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
				'MESSAGE_AUTHOR_FULL'		=> get_username_string('full', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
				'U_MESSAGE_AUTHOR'			=> get_username_string('profile', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
				'SENT_TIME'					=> $this->user->format_date($row['message_time']),
				'S_CURRENT_CONVERSATION'	=> $row['msg_id'] == $msg_id,
				'S_AUTHOR_ONLINE'			=> true, // TODO: really fetch the info
			));

			// include author to the list of recipients
			$this->template->assign_block_vars('conversations.to', array(
				'TYPE'		=> 'user',
				'U_LINK'	=> get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour'], $row['username']),
				'NAME'		=> '<span style="color: #' . $row['user_colour'] . '">' . get_username_string('username', $row['user_id'], $row['username'], $row['user_colour'], $row['username']) . '</span>',
			));

			if (!empty($row['addresses']['u']))
			{
				foreach ($row['addresses']['u'] as $user_id => $type)
				{
					// skip the author
					if ($user_id == $row['user_id'])
					{
						continue;
					}

					$user_info = $this->user_loader->get_user($user_id);
					$this->template->assign_block_vars('conversations.to', array(
						'TYPE'		=> 'user',
						'U_LINK'	=> get_username_string('profile', $user_info['user_id'], $user_info['username'], $user_info['user_colour'], $user_info['username']),
						'NAME'		=> '<span style="color: #' . $user_info['user_colour'] . '">' . get_username_string('username', $user_info['user_id'], $user_info['username'], $user_info['user_colour'], $user_info['username']) . '</span>',
					));
				}
			}

			if (!empty($row['addresses']['g']))
			{
				foreach ($row['addresses']['g'] as $group_id => $type)
				{
					$group_info = $to_groups[$group_id];
					$this->template->assign_block_vars('conversations.to', array(
						'TYPE'		=> 'group',
						'U_LINK'	=> append_sid($this->root_path . 'memberlist.' . $this->php_ext, 'mode=group&g=' . $group_id),
						'NAME'		=> '<span style="color: #' . $group_info['group_colour'] . '">' . $this->group_helper->get_name($group_info['group_name']) . '</span>',
					));
				}
			}
		}

		$newest_start = $start - $this->config['topics_per_page'] < 0 ? 0 : $start - $this->config['topics_per_page'];
		$this->template->assign_vars(array(
			'U_OLDER_CONVERSATIONS'	=> $start + $this->config['topics_per_page'] >= $num_conversations ? false : $this->get_patination_url($in_conversation ? 'conversation' : 'folder', $in_conversation ? $msg_id : $folder_id, $start + $this->config['topics_per_page'], $mstart),
			'U_NEWER_CONVERSATIONS'	=> $start == 0 ? false : $this->get_patination_url($in_conversation ? 'conversation' : 'folder', $in_conversation ? $msg_id : $folder_id, $newest_start, $mstart),
		));

		// does the user have custom folders? If yes, we will display a link to the list of folders.
		$sql = 'SELECT COUNT(folder_id) as num_folders
			FROM ' . $this->privmsgs_folder_table . '
				WHERE user_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		$num_folders = (int) $this->db->sql_fetchfield('num_folders', $result);
		$this->db->sql_freeresult($result);

		if ($num_folders)
		{
			$this->template->assign_vars(array(
				'U_BACK_TO_FOLDERS'	=> $this->helper->route('phpbb_privatemessage_index'),
			));
		}
	}

	public function get_messages($root_msg_id)
	{
		$cstart = $this->request->variable('cstart', 0);
		$start = $this->request->variable('mstart', 0);

		// get the newest message ID (will be used to hide or display pagination to newer messages)
		$sql = 'SELECT msg_id
			FROM ' . $this->privmsgs_table . '
			WHERE root_level = ' . (int) $root_msg_id . '
				OR msg_id = ' . (int) $root_msg_id . '
			ORDER BY msg_id DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$newest_msg_id = $this->db->sql_fetchfield('msg_id', $result);
		$this->db->sql_freeresult($result);

		$sql_ary = array(
			'SELECT'	=> 'p.*, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height',
			'FROM'		=> array($this->privmsgs_table	=> 'p'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->users_table => 'u'),
					'ON'	=> 'u.user_id = p.author_id',
				),
			),
			'WHERE'		=> 'p.msg_id = ' . $root_msg_id . '
				OR p.root_level = ' . $root_msg_id,
			'ORDER_BY'	=> 'p.message_time DESC',
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query_limit($sql, $this->config['posts_per_page'], $start);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		// reverse the order
		$rowset = array_reverse($rowset);
		
		foreach ($rowset as $row)
		{
			$parse_flags = ($row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0);
			$parse_flags |= ($row['enable_smilies'] ? OPTION_FLAG_SMILIES : 0);

			$this->template->assign_block_vars('messages', array(
				'MSG_ID'				=> $row['msg_id'],
				'MESSAGE_AVATAR'		=> phpbb_get_user_avatar($row),
				'S_IS_SELF'				=> $row['author_id'] == $this->user->data['user_id'],
				'MESSAGE_AUTHOR'		=> get_username_string('username', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
				'MESSAGE_AUTHOR_FULL'	=> get_username_string('full', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
				'U_MESSAGE_AUTHOR'		=> get_username_string('profile', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
				'SENT_TIME'				=> $this->user->format_date($row['message_time']),
				'MESSAGE'				=> generate_text_for_display(censor_text($row['message_text']), $row['bbcode_uid'], $row['bbcode_bitfield'], $parse_flags, false),
				'U_EDIT'				=> $this->helper->route('phpbb_privatemessage_compose', array('action' => 'edit', 'p' => $row['msg_id'])),
				'U_QUOTE'				=> $this->helper->route('phpbb_privatemessage_compose', array('action' => 'quote', 'p' => $row['msg_id'])),
				'U_DELETE'				=> $this->helper->route('phpbb_privatemessage_compose', array('action' => 'delete', 'p' => $row['msg_id'])),
				'U_FORWARD'				=> $this->helper->route('phpbb_privatemessage_compose', array('action' => 'forward', 'p' => $row['msg_id'])),
			));
		}

		$newest_start = $start - $this->config['posts_per_page'] < 0 ? 0 : $start - $this->config['posts_per_page'];
		$this->template->assign_vars(array(
			'U_OLDER_MESSAGES'	=> $rowset[0]['msg_id'] == $root_msg_id ? false : $this->get_patination_url('conversation', $root_msg_id, $cstart, $start + $this->config['posts_per_page']),
			'U_NEWER_MESSAGES'	=> $rowset[count($rowset) - 1]['msg_id'] == $newest_msg_id ? false : $this->get_patination_url('conversation', $root_msg_id, $cstart, $newest_start),
		));
	}

	public function check_permissions()
	{
		if (!$this->user->data['is_registered'])
		{
			return $this->helper->error('NO_MESSAGE', 401);
		}

		if (!$this->config['allow_privmsg'])
		{
			return $this->helper->error('PM_DISABLED', 403);
		}

		if (!$this->auth->acl_get('u_readpm'))
		{
			return $this->helper->error('NO_AUTH_READ_MESSAGE', 403);
		}

		$this->language->add_lang('privatemessage');
	}

	public function get_message_subject($msg_id)
	{
		$sql = 'SELECT message_subject
			FROM ' . $this->privmsgs_table . '
			WHERE msg_id = ' . (int) $msg_id;
		$result = $this->db->sql_query($sql);
		$message_subject = $this->db->sql_fetchfield('message_subject', $result);
		$this->db->sql_freeresult($result);

		return $message_subject;
	}

	public function update_unread_status($root_msg_id)
	{
		$sql = 'SELECT pt.msg_id
			FROM ' . $this->privmsgs_table . ' p
			LEFT JOIN ' . $this->privmsgs_to_table . ' pt
				ON (pt.msg_id = p.msg_id AND pt.user_id = ' . (int) $this->user->data['user_id'] . ')
			WHERE (p.msg_id = ' . (int) $root_msg_id . '
				OR p.root_level = ' . (int) $root_msg_id . ')
				AND pt.pm_unread = 1';
		$result = $this->db->sql_query($sql);
		$unread_messages = array_column($this->db->sql_fetchrowset($result), 'msg_id');
		$this->db->sql_freeresult($result);

		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_unread_privmsg = user_unread_privmsg - ' . count($unread_messages) . '
			WHERE user_id = ' . (int) $this->user->data['user_id'];
		$this->db->sql_query($sql);

		$this->user->data['user_unread_privmsg'] -= count($unread_messages);

		$sql = 'UPDATE ' . $this->privmsgs_to_table . '
			SET pm_unread = 0
			WHERE ' . $this->db->sql_in_set('msg_id', $unread_messages, false, true) . '
				AND user_id = ' . (int) $this->user->data['user_id'];
		$this->db->sql_query($sql);
	}

	public function get_patination_url($route, $id, $cstart, $mstart)
	{
		return $this->helper->route('phpbb_privatemessage_' . $route, array('id' => $id, 'cstart' => $cstart, 'mstart' => $mstart));
	}
}
