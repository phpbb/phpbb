<?php
/**
*
* @package avatar
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* Base class for avatar drivers
* @package avatars
*/
abstract class phpbb_avatar_driver implements phpbb_avatar_driver_interface
{
	protected $name;

	/**
	* Current board configuration
	* @type phpbb_config
	*/
	protected $config;

	/**
	* Request object
	* @type phpbb_request
	*/
	protected $request;

	/**
	* Current $phpbb_root_path
	* @type string
	*/
	protected $phpbb_root_path;

	/**
	* Current $php_ext
	* @type string
	*/
	protected $php_ext;

	/**
	* A cache driver
	* @type phpbb_cache_driver_interface
	*/
	protected $cache;

	/**
	* This flag should be set to true if the avatar requires a nonstandard image
	* tag, and will generate the html itself.
	* @type boolean
	*/
	public $custom_html = false;

	/**
	* Construct a driver object
	*
	* @param phpbb_config $config The phpBB configuration
	* @param phpbb_request $request The request object
	* @param string $phpbb_root_path The path to the phpBB root
	* @param string $php_ext The php file extension
	* @param phpbb_cache_driver_interface $cache A cache driver
	*/
	public function __construct(phpbb_config $config, phpbb_request $request, $phpbb_root_path, $php_ext, phpbb_cache_driver_interface $cache = null)
	{
		$this->config = $config;
		$this->request = $request;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->cache = $cache;
	}

	/**
	* @inheritdoc
	*/
	public function get_data($row, $ignore_config = false)
	{
		return array(
			'src' => '',
			'width' => 0,
			'height' => 0,
		);
	}

	/**
	* @inheritdoc
	*/
	public function get_custom_html($row, $ignore_config = false, $alt = '')
	{
		return '';
	}

	/**
	* @inheritdoc
	**/
	public function prepare_form($template, $row, &$error)
	{
		return false;
	}

	/**
	* @inheritdoc
	**/
	public function prepare_form_acp()
	{
		return array();
	}

	/**
	* @inheritdoc
	**/
	public function process_form($template, $row, &$error)
	{
		return false;
	}

	/**
	* @inheritdoc
	**/
	public function delete($row)
	{
		return true;
	}

	/**
	* @inheritdoc
	**/
	public function is_enabled()
	{
		$driver = preg_replace('#^phpbb_avatar_driver_#', '', get_class($this));

		return $this->config["allow_avatar_$driver"];
	}

	/**
	* @inheritdoc
	**/
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
	* @param string	$name The driver name
	*/
	public function set_name($name)
	{
		$this->name = $name;
	}
}
