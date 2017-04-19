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

namespace phpbb\db\migration\data\v320;

class notifications_board extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v320\dev',
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('allow_board_notifications', 1)),
			array('custom', array(array($this, 'update_user_subscriptions'))),
			array('custom', array(array($this, 'update_module'))),
		);
	}

	public function update_module()
	{
		$sql = 'UPDATE ' . MODULES_TABLE . "
			SET module_auth = 'cfg_allow_board_notifications'
			WHERE module_basename = 'ucp_notifications'
				AND module_mode = 'notification_list'";
		$this->sql_query($sql);
	}

	public function update_user_subscriptions()
	{
		$sql = 'UPDATE ' . USER_NOTIFICATIONS_TABLE . "
			SET method = 'notification.method.board'
			WHERE method = ''";
		$this->sql_query($sql);
	}

	public function revert_data()
	{
		return array(
			array('custom', array(array($this, 'revert_user_subscriptions'))),
			array('custom', array(array($this, 'revert_module'))),
		);
	}

	public function revert_user_subscriptions()
	{
		$sql = 'UPDATE ' . USER_NOTIFICATIONS_TABLE . "
			SET method = ''
			WHERE method = 'notification.method.board'";
		$this->sql_query($sql);
	}

	public function revert_module()
	{
		$sql = 'UPDATE ' . MODULES_TABLE . "
			SET auth = ''
			WHERE module_basename = 'ucp_notifications'
				AND module_mode = 'notification_list'";
		$this->sql_query($sql);
	}
}
