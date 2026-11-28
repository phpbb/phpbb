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

namespace test\email_catcher\controller;

use phpbb\config\config;
use phpbb\messenger\method\email;
use phpbb\notification\manager;
use phpbb\request\request_interface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test-only controller used to trigger notifications that cannot be reached
 * reliably from a functional test without external services.
 */
class trigger
{
	/** @var manager */
	protected $notification_manager;

	/** @var request_interface */
	protected $request;

	/** @var config */
	protected $config;

	/** @var email */
	protected $email_method;

	/**
	 * Constructor
	 *
	 * @param manager			$notification_manager Notification manager
	 * @param request_interface	$request			Request object
	 * @param config			$config				Config object
	 * @param email				$email_method		Email messenger method
	 */
	public function __construct(manager $notification_manager, request_interface $request, config $config, email $email_method)
	{
		$this->notification_manager = $notification_manager;
		$this->request = $request;
		$this->config = $config;
		$this->email_method = $email_method;
	}

	/**
	 * Handle the request.
	 *
	 * @return Response
	 */
	public function handle(): Response
	{
		if (!defined('PHPBB_ENVIRONMENT') || PHPBB_ENVIRONMENT !== 'test')
		{
			return new Response('Not available', Response::HTTP_NOT_FOUND);
		}

		$type = $this->request->variable('type', '');

		if ($type === 'delete_users')
		{
			$user_ids = $this->request->variable('user_ids', '');
			$user_ids = array_filter(array_map('intval', explode(',', $user_ids)), static function (int $user_id): bool
			{
				return $user_id > 1;
			});

			if (!empty($user_ids))
			{
				global $phpbb_root_path, $phpEx;

				if (!function_exists('user_delete'))
				{
					require_once $phpbb_root_path . 'includes/functions_user.' . $phpEx;
				}

				user_delete('retain', $user_ids);
			}

			return new Response('OK');
		}

		if ($type === 'installed')
		{
			$this->email_method->set_use_queue(false);
			$this->email_method->template('installed', 'en');
			$this->email_method->to($this->config['board_email'], 'admin');
			$this->email_method->assign_vars([
				'USERNAME'	=> 'admin',
				'PASSWORD'	=> 'adminadmin',
			]);
			$this->email_method->send();

			return new Response('OK');
		}

		if ($type !== 'update')
		{
			return new Response('Unknown type', Response::HTTP_BAD_REQUEST);
		}

		$template = $this->request->variable('template', 'update_maintenance');
		$allowed_templates = ['update_maintenance', 'update_critical', 'update_security'];

		if (!in_array($template, $allowed_templates, true))
		{
			return new Response('Unknown template', Response::HTTP_BAD_REQUEST);
		}

		$new_version = '99.0.0';

		$this->notification_manager->add_notifications('notification.type.update_maintenance', [
			'item_id'			=> crc32($template . $new_version) & 0xFFFFFF,
			'template'			=> $template,
			'current_version'	=> $this->config['version'],
			'new_version'		=> $new_version,
			'announcement'		=> '',
			'download'			=> '',
		]);

		return new Response('OK');
	}
}
