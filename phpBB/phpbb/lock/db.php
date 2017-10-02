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

namespace phpbb\lock;

/**
* Database locking class
*/
class db
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
	* @var \phpbb\config\config
	*/
	private $config;

	/**
	* A database connection
	* @var \phpbb\db\driver\driver_interface
	*/
	private $db;

	/**
	* Creates a named released instance of the lock.
	*
	* You have to call acquire() to actually create the lock.
	*
	* @param	string								$config_name	A config variable to be used for locking
	* @param	\phpbb\config\config				$config			The phpBB configuration
	* @param	\phpbb\db\driver\driver_interface	$db				A database connection
	*/
	public function __construct($config_name, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db)
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

		if ($this->locked == true)
		{
			if ($this->config->ensure_lock($this->config_name, $this->unique_id))
			{
				return true;
			}
		}
		return $this->locked;
	}

	/**
	* Does this process own the lock?
	*
	* @return	bool			true if lock is owned
	*							false otherwise
	*/
	public function owns_lock()
	{
		return (bool) $this->locked;
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
	* @return null
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
