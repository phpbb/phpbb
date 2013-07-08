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
     * @var phpbb_model_api_topic
     */
    protected $model;

    /**
     * Constructor
     *
     * @param phpbb_model_api_topic $model
     */
    function __construct(phpbb_model_api_topic $model)
    {
        $this->model = $model;
    }

    /**
     * Controller method to return a list of topics in a given forum
     *
     * Accesible trough /api/topics/{forum_id}/{page} (no {page} defaults to 1)
     * Method: GET
     *
     * @param int $forum_id the forum to retrieve topics from
     * @param int $page the page to get
     * @return Response an array of topics, serialized to json
     */
    public function topics($forum_id, $page)
    {
        $topics = $this->model->get($forum_id, $page);

        $serializer = new Serializer(array(new phpbb_model_normalizer_topic()), array(new JsonEncoder()));
        $json = $serializer->serialize($topics, 'json');

        return new Response($json);
    }

}
