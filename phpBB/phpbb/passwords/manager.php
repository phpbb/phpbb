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

namespace phpbb\passwords;

class manager
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
	* Passwords helper
	* @var \phpbb\passwords\helper
	*/
	protected $helper;

	/**
	* phpBB configuration
	* @var \phpbb\config\config
	*/
	protected $config;

	/**
	 * @var bool Whether or not initialized() has been called
	 */
	private $initialized = false;

	/**
	 * @var array Hashing driver service collection
	 */
	private $hashing_algorithms;

	/**
	 * @var array List of default driver types
	 */
	private $defaults;

	/**
	* Construct a passwords object
	*
	* @param \phpbb\config\config		$config				phpBB configuration
	* @param array						$hashing_algorithms	Hashing driver service collection
	* @param \phpbb\passwords\helper	$helper				Passwords helper object
	* @param array						$defaults			List of default driver types
	*/
	public function __construct(\phpbb\config\config $config, $hashing_algorithms, helper $helper, $defaults)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->hashing_algorithms = $hashing_algorithms;
		$this->defaults = $defaults;
	}

	/**
	 * Initialize the internal state
	 */
	protected function initialize()
	{
		if (!$this->initialized)
		{
			$this->initialized = true;
			$this->fill_type_map($this->hashing_algorithms);
			$this->register_default_type($this->defaults);
		}
	}

	/**
	* Register default type
	* Will register the first supported type from the list of default types
	*
	* @param array $defaults List of default types in order from first to
	*			use to last to use
	*/
	protected function register_default_type($defaults)
	{
		foreach ($defaults as $type)
		{
			if ($this->algorithms[$type]->is_supported())
			{
				$this->type = $this->algorithms[$type]->get_prefix();
				break;
			}
		}
	}

	/**
	* Fill algorithm type map
	*
	* @param \phpbb\di\service_collection $hashing_algorithms
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
		$this->algorithms = $hashing_algorithms;
	}

	/**
	* Get the algorithm specified by a specific prefix
	*
	* @param string $prefix Password hash prefix
	*
	* @return object|bool The hash type object or false if prefix is not
	*			supported
	*/
	protected function get_algorithm($prefix)
	{
		if (isset($this->type_map[$prefix]))
		{
			return $this->type_map[$prefix];
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
			return false;
		}

		$this->initialize();

		// Be on the lookout for multiple hashing algorithms
		// 2 is correct: H\2a > 2, H\P > 2
		if (strlen($match[1]) > 2 && strpos($match[1], '\\') !== false)
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

				$return_ary[$type] = $this->get_algorithm('$' . $type . '$');

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
	public function hash($password, $type = '')
	{
		if (strlen($password) > 4096)
		{
			// If the password is too huge, we will simply reject it
			// and not let the server try to hash it.
			return false;
		}

		$this->initialize();

		// Try to retrieve algorithm by service name if type doesn't
		// start with dollar sign
		if (!is_array($type) && strpos($type, '$') !== 0 && isset($this->algorithms[$type]))
		{
			$type = $this->algorithms[$type]->get_prefix();
		}

		$type = ($type === '') ? $this->type : $type;

		if (is_array($type))
		{
			return $this->combined_hash_password($password, $type);
		}

		if (isset($this->type_map[$type]))
		{
			$hashing_algorithm = $this->type_map[$type];
		}
		else
		{
			return false;
		}

		return $hashing_algorithm->hash($password);
	}

	/**
	* Check supplied password against hash and set convert_flag if password
	* needs to be converted to different format (preferably newer one)
	*
	* @param string $password Password that should be checked
	* @param string $hash Stored hash
	* @param array	$user_row User's row in users table
	* @return string|bool True if password is correct, false if not
	*/
	public function check($password, $hash, $user_row = array())
	{
		if (strlen($password) > 4096)
		{
			// If the password is too huge, we will simply reject it
			// and not let the server try to hash it.
			return false;
		}

		// Empty hashes can't be checked
		if (empty($hash))
		{
			return false;
		}

		$this->initialize();

		// First find out what kind of hash we're dealing with
		$stored_hash_type = $this->detect_algorithm($hash);
		if ($stored_hash_type == false)
		{
			// Still check MD5 hashes as that is what the installer
			// will default to for the admin user
			return $this->get_algorithm('$H$')->check($password, $hash);
		}

		// Multiple hash passes needed
		if (is_array($stored_hash_type))
		{
			$correct = $this->check_combined_hash($password, $stored_hash_type, $hash);
			$this->convert_flag = ($correct === true) ? true : false;
			return $correct;
		}

		if ($stored_hash_type->get_prefix() !== $this->type)
		{
			$this->convert_flag = true;
		}
		else
		{
			if ($stored_hash_type instanceof driver\rehashable_driver_interface)
			{
				$this->convert_flag = $stored_hash_type->needs_rehash($hash);
			}
			else
			{
				$this->convert_flag = false;
			}
		}

		// Check all legacy hash types if prefix is $CP$
		if ($stored_hash_type->get_prefix() === '$CP$')
		{
			// Remove $CP$ prefix for proper checking
			$hash = substr($hash, 4);

			foreach ($this->type_map as $algorithm)
			{
				if ($algorithm->is_legacy() && $algorithm->check($password, $hash, $user_row) === true)
				{
					return true;
				}
			}
		}

		return $stored_hash_type->check($password, $hash);
	}

	/**
	* Create combined hash from already hashed password
	*
	* @param string $password_hash Complete current password hash
	* @param string $type Type of the hashing algorithm the password hash
	*		should be combined with
	* @return string|bool Combined password hash if combined hashing was
	*		successful, else false
	*/
	public function combined_hash_password($password_hash, $type)
	{
		$this->initialize();

		$data = array(
			'prefix' => '$',
			'settings' => '$',
		);
		$hash_settings = $this->helper->get_combined_hash_settings($password_hash);
		$hash = $hash_settings[0];

		// Put settings of current hash into data array
		$stored_hash_type = $this->detect_algorithm($password_hash);
		$this->helper->combine_hash_output($data, 'prefix', $stored_hash_type->get_prefix());
		$this->helper->combine_hash_output($data, 'settings', $stored_hash_type->get_settings_only($password_hash));

		// Hash current hash with the defined types
		foreach ($type as $cur_type)
		{
			if (isset($this->algorithms[$cur_type]))
			{
				$new_hash_type = $this->algorithms[$cur_type];
			}
			else
			{
				$new_hash_type = $this->get_algorithm($cur_type);
			}

			if (!$new_hash_type)
			{
				return false;
			}

			$new_hash = $new_hash_type->hash(str_replace($stored_hash_type->get_settings_only($password_hash), '', $hash));
			$this->helper->combine_hash_output($data, 'prefix', $new_hash_type->get_prefix());
			$this->helper->combine_hash_output($data, 'settings', substr(str_replace('$', '\\', $new_hash_type->get_settings_only($new_hash, true)), 0));
			$hash = str_replace($new_hash_type->get_settings_only($new_hash), '', $this->helper->obtain_hash_only($new_hash));
		}
		return $this->helper->combine_hash_output($data, 'hash', $hash);
	}

	/**
	* Check combined password hash against the supplied password
	*
	* @param string $password Password entered by user
	* @param array $stored_hash_type An array containing the hash types
	*				as described by stored password hash
	* @param string $hash Stored password hash
	*
	* @return bool True if password is correct, false if not
	*/
	public function check_combined_hash($password, $stored_hash_type, $hash)
	{
		$i = 0;
		$data = array(
			'prefix' => '$',
			'settings' => '$',
		);
		$hash_settings = $this->helper->get_combined_hash_settings($hash);
		foreach ($stored_hash_type as $key => $hash_type)
		{
			$rebuilt_hash = $this->helper->rebuild_hash($hash_type->get_prefix(), $hash_settings[$i]);
			$this->helper->combine_hash_output($data, 'prefix', $key);
			$this->helper->combine_hash_output($data, 'settings', $hash_settings[$i]);
			$cur_hash = $hash_type->hash($password, $rebuilt_hash);
			$password = str_replace($rebuilt_hash, '', $cur_hash);
			$i++;
		}
		return ($hash === $this->helper->combine_hash_output($data, 'hash', $password));
	}
}
