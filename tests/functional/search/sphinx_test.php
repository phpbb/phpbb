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

require_once __DIR__ . '/base.php';

/**
* @group functional
*/
class phpbb_functional_search_sphinx_test extends phpbb_functional_search_base
{
	protected $search_backend = '\phpbb\search\fulltext_sphinx';

	protected function setUp(): void
	{
		$sql_layer = substr(self::$config['dbms'], strlen('phpbb\\db\\driver\\'));

		// Sphinx test runs on Linux with MySQL/MariaDB only so far
		if ($sql_layer !== 'mysqli' || strtolower(substr(PHP_OS, 0, 3)) === 'win')
		{
			$this->markTestSkipped($sql_layer . ': Sphinx search is not supported');
		}

		// Check if the sphinx indexer command exists before proceeding with Sphinx-related tests
		$indexer_check = [];
		exec('which indexer', $indexer_check);
		if (empty($indexer_check[0]))
		{
			$this->markTestSkipped('Sphinx indexer command is not installed or not found');
		}

		parent::setUp();
	}

	protected function tearDown(): void
	{
		// Reset search backend to default after test
		parent::delete_search_index();
		parent::create_search_index('\phpbb\search\fulltext_native');

		parent::tearDown();
	}

	protected function create_search_index($backend = null)
	{
		parent::create_search_index($backend);
		$this->purge_cache();

		if (!$backend || $this->search_backend == $backend)
		{
			exec('indexer --all --rotate');
		}
	}
}
