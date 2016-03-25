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

if (php_sapi_name() != 'cli')
{
	die("This program must be run from the command line.\n");
}

if ($argc < 2)
{
	echo 'Usage: php ' . basename(__FILE__) . " index_type [batch_size]\n";
	exit(1);
}

$class_name = basename($argv[1]);

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'common.' . $phpEx);
require($phpbb_root_path . 'includes/acp/acp_search.' . $phpEx);

$user->session_begin();
$auth->acl($user->data);
$user->setup('acp/search');

$search_name = ucfirst(strtolower(str_replace('_', ' ', $class_name)));
$search_errors = array();
$search = new $class_name($search_errors);

$batch_size = isset($argv[2]) ? $argv[2] : 2000;

if (method_exists($search, 'create_index'))
{
	if ($error = $search->create_index(null, ''))
	{
		var_dump($error);
		exit(1);
	}
}
else
{
	$sql = 'SELECT forum_id, enable_indexing
		FROM ' . FORUMS_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$forums[$row['forum_id']] = (bool) $row['enable_indexing'];
	}
	$db->sql_freeresult($result);

	$sql = 'SELECT post_id
		FROM ' . POSTS_TABLE . '
		ORDER BY post_id DESC';
	$result = $db->sql_query_limit($sql, 1);
	$max_post_id = (int) $db->sql_fetchfield('post_id');

	$post_counter = 0;
	while ($post_counter <= $max_post_id)
	{
		$row_count = 0;
		$time = time();

		printf("Processing posts with %d <= post_id <= %d\n",
			$post_counter + 1,
			$post_counter + $batch_size
		);

		$sql = 'SELECT post_id, post_subject, post_text, poster_id, forum_id
			FROM ' . POSTS_TABLE . '
			WHERE post_id >= ' . (int) ($post_counter + 1) . '
				AND post_id <= ' . (int) ($post_counter + $batch_size);
		$result = $db->sql_query($sql);

		$buffer = $db->sql_buffer_nested_transactions();

		if ($buffer)
		{
			$rows = $db->sql_fetchrowset($result);
			$rows[] = false; // indicate end of array for while loop below

			$db->sql_freeresult($result);
		}

		$i = 0;
		while ($row = ($buffer ? $rows[$i++] : $db->sql_fetchrow($result)))
		{
			// Indexing enabled for this forum or global announcement?
			// Global announcements get indexed by default.
			if (!$row['forum_id'] || !empty($forums[$row['forum_id']]))
			{
				++$row_count;

				$search->index('post',
					$row['post_id'],
					$row['post_text'],
					$row['post_subject'],
					$row['poster_id'],
					$row['forum_id']
				);

				if ($row_count % 10 == 0)
				{
					echo '.';
				}
			}
		}

		$delta = (time() - $time);
		$delta = $delta <= 0 ? 1 : $delta;
		printf(" %d posts/sec\n", $row_count / $delta);

		if (!$buffer)
		{
			$db->sql_freeresult($result);
		}

		$post_counter += $batch_size;
	}
}

$search->tidy();

$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_SEARCH_INDEX_CREATED', false, array($search_name));

echo $user->lang['SEARCH_INDEX_CREATED'] . "\n";
echo 'Peak Memory Usage: ' . get_formatted_filesize(memory_get_peak_usage()) . "\n";

exit(0);
