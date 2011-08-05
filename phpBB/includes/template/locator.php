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
* Template locator. Maintains mapping from template handles to source paths.
*
* Template locator is aware of template inheritance, and can return actual
* filesystem paths (i.e., the "primary" template or the "parent" template)
* depending on what files exist.
*
* @package phpBB3
*/
class phpbb_template_locator
{
	/**
	* @var string Path to directory that templates are stored in.
	*/
	private $root = '';

	/**
	* @var string Path to parent/fallback template directory.
	*/
	private $inherit_root = '';

	/**
	* @var array Map from handles to source template file paths.
	* Normally it only contains paths for handles that are used
	* (or are likely to be used) by the page being rendered and not
	* all templates that exist on the filesystem.
	*/
	private $files = array();

	/**
	* @var array Map from handles to source template file names.
	* Covers the same data as $files property but maps to basenames
	* instead of paths.
	*/
	private $filenames = array();

	/**
	* @var array Map from handles to parent/fallback source template
	* file paths. Covers the same data as $files.
	*/
	private $files_inherit = array();

	/**
	* Set custom template location (able to use directory outside of phpBB).
	*
	* Note: Templates are still compiled to phpBB's cache directory.
	*
	* @param string $template_path Path to template directory
	* @param string|bool $fallback_template_path Path to fallback template, or false to disable fallback
	*/
	public function set_custom_template($template_path, $fallback_template_path = false)
	{
		// Make sure $template_path has no ending slash
		if (substr($template_path, -1) == '/')
		{
			$template_path = substr($template_path, 0, -1);
		}

		$this->root = $template_path;

		if ($fallback_template_path !== false)
		{
			if (substr($fallback_template_path, -1) == '/')
			{
				$fallback_template_path = substr($fallback_template_path, 0, -1);
			}

			$this->inherit_root = $fallback_template_path;
		}
	}

	/**
	* Sets the template filenames for handles. $filename_array
	* should be a hash of handle => filename pairs.
	* @param array $filname_array Should be a hash of handle => filename pairs.
	*/
	public function set_filenames(array $filename_array)
	{
		foreach ($filename_array as $handle => $filename)
		{
			if (empty($filename))
			{
				trigger_error("template locator: set_filenames: Empty filename specified for $handle", E_USER_ERROR);
			}

			$this->filename[$handle] = $filename;
			$this->files[$handle] = $this->root . '/' . $filename;

			if ($this->inherit_root)
			{
				$this->files_inherit[$handle] = $this->inherit_root . '/' . $filename;
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
			trigger_error("template->_tpl_load(): No file specified for handle $handle", E_USER_ERROR);
		}
		return $this->filename[$handle];
	}

	/**
	* Determines the source file path for a template handle without
	* regard for template inheritance.
	*
	* This function returns the path in "primary" template directory
	* corresponding to the given template handle. That path may or
	* may not actually exist on the filesystem. Because this function
	* does not perform stat calls to determine whether the path it
	* returns actually exists, it is faster than get_source_file_for_handle.
	*
	* Use get_source_file_for_handle to obtain the actual path that is
	* guaranteed to exist (which might come from the parent/fallback
	* template directory if template inheritance is used).
	*
	* This function will trigger an error if the handle was never
	* associated with a template file via set_filenames.
	*
	* @param $handle string Template handle
	* @return string Path to source file path in primary template directory
	*/
	public function get_virtual_source_file_for_handle($handle)
	{
		// If we don't have a file assigned to this handle, die.
		if (!isset($this->files[$handle]))
		{
			trigger_error("template locator: No file specified for handle $handle", E_USER_ERROR);
		}

		$source_file = $this->files[$handle];
		return $source_file;
	}

	/**
	* Determines the source file path for a template handle, accounting
	* for template inheritance and verifying that the path exists.
	*
	* This function returns the actual path that may be compiled for
	* the specified template handle. It will trigger an error if
	* the template handle was never associated with a template path
	* via set_filenames or if the template file does not exist on the
	* filesystem.
	*
	* Use get_virtual_source_file_for_handle to just resolve a template
	* handle to a path without any filesystem or inheritance checks.
	*
	* @param string $handle Template handle (i.e. "friendly" template name)
	* @return string Source file path
	*/
	public function get_source_file_for_handle($handle)
	{
		// If we don't have a file assigned to this handle, die.
		if (!isset($this->files[$handle]))
		{
			trigger_error("template locator: No file specified for handle $handle", E_USER_ERROR);
		}

		$source_file = $this->files[$handle];

		// Try and open template for reading
		if (!file_exists($source_file))
		{
			if (isset($this->files_inherit[$handle]) && $this->files_inherit[$handle])
			{
				$parent_source_file = $this->files_inherit[$handle];
				if (!file_exists($parent_source_file))
				{
					trigger_error("template locator: Neither $source_file nor $parent_source_file exist", E_USER_ERROR);
				}
				$source_file = $parent_source_file;
			}
			else
			{
				trigger_error("template locator: File $source_file does not exist", E_USER_ERROR);
			}
		}
		return $source_file;
	}
}
