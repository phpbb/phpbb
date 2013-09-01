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
 * Forum repository
 * @package phpBB3
 */
class phpbb_model_repository_forum
{
	/** @var phpbb_tree_nestedset_forum */
	protected $nestedset_forum;

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
	 * @param phpbb_tree_nestedset_forum $nestedset_forum
	 * @param phpbb_auth $auth
	 * @param phpbb_config $config
	 * @param phpbb_db_driver $db
	 */
	function __construct(phpbb_tree_nestedset_forum $nestedset_forum, phpbb_auth $auth, phpbb_config $config, phpbb_db_driver $db)
	{
		$this->nestedset_forum = $nestedset_forum;
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
	}

	public function get($forum_id, $user_id)
	{
		if ($forum_id == 0)
		{
			$result = $this->nestedset_forum->get_full_tree_data();
		}
		else
		{
			$result = $this->nestedset_forum->get_subtree_data($forum_id);
		}

		$userdata = $this->auth->obtain_user_data($user_id);
		$this->auth->acl($userdata);

		$forums = array();
		foreach ($result as $row)
		{
			$forum = new phpbb_model_entity_forum($row);

			$fid = $forum->get('forum_id');
			if (!$this->auth->acl_get('f_read', $fid))
			{
				continue;
			}

			if ($fid == $forum_id || $forum->get('parent_id') == 0)
			{
				$forums[] = $forum;
			}
			else
			{
				$forums = $this->add_subforum($forum, $forums);
			}
		}
		return $forums;
	}

	/**
	 * Get a list of topics in the given forum
	 * @param $forum_id int The forum id to get the topics from
	 * @param $page int the page
	 * @return array An array of topics
	 */
	public function get_topics($forum_id, $page)
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

	/** @TODO: Something for nestedset? */
	/** @TODO: Optimize using left and right ids instead of checking every node until the correct one is found */
	/**
	 * Adds subforums in the recursive structure. The forum entity currently being added is added to
	 * the forum entity with the forum_id corresponding to the parent_id
	 * @param $forum phpbb_model_entity_forum The forum entity to add
	 * @param $forums array An array of forum entities
	 * @return array The new array with subforums added
	 */
	public function add_subforum($forum, $forums)
	{
		for ($i = 0; $i < count($forums); $i++)
		{
			if ($forums[$i]->get('forum_id') == $forum->get('parent_id'))
			{
				$subforums = $forums[$i]->get('subforums');
				$subforums[] = $forum;
				$forums[$i]->set('subforums', $subforums);
				break;
			}
			else if ($forums[$i]->get('subforums') !== null)
			{
				$forums[$i]->set('subforums', $this->add_subforum($forum, $forums[$i]->get('subforums')));
			}
		}
		return $forums;
	}
}
