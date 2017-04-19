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

class bcrypt extends base
{
	const PREFIX = '$2a$';

	/** @var int Hashing cost factor */
	protected $cost_factor;

	/**
	 * Constructor of passwords driver object
	 *
	 * @param \phpbb\config\config $config phpBB config
	 * @param \phpbb\passwords\driver\helper $helper Password driver helper
	 * @param int $cost_factor Hashing cost factor (optional)
	 */
	public function __construct(\phpbb\config\config $config, helper $helper, $cost_factor = 10)
	{
		parent::__construct($config, $helper);

		// Don't allow cost factor to be below default setting
		$this->cost_factor = max(10, $cost_factor);
	}

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
	public function needs_rehash($hash)
	{
		preg_match('/^' . preg_quote($this->get_prefix()) . '([0-9]+)\$/', $hash, $matches);

		list(, $cost_factor) = $matches;

		return empty($cost_factor) || $this->cost_factor !== intval($cost_factor);
	}

	/**
	* {@inheritdoc}
	*/
	public function hash($password, $salt = '')
	{
		// The 2x and 2y prefixes of bcrypt might not be supported
		// Revert to 2a if this is the case
		$prefix = (!$this->is_supported()) ? '$2a$' : $this->get_prefix();

		// Do not support 8-bit characters with $2a$ bcrypt
		// Also see http://www.php.net/security/crypt_blowfish.php
		if ($prefix === self::PREFIX)
		{
			if (ord($password[strlen($password)-1]) & 128)
			{
				return false;
			}
		}

		if ($salt == '')
		{
			$salt = $prefix . $this->cost_factor . '$' . $this->get_random_salt();
		}

		$hash = crypt($password, $salt);
		if (strlen($hash) < 60)
		{
			return false;
		}
		return $hash;
	}

	/**
	* {@inheritdoc}
	*/
	public function check($password, $hash, $user_row = array())
	{
		$salt = substr($hash, 0, 29);
		if (strlen($salt) != 29)
		{
			return false;
		}

		if ($this->helper->string_compare($hash, $this->hash($password, $salt)))
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
	* {@inheritdoc}
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
