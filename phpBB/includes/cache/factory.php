<?php
/**
*
* @package acm
* @version $Id$
* @copyright (c) 2010 phpBB Group
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
* @package acm
*/
class phpbb_cache_factory
{
	private $acm_type;
	
	public function __construct($acm_type)
	{
		$this->acm_type = $acm_type;
	}
	
	public function get_acm()
	{
		$class_name = 'phpbb_cache_driver_' . $this->acm_type;
		return new $class_name();
	}
	
	public function get_service()
	{
		$acm = $this->get_acm();
		$service = new phpbb_cache_service($acm);
		return $service;
	}
	
	/**
	* for convenience to allow:
	* $cache = phpbb_cache_factory::create('file')->get_service();
	*/
	public static function create($acm_type)
	{
		return new self($acm_type);
	}
}
