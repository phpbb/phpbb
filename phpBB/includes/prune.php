<?php
/***************************************************************************
*                                 prune.php
*                            -------------------
*   begin                : Thursday, June 14, 2001
*   copyright            : (C) 2001 The phpBB Group
*   email                : support@phpbb.com
*
*   $Id$
*
*
***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

function prune($forum_id, $prune_date)
{
	global $db, $lang;

	$pruned_topic_list = array();

	//
	// Those without polls ...
	//
	$sql = "SELECT t.topic_id 
		FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t
		WHERE t.forum_id = $forum_id
			AND t.topic_vote = 0 
			AND t.topic_type <> " . POST_ANNOUNCE . "
			AND p.post_id = t.topic_last_post_id";
	if ($prune_date != "")
	{
		$sql .= " AND p.post_time < $prune_date";
	}

	if(!$result_topics = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Couldn't obtain lists of topics to prune.", "", __LINE__, __FILE__, $sql);
	}

	if( $pruned_topics = $db->sql_numrows($result_topics) )
	{
		$pruned_topic_list = $db->sql_fetchrowset($result_topics);
	
		$sql_topics = "";
		for($i = 0; $i < $pruned_topics; $i++)
		{
			if($sql_topics != "")
			{
				$sql_topics .= ", ";
			}
			$sql_topics .= $pruned_topic_list[$i]['topic_id'];
		}
		$sql_topics = "topic_id IN (" . $sql_topics . ")";

		$sql = "SELECT post_id
			FROM " . POSTS_TABLE . " 
			WHERE forum_id = $forum_id 
				AND $sql_topics";
		if(!$result_posts = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Couldn't obtain list of posts to prune.", "", __LINE__, __FILE__, $sql);
		}

		$pruned_posts = $db->sql_numrows($result_posts);
		$pruned_post_list = $db->sql_fetchrowset($result_posts);

		$sql_post = "";
		for($i = 0; $i < $pruned_posts; $i++)
		{
			$post_id = $pruned_post_list[$i]['post_id'];

			if( $sql_post != "" )
			{
				$sql_post .= ", ";
			}
			$sql_post .= $post_id;
		}
		$sql_post = "post_id IN (" . $sql_post . ")";

		$sql = "DELETE FROM " . TOPICS_TABLE . " 
			WHERE " . $sql_topics;
		if(!$result = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Couldn't delete topics during prune.", "", __LINE__, __FILE__, $sql);
		}

		$sql = "DELETE FROM " . POSTS_TABLE . " 
			WHERE " . $sql_post;
		if(!$result = $db->sql_query($sql, BEGIN_TRANSACTION))
		{
			message_die(GENERAL_ERROR, "Couldn't delete post_text during prune.", "", __LINE__, __FILE__, $sql);
		}
		else
		{
			$sql = "DELETE FROM " . POSTS_TEXT_TABLE . " 
				WHERE " . $sql_post;
			if(!$result = $db->sql_query($sql, END_TRANSACTION))
			{
				message_die(GENERAL_ERROR, "Couldn't delete post during prune.", "", __LINE__, __FILE__, $sql);
			}
		}

		$sql = "UPDATE " . FORUMS_TABLE . "
			SET forum_topics = forum_topics - $pruned_topics, forum_posts = forum_posts - $pruned_posts
			WHERE forum_id = $forum_id";
		if(!$result = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Couldn't update forum data after prune.", "", __LINE__, __FILE__, $sql);
		}

		$returnval = array (
			"topics" => $pruned_topics,
			"posts" => $pruned_posts);

		return $returnval;
	}
	else
	{
		return (array("topics" => 0, "posts" => 0));
	}
}

/***************************************************************************\
*
*	Function auto_prune(), this function will read the configuration data from
* 	the auto_prune table and call the prune function with the necessary info.
*
****************************************************************************/
function auto_prune($forum_id = 0)
{
	global $db, $lang;

	$sql = "SELECT *
		FROM " . PRUNE_TABLE . "
		WHERE forum_id = $forum_id";

	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Auto-Prune: Couldn't read auto_prune table.", __LINE__, __FILE__);
	}

	if( $db->sql_numrows($result) )
	{
		$row = $db->sql_fetchrow($result);

		if( $row['prune_freq'] && $row['prune_days'] )
		{
			$prune_date = time() - ( $row['prune_days'] * 86400 );
			prune($forum_id, $prune_date);

			$next_prune = time() + ( $row['prune_freq'] * 86400 );

			$sql = "UPDATE " . FORUMS_TABLE . " 
				SET prune_next = $next_prune
				WHERE forum_id = $forum_id";
			if( !$db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Auto-Prune: Couldn't update forum table.", __LINE__, __FILE__);
			}
		}
	}

	return;
}

?>