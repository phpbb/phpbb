<?php

$phpbb_root_path = "../";

include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'config.'.$phpEx);
include($phpbb_root_path . 'includes/constants.'.$phpEx);
include($phpbb_root_path . 'includes/db.'.$phpEx);

//
// Alter table ...
//
echo "Alter tables  ... ";

echo $sql = "ALTER TABLE " . PRIVMSGS_TABLE . " 
	ADD privmsgs_enable_bbcode TINYINT(1) DEFAULT '1' NOT NULL, 
	ADD privmsgs_enable_html TINYINT(1) DEFAULT '0' NOT NULL, 
	ADD privmsgs_enable_smilies TINYINT(1) DEFAULT '1' NOT NULL, 
	ADD privmsgs_attach_sig TINYINT(1) DEFAULT '1' NOT NULL";
if( !$result = $db->sql_query($sql) )
{
	die("Couldn't alter privmsgs table");
}
echo $sql = "ALTER TABLE " . PRIVMSGS_TEXT_TABLE . " 
	ADD privmsgs_bbcode_uid CHAR(10) AFTER privmsgs_text_id";
if( !$result = $db->sql_query($sql) )
{
	die("Couldn't alter privmsgs text table");
}
echo "COMPLETE<BR>";

//
// Move bbcode ...
//
echo "Move bbcode uid's ... ";

$sql = "SELECT privmsgs_id, privmsgs_bbcode_uid 
	FROM " . PRIVMSGS_TABLE;
if( $result = $db->sql_query($sql) )
{
	$rowset = $db->sql_fetchrowset($result);

	for($i = 0; $i < count($rowset); $i++)
	{
		$sql = "UPDATE " . PRIVMSGS_TEXT_TABLE . " 
			SET privmsgs_bbcode_uid = '" . $rowset[$i]['privmsgs_bbcode_uid'] . "' 
			WHERE privmsgs_text_id = " . $rowset[$i]['privmsgs_id'];
		if( !$result = $db->sql_query($sql) )
		{
			die("Couldn't update privmsgs text bbcode - " . $i);
		}
	}

	$sql = "ALTER TABLE " . PRIVMSGS_TABLE . " 
		DROP privmsgs_bbcode_uid";
	if( !$result = $db->sql_query($sql) )
	{
		die("Couldn't alter privmsgs table - drop privmsgs_bbcode_uid");
	}
}

echo "COMPLETE<BR>";

//
// Stripslashes from titles
//
echo "Strip subject slashes ... ";

$sql = "SELECT privmsgs_subject , privmsgs_id, privmsgs_to_userid, privmsgs_from_userid   
	FROM " . PRIVMSGS_TABLE;
if( $result = $db->sql_query($sql) )
{
	$rowset = $db->sql_fetchrowset($result);

	for($i = 0; $i < count($rowset); $i++)
	{
		$sql = "UPDATE " . PRIVMSGS_TABLE . " 
			SET privmsgs_subject = '" . addslashes(stripslashes($rowset[$i]['privmsgs_subject'])) . "'  
			WHERE privmsgs_id = " . $rowset[$i]['privmsgs_id'];
		if( !$result = $db->sql_query($sql) )
		{
			die("Couldn't update subjects - $i");
		}
	}
}
echo "COMPLETE<BR>";				

//
// Update sigs
//
echo "Remove [addsig], stripslashes and update privmsgs table sig enable ...";

$sql = "SELECT privmsgs_text_id , privmsgs_text 
	FROM " . PRIVMSGS_TEXT_TABLE;
if( $result = $db->sql_query($sql) )
{
	$rowset = $db->sql_fetchrowset($result);

	$attach_sql = "";
	$non_attach_sql = "";

	for($i = 0; $i < count($rowset); $i++)
	{
		if( ereg("\[addsig]$", $rowset[$i]['privmsgs_text']))
		{
			if( $attach_sql != "" )
			{
				$attach_sql .= ", ";
			}
			$attach_sql .= $rowset[$i]['privmsgs_text_id'];

			$sql = "UPDATE " . PRIVMSGS_TEXT_TABLE . " 
				SET privmsgs_text = '" . addslashes(preg_replace("/\[addsig\]/is", "", stripslashes($rowset[$i]['privmsgs_text']))) . "'  
				WHERE privmsgs_text_id = " . $rowset[$i]['privmsgs_text_id'];
			if( !$result = $db->sql_query($sql) )
			{
				die("Couldn't update privmsgs text - " . $i);
			}

		}
		else
		{
			$sql = "UPDATE " . PRIVMSGS_TEXT_TABLE . " 
				SET privmsgs_text = '" . addslashes(stripslashes($rowset[$i]['privmsgs_text'])) . "'  
				WHERE privmsgs_text_id = " . $rowset[$i]['privmsgs_text_id'];
			if( !$result = $db->sql_query($sql) )
			{
				die("Couldn't update privmsgs text - " . $i);
			}

			if( $non_attach_sql != "" )
			{
				$non_attach_sql .= ", ";
			}
			$non_attach_sql .= $rowset[$i]['privmsgs_text_id'];
		}
	}

	if( $attach_sql != "" )
	{
		$sql = "UPDATE " . PRIVMSGS_TABLE . " 
			SET privmsgs_attach_sig = 1 
			WHERE privmsgs_id IN ($attach_sql)";
		if( !$result = $db->sql_query($sql) )
		{
			die("Couldn't update privmsgs table attach_sig - ");
		}
	}

	if( $non_attach_sql != "" )
	{
		$sql = "UPDATE " . PRIVMSGS_TABLE . " 
			SET privmsgs_attach_sig = 0 
			WHERE privmsgs_id IN ($non_attach_sql)";
		if( !$result = $db->sql_query($sql) )
		{
			die("Couldn't update privmsgs table non_attach_sig - ");
		}
	}

}

echo "COMPLETE<BR>";

$db->sql_close();

?>
