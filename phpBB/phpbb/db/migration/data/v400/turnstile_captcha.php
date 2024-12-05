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

namespace phpbb\db\migration\data\v400;

use phpbb\db\migration\migration;

class turnstile_captcha extends migration
{
	public function effectively_installed(): bool
	{
		return $this->config->offsetExists('captcha_turnstile_sitekey')
			&& $this->config->offsetExists('captcha_turnstile_secret')
			&& $this->config->offsetExists('captcha_turnstile_theme');
	}

	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
		];
	}

	public function update_data(): array
	{
		return [
			['config.add', ['captcha_turnstile_sitekey', '']],
			['config.add', ['captcha_turnstile_secret', '']],
			['config.add', ['captcha_turnstile_theme', 'light']],
		];
	}

	public function revert_data(): array
	{
		return [
			['config.remove', ['captcha_turnstile_sitekey']],
			['config.remove', ['captcha_turnstile_secret']],
			['config.remove', ['captcha_turnstile_theme']],
		];
	}
}
