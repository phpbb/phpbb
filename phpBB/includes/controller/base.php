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
* Controller base class
* @package phpBB3
*/
abstract class phpbb_controller_base implements phpbb_controller_interface
{
	/**
	* User object
	* @var phpbb_user
	*/
	protected $user;

	/**
	* Constructor method
	*
	* Makes commonly used phpBB objects available as class properties
	* for controllers
	*/
	public function __construct()
	{
		global $user;
		$this->user = $user;
	}
}
