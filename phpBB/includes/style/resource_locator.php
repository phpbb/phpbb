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
	private $template_path;

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
	* Constructor.
	*
	* Sets default template path to template/.
	*/
	public function __construct()
	{
		$this->set_default_template_path();
	}

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
	* Sets the location of templates directory within style directories.
	*
	* The location must be a relative path, with a trailing slash.
	* Typically it is one directory level deep, e.g. "template/".
	*
	* @param string $template_path Relative path to templates directory within style directories
	* @return null
	*/
	public function set_template_path($template_path)
	{
		$this->template_path = $template_path;
	}

	/**
	* Sets the location of templates directory within style directories
	* to the default, which is "template/".
	*
	* @return null
	*/
	public function set_default_template_path()
	{
		$this->template_path = 'template/';
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

	/**
	* Obtains filesystem path for a template file.
	*
	* The simplest use is specifying a single template file as a string
	* in the first argument. This template file should be a basename
	* of a template file in the selected style, or its parent styles
	* if template inheritance is being utilized.
	*
	* Note: "selected style" is whatever style the style resource locator
	* is configured for.
	*
	* The return value then will be a path, relative to the current
	* directory or absolute, to the template file in the selected style
	* or its closest parent.
	*
	* If the selected style does not have the template file being searched,
	* (and if inheritance is involved, none of the parents have it either),
	* false will be returned.
	*
	* Specifying true for $return_default will cause the function to
	* return the first path which was checked for existence in the event
	* that the template file was not found, instead of false.
	* This is the path in the selected style itself, not any of its
	* parents.
	*
	* $files can be given an array of templates instead of a single
	* template. When given an array, the function will try to resolve
	* each template in the array to a path, and will return the first
	* path that exists, or false if none exist.
	*
	* If $files is an array and template inheritance is involved, first
	* each of the files will be checked in the selected style, then each
	* of the files will be checked in the immediate parent, and so on.
	*
	* If $return_full_path is false, then instead of returning a usable
	* path (when the template is found) only the template's basename
	* will be returned. This can be used to check which of the templates
	* specified in $files exists. Naturally more than one template must
	* be given in $files.
	*
	* This function works identically to get_first_file_location except
	* it operates on a list of templates, not files. Practically speaking,
	* the templates given in the first argument first are prepended with
	* the template path (property in this class), then given to
	* get_first_file_location for the rest of the processing.
	*
	* Templates given to this function can be relative paths for templates
	* located in subdirectories of the template directories. The paths
	* should be relative to the templates directory (template/ by default).
	*
	* @param string or array $files List of templates to locate. If there is only
	*				one template, $files can be a string to make code easier to read.
	* @param bool $return_default Determines what to return if template does not
	*				exist. If true, function will return location where template is
	*				supposed to be. If false, function will return false.
	* @param bool $return_full_path If true, function will return full path
	*				to template. If false, function will return template file name.
	*				This parameter can be used to check which one of set of template
	*				files is available.
	* @return string or boolean Source template path if template exists or $return_default is
	*				true. False if template does not exist and $return_default is false
	*/
	public function get_first_template_location($templates, $return_default = false, $return_full_path = true)
	{
		// add template path prefix
		$files = array();
		if (is_string($templates))
		{
			$files[] = $this->template_path . $templates;
		}
		else
		{
			foreach ($templates as $template)
			{
				$files[] = $this->template_path . $template;
			}
		}

		return $this->get_first_file_location($files, $return_default, $return_full_path);
	}
}
