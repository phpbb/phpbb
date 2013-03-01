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
	* Maximum number of items to delete at a time
	*/
	const BATCH_SIZE = 500;

	/**
	* Constructor that makes available the config and dbal objects
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
		$affected_posts = $prune_revision_ids = array();

		// The max age stored in the database is in days; we want seconds
		// We multiple the number of days by the number of seconds in a day
		$max_age = $this->config['post_revisions_max_age'] * 86400;

		$sql = 'SELECT revision_id, post_id
			FROM ' . POST_REVISIONS_TABLE . '
			WHERE revision_time < ' . (time() - $max_age) . '
				AND revision_protected = 0';
		do
		{
			$result = $this->db->sql_query_limit($sql, self::BATCH_SIZE);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$affected_posts[$row['post_id']] = isset($affected_posts[$row['post_id']]) ? $affected_posts[$row['post_id']]++ : 1;
				$prune_revision_ids[] = $row['revision_id'];
			}
			$this->db->sql_freeresult($result);

			if (!empty($prune_revision_ids))
			{
				$sql = 'DELETE FROM ' . POST_REVISIONS_TABLE . '
					WHERE ' . $this->db->sql_in_set('revision_id', $prune_revision_ids);
				$this->db->sql_query($sql);

				foreach ($affected_posts as $post_id => $deleted_revisions_count)
				{
					$sql = 'UPDATE ' . POSTS_TABLE . '
						SET post_revision_count =  post_revision_count - ' . $deleted_revisions_count  . '
						WHERE post_id = ' . $post_id;
					$this->db->sql_query($sql);
				}
			}
		}
		while (sizeof($prune_revision_ids) == self::BATCH_SIZE);

		add_log('admin', 'LOG_PRUNED_OLD_POST_REVISIONS');

		$this->config->set('old_revisions_last_prune_time', time());
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		// The max age stored in the database is in days; we want seconds
		// We multiple the number of days by the number of seconds in a day
		$max_age = $this->config['post_revisions_max_age'] * 86400;
		return $this->config['track_post_revisions'] && $max_age;
	}

	/**
	* Returns whether this cron task should run, given last run time.
	*
	* @return bool
	*/
	public function should_run()
	{
		// Both config values below are stored as # of days; we want seconds
		$last_prune_time = $this->config['old_revisions_last_prune_time'] * 86400;
		$frequency = $this->config['revision_cron_age_frequency'] * 86400;
		return $last_prune_time < (time() - $frequency);
	}
}
