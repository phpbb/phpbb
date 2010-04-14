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
* Cron class
* @package phpBB3
*/
class cron
{
	var $tasks = array();
	
	function cron() {
		global $config, $phpbb_root_path, $phpEx;
		$modules = $config['cron_modules'];
		$modules = explode(',', $modules);
		foreach ($modules as $module) {
			// explode will return array("") when exploding an empty string;
			// users may also specify something like foo,,bar.
			// Account for module being possibly empty
			if (!empty($module)) {
				// Misspelling or specifying nonexistent modules here may make the board
				// unusable due to error messages screwing up header output
				include_once($phpbb_root_path . "includes/cron/$module.$phpEx");
				$cron_class = "cron_tasks_$module";
				$object = new $cron_class;
				foreach ($object->tasks as $cron_type => $params) {
					$params['object'] = $object;
					$this->tasks[$cron_type] = $params;
				}
			}
		}
	}
	
	function is_valid_task($cron_type) {
		return isset($this->tasks[$cron_type]);
	}
	
	function is_task_runnable($cron_type, $args=null) {
		global $config;
		$time_now = time();
		$cron_params = $this->tasks[$cron_type];
		if ($cron_params['enable_config'] && !$config[$cron_params['enable_config']]) {
			return false;
		}
		if ($cron_param['custom_condition']) {
			$callable = array($cron_params['object'], $cron_type . '_condition');
			if ($args) {
				$answer = call_user_func_array($callable, $args);
			} else {
				$answer = call_user_func($callable);
			}
			if (!$answer) {
				return false;
			}
		}
		if ($time_now - $config[$cron_params['interval_config']] > $config[$cron_params['last_run_config']]) {
			return true;
		}
		return false;
	}
	
	function is_task_shutdown_function_compatible($cron_type) {
		$cron_params = $this->tasks[$cron_type];
		if (isset($cron_params['shutdown_function_condition'])) {
			return call_user_func(array($cron_params->object, $cron_type . '_shutdown_function_condition'));
		} else {
			return true;
		}
	}
	
	function determine_cron_mode_param() {
		global $config;
		if ($config['use_system_cron']) {
			$mode = 'run_from_system';
		} else {
			$mode_param = 'run_from_phpbb';
		}
		return $mode_param;
	}
	
	function find_one_runnable_task() {
		$mode_param = $this->determine_cron_mode_param();
		foreach ($this->tasks as $cron_type => $cron_params) {
			if ($cron_params[$mode_param] && $this->is_task_runnable($cron_type)) {
				return $cron_type;
			}
		}
		return null;
	}
	
	function find_all_runnable_tasks() {
		$mode_param = $this->determine_cron_mode_param();
		$tasks = array();
		foreach ($this->tasks as $cron_type => $cron_params) {
			if ($cron_params[$mode_param] && $this->is_task_runnable($cron_type)) {
				$tasks[] = $cron_type;
			}
		}
		return $tasks;
	}
	
	function generate_task_code($cron_type, $args=array()) {
		$cron_params = $this->tasks[$cron_type];
		if ($cron_params['custom_code']) {
			$code = call_user_func_array(array($cron_params['object'], $cron_type . '_code'), $args);
		} else {
			$code = $this->generate_generic_task_code($cron_type);
		}
		return $code;
	}
	
	function generate_generic_task_code($cron_type) {
		global $phpbb_root_path, $phpEx;
		return '<img src="' . append_sid($phpbb_root_path . 'cron.' . $phpEx, 'cron_type=' . $cron_type) . '" width="1" height="1" alt="cron" />';
	}
	
	function run_task($cron_type) {
		call_user_func(array($this->tasks[$cron_type]['object'], 'run_' . $cron_type));
	}
}
