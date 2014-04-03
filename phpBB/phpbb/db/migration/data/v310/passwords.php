<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class passwords extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_11');
	}

	public function update_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'users'	=> array(
					'user_password'		=> array('VCHAR:255', ''),
				),
				$this->table_prefix . 'forums'	=> array(
					'forum_password'	=> array('VCHAR:255', ''),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'users'	=> array(
					'user_password'		=> array('VCHAR:40', ''),
				),
				$this->table_prefix . 'forums'	=> array(
					'forum_password'	=> array('VCHAR:40', ''),
				),
			),
		);
	}
}
