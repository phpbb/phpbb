<?php
/**
 *
 * @package controller
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace phpbb\controller\api;

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
use phpbb\model\exception\api_exception;

/**
 * Controller for authentication
 * @package phpBB3
 */
class auth
{
	/**
	 * API Model
	 * @var \phpbb\model\repository\auth
	 */
	protected $auth_repository;

	/**
	 * User object
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * Controller helper object
	 * @var \phpbb\controller\helper
	 */
	protected $helper;

	/**
	 * Template object
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * Template object
	 * @var \phpbb\request\request
	 */
	protected $request;

	/**
	 * Config object
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * Auth object
	 * @var \phpbb\model\repository\auth
	 */
	protected $auth;

	/**
	 * Constructor
	 *
	 * @param \phpbb\model\repository\auth $auth_repository
	 * @param \phpbb\user $user
	 * @param \phpbb\controller\helper $helper
	 * @param \phpbb\template\template $template
	 * @param \phpbb\request\request $request
	 * @param \phpbb\config\config $config
	 * @param \phpbb\auth\auth $auth
	 */
	function __construct(\phpbb\model\repository\auth $auth_repository, \phpbb\user $user,
						 \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\request\request $request,
						 \phpbb\config\config $config, \phpbb\auth\auth $auth)
	{
		$this->auth_repository = $auth_repository;
		$this->user = $user;
		$this->helper = $helper;
		$this->template = $template;
		$this->request = $request;
		$this->config = $config;
		$this->auth = $auth;

		$this->user->add_lang('api');
	}

	public function generate_keys()
	{
		$serializer = new Serializer(array(), array(new JsonEncoder()));

		$keys = array(
			'auth_key' => unique_id(),
			'sign_key' => unique_id(),
		);

		$response = array(
			'status' => 200,
			'data' => $keys,
			);

		return new Response($serializer->serialize($response, 'json'), $response['status']);
	}

	public function auth($auth_key, $sign_key)
	{
		if (!$this->auth->acl_get('u_api'))
		{
			return $this->helper->error($this->user->lang('API_NO_PERMISSION'));
		}

		try {

			if (!$this->config['allow_api'])
			{
				throw new api_exception('The API is not enabled on this board', 500);
			}

			$url = $this->helper->route('api_auth_allow_controller');

			$this->template->assign_vars(array(
				'S_AUTH_ACTION' => $url,
				'T_AUTH_KEY' => $auth_key,
				'T_SIGN_KEY' => $sign_key,
			));

			add_form_key('api_auth');

			return $this->helper->render('api_auth.html', $this->user->lang['AUTH_TITLE']);
		}
		catch (api_exception $e)
		{
			return $this->helper->error($this->user->lang('API_NOT_ENABLED'));
		}
	}

	public function allow()
	{
		if (!$this->auth->acl_get('u_api'))
		{
			return $this->helper->error($this->user->lang('API_NO_PERMISSION'));
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

			$auth_key = $this->request->variable('auth_key', '');
			$sign_key = $this->request->variable('sign_key', '');
			$appname = $this->request->variable('appname', '');

			if (empty($appname))
			{
				$url = $this->helper->route('api/auth', array(
					'auth_key' => $auth_key,
					'sign_key' => $sign_key,
				));
				return $this->helper->error($this->user->lang['AUTH_MISSING_NAME'] . ' <a href="' . $url . '">' .
					$this->user->lang['AUTH_RETURN'] . '</a>');
			}

			if (strlen($auth_key) != 16 || strlen($sign_key) != 16)
			{
				return $this->helper->error($this->user->lang['AUTH_KEY_ERROR']);
			}

			$this->auth_repository->allow($auth_key, $sign_key, $this->user->data['user_id'], $appname);

			$this->template->assign_vars(array(
				'MESSAGE_TEXT'	=> $this->user->lang['AUTH_ALLOWED']  . '<br /><br />' . sprintf($this->user->lang['RETURN_TO_INDEX'], '<a href="' . generate_board_url() . '">', '</a>'),
				'MESSAGE_TITLE'	=> $this->user->lang('INFORMATION'),
			));

			return $this->helper->render('message_body.html', $this->user->lang('INFORMATION'));
		}
		catch (api_exception $e)
		{
			return $this->helper->error($this->user->lang('API_NOT_ENABLED'));
		}
	}

	public function verify()
	{
		$serializer = new Serializer(array(), array(new JsonEncoder()));

		try {
			$user_id = $this->auth_repository->auth();
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

		return new Response($serializer->serialize($response, 'json'), $response['status']);
	}
}
