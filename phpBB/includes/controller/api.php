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

$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'includes/tree/nestedset_forum.' . $phpEx);

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
	 * Service container object
	 * @var object
	 */
	protected $container;

	/**
	 * Constructor
	 *
	 * @param phpbb_db_driver $db
	 */
	function __construct(phpbb_db_driver $db, $container)
	{
		$this->db = $db;
		$this->container = $container;
	}

	/**
	 * Controller method to return a list of forums
	 *
	 * Accesible trough /api/forums/{forum_id} (no {forum_id} defaults to 0)
	 * Method: GET
	 *
	 * @param $forum_id The forum to fetch, 0 fetches everything
	 * @return Response an array of forums, jsonencoded
	 */
	public function forums($forum_id)
	{
		if($forum_id == 0)
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
			$lock = $this->container->get('cron.lock_db');
			$nestedset_forum = new phpbb_tree_nestedset_forum($this->db, $lock, FORUMS_TABLE);

			$result = $nestedset_forum->get_subtree_data($forum_id);
		}

		$forums = array();
		foreach ($result as $row)
		{
			if($row['forum_id'] == $forum_id || $row['parent_id'] == 0)
			{
				$forums[] = $row;
			}
			else
			{
				$forums = $this->addSubForum($row, $row['parent_id'], $forums);
			}
		}

		$response = array(200, array('status' => 'success', 'response' => $forums));
		return new Response(json_encode($response[1]), $response[0]);
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
