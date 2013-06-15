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
class phpbb_crypto_driver_salted_md5 extends phpbb_crypto_driver_base
{
	protected $itoa = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

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
	public function get_type()
	{
		return get_class($this);
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
		$output .= _hash_encode64($hash, 16, $this->itoa);

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
		if (strlen($hash) != 34)
		{
			return false;
		}
		// No need to check prefix, already did that in manage

		if ($hash === $this->hash($password, $hash))
		{
			return true;
		}
		return false;
	}

	/**
	* Return unique id
	* @param string $extra additional entropy
	*/
	protected function unique_id($extra = 'c')
	{
		static $dss_seeded = false;

		$val = $this->config['rand_seed'] . microtime();
		$val = md5($val);
		$this->config['rand_seed'] = md5($this->config['rand_seed'] . $val . $extra);

		if ($dss_seeded !== true && ($this->config['rand_seed_last_update'] < time() - rand(1,10)))
		{
			set_config('rand_seed_last_update', time(), true);
			set_config('rand_seed', $this->config['rand_seed'], true);
			$dss_seeded = true;
		}

		return substr($val, 4, 16);
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

		if (($fh = @fopen('/dev/urandom', 'rb')))
		{
			$random = fread($fh, $count);
			fclose($fh);
		}

		if (strlen($random) < $count)
		{
			$random = '';
			$random_state = unique_id();

			for ($i = 0; $i < $count; $i += 16)
			{
				$random_state = md5(unique_id() . $random_state);
				$random .= pack('H*', md5($random_state));
			}
			$random = substr($random, 0, $count);
		}

		$salt = '$H$';
		$salt .= $this->itoa[min($count + 5, 30)];
		$salt .= _hash_encode64($random, 6, $this->itoa);
		var_dump($salt);

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
		$count_log2 = strpos($this->itoa, $hash[3]);
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
}
