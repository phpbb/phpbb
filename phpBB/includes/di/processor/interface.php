<?php
/**
*
* @package phpBB3
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

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface phpbb_di_processor_interface
{
	/**
	* Mutate the container.
	*
	* @param ContainerBuilder $container The container
	*/
	public function process(ContainerBuilder $container);
}
