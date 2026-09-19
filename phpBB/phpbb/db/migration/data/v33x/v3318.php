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

class v3318 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['version'], '3.3.18', '>=');
	}

	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v33x\ai_crawler_bots',
			'\phpbb\db\migration\data\v33x\profilefields_x_update',
			'\phpbb\db\migration\data\v33x\add_version_check_cron',
			'\phpbb\db\migration\data\v33x\v3317',
			'\phpbb\db\migration\data\v33x\add_oauth_state_time',
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['version', '3.3.18']],
		];
	}
}
