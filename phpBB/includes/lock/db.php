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
	* @var phpbb_config
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
	public function __construct($config_name, phpbb_config $config, dbal $db)
	{
		$this->config_name = $config_name;
		$this->config = $config;
		$this->db = $db;
	}

	/**
	* Tries to acquire the lock by updating
	* the configuration variable in the database.
	*
	* As a lock may only be held by one process at a time, lock
	* acquisition may fail if another process is holding the lock
	* or if another process obtained the lock but never released it.
	* Locks are forcibly released after a timeout of 1 hour.
	*
	* @return	bool			true if lock was acquired
	*							false otherwise
	*/
	public function acquire()
	{
		if ($this->locked)
		{
			return false;
		}

		if (!isset($this->config[$this->config_name]))
		{
			$this->config->set($this->config_name, '0', false);
		}
		$lock_value = $this->config[$this->config_name];

		// make sure lock cannot be acquired by multiple processes
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

		// try to update the config value, if it was already modified by another
		// process we failed to acquire the lock.
		$this->locked = $this->config->set_atomic($this->config_name, $lock_value, $this->unique_id, false);

		return $this->locked;
	}

	/**
	* Releases the lock.
	*
	* The lock must have been previously obtained, that is, acquire() call
	* was issued and returned true.
	*
	* Note: Attempting to release a lock that is already released,
	* that is, calling release() multiple times, is harmless.
	*
	* @return void
	*/
	public function release()
	{
		if ($this->locked)
		{
			$this->config->set_atomic($this->config_name, $this->unique_id, '0', false);
			$this->locked = false;
		}
	}
}
