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

namespace phpbb\db\migration\data\v330;

class search_backend_update extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		switch ($this->config['search_type'])
		{
			case '\\phpbb\\search\\fulltext_mysql':
				$new_search_type = 'phpbb\\search\\backend\\fulltext_mysql';
				break;
			case '\\phpbb\\search\\fulltext_postgres':
				$new_search_type = 'phpbb\\search\\backend\\fulltext_postgres';
				break;
			case '\\phpbb\\search\\fulltext_sphinx':
				$new_search_type = 'phpbb\\search\\backend\\fulltext_sphinx';
				break;
			default:
				$new_search_type = 'phpbb\\search\\backend\\fulltext_native';
		}

		return [
			['config.update', ['search_type', $new_search_type]],
		];
	}
}
