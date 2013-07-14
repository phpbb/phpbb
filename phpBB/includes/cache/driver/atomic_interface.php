<?php
/**
 *
 * @package acm
 * @copyright (c) 2013 phpBB Group
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
 * An interface that cache drivers can implement
 * if they provide data mechanisms for consistent data
 *
 * @package acm
 */
interface phpbb_cache_driver_atomic_interface
{
	/** This function gets a key from the cache server
	 *  and saves atomic information or sets a flag on the server
	 *  to prevent race conditions.
	 *
	 *  @param string $key key from the server to retrieve
	 *  @return string Value from the cache
	 */
	function atomic_get($key);

	/** This function saves a value to the server
	 *  using some sort of mechanism to prevent data loss.
	 *
	 *  If the driver fails, the function retries in $usleep_time
	 *  @param string $key key for the server
	 *  @param string $value new value to persist to the server
	 *  @param int $expires (in seconds) time to save the key/value. 0 for forever
	 *  @param int $retry_usleep_time (in microseconds) how long to wait to retry on failure
	 */
	function atomic_set($key, $value, $expires=0, $retry_usleep_time=10);
}
