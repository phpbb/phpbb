<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class board_service extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['board_service']) &&
			isset($this->config['board_service_msg']);
	}
	
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\alpha1');
	}			

	public function update_data()
	{
		return array(
			array('config.add', array('board_service', '0')),  
			array('config.add', array('board_service_msg', '')),			
		);                                                       
	}
}
