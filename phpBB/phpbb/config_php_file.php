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

namespace phpbb;

class config_php_file
{
	/** @var string phpBB Root Path */
	protected $phpbb_root_path;

	/** @var string php file extension  */
	protected $php_ext;

	/**
	* Indicates whether the php config file has been loaded.
	*
	* @var bool
	*/
	protected $config_loaded = false;

	/**
	* The content of the php config file
	*
	* @var array
	*/
	protected $config_data = array();

	/**
	* The path to the config file. (Default: $phpbb_root_path . 'config.' . $php_ext)
	*
	* @var string
	*/
	protected $config_file;

	private $defined_vars;

	/**
	* Constructor
	*
	* @param string $phpbb_root_path phpBB Root Path
	* @param string $php_ext php file extension
	*/
	function __construct($phpbb_root_path, $php_ext)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->config_file = $this->phpbb_root_path . 'config.' . $this->php_ext;
	}

	/**
	* Set the path to the config file.
	*
	* @param string $config_file
	*/
	public function set_config_file($config_file)
	{
		$this->config_file = $config_file;
		$this->config_loaded = false;
	}

	/**
	* Returns an associative array containing the variables defined by the config file.
	*
	* @return array Return the content of the config file or an empty array if the file does not exists.
	*/
	public function get_all()
	{
		$this->load_config_file();

		return $this->config_data;
	}

	/**
	* Return the value of a variable defined into the config.php file or null if the variable does not exist.
	*
	* @param string $variable The name of the variable
	* @return mixed Value of the variable or null if the variable is not defined.
	*/
	public function get($variable)
	{
		$this->load_config_file();

		return isset($this->config_data[$variable]) ? $this->config_data[$variable] : null;
	}

	/**
	* Load the config file and store the information.
	*
	* @return null
	*/
	protected function load_config_file()
	{
		if (!$this->config_loaded && file_exists($this->config_file))
		{
			$this->defined_vars = get_defined_vars();

			require($this->config_file);
			$this->config_data = array_diff_key(get_defined_vars(), $this->defined_vars);

			$this->config_loaded = true;
		}
	}

	/**
	* Convert either 3.0 dbms or 3.1 db driver class name to 3.1 db driver class name.
	*
	* If $dbms is a valid 3.1 db driver class name, returns it unchanged.
	* Otherwise prepends phpbb\db\driver\ to the dbms to convert a 3.0 dbms
	* to 3.1 db driver class name.
	*
	* @param string $dbms dbms parameter
	* @return string driver class
	* @throws \RuntimeException
	*/
	public function convert_30_dbms_to_31($dbms)
	{
		// Note: this check is done first because mysqli extension
		// supplies a mysqli class, and class_exists($dbms) would return
		// true for mysqli class.
		// However, per the docblock any valid 3.1 driver name should be
		// recognized by this function, and have priority over 3.0 dbms.
		if (strpos($dbms, 'phpbb\db\driver') === false && class_exists('phpbb\db\driver\\' . $dbms))
		{
			return 'phpbb\db\driver\\' . $dbms;
		}

		if (class_exists($dbms))
		{
			// Additionally we could check that $dbms extends phpbb\db\driver\driver.
			// http://php.net/manual/en/class.reflectionclass.php
			// Beware of possible performance issues:
			// http://stackoverflow.com/questions/294582/php-5-reflection-api-performance
			// We could check for interface implementation in all paths or
			// only when we do not prepend phpbb\db\driver\.

			/*
			$reflection = new \ReflectionClass($dbms);

			if ($reflection->isSubclassOf('phpbb\db\driver\driver'))
			{
				return $dbms;
			}
			*/

			return $dbms;
		}

		throw new \RuntimeException("You have specified an invalid dbms driver: $dbms");
	}
}
