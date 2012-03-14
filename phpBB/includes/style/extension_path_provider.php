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
* Provides a template locator with core template paths and extension template paths
*
* Finds installed template paths and makes them available to the locator.
*
* @package phpBB3
*/
class phpbb_template_extension_path_provider extends phpbb_extension_provider implements phpbb_template_path_provider_interface
{
	/**
	* Optional prefix for template paths searched within extensions.
	*
	* Empty by default. Relative to the extension directory. As an example, it
	* could be adm/ for admin templates.
	*
	* @var string
	*/
	protected $ext_dir_prefix = '';

	/**
	* A provider of paths to be searched for templates
	* @var phpbb_template_path_provider
	*/
	protected $base_path_provider;

	/**
	* Constructor stores extension manager
	*
	* @param phpbb_extension_manager $extension_manager phpBB extension manager
	* @param phpbb_template_path_provider $base_path_provider A simple path provider
	*            to provide paths to be located in extensions
	*/
	public function __construct(phpbb_extension_manager $extension_manager, phpbb_template_path_provider $base_path_provider)
	{
		parent::__construct($extension_manager);
		$this->base_path_provider = $base_path_provider;
	}

	/**
	* Sets a prefix for template paths searched within extensions.
	*
	* The prefix is inserted between the extension's path e.g. ext/foo/ and
	* the looked up template path, e.g. styles/bar/template/some.html. So it
	* should not have a leading slash, but should have a trailing slash.
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
	* Locates a path (e.g. styles/prosilver/template/) in all active extensions.
	* Then appends the core template paths based in the current working
	* directory.
	*
	* @return array     List of template paths
	*/
	public function find()
	{
		$directories = array();

		$finder = $this->extension_manager->get_finder();
		foreach ($this->base_path_provider as $path)
		{
			if ($path && !phpbb_is_absolute($path))
			{
				$directories = array_merge($directories, $finder
					->directory('/' . $this->ext_dir_prefix . $path)
					->get_directories()
				);
			}
		}

		foreach ($this->base_path_provider as $path)
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
		$this->base_path_provider->set_templates($templates, $style_root_path);
		$this->items = null;
	}

	/**
	* Retrieves the path to the main template passed into set_templates()
	*
	* @return string Main template path
	*/
	public function get_main_template_path()
	{
		return $this->base_path_provider->get_main_template_path();
	}
}
