<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_mock_event_dispatcher
{
	public function trigger_event($eventName, $data)
	{
		return array();
	}
}
