<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class beta2 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\beta1',
			'\phpbb\db\migration\data\v310\acp_prune_users_module',
			'\phpbb\db\migration\data\v310\profilefield_location_cleanup',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.1.0-b2')),
		);
	}
}
