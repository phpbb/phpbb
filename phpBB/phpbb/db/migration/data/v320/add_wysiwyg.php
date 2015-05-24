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

namespace phpbb\db\migration\data\v320;

class add_wysiwyg extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		// return array('\phpbb\db\migration\data\v310\config_db_text');
		return array();
	}

	public function update_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'users'	=> array(
					'COLUMNS'			=> array(
						'user_wysiwyg_editor'			=> array('VCHAR:15', ''),
						'user_wysiwyg_default_mode'		=> array('TINT:2', 0),
						'user_wysiwyg_buttons_mode'		=> array('TINT:2', 1),
						'user_wysiwyg_override_data'	=> array('VCHAR:255', ''),
					),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'		=> array(
				$this->table_prefix . 'users'	=> array(
						'user_wysiwyg_editor',
						'user_wysiwyg_default_mode',
						'user_wysiwyg_buttons_mode',
					),
				),
		);
	}
}
