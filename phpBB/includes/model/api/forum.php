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
 * Forum model
 * @package phpBB3
 */
class phpbb_model_api_forum
{
	/** @var phpbb_tree_nestedset_forum */
	protected $nestedset_forum;

	/**
	 * Constructor
	 *
	 * @param phpbb_tree_nestedset_forum $nestedset_forum
	 */
	function __construct(phpbb_tree_nestedset_forum $nestedset_forum)
	{
		$this->nestedset_forum = $nestedset_forum;
	}
	public function get($forum_id){
		if ($forum_id == 0)
		{
			$result = $this->nestedset_forum->get_full_tree_data();
		}
		else
		{
			$result = $this->nestedset_forum->get_subtree_data($forum_id);
		}

		$forums = array();
		foreach ($result as $row)
		{
			$forum = new phpbb_model_entity_forum($row);

			if ($forum->get('forum_id') == $forum_id || $forum->get('parent_id') == 0)
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
	 * @param $forum phpbb_model_entity_forum The forum entity to add
	 * @param $forums array An array of forum entities
	 * @return array The new array with subforums added
	 */
	public function add_subforum($forum, $forums)
	{
		for ($i = 0; $i < count($forums);$i++)
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
