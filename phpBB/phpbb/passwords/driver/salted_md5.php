<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\passwords\driver;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

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

/**
* @package passwords
*/
class salted_md5 extends \phpbb\passwords\driver\base
{
	const PREFIX = '$H$';

	/**
	* @inheritdoc
	*/
	public function get_prefix()
	{
		return self::PREFIX;
	}

	/**
	* @inheritdoc
	*/
	public function hash($password, $setting = '')
	{
		if ($setting != '')
		{
			if (($settings = $this->get_hash_settings($setting)) === false)
			{
				// Return md5 of password if settings do not
				// comply with our standards. This will only
				// happen if pre-determined settings are
				// directly passed to the driver. The manager
				// will not do this. Same as the old hashing
				// implementatio in phpBB 3.0
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
	* @inheritdoc
	*/
	public function check($password, $hash)
	{
		if (strlen($hash) !== 34)
		{
			return (md5($password) === $hash) ? true : false;
		}
		// No need to check prefix, already did that in manage

		if ($hash === $this->hash($password, $hash))
		{
			return true;
		}
		return false;
	}

	/**
	* Generate salt for hashing method
	*
	* @return string Salt for hashing method
	*/
	protected function generate_salt()
	{
		$salt = '';
		$random = '';
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
	* @return array Array containing the count_log2, salt, and full hash
	*		settings string
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
	* @inheritdoc
	*/
	public function get_settings_only($hash, $full = false)
	{
		return substr($hash, 3, 9);
	}
}
