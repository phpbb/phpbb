<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
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
* Provides a style resource locator with paths
*
* Finds installed style paths and makes them available to the resource locator.
*
* @package phpBB3
*/
interface phpbb_style_path_provider_interface extends Traversable
{
	/**
	* Defines a prefix to use for style paths in extensions
	*
	* @param string $ext_dir_prefix The prefix including trailing slash
	* @return null
	*/
	public function set_ext_dir_prefix($ext_dir_prefix);

	/**
	* Overwrites the current style paths
	*
	* @param array $styles An array of style paths. The first element is the main style.
	* @return null
	*/
	public function set_styles(array $styles);
}
