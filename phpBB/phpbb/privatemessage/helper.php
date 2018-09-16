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

namespace phpbb\privatemessage;

class helper
{
	public function rebuild_header($check_ary)
	{
		$address = array();
	
		foreach ($check_ary as $check_type => $address_field)
		{
			// Split Addresses into users and groups
			preg_match_all('/:?(u|g)_([0-9]+):?/', $address_field, $match);
	
			$u = $g = array();
			foreach ($match[1] as $id => $type)
			{
				${$type}[] = (int) $match[2][$id];
			}
	
			$_types = array('u', 'g');
			foreach ($_types as $type)
			{
				if (count(${$type}))
				{
					foreach (${$type} as $id)
					{
						$address[$type][$id] = $check_type;
					}
				}
			}
		}
	
		return $address;
	}
}
