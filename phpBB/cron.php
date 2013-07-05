<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
*/
define('IN_PHPBB', true);
define('IN_CRON', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Do not update users last page entry
$user->session_begin(false);
$auth->acl($user->data);

function output_image()
{
	define('IMAGE_OUTPUT', true);

	// Output transparent gif
	header('Cache-Control: no-cache');
	header('Content-type: image/gif');
	header('Content-length: 43');

	echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

	// Flush here to prevent browser from showing the page as loading while
	// running cron.
	flush();
}

function do_cron($cron_lock, $run_tasks)
{
	global $config;

	foreach ($run_tasks as $task)
	{
		if (defined('DEBUG') && $config['use_system_cron'])
		{
			echo "[phpBB cron] Running task '{$task->get_name()}'\n";
		}

		$task->run();
	}

	// Unloading cache and closing db after having done the dirty work.
	$cron_lock->release();
	garbage_collection();
}

// Thanks to various fatal errors and lack of try/finally, it is quite easy to leave
// the cron lock locked, especially when working on cron-related code.
//
// Attempt to alleviate the problem by doing setup outside of the lock as much as possible.
//
// If DEBUG is defined and cron lock cannot be obtained, a message will be printed.

if ($config['use_system_cron'])
{
	$cron = $phpbb_container->get('cron.manager');
}
else
{
	$cron_type = request_var('cron_type', '');

	// Comment this line out for debugging so the page does not return an image.
	output_image();
}

$cron_lock = $phpbb_container->get('cron.lock_db');
if ($cron_lock->acquire())
{
	if ($config['use_system_cron'])
	{
		$run_tasks = $cron->find_all_ready_tasks();
	}
	else
	{
		// If invalid task is specified, empty $run_tasks is passed to do_cron which then does nothing
		$run_tasks = array();
		$task = $cron->find_task($cron_type);
		if ($task)
		{
			if ($task->is_parametrized())
			{
				$task->parse_parameters($request);
			}
			if ($task->is_ready())
			{
				$run_tasks = array($task);
			}
		}
	}

	do_cron($cron_lock, $run_tasks);
}
else
{
	if (defined('DEBUG'))
	{
		echo "Could not obtain cron lock.\n";
	}
}
