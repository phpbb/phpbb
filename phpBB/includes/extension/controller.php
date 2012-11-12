<?php
/**
*
* @package extension
* @copyright (c) 2011 phpBB Group
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
* Abstract class extended by extension front controller classes
*
* @package extension
*/
abstract class phpbb_extension_controller implements phpbb_extension_controller_interface
{
	/**
	* Request class object
	* @var phpbb_request
	*/
	protected $request;

	/**
	* DBAL class object
	* @var dbal
	*/
	protected $db;

	/**
	* User class object
	* @var phpbb_user
	*/
	protected $user;

	/**
	* Template class object
	* @var phpbb_template
	*/
	protected $template;

	/**
	* Config object
	* @var phpbb_config
	*/
	protected $config;

	/**
	* PHP Extension
	* @var string
	*/
	protected $php_ext;

	/**
	* Relative path to board root
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* Constructor method that provides the common phpBB objects as inherited class
	* properties for automatic availability in extension controllers
	*/
	public function __construct()
	{
		global $request, $db, $user, $template, $config;
		global $phpEx, $phpbb_root_path;

		$this->request = $request;
		$this->db = $db;
		$this->user = $user;
		$this->template = $template;
		$this->config = $config;
		$this->php_ext = $phpEx;
		$this->phpbb_root_path = $phpbb_root_path;
	}
}
