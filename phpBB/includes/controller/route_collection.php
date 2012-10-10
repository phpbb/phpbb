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

use Symfony\Component\Routing\RouteCollection;

/**
* Controller manager class
* @package phpBB3
*/
class phpbb_controller_route_collection extends RouteCollection
{
	/**
	* Construct method
	*
	* @param phpbb_extension_finder $finder Finder object
	*/
	public function __construct(phpbb_extension_finder $finder, phpbb_controller_provider $provider)
	{
		parent::__construct();
		$this->addCollection($provider->get_paths($finder)->find());
	}
}
