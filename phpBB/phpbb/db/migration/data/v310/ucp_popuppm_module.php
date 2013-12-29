<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class ucp_popuppm_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT module_id
			FROM ' . MODULES_TABLE . "
			WHERE module_class = 'ucp'
				AND module_langname = 'UCP_PM_POPUP_TITLE'";
		$result = $this->db->sql_query($sql);
		$module_id = $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id == false;
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_data()
	{
		return array(
			array('module.remove', array(
				'ucp',
				'UCP_PM',
				'UCP_PM_POPUP_TITLE',
			)),
		);
	}
}
