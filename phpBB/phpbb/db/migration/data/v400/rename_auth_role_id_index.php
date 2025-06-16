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

namespace phpbb\db\migration\data\v400;

use phpbb\db\migration\migration;

class rename_auth_role_id_index extends migration
{
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
		];
	}

	public function update_schema()
	{
		return [
			'rename_index' => [
				$this->table_prefix . 'acl_groups' => [
					'auth_role_id' => 'aclgrps_auth_role_id',
					'group_id' => 'aclgrps_group_id',
				],
				$this->table_prefix . 'acl_users' => [
					'auth_role_id' => 'aclusrs_auth_role_id',
					'user_id' => 'aclusrs_user_id',
				],
				$this->table_prefix . 'attachments' => [
					'poster_id' => 'attchmnts_poster_id',
					'topic_id' => 'attchmnts_topic_id',
				],
				$this->table_prefix . 'bbcodes' => [
					'display_on_post' => 'bbcds_display_on_post',
				],
				$this->table_prefix . 'forums' => [
					'left_right_id' => 'frms_left_right_id',
				],
				$this->table_prefix . 'forums_watch' => [
					'forum_id' => 'frmswtch_forum_id',
					'notify_stat' => 'frmswtch_notify_stat',
					'user_id' => 'frmswtch_user_id',
				],
				$this->table_prefix . 'log' => [
					'forum_id' => 'log_forum_id',
					'topic_id' => 'log_topic_id',
					'user_id' => 'log_user_id',
				],
				$this->table_prefix . 'login_attempts' => [
					'user_id' => 'lgnatmpts_user_id',
				],
				$this->table_prefix . 'moderator_cache' => [
					'forum_id' => 'mdrtrcch_forum_id',
				],
				$this->table_prefix . 'modules' => [
					'left_right_id' => 'mdls_left_right_id',
				],
				$this->table_prefix . 'oauth_states' => [
					'provider' => 'oauthsts_provider',
					'user_id' => 'oauthsts_user_id',
				],
				$this->table_prefix . 'oauth_tokens' => [
					'provider' => 'oauthtkns_provider',
					'user_id' => 'oauthtkns_user_id',
				],
				$this->table_prefix . 'poll_options' => [
					'topic_id' => 'pllopts_topic_id',
				],
				$this->table_prefix . 'poll_votes' => [
					'topic_id' => 'pllvts_topic_id',
				],
				$this->table_prefix . 'posts' => [
					'forum_id' => 'psts_forum_id',
					'poster_id' => 'psts_poster_id',
					'topic_id' => 'psts_topic_id',
				],
				$this->table_prefix . 'privmsgs' => [
					'author_id' => 'pms_author_id',
				],
				$this->table_prefix . 'privmsgs_folder' => [
					'user_id' => 'pmsfldr_user_id',
				],
				$this->table_prefix . 'privmsgs_rules' => [
					'user_id' => 'pmsrls_user_id',
				],
				$this->table_prefix . 'privmsgs_to' => [
					'author_id' => 'pmsto_author_id',
				],
				$this->table_prefix . 'reports' => [
					'post_id' => 'rprts_post_id',
				],
				$this->table_prefix . 'search_wordmatch' => [
					'post_id' => 'wrdmtch_post_id',
				],
				$this->table_prefix . 'smilies' => [
					'display_on_post' => 'smls_display_on_post',
				],
				$this->table_prefix . 'topics' => [
					'forum_id' => 'tpcs_forum_id',
				],
				$this->table_prefix . 'topics_track' => [
					'forum_id' => 'tpcstrk_forum_id',
					'topic_id' => 'tpcstrk_topic_id',
				],
				$this->table_prefix . 'topics_watch' => [
					'topic_id' => 'tpcswtch_topic_id',
					'notify_stat' => 'tpcswtch_notify_stat',
					'user_id' => 'tpcswtch_user_id',
				],
				$this->table_prefix . 'user_group' => [
					'group_id' => 'usrgrp_group_id',
					'user_id' => 'usrgrp_user_id',
				],
				$this->table_prefix . 'user_notifications' => [
					'user_id' => 'usrntf_user_id',
				],
			],
		];
	}

	public function revert_schema()
	{
		$schema = $this->update_schema();
		array_walk($schema['rename_index'], function (&$index_data, $table_name) {
		  $index_data = array_flip($index_data);
		});

		return $schema;
	}
}
