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
	* Maximum number of items to delete at a time
	*/
	const BATCH_SIZE = 500;

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
		$posts_excess = $prune_revision_ids = array();
		do
		{
			$sql = 'SELECT post_id, post_revision_count
				FROM ' . POSTS_TABLE . '
				WHERE post_revision_count > ' . (int) $this->config['revisions_per_post_max'] . '
					AND revision_protected = 0';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$posts_excess[] = array(
					'id' => $row['post_id'],
					'excess' => $row['post_revision_count'] - $this->config['revisions_per_post_max'],
				);
			}
			$this->db->sql_freeresult($result);

			$post = current($posts_excess);
			do
			{
				$sql = 'SELECT revision_id
					FROM ' . POST_REVISIONS_TABLE . '
					WHERE post_id = ' . $post['id'] . '
						AND revision_protected = 0
					ORDER BY revision_id ASC';
				$result = $this->$db->sql_query_limit($sql, $post['excess']);
				while ($row = $this->db->sql_fetchrow($result))
				{
					if (sizeof($prune_revision_ids) == self::BATCH_SIZE)
					{
						break;
					}

					$prune_revision_ids[] = (int) $row['revision_id'];
				}
				$this->db->sql_freeresult($result);
			}
			while(($post = next($posts_excess)) !== false && sizeof($prune_revision_ids) < self::BATCH_SIZE);

			// Finally, if we have any revisions that meet the criteria, we delete them
			if (!empty($prune_revision_ids))
			{
				$sql = 'DELETE FROM ' . POST_REVISIONS_TABLE . '
					WHERE ' . $this->db->sql_in_set('revision_id', $prune_revision_ids);
				$this->db->sql_query($sql);

				foreach ($posts_excess as $post)
				{
					$sql = 'UPDATE ' . POSTS_TABLE . '
						SET post_revision_count = post_revision_count - ' . $post['excess'] . '
						WHERE post_id = ' . $post['id'];
					$this->db->sql_query($sql);
				}
			}
		}
		while (sizeof($prune_revision_ids) == self::BATCH_SIZE);

		add_log('admin', 'LOG_PRUNED_EXCESS_POST_REVISIONS');

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
		// Both config values below are stored as # of days; we want seconds
		$last_prune_time = $this->config['excess_revisions_last_prune_time'] * 86400;
		$frequency = $this->config['revision_cron_excess_frequency'] * 86400;
		return $last_prune_time < (time() - $frequency);
	}
}
