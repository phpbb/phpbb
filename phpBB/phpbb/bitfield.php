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

// I dont understand very well this class, i just moved all stuff here
// Probably the static methods can be replaced to do something
// like $var = new bitfield($data)->get($bit)
// I know phpbb_option(get/set) is for options, but i think maybe
// it could be combined with this somehow
class bitfield
{
	protected $data;

	public function __construct($bitfield = '')
	{
		$this->data = base64_decode($bitfield);
	}

	/**
	* Get option bitfield from custom data
	*
	* @param int	$bit		The bit/value to get
	* @param int	$data		Current bitfield to check
	* @return bool	Returns true if value of constant is set in bitfield, else false
	*/
	public static function optionget($bit, $data)
	{
		return ($data & 1 << (int) $bit) ? true : false;
	}

	/**
	* Set option bitfield
	*
	* @param int	$bit		The bit/value to set/unset
	* @param bool	$set		True if option should be set, false if option should be unset.
	* @param int	$data		Current bitfield to change
	*
	* @return int	The new bitfield
	*/
	public static function optionset($bit, $set, $data)
	{
		if ($set && !($data & 1 << $bit))
		{
			$data += 1 << $bit;
		}
		else if (!$set && ($data & 1 << $bit))
		{
			$data -= 1 << $bit;
		}

		return $data;
	}

	/**
	*/
	public function get($n)
	{
		// Get the ($n / 8)th char
		$byte = $n >> 3;

		if (strlen($this->data) >= $byte + 1)
		{
			$c = $this->data[$byte];

			// Lookup the ($n % 8)th bit of the byte
			$bit = 7 - ($n & 7);
			return (bool) (ord($c) & (1 << $bit));
		}
		else
		{
			return false;
		}
	}

	public function set($n)
	{
		$byte = $n >> 3;
		$bit = 7 - ($n & 7);

		if (strlen($this->data) >= $byte + 1)
		{
			$this->data[$byte] = $this->data[$byte] | chr(1 << $bit);
		}
		else
		{
			$this->data .= str_repeat("\0", $byte - strlen($this->data));
			$this->data .= chr(1 << $bit);
		}
	}

	public function clear($n)
	{
		$byte = $n >> 3;

		if (strlen($this->data) >= $byte + 1)
		{
			$bit = 7 - ($n & 7);
			$this->data[$byte] = $this->data[$byte] &~ chr(1 << $bit);
		}
	}

	public function get_blob()
	{
		return $this->data;
	}

	public function get_base64()
	{
		return base64_encode($this->data);
	}

	public function get_bin()
	{
		$bin = '';
		$len = strlen($this->data);

		for ($i = 0; $i < $len; ++$i)
		{
			$bin .= str_pad(decbin(ord($this->data[$i])), 8, '0', STR_PAD_LEFT);
		}

		return $bin;
	}

	public function get_all_set()
	{
		return array_keys(array_filter(str_split($this->get_bin())));
	}

	public function merge($bitfield)
	{
		$this->data = $this->data | $bitfield->get_blob();
	}
}
