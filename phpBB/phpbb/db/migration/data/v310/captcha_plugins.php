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

namespace phpbb\db\migration\data\v310;

class captcha_plugins extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\rc2',
		);
	}

	public function update_data()
	{
		$captcha_plugin = $this->config['captcha_plugin'];
		if (strpos($captcha_plugin, 'phpbb_captcha_') === 0)
		{
			$captcha_plugin = substr($captcha_plugin, strlen('phpbb_captcha_'));
		}
		else if (strpos($captcha_plugin, 'phpbb_') === 0)
		{
			$captcha_plugin = substr($captcha_plugin, strlen('phpbb_'));
		}

		return array(
			array('if', array(
				(is_file($this->phpbb_root_path . 'phpbb/captcha/plugins/' . $captcha_plugin . '.' . $this->php_ext)),
				array('config.update', array('captcha_plugin', 'core.captcha.plugins.' . $captcha_plugin)),
			)),
			array('if', array(
				(!is_file($this->phpbb_root_path . 'phpbb/captcha/plugins/' . $captcha_plugin . '.' . $this->php_ext)),
				array('config.update', array('captcha_plugin', 'core.captcha.plugins.nogd')),
			)),
		);
	}
}
