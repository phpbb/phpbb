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

use phpbb\db\migration\migration;

class extensions_composer_2 extends migration
{
	public function effectively_installed()
	{
		return strpos($this->config['exts_composer_repositories'], 'https://satis.phpbb.com') !== false;
	}

	public function update_data()
	{
		$repositories = json_decode($this->config['exts_composer_repositories'], true);
		$repositories[] = 'https://satis.phpbb.com';
		$repositories = array_unique($repositories);

		return [
			['config.update', ['exts_composer_repositories', json_encode($repositories, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]],
		];
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v400\extensions_composer'];
	}
}
