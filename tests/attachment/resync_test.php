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

class phpbb_attachment_resync_test extends \phpbb_database_test_case
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\attachment\resync */
	protected $resync;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/resync.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->resync = new \phpbb\attachment\resync($this->db);
	}

	public function data_resync()
	{
		return array(
			array('', array(1), 'post_id', POSTS_TABLE, array('post_attachment' => '1'), array('post_attachment' => '1')),
			array('post', array(1), 'post_id', POSTS_TABLE, array('post_attachment' => '1'), array('post_attachment' => '1')),
			array('post', array(2), 'post_id', POSTS_TABLE, array('post_attachment' => '1'), array('post_attachment' => '0')),
			array('topic', array(1), 'topic_id', TOPICS_TABLE, array('topic_attachment' => '1'), array('topic_attachment' => '1')),
			array('topic', array(2), 'topic_id', TOPICS_TABLE, array('topic_attachment' => '1'), array('topic_attachment' => '0')),
			array('message', array(1), 'msg_id', PRIVMSGS_TABLE, array('message_attachment' => '1'), array('message_attachment' => '1')),
			array('message', array(2), 'msg_id', PRIVMSGS_TABLE, array('message_attachment' => '1'), array('message_attachment' => '0')),
		);
	}

	/**
	 * @dataProvider data_resync
	 */
	public function test_resync($type, $ids, $sql_id, $exist_table, $exist_data, $resync_data)
	{
		$sql_prefix = ($type) ?: 'post';
		$sql = 'SELECT ' . $sql_prefix . '_attachment
			FROM ' . $exist_table . '
			WHERE ' . $sql_id . ' = ' . $ids[0];
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->assertEquals($exist_data, $data);

		$this->resync->resync($type, $ids);

		$sql = 'SELECT ' . $sql_prefix . '_attachment
			FROM ' . $exist_table . '
			WHERE ' . $sql_id . ' = ' . $ids[0];
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->assertEquals($resync_data, $data);
	}
}
