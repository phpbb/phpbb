<?php
/***************************************************************************  
 *                            admin_userauth.php 
 *                            -------------------                         
 *   begin                : Saturday, Feb 13, 2001 
 *   copyright            : (C) 2001 The phpBB Group        
 *   email                : support@phpbb.com                           
 *                                                          
 *   $Id$                                                           
 *                                                            
 * 
 ***************************************************************************/ 


/***************************************************************************  
 *                                                     
 *   This program is free software; you can redistribute it and/or modify    
 *   it under the terms of the GNU General Public License as published by   
 *   the Free Software Foundation; either version 2 of the License, or  
 *   (at your option) any later version.                      
 *                                                          
 * 
 ***************************************************************************/ 

if($setmodules == 1)
{
	$filename = basename(__FILE__);
	$module['Groups']['Permissions'] = $filename;

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
if( !$userdata['session_logged_in'] )
{
	header("Location: ../login.$phpEx?forward_page=admin/");
}
else if( $userdata['user_level'] != ADMIN )
{
	message_die(GENERAL_MESSAGE, $lang['Not_admin']);
}

//
// Start program - define vars
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

$forum_auth_fields = array("auth_view", "auth_read", "auth_post", "auth_reply", "auth_edit", "auth_delete", "auth_sticky", "auth_announce");

$forum_auth_key_fields = array("auth_view", "auth_read", "auth_post", "auth_reply");

//
// Future stuff
//
//, "auth_votecreate", "auth_vote", "auth_attachments", "auth_allow_html", "auth_allow_bbcode", "auth_allow_smilies"
//
/*	, 
	"auth_vote" => AUTH_VOTE,
	"auth_votecreate" => AUTH_VOTECREATE,
	"auth_attachments" => AUTH_ATTACH,

	"auth_allow_html" => AUTH_ALLOW_HTML
	"auth_allow_bbcode" => AUTH_ALLOW_BBCODE
	"auth_allow_smilies" => AUTH_ALLOW_SMILIES
);*/


// ---------------
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
// End Functions
// -------------


//
//
//
$adv = (isset($HTTP_GET_VARS['adv'])) ? $HTTP_GET_VARS['adv'] : -1;

if(isset($HTTP_POST_VARS['submit']) && !empty($HTTP_POST_VARS[POST_GROUPS_URL]))
{
	$group_id = $HTTP_POST_VARS[POST_GROUPS_URL];
	$adv = (isset($HTTP_POST_VARS['adv'])) ? TRUE : FALSE;

	//
	// This is where things become fun ...
	//
	
	//
	// Get list of user id's for this group_id
	//
	$sql_groupid = "SELECT user_id 
		FROM " . USER_GROUP_TABLE . " 
		WHERE group_id = $group_id 
			AND user_id <> " . ANONYMOUS;
	if(!$result = $db->sql_query($sql_groupid))
	{
		// Error no such user/group
	}
	$ug_info = $db->sql_fetchrow($result);


	//
	// Pull all the auth/group 
	// for this user
	//

	$sql = "SELECT aa.*, g2.group_single_user, u.username, u.user_id, g.group_id, g.group_name 
		FROM " . AUTH_ACCESS_TABLE . " aa, " . AUTH_ACCESS_TABLE . " aa2, " . USER_GROUP_TABLE . " ug, " . USER_GROUP_TABLE . " ug2, " . GROUPS_TABLE . " g, " . GROUPS_TABLE . " g2, " . USERS_TABLE . " u 
		WHERE g.group_id = $group_id 
			AND aa.group_id = g.group_id 
			AND ug.group_id = g.group_id 
			AND ug2.user_id = ug.user_id 
			AND u.user_id = ug2.user_id 
			AND g2.group_id = ug2.group_id 
			AND aa2.group_id = g2.group_id";
/*
	$sql = "SELECT aa.*, g.group_single_user, g.group_id, g.group_name   
		FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE. " g   
		WHERE g.group_id = $group_id 
			AND aa.group_id = g.group_id";*/
	$ag_result = $db->sql_query($sql);

	if($num_g_access = $db->sql_numrows($ag_result))
	{
		$g_access = $db->sql_fetchrowset($ag_result);
	}

	$sql = "SELECT f.* 
		FROM " . FORUMS_TABLE . " f, " . CATEGORIES_TABLE . " c 
		WHERE c.cat_id = f.cat_id 
		ORDER BY c.cat_order ASC, f.forum_order ASC";
	$fa_result = $db->sql_query($sql);

	$forum_access = $db->sql_fetchrowset($fa_result);

	$change_prv_list = array();

	$change_mod_ary = (isset($HTTP_POST_VARS['moderator'])) ? $HTTP_POST_VARS['moderator'] : array();

	for($i = 0; $i < count($forum_access); $i++)
	{
		$forum_id = $forum_access[$i]['forum_id'];

		for($j = 0; $j < count($forum_auth_fields); $j++)
		{
			$field = $forum_auth_fields[$j];

			if( isset($HTTP_POST_VARS['private']) )
			{
				if( $forum_access[$i][$field] == AUTH_ACL )
				{
					if( isset($HTTP_POST_VARS['private'][$forum_id]) )
					{
						$change_prv_list[$forum_id][$field] = $HTTP_POST_VARS['private'][$forum_id];
					}
				}
			}
			else
			{
				if( isset($HTTP_POST_VARS[$field][$forum_id]) )
				{
					$change_prv_list[$forum_id][$field] = $HTTP_POST_VARS[$field][$forum_id];
				}
			}
		}
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
	// 
	//
	$warning_mod_userid = array();
	$warning_mod_username = array();
	$warning_mod_frmname = array();
	$valid_auth_mod_sql = array();

	$warning_prv_userid = array();
	$warning_prv_username = array();
	$warning_prv_frmname = array();
	$valid_auth_prv_sql = array();

	for($i = 0; $i < count($forum_access); $i++)
	{
		$this_forum_id = $forum_access[$i]['forum_id'];

		$update_mod = FALSE;
		$update_acl = FALSE;

		$valid_auth_mod_sql_val = "";
		$valid_auth_prv_sql_fld = "";
		$valid_auth_prv_sql_val = "";

		@reset($change_mod_ary);
		@reset($change_prv_list);

		//
		// Moderator control
		//
		while(list($mod_forum_id, $new_mod_status) = @each($change_mod_ary))
		{
			if($mod_forum_id == $this_forum_id)
			{
				$warned = FALSE;

				for($j = 0; $j < count($g_access); $j++)
				{
					if($g_access[$j]['forum_id'] == $this_forum_id)
					{
						$cur_mod_status = $g_access[$j]['auth_mod'];
						$is_single_user = $g_access[$j]['group_single_user'];

						if($cur_mod_status == $new_mod_status && !$is_single_user)
						{
							//
							// No need to update so set update to true
							//
							$update_mod = TRUE;
						}
						else if($cur_mod_status && !$new_mod_status && $is_single_user && !$warned)
						{
							//
							// users within group can mod via their own auth, we'll warn
							// the admin but carry out the op anyway if reqd.
							//
							$warning_mod_userid[$this_forum_id][] = $g_access[$j]['user_id'];
							$warning_mod_username[$this_forum_id][] = $g_access[$j]['username'];
							$warning_mod_frmname[$this_forum_id][] = $forum_access[$i]['forum_name'];

							$warned = TRUE;
						}
						else if($cur_mod_status != $new_mod_status && !$is_single_user)
						{
							if($new_mod_status)
							{
								$valid_auth_mod_sql[$this_forum_id] = "UPDATE " . AUTH_ACCESS_TABLE . " 
									SET auth_view = 0, auth_read = 0, auth_post = 0, auth_reply = 0, auth_edit = 0, auth_delete = 0, auth_announce = 0, auth_sticky = 0, auth_mod = $new_mod_status 
									WHERE forum_id = $this_forum_id 
										AND group_id = $group_id";
							}
							else
							{
								$valid_auth_mod_sql[$this_forum_id] = "DELETE FROM " . AUTH_ACCESS_TABLE . " 
									WHERE forum_id = $this_forum_id 
										AND group_id = $group_id";
							}
							$update_mod = TRUE;
						}
					}
				}

				if(!$update_mod && $new_mod_status)
				{
					$valid_auth_mod_sql[$this_forum_id] = "INSERT INTO " . AUTH_ACCESS_TABLE . " 
						(forum_id, group_id, auth_mod) 
						VALUES ($this_forum_id, $group_id, $new_mod_status)";
					$update_mod = TRUE;
				}
			}
		}

		//
		// Private/ACL control
		//
		while(list($prv_forum_id, $new_prv_ary) = @each($change_prv_list))
		{
			if($prv_forum_id == $this_forum_id && empty($valid_auth_mod_sql[$this_forum_id]) )
			{
				for($j = 0; $j < count($g_access); $j++)
				{
					if($g_access[$j]['forum_id'] == $this_forum_id)
					{
						$is_single_user = $u_access[$j]['group_single_user'];

						if(!$is_single_user)
						{
							$valid_auth_prv_sql[$this_forum_id] = "UPDATE " . AUTH_ACCESS_TABLE . " SET ";
						}

						$is_all_zeroed = FALSE;
						$warned = FALSE;

						//
						// Step through all auth fields
						//
						@reset($new_prv_ary);
						while( list($this_prv_field, $new_prv_status) = each($new_prv_ary) )
						{
							//
							// Is this field set to ACL?
							//
							$cur_prv_status = $g_access[$j][$this_prv_field];

							if($cur_prv_status == $new_prv_status && !$is_single_user)
							{
								//
								// No need to update so set update to true
								//
								$update_acl = TRUE;
							}
							else if( ( $cur_prv_status || $g_access[$j]['auth_mod'] ) && !$new_prv_status && $is_single_user && !$warned)
							{
								//
								// user can mod via group auth, we'll warn
								// the admin but carry out the op anyway if reqd.
								//
								$warning_prv_userid[$this_forum_id][] = $g_access[$j]['user_id'];
								$warning_prv_username[$this_forum_id][] = $g_access[$j]['username'];
								$warning_prv_frmname[$this_forum_id][] = $forum_access[$i]['forum_name'];
								$warned = TRUE;
							}
							else if($cur_prv_status != $new_prv_status && !$is_single_user)
							{
								if( $valid_auth_prv_sql_val != "")
								{
									$valid_auth_prv_sql_val .= ", ";
								}
								$valid_auth_prv_sql_val .= "$this_prv_field = $new_prv_status";

								$update_acl = TRUE;

								if(!$new_prv_status)
								{
									$is_all_zeroed = TRUE;
								}
							}
						}

						if(!$is_single_user)
						{
							if(!$is_all_zeroed)
							{
								$valid_auth_prv_sql[$this_forum_id] .= $valid_auth_prv_sql_val . " WHERE forum_id = $this_forum_id AND group_id = $group_id";
							}
							else
							{
								$valid_auth_prv_sql[$this_forum_id] = "DELETE FROM " . AUTH_ACCESS_TABLE . " 
									WHERE forum_id = $this_forum_id 
										AND group_id = $group_id";
							}
						}
						$valid_auth_prv_sql_val = "";
					}
				}

				if(!$update_acl)
				{
					//
					// Step through all auth fields
					//
					$all_zeroed = TRUE;

					@reset($new_prv_ary);
					while( list($this_prv_field, $new_prv_status) = each($new_prv_ary) )
					{
						//
						// Is this field set to ACL?
						//
						if( $valid_auth_prv_sql_fld != "" )
						{
							$valid_auth_prv_sql_fld .= ", ";
						}

						if( $valid_auth_prv_sql_val != "" )
						{
							$valid_auth_prv_sql_val .= ", ";
						}
						$valid_auth_prv_sql_fld .= "$this_prv_field";
						$valid_auth_prv_sql_val .= "$new_prv_status";

						if($new_prv_status)
						{
							$all_zeroed = FALSE;
						}
					}

					if(!$all_zeroed)
					{
						$valid_auth_prv_sql[$this_forum_id] = "INSERT INTO " . AUTH_ACCESS_TABLE . " (forum_id, group_id,  $valid_auth_prv_sql_fld) VALUES ($this_forum_id, $group_id, $valid_auth_prv_sql_val)";
					}

					$update_acl = TRUE;
				}
			}
		}
	}

	//
	// Checks complete, make updates to DB
	//
	while( list($chg_forum_id, $sql) = each($valid_auth_mod_sql) )
	{
		if( !empty($sql) )
		{
			if( !$result = $db->sql_query($sql) )
			{
				// Error ...
			}
		}
	}

	while( list($chg_forum_id, $sql) = each($valid_auth_prv_sql) )
	{
		if( !empty($sql) )
		{
			if( !$result = $db->sql_query($sql) )
			{
				// Error ...
			}
		}
	}

	//
	// Any warnings?
	//
	$warning_list = "";
	while( list($forum_id, $user_ary) = each($warning_mod_userid) )
	{
		for($i = 0; $i < count($user_ary); $i++)
		{
			if(!empty($valid_auth_mod_sql[$forum_id]))
			{
				$warning_list .= "<b><a href=\"admin_userauth.$phpEx?" . POST_USERS_URL . "=" . $user_ary[$i] . "\">" . $warning_mod_username[$forum_id][$i] . "</a></b> has moderator status on <b>" . $warning_mod_frmname[$forum_id][$i] . "</b><br />";
			}
		}
	}

	while( list($forum_id, $user_ary) = each($warning_prv_userid) )
	{
		for($i = 0; $i < count($user_ary); $i++)
		{
			if(!empty($valid_auth_prv_sql[$forum_id]))
			{
				$warning_list .= "<b><a href=\"admin_userauth.$phpEx?" . POST_USERS_URL . "=" . $user_ary[$i] . "\">" . $warning_prv_username[$forum_id][$i] . "</a></b> has access status to <b>" . $warning_prv_frmname[$forum_id][$i] . "</b><br />";
			}
		}
	}

	if($warning_list != "")
	{
		$warning_list = "<br />The following user/s still have access/moderator rights to this forum via their user auth settings. You may want to alter the user authorisation/s to fully prevent them having access/moderator rights. The users granted rights are noted below.<br/><br/>" . $warning_list . "<br />Click <a href=\"admin_groupauth.$phpEx?" . POST_GROUPS_URL . "=$group_id\">HERE</a> to return to group auth admin<br />";

		$template_header = "admin/page_header.tpl";
		include('page_header_admin.'.$phpEx);

		$template->set_filenames(array(
			"body" => "admin/admin_message_body.tpl")
		);
		$template->assign_vars(array(
			"MESSAGE_TITLE" => "Authorisation Conflict Warning", 
			"MESSAGE_TEXT" => $warning_list)
		);
	}
	else
	{
		header("Location: admin_groupauth.$phpEx?" . POST_GROUPS_URL . "=$group_id");
	}

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
		WHERE group_single_user <> " . TRUE;
	$g_result = $db->sql_query($sql);
	$group_list = $db->sql_fetchrowset($g_result);

	$select_list = "<select name=\"" . POST_GROUPS_URL . "\">";
	for($i = 0; $i < count($group_list); $i++)
	{
		$select_list .= "<option value=\"" . $group_list[$i]['group_id'] . "\">" . $group_list[$i]['group_name'] . "</option>";
	}
	$select_list .= "</select>";

	$template_header = "admin/page_header.tpl";
	include('page_header_admin.'.$phpEx);

	$template->set_filenames(array(
		"body" => "admin/auth_select_body.tpl")
	);

	$template->assign_vars(array(
		"L_AUTH_TITLE" => $lang['Group'] . " " . $lang['Auth_Control'], 
		"L_AUTH_EXPLAIN" => $lang['Group_auth_explain'], 
		"L_AUTH_SELECT" => $lang['Select_a'] . " " . $lang['Group'], 
		"L_LOOK_UP" => $lang['Look_up'] . " " . $lang['Group'], 

		"S_AUTH_ACTION" => append_sid("admin_groupauth.$phpEx"), 
		"S_AUTH_SELECT" => $select_list)
	);

}
else
{
	//
	// Front end
	//
	$group_id = $HTTP_GET_VARS[POST_GROUPS_URL];
	if( isset($HTTP_GET_VARS['adv']) )
	{
		$adv = $HTTP_GET_VARS['adv'];
	}
	else
	{
		$adv = FALSE;
	}

	$template_header = "admin/page_header.tpl";
	include('page_header_admin.'.$phpEx);

	$template->set_filenames(array(
		"body" => "admin/auth_ug_body.tpl")
	);

	//
	//
	//
	$sql = "SELECT f.* 
		FROM " . FORUMS_TABLE . " f, " . CATEGORIES_TABLE . " c 
		WHERE c.cat_id = f.cat_id 
		ORDER BY c.cat_order ASC, f.forum_order ASC";
	$fa_result = $db->sql_query($sql);

	$forum_access = $db->sql_fetchrowset($fa_result);

	if( empty($adv) )
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
	}

	$sql = "SELECT u.user_id, u.username, u.user_level, g.group_id, g.group_name, g.group_single_user  
		FROM " . USERS_TABLE . " u, " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug   
		WHERE g.group_id = $group_id 
			AND ug.group_id = g.group_id
			AND u.user_id = ug.user_id";
	$g_result = $db->sql_query($sql);
	$groupinf = $db->sql_fetchrowset($g_result);

	$sql = "SELECT aa.*   
		FROM " . AUTH_ACCESS_TABLE . " aa, " . GROUPS_TABLE. " g   
		WHERE g.group_id = $group_id 
			AND aa.group_id = g.group_id 
			AND g.group_single_user = 0";
	$ag_result = $db->sql_query($sql);

	$num_g_access = $db->sql_numrows($ag_result);

	if($num_g_access)
	{
		while($g_row = $db->sql_fetchrow($ag_result))
		{
			$g_access[$g_row['forum_id']][] = $g_row;
			$num_forum_access[$g_row['forum_id']]++;
		}
	}

	$auth_group = array();
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
					$auth_group[$f_forum_id][$key] = 1;
					break;

				case AUTH_REG:
					$auth_group[$f_forum_id][$key] = 1;
					break;

				case AUTH_ACL:
					if($num_forum_access[$f_forum_id])
					{
						$result = a_auth_check_user(AUTH_ACL, $key, $g_access[$f_forum_id], 0);
						$auth_group[$f_forum_id][$key] = $result['auth'];
						$auth_field_acl[$f_forum_id][$key] = $result['auth'];
					}
					else
					{
						$auth_group[$f_forum_id][$key] = 0;
					}
					break;
	
				case AUTH_MOD:
					if($num_forum_access[$f_forum_id])
					{
						$result = a_auth_check_user(AUTH_MOD, $key, $g_access[$f_forum_id], 0);
						$auth_group[$f_forum_id][$key] = $result['auth'];
					}
					else
					{
						$auth_group[$f_forum_id][$key] = 0;
					}
					break;

				case AUTH_ADMIN:
					$auth_group[$f_forum_id][$key] = 0;
					break;

				default:
					$auth_group[$f_forum_id][$key] = 0;
					break;
			}
		}

		//
		// Is user a moderator?
		//
		if($num_forum_access[$f_forum_id])
		{
			$result = a_auth_check_user(AUTH_MOD, 'auth_mod', $g_access[$f_forum_id], 0);
			$auth_group[$f_forum_id]['auth_mod'] = $result['auth'];
		}
		else
		{
			$auth_group[$f_forum_id][$key] = 0;
		}
	}

	$i = 0;
	while(list($forumkey, $group_ary) = each($auth_group))
	{
		if( empty($adv) )
		{
			if($basic_auth_level[$forumkey] == "private")
			{
				$allowed = 1;

				for($j = 0; $j < count($basic_auth_level_fields[$forumkey]); $j++)
				{
					if(!$group_ary[$basic_auth_level_fields[$forumkey][$j]])
					{
						$allowed = 0;
					}
				}
				$optionlist_acl = "<select name=\"private[$forumkey]\">";
				if( $group_ary['auth_mod'] )
				{
					$optionlist_acl .= "<option value=\"1\">Allowed Access</option>";
				}
				else if( $allowed )
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
		}
		else
		{
			@reset($forum_access);
			while(list($key, $forum_row) = each($forum_access))
			{
				$forum_id =  $forum_row['forum_id'];

				for($j = 0; $j < count($forum_auth_fields); $j++)
				{
					$field_name = $forum_auth_fields[$j];

					if( $forum_row[$field_name] == AUTH_ACL )
					{
						$optionlist_acl_adv[$forum_id][$j] = "<select name=\"" . $field_name . "[$forum_id]\">";

						if( isset($auth_field_acl[$forum_id][$field_name]) && !$group_ary['auth_mod'] )
						{
							if(!$auth_field_acl[$forum_id][$field_name])
							{
								$optionlist_acl_adv[$forum_id][$j] .= "<option value=\"1\">On</option><option value=\"0\" selected>Off</option>";
							}
							else
							{
								$optionlist_acl_adv[$forum_id][$j] .= "<option value=\"1\" selected>On</option><option value=\"0\">Off</option>";
							}
						}
						else
						{
							if( $group_ary['auth_mod'] )
							{
								$optionlist_acl_adv[$forum_id][$j] .= "<option value=\"1\">On</option>";
							}
							else
							{
								$optionlist_acl_adv[$forum_id][$j] .= "<option value=\"1\">On</option><option value=\"0\" selected>Off</option>";
							}
						}

						$optionlist_acl_adv[$forum_id][$j] .= "</select>";

					}
				}
			}
		}

		$optionlist_mod = "<select name=\"moderator[$forumkey]\">";
		if($group_ary['auth_mod'])
		{
			$optionlist_mod .= "<option value=\"1\" selected>Moderator</option><option value=\"0\">Not Moderator</option>";
		}
		else
		{
			$optionlist_mod .= "<option value=\"1\">Moderator</option><option value=\"0\" selected>Not Moderator</option>";
		}
		$optionlist_mod .= "</select>";

		$row_class = ($i%2) ? "row2" : "row1";

		$template->assign_block_vars("forums", array(
			"ROW_CLASS" => $row_class, 
			"FORUM_NAME" => $forum_access[$i]['forum_name'], 

			"U_FORUM_AUTH" => append_sid("admin_forumauth.$phpEx?f=" . $forum_access[$i]['forum_id']), 
	
			"S_MOD_SELECT" => $optionlist_mod)
		);

		if(!$adv)
		{
			$template->assign_block_vars("forums.aclvalues", array(
				"S_ACL_SELECT" => $optionlist_acl)
			);
		}
		else
		{
			for($j = 0; $j < count($forum_auth_fields); $j++)
			{
				$template->assign_block_vars("forums.aclvalues", array(
					"S_ACL_SELECT" => $optionlist_acl_adv[$forumkey][$j])
				);
			}
		}

		$i++;
	}
	@reset($auth_group);

	for($i = 0; $i < count($groupinf); $i++)
	{
		$username[] = $groupinf[$i]['username'];
		$user_id[] = $groupinf[$i]['user_id'];
	}

	$t_groupname .= $groupinf[0]['group_name'];

	if(count($username))
	{
		$t_usergroup_list = "";
		for($i = 0; $i < count($username); $i++)
		{
			$t_usergroup_list .= "<a href=\"admin_userauth.$phpEx?" . POST_USERS_URL . "=" . $user_id[$i] . "\">" . $username[$i] . "</a>";
			if($i < count($username) - 1)
			{
				$t_usergroup_list .= ", ";
			}
		}
	}
	else
	{
		$t_usergroup_list = "None";
	}

	$s_hidden_fields = "<input type=\"hidden\" name=\"" . POST_GROUPS_URL . "\" value=\"$group_id\">";
	$s_hidden_fields .= "<input type=\"hidden\" name=\"curadmin\" value=\"" . $is_admin ."\">";

	$s_column_span = 2; // Two columns always present
	if(!$adv)
	{
		$template->assign_block_vars("acltype", array(
			"L_UG_ACL_TYPE" => "Simple Auth Setting")
		);
		$s_column_span++;
	}
	else
	{
		for($i = 0; $i < count($forum_auth_fields); $i++)
		{
			$template->assign_block_vars("acltype", array(
				"L_UG_ACL_TYPE" => ucfirst(preg_replace("/auth_/", "", $forum_auth_fields[$i])))
			);
			$s_column_span++;
		}
	}
	
	$switch_mode = "admin_groupauth.$phpEx?" . POST_GROUPS_URL . "=" . $group_id . "&adv=";
	$switch_mode .= ( !$adv ) ? "1" : "0";
	$switch_mode_text = ( !$adv ) ? "Advanced Mode" : "Simple Mode";
	$u_switch_mode = '<a href="' . $switch_mode . '">' . $switch_mode_text . '</a>';

	$template->assign_vars(array(
		"USERNAME" => $t_groupname, 
		"USER_GROUP_MEMBERSHIPS" => "This group has the following members: $t_usergroup_list",

		"L_USER_OR_GROUPNAME" => $lang['Group_name'], 
		"L_AUTH_TITLE" => $lang['User'] . " " . $lang['Auth_Control'], 
		"L_AUTH_EXPLAIN" => $lang['User_auth_explain'], 
		"L_PERMISSIONS" => $lang['Permissions'], 
		"L_SUBMIT_CHANGES" => $lang['Submit_changes'],
		"L_RESET_CHANGES" => $lang['Reset_changes'],
		"U_USER_OR_GROUP" => append_sid("admin_groupauth.$phpEx"), 
		"U_SWITCH_MODE" => $u_switch_mode,

		"S_COLUMN_SPAN" => $s_column_span, 
		"S_AUTH_ACTION" => append_sid("admin_groupauth.$phpEx"),
		"S_HIDDEN_FIELDS" => $s_hidden_fields)
	);

}

$template->pparse("body");

include('page_footer_admin.'.$phpEx);

?>