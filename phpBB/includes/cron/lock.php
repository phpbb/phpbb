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
* Cron lock class
* @package phpBB3
*/
class phpbb_cron_lock
{
	/**
	* Unique identifier for this lock.
	*
	* @var string
	*/
	private $cron_id;

	/**
	* Tries to acquire the cron lock by updating 
	* the 'cron_lock' configuration variable in the database.
	*
	* @return bool		true if lock was acquired
	*					false otherwise
	*/
	public function lock()
	{
		global $config, $db;

		if (!isset($config['cron_lock']))
		{
			set_config('cron_lock', '0', true);
		}

		// make sure cron doesn't run multiple times in parallel
		if ($config['cron_lock'])
		{
			// if the other process is running more than an hour already we have to assume it
			// aborted without cleaning the lock
			$time = explode(' ', $config['cron_lock']);
			$time = $time[0];

			if ($time + 3600 >= time())
			{
				return false;
			}
		}

		$this->cron_id = time() . ' ' . unique_id();

		$sql = 'UPDATE ' . CONFIG_TABLE . "
			SET config_value = '" . $db->sql_escape($this->cron_id) . "'
			WHERE config_name = 'cron_lock'
				AND config_value = '" . $db->sql_escape($config['cron_lock']) . "'";
		$db->sql_query($sql);

		// another cron process altered the table between script start and UPDATE query so exit
		if ($db->sql_affectedrows() != 1)
		{
			return false;
		}

		return true;
	}

	/**
	* Releases cron lock.
	*
	* Attempting to release a cron lock that is already released is harmless.
	*
	* @return void
	*/
	public function unlock()
	{
		global $db;

		$sql = 'UPDATE ' . CONFIG_TABLE . "
			SET config_value = '0'
			WHERE config_name = 'cron_lock'
				AND config_value = '" . $db->sql_escape($this->cron_id) . "'";
		$db->sql_query($sql);
	}
}
