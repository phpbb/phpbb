<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data\v30x;

class release_3_0_12_rc3 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.0.12-RC3', '>=') && phpbb_version_compare($this->config['version'], '3.1.0-dev', '<');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_12_rc2');
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.0.12-RC3')),
		);
	}
}
