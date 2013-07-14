<?php
/**
*
* @package extension
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* The interface extension meta classes have to implement to run custom code
* on enable/disable/purge.
*
* @package extension
*/
interface phpbb_extension_extension_interface
{
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
