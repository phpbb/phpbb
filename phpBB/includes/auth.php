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

/*
	$type's accepted (eventually!):
	VIEW, READ, POST, REPLY, EDIT, DELETE, VOTE, VOTECREATE, MOD, ADMIN

	Possible options to send to auth (not all are functional yet!):

	* If you include a type then a specific lookup will
	be done and the single result returned

	* If you set type to ALL an array of all auth types
	will be returned

	* If you provide a forum_id a specific lookup on that
	forum will be done

	* If you set forum_id to LIST_ALL an array of all
	forums to which the user has access of type will be returned
	<- used for index and search? (type VIEW and READ respectively)
	
	* If you set forum_id to LIST_ALL and type to ALL a 
	multidimensional array containing the auth permissions
	for all types and all forums for that user is returned

	* If you set $userdata to ALL, then the permissions of all
	users listed in the auth_access table will be returned for 
	the given type and forum_id <- use to check for moderators?

	All results are returned as associative arrays, even
	when a single auth type is specified

*/
function auth($type, $forum_id, $userdata, $f_access = -1)
{
	global $db;

	switch($type)
	{
		case AUTH_ALL:
			$a_sql = "aa.auth_view, aa.auth_read, aa.auth_post, aa.auth_reply, aa.auth_edit, aa.auth_delete, aa.auth_votecreate, aa.auth_vote";
			$auth_fields = array("auth_view", "auth_read", "auth_post", "auth_reply", "auth_edit", "auth_delete", "auth_votecreate", "auth_vote");
			break;
		case AUTH_VIEW:
			$a_sql = "aa.auth_view";
			$auth_fields = array("auth_view");
			break;
		case AUTH_READ:
			$a_sql = "aa.auth_read";
			$auth_fields = array("auth_read");
			break;
		case AUTH_POST:
			$a_sql = "aa.auth_post";
			$auth_fields = array("auth_post");
			break;
		case AUTH_REPLY:
			$a_sql = "aa.auth_reply";
			$auth_fields = array("auth_reply");
			break;
		case AUTH_EDIT:
			$a_sql = "aa.auth_edit";
			$auth_fields = array("auth_edit");
			break;
		case AUTH_DELETE:
			$a_sql = "aa.auth_delete";
			$auth_fields = array("auth_delete");
			break;
		case AUTH_VOTECREATE:
			$a_sql = "aa.auth_votecreate";
			$auth_fields = array("auth_votecreate");
			break;
		case AUTH_VOTE:
			$a_sql = "aa.auth_vote";
			$auth_fields = array("auth_vote");
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
	if($f_access == -1)
	{
		$forum_match_sql = ($forum_id != AUTH_LIST_ALL) ? "WHERE aa.forum_id = $forum_id" : "";
		$sql = "SELECT $a_sql 
			FROM ".AUTH_FORUMS_TABLE." aa 
			$forum_match_sql";
		$af_result = $db->sql_query($sql);

		$f_access = $db->sql_fetchrow($af_result);
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
		if($forum_id != AUTH_LIST_ALL)
		{
			for($i = 0; $i < count($f_access); $i++)
			{
				$auth_user[$auth_fields[$i]] = ($f_access[$auth_fields[$i]] == AUTH_ALL) ? 1 : 0;
			}
		}
		else
		{
			$auth_user_list = array();
			for($i = 0; $i < count($f_access); $i++)
			{
				for($j = 0; $j < count($auth_fields); $j++)
				{
					$auth_user_list[$f_access[$i]['forum_id']][$auth_fields[$j]] = ($f_access[$i][$auth_fields[$j]] == AUTH_ALL) ? 1 : 0;
				}
			}
		}
	}
	else 
	{
		$forum_match_sql = ($forum_id != AUTH_LIST_ALL) ? "AND aa.forum_id = $forum_id" : "";
		$sql = "SELECT aa.forum_id, $a_sql, aa.auth_mod, g.single_user, u.user_level  
			FROM ".AUTH_ACCESS_TABLE." aa, " . USER_GROUP_TABLE. " ug, " . GROUPS_TABLE. " g, " . USERS_TABLE . " u 
			WHERE ug.user_id = ".$userdata['user_id']. " 
				AND g.group_id = ug.group_id 
				AND aa.group_id = ug.group_id 
				AND u.user_id = ug.user_id 
				$forum_match_sql";
		$au_result = $db->sql_query($sql);

		$u_access = $db->sql_fetchrowset($au_result);

		$auth_user = array();
		for($i = 0; $i < count($auth_fields); $i++)
		{
			$key = $auth_fields[$i];
			$value = $f_access[$key];

			//
			// If the user is logged on and the forum
			// type is either ALL or REG then the user
			// has access
			//
			if($value == AUTH_ALL || $value == AUTH_REG)
			{
				$auth_user[$key] = 1;
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
	
				$single_user = 0;

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
				// PS : I appologise for the fantastically clear
				// and hugely readable code here ;) Simple gist
				// is, if this row of auth_access doesn't represent
				// a single user then OR the contents of relevant auth_access
				// levels against the current level (allows
				// maximum group privileges to be assigned). If
				// the row does represent a single user then forget
				// any previous group results and instead set
				// the auth to whatever the OR'd contents of the
				// access levels are.
				//
				switch($value)
				{
					case AUTH_ACL:
						for($j = 0; $j < count($u_access); $j++)
						{
							if(!$single_user)
							{
								$single_user = $u_access[$j]['single_user'];

								$auth_user[$key] = (!$single_user) ? ($auth_user[$key] || $u_access[$j][$key] || $u_access[$i]['auth_mod'] || ($userdata['user_level'] == ADMIN)) : ($u_access[$j][$key] || $u_access[$i]['auth_mod'] || ($userdata['user_level'] == ADMIN));
							}
						}
						break;
		
					case AUTH_MOD:
						for($j = 0; $j < count($u_access); $j++)
						{
							if(!$single_user)
							{
								$single_user = $u_access[$j]['single_user'];

								$auth_user[$key] = (!$single_user) ? ($auth_user[$key] || $u_access[$i]['auth_mod'] || ($userdata['user_level'] == ADMIN)) : ($u_access[$i]['auth_mod'] || ($userdata['user_level'] == ADMIN));
							}
						}
						break;
	
					case AUTH_ADMIN:
						//
						// Pretty redundant right now ...
						// 
						$auth_user[$key] = ($userdata['user_level'] == ADMIN) ? 1 : 0;
						break;

					default:
						$auth_user[$key] = 0;
						break;
				}
			}
		}
	
		//
		// Is user a moderator?
		//
		$single_user = 0;
		for($j = 0; $j < count($u_access); $j++)
		{
			if(!$single_user)
			{
				$auth_user['auth_mod'] = $auth_user['auth_mod'] || $u_access[$j]['auth_mod'];
				$single_user = $u_access[$j]['single_user'];
			}
		}

		//
		// Is user an admin (this is
		// really redundant at this time)
		//
		$auth_user[$key] = ($userdata['user_level'] == ADMIN) ? 1 : 0;

	}

	//
	// This currently only returns 1 or 0
	// however it will also return an array if a listing
	// of all forums to which a user has access was requested.
	// 
	return ( ($forum_id != AUTH_LIST_ALL) ? $auth_user : $auth_user_list );
}

?>