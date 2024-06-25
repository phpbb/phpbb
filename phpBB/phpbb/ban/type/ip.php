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

namespace phpbb\ban\type;

use phpbb\ban\exception\no_valid_ips_exception;
use Symfony\Component\HttpFoundation\IpUtils;

class ip extends base
{
	private const USER_IP = 'user_ip';

	/**
	 * @inheritDoc
	 */
	public function get_type(): string
	{
		return 'ip';
	}

	/**
	 * @inheritDoc
	 */
	public function get_user_column(): ?string
	{
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function check(array $ban_rows, array $user_data)
	{
		if (!isset($user_data[self::USER_IP]))
		{
			return false;
		}

		foreach ($ban_rows as $ip_ban)
		{
			if (IpUtils::checkIp($user_data[self::USER_IP], $ip_ban['item']))
			{
				return $ip_ban;
			}
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_for_storage(array $items): array
	{
		$ban_items = [];
		foreach ($items as $ip)
		{
			try
			{
				// Misuse checkIp for checking validity of IP. Should return true if defined IP is valid.
				if (!IpUtils::checkIp($ip, $ip))
				{
					continue;
				}

				$ban_items[] = $ip;
			}
			// @codeCoverageIgnoreStart
			catch (\RuntimeException $exception)
			{
				// IPv6 not supported, therefore IPv6 address will not be added
			}
			// @codeCoverageIgnoreEnd
		}

		if (empty($ban_items))
		{
			throw new no_valid_ips_exception('NO_IPS_DEFINED');
		}

		return $ban_items;
	}
}
