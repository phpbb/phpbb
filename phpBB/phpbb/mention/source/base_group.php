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

abstract class base_group implements source_interface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\group\helper */
	protected $helper;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var array Fetched groups' data */
	protected $groups = null;

	/**
	 * Constructor
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\group\helper $helper, \phpbb\user $user, \phpbb\auth\auth $auth, $phpbb_root_path, $phpEx)
	{
		$this->db = $db;
		$this->helper = $helper;
		$this->user = $user;
		$this->auth = $auth;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		if (!function_exists('phpbb_get_user_rank'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
		}
	}

	/**
	 * Returns data for all board groups
	 *
	 * @return array Array of groups' data
	 */
	protected function get_groups()
	{
		if (is_null($this->groups))
		{
			$query = $this->db->sql_build_query('SELECT', [
				'SELECT' => 'g.*, ug.user_id as ug_user_id',
				'FROM'   => [
					GROUPS_TABLE => 'g',
				],
				'LEFT_JOIN'	=> [
					[
						'FROM'	=> [USER_GROUP_TABLE => 'ug'],
						'ON'	=> 'ug.group_id = g.group_id AND ug.user_pending = 0 AND ug.user_id = ' . (int) $this->user->data['user_id'],
					],
				],
			]);
			// Cache results for 5 minutes
			$result = $this->db->sql_query($query, 600);

			$this->groups = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				if ($row['group_type'] == GROUP_SPECIAL && !in_array($row['group_name'], ['ADMINISTRATORS', 'GLOBAL_MODERATORS']) || $row['group_type'] == GROUP_HIDDEN && !$this->auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel') && $row['ug_user_id'] != $this->user->data['user_id'])
				{
					// Skip the group that we should not be able to mention.
					continue;
				}

				$group_name = $this->helper->get_name($row['group_name']);
				$this->groups['names'][$row['group_id']] = $group_name;
				$this->groups[$row['group_id']] = $row;
				$this->groups[$row['group_id']]['group_name'] = $group_name;
			}

			$this->db->sql_freeresult($result);
		}
		return $this->groups;
	}

	/**
	 * Builds a query for getting group IDs based on user input
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
		// Grab all group IDs, cache for 5 minutes
		$result = $this->db->sql_query($this->query($keyword, $topic_id), 300);

		$group_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$group_ids[] = $row['group_id'];
		}

		$this->db->sql_freeresult($result);

		// Grab group data
		$groups = $this->get_groups();

		$matches = preg_grep('/^' . preg_quote($keyword) . '.*/i', $groups['names']);
		$group_ids = array_intersect($group_ids, array_flip($matches));

		foreach ($group_ids as $group_id)
		{
			$group_rank = phpbb_get_user_rank($groups[$group_id], false);
			array_push($names, [
				'name'		=> $groups[$group_id]['group_name'],
				'type'		=> 'g',
				'id'		=> $group_id,
				'avatar'	=> [
					'type'	=> 'group',
					'img'	=> phpbb_get_group_avatar($groups[$group_id]),
				],
				'rank'		=> (isset($group_rank['title'])) ? $group_rank['title'] : '',
				'priority'	=> $this->get_priority($groups[$group_id]),
			]);
		}
	}
}