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

use \phpbb\language\exception\language_file_not_found;

/**
 * Language file loader
 */
class language_file_loader
{
	/**
	 * @var string	Path to phpBB's root
	 */
	protected $phpbb_root_path;

	/**
	 * @var string	Extension of PHP files
	 */
	protected $php_ext;

	/**
	 * @var \phpbb\extension\manager	Extension manager
	 */
	protected $extension_manager;

	/**
	 * Constructor
	 *
	 * @param string	$phpbb_root_path	Path to phpBB's root
	 * @param string	$php_ext			Extension of PHP files
	 */
	public function __construct($phpbb_root_path, $php_ext)
	{
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;

		$this->extension_manager = null;
	}

	/**
	 * Extension manager setter
	 *
	 * @param \phpbb\extension\manager	$extension_manager	Extension manager
	 */
	public function set_extension_manager(\phpbb\extension\manager $extension_manager)
	{
		$this->extension_manager = $extension_manager;
	}

	/**
	 * Loads language array for the given component
	 *
	 * @param string		$component	Name of the language component
	 * @param string|array	$locale		ISO code of the language to load, or array of ISO codes if you want to
	 * 									specify additional language fallback steps
	 * @param array			$lang		Array reference containing language strings
	 */
	public function load($component, $locale, &$lang)
	{
		$locale = (array) $locale;

		// Determine path to language directory
		$path = $this->phpbb_root_path . 'language/';

		$this->load_file($path, $component, $locale, $lang);
	}

	/**
	 * Loads language array for the given extension component
	 *
	 * @param string		$extension	Name of the extension
	 * @param string		$component	Name of the language component
	 * @param string|array	$locale		ISO code of the language to load, or array of ISO codes if you want to
	 * 									specify additional language fallback steps
	 * @param array			$lang		Array reference containing language strings
	 */
	public function load_extension($extension, $component, $locale, &$lang)
	{
		// Check if extension manager was loaded
		if ($this->extension_manager === null)
		{
			// If not, let's return
			return;
		}

		$locale = (array) $locale;

		// Determine path to language directory
		$path = $this->extension_manager->get_extension_path($extension, true) . 'language/';

		$this->load_file($path, $component, $locale, $lang);
	}

	/**
	 * Prepares language file loading
	 *
	 * @param string	$path		Path to search for file in
	 * @param string	$component	Name of the language component
	 * @param array		$locale		Array containing language fallback options
	 * @param array		$lang		Array reference of language strings
	 */
	protected function load_file($path, $component, $locale, &$lang)
	{
		// This is BC stuff and not the best idea as it makes language fallback
		// implementation quite hard like below.
		if (strpos($this->phpbb_root_path . $component, $path) === 0)
		{
			// Filter out the path
			$path_diff = str_replace($path, '', dirname($this->phpbb_root_path . $component));
			$language_file = basename($component, '.' . $this->php_ext);
			$component = '';

			// This step is needed to resolve language/en/subdir style $component
			// $path already points to the language base directory so we need to eliminate
			// the first directory from the path (that should be the language directory)
			$path_diff_parts = explode('/', $path_diff);

			if (count($path_diff_parts) > 1)
			{
				array_shift($path_diff_parts);
				$component = implode('/', $path_diff_parts) . '/';
			}

			$component .= $language_file;
		}

		// Determine filename
		$filename = $component . '.' . $this->php_ext;

		// Determine path to file
		$file_path = $this->get_language_file_path($path, $filename, $locale);

		// Load language array
		$this->load_language_file($file_path, $lang);
	}

	/**
	 * This function implements language fallback logic
	 *
	 * @param string	$path		Path to language directory
	 * @param string	$filename	Filename to load language strings from
	 *
	 * @return string	Relative path to language file
	 *
	 * @throws language_file_not_found	When the path to the file cannot be resolved
	 */
	protected function get_language_file_path($path, $filename, $locales)
	{
		$language_file_path = $filename;

		// Language fallback logic
		foreach ($locales as $locale)
		{
			$language_file_path = $path . $locale . '/' . $filename;

			// If we are in install, try to use the updated version, when available
			if (defined('IN_INSTALL'))
			{
				$install_language_path = str_replace('language/', 'install/update/new/language/', $language_file_path);
				if (file_exists($install_language_path))
				{
					return $install_language_path;
				}
			}

			if (file_exists($language_file_path))
			{
				return $language_file_path;
			}
		}

		// The language file is not exist
		throw new language_file_not_found('Language file ' . $language_file_path . ' couldn\'t be opened.');
	}

	/**
	 * Loads language file
	 *
	 * @param string	$path	Path to language file to load
	 * @param array		$lang	Reference of the array of language strings
	 */
	protected function load_language_file($path, &$lang)
	{
		// Do not suppress error if in DEBUG mode
		if (defined('DEBUG'))
		{
			include $path;
		}
		else
		{
			@include $path;
		}
	}
}
