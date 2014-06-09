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

class phpbb_session_append_sid_test extends phpbb_test_case
{

	public function append_sid_data()
	{
		return array(
			array('viewtopic.php?t=1&amp;f=2', false, true, false, 'viewtopic.php?t=1&amp;f=2', 'parameters in url-argument'),
			array('viewtopic.php', 't=1&amp;f=2', true, false, 'viewtopic.php?t=1&amp;f=2', 'parameters in params-argument using amp'),
			array('viewtopic.php', 't=1&f=2', false, false, 'viewtopic.php?t=1&f=2', 'parameters in params-argument using &'),
			array('viewtopic.php', array('t' => 1, 'f' => 2), true, false, 'viewtopic.php?t=1&amp;f=2', 'parameters in params-argument as array'),

			// Custom sid parameter
			array('viewtopic.php', 't=1&amp;f=2', true, 'custom-sid', 'viewtopic.php?t=1&amp;f=2&amp;sid=custom-sid', 'using session_id'),

			// Testing anchors
			array('viewtopic.php?t=1&amp;f=2#anchor', false, true, false, 'viewtopic.php?t=1&amp;f=2#anchor', 'anchor in url-argument'),
			array('viewtopic.php', 't=1&amp;f=2#anchor', true, false, 'viewtopic.php?t=1&amp;f=2#anchor', 'anchor in params-argument'),
			array('viewtopic.php', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, false, 'viewtopic.php?t=1&amp;f=2#anchor', 'anchor in params-argument (array)'),

			// Anchors and custom sid
			array('viewtopic.php?t=1&amp;f=2#anchor', false, true, 'custom-sid', 'viewtopic.php?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in url-argument using session_id'),
			array('viewtopic.php', 't=1&amp;f=2#anchor', true, 'custom-sid', 'viewtopic.php?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument using session_id'),
			array('viewtopic.php', array('t' => 1, 'f' => 2, '#' => 'anchor'), true, 'custom-sid', 'viewtopic.php?t=1&amp;f=2&amp;sid=custom-sid#anchor', 'anchor in params-argument (array) using session_id'),

			// Empty parameters should not append the ?
			array('viewtopic.php', false, true, false, 'viewtopic.php', 'no params using bool false'),
			array('viewtopic.php', '', true, false, 'viewtopic.php', 'no params using empty string'),
			array('viewtopic.php', array(), true, false, 'viewtopic.php', 'no params using empty array'),
		);
	}

	/**
	* @dataProvider append_sid_data
	*/
	public function test_append_sid($url, $params, $is_amp, $session_id, $expected, $description)
	{
		global $phpbb_dispatcher;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher;
		$this->assertEquals($expected, append_sid($url, $params, $is_amp, $session_id));
	}
}

