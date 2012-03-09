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
		global $user, $template, $request;

		$user->add_lang(array('install', 'acp/customisations'));

		$this->page_title = 'ACP_EXTENSIONS';

		$action = $request->variable('action', '');
		$ext_name = $request->variable('ext_name', '');

		// Set action to list if not set
		if (empty($action))
		{
			$action = 'list';
		}

		// What are we doing?
		switch ($action)
		{
			case 'list':
			default:
				$this->list_enabled_exts();
				$this->list_disabled_exts();
				$this->tpl_name = 'acp_ext_list';
			break;

			case 'enable_pre':
				$this->tpl_name = 'acp_ext_enable';
				$template->assign_vars(array(
					'PRE'		=> true,
					'U_ENABLE'	=> $this->u_action . '&amp;action=enable&amp;ext_name=' . $ext_name,
				   )
				);
			break;

			case 'enable':
				$this->enable_extension($ext_name);
				$this->tpl_name = 'acp_ext_enable';
			break;

			case 'disable_pre':
				$this->tpl_name = 'acp_ext_disable';
				$template->assign_vars(array(
					'PRE'		=> true,
					'U_DISABLE'	=> $this->u_action . '&amp;action=disable&amp;ext_name=' . $ext_name,
				   )
				);				
			break;	

			case 'disable':
				$this->disable_extension($ext_name);
				$this->tpl_name = 'acp_ext_disable';
			break;

			case 'purge_pre':
				$this->tpl_name = 'acp_ext_purge';
				$template->assign_vars(array(
					'PRE'		=> true,
					'U_PURGE'	=> $this->u_action . '&amp;action=purge&amp;ext_name=' . $ext_name,
				   )
				);
			break;		

			case 'purge':
				$this->purge_extension($ext_name);
				$this->tpl_name = 'acp_ext_purge';
			break;

			case 'delete_pre':
				$this->tpl_name = 'acp_ext_delete';
				$template->assign_vars(array(
					'PRE'		=> true,
					'U_DELETE'	=> $this->u_action . '&amp;action=delete&amp;ext_name=' . $ext_name,
				   )
				);
			break;

			case 'delete':
				$this->tpl_name = 'acp_ext_delete';
			break;

			case 'details':
				$filepath = $phpbb_root_path . 'ext/' . $ext_name . '/extension.json';
				$this->tpl_name = 'acp_ext_details';
				$this->get_meta_info($filepath);
			break;
		}
	}
		
	function enable_extension($ext_name)
	{
		global $phpbb_extension_manager, $template, $cache;

		$phpbb_extension_manager->enable($name);
		$template->assign_vars(array(
			'U_RETURN'	=> $this->u_action . '&amp;action=list',
		));
		$cache->purge();
	}

	function disable_extension($ext_name)
	{
		global $phpbb_extension_manager, $template, $cache;
		$phpbb_extension_manager->disable($name);
		$template->assign_vars(array(
			'U_RETURN'	=> $this->u_action . '&amp;action=list',
		));
		$cache->purge();
	}

	function purge_extension($ext_name)
	{
		global $phpbb_extension_manager, $template, $cache;
		$phpbb_extension_manager->purge($name);
		$template->assign_vars(array(
			'U_RETURN'	=> $this->u_action . '&amp;action=list',
		));
		$cache->purge();
	}

	function list_enabled_exts()
	{
		global $db, $template;

		$sql = 'SELECT ext_name
			FROM ' . EXT_TABLE . '
			WHERE ext_active= 1
			ORDER BY ext_name ASC';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('enabled', array(
				'EXT_NAME'		=> $row['ext_name'],

				'U_DETAILS'		=> $this->u_action . '&amp;action=details&amp;ext_name=' . $row['ext_name'],
				'U_PURGE'		=> $this->u_action . '&amp;action=purge_pre&amp;ext_name=' . $row['ext_name'],
				'U_DISABLE'		=> $this->u_action . '&amp;action=disable_pre&amp;ext_name=' . $row['ext_name'],
			));
		}
		$db->sql_freeresult($result);

		return;
	}

	function list_disabled_exts()
	{
		global $db, $template;

		$sql = 'SELECT ext_name
			FROM ' . EXT_TABLE . '
			WHERE ext_active= 0
			ORDER BY ext_name ASC';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('disabled', array(
				'EXT_NAME'		=> $row['ext_name'],

				'U_DETAILS'		=> $this->u_action . '&amp;action=details&amp;ext_name=' . $row['ext_name'],
				'U_PURGE'		=> $this->u_action . '&amp;action=purge_pre&amp;ext_name=' . $row['ext_name'],
				'U_DELETE'		=> $this->u_action . '&amp;action=delete_pre&amp;ext_name=' . $row['ext_name'],
				'U_ENABLE'		=> $this->u_action . '&amp;action=enable_pre&amp;ext_name=' . $row['ext_name'],
			));
		}
		$db->sql_freeresult($result);

		return;
	}

	function list_available_exts()
	{
		$phpbb_extension_manager->load_extensions();
		$all_available = array_keys($phpbb_extension_manager->all_available());
		$all_configured = array_keys($phpbb_extension_manager->all_configured());
		$uninstalled = array_diff($all_available, $all_configured);

		foreach ($uninstalled as $ext)
		{
			$template->assign_block_vars('disabled', array(
				'EXT_NAME'		=> $ext['ext_name'],

				'U_DETAILS'		=> $this->u_action . '&amp;action=details&amp;ext_name=' . $ext['ext_name'],
				'U_DELETE'		=> $this->u_action . '&amp;action=delete_pre&amp;ext_name=' . $ext['ext_name'],
				'U_ENABLE'		=> $this->u_action . '&amp;action=enable_pre&amp;ext_name=' . $ext['ext_name'],
			));
		}

		return;
	}

	function get_meta_info($filepath)
	{
		global $template;

		$metadatafile = file_get_contents($filepath);
		$metadata = json_decode($metadatafile,true);

		$template->assign_vars(array(
			'NAME'			=> $metadata['name'],
			'TYPE'			=> $metadata['type'],
			'DESCRIPTION'	=> $metadata['description'],
			'HOMEPAGE'		=> $metadata['homepage'],
			'VERSION'		=> $metadata['version'],
			'TIME'			=> $metadata['time'],
			'LICENSE'		=> $metadata['licence'],
			'REQUIRE_PHP'	=> $metadata['require']['php'],
			'REQUIRE_PHPBB'	=> $metadata['require']['phpbb'],
			'DISPLAY_NAME'	=> $metadata['extra']['display-name'],
			)
		);

		foreach ($metadata["authors"] as $author)
		{
			$template->assign_block_vars('authors', array(
				'AUTHOR_NAME'		=> $author["name"],
				'AUTHOR_USERNAME'	=> $author["username"],
				'AUTHOR_EMAIL'		=> $author["email"],
				'AUTHOR_HOMEPAGE'	=> $author["homepage"],
				'AUTHOR_ROLE'		=> $author["role"],
			));
		}
	}
}
