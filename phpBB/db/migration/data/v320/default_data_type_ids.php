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
		return array(
			'\phpbb\db\migration\data\v320\v320a2',
			'\phpbb\db\migration\data\v320\oauth_states',
		);
	}

	public function update_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'acl_users'			=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'attachments'			=> array(
					'attach_id'		=> array('ULINT', null, 'auto_increment'),
					'post_msg_id'	=> array('ULINT', 0),
					'poster_id'		=> array('ULINT', 0),
					'topic_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'banlist'				=> array(
					'ban_id'		=> array('ULINT', null, 'auto_increment'),
					'ban_userid'	=> array('ULINT', 0),
				),
				$this->table_prefix . 'bookmarks'			=> array(
					'topic_id'		=> array('ULINT', 0),
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'bots'				=> array(
					'bot_id'		=> array('ULINT', null, 'auto_increment'),
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'drafts'				=> array(
					'draft_id'		=> array('ULINT', null, 'auto_increment'),
					'user_id'		=> array('ULINT', 0),
					'topic_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'forums'				=> array(
					'forum_last_post_id'	=> array('ULINT', 0),
					'forum_last_poster_id'	=> array('ULINT', 0),
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
					'log_id'		=> array('ULINT', null, 'auto_increment'),
					'post_id'		=> array('ULINT', 0),
					'reportee_id'	=> array('ULINT', 0),
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
				$this->table_prefix . 'oauth_states'		=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'oauth_tokens'		=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'poll_options'		=> array(
					'topic_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'poll_votes'			=> array(
					'topic_id'		=> array('ULINT', 0),
					'vote_user_id'	=> array('ULINT', 0),
				),
				$this->table_prefix . 'posts'				=> array(
					'post_id'			=> array('ULINT', null, 'auto_increment'),
					'poster_id'			=> array('ULINT', 0),
					'post_delete_user'	=> array('ULINT', 0),
					'post_edit_user'	=> array('ULINT', 0),
					'topic_id'			=> array('ULINT', 0),
				),
				$this->table_prefix . 'privmsgs'			=> array(
					'author_id'			=> array('ULINT', 0),
					'message_edit_user'	=> array('ULINT', 0),
					'msg_id'			=> array('ULINT', null, 'auto_increment'),
				),
				$this->table_prefix . 'privmsgs_folder'		=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'privmsgs_rules'		=> array(
					'rule_user_id'	=> array('ULINT', 0),
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'privmsgs_to'			=> array(
					'author_id'		=> array('ULINT', 0),
					'msg_id'		=> array('ULINT', 0),
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'profile_fields_data'	=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'reports'				=> array(
					'report_id'		=> array('ULINT', 0),
					'pm_id'			=> array('ULINT', 0),
					'post_id'		=> array('ULINT', 0),
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'search_wordlist'		=> array(
					'word_id'		=> array('ULINT', null, 'auto_increment'),
				),
				$this->table_prefix . 'search_wordmatch'	=> array(
					'post_id'		=> array('ULINT', 0),
					'word_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'sessions'			=> array(
					'session_user_id'	=> array('ULINT', 0),
				),
				$this->table_prefix . 'sessions_keys'		=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'topics'				=> array(
					'topic_id'				=> array('ULINT', null, 'auto_increment'),
					'topic_poster'			=> array('ULINT', 0),
					'topic_first_post_id'	=> array('ULINT', 0),
					'topic_last_post_id'	=> array('ULINT', 0),
					'topic_last_poster_id'	=> array('ULINT', 0),
					'topic_moved_id'		=> array('ULINT', 0),
					'topic_delete_user'		=> array('ULINT', 0),
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
					'item_id'		=> array('ULINT', 0),
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'user_group'			=> array(
					'user_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'users'				=> array(
					'user_id'		=> array('ULINT', null, 'auto_increment'),
				),
				$this->table_prefix . 'warnings'			=> array(
					'log_id'		=> array('ULINT', 0),
					'user_id'		=> array('ULINT', 0),
					'post_id'		=> array('ULINT', 0),
				),
				$this->table_prefix . 'words'				=> array(
					'word_id'		=> array('ULINT', null, 'auto_increment'),
				),
				$this->table_prefix . 'zebra'			=> array(
					'user_id'		=> array('ULINT', 0),
					'zebra_id'		=> array('ULINT', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'acl_users'			=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'attachments'			=> array(
					'attach_id'		=> array('UINT', null, 'auto_increment'),
					'post_msg_id'	=> array('UINT', 0),
					'poster_id'		=> array('UINT', 0),
					'topic_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'banlist'				=> array(
					'ban_id'		=> array('UINT', null, 'auto_increment'),
					'ban_userid'	=> array('UINT', 0),
				),
				$this->table_prefix . 'bookmarks'			=> array(
					'topic_id'		=> array('UINT', 0),
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'bots'				=> array(
					'bot_id'		=> array('UINT', null, 'auto_increment'),
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'drafts'				=> array(
					'draft_id'		=> array('UINT', null, 'auto_increment'),
					'user_id'		=> array('UINT', 0),
					'topic_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'forums'				=> array(
					'forum_last_post_id'	=> array('UINT', 0),
					'forum_last_poster_id'	=> array('UINT', 0),
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
					'log_id'		=> array('UINT', null, 'auto_increment'),
					'post_id'		=> array('UINT', 0),
					'reportee_id'	=> array('UINT', 0),
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
				$this->table_prefix . 'oauth_states'		=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'oauth_tokens'		=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'poll_options'		=> array(
					'topic_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'poll_votes'			=> array(
					'topic_id'		=> array('UINT', 0),
					'vote_user_id'	=> array('UINT', 0),
				),
				$this->table_prefix . 'posts'				=> array(
					'post_id'			=> array('UINT', null, 'auto_increment'),
					'poster_id'			=> array('UINT', 0),
					'post_delete_user'	=> array('UINT', 0),
					'post_edit_user'	=> array('UINT', 0),
					'topic_id'			=> array('UINT', 0),
				),
				$this->table_prefix . 'privmsgs'			=> array(
					'author_id'			=> array('UINT', 0),
					'message_edit_user'	=> array('UINT', 0),
					'msg_id'			=> array('UINT', null, 'auto_increment'),
				),
				$this->table_prefix . 'privmsgs_folder'		=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'privmsgs_rules'		=> array(
					'rule_user_id'	=> array('UINT', 0),
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'privmsgs_to'			=> array(
					'author_id'		=> array('UINT', 0),
					'msg_id'		=> array('UINT', 0),
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'profile_fields_data'	=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'reports'				=> array(
					'report_id'		=> array('UINT', 0),
					'pm_id'			=> array('UINT', 0),
					'post_id'		=> array('UINT', 0),
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'search_wordlist'		=> array(
					'word_id'		=> array('UINT', null, 'auto_increment'),
				),
				$this->table_prefix . 'search_wordmatch'	=> array(
					'post_id'		=> array('UINT', 0),
					'word_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'sessions'			=> array(
					'session_user_id'	=> array('UINT', 0),
				),
				$this->table_prefix . 'sessions_keys'		=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'topics'				=> array(
					'topic_id'				=> array('UINT', null, 'auto_increment'),
					'topic_poster'			=> array('UINT', 0),
					'topic_first_post_id'	=> array('UINT', 0),
					'topic_last_post_id'	=> array('UINT', 0),
					'topic_last_poster_id'	=> array('UINT', 0),
					'topic_moved_id'		=> array('UINT', 0),
					'topic_delete_user'		=> array('UINT', 0),
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
					'item_id'		=> array('UINT', 0),
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'user_group'			=> array(
					'user_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'users'				=> array(
					'user_id'		=> array('UINT', null, 'auto_increment'),
				),
				$this->table_prefix . 'warnings'			=> array(
					'log_id'		=> array('UINT', 0),
					'user_id'		=> array('UINT', 0),
					'post_id'		=> array('UINT', 0),
				),
				$this->table_prefix . 'words'				=> array(
					'word_id'		=> array('UINT', null, 'auto_increment'),
				),
				$this->table_prefix . 'zebra'			=> array(
					'user_id'		=> array('UINT', 0),
					'zebra_id'		=> array('UINT', 0),
				),
			),
		);
	}
}
