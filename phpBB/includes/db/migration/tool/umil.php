<?php
/**
 *
 * @author Nathan Guse (EXreaction) http://lithiumstudios.org
 * @author David Lewis (Highway of Life) highwayoflife@gmail.com
 * @package umil
 * @version $Id$
 * @copyright (c) 2008 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

define('UMIL_VERSION', '1.0.4');

/**
* Multicall instructions
*
* With the "multicall" (as I am calling it) you can make a single function call and have it repeat the actions multiple times on information sent from an array.
*
* To do this (it does not work on the _exists functions), all you must do is send the first variable in the function call as an array and for each item, send an array for each of the variables in order.
*
* Example:
* $umil->config_add(array(
*	array('config_name', 'config_value'),
*	array('config_name1', 'config_value1'),
*	array('config_name2', 'config_value2', true),
*	array('config_name3', 'config_value3', true),
* );
*/

/**
* UMIL - Unified MOD Installation Library class
*
* Cache Functions
*	cache_purge($type = '', $style_id = 0)
*
* Config Functions:
*	config_exists($config_name, $return_result = false)
*	config_add($config_name, $config_value = '', $is_dynamic = false)
*	config_update($config_name, $config_value, $is_dynamic = false)
*	config_remove($config_name)
*
* Module Functions
*	module_exists($class, $parent, $module)
*	module_add($class, $parent = 0, $data = array())
*	module_remove($class, $parent = 0, $module = '')
*
* Permissions/Auth Functions
*	permission_exists($auth_option, $global = true)
*	permission_add($auth_option, $global = true)
*	permission_remove($auth_option, $global = true)
*	permission_set($name, $auth_option = array(), $type = 'role', $global = true, $has_permission = true)
*	permission_unset($name, $auth_option = array(), $type = 'role', $global = true)
*
* Table Functions
*	table_exists($table_name)
*	table_add($table_name, $table_data = array())
*	table_remove($table_name)
*
* Table Column Functions
*	table_column_exists($table_name, $column_name)
*	table_column_add($table_name, $column_name = '', $column_data = array())
*	table_column_update($table_name, $column_name = '', $column_data = array())
*	table_column_remove($table_name, $column_name = '')
*
* Table Key/Index Functions
*	table_index_exists($table_name, $index_name)
*	table_index_add($table_name, $index_name = '', $column = array())
*	table_index_remove($table_name, $index_name = '')
*
* Table Row Functions (note that these actions are not reversed automatically during uninstallation)
*	table_row_insert($table_name, $data = array())
*	table_row_remove($table_name, $data = array())
*	table_row_update($table_name, $data = array(), $new_data = array())
*
* Version Check Function
* 	version_check($url, $path, $file)
*/
class umil
{
	/**
	* This will hold the text output for the inputted command (if the mod author would like to display the command that was ran)
	*
	* @var string
	*/
	var $command = '';

	/**
	* This will hold the text output for the result of the command.  $user->lang['SUCCESS'] if everything worked.
	*
	* @var string
	*/
	var $result = '';

	/**
	* Auto run $this->display_results after running a command
	*/
	var $auto_display_results = false;

	/**
	* Stand Alone option (this makes it possible to just use the single umil file and not worry about any language stuff
	*/
	var $stand_alone = false;

	/**
	* Were any new permissions added (used in umil_frontend)?
	*/
	var $permissions_added = false;

	/**
	* Database Object
	*/
	var $db = false;

	/**
	* Database Tools Object
	*/
	var $db_tools = false;

	/**
	* Do we want a custom prefix besides the phpBB table prefix?  You *probably* should not change this...
	*/
	var $table_prefix = false;

	/**
	* Constructor
	*/
	function umil($stand_alone = false, $db = false)
	{
		// Setup $this->db
		if ($db !== false)
		{
			if (!is_object($db) || !method_exists($db, 'sql_query'))
			{
				trigger_error('Invalid $db Object');
			}

			$this->db = $db;
		}
		else
		{
			global $db;
			$this->db = $db;
		}

		// Setup $this->db_tools
		if (!class_exists('phpbb_db_tools'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
		}
		$this->db_tools = new phpbb_db_tools($this->db);

		$this->stand_alone = $stand_alone;

		if (!$stand_alone)
		{
			global $config, $user, $phpbb_root_path, $phpEx;

			/* Does not have the fall back option to use en/ if the user's language file does not exist, so we will not use it...unless that is changed.
			if (method_exists('user', 'set_custom_lang_path'))
			{
				$user->set_custom_lang_path($phpbb_root_path . 'umil/language/');
				$user->add_lang('umil');
				$user->set_custom_lang_path($phpbb_root_path . 'language/');
			}
			else
			{*/
				// Include the umil language file.  First we check if the language file for the user's language is available, if not we check if the board's default language is available, if not we use the english file.
				if (isset($user->data['user_lang']) && file_exists("{$phpbb_root_path}umil/language/{$user->data['user_lang']}/umil.$phpEx"))
				{
					$path = $user->data['user_lang'];
				}
				else if (file_exists("{$phpbb_root_path}umil/language/" . basename($config['default_lang']) . "/umil.$phpEx"))
				{
					$path = basename($config['default_lang']);
				}
				else if (file_exists("{$phpbb_root_path}umil/language/en/umil.$phpEx"))
				{
					$path = 'en';
				}
				else
				{
					trigger_error('Language Files Missing.<br /><br />Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
				}

				$user->add_lang('./../../umil/language/' . $path . '/umil');
			//}

			$user->add_lang(array('acp/common', 'acp/permissions'));

			// Check to see if a newer version is available.
			$info = $this->version_check('version.phpbb.com', '/umil', ((defined('PHPBB_QA')) ? 'umil_qa.txt' : 'umil.txt'));
			if (is_array($info) && isset($info[0]) && isset($info[1]))
			{
				if (version_compare(UMIL_VERSION, $info[0], '<'))
				{
					global $template;

					// Make sure user->setup() has been called
					if (empty($user->lang))
					{
						$user->setup();
					}

					page_header('', false);

					$user->lang['UPDATE_UMIL'] = (isset($user->lang['UPDATE_UMIL'])) ? $user->lang['UPDATE_UMIL'] : 'This version of UMIL is outdated.<br /><br />Please download the latest UMIL (Unified MOD Install Library) from: <a href="%1$s">%1$s</a>';
					$template->assign_vars(array(
						'S_BOARD_DISABLED'		=> true,
						'L_BOARD_DISABLED'		=> sprintf($user->lang['UPDATE_UMIL'], $info[1]),
					));
				}
			}
		}
	}

	/**
	* umil_start
	*
	* A function which runs (almost) every time a function here is ran
	*/
	function umil_start()
	{
		global $user;

		// Set up the command.  This will get the arguments sent to the function.
		$args = func_get_args();
		$this->command = call_user_func_array(array($this, 'get_output_text'), $args);

		$this->result = (isset($user->lang['SUCCESS'])) ? $user->lang['SUCCESS'] : 'SUCCESS';
		$this->db->sql_return_on_error(true);

		//$this->db->sql_transaction('begin');
	}

	/**
	* umil_end
	*
	* A function which runs (almost) every time a function here is ran
	*/
	function umil_end()
	{
		global $user;

		// Set up the result.  This will get the arguments sent to the function.
		$args = func_get_args();
		$result = call_user_func_array(array($this, 'get_output_text'), $args);
		$this->result = ($result) ? $result : $this->result;

		if ($this->db->sql_error_triggered)
		{
			if ($this->result == ((isset($user->lang['SUCCESS'])) ? $user->lang['SUCCESS'] : 'SUCCESS'))
			{
				$this->result = 'SQL ERROR ' . $this->db->sql_error_returned['message'];
			}
			else
			{
				$this->result .= '<br /><br />SQL ERROR ' . $this->db->sql_error_returned['message'];
			}

			//$this->db->sql_transaction('rollback');
		}
		else
		{
			//$this->db->sql_transaction('commit');
		}

		$this->db->sql_return_on_error(false);

		// Auto output if requested.
		if ($this->auto_display_results && method_exists($this, 'display_results'))
		{
			$this->display_results();
		}

		return '<strong>' . $this->command . '</strong><br />' . $this->result;
	}

	/**
	* Get text for output
	*
	* Takes the given arguments and prepares them for the UI
	*
	* First argument sent is used as the language key
	* Further arguments (if send) are used on the language key through vsprintf()
	*
	* @return string Returns the prepared string for output
	*/
	function get_output_text()
	{
		global $user;

		// Set up the command.  This will get the arguments sent to the function.
		$args = func_get_args();
		if (sizeof($args))
		{
			$lang_key = array_shift($args);

			if (sizeof($args))
			{
				$lang_args = array();
				foreach ($args as $arg)
				{
					$lang_args[] = (isset($user->lang[$arg])) ? $user->lang[$arg] : $arg;
				}

				return @vsprintf(((isset($user->lang[$lang_key])) ? $user->lang[$lang_key] : $lang_key), $lang_args);
			}
			else
			{
				return ((isset($user->lang[$lang_key])) ? $user->lang[$lang_key] : $lang_key);
			}
		}

		return '';
	}

	/**
	* Run Actions
	*
	* Do-It-All function that can do everything required for installing/updating/uninstalling a mod based on an array of actions and the versions.
	*
	* @param string $action The action. install|update|uninstall
	* @param array $versions The array of versions and the actions for each
	* @param string $version_config_name The name of the config setting which holds/will hold the currently installed version
	* @param string $version_select Added for the UMIL Auto system to allow you to select the version you want to install/update/uninstall to.
	*/
	function run_actions($action, $versions, $version_config_name, $version_select = '')
	{
		// We will sort the actions to prevent issues from mod authors incorrectly listing the version numbers
		uksort($versions, 'version_compare');

		// Find the current version to install
		$current_version = '0.0.0';
		foreach ($versions as $version => $actions)
		{
			$current_version = $version;
		}

		$db_version = '';
		if ($this->config_exists($version_config_name))
		{
			global $config;
			$db_version = $config[$version_config_name];
		}

		// Set the action to install from update if nothing is currently installed
		if ($action == 'update' && !$db_version)
		{
			$action = 'install';
		}

		if ($action == 'install' || $action == 'update')
		{
			$version_installed = $db_version;
			foreach ($versions as $version => $version_actions)
			{
				// If we are updating
				if ($db_version && version_compare($version, $db_version, '<='))
				{
					continue;
				}

				if ($version_select && version_compare($version, $version_select, '>'))
				{
					break;
				}

				foreach ($version_actions as $method => $params)
				{
					if ($method == 'custom')
					{
						$this->_call_custom_function($params, $action, $version);
					}
					else
					{
						if (method_exists($this, $method))
						{
							call_user_func(array($this, $method), $params);
						}
					}
				}

				$version_installed = $version;
			}

			// update the version number or add it
			if ($this->config_exists($version_config_name))
			{
				$this->config_update($version_config_name, $version_installed);
			}
			else
			{
				$this->config_add($version_config_name, $version_installed);
			}
		}
		else if ($action == 'uninstall' && $db_version)
		{
			// reverse version list
			$versions = array_reverse($versions);

			foreach ($versions as $version => $version_actions)
			{
				// Uninstalling and this listed version is newer than installed
				if (version_compare($version, $db_version, '>'))
				{
					continue;
				}

				// Version selection stuff
				if ($version_select && version_compare($version, $version_select, '<='))
				{
					// update the version number
					$this->config_update($version_config_name, $version);
					break;
				}

				$cache_purge = false;
				$version_actions = array_reverse($version_actions);
				foreach ($version_actions as $method => $params)
				{
					if ($method == 'custom')
					{
						$this->_call_custom_function($params, $action, $version);
					}
					else
					{
						// This way we always run the cache purge at the end of the version (done for the uninstall because the instructions are reversed, which would cause the cache purge to be run at the beginning if it was meant to run at the end).
						if ($method == 'cache_purge')
						{
							$cache_purge = $params;
							continue;
						}

						// A few things are not possible for uninstallations update actions and table_row actions
						if (strpos($method, 'update') !== false || strpos($method, 'table_insert') !== false || strpos($method, 'table_row_') !== false)
						{
							continue;
						}

						// reverse function call
						$method = str_replace(array('add', 'remove', 'temp'), array('temp', 'add', 'remove'), $method);
						$method = str_replace(array('set', 'unset', 'temp'), array('temp', 'set', 'unset'), $method);

						if (method_exists($this, $method))
						{
							call_user_func(array($this, $method), ((is_array($params) ? array_reverse($params) : $params)));
						}
					}
				}

				if ($cache_purge !== false)
				{
					$this->cache_purge($cache_purge);
				}
			}

			if (!$version_select)
			{
				// Unset the version number
				$this->config_remove($version_config_name);
			}
		}
	}

	/**
	* Call custom function helper
	*/
	function _call_custom_function($functions, $action, $version)
	{
		if (!is_array($functions))
		{
			$functions = array($functions);
		}

		$return = '';

		foreach ($functions as $function)
		{
			if (function_exists($function))
			{
				// Must reset before calling the function
				$this->umil_start();

				$returned = call_user_func($function, $action, $version);
				if (is_string($returned))
				{
					$this->command = $this->get_output_text($returned);
				}
				else if (is_array($returned) && isset($returned['command']))
				{
					if (is_array($returned['command']))
					{
						$this->command = call_user_func_array(array($this, 'get_output_text'), $returned['command']);
					}
					else
					{
						$this->command = $this->get_output_text($returned['command']);
					}

					if (isset($returned['result']))
					{
						$this->result = $this->get_output_text($returned['result']);
					}
				}
				else
				{
					$this->command = $this->get_output_text('UNKNOWN');
				}

				$return .= $this->umil_end() . '<br />';
			}
		}

		return $return;
	}

	/**
	* Multicall Helper
	*
	* @param mixed $function Function name to call
	* @param mixed $params The parameters array
	*
	* @return bool True if we have done a multicall ($params is an array), false if not ($params is not an array)
	*/
	function multicall($function, $params)
	{
		if (is_array($params) && !empty($params))
		{
			foreach ($params as $param)
			{
				if (!is_array($param))
				{
					call_user_func(array($this, $function), $param);
				}
				else
				{
					call_user_func_array(array($this, $function), $param);
				}
			}
			return true;
		}

		return false;
	}

	/**
	* Cache Purge
	*
	* This function is for purging either phpBB3’s data cache, authorization cache, or the styles cache.
	*
	* @param string $type The type of cache you want purged.  Available types: auth, imageset, template, theme.  Anything else sent will purge the forum's cache.
	* @param int $style_id The id of the item you want purged (if the type selected is imageset/template/theme, 0 for all items in that section)
	*/
	function cache_purge($type = '', $style_id = 0)
	{
		global $auth, $cache, $user, $phpbb_root_path, $phpEx;

		// Multicall
		if ($this->multicall(__FUNCTION__, $type))
		{
			return;
		}

		$style_id = (int) $style_id;
		$type = (string) $type; // Prevent PHP bug.

		switch ($type)
		{
			case 'auth' :
				$this->umil_start('AUTH_CACHE_PURGE');
				$cache->destroy('_acl_options');
				$auth->acl_clear_prefetch();

				return $this->umil_end();
			break;

			case 'imageset' :
				if ($style_id == 0)
				{
					$return = array();
					$sql = 'SELECT imageset_id
						FROM ' . STYLES_IMAGESET_TABLE;
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$return[] = $this->cache_purge('imageset', $row['imageset_id']);
					}
					$this->db->sql_freeresult($result);

					return implode('<br /><br />', $return);
				}
				else
				{
					$sql = 'SELECT *
						FROM ' . STYLES_IMAGESET_TABLE . "
						WHERE imageset_id = $style_id";
					$result = $this->db->sql_query($sql);
					$imageset_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if (!$imageset_row)
					{
						$this->umil_start('IMAGESET_CACHE_PURGE', 'UNKNOWN');
						return $this->umil_end('FAIL');
					}

					$this->umil_start('IMAGESET_CACHE_PURGE', $imageset_row['imageset_name']);

					// The following is from includes/acp/acp_styles.php (edited)
					$sql_ary = array();

					$cfg_data_imageset = parse_cfg_file("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/imageset.cfg");

					$sql = 'DELETE FROM ' . STYLES_IMAGESET_DATA_TABLE . '
						WHERE imageset_id = ' . $style_id;
					$result = $this->db->sql_query($sql);

					foreach ($cfg_data_imageset as $image_name => $value)
					{
						if (strpos($value, '*') !== false)
						{
							if (substr($value, -1, 1) === '*')
							{
								list($image_filename, $image_height) = explode('*', $value);
								$image_width = 0;
							}
							else
							{
								list($image_filename, $image_height, $image_width) = explode('*', $value);
							}
						}
						else
						{
							$image_filename = $value;
							$image_height = $image_width = 0;
						}

						if (strpos($image_name, 'img_') === 0 && $image_filename)
						{
							$image_name = substr($image_name, 4);

							$sql_ary[] = array(
								'image_name'		=> (string) $image_name,
								'image_filename'	=> (string) $image_filename,
								'image_height'		=> (int) $image_height,
								'image_width'		=> (int) $image_width,
								'imageset_id'		=> (int) $style_id,
								'image_lang'		=> '',
							);
						}
					}

					$sql = 'SELECT lang_dir
						FROM ' . LANG_TABLE;
					$result = $this->db->sql_query($sql);

					while ($row = $this->db->sql_fetchrow($result))
					{
						if (@file_exists("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/{$row['lang_dir']}/imageset.cfg"))
						{
							$cfg_data_imageset_data = parse_cfg_file("{$phpbb_root_path}styles/{$imageset_row['imageset_path']}/imageset/{$row['lang_dir']}/imageset.cfg");
							foreach ($cfg_data_imageset_data as $image_name => $value)
							{
								if (strpos($value, '*') !== false)
								{
									if (substr($value, -1, 1) === '*')
									{
										list($image_filename, $image_height) = explode('*', $value);
										$image_width = 0;
									}
									else
									{
										list($image_filename, $image_height, $image_width) = explode('*', $value);
									}
								}
								else
								{
									$image_filename = $value;
									$image_height = $image_width = 0;
								}

								if (strpos($image_name, 'img_') === 0 && $image_filename)
								{
									$image_name = substr($image_name, 4);
									$sql_ary[] = array(
										'image_name'		=> (string) $image_name,
										'image_filename'	=> (string) $image_filename,
										'image_height'		=> (int) $image_height,
										'image_width'		=> (int) $image_width,
										'imageset_id'		=> (int) $style_id,
										'image_lang'		=> (string) $row['lang_dir'],
									);
								}
							}
						}
					}
					$this->db->sql_freeresult($result);

					$this->db->sql_multi_insert(STYLES_IMAGESET_DATA_TABLE, $sql_ary);

					$cache->destroy('sql', STYLES_IMAGESET_DATA_TABLE);

					return $this->umil_end();
				}
			break;
			//case 'imageset' :

			case 'template' :
				if ($style_id == 0)
				{
					$return = array();
					$sql = 'SELECT template_id
						FROM ' . STYLES_TEMPLATE_TABLE;
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$return[] = $this->cache_purge('template', $row['template_id']);
					}
					$this->db->sql_freeresult($result);

					return implode('<br /><br />', $return);
				}
				else
				{
					$sql = 'SELECT *
						FROM ' . STYLES_TEMPLATE_TABLE . "
						WHERE template_id = $style_id";
					$result = $this->db->sql_query($sql);
					$template_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if (!$template_row)
					{
						$this->umil_start('TEMPLATE_CACHE_PURGE', 'UNKNOWN');
						return $this->umil_end('FAIL');
					}

					$this->umil_start('TEMPLATE_CACHE_PURGE', $template_row['template_name']);

					// The following is from includes/acp/acp_styles.php
					if ($template_row['template_storedb'] && file_exists("{$phpbb_root_path}styles/{$template_row['template_path']}/template/"))
					{
						$filelist = array('' => array());

						$sql = 'SELECT template_filename, template_mtime
							FROM ' . STYLES_TEMPLATE_DATA_TABLE . "
							WHERE template_id = $style_id";
						$result = $this->db->sql_query($sql);

						while ($row = $this->db->sql_fetchrow($result))
						{
//							if (@filemtime("{$phpbb_root_path}styles/{$template_row['template_path']}/template/" . $row['template_filename']) > $row['template_mtime'])
//							{
								// get folder info from the filename
								if (($slash_pos = strrpos($row['template_filename'], '/')) === false)
								{
									$filelist[''][] = $row['template_filename'];
								}
								else
								{
									$filelist[substr($row['template_filename'], 0, $slash_pos + 1)][] = substr($row['template_filename'], $slash_pos + 1, strlen($row['template_filename']) - $slash_pos - 1);
								}
//							}
						}
						$this->db->sql_freeresult($result);

						$includes = array();
						foreach ($filelist as $pathfile => $file_ary)
						{
							foreach ($file_ary as $file)
							{
								if (!($fp = @fopen("{$phpbb_root_path}styles/{$template_row['template_path']}$pathfile$file", 'r')))
								{
									return $this->umil_end('FILE_COULD_NOT_READ', "{$phpbb_root_path}styles/{$template_row['template_path']}$pathfile$file");
								}
								$template_data = fread($fp, filesize("{$phpbb_root_path}styles/{$template_row['template_path']}$pathfile$file"));
								fclose($fp);

								if (preg_match_all('#<!-- INCLUDE (.*?\.html) -->#is', $template_data, $matches))
								{
									foreach ($matches[1] as $match)
									{
										$includes[trim($match)][] = $file;
									}
								}
							}
						}

						foreach ($filelist as $pathfile => $file_ary)
						{
							foreach ($file_ary as $file)
							{
								// Skip index.
								if (strpos($file, 'index.') === 0)
								{
									continue;
								}

								// We could do this using extended inserts ... but that could be one
								// heck of a lot of data ...
								$sql_ary = array(
									'template_id'			=> (int) $style_id,
									'template_filename'		=> "$pathfile$file",
									'template_included'		=> (isset($includes[$file])) ? implode(':', $includes[$file]) . ':' : '',
									'template_mtime'		=> (int) filemtime("{$phpbb_root_path}styles/{$template_row['template_path']}$pathfile$file"),
									'template_data'			=> (string) file_get_contents("{$phpbb_root_path}styles/{$template_row['template_path']}$pathfile$file"),
								);

								$sql = 'UPDATE ' . STYLES_TEMPLATE_DATA_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . "
									WHERE template_id = $style_id
										AND template_filename = '" . $this->db->sql_escape("$pathfile$file") . "'";
								$this->db->sql_query($sql);
							}
						}
						unset($filelist);
					}

					// Purge the forum's cache as well.
					$cache->purge();

					return $this->umil_end();
				}
			break;
			//case 'template' :

			case 'theme' :
				if ($style_id == 0)
				{
					$return = array();
					$sql = 'SELECT theme_id
						FROM ' . STYLES_THEME_TABLE;
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$return[] = $this->cache_purge('theme', $row['theme_id']);
					}
					$this->db->sql_freeresult($result);

					return implode('<br /><br />', $return);
				}
				else
				{
					$sql = 'SELECT *
						FROM ' . STYLES_THEME_TABLE . "
						WHERE theme_id = $style_id";
					$result = $this->db->sql_query($sql);
					$theme_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if (!$theme_row)
					{
						$this->umil_start('THEME_CACHE_PURGE', 'UNKNOWN');
						return $this->umil_end('FAIL');
					}

					$this->umil_start('THEME_CACHE_PURGE', $theme_row['theme_name']);

					// The following is from includes/acp/acp_styles.php
					if ($theme_row['theme_storedb'] && file_exists("{$phpbb_root_path}styles/{$theme_row['theme_path']}/theme/stylesheet.css"))
					{
						$stylesheet = file_get_contents($phpbb_root_path . 'styles/' . $theme_row['theme_path'] . '/theme/stylesheet.css');

						// Match CSS imports
						$matches = array();
						preg_match_all('/@import url\(["\'](.*)["\']\);/i', $stylesheet, $matches);

						if (sizeof($matches))
						{
							foreach ($matches[0] as $idx => $match)
							{
								if (!file_exists("{$phpbb_root_path}styles/{$theme_row['theme_path']}/theme/{$matches[1][$idx]}"))
								{
									continue;
								}

								$content = trim(file_get_contents("{$phpbb_root_path}styles/{$theme_row['theme_path']}/theme/{$matches[1][$idx]}"));
								$stylesheet = str_replace($match, $content, $stylesheet);
							}
						}

						// adjust paths
						$db_theme_data = str_replace('./', 'styles/' . $theme_row['theme_path'] . '/theme/', $stylesheet);

						// Save CSS contents
						$sql_ary = array(
							'theme_mtime'	=> (int) filemtime("{$phpbb_root_path}styles/{$theme_row['theme_path']}/theme/stylesheet.css"),
							'theme_data'	=> $db_theme_data,
						);

						$sql = 'UPDATE ' . STYLES_THEME_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . "
							WHERE theme_id = $style_id";
						$this->db->sql_query($sql);

						$cache->destroy('sql', STYLES_THEME_TABLE);
					}

					return $this->umil_end();
				}
			break;
			//case 'theme' :

			default:
				$this->umil_start('CACHE_PURGE');
				$cache->purge();

				return $this->umil_end();
			break;
		}
	}

	/**
	* Config Exists
	*
	* This function is to check to see if a config variable exists or if it does not.
	*
	* @param string $config_name The name of the config setting you wish to check for.
	* @param bool $return_result - return the config value/default if true : default false.
	*
	* @return bool true/false if config exists
	*/
	function config_exists($config_name, $return_result = false)
	{
		global $config, $cache;

		$sql = 'SELECT *
				FROM ' . CONFIG_TABLE . "
				WHERE config_name = '" . $this->db->sql_escape($config_name) . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row)
		{
			if (!isset($config[$config_name]))
			{
				$config[$config_name] = $row['config_value'];

				if (!$row['is_dynamic'])
				{
					$cache->destroy('config');
				}
			}

			return ($return_result) ? $row : true;
		}

		// this should never happen, but if it does, we need to remove the config from the array
		if (isset($config[$config_name]))
		{
			unset($config[$config_name]);
			$cache->destroy('config');
		}

		return false;
	}

	/**
	* Config Add
	*
	* This function allows you to add a config setting.
	*
	* @param string $config_name The name of the config setting you would like to add
	* @param mixed $config_value The value of the config setting
	* @param bool $is_dynamic True if it is dynamic (changes very often) and should not be stored in the cache, false if not.
	*
	* @return result
	*/
	function config_add($config_name, $config_value = '', $is_dynamic = false)
	{
		// Multicall
		if ($this->multicall(__FUNCTION__, $config_name))
		{
			return;
		}

		$this->umil_start('CONFIG_ADD', $config_name);

		if ($this->config_exists($config_name))
		{
			return $this->umil_end('CONFIG_ALREADY_EXISTS', $config_name);
		}

		set_config($config_name, $config_value, $is_dynamic);

		return $this->umil_end();
	}

	/**
	* Config Update
	*
	* This function allows you to update an existing config setting.
	*
	* @param string $config_name The name of the config setting you would like to update
	* @param mixed $config_value The value of the config setting
	* @param bool $is_dynamic True if it is dynamic (changes very often) and should not be stored in the cache, false if not.
	*
	* @return result
	*/
	function config_update($config_name, $config_value = '', $is_dynamic = false)
	{
		// Multicall
		if ($this->multicall(__FUNCTION__, $config_name))
		{
			return;
		}

		$this->umil_start('CONFIG_UPDATE', $config_name);

		if (!$this->config_exists($config_name))
		{
			return $this->umil_end('CONFIG_NOT_EXIST', $config_name);
		}

		set_config($config_name, $config_value, $is_dynamic);

		return $this->umil_end();
	}

	/**
	* Config Remove
	*
	* This function allows you to remove an existing config setting.
	*
	* @param string $config_name The name of the config setting you would like to remove
	*
	* @return result
	*/
	function config_remove($config_name)
	{
		global $cache, $config;

		// Multicall
		if ($this->multicall(__FUNCTION__, $config_name))
		{
			return;
		}

		$this->umil_start('CONFIG_REMOVE', $config_name);

		if (!$this->config_exists($config_name))
		{
			return $this->umil_end('CONFIG_NOT_EXIST', $config_name);
		}

		$sql = 'DELETE FROM ' . CONFIG_TABLE . " WHERE config_name = '" . $this->db->sql_escape($config_name) . "'";
		$this->db->sql_query($sql);

		unset($config[$config_name]);
		$cache->destroy('config');

		return $this->umil_end();
	}

	/**
	* Module Exists
	*
	* Check if a module exists
	*
	* @param string $class The module class(acp|mcp|ucp)
	* @param int|string|bool $parent The parent module_id|module_langname (0 for no parent).  Use false to ignore the parent check and check class wide.
	* @param int|string $module The module_id|module_langname you would like to check for to see if it exists
	*/
	function module_exists($class, $parent, $module)
	{
		// the main root directory should return true
		if (!$module)
		{
			return true;
		}

		$class = $this->db->sql_escape($class);
		$module = $this->db->sql_escape($module);

		$parent_sql = '';
		if ($parent !== false)
		{
			// Allows '' to be sent as 0
			$parent = (!$parent) ? 0 : $parent;

			if (!is_numeric($parent))
			{
				$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
					WHERE module_langname = '" . $this->db->sql_escape($parent) . "'
					AND module_class = '$class'";
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$row)
				{
					return false;
				}

				$parent_sql = 'AND parent_id = ' . (int) $row['module_id'];
			}
			else
			{
				$parent_sql = 'AND parent_id = ' . (int) $parent;
			}
		}

		$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
			WHERE module_class = '$class'
			$parent_sql
			AND " . ((is_numeric($module)) ? 'module_id = ' . (int) $module : "module_langname = '$module'");
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row)
		{
			return true;
		}

		return false;
	}

	/**
	* Module Add
	*
	* Add a new module
	*
	* @param string $class The module class(acp|mcp|ucp)
	* @param int|string $parent The parent module_id|module_langname (0 for no parent)
	* @param array $data an array of the data on the new module.  This can be setup in two different ways.
	*	1. The "manual" way.  For inserting a category or one at a time.  It will be merged with the base array shown a bit below,
	*		but at the least requires 'module_langname' to be sent, and, if you want to create a module (instead of just a category) you must send module_basename and module_mode.
	* array(
	*		'module_enabled'	=> 1,
	*		'module_display'	=> 1,
	*		'module_basename'	=> '',
	*		'module_class'		=> $class,
	*		'parent_id'			=> (int) $parent,
	*		'module_langname'	=> '',
	*		'module_mode'		=> '',
	*		'module_auth'		=> '',
	*	)
	*	2. The "automatic" way.  For inserting multiple at a time based on the specs in the info file for the module(s).  For this to work the modules must be correctly setup in the info file.
	*		An example follows (this would insert the settings, log, and flag modes from the includes/acp/info/acp_asacp.php file):
	* array(
	* 		'module_basename'	=> 'asacp',
	* 		'modes'				=> array('settings', 'log', 'flag'),
	* )
	* 		Optionally you may not send 'modes' and it will insert all of the modules in that info file.
	*  @param string|bool $include_path If you would like to use a custom include path, specify that here
	*/
	function module_add($class, $parent = 0, $data = array(), $include_path = false)
	{
		global $cache, $user, $phpbb_root_path, $phpEx;

		// Multicall
		if ($this->multicall(__FUNCTION__, $class))
		{
			return;
		}

		// Prevent stupid things like trying to add a module with no name or any data on it
		if (empty($data))
		{
			$this->umil_start('MODULE_ADD', $class, 'UNKNOWN');
			return $this->umil_end('FAIL');
		}

        // Allows '' to be sent as 0
		$parent = (!$parent) ? 0 : $parent;

		// allow sending the name as a string in $data to create a category
		if (!is_array($data))
		{
			$data = array('module_langname' => $data);
		}

		if (!isset($data['module_langname']))
		{
			// The "automatic" way
			$basename = (isset($data['module_basename'])) ? $data['module_basename'] : '';
			$basename = str_replace(array('/', '\\'), '', $basename);
			$class = str_replace(array('/', '\\'), '', $class);
			$info_file = "$class/info/{$class}_$basename.$phpEx";

			// The manual and automatic ways both failed...
			if (!file_exists((($include_path === false) ? $phpbb_root_path . 'includes/' : $include_path) . $info_file))
			{
				$this->umil_start('MODULE_ADD', $class, $info_file);
				return $this->umil_end('FAIL');
			}

			$classname = "{$class}_{$basename}_info";

			if (!class_exists($classname))
			{
				include((($include_path === false) ? $phpbb_root_path . 'includes/' : $include_path) . $info_file);
			}

			$info = new $classname;
			$module = $info->module();
			unset($info);

			$result = '';
			foreach ($module['modes'] as $mode => $module_info)
			{
				if (!isset($data['modes']) || in_array($mode, $data['modes']))
				{
					$new_module = array(
						'module_basename'	=> $basename,
						'module_langname'	=> $module_info['title'],
						'module_mode'		=> $mode,
						'module_auth'		=> $module_info['auth'],
						'module_display'	=> (isset($module_info['display'])) ? $module_info['display'] : true,
						'before'			=> (isset($module_info['before'])) ? $module_info['before'] : false,
						'after'				=> (isset($module_info['after'])) ? $module_info['after'] : false,
					);

					// Run the "manual" way with the data we've collected.
					$result .= ((isset($data['spacer'])) ? $data['spacer'] : '<br />') . $this->module_add($class, $parent, $new_module);
				}
			}

			return $result;
		}

		// The "manual" way
		$this->umil_start('MODULE_ADD', $class, ((isset($user->lang[$data['module_langname']])) ? $user->lang[$data['module_langname']] : $data['module_langname']));
		add_log('admin', 'LOG_MODULE_ADD', ((isset($user->lang[$data['module_langname']])) ? $user->lang[$data['module_langname']] : $data['module_langname']));

		$class = $this->db->sql_escape($class);

		if (!is_numeric($parent))
		{
			$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
				WHERE module_langname = '" . $this->db->sql_escape($parent) . "'
				AND module_class = '$class'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$row)
			{
				return $this->umil_end('PARENT_NOT_EXIST');
			}

			$parent = $data['parent_id'] = $row['module_id'];
		}
		else if (!$this->module_exists($class, false, $parent))
		{
			return $this->umil_end('PARENT_NOT_EXIST');
		}

		if ($this->module_exists($class, $parent, $data['module_langname']))
		{
			return $this->umil_end('MODULE_ALREADY_EXIST');
		}

		if (!class_exists('acp_modules'))
		{
			include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
			$user->add_lang('acp/modules');
		}
		$acp_modules = new acp_modules();

		$module_data = array(
			'module_enabled'	=> (isset($data['module_enabled'])) ? $data['module_enabled'] : 1,
			'module_display'	=> (isset($data['module_display'])) ? $data['module_display'] : 1,
			'module_basename'	=> (isset($data['module_basename'])) ? $data['module_basename'] : '',
			'module_class'		=> $class,
			'parent_id'			=> (int) $parent,
			'module_langname'	=> (isset($data['module_langname'])) ? $data['module_langname'] : '',
			'module_mode'		=> (isset($data['module_mode'])) ? $data['module_mode'] : '',
			'module_auth'		=> (isset($data['module_auth'])) ? $data['module_auth'] : '',
		);
		$result = $acp_modules->update_module_data($module_data, true);

		// update_module_data can either return a string or an empty array...
		if (is_string($result))
		{
			// Error
			$this->result = $this->get_output_text($result);
		}
		else
		{
			// Success

			// Move the module if requested above/below an existing one
			if (isset($data['before']) && $data['before'])
			{
				$sql = 'SELECT left_id FROM ' . MODULES_TABLE . '
					WHERE module_class = \'' . $class . '\'
					AND parent_id = ' . (int) $parent . '
					AND module_langname = \'' . $this->db->sql_escape($data['before']) . '\'';
				$this->db->sql_query($sql);
				$to_left = $this->db->sql_fetchfield('left_id');

				$sql = 'UPDATE ' . MODULES_TABLE . " SET left_id = left_id + 2, right_id = right_id + 2
					WHERE module_class = '$class'
					AND left_id >= $to_left
					AND left_id < {$module_data['left_id']}";
				$this->db->sql_query($sql);

				$sql = 'UPDATE ' . MODULES_TABLE . " SET left_id = $to_left, right_id = " . ($to_left + 1) . "
					WHERE module_class = '$class'
					AND module_id = {$module_data['module_id']}";
				$this->db->sql_query($sql);
			}
			else if (isset($data['after']) && $data['after'])
			{
				$sql = 'SELECT right_id FROM ' . MODULES_TABLE . '
					WHERE module_class = \'' . $class . '\'
					AND parent_id = ' . (int) $parent . '
					AND module_langname = \'' . $this->db->sql_escape($data['after']) . '\'';
				$this->db->sql_query($sql);
				$to_right = $this->db->sql_fetchfield('right_id');

				$sql = 'UPDATE ' . MODULES_TABLE . " SET left_id = left_id + 2, right_id = right_id + 2
					WHERE module_class = '$class'
					AND left_id >= $to_right
					AND left_id < {$module_data['left_id']}";
				$this->db->sql_query($sql);

				$sql = 'UPDATE ' . MODULES_TABLE . ' SET left_id = ' . ($to_right + 1) . ', right_id = ' . ($to_right + 2) . "
					WHERE module_class = '$class'
					AND module_id = {$module_data['module_id']}";
				$this->db->sql_query($sql);
			}
		}

		// Clear the Modules Cache
		$cache->destroy("_modules_$class");

		return $this->umil_end();
	}

	/**
	* Module Remove
	*
	* Remove a module
	*
	* @param string $class The module class(acp|mcp|ucp)
	* @param int|string|bool $parent The parent module_id|module_langname (0 for no parent).  Use false to ignore the parent check and check class wide.
	* @param int|string $module The module id|module_langname
	* @param string|bool $include_path If you would like to use a custom include path, specify that here
	*/
	function module_remove($class, $parent = 0, $module = '', $include_path = false)
	{
		global $cache, $user, $phpbb_root_path, $phpEx;

		// Multicall
		if ($this->multicall(__FUNCTION__, $class))
		{
			return;
		}

		// Imitation of module_add's "automatic" and "manual" method so the uninstaller works from the same set of instructions for umil_auto
		if (is_array($module))
		{
			if (isset($module['module_langname']))
			{
				// Manual Method
				return $this->module_remove($class, $parent, $module['module_langname'], $include_path);
			}

			// Failed.
			if (!isset($module['module_basename']))
			{
				$this->umil_start('MODULE_REMOVE', $class, 'UNKNOWN');
				return $this->umil_end('FAIL');
			}

			// Automatic method
			$basename = str_replace(array('/', '\\'), '', $module['module_basename']);
			$class = str_replace(array('/', '\\'), '', $class);
			$info_file = "$class/info/{$class}_$basename.$phpEx";

			if (!file_exists((($include_path === false) ? $phpbb_root_path . 'includes/' : $include_path) . $info_file))
			{
				$this->umil_start('MODULE_REMOVE', $class, $info_file);
				return $this->umil_end('FAIL');
			}

			$classname = "{$class}_{$basename}_info";

			if (!class_exists($classname))
			{
				include((($include_path === false) ? $phpbb_root_path . 'includes/' : $include_path) . $info_file);
			}

			$info = new $classname;
			$module_info = $info->module();
			unset($info);

			$result = '';
			foreach ($module_info['modes'] as $mode => $info)
			{
				if (!isset($module['modes']) || in_array($mode, $module['modes']))
				{
					$result .= $this->module_remove($class, $parent, $info['title']) . '<br />';
				}
			}
			return $result;
		}
		else
		{
			$class = $this->db->sql_escape($class);

			if (!$this->module_exists($class, $parent, $module))
			{
				$this->umil_start('MODULE_REMOVE', $class, ((isset($user->lang[$module])) ? $user->lang[$module] : $module));
				return $this->umil_end('MODULE_NOT_EXIST');
			}

			$parent_sql = '';
			if ($parent !== false)
			{
				// Allows '' to be sent as 0
				$parent = (!$parent) ? 0 : $parent;

				if (!is_numeric($parent))
				{
					$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
						WHERE module_langname = '" . $this->db->sql_escape($parent) . "'
						AND module_class = '$class'";
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					// we know it exists from the module_exists check
					$parent_sql = 'AND parent_id = ' . (int) $row['module_id'];
				}
				else
				{
					$parent_sql = 'AND parent_id = ' . (int) $parent;
				}
			}

			$module_ids = array();
			if (!is_numeric($module))
			{
				$module = $this->db->sql_escape($module);
				$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
					WHERE module_langname = '$module'
					AND module_class = '$class'
					$parent_sql";
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$module_ids[] = (int) $row['module_id'];
				}
				$this->db->sql_freeresult($result);

				$module_name = $module;
			}
			else
			{
				$module = (int) $module;
				$sql = 'SELECT module_langname FROM ' . MODULES_TABLE . "
					WHERE module_id = $module
					AND module_class = '$class'
					$parent_sql";
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$module_name = $row['module_langname'];
				$module_ids[] = $module;
			}

			$this->umil_start('MODULE_REMOVE', $class, ((isset($user->lang[$module_name])) ? $user->lang[$module_name] : $module_name));
			add_log('admin', 'LOG_MODULE_REMOVED', ((isset($user->lang[$module_name])) ? $user->lang[$module_name] : $module_name));

			if (!class_exists('acp_modules'))
			{
				include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
				$user->add_lang('acp/modules');
			}
			$acp_modules = new acp_modules();
			$acp_modules->module_class = $class;

			foreach ($module_ids as $module_id)
			{
				$result = $acp_modules->delete_module($module_id);
				if (!empty($result))
				{
					if ($this->result == ((isset($user->lang['SUCCESS'])) ? $user->lang['SUCCESS'] : 'SUCCESS'))
					{
						$this->result = implode('<br />', $result);
					}
					else
					{
						$this->result .= '<br />' . implode('<br />', $result);
					}
				}
			}

			$cache->destroy("_modules_$class");

			return $this->umil_end();
		}
	}

	/**
	* Permission Exists
	*
	* Check if a permission (auth) setting exists
	*
	* @param string $auth_option The name of the permission (auth) option
	* @param bool $global True for checking a global permission setting, False for a local permission setting
	*
	* @return bool true if it exists, false if not
	*/
	function permission_exists($auth_option, $global = true)
	{
		if ($global)
		{
			$type_sql = ' AND is_global = 1';
		}
		else
		{
			$type_sql = ' AND is_local = 1';
		}

		$sql = 'SELECT auth_option_id
				FROM ' . ACL_OPTIONS_TABLE . "
				WHERE auth_option = '" . $this->db->sql_escape($auth_option) . "'"
				. $type_sql;
		$result = $this->db->sql_query($sql);

		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row)
		{
			return true;
		}

		return false;
	}

	/**
	* Permission Add
	*
	* Add a permission (auth) option
	*
	* @param string $auth_option The name of the permission (auth) option
	* @param bool $global True for checking a global permission setting, False for a local permission setting
	*
	* @return result
	*/
	function permission_add($auth_option, $global = true)
	{
		// Multicall
		if ($this->multicall(__FUNCTION__, $auth_option))
		{
			return;
		}

		$this->umil_start('PERMISSION_ADD', $auth_option);

		if ($this->permission_exists($auth_option, $global))
		{
			return $this->umil_end('PERMISSION_ALREADY_EXISTS', $auth_option);
		}

		// We've added permissions, so set to true to notify the user.
		$this->permissions_added = true;

		if (!class_exists('auth_admin'))
		{
			global $phpbb_root_path, $phpEx;

			include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
		}
		$auth_admin = new auth_admin();

		// We have to add a check to see if the !$global (if global, local, and if local, global) permission already exists.  If it does, acl_add_option currently has a bug which would break the ACL system, so we are having a work-around here.
		if ($this->permission_exists($auth_option, !$global))
		{
			$sql_ary = array(
				'is_global'	=> 1,
				'is_local'	=> 1,
			);
			$sql = 'UPDATE ' . ACL_OPTIONS_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE auth_option = \'' . $this->db->sql_escape($auth_option) . "'";
			$this->db->sql_query($sql);
		}
		else
		{
			if ($global)
			{
				$auth_admin->acl_add_option(array('global' => array($auth_option)));
			}
			else
			{
				$auth_admin->acl_add_option(array('local' => array($auth_option)));
			}
		}

		return $this->umil_end();
	}

	/**
	* Permission Remove
	*
	* Remove a permission (auth) option
	*
	* @param string $auth_option The name of the permission (auth) option
	* @param bool $global True for checking a global permission setting, False for a local permission setting
	*
	* @return result
	*/
	function permission_remove($auth_option, $global = true)
	{
		global $auth, $cache;

		// Multicall
		if ($this->multicall(__FUNCTION__, $auth_option))
		{
			return;
		}

		$this->umil_start('PERMISSION_REMOVE', $auth_option);

		if (!$this->permission_exists($auth_option, $global))
		{
			return $this->umil_end('PERMISSION_NOT_EXIST', $auth_option);
		}

		if ($global)
		{
			$type_sql = ' AND is_global = 1';
		}
		else
		{
			$type_sql = ' AND is_local = 1';
		}
		$sql = 'SELECT auth_option_id, is_global, is_local FROM ' . ACL_OPTIONS_TABLE . "
			WHERE auth_option = '" . $this->db->sql_escape($auth_option) . "'" .
			$type_sql;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$id = $row['auth_option_id'];

		// If it is a local and global permission, do not remove the row! :P
		if ($row['is_global'] && $row['is_local'])
		{
			$sql = 'UPDATE ' . ACL_OPTIONS_TABLE . '
				SET ' . (($global) ? 'is_global = 0' : 'is_local = 0') . '
				WHERE auth_option_id = ' . $id;
			$this->db->sql_query($sql);
		}
		else
		{
			// Delete time
			$this->db->sql_query('DELETE FROM ' . ACL_GROUPS_TABLE . ' WHERE auth_option_id = ' . $id);
			$this->db->sql_query('DELETE FROM ' . ACL_ROLES_DATA_TABLE . ' WHERE auth_option_id = ' . $id);
			$this->db->sql_query('DELETE FROM ' . ACL_USERS_TABLE . ' WHERE auth_option_id = ' . $id);
			$this->db->sql_query('DELETE FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option_id = ' . $id);
		}

		// Purge the auth cache
		$cache->destroy('_acl_options');
		$auth->acl_clear_prefetch();

		return $this->umil_end();
	}

	/**
	* Add a new permission role
	*
	* @param string $role_name The new role name
	* @param sting $role_type The type (u_, m_, a_)
	*/
	function permission_role_add($role_name, $role_type = '', $role_description = '')
	{
		// Multicall
		if ($this->multicall(__FUNCTION__, $role_name))
		{
			return;
		}

		$this->umil_start('PERMISSION_ROLE_ADD', $role_name);

		$sql = 'SELECT role_id FROM ' . ACL_ROLES_TABLE . '
			WHERE role_name = \'' . $this->db->sql_escape($role_name) . '\'';
		$this->db->sql_query($sql);
		$role_id = $this->db->sql_fetchfield('role_id');

		if ($role_id)
		{
			return $this->umil_end('ROLE_ALREADY_EXISTS', $old_role_name);
		}

		$sql = 'SELECT MAX(role_order) AS max FROM ' . ACL_ROLES_TABLE . '
			WHERE role_type = \'' . $this->db->sql_escape($role_type) . '\'';
		$this->db->sql_query($sql);
		$role_order = $this->db->sql_fetchfield('max');
		$role_order = (!$role_order) ? 1 : $role_order + 1;

		$sql_ary = array(
			'role_name'			=> $role_name,
			'role_description'	=> $role_description,
			'role_type'			=> $role_type,
			'role_order'		=> $role_order,
		);

		$sql = 'INSERT INTO ' . ACL_ROLES_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		return $this->umil_end();
	}

	/**
	* Update the name on a permission role
	*
	* @param string $old_role_name The old role name
	* @param string $new_role_name The new role name
	*/
	function permission_role_update($old_role_name, $new_role_name = '')
	{
		// Multicall
		if ($this->multicall(__FUNCTION__, $role_name))
		{
			return;
		}

		$this->umil_start('PERMISSION_ROLE_UPDATE', $old_role_name);

		$sql = 'SELECT role_id FROM ' . ACL_ROLES_TABLE . '
			WHERE role_name = \'' . $this->db->sql_escape($old_role_name) . '\'';
		$this->db->sql_query($sql);
		$role_id = $this->db->sql_fetchfield('role_id');

		if (!$role_id)
		{
			return $this->umil_end('ROLE_NOT_EXIST', $old_role_name);
		}

		$sql = 'UPDATE ' . ACL_ROLES_TABLE . '
			SET role_name = \'' . $this->db->sql_escape($new_role_name) . '\'
			WHERE role_name = \'' . $this->db->sql_escape($old_role_name) . '\'';
		$this->db->sql_query($sql);

		return $this->umil_end();
	}

	/**
	* Remove a permission role
	*
	* @param string $role_name The role name to remove
	*/
	function permission_role_remove($role_name)
	{
		global $auth;

		// Multicall
		if ($this->multicall(__FUNCTION__, $role_name))
		{
			return;
		}

		$this->umil_start('PERMISSION_ROLE_REMOVE', $role_name);

		$sql = 'SELECT role_id FROM ' . ACL_ROLES_TABLE . '
			WHERE role_name = \'' . $this->db->sql_escape($role_name) . '\'';
		$this->db->sql_query($sql);
		$role_id = $this->db->sql_fetchfield('role_id');

		if (!$role_id)
		{
			return $this->umil_end('ROLE_NOT_EXIST', $role_name);
		}

		$sql = 'DELETE FROM ' . ACL_ROLES_DATA_TABLE . '
			WHERE role_id = ' . $role_id;
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . ACL_ROLES_TABLE . '
			WHERE role_id = ' . $role_id;
		$this->db->sql_query($sql);

		$auth->acl_clear_prefetch();

		return $this->umil_end();
	}

	/**
	* Permission Set
	*
	* Allows you to set permissions for a certain group/role
	*
	* @param string $name The name of the role/group
	* @param string|array $auth_option The auth_option or array of auth_options you would like to set
	* @param string $type The type (role|group)
	* @param bool $has_permission True if you want to give them permission, false if you want to deny them permission
	*/
	function permission_set($name, $auth_option = array(), $type = 'role', $has_permission = true)
	{
		global $auth;

		// Multicall
		if ($this->multicall(__FUNCTION__, $name))
		{
			return;
		}

		if (!is_array($auth_option))
		{
			$auth_option = array($auth_option);
		}

		$new_auth = array();
		$sql = 'SELECT auth_option_id FROM ' . ACL_OPTIONS_TABLE . '
			WHERE ' . $this->db->sql_in_set('auth_option', $auth_option);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$new_auth[] = $row['auth_option_id'];
		}
		$this->db->sql_freeresult($result);

		if (!sizeof($new_auth))
		{
			return false;
		}

		$current_auth = array();

		$type = (string) $type; // Prevent PHP bug.

		switch ($type)
		{
			case 'role' :
				$this->umil_start('PERMISSION_SET_ROLE', $name);

				$sql = 'SELECT role_id FROM ' . ACL_ROLES_TABLE . '
					WHERE role_name = \'' . $this->db->sql_escape($name) . '\'';
				$this->db->sql_query($sql);
				$role_id = $this->db->sql_fetchfield('role_id');

				if (!$role_id)
				{
					return $this->umil_end('ROLE_NOT_EXIST');
				}

				$sql = 'SELECT auth_option_id, auth_setting FROM ' . ACL_ROLES_DATA_TABLE . '
					WHERE role_id = ' . $role_id;
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$current_auth[$row['auth_option_id']] = $row['auth_setting'];
				}
				$this->db->sql_freeresult($result);
			break;

			case 'group' :
				$sql = 'SELECT group_id FROM ' . GROUPS_TABLE . ' WHERE group_name = \'' . $this->db->sql_escape($name) . '\'';
				$this->db->sql_query($sql);
				$group_id = $this->db->sql_fetchfield('group_id');

				if (!$group_id)
				{
					$this->umil_start('PERMISSION_SET_GROUP', $name);
					return $this->umil_end('GROUP_NOT_EXIST');
				}

				// If the group has a role set for them we will add the requested permissions to that role.
				$sql = 'SELECT auth_role_id FROM ' . ACL_GROUPS_TABLE . '
					WHERE group_id = ' . $group_id . '
					AND auth_role_id <> 0
					AND forum_id = 0';
				$this->db->sql_query($sql);
				$role_id = $this->db->sql_fetchfield('auth_role_id');
				if ($role_id)
				{
					$sql = 'SELECT role_name FROM ' . ACL_ROLES_TABLE . '
						WHERE role_id = ' . $role_id;
					$this->db->sql_query($sql);
					$role_name = $this->db->sql_fetchfield('role_name');

					return $this->permission_set($role_name, $auth_option, 'role', $has_permission);
				}

				$this->umil_start('PERMISSION_SET_GROUP', $name);

				$sql = 'SELECT auth_option_id, auth_setting FROM ' . ACL_GROUPS_TABLE . '
					WHERE group_id = ' . $group_id;
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$current_auth[$row['auth_option_id']] = $row['auth_setting'];
				}
				$this->db->sql_freeresult($result);
			break;
		}

		$sql_ary = array();
		switch ($type)
		{
			case 'role' :
				foreach ($new_auth as $auth_option_id)
				{
					if (!isset($current_auth[$auth_option_id]))
					{
						$sql_ary[] = array(
							'role_id'			=> $role_id,
							'auth_option_id'	=> $auth_option_id,
							'auth_setting'		=> $has_permission,
				        );
					}
				}

				$this->db->sql_multi_insert(ACL_ROLES_DATA_TABLE, $sql_ary);
			break;

			case 'group' :
				foreach ($new_auth as $auth_option_id)
				{
					if (!isset($current_auth[$auth_option_id]))
					{
						$sql_ary[] = array(
							'group_id'			=> $group_id,
							'auth_option_id'	=> $auth_option_id,
							'auth_setting'		=> $has_permission,
				        );
					}
				}

				$this->db->sql_multi_insert(ACL_GROUPS_TABLE, $sql_ary);
			break;
		}

		$auth->acl_clear_prefetch();

		return $this->umil_end();
	}

	/**
	* Permission Unset
	*
	* Allows you to unset (remove) permissions for a certain group/role
	*
	* @param string $name The name of the role/group
	* @param string|array $auth_option The auth_option or array of auth_options you would like to set
	* @param string $type The type (role|group)
	*/
	function permission_unset($name, $auth_option = array(), $type = 'role')
	{
		global $auth;

		// Multicall
		if ($this->multicall(__FUNCTION__, $name))
		{
			return;
		}

		if (!is_array($auth_option))
		{
			$auth_option = array($auth_option);
		}

		$to_remove = array();
		$sql = 'SELECT auth_option_id FROM ' . ACL_OPTIONS_TABLE . '
			WHERE ' . $this->db->sql_in_set('auth_option', $auth_option);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$to_remove[] = $row['auth_option_id'];
		}
		$this->db->sql_freeresult($result);

		if (!sizeof($to_remove))
		{
			return false;
		}

		$type = (string) $type; // Prevent PHP bug.

		switch ($type)
		{
			case 'role' :
				$this->umil_start('PERMISSION_UNSET_ROLE', $name);

				$sql = 'SELECT role_id FROM ' . ACL_ROLES_TABLE . '
					WHERE role_name = \'' . $this->db->sql_escape($name) . '\'';
				$this->db->sql_query($sql);
				$role_id = $this->db->sql_fetchfield('role_id');

				if (!$role_id)
				{
					return $this->umil_end('ROLE_NOT_EXIST');
				}

				$sql = 'DELETE FROM ' . ACL_ROLES_DATA_TABLE . '
					WHERE ' . $this->db->sql_in_set('auth_option_id', $to_remove);
				$this->db->sql_query($sql);
			break;

			case 'group' :
				$sql = 'SELECT group_id FROM ' . GROUPS_TABLE . ' WHERE group_name = \'' . $this->db->sql_escape($name) . '\'';
				$this->db->sql_query($sql);
				$group_id = $this->db->sql_fetchfield('group_id');

				if (!$group_id)
				{
					$this->umil_start('PERMISSION_UNSET_GROUP', $name);
					return $this->umil_end('GROUP_NOT_EXIST');
				}

				// If the group has a role set for them we will remove the requested permissions from that role.
				$sql = 'SELECT auth_role_id FROM ' . ACL_GROUPS_TABLE . '
					WHERE group_id = ' . $group_id . '
					AND auth_role_id <> 0';
				$this->db->sql_query($sql);
				$role_id = $this->db->sql_fetchfield('auth_role_id');
				if ($role_id)
				{
					$sql = 'SELECT role_name FROM ' . ACL_ROLES_TABLE . '
						WHERE role_id = ' . $role_id;
					$this->db->sql_query($sql);
					$role_name = $this->db->sql_fetchfield('role_name');

					return $this->permission_unset($role_name, $auth_option, 'role');
				}

				$this->umil_start('PERMISSION_UNSET_GROUP', $name);

				$sql = 'DELETE FROM ' . ACL_GROUPS_TABLE . '
					WHERE ' . $this->db->sql_in_set('auth_option_id', $to_remove);
				$this->db->sql_query($sql);
			break;
		}

		$auth->acl_clear_prefetch();

		return $this->umil_end();
	}

	/**
	* Table Exists
	*
	* Check if a table exists in the DB or not
	*
	* @param string $table_name The table name to check for
	*
	* @return bool true if the table exists, false if not
	*/
	function table_exists($table_name)
	{
		$this->get_table_name($table_name);

		// Use sql_table_exists if available
		if (method_exists($this->db_tools, 'sql_table_exists'))
		{
			$roe = $this->db->return_on_error;
			$result = $this->db_tools->sql_table_exists($table_name);

			// db_tools::sql_table_exists resets the return_on_error to false always after completing, so we must make sure we set it to true again if it was before
			if ($roe)
			{
				$this->db->sql_return_on_error(true);
			}

			return $result;
		}

		if (!function_exists('get_tables'))
		{
			global $phpbb_root_path, $phpEx;
			include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
		}

		$tables = get_tables($this->db);

		if (in_array($table_name, $tables))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Table Add
	*
	* This only supports input from the array format of db_tools or create_schema_files.
	*/
	function table_add($table_name, $table_data = array())
	{
		global $dbms, $user;

		// Multicall
		if ($this->multicall(__FUNCTION__, $table_name))
		{
			return;
		}

		/**
		* $table_data can be empty when uninstalling a mod and table_remove was used, but no 2rd argument was given.
		* In that case we'll assume that it was a column previously added by the mod (if not the author should specify a 2rd argument) and skip this to prevent an error
		*/
		if (empty($table_data))
		{
			return;
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_ADD', $table_name);

		if ($this->table_exists($table_name))
		{
			return $this->umil_end('TABLE_ALREADY_EXISTS', $table_name);
		}

		if (!is_array($table_data))
		{
			return $this->umil_end('NO_TABLE_DATA');
		}

		if (!function_exists('get_available_dbms'))
		{
			global $phpbb_root_path, $phpEx;
			include("{$phpbb_root_path}includes/functions_install.$phpEx");
		}

		/*
		* This function has had numerous problems and is currently broken, so until phpBB uses it I will not be anymore
		if (method_exists($this->db_tools, 'sql_create_table'))
		{
			// Added in 3.0.5
			$this->db_tools->sql_create_table($table_name, $table_data);
		}
		else
		{*/
			$available_dbms = get_available_dbms($dbms);

			$sql_query = $this->create_table_sql($table_name, $table_data);
			$sql_query = split_sql_file($sql_query, $available_dbms[$dbms]['DELIM']);

			foreach ($sql_query as $sql)
			{
				$this->db->sql_query($sql);
			}
		//}

		return $this->umil_end();
	}

	/**
	* Table Remove
	*
	* Delete/Drop a DB table
	*/
	function table_remove($table_name)
	{
		// Multicall
		if ($this->multicall(__FUNCTION__, $table_name))
		{
			return;
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_REMOVE', $table_name);

		if (!$this->table_exists($table_name))
		{
			return $this->umil_end('TABLE_NOT_EXIST', $table_name);
		}

		if (method_exists($this->db_tools, 'sql_table_drop'))
		{
			// Added in 3.0.5
			$this->db_tools->sql_table_drop($table_name);
		}
		else
		{
			$this->db->sql_query('DROP TABLE ' . $table_name);
		}

		return $this->umil_end();
	}

	/**
	* Table Column Exists
	*
	* Check to see if a column exists in a table
	*/
	function table_column_exists($table_name, $column_name)
	{
		$this->get_table_name($table_name);

		return $this->db_tools->sql_column_exists($table_name, $column_name);
	}

	/**
	* Table Column Add
	*
	* Add a new column to a table.
	*/
	function table_column_add($table_name, $column_name = '', $column_data = array())
	{
		// Multicall
		if ($this->multicall(__FUNCTION__, $table_name))
		{
			return;
		}

		/**
		* $column_data can be empty when uninstalling a mod and table_column_remove was used, but no 3rd argument was given.
		* In that case we'll assume that it was a column previously added by the mod (if not the author should specify a 3rd argument) and skip this to prevent an error
		*/
		if (empty($column_data))
		{
			return;
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_COLUMN_ADD', $table_name, $column_name);

		if ($this->table_column_exists($table_name, $column_name))
		{
			return $this->umil_end('TABLE_COLUMN_ALREADY_EXISTS', $table_name, $column_name);
		}

		$this->db_tools->sql_column_add($table_name, $column_name, $column_data);

		return $this->umil_end();
	}

	/**
	* Table Column Update
	*
	* Alter/Update a column in a table.  You can not change a column name with this.
	*/
	function table_column_update($table_name, $column_name = '', $column_data = array())
	{
		// Multicall
		if ($this->multicall(__FUNCTION__, $table_name))
		{
			return;
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_COLUMN_UPDATE', $table_name, $column_name);

		if (!$this->table_column_exists($table_name, $column_name))
		{
			return $this->umil_end('TABLE_COLUMN_NOT_EXIST', $table_name, $column_name);
		}

		$this->db_tools->sql_column_change($table_name, $column_name, $column_data);

		return $this->umil_end();
	}

	/**
	* Table Column Remove
	*
	* Remove a column from a table
	*/
	function table_column_remove($table_name, $column_name = '')
	{
		// Multicall
		if ($this->multicall(__FUNCTION__, $table_name))
		{
			return;
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_COLUMN_REMOVE', $table_name, $column_name);

		if (!$this->table_column_exists($table_name, $column_name))
		{
			return $this->umil_end('TABLE_COLUMN_NOT_EXIST', $table_name, $column_name);
		}

		$this->db_tools->sql_column_remove($table_name, $column_name);

		return $this->umil_end();
	}

	/**
	* Table Index Exists
	*
	* Check if a table key/index exists on a table (can not check primary or unique)
	*/
	function table_index_exists($table_name, $index_name)
	{
		$this->get_table_name($table_name);

		$indexes = $this->db_tools->sql_list_index($table_name);

		if (in_array($index_name, $indexes))
		{
			return true;
		}

		return false;
	}

	/**
	* Table Index Add
	*
	* Add a new key/index to a table
	*/
	function table_index_add($table_name, $index_name = '', $column = array())
	{
		global $config;

		// Multicall
		if ($this->multicall(__FUNCTION__, $table_name))
		{
			return;
		}

		// Let them skip the column field and just use the index name in that case as the column as well
		if (empty($column))
		{
			$column = array($index_name);
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_KEY_ADD', $table_name, $index_name);

		if ($this->table_index_exists($table_name, $index_name))
		{
			return $this->umil_end('TABLE_KEY_ALREADY_EXIST', $table_name, $index_name);
		}

		if (!is_array($column))
		{
			$column = array($column);
		}

		// remove index length if we are before 3.0.8
		// the feature (required for some types when using MySQL4)
		// was added in that release (ticket PHPBB3-8944)
		if (version_compare($config['version'], '3.0.7-pl1', '<='))
		{
			$column = preg_replace('#:.*$#', '', $column);
		}

		$this->db_tools->sql_create_index($table_name, $index_name, $column);

		return $this->umil_end();
	}

	/**
	* Table Index Remove
	*
	* Remove a key/index from a table
	*/
	function table_index_remove($table_name, $index_name = '')
	{
		// Multicall
		if ($this->multicall(__FUNCTION__, $table_name))
		{
			return;
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_KEY_REMOVE', $table_name, $index_name);

		if (!$this->table_index_exists($table_name, $index_name))
		{
			return $this->umil_end('TABLE_KEY_NOT_EXIST', $table_name, $index_name);
		}

		$this->db_tools->sql_index_drop($table_name, $index_name);

		return $this->umil_end();
	}

	// Ignore, function was renamed to table_row_insert and keeping for backwards compatibility
	function table_insert($table_name, $data = array()) { $this->table_row_insert($table_name, $data); }

	/**
	* Table Insert
	*
	* Insert data into a table
	*/
	function table_row_insert($table_name, $data = array())
	{
		// Multicall
		if ($this->multicall(__FUNCTION__, $table_name))
		{
			return;
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_ROW_INSERT_DATA', $table_name);

		if (!$this->table_exists($table_name))
		{
			return $this->umil_end('TABLE_NOT_EXIST', $table_name);
		}

		$this->db->sql_multi_insert($table_name, $data);

		return $this->umil_end();
	}

	/**
	* Table Row Update
	*
	* Update a row in a table
	*
	* $data should be an array with the column names as keys and values as the items to check for each column.  Example:
	* array('user_id' => 123, 'user_name' => 'test user') would become:
	* WHERE user_id = 123 AND user_name = 'test user'
	*
	* $new_data is the new data it will be updated to (same format as you'd enter into $db->sql_build_array('UPDATE' ).
	*/
	function table_row_update($table_name, $data = array(), $new_data = array())
	{
		// Multicall
		if ($this->multicall(__FUNCTION__, $table_name))
		{
			return;
		}

		if (!sizeof($data))
		{
			return $this->umil_end('FAIL');
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_ROW_UPDATE_DATA', $table_name);

		if (!$this->table_exists($table_name))
		{
			return $this->umil_end('TABLE_NOT_EXIST', $table_name);
		}

		$sql = 'UPDATE ' . $table_name . '
			SET ' . $this->db->sql_build_array('UPDATE', $new_data) . '
			WHERE ' . $this->db->sql_build_array('SELECT', $data);
		$this->db->sql_query($sql);

		return $this->umil_end();
	}

	/**
	* Table Row Remove
	*
	* Remove a row from a table
	*
	* $data should be an array with the column names as keys and values as the items to check for each column.  Example:
	* array('user_id' => 123, 'user_name' => 'test user') would become:
	* WHERE user_id = 123 AND user_name = 'test user'
	*/
	function table_row_remove($table_name, $data = array())
	{
		// Multicall
		if ($this->multicall(__FUNCTION__, $table_name))
		{
			return;
		}

		if (!sizeof($data))
		{
			return $this->umil_end('FAIL');
		}

		$this->get_table_name($table_name);

		$this->umil_start('TABLE_ROW_REMOVE_DATA', $table_name);

		if (!$this->table_exists($table_name))
		{
			return $this->umil_end('TABLE_NOT_EXIST', $table_name);
		}

		$sql = 'DELETE FROM ' . $table_name . ' WHERE ' . $this->db->sql_build_array('SELECT', $data);
		$this->db->sql_query($sql);

		return $this->umil_end();
	}

	/**
	* Version Checker
	*
	* Format the file like the following:
	* http://www.phpbb.com/updatecheck/30x.txt
	*
	* @param string $url The url to access (ex: www.phpbb.com)
	* @param string $path The path to access (ex: /updatecheck)
	* @param string $file The name of the file to access (ex: 30x.txt)
	*
	* @return array|string Error Message if there was any error, or an array (each line in the file as a value)
	*/
	function version_check($url, $path, $file, $timeout = 10, $port = 80)
	{
		if (!function_exists('get_remote_file'))
		{
			global $phpbb_root_path, $phpEx;

			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		}

		$errstr = $errno = '';

		$info = get_remote_file($url, $path, $file, $errstr, $errno, $port, $timeout);

		if ($info === false)
		{
			return $errstr . ' [ ' . $errno . ' ]';
		}

		$info = str_replace("\r\n", "\n", $info);
		$info = explode("\n", $info);

		return $info;
	}

	/**
	* Create table SQL
	*
	* Create the SQL query for the specified DBMS on the fly from a create_schema_files type of table array
	*
	* @param string $table_name The name of the table
	* @param array $table_data The table data (formatted in the array format used by create_schema_files)
	* @param string $dbms The dbms this will be built for (for testing only, leave blank to use the current DBMS)
	*
	* @return The sql query to run for the submitted dbms to insert the table
	*/
	function create_table_sql($table_name, $table_data, $dbms = '')
	{
		// To allow testing
		$dbms = ($dbms) ? $dbms : $this->db_tools->sql_layer;

		// A list of types being unsigned for better reference in some db's
		$unsigned_types = array('UINT', 'UINT:', 'USINT', 'BOOL', 'TIMESTAMP');
		$supported_dbms = array('firebird', 'mssql', 'mysql_40', 'mysql_41', 'oracle', 'postgres', 'sqlite');

		$sql = '';

		// Create Table statement
		$generator = $textimage = false;

		switch ($dbms)
		{
			case 'mysql_40':
			case 'mysql_41':
			case 'firebird':
			case 'oracle':
			case 'sqlite':
			case 'postgres':
				$sql .= "CREATE TABLE {$table_name} (\n";
			break;

			case 'mssql':
				$sql .= "CREATE TABLE [{$table_name}] (\n";
			break;
		}

		// Table specific so we don't get overlap
		$modded_array = array();

		// Write columns one by one...
		foreach ($table_data['COLUMNS'] as $column_name => $column_data)
		{
			// Get type
			if (strpos($column_data[0], ':') !== false)
			{
				list($orig_column_type, $column_length) = explode(':', $column_data[0]);
				if (!is_array($this->db_tools->dbms_type_map[$dbms][$orig_column_type . ':']))
				{
					$column_type = sprintf($this->db_tools->dbms_type_map[$dbms][$orig_column_type . ':'], $column_length);
				}
				else
				{
					if (isset($this->db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['rule']))
					{
						switch ($this->db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['rule'][0])
						{
							case 'div':
								$column_length /= $this->db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['rule'][1];
								$column_length = ceil($column_length);
								$column_type = sprintf($this->db_tools->dbms_type_map[$dbms][$orig_column_type . ':'][0], $column_length);
							break;
						}
					}

					if (isset($this->db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['limit']))
					{
						switch ($this->db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['limit'][0])
						{
							case 'mult':
								$column_length *= $this->db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['limit'][1];
								if ($column_length > $this->db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['limit'][2])
								{
									$column_type = $this->db_tools->dbms_type_map[$dbms][$orig_column_type . ':']['limit'][3];
									$modded_array[$column_name] = $column_type;
								}
								else
								{
									$column_type = sprintf($this->db_tools->dbms_type_map[$dbms][$orig_column_type . ':'][0], $column_length);
								}
							break;
						}
					}
				}
				$orig_column_type .= ':';
			}
			else
			{
				$orig_column_type = $column_data[0];
				$column_type = $this->db_tools->dbms_type_map[$dbms][$column_data[0]];
				if ($column_type == 'text' || $column_type == 'blob')
				{
					$modded_array[$column_name] = $column_type;
				}
			}

			// Adjust default value if db-dependant specified
			if (is_array($column_data[1]))
			{
				$column_data[1] = (isset($column_data[1][$dbms])) ? $column_data[1][$dbms] : $column_data[1]['default'];
			}

			switch ($dbms)
			{
				case 'mysql_40':
				case 'mysql_41':
					$sql .= "\t{$column_name} {$column_type} ";

					// For hexadecimal values do not use single quotes
					if (!is_null($column_data[1]) && substr($column_type, -4) !== 'text' && substr($column_type, -4) !== 'blob')
					{
						$sql .= (strpos($column_data[1], '0x') === 0) ? "DEFAULT {$column_data[1]} " : "DEFAULT '{$column_data[1]}' ";
					}
					$sql .= 'NOT NULL';

					if (isset($column_data[2]))
					{
						if ($column_data[2] == 'auto_increment')
						{
							$sql .= ' auto_increment';
						}
						else if ($dbms === 'mysql_41' && $column_data[2] == 'true_sort')
						{
							$sql .= ' COLLATE utf8_unicode_ci';
						}
					}

					$sql .= ",\n";
				break;

				case 'sqlite':
					if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
					{
						$sql .= "\t{$column_name} INTEGER PRIMARY KEY ";
						$generator = $column_name;
					}
					else
					{
						$sql .= "\t{$column_name} {$column_type} ";
					}

					$sql .= 'NOT NULL ';
					$sql .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}'" : '';
					$sql .= ",\n";
				break;

				case 'firebird':
					$sql .= "\t{$column_name} {$column_type} ";

					if (!is_null($column_data[1]))
					{
						$sql .= 'DEFAULT ' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ' ';
					}

					$sql .= 'NOT NULL';

					// This is a UNICODE column and thus should be given it's fair share
					if (preg_match('/^X?STEXT_UNI|VCHAR_(CI|UNI:?)/', $column_data[0]))
					{
						$sql .= ' COLLATE UNICODE';
					}

					$sql .= ",\n";

					if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
					{
						$generator = $column_name;
					}
				break;

				case 'mssql':
					if ($column_type == '[text]')
					{
						$textimage = true;
					}

					$sql .= "\t[{$column_name}] {$column_type} ";

					if (!is_null($column_data[1]))
					{
						// For hexadecimal values do not use single quotes
						if (strpos($column_data[1], '0x') === 0)
						{
							$sql .= 'DEFAULT (' . $column_data[1] . ') ';
						}
						else
						{
							$sql .= 'DEFAULT (' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ') ';
						}
					}

					if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
					{
						$sql .= 'IDENTITY (1, 1) ';
					}

					$sql .= 'NOT NULL';
					$sql .= " ,\n";
				break;

				case 'oracle':
					$sql .= "\t{$column_name} {$column_type} ";
					$sql .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}' " : '';

					// In Oracle empty strings ('') are treated as NULL.
					// Therefore in oracle we allow NULL's for all DEFAULT '' entries
					$sql .= ($column_data[1] === '') ? ",\n" : "NOT NULL,\n";

					if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
					{
						$generator = $column_name;
					}
				break;

				case 'postgres':
					$sql .= "\t{$column_name} {$column_type} ";

					if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
					{
						$sql .= "DEFAULT nextval('{$table_name}_seq'),\n";

						// Make sure the sequence will be created before creating the table
						$sql = "CREATE SEQUENCE {$table_name}_seq;\n\n" . $sql;
					}
					else
					{
						$sql .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}' " : '';
						$sql .= "NOT NULL";

						// Unsigned? Then add a CHECK contraint
						if (in_array($orig_column_type, $unsigned_types))
						{
							$sql .= " CHECK ({$column_name} >= 0)";
						}

						$sql .= ",\n";
					}
				break;
			}
		}

		switch ($dbms)
		{
			case 'firebird':
				// Remove last line delimiter...
				$sql = substr($sql, 0, -2);
				$sql .= "\n);;\n\n";
			break;

			case 'mssql':
				$sql = substr($sql, 0, -2);
				$sql .= "\n) ON [PRIMARY]" . (($textimage) ? ' TEXTIMAGE_ON [PRIMARY]' : '') . "\n";
				$sql .= "GO\n\n";
			break;
		}

		// Write primary key
		if (isset($table_data['PRIMARY_KEY']))
		{
			if (!is_array($table_data['PRIMARY_KEY']))
			{
				$table_data['PRIMARY_KEY'] = array($table_data['PRIMARY_KEY']);
			}

			switch ($dbms)
			{
				case 'mysql_40':
				case 'mysql_41':
				case 'postgres':
					$sql .= "\tPRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . "),\n";
				break;

				case 'firebird':
					$sql .= "ALTER TABLE {$table_name} ADD PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . ");;\n\n";
				break;

				case 'sqlite':
					if ($generator === false || !in_array($generator, $table_data['PRIMARY_KEY']))
					{
						$sql .= "\tPRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . "),\n";
					}
				break;

				case 'mssql':
					$sql .= "ALTER TABLE [{$table_name}] WITH NOCHECK ADD \n";
					$sql .= "\tCONSTRAINT [PK_{$table_name}] PRIMARY KEY  CLUSTERED \n";
					$sql .= "\t(\n";
					$sql .= "\t\t[" . implode("],\n\t\t[", $table_data['PRIMARY_KEY']) . "]\n";
					$sql .= "\t)  ON [PRIMARY] \n";
					$sql .= "GO\n\n";
				break;

				case 'oracle':
					$sql .= "\tCONSTRAINT pk_{$table_name} PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . "),\n";
				break;
			}
		}

		switch ($dbms)
		{
			case 'oracle':
				// UNIQUE contrains to be added?
				if (isset($table_data['KEYS']))
				{
					foreach ($table_data['KEYS'] as $key_name => $key_data)
					{
						if (!is_array($key_data[1]))
						{
							$key_data[1] = array($key_data[1]);
						}

						if ($key_data[0] == 'UNIQUE')
						{
							$sql .= "\tCONSTRAINT u_phpbb_{$key_name} UNIQUE (" . implode(', ', $key_data[1]) . "),\n";
						}
					}
				}

				// Remove last line delimiter...
				$sql = substr($sql, 0, -2);
				$sql .= "\n)\n/\n\n";
			break;

			case 'postgres':
				// Remove last line delimiter...
				$sql = substr($sql, 0, -2);
				$sql .= "\n);\n\n";
			break;

			case 'sqlite':
				// Remove last line delimiter...
				$sql = substr($sql, 0, -2);
				$sql .= "\n);\n\n";
			break;
		}

		// Write Keys
		if (isset($table_data['KEYS']))
		{
			foreach ($table_data['KEYS'] as $key_name => $key_data)
			{
				if (!is_array($key_data[1]))
				{
					$key_data[1] = array($key_data[1]);
				}

				switch ($dbms)
				{
					case 'mysql_40':
					case 'mysql_41':
						$sql .= ($key_data[0] == 'INDEX') ? "\tKEY" : '';
						$sql .= ($key_data[0] == 'UNIQUE') ? "\tUNIQUE" : '';
						foreach ($key_data[1] as $key => $col_name)
						{
							if (isset($modded_array[$col_name]))
							{
								switch ($modded_array[$col_name])
								{
									case 'text':
									case 'blob':
										$key_data[1][$key] = $col_name . '(255)';
									break;
								}
							}
						}
						$sql .= ' ' . $key_name . ' (' . implode(', ', $key_data[1]) . "),\n";
					break;

					case 'firebird':
						$sql .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
						$sql .= ($key_data[0] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : '';

						$sql .= ' ' . $table_name . '_' . $key_name . ' ON ' . $table_name . '(' . implode(', ', $key_data[1]) . ");;\n";
					break;

					case 'mssql':
						$sql .= ($key_data[0] == 'INDEX') ? 'CREATE  INDEX' : '';
						$sql .= ($key_data[0] == 'UNIQUE') ? 'CREATE  UNIQUE  INDEX' : '';
						$sql .= " [{$key_name}] ON [{$table_name}]([" . implode('], [', $key_data[1]) . "]) ON [PRIMARY]\n";
						$sql .= "GO\n\n";
					break;

					case 'oracle':
						if ($key_data[0] == 'UNIQUE')
						{
							continue;
						}

						$sql .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';

						$sql .= " {$table_name}_{$key_name} ON {$table_name} (" . implode(', ', $key_data[1]) . ")\n";
						$sql .= "/\n";
					break;

					case 'sqlite':
						$sql .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
						$sql .= ($key_data[0] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : '';

						$sql .= " {$table_name}_{$key_name} ON {$table_name} (" . implode(', ', $key_data[1]) . ");\n";
					break;

					case 'postgres':
						$sql .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
						$sql .= ($key_data[0] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : '';

						$sql .= " {$table_name}_{$key_name} ON {$table_name} (" . implode(', ', $key_data[1]) . ");\n";
					break;
				}
			}
		}

		switch ($dbms)
		{
			case 'mysql_40':
				// Remove last line delimiter...
				$sql = substr($sql, 0, -2);
				$sql .= "\n);\n\n";
			break;

			case 'mysql_41':
				// Remove last line delimiter...
				$sql = substr($sql, 0, -2);
				$sql .= "\n) CHARACTER SET utf8 COLLATE utf8_bin;\n\n";
			break;

			// Create Generator
			case 'firebird':
				if ($generator !== false)
				{
					$sql .= "\nCREATE GENERATOR {$table_name}_gen;;\n";
					$sql .= 'SET GENERATOR ' . $table_name . "_gen TO 0;;\n\n";

					$sql .= 'CREATE TRIGGER t_' . $table_name . ' FOR ' . $table_name . "\n";
					$sql .= "BEFORE INSERT\nAS\nBEGIN\n";
					$sql .= "\tNEW.{$generator} = GEN_ID({$table_name}_gen, 1);\nEND;;\n\n";
				}
			break;

			case 'oracle':
				if ($generator !== false)
				{
					$sql .= "\nCREATE SEQUENCE {$table_name}_seq\n/\n\n";

					$sql .= "CREATE OR REPLACE TRIGGER t_{$table_name}\n";
					$sql .= "BEFORE INSERT ON {$table_name}\n";
					$sql .= "FOR EACH ROW WHEN (\n";
					$sql .= "\tnew.{$generator} IS NULL OR new.{$generator} = 0\n";
					$sql .= ")\nBEGIN\n";
					$sql .= "\tSELECT {$table_name}_seq.nextval\n";
					$sql .= "\tINTO :new.{$generator}\n";
					$sql .= "\tFROM dual;\nEND;\n/\n\n";
				}
			break;
		}

		return $sql;
	}

	/**
	* Get the real table name
	* By A_Jelly_Doughnut
	*
	* @param string $table_name The table name to get the real table name from
	*/
	function get_table_name(&$table_name)
	{
		// Use the global table prefix if a custom one is not specified
		if ($this->table_prefix === false)
		{
			global $table_prefix;
		}
		else
		{
			$table_prefix = $this->table_prefix;
		}

		static $constants = NULL;

		if (is_null($constants))
		{
			$constants = get_defined_constants();
		}

		/**
		* only do the replace if the table prefix is not already present
		* this is required since UMIL supports specifying a table via phpbb_foo
		* (where a replace would be needed)
		* or by FOO_TABLE (where a replace is already done at constant-define time)
		*/
		if (!preg_match('#^' . preg_quote($table_prefix, '#') . '#', $table_name) || !in_array($table_name, $constants, true))
		{
			$table_name = preg_replace('#^phpbb_#i', $table_prefix, $table_name);
		}
	}
}

?>