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

$auth_field_match = array(
	"auth_view" => AUTH_VIEW,
	"auth_read" => AUTH_READ,
	"auth_post" => AUTH_POST,
	"auth_reply" => AUTH_REPLY,
	"auth_edit" => AUTH_EDIT,
	"auth_delete" => AUTH_DELETE,
	"auth_vote" => AUTH_VOTE,
	"auth_votecreate" => AUTH_VOTECREATE,
	"auth_attachments" => AUTH_ATTACH
);
$forum_auth_fields = array("auth_view", "auth_read", "auth_post", "auth_reply", "auth_edit", "auth_delete", "auth_votecreate", "auth_vote", "auth_attachments");



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
	H3 {font-family:Arial,Helvetica,sans-serif;font-size:10pt;}

	TH {font-family:Verdana,serif;font-size:8pt}
	TD {font-family:Verdana,serif;font-size:8pt}

	SELECT.small	{width:140px;font-family:"Courier New",courier;font-size:8pt;}
	INPUT.text		{font-family:"Courier New",courier;font-size:8pt;}
//-->
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000">

<h1>User Authorisation Control</h1>

<?php

if(!empty($HTTP_GET_VARS[POST_FORUM_URL]))
{

	$forum_id = $HTTP_GET_VARS[POST_FORUM_URL];

	if(!empty($HTTP_GET_VARS['auth']))
	{
//		$fields = $HTTP_GET_VARS['auth'] . ", ";
		$fields = "af.".$HTTP_GET_VARS['auth'] . ", ";
		$forum_field_name[0] = $HTTP_GET_VARS['auth'];
	}
	else
	{
		$fields = "";
		$i = 0;
		while(list($key, $value) = each($auth_field_match))
		{
//			$fields .= $key . ", ";
			$fields .= "af.".$key . ", ";
			$forum_field_name[$i] = $key;
			$i++;
		}
	}

/*	$sql = "SELECT " . $fields . "forum_id, forum_name 
		FROM ".FORUMS_TABLE." 
		WHERE forum_id = $forum_id";*/
	$sql = "SELECT " . $fields. "f.forum_id, f.forum_name   
		FROM " . FORUMS_TABLE . " f, ".AUTH_FORUMS_TABLE." af 
		WHERE af.forum_id = f.forum_id 
			AND f.forum_id = $forum_id";
	$f_result = $db->sql_query($sql);
	$forum_fields = $db->sql_fetchrow($f_result);

	$sql = "SELECT aa.*, g.group_name, u.user_id, u.username, u.user_level, f.forum_name 
		FROM ".AUTH_ACCESS_TABLE." aa, ".GROUPS_TABLE." g, ".USER_GROUP_TABLE." ug, ".USERS_TABLE." u, ".FORUMS_TABLE." f 
		WHERE f.forum_id = $forum_id 
			AND aa.forum_id = f.forum_id 
			AND ug.group_id = aa.group_id 
			AND g.group_id = ug.group_id 
			AND u.user_id = ug.user_id 
			ORDER BY u.user_id, aa.group_id";
	$aa_result = $db->sql_query($sql);
	$user_list = $db->sql_fetchrowset($aa_result);


	for($i = 0; $i < count($user_list); $i++)
	{
		$user_id = $user_list[$i]['user_id'];
		$userinfo[$user_id]['username'] = $user_list[$i]['username'];

		$is_admin = ($user_list[$i]['user_level'] == ADMIN) ? 1 : 0;

		for($j = 0; $j < count($forum_field_name); $j++)
		{
			$this_field = $forum_field_name[$j];
			$is_auth[$this_field][$user_id] = auth_check_user($forum_fields[$this_field], $this_field, $user_list[$i], $is_admin);
		}
	}
		

	echo "<h2>Forum: ".$forum_fields['forum_name']."</h2>\n";

?>
<div align="center"><table cellspacing="1" cellpadding="4" border="0">
	<tr>
		<th bgcolor="#CCCCCC">Forum Auth Field</th>
		<th bgcolor="#CCCCCC">Users with Access</th>
	</tr>
<?php

	for($i = 0; $i < count($forum_field_name); $i++)
	{
		echo "\t<tr><form method=\"get\" action=\"userauth.php\">\n";

		echo "\t\t<td bgcolor=\"#DDDDDD\">" . $forum_field_name[$i] . "</td>\n";

		reset($is_auth);
		$user_auth_ary = $is_auth[$forum_field_name[$i]];

		if($forum_fields[$forum_field_name[$i]] == AUTH_ALL || $forum_fields[$forum_field_name[$i]] == AUTH_REG)
		{
			if($forum_fields[$forum_field_name[$i]] == AUTH_ALL)
			{
				echo "\t\t<td align=\"center\" bgcolor=\"#EEEEEE\">&nbsp;All Users&nbsp;</td>";
			}
			else
			{
				echo "\t\t<td align=\"center\" bgcolor=\"#EEEEEE\">&nbsp;Registered Users&nbsp;</td>";
			}
		}
		else
		{
			echo "\t\t<td bgcolor=\"#EEEEEE\">&nbsp;<select name=\"u\">";
			while(list($userkey, $auth_value) = each($user_auth_ary))
			{
				if($auth_value)
				{
					echo "<option value=\"$userkey\">" . $userinfo[$userkey]['username'] . "</option>";
				}
			}
			echo "</select>&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Look up User\">&nbsp;</td>\n";
		}

		echo "\t</form></tr>\n";

	}

?>
</table></div>
<?php

}
else if(isset($HTTP_GET_VARS[POST_USERS_URL]))
{
	$user_id = $HTTP_GET_VARS[POST_USERS_URL];

/*	$sql = "SELECT * 
		FROM " . FORUMS_TABLE;*/
	$sql = "SELECT f.forum_id, f.forum_name, fa.* 
		FROM " . FORUMS_TABLE . " f, ".AUTH_FORUMS_TABLE." fa 
		WHERE fa.forum_id = f.forum_id";
	$af_result = $db->sql_query($sql);
	$f_access = $db->sql_fetchrowset($af_result);

	$sql = "SELECT user_id, username, user_level  
		FROM " . USERS_TABLE . " 
		WHERE user_id = $user_id";
	$u_result = $db->sql_query($sql);
	$userinf = $db->sql_fetchrow($u_result);

	$sql = "SELECT aa.forum_id, aa.auth_view, aa.auth_read, aa.auth_post, aa.auth_reply, aa.auth_edit, aa.auth_delete, aa.auth_votecreate, aa.auth_vote, aa.auth_attachments, aa.auth_mod, g.group_single_user  
		FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE. " g   
		WHERE ug.user_id = $user_id 
			AND g.group_id = ug.group_id 
			AND aa.group_id = ug.group_id";
	$au_result = $db->sql_query($sql);

	$num_u_access = $db->sql_numrows($au_result);
	if($num_u_access)
	{
		$u_access = $db->sql_fetchrowset($au_result);
	}

	$is_admin = ($userinf['user_level'] == ADMIN) ? 1 : 0;

	for($i = 0; $i < count($f_access); $i++)
	{
		$f_forum_id = $f_access[$i]['forum_id'];
		$is_forum_restricted[$f_forum_id] = 0;

		for($j = 0; $j < count($forum_auth_fields); $j++)
		{
			$key = $forum_auth_fields[$j];
			$value = $f_access[$i][$key];

			if($user_id == ANONYMOUS)
			{
				$auth_user[$f_forum_id][$key] = ($value == AUTH_ALL) ? 1 : 0;
				if($value == AUTH_ACL || $value == AUTH_MOD || $value == AUTH_ADMIN)
				{
					$is_forum_restricted[$f_forum_id] = 1;
				}
			}
			else if(!$num_u_access)
			{
				$auth_user[$f_forum_id][$key] = ($value == AUTH_ALL || $value == AUTH_REG) ? 1 : 0;
				if($value == AUTH_ACL || $value == AUTH_MOD || $value == AUTH_ADMIN)
				{
					$is_forum_restricted[$f_forum_id] = 1;
				}
			}
			else 
			{
				switch($value)
				{
					case AUTH_ALL:
						$auth_user[$f_forum_id][$key] = 1;
						break;

					case AUTH_REG:
						$auth_user[$f_forum_id][$key] = 1;
						break;

					case AUTH_ACL:
						$auth_user[$f_forum_id][$key] = auth_check_user(AUTH_ACL, $key, $u_access, $is_admin);
						$is_forum_restricted[$f_forum_id] = 1;
						break;
		
					case AUTH_MOD:
						$auth_user[$f_forum_id][$key] = auth_check_user(AUTH_MOD, $key, $u_access, $is_admin);
						$is_forum_restricted[$f_forum_id] = 1;
						break;
	
					case AUTH_ADMIN:
						$auth_user[$f_forum_id][$key] = $is_admin;
						$is_forum_restricted[$f_forum_id] = 1;
						break;

					default:
						$auth_user[$f_forum_id][$key] = 0;
						break;
				}
			}
		}
		//
		// Is user a moderator?
		//
		$auth_user[$f_forum_id]['auth_mod'] = auth_check_user(AUTH_MOD, 'auth_mod', $u_access, $is_admin);
	}

?>

<h2><?php 
	
	echo $userinf['username'] . " is ";
	if($userinf['user_level'] == ADMIN)
	{
		echo "an Administrator";
	}
	else
	{
		echo "a User";
	}

	
?></h2>

<h3>Restricted forums</h3>

<div align="center"><table width="80%" cellspacing="1" cellpadding="4" border="0">
	<tr>
		<th width="25%" bgcolor="#CCCCCC">Forum Name</th>
<?php

	for($j = 0; $j < count($forum_auth_fields); $j++)
	{
		echo "\t<th bgcolor=\"#CCCCCC\">".preg_replace("/auth_/", "", $forum_auth_fields[$j])."</th>\n";
	}
	echo "\t<th bgcolor=\"#CCCCCC\">Moderator</th>\n";

	echo "</tr>\n";

	$i = 0;
	while(list($forumkey, $user_ary) = each($auth_user))
	{
		if($is_forum_restricted[$forumkey])
		{
			echo "<tr>\n";
			echo "\t<td bgcolor=\"#DDDDDD\"><a href=\"userauth.php?" . POST_FORUM_URL . "=$forumkey&" . POST_USERS_URL . "=$user_id\">".$f_access[$i]['forum_name']."</a></td>\n";
			while(list($fieldkey, $value) = each($user_ary))
			{
				$can_they = ($auth_user[$forumkey][$fieldkey]) ? "Yes" : "No";
				echo "\t<td bgcolor=\"#DDDDDD\">$can_they</td>\n";
			}
			echo "</tr>\n";
		}
		$i++;
	}
	reset($auth_user);

?>
</table></div>

<h3>Forums with general (public or registered) access</h3>

<p>The following forums are set to be generally accessible to most users, either everyone or just registered users. To limit these forums (or certain fields) to specific users you need to change the forum authorisation type via the <a href="forumauth.php">Forum Authorisation Admin</a> panel.</p>

<div align="center"><table width="80%" cellspacing="1" cellpadding="4" border="0">
	<tr>
		<th width="25%" bgcolor="#CCCCCC">Forum Name</th>
<?php

	for($j = 0; $j < count($forum_auth_fields); $j++)
	{
		echo "\t<th bgcolor=\"#CCCCCC\">".preg_replace("/auth_/", "", $forum_auth_fields[$j])."</th>\n";
	}
	echo "\t<th bgcolor=\"#CCCCCC\">Moderator</th>\n";

	echo "</tr>\n";

	$i = 0;
	while(list($forumkey, $user_ary) = each($auth_user))
	{
		if(!$is_forum_restricted[$forumkey])
		{
			echo "<tr>\n";
			echo "\t<td bgcolor=\"#DDDDDD\">".$f_access[$i]['forum_name']."</td>\n";
			while(list($fieldkey, $value) = each($user_ary))
			{
				$can_they = ($auth_user[$forumkey][$fieldkey]) ? "Yes" : "No";
				echo "\t<td bgcolor=\"#DDDDDD\">$can_they</td>\n";
			}
			echo "</tr>\n";
		}
		$i++;
	}
	reset($auth_user);

?>
</table></div>

<?php

}
else
{

	$sql = "SELECT user_id, username  
		FROM ".USERS_TABLE;
	$u_result = $db->sql_query($sql);
	$user_list = $db->sql_fetchrowset($u_result);

?>
<div align="center"><table cellspacing="1" cellpadding="4" border="0">
	<tr>
		<th bgcolor="#CCCCCC">Select a User</th>
	</tr>
	<tr><form method="get" action="userauth.php">
		<td bgcolor="#DDDDDD" align="center"><select name="<?php echo POST_USERS_URL; ?>"><?php

	for($i = 0; $i < count($user_list); $i++)
	{
		echo "<option value=\"" . $user_list[$i]['user_id'] . "\">" . $user_list[$i]['username'] . "</option>";
	}

?></select>&nbsp;&nbsp;<input type="submit" value="Look up User">&nbsp;</td>
	</form></tr>
</table></div>
<?php

}

?>
<center>
<p><a href="forumauth.php">Forum Authorisation Admin</a></p>

<font face="Verdana,serif" size="1">Powered By <a href="http://www.phpbb.com/" target="_phpbb">phpBB 2.0</a></font>
<br clear="all">
<font face="Verdana,serif" size="1">
Copyright &copy; 2001 phpBB Group, All Rights Reserved</font>
<br>

</body>
</html>