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

class custom_profile_field_contact_icon extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'profile_fields', 'field_icon');
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330',];
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> [
				$this->table_prefix . 'profile_fields'	=> [
					'field_icon'	=> array('VCHAR:255', ''),
				],
			],
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'profile_fields'	=> array(
					'field_icon',
				),
			),
		);
	}
}
