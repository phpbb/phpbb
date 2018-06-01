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

abstract class group implements source_interface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\group\helper */
	protected $helper;

	/**
	 * Constructor
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\group\helper $helper)
	{
		$this->db = $db;
		$this->helper = $helper;
	}

	/**
	 * Returns data for all board groups
	 *
	 * @return array Array of groups' data
	 */
	protected function get_groups()
	{
		static $groups = null;

		if (is_null($groups))
		{
			$query = $this->db->sql_build_query('SELECT', [
				'SELECT' => 'g.*',
				'FROM'   => [
					GROUPS_TABLE => 'g',
				],
			]);
			$res = $this->db->sql_query($query);

			$groups = [];
			while ($row = $this->db->sql_fetchrow($res))
			{
				$group_name = $this->helper->get_name($row['group_name']);
				$groups['names'][$row['group_id']] = $group_name;
				$groups[$row['group_id']] = $row;
				$groups[$row['group_id']]['group_name'] = $group_name;
			}
		}
		return $groups;
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
	public function get($keyword, $topic_id)
	{
		// Grab all group IDs
		$res = $this->db->sql_query($this->query($keyword, $topic_id));

		$group_ids = [];
		while ($row = $this->db->sql_fetchrow($res))
		{
			$group_ids[] = $row['group_id'];
		}

		// Grab group data
		$groups = $this->get_groups();

		$matches = preg_grep('/^' . $keyword . '.*/i', $groups['names']);
		$group_ids = array_intersect($group_ids, array_flip($matches));

		$names = [];
		foreach ($group_ids as $group_id)
		{
			$names['g' . $group_id] = [
				'name'		=> $groups[$group_id]['group_name'],
				'param'		=> 'group_id',
				'id'		=> $group_id,
				'avatar'	=> [
					'type'	=> 'group',
					'src'	=> phpbb_get_group_avatar($groups[$group_id]),
				],
			];
		}

		return $names;
	}
}
