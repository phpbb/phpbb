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

/**************************************************************************\
*                                                     
*   This program is free software; you can redistribute it and/or modify    
*   it under the terms of the GNU General Public License as published by   
*   the Free Software Foundation; either version 2 of the License, or  
*   (at your option) any later version.                      
*                                                          
\**************************************************************************/
 
// I am currently seperating the prune functions from functions.php due to the 
// fact that they are only really needed in one or two places so I don't see 
// the need to include them everywhere.  If someone else thinks this is a bad
// idea I am not opposed to moving them elsewhere ;) Jonathan "The_Systech" 

/***************************************************************************\
*
* 	function prune.  This function takes as it's arguments the forum id to 
*  perform the prune on, and the date before which topics should be pruned.
*
*	This function returns the number of topics pruned upon success.
*
\***************************************************************************/
function prune($forum_id, $prune_date)
{
	global $db, $lang;
	$sql = 'SELECT t.topic_id
		FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
		WHERE t.forum_id = $forum_id
			AND p.post_id = t.topic_last_post_id
			AND t.topic_type != " . POST_ANNOUNCE . "
			AND p.post_time < $prune_date";
	if(!$result = $db->sql_query($sql))
	{
		error_die(SQL_QUERY, "Couldn't obtain list of topics to prune.", __LINE__, __FILE__);
	} // End if(!$result...
	$pruned_topics = $db->sql_numrows($result);
	if($pruned_topics > 0) 
	{
		$prune_posts_sql = 'DELETE FROM ' . POSTS_TABLE . "
			WHERE forum_id = $forum_id AND (";
		$prune_topic_sql = 'DELETE FROM ' . TOPICS_TABLE . "
			WHERE forum_id = $forum_id AND (";
		// ADD a list ORing all Topic ID's to prune....
		while($row = $db->sql_fetchrow($result))
		{
			$prune_posts_sql .= 'topic_id = ' . $row['topic_id'] . ' OR ';
			$prune_topic_sql .= 'topic_id = ' . $row['topic_id'] . ' OR ';
		} // End while loop
		// Remove the final OR...
		$prune_posts_sql = substr($prune_posts_sql, 0, (strlen($prune_posts_sql) - 4));
		$prune_topic_sql = substr($prune_topic_sql, 0, strlen($prune_topic_sql) - 4);
		$prune_posts_sql .= ')';
		$prune_topic_sql .= ')';
		if(!$result = $db->sql_query($prune_posts_sql))
		{
			error_die(SQL_QUERY, "While Pruning: Couldn't remove affected posts.<br>$prune_posts_sql", __LINE__, __FILE__);
		} // end if(!$result...
		if(!$result = $db->sql_query($prune_topic_sql))
		{
			error_die(SQL_QUERY, "While Pruning: Couldn't remove affected topics.", __LINE__, __FILE__);
		} // end if(!$result...
	} // End if $prune_topics
	return $pruned_topics;
} // End function prune.

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
	$sql = 'SELECT * 
		FROM ' . PRUNE_TABLE . " 
		WHERE forum_id = '$forum_id'";
	
	if(!$result = $db->sql_query($sql))
	{
		error_die(SQL_QUERY, "Auto-Prune: Couldn't read auto_prune table.", __LINE__, __FILE__);
	} // End if(!$result...
	while($row = $db->sql_fetchrow($result))
	{
		$forum_id = $row['forum_id'];
		$prune_date = time() - ($row['prune_days'] * $one_day);
		$pruned = prune($forum_id, $prune_date);
		$next_prune = time() + ($row['prune_freq'] * $one_day);
		$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET prune_next = '$next_prune'
			WHERE forum_id = '$forum_id'";
		if(!$db->sql_query($sql))
		{
			error_die(SQL_QUERY, "Auto-Prune: Couldn't update forum table.", __LINE__, __FILE__);
		} // End if(!$db->sql..
	} // End While Loop.
} // End auto_prune function.
