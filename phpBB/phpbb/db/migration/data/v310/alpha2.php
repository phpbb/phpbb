<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class alpha2 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\alpha1',
			'\phpbb\db\migration\data\v310\notifications_cron_p2',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.1.0-a2')),
		);
	}
}
