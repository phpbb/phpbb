<?php
/**
*
* @package event
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\event;

/**
* Class recursive_event_filter_iterator
*
* This filter ignores directories and files starting with a dot.
* It also skips some directories that do not contain events anyway,
* such as e.g. files/, store/ and vendor/
*
* @package phpbb\event
*/
class recursive_event_filter_iterator extends \RecursiveFilterIterator
{
	/**
	* {@inheritDoc}
	*/
	public function accept()
	{
		$relative_path = str_replace(DIRECTORY_SEPARATOR, '/', $this->current());
		$filename = $this->current()->getFilename();

		return (substr($relative_path, -4) === '.php' || $this->current()->isDir())
			&& $filename[0] !== '.'
			&& strpos($relative_path, 'phpBB/cache/') !== 0
			&& strpos($relative_path, 'phpBB/develop/') !== 0
			&& strpos($relative_path, 'phpBB/ext/') !== 0
			&& strpos($relative_path, 'phpBB/files/') !== 0
			&& strpos($relative_path, 'phpBB/includes/utf/') !== 0
			&& strpos($relative_path, 'phpBB/language/') !== 0
			&& strpos($relative_path, 'phpBB/phpbb/db/migration/data/') !== 0
			&& strpos($relative_path, 'phpBB/phpbb/event/') !== 0
			&& strpos($relative_path, 'phpBB/store/') !== 0
			&& strpos($relative_path, 'phpBB/vendor/') !== 0
		;
	}
}
