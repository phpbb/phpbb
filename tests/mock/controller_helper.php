<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_mock_controller_helper extends \phpbb\controller\helper
{
	public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\config\config $config, \phpbb\controller\provider $provider, \phpbb\extension\manager $manager, $phpbb_root_path, $php_ext, $phpbb_root_path_ext)
	{
		$this->template = $template;
		$this->user = $user;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$provider->set_ext_finder($manager->get_finder());
		$this->route_collection = $provider->find($phpbb_root_path_ext)->get_routes();
	}
}
