<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_3_0_7_rc1 extends phpbb_db_migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['version'], '3.0.7-rc1', '>=');
	}

	static public function depends_on()
	{
		return array('phpbb_db_migration_data_3_0_6');
	}

	public function update_schema()
	{
		return array(
			'drop_keys' => array(
				$this->table_prefix . 'log' => array(
					'log_time',
				),
			),
			'add_index' => array(
				$this->table_prefix . 'topics_track' => array(
					'topic_id' => array('topic_id'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'add_index' => array(
				$this->table_prefix . 'log' => array(
					'log_time'	=> array('log_time'),
				),
			),
			'drop_keys' => array(
				$this->table_prefix . 'topics_track' => array(
					'topic_id',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('feed_overall', 1)),
			array('config.add', array('feed_http_auth', 0)),
			array('config.add', array('feed_limit_post', $this->config['feed_limit'])),
			array('config.add', array('feed_limit_topic', $this->config['feed_overall_topics_limit'])),
			array('config.add', array('feed_topics_new', $this->config['feed_overall_topics'])),
			array('config.add', array('feed_topics_active', $this->config['feed_overall_topics'])),
			array('custom', array(array(&$this, 'delete_text_templates'))),

			array('config.update', array('version', '3.0.7-rc1')),
		);
	}

	public function delete_text_templates()
	{
		// Delete all text-templates from the template_data
		$sql = 'DELETE FROM ' . STYLES_TEMPLATE_DATA_TABLE . '
			WHERE template_filename ' . $this->db->sql_like_expression($this->db->any_char . '.txt');
		$this->sql_query($sql);
	}
}
