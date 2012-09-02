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
* @package controller
*/
class phpbb_controller_provider extends phpbb_extension_provider
{
	/**
	* Finds controller classes using the extension manager.
	*
	* All PHP files in includes/controller/ with a _controller suffix
	* are considered tasks controllers. Controllers in extensions must be
	* located in a directory called controller and must have the _controller
	* suffix.
	*
	* @return array     List of controller class names
	*/
	protected function find()
	{
		$finder = $this->extension_manager->get_finder();

		return $finder
			->suffix('_controller')
			->core_path('includes/')
			->directory('controller')
			->get_classes();
	}
}
