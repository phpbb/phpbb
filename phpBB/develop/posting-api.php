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
	topic_type
	topic_shadow_id // to implement

	sec:
	topic_posts
	topic_shadow_posts
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
	post_type
	post_username
	poster_id
	forum_id
	post_time
	
	/*
	forum table:
	forum_id
	forum_posts
	forum_shadow_posts
	forum_deleted_posts
	forum_unapproved_posts

	sec:
	forum_topics
	forum_shadow_topics
	forum_deleted_topics
	forum_unapproved_topics

	forum_last_poster_name
	forum_last_user_id
	forum_last_post_title
	forum_last_post_time
	*/

	const NORMAL = 0;

	// status
	const UNAPPROVED = 1;
	const DELETED = 2;

	// type
	const ANNOUNCEMENT = 1;
	const STICKY = 2;

	// we, for now, only support the insertion of posts that are not already
	// shadow'd and not deleted (it must first exist for it to be shadow'd or deleted!)
	static function insert_topic($data)
	{
		global $db;

		// one transaction, we can now garuntee that atomicity of insertions
		$db->sql_transaction('begin');

		$user_id = (int) $data['user_id'];
		$forum_id = (int) $data['forum_id'];
		$topic_title = $data['title'];
		$post_contents = $data['post_contents'];
		$topic_status = (int) $data['status'];
		$topic_type = (int) $data['type'];

		$shadow_forums = $data['shadow_forums'];

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
			$username = (string) $db->sql_fetchfield('username');
			$db->sql_freeresult($result);
		}
		
		$sql = 'SELECT forum_topics, forum_unapproved_topics, forum_posts, forum_unapproved_posts
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $forum_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// throw our topic to the dogs
		$topic_data = array(
			'topic_title'				=> $topic_title,
			'topic_status'				=> $topic_status,
			'topic_type'				=> $topic_type,
			'topic_posts'				=> 1,
			'topic_shadow_posts'		=> 0,
			'topic_deleted_posts'		=> 0,
			'topic_unapproved_posts'	=> ($approved) ? 0 : 1,
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
			'post_status'	=> $topic_status, // first post inherits its type from the topic
			'post_type'		=> self::NORMAL, // for now, there are no shadow, global or sticky posts
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

		foreach ($shadow_forums as $shadow_forum_id)
		{
			$data['shadow_topic_id'] = $topic_id;
			$data['forum_id'] = $forum_id;
			self::insert_shadow_topic($data); 
		}

		// we are consistant, victory is ours
		$db->sql_transaction('commit');
	}

	// inserts a shadow topic into the database
	static function insert_shadow_topic($data)
	{
		global $db;

		// one transaction, we can now garuntee that atomicity of insertions
		$db->sql_transaction('begin');

		$user_id = (int) $data['user_id'];
		$forum_id = (int) $data['forum_id'];
		$topic_title = $data['title'];
		$post_contents = $data['post_contents'];
		$topic_status = (int) $data['status'];
		$topic_type = (int) $data['type'];
		$time = ($data['time']) ? (int) $data['time'] : time();
		$shadow_topic_id = (int) $data['shadow_topic_id'];

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
		
		$sql = 'SELECT forum_topics, forum_shadow_topics
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $forum_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($query);
		$db->sql_freeresult($result);

		// throw our topic to the dogs
		$topic_data = array(
			'topic_title'				=> $topic_title,
			'topic_status'				=> $topic_status,
			'topic_type'				=> $topic_type,
			'topic_posts'				=> 0,
			'topic_shadow_posts'		=> 0,
			'topic_deleted_posts'		=> 0,
			'topic_unapproved_posts'	=> ($approved ? 0 : 1),
			'topic_first_poster_name'	=> $username,
			'topic_poster'				=> $user_id,
			'topic_last_username'		=> $username,
			'topic_last_post_title'		=> $topic_title,
			'topic_last_post_time'		=> $time,
			'topic_last_poster_id'		=> $user_id,
			'forum_id'					=> $forum_id,
			'topic_shadow_id'			=> $shadow_topic_id
		);

		$db->sql_handle_data('INSERT', TOPICS_TABLE, $topic_data);

		// let's go update the forum table
		$forum_data = array(
			'forum_topics'				=> ++$row['forum_topics'],
			'forum_shadow_topics'		=> ++$row['forum_shadow_topics']
		);

		// an unapproved shadow topic? I suppose...
		if (!$approved)
		{
			$forum_data['forum_unapproved_topics']	= ++$row['forum_unapproved_topics'];
		}

		$db->sql_handle_data('UPDATE', FORUMS_TABLE, $forum_data, "forum_id = $forum_id");

		// we are consistant, victory is ours
		$db->transaction('END');
	}

	static function insert_post($data)
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
			$forum_id = (int) $db->sql_fetchfield('forum_id');
			$db->sql_freeresult($result);
		}

		$post_title = $data['title'];
		$post_contents = $data['post_contents'];
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
			$username = (string) $db->sql_fetchfield('username');
			$db->sql_freeresult($result);
		}

		// hand holding complete, lets write some posts

		$sql = 'SELECT forum_topics, forum_unapproved_topics, forum_posts, forum_unapproved_posts
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $forum_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$post_status = (int) $data['post_status'];
		$approved = ($post_status === self::NORMAL);

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
		$db->sql_transaction('commit');
	}

	static function delete_topic($data)
	{
		global $db;

		// lets get this party started
		$db->sql_transaction('begin');

		$topic_id = (int) $data['topic_id'];

		// what kind of topic is this? lets find out how much we must tamper with the forum table...
		$sql = 'SELECT topic_posts, topic_shadow_posts, topic_deleted_posts, topic_unapproved_posts, topic_shadow_id, forum_id
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . $topic_id;
		$result = $db->sql_query($sql);
		$topic_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// goodnight topic
		$db->sql_query('DELETE FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . $topic_id);

		// goodnight post
		$db->sql_query('DELETE FROM ' . POSTS_TABLE . ' WHERE topic_id = ' . $topic_id);

		$forum_id = (int) $topic_row['forum_id'];

		// what kind of topic is this? lets find out how much we must tamper with the forum table...
		$sql = 'SELECT forum_posts, forum_shadow_posts, forum_deleted_posts, forum_unapproved_posts
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . $forum_id;
		$result = $db->sql_query($sql);
		$forum_row = $db->sql_fetchrow($result);

		$forum_array = array(
			'forum_posts'				=> max($forum_row['forum_posts'] - $topic_id['forum_posts'], 0),
			'forum_shadow_posts'		=> max($forum_row['forum_shadow_posts'] - $topic_id['forum_shadow_posts'], 0),
			'forum_deleted_posts'		=> max($forum_row['forum_deleted_posts'] - $topic_id['forum_deleted_posts'], 0),
			'forum_unapproved_posts'	=> max($forum_row['forum_unapproved_posts'] - $topic_id['forum_unapproved_posts'], 0),

			'forum_topics'				=> max($forum_row['forum_topics'] - 1, 0),
			'forum_shadow_topics'		=> max($forum_row['forum_shadow_topics'] - (($topic_type == self::SHADOW) ? 1 : 0), 0),
			'forum_deleted_topics'		=> max($forum_row['forum_deleted_topics'] - (($topic_status == self::DELETED) ? 1 : 0), 0),
			'forum_unapproved_topics'	=> max($forum_row['forum_unapproved_topics'] - (($topic_row['topic_shadow_id'] != 0) ? 1 : 0), 0),
		);

		// get the last "normal" post in the forum, we _must_ update it
		$sql = 'SELECT MAX(post_id) as max_post_id
			FROM ' . POSTS_TABLE . '
			WHERE post_status = ' . self::NORMAL . '
				AND forum_id = ' . $forum_id;
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

			$forum_array['forum_last_user_id'] = (int) $last_post['poster_id'];
			$forum_array['forum_last_poster_name'] = $last_post['post_username'];
			$forum_array['forum_last_post_title'] = $last_post['post_subject'];
			$forum_array['forum_last_post_time'] = (int) $last_post['post_time'];
		}
		else
		{
			// reset forum state
			$forum_array['forum_last_user_id'] = 0;
			$forum_array['forum_last_poster_name'] = '';
			$forum_array['forum_last_post_title'] = '';
			$forum_array['forum_last_post_time'] = 0;
		}

		$db->sql_handle_data('UPDATE', FORUMS_TABLE, $forum_data, "forum_id = $forum_id");

		// lastly, kill off all the unbelievers... erm, I mean shadow topics...
		$sql = 'SELECT topic_id, forum_id
			FROM ' . TOPICS_TABLE . '
			WHERE topic_shadow_id = ' . $topic_id;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$data['topic_id'] = (int) $topic_id;
			self::delete_topic($data);
		}

		// goodnight, moon
		$db->transaction('COMMIT');
	}

	static function move_topic($data)
	{
		global $db;

		// lets get this party started
		$db->transaction('BEGIN');

		$topic_id = (int) $data['topic_id'];
		$to_forum_id = (int) $data['forum_id'];
		$make_shadow = (bool) $data['make_shadow'];

		// let us first determine how many items we are removing from the pool
		$sql = 'SELECT topic_posts, topic_shadow_posts, topic_deleted_posts, topic_unapproved_posts, forum_id as from_forum_id, topic_status, topic_type, topic_shadow_id
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . $topic_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$topic_status = (int) $row['topic_status'];
		$topic_type = (int) $row['topic_type'];
		$from_forum_id = (int) $row['from_forum_id'];

		$topic_row['topic_posts']				= (int) $row['topic_posts'];
		$topic_row['topic_shadow_posts']		= (int) $row['topic_shadow_posts'];
		$topic_row['topic_deleted_posts']		= (int) $row['topic_deleted_posts'];
		$topic_row['topic_unapproved_posts']	= (int) $row['topic_unapproved_posts'];

		// let us first determine how many items we are removing from the pool
		$sql = 'SELECT forum_posts, forum_shadow_posts, forum_deleted_posts, forum_unapproved_posts, forum_id, forum_topics, forum_deleted_topics, forum_unapproved_topics
			FROM ' . FORUMS_TABLE . '
			WHERE ' . $db->sql_in_set('forum_id', array($to_forum_id, $from_forum_id));
		$result = $db->sql_query($sql);

		$forum_row = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$forum_id = (int) $row['forum_id'];

			$forum_row[$forum_id]['forum_posts']			= (int) $row['forum_posts'];
			$forum_row[$forum_id]['forum_shadow_posts']		= (int) $row['forum_shadow_posts'];
			$forum_row[$forum_id]['forum_deleted_posts']	= (int) $row['forum_deleted_posts'];
			$forum_row[$forum_id]['forum_unapproved_posts']	= (int) $row['forum_unapproved_posts'];

			$forum_row[$forum_id]['forum_topics']				= (int) $row['forum_topics'];
			$forum_row[$forum_id]['forum_shadow_topics']		= (int) $row['forum_shadow_topics'];
			$forum_row[$forum_id]['forum_deleted_topics']		= (int) $row['forum_deleted_topics'];
			$forum_row[$forum_id]['forum_unapproved_topics']	= (int) $row['forum_unapproved_topics'];
		}

		$db->sql_freeresult($result);

		// update the topic itself
		$db->sql_handle_data('UPDATE', TOPICS_TABLE, array('forum_id' => $to_forum_id), "topic_id = $topic_id");

		// update the posts now
		$db->sql_handle_data('UPDATE', POSTS_TABLE, array('forum_id' => $to_forum_id), "topic_id = $topic_id");

		// remove the numbers from the old forum row
		$from_forum_array = array(
			'forum_posts'				=> max($forum_row[$from_forum_id]['forum_posts'] - $topic_row['forum_posts'], 0),
			'forum_shadow_posts'		=> max($forum_row[$from_forum_id]['forum_shadow_posts'] - $topic_row['forum_shadow_posts'], 0),
			'forum_deleted_posts'		=> max($forum_row[$from_forum_id]['forum_deleted_posts'] - $topic_row['forum_deleted_posts'], 0),
			'forum_unapproved_posts'	=> max($forum_row[$from_forum_id]['forum_unapproved_posts'] - $topic_row['forum_unapproved_posts'], 0),

			'forum_topics'				=> max($forum_row[$from_forum_id]['forum_topics'] - 1, 0),
			'forum_shadow_topics'		=> max($forum_row[$from_forum_id]['forum_shadow_topics'] - (($topic_row['topic_shadow_id'] != 0) ? 1 : 0), 0),
			'forum_deleted_topics'		=> max($forum_row[$from_forum_id]['forum_deleted_topics'] - (($topic_status == self::DELETED) ? 1 : 0), 0),
			'forum_unapproved_topics'	=> max($forum_row[$from_forum_id]['forum_unapproved_topics'] - (($topic_status == self::UNAPPROVED) ? 1 : 0), 0),
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
			$from_forum_array['forum_last_poster_name'] = $last_post['post_username'];
			$from_forum_array['forum_last_post_title'] = $last_post['post_subject'];
			$from_forum_array['forum_last_post_time'] = (int) $last_post['post_time'];
		}
		else
		{
			// reset forum state
			$from_forum_array['forum_last_user_id'] = 0;
			$from_forum_array['forum_last_poster_name'] = '';
			$from_forum_array['forum_last_post_title'] = '';
			$from_forum_array['forum_last_post_time'] = 0;
		}

		// update the old forum
		$db->sql_handle_data('UPDATE', FORUMS_TABLE, $from_forum_array, "forum_id = $from_forum_id");

		// add the numbers to the new forum row
		$to_forum_array = array(
			'forum_posts'			=> $forum_row[$to_forum_id]['forum_posts'] + $topic_row['forum_posts'],
			'forum_shadow_posts'	=> $forum_row[$to_forum_id]['forum_shadow_posts'] + $topic_row['forum_shadow_posts'],
			'forum_deleted_posts'	=> $forum_row[$to_forum_id]['forum_deleted_posts'] + $topic_row['forum_deleted_posts'],
			'forum_unapproved_posts'=> $forum_row[$to_forum_id]['forum_unapproved_posts'] + $topic_row['forum_unapproved_posts'],

			'forum_topics'				=> $forum_row[$from_forum_id]['forum_topics'] + 1,
			'forum_shadow_topics'		=> $forum_row[$from_forum_id]['forum_shadow_topics'] + (($topic_row['topic_shadow_id'] != 0) ? 1 : 0),
			'forum_deleted_topics'		=> $forum_row[$from_forum_id]['forum_deleted_topics'] + (($topic_status === self::DELETED) ? 1 : 0),
			'forum_unapproved_topics'	=> $forum_row[$from_forum_id]['forum_unapproved_topics'] + (($topic_status === self::UNAPPROVED) ? 1 : 0),
		);

		// the new topic is approved and is not soft deleted and is not unapproved, go and sync some status
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
	
			// we better find something... after all, we just moved a topic here!
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
				$from_forum_array['forum_last_poster_name'] = $last_post['post_username'];
				$from_forum_array['forum_last_post_title'] = $last_post['post_subject'];
				$from_forum_array['forum_last_post_time'] = (int) $last_post['post_time'];
			}
		}

		// update the new forum
		$db->sql_handle_data('UPDATE', FORUMS_TABLE, $to_forum_array, "forum_id = $to_forum_id");

		if ($make_shadow === true)
		{
			$data['shadow_topic_id'] = $topic_id;
			$data['forum_id'] = $from_forum_id;
			self::insert_shadow_topic($data);
		}

		$db->sql_transaction('commit');
	}
}

?>