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

	/**
	 * Normalises a CIDR notation so 127.244.200.4/8 will get 127.0.0.0/8
	 *
	 * @param string	$cidr_range		The CIDR notation to be normalised
	 *
	 * @return string	The normalised CIDR notation
	 */
	protected function normalise_cidr($cidr_range)
	{
		if (stripos($cidr_range, '/') === false)
		{
			$cidr_range .= '/' . (stripos($cidr_range, ':') !== false) ? 128 : 32;
		}

		list($ip, $subnet) = array_map('trim', explode('/', $cidr_range, 2));
		$ip_in_addr = phpbb_inet_pton($ip);

		if ($ip_in_addr === false)
		{
			// @TODO: throw ex, malformed ip address
			return false;
		}

		$ip_blocks = unpack('n*', $ip_in_addr);
		$size = sizeof($ip_blocks);
		// 16 bits per array element
		$max_subnet = $size * 16;

		if ($subnet < 0 || $subnet > $max_subnet)
		{
			// @TODO: throw ex
			return false;
		}

		// If it's /32 for IPv4 or /128 for IPv6 we don't have anything to do here
		if ($subnet < $max_subnet)
		{
			$start_block = (int) ceil($subnet / 16);
			$position = ($max_subnet - $subnet) % 16;

			// Indicies start at 1
			if ($start_block > 0)
			{
				// Clear last $position bits, the simple way
				$ip_blocks[$start_block] >>= $position;
				$ip_blocks[$start_block] <<= $position;
			}

			// Clear the other blocks as well
			for ($i = $start_block + 1; $i <= $size; $i++)
			{
				$ip_blocks[$i] = 0;
			}
		}

		// We have to convert it back to an IP like this because pack doesn't want an array as a result
		return phpbb_inet_ntop(call_user_func_array('pack', array('n*') + $ip_blocks)) . '/' . $subnet;
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
		$start_ip_in_addr = phpbb_inet_pton($start_ip);
		$end_ip_in_addr = phpbb_inet_pton($end_ip);

		if ($start_ip_in_addr === false || $end_ip_in_addr === false)
		{
			//@TODO: throw ex, malformed ip address
			return false;
		}

		$start_ip_blocks = unpack('n*', $start_ip_in_addr);
		$end_ip_blocks = unpack('n*', $end_ip_in_addr);

		$size = sizeof($start_ip_blocks);
		if ($size !== sizeof($end_ip_blocks))
		{
			//@TODO: throw ex, range covers IPv4 and IPv6 addresses at the same time
			return false;
		}
		// 16 bits in an array element
		$max_subnet = $size * 16;

		// We're already done
		if ($start_ip_in_addr === $end_ip_in_addr)
		{
			return array(phpbb_inet_ntop($start_ip_in_addr) . '/' . $max_subnet);
		}
		else if ($this->ip_compare_blocks($start_ip_blocks, 0) && $this->ip_compare_blocks($end_ip_blocks, 0xFFFF))
		{
			return array(phpbb_inet_ntop($start_ip_in_addr) . '/0');
		}

		$swap = false;
		// Indicies start at 1 for whatever reason
		for ($i = 1; $i <= $size; $i++)
		{
			if ($start_ip_blocks[$i] > $end_ip_blocks[$i])
			{
				$swap = true;
				break;
			}
			else if ($start_ip_blocks[$i] < $end_ip_blocks[$i])
			{
				break;
			}
		}
		if ($swap)
		{
			$temp = $start_ip;
			$start_ip = $end_ip;
			$end_ip = $temp;

			$temp = $start_ip_blocks;
			$start_ip_blocks = $end_ip_blocks;
			$end_ip_blocks = $temp;
		}

		// Have to seperate IPv4 and IPv6 here
		$first_block_equals = ($size === 2) ? (($start_ip_blocks[1] & 0xFF00) === ($end_ip_blocks[1] & 0xFF00)) : ($start_ip_blocks[1] === $end_ip_blocks[1]);

		// Split the range into two parts because it wouldn't work out otherwise
		if (!$first_block_equals && !$this->ip_compare_blocks($start_ip_blocks, 0, 2))
		{
			$new_end_ip_blocks = $new_start_ip_blocks = $start_ip_blocks;

			// Increment first block to get next /16 range (in IPv6)
			$new_start_ip_blocks[1]++;

			// Handling IPv4 here
			if ($size === 2)
			{
				// Second octet should be 255
				$new_end_ip_blocks[1] |= 0xFF;

				// Clear second octet and add 1 to first octet
				$new_start_ip_blocks[1] >>= 8;
				$new_start_ip_blocks[1]++;
				$new_start_ip_blocks[1] <<= 8;
				// We don't have to handle an overflow or something like that, because we made sure that $start_ip
				// hasn't 255 as its first block. ($end_ip would have to have 256 because they must not be the same)
			}

			for ($i = 2; $i <= $size; $i++)
			{
				$new_end_ip_blocks[$i] = 0xFFFF;
				$new_start_ip_blocks[$i] = 0;
			}

			$new_end_ip = phpbb_inet_ntop(call_user_func_array('pack', array('n*') + $new_end_ip_blocks));
			$new_start_ip = phpbb_inet_ntop(call_user_func_array('pack', array('n*') + $new_start_ip_blocks));

			return array_merge($this->range_to_cidr($start_ip, $new_end_ip), $this->range_to_cidr($new_start_ip, $end_ip));
		}

		// Find out which powers of two fit between the start and end ip.
		$number_ips = $this->ip_count($start_ip_blocks, $end_ip_blocks);
		$subnets = array();
		for ($i = 1; $i <= $size; $i++)
		{
			$number_block_bin = decbin($number_ips[$i]);
			for ($j = 0, $bits = strlen($number_block_bin); $j < $bits; $j++)
			{
				if ($number_block_bin[$j])
				{
					// (current block - 1) * 16 bits + missing bits + $j'th bit + 1 (because we don't include the subnet /0)
					$subnets[] = ($i - 1) * 16 + (16 - $bits) + $j + 1;
				}
			}
		}

		// We're handling ranges within the same block - reverse array to work from smallest to biggest subnet
		if ($first_block_equals)
		{
			$subnets = array_reverse($subnets);
		}

		// We add 1 (smallest subnet) to the end address so we can check if we reached the goal because
		// $bytes_start_ip will always contain the next start_ip, not the end of the current range
		$end_ip_check = phpbb_inet_ntop(call_user_func_array('pack', array('n*') + $this->ip_add_subnet($end_ip_blocks, $max_subnet)));

		$cidr_ranges = array();

		foreach ($subnets as $subnet)
		{
			$ip = phpbb_inet_ntop(call_user_func_array('pack', array('n*') + $start_ip_blocks));
			$cidr_ranges[] = $ip . '/' . $subnet;
			$start_ip_blocks = $this->ip_add_subnet($start_ip_blocks, $subnet);

			if ($ip === $end_ip_check)
			{
				break;
			}
		}

		return $cidr_ranges;
	}

	/**
	 * Adds a subnet to an IP address in its unpacked form
	 *
	 * @param array		$bytes_ip	The IP address in its unpacked form (format: n*)
	 * @param int		$subnet		The subnet which should be added
	 *
	 * @return array	Returns the IP address with the added subnet in its unpacked form
	 */
	protected function ip_add_subnet(array $bytes_ip, $subnet)
	{
		if (empty($bytes_ip))
		{
			// @TODO: throw ex
			return false;
		}

		// 16 bits per array element
		$max_subnet = sizeof($bytes_ip) * 16;

		if ($subnet < 1 || $subnet > $max_subnet)
		{
			// @TODO: throw ex
			return false;
		}

		$block = (int) ceil($subnet / 16);
		$position = ($max_subnet - $subnet) % 16;

		$bytes_ip[$block] += 1 << $position;
		// If we got a carry after adding 2^x to the block, we remove it and add one to the next block
		if (($bytes_ip[$block] & 0x10000) === 0x10000)
		{
			$bytes_ip[$block] &= 0xFFFF;
			for ($i = $block - 1; $i >= 1; $i--)
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
	 * Compares every block of the IP address in its unpacked form with $value, beginning at $start
	 *
	 * @param array		$bytes_ip	The IP address in its unpacked form (format: n*)
	 * @param int		$value		The value to check with
	 * @param int		$start		The block index to start at (note: indicies start at 1)
	 *
	 * @return bool		Returns true if all blocks have the same value as $value
	 */
	protected function ip_compare_blocks(array $bytes_ip, $value, $start = 1)
	{
		if (empty($bytes_ip))
		{
			// @TODO: throw ex
			return false;
		}

		for ($i = $start, $size = sizeof($bytes_ip); $i <= $size; $i++)
		{
			if ($bytes_ip[$i] !== $value)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Gets the number of IPs between two IP addresses in their unpacked form (format: n*)
	 *
	 * @param array 	$bytes_ip1	The first IP address
	 * @param array 	$bytes_ip2	The second IP address
	 *
	 * @return array	The difference in the same format
	 */
	protected function ip_count(array $bytes_ip1, array $bytes_ip2)
	{
		$size = sizeof($bytes_ip1);
		if ($size !== sizeof($bytes_ip2))
		{
			// @TODO: throw ex
			return false;
		}

		$swap = false;
		for ($i = 1; $i <= $size; $i++)
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

		$diff_blocks = array();
		// Set carry to -1 to add 1 to the overall result
		$carry = -1;

		for ($i = $size; $i >= 1; $i--)
		{
			$diff = $bytes_ip2[$i] - $bytes_ip1[$i] - $carry;
			// We would have a strange result for 0xFFFF - 0x0000 - (-1)
			$carry = ($diff === 0x10000) ? -1 : (($diff < 0) ? 1 : 0);
			// Add 2^16 and modulo 2^16 because 2^16 is as high as we can get per element
			$diff_blocks[$i] = ($diff + 0x10000) % 0x10000;
		}

		// Restore the correct order
		return array_reverse($diff_blocks, true);
	}
}
