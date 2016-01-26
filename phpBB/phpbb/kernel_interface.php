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

namespace phpbb;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

interface kernel_interface extends HttpKernelInterface
{
	/**
	 * Boots the current kernel.
	 */
	public function boot();

	/**
	 * Shutdowns the kernel.
	 *
	 * This method is mainly useful when doing functional testing.
	 */
	public function shutdown();

	/**
	 * Gets the environment.
	 *
	 * @return string The current environment
	 */
	public function get_environment();

	/**
	 * Checks if debug mode is enabled.
	 *
	 * @return bool true if debug mode is enabled, false otherwise
	 */
	public function is_debug();

	/**
	 * Gets the phpBB root dir.
	 *
	 * @return string The phpBB root dir
	 */
	public function get_root_dir();

	/**
	 * Gets the phpBB files' extension.
	 *
	 * @return string The phpBB files' extension
	 */
	public function get_php_ext();

	/**
	 * Gets the current container.
	 *
	 * @return ContainerInterface A ContainerInterface instance
	 */
	public function get_container();

	/**
	 * Gets the request start time (not available if debug is disabled).
	 *
	 * @return int The request start timestamp
	 */
	public function get_start_time();

	/**
	 * Gets the cache directory.
	 *
	 * @return string The cache directory
	 */
	public function get_cache_dir();
}
