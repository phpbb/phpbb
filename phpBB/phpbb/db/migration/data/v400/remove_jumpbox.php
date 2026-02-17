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

class remove_jumpbox extends migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
		];
	}

	public function effectively_installed()
	{
		return !isset($this->config['load_jumpbox']);
	}

	public function update_data(): array
	{
		return [
			['config.remove', ['load_jumpbox']],
		];
	}

	public function revert_data(): array
	{
		return [
			['config.add', ['load_jumpbox', 1]],
		];
	}
}
