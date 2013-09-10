<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\style;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Provides a style resource locator with core style paths and extension style paths
*
* Finds installed style paths and makes them available to the resource locator.
*
* @package phpBB3
*/
class extension_path_provider extends \phpbb\extension\provider implements \phpbb\style\path_provider_interface
{
	/**
	* Optional prefix for style paths searched within extensions.
	*
	* Empty by default. Relative to the extension directory. As an example, it
	* could be adm/ for admin style.
	*
	* @var string
	*/
	protected $ext_dir_prefix = '';

	/**
	* A provider of paths to be searched for styles
	* @var \phpbb\style\path_provider
	*/
	protected $base_path_provider;

	/** @var string */
	protected $phpbb_root_path;

	/**
	* Constructor stores extension manager
	*
	* @param \phpbb\extension\manager $extension_manager phpBB extension manager
	* @param \phpbb\style\path_provider $base_path_provider A simple path provider
	*            to provide paths to be located in extensions
	* @param string		$phpbb_root_path	phpBB root path
	*/
	public function __construct(\phpbb\extension\manager $extension_manager, \phpbb\style\path_provider $base_path_provider, $phpbb_root_path)
	{
		parent::__construct($extension_manager);
		$this->base_path_provider = $base_path_provider;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	* Sets a prefix for style paths searched within extensions.
	*
	* The prefix is inserted between the extension's path e.g. ext/foo/ and
	* the looked up style path, e.g. styles/bar/. So it should not have a
	* leading slash, but should have a trailing slash.
	*
	* @param string $ext_dir_prefix The prefix including trailing slash
	* @return null
	*/
	public function set_ext_dir_prefix($ext_dir_prefix)
	{
		$this->ext_dir_prefix = $ext_dir_prefix;
	}

	/**
	* Finds style paths using the extension manager
	*
	* Locates a path (e.g. styles/prosilver/) in all active extensions.
	* Then appends the core style paths based in the current working
	* directory.
	*
	* @return array     List of style paths
	*/
	public function find()
	{
		$directories = array();

		$finder = $this->extension_manager->get_finder();
		foreach ($this->base_path_provider as $key => $paths)
		{
			if ($key == 'style')
			{
				foreach ($paths as $path)
				{
					$directories['style'][] = $path;
					if ($path && !phpbb_is_absolute($path))
					{
						// Remove phpBB root path from the style path,
						// so the finder is able to find extension styles,
						// when the root path is not ./
						if (strpos($path, $this->phpbb_root_path) === 0)
						{
							$path = substr($path, strlen($this->phpbb_root_path));
						}

						$result = $finder->directory('/' . $this->ext_dir_prefix . $path)
							->get_directories(true, false, true);
						foreach ($result as $ext => $ext_path)
						{
							// Make sure $ext_path has no ending slash
							if (substr($ext_path, -1) === '/')
							{
								$ext_path = substr($ext_path, 0, -1);
							}
							$directories[$ext][] = $ext_path;
						}
					}
				}
			}
		}

		return $directories;
	}

	/**
	* Overwrites the current style paths
	*
	* @param array $styles An array of style paths. The first element is the main style.
	* @return null
	*/
	public function set_styles(array $styles)
	{
		$this->base_path_provider->set_styles($styles);
		$this->items = null;
	}
}
