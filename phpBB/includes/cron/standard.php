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
* Standard cron tasks
* @package phpBB3
*/
class cron_tasks_standard
{
	var $tasks = array(
		// key: cron type
		// values: config name for enable/disable flag,
		//         whether to check condition function to determine if the task can/should be run,
		//         config name for interval,
		//         config name for last run time,
		//         whether task should be considered in phpbb cron mode,
		//         whether task should be considered in system cron mode,
		//         whether task requires special code generation
		'prune_all_forums' => array(
			'custom_condition' => true,
			'run_from_system' => true,
		),
		'prune_forum' => array(
			'custom_condition' => true,
			'custom_code' => true,
		),
		'queue' => array(
			'custom_condition' => true,
			'interval_config' => 'queue_interval_config',
			'last_run_config' => 'last_queue_run',
			'run_from_phpbb' => true,
			'run_from_system' => true,
			'shutdown_function_condition' => true,
		),
		'tidy_cache' => array(
			'custom_condition' => true,
			'interval_config' => 'cache_gc',
			'last_run_config' => 'cache_last_gc',
			'run_from_phpbb' => true,
			'run_from_system' => true,
		),
		'tidy_database' => array(
			'interval_config' => 'database_gc',
			'last_run_config' => 'database_last_gc',
			'run_from_phpbb' => true,
			'run_from_system' => true,
		),
		'tidy_search' => array(
			'interval_config' => 'search_gc',
			'last_run_config' => 'search_last_gc',
			'run_from_phpbb' => true,
			'run_from_system' => true,
		),
		'tidy_sessions' => array(
			'interval_config' => 'session_gc',
			'last_run_config' => 'session_last_gc',
			'run_from_phpbb' => true,
			'run_from_system' => true,
		),
		'tidy_warnings' => array(
			'enable_config' => 'warnings_expire_days',
			'interval_config' => 'warnings_gc',
			'last_run_config' => 'warnings_last_gc',
			'run_from_phpbb' => true,
			'run_from_system' => true,
		),
	);
	
	function prune_forum_condition($forum_data) {
		return $forum_data['enable_prune'] && $forum_data['prune_next'] < time();
	}
	
	function prune_forum_code($forum_id) {
		global $phpbb_root_path, $phpEx;
		return '<img src="' . append_sid($phpbb_root_path . 'cron.' . $phpEx, 'cron_type=prune_forum&amp;f=' . $forum_id) . '" alt="cron" width="1" height="1" />';
	}
	
	function run_prune_forum() {
	}
	
	function queue_condition() {
		global $phpbb_root_path, $phpEx;
		return file_exists($phpbb_root_path . 'cache/queue.' . $phpEx);
	}
	
	function queue_shutdown_function_condition() {
		global $config;
		return !$config['smtp_delivery'];
	}
	
	function run_queue() {
		global $phpbb_root_path, $phpEx;
		include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
		$queue = new queue();
		$queue->process();
	}
	
	function tidy_cache_condition() {
		global $cache;
		return method_exists($cache, 'tidy');
	}
	
	function run_tidy_cache() {
		global $cache;
		$cache->tidy();
	}
	
	function run_tidy_database() {
		include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		tidy_database();
	}
	
	function tidy_search_condition() {
		global $phpbb_root_path, $phpEx, $config;
		
		// Select the search method
		$search_type = basename($config['search_type']);
		
		return file_exists($phpbb_root_path . 'includes/search/' . $search_type . '.' . $phpEx);
	}
	
	function run_tidy_search() {
		global $phpbb_root_path, $phpEx, $config, $error;
		
		// Select the search method
		$search_type = basename($config['search_type']);
		
		include_once("{$phpbb_root_path}includes/search/$search_type.$phpEx");

		// We do some additional checks in the module to ensure it can actually be utilised
		$error = false;
		$search = new $search_type($error);
		
		if (!$error) {
			$search->tidy();
		}
	}
	
	function run_tidy_sessions() {
		global $user;
		$user->session_gc();
	}
	
	function run_tidy_warnings() {
		include_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		tidy_warnings();
	}
}
