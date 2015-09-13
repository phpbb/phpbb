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

namespace phpbb\db\migration\data\v31x;

class m_pm_report extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v316rc1');
	}

	public function update_data()
	{
		return array(
			array('permission.add', array('m_pm_report', true, 'm_report')),
			array('custom', array(
					array($this, 'update_module_auth'),
				),
			),
		);
	}

	public function revert_data()
	{
		return array(
			array('permission.remove', array('m_pm_report')),
			array('custom', array(
					array($this, 'revert_module_auth'),
				),
			),
		);
	}

	public function update_module_auth()
	{
		$sql = 'UPDATE ' . MODULES_TABLE . "
			SET module_auth = 'acl_m_pm_report'
			WHERE module_class = 'mcp'
				AND module_basename = 'mcp_pm_reports'
				AND module_auth = 'aclf_m_report'";
		$this->db->sql_query($sql);
	}

	public function revert_module_auth()
	{
		$sql = 'UPDATE ' . MODULES_TABLE . "
			SET module_auth = 'aclf_m_report'
			WHERE module_class = 'mcp'
				AND module_basename = 'mcp_pm_reports'
				AND module_auth = 'acl_m_pm_report'";
		$this->db->sql_query($sql);
	}
}
