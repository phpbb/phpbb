<?php

namespace phpbb\ucp\controller;

use phpbb\config\config;
use phpbb\controller\helper as controller_helper;
use phpbb\db\driver\driver_interface;
use phpbb\exception\http_exception;
use phpbb\form\form_helper;
use phpbb\json\sanitizer as json_sanitizer;
use phpbb\path_helper;
use phpbb\request\request_interface;
use phpbb\symfony_request;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class webpush
{
	/** @var string UCP form token name */
	private const FORM_TOKEN_UCP = 'ucp_webpush';

	/** @var string Push worker form token name */
	private const FORM_TOKEN_WORKER = 'webpush_worker';

	/** @var config */
	protected $config;

	/** @var controller_helper */
	protected $controller_helper;

	/** @var driver_interface */
	protected $db;

	/** @var form_helper */
	protected $form_helper;

	/** @var path_helper */
	protected $path_helper;

	/** @var request_interface */
	protected $request;

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
	 * @param path_helper $path_helper
	 * @param request_interface $request
	 * @param user $user
	 * @param Environment $template
	 * @param string $notification_webpush_table
	 * @param string $push_subscriptions_table
	 */
	public function __construct(config $config, controller_helper $controller_helper, driver_interface $db, form_helper $form_helper, path_helper $path_helper,
								request_interface $request, user $user, Environment $template, string $notification_webpush_table, string $push_subscriptions_table)
	{
		$this->config = $config;
		$this->controller_helper = $controller_helper;
		$this->db = $db;
		$this->form_helper = $form_helper;
		$this->path_helper = $path_helper;
		$this->request = $request;
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
		// Subscribe should only be available for logged-in "normal" users
		if (!$this->request->is_ajax() || $this->user->id() == ANONYMOUS || $this->user->data['is_bot']
			|| $this->user->data['user_type'] == USER_IGNORE || $this->user->data['user_type'] == USER_INACTIVE)
		{
			throw new http_exception(Response::HTTP_FORBIDDEN, 'Forbidden');
		}

		$item_id = $this->request->variable('item_id', 0);
		$type_id = $this->request->variable('type_id', 0);

		$sql = 'SELECT push_data
			FROM ' . $this->notification_webpush_table . '
			WHERE user_id = ' . $this->user->id() . '
				AND notification_type_id = ' . $type_id . '
				AND item_id = ' . $item_id;
		$result = $this->db->sql_query($sql);
		$notification_data = $this->db->sql_fetchfield('push_data');
		$this->db->sql_freeresult($result);
		$data = json_decode($notification_data, true);
		$data['url'] = isset($data['url']) ? $this->path_helper->update_web_root_path($data['url']) : '';

		return new JsonResponse($data);
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
		// @todo: only work for logged in users, no anonymous & bot
		$content = $this->template->render('push_worker.js.twig', [
			'U_WEBPUSH_GET_NOTIFICATION'	=> $this->controller_helper->route('phpbb_ucp_push_get_notification_controller'),
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
	 * Get template variables for subscribe type pages
	 *
	 * @return array
	 */
	protected function get_subscribe_vars(): array
	{
		return [
			'U_WEBPUSH_SUBSCRIBE'	=> $this->controller_helper->route('phpbb_ucp_push_subscribe_controller'),
			'U_WEBPUSH_UNSUBSCRIBE'	=> $this->controller_helper->route('phpbb_ucp_push_unsubscribe_controller'),
			'FORM_TOKENS'			=> $this->form_helper->get_form_tokens(self::FORM_TOKEN_UCP),
		];
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
		if (!$this->request->is_ajax() || $this->user->id() == ANONYMOUS || $this->user->data['is_bot']
			|| $this->user->data['user_type'] == USER_IGNORE || $this->user->data['user_type'] == USER_INACTIVE)
		{
			throw new http_exception(Response::HTTP_FORBIDDEN, 'NO_AUTH_OPERATION');
		}
	}

	/**
	 * Handle request to web push javascript
	 *
	 * @return Response
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function js(): Response
	{
		// @todo: return forbidden for guest & bot

		$template_data = $this->get_subscribe_vars();
		$template_data += [
			'VAPID_PUBLIC_KEY'		=> $this->config['webpush_vapid_public'],
			'U_WEBPUSH_WORKER_URL'	=> $this->controller_helper->route('phpbb_ucp_push_worker_controller'),
		];

		$content = $this->template->render('webpush.js.twig', $template_data);

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
			WHERE user_id = ' . $this->user->id() . "
				AND endpoint = '" . $this->db->sql_escape($endpoint) . "'";
		$this->db->sql_query($sql);

		return new JsonResponse([
			'success'		=> true,
			'form_tokens'	=> $this->form_helper->get_form_tokens(self::FORM_TOKEN_UCP),
		]);
	}
}
