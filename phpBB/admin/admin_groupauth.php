<?php

if($setmodules == 1)
{
	$filename = basename(__FILE__);
	$module['Auth']['groups'] = $filename;

	return;
}

$phpbb_root_path = "./../";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

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
	"auth_announce" => AUTH_ANNOUNCE);
/*	, 
	"auth_vote" => AUTH_VOTE,
	"auth_votecreate" => AUTH_VOTECREATE,
	"auth_attachments" => AUTH_ATTACH,

	"auth_allow_html" => AUTH_ALLOW_HTML
	"auth_allow_bbcode" => AUTH_ALLOW_BBCODE
	"auth_allow_smilies" => AUTH_ALLOW_SMILIES
);*/
$forum_auth_fields = array("auth_view", "auth_read", "auth_post", "auth_reply", "auth_edit", "auth_delete", "auth_sticky", "auth_announce");
//, "auth_votecreate", "auth_vote", "auth_attachments", "auth_allow_html", "auth_allow_bbcode", "auth_allow_smilies"
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

if(isset($HTTP_POST_VARS['submit']) && !empty($HTTP_POST_VARS[POST_GROUPS_URL]))
{
	$group_id = $HTTP_POST_VARS[POST_GROUPS_URL];

	//
	// This is where things become fun ...
	//

	$change_mod_ary = (isset($HTTP_POST_VARS['moderator'])) ? $HTTP_POST_VARS['moderator'] : 0;
	$change_prv_ary = (isset($HTTP_POST_VARS['private'])) ? $HTTP_POST_VARS['private'] : 0;

	//
	// Pull all the auth/group 
	// for this group
	//
	$sql = "SELECT aa.forum_id, aa.auth_view, aa.auth_read, aa.auth_post, aa.auth_reply, aa.auth_edit, aa.auth_delete, aa.auth_sticky, aa.auth_announce, aa.auth_mod, g.group_single_user  
	FROM " . AUTH_ACCESS_TABLE . " aa, " . GROUPS_TABLE. " g   
		WHERE g.group_id = $group_id  
			AND aa.group_id = g.group_id";
	$au_result = $db->sql_query($sql);

	if($num_u_access = $db->sql_numrows($au_result))
	{
		$u_access = $db->sql_fetchrowset($au_result);
	}

	$sql = "SELECT f.forum_id, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_sticky, f.auth_announce 
		FROM " . FORUMS_TABLE . " f, " . CATEGORIES_TABLE . " c 
		WHERE c.cat_id = f.cat_id 
		ORDER BY c.cat_order ASC, f.forum_order ASC";
	$fa_result = $db->sql_query($sql);

	$forum_access = $db->sql_fetchrowset($fa_result);

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
	// 
	//
	$warning_mod = array();
	$valid_auth_mod = array();
	$valid_auth_mod_sql = array();

	@reset($change_mod_ary);

	while(list($chg_forum_id, $value) = @each($change_mod_ary))
	{
		$a_match = $value;

		$auth_exists = FALSE;

		for($i = 0; $i < count($u_access); $i++)
		{
			$forum_id = $u_access[$i]['forum_id'];

			if( $forum_id == $chg_forum_id )
			{

				if( $u_access[$i]['auth_mod'] == $value && !$u_access[$i]['group_single_user'] )
				{
					$a_match = -1;
				}
				else if( $u_access[$i]['auth_mod'] && !$value && $u_access[$i]['group_single_user'] )
				{
					//
					// User is being removed as a moderator but is a moderator
					// via a group, carry out the update but warn the moderator
					//
					$warning_mod[$chg_forum_id] = TRUE;
				}
				else
				{
					if(!$value)
					{
						$sql = "DELETE FROM " . AUTH_ACCESS_TABLE; 
					}
					else
					{
						$sql = "UPDATE " . AUTH_ACCESS_TABLE . " 
							SET auth_view = 0, auth_read = 0, auth_post = 0, auth_reply = 0, auth_edit = 0, auth_delete = 0, auth_sticky = 0, auth_announce = 0, auth_mod = " . TRUE;
					}
					$valid_auth_mod_sql[$chg_forum_id] = $sql . " WHERE forum_id = $chg_forum_id AND group_id = $group_id";
					$valid_auth_mod[$chg_forum_id] = 1;
				}

				$auth_exists = TRUE;
			}
		}
		
		if(!$auth_exists && $value)
		{
			$valid_auth_mod_sql[$chg_forum_id] = "INSERT INTO " . AUTH_ACCESS_TABLE . " (forum_id, group_id, auth_mod) VALUES ($chg_forum_id, $group_id, 1)";
			$valid_auth_mod[$chg_forum_id] = 0;
		}
	}


	//
	// Check against priv access table ... 
	//
	$warning_mod = array();
	$valid_auth_acl_sql = array();

	@reset($valid_auth_mod);
	@reset($change_prv_ary);

	while(list($chg_forum_id, $value) = @each($change_prv_ary))
	{
		$valid_auth_acl_sql[$chg_forum_id] = "";
		$auth_exists = FALSE;

		for($i = 0; $i < count($u_access); $i++)
		{
			if( $u_access[$i]['forum_id'] == $chg_forum_id )
			{

				//
				// If we're updating/inserting a moderator access
				// control then we don't need to both with anything here, 
				// adding (or updating) a user to mod status automatically
				// grants access to all forum functions (unless they
				// are set at admin status!). Removing moderator permissions
				// automatically removes all priviledges, it does mean the
				// admin has to re-enable ACL privs but it does prevent
				// them accidently leaving a user with access to a forum
				// they should be now denied.
				//
//				echo "<BR>" . $chg_forum_id . " : " . $valid_auth_mod[$chg_forum_id] . "<BR>";

//				echo $chg_forum_id . " : " . $valid_auth_mod[$chg_forum_id] . " : " . $u_access[$i]['auth_mod'] . "<BR>";

				if( empty($valid_auth_mod[$chg_forum_id]) && !$u_access[$i]['auth_mod'])
				{
					//
					// User isn't a moderator so now we have to decide whether the
					// the access needs creating, updating or deleting ...
					//

					for($j = 0; $j < count($forum_access); $j++)
					{
						if( $chg_forum_id == $forum_access[$j]['forum_id'] )
						{
							$update_acl_sql = "";

							for($k = 0; $k < count($forum_auth_fields); $k++)
							{
								$auth_field = $forum_auth_fields[$k];

								if( $forum_access[$j][$auth_field] == AUTH_ACL )
								{

									if( $u_access[$i][$auth_field] && !$value && $u_access[$i]['group_single_user'] )
									{
										//
										// User is having ACL access removed from this field 
										// but retains access via a group they belong too, 
										// carry out the update but warn the moderator
										//

										$warning_acl[$chg_forum_id][$auth_field] = TRUE;
									}
									else if( $u_access[$i][$auth_field] != $value && !$u_access[$i]['group_single_user'] )
									{
										$update_acl_sql .= ($update_acl_sql != "") ? ", $auth_field = $value" : "$auth_field = $value";
									}
								}
							}

							$valid_auth_acl_sql[$chg_forum_id] = "UPDATE " . AUTH_ACCESS_TABLE . " SET " . $update_acl_sql ." WHERE forum_id = $chg_forum_id AND group_id = $group_id";

						} // forum_id = forum_access

					} // for ... forum_access

				} // not_mod

				$auth_exists = TRUE;

			} // if forum ... chg_forum

		} // for ... u_access

		if($valid_auth_acl_sql[$chg_forum_id] == "" && !$auth_exists)
		{
			for($j = 0; $j < count($forum_access); $j++)
			{
				if( $chg_forum_id == $forum_access[$j]['forum_id'] && $value)
				{
					$valid_auth_acl_sql_val = "";
					$valid_auth_acl_sql_fld = "";

					for($k = 0; $k < count($forum_auth_fields); $k++)
					{
						$auth_field = $forum_auth_fields[$k];

						if( $forum_access[$j][$auth_field] == AUTH_ACL )
						{
							$valid_auth_acl_sql_fld .= ($valid_auth_acl_sql_fld != "") ? ", $auth_field" : "$auth_field"; 
							$valid_auth_acl_sql_val .= ($valid_auth_acl_sql_val != "") ? ", $value" : "$value";
						}
					}

					$valid_auth_acl_sql[$chg_forum_id] = "INSERT INTO " . AUTH_ACCESS_TABLE . " (forum_id, group_id,  $valid_auth_acl_sql_fld) VALUES ($chg_forum_id, $group_id, $valid_auth_acl_sql_val)";
				}
			}
		}
	}

//	print_r($valid_auth_acl_sql);
//	echo "<BR><BR>";

	//
	// The next part requires that we know whether we're
	// updating an existing entry, inserting a new one or
	// deleting an existing entry ... as well as what we're
	// updating and with what value ...
	//
		
	//
	// Checks complete, make updates
	//
	while(list($chg_forum_id, $sql) = each($valid_auth_mod_sql))
	{
		if( !empty($sql) )
		{
			if(!$result = $db->sql_query($sql))
			{
				// Error ...
			}
		}
	}

	while(list($chg_forum_id, $sql) = each($valid_auth_acl_sql))
	{
		if( !empty($sql) )
		{
			if(!$result = $db->sql_query($sql))
			{
				// Error ...
			}
		}
	}

	header("Location: admin_groupauth.$phpEx?" . POST_GROUPS_URL . "=$group_id");

}
else if(empty($HTTP_GET_VARS[POST_GROUPS_URL]))
{
	//
	// Default user selection box
	//
	// This should be altered on the final system 
	//

	$sql = "SELECT group_id, group_name  
		FROM " . GROUPS_TABLE . " 
		WHERE group_single_user <> 1";
	$u_result = $db->sql_query($sql);

	$user_list = $db->sql_fetchrowset($u_result);

	$select_list = "<select name=\"" . POST_GROUPS_URL . "\">";
	for($i = 0; $i < count($user_list); $i++)
	{
		$select_list .= "<option value=\"" . $user_list[$i]['group_id'] . "\">" . $user_list[$i]['group_name'] . "</option>";
	}
	$select_list .= "</select>";

	$template->set_filenames(array(
		"body" => "admin/ug_auth_select_body.tpl"));

	$template->assign_vars(array(
		"L_USER_OR_GROUP" => "Group", 

		"S_USERAUTH_ACTION" => append_sid("admin_groupauth.$phpEx"), 
		"S_USERS_SELECT" => $select_list, 
		
		"U_FORUMAUTH" => append_sid("admin_forumauth.$phpEx"))
	);

	$template->pparse("body");

	exit;

}


//
// Front end
//

$template->set_filenames(array(
	"body" => "admin/ug_auth_body.tpl")
);

$group_id = $HTTP_GET_VARS[POST_GROUPS_URL];

$sql = "SELECT f.forum_id, f.forum_name, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_announce, f.auth_sticky 
	FROM " . FORUMS_TABLE . " f, " . CATEGORIES_TABLE . " c 
	WHERE c.cat_id = f.cat_id 
	ORDER BY c.cat_order ASC, f.forum_order ASC";
$fa_result = $db->sql_query($sql);

$forum_access = $db->sql_fetchrowset($fa_result);

if($adv == -1)
{
	for($i = 0; $i < count($forum_access); $i++)
	{
		while(list($forum_id, $forum_row) = each($forum_access))
		{
			for($j = 0; $j < count($forum_auth_key_fields); $j++)
			{
				$basic_auth_level[$forum_row['forum_id']] = "public";

				if($forum_row[$forum_auth_key_fields[$j]] == AUTH_REG)
				{
					$basic_auth_level[$forum_row['forum_id']] = "registered";
					$basic_auth_level_fields[$forum_row['forum_id']][] = $forum_auth_fields[$j];
				}
				else if($forum_row[$forum_auth_key_fields[$j]] == AUTH_ACL)
				{
					$basic_auth_level[$forum_row['forum_id']] = "private";
					$basic_auth_level_fields[$forum_row['forum_id']][] = $forum_auth_fields[$j];
				}
				else if($forum_row[$forum_auth_key_fields[$j]] == AUTH_MOD)
				{
					$basic_auth_level[$forum_row['forum_id']] = "moderator";
					$basic_auth_level_fields[$forum_row['forum_id']][] = $forum_auth_fields[$j];
				}
				else if($forum_row[$forum_auth_key_fields[$j]] == AUTH_ADMIN)
				{
					$basic_auth_level[$forum_row['forum_id']] = "admin";
					$basic_auth_level_fields[$forum_row['forum_id']][] = $forum_auth_fields[$j];
				}
			}
		}
	}

	$sql = "SELECT u.user_id, u.username, g.group_id, g.group_name, g.group_single_user  
		FROM " . USERS_TABLE . " u, " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug   
		WHERE g.group_id = $group_id 
			AND ug.group_id = g.group_id 
			AND u.user_id = ug.user_id";
	$g_result = $db->sql_query($sql);

	$groupinf = $db->sql_fetchrowset($g_result);

	$sql = "SELECT aa.forum_id, aa.auth_view, aa.auth_read, aa.auth_post, aa.auth_reply, aa.auth_edit, aa.auth_delete, aa.auth_mod  
		FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE. " g   
		WHERE g.group_id = $group_id 
			AND aa.group_id = g.group_id 
			AND g.group_single_user <> " . TRUE;
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
						$result = a_auth_check_user(AUTH_ACL, $key, $u_access[$f_forum_id], 0);
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
						$result = a_auth_check_user(AUTH_MOD, $key, $u_access[$f_forum_id], 0);
						$auth_user[$f_forum_id][$key] = $result['auth'];
					}
					else
					{
						$auth_user[$f_forum_id][$key] = 0;
					}
					break;
	
				case AUTH_ADMIN:
					$auth_user[$f_forum_id][$key] = 0;
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
			$result = a_auth_check_user(AUTH_MOD, 'auth_mod', $u_access[$f_forum_id], 0);
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
				$optionlist_acl = "<select name=\"private[$forumkey]\">";
				if($is_admin || $user_ary['auth_mod'])
				{
					$optionlist_acl .= "<option value=\"1\">Allowed Access</option>";
				}
				else if($allowed)
				{
					$optionlist_acl .= "<option value=\"1\" selected>Allowed Access</option><option value=\"0\">Disallowed Access</option>";
				}
				else
				{
					$optionlist_acl .= "<option value=\"1\">Allowed Access</option><option value=\"0\" selected>Disallowed Access</option>";
				}
				$optionlist_acl .= "</select>";
			}
			else
			{
				$optionlist_acl = "&nbsp;";
			}

			$optionlist_mod = "<select name=\"moderator[$forumkey]\">";
			if($user_ary['auth_mod'])
			{
				$optionlist_mod .= "<option value=\"1\" selected>Is a Moderator</option><option value=\"0\">Is not a Moderator</option>";
			}
			else
			{
				$optionlist_mod .= "<option value=\"1\">Is a Moderator</option><option value=\"0\" selected>Is not a Moderator</option>";
			}
			$optionlist_mod .= "</select>";

			$row_class = ($i%2) ? "row2" : "row1";

			$template->assign_block_vars("forums", array(
				"ROW_CLASS" => $row_class, 
				"FORUM_NAME" => $forum_access[$i]['forum_name'], 

				"U_FORUM_AUTH" => append_sid("admin_forumauth.$phpEx?f=" . $forum_access[$i]['forum_id']), 
			
				"S_ACL_SELECT" => $optionlist_acl,
				"S_MOD_SELECT" => $optionlist_mod)
			);
			$i++;
		}
	}
	reset($auth_user);

	$t_groupname .= $groupinf[0]['group_name'];

	for($i = 0; $i < count($groupinf); $i++)
	{
		$username[] = $groupinf[$i]['username'];
		$user_id[] = $groupinf[$i]['user_id'];
	}
	
	if(count($username))
	{
		$t_username_list = "";

		for($i = 0; $i < count($groupinf); $i++)
		{
			$t_username_list .= "<a href=\"admin_userauth.$phpEx?" . POST_USERS_URL . "=" . $user_id[$i] . "\">" . $username[$i] . "</a>";
			if($i < count($username) - 1)
			{
				$t_username_list .= ", ";
			}
		}
	}
	else
	{
		$t_username_list = "<b>Has no members</b>";
	}

	$s_hidden_fields = "<input type=\"hidden\" name=\"" . POST_GROUPS_URL . "\" value=\"$group_id\">";

	$template->assign_vars(array(
		"USERNAME" => $t_groupname, 
		"USER_GROUP_MEMBERSHIPS" => "This group has the following members: $t_username_list",

		"L_USER_OR_GROUPNAME" => "Groupname", 
		"L_USER_OR_GROUP" => "Group", 

		"U_USER_OR_GROUP" => append_sid("admin_groupauth.$phpEx"), 
		"U_FORUMAUTH" => append_sid("admin_forumauth.$phpEx"), 
		
		"S_USER_AUTH_ACTION" => append_sid("admin_groupauth.$phpEx"),
		"S_HIDDEN_FIELDS" => $s_hidden_fields)
	);

} // if adv == -1

$template->pparse("body");

exit;

?>