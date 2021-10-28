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

declare(strict_types=1);

namespace phpbb\db\migration\data\v33x;

use phpbb\db\migration\migration;

class add_email_encoding_config extends migration
{
	public function effectively_installed(): bool
	{
		return $this->config->offsetExists('smtp_encoding');
	}

	static public function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v33x\v335'
		];
	}

	public function update_data(): array
	{
		return [
			['config.add', ['smtp_encoding', 0]],
		];
	}
}
