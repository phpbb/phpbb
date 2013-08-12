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
 * Controller for the api of a phpBB forum
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
	 * Config object
	 * @var phpbb_config_db
	 */
	protected $config;

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
	 * @param phpbb_config_db $config
	 * @param phpbb_model_repository_auth $auth_repository
	 * @param phpbb_request $request
	 */
	function __construct(phpbb_model_repository_topic $topic_repository, phpbb_config_db $config,
						 phpbb_model_repository_auth $auth_repository, phpbb_request $request)
	{
		$this->topic_repository = $topic_repository;
		$this->config = $config;
		$this->request = $request;
		$this->auth_repository = $auth_repository;
	}

	/**
	 * Controller method to return a list of topics in a given forum
	 *
	 * Accesible trough /api/forums/{forum_id}/topics/{page} (no {page} defaults to 1)
	 * Method: GET
	 *
	 * @param int $forum_id the forum to retrieve topics from
	 * @param int $page the page to get
	 * @return Response an array of topics, serialized to json
	 */
	public function topics($forum_id, $page)
	{


		$auth_key = $this->request->variable('auth_key', 'guest');
		$serial = $this->request->variable('serial', -1);
		$hash = $this->request->variable('hash', '');

		$user_id = $this->auth_repository->auth($this->request->variable('controller', 'api/forums/' .
			$forum_id . '/' . $page), $auth_key, $serial, $hash, $forum_id);

		$serializer = new Serializer(array(
			new phpbb_model_normalizer_topic(),
		), array(new JsonEncoder()));

		if (is_int($user_id))
		{
			$topics = $this->topic_repository->get($forum_id, $page);

			$response = array(
				'status' => 200,
				'data' => $serializer->normalize($topics),
			);
		}
		else
		{
			$response = $user_id;
		}

		$json = $serializer->serialize($response, 'json');

		return new Response($json, $response['status']);
	}

}
