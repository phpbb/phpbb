<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class extensions extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'ext');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_11');
	}

	public function update_schema()
	{
		return array(
			'add_tables'		=> array(
				$this->table_prefix . 'ext'	=> array(
					'COLUMNS'			=> array(
						'ext_name'		=> array('VCHAR', ''),
						'ext_active'	=> array('BOOL', 0),
						'ext_state'		=> array('TEXT', ''),
					),
					'KEYS'				=> array(
						'ext_name'		=> array('UNIQUE', 'ext_name'),
					),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'		=> array(
				$this->table_prefix . 'ext',
			),
		);
	}

	public function update_data()
	{
		return array(
			// Module will be renamed later
			array('module.add', array(
				'acp',
				'ACP_CAT_STYLES',
				'ACP_EXTENSION_MANAGEMENT'
			)),
			array('module.add', array(
				'acp',
				'ACP_EXTENSION_MANAGEMENT',
				array(
					'module_basename'	=> 'acp_extensions',
					'modes'				=> array('main'),
				),
			)),
			array('permission.add', array('a_extensions', true, 'a_styles')),
		);
	}
}
