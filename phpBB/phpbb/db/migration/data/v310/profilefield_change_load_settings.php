<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class profilefield_change_load_settings extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\profilefield_aol_cleanup',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('load_cpf_memberlist', '1')),
			array('config.update', array('load_cpf_pm', '1')),
			array('config.update', array('load_cpf_viewprofile', '1')),
			array('config.update', array('load_cpf_viewtopic', '1')),
		);
	}
}
