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
	/** This function performs an operation with data
	 *  using some sort of mechanism to prevent data loss-
	 *  repeating the operation when there is a conflict.
	 *
	 *  If the driver fails, the function retries in $usleep_time
	 *  @param string $key key for the server
	 *  @param callable $operation takes (string $data) returns (string $data)
	 *  @param int $retry_usleep_time (in microseconds) how long to wait to retry on failure
	 */
	function atomic_operation($key, Closure $operation, $retry_usleep_time=10);
}
