<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class board_contact_name extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['board_contact_name']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\beta2');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('board_contact_name', '')),
		);
	}
}
