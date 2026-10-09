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

class remove_defunct_profilefields extends \phpbb\db\migration\migration
{
	/**
	 * Default profile fields of instant messaging services that no longer exist:
	 * ICQ was shut down in 2024, Skype in 2025 and Yahoo Messenger in 2018.
	 *
	 * @return array Field data of the removed fields, keyed by field name
	 */
	protected function get_removed_fields(): array
	{
		return [
			'phpbb_icq'		=> [
				'field_name'			=> 'phpbb_icq',
				'field_type'			=> 'profilefields.type.string',
				'field_ident'			=> 'phpbb_icq',
				'field_length'			=> '20',
				'field_minlen'			=> '3',
				'field_maxlen'			=> '15',
				'field_novalue'			=> '',
				'field_default_value'	=> '',
				'field_validation'		=> '[0-9]+',
				'field_required'		=> 0,
				'field_show_novalue'	=> 0,
				'field_show_on_reg'		=> 0,
				'field_show_on_pm'		=> 1,
				'field_show_on_vt'		=> 1,
				'field_show_on_ml'		=> 1,
				'field_show_profile'	=> 1,
				'field_hide'			=> 0,
				'field_no_view'			=> 0,
				'field_active'			=> 0,
				'field_is_contact'		=> 1,
				'field_contact_desc'	=> 'SEND_ICQ_MESSAGE',
				'field_contact_url'		=> 'https://www.icq.com/people/%s/',
			],
			'phpbb_yahoo'	=> [
				'field_name'			=> 'phpbb_yahoo',
				'field_type'			=> 'profilefields.type.string',
				'field_ident'			=> 'phpbb_yahoo',
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
				'field_show_on_ml'		=> 1,
				'field_show_profile'	=> 1,
				'field_hide'			=> 0,
				'field_no_view'			=> 0,
				'field_active'			=> 0,
				'field_is_contact'		=> 1,
				'field_contact_desc'	=> 'SEND_YIM_MESSAGE',
				'field_contact_url'		=> 'ymsgr:sendim?%s',
			],
			'phpbb_skype'	=> [
				'field_name'			=> 'phpbb_skype',
				'field_type'			=> 'profilefields.type.string',
				'field_ident'			=> 'phpbb_skype',
				'field_length'			=> '20',
				'field_minlen'			=> '6',
				'field_maxlen'			=> '32',
				'field_novalue'			=> '',
				'field_default_value'	=> '',
				'field_validation'		=> '[a-zA-Z][\w\.,\-_]+',
				'field_required'		=> 0,
				'field_show_novalue'	=> 0,
				'field_show_on_reg'		=> 0,
				'field_show_on_pm'		=> 1,
				'field_show_on_vt'		=> 1,
				'field_show_on_ml'		=> 1,
				'field_show_profile'	=> 1,
				'field_hide'			=> 0,
				'field_no_view'			=> 0,
				'field_active'			=> 1,
				'field_is_contact'		=> 1,
				'field_contact_desc'	=> 'VIEW_SKYPE_PROFILE',
				'field_contact_url'		=> 'skype:%s?userinfo',
			],
		];
	}

	public function effectively_installed()
	{
		foreach ($this->get_removed_fields() as $field_name => $field_data)
		{
			if ($this->db_tools->sql_column_exists($this->table_prefix . 'profile_fields_data', 'pf_' . $field_name))
			{
				return false;
			}
		}

		return true;
	}

	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v33x\v3317',
		];
	}

	public function update_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'profile_fields_data'			=> [
					'pf_phpbb_icq',
					'pf_phpbb_yahoo',
					'pf_phpbb_skype',
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'profile_fields_data'			=> [
					'pf_phpbb_icq'		=> ['VCHAR', ''],
					'pf_phpbb_yahoo'	=> ['VCHAR', ''],
					'pf_phpbb_skype'	=> ['VCHAR', ''],
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
			['custom', [[$this, 'create_custom_fields']]],
		];
	}

	public function delete_custom_profile_field_data()
	{
		foreach ($this->get_removed_fields() as $field_name => $field_data)
		{
			$sql = 'SELECT field_id
				FROM ' . PROFILE_FIELDS_TABLE . "
				WHERE field_name = '" . $this->db->sql_escape($field_name) . "'";
			$result = $this->db->sql_query($sql);
			$field_id = (int) $this->db->sql_fetchfield('field_id');
			$this->db->sql_freeresult($result);

			if (!$field_id)
			{
				continue;
			}

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
	}

	public function create_custom_fields()
	{
		$sql = 'SELECT MAX(field_order) as max_field_order
			FROM ' . PROFILE_FIELDS_TABLE;
		$result = $this->db->sql_query($sql);
		$max_field_order = (int) $this->db->sql_fetchfield('max_field_order');
		$this->db->sql_freeresult($result);

		$lang_ids = [];
		$sql = 'SELECT lang_id
			FROM ' . LANG_TABLE;
		$result = $this->db->sql_query($sql);
		while ($lang_id = (int) $this->db->sql_fetchfield('lang_id'))
		{
			$lang_ids[] = $lang_id;
		}
		$this->db->sql_freeresult($result);

		$insert_buffer = new \phpbb\db\sql_insert_buffer($this->db, PROFILE_LANG_TABLE);

		foreach ($this->get_removed_fields() as $field_name => $field_data)
		{
			$field_data['field_order'] = ++$max_field_order;

			$sql = 'INSERT INTO ' . PROFILE_FIELDS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $field_data);
			$this->db->sql_query($sql);
			$field_id = (int) $this->db->sql_nextid();

			foreach ($lang_ids as $lang_id)
			{
				$insert_buffer->insert([
					'field_id'				=> (int) $field_id,
					'lang_id'				=> (int) $lang_id,
					'lang_name'				=> strtoupper(substr($field_name, 6)),
					'lang_explain'			=> '',
					'lang_default_value'	=> '',
				]);
			}
		}

		$insert_buffer->flush();
	}
}
