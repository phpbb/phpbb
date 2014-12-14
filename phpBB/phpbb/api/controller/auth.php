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

namespace phpbb\api\controller;

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\api\exception\api_exception;
use phpbb\api\exception\invalid_key_exception;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * Controller for authentication
 */
class auth
{
	/**
	 * API Repository
	 * @var \phpbb\api\repository\auth
	 */
	protected $auth_repository;

	/**
	 * User object
	 * @var user
	 */
	protected $user;

	/**
	 * Controller helper object
	 * @var helper
	 */
	protected $helper;

	/**
	 * Template object
	 * @var template
	 */
	protected $template;

	/**
	 * Template object
	 * @var request
	 */
	protected $request;

	/**
	 * Config object
	 * @var config
	 */
	protected $config;

	/**
	 * Auth object
	 * @var \phpbb\auth\auth
	 */
	protected $auth;

	/**
	 * @var Serializer
	 */
	protected $serializer;

	/**
	 * Constructor
	 *
	 * @param \phpbb\api\repository\auth $auth_repository The repository containing logic regarding API authentication
	 * @param user 						 $user
	 * @param helper 					 $helper The controller helper object that helps with rendering
	 * @param template 					 $template
	 * @param request 					 $request
	 * @param config 					 $config
	 * @param \phpbb\auth\auth 			 $auth The phpBB auth object
	 */
	function __construct(\phpbb\api\repository\auth $auth_repository, user $user, helper $helper, template $template, request $request, config $config, \phpbb\auth\auth $auth)
	{
		$this->auth_repository = $auth_repository;
		$this->user = $user;
		$this->helper = $helper;
		$this->template = $template;
		$this->request = $request;
		$this->config = $config;
		$this->auth = $auth;
		$this->serializer = new Serializer(array(), array(new JsonEncoder()));

		$this->user->add_lang('api');
	}

	/**
	 * Starting endpoint for authorization of an application. Generates 3 keys, an exchange key, an authentication key
	 * and a sign key. Only the exchange key is returned to the application, which can exchange that key
	 * for the authentication key and sign key after the user has authorized the application
	 *
	 * @return JsonResponse A Json object containing status and the exchange_key
	 */
	public function generate_keys()
	{
		$exchange_key = $this->auth_repository->generate_keys();

		$response = array(
			'status' => 200,
			'exchange_key' => $exchange_key,
		);

		return new JsonResponse($this->serializer->serialize($response, 'json'), $response['status']);
	}

	/**
	 * Second endpoint in authorization that lets the user authorize the application. This endpoint + exchange_key
	 * should be given to the user to visit in his or her browser by the application.
	 *
	 * @param string $exchange_key The exchange key returned by generate_keys()
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A page (template) with the required form for the user to
	 * authorize the application
	 */
	public function authorize_application($exchange_key)
	{
		if (!$this->auth->acl_get('u_api'))
		{
			return $this->helper->error($this->user->lang['API_NO_PERMISSION']);
		}

		try
		{
			if (!$this->config['allow_api'])
			{
				throw new api_exception('The API is not enabled on this board', 500);
			}

			$url = $this->helper->route('api_auth_authorize_controller');

			$this->template->assign_vars(array(
				'S_AUTH_ACTION' => $url,
				'T_EXCHANGE_KEY' => $exchange_key,
			));

			add_form_key('api_auth');

			return $this->helper->render('api_auth.html', $this->user->lang['AUTH_TITLE']);
		}
		catch (api_exception $e)
		{
			return $this->helper->error($this->user->lang['API_NOT_ENABLED']);
		}
	}

	/**
	 * The post endpoint after the form in authorize_application() has been submitted.
	 * This either authorizes the application or not, depending on the choice the user made
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A information page that the application has been authorized
	 * or a redirect to the homepage if the user presses cancel
	 */
	public function authorize_application_post()
	{
		if (!$this->auth->acl_get('u_api'))
		{
			return $this->helper->error($this->user->lang['API_NO_PERMISSION']);
		}

		try {
			if (!$this->config['allow_api'])
			{
				throw new api_exception('The API is not enabled on this board', 500);
			}

			if (!check_form_key('api_auth'))
			{
				return $this->helper->error($this->user->lang['AUTH_FORM_ERROR']);
			}

			$cancel = $this->request->variable('cancel', '');
			if (!empty($cancel))
			{
				redirect(generate_board_url());
			}

			$exchange_key = $this->request->variable('exchange_key', '');
			$app_name = $this->request->variable('appname', '');

			if (empty($app_name))
			{
				$url = $this->helper->route('api/auth', array(
					'exchange_key' => $exchange_key,
				));
				return $this->helper->error($this->user->lang['AUTH_MISSING_NAME'] . ' <a href="' . $url . '">' .
					$this->user->lang['AUTH_RETURN'] . '</a>');
			}

			if (strlen($exchange_key) != 16)
			{
				return $this->helper->error($this->user->lang['AUTH_KEY_ERROR']);
			}

			$this->auth_repository->authorize($exchange_key, $this->user->data['user_id'], $app_name);

			$this->template->assign_vars(array(
				'MESSAGE_TEXT'	=> $this->user->lang['AUTH_ALLOWED']  . '<br /><br />
					 <a href="' . generate_board_url() . '">' . $this->user->lang['RETURN_TO_INDEX'] . '</a>',
				'MESSAGE_TITLE'	=> $this->user->lang['INFORMATION'],
			));

			return $this->helper->render('message_body.html', $this->user->lang['INFORMATION']);
		}
		catch (api_exception $e)
		{
			return $this->helper->error($this->user->lang['API_NOT_ENABLED']);
		}
	}

	/**
	 * Exchanges an exchange_key for the authentication key and sign key if the user has authorized the application
	 * Otherwise return an error if the user hasn't
	 *
	 * @param string $exchange_key The exchange key that was obtained in generate_keys()
	 *
	 * @return JsonResponse Either a Json object containing status, auth key and sign key on success, or
	 * a Json object containing status and the error on error
	 */
	public function exchange_key($exchange_key)
	{
		try
		{
			$keys = $this->auth_repository->exchange_key($exchange_key);

			$response = array(
				'status' => 200,
				'data' => $keys,
			);

		}
		catch (invalid_key_exception $e)
		{
			$response = array(
				'status' => $e->getCode(),
				'data' => array(
					'error' => $e->getMessage(),
				),
			);
		}

		return new JsonResponse($this->serializer->serialize($response, 'json'), $response['status']);
	}

	/**
	 * Endpoint to let an application verify if it has valid auth data and has access to the board
	 *
	 * @return JsonResponse A Json object containing information wether or not the user is logged in (member)
	 * or a guest, or an error if the API is disabled/not allowed
	 */
	public function verify()
	{
		$request = $this->request->server("PATH_INFO");
		$auth_key = $this->request->variable('auth_key', ANONYMOUS);
		$serial = $this->request->variable('serial', -1);
		$hash = $this->request->variable('hash', '');

		try
		{
			$user_id = $this->auth_repository->authenticate($request, $auth_key, $serial, $hash);
			if ($user_id == ANONYMOUS)
			{
				$response = array(
					'status' => 200,
					'data' => array(
						'valid'	=> true,
						'usertype' => 'guest',
					),
				);
			}
			else
			{
				$response = array(
					'status' => 200,
					'data' => array(
						'valid'	=> true,
						'usertype' => 'member',
					),
				);
			}
		}
		catch (api_exception $e)
		{
			$response = array(
				'status' => $e->getCode(),
				'data' => array(
					'error' => $e->getMessage(),
					'valid' => false,
				),
			);
		}

		return new JsonResponse($this->serializer->serialize($response, 'json'), $response['status']);
	}
}
