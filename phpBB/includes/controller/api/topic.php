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
	 * Constructor
	 *
	 * @param phpbb_model_repository_topic $topic_repository
	 * @param phpbb_config_db $config
	 */
	function __construct(phpbb_model_repository_topic $topic_repository, phpbb_config_db $config)
	{
		$this->topic_repository = $topic_repository;
		$this->config = $config;
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
		$serializer = new Serializer(array(
			new phpbb_model_normalizer_api_response(),
			new phpbb_model_normalizer_topic(),
		), array(new JsonEncoder()));

		if (!$this->config['allow_api'])
		{
			$response = new phpbb_model_entity_api_response(array(
				'status' => 500,
				'data' => 'The API is not enabled on this board',
			));
			return new Response($serializer->serialize($response, 'json'), $response->get('status'));
		}
		$topics = $this->topic_repository->get($forum_id, $page);

		$response = new phpbb_model_entity_api_response(array(
			'status' => 200,
			'data' => $serializer->normalize($topics),
		));

		$json = $serializer->serialize($response, 'json');

		return new Response($json, $response->get('status'));
	}

}
