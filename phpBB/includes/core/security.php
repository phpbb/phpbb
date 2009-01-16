<?php
/**
*
* @package core
* @version $Id: core.php 9200 2008-12-15 18:06:53Z acydburn $
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit();
}
/**
* Class for generating random numbers, unique ids, unique keys, seeds, hashes...
* @package core
*/
class phpbb_security extends phpbb_plugin_support
{
	/**
	* @var array required phpBB objects
	*/
	public $phpbb_required = array();

	/**
	* @var array Optional phpBB objects
	*/
	public $phpbb_optional = array('config');

	/**
	* @var string Used hash type. The default type is $P$, phpBB uses a different one.
	*/
	public $hash_type = '$H$';

	/**
	* @var bool Is true if random seed got updated.
	*/
	private $dss_seeded = false;

	/**
	* Constructor
	* @access public
	*/
	public function __construct() {}

	/**
	* Generates an alphanumeric random string of given length
	*
	* @param int	$num_chars	Number of characters to return
	* @return string	Random string of $num_chars characters.
	* @access public
	*/
	public function gen_rand_string($num_chars = 8)
	{
		$rand_str = $this->unique_id();
		$rand_str = str_replace('0', 'Z', strtoupper(base_convert($rand_str, 16, 35)));

		return substr($rand_str, 0, $num_chars);
	}

	/**
	* Return unique id
	*
	* @param string	$extra	Additional entropy
	* @return string	Unique id
	* @access public
	*/
	public function unique_id($extra = 'c')
	{
		if (!isset(phpbb::$config['rand_seed']))
		{
			$val = md5(md5($extra) . microtime());
			$val = md5(md5($extra) . $val . $extra);
			return substr($val, 4, 16);
		}


		$val = phpbb::$config['rand_seed'] . microtime();
		$val = md5($val);
		phpbb::$config['rand_seed'] = md5(phpbb::$config['rand_seed'] . $val . $extra);

		if (!$this->dss_seeded && phpbb::$config['rand_seed_last_update'] < time() - rand(1, 10))
		{
			set_config('rand_seed', phpbb::$config['rand_seed'], true);
			set_config('rand_seed_last_update', time(), true);

			$this->dss_seeded = true;
		}

		return substr($val, 4, 16);
	}

	/**
	* Hash passwords
	*
	* @version Version 0.1
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
	* @param string	$password	Password to hash
	* @return string	Hashed password
	* @access public
	*/
	public function hash_password($password)
	{
		$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

		$random_state = $this->unique_id();
		$random = '';
		$count = 6;

		if (($fh = @fopen('/dev/urandom', 'rb')))
		{
			$random = fread($fh, $count);
			fclose($fh);
		}

		if (strlen($random) < $count)
		{
			$random = '';

			for ($i = 0; $i < $count; $i += 16)
			{
				$random_state = md5($this->unique_id() . $random_state);
				$random .= pack('H*', md5($random_state));
			}
			$random = substr($random, 0, $count);
		}

		$hash = $this->_hash_crypt_private($password, $this->_hash_gensalt_private($random, $itoa64), $itoa64);
		$result = (strlen($hash) == 34) ? $hash : md5($password);

		return $result;
	}

	/**
	* Check for correct password
	*
	* If the hash length is != 34, then a md5($password) === $hash comparison is done. The correct hash length is 34.
	*
	* @param string	$password	The password in plain text
	* @param string	$hash		The stored password hash
	*
	* @return bool	Returns true if the password is correct, false if not.
	* @access public
	*/
	public function check_password($password, $hash)
	{
		$itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		if (strlen($hash) == 34)
		{
			$result = ($this->_hash_crypt_private($password, $hash, $itoa64) === $hash) ? true : false;
		}
		else
		{
			$result = (md5($password) === $hash) ? true : false;
		}

		return $result;
	}

	/**
	* Generate salt for hash generation
	* @access private
	*/
	private function _hash_gensalt_private($input, &$itoa64, $iteration_count_log2 = 6)
	{
		if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31)
		{
			$iteration_count_log2 = 8;
		}

		$output = $this->hash_type;
		$output .= $itoa64[min($iteration_count_log2 + 5, 30)];
		$output .= $this->_hash_encode64($input, 6, $itoa64);

		return $output;
	}

	/**
	* Encode hash
	* @access private
	*/
	private function _hash_encode64($input, $count, &$itoa64)
	{
		$output = '';
		$i = 0;

		do
		{
			$value = ord($input[$i++]);
			$output .= $itoa64[$value & 0x3f];

			if ($i < $count)
			{
				$value |= ord($input[$i]) << 8;
			}

			$output .= $itoa64[($value >> 6) & 0x3f];

			if ($i++ >= $count)
			{
				break;
			}

			if ($i < $count)
			{
				$value |= ord($input[$i]) << 16;
			}

			$output .= $itoa64[($value >> 12) & 0x3f];

			if ($i++ >= $count)
			{
				break;
			}

			$output .= $itoa64[($value >> 18) & 0x3f];
		}
		while ($i < $count);

		return $output;
	}

	/**
	* The crypt function/replacement
	* @access private
	*/
	private function _hash_crypt_private($password, $setting, &$itoa64)
	{
		$output = '*';

		// Check for correct hash
		if (substr($setting, 0, 3) != $this->hash_type)
		{
			return $output;
		}

		$count_log2 = strpos($itoa64, $setting[3]);

		if ($count_log2 < 7 || $count_log2 > 30)
		{
			return $output;
		}

		$count = 1 << $count_log2;
		$salt = substr($setting, 4, 8);

		if (strlen($salt) != 8)
		{
			return $output;
		}

		/**
		* We're kind of forced to use MD5 here since it's the only
		* cryptographic primitive available in all versions of PHP
		* currently in use.  To implement our own low-level crypto
		* in PHP would result in much worse performance and
		* consequently in lower iteration counts and hashes that are
		* quicker to crack (by non-PHP code).
		*/
		$hash = md5($salt . $password, true);
		do
		{
			$hash = md5($hash . $password, true);
		}
		while (--$count);

		$output = substr($setting, 0, 12);
		$output .= $this->_hash_encode64($hash, 16, $itoa64);

		return $output;
	}
}

?>