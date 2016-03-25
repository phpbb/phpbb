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
* Prune all forums cron task.
*
* It is intended to be invoked from system cron.
* This task will find all forums for which pruning is enabled, and will
* prune all forums as necessary.
*/
class prune_all_forums extends \phpbb\cron\task\base
{
	protected $phpbb_root_path;
	protected $php_ext;
	protected $config;
	protected $db;

	/**
	* Constructor.
	*
	* @param string $phpbb_root_path The root path
	* @param string $php_ext The PHP file extension
	* @param \phpbb\config\config $config The config
	* @param \phpbb\db\driver\driver_interface $db The db connection
	*/
	public function __construct($phpbb_root_path, $php_ext, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->config = $config;
		$this->db = $db;
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

		$sql = 'SELECT forum_id, prune_next, enable_prune, prune_days, prune_viewed, forum_flags, prune_freq
			FROM ' . FORUMS_TABLE . "
			WHERE enable_prune = 1
				AND prune_next < " . time();
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['prune_days'])
			{
				auto_prune($row['forum_id'], 'posted', $row['forum_flags'], $row['prune_days'], $row['prune_freq']);
			}

			if ($row['prune_viewed'])
			{
				auto_prune($row['forum_id'], 'viewed', $row['forum_flags'], $row['prune_viewed'], $row['prune_freq']);
			}
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* This cron task will only run when system cron is utilised.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return (bool) $this->config['use_system_cron'];
	}
}
