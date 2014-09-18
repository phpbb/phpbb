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

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher;
		$this->user = new \phpbb\user('\phpbb\datetime');

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
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '0'));
		$this->template = new phpbb\template\twig\twig($phpbb_path_helper, $this->config, $this->user, new \phpbb\template\context());
		$this->extension_manager = new phpbb_mock_extension_manager(
			dirname(__FILE__) . '/',
			array(
				'vendor2/foo' => array(
					'ext_name' => 'vendor2/foo',
					'ext_active' => '1',
					'ext_path' => 'ext/vendor2/foo/',
				),
			)
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
	}

	/**
	* @dataProvider helper_url_data_no_rewrite()
	*/
	public function test_helper_url_no_rewrite($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->provider, $this->extension_manager, $this->symfony_request, $this->filesystem, './../', 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id));
	}

	/**
	* @dataProvider helper_url_data_with_rewrite()
	*/
	public function test_helper_url_with_rewrite($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '1'));
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->provider, $this->extension_manager, $this->symfony_request, $this->filesystem, './../', 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id));
	}

	/**
	* @dataProvider helper_url_data_absolute()
	*/
	public function test_helper_url_absolute($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '0'));
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->provider, $this->extension_manager, $this->symfony_request, $this->filesystem, './../', 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id, UrlGeneratorInterface::ABSOLUTE_URL));
	}

	/**
	* @dataProvider helper_url_data_relative_path()
	*/
	public function test_helper_url_relative_path($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '0'));
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->provider, $this->extension_manager, $this->symfony_request, $this->filesystem, './../', 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id, UrlGeneratorInterface::RELATIVE_PATH));
	}

	/**
	* @dataProvider helper_url_data_network()
	*/
	public function test_helper_url_network($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '0'));
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->provider, $this->extension_manager, $this->symfony_request, $this->filesystem, './../', 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id, UrlGeneratorInterface::NETWORK_PATH));
	}

	/**
	 * @dataProvider helper_url_data_absolute_with_rewrite()
	 */
	public function test_helper_url_absolute_with_rewrite($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '1'));
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->provider, $this->extension_manager, $this->symfony_request, $this->filesystem, './../', 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id, UrlGeneratorInterface::ABSOLUTE_URL));
	}

	/**
	 * @dataProvider helper_url_data_relative_path_with_rewrite()
	 */
	public function test_helper_url_relative_path_with_rewrite($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '1'));
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->provider, $this->extension_manager, $this->symfony_request, $this->filesystem, './../', 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id, UrlGeneratorInterface::RELATIVE_PATH));
	}

	/**
	 * @dataProvider helper_url_data_network_with_rewrite()
	 */
	public function test_helper_url_network_with_rewrite($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '1'));
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->provider, $this->extension_manager, $this->symfony_request, $this->filesystem, './../', 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id, UrlGeneratorInterface::NETWORK_PATH));
	}
}
