<?php
/***************************************************************************
 *                             admin_forums.php
 *                            -------------------
 *   begin                : Sunday, Nov 11, 2002
 *   copyright            : (C) 2002 by Saerdnaer
 *   email                : saerdnaer@web.de
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

@define('IN_PHPBB', 1);

if( !empty($setmodules) )
{
	$file = basename(__FILE__);
	$module['Users']['Group_rank_order'] = "$file";
	return;
}

//
// Load default header
//
$phpbb_root_path = "./../";
require($phpbb_root_path . 'extension.inc');
require('./pagestart.' . $phpEx);
include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);

if ( isset($_POST['resync']) || isset($_GET['resync']) )
{
	$sql = "SELECT group_id
		FROM " . GROUPS_TABLE . "
		WHERE group_single_user = 0
		ORDER BY group_order, group_name";

	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't get group order data", "", __LINE__, __FILE__, $sql);
	}

	$i = 1;
	while( $row = $db->sql_fetchrow($result) )
	{
		$sql = "UPDATE " . GROUPS_TABLE . "
			SET group_order = $i
			WHERE group_id = " . $row['group_id'];
		if( !$db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't update order fields", "", __LINE__, __FILE__, $sql);
		}
		$i++;
	}
	$msg = $lang['Group_rank_resynced'];
}
if ( isset($_POST['move']) || isset($_GET['move']) || isset($_POST[POST_GROUPS_URL]) || isset($_GET[POST_GROUPS_URL]) )
{
	if( isset($_POST['move']) || isset($_GET['move']) )
	{
		$move = ( isset($_POST['move']) ) ? $_POST['move'] : $_GET['move'];
	}
	else
	{
		message_die(GENERAL_ERROR, "No move mode selected");
	}
	if( isset($_POST[POST_GROUPS_URL]) || isset($_GET[POST_GROUPS_URL]) )
	{
		$group_id = intval( isset($_POST[POST_GROUPS_URL]) ? $_POST[POST_GROUPS_URL] : $_GET[POST_GROUPS_URL] );
	}
	else
	{
		message_die(GENERAL_ERROR, "No group selected");
	}
}
if ( !empty($move) )
{
	if ( $move == 'down' )
	{
		$a = '<';
		$b = 'ASC';
		$c = '+ 1';
		$d = '- 1';
	}
	else
	{
		$a = '>';
		$b = 'DESC';
		$c = '- 1';
		$d = '+ 1';
	}

	$sql = "SELECT g2.group_id, g1.group_order
		FROM " . GROUPS_TABLE . " g1, " . GROUPS_TABLE . " g2
		WHERE g1.group_id = $group_id
			AND g1.group_order $a g2.group_order
		ORDER BY g2.group_order $b
		LIMIT 1";
	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't get group2 id", "", __LINE__, __FILE__, $sql);
	}
	if ( !list($group2_id, $group_order) = $db->sql_fetchrow($result) )
	{
		$msg = $lang['Group_rank_order_could_not_moved'];
	}
	else if ( isset($_GET['o']) && $_GET['o'] != $group_order )
	{
		$msg = $lang['Group_rank_order_alreay_moved'];
	}
	else
	{
		$sql = "UPDATE " . GROUPS_TABLE . "
			SET group_order = group_order $c
			WHERE group_id = $group_id";
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't change group order", "", __LINE__, __FILE__, $sql);
		}

		$sql = "UPDATE " . GROUPS_TABLE . "
			SET group_order = group_order $d
			WHERE group_id = $group2_id";
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't change group2 order", "", __LINE__, __FILE__, $sql);
		}
		$msg = $lang['Group_rank_order_moved'];
	}
}

//
// Start page proper
//
$template->set_filenames(array(
	'body' => 'admin/group_rank_order_body.tpl')
);

$template->assign_vars(array(
	'U_RESYNC' => append_sid("admin_group_rank.$phpEx?resync=1"),

	'L_TITLE' => $lang['Group_rank_order_title'],
	'L_EXPLAIN' => $lang['Group_rank_order_explain'],
	'L_GROUP_NAME' => $lang['Group_name'],
	'L_MOVE_UP' => $lang['Move_up'],
	'L_MOVE_DOWN' => $lang['Move_down'],
	'L_RESYNC' => $lang['Resync'])
);
if ( !empty($msg) )
{
	$template->assign_block_vars("msg", array(
		'ROW_COLOR' => '#' . $theme['td_color2'],
		'ROW_CLASS' => $theme['td_class2'],
		'MSG' => $msg)
	);
}
$sql = "SELECT group_id, group_name, group_order
	FROM " . GROUPS_TABLE . "
	WHERE group_single_user = 0
	ORDER BY group_order";
if( !$result = $db->sql_query($sql) )
{
	message_die(GENERAL_ERROR, "Could not query group list", "", __LINE__, __FILE__, $sql);
}
$group_count = $db->sql_numrows($result);
if ( $group_count == 0 )
{
	message_die(GENERAL_MESSAGE, $lang['No_groups_exist']);
}
$i = 0;
while ( $row = $db->sql_fetchrow($result) )
{
	$template->assign_block_vars("row", array(
		'GROUP_NAME' => $row['group_name'],
		'ROW_COLOR' => '#' . ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'],
		'ROW_CLASS' => ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'],

		'L_MOVE_UP' => ( $i != 0 ) ? $lang['Move_up'] : '',
		'L_MOVE_DOWN' => ( $i != $group_count - 1 ) ? $lang['Move_down'] : '',

		'U_MOVE_UP' => append_sid("admin_group_rank.$phpEx?" . POST_GROUPS_URL . "=" . $row['group_id'] . "&amp;move=up&amp;o=" . $row['group_order']),
		'U_MOVE_DOWN' => append_sid("admin_group_rank.$phpEx?" . POST_GROUPS_URL . "=" . $row['group_id'] . "&amp;move=down&amp;o=" . $row['group_order']))
	);
	$i++;
}

$template->pparse("body");

include('./page_footer_admin.'.$phpEx);

?>