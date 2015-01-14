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

namespace phpbb\db\migration;

abstract class profilefield_base_migration extends container_aware_migration
{
	protected $profilefield_name;

	protected $profilefield_database_type;

	protected $profilefield_data;

	/**
	* Language data should be in array -> each language_data in separate key
	* array(
	*	array(
	*		'option_id'	=> value,
	*		'field_type'	=> value,
	*		'lang_value'	=> value,
	*	),
	*	array(
	*		'option_id'	=> value,
	*		'field_type'	=> value,
	*		'lang_value'	=> value,
	*	),
	* )
	*/
	protected $profilefield_language_data;

	protected $user_column_name;

	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'profile_fields_data', 'pf_' . $this->profilefield_name);
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'profile_fields_data'			=> array(
					'pf_' . $this->profilefield_name		=> $this->profilefield_database_type,
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'profile_fields_data'			=> array(
					'pf_' . $this->profilefield_name,
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'create_custom_field'))),
			array('custom', array(array($this, 'convert_user_field_to_custom_field'))),
		);
	}

	public function revert_data()
	{
		return array(
			array('custom', array(array($this, 'delete_custom_profile_field_data'))),
		);
	}

	public function create_custom_field()
	{
		$sql = 'SELECT MAX(field_order) as max_field_order
			FROM ' . PROFILE_FIELDS_TABLE;
		$result = $this->db->sql_query($sql);
		$max_field_order = (int) $this->db->sql_fetchfield('max_field_order');
		$this->db->sql_freeresult($result);

		$sql_ary = array_merge($this->profilefield_data, array(
			'field_order'			=> $max_field_order + 1,
		));

		$sql = 'INSERT INTO ' . PROFILE_FIELDS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);
		$field_id = (int) $this->db->sql_nextid();

		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, PROFILE_LANG_TABLE);

		$sql = 'SELECT lang_id
			FROM ' . LANG_TABLE;
		$result = $this->db->sql_query($sql);
		$lang_name = (strpos($this->profilefield_name, 'phpbb_') === 0) ? strtoupper(substr($this->profilefield_name, 6)) : strtoupper($this->profilefield_name);
		while ($lang_id = (int) $this->db->sql_fetchfield('lang_id'))
		{
			$insert_buffer->insert(array(
				'field_id'				=> (int) $field_id,
				'lang_id'				=> (int) $lang_id,
				'lang_name'				=> $lang_name,
				'lang_explain'			=> '',
				'lang_default_value'	=> '',
			));
		}
		$this->db->sql_freeresult($result);

		$insert_buffer->flush();
	}

	/**
	* Create Custom profile fields languguage entries
	*/
	public function create_language_entries()
	{
		$field_id = $this->get_custom_profile_field_id();

		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, PROFILE_FIELDS_LANG_TABLE);

		$sql = 'SELECT lang_id
			FROM ' . LANG_TABLE;
		$result = $this->db->sql_query($sql);
		while ($lang_id = (int) $this->db->sql_fetchfield('lang_id'))
		{
			foreach ($this->profilefield_language_data as $language_data)
			{
				$insert_buffer->insert(array_merge(array(
					'field_id'	=> (int) $field_id,
					'lang_id'	=> (int) $lang_id,
				), $language_data));
			}
		}
		$this->db->sql_freeresult($result);

		$insert_buffer->flush();
	}

	/**
	* Clean database when reverting the migration
	*/
	public function delete_custom_profile_field_data()
	{
		$field_id = $this->get_custom_profile_field_id();

		$sql = 'DELETE FROM ' . PROFILE_FIELDS_TABLE . '
			WHERE field_id = ' . (int) $field_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . PROFILE_LANG_TABLE . '
			WHERE field_id = ' . (int) $field_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . PROFILE_FIELDS_LANG_TABLE . '
			WHERE field_id = ' . (int) $field_id;
		$this->db->sql_query($sql);
	}

	/**
	* Get custom profile field id
	* @return	int	custom profile filed id
	*/
	public function get_custom_profile_field_id()
	{
		$sql = 'SELECT field_id
			FROM ' . PROFILE_FIELDS_TABLE . "
			WHERE field_name = '" . $this->profilefield_name . "'";
		$result = $this->db->sql_query($sql);
		$field_id = (int) $this->db->sql_fetchfield('field_id');
		$this->db->sql_freeresult($result);

		return $field_id;
	}

	/**
	* @param int			$start		Start of staggering step
	* @return		mixed		int start of the next step, null if the end was reached
	*/
	public function convert_user_field_to_custom_field($start)
	{
		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, $this->table_prefix . 'profile_fields_data');
		$limit = 250;
		$converted_users = 0;

		$sql = 'SELECT user_id, ' . $this->user_column_name . '
			FROM ' . $this->table_prefix . 'users
			WHERE ' . $this->user_column_name . " <> ''
			ORDER BY user_id";
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$converted_users++;

			$cp_data = array(
				'pf_' . $this->profilefield_name		=> $row[$this->user_column_name],
			);

			$sql = 'UPDATE ' . $this->table_prefix . 'profile_fields_data
				SET ' . $this->db->sql_build_array('UPDATE', $cp_data) . '
				WHERE user_id = ' . (int) $row['user_id'];
			$this->db->sql_query($sql);

			if (!$this->db->sql_affectedrows())
			{
				$cp_data['user_id'] = (int) $row['user_id'];
				$cp_data = array_merge($this->get_insert_sql_array(), $cp_data);
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

	protected function get_insert_sql_array()
	{
		static $profile_row;

		if ($profile_row === null)
		{
			$manager = $this->container->get('profilefields.manager');
			$profile_row = $manager->build_insert_sql_array(array());
		}

		return $profile_row;
	}
}
