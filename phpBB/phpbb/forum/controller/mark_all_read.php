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

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class mark_all_read
{
	/** @var config */
	protected $config;

	/** @var helper */
	protected $controller_helper;

	/** @var language */
	protected $language;

	/** @var request */
	protected $request;

	/** @var user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param config $config
	 * @param helper $controller_helper
	 * @param language $language
	 * @param request $request
	 * @param user $user
	 */
	public function __construct(config $config, helper $controller_helper, language $language, request $request, user $user)
	{
		$this->config = $config;
		$this->controller_helper = $controller_helper;
		$this->language = $language;
		$this->request = $request;
		$this->user = $user;
	}

	/**
	 * Handle marking everything read
	 *
	 * @return Response
	 */
	public function handle(): Response
	{
		$this->language->add_lang('viewforum');

		// Handle marking everything read
		$redirect = $this->controller_helper->route('phpbb_index_controller');
		meta_refresh(3, $redirect);

		$return_message = $this->language->lang('RETURN_INDEX', '<a href="' . $redirect . '">', '</a>');

		$token = $this->request->variable('hash', '');
		if (!check_link_hash($token, 'global'))
		{
			return $this->controller_helper->message($return_message);
		}

		markread('all', false, false, $this->request->variable('mark_time', 0));

		if ($this->request->is_ajax())
		{
			// Tell the ajax script what language vars and URL need to be replaced
			$data = [
				'NO_UNREAD_POSTS'	=> $this->language->lang('NO_UNREAD_POSTS'),
				'UNREAD_POSTS'		=> $this->language->lang('UNREAD_POSTS'),
				'U_MARK_FORUMS'		=> ($this->user->data['is_registered'] || $this->config['load_anon_lastread']) ? $this->controller_helper->route('phpbb_forum_mark_all_read', ['hash' => generate_link_hash('global'), 'mark_time' => time()], false) : '',
				'MESSAGE_TITLE'		=> $this->language->lang('INFORMATION'),
				'MESSAGE_TEXT'		=> $this->language->lang('FORUMS_MARKED'),
			];
			return new JsonResponse($data);
		}

		return $this->controller_helper->message($this->language->lang('FORUMS_MARKED') . '<br /><br />' . $return_message);
	}
}
