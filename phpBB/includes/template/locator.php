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
* Resource locator interface.
*
* Objects implementing this interface maintain mapping from template handles
* to source template file paths and locate templates.
*
* Locates style files.
*
* Resource locator is aware of styles tree, and can return actual
* filesystem paths (i.e., the "child" style or the "parent" styles)
* depending on what files exist.
*
* Root paths stored in locator are paths to style directories. Templates are
* stored in subdirectory that $template_path points to.
*
* @package phpBB3
*/
interface phpbb_template_locator
{
	/**
	* Sets the template filenames for handles. $filename_array
	* should be a hash of handle => filename pairs.
	*
	* @param array $filname_array Should be a hash of handle => filename pairs.
	*/
	public function set_filenames(array $filename_array);

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
	public function get_filename_for_handle($handle);

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
	public function get_virtual_source_file_for_handle($handle);

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
	* @param bool $find_all If true, each root path will be checked and function
	*				will return array of files instead of string and will not
	*				trigger a error if template does not exist
	* @return string Source file path
	*/
	public function get_source_file_for_handle($handle, $find_all = false);

	/**
	* Locates source file path, accounting for styles tree and verifying that
	* the path exists.
	*
	* Unlike previous functions, this function works without template handle
	* and it can search for more than one file. If more than one file name is
	* specified, it will return location of file that it finds first.
	*
	* @param array $files List of files to locate.
	* @param bool $return_default Determines what to return if file does not
	*				exist. If true, function will return location where file is
	*				supposed to be. If false, function will return false.
	* @param bool $return_full_path If true, function will return full path
	*				to file. If false, function will return file name. This
	*				parameter can be used to check which one of set of files
	*				is available.
	* @return string or boolean Source file path if file exists or $return_default is
	*				true. False if file does not exist and $return_default is false
	*/
	public function get_first_file_location($files, $return_default = false, $return_full_path = true);
}
