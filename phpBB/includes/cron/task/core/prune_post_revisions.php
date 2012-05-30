<?php
/**
*
* @package phpBB3
* @copyright (c) 2010 phpBB Group
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
* It is intended to be invoked from system cron.
* This task will find all posts that have old revisions or
* more than the maximum number of allowed revisions and
* remove the excess revisions.
*
* @package phpBB3
*/
class phpbb_cron_task_core_prune_post_revisions extends phpbb_cron_task_base
{
	/**
	* Runs this cron task.
	*
	* @return void
	*/
	public function run()
	{
		global $phpbb_root_path, $phpEx, $db;

		$prune_revision_ids = array();

		if ($config['post_revisions_max_age'])
		{
			// First we get all revision IDs of revisions that are older than the date in the board settings
			$sql = 'SELECT revision_id
				FROM ' . POST_REVISIONS_TABLE . '
				WHERE revision_time < ' . (time() - $config['post_revisions_max_age']);
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$prune_revision_ids[] = $row['revision_id'];
			}
			$db->sql_freeresult($result);
		}

		if ($config['post_revisions_max_age'])
		{
			$overloaded_posts = array();

			// Now we get post IDs of posts with > the max number of revisions 
			$sql = 'SELECT post_id, post_edit_count
				FROM ' . POSTS_TABLE . '
				WHERE post_edit_count > ' . (int) $config['revisions_per_post_max'];
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$id	= $row['post_id'];
				$excess	= $row['post_edit_count'] - $config['revisions_per_post_max'];

				// Query in a loop? Uh oh! But I can't find a better way...
				// At least this will really only occur when the config value is decreased in the ACP
				// because it is also looked at on an individual post bases when a revision is made
				$inner_sql = 'SELECT revision_id
					FROM ' . POST_REVISIONS_TABLE . '
					WHERE post_id = ' . (int) $id . '
					ORDER BY revision_id ASC
					LIMIT ' . (int) $excess;
				$inner_result = $db->sql_query($inner_sql);
				while ($inner_row = $db->sql_fetchrow($inner_result))
				{
					$prune_revision_ids[] = $inner_row['revision_id'];
				}
				$db->sql_freeresult($inner_result);
			}
			$db->sql_freeresult($result);
		}

		$prune_revision_ids = array_unique($prune_revision_ids);

		// Finally, if we have any revisions that meet the criteria, we delete them
		if (!empty($prune_revision_ids))
		{
			$sql = 'DELETE FROM ' . POST_REVISIONS_TABLE . '
				WHERE ' . $db->sql_in_set('revision_id', $prune_revision_ids);
			$result = $db->sql_query($sql);
		}
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* This cron task will only run when system cron is utilised.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		global $config;
		return $config['use_system_cron'] && $config['track_post_revisions'] && ($config['revisions_per_post_max'] || $config['post_revisions_max_age']);
	}
}
