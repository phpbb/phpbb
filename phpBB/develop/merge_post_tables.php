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

@set_time_limit(2400);

$db = $dbhost = $dbuser = $dbpasswd = $dbport = $dbname = '';

define('IN_PHPBB', 1);
define('ANONYMOUS', 1);
$phpbb_root_path='./../';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'config.'.$phpEx);
include($phpbb_root_path . 'includes/functions.'.$phpEx);
require($phpbb_root_path . 'includes/acm/cache_' . $acm_type . '.'.$phpEx);
include($phpbb_root_path . 'db/' . $dbms . '.'.$phpEx);

$cache = new acm();
$db = new sql_db($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false);

// Just Do it (tm) 
$sql = "RENAME TABLE {$table_prefix}posts TO {$table_prefix}posts_temp";
$db->sql_query($sql);

$sql = "CREATE TABLE {$table_prefix}posts 
	SELECT p.*, pt.post_subject, pt.post_text, pt.post_checksum, pt.bbcode_bitfield, pt.bbcode_uid 
		FROM {$table_prefix}posts_temp p, {$table_prefix}posts_text pt 
		WHERE pt.post_id = p.post_id";
$db->sql_query($sql);

switch ($db->get_sql_layer())
{
	case 'mysql':
	case 'mysql4':
		$sql = 'ALTER TABLE ' . $table_prefix . 'posts 
			ADD PRIMARY KEY (post_id), 
			ADD INDEX topic_id (topic_id), 
			ADD INDEX poster_ip (poster_ip), 
			ADD INDEX post_visibility (post_visibility), 
			MODIFY COLUMN post_id mediumint(8) UNSIGNED NOT NULL auto_increment, 
			ADD COLUMN post_encoding varchar(11) DEFAULT \'iso-8859-15\' NOT NULL'; 
		break;

	case 'mssql':
	case 'mssql-odbc':
	case 'msaccess':
		break;

	case 'postgresql':
		break;
}
$db->sql_query($sql);

$sql = "UPDATE {$table_prefix}topics SET topic_poster = 1 WHERE topic_poster = 0 OR topic_poster IS NULL";
$db->sql_query($sql);
$sql = "UPDATE {$table_prefix}topics SET topic_last_poster_id = 1 WHERE topic_last_poster_id = 0 OR topic_last_poster_id IS NULL";
$db->sql_query($sql);
$sql = "UPDATE {$table_prefix}posts SET poster_id = 1 WHERE poster_id = 0 OR poster_id IS NULL";
$db->sql_query($sql);
$sql = "UPDATE {$table_prefix}users SET user_id = 1 WHERE user_id = 0";
$db->sql_query($sql);

$sql = "SELECT t.topic_id 
	FROM {$table_prefix}topics t 
	LEFT JOIN {$table_prefix}posts p ON p.topic_id = t.topic_id 
	WHERE p.topic_id IS NULL";
$result = $db->sql_query($sql);

if ($row = $db->sql_fetchrow($result))
{
	$del_sql = '';
	do
	{
		$del_sql .= (($del_sql != '') ? ', ' : '') . $row['topic_id'];
	}
	while ($row = $db->sql_fetchrow($result));

	$sql = "DELETE FROM {$table_prefix}topics 
		WHERE topic_id IN ($del_sql)";
	$db->sql_query($sql);
}
$db->sql_freeresult($result);

$del_sql = '';
$sql = "SELECT topic_id, MIN(post_id) AS first_post_id, MAX(post_id) AS last_post_id, COUNT(post_id) AS total_posts 
	FROM {$table_prefix}posts 
	GROUP BY topic_id";
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$del_sql .= (($del_sql != '') ? ', ' : '') . $row['topic_id'];

	$sql = "UPDATE {$table_prefix}topics 
		SET topic_first_post_id = " . $row['first_post_id'] . ", topic_last_post_id = " . $row['last_post_id'] . ", topic_replies = " . ($row['total_posts'] - 1) . "
		WHERE topic_id = " . $row['topic_id'];
	$db->sql_query($sql);
}
$db->sql_freeresult($result);

$sql = "DELETE FROM {$table_prefix}topics WHERE topic_id NOT IN ($del_sql)";
$db->sql_query($sql);

$topic_count = $post_count = array();
$sql = "SELECT forum_id, COUNT(topic_id) AS topics 
	FROM {$table_prefix}topics 
	GROUP BY forum_id";
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$topic_count[$row['forum_id']] = $row['topics'];
}
$db->sql_freeresult($result);

$sql = "SELECT forum_id, COUNT(post_id) AS posts  
	FROM {$table_prefix}posts 
	GROUP BY forum_id";
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$post_count[$row['forum_id']] = $row['posts'];
}
$db->sql_freeresult($result);

switch ($db->get_sql_layer())
{
	case 'oracle':
		$sql = "SELECT f.*, p.post_time, p.post_username, u.username, u.user_id
			FROM " . $table_prefix . "forums f, " . $table_prefix . "posts p, " . $table_prefix . "users u
			WHERE p.post_id = f.forum_last_post_id(+)
				AND u.user_id = p.poster_id(+)";
		break;

	default:
		$sql = "SELECT f.forum_id, p.post_time, p.post_username, u.username, u.user_id
			FROM ((" . $table_prefix . "forums f
			LEFT JOIN " . $table_prefix . "posts p ON p.post_id = f.forum_last_post_id)
			LEFT JOIN " . $table_prefix . "users u ON u.user_id = p.poster_id)";
		break;
}
$result = $db->sql_query($sql);

$sql_ary = array();
while ($row = $db->sql_fetchrow($result))
{
	$forum_id = $row['forum_id'];

	$sql_ary[] = "UPDATE " . $table_prefix . "forums
		SET forum_last_poster_id = " . ((!empty($row['user_id']) && $row['user_id'] != ANONYMOUS) ? $row['user_id'] : ANONYMOUS) . ", forum_last_poster_name = '" . ((!empty($row['user_id']) && $row['user_id'] !=  ANONYMOUS) ? addslashes($row['username']) : addslashes($row['post_username'])) . "', forum_last_post_time = " . $row['post_time'] . ", forum_posts_approved = " . (($post_count[$forum_id]) ? $post_count[$forum_id] : 0) . ", forum_topics_approved = " . (($topic_count[$forum_id]) ? $topic_count[$forum_id] : 0) . " 
		WHERE forum_id = $forum_id";

	$sql = "SELECT t.topic_id, u.username, u.user_id, u2.username as user2, u2.user_id as id2, p.post_username, p2.post_username AS post_username2, p2.post_time
		FROM " . $table_prefix . "topics t, " . $table_prefix . "users u, " . $table_prefix . "posts p, " . $table_prefix . "posts p2, " . $table_prefix . "users u2
		WHERE t.forum_id = $forum_id 
			AND u.user_id = t.topic_poster 
			AND p.post_id = t.topic_first_post_id
			AND p2.post_id = t.topic_last_post_id
			AND u2.user_id = p2.poster_id";
	$result2 = $db->sql_query($sql);

	while ($row2 = $db->sql_fetchrow($result2))
	{
		$sql_ary[] = "UPDATE " . $table_prefix . "topics
			SET topic_poster = " . $row2['user_id'] . ", topic_first_poster_name = '" . ((!empty($row2['user_id']) && $row2['user_id'] != ANONYMOUS) ? addslashes($row2['username']) : addslashes($row2['post_username'])) . "', topic_last_poster_id = " . ((!empty($row2['id2']) && $row2['id2'] != ANONYMOUS) ? $row2['id2'] : ANONYMOUS) . ", topic_last_post_time = " . $row2['post_time'] . ", topic_last_poster_name = '" . ((!empty($row2['id2']) && $row2['id2'] !=  ANONYMOUS) ? addslashes($row2['user2']) : addslashes($row2['post_username2'])) . "'
			WHERE topic_id = " . $row2['topic_id'];
	}
	$db->sql_freeresult($result2);

	unset($row2);
}
$db->sql_freeresult($result);

foreach ($sql_ary as $sql)
{
	$sql . "<br />";
	$db->sql_query($sql);
}

echo "<p><b>Done</b></p>\n";
