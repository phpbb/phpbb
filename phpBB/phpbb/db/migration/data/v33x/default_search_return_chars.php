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

namespace phpbb\db\migration\data\v33x;

class default_search_return_chars extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->config->offsetExists('default_search_return_chars');
	}

	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v330\v330',
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['default_search_return_chars', 300]],
		];
	}
}
