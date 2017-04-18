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
		global $phpbb_container, $phpbb_log, $user;

		$style_manager = $phpbb_container->get('style.manager');

		// Get list of styles to install
		$dirs = $this->request_vars('dir', '', true);

		$messages = array();

		foreach ($dirs as $dir)
		{
			try {
				$style_manager->install($dir);
				$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_STYLE_ADD', false, array($dir)); // TODO: Style name
				$messages[] = sprintf($this->user->lang['STYLE_INSTALLED'], htmlspecialchars($dir)); // TODO: Style name instead of dir
			} catch (exception $e) {
				$msg = $this->user->lang($e->getMessage());
				$messages[] = sprintf($msg, htmlspecialchars($style['style_name'])); // TODO: Style name instead of dir
			}
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
		global $user, $phpbb_log, $phpbb_container;

		$style_manager = $phpbb_container->get('style.manager');

		$default = $this->default_style;
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
			// Uninstall style
			try
			{
				$style_manager->uninstall($style['style_path']);
				$uninstalled[] = $style['style_name'];
				$messages[] = sprintf($this->user->lang['STYLE_UNINSTALLED'], htmlspecialchars($style['style_name']));
			}
			catch (exception $e)
			{
				$msg = $this->user->lang($e->getMessage());
				$messages[] = sprintf($msg, htmlspecialchars($style['style_name']));
			}

			// Attempt to delete files
			if ($delete_files)
			{
				try
				{
					$style_manager->delete_style_files($style['style_path']);
					$messages[] = sprintf($this->user->lang['DELETE_STYLE_FILES_SUCCESS'], htmlspecialchars($style['style_name']));
				}
				catch (exception $e)
				{
					$msg = $this->user->lang($e->getMessage());
					$messages[] = sprintf($msg, htmlspecialchars($style['style_name']));
				}
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
		global $phpbb_container;

		$style_manager = $phpbb_container->get('style.manager');

		// Get list of styles to activate
		$ids = $this->request_vars('id', 0, true);

		try
		{
			$style_manager->activate($ids);
			// TODO: show names instead of ids, and add one entry per style
			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_STYLE_ACTIVATE', false, array($ids));
		}
		catch (exception $e)
		{
			trigger_error($e->getMessage() . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Show styles list
		$this->frontend();
	}

	/**
	* Deactivate styles
	*/
	protected function action_deactivate()
	{
		global $phpbb_container;

		$style_manager = $phpbb_container->get('style.manager');

		// Get list of styles to deactivate
		$ids = $this->request_vars('id', 0, true);

		try
		{
			$style_manager->deactivate($ids);
			// TODO: Show names instead of ids, and add one entry per style
			$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_STYLE_DEACTIVATE', false, array($ids));
		}
		catch (exception $e)
		{
			trigger_error($e->getMessage() . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Show styles list
		$this->frontend();
	}

	/**
	* Show style details
	*/
	protected function action_details()
	{
		global $user, $phpbb_log, $phpbb_container;

		$style_manager = $phpbb_container->get('style.manager');

		$id = $this->request->variable('id', 0);
		if (!$id)
		{
			trigger_error($this->user->lang['NO_MATCHING_STYLES_FOUND'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Get all styles
		$styles = $style_manager->get_installed_styles();
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
		$style_cfg = $phpbb_container->get('style.manager')->read_style_cfg($style['style_path']);

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
					$styles = $style_manager->get_installed_styles();
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
		global $phpbb_container;

		$style_manager = $phpbb_container->get('style.manager');

		// Get all installed styles
		$styles = $style_manager->get_installed_styles();

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
		$this->styles_list_cols = 4;
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
		global $phpbb_container;

		$style_manager = $phpbb_container->get('style.manager');

		// Get list of styles
		$styles = $style_manager->find_available(true);

		// Show styles
		if (empty($styles))
		{
			trigger_error($this->user->lang['NO_UNINSTALLED_STYLE'] . adm_back_link($this->u_base_action), E_USER_NOTICE);
		}

		usort($styles, array($this, 'sort_styles'));

		$this->styles_list_cols = 3;
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

		// Generate template variables
		$actions = array();
		$row = array(
			// Style data
			'STYLE_ID'		=> $style['style_id'],
			'STYLE_NAME'	=> htmlspecialchars($style['style_name']),
			'STYLE_PATH'	=> htmlspecialchars($style['style_path']),
			'STYLE_COPYRIGHT'	=> strip_tags($style['style_copyright']),
			'STYLE_ACTIVE'	=> $style['style_active'],

			// Additional data
			'DEFAULT'		=> ($style['style_id'] && $style['style_id'] == $this->default_style),
			'USERS'			=> (isset($style['_users'])) ? $style['_users'] : '',
			'LEVEL'			=> $level,
			'PADDING'		=> (4 + 16 * $level),
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

			// Uninstall
			$actions[] = array(
				'U_ACTION'	=> $this->u_action . '&amp;action=uninstall&amp;hash=' . generate_link_hash('uninstall') . '&amp;id=' . $style['style_id'],
				'L_ACTION'	=> $this->user->lang['STYLE_UNINSTALL']
			);

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

}
