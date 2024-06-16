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

namespace phpbb\db\migration\data\v400;

class add_bbcode_font_icon extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'bbcodes', 'bbcode_font_icon');
	}

	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\dev'
		];
	}

	public function update_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'bbcodes'	=> [
					'bbcode_font_icon'	=> ['VCHAR:50', ''],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'bbcodes'	=> [
					'bbcode_font_icon',
				],
			],
		];
	}
}
