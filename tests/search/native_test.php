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

require_once __DIR__ . '/../test_framework/phpbb_search_test_case.php';

class phpbb_search_native_test extends phpbb_search_test_case
{
	protected $db;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/posts.xml');
	}

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpEx, $config, $cache;

		parent::setUp();

		// dbal uses cache
		$cache = $this->createMock('\phpbb\cache\service');
		$language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$user = $this->createMock('\phpbb\user');

		$this->db = $this->new_dbal();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$class = self::get_search_wrapper('\phpbb\search\backend\fulltext_native');
		$config['fulltext_native_min_chars'] = 2;
		$config['fulltext_native_max_chars'] = 14;
		$this->search = new $class($config, $this->db, $phpbb_dispatcher, $language, $user, $phpbb_root_path, $phpEx);
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
				'baaz*',
				'all',
				true,
				array('\'baaz%\''),
				array(),
				array(),
			),
			array(
				'ba*az',
				'all',
				true,
				array(4),
				array(),
				array(),
			),
			array(
				'ba*z',
				'all',
				true,
				array(), // <= 3 chars after removing *
				array(),
				array(),
			),
			array(
				'baa* baaz*',
				'all',
				true,
				array('\'baa%\'', 4),
				array(),
				array(),
			),
			array(
				'ba*z baa*',
				'all',
				true,
				array('\'baa%\''), // baz is <= 3 chars, only baa* is left
				array(),
				array(),
			),
			array(
				'baaz* commonword',
				'all',
				true,
				array('\'baaz%\''),
				array(),
				array('commonword'),
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
				true,
				array(),
				array(1),
				array(),
			),
			array(
				'-foo -bar',
				'all',
				true,
				array(),
				array(1, 2),
				array(),
			),
			array(
				'foo -foo',
				'all',
				true,
				array(1),
				array(1),
				array(),
			),
			array(
				'-foo foo',
				'all',
				true,
				array(1),
				array(1),
				array(),
			),
			// some creative edge cases
			array(
				'foo foo-',
				'all',
				true,
				array(1, 1),
				array(),
				array(),
			),
			array(
				'foo- foo',
				'all',
				true,
				array(1, 1),
				array(),
				array(),
			),
			array(
				'foo-bar',
				'all',
				true,
				array(1, 2),
				array(),
				array(),
			),
			array(
				'foo-bar-foo',
				'all',
				true,
				array(1, 2, 1),
				array(),
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
