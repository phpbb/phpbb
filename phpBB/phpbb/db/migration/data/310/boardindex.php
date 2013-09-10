<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data0;

class boardindex extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['board_index_text']);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('board_index_text', '')),
		);
	}
}
