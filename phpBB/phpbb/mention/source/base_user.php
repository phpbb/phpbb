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
	 * {@inheritdoc}
	 */
	public function get_priority($row)
	{
		// By default every result from the source increases the priority by a fixed value
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(array &$names, $keyword, $topic_id)
	{
		$fetched_all = false;
		$keyword = utf8_clean_string($keyword);

		// Grab all necessary user IDs, cache results for 5 minutes
		$result = $this->db->sql_query($this->query($keyword, $topic_id), 300);

		$i = 0;
		$users = [];
		$user_ids = [];
		while ($i < $this->config['mention_batch_size'])
		{
			$row = $this->db->sql_fetchrow($result);

			if (!$row)
			{
				$fetched_all = true;
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

		// Determine whether all usernames were fetched in current batch
		if (!$fetched_all)
		{
			$fetched_all = true;

			while ($row = $this->db->sql_fetchrow($result))
			{
				if (!empty($keyword) && strpos($row['username_clean'], $keyword) !== 0)
				{
					continue;
				}

				// At least one username hasn't been fetched - exit loop
				$fetched_all = false;
				break;
			}
		}

		$this->db->sql_freeresult($result);

		// Load all user data with a single SQL query, needed for ranks and avatars
		$this->user_loader->load_users($user_ids);

		foreach ($users as $user)
		{
			$user_rank = $this->user_loader->get_rank($user['user_id']);
			array_push($names, [
				'name'		=> $this->user_loader->get_username($user['user_id'], 'username'),
				'type'		=> 'u',
				'id'		=> $user['user_id'],
				'avatar'	=> [
					'type'	=> 'user',
					'img'	=> $this->user_loader->get_avatar($user['user_id']),
				],
				'rank'		=> (isset($user_rank['rank_title'])) ? $user_rank['rank_title'] : '',
				'priority'	=> $this->get_priority($user),
			]);
		}

		return $fetched_all;
	}
}
