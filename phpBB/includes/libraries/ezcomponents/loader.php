<?php
/**
*
* @package ezcomponents
* @version $Id$
* @copyright (c) 2005 phpBB Group
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
* eZ components class loader
* Replaces the autoload mechanism eZ Components normally use
* @package ezcomponents
*/
class phpbb_ezcomponents_loader
{
	var $loaded;

	/**
	* Constructor which makes sure the PHP version requirement is met.
	*/
	function phpbb_ezcomponents_loader()
	{
		$this->loaded = array();
		if (version_compare(PHP_VERSION, '5.2.1', '<'))
		{
			trigger_error('PHP >= 5.2.1 required', E_USER_ERROR);
		}
	}

	/**
	* Loads all classes of a particular component.
	* The base component is always loaded first.
	*
	* @param	$component	string	Lower case component name
	*/
	function load_component($component)
	{
		global $phpbb_root_path;

		// don't allow loading the same component twice
		if (isset($this->loaded[$component]) && $this->loaded[$component])
		{
			return;
		}

		// make sure base is always loaded first
		if ($component != 'base' && !isset($this->loaded['base']))
		{
			$this->load_component('base');
		}

		$ezc_path = $phpbb_root_path . 'includes/ezcomponents/';

		// retrieve the autoload list
		$classes = include($ezc_path . ucfirst($component) . '/' . $component . '_autoload.php');

		// include all files related to this component
		foreach ($classes as $class => $path)
		{
			include($ezc_path . $path);
		}
	}
}