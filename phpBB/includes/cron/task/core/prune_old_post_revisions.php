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
		$prune_revision_ids = array();

		$sql = 'SELECT revision_id
			FROM ' . POST_REVISIONS_TABLE . '
			WHERE revision_time < ' . (time() - $this->config['post_revisions_max_age']) . '
				AND revision_protected = 0';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$prune_revision_ids[] = $row['revision_id'];
		}
		$this->db->sql_freeresult($result);

		if (!empty($prune_revision_ids))
		{
			foreach ($prune_revision_ids as $revision_id)
			{
				$sql = 'DELETE FROM ' . POST_REVISIONS_TABLE . '
					WHERE revision_id = ' . (int) $revision_id;
				$this->db->sql_query($sql);
			}
		}

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
		return $this->config['track_post_revisions'] && $this->config['post_revisions_max_age'];
	}

	/**
	* Returns whether this cron task should run, given last run time.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['old_revisions_last_prune_time'] < (time() - $this->config['revision_cron_age_frequency']);
	}
}
