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

class notifications_cron_p2 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\notifications_cron');
	}

	public function update_data()
	{
		return array(
			// Make read_notification_last_gc dynamic.
			array('config.remove', array('read_notification_last_gc')),
			array('config.add', array('read_notification_last_gc', 0, 1)),
		);
	}
}
