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

namespace phpbb\db\migration\data\v33x;

class google_recaptcha_v3 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->config->offsetExists('recaptcha_v3_key');
	}

	static public function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v330\v330',
		];
	}

	public function update_data()
	{
		$data = [
			['config.add', ['recaptcha_v3_key', '']],
			['config.add', ['recaptcha_v3_secret', '']],
			['config.add', ['recaptcha_v3_domain', \phpbb\captcha\plugins\recaptcha_v3::GOOGLE]],
			['config.add', ['recaptcha_v3_method', \phpbb\captcha\plugins\recaptcha_v3::POST]],
		];

		foreach (\phpbb\captcha\plugins\recaptcha_v3::get_actions() as $action)
		{
			$data[] = ['config.add', ["recaptcha_v3_threshold_{$action}", '0.5']];
		}

		return $data;
	}
}
