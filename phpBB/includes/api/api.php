<?php
/**
 *
 * @package phpBB3
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
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

class api
{

	public function list_forums()
	{
		global $db;
		$sql = 'SELECT forum_id, parent_id, forum_name, forum_desc, forum_type, forum_posts, forum_topics,
			forum_last_post_id, forum_last_poster_id, forum_last_post_subject, forum_last_post_time,
			forum_last_poster_name, forum_last_poster_colour
			FROM ' . FORUMS_TABLE;
		$result = $db->sql_query($sql);

		$forums = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$forums[] = $row;
		}
		$result = array(200, array('status' => 'success', 'response' => $forums));
		return $result;
	}

	/** @TODO decide on this, maybe merge functions and decide by parameters which responsetype to use **/
	public function list_forums_r()
	{
		global $db;
		$sql = 'SELECT forum_id, parent_id, forum_name, forum_desc, forum_type, forum_posts, forum_topics,
			forum_last_post_id, forum_last_poster_id, forum_last_post_subject, forum_last_post_time,
			forum_last_poster_name, forum_last_poster_colour, forum_link
			FROM ' . FORUMS_TABLE;
		$result = $db->sql_query($sql);

		$forums = array();
		while ($row = $db->sql_fetchrow($result))
		{
			if($row['parent_id'] == 0)
			{
				$forums[] = $row;
			}
			else
			{
				$forums = $this->addSubForum($row, $row['parent_id'], $forums);
			}

		}
		$result = array(200, array('status' => 'success', 'response' => $forums));
		return $result;
	}


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