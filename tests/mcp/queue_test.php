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

class phpbb_mcp_queue_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/queue_orphaned_poster.xml');
	}

	public function data_unapproved_posts_query()
	{
		// Sort orders available in the MCP for unapproved posts
		return array(
			array('p.post_time DESC, p.post_id DESC', array(2, 1)),
			array('p.post_time ASC, p.post_id ASC', array(1, 2)),
			array('p.post_subject ASC', array(2, 1)),
			array('u.username_clean ASC', null),
			array('u.username_clean DESC', null),
		);
	}

	/**
	* Posts whose poster no longer exists in the users table must still be
	* returned by the unapproved posts query in mcp_queue::main()
	*
	* @dataProvider data_unapproved_posts_query
	*/
	public function test_unapproved_posts_query($sort_order_sql, $expected_order)
	{
		$db = $this->new_dbal();

		$sql = $db->sql_build_query('SELECT', array(
			'SELECT'	=> 't.topic_id, t.topic_title, t.forum_id, p.post_id, p.post_subject, p.post_username, p.poster_id, p.post_time, p.post_attachment, u.username, u.username_clean, u.user_colour',
			'FROM'		=> array(
				POSTS_TABLE		=> 'p',
				TOPICS_TABLE	=> 't',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'u.user_id = p.poster_id',
				),
			),
			'WHERE'		=> $db->sql_in_set('p.post_id', array(1, 2)) . '
				AND t.topic_id = p.topic_id',
			'ORDER_BY'	=> $sort_order_sql,
		));
		$result = $db->sql_query($sql);

		$post_data = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$post_data[(int) $row['post_id']] = $row;
		}
		$db->sql_freeresult($result);

		$this->assertCount(2, $post_data);

		if ($expected_order !== null)
		{
			$this->assertSame($expected_order, array_keys($post_data));
		}

		$this->assertEquals('admin', $post_data[1]['username']);
		$this->assertEquals('AA0000', $post_data[1]['user_colour']);
		$this->assertEquals(1, $post_data[1]['topic_id']);
		$this->assertEquals(2, $post_data[1]['forum_id']);

		$this->assertEquals(999, $post_data[2]['poster_id']);
		$this->assertEmpty($post_data[2]['username']);
		$this->assertEmpty($post_data[2]['user_colour']);
		$this->assertEquals('Topic', $post_data[2]['topic_title']);
		$this->assertEquals(1, $post_data[2]['topic_id']);
		$this->assertEquals(2, $post_data[2]['forum_id']);
	}
}
