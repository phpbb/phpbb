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

define('BOARD_ADMIN', 98);

/**
* Settings management
*/
class class_settings
{

	var $modules = array();
	var $list_yes_no = array('Yes' => 1, 'No' => 0);
	var $list_time_intervals = array(
		'Cron_Disabled' => 0,
		'15M' => 900,
		'30M' => 1800,
		'1H' => 3600,
		'2H' => 7200,
		'3H' => 10800,
		'6H' => 21600,
		'12H' => 43200,
		'1D' => 86400,
		'3D' => 259200,
		'7D' => 604800,
		'14D' => 1209600,
		'30D' => 2592000,
	);

	/**
	* Setup settings
	*/
	function setup_settings()
	{
		global $db, $cache, $config, $lang;

		// Get all settings
		$this->modules = array();
		foreach ($cache->obtain_settings() as $settings_file)
		{
			include(PHPBB_ROOT_PATH . 'includes/' . SETTINGS_PATH . $settings_file . '.' . PHP_EXT);
		}

		return true;
	}

	/**
	* Set config
	*/
	function set_config($config_name, $config_value, $clear_cache = false, $return = false)
	{
		global $db, $cache, $config, $lang;

		set_config($config_name, $config_value, false, $return);

		if ($clear_cache)
		{
			$this->cache_clear();
		}
	}

	/**
	* Remove plugin config
	*/
	function remove_config($config_name, $config_value, $clear_cache = true, $return = false)
	{
		global $db, $cache, $config, $lang;

		$sql = "DELETE FROM " . CONFIG_TABLE . " WHERE config_name = '" . $db->sql_escape($config_name) . "'";
		$db->sql_return_on_error($return);
		$db->sql_query($sql);
		$db->sql_return_on_error(false);

		if ($clear_cache)
		{
			$this->cache_clear();
		}
	}

	/*
	* Get the user config if defined
	*/
	function user_config_key($key, $user_field = '', $over_field = '')
	{
		global $config, $user;

		// Get the user fields name if not given
		if (empty($user_field))
		{
			$user_field = 'user_' . $key;
		}

		// Get the overwrite allowed switch name if not given
		if (empty($over_field))
		{
			$over_field = $key . '_over';
		}

		// Does the key exists?
		if (!isset($config[$key])) return;

		// Does the user field exists ?
		if (!isset($user->data[$user_field])) return;

		// Does the overwrite switch exists?
		if (!isset($config[$over_field]))
		{
			$config[$over_field] = 0; // no overwrite
		}

		// Overwrite with the user data only if not overwrite set, not anonymous, logged in
		// If the user is admin we will not overwrite his setting either...
		if ((!intval($config[$over_field]) && ($user->data['user_id'] != ANONYMOUS) && $user->data['session_logged_in']) || ($user->data['user_level'] == ADMIN))
		{
			$config[$key] = $user->data[$user_field];
		}
		else
		{
			$user->data[$user_field] = $config[$key];
		}
	}

	/*
	* Initialize configuration
	*/
	function init_config($settings_details, $settings_data)
	{
		global $db, $cache, $config, $lang;

		$this->modules[$settings_details['menu_name']]['data'][$settings_details['name']]['id'] = array($settings_details['id'] => $settings_details['name']);
		@reset($settings_data);
		while (list($config_key, $config_data) = each($settings_data))
		{
			if (!isset($config_data['user_only']) || !$config_data['user_only'])
			{
				// Create the key value
				$config_value = (isset($config_data['values'][$config_data['default']]) ? $config_data['values'][$config_data['default']] : $config_data['default']);
				if (!isset($config[$config_key]))
				{
					$this->set_config($config_key, $config_value, true, true);
				}
				if (!empty($config_data['user']))
				{
					$config_key_over = $config_key . '_over';
					if (!isset($config[$config_key_over]))
					{
						// Create the "overwrite user choice" value
						$this->set_config($config_key_over, 0, true, true);
					}

					// Get user choice value
					$this->user_config_key($config_key, $config_data['user']);
				}
			}

			// Deliver it for input only if not hidden
			if (!isset($config_data['hide']) || !$config_data['hide'])
			{
				$this->modules[$settings_details['menu_name']]['data'][$settings_details['name']]['data'][$settings_details['sub_name']]['data'][$config_key] = $config_data;

				// Sort values: overwrite only if not yet provided
				if (empty($this->modules[$settings_details['menu_name']]['sort']) || ($this->modules[$settings_details['menu_name']]['sort'] == 0))
				{
					$this->modules[$settings_details['menu_name']]['sort'] = $settings_details['menu_sort'];
				}
				if (empty($this->modules[$settings_details['menu_name']]['data'][$settings_details['name']]['sort']) || ($this->modules[$settings_details['menu_name']]['data'][$settings_details['name']]['sort'] == 0))
				{
					$this->modules[$settings_details['menu_name']]['data'][$settings_details['name']]['sort'] = $settings_details['sort'];
				}
				if (empty($this->modules[$settings_details['menu_name']]['data'][$settings_details['name']]['data'][$settings_details['sub_name']]['sort']) || ($this->modules[$settings_details['menu_name']]['data'][$settings_details['name']]['data'][$settings_details['sub_name']]['sort'] == 0))
				{
					$this->modules[$settings_details['menu_name']]['data'][$settings_details['name']]['data'][$settings_details['sub_name']]['sort'] = $settings_details['sub_sort'];
				}
			}
		}

		if ($settings_details['clear_cache'])
		{
			$this->cache_clear();
		}
	}

	/*
	* Process settings modules
	*/
	function process_settings_modules($settings_modules, $in_acp = true, $target_userdata = false)
	{
		// Menu
		$menu_id = array();
		$menu_keys = array();
		$menu_sort = array();

		// Mod ID
		$mod_id = array();
		$mod_keys = array();
		$mod_sort = array();

		// Fields
		$sub_id = array();
		$sub_keys = array();
		$sub_sort = array();

		// Process
		@reset($settings_modules);
		while (list($menu_name, $menu) = each($settings_modules))
		{
			// Check if there are some config fields in the mods under this menu
			$found = false;

			// Menu
			@reset($menu['data']);
			while ((list($mod_name, $mod) = @each($menu['data'])) && !$found)
			{
				// Sub menu
				@reset($mod['data']);
				while ((list($sub_name, $sub) = @each($mod['data'])) && !$found)
				{
					// Fields
					@reset($sub['data']);
					while ((list($field_name, $field) = @each($sub['data'])) && !$found)
					{
						if ($this->is_auth_display($field_name, $field, $in_acp, $target_userdata))
						{
							$found = true;
							break;
						}
					}
				}
			}

			// Menu ok
			if ($found)
			{
				$i = sizeof($menu_keys);
				$menu_id[$i] = !empty($menu['id']) ? $menu['id'] : '';
				$menu_keys[$i] = $menu_name;
				$menu_sort[$i] = $menu['sort'];

				// Init mod level
				$mod_id[$i] = array();
				$mod_keys[$i] = array();
				$mod_sort[$i] = array();

				@reset($menu['data']);
				while (list($mod_name, $mod) = @each($menu['data']))
				{
					// Check if there are some config fields
					$found = false;
					@reset($mod['data']);
					while (list($sub_name, $sub) = @each($mod['data']))
					{
						@reset($sub['data']);
						while (list($field_name, $field) = @each($sub['data']))
						{
							if ($this->is_auth_display($field_name, $field, $in_acp, $target_userdata))
							{
								$found = true;
								break;
							}
						}
					}
					if ($found)
					{
						$j = sizeof($mod_keys[$i]);
						$mod_id[$i][$j] = !empty($mod['id']) ? $mod['id'] : '';
						$mod_keys[$i][$j] = $mod_name;
						$mod_sort[$i][$j] = $mod['sort'];

						// Init sub levels
						$sub_id[$i][$j] = array();
						$sub_keys[$i][$j] = array();
						$sub_sort[$i][$j] = array();

						// Sub names
						@reset($mod['data']);
						while (list($sub_name, $sub) = @each($mod['data']))
						{
							if (!empty($sub_name))
							{
								// Check if there is some config fields in this level
								$found = false;
								@reset($sub['data']);
								while (list($field_name, $field) = @each($sub['data']))
								{
									if ($this->is_auth_display($field_name, $field, $in_acp, $target_userdata))
									{
										$found = true;
										break;
									}
								}
								if ($found)
								{
									$sub_id[$i][$j][] = $sub['id'];
									$sub_keys[$i][$j][] = $sub_name;
									$sub_sort[$i][$j][] = $sub['sort'];
								}
							}
						}
						@array_multisort($sub_sort[$i][$j], $sub_keys[$i][$j]);
					}
				}
				@array_multisort($mod_sort[$i], $mod_keys[$i], $sub_sort[$i], $sub_keys[$i]);
			}
		}
		@array_multisort($menu_sort, $menu_keys, $mod_sort, $mod_keys, $sub_sort, $sub_keys);

		$return_values = array();
		$return_values = array(
			'menu_id' => $menu_id,
			'menu_keys' => $menu_keys,
			'menu_sort' => $menu_sort,
			'mod_id' => $mod_id,
			'mod_keys' => $mod_keys,
			'mod_sort' => $mod_sort,
			'sub_id' => $sub_id,
			'sub_keys' => $sub_keys,
			'sub_sort' => $sub_sort,
		);

		return $return_values;
	}

	/*
	* Check if user is permitted to access the data
	*/
	function is_auth($level_required)
	{
		global $user;

		$return = false;
		// add also JUNIOR_ADMIN?
		//if (($user->data['user_level'] == ADMIN) || ($user->data['user_level'] == JUNIOR_ADMIN))
		if ($user->data['user_level'] == ADMIN)
		{
			$return = true;
		}
		elseif (($level_required == USER) || empty($level_required))
		{
			$return = true;
		}

		return $return;
	}

	/*
	* Check if the field is to be displayed or not
	*/
	function is_auth_display($config_name, $config_data, $in_acp = true, $target_userdata = false)
	{
		global $config, $user;

		$return = false;

		if ($in_acp || empty($target_userdata))
		{
			if (!isset($config_data['user_only']) || !$config_data['user_only'])
			{
				$return = true;
			}
		}
		else
		{
			if (((!empty($config_data['user']) && isset($target_userdata[$config_data['user']]) && (!$config[$config_name . '_over'] || ($user->data['user_level'] == ADMIN))) || $config_data['system']) && $this->is_auth($config_data['auth']))
			{
				$return = true;
			}
		}

		return $return;
	}

	/*
	* Get template file: check if template file exists and set the correct path to template file
	*/
	function get_tpl_file($tpl_base_path, $tpl_file)
	{
		global $theme;

		$tpl_path = $tpl_base_path . 'default/' . $tpl_file;
		$tpl_temp_file = $tpl_base_path . $theme['template_name'] . '/' . $tpl_file;
		if (file_exists($tpl_temp_file))
		{
			$tpl_path = $tpl_temp_file;
		}
		return $tpl_path;
	}

	/*
	* Get lang var
	*/
	function get_lang($key)
	{
		global $lang;
		return ((!empty($key) && isset($lang[$key])) ? $lang[$key] : $key);
	}

	/**
	* Setup modules
	*/
	function setup_modules($modules_path, $modules_prefix = 'settings_')
	{
		global $db, $cache, $config, $lang;

		// We need to reset the modules to avoid modules in memory to be parsed again
		$this->modules = array();

		$modules_path = PHPBB_ROOT_PATH . 'includes/' . SETTINGS_PATH . (!empty($modules_path) ? (trim(basename($modules_path)) . '/') : '');
		$modules_prefix = (empty($modules_prefix) ? 'settings_' : trim(basename($modules_prefix)));

		// Search for modules...
		if (@is_dir($modules_path))
		{
			$dir = @opendir($modules_path);

			if ($dir)
			{
				while (($file = @readdir($dir)) !== false)
				{
					if ((strpos($file, $modules_prefix) === 0) && (substr($file, -(strlen(PHP_EXT) + 1)) === '.' . PHP_EXT))
					{
						@include($modules_path . $file);
					}
				}
				@closedir($dir);
			}
		}

		return true;
	}

	/**
	* Obtain lang files...
	*/
	function obtain_lang_files($suffix = 'settings_')
	{
		global $cache, $config;

		$suffix = !empty($suffix) ? trim(basename($suffix)) : 'settings_';

		if (($lang_files = $cache->get('_lang_' . $suffix . $config['default_lang'])) === false)
		{
			$lang_files = array();

			// Now search for langs...
			$dir = @opendir(PHPBB_ROOT_PATH . 'language/lang_' . $config['default_lang'] . '/');

			if ($dir)
			{
				while (($file = @readdir($dir)) !== false)
				{
					if ((strpos($file, 'lang_' . $suffix) === 0) && (substr($file, -(strlen(PHP_EXT) + 1)) === '.' . PHP_EXT))
					{
						$lang_files[] = substr($file, 0, -(strlen(PHP_EXT) + 1));
					}
				}
				@closedir($dir);
			}

			$cache->put('_lang_' . $suffix . $config['default_lang'], $lang_files);
		}

		return $lang_files;
	}

	/**
	* Cache clear
	*/
	function cache_clear()
	{
		global $db, $cache, $config, $lang;

		$cache->destroy('config');
		$db->clear_cache('config_');

		return true;
	}

}

?>