<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class auth_provider_oauth2 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\auth_provider_oauth',
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(
				array($this, 'update_auth_link_module_auth'),
			)),
		);
	}

	public function update_auth_link_module_auth()
	{
		$sql = 'UPDATE ' . MODULES_TABLE . "
			SET module_auth = 'authmethod_oauth'
			WHERE module_class = 'ucp'
				AND module_basename = 'ucp_auth_link'
				AND module_mode = 'auth_link'
				AND module_auth = ''";
		$this->db->sql_query($sql);
	}
}
