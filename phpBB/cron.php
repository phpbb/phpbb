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
include($phpbb_root_path . 'common.' . $phpEx);

// Do not update users last page entry
$user->session_begin(false);
$auth->acl($user->data);

$cron_type = request_var('cron_type', '');
$use_shutdown_function = (@function_exists('register_shutdown_function')) ? true : false;

// Output transparent gif
header('Cache-Control: no-cache');
header('Content-type: image/gif');
header('Content-length: 43');

echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

// test without flush ;)
// flush();

/**
* Run cron-like action
* Real cron-based layer will be introduced in 3.2
*/
switch ($cron_type)
{
	case 'queue':

		if (time() - $config['queue_interval'] <= $config['last_queue_run'] || !file_exists($phpbb_root_path . 'cache/queue.' . $phpEx))
		{
			break;
		}

		include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
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

		if (time() - $config['cache_gc'] <= $config['cache_last_gc'] || !method_exists($cache, 'tidy'))
		{
			break;
		}

		if ($use_shutdown_function)
		{
			register_shutdown_function(array(&$cache, 'tidy'));
		}
		else
		{
			$cache->tidy();
		}

	break;

	case 'tidy_search':
		
		// Select the search method
		$search_type = basename($config['search_type']);

		if (time() - $config['search_gc'] <= $config['search_last_gc'] || !file_exists($phpbb_root_path . 'includes/search/' . $search_type . '.' . $phpEx))
		{
			break;
		}

		include_once("{$phpbb_root_path}includes/search/$search_type.$phpEx");

		// We do some additional checks in the module to ensure it can actually be utilised
		$error = false;
		$search = new $search_type($error);

		if ($error)
		{
			break;
		}

		if ($use_shutdown_function)
		{
			register_shutdown_function(array(&$search, 'tidy'));
		}
		else
		{
			$search->tidy();
		}

	break;

	case 'tidy_warnings':

		if (time() - $config['warnings_gc'] <= $config['warnings_last_gc'])
		{
			break;
		}

		include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

		if ($use_shutdown_function)
		{
			register_shutdown_function('tidy_warnings');
		}
		else
		{
			tidy_warnings();
		}

	break;

	case 'tidy_database':

		if (time() - $config['database_gc'] <= $config['database_last_gc'])
		{
			break;
		}

		include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

		if ($use_shutdown_function)
		{
			register_shutdown_function('tidy_database');
		}
		else
		{
			tidy_database();
		}

	break;

	case 'tidy_sessions':

		if (time() - $config['session_gc'] <= $config['session_last_gc'])
		{
			break;
		}

		if ($use_shutdown_function)
		{
			register_shutdown_function(array(&$user, 'session_gc'));
		}
		else
		{
			$user->session_gc();
		}

	break;

	case 'prune_forum':

		$forum_id = request_var('f', 0);

		$sql = 'SELECT forum_id, prune_next, enable_prune, prune_days, prune_viewed, forum_flags, prune_freq
			FROM ' . FORUMS_TABLE . "
			WHERE forum_id = $forum_id";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			break;
		}

		// Do the forum Prune thang
		if ($row['prune_next'] < time() && $row['enable_prune'])
		{
			include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

			if ($row['prune_days'])
			{
				if ($use_shutdown_function)
				{
					register_shutdown_function('auto_prune', $row['forum_id'], 'posted', $row['forum_flags'], $row['prune_days'], $row['prune_freq']);
				}
				else
				{
					auto_prune($row['forum_id'], 'posted', $row['forum_flags'], $row['prune_days'], $row['prune_freq']);
				}
			}

			if ($row['prune_viewed'])
			{
				if ($use_shutdown_function)
				{
					register_shutdown_function('auto_prune', $row['forum_id'], 'viewed', $row['forum_flags'], $row['prune_viewed'], $row['prune_freq']);
				}
				else
				{
					auto_prune($row['forum_id'], 'viewed', $row['forum_flags'], $row['prune_viewed'], $row['prune_freq']);
				}
			}
		}

	break;
}

// Unloading cache and closing db after having done the dirty work.
if ($use_shutdown_function)
{
	register_shutdown_function('garbage_collection');
}
else
{
	garbage_collection();
}

exit;

?>