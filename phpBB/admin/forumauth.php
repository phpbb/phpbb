<?php

chdir("../");

include('extension.inc');
include('common.'.$phpEx);

//
// Start session management
//
//$userdata = session_pagestart($user_ip, PAGE_INDEX, $session_length);
//init_userprefs($userdata);
//
// End session management
//

$simple_auth_ary = array(
	0  => array(0, 0, 0, 0, 0, 0, 0, 0, 0), 
	1  => array(0, 0, 1, 1, 1, 1, 1, 1, 1), 
	2  => array(1, 1, 1, 1, 1, 1, 1, 1, 1), 
	3  => array(0, 2, 2, 2, 2, 2, 2, 2, 2),
	4  => array(2, 2, 2, 2, 2, 2, 2, 2, 2),
	5  => array(0, 3, 3, 3, 3, 3, 3, 3, 3),
	6  => array(3, 3, 3, 3, 3, 3, 3, 3, 3), 
	7  => array(0, 0, 3, 0, 0, 0, 3, 3, 3), 
	8  => array(0, 0, 3, 1, 0, 0, 3, 3, 3)
);

$simple_auth_types = array("Public", "Registered", "Registered [Hidden]", "Private", "Private_[Hidden]", "Moderators", "Moderators [Hidden]", "Mod Post, All Reply", "Mod Post, Reg Reply");

$forum_auth_levels = array("ALL", "REG", "ACL", "MOD", "ADMIN");
$forum_auth_const = array(AUTH_ALL, AUTH_REG, AUTH_ACL, AUTH_MOD, AUTH_ADMIN);

$forum_auth_fields = array("auth_view", "auth_read", "auth_post", "auth_reply", "auth_edit", "auth_delete", "auth_votecreate", "auth_vote", "auth_attachments");

if(isset($HTTP_GET_VARS[POST_FORUM_URL]))
{
	$forum_id = $HTTP_GET_VARS[POST_FORUM_URL];
	$forum_sql = "WHERE forum_id = $forum_id";
}
else
{
	$forum_sql = "";
}

//
// Start output
//
$sql = "SELECT *
	FROM ".FORUMS_TABLE." 
	$forum_sql 
	ORDER BY forum_id ASC";
$f_result = $db->sql_query($sql);

$forum_rows = $db->sql_fetchrowset($f_result);

$sql = "SELECT f.forum_id, u.username, u.user_id   
	FROM ".FORUMS_TABLE." f, ".USERS_TABLE." u, ".USER_GROUP_TABLE." ug, ".AUTH_ACCESS_TABLE." aa 
	WHERE aa.forum_id = f.forum_id 
		AND aa.auth_mod = 1 
		AND ug.group_id = aa.group_id 
		AND u.user_id = ug.user_id 
	ORDER BY f.forum_id, u.user_id";
if(!$q_forum_mods = $db->sql_query($sql))
{
	error_die(SQL_QUERY, "Could not query forum moderator information.", __LINE__, __FILE__);
}
$forum_mods_list = $db->sql_fetchrowset($q_forum_mods);

for($i = 0; $i < count($forum_mods_list); $i++)
{
	$forum_mods['forum_'.$forum_mods_list[$i]['forum_id'].'_name'][] = $forum_mods_list[$i]['username'];
	$forum_mods['forum_'.$forum_mods_list[$i]['forum_id'].'_id'][] = $forum_mods_list[$i]['user_id'];
}

//
// Show data
//
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>phpBB - auth testing</title>
<style type="text/css">
<!--
	P {font-family:Verdana,serif;font-size:8pt}

	H1 {font-family:Arial,Helvetica,sans-serif;font-size:14pt;}
	H2 {font-family:Arial,Helvetica,sans-serif;font-size:12pt;}

	TH {font-family:Verdana,serif;font-size:8pt}
	TD {font-family:Verdana,serif;font-size:8pt}

	SELECT.small	{width:140px;font-family:"Courier New",courier;font-size:8pt;}
	INPUT.text		{font-family:"Courier New",courier;font-size:8pt;}
//-->
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000">

<h1>Forum Authorisation Control</h1>

<?php

	if(isset($forum_id))
	{

?>
<h2>Forum : <?php echo $forum_rows[0]['forum_name']; ?></h2>
<?php

	}

?>

<div align="center"><table width="98%" cellspacing="1" cellpadding="3" border="1">
	<tr><form method="post" action="forumauth.php">
<?php

	if(empty($forum_id))
	{

?>
		<th>Forum Title</th>
		<th>Moderator/s</th>
<?php

	}

?>
		<th>Simple Auth</th>
<?php

	for($j = 0; $j < count($forum_auth_fields); $j++)
	{
		echo "<th>".preg_replace("/auth_/", "", $forum_auth_fields[$j])."</th>\n";
	}
?>
	</tr>
<?php

	for($i = 0; $i < count($forum_rows); $i++)
	{
		$forum_name = "<a href=\"" . append_sid("forumauth.php?" . POST_FORUM_URL . "=" . $forum_rows[$i]['forum_id']) . "\">" . $forum_rows[$i]['forum_name'] . "</a>";

		unset($moderators_links);
		for($mods = 0; $mods < count($forum_mods['forum_'.$forum_rows[$i]['forum_id'].'_id']); $mods++)
		{
			if(isset($moderators_links))
			{
				$moderators_links .= ", ";
			}
			if(!($mods % 2) && $mods != 0)
			{
				$moderators_links .= "<br>";
			}
			$moderators_links .= "<a href=\"".append_sid("../profile.$phpEx?mode=viewprofile&".POST_USERS_URL."=".$forum_mods['forum_'.$forum_rows[$i]['forum_id'].'_id'][$mods])."\">".$forum_mods['forum_'.$forum_rows[$i]['forum_id'].'_name'][$mods]."</a>";
		}

		reset($simple_auth_ary);
		while(list($key, $auth_levels) = each($simple_auth_ary))
		{
			$matched = 1;
			for($k = 0; $k < count($auth_levels); $k++)
			{
				$matched_type = $key;
				if($forum_rows[$i][$forum_auth_fields[$k]] != $auth_levels[$k])
				{
					$matched = 0;
				}
			}
			if($matched)
				break;
		}
		
		//
		// Determine whether the current
		// forum auth fields match a preset 'simple'
		// type
		//
		$simple_auth = (isset($forum_id)) ? "<select name=\"simple_auth\">" : "";
		if(!$matched)
		{
			$simple_auth .= (isset($forum_id)) ? "<option value=\"-1\" selected>" : "";
			$simple_auth .= "Custom";
			$simple_auth .= (isset($forum_id)) ? "</option>" : ""; 

			$matched_type = -1;
		}
		for($j = 0; $j < count($simple_auth_types); $j++)
		{
			if($matched_type == $j)
			{
				$simple_auth .= (isset($forum_id)) ? "<option value=\"$k\" selected>" : "";
				$simple_auth .= $simple_auth_types[$j];
				$simple_auth .= (isset($forum_id)) ? "</option>" : "";
			}
			else if(isset($forum_id))
			{
				$simple_auth .= "<option value=\"$k\">".$simple_auth_types[$j]."</option>";
			}
		}
		$simple_auth .= (isset($forum_id)) ? "</select>" : "";

		//
		// Output values of individual 
		// fields
		//
		for($j = 0; $j < count($forum_auth_fields); $j++)
		{
			$custom_auth[$j] = (isset($forum_id)) ? "<select name=\"".$forum_auth_fields[$j]."\">" : "";
			for($k = 0; $k < count($forum_auth_levels); $k++)
			{
				if($forum_rows[$i][$forum_auth_fields[$j]] == $forum_auth_const[$k])
				{
					$custom_auth[$j] .= (isset($forum_id)) ? "<option value=\"$k\" selected>" : "";
					$custom_auth[$j] .= $forum_auth_levels[$k];
					$custom_auth[$j] .= (isset($forum_id)) ? "</option>" : "";
				}
				else if(isset($forum_id))
				{
					$custom_auth[$j] .= "<option value=\"$k\">". $forum_auth_levels[$k]."</option>";
				}
			}
			$custom_auth[$j] .= (isset($forum_id)) ? "</select>" : "";

		}

		echo "<tr>\n";

		if(empty($forum_id))
		{
			echo "<td>".$forum_name."</td>\n";
			echo "<td>".$moderators_links."</td>\n";
		}
		echo "<td>".$simple_auth."</td>\n";

		for($j = 0; $j < count($custom_auth); $j++)
		{
			echo "<td>".$custom_auth[$j]."</td>\n";
		}

		echo "</tr>\n";

	}

	if(isset($forum_id))
	{
?>
		<tr>
			<td colspan="12"><table width="100%" cellspacing="0" cellpadding="4" border="0">
				<tr>
					<td width="33%" align="center"><input type="submit" name="return" value="Return to Forum Auth"></td>
					<td width="34%" align="center"><input type="hidden" name="<?php echo POST_FORUM_URL; ?>" value="<?php echo $forum_id; ?>"><input type="submit" name="submit" value="Submit Changes"></td>
					<td width="33%" align="center"><input type="reset" value="Reset to Initial"></td>
				</tr>
			</table></td>
		</tr>
<?php

	}

?>
	</form></tr>
</table></div>

<?php

?>
<center>
<br clear="all" />
<font face="Verdana,serif" size="1">Powered By <a href="http://www.phpbb.com/" target="_phpbb">phpBB</a></font>
<br clear="all" />
<font face="Verdana,serif" size="1">
Copyright &copy; 2001 phpBB Group, All Rights Reserved</font>
<br />

</body>
</html>