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

$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);


use Symfony\Component\HttpFoundation\Response,
	Symfony\Component\Serializer\Serializer,
	Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Controller for the api of a phpBB forum
 * @package phpBB3
 */
class phpbb_controller_api_forum
{

	/**
	 * API Model
	 * @var phpbb_model_api_forum
	 */
	protected $model;

	/**
	 * Constructor
	 *
	 * @param phpbb_db_driver $db
	 */
	function __construct(phpbb_model_api_forum $model)
	{
		$this->model = $model;
	}

	/**
	 * Controller method to return a list of forums
	 *
	 * Accesible trough /api/forums/{forum_id} (no {forum_id} defaults to 0)
	 * Method: GET
	 *
	 * @param $forum_id The forum to fetch, 0 fetches everything
	 * @return Response an array of forums, jsonencoded
	 */
	public function forums($forum_id)
	{
		$forums = $this->model->get($forum_id);

		$serializer = new Serializer(array(new phpbb_model_normalizer_forum()), array(new JsonEncoder()));
		$json = $serializer->serialize($forums, 'json');

		return new Response($json);
	}

}
