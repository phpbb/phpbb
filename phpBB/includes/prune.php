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

function prune($forum_id, $prune_date)
{
	global $db, $lang;

	$sql = "SELECT t.topic_id  
		FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t 
		WHERE t.forum_id = $forum_id 
			AND t.topic_type = " . POST_NORMAL . " 
			AND p.post_id = t.topic_last_post_id";
	// Do we want to delete everything in the forum?
	if ($prune_date != FALSE)
	{
		$sql .= " AND p.post_time < $prune_date"; 
	}
	if(!$result_topics = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Couldn't obtain lists of topics to prune.", "", __LINE__, __FILE__, $sql);
	}
	$pruned_topics = $db->sql_numrows($result_topics);

	$sql = "SELECT p.post_id  
		FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t 
		WHERE p.forum_id = $forum_id 
			AND t.topic_id = p.topic_id  
			AND t.topic_type = " . POST_NORMAL;
	// Do we want to delete everything in the forum?
	if ($prune_date != FALSE)
	{
		$sql .= " AND p.post_time < $prune_date"; 
	}
	if(!$result_posts = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Couldn't obtain list of posts to prune.", "", __LINE__, __FILE__, $sql);
	}
	$pruned_posts = $db->sql_numrows($result_posts);

	if( $pruned_topics > 0 )
	{
		$pruned_topic_list = $db->sql_fetchrowset($result_topics);

		$sql_topics = "";

		for($i = 0; $i < $pruned_topics; $i++)
		{
			if($sql_topics != "")
			{
				$sql_topics .= " OR ";
			}
			$sql_topics .= "topic_id = " . $pruned_topic_list[$i]['topic_id'];
		}

		$sql_topics = "DELETE FROM " . TOPICS_TABLE . " WHERE " . $sql_topics;

		if(!$result = $db->sql_query($sql_topics))
		{
			message_die(GENERAL_ERROR, "Couldn't delete topics during prune.", "", __LINE__, __FILE__, $sql_topics);
		}
	}

	if( $pruned_posts > 0 )
	{
		$pruned_post_list = $db->sql_fetchrowset($result_posts);

		$sql_post_text = "";
		$sql_post = "";

		for($i = 0; $i < $pruned_posts; $i++)
		{
			$post_id = $pruned_post_list[$i]['post_id'];

			if($sql_post_text != "")
			{
				$sql_post_text .= " OR ";
			}
			$sql_post_text .= "post_id = $post_id";

			if($sql_post != "")
			{
				$sql_post .= " OR ";
			}
			$sql_post .= "post_id = $post_id";
		}

		$sql_post_text = "DELETE FROM " . POSTS_TEXT_TABLE . " WHERE " . $sql_post_text;
		$sql_post = "DELETE FROM " . POSTS_TABLE . " WHERE " . $sql_post; 

		if(!$result = $db->sql_query($sql_post_text, BEGIN_TRANSACTION))
		{
			message_die(GENERAL_ERROR, "Couldn't delete post_text during prune.", "", __LINE__, __FILE__, $sql_post_text);
		}
		else
		{
			if(!$result = $db->sql_query($sql_post, END_TRANSACTION))
			{
				message_die(GENERAL_ERROR, "Couldn't delete post during prune.", "", __LINE__, __FILE__, $sql_post);
			}
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

/***************************************************************************\
*
*	Function auto_prune(), this function will read the configuration data from
* 	the auto_prune table and call the prune function with the necessary info.
*
****************************************************************************/
function auto_prune($forum_id = 0)
{
	global $db, $lang;

	$one_day = 60 * 60 * 24;

	$sql = "SELECT * 
		FROM " . PRUNE_TABLE . " 
		WHERE forum_id = $forum_id";
	
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Auto-Prune: Couldn't read auto_prune table.", __LINE__, __FILE__);
	}

	while($row = $db->sql_fetchrow($result))
	{
		$forum_id = $row['forum_id'];

		$prune_date = time() - ($row['prune_days'] * $one_day);

		$pruned = prune($forum_id, $prune_date);

		$next_prune = time() + ($row['prune_freq'] * $one_day);

		$sql = "UPDATE " . FORUMS_TABLE . "
			SET prune_next = $next_prune
			WHERE forum_id = $forum_id";
		if(!$db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Auto-Prune: Couldn't update forum table.", __LINE__, __FILE__);
		}

	}

	return;
}

?>