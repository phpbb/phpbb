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
	protected $search_backend = 'phpbb\search\backend\fulltext_sphinx';

	protected function create_search_index($backend = null)
	{
		parent::create_search_index($backend);
		$this->purge_cache();

		if (!$backend || $this->search_backend == $backend)
		{
			// After creating phpBB search index, build Sphinx index
			exec('sudo -S service sphinxsearch stop', $output, $retval); // Attemtp to stop sphinxsearch service in case it's running
			exec('sudo -S indexer --all', $output, $retval); // Run sphinxsearch indexer
			exec('sudo -S service sphinxsearch start', $output, $retval); // Attempt to start sphinxsearch service again
		}
	}

	public function test_search_backend()
	{
		if ($this->db->sql_layer != 'mysqli') // Sphinx test runs on MySQL/MariaDB only so far
		{
			$this->markTestIncomplete('Sphinx Tests are not supported');
		}
		else
		{
			parent::test_search_backend();
		}
	}
}
