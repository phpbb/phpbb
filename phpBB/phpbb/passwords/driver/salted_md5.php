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

namespace phpbb\passwords\driver;

/**
*
* @version Version 0.1 / slightly modified for phpBB 3.1.x (using $H$ as hash type identifier)
*
* Portable PHP password hashing framework.
*
* Written by Solar Designer <solar at openwall.com> in 2004-2006 and placed in
* the public domain.
*
* There's absolutely no warranty.
*
* The homepage URL for this framework is:
*
*	http://www.openwall.com/phpass/
*
* Please be sure to update the Version line if you edit this file in any way.
* It is suggested that you leave the main version number intact, but indicate
* your project name (after the slash) and add your own revision information.
*
* Please do not change the "private" password hashing method implemented in
* here, thereby making your hashes incompatible.  However, if you must, please
* change the hash type identifier (the "$P$") to something different.
*
* Obviously, since this code is in the public domain, the above are not
* requirements (there can be none), but merely suggestions.
*
*/

class salted_md5 extends base
{
	const PREFIX = '$H$';

	/**
	* {@inheritdoc}
	*/
	public function get_prefix()
	{
		return self::PREFIX;
	}

	/**
	* {@inheritdoc}
	*/
	public function is_legacy()
	{
		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function hash($password, $setting = '')
	{
		if ($setting)
		{
			if (($settings = $this->get_hash_settings($setting)) === false)
			{
				// Return md5 of password if settings do not
				// comply with our standards. This will only
				// happen if pre-determined settings are
				// directly passed to the driver. The manager
				// will not do this. Same as the old hashing
				// implementation in phpBB 3.0
				return md5($password);
			}
		}
		else
		{
			$settings = $this->get_hash_settings($this->generate_salt());
		}

		$hash = md5($settings['salt'] . $password, true);
		do
		{
			$hash = md5($hash . $password, true);
		}
		while (--$settings['count']);

		$output = $settings['full'];
		$output .= $this->helper->hash_encode64($hash, 16);

		return $output;
	}

	/**
	* {@inheritdoc}
	*/
	public function check($password, $hash, $user_row = array())
	{
		if (strlen($hash) !== 34)
		{
			return md5($password) === $hash;
		}

		return $this->helper->string_compare($hash, $this->hash($password, $hash));
	}

	/**
	* Generate salt for hashing method
	*
	* @return string Salt for hashing method
	*/
	protected function generate_salt()
	{
		$count = 6;

		$random = $this->helper->get_random_salt($count);

		$salt = $this->get_prefix();
		$salt .= $this->helper->itoa64[min($count + 5, 30)];
		$salt .= $this->helper->hash_encode64($random, $count);

		return $salt;
	}

	/**
	* Get hash settings
	*
	* @param string $hash The hash that contains the settings
	*
	* @return bool|array Array containing the count_log2, salt, and full
	*		hash settings string or false if supplied hash is empty
	*		or contains incorrect settings
	*/
	public function get_hash_settings($hash)
	{
		if (empty($hash))
		{
			return false;
		}

		$count_log2 = strpos($this->helper->itoa64, $hash[3]);
		$salt = substr($hash, 4, 8);

		if ($count_log2 < 7 || $count_log2 > 30 || strlen($salt) != 8)
		{
			return false;
		}

		return array(
			'count'	=> 1 << $count_log2,
			'salt'	=> $salt,
			'full'	=> substr($hash, 0, 12),
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_settings_only($hash, $full = false)
	{
		return substr($hash, 3, 9);
	}
}
