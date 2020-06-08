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

class v331rc1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['version'], '3.3.1-RC1', '>=');
	}

	static public function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v33x\add_notification_emails_table',
			'\phpbb\db\migration\data\v33x\fix_display_unapproved_posts_config',
			'\phpbb\db\migration\data\v33x\bot_update',
			'\phpbb\db\migration\data\v33x\font_awesome_5_update',
			'\phpbb\db\migration\data\v33x\profilefield_cleanup',
			'\phpbb\db\migration\data\v33x\google_recaptcha_v3',
			'\phpbb\db\migration\data\v33x\default_search_return_chars',
			'\phpbb\db\migration\data\v32x\v3210rc2',
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['version', '3.3.1-RC1']],
		];
	}
}
