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

namespace phpbb\install\module\install_data\task;

use phpbb\install\exception\resource_limit_reached_exception;
use phpbb\install\helper\config;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\iohandler\iohandler_interface;

class add_modules extends \phpbb\install\task_base
{
	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\extension\manager
	 */
	protected $extension_manager;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var \phpbb\module\module_manager
	 */
	protected $module_manager;

	/**
	 * Define the module structure so that we can populate the database without
	 * needing to hard-code module_id values
	 *
	 * @var array
	 */
	protected $module_categories = array(
		'acp' => array(
			'ACP_CAT_GENERAL' => array(
				'ACP_QUICK_ACCESS',
				'ACP_BOARD_CONFIGURATION',
				'ACP_CLIENT_COMMUNICATION',
				'ACP_SERVER_CONFIGURATION',
			),
			'ACP_CAT_FORUMS' => array(
				'ACP_MANAGE_FORUMS',
				'ACP_FORUM_BASED_PERMISSIONS',
			),
			'ACP_CAT_POSTING' => array(
				'ACP_MESSAGES',
				'ACP_ATTACHMENTS',
			),
			'ACP_CAT_USERGROUP' => array(
				'ACP_CAT_USERS',
				'ACP_GROUPS',
				'ACP_USER_SECURITY',
			),
			'ACP_CAT_PERMISSIONS' => array(
				'ACP_GLOBAL_PERMISSIONS',
				'ACP_FORUM_BASED_PERMISSIONS',
				'ACP_PERMISSION_ROLES',
				'ACP_PERMISSION_MASKS',
			),
			'ACP_CAT_CUSTOMISE' => array(
				'ACP_STYLE_MANAGEMENT',
				'ACP_EXTENSION_MANAGEMENT',
				'ACP_LANGUAGE',
			),
			'ACP_CAT_MAINTENANCE' => array(
				'ACP_FORUM_LOGS',
				'ACP_CAT_DATABASE',
			),
			'ACP_CAT_SYSTEM' => array(
				'ACP_AUTOMATION',
				'ACP_GENERAL_TASKS',
				'ACP_MODULE_MANAGEMENT',
			),
			'ACP_CAT_DOT_MODS' => null,
		),
		'mcp' => array(
			'MCP_MAIN'		=> null,
			'MCP_QUEUE'		=> null,
			'MCP_REPORTS'	=> null,
			'MCP_NOTES'		=> null,
			'MCP_WARN'		=> null,
			'MCP_LOGS'		=> null,
			'MCP_BAN'		=> null,
		),
		'ucp' => array(
			'UCP_MAIN'			=> null,
			'UCP_PROFILE'		=> null,
			'UCP_PREFS'			=> null,
			'UCP_PM'			=> null,
			'UCP_USERGROUPS'	=> null,
			'UCP_ZEBRA'			=> null,
		),
	);

	/**
	 * @var array
	 */
	protected $module_categories_basenames = array(
		'UCP_PM' => 'ucp_pm',
	);

	/**
	 * @var array
	 */
	protected $module_extras = array(
		'acp'	=> array(
			'ACP_QUICK_ACCESS' => array(
				'ACP_MANAGE_USERS',
				'ACP_GROUPS_MANAGE',
				'ACP_MANAGE_FORUMS',
				'ACP_MOD_LOGS',
				'ACP_BOTS',
				'ACP_PHP_INFO',
			),
			'ACP_FORUM_BASED_PERMISSIONS' => array(
				'ACP_FORUM_PERMISSIONS',
				'ACP_FORUM_PERMISSIONS_COPY',
				'ACP_FORUM_MODERATORS',
				'ACP_USERS_FORUM_PERMISSIONS',
				'ACP_GROUPS_FORUM_PERMISSIONS',
			),
		),
	);

	/**
	 * Constructor
	 *
	 * @parma config				$config		Installer's config
	 * @param iohandler_interface	$iohandler	Installer's input-output handler
	 * @param container_factory		$container	Installer's DI container
	 */
	public function __construct(config $config, iohandler_interface $iohandler, container_factory $container)
	{
		$this->config				= $config;
		$this->db					= $container->get('dbal.conn');
		$this->extension_manager	= $container->get('ext.manager');
		$this->iohandler			= $iohandler;
		$this->module_manager		= $container->get('module.manager');

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$this->db->sql_return_on_error(true);

		$module_classes = array('acp', 'mcp', 'ucp');
		$total = count($module_classes);
		$i = $this->config->get('module_class_index', 0);
		$module_classes = array_slice($module_classes, $i);

		foreach ($module_classes as $module_class)
		{
			$categories = $this->config->get('module_categories_array', array());

			$k = $this->config->get('module_categories_index', 0);
			$module_categories = array_slice($this->module_categories[$module_class], $k);
			$timed_out = false;

			foreach ($module_categories as $cat_name => $subs)
			{
				// Check if this sub-category has a basename. If it has, use it.
				$basename = (isset($this->module_categories_basenames[$cat_name])) ? $this->module_categories_basenames[$cat_name] : '';

				$module_data = array(
					'module_basename'	=> $basename,
					'module_enabled'	=> 1,
					'module_display'	=> 1,
					'parent_id'			=> 0,
					'module_class'		=> $module_class,
					'module_langname'	=> $cat_name,
					'module_mode'		=> '',
					'module_auth'		=> '',
				);

				$this->module_manager->update_module_data($module_data);

				// Check for last sql error happened
				if ($this->db->get_sql_error_triggered())
				{
					$error = $this->db->sql_error($this->db->get_sql_error_sql());
					$this->iohandler->add_error_message('INST_ERR_DB', $error['message']);
				}

				$categories[$cat_name]['id'] = (int) $module_data['module_id'];
				$categories[$cat_name]['parent_id'] = 0;

				if (is_array($subs))
				{
					foreach ($subs as $level2_name)
					{
						// Check if this sub-category has a basename. If it has, use it.
						$basename = (isset($this->module_categories_basenames[$level2_name])) ? $this->module_categories_basenames[$level2_name] : '';

						$module_data = array(
							'module_basename'	=> $basename,
							'module_enabled'	=> 1,
							'module_display'	=> 1,
							'parent_id'			=> (int) $categories[$cat_name]['id'],
							'module_class'		=> $module_class,
							'module_langname'	=> $level2_name,
							'module_mode'		=> '',
							'module_auth'		=> '',
						);

						$this->module_manager->update_module_data($module_data);

						// Check for last sql error happened
						if ($this->db->get_sql_error_triggered())
						{
							$error = $this->db->sql_error($this->db->get_sql_error_sql());
							$this->iohandler->add_error_message('INST_ERR_DB', $error['message']);
						}

						$categories[$level2_name]['id'] = (int) $module_data['module_id'];
						$categories[$level2_name]['parent_id'] = (int) $categories[$cat_name]['id'];
					}
				}

				$k++;

				// Stop execution if resource limit is reached
				if ($this->config->get_time_remaining() <= 0 || $this->config->get_memory_remaining() <= 0)
				{
					$timed_out = true;
					break;
				}
			}

			$this->config->set('module_categories_array', $categories);
			$this->config->set('module_categories_index', $k);

			if ($timed_out)
			{
				throw new resource_limit_reached_exception();
			}

			// Get the modules we want to add... returned sorted by name
			$module_info = $this->module_manager->get_module_infos($module_class);

			$k = $this->config->get('module_info_index', 0);
			$module_info = array_slice($module_info, $k);

			foreach ($module_info as $module_basename => $fileinfo)
			{
				foreach ($fileinfo['modes'] as $module_mode => $row)
				{
					foreach ($row['cat'] as $cat_name)
					{
						if (!isset($categories[$cat_name]))
						{
							continue;
						}

						$module_data = array(
							'module_basename'	=> $module_basename,
							'module_enabled'	=> 1,
							'module_display'	=> (isset($row['display'])) ? (int) $row['display'] : 1,
							'parent_id'			=> (int) $categories[$cat_name]['id'],
							'module_class'		=> $module_class,
							'module_langname'	=> $row['title'],
							'module_mode'		=> $module_mode,
							'module_auth'		=> $row['auth'],
						);

						$this->module_manager->update_module_data($module_data);

						// Check for last sql error happened
						if ($this->db->get_sql_error_triggered())
						{
							$error = $this->db->sql_error($this->db->get_sql_error_sql());
							$this->iohandler->add_error_message('INST_ERR_DB', $error['message']);
						}
					}
				}

				$k++;

				// Stop execution if resource limit is reached
				if ($this->config->get_time_remaining() <= 0 || $this->config->get_memory_remaining() <= 0)
				{
					$timed_out = true;
					break;
				}
			}

			$this->config->set('module_info_index', $k);

			// Stop execution if resource limit is reached
			if ($timed_out)
			{
				throw new resource_limit_reached_exception();
			}

			// Move some of the modules around since the code above will put them in the wrong place
			if (!$this->config->get('modules_ordered', false))
			{
				$this->order_modules($module_class);
				$this->config->set('modules_ordered', true);

				// Stop execution if resource limit is reached
				if ($this->config->get_time_remaining() <= 0 || $this->config->get_memory_remaining() <= 0)
				{
					throw new resource_limit_reached_exception();
				}
			}

			// And now for the special ones
			// (these are modules which appear in multiple categories and thus get added manually
			// to some for more control)
			if (isset($this->module_extras[$module_class]))
			{
				$this->add_module_extras($module_class);
			}

			$this->module_manager->remove_cache_file($module_class);

			$i++;

			$this->config->set('module_class_index', $i);
			$this->config->set('module_categories_index', 0);
			$this->config->set('module_info_index', 0);
			$this->config->set('added_extra_modules', false);
			$this->config->set('modules_ordered', false);
			$this->config->set('module_categories_array', array());

			// Stop execution if resource limit is reached
			if ($this->config->get_time_remaining() <= 0 || $this->config->get_memory_remaining() <= 0)
			{
				break;
			}
		}

		if ($i < $total)
		{
			throw new resource_limit_reached_exception();
		}
	}

	/**
	 * Move modules to their correct place
	 *
	 * @param string	$module_class
	 */
	protected function order_modules($module_class)
	{
		if ($module_class == 'acp')
		{
			// Move main module 4 up...
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = 'acp_main'
					AND module_class = 'acp'
					AND module_mode = 'main'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$this->module_manager->move_module_by($row, 'acp', 'move_up', 4);

			// Move permissions intro screen module 4 up...
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = 'acp_permissions'
					AND module_class = 'acp'
					AND module_mode = 'intro'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$this->module_manager->move_module_by($row, 'acp', 'move_up', 4);

			// Move manage users screen module 5 up...
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = 'acp_users'
					AND module_class = 'acp'
					AND module_mode = 'overview'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$this->module_manager->move_module_by($row, 'acp', 'move_up', 5);

			// Move extension management module 1 up...
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_langname = 'ACP_EXTENSION_MANAGEMENT'
					AND module_class = 'acp'
					AND module_mode = ''
					AND module_basename = ''";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$this->module_manager->move_module_by($row, 'acp', 'move_up', 1);
		}

		if ($module_class == 'mcp')
		{
			// Move pm report details module 3 down...
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = 'mcp_pm_reports'
					AND module_class = 'mcp'
					AND module_mode = 'pm_report_details'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$this->module_manager->move_module_by($row, 'mcp', 'move_down', 3);

			// Move closed pm reports module 3 down...
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = 'mcp_pm_reports'
					AND module_class = 'mcp'
					AND module_mode = 'pm_reports_closed'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$this->module_manager->move_module_by($row, 'mcp', 'move_down', 3);

			// Move open pm reports module 3 down...
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = 'mcp_pm_reports'
					AND module_class = 'mcp'
					AND module_mode = 'pm_reports'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$this->module_manager->move_module_by($row, 'mcp', 'move_down', 3);
		}

		if ($module_class == 'ucp')
		{
			// Move attachment module 4 down...
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = 'ucp_attachments'
					AND module_class = 'ucp'
					AND module_mode = 'attachments'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$this->module_manager->move_module_by($row, 'ucp', 'move_down', 4);

			// Move notification options module 4 down...
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = 'ucp_notifications'
					AND module_class = 'ucp'
					AND module_mode = 'notification_options'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$this->module_manager->move_module_by($row, 'ucp', 'move_down', 4);

			// Move OAuth module 5 down...
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = 'ucp_auth_link'
					AND module_class = 'ucp'
					AND module_mode = 'auth_link'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$this->module_manager->move_module_by($row, 'ucp', 'move_down', 5);
		}
	}

	/**
	 * Add extra modules
	 *
	 * @param string	$module_class
	 */
	protected function add_module_extras($module_class)
	{
		foreach ($this->module_extras[$module_class] as $cat_name => $mods)
		{
			$sql = 'SELECT module_id, left_id, right_id
				FROM ' . MODULES_TABLE . "
				WHERE module_langname = '" . $this->db->sql_escape($cat_name) . "'
					AND module_class = '" . $this->db->sql_escape($module_class) . "'";
			$result = $this->db->sql_query_limit($sql, 1);
			$row2 = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			foreach ($mods as $mod_name)
			{
				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_langname = '" . $this->db->sql_escape($mod_name) . "'
						AND module_class = '" . $this->db->sql_escape($module_class) . "'
						AND module_basename <> ''";
				$result = $this->db->sql_query_limit($sql, 1);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$module_data = array(
					'module_basename'	=> $row['module_basename'],
					'module_enabled'	=> (int) $row['module_enabled'],
					'module_display'	=> (int) $row['module_display'],
					'parent_id'			=> (int) $row2['module_id'],
					'module_class'		=> $row['module_class'],
					'module_langname'	=> $row['module_langname'],
					'module_mode'		=> $row['module_mode'],
					'module_auth'		=> $row['module_auth'],
				);

				$this->module_manager->update_module_data($module_data);

				// Check for last sql error happened
				if ($this->db->get_sql_error_triggered())
				{
					$error = $this->db->sql_error($this->db->get_sql_error_sql());
					$this->iohandler->add_error_message('INST_ERR_DB', $error['message']);
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	static public function get_step_count()
	{
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name()
	{
		return 'TASK_ADD_MODULES';
	}
}
