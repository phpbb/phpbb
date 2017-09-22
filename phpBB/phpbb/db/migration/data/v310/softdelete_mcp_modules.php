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

class softdelete_mcp_modules extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT module_id
			FROM ' . MODULES_TABLE . "
			WHERE module_class = 'mcp'
				AND module_basename = 'mcp_queue'
				AND module_mode = 'deleted_topics'";
		$result = $this->db->sql_query($sql);
		$module_id = $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		return $module_id !== false;
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\dev',
			'\phpbb\db\migration\data\v310\softdelete_p2',
		);
	}

	public function update_data()
	{
		return array(
			array('module.add', array(
				'mcp',
				'MCP_QUEUE',
				array(
					'module_basename'	=> 'mcp_queue',
					'module_langname'	=> 'MCP_QUEUE_DELETED_TOPICS',
					'module_mode'		=> 'deleted_topics',
					'module_auth'		=> 'aclf_m_approve',
				),
			)),
			array('module.add', array(
				'mcp',
				'MCP_QUEUE',
				array(
					'module_basename'	=> 'mcp_queue',
					'module_langname'	=> 'MCP_QUEUE_DELETED_POSTS',
					'module_mode'		=> 'deleted_posts',
					'module_auth'		=> 'aclf_m_approve',
				),
			)),
		);
	}
}
