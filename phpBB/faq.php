<?php
/***************************************************************************
 *                                  faq.php
 *                            -------------------
 *   begin                : Sunday, Jul 8, 2001
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

$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_FAQ, $session_length);
init_userprefs($userdata);
//
// End session management
//

//
// Load the appropriate faq file
//
include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_faq.' . $phpEx);

//
// Pull the array data from the lang pack
//
$j = 0;
$counter = 0;
$counter_2 = 0;
$faq_block = array();
for($i = 0; $i < count($faq); $i++)
{
	if( $faq[$i][0] != "--" )
	{
		$faq_block[$j][$counter]['id'] = $counter_2;
		$faq_block[$j][$counter]['question'] = $faq[$i][0];
		$faq_block[$j][$counter]['answer'] = $faq[$i][1];

		$counter++;
		$counter_2++;
	}
	else
	{
		$counter = 0;
		$j++;
	}
}

//
// Lets build a page ...
//
$page_title = $lang['FAQ'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	"body" => "faq_body.tpl",
	"jumpbox" => "jumpbox.tpl")
);

$jumpbox = make_jumpbox($forum_id);
$template->assign_vars(array(
	"L_GO" => $lang['Go'],
	"L_JUMP_TO" => $lang['Jump_to'],
	"L_SELECT_FORUM" => $lang['Select_forum'],

	"S_JUMPBOX_LIST" => $jumpbox,
	"S_JUMPBOX_ACTION" => append_sid("viewforum.$phpEx"))
);
$template->assign_var_from_handle("JUMPBOX", "jumpbox");

$template->assign_vars(array(
	"L_FAQ" => $lang['FAQ'])
);

for($i = 0; $i < count($faq_block); $i++)
{
	if( count($faq_block[$i]) )
	{
		$template->assign_block_vars("faq_block", array(
			"BLOCK_TITLE" => $faq_block[$i]['title'])
		);
		$template->assign_block_vars("faq_block_link", array( 
			"BLOCK_TITLE" => $faq_block[$i]['title'])
		);

		for($j = 0; $j < count($faq_block[$i]); $j++)
		{
			$row_color = ( !($j % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
			$row_class = ( !($j % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

			$template->assign_block_vars("faq_block.faq_row", array(
				"ROW_COLOR" => "#" . $row_color,
				"ROW_CLASS" => $row_class,
				"FAQ_QUESTION" => $faq_block[$i][$j]['question'], 
				"FAQ_ANSWER" => $faq_block[$i][$j]['answer'], 

				"U_FAQ_ID" => $faq_block[$i][$j]['id'])
			);

			$template->assign_block_vars("faq_block_link.faq_row_link", array(
				"ROW_COLOR" => "#" . $row_color,
				"ROW_CLASS" => $row_class,
				"FAQ_LINK" => $faq_block[$i][$j]['question'], 

				"U_FAQ_LINK" => "#" . $faq_block[$i][$j]['id'])
			);
		}
	}
}

$template->pparse("body");

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>