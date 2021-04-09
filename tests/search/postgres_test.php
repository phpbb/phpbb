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

require_once __DIR__ . '/common_test_case.php';

class phpbb_search_postgres_test extends phpbb_search_common_test_case
{
	protected $db;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/../fixtures/empty.xml');
	}

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpEx, $config, $cache;

		parent::setUp();

		// dbal uses cache
		$cache = $this->createMock('\phpbb\cache\service');
		$language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$user = $this->createMock('\phpbb\user');

		//  set config values
		$config['fulltext_postgres_min_word_len'] = 4;
		$config['fulltext_postgres_max_word_len'] = 254;

		$this->db = $this->new_dbal();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$class = self::get_search_wrapper('\phpbb\search\backend\fulltext_postgres');
		$this->search = new $class($config, $this->db, $phpbb_dispatcher, $language, $user, SEARCH_RESULTS_TABLE, $phpbb_root_path, $phpEx);
	}
}
