<?php
/**
*
* @package extension
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb;

/**
* Class recursive_dot_prefix_filter_iterator
*
* This filter ignores directories starting with a dot.
* When searching for php classes and template files of extensions
* we don't need to look inside these directories.
*
* @package phpbb
*/
class recursive_dot_prefix_filter_iterator extends \RecursiveFilterIterator
{
	public function accept()
	{
		return !$this->current()->isDir() || substr($this->current()->getFilename(), 0, 1) !== '.';
	}
}
