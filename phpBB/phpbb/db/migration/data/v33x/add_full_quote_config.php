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

class add_full_quote_config extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return [
			'\phpbb\db\migration\data\v33x\v3315',
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['allow_full_quotes', 1]],
		];
	}
}
