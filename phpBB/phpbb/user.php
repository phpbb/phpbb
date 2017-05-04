<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb;

/**
* Base user class
*
* This is the overarching class which contains (through session extend)
* all methods utilised for user functionality during a session.
*/
class user extends \phpbb\session
{
	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	var $style = array();
	var $date_format;

	/**
	* DateTimeZone object holding the timezone of the user
	*/
	public $timezone;

	/**
	* @var string Class name of datetime object
	*/
	protected $datetime;

	var $lang_name = false;
	var $lang_id = false;
	var $lang_path;
	var $img_lang;
	var $img_array = array();

	/** @var bool */
	protected $is_setup_flag;

	// Able to add new options (up to id 31)
	var $keyoptions = array('viewimg' => 0, 'viewflash' => 1, 'viewsmilies' => 2, 'viewsigs' => 3, 'viewavatars' => 4, 'viewcensors' => 5, 'attachsig' => 6, 'bbcode' => 8, 'smilies' => 9, 'sig_bbcode' => 15, 'sig_smilies' => 16, 'sig_links' => 17);

	/**
	* Constructor to set the lang path
	*
	* @param \phpbb\language\language	$lang			phpBB's Language loader
	* @param string						$datetime_class	Class name of datetime class
	*/
	function __construct(\phpbb\language\language $lang, $datetime_class)
	{
		global $phpbb_root_path;

		$this->lang_path = $phpbb_root_path . 'language/';
		$this->language = $lang;
		$this->datetime = $datetime_class;

		$this->is_setup_flag = false;
	}

	/**
	 * Returns whether user::setup was called
	 *
	 * @return bool
	 */
	public function is_setup()
	{
		return $this->is_setup_flag;
	}

	/**
	 * Magic getter for BC compatibility
	 *
	 * Implement array access for user::lang.
	 *
	 * @param string	$param_name	Name of the BC component the user want to access
	 *
	 * @return array	The appropriate array
	 *
	 * @deprecated 3.2.0-dev (To be removed: 4.0.0)
	 */
	public function __get($param_name)
	{
		if ($param_name === 'lang')
		{
			return $this->language->get_lang_array();
		}
		else if ($param_name === 'help')
		{
			$help_array = $this->language->get_lang_array();
			return $help_array['__help'];
		}

		return array();
	}

	/**
	* Setup basic user-specific items (style, language, ...)
	*/
	function setup($lang_set = false, $style_id = false)
	{
		global $db, $request, $template, $config, $auth, $phpEx, $phpbb_root_path, $cache;
		global $phpbb_dispatcher;

		$this->language->set_default_language($config['default_lang']);

		if ($this->data['user_id'] != ANONYMOUS)
		{
			$user_lang_name = (file_exists($this->lang_path . $this->data['user_lang'] . "/common.$phpEx")) ? $this->data['user_lang'] : basename($config['default_lang']);
			$user_date_format = $this->data['user_dateformat'];
			$user_timezone = $this->data['user_timezone'];
		}
		else
		{
			$lang_override = $request->variable('language', '');
			if ($lang_override)
			{
				$this->set_cookie('lang', $lang_override, 0, false);
			}
			else
			{
				$lang_override = $request->variable($config['cookie_name'] . '_lang', '', true, \phpbb\request\request_interface::COOKIE);
			}

			if ($lang_override)
			{
				$use_lang = basename($lang_override);
				$user_lang_name = (file_exists($this->lang_path . $use_lang . "/common.$phpEx")) ? $use_lang : basename($config['default_lang']);
				$this->data['user_lang'] = $user_lang_name;
			}
			else
			{
				$user_lang_name = basename($config['default_lang']);
			}

			$user_date_format = $config['default_dateformat'];
			$user_timezone = $config['board_timezone'];

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
						$user_lang_name = $config['default_lang'] = $accept_lang;
						break;
					}
					else
					{
						// No match on xx_YY so try xx
						$accept_lang = substr($accept_lang, 0, 2);
						$accept_lang = basename($accept_lang);

						if (file_exists($this->lang_path . $accept_lang . "/common.$phpEx"))
						{
							$user_lang_name = $config['default_lang'] = $accept_lang;
							break;
						}
					}
				}
			}
			*/
		}

		$user_data = $this->data;
		$lang_set_ext = array();

		/**
		* Event to load language files and modify user data on every page
		*
		* @event core.user_setup
		* @var	array	user_data			Array with user's data row
		* @var	string	user_lang_name		Basename of the user's langauge
		* @var	string	user_date_format	User's date/time format
		* @var	string	user_timezone		User's timezone, should be one of
		*							http://www.php.net/manual/en/timezones.php
		* @var	mixed	lang_set			String or array of language files
		* @var	array	lang_set_ext		Array containing entries of format
		* 					array(
		* 						'ext_name' => (string) [extension name],
		* 						'lang_set' => (string|array) [language files],
		* 					)
		* 					For performance reasons, only load translations
		* 					that are absolutely needed globally using this
		* 					event. Use local events otherwise.
		* @var	mixed	style_id			Style we are going to display
		* @since 3.1.0-a1
		*/
		$vars = array(
			'user_data',
			'user_lang_name',
			'user_date_format',
			'user_timezone',
			'lang_set',
			'lang_set_ext',
			'style_id',
		);
		extract($phpbb_dispatcher->trigger_event('core.user_setup', compact($vars)));

		$this->data = $user_data;
		$this->lang_name = $user_lang_name;
		$this->date_format = $user_date_format;

		$this->language->set_user_language($user_lang_name);

		try
		{
			$this->timezone = new \DateTimeZone($user_timezone);
		}
		catch (\Exception $e)
		{
			// If the timezone the user has selected is invalid, we fall back to UTC.
			$this->timezone = new \DateTimeZone('UTC');
		}

		$this->add_lang($lang_set);
		unset($lang_set);

		foreach ($lang_set_ext as $ext_lang_pair)
		{
			$this->add_lang_ext($ext_lang_pair['ext_name'], $ext_lang_pair['lang_set']);
		}
		unset($lang_set_ext);

		$style_request = $request->variable('style', 0);
		if ($style_request && (!$config['override_user_style'] || $auth->acl_get('a_styles')) && !defined('ADMIN_START'))
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
		$this->style = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// Fallback to user's standard style
		if (!$this->style && $style_id != $this->data['user_style'])
		{
			$style_id = $this->data['user_style'];

			$sql = 'SELECT *
				FROM ' . STYLES_TABLE . " s
				WHERE s.style_id = $style_id";
			$result = $db->sql_query($sql, 3600);
			$this->style = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}

		// User has wrong style
		if (!$this->style && $style_id == $this->data['user_style'])
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
			$this->style = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}

		if (!$this->style)
		{
			trigger_error('NO_STYLE_DATA', E_USER_ERROR);
		}

		// Now parse the cfg file and cache it
		$parsed_items = $cache->obtain_cfg_items($this->style);

		$check_for = array(
			'pagination_sep'    => (string) ', '
		);

		foreach ($check_for as $key => $default_value)
		{
			$this->style[$key] = (isset($parsed_items[$key])) ? $parsed_items[$key] : $default_value;
			settype($this->style[$key], gettype($default_value));

			if (is_string($default_value))
			{
				$this->style[$key] = htmlspecialchars($this->style[$key]);
			}
		}

		$template->set_style();

		$this->img_lang = $this->lang_name;

		// Call phpbb_user_session_handler() in case external application want to "bend" some variables or replace classes...
		// After calling it we continue script execution...
		phpbb_user_session_handler();

		/**
		* Execute code at the end of user setup
		*
		* @event core.user_setup_after
		* @since 3.1.6-RC1
		*/
		$phpbb_dispatcher->dispatch('core.user_setup_after');

		// If this function got called from the error handler we are finished here.
		if (defined('IN_ERROR_HANDLER'))
		{
			return;
		}

		// Disable board if the install/ directory is still present
		// For the brave development army we do not care about this, else we need to comment out this everytime we develop locally
		if (!defined('DEBUG') && !defined('ADMIN_START') && !defined('IN_INSTALL') && !defined('IN_LOGIN') && file_exists($phpbb_root_path . 'install') && !is_file($phpbb_root_path . 'install'))
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
		if ($config['board_disable'] && !defined('IN_LOGIN') && !defined('SKIP_CHECK_DISABLED') && !$auth->acl_gets('a_', 'm_') && !$auth->acl_getf_global('m_'))
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

		$this->is_setup_flag = true;

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
	*
	* @deprecated 3.2.0-dev (To be removed 4.0.0)
	*/
	function lang()
	{
		$args = func_get_args();
		return call_user_func_array(array($this->language, 'lang'), $args);
	}

	/**
	* Determine which plural form we should use.
	* For some languages this is not as simple as for English.
	*
	* @param $number        int|float   The number we want to get the plural case for. Float numbers are floored.
	* @param $force_rule    mixed   False to use the plural rule of the language package
	*                               or an integer to force a certain plural rule
	* @return int|bool     The plural-case we need to use for the number plural-rule combination, false if $force_rule
	* 					   was invalid.
	*
	* @deprecated: 3.2.0-dev (To be removed: 3.3.0)
	*/
	function get_plural_form($number, $force_rule = false)
	{
		return $this->language->get_plural_form($number, $force_rule);
	}

	/**
	* Add Language Items - use_db and use_help are assigned where needed (only use them to force inclusion)
	*
	* @param mixed $lang_set specifies the language entries to include
	* @param bool $use_db internal variable for recursion, do not use	@deprecated 3.2.0-dev (To be removed: 3.3.0)
	* @param bool $use_help internal variable for recursion, do not use	@deprecated 3.2.0-dev (To be removed: 3.3.0)
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
	*
	* Note: $use_db and $use_help should be removed. The old function was kept for BC purposes,
	* 		so the BC logic is handled here.
	*
	* @deprecated: 3.2.0-dev (To be removed: 3.3.0)
	*/
	function add_lang($lang_set, $use_db = false, $use_help = false, $ext_name = '')
	{
		if (is_array($lang_set))
		{
			foreach ($lang_set as $key => $lang_file)
			{
				// Please do not delete this line.
				// We have to force the type here, else [array] language inclusion will not work
				$key = (string) $key;

				if ($key == 'db')
				{
					// This is never used
					$this->add_lang($lang_file, true, $use_help, $ext_name);
				}
				else if ($key == 'help')
				{
					$this->add_lang($lang_file, $use_db, true, $ext_name);
				}
				else if (!is_array($lang_file))
				{
					$this->set_lang($lang_file, $use_help, $ext_name);
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
			$this->set_lang($lang_set, $use_help, $ext_name);
		}
	}

	/**
	 * BC function for loading language files
	 *
	 * @deprecated 3.2.0-dev (To be removed: 3.3.0)
	 */
	private function set_lang($lang_set, $use_help, $ext_name)
	{
		if (empty($ext_name))
		{
			$ext_name = null;
		}

		if ($use_help && strpos($lang_set, '/') !== false)
		{
			$component = dirname($lang_set) . '/help_' . basename($lang_set);

			if ($component[0] === '/')
			{
				$component = substr($component, 1);
			}
		}
		else
		{
			$component = (($use_help) ? 'help_' : '') . $lang_set;
		}

		$this->language->add_lang($component, $ext_name);
	}

	/**
	* Add Language Items from an extension - use_db and use_help are assigned where needed (only use them to force inclusion)
	*
	* @param string $ext_name The extension to load language from, or empty for core files
	* @param mixed $lang_set specifies the language entries to include
	* @param bool $use_db internal variable for recursion, do not use
	* @param bool $use_help internal variable for recursion, do not use
	*
	* Note: $use_db and $use_help should be removed. Kept for BC purposes.
	*
	* @deprecated: 3.2.0-dev (To be removed: 3.3.0)
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
		global $phpbb_dispatcher;
		static $utc;

		if (!isset($utc))
		{
			$utc = new \DateTimeZone('UTC');
		}

		$format_date_override = false;
		$function_arguments = func_get_args();
		/**
		* Execute code and/or override format_date()
		*
		* To override the format_date() function generated value
		* set $format_date_override to new return value
		*
		* @event core.user_format_date_override
		* @var DateTimeZone	utc Is DateTimeZone in UTC
		* @var array function_arguments is array comprising a function's argument list
		* @var string format_date_override Shall we return custom format (string) or not (false)
		* @since 3.2.1-RC1
		*/
		$vars = array('utc', 'function_arguments', 'format_date_override');
		extract($phpbb_dispatcher->trigger_event('core.user_format_date_override', compact($vars)));

		if (!$format_date_override)
		{
			$time = new $this->datetime($this, '@' . (int) $gmepoch, $utc);
			$time->setTimezone($this->timezone);

			return $time->format($format, $forcedate);
		}
		else
		{
			return $format_date_override;
		}
	}

	/**
	* Create a \phpbb\datetime object in the context of the current user
	*
	* @since 3.1
	* @param string $time String in a format accepted by strtotime().
	* @param DateTimeZone $timezone Time zone of the time.
	* @return \phpbb\datetime Date time object linked to the current users locale
	*/
	public function create_datetime($time = 'now', \DateTimeZone $timezone = null)
	{
		$timezone = $timezone ?: $this->timezone;
		return new $this->datetime($this, $time, $timezone);
	}

	/**
	* Get the UNIX timestamp for a datetime in the users timezone, so we can store it in the database.
	*
	* @param	string			$format		Format of the entered date/time
	* @param	string			$time		Date/time with the timezone applied
	* @param	DateTimeZone	$timezone	Timezone of the date/time, falls back to timezone of current user
	* @return	int			Returns the unix timestamp
	*/
	public function get_timestamp_from_format($format, $time, \DateTimeZone $timezone = null)
	{
		$timezone = $timezone ?: $this->timezone;
		$date = \DateTime::createFromFormat($format, $time, $timezone);
		return ($date !== false) ? $date->format('U') : false;
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
		$title = '';

		if ($alt)
		{
			$alt = $this->language->lang($alt);
			$title = ' title="' . $alt . '"';
		}
		return '<span class="imageset ' . $img . '"' . $title . '>' . $alt . '</span>';
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
