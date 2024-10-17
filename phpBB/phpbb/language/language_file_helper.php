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

use DomainException;
use phpbb\json\sanitizer as json_sanitizer;
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
	 * @param string $phpbb_root_path 		Path to phpBB's root
	 *
	 */
	public function __construct(string $phpbb_root_path)
	{
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	 * Returns available languages
	 *
	 * @return array
	 *
	 * @throws DomainException When one of the languages in language directory
	 *						could not be loaded or have invalid composer.json data
	 */
	public function get_available_languages() : array
	{
		// Find available language packages
		$finder = new Finder();
		$finder->files()
			->name('composer.json')
			->depth('== 1')
			->followLinks()
			->in($this->phpbb_root_path . 'language');

		$available_languages = array();
		foreach ($finder as $file)
		{
			$json = $file->getContents();
			$data = json_sanitizer::decode($json);

			$available_languages[] = $this->get_language_data_from_json($data);
		}

		return $available_languages;
	}

	/**
	 * Collect some data from the composer.json file
	 *
	 * @param string $path
	 * @return array
	 *
	 * @throws DomainException When unable to language data from composer.json
	 */
	public function get_language_data_from_composer_file(string $path) : array
	{
		$json_data = file_get_contents($path);
		return $this->get_language_data_from_json(json_sanitizer::decode($json_data));
	}

	/**
	 * Collect some data from the composer.json data
	 *
	 * @param array $data
	 * @return array
	 *
	 * @throws DomainException When composer.json data is invalid for language files
	 */
	protected function get_language_data_from_json(array $data) : array
	{
		if (!isset($data['extra']['language-iso']) || !isset($data['extra']['english-name']) || !isset($data['extra']['local-name']) || !isset($data['extra']['direction']) || !isset($data['extra']['user-lang']) || !isset($data['extra']['plural-rule']) || !isset($data['extra']['recaptcha-lang']))
		{
			throw new DomainException('INVALID_LANGUAGE_PACK');
		}

		$authors = [];
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

		return [
			'iso'				=> $data['extra']['language-iso'],
			'name'				=> $data['extra']['english-name'],
			'local_name'		=> $data['extra']['local-name'],
			'author'			=> implode(', ', $authors),
			'version'			=> $data['version'],
			'phpbb_version'		=> $data['extra']['phpbb-version'],
			'direction'			=> $data['extra']['direction'],
			'user_lang'			=> $data['extra']['user-lang'],
			'plural_rule'		=> $data['extra']['plural-rule'],
			'recaptcha_lang'	=> $data['extra']['recaptcha-lang'],
			'turnstile_lang'	=> $data['extra']['turnstile-lang'] ?? '',
		];
	}
}
