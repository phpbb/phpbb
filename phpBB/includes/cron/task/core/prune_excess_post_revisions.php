<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
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
* Prune excess post revisions
*
* This task will find all posts that have more than the maximum
* number of allowed revisions and remove the excess revisions.
*
* @package phpBB3
*/
class phpbb_cron_task_core_prune_excess_post_revisions extends phpbb_cron_task_base
{
	/**
	* Config array
	* @var array
	*/
	protected $config;

	/**
	* DBAL Object
	* @var dbal
	*/
	protected $db;

	/**
	* Constructor method
	*/
	public function __construct()
	{
		global $config, $db;
		$this->config = $config;
		$this->db = $db;
	}

	/**
	* Runs this cron task.
	*
	* @return void
	*/
	public function run()
	{
		$prune_revision_ids = array();

		$iteration = 0;
		$ids_per_iteration = 500;

		// Now we get post IDs of posts with > the max number of revisions 
		$sql = 'SELECT post_id, post_edit_count
			FROM ' . POSTS_TABLE . '
			WHERE post_edit_count > ' . (int) $this->config['revisions_per_post_max'];
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Query in a loop? Uh oh! But I can't find a better way...
			// At least this will really only occur when the config value is decreased in the ACP
			// because this is also looked at on an individual post basis when a revision is made
			// as well as during the revision reverting process
			$inner_sql = 'SELECT revision_id
				FROM ' . POST_REVISIONS_TABLE . '
				WHERE post_id = ' . (int) $row['post_id'] . '
				ORDER BY revision_id ASC';
			$inner_result = $this->db->sql_query_limit($inner_sql, $row['post_edit_count'] - $config['revisions_per_post_max']);
			while ($inner_row = $this->db->sql_fetchrow($inner_result))
			{
				if (sizeof($prune_revision_ids[$iteration]) == $ids_per_iteration)
				{
					$iteration++;
				}
				$prune_revision_ids[$iteration][] = $inner_row['revision_id'];
			}
			$this->db->sql_freeresult($inner_result);
		}
		$this->db->sql_freeresult($result);

		// Finally, if we have any revisions that meet the criteria, we delete them
		if (!empty($prune_revision_ids))
		{
			for($i = 0; $i < $iteration; $i++)
			{
				$sql = 'DELETE FROM ' . POST_REVISIONS_TABLE . '
					WHERE ' . $this->db->sql_in_set('revision_id', $prune_revision_ids[$i]);
				$this->db->sql_query($sql);
			}
		}

		$this->config->set('excess_revisions_last_prune_time', time());
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return $this->config['track_post_revisions'] && $this->config['revisions_per_post_max'];
	}

	/**
	* Returns whether this cron task should run, given last run time.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['excess_revisions_last_prune_time'] < (time() - $this->config['revision_cron_excess_frequency']);
	}
}
