<?php
/**
*
* @package phpBB3
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\cron\task;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Parametrized cron task interface.
*
* Parametrized cron tasks are somewhat of a cross between regular cron tasks and
* delayed jobs. Whereas regular cron tasks perform some action globally,
* parametrized cron tasks perform actions on a particular object (or objects).
* Parametrized cron tasks do not make sense and are not usable without
* specifying these objects.
*
* @package phpBB3
*/
interface parametrized extends \phpbb\cron\task\task
{
	/**
	* Returns parameters of this cron task as an array.
	*
	* The array must map string keys to string values.
	*
	* @return array
	*/
	public function get_parameters();

	/**
	* Parses parameters found in $request, which is an instance of
	* \phpbb\request\request_interface.
	*
	* $request contains user input and must not be trusted.
	* Cron task must validate all data before using it.
	*
	* @param \phpbb\request\request_interface $request Request object.
	*
	* @return null
	*/
	public function parse_parameters(\phpbb\request\request_interface $request);
}
