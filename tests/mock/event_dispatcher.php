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

	public function trigger_event($eventName, $data = array()): array
	{
		// Ensure tests never hard-exit when phpBB calls exit_handler()
		if ($eventName === 'core.exit_handler')
		{
			// Set the override flag so exit_handler() returns instead of exit;
			if (is_array($data))
			{
				$data['exit_handler_override'] = true;
			}
			return (array) $data;
		}

		// Default behaviour of the mock: return the input data unchanged
		return (array) $data;
	}
}
