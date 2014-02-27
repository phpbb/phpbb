<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class alpha3 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\alpha2',
			'\phpbb\db\migration\data\v310\avatar_types',
			'\phpbb\db\migration\data\v310\passwords',
			'\phpbb\db\migration\data\v310\profilefield_types',
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.1.0-a3')),
		);
	}
}
