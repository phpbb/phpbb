<?php
/**
 *
 * @package controller
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

use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for the api of a phpBB forum
 * @package phpBB3
 */
class phpbb_controller_api
{
	/** @var phpbb_db_driver */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param phpbb_db_driver $db
	 */
	function __construct(phpbb_db_driver $db)
	{
		$this->db = $db;
	}

	public function forums($forum_id)
	{
		/** @TODO: Implement this in nested sets instead */
		$sql = 'SELECT f1.forum_id, f1.parent_id, f1.forum_name, f1.forum_desc, f1.forum_type, f1.forum_posts,
			f1.forum_topics, f1.forum_last_post_id, f1.forum_last_poster_id, f1.forum_last_post_subject,
			f1.forum_last_post_time, f1.forum_last_poster_name, f1.forum_last_poster_colour
			FROM ' . FORUMS_TABLE . ' as f1, ' . FORUMS_TABLE . " as f2
				WHERE f2.parent_id = $forum_id
					AND f1.left_id >= f2.left_id
						AND f1.right_id <= f2.right_id";

		$result = $this->db->sql_query($sql);

		$forums = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			if($row['parent_id'] == $forum_id)
			{
				$forums[] = $row;
			}
			else
			{
				$forums = $this->addSubForum($row, $row['parent_id'], $forums);
			}
		}

		$result = array(200, array('status' => 'success', 'response' => $forums));
		return new Response(json_encode($result[1]), $result[0]);
	}

	/** @TODO: Move this somewhere other than the controller, fix better name, maybe not even needed after nested sets */
	private function addSubForum($row, $parent_id, $forums)
	{
		for($i = 0; $i < count($forums);$i++)
		{
			if($forums[$i]['forum_id'] == $parent_id)
			{
				$forums[$i]['subforums'][] = $row;
				break;
			}
			else if(isset($forums[$i]['subforums']))
			{
				$forums[$i]['subforums'] = $this->addSubForum($row, $parent_id, $forums[$i]['subforums']);
			}
		}
		return $forums;
	}
}
