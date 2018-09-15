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
	protected $root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	public function __construct(\phpbb\controller\helper $helper, \phpbb\user $user, \phpbb\config\config $config, \phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\template\template $template, $privmsgs_table, $privmsgs_to_table, $privmsgs_folder_table, $users_table, $root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->user = $user;
		$this->config = $config;
		$this->auth = $auth;
		$this->db = $db;
		$this->language = $language;
		$this->template = $template;
		$this->privmsgs_table = $privmsgs_table;
		$this->privmsgs_to_table = $privmsgs_to_table;
		$this->privmsgs_folder_table = $privmsgs_folder_table;
		$this->users_table = $users_table;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function thread($id)
	{
		$this->check_permissions();

		// select message folder and double-check it's root message of the thread
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
			'U_COMPOSE'			=> $this->helper->route('phpbb_privatemessage_compose'),
			'S_PM_THREAD'		=> true,
			'THREAD_SUBJECT'	=> $this->get_message_subject($root_msg_id),
			'ROOT_MSG_ID'		=> $root_msg_id,
			'CURRENT_TIME'		=> time(),
		));

		$this->update_unread_status($root_msg_id);
		$this->get_threads($folder_id, $id);
		$this->get_messages($root_msg_id);

		return $this->helper->render('ucp_pm_view.html', '');
	}

	public function folder($id)
	{
		$this->check_permissions();

		$this->set_user_message_limit();

		$this->get_threads($id);

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

	public function get_threads($folder_id, $msg_id = null)
	{
		if (!function_exists('rebuild_header'))
		{
			include($this->root_path . 'includes/functions_privmsgs.' . $this->php_ext);
		}

		if ($folder_id > 0)
		{
			$where_folder_id = 'AND t.folder_id = ' . (int) $folder_id;
		}
		else
		{
			// combine inbox, outbox and sent messages
			$where_folder_id = 'AND ' . $this->db->sql_in_set('t.folder_id', array(PRIVMSGS_INBOX, PRIVMSGS_OUTBOX, PRIVMSGS_SENTBOX));
		}

		$sql_ary = array(
			'SELECT'	=> 't.*, p.root_level, p.message_time, p.message_subject, p.icon_id, p.to_address, p.message_attachment, p.bcc_address, u.username, u.username_clean, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, p.message_reported, (
				SELECT SUM(tu.pm_unread)
				FROM ' . $this->privmsgs_to_table . ' tu
				LEFT JOIN ' . $this->privmsgs_table . ' pu
					ON (tu.msg_id = pu.msg_id)
				WHERE pu.root_level = p.msg_id
					OR pu.msg_id = p.msg_id
			) AS pm_unread',
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
			//'ORDER_BY'	=> $sql_sort_order,
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			print_r(\rebuild_header(array('to' => $row['to_address'])));
			$this->template->assign_block_vars('threads', array(
				'MESSAGE_AVATAR'		=> phpbb_get_user_avatar($row),
				'S_PM_UNREAD'			=> $row['pm_unread'] > 0,
				'PM_UNREAD_COUNT'		=> $row['pm_unread'],
				'S_PM_MARKED'			=> $row['pm_marked'],
				'U_VIEW_PM'				=> $this->helper->route('phpbb_privatemessage_thread', array('id' => $row['msg_id'])),
				'SUBJECT'				=> censor_text($row['message_subject']),
				'S_AUTHOR_FOE'			=> false, // TODO: calculate this
				'MESSAGE_AUTHOR'		=> get_username_string('username', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
				'MESSAGE_AUTHOR_FULL'	=> get_username_string('full', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
				'U_MESSAGE_AUTHOR'		=> get_username_string('profile', $row['author_id'], $row['username'], $row['user_colour'], $row['username']),
				'SENT_TIME'				=> $this->user->format_date($row['message_time']),
				'S_CURRENT_THREAD'		=> $row['msg_id'] == $msg_id,
				'S_AUTHOR_ONLINE'		=> true, // TODO: really fetch the info
			));
		}
		$this->db->sql_freeresult($result);

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
			'ORDER_BY'	=> 'p.message_time ASC',
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
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
			));
		}
		$this->db->sql_freeresult($result);
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
}
