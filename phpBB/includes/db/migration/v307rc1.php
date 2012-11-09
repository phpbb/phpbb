<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v307rc1 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v306');
	}

	function update_schema()
	{
		return array(
			'drop_keys' => array(
				$this->table_prefix . 'log' => array('log_time'),
			),
			'add_index' => array(
				$this->table_prefix . 'topics_track' => array(
					'topic_id' => array('topic_id'),
				),
			),
		);
	}

	function update_data()
	{
		// ATOM Feeds
		set_config('feed_overall', '1');
		set_config('feed_http_auth', '0');
		set_config('feed_limit_post', (string) (isset($config['feed_limit']) ? (int) $config['feed_limit'] : 15));
		set_config('feed_limit_topic', (string) (isset($config['feed_overall_topics_limit']) ? (int) $config['feed_overall_topics_limit'] : 10));
		set_config('feed_topics_new', (!empty($config['feed_overall_topics']) ? '1' : '0'));
		set_config('feed_topics_active', (!empty($config['feed_overall_topics']) ? '1' : '0'));

		// Delete all text-templates from the template_data
		$sql = 'DELETE FROM ' . STYLES_TEMPLATE_DATA_TABLE . '
			WHERE template_filename ' . $db->sql_like_expression($db->any_char . '.txt');
		_sql($sql, $errored, $error_ary);
	}
}
