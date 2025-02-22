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

namespace phpbb\storage\provider;

use phpbb\language\language;

class local implements provider_interface
{
	/**
	 * @var language
	 */
	protected $language;

	/**
	 * Constructor
	 *
	 * @param language $language
	 */
	public function __construct(language $language)
	{
		$this->language = $language;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_name(): string
	{
		return 'local';
	}

	public function get_title(): string
	{
		return $this->language->lang('STORAGE_ADAPTER_LOCAL_NAME');
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_adapter_class(): string
	{
		return \phpbb\storage\adapter\local::class;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_options(): array
	{
		return [
			'path' => [
				'title' => $this->language->lang('STORAGE_ADAPTER_LOCAL_OPTION_PATH'),
				'description' => $this->language->lang('STORAGE_ADAPTER_LOCAL_OPTION_PATH_EXPLAIN'),
				'form_macro' => [
					'tag' => 'input',
					'type' => 'text',
				],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available(): bool
	{
		return true;
	}
}
