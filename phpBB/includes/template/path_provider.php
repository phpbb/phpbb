<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
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
* Provides a template locator with paths
*
* Finds installed template paths and makes them available to the locator.
*
* @package phpBB3
*/
class phpbb_template_path_provider extends phpbb_extension_provider
{
	protected $main_template_name = '';
	protected $templates = array();
	protected $ext_dir_prefix = '';

	/**
	* Constructor stores extension manager
	*
	* @param phpbb_extension_manager $extension_manager phpBB extension manager
	*/
	public function __construct(phpbb_extension_manager $extension_manager)
	{
		// no super call to avoid find() call
		$this->extension_manager = $extension_manager;
	}

	/**
	* Defines a prefix to use for template paths in extensions
	*
	* @param string $ext_dir_prefix The prefix including trailing slash
	* @return null
	*/
	public function set_ext_dir_prefix($ext_dir_prefix)
	{
		$this->ext_dir_prefix = $ext_dir_prefix;
	}

	/**
	* Finds template paths using the extension manager
	*
	* Finds paths with the same name (e.g. styles/prosilver/template/) in all
	* active extensions. Then appends the actual template paths based in the
	* current working directory.
	*
	* @return array     List of template paths
	*/
	public function find()
	{
		$finder = $this->extension_manager->get_finder();

		$directories = array();

		foreach ($this->templates as $name => $path)
		{
			if ($path && !phpbb_is_absolute($path))
			{
				$directories = array_merge($directories, $finder
					->directory('/' . $this->ext_dir_prefix . $path)
					->get_directories()
				);
			}
		}

		foreach ($this->templates as $name => $path)
		{
			$directories[] = $path;
		}

		return $directories;
	}

	/**
	* Overwrites the current template names and paths
	*
	* @param array $templates An associative map from template names to paths.
	*                         The first element is the main template.
	*                         If the path is false, it will be generated from
	*                         the supplied name.
	* @return null
	*/
	public function set_templates(array $templates)
	{
		$this->templates = array();

		foreach ($templates as $name => $path)
		{
			if (!$path)
			{
				$path = $this->template_root_for_style($name);
			}

			$this->templates[$name] = $path;
		}

		reset($this->templates);
		$this->main_template_path = current($this->templates);

		$this->items = $this->find();
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
	* Converts a style name to relative (to board root or extension) path to
	* the style's template files.
	*
	* @param $style_name string Style name
	* @return string Path to style template files
	*/
	private function template_root_for_style($style_name)
	{
		return 'styles/' . $style_name . '/template';
	}
}
