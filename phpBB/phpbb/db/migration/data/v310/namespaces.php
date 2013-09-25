<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class namespaces extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\dev',
		);
	}

	public function update_data()
	{
		return array(
			array('if', array(
				(preg_match('#^phpbb_search_#', $this->config['search_type'])),
				array('config.update', array('search_type', str_replace('phpbb_search_', 'phpbb\\search\\', $this->config['search_type']))),
			)),
		);
	}
}
