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

namespace phpbb\db\migration\data\v330;

class remove_broken_captchas extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			array('config.remove', array('captcha_gd')),
			array('config.remove', array('captcha_gd_3d_noise')),
			array('config.remove', array('captcha_gd_fonts')),
			array('config.remove', array('captcha_gd_foreground_noise')),
			array('config.remove', array('captcha_gd_wave')),
			array('config.remove', array('captcha_gd_x_grid')),
			array('config.remove', array('captcha_gd_y_grid')),
			array('config.update', array('captcha_plugin', 'core.captcha.plugins.recaptcha')),
		);
	}
}
