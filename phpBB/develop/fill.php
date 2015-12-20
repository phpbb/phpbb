<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");


define('IN_PHPBB', true);
$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);

set_time_limit(0);
header('Expires: 0');
ignore_user_abort(true);

// number of topics to create
$num_topics = 10000;

// number of topics to be generated per call
$batch_size = 2000;

// max number of posts per topic
$posts_per_topic = 500;


// general vars
$mode = $request->variable('mode', 'generate');
$start = $request->variable('start', 0);

switch ($mode)
{
	case 'generate':
		$user_ids = $forum_ids = $topic_rows = array();

		$sql = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ') OR user_id = ' . ANONYMOUS;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$user_ids[] = $row['user_id'];
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT forum_id FROM ' . FORUMS_TABLE . ' WHERE forum_type = ' . FORUM_POST;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$forum_ids[$row['forum_id']] = $row['forum_id'];
		}
		$db->sql_freeresult($result);

		if (!$start)
		{
			$db->sql_query('TRUNCATE TABLE ' . POSTS_TABLE);
			$db->sql_query('TRUNCATE TABLE ' . TOPICS_TABLE);
//			$db->sql_query('TRUNCATE TABLE ' . TOPICS_TABLE . '_prefetch');
		}

		$db->sql_query('LOCK TABLES ' . POSTS_TABLE . ' WRITE, ' . TOPICS_TABLE . ' WRITE');

		for ($topic_id = $start + 1; $topic_id < min($start + $batch_size, $num_topics + 1); ++$topic_id)
		{
			$forum_id = array_rand($forum_ids);

			if (count($topic_rows) == 10)
			{
				$sql = 'INSERT IGNORE INTO ' . TOPICS_TABLE . " (topic_id, forum_id, topic_title, topic_reported)
					VALUES " . implode(', ', $topic_rows);
				$db->sql_query($sql);

				$topic_rows = array();
			}

			$topic_rows[] = "($topic_id, $forum_id, '$forum_id-$topic_id', " . (($topic_id % 34) ? '0' : '1') . ')';

			$sql = 'INSERT IGNORE INTO ' . POSTS_TABLE . ' (topic_id, forum_id, poster_id, post_subject, post_text, post_username, post_visibility, post_time, post_reported)
				VALUES ';

			$rows = array();
			$post_time = mt_rand(0, time());

			$num_posts = $posts_per_topic; //mt_rand(1, $posts_per_topic);
			for ($i = 0; $i < $num_posts; ++$i)
			{
				$poster_id = $user_ids[array_rand($user_ids)];
				$poster_name = ($poster_id == ANONYMOUS) ? rndm_username() : '';
				$rows[] = "($topic_id, $forum_id, $poster_id, '$forum_id-$topic_id-$i', '$forum_id-$topic_id-$i', '$poster_name', " . (mt_rand(0, 12) ? '1' : '0') . ', ' . ($post_time + $i * 60) . ', ' . (mt_rand(0, 32) ? '0' : '1') . ')';
			}

			$db->sql_query($sql . implode(', ', $rows));
		}

		if (count($topic_rows))
		{
			$sql = 'INSERT IGNORE INTO ' . TOPICS_TABLE . " (topic_id, forum_id, topic_title, topic_reported)
				VALUES " . implode(', ', $topic_rows);
			$db->sql_query($sql);
		}

		$db->sql_query('UNLOCK TABLES');

		if ($topic_id >= $num_topics)
		{
			echo '<meta http-equiv="refresh" content="10; url=fill.' . $phpEx . '?mode=sync&amp;' . time() . '">And now for something completely different...';

			$db->sql_query('ANALYZE TABLES ' . TOPICS_TABLE . ', ' . POSTS_TABLE);
			flush();
		}
		else
		{
			echo '<meta http-equiv="refresh" content="10; url=fill.' . $phpEx . '?start=' . $topic_id . '&amp;' . time() . '">To the next page... (' . $topic_id . '/' . $num_topics . ')';
			flush();
		}
	break;

	case 'sync':
/*		error_reporting(E_ALL);
		$sync_all = TRUE;

		if ($sync_all)
		{
			$s = explode(' ', microtime());
			sync('topic', '', '', TRUE, FALSE);
//			sync('forum');
			$e = explode(' ', microtime());

			echo '<pre><b>' . ($e[0] + $e[1] - $s[0] - $s[1]) . '</b></pre>';
			echo '<a href="fill.' . $phpEx . '">Here we go again</a>';
		}
		else
		{
			$batch_size = $batch_size * 10;
			$end = $start + $batch_size;

			$s = explode(' ', microtime());
			sync('topic', 'range', "topic_id BETWEEN $start AND $end", TRUE, FALSE);
			$e = explode(' ', microtime());

			echo '<pre>Time taken: <b>' . ($e[0] + $e[1] - $s[0] - $s[1]) . '</b></pre>';

			if ($end < $num_topics)
			{
				$start += $batch_size;
				echo '<meta http-equiv="refresh" content="0; url=fill.' . $phpEx . "?mode=sync&amp;start=$start&amp;" . time() . "\">And now for something completely different... ($start/$num_topics)";
			}
			else
			{
				echo '<a href="fill.' . $phpEx . '">Here we go again</a>';
			}
		}

		if (isset($_GET['explain']))
		{
			trigger_error('Done');
		}
	*/
}

$db->sql_close();

function rndm_username()
{
	static $usernames;

	if (!isset($usernames))
	{
		$usernames = get_defined_functions();
		$usernames = $usernames['internal'];
	}

	return $usernames[array_rand($usernames)];
}
