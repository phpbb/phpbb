<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package passwords
*/
class phpbb_passwords_driver_bcrypt extends phpbb_passwords_driver_base
{
	const PREFIX = '$2a$';

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
	public function get_type()
	{
		return get_class($this);
	}

	/**
	* @inheritdoc
	*/
	public function hash($password, $salt = '')
	{
		// The 2x and 2y prefixes of bcrypt might not be supported
		// Revert to 2a if this is the case
		$prefix = (!$this->is_supported()) ? '$2a$' : $this->get_prefix();

		if ($salt == '')
		{
			$salt = $prefix . '10$' . $this->get_random_salt();
		}

		$hash = crypt($password, $salt);
		if (strlen($hash) < 60)
		{
			return false;
		}
		return $hash;
	}

	/**
	* @inheritdoc
	*/
	public function check($password, $hash)
	{
		$salt = substr($hash, 0, 29);
		if (strlen($salt) != 29)
		{
			return false;
		}

		if ($hash == $this->hash($password, $salt))
		{
			return true;
		}
		return false;
	}

	/**
	* Get a random salt value with a length of 22 characters
	*
	* @return string Salt for password hashing
	*/
	protected function get_random_salt()
	{
		return $this->helper->hash_encode64($this->helper->get_random_salt(22), 22);
	}

	/**
	* @inheritdoc
	*/
	public function get_settings_only($hash, $full = false)
	{
		if ($full)
		{
			$pos = stripos($hash, '$', 1) + 1;
			$length = 22 + (strripos($hash, '$') + 1 - $pos);
		}
		else
		{
			$pos = strripos($hash, '$') + 1;
			$length = 22;
		}
		return substr($hash, $pos, $length);
	}
}
