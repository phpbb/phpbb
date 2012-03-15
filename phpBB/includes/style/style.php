<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group, sections (c) 2001 ispi of Lincoln Inc
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
* Base Style class.
* @package phpBB3
*/
class phpbb_style
{
	/**
	* @var phpbb_style_template Template class.
	* Handles everything related to templates.
	*/
	public $template;

	/**
	* @var string Path of the cache directory for the template
	*/
	public $cachepath = '';

	/**
	* @var string phpBB root path
	*/
	private $phpbb_root_path;

	/**
	* @var phpEx PHP file extension
	*/
	private $phpEx;

	/**
	* @var phpbb_config phpBB config instance
	*/
	private $config;

	/**
	* @var user current user
	*/
	private $user;

	/**
	* Style resource locator
	* @var phpbb_style_resource_locator
	* This item is temporary public, until locate() function is implemented
	*/
	public $locator;

	/**
	* Style path provider
	* @var phpbb_style_path_provider
	*/
	private $provider;

	/**
	* Constructor.
	*
	* @param string $phpbb_root_path phpBB root path
	* @param user $user current user
	* @param phpbb_extension_manager $phpbb_extension_manager extension manager. Set it to false if extension manager should not be used.
	*/
	public function __construct($phpbb_root_path, $phpEx, $config, $user, $phpbb_extension_manager = false)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->config = $config;
		$this->user = $user;
		$this->locator = new phpbb_style_resource_locator();
		$this->provider = new phpbb_style_path_provider();
		if ($phpbb_extension_manager !== false)
		{
			$this->provider = new phpbb_style_extension_path_provider($phpbb_extension_manager, $this->provider);
		}
		$this->template = new phpbb_style_template($this->phpbb_root_path, $this->phpEx, $this->config, $this->user, $this->locator, $this->provider);
	}
}
