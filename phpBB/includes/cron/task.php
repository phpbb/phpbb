<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2010 phpBB Group
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
* Cron task interface
* @package phpBB3
*/
interface phpbb_cron_task
{
	/**
	* Runs this cron task.
	*/
	public function run();

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* For example, a cron task that prunes forums can only run when
	* forum pruning is enabled.
	*/
	public function is_runnable();

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*/
	public function should_run();

	/**
	* Returns whether this cron task can be run in shutdown function.
	*/
	public function is_shutdown_function_safe();
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
interface phpbb_parametrized_cron_task extends cron_task
{
	/**
	* Returns parameters of this cron task as an array.
	*
	* The array must map string keys to string values.
	*/
	public function get_parameters();

	/**
	* Parses parameters found in $params, which is an array.
	*
	* $params contains user input and must not be trusted.
	* In normal operation $params contains the same data that was returned by
	* get_parameters method. However, a malicious user can supply arbitrary
	* data in $params.
	* Cron task must validate all keys and values in $params before using them.
	*/
	public function parse_parameters($params);
}
