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

	public function helper_url_data()
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
	* @dataProvider helper_url_data
	*/
	public function test_helper_url($route, $params, $is_amp, $session_id, $expected, $description)
	{
		global $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher;
		$this->user = $this->getMock('phpbb_user');
		$this->template = new phpbb_template_twig($phpbb_root_path, $phpEx, $config, $this->user, new phpbb_template_context());


		$request = new phpbb_mock_request($_GET, $_POST, $_COOKIE, $_SERVER, false, $_FILES);
		$helper = new phpbb_controller_helper($this->template, $this->user, $request, '', 'php');
		$this->assertEquals($helper->url($route, $params, $is_amp, $session_id), $expected);
	}
}
