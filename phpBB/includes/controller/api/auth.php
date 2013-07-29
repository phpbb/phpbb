<?php
/**
 *
 * @package controller
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Controller for authentication
 * @package phpBB3
 */
class phpbb_controller_api_auth
{
	/**
	 * API Model
	 * @var phpbb_model_repository_auth
	 */
	protected $auth_repository;

	/**
	 * User object
	 * @var phpbb_user
	 */
	protected $user;

	/**
	 * Controller helper object
	 * @var phpbb_controller_helper
	 */
	protected $helper;

	/**
	 * Template object
	 * @var phpbb_template
	 */
	protected $template;

	/**
	 * Template object
	 * @var phpbb_request
	 */
	protected $request;

	/**
	 * Config object
	 * @var phpbb_config_db
	 */
	protected $config;

	/**
	 * Constructor
	 *
	 * @param phpbb_model_repository_auth $auth_repository
	 * @param phpbb_user $user
	 * @param phpbb_controller_helper $helper
	 * @param phpbb_template $template
	 * @param phpbb_request $request
	 * @param phpbb_config_db $config
	 */
	function __construct(phpbb_model_repository_auth $auth_repository, phpbb_user $user, phpbb_controller_helper $helper, phpbb_template $template, phpbb_request $request, phpbb_config_db $config)
	{
		$this->auth_repository = $auth_repository;
		$this->user = $user;
		$this->helper = $helper;
		$this->template = $template;
		$this->request = $request;
		$this->config = $config;

		$this->user->add_lang('api');
	}

	public function generate_keys()
	{
		$serializer = new Serializer(array(new phpbb_model_normalizer_api_response()), array(new JsonEncoder()));
		if (!$this->config['allow_api'])
		{
			$response = new phpbb_model_entity_api_response(array(
				'status' => 500,
				'data' => 'The API is not enabled on this board',
			));
			return new Response($serializer->serialize($response, 'json'), $response->get('status'));
		}

		$keys = array(
			'auth_key' => $this->auth_repository->generate_key(),
			'sign_key' => $this->auth_repository->generate_key(),
		);

		$response = new phpbb_model_entity_api_response(array(
			'status' => 200,
			'data' => $keys,
		));


		return new Response($serializer->serialize($response, 'json'), $response->get('status'));
	}

	public function auth($auth_key, $sign_key)
	{
		if (!$this->config['allow_api'])
		{
			return $this->helper->error($this->user->lang('API_NOT_ENABLED'));
		}

		$this->template->assign_vars(array(
			'S_AUTH_ACTION' => 'app.php?controller=api/auth/allow',
			'T_AUTH_KEY' => $auth_key,
			'T_SIGN_KEY' => $sign_key,
		));

		add_form_key('api_auth');

		return $this->helper->render('api_auth.html', $this->user->lang['AUTH_TITLE']);
	}

	public function allow()
	{
		if (!$this->config['allow_api'])
		{
			return $this->helper->error($this->user->lang('API_NOT_ENABLED'));
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

		$auth_key = $this->request->variable('auth_key', '');
		$sign_key = $this->request->variable('sign_key', '');
		$appname = $this->request->variable('appname', '');

		if (empty($appname))
		{
			return $this->helper->error($this->user->lang['AUTH_MISSING_NAME'] . ' <a href="app.php?controller=api/auth/' . $auth_key . '/' . $sign_key .'">' .
			$this->user->lang['AUTH_RETURN'] . '</a>');
		}

		if (strlen($auth_key) != 32 || strlen($sign_key) != 32)
		{
			return $this->helper->error($this->user->lang['AUTH_KEY_ERROR']);
		}

		$this->auth_repository->allow($auth_key, $sign_key, $this->user->data['user_id'], $appname);
		return new Response();

	}

	public function verify($auth_key, $serial, $hash)
	{
		$serializer = new Serializer(array(new phpbb_model_normalizer_api_response()), array(new JsonEncoder()));
		if (!$this->config['allow_api'])
		{
			$response = new phpbb_model_entity_api_response(array(
				'status' => 500,
				'data' => 'The API is not enabled on this board',
			));
			return new Response($serializer->serialize($response, 'json'), $response->get('status'));
		}

		$is_verified = $this->auth_repository->verify($auth_key, $serial, $hash);

		$response = new phpbb_model_entity_api_response(array(
			'status' => 200,
			'data' => array(
				'valid'	=> $is_verified,
			),
		));

		return new Response($serializer->serialize($response, 'json'), $response->get('status'));
	}
}
