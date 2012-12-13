<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../test_framework/phpbb_search_test_case.php';

class phpbb_search_native_test extends phpbb_search_test_case
{
	protected $db;
	protected $search;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/posts.xml');
	}

	protected function setUp()
	{
		global $phpbb_root_path, $phpEx, $config, $user, $cache;

		parent::setUp();

		// dbal uses cache
		$cache = new phpbb_mock_cache();

		$this->db = $this->new_dbal();
		$error = null;
		$class = self::get_search_wrapper('phpbb_search_fulltext_native');
		$this->search = new $class($error, $phpbb_root_path, $phpEx, null, $config, $this->db, $user);
	}

	public function keywords()
	{
		return array(
			// keywords
			// terms
			// ok
			// must contain ids
			// must not contain ids
			// common words
			array(
				'foo',
				'all',
				true,
				array(1),
				array(),
				array(),
			),
			array(
				'foo bar',
				'all',
				true,
				array(1, 2),
				array(),
				array(),
			),
			// leading, trailing and multiple spaces
			array(
				'      foo    bar   ',
				'all',
				true,
				array(1, 2),
				array(),
				array(),
			),
			// words too short
			array(
				'f',
				'all',
				false,
				null,
				null,
				// short words count as "common" words
				array('f'),
			),
			array(
				'f o o',
				'all',
				false,
				null,
				null,
				array('f', 'o', 'o'),
			),
			array(
				'f -o -o',
				'all',
				false,
				null,
				null,
				array('f', 'o', 'o'),
			),
			array(
				'foo -bar',
				'all',
				true,
				array(1),
				array(2),
				array(),
			),
			// all negative
			array(
				'-foo',
				'all',
				false,
				null,
				null,
				array(),
			),
			array(
				'-foo -bar',
				'all',
				false,
				null,
				null,
				array(),
			),
			// all common
			array(
				'commonword',
				'all',
				false,
				null,
				null,
				array('commonword'),
			),
			// some common
			array(
				'commonword foo',
				'all',
				true,
				array(1),
				array(),
				array('commonword'),
			),
		);
	}

	/**
	* @dataProvider keywords
	*/
	public function test_split_keywords($keywords, $terms, $ok, $must_contain, $must_not_contain, $common)
	{
		$rv = $this->search->split_keywords($keywords, $terms);
		$this->assertEquals($ok, $rv);
		if ($ok)
		{
			// only check criteria if the search is going to be performed
			$this->assert_array_content_equals($must_contain, $this->search->get_must_contain_ids());
			$this->assert_array_content_equals($must_not_contain, $this->search->get_must_not_contain_ids());
		}
		$this->assert_array_content_equals($common, $this->search->get_common_words());
	}
}
