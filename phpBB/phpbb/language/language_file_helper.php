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
			->name('composer.json')
			->depth('== 1')
			->in($this->phpbb_root_path . 'language');

		$available_languages = array();
		foreach ($finder as $file)
		{
			$json = $file->getContents();
			$data = json_decode($json, true);

			$available_languages[] = $this->get_language_data_from_json($data);
		}

		return $available_languages;
	}

	/**
	 * Collect some data from the composer.json file
	 *
	 * @param string $path
	 * @return array
	 */
	public function get_language_data_from_composer_file($path)
	{
		$json_data = file_get_contents($path);
		return $this->get_language_data_from_json(json_decode($json_data, true));
	}

	/**
	 * Collect some data from the composer.json data
	 *
	 * @param array $data
	 * @return array
	 */
	protected function get_language_data_from_json(array $data)
	{
		if (!isset($data['extra']['language-iso']) || !isset($data['extra']['english-name']) || !isset($data['extra']['local-name']))
		{
			throw new \DomainException('INVALID_LANGUAGE_PACK');
		}

		$authors = array();
		if (isset($data['authors']))
		{
			foreach ($data['authors'] as $author)
			{
				if (isset($author['name']) && $author['name'] !== '')
				{
					$authors[] = $author['name'];
				}
			}
		}

		return array(
			'iso' => $data['extra']['language-iso'],

			'name' => $data['extra']['english-name'],
			'local_name' => $data['extra']['local-name'],
			'author' => implode(', ', $authors),
		);
	}
}
