<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\db\migration\data\v310;

/**
 * Migration to convert the Soft Delete MOD for 3.0
 *
 * https://www.phpbb.com/customise/db/mod/soft_delete/
 */
class soft_delete_mod_convert2 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\soft_delete_mod_convert',
		);
	}

	public function effectively_installed()
	{
		return !$this->db_tools->sql_column_exists($this->table_prefix . 'posts', 'post_deleted');
	}

	public function update_schema()
	{
		return array(
			'drop_columns'		=> array(
				$this->table_prefix . 'forums'			=> array('forum_deleted_topic_count', 'forum_deleted_reply_count'),
				$this->table_prefix . 'posts'			=> array('post_deleted', 'post_deleted_time'),
				$this->table_prefix . 'topics'			=> array('topic_deleted', 'topic_deleted_time', 'topic_deleted_reply_count'),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'forums'			=> array(
					'forum_deleted_topic_count'		=> array('UINT', 0),
					'forum_deleted_reply_count'		=> array('UINT', 0),
				),
				$this->table_prefix . 'posts'			=> array(
					'post_deleted'					=> array('UINT', 0),
					'post_deleted_time'				=> array('TIMESTAMP', 0),
				),
				$this->table_prefix . 'topics'			=> array(
					'topic_deleted'					=> array('UINT', 0),
					'topic_deleted_time'			=> array('TIMESTAMP', 0),
					'topic_deleted_reply_count'		=> array('UINT', 0),
				),
			),
		);
	}
}
