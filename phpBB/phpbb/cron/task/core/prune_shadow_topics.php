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

namespace phpbb\cron\task\core;

/**
* Prune one forum of its shadow topics cron task.
*
* It is intended to be used when cron is invoked via web.
* This task can decide whether it should be run using data obtained by viewforum
* code, without making additional database queries.
*/
class prune_shadow_topics extends \phpbb\cron\task\base implements \phpbb\cron\task\parametrized
{
	protected $phpbb_root_path;
	protected $php_ext;
	protected $config;
	protected $db;
	protected $log;
	protected $user;

	/**
	* If $forum_data is given, it is assumed to contain necessary information
	* about a single forum that is to be pruned.
	*
	* If $forum_data is not given, forum id will be retrieved via $request->variable()
	* and a database query will be performed to load the necessary information
	* about the forum.
	*/
	protected $forum_data;

	/**
	* Constructor.
	*
	* @param string $phpbb_root_path The root path
	* @param string $php_ext PHP file extension
	* @param \phpbb\config\config $config The config
	* @param \phpbb\db\driver\driver_interface $db The db connection
	* @param \phpbb\log\log $log The phpBB log system
	* @param \phpbb\user $user The phpBB user object
	*/
	public function __construct($phpbb_root_path, $php_ext, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\log\log $log, \phpbb\user $user)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->config = $config;
		$this->db = $db;
		$this->log = $log;
		$this->user = $user;
	}

	/**
	* Manually set forum data.
	*
	* @param array $forum_data Information about a forum to be pruned.
	*/
	public function set_forum_data($forum_data)
	{
		$this->forum_data = $forum_data;
	}

	/**
	* Runs this cron task.
	*
	* @return null
	*/
	public function run()
	{
		if (!function_exists('auto_prune'))
		{
			include($this->phpbb_root_path . 'includes/functions_admin.' . $this->php_ext);
		}

		if ($this->forum_data['prune_shadow_days'])
		{
			$this->auto_prune_shadow_topics($this->forum_data['forum_id'], 'shadow', $this->forum_data['forum_flags'], $this->forum_data['prune_shadow_days'], $this->forum_data['prune_shadow_freq']);
		}
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* This cron task will not run when system cron is utilised, as in
	* such cases prune_all_forums task would run instead.
	*
	* Additionally, this task must be given the forum data, either via
	* the constructor or parse_parameters method.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return !$this->config['use_system_cron'] && $this->forum_data;
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* Forum pruning interval is specified in the forum data.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->forum_data['enable_shadow_prune'] && $this->forum_data['prune_shadow_next'] < time();
	}

	/**
	* Returns parameters of this cron task as an array.
	* The array has one key, f, whose value is id of the forum to be pruned.
	*
	* @return array
	*/
	public function get_parameters()
	{
		return array('f' => $this->forum_data['forum_id']);
	}

	/**
	* Parses parameters found in $request, which is an instance of
	* \phpbb\request\request_interface.
	*
	* It is expected to have a key f whose value is id of the forum to be pruned.
	*
	* @param \phpbb\request\request_interface $request Request object.
	*
	* @return null
	*/
	public function parse_parameters(\phpbb\request\request_interface $request)
	{
		$this->forum_data = null;
		if ($request->is_set('f'))
		{
			$forum_id = $request->variable('f', 0);

			$sql = 'SELECT forum_id, prune_shadow_next, enable_shadow_prune, prune_shadow_days, forum_flags, prune_shadow_freq
				FROM ' . FORUMS_TABLE . "
				WHERE forum_id = $forum_id";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				$this->forum_data = $row;
			}
		}
	}

	/**
	* Automatically prune shadow topics
	* Based on fuunction auto_prune()
	* @param int $forum_id Forum ID of forum that should be pruned
	* @param string $prune_mode Prune mode
	* @param int $prune_flags Prune flags
	* @param int $prune_days Prune date in days
	* @param int $prune_freq Prune frequency
	* @return null
	*/
	protected function auto_prune_shadow_topics($forum_id, $prune_mode, $prune_flags, $prune_days, $prune_freq)
	{
		$sql = 'SELECT forum_name
			FROM ' . FORUMS_TABLE . "
			WHERE forum_id = $forum_id";
		$result = $this->db->sql_query($sql, 3600);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row)
		{
			$prune_date = time() - ($prune_days * 86400);
			$next_prune = time() + ($prune_freq * 86400);

			prune($forum_id, $prune_mode, $prune_date, $prune_flags, true);

			$sql = 'UPDATE ' . FORUMS_TABLE . "
				SET prune_shadow_next = $next_prune
				WHERE forum_id = $forum_id";
			$this->db->sql_query($sql);

			$user_id = (empty($this->user->data)) ? ANONYMOUS : $this->user->data['user_id'];
			$user_ip = (empty($this->user->ip)) ? '' : $this->user->ip;

			$this->log->add('admin', $user_id, $user_ip, 'LOG_PRUNE_SHADOW', false, array($row['forum_name']));
		}

		return;
	}
}
