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
		echo "\t<tr><form method=\"post\" action=\"userauth.php\">\n";

		echo "\t\t<td bgcolor=\"#DDDDDD\">" . $forum_field_name[$i] . "</td>\n";

		reset($is_auth);
		$user_auth_ary = $is_auth[$forum_field_name[$i]];

		if($forum_fields[$forum_field_name[$i]] == AUTH_ALL || $forum_fields[$forum_field_name[$i]] == AUTH_ALL)
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
			echo "</select>&nbsp;&nbsp;&nbsp;<input type=\"submit\" name=\"moduser\" value=\"Look up User\">&nbsp;</td>\n";
		}

		echo "\t</form></tr>\n";

	}

?>
</table></div>
<?php

}
else if(!empty($HTTP_GET_VARS['u']) || !empty($HTTP_POST_VARS['u']))
{
	$userid = (!empty($HTTP_GET_VARS['u'])) ? $HTTP_GET_VARS['u'] : $HTTP_POST_VARS['u'];

	$sql = "SELECT username, user_level 
		FROM ".USERS_TABLE." 
		WHERE user_id = $userid";
	$u_result = $db->sql_query($sql);

	$sql = "SELECT af.*, g.*, f.forum_name 
		FROM ".AUTH_ACCESS_TABLE." af, ".GROUPS_TABLE." g, ".USER_GROUP_TABLE." ug, ".FORUMS_TABLE." f 
		WHERE ug.user_id = $userid 
			AND g.group_id = ug.group_id 
			AND af.forum_id = f.forum_id 
			ORDER BY g.group_id";
	$aa_result = $db->sql_query($sql);

	$user_inf = $db->sql_fetchrow($u_result);
	$access_inf = $db->sql_fetchrowset($aa_result);

	//
	// Show data
	//
	$userdata['user_id'] = $userid;
	$userdata['username'] = $user_inf['username'];
	$userdata['user_level'] = $user_inf['user_level'];
	$userdata['session_logged_in'] = 1;

	$is_auth = auth(AUTH_ALL, AUTH_LIST_ALL, $userdata);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>phpBB - auth testing</title>
<style type="text/css">
<!--
	P {font-family:Verdana,serif;font-size:10pt}

	H1 {font-family:Arial,Helvetica,sans-serif;font-size:14pt;}

	TH {font-family:Verdana,serif;font-size:10pt}
	TD {font-family:Verdana,serif;font-size:10pt}

	SELECT.small	{width:140px;font-family:"Courier New",courier;font-size:8pt;}
	INPUT.text		{font-family:"Courier New",courier;font-size:8pt;}
//-->
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000">

<h1><?php 
	
	echo $userdata['username']; 
	echo (($userdata['user_level'] == ADMIN) ? " is an Admin" : " is a User"); 
	
?></h1>

<div align="center"><table width="98%" cellspacing="1" cellpadding="3" border="1">
	<tr>
		<th>Group</th>
		<th>Group Name</th>
		<th>Forum Title</th>
		<th>Can View?</th>
		<th>Can Read?</th>
		<th>Can Post?</th>
		<th>Can Reply?</th>
		<th>Can Edit?</th>
		<th>Can Delete?</th>
		<th>Is Moderator?</th>
	</tr>
<?php

	for($i = 0; $i < count($is_auth); $i++)
	{
		$auth_view = ($is_auth[$i]['auth_view'] == 1) ? "Yes" : "No";
		$auth_read = ($is_auth[$i]['auth_read'] == 1) ? "Yes" : "No";
		$auth_post = ($is_auth[$i]['auth_post'] == 1) ? "Yes" : "No";
		$auth_reply = ($is_auth[$i]['auth_reply'] == 1) ? "Yes" : "No";
		$auth_edit = ($is_auth[$i]['auth_edit'] == 1) ? "Yes" : "No";
		$auth_delete = ($is_auth[$i]['auth_delete'] == 1) ? "Yes" : "No";
		$auth_mod = ($is_auth[$i]['auth_mod'] == 1) ? "Yes" : "No";


		echo "<tr>\n";
		echo "<td>".$access_inf[$i]['group_id']."</td>\n";
		echo "<td>".$access_inf[$i]['group_name']."</td>\n";
		echo "<td>".$access_inf[$i]['forum_name']."</td>\n";
		echo "<td>".$auth_view."</td>\n";
		echo "<td>".$auth_read."</td>\n";
		echo "<td>".$auth_post."</td>\n";
		echo "<td>".$auth_reply."</td>\n";
		echo "<td>".$auth_edit."</td>\n";
		echo "<td>".$auth_delete."</td>\n";
		echo "<td>".$auth_mod."</td>\n";
		echo "</tr>\n";
	}
?>
	</tr>
</table></div>

<?php

}

?>
<center>
<br clear="all">
<font face="Verdana,serif" size="1">Powered By <a href="http://www.phpbb.com/" target="_phpbb">phpBB 2.0</a></font>
<br clear="all">
<font face="Verdana,serif" size="1">
Copyright &copy; 2001 phpBB Group, All Rights Reserved</font>
<br>

</body>
</html>