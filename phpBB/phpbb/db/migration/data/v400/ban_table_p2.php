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

class ban_table_p2 extends \phpbb\db\migration\migration
{
	public static function depends_on(): array
	{
		return ['\phpbb\db\migration\data\v400\ban_table_p1'];
	}

	public function update_schema(): array
	{
		return [
			'drop_tables'	=> [
				$this->table_prefix . 'banlist',
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'add_tables'	=> [
				$this->table_prefix . 'banlist'		=> [
					'COLUMNS'	=> [
						'ban_id'			=> ['ULINT', null, 'auto_increment'],
						'ban_userid'		=> ['ULINT', 0],
						'ban_ip'			=> ['VCHAR:40', ''],
						'ban_email'			=> ['VCHAR_UNI:100', ''],
						'ban_start'			=> ['TIMESTAMP', 0],
						'ban_end'			=> ['TIMESTAMP', 0],
						'ban_exclude'		=> ['BOOL', 0],
						'ban_reason'		=> ['VCHAR_UNI', ''],
						'ban_give_reason'	=> ['VCHAR_UNI', ''],
					],
					'PRIMARY_KEY'	=> 'ban_id',
					'KEYS'	=> [
						'ban_end'	=> ['INDEX', 'ban_end'],
						'ban_user'	=> ['INDEX', ['ban_userid', 'ban_exclude']],
						'ban_email'	=> ['INDEX', ['ban_email', 'ban_exclude']],
						'ban_ip'	=> ['INDEX', ['ban_ip', 'ban_exclude']],
					],
				],
			],
		];
	}
}
