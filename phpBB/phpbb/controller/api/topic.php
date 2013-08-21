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
class phpbb_controller_api_topic
{
	/**
	 * API Model
	 * @var phpbb_model_repository_topic
	 */
	protected $topic_repository;

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
	 * @param phpbb_model_repository_topic $topic_repository
	 * @param phpbb_model_repository_auth $auth_repository
	 * @param phpbb_request $request
	 * @param phpbb_config $config
	 * @param $phpbb_root_path
	 * @param $php_ext
	 */
	function __construct(phpbb_model_repository_topic $topic_repository,  phpbb_model_repository_auth $auth_repository,
						 phpbb_request $request, phpbb_config $config, $phpbb_root_path, $php_ext)
	{
		$this->topic_repository = $topic_repository;
		$this->auth_repository = $auth_repository;
		$this->request = $request;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Controller method to return a list of posts in a given topic
	 *
	 * Accesible trough /api/topic/{topic_id}/{page} (no {page} defaults to 1)
	 * Method: GET
	 *
	 * @param $topic_id
	 * @param int $page the page to get
	 * @return Response an array of posts, serialized to json
	 */
	public function topic($topic_id, $page)
	{
		$auth_key = $this->request->variable('auth_key', 'guest');
		$serial = $this->request->variable('serial', -1);
		$hash = $this->request->variable('hash', '');

		$user_id = $this->auth_repository->auth($this->request->variable('controller', 'api/topic/' .
			$topic_id . '/' . $page), $auth_key, $serial, $hash);

		$serializer = new Serializer(array(
			new phpbb_model_normalizer_post(),
		), array(new JsonEncoder()));

		if (is_int($user_id))
		{
			$posts = $this->topic_repository->get($topic_id, $page, $user_id);

			if ($posts !== false)
			{
				$response = array(
					'status' => 200,
					'data' => $serializer->normalize($posts),
				);
			}
			else
			{
				$response = array(
					'status' => 403,
					'data' => array(
						'error' => 'User has no permission to see this forum.',
						'valid' => false,
					),
				);
			}
		}
		else
		{
			$response = $user_id;
		}

		$json = $serializer->serialize($response, 'json');

		return new Response($json, $response['status']);
	}

	public function new_topic()
	{

		$serializer = new Serializer(array(
		), array(new JsonEncoder()));

		$hash = $this->request->variable('hash', '');

		$request = array(
			'auth_key' => $this->request->variable('auth_key', 'guest'),
			'serial' => $this->request->variable('serial', -1),

			'username' => $this->request->variable('username', ''),
			'topic_type' => $this->request->variable('topic_type', POST_NORMAL),

			'forum_id' => $this->request->variable('forum_id', 0),
			'icon_id' => $this->request->variable('icon_id', 0),

			'enable_bbcode' => $this->request->variable('enable_bbcode', true),
			'enable_smilies' => $this->request->variable('enable_smilies', true),
			'enable_urls' => $this->request->variable('enable_urls', true),
			'enable_sig' => $this->request->variable('enable_sig', true),

			'message' => $this->request->variable('message', '', true),

			'topic_title' => $this->request->variable('topic_title', '', true),

			'notify' => $this->request->variable('notify', false),
		);

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

		$user_id = $this->auth_repository->auth($request, $request['auth_key'], $request['serial'], $hash);

		if (is_int($user_id))
		{
			$response = $this->topic_repository->new_topic($request, $user_id);
		}
		else
		{
			$response = $user_id;
		}

		$json = $serializer->serialize($response, 'json');
		return new Response($json, 200);
	}

}
