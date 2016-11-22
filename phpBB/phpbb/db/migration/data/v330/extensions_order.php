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

class extensions_order extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v320\dev',
		);
	}

	public function update_schema()
	{
		return array(
			'add_tables'		=> array(
				$this->table_prefix . 'ext_events'	=> array(
					'COLUMNS'			=> array(
						'ext_event_name'		=> array('VCHAR', ''),
						'ext_event_ext_name'	=> array('VCHAR', ''),
						'ext_event_order'		=> array('UINT', 0),
						'ext_event_active'		=> array('BOOL', 0),
					),
					'KEYS'				=> array(
						'ext_name'		=> array('UNIQUE', array('ext_event_name', 'ext_event_ext_name')),
					),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'		=> array(
				$this->table_prefix . 'ext_events',
			),
		);
	}

	public function update_data()
	{
		return array(
			array('module.add', array(
				'acp',
				'ACP_EXTENSION_MANAGEMENT',
				array(
					'module_basename'	=> 'acp_extensions',
					'modes'				=> array('order'),
				),
			)),
		);
	}
}
