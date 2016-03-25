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

namespace phpbb\extension;

/**
* The interface extension meta classes have to implement to run custom code
* on enable/disable/purge.
*/
interface extension_interface
{
	/**
	* Indicate whether or not the extension can be enabled.
	*
	* @return bool
	*/
	public function is_enableable();

	/**
	* enable_step is executed on enabling an extension until it returns false.
	*
	* Calls to this function can be made in subsequent requests, when the
	* function is invoked through a webserver with a too low max_execution_time.
	*
	* @param	mixed	$old_state	The return value of the previous call
	*								of this method, or false on the first call
	* @return	mixed				Returns false after last step, otherwise
	*								temporary state which is passed as an
	*								argument to the next step
	*/
	public function enable_step($old_state);

	/**
	* Disables the extension.
	*
	* Calls to this function can be made in subsequent requests, when the
	* function is invoked through a webserver with a too low max_execution_time.
	*
	* @param	mixed	$old_state	The return value of the previous call
	*								of this method, or false on the first call
	* @return	mixed				Returns false after last step, otherwise
	*								temporary state which is passed as an
	*								argument to the next step
	*/
	public function disable_step($old_state);

	/**
	* purge_step is executed on purging an extension until it returns false.
	*
	* Calls to this function can be made in subsequent requests, when the
	* function is invoked through a webserver with a too low max_execution_time.
	*
	* @param	mixed	$old_state	The return value of the previous call
	*								of this method, or false on the first call
	* @return	mixed				Returns false after last step, otherwise
	*								temporary state which is passed as an
	*								argument to the next step
	*/
	public function purge_step($old_state);
}
