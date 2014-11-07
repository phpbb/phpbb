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

class api extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'api_keys');
	}

	public function update_schema()
	{
		return array(
			'add_tables'		=> array(
				$this->table_prefix . 'api_keys'	=> array(
					'COLUMNS'		=> array(
						'key_id'			=> array('UINT', null, 'auto_increment'),
						'user_id'			=> array('UINT', 0),
						'name'				=> array('VCHAR', ''),
						'auth_key'			=> array('VCHAR:16', ''),
						'sign_key'			=> array('VCHAR:16', ''),
						'serial'			=> array('UINT', 0),
					),
					'PRIMARY_KEY'			=> 'key_id',
				),
				$this->table_prefix . 'api_exchange_keys'	=> array(
					'COLUMNS'		=> array(
						'exchange_key'		=> array('VCHAR:16', ''),
						'timestamp'			=> array('TIMESTAMP', 0),
						'auth_key'			=> array('VCHAR:16', ''),
						'sign_key'			=> array('VCHAR:16', ''),
						'user_id'			=> array('UINT', 0),
						'name'				=> array('VCHAR', ''),
					),
					'PRIMARY_KEY'			=> 'exchange_key',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'api_keys',
				$this->table_prefix . 'api_exchange_keys',
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('allow_api', 1)),
			array('permission.add', array('u_api')),
			array('permission.add', array('m_api')),
		);
	}
}
