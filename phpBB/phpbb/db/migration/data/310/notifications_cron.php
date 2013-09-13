<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_db_migration_data_310_notifications_cron extends phpbb_db_migration
{
	static public function depends_on()
	{
		return array('phpbb_db_migration_data_310_notifications');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('read_notification_expire_days', 30)),
			array('config.add', array('read_notification_last_gc', 0)), // last run
			array('config.add', array('read_notification_gc', (60 * 60 * 24))), // seconds between run; 1 day
		);
	}
}
