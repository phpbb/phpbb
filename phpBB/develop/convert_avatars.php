<?php

$phpbb_root_path = "../";

include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'config.'.$phpEx);
include($phpbb_root_path . 'includes/constants.'.$phpEx);
include($phpbb_root_path . 'includes/db.'.$phpEx);


$sql = "ALTER TABLE " . USERS_TABLE . " 
	ADD user_avatar_type TINYINT(4) DEFAULT '0' NOT NULL";
if( !$result = $db->sql_query($sql) )
{
	die("Couldn't alter users table");
}

$sql = "SELECT user_id, user_avatar 
	FROM " . USERS_TABLE;
if( $result = $db->sql_query($sql) )
{
	$rowset = $db->sql_fetchrowset($result);

	for($i = 0; $i < count($rowset); $i++)
	{
		if( ereg("^http", $rowset[$i]['user_avatar']))
		{
			$sql_type = USER_AVATAR_REMOTE;
		}
		else if( $rowset[$i]['user_avatar'] != "" )
		{
			$sql_type = USER_AVATAR_UPLOAD;
		}
		else
		{
			$sql_type = USER_AVATAR_NONE;
		}

		$sql = "UPDATE " . USERS_TABLE . " 
			SET user_avatar_type = $sql_type 
			WHERE user_id = " . $rowset[$i]['user_id'];
		if( !$result = $db->sql_query($sql) )
		{
			die("Couldn't update users table- " . $i);
		}
	}
}

echo "<BR><BR>COMPLETE<BR>";

?>
