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

class add_webpush_options extends migration
{
	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\add_webpush',
		];
	}

	public function effectively_installed(): bool
	{
		return $this->config->offsetExists('webpush_method_default_enable') || $this->config->offsetExists('webpush_dropdown_subscribe');
	}

	public function update_data(): array
	{
		return [
			['config.add', ['webpush_method_default_enable', true]],
			['config.add', ['webpush_dropdown_subscribe', true]],
		];
	}

	public function revert_data(): array
	{
		return [
			['config.remove', ['webpush_method_default_enable']],
			['config.remove', ['webpush_dropdown_subscribe']],
		];
	}
}
