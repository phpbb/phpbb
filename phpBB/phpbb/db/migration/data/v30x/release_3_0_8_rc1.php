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

class release_3_0_8_rc1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.0.8-RC1', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_7_pl1');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array(&$this, 'update_file_extension_group_names'))),
			array('custom', array(array(&$this, 'update_module_auth'))),
			array('custom', array(array(&$this, 'delete_orphan_shadow_topics'))),
			array('module.add', array(
				'acp',
				'ACP_MESSAGES',
				array(
					'module_basename'	=> 'acp_board',
					'module_langname'	=> 'ACP_POST_SETTINGS',
					'module_mode'		=> 'post',
					'module_auth'		=> 'acl_a_board',
					'after'				=> array('message', 'ACP_MESSAGE_SETTINGS'),
				),
			)),
			array('config.add', array('load_unreads_search', 1)),
			array('config.update_if_equals', array(600, 'queue_interval', 60)),
			array('config.update_if_equals', array(50, 'email_package_size', 20)),

			array('config.update', array('version', '3.0.8-RC1')),
		);
	}

	public function update_file_extension_group_names()
	{
		// Update file extension group names to use language strings.
		$sql = 'SELECT lang_dir
			FROM ' . LANG_TABLE;
		$result = $this->db->sql_query($sql);

		$extension_groups_updated = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (empty($row['lang_dir']))
			{
				continue;
			}

			$lang_dir = basename($row['lang_dir']);

			// The language strings we need are either in language/.../acp/attachments.php
			// in the update package if we're updating to 3.0.8-RC1 or later,
			// or they are in language/.../install.php when we're updating from 3.0.7-PL1 or earlier.
			// On an already updated board, they can also already be in language/.../acp/attachments.php
			// in the board root.
			$lang_files = array(
				"{$this->phpbb_root_path}install/update/new/language/$lang_dir/acp/attachments.{$this->php_ext}",
				"{$this->phpbb_root_path}language/$lang_dir/install.{$this->php_ext}",
				"{$this->phpbb_root_path}language/$lang_dir/acp/attachments.{$this->php_ext}",
			);

			foreach ($lang_files as $lang_file)
			{
				if (!file_exists($lang_file))
				{
					continue;
				}

				$lang = array();
				include($lang_file);

				foreach($lang as $lang_key => $lang_val)
				{
					if (isset($extension_groups_updated[$lang_key]) || strpos($lang_key, 'EXT_GROUP_') !== 0)
					{
						continue;
					}

					$sql_ary = array(
						'group_name'	=> substr($lang_key, 10), // Strip off 'EXT_GROUP_'
					);

					$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . '
						SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . "
						WHERE group_name = '" . $this->db->sql_escape($lang_val) . "'";
					$this->sql_query($sql);

					$extension_groups_updated[$lang_key] = true;
				}
			}
		}
		$this->db->sql_freeresult($result);
	}

	public function update_module_auth()
	{
		$sql = 'UPDATE ' . MODULES_TABLE . '
			SET module_auth = \'cfg_allow_avatar && (cfg_allow_avatar_local || cfg_allow_avatar_remote || cfg_allow_avatar_upload || cfg_allow_avatar_remote_upload)\'
			WHERE module_class = \'ucp\'
				AND module_basename = \'profile\'
				AND module_mode = \'avatar\'';
		$this->sql_query($sql);
	}

	public function delete_orphan_shadow_topics()
	{
		// Delete shadow topics pointing to not existing topics
		$batch_size = 500;

		// Set of affected forums we have to resync
		$sync_forum_ids = array();

		$sql_array = array(
			'SELECT'	=> 't1.topic_id, t1.forum_id',
			'FROM'		=> array(
				TOPICS_TABLE	=> 't1',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(TOPICS_TABLE	=> 't2'),
					'ON'	=> 't1.topic_moved_id = t2.topic_id',
				),
			),
			'WHERE'		=> 't1.topic_moved_id <> 0
						AND t2.topic_id IS NULL',
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $batch_size);

		$topic_ids = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$topic_ids[] = (int) $row['topic_id'];

			$sync_forum_ids[(int) $row['forum_id']] = (int) $row['forum_id'];
		}
		$this->db->sql_freeresult($result);

		if (!empty($topic_ids))
		{
			$sql = 'DELETE FROM ' . TOPICS_TABLE . '
				WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids);
			$this->db->sql_query($sql);

			// Sync the forums we have deleted shadow topics from.
			sync('forum', 'forum_id', $sync_forum_ids, true, true);

			return false;
		}
	}
}
