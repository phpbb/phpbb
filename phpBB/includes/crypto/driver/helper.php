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
class phpbb_crypto_driver_helper
{
	/** @var phpbb_config */
	protected $driver;

	/**
	* Constructor of crypto driver helper object
	*/
	public function __construct($driver)
	{
		$this->driver = $driver;
	}

	/**
	* Base64 encode hash
	*
	* @param string $input Input string
	* @param int $count Input string length
	* @param string $itoa64 Allowed characters string
	*
	* @return string base64 encoded string
	*/
	public function hash_encode64($input, $count, &$itoa64)
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
	* Return unique id
	* @param string $extra additional entropy
	*
	* @return string Unique id
	*/
	public function unique_id($extra = 'c')
	{
		static $dss_seeded = false;
		global $config;

		$val = $config['rand_seed'] . microtime();
		$val = md5($val);
		$config['rand_seed'] = md5($config['rand_seed'] . $val . $extra);

		if ($dss_seeded !== true && ($config['rand_seed_last_update'] < time() - rand(1,10)))
		{
			set_config('rand_seed_last_update', time(), true);
			set_config('rand_seed', $config['rand_seed'], true);
			$dss_seeded = true;
		}

		return substr($val, 4, 16);
	}
}
