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
class phpbb_functional_search_postgres_test extends phpbb_functional_search_base
{
	protected $search_backend = 'phpbb\search\backend\fulltext_postgres';

	protected function setUp(): void
	{
		$sql_layer = substr(self::$config['dbms'], strlen('phpbb\\db\\driver\\'));
		if ($sql_layer !== 'postgres') // PostgreSQL search backend runs on PostgreSQL only
		{
			$this->markTestSkipped($sql_layer . ': PostgreSQL search is not supported');
		}

		parent::setUp();
	}
}
