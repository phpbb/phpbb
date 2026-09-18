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
use phpbb\controller\helper;
use phpbb\forum\helper as forum_helper;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;

class mark_topics_read
{
	/** @var auth */
	protected $auth;

	/** @var config */
	protected $config;

	/** @var helper */
	protected $controller_helper;

	/** @var forum_helper */
	protected $forum_helper;

	/** @var language */
	protected $language;

	/** @var request */
	protected $request;

	/** @var user */
	protected $user;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param auth         $auth
	 * @param config       $config
	 * @param helper       $controller_helper
	 * @param forum_helper $forum_helper
	 * @param language     $language
	 * @param request      $request
	 * @param user         $user
	 * @param string       $phpbb_root_path
	 * @param string       $php_ext
	 */
	public function __construct(auth $auth, config $config, helper $controller_helper, forum_helper $forum_helper, language $language, request $request, user $user, string $phpbb_root_path, string $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->controller_helper = $controller_helper;
		$this->forum_helper = $forum_helper;
		$this->language = $language;
		$this->request = $request;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Handle marking topics read
	 *
	 * @param int $id Forum ID
	 * @return void|JsonResponse
	 */
	public function handle(int $id)
	{
		$this->language->add_lang('viewforum');

		$forum_data = $this->forum_helper->get_forum_data($id);

		if (!$forum_data)
		{
			trigger_error('NO_FORUM');
		}

		if (!$this->auth->acl_gets('f_read', 'f_list_topics', $id))
		{
			if ($this->user->data['user_id'] != ANONYMOUS)
			{
				send_status_line(403, 'Forbidden');
				trigger_error('SORRY_AUTH_READ');
			}

			login_box('', $this->language->lang('LOGIN_VIEWFORUM'));
		}

		// Handle marking posts
		$redirect = append_sid("{$this->phpbb_root_path}viewforum.{$this->php_ext}", 'f=' . $id);
		meta_refresh(3, $redirect);

		$token = $this->request->variable('hash', '');
		if (check_link_hash($token, 'global'))
		{
			markread('topics', $id, false, $this->request->variable('mark_time', 0));

			if ($this->request->is_ajax())
			{
				// Tell the ajax script what language vars and URL need to be replaced
				$data = [
					'NO_UNREAD_POSTS' => $this->language->lang('NO_UNREAD_POSTS'),
					'UNREAD_POSTS'    => $this->language->lang('UNREAD_POSTS'),
					'U_MARK_TOPICS'   => ($this->user->data['is_registered'] || $this->config['load_anon_lastread']) ? $this->controller_helper->route('phpbb_forum_mark_topics_read', ['id' => $id, 'hash' => generate_link_hash('global'), 'mark_time' => time()], false) : '',
					'MESSAGE_TITLE'   => $this->language->lang('INFORMATION'),
					'MESSAGE_TEXT'    => $this->language->lang('TOPICS_MARKED')
				];
				return new JsonResponse($data);
			}

			$message = sprintf($this->language->lang('RETURN_FORUM'), '<a href="' . $redirect . '">', '</a>');
			trigger_error($this->language->lang('TOPICS_MARKED') . '<br /><br />' . $message);
		}
		else
		{
			$message = sprintf($this->language->lang('RETURN_FORUM'), '<a href="' . $redirect . '">', '</a>');
			trigger_error($message);
		}
	}
}
