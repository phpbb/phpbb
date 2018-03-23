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

class acp_prune_users_module extends \phpbb\db\migration\container_aware_migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT module_id
			FROM ' . MODULES_TABLE . "
			WHERE module_class = 'acp'
				AND module_langname = 'ACP_CAT_USERS'";
		$result = $this->db->sql_query($sql);
		$acp_cat_users_id = (int) $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT parent_id
			FROM ' . MODULES_TABLE . "
			WHERE module_class = 'acp'
				AND module_basename = 'acp_prune'
				AND module_mode = 'users'";
		$result = $this->db->sql_query($sql);
		$acp_prune_users_parent = (int) $this->db->sql_fetchfield('parent_id');
		$this->db->sql_freeresult($result);

		// Skip migration if "Users" category has been deleted
		// or the module has already been moved to that category
		return !$acp_cat_users_id || $acp_cat_users_id === $acp_prune_users_parent;
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\beta1');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'move_prune_users_module'))),
		);
	}

	public function move_prune_users_module()
	{
		$sql = 'SELECT module_id
			FROM ' . MODULES_TABLE . "
			WHERE module_class = 'acp'
				AND module_basename = 'acp_prune'
				AND module_mode = 'users'";
		$result = $this->db->sql_query($sql);
		$acp_prune_users_id = (int) $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		$sql = 'SELECT module_id
			FROM ' . MODULES_TABLE . "
			WHERE module_class = 'acp'
				AND module_langname = 'ACP_CAT_USERS'";
		$result = $this->db->sql_query($sql);
		$acp_cat_users_id = (int) $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		$module_manager = $this->container->get('module.manager');
		$module_manager->move_module($acp_prune_users_id, $acp_cat_users_id, 'acp');
	}
}
