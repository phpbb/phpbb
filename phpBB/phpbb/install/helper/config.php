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

namespace phpbb\install\helper;

use phpbb\install\exception\installer_config_not_writable_exception;

/**
 * Stores common settings and installation status
 */
class config
{
	/**
	 * @var \phpbb\filesystem\filesystem_interface
	 */
	protected $filesystem;

	/**
	 * Array which contains config settings for the installer
	 *
	 * The array will also store all the user input, as well as any
	 * data that is passed to other tasks by a task.
	 *
	 * @var array
	 */
	protected $installer_config;

	/**
	 * @var string
	 */
	protected $install_config_file;

	/**
	 * @var \bantu\IniGetWrapper\IniGetWrapper
	 */
	protected $php_ini;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * Array containing progress information
	 *
	 * @var array
	 */
	protected $progress_data;

	/**
	 * Array containing system information
	 *
	 * The array contains run time and memory limitations.
	 *
	 * @var array
	 */
	protected $system_data;

	/**
	 * Array containing navigation bar information
	 *
	 * @var array
	 */
	protected $navigation_data;

	/**
	 * Flag indicating that config file should be cleaned up
	 *
	 * @var bool
	 */
	protected $do_clean_up;

	/**
	 * Constructor
	 */
	public function __construct(\phpbb\filesystem\filesystem_interface $filesystem, \bantu\IniGetWrapper\IniGetWrapper $php_ini, $phpbb_root_path)
	{
		$this->filesystem		= $filesystem;
		$this->php_ini			= $php_ini;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->do_clean_up		= false;

		// Set up data arrays
		$this->navigation_data	= array();
		$this->installer_config	= array();
		$this->system_data		= array();
		$this->progress_data	= array(
			'last_task_module_name'		=> '', // Stores the service name of the latest finished module
			'last_task_module_index'	=> 0, // Stores the index of the latest finished module
			'last_task_index'			=> 0, // Stores the index of the latest finished task
			'max_task_progress'			=> 0,
			'current_task_progress'		=> 0,
			'_restart_points'			=> array(),
			'use_restart_point'			=> false,
		);

		$this->install_config_file = $this->phpbb_root_path . 'store/install_config.php';

		$this->setup_system_data();
	}

	/**
	 * Returns data for a specified parameter
	 *
	 * @param	string	$param_name	Name of the parameter to return
	 * @param	mixed	$default	Default value to return when the specified data
	 * 								does not exist.
	 *
	 * @return 	mixed	value of the specified parameter or the default value if the data
	 * 					cannot be recovered.
	 */
	public function get($param_name, $default = false)
	{
		return (isset($this->installer_config[$param_name])) ? $this->installer_config[$param_name] : $default;
	}

	/**
	 * Sets a parameter in installer_config
	 *
	 * @param	string	$param_name	Name of the parameter
	 * @param	mixed	$value		Values to set the parameter
	 */
	public function set($param_name, $value)
	{
		$this->installer_config = array_merge($this->installer_config, array(
			$param_name => $value,
		));
	}

	/**
	 * Returns system parameter
	 *
	 * @param string	$param_name	Name of the parameter
	 *
	 * @return mixed	Returns system parameter if it is defined, false otherwise
	 */
	public function system_get($param_name)
	{
		return (isset($this->system_data[$param_name])) ? $this->system_data[$param_name] : false;
	}

	/**
	 * Returns remaining time until the run time limit
	 *
	 * @return int	Remaining time until the run time limit in seconds
	 */
	public function get_time_remaining()
	{
		if ($this->system_data['max_execution_time'] <= 0)
		{
			return PHP_INT_MAX;
		}

		return ($this->system_data['start_time'] + $this->system_data['max_execution_time']) - microtime(true);
	}

	/**
	 * Returns remaining memory available for PHP
	 *
	 * @return int	Remaining memory until reaching the limit
	 */
	public function get_memory_remaining()
	{
		if ($this->system_data['memory_limit'] <= 0)
		{
			return 1;
		}

		if (function_exists('memory_get_usage'))
		{
			return ($this->system_data['memory_limit'] - memory_get_usage());
		}

		// If we cannot get the information then just return a positive number (and cross fingers)
		return 1;
	}

	/**
	 * Saves the latest executed task
	 *
	 * @param int	$task_service_index	Index of the installer task service in the module
	 */
	public function set_finished_task($task_service_index)
	{
		$this->progress_data['last_task_index']	= $task_service_index;
	}

	/**
	 * Set active module
	 *
	 * @param string	$module_service_name	Name of the installer module service
	 * @param int		$module_service_index	Index of the installer module service
	 */
	public function set_active_module($module_service_name, $module_service_index)
	{
		$this->progress_data['last_task_module_name']	= $module_service_name;
		$this->progress_data['last_task_module_index']	= $module_service_index;
	}

	/**
	 * Getter for progress data
	 *
	 * @return array
	 */
	public function get_progress_data()
	{
		return $this->progress_data;
	}

	/**
	 * Recovers install configuration from file
	 */
	public function load_config()
	{
		if (!$this->filesystem->exists($this->install_config_file))
		{
			return;
		}

		$file_content = @file_get_contents($this->install_config_file);
		$serialized_data = trim(substr($file_content, 8));

		$installer_config = array();
		$progress_data = array();
		$navigation_data = array();

		if (!empty($serialized_data))
		{
			$unserialized_data = json_decode($serialized_data, true);

			$installer_config = (is_array($unserialized_data['installer_config'])) ? $unserialized_data['installer_config'] : array();
			$progress_data = (is_array($unserialized_data['progress_data'])) ? $unserialized_data['progress_data'] : array();
			$navigation_data = (is_array($unserialized_data['navigation_data'])) ? $unserialized_data['navigation_data'] : array();
		}

		$this->installer_config = array_merge($this->installer_config, $installer_config);
		$this->progress_data = array_merge($this->progress_data, $progress_data);
		$this->navigation_data = array_merge($this->navigation_data, $navigation_data);
	}

	/**
	 * Creates a progress restart point
	 *
	 * Restart points can be used to repeat certain tasks periodically.
	 * You need to call this method from the first task you want to repeat.
	 *
	 * @param string	$name	Name of the restart point
	 */
	public function create_progress_restart_point($name)
	{
		$tmp_progress_data = $this->progress_data;
		unset($tmp_progress_data['_restart_points']);

		$this->progress_data['_restart_points'][$name] = $tmp_progress_data;
	}

	/**
	 * Set restart point to continue from
	 *
	 * @param string	$name	Name of the restart point
	 *
	 * @return bool	Returns false if the restart point name does not exist, otherwise true
	 */
	public function jump_to_restart_point($name)
	{
		if (!isset($this->progress_data['_restart_points'][$name]) || empty($this->progress_data['_restart_points'][$name]))
		{
			return false;
		}

		foreach ($this->progress_data['_restart_points'][$name] as $key => $value)
		{
			$this->progress_data[$key] = $value;
		}

		return true;
	}

	/**
	 * Returns whether a restart point with a given name exists or not
	 *
	 * @param string	$name Name of the restart point
	 *
	 * @return bool
	 */
	public function has_restart_point($name)
	{
		return isset($this->progress_data['_restart_points'][$name]);
	}

	/**
	 * Dumps install configuration to disk
	 */
	public function save_config()
	{
		if ($this->do_clean_up)
		{
			@unlink($this->install_config_file);
			return;
		}

		// Create array to save
		$save_array = array(
			'installer_config'	=> $this->installer_config,
			'progress_data'		=> $this->progress_data,
			'navigation_data'	=> $this->navigation_data,
		);

		// Create file content
		$file_content = '<?php // ';
		$file_content .= json_encode($save_array);
		$file_content .= "\n";

		// Dump file_content to disk
		$fp = @fopen($this->install_config_file, 'w');
		if (!$fp)
		{
			throw new installer_config_not_writable_exception();
		}

		fwrite($fp, $file_content);
		fclose($fp);
		// Enforce 0600 permission for install config
		$this->filesystem->chmod([$this->install_config_file], 0600);
	}

	/**
	 * Increments the task progress
	 *
	 * @param int	$increment_by	The amount to increment by
	 */
	public function increment_current_task_progress($increment_by = 1)
	{
		$this->progress_data['current_task_progress'] += $increment_by;

		if ($this->progress_data['current_task_progress'] > $this->progress_data['max_task_progress'])
		{
			$this->progress_data['current_task_progress'] = $this->progress_data['max_task_progress'];
		}
	}

	/**
	 * Sets the task progress to a specific number
	 *
	 * @param int	$task_progress	The task progress number to be set
	 */
	public function set_current_task_progress($task_progress)
	{
		$this->progress_data['current_task_progress'] = $task_progress;
	}

	/**
	 * Sets the number of tasks belonging to the installer in the current mode.
	 *
	 * @param int	$task_progress_count	Number of tasks
	 */
	public function set_task_progress_count($task_progress_count)
	{
		$this->progress_data['max_task_progress'] = $task_progress_count;
	}

	/**
	 * Returns the number of the current task being executed
	 *
	 * @return int
	 */
	public function get_current_task_progress()
	{
		return $this->progress_data['current_task_progress'];
	}

	/**
	 * Returns the number of tasks belonging to the installer in the current mode.
	 *
	 * @return int
	 */
	public function get_task_progress_count()
	{
		return $this->progress_data['max_task_progress'];
	}

	/**
	 * Marks stage as completed in the navigation bar
	 *
	 * @param array	$nav_path	Array to the navigation elem
	 */
	public function set_finished_navigation_stage($nav_path)
	{
		if (isset($this->navigation_data['finished']) && in_array($nav_path, $this->navigation_data['finished']))
		{
			return;
		}

		$this->navigation_data['finished'][] = $nav_path;
	}

	/**
	 * Marks stage as active in the navigation bar
	 *
	 * @param array	$nav_path	Array to the navigation elem
	 */
	public function set_active_navigation_stage($nav_path)
	{
		$this->navigation_data['active'] = $nav_path;
	}

	/**
	 * Returns navigation data
	 *
	 * @return array
	 */
	public function get_navigation_data()
	{
		return $this->navigation_data;
	}

	/**
	 * Removes install config file
	 */
	public function clean_up_config_file()
	{
		$this->do_clean_up = true;
		@unlink($this->install_config_file);
	}

	/**
	 * Filling up system_data array
	 */
	protected function setup_system_data()
	{
		// Query maximum runtime from php.ini
		$execution_time = $this->php_ini->getNumeric('max_execution_time');
		$execution_time = min(15, $execution_time / 2);
		$this->system_data['max_execution_time'] = $execution_time;

		// Set start time
		$this->system_data['start_time'] = microtime(true);

		// Get memory limit
		$this->system_data['memory_limit'] = $this->php_ini->getBytes('memory_limit');
	}
}
