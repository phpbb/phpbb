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
class phpbb_passwords_driver_salted_md5 extends phpbb_passwords_driver_base
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
				return false;
			}
		}
		else
		{
			if (($settings = $this->get_hash_settings($this->generate_salt())) === false)
			{
				return false;
			}
		}

		$hash = md5($settings['salt'] . $password, true);
		do
		{
			$hash = md5($hash . $password, true);
		}
		while (--$settings['count']);

		$output = $settings['full'];
		$output .= $this->helper->hash_encode64($hash, 16);

		if (strlen($output) == 34)
		{
			return $output;
		}

		// Should we really just return the md5 of the password? O.o
		return md5($password);
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
