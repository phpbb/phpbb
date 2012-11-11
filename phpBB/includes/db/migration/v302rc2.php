<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v302rc1 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v302rc1');
	}

	function update_schema()
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
				$this->table_prefix . 'sessions' => array('session_forum_id'),
			),
			'add_index' => array(
				$this->table_prefix . 'sessions' => array(
					'session_fid' => array('session_forum_id'),
				),
			),
		);
	}

	function update_data()
	{
	}
}
