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
class phpbb_template_path_provider implements IteratorAggregate, phpbb_template_path_provider_interface
{
	protected $main_template_name = '';
	protected $paths = array();

	/**
	* Ignores the extension dir prefix
	*
	* @param string $ext_dir_prefix The prefix including trailing slash
	* @return null
	*/
	public function set_ext_dir_prefix($ext_dir_prefix)
	{
	}

	/**
	* Overwrites the current template names and paths
	*
	* The first element of the passed templates map, is considered the main
	* template and can be retrieved through get_main_template_path().
	*
	* @param array $templates An associative map from template names to paths.
	*                         The first element is the main template.
	*                         If the path is false, it will be generated from
	*                         the supplied name.
	* @return null
	*/
	public function set_templates(array $templates)
	{
		$this->paths = $templates;
		$this->main_template_path = $this->paths[0];
	}

	/**
	* Retrieves the path to the main template passed into set_templates()
	*
	* @return string Main template path
	*/
	public function get_main_template_path()
	{
		return $this->main_template_path;
	}

	/**
	* Retrieve an iterator over all template paths
	*
	* @return ArrayIterator An iterator for the array of template paths
	*/
	public function getIterator()
	{
		return new ArrayIterator($this->paths);
	}
}
