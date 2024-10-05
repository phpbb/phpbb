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
	 * {@inheritDoc}
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
	 * {@inheritDoc}
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
	 * {@inheritDoc}
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
	 * {@inheritDoc}
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
	public function get_hidden_fields(): array
	{
		if (method_exists($this->legacy_captcha, 'get_hidden_fields'))
		{
			return $this->legacy_captcha->get_hidden_fields();
		}

		return [];
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

	/**
	 * {@inheritDoc}
	 */
	public function get_demo_template(): string
	{
		if (method_exists($this->legacy_captcha, 'get_demo_template'))
		{
			return $this->legacy_captcha->get_demo_template(0);
		}

		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function garbage_collect(int $confirm_type = 0): void
	{
		if (method_exists($this->legacy_captcha, 'garbage_collect'))
		{
			$this->legacy_captcha->garbage_collect($confirm_type);
		}
	}

	public function acp_page($id, $module): void
	{
		if (method_exists($this->legacy_captcha, 'acp_page'))
		{
			$this->legacy_captcha->acp_page($id, $module);
		}
	}
}
