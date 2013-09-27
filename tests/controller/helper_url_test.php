<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_controller_helper_url_test extends phpbb_test_case
{

	public function helper_url_data_no_rewrite()
	{
		return array(
			array('foo/bar?t=1&amp;f=2', false, true, false, 'app.php/foo/bar?t=1&amp;f=2', 'parameters in url-argument'),
			array('foo/bar', 't=1&amp;f=2', true, false, 'app.php/foo/bar?t=1&amp;f=2', 'parameters in params-argument using amp'),
			array('foo/bar', 't=1&f=2', false, false, 'app.php/foo/bar?t=1&f=2', 'parameters in params-argument using &'),
			array('foo/bar', array('t' => 1, 'f' => 2), true, false, 'app.php/foo/bar?t=1&amp;f=2', 'parameters in params-argument as array'),

			// Custom sid parameter
			array('foo/bar', 't=1&amp;f=2', true, 'custom-sid', 'app.php/foo/bar?t=1&amp;f=2&amp;sid=custom-sid', 'using session_id'),

			// Testing anchors
			array('foo/bar?t=1&amp;f=2#anchor', false, true, false, 'app.php/foo/bar?t=1&amp;f=2#anchor', 'anchor in url-argument'),
			array('foo/bar', 't=1&amp;f=2#anchor', true, false, 'app.php/foo/bar?t=1&amp;f=2#anchor', 'anchor in params-argument'),
			array('foo/bar', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, false, 'app.php/foo/bar?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),

			// Anchors and custom sid
			array('foo/bar?t=1&amp;f=2#anchor', false, true, 'custom-sid', 'app.php/foo/bar?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in url-argument using session_id'),
			array('foo/bar', 't=1&amp;f=2#anchor', true, 'custom-sid', 'app.php/foo/bar?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument using session_id'),
			array('foo/bar', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', 'app.php/foo/bar?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),

			// Empty parameters should not append the &amp;
			array('foo/bar', false, true, false, 'app.php/foo/bar', 'no params using bool false'),
			array('foo/bar', '', true, false, 'app.php/foo/bar', 'no params using empty string'),
			array('foo/bar', array(), true, false, 'app.php/foo/bar', 'no params using empty array'),
		);
	}

	/**
	* @dataProvider helper_url_data_no_rewrite()
	*/
	public function test_helper_url_no_rewrite($route, $params, $is_amp, $session_id, $expected, $description)
	{
		global $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher;
		$this->user = $this->getMock('\phpbb\user');
		$phpbb_filesystem = new \phpbb\filesystem(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			$phpbb_root_path,
			$phpEx
		);
		$this->template = new phpbb\template\twig\twig($phpbb_filesystem, $config, $this->user, new \phpbb\template\context());

		// We don't use mod_rewrite in these tests
		$config = new \phpbb\config\config(array('enable_mod_rewrite' => '0'));
		$helper = new \phpbb\controller\helper($this->template, $this->user, $config, '', 'php');
		$this->assertEquals($helper->url($route, $params, $is_amp, $session_id), $expected);
	}

	public function helper_url_data_with_rewrite()
	{
		return array(
			array('foo/bar?t=1&amp;f=2', false, true, false, 'foo/bar?t=1&amp;f=2', 'parameters in url-argument'),
			array('foo/bar', 't=1&amp;f=2', true, false, 'foo/bar?t=1&amp;f=2', 'parameters in params-argument using amp'),
			array('foo/bar', 't=1&f=2', false, false, 'foo/bar?t=1&f=2', 'parameters in params-argument using &'),
			array('foo/bar', array('t' => 1, 'f' => 2), true, false, 'foo/bar?t=1&amp;f=2', 'parameters in params-argument as array'),

			// Custom sid parameter
			array('foo/bar', 't=1&amp;f=2', true, 'custom-sid', 'foo/bar?t=1&amp;f=2&amp;sid=custom-sid', 'using session_id'),

			// Testing anchors
			array('foo/bar?t=1&amp;f=2#anchor', false, true, false, 'foo/bar?t=1&amp;f=2#anchor', 'anchor in url-argument'),
			array('foo/bar', 't=1&amp;f=2#anchor', true, false, 'foo/bar?t=1&amp;f=2#anchor', 'anchor in params-argument'),
			array('foo/bar', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, false, 'foo/bar?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),

			// Anchors and custom sid
			array('foo/bar?t=1&amp;f=2#anchor', false, true, 'custom-sid', 'foo/bar?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in url-argument using session_id'),
			array('foo/bar', 't=1&amp;f=2#anchor', true, 'custom-sid', 'foo/bar?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument using session_id'),
			array('foo/bar', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', 'foo/bar?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),

			// Empty parameters should not append the &amp;
			array('foo/bar', false, true, false, 'foo/bar', 'no params using bool false'),
			array('foo/bar', '', true, false, 'foo/bar', 'no params using empty string'),
			array('foo/bar', array(), true, false, 'foo/bar', 'no params using empty array'),
		);
	}

	/**
	* @dataProvider helper_url_data_with_rewrite()
	*/
	public function test_helper_url_with_rewrite($route, $params, $is_amp, $session_id, $expected, $description)
	{
		global $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher;
		$this->user = $this->getMock('\phpbb\user');
		$phpbb_filesystem = new \phpbb\filesystem(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			$phpbb_root_path,
			$phpEx
		);
		$this->template = new \phpbb\template\twig\twig($phpbb_filesystem, $config, $this->user, new \phpbb\template\context());

		$config = new \phpbb\config\config(array('enable_mod_rewrite' => '1'));
		$helper = new \phpbb\controller\helper($this->template, $this->user, $config, '', 'php');
		$this->assertEquals($helper->url($route, $params, $is_amp, $session_id), $expected);
	}
}
