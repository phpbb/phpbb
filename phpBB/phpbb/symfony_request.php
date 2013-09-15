<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

use Symfony\Component\HttpFoundation\Request;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_symfony_request extends Request
{
	/**
	* Constructor
	*
	* @param phpbb_request_interface $phpbb_request
	*/
	public function __construct(phpbb_request_interface $phpbb_request)
	{
		// This function is meant to sanitize the global input arrays
		$sanitizer = function(&$value, $key) {
			$type_cast_helper = new phpbb_request_type_cast_helper();
			$type_cast_helper->set_var($value, $value, gettype($value), true);
		};

		$get_parameters = $phpbb_request->get_super_global(phpbb_request_interface::GET);
		$post_parameters = $phpbb_request->get_super_global(phpbb_request_interface::POST);
		$server_parameters = $phpbb_request->get_super_global(phpbb_request_interface::SERVER);
		$files_parameters = $phpbb_request->get_super_global(phpbb_request_interface::FILES);
		$cookie_parameters = $phpbb_request->get_super_global(phpbb_request_interface::COOKIE);

		array_walk_recursive($get_parameters, $sanitizer);
		array_walk_recursive($post_parameters, $sanitizer);

		parent::__construct($get_parameters, $post_parameters, array(), $cookie_parameters, $files_parameters, $server_parameters);
	}
}
