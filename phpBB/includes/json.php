<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* JSON class
* @package phpBB3
*/
class JSON
{
	private static $data = array();

	/**
	 * Send the data to the client and exit the script.
	 *
	 * @param array $data Any additional data to send.
	 * @param bool $exit Will exit the script if true.
	 */
	public static function send($data = false, $exit = true)
	{
		if ($data)
		{
			self::add($data);
		}

		header('Content-type: application/json');
		echo json_encode(self::$data);

		if ($exit)
		{
			garbage_collection();
			exit_handler();
		}
	}

	/**
	 * Saves some data to be written when JSON::send() is called.
	 *
	 * @param array $data Data to save to be sent.
	 */
	public static function add($data)
	{
		self::$data = array_merge(self::$data, $data);
	}
}
