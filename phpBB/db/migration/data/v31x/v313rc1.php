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

namespace phpbb\db\migration\data\v31x;

class v313rc1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.1.3-RC1', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v30x\release_3_0_13_rc1',
			'\phpbb\db\migration\data\v31x\plupload_last_gc_dynamic',
			'\phpbb\db\migration\data\v31x\profilefield_remove_underscore_from_alpha',
			'\phpbb\db\migration\data\v31x\profilefield_yahoo_update_url',
			'\phpbb\db\migration\data\v31x\update_custom_bbcodes_with_idn',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.1.3-RC1')),
		);
	}
}
