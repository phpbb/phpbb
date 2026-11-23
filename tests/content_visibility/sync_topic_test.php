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

require_once __DIR__ . '/../../phpBB/includes/functions_admin.php';

class phpbb_content_visibility_sync_topic_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/delete_post.xml');
	}

	public function test_sync_counts_unapproved_and_reapprove_posts()
	{
		global $db, $phpbb_dispatcher;

		$db = $this->new_dbal();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		// Topic 3 already contains one unapproved post in the fixture.
		$sql = 'INSERT INTO ' . POSTS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'post_id' => 7,
			'topic_id' => 3,
			'forum_id' => 3,
			'poster_id' => 1,
			'post_visibility' => ITEM_REAPPROVE,
			'post_time' => 5,
			'post_text' => 'Edited post awaiting reapproval',
		));
		$db->sql_query($sql);

		sync('topic', 'topic_id', 3);

		$result = $db->sql_query('SELECT topic_posts_unapproved FROM ' . TOPICS_TABLE . ' WHERE topic_id = 3');
		$this->assertEquals(2, (int) $db->sql_fetchfield('topic_posts_unapproved'));
		$db->sql_freeresult($result);
	}
}
