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

class extend_bbcode_tooltip extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return !$this->db_tools->sql_column_exists($this->table_prefix . 'bbcodes', 'bbcode_helpline');
	}

	static public function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v33x\v334'
		];
	}

	public function update_schema()
	{
		return [
			'change_columns'	=> [
				$this->table_prefix . 'bbcodes'	=> [
					'bbcode_helpline'	=> ['TEXT_UNI', ''],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'change_columns'	=> [
				$this->table_prefix . 'bbcodes'	=> [
					'bbcode_helpline'	=> ['VCHAR_UNI', ''],
				],
			],
		];
	}
}
