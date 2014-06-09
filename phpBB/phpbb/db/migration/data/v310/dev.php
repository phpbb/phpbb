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

class dev extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['version'], '3.1.0-dev', '>=');
	}

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\extensions',
			'\phpbb\db\migration\data\v310\style_update_p2',
			'\phpbb\db\migration\data\v310\timezone_p2',
			'\phpbb\db\migration\data\v310\reported_posts_display',
			'\phpbb\db\migration\data\v310\migrations_table',
		);
	}

	public function update_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'groups'		=> array(
					'group_teampage'	=> array('UINT', 0, 'after' => 'group_legend'),
				),
				$this->table_prefix . 'profile_fields'	=> array(
					'field_show_on_pm'		=> array('BOOL', 0),
				),
				$this->table_prefix . 'styles'		=> array(
					'style_path'			=> array('VCHAR:100', ''),
					'bbcode_bitfield'		=> array('VCHAR:255', 'kNg='),
					'style_parent_id'		=> array('UINT:4', 0),
					'style_parent_tree'		=> array('TEXT', ''),
				),
				$this->table_prefix . 'reports'		=> array(
					'reported_post_text'		=> array('MTEXT_UNI', ''),
					'reported_post_uid'			=> array('VCHAR:8', ''),
					'reported_post_bitfield'	=> array('VCHAR:255', ''),
				),
			),
			'change_columns'	=> array(
				$this->table_prefix . 'groups'		=> array(
					'group_legend'		=> array('UINT', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'		=> array(
				$this->table_prefix . 'groups'		=> array(
					'group_teampage',
				),
				$this->table_prefix . 'profile_fields'	=> array(
					'field_show_on_pm',
				),
				$this->table_prefix . 'styles'		=> array(
					'style_path',
					'bbcode_bitfield',
					'style_parent_id',
					'style_parent_tree',
				),
				$this->table_prefix . 'reports'		=> array(
					'reported_post_text',
					'reported_post_uid',
					'reported_post_bitfield',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('if', array(
				(strpos('phpbb_search_', $this->config['search_type']) !== 0),
				array('config.update', array('search_type', 'phpbb_search_' . $this->config['search_type'])),
			)),

			array('config.add', array('fulltext_postgres_ts_name', 'simple')),
			array('config.add', array('fulltext_postgres_min_word_len', 4)),
			array('config.add', array('fulltext_postgres_max_word_len', 254)),
			array('config.add', array('fulltext_sphinx_stopwords', 0)),
			array('config.add', array('fulltext_sphinx_indexer_mem_limit', 512)),

			array('config.add', array('load_jquery_cdn', 0)),
			array('config.add', array('load_jquery_url', '//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js')),

			array('config.add', array('use_system_cron', 0)),

			array('config.add', array('legend_sort_groupname', 0)),
			array('config.add', array('teampage_forums', 1)),
			array('config.add', array('teampage_memberships', 1)),

			array('config.add', array('load_cpf_pm', 0)),

			array('config.add', array('display_last_subject', 1)),

			array('config.add', array('assets_version', 1)),

			array('config.add', array('site_home_url', '')),
			array('config.add', array('site_home_text', '')),

			array('permission.add', array('u_chgprofileinfo', true, 'u_sig')),

			array('module.add', array(
				'acp',
				'ACP_GROUPS',
				array(
					'module_basename'	=> 'acp_groups',
					'modes'				=> array('position'),
				),
			)),
			array('module.add', array(
				'acp',
				'ACP_ATTACHMENTS',
				array(
					'module_basename'	=> 'acp_attachments',
					'modes'				=> array('manage'),
				),
			)),
			array('module.add', array(
				'acp',
				'ACP_STYLE_MANAGEMENT',
				array(
					'module_basename'	=> 'acp_styles',
					'modes'				=> array('install', 'cache'),
				),
			)),
			array('module.add', array(
				'ucp',
				'UCP_PROFILE',
				array(
					'module_basename'	=> 'ucp_profile',
					'modes'				=> array('autologin_keys'),
				),
			)),
			// Module will be renamed later
			array('module.add', array(
				'acp',
				'ACP_CAT_STYLES',
				'ACP_LANGUAGE'
			)),

			array('module.remove', array(
				'acp',
				false,
				'ACP_TEMPLATES',
			)),
			array('module.remove', array(
				'acp',
				false,
				'ACP_THEMES',
			)),
			array('module.remove', array(
				'acp',
				false,
				'ACP_IMAGESETS',
			)),

			array('custom', array(array($this, 'rename_module_basenames'))),
			array('custom', array(array($this, 'rename_styles_module'))),
			array('custom', array(array($this, 'add_group_teampage'))),
			array('custom', array(array($this, 'update_group_legend'))),
			array('custom', array(array($this, 'localise_global_announcements'))),
			array('custom', array(array($this, 'update_ucp_pm_basename'))),
			array('custom', array(array($this, 'update_ucp_profile_auth'))),
			array('custom', array(array($this, 'move_customise_modules'))),

			array('config.update', array('version', '3.1.0-dev')),
		);
	}

	public function move_customise_modules()
	{
		// Move language management to new location in the Customise tab
		// First get language module id
		$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
			WHERE module_basename = 'acp_language'";
		$result = $this->db->sql_query($sql);
		$language_module_id = $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);
		// Next get language management module id of the one just created
		$sql = 'SELECT module_id FROM ' . MODULES_TABLE . "
			WHERE module_langname = 'ACP_LANGUAGE'";
		$result = $this->db->sql_query($sql);
		$language_management_module_id = $this->db->sql_fetchfield('module_id');
		$this->db->sql_freeresult($result);

		if (!class_exists('acp_modules'))
		{
			include($this->phpbb_root_path . 'includes/acp/acp_modules.' . $this->php_ext);
		}
		// acp_modules calls adm_back_link, which is undefined at this point
		if (!function_exists('adm_back_link'))
		{
			include($this->phpbb_root_path . 'includes/functions_acp.' . $this->php_ext);
		}
		$module_manager = new \acp_modules();
		$module_manager->module_class = 'acp';
		$module_manager->move_module($language_module_id, $language_management_module_id);
	}

	public function update_ucp_pm_basename()
	{
		$sql = 'SELECT module_id, module_basename
			FROM ' . MODULES_TABLE . "
			WHERE module_basename <> 'ucp_pm' AND
				module_langname='UCP_PM'";
		$result = $this->db->sql_query_limit($sql, 1);

		if ($row = $this->db->sql_fetchrow($result))
		{
			// This update is still not applied. Applying it

			$sql = 'UPDATE ' . MODULES_TABLE . "
				SET module_basename = 'ucp_pm'
				WHERE  module_id = " . (int) $row['module_id'];

			$this->sql_query($sql);
		}
		$this->db->sql_freeresult($result);
	}

	public function update_ucp_profile_auth()
	{
		// Update the auth setting for the module
		$sql = 'UPDATE ' . MODULES_TABLE . "
			SET module_auth = 'acl_u_chgprofileinfo'
			WHERE module_class = 'ucp'
				AND module_basename = 'ucp_profile'
				AND module_mode = 'profile_info'";
		$this->sql_query($sql);
	}

	public function rename_styles_module()
	{
		// Rename styles module to Customise
		$sql = 'UPDATE ' . MODULES_TABLE . "
			SET module_langname = 'ACP_CAT_CUSTOMISE'
			WHERE module_langname = 'ACP_CAT_STYLES'";
		$this->sql_query($sql);
	}

	public function rename_module_basenames()
	{
		// rename all module basenames to full classname
		$sql = 'SELECT module_id, module_basename, module_class
			FROM ' . MODULES_TABLE;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$module_id = (int) $row['module_id'];
			unset($row['module_id']);

			if (!empty($row['module_basename']) && !empty($row['module_class']))
			{
				// all the class names start with class name or with phpbb_ for auto loading
				if (strpos($row['module_basename'], $row['module_class'] . '_') !== 0 &&
					strpos($row['module_basename'], 'phpbb_') !== 0)
				{
					$row['module_basename'] = $row['module_class'] . '_' . $row['module_basename'];

					$sql_update = $this->db->sql_build_array('UPDATE', $row);

					$sql = 'UPDATE ' . MODULES_TABLE . '
						SET ' . $sql_update . '
						WHERE module_id = ' . $module_id;
					$this->sql_query($sql);
				}
			}
		}

		$this->db->sql_freeresult($result);
	}

	public function add_group_teampage()
	{
		$sql = 'UPDATE ' . GROUPS_TABLE . '
			SET group_teampage = 1
			WHERE group_type = ' . GROUP_SPECIAL . "
				AND group_name = 'ADMINISTRATORS'";
		$this->sql_query($sql);

		$sql = 'UPDATE ' . GROUPS_TABLE . '
			SET group_teampage = 2
			WHERE group_type = ' . GROUP_SPECIAL . "
				AND group_name = 'GLOBAL_MODERATORS'";
		$this->sql_query($sql);
	}

	public function update_group_legend()
	{
		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . '
			WHERE group_legend = 1
			ORDER BY group_name ASC';
		$result = $this->db->sql_query($sql);

		$next_legend = 1;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql = 'UPDATE ' . GROUPS_TABLE . '
				SET group_legend = ' . $next_legend . '
				WHERE group_id = ' . (int) $row['group_id'];
			$this->sql_query($sql);

			$next_legend++;
		}
		$this->db->sql_freeresult($result);
	}

	public function localise_global_announcements()
	{
		// Localise Global Announcements
		$sql = 'SELECT topic_id, topic_approved, (topic_replies + 1) AS topic_posts, topic_last_post_id, topic_last_post_subject, topic_last_post_time, topic_last_poster_id, topic_last_poster_name, topic_last_poster_colour
			FROM ' . TOPICS_TABLE . '
			WHERE forum_id = 0
				AND topic_type = ' . POST_GLOBAL;
		$result = $this->db->sql_query($sql);

		$global_announcements = $update_lastpost_data = array();
		$update_lastpost_data['forum_last_post_time'] = 0;
		$update_forum_data = array(
			'forum_posts'		=> 0,
			'forum_topics'		=> 0,
			'forum_topics_real'	=> 0,
		);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$global_announcements[] = (int) $row['topic_id'];

			$update_forum_data['forum_posts'] += (int) $row['topic_posts'];
			$update_forum_data['forum_topics_real']++;
			if ($row['topic_approved'])
			{
				$update_forum_data['forum_topics']++;
			}

			if ($update_lastpost_data['forum_last_post_time'] < $row['topic_last_post_time'])
			{
				$update_lastpost_data = array(
					'forum_last_post_id'		=> (int) $row['topic_last_post_id'],
					'forum_last_post_subject'	=> $row['topic_last_post_subject'],
					'forum_last_post_time'		=> (int) $row['topic_last_post_time'],
					'forum_last_poster_id'		=> (int) $row['topic_last_poster_id'],
					'forum_last_poster_name'	=> $row['topic_last_poster_name'],
					'forum_last_poster_colour'	=> $row['topic_last_poster_colour'],
				);
			}
		}
		$this->db->sql_freeresult($result);

		if (!empty($global_announcements))
		{
			// Update the post/topic-count for the forum and the last-post if needed
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE forum_type = ' . FORUM_POST;
			$result = $this->db->sql_query_limit($sql, 1);
			$ga_forum_id = $this->db->sql_fetchfield('forum_id');
			$this->db->sql_freeresult($result);

			$sql = 'SELECT forum_last_post_time
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . $ga_forum_id;
			$result = $this->db->sql_query($sql);
			$lastpost = (int) $this->db->sql_fetchfield('forum_last_post_time');
			$this->db->sql_freeresult($result);

			$sql_update = 'forum_posts = forum_posts + ' . $update_forum_data['forum_posts'] . ', ';
			$sql_update .= 'forum_topics_real = forum_topics_real + ' . $update_forum_data['forum_topics_real'] . ', ';
			$sql_update .= 'forum_topics = forum_topics + ' . $update_forum_data['forum_topics'];
			if ($lastpost < $update_lastpost_data['forum_last_post_time'])
			{
				$sql_update .= ', ' . $this->db->sql_build_array('UPDATE', $update_lastpost_data);
			}

			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET ' . $sql_update . '
				WHERE forum_id = ' . $ga_forum_id;
			$this->sql_query($sql);

			// Update some forum_ids
			$table_ary = array(TOPICS_TABLE, POSTS_TABLE, LOG_TABLE, DRAFTS_TABLE, TOPICS_TRACK_TABLE);
			foreach ($table_ary as $table)
			{
				$sql = "UPDATE $table
					SET forum_id = $ga_forum_id
					WHERE " . $this->db->sql_in_set('topic_id', $global_announcements);
				$this->sql_query($sql);
			}
			unset($table_ary);
		}
	}
}
