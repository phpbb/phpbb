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

class release_3_0_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.0.0', '>=');
	}

	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'attachments'	=> array(
					'COLUMNS'	=> array(
						'attach_id'	=> array('UINT', NULL, 'auto_increment'),
						'post_msg_id'	=> array('UINT', 0),
						'topic_id'	=> array('UINT', 0),
						'in_message'	=> array('BOOL', 0),
						'poster_id'	=> array('UINT', 0),
						'is_orphan'	=> array('BOOL', 1),
						'physical_filename'	=> array('VCHAR', ''),
						'real_filename'	=> array('VCHAR', ''),
						'download_count'	=> array('UINT', 0),
						'attach_comment'	=> array('TEXT_UNI', ''),
						'extension'	=> array('VCHAR:100', ''),
						'mimetype'	=> array('VCHAR:100', ''),
						'filesize'	=> array('UINT:20', 0),
						'filetime'	=> array('TIMESTAMP', 0),
						'thumbnail'	=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'attach_id',
					'KEYS'	=> array(
						'filetime'	=> array('INDEX', 'filetime'),
						'post_msg_id'	=> array('INDEX', 'post_msg_id'),
						'topic_id'	=> array('INDEX', 'topic_id'),
						'poster_id'	=> array('INDEX', 'poster_id'),
						'is_orphan'	=> array('INDEX', 'is_orphan'),
					),
				),

				$this->table_prefix . 'acl_groups'	=> array(
					'COLUMNS'	=> array(
						'group_id'	=> array('UINT', 0),
						'forum_id'	=> array('UINT', 0),
						'auth_option_id'	=> array('UINT', 0),
						'auth_role_id'	=> array('UINT', 0),
						'auth_setting'	=> array('TINT:2', 0),
					),
					'KEYS'	=> array(
						'group_id'	=> array('INDEX', 'group_id'),
						'auth_opt_id'	=> array('INDEX', 'auth_option_id'),
						'auth_role_id'	=> array('INDEX', 'auth_role_id'),
					),
				),

				$this->table_prefix . 'acl_options'	=> array(
					'COLUMNS'	=> array(
						'auth_option_id'	=> array('UINT', NULL, 'auto_increment'),
						'auth_option'	=> array('VCHAR:50', ''),
						'is_global'	=> array('BOOL', 0),
						'is_local'	=> array('BOOL', 0),
						'founder_only'	=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'auth_option_id',
					'KEYS'	=> array(
						'auth_option'	=> array('INDEX', 'auth_option'),
					),
				),

				$this->table_prefix . 'acl_roles'	=> array(
					'COLUMNS'	=> array(
						'role_id'	=> array('UINT', NULL, 'auto_increment'),
						'role_name'	=> array('VCHAR_UNI', ''),
						'role_description'	=> array('TEXT_UNI', ''),
						'role_type'	=> array('VCHAR:10', ''),
						'role_order'	=> array('USINT', 0),
					),
					'PRIMARY_KEY'	=> 'role_id',
					'KEYS'	=> array(
						'role_type'	=> array('INDEX', 'role_type'),
						'role_order'	=> array('INDEX', 'role_order'),
					),
				),

				$this->table_prefix . 'acl_roles_data'	=> array(
					'COLUMNS'	=> array(
						'role_id'	=> array('UINT', 0),
						'auth_option_id'	=> array('UINT', 0),
						'auth_setting'	=> array('TINT:2', 0),
					),
					'PRIMARY_KEY'	=> array('role_id', 'auth_option_id'),
					'KEYS'	=> array(
						'ath_op_id'	=> array('INDEX', 'auth_option_id'),
					),
				),

				$this->table_prefix . 'acl_users'	=> array(
					'COLUMNS'	=> array(
						'user_id'	=> array('UINT', 0),
						'forum_id'	=> array('UINT', 0),
						'auth_option_id'	=> array('UINT', 0),
						'auth_role_id'	=> array('UINT', 0),
						'auth_setting'	=> array('TINT:2', 0),
					),
					'KEYS'	=> array(
						'user_id'	=> array('INDEX', 'user_id'),
						'auth_option_id'	=> array('INDEX', 'auth_option_id'),
						'auth_role_id'	=> array('INDEX', 'auth_role_id'),
					),
				),

				$this->table_prefix . 'banlist'	=> array(
					'COLUMNS'	=> array(
						'ban_id'	=> array('UINT', NULL, 'auto_increment'),
						'ban_userid'	=> array('UINT', 0),
						'ban_ip'	=> array('VCHAR:40', ''),
						'ban_email'	=> array('VCHAR_UNI:100', ''),
						'ban_start'	=> array('TIMESTAMP', 0),
						'ban_end'	=> array('TIMESTAMP', 0),
						'ban_exclude'	=> array('BOOL', 0),
						'ban_reason'	=> array('VCHAR_UNI', ''),
						'ban_give_reason'	=> array('VCHAR_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'ban_id',
					'KEYS'	=> array(
						'ban_end'	=> array('INDEX', 'ban_end'),
						'ban_user'	=> array('INDEX', array('ban_userid', 'ban_exclude')),
						'ban_email'	=> array('INDEX', array('ban_email', 'ban_exclude')),
						'ban_ip'	=> array('INDEX', array('ban_ip', 'ban_exclude')),
					),
				),

				$this->table_prefix . 'bbcodes'	=> array(
					'COLUMNS'	=> array(
						'bbcode_id'	=> array('TINT:3', 0),
						'bbcode_tag'	=> array('VCHAR:16', ''),
						'bbcode_helpline'	=> array('VCHAR_UNI', ''),
						'display_on_posting'	=> array('BOOL', 0),
						'bbcode_match'	=> array('TEXT_UNI', ''),
						'bbcode_tpl'	=> array('MTEXT_UNI', ''),
						'first_pass_match'	=> array('MTEXT_UNI', ''),
						'first_pass_replace'	=> array('MTEXT_UNI', ''),
						'second_pass_match'	=> array('MTEXT_UNI', ''),
						'second_pass_replace'	=> array('MTEXT_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'bbcode_id',
					'KEYS'	=> array(
						'display_on_post'	=> array('INDEX', 'display_on_posting'),
					),
				),

				$this->table_prefix . 'bookmarks'	=> array(
					'COLUMNS'	=> array(
						'topic_id'	=> array('UINT', 0),
						'user_id'	=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> array('topic_id', 'user_id'),
				),

				$this->table_prefix . 'bots'	=> array(
					'COLUMNS'	=> array(
						'bot_id'	=> array('UINT', NULL, 'auto_increment'),
						'bot_active'	=> array('BOOL', 1),
						'bot_name'	=> array('STEXT_UNI', ''),
						'user_id'	=> array('UINT', 0),
						'bot_agent'	=> array('VCHAR', ''),
						'bot_ip'	=> array('VCHAR', ''),
					),
					'PRIMARY_KEY'	=> 'bot_id',
					'KEYS'	=> array(
						'bot_active'	=> array('INDEX', 'bot_active'),
					),
				),

				$this->table_prefix . 'config'	=> array(
					'COLUMNS'	=> array(
						'config_name'	=> array('VCHAR', ''),
						'config_value'	=> array('VCHAR_UNI', ''),
						'is_dynamic'	=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'config_name',
					'KEYS'	=> array(
						'is_dynamic'	=> array('INDEX', 'is_dynamic'),
					),
				),

				$this->table_prefix . 'confirm'	=> array(
					'COLUMNS'	=> array(
						'confirm_id'	=> array('CHAR:32', ''),
						'session_id'	=> array('CHAR:32', ''),
						'confirm_type'	=> array('TINT:3', 0),
						'code'	=> array('VCHAR:8', ''),
						'seed'	=> array('UINT:10', 0),
					),
					'PRIMARY_KEY'	=> array('session_id', 'confirm_id'),
					'KEYS'	=> array(
						'confirm_type'	=> array('INDEX', 'confirm_type'),
					),
				),

				$this->table_prefix . 'disallow'	=> array(
					'COLUMNS'	=> array(
						'disallow_id'	=> array('UINT', NULL, 'auto_increment'),
						'disallow_username'	=> array('VCHAR_UNI:255', ''),
					),
					'PRIMARY_KEY'	=> 'disallow_id',
				),

				$this->table_prefix . 'drafts'	=> array(
					'COLUMNS'	=> array(
						'draft_id'	=> array('UINT', NULL, 'auto_increment'),
						'user_id'	=> array('UINT', 0),
						'topic_id'	=> array('UINT', 0),
						'forum_id'	=> array('UINT', 0),
						'save_time'	=> array('TIMESTAMP', 0),
						'draft_subject'	=> array('XSTEXT_UNI', ''),
						'draft_message'	=> array('MTEXT_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'draft_id',
					'KEYS'	=> array(
						'save_time'	=> array('INDEX', 'save_time'),
					),
				),

				$this->table_prefix . 'extensions'	=> array(
					'COLUMNS'	=> array(
						'extension_id'	=> array('UINT', NULL, 'auto_increment'),
						'group_id'	=> array('UINT', 0),
						'extension'	=> array('VCHAR:100', ''),
					),
					'PRIMARY_KEY'	=> 'extension_id',
				),

				$this->table_prefix . 'extension_groups'	=> array(
					'COLUMNS'	=> array(
						'group_id'	=> array('UINT', NULL, 'auto_increment'),
						'group_name'	=> array('VCHAR_UNI', ''),
						'cat_id'	=> array('TINT:2', 0),
						'allow_group'	=> array('BOOL', 0),
						'download_mode'	=> array('BOOL', 1),
						'upload_icon'	=> array('VCHAR', ''),
						'max_filesize'	=> array('UINT:20', 0),
						'allowed_forums'	=> array('TEXT', ''),
						'allow_in_pm'	=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'group_id',
				),

				$this->table_prefix . 'forums'	=> array(
					'COLUMNS'	=> array(
						'forum_id'	=> array('UINT', NULL, 'auto_increment'),
						'parent_id'	=> array('UINT', 0),
						'left_id'	=> array('UINT', 0),
						'right_id'	=> array('UINT', 0),
						'forum_parents'	=> array('MTEXT', ''),
						'forum_name'	=> array('STEXT_UNI', ''),
						'forum_desc'	=> array('TEXT_UNI', ''),
						'forum_desc_bitfield'	=> array('VCHAR:255', ''),
						'forum_desc_options'	=> array('UINT:11', 7),
						'forum_desc_uid'	=> array('VCHAR:8', ''),
						'forum_link'	=> array('VCHAR_UNI', ''),
						'forum_password'	=> array('VCHAR_UNI:40', ''),
						'forum_style'	=> array('USINT', 0),
						'forum_image'	=> array('VCHAR', ''),
						'forum_rules'	=> array('TEXT_UNI', ''),
						'forum_rules_link'	=> array('VCHAR_UNI', ''),
						'forum_rules_bitfield'	=> array('VCHAR:255', ''),
						'forum_rules_options'	=> array('UINT:11', 7),
						'forum_rules_uid'	=> array('VCHAR:8', ''),
						'forum_topics_per_page'	=> array('TINT:4', 0),
						'forum_type'	=> array('TINT:4', 0),
						'forum_status'	=> array('TINT:4', 0),
						'forum_posts'	=> array('UINT', 0),
						'forum_topics'	=> array('UINT', 0),
						'forum_topics_real'	=> array('UINT', 0),
						'forum_last_post_id'	=> array('UINT', 0),
						'forum_last_poster_id'	=> array('UINT', 0),
						'forum_last_post_subject' => array('XSTEXT_UNI', ''),
						'forum_last_post_time'	=> array('TIMESTAMP', 0),
						'forum_last_poster_name'=> array('VCHAR_UNI', ''),
						'forum_last_poster_colour'=> array('VCHAR:6', ''),
						'forum_flags'	=> array('TINT:4', 32),
						'display_on_index'	=> array('BOOL', 1),
						'enable_indexing'	=> array('BOOL', 1),
						'enable_icons'	=> array('BOOL', 1),
						'enable_prune'	=> array('BOOL', 0),
						'prune_next'	=> array('TIMESTAMP', 0),
						'prune_days'	=> array('UINT', 0),
						'prune_viewed'	=> array('UINT', 0),
						'prune_freq'	=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'forum_id',
					'KEYS'	=> array(
						'left_right_id'	=> array('INDEX', array('left_id', 'right_id')),
						'forum_lastpost_id'	=> array('INDEX', 'forum_last_post_id'),
					),
				),

				$this->table_prefix . 'forums_access'	=> array(
					'COLUMNS'	=> array(
						'forum_id'	=> array('UINT', 0),
						'user_id'	=> array('UINT', 0),
						'session_id'	=> array('CHAR:32', ''),
					),
					'PRIMARY_KEY'	=> array('forum_id', 'user_id', 'session_id'),
				),

				$this->table_prefix . 'forums_track'	=> array(
					'COLUMNS'	=> array(
						'user_id'	=> array('UINT', 0),
						'forum_id'	=> array('UINT', 0),
						'mark_time'	=> array('TIMESTAMP', 0),
					),
					'PRIMARY_KEY'	=> array('user_id', 'forum_id'),
				),

				$this->table_prefix . 'forums_watch'	=> array(
					'COLUMNS'	=> array(
						'forum_id'	=> array('UINT', 0),
						'user_id'	=> array('UINT', 0),
						'notify_status'	=> array('BOOL', 0),
					),
					'KEYS'	=> array(
						'forum_id'	=> array('INDEX', 'forum_id'),
						'user_id'	=> array('INDEX', 'user_id'),
						'notify_stat'	=> array('INDEX', 'notify_status'),
					),
				),

				$this->table_prefix . 'groups'	=> array(
					'COLUMNS'	=> array(
						'group_id'	=> array('UINT', NULL, 'auto_increment'),
						'group_type'	=> array('TINT:4', 1),
						'group_founder_manage'	=> array('BOOL', 0),
						'group_name'	=> array('VCHAR_CI', ''),
						'group_desc'	=> array('TEXT_UNI', ''),
						'group_desc_bitfield'	=> array('VCHAR:255', ''),
						'group_desc_options'	=> array('UINT:11', 7),
						'group_desc_uid'	=> array('VCHAR:8', ''),
						'group_display'	=> array('BOOL', 0),
						'group_avatar'	=> array('VCHAR', ''),
						'group_avatar_type'	=> array('TINT:2', 0),
						'group_avatar_width'	=> array('USINT', 0),
						'group_avatar_height'	=> array('USINT', 0),
						'group_rank'	=> array('UINT', 0),
						'group_colour'	=> array('VCHAR:6', ''),
						'group_sig_chars'	=> array('UINT', 0),
						'group_receive_pm'	=> array('BOOL', 0),
						'group_message_limit'	=> array('UINT', 0),
						'group_legend'	=> array('BOOL', 1),
					),
					'PRIMARY_KEY'	=> 'group_id',
					'KEYS'	=> array(
						'group_legend'	=> array('INDEX', 'group_legend'),
					),
				),

				$this->table_prefix . 'icons'	=> array(
					'COLUMNS'	=> array(
						'icons_id'	=> array('UINT', NULL, 'auto_increment'),
						'icons_url'	=> array('VCHAR', ''),
						'icons_width'	=> array('TINT:4', 0),
						'icons_height'	=> array('TINT:4', 0),
						'icons_order'	=> array('UINT', 0),
						'display_on_posting'	=> array('BOOL', 1),
					),
					'PRIMARY_KEY'	=> 'icons_id',
					'KEYS'	=> array(
						'display_on_posting'	=> array('INDEX', 'display_on_posting'),
					),
				),

				$this->table_prefix . 'lang'	=> array(
					'COLUMNS'	=> array(
						'lang_id'	=> array('TINT:4', NULL, 'auto_increment'),
						'lang_iso'	=> array('VCHAR:30', ''),
						'lang_dir'	=> array('VCHAR:30', ''),
						'lang_english_name'	=> array('VCHAR_UNI:100', ''),
						'lang_local_name'	=> array('VCHAR_UNI:255', ''),
						'lang_author'	=> array('VCHAR_UNI:255', ''),
					),
					'PRIMARY_KEY'	=> 'lang_id',
					'KEYS'	=> array(
						'lang_iso'	=> array('INDEX', 'lang_iso'),
					),
				),

				$this->table_prefix . 'log'	=> array(
					'COLUMNS'	=> array(
						'log_id'	=> array('UINT', NULL, 'auto_increment'),
						'log_type'	=> array('TINT:4', 0),
						'user_id'	=> array('UINT', 0),
						'forum_id'	=> array('UINT', 0),
						'topic_id'	=> array('UINT', 0),
						'reportee_id'	=> array('UINT', 0),
						'log_ip'	=> array('VCHAR:40', ''),
						'log_time'	=> array('TIMESTAMP', 0),
						'log_operation'	=> array('TEXT_UNI', ''),
						'log_data'	=> array('MTEXT_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'log_id',
					'KEYS'	=> array(
						'log_type'	=> array('INDEX', 'log_type'),
						'forum_id'	=> array('INDEX', 'forum_id'),
						'topic_id'	=> array('INDEX', 'topic_id'),
						'reportee_id'	=> array('INDEX', 'reportee_id'),
						'user_id'	=> array('INDEX', 'user_id'),
					),
				),

				$this->table_prefix . 'moderator_cache'	=> array(
					'COLUMNS'	=> array(
						'forum_id'	=> array('UINT', 0),
						'user_id'	=> array('UINT', 0),
						'username'	=> array('VCHAR_UNI:255', ''),
						'group_id'	=> array('UINT', 0),
						'group_name'	=> array('VCHAR_UNI', ''),
						'display_on_index'	=> array('BOOL', 1),
					),
					'KEYS'	=> array(
						'disp_idx'	=> array('INDEX', 'display_on_index'),
						'forum_id'	=> array('INDEX', 'forum_id'),
					),
				),

				$this->table_prefix . 'modules'	=> array(
					'COLUMNS'	=> array(
						'module_id'	=> array('UINT', NULL, 'auto_increment'),
						'module_enabled'	=> array('BOOL', 1),
						'module_display'	=> array('BOOL', 1),
						'module_basename'	=> array('VCHAR', ''),
						'module_class'	=> array('VCHAR:10', ''),
						'parent_id'	=> array('UINT', 0),
						'left_id'	=> array('UINT', 0),
						'right_id'	=> array('UINT', 0),
						'module_langname'	=> array('VCHAR', ''),
						'module_mode'	=> array('VCHAR', ''),
						'module_auth'	=> array('VCHAR', ''),
					),
					'PRIMARY_KEY'	=> 'module_id',
					'KEYS'	=> array(
						'left_right_id'	=> array('INDEX', array('left_id', 'right_id')),
						'module_enabled'	=> array('INDEX', 'module_enabled'),
						'class_left_id'	=> array('INDEX', array('module_class', 'left_id')),
					),
				),

				$this->table_prefix . 'poll_options'	=> array(
					'COLUMNS'	=> array(
						'poll_option_id'	=> array('TINT:4', 0),
						'topic_id'	=> array('UINT', 0),
						'poll_option_text'	=> array('TEXT_UNI', ''),
						'poll_option_total'	=> array('UINT', 0),
					),
					'KEYS'	=> array(
						'poll_opt_id'	=> array('INDEX', 'poll_option_id'),
						'topic_id'	=> array('INDEX', 'topic_id'),
					),
				),

				$this->table_prefix . 'poll_votes'	=> array(
					'COLUMNS'	=> array(
						'topic_id'	=> array('UINT', 0),
						'poll_option_id'	=> array('TINT:4', 0),
						'vote_user_id'	=> array('UINT', 0),
						'vote_user_ip'	=> array('VCHAR:40', ''),
					),
					'KEYS'	=> array(
						'topic_id'	=> array('INDEX', 'topic_id'),
						'vote_user_id'	=> array('INDEX', 'vote_user_id'),
						'vote_user_ip'	=> array('INDEX', 'vote_user_ip'),
					),
				),

				$this->table_prefix . 'posts'	=> array(
					'COLUMNS'	=> array(
						'post_id'	=> array('UINT', NULL, 'auto_increment'),
						'topic_id'	=> array('UINT', 0),
						'forum_id'	=> array('UINT', 0),
						'poster_id'	=> array('UINT', 0),
						'icon_id'	=> array('UINT', 0),
						'poster_ip'	=> array('VCHAR:40', ''),
						'post_time'	=> array('TIMESTAMP', 0),
						'post_approved'	=> array('BOOL', 1),
						'post_reported'	=> array('BOOL', 0),
						'enable_bbcode'	=> array('BOOL', 1),
						'enable_smilies'	=> array('BOOL', 1),
						'enable_magic_url'	=> array('BOOL', 1),
						'enable_sig'	=> array('BOOL', 1),
						'post_username'	=> array('VCHAR_UNI:255', ''),
						'post_subject'	=> array('XSTEXT_UNI', '', 'true_sort'),
						'post_text'	=> array('MTEXT_UNI', ''),
						'post_checksum'	=> array('VCHAR:32', ''),
						'post_attachment'	=> array('BOOL', 0),
						'bbcode_bitfield'	=> array('VCHAR:255', ''),
						'bbcode_uid'	=> array('VCHAR:8', ''),
						'post_postcount'	=> array('BOOL', 1),
						'post_edit_time'	=> array('TIMESTAMP', 0),
						'post_edit_reason'	=> array('STEXT_UNI', ''),
						'post_edit_user'	=> array('UINT', 0),
						'post_edit_count'	=> array('USINT', 0),
						'post_edit_locked'	=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'post_id',
					'KEYS'	=> array(
						'forum_id'	=> array('INDEX', 'forum_id'),
						'topic_id'	=> array('INDEX', 'topic_id'),
						'poster_ip'	=> array('INDEX', 'poster_ip'),
						'poster_id'	=> array('INDEX', 'poster_id'),
						'post_approved'	=> array('INDEX', 'post_approved'),
						'tid_post_time'	=> array('INDEX', array('topic_id', 'post_time')),
					),
				),

				$this->table_prefix . 'privmsgs'	=> array(
					'COLUMNS'	=> array(
						'msg_id'	=> array('UINT', NULL, 'auto_increment'),
						'root_level'	=> array('UINT', 0),
						'author_id'	=> array('UINT', 0),
						'icon_id'	=> array('UINT', 0),
						'author_ip'	=> array('VCHAR:40', ''),
						'message_time'	=> array('TIMESTAMP', 0),
						'enable_bbcode'	=> array('BOOL', 1),
						'enable_smilies'	=> array('BOOL', 1),
						'enable_magic_url'	=> array('BOOL', 1),
						'enable_sig'	=> array('BOOL', 1),
						'message_subject'	=> array('XSTEXT_UNI', ''),
						'message_text'	=> array('MTEXT_UNI', ''),
						'message_edit_reason'	=> array('STEXT_UNI', ''),
						'message_edit_user'	=> array('UINT', 0),
						'message_attachment'	=> array('BOOL', 0),
						'bbcode_bitfield'	=> array('VCHAR:255', ''),
						'bbcode_uid'	=> array('VCHAR:8', ''),
						'message_edit_time'	=> array('TIMESTAMP', 0),
						'message_edit_count'	=> array('USINT', 0),
						'to_address'	=> array('TEXT_UNI', ''),
						'bcc_address'	=> array('TEXT_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'msg_id',
					'KEYS'	=> array(
						'author_ip'	=> array('INDEX', 'author_ip'),
						'message_time'	=> array('INDEX', 'message_time'),
						'author_id'	=> array('INDEX', 'author_id'),
						'root_level'	=> array('INDEX', 'root_level'),
					),
				),

				$this->table_prefix . 'privmsgs_folder'	=> array(
					'COLUMNS'	=> array(
						'folder_id'	=> array('UINT', NULL, 'auto_increment'),
						'user_id'	=> array('UINT', 0),
						'folder_name'	=> array('VCHAR_UNI', ''),
						'pm_count'	=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'folder_id',
					'KEYS'	=> array(
						'user_id'	=> array('INDEX', 'user_id'),
					),
				),

				$this->table_prefix . 'privmsgs_rules'	=> array(
					'COLUMNS'	=> array(
						'rule_id'	=> array('UINT', NULL, 'auto_increment'),
						'user_id'	=> array('UINT', 0),
						'rule_check'	=> array('UINT', 0),
						'rule_connection'	=> array('UINT', 0),
						'rule_string'	=> array('VCHAR_UNI', ''),
						'rule_user_id'	=> array('UINT', 0),
						'rule_group_id'	=> array('UINT', 0),
						'rule_action'	=> array('UINT', 0),
						'rule_folder_id'	=> array('INT:11', 0),
					),
					'PRIMARY_KEY'	=> 'rule_id',
					'KEYS'	=> array(
						'user_id'	=> array('INDEX', 'user_id'),
					),
				),

				$this->table_prefix . 'privmsgs_to'	=> array(
					'COLUMNS'	=> array(
						'msg_id'	=> array('UINT', 0),
						'user_id'	=> array('UINT', 0),
						'author_id'	=> array('UINT', 0),
						'pm_deleted'	=> array('BOOL', 0),
						'pm_new'	=> array('BOOL', 1),
						'pm_unread'	=> array('BOOL', 1),
						'pm_replied'	=> array('BOOL', 0),
						'pm_marked'	=> array('BOOL', 0),
						'pm_forwarded'	=> array('BOOL', 0),
						'folder_id'	=> array('INT:11', 0),
					),
					'KEYS'	=> array(
						'msg_id'	=> array('INDEX', 'msg_id'),
						'author_id'	=> array('INDEX', 'author_id'),
						'usr_flder_id'	=> array('INDEX', array('user_id', 'folder_id')),
					),
				),

				$this->table_prefix . 'profile_fields'	=> array(
					'COLUMNS'	=> array(
						'field_id'	=> array('UINT', NULL, 'auto_increment'),
						'field_name'	=> array('VCHAR_UNI', ''),
						'field_type'	=> array('TINT:4', 0),
						'field_ident'	=> array('VCHAR:20', ''),
						'field_length'	=> array('VCHAR:20', ''),
						'field_minlen'	=> array('VCHAR', ''),
						'field_maxlen'	=> array('VCHAR', ''),
						'field_novalue'	=> array('VCHAR_UNI', ''),
						'field_default_value'	=> array('VCHAR_UNI', ''),
						'field_validation'	=> array('VCHAR_UNI:20', ''),
						'field_required'	=> array('BOOL', 0),
						'field_show_on_reg'	=> array('BOOL', 0),
						'field_hide'	=> array('BOOL', 0),
						'field_no_view'	=> array('BOOL', 0),
						'field_active'	=> array('BOOL', 0),
						'field_order'	=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'field_id',
					'KEYS'	=> array(
						'fld_type'	=> array('INDEX', 'field_type'),
						'fld_ordr'	=> array('INDEX', 'field_order'),
					),
				),

				$this->table_prefix . 'profile_fields_data'	=> array(
					'COLUMNS'	=> array(
						'user_id'	=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'user_id',
				),

				$this->table_prefix . 'profile_fields_lang'	=> array(
					'COLUMNS'	=> array(
						'field_id'	=> array('UINT', 0),
						'lang_id'	=> array('UINT', 0),
						'option_id'	=> array('UINT', 0),
						'field_type'	=> array('TINT:4', 0),
						'lang_value'	=> array('VCHAR_UNI', ''),
					),
					'PRIMARY_KEY'	=> array('field_id', 'lang_id', 'option_id'),
				),

				$this->table_prefix . 'profile_lang'	=> array(
					'COLUMNS'	=> array(
						'field_id'	=> array('UINT', 0),
						'lang_id'	=> array('UINT', 0),
						'lang_name'	=> array('VCHAR_UNI', ''),
						'lang_explain'	=> array('TEXT_UNI', ''),
						'lang_default_value'	=> array('VCHAR_UNI', ''),
					),
					'PRIMARY_KEY'	=> array('field_id', 'lang_id'),
				),

				$this->table_prefix . 'ranks'	=> array(
					'COLUMNS'	=> array(
						'rank_id'	=> array('UINT', NULL, 'auto_increment'),
						'rank_title'	=> array('VCHAR_UNI', ''),
						'rank_min'	=> array('UINT', 0),
						'rank_special'	=> array('BOOL', 0),
						'rank_image'	=> array('VCHAR', ''),
					),
					'PRIMARY_KEY'	=> 'rank_id',
				),

				$this->table_prefix . 'reports'	=> array(
					'COLUMNS'	=> array(
						'report_id'	=> array('UINT', NULL, 'auto_increment'),
						'reason_id'	=> array('USINT', 0),
						'post_id'	=> array('UINT', 0),
						'user_id'	=> array('UINT', 0),
						'user_notify'	=> array('BOOL', 0),
						'report_closed'	=> array('BOOL', 0),
						'report_time'	=> array('TIMESTAMP', 0),
						'report_text'	=> array('MTEXT_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'report_id',
				),

				$this->table_prefix . 'reports_reasons'	=> array(
					'COLUMNS'	=> array(
						'reason_id'	=> array('USINT', NULL, 'auto_increment'),
						'reason_title'	=> array('VCHAR_UNI', ''),
						'reason_description'	=> array('MTEXT_UNI', ''),
						'reason_order'	=> array('USINT', 0),
					),
					'PRIMARY_KEY'	=> 'reason_id',
				),

				$this->table_prefix . 'search_results'	=> array(
					'COLUMNS'	=> array(
						'search_key'	=> array('VCHAR:32', ''),
						'search_time'	=> array('TIMESTAMP', 0),
						'search_keywords'	=> array('MTEXT_UNI', ''),
						'search_authors'	=> array('MTEXT', ''),
					),
					'PRIMARY_KEY'	=> 'search_key',
				),

				$this->table_prefix . 'search_wordlist'	=> array(
					'COLUMNS'	=> array(
						'word_id'	=> array('UINT', NULL, 'auto_increment'),
						'word_text'	=> array('VCHAR_UNI', ''),
						'word_common'	=> array('BOOL', 0),
						'word_count'	=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'word_id',
					'KEYS'	=> array(
						'wrd_txt'	=> array('UNIQUE', 'word_text'),
						'wrd_cnt'	=> array('INDEX', 'word_count'),
					),
				),

				$this->table_prefix . 'search_wordmatch'	=> array(
					'COLUMNS'	=> array(
						'post_id'	=> array('UINT', 0),
						'word_id'	=> array('UINT', 0),
						'title_match'	=> array('BOOL', 0),
					),
					'KEYS'	=> array(
						'unq_mtch'	=> array('UNIQUE', array('word_id', 'post_id', 'title_match')),
						'word_id'	=> array('INDEX', 'word_id'),
						'post_id'	=> array('INDEX', 'post_id'),
					),
				),

				$this->table_prefix . 'sessions'	=> array(
					'COLUMNS'	=> array(
						'session_id'	=> array('CHAR:32', ''),
						'session_user_id'	=> array('UINT', 0),
						'session_last_visit'	=> array('TIMESTAMP', 0),
						'session_start'	=> array('TIMESTAMP', 0),
						'session_time'	=> array('TIMESTAMP', 0),
						'session_ip'	=> array('VCHAR:40', ''),
						'session_browser'	=> array('VCHAR:150', ''),
						'session_forwarded_for'	=> array('VCHAR:255', ''),
						'session_page'	=> array('VCHAR_UNI', ''),
						'session_viewonline'	=> array('BOOL', 1),
						'session_autologin'	=> array('BOOL', 0),
						'session_admin'	=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'session_id',
					'KEYS'	=> array(
						'session_time'	=> array('INDEX', 'session_time'),
						'session_user_id'	=> array('INDEX', 'session_user_id'),
					),
				),

				$this->table_prefix . 'sessions_keys'	=> array(
					'COLUMNS'	=> array(
						'key_id'	=> array('CHAR:32', ''),
						'user_id'	=> array('UINT', 0),
						'last_ip'	=> array('VCHAR:40', ''),
						'last_login'	=> array('TIMESTAMP', 0),
					),
					'PRIMARY_KEY'	=> array('key_id', 'user_id'),
					'KEYS'	=> array(
						'last_login'	=> array('INDEX', 'last_login'),
					),
				),

				$this->table_prefix . 'sitelist'	=> array(
					'COLUMNS'	=> array(
						'site_id'	=> array('UINT', NULL, 'auto_increment'),
						'site_ip'	=> array('VCHAR:40', ''),
						'site_hostname'	=> array('VCHAR', ''),
						'ip_exclude'	=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'site_id',
				),

				$this->table_prefix . 'smilies'	=> array(
					'COLUMNS'	=> array(
						'smiley_id'	=> array('UINT', NULL, 'auto_increment'),
// We may want to set 'code' to VCHAR:50 or check if unicode support is possible... at the moment only ASCII characters are allowed.
						'code'	=> array('VCHAR_UNI:50', ''),
						'emotion'	=> array('VCHAR_UNI:50', ''),
						'smiley_url'	=> array('VCHAR:50', ''),
						'smiley_width'	=> array('USINT', 0),
						'smiley_height'	=> array('USINT', 0),
						'smiley_order'	=> array('UINT', 0),
						'display_on_posting'=> array('BOOL', 1),
					),
					'PRIMARY_KEY'	=> 'smiley_id',
					'KEYS'	=> array(
						'display_on_post'	=> array('INDEX', 'display_on_posting'),
					),
				),

				$this->table_prefix . 'styles'	=> array(
					'COLUMNS'	=> array(
						'style_id'	=> array('USINT', NULL, 'auto_increment'),
						'style_name'	=> array('VCHAR_UNI:255', ''),
						'style_copyright'	=> array('VCHAR_UNI', ''),
						'style_active'	=> array('BOOL', 1),
						'template_id'	=> array('USINT', 0),
						'theme_id'	=> array('USINT', 0),
						'imageset_id'	=> array('USINT', 0),
					),
					'PRIMARY_KEY'	=> 'style_id',
					'KEYS'	=> array(
						'style_name'	=> array('UNIQUE', 'style_name'),
						'template_id'	=> array('INDEX', 'template_id'),
						'theme_id'	=> array('INDEX', 'theme_id'),
						'imageset_id'	=> array('INDEX', 'imageset_id'),
					),
				),

				$this->table_prefix . 'styles_template'	=> array(
					'COLUMNS'	=> array(
						'template_id'	=> array('USINT', NULL, 'auto_increment'),
						'template_name'	=> array('VCHAR_UNI:255', ''),
						'template_copyright'	=> array('VCHAR_UNI', ''),
						'template_path'	=> array('VCHAR:100', ''),
						'bbcode_bitfield'	=> array('VCHAR:255', 'kNg='),
						'template_storedb'	=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'template_id',
					'KEYS'	=> array(
						'tmplte_nm'	=> array('UNIQUE', 'template_name'),
					),
				),

				$this->table_prefix . 'styles_template_data'	=> array(
					'COLUMNS'	=> array(
						'template_id'	=> array('USINT', 0),
						'template_filename'	=> array('VCHAR:100', ''),
						'template_included'	=> array('TEXT', ''),
						'template_mtime'	=> array('TIMESTAMP', 0),
						'template_data'	=> array('MTEXT_UNI', ''),
					),
					'KEYS'	=> array(
						'tid'	=> array('INDEX', 'template_id'),
						'tfn'	=> array('INDEX', 'template_filename'),
					),
				),

				$this->table_prefix . 'styles_theme'	=> array(
					'COLUMNS'	=> array(
						'theme_id'	=> array('USINT', NULL, 'auto_increment'),
						'theme_name'	=> array('VCHAR_UNI:255', ''),
						'theme_copyright'	=> array('VCHAR_UNI', ''),
						'theme_path'	=> array('VCHAR:100', ''),
						'theme_storedb'	=> array('BOOL', 0),
						'theme_mtime'	=> array('TIMESTAMP', 0),
						'theme_data'	=> array('MTEXT_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'theme_id',
					'KEYS'	=> array(
						'theme_name'	=> array('UNIQUE', 'theme_name'),
					),
				),

				$this->table_prefix . 'styles_imageset'	=> array(
					'COLUMNS'	=> array(
						'imageset_id'	=> array('USINT', NULL, 'auto_increment'),
						'imageset_name'	=> array('VCHAR_UNI:255', ''),
						'imageset_copyright'	=> array('VCHAR_UNI', ''),
						'imageset_path'	=> array('VCHAR:100', ''),
					),
					'PRIMARY_KEY'	=> 'imageset_id',
					'KEYS'	=> array(
						'imgset_nm'	=> array('UNIQUE', 'imageset_name'),
					),
				),

				$this->table_prefix . 'styles_imageset_data'	=> array(
					'COLUMNS'	=> array(
						'image_id'	=> array('USINT', NULL, 'auto_increment'),
						'image_name'	=> array('VCHAR:200', ''),
						'image_filename'	=> array('VCHAR:200', ''),
						'image_lang'	=> array('VCHAR:30', ''),
						'image_height'	=> array('USINT', 0),
						'image_width'	=> array('USINT', 0),
						'imageset_id'	=> array('USINT', 0),
					),
					'PRIMARY_KEY'	=> 'image_id',
					'KEYS'	=> array(
						'i_d'	=> array('INDEX', 'imageset_id'),
					),
				),

				$this->table_prefix . 'topics'	=> array(
					'COLUMNS'	=> array(
						'topic_id'	=> array('UINT', NULL, 'auto_increment'),
						'forum_id'	=> array('UINT', 0),
						'icon_id'	=> array('UINT', 0),
						'topic_attachment'	=> array('BOOL', 0),
						'topic_approved'	=> array('BOOL', 1),
						'topic_reported'	=> array('BOOL', 0),
						'topic_title'	=> array('XSTEXT_UNI', '', 'true_sort'),
						'topic_poster'	=> array('UINT', 0),
						'topic_time'	=> array('TIMESTAMP', 0),
						'topic_time_limit'	=> array('TIMESTAMP', 0),
						'topic_views'	=> array('UINT', 0),
						'topic_replies'	=> array('UINT', 0),
						'topic_replies_real'	=> array('UINT', 0),
						'topic_status'	=> array('TINT:3', 0),
						'topic_type'	=> array('TINT:3', 0),
						'topic_first_post_id'	=> array('UINT', 0),
						'topic_first_poster_name'	=> array('VCHAR_UNI', ''),
						'topic_first_poster_colour'	=> array('VCHAR:6', ''),
						'topic_last_post_id'	=> array('UINT', 0),
						'topic_last_poster_id'	=> array('UINT', 0),
						'topic_last_poster_name'	=> array('VCHAR_UNI', ''),
						'topic_last_poster_colour'	=> array('VCHAR:6', ''),
						'topic_last_post_subject'	=> array('XSTEXT_UNI', ''),
						'topic_last_post_time'	=> array('TIMESTAMP', 0),
						'topic_last_view_time'	=> array('TIMESTAMP', 0),
						'topic_moved_id'	=> array('UINT', 0),
						'topic_bumped'	=> array('BOOL', 0),
						'topic_bumper'	=> array('UINT', 0),
						'poll_title'	=> array('STEXT_UNI', ''),
						'poll_start'	=> array('TIMESTAMP', 0),
						'poll_length'	=> array('TIMESTAMP', 0),
						'poll_max_options'	=> array('TINT:4', 1),
						'poll_last_vote'	=> array('TIMESTAMP', 0),
						'poll_vote_change'	=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'topic_id',
					'KEYS'	=> array(
						'forum_id'	=> array('INDEX', 'forum_id'),
						'forum_id_type'	=> array('INDEX', array('forum_id', 'topic_type')),
						'last_post_time'	=> array('INDEX', 'topic_last_post_time'),
						'topic_approved'	=> array('INDEX', 'topic_approved'),
						'forum_appr_last'	=> array('INDEX', array('forum_id', 'topic_approved', 'topic_last_post_id')),
						'fid_time_moved'	=> array('INDEX', array('forum_id', 'topic_last_post_time', 'topic_moved_id')),
					),
				),

				$this->table_prefix . 'topics_track'	=> array(
					'COLUMNS'	=> array(
						'user_id'	=> array('UINT', 0),
						'topic_id'	=> array('UINT', 0),
						'forum_id'	=> array('UINT', 0),
						'mark_time'	=> array('TIMESTAMP', 0),
					),
					'PRIMARY_KEY'	=> array('user_id', 'topic_id'),
					'KEYS'	=> array(
						'forum_id'	=> array('INDEX', 'forum_id'),
					),
				),

				$this->table_prefix . 'topics_posted'	=> array(
					'COLUMNS'	=> array(
						'user_id'	=> array('UINT', 0),
						'topic_id'	=> array('UINT', 0),
						'topic_posted'	=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> array('user_id', 'topic_id'),
				),

				$this->table_prefix . 'topics_watch'	=> array(
					'COLUMNS'	=> array(
						'topic_id'	=> array('UINT', 0),
						'user_id'	=> array('UINT', 0),
						'notify_status'	=> array('BOOL', 0),
					),
					'KEYS'	=> array(
						'topic_id'	=> array('INDEX', 'topic_id'),
						'user_id'	=> array('INDEX', 'user_id'),
						'notify_stat'	=> array('INDEX', 'notify_status'),
					),
				),

				$this->table_prefix . 'user_group'	=> array(
					'COLUMNS'	=> array(
						'group_id'	=> array('UINT', 0),
						'user_id'	=> array('UINT', 0),
						'group_leader'	=> array('BOOL', 0),
						'user_pending'	=> array('BOOL', 1),
					),
					'KEYS'	=> array(
						'group_id'	=> array('INDEX', 'group_id'),
						'user_id'	=> array('INDEX', 'user_id'),
						'group_leader'	=> array('INDEX', 'group_leader'),
					),
				),

				$this->table_prefix . 'users'	=> array(
					'COLUMNS'	=> array(
						'user_id'	=> array('UINT', NULL, 'auto_increment'),
						'user_type'	=> array('TINT:2', 0),
						'group_id'	=> array('UINT', 3),
						'user_permissions'	=> array('MTEXT', ''),
						'user_perm_from'	=> array('UINT', 0),
						'user_ip'	=> array('VCHAR:40', ''),
						'user_regdate'	=> array('TIMESTAMP', 0),
						'username'	=> array('VCHAR_CI', ''),
						'username_clean'	=> array('VCHAR_CI', ''),
						'user_password'	=> array('VCHAR_UNI:40', ''),
						'user_passchg'	=> array('TIMESTAMP', 0),
						'user_pass_convert'	=> array('BOOL', 0),
						'user_email'	=> array('VCHAR_UNI:100', ''),
						'user_email_hash'	=> array('BINT', 0),
						'user_birthday'	=> array('VCHAR:10', ''),
						'user_lastvisit'	=> array('TIMESTAMP', 0),
						'user_lastmark'	=> array('TIMESTAMP', 0),
						'user_lastpost_time'	=> array('TIMESTAMP', 0),
						'user_lastpage'	=> array('VCHAR_UNI:200', ''),
						'user_last_confirm_key'	=> array('VCHAR:10', ''),
						'user_last_search'	=> array('TIMESTAMP', 0),
						'user_warnings'	=> array('TINT:4', 0),
						'user_last_warning'	=> array('TIMESTAMP', 0),
						'user_login_attempts'	=> array('TINT:4', 0),
						'user_inactive_reason'	=> array('TINT:2', 0),
						'user_inactive_time'	=> array('TIMESTAMP', 0),
						'user_posts'	=> array('UINT', 0),
						'user_lang'	=> array('VCHAR:30', ''),
						'user_timezone'	=> array('DECIMAL', 0),
						'user_dst'	=> array('BOOL', 0),
						'user_dateformat'	=> array('VCHAR_UNI:30', 'd M Y H:i'),
						'user_style'	=> array('USINT', 0),
						'user_rank'	=> array('UINT', 0),
						'user_colour'	=> array('VCHAR:6', ''),
						'user_new_privmsg'	=> array('INT:4', 0),
						'user_unread_privmsg'	=> array('INT:4', 0),
						'user_last_privmsg'	=> array('TIMESTAMP', 0),
						'user_message_rules'	=> array('BOOL', 0),
						'user_full_folder'	=> array('INT:11', -3),
						'user_emailtime'	=> array('TIMESTAMP', 0),
						'user_topic_show_days'	=> array('USINT', 0),
						'user_topic_sortby_type'	=> array('VCHAR:1', 't'),
						'user_topic_sortby_dir'	=> array('VCHAR:1', 'd'),
						'user_post_show_days'	=> array('USINT', 0),
						'user_post_sortby_type'	=> array('VCHAR:1', 't'),
						'user_post_sortby_dir'	=> array('VCHAR:1', 'a'),
						'user_notify'	=> array('BOOL', 0),
						'user_notify_pm'	=> array('BOOL', 1),
						'user_notify_type'	=> array('TINT:4', 0),
						'user_allow_pm'	=> array('BOOL', 1),
						'user_allow_viewonline'	=> array('BOOL', 1),
						'user_allow_viewemail'	=> array('BOOL', 1),
						'user_allow_massemail'	=> array('BOOL', 1),
						'user_options'	=> array('UINT:11', 895),
						'user_avatar'	=> array('VCHAR', ''),
						'user_avatar_type'	=> array('TINT:2', 0),
						'user_avatar_width'	=> array('USINT', 0),
						'user_avatar_height'	=> array('USINT', 0),
						'user_sig'	=> array('MTEXT_UNI', ''),
						'user_sig_bbcode_uid'	=> array('VCHAR:8', ''),
						'user_sig_bbcode_bitfield'	=> array('VCHAR:255', ''),
						'user_from'	=> array('VCHAR_UNI:100', ''),
						'user_icq'	=> array('VCHAR:15', ''),
						'user_aim'	=> array('VCHAR_UNI', ''),
						'user_yim'	=> array('VCHAR_UNI', ''),
						'user_msnm'	=> array('VCHAR_UNI', ''),
						'user_jabber'	=> array('VCHAR_UNI', ''),
						'user_website'	=> array('VCHAR_UNI:200', ''),
						'user_occ'	=> array('TEXT_UNI', ''),
						'user_interests'	=> array('TEXT_UNI', ''),
						'user_actkey'	=> array('VCHAR:32', ''),
						'user_newpasswd'	=> array('VCHAR_UNI:40', ''),
						'user_form_salt'	=> array('VCHAR_UNI:32', ''),

					),
					'PRIMARY_KEY'	=> 'user_id',
					'KEYS'	=> array(
						'user_birthday'	=> array('INDEX', 'user_birthday'),
						'user_email_hash'	=> array('INDEX', 'user_email_hash'),
						'user_type'	=> array('INDEX', 'user_type'),
						'username_clean'	=> array('UNIQUE', 'username_clean'),
					),
				),

				$this->table_prefix . 'warnings'	=> array(
					'COLUMNS'	=> array(
						'warning_id'	=> array('UINT', NULL, 'auto_increment'),
						'user_id'	=> array('UINT', 0),
						'post_id'	=> array('UINT', 0),
						'log_id'	=> array('UINT', 0),
						'warning_time'	=> array('TIMESTAMP', 0),
					),
					'PRIMARY_KEY'	=> 'warning_id',
				),

				$this->table_prefix . 'words'	=> array(
					'COLUMNS'	=> array(
						'word_id'	=> array('UINT', NULL, 'auto_increment'),
						'word'	=> array('VCHAR_UNI', ''),
						'replacement'	=> array('VCHAR_UNI', ''),
					),
					'PRIMARY_KEY'	=> 'word_id',
				),

				$this->table_prefix . 'zebra'	=> array(
					'COLUMNS'	=> array(
						'user_id'	=> array('UINT', 0),
						'zebra_id'	=> array('UINT', 0),
						'friend'	=> array('BOOL', 0),
						'foe'	=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> array('user_id', 'zebra_id'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'attachments',
				$this->table_prefix . 'acl_groups',
				$this->table_prefix . 'acl_options',
				$this->table_prefix . 'acl_roles',
				$this->table_prefix . 'acl_roles_data',
				$this->table_prefix . 'acl_users',
				$this->table_prefix . 'banlist',
				$this->table_prefix . 'bbcodes',
				$this->table_prefix . 'bookmarks',
				$this->table_prefix . 'bots',
				$this->table_prefix . 'config',
				$this->table_prefix . 'confirm',
				$this->table_prefix . 'disallow',
				$this->table_prefix . 'drafts',
				$this->table_prefix . 'extensions',
				$this->table_prefix . 'extension_groups',
				$this->table_prefix . 'forums',
				$this->table_prefix . 'forums_access',
				$this->table_prefix . 'forums_track',
				$this->table_prefix . 'forums_watch',
				$this->table_prefix . 'groups',
				$this->table_prefix . 'icons',
				$this->table_prefix . 'lang',
				$this->table_prefix . 'log',
				$this->table_prefix . 'moderator_cache',
				$this->table_prefix . 'modules',
				$this->table_prefix . 'poll_options',
				$this->table_prefix . 'poll_votes',
				$this->table_prefix . 'posts',
				$this->table_prefix . 'privmsgs',
				$this->table_prefix . 'privmsgs_folder',
				$this->table_prefix . 'privmsgs_rules',
				$this->table_prefix . 'privmsgs_to',
				$this->table_prefix . 'profile_fields',
				$this->table_prefix . 'profile_fields_data',
				$this->table_prefix . 'profile_fields_lang',
				$this->table_prefix . 'profile_lang',
				$this->table_prefix . 'ranks',
				$this->table_prefix . 'reports',
				$this->table_prefix . 'reports_reasons',
				$this->table_prefix . 'search_results',
				$this->table_prefix . 'search_wordlist',
				$this->table_prefix . 'search_wordmatch',
				$this->table_prefix . 'sessions',
				$this->table_prefix . 'sessions_keys',
				$this->table_prefix . 'sitelist',
				$this->table_prefix . 'smilies',
				$this->table_prefix . 'styles',
				$this->table_prefix . 'styles_template',
				$this->table_prefix . 'styles_template_data',
				$this->table_prefix . 'styles_theme',
				$this->table_prefix . 'styles_imageset',
				$this->table_prefix . 'styles_imageset_data',
				$this->table_prefix . 'topics',
				$this->table_prefix . 'topics_track',
				$this->table_prefix . 'topics_posted',
				$this->table_prefix . 'topics_watch',
				$this->table_prefix . 'user_group',
				$this->table_prefix . 'users',
				$this->table_prefix . 'warnings',
				$this->table_prefix . 'words',
				$this->table_prefix . 'zebra',
			),
		);
	}
}
