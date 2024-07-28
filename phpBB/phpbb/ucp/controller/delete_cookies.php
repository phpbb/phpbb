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

namespace phpbb\ucp\controller;

use phpbb\config\config;
use phpbb\event\dispatcher_interface;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\user;

class delete_cookies
{
	/** @var config */
	private $config;

	/** @var dispatcher_interface */
	private $dispatcher;

	/** @var language */
	private $language;

	/** @var request_interface */
	private $request;

	/** @var user */
	private $user;

	/** @var string phpBB root path */
	private $phpbb_root_path;

	/** @var string PHP extension */
	private $php_ext;

	/**
	 * Constructor for delete_cookies controller
	 *
	 * @param config $config
	 * @param dispatcher_interface $dispatcher
	 * @param language $language
	 * @param request_interface $request
	 * @param user $user
	 */
	public function __construct(config $config, dispatcher_interface $dispatcher, language $language, request_interface $request, user $user, string $phpbb_root_path, string $php_ext)
	{
		$this->config = $config;
		$this->dispatcher = $dispatcher;
		$this->language = $language;
		$this->request = $request;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Handle delete cookies requests
	 *
	 * @return void
	 */
	public function handle()
	{
		$this->language->add_lang(['ucp']);

		// Delete Cookies with dynamic names (do NOT delete poll cookies)
		if (confirm_box(true))
		{
			$set_time = time() - 31536000;

			foreach ($this->request->variable_names(request_interface::COOKIE) as $cookie_name)
			{
				// Only delete board cookies
				if (strpos($cookie_name, $this->config['cookie_name'] . '_') !== 0)
				{
					continue;
				}

				$cookie_name = str_replace($this->config['cookie_name'] . '_', '', $cookie_name);

				/**
				 * Event to save custom cookies from deletion
				 *
				 * @event core.ucp_delete_cookies
				 * @var	string	cookie_name		Cookie name to checking
				 * @var	bool	retain_cookie	Do we retain our cookie or not, true if retain
				 * @since 3.1.3-RC1
				 * @changed 3.3.13-RC1 Moved to new delete_cookies controller
				 */
				$retain_cookie = false;
				$vars = ['cookie_name', 'retain_cookie'];
				extract($this->dispatcher->trigger_event('core.ucp_delete_cookies', compact($vars)));
				if ($retain_cookie)
				{
					continue;
				}

				// Polls are stored as {cookie_name}_poll_{topic_id}, cookie_name_ got removed, therefore checking for poll_
				if (strpos($cookie_name, 'poll_') !== 0)
				{
					$this->user->set_cookie($cookie_name, '', $set_time);
				}
			}

			$this->user->set_cookie('track', '', $set_time);
			$this->user->set_cookie('u', '', $set_time);
			$this->user->set_cookie('k', '', $set_time);
			$this->user->set_cookie('sid', '', $set_time);

			// We destroy the session here, the user will be logged out nevertheless
			$this->user->session_kill();
			$this->user->session_begin();

			meta_refresh(3, append_sid("{$this->phpbb_root_path}index.$this->php_ext"));

			$message = $this->language->lang('COOKIES_DELETED') . '<br><br>' . $this->language->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->phpbb_root_path}index.$this->php_ext") . '">', '</a>');
			trigger_error($message);
		}
		else
		{
			confirm_box(false, 'DELETE_COOKIES', '');
		}

		redirect(append_sid("{$this->phpbb_root_path}index.$this->php_ext"));
	}
}
