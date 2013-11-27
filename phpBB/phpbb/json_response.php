<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace phpbb;

/**
* JSON class
* @package phpBB3
*/
class json_response
{
	/**
	 * Send the data to the client and exit the script.
	 *
	 * @param array $data Any additional data to send.
	 * @param bool $exit Will exit the script if true.
	 */
	public function send($data, $exit = true)
	{
		header('Content-Type: application/json');
		echo json_encode($data);

		if ($exit)
		{
			garbage_collection();
			exit_handler();
		}
	}
}
