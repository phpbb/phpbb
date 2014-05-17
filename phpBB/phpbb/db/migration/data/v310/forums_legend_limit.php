<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class forums_legend_limit extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'forums', 'display_subforum_limit');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\beta3');
	}

	public function update_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'forums'	=> array(
					'display_subforum_limit'	=> array('BOOL', 0, 'after' => 'display_subforum_list'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'		=> array(
				$this->table_prefix . 'forums'		=> array(
					'display_subforum_limit',
				),
			),
		);
	}
}
