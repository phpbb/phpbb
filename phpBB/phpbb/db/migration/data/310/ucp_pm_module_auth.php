<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_310_ucp_pm_module_auth extends phpbb_db_migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT module_auth
			FROM ' . MODULES_TABLE . "
			WHERE module_class = 'ucp'
				AND module_basename = 'ucp_pm'
				AND parent_id = 0";
		$result = $this->db->sql_query($sql);
		$module_auth = $this->db->sql_fetchfield('module_auth');
		$this->db->sql_freeresult($result);

		return $module_auth === 'cfg_allow_privmsg';
	}

	static public function depends_on()
	{
		return array(
			'phpbb_db_migration_data_310_dev',
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_ucp_pm_auth'))),
		);
	}

	public function update_ucp_pm_auth()
	{
		$sql = 'UPDATE ' . MODULES_TABLE . "
			SET module_auth = 'cfg_allow_privmsg'
			WHERE module_class = 'ucp'
				AND module_basename = 'ucp_pm'
				AND parent_id = 0";
		$this->sql_query($sql);
	}

}
