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

class release_3_0_5_rc1part2 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.0.5-RC1', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_5_rc1');
	}

	public function update_schema()
	{
		return array(
			'drop_keys'			=> array(
				$this->table_prefix . 'acl_options'		=> array('auth_option'),
			),
			'add_unique_index'	=> array(
				$this->table_prefix . 'acl_options'		=> array(
					'auth_option'		=> array('auth_option'),
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.0.5-RC1')),
		);
	}
}
