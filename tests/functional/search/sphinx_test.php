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

	protected function create_search_index($backend = null)
	{
		parent::create_search_index($backend);
		$this->purge_cache();

		if (!$backend || $this->search_backend == $backend)
		{
			$commands = [
				'service sphinxsearch stop', // Attempt to stop sphinxsearch service in case it's running
				'indexer --all', // Run sphinxsearch indexer
				'service sphinxsearch start', // Attempt to start sphinxsearch service again
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

	public function test_search_backend()
	{
		// Sphinx test runs on Linux with MySQL/MariaDB only so far
		if ($this->db->sql_layer != 'mysqli' || strtolower(substr(PHP_OS, 0, 3)) === 'win')
		{
			$this->markTestIncomplete('Sphinx Tests are not supported');
		}
		else
		{
			parent::test_search_backend();
		}
	}
}
