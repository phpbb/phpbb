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

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\ServerVersionProvider;
use phpbb\db\mysql_ssl;

/**
 * MySQL Doctrine driver middleware.
 * Makes use of phpBB's MySQL specific platform, and retries a preferred ssl connection unencrypted when the server
 * does not offer TLS.
 */
class driver extends AbstractDriverMiddleware
{
	/** @var bool Whether a connection that fails for want of TLS is retried unencrypted */
	private bool $ssl_fallback;

	/**
	 * @param DriverInterface	$wrapped_driver	The driver to wrap.
	 * @param bool				$ssl_fallback	Whether a connection that fails for want of TLS is retried unencrypted.
	 */
	public function __construct(DriverInterface $wrapped_driver, bool $ssl_fallback = false)
	{
		parent::__construct($wrapped_driver);
		$this->ssl_fallback = $ssl_fallback;
	}

	/**
	 * {@inheritDoc}
	 */
	public function connect(array $params): Connection
	{
		try
		{
			return parent::connect($params);
		}
		catch (Exception $e)
		{
			if (!$this->ssl_fallback || !mysql_ssl::is_unavailable((int) $e->getCode(), $e->getMessage()))
			{
				throw $e;
			}
		}

		foreach (mysql_ssl::get_pdo_attributes() as $attribute)
		{
			unset($params['driverOptions'][$attribute]);
		}

		return parent::connect($params);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDatabasePlatform(ServerVersionProvider $versionProvider): AbstractPlatform
	{
		return new platform();
	}
}
