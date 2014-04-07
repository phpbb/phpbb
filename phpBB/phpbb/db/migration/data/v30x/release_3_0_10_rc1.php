<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v30x;

class release_3_0_10_rc1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.0.10-RC1', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_9');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('email_max_chunk_size', 50)),

			array('config.update', array('version', '3.0.10-RC1')),
		);
	}
}
