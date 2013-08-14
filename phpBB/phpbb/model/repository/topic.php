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
 * Topic repository
 * @package phpBB3
 */
class phpbb_model_repository_topic
{
	/** @var phpbb_auth */
	protected $auth;

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
	 * @param phpbb_auth $auth
	 * @param phpbb_config $config
	 * @param phpbb_db_driver $db
	 */
	function __construct(phpbb_auth $auth, phpbb_config $config, phpbb_db_driver $db)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
	}

	/**
	 * Get a topic and its posts
	 * @param $topic_id int The topic to fetch
	 * @param $page int the page
	 * @param $user_id
	 * @return array An array of topics
	 */
	public function get($topic_id, $page, $user_id)
	{
		$post_limit = $this->config['posts_per_page'];

		$sql = 'SELECT *
			FROM ' . POSTS_TABLE . '
			WHERE topic_id = ' . (int)$topic_id . '
			ORDER BY post_id ASC';

		$result = $this->db->sql_query_limit($sql, $post_limit, $post_limit * ($page - 1));

		$userdata = $this->auth->obtain_user_data($user_id);
		$this->auth->acl($userdata);

		$posts = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$this->auth->acl_get('f_read', $row['forum_id']))
			{
				return false;
			}
			$posts[] = new phpbb_model_entity_post($row);
		}
		$this->db->sql_freeresult($result);

		return $posts;
	}

}
