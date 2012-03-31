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
class phpbb_style_resource_locator
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
	* Sets the template filenames for handles. $filename_array
	* should be a hash of handle => filename pairs.
	*
	* @param array $filname_array Should be a hash of handle => filename pairs.
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
	* Determines the filename for a template handle.
	*
	* The filename comes from array used in a set_filenames call,
	* which should have been performed prior to invoking this function.
	* Return value is a file basename (without path).
	*
	* @param $handle string Template handle
	* @return string Filename corresponding to the template handle
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
	* Determines the source file path for a template handle without
	* regard for styles tree.
	*
	* This function returns the path in "primary" style directory
	* corresponding to the given template handle. That path may or
	* may not actually exist on the filesystem. Because this function
	* does not perform stat calls to determine whether the path it
	* returns actually exists, it is faster than get_source_file_for_handle.
	*
	* Use get_source_file_for_handle to obtain the actual path that is
	* guaranteed to exist (which might come from the parent style 
	* directory if primary style has parent styles).
	*
	* This function will trigger an error if the handle was never
	* associated with a template file via set_filenames.
	*
	* @param $handle string Template handle
	* @return string Path to source file path in primary style directory
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
	* Determines the source file path for a template handle, accounting
	* for styles tree and verifying that the path exists.
	*
	* This function returns the actual path that may be compiled for
	* the specified template handle. It will trigger an error if
	* the template handle was never associated with a template path
	* via set_filenames or if the template file does not exist on the
	* filesystem.
	*
	* Use get_virtual_source_file_for_handle to just resolve a template
	* handle to a path without any filesystem or styles tree checks.
	*
	* @param string $handle Template handle (i.e. "friendly" template name)
	* @return string Source file path
	*/
	public function get_source_file_for_handle($handle)
	{
		// If we don't have a file assigned to this handle, die.
		if (!isset($this->files['style'][0][$handle]))
		{
			trigger_error("style resource locator: No file specified for handle $handle", E_USER_ERROR);
		}

		// locate a source file that exists
		$source_file = $this->files['style'][0][$handle];
		$tried = $source_file;
		foreach ($this->roots as $root_key => $root_paths)
		{
			foreach ($root_paths as $root_index => $root)
			{
				$source_file = $this->files[$root_key][$root_index][$handle];
				if (file_exists($source_file))
				{
					return $source_file;
				}
				$tried .= ', ' . $source_file;
			}
		}

		// search failed
		trigger_error("style resource locator: File for handle $handle does not exist. Could not find: $tried", E_USER_ERROR);
	}
}
