<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data\v30x;

class release_3_0_5_rc1part2 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.0.5-RC1', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_5_rc1');
	}

	public function update_schema()
	{
		return array(
			'drop_keys'			=> array(
				$this->table_prefix . 'acl_options'		=> array('auth_option'),
			),
			'add_unique_index'	=> array(
				$this->table_prefix . 'acl_options'		=> array(
					'auth_option'		=> array('auth_option'),
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.0.5-RC1')),
		);
	}
}
