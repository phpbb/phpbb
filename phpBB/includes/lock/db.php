<?php
/**
*
* @package phpBB3
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
* Database locking class
* @package phpBB3
*/
class phpbb_lock_db
{
	/**
	* Name of the config variable this lock uses
	* @var string
	*/
	private $config_name;

	/**
	* Unique identifier for this lock.
	*
	* @var string
	*/
	private $unique_id;

	/**
	* Stores the state of this lock
	* @var bool
	*/
	private $locked;

	/**
	* The phpBB configuration
	* @var array
	*/
	private $config;

	/**
	* A database connection
	* @var dbal
	*/
	private $db;

	/**
	* Creates a named released instance of the lock.
	*
	* You have to call lock to actually create the lock.
	*
	* @param	string	$config_name	A config variable to be used for locking
	* @param	array	$config			The phpBB configuration
	* @param	dbal	$db				A database connection
	*/
	public function __construct($config_name, $config, dbal $db)
	{
		$this->config_name = $config_name;
		$this->config = $config;
		$this->db = $db;
	}

	/**
	* Tries to acquire the cron lock by updating
	* the 'cron_lock' configuration variable in the database.
	*
	* As a lock may only be held by one process at a time, lock
	* acquisition may fail if another process is holding the lock
	* or if another process obtained the lock but never released it.
	* Locks are forcibly released after a timeout of 1 hour.
	*
	* @return	bool			true if lock was acquired
	*							false otherwise
	*/
	public function lock()
	{
		if ($this->locked)
		{
			return true;
		}

		if (!isset($this->config[$this->config_name]))
		{
			set_config($this->config_name, '0', true);
			global $config;
			$this->config = $config;
		}
		$lock_value = $this->config[$this->config_name];

		// make sure cron doesn't run multiple times in parallel
		if ($lock_value)
		{
			// if the other process is running more than an hour already we have to assume it
			// aborted without cleaning the lock
			$time = explode(' ', $lock_value);
			$time = $time[0];

			if ($time + 3600 >= time())
			{
				return false;
			}
		}

		$this->unique_id = time() . ' ' . unique_id();

		$sql = 'UPDATE ' . CONFIG_TABLE . "
			SET config_value = '" . $this->db->sql_escape($this->unique_id) . "'
			WHERE config_name = '" . $this->db->sql_escape($this->config_name) . "'
				AND config_value = '" . $this->db->sql_escape($lock_value) . "'";
		$this->db->sql_query($sql);

		if ($this->db->sql_affectedrows())
		{
			$this->locked = true;
		}
		else
		{
			// another cron process altered the table between script start and
			// UPDATE query so return false
			$this->locked = false;
		}

		return $this->locked;
	}

	/**
	* Releases the cron lock.
	*
	* The lock must have been previously obtained, that is, lock() call
	* was issued and returned true.
	*
	* Note: Attempting to release a cron lock that is already released,
	* that is, calling unlock() multiple times, is harmless.
	*
	* @return void
	*/
	public function unlock()
	{
		if ($this->locked)
		{
			$sql = 'UPDATE ' . CONFIG_TABLE . "
				SET config_value = '0'
				WHERE config_name = '" . $this->db->sql_escape($this->config_name) . "'
					AND config_value = '" . $this->db->sql_escape($this->unique_id) . "'";
			$this->db->sql_query($sql);

			$this->locked = false;
		}
	}
}
