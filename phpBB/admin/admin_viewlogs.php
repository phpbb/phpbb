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

if (!empty($setmodules))
{
	if (!$auth->acl_get('a_general'))
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['LOG']['ADMIN_LOGS'] = $filename . "$SID&amp;mode=admin";
	$module['LOG']['MOD_LOGS'] = $filename . "$SID&amp;mode=mod";

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);
require_once($phpbb_root_path . 'includes/functions_admin.'.$phpEx);

// Do we have styles admin permissions?
if (!$auth->acl_get('a_general'))
{
	trigger_error($user->lang['No_admin']);
}

// Set some variables
$forum_id = (isset($_REQUEST['f'])) ? intval($_REQUEST['f']) : 0;
$start = (isset($_GET['start'])) ? intval($_GET['start']) : 0;
$mode = (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : 'admin';

// Define some vars depending on which logs we're looking at
$log_table_sql = ($mode == 'admin') ? LOG_ADMIN_TABLE : LOG_MOD_TABLE;
$l_title = ($mode == 'admin') ? $user->lang['ADMIN_LOGS'] : $user->lang['MOD_LOGS'];
$l_title_explain = ($mode == 'admin') ? $user->lang['Admin_logs_explain'] : $user->lang['Mod_logs_explain'];

// Delete entries if requested and able
if ((isset($_POST['delmarked']) || isset($_POST['delall'])) && $auth->acl_get('a_clearlogs'))
{
	$where_sql = '';
	if (isset($_POST['delmarked']) && isset($_POST['mark']))
	{
		foreach ($_POST['mark'] as $marked)
		{
			$where_sql .= (($where_sql != '') ? ', ' : '') . intval($marked);
		}
		$where_sql = "WHERE log_id IN ($where_sql)";
	}

	$sql = "DELETE FROM $table_sql
		$where_sql";
	$db->sql_query($sql);

	add_admin_log('log_' . $mode . '_clear');
}

// Sorting ... this could become a function
if (isset($_POST['sort']) || $start)
{
	if (!empty($_REQUEST['sort_days']))
	{
		$sort_days = intval($_REQUEST['sort_days']);
		$where_sql = time() - ($sort_days * 86400);
	}
	else
	{
		$where_sql = 0;
	}

	$sort_key = (isset($_REQUEST['sort_key'])) ? $_REQUEST['sort_key'] : '';
	$sort_dir = (isset($_REQUEST['sort_dir'])) ? $_REQUEST['sort_dir'] : '';
}
else
{
	$where_sql = 0;

	$sort_days = 0;
	$sort_key = 't';
	$sort_dir = 'd';
}

$previous_days = array(0 => $user->lang['All_Entries'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
$sort_by_text = array('u' => $user->lang['Sort_Username'], 't' => $user->lang['Sort_date'], 'i' => $user->lang['Sort_ip'], 'o' => $user->lang['Sort_action']);
$sort_by = array('u' => 'l.user_id', 't' => 'l.log_time', 'i' => 'l.log_ip', 'o' => 'l.log_operation');

$sort_day_options = '';
foreach ($previous_days as $day => $text)
{
	$selected = ($sort_days == $day) ? ' selected="selected"' : '';
	$sort_day_options .= '<option value="' . $day . '"' . $selected . '>' . $text . '</option>';
}

$sort_key_options = '';
foreach ($sort_by_text as $key => $text)
{
	$selected = ($sort_key == $key) ? ' selected="selected"' : '';
	$sort_key_options .= '<option value="' . $key . '"' . $selected . '>' . $text . '</option>';
}

$sort_order_options = ($sort_dir == 'a') ? '<option value="a" selected="selected">' . $user->lang['Sort_Ascending'] . '</option><option value="d">' . $user->lang['Sort_Descending'] . '</option>' : '<option value="a">' . $user->lang['Sort_Ascending'] . '</option><option value="d" selected="selected">' . $user->lang['Sort_Descending'] . '</option>';

$sort_sql = $sort_by[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

// Output page
page_header($l_title);

?>

<h1><?php echo $l_title; ?></h1>

<p><?php echo $l_title_explain; ?></p>

<form method="post" action="<?php echo "admin_viewlogs.$phpEx$SID&amp;mode=$mode"; ?>">
<?php

// Define forum list if we're looking @ mod logs
if ($mode == 'mod')
{
//include($phpbb_root_path . '/includes/functions_admin.'.$phpEx);
	$forum_box = make_forum_select($forum_id);

?>
<table width="100%" cellpadding="1" cellspacing="1" border="0">
	<tr>
		<td align="right"><?php echo $user->lang['SELECT_FORUM']; ?>: <select name="f" onChange="if(this.options[this.selectedIndex].value != -1){ this.form.submit() }"><?php echo $forum_box; ?></select> <input class="liteoption" type="submit" value="<?php echo $user->lang['GO']; ?>" /></td>
	</tr>
</table>
<?php

}

?>

<table class="bg" width="100%" cellpadding="4" cellspacing="1" border="0">
	<tr>
		<td class="cat" colspan="5" height="28" align="center"><span class="gensmall"><?php echo $user->lang['Display_log']; ?>: &nbsp;<select name="sort_days"><?php echo $sort_day_options; ?></select>&nbsp;<?php echo $user->lang['Sort_by']; ?> <select name="sort_key"><?php echo $sort_key_options; ?></select> <select name="sort_dir"><?php echo $sort_order_options; ?></select>&nbsp;<input class="liteoption" type="submit" value="<?php echo $user->lang['GO']; ?>" name="sort" /></span></td>
	</tr>
	<tr>
		<th width="15%" height="25" nowrap="nowrap"><?php echo $user->lang['Username']; ?></th>
		<th width="15%" nowrap="nowrap"><?php echo $user->lang['IP']; ?></th>
		<th width="20%" nowrap="nowrap"><?php echo $user->lang['Time']; ?></th>
		<th width="45%" nowrap="nowrap"><?php echo $user->lang['Action']; ?></th>
		<th nowrap="nowrap"><?php echo $user->lang['Mark']; ?></th>
	</tr>
<?php

//
// Grab log data
//
$log_data = array();
$log_count = 0;
view_log($mode, $log_data, $log_count, $config['topics_per_page'], $start, $forum_id, $where_sql, $sort_sql);

if ($log_count)
{
	for($i = 0; $i < sizeof($log_data); $i++)
	{
		$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" nowrap="nowrap"><?php echo $log_data[$i]['username']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap"><?php echo $log_data[$i]['ip']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap"><?php echo $user->format_date($log_data[$i]['time']); ?></td>
		<td class="<?php echo $row_class; ?>"><?php echo $log_data[$i]['action']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap"><input type="checkbox" name="mark[]" value="<?php echo $log_data[$i]['id']; ?>" /></td>
	</tr>
<?php

	}

	if ($auth->acl_get('a_clearlogs'))
	{

?>
	<tr>
		<td class="cat" colspan="5" height="28" align="right"><input class="liteoption" type="submit" name="delmarked" value="<?php echo $user->lang['Delete_marked']; ?>" />&nbsp; <input class="liteoption" type="submit" name="delall" value="<?php echo $user->lang['Delete_all']; ?>" />&nbsp;</td>
	</tr>
<?php

	}
}
else
{
?>
	<tr>
		<td class="row1" colspan="5" align="center" nowrap="nowrap"><?php echo $user->lang['No_entries']; ?></td>
	</tr>
<?php

}

?>
</table>

<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr>
		<td align="left" valign="top">&nbsp;<span class="nav"><?php echo on_page($log_count, $config['topics_per_page'], $start); ?></span></td>
		<td align="right" valign="top" nowrap="nowrap"><?php

	if ($auth->acl_get('a_clearlogs'))
	{


?><b><span class="gensmall"><a href="javascript:marklist(true);" class="gensmall"><?php echo $user->lang['Mark_all']; ?></a> :: <a href="javascript:marklist(false);" class="gensmall"><?php echo $user->lang['Unmark_all']; ?></a></span></b>&nbsp;<br /><br /><?php

	}

	$pagination = generate_pagination("admin_viewlogs.$phpEx$SID&amp;mode=$mode&amp;sort_days=$sort_days&amp;sort_key=$sort_key&amp;sort_dir=$sort_dir", $log_count, $config['topics_per_page'], $start);

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