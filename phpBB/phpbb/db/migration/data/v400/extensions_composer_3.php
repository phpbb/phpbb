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

class extensions_composer_3 extends migration
{
	public function effectively_installed(): bool
	{
		return $this->config->offsetExists('exts_composer_minimum_stability');
	}

	public function update_data(): array
	{
		return [
			['config.add', ['exts_composer_minimum_stability', 'stable']],
		];
	}

	public static function depends_on(): array
	{
		return ['\phpbb\db\migration\data\v400\extensions_composer'];
	}
}
