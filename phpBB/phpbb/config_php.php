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

class config_php
{
	/** @var string phpBB Root Path */
	protected $phpbb_root_path;

	/** @var string php file extension  */
	protected $php_ext;

	/**
	* Indicates if the php config file has been loaded.
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
	* Constructor
	*
	* @param string $phpbb_root_path Path to the phpbb includes directory.
	* @param string $php_ext php file extension
	*/
	function __construct($phpbb_root_path, $php_ext)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Load the config file, store the information and return them
	*
	* @return bool|array Return the content of the config file or false if the file does not exists.
	*/
	public function load_config_file()
	{
		if (!$this->config_loaded)
		{
			if (file_exists($this->phpbb_root_path . 'config.' . $this->php_ext))
			{
				$x7eeee37ce4d5f1ce4d968ed8fdd9bcbb = null;
				$x7eeee37ce4d5f1ce4d968ed8fdd9bcbb = get_defined_vars();

				require($this->phpbb_root_path . 'config.' . $this->php_ext);
				$this->config_data = array_diff_key(get_defined_vars(), $x7eeee37ce4d5f1ce4d968ed8fdd9bcbb);

				$this->config_loaded = true;
			}
			else
			{
				return false;
			}
		}

		return $this->config_data;
	}
}
