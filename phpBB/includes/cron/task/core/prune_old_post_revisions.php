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
* Prune old post revisions
*
* This task will find all posts that have old revisions
* remove the revisions that are older than the maximum revision age.
*
* @package phpBB3
*/
class phpbb_cron_task_core_prune_old_post_revisions extends phpbb_cron_task_base
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
	* Constructor that provides $
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

		// This allows us to split up the IDs so that we don't try to delete a ton all at once
		$iteration = 0;
		// This is how many IDs to have per iteration
		$ids_per_iteration = 20;

		$sql = 'SELECT revision_id
			FROM ' . POST_REVISIONS_TABLE . '
			WHERE revision_time < ' . (time() - $this->config['post_revisions_max_age']);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (sizeof($prune_revision_ids[$iteration]) == $ids_per_iteration)
			{
				$iteration++;
			}

			$prune_revision_ids[$iteration][] = $row['revision_id'];
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

		set_config('old_revisions_last_prune_time', time());
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		return $this->config['track_post_revisions'] && $this->config['post_revisions_max_age'];
	}

	/**
	* Returns whether this cron task should run, given last run time.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['old_revisions_last_prune_time'] < (time() - $this->config['excess_revisions_prune_wait_time']);
	}
}
