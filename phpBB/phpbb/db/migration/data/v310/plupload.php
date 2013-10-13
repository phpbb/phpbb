<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class plupload extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['plupload_last_gc']) &&
			isset($this->config['plupload_salt']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\310\dev');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('plupload_last_gc', 0)),
			array('config.add', array('plupload_salt', unique_id())),
		);
	}
}
