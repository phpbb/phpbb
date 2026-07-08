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

use phpbb\db\driver\driver_interface;
use phpbb\install\exception\resource_limit_reached_exception;
use phpbb\install\helper\config;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\task_base;
use phpbb\language\language;

class add_ai_crawlers extends task_base
{
	/**
	 * A list of the AI crawlers we recognise by default
	 *
	 * @var array
	 */
	protected $ai_crawlers = [
		// 'BotName' => 'Agent partial name',
		// OpenAI
		'OAI-SearchBot [Bot]'			=> 'OAI-SearchBot/',
		'OAI-AdsBot [Bot]'				=> 'OAI-AdsBot/',
		'GPTBot [Bot]'					=> 'GPTBot/',
		'ChatGPT-User [Bot]'			=> 'ChatGPT-User/',

		// Anthropic
		'ClaudeBot [Bot]'				=> 'ClaudeBot/',
		'Claude-User [Bot]'				=> 'Claude-User/',
		'Claude-SearchBot [Bot]'		=> 'Claude-SearchBot/',
		'Claude-Web [Bot]'				=> 'Claude-Web/',

		// Google Gemini
		'Google-Extended [Bot]'			=> 'Google-Extended',
		'Google-CloudVertexBot [Bot]'	=> 'Google-CloudVertexBot/',

		// Meta
		'FacebookBot [Bot]'				=> 'FacebookBot/',
		'FacebookExternalHit [Bot]'		=> 'facebookexternalhit/',
		'Meta-ExternalAgent [Bot]'		=> 'meta-externalagent/',
		'Meta-ExternalAds [Bot]'		=> 'meta-externalads/',
		'Meta-ExternalFetcher [Bot]'	=> 'meta-externalfetcher/',
		'Meta-WebIndexer [Bot]'			=> 'meta-webindexer/',

		// Perplexity AI
		'PerplexityBot [Bot]'			=> 'PerplexityBot/',
		'Perplexity-User [Bot]'			=> 'Perplexity-User',

		// ByteDance / TikTok
		'Bytespider [Bot]'				=> 'Bytespider',
		'TikTokSpider [Bot]'			=> 'TikTokSpider',

		// Apple Intelligence
		'Applebot [Bot]'				=> 'Applebot/',
		'Applebot-Extended [Bot]'	=> 'Applebot-Extended',

		// Cohere
		'Cohere [Bot]'					=> 'cohere-ai',
		'Cohere Training Crawler [Bot]'	=> 'cohere-training-data-crawler',

		// Diffbot
		'Diffbot [Bot]'				=> 'Diffbot',

		// Common Crawl (used by many AI training pipelines)
		'CCBot [Bot]'				=> 'CCBot/',
	];

	/**
	 * @var driver_interface
	 */
	protected $db;

	/**
	 * @var config
	 */
	protected $install_config;

	/**
	 * @var iohandler_interface
	 */
	protected $io_handler;

	/**
	 * @var language
	 */
	protected $language;

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
	 * @param config							$install_config		Installer's config
	 * @param iohandler_interface	$iohandler			Input-output handler for the installer
	 * @param container_factory				$container			Installer's DI container
	 * @param language								$language			Language provider
	 * @param string												$phpbb_root_path	Relative path to phpBB root
	 * @param string												$php_ext			PHP extension
	 */
	public function __construct(config $install_config, iohandler_interface $iohandler, container_factory $container,
								language $language, string $phpbb_root_path, string $php_ext)
	{
		parent::__construct();

		$this->db				= $container->get('dbal.conn');
		$this->install_config	= $install_config;
		$this->io_handler		= $iohandler;
		$this->language			= $language;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$this->db->sql_return_on_error(true);

		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . "
			WHERE group_name = 'AI_CRAWLERS'";
		$result = $this->db->sql_query($sql);
		$group_id = (int) $this->db->sql_fetchfield('group_id');
		$this->db->sql_freeresult($result);

		if (!$group_id)
		{
			// If we reach this point then something has gone very wrong
			$this->io_handler->add_error_message('NO_GROUP');
		}

		$i = $this->install_config->get('add_ai_crawlers_index', 0);
		$crawlers_list = array_slice($this->ai_crawlers, $i);

		foreach ($crawlers_list as $crawler_name => $user_agent)
		{
			$user_row = array(
				'user_type'				=> USER_IGNORE,
				'group_id'				=> $group_id,
				'username'				=> $crawler_name,
				'user_regdate'			=> time(),
				'user_password'			=> '',
				'user_colour'			=> '7D9EAE',
				'user_email'			=> '',
				'user_lang'				=> $this->install_config->get('default_lang'),
				'user_style'			=> 1,
				'user_timezone'			=> 'UTC',
				'user_dateformat'		=> $this->language->lang('default_dateformat'),
				'user_allow_massemail'	=> 0,
				'user_allow_pm'			=> 0,
			);

			if (!function_exists('user_add'))
			{
				include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
			}

			$user_id = user_add($user_row);

			if (!$user_id)
			{
				// If we can't insert this user then continue to the next one to avoid inconsistent data
				$this->io_handler->add_error_message('CONV_ERROR_INSERT_BOT');

				$i++;
				continue;
			}

			$sql = 'INSERT INTO ' . BOTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
				'bot_active'	=> 1,
				'bot_name'		=> (string) $crawler_name,
				'user_id'		=> (int) $user_id,
				'bot_agent'		=> (string) $user_agent,
				'bot_ip'		=> '',
			));

			$this->db->sql_query($sql);

			$i++;

			// Stop execution if resource limit is reached
			if ($this->install_config->get_time_remaining() <= 0 || $this->install_config->get_memory_remaining() <= 0)
			{
				break;
			}
		}

		$this->install_config->set('add_ai_crawlers_index', $i);

		if ($i < count($this->ai_crawlers))
		{
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
		return 'TASK_ADD_AI_CRAWLERS';
	}
}
