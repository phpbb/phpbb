<?php
$phpbb_root_path = "../";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/post.'.$phpEx);
include($phpbb_root_path . 'includes/bbcode.'.$phpEx);

srand ((double) microtime() * 1000000);
set_time_limit(240*60);

// Here's the text we stick in posts..
$bigass_text = ' 
phpBB BBCode test suite v0.0.2
auto-linkification:
http://something.com
www.something.com
nate@phpbb.com
http://something.com/foo.php?this=that&theother=some%20encoded%20string is a link.
[code]
Simple code block with some <html> <tags>.
[/code]
[b]bolded[/b], [i]italic[/i]
[email]james@totalgeek.org[/email]
[url=http://www.totalgeek.org]totalgeek.org[/url]
[url]www.totalgeek.org[/url] 
[list] 
[*] This is the first bulleted item.
[*] This is the second bulleted item.
[/list] 
[list=A] 
[*] This is the first bulleted item.
[*] This is the second bulleted item.
[/list] 
[quote]
And a quote!
[/quote]
';


// The script expects the ID's in the tables to sequential (1,2,3,4,5), 
// so no holes please (1,4,5,8)...
$nr_of_users  = nrof(USERS_TABLE);
$nr_of_cats   = nrof(CATEGORIES_TABLE);
$nr_of_forums = nrof(FORUMS_TABLE);
$nr_of_posts  = nrof(POSTS_TABLE);

$u = $users;

$starttime = microtime();

while($users > 0)
{

	$name = "testuser_" . substr(md5(uniqid(rand())), 0, 10);
	if (make_user($name))
	{
		echo "Created user: $name <br>\n";
		flush();
	}
	$users--;
}

if ($posts > 0)
{
	filldb($posts);
}

$endtime = microtime();

if ($submit="" || !isset($submit))
{
    ?>
Hello, welcome to this little phpBB Benchmarking script :)<p>

At the moment there are:<br>

<table>
<tr><td align="right"><?php echo $nr_of_users?></td><td>Users</td></tr>
<tr><td align="right"><?php echo $nr_of_forums?></td><td>Forums</td></tr>
<tr><td align="right"><?php echo $nr_of_posts?></td><td>Posts</td></tr>
</table>
<p>
What do you want to create?<p>

<form method="get" action="<?php echo $PHP_SELF?>">
<input type="text" name="users" size="3"> Users<br>
<input type="text" name="posts" size="3"> Posts/topics (optional: post size in <input type="text" name="size" size="3"> bytes)<br>
<input type="submit" name="submit">
</form>

    <?php
} 
else 
{

	list ($starttime_msec,$starttime_sec) = explode(" ",$starttime);
	list ($endtime_msec,$endtime_sec) = explode(" ",$endtime);
	$timetaken_sec = ($endtime_sec+$endtime_msec) - ($starttime_sec+$starttime_msec);
	print "<B>TIME TAKEN : ".$timetaken_sec."s</B><BR>\n"; 

	print "<p>\n<a href=\"$PHP_SELF\">Back to the overview page</a>\n";
}


function filldb($newposts)
{
	global $nr_of_forums;
	global $nr_of_users;
  
	$forum_topic_counts = array();
  
	for ($i = 1; $i <= $nr_of_forums; $i++)
	{
  		$forum_topic_counts[$i] = get_topic_count($i);
	}
  
	for($i = 0; $i < $newposts; $i++)
	{
		$userid   = rand(2, $nr_of_users - 1);
		$forum    = rand(1,$nr_of_forums);
		
		if ((rand(0,30) < 1) || ($forum_topic_count[$forum] == 0))
		{
			// create a new topic 1 in 30 times (or when there are none);
			$topic = make_topic($userid, "Testing topic $i", $forum);
			$forum_topic_count[$forum]++;
		} 
		else 
		{
			// Otherwise create a reply(posting) somewhere.
			$topic = get_smallest_topic($forum);
			create_posting($userid, $topic, $forum, "reply");
		}
		
		if (($i % 1000) == 0)
		{
			echo "ping.pong.<br>";
			flush();
		}
	 
	}
}


function get_smallest_topic($forum_id)
{
	global $db;
	
	$sql = "SELECT topic_id
		FROM " . TOPICS_TABLE . "
		WHERE (forum_id = $forum_id)
		ORDER BY topic_replies ASC LIMIT 1";
	if($result = $db->sql_query($sql))
	{
		$row = $db->sql_fetchrow($result);
		$topic_id = $row['topic_id'];

		unset($result);
		unset($row);
		return $topic_id;
	}
	else
	{
		message_die(GENERAL_ERROR, "Couldn't get smallest topic.", "", __LINE__, __FILE__, $sql);
	}
	
}


function get_topic_count($forum_id)
{
	global $db;
	
	$sql = "SELECT forum_topics
		FROM " . FORUMS_TABLE . "
		WHERE (forum_id = $forum_id)";
	if($result = $db->sql_query($sql))
	{
		$row = $db->sql_fetchrow($result);
		$topic_count = $row['forum_topics'];

		unset($result);
		unset($row);
		return $topic_count;
	}
	else
	{
		message_die(GENERAL_ERROR, "Couldn't get topic count.", "", __LINE__, __FILE__, $sql);
	}
	
}


function make_topic($user_id, $subject, $forum_id)
{
	global $db;
	$topic_type = POST_NORMAL;
	$topic_vote = 0;
	$current_time = time();
	
	$sql  = "INSERT INTO " . TOPICS_TABLE . " (topic_title, topic_poster, topic_time, forum_id, topic_status, topic_type, topic_vote)
			VALUES ('$subject', $user_id, $current_time, $forum_id, " . TOPIC_UNLOCKED . ", $topic_type, $topic_vote)";

	if( $result = $db->sql_query($sql, BEGIN_TRANSACTION) )
	{
		$new_topic_id = $db->sql_nextid();
	}
	else
	{
		message_die(GENERAL_ERROR, "Error inserting data into topics table", "", __LINE__, __FILE__, $sql);
	}
	
	create_posting($user_id, $new_topic_id, $forum_id);
	
	return $new_topic_id;
}



function create_posting($userid, $topic_id, $forum, $mode='newtopic')
{
	$message = generatepost();

	return make_post($topic_id, $forum, $userid, "", $message, $mode);

}



function make_post($new_topic_id, $forum_id, $user_id, $post_username, $text, $mode='newtopic')
{
	global $db;
	$current_time = time();
	$user_ip = "ac100202";
	$bbcode_on = 1;
	$html_on = 1;
	$smilies_on = 1;
	$attach_sig = 1;
	$bbcode_uid = make_bbcode_uid();
	
	$post_subject = 'random subject';
	
	$post_message = prepare_message($text, $html_on, $bbcode_on, $smilies_on, $bbcode_uid);	
	
	$sql = "INSERT INTO " . POSTS_TABLE . " (topic_id, forum_id, poster_id, post_username, post_time, poster_ip, bbcode_uid, enable_bbcode, enable_html, enable_smilies, enable_sig)
		VALUES ($new_topic_id, $forum_id, $user_id, '$post_username', $current_time, '$user_ip', '$bbcode_uid', $bbcode_on, $html_on, $smilies_on, $attach_sig)";
	$result = $db->sql_query($sql);
	
	if($result)
	{
		$new_post_id = $db->sql_nextid();
	
		$sql = "INSERT INTO " . POSTS_TEXT_TABLE . " (post_id, post_subject, post_text)
			VALUES ($new_post_id, '$post_subject', '$post_message')";
	
		if($db->sql_query($sql))
		{
			$sql = "UPDATE " . TOPICS_TABLE . "
				SET topic_last_post_id = $new_post_id";
			if($mode == "reply")
			{
				$sql .= ", topic_replies = topic_replies + 1 ";
			}
			$sql .= " WHERE topic_id = $new_topic_id";
	
			if($db->sql_query($sql))
			{
				$sql = "UPDATE " . FORUMS_TABLE . "
					SET forum_last_post_id = $new_post_id, forum_posts = forum_posts + 1";
				if($mode == "newtopic")
				{
					$sql .= ", forum_topics = forum_topics + 1";
				}
				$sql .= " WHERE forum_id = $forum_id";
	
				if($db->sql_query($sql))
				{
					$sql = "UPDATE " . USERS_TABLE . "
						SET user_posts = user_posts + 1
						WHERE user_id = " . $user_id;
	
					if($db->sql_query($sql, END_TRANSACTION))
					{
						// SUCCESS.
						return true;
					}
					else
					{
						message_die(GENERAL_ERROR, "Error updating users table", "", __LINE__, __FILE__, $sql);
					}
				}
				else
				{
					message_die(GENERAL_ERROR, "Error updating forums table", "", __LINE__, __FILE__, $sql);
				}
			}
			else
			{
				message_die(GENERAL_ERROR, "Error updating topics table", "", __LINE__, __FILE__, $sql);
			}
		}
		else
		{
			// Rollback
			if(SQL_LAYER == "mysql")
			{
				$sql = "DELETE FROM " . POSTS_TABLE . "
					WHERE post_id = $new_post_id";
				$db->sql_query($sql);
			}
			message_die(GENERAL_ERROR, "Error inserting data into posts text table", "", __LINE__, __FILE__, $sql);
		}
	}
	else
	{
		message_die(GENERAL_ERROR, "Error inserting data into posts table", "", __LINE__, __FILE__, $sql);
	}	

}


function generatepost($size=850)
{
   global $bigass_text;
   // Returns a string with a length between $size and $size*0.2
   $size = rand(0.2*$size, $size);
   
   $textsize = strlen($bigass_text);
   $currentsize = 0;
   // Add whole $text multiple times
   while($currentsize < $size && $size-$currentsize <= $textsize)
   {
      $message .= $bigass_text;
      $currentsize += $textsize;
   }
   // Add the remainder number of chars and return it.
   $message .= substr($bigass_text, 0, $size-$currentsize);

   return (addslashes($message));
}
   
      
function nrof($table)
{
	global $db;
	$sql = "SELECT count(*) AS counted FROM $table";
	$result = $db->sql_query($sql);
	$topics = $db->sql_fetchrow($result);
	return $topics[counted];
}


function make_user($username)
{
	global $db, $board_config;

	$password = md5("benchpass");
	$email = "nobody@localhost";
	$icq = "12345678";
	$website = "http://www.phpbb.com";
	$occupation = "phpBB tester";
	$location = "phpBB world hq";
	$interests = "Eating, sleeping, living, and breathing phpBB";
	$signature = "$username: phpBB tester.";
	$signature_bbcode_uid = "";
	$avatar_filename = "";
	$viewemail = 0;
	$aim = 0;
	$yim = 0;
	$msn = 0;
	$attachsig = 1;
	$allowsmilies = 1;
	$allowhtml = 1;
	$allowbbcode = 1;
	$allowviewonline = 1;
	$notifyreply = 0;
	$notifypm = 0;
	$user_timezone = $board_config['board_timezone'];
	$user_dateformat = $board_config['default_dateformat'];
	$user_lang = $board_config['default_lang'];
	$user_style = $board_config['default_style'];


	$sql = "SELECT MAX(user_id) AS total
		FROM " . USERS_TABLE;
	if($result = $db->sql_query($sql))
	{
		$row = $db->sql_fetchrow($result);
		$new_user_id = $row['total'] + 1;

		unset($result);
		unset($row);
	}
	else
	{
		message_die(GENERAL_ERROR, "Couldn't obtained next user_id information.", "", __LINE__, __FILE__, $sql);
	}

	$sql = "SELECT MAX(group_id) AS total
		FROM " . GROUPS_TABLE;
	if($result = $db->sql_query($sql))
	{
		$row = $db->sql_fetchrow($result);
		$new_group_id = $row['total'] + 1;

		unset($result);
		unset($row);
	}
	else
	{
		message_die(GENERAL_ERROR, "Couldn't obtained next user_id information.", "", __LINE__, __FILE__, $sql);
	}


	$sql = "INSERT INTO " . USERS_TABLE . "	(user_id, username, user_regdate, user_password, user_email, user_icq, user_website, user_occ, user_from, user_interests, user_sig, user_sig_bbcode_uid, user_avatar, user_viewemail, user_aim, user_yim, user_msnm, user_attachsig, user_allowsmile, user_allowhtml, user_allowbbcode, user_allow_viewonline, user_notify, user_notify_pm, user_timezone, user_dateformat, user_lang, user_style, user_level, user_allow_pm, user_active, user_actkey)
		VALUES ($new_user_id, '$username', " . time() . ", '$password', '$email', '$icq', '$website', '$occupation', '$location', '$interests', '$signature', '$signature_bbcode_uid', '$avatar_filename', $viewemail, '$aim', '$yim', '$msn', $attachsig, $allowsmilies, $allowhtml, $allowbbcode, $allowviewonline, $notifyreply, $notifypm, $user_timezone, '$user_dateformat', '$user_lang', $user_style, 0, 1, ";

	
	$sql .= "1, '')";
	
	if($result = $db->sql_query($sql, BEGIN_TRANSACTION))
	{
		$sql = "INSERT INTO " . GROUPS_TABLE . " (group_id, group_name, group_description, group_single_user, group_moderator)
			VALUES ($new_group_id, '', 'Personal User', 1, 0)";
		if($result = $db->sql_query($sql))
		{
			$sql = "INSERT INTO " . USER_GROUP_TABLE . " (user_id, group_id, user_pending)
				VALUES ($new_user_id, $new_group_id, 0)";
			if($result = $db->sql_query($sql, END_TRANSACTION))
			{
				
				// SUCCESS.
				return true;
			}
			else
			{
				message_die(GENERAL_ERROR, "Couldn't insert data into user_group table", "", __LINE__, __FILE__, $sql);
			}
		}
		else
		{
			message_die(GENERAL_ERROR, "Couldn't insert data into groups table", "", __LINE__, __FILE__, $sql);
		}
	}
	else
	{
		message_die(GENERAL_ERROR, "Couldn't insert data into users table", "", __LINE__, __FILE__, $sql);
	}

}



 
  
?>
