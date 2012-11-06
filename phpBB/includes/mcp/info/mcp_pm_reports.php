<?php
/**
*
* @package mcp
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @package module_install
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
				'pm_reports'			=> array('title' => 'MCP_PM_REPORTS_OPEN', 'auth' => 'aclf_m_report', 'cat' => array('MCP_REPORTS')),
				'pm_reports_closed'	=> array('title' => 'MCP_PM_REPORTS_CLOSED', 'auth' => 'aclf_m_report', 'cat' => array('MCP_REPORTS')),
				'pm_report_details'	=> array('title' => 'MCP_PM_REPORT_DETAILS', 'auth' => 'aclf_m_report', 'cat' => array('MCP_REPORTS')),
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
