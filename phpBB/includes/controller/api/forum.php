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
	 * Constructor
	 *
	 * @param phpbb_model_repository_forum $forum_repository
	 */
	function __construct(phpbb_model_repository_forum $forum_repository)
	{
		$this->forum_repository = $forum_repository;
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
		$forums = $this->forum_repository->get($forum_id);

		$serializer = new Serializer(array(
			new phpbb_model_normalizer_api_response(),
			new phpbb_model_normalizer_forum(),
		), array(new JsonEncoder()));

		$response = new phpbb_model_entity_api_response(array(
			'status' => 200,
			'data' => $serializer->normalize($forums),
		));

		$json = $serializer->serialize($response, 'json');

		return new Response($json, $response->get('status'));
	}

}
