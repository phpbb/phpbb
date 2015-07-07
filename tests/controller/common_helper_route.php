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

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class phpbb_controller_common_helper_route extends phpbb_test_case
{
	protected $root_path;

	public function setUp()
	{
		global $phpbb_dispatcher, $phpbb_root_path, $phpEx;

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
		$this->generate_route_objects();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher;
	}

	protected function get_phpbb_root_path()
	{
		return '';
	}

	protected function get_uri()
	{
		return '/app.php';
	}

	protected function get_base_uri()
	{
		return $this->get_uri();
	}

	protected function get_script_name()
	{
		return 'app.php';
	}

	protected function path_to_app()
	{
		return '';
	}

	protected function generate_route_objects()
	{
		$this->request = new phpbb_mock_request();
		$this->request->overwrite('SCRIPT_NAME', $this->get_uri(), \phpbb\request\request_interface::SERVER);
		$this->request->overwrite('SCRIPT_FILENAME', $this->get_script_name(), \phpbb\request\request_interface::SERVER);
		$this->request->overwrite('REQUEST_URI', $this->get_base_uri(), \phpbb\request\request_interface::SERVER);
		$this->request->overwrite('SERVER_NAME', 'localhost', \phpbb\request\request_interface::SERVER);
		$this->request->overwrite('SERVER_PORT', '80', \phpbb\request\request_interface::SERVER);

		$this->symfony_request = new \phpbb\symfony_request(
			$this->request
		);
		$this->filesystem = new \phpbb\filesystem\filesystem();
		$this->phpbb_path_helper = new \phpbb\path_helper(
			$this->symfony_request,
			$this->filesystem,
			$this->request,
			$phpbb_root_path,
			$phpEx
		);

		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '0'));
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$this->user = new \phpbb\user($lang, '\phpbb\datetime');;

		$container = new phpbb_mock_container_builder();
		$container->setParameter('core.environment', PHPBB_ENVIRONMENT);
		$cache_path = $phpbb_root_path . 'cache/twig';
		$context = new \phpbb\template\context();
		$loader = new \phpbb\template\twig\loader($this->filesystem, '');
		$twig = new \phpbb\template\twig\environment(
			$this->config,
			$this->filesystem,
			$this->phpbb_path_helper,
			$container,
			$cache_path,
			null,
			$loader,
			array(
				'cache'			=> false,
				'debug'			=> false,
				'auto_reload'	=> true,
				'autoescape'	=> false,
			)
		);
		$this->template = new phpbb\template\twig\twig($this->phpbb_path_helper, $this->config, $context, $twig, $cache_path, $this->user, array(new \phpbb\template\twig\extension($context, $this->user)));
		$container->set('template.twig.lexer', new \phpbb\template\twig\lexer($twig));

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

		$this->router = new phpbb_mock_router($container, $this->filesystem, dirname(__FILE__) . '/', 'php', PHPBB_ENVIRONMENT, $this->extension_manager);
		$this->router->find_routing_files($this->extension_manager->all_enabled(false));
		$this->router->find(dirname(__FILE__) . '/');
		// Set correct current phpBB root path
		$this->root_path = $this->get_phpbb_root_path();
	}

	public function helper_url_data_no_rewrite()
	{
		return array(
			array('controller2', array('t' => 1, 'f' => 2), true, false, '/' . $this->path_to_app() . 'app.php/foo/bar?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller2', array('t' => 1, 'f' => 2), false, false, '/' . $this->path_to_app() . 'app.php/foo/bar?t=1&f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, false, '/' . $this->path_to_app() . 'app.php/foo/bar/p-3?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), false, false, '/' . $this->path_to_app() . 'app.php/foo/bar/p-3?t=1&f=2', 'parameters in params-argument as array'),

			// Custom sid parameter
			array('controller2', array('t' => 1, 'f' => 2), true, 'custom-sid', '/' . $this->path_to_app() . 'app.php/foo/bar?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2), false, 'custom-sid', '/' . $this->path_to_app() . 'app.php/foo/bar?t=1&f=2&sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, 'custom-sid', '/' . $this->path_to_app() . 'app.php/foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),

			// Testing anchors
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, false, '/' . $this->path_to_app() . 'app.php/foo/bar?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, false, '/' . $this->path_to_app() . 'app.php/foo/bar?t=1&f=2#anchor', 'anchor in params-argument (array)'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, false, '/' . $this->path_to_app() . 'app.php/foo/bar/p-3?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),

			// Anchors and custom sid
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', '/' . $this->path_to_app() . 'app.php/foo/bar?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, 'custom-sid', '/' . $this->path_to_app() . 'app.php/foo/bar?t=1&f=2&sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', '/' . $this->path_to_app() . 'app.php/foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),

			// Empty parameters should not append the &amp; or ?
			array('controller2', array(), true, false, '/' . $this->path_to_app() . 'app.php/foo/bar', 'no params using empty array'),
			array('controller2', array(), false, false, '/' . $this->path_to_app() . 'app.php/foo/bar', 'no params using empty array'),
			array('controller3', array('p' => 3), true, false, '/' . $this->path_to_app() . 'app.php/foo/bar/p-3', 'no params using empty array'),

			// Resolves DI parameters
			array('controller4', array(), true, false, '/' . $this->path_to_app() . 'app.php/foo/' . PHPBB_ENVIRONMENT, 'di parameter'),
		);
	}

	/**
	* @dataProvider helper_url_data_no_rewrite()
	*/
	public function test_helper_url_no_rewrite($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '0'));
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->router, $this->symfony_request, $this->request, $this->filesystem, $this->root_path, 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id), $description);
	}

	public function helper_url_data_with_rewrite()
	{
		return array(
			array('controller2', array('t' => 1, 'f' => 2), true, false, '/' . $this->path_to_app() . 'foo/bar?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller2', array('t' => 1, 'f' => 2), false, false, '/' . $this->path_to_app() . 'foo/bar?t=1&f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, false, '/' . $this->path_to_app() . 'foo/bar/p-3?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), false, false, '/' . $this->path_to_app() . 'foo/bar/p-3?t=1&f=2', 'parameters in params-argument as array'),

			// Custom sid parameter
			array('controller2', array('t' => 1, 'f' => 2), true, 'custom-sid', '/' . $this->path_to_app() . 'foo/bar?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2), false, 'custom-sid', '/' . $this->path_to_app() . 'foo/bar?t=1&f=2&sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, 'custom-sid', '/' . $this->path_to_app() . 'foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),

			// Testing anchors
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, false, '/' . $this->path_to_app() . 'foo/bar?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, false, '/' . $this->path_to_app() . 'foo/bar?t=1&f=2#anchor', 'anchor in params-argument (array)'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, false, '/' . $this->path_to_app() . 'foo/bar/p-3?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),

			// Anchors and custom sid
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', '/' . $this->path_to_app() . 'foo/bar?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, 'custom-sid', '/' . $this->path_to_app() . 'foo/bar?t=1&f=2&sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', '/' . $this->path_to_app() . 'foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),

			// Empty parameters should not append the &amp; or ?
			array('controller2', array(), true, false, '/' . $this->path_to_app() . 'foo/bar', 'no params using empty array'),
			array('controller2', array(), false, false, '/' . $this->path_to_app() . 'foo/bar', 'no params using empty array'),
			array('controller3', array('p' => 3), true, false, '/' . $this->path_to_app() . 'foo/bar/p-3', 'no params using empty array'),

			// Resolves DI parameters
			array('controller4', array(), true, false, '/' . $this->path_to_app() . 'foo/' . PHPBB_ENVIRONMENT, 'di parameter'),
		);
	}

	/**
	* @dataProvider helper_url_data_with_rewrite()
	*/
	public function test_helper_url_with_rewrite($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '1'));
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->router, $this->symfony_request, $this->request, $this->filesystem, $this->root_path, 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id), $description);
	}

	public function helper_url_data_absolute()
	{
		return array(
			array('controller2', array('t' => 1, 'f' => 2), true, false, 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller2', array('t' => 1, 'f' => 2), false, false, 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, false, 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar/p-3?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), false, false, 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar/p-3?t=1&f=2', 'parameters in params-argument as array'),

			// Custom sid parameter
			array('controller2', array('t' => 1, 'f' => 2), true, 'custom-sid', 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2), false, 'custom-sid', 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&f=2&sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, 'custom-sid', 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),

			// Testing anchors
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, false, 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, false, 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&f=2#anchor', 'anchor in params-argument (array)'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, false, 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar/p-3?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),

			// Anchors and custom sid
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, 'custom-sid', 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&f=2&sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),

			// Empty parameters should not append the &amp; or ?
			array('controller2', array(), true, false, 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar', 'no params using empty array'),
			array('controller2', array(), false, false, 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar', 'no params using empty array'),
			array('controller3', array('p' => 3), true, false, 'http://localhost/' . $this->path_to_app() . 'app.php/foo/bar/p-3', 'no params using empty array'),

			// Resolves DI parameters
			array('controller4', array(), true, false, 'http://localhost/' . $this->path_to_app() . 'app.php/foo/' . PHPBB_ENVIRONMENT, 'di parameter'),
		);
	}

	/**
	* @dataProvider helper_url_data_absolute()
	*/
	public function test_helper_url_absolute($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '0'));
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->router, $this->symfony_request, $this->request, $this->filesystem, $this->root_path, 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id, UrlGeneratorInterface::ABSOLUTE_URL), $description);
	}

	public function helper_url_data_relative_path()
	{
		return array(
			array('controller2', array('t' => 1, 'f' => 2), true, false, 'app.php/foo/bar?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller2', array('t' => 1, 'f' => 2), false, false, 'app.php/foo/bar?t=1&f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, false, 'app.php/foo/bar/p-3?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), false, false, 'app.php/foo/bar/p-3?t=1&f=2', 'parameters in params-argument as array'),

			// Custom sid parameter
			array('controller2', array('t' => 1, 'f' => 2), true, 'custom-sid', 'app.php/foo/bar?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2), false, 'custom-sid', 'app.php/foo/bar?t=1&f=2&sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, 'custom-sid', 'app.php/foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),

			// Testing anchors
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, false, 'app.php/foo/bar?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, false, 'app.php/foo/bar?t=1&f=2#anchor', 'anchor in params-argument (array)'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, false, 'app.php/foo/bar/p-3?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),

			// Anchors and custom sid
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', 'app.php/foo/bar?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, 'custom-sid', 'app.php/foo/bar?t=1&f=2&sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', 'app.php/foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),

			// Empty parameters should not append the &amp; or ?
			array('controller2', array(), true, false, 'app.php/foo/bar', 'no params using empty array'),
			array('controller2', array(), false, false, 'app.php/foo/bar', 'no params using empty array'),
			array('controller3', array('p' => 3), true, false, 'app.php/foo/bar/p-3', 'no params using empty array'),

			// Resolves DI parameters
			array('controller4', array(), true, false,  'app.php/foo/' . PHPBB_ENVIRONMENT, 'di parameter'),
		);
	}

	/**
	* @dataProvider helper_url_data_relative_path()
	*/
	public function test_helper_url_relative_path($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '0'));
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->router, $this->symfony_request, $this->request, $this->filesystem, $this->root_path, 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id, UrlGeneratorInterface::RELATIVE_PATH), $description);
	}

	public function helper_url_data_network()
	{
		return array(
			array('controller2', array('t' => 1, 'f' => 2), true, false, '//localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller2', array('t' => 1, 'f' => 2), false, false, '//localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, false, '//localhost/' . $this->path_to_app() . 'app.php/foo/bar/p-3?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), false, false, '//localhost/' . $this->path_to_app() . 'app.php/foo/bar/p-3?t=1&f=2', 'parameters in params-argument as array'),

			// Custom sid parameter
			array('controller2', array('t' => 1, 'f' => 2), true, 'custom-sid', '//localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2), false, 'custom-sid', '//localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&f=2&sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, 'custom-sid', '//localhost/' . $this->path_to_app() . 'app.php/foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),

			// Testing anchors
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, false, '//localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, false, '//localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&f=2#anchor', 'anchor in params-argument (array)'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, false, '//localhost/' . $this->path_to_app() . 'app.php/foo/bar/p-3?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),

			// Anchors and custom sid
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', '//localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, 'custom-sid', '//localhost/' . $this->path_to_app() . 'app.php/foo/bar?t=1&f=2&sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', '//localhost/' . $this->path_to_app() . 'app.php/foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),

			// Empty parameters should not append the &amp; or ?
			array('controller2', array(), true, false, '//localhost/' . $this->path_to_app() . 'app.php/foo/bar', 'no params using empty array'),
			array('controller2', array(), false, false, '//localhost/' . $this->path_to_app() . 'app.php/foo/bar', 'no params using empty array'),
			array('controller3', array('p' => 3), true, false, '//localhost/' . $this->path_to_app() . 'app.php/foo/bar/p-3', 'no params using empty array'),

			// Resolves DI parameters
			array('controller4', array(), true, false, '//localhost/' . $this->path_to_app() . 'app.php/foo/' . PHPBB_ENVIRONMENT, 'di parameter'),
		);
	}

	/**
	* @dataProvider helper_url_data_network()
	*/
	public function test_helper_url_network($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '0'));
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->router, $this->symfony_request, $this->request, $this->filesystem, $this->root_path, 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id, UrlGeneratorInterface::NETWORK_PATH), $description);
	}

	public function helper_url_data_absolute_with_rewrite()
	{
		return array(
			array('controller2', array('t' => 1, 'f' => 2), true, false, 'http://localhost/' . $this->path_to_app() . 'foo/bar?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller2', array('t' => 1, 'f' => 2), false, false, 'http://localhost/' . $this->path_to_app() . 'foo/bar?t=1&f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, false, 'http://localhost/' . $this->path_to_app() . 'foo/bar/p-3?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), false, false, 'http://localhost/' . $this->path_to_app() . 'foo/bar/p-3?t=1&f=2', 'parameters in params-argument as array'),

			// Custom sid parameter
			array('controller2', array('t' => 1, 'f' => 2), true, 'custom-sid', 'http://localhost/' . $this->path_to_app() . 'foo/bar?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2), false, 'custom-sid', 'http://localhost/' . $this->path_to_app() . 'foo/bar?t=1&f=2&sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, 'custom-sid', 'http://localhost/' . $this->path_to_app() . 'foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),

			// Testing anchors
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, false, 'http://localhost/' . $this->path_to_app() . 'foo/bar?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, false, 'http://localhost/' . $this->path_to_app() . 'foo/bar?t=1&f=2#anchor', 'anchor in params-argument (array)'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, false, 'http://localhost/' . $this->path_to_app() . 'foo/bar/p-3?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),

			// Anchors and custom sid
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', 'http://localhost/' . $this->path_to_app() . 'foo/bar?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, 'custom-sid', 'http://localhost/' . $this->path_to_app() . 'foo/bar?t=1&f=2&sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', 'http://localhost/' . $this->path_to_app() . 'foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),

			// Empty parameters should not append the &amp; or ?
			array('controller2', array(), true, false, 'http://localhost/' . $this->path_to_app() . 'foo/bar', 'no params using empty array'),
			array('controller2', array(), false, false, 'http://localhost/' . $this->path_to_app() . 'foo/bar', 'no params using empty array'),
			array('controller3', array('p' => 3), true, false, 'http://localhost/' . $this->path_to_app() . 'foo/bar/p-3', 'no params using empty array'),

			// Resolves DI parameters
			array('controller4', array(), true, false, 'http://localhost/' . $this->path_to_app() . 'foo/' . PHPBB_ENVIRONMENT, 'di parameter'),
		);
	}

	/**
	 * @dataProvider helper_url_data_absolute_with_rewrite()
	 */
	public function test_helper_url_absolute_with_rewrite($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '1'));
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->router, $this->symfony_request, $this->request, $this->filesystem, $this->root_path, 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id, UrlGeneratorInterface::ABSOLUTE_URL), $description);
	}

	public function helper_url_data_relative_path_with_rewrite()
	{
		return array(
			array('controller2', array('t' => 1, 'f' => 2), true, false, 'foo/bar?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller2', array('t' => 1, 'f' => 2), false, false, 'foo/bar?t=1&f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, false, 'foo/bar/p-3?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), false, false, 'foo/bar/p-3?t=1&f=2', 'parameters in params-argument as array'),

			// Custom sid parameter
			array('controller2', array('t' => 1, 'f' => 2), true, 'custom-sid', 'foo/bar?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2), false, 'custom-sid', 'foo/bar?t=1&f=2&sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, 'custom-sid', 'foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),

			// Testing anchors
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, false, 'foo/bar?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, false, 'foo/bar?t=1&f=2#anchor', 'anchor in params-argument (array)'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, false, 'foo/bar/p-3?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),

			// Anchors and custom sid
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', 'foo/bar?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, 'custom-sid', 'foo/bar?t=1&f=2&sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', 'foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),

			// Empty parameters should not append the &amp; or ?
			array('controller2', array(), true, false, 'foo/bar', 'no params using empty array'),
			array('controller2', array(), false, false, 'foo/bar', 'no params using empty array'),
			array('controller3', array('p' => 3), true, false, 'foo/bar/p-3', 'no params using empty array'),
		);
	}

	/**
	 * @dataProvider helper_url_data_relative_path_with_rewrite()
	 */
	public function test_helper_url_relative_path_with_rewrite($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '1'));
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->router, $this->symfony_request, $this->request, $this->filesystem, $this->root_path, 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id, UrlGeneratorInterface::RELATIVE_PATH), $description);
	}

	public function helper_url_data_network_with_rewrite()
	{
		return array(
			array('controller2', array('t' => 1, 'f' => 2), true, false, '//localhost/' . $this->path_to_app() . 'foo/bar?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller2', array('t' => 1, 'f' => 2), false, false, '//localhost/' . $this->path_to_app() . 'foo/bar?t=1&f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, false, '//localhost/' . $this->path_to_app() . 'foo/bar/p-3?t=1&amp;f=2', 'parameters in params-argument as array'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), false, false, '//localhost/' . $this->path_to_app() . 'foo/bar/p-3?t=1&f=2', 'parameters in params-argument as array'),

			// Custom sid parameter
			array('controller2', array('t' => 1, 'f' => 2), true, 'custom-sid', '//localhost/' . $this->path_to_app() . 'foo/bar?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2), false, 'custom-sid', '//localhost/' . $this->path_to_app() . 'foo/bar?t=1&f=2&sid=custom-sid', 'params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2), true, 'custom-sid', '//localhost/' . $this->path_to_app() . 'foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid', 'params-argument (array) using session_id'),

			// Testing anchors
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, false, '//localhost/' . $this->path_to_app() . 'foo/bar?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, false, '//localhost/' . $this->path_to_app() . 'foo/bar?t=1&f=2#anchor', 'anchor in params-argument (array)'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, false, '//localhost/' . $this->path_to_app() . 'foo/bar/p-3?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),

			// Anchors and custom sid
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', '//localhost/' . $this->path_to_app() . 'foo/bar?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller2', array('t' => 1, 'f' => 2, '#' => 'anchor'), false, 'custom-sid', '//localhost/' . $this->path_to_app() . 'foo/bar?t=1&f=2&sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),
			array('controller3', array('p' => 3, 't' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', '//localhost/' . $this->path_to_app() . 'foo/bar/p-3?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),

			// Empty parameters should not append the &amp; or ?
			array('controller2', array(), true, false, '//localhost/' . $this->path_to_app() . 'foo/bar', 'no params using empty array'),
			array('controller2', array(), false, false, '//localhost/' . $this->path_to_app() . 'foo/bar', 'no params using empty array'),
			array('controller3', array('p' => 3), true, false, '//localhost/' . $this->path_to_app() . 'foo/bar/p-3', 'no params using empty array'),

			// Resolves DI parameters
			array('controller4', array(), true, false, '//localhost/' . $this->path_to_app() . 'foo/' . PHPBB_ENVIRONMENT, 'di parameter'),
		);
	}

	/**
	 * @dataProvider helper_url_data_network_with_rewrite()
	 */
	public function test_helper_url_network_with_rewrite($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '1'));
		$this->helper = new phpbb_mock_controller_helper($this->template, $this->user, $this->config, $this->router, $this->symfony_request, $this->request, $this->filesystem, $this->root_path, 'php', dirname(__FILE__) . '/');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id, UrlGeneratorInterface::NETWORK_PATH), $description);
	}
}
