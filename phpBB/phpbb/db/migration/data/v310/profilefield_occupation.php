<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class profilefield_occupation extends \phpbb\db\migration\profilefield_base_migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\profilefield_interests',
		);
	}

	protected $profilefield_name = 'phpbb_occupation';

	protected $profilefield_database_type = array('MTEXT', '');

	protected $profilefield_data = array(
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
	);

	protected $user_column_name = 'user_occ';
}
