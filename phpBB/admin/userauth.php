<?php

chdir("../");

include('extension.inc');
include('common.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_INDEX, $session_length);
init_userprefs($userdata);
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
	"auth_sticky" => AUTH_STICKY, 
	"auth_announce" => AUTH_ANNOUNCE, 
	"auth_vote" => AUTH_VOTE,
	"auth_votecreate" => AUTH_VOTECREATE,
	"auth_attachments" => AUTH_ATTACH
);
$forum_auth_fields = array("auth_view", "auth_read", "auth_post", "auth_reply", "auth_edit", "auth_delete", "auth_sticky", "auth_announce", "auth_votecreate", "auth_vote", "auth_attachments");


//
//
//
if(isset($HTTP_GET_VARS['adv']))
{
	$adv = $HTTP_GET_VARS['adv'];
}
else
{
	$adv = -1;
}


if(isset($HTTP_GET_VARS[POST_USERS_URL]))
{

	$template->set_filenames(array(
		"body" => "admin/userauth_body.tpl"));

	$user_id = $HTTP_GET_VARS[POST_USERS_URL];

	$sql = "SELECT f.forum_id, f.forum_name, fa.auth_view, fa.auth_read, fa.auth_post, fa.auth_reply, fa.auth_edit, fa.auth_delete, fa.auth_announce, fa.auth_sticky, fa.auth_votecreate, fa.auth_vote, fa.auth_attachments 
		FROM " . FORUMS_TABLE . " f, ".AUTH_FORUMS_TABLE." fa 
		WHERE fa.forum_id = f.forum_id";
	$fa_result = $db->sql_query($sql);
	$forum_access = $db->sql_fetchrowset($fa_result);

	for($i = 0; $i < count($forum_access); $i++)
	{
		while(list($forum_id, $forum_row) = each($forum_access))
		{
			for($j = 0; $j < count($forum_auth_fields); $j++)
			{
				$basic_auth_level[$forum_row['forum_id']] = "public";
				if($forum_row[$forum_auth_fields[$j]] == AUTH_ACL)
				{
					$basic_auth_level[$forum_row['forum_id']] = "private";
					$basic_auth_level_fields[$forum_row['forum_id']][] = $forum_auth_fields[$j];
				}
			}
			if($forum_row['auth_view'] == AUTH_MOD || $forum_row['auth_read'] == AUTH_MOD || $forum_row['auth_post'] == AUTH_MOD || $forum_row['auth_reply'] == AUTH_MOD)
			{
				$basic_auth_level[$forum_row['forum_id']] = "moderate";
			}
			if($forum_row['auth_view'] == AUTH_ADMIN || $forum_row['auth_read'] == AUTH_ADMIN || $forum_row['auth_post'] == AUTH_ADMIN || $forum_row['auth_reply'] == AUTH_ADMIN)
			{
				$basic_auth_level[$forum_row['forum_id']] = "admin";
			}
		}
	}

	$sql = "SELECT u.user_id, u.username, u.user_level, g.group_id, g.group_name, g.group_single_user  
		FROM " . USERS_TABLE . " u, " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug   
		WHERE u.user_id = $user_id 
			AND ug.user_id = u.user_id 
			AND g.group_id = ug.group_id";
	$u_result = $db->sql_query($sql);
	$userinf = $db->sql_fetchrowset($u_result);

	$sql = "SELECT aa.forum_id, aa.auth_view, aa.auth_read, aa.auth_post, aa.auth_reply, aa.auth_edit, aa.auth_delete, aa.auth_votecreate, aa.auth_vote, aa.auth_attachments, aa.auth_mod, g.group_single_user  
		FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE. " g   
		WHERE ug.user_id = $user_id 
			AND g.group_id = ug.group_id 
			AND aa.group_id = ug.group_id 
			AND g.group_single_user = 1";
	$au_result = $db->sql_query($sql);

	$num_u_access = $db->sql_numrows($au_result);
	if($num_u_access)
	{
		$u_access = $db->sql_fetchrowset($au_result);
	}

	$is_admin = ($userinf[0]['user_level'] == ADMIN) ? 1 : 0;

	for($i = 0; $i < count($forum_access); $i++)
	{
		$f_forum_id = $forum_access[$i]['forum_id'];
		$is_forum_restricted[$f_forum_id] = 0;

		for($j = 0; $j < count($forum_auth_fields); $j++)
		{
			$key = $forum_auth_fields[$j];
			$value = $f_access[$i][$key];

			switch($value)
			{
				case AUTH_ALL:
					$auth_user[$f_forum_id][$key] = 1;
					break;

				case AUTH_REG:
					$auth_user[$f_forum_id][$key] = ($user_id != ANONYMOUS) ? 1 : 0;
					break;

				case AUTH_ACL:
					$auth_user[$f_forum_id][$key] = ($user_id != ANONYMOUS && $num_u_access) ? auth_check_user(AUTH_ACL, $key, $u_access, $is_admin) : 0;
					break;
		
				case AUTH_MOD:
					$auth_user[$f_forum_id][$key] = ($user_id != ANONYMOUS && $num_u_access) ? auth_check_user(AUTH_MOD, $key, $u_access, $is_admin) : 0;
					break;
	
				case AUTH_ADMIN:
					$auth_user[$f_forum_id][$key] = $is_admin;
					break;

				default:
					$auth_user[$f_forum_id][$key] = 0;
					break;
			}
		}
		//
		// Is user a moderator?
		//
		$auth_user[$f_forum_id]['auth_mod'] = ($user_id != ANONYMOUS && $num_u_access) ? auth_check_user(AUTH_MOD, 'auth_mod', $u_access, $is_admin) : 0;
	}

	while(list($forumkey, $user_ary) = each($auth_user))
	{
		$simple_auth[$forumkey] = 1;
		while(list($fieldkey, $value) = each($user_ary))
		{
			$simple_auth[$forumkey] = $simple_auth[$forumkey] && $value;

		}
	}
	reset($auth_user);

	$t_username .= $userinf[0]['username'];
	$t_usertype = ($userinf[0]['user_level'] == ADMIN) ? "an <b>Administrator</b>" : "a <b>User</b>";

	for($i = 0; $i < count($userinf); $i++)
	{
		if(!$userinf[$i]['group_single_user'])
		{
			$group_name[] = $userinf[$i]['group_name'];
			$group_id[] = $userinf[$i]['group_name'];
		}
	}
	
	if(count($group_name))
	{
		$t_usergroup_list = "belongs to the following groups; ";
		for($i = 0; $i < count($userinf); $i++)
		{
			$t_usergroup_list .= $group_name[$i];
			if($i < count($group_name) - 1)
			{
				$t_usergroup_list .= ", ";
			}
		}
	}
	else
	{
		$t_usergroup_list = "belongs to no usergroups.";
	}


	$i = 0;
	if($adv == -1)
	{
		while(list($forumkey, $user_ary) = each($auth_user))
		{
			if($basic_auth_level[$forumkey] == "private")
			{
				$allowed = 1;
				for($j = 0; $j < count($basic_auth_level_fields[$forumkey]); $j++)
				{
					if(!$auth_user[$forumkey][$basic_auth_level_fields[$forumkey][$j]])
					{
						$allowed = 0;
					}
				}
				$optionlist_grant = "<select name=\"simple[$forumkey]\">";
				if($allowed)
				{
					$optionlist_grant .= "<option value=\"1\" selected>Allow Access</option><option value=\"0\">Disallow Access</option>";
				}
				else
				{
					$optionlist_grant .= "<option value=\"1\">Allow Access</option><option value=\"0\" selected>Disallow Access</option>";
				}
				$optionlist_grant .= "</select>";
			}
			else
			{
				$optionlist_grant = "";
			}
			if($user_ary['auth_mod'])
			{
				$optionlist_mod = "<option value=\"1\">Remove Moderator</option><option value=\"0\" selected>Make Moderator</option>";
			}
			else
			{
				$optionlist_mod = "<option value=\"1\" selected>Remove Moderator</option><option value=\"0\">Make Moderator</option>";
			}
			switch($basic_auth_level[$forumkey])
			{
				case 'public':
					$row_class = "authall";
					break;
				case 'private':
					$row_class = "authacl";
					break;
				case 'moderate':
					$row_class = "authmod";
					break;
				case 'admin':
					$row_class = "authadmin";
					break;
				default:
					$row_class = "authall";
					break;
			}

			$template->assign_block_vars("restrictedforums", array(
				"ROW_CLASS" => $row_class,
				"FORUM_NAME" => $forum_access[$i]['forum_name'],

				"SELECT_GRANT_LIST" => "$optionlist_grant",
				"SELECT_MOD_LIST" => "<select name=\"moderator[$forumkey]\">$optionlist_mod</select>")
			);
			$i++;
		}
	}
	else
	{
		while(list($forumkey, $user_ary) = each($auth_user))
		{
			echo "<tr>\n";
			echo "\t<td bgcolor=\"#DDDDDD\"><a href=\"userauth.php?" . POST_FORUM_URL . "=$forumkey&" . POST_USERS_URL . "=$user_id\">" . $f_access[$i]['forum_name'] . "</a></td>\n";
			while(list($fieldkey, $value) = each($user_ary))
			{
				$can_they = ($auth_user[$forumkey][$fieldkey]) ? "Yes" : "No";
				echo "\t<td bgcolor=\"#DDDDDD\">$can_they</td>\n";
			}
			echo "</tr>\n";
			$i++;
		}
	}
	reset($auth_user);


	$template->assign_vars(array(
		"USERNAME" => $t_username, 
		"USERTYPE" => $t_usertype, 
		
		"USER_GROUP_LIST" => $t_usergroup_list)
	);

	$template->pparse("body");


}
else
{

	//
	// Default user selection box
	// This should be altered on the final
	// system to list users via an alphabetical
	// selection system ... otherwise this
	// could get 'cumbersome' for boards
	// with several thousand users!
	//

	$sql = "SELECT user_id, username  
		FROM ".USERS_TABLE;
	$u_result = $db->sql_query($sql);
	$user_list = $db->sql_fetchrowset($u_result);

	$select_list = "<select name=\"" . POST_USERS_URL . "\">";
	for($i = 0; $i < count($user_list); $i++)
	{
		$select_list .= "<option value=\"" . $user_list[$i]['user_id'] . "\">" . $user_list[$i]['username'] . "</option>";
	}
	$select_list .= "</select>";

	$template->set_filenames(array(
		"body" => "admin/userauth_select_body.tpl"));

	$template->assign_vars(array(
		"S_USERAUTH_ACTION" => append_sid("userauth.$phpEx"), 
		"S_USERS_SELECT" => $select_list, 
		
		"U_FORUMAUTH" => append_sid("forumauth.$phpEx"))
	);

	$template->pparse("body");

}


?>