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

namespace phpbb\db\migration\data\v330;

class v330b2 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['version'], '3.3.0-b2', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v330\add_display_unapproved_posts_config',
			'\phpbb\db\migration\data\v330\forums_legend_limit',
			'\phpbb\db\migration\data\v330\remove_email_hash',
			'\phpbb\db\migration\data\v330\v330b1',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.3.0-b2')),
		);
	}
}
