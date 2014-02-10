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

use phpbb\model\exception\api_exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Controller for the api of a topic
 * @package phpBB3
 */
class post
{

	/**
	 * API Model
	 * @var \phpbb\model\repository\post
	 */
	protected $post_repository;

	/**
	 * Auth repository object
	 * @var \phpbb\model\repository\auth
	 */
	protected $auth_repository;

	/**
	 * Request object
	 * @var \phpbb\request\request
	 */
	protected $request;

	/**
	 * Config object
	 * @var \phpbb\config\config
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
	 * @param \phpbb\model\repository\post $post_repository
	 * @param \phpbb\model\repository\auth $auth_repository
	 * @param \phpbb\request\request $request
	 * @param \phpbb\config\config $config
	 * @param $phpbb_root_path
	 * @param $php_ext
	 */
	function __construct(\phpbb\model\repository\post $post_repository,
						 \phpbb\model\repository\auth $auth_repository, \phpbb\request\request $request, \phpbb\config\config $config,
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
			$user_id = $this->auth_repository->auth(0, $request, $request['auth_key'], $request['serial'], $hash);
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

		$json = $serializer->serialize($response, 'json');
		return new Response($json, 200);
	}

}
