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

namespace phpbb\db\migration\data\v30x;

class release_3_0_2_rc1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.0.2-RC1', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_1');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('referer_validation', '1')),
			array('config.add', array('check_attachment_content', '1')),
			array('config.add', array('mime_triggers', 'body|head|html|img|plaintext|a href|pre|script|table|title')),

			array('config.update', array('version', '3.0.2-RC1')),
		);
	}
}
