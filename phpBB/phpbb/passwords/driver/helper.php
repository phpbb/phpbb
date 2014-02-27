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
* @package passwords
*/
class helper
{
	/**
	* @var phpbb\config\config
	*/
	protected $config;

	/**
	* base64 alphabet
	* @var string
	*/
	public $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

	/**
	* Construct a driver helper object
	*
	* @param phpbb\config\config $config phpBB configuration
	*/
	public function __construct(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	/**
	* Base64 encode hash
	*
	* @param string $input Input string
	* @param int $count Input string length
	*
	* @return string base64 encoded string
	*/
	public function hash_encode64($input, $count)
	{
		$output = '';
		$i = 0;

		do
		{
			$value = ord($input[$i++]);
			$output .= $this->itoa64[$value & 0x3f];

			if ($i < $count)
			{
				$value |= ord($input[$i]) << 8;
			}

			$output .= $this->itoa64[($value >> 6) & 0x3f];

			if ($i++ >= $count)
			{
				break;
			}

			if ($i < $count)
			{
				$value |= ord($input[$i]) << 16;
			}

			$output .= $this->itoa64[($value >> 12) & 0x3f];

			if ($i++ >= $count)
			{
				break;
			}

			$output .= $this->itoa64[($value >> 18) & 0x3f];
		}
		while ($i < $count);

		return $output;
	}

	/**
	* Return unique id
	*
	* @param string $extra Additional entropy
	*
	* @return string Unique id
	*/
	public function unique_id($extra = 'c')
	{
		static $dss_seeded = false;

		$val = $this->config['rand_seed'] . microtime();
		$val = md5($val);
		$this->config['rand_seed'] = md5($this->config['rand_seed'] . $val . $extra);

		if ($dss_seeded !== true && ($this->config['rand_seed_last_update'] < time() - rand(1,10)))
		{
			$this->config->set('rand_seed_last_update', time(), true);
			$this->config->set('rand_seed', $this->config['rand_seed'], true);
			$dss_seeded = true;
		}

		return substr($val, 4, 16);
	}

	/**
	* Get random salt with specified length
	*
	* @param int $length Salt length
	* @param string $rand_seed Seed for random data (optional). For tests.
	*
	* @return string Random salt with specified length
	*/
	public function get_random_salt($length, $rand_seed = '/dev/urandom')
	{
		$random = '';

		if (($fh = @fopen($rand_seed, 'rb')))
		{
			$random = fread($fh, $length);
			fclose($fh);
		}

		if (strlen($random) < $length)
		{
			$random = '';
			$random_state = $this->unique_id();

			for ($i = 0; $i < $length; $i += 16)
			{
				$random_state = md5($this->unique_id() . $random_state);
				$random .= pack('H*', md5($random_state));
			}
			$random = substr($random, 0, $length);
		}
		return $random;
	}
}
