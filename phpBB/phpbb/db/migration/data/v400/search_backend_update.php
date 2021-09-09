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

namespace phpbb\db\migration\data\v400;

use phpbb\search\backend\fulltext_mysql;
use phpbb\search\backend\fulltext_postgres;
use phpbb\search\backend\fulltext_sphinx;
use phpbb\search\backend\fulltext_native;

class search_backend_update extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
		];
	}

	public function update_data()
	{
		switch ($this->config['search_type'])
		{
			case '\\phpbb\\search\\fulltext_mysql':
				$new_search_type = fulltext_mysql::class;
			break;
			case '\\phpbb\\search\\fulltext_postgres':
				$new_search_type = fulltext_postgres::class;
			break;
			case '\\phpbb\\search\\fulltext_sphinx':
				$new_search_type = fulltext_sphinx::class;
			break;
			default:
				$new_search_type = fulltext_native::class;
		}

		return [
			['config.update', ['search_type', $new_search_type]],
		];
	}
}
