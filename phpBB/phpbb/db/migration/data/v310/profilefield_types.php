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

namespace phpbb\db\migration\data\v310;

class profilefield_types extends \phpbb\db\migration\migration
{

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\alpha2',
		);
	}

	public function update_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'profile_fields'			=> array(
					'field_type'		=> array('VCHAR:100', ''),
				),
				$this->table_prefix . 'profile_fields_lang'		=> array(
					'field_type'		=> array('VCHAR:100', ''),
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_profile_fields_type'))),
			array('custom', array(array($this, 'update_profile_fields_lang_type'))),
		);
	}

	public function update_profile_fields_type()
	{
		// Update profile field types
		$sql = 'SELECT field_type
			FROM ' . $this->table_prefix . 'profile_fields
			GROUP BY field_type';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql = 'UPDATE ' . $this->table_prefix . "profile_fields
				SET field_type = '" . $this->db->sql_escape($this->convert_phpbb30_field_type($row['field_type'])) . "'
				WHERE field_type = '" . $this->db->sql_escape($row['field_type']) . "'";
			$this->sql_query($sql);
		}
		$this->db->sql_freeresult($result);
	}

	public function update_profile_fields_lang_type()
	{
		// Update profile field language types
		$sql = 'SELECT field_type
			FROM ' . $this->table_prefix . 'profile_fields_lang
			GROUP BY field_type';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql = 'UPDATE ' . $this->table_prefix . "profile_fields_lang
				SET field_type = '" . $this->db->sql_escape($this->convert_phpbb30_field_type($row['field_type'])) . "'
				WHERE field_type = '" . $this->db->sql_escape($row['field_type']) . "'";
			$this->sql_query($sql);
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Determine the new field type for a given phpBB 3.0 field type
	*
	*	@param	$field_type	string		Field type in 3.0
	*	@return		string		Field new type which is used since 3.1
	*/
	public function convert_phpbb30_field_type($field_type)
	{
		switch ($field_type)
		{
			case FIELD_INT:
				return 'profilefields.type.int';
			case FIELD_STRING:
				return 'profilefields.type.string';
			case FIELD_TEXT:
				return 'profilefields.type.text';
			case FIELD_BOOL:
				return 'profilefields.type.bool';
			case FIELD_DROPDOWN:
				return 'profilefields.type.dropdown';
			case FIELD_DATE:
				return 'profilefields.type.date';
			default:
				return $field_type;
		}
	}
}
