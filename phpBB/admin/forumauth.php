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
	0  => array(0, 0, 0, 0, 1, 0, 0, 0, 0), 
	1  => array(0, 0, 1, 1, 1, 1, 1, 1, 1), 
	2  => array(1, 1, 1, 1, 1, 1, 1, 1, 1), 
	3  => array(0, 2, 2, 2, 2, 2, 2, 2, 2),
	4  => array(2, 2, 2, 2, 2, 2, 2, 2, 2),
	5  => array(0, 3, 3, 3, 3, 3, 3, 3, 3),
	6  => array(3, 3, 3, 3, 3, 3, 3, 3, 3), 
	7  => array(0, 0, 3, 0, 0, 0, 3, 3, 3), 
	8  => array(0, 0, 3, 1, 0, 0, 3, 3, 3)
);

$simple_auth_types = array("Public", "Registered", "Registered [Hidden]", "Private", "Private [Hidden]", "Moderators", "Moderators [Hidden]", "Moderator Post + All Reply", "Moderator Post + Reg Reply");


$forum_auth_fields = array("auth_view", "auth_read", "auth_post", "auth_reply", "auth_edit", "auth_delete", "auth_votecreate", "auth_vote", "auth_attachments");
$forum_auth_levels = array("ALL", "REG", "ACL", "MOD", "ADMIN");
$forum_auth_const = array(AUTH_ALL, AUTH_REG, AUTH_ACL, AUTH_MOD, AUTH_ADMIN);


if(isset($HTTP_GET_VARS[POST_FORUM_URL]) || isset($HTTP_POST_VARS[POST_FORUM_URL]))
{
	$forum_id = (isset($HTTP_POST_VARS[POST_FORUM_URL])) ? $HTTP_POST_VARS[POST_FORUM_URL] : $HTTP_GET_VARS[POST_FORUM_URL];
//	$forum_sql = "WHERE forum_id = $forum_id";
	$forum_sql = "AND f.forum_id = $forum_id";
}
else
{
	unset($forum_id);
	$forum_sql = "";
}

if(isset($HTTP_GET_VARS['adv']))
{
	$adv = $HTTP_GET_VARS['adv'];
}
else
{
	$adv = -1;
}

if(isset($HTTP_POST_VARS['submit']))
{
	if(!empty($forum_id))
	{
//		$sql = "UPDATE " . FORUMS_TABLE . " SET ";
		$sql = "UPDATE " . AUTH_FORUMS_TABLE . " SET ";

		if(isset($HTTP_POST_VARS['simpleauth']))
		{
			$simple_ary = $simple_auth_ary[$HTTP_POST_VARS['simpleauth']];
			for($i = 0; $i < count($simple_ary); $i++)
			{
				$sql .= $forum_auth_fields[$i] . " = " . $simple_ary[$i];
				if($i < count($simple_ary) - 1)
				{
					$sql .= ", ";
				}
			}

			$sql .= " WHERE forum_id = $forum_id";
		}
		else
		{
//			$sql = "UPDATE " . FORUMS_TABLE . " SET ";
			$sql = "UPDATE " . AUTH_FORUMS_TABLE . " SET ";

			for($i = 0; $i < count($forum_auth_fields); $i++)
			{
				$value = $HTTP_POST_VARS[$forum_auth_fields[$i]];
				if($forum_auth_fields[$i] != 'auth_view')
				{
					if($HTTP_POST_VARS['auth_view'] > $value)
					{
						$value = $HTTP_POST_VARS['auth_view'];
					}
				}
				$sql .= $forum_auth_fields[$i] . " = " . $value;
				if($i < count($forum_auth_fields) - 1)
				{
					$sql .= ", ";
				}
			}

			$sql .= " WHERE forum_id = $forum_id";

		}

		if(strlen($sql))
		{
			if(!$db->sql_query($sql))
			{
				error_die(QUERY_ERROR, "Couldn't update auth table!", __LINE__, __FILE__);
			}
		}

		unset($forum_id);
		$forum_sql = "";
		$adv = 0;

	}
}


//
// Start output
//
/*$sql = "SELECT *
	FROM ".FORUMS_TABLE." 
	$forum_sql 
	ORDER BY forum_id ASC";*/
$sql = "SELECT f.forum_id, f.forum_name, fa.* 
	FROM " . FORUMS_TABLE . " f, ".AUTH_FORUMS_TABLE." fa 
	WHERE fa.forum_id = f.forum_id 
	$forum_sql
	ORDER BY f.forum_id ASC";
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

	if(!empty($forum_id))
	{

?>
<h2>Forum : <?php echo $forum_rows[0]['forum_name']; ?></h2>
<?php

	}

?>

<div align="center"><table cellspacing="1" cellpadding="4" border="0">
<?php

	for($i = 0; $i < count($forum_rows); $i++)
	{
		$forum_name[$i] = "<a href=\"" . append_sid("forumauth.php?" . POST_FORUM_URL . "=" . $forum_rows[$i]['forum_id']) . "\">" . $forum_rows[$i]['forum_name'] . "</a>";

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
		// If we've got a custom setup
		// then we jump into advanced
		// mode by default
		//
		if($adv == -1 && !$matched)
		{
			$adv = 1;
		}
		
		if($adv <= 0 || empty($forum_id))
		{

			//
			// Determine whether the current
			// forum auth fields match a preset 'simple'
			// type
			//

			$simple_auth[$i] = (isset($forum_id)) ? "&nbsp;<select name=\"simpleauth\">" : "";
			if(!$matched && empty($forum_id))
			{
				$simple_auth[$i] .= "Custom";
				$matched_type = -1;
			}
			for($j = 0; $j < count($simple_auth_types); $j++)
			{
				if($matched_type == $j)
				{
					$simple_auth[$i] .= (isset($forum_id)) ? "<option value=\"$j\" selected>" : "";
					$simple_auth[$i] .= $simple_auth_types[$j];
					$simple_auth[$i] .= (isset($forum_id)) ? "</option>" : "";
				}
				else if(isset($forum_id))
				{
					$simple_auth[$i] .= "<option value=\"$j\">".$simple_auth_types[$j]."</option>";
				}
			}
			$simple_auth[$i] .= (isset($forum_id)) ? "</select>&nbsp;" : "";

		}

		if($adv == 1 || empty($forum_id))
		{

			//
			// Output values of individual 
			// fields
			//

			for($j = 0; $j < count($forum_auth_fields); $j++)
			{
				$custom_auth[$i][$j] = (isset($forum_id)) ? "&nbsp;<select name=\"".$forum_auth_fields[$j]."\">" : "";
				for($k = 0; $k < count($forum_auth_levels); $k++)
				{
					if($forum_rows[$i][$forum_auth_fields[$j]] == $forum_auth_const[$k])
					{
						$custom_auth[$i][$j] .= (isset($forum_id)) ? "<option value=\"" . $forum_auth_const[$k] . "\" selected>" : "";
						if(empty($forum_id))
						{
							if($forum_auth_levels[$k] == "ACL" || $forum_auth_levels[$k] == "MOD" || $forum_auth_levels[$k] == "ADMIN")
							{
								$custom_auth[$i][$j] .= "<a href=\"userauth.php?" . POST_FORUM_URL . "=" . $forum_rows[$i]['forum_id'] . "&auth=" . $forum_auth_fields[$j] . "\">";
							}
						}
						$custom_auth[$i][$j] .= $forum_auth_levels[$k];
						if(empty($forum_id))
						{
							if($forum_auth_levels[$k] == "ACL" || $forum_auth_levels[$k] == "MOD" || $forum_auth_levels[$k] == "ADMIN")
							{
								$custom_auth[$i][$j] .= "</a>";
							}
						}
						$custom_auth[$i][$j] .= (isset($forum_id)) ? "</option>" : "";
					}
					else if(isset($forum_id))
					{
						$custom_auth[$i][$j] .= "<option value=\"" . $forum_auth_const[$k] . "\">". $forum_auth_levels[$k]."</option>";
					}
				}
				$custom_auth[$i][$j] .= (isset($forum_id)) ? "</select>&nbsp;" : "";
			}

		}
	}

?>
	<tr><form method="post" action="forumauth.php">
<?php

	if(empty($forum_id))
	{

?>
		<th bgcolor="#CCCCCC">Forum Title</th>
		<th bgcolor="#CCCCCC">Moderator/s</th>
<?php

	}

	if($adv <= 0  || empty($forum_id))
	{

?>
		<th bgcolor="#CCCCCC">Simple Auth</th>
<?php

	}

	if($adv == 1 || empty($forum_id))
	{
		for($j = 0; $j < count($forum_auth_fields); $j++)
		{
			echo "<th bgcolor=\"#CCCCCC\">".preg_replace("/auth_/", "", $forum_auth_fields[$j])."</th>\n";
		}
	}

?>
	</tr>
<?php

	for($i = 0; $i < count($forum_rows); $i++)
	{

		unset($moderators_links);
		for($mods = 0; $mods < count($forum_mods['forum_' . $forum_rows[$i]['forum_id'] . '_id']); $mods++)
		{
			if(isset($moderators_links))
			{
				$moderators_links .= ", ";
			}
			if(!($mods % 2) && $mods != 0)
			{
				$moderators_links .= "<br>";
			}
			$moderators_links .= "<a href=\"".append_sid("../profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=" . $forum_mods['forum_'.$forum_rows[$i]['forum_id'] . '_id'][$mods]) . "\">" . $forum_mods['forum_'.$forum_rows[$i]['forum_id'] . '_name'][$mods] . "</a>";
		}

		echo "<tr>\n";

		if(empty($forum_id))
		{
			echo "<td align=\"center\" bgcolor=\"#DDDDDD\">".$forum_name[$i]."</td>\n";
			echo "<td align=\"center\" bgcolor=\"#DDDDDD\">".$moderators_links."</td>\n";

			$colspan = 2;
		}

		if($adv <= 0  || empty($forum_id))
		{
			echo "<td align=\"center\" bgcolor=\"#DDDDDD\">".$simple_auth[$i]."</td>\n";

			$colspan ++;
		}

		if($adv == 1 || empty($forum_id))
		{
			for($j = 0; $j < count($custom_auth[$i]); $j++)
			{
				echo "<td align=\"center\" bgcolor=\"#DDDDDD\">".$custom_auth[$i][$j]."</td>\n";

				$colspan++;
			}
		}

		echo "</tr>\n";

	}

	if(isset($forum_id))
	{

		$switch_mode = "forumauth.php?" . POST_FORUM_URL . "=" . $forum_id . "&adv=";
		$switch_mode .= ($adv <= 0 ) ? "1" : "0";

		$switch_mode_text = ($adv <= 0 ) ? "Advanced Mode" : "Simple Mode";

?>
		<tr>
			<td colspan="<?php echo $colspan; ?>"><table width="100%" cellspacing="0" cellpadding="4" border="0">
				<tr>
					<td align="center"><a href="<?php echo $switch_mode ?>">Switch to <?php echo $switch_mode_text; ?></a></td>
				</tr>
				<tr>
					<td align="center"><input type="hidden" name="<?php echo POST_FORUM_URL; ?>" value="<?php echo $forum_id; ?>"><input type="submit" name="submit" value="Submit Changes">&nbsp;&nbsp;<input type="reset" value="Reset to Initial"></td>
				</tr>
				<tr>
					<td align="center"><a href="forumauth.php">Return to Forum Auth Index</a></td>
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
<p><a href="userauth.php">User Authorisation Admin</a></p>

<font face="Verdana,serif" size="1">Powered By <a href="http://www.phpbb.com/" target="_phpbb">phpBB 2.0</a></font>
<br clear="all">
<font face="Verdana,serif" size="1">
Copyright &copy; 2001 phpBB Group, All Rights Reserved</font>
<br>

</body>
</html>