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

use phpbb\event\dispatcher;
use phpbb\exception\http_exception;
use phpbb\language\language;
use phpbb\notification\manager;
use phpbb\notification\type\type_interface;
use phpbb\request\request;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class mark_read
{
	/** @var dispatcher  */
	protected $dispatcher;

	/** @var language */
	protected $language;

	/** @var manager */
	protected $manager;

	/** @var request */
	protected $request;

	/** @var user */
	protected $user;

	/** @var string */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param dispatcher	$dispatcher		Event dispatcher
	 * @param language		$language		Language object
	 * @param manager		$manager		Notification manager
	 * @param request		$request		Request object
	 * @param user			$user			User object
	 * @param string		$phpbb_root_path phpBB root path
	 */
	public function __construct(dispatcher $dispatcher, language $language, manager $manager, request $request, user $user, string $phpbb_root_path)
	{
		$this->dispatcher = $dispatcher;
		$this->language = $language;
		$this->manager = $manager;
		$this->request = $request;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	 * Mark notification as read
	 *
	 * @param int $id Notification ID
	 *
	 * @return Response
	 *
	 * @throws http_exception
	 *
	 * @psalm-suppress InvalidArgument
	 */
	public function handle(int $id): Response
	{
		$mark_notification = $id;

		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			if ($this->request->is_ajax())
			{
				throw new http_exception(403, 'LOGIN_REQUIRED');
			}
			login_box('', $this->language->lang('LOGIN_REQUIRED'));
		}

		if (!check_link_hash($this->request->variable('hash', ''), 'mark_notification_read'))
		{
			// Link hash error
			throw new http_exception(403, 'NOT_AUTHORISED');
		}

		$notification = $this->manager->load_notifications('notification.method.board', [
			'notification_id'	=> $mark_notification,
		]);

		if (!isset($notification['notifications'][$mark_notification]))
		{
			// Notification id not found
			throw new http_exception(404, 'PAGE_NOT_FOUND');
		}

		$notification = $notification['notifications'][$mark_notification];

		$notification->mark_read();

		/**
		 * You can use this event to perform additional tasks or redirect user elsewhere.
		 *
		 * @event core.index_mark_notification_after
		 * @var		int				mark_notification	Notification ID
		 * @var		type_interface	notification		Notification instance
		 * @since 3.2.6-RC1
		 */
		$vars = ['mark_notification', 'notification'];
		extract($this->dispatcher->trigger_event('core.index_mark_notification_after', compact($vars)));

		if ($this->request->is_ajax())
		{
			$data = [
				'success'	=> true,
			];
			return new JsonResponse($data);
		}

		if (($redirect = $this->request->variable('redirect', '')))
		{
			return new RedirectResponse(append_sid($this->phpbb_root_path . $redirect));
		}

		return new RedirectResponse($notification->get_redirect_url());
	}
}
