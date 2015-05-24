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

class phpbb_mock_controller_helper extends \phpbb\controller\helper
{
	public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\config\config $config, \phpbb\controller\provider $provider, \phpbb\extension\manager $manager, \phpbb\symfony_request $symfony_request, \phpbb\request\request_interface $request, \phpbb\filesystem $filesystem, $phpbb_root_path, $php_ext, $phpbb_root_path_ext)
	{
		$this->template = $template;
		$this->user = $user;
		$this->config = $config;
		$this->symfony_request = $symfony_request;
		$this->request = $request;
		$this->filesystem = $filesystem;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$provider->find_routing_files($manager->get_finder());
		$this->route_collection = $provider->find($phpbb_root_path_ext)->get_routes();
	}

	public function get_current_url()
	{
		return '';
	}
}
