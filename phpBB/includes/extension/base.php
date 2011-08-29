<?php
/**
*
* @package extension
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* A base class for extensions without custom enable/disbale/purge code.
*
* @package extension
*/
class phpbb_extension_base implements phpbb_extension_interface
{
	/**
	* Single enable step that does nothing
	*
	* @return false Indicates no further steps are required
	*/
	public function enable_step($old_state)
	{
		return false;
	}

	/**
	* Empty disable method
	*
	* @return null
	*/
	public function disable()
	{
	}

	/**
	* Single purge step that does nothing
	*
	* @return false Indicates no further steps are required
	*/
	public function purge_step($old_state)
	{
		return false;
	}
}
