<?php
/***************************************************************************
 *                             admin_forums.php
 *                            -------------------
 *   begin                : Thursday, Jul 12, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 *
 ***************************************************************************/

if($setmodules==1)
{
        $file = basename(__FILE__);
        $module['Forums']['Manage'] = $file;
        return;
}

function check_forum_name($forumname)
{
	global $db;
	
	$sql = "SELECT * from " . FORUMS_TABLE . "WHERE forum_name = '$forumname'";
	$result = $db->sql_query($sql);
	if( !$result )
	{  
		message_die(GENERAL_ERROR, "Couldn't get list of Categories", "", __LINE__, __FILE__, $sql);
	}
	if ($db->sql_num_rows($result) > 0)
	{
		message_die(GENERAL_ERROR, "A forum with that name already exists", "", __LINE__, __FILE__, $sql);
	}
}

//
// Include required files, get $phpEx and check permissions
//
require('pagestart.inc');

if (isset($HTTP_POST_VARS['mode']))
{
	$mode = $HTTP_POST_VARS['mode'];
}
elseif (isset($HTTP_GET_VARS['mode']))
{
	$mode = $HTTP_GET_VARS['mode'];
}
else
{
	unset($mode);
}

if(isset($mode))  // Are we supposed to do something?
{
	switch($mode)
	{
		case 'createforum':  // Create a forum in the DB
			$sql = "SELECT 
							max(forum_order) as max_order
						FROM ".FORUMS_TABLE." 
						WHERE cat_id = '".$HTTP_POST_VARS['cat_id']."'";
			if( !$result = $db->sql_query($sql) )
			{  
				message_die(GENERAL_ERROR, "Couldn't get order number from forums table", "", __LINE__, __FILE__, $sql);
			}
			$row = $db->sql_fetchrow($result);
			$max_order = $row['max_order'];
			$next_order = $max_order + 1;

			// There is no problem having duplicate forum names so we won't check for it.
			$sql = "INSERT 
						INTO ".FORUMS_TABLE."(
							forum_name,
							cat_id,
							forum_desc,
							forum_order,
							forum_status)
						VALUES (
							'".$HTTP_POST_VARS['forumname']."',
							'".$HTTP_POST_VARS['cat_id']."',
							'".$HTTP_POST_VARS['forumdesc']."',
							'".$next_order."',
							'".$HTTP_POST_VARS['forumstatus']."')";
			if( !$result = $db->sql_query($sql) )
			{  
				message_die(GENERAL_ERROR, "Couldn't insert row in forums table", "", __LINE__, __FILE__, $sql);
			}
			$show_index = TRUE;
			break;
		case 'modforum':  // Modify a forum in the DB
			$sql = "UPDATE ".FORUMS_TABLE." SET 
							forum_name = '".$HTTP_POST_VARS['forumname']."',
							cat_id = '".$HTTP_POST_VARS['cat_id']."',
							forum_desc = '".$HTTP_POST_VARS['forumdesc']."',
							forum_status = '".$HTTP_POST_VARS['forumstatus']."'
						WHERE forum_id = '".$HTTP_POST_VARS['forum_id']."'";
			if( !$result = $db->sql_query($sql) )
			{  
				message_die(GENERAL_ERROR, "Couldn't update forum information", "", __LINE__, __FILE__, $sql);
			}
			$show_index = TRUE;
			break;
							
		case 'addcat':
			$sql = "SELECT 
							max(cat_order) as max_order
						FROM ".CATEGORIES_TABLE;
			if( !$result = $db->sql_query($sql) )
			{  
				message_die(GENERAL_ERROR, "Couldn't get order number from categories table", "", __LINE__, __FILE__, $sql);
			}
			$row = $db->sql_fetchrow($result);
			$max_order = $row['max_order'];
			$next_order = $max_order + 1;
			// There is no problem having duplicate forum names so we won't check for it.
			$sql = "INSERT INTO ".CATEGORIES_TABLE."(
							cat_title,
							cat_order)
						VALUES (
							'".$HTTP_POST_VARS['catname']."',
							'".$next_order."')";
			if( !$result = $db->sql_query($sql) )
			{  
				message_die(GENERAL_ERROR, "Couldn't insert row in categories table", "", __LINE__, __FILE__, $sql);
			}
			$show_index = TRUE;
			break;
		case 'addforum':
		case 'editforum':
			if ($mode == 'editforum')
			{
				// $newmode determines if we are going to INSERT or UPDATE after posting?
				$newmode = 'modforum';
				$buttonvalue = 'Change';
				
				$forum_id = $HTTP_GET_VARS['forum_id'];
				$sql = "	SELECT 
								forum_name, 
								cat_id,
								forum_desc,
								forum_status
							FROM " . FORUMS_TABLE . "
							WHERE forum_id = $forum_id";
				if( !$result = $db->sql_query($sql) )
				{  
					message_die(GENERAL_ERROR, "Couldn't get Forum information", "", __LINE__, __FILE__, $sql);
				}
				if( $db->sql_numrows($result) != 1 )
				{
					message_die(GENERAL_ERROR, "Forum doesn't exist or multiple forums with ID $forum_id", "", __LINE__, __FILE__);
				}
				$row = $db->sql_fetchrow($result);
				$forumname = $row['forum_name'];
				$cat_id = $row['cat_id'];
				$forumdesc = $row['forum_desc'];
				$forumstatus = $row['forum_status'];
			}
			else
			{
				$newmode = 'createforum';
				$buttonvalue = 'Create';

				$forumname = stripslashes($HTTP_POST_VARS['forumname']);
				$cat_id = $HTTP_POST_VARS['cat_id'];
				$forumdesc = '';
				$forumstatus = FORUM_UNLOCKED;
				$forum_id = '';
			}
				
			
			$sql = "SELECT * FROM " . CATEGORIES_TABLE;
			if( !$result = $db->sql_query($sql) )
			{  
				message_die(GENERAL_ERROR, "Couldn't get list of Categories", "", __LINE__, __FILE__, $sql);
			}
			$cat_list = "";
			while( $row = $db->sql_fetchrow($result) )
			{
				$s = "";
				if ($row['cat_id'] == $cat_id)
				{
					$s = " SELECTED";
				}
				$catlist .= "<OPTION VALUE=\"$row[cat_id]\"$s>$row[cat_title]</OPTION>\n";
			}
			$forumstatus == FORUM_LOCKED ? $forumlocked = "selected" : $forumunlocked = "selected";
			$statuslist = "<OPTION VALUE=\"".FORUM_UNLOCKED."\" $forumunlocked>Unlocked</OPTION>\n";
			$statuslist .= "<OPTION VALUE=\"".FORUM_LOCKED."\" $forumlocked>Locked</OPTION>\n";
			
			$template->set_filenames(array(
				"body" => "admin/forum_edit_body.tpl")
			);
			$template->assign_vars(array(
				'FORUMNAME' => $forumname,
				'DESCRIPTION' => $forumdesc,
				'S_CATLIST' => $catlist,
				'S_STATUSLIST' => $statuslist,
				'S_FORUMID' => $forum_id,
				'S_NEWMODE' => $newmode,
				'BUTTONVALUE' => $buttonvalue)
			);
			$template->pparse("body");
			
			
			break;
		case 'editcat':
			$newmode = 'modcat';
			$buttonvalue = 'Change';
			
			$cat_id = $HTTP_GET_VARS['cat_id'];
			$sql = "	SELECT *
						FROM " . CATEGORIES_TABLE . "
						WHERE cat_id = $cat_id";
			if( !$result = $db->sql_query($sql) )
			{  
				message_die(GENERAL_ERROR, "Couldn't get Category information", "", __LINE__, __FILE__, $sql);
			}
			if( $db->sql_numrows($result) != 1 )
			{
				message_die(GENERAL_ERROR, "Category doesn't exist or multiple categories with ID $cat_id", "", __LINE__, __FILE__);
			}
			$row = $db->sql_fetchrow($result);
			$cat_title = $row['cat_title'];
			
			$template->set_filenames(array(
				"body" => "admin/category_edit_body.tpl")
			);
			$template->assign_vars(array(
				'CAT_TITLE' => $cat_title,
				'S_CATID' => $cat_id,
				'S_NEWMODE' => $newmode,
				'BUTTONVALUE' => $buttonvalue)
			);
			$template->pparse("body");
		
			break;
		case 'modcat':
			$sql = "UPDATE ".CATEGORIES_TABLE." SET 
							cat_title = '".$HTTP_POST_VARS['cat_title']."'
						WHERE cat_id = '".$HTTP_POST_VARS['cat_id']."'";
			if( !$result = $db->sql_query($sql) )
			{  
				message_die(GENERAL_ERROR, "Couldn't update forum information", "", __LINE__, __FILE__, $sql);
			}
			print "Modforum: ". $HTTP_POST_VARS['forumname']." sql= <pre>$sql</pre>";
			$show_index = TRUE;
			break;
		case 'cat_order':
		case 'forum_order':
			message_die(GENERAL_ERROR, "Sorry, not implemented yet");
			break;
		default:
			print "Oops! Wrong mode..";
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
	"body" => "admin/forums_body.tpl")
);

$sql = "SELECT cat_id, cat_title, cat_order
	FROM " . CATEGORIES_TABLE . "
	ORDER BY cat_order";
if(!$q_categories = $db->sql_query($sql))
{
	message_die(GENERAL_ERROR, "Could not query categories list", "", __LINE__, __FILE__, $sql);
}

if($total_categories = $db->sql_numrows($q_categories))
{
	$category_rows = $db->sql_fetchrowset($q_categories);

	$sql = "SELECT *
					FROM " . FORUMS_TABLE . "
					ORDER BY cat_id, forum_order";

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
	// Okay, let's build the index
	//
	$gen_cat = array();


	for($i = 0; $i < $total_categories; $i++)
	{
		$cat_id = $category_rows[$i]['cat_id'];

		for($j = 0; $j < $total_forums; $j++)
		{
			$forum_id = $forum_rows[$j]['forum_id'];

			if(!$gen_cat[$cat_id])
			{
				$template->assign_block_vars("catrow", array(
					"CAT_ID" => $cat_id,
					"CAT_DESC" => stripslashes($category_rows[$i]['cat_title']),
					"CAT_EDIT" => "<a href='$PHPSELF?mode=editcat&cat_id=$cat_id'>Edit/Delete</a>",
					"CAT_UP" => "<a href='$PHPSELF?mode=cat_order&pos=1&cat_id=$cat_id'>Move up</a>",
					"CAT_DOWN" => "<a href='$PHPSELF?mode=cat_order&pos=-1&forum_id=$cat_id'>Move down</a>",
					"U_VIEWCAT" => append_sid("index.$phpEx?viewcat=$cat_id"),
					"U_ADDFORUM" => append_sid("$PHPSELF?mode=addforum&cat_id=$cat_id"),
					"ADDFORUM" => "Add Forum")
				);
				$gen_cat[$cat_id] = 1;
			}
			if( $forum_rows[$j]['cat_id'] != $cat_id)
			{
				continue;
			}

			//
			// This should end up in the template using IF...ELSE...ENDIF
			//
			$row_color == "#DDDDDD" ?	$row_color = "#CCCCCC" : $row_color = "#DDDDDD";

			$template->assign_block_vars("catrow.forumrow",	array(
				"FORUM_NAME" => stripslashes($forum_rows[$j]['forum_name']),
				"FORUM_DESC" => stripslashes($forum_rows[$j]['forum_desc']),
				"ROW_COLOR" => $row_color,
				"U_VIEWFORUM" => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id&" . $forum_rows[$j]['forum_posts']),
				"FORUM_EDIT" => "<a href='$PHPSELF?mode=editforum&forum_id=$forum_id'>Edit/Delete</a>",
				"FORUM_UP" => "<a href='$PHPSELF?forum_mode=order&pos=1&forum_id=$forum_id'>Move up</a>",
				"FORUM_DOWN" => "<a href='$PHPSELF?mode=forum_order&pos=-1&forum_id=$forum_id'>Move down</a>")
			);
		} // for ... forums
		$template->assign_block_vars("catrow.forumrow", array(
			"S_ADDFORUM" => '<FORM METHOD="POST" ACTION="'.append_sid($PHP_SELF).'">
					<INPUT TYPE="text" NAME="forumname">
					<INPUT TYPE="hidden" NAME="cat_id" VALUE="'.$cat_id.'">
					<INPUT TYPE="hidden" NAME="mode" VALUE="addforum">
					<INPUT TYPE="submit" NAME="submit" VALUE="Create new Forum">',
			"S_ADDFORUM_ENDFORM" => "</FORM>")
		);
	} // for ... categories
	// Extra 'category' to create new categories at the end of the list.
	$template->assign_block_vars("catrow", array(
		"S_ADDCAT" => '<FORM METHOD="POST" ACTION="'.append_sid($PHP_SELF).'">
				<INPUT TYPE="text" NAME="catname">
				<INPUT TYPE="hidden" NAME="mode" VALUE="addcat">
				<INPUT TYPE="submit" NAME="submit" VALUE="Create new category">',
		"S_ADDCAT_ENDFORM" => "</FORM>")
	);

}// if ... total_categories
else
{
	message_die(GENERAL_MESSAGE, "There are no Categories or Forums on this board", "", __LINE__, __FILE__, $sql);
}

//
// Generate the page
//
$template->pparse("body");

//
// Page Footer
//
include('page_footer_admin.'.$phpEx);

?>