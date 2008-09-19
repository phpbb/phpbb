<?
/**
*
* @package VC
* @version $Id: $
* @copyright (c) 2008 phpBB Group
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

if (!interface_exists('phpbb_captcha_plugin'))
{
	include(PHPBB_ROOT_PATH . "includes/captcha/captcha_plugin." . PHP_EXT);
}

/** A small class until we get the autoloader done */
class phpbb_captcha_factory
{
	/**
	* return an instance of class $name in file $name_plugin.php
	*/
	public static function get_instance($name)
	{
		$name = basename($name);
		if (!class_exists($name))
		{
			include(PHPBB_ROOT_PATH . "includes/captcha/plugins/{$name}_plugin." . PHP_EXT);
		}
		return call_user_func(array($name, 'get_instance'));
	}
	
	/**
	* Call the garbage collector
	*/
	public static function garbage_collect($name)
	{
		$name = basename($name);
		if (!class_exists($name))
		{
			include(PHPBB_ROOT_PATH . "includes/captcha/plugins/{$name}_plugin." . PHP_EXT);
		}
		call_user_func(array($name, 'garbage_collect'), 0);
	}
	
	/**
	* return a list of all discovered CAPTCHA plugins
	*/
	public static function get_captcha_types()
	{
		$captchas = array();
		$captchas['available'] = array();
		$captchas['unavailable'] = array();
	
		$dp = @opendir(PHPBB_ROOT_PATH . 'includes/captcha/plugins');

		if ($dp)
		{
			while (($file = readdir($dp)) !== false)
			{
				if ((preg_match('#_plugin\.' . PHP_EXT . '$#', $file)))
				{
					$name = preg_replace('#^(.*?)_plugin\.' . PHP_EXT . '$#', '\1', $file);
					if (!class_exists($name))
					{
						include(PHPBB_ROOT_PATH . "includes/captcha/plugins/$file");
					}
					if (call_user_func(array($name, 'is_available')))
					{
						$captchas['available'][$name] = call_user_func(array($name, 'get_name'));
					}
					else
					{
						$captchas['unavailable'][$name] = call_user_func(array($name, 'get_name'));
					}
				}
			}
			closedir($dp);
		}

		return $captchas;
	}
}