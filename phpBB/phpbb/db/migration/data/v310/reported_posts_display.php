<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class reported_posts_display extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'reports', 'reported_post_enable_bbcode');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_11');
	}

	public function update_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'reports'		=> array(
					'reported_post_enable_bbcode'		=> array('BOOL', 1),
					'reported_post_enable_smilies'		=> array('BOOL', 1),
					'reported_post_enable_magic_url'	=> array('BOOL', 1),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'		=> array(
				$this->table_prefix . 'reports'		=> array(
					'reported_post_enable_bbcode',
					'reported_post_enable_smilies',
					'reported_post_enable_magic_url',
				),
			),
		);
	}
}
