<?php
/**
 *
 * @package api
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * Topic model
 * @package phpBB3
 */
class phpbb_model_api_topic
{
	/**
	 * phpBB configuration
	 * @var phpbb_config
	 */
	protected $config;

	/** @var phpbb_db_driver */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param phpbb_config $config
	 * @param phpbb_db_driver $db
	 */
	function __construct(phpbb_config $config, phpbb_db_driver $db)
	{
		$this->config = $config;
		$this->db = $db;
	}

	public function get($forum_id, $page)
	{
		$topic_limit = $this->config['topics_per_page'];

		$sql = 'SELECT *
			FROM ' . TOPICS_TABLE . '
			WHERE forum_id = ' . (int)$forum_id . '
			ORDER BY topic_id DESC';

		$result = $this->db->sql_query_limit($sql, $topic_limit, $topic_limit * ($page - 1));

		$topics = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$topics[] = new phpbb_model_entity_topic($row);
		}
		$this->db->sql_freeresult($result);

		return $topics;
	}

}
