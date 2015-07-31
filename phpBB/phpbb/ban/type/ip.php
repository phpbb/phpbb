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

use Symfony\Component\HttpFoundation\IpUtils;

class ip extends base
{
	/**
	 * {@inheritdoc}
	 */
	public function exclude_possible()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type()
	{
		return 'ip';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_user_column()
	{
		return 'user_ip';
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_ban(array $ban_list, $ban_end, $ban_exclude, $ban_reason, $ban_reason_display)
	{
		$banned_ips = array();

		foreach ($ban_list as $ban_entry)
		{
			$ban_entry = trim($ban_entry);

			// Attention, we're banning a range of IPs
			if (stripos($ban_entry, '-') !== false)
			{
				// Check 'em
				$ips = array_map('trim', explode('-', $ban_entry));
				if ((!preg_match(get_preg_expression('ipv4'), $ips[0]) || !preg_match(get_preg_expression('ipv4'), $ips[1]))
				&& (!preg_match(get_preg_expression('ipv6'), $ips[0]) || !preg_match(get_preg_expression('ipv6'), $ips[1])))
				{
					continue;
				}

				// We will convert it later, just add it for now
				$banned_ips[] = implode('-', $ips);
			}
			// we will check it for validity better later
			else if (preg_match('#^\d{1,3}(?:\.[0-9*]{1,3}){1,3}(?:/\d{1,2})?$#', $ban_entry))
			{
				$ban_item = array_map('trim', explode('/', $ban_entry, 2));

				$octets = substr_count($ban_item[0], '.') + 1;

				if ($octets > 4)
				{
					continue;
				}
				else if ($octets < 4)
				{
					$ban_item[0] .= str_repeat('.*', 4 - $octets);
				}

				// Do a dirty trick to check if it's valid:
				if (!preg_match(get_preg_expression('ipv4'), str_replace('*', '255', $ban_item[0])))
				{
					continue;
				}

				if (sizeof($ban_item == 2))
				{
					if ($ban_item[1] > 32)
					{
						continue;
					}

					$ban_item[0] = str_replace('*', '0', $ban_item[0]);
				}

				//$wildcards = substr_count($ban_entry, '*');
				//$banned_ips[] = str_replace('*', '0', $ban_entry) . '/' . (32 - $wildcards * 4);
				$banned_ip = implode('/', $ban_item);
				if (stripos($banned_ip, '/') !== false)
				{
					$banned_ip = $this->normalise_cidr($banned_ip);
				}
				$banned_ips[] = $banned_ip;
			}
			// Okay, we are really lazy here...
			else if (preg_match('#^[a-f0-9:.*]+(?:/\d{1,3})?$#', $ban_entry))
			{
				$ban_item = array_map('trim', explode('/', $ban_entry, 2));

				// Try to expand it first
				$ban_item[0] = phpbb_ip_normalise($ban_item[0]);
				if ($ban_item[0] === false)
				{
					continue;
				}

				$ban_item[0] = short_ipv6($ban_item[0], 8);

				// Did we make it?
				$blocks = substr_count($ban_item[0], ':') + 1;
				if ($blocks > 8)
				{
					continue;
				}
				else if ($blocks < 8)
				{
					$ban_entry .= str_repeat(':*', 8 - $blocks);
				}

				// The dirty trick again...
				if (!preg_match(get_preg_expression('ipv6'), str_replace('*', 'ffff', $ban_entry)))
				{
					continue;
				}

				if (sizeof($ban_item) === 2)
				{
					if ($ban_item[1] > 128)
					{
						continue;
					}

					$ban_item[0] = str_replace('*', '0', $ban_item[0]);
				}

				$banned_ip = implode('/', $ban_item);
				if (stripos($banned_ip, '/') !== false)
				{
					$banned_ip = $this->normalise_cidr($banned_ip);
				}
				$banned_ips[] = $banned_ip;
			}
			// Ban everything
			else if (preg_match('#^\*$', $ban_entry))
			{
				$banned_ips[] = '0.0.0.0/0';
				$banned_ips[] = '::/0';
			}
			else if (preg_match('#^([\w\-]\.?){2,}$#is', $ban_entry))
			{
				$ips = gethostbynamel($ban_entry);

				if (empty($ips))
				{
					continue;
				}

				foreach ($ips as $ip)
				{
					if ($ip && strlen($ip) <= 32)
					{
						$banned_ips[] = $ip;
					}
				}
			}
		}
		if (empty($banned_ips))
		{
			return false;
		}

		// @TODO
	}

	protected function normalise_cidr($cidr_range)
	{
		if (stripos($cidr_range, '/') === false)
		{
			// @TODO: throw ex
			return false;
		}

		list($ip, $subnet) = array_map('trim', explode('/', $cidr_range, 2));

		if ($subnet < 0 || (stripos($ip, ':') !== false && $subnet > 128) || (stripos($ip, '.') !== false && $subnet > 32))
		{
			// @TODO: throw ex
			return false;
		}

		if (stripos($ip, ':') !== false && $subnet < 128)
		{
			$ip = short_ipv6(phpbb_ip_normalise($ip), 8);
			$blocks = explode(':', $ip);
			if (sizeof($blocks) !== 8)
			{
				// @TODO: throw ex
				return false;
			}

			$start_block = (int) ($subnet / 16);
			$start_position = $subnet % 16;

			$start_block_bin = str_pad(base_convert($blocks[$start_block], 16, 2), 16, '0', STR_PAD_LEFT);
			$start_block_bin = substr($start_block_bin, 0, $start_position) . str_repeat('0', 16 - $start_position);
			$blocks[$start_block] = base_convert($start_block_bin, 2, 16);

			for ($i = 0; $i < 8; $i++)
			{
				if ($i > $start_block)
				{
					$blocks[$i] = '0';
				}
				else
				{
					$blocks[$i] = (int) $blocks[$i];
				}
			}

			$ip = implode(':', $blocks);
		}
		else if (stripos($ip, '.') !== false && $subnet < 32)
		{
			$ip_long = sprintf('%u', ip2long($ip));
			$ip_bin = str_pad(decbin($ip_long), 32, '0', STR_PAD_LEFT);
			$ip = long2ip(bindec(substr($ip_bin, 0, $subnet) . str_repeat('0', 32 - $subnet)));
		}

		return phpbb_ip_normalise($ip) . '/' . $subnet;
	}

	/**
	 * Converts a "normal" IP range ($start_ip - $end_ip) to an array of CIDR ranges
	 *
	 * @param string	$start_ip	The start IP of the range
	 * @param string	$end_ip		The end IP of the range
	 *
	 * @return array	An array with CIDR ranges that covers the given range
	 */
	protected function range_to_cidr($start_ip, $end_ip)
	{
		$start_ip = phpbb_ip_normalise($start_ip);
		$end_ip = phpbb_ip_normalise($end_ip);

		if ($start_ip === false || $end_ip === false)
		{
			//@TODO: throw ex
			return false;
		}

		$cidr_ranges = array();

		if (stripos($start_ip, ':') !== false && stripos($end_ip, ':') !== false)
		{
			if (!preg_match(get_preg_expression('ipv6'), $start_ip) || !preg_match(get_preg_expression('ipv6'), $end_ip))
			{
				// @TODO: throw ex
				return false;
			}

			// We're already done
			if ($start_ip === '::' && $end_ip === 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff')
			{
				return array('::/0');
			}
			else if ($start_ip === $end_ip)
			{
				return array($start_ip . '/128');
			}

			$bytes_start_ip = unpack('n*', phpbb_inet_pton($start_ip));
			$bytes_end_ip = unpack('n*', phpbb_inet_pton($end_ip));

			$swap = false;
			// Indicies start at 1 for whatever reason
			for ($i = 1; $i <= 8; $i++)
			{
				if ($bytes_start_ip[$i] > $bytes_end_ip[$i])
				{
					$swap = true;
					break;
				}
				else if ($bytes_start_ip[$i] < $bytes_end_ip[$i])
				{
					break;
				}
			}
			if ($swap)
			{
				$temp = $start_ip;
				$start_ip = $end_ip;
				$end_ip = $temp;

				$temp = $bytes_start_ip;
				$bytes_start_ip = $bytes_end_ip;
				$bytes_end_ip = $temp;
			}

			// Split the range into two parts because it wouldn't work out otherwise
			if ($bytes_start_ip[1] !== $bytes_end_ip[1] && !preg_match('#^[0-9a-f]{0,4}::$#i', $start_ip))
			{
				$new_end_ip = dechex($bytes_start_ip[1]) . ':ffff:ffff:ffff:ffff:ffff:ffff:ffff';
				$new_start_ip = dechex($bytes_start_ip[1] + 1) . '::';

				return array_merge($this->range_to_cidr($start_ip, $new_end_ip), $this->range_to_cidr($new_start_ip, $end_ip));
			}

			// Find out which powers of two fit between the start and end ip.
			$number_ips = $this->ipv6_count_ips($bytes_start_ip, $bytes_end_ip);
			$number_ips_bin = '';
			for ($i = 1; $i <= 8; $i++)
			{
				$number_ips_bin .= str_pad(decbin($number_ips[$i]), 16, '0', STR_PAD_LEFT);
			}

			// We add 1 (/128 is one address) to the end address so we can check if we reached the goal because
			// $bytes_start_ip will always contain the next start_ip, not the end of the current range
			$end_ip_check = phpbb_inet_ntop(call_user_func_array('pack', array('n*') + $this->ipv6_add_subnet($bytes_end_ip, 128)));

			if ($bytes_start_ip[1] === $bytes_end_ip[1])
			{
				// We have to begin with the smallest subnet here
				for ($i = strlen($number_ips_bin); $i > 0; $i--)
				{
					if ($number_ips_bin[$i - 1])
					{
						$ip = phpbb_inet_ntop(call_user_func_array('pack', array('n*') + $bytes_start_ip));
						$cidr_ranges[] = $ip . '/' . $i;
						$bytes_start_ip = $this->ipv6_add_subnet($bytes_start_ip, $i);

						if ($ip === $end_ip_check)
						{
							break;
						}
					}
				}
			}
			else
			{
				// And here we start with the biggest subnet, except 0
				for ($i = 1, $size = strlen($number_ips_bin); $i <= $size; $i++)
				{
					if ($number_ips_bin[$i - 1])
					{
						$ip = phpbb_inet_ntop(call_user_func_array('pack', array('n*') + $bytes_start_ip));
						$cidr_ranges[] = $ip . '/' . $i;
						$bytes_start_ip = $this->ipv6_add_subnet($bytes_start_ip, $i);

						if ($ip === $end_ip_check)
						{
							break;
						}
					}
				}
			}
		}
		else if (stripos($start_ip, '.') !== false && stripos($end_ip, '.') !== false)
		{
			if (!preg_match(get_preg_expression('ipv4'), $start_ip) || !preg_match(get_preg_expression('ipv4'), $end_ip))
			{
				// @TODO: throw ex
				return false;
			}

			if ($start_ip === '0.0.0.0' && $end_ip === '255.255.255.255')
			{
				return array('0.0.0.0/0');
			}
			else if ($start_ip === $end_ip)
			{
				return array($start_ip . '/32');
			}

			$blocks_start_ip = explode('.', $start_ip);
			$blocks_end_ip = explode('.', $end_ip);

			$swap = false;
			for ($i = 0; $i < 4; $i++)
			{
				if ($blocks_start_ip[$i] > $blocks_end_ip[$i])
				{
					$swap = true;
					break;
				}
				else if ($blocks_start_ip[$i] < $blocks_end_ip[$i])
				{
					break;
				}
			}
			if ($swap)
			{
				$temp = $start_ip;
				$start_ip = $end_ip;
				$end_ip = $temp;

				$temp = $blocks_start_ip;
				$blocks_start_ip = $blocks_end_ip;
				$blocks_end_ip = $temp;
			}

			// Split the range into two parts because it wouldn't work out otherwise
			if ($blocks_start_ip[0] !== $blocks_end_ip[0] && !preg_match('#\d{1,3}\.0\.0\.0#', $start_ip))
			{
				$new_end_ip = $blocks_start_ip[0] . '.255.255.255';
				$new_start_ip = ($blocks_start_ip[0] + 1) . '.0.0.0';

				return array_merge($this->range_to_cidr($start_ip, $new_end_ip), $this->range_to_cidr($new_start_ip, $end_ip));
			}

			$start_ip_long = ip2long($start_ip);
			$end_ip_long = ip2long($end_ip) + 1;

			$number_ips = $end_ip_long - $start_ip_long + 1;
			$number_ips_bin = str_pad(decbin($number_ips), 32, '0', STR_PAD_LEFT);

			if ($blocks_start_ip[0] === $blocks_end_ip[0])
			{
				for ($i = strlen($number_ips_bin); $i > 0; $i--)
				{
					if ($number_ips_bin[$i - 1])
					{
						$cidr_ranges[] = long2ip($start_ip_long) . '/' . $i;
						$start_ip_long += (1 << 32 - $i);

						if ($start_ip_long === $end_ip_long + 1)
						{
							break;
						}
					}
				}
			}
			else
			{
				for ($i = 1, $size = strlen($number_ips_bin); $i <= $size; $i++)
				{
					if ($number_ips_bin[$i - 1])
					{
						$cidr_ranges[] = long2ip($start_ip_long) . '/' . $i;
						$start_ip_long += (1 << 32 - $i);

						if ($start_ip_long === $end_ip_long + 1)
						{
							break;
						}
					}
				}
			}
		}
		else
		{
			// @TODO: throw ex
			return false;
		}

		return $cidr_ranges;
	}

	/**
	 * Adds a subnet to an IPv6 address in its unpacked form
	 *
	 * @param array		$bytes_ip	The IPv6 address in its unpackd form (format: n*)
	 * @param int		$subnet		The subnet which should be added
	 *
	 * @return array	Returns the IPv6 address with the added subnet in its unpacked form
	 */
	protected function ipv6_add_subnet(array $bytes_ip, $subnet)
	{
		if (empty($bytes_ip))
		{
			// @TODO: throw ex
			return false;
		}
		if ($subnet < 1 || $subnet > 128)
		{
			// @TODO: throw ex
			return false;
		}

		$block = (int) ceil($subnet / 16);
		$position = (128 - $subnet) % 16;

		$bytes_ip[$block] += 1 << $position;
		// If we got a carry after adding 2^x to the block, we remove it and add one to the next block
		if (($bytes_ip[$block] & 0x10000) === 0x10000)
		{
			$bytes_ip[$block] &= 0xFFFF;
			for ($i = $block - 1; $i >= 0; $i--)
			{
				$bytes_ip[$i] += 1;
				// ... and so on...
				if (($bytes_ip[$i] & 0x10000) !== 0x10000)
				{
					break;
				}
				$bytes_ip[$i] &= 0xFFFF;
			}
		}

		return $bytes_ip;
	}

	/**
	 * Gets the number of IPs between two IPv6 addresses in their unpacked form (format: n*)
	 *
	 * @param array 	$bytes_ip1	The first IPv6 address
	 * @param array 	$bytes_ip2	The second IPv6 address
	 *
	 * @return array	The difference in the same format
	 */
	protected function ipv6_count_ips(array $bytes_ip1, array $bytes_ip2)
	{
		$swap = false;
		for ($i = 1; $i <= 8; $i++)
		{
			if ($bytes_ip1[$i] > $bytes_ip2[$i])
			{
				$swap = true;
				break;
			}
			else if ($bytes_ip1[$i] < $bytes_ip2[$i])
			{
				break;
			}
		}
		if ($swap)
		{
			$temp = $bytes_ip1;
			$bytes_ip1 = $bytes_ip2;
			$bytes_ip2 = $temp;
		}

		// We will overwrite all blocks anyway, we just need to keep the order
		$diff_blocks = $bytes_ip1;
		// Set carry to -1 to add 1 to the overall result
		$carry = -1;

		for ($i = 8; $i >= 1; $i--)
		{
			$diff = $bytes_ip2[$i] - $bytes_ip1[$i] - $carry;
			// We would have a strange result for 0xFFFF - 0x0000 - (-1)
			$carry = ($diff === 0x10000) ? -1 : (($diff < 0) ? 1 : 0);
			// Add 2^16 and modulo 2^16 because 2^16 is as high as we can get.
			$diff_blocks[$i] = ($diff + 0x10000) % 0x10000;
		}

		return $diff_blocks;
	}
}
