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

namespace phpbb\language;

use phpbb\language\exception\invalid_plural_rule_exception;

/**
 * Wrapper class for loading translations
 */
class language
{
	/**
	 * Global fallback language
	 *
	 * ISO code of the language to fallback to when the specified language entries
	 * cannot be found.
	 *
	 * @var string
	 */
	const FALLBACK_LANGUAGE = 'en';

	/**
	 * @var array	List of common language files
	 */
	protected $common_language_files;

	/**
	 * @var bool
	 */
	protected $common_language_files_loaded;

	/**
	 * @var string	ISO code of the default board language
	 */
	protected $default_language;

	/**
	 * @var string	ISO code of the User's language
	 */
	protected $user_language;

	/**
	 * @var array	Language fallback array (the order is important)
	 */
	protected $language_fallback;

	/**
	 * @var array	Array of language variables
	 */
	protected $lang;

	/**
	 * @var array	Loaded language sets
	 */
	protected $loaded_language_sets;

	/**
	 * @var \phpbb\language\language_file_loader Language file loader
	 */
	protected $loader;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language_file_loader	$loader			Language file loader
	 * @param array|null							$common_modules	Array of common language modules to load (optional)
	 */
	public function __construct(language_file_loader $loader, $common_modules = null)
	{
		$this->loader = $loader;

		// Set up default information
		$this->user_language		= false;
		$this->default_language		= false;
		$this->lang					= array();
		$this->loaded_language_sets	= array(
			'core'	=> array(),
			'ext'	=> array(),
		);

		// Common language files
		if (is_array($common_modules))
		{
			$this->common_language_files = $common_modules;
		}
		else
		{
			$this->common_language_files = array(
				'common',
			);
		}

		$this->common_language_files_loaded = false;

		$this->language_fallback = array(self::FALLBACK_LANGUAGE);
	}

	/**
	 * Function to set user's language to display.
	 *
	 * @param string	$user_lang_iso		ISO code of the User's language
	 * @param bool		$reload				Whether or not to reload language files
	 */
	public function set_user_language($user_lang_iso, $reload = false)
	{
		$this->user_language = $user_lang_iso;

		$this->set_fallback_array($reload);
	}

	/**
	 * Function to set the board's default language to display.
	 *
	 * @param string	$default_lang_iso	ISO code of the board's default language
	 * @param bool		$reload				Whether or not to reload language files
	 */
	public function set_default_language($default_lang_iso, $reload = false)
	{
		$this->default_language = $default_lang_iso;

		$this->set_fallback_array($reload);
	}

	/**
	 * Returns language array
	 *
	 * Note: This function is needed for the BC purposes, until \phpbb\user::lang[] is
	 *       not removed.
	 *
	 * @return array	Array of loaded language strings
	 */
	public function get_lang_array()
	{
		// Load common language files if they not loaded yet
		if (!$this->common_language_files_loaded)
		{
			$this->load_common_language_files();
		}

		return $this->lang;
	}

	/**
	 * Add Language Items
	 *
	 * Examples:
	 * <code>
	 * $component = array('posting');
	 * $component = array('posting', 'viewtopic')
	 * $component = 'posting'
	 * </code>
	 *
	 * @param string|array	$component		The name of the language component to load
	 * @param string|null	$extension_name	Name of the extension to load component from, or null for core file
	 */
	public function add_lang($component, $extension_name = null)
	{
		// Load common language files if they not loaded yet
		// This needs to be here to correctly merge language arrays
		if (!$this->common_language_files_loaded)
		{
			$this->load_common_language_files();
		}

		if (!is_array($component))
		{
			if (!is_null($extension_name))
			{
				$this->load_extension($extension_name, $component);
			}
			else
			{
				$this->load_core_file($component);
			}
		}
		else
		{
			foreach ($component as $lang_file)
			{
				$this->add_lang($lang_file, $extension_name);
			}
		}
	}

	/**
	 * @param $key array|string		The language key we want to know more about. Can be string or array.
	 *
	 * @return bool		Returns whether the language key is set.
	 */
	public function is_set($key)
	{
		// Load common language files if they not loaded yet
		if (!$this->common_language_files_loaded)
		{
			$this->load_common_language_files();
		}

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

		return isset($lang);
	}

	/**
	 * Advanced language substitution
	 *
	 * Function to mimic sprintf() with the possibility of using phpBB's language system to substitute nullar/singular/plural forms.
	 * Params are the language key and the parameters to be substituted.
	 * This function/functionality is inspired by SHS` and Ashe.
	 *
	 * Example call: <samp>$user->lang('NUM_POSTS_IN_QUEUE', 1);</samp>
	 *
	 * If the first parameter is an array, the elements are used as keys and subkeys to get the language entry:
	 * Example: <samp>$user->lang(array('datetime', 'AGO'), 1)</samp> uses $user->lang['datetime']['AGO'] as language entry.
	 *
	 * @return string	Return localized string or the language key if the translation is not available
	 */
	public function lang()
	{
		$args = func_get_args();
		$key = array_shift($args);

		return $this->lang_array($key, $args);
	}

	/**
	 * Returns the raw value associated to a language key or the language key no translation is available.
	 * No parameter substitution is performed, can be a string or an array.
	 *
	 * @param string|array	$key	Language key
	 *
	 * @return array|string
	 */
	public function lang_raw($key)
	{
		// Load common language files if they not loaded yet
		if (!$this->common_language_files_loaded)
		{
			$this->load_common_language_files();
		}

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

		return $lang;
	}

	/**
	 * Act like lang() but takes a key and an array of parameters instead of using variadic
	 *
	 * @param string|array	$key	Language key
	 * @param array			$args	Parameters
	 *
	 * @return string
	 */
	public function lang_array($key, array $args = [])
	{
		$lang = $this->lang_raw($key);

		if ($lang === $key)
		{
			return $key;
		}

		// If the language entry is a string, we simply mimic sprintf() behaviour
		if (is_string($lang))
		{
			if (count($args) === 0)
			{
				return $lang;
			}

			// Replace key with language entry and simply pass along...
			return vsprintf($lang, $args);
		}
		else if (count($lang) == 0)
		{
			// If the language entry is an empty array, we just return the language key
			return $key;
		}

		// It is an array... now handle different nullar/singular/plural forms
		$key_found = false;

		// We now get the first number passed and will select the key based upon this number
		for ($i = 0, $num_args = count($args); $i < $num_args; $i++)
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
		return vsprintf($lang[$key_found], $args);
	}

	/**
	 * Loads common language files
	 */
	protected function load_common_language_files()
	{
		if (!$this->common_language_files_loaded)
		{
			foreach ($this->common_language_files as $lang_file)
			{
				$this->load_core_file($lang_file);
			}

			$this->common_language_files_loaded = true;
		}
	}

	/**
	 * Determine which plural form we should use.
	 *
	 * For some languages this is not as simple as for English.
	 *
	 * @param int|float		$number		The number we want to get the plural case for. Float numbers are floored.
	 * @param int|bool		$force_rule	False to use the plural rule of the language package
	 *									or an integer to force a certain plural rule
	 *
	 * @return int	The plural-case we need to use for the number plural-rule combination
	 *
	 * @throws \phpbb\language\exception\invalid_plural_rule_exception	When $force_rule has an invalid value
	 */
	public function get_plural_form($number, $force_rule = false)
	{
		$number			= (int) $number;
		$plural_rule	= ($force_rule !== false) ? $force_rule : ((isset($this->lang['PLURAL_RULE'])) ? $this->lang['PLURAL_RULE'] : 1);

		if ($plural_rule > 15 || $plural_rule < 0)
		{
			throw new invalid_plural_rule_exception('INVALID_PLURAL_RULE', array(
				'plural_rule' => $plural_rule,
			));
		}

		/**
		 * The following plural rules are based on a list published by the Mozilla Developer Network
		 * https://developer.mozilla.org/en/Localization_and_Plurals
		 */
		switch ($plural_rule)
		{
			case 0:
				/**
				 * Families: Asian (Chinese, Japanese, Korean, Vietnamese), Persian, Turkic/Altaic (Turkish), Thai, Lao
				 * 1 - everything: 0, 1, 2, ...
				 */
				return 1;

			case 1:
				/**
				 * Families: Germanic (Danish, Dutch, English, Faroese, Frisian, German, Norwegian, Swedish), Finno-Ugric (Estonian, Finnish, Hungarian), Language isolate (Basque), Latin/Greek (Greek), Semitic (Hebrew), Romanic (Italian, Portuguese, Spanish, Catalan)
				 * 1 - 1
				 * 2 - everything else: 0, 2, 3, ...
				 */
				return ($number === 1) ? 1 : 2;

			case 2:
				/**
				 * Families: Romanic (French, Brazilian Portuguese)
				 * 1 - 0, 1
				 * 2 - everything else: 2, 3, ...
				 */
				return (($number === 0) || ($number === 1)) ? 1 : 2;

			case 3:
				/**
				 * Families: Baltic (Latvian)
				 * 1 - 0
				 * 2 - ends in 1, not 11: 1, 21, ... 101, 121, ...
				 * 3 - everything else: 2, 3, ... 10, 11, 12, ... 20, 22, ...
				 */
				return ($number === 0) ? 1 : ((($number % 10 === 1) && ($number % 100 != 11)) ? 2 : 3);

			case 4:
				/**
				 * Families: Celtic (Scottish Gaelic)
				 * 1 - is 1 or 11: 1, 11
				 * 2 - is 2 or 12: 2, 12
				 * 3 - others between 3 and 19: 3, 4, ... 10, 13, ... 18, 19
				 * 4 - everything else: 0, 20, 21, ...
				 */
				return ($number === 1 || $number === 11) ? 1 : (($number === 2 || $number === 12) ? 2 : (($number >= 3 && $number <= 19) ? 3 : 4));

			case 5:
				/**
				 * Families: Romanic (Romanian)
				 * 1 - 1
				 * 2 - is 0 or ends in 01-19: 0, 2, 3, ... 19, 101, 102, ... 119, 201, ...
				 * 3 - everything else: 20, 21, ...
				 */
				return ($number === 1) ? 1 : ((($number === 0) || (($number % 100 > 0) && ($number % 100 < 20))) ? 2 : 3);

			case 6:
				/**
				 * Families: Baltic (Lithuanian)
				 * 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, ...
				 * 2 - ends in 0 or ends in 10-20: 0, 10, 11, 12, ... 19, 20, 30, 40, ...
				 * 3 - everything else: 2, 3, ... 8, 9, 22, 23, ... 29, 32, 33, ...
				 */
				return (($number % 10 === 1) && ($number % 100 != 11)) ? 1 : ((($number % 10 < 2) || (($number % 100 >= 10) && ($number % 100 < 20))) ? 2 : 3);

			case 7:
				/**
				 * Families: Slavic (Croatian, Serbian, Russian, Ukrainian)
				 * 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, ...
				 * 2 - ends in 2-4, not 12-14: 2, 3, 4, 22, 23, 24, 32, ...
				 * 3 - everything else: 0, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 25, 26, ...
				 */
				return (($number % 10 === 1) && ($number % 100 != 11)) ? 1 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 10) || ($number % 100 >= 20))) ? 2 : 3);

			case 8:
				/**
				 * Families: Slavic (Slovak, Czech)
				 * 1 - 1
				 * 2 - 2, 3, 4
				 * 3 - everything else: 0, 5, 6, 7, ...
				 */
				return ($number === 1) ? 1 : ((($number >= 2) && ($number <= 4)) ? 2 : 3);

			case 9:
				/**
				 * Families: Slavic (Polish)
				 * 1 - 1
				 * 2 - ends in 2-4, not 12-14: 2, 3, 4, 22, 23, 24, 32, ... 104, 122, ...
				 * 3 - everything else: 0, 5, 6, ... 11, 12, 13, 14, 15, ... 20, 21, 25, ...
				 */
				return ($number === 1) ? 1 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 12) || ($number % 100 > 14))) ? 2 : 3);

			case 10:
				/**
				 * Families: Slavic (Slovenian, Sorbian)
				 * 1 - ends in 01: 1, 101, 201, ...
				 * 2 - ends in 02: 2, 102, 202, ...
				 * 3 - ends in 03-04: 3, 4, 103, 104, 203, 204, ...
				 * 4 - everything else: 0, 5, 6, 7, 8, 9, 10, 11, ...
				 */
				return ($number % 100 === 1) ? 1 : (($number % 100 === 2) ? 2 : ((($number % 100 === 3) || ($number % 100 === 4)) ? 3 : 4));

			case 11:
				/**
				 * Families: Celtic (Irish Gaeilge)
				 * 1 - 1
				 * 2 - 2
				 * 3 - is 3-6: 3, 4, 5, 6
				 * 4 - is 7-10: 7, 8, 9, 10
				 * 5 - everything else: 0, 11, 12, ...
				 */
				return ($number === 1) ? 1 : (($number === 2) ? 2 : (($number >= 3 && $number <= 6) ? 3 : (($number >= 7 && $number <= 10) ? 4 : 5)));

			case 12:
				/**
				 * Families: Semitic (Arabic)
				 * 1 - 1
				 * 2 - 2
				 * 3 - ends in 03-10: 3, 4, ... 10, 103, 104, ... 110, 203, 204, ...
				 * 4 - ends in 11-99: 11, ... 99, 111, 112, ...
				 * 5 - everything else: 100, 101, 102, 200, 201, 202, ...
				 * 6 - 0
				 */
				return ($number === 1) ? 1 : (($number === 2) ? 2 : ((($number % 100 >= 3) && ($number % 100 <= 10)) ? 3 : ((($number % 100 >= 11) && ($number % 100 <= 99)) ? 4 : (($number != 0) ? 5 : 6))));

			case 13:
				/**
				 * Families: Semitic (Maltese)
				 * 1 - 1
				 * 2 - is 0 or ends in 01-10: 0, 2, 3, ... 9, 10, 101, 102, ...
				 * 3 - ends in 11-19: 11, 12, ... 18, 19, 111, 112, ...
				 * 4 - everything else: 20, 21, ...
				 */
				return ($number === 1) ? 1 : ((($number === 0) || (($number % 100 > 1) && ($number % 100 < 11))) ? 2 : ((($number % 100 > 10) && ($number % 100 < 20)) ? 3 : 4));

			case 14:
				/**
				 * Families: Slavic (Macedonian)
				 * 1 - ends in 1: 1, 11, 21, ...
				 * 2 - ends in 2: 2, 12, 22, ...
				 * 3 - everything else: 0, 3, 4, ... 10, 13, 14, ... 20, 23, ...
				 */
				return ($number % 10 === 1) ? 1 : (($number % 10 === 2) ? 2 : 3);

			case 15:
				/**
				 * Families: Icelandic
				 * 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, 131, ...
				 * 2 - everything else: 0, 2, 3, ... 10, 11, 12, ... 20, 22, ...
				 */
				return (($number % 10 === 1) && ($number % 100 != 11)) ? 1 : 2;
		}
	}

	/**
	 * Returns the ISO code of the used language
	 *
	 * @return string	The ISO code of the currently used language
	 */
	public function get_used_language()
	{
		return $this->language_fallback[0];
	}

	/**
	 * Returns language fallback data
	 *
	 * @param bool	$reload	Whether or not to reload language files
	 *
	 * @return array
	 */
	protected function set_fallback_array($reload = false)
	{
		$fallback_array = array();

		if ($this->user_language)
		{
			$fallback_array[] = $this->user_language;
		}

		if ($this->default_language)
		{
			$fallback_array[] = $this->default_language;
		}

		$fallback_array[] = self::FALLBACK_LANGUAGE;

		$this->language_fallback = $fallback_array;

		if ($reload)
		{
			$this->reload_language_files();
		}
	}

	/**
	 * Load core language file
	 *
	 * @param string	$component	Name of the component to load
	 */
	protected function load_core_file($component)
	{
		// Check if the component is already loaded
		if (isset($this->loaded_language_sets['core'][$component]))
		{
			return;
		}

		$this->loader->load($component, $this->language_fallback, $this->lang);
		$this->loaded_language_sets['core'][$component] = true;
	}

	/**
	 * Load extension language file
	 *
	 * @param string	$extension_name	Name of the extension to load language from
	 * @param string	$component		Name of the component to load
	 */
	protected function load_extension($extension_name, $component)
	{
		// Check if the component is already loaded
		if (isset($this->loaded_language_sets['ext'][$extension_name][$component]))
		{
			return;
		}

		$this->loader->load_extension($extension_name, $component, $this->language_fallback, $this->lang);
		$this->loaded_language_sets['ext'][$extension_name][$component] = true;
	}

	/**
	 * Reload language files
	 */
	protected function reload_language_files()
	{
		$loaded_files = $this->loaded_language_sets;
		$this->loaded_language_sets	= array(
			'core'	=> array(),
			'ext'	=> array(),
		);

		// Reload core files
		foreach ($loaded_files['core'] as $component => $value)
		{
			$this->load_core_file($component);
		}

		// Reload extension files
		foreach ($loaded_files['ext'] as $ext_name => $ext_info)
		{
			foreach ($ext_info as $ext_component => $value)
			{
				$this->load_extension($ext_name, $ext_component);
			}
		}
	}
}
