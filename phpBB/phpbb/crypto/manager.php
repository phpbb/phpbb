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
class phpbb_crypto_manager
{
	/**
	* Default hashing method
	*/
	protected $type = false;

	/**
	* Hashing algorithm types
	*/
	protected $type_map = false;

	/**
	* Password convert flag. Password should be converted
	*/
	public $convert_flag = false;

	/**
	* Crypto helper
	* @var phpbb_crypto_helper
	*/
	protected $helper;

	/**
	* phpBB configuration
	* @var phpbb_config
	*/
	protected $config;

	/**
	* phpBB compiled container
	* @var service_container
	*/
	protected $container;

	/**
	* Construct a crypto object
	*
	* @param phpbb_config $config phpBB configuration
	*/
	public function __construct($config, $container, $hashing_algorithms, $default)
	{
		$this->config = $config;
		$this->container = $container;
		$this->type = $default;

		$this->fill_type_map($hashing_algorithms);
		$this->load_crypto_helper();
	}

	/**
	* Fill algorithm type map
	*
	* @param phpbb_di_service_collection $hashing_algorithms
	*/
	protected function fill_type_map($hashing_algorithms)
	{
		foreach ($hashing_algorithms as $algorithm)
		{
			if (!isset($this->type_map[$algorithm->get_prefix()]))
			{
				$this->type_map[$algorithm->get_prefix()] = $algorithm;
			}
		}
	}

	/**
	* Load crypto helper class
	*/
	protected function load_crypto_helper()
	{
		if ($this->helper === null)
		{
			$this->helper = new phpbb_crypto_helper($this, $this->container);
		}
	}

	/**
	* Get the hash type from the supplied hash
	*
	* @param string $hash Password hash that should be checked
	*
	* @return object The hash type object
	*/
	public function get_hashing_algorithm($hash)
	{
		/*
		* preg_match() will also show hashing algos like $2a\H$, which
		* is a combination of bcrypt and phpass. Legacy algorithms
		* like md5 will not be matched by this and need to be treated
		* differently.
		*/
		if (!preg_match('#^\$([a-zA-Z0-9\\\]*?)\$#', $hash, $match))
		{
			return $this->type_map['$H$'];
		}

		// Be on the lookout for multiple hashing algorithms
		// 2 is correct: H\2a > 2, H\P > 2
		if (strlen($match[1]) > 2)
		{
			$hash_types = explode('\\', $match[1]);
			$return_ary = array();
			foreach ($hash_types as $type)
			{
				if (isset($this->type_map["\${$type}\$"]))
				{
					// we do not support the same hashing
					// algorithm more than once
					if (isset($return_ary[$type]))
					{
						return false;
					}
					$return_ary[$type] = $this->type_map["\${$type}\$"];
				}
				else
				{
					return false;
				}
			}
			return $return_ary;
		}
		if (isset($this->type_map[$match[0]]))
		{
			return $this->type_map[$match[0]];
		}
		else
		{
			return false;
		}
	}

	/**
	* Hash supplied password
	*
	* @param string $password Password that should be hashed
	* @param string $type Hash type. Will default to standard hash type if
	*			none is supplied
	* @return string|bool Password hash of supplied password or false if
	*			if something went wrong during hashing
	*/
	public function hash_password($password, $type = '')
	{
		$type = ($type === '') ? $this->type : $type;

		if (is_array($type))
		{
			return $this->helper->combined_hash_password($password, $type);
		}

		$hashing_algorithm = $this->container->get($type);
		// Do not support 8-bit characters with $2a$ bcrypt
		if ($type === 'crypto.driver.bcrypt' || ($type === 'crypto.driver.bcrypt_2y' && !$hashing_algorithm->is_supported()))
		{
			if (ord($password[strlen($password)-1]) & 128)
			{
				return false;
			}
		}

		return $this->container->get($type)->hash($password);
	}

	public function check_hash($password, $hash)
	{
		// First find out what kind of hash we're dealing with
		$stored_hash_type = $this->get_hashing_algorithm($hash);
		if ($stored_hash_type == false)
		{
			return false;
		}

		// Multiple hash passes needed
		if (is_array($stored_hash_type))
		{
			return $this->helper->check_combined_hash($password, $stored_hash_type, $hash);
		}

		if ($stored_hash_type->get_name() !== $this->type)
		{
			$this->convert_flag = true;
		}
		else
		{
			$this->convert_flag = false;
		}

		return $stored_hash_type->check($password, $hash);
	}
}
