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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/common_helper_route.php';

class phpbb_controller_helper_route_test extends phpbb_controller_common_helper_route
{
	protected function get_phpbb_root_path()
	{
		return '';
	}

	protected function get_uri()
	{
		return '/app.php';
	}

	protected function get_script_name()
	{
		return 'app.php';
	}
}
