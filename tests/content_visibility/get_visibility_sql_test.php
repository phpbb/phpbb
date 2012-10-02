<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_content_visibility_get_visibility_sql_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/get_visibility_sql.xml');
	}

	public function get_visibility_sql_data()
	{
		return array(
			array(
				'phpbb_posts',
				'post', 1, '',
				array(
					array('m_approve', 1, true),
					array('m_restore', 1, true),
				),
				array(
					array('post_id' => 1),
					array('post_id' => 2),
					array('post_id' => 3),
				),
			),
			array(
				'phpbb_posts',
				'post', 1, '',
				array(
					array('m_approve', 1, true),
				),
				array(
					array('post_id' => 1),
					array('post_id' => 2),
				),
			),
			array(
				'phpbb_posts',
				'post', 1, '',
				array(
					array('m_restore', 1, true),
				),
				array(
					array('post_id' => 2),
					array('post_id' => 3),
				),
			),
			array(
				'phpbb_posts',
				'post', 1, '',
				array(
				),
				array(
					array('post_id' => 2),
				),
			),
			array(
				'phpbb_topics',
				'topic', 1, '',
				array(
					array('m_approve', 1, true),
					array('m_restore', 1, true),
				),
				array(
					array('topic_id' => 1),
					array('topic_id' => 2),
					array('topic_id' => 3),
				),
			),
			array(
				'phpbb_topics',
				'topic', 1, '',
				array(
					array('m_approve', 1, true),
				),
				array(
					array('topic_id' => 1),
					array('topic_id' => 2),
				),
			),
			array(
				'phpbb_topics',
				'topic', 1, '',
				array(
					array('m_restore', 1, true),
				),
				array(
					array('topic_id' => 2),
					array('topic_id' => 3),
				),
			),
			array(
				'phpbb_topics',
				'topic', 1, '',
				array(),
				array(
					array('topic_id' => 2),
				),
			),
		);
	}

	/**
	* @dataProvider get_visibility_sql_data
	*/
	public function test_get_visibility_sql($table, $mode, $forum_id, $table_alias, $permissions, $expected)
	{
		global $db, $auth;

		$db = $this->new_dbal();

		// Create auth mock
		$auth = $this->getMock('phpbb_auth');
		$auth->expects($this->any())
			->method('acl_get')
			->with($this->stringContains('_'), $this->anything())
			->will($this->returnValueMap($permissions));

		$result = $db->sql_query('SELECT ' . $mode . '_id
			FROM ' . $table . '
			WHERE ' . phpbb_content_visibility::get_visibility_sql($mode, $forum_id, $table_alias) . '
			ORDER BY ' . $mode . '_id ASC');

		$this->assertEquals($expected, $db->sql_fetchrowset($result));
	}
}
