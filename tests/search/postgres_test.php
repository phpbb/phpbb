<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/common_test_case.php';

class phpbb_search_postgres_test extends phpbb_search_common_test_case
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
		$cache = new phpbb_mock_cache();

		//  set config values
		$config['fulltext_postgres_min_word_len'] = 4;
		$config['fulltext_postgres_max_word_len'] = 254;

		$this->db = $this->new_dbal();
		$error = null;
		$class = self::get_search_wrapper('\phpbb\search\fulltext_postgres');
		$this->search = new $class($error, $phpbb_root_path, $phpEx, null, $config, $this->db, $user);
	}
}
