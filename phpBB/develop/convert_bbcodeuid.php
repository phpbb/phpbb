<?php

$phpbb_root_path = "../";

include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'config.'.$phpEx);
include($phpbb_root_path . 'includes/constants.'.$phpEx);
include($phpbb_root_path . 'includes/db.'.$phpEx);

function query($sql, $errormsg)
{
	global $db;
	if(!$result = $db->sql_query($sql))
	{
		print "<br><font color=\"red\">\n";
		print "$errormsg<br>";
		$sql_error = $db->sql_error();
		print $sql_error['code'] .": ". $sql_error['message']. "<br>\n";
		print "<pre>$sql</pre>";
		print "</font>\n";
		return FALSE;
	}
	else
	{
		return $result;
	}
}

if($HTTP_GET_VARS['delete'] == 'true')
{
	$sql = "ALTER TABLE ".POSTS_TABLE."
		DROP bbcode_uid";
	query($sql, "Didn't manage to drop the bbcode_uid table in ".POSTS_TABLE);
	print "All done now. Deleted the bbcode_uid column from the posts table.<p>";
	exit;
}


$sql = "ALTER TABLE ".POSTS_TEXT_TABLE." 
	ADD bbcode_uid char(10) NOT NULL";
print "Adding bbcode_uid field to ".POSTS_TEXT_TABLE.".<br>\n";
$result = query($sql, "Couldn't get add bbcode_uid field to ".POSTS_TEXT_TABLE.".");

$sql = "
	SELECT 
		count(*) as total,
		max(post_id) as maxid 
	FROM ". POSTS_TABLE;
$result = query($sql, "Couldn't get max post_id.");
$maxid = $db->sql_fetchrow($result);
$totalposts = $maxid['total'];
$maxid = $maxid['maxid'];

$batchsize = 200;
print "Going to convert BBcode in posts with $batchsize messages at a time and $totalposts in total.<br>\n";
for($i = 0; $i <= $maxid; $i += $batchsize)
{
	$batchstart = $i + 1;
	$batchend = $i + $batchsize;
	
	print "Moving BBcode UID in post number $batchstart to $batchend<br>\n";
	flush();
	$sql = "
		SELECT 
			post_id,
			bbcode_uid
		FROM "
			.POSTS_TABLE."
		WHERE
			post_id BETWEEN $batchstart AND $batchend";
	$result = query($sql, "Couldn't get ". POSTS_TABLE .".post_id $batchstart to $batchend");
	while($row = mysql_fetch_array($result))
	{
		query("UPDATE ".POSTS_TEXT_TABLE." set bbcode_uid = '". $row['bbcode_uid']. "' WHERE post_id = ".$row['post_id'], "Was unable to update the posts text table with the BBcode_uid");
	}
}

echo "Click <a href=\"$PHP_SELF?delete=true\">HERE</a> to remove the bbcode_uid table from the POSTS table (if you didn't get any serious error messages).<p>";

$db->sql_close();

?>
