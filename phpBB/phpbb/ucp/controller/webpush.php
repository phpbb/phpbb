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
use phpbb\controller\helper as controller_helper;
use phpbb\db\driver\driver_interface;
use phpbb\exception\http_exception;
use phpbb\form\form_helper;
use phpbb\json\sanitizer as json_sanitizer;
use phpbb\language\language;
use phpbb\notification\manager;
use phpbb\path_helper;
use phpbb\request\request_interface;
use phpbb\symfony_request;
use phpbb\user;
use phpbb\user_loader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class webpush
{
	/** @var string UCP form token name */
	public const FORM_TOKEN_UCP = 'ucp_webpush';

	/** @var config */
	protected $config;

	/** @var controller_helper */
	protected $controller_helper;

	/** @var driver_interface */
	protected $db;

	/** @var form_helper */
	protected $form_helper;

	/** @var language */
	protected $language;

	/** @var manager */
	protected $notification_manager;

	/** @var path_helper */
	protected $path_helper;

	/** @var request_interface */
	protected $request;

	/** @var user_loader */
	protected $user_loader;

	/** @var user */
	protected $user;

	/** @var Environment */
	protected $template;

	/** @var string */
	protected $notification_webpush_table;

	/** @var string */
	protected $push_subscriptions_table;

	/**
	 * Constructor for webpush controller
	 *
	 * @param config $config
	 * @param controller_helper $controller_helper
	 * @param driver_interface $db
	 * @param form_helper $form_helper
	 * @param language $language
	 * @param manager $notification_manager
	 * @param path_helper $path_helper
	 * @param request_interface $request
	 * @param user_loader $user_loader
	 * @param user $user
	 * @param Environment $template
	 * @param string $notification_webpush_table
	 * @param string $push_subscriptions_table
	 */
	public function __construct(config $config, controller_helper $controller_helper, driver_interface $db, form_helper $form_helper, language $language, manager $notification_manager,
								path_helper $path_helper, request_interface $request, user_loader $user_loader, user $user, Environment $template, string $notification_webpush_table, string $push_subscriptions_table)
	{
		$this->config = $config;
		$this->controller_helper = $controller_helper;
		$this->db = $db;
		$this->form_helper = $form_helper;
		$this->language = $language;
		$this->notification_manager = $notification_manager;
		$this->path_helper = $path_helper;
		$this->request = $request;
		$this->user_loader = $user_loader;
		$this->user = $user;
		$this->template = $template;
		$this->notification_webpush_table = $notification_webpush_table;
		$this->push_subscriptions_table = $push_subscriptions_table;
	}

	/**
	 * Handle request to retrieve notification data
	 *
	 * @return JsonResponse
	 */
	public function notification(): JsonResponse
	{
		if (!$this->request->is_ajax() || $this->user->data['is_bot'] || $this->user->data['user_type'] == USER_INACTIVE)
		{
			throw new http_exception(Response::HTTP_FORBIDDEN, 'NO_AUTH_OPERATION');
		}

		if ($this->user->id() !== ANONYMOUS)
		{
			$notification_data = $this->get_user_notifications();
		}
		else
		{
			$notification_data = $this->get_anonymous_notifications();
		}

		// Decode and return data if everything is fine
		$data = json_decode($notification_data, true);
		$data['url'] = isset($data['url']) ? $this->path_helper->update_web_root_path($data['url']) : '';

		return new JsonResponse($data);
	}

	/**
	 * Get notification data for logged in user
	 *
	 * @return string Notification data
	 */
	private function get_user_notifications(): string
	{
		// Subscribe should only be available for logged-in "normal" users
		if ($this->user->data['user_type'] == USER_IGNORE)
		{
			throw new http_exception(Response::HTTP_FORBIDDEN, 'NO_AUTH_OPERATION');
		}

		$item_id = $this->request->variable('item_id', 0);
		$type_id = $this->request->variable('type_id', 0);

		$sql = 'SELECT push_data
			FROM ' . $this->notification_webpush_table . '
			WHERE user_id = ' . (int) $this->user->id() . '
				AND notification_type_id = ' . (int) $type_id . '
				AND item_id = ' . (int) $item_id;
		$result = $this->db->sql_query($sql);
		$notification_data = $this->db->sql_fetchfield('push_data');
		$this->db->sql_freeresult($result);

		return $this->get_notification_data($notification_data);
	}

	/**
	 * Get notification data for not logged in user via token
	 *
	 * @return string
	 */
	private function get_anonymous_notifications(): string
	{
		$token = $this->request->variable('token', '');

		if ($token)
		{
			$item_id = $this->request->variable('item_id', 0);
			$type_id = $this->request->variable('type_id', 0);
			$user_id = $this->request->variable('user_id', 0);

			$sql = 'SELECT push_data, push_token
				FROM ' . $this->notification_webpush_table . '
				WHERE user_id = ' . (int) $user_id . '
					AND notification_type_id = ' . (int) $type_id . '
					AND item_id = ' . (int) $item_id;
			$result = $this->db->sql_query($sql);
			$notification_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$notification_data = $notification_row['push_data'];
			$push_token = $notification_row['push_token'];

			// Check if passed push token is valid
			$sql = 'SELECT user_form_salt, user_lang
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $user_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$user_form_token = $row['user_form_salt'];
			$user_lang = $row['user_lang'];

			$expected_push_token = hash('sha256', $user_form_token . $push_token);
			if ($expected_push_token === $token)
			{
				if ($user_lang !== $this->language->get_used_language())
				{
					$this->language->set_user_language($user_lang, true);
				}
				return $this->get_notification_data($notification_data);
			}
		}

		throw new http_exception(Response::HTTP_FORBIDDEN, 'NO_AUTH_OPERATION');
	}

	/**
	 * Get notification data for output from json encoded data stored in database
	 *
	 * @param string $notification_data Encoded data stored in database
	 *
	 * @return string Data for notification output with javascript
	 */
	private function get_notification_data(string $notification_data): string
	{
		$row_data = json_decode($notification_data, true);

		// Old notification data is pre-parsed and just needs to be returned
		if (isset($row_data['heading']))
		{
			return $notification_data;
		}

		// Get notification from row_data
		$notification = $this->notification_manager->get_item_type_class($row_data['notification_type_name'], $row_data);

		// Load users for notification
		$this->user_loader->load_users($notification->users_to_query());

		return json_encode([
			'heading'	=> $this->config['sitename'],
			'title'		=> strip_tags(html_entity_decode($notification->get_title(), ENT_NOQUOTES, 'UTF-8')),
			'text'		=> strip_tags(html_entity_decode($notification->get_reference(), ENT_NOQUOTES, 'UTF-8')),
			'url'		=> htmlspecialchars_decode($notification->get_url()),
			'avatar'	=> $notification->get_avatar(),
		]);
	}

	/**
	 * Handle request to push worker javascript
	 *
	 * @return Response
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function worker(): Response
	{
		$content = $this->template->render('push_worker.js.twig', [
			'U_WEBPUSH_GET_NOTIFICATION'	=> $this->controller_helper->route('phpbb_ucp_push_get_notification_controller'),
			'ASSETS_VERSION'				=> $this->config['assets_version'],
		]);

		$response = new Response($content);
		$response->headers->set('Content-Type', 'text/javascript; charset=UTF-8');

		if (!empty($this->user->data['is_bot']))
		{
			// Let reverse proxies know we detected a bot.
			$response->headers->set('X-PHPBB-IS-BOT', 'yes');
		}

		return $response;
	}

	/**
	 * Check (un)subscribe form for valid link hash
	 *
	 * @throws http_exception If form is invalid or user should not request (un)subscription
	 * @return void
	 */
	protected function check_subscribe_requests(): void
	{
		if (!$this->form_helper->check_form_tokens(self::FORM_TOKEN_UCP))
		{
			throw new http_exception(Response::HTTP_BAD_REQUEST, 'FORM_INVALID');
		}

		// Subscribe should only be available for logged-in "normal" users
		if (!$this->request->is_ajax() || $this->user->id() === ANONYMOUS || $this->user->data['is_bot']
			|| $this->user->data['user_type'] == USER_IGNORE || $this->user->data['user_type'] == USER_INACTIVE)
		{
			throw new http_exception(Response::HTTP_FORBIDDEN, 'NO_AUTH_OPERATION');
		}
	}

	/**
	 * Handle subscribe requests
	 *
	 * @param symfony_request $symfony_request
	 * @return JsonResponse
	 */
	public function subscribe(symfony_request $symfony_request): JsonResponse
	{
		$this->check_subscribe_requests();

		$data = json_sanitizer::decode($symfony_request->get('data', ''));

		$sql = 'INSERT INTO ' . $this->push_subscriptions_table . ' ' . $this->db->sql_build_array('INSERT', [
			'user_id'			=> $this->user->id(),
			'endpoint'			=> $data['endpoint'],
			'expiration_time'	=> $data['expiration_time'] ?? 0,
			'p256dh'			=> $data['keys']['p256dh'],
			'auth'				=> $data['keys']['auth'],
		]);
		$this->db->sql_query($sql);

		return new JsonResponse([
			'success'		=> true,
			'form_tokens'	=> $this->form_helper->get_form_tokens(self::FORM_TOKEN_UCP),
		]);
	}

	/**
	 * Handle unsubscribe requests
	 *
	 * @param symfony_request $symfony_request
	 * @return JsonResponse
	 */
	public function unsubscribe(symfony_request $symfony_request): JsonResponse
	{
		$this->check_subscribe_requests();

		$data = json_sanitizer::decode($symfony_request->get('data', ''));

		$endpoint = $data['endpoint'];

		$sql = 'DELETE FROM ' . $this->push_subscriptions_table . '
			WHERE user_id = ' . (int) $this->user->id() . "
				AND endpoint = '" . $this->db->sql_escape($endpoint) . "'";
		$this->db->sql_query($sql);

		return new JsonResponse([
			'success'		=> true,
			'form_tokens'	=> $this->form_helper->get_form_tokens(self::FORM_TOKEN_UCP),
		]);
	}
}
