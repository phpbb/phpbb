<?php
/**
*
* @package acp
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @package module_install
*/
class acp_modules_info
{
	function module()
	{
		global $phpbb_dispatcher;

		$modules = array(
				'acp'		=> array('title' => 'ACP', 'auth' => 'acl_a_modules', 'cat' => array('ACP_MODULE_MANAGEMENT')),
				'ucp'		=> array('title' => 'UCP', 'auth' => 'acl_a_modules', 'cat' => array('ACP_MODULE_MANAGEMENT')),
				'mcp'		=> array('title' => 'MCP', 'auth' => 'acl_a_modules', 'cat' => array('ACP_MODULE_MANAGEMENT')),
		),

		$vars = array('modules');
		$event = new phpbb_event_data(compact($vars));
		$phpbb_dispatcher->dispatch('core.acp_modules_modules', $event);
		extract($event->get_data_filtered($vars));

		$data = array(
			'filename'	=> 'acp_modules',
			'title'		=> 'ACP_MODULE_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> $modules
		);

		return $data
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}
