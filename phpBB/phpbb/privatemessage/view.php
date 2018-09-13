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
	 * @var \phpbb\request\request
	 */
	protected $request;

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

	public function __construct(\phpbb\controller\helper $helper, \phpbb\user $user, \phpbb\config\config $config, \phpbb\request\request $request, \phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\template\template $template, $privmsgs_table, $privmsgs_to_table, $privmsgs_folder_table, $users_table)
	{
		$this->helper = $helper;
		$this->user = $user;
		$this->config = $config;
		$this->request = $request;
		$this->auth = $auth;
		$this->db = $db;
		$this->language = $language;
		$this->template = $template;
		$this->privmsgs_table = $privmsgs_table;
		$this->privmsgs_to_table = $privmsgs_to_table;
		$this->privmsgs_folder_table = $privmsgs_folder_table;
		$this->users_table = $users_table;
	}

	public function handle($mode)
	{
		if (!$this->user->data['is_registered'])
		{
			return $this->helper->error('NO_MESSAGE', 401);
		}

		if (!$this->config['allow_privmsg'])
		{
			return $this->helper->error('PM_DISABLED', 403);
		}

		$this->language->add_lang('privatemessage');

		$folder_id = null;
		$msg_id = null;

		switch ($mode)
		{
			case 'thread':

				if (!$this->auth->acl_get('u_readpm'))
				{
					return $this->helper->error('NO_AUTH_READ_MESSAGE', 403);
				}

				$msg_id = $this->request->variable('id', 0);

				$sql = 'SELECT pt.folder_id, p.root_level
					FROM ' . $this->privmsgs_to_table . ' pt
					LEFT JOIN ' . $this->privmsgs_table . ' p
						ON (p.msg_id = pt.msg_id)
					WHERE pt.msg_id = ' . (int) $msg_id . '
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

				$root_msg_id = $row['root_level'] ?: $msg_id;

				$sql = 'SELECT message_subject
					FROM ' . $this->privmsgs_table . '
					WHERE msg_id = ' . (int) $root_msg_id;
				$result = $this->db->sql_query($sql);
				$message_subject = $this->db->sql_fetchfield('message_subject', $result);
				$this->db->sql_freeresult($result);

				$this->template->assign_vars(array(
					'THREAD_SUBJECT'	=> $message_subject,
				));

				$this->get_messages($root_msg_id);

			// no break; we need to display the folder as well

			case 'folder':

				$this->set_user_message_limit();

				if ($folder_id === null)
				{
					$folder_id = $this->request->variable('id', PRIVMSGS_NO_BOX);
				}

				$this->get_threads($folder_id, $msg_id);

				$this->template->assign_vars(array(
					'U_BACK_TO_FOLDERS'	=> $this->helper->route('phpbb_privatemessage_view', array('mode' => 'index')),
				));

			break;

			default:

				$this->get_folders();

			break;
		}

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

	/**
	* Get all folders
	*/
	public function get_folders($folder_id = false)
	{
		$folder = array();
	
		// Get folder information
		$sql = 'SELECT folder_id, COUNT(msg_id) as num_messages, SUM(pm_unread) as num_unread
			FROM ' . $this->privmsgs_to_table . '
			WHERE user_id = ' . (int) $this->user->data['user_id'] . '
				AND folder_id <> ' . PRIVMSGS_NO_BOX . '
			GROUP BY folder_id';
		$result = $this->db->sql_query($sql);
	
		$num_messages = $num_unread = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$num_messages[(int) $row['folder_id']] = $row['num_messages'];
			$num_unread[(int) $row['folder_id']] = $row['num_unread'];
		}
		$this->db->sql_freeresult($result);
	
		// Make sure the default boxes are defined
		$available_folders = array(PRIVMSGS_INBOX, PRIVMSGS_OUTBOX, PRIVMSGS_SENTBOX);
	
		foreach ($available_folders as $default_folder)
		{
			if (!isset($num_messages[$default_folder]))
			{
				$num_messages[$default_folder] = 0;
			}
	
			if (!isset($num_unread[$default_folder]))
			{
				$num_unread[$default_folder] = 0;
			}
		}
	
		// Adjust unread status for outbox
		$num_unread[PRIVMSGS_OUTBOX] = $num_messages[PRIVMSGS_OUTBOX];
	
		$folder[PRIVMSGS_INBOX] = array(
			'folder_name'		=> $this->language->lang('PM_INBOX'),
			'num_messages'		=> $num_messages[PRIVMSGS_INBOX],
			'unread_messages'	=> $num_unread[PRIVMSGS_INBOX]
		);
	
		// Custom Folder
		$sql = 'SELECT folder_id, folder_name, pm_count
			FROM ' . $this->privmsgs_folder_table . '
				WHERE user_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
	
		while ($row = $this->db->sql_fetchrow($result))
		{
			$folder[$row['folder_id']] = array(
				'folder_name'		=> $row['folder_name'],
				'num_messages'		=> $row['pm_count'],
				'unread_messages'	=> ((isset($num_unread[$row['folder_id']])) ? $num_unread[$row['folder_id']] : 0)
			);
		}
		$this->db->sql_freeresult($result);
	
		$folder[PRIVMSGS_OUTBOX] = array(
			'folder_name'		=> $this->language->lang('PM_OUTBOX'),
			'num_messages'		=> $num_messages[PRIVMSGS_OUTBOX],
			'unread_messages'	=> $num_unread[PRIVMSGS_OUTBOX]
		);
	
		$folder[PRIVMSGS_SENTBOX] = array(
			'folder_name'		=> $this->language->lang('PM_SENTBOX'),
			'num_messages'		=> $num_messages[PRIVMSGS_SENTBOX],
			'unread_messages'	=> $num_unread[PRIVMSGS_SENTBOX]
		);
	
		// Define Folder Array for template designers (and for making custom folders usable by the template too)
		foreach ($folder as $f_id => $folder_ary)
		{
			$this->template->assign_block_vars('folders', array(
				'FOLDER_ID'			=> $f_id,
				'FOLDER_NAME'		=> $folder_ary['folder_name'],
				'NUM_MESSAGES'		=> $folder_ary['num_messages'],
				'UNREAD_MESSAGES'	=> $folder_ary['unread_messages'],
	
				// TODO: use route
				'U_FOLDER'			=> $this->helper->route('phpbb_privatemessage_view', array('mode' => 'folder', 'id' => $f_id)),
	
				'S_CUR_FOLDER'		=> $f_id === $folder_id,
				'S_UNREAD_MESSAGES'	=> ($folder_ary['unread_messages']) ? true : false,
				'S_CUSTOM_FOLDER'	=> $f_id > 0
			));
		}
	
		if ($folder_id !== false && $folder_id !== PRIVMSGS_HOLD_BOX && !isset($folder[$folder_id]))
		{
			return $this->helper->error('UNKNOWN_FOLDER', 404);
		}
	
		return $folder;
	}

	public function get_threads($folder_id, $msg_id)
	{
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
				AND t.folder_id = ' . (int) $folder_id . '
				AND p.root_level = 0',
			//'ORDER_BY'	=> $sql_sort_order,
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('threads', array(
				'MESSAGE_AVATAR'		=> phpbb_get_user_avatar($row),
				'S_PM_UNREAD'			=> $row['pm_unread'] > 0,
				'PM_UNREAD_COUNT'		=> $row['pm_unread'],
				'S_PM_MARKED'			=> $row['pm_marked'],
				'U_VIEW_PM'				=> $this->helper->route('phpbb_privatemessage_view', array('mode' => 'thread', 'id' => $row['msg_id'])),
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
}
