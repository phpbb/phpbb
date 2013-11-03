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
 * Controller for the api of a topic
 * @package phpBB3
 */
class phpbb_controller_api_post
{

	/**
	 * API Model
	 * @var phpbb_model_repository_post
	 */
	protected $post_repository;

	/**
	 * Auth repository object
	 * @var phpbb_model_repository_auth
	 */
	protected $auth_repository;

	/**
	 * Request object
	 * @var phpbb_request
	 */
	protected $request;

	/**
	 * Config object
	 * @var phpbb_config
	 */
	protected $config;


	/**
	 * Root path.
	 *
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * PHP extension.
	 *
	 * @var string
	 */
	protected $php_ext;


	/**
	 * Constructor
	 *
	 * @param phpbb_model_repository_post $post_repository
	 * @param phpbb_model_repository_auth $auth_repository
	 * @param phpbb_request $request
	 * @param phpbb_config $config
	 * @param $phpbb_root_path
	 * @param $php_ext
	 */
	function __construct(phpbb_model_repository_post $post_repository,
						 phpbb_model_repository_auth $auth_repository, phpbb_request $request, phpbb_config $config,
						 $phpbb_root_path, $php_ext)
	{
		$this->post_repository = $post_repository;
		$this->auth_repository = $auth_repository;
		$this->request = $request;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	public function new_post()
	{
		$serializer = new Serializer(array(
		), array(new JsonEncoder()));

		$request = json_decode(html_entity_decode($this->request->variable('data', '')), true);

		$hash = $this->request->variable('hash', '');

		if (!function_exists('validate_string'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}

		$errors = array();

		if ($err = validate_string($request['topic_title'], false, 1, 120))
		{
			$errors['topic_title'] = $err;
		}

		if ($err = validate_string($request['message'], false, $this->config['min_post_chars'], $this->config['max_post_chars']))
		{
			$errors['message'] = $err;
		}

		if (count($errors) > 0)
		{
			$response = array(
				'status' => 400,
				'data' => $errors,
			);
			$json = $serializer->serialize($response, 'json');
			return new Response($json, 200);
		}

		try {
			$user_id = $this->auth_repository->auth($request, $request['auth_key'], $request['serial'], $hash);
			$post = $this->post_repository->new_post($request, $user_id);

			if(is_int($post['post_id']))
			{
				$response = array(
					'status' => 200,
					'data' => array(
						'post_id' => $post['post_id'],
					),
				);
			}
			else
			{
				// @ToDo: Research if posting will ever fail and return no post id
				$response = array(
					'status' => 500,
					'data' => array(
						'error' => 'Something went wrong', // @ToDo: Figure out something better
					),
				);
			}
		}
		catch (phpbb_model_exception_api_exception $e)
		{
			$response = array(
				'status' => $e->getCode(),
				'data' => array(
					'error' => $e->getMessage(),
					'valid' => false,
				),
			);
		}

		$json = $serializer->serialize($response, 'json');
		return new Response($json, 200);
	}

}
