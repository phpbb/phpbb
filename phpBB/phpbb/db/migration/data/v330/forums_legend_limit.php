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

class forums_legend_limit extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'forums', 'display_subforum_limit');
	}

	static public function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330b1'];
	}

	public function update_schema()
	{
		return [
			'add_columns'		=> [
				$this->table_prefix . 'forums'	=> [
					'display_subforum_limit'	=> ['BOOL', 0, 'after' => 'display_subforum_list'],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns'		=> [
				$this->table_prefix . 'forums'		=> [
					'display_subforum_limit',
				],
			],
		];
	}
}
