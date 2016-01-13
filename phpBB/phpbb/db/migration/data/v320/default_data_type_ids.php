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

namespace phpbb\db\migration\data\v320;

class default_data_type_ids extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v320\v320a2');
	}

	public function update_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'attachments'			=> array(
					'topic_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'acl_users'			=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'bookmarks'			=> array(
					'topic_id'		=> array('ULINT', 0),
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'bots'				=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'drafts'				=> array(
					'topic_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'forums_access'		=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'forums_track'		=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'forums_watch'		=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'log'					=> array(
					'user_id'		=> array('ULINT', 0),
					'topic_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'login_attempts'		=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'moderator_cache'		=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'notifications'		=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'oauth_accounts'		=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'oauth_tokens'		=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'poll_options'		=> array(
					'topic_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'poll_votes'			=> array(
					'topicr_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'posts'				=> array(
					'post_id'		=> array('ULINT', null, 'auto_increment'),
					'topic_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'privmsgs_folder'		=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'privmsgs_rules'		=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'privmsgs_to'			=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'profile_fields_data'	=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'reports'				=> array(
					'post_id'		=> array('ULINT', 0),
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'search_wordmatch'	=> array(
					'post_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'sessions_keys'		=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'topics'				=> array(
					'topic_id'		=> array('ULINT', null, 'auto_increment'),
				),
				$this->table_prefix . 'topics_track'		=> array(
					'user_id'		=> array('ULINT', 0),
					'topic_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'topics_posted'		=> array(
					'user_id'		=> array('ULINT', 0),
					'topic_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'topics_watch'		=> array(
					'user_id'		=> array('ULINT', 0),
					'topic_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'user_notifications'	=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'user_group'			=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'users'				=> array(
					'user_id'		=> array('ULINT', null, 'auto_increment'),
				),
				$this->table_prefix . 'warnings'			=> array(
					'user_id'		=> array('ULINT', 0),
					'post_Id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'zebra'			=> array(
					'user_id'		=> array('ULINT', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'attachments'			=> array(
					'topic_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'acl_users'			=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'bookmarks'			=> array(
					'topic_id'		=> array('UINT', 0),
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'bots'				=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'drafts'				=> array(
					'topic_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'forums_access'		=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'forums_track'		=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'forums_watch'		=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'log'					=> array(
					'user_id'		=> array('UINT', 0),
					'topic_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'login_attempts'		=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'moderator_cache'		=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'notifications'		=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'oauth_accounts'		=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'oauth_tokens'		=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'poll_options'		=> array(
					'topic_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'poll_votes'			=> array(
					'topicr_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'posts'				=> array(
					'post_id'		=> array('UINT', null, 'auto_increment'),
					'topic_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'privmsgs_folder'		=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'privmsgs_rules'		=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'privmsgs_to'			=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'profile_fields_data'	=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'reports'				=> array(
					'post_id'		=> array('UINT', 0),
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'search_wordmatch'	=> array(
					'post_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'sessions_keys'		=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'topics'				=> array(
					'topic_id'		=> array('UINT', null, 'auto_increment'),
				),
				$this->table_prefix . 'topics_track'		=> array(
					'user_id'		=> array('UINT', 0),
					'topic_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'topics_posted'		=> array(
					'user_id'		=> array('UINT', 0),
					'topic_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'topics_watch'		=> array(
					'user_id'		=> array('UINT', 0),
					'topic_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'user_notifications'	=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'user_group'			=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'users'				=> array(
					'user_id'		=> array('UINT', null, 'auto_increment'),
				),
				$this->table_prefix . 'warnings'			=> array(
					'user_id'		=> array('UINT', 0),
					'post_Id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'zebra'			=> array(
					'user_id'		=> array('UINT', 0),
				),
			),
		);
	}
}
