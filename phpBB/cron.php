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
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);

$cron_type = request_var('cron_type', '');

$use_shutdown_function = (@function_exists('register_shutdown_function')) ? true : false;

// Run cron-like action
// Real cron-based layer will be introduced in 3.2
switch ($cron_type)
{
	case 'queue':
		include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);
		$queue = new queue();
		if ($use_shutdown_function)
		{
			register_shutdown_function(array(&$queue, 'process'));
		}
		else
		{
			$queue->process();
		}
		break;

	case 'tidy_cache':
		if ($use_shutdown_function)
		{
			register_shutdown_function(array(&$cache, 'tidy'));
		}
		else
		{
			$cache->tidy();
		}
		break;

	case 'tidy_database':
		include_once($phpbb_root_path . 'includes/functions_admin.'.$phpEx);

		if ($use_shutdown_function)
		{
			register_shutdown_function('tidy_database');
		}
		else
		{
			tidy_database();
		}
		break;
		
	case 'tidy_login_keys':
		if ($use_shutdown_function)
		{
			register_shutdown_function(array(&$user, 'tidy_login_keys'));
		}
		else
		{
			$user->tidy_login_keys();
		}
}

// Output transparent gif
header('Cache-Control: no-cache');
header('Content-type: image/gif');
header('Content-length: 43');

echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

flush();
exit;

?>