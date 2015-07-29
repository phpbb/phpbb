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

namespace phpbb\avatar\driver;

/**
* Base class for avatar drivers
*/
abstract class driver implements \phpbb\avatar\driver\driver_interface
{
	/**
	* Avatar driver name
	* @var string
	*/
	protected $name;

	/**
	* Current board configuration
	* @var \phpbb\config\config
	*/
	protected $config;

	/**
	* Current $phpbb_root_path
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* Current $php_ext
	* @var string
	*/
	protected $php_ext;

	/**
	* Path Helper
	* @var \phpbb\path_helper
	*/
	protected $path_helper;

	/**
	* Cache driver
	* @var \phpbb\cache\driver\driver_interface
	*/
	protected $cache;

	/**
	* Array of allowed avatar image extensions
	* Array is used for setting the allowed extensions in the fileupload class
	* and as a base for a regex of allowed extensions, which will be formed by
	* imploding the array with a "|".
	*
	* @var array
	*/
	protected $allowed_extensions = array(
		'gif',
		'jpg',
		'jpeg',
		'png',
	);

	/**
	* Construct a driver object
	*
	* @param \phpbb\config\config $config phpBB configuration
	* @param string $phpbb_root_path Path to the phpBB root
	* @param string $php_ext PHP file extension
	* @param \phpbb\path_helper $path_helper phpBB path helper
	* @param \phpbb\cache\driver\driver_interface $cache Cache driver
	*/
	public function __construct(\phpbb\config\config $config, $phpbb_root_path, $php_ext, \phpbb\path_helper $path_helper, \phpbb\cache\driver\driver_interface $cache = null)
	{
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->path_helper = $path_helper;
		$this->cache = $cache;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_custom_html($user, $row, $alt = '')
	{
		return '';
	}

	/**
	* {@inheritdoc}
	*/
	public function prepare_form_acp($user)
	{
		return array();
	}

	/**
	* {@inheritdoc}
	*/
	public function delete($row)
	{
		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_name()
	{
		return $this->name;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_config_name()
	{
		return preg_replace('#^phpbb\\\\avatar\\\\driver\\\\#', '', get_class($this));
	}

	/**
	* {@inheritdoc}
	*/
	public function get_acp_template_name()
	{
		return 'acp_avatar_options_' . $this->get_config_name() . '.html';
	}

	/**
	* Sets the name of the driver.
	*
	* @param string	$name Driver name
	*/
	public function set_name($name)
	{
		$this->name = $name;
	}
}
