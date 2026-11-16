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

namespace phpbb\members;

use phpbb\filesystem\helper as filesystem_helper;

/**
 * Class to handle viewonline related tasks
 */
class viewonline_helper
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	/** @var \phpbb\routing\router */
	protected $router;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ex;

	/** @var string */
	protected $phpbb_adm_relative_path;

	/** @var string */
	protected $forums_table;

	/** @var string */
	protected $topics_table;

	/** @var string */
	protected $users_table;

	/** @var string */
	protected $sessions_table;

	/**
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\config\config $config
	 * @param \phpbb\event\dispatcher_interface $dispatcher
	 * @param \phpbb\routing\router $router
	 * @param \phpbb\controller\helper $helper
	 * @param \phpbb\language\language $language
	 * @param \phpbb\auth\auth $auth
	 * @param string $phpbb_root_path
	 * @param string $php_ex
	 * @param string $phpbb_adm_relative_path
	 * @param string $users_table
	 * @param string $sessions_table
	 * @param string $topics_table
	 * @param string $forums_table
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\event\dispatcher_interface $dispatcher, \phpbb\routing\router $router, \phpbb\controller\helper $helper, \phpbb\language\language $language, \phpbb\auth\auth $auth, string $phpbb_root_path, string $php_ex, string $phpbb_adm_relative_path, string $users_table, string $sessions_table, string $topics_table, string $forums_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->dispatcher = $dispatcher;

		$this->router = $router;
		$this->helper = $helper;
		$this->language = $language;
		$this->auth = $auth;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ex = $php_ex;
		$this->phpbb_adm_relative_path = $phpbb_adm_relative_path;

		$this->users_table = $users_table;
		$this->sessions_table = $sessions_table;
		$this->topics_table = $topics_table;
		$this->forums_table = $forums_table;
	}

	/**
	 * Get forum IDs for topics
	 *
	 * Retrieve forum IDs and add the data into the session data array
	 * Array structure matches sql_fethrowset() result array
	 *
	 * @param array $session_data_rowset Users' session data array
	 * @return void
	 */
	public function get_forum_ids(array &$session_data_rowset): void
	{
		$topic_ids = $match = [];
		foreach ($session_data_rowset as $number => $row)
		{
			if ($row['session_forum_id'] == 0 && preg_match('#t=([0-9]+)#', $row['session_page'], $match))
			{
				$topic_ids[$number] = (int) $match[1];
			}
		}

		if (count($topic_ids = array_unique($topic_ids)))
		{
			$sql_ary = [
				'SELECT'	=> 't.topic_id, t.forum_id',
				'FROM'		=> [
					$this->topics_table => 't',
				],
				'WHERE'		=> $this->db->sql_in_set('t.topic_id', $topic_ids),
				'ORDER_BY'	=> 't.topic_id',
			];
			$result = $this->db->sql_query($this->db->sql_build_query('SELECT', $sql_ary));
			$forum_ids_rowset = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);

			foreach ($forum_ids_rowset as $forum_ids_row)
			{
				$session_data_row_number = array_search((int) $forum_ids_row['topic_id'], $topic_ids);
				$session_data_rowset[$session_data_row_number]['session_forum_id'] = (int) $forum_ids_row['forum_id'];
			}
		}
	}

	/**
	 * Get user page
	 *
	 * @param string $session_page User's session page
	 * @return array Match array filled by preg_match()
	 */
	public function get_user_page($session_page): array
	{
		$session_page = filesystem_helper::clean_path($session_page);

		if (str_starts_with($session_page, './'))
		{
			$session_page = substr($session_page, 2);
		}

		preg_match('#^((\.\./)*([a-z0-9/_-]+))#i', $session_page, $on_page);
		if (empty($on_page))
		{
			$on_page[1] = '';
		}

		return $on_page;
	}

	/**
	 * Given a certain page, it returns the title of the page and a link to it.
	 * There are 2 strategies to detect the page the user is in:
	 *  - Try to get the controller route from the router service
	 *  - And second, if is not possible to get the controller route, the
	 * page is analyzed by path, analyzing the file that is accessed. This
	 * is mostly for legacy pages, and should be removed in the future.
	 *
	 * @param string $session_page
	 * @param int $forum_id
	 * @return array
	 */
	public function get_location(string $session_page, int $forum_id): array
	{
		try
		{
			$match = $this->router->match($session_page);

			switch ($match['_route'])
			{
				case 'phpbb_index_controller':
					$location = $this->language->lang('INDEX');
					$location_url = $this->helper->route('phpbb_index_controller');
					break;

				case 'phpbb_help_bbcode_controller':
				case 'phpbb_help_faq_controller':
					$location = $this->language->lang('VIEWING_FAQ');
					$location_url = $this->helper->route('phpbb_help_faq_controller');
					break;

				case 'phpbb_members_online':
				case 'phpbb_members_online_whois':
					$location = $this->language->lang('VIEWING_ONLINE');
					$location_url = $this->helper->route('phpbb_members_online');
					break;

				case 'phpbb_members_team':
					$location_url = append_sid($this->phpbb_root_path . "memberlist." . $this->php_ex);
					$location = $this->language->lang('VIEWING_MEMBERS');
					break;

				case 'phpbb_report_pm_controller':
				case 'phpbb_report_post_controller':
					$location = $this->language->lang('REPORTING_POST');
					$location_url = append_sid($this->phpbb_root_path . "index." . $this->php_ex);
					break;

				default:
					// Is a route, but not in the switch
					$location = $this->language->lang('INDEX');
					$location_url = append_sid($this->phpbb_root_path . "index." . $this->php_ex);
			}
		}
		catch (\RuntimeException $e) // Urls without route
		{
			$on_page = $this->get_user_page($session_page);

			switch ($on_page[1])
			{
				case $this->phpbb_adm_relative_path . 'index':
					$location = $this->language->lang('ACP');
					$location_url = append_sid($this->phpbb_root_path . "index." . $this->php_ex);
					break;

				case 'posting':
				case 'viewforum':
				case 'viewtopic':

					$forum_data = $this->get_forum_data();

					if ($forum_id && $this->auth->acl_get('f_list', $forum_id))
					{
						$location = '';
						$location_url = append_sid($this->phpbb_root_path . "viewforum." . $this->php_ex, 'f=' . $forum_id);

						if ($forum_data[$forum_id]['forum_type'] == FORUM_LINK)
						{
							$location = $this->language->lang('READING_LINK', $forum_data[$forum_id]['forum_name']);
							break;
						}

						switch ($on_page[1])
						{
							case 'posting':
								preg_match('#mode=([a-z]+)#', $session_page, $on_page);
								$posting_mode = (!empty($on_page[1])) ? $on_page[1] : '';

								switch ($posting_mode)
								{
									case 'reply':
									case 'quote':
										$location = $this->language->lang('REPLYING_MESSAGE', $forum_data[$forum_id]['forum_name']);
										break;

									default:
										$location = $this->language->lang('POSTING_MESSAGE', $forum_data[$forum_id]['forum_name']);
										break;
								}
								break;

							case 'viewtopic':
								$location = $this->language->lang('READING_TOPIC', $forum_data[$forum_id]['forum_name']);
								break;

							case 'viewforum':
								$location = $this->language->lang('READING_FORUM', $forum_data[$forum_id]['forum_name']);
								break;
						}
					}
					else
					{
						$location = $this->language->lang('INDEX');
						$location_url = append_sid($this->phpbb_root_path . "index." . $this->php_ex);
					}
					break;

				case 'search':
					$location = $this->language->lang('SEARCHING_FORUMS');
					$location_url = append_sid($this->phpbb_root_path . "search." . $this->php_ex);
					break;

				case 'memberlist':
					$location_url = append_sid($this->phpbb_root_path . "memberlist." . $this->php_ex);

					if (str_contains($session_page, 'mode=viewprofile'))
					{
						$location = $this->language->lang('VIEWING_MEMBER_PROFILE');
					}
					else if (str_contains($session_page, 'mode=contactadmin'))
					{
						$location = $this->language->lang('VIEWING_CONTACT_ADMIN');
						$location_url = append_sid($this->phpbb_root_path . "memberlist." . $this->php_ex, 'mode=contactadmin');
					}
					else
					{
						$location = $this->language->lang('VIEWING_MEMBERS');
					}
					break;

				case 'mcp':
					$location = $this->language->lang('VIEWING_MCP');
					$location_url = append_sid($this->phpbb_root_path . "index." . $this->php_ex);
					break;

				case 'ucp':
					$location = $this->language->lang('VIEWING_UCP');

					// Grab some common modules
					$url_params = [
						'mode=register'		=> 'VIEWING_REGISTER',
						'i=pm&mode=compose'	=> 'POSTING_PRIVATE_MESSAGE',
						'i=pm&'				=> 'VIEWING_PRIVATE_MESSAGES',
						'i=profile&'		=> 'CHANGING_PROFILE',
						'i=prefs&'			=> 'CHANGING_PREFERENCES',
					];

					foreach ($url_params as $param => $lang)
					{
						if (strpos($session_page, $param) !== false)
						{
							$location = $this->language->lang($lang);
							break;
						}
					}

					$location_url = append_sid($this->phpbb_root_path . "index." . $this->php_ex);
					break;

				default:
					$location = $this->language->lang('INDEX');
					$location_url = append_sid($this->phpbb_root_path . "index." . $this->php_ex);
					break;
			}
		}

		return [$location, $location_url];
	}

	/**
	 * Get number of guests online
	 *
	 * @return int
	 */
	public function get_number_guests(): int
	{
		switch ($this->db->get_sql_layer())
		{
			case 'sqlite3':
				$sql = 'SELECT COUNT(session_ip) as num_guests
					FROM (
						SELECT DISTINCT session_ip
							FROM ' . $this->sessions_table . '
							WHERE session_user_id = ' . ANONYMOUS . '
								AND session_time >= ' . (time() - ($this->config['load_online_time'] * 60)) .
					')';
				break;

			default:
				$sql = 'SELECT COUNT(DISTINCT session_ip) as num_guests
					FROM ' . $this->sessions_table . '
					WHERE session_user_id = ' . ANONYMOUS . '
						AND session_time >= ' . (time() - ($this->config['load_online_time'] * 60));
				break;
		}
		$result = $this->db->sql_query($sql);
		$guest_counter = (int) $this->db->sql_fetchfield('num_guests');
		$this->db->sql_freeresult($result);

		return $guest_counter;
	}

	/**
	 * Get forum data
	 *
	 * @return array
	 */
	public function get_forum_data(): array
	{
		static $forum_data;

		if (isset($forum_data))
		{
			return $forum_data;
		}

		// Forum info
		$sql_ary = [
			'SELECT'	=> 'f.forum_id, f.forum_name, f.parent_id, f.forum_type, f.left_id, f.right_id',
			'FROM'		=> [
				$this->forums_table    => 'f',
			],
			'ORDER_BY'	=> 'f.left_id ASC',
		];

		/**
		 * Modify the forum data SQL query for getting additional fields if needed
		 *
		 * @event core.viewonline_modify_forum_data_sql
		 * @var	array	sql_ary			The SQL array
		 * @since 3.1.5-RC1
		 */
		$vars = ['sql_ary'];
		extract($this->dispatcher->trigger_event('core.viewonline_modify_forum_data_sql', compact($vars)));

		$result = $this->db->sql_query($this->db->sql_build_query('SELECT', $sql_ary), 600);
		unset($sql_ary);

		$forum_data = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$forum_data[$row['forum_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $forum_data;
	}

	/**
	 * Build and execute the sessions query and return the rowset.
	 *
	 * @param bool $show_guests
	 * @param string $order_by
	 * @param int &$guest_counter
	 * @return array
	 */
	public function get_session_data_rowset(bool &$show_guests, string $order_by, int &$guest_counter): array
	{
		$forum_data = $this->get_forum_data();

		$sql_ary = [
			'SELECT'    => 'u.user_id, u.username, u.username_clean, u.user_type, u.user_colour, s.session_id, s.session_time, s.session_page, s.session_ip, s.session_browser, s.session_viewonline, s.session_forum_id',
			'FROM'      => [
				$this->users_table     => 'u',
				$this->sessions_table  => 's',
			],
			'WHERE'     => 'u.user_id = s.session_user_id
				AND s.session_time >= ' . (time() - ($this->config['load_online_time'] * 60)) .
				(($show_guests) ? '' : ' AND s.session_user_id <> ' . ANONYMOUS),
			'ORDER_BY'  => $order_by,
		];

		/**
		 * Modify the SQL query for getting the user data to display viewonline list
		 *
		 * @event core.viewonline_modify_sql
		 * @var	array	sql_ary			The SQL array
		 * @var	bool	show_guests		Do we display guests in the list
		 * @var	int		guest_counter	Number of guests displayed
		 * @var	array	forum_data		Array with forum data
		 * @since 3.1.0-a1
		 * @changed 3.1.0-a2 Added vars guest_counter and forum_data
		 */
		$vars = ['sql_ary', 'show_guests', 'guest_counter', 'forum_data'];
		extract($this->dispatcher->trigger_event('core.viewonline_modify_sql', compact($vars)));

		$result = $this->db->sql_query($this->db->sql_build_query('SELECT', $sql_ary));
		$session_data_rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $session_data_rowset;
	}
}
