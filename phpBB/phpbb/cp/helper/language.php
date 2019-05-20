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

namespace phpbb\cp\helper;

class language
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\extension\manager */
	protected $ext_manager;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var string php File extension */
	protected $php_ext;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\extension\manager $ext_manager,
		\phpbb\language\language $lang,
		$php_ext
	)
	{
		$this->config		= $config;
		$this->ext_manager	= $ext_manager;
		$this->lang			= $lang;

		$this->php_ext		= $php_ext;
	}

	public function load_cp_language_files($cp)
	{
		$files = [];
		$finder = $this->ext_manager->get_finder();

		$languages = array_unique([
			\phpbb\language\language::FALLBACK_LANGUAGE,
			$this->config['default_lang'],
			$this->lang->get_used_language(),
		]);

		foreach ($languages as $lang)
		{
			$result = $finder
				->prefix("info_{$cp}_")
				->suffix(".{$this->php_ext}")
				->extension_directory("/language/{$lang}")
				->find();

			$files = array_merge($files, $result);
		}

		foreach ($files as $lang_file => $ext_name)
		{
			$this->lang->add_lang($lang_file, $ext_name);
		}
	}
}
