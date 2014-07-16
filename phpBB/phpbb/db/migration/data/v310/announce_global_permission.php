<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class announce_global_permission extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT auth_option_id
			FROM ' . ACL_OPTIONS_TABLE . "
			WHERE auth_option = 'f_announce_global'";
		$result = $this->db->sql_query($sql);
		$auth_option_id = $this->db->sql_fetchfield('auth_option_id');
		$this->db->sql_freeresult($result);

		return $auth_option_id !== false;
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\beta2');
	}

	public function update_data()
	{
		return array(
			array('permission.add', array('f_announce_global', false, 'f_announce')),
		);
	}
}
