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

class jquery_ui extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\jquery_update2',
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('load_jquery_ui_url', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js')),
		);
	}

}
