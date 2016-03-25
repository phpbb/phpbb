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

class phpbb_mock_event_dispatcher extends \phpbb\event\dispatcher
{
	/**
	* Constructor.
	*
	* Overwrite the constructor to get rid of ContainerInterface param instance
	*/
	public function __construct()
	{
	}

	public function trigger_event($eventName, $data = array())
	{
		return array();
	}
}
