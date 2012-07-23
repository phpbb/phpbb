<?php
/**
*
* @package acp
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_extensions
{
	var $u_action;

	function main()
	{
		// Start the page
		global $user, $template, $request, $phpbb_extension_manager, $db, $phpbb_root_path, $phpEx;

		$user->add_lang(array('install', 'acp/extensions'));

		$this->page_title = 'ACP_EXTENSIONS';

		$action = $request->variable('action', 'list');
		$ext_name = $request->variable('ext_name', '');

		// What are we doing?
		switch ($action)
		{
			case 'list':
			default:
				$this->list_enabled_exts($phpbb_extension_manager, $template);
				$this->list_disabled_exts($phpbb_extension_manager, $template);
				$this->list_available_exts($phpbb_extension_manager, $template);

				$this->tpl_name = 'acp_ext_list';
			break;

			case 'enable_pre':
				$this->tpl_name = 'acp_ext_enable';

				$template->assign_vars(array(
					'PRE'		=> true,
					'U_ENABLE'	=> $this->u_action . '&amp;action=enable&amp;ext_name=' . $ext_name,
				));
			break;

			case 'enable':
				$phpbb_extension_manager->enable($ext_name);

				$this->tpl_name = 'acp_ext_enable';

				$template->assign_vars(array(
					'U_RETURN'	=> $this->u_action . '&amp;action=list',
				));
			break;

			case 'disable_pre':
				$this->tpl_name = 'acp_ext_disable';

				$template->assign_vars(array(
					'PRE'		=> true,
					'U_DISABLE'	=> $this->u_action . '&amp;action=disable&amp;ext_name=' . $ext_name,
				));				
			break;	

			case 'disable':
				$phpbb_extension_manager->disable($ext_name);

				$this->tpl_name = 'acp_ext_disable';

				$template->assign_vars(array(
					'U_RETURN'	=> $this->u_action . '&amp;action=list',
				));
			break;

			case 'purge_pre':
				$this->tpl_name = 'acp_ext_purge';

				$template->assign_vars(array(
					'PRE'		=> true,
					'U_PURGE'	=> $this->u_action . '&amp;action=purge&amp;ext_name=' . $ext_name,
				));
			break;		

			case 'purge':
				$phpbb_extension_manager->purge($ext_name);

				$this->tpl_name = 'acp_ext_purge';

				$template->assign_vars(array(
					'U_RETURN'	=> $this->u_action . '&amp;action=list',
				));
			break;

			/*case 'delete_pre':
				$this->tpl_name = 'acp_ext_delete';

				$template->assign_vars(array(
					'PRE'		=> true,
					'U_DELETE'	=> $this->u_action . '&amp;action=delete&amp;ext_name=' . $ext_name,
				));
			break;

			case 'delete':
				$this->tpl_name = 'acp_ext_delete';
			break;*/

			case 'details':
				$md_manager = new phpbb_extension_metadata_manager($ext_name, $db, $phpbb_extension_manager, $phpbb_root_path, ".$phpEx", $template);
				
				if ($md_manager->get_metadata('all', true) === false)
				{
					trigger_error('EXTENSION_INVALID');
				}

				$this->tpl_name = 'acp_ext_details';
			break;
		}
	}

	/**
	 * Lists all the enabled extensions and dumps to the template
	 * 
	 * @param  $phpbb_extension_manager     An instance of the extension manager
	 * @param  $template 					An instance of the template engine
	 * @return null
	 */
	private function list_enabled_exts(phpbb_extension_manager $phpbb_extension_manager, phpbb_template $template)
	{
		foreach ($phpbb_extension_manager->all_enabled() as $name => $location)
		{
			$md_manager = $phpbb_extension_manager->get_extension_metadata($name, $template);
			
			$template->assign_block_vars('enabled', array(
				'EXT_NAME'		=> $md_manager->get_metadata('name'),

				'U_DETAILS'		=> $this->u_action . '&amp;action=details&amp;ext_name=' . $name,
				'U_PURGE'		=> $this->u_action . '&amp;action=purge_pre&amp;ext_name=' . $name,
				'U_DISABLE'		=> $this->u_action . '&amp;action=disable_pre&amp;ext_name=' . $name,
			));
		}
	}

	/**
	 * Lists all the disabled extensions and dumps to the template
	 * 
	 * @param  $phpbb_extension_manager     An instance of the extension manager
	 * @param  $template 					An instance of the template engine
	 * @return null
	 */
	private function list_disabled_exts(phpbb_extension_manager $phpbb_extension_manager, phpbb_template $template)
	{
		foreach ($phpbb_extension_manager->all_disabled() as $name => $location)
		{
			$md_manager = $phpbb_extension_manager->get_extension_metadata($name, $template);
			
			$template->assign_block_vars('disabled', array(
				'EXT_NAME'		=> $md_manager->get_metadata('name'),

				'U_DETAILS'		=> $this->u_action . '&amp;action=details&amp;ext_name=' . $name,
				'U_PURGE'		=> $this->u_action . '&amp;action=purge_pre&amp;ext_name=' . $name,
				//'U_DELETE'		=> $this->u_action . '&amp;action=delete_pre&amp;ext_name=' . $name,
				'U_ENABLE'		=> $this->u_action . '&amp;action=enable_pre&amp;ext_name=' . $name,
			));
		}
	}

	/**
	 * Lists all the available extensions and dumps to the template
	 * 
	 * @param  $phpbb_extension_manager     An instance of the extension manager
	 * @param  $template 					An instance of the template engine
	 * @return null
	 */
	function list_available_exts(phpbb_extension_manager $phpbb_extension_manager, phpbb_template $template)
	{
		$uninstalled = array_diff_key($phpbb_extension_manager->all_available(), $phpbb_extension_manager->all_configured());

		foreach ($uninstalled as $name => $location)
		{
			$md_manager = $phpbb_extension_manager->get_extension_metadata($name, $template);

			$template->assign_block_vars('disabled', array(
				'EXT_NAME'		=> $md_manager->get_metadata('name'),

				'U_DETAILS'		=> $this->u_action . '&amp;action=details&amp;ext_name=' . $name,
				//'U_DELETE'		=> $this->u_action . '&amp;action=delete_pre&amp;ext_name=' . $name,
				'U_ENABLE'		=> $this->u_action . '&amp;action=enable_pre&amp;ext_name=' . $name,
			));
		}
	}
}
