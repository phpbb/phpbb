<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\passwords\driver;

/**
* @package passwords
*/
class bcrypt_wcf2 extends base
{
	const PREFIX = '$wcf2$';

	/** @var \phpbb\passwords\driver\bcrypt */
	protected $bcrypt;

	/** @var phpbb\passwords\driver\helper */
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
	* @inheritdoc
	*/
	public function get_prefix()
	{
		return self::PREFIX;
	}

	/**
	* @inheritdoc
	*/
	public function is_legacy()
	{
		return true;
	}

	/**
	* @inheritdoc
	*/
	public function hash($password, $user_row = '')
	{
		// Do not support hashing
		return false;
	}

	/**
	* @inheritdoc
	*/
	public function check($password, $hash, $user_row = array())
	{
		if (empty($hash))
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
			return $hash === $this->bcrypt->hash($this->bcrypt->hash($password, $salt), $salt);
		}
	}
}
