<?php
/**
 *
 * @package entity
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
 * Forum enitity
 * @package phpBB3
 */
class phpbb_model_api_forum
{
	/** @var phpbb_tree_nestedset_forum */
	protected $nestedset_forum;

	/** @var phpbb_db_driver */
	protected $db;
	/**
	 * Constructor
	 *
	 * @param phpbb_tree_nestedset_forum $nestedset_forum
	 */
	function __construct(phpbb_tree_nestedset_forum $nestedset_forum, phpbb_db_driver $db)
	{
		$this->nestedset_forum = $nestedset_forum;
		$this->db = $db;

	}
	public function get($forum_id){
		if ($forum_id == 0)
		{
			/** @TODO: Implement this in nested sets instead */
			$sql = 'SELECT *
					FROM ' . FORUMS_TABLE;

			$query = $this->db->sql_query($sql);
			$result = $this->db->sql_fetchrowset();
			$this->db->sql_freeresult($query);
		}
		else
		{

			$result = $this->nestedset_forum->get_subtree_data($forum_id);
		}

		$forums = array();
		foreach ($result as $row)
		{
			$forum = new phpbb_model_entity_forum($row);
			if ($forum->forum_id == $forum_id || $forum->parent_id == 0)
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

	/** @TODO: Something for nestedset? */
	/** @TODO: Optimize using left and right ids instead of checking every node until the correct one is found */
	/**
	 * Adds subforums in the recursive structure. The forum entity currently being added is added to
	 * the forum entity with the forum_id corresponding to the parent_id
	 * @param $forum The forum entity to add
	 * @param $forums An array of forum entities
	 * @return mixed The new array with subforums added
	 */
	public function add_subforum($forum, $forums)
	{
		for ($i = 0; $i < count($forums);$i++)
		{
			if ($forums[$i]->forum_id == $forum->parent_id)
			{
				$forums[$i]->subforums[] = $forum;
				break;
			}
			else if (isset($forums[$i]->subforums))
			{
				$forums[$i]->subforums = $this->add_subforum($forum, $forums[$i]->subforums);
			}
		}
		return $forums;
	}
}
