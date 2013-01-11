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

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* A base class for extensions without custom enable/disable/purge code.
*
* @package extension
*/
class phpbb_extension_base implements phpbb_extension_interface
{
	/** @var ContainerInterface */
	protected $container;

	/**
	* Constructor
	*
	* @param ContainerInterface $container Container object
	*/
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	* Single enable step that does nothing
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return false Indicates no further steps are required
	*/
	public function enable_step($old_state)
	{
		return false;
	}

	/**
	* Single disable step that does nothing
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return false Indicates no further steps are required
	*/
	public function disable_step($old_state)
	{
		return false;
	}

	/**
	* Single purge step that does nothing
	*
	* @param mixed $old_state State returned by previous call of this method
	* @return false Indicates no further steps are required
	*/
	public function purge_step($old_state)
	{
		return false;
	}
}
