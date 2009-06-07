<?php
/**
*
* @package zend
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

if (version_compare(PHP_VERSION, '5.2.4', '<') || !function_exists('spl_autoload_register'))
{
	trigger_error('PHP >= 5.2.4 and spl_autoload_register() required', E_USER_ERROR);
}

/**
* Autoload function for Zend framework classes
*/
function phpbb_zend_autoload($class_name)
{
	global $phpbb_root_path;

	if (strpos($class_name, '_') !== false)
	{
		$path = str_replace('_', '/', $class_name) . '.php';
		require("{$phpbb_root_path}includes/" . $path);
		return true;
	}
}

/**
* @ignore
*/
// make sure Zend is in the include path
ini_set( 'include_path', ini_get( 'include_path' ) . PATH_SEPARATOR . $phpbb_root_path . 'includes' );

// check whether a regular autoload function already exists, so we can load it into the spl stack afterwards
$register_autoload = false;
if (function_exists('__autoload'))
{
	$register_autoload = true;
}

spl_autoload_register('phpbb_zend_autoload'); 

// load the old autoload function into the spl stack if necessary
if ($register_autoload)
{
	spl_autoload_register('__autoload');
}