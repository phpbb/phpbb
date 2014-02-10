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
class topic
{
	/**
	 * API Model
	 * @var \phpbb\model\repository\topic
	 */
	protected $topic_repository;

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
	 * Constructor
	 *
	 * @param \phpbb\model\repository\topic $topic_repository
	 * @param \phpbb\model\repository\post $post_repository
	 * @param \phpbb\model\repository\auth $auth_repository
	 */
	function __construct(\phpbb\model\repository\topic $topic_repository, \phpbb\model\repository\post $post_repository,
						 \phpbb\model\repository\auth $auth_repository)
	{
		$this->topic_repository = $topic_repository;
		$this->post_repository = $post_repository;
		$this->auth_repository = $auth_repository;
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

		$serializer = new Serializer(array(
			new \phpbb\model\normalizer\post(),
		), array(new JsonEncoder()));

		try {
			$user_id = $this->auth_repository->auth();

			$posts = $this->topic_repository->get($topic_id, $page, $user_id);

			$response = array(
				'status' => 200,
				'total' => $posts['total'],
				'per_page' => $posts['per_page'],
				'page' => $posts['page'],
				'last_page' => $posts['last_page'],
				'data' => $serializer->normalize($posts['posts']),
			);
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

		return new Response($json, $response['status']);
	}

}
