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

	private string $last_error;

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
	public function init(int $type): void
	{
		if (method_exists($this->legacy_captcha, 'init'))
		{
			$this->legacy_captcha->init($type);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(array $request_data): bool
	{
		if (method_exists($this->legacy_captcha, 'validate'))
		{
			$error = $this->legacy_captcha->validate($request_data);
			if ($error)
			{
				$this->last_error = $error;
				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_solved(): bool
	{
		if (method_exists($this->legacy_captcha, 'is_solved'))
		{
			return $this->legacy_captcha->is_solved();
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset(): void
	{
		if (method_exists($this->legacy_captcha, 'reset'))
		{
			$this->legacy_captcha->reset();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_attempt_count(): int
	{
		if (method_exists($this->legacy_captcha, 'get_attempt_count'))
		{
			return $this->legacy_captcha->get_attempt_count();
		}

		// Ensure this is deemed as too many attempts
		return PHP_INT_MAX;
	}
}
