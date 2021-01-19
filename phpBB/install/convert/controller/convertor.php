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

namespace phpbb\convert\controller;

use phpbb\cache\driver\driver_interface;
use phpbb\exception\http_exception;
use phpbb\install\controller\helper;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\database;
use phpbb\install\helper\install_helper;
use phpbb\install\helper\iohandler\factory;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\helper\navigation\navigation_provider;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller for forum convertors
 *
 * WARNING: This file did not meant to be present in a production environment, so moving
 * 			this file to a location which is accessible after board installation might
 * 			lead to security issues.
 */
class convertor
{
	/**
	 * @var driver_interface
	 */
	protected $cache;

	/**
	 * @var driver_interface
	 */
	protected $installer_cache;

	/**
	 * @var \phpbb\config\db
	 */
	protected $config;

	/**
	 * @var \phpbb\config_php_file
	 */
	protected $config_php_file;

	/**
	 * @var string
	 */
	protected $config_table;

	/**
	 * @var helper
	 */
	protected $controller_helper;

	/**
	 * @var database
	 */
	protected $db_helper;

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var install_helper
	 */
	protected $install_helper;

	/**
	 * @var iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var navigation_provider
	 */
	protected $navigation_provider;

	/**
	 * @var request_interface
	 */
	protected $request;

	/**
	 * @var string
	 */
	protected $session_keys_table;

	/**
	 * @var string
	 */
	protected $session_table;

	/**
	 * @var template
	 */
	protected $template;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param driver_interface		$cache
	 * @param container_factory		$container
	 * @param database				$db_helper
	 * @param helper				$controller_helper
	 * @param install_helper		$install_helper
	 * @param factory				$iohandler
	 * @param language				$language
	 * @param navigation_provider	$nav
	 * @param request_interface		$request
	 * @param template				$template
	 * @param string				$phpbb_root_path
	 * @param string				$php_ext
	 */
	public function __construct(driver_interface $cache, container_factory $container, database $db_helper, helper $controller_helper, install_helper $install_helper, factory $iohandler, language $language, navigation_provider $nav, request_interface $request, template $template, $phpbb_root_path, $php_ext)
	{
		$this->installer_cache		= $cache;
		$this->controller_helper	= $controller_helper;
		$this->db_helper			= $db_helper;
		$this->install_helper		= $install_helper;
		$this->language				= $language;
		$this->navigation_provider	= $nav;
		$this->request				= $request;
		$this->template				= $template;
		$this->phpbb_root_path		= $phpbb_root_path;
		$this->php_ext				= $php_ext;

		$iohandler->set_environment('ajax');
		$this->iohandler = $iohandler->get();

		if (!$this->install_helper->is_phpbb_installed() || !defined('IN_INSTALL'))
		{
			throw new http_exception(403, 'INSTALL_PHPBB_NOT_INSTALLED');
		}

		$this->controller_helper->handle_language_select();

		$this->cache	= $container->get('cache.driver');
		$this->config	= $container->get('config');
		$this->config_php_file	= new \phpbb\config_php_file($this->phpbb_root_path, $this->php_ext);
		$this->db		= $container->get('dbal.conn.driver');

		$this->config_table			= $container->get_parameter('tables.config');
		$this->session_keys_table	= $container->get_parameter('tables.sessions_keys');
		$this->session_table		= $container->get_parameter('tables.sessions');
	}

	/**
	 * Render the intro page
	 *
	 * @param bool|int	$start_new	Whether or not to force to start a new convertor
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function intro($start_new)
	{
		$this->setup_navigation('intro');

		if ($start_new)
		{
			if ($this->request->is_ajax())
			{
				$response = new StreamedResponse();
				$iohandler = $this->iohandler;
				$url = $this->controller_helper->route('phpbb_convert_intro', array('start_new' => 'new'));
				$response->setCallback(function() use ($iohandler, $url) {
					$iohandler->redirect($url);
				});
				$response->headers->set('X-Accel-Buffering', 'no');

				return $response;
			}

			$this->config['convert_progress'] = '';
			$this->config['convert_db_server'] = '';
			$this->config['convert_db_user'] = '';
			$this->db->sql_query('DELETE FROM ' . $this->config_table . "
				WHERE config_name = 'convert_progress'
					OR config_name = 'convert_db_server'
					OR config_name = 'convert_db_user'"
			);
		}

		// Let's see if there is a conversion in the works...
		$options = array();
		if (!empty($this->config['convert_progress']) &&
			!empty($this->config['convert_db_server']) &&
			!empty($this->config['convert_db_user']) &&
			!empty($this->config['convert_options']))
		{
			$options = unserialize($this->config['convert_progress']);
			$options = array_merge($options,
				unserialize($this->config['convert_db_server']),
				unserialize($this->config['convert_db_user']),
				unserialize($this->config['convert_options'])
			);
		}

		// This information should have already been checked once, but do it again for safety
		if (!empty($options) && !empty($options['tag']) &&
			isset($options['dbms']) &&
			isset($options['dbhost']) &&
			isset($options['dbport']) &&
			isset($options['dbuser']) &&
			isset($options['dbpasswd']) &&
			isset($options['dbname']) &&
			isset($options['table_prefix']))
		{
			$this->template->assign_vars(array(
				'TITLE'				=> $this->language->lang('CONTINUE_CONVERT'),
				'BODY'				=> $this->language->lang('CONTINUE_CONVERT_BODY'),
				'S_CONTINUE'		=> true,
				'U_NEW_ACTION'		=> $this->controller_helper->route('phpbb_convert_intro', array('start_new' => 'new')),
				'U_CONTINUE_ACTION'	=> $this->controller_helper->route('phpbb_convert_convert', array('converter' => $options['tag'])),
			));

			return $this->controller_helper->render('installer_convert.html', 'CONTINUE_CONVERT', true);
		}

		return $this->render_convert_list();
	}

	/**
	 * Obtain convertor settings
	 *
	 * @param string	$converter	Name of the convertor
	 *
	 * @return \Symfony\Component\HttpFoundation\Response|StreamedResponse
	 */
	public function settings($converter)
	{
		$this->setup_navigation('settings');

		require_once ($this->phpbb_root_path . 'includes/constants.' . $this->php_ext);
		require_once ($this->phpbb_root_path . 'includes/functions_convert.' . $this->php_ext);

		// Include convertor if available
		$convertor_file_path = $this->phpbb_root_path . 'install/convertors/convert_' . $converter . '.' . $this->php_ext;
		if (!file_exists($convertor_file_path))
		{
			if ($this->request->is_ajax())
			{
				$response = new StreamedResponse();
				$ref = $this;
				$response->setCallback(function() use ($ref) {
					$ref->render_error('CONVERT_NOT_EXIST');
				});
				$response->headers->set('X-Accel-Buffering', 'no');

				return $response;
			}

			$this->render_error('CONVERT_NOT_EXIST');
			return $this->controller_helper->render('installer_convert.html', 'STAGE_SETTINGS', true);
		}

		$get_info = true;
		$phpbb_root_path = $this->phpbb_root_path; // These globals are required
		$phpEx = $this->php_ext; // See above
		include_once ($convertor_file_path);

		// The test_file is a file that should be present in the location of the old board.
		if (!isset($test_file))
		{
			if ($this->request->is_ajax())
			{
				$response = new StreamedResponse();
				$ref = $this;
				$response->setCallback(function() use ($ref) {
					$ref->render_error('DEV_NO_TEST_FILE');
				});
				$response->headers->set('X-Accel-Buffering', 'no');

				return $response;
			}

			$this->render_error('DEV_NO_TEST_FILE');
			return $this->controller_helper->render('installer_convert.html', 'STAGE_SETTINGS', true);
		}

		if ($this->request->variable('submit', false))
		{
			// It must be an AJAX request at this point
			$response = new StreamedResponse();
			$ref = $this;
			$response->setCallback(function() use ($ref, $converter) {
				$ref->proccess_settings_form($converter);
			});
			$response->headers->set('X-Accel-Buffering', 'no');

			return $response;
		}
		else
		{
			$this->template->assign_vars(array(
				'U_ACTION'	=> $this->controller_helper->route('phpbb_convert_settings', array(
					'converter'	=> $converter,
				))
			));

			if ($this->request->is_ajax())
			{
				$response = new StreamedResponse();
				$ref = $this;
				$response->setCallback(function() use ($ref) {
					$ref->render_settings_form();
				});
				$response->headers->set('X-Accel-Buffering', 'no');

				return $response;
			}

			$this->render_settings_form();
		}

		return $this->controller_helper->render('installer_convert.html', 'STAGE_SETTINGS', true);
	}

	/**
	 * Run conversion
	 */
	public function convert($converter)
	{
		$this->setup_navigation('convert');

		if ($this->request->is_ajax())
		{
			$route = $this->controller_helper->route('phpbb_convert_convert', array('converter' => $converter));
			$response = new StreamedResponse();
			$ref = $this;
			$response->setCallback(function() use ($ref, $route) {
				$ref->redirect_to_html($route);
			});
			$response->headers->set('X-Accel-Buffering', 'no');

			return $response;
		}

		$convertor = new \phpbb\convert\convertor($this->template, $this->controller_helper);
		$convertor->convert_data($converter);

		return $this->controller_helper->render('installer_convert.html', 'STAGE_IN_PROGRESS');
	}

	/**
	 * Render the final page of the convertor
	 */
	public function finish()
	{
		$this->setup_navigation('finish');

		$this->template->assign_vars(array(
			'TITLE'		=> $this->language->lang('CONVERT_COMPLETE'),
			'BODY'		=> $this->language->lang('CONVERT_COMPLETE_EXPLAIN'),
		));

		// If we reached this step (conversion completed) we want to purge the cache and log the user out.
		// This is for making sure the session get not screwed due to the 3.0.x users table being completely new.
		$this->cache->purge();
		$this->installer_cache->purge();

		require_once($this->phpbb_root_path . 'includes/constants.' . $this->php_ext);
		require_once($this->phpbb_root_path . 'includes/functions_convert.' . $this->php_ext);

		$sql = 'SELECT config_value
			FROM ' . $this->config_table . '
			WHERE config_name = \'search_type\'';
		$result = $this->db->sql_query($sql);

		if ($this->db->sql_fetchfield('config_value') != 'fulltext_mysql')
		{
			$this->template->assign_vars(array(
				'S_ERROR_BOX'	=> true,
				'ERROR_TITLE'	=> $this->language->lang('SEARCH_INDEX_UNCONVERTED'),
				'ERROR_MSG'		=> $this->language->lang('SEARCH_INDEX_UNCONVERTED_EXPLAIN'),
			));
		}

		$this->db->sql_freeresult($result);

		switch ($this->db->get_sql_layer())
		{
			case 'sqlite3':
				$this->db->sql_query('DELETE FROM ' . $this->session_keys_table);
				$this->db->sql_query('DELETE FROM ' . $this->session_table);
			break;

			default:
				$this->db->sql_query('TRUNCATE TABLE ' . $this->session_keys_table);
				$this->db->sql_query('TRUNCATE TABLE ' . $this->session_table);
			break;
		}

		return $this->controller_helper->render('installer_convert.html', 'CONVERT_COMPLETE');
	}

	/**
	 * Validates settings form
	 *
	 * @param string	$convertor
	 */
	public function proccess_settings_form($convertor)
	{
		global $phpbb_root_path, $phpEx, $get_info;

		$phpbb_root_path = $this->phpbb_root_path;
		$phpEx = $this->php_ext;
		$get_info = true;

		require_once($this->phpbb_root_path . 'includes/constants.' . $this->php_ext);
		require_once($this->phpbb_root_path . 'includes/functions_convert.' . $this->php_ext);

		// Include convertor if available
		$convertor_file_path = $this->phpbb_root_path . 'install/convertors/convert_' . $convertor . '.' . $this->php_ext;
		include ($convertor_file_path);

		// We expect to have an AJAX request here
		$src_dbms			= $this->request->variable('src_dbms', $convertor_data['dbms']);
		$src_dbhost			= $this->request->variable('src_dbhost', $convertor_data['dbhost']);
		$src_dbport			= $this->request->variable('src_dbport', $convertor_data['dbport']);
		$src_dbuser			= $this->request->variable('src_dbuser', $convertor_data['dbuser']);
		$src_dbpasswd		= $this->request->variable('src_dbpasswd', $convertor_data['dbpasswd']);
		$src_dbname			= $this->request->variable('src_dbname', $convertor_data['dbname']);
		$src_table_prefix	= $this->request->variable('src_table_prefix', $convertor_data['table_prefix']);
		$forum_path			= $this->request->variable('forum_path', $convertor_data['forum_path']);
		$refresh			= $this->request->variable('refresh', 1);

		// Default URL of the old board
		// @todo Are we going to use this for attempting to convert URL references in posts, or should we remove it?
		//		-> We should convert old urls to the new relative urls format
		// $src_url = $request->variable('src_url', 'Not in use at the moment');

		// strip trailing slash from old forum path
		$forum_path = (strlen($forum_path) && $forum_path[strlen($forum_path) - 1] == '/') ? substr($forum_path, 0, -1) : $forum_path;

		$error = array();
		if (!file_exists($this->phpbb_root_path . $forum_path . '/' . $test_file))
		{
			$error[] = $this->language->lang('COULD_NOT_FIND_PATH', $forum_path);
		}

		$connect_test = false;
		$available_dbms = $this->db_helper->get_available_dbms(false, true, true);
		if (!isset($available_dbms[$src_dbms]) || !$available_dbms[$src_dbms]['AVAILABLE'])
		{
			$error[] = $this->language->lang('INST_ERR_NO_DB');
		}
		else
		{
			$connect_test = $this->db_helper->check_database_connection($src_dbms, $src_dbhost, $src_dbport, $src_dbuser, $src_dbpasswd, $src_dbname, $src_table_prefix);
		}

		extract($this->config_php_file->get_all());

		// The forum prefix of the old and the new forum can only be the same if two different databases are used.
		if ($src_table_prefix === $table_prefix && $src_dbms === $dbms && $src_dbhost === $dbhost && $src_dbport === $dbport && $src_dbname === $dbname)
		{
			$error[] = $this->language->lang('TABLE_PREFIX_SAME', $src_table_prefix);
		}

		if (!$connect_test)
		{
			$error[] = $this->language->lang('INST_ERR_DB_CONNECT');
		}

		$src_dbms = $this->config_php_file->convert_30_dbms_to_31($src_dbms);

		// Check table prefix
		if (empty($error))
		{
			// initiate database connection to old db if old and new db differ
			global $src_db, $same_db;
			$src_db = $same_db = false;

			if ($src_dbms != $dbms || $src_dbhost != $dbhost || $src_dbport != $dbport || $src_dbname != $dbname || $src_dbuser != $dbuser)
			{
				/** @var \phpbb\db\driver\driver_interface $src_db */
				$src_db = new $src_dbms();
				$src_db->sql_connect($src_dbhost, $src_dbuser, htmlspecialchars_decode($src_dbpasswd, ENT_COMPAT), $src_dbname, $src_dbport, false, true);
				$same_db = false;
			}
			else
			{
				$src_db = $this->db;
				$same_db = true;
			}

			$src_db->sql_return_on_error(true);
			$this->db->sql_return_on_error(true);

			// Try to select one row from the first table to see if the prefix is OK
			$result = $src_db->sql_query_limit('SELECT * FROM ' . $src_table_prefix . $tables[0], 1);

			if (!$result)
			{
				$prefixes = array();

				$db_tools_factory = new \phpbb\db\tools\factory();
				$db_tools = $db_tools_factory->get($src_db);
				$tables_existing = $db_tools->sql_list_tables();
				$tables_existing = array_map('strtolower', $tables_existing);
				foreach ($tables_existing as $table_name)
				{
					compare_table($tables, $table_name, $prefixes);
				}
				unset($tables_existing);

				foreach ($prefixes as $prefix => $count)
				{
					if ($count >= count($tables))
					{
						$possible_prefix = $prefix;
						break;
					}
				}

				$msg = '';
				if (!empty($convertor_data['table_prefix']))
				{
					$msg .= $this->language->lang_array('DEFAULT_PREFIX_IS', array($convertor_data['forum_name'], $convertor_data['table_prefix']));
				}

				if (!empty($possible_prefix))
				{
					$msg .= '<br />';
					$msg .= ($possible_prefix == '*') ? $this->language->lang('BLANK_PREFIX_FOUND') : $this->language->lang_array('PREFIX_FOUND', array($possible_prefix));
					$src_table_prefix = ($possible_prefix == '*') ? '' : $possible_prefix;
				}

				$error[] = $msg;
			}

			$src_db->sql_freeresult($result);
			$src_db->sql_return_on_error(false);
		}

		if (empty($error))
		{
			// Save convertor Status
			$this->config->set('convert_progress', serialize(array(
				'step'			=> '',
				'table_prefix'	=> $src_table_prefix,
				'tag'			=> $convertor,
			)), false);
			$this->config->set('convert_db_server', serialize(array(
				'dbms'			=> $src_dbms,
				'dbhost'		=> $src_dbhost,
				'dbport'		=> $src_dbport,
				'dbname'		=> $src_dbname,
			)), false);
			$this->config->set('convert_db_user', serialize(array(
				'dbuser'		=> $src_dbuser,
				'dbpasswd'		=> $src_dbpasswd,
			)), false);

			// Save options
			$this->config->set('convert_options', serialize(array(
				'forum_path' => $this->phpbb_root_path . $forum_path,
				'refresh' => $refresh
			)), false);

			$url = $this->controller_helper->route('phpbb_convert_convert', array('converter' => $convertor));
			$this->iohandler->redirect($url);
			$this->iohandler->send_response(true);
		}
		else
		{
			$this->render_settings_form($error);
		}
	}

	/**
	 * Renders settings form
	 *
	 * @param array	$error	Array of errors
	 */
	public function render_settings_form($error = array())
	{
		foreach ($error as $msg)
		{
			$this->iohandler->add_error_message($msg);
		}

		$dbms_options = array();
		foreach ($this->db_helper->get_available_dbms() as $dbms_key => $dbms_array)
		{
			$dbms_options[] = array(
				'value'		=> $dbms_key,
				'label'		=> 'DB_OPTION_' . strtoupper($dbms_key),
			);
		}

		$form_title = 'SPECIFY_OPTIONS';
		$form_data = array(
			'src_dbms'	=> array(
				'label'		=> 'DBMS',
				'type'		=> 'select',
				'options'	=> $dbms_options,
			),
			'src_dbhost' => array(
				'label'			=> 'DB_HOST',
				'description'	=> 'DB_HOST_EXPLAIN',
				'type'			=> 'text',
			),
			'src_dbport' => array(
				'label'			=> 'DB_PORT',
				'description'	=> 'DB_PORT_EXPLAIN',
				'type'			=> 'text',
			),
			'src_dbname' => array(
				'label'		=> 'DB_NAME',
				'type'		=> 'text',
			),
			'src_dbuser' => array(
				'label'		=> 'DB_USERNAME',
				'type'		=> 'text',
			),
			'src_dbpasswd' => array(
				'label'	=> 'DB_PASSWORD',
				'type'	=> 'password',
			),
			'src_table_prefix' => array(
				'label'			=> 'TABLE_PREFIX',
				'description'	=> 'TABLE_PREFIX_EXPLAIN',
				'type'			=> 'text',
			),
			'forum_path' => array(
				'label'			=> 'FORUM_PATH',
				'description'	=> 'FORUM_PATH_EXPLAIN',
				'type'			=> 'text',
			),
			'refresh' => array(
				'label'			=> 'REFRESH_PAGE',
				'description'	=> 'REFRESH_PAGE_EXPLAIN',
				'type'			=> 'radio',
				'options'		=> array(
					array(
						'value'		=> 0,
						'label'		=> 'NO',
						'selected'	=> true,
					),
					array(
						'value'		=> 1,
						'label'		=> 'YES',
						'selected'	=> false,
					),
				),
			),
			'submit' => array(
				'label'	=> 'SUBMIT',
				'type'	=> 'submit',
			),
		);

		if ($this->request->is_ajax())
		{
			$this->iohandler->add_user_form_group($form_title, $form_data);
			$this->iohandler->send_response(true);
		}
		else
		{
			$rendered_form = $this->iohandler->generate_form_render_data($form_title, $form_data);

			$this->template->assign_vars(array(
				'TITLE'		=> $this->language->lang('STAGE_SETTINGS'),
				'CONTENT'	=> $rendered_form,
			));
		}
	}

	/**
	 * Render the list of available convertors
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function render_convert_list()
	{
		$this->template->assign_vars(array(
			'TITLE'		=> $this->language->lang('CONVERT_INTRO'),
			'BODY'		=> $this->language->lang('CONVERT_INTRO_BODY'),
			'S_LIST'	=> true,
		));

		$convertors = $sort = array();
		$get_info = true; // Global flag

		$handle = @opendir($this->phpbb_root_path . 'install/convertors/');

		if (!$handle)
		{
			die('Unable to access the convertors directory');
		}

		while ($entry = readdir($handle))
		{
			if (preg_match('/^convert_([a-z0-9_]+).' . $this->php_ext . '$/i', $entry, $m))
			{
				$phpbb_root_path = $this->phpbb_root_path; // These globals are required
				$phpEx = $this->php_ext; // See above
				include_once($this->phpbb_root_path . 'install/convertors/' . $entry);
				if (isset($convertor_data))
				{
					$sort[strtolower($convertor_data['forum_name'])] = count($convertors);

					$convertors[] = array(
						'tag'			=>	$m[1],
						'forum_name'	=>	$convertor_data['forum_name'],
						'version'		=>	$convertor_data['version'],
						'dbms'			=>	$convertor_data['dbms'],
						'dbhost'		=>	$convertor_data['dbhost'],
						'dbport'		=>	$convertor_data['dbport'],
						'dbuser'		=>	$convertor_data['dbuser'],
						'dbpasswd'		=>	$convertor_data['dbpasswd'],
						'dbname'		=>	$convertor_data['dbname'],
						'table_prefix'	=>	$convertor_data['table_prefix'],
						'author'		=>	$convertor_data['author']
					);
				}
				unset($convertor_data);
			}
		}
		closedir($handle);

		@ksort($sort);

		foreach ($sort as $void => $index)
		{
			$this->template->assign_block_vars('convertors', array(
				'AUTHOR'	=> $convertors[$index]['author'],
				'SOFTWARE'	=> $convertors[$index]['forum_name'],
				'VERSION'	=> $convertors[$index]['version'],

				'U_CONVERT'	=> $this->controller_helper->route('phpbb_convert_settings', array('converter' => $convertors[$index]['tag'])),
			));
		}

		return $this->controller_helper->render('installer_convert.html', 'SUB_INTRO', true);
	}

	/**
	 * Renders an error form
	 *
	 * @param string		$msg
	 * @param string|bool	$desc
	 */
	public function render_error($msg, $desc = false)
	{
		if ($this->request->is_ajax())
		{
			$this->iohandler->add_error_message($msg, $desc);
			$this->iohandler->send_response(true);
		}
		else
		{
			$this->template->assign_vars(array(
				'S_ERROR_BOX'	=> true,
				'ERROR_TITLE'	=> $this->language->lang($msg),
			));

			if ($desc)
			{
				$this->template->assign_var('ERROR_MSG', $this->language->lang($desc));
			}
		}
	}

	/**
	 * Redirects an AJAX request to a non-JS version
	 *
	 * @param string	$url	URL to redirect to
	 */
	public function redirect_to_html($url)
	{
		$this->iohandler->redirect($url);
		$this->iohandler->send_response(true);
	}

	private function setup_navigation($stage)
	{
		$active = true;
		$completed = false;

		switch ($stage)
		{
			case 'finish':
				$this->navigation_provider->set_nav_property(
					array('convert', 0, 'finish'),
					array(
						'selected'	=> $active,
						'completed'	=> $completed,
					)
				);

				$active = false;
				$completed = true;
			// no break;

			case 'convert':
				$this->navigation_provider->set_nav_property(
					array('convert', 0, 'convert'),
					array(
						'selected'	=> $active,
						'completed'	=> $completed,
					)
				);

				$active = false;
				$completed = true;
			// no break;

			case 'settings':
				$this->navigation_provider->set_nav_property(
					array('convert', 0, 'settings'),
					array(
						'selected'	=> $active,
						'completed'	=> $completed,
					)
				);

				$active = false;
				$completed = true;
			// no break;

			case 'intro':
				$this->navigation_provider->set_nav_property(
					array('convert', 0, 'intro'),
					array(
						'selected'	=> $active,
						'completed'	=> $completed,
					)
				);
			break;
		}
	}
}
