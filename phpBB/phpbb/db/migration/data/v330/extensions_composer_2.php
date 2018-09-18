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

class extensions_composer_2 extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		$repositories = json_decode($this->config['exts_composer_repositories'], true);
		$repositories[] = 'https://satis.phpbb.com';
		$repositories = array_unique($repositories);

		return array(
			array('config.update', array('exts_composer_repositories', json_encode($repositories, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))),
		);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v330\extensions_composer');
	}
}
