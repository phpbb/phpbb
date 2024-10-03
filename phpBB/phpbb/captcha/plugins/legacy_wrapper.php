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

class legacy_wrapper implements plugin_interface
{
	private $legacy_captcha;

	public function __construct($legacy_captcha)
	{
		$this->legacy_captcha = $legacy_captcha;
	}

	/**
	 * Check if the plugin is available
	 * @return bool True if the plugin is available, false if not
	 */
	public function is_available(): bool
	{
		if (method_exists($this->legacy_captcha, 'is_available'))
		{
			return $this->legacy_captcha->is_available();
		}

		return false;
	}

	/**
	 * Check if the plugin has a configuration
	 *
	 * @return bool True if the plugin has a configuration, false if not
	 */
	public function has_config(): bool
	{
		if (method_exists($this->legacy_captcha, 'has_config'))
		{
			return $this->legacy_captcha->has_config();
		}

		return false;
	}

	/**
	 * Get the name of the plugin, should be language variable
	 *
	 * @return string
	 */
	public function get_name(): string
	{
		if (method_exists($this->legacy_captcha, 'get_name'))
		{
			return $this->legacy_captcha->has_config();
		}

		return false;
	}

	/**
	 * Display the captcha for the specified type
	 *
	 * @param int $type Type of captcha, should be one of the CONFIRMATION_* constants
	 * @return void
	 */
	public function show(int $type): void
	{
		if (method_exists($this->legacy_captcha, 'init'))
		{
			$this->legacy_captcha->init($type);
		}
	}
}