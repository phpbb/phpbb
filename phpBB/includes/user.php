<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
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
* Base user class
*
* This is the overarching class which contains (through session extend)
* all methods utilised for user functionality during a session.
*
* @package phpBB3
*/
class phpbb_user extends phpbb_session
{
	var $lang = array();
	var $help = array();
	var $theme = array();
	var $date_format;
	var $timezone;
	var $dst;

	var $lang_name = false;
	var $lang_id = false;
	var $lang_path;
	var $img_lang;
	var $img_array = array();

	// Able to add new options (up to id 31)
	var $keyoptions = array('viewimg' => 0, 'viewflash' => 1, 'viewsmilies' => 2, 'viewsigs' => 3, 'viewavatars' => 4, 'viewcensors' => 5, 'attachsig' => 6, 'bbcode' => 8, 'smilies' => 9, 'popuppm' => 10, 'sig_bbcode' => 15, 'sig_smilies' => 16, 'sig_links' => 17);

	/**
	* Constructor to set the lang path
	*/
	function __construct()
	{
		global $phpbb_root_path;

		$this->lang_path = $phpbb_root_path . 'language/';
	}

	/**
	* Function to set custom language path (able to use directory outside of phpBB)
	*
	* @param string $lang_path New language path used.
	* @access public
	*/
	function set_custom_lang_path($lang_path)
	{
		$this->lang_path = $lang_path;

		if (substr($this->lang_path, -1) != '/')
		{
			$this->lang_path .= '/';
		}
	}

	/**
	* Setup basic user-specific items (style, language, ...)
	*/
	function setup($lang_set = false, $style_id = false)
	{
		global $db, $style, $template, $config, $auth, $phpEx, $phpbb_root_path, $cache;

		if ($this->data['user_id'] != ANONYMOUS)
		{
			$this->lang_name = (file_exists($this->lang_path . $this->data['user_lang'] . "/common.$phpEx")) ? $this->data['user_lang'] : basename($config['default_lang']);

			$this->date_format = $this->data['user_dateformat'];
			$this->timezone = $this->data['user_timezone'] * 3600;
			$this->dst = $this->data['user_dst'] * 3600;
		}
		else
		{
			$this->lang_name = basename($config['default_lang']);
			$this->date_format = $config['default_dateformat'];
			$this->timezone = $config['board_timezone'] * 3600;
			$this->dst = $config['board_dst'] * 3600;

			/**
			* If a guest user is surfing, we try to guess his/her language first by obtaining the browser language
			* If re-enabled we need to make sure only those languages installed are checked
			* Commented out so we do not loose the code.

			if ($request->header('Accept-Language'))
			{
				$accept_lang_ary = explode(',', $request->header('Accept-Language'));

				foreach ($accept_lang_ary as $accept_lang)
				{
					// Set correct format ... guess full xx_YY form
					$accept_lang = substr($accept_lang, 0, 2) . '_' . strtoupper(substr($accept_lang, 3, 2));
					$accept_lang = basename($accept_lang);

					if (file_exists($this->lang_path . $accept_lang . "/common.$phpEx"))
					{
						$this->lang_name = $config['default_lang'] = $accept_lang;
						break;
					}
					else
					{
						// No match on xx_YY so try xx
						$accept_lang = substr($accept_lang, 0, 2);
						$accept_lang = basename($accept_lang);

						if (file_exists($this->lang_path . $accept_lang . "/common.$phpEx"))
						{
							$this->lang_name = $config['default_lang'] = $accept_lang;
							break;
						}
					}
				}
			}
			*/
		}

		// We include common language file here to not load it every time a custom language file is included
		$lang = &$this->lang;

		// Do not suppress error if in DEBUG_EXTRA mode
		$include_result = (defined('DEBUG_EXTRA')) ? (include $this->lang_path . $this->lang_name . "/common.$phpEx") : (@include $this->lang_path . $this->lang_name . "/common.$phpEx");

		if ($include_result === false)
		{
			die('Language file ' . $this->lang_path . $this->lang_name . "/common.$phpEx" . " couldn't be opened.");
		}

		$this->add_lang($lang_set);
		unset($lang_set);

		$style_request = request_var('style', 0);
		if ($style_request && $auth->acl_get('a_styles') && !defined('ADMIN_START'))
		{
			global $SID, $_EXTRA_URL;

			$style_id = $style_request;
			$SID .= '&amp;style=' . $style_id;
			$_EXTRA_URL = array('style=' . $style_id);
		}
		else
		{
			// Set up style
			$style_id = ($style_id) ? $style_id : ((!$config['override_user_style']) ? $this->data['user_style'] : $config['default_style']);
		}

		$sql = 'SELECT *
			FROM ' . STYLES_TABLE . " s
			WHERE s.style_id = $style_id";
		$result = $db->sql_query($sql, 3600);
		$this->theme = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// User has wrong style
		if (!$this->theme && $style_id == $this->data['user_style'])
		{
			$style_id = $this->data['user_style'] = $config['default_style'];

			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_style = $style_id
				WHERE user_id = {$this->data['user_id']}";
			$db->sql_query($sql);

			$sql = 'SELECT *
				FROM ' . STYLES_TABLE . " s
				WHERE s.style_id = $style_id";
			$result = $db->sql_query($sql, 3600);
			$this->theme = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}

		if (!$this->theme)
		{
			trigger_error('Could not get style data', E_USER_ERROR);
		}

		// Now parse the cfg file and cache it
		$parsed_items = $cache->obtain_cfg_items($this->theme);

		// We are only interested in the theme configuration for now
		$parsed_items = $parsed_items['theme'];

		$check_for = array(
			'pagination_sep'    => (string) ', '
		);

		foreach ($check_for as $key => $default_value)
		{
			$this->theme[$key] = (isset($parsed_items[$key])) ? $parsed_items[$key] : $default_value;
			settype($this->theme[$key], gettype($default_value));

			if (is_string($default_value))
			{
				$this->theme[$key] = htmlspecialchars($this->theme[$key]);
			}
		}

		$style->set_style();

		$this->img_lang = $this->lang_name;

		// Call phpbb_user_session_handler() in case external application want to "bend" some variables or replace classes...
		// After calling it we continue script execution...
		phpbb_user_session_handler();

		// If this function got called from the error handler we are finished here.
		if (defined('IN_ERROR_HANDLER'))
		{
			return;
		}

		// Disable board if the install/ directory is still present
		// For the brave development army we do not care about this, else we need to comment out this everytime we develop locally
		if (!defined('DEBUG_EXTRA') && !defined('ADMIN_START') && !defined('IN_INSTALL') && !defined('IN_LOGIN') && file_exists($phpbb_root_path . 'install') && !is_file($phpbb_root_path . 'install'))
		{
			// Adjust the message slightly according to the permissions
			if ($auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_'))
			{
				$message = 'REMOVE_INSTALL';
			}
			else
			{
				$message = (!empty($config['board_disable_msg'])) ? $config['board_disable_msg'] : 'BOARD_DISABLE';
			}
			trigger_error($message);
		}

		// Is board disabled and user not an admin or moderator?
		if ($config['board_disable'] && !defined('IN_LOGIN') && !$auth->acl_gets('a_', 'm_') && !$auth->acl_getf_global('m_'))
		{
			if ($this->data['is_bot'])
			{
				send_status_line(503, 'Service Unavailable');
			}

			$message = (!empty($config['board_disable_msg'])) ? $config['board_disable_msg'] : 'BOARD_DISABLE';
			trigger_error($message);
		}

		// Is load exceeded?
		if ($config['limit_load'] && $this->load !== false)
		{
			if ($this->load > floatval($config['limit_load']) && !defined('IN_LOGIN') && !defined('IN_ADMIN'))
			{
				// Set board disabled to true to let the admins/mods get the proper notification
				$config['board_disable'] = '1';

				if (!$auth->acl_gets('a_', 'm_') && !$auth->acl_getf_global('m_'))
				{
					if ($this->data['is_bot'])
					{
						send_status_line(503, 'Service Unavailable');
					}
					trigger_error('BOARD_UNAVAILABLE');
				}
			}
		}

		if (isset($this->data['session_viewonline']))
		{
			// Make sure the user is able to hide his session
			if (!$this->data['session_viewonline'])
			{
				// Reset online status if not allowed to hide the session...
				if (!$auth->acl_get('u_hideonline'))
				{
					$sql = 'UPDATE ' . SESSIONS_TABLE . '
						SET session_viewonline = 1
						WHERE session_user_id = ' . $this->data['user_id'];
					$db->sql_query($sql);
					$this->data['session_viewonline'] = 1;
				}
			}
			else if (!$this->data['user_allow_viewonline'])
			{
				// the user wants to hide and is allowed to  -> cloaking device on.
				if ($auth->acl_get('u_hideonline'))
				{
					$sql = 'UPDATE ' . SESSIONS_TABLE . '
						SET session_viewonline = 0
						WHERE session_user_id = ' . $this->data['user_id'];
					$db->sql_query($sql);
					$this->data['session_viewonline'] = 0;
				}
			}
		}


		// Does the user need to change their password? If so, redirect to the
		// ucp profile reg_details page ... of course do not redirect if we're already in the ucp
		if (!defined('IN_ADMIN') && !defined('ADMIN_START') && $config['chg_passforce'] && !empty($this->data['is_registered']) && $auth->acl_get('u_chgpasswd') && $this->data['user_passchg'] < time() - ($config['chg_passforce'] * 86400))
		{
			if (strpos($this->page['query_string'], 'mode=reg_details') === false && $this->page['page_name'] != "ucp.$phpEx")
			{
				redirect(append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=profile&amp;mode=reg_details'));
			}
		}

		return;
	}

	/**
	* More advanced language substitution
	* Function to mimic sprintf() with the possibility of using phpBB's language system to substitute nullar/singular/plural forms.
	* Params are the language key and the parameters to be substituted.
	* This function/functionality is inspired by SHS` and Ashe.
	*
	* Example call: <samp>$user->lang('NUM_POSTS_IN_QUEUE', 1);</samp>
	*
	* If the first parameter is an array, the elements are used as keys and subkeys to get the language entry:
	* Example: <samp>$user->lang(array('datetime', 'AGO'), 1)</samp> uses $user->lang['datetime']['AGO'] as language entry.
	*/
	function lang()
	{
		$args = func_get_args();
		$key = $args[0];

		if (is_array($key))
		{
			$lang = &$this->lang[array_shift($key)];

			foreach ($key as $_key)
			{
				$lang = &$lang[$_key];
			}
		}
		else
		{
			$lang = &$this->lang[$key];
		}

		// Return if language string does not exist
		if (!isset($lang) || (!is_string($lang) && !is_array($lang)))
		{
			return $key;
		}

		// If the language entry is a string, we simply mimic sprintf() behaviour
		if (is_string($lang))
		{
			if (sizeof($args) == 1)
			{
				return $lang;
			}

			// Replace key with language entry and simply pass along...
			$args[0] = $lang;
			return call_user_func_array('sprintf', $args);
		}
		else if (sizeof($lang) == 0)
		{
			// If the language entry is an empty array, we just return the language key
			return $args[0];
		}

		// It is an array... now handle different nullar/singular/plural forms
		$key_found = false;

		// We now get the first number passed and will select the key based upon this number
		for ($i = 1, $num_args = sizeof($args); $i < $num_args; $i++)
		{
			if (is_int($args[$i]) || is_float($args[$i]))
			{
				if ($args[$i] == 0 && isset($lang[0]))
				{
					// We allow each translation using plural forms to specify a version for the case of 0 things,
					// so that "0 users" may be displayed as "No users".
					$key_found = 0;
					break;
				}
				else
				{
					$use_plural_form = $this->get_plural_form($args[$i]);
					if (isset($lang[$use_plural_form]))
					{
						// The key we should use exists, so we use it.
						$key_found = $use_plural_form;
					}
					else
					{
						// If the key we need to use does not exist, we fall back to the previous one.
						$numbers = array_keys($lang);

						foreach ($numbers as $num)
						{
							if ($num > $use_plural_form)
							{
								break;
							}

							$key_found = $num;
						}
					}
					break;
				}
			}
		}

		// Ok, let's check if the key was found, else use the last entry (because it is mostly the plural form)
		if ($key_found === false)
		{
			$numbers = array_keys($lang);
			$key_found = end($numbers);
		}

		// Use the language string we determined and pass it to sprintf()
		$args[0] = $lang[$key_found];
		return call_user_func_array('sprintf', $args);
	}

	/**
	* Determine which plural form we should use.
	* For some languages this is not as simple as for English.
	*
	* @param $number        int|float   The number we want to get the plural case for. Float numbers are floored.
	* @param $force_rule    mixed   False to use the plural rule of the language package
	*                               or an integer to force a certain plural rule
	* @return   int     The plural-case we need to use for the number plural-rule combination
	*/
	function get_plural_form($number, $force_rule = false)
	{
		$number = (int) $number;

		// Default to English system
		$plural_rule = ($force_rule !== false) ? $force_rule : ((isset($this->lang['PLURAL_RULE'])) ? $this->lang['PLURAL_RULE'] : 1);

		return phpbb_get_plural_form($plural_rule, $number);
	}

	/**
	* Add Language Items - use_db and use_help are assigned where needed (only use them to force inclusion)
	*
	* @param mixed $lang_set specifies the language entries to include
	* @param bool $use_db internal variable for recursion, do not use
	* @param bool $use_help internal variable for recursion, do not use
	* @param string $ext_name The extension to load language from, or empty for core files
	*
	* Examples:
	* <code>
	* $lang_set = array('posting', 'help' => 'faq');
	* $lang_set = array('posting', 'viewtopic', 'help' => array('bbcode', 'faq'))
	* $lang_set = array(array('posting', 'viewtopic'), 'help' => array('bbcode', 'faq'))
	* $lang_set = 'posting'
	* $lang_set = array('help' => 'faq', 'db' => array('help:faq', 'posting'))
	* </code>
	*/
	function add_lang($lang_set, $use_db = false, $use_help = false, $ext_name = '')
	{
		global $phpEx;

		if (is_array($lang_set))
		{
			foreach ($lang_set as $key => $lang_file)
			{
				// Please do not delete this line.
				// We have to force the type here, else [array] language inclusion will not work
				$key = (string) $key;

				if ($key == 'db')
				{
					$this->add_lang($lang_file, true, $use_help, $ext_name);
				}
				else if ($key == 'help')
				{
					$this->add_lang($lang_file, $use_db, true, $ext_name);
				}
				else if (!is_array($lang_file))
				{
					$this->set_lang($this->lang, $this->help, $lang_file, $use_db, $use_help, $ext_name);
				}
				else
				{
					$this->add_lang($lang_file, $use_db, $use_help, $ext_name);
				}
			}
			unset($lang_set);
		}
		else if ($lang_set)
		{
			$this->set_lang($this->lang, $this->help, $lang_set, $use_db, $use_help, $ext_name);
		}
	}

	/**
	* Add Language Items from an extension - use_db and use_help are assigned where needed (only use them to force inclusion)
	*
	* @param string $ext_name The extension to load language from, or empty for core files
	* @param mixed $lang_set specifies the language entries to include
	* @param bool $use_db internal variable for recursion, do not use
	* @param bool $use_help internal variable for recursion, do not use
	*/
	function add_lang_ext($ext_name, $lang_set, $use_db = false, $use_help = false)
	{
		if ($ext_name === '/')
		{
			$ext_name = '';
		}

		$this->add_lang($lang_set, $use_db, $use_help, $ext_name);
	}

	/**
	* Set language entry (called by add_lang)
	* @access private
	*/
	function set_lang(&$lang, &$help, $lang_file, $use_db = false, $use_help = false, $ext_name = '')
	{
		global $phpbb_root_path, $phpEx;

		// Make sure the language name is set (if the user setup did not happen it is not set)
		if (!$this->lang_name)
		{
			global $config;
			$this->lang_name = basename($config['default_lang']);
		}

		// $lang == $this->lang
		// $help == $this->help
		// - add appropriate variables here, name them as they are used within the language file...
		if (!$use_db)
		{
			if ($use_help && strpos($lang_file, '/') !== false)
			{
				$filename = dirname($lang_file) . '/help_' . basename($lang_file);
			}
			else
			{
				$filename = (($use_help) ? 'help_' : '') . $lang_file;
			}

			if ($ext_name)
			{
				global $phpbb_extension_manager;
				$ext_path = $phpbb_extension_manager->get_extension_path($ext_name, true);

				$lang_path = $ext_path . 'language/';
			}
			else
			{
				$lang_path = $this->lang_path;
			}

			if (strpos($phpbb_root_path . $filename, $lang_path . $this->lang_name . '/') === 0)
			{
				$language_filename = $phpbb_root_path . $filename;
			}
			else
			{
				$language_filename = $lang_path . $this->lang_name . '/' . $filename . '.' . $phpEx;
			}

			if (!file_exists($language_filename))
			{
				global $config;

				if ($this->lang_name == 'en')
				{
					// The user's selected language is missing the file, the board default's language is missing the file, and the file doesn't exist in /en.
					$language_filename = str_replace($lang_path . 'en', $lang_path . $this->data['user_lang'], $language_filename);
					trigger_error('Language file ' . $language_filename . ' couldn\'t be opened.', E_USER_ERROR);
				}
				else if ($this->lang_name == basename($config['default_lang']))
				{
					// Fall back to the English Language
					$this->lang_name = 'en';
					$this->set_lang($lang, $help, $lang_file, $use_db, $use_help, $ext_name);
				}
				else if ($this->lang_name == $this->data['user_lang'])
				{
					// Fall back to the board default language
					$this->lang_name = basename($config['default_lang']);
					$this->set_lang($lang, $help, $lang_file, $use_db, $use_help, $ext_name);
				}

				// Reset the lang name
				$this->lang_name = (file_exists($lang_path . $this->data['user_lang'] . "/common.$phpEx")) ? $this->data['user_lang'] : basename($config['default_lang']);
				return;
			}

			// Do not suppress error if in DEBUG_EXTRA mode
			$include_result = (defined('DEBUG_EXTRA')) ? (include $language_filename) : (@include $language_filename);

			if ($include_result === false)
			{
				trigger_error('Language file ' . $language_filename . ' couldn\'t be opened.', E_USER_ERROR);
			}
		}
		else if ($use_db)
		{
			// Get Database Language Strings
			// Put them into $lang if nothing is prefixed, put them into $help if help: is prefixed
			// For example: help:faq, posting
		}
	}

	/**
	* Format user date
	*
	* @param int $gmepoch unix timestamp
	* @param string $format date format in date() notation. | used to indicate relative dates, for example |d m Y|, h:i is translated to Today, h:i.
	* @param bool $forcedate force non-relative date format.
	*
	* @return mixed translated date
	*/
	function format_date($gmepoch, $format = false, $forcedate = false)
	{
		static $midnight;
		static $date_cache;

		$format = (!$format) ? $this->date_format : $format;
		$now = time();
		$delta = $now - $gmepoch;

		if (!isset($date_cache[$format]))
		{
			// Is the user requesting a friendly date format (i.e. 'Today 12:42')?
			$date_cache[$format] = array(
				'is_short'      => strpos($format, '|'),
				'format_short'  => substr($format, 0, strpos($format, '|')) . '||' . substr(strrchr($format, '|'), 1),
				'format_long'   => str_replace('|', '', $format),
				'lang'          => $this->lang['datetime'],
			);

			// Short representation of month in format? Some languages use different terms for the long and short format of May
			if ((strpos($format, '\M') === false && strpos($format, 'M') !== false) || (strpos($format, '\r') === false && strpos($format, 'r') !== false))
			{
				$date_cache[$format]['lang']['May'] = $this->lang['datetime']['May_short'];
			}
		}

		// Zone offset
		$zone_offset = $this->timezone + $this->dst;

		// Show date < 1 hour ago as 'xx min ago' but not greater than 60 seconds in the future
		// A small tolerence is given for times in the future but in the same minute are displayed as '< than a minute ago'
		if ($delta < 3600 && $delta > -60 && ($delta >= -5 || (($now / 60) % 60) == (($gmepoch / 60) % 60)) && $date_cache[$format]['is_short'] !== false && !$forcedate && isset($this->lang['datetime']['AGO']))
		{
			return $this->lang(array('datetime', 'AGO'), max(0, (int) floor($delta / 60)));
		}

		if (!$midnight)
		{
			list($d, $m, $y) = explode(' ', gmdate('j n Y', time() + $zone_offset));
			$midnight = gmmktime(0, 0, 0, $m, $d, $y) - $zone_offset;
		}

		if ($date_cache[$format]['is_short'] !== false && !$forcedate && !($gmepoch < $midnight - 86400 || $gmepoch > $midnight + 172800))
		{
			$day = false;

			if ($gmepoch > $midnight + 86400)
			{
				$day = 'TOMORROW';
			}
			else if ($gmepoch > $midnight)
			{
				$day = 'TODAY';
			}
			else if ($gmepoch > $midnight - 86400)
			{
				$day = 'YESTERDAY';
			}

			if ($day !== false)
			{
				return str_replace('||', $this->lang['datetime'][$day], strtr(@gmdate($date_cache[$format]['format_short'], $gmepoch + $zone_offset), $date_cache[$format]['lang']));
			}
		}

		return strtr(@gmdate($date_cache[$format]['format_long'], $gmepoch + $zone_offset), $date_cache[$format]['lang']);
	}

	/**
	* Get language id currently used by the user
	*/
	function get_iso_lang_id()
	{
		global $config, $db;

		if (!empty($this->lang_id))
		{
			return $this->lang_id;
		}

		if (!$this->lang_name)
		{
			$this->lang_name = $config['default_lang'];
		}

		$sql = 'SELECT lang_id
			FROM ' . LANG_TABLE . "
			WHERE lang_iso = '" . $db->sql_escape($this->lang_name) . "'";
		$result = $db->sql_query($sql);
		$this->lang_id = (int) $db->sql_fetchfield('lang_id');
		$db->sql_freeresult($result);

		return $this->lang_id;
	}

	/**
	* Get users profile fields
	*/
	function get_profile_fields($user_id)
	{
		global $db;

		if (isset($this->profile_fields))
		{
			return;
		}

		$sql = 'SELECT *
			FROM ' . PROFILE_FIELDS_DATA_TABLE . "
			WHERE user_id = $user_id";
		$result = $db->sql_query_limit($sql, 1);
		$this->profile_fields = (!($row = $db->sql_fetchrow($result))) ? array() : $row;
		$db->sql_freeresult($result);
	}

	/**
	* Specify/Get image
	*/
	function img($img, $alt = '')
	{
		$alt = (!empty($this->lang[$alt])) ? $this->lang[$alt] : $alt;
		return '<span class="imageset ' . $img . '">' . $alt . '</span>';
	}

	/**
	* Get option bit field from user options.
	*
	* @param int $key option key, as defined in $keyoptions property.
	* @param int $data bit field value to use, or false to use $this->data['user_options']
	* @return bool true if the option is set in the bit field, false otherwise
	*/
	function optionget($key, $data = false)
	{
		$var = ($data !== false) ? $data : $this->data['user_options'];
		return phpbb_optionget($this->keyoptions[$key], $var);
	}

	/**
	* Set option bit field for user options.
	*
	* @param int $key Option key, as defined in $keyoptions property.
	* @param bool $value True to set the option, false to clear the option.
	* @param int $data Current bit field value, or false to use $this->data['user_options']
	* @return int|bool If $data is false, the bit field is modified and
	*                  written back to $this->data['user_options'], and
	*                  return value is true if the bit field changed and
	*                  false otherwise. If $data is not false, the new
	*                  bitfield value is returned.
	*/
	function optionset($key, $value, $data = false)
	{
		$var = ($data !== false) ? $data : $this->data['user_options'];

		$new_var = phpbb_optionset($this->keyoptions[$key], $value, $var);

		if ($data === false)
		{
			if ($new_var != $var)
			{
				$this->data['user_options'] = $new_var;
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $new_var;
		}
	}

	/**
	* Funtion to make the user leave the NEWLY_REGISTERED system group.
	* @access public
	*/
	function leave_newly_registered()
	{
		global $db;

		if (empty($this->data['user_new']))
		{
			return false;
		}

		if (!function_exists('remove_newly_registered'))
		{
			global $phpbb_root_path, $phpEx;

			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}
		if ($group = remove_newly_registered($this->data['user_id'], $this->data))
		{
			$this->data['group_id'] = $group;

		}
		$this->data['user_permissions'] = '';
		$this->data['user_new'] = 0;

		return true;
	}

	/**
	* Returns all password protected forum ids the user is currently NOT authenticated for.
	*
	* @return array     Array of forum ids
	* @access public
	*/
	function get_passworded_forums()
	{
		global $db;

		$sql = 'SELECT f.forum_id, fa.user_id
			FROM ' . FORUMS_TABLE . ' f
			LEFT JOIN ' . FORUMS_ACCESS_TABLE . " fa
				ON (fa.forum_id = f.forum_id
					AND fa.session_id = '" . $db->sql_escape($this->session_id) . "')
			WHERE f.forum_password <> ''";
		$result = $db->sql_query($sql);

		$forum_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$forum_id = (int) $row['forum_id'];

			if ($row['user_id'] != $this->data['user_id'])
			{
				$forum_ids[$forum_id] = $forum_id;
			}
		}
		$db->sql_freeresult($result);

		return $forum_ids;
	}
}
