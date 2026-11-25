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

namespace phpbb\db\middleware\mysql;

use Doctrine\DBAL\Driver;
use phpbb\db\middleware\mysql\driver as mysql_driver;

/**
 * MySQL Doctrine middleware.
 * Makes use of phpBB's MySQL specific platform.
 */
class middleware implements Driver\Middleware
{
	/** @var bool Whether a connection that fails for want of TLS is retried unencrypted */
	private bool $ssl_fallback;

	/**
	 * @param bool $ssl_fallback Whether a connection that fails for want of TLS is retried unencrypted, for the
	 *                           preferred ssl mode.
	 */
	public function __construct(bool $ssl_fallback = false)
	{
		$this->ssl_fallback = $ssl_fallback;
	}

	public function wrap(Driver $driver): Driver
	{
		return new mysql_driver($driver, $this->ssl_fallback);
	}
}
