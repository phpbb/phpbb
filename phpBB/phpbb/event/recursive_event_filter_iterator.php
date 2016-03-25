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

namespace phpbb\event;

/**
* This filter ignores directories and files starting with a dot.
* It also skips some directories that do not contain events anyway,
* such as e.g. files/, store/ and vendor/
*/
class recursive_event_filter_iterator extends \RecursiveFilterIterator
{
	protected $root_path;

	/**
	* Construct
	*
	* @param \RecursiveIterator	$iterator
	* @param string				$root_path
	*/
	public function __construct(\RecursiveIterator $iterator, $root_path)
	{
		$this->root_path = str_replace(DIRECTORY_SEPARATOR, '/', $root_path);
		parent::__construct($iterator);
	}

	/**
	* Return the inner iterator's children contained in a recursive_event_filter_iterator
	*
	* @return recursive_event_filter_iterator
	*/
	public function getChildren()
	{
		return new self($this->getInnerIterator()->getChildren(), $this->root_path);
	}

	/**
	* {@inheritDoc}
	*/
	public function accept()
	{
		$relative_path = str_replace(DIRECTORY_SEPARATOR, '/', $this->current());
		$filename = $this->current()->getFilename();

		return (substr($relative_path, -4) === '.php' || $this->current()->isDir())
			&& $filename[0] !== '.'
			&& strpos($relative_path, $this->root_path . 'cache/') !== 0
			&& strpos($relative_path, $this->root_path . 'develop/') !== 0
			&& strpos($relative_path, $this->root_path . 'docs/') !== 0
			&& strpos($relative_path, $this->root_path . 'ext/') !== 0
			&& strpos($relative_path, $this->root_path . 'files/') !== 0
			&& strpos($relative_path, $this->root_path . 'includes/utf/') !== 0
			&& strpos($relative_path, $this->root_path . 'language/') !== 0
			&& strpos($relative_path, $this->root_path . 'phpbb/db/migration/data/') !== 0
			&& strpos($relative_path, $this->root_path . 'phpbb/event/') !== 0
			&& strpos($relative_path, $this->root_path . 'store/') !== 0
			&& strpos($relative_path, $this->root_path . 'tests/') !== 0
			&& strpos($relative_path, $this->root_path . 'vendor/') !== 0
		;
	}
}
