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

namespace phpbb\forum\controller;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\controller\helper as controller_helper;
use phpbb\event\dispatcher;
use phpbb\forum\birthday_helper;
use phpbb\group\helper as group_helper;
use phpbb\language\language;
use phpbb\user;

class index
{
	protected $auth;
	protected $birthday_helper;
	protected $config;
	protected $controller_helper;
	protected $dispatcher;
	protected $group_helper;
	protected $language;
	protected $user;
	protected $phpbb_root_path;
	protected $phpEx;

	public function __construct(auth $auth, birthday_helper $birthday_helper, config $config, controller_helper $controller_helper, dispatcher $dispatcher, group_helper $group_helper, language $language, user $user, string $phpbb_root_path, string $phpEx)
	{
		$this->auth = $auth;
		$this->birthday_helper = $birthday_helper;
		$this->config = $config;
		$this->controller_helper = $controller_helper;
		$this->dispatcher = $dispatcher;
		$this->group_helper = $group_helper;
		$this->language = $language;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
	}

	public function handle()
	{
		if (!function_exists('display_forums'))
		{
			include($this->phpbb_root_path . 'includes/functions_display.' . $this->phpEx);
		}

		if (!function_exists('get_username_string'))
		{
			include($this->phpbb_root_path . 'includes/functions_content.' . $this->phpEx);
		}

		$this->language->add_lang('viewforum');

		display_forums('', $this->config['load_moderators']);

		global $db, $template;
		$this->group_helper->display_legend($db, $template); // todo: updated in https://github.com/phpbb/phpbb/pull/5410

		$this->birthday_helper->display_birthdays();

		$template->assign_vars(array(
				'TOTAL_POSTS'	=> $this->language->lang('TOTAL_POSTS_COUNT', (int) $this->config['num_posts']),
				'TOTAL_TOPICS'	=> $this->language->lang('TOTAL_TOPICS', (int) $this->config['num_topics']),
				'TOTAL_USERS'	=> $this->language->lang('TOTAL_USERS', (int) $this->config['num_users']),
				'NEWEST_USER'	=> $this->language->lang('NEWEST_USER', get_username_string('full', $this->config['newest_user_id'], $this->config['newest_username'], $this->config['newest_user_colour'])),

				'S_LOGIN_ACTION'			=> append_sid("{$this->phpbb_root_path}ucp.$this->phpEx", 'mode=login'),
				'U_SEND_PASSWORD'           => ($this->config['email_enable'] && $this->config['allow_password_reset']) ? $this->controller_helper->route('phpbb_ucp_forgot_password_controller') : '',
				'S_INDEX'					=> true,

				'U_CANONICAL'		=> generate_board_url() . '/',
				'U_MARK_FORUMS'		=> ($this->user->data['is_registered'] || $this->config['load_anon_lastread']) ? append_sid("{$this->phpbb_root_path}index.$this->phpEx", 'hash=' . generate_link_hash('global') . '&amp;mark=forums&amp;mark_time=' . time()) : '',
				'U_MCP'				=> ($this->auth->acl_get('m_') || $this->auth->acl_getf_global('m_')) ? append_sid("{$this->phpbb_root_path}mcp.$this->phpEx", 'i=main&amp;mode=front') : '')
		);

		$page_title = ($this->config['board_index_text'] !== '') ? $this->config['board_index_text'] : $this->language->lang('INDEX');

		/**
		 * You can use this event to modify the page title and load data for the index
		 *
		 * @event core.index_modify_page_title
		 * @var	string	page_title		Title of the index page
		 * @since 3.1.0-a1
		 */
		$vars = array('page_title');
		extract($this->dispatcher->trigger_event('core.index_modify_page_title', compact($vars)));

		return $this->controller_helper->render('index_body.html', $page_title, 200, true);
	}
}
