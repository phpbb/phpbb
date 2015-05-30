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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_tests_tree_nestedset_forum_base extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/phpbb_forums.xml');
	}

	protected $forum_data = array(
		// \__/
		1	=> array('forum_id' => 1, 'parent_id' => 0, 'user_id' => 0, 'left_id' => 1, 'right_id' => 6, 'forum_parents' => 'a:0:{}'),
		2	=> array('forum_id' => 2, 'parent_id' => 1, 'user_id' => 0, 'left_id' => 2, 'right_id' => 3, 'forum_parents' => 'a:0:{}'),
		3	=> array('forum_id' => 3, 'parent_id' => 1, 'user_id' => 0, 'left_id' => 4, 'right_id' => 5, 'forum_parents' => 'a:0:{}'),

		// \  /
		//  \/
		4	=> array('forum_id' => 4, 'parent_id' => 0, 'user_id' => 0, 'left_id' => 7, 'right_id' => 12, 'forum_parents' => 'a:0:{}'),
		5	=> array('forum_id' => 5, 'parent_id' => 4, 'user_id' => 0, 'left_id' => 8, 'right_id' => 11, 'forum_parents' => 'a:0:{}'),
		6	=> array('forum_id' => 6, 'parent_id' => 5, 'user_id' => 0, 'left_id' => 9, 'right_id' => 10, 'forum_parents' => 'a:0:{}'),

		// \_  _/
		//   \/
		7	=> array('forum_id' => 7, 'parent_id' => 0, 'user_id' => 0, 'left_id' => 13, 'right_id' => 22, 'forum_parents' => 'a:0:{}'),
		8	=> array('forum_id' => 8, 'parent_id' => 7, 'user_id' => 0, 'left_id' => 14, 'right_id' => 15, 'forum_parents' => 'a:0:{}'),
		9	=> array('forum_id' => 9, 'parent_id' => 7, 'user_id' => 0, 'left_id' => 16, 'right_id' => 19, 'forum_parents' => 'a:0:{}'),
		10	=> array('forum_id' => 10, 'parent_id' => 9, 'user_id' => 0, 'left_id' => 17, 'right_id' => 18, 'forum_parents' => 'a:0:{}'),
		11	=> array('forum_id' => 11, 'parent_id' => 7, 'user_id' => 0, 'left_id' => 20, 'right_id' => 21, 'forum_parents' => 'a:0:{}'),

		// Non-existent forums
		0	=> array('forum_id' => 0, 'parent_id' => 0, 'user_id' => 0, 'left_id' => 0, 'right_id' => 0, 'forum_parents' => 'a:0:{}'),
		200	=> array('forum_id' => 200, 'parent_id' => 0, 'user_id' => 0, 'left_id' => 0, 'right_id' => 0, 'forum_parents' => 'a:0:{}'),
	);

	protected $set,
		$config,
		$lock,
		$db;

	public function setUp()
	{
		parent::setUp();

		$this->db = $this->new_dbal();

		global $config;

		$config = $this->config = new \phpbb\config\config(array('nestedset_forum_lock' => 0));

		$this->lock = new \phpbb\lock\db('nestedset_forum_lock', $this->config, $this->db);
		$this->set = new \phpbb\tree\nestedset_forum($this->db, $this->lock, 'phpbb_forums');

		$this->set_up_forums();
	}

	protected function set_up_forums()
	{
		static $forums;

		if (empty($forums))
		{ 
			$this->create_forum('Parent with two flat children');
			$this->create_forum('Flat child #1', 1);
			$this->create_forum('Flat child #2', 1);

			$this->create_forum('Parent with two nested children');
			$this->create_forum('Nested child #1', 4);
			$this->create_forum('Nested child #2', 5);

			$this->create_forum('Parent with flat and nested children');
			$this->create_forum('Mixed child #1', 7);
			$this->create_forum('Mixed child #2', 7);
			$this->create_forum('Nested child #1 of Mixed child #2', 9);
			$this->create_forum('Mixed child #3', 7);

			// Updating forum_parents column here so it's not empty
			// This is required, so we can see whether the methods
			// correctly clear the values. 
			$sql = "UPDATE phpbb_forums
				SET forum_parents = 'a:0:{}'";
			$this->db->sql_query($sql);

			// Copy the forums into a static array, so we can reuse the list later
			$sql = 'SELECT *
				FROM phpbb_forums';
			$result = $this->db->sql_query($sql);
			$forums = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);
		}
		else
		{
			$buffer = new \phpbb\db\sql_insert_buffer($this->db, 'phpbb_forums');
			$buffer->insert_all($forums);
			$buffer->flush();

			$this->database_synchronisation(array(
				'phpbb_forums'	=> array('forum_id'),
			));
		} 
	}

	protected function create_forum($name, $parent_id = 0)
	{
		$forum = $this->set->insert(array('forum_name' => $name, 'forum_desc' => '', 'forum_rules' => ''));
		$this->set->change_parent($forum['forum_id'], $parent_id);
	}
}
