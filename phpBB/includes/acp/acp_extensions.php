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
		global $config, $user, $template, $request, $phpbb_extension_manager, $db, $phpbb_root_path, $phpEx;

		$user->add_lang(array('install', 'acp/extensions'));

		$this->page_title = 'ACP_EXTENSIONS';

		$action = $request->variable('action', 'list');
		$ext_name = $request->variable('ext_name', '');

		// If they've specificed an extension, let's load the metadata manager and validate it.
		if ($ext_name)
		{
			$md_manager = new phpbb_extension_metadata_manager($ext_name, $db, $phpbb_extension_manager, $phpbb_root_path, ".$phpEx", $template, $config);

			if ($md_manager->get_metadata('all') === false)
			{
				trigger_error('EXTENSION_INVALID');
			}
		}

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
				if (!$md_manager->validate_enable())
				{
					trigger_error('EXTENSION_NOT_AVAILABLE');
				}

				$this->tpl_name = 'acp_ext_enable';

				$template->assign_vars(array(
					'PRE'		=> true,
					'U_ENABLE'	=> $this->u_action . '&amp;action=enable&amp;ext_name=' . $ext_name,
				));
			break;

			case 'enable':
				if (!$md_manager->validate_enable())
				{
					trigger_error('EXTENSION_NOT_AVAILABLE');
				}

				if ($phpbb_extension_manager->enable_step($ext_name))
				{
					$template->assign_var('S_NEXT_STEP', true);

					meta_refresh(0, $this->u_action . '&amp;action=enable&amp;ext_name=' . $ext_name);
				}

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
				if ($phpbb_extension_manager->disable_step($ext_name))
				{
					$template->assign_var('S_NEXT_STEP', true);

					meta_refresh(0, $this->u_action . '&amp;action=disable&amp;ext_name=' . $ext_name);
				}

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
				if ($phpbb_extension_manager->purge_step($ext_name))
				{
					$template->assign_var('S_NEXT_STEP', true);

					meta_refresh(0, $this->u_action . '&amp;action=purge&amp;ext_name=' . $ext_name);
				}

				$this->tpl_name = 'acp_ext_purge';

				$template->assign_vars(array(
					'U_RETURN'	=> $this->u_action . '&amp;action=list',
				));
			break;

			case 'details':
				// Output it to the template
				$md_manager->output_template_data();

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
	public function list_enabled_exts(phpbb_extension_manager $phpbb_extension_manager, phpbb_template $template)
	{
		foreach ($phpbb_extension_manager->all_enabled() as $name => $location)
		{
			$md_manager = $phpbb_extension_manager->get_extension_metadata_manager($name, $template);

			$template->assign_block_vars('enabled', array(
				'EXT_NAME'		=> $md_manager->get_metadata('display-name'),

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
	public function list_disabled_exts(phpbb_extension_manager $phpbb_extension_manager, phpbb_template $template)
	{
		foreach ($phpbb_extension_manager->all_disabled() as $name => $location)
		{
			$md_manager = $phpbb_extension_manager->get_extension_metadata_manager($name, $template);

			$template->assign_block_vars('disabled', array(
				'EXT_NAME'		=> $md_manager->get_metadata('display-name'),

				'U_DETAILS'		=> $this->u_action . '&amp;action=details&amp;ext_name=' . $name,
				'U_PURGE'		=> $this->u_action . '&amp;action=purge_pre&amp;ext_name=' . $name,
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
	public function list_available_exts(phpbb_extension_manager $phpbb_extension_manager, phpbb_template $template)
	{
		$uninstalled = array_diff_key($phpbb_extension_manager->all_available(), $phpbb_extension_manager->all_configured());

		foreach ($uninstalled as $name => $location)
		{
			$md_manager = $phpbb_extension_manager->get_extension_metadata_manager($name, $template);

			$template->assign_block_vars('disabled', array(
				'EXT_NAME'		=> $md_manager->get_metadata('display-name'),

				'U_DETAILS'		=> $this->u_action . '&amp;action=details&amp;ext_name=' . $name,
				'U_ENABLE'		=> $this->u_action . '&amp;action=enable_pre&amp;ext_name=' . $name,
			));
		}
	}
}
