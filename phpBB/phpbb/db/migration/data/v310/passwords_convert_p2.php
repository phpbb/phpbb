<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class passwords_convert_p2 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\passwords_convert_p1');
	}

	public function update_schema()
	{
		return array(
			'drop_columns'		=> array(
				$this->table_prefix . 'users'		=> array(
					'user_pass_convert',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'users'		=> array(
					'user_pass_convert'	=> array('BOOL', 0, 'after' => 'user_passchg'),
				),
			),
		);
	}
}
