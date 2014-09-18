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
require_once dirname(__FILE__) . '/helper_route_test.php';

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class phpbb_controller_helper_route_adm_test extends phpbb_controller_helper_route_test
{
	public function setUp()
	{
		global $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		parent::setUp();

		$request = new phpbb_mock_request();
		$request->overwrite('SCRIPT_NAME', '/adm/index.php', \phpbb\request\request_interface::SERVER);
		$request->overwrite('SCRIPT_FILENAME', 'index.php', \phpbb\request\request_interface::SERVER);
		$request->overwrite('REQUEST_URI', '/adm/index.php', \phpbb\request\request_interface::SERVER);
		$request->overwrite('SERVER_NAME', 'localhost', \phpbb\request\request_interface::SERVER);
		$request->overwrite('SERVER_PORT', '80', \phpbb\request\request_interface::SERVER);

		$this->symfony_request = new \phpbb\symfony_request(
			$request
		);
		$this->filesystem = new \phpbb\filesystem();
		$phpbb_path_helper = new \phpbb\path_helper(
			$this->symfony_request,
			$this->filesystem,
			$this->getMock('\phpbb\request\request'),
			$phpbb_root_path,
			$phpEx
		);

		$finder = new \phpbb\finder(
			new \phpbb\filesystem(),
			dirname(__FILE__) . '/',
			new phpbb_mock_cache()
		);
		$finder->set_extensions(array_keys($this->extension_manager->all_enabled()));
		$this->provider = new \phpbb\controller\provider();
		$this->provider->find_routing_files($finder);
		$this->provider->find(dirname(__FILE__) . '/');
		// Set correct current phpBB root path
		$this->root_path = './../';
	}
}
