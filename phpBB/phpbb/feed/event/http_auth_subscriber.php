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

namespace phpbb\feed\event;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber for HTTP authentication on feed routes
 */
class http_auth_subscriber implements EventSubscriberInterface
{
	/** @var auth */
	protected $auth;

	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/** @var request_interface */
	protected $request;

	/** @var user */
	protected $user;

	/**
	 * Constructor
	 *
	 * @param auth				$auth		Auth object
	 * @param config			$config		Config object
	 * @param language	$language	Language object
	 * @param request_interface	$request	Request object
	 * @param user				$user		User object
	 */
	public function __construct(auth $auth, config $config, language $language, request_interface $request, user $user)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->language = $language;
		$this->request = $request;
		$this->user = $user;
	}

	/**
	 * Handle HTTP authentication for feed routes
	 *
	 * @param RequestEvent $event
	 * @return void
	 */
	public function on_kernel_request(RequestEvent $event)
	{
		// Check if HTTP authentication is enabled
		if (!$this->config['feed_http_auth'])
		{
			return;
		}

		$request = $event->getRequest();
		$route = $request->attributes->get('_route');

		// Only apply to feed routes
		if (strpos($route, 'phpbb_feed_') !== 0)
		{
			return;
		}

		// Only allow HTTP authentication in secure context (HTTPS)
		if (!$request->isSecure())
		{
			return;
		}

		// User is already logged in, no need to authenticate
		if (!empty($this->user->data['is_registered']))
		{
			return;
		}

		// Get HTTP authentication credentials
		[$username, $password] = $this->get_credentials();

		// If no credentials provided, send authentication challenge
		if ($username === null || $password === null)
		{
			$this->send_auth_challenge($event);
			return;
		}

		// Attempt to login with the provided credentials
		$auth_result = $this->auth->login($username, $password, false, true, false);

		if ($auth_result['status'] == LOGIN_SUCCESS)
		{
			// Reload ACL for the newly logged-in user
			$this->auth->acl($this->user->data);
			return;
		}
		else if ($auth_result['status'] == LOGIN_ERROR_ATTEMPTS)
		{
			// Too many login attempts
			$response = new Response($this->language->lang('LOGIN_ERROR_ATTEMPTS'), Response::HTTP_UNAUTHORIZED);
			$event->setResponse($response);
			return;
		}

		// Authentication failed, send challenge
		$this->send_auth_challenge($event);
	}

	/**
	 * Retrieve HTTP authentication credentials from server variables
	 *
	 * @return array [username, password] Array containing the username and password, or null if not found
	 */
	protected function get_credentials(): array
	{
		$username_keys = [
			'PHP_AUTH_USER',
			'Authorization',
			'REMOTE_USER',
			'REDIRECT_REMOTE_USER',
			'HTTP_AUTHORIZATION',
			'REDIRECT_HTTP_AUTHORIZATION',
			'REMOTE_AUTHORIZATION',
			'REDIRECT_REMOTE_AUTHORIZATION',
			'AUTH_USER',
		];

		$password_keys = [
			'PHP_AUTH_PW',
			'REMOTE_PASSWORD',
			'AUTH_PASSWORD',
		];

		$username = null;
		foreach ($username_keys as $key)
		{
			if ($this->request->is_set($key, request_interface::SERVER))
			{
				$username = htmlspecialchars_decode($this->request->server($key));
				break;
			}
		}

		$password = null;
		foreach ($password_keys as $key)
		{
			if ($this->request->is_set($key, request_interface::SERVER))
			{
				$password =  htmlspecialchars_decode($this->request->server($key));
				break;
			}
		}

		// Decode Basic authentication header if needed
		if (!is_null($username) && is_null($password) && strpos($username, 'Basic ') === 0)
		{
			[$username, $password] = explode(':', base64_decode(substr($username, 6)), 2);
		}

		return [$username, $password];
	}

	/**
	 * Send HTTP authentication challenge
	 *
	 * @param RequestEvent $event
	 * @return void
	 */
	protected function send_auth_challenge(RequestEvent $event)
	{
		$realm = $this->config['sitename'];

		// Filter out non-ASCII characters per RFC2616
		$realm = preg_replace('/[\x80-\xFF]/', '?', $realm);

		$response = new Response($this->language->lang('NOT_AUTHORISED'), Response::HTTP_UNAUTHORIZED);
		$response->headers->set('WWW-Authenticate', 'Basic realm="' . $realm . ' - Feed"');
		$event->setResponse($response);
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			KernelEvents::REQUEST => ['on_kernel_request', 5],
		];
	}
}
