<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005, 2008 phpBB Group
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
* Base user class
*
* This is the overarching class which contains (through session extend)
* all methods utilised for user functionality during a session.
*
* @package phpBB3
*/
class phpbb_user extends phpbb_session
{
	/**
	* @var array required phpBB objects
	*/
	public $phpbb_required = array('config', 'acl', 'db', 'template', 'security', 'server-vars', 'acm', 'api:user');

	/**
	* @var array Optional phpBB objects
	*/
	public $phpbb_optional = array();

	/**
	* @var array language entries
	*/
	public $lang = array();

	/**
	* @var array help entries
	*/
	public $help = array();

	/**
	* @var array theme entries
	*/
	public $theme = array();

	/**
	* @var string users date format
	*/
	public $date_format;

	/**
	* @var double users time zone
	*/
	public $timezone;

	/**
	* @var int users summer time setting. 0 if not in DST, else DST offset.
	*/
	public $dst;

	/**
	* @var string language name, for example 'en'
	*/
	public $lang_name = false;

	/**
	* @var int the language id for the specified language name
	*/
	public $lang_id = false;

	/**
	* @var string the language path phpBB can find installed languages
	*/
	public $lang_path;

	/**
	* @var string Imageset language name. If no fallback happens it is the same as {@link lang_name lang_name}.
	*/
	public $img_lang;

	/**
	* @var array Holds information about the imageset to build {@link img() images}.
	*/
	public $img_array = array();

	/**
	* @var bool Is true if the user is a logged in registered user
	*/
	public $is_registered = false;

	/**
	* @var bool Is true if the user is logged in and a search engine/bot
	*/
	public $is_bot = false;

	/**
	* @var bool Is true if user is founder
	*/
	public $is_founder = false;

	/**
	* @var bool Is true if user is anonymous/guest
	*/
	public $is_guest = true;

	/**
	* Ablility to add new option (id 7). Enabled user options is stored in $data['user_options'].
	* @var array user options defining their possibilities to view flash, images, etc. User options only supports set/unset (true/false)
	*/
	public $keyoptions = array('viewimg' => 0, 'viewflash' => 1, 'viewsmilies' => 2, 'viewsigs' => 3, 'viewavatars' => 4, 'viewcensors' => 5, 'attachsig' => 6, 'bbcode' => 8, 'smilies' => 9, 'popuppm' => 10);

	/**
	* @var array The respective true/false values for the {@link keyoptions keyoptions}.
	*/
	public $keyvalues = array();

	/**
	* Constructor to set the lang path. Calls parrent::__construct()
	*
	* @param string	$auth_method		The authentication method to use, for example 'db'
	* @param string	$lang_path			Language pack path
	* @access public
	*/
	public function __construct($auth_method, $lang_path)
	{
		parent::__construct();

		// Init auth object
		$method = basename(trim($auth_method));
		$class = 'phpbb_auth_' . $method;

		if (class_exists($class))
		{
			$this->auth = new $class();
		}

		// Set language path
		$this->lang_path = $lang_path;

		// Make sure last character is a directory separator
		if (substr($this->lang_path, -1) != '/')
		{
			$this->lang_path .= '/';
		}
	}

	/**
	* Determine and set language
	*/
	public function set_language($lang_name = '')
	{
		// Try to determine language from browser
		if (!$lang_name && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			$accept_lang_ary = explode(',', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));

			foreach ($accept_lang_ary as $accept_lang)
			{
				// Set correct format ... guess full xx_yy form
				$accept_lang = substr($accept_lang, 0, 2) . '_' . substr($accept_lang, 3, 2);

				if (file_exists($this->lang_path . basename($accept_lang)))
				{
					$lang_name = $accept_lang;
					break;
				}
				else
				{
					// No match on xx_yy so try xx
					$accept_lang = substr($accept_lang, 0, 2);
					if (file_exists($this->lang_path . basename($accept_lang)))
					{
						$lang_name = $accept_lang;
						break;
					}
				}
			}
		}

		// Still no luck?
		$lang_name = (!$lang_name && isset(phpbb::$config['default_lang'])) ? phpbb::$config['default_lang'] : basename($lang_name);

		// No appropriate language found ... so let's use the first one in the language
		// dir, this may or may not be English
		if (!$lang_name)
		{
			$dir = @opendir($this->lang_path);

			if (!$dir)
			{
				trigger_error('Unable to access the language directory', E_USER_ERROR);
			}

			while (($file = readdir($dir)) !== false)
			{
				$path = $this->lang_path . $file;

				if (!is_file($path) && !is_link($path) && file_exists($path . '/iso.txt'))
				{
					$lang_name = $file;
					break;
				}
			}
			closedir($dir);
		}

		// Update language name if changed
		if ($this->lang_name !== $lang_name)
		{
			$this->lang_name = $lang_name;
		}

		// Set data language in case we have a user where this is not set
		if (empty($this->data['user_lang']))
		{
			$this->data['user_lang'] = $this->lang_name;
		}

		// We include common language file here to not load it every time a custom language file is included
		$lang = &$this->lang;

		if ((include $this->lang_path . $this->lang_name . "/common." . PHP_EXT) === false)
		{
			die('Language file ' . $this->lang_path . $this->lang_name . "/common." . PHP_EXT . " couldn't be opened.");
		}
	}

	/**
	* Setup basic user-specific items (style, language, ...)
	*
	* @param string|array	$lang_set	Language set to setup.
	* 									Can be a string or an array of language files without a path and extension.
	* 									Format must match {@link add_lang() add_lang}.
	* @param int			$style		If not set to false this specifies the style id to use.
	* 									The page will then use the specified style id instead of the default one.
	* @access public
	*/
	public function setup($lang_set = false, $style = false)
	{
		// Check if there is a valid session
		if (empty($this->data))
		{
			$this->session_begin();

			if (phpbb::registered('acl'))
			{
				phpbb::$acl->init($this->data);
			}
		}

		if ($this->data['user_id'] != ANONYMOUS)
		{
			$this->lang_name = (file_exists($this->lang_path . $this->data['user_lang'] . "/common." . PHP_EXT)) ? $this->data['user_lang'] : basename(phpbb::$config['default_lang']);
			$this->date_format = $this->data['user_dateformat'];
			$this->timezone = $this->data['user_timezone'] * 3600;
			$this->dst = $this->data['user_dst'] * 3600;
		}
		else
		{
			$this->lang_name = basename(phpbb::$config['default_lang']);
			$this->date_format = phpbb::$config['default_dateformat'];
			$this->timezone = phpbb::$config['board_timezone'] * 3600;
			$this->dst = phpbb::$config['board_dst'] * 3600;
		}

		$this->set_language($this->lang_name);

		$this->add_lang($lang_set);
		unset($lang_set);

		if (phpbb_request::variable('style', false, false, phpbb_request::GET) && phpbb::$acl->acl_get('a_styles'))
		{
			$style = request_var('style', 0);
			$this->extra_url = array('style=' . $style);
		}
		else
		{
			// Set up style
			$style = ($style) ? $style : ((!phpbb::$config['override_user_style']) ? $this->data['user_style'] : phpbb::$config['default_style']);
		}

		$sql = 'SELECT s.style_id, t.template_path, t.template_id, t.bbcode_bitfield, c.theme_path, c.theme_name, c.theme_storedb, c.theme_id, i.imageset_path, i.imageset_id, i.imageset_name
			FROM ' . STYLES_TABLE . ' s, ' . STYLES_TEMPLATE_TABLE . ' t, ' . STYLES_THEME_TABLE . ' c, ' . STYLES_IMAGESET_TABLE . " i
			WHERE s.style_id = $style
				AND t.template_id = s.template_id
				AND c.theme_id = s.theme_id
				AND i.imageset_id = s.imageset_id";
		$result = phpbb::$db->sql_query($sql, 3600);
		$this->theme = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		// User has wrong style
		if (!$this->theme && $style == $this->data['user_style'])
		{
			$style = $this->data['user_style'] = phpbb::$config['default_style'];

			$sql = 'UPDATE ' . USERS_TABLE . "
				SET user_style = $style
				WHERE user_id = {$this->data['user_id']}";
			phpbb::$db->sql_query($sql);

			$sql = 'SELECT s.style_id, t.template_path, t.template_id, t.bbcode_bitfield, c.theme_path, c.theme_name, c.theme_storedb, c.theme_id, i.imageset_path, i.imageset_id, i.imageset_name
				FROM ' . STYLES_TABLE . ' s, ' . STYLES_TEMPLATE_TABLE . ' t, ' . STYLES_THEME_TABLE . ' c, ' . STYLES_IMAGESET_TABLE . " i
				WHERE s.style_id = $style
					AND t.template_id = s.template_id
					AND c.theme_id = s.theme_id
					AND i.imageset_id = s.imageset_id";
			$result = phpbb::$db->sql_query($sql, 3600);
			$this->theme = phpbb::$db->sql_fetchrow($result);
			phpbb::$db->sql_freeresult($result);
		}

		if (!$this->theme)
		{
			trigger_error('Could not get style data', E_USER_ERROR);
		}

		// Now parse the cfg file and cache it,
		// we are only interested in the theme configuration for now
		$parsed_items = phpbb_cache::obtain_cfg_item($this->theme, 'theme');

		$check_for = array(
			'parse_css_file'	=> (int) 0,
			'pagination_sep'	=> (string) ', ',
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

		// If the style author specified the theme needs to be cached
		// (because of the used paths and variables) than make sure it is the case.
		// For example, if the theme uses language-specific images it needs to be stored in db.
		if (!$this->theme['theme_storedb'] && $this->theme['parse_css_file'])
		{
			$this->theme['theme_storedb'] = 1;

			$stylesheet = file_get_contents(PHPBB_ROOT_PATH . "styles/{$this->theme['theme_path']}/theme/stylesheet.css");
			// Match CSS imports
			$matches = array();
			preg_match_all('/@import url\(["\'](.*)["\']\);/i', $stylesheet, $matches);

			if (sizeof($matches))
			{
				$content = '';
				foreach ($matches[0] as $idx => $match)
				{
					if ($content = @file_get_contents(PHPBB_ROOT_PATH . "styles/{$this->theme['theme_path']}/theme/" . $matches[1][$idx]))
					{
						$content = trim($content);
					}
					else
					{
						$content = '';
					}
					$stylesheet = str_replace($match, $content, $stylesheet);
				}
				unset($content);
			}

			$stylesheet = str_replace('./', 'styles/' . $this->theme['theme_path'] . '/theme/', $stylesheet);

			$sql_ary = array(
				'theme_data'	=> $stylesheet,
				'theme_mtime'	=> time(),
				'theme_storedb'	=> 1
			);

			phpbb::$db->sql_query('UPDATE ' . STYLES_THEME_TABLE . ' SET ' . phpbb::$db->sql_build_array('UPDATE', $sql_ary) . ' WHERE theme_id = ' . $this->theme['theme_id']);
			unset($sql_ary);
		}

		phpbb::$template->set_template();

		$this->img_lang = (file_exists(PHPBB_ROOT_PATH . 'styles/' . $this->theme['imageset_path'] . '/imageset/' . $this->lang_name)) ? $this->lang_name : phpbb::$config['default_lang'];

		$sql = 'SELECT image_name, image_filename, image_lang, image_height, image_width
			FROM ' . STYLES_IMAGESET_DATA_TABLE . '
			WHERE imageset_id = ' . $this->theme['imageset_id'] . "
			AND image_filename <> ''
			AND image_lang IN ('" . phpbb::$db->sql_escape($this->img_lang) . "', '')";
		$result = phpbb::$db->sql_query($sql, 3600);

		$localised_images = false;
		while ($row = phpbb::$db->sql_fetchrow($result))
		{
			if ($row['image_lang'])
			{
				$localised_images = true;
			}

			$row['image_filename'] = rawurlencode($row['image_filename']);
			$this->img_array[$row['image_name']] = $row;
		}
		phpbb::$db->sql_freeresult($result);

		// there were no localised images, try to refresh the localised imageset for the user's language
		if (!$localised_images)
		{
			// Attention: this code ignores the image definition list from acp_styles and just takes everything
			// that the config file contains
			$sql_ary = array();

			phpbb::$db->sql_transaction('begin');

			$sql = 'DELETE FROM ' . STYLES_IMAGESET_DATA_TABLE . '
				WHERE imageset_id = ' . $this->theme['imageset_id'] . '
					AND image_lang = \'' . phpbb::$db->sql_escape($this->img_lang) . '\'';
			$result = phpbb::$db->sql_query($sql);

			if (@file_exists(PHPBB_ROOT_PATH . "styles/{$this->theme['imageset_path']}/imageset/{$this->img_lang}/imageset.cfg"))
			{
				$cfg_data_imageset_data = parse_cfg_file(PHPBB_ROOT_PATH . "styles/{$this->theme['imageset_path']}/imageset/{$this->img_lang}/imageset.cfg");
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
							'imageset_id'		=> (int) $this->theme['imageset_id'],
							'image_lang'		=> (string) $this->img_lang,
						);
					}
				}
			}

			if (sizeof($sql_ary))
			{
				phpbb::$db->sql_multi_insert(STYLES_IMAGESET_DATA_TABLE, $sql_ary);
				phpbb::$db->sql_transaction('commit');
				phpbb::$acm->destroy_sql(STYLES_IMAGESET_DATA_TABLE);

				add_log('admin', 'LOG_IMAGESET_LANG_REFRESHED', $this->theme['imageset_name'], $this->img_lang);
			}
			else
			{
				phpbb::$db->sql_transaction('commit');
				add_log('admin', 'LOG_IMAGESET_LANG_MISSING', $this->theme['imageset_name'], $this->img_lang);
			}
		}

		// Call phpbb_user_session_handler() in case external application want to "bend" some variables or replace classes...
		// After calling it we continue script execution...
		// phpbb_user_session_handler();

		// If this function got called from the error handler we are finished here.
		if (defined('IN_ERROR_HANDLER'))
		{
			return;
		}

		// Disable board if the install/ directory is still present
		// For the brave development army we do not care about this, else we need to comment out this everytime we develop locally
		if (!phpbb::$base_config['debug_extra'] && !defined('ADMIN_START') && !defined('IN_INSTALL') && !defined('IN_LOGIN') && file_exists(PHPBB_ROOT_PATH . 'install'))
		{
			// Adjust the message slightly according to the permissions
			if (phpbb::$acl->acl_gets('a_', 'm_') || phpbb::$acl->acl_getf_global('m_'))
			{
				$message = 'REMOVE_INSTALL';
			}
			else
			{
				$message = (!empty(phpbb::$config['board_disable_msg'])) ? phpbb::$config['board_disable_msg'] : 'BOARD_DISABLE';
			}
			trigger_error($message);
		}

		// Is board disabled and user not an admin or moderator?
		if (phpbb::$config['board_disable'] && !defined('IN_LOGIN') && !phpbb::$acl->acl_gets('a_', 'm_') && !phpbb::$acl->acl_getf_global('m_'))
		{
			header('HTTP/1.1 503 Service Unavailable');

			$message = (!empty(phpbb::$config['board_disable_msg'])) ? phpbb::$config['board_disable_msg'] : 'BOARD_DISABLE';
			trigger_error($message);
		}

		// Is load exceeded?
		if (phpbb::$config['limit_load'] && $this->load !== false)
		{
			if ($this->load > floatval(phpbb::$config['limit_load']) && !defined('IN_LOGIN'))
			{
				// Set board disabled to true to let the admins/mods get the proper notification
				phpbb::$config['board_disable'] = '1';

				if (!phpbb::$acl->acl_gets('a_', 'm_') && !phpbb::$acl->acl_getf_global('m_'))
				{
					header('HTTP/1.1 503 Service Unavailable');
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
				if (!phpbb::$acl->acl_get('u_hideonline'))
				{
					$sql = 'UPDATE ' . SESSIONS_TABLE . '
						SET session_viewonline = 1
						WHERE session_user_id = ' . $this->data['user_id'];
					phpbb::$db->sql_query($sql);
					$this->data['session_viewonline'] = 1;
				}
			}
			else if (!$this->data['user_allow_viewonline'])
			{
				// the user wants to hide and is allowed to  -> cloaking device on.
				if (phpbb::$acl->acl_get('u_hideonline'))
				{
					$sql = 'UPDATE ' . SESSIONS_TABLE . '
						SET session_viewonline = 0
						WHERE session_user_id = ' . $this->data['user_id'];
					phpbb::$db->sql_query($sql);
					$this->data['session_viewonline'] = 0;
				}
			}
		}


		// Does the user need to change their password? If so, redirect to the
		// ucp profile reg_details page ... of course do not redirect if we're already in the ucp
		if (!defined('IN_ADMIN') && !defined('ADMIN_START') && phpbb::$config['chg_passforce'] && $this->is_registered && phpbb::$acl->acl_get('u_chgpasswd') && $this->data['user_passchg'] < time() - (phpbb::$config['chg_passforce'] * 86400))
		{
			if (strpos($this->page['query_string'], 'mode=reg_details') === false && $this->page['page_name'] != 'ucp.' . PHP_EXT)
			{
				redirect(append_sid('ucp', 'i=profile&amp;mode=reg_details'));
			}
		}

		return;
	}

	/**
	* More advanced language substitution
	*
	* Function to mimic sprintf() with the possibility of using phpBB's language system to substitute nullar/singular/plural forms.
	* Params are the language key and the parameters to be substituted.
	* This function/functionality was inspired by SHS` and Ashe.
	*
	* For example:
	* <code>
	* 	phpbb::$user->lang('NUM_POSTS_IN_QUEUE', 1);
	* </code>
	*
	* @param string	$key			The language key to use
	* @param mixed	$parameter,...	An unlimited number of parameter to apply.
	*
	* @return string	Substituted language string
	* @see sprintf()
	* @access public
	*/
	public function lang()
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

		// It is an array... now handle different nullar/singular/plural forms
		$key_found = false;

		// We now get the first number passed and will select the key based upon this number
		for ($i = 1, $num_args = sizeof($args); $i < $num_args; $i++)
		{
			if (is_int($args[$i]))
			{
				$numbers = array_keys($lang);

				foreach ($numbers as $num)
				{
					if ($num > $args[$i])
					{
						break;
					}

					$key_found = $num;
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
	* Add Language Items - use_db and use_help are assigned where needed (only use them to force inclusion)
	*
	* Examples:
	* <code>
	*	$lang_set = array('posting', 'help' => 'faq');
	*	$lang_set = array('posting', 'viewtopic', 'help' => array('bbcode', 'faq'))
	*	$lang_set = array(array('posting', 'viewtopic'), 'help' => array('bbcode', 'faq'))
	*	$lang_set = 'posting'
	*	$lang_set = array('help' => 'faq', 'db' => array('help:faq', 'posting'))
	* </code>
	*
	* @param mixed	$lang_set	specifies the language entries to include
	* @param bool	$use_db		internal variable for recursion, do not use
	* @param bool	$use_help	internal variable for recursion, do not use
	* @access public
	*/
	public function add_lang($lang_set, $use_db = false, $use_help = false)
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
					$this->add_lang($lang_file, true, $use_help);
				}
				else if ($key == 'help')
				{
					$this->add_lang($lang_file, $use_db, true);
				}
				else if (!is_array($lang_file))
				{
					$this->set_lang($this->lang, $this->help, $lang_file, $use_db, $use_help);
				}
				else
				{
					$this->add_lang($lang_file, $use_db, $use_help);
				}
			}
			unset($lang_set);
		}
		else if ($lang_set)
		{
			$this->set_lang($this->lang, $this->help, $lang_set, $use_db, $use_help);
		}
	}

	/**
	* Set language entry (called by {@link add_lang() add_lang})
	*
	* @param array	&$lang		A reference to the language array phpbb::$user->lang
	* @param array	&$help		A reference to the language help array phpbb::$user->help
	* @param string	$lang_file	Language filename
	* @param bool	$use_db		True if the database is used for obtaining the information
	* @param bool	$use_help	True if we fetch help entries instead of language entries
	* @access private
	*/
	private function set_lang(&$lang, &$help, $lang_file, $use_db = false, $use_help = false)
	{
		// Make sure the language name is set (if the user setup did not happen it is not set)
		if (!$this->lang_name)
		{
			$this->lang_name = basename(phpbb::$config['default_lang']);
		}

		// $lang == $this->lang
		// $help == $this->help
		// - add appropriate variables here, name them as they are used within the language file...
		if (!$use_db)
		{
			if ($use_help && strpos($lang_file, '/') !== false)
			{
				$language_filename = $this->lang_path . $this->lang_name . '/' . substr($lang_file, 0, stripos($lang_file, '/') + 1) . 'help_' . substr($lang_file, stripos($lang_file, '/') + 1) . '.' . PHP_EXT;
			}
			else
			{
				$language_filename = $this->lang_path . $this->lang_name . '/' . (($use_help) ? 'help_' : '') . $lang_file . '.' . PHP_EXT;
			}

			if (!file_exists($language_filename))
			{
				if ($this->lang_name == 'en')
				{
					// The user's selected language is missing the file, the board default's language is missing the file, and the file doesn't exist in /en.
					$language_filename = str_replace($this->lang_path . 'en', $this->lang_path . $this->data['user_lang'], $language_filename);
					trigger_error('Language file ' . $language_filename . ' couldn\'t be opened.', E_USER_ERROR);
				}
				else if ($this->lang_name == basename(phpbb::$config['default_lang']))
				{
					// Fall back to the English Language
					$this->lang_name = 'en';
					$this->set_lang($lang, $help, $lang_file, $use_db, $use_help);
				}
				else if (file_exists($this->lang_path . $this->data['user_lang'] . '/common.' . PHP_EXT))
				{
					// Fall back to the board default language
					$this->lang_name = basename(phpbb::$config['default_lang']);
					$this->set_lang($lang, $help, $lang_file, $use_db, $use_help);
				}

				// Reset the lang name
				$this->lang_name = (file_exists($this->lang_path . $this->data['user_lang'] . '/common.' . PHP_EXT)) ? $this->data['user_lang'] : basename(phpbb::$config['default_lang']);
				return;
			}

			if ((include $language_filename) === false)
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
	* @param int		$gmepoch	Unix timestamp to format
	* @param string		$format		Date format in date() notation.
	* 								The character | used to indicate relative dates, for example |d m Y|, h:i is translated to Today, h:i.
	* @param bool		$forcedate	Force non-relative date format.
	*
	* @staticvar int	$midnight	Midnight time offset
	* @staticvar array	$date_cache	Array to cache commonly needed structures within this function
	*
	* @return mixed translated date
	* @access public
	*/
	public function format_date($gmepoch, $format = false, $forcedate = false)
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
				'is_short'		=> strpos($format, '|'),
				'format_short'	=> substr($format, 0, strpos($format, '|')) . '||' . substr(strrchr($format, '|'), 1),
				'format_long'	=> str_replace('|', '', $format),
				'lang'			=> $this->lang['datetime'],
			);

			// Short representation of month in format? Some languages use different terms for the long and short format of May
			if ((strpos($format, '\M') === false && strpos($format, 'M') !== false) || (strpos($format, '\r') === false && strpos($format, 'r') !== false))
			{
				$date_cache[$format]['lang']['May'] = $this->lang['datetime']['May_short'];
			}
		}

		// Zone offset
		$zone_offset = $this->timezone + $this->dst;

		// Show date <= 1 hour ago as 'xx min ago'
		// A small tolerence is given for times in the future and times in the future but in the same minute are displayed as '< than a minute ago'
		if ($delta <= 3600 && ($delta >= -5 || (($now / 60) % 60) == (($gmepoch / 60) % 60)) && $date_cache[$format]['is_short'] !== false && !$forcedate && isset($this->lang['datetime']['AGO']))
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
	*
	* @return int language id
	* @access public
	*/
	public function get_iso_lang_id()
	{
		if (!empty($this->lang_id))
		{
			return $this->lang_id;
		}

		if (!$this->lang_name)
		{
			$this->lang_name = phpbb::$config['default_lang'];
		}

		$sql = 'SELECT lang_id
			FROM ' . LANG_TABLE . "
			WHERE lang_iso = '" . phpbb::$db->sql_escape($this->lang_name) . "'";
		$result = phpbb::$db->sql_query($sql);
		$this->lang_id = (int) phpbb::$db->sql_fetchfield('lang_id');
		phpbb::$db->sql_freeresult($result);

		return $this->lang_id;
	}

	/**
	* Get users profile fields
	*
	* @param int	$user_id	User id. If not specified the current users profile fields are grabbed.
	*
	* @return array	Profile fields. If the current user then they are also stored as property $profile_fields.
	* @access public
	*/
	public function get_profile_fields($user_id = false)
	{
		$user_id = ($user_id === false) ? $this->data['user_id'] : $user_id;

		if (isset($this->profile_fields) && $user_id === $this->data['user_id'])
		{
			return $this->profile_fields;
		}

		$sql = 'SELECT *
			FROM ' . PROFILE_FIELDS_DATA_TABLE . "
			WHERE user_id = $user_id";
		$result = phpbb::$db->sql_query_limit($sql, 1);
		$row = phpbb::$db->sql_fetchrow($result);
		phpbb::$db->sql_freeresult($result);

		if ($user_id === $this->data['user_id'])
		{
			$this->profile_fields = (!$row) ? array() : $row;
		}

		return $row;
	}

	/**
	* Specify/Get image from style imageset
	*
	* @param string	$img	The imageset image key name
	* @param string	$alt	An optional alternative image attribute.
	* 						If a corresponding language key exist it will be used: phpbb::$user->lang[$alt]
	* @param string	$type	The preferred type to return. Allowed types are: full_tag, src, width, height
	* @param int	$width	Set image width
	*
	* @return mixed	returns the preferred type from $type
	* @access public
	*/
	public function img($img, $alt = '', $type = 'full_tag', $width = false)
	{
		static $imgs;

		$img_data = &$imgs[$img];

		if (empty($img_data))
		{
			if (!isset($this->img_array[$img]))
			{
				// Do not fill the image to let designers decide what to do if the image is empty
				$img_data = '';
				return $img_data;
			}

			$img_data['src'] = PHPBB_ROOT_PATH . 'styles/' . $this->theme['imageset_path'] . '/imageset/' . ($this->img_array[$img]['image_lang'] ? $this->img_array[$img]['image_lang'] .'/' : '') . $this->img_array[$img]['image_filename'];
			$img_data['width'] = $this->img_array[$img]['image_width'];
			$img_data['height'] = $this->img_array[$img]['image_height'];
		}

		$alt = (!empty($this->lang[$alt])) ? $this->lang[$alt] : $alt;

		switch ($type)
		{
			case 'src':
				return $img_data['src'];
			break;

			case 'width':
				return ($width === false) ? $img_data['width'] : $width;
			break;

			case 'height':
				return $img_data['height'];
			break;

			default:
				$use_width = ($width === false) ? $img_data['width'] : $width;

				return '<img src="' . $img_data['src'] . '"' . (($use_width) ? ' width="' . $use_width . '"' : '') . (($img_data['height']) ? ' height="' . $img_data['height'] . '"' : '') . ' alt="' . $alt . '" title="' . $alt . '" />';
			break;
		}
	}

	/**
	* Get option bit field from user options.
	*
	* @param string	$key	The option key from {@link $keyoptions keyoptions}
	* @param int	$data	Optional user options bitfield.
	* 						If not specified then {@link $data $data['user_options']} is used.
	*
	* @return bool	Corresponding option value returned. Is the option enabled or disabled.
	* @access public
	*/
	public function optionget($key, $data = false)
	{
		if ($data !== false)
		{
			return $data & 1 << $this->keyoptions[$key];
		}

		if (!isset($this->keyvalues[$key]))
		{
			$this->keyvalues[$key] = $this->data['user_options'] & 1 << $this->keyoptions[$key];
		}

		return $this->keyvalues[$key];
	}

	/**
	* Set option bit field for user options.
	*
	* @param string	$key	The option key from {@link $keyoptions keyoptions}
	* @param bool	$value	True to enable the option, false to disable it
	* @param int	$data	Optional user options bitfield.
	* 						If not specified then {@link $data $data['user_options']} is used.
	*
	* @return bool	The new user options bitfield is returned if $data is specified.
	* 				Else: false is returned if user options not changed, true if changed.
	* @access public
	*/
	public function optionset($key, $value, $data = false)
	{
		$var = ($data) ? $data : $this->data['user_options'];

		if ($value && !($var & 1 << $this->keyoptions[$key]))
		{
			$var += 1 << $this->keyoptions[$key];
		}
		else if (!$value && ($var & 1 << $this->keyoptions[$key]))
		{
			$var -= 1 << $this->keyoptions[$key];
		}
		else
		{
			return ($data) ? $var : false;
		}

		if (!$data)
		{
			$this->data['user_options'] = $this->keyvalues[$key] = $var;
			return true;
		}

		return $var;
	}

	/**
	* User login. Log the user in.
	*
	* @param string	$username	The specified user name
	* @param string	$password	The specified password
	* @param bool	$autologin	Enable/disable persistent login
	* @param bool	$viewonline	If false then the user will be logged in as hidden
	* @param bool	$admin		If true the user requests an admin login
	*
	* @return array	Login result array. This array returns results to the login script to show errors, notices, confirmations.
	* @access public
	*/
	public function login($username, $password, $autologin = false, $viewonline = 1, $admin = 0)
	{
		if ($this->auth === false || !method_exists($this->auth, 'login'))
		{
			trigger_error('Authentication method not found', E_USER_ERROR);
		}

		$login = $this->auth->login($username, $password);

		// If the auth module wants us to create an empty profile do so and then treat the status as LOGIN_SUCCESS
		if ($login['status'] == LOGIN_SUCCESS_CREATE_PROFILE)
		{
			phpbb::$api->user->add($login['user_row'], (isset($login['cp_data'])) ? $login['cp_data'] : false);

			$sql = 'SELECT user_id, username, user_password, user_passchg, user_email, user_type
				FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . phpbb::$db->sql_escape(utf8_clean_string($username)) . "'";
			$result = phpbb::$db->sql_query($sql);
			$row = phpbb::$db->sql_fetchrow($result);
			phpbb::$db->sql_freeresult($result);

			if (!$row)
			{
				return array(
					'status'		=> LOGIN_ERROR_EXTERNAL_AUTH,
					'error_msg'		=> 'AUTH_NO_PROFILE_CREATED',
					'user_row'		=> array('user_id' => ANONYMOUS),
				);
			}

			$login = array(
				'status'	=> LOGIN_SUCCESS,
				'error_msg'	=> false,
				'user_row'	=> $row,
			);
		}

		// If login succeeded, we will log the user in... else we pass the login array through...
		if ($login['status'] == LOGIN_SUCCESS)
		{
			// We create a new session. The session creation makes sure the old session is killed if there was one.
			$result = $this->session_create($login['user_row']['user_id'], $admin, $autologin, $viewonline);

			// Successful session creation
			if ($result === true)
			{
				return array(
					'status'		=> LOGIN_SUCCESS,
					'error_msg'		=> false,
					'user_row'		=> $login['user_row'],
				);
			}

			return array(
				'status'		=> LOGIN_BREAK,
				'error_msg'		=> $result,
				'user_row'		=> $login['user_row'],
			);
		}

		return $login;
	}
}

?>