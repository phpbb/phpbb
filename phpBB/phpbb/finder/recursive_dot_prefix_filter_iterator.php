<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\finder;

/**
* Class recursive_dot_prefix_filter_iterator
*
* This filter ignores directories starting with a dot.
* When searching for php classes and template files of extensions
* we don't need to look inside these directories.
*/
class recursive_dot_prefix_filter_iterator extends \RecursiveFilterIterator
{
	/**
	 * Check whether the current element of the iterator is acceptable
	 *
	 * @return bool
	 */
	public function accept(): bool
	{
		$filename = $this->current()->getFilename();
		return $filename[0] !== '.' || !$this->current()->isDir();
	}

	/**
	 * Get sub path
	 *
	 * @return string
	 */
	public function getSubPath(): string
	{
		$directory_iterator = $this->getInnerIterator();
		assert($directory_iterator instanceof \RecursiveDirectoryIterator);
		return $directory_iterator->getSubPath();
	}

	/**
	 * Get sub path and name
	 *
	 * @return string
	 */
	public function getSubPathname(): string
	{
		$directory_iterator = $this->getInnerIterator();
		assert($directory_iterator instanceof \RecursiveDirectoryIterator);
		return $directory_iterator->getSubPathname();
	}
}
