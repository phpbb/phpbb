<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_session_append_sid_test extends phpbb_test_case
{

	public function append_sid_data()
	{
		return array(
			array('viewtopic.php?t=1&amp;f=2', false, true, false, 'viewtopic.php?t=1&amp;f=2'),
			array('viewtopic.php', 't=1&amp;f=2', true, false, 'viewtopic.php?t=1&amp;f=2'),
			array('viewtopic.php', 't=1&f=2', false, false, 'viewtopic.php?t=1&f=2'),
			array('viewtopic.php', array('t' => 1, 'f' => 2), true, false, 'viewtopic.php?t=1&amp;f=2'),

			// Custom sid parameter
			array('viewtopic.php', 't=1&amp;f=2', true, 'custom-sid', 'viewtopic.php?t=1&amp;f=2&amp;sid=custom-sid'),

			// Testing anchors
			array('viewtopic.php?t=1&amp;f=2#anchor', false, true, false, 'viewtopic.php?t=1&amp;f=2#anchor'),
			array('viewtopic.php', 't=1&amp;f=2#anchor', true, false, 'viewtopic.php?t=1&amp;f=2#anchor'),
			array('viewtopic.php', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, false, 'viewtopic.php?t=1&amp;f=2#anchor'),

			// Anchors and custom sid
			array('viewtopic.php?t=1&amp;f=2#anchor', false, true, 'custom-sid', 'viewtopic.php?t=1&amp;f=2&amp;sid=custom-sid#anchor'),
			array('viewtopic.php', 't=1&amp;f=2#anchor', true, 'custom-sid', 'viewtopic.php?t=1&amp;f=2&amp;sid=custom-sid#anchor'),
			array('viewtopic.php', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', 'viewtopic.php?t=1&amp;f=2&amp;sid=custom-sid#anchor'),

			// Empty parameters should not append the ?
			array('viewtopic.php', false, true, false, 'viewtopic.php'),
			array('viewtopic.php', '', true, false, 'viewtopic.php'),
			array('viewtopic.php', array(), true, false, 'viewtopic.php'),
		);
	}

	/**
	* @dataProvider append_sid_data
	*/
	public function test_append_sid($url, $params, $is_amp, $session_id, $expected)
	{
		$this->assertEquals($expected, append_sid($url, $params, $is_amp, $session_id));
	}
}

