<?php
/**
*
* @package extension
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\extension;

/**
* Class recursive_filter_iterator
*
* This Filter ignores .svn and .git directories.
* When searching for php classes and template files of extensions
* we don't need to look inside these directories.
*
* @package phpbb\extension
*/
class recursive_filter_iterator extends \RecursiveFilterIterator
{
	public static $ignore_folders = array(
		'.svn',
		'.git',
	);

	public function accept()
	{
		return !in_array($this->current()->getFilename(), self::$ignore_folders);
	}
}
