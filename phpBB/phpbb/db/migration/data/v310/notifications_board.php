<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class notifications_board extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\notifications');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('allow_board_notifications', 1)),
			array('custom', array(array($this, 'update_user_subscriptions'))),
		);
	}

	public function update_user_subscriptions()
	{
		$sql = 'UPDATE ' . USER_NOTIFICATIONS_TABLE . "
		SET method = 'board'
		WHERE method = ''";
		$this->sql_query($sql);
	}

	public function revert_data()
	{
		return array(
			array('custom', array(array($this, 'revert_user_subscriptions   '))),
		);
	}

	public function revert_user_subscriptions()
	{
		$sql = 'UPDATE ' . USER_NOTIFICATIONS_TABLE . "
		SET method = ''
		WHERE method = 'board'";
		$this->sql_query($sql);
	}

}
