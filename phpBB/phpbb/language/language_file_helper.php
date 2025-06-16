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

namespace phpbb\language;

use Symfony\Component\Finder\Finder;

/**
 * Helper class for language file related functions
 */
class language_file_helper
{
	/**
	 * @var string	Path to phpBB's root
	 */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param string	$phpbb_root_path	Path to phpBB's root
	 */
	public function __construct($phpbb_root_path)
	{
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	 * Returns available languages
	 *
	 * @return array
	 */
	public function get_available_languages()
	{
		// Find available language packages
		$finder = new Finder();
		$finder->files()
			->name('iso.txt')
			->depth('== 1')
			->followLinks()
			->in($this->phpbb_root_path . 'language');

		$available_languages = array();
		foreach ($finder as $file)
		{
			$path = $file->getRelativePath();
			$info = explode("\n", $file->getContents());

			$available_languages[] = array(
				// Get the name of the directory containing iso.txt
				'iso' => $path,

				// Recover data from file
				'name' => trim($info[0]),
				'local_name' => trim($info[1]),
				'author' => trim($info[2])
			);
		}

		usort($available_languages, [$this, 'sort_by_local_name']);

		return $available_languages;
	}

	/**
	 * Sorts the languages by their name instead of iso code
	 *
	 * @param mixed $a First language data
	 * @param mixed $b Second language data
	 * @return int
	 */
	private static function sort_by_local_name($a, $b): int
	{
		return $a['local_name'] <=> $b['local_name'];
	}
}
