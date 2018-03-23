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

class profilefield_facebook extends \phpbb\db\migration\profilefield_base_migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\profilefield_contact_field',
			'\phpbb\db\migration\data\v310\profilefield_show_novalue',
			'\phpbb\db\migration\data\v310\profilefield_types',
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'create_custom_field'))),
		);
	}

	protected $profilefield_name = 'phpbb_facebook';

	protected $profilefield_database_type = array('VCHAR', '');

	protected $profilefield_data = array(
		'field_name'			=> 'phpbb_facebook',
		'field_type'			=> 'profilefields.type.string',
		'field_ident'			=> 'phpbb_facebook',
		'field_length'			=> '20',
		'field_minlen'			=> '5',
		'field_maxlen'			=> '50',
		'field_novalue'			=> '',
		'field_default_value'	=> '',
		'field_validation'		=> '[\w.]+',
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
		'field_contact_desc'	=> 'VIEW_FACEBOOK_PROFILE',
		'field_contact_url'		=> 'http://facebook.com/%s/',
	);
}
