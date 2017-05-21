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

namespace phpbb\install\module\install_database\task;

use phpbb\install\exception\resource_limit_reached_exception;

/**
 * Create database schema
 */
class add_config_settings extends \phpbb\install\task_base
{
	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\filesystem\filesystem_interface
	 */
	protected $filesystem;

	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $install_config;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/**
	 * @var \phpbb\passwords\manager
	 */
	protected $password_manager;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $config_table;

	/**
	 * @var string
	 */
	protected $user_table;

	/**
	 * @var string
	 */
	protected $topics_table;

	/**
	 * @var string
	 */
	protected $forums_table;

	/**
	 * @var string
	 */
	protected $posts_table;

	/**
	 * @var string
	 */
	protected $moderator_cache_table;

	/**
	 * Constructor
	 *
	 * @param \phpbb\filesystem\filesystem_interface				$filesystem			Filesystem service
	 * @param \phpbb\install\helper\config							$install_config		Installer's config helper
	 * @param \phpbb\install\helper\iohandler\iohandler_interface	$iohandler			Installer's input-output handler
	 * @param \phpbb\install\helper\container_factory				$container			Installer's DI container
	 * @param \phpbb\language\language								$language			Language service
	 * @param string												$phpbb_root_path	Path to phpBB's root
	 */
	public function __construct(\phpbb\filesystem\filesystem_interface $filesystem,
								\phpbb\install\helper\config $install_config,
								\phpbb\install\helper\iohandler\iohandler_interface $iohandler,
								\phpbb\install\helper\container_factory $container,
								\phpbb\language\language $language,
								$phpbb_root_path)
	{
		$this->db				= $container->get('dbal.conn');
		$this->filesystem		= $filesystem;
		$this->install_config	= $install_config;
		$this->iohandler		= $iohandler;
		$this->language			= $language;
		$this->password_manager	= $container->get('passwords.manager');
		$this->phpbb_root_path	= $phpbb_root_path;

		// Table names
		$this->config_table				= $container->get_parameter('tables.config');
		$this->forums_table				= $container->get_parameter('tables.forums');
		$this->topics_table				= $container->get_parameter('tables.topics');
		$this->user_table				= $container->get_parameter('tables.users');
		$this->moderator_cache_table	= $container->get_parameter('tables.moderator_cache');
		$this->posts_table				= $container->get_parameter('tables.posts');

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$this->db->sql_return_on_error(true);

		$server_name	= $this->install_config->get('server_name');
		$current_time 	= time();
		$user_ip		= phpbb_ip_normalise($this->iohandler->get_server_variable('REMOTE_ADDR'));
		$user_ip		= ($user_ip === false) ? '' : $user_ip;
		$referer		= $this->iohandler->get_server_variable('REFERER');

		// Calculate cookie domain
		$cookie_domain = $server_name;

		if (strpos($cookie_domain, 'www.') === 0)
		{
			$cookie_domain = substr($cookie_domain, 3);
		}

		// Set default config and post data, this applies to all DB's
		$sql_ary = array(
			'INSERT INTO ' . $this->config_table . " (config_name, config_value)
				VALUES ('board_startdate', '$current_time')",

			'INSERT INTO ' . $this->config_table . " (config_name, config_value)
				VALUES ('default_lang', '" . $this->db->sql_escape($this->install_config->get('default_lang')) . "')",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('img_imagick')) . "'
				WHERE config_name = 'img_imagick'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('server_name')) . "'
				WHERE config_name = 'server_name'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('server_port')) . "'
				WHERE config_name = 'server_port'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('board_email')) . "'
				WHERE config_name = 'board_email'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('board_email')) . "'
				WHERE config_name = 'board_contact'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($cookie_domain) . "'
				WHERE config_name = 'cookie_domain'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->language->lang('default_dateformat')) . "'
				WHERE config_name = 'default_dateformat'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('email_enable')) . "'
				WHERE config_name = 'email_enable'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('smtp_delivery')) . "'
				WHERE config_name = 'smtp_delivery'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('smtp_host')) . "'
				WHERE config_name = 'smtp_host'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('smtp_port')) . "'
				WHERE config_name = 'smtp_port'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('smtp_auth')) . "'
				WHERE config_name = 'smtp_auth_method'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('smtp_user')) . "'
				WHERE config_name = 'smtp_username'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('smtp_pass')) . "'
				WHERE config_name = 'smtp_password'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('cookie_secure')) . "'
				WHERE config_name = 'cookie_secure'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('force_server_vars')) . "'
				WHERE config_name = 'force_server_vars'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('script_path')) . "'
				WHERE config_name = 'script_path'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('server_protocol')) . "'
				WHERE config_name = 'server_protocol'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('admin_name')) . "'
				WHERE config_name = 'newest_username'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . md5(mt_rand()) . "'
				WHERE config_name = 'avatar_salt'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . md5(mt_rand()) . "'
				WHERE config_name = 'plupload_salt'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('board_name')) . "'
				WHERE config_name = 'sitename'",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->install_config->get('board_description')) . "'
				WHERE config_name = 'site_desc'",

			'UPDATE ' . $this->user_table . "
				SET username = '" . $this->db->sql_escape($this->install_config->get('admin_name')) . "',
					user_password='" . $this->password_manager->hash($this->install_config->get('admin_passwd')) . "',
					user_ip = '" . $this->db->sql_escape($user_ip) . "',
					user_lang = '" . $this->db->sql_escape($this->install_config->get('user_language', 'en')) . "',
					user_email='" . $this->db->sql_escape($this->install_config->get('board_email')) . "',
					user_dateformat='" . $this->db->sql_escape($this->language->lang('default_dateformat')) . "',
					user_email_hash = " . $this->db->sql_escape(phpbb_email_hash($this->install_config->get('board_email'))) . ",
					username_clean = '" . $this->db->sql_escape(utf8_clean_string($this->install_config->get('admin_name'))) . "'
				WHERE username = 'Admin'",

			'UPDATE ' . $this->moderator_cache_table . "
				SET username = '" . $this->db->sql_escape($this->install_config->get('admin_name')) . "'
				WHERE username = 'Admin'",

			'UPDATE ' . $this->forums_table . "
				SET forum_last_poster_name = '" . $this->db->sql_escape($this->install_config->get('admin_name')) . "'
				WHERE forum_last_poster_name = 'Admin'",

			'UPDATE ' . $this->topics_table . "
				SET topic_first_poster_name = '" . $this->db->sql_escape($this->install_config->get('admin_name')) . "',
				topic_last_poster_name = '" . $this->db->sql_escape($this->install_config->get('admin_name')) . "'
				WHERE topic_first_poster_name = 'Admin'
					OR topic_last_poster_name = 'Admin'",

			'UPDATE ' . $this->user_table . "
				SET user_regdate = $current_time",

			'UPDATE ' . $this->posts_table . "
				SET post_time = $current_time, poster_ip = '" . $this->db->sql_escape($user_ip) . "'",

			'UPDATE ' . $this->topics_table . "
				SET topic_time = $current_time, topic_last_post_time = $current_time",

			'UPDATE ' . $this->forums_table . "
				SET forum_last_post_time = $current_time",

			'UPDATE ' . $this->config_table . "
				SET config_value = '" . $this->db->sql_escape($this->db->sql_server_info(true)) . "'
				WHERE config_name = 'dbms_version'",
		);

		if (@extension_loaded('gd'))
		{
			$sql_ary[] = 'UPDATE ' . $this->config_table . "
				SET config_value = 'core.captcha.plugins.gd'
				WHERE config_name = 'captcha_plugin'";

			$sql_ary[] = 'UPDATE ' . $this->config_table . "
				SET config_value = '1'
				WHERE config_name = 'captcha_gd'";
		}

		$ref = substr($referer, strpos($referer, '://') + 3);
		if (!(stripos($ref, $server_name) === 0))
		{
			$sql_ary[] = 'UPDATE ' . $this->config_table . "
				SET config_value = '0'
				WHERE config_name = 'referer_validation'";
		}

		// We set a (semi-)unique cookie name to bypass login issues related to the cookie name.
		$cookie_name = 'phpbb3_';
		$rand_str = md5(mt_rand());
		$rand_str = str_replace('0', 'z', base_convert($rand_str, 16, 35));
		$rand_str = substr($rand_str, 0, 5);
		$cookie_name .= strtolower($rand_str);

		$sql_ary[] = 'UPDATE ' . $this->config_table . "
			SET config_value = '" . $this->db->sql_escape($cookie_name) . "'
			WHERE config_name = 'cookie_name'";

		// Disable avatars if upload directory is not writable
		if (!$this->filesystem->is_writable($this->phpbb_root_path . 'images/avatars/upload/'))
		{
			$sql_ary[] = 'UPDATE ' . $this->config_table . "
				SET config_value = '0'
				WHERE config_name = 'allow_avatar'";

			$sql_ary[] = 'UPDATE ' . $this->config_table . "
				SET config_value = '0'
				WHERE config_name = 'allow_avatar_upload'";
		}

		$i = $this->install_config->get('add_config_settings_index', 0);
		$total = sizeof($sql_ary);
		$sql_ary = array_slice($sql_ary, $i);

		foreach ($sql_ary as $sql)
		{
			if (!$this->db->sql_query($sql))
			{
				$error = $this->db->sql_error($this->db->get_sql_error_sql());
				$this->iohandler->add_error_message('INST_ERR_DB', $error['message']);
			}

			$i++;

			// Stop execution if resource limit is reached
			if ($this->install_config->get_time_remaining() <= 0 || $this->install_config->get_memory_remaining() <= 0)
			{
				break;
			}
		}

		if ($i < $total)
		{
			$this->install_config->set('add_config_settings_index', $i);
			throw new resource_limit_reached_exception();
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
		return 'TASK_ADD_CONFIG_SETTINGS';
	}
}
