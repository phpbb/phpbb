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

class remove_broken_captcha extends migration
{
	/** @var array List of broken captcha that have been removed  */
	private array $removed_captchas = [
		'core.captcha.plugins.gd',
		'core.captcha.plugins.gd_wave',
		'core.captcha.plugins.nogd'
	];

	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
		];
	}

	public function update_data(): array
	{
		return [
			['config.remove', ['captcha_gd']],
			['config.remove', ['captcha_gd_3d_noise']],
			['config.remove', ['captcha_gd_fonts']],
			['config.remove', ['captcha_gd_foreground_noise']],
			['config.remove', ['captcha_gd_wave']],
			['config.remove', ['captcha_gd_x_grid']],
			['config.remove', ['captcha_gd_y_grid']],
			['custom', [[$this, 'replace_broken_captcha']]],
		];
	}

	public function revert_data(): array
	{
		return [
			['config.add', ['captcha_gd', 0]],
			['config.add', ['captcha_gd_3d_noise', 1]],
			['config.add', ['captcha_gd_fonts', 1]],
			['config.add', ['captcha_gd_foreground_noise', 1]],
			['config.add', ['captcha_gd_wave', 0]],
			['config.add', ['captcha_gd_x_grid', 25]],
			['config.add', ['captcha_gd_y_grid', 25]],
		];
	}

	public function replace_broken_captcha(): void
	{
		if (in_array($this->config['captcha_plugin'], $this->removed_captchas))
		{
			$this->config->set('captcha_plugin', 'core.captcha.plugins.incomplete');
		}
	}
}
