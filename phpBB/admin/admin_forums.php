<?php
/***************************************************************************
 *										admin_forums.php
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
 ***************************************************************************/

if($setmodules==1)
{
	$file = basename(__FILE__);
	$module['Forums']['Manage'] = $file;
	return;
}

//
// Load default header
//
$phpbb_root_dir = "./../";
require('pagestart.inc');

$forum_auth_ary = array(
	"auth_view" => AUTH_ALL, 
	"auth_read" => AUTH_ALL, 
	"auth_post" => AUTH_ALL, 
	"auth_reply" => AUTH_ALL, 
	"auth_edit" => AUTH_REG, 
	"auth_delete" => AUTH_REG, 
	"auth_sticky" => AUTH_REG, 
	"auth_announce" => AUTH_MOD, 
	"auth_vote" => AUTH_REG, 
	"auth_pollcreate" => AUTH_REG
);

//
// Mode setting
//
if( isset($HTTP_POST_VARS['mode']) || isset($HTTP_GET_VARS['mode']) )
{
	$mode = ( isset($HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
}
else
{
	$mode = "";
}

// ------------------
// Begin function block
//
function get_info($mode, $id)
{
	global $db;

	switch($mode)
	{
		case 'category':
			$table = CATEGORIES_TABLE;
			$idfield = 'cat_id';
			$namefield = 'cat_title';
			break;

		case 'forum':
			$table = FORUMS_TABLE;
			$idfield = 'forum_id';
			$namefield = 'forum_name';
			break;

		default:
			message_die(GENERAL_ERROR, "Wrong mode for generating select list", "", __LINE__, __FILE__);
			break;
	}
	$sql = "SELECT count(*) as total
		FROM $table";
	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't get Forum/Category information", "", __LINE__, __FILE__, $sql);
	}
	$count = $db->sql_fetchrow($result);
	$count = $count['total'];

	$sql = "SELECT *
		FROM $table
		WHERE $idfield = $id"; 

	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't get Forum/Category information", "", __LINE__, __FILE__, $sql);
	}

	if( $db->sql_numrows($result) != 1 )
	{
		message_die(GENERAL_ERROR, "Forum/Category doesn't exist or multiple forums/categories with ID $id", "", __LINE__, __FILE__);
	}

	$return = $db->sql_fetchrow($result);
	$return['number'] = $count;
	return $return;
}

function get_list($mode, $id, $select)
{
	global $db;

	switch($mode)
	{
		case 'category':
			$table = CATEGORIES_TABLE;
			$idfield = 'cat_id';
			$namefield = 'cat_title';
			break;

		case 'forum':
			$table = FORUMS_TABLE;
			$idfield = 'forum_id';
			$namefield = 'forum_name';
			break;

		default:
			message_die(GENERAL_ERROR, "Wrong mode for generating select list", "", __LINE__, __FILE__);
			break;
	}

	$sql = "SELECT *
		FROM $table";
	if( $select == 0 )
	{
		$sql .= " WHERE $idfield <> $id";
	}

	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't get list of Categories/Forums", "", __LINE__, __FILE__, $sql);
	}

	$cat_list = "";

	while( $row = $db->sql_fetchrow($result) )
	{
		$s = "";
		if ($row[$idfield] == $id)
		{
			$s = " selected=\"selected\"";
		}
		$catlist .= "<option value=\"$row[$idfield]\"$s>" . $row[$namefield] . "</option>\n";
	}

	return($catlist);
}

function renumber_order($mode, $cat = 0)
{
	global $db;

	switch($mode)
	{
		case 'category':
			$table = CATEGORIES_TABLE;
			$idfield = 'cat_id';
			$orderfield = 'cat_order';
			$cat = 0;
			break;

		case 'forum':
			$table = FORUMS_TABLE;
			$idfield = 'forum_id';
			$orderfield = 'forum_order';
			$catfield = 'cat_id';
			break;

		default:
			message_die(GENERAL_ERROR, "Wrong mode for generating select list", "", __LINE__, __FILE__);
			break;
	}

	$sql = "SELECT * FROM $table";
	if( $cat != 0)
	{
		$sql .= " WHERE $catfield = $cat";
	}
	$sql .= " ORDER BY $orderfield ASC";


	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't get list of Categories", "", __LINE__, __FILE__, $sql);
	}

	$i = 10;
	$inc = 10;

	while( $row = $db->sql_fetchrow($result) )
	{
		$sql = "UPDATE $table
			SET $orderfield = $i
			WHERE $idfield = " . $row[$idfield];
		if( !$db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't update order fields", "", __LINE__, __FILE__, $sql);
		}
		$i += 10;
	}

}
//
// End function block
// ------------------

//
// Begin program proper
//
if( isset($HTTP_POST_VARS['addforum']) || isset($HTTP_POST_VARS['addcategory']) )
{
	$mode = ( isset($HTTP_POST_VARS['addforum']) ) ? "addforum" : "addcat";

	if( $mode == "addforum" )
	{
		list($cat_id) = each($HTTP_POST_VARS['addforum']);
		// 
		// stripslashes needs to be run on this because slashes are added when the forum name is posted
		//
		$forumname = stripslashes($HTTP_POST_VARS['forumname'][$cat_id]);
	}
}

if( !empty($mode) ) 
{
	switch($mode)
	{
		case 'addforum':
		case 'editforum':
			//
			// Show form to create/modify a forum
			//
			if ($mode == 'editforum')
			{
				// $newmode determines if we are going to INSERT or UPDATE after posting?

				$l_title = $lang['Edit_forum'];
				$newmode = 'modforum';
				$buttonvalue = $lang['Update'];

				$forum_id = intval($HTTP_GET_VARS['forum_id']);

				$row = get_info('forum', $forum_id);

				$cat_id = $row['cat_id'];
				$forumname = $row['forum_name'];
				$forumdesc = $row['forum_desc'];
				$forumstatus = $row['forum_status'];

				//
				// start forum prune stuff.
				//
				if( $row['prune_enable'] )
				{
					$prune_enabled = "checked=\"checked\"";
					$sql = "SELECT *
               			FROM " . PRUNE_TABLE . "
               			WHERE forum_id = $forum_id";
					if(!$pr_result = $db->sql_query($sql))
					{
						 message_die(GENERAL_ERROR, "Auto-Prune: Couldn't read auto_prune table.", __LINE__, __FILE__);
        			}

					$pr_row = $db->sql_fetchrow($pr_result);
				}
				else
				{
					$prune_enabled = '';
				}
			}
			else
			{
				$l_title = $lang['Create_forum'];
				$newmode = 'createforum';
				$buttonvalue = $lang['Create_forum'];

				$forumdesc = '';
				$forumstatus = FORUM_UNLOCKED;
				$forum_id = ''; 
				$prune_enabled = '';
			}

			$catlist = get_list('category', $cat_id, TRUE);

			$forumstatus == ( FORUM_LOCKED ) ? $forumlocked = "selected=\"selected\"" : $forumunlocked = "selected=\"selected\"";
			$statuslist = "<option value=\"" . FORUM_UNLOCKED . "\" $forumunlocked>Unlocked</option>\n";
			$statuslist .= "<option value=\"" . FORUM_LOCKED . "\" $forumlocked>Locked</option>\n";

			$template->set_filenames(array(
				"body" => "admin/forum_edit_body.tpl")
			);

			$s_hidden_fields = '<input type="hidden" name="mode" value="' . $newmode .'" /><input type="hidden" name="forum_id" value="' . $forum_id . '" />';

			$template->assign_vars(array(
				'S_FORUM_ACTION' => append_sid("admin_forums.$phpEx"),
				'S_HIDDEN_FIELDS' => $s_hidden_fields,
				'S_SUBMIT_VALUE' => $buttonvalue, 
				'S_CAT_LIST' => $catlist,
				'S_STATUS_LIST' => $statuslist,
				'S_PRUNE_ENABLED' => $prune_enabled,

				'L_FORUM_TITLE' => $l_title, 
				'L_FORUM_EXPLAIN' => $lang['Forum_edit_delete_explain'], 
				'L_FORUM_SETTINGS' => $lang['Forum_settings'], 
				'L_FORUM_NAME' => $lang['Forum_name'], 
				'L_CATEGORY' => $lang['Category'], 
				'L_FORUM_DESCRIPTION' => $lang['Forum_desc'],
				'L_FORUM_STATUS' => $lang['Forum_status'],
				'L_AUTO_PRUNE' => $lang['Forum_pruning'],
				'L_ENABLED' => $lang['Enabled'],
				'L_PRUNE_DAYS' => $lang['prune_days'],
				'L_PRUNE_FREQ' => $lang['prune_freq'],
				'L_DAYS' => $lang['days'],

				'PRUNE_DAYS' => ( isset($pr_row['prune_days']) ) ? $pr_row['prune_days'] : 7,
				'PRUNE_FREQ' => ( isset($pr_row['prune_freq']) ) ? $pr_row['prune_freq'] : 1,
				'FORUM_NAME' => $forumname,
				'DESCRIPTION' => $forumdesc)
			);
			$template->pparse("body");
			break;

		case 'createforum':
			//
			// Create a forum in the DB
			//
			if( trim($HTTP_POST_VARS['forumname']) == "" )
			{
				message_die(GENERAL_ERROR, "Can't create a forum without a name");
			}

			$sql = "SELECT MAX(forum_order) AS max_order
				FROM " . FORUMS_TABLE . "
				WHERE cat_id = " . intval($HTTP_POST_VARS['cat_id']);
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't get order number from forums table", "", __LINE__, __FILE__, $sql);
			}
			$row = $db->sql_fetchrow($result);

			$max_order = $row['max_order'];
			$next_order = $max_order + 1;
			
			$sql = "SELECT MAX(forum_id) AS max_id
				FROM " . FORUMS_TABLE;
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't get order number from forums table", "", __LINE__, __FILE__, $sql);
			}
			$row = $db->sql_fetchrow($result);

			$max_id = $row['max_id'];
			$next_id = $max_id + 1;

			//
			// Default permissions of public :: 
			//
			$field_sql = "";
			$value_sql = "";
			while( list($field, $value) = each($forum_auth_ary) )
			{
				$field_sql .= ", $field";
				$value_sql .= ", $value";

			}

			// There is no problem having duplicate forum names so we won't check for it.
			$sql = "INSERT INTO " . FORUMS_TABLE . " (forum_id, forum_name, cat_id, forum_desc, forum_order, forum_status, prune_enable" . $field_sql . ")
				VALUES ('" . $next_id . "', '" . $HTTP_POST_VARS['forumname'] . "', " . intval($HTTP_POST_VARS['cat_id']) . ", '" . $HTTP_POST_VARS['forumdesc'] . "', $next_order, " . intval($HTTP_POST_VARS['forumstatus']) . ", " . intval($HTTP_POST_VARS['prune_enable']) . $value_sql . ")";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't insert row in forums table", "", __LINE__, __FILE__, $sql);
			}

			if( $HTTP_POST_VARS['prune_enable'] )
			{
				$new_forum_id = $db->sql_nextid();

				if($HTTP_POST_VARS['prune_days'] == "" || $HTTP_POST_VARS['prune_freq'] == "")
				{
					message_die(GENERAL_MESSAGE, $lang['Set_prune_data']);
				}

				$sql = "INSERT INTO " . PRUNE_TABLE . " (forum_id, prune_days, prune_freq)
					VALUES($new_forum_id, " . intval($HTTP_POST_VARS['prune_days']) . ", " . intval($HTTP_POST_VARS['prune_freq']) . ")";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't insert row in prune table", "", __LINE__, __FILE__, $sql);
				}
			}

			$message = $lang['Forums_updated'] . "<br /><br />" . sprintf($lang['Click_return_forumadmin'], "<a href=\"" . append_sid("admin_forums.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);

			break;

		case 'modforum':
			// Modify a forum in the DB
			if( isset($HTTP_POST_VARS['prune_enable']))
			{
				if( $HTTP_POST_VARS['prune_enable'] != 1 )
				{
					$HTTP_POST_VARS['prune_enable'] = 0;
				}
			}

			$sql = "UPDATE " . FORUMS_TABLE . "
				SET forum_name = '" . $HTTP_POST_VARS['forumname'] . "', cat_id = " . intval($HTTP_POST_VARS['cat_id']) . ", forum_desc = '" . $HTTP_POST_VARS['forumdesc'] . "', forum_status = " . intval($HTTP_POST_VARS['forumstatus']) . ", prune_enable = " . intval($HTTP_POST_VARS['prune_enable']) . "
				WHERE forum_id = " . intval($HTTP_POST_VARS['forum_id']);
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't update forum information", "", __LINE__, __FILE__, $sql);
			}

			if($HTTP_POST_VARS['prune_enable'] == 1)
			{
				if($HTTP_POST_VARS['prune_days'] == "" || $HTTP_POST_VARS['prune_freq'] == "")
				{
					message_die(GENERAL_MESSAGE, $lang['Set_prune_data']);
				}

				$sql = "SELECT *
					FROM " . PRUNE_TABLE . "
					WHERE forum_id = " . intval($HTTP_POST_VARS['forum_id']);
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't get forum Prune Information","",__LINE__, __FILE__, $sql);
				}

				if( $db->sql_numrows($result) > 0 )
				{
					$sql = "UPDATE " . PRUNE_TABLE . "
						SET	prune_days = " . intval($HTTP_POST_VARS['prune_days']) . ",	prune_freq = " . intval($HTTP_POST_VARS['prune_freq']) . "
				 		WHERE forum_id = " . intval($HTTP_POST_VARS['forum_id']);
				}
				else
				{
					$sql = "INSERT INTO " . PRUNE_TABLE . " (forum_id, prune_days, prune_freq)
						VALUES(" . intval($HTTP_POST_VARS['forum_id']) . ", " . intval($HTTP_POST_VARS['prune_days']) . ", " . intval($HTTP_POST_VARS['prune_freq']) . ")";
				}

				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't Update Forum Prune Information","",__LINE__, __FILE__, $sql);
				}
			}

			$message = $lang['Forums_updated'] . "<br /><br />" . sprintf($lang['Click_return_forumadmin'], "<a href=\"" . append_sid("admin_forums.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);

			break;
			
		case 'addcat':
			// Create a category in the DB
			if( trim($HTTP_POST_VARS['categoryname']) == '')
			{
				message_die(GENERAL_ERROR, "Can't create a category without a name");
			}

			$sql = "SELECT MAX(cat_order) AS max_order
				FROM " . CATEGORIES_TABLE;
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't get order number from categories table", "", __LINE__, __FILE__, $sql);
			}
			$row = $db->sql_fetchrow($result);

			$max_order = $row['max_order'];
			$next_order = $max_order + 1;

			//
			// There is no problem having duplicate forum names so we won't check for it.
			//
			$sql = "INSERT INTO " . CATEGORIES_TABLE . " (cat_title, cat_order)
				VALUES ('" . $HTTP_POST_VARS['categoryname'] . "', $next_order)";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't insert row in categories table", "", __LINE__, __FILE__, $sql);
			}

			$message = $lang['Forums_updated'] . "<br /><br />" . sprintf($lang['Click_return_forumadmin'], "<a href=\"" . append_sid("admin_forums.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);

			break;
			
		case 'editcat':
			//
			// Show form to edit a category
			//
			$newmode = 'modcat';
			$buttonvalue = $lang['Update'];

			$cat_id = intval($HTTP_GET_VARS['cat_id']);

			$row = get_info('category', $cat_id);
			$cat_title = $row['cat_title'];

			$template->set_filenames(array(
				"body" => "admin/category_edit_body.tpl")
			);

			$s_hidden_fields = '<input type="hidden" name="mode" value="' . $newmode . '" /><input type="hidden" name="cat_id" value="' . $cat_id . '" />';

			$template->assign_vars(array(
				'CAT_TITLE' => $cat_title,

				'L_EDIT_CATEGORY' => $lang['Edit_Category'], 
				'L_EDIT_CATEGORY_EXPLAIN' => $lang['Edit_Category_explain'], 
				'L_CATEGORY' => $lang['Category'], 

				'S_HIDDEN_FIELDS' => $s_hidden_fields, 
				'S_SUBMIT_VALUE' => $buttonvalue, 
				'S_FORUM_ACTION' => append_sid("admin_forums.$phpEx"))
			);

			$template->pparse("body");
			break;

		case 'modcat':
			// Modify a category in the DB
			$sql = "UPDATE " . CATEGORIES_TABLE . "
				SET cat_title = '" . $HTTP_POST_VARS['cat_title'] . "'
				WHERE cat_id = " . intval($HTTP_POST_VARS['cat_id']);
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't update forum information", "", __LINE__, __FILE__, $sql);
			}

			$message = $lang['Forums_updated'] . "<br /><br />" . sprintf($lang['Click_return_forumadmin'], "<a href=\"" . append_sid("admin_forums.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);

			break;
			
		case 'deleteforum':
			// Show form to delete a forum
			$forum_id = intval($HTTP_GET_VARS['forum_id']);

			$select_to = '<select name="to_id">';
			$select_to .= "<option value=\"-1\"$s>" . $lang['Delete_all_posts'] . "</option>\n";
			$select_to .= get_list('forum', $forum_id, 0);
			$select_to .= '</select>';

			$buttonvalue = $lang['Move_and_Delete'];

			$newmode = 'movedelforum';

			$foruminfo = get_info('forum', $forum_id);
			$name = $foruminfo['forum_name'];

			$template->set_filenames(array(
				"body" => "admin/forum_delete_body.tpl")
			);

			$s_hidden_fields = '<input type="hidden" name="mode" value="' . $newmode . '" /><input type="hidden" name="from_id" value="' . $forum_id . '" />';

			$template->assign_vars(array(
				'NAME' => $name, 

				'L_FORUM_DELETE' => $lang['Forum_delete'], 
				'L_FORUM_DELETE_EXPLAIN' => $lang['Forum_delete_explain'], 
				'L_MOVE_CONTENTS' => $lang['Move_contents'], 
				'L_FORUM_NAME' => $lang['Forum_name'], 

				"S_HIDDEN_FIELDS" => $s_hidden_fields,
				'S_FORUM_ACTION' => append_sid("admin_forums.$phpEx"), 
				'S_SELECT_TO' => $select_to,
				'S_SUBMIT_VALUE' => $buttonvalue)
			);

			$template->pparse("body");
			break;

		case 'movedelforum':
			//
			// Move or delete a forum in the DB
			//
			$from_id = intval($HTTP_POST_VARS['from_id']);
			$to_id = intval($HTTP_POST_VARS['to_id']);
			$delete_old = intval($HTTP_POST_VARS['delete_old']);

			// Either delete or move all posts in a forum
			if($to_id == -1)
			{
				include($phpbb_root_path . "includes/prune.$phpEx");
				prune($from_id, 0); // Delete everything from forum
			}
			else
			{
				$sql = "SELECT *
					FROM " . FORUMS_TABLE . "
					WHERE forum_id IN ($from_id, $to_id)";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't verify existence of forums", "", __LINE__, __FILE__, $sql);
				}
				if($db->sql_numrows($result) != 2)
				{
					message_die(GENERAL_ERROR, "Ambiguous forum ID's", "", __LINE__, __FILE__);
				}
				$sql = "UPDATE " . TOPICS_TABLE . "
					SET forum_id = $to_id
					WHERE forum_id = $from_id";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't move topics to other forum", "", __LINE__, __FILE__, $sql);
				}
				$sql = "UPDATE " . POSTS_TABLE . "
					SET	forum_id = $to_id
					WHERE forum_id = $from_id";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't move posts to other forum", "", __LINE__, __FILE__, $sql);
				}
				sync('forum', $to_id);
			}

			$sql = "DELETE FROM " . FORUMS_TABLE . "
				WHERE forum_id = $from_id";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't delete forum", "", __LINE__, __FILE__, $sql);
			}

			$message = $lang['Forums_updated'] . "<br /><br />" . sprintf($lang['Click_return_forumadmin'], "<a href=\"" . append_sid("admin_forums.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);

			break;
			
		case 'deletecat':
			//
			// Show form to delete a category
			//
			$cat_id = intval($HTTP_GET_VARS['cat_id']);

			$buttonvalue = $lang['Move_and_Delete'];
			$newmode = 'movedelcat';
			$catinfo = get_info('category', $cat_id);
			$name = $catinfo['cat_title'];

			if ($catinfo['number'] == 1)
			{
				$sql = "SELECT count(*) as total
					FROM ". FORUMS_TABLE;
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't get Forum count", "", __LINE__, __FILE__, $sql);
				}
				$count = $db->sql_fetchrow($result);
				$count = $count['total'];

				if ($count > 0)
				{
					message_die(GENERAL_ERROR, $lang['Must_delete_forums']);
				}
				else
				{
					$select_to = $lang['Nowhere_to_move'];
				}
			}
			else
			{
				$select_to = '<select name="to_id">';
				$select_to .= get_list('category', $cat_id, 0);
				$select_to .= '</select>';
			}

			$template->set_filenames(array(
				"body" => "admin/forum_delete_body.tpl")
			);

			$s_hidden_fields = '<input type="hidden" name="mode" value="' . $newmode . '" /><input type="hidden" name="from_id" value="' . $cat_id . '" />';

			$template->assign_vars(array(
				'NAME' => $name, 

				'L_FORUM_DELETE' => $lang['Forum_delete'], 
				'L_FORUM_DELETE_EXPLAIN' => $lang['Forum_delete_explain'], 
				'L_MOVE_CONTENTS' => $lang['Move_contents'], 
				'L_FORUM_NAME' => $lang['Forum_name'], 
				
				'S_HIDDEN_FIELDS' => $s_hidden_fields,
				'S_FORUM_ACTION' => append_sid("admin_forums.$phpEx"), 
				'S_SELECT_TO' => $select_to,
				'S_SUBMIT_VALUE' => $buttonvalue)
			);

			$template->pparse("body");
			break;

		case 'movedelcat':
			//
			// Move or delete a category in the DB
			//
			$from_id = intval($HTTP_POST_VARS['from_id']);
			$to_id = intval($HTTP_POST_VARS['to_id']);

			if (!empty($to_id))
			{
				$sql = "SELECT *
					FROM " . CATEGORIES_TABLE . "
					WHERE cat_id IN ($from_id, $to_id)";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't verify existence of categories", "", __LINE__, __FILE__, $sql);
				}
				if($db->sql_numrows($result) != 2)
				{
					message_die(GENERAL_ERROR, "Ambiguous category ID's", "", __LINE__, __FILE__);
				}

				$sql = "UPDATE " . FORUMS_TABLE . "
					SET cat_id = $to_id
					WHERE cat_id = $from_id";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't move forums to other category", "", __LINE__, __FILE__, $sql);
				}
			}

			$sql = "DELETE FROM " . CATEGORIES_TABLE ."
				WHERE cat_id = $from_id";
				
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't delete category", "", __LINE__, __FILE__, $sql);
			}

			$message = $lang['Forums_updated'] . "<br /><br />" . sprintf($lang['Click_return_forumadmin'], "<a href=\"" . append_sid("admin_forums.$phpEx") . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_admin_index'], "<a href=\"" . append_sid("index.$phpEx?pane=right") . "\">", "</a>");

			message_die(GENERAL_MESSAGE, $message);

			break;

		case 'forum_order':
			//
			// Change order of forums in the DB
			//
			$move = intval($HTTP_GET_VARS['move']);
			$forum_id = intval($HTTP_GET_VARS['forum_id']);

			$forum_info = get_info('forum', $forum_id);

			$cat_id = $forum_info['cat_id'];

			$sql = "UPDATE " . FORUMS_TABLE . "
				SET forum_order = forum_order + $move
				WHERE forum_id = $forum_id";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't change category order", "", __LINE__, __FILE__, $sql);
			}

			renumber_order('forum', $forum_info['cat_id']);
			$show_index = TRUE;

			break;
			
		case 'cat_order':
			//
			// Change order of categories in the DB
			//
			$move = intval($HTTP_GET_VARS['move']);
			$cat_id = intval($HTTP_GET_VARS['cat_id']);

			$sql = "UPDATE " . CATEGORIES_TABLE . "
				SET cat_order = cat_order + $move
				WHERE cat_id = $cat_id";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Couldn't change category order", "", __LINE__, __FILE__, $sql);
			}

			renumber_order('category');
			$show_index = TRUE;

			break;

		case 'forum_sync':
			sync('forum', intval($HTTP_GET_VARS['forum_id']));
			$show_index = TRUE;

			break;

		default:
			message_die(GENERAL_MESSAGE, $lang['No_mode']);
			break;
	}

	if ($show_index != TRUE)
	{
		include('page_footer_admin.'.$phpEx);
		exit;
	}
}

//
// Start page proper
//
$template->set_filenames(array(
	"body" => "admin/forum_admin_body.tpl")
);

$template->assign_vars(array(
	'S_FORUM_ACTION' => append_sid("admin_forums.$phpEx"),
	'L_FORUM_TITLE' => $lang['Forum_admin'], 
	'L_FORUM_EXPLAIN' => $lang['Forum_admin_explain'], 
	'L_CREATE_FORUM' => $lang['Create_forum'], 
	'L_CREATE_CATEGORY' => $lang['Create_category'], 
	'L_EDIT' => $lang['Edit'], 
	'L_DELETE' => $lang['Delete'], 
	'L_MOVE_UP' => $lang['Move_up'], 
	'L_MOVE_DOWN' => $lang['Move_down'], 
	'L_RESYNC' => $lang['Resync'])
);

$sql = "SELECT cat_id, cat_title, cat_order
	FROM " . CATEGORIES_TABLE . "
	ORDER BY cat_order";
if( !$q_categories = $db->sql_query($sql) )
{
	message_die(GENERAL_ERROR, "Could not query categories list", "", __LINE__, __FILE__, $sql);
}

if( $total_categories = $db->sql_numrows($q_categories) )
{
	$category_rows = $db->sql_fetchrowset($q_categories);

	$sql = "SELECT *
		FROM " . FORUMS_TABLE . "
		ORDER BY cat_id, forum_order";
	if(!$q_forums = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Could not query forums information", "", __LINE__, __FILE__, $sql);
	}

	if( $total_forums = $db->sql_numrows($q_forums) )
	{
		$forum_rows = $db->sql_fetchrowset($q_forums);
	}

	//
	// Okay, let's build the index
	//
	$gen_cat = array();

	for($i = 0; $i < $total_categories; $i++)
	{
		$cat_id = $category_rows[$i]['cat_id'];

		$template->assign_block_vars("catrow", array( 
			'S_ADD_FORUM_SUBMIT' => "addforum[$cat_id]", 
			'S_ADD_FORUM_NAME' => "forumname[$cat_id]", 

			'CAT_ID' => $cat_id,
			'CAT_DESC' => $category_rows[$i]['cat_title'],

			'U_CAT_EDIT' => append_sid("admin_forums.$phpEx?mode=editcat&amp;cat_id=$cat_id"),
			'U_CAT_DELETE' => append_sid("admin_forums.$phpEx?mode=deletecat&cat_id=$cat_id"),
			'U_CAT_MOVE_UP' => append_sid("admin_forums.$phpEx?mode=cat_order&move=-15&cat_id=$cat_id"),
			'U_CAT_MOVE_DOWN' => append_sid("admin_forums.$phpEx?mode=cat_order&move=15&cat_id=$cat_id"),
			'U_VIEWCAT' => append_sid($phpbb_root_path."index.$phpEx?viewcat=$cat_id"))
		);

		for($j = 0; $j < $total_forums; $j++)
		{
			$forum_id = $forum_rows[$j]['forum_id'];
			
			if ($forum_rows[$j]['cat_id'] == $cat_id)
			{

				$template->assign_block_vars("catrow.forumrow",	array(
					'FORUM_NAME' => $forum_rows[$j]['forum_name'],
					'FORUM_DESC' => $forum_rows[$j]['forum_desc'],
					'ROW_COLOR' => $row_color,
					'NUM_TOPICS' => $forum_rows[$j]['forum_topics'],
					'NUM_POSTS' => $forum_rows[$j]['forum_posts'],

					'U_VIEWFORUM' => append_sid($phpbb_root_path."viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id&amp;" . $forum_rows[$j]['forum_posts']),
					'U_FORUM_EDIT' => append_sid("admin_forums.$phpEx?mode=editforum&amp;forum_id=$forum_id"),
					'U_FORUM_DELETE' => append_sid("admin_forums.$phpEx?mode=deleteforum&amp;forum_id=$forum_id"),
					'U_FORUM_MOVE_UP' => append_sid("admin_forums.$phpEx?mode=forum_order&amp;move=-15&forum_id=$forum_id"),
					'U_FORUM_MOVE_DOWN' => append_sid("admin_forums.$phpEx?mode=forum_order&amp;move=15&forum_id=$forum_id"),
					'U_FORUM_RESYNC' => append_sid("admin_forums.$phpEx?mode=forum_sync&amp;forum_id=$forum_id"))
				);

			}// if ... forumid == catid
			
		} // for ... forums

	} // for ... categories

}// if ... total_categories

$template->pparse("body");

include('page_footer_admin.'.$phpEx);

?>
