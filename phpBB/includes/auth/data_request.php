<?php
/**
*
* @package auth
* @copyright (c) 2012 phpBB Group
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

/**
* Holds data readily identifiable as a
*
* @package auth
*/
class phpbb_auth_data_request
{
	/**
	 * Populates the phpbb_auth_data_request with information about the request.
	 *
	 * @param phpbb_user $user
	 * @param array $args
	 * @throws InvalidArgumentException
	 */
	public function __construct(phpbb_user $user, $args)
	{
		if (!is_array($args))
		{
			throw new InvalidArgumentException('INVALID: ' . $args);
		}
		foreach ($args as $data_type => $data_error)
		{
			$this->$data_type = $data_type;
			$data_error_name = $data_type . '_ERROR';
			$this->$data_error_name = (empty($user->lang[$data_error])) ? $user->lang[$data_error . '_' . $data_type] : $user->lang[$data_error];;
		}
	}
}
