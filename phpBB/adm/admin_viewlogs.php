<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : admin_viewlogs.php 
// STARTED   : Sat Feb 13, 2001
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

if (!empty($setmodules))
{
	if (!$auth->acl_get('a_'))
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['LOG']['ADMIN_LOGS'] = $filename . "$SID&amp;mode=admin";
	$module['LOG']['MOD_LOGS'] = $filename . "$SID&amp;mode=mod";
	$module['LOG']['CRITICAL_LOGS'] = $filename . "$SID&amp;mode=critical";

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);

// Do we have styles admin permissions?
if (!$auth->acl_get('a_'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Set some variables
$mode		= request_var('mode', 'admin');
$forum_id	= request_var('f', 0);
$start		= request_var('start', 0);
$deletemark = (isset($_POST['delmarked'])) ? true : false;
$deleteall	= (isset($_POST['delall'])) ? true : false;
$marked		= request_var('mark', 0);

// Sort keys
$sort_days	= request_var('st', 0);
$sort_key	= request_var('sk', 't');
$sort_dir	= request_var('sd', 'd');

// Define some vars depending on which logs we're looking at
$log_type = ($mode == 'admin') ? LOG_ADMIN : (($mode == 'mod') ? LOG_MOD : LOG_CRITICAL);

if ($log_type == LOG_MOD)
{
	$user->add_lang('mcp');
}

// Delete entries if requested and able
if (($deletemark || $deleteall) && $auth->acl_get('a_clearlogs'))
{
	$where_sql = '';
	if ($deletemark && $marked)
	{
		$sql_in = array();
		foreach ($marked as $mark)
		{
			$sql_in[] =  $mark;
		}
		$where_sql = ' AND log_id IN (' . implode(', ', $sql_in) . ')';
		unset($sql_in);
	}

	$sql = 'DELETE FROM ' . LOG_TABLE . "
		WHERE log_type = $log_type 
			$where_sql";
	$db->sql_query($sql);

	add_log('admin', 'LOG_' . strtoupper($mode) . '_CLEAR');
}

// Sorting
$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
$sort_by_text = array('u' => $user->lang['SORT_USERNAME'], 't' => $user->lang['SORT_DATE'], 'i' => $user->lang['SORT_IP'], 'o' => $user->lang['SORT_ACTION']);
$sort_by_sql = array('u' => 'l.user_id', 't' => 'l.log_time', 'i' => 'l.log_ip', 'o' => 'l.log_operation');

$s_limit_days = $s_sort_key = $s_sort_dir = '';
gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir);

// Define where and sort sql for use in displaying logs
$sql_where = ($sort_days) ? (time() - ($sort_days * 86400)) : 0;
$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

$l_title = $user->lang[strtoupper($mode) . '_LOGS'];
$l_title_explain = $user->lang[strtoupper($mode) . '_LOGS_EXPLAIN'];

// Output page
adm_page_header($l_title);

?>

<h1><?php echo $l_title; ?></h1>

<p><?php echo $l_title_explain; ?></p>

<form name="list" method="post" action="<?php echo "admin_viewlogs.$phpEx$SID&amp;mode=$mode"; ?>">
<?php

// Define forum list if we're looking @ mod logs
if ($mode == 'mod')
{

	$forum_box = '<option value="0">' . $user->lang['ALL_FORUMS'] . '</option>' . make_forum_select($forum_id);

?>
<table width="100%" cellpadding="1" cellspacing="1" border="0">
	<tr>
		<td align="right"><?php echo $user->lang['SELECT_FORUM']; ?>: <select name="f" onchange="if(this.options[this.selectedIndex].value != -1){ this.form.submit() }"><?php echo $forum_box; ?></select> <input class="btnlite" type="submit" value="<?php echo $user->lang['GO']; ?>" /></td>
	</tr>
</table>
<?php

}

?>

<table class="bg" width="100%" cellpadding="4" cellspacing="1" border="0">
	<tr>
		<td class="cat" colspan="5" height="28" align="center"><?php echo $user->lang['DISPLAY_LOG']; ?>: &nbsp;<?php echo $s_limit_days; ?>&nbsp;<?php echo $user->lang['SORT_BY']; ?>: <?php echo $s_sort_key; ?> <?php echo $s_sort_dir; ?>&nbsp;<input class="btnlite" type="submit" value="<?php echo $user->lang['GO']; ?>" name="sort" /></td>
	</tr>
	<tr>
		<th width="15%" height="25" nowrap="nowrap"><?php echo $user->lang['USERNAME']; ?></th>
		<th width="15%" nowrap="nowrap"><?php echo $user->lang['IP']; ?></th>
		<th width="20%" nowrap="nowrap"><?php echo $user->lang['TIME']; ?></th>
		<th width="45%" nowrap="nowrap"><?php echo $user->lang['ACTION']; ?></th>
		<th nowrap="nowrap"><?php echo $user->lang['MARK']; ?></th>
	</tr>
<?php

//
// Grab log data
//
$log_data = array();
$log_count = 0;
view_log($mode, $log_data, $log_count, $config['topics_per_page'], $start, $forum_id, 0, 0, $sql_where, $sql_sort);

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
		<td class="<?php echo $row_class; ?>"><?php 
			echo $log_data[$i]['action']; 

			$data = array();
				
			foreach (array('viewtopic', 'viewlogs', 'viewforum') as $check)
			{
				if ($log_data[$i][$check])
				{
					$data[] = '<a href="' . $log_data[$i][$check] . '">' . $user->lang['LOGVIEW_' . strtoupper($check)] . '</a>';
				}
			}

			if (sizeof($data))
			{
				echo '<br />&#187; <span class="gensmall">[ ' . implode(' | ', $data) . ' ]</span>';
			}
?>
		</td>
		<td class="<?php echo $row_class; ?>" align="center" nowrap="nowrap"><input type="checkbox" name="mark[]" value="<?php echo $log_data[$i]['id']; ?>" /></td>
	</tr>
<?php

	}

	if ($auth->acl_get('a_clearlogs'))
	{

?>
	<tr>
		<td class="cat" colspan="5" height="28" align="right"><input class="btnlite" type="submit" name="delmarked" value="<?php echo $user->lang['DELETE_MARKED']; ?>" />&nbsp; <input class="btnlite" type="submit" name="delall" value="<?php echo $user->lang['DELETE_ALL']; ?>" />&nbsp;</td>
	</tr>
<?php

	}
}
else
{
?>
	<tr>
		<td class="row1" colspan="5" align="center" nowrap="nowrap"><?php echo $user->lang['NO_ENTRIES']; ?></td>
	</tr>
<?php

}

?>
</table>

<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr>
		<td align="left" valign="top">&nbsp;<span class="nav"><?php echo on_page($log_count, $config['topics_per_page'], $start); ?></span></td>
		<td align="right" valign="top" nowrap="nowrap"><span class="nav"><?php

	if ($auth->acl_get('a_clearlogs'))
	{


?><b><a href="javascript:marklist('list', true);"><?php echo $user->lang['MARK_ALL']; ?></a> :: <a href="javascript:marklist('list', false);"><?php echo $user->lang['UNMARK_ALL']; ?></a></b>&nbsp;<br /><br /><?php

	}

	echo generate_pagination("admin_viewlogs.$phpEx$SID&amp;mode=$mode&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir", $log_count, $config['topics_per_page'], $start); 
	
?></span></td>
	</tr>
</table></form>

<script language="Javascript" type="text/javascript">
<!--
function marklist(match, status)
{
	len = eval('document.' + match + '.length');
	for (i = 0; i < len; i++)
	{
		eval('document.' + match + '.elements[i].checked = ' + status);
	}
}
//-->
</script>

<?php

adm_page_footer();

?>