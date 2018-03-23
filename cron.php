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
	// Output transparent gif
	header('Cache-Control: no-cache');
	header('Content-type: image/gif');
	header('Content-length: 43');

	echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

	// Flush here to prevent browser from showing the page as loading while
	// running cron.
	flush();
}

// Thanks to various fatal errors and lack of try/finally, it is quite easy to leave
// the cron lock locked, especially when working on cron-related code.
//
// Attempt to alleviate the problem by doing setup outside of the lock as much as possible.

$cron_type = $request->variable('cron_type', '');

// Comment this line out for debugging so the page does not return an image.
output_image();

/* @var $cron_lock \phpbb\lock\db */
$cron_lock = $phpbb_container->get('cron.lock_db');
if ($cron_lock->acquire())
{
	/* @var $cron \phpbb\cron\manager */
	$cron = $phpbb_container->get('cron.manager');

	$task = $cron->find_task($cron_type);
	if ($task)
	{
		/**
		 * This event enables you to catch the task before it runs
		 *
		 * @event core.cron_run_before
		 * @var	\phpbb\cron\task\wrapper	task	Current Cron task
		 * @since 3.1.8-RC1
		 */
		$vars = array(
			'task',
		);
		extract($phpbb_dispatcher->trigger_event('core.cron_run_before', compact($vars)));

		if ($task->is_parametrized())
		{
			$task->parse_parameters($request);
		}
		if ($task->is_ready())
		{
			$task->run();
		}
	}
	$cron_lock->release();
}

garbage_collection();
