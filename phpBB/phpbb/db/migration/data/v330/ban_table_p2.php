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

class ban_table_p2 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v330\ban_table_p1');
	}

	public function update_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'banlist',
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'banlist'		=> array(
					'COLUMNS'	=> array(
						'ban_id'			=> array('ULINT', NULL, 'auto_increment'),
						'ban_userid'		=> array('ULINT', 0),
						'ban_ip'			=> array('VCHAR:40', ''),
						'ban_email'			=> array('VCHAR_UNI:100', ''),
						'ban_start'			=> array('TIMESTAMP', 0),
						'ban_end'			=> array('TIMESTAMP', 0),
						'ban_exclude'		=> array('BOOL', 0),
						'ban_reason'		=> array('VCHAR_UNI', ''),
						'ban_give_reason'	=> array('VCHAR_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'ban_id',
					'KEYS'	=> array(
						'ban_end'	=> array('INDEX', 'ban_end'),
						'ban_user'	=> array('INDEX', array('ban_userid', 'ban_exclude')),
						'ban_email'	=> array('INDEX', array('ban_email', 'ban_exclude')),
						'ban_ip'	=> array('INDEX', array('ban_ip', 'ban_exclude')),
					),
				),
			),
		);
	}
}
