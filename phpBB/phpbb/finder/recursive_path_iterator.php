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

declare(strict_types=1);

namespace phpbb\finder;

class recursive_path_iterator extends \RecursiveIteratorIterator
{
	/**
	 * Constructor
	 *
	 * @param string $path Path to iterate over
	 * @param int $mode Iterator mode
	 * @param int $flags Flags
	 */
	public function __construct(string $path, int $mode = self::LEAVES_ONLY, int $flags = \FilesystemIterator::SKIP_DOTS)
	{
		parent::__construct(
			new recursive_dot_prefix_filter_iterator(new \RecursiveDirectoryIterator($path, $flags)),
			\RecursiveIteratorIterator::SELF_FIRST
		);
	}

	/**
	 * Get inner iterator
	 *
	 * @return recursive_dot_prefix_filter_iterator
	 */
	public function getInnerIterator(): \RecursiveIterator
	{
		$inner_iterator = parent::getInnerIterator();

		assert($inner_iterator instanceof recursive_dot_prefix_filter_iterator);
		return $inner_iterator;
	}
}
