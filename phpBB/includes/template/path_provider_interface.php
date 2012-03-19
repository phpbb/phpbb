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
* Provides a template locator with paths
*
* Finds installed template paths and makes them available to the locator.
*
* @package phpBB3
*/
interface phpbb_template_path_provider_interface extends Traversable
{
	/**
	* Defines a prefix to use for template paths in extensions
	*
	* @param string $ext_dir_prefix The prefix including trailing slash
	* @return null
	*/
	public function set_ext_dir_prefix($ext_dir_prefix);

	/**
	* Overwrites the current template names and paths
	*
	* @param array $templates An associative map from template names to paths.
	*                         The first element is the main template.
	*                         If the path is false, it will be generated from
	*                         the supplied name.
	* @param string $style_root_path The root directory for styles identified
	*                         by name only.
	* @return null
	*/
	public function set_templates(array $templates, $style_root_path);

	/**
	* Retrieves the path to the main template passed into set_templates()
	*
	* @return string Main template path
	*/
	public function get_main_template_path();
}
