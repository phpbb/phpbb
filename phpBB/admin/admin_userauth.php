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
	$module['Auth']['Users'] = $filename;

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
	header("Location: ../login.$phpEx?forward_page=/admin");
}
else if( $userdata['user_level'] != ADMIN )
{
	message_die(GENERAL_MESSAGE, "You are not authorised to administer this board");
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

if(isset($HTTP_POST_VARS['submit']) && !empty($HTTP_POST_VARS[POST_USERS_URL]))
{
	$user_id = $HTTP_POST_VARS[POST_USERS_URL];

	//
	// This is where things become fun ...
	//
	
	//
	// Get group_id for this user_id
	//
	$sql_groupid = "SELECT ug.group_id, u.user_level 
		FROM " . USER_GROUP_TABLE . " ug, " . USERS_TABLE . " u, " . GROUPS_TABLE . " g  
		WHERE u.user_id = $user_id 
			AND ug.user_id = u.user_id 
			AND g.group_id = ug.group_id 
			AND g.group_single_user = " . TRUE;
	if(!$result = $db->sql_query($sql_groupid))
	{
		// Error no such user/group
	}
	$ug_info = $db->sql_fetchrow($result);

	//
	// Carry out requests
	//
	if( $HTTP_POST_VARS['userlevel'] == "user" && $ug_info['user_level'] == ADMIN )
	{
		//
		// Make admin a user (if already admin)
		//
		if( $userdata['user_id'] != $user_id )
		{
			//
			// Delete any entries granting in auth_access
			//
			$sql = "UPDATE " . AUTH_ACCESS_TABLE . " 
				SET auth_view = 0, auth_read = 0, auth_post = 0, auth_reply = 0, auth_edit = 0, auth_delete = 0, auth_sticky = 0, auth_announce = 0 
				WHERE group_id = " . $ug_info['group_id'];
			if(!$result = $db->sql_query($sql))
			{
				// Error ...
			} 

			//
			// Update users level, reset to USER
			//
			$sql = "UPDATE " . USERS_TABLE . " 
				SET user_level = " . USER . " 
				WHERE user_id = $user_id";
			if(!$result = $db->sql_query($sql))
			{
				// Error ...
			}
		}
	
		header("Location: admin_userauth.$phpEx?" . POST_USERS_URL . "=$user_id");

	}
	else if( $HTTP_POST_VARS['userlevel'] == "admin" && $ug_info['user_level'] != ADMIN )
	{

		//
		// Make user an admin (if already user)
		//
		$sql_userlevel = "UPDATE " . USERS_TABLE . " 
			SET user_level = " . ADMIN . " 
			WHERE user_id = $user_id";
		if(!$result = $db->sql_query($sql_userlevel))
		{
			// Error ...
		}
			
		// Delete any entries in auth_access, they
		// are unrequired if user is becoming an 
		// admin
		//
		$sql_unmod = "UPDATE " . AUTH_ACCESS_TABLE . " 
			SET auth_view = 0, auth_read = 0, auth_post = 0, auth_reply = 0, auth_edit = 0, auth_delete = 0, auth_sticky = 0, auth_announce = 0 
			WHERE group_id = " . $ug_info['group_id'];
		if(!$result = $db->sql_query($sql_unmod))
		{
			// Error ...
		} 

		$sql_unauth = "DELETE FROM " . AUTH_ACCESS_TABLE . "     
			WHERE group_id = $group_id 
				AND auth_mod = 0";
		if(!$result = $db->sql_query($sql_unauth))
		{
			// Error ...
		}

		header("Location: admin_userauth.$phpEx?" . POST_USERS_URL . "=$user_id");

	}
	else
	{

		$change_mod_ary = (isset($HTTP_POST_VARS['moderator'])) ? $HTTP_POST_VARS['moderator'] : array();
		$change_prv_ary = (isset($HTTP_POST_VARS['private'])) ? $HTTP_POST_VARS['private'] : array();
		if( !isset($change_prv_ary) )
		{


		}

		//
		// Pull all the auth/group 
		// for this user
		//
		$sql = "SELECT aa.forum_id, aa.auth_view, aa.auth_read, aa.auth_post, aa.auth_reply, aa.auth_edit, aa.auth_delete, aa.auth_sticky, aa.auth_announce, aa.auth_mod, g.group_single_user, g.group_id, g.group_name   
		FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE. " g   
			WHERE ug.user_id = $user_id 
				AND g.group_id = ug.group_id 
				AND aa.group_id = ug.group_id";
		$au_result = $db->sql_query($sql);

		if($num_u_access = $db->sql_numrows($au_result))
		{
			$u_access = $db->sql_fetchrowset($au_result);
		}

		$sql = "SELECT f.forum_id, f.forum_name, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_sticky, f.auth_announce 
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
		$warning_mod_grpid = array();
		$warning_mod_grpname = array();
		$warning_mod_frmname = array();
		$valid_auth_mod_sql = array();
		$valid_auth_mod_sql_val = "";

		$warning_prv_grpid = array();
		$warning_prv_grpname = array();
		$warning_prv_frmname = array();
		$valid_auth_prv_sql = array();
		$valid_auth_prv_sql_fld = "";
		$valid_auth_prv_sql_val = "";

		for($i = 0; $i < count($forum_access); $i++)
		{
			$this_forum_id = $forum_access[$i]['forum_id'];

			$update_mod = FALSE;
			$update_acl = FALSE;

			@reset($change_mod_ary);
			@reset($change_mod_prv);

			//
			// Moderator control
			//
			while(list($mod_forum_id, $new_mod_status) = @each($change_mod_ary))
			{
				if($mod_forum_id == $this_forum_id)
				{
					for($j = 0; $j < count($u_access); $j++)
					{
						if($u_access[$j]['forum_id'] == $this_forum_id)
						{
							$cur_mod_status = $u_access[$j]['auth_mod'];
							$is_single_user = $u_access[$j]['group_single_user'];

							if($cur_mod_status == $new_mod_status && $is_single_user)
							{
								//
								// No need to update so set update to true
								//
								$update_mod = TRUE;
							}
							else if($cur_mod_status && !$new_mod_status && !$is_single_user)
							{
								//
								// user can mod via group auth, we'll warn
								// the admin but carry out the op anyway if reqd.
								//
								$warning_mod_grpid[$this_forum_id][] = $u_access[$j]['group_id'];
								$warning_mod_grpname[$this_forum_id][] = $u_access[$j]['group_name'];
								$warning_mod_frmname[$this_forum_id][] = $forum_access[$i]['forum_name'];
							}
							else if($cur_mod_status != $new_mod_status && $is_single_user)
							{
								if($new_mod_status)
								{
									$valid_auth_mod_sql[$this_forum_id] = "UPDATE " . AUTH_ACCESS_TABLE . " 
										SET auth_view = 0, auth_read = 0, auth_post = 0, auth_reply = 0, auth_edit = 0, auth_delete = 0, auth_announce = 0, auth_sticky = 0, auth_mod = $new_mod_status 
										WHERE forum_id = $this_forum_id 
											AND group_id = " . $ug_info['group_id'];
								}
								else
								{
									$valid_auth_mod_sql[$this_forum_id] = "DELETE FROM " . AUTH_ACCESS_TABLE . " 
										WHERE forum_id = $this_forum_id 
											AND group_id = " . $ug_info['group_id'];
								}
								$update_mod = TRUE;
							}
						}
					}

					if(!$update_mod && $new_mod_status)
					{
						$valid_auth_mod_sql[$this_forum_id] = "INSERT INTO " . AUTH_ACCESS_TABLE . " 
							(forum_id, group_id, auth_mod) 
							VALUES ($this_forum_id, " . $ug_info['group_id'] . ", $new_mod_status)";
						$update_mod = TRUE;
					}
				}
			}

			//
			// Private/ACL control
			//
			while(list($prv_forum_id, $new_prv_status) = @each($change_prv_ary))
			{
				if($prv_forum_id == $this_forum_id && empty($valid_auth_mod_sql[$this_forum_id]) )
				{
					for($j = 0; $j < count($u_access); $j++)
					{
						if($u_access[$j]['forum_id'] == $this_forum_id)
						{
							$is_single_user = $u_access[$j]['group_single_user'];

							if($is_single_user)
							{
								$valid_auth_prv_sql[$this_forum_id] = "UPDATE " . AUTH_ACCESS_TABLE . " SET ";
							}

							$is_all_zeroed = FALSE;
							$warned = FALSE;

							//
							// Step through all auth fields
							//
							for($k = 0; $k < count($forum_auth_fields); $k++)
							{
								$this_prv_field = $forum_auth_fields[$k];

								//
								// Is this field set to ACL?
								//
								if($forum_access[$i][$this_prv_field] == AUTH_ACL)
								{
									$cur_prv_status = $u_access[$j][$this_prv_field];

									if($cur_prv_status == $new_prv_status && $is_single_user)
									{
										//
										// No need to update so set update to true
										//
										$update_acl = TRUE;
									}
									else if( ( $cur_prv_status || $u_access[$j]['auth_mod'] ) && !$new_prv_status && !$is_single_user && !$warned)
									{
										//
										// user can mod via group auth, we'll warn
										// the admin but carry out the op anyway if reqd.
										//
										$warning_prv_grpid[$this_forum_id][] = $u_access[$j]['group_id'];
										$warning_prv_grpname[$this_forum_id][] = $u_access[$j]['group_name'];
										$warning_prv_frmname[$this_forum_id][] = $forum_access[$i]['forum_name'];
										$warned = TRUE;
									}
									else if($cur_prv_status != $new_prv_status && $is_single_user)
									{
										if(!empty($valid_auth_prv_sql_val))
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
							}

							if($is_single_user)
							{
								if(!$is_all_zeroed)
								{
									$valid_auth_prv_sql[$this_forum_id] .= $valid_auth_prv_sql_val . " WHERE forum_id = $this_forum_id AND group_id = " . $ug_info['group_id'];
								}
								else
								{
									$valid_auth_prv_sql[$this_forum_id] = "DELETE FROM " . AUTH_ACCESS_TABLE . " 
										WHERE forum_id = $this_forum_id 
											AND group_id = " . $ug_info['group_id'];
								}
							}
							$valid_auth_prv_sql_val = "";
						}
					}

					if(!$update_acl && $new_prv_status)
					{
						$valid_auth_prv_sql[$this_forum_id] = "INSERT INTO " . AUTH_ACCESS_TABLE . " (forum_id, group_id, ";

						//
						// Step through all auth fields
						//
						for($k = 0; $k < count($forum_auth_fields); $k++)
						{
							$this_prv_field = $forum_auth_fields[$k];

							//
							// Is this field set to ACL?
							//
							if($forum_access[$i][$this_prv_field] == AUTH_ACL)
							{
								if( !empty($valid_auth_prv_sql_fld) )
								{
									$valid_auth_prv_sql_fld .= ", ";
								}

								if( !empty($valid_auth_prv_sql_val) )
								{
									$valid_auth_prv_sql_val .= ", ";
								}
								$valid_auth_prv_sql_fld .= "$this_prv_field";
								$valid_auth_prv_sql_val .= "$new_prv_status";
							}
						}

						$valid_auth_prv_sql[$this_forum_id] .= $valid_auth_prv_sql_fld . ") VALUES ($this_forum_id, " . $ug_info['group_id'] . ", " . $valid_auth_prv_sql_val . ")";

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
		while( list($forum_id, $group_ary) = each($warning_mod_grpid) )
		{
			for($i = 0; $i < count($group_ary); $i++)
			{
				if(!empty($valid_auth_mod_sql[$forum_id]))
				{
					$warning_list .= "<b><a href=\"admin_groupauth.$phpEx?" . POST_GROUPS_URL . "=" . $group_ary[$i] . "\">" . $warning_mod_grpname[$forum_id][$i] . "</a></b> grants moderator status to <b>" . $warning_mod_frmname[$forum_id][$i] . "</b> for this user<br />";
				}
			}
		}

		while( list($forum_id, $group_ary) = each($warning_prv_grpid) )
		{
			for($i = 0; $i < count($group_ary); $i++)
			{
				if(!empty($valid_auth_prv_sql[$forum_id]))
				{
					$warning_list .= "<b><a href=\"admin_groupauth.$phpEx?" . POST_GROUPS_URL . "=" . $group_ary[$i] . "\">" . $warning_prv_grpname[$forum_id][$i] . "</a></b> grants access status to <b>" . $warning_prv_frmname[$forum_id][$i] . "</b> for this user<br />";
				}
			}
		}

		if($warning_list != "")
		{
			$warning_list = "<br />This user still has access/moderator rights to this forum via group membership. You may want to alter the group authorisation or remove this user the group to fully prevent them having access/moderator rights. The groups granting rights are noted below.<br/><br/>" . $warning_list . "<br />Click <a href=\"admin_userauth.$phpEx?" . POST_USERS_URL . "=$user_id\">HERE</a> to return to user auth admin<br />";

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
			header("Location: admin_userauth.$phpEx?" . POST_USERS_URL . "=$user_id");
		}
	}

}
else if(empty($HTTP_GET_VARS[POST_USERS_URL]))
{
	//
	// Default user selection box
	//
	// This should be altered on the final system 
	//

	$sql = "SELECT user_id, username  
		FROM " . USERS_TABLE . " 
		WHERE user_id <> " . ANONYMOUS;
	$u_result = $db->sql_query($sql);
	$user_list = $db->sql_fetchrowset($u_result);

	$select_list = "<select name=\"" . POST_USERS_URL . "\">";
	for($i = 0; $i < count($user_list); $i++)
	{
		$select_list .= "<option value=\"" . $user_list[$i]['user_id'] . "\">" . $user_list[$i]['username'] . "</option>";
	}
	$select_list .= "</select>";

	$template_header = "admin/page_header.tpl";
	include('page_header_admin.'.$phpEx);

	$template->set_filenames(array(
		"body" => "admin/ug_auth_select_body.tpl")
	);

	$template->assign_vars(array(
		"L_USER_OR_GROUP" => "User", 

		"S_USERAUTH_ACTION" => append_sid("admin_userauth.$phpEx"), 
		"S_USERS_SELECT" => $select_list)
	);

}
else
{
	//
	// Front end
	//
	$user_id = $HTTP_GET_VARS[POST_USERS_URL];

	$template_header = "admin/page_header.tpl";
	include('page_header_admin.'.$phpEx);

	$template->set_filenames(array(
		"body" => "admin/ug_auth_body.tpl")
	);


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

		$sql = "SELECT u.user_id, u.username, u.user_level, g.group_id, g.group_name, g.group_single_user  
			FROM " . USERS_TABLE . " u, " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug   
			WHERE u.user_id = $user_id 
				AND ug.user_id = u.user_id 
				AND g.group_id = ug.group_id";
		$u_result = $db->sql_query($sql);
		$userinf = $db->sql_fetchrowset($u_result);

		$sql = "SELECT aa.forum_id, aa.auth_view, aa.auth_read, aa.auth_post, aa.auth_reply, aa.auth_edit, aa.auth_delete, aa.auth_mod  
			FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE. " g   
			WHERE ug.user_id = $user_id 
				AND g.group_id = ug.group_id 
				AND aa.group_id = ug.group_id 
				AND g.group_single_user = " . TRUE;
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

		$t_username .= $userinf[0]['username'];
		$s_user_type = ($is_admin) ? '<select name="userlevel"><option value="admin" selected>Administrator</option><option value="user">User</option></select>' : '<select name="userlevel"><option value="admin">Administrator</option><option value="user" selected>User</option></select>';

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
			$t_usergroup_list = "";
			for($i = 0; $i < count($userinf); $i++)
			{
				$t_usergroup_list .= "<a href=\"admin_groupauth.$phpEx?" . POST_GROUPS_URL . "=" . $group_id[$i] . "\">" . $group_name[$i] . "</a>";
				if($i < count($group_name) - 1)
				{
					$t_usergroup_list .= ", ";
				}
			}
		}
		else
		{
			$t_usergroup_list = "None";
		}

		$s_hidden_fields = "<input type=\"hidden\" name=\"" . POST_USERS_URL . "\" value=\"$user_id\">";
		$s_hidden_fields .= "<input type=\"hidden\" name=\"curadmin\" value=\"" . $is_admin ."\">";

		$template->assign_vars(array(
			"USERNAME" => $t_username, 
			"USER_GROUP_MEMBERSHIPS" => "This user is a $s_user_type and belongs to the following groups: $t_usergroup_list",

			"L_USER_OR_GROUPNAME" => "Username", 
			"L_USER_OR_GROUP" => "User", 

			"U_USER_OR_GROUP" => append_sid("admin_userauth.$phpEx"), 
			"U_FORUMAUTH" => append_sid("admin_forumauth.$phpEx"), 

			"S_USER_AUTH_ACTION" => append_sid("admin_userauth.$phpEx"),
			"S_HIDDEN_FIELDS" => $s_hidden_fields)
		);

	} // if adv == -1

}

$template->pparse("body");

include('page_footer_admin.'.$phpEx);

?>