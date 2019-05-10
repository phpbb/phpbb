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

namespace phpbb\acp;

class styles
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\textformatter\cache_interface */
	protected $tf_cache;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB admin path */
	protected $admin_path;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/** @var string */
	protected $mode;

	/** @var string */
	protected $u_base_action;

	/** @var array */
	protected $s_hidden_fields;

	/** @var string */
	protected $styles_path;

	/** @var string */
	protected $styles_path_absolute = 'styles';

	/** @var int */
	protected $default_style = 0;

	/** @var int */
	protected $styles_list_cols = 0;

	/** @var array */
	protected $reserved_style_names = ['adm', 'admin', 'all'];

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth						$auth			Auth object
	 * @param \phpbb\cache\driver\driver_interface	$cache			Cache object
	 * @param \phpbb\config\config					$config			Config object
	 * @param \phpbb\db\driver\driver_interface		$db				Database object
	 * @param \phpbb\event\dispatcher				$dispatcher		Event dispatcher object
	 * @param \phpbb\language\language				$lang			Language object
	 * @param \phpbb\log\log						$log			Log object
	 * @param \phpbb\request\request				$request		Request object
	 * @param \phpbb\template\template				$template		Template object
	 * @param \phpbb\textformatter\cache_interface	$tf_cache		Textformatter cache object
	 * @param \phpbb\user							$user			User object
	 * @param string								$admin_path		phpBB admin path
	 * @param string								$root_path		phpBB root path
	 * @param string								$php_ext		php File extension
	 * @param array									$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\textformatter\cache_interface $tf_cache,
		\phpbb\user $user,
		$admin_path,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth			= $auth;
		$this->cache		= $cache;
		$this->config		= $config;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->request		= $request;
		$this->template		= $template;
		$this->tf_cache		= $tf_cache;
		$this->user			= $user;

		$this->admin_path	= $admin_path;
		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	public function main($id, $mode)
	{
		$this->lang->add_lang('acp/styles');

		$this->default_style	= $this->config['default_style'];
		$this->styles_path		= $this->root_path . $this->styles_path_absolute . '/';

		$this->u_base_action	= append_sid("{$this->admin_path}index.{$this->php_ext}", "i={$id}");
		$this->s_hidden_fields	= ['mode' => $mode];

		$this->tpl_name			= 'acp_styles';
		$this->page_title		= 'ACP_CAT_STYLES';
		$this->mode				= $mode;

		$action			= $this->request->variable('action', '');
		$post_actions	= ['install', 'activate', 'deactivate', 'uninstall'];

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
			$is_valid_request = check_link_hash($this->request->variable('hash', ''), $action) || check_form_key('styles_management');

			if (!$is_valid_request)
			{
				trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		if ($action !== '')
		{
			$this->s_hidden_fields['action'] = $action;
		}

		$this->template->assign_vars([
			'U_ACTION'			=> $this->u_base_action,
			'S_HIDDEN_FIELDS'	=> build_hidden_fields($this->s_hidden_fields),
		]);

		/**
		 * Run code before ACP styles action execution
		 *
		 * @event core.acp_styles_action_before
		 * @var	int     id          Module ID
		 * @var	string  mode        Active module
		 * @var	string  action      Module that should be run
		 * @since 3.1.7-RC1
		 */
		$vars = ['id', 'mode', 'action'];
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
	 * Main page.
	 *
	 * @return void
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

		trigger_error($this->lang->lang('NO_MODE') . adm_back_link($this->u_action), E_USER_WARNING);
	}

	/**
	 * Install style(s).
	 *
	 * @return void
	 */
	protected function action_install()
	{
		// Get list of styles to install
		$dirs = $this->request_vars('dir', '', true);

		// Get list of styles that can be installed
		$styles = $this->find_available(false);

		// Install each style
		$messages = [];
		$installed_dirs = [];
		$installed_names = [];

		foreach ($dirs as $dir)
		{
			if (in_array($dir, $this->reserved_style_names))
			{
				$messages[] = $this->lang->lang('STYLE_NAME_RESERVED', htmlspecialchars($dir));
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
					$messages[] = $this->lang->lang('STYLE_INSTALLED', htmlspecialchars($style['style_name']));
				}
			}
			if (!$found)
			{
				$messages[] = $this->lang->lang('STYLE_NOT_INSTALLED', htmlspecialchars($dir));
			}
		}

		// Invalidate the text formatter's cache for the new styles to take effect
		if (!empty($installed_names))
		{
			$this->tf_cache->invalidate();
		}

		// Show message
		if (empty($messages))
		{
			trigger_error($this->lang->lang('NO_MATCHING_STYLES_FOUND') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$message = implode('<br />', $messages);
		$message .= '<br /><br /><a href="' . $this->u_base_action . '&amp;mode=style' . '">&laquo; ' . $this->lang->lang('STYLE_INSTALLED_RETURN_INSTALLED_STYLES') . '</a>';
		$message .= '<br /><br /><a href="' . $this->u_base_action . '&amp;mode=install' . '">&raquo; ' . $this->lang->lang('STYLE_INSTALLED_RETURN_UNINSTALLED_STYLES') . '</a>';

		trigger_error($message, E_USER_NOTICE);
	}

	/**
	 * Confirm styles removal.
	 *
	 * @return void
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

		$this->template->assign_var('S_CONFIRM_DELETE', true);

		// Confirm box
		confirm_box(false, $this->lang->lang('CONFIRM_UNINSTALL_STYLES'), build_hidden_fields([
			'action'	=> 'uninstall',
			'ids'		=> $ids,
		]), 'acp_styles.html');

		// Canceled - show styles list
		$this->frontend();
	}

	/**
	 * Uninstall styles(s)
	 *
	 * @param array		$ids			List of style IDs
	 * @param bool		$delete_files	If true, script will attempt to remove files for selected styles
	 * @return void
	 */
	protected function action_uninstall_confirmed(array $ids, $delete_files)
	{
		$default = $this->default_style;
		$uninstalled = [];
		$messages = [];

		// Check styles list
		foreach ($ids as $id)
		{
			if (!$id)
			{
				trigger_error($this->lang->lang('INVALID_STYLE_ID') . adm_back_link($this->u_action), E_USER_WARNING);
			}
			if ($id == $default)
			{
				trigger_error($this->lang->lang('UNINSTALL_DEFAULT') . adm_back_link($this->u_action), E_USER_WARNING);
			}
			$uninstalled[$id] = false;
		}

		// Order by reversed style_id, so parent styles would be removed after child styles
		// This way parent and child styles can be removed in same function call
		$sql = 'SELECT *
			FROM ' . $this->tables['styles'] . '
			WHERE style_id IN (' . implode(', ', $ids) . ')
			ORDER BY style_id DESC';
		$result = $this->db->sql_query($sql);

		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		// Uninstall each style
		$uninstalled = [];
		foreach ($rows as $style)
		{
			$result = $this->uninstall_style($style);

			if (is_string($result))
			{
				$messages[] = $result;
				continue;
			}

			$messages[] = $this->lang->lang('STYLE_UNINSTALLED', $style['style_name']);
			$uninstalled[] = $style['style_name'];

			// Attempt to delete files
			if ($delete_files)
			{
				$s_deleted = $this->delete_style_files($style['style_path']);

				$messages[] = $this->lang->lang($s_deleted ? 'DELETE_STYLE_FILES_SUCCESS' : 'DELETE_STYLE_FILES_FAILED', $style['style_name']);
			}
		}

		if (empty($messages))
		{
			// Nothing to uninstall?
			trigger_error($this->lang->lang('NO_MATCHING_STYLES_FOUND') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Log action
		if (!empty($uninstalled))
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_STYLE_DELETE', false, [implode($this->lang->lang('COMMA_SEPARATOR'), $uninstalled)]);
		}

		// Clear cache
		$this->cache->purge();

		// Show message
		trigger_error(implode('<br />', $messages) . adm_back_link($this->u_action), E_USER_NOTICE);
	}

	/**
	 * Activate styles.
	 *
	 * @return void
	 */
	protected function action_activate()
	{
		// Get list of styles to activate
		$ids = $this->request_vars('id', 0, true);

		// Activate styles
		$sql = 'UPDATE ' . $this->tables['styles'] . '
			SET style_active = 1
			WHERE ' . $this->db->sql_in_set('style_id', $ids);
		$this->db->sql_query($sql);

		// Purge cache
		$this->cache->destroy('sql', $this->tables['styles']);

		// Show styles list
		$this->frontend();
	}

	/**
	 * Deactivate styles.
	 *
	 * @return void
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
				trigger_error($this->lang->lang('DEACTIVATE_DEFAULT') . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		// Reset default style for users who use selected styles
		$sql = 'UPDATE ' . $this->tables['users'] . '
			SET user_style = ' . (int) $this->default_style . '
			WHERE user_style IN (' . implode(', ', $ids) . ')';
		$this->db->sql_query($sql);

		// Deactivate styles
		$sql = 'UPDATE ' . $this->tables['styles'] . '
			SET style_active = 0
			WHERE style_id IN (' . implode(', ', $ids) . ')';
		$this->db->sql_query($sql);

		// Purge cache
		$this->cache->destroy('sql', $this->tables['styles']);

		// Show styles list
		$this->frontend();
	}

	/**
	 * Show style details.
	 *
	 * @return void
	 */
	protected function action_details()
	{
		$id = $this->request->variable('id', 0);

		if (!$id)
		{
			trigger_error($this->lang->lang('NO_MATCHING_STYLES_FOUND') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		// Get all styles
		$styles = $this->get_styles();
		usort($styles, [$this, 'sort_styles']);

		$style = false;

		// Find current style
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
			trigger_error($this->lang->lang('NO_MATCHING_STYLES_FOUND') . adm_back_link($this->u_action), E_USER_WARNING);
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
				trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$update_action = $this->u_action . '&amp;action=details&amp;id=' . $id;
			$update = [
				'style_name'		=> $this->request->variable('style_name', $style['style_name']),
				'style_parent_id'	=> $this->request->variable('style_parent', (int) $style['style_parent_id']),
				'style_active'		=> $this->request->variable('style_active', (int) $style['style_active']),
			];

			// Check style name
			if ($update['style_name'] != $style['style_name'])
			{
				if (!strlen($update['style_name']))
				{
					trigger_error($this->lang->lang('STYLE_ERR_STYLE_NAME') . adm_back_link($update_action), E_USER_WARNING);
				}
				foreach ($styles as $row)
				{
					if ($row['style_name'] == $update['style_name'])
					{
						trigger_error($this->lang->lang('STYLE_ERR_NAME_EXIST') . adm_back_link($update_action), E_USER_WARNING);
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
						trigger_error($this->lang->lang('STYLE_ERR_INVALID_PARENT') . adm_back_link($update_action), E_USER_WARNING);
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
					trigger_error($this->lang->lang('DEACTIVATE_DEFAULT') . adm_back_link($update_action), E_USER_WARNING);
				}
			}
			else
			{
				unset($update['style_active']);
			}

			// Update data
			if (!empty($update))
			{
				$sql = 'UPDATE ' . $this->tables['styles'] . '
					SET ' . $this->db->sql_build_array('UPDATE', $update) . '
					WHERE style_id = ' . $id;
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

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_STYLE_EDIT_DETAILS', false, [$style['style_name']]);
			}

			// Update default style
			$default = $this->request->variable('style_default', 0);
			if ($default)
			{
				if (!$style['style_active'])
				{
					trigger_error($this->lang->lang('STYLE_DEFAULT_CHANGE_INACTIVE') . adm_back_link($update_action), E_USER_WARNING);
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
			$this->template->assign_block_vars('parent_styles', [
				'STYLE_ID'		=> $row['style_id'],
				'STYLE_NAME'	=> htmlspecialchars($row['style_name']),
				'LEVEL'			=> $row['level'],
				'SPACER'		=> str_repeat('&nbsp; ', $row['level']),
			]);
		}

		// Show style details
		$this->template->assign_vars([
			'S_STYLE_DETAILS'	=> true,
			'STYLE_ID'			=> $style['style_id'],
			'STYLE_NAME'		=> htmlspecialchars($style['style_name']),
			'STYLE_PATH'		=> htmlspecialchars($style['style_path']),
			'STYLE_VERSION'		=> htmlspecialchars($style_cfg['style_version']),
			'STYLE_COPYRIGHT'	=> strip_tags($style['style_copyright']),
			'STYLE_PARENT'		=> $style['style_parent_id'],
			'S_STYLE_ACTIVE'	=> $style['style_active'],
			'S_STYLE_DEFAULT'	=> $style['style_id'] == $this->default_style,
		]);
	}

	/**
	 * List installed styles.
	 *
	 * @return void
	 */
	protected function show_installed()
	{
		// Get all installed styles
		$styles = $this->get_styles();

		if (empty($styles))
		{
			trigger_error($this->lang->lang('NO_MATCHING_STYLES_FOUND') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		usort($styles, [$this, 'sort_styles']);

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
				$style['_note'] = $this->lang->lang('REQUIRES_STYLE', htmlspecialchars($style['style_parent_tree']));
				$this->list_style($style, 0);
			}
		}

		// Add buttons
		$this->template->assign_block_vars('extra_actions', [
			'ACTION_NAME'	=> 'activate',
			'L_ACTION'		=> $this->lang->lang('STYLE_ACTIVATE'),
		]);

		$this->template->assign_block_vars('extra_actions', [
			'ACTION_NAME'	=> 'deactivate',
			'L_ACTION'		=> $this->lang->lang('STYLE_DEACTIVATE'),
		]);

		if (isset($this->style_counters) && $this->style_counters['total'] > 1)
		{
			$this->template->assign_block_vars('extra_actions', [
				'ACTION_NAME'	=> 'uninstall',
				'L_ACTION'		=> $this->lang->lang('STYLE_UNINSTALL'),
			]);
		}
	}

	/**
	 * Show list of styles that can be installed.
	 *
	 * @return void
	 */
	protected function show_available()
	{
		// Get list of styles
		$styles = $this->find_available(true);

		// Show styles
		if (empty($styles))
		{
			trigger_error($this->lang->lang('NO_UNINSTALLED_STYLE') . adm_back_link($this->u_base_action), E_USER_NOTICE);
		}

		usort($styles, [$this, 'sort_styles']);

		$this->styles_list_cols = 3;

		$this->template->assign_vars([
			'STYLES_LIST_COLS'			=> $this->styles_list_cols,
			'STYLES_LIST_HIDE_COUNT'	=> true,
		]);

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
			$this->template->assign_block_vars('extra_actions', [
				'ACTION_NAME'	=> 'install',
				'L_ACTION'		=> $this->lang->lang('INSTALL_STYLES'),
			]);
		}
	}

	/**
	 * Find styles available for installation.
	 *
	 * @param bool		$all		if true, function will return all installable styles
	 *                  			if false, function will return only styles that can be installed
	 * @return array				The styles list
	 */
	protected function find_available($all)
	{
		// Get list of installed styles
		$installed = $this->get_styles();

		$styles = [];
		$installed_dirs = [];
		$installed_names = [];

		foreach ($installed as $style)
		{
			$installed_dirs[] = $style['style_path'];

			$installed_names[$style['style_name']] = [
				'path'		=> $style['style_path'],
				'id'		=> $style['style_id'],
				'parent'	=> $style['style_parent_id'],
				'tree'		=> strlen($style['style_parent_tree'] ? $style['style_parent_tree'] . '/' : '') . $style['style_path'],
			];
		}

		// Get list of directories
		$dirs = $this->find_style_dirs();

		// Find styles that can be installed
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
			$style = [
				'style_id'				=> 0,
				'style_name'			=> $cfg['name'],
				'style_copyright'		=> $cfg['copyright'],
				'style_active'			=> 0,
				'style_path'			=> $dir,
				'bbcode_bitfield'		=> $cfg['template_bitfield'],
				'style_parent_id'		=> 0,
				'style_parent_tree'		=> '',

				// Extra values for styles list
				// All extra variable start with _ so they won't be confused with data that can be added to styles table
				'_inherit_name'			=> $parent,
				'_available'			=> true,
				'_note'					=> '',
			];

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
					$style['_note'] = $this->lang->lang('REQUIRES_STYLE', htmlspecialchars($parent));
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
	 * @param array		$styles			The styles list
	 * @param int		$parent			The parent style identifier
	 * @param int		$level			The style inheritance level
	 * @return void
	 */
	protected function show_styles_list(array &$styles, $parent, $level)
	{
		foreach ($styles as &$style)
		{
			if (empty($style['_shown']) && $style['style_parent_id'] === $parent)
			{
				$this->list_style($style, $level);
				$this->show_styles_list($styles, $style['style_id'], $level + 1);
			}
		}
	}

	/**
	 * Show available styles tree.
	 *
	 * @param array		$styles			The styles list
	 * @param string	$name			The parent style name
	 * @param int		$level			The styles inheritance level
	 * @return void
	 */
	protected function show_available_child_styles(array &$styles, $name, $level)
	{
		foreach ($styles as &$style)
		{
			if (empty($style['_shown']) && $style['_inherit_name'] === $name)
			{
				$this->list_style($style, $level);
				$this->show_available_child_styles($styles, $style['style_name'], $level + 1);
			}
		}
	}

	/**
	 * Update styles tree.
	 *
	 * @param array			$styles		The styles list
	 * @param array|false	$style		The current style, false if root
	 * @return bool						True if something was updated, false if not
	 */
	protected function update_styles_tree(array &$styles, $style = false)
	{
		$update			= false;
		$updated		= false;
		$parent_id		= $style === false ? 0 : $style['style_id'];
		$parent_tree	= $style === false ? '' : ($style['style_parent_tree'] == '' ? '' : $style['style_parent_tree']) . $style['style_path'];

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
			$sql = 'UPDATE ' . $this->tables['styles'] . "
				SET style_parent_tree = '" . $this->db->sql_escape($parent_tree) . "'
				WHERE style_parent_id = " . (int) $parent_id;
			$this->db->sql_query($sql);

			$updated = true;
		}

		return $updated;
	}

	/**
	 * Find all possible parent styles for style.
	 *
	 * @param array		$styles		The styles list
	 * @param int		$id			The style identifier
	 * @param int		$parent		The parent style identifier
	 * @param int		$level		The current tree level
	 * @return array				The style identifiers, names and levels
	 */
	protected function find_possible_parents(array $styles, $id = -1, $parent = 0, $level = 0)
	{
		$results = [];

		foreach ($styles as $style)
		{
			if ($style['style_id'] != $id && $style['style_parent_id'] == $parent)
			{
				$results[] = [
					'style_id'			=> $style['style_id'],
					'style_name'		=> $style['style_name'],
					'style_path'		=> $style['style_path'],
					'style_parent_id'	=> $style['style_parent_id'],
					'style_parent_tree'	=> $style['style_parent_tree'],
					'level'				=> $level,
				];

				$results = array_merge($results, $this->find_possible_parents($styles, $id, $style['style_id'], $level + 1));
			}
		}

		return $results;
	}

	/**
	 * Show item in styles list
	 *
	 * @param array		$style		The style data
	 * @param int		$level		The style inheritance level
	 * @return void
	 */
	protected function list_style(array &$style, $level)
	{
		// Mark row as shown
		if (!empty($style['_shown']))
		{
			return;
		}

		$style['_shown'] = true;

		// Generate template variables
		$actions = [];
		$row = [
			// Style data
			'STYLE_ID'				=> $style['style_id'],
			'STYLE_NAME'			=> htmlspecialchars($style['style_name']),
			'STYLE_PATH'			=> htmlspecialchars($style['style_path']),
			'STYLE_COPYRIGHT'		=> strip_tags($style['style_copyright']),
			'STYLE_ACTIVE'			=> $style['style_active'],
			'STYLE_PHPBB_VERSION'	=> $this->read_style_cfg($style['style_path'])['phpbb_version'],

			// Additional data
			'DEFAULT'			=> ($style['style_id'] && $style['style_id'] == $this->default_style),
			'USERS'				=> isset($style['_users']) ? $style['_users'] : '',
			'LEVEL'				=> $level,
			'PADDING'			=> (4 + 16 * $level),
			'SHOW_COPYRIGHT'	=> ($style['style_id']) ? false : true,
			'STYLE_PATH_FULL'	=> htmlspecialchars($this->styles_path_absolute . '/' . $style['style_path']) . '/',

			// Comment to show below style
			'COMMENT'			=> isset($style['_note']) ? $style['_note'] : '',

			// The following variables should be used by hooks to add custom HTML code
			'EXTRA'				=> '',
			'EXTRA_OPTIONS'		=> '',
		];

		// Status specific data
		if ($style['style_id'])
		{
			// Style is installed

			// Details
			$actions[] = [
				'L_ACTION'	=> $this->lang->lang('DETAILS'),
				'U_ACTION'	=> $this->u_action . '&amp;action=details&amp;id=' . $style['style_id'],
			];

			// Activate/Deactivate
			$action_name = ($style['style_active'] ? 'de' : '') . 'activate';

			$actions[] = [
				'L_ACTION'	=> $this->lang->lang['STYLE_' . ($style['style_active'] ? 'DE' : '') . 'ACTIVATE'],
				'U_ACTION'	=> $this->u_action . '&amp;action=' . $action_name . '&amp;hash=' . generate_link_hash($action_name) . '&amp;id=' . $style['style_id'],
			];

			/*			// Export
						$actions[] = array(
							'L_ACTION'	=> $this->lang->lang('EXPORT'),
							'U_ACTION'	=> $this->u_action . '&amp;action=export&amp;hash=' . generate_link_hash('export') . '&amp;id=' . $style['style_id'],
						); */

			// Uninstall
			$actions[] = [
				'L_ACTION'	=> $this->lang->lang('STYLE_UNINSTALL'),
				'U_ACTION'	=> $this->u_action . '&amp;action=uninstall&amp;hash=' . generate_link_hash('uninstall') . '&amp;id=' . $style['style_id'],
			];

			// Preview
			$actions[] = [
				'L_ACTION'	=> $this->lang->lang('PREVIEW'),
				'U_ACTION'	=> append_sid($this->root_path . 'index.' . $this->php_ext, 'style=' . $style['style_id']),
			];
		}
		else
		{
			// Style is not installed
			if (empty($style['_available']))
			{
				$actions[] = [
					'HTML'		=> $this->lang->lang('CANNOT_BE_INSTALLED'),
				];
			}
			else
			{
				$actions[] = [
					'U_ACTION'	=> $this->u_action . '&amp;action=install&amp;hash=' . generate_link_hash('install') . '&amp;dir=' . urlencode($style['style_path']),
					'L_ACTION'	=> $this->lang->lang('INSTALL_STYLE'),
				];
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
		$counter = $style['style_id'] ? ($style['style_active'] ? 'active' : 'inactive') : (empty($style['_available']) ? 'cannotinstall' : 'caninstall');

		if (!isset($this->style_counters))
		{
			$this->style_counters = [
				'total'			=> 0,
				'active'		=> 0,
				'inactive'		=> 0,
				'caninstall'	=> 0,
				'cannotinstall'	=> 0,
			];
		}

		$this->style_counters[$counter]++;
		$this->style_counters['total']++;
	}

	/**
	 * Show welcome message.
	 *
	 * @param string	$title			The page title
	 * @param string	$description	The page description
	 * @retun void
	 */
	protected function welcome_message($title, $description)
	{
		$this->template->assign_vars([
			'L_TITLE'	=> $this->lang->lang[$title],
			'L_EXPLAIN'	=> $this->lang->is_set($description) ? $this->lang->lang($description) : '',
		]);
	}

	/**
	 * Find all directories that have styles.
	 *
	 * @return array 					Directory names
	 */
	protected function find_style_dirs()
	{
		$styles = [];

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
	 * Sort styles.
	 *
	 * @param array		$style1
	 * @param array		$style2
	 * @return int
	 */
	public function sort_styles(array $style1, array $style2)
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
	 * Read style configuration file.
	 *
	 * @param string		$dir	The style directory
	 * @return array|bool			The style data, false on error
	 */
	protected function read_style_cfg($dir)
	{
		static $required = ['name', 'phpbb_version', 'copyright'];

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
	 * Install style.
	 *
	 * @param array		$style		The style data
	 * @return int					The style identifier
	 */
	protected function install_style(array $style)
	{
		// Generate row
		$sql_ary = [];
		foreach ($style as $key => $value)
		{
			if ($key != 'style_id' && substr($key, 0, 1) != '_')
			{
				$sql_ary[$key] = $value;
			}
		}

		// Add to database
		$this->db->sql_transaction('begin');

		$sql = 'INSERT INTO ' . $this->tables['styles'] . '
			' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		$id = $this->db->sql_nextid();

		$this->db->sql_transaction('commit');

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_STYLE_ADD', false, [$sql_ary['style_name']]);

		return $id;
	}

	/**
	 * Lists all styles.
	 *
	 * @return array				Rows with styles data
	 */
	protected function get_styles()
	{
		$sql = 'SELECT *
			FROM ' . $this->tables['styles'];
		$result = $this->db->sql_query($sql);

		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Count users for each style.
	 *
	 * @return array				Styles in following format: [style_id] = number of users
	 */
	protected function get_users()
	{
		$style_count = [];

		$sql = 'SELECT user_style, COUNT(user_style) AS style_count
			FROM ' . $this->tables['users'] . '
			GROUP BY user_style';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$style_count[$row['user_style']] = $row['style_count'];
		}
		$this->db->sql_freeresult($result);

		return $style_count;
	}

	/**
	 * Uninstall style.
	 *
	 * @param array			$style		The style data
	 * @return bool|string				True on success, error message on error
	 */
	protected function uninstall_style(array $style)
	{
		$id		= $style['style_id'];
		$path	= $style['style_path'];

		// Check if style has child styles
		$sql = 'SELECT style_id
			FROM ' . $this->tables['styles'] . '
			WHERE style_parent_id = ' . (int) $id . " OR style_parent_tree = '" . $this->db->sql_escape($path) . "'";
		$result = $this->db->sql_query($sql);
		$conflict = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($conflict !== false)
		{
			return $this->lang->lang('STYLE_UNINSTALL_DEPENDENT', $style['style_name']);
		}

		// Change default style for users
		$sql = 'UPDATE ' . $this->tables['users'] . '
			SET user_style = ' . (int) $this->default_style . '
			WHERE user_style = ' . $id;
		$this->db->sql_query($sql);

		// Uninstall style
		$sql = 'DELETE FROM ' . $this->tables['styles'] . '
			WHERE style_id = ' . $id;
		$this->db->sql_query($sql);

		return true;
	}

	/**
	 * Delete all files in style directory.
	 *
	 * @param string	$path		The style directory
	 * @param string	$dir		Directory to remove inside the style's directory
	 * @return bool					True on success, false on error
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
	 * Get list of items from posted data.
	 *
	 * @param string		$name		Variable name
	 * @param string|int	$default	Default value for array
	 * @param bool			$error		If true, error will be triggered if list is empty
	 * @return array					Items
	 */
	protected function request_vars($name, $default, $error = false)
	{
		$item = $this->request->variable($name, $default);
		$items = $this->request->variable($name . 's', [$default]);

		if (count($items) === 1 && $items[0] == $default)
		{
			$items = [];
		}

		if ($item != $default && empty($items))
		{
			$items[] = $item;
		}

		if ($error && empty($items))
		{
			trigger_error($this->lang->lang('NO_MATCHING_STYLES_FOUND') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		return $items;
	}

	/**
	 * Generates default bitfield.
	 *
	 * This bitfield decides which bbcodes are defined in a template.
	 *
	 * @return string			Bitfield
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

		$bitfield = new \bitfield();

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
