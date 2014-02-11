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
 * Controller for returning the API version
 * @package phpBB3
 */
class version
{

	/**
	 * Config object
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config $config
	 */
	function __construct(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	public function version()
	{
		$serializer = new Serializer(array(), array(new JsonEncoder()));

		try {
			if (!$this->config['allow_api'])
			{
				throw new api_exception('The API is not enabled on this board', 500);
			}

			$response = array(
				'status' => 200,
				'version' => PHPBB_API_VERSION,
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
