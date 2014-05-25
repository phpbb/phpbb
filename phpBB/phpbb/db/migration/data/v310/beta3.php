<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class beta3 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\beta2',
			'\phpbb\db\migration\data\v310\auth_provider_oauth2',
			'\phpbb\db\migration\data\v310\board_contact_name',
			'\phpbb\db\migration\data\v310\jquery_update2',
			'\phpbb\db\migration\data\v310\live_searches_config',
			'\phpbb\db\migration\data\v310\prune_shadow_topics',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.1.0-b3')),
		);
	}
}
