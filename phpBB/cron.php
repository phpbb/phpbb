<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
*/
define('IN_PHPBB', true);
define('IN_CRON', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/cron_lock.' . $phpEx);

// Do not update users last page entry
$user->session_begin(false);
$auth->acl($user->data);

function output_image()
{
	// Output transparent gif
	header('Cache-Control: no-cache');
	header('Content-type: image/gif');
	header('Content-length: 43');

	echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

	// test without flush ;)
	// flush();
}

function do_cron($run_tasks)
{
	global $cron_lock;

	foreach ($run_tasks as $cron_type)
	{
		$cron->run_task($cron_type);
	}

	// Unloading cache and closing db after having done the dirty work.
	$cron_lock->unlock();
	garbage_collection();
}

if ($cron_lock->lock())
{
	if ($config['use_system_cron'])
	{
		$use_shutdown_function = false;

		$run_tasks = $cron->find_all_runnable_tasks();
	}
	else
	{
		$cron_type = request_var('cron_type', '');
		$use_shutdown_function = (@function_exists('register_shutdown_function')) ? true : false;

		output_image();

		if ($cron->is_valid_task($cron_type) && $cron->is_task_runnable($cron_type))
		{
			if ($use_shutdown_function && !$cron->is_task_shutdown_function_compatible($cron_type))
			{
				$use_shutdown_function = false;
			}
			$run_tasks = array($cron_type);
		}
	}
	if ($use_shutdown_function)
	{
		register_shutdown_function('do_cron', $run_tasks);
	}
	else
	{
		do_cron($run_tasks);
	}
}
