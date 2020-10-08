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

use phpbb\auth\auth;
use phpbb\db\driver\driver_interface;
use phpbb\event\dispatcher;
use phpbb\config\config;
use phpbb\install\helper\container_factory;
use phpbb\language\language;
use phpbb\search\fulltext_native;
use phpbb\user;

class create_search_index extends \phpbb\install\task_base
{
	/**
	 * @var auth
	 */
	protected $auth;

	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var driver_interface
	 */
	protected $db;

	/**
	 * @var dispatcher
	 */
	protected $phpbb_dispatcher;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var user
	 */
	protected $user;

	/**
	 * @var string phpBB root path
	 */
	protected $phpbb_root_path;

	/**
	 * @var string PHP file extension
	 */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param config				$config				phpBB config
	 * @param container_factory		$container			Installer's DI container
	 * @param string				$phpbb_root_path	phpBB root path
	 * @param string				$php_ext			PHP file extension
	 */
	public function __construct(config $config, container_factory $container,
								$phpbb_root_path, $php_ext)
	{
		$this->auth				= $container->get('auth');
		$this->config			= $config;
		$this->db				= $container->get('dbal.conn');
		$this->language			= $container->get('language');
		$this->phpbb_dispatcher = $container->get('dispatcher');
		$this->user 			= $container->get('user');
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		// Make sure fulltext native load update is set
		$this->config->set('fulltext_native_load_upd', 1);

		$error = false;
		$search = new fulltext_native(
			$error,
			$this->phpbb_root_path,
			$this->php_ext,
			$this->auth,
			$this->config,
			$this->db,
			$this->user,
			$this->phpbb_dispatcher
		);

		$sql = 'SELECT post_id, post_subject, post_text, poster_id, forum_id
			FROM ' . POSTS_TABLE;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$search->index('post', $row['post_id'], $row['post_text'], $row['post_subject'], $row['poster_id'], $row['forum_id']);
		}
		$this->db->sql_freeresult($result);
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
		return 'TASK_CREATE_SEARCH_INDEX';
	}
}
