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
class phpbb_controller_api_forum
{
	/**
	 * API Model
	 * @var phpbb_model_repository_forum
	 */
	protected $forum_repository;

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
	 * @param phpbb_model_repository_forum $forum_repository
	 * @param phpbb_config_db $config
	 * @param phpbb_model_repository_auth $auth_repository
	 * @param phpbb_request $request
	 */
	function __construct(phpbb_model_repository_forum $forum_repository, phpbb_config_db $config,
						 phpbb_model_repository_auth $auth_repository, phpbb_request $request)
	{
		$this->forum_repository = $forum_repository;
		$this->config = $config;
		$this->auth_repository = $auth_repository;
		$this->request = $request;
	}

	/**
	 * Controller method to return a list of forums
	 *
	 * Accessible trough /api/forums/{forum_id} (no {forum_id} defaults to 0)
	 * Method: GET
	 *
	 * @param int $forum_id The forum to fetch, 0 fetches everything
	 * @return Response an array of forums, serialized to json
	 */
	public function forums($forum_id)
	{
		$serializer = new Serializer(array(
			new phpbb_model_normalizer_forum(),
		), array(new JsonEncoder()));

		$auth_key = $this->request->variable('auth_key', 'guest');
		$serial = $this->request->variable('serial', -1);
		$hash = $this->request->variable('hash', '');

		$user_id = $this->auth_repository->auth($this->request->variable('controller', 'api/forums/' .
			$forum_id), $auth_key, $serial, $hash, $forum_id);

		if (is_int($user_id))
		{
			$forums = $this->forum_repository->get($forum_id, $user_id);

			$response = array(
				'status' => 200,
				'data' => $serializer->normalize($forums),
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
