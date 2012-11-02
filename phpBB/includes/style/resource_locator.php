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
* Style resource locator. 
* Maintains mapping from template handles to source template file paths.
* Locates style files: resources (such as .js and .css files) and templates.
*
* Style resource locator is aware of styles tree, and can return actual
* filesystem paths (i.e., the "child" style or the "parent" styles)
* depending on what files exist.
*
* Root paths stored in locator are paths to style directories. Templates are
* stored in subdirectory that $template_path points to.
*
* @package phpBB3
*/
class phpbb_style_resource_locator implements phpbb_template_locator
{
	/**
	* Paths to style directories.
	* @var array
	*/
	private $roots = array();

	/**
	* Location of templates directory within style directories.
	* Must have trailing slash. Empty if templates are stored in root
	* style directory, such as admin control panel templates.
	* @var string
	*/
	public $template_path = 'template/';

	/**
	* Map from root index to handles to source template file paths.
	* Normally it only contains paths for handles that are used
	* (or are likely to be used) by the page being rendered and not
	* all templates that exist on the filesystem.
	* @var array
	*/
	private $files = array();

	/**
	* Map from handles to source template file names.
	* Covers the same data as $files property but maps to basenames
	* instead of paths.
	* @var array
	*/
	private $filenames = array();

	/**
	* Sets the list of style paths
	*
	* These paths will be searched for style files in the provided order.
	* Paths may be outside of phpBB, but templates loaded from these paths
	* will still be cached.
	*
	* @param array $style_paths An array of paths to style directories
	* @return null
	*/
	public function set_paths($style_paths)
	{
		$this->roots = array();
		$this->files = array();
		$this->filenames = array();

		foreach ($style_paths as $key => $paths)
		{
			foreach ($paths as $path)
			{
				// Make sure $path has no ending slash
				if (substr($path, -1) === '/')
				{
					$path = substr($path, 0, -1);
				}
				$this->roots[$key][] = $path;
			}
		}
	}

	/**
	* {@inheritDoc}
	*/
	public function set_filenames(array $filename_array)
	{
		foreach ($filename_array as $handle => $filename)
		{
			if (empty($filename))
			{
				trigger_error("style resource locator: set_filenames: Empty filename specified for $handle", E_USER_ERROR);
			}

			$this->filename[$handle] = $filename;

			foreach ($this->roots as $root_key => $root_paths)
			{
				foreach ($root_paths as $root_index => $root)
				{
					$this->files[$root_key][$root_index][$handle] = $root . '/' . $this->template_path . $filename;
				}
			}
		}
	}

	/**
	* {@inheritDoc}
	*/
	public function get_filename_for_handle($handle)
	{
		if (!isset($this->filename[$handle]))
		{
			trigger_error("style resource locator: get_filename_for_handle: No file specified for handle $handle", E_USER_ERROR);
		}
		return $this->filename[$handle];
	}

	/**
	* {@inheritDoc}
	*/
	public function get_virtual_source_file_for_handle($handle)
	{
		// If we don't have a file assigned to this handle, die.
		if (!isset($this->files['style'][0][$handle]))
		{
			trigger_error("style resource locator: No file specified for handle $handle", E_USER_ERROR);
		}

		$source_file = $this->files['style'][0][$handle];
		return $source_file;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_source_file_for_handle($handle, $find_all = false)
	{
		// If we don't have a file assigned to this handle, die.
		if (!isset($this->files['style'][0][$handle]))
		{
			trigger_error("style resource locator: No file specified for handle $handle", E_USER_ERROR);
		}

		// locate a source file that exists
		$source_file = $this->files['style'][0][$handle];
		$tried = $source_file;
		$found = false;
		$found_all = array();
		foreach ($this->roots as $root_key => $root_paths)
		{
			foreach ($root_paths as $root_index => $root)
			{
				$source_file = $this->files[$root_key][$root_index][$handle];
				$tried .= ', ' . $source_file;
				if (file_exists($source_file))
				{
					$found = true;
					break;
				}
			}
			if ($found)
			{
				if ($find_all)
				{
					$found_all[] = $source_file;
					$found = false;
				}
				else
				{
					break;
				}
			}
		}

		// search failed
		if (!$found && !$find_all)
		{
			trigger_error("style resource locator: File for handle $handle does not exist. Could not find: $tried", E_USER_ERROR);
		}

		return ($find_all) ? $found_all : $source_file;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_first_file_location($files, $return_default = false, $return_full_path = true)
	{
		// set default value
		$default_result = false;

		// check all available paths
		foreach ($this->roots as $root_paths)
		{
			foreach ($root_paths as $path)
			{
				// check all files
				foreach ($files as $filename)
				{
					$source_file = $path . '/' . $filename;
					if (file_exists($source_file))
					{
						return ($return_full_path) ? $source_file : $filename;
					}

					// assign first file as result if $return_default is true
					if ($return_default && $default_result === false)
					{
						$default_result = $source_file;
					}
				}
			}
		}

		// search failed
		return $default_result;
	}
}
