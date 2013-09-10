<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\template;

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
interface locator
{
	/**
	* Sets the template filenames for handles. $filename_array
	* should be a hash of handle => filename pairs.
	*
	* @param array $filename_array Should be a hash of handle => filename pairs.
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
	* Obtains a complete filesystem path for a file in a style.
	*
	* This function traverses the style tree (selected style and
	* its parents in order, if inheritance is being used) and finds
	* the first file on the filesystem matching specified relative path,
	* or the first of the specified paths if more than one path is given.
	*
	* This function can be used to determine filesystem path of any
	* file under any style, with the consequence being that complete
	* relative to the style directory path must be provided as an argument.
	*
	* In particular, this function can be used to locate templates
	* and javascript files.
	*
	* For locating templates get_first_template_location should be used
	* as it prepends the configured template path to the template basename.
	*
	* Note: "selected style" is whatever style the style resource locator
	* is configured for.
	*
	* The return value then will be a path, relative to the current
	* directory or absolute, to the first existing file in the selected
	* style or its closest parent.
	*
	* If the selected style does not have the file being searched,
	* (and if inheritance is involved, none of the parents have it either),
	* false will be returned.
	*
	* Multiple files can be specified, in which case the first file in
	* the list that can be found on the filesystem is returned.
	*
	* If multiple files are specified and inheritance is involved,
	* first each of the specified files is checked in the selected style,
	* then each of the specified files is checked in the immediate parent,
	* etc.
	*
	* Specifying true for $return_default will cause the function to
	* return the first path which was checked for existence in the event
	* that the template file was not found, instead of false.
	* This is always a path in the selected style itself, not any of its
	* parents.
	*
	* If $return_full_path is false, then instead of returning a usable
	* path (when the file is found) the file's path relative to the style
	* directory will be returned. This is the same path as was given to
	* the function as a parameter. This can be used to check which of the
	* files specified in $files exists. Naturally this requires passing
	* more than one file in $files.
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
