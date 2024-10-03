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
	const CONFIRMATION_REGISTRATION = 1;
	const CONFIRMATION_LOGIN = 2;

	const CONFIRMATION_POST = 3;

	const CONFIRMATION_REPORT = 4;


	/**
	 * Check if the plugin is available
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
	 * Display the captcha for the specified type
	 *
	 * @param int $type Type of captcha, should be one of the CONFIRMATION_* constants
	 * @return void
	 */
	public function show(int $type): void;
}
