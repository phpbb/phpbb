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

class release_3_0_6_rc3 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.0.6-RC3', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_6_rc2');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array(&$this, 'update_cp_fields'))),

			array('config.update', array('version', '3.0.6-RC3')),
		);
	}

	public function update_cp_fields()
	{
		// Update the Custom Profile Fields based on previous settings to the new \format
		$sql = 'UPDATE ' . PROFILE_FIELDS_TABLE . '
			SET field_show_on_vt = 1
			WHERE field_hide = 0
				AND (field_required = 1 OR field_show_on_reg = 1 OR field_show_profile = 1)';
		$this->sql_query($sql);
	}
}
