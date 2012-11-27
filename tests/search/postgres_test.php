<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../test_framework/phpbb_search_test_case.php';

class phpbb_search_postgres_test extends phpbb_search_test_case
{
	protected $db;
	protected $search;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/../fixtures/empty.xml');
	}

	protected function setUp()
	{
		global $phpbb_root_path, $phpEx, $config, $user, $cache;

		parent::setUp();

		// dbal uses cache
		$cache = new phpbb_cache_driver_null;

		//  set config values
		$config['fulltext_postgres_min_word_len'] = 4;
		$config['fulltext_postgres_max_word_len'] = 254;

		$this->db = $this->new_dbal();
		$error = null;
		$class = self::get_search_wrapper('phpbb_search_fulltext_postgres');
		$this->search = new $class($error, $phpbb_root_path, $phpEx, null, $config, $this->db, $user);
	}

	public function keywords()
	{
		return array(
			// keywords
			// terms
			// ok
			// split words
			// common words
			array(
				'fooo',
				'all',
				true,
				array('fooo'),
				array(),
			),
			array(
				'fooo baar',
				'all',
				true,
				array('fooo', 'baar'),
				array(),
			),
			// leading, trailing and multiple spaces
			array(
				'      fooo    baar   ',
				'all',
				true,
				array('fooo', 'baar'),
				array(),
			),
			// words too short
			array(
				'f',
				'all',
				false,
				null,
				// short words count as "common" words
				array('f'),
			),
			array(
				'f o o',
				'all',
				false,
				null,
				array('f', 'o', 'o'),
			),
			array(
				'f -o -o',
				'all',
				false,
				null,
				array('f', '-o', '-o'),
			),
			array(
				'fooo -baar',
				'all',
				true,
				array('-baar', 'fooo'),
				array(),
			),
			// all negative
			array(
				'-fooo',
				'all',
				true,
				array('-fooo'),
				array(),
			),
			array(
				'-fooo -baar',
				'all',
				true,
				array('-fooo', '-baar'),
				array(),
			),
		);
	}

	/**
	* @dataProvider keywords
	*/
	public function test_split_keywords($keywords, $terms, $ok, $split_words, $common)
	{
		$rv = $this->search->split_keywords($keywords, $terms);
		$this->assertEquals($ok, $rv);
		if ($ok)
		{
			// only check criteria if the search is going to be performed
			$this->assert_array_content_equals($split_words, $this->search->get_split_words());
		}
		$this->assert_array_content_equals($common, $this->search->get_common_words());
	}
}
