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

if($setmodules==1)
{
        $file = basename(__FILE__);
        $module['General']['forums'] = $file;
        return;
}

//
// Include required files, get $phpEx and check permissions
//
require('pagestart.inc');


if($HTTP_POST_VARS['newcat'] != "")
{
	$mode = 'newcat';
	$catname = $HTTP_POST_VARS['catname'];
}
if(isset($HTTP_POST_VARS['newforum']))
{
	$mode = 'newcat';
	$forumname = $HTTP_POST_VARS['forumname'];
	list($cat_id) = $HTTP_POST_VARS['newforum'];
}
		


$template_header = "admin/page_header.tpl";

if(isset($mode))
{
	switch($mode)
	{
		case 'newcat':
			print "Newcat: $catname";
			break;
		case 'newforum':
			print "Newforum: cat = $cat_id name = $forumname";
			break;
	}

}


$template->set_filenames(array(
	"body" => "admin/forums_body.tpl")
);

$viewcat = (!empty($HTTP_GET_VARS['viewcat'])) ? $HTTP_GET_VARS['viewcat'] : -1;

//
// Start page proper
//
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

	$sql = "SELECT f.*
					FROM " . FORUMS_TABLE . " f
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
					"U_VIEWCAT" => append_sid("index.$phpEx?viewcat=$cat_id"),
					"U_ADDFORUM" => append_sid("$PHPSELF?mode=addforum&cat=$cat_id"),
					"ADDFORUM" => "Add Forum")
				);
				$gen_cat[$cat_id] = 1;
			}

			//
			// This should end up in the template using IF...ELSE...ENDIF
			//
			$row_color == "#DDDDDD" ?	$row_color = "#CCCCCC" : $row_color = "#DDDDDD";

			$template->assign_block_vars("catrow.forumrow",	array(
				"FORUM_NAME" => stripslashes($forum_rows[$j]['forum_name']),
				"FORUM_DESC" => stripslashes($forum_rows[$j]['forum_desc']),
				"ROW_COLOR" => $row_color,
				"U_VIEWFORUM" => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id&" . $forum_rows[$j]['forum_posts']))
			);
		} // for ... forums
		$template->assign_block_vars("catrow.forumrow", array(
			"S_NEWFORUM" => "<INPUT TYPE='text' NAME='forumname'> <INPUT TYPE='submit' NAME='newforum[$cat_id]' VALUE='New Forum'>")
		);
	} // for ... categories
	$template->assign_block_vars("catrow", array(
		"S_NEWCAT" => "<INPUT TYPE='text' NAME='catname'> <INPUT TYPE='submit' NAME='newcat' VALUE='New Category'>")
	);

}// if ... total_categories
else
{
	message_die(GENERAL_MESSAGE, "There are no Categories or Forums on this board", "", __LINE__, __FILE__, $sql);
}

$othertext = "<a href='$PHPSELF?mode=addcat'>Add category</a><br>\n";

$template->assign_vars(array(
	"S_FORMSTART" => "<FORM METHOD='post' ACTION='$PHP_SELF'>",
	"S_FORMEND" => "</FORM>"
	)
);

//
// Generate the page
//
$template->pparse("body");

//
// Page Footer
//
include('page_footer_admin.'.$phpEx);
?>
