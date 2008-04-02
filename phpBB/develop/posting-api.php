<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

//$db = new dbal_mysqli();

class posting_api
{
	// ideas on global topics? I am stuck here :'-(
	/*
	topic table:
	topic_id
	forum_id
	topic_title
	topic_status

	sec:
	topic_posts
	topic_moved_posts
	topic_deleted_posts
	topic_unapproved_posts

	all must be approved:
	topic_poster
	topic_first_poster_name
	topic_first_poster_id
	topic_last_post_id
	topic_last_poster_name
	topic_last_poster_id
	topic_last_post_title
	topic_last_post_time
	*/
	
	/*
	post table:
	post_id
	topic_id
	post_subject
	post_body
	post_status
	post_username
	poster_id
	forum_id
	post_time
	
	/*
	forum table:
	forum_id
	forum_posts
	forum_moved_posts
	forum_deleted_posts
	forum_unapproved_posts

	sec:
	forum_topics
	forum_moved_topics
	forum_deleted_topics
	forum_unapproved_topics

	forum_last_poster_name
	forum_last_user_id
	forum_last_post_title
	forum_last_post_time
	*/

	const NORMAL = 0;
	const UNAPPROVED = 1;
	const DELETED = 2;
	const MOVED = 4;

	// we, for now, only support the insertion of posts that are not already
	// moved and not deleted (it must first exist for it to be moved or deleted!)
	function insert_topic($data)
	{
		global $db;

		// one transaction, we can now garuntee that atomicity of insertions
		$db->transaction('BEGIN');

		$user_id = (int) $data['user_id'];
		$forum_id = (int) $data['forum_id'];
		$topic_title = $data['title'];
		$post_contents = $data['post_contents'];
		$approved = $data['approved'];
		$time = ($data['time']) ? (int) $data['time'] : time();

		if (isset($data['username']))
		{
			$username = $data['username'];
		}
		else
		{
			$sql = 'SELECT username
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $user_id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$username = $row['username'];
			$db->sql_freeresult($result);
		}
		
		$sql = 'SELECT forum_topics, forum_unapproved_topics, forum_posts, forum_unapproved_posts
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $forum_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// everything starts out normal, one way or another
		$topic_status = self::NORMAL;

		// are we approved?
		if (!$approved)
		{
			// this topic is going to be unapproved
			$topic_status |= self::UNAPPROVED;
		}

		// throw our topic to the dogs
		$topic_data = array(
			'topic_title'				=> $topic_title,
			'topic_status'				=> $topic_status,
			'topic_posts'				=> 1,
			'topic_moved_posts'			=> 0,
			'topic_deleted_posts'		=> 0,
			'topic_unapproved_posts'	=> ($approved ? 0 : 1),
			'topic_first_poster_name'	=> $username,
			'topic_poster'				=> $user_id,
			'topic_last_username'		=> $username,
			'topic_last_post_title'		=> $topic_title,
			'topic_last_post_time'		=> $time,
			'topic_last_poster_id'		=> $user_id,
			'forum_id'					=> $forum_id
		);

		$db->sql_handle_data('INSERT', TOPICS_TABLE, $topic_data);
		$topic_id = $db->sql_nextid();

		// I suppose it is time to make us a post, no?
		$post_data = array(
			'topic_id'		=> $topic_id,
			'post_subject'	=> $topic_title,
			'post_body'		=> $post_contents,
			'post_username'	=> $username,
			'poster_id'		=> $user_id,
			'post_status'	=> $topic_status,
			'forum_id'		=> $forum_id
		);

		$db->sql_handle_data('INSERT', POSTS_TABLE, $post_data);
		$post_id = $db->sql_nextid();

		// time to fill in the blanks
		$db->sql_handle_data('UPDATE', TOPICS_TABLE, array('topic_first_post_id' => $post_id, 'topic_last_post_id' => $post_id), "topic_id = $topic_id");

		// let's go update the forum table
		$forum_data = array(
			'forum_posts'				=> ++$row['forum_posts'],
			'forum_topics'				=> ++$row['forum_topics'],
		);

		// the last post inserted is always the latest,
		// we must update the forum records to make sure everybody knows the good news
		if ($approved)
		{
			$forum_data['forum_last_poster_name']	= $username;
			$forum_data['forum_last_user_id']		= $user_id;
			$forum_data['forum_last_post_title']	= $topic_title;
			$forum_data['forum_last_post_time']		= $time;
		}
		else
		{
			$forum_data['forum_unapproved_posts']	= ++$row['forum_unapproved_posts'];
			$forum_data['forum_unapproved_topics']	= ++$row['forum_unapproved_topics'];
		}

		$db->sql_handle_data('UPDATE', FORUMS_TABLE, $forum_data, "forum_id = $forum_id");

		// we are consistant, victory is ours
		$db->transaction('END');
	}

	public function insert_post($data)
	{
		global $db;
		// one transaction, we can now garuntee that atomicity of insertions
		$db->transaction('BEGIN');

		$user_id = (int) $data['user_id'];
		$topic_id = (int) $data['topic_id'];

		// begin massive amounts of hand holding

		if (isset($data['forum_id']))
		{
			$forum_id = (int) $data['forum_id'];
		}
		else
		{
			$sql = 'SELECT forum_id
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . $topic_id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$forum_id = (int) $row['forum_id'];
			$db->sql_freeresult($result);
		}

		$post_title = $data['title'];
		$post_contents = $data['post_contents'];
		$approved = $data['approved'];
		$time = ($data['time']) ? (int) $data['time'] : time();

		if (isset($data['username']))
		{
			$username = $data['username'];
		}
		else
		{
			$sql = 'SELECT username
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $user_id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$username = $row['username'];
			$db->sql_freeresult($result);
		}

		// hand holding complete, lets write some posts

		$sql = 'SELECT forum_topics, forum_unapproved_topics, forum_posts, forum_unapproved_posts
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $forum_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// everything starts out normal, one way or another
		$post_status = self::NORMAL;

		// are we approved?
		if (!$approved)
		{
			// this topic is going to be unapproved
			$post_status |= self::UNAPPROVED;
		}

		// I suppose it is time to make us a post, no?
		$post_data = array(
			'topic_id'		=> $topic_id,
			'post_subject'	=> $post_title,
			'post_body'		=> $post_contents,
			'post_username'	=> $username,
			'poster_id'		=> $user_id,
			'post_status'	=> $post_status,
			'forum_id'		=> $forum_id,
		);
		$db->sql_handle_data('INSERT', POSTS_TABLE, $post_data);

		// what is the new post_id?
		$post_id = $db->sql_nextid();

		// iceberg ahead! we must only update the topic information if the post is approved ;)
		if ($approved)
		{
			// time to fill in the blanks
			$topics_data = array(
				'topic_last_poster_id'	=> $user_id,
				'topic_last_post_id'	=> $post_id,
				'topic_last_poster_name'=> $username,
				'topic_last_post_title'	=> $post_title,
				'topic_last_post_time'	=> $time,
			);
			$db->sql_handle_data('UPDATE', TOPICS_TABLE, $topics_data, "topic_id = $topic_id");
		}

		// let's go update the forum table
		$forum_data = array(
			'forum_posts'	=> ++$row['forum_posts'],
		);

		// the last post inserted is always the latest,
		// we must update the forum records to make sure everybody knows the good news
		if ($approved)
		{
			$forum_data['forum_last_poster_name']	= $username;
			$forum_data['forum_last_user_id']		= $user_id;
			$forum_data['forum_last_post_title']	= $post_title;
			$forum_data['forum_last_post_time']		= $time;
		}
		else
		{
			$forum_data['forum_unapproved_posts']	= ++$row['forum_unapproved_posts'];
		}

		$db->sql_handle_data('UPDATE', FORUMS_TABLE, $forum_data, "forum_id = $forum_id");

		// we are consistant, victory is ours
		$db->transaction('END');
	}

	function move_topic($data)
	{
		global $db;

		// lets get this party started
		$db->transaction('BEGIN');

		$topic_id = (int) $data['topic_id'];
		$to_forum_id = (int) $data['forum_id'];

		// let us first determine how many items we are removing from the pool
		$sql = 'SELECT topic_posts, topic_moved_posts, topic_deleted_posts, topic_unapproved_posts, forum_id as from_forum_id, topic_status
			FROM ' . TOPICS_TABLE. '
			WHERE topic_id = ' . $topic_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);

		$topic_status = (int) $row['topic_status'];
		$from_forum_id = (int) $row['from_forum_id'];

		$topic_row['topic_posts']				= (int) $row['topic_posts'];
		$topic_row['topic_moved_posts']			= (int) $row['topic_moved_posts'];
		$topic_row['topic_deleted_posts']		= (int) $row['topic_deleted_posts'];
		$topic_row['topic_unapproved_posts']	= (int) $row['topic_unapproved_posts'];

		$db->sql_freeresult($result);

		// let us first determine how many items we are removing from the pool
		$sql = 'SELECT forum_posts, forum_moved_posts, forum_deleted_posts, forum_unapproved_posts, forum_id, forum_topics, forum_deleted_topics, forum_unapproved_topics
			FROM ' . FORUMS_TABLE. '
			WHERE ' . $db->sql_in_set('forum_id', array($to_forum_id, $from_forum_id));
		$result = $db->sql_query($sql);

		$forum_row = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$forum_id = (int) $row['forum_id'];

			$forum_row[$forum_id]['forum_posts']			= (int) $row['forum_posts'];
			$forum_row[$forum_id]['forum_moved_posts']		= (int) $row['forum_moved_posts'];
			$forum_row[$forum_id]['forum_deleted_posts']	= (int) $row['forum_deleted_posts'];
			$forum_row[$forum_id]['forum_unapproved_posts']	= (int) $row['forum_unapproved_posts'];

			$forum_row[$forum_id]['forum_topics']				= (int) $row['forum_topics'];
			$forum_row[$forum_id]['forum_moved_topics']			= (int) $row['forum_moved_topics'];
			$forum_row[$forum_id]['forum_deleted_topics']		= (int) $row['forum_deleted_topics'];
			$forum_row[$forum_id]['forum_unapproved_topics']	= (int) $row['forum_unapproved_topics'];
		}

		$db->sql_freeresult($result);

		// update the topic itself
		$db->sql_handle_data('UPDATE', TOPICS_TABLE, array('forum_id' => $to_forum_id), "topic_id = $topic_id");

		// update the posts now
		$db->sql_handle_data('UPDATE', POSTS_TABLE, array('forum_id' => $to_forum_id), "forum_id = $topic_id");		

		// remove the numbers from the old forum row
		$from_forum_array = array(
			'forum_posts'				=> $forum_row[$from_forum_id]['forum_posts'] - $topic_id['forum_posts'],
			'forum_moved_posts'			=> $forum_row[$from_forum_id]['forum_moved_posts'] - $topic_id['forum_moved_posts'],
			'forum_deleted_posts'		=> $forum_row[$from_forum_id]['forum_deleted_posts'] - $topic_id['forum_deleted_posts'],
			'forum_unapproved_posts'	=> $forum_row[$from_forum_id]['forum_unapproved_posts'] - $topic_id['forum_unapproved_posts'],

			'forum_topics'				=> $forum_row[$from_forum_id]['forum_topics'] - 1,
			'forum_moved_topics'		=> $forum_row[$from_forum_id]['forum_moved_topics'] - (($topic_status & self::MOVED) ? 1 : 0),
			'forum_deleted_topics'		=> $forum_row[$from_forum_id]['forum_deleted_topics'] - (($topic_status & self::DELETED) ? 1 : 0),
			'forum_unapproved_topics'	=> $forum_row[$from_forum_id]['forum_unapproved_topics'] - (($topic_status & self::UNAPPROVED) ? 1 : 0),
		);

		// get the last "normal" post in the old forum, we _must_ update it
		$sql = 'SELECT MAX(post_id) as max_post_id
			FROM ' . POSTS_TABLE . '
			WHERE post_status = ' . self::NORMAL . '
				AND forum_id = ' . $from_forum_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// anything left?
		if ($row)
		{
			// OK, lets go do some magick
			$sql = 'SELECT post_username, poster_id, post_subject, post_time
				FROM '. POSTS_TABLE . '
				WHERE post_id = ' . (int) $row['max_post_id'];
			$result = $db->sql_query($sql);
			$last_post = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$from_forum_array['forum_last_user_id'] = (int) $last_post['poster_id'];
			$from_forum_array['forum_last_poster_name'] = (int) $last_post['post_username'];
			$from_forum_array['forum_last_post_title'] = (int) $last_post['post_subject'];
			$from_forum_array['forum_last_post_time'] = (int) $last_post['post_time'];
		}

		// update the old forum
		$db->sql_handle_data('UPDATE', FORUMS_TABLE, $from_forum_array, "forum_id = $from_forum_id");

		// add the numbers to the new forum row
		$to_forum_array = array(
			'forum_posts'			=> $forum_row[$to_forum_id]['forum_posts'] - $topic_id['forum_posts'],
			'forum_moved_posts'		=> $forum_row[$to_forum_id]['forum_moved_posts'] - $topic_id['forum_moved_posts'],
			'forum_deleted_posts'	=> $forum_row[$to_forum_id]['forum_deleted_posts'] - $topic_id['forum_deleted_posts'],
			'forum_unapproved_posts'=> $forum_row[$to_forum_id]['forum_unapproved_posts'] - $topic_id['forum_unapproved_posts'],

			'forum_topics'				=> $forum_row[$from_forum_id]['forum_topics'] + 1,
			'forum_moved_topics'		=> $forum_row[$from_forum_id]['forum_moved_topics'] + (($topic_status & self::MOVED) ? 1 : 0),
			'forum_deleted_topics'		=> $forum_row[$from_forum_id]['forum_deleted_topics'] + (($topic_status & self::DELETED) ? 1 : 0),
			'forum_unapproved_topics'	=> $forum_row[$from_forum_id]['forum_unapproved_topics'] + (($topic_status & self::UNAPPROVED) ? 1 : 0),
		);

		// the new topic is approved and is not soft deleted and is not moved, go and sync some status
		if ($topic_status === self::NORMAL)
		{
			// get the lastest "normal" post in the new forum, we _must_ update it
			$sql = 'SELECT MAX(post_id) as max_post_id
				FROM ' . POSTS_TABLE . '
				WHERE post_status = ' . self::NORMAL . '
					AND forum_id = ' . $to_forum_id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
	
			// anything left?
			if ($row)
			{
				// OK, lets go do some magick
				$sql = 'SELECT post_username, poster_id, post_subject, post_time
					FROM '. POSTS_TABLE . '
					WHERE post_id = ' . (int) $row['max_post_id'];
				$result = $db->sql_query($sql);
				$last_post = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
	
				$from_forum_array['forum_last_user_id'] = (int) $last_post['poster_id'];
				$from_forum_array['forum_last_poster_name'] = (int) $last_post['post_username'];
				$from_forum_array['forum_last_post_title'] = (int) $last_post['post_subject'];
				$from_forum_array['forum_last_post_time'] = (int) $last_post['post_time'];
			}
		}

		// update the new forum
		$db->sql_handle_data('UPDATE', FORUMS_TABLE, $to_forum_array, "forum_id = $to_forum_id");

		// in hundreds of fewer lines of code, we have now moved a topic
		// (this totally ignores the shadow topic thingy, I do not care for now)
		$db->transaction('COMMIT');
	}
}

?>