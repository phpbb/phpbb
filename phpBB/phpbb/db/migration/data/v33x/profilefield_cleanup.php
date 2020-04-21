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

class profilefield_cleanup extends \phpbb\db\migration\profilefield_base_migration
{
	protected $profilefield_name = 'phpbb_googleplus';
	protected $profilefield_database_type = ['VCHAR', ''];
	protected $profilefield_data = [
		'field_name'			=> 'phpbb_googleplus',
		'field_type'			=> 'profilefields.type.googleplus',
		'field_ident'			=> 'phpbb_googleplus',
		'field_length'			=> '20',
		'field_minlen'			=> '3',
		'field_maxlen'			=> '255',
		'field_novalue'			=> '',
		'field_default_value'	=> '',
		'field_validation'		=> '(?:(?!\.{2,})([^<>=+]))+',
		'field_required'		=> 0,
		'field_show_novalue'	=> 0,
		'field_show_on_reg'		=> 0,
		'field_show_on_pm'		=> 1,
		'field_show_on_vt'		=> 1,
		'field_show_profile'	=> 1,
		'field_hide'			=> 0,
		'field_no_view'			=> 0,
		'field_active'			=> 1,
		'field_is_contact'		=> 1,
		'field_contact_desc'	=> 'VIEW_GOOGLEPLUS_PROFILE',
		'field_contact_url'		=> 'http://plus.google.com/%s'
	];

	static public function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330'];
	}

	public function effectively_installed()
	{
		$sql = 'SELECT field_id
			FROM ' . $this->table_prefix . 'profile_fields
			WHERE ' . $this->db->sql_build_array('SELECT', ['field_name' => $this->profilefield_name]);
		$result = $this->db->sql_query($sql);
		$profile_field = (bool) $this->db->sql_fetchfield('field_id');
		$this->db->sql_freeresult($result);

		$profile_field_data = $this->db_tools->sql_column_exists($this->table_prefix . 'profile_fields_data', 'pf_' . $this->profilefield_name);

		return (!$profile_field && !$profile_field_data);
	}

	public function update_schema()
	{
		return parent::revert_schema();
	}

	public function revert_schema()
	{
		return [];
	}

	public function update_data()
	{
		return parent::revert_data();
	}

	public function revert_data()
	{
		return [];
	}
}
