<?php
/**
 * This file is part of the phpBB Forum Software package.
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 */

namespace phpbb\db\migration\data\v330;

class remove_new_passwd_col extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v320\v320',
		);
	}

	public function update_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'users'				=> array(
					'user_newpasswd',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'users'		=> array(
					'user_newpasswd'		=> array('VCHAR:255', '', 'after' => 'user_actkey'),
				),
			),
		);
	}
}
