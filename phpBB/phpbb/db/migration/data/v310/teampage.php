<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class teampage extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'teampage');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return array(
			'add_tables'		=> array(
				$this->table_prefix . 'teampage'	=> array(
					'COLUMNS'		=> array(
						'teampage_id'		=> array('UINT', null, 'auto_increment'),
						'group_id'			=> array('UINT', 0),
						'teampage_name'		=> array('VCHAR_UNI:255', ''),
						'teampage_position'	=> array('UINT', 0),
						'teampage_parent'	=> array('UINT', 0),
					),
					'PRIMARY_KEY'		=> 'teampage_id',
				),
			),
			'drop_columns'		=> array(
				$this->table_prefix . 'groups'		=> array(
					'group_teampage',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'		=> array(
				$this->table_prefix . 'teampage',
			),
			'add_columns'		=> array(
				$this->table_prefix . 'groups'		=> array(
					'group_teampage'	=> array('UINT', 0, 'after' => 'group_legend'),
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'add_groups_teampage'))),
		);
	}

	public function add_groups_teampage()
	{
		$sql = 'SELECT teampage_id
			FROM ' . TEAMPAGE_TABLE;
		$result = $this->db->sql_query_limit($sql, 1);
		$added_groups_teampage = (bool) $this->db->sql_fetchfield('teampage_id');
		$this->db->sql_freeresult($result);

		if (!$added_groups_teampage)
		{
			$sql = 'SELECT *
				FROM ' . GROUPS_TABLE . '
				WHERE group_type = ' . GROUP_SPECIAL . "
					AND (group_name = 'ADMINISTRATORS'
						OR group_name = 'GLOBAL_MODERATORS')
				ORDER BY group_name ASC";
			$result = $this->db->sql_query($sql);

			$teampage_entries = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$teampage_entries[] = array(
					'group_id'			=> (int) $row['group_id'],
					'teampage_name'		=> '',
					'teampage_position'	=> sizeof($teampage_entries) + 1,
					'teampage_parent'	=> 0,
				);
			}
			$this->db->sql_freeresult($result);

			if (sizeof($teampage_entries))
			{
				$this->db->sql_multi_insert(TEAMPAGE_TABLE, $teampage_entries);
			}
			unset($teampage_entries);
		}

	}
}
