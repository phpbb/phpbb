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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_styles
{
	public $u_action;

	protected $u_base_action;
	protected $s_hidden_fields;
	protected $mode;
	protected $styles_path;
	protected $styles_path_absolute = 'styles';
	protected $default_style = 0;
	protected $styles_list_cols = 0;
	protected $reserved_style_names = array('adm', 'admin', 'all');

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\textformatter\cache_interface */
	protected $text_formatter_cache;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	public function main($id, $mode)
	{
		global $db, $user, $phpbb_admin_path, $phpbb_root_path, $phpEx, $template, $request, $cache, $auth, $config, $phpbb_dispatcher, $phpbb_container;

		$this->db = $db;
		$this->user = $user;
		$this->template = $template;
		$this->request = $request;
		$this->cache = $cache;
		$this->auth = $auth;
		$this->text_formatter_cache = $phpbb_container->get('text_formatter.cache');
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;
		$this->dispatcher = $phpbb_dispatcher;

		$this->default_style = $config['default_style'];
		$this->styles_path = $this->phpbb_root_path . $this->styles_path_absolute . '/';

		$this->u_base_action = append_sid("{$phpbb_admin_path}index.{$this->php_ext}", "i={$id}");
		$this->s_hidden_fields = array(
			'mode'		=> $mode,
		);

		$this->user->add_lang('acp/styles');

		$this->tpl_name = 'acp_styles';
		$this->page_title = 'ACP_CAT_STYLES';
		$this->mode = $mode;

		$action = $this->request->variable('action', '');
		$post_actions = array('install', 'activate', 'deactivate', 'uninstall');

		foreach ($post_actions as $key)
		{
			if ($this->request->is_set_post($key))
			{
				$action = $key;
			}
		}

		// The uninstall action uses confirm_box() to verify the validity of the request,
		// so there is no need to check for a valid token here.
		if (in_array($action, $post_actions) && $action != 'uninstall')
		{
			$is_valid_request = check_link_hash($request->variable('hash', ''), $action) || check_form_key('styles_management');

			if (!$is_valid_request)
			{
				trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		if ($action != '')
		{
			$this->s_hidden_fields['action'] = $action;
		}

		$this->template->assign_vars(array(
			'U_ACTION'			=> $this->u_base_action,
			'S_HIDDEN_FIELDS'	=> build_hidden_fields($this->s_hidden_fields)
			)
		);

		/**
		 * Run code before ACP styles action execution
		 *
		 * @event core.acp_styles_action_before
		 * @var	int     id          Module ID
		 * @var	string  mode        Active module
		 * @var	string  action      Module that should be run
		 * @since 3.1.7-RC1
		 */
		$vars = array('id', 'mode', 'action');
		extract($this->dispatcher->trigger_event('core.acp_styles_action_before', compact($vars)));

		// Execute actions
		switch ($action)
		{
			case 'install':
				$this->action_install();
				return;
			case 'uninstall':
				$this->action_uninstall();
				return;
			case 'activate':
				$this->action_activate();
				return;
			case 'deactivate':
				$this->action_deactivate();
				return;
			case 'details':
				$this->action_details();
				return;
			default:
				$this->frontend();
		}
	}

	/**
	* Main page
	*/
	protected function frontend()
	{
		add_form_key('styles_management');

		// Check mode
		switch ($this->mode)
		{
			case 'style':
				$this->welcome_message('ACP_STYLES', 'ACP_STYLES_EXPLAIN');
				$this->show_installed();
				return;
			case 'install':
				$this->welcome_message('INSTALL_STYLES', 'INSTALL_STYLES_EXPLAIN');
				$this->show_available();
				return;
		}
		trigger_error($this->user->lang['NO_MODE'] . adm_back_link($this->u_action), E_USER_WARNING);
	}

	/**
	* Install style(s)
	*/
	protected function action_install()
	{
		// Get list of styles to install
		$dirs = $this->request_vars('dir', '', true);

		// Get list of styles that can be installed
		$styles = $this->find_available(false);

		// Install each style
		$messages = array();
		$installed_names = array();
		$installed_dirs = array();
		foreach ($dirs as $dir)
		{
			if (in_array($dir, $this->reserved_style_names))
			{
				$messages[] = $this->user->lang('STYLE_NAME_RESERVED', htmlspecialchars($dir));
				continue;
			}

			$found = false;
			foreach ($styles as &$style)
			{
				// Check if:
				// 1. Directory matches directory we are looking for
				// 2. Style is not installed yet
				// 3. Style with same name or directory hasn't been installed already within this function
				if ($style['style_path'] == $dir && empty($style['_installed']) && !in_array($style['style_path'], $installed_dirs) && !in_array($style['style_name'], $installed_names))
				{
					// Install style
					$style['style_active'] = 1;
					$style['style_id'] = $this->install_style($style);
					$style['_installed'] = true;
					$found = true;
					$installed_names[] = $style['style_name'];
					$installed_dirs[] = $style['style_path'];
					$messages[] = sprintf($this->user->lang['STYLE_INSTALLED'], htmlspecialchars($style['style_name']));
				}
			}
			if (!$found)
			{
				$messages[] = sprintf($this->user->lang['STYLE_NOT_INSTALLED'], htmlspecialchars($dir));
			}
		}

		// Invalidate the text formatter's cache for the new styles to take effect
		if (!empty($installed_names))
		{
			$this->text_formatter_cache->invalidate();
		}

		// Show message
		if (!count($messages))
		{
			trigger_error($this->user->lang['NO_MATCHING_STYLES_FOUND'] . adm_back_link($this->u_action), E_USER_WARNING);
		}
		$message = implode('<br />', $messages);
		$message .= '<br /><br /><a href="' . $this->u_base_action . '&amp;mode=style' . '">&laquo; ' . $this->user->lang('STYLE_INSTALLED_RETURN_INSTALLED_STYLES') . '</a>';
		$message .= '<br /><br /><a href="' . $this->u_base_action . '&amp;mode=install' . '">&raquo; ' . $this->user->lang('STYLE_INSTALLED_RETURN_UNINSTALLED_STYLES') . '</a>';
		trigger_error($message, E_USER_NOTICE);
	}

	/**
	* Confirm styles removal
	*/
	protected function action_uninstall()
	{
		// Get list of styles to uninstall
		$ids = $this->request_vars('id', 0, true);

		// Don't remove prosilver, you can still deactivate it.
		$sql = 'SELECT style_id
			FROM ' . STYLES_TABLE . "
			WHERE style_name = '" . $this->db->sql_escape('prosilver') . "'";
		$result = $this->db->sql_query($sql);
		$prosilver_id = (int) $this->db->sql_fetchfield('style_id');
		$this->db->sql_freeresult($result);

		if ($prosilver_id && in_array($prosilver_id, $ids))
		{
			trigger_error($this->user->lang('UNINSTALL_PROSILVER') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Check if confirmation box was submitted
		if (confirm_box(true))
		{
			// Uninstall
			$this->action_uninstall_confirmed($ids, $this->request->variable('confirm_delete_files', false));
			return;
		}

		// Confirm box
		$s_hidden = build_hidden_fields(array(
			'action'	=> 'uninstall',
			'ids'		=> $ids
		));
		$this->template->assign_var('S_CONFIRM_DELETE', true);
		confirm_box(false, $this->user->lang['CONFIRM_UNINSTALL_STYLES'], $s_hidden, 'acp_styles.html');

		// Canceled - show styles list
		$this->frontend();
	}

	/**
	* Uninstall styles(s)
	*
	* @param array $ids List of style IDs
	* @param bool $delete_files If true, script will attempt to remove files for selected styles
	*/
	protected function action_uninstall_confirmed($ids, $delete_files)
	{
		global $user, $phpbb_log;

		$default = $this->default_style;
		$uninstalled = array();
		$messages = array();

		// Check styles list
		foreach ($ids as $id)
		{
			if (!$id)
			{
				trigger_error($this->user->lang['INVALID_STYLE_ID'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
			if ($id == $default)
			{
				trigger_error($this->user->lang['UNINSTALL_DEFAULT'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
			$uninstalled[$id] = false;
		}

		// Order by reversed style_id, so parent styles would be removed after child styles
		// This way parent and child styles can be removed in same function call
		$sql = 'SELECT *
			FROM ' . STYLES_TABLE . '
			WHERE style_id IN (' . implode(', ', $ids) . ')
			ORDER BY style_id DESC';
		$result = $this->db->sql_query($sql);

		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		// Uinstall each style
		$uninstalled = array();
		foreach ($rows as $style)
		{
			$result = $this->uninstall_style($style, $delete_files);

			if (is_string($result))
			{
				$messages[] = $result;
				continue;
			}
			$messages[] = sprintf($this->user->lang['STYLE_UNINSTALLED'], $style['style_name']);
			$uninstalled[] = $style['style_name'];

			// Attempt to delete files
			if ($delete_files)
			{
				$messages[] = sprintf($this->user->lang[$this->delete_style_files($style['style_path']) ? 'DELETE_STYLE_FILES_SUCCESS' : 'DELETE_STYLE_FILES_FAILED'], $style['style_name']);
			}
		}

		if (empty($messages))
		{
			// Nothing to uninstall?
			trigger_error($this->user->lang['NO_MATCHING_STYLES_FOUND'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Log action
		if (count($uninstalled))
		{
			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_STYLE_DELETE', false, array(implode(', ', $uninstalled)));
		}

		// Clear cache
		$this->cache->purge();

		// Show message
		trigger_error(implode('<br />', $messages) . adm_back_link($this->u_action), E_USER_NOTICE);
	}

	/**
	* Activate styles
	*/
	protected function action_activate()
	{
		// Get list of styles to activate
		$ids = $this->request_vars('id', 0, true);

		// Activate styles
		$sql = 'UPDATE ' . STYLES_TABLE . '
			SET style_active = 1
			WHERE style_id IN (' . implode(', ', $ids) . ')';
		$this->db->sql_query($sql);

		// Purge cache
		$this->cache->destroy('sql', STYLES_TABLE);

		// Show styles list
		$this->frontend();
	}

	/**
	* Deactivate styles
	*/
	protected function action_deactivate()
	{
		// Get list of styles to deactivate
		$ids = $this->request_vars('id', 0, true);

		// Check for default style
		foreach ($ids as $id)
		{
			if ($id == $this->default_style)
			{
				trigger_error($this->user->lang['DEACTIVATE_DEFAULT'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		// Reset default style for users who use selected styles
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_style = ' . (int) $this->default_style . '
			WHERE user_style IN (' . implode(', ', $ids) . ')';
		$this->db->sql_query($sql);

		// Deactivate styles
		$sql = 'UPDATE ' . STYLES_TABLE . '
			SET style_active = 0
			WHERE style_id IN (' . implode(', ', $ids) . ')';
		$this->db->sql_query($sql);

		// Purge cache
		$this->cache->destroy('sql', STYLES_TABLE);

		// Show styles list
		$this->frontend();
	}

	/**
	* Show style details
	*/
	protected function action_details()
	{
		global $user, $phpbb_log;

		$id = $this->request->variable('id', 0);
		if (!$id)
		{
			trigger_error($this->user->lang['NO_MATCHING_STYLES_FOUND'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Get all styles
		$styles = $this->get_styles();
		usort($styles, array($this, 'sort_styles'));

		// Find current style
		$style = false;
		foreach ($styles as $row)
		{
			if ($row['style_id'] == $id)
			{
				$style = $row;
				break;
			}
		}

		if ($style === false)
		{
			trigger_error($this->user->lang['NO_MATCHING_STYLES_FOUND'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Read style configuration file
		$style_cfg = $this->read_style_cfg($style['style_path']);

		// Find all available parent styles
		$list = $this->find_possible_parents($styles, $id);

		// Add form key
		$form_key = 'acp_styles';
		add_form_key($form_key);

		// Change data
		if ($this->request->variable('update', false))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$update = array(
				'style_name'		=> trim($this->request->variable('style_name', $style['style_name'])),
				'style_parent_id'	=> $this->request->variable('style_parent', (int) $style['style_parent_id']),
				'style_active'		=> $this->request->variable('style_active', (int) $style['style_active']),
			);
			$update_action = $this->u_action . '&amp;action=details&amp;id=' . $id;

			// Check style name
			if ($update['style_name'] != $style['style_name'])
			{
				if (!strlen($update['style_name']))
				{
					trigger_error($this->user->lang['STYLE_ERR_STYLE_NAME'] . adm_back_link($update_action), E_USER_WARNING);
				}
				foreach ($styles as $row)
				{
					if ($row['style_name'] == $update['style_name'])
					{
						trigger_error($this->user->lang['STYLE_ERR_NAME_EXIST'] . adm_back_link($update_action), E_USER_WARNING);
					}
				}
			}
			else
			{
				unset($update['style_name']);
			}

			// Check parent style id
			if ($update['style_parent_id'] != $style['style_parent_id'])
			{
				if ($update['style_parent_id'] != 0)
				{
					$found = false;
					foreach ($list as $row)
					{
						if ($row['style_id'] == $update['style_parent_id'])
						{
							$found = true;
							$update['style_parent_tree'] = ($row['style_parent_tree'] != '' ? $row['style_parent_tree'] . '/' : '') . $row['style_path'];
							break;
						}
					}
					if (!$found)
					{
						trigger_error($this->user->lang['STYLE_ERR_INVALID_PARENT'] . adm_back_link($update_action), E_USER_WARNING);
					}
				}
				else
				{
					$update['style_parent_tree'] = '';
				}
			}
			else
			{
				unset($update['style_parent_id']);
			}

			// Check style_active
			if ($update['style_active'] != $style['style_active'])
			{
				if (!$update['style_active'] && $this->default_style == $style['style_id'])
				{
					trigger_error($this->user->lang['DEACTIVATE_DEFAULT'] . adm_back_link($update_action), E_USER_WARNING);
				}
			}
			else
			{
				unset($update['style_active']);
			}

			// Update data
			if (count($update))
			{
				$sql = 'UPDATE ' . STYLES_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $update) . "
					WHERE style_id = $id";
				$this->db->sql_query($sql);

				$style = array_merge($style, $update);

				if (isset($update['style_parent_id']))
				{
					// Update styles tree
					$styles = $this->get_styles();
					if ($this->update_styles_tree($styles, $style))
					{
						// Something was changed in styles tree, purge all cache
						$this->cache->purge();
					}
				}

				$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_STYLE_EDIT_DETAILS', false, array($style['style_name']));
			}

			// Update default style
			$default = $this->request->variable('style_default', 0);
			if ($default)
			{
				if (!$style['style_active'])
				{
					trigger_error($this->user->lang['STYLE_DEFAULT_CHANGE_INACTIVE'] . adm_back_link($update_action), E_USER_WARNING);
				}
				$this->config->set('default_style', $id);
				$this->cache->purge();
			}

			// Show styles list
			$this->frontend();
			return;
		}

		// Show page title
		$this->welcome_message('ACP_STYLES', null);

		// Show parent styles
		foreach ($list as $row)
		{
			$this->template->assign_block_vars('parent_styles', array(
				'STYLE_ID'		=> $row['style_id'],
				'STYLE_NAME'	=> htmlspecialchars($row['style_name']),
				'LEVEL'			=> $row['level'],
				'SPACER'		=> str_repeat('&nbsp; ', $row['level']),
				)
			);
		}

		// Show style details
		$this->template->assign_vars(array(
			'S_STYLE_DETAILS'	=> true,
			'STYLE_ID'			=> $style['style_id'],
			'STYLE_NAME'		=> htmlspecialchars($style['style_name']),
			'STYLE_PATH'		=> htmlspecialchars($style['style_path']),
			'STYLE_VERSION'		=> htmlspecialchars($style_cfg['style_version']),
			'STYLE_COPYRIGHT'	=> strip_tags($style['style_copyright']),
			'STYLE_PARENT'		=> $style['style_parent_id'],
			'S_STYLE_ACTIVE'	=> $style['style_active'],
			'S_STYLE_DEFAULT'	=> ($style['style_id'] == $this->default_style)
			)
		);
	}

	/**
	* List installed styles
	*/
	protected function show_installed()
	{
		// Get all installed styles
		$styles = $this->get_styles();

		if (!count($styles))
		{
			trigger_error($this->user->lang['NO_MATCHING_STYLES_FOUND'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		usort($styles, array($this, 'sort_styles'));

		// Get users
		$users = $this->get_users();

		// Add users counter to rows
		foreach ($styles as &$style)
		{
			$style['_users'] = isset($users[$style['style_id']]) ? $users[$style['style_id']] : 0;
		}

		// Set up styles list variables
		// Addons should increase this number and update template variable
		$this->styles_list_cols = 5;
		$this->template->assign_var('STYLES_LIST_COLS', $this->styles_list_cols);

		// Show styles list
		$this->show_styles_list($styles, 0, 0);

		// Show styles with invalid inherits_id
		foreach ($styles as $style)
		{
			if (empty($style['_shown']))
			{
				$style['_note'] = sprintf($this->user->lang['REQUIRES_STYLE'], htmlspecialchars($style['style_parent_tree']));
				$this->list_style($style, 0);
			}
		}

		// Add buttons
		$this->template->assign_block_vars('extra_actions', array(
				'ACTION_NAME'	=> 'activate',
				'L_ACTION'		=> $this->user->lang['STYLE_ACTIVATE'],
			)
		);

		$this->template->assign_block_vars('extra_actions', array(
				'ACTION_NAME'	=> 'deactivate',
				'L_ACTION'		=> $this->user->lang['STYLE_DEACTIVATE'],
			)
		);

		if (isset($this->style_counters) && $this->style_counters['total'] > 1)
		{
			$this->template->assign_block_vars('extra_actions', array(
					'ACTION_NAME'	=> 'uninstall',
					'L_ACTION'		=> $this->user->lang['STYLE_UNINSTALL'],
				)
			);
		}
	}

	/**
	* Show list of styles that can be installed
	*/
	protected function show_available()
	{
		// Get list of styles
		$styles = $this->find_available(true);

		// Show styles
		if (empty($styles))
		{
			trigger_error($this->user->lang['NO_UNINSTALLED_STYLE'] . adm_back_link($this->u_base_action), E_USER_NOTICE);
		}

		usort($styles, array($this, 'sort_styles'));

		$this->styles_list_cols = 4;
		$this->template->assign_vars(array(
			'STYLES_LIST_COLS'	=> $this->styles_list_cols,
			'STYLES_LIST_HIDE_COUNT'	=> true
			)
		);

		// Show styles
		foreach ($styles as &$style)
		{
			// Check if style has a parent style in styles list
			$has_parent = false;
			if ($style['_inherit_name'] != '')
			{
				foreach ($styles as $parent_style)
				{
					if ($parent_style['style_name'] == $style['_inherit_name'] && empty($parent_style['_shown']))
					{
						// Show parent style first
						$has_parent = true;
					}
				}
			}
			if (!$has_parent)
			{
				$this->list_style($style, 0);
				$this->show_available_child_styles($styles, $style['style_name'], 1);
			}
		}

		// Show styles that do not have parent style in styles list
		foreach ($styles as $style)
		{
			if (empty($style['_shown']))
			{
				$this->list_style($style, 0);
			}
		}

		// Add button
		if (isset($this->style_counters) && $this->style_counters['caninstall'] > 0)
		{
			$this->template->assign_block_vars('extra_actions', array(
					'ACTION_NAME'	=> 'install',
					'L_ACTION'		=> $this->user->lang['INSTALL_STYLES'],
				)
			);
		}
	}

	/**
	* Find styles available for installation
	*
	* @param bool $all if true, function will return all installable styles. if false, function will return only styles that can be installed
	* @return array List of styles
	*/
	protected function find_available($all)
	{
		// Get list of installed styles
		$installed = $this->get_styles();

		$installed_dirs = array();
		$installed_names = array();
		foreach ($installed as $style)
		{
			$installed_dirs[] = $style['style_path'];
			$installed_names[$style['style_name']] = array(
				'path'		=> $style['style_path'],
				'id'		=> $style['style_id'],
				'parent'	=> $style['style_parent_id'],
				'tree'		=> (strlen($style['style_parent_tree']) ? $style['style_parent_tree'] . '/' : '') . $style['style_path'],
			);
		}

		// Get list of directories
		$dirs = $this->find_style_dirs();

		// Find styles that can be installed
		$styles = array();
		foreach ($dirs as $dir)
		{
			if (in_array($dir, $installed_dirs))
			{
				// Style is already installed
				continue;
			}
			$cfg = $this->read_style_cfg($dir);
			if ($cfg === false)
			{
				// Invalid style.cfg
				continue;
			}

			// Style should be available for installation
			$parent = $cfg['parent'];
			$style = array(
				'style_id'			=> 0,
				'style_name'		=> $cfg['name'],
				'style_copyright'	=> $cfg['copyright'],
				'style_active'		=> 0,
				'style_path'		=> $dir,
				'bbcode_bitfield'	=> $cfg['template_bitfield'],
				'style_parent_id'	=> 0,
				'style_parent_tree'	=> '',
				// Extra values for styles list
				// All extra variable start with _ so they won't be confused with data that can be added to styles table
				'_inherit_name'			=> $parent,
				'_available'			=> true,
				'_note'					=> '',
			);

			// Check style inheritance
			if ($parent != '')
			{
				if (isset($installed_names[$parent]))
				{
					// Parent style is installed
					$row = $installed_names[$parent];
					$style['style_parent_id'] = $row['id'];
					$style['style_parent_tree'] = $row['tree'];
				}
				else
				{
					// Parent style is not installed yet
					$style['_available'] = false;
					$style['_note'] = sprintf($this->user->lang['REQUIRES_STYLE'], htmlspecialchars($parent));
				}
			}

			if ($all || $style['_available'])
			{
				$styles[] = $style;
			}
		}

		return $styles;
	}

	/**
	* Show styles list
	*
	* @param array $styles styles list
	* @param int $parent parent style id
	* @param int $level style inheritance level
	*/
	protected function show_styles_list(&$styles, $parent, $level)
	{
		foreach ($styles as &$style)
		{
			if (empty($style['_shown']) && $style['style_parent_id'] == $parent)
			{
				$this->list_style($style, $level);
				$this->show_styles_list($styles, $style['style_id'], $level + 1);
			}
		}
	}

	/**
	* Show available styles tree
	*
	* @param array $styles Styles list, passed as reference
	* @param string $name Name of parent style
	* @param int $level Styles tree level
	*/
	protected function show_available_child_styles(&$styles, $name, $level)
	{
		foreach ($styles as &$style)
		{
			if (empty($style['_shown']) && $style['_inherit_name'] == $name)
			{
				$this->list_style($style, $level);
				$this->show_available_child_styles($styles, $style['style_name'], $level + 1);
			}
		}
	}

	/**
	* Update styles tree
	*
	* @param array $styles Styles list, passed as reference
	* @param array|false $style Current style, false if root
	* @return bool True if something was updated, false if not
	*/
	protected function update_styles_tree(&$styles, $style = false)
	{
		$parent_id = ($style === false) ? 0 : $style['style_id'];
		$parent_tree = ($style === false) ? '' : ($style['style_parent_tree'] == '' ? '' : $style['style_parent_tree']) . $style['style_path'];
		$update = false;
		$updated = false;
		foreach ($styles as &$row)
		{
			if ($row['style_parent_id'] == $parent_id)
			{
				if ($row['style_parent_tree'] != $parent_tree)
				{
					$row['style_parent_tree'] = $parent_tree;
					$update = true;
				}
				$updated |= $this->update_styles_tree($styles, $row);
			}
		}
		if ($update)
		{
			$sql = 'UPDATE ' . STYLES_TABLE . "
				SET style_parent_tree = '" . $this->db->sql_escape($parent_tree) . "'
				WHERE style_parent_id = {$parent_id}";
			$this->db->sql_query($sql);
			$updated = true;
		}
		return $updated;
	}

	/**
	* Find all possible parent styles for style
	*
	* @param array $styles list of styles
	* @param int $id id of style
	* @param int $parent current parent style id
	* @param int $level current tree level
	* @return array Style ids, names and levels
	*/
	protected function find_possible_parents($styles, $id = -1, $parent = 0, $level = 0)
	{
		$results = array();
		foreach ($styles as $style)
		{
			if ($style['style_id'] != $id && $style['style_parent_id'] == $parent)
			{
				$results[] = array(
					'style_id'		=> $style['style_id'],
					'style_name'	=> $style['style_name'],
					'style_path'	=> $style['style_path'],
					'style_parent_id'	=> $style['style_parent_id'],
					'style_parent_tree'	=> $style['style_parent_tree'],
					'level'			=> $level
				);
				$results = array_merge($results, $this->find_possible_parents($styles, $id, $style['style_id'], $level + 1));
			}
		}
		return $results;
	}

	/**
	* Show item in styles list
	*
	* @param array $style style row
	* @param int $level style inheritance level
	*/
	protected function list_style(&$style, $level)
	{
		// Mark row as shown
		if (!empty($style['_shown']))
		{
			return;
		}

		$style['_shown'] = true;

		$style_cfg = $this->read_style_cfg($style['style_path']);

		// Generate template variables
		$actions = array();
		$row = array(
			// Style data
			'STYLE_ID'				=> $style['style_id'],
			'STYLE_NAME'			=> htmlspecialchars($style['style_name']),
			'STYLE_VERSION'			=> $style_cfg['style_version'] ?? '-',
			'STYLE_PHPBB_VERSION'	=> $style_cfg['phpbb_version'],
			'STYLE_PATH'			=> htmlspecialchars($style['style_path']),
			'STYLE_COPYRIGHT'		=> strip_tags($style['style_copyright']),
			'STYLE_ACTIVE'			=> $style['style_active'],

			// Additional data
			'DEFAULT'			=> ($style['style_id'] && $style['style_id'] == $this->default_style),
			'USERS'				=> (isset($style['_users'])) ? $style['_users'] : '',
			'LEVEL'				=> $level,
			'PADDING'			=> (4 + 16 * $level),
			'SHOW_COPYRIGHT'	=> ($style['style_id']) ? false : true,
			'STYLE_PATH_FULL'	=> htmlspecialchars($this->styles_path_absolute . '/' . $style['style_path']) . '/',

			// Comment to show below style
			'COMMENT'		=> (isset($style['_note'])) ? $style['_note'] : '',

			// The following variables should be used by hooks to add custom HTML code
			'EXTRA'			=> '',
			'EXTRA_OPTIONS'	=> ''
		);

		// Status specific data
		if ($style['style_id'])
		{
			// Style is installed

			// Details
			$actions[] = array(
				'U_ACTION'	=> $this->u_action . '&amp;action=details&amp;id=' . $style['style_id'],
				'L_ACTION'	=> $this->user->lang['DETAILS']
			);

			// Activate/Deactive
			$action_name = ($style['style_active'] ? 'de' : '') . 'activate';

			$actions[] = array(
				'U_ACTION'	=> $this->u_action . '&amp;action=' . $action_name . '&amp;hash=' . generate_link_hash($action_name) . '&amp;id=' . $style['style_id'],
				'L_ACTION'	=> $this->user->lang['STYLE_' . ($style['style_active'] ? 'DE' : '') . 'ACTIVATE']
			);

/*			// Export
			$actions[] = array(
				'U_ACTION'	=> $this->u_action . '&amp;action=export&amp;hash=' . generate_link_hash('export') . '&amp;id=' . $style['style_id'],
				'L_ACTION'	=> $this->user->lang['EXPORT']
			); */

			if ($style['style_name'] !== 'prosilver')
			{
				// Uninstall
				$actions[] = array(
					'U_ACTION'	=> $this->u_action . '&amp;action=uninstall&amp;hash=' . generate_link_hash('uninstall') . '&amp;id=' . $style['style_id'],
					'L_ACTION'	=> $this->user->lang['STYLE_UNINSTALL']
				);
			}

			// Preview
			$actions[] = array(
				'U_ACTION'	=> append_sid($this->phpbb_root_path . 'index.' . $this->php_ext, 'style=' . $style['style_id']),
				'L_ACTION'	=> $this->user->lang['PREVIEW']
			);
		}
		else
		{
			// Style is not installed
			if (empty($style['_available']))
			{
				$actions[] = array(
					'HTML'		=> $this->user->lang['CANNOT_BE_INSTALLED']
				);
			}
			else
			{
				$actions[] = array(
					'U_ACTION'	=> $this->u_action . '&amp;action=install&amp;hash=' . generate_link_hash('install') . '&amp;dir=' . urlencode($style['style_path']),
					'L_ACTION'	=> $this->user->lang['INSTALL_STYLE']
				);
			}
		}

		// todo: add hook

		// Assign template variables
		$this->template->assign_block_vars('styles_list', $row);
		foreach ($actions as $action)
		{
			$this->template->assign_block_vars('styles_list.actions', $action);
		}

		// Increase counters
		$counter = ($style['style_id']) ? ($style['style_active'] ? 'active' : 'inactive') : (empty($style['_available']) ? 'cannotinstall' : 'caninstall');
		if (!isset($this->style_counters))
		{
			$this->style_counters = array(
				'total'		=> 0,
				'active'	=> 0,
				'inactive'	=> 0,
				'caninstall'	=> 0,
				'cannotinstall'	=> 0
				);
		}
		$this->style_counters[$counter]++;
		$this->style_counters['total']++;
	}

	/**
	* Show welcome message
	*
	* @param string $title main title
	* @param string $description page description
	*/
	protected function welcome_message($title, $description)
	{
		$this->template->assign_vars(array(
			'L_TITLE'	=> $this->user->lang[$title],
			'L_EXPLAIN'	=> (isset($this->user->lang[$description])) ? $this->user->lang[$description] : ''
			)
		);
	}

	/**
	* Find all directories that have styles
	*
	* @return array Directory names
	*/
	protected function find_style_dirs()
	{
		$styles = array();

		$dp = @opendir($this->styles_path);
		if ($dp)
		{
			while (($file = readdir($dp)) !== false)
			{
				$dir = $this->styles_path . $file;
				if ($file[0] == '.' || !is_dir($dir))
				{
					continue;
				}

				if (file_exists("{$dir}/style.cfg"))
				{
					$styles[] = $file;
				}
			}
			closedir($dp);
		}

		return $styles;
	}

	/**
	* Sort styles
	*/
	public function sort_styles($style1, $style2)
	{
		if ($style1['style_active'] != $style2['style_active'])
		{
			return ($style1['style_active']) ? -1 : 1;
		}
		if (isset($style1['_available']) && $style1['_available'] != $style2['_available'])
		{
			return ($style1['_available']) ? -1 : 1;
		}
		return strcasecmp(isset($style1['style_name']) ? $style1['style_name'] : $style1['name'], isset($style2['style_name']) ? $style2['style_name'] : $style2['name']);
	}

	/**
	* Read style configuration file
	*
	* @param string $dir style directory
	* @return array|bool Style data, false on error
	*/
	protected function read_style_cfg($dir)
	{
		// This should never happen, we give them a red warning because of its relevance.
		if (!file_exists($this->styles_path . $dir . '/style.cfg'))
		{
			trigger_error($this->user->lang('NO_STYLE_CFG', $dir), E_USER_WARNING);
		}

		static $required = array('name', 'phpbb_version', 'copyright');

		$cfg = parse_cfg_file($this->styles_path . $dir . '/style.cfg');

		// Check if it is a valid file
		foreach ($required as $key)
		{
			if (!isset($cfg[$key]))
			{
				return false;
			}
		}

		// Check data
		if (!isset($cfg['parent']) || !is_string($cfg['parent']) || $cfg['parent'] == $cfg['name'])
		{
			$cfg['parent'] = '';
		}
		if (!isset($cfg['template_bitfield']))
		{
			$cfg['template_bitfield'] = $this->default_bitfield();
		}

		return $cfg;
	}

	/**
	* Install style
	*
	* @param array $style style data
	* @return int Style id
	*/
	protected function install_style($style)
	{
		global $user, $phpbb_log;

		// Generate row
		$sql_ary = array();
		foreach ($style as $key => $value)
		{
			if ($key != 'style_id' && substr($key, 0, 1) != '_')
			{
				$sql_ary[$key] = $value;
			}
		}

		// Add to database
		$this->db->sql_transaction('begin');

		$sql = 'INSERT INTO ' . STYLES_TABLE . '
			' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		$id = $this->db->sql_nextid();

		$this->db->sql_transaction('commit');

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_STYLE_ADD', false, array($sql_ary['style_name']));

		return $id;
	}

	/**
	* Lists all styles
	*
	* @return array Rows with styles data
	*/
	protected function get_styles()
	{
		$sql = 'SELECT *
			FROM ' . STYLES_TABLE;
		$result = $this->db->sql_query($sql);

		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	* Count users for each style
	*
	* @return array Styles in following format: [style_id] = number of users
	*/
	protected function get_users()
	{
		$sql = 'SELECT user_style, COUNT(user_style) AS style_count
			FROM ' . USERS_TABLE . '
			GROUP BY user_style';
		$result = $this->db->sql_query($sql);

		$style_count = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$style_count[$row['user_style']] = $row['style_count'];
		}
		$this->db->sql_freeresult($result);

		return $style_count;
	}

	/**
	* Uninstall style
	*
	* @param array $style Style data
	* @return bool|string True on success, error message on error
	*/
	protected function uninstall_style($style)
	{
		$id = $style['style_id'];
		$path = $style['style_path'];

		// Check if style has child styles
		$sql = 'SELECT style_id
			FROM ' . STYLES_TABLE . '
			WHERE style_parent_id = ' . (int) $id . " OR style_parent_tree = '" . $this->db->sql_escape($path) . "'";
		$result = $this->db->sql_query($sql);

		$conflict = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($conflict !== false)
		{
			return sprintf($this->user->lang['STYLE_UNINSTALL_DEPENDENT'], $style['style_name']);
		}

		// Change default style for users
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_style = ' . (int) $this->default_style . '
			WHERE user_style = ' . $id;
		$this->db->sql_query($sql);

		// Uninstall style
		$sql = 'DELETE FROM ' . STYLES_TABLE . '
			WHERE style_id = ' . $id;
		$this->db->sql_query($sql);
		return true;
	}

	/**
	* Delete all files in style directory
	*
	* @param string $path Style directory
	* @param string $dir Directory to remove inside style's directory
	* @return bool True on success, false on error
	*/
	protected function delete_style_files($path, $dir = '')
	{
		$dirname = $this->styles_path . $path . $dir;
		$result = true;

		$dp = @opendir($dirname);

		if ($dp)
		{
			while (($file = readdir($dp)) !== false)
			{
				if ($file == '.' || $file == '..')
				{
					continue;
				}
				$filename = $dirname . '/' . $file;
				if (is_dir($filename))
				{
					if (!$this->delete_style_files($path, $dir . '/' . $file))
					{
						$result = false;
					}
				}
				else
				{
					if (!@unlink($filename))
					{
						$result = false;
					}
				}
			}
			closedir($dp);
		}
		if (!@rmdir($dirname))
		{
			return false;
		}

		return $result;
	}

	/**
	* Get list of items from posted data
	*
	* @param string $name Variable name
	* @param string|int $default Default value for array
	* @param bool $error If true, error will be triggered if list is empty
	* @return array Items
	*/
	protected function request_vars($name, $default, $error = false)
	{
		$item = $this->request->variable($name, $default);
		$items = $this->request->variable($name . 's', array($default));

		if (count($items) == 1 && $items[0] == $default)
		{
			$items = array();
		}

		if ($item != $default && !count($items))
		{
			$items[] = $item;
		}

		if ($error && !count($items))
		{
			trigger_error($this->user->lang['NO_MATCHING_STYLES_FOUND'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		return $items;
	}

	/**
	* Generates default bitfield
	*
	* This bitfield decides which bbcodes are defined in a template.
	*
	* @return string Bitfield
	*/
	protected function default_bitfield()
	{
		static $value;
		if (isset($value))
		{
			return $value;
		}

		// Hardcoded template bitfield to add for new templates
		$default_bitfield = '1111111111111';

		$bitfield = new bitfield();
		for ($i = 0; $i < strlen($default_bitfield); $i++)
		{
			if ($default_bitfield[$i] == '1')
			{
				$bitfield->set($i);
			}
		}

		return $bitfield->get_base64();
	}

}
