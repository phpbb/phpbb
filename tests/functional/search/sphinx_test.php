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
			$commands = [
				'indexer --all --rotate', // Run sphinxsearch indexer
			];

			foreach ($commands as $command)
			{
				$output = $retval = null;

				exec($command, $output, $retval);

				if ($retval !== 0)
				{
					$this->markTestIncomplete("Running sphinx indexer not possible. Command '$command' failed with return value $retval. Output: " . implode("\n", $output));
				}
			}
		}
	}
}
