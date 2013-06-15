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
* @package crypto
*/
class phpbb_crypto_driver_bcrypt extends phpbb_crypto_driver_base
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
		$prefix = (!$this->is_supported()) ? '$2a$' : self::PREFIX;

		if ($salt == '')
		{
			$salt = $prefix . '10$' . $this->get_random_salt();
		}

		$hash = crypt($password, $salt);
		return $hash;
	}

	/**
	* @inheritdoc
	*/
	public function check($password, $hash)
	{
		$salt = substr($hash, strpos($hash, '$', 4) + 1, 22);
		var_dump('bcrypt salt: ' . $salt . ' with length ' . strlen($salt));
		if (strlen($salt) != 22)
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
		return substr(str_replace('+', '.', bin2hex(openssl_random_pseudo_bytes(22))), 0, 22);
	}
}
