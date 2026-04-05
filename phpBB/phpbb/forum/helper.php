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

namespace phpbb\forum;

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\event\dispatcher;
use phpbb\user;

class helper
{
	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

	/** @var dispatcher */
	protected $dispatcher;

	/** @var user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param config           $config
	 * @param driver_interface $db
	 * @param dispatcher       $dispatcher
	 * @param user             $user
	 */
	public function __construct(config $config, driver_interface $db, dispatcher $dispatcher, user $user)
	{
		$this->config = $config;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->user = $user;
	}

	/**
	 * Get forum rows
	 *
	 * @param array|null $root_data Root forum data, or false to fetch rows from the top level
	 * @return mixed
	 */
	public function get_forums_rows(array|null $root_data): mixed
	{
		if (!$root_data)
		{
			$sql_where = '';
		}
		else
		{
			$sql_where = 'left_id > ' . $root_data['left_id'] . ' AND left_id < ' . $root_data['right_id'];
		}

		// Display list of active topics for this category?
		$show_active = isset($root_data['forum_flags']) && ($root_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS);

		$sql_array = array(
			'SELECT'	=> 'f.*',
			'FROM'		=> array(
				FORUMS_TABLE		=> 'f'
			),
			'LEFT_JOIN'	=> array(),
		);

		if ($this->config['load_db_lastread'] && $this->user->data['is_registered'])
		{
			$sql_array['LEFT_JOIN'][] = array('FROM' => array(FORUMS_TRACK_TABLE => 'ft'), 'ON' => 'ft.user_id = ' . $this->user->data['user_id'] . ' AND ft.forum_id = f.forum_id');
			$sql_array['SELECT'] .= ', ft.mark_time';
		}

		if ($show_active)
		{
			$sql_array['LEFT_JOIN'][] = array(
				'FROM'	=> array(FORUMS_ACCESS_TABLE => 'fa'),
				'ON'	=> "fa.forum_id = f.forum_id AND fa.session_id = '" . $this->db->sql_escape($this->user->session_id) . "'"
			);

			$sql_array['SELECT'] .= ', fa.user_id';
		}

		$sql_ary = array(
			'SELECT'	=> $sql_array['SELECT'],
			'FROM'		=> $sql_array['FROM'],
			'LEFT_JOIN'	=> $sql_array['LEFT_JOIN'],

			'WHERE'		=> $sql_where,

			'ORDER_BY'	=> 'f.left_id',
		);

		/**
		 * Event to modify the SQL query before the forum data is queried
		 *
		 * @event core.display_forums_modify_sql
		 * @var	array	sql_ary		The SQL array to get the data of the forums
		 * @since 3.1.0-a1
		 */
		$vars = array('sql_ary');
		extract($this->dispatcher->trigger_event('core.display_forums_modify_sql', compact($vars)));

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $data;
	}

	/**
	 * Get forum data
	 *
	 * @param int $forum_id
	 * @return array|false
	 */
	public function get_forum_data(int $forum_id): array|false
	{
		$sql_ary = [
			'SELECT' => 'f.*',
			'FROM' => [
				FORUMS_TABLE => 'f',
			],
			'WHERE' => 'f.forum_id = ' . $forum_id,
		];

		// Grab appropriate forum data
		if ($this->config['load_db_lastread'] && $this->user->data['is_registered'])
		{
			$sql_ary['LEFT_JOIN'][] = [
				'FROM' => [FORUMS_TRACK_TABLE => 'ft'],
				'ON' => 'ft.user_id = ' . $this->user->data['user_id'] . ' AND ft.forum_id = f.forum_id',
			];
			$sql_ary['SELECT'] .= ', ft.mark_time';
		}

		if ($this->user->data['is_registered'])
		{
			$sql_ary['LEFT_JOIN'][] = [
				'FROM' => [FORUMS_WATCH_TABLE => 'fw'],
				'ON' => 'fw.forum_id = f.forum_id AND fw.user_id = ' . $this->user->data['user_id'],
			];
			$sql_ary['SELECT'] .= ', fw.notify_status';
		}

		/**
		 * You can use this event to modify the sql that selects the forum on the viewforum page.
		 *
		 * @event core.viewforum_modify_sql
		 * @var array    sql_ary        The SQL array to get the data for a forum
		 * @since 3.3.14-RC1
		 */
		$vars = ['sql_ary'];
		extract($this->dispatcher->trigger_event('core.viewforum_modify_sql', compact($vars)));

		$result = $this->db->sql_query($this->db->sql_build_query('SELECT', $sql_ary));
		$forum_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $forum_data;
	}
}
