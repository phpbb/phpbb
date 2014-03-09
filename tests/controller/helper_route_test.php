<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_controller_helper_route_test extends phpbb_test_case
{
	public function setUp()
	{
		global $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher;
		$this->user = $this->getMock('\phpbb\user');
		$phpbb_path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			new \phpbb\filesystem(),
			$phpbb_root_path,
			$phpEx
		);
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '0'));
		$this->template = new phpbb\template\twig\twig($phpbb_path_helper, $this->config, $this->user, new \phpbb\template\context());

		$finder = new \phpbb\extension\finder(
			new phpbb_mock_extension_manager(
				dirname(__FILE__) . '/',
				array(
					'vendor2/foo' => array(
						'ext_name' => 'vendor2/foo',
						'ext_active' => '1',
						'ext_path' => 'ext/vendor2/foo/',
					),
				)
			),
			new \phpbb\filesystem(),
			dirname(__FILE__) . '/',
			new phpbb_mock_cache()
		);
		$this->provider = new \phpbb\controller\provider($finder);
		$this->provider->find(dirname(__FILE__) . '/');
	}

	public function helper_url_data_no_rewrite()
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
		);
	}

	/**
	* @dataProvider helper_url_data_no_rewrite()
	*/
	public function test_helper_url_no_rewrite($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->helper = new \phpbb\controller\helper($this->template, $this->user, $this->config, $this->provider, '', 'php');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id));
	}

	public function helper_url_data_with_rewrite()
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
	* @dataProvider helper_url_data_with_rewrite()
	*/
	public function test_helper_url_with_rewrite($route, $params, $is_amp, $session_id, $expected, $description)
	{
		$this->config = new \phpbb\config\config(array('enable_mod_rewrite' => '1'));
		$this->helper = new \phpbb\controller\helper($this->template, $this->user, $this->config, $this->provider, '', 'php');
		$this->assertEquals($expected, $this->helper->route($route, $params, $is_amp, $session_id));
	}
}
