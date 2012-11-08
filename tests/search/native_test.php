<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

function phpbb_search_wrapper($class)
{
	$wrapped = $class . '_wrapper';
	if (!class_exists($wrapped))
	{
		$code = "
class $wrapped extends $class
{
	public function get_must_contain_ids() { return \$this->must_contain_ids; }
	public function get_must_not_contain_ids() { return \$this->must_not_contain_ids; }
}
		";
		eval($code);
	}
	return $wrapped;
}

class phpbb_search_native_test extends phpbb_database_test_case
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
		$cache = new phpbb_cache_driver_null;

		$this->db = $this->new_dbal();
		$error = null;
		$class = phpbb_search_wrapper('phpbb_search_fulltext_native');
		$this->search = new $class($error, $phpbb_root_path, $phpEx, null, $config, $this->db, $user);
	}

	protected function tearDown()
	{
		parent::tearDown();
	}

	public function keywords()
	{
		return array(
			// keywords
			// terms
			// ok
			// must contain ids
			// must not contain ids
			array(
				'foo',
				'all',
				true,
				array(1),
				array(),
			),
			array(
				'foo bar',
				'all',
				true,
				array(1, 2),
				array(),
			),
			array(
				'foo -bar',
				'all',
				true,
				array(1),
				array(2),
			),
			array(
				'-foo',
				'all',
				false,
				array(),
				array(),
			),
			array(
				'-foo -bar',
				'all',
				false,
				array(),
				array(),
			),
		);
	}

	/**
	* @dataProvider keywords
	*/
	public function test_split_keywords($keywords, $terms, $ok, $must_contain, $must_not_contain)
	{
		$rv = $this->search->split_keywords($keywords, $terms);
		$this->assertEquals($ok, $rv);
		// http://stackoverflow.com/questions/3838288/phpunit-assert-two-arrays-are-equal-but-order-of-elements-not-important
		$this->assertEmpty(array_diff($must_contain, $this->search->get_must_contain_ids()));
		$this->assertEmpty(array_diff($must_not_contain, $this->search->get_must_not_contain_ids()));
	}
}
