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

require_once __DIR__ . '/../../phpBB/includes/functions_mcp.php';

class phpbb_mcp_get_post_data_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/queue_orphaned_poster.xml');
	}

	protected function setUp(): void
	{
		global $db, $auth, $config, $user, $phpbb_dispatcher, $phpbb_container;

		parent::setUp();

		$db = $this->new_dbal();
		$config = new \phpbb\config\config(array('load_db_lastread' => 0));
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		$user = new \phpbb\user(new \phpbb\language\language(new \phpbb\language\language_file_loader(__DIR__ . '/../../phpBB/', 'php')), '\phpbb\datetime');
		$user->data['user_id'] = 2;

		$auth = $this->createMock('\phpbb\auth\auth');
		$auth->method('acl_gets')->willReturn(true);

		$content_visibility = $this->createMock('\phpbb\content_visibility');
		$content_visibility->method('is_visible')->willReturn(true);

		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('content.visibility', $content_visibility);
	}

	public function test_existing_poster()
	{
		$post_data = phpbb_get_post_data(array(1));

		$this->assertCount(1, $post_data);
		$this->assertEquals(2, $post_data[1]['poster_id']);
		$this->assertEquals(2, $post_data[1]['user_id']);
		$this->assertEquals('admin', $post_data[1]['username']);
		$this->assertEquals('AA0000', $post_data[1]['user_colour']);
		$this->assertEquals('Topic', $post_data[1]['topic_title']);
		$this->assertEquals('Forum', $post_data[1]['forum_name']);
	}

	public function data_orphaned_poster()
	{
		return array(
			array(false),
			array(true),
		);
	}

	/**
	* @dataProvider data_orphaned_poster
	*/
	public function test_orphaned_poster($read_tracking)
	{
		global $config;

		$config['load_db_lastread'] = (int) $read_tracking;

		$post_data = phpbb_get_post_data(array(1, 2), 'm_approve', $read_tracking);

		$this->assertCount(2, $post_data);

		// Post by user that no longer exists is returned as guest post
		$this->assertEquals(ANONYMOUS, $post_data[2]['poster_id']);
		$this->assertEquals(ANONYMOUS, $post_data[2]['user_id']);
		$this->assertEquals('Anonymous', $post_data[2]['username']);
		$this->assertEquals('Post by deleted user', $post_data[2]['post_subject']);
		$this->assertEquals(1, $post_data[2]['topic_id']);
		$this->assertEquals(2, $post_data[2]['forum_id']);
		$this->assertEquals('Topic', $post_data[2]['topic_title']);
		$this->assertEquals('Forum', $post_data[2]['forum_name']);

		// Other posts are not affected
		$this->assertEquals(2, $post_data[1]['poster_id']);
		$this->assertEquals('admin', $post_data[1]['username']);
	}
}
