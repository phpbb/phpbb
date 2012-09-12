<?php
/**
*
* @package controller
* @copyright (c) 2012 phpBB Group
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
* Controller interface
* @package phpBB3
*/
class phpbb_controller_route_provider
{
	/**
	* Extension Finder object
	* @var phpbb_extension_finder
	*/
	protected $finder;

	/**
	* Constructor method
	* @param phpbb_extension_manager $extension_manager Extension Manager object
	*/
	public function __construct(phpbb_extension_finder $finder)
	{
		$this->finder = $finder;
	}

	public function find()
	{
		return array_keys($this->finder
			->directory('config')
			->prefix('routing')
			->suffix('yml')
			->find());
	}
}
