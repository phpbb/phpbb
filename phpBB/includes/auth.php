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

	//
	// If not logged on all we
	// need do is find out
	// if $forum_id has ANY 
	// auth for $type
	//
	// If logged on we want to
	// find out if $forum_id has
	// ALL, REG, ACL, MOD or ADMIN
	// for $type
	//
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

	if($f_access == -1 || $forum_id == LIST_ALL)
	{
		$forum_match_sql = ($forum_id != LIST_ALL) ? "" : "WHERE forum_id = $forum_id";
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
		if($f_access == ALL || $f_access == REG)
		{
			$auth_user = true;
		}
		else
		{
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
				// No entry was found
				// for this forum and user
				// thus they don't have
				// access
				//
				$auth_user = false;
			}
			else
			{
				$u_access = $db->sql_fetchrowset($au_result);
			}

			$single_user = false;

//			echo "<br><BR>".$f_access."<BR>".ADMIN."<BR>";
			switch($f_access)
			{
				case ACL:
//					echo "HERE1";
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
//					echo "HERE2";
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
//					echo "HERE3";
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
//					echo "HERE4";
					$auth_user = false;
					break;
			}
		}
	}

	return ( ($forum_id != LIST_ALL) ? $auth_user : $auth_user_list );
}

?>