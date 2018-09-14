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

class index
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
	protected $privmsgs_to_table;

	/**
	 * @var string
	 */
	protected $privmsgs_folder_table;

	public function __construct(\phpbb\controller\helper $helper, \phpbb\user $user, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\template\template $template, $privmsgs_to_table, $privmsgs_folder_table)
	{
		$this->helper = $helper;
		$this->user = $user;
		$this->config = $config;
		$this->db = $db;
		$this->language = $language;
		$this->template = $template;
		$this->privmsgs_to_table = $privmsgs_to_table;
		$this->privmsgs_folder_table = $privmsgs_folder_table;
	}

	public function handle()
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
				'U_FOLDER'			=> $this->helper->route('phpbb_privatemessage_folder', array('id' => $f_id)),
	
				'S_UNREAD_MESSAGES'	=> ($folder_ary['unread_messages']) ? true : false,
				'S_CUSTOM_FOLDER'	=> $f_id > 0
			));
		}

		return $this->helper->render('ucp_pm_view.html', '');
	}
}
