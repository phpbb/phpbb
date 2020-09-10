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

namespace phpbb\db\migration\data\v33x;

class remove_profilefield_aol extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return !$this->db_tools->sql_column_exists($this->table_prefix . 'profile_fields_data', 'pf_phpbb_aol');
	}

	static public function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v33x\v331',
		];
	}

	public function update_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'profile_fields_data'			=> [
					'pf_phpbb_aol',
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'profile_fields_data'			=> [
					'pf_phpbb_aol'		=> ['VCHAR', ''],
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'delete_custom_profile_field_data']]],
		];
	}

	public function revert_data()
	{
		return [
			['custom', [[$this, 'create_custom_field']]],
		];
	}

	public function delete_custom_profile_field_data()
	{
		$sql = 'SELECT field_id
			FROM ' . PROFILE_FIELDS_TABLE . "
			WHERE field_name = 'phpbb_aol'";
		$result = $this->db->sql_query($sql);
		$field_id = (int) $this->db->sql_fetchfield('field_id');
		$this->db->sql_freeresult($result);

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

	public function create_custom_field()
	{
		$sql = 'SELECT MAX(field_order) as max_field_order
			FROM ' . PROFILE_FIELDS_TABLE;
		$result = $this->db->sql_query($sql);
		$max_field_order = (int) $this->db->sql_fetchfield('max_field_order');
		$this->db->sql_freeresult($result);

		$sql_ary = [
			'field_name'			=> 'phpbb_aol',
			'field_type'			=> 'profilefields.type.string',
			'field_ident'			=> 'phpbb_aol',
			'field_length'			=> '40',
			'field_minlen'			=> '5',
			'field_maxlen'			=> '255',
			'field_novalue'			=> '',
			'field_default_value'	=> '',
			'field_validation'		=> '.*',
			'field_required'		=> 0,
			'field_show_novalue'	=> 0,
			'field_show_on_reg'		=> 0,
			'field_show_on_pm'		=> 1,
			'field_show_on_vt'		=> 1,
			'field_show_on_ml'		=> 0,
			'field_show_profile'	=> 1,
			'field_hide'			=> 0,
			'field_no_view'			=> 0,
			'field_active'			=> 1,
			'field_is_contact'		=> 1,
			'field_contact_desc'	=> '',
			'field_contact_url'		=> '',
			'field_order'			=> $max_field_order + 1,
		];

		$sql = 'INSERT INTO ' . PROFILE_FIELDS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);
		$field_id = (int) $this->db->sql_nextid();

		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, PROFILE_LANG_TABLE);

		$sql = 'SELECT lang_id
			FROM ' . LANG_TABLE;
		$result = $this->db->sql_query($sql);
		$lang_name = 'AOL';
		while ($lang_id = (int) $this->db->sql_fetchfield('lang_id'))
		{
			$insert_buffer->insert([
				'field_id'				=> (int) $field_id,
				'lang_id'				=> (int) $lang_id,
				'lang_name'				=> $lang_name,
				'lang_explain'			=> '',
				'lang_default_value'	=> '',
			]);
		}
		$this->db->sql_freeresult($result);

		$insert_buffer->flush();
	}
}
