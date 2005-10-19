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

/**
* Run cron-like action
* Real cron-based layer will be introduced in 3.2
*
* @todo: check gc-intervals here too (important!)
*/
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
		
	case 'tidy_sessions':
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
			include_once($phpbb_root_path . 'includes/functions_admin.'.$phpEx);

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

// Output transparent gif
header('Cache-Control: no-cache');
header('Content-type: image/gif');
header('Content-length: 43');

echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

flush();
exit;

?>