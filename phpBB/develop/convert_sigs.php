<?php

$phpbb_root_path = "../";

include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'config.'.$phpEx);
include($phpbb_root_path . 'includes/constants.'.$phpEx);
include($phpbb_root_path . 'includes/db.'.$phpEx);

$sql = "SELECT post_id, post_text 
	FROM " . POSTS_TEXT_TABLE;
if( $result = $db->sql_query($sql) )
{
	$rowset = $db->sql_fetchrowset($result);

	$attach_sql = "";
	$non_attach_sql = "";

	for($i = 0; $i < count($rowset); $i++)
	{
		if( ereg("\[addsig]$", $rowset[$i]['post_text']))
		{
			if( $attach_sql != "" )
			{
				$attach_sql .= ", ";
			}
			$attach_sql .= $rowset[$i]['post_id'];

			$sql = "UPDATE " . POSTS_TEXT_TABLE . " 
				SET post_text = '" . addslashes(preg_replace("/\[addsig\]/is", "", $rowset[$i]['post_text'])) . "'  
				WHERE post_id = " . $rowset[$i]['post_id'];
			if( !$result = $db->sql_query($sql) )
			{
				die("Couldn't update post_text - " . $i);
			}

		}
		else
		{
			if( $non_attach_sql != "" )
			{
				$non_attach_sql .= ", ";
			}
			$non_attach_sql .= $rowset[$i]['post_id'];
		}
	}

	echo "<BR>";

	if( $attach_sql != "" )
	{
		echo $sql = "UPDATE " . POSTS_TABLE . " 
			SET enable_sig = 1 
			WHERE post_id IN ($attach_sql)";
		if( !$result = $db->sql_query($sql) )
		{
			die("Couldn't update post table attach_sig - ");
		}
	}

	echo "<BR>";

	if( $non_attach_sql != "" )
	{
		echo $sql = "UPDATE " . POSTS_TABLE . " 
			SET enable_sig = 0 
			WHERE post_id IN ($non_attach_sql)";
		if( !$result = $db->sql_query($sql) )
		{
			die("Couldn't update post table non_attach_sig - ");
		}
	}

}

$db->sql_close();

	echo "<BR><BR>COMPLETE<BR>";

?>
