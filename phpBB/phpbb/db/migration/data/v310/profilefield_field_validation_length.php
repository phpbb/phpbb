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

class profilefield_field_validation_length extends \phpbb\db\migration\migration
{
	protected $validation_options_old = array(
		'ALPHA_SPACERS'		=> '[\w_\+\. \-\[\]]+',
	);

	protected $validation_options_new = array(
		'ALPHA_SPACERS'		=> '[\w\x20_+\-\[\]]+',
	);

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\rc3',
		);
	}

	public function update_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'profile_fields'			=> array(
					'field_validation'	=> array('VCHAR_UNI:64', ''),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'profile_fields'			=> array(
					'field_validation'	=> array('VCHAR_UNI:20', ''),
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_profile_fields_validation'))),
		);
	}

	public function revert_data()
	{
		return array(
			array('custom', array(array($this, 'revert_profile_fields_validation'))),
		);
	}

	public function update_profile_fields_validation()
	{
		foreach ($this->validation_options_new as $validation_type => $regex)
		{
			$sql = 'UPDATE ' . $this->table_prefix . "profile_fields
				SET field_validation = '" . $this->db->sql_escape($this->validation_options_new[$validation_type]) . "'
				WHERE field_validation = '" . $this->db->sql_escape($this->validation_options_old[$validation_type]) . "'";
			$this->sql_query($sql);
		}
	}

	public function revert_profile_fields_validation()
	{
		foreach ($this->validation_options_new as $validation_type => $regex)
		{
			$sql = 'UPDATE ' . $this->table_prefix . "profile_fields
				SET field_validation = '" . $this->db->sql_escape($this->validation_options_old[$validation_type]) . "'
				WHERE field_validation = '" . $this->db->sql_escape($this->validation_options_new[$validation_type]) . "'";
			$this->sql_query($sql);
		}
	}
}
