<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
* File locking class
* @package phpBB3
*/
class phpbb_lock_flock
{
	/**
	* Path to the file access to which is controlled
	*
	* @var string
	*/
	private $path;

	/**
	* File pointer for the lock file
	* @var string
	*/
	private $lock_fp;

	/**
	* Creates an instance of the lock.
	*
	* You have to call acquire() to actually create the lock.
	*
	* @param	string	$path	Path to the file access to which is controlled
	*/
	public function __construct($path)
	{
		$this->path = $path;
		$this->lock_fp = null;
	}

	/**
	* Tries to acquire the lock.
	*
	* As a lock may only be held by one process at a time, lock
	* acquisition may fail if another process is holding the lock.
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

		// For systems that can't have two processes opening
		// one file for writing simultaneously
		if (file_exists($this->path . '.lock'))
		{
			$mode = 'rb';
		}
		else
		{
			$mode = 'wb';
		}

		$this->lock_fp = @fopen($this->path . '.lock', $mode);

		if ($mode == 'wb')
		{
			if (!$this->lock_fp)
			{
				// Two processes may attempt to create lock file at the same time.
				// Have the losing process try opening the lock file again for reading
				// on the assumption that the winning process created it
				$mode = 'rb';
				$this->lock_fp = @fopen($this->path . '.lock', $mode);
			}
			else
			{
				// Only need to set mode when the lock file is written
				@chmod($this->path . '.lock', 0666);
			}
		}

		if ($this->lock_fp)
		{
			@flock($this->lock_fp, LOCK_EX);
		}

		return (bool) $this->lock_fp;
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
		if ($this->lock_fp)
		{
			@flock($this->lock_fp, LOCK_UN);
			fclose($this->lock_fp);
			$this->lock_fp = null;
		}
	}
}
