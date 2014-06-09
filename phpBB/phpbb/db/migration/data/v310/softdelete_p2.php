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

class softdelete_p2 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return !$this->db_tools->sql_column_exists($this->table_prefix . 'posts', 'post_approved');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\dev',
			'\phpbb\db\migration\data\v310\softdelete_p1',
		);
	}

	public function update_schema()
	{
		return array(
			'drop_columns'		=> array(
				$this->table_prefix . 'forums'			=> array('forum_posts', 'forum_topics', 'forum_topics_real'),
				$this->table_prefix . 'posts'			=> array('post_approved'),
				$this->table_prefix . 'topics'			=> array('topic_approved', 'topic_replies', 'topic_replies_real'),
			),
			'drop_keys'		=> array(
				$this->table_prefix . 'posts'			=> array('post_approved'),
				$this->table_prefix . 'topics'			=> array(
					'forum_appr_last',
					'topic_approved',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'forums'			=> array(
					'forum_posts'			=> array('UINT', 0),
					'forum_topics'			=> array('UINT', 0),
					'forum_topics_real'		=> array('UINT', 0),
				),
				$this->table_prefix . 'posts'			=> array(
					'post_approved'			=> array('BOOL', 1),
				),
				$this->table_prefix . 'topics'		=> array(
					'topic_approved'			=> array('BOOL', 1),
					'topic_replies'				=> array('UINT', 0),
					'topic_replies_real'		=> array('UINT', 0),
				),
			),
			'add_index'		=> array(
				$this->table_prefix . 'posts'			=> array(
					'post_approved'		=> array('post_approved'),
				),
				$this->table_prefix . 'topics'		=> array(
					'forum_appr_last'	=> array('forum_id', 'topic_approved', 'topic_last_post_id'),
					'topic_approved'	=> array('topic_approved'),
				),
			),
		);
	}
}
