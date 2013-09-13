<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_310_revisions extends phpbb_db_migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'post_revisions');
	}

	static public function depends_on()
	{
		return array('phpbb_db_migration_data_30x_3_0_11');
	}

	public function update_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'posts'		=> array(
					'post_revision_count'	=> array('USINT', 0),
					'post_wiki'				=> array('BOOL', 0),
				),
			),
			'add_tables'		=> array(
				$this->table_prefix . 'post_revisions'	=> array(
					'COLUMNS'		=> array(
						'revision_id'			=> array('UINT', NULL, 'auto_increment'),
						'post_id'				=> array('UINT', 0),
						'user_id'				=> array('UINT', 0),
						'revision_time'			=> array('TIMESTAMP', 0),
						'revision_subject'		=> array('STEXT_UNI', '', 'true_sort'),
						'revision_text'			=> array('MTEXT_UNI', ''),
						'revision_checksum'		=> array('VCHAR:32', ''),
						'bbcode_bitfield'		=> array('VCHAR:255', ''),
						'bbcode_uid'			=> array('VCHAR:8', ''),
						'revision_reason'		=> array('STEXT_UNI', ''),
						'revision_protected'	=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'revision_id',
					'KEYS'			=> array(
						'post_id'				=> array('INDEX', 'post_id'),
						'user_id'				=> array('INDEX', 'user_id'),
						'time'					=> array('INDEX', 'revision_time'),
					),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'		=> array(
				$this->table_prefix . 'posts'		=> array(
					'post_revision_count',
					'post_wiki',
				),
			),
			'drop_tables'		=> array(
				$this->table_prefix . 'post_revisions',
			),
		);
	}

	public function update_data()
	{
		return array(
			array('permission.add', array('f_revisions', 'f_edit')),
			array('permission.add', array('f_wiki_create', false)),
			array('permission.add', array('f_wiki_edit', false)),
			array('permission.add', array('m_revisions', 'm_edit')),
			array('permission.add', array('m_delete_revisions', 'm_edit')),
			array('permission.add', array('m_protect_revisions', 'm_edit')),

			array('permission.permission_unset', array('ROLE_FORUM_STANDARD', 'f_revisions')),
			array('permission.permission_unset', array('ROLE_FORUM_STANDARD', 'f_wiki_create')),
			array('permission.permission_unset', array('ROLE_FORUM_STANDARD', 'f_wiki_edit')),
			array('permission.permission_unset', array('ROLE_FORUM_ONQUEUE', 'f_revisions')),
			array('permission.permission_unset', array('ROLE_FORUM_ONQUEUE', 'f_wiki_create')),
			array('permission.permission_unset', array('ROLE_FORUM_ONQUEUE', 'f_wiki_edit')),
			array('permission.permission_unset', array('ROLE_FORUM_POLLS', 'f_revisions')),
			array('permission.permission_unset', array('ROLE_FORUM_POLLS', 'f_wiki_create')),
			array('permission.permission_unset', array('ROLE_FORUM_POLLS', 'f_wiki_edit')),
			array('permission.permission_unset', array('ROLE_FORUM_LIMITED', 'f_revisions')),
			array('permission.permission_unset', array('ROLE_FORUM_LIMITED', 'f_wiki_create')),
			array('permission.permission_unset', array('ROLE_FORUM_LIMITED', 'f_wiki_edit')),
			array('permission.permission_unset', array('ROLE_FORUM_LIMITED_POLLS', 'f_revisions')),
			array('permission.permission_unset', array('ROLE_FORUM_LIMITED_POLLS', 'f_wiki_create')),
			array('permission.permission_unset', array('ROLE_FORUM_LIMITED_POLLS', 'f_wiki_edit')),

			array('config.add', array('track_post_revisions', '0')),
			array('config.add', array('post_revisions_max_age', '0')),
			array('config.add', array('revisions_per_post_max', '0')),
			array('config.add', array('revisions_per_wiki_post_max', '0')),
			array('config.add', array('revisions_allow_wiki', '0')),
			array('config.add', array('revision_cron_age_frequency', '0')),
			array('config.add', array('revision_cron_excess_frequency', '0')),
			array('config.add', array('old_revisions_last_prune_time', '0')),
			array('config.add', array('excess_revisions_last_prune_time', '0')),
		);
	}
}
