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

interface provider_interface
{
	/**
	 * Gets adapter name
	 *
	 * @return string
	 */
	public function get_name(): string;

	/**
	 * Gets adapter title for acp
	 *
	 * @return string
	 */
	public function get_title(): string;

	/**
	 * Gets adapter class
	 *
	 * @return string
	 */
	public function get_adapter_class(): string;

	/**
	 * Gets adapter options
	 *
	 * Example:
	 * public function get_options()
	 * {
	 * 	return [
	 * 		'text-test' => [
	 * 			'title' => $this->language->lang('STORAGE_ADAPTER_DEMO_OPTION_TEXT_TEST'),
	 * 			'description' => $this->language->lang('STORAGE_ADAPTER_DEMO_OPTION_TEXT_TEST_EXPLAIN'),
	 * 			'form_macro' => [
	 * 				'tag' => 'input',
	 * 				'type' => 'text',
	 * 			],
	 * 		],
	 * 		'password-test' => [
	 * 			'title' => $this->language->lang('STORAGE_ADAPTER_DEMO_OPTION_PASSWORD_TEST'),
	 * 			'description' => $this->language->lang('STORAGE_ADAPTER_DEMO_OPTION_PASSWORD_TEST_EXPLAIN'),
	 * 			'form_macro' => [
	 * 				'tag' => 'input',
	 * 				'type' => 'password',
	 * 			],
	 * 		],
	 * 		'radio-test' => [
	 * 			'title' => $this->language->lang('STORAGE_ADAPTER_DEMO_OPTION_RADIO_TEST'),
	 * 			'description' => $this->language->lang('STORAGE_ADAPTER_DEMO_OPTION_RADIO_TEST_EXPLAIN'),
	 * 			'form_macro' => [
	 * 				'tag' => 'radio',
	 * 				'buttons' => [
	 * 					[
	 * 						'type' => 'radio',
	 * 						'value' => '1',
	 * 						'label' => $this->language->lang('STORAGE_ADAPTER_DEMO_OPTION_RADIO_TEST_LABEL_ONE'),
	 * 					],
	 * 					[
	 * 						'type' => 'radio',
	 * 						'value' => '2',
	 * 						'label' => $this->language->lang('STORAGE_ADAPTER_DEMO_OPTION_RADIO_TEST_LABEL_TWO'),
	 * 					],
	 * 				],
	 * 			],
	 * 		],
	 * 		'select-test' => [
	 * 			'title' => $this->language->lang('STORAGE_ADAPTER_DEMO_OPTION_SELECT_TEST'),
	 * 			'description' => $this->language->lang('STORAGE_ADAPTER_DEMO_OPTION_SELECT_TEST_EXPLAIN'),
	 * 			'form_macro' => [
	 * 				'tag' => 'select',
	 * 				'options' => [
	 * 					['value' => 'one', 'label' => $this->language->lang('STORAGE_ADAPTER_DEMO_OPTION_SELECT_TEST_LABEL_ONE')],
	 * 					['value' => 'two', 'label' => $this->language->lang('STORAGE_ADAPTER_DEMO_OPTION_SELECT_TEST_LABEL_TWO')],
	 * 				],
	 * 			],
	 * 		],
	 * 		'textarea-test' => [
	 * 			'title' => $this->language->lang('STORAGE_ADAPTER_DEMO_OPTION_TEXTAREA_TEST'),
	 * 			'description' => $this->language->lang('STORAGE_ADAPTER_DEMO_OPTION_TEXTAREA_TEST_EXPLAIN'),
	 * 			'form_macro' => [
	 * 				'tag' => 'textarea',
	 * 			]
	 * 		],
	 * 	];
	 * }
	 *
	 * @return array    Configuration keys
	 */
	public function get_options(): array;

	/**
	 * Return true if the adapter is available
	 *
	 * @return bool
	 */
	public function is_available(): bool;
}
