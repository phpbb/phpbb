<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb;

use Symfony\Component\HttpFoundation\Request;

class symfony_request extends Request
{
	/**
	* Constructor
	*
	* @param phpbb\request\request_interface $phpbb_request
	*/
	public function __construct(\phpbb\request\request_interface $phpbb_request)
	{
		// This function is meant to sanitize the global input arrays
		$sanitizer = function(&$value, $key) {
			$type_cast_helper = new \phpbb\request\type_cast_helper();
			$type_cast_helper->set_var($value, $value, gettype($value), true);
		};

		$get_parameters = $phpbb_request->get_super_global(\phpbb\request\request_interface::GET);
		$post_parameters = $phpbb_request->get_super_global(\phpbb\request\request_interface::POST);
		$server_parameters = $phpbb_request->get_super_global(\phpbb\request\request_interface::SERVER);
		$files_parameters = $phpbb_request->get_super_global(\phpbb\request\request_interface::FILES);
		$cookie_parameters = $phpbb_request->get_super_global(\phpbb\request\request_interface::COOKIE);

		array_walk_recursive($get_parameters, $sanitizer);
		array_walk_recursive($post_parameters, $sanitizer);

		parent::__construct($get_parameters, $post_parameters, array(), $cookie_parameters, $files_parameters, $server_parameters);
	}
}
