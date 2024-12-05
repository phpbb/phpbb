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

use phpbb\install\database_task;
use phpbb\install\exception\resource_limit_reached_exception;
use phpbb\install\helper\config;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\database;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\language\language;

/**
 * Update the admin's info as well as the welcome post.
 */
class update_user_and_post_data extends database_task
{
	/** @var config */
	private $install_config;

	private $iohandler;

	/** @var language */
	private $language;

	/** @var \phpbb\passwords\manager */
	private $password_manager;

	/** @var string */
	private $forums_table;

	/** @var string */
	private $moderator_cache_table;

	/** @var string */
	private $posts_table;

	/** @var string */
	private $topics_table;

	/** @var string */
	private $user_table;

	/**
	 * Constructor.
	 *
	 * @param config				$install_config
	 * @param container_factory		$container
	 * @param database				$db_helper
	 * @param iohandler_interface	$iohandler
	 * @param language				$language
	 */
	public function __construct(
		config $install_config,
		container_factory $container,
		database $db_helper,
		iohandler_interface $iohandler,
		language $language)
	{
		$this->install_config	= $install_config;
		$this->iohandler		= $iohandler;
		$this->language			= $language;
		$this->password_manager	= $container->get('passwords.manager');

		$this->forums_table				= $container->get_parameter('tables.forums');
		$this->moderator_cache_table	= $container->get_parameter('tables.moderator_cache');
		$this->posts_table				= $container->get_parameter('tables.posts');
		$this->topics_table				= $container->get_parameter('tables.topics');
		$this->user_table				= $container->get_parameter('tables.users');

		parent::__construct(
			self::get_doctrine_connection($db_helper, $install_config),
			$this->iohandler,
			true
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		// Force a refresh.
		$count = $this->install_config->get('correct_user_and_post_data_count');
		if ($count === false)
		{
			if ($this->install_config->get_time_remaining() < 5)
			{
				$this->install_config->set('correct_user_and_post_data_count', 1);
				throw new resource_limit_reached_exception();
			}
		}

		$user_ip = phpbb_ip_normalise($this->iohandler->get_server_variable('REMOTE_ADDR'));
		$user_ip = ($user_ip === false) ? '' : $user_ip;
		$current_time = $this->install_config->get('install_board_time', time());

		// Update user data
		$sql = 'UPDATE ' . $this->user_table
			. ' SET username = :username,'
			. '		user_password = :password,'
			. '		user_ip = :ip,'
			. '		user_lang = :lang,'
			. '		user_email = :email,'
			. '		user_dateformat = :dateformat,'
			. '		username_clean = :clean_username,'
			. '		user_timezone = :timezone'
			. ' WHERE username = \'Admin\'';

		$this->create_and_execute_prepared_stmt($sql, [
			'username'			=> $this->install_config->get('admin_name'),
			'password'			=> $this->password_manager->hash($this->install_config->get('admin_passwd')),
			'ip'				=> $user_ip,
			'lang'				=> $this->install_config->get('user_language', 'en'),
			'email'				=> $this->install_config->get('board_email'),
			'dateformat'		=> $this->language->lang('default_dateformat'),
			'clean_username'	=> utf8_clean_string($this->install_config->get('admin_name')),
			'timezone'			=> $this->install_config->get('admin_timezone'),
		]);
		$this->exec_sql('UPDATE ' . $this->user_table . ' SET user_regdate = ' . $current_time);

		// Update forums table
		$sql = 'UPDATE ' . $this->forums_table
			. ' SET forum_last_poster_name = :poster_name'
			. ' WHERE forum_last_poster_name = \'Admin\'';
		$this->create_and_execute_prepared_stmt($sql, [
			'poster_name' => $this->install_config->get('admin_name'),
		]);
		$this->exec_sql('UPDATE ' . $this->forums_table . ' SET forum_last_post_time = ' . $current_time);

		// Topics table
		$sql = 'UPDATE ' . $this->topics_table
			. '	SET topic_first_poster_name = :poster_name,'
			. '		topic_last_poster_name = :poster_name'
			. ' WHERE topic_first_poster_name = \'Admin\''
			. '	OR topic_last_poster_name = \'Admin\'';
		$this->create_and_execute_prepared_stmt($sql, [
			'poster_name' => $this->install_config->get('admin_name'),
		]);
		$this->exec_sql('UPDATE ' . $this->topics_table
			. ' SET topic_time = ' . $current_time . ', topic_last_post_time = ' . $current_time
		);

		// Posts table
		$sql = 'UPDATE ' . $this->posts_table
			. ' SET post_time = :post_time,'
			. '		poster_ip = :poster_ip';
		$this->create_and_execute_prepared_stmt($sql, [
			'post_time' => $current_time,
			'poster_ip' => $user_ip,
		]);

		// Moderator cache
		$sql = 'UPDATE ' . $this->moderator_cache_table
			. ' SET username = :username'
			. ' WHERE username =  \'Admin\'';
		$this->create_and_execute_prepared_stmt($sql, [
			'username' => $this->install_config->get('admin_name'),
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_step_count() : int
	{
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name() : string
	{
		return 'TASK_UPDATE_POSTS';
	}
}
