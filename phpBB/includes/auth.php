<?php
/***************************************************************************  
 *                                 auth.php
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

/* Notes:
 * auth() returns: 
 * TRUE if the user authorized
 * FALSE if the user is not
 */
function auth($type, $forum_id, $userdata, $f_access = -1)
{
	global $db;

	switch($type)
	{
		case VIEW:
			$a_sql = "auth_view";
			break;
		case READ:
			$a_sql = "auth_read";
			break;
		case POST:
			$a_sql = "auth_post";
			break;
		case REPLY:
			$a_sql = "auth_reply";
			break;
		case EDIT:
			$a_sql = "auth_edit";
			break;
		case DELETE:
			$a_sql = "auth_delete";
			break;
		case VOTECREATE:
			$a_sql = "auth_votecreate";
			break;
		case VOTE:
			$a_sql = "auth_vote";
			break;
		default:
			break;
	}

	//
	// If f_access has been passed, or auth
	// is needed to return an array of forums
	// then we need to pull the auth information
	// on the given forum (or all forums)
	//
	if($f_access == -1 || $forum_id == LIST_ALL)
	{
		$forum_match_sql = ($forum_id != LIST_ALL) ? "WHERE forum_id = $forum_id" : "";
		$sql = "SELECT $a_sql AS forum_auth 
			FROM ".AUTH_FORUMS_TABLE." 
			$forum_match_sql";
		$af_result = $db->sql_query($sql);

		if($forum_id != LIST_ALL)
		{
			$f_access = $db->sql_fetchfield("forum_auth", -1, $af_result);
		}
		else
		{
			$f_access_rows = $db->sql_fetchrowset($af_result);

		}
	}

	//
	// If the user isn't logged on then
	// all we need do is check if the forum
	// has the type set to ALL, if yes then
	// they're good to go, if not then they
	// are denied access
	//
	if(!$userdata['session_logged_in'])
	{
		if($forum_id != LIST_ALL)
		{
			$auth_user = ($f_access == ALL) ? true : false;
		}
		else
		{
			$auth_user_list = array();
			for($i = 0; $i < count($auth_forum_rows); $i++)
			{
				$auth_user_list[] = ($f_access_rows['0']['forum_auth'] == ALL) ? true : false;
			}
		}

	}
	else 
	{
		//
		// If the user is logged on and the forum
		// type is either ALL or REG then the user
		// has access
		//
		if($f_access == ALL || $f_access == REG)
		{
			$auth_user = true;
		}
		else
		{
			//
			// If the type if ACL, MOD or ADMIN
			// then we need to see if the user has
			// specific permissions to do whatever it
			// is they want to do ... to do this
			// we pull relevant information for the user
			// (and any groups they belong to)
			//
			$forum_match_sql = ($forum_id != LIST_ALL) ? "AND ( aa.forum_id = $forum_id OR aa.forum_id = " . ALL . ")" : "";
			$sql = "SELECT aa.$a_sql AS user_auth, aa.auth_mod, aa.auth_admin, g.single_user 
				FROM ".AUTH_ACCESS_TABLE." aa, " . USER_GROUP_TABLE. " ug, " . GROUPS_TABLE. " g 
				WHERE ug.user_id = ".$userdata['user_id']. " 
					AND g.group_id = ug.group_id 
					AND aa.group_id = ug.group_id 
					$forum_match_sql";
			$au_result = $db->sql_query($sql);

			if(!$db->sql_numrows($au_result))
			{
				//
				// No entry was found for this user
				// thus they don't have access,
				// You are the Weakest Link, Goodbye!
				//
				$auth_user = false;
			}
			else
			{
				$u_access = $db->sql_fetchrowset($au_result);
			}

			$single_user = false;

			//
			// Now we compare the users access level
			// against the forums We assume here that
			// a moderator and admin automatically have
			// access to an ACL forum, similarly we assume
			// admins meet an auth requirement of MOD
			//
			// The access level assigned to a single user
			// automatically takes precedence over any
			// levels granted by that user being a member
			// of a multi-user usergroup, eg. a user
			// who is banned from a forum won't gain
			// access to it even if they belong to a group
			// which has access (and vice versa). This
			// check is done via the single_user check
			//
			switch($f_access)
			{
				case ACL:
					for($i = 0; $i < count($u_access); $i++)
					{
						if(!$single_user)
						{
							$auth_user = $auth_user || $u_access[$i]['user_auth'] || $u_access[$i]['auth_mod'] || $u_access[$i]['auth_admin'];
							$single_user = $u_access[$i]['single_user'];
						}
					}
					break;
		
				case MOD:
					for($i = 0; $i < count($u_access); $i++)
					{
						if(!$single_user)
						{
							$auth_user = $auth_user || $u_access[$i]['auth_mod'] || $u_access[$i]['auth_admin'];
							$single_user = $u_access[$i]['single_user'];
						}
					}
					break;
	
				case ADMIN:
					for($i = 0; $i < count($u_access); $i++)
					{
						if(!$single_user)
						{
							$auth_user = $auth_user || $u_access[$i]['auth_admin'];
							$single_user = $u_access[$i]['single_user'];
						}
					}
					break;

				default:
					$auth_user = false;
					break;
			}
		}
	}

	//
	// This currently only returns true or false
	// however it will also return an array if a listing
	// of all forums to which a user has access was requested.
	// 
	return ( ($forum_id != LIST_ALL) ? $auth_user : $auth_user_list );
}

?>