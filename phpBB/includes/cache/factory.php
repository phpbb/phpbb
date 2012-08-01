<?php
/**
*
* @package acm
* @copyright (c) 2010 phpBB Group
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
* @package acm
*/
class phpbb_cache_factory
{
	private $acm_type;

	public function __construct($acm_type)
	{
		$this->acm_type = $acm_type;
	}

	public function get_driver($phpbb_root_path, $phpEx, $cache_dir = 'cache/')
	{
		$class_name = 'phpbb_cache_driver_' . $this->acm_type;
		return new $class_name($phpbb_root_path, $phpEx, $cache_dir);
	}

	public function get_service($phpbb_root_path, $phpEx, $cache_dir = 'cache/')
	{
		$driver = $this->get_driver($phpbb_root_path, $phpEx, $cache_dir);
		$service = new phpbb_cache_service($driver);
		return $service;
	}
}
