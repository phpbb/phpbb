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

namespace phpbb\captcha\plugins;

interface plugin_interface
{
	/**
	 * Check if the plugin is available
	 *
	 * @return bool True if the plugin is available, false if not
	 */
	public function is_available(): bool;

	/**
	 * Check if the plugin has a configuration
	 *
	 * @return bool True if the plugin has a configuration, false if not
	 */
	public function has_config(): bool;

	/**
	 * Get the name of the plugin, should be language variable
	 *
	 * @return string
	 */
	public function get_name(): string;

	/**
	 * Set the service name of the plugin
	 *
	 * @param string $name
	 */
	public function set_name(string $name): void;

	/**
	 * Display the captcha for the specified type
	 *
	 * @param confirm_type $type Type of captcha, should be one of the CONFIRMATION_* constants
	 * @return void
	 */
	public function init(confirm_type $type): void;

	/**
	 * Get hidden form fields for this captcha plugin
	 *
	 * @return array Hidden form fields
	 */
	public function get_hidden_fields(): array;

	/**
	 * Validate the captcha with the given request data
	 *
	 * @return bool True if request data was valid captcha reply, false if not
	 */
	public function validate(): bool;

	/**
	 * Get error string from captcha
	 *
	 * @return string Error string, empty string if there is no error
	 */
	public function get_error(): string;

	/**
	 * Return whether captcha was solved
	 *
	 * @return bool True if captcha was solved, false if not
	 */
	public function is_solved(): bool;

	/**
	 * Reset captcha state, e.g. after checking if it's valid
	 *
	 * @return void
	 */
	public function reset(): void;

	/**
	 * Get attempt count for this captcha and user
	 *
	 * @return int Number of attempts
	 */
	public function get_attempt_count(): int;

	/**
	 * Get template filename for captcha
	 *
	 * @return string Template file name
	 */
	public function get_template(): string;

	/**
	 * Get template filename for demo
	 *
	 * @return string Demo template file name
	 */
	public function get_demo_template(): string;

	/**
	 * Garbage collect captcha plugin
	 *
	 * @param confirm_type $confirm_type Confirm type to garbage collect, defaults to all (0)
	 * @return void
	 */
	public function garbage_collect(confirm_type $confirm_type = confirm_type::UNDEFINED): void;

	/**
	 * Display acp page
	 *
	 * @param mixed $id ACP module id
	 * @param mixed $module ACP module name
	 * @return void
	 */
	public function acp_page(mixed $id, mixed $module): void;
}
