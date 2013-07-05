<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group, sections (c) 2001 ispi of Lincoln Inc
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
* Base Style class.
* @package phpBB3
*/
class phpbb_style
{
	/**
	* Template class.
	* Handles everything related to templates.
	* @var phpbb_template
	*/
	private $template;

	/**
	* phpBB root path
	* @var string
	*/
	private $phpbb_root_path;

	/**
	* PHP file extension
	* @var string
	*/
	private $php_ext;

	/**
	* phpBB config instance
	* @var phpbb_config
	*/
	private $config;

	/**
	* Current user
	* @var phpbb_user
	*/
	private $user;

	/**
	* Style path provider
	* @var phpbb_style_path_provider
	*/
	private $provider;

	/**
	* Constructor.
	*
	* @param string $phpbb_root_path phpBB root path
	* @param user $user current user
	* @param phpbb_style_path_provider $provider style path provider
	* @param phpbb_template $template template
	*/
	public function __construct($phpbb_root_path, $php_ext, $config, $user, phpbb_style_path_provider_interface $provider, phpbb_template $template)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->config = $config;
		$this->user = $user;
		$this->provider = $provider;
		$this->template = $template;
	}

	/**
	* Get the style tree of the style preferred by the current user
	*
	* @return array Style tree, most specific first
	*/
	public function get_user_style()
	{
		$style_list = array(
			$this->user->style['style_path'],
		);

		if ($this->user->style['style_parent_id'])
		{
			$style_list = array_merge($style_list, array_reverse(explode('/', $this->user->style['style_parent_tree'])));
		}

		return $style_list;
	}

	/**
	* Set style location based on (current) user's chosen style.
	*
	* @param array $style_directories The directories to add style paths for
	* 	E.g. array('ext/foo/bar/styles', 'styles')
	* 	Default: array('styles') (phpBB's style directory)
	* @return bool true
	*/
	public function set_style($style_directories = array('styles'))
	{
		$this->names = $this->get_user_style();

		$paths = array();
		foreach ($style_directories as $directory)
		{
			foreach ($this->names as $name)
			{
				$path = $this->get_style_path($name, $directory);

				if (is_dir($path))
				{
					$paths[] = $path;
				}
			}
		}

		$this->provider->set_styles($paths);

		foreach ($paths as &$path)
		{
			$path .= '/template/';
		}

		$this->template->set_style_names($this->names, $paths, ($style_directories === array('styles')));

		return true;
	}

	/**
	* Set custom style location (able to use directory outside of phpBB).
	*
	* Note: Templates are still compiled to phpBB's cache directory.
	*
	* @param string $name Name of style, used for cache prefix. Examples: "admin", "prosilver"
	* @param array or string $paths Array of style paths, relative to current root directory
	* @param array $names Array of names of templates in inheritance tree order, used by extensions. If empty, $name will be used.
	* @param string $template_path Path to templates, relative to style directory. False if path should be set to default (templates/).
	* @return bool true
	*/
	public function set_custom_style($name, $paths, $names = array(), $template_path = false)
	{
		if (is_string($paths))
		{
			$paths = array($paths);
		}

		if (empty($names))
		{
			$names = array($name);
		}
		$this->names = $names;

		$this->provider->set_styles($paths);

		foreach ($paths as &$path)
		{
			$path .= '/' . (($template_path !== false) ? $template_path : 'template/');
		}

		$this->template->set_style_names($names, $paths);

		return true;
	}

	/**
	* Get location of style directory for specific style_path
	*
	* @param string $path Style path, such as "prosilver"
	* @param string $style_base_directory The base directory the style is in
	* 	E.g. 'styles', 'ext/foo/bar/styles'
	* 	Default: 'styles'
	* @return string Path to style directory, relative to current path
	*/
	public function get_style_path($path, $style_base_directory = 'styles')
	{
		return $this->phpbb_root_path . trim($style_base_directory, '/') . '/' . $path;
	}
}
