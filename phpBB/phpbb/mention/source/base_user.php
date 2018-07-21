<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\mention\source;

abstract class base_user implements source_interface
{
	/** @var int */
	const NAMES_BATCH_SIZE = 100;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\user_loader $user_loader, $phpbb_root_path, $phpEx)
	{
		$this->db = $db;
		$this->config = $config;
		$this->user_loader = $user_loader;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		if (!function_exists('phpbb_get_user_rank'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
		}
	}

	/**
	 * Builds a query based on user input
	 *
	 * @param string $keyword  Search string
	 * @param int    $topic_id Current topic ID
	 * @return string Query ready for execution
	 */
	abstract protected function query($keyword, $topic_id);

	/**
	 * Returns the priority of the currently selected name
	 *
	 * @param array $row Array of fetched user data
	 * @return int Priority (defaults to 1)
	 */
	public function get_priority($row)
	{
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(array &$names, $keyword, $topic_id)
	{
		$keyword = utf8_clean_string($keyword);

		// Do not query all possible users (just a moderate amount), cache results for 5 minutes
		$result = $this->db->sql_query($this->query($keyword, $topic_id), 300);

		$i = 0;
		$users = [];
		$user_ids = [];
		while ($i < self::NAMES_BATCH_SIZE)
		{
			$row = $this->db->sql_fetchrow($result);

			if (!$row)
			{
				break;
			}

			if (!empty($keyword) && strpos($row['username_clean'], $keyword) !== 0)
			{
				continue;
			}

			$i++;
			$users[] = $row;
			$user_ids[] = $row['user_id'];
		}

		// Load all user data with a single SQL query, needed for ranks and avatars
		$this->user_loader->load_users($user_ids);

		foreach ($users as $user)
		{
			$user_rank = $this->user_loader->get_rank($user['user_id'], true);
			array_push($names, [
				'name'		=> $user['username'],
				'type'		=> 'u',
				'id'		=> $user['user_id'],
				'avatar'	=> [
					'type'	=> 'user',
					'img'	=> $this->user_loader->get_avatar($user['user_id'], true),
				],
				'rank'		=> (isset($user_rank['rank_title'])) ? $user_rank['rank_title'] : '',
				'priority'	=> $this->get_priority($user),
			]);
		}

		$this->db->sql_freeresult($result);
	}
}
