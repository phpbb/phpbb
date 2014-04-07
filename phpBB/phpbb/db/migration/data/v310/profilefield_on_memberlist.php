<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class profilefield_on_memberlist extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'profile_fields', 'field_show_on_ml');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\profilefield_cleanup',
		);
	}

	public function update_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'profile_fields'	=> array(
					'field_show_on_ml'		=> array('BOOL', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'profile_fields'	=> array(
					'field_show_on_ml',
				),
			),
		);
	}
}
