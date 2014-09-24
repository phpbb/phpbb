<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/cache_memory.php';

class phpbb_cache_memory_test extends phpbb_database_test_case
{
	protected $cache;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/cache_memory.xml');
	}

	protected function setUp()
	{
		global $db;
		parent::setUp();

		$this->cache = new phpbb_cache_memory();
		$db = $this->new_dbal();
	}

	static public function cache_single_query_data()
	{
		return array(
			array(
				array(
					array(
						'SELECT * FROM ' . POSTS_TABLE,
						3,
					),
				),
				POSTS_TABLE,
			),
			array(
				array(
					array(
						'SELECT * FROM ' . POSTS_TABLE,
						3,
					),
					array(
						'SELECT * FROM ' . POSTS_TABLE . ' p
							LEFT JOIN ' . TOPICS_TABLE . ' t ON p.topic_id = t.topic_id',
						3,
					),
				),
				POSTS_TABLE,
			),
			array(
				array(
					array(
						'SELECT * FROM ' . POSTS_TABLE,
						3,
					),
					array(
						'SELECT * FROM ' . POSTS_TABLE . ' p
							LEFT JOIN ' . TOPICS_TABLE . ' t ON p.topic_id = t.topic_id',
						3,
					),
					array(
						'SELECT * FROM ' . POSTS_TABLE . ' p
							LEFT JOIN ' . TOPICS_TABLE . ' t ON p.topic_id = t.topic_id
							LEFT JOIN ' . USERS_TABLE . ' u ON p.poster_id = u.user_id',
						3,
					),
				),
				POSTS_TABLE,
			),
			array(
				array(
					array(
						'SELECT * FROM ' . POSTS_TABLE . ' p
							LEFT JOIN ' . TOPICS_TABLE . ' t ON p.topic_id = t.topic_id',
						3,
					),
					array(
						'SELECT * FROM ' . POSTS_TABLE . ' p
							LEFT JOIN ' . TOPICS_TABLE . ' t ON p.topic_id = t.topic_id
							LEFT JOIN ' . USERS_TABLE . ' u ON p.poster_id = u.user_id',
						3,
					),
				),
				TOPICS_TABLE,
			),
		);
	}

	/**
	* @dataProvider cache_single_query_data
	*/
	public function test_cache_single_query($sql_queries, $table)
	{
		global $db;

		foreach ($sql_queries as $query)
		{
			$sql_request_res = $db->sql_query($query[0]);

			$this->cache->sql_save($query[0], $sql_request_res, 1);

			$results = array();
			$query_id = $this->cache->sql_load($query[0]);
			while ($row = $this->cache->sql_fetchrow($query_id))
			{
				$results[] = $row;
			}
			$this->cache->sql_freeresult($query_id);
			$this->assertEquals($query[1], sizeof($results));
		}

		$this->cache->destroy('sql', $table);

		foreach ($sql_queries as $query)
		{
			$this->assertNotEquals(false, $this->cache->sql_load($query[0]));
		}
	}
}
