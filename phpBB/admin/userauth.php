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

$forum_auth_key_fields = array("auth_view", "auth_read", "auth_post", "auth_reply");

// ----------
// Start Functions
//
function a_auth_check_user($type, $key, $u_auth, $is_admin)
{

	$single_user = 0;
	$auth_user = array();

	while( list($entry, $u_ary) = each($u_auth) )
	{
		if(!$single_user)
		{
			$single_user = $u_ary['group_single_user'];
			
			$result = 0;
			switch($type)
			{
				case AUTH_ACL:
					$result = $u_ary[$key];

				case AUTH_MOD:
					$result = $result || $u_ary['auth_mod'];

				case AUTH_ADMIN:
					$result = $result || $is_admin;
					break;
			}

			$auth_user['auth'] = (!$single_user) ? ( $auth_user || $result ) : $result;

		}
		$auth_user['single_group'] = ($single_user) ? "single" : "group";

	}
	
	return $auth_user;
}
//
// Start Functions
// ----------


//
//
//
$adv = (isset($HTTP_GET_VARS['adv'])) ? $HTTP_GET_VARS['adv'] : -1;

if(isset($HTTP_POST_VARS['submit']) && !empty($HTTP_POST_VARS[POST_USERS_URL]))
{
	$user_id = $HTTP_POST_VARS[POST_USERS_URL];

	//
	// This is where things become fun ...
	//
	// We have to do a pile of cross-checking
	// to ensure what the admin has requested
	// for a user doesn't conflict with
	// permissions already assigned. If they
	// do we warn the admin and give them 
	// options ... where possible
	//
	
	//
	// Get group_id for this user_id
	//
	$sql_groupid = "SELECT group_id 
		FROM " . USER_GROUP_TABLE . "
		WHERE user_id = $user_id";
	if(!$result = $db->sql_query($sql_groupid))
	{
		// Error no such user/group
	}
	list($group_id) = $db->sql_fetchrow($result);

	//
	// Carry out requests
	//
	if( !$HTTP_POST_VARS['makeadmin'] && $HTTP_POST_VARS['curadmin'] )
	{
		//
		// Delete any entries granting in auth_access
		//
		$sql_unmod = "DELETE FROM " . AUTH_ACCESS_TABLE . "     
			WHERE group_id = $group_id";
		if(!$result = $db->sql_query($sql_unmod))
		{
			// Error, couldn't delete entries
		} 

		//
		// Update users level, reset to USER
		//
		$sql_userlevel = "UPDATE " . USERS_TABLE . " 
			SET user_level = " . USER . " 
			WHERE user_id = $user_id";
		if(!$result = $db->sql_query($sql_userlevel))
		{
			// Error, couldn't set user level
		}
	
		header("Location: userauth.$phpEx?" . POST_USERS_URL . "=$user_id");

	}
	else if( $HTTP_POST_VARS['makeadmin'] && !$HTTP_POST_VARS['curadmin'] )
	{
		//
		// Switch user_level to ADMIN
		//
		$sql_userlevel = "UPDATE " . USERS_TABLE . " 
			SET user_level = " . ADMIN . " 
			WHERE user_id = $user_id";
		if(!$result = $db->sql_query($sql_userlevel))
		{
			// Error, couldn't set user level
		}
			
		// This needs changing -> Remove the
		// user from auth_access where special 
		// access permissions are granted but leave
		// moderator status
		//
		// ---------------------------------------
		// Delete any entries in auth_access, they
		// are unrequired if user is becoming an 
		// admin
		//
		$sql_unauth = "DELETE FROM " . AUTH_ACCESS_TABLE . "     
			WHERE aa.group_id = $group_id";
		if(!$result = $db->sql_query($sql_unauth))
		{
			// Error, couldn't delete entries
		}

		//
		//
		// ----------------------------------------

		header("Location: userauth.$phpEx?" . POST_USERS_URL . "=$user_id");

	}
	else
	{

		$change_mod_ary = (isset($HTTP_POST_VARS['moderator'])) ? $HTTP_POST_VARS['moderator'] : 0;
		$change_prv_ary = (isset($HTTP_POST_VARS['private'])) ? $HTTP_POST_VARS['private'] : 0;

		//
		// Pull all the group info
		// for this user
		//
		$sql = "SELECT aa.forum_id, aa.auth_view, aa.auth_read, aa.auth_post, aa.auth_reply, aa.auth_edit, aa.auth_delete, aa.auth_votecreate, aa.auth_vote, aa.auth_attachments, aa.auth_mod, g.group_single_user  
		FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE. " g   
			WHERE ug.user_id = $user_id 
				AND g.group_id = ug.group_id 
				AND aa.group_id = ug.group_id 
				AND g.group_single_user <> " . TRUE;
		$au_result = $db->sql_query($sql);

		$num_u_access = $db->sql_numrows($au_result);
		if($num_u_access)
		{
			$u_access = $db->sql_fetchrowset($au_result);
		}

		//
		// The data above lists access and moderator permissions
		// for this user given by all the groups they belong to.
		// These values must be checked against those requested
		// by the admin and where necessary the admin is 
		// informed of problems. For example, if a group the user
		// belongs to already grants the user moderator status
		// then the user won't have moderator status enabled.
		// If the user has a group entry preventing access to a 
		// forum then again, we must warn the admin that giving
		// the user access goes against the group permissions
		// (although in this case we'll go ahead and add the user)
		//
		
		//
		// Check against moderator table ...
		//
		$valid_auth_mod_chg = array();
		reset($change_mod_ary);
		while(list($chg_forum_id, $value) = each($change_mod_ary))
		{
			$a_match = $value;
			for($i = 0; $i < count($u_access); $i++)
			{
				$forum_id = $u_access[$i]['forum_id'];

				if($forum_id == $chg_forum_id && $u_access[$i]['auth_mod'] == 1)
				{
					$a_match = 0;
				}
			}
	
			$valid_auth_mod_chg[$chg_forum_id] = $a_match;

		}
		//
		// valid_auth_mod_chg now contains an array (key is forum_id)
		// where the value is 1 if the user should be an admin and 0
		// where the user is prevented (either by the admin disallowing
		// or the user belonging to a group which already moderates)
		//

		print_r($valid_auth_mod_chg);
		echo "<BR><BR>";

		//
		// Check against priv access table ...
		//
		$valid_auth_prv_chg = array();
		reset($change_prv_ary);
		while(list($chg_forum_id, $value) = each($change_prv_ary))
		{
			$a_match = $value;
			for($i = 0; $i < count($u_access); $i++)
			{
				$forum_id = $u_access[$i]['forum_id'];

				if($forum_id == $chg_forum_id)
				{
					for($k = 0; $k < count($forum_auth_key_fields); $k++)
					{
						$a_match = $a_match && $u_access[$i][$forum_auth_key_fields[$k]];
					}
				}
			}
	
			$valid_auth_prv_chg[$chg_forum_id] = $a_match;

		}
		//
		// valid_auth_mod_chg now contains an array (key is forum_id)
		// where the value is 1 if the user should be an admin and 0
		// where the user is prevented (either by the admin disallowing
		// or the user belonging to a group which already moderates)
		//

		print_r($valid_auth_prv_chg);
		echo "<BR><BR>";

		exit;

		header("Location: userauth.$phpEx?" . POST_USERS_URL . "=$user_id");

	}

}
else if(empty($HTTP_GET_VARS[POST_USERS_URL]))
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

	exit;

}

	$template->set_filenames(array(
		"body" => "admin/userauth_body.tpl")
	);

	$user_id = $HTTP_GET_VARS[POST_USERS_URL];

	$sql = "SELECT forum_id, forum_name, auth_view, auth_read, auth_post, auth_reply, auth_edit, auth_delete, auth_announce, auth_sticky, auth_votecreate, auth_vote, auth_attachments 
		FROM " . FORUMS_TABLE;
	$fa_result = $db->sql_query($sql);
	$forum_access = $db->sql_fetchrowset($fa_result);

	for($i = 0; $i < count($forum_access); $i++)
	{
		while(list($forum_id, $forum_row) = each($forum_access))
		{
			for($j = 0; $j < count($forum_auth_fields); $j++)
			{
				$basic_auth_level[$forum_row['forum_id']] = "public";

				if($forum_row[$forum_auth_fields[$j]] == AUTH_REG)
				{
					$basic_auth_level[$forum_row['forum_id']] = "registered";
					$basic_auth_level_fields[$forum_row['forum_id']][] = $forum_auth_fields[$j];
				}
				else if($forum_row[$forum_auth_fields[$j]] == AUTH_ACL)
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
			AND aa.group_id = ug.group_id";
	$au_result = $db->sql_query($sql);

	$num_u_access = $db->sql_numrows($au_result);
	if($num_u_access)
	{
		while($u_row = $db->sql_fetchrow($au_result))
		{
			$u_access[$u_row['forum_id']][] = $u_row;
			$num_forum_access[$u_row['forum_id']]++;
		}
	}

	$is_admin = ($userinf[0]['user_level'] == ADMIN && $userinf[0]['user_id'] != ANONYMOUS) ? 1 : 0;

	for($i = 0; $i < count($forum_access); $i++)
	{
		$f_forum_id = $forum_access[$i]['forum_id'];
		$is_forum_restricted[$f_forum_id] = 0;

		for($j = 0; $j < count($forum_auth_fields); $j++)
		{
			$key = $forum_auth_fields[$j];
			$value = $forum_access[$i][$key];

			switch($value)
			{
				case AUTH_ALL:
					$auth_user[$f_forum_id][$key] = 1;
					break;

				case AUTH_REG:
					$auth_user[$f_forum_id][$key] = ($user_id != ANONYMOUS) ? 1 : 0;
					break;

				case AUTH_ACL:
					if($user_id != ANONYMOUS && $num_forum_access[$f_forum_id])
					{
						$result = a_auth_check_user(AUTH_ACL, $key, $u_access[$f_forum_id], $is_admin);
						$auth_user_group[$f_forum_id][$key] = $result['single_group'];
						$auth_user[$f_forum_id][$key] = $result['auth'];
					}
					else
					{
						$auth_user[$f_forum_id][$key] = 0;
					}
					break;
		
				case AUTH_MOD:
					if($user_id != ANONYMOUS && $num_forum_access[$f_forum_id])
					{
						$result = a_auth_check_user(AUTH_MOD, $key, $u_access[$f_forum_id], $is_admin);
						$auth_user_group[$f_forum_id][$key] = $result['single_group'];
						$auth_user[$f_forum_id][$key] = $result['auth'];
					}
					else
					{
						$auth_user[$f_forum_id][$key] = 0;
					}
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
		if($user_id != ANONYMOUS && $num_forum_access[$f_forum_id])
		{
			$result = a_auth_check_user(AUTH_MOD, 'auth_mod', $u_access[$f_forum_id], $is_admin);
			$auth_user_group[$f_forum_id]['auth_mod'] = $result['single_group'];
			$auth_user[$f_forum_id]['auth_mod'] = $result['auth'];
		}
		else
		{
			$auth_user[$f_forum_id][$key] = 0;
		}
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

	while(list($forumkey, $user_ary) = each($auth_user_group))
	{
		$simple_auth_acl[$forumkey] = "single";
		$simple_auth_mod[$forumkey] = "single";

		while(list($fieldkey, $value) = each($user_ary))
		{
			$simple_auth_acl[$forumkey] = ($simple_auth_acl[$forumkey] != "group") ? $value : "group";
			$simple_auth_mod[$forumkey] = ($simple_auth_mod[$forumkey] != "group") ? $value : "group";
		}
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
				$optionlist_grant = "<select name=\"private[$forumkey]\">";
				if($is_admin || $user_ary['auth_mod'])
				{
					$optionlist_grant .= "<option value=\"1\">Allowed Access</option>";
				}
				else if($allowed)
				{
					$optionlist_grant .= "<option value=\"1\" selected>Allowed Access</option><option value=\"0\">Disallowed Access</option>";
				}
				else
				{
					$optionlist_grant .= "<option value=\"1\">Allowed Access</option><option value=\"0\" selected>Disallowed Access</option>";
				}
				$optionlist_grant .= "</select>";
			}
			else
			{
				$optionlist_grant = "";
			}

			if($user_ary['auth_mod'])
			{
				$optionlist_mod = "<option value=\"1\" selected>Is a Moderator</option><option value=\"0\">Is not a Moderator</option>";
			}
			else
			{
				$optionlist_mod = "<option value=\"1\">Is a Moderator</option><option value=\"0\" selected>Is not a Moderator</option>";
			}
			switch($basic_auth_level[$forumkey])
			{
				case 'public':
					$min_auth = "ANY";
					break;
				case 'registered':
					$min_auth = "REG";
					break;
				case 'private':
					$min_auth = "ACL";
					break;
				case 'moderate':
					$min_auth = "MOD";
					break;
				case 'admin':
					$min_auth = "MOD";
					break;
				default:
					$min_auth = "authall";
					break;
			}
			$single_group_acl = "";
			if(!empty($simple_auth_acl[$forumkey]))
			{
				switch($simple_auth_acl[$forumkey])
				{
					case 'single':
						$single_group_acl = "authuser";
						break;
					case 'group':
						$single_group_acl = "authgroup";
						break;
				}
			}
			$single_group_mod = "";
			if(!empty($simple_auth_mod[$forumkey]))
			{
				switch($simple_auth_mod[$forumkey])
				{
					case 'single':
						$single_group_mod = "authuser";
						break;
					case 'group':
						$single_group_mod = "authgroup";
						break;
				}
			}

			$row_class = ($i%2) ? "row2" : "row1";

			$template->assign_block_vars("forums", array(
				"ROW_CLASS" => $row_class, 
				"MIN_AUTH" => $min_auth, 
				"FORUM_NAME" => $forum_access[$i]['forum_name'], 

				"AUTH_TYPE_ACL" => $row_class . $single_group_acl, 
				"AUTH_TYPE_MOD" => $row_class . $single_group_mod, 

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
			echo "\t<td bgcolor=\"#DDDDDD\"><a href=\"userauth.$phpEx?" . POST_FORUM_URL . "=$forumkey&" . POST_USERS_URL . "=$user_id\">" . $f_access[$i]['forum_name'] . "</a></td>\n";
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

	$t_username .= $userinf[0]['username'];
	$t_usertype = ($is_admin) ? "an <b>Administrator</b>" : "a <b>User</b>";

	for($i = 0; $i < count($userinf); $i++)
	{
		if(!$userinf[$i]['group_single_user'])
		{
			$group_name[] = $userinf[$i]['group_name'];
			$group_id[] = $userinf[$i]['group_id'];
		}
	}
	
	if(count($group_name))
	{
		$t_usergroup_list = "belongs to the following groups; ";
		for($i = 0; $i < count($userinf); $i++)
		{
			$t_usergroup_list .= "<a href=\"groupauth.$phpEx?" . POST_GROUPS_URL . "=" . $group_id[$i] . "\">" . $group_name[$i] . "</a>";
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

	$s_hidden_fields = "<input type=\"hidden\" name=\"" . POST_USERS_URL . "\" value=\"$user_id\">";
	$s_hidden_fields .= "<input type=\"hidden\" name=\"curadmin\" value=\"" . $is_admin ."\">";
	$s_hidden_fields .= "<input type=\"hidden\" name=\"" . POST_GROUPS_URL . "\" value=\"" . "\">";

	$template->assign_vars(array(
		"USERNAME" => $t_username, 
		"USERTYPE" => $t_usertype, 
		
		"S_ADMIN_CHECK_SELECTED" => (($is_admin) ? " checked" : ""), 

		"S_USER_AUTH_ACTION" => append_sid("userauth.$phpEx"),
		"S_HIDDEN_FIELDS" => $s_hidden_fields,

		"USER_GROUP_LIST" => $t_usergroup_list)
	);

	$template->pparse("body");

	exit;

?>