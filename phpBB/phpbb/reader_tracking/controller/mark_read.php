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

namespace phpbb\reader_tracking\controller;

use phpbb\exception\http_exception;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for marking forums read.
 */
class mark_read
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $controller_helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\forum\forum_retriever */
	protected $forum_retriever;

	/** @var \phpbb\forum\visibility_helper */
	protected $forum_visibility_helper;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config $config
	 * @param \phpbb\controller\helper $helper
	 * @param \phpbb\language\language $language
	 * @param \phpbb\request\request_interface $request
	 * @param \phpbb\user $user
	 * @param \phpbb\forum\forum_retriever $forum_retriever
	 * @param \phpbb\forum\visibility_helper $forum_visibility_helper
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\request\request_interface $request,
		\phpbb\user $user,
		\phpbb\forum\forum_retriever $forum_retriever,
		\phpbb\forum\visibility_helper $forum_visibility_helper)
	{
		$this->config = $config;
		$this->controller_helper = $helper;
		$this->language = $language;
		$this->request = $request;
		$this->user = $user;

		$this->forum_retriever = $forum_retriever;
		$this->forum_visibility_helper = $forum_visibility_helper;
	}

	/**
	 * Marks forums read.
	 *
	 * @param int		$forum_id	ID of the forum that we want to mark as read.
	 * @param int		$time		Timestamp before which we want to mark posts read.
	 * @param string	$token		Link security token.
	 *
	 * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function mark_forum_read($forum_id, $time, $token)
	{
		$forum_id = (int) $forum_id;
		$result = false;

		try
		{
			$forum_data = $this->forum_retriever->get_forum_metadata($forum_id);

			$forums = $this->forum_retriever->get_subforums($forum_data);
			$forums = $this->forum_visibility_helper->filter_subforums($forums);

			$ids = array_column($forums, 'forum_id');

			if (check_link_hash($token, 'global'))
			{
				markread('topics', $ids, false, $time);
				$result = true;
			}
		}
		catch (\Exception $e)
		{
			throw new http_exception(404, 'NO_FORUM');
		}

		$viewforum_route = $this->controller_helper->route(
			'phpbb_view_forum',
			[
				'forum_id' => $forum_id,
				'parameters' => ''
			]
		);

		if ($result)
		{
			return $this->create_success_message(
				'FORUMS_MARKED',
				'RETURN_FORUM',
				['<a href="' . $viewforum_route . '">', '</a>'],
				$viewforum_route,
				$forum_id
			);
		}

		return $this->create_failure_message($viewforum_route);
	}

	/**
	 * Creates a confirmation response on success.
	 *
	 * @param string	$title		The title of the message.
	 * @param string	$msg		The message
	 * @param array		$msg_params	Params of the message.
	 * @param string	$url		Return url.
	 * @param int		$forum_id	Forum ID.
	 *
	 * @return JsonResponse|\Symfony\Component\HttpFoundation\Response The response object.
	 */
	protected function create_success_message($title, $msg, $msg_params, $url, $forum_id)
	{
		if ($this->request->is_ajax())
		{
			$mark_link = '';
			if ($this->user->data['is_registered'] || $this->config['load_anon_lastread'])
			{
				$mark_link = $this->controller_helper->route(
					'phpbb_mark_forum_read',
					[
						'forum_id'	=> $forum_id,
						'time'		=> time(),
						'token'		=> generate_link_hash('global')
					]
				);
			}

			$data = [
				'NO_UNREAD_POSTS'	=> $this->language->lang('NO_UNREAD_POSTS'),
				'UNREAD_POSTS'		=> $this->language->lang('UNREAD_POSTS'),
				'U_MARK_FORUMS'		=> $mark_link,
				'MESSAGE_TITLE'		=> $this->language->lang('INFORMATION'),
				'MESSAGE_TEXT'		=> $this->language->lang('FORUMS_MARKED')
			];

			return new JsonResponse($data);
		}

		$this->controller_helper->assign_meta_refresh_var(3, $url);
		return $this->controller_helper->message($msg, $msg_params, $title);
	}

	/**
	 * Generate error message.
	 *
	 * @param string $url	Redirect url.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response The response object.
	 */
	protected function create_failure_message($url)
	{
		$this->controller_helper->assign_meta_refresh_var(3, $url);
		return $this->controller_helper->message(
			'RETURN_PAGE',
			['<a href="' . $url . '">', '</a>']
		);
	}
}
