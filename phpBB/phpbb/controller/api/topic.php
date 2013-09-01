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
	 * Constructor
	 *
	 * @param phpbb_model_repository_topic $topic_repository
	 * @param phpbb_model_repository_post $post_repository
	 * @param phpbb_model_repository_auth $auth_repository
	 * @param phpbb_request $request
	 */
	function __construct(phpbb_model_repository_topic $topic_repository, phpbb_model_repository_post $post_repository,
						 phpbb_model_repository_auth $auth_repository, phpbb_request $request)
	{
		$this->topic_repository = $topic_repository;
		$this->post_repository = $post_repository;
		$this->auth_repository = $auth_repository;
		$this->request = $request;
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

}
