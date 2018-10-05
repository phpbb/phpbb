<?php
/**
*
* @package Icy Phoenix
* @version $Id$
* @copyright (c) 2008 Icy Phoenix
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}

abstract class SocialConnect
{
	private static $social_networks = array("facebook");
	private static $available_networks = array();

	private $network_name;
	private $network_name_clean;

	public function __construct($network_name)
	{
		global $lang, $redirect;
		$this->network_name = empty($lang[strtoupper($network_name)]) ? $network_name : $lang[strtoupper($network_name)];
		$this->network_name_clean = $network_name;
	}
	
	public static function get_available_networks()
	{
		global $board_config, $phpEx;

		foreach (self::$social_networks as $network_name)
		{
			if (empty(self::$available_networks))
			{
				if (in_array($network_name, self::$social_networks) && !empty($board_config['enable_' . $network_name . '_login']))
				{
					include(PHPBB_ROOT_PATH . 'includes/social_connect/class_' . $network_name . '_connect.' . $phpEx);
					$class_name = strtoupper(substr($network_name, 0, 1)) . substr($network_name, 1) . 'Connect';
					$network = new $class_name($network_name);
					self::$available_networks[$network_name] = $network;
				}
			}
		}
		return self::$available_networks;
	}

	public function get_name()
	{
		return $this->network_name;
	}

	public function get_name_clean()
	{
		return $this->network_name_clean;
	}

	public abstract function do_login($redirect, $force_retry = false);
	public abstract function get_user_data();
}

?>