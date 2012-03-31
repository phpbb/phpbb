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
	* @var phpbb_request Request class object
	*/
	protected $request;

	/**
	* @var dbal DBAL class object
	*/
	protected $db;

	/**
	* @var user User class object
	*/
	protected $user;

	/**
	* @var phpbb_template Template class object
	*/
	protected $template;

	/**
	* @var array Config array
	*/
	protected $config;

	/**
	* @var string PHP Extension
	*/
	protected $phpEx;

	/**
	* @var string Relative path to board root
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
		$this->phpEx = $phpEx;
		$this->phpbb_root_path = $phpbb_root_path;
	}
}
