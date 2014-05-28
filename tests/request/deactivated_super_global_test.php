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

class phpbb_deactivated_super_global_test extends phpbb_test_case
{
	/**
	* Checks that on write access the correct error is thrown
	*/
	public function test_write_triggers_error()
	{
		$this->setExpectedTriggerError(E_USER_ERROR);
		$obj = new \phpbb\request\deactivated_super_global($this->getMock('\phpbb\request\request_interface'), 'obj', \phpbb\request\request_interface::POST);
		$obj->offsetSet(0, 0);
	}
}
