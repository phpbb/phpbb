<?php
/***************************************************************************
 *                            admin_viewlogs.php
 *                            -------------------
 *   begin                : Friday, May 11, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
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

if ( !empty($setmodules) )
{
	if ( !$acl->get_acl_admin('general') )
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['Log']['Admin_logs'] = $filename . "$SID&amp;mode=admin";
	$module['Log']['Mod_logs'] = $filename . "$SID&amp;mode=mod";

	return;
}

define('IN_PHPBB', 1);
//
// Include files
//
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

//
// Do we have styles admin permissions?
//
if ( !$acl->get_acl_admin('general') )
{
	message_die(MESSAGE, $lang['No_admin']);
}

//
// Set some variables
//
$start = ( isset($HTTP_GET_VARS['start']) ) ? intval($HTTP_GET_VARS['start']) : 0;

if ( isset($HTTP_POST_VARS['mode']) ||  isset($HTTP_GET_VARS['mode']) )
{
	$mode = ( isset($HTTP_POST_VARS['mode']) ) ? $HTTP_POST_VARS['mode'] : $HTTP_GET_VARS['mode'];
}
else
{
	$mode = 'admin';
}

//
// Define some vars depending on which logs we're looking at
//
$log_table_sql = ( $mode == 'admin' ) ? LOG_ADMIN_TABLE : LOG_MOD_TABLE;
$l_title = ( $mode == 'admin' ) ? $lang['Admin_logs'] : $lang['Mod_logs'];
$l_title_explain = ( $mode == 'admin' ) ? $lang['Admin_logs_explain'] : $lang['Mod_logs_explain'];

//
// Delete entries if requested and able
//
if ( ( isset($HTTP_POST_VARS['delmarked']) || isset($HTTP_POST_VARS['delall']) ) && $acl->get_acl_admin('clearlogs'))
{
	$where_sql = '';
	if ( isset($HTTP_POST_VARS['delmarked']) && isset($HTTP_POST_VARS['mark']) )
	{
		foreach ( $HTTP_POST_VARS['mark'] as $marked )
		{
			$where_sql .= ( ( $where_sql != '' ) ? ', ' : '' ) . intval($marked);
		}
		$where_sql = "WHERE log_id IN ($where_sql)";
	}

	$sql = "DELETE FROM $table_sql 
		$where_sql";
	$db->sql_query($sql);

	add_admin_log('log_' . $mode . '_clear');
}

//
// Sorting ... this could become a function
//
if ( isset($HTTP_POST_VARS['sort']) || $start )
{
	if ( !empty($HTTP_POST_VARS['sort_days']) || !empty($HTTP_GET_VARS['sort_days']) )
	{
		$sort_days = ( !empty($HTTP_POST_VARS['sort_days']) ) ? intval($HTTP_POST_VARS['sort_days']) : intval($HTTP_GET_VARS['sort_days']);
		$where_sql = time() - ( $sort_days * 86400 );
	}
	else
	{
		$where_sql = 0;
	}

	$sort_key = ( isset($HTTP_POST_VARS['sort_key']) ) ? $HTTP_POST_VARS['sort_key'] : $HTTP_GET_VARS['sort_key'];
	$sort_dir = ( isset($HTTP_POST_VARS['sort_dir']) ) ? $HTTP_POST_VARS['sort_dir'] : $HTTP_GET_VARS['sort_dir'];
}
else
{
	$where_sql = 0;

	$sort_days = 0;
	$sort_key = 't';
	$sort_dir = 'd';
}

$previous_days = array(0 => $lang['All_Entries'], 1 => $lang['1_Day'], 7 => $lang['7_Days'], 14 => $lang['2_Weeks'], 30 => $lang['1_Month'], 90 => $lang['3_Months'], 180 => $lang['6_Months'], 364 => $lang['1_Year']);
$sort_by_text = array('u' => $lang['Sort_Username'], 't' => $lang['Sort_date'], 'i' => $lang['Sort_ip'], 'o' => $lang['Sort_action']);
$sort_by = array('u' => 'l.user_id', 't' => 'l.log_time', 'i' => 'l.log_ip', 'o' => 'l.log_operation');

$sort_day_options = '';
foreach ( $previous_days as $day => $text )
{
	$selected = ( $sort_days == $day ) ? ' selected="selected"' : '';
	$sort_day_options .= '<option value="' . $day . '"' . $selected . '>' . $text . '</option>';
}

$sort_key_options = '';
foreach ( $sort_by_text as $key => $text )
{
	$selected = ( $sort_key == $key ) ? ' selected="selected"' : '';
	$sort_key_options .= '<option value="' . $key . '"' . $selected . '>' . $text . '</option>';
}

$sort_order_options = ( $sort_dir == 'a' ) ? '<option value="a" selected="selected">' . $lang['Sort_Ascending'] . '</option><option value="d">' . $lang['Sort_Descending'] . '</option>' : '<option value="a">' . $lang['Sort_Ascending'] . '</option><option value="d" selected="selected">' . $lang['Sort_Descending'] . '</option>';

$sort_sql = $sort_by[$sort_key] . ' ' . ( ( $sort_dir == 'd' ) ? 'DESC' : 'ASC' );

//
// Define forum list if we're looking @ mod logs
//
$forum_options = '';
if ( $mode == 'mod' )
{
	$sql = "SELECT forum_id, forum_name
		FROM " . FORUMS_TABLE . " 
		ORDER BY cat_id, forum_order";
	$result = $db->sql_query($sql);

	if ( $row = $db->sql_fetchrow($result) )
	{
		$forum_id = ( isset($HTTP_POST_VARS['f']) ) ? intval($HTTP_POST_VARS['f']) : $row['forum_id'];

		do
		{
			$selected = ( $row['forum_id'] == $forum_id ) ? ' selected="selected"' : '';
			$forum_options .= '<option value="' . $row['forum_id'] . '"' . $selected . '>' . $row['forum_name'] . '</option>';
		}
		while ( $row = $db->sql_fetchrow($result) );
	}
	else
	{
		$forum_id = 0;
		$forum_options = '<option>' . $lang['No_forums'] . '</option>';
	}
}

//
// Output page
//
page_header($l_title);

?>

<h1><?php echo $l_title; ?></h1>

<p><?php echo $l_title_explain; ?></p>

<form method="post" name="log" action="<?php echo "admin_viewlogs.$phpEx$SID&amp;mode=$mode"; ?>">
<?php

if ( $mode == 'mod' )
{

?>
<table width="100%" cellpadding="1" cellspacing="1" border="0">
	<tr>
		<td align="right"><?php echo $lang['Select_forum']; ?>: <select name="f"><?php echo $forum_options; ?></select> <input class="liteoption" type="submit" value="<?php echo $lang['Go']; ?>" /></td>
	</tr>
</table>
<?php

}

?>

<table class="bg" width="100%" cellpadding="4" cellspacing="1" border="0">
	<tr>
		<td class="cat" colspan="5" height="28" align="center"><span class="gensmall"><?php echo $lang['Display_log']; ?>: &nbsp;<select name="sort_days"><?php echo $sort_day_options; ?></select>&nbsp;<?php echo $lang['Sort_by']; ?> <select name="sort_key"><?php echo $sort_key_options; ?></select> <select name="sort_dir"><?php echo $sort_order_options; ?></select>&nbsp;<input class="liteoption" type="submit" value="<?php echo $lang['Go']; ?>" name="sort" /></span></td>
	</tr>
	<tr> 
		<th width="15%" height="25" nowrap="nowrap"><?php echo $lang['Username']; ?></th>
		<th width="15%" nowrap="nowrap"><?php echo $lang['IP']; ?></th>
		<th width="20%" nowrap="nowrap"><?php echo $lang['Time']; ?></th>
		<th width="45%" nowrap="nowrap"><?php echo $lang['Action']; ?></th>
		<th nowrap="nowrap"><?php echo $lang['Mark']; ?></th>
	</tr>
<?php

//
// Grab log data
//
$log_data = array();
$log_count = 0;
view_log($mode, $log_data, $log_count, $board_config['topics_per_page'], $start, $forum_id, $where_sql, $sort_sql);

if ( $log_count )
{
	for($i = 0; $i < sizeof($log_data); $i++)
	{
		$row_class = ( $row_class == 'row1' ) ? 'row2' : 'row1';
	
?>
	<tr>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap"><?php echo $log_data[$i]['username']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap"><?php echo $log_data[$i]['ip']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap"><?php echo create_date($board_config['default_dateformat'], $log_data[$i]['time'], $board_config['board_timezone']); ?></td>
		<td class="<?php echo $row_class; ?>"><?php echo $log_data[$i]['action']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap"><input type="checkbox" name="mark[]" value="<?php echo $log_data[$i]['id']; ?>" /></td>
	</tr>
<?php

	}

	if ( $acl->get_acl_admin('clearlogs') )
	{

?>
	<tr>
		<td class="cat" colspan="5" height="28" align="right"><input class="liteoption" type="submit" name="delmarked" value="<?php echo $lang['Delete_marked']; ?>" />&nbsp; <input class="liteoption" type="submit" name="delall" value="<?php echo $lang['Delete_all']; ?>" />&nbsp;</td>
	</tr>
<?php

	}
}
else
{
?>
	<tr>
		<td class="row1" colspan="5" align="center" nowrap="nowrap"><?php echo $lang['No_entries']; ?></td>
	</tr>
<?php

}

?>
</table>

<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr> 
		<td align="left" valign="top">&nbsp;<span class="nav"><?php echo on_page($log_count, $board_config['topics_per_page'], $start); ?></span></td>
		<td align="right" valign="top" nowrap="nowrap"><?php 

	if ( $acl->get_acl_admin('clearlogs') )
	{

		
?><b><span class="gensmall"><a href="javascript:marklist(true);" class="gensmall"><?php echo $lang['Mark_all']; ?></a> :: <a href="javascript:marklist(false);" class="gensmall"><?php echo $lang['Unmark_all']; ?></a></span></b>&nbsp;<br /><br /><?php

	}

	$pagination = generate_pagination("admin_viewlogs.$phpEx$SID&amp;mode=$mode&amp;sort_days=$sort_days&amp;sort_key=$sort_key&amp;sort_dir=$sort_dir", $log_count, $board_config['topics_per_page'], $start);

		?><span class="nav"><?php echo $pagination; ?></span></td>
	</tr>
</table></form>

<script language="Javascript" type="text/javascript">
<!--
//
// Should really check the browser to stop this whining ...
//
function marklist(status)
{
	for (i = 0; i < document.log.length; i++)
	{
		document.log.elements[i].checked = status;
	}
}
//-->
</script>

<?php

page_footer();

?>