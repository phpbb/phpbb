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

class bcrypt_wcf2 extends base
{
	const PREFIX = '$wcf2$';

	/** @var \phpbb\passwords\driver\bcrypt */
	protected $bcrypt;

	/** @var \phpbb\passwords\driver\helper */
	protected $helper;

	/**
	* Constructor of passwords driver object
	*
	* @param \phpbb\passwords\driver\bcrypt $bcrypt Salted md5 driver
	* @param \phpbb\passwords\driver\helper $helper Password driver helper
	*/
	public function __construct(\phpbb\passwords\driver\bcrypt $bcrypt, helper $helper)
	{
		$this->bcrypt = $bcrypt;
		$this->helper = $helper;
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
	public function is_legacy()
	{
		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function hash($password, $user_row = '')
	{
		// Do not support hashing
		return false;
	}

	/**
	* {@inheritdoc}
	*/
	public function check($password, $hash, $user_row = array())
	{
		if (empty($hash) || strlen($hash) != 60)
		{
			return false;
		}
		else
		{
			$salt = substr($hash, 0, 29);

			if (strlen($salt) != 29)
			{
				return false;
			}
			// Works for standard WCF 2.x, i.e. WBB4 and similar
			return $this->helper->string_compare($hash, $this->bcrypt->hash($this->bcrypt->hash($password, $salt), $salt));
		}
	}
}
