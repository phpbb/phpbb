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

namespace phpbb\notification\controller;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;

class mark_subforums_read
{
	/** @var auth */
	protected $auth;

	/** @var config */
	protected $config;

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
	 * @param auth $auth
	 * @param config $config
	 * @param language $language
	 * @param request $request
	 * @param user $user
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	 */
	public function __construct(auth $auth, config $config, language $language, request $request, user $user, string $phpbb_root_path, string $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->language = $language;
		$this->request = $request;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Handle marking forums read
	 *
	 * @param int $id Forum ID
	 * @return void|JsonResponse
	 */
	public function handle(int $id)
	{
		global $phpbb_container;

		/** @var \phpbb\controller\helper  $controller_helper */
		$controller_helper = $phpbb_container->get('controller.helper');

		$root_data = get_forum_data($id);

		if (!$root_data)
		{
			trigger_error('NO_FORUM');
		}

		$rows = get_forums_rows($root_data);

		$forum_ids = [];
		foreach ($rows as $row)
		{
			$forum_id = $row['forum_id'];

			if ($this->auth->acl_get('f_list', $forum_id))
			{
				$forum_ids[] = $forum_id;
			}
		}

		$redirect = build_url(['mark', 'hash', 'mark_time']);
		$token = $this->request->variable('hash', '');
		if (check_link_hash($token, 'global'))
		{
			markread('topics', $forum_ids, false, $this->request->variable('mark_time', 0));
			$message = sprintf($this->language->lang('RETURN_FORUM'), '<a href="' . $redirect . '">', '</a>');
			meta_refresh(3, $redirect);

			if ($this->request->is_ajax())
			{
				// Tell the ajax script what language vars and URL need to be replaced
				$data = [
					'NO_UNREAD_POSTS'	=> $this->language->lang('NO_UNREAD_POSTS'),
					'UNREAD_POSTS'		=> $this->language->lang('UNREAD_POSTS'),
					'U_MARK_FORUMS'		=> ($this->user->data['is_registered'] || $this->config['load_anon_lastread']) ? $controller_helper->route('phpbb_notifications_mark_subforums_read', ['id' => $root_data['forum_id'], 'hash' => generate_link_hash('global'), 'mark_time' => time()]) : '',
					'MESSAGE_TITLE'		=> $this->language->lang('INFORMATION'),
					'MESSAGE_TEXT'		=> $this->language->lang('FORUMS_MARKED')
				];
				return new JsonResponse($data);
			}

			trigger_error($this->language->lang('FORUMS_MARKED') . '<br /><br />' . $message);
		}
		else
		{
			$message = sprintf($this->language->lang('RETURN_PAGE'), '<a href="' . $redirect . '">', '</a>');
			meta_refresh(3, $redirect);
			trigger_error($message);
		}
	}
}
