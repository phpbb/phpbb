<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class profilefield_occupation extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'profile_fields_data', 'pf_phpbb_occupation');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\profilefield_types',
		);
	}

	public function update_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'profile_fields_data'			=> array(
					'pf_phpbb_occupation'		=> array('MTEXT', ''),
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'create_occupation_custom_field'))),
			array('custom', array(array($this, 'convert_occupation_to_custom_field'))),
		);
	}

	public function create_occupation_custom_field()
	{
		$sql = 'SELECT MAX(field_order) as max_field_order
			FROM ' . PROFILE_FIELDS_TABLE;
		$result = $this->db->sql_query($sql);
		$max_field_order = (int) $this->db->sql_fetchfield('max_field_order');
		$this->db->sql_freeresult($result);

		$sql_ary = array(
			'field_name'			=> 'phpbb_occupation',
			'field_type'			=> 'profilefields.type.text',
			'field_ident'			=> 'phpbb_occupation',
			'field_length'			=> '3|30',
			'field_minlen'			=> '2',
			'field_maxlen'			=> '500',
			'field_novalue'			=> '',
			'field_default_value'	=> '',
			'field_validation'		=> '.*',
			'field_required'		=> 0,
			'field_show_novalue'	=> 0,
			'field_show_on_reg'		=> 0,
			'field_show_on_pm'		=> 0,
			'field_show_on_vt'		=> 0,
			'field_show_profile'	=> 1,
			'field_hide'			=> 0,
			'field_no_view'			=> 0,
			'field_active'			=> 1,
			'field_order'			=> $max_field_order + 1,
		);

		$sql = 'INSERT INTO ' . PROFILE_FIELDS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);
		$field_id = (int) $this->db->sql_nextid(); 

		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, PROFILE_LANG_TABLE);

		$sql = 'SELECT lang_id
			FROM ' . LANG_TABLE;
		$result = $this->db->sql_query($sql);
		while ($lang_id = (int) $this->db->sql_fetchfield('lang_id'))
		{
			$insert_buffer->add(array(
				'field_id'				=> $field_id,
				'lang_id'				=> $lang_id,
				'lang_name'				=> 'OCCUPATION',
				'lang_explain'			=> '',
				'lang_default_value'	=> '',
			));
		}
		$this->db->sql_freeresult($result);

		$insert_buffer->flush();
	}

	/**
	* @param int			$start		Start of staggering step
	* @return		mixed		int start of the next step, null if the end was reached
	*/
	public function convert_occupation_to_custom_field($start)
	{
		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, $this->table_prefix . 'profile_fields_data');
		$limit = 250;
		$converted_users = 0;

		$sql = 'SELECT user_id, user_occ
			FROM ' . $this->table_prefix . "users
			WHERE user_occ <> ''
			ORDER BY user_id";
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$converted_users++;

			$cp_data = array(
				'pf_phpbb_occupation'		=> $row['user_occ'],
			);

			$sql = 'UPDATE ' . $this->fields_data_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $cp_data) . '
				WHERE user_id = ' . (int) $row['user_id'];
			$this->db->sql_query($sql);

			if (!$this->db->sql_affectedrows())
			{
				$cp_data['user_id'] = (int) $row['user_id'];
				$insert_buffer->insert($cp_data);
			}
		}
		$this->db->sql_freeresult($result);

		$insert_buffer->flush();

		if ($converted_users < $limit)
		{
			// No more users left, we are done...
			return;
		}

		return $start + $limit;
	}
}
