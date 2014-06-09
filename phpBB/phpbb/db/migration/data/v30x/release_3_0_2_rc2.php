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

namespace phpbb\db\migration\data\v30x;

class release_3_0_2_rc2 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.0.2-RC2', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_2_rc1');
	}

	public function update_schema()
	{
		return array(
			'change_columns' => array(
				$this->table_prefix . 'drafts' => array(
					'draft_subject' => array('STEXT_UNI', ''),
				),
				$this->table_prefix . 'forums' => array(
					'forum_last_post_subject' => array('STEXT_UNI', ''),
				),
				$this->table_prefix . 'posts' => array(
					'post_subject' => array('STEXT_UNI', '', 'true_sort'),
				),
				$this->table_prefix . 'privmsgs' => array(
					'message_subject' => array('STEXT_UNI', ''),
				),
				$this->table_prefix . 'topics' => array(
					'topic_title' => array('STEXT_UNI', '', 'true_sort'),
					'topic_last_post_subject' => array('STEXT_UNI', ''),
				),
			),
			'drop_keys' => array(
				$this->table_prefix . 'sessions' => array(
					'session_forum_id',
				),
			),
			'add_index' => array(
				$this->table_prefix . 'sessions' => array(
					'session_fid' => array('session_forum_id'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'add_index' => array(
				$this->table_prefix . 'sessions' => array(
					'session_forum_id' => array(
						'session_forum_id',
					),
				),
			),
			'drop_keys' => array(
				$this->table_prefix . 'sessions' => array(
					'session_fid',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.0.2-RC2')),
		);
	}
}
