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
	 * Constructor
	 *
	 * @param phpbb_model_repository_auth $auth_repository
	 * @param phpbb_user $user
	 * @param phpbb_controller_helper $helper
	 * @param phpbb_template $template
	 * @param phpbb_request $request
	 */
	function __construct(phpbb_model_repository_auth $auth_repository, phpbb_user $user, phpbb_controller_helper $helper, phpbb_template $template, phpbb_request $request)
	{
		$this->auth_repository = $auth_repository;
		$this->user = $user;
		$this->helper = $helper;
		$this->template = $template;
		$this->request = $request;

		$this->user->add_lang('api');
	}

	public function generate_token()
	{
		$tokens = array(
			'auth_token' => $this->auth_repository->generate_token(),
			'sign_token' => $this->auth_repository->generate_token(),
		);

		$response = new phpbb_model_entity_api_response(array(
			'status' => 200,
			'data' => $tokens,
		));

		$serializer = new Serializer(array(new phpbb_model_normalizer_api_response()), array(new JsonEncoder()));
		return new Response($serializer->serialize($response, 'json'), $response->get('status'));
	}

	public function auth($token, $sign_token)
	{
		$this->template->assign_vars(array(
			'S_AUTH_ACTION' => 'app.php?controller=api/auth/allow',
			'T_TOKEN' => $token,
			'T_SIGN_TOKEN' => $sign_token,
		));

		add_form_key('api_auth');

		return $this->helper->render('api_auth.html', $this->user->lang['AUTH_TITLE']);
	}

	public function allow()
	{
		if (!check_form_key('api_auth'))
		{
			return $this->helper->error($this->user->lang['AUTH_FORM_ERROR']);
		}

		$cancel = $this->request->variable('cancel', '');
		if (!empty($cancel))
		{
			redirect(generate_board_url());
		}

		$token = $this->request->variable('token', '');
		$sign_token = $this->request->variable('sign_token', '');
		$appname = $this->request->variable('appname', '');

		if (empty($appname))
		{
			return $this->helper->error($this->user->lang['AUTH_MISSING_NAME'] . ' <a href="app.php?controller=api/auth/' . $token . '/' . $sign_token .'">' .
			$this->user->lang['AUTH_RETURN'] . '</a>');
		}

		if (strlen($token) != 32 || strlen($sign_token) != 32)
		{
			return $this->helper->error($this->user->lang['AUTH_TOKEN_ERROR']);
		}

		$this->auth_repository->allow($token, $sign_token, $this->user->data['user_id'], $appname);
		return new Response();

	}

	public function verify($token, $timestamp, $hash)
	{
		$is_verified = $this->auth_repository->verify($token, $timestamp, $hash);

		$response = new phpbb_model_entity_api_response(array(
			'status' => 200,
			'data' => array(
				'valid'	=> $is_verified,
			),
		));

		$serializer = new Serializer(array(new phpbb_model_normalizer_api_response()), array(new JsonEncoder()));
		return new Response($serializer->serialize($response, 'json'), $response->get('status'));
	}
}
