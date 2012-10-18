<?php
/**
 *
 * @author Nathan Guse (EXreaction) http://lithiumstudios.org
 * @author David Lewis (Highway of Life) highwayoflife@gmail.com
 * @package umil
 * @version $Id$
 * @copyright (c) 2008 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (!class_exists('umil'))
{
    if (!file_exists($phpbb_root_path . 'umil/umil.' . $phpEx))
	{
		trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
	}

	include($phpbb_root_path . 'umil/umil.' . $phpEx);
}

/**
 * UMIL - Unified MOD Installation File class Front End
 */
class umil_frontend extends umil
{
	// The title of the mod
	var $title = '';

	// Were there any errors so far (used when displaying results)?
	var $errors = false;

	// Was anything done at all (used when displaying results)?
	var $results = false;

	// The file we will record any errors in
	var $error_file = '';

	// Force displaying of the results?
	var $force_display_results = false;

	/**
	* Constructor
	*
	* @param string $title The title to display
	* @param bool $auto_display_results Automatically display results or not?
	* @param bool $force_display_results Allows you to force this to automatically display all results
	* @param object|bool $db Allows you to use your own $db object instead of the global $db
	*/
	function umil_frontend($title = '', $auto_display_results = false, $force_display_results = false, $db = false)
	{
		global $phpbb_root_path, $phpEx, $template, $user;

		$this->title = $title;

		// we must call the main constructor
		$this->umil(false, $db);
		$this->auto_display_results = $auto_display_results;
		$this->force_display_results = $force_display_results;

		$user->add_lang('install');

		// Setup the template
		$template->set_custom_template($phpbb_root_path . 'umil/style', 'umil');

		// The UMIL template is never stored in the database
		$user->theme['template_storedb'] = false;

		$template->set_filenames(array(
			'body' => 'index_body.html',
		));

		$title_explain = (isset($user->lang[$title . '_EXPLAIN'])) ? $user->lang[$title . '_EXPLAIN'] : '';
		$title = (isset($user->lang[$title])) ? $user->lang[$title] : $title;

		page_header($title, false);

		$template->assign_vars(array(
			'SQL_LAYER'			=> $this->db->sql_layer,
			'UMIL_ROOT_PATH'	=> $phpbb_root_path . 'umil/',

			'U_ADM_INDEX'		=> append_sid("{$phpbb_root_path}adm/index.$phpEx", false, true, $user->session_id),
			'U_INDEX'			=> append_sid("{$phpbb_root_path}index.$phpEx"),

			'PAGE_TITLE'		=> $title,
			'L_TITLE'			=> $title,
			'L_TITLE_EXPLAIN'	=> $title_explain,
		));
	}

	/**
	* Display Stages
	*
	* Outputs the stage list
	*
	* @param array $stages The list of stages.
	*	Either send the array like: array('CONFIGURE', 'INSTALL') or you can send it like array('CONFIGURE' => array('url' => $url), 'INSTALL' => array('url' => $url)) or you can use a mixture of the two.
	* @param int $selected The current stage
	*/
	function display_stages($stages, $selected = 1)
	{
		global $template, $user;

		$i = 1;
		foreach ($stages as $stage => $data)
		{
			if (!is_array($data))
			{
				$stage = $data;
				$data = array();
			}

			$template->assign_block_vars('l_block', array(
				'L_TITLE'			=> (isset($user->lang[$stage])) ? $user->lang[$stage] : $stage,
				'U_TITLE'			=> (isset($data['url'])) ? $data['url'] : false,
				'S_COMPLETE'		=> ($i < $selected) ? true : false,
				'S_SELECTED'		=> ($i == $selected) ? true : false,
			));

			$i++;
		}
	}

	/**
	* Confirm Box
	*
	* Displays an inline confirm box (makes it possible to have a nicer looking confirm box shown if you want to use stages)
	*
	* @param boolean $check True for checking if confirmed (without any additional parameters) and false for displaying the confirm box
	* @param string $title Title/Message used for confirm box.
	*		message text is _CONFIRM appended to title.
	*		If title cannot be found in user->lang a default one is displayed
	*		If title_CONFIRM cannot be found in user->lang the text given is used.
	* @param string $hidden Hidden variables
	*/
	function confirm_box($check, $title = '', $hidden = '', $html_body = 'index_body.html')
	{
		if (!$check)
		{
			global $template;
			$template->assign_var('S_CONFIRM', true);
		}

		if (is_array($hidden))
		{
			$hidden = build_hidden_fields($hidden);
		}

		return confirm_box($check, $title, $hidden, $html_body);
	}

	/**
	* Display Options
	*
	* Display a set of options from an inputted array.
	*
	* @param array $options This is the array of options.  Format it like you would if you were using the setup in acp_board except only enter what would go in the 'vars' array.
	*/
	function display_options($options)
	{
		global $phpbb_root_path, $phpEx, $template, $user;

		foreach ($options as $name => $vars)
		{
			if (!is_array($vars) && strpos($name, 'legend') === false)
			{
				continue;
			}

			if (strpos($name, 'legend') !== false)
			{
				$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if (isset($vars['explain']) && $vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if (isset($vars['explain']) && $vars['explain'])
			{
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$content = $this->build_cfg_template($type, $name, $vars);

			if (!sizeof($content))
			{
				continue;
			}

			$template->assign_block_vars('options', array(
				'KEY'			=> $name,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> (isset($vars['explain'])) ? $vars['explain'] : false,
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content['tpl'],

				// Find user link
				'S_FIND_USER'	=> (isset($content['find_user'])) ? true : false,
				'U_FIND_USER'	=> (isset($content['find_user'])) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", array('mode' => 'searchuser', 'form' => 'select_user', 'field' => 'username', 'select_single' => 'true', 'form' => 'umil', 'field' => $content['find_user_field'])) : '',
			));
		}
	}

	/**
	* Display results
	*
	* Display the results from the previous command, or you may enter your own command/result if you would like.
	*
	* @param string $command The command you would like shown (leave blank to use the last command saved in $this->command)
	* @param string $result The result you would like shown (leave blank to use the last result saved in $this->result)
	*/
	function display_results($command = '', $result = '')
	{
		global $config, $template, $user, $phpbb_root_path;

		$command = ($command) ? $command : $this->command;
		$command = (isset($user->lang[$command])) ? $user->lang[$command] : $command;
		$result = ($result) ? $result : $this->result;
		$result = (isset($user->lang[$result])) ? $user->lang[$result] : $result;

		$this->results = true;

		if ($result != $user->lang['SUCCESS'])
		{
			// Check if the umil/error_files/ is writable
			if (!is_writable("{$phpbb_root_path}umil/error_files/"))
			{
				phpbb_chmod("{$phpbb_root_path}umil/error_files/", CHMOD_ALL);
			}

			// Hopefully it is writable now.  If not there is nothing we can do.
			if (is_writable("{$phpbb_root_path}umil/error_files/"))
			{
				if ($this->errors == false)
				{
					$this->errors = true;

					// Setting up an error recording file
					$append = 0;
					$this->error_file = "{$phpbb_root_path}umil/error_files/" . strtolower($this->title) . '.txt';
					while (file_exists($this->error_file))
					{
						$this->error_file = "{$phpbb_root_path}umil/error_files/" . strtolower($this->title) . $append . '.txt';
						$append++;
					}
				}

				if (file_exists($this->error_file) && filesize($this->error_file))
				{
					$fp = fopen($this->error_file, 'rb');
					$contents = fread($fp, filesize($this->error_file));
					fclose($fp);
					phpbb_chmod($this->error_file, CHMOD_ALL);
				}
				else
				{
					$contents = ((isset($user->lang[$this->title])) ? $user->lang[$this->title] : $this->title) . "\n";
					$contents .= 'PHP Version: ' . phpversion() . "\n";
					$contents .= 'DBMS: ' . $this->db->sql_server_info() . "\n";
					$contents .= 'phpBB3 Version: ' . $config['version'] . "\n\n";
				}

				$contents .= "{$command}\n{$result}\n\n";

				$fp = fopen($this->error_file, 'wb');
				fwrite($fp, $contents);
				fclose($fp);
				phpbb_chmod($this->error_file, CHMOD_ALL);
			}
			else
			{
				$this->errors = true;
			}
		}

		if ($result != $user->lang['SUCCESS'] || $this->force_display_results == true)// || defined('DEBUG'))
		{
			$template->assign_block_vars('results', array(
				'COMMAND'	=> $command,
				'RESULT'	=> $result,
				'S_SUCCESS'	=> ($result == $user->lang['SUCCESS']) ? true : false,
			));
		}
	}

	/**
	* Done
	*
	* This should be called when everything is done for this page.
	*/
	function done()
	{
		global $phpbb_root_path, $phpEx, $template, $user;

		$download_file = ($this->error_file) ? append_sid("{$phpbb_root_path}umil/file.$phpEx", 'file=' . basename($this->error_file, '.txt')) : '';
		$filename = ($this->error_file) ? 'umil/error_files/' . basename($this->error_file) : '';

		$template->assign_vars(array(
			'U_ERROR_FILE'		=> $this->error_file,

			'L_RESULTS'			=> ($this->errors) ? $user->lang['FAIL'] : $user->lang['SUCCESS'],
			'L_ERROR_NOTICE'	=> ($this->errors) ? (($this->error_file) ? sprintf($user->lang['ERROR_NOTICE'], $download_file, $filename) : $user->lang['ERROR_NOTICE_NO_FILE']) : '',

			'S_RESULTS'			=> $this->results,
			'S_SUCCESS'			=> ($this->errors) ? false : true,
			'S_PERMISSIONS'		=> $this->permissions_added,
		));

		page_footer();
	}

	/**
	* Build configuration template for acp configuration pages
	*
	* Slightly modified from adm/index.php
	*/
	function build_cfg_template($tpl_type, $name, $vars)
	{
		global $user;

		$tpl = array();

		$default = (isset($vars['default'])) ? request_var($name, $vars['default']) : request_var($name, '');

		switch ($tpl_type[0])
		{
			case 'text':
				// If requested set some vars so that we later can display the link correct
				if (isset($vars['select_user']) && $vars['select_user'] === true)
				{
					$tpl['find_user']		= true;
					$tpl['find_user_field']	= $name;
				}
			case 'password':
				$size = (int) $tpl_type[1];
				$maxlength = (int) $tpl_type[2];

				$tpl['tpl'] = '<input id="' . $name . '" type="' . $tpl_type[0] . '"' . (($size) ? ' size="' . $size . '"' : '') . ' maxlength="' . (($maxlength) ? $maxlength : 255) . '" name="' . $name . '" value="' . $default . '" />';
			break;

			case 'textarea':
				$rows = (int) $tpl_type[1];
				$cols = (int) $tpl_type[2];

				$tpl['tpl'] = '<textarea id="' . $name . '" name="' . $name . '" rows="' . $rows . '" cols="' . $cols . '">' . $default . '</textarea>';
			break;

			case 'radio':
				$name_yes	= ($default) ? ' checked="checked"' : '';
				$name_no	= (!$default) ? ' checked="checked"' : '';

				$tpl_type_cond = explode('_', $tpl_type[1]);
				$type_no = ($tpl_type_cond[0] == 'disabled' || $tpl_type_cond[0] == 'enabled') ? false : true;

				$tpl_no = '<label><input type="radio" name="' . $name . '" value="0"' . $name_no . ' class="radio" /> ' . (($type_no) ? $user->lang['NO'] : $user->lang['DISABLED']) . '</label>';
				$tpl_yes = '<label><input type="radio" id="' . $name . '" name="' . $name . '" value="1"' . $name_yes . ' class="radio" /> ' . (($type_no) ? $user->lang['YES'] : $user->lang['ENABLED']) . '</label>';

				$tpl['tpl'] = ($tpl_type_cond[0] == 'yes' || $tpl_type_cond[0] == 'enabled') ? $tpl_yes . $tpl_no : $tpl_no . $tpl_yes;
			break;

			case 'checkbox':
				$checked	= ($default) ? ' checked="checked"' : '';

				$tpl['tpl'] = '<input type="checkbox" id="' . $name . '" name="' . $name . '"' . $checked . ' />';
			break;

			case 'select':
			case 'select_multiple':
			case 'custom':

				$return = '';

				if (isset($vars['function']))
				{
					$call = $vars['function'];
				}
				else
				{
					break;
				}

				if (isset($vars['params']))
				{
					$args = array();
					foreach ($vars['params'] as $value)
					{
						switch ($value)
						{
							case '{CONFIG_VALUE}':
								$value = $default;
							break;

							case '{KEY}':
								$value = $name;
							break;
						}

						$args[] = $value;
					}
				}
				else
				{
					if ($tpl_type[0] == 'select_multiple')
					{
						$new[$config_key] = @unserialize(trim($new[$config_key]));
					}

					$args = array($default, $name);
				}

				$return = call_user_func_array($call, $args);


				if ($tpl_type[0] == 'select_multiple')
				{
					$tpl = '<select id="' . $key . '" name="' . $name . '[]" multiple="multiple">' . $return . '</select>';
				}
				else if ($tpl_type[0] == 'select')
				{
					$multiple	= ((isset($vars['multiple']) && $vars['multiple']) ? ' multiple="multiple"' : '');
					$tpl['tpl']		= '<select id="' . $name . '" name="' . $name . (!empty($multiple) ? '[]' : '') . '"' . $multiple . '>' . $return . '</select>';
				}
				else
				{
					$tpl['tpl'] = $return;
				}

			break;

			default:
			break;
		}

		if (isset($vars['append']))
		{
			$tpl['tpl'] .= $vars['append'];
		}

		return $tpl;
	}
}

// Compatibility
if (!function_exists('phpbb_chmod'))
{
	// They shouldn't be defined...but just in case...
	if (!defined('CHMOD_ALL'))
	{
		@define('CHMOD_ALL', 7);
		@define('CHMOD_READ', 4);
		@define('CHMOD_WRITE', 2);
		@define('CHMOD_EXECUTE', 1);
	}

	/**
	* Global function for chmodding directories and files for internal use
	* This function determines owner and group whom the file belongs to and user and group of PHP and then set safest possible file permissions.
	* The function determines owner and group from common.php file and sets the same to the provided file. Permissions are mapped to the group, user always has rw(x) permission.
	* The function uses bit fields to build the permissions.
	* The function sets the appropiate execute bit on directories.
	*
	* Supported constants representing bit fields are:
	*
	* CHMOD_ALL - all permissions (7)
	* CHMOD_READ - read permission (4)
	* CHMOD_WRITE - write permission (2)
	* CHMOD_EXECUTE - execute permission (1)
	*
	* NOTE: The function uses POSIX extension and fileowner()/filegroup() functions. If any of them is disabled, this function tries to build proper permissions, by calling is_readable() and is_writable() functions.
	*
	* @param $filename The file/directory to be chmodded
	* @param $perms Permissions to set
	* @return true on success, otherwise false
	*
	* @author faw, phpBB Group
	*/
	function phpbb_chmod($filename, $perms = CHMOD_READ)
	{
		// Return if the file no longer exists.
		if (!file_exists($filename))
		{
			return false;
		}

		if (!function_exists('fileowner') || !function_exists('filegroup'))
		{
			$file_uid = $file_gid = false;
			$common_php_owner = $common_php_group = false;
		}
		else
		{
			global $phpbb_root_path, $phpEx;

			// Determine owner/group of common.php file and the filename we want to change here
			$common_php_owner = fileowner($phpbb_root_path . 'common.' . $phpEx);
			$common_php_group = filegroup($phpbb_root_path . 'common.' . $phpEx);

			$file_uid = fileowner($filename);
			$file_gid = filegroup($filename);

			// Try to set the owner to the same common.php has
			if ($common_php_owner !== $file_uid && $common_php_owner !== false && $file_uid !== false)
			{
				// Will most likely not work
				if (@chown($filename, $common_php_owner));
				{
					clearstatcache();
					$file_uid = fileowner($filename);
				}
			}

			// Try to set the group to the same common.php has
			if ($common_php_group !== $file_gid && $common_php_group !== false && $file_gid !== false)
			{
				if (@chgrp($filename, $common_php_group));
				{
					clearstatcache();
					$file_gid = filegroup($filename);
				}
			}
		}

		// And the owner and the groups PHP is running under.
		$php_uid = (function_exists('posix_getuid')) ? @posix_getuid() : false;
		$php_gids = (function_exists('posix_getgroups')) ? @posix_getgroups() : false;

		// Who is PHP?
		if ($file_uid === false || $file_gid === false || $php_uid === false || $php_gids === false)
		{
			$php = NULL;
		}
		else if ($file_uid == $php_uid /* && $common_php_owner !== false && $common_php_owner === $file_uid*/)
		{
			$php = 'owner';
		}
		else if (in_array($file_gid, $php_gids))
		{
			$php = 'group';
		}
		else
		{
			$php = 'other';
		}

		// Owner always has read/write permission
		$owner = CHMOD_READ | CHMOD_WRITE;
		if (is_dir($filename))
		{
			$owner |= CHMOD_EXECUTE;

			// Only add execute bit to the permission if the dir needs to be readable
			if ($perms & CHMOD_READ)
			{
				$perms |= CHMOD_EXECUTE;
			}
		}

		switch ($php)
		{
			case null:
			case 'owner':
				/* ATTENTION: if php is owner or NULL we set it to group here. This is the most failsafe combination for the vast majority of server setups.

				$result = @chmod($filename, ($owner << 6) + (0 << 3) + (0 << 0));

				clearstatcache();

				if (!is_null($php) || (is_readable($filename) && is_writable($filename)))
				{
					break;
				}
			*/

			case 'group':
				$result = @chmod($filename, ($owner << 6) + ($perms << 3) + (0 << 0));

				clearstatcache();

				if (!is_null($php) || ((!($perms & CHMOD_READ) || is_readable($filename)) && (!($perms & CHMOD_WRITE) || is_writable($filename))))
				{
					break;
				}

			case 'other':
				$result = @chmod($filename, ($owner << 6) + ($perms << 3) + ($perms << 0));

				clearstatcache();

				if (!is_null($php) || ((!($perms & CHMOD_READ) || is_readable($filename)) && (!($perms & CHMOD_WRITE) || is_writable($filename))))
				{
					break;
				}

			default:
				return false;
			break;
		}

		return $result;
	}
}
?>