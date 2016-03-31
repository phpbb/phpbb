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

namespace phpbb\db\migration\data\v310;

class alpha1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.1.0-a1', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v30x\local_url_bbcode',
			'\phpbb\db\migration\data\v30x\release_3_0_12',
			'\phpbb\db\migration\data\v310\acp_style_components_module',
			'\phpbb\db\migration\data\v310\allow_cdn',
			'\phpbb\db\migration\data\v310\auth_provider_oauth',
			'\phpbb\db\migration\data\v310\avatars',
			'\phpbb\db\migration\data\v310\boardindex',
			'\phpbb\db\migration\data\v310\config_db_text',
			'\phpbb\db\migration\data\v310\forgot_password',
			'\phpbb\db\migration\data\v310\mod_rewrite',
			'\phpbb\db\migration\data\v310\mysql_fulltext_drop',
			'\phpbb\db\migration\data\v310\namespaces',
			'\phpbb\db\migration\data\v310\notifications_cron',
			'\phpbb\db\migration\data\v310\notification_options_reconvert',
			'\phpbb\db\migration\data\v310\plupload',
			'\phpbb\db\migration\data\v310\signature_module_auth',
			'\phpbb\db\migration\data\v310\softdelete_mcp_modules',
			'\phpbb\db\migration\data\v310\teampage',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.1.0-a1')),
		);
	}
}
