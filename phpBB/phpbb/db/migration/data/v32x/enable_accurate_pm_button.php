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

namespace phpbb\db\migration\data\v32x;

class enable_accurate_pm_button extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v32x\v322',
		);
	}

	public function effectively_installed()
	{
		return isset($this->config['enable_accurate_pm_button']);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('enable_accurate_pm_button', '1')),
		);
	}
}
