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
class phpbb_passwords_manager
{
	/**
	* Default hashing method
	*/
	protected $type = false;

	/**
	* Hashing algorithm type map
	* Will be used to map hash prefix to type
	*/
	protected $type_map = false;

	/**
	* Service collection of hashing algorithms
	* Needs to be public for passwords helper
	*/
	public $algorithms = false;

	/**
	* Password convert flag. Signals that password should be converted
	*/
	public $convert_flag = false;

	/**
	* Crypto helper
	* @var phpbb_passwords_helper
	*/
	protected $helper;

	/**
	* phpBB configuration
	* @var phpbb_config
	*/
	protected $config;

	/**
	* Construct a passwords object
	*
	* @param phpbb_config $config phpBB configuration
	* @param phpbb_di_service_collection $hashing_algorithms Hashing driver
	*			service collection
	* @param string $default Default driver name
	*/
	public function __construct($config, $hashing_algorithms, $default)
	{
		$this->config = $config;
		$this->type = $default;

		$this->fill_type_map($hashing_algorithms);
		$this->load_passwords_helper();
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
			if (!isset($this->algorithms[$algorithm->get_name()]))
			{
				$this->algorithms[$algorithm->get_name()] = $algorithm;
			}

			if (!isset($this->type_map[$algorithm->get_prefix()]))
			{
				$this->type_map[$algorithm->get_prefix()] = $algorithm->get_name();
			}
		}
	}

	/**
	* Load passwords helper class
	*/
	protected function load_passwords_helper()
	{
		if ($this->helper === null)
		{
			$this->helper = new phpbb_passwords_helper($this);
		}
	}

	/**
	* Get the algorithm specified by a specific prefix
	*
	* @param string $prefix Password hash prefix
	*
	* @return object The hash type object
	*/
	protected function get_algorithm($prefix)
	{
		if (isset($this->type_map[$prefix]) && isset($this->algorithms[$this->type_map[$prefix]]))
		{
			return $this->algorithms[$this->type_map[$prefix]];
		}
		else
		{
			return false;
		}
	}

	/**
	* Detect the hash type of the supplied hash
	*
	* @param string $hash Password hash that should be checked
	*
	* @return object|bool The hash type object or false if the specified
	*			type is not supported
	*/
	public function detect_algorithm($hash)
	{
		/*
		* preg_match() will also show hashing algos like $2a\H$, which
		* is a combination of bcrypt and phpass. Legacy algorithms
		* like md5 will not be matched by this and need to be treated
		* differently.
		*/
		if (!preg_match('#^\$([a-zA-Z0-9\\\]*?)\$#', $hash, $match))
		{
			return $this->get_algorithm('$H$');
		}

		// Be on the lookout for multiple hashing algorithms
		// 2 is correct: H\2a > 2, H\P > 2
		if (strlen($match[1]) > 2)
		{
			$hash_types = explode('\\', $match[1]);
			$return_ary = array();
			foreach ($hash_types as $type)
			{
				// we do not support the same hashing
				// algorithm more than once
				if (isset($return_ary[$type]))
				{
					return false;
				}

				$return_ary[$type] = $this->get_algorithm("\${$type}\$");

				if (empty($return_ary[$type]))
				{

					return false;
				}
			}
			return $return_ary;
		}

		// get_algorithm() will automatically return false if prefix
		// is not supported
		return $this->get_algorithm($match[0]);
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

		if (isset($this->algorithms[$type]))
		{
			$hashing_algorithm = $this->algorithms[$type];
		}
		else
		{
			return false;
		}

		// Do not support 8-bit characters with $2a$ bcrypt
		// Also see http://www.php.net/security/crypt_blowfish.php
		if ($type === 'passwords.driver.bcrypt' || ($type === 'passwords.driver.bcrypt_2y' && !$hashing_algorithm->is_supported()))
		{
			if (ord($password[strlen($password)-1]) & 128)
			{
				return false;
			}
		}

		return $hashing_algorithm->hash($password);
	}

	/**
	* Check supplied password against hash and set convert_flag if password
	* needs to be converted to different format (preferrably newer one)
	*
	* @param string $password Password that should be checked
	* @param string $hash Stored hash
	* @return string|bool True if password is correct, false if not
	*/
	public function check_hash($password, $hash)
	{
		// First find out what kind of hash we're dealing with
		$stored_hash_type = $this->detect_algorithm($hash);
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
