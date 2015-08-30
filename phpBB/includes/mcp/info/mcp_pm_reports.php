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

class mcp_pm_reports_info
{
	function module()
	{
		return array(
			'filename'	=> 'mcp_pm_reports',
			'title'		=> 'MCP_PM_REPORTS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'pm_reports'		=> array('title' => 'MCP_PM_REPORTS_OPEN', 'auth' => 'acl_m_pm_report', 'cat' => array('MCP_REPORTS')),
				'pm_reports_closed'	=> array('title' => 'MCP_PM_REPORTS_CLOSED', 'auth' => 'acl_m_pm_report', 'cat' => array('MCP_REPORTS')),
				'pm_report_details'	=> array('title' => 'MCP_PM_REPORT_DETAILS', 'auth' => 'acl_m_pm_report', 'cat' => array('MCP_REPORTS')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}
