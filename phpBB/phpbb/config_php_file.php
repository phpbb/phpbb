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
	* @return bool|array Return the content of the config file or false if the file does not exists.
	*/
	public function get_all()
	{
		if (!$this->load_config_file())
		{
			return false;
		}

		return $this->config_data;
	}

	/**
	* Return the value of a variable defined into the config.php file and false if the variable does not exist.
	*
	* @param string $variable The name of the variable
	* @return mixed
	*/
	public function get($variable)
	{
		if (!$this->load_config_file())
		{
			return false;
		}

		return isset($this->config_data[$variable]) ? $this->config_data[$variable] : false;
	}

	/**
	* Load the config file and store the information.
	*
	* @return bool True if the file was correctly loaded, false otherwise.
	*/
	protected function load_config_file()
	{
		if (!$this->config_loaded)
		{
			if (file_exists($this->config_file))
			{
				$this->defined_vars = null;
				$this->defined_vars = get_defined_vars();

				require($this->config_file);
				$this->config_data = array_diff_key(get_defined_vars(), $this->defined_vars);

				$this->config_loaded = true;
			}
			else
			{
				return false;
			}
		}

		return true;
	}
}
