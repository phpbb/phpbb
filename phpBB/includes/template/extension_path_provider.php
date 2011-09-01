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
* Provides a template locator with template paths and extension template paths
*
* Finds installed template paths and makes them available to the locator.
*
* @package phpBB3
*/
class phpbb_template_extension_path_provider extends phpbb_extension_provider implements phpbb_template_path_provider_interface
{
	protected $ext_dir_prefix = '';
	protected $base_paths;

	/**
	* Constructor stores extension manager
	*
	* @param phpbb_extension_manager $extension_manager phpBB extension manager
	* @param phpbb_template_path_provider $base_paths A simple path provider
	*            to provide paths to be located in extensions
	*/
	public function __construct(phpbb_extension_manager $extension_manager, phpbb_template_path_provider $base_paths)
	{
		parent::__construct($extension_manager);
		$this->base_paths = $base_paths;
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
		$directories = array();

		$finder = $this->extension_manager->get_finder();
		foreach ($this->base_paths as $path)
		{
			if ($path && !phpbb_is_absolute($path))
			{
				$directories = array_merge($directories, $finder
					->directory('/' . $this->ext_dir_prefix . $path)
					->get_directories()
				);
			}
		}

		foreach ($this->base_paths as $path)
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
	* @param string $style_root_path The root directory for styles identified
	*                         by name only.
	* @return null
	*/
	public function set_templates(array $templates, $style_root_path)
	{
		$this->base_paths->set_templates($templates, $style_root_path);
		$this->items = null;
	}

	/**
	* Retrieves the path to the main template passed into set_templates()
	*
	* @return string Main template path
	*/
	public function get_main_template_path()
	{
		return $this->base_paths->get_main_template_path();
	}
}
