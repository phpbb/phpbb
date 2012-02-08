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

	public function get_driver()
	{
		$class_name = 'phpbb_cache_driver_' . $this->acm_type;
		return new $class_name();
	}

	public function get_service()
	{
		$driver = $this->get_driver();
		$service = new phpbb_cache_service($driver);
		return $service;
	}
}
