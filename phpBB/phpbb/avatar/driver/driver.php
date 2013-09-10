<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\avatar\driver;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Base class for avatar drivers
* @package phpBB3
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
	* @param \phpbb\request\request $request Request object
	* @param string $phpbb_root_path Path to the phpBB root
	* @param string $php_ext PHP file extension
	* @param \phpbb\cache\driver\driver_interface $cache Cache driver
	*/
	public function __construct(\phpbb\config\config $config, $phpbb_root_path, $php_ext, \phpbb\cache\driver\driver_interface $cache = null)
	{
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->cache = $cache;
	}

	/**
	* @inheritdoc
	*/
	public function get_custom_html($user, $row, $alt = '')
	{
		return '';
	}

	/**
	* @inheritdoc
	*/
	public function prepare_form_acp($user)
	{
		return array();
	}

	/**
	* @inheritdoc
	*/
	public function delete($row)
	{
		return true;
	}

	/**
	* @inheritdoc
	*/
	public function get_template_name()
	{
		$driver = preg_replace('#^phpbb_avatar_driver_#', '', get_class($this));
		$template = "ucp_avatar_options_$driver.html";

		return $template;
	}

	/**
	* @inheritdoc
	*/
	public function get_name()
	{
		return $this->name;
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
