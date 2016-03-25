<?php

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");

//
// Do not change anything below this line.
//
set_time_limit(0);

define('IN_PHPBB', true);
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$search_type = $config['search_type'];

if (!class_exists($search_type))
{
	trigger_error('NO_SUCH_SEARCH_MODULE');
}

$error = false;
$search = new $search_type($error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, $phpbb_dispatcher);

if ($error)
{
	trigger_error($error);
}

print "<html>\n<body>\n";

//
// Fetch a batch of posts_text entries
//
$sql = "SELECT COUNT(*) as total, MAX(post_id) as max_post_id
	FROM ". POSTS_TABLE;
if ( !($result = $db->sql_query($sql)) )
{
	$error = $db->sql_error();
	die("Couldn't get maximum post ID :: " . $sql . " :: " . $error['message']);
}

$max_post_id = $db->sql_fetchrow($result);

$totalposts = $max_post_id['total'];
$max_post_id = $max_post_id['max_post_id'];

$postcounter = (!isset($HTTP_GET_VARS['batchstart'])) ? 0 : $HTTP_GET_VARS['batchstart'];

$batchsize = 200; // Process this many posts per loop
$batchcount = 0;
for(;$postcounter <= $max_post_id; $postcounter += $batchsize)
{
	$batchstart = $postcounter + 1;
	$batchend = $postcounter + $batchsize;
	$batchcount++;
	
	$sql = "SELECT *
		FROM " . POSTS_TABLE . "
		WHERE post_id
			BETWEEN $batchstart
				AND $batchend";
	if( !($result = $db->sql_query($sql)) )
	{
		$error = $db->sql_error();
		die("Couldn't get post_text :: " . $sql . " :: " . $error['message']);
	}

	$rowset = $db->sql_fetchrowset($result);
	$db->sql_freeresult($result);

	$post_rows = sizeof($rowset);
	
	if( $post_rows )
	{

	// $sql = "LOCK TABLES ".POST_TEXT_TABLE." WRITE";
	// $result = $db->sql_query($sql);
		print "\n<p>\n<a href='{$_SERVER['PHP_SELF']}?batchstart=$batchstart'>Restart from posting $batchstart</a><br>\n";

		// For every post in the batch:
		for($post_nr = 0; $post_nr < $post_rows; $post_nr++ )
		{
			print ".";
			flush();

			$post_id = $rowset[$post_nr]['post_id'];

			$search->index('post', $rowset[$post_nr]['post_id'], $rowset[$post_nr]['post_text'], $rowset[$post_nr]['post_subject'], $rowset[$post_nr]['poster_id']);
		}
	// $sql = "UNLOCK TABLES";
	// $result = $db->sql_query($sql);

	}
}

print "<br>Removing common words (words that appear in more than 50% of the posts)<br>\n";
flush();
$search->tidy();
print "Removed words that where too common.<br>";

echo "<br>Done";

?>

</body>
</html>
