<?php
/***************************************************************************
 *
 *                            -------------------
 *   begin                : Thursday, Jul 12, 2001
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

if( $setmodules == 1 )
{
	$filename = basename(__FILE__);
	$module['Forums']['Add_new']    = "$filename?mode=add";
	$module['Forums']['Manage']	= "$filename?mode=manage";

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

include('page_header_admin.'.$phpEx);

$mode = ($HTTP_GET_VARS['mode']) ? $HTTP_GET_VARS['mode'] : $HTTP_POST_VARS['mode'];

switch($mode)
{
	case 'manage':

		$template->set_filenames(array(
			"body" => "admin/admin_forum_manage.tpl")
		);
		$template->assign_vars(array("S_MANAGE_ACTION" => append_sid("admin_forums.$phpEx"),
											  "L_FORUM" => $lang['Forum'],
											  "L_MODERATOR" => $lang['Moderator'],
											  "L_ORDER" => $lang['Order'],
											  "POST_FORUM_URL" => POST_FORUM_URL,
											  "L_REMOVE" => $lang['Remove'],
											  "L_EDIT" => $lang['Edit'],
											  "L_LOCK" => $lang['Lock'],
											  "L_UPDATE_ORDER" => $lang['Update_order'],
											  "L_ACTION" => $lang['Action']));

		$sql = "SELECT c.cat_id, c.cat_title, c.cat_order
			FROM " . CATEGORIES_TABLE . " c, " . FORUMS_TABLE . " f
			WHERE f.cat_id = c.cat_id
			GROUP BY c.cat_id, c.cat_title, c.cat_order
			ORDER BY c.cat_order";

		if(!$q_categories = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Could not query categories list", "", __LINE__, __FILE__, $sql);
		}

		if($total_categories = $db->sql_numrows($q_categories))
		{
			$category_rows = $db->sql_fetchrowset($q_categories);

			$sql = "SELECT f.forum_id, f.forum_name, f.forum_desc, f.cat_id, f.forum_order
						FROM  " . FORUMS_TABLE . " f
						ORDER BY f.cat_id, f.forum_order";

			if(!$q_forums = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not query forums information", "", __LINE__, __FILE__, $sql);
			}
			if( !$total_forums = $db->sql_numrows($q_forums) )
			{
				message_die(GENERAL_MESSAGE, $lang['No_forums']);
			}
			$forum_rows = $db->sql_fetchrowset($q_forums);

			//
			// Obtain list of moderators of each forum
			//
			$sql = "SELECT aa.forum_id, g.group_name, g.group_id, g.group_single_user, u.user_id, u.username
				FROM " . AUTH_ACCESS_TABLE . " aa, " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g, " . USERS_TABLE . " u
				WHERE aa.auth_mod = " . TRUE . "
					AND ug.group_id = aa.group_id
					AND g.group_id = aa.group_id
					AND u.user_id = ug.user_id
				ORDER BY aa.forum_id, g.group_id, u.user_id";

			if(!$q_forum_mods = $db->sql_query($sql))
			{
				message_die(GENERAL_ERROR, "Could not query forum moderator information", "", __LINE__, __FILE__, $sql);
			}
			$forum_mods_list = $db->sql_fetchrowset($q_forum_mods);

			for($i = 0; $i < count($forum_mods_list); $i++)
			{
				if($forum_mods_list[$i]['group_single_user'] || !$forum_mods_list[$i]['group_id'])
				{
					$forum_mods_single_user[$forum_mods_list[$i]['forum_id']][] = 1;

					$forum_mods_name[$forum_mods_list[$i]['forum_id']][] = $forum_mods_list[$i]['username'];
					$forum_mods_id[$forum_mods_list[$i]['forum_id']][] = $forum_mods_list[$i]['user_id'];
				}
				else
				{
					$forum_mods_single_user[$forum_mods_list[$i]['forum_id']][] = 0;

					$forum_mods_name[$forum_mods_list[$i]['forum_id']][] = $forum_mods_list[$i]['group_name'];
					$forum_mods_id[$forum_mods_list[$i]['forum_id']][] = $forum_mods_list[$i]['group_id'];
				}
			}

			for($i = 0; $i < $total_categories; $i++)
			{
				$cat_id = $category_rows[$i]['cat_id'];
				$count = 0;

				for($j = 0; $j < $total_forums; $j++)
				{
					$forum_id = $forum_rows[$j]['forum_id'];

					if($forum_rows[$j]['cat_id'] == $cat_id )
					{
						if(!$gen_cat[$cat_id])
						{
							$template->assign_block_vars("catrow", array(
								"CAT_DESC" => stripslashes($category_rows[$i]['cat_title']))
							);
							$gen_cat[$cat_id] = 1;
						}

						$mod_count = 0;
						$moderators_links = "";
						for($mods = 0; $mods < count($forum_mods_name[$forum_id]); $mods++)
						{
							if( !strstr($moderators_links, $forum_mods_name[$forum_id][$mods]) )
							{
								if($mods > 0)
								{
									$moderators_links .= ", ";
								}

								if( !($mod_count % 2) && $mod_count != 0 )
								{
									$moderators_links .= "<br />";
								}

								if( $forum_mods_single_user[$forum_id][$mods])
								{
									$moderators_links .= "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $forum_mods_id[$forum_id][$mods]) . "\">" . $forum_mods_name[$forum_id][$mods] . "</a>";
								}
								else
								{
									$moderators_links .= "<a href=\"" . append_sid("groupcp.$phpEx?" . POST_GROUPS_URL . "=" . $forum_mods_id[$forum_id][$mods]) . "\">" . $forum_mods_name[$forum_id][$mods] . "</a>";
								}

								$mod_count++;
							}
						}
						if($moderators_links == "")
						{
							$moderators_links = "&nbsp;";
						}

						$template->assign_block_vars("catrow.forumrow",	array(
							"FORUM_NAME" => stripslashes($forum_rows[$j]['forum_name']),
							"FORUM_DESC" => stripslashes($forum_rows[$j]['forum_desc']),
							"MODERATORS" => $moderators_links,
							"FORUM_ID" => $forum_id,
							"FORUM_ORDER" => $forum_rows[$j]['forum_order'],
							"U_VIEWFORUM" => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id"))
						);

						$count++;
					}
				}
			} // for ... categories
		}
		$template->pparse("body");
		break;

}
include('page_footer_admin.'.$phpEx);
?>