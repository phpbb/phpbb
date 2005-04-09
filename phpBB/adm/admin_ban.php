<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/
if (!empty($setmodules))
{
	if (!$auth->acl_get('a_ban'))
	{
		return;
	}

	$filename = basename(__FILE__);
	$module['USER']['BAN_USERS'] = $filename . "$SID&amp;mode=user";
	$module['USER']['BAN_EMAILS'] = $filename . "$SID&amp;mode=email";
	$module['USER']['BAN_IPS'] = $filename . "$SID&amp;mode=ip";

	return;
}

define('IN_PHPBB', 1);
// Load default header
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.'.$phpEx);

// Do we have ban permissions?
if (!$auth->acl_get('a_ban'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Mode setting
$mode		= request_var('mode', '');
$bansubmit	= (isset($_POST['bansubmit'])) ? true : false;
$unbansubmit= (isset($_POST['unbansubmit'])) ? true : false;

// Set some vars
$current_time = time();

// Start program
if ($bansubmit)
{
	// Grab the list of entries
	$ban			= request_var('ban', '');
	$ban_len		= request_var('banlength', 0);
	$ban_len_other	= request_var('banlengthother', '');
	$ban_exclude	= request_var('banexclude', 0);
	$ban_reason		= request_var('banreason', '');

	user_ban($mode, $ban, $ban_len, $ban_len_other, $ban_exclude, $ban_reason);

	trigger_error($user->lang['BAN_UPDATE_SUCESSFUL']);
}
else if ($unbansubmit)
{
	$ban = request_var('unban', '');

	user_unban($mode, $ban);

	trigger_error($user->lang['BAN_UPDATE_SUCESSFUL']);
}

//
// Output relevant entry page
//


//
// Ban length options
//
$ban_end_text = array(0 => $user->lang['PERMANENT'], 30 => $user->lang['30_MINS'], 60 => $user->lang['1_HOUR'], 360 => $user->lang['6_HOURS'], 1440 => $user->lang['1_DAY'], 10080 => $user->lang['7_DAYS'], 20160 => $user->lang['2_WEEKS'], 40320 => $user->lang['1_MONTH'], -1 => $user->lang['OTHER'] . ' -&gt; ');

$ban_end_options = '';
foreach ($ban_end_text as $length => $text)
{
	$ban_end_options .= '<option value="' . $length . '">' . $text . '</option>';
}

// Title
switch ($mode)
{
	case 'user':
		$l_title = $user->lang['BAN_USERS'];
		break;
	case 'email':
		$l_title = $user->lang['BAN_EMAILS'];
		break;
	case 'ip':
		$l_title = $user->lang['BAN_IPS'];
		break;
}

// Output page
adm_page_header($l_title);

?>

<p><?php echo $user->lang['BAN_EXPLAIN']; ?></p>

<?php

switch ($mode)
{
	case 'user':

		$field = 'username';
		$l_ban_title = $user->lang['BAN_USERS'];
		$l_ban_explain = $user->lang['BAN_USERNAME_EXPLAIN'];
		$l_ban_exclude_explain = $user->lang['BAN_USER_EXCLUDE_EXPLAIN'];
		$l_unban_title = $user->lang['UNBAN_USERNAME'];
		$l_unban_explain = $user->lang['UNBAN_USERNAME_EXPLAIN'];
		$l_ban_cell = $user->lang['USERNAME'] . ': <br /><span class="gensmall">[ <a href="' . "../memberlist.$phpEx$SID&amp;mode=searchuser&amp;form=banning&amp;field=ban\" onclick=\"window.open('../memberlist.$phpEx$SID&amp;mode=searchuser&amp;form=banning&amp;field=ban', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=740');return false;\">" . $user->lang['FIND_USERNAME'] .'</a> ]</span>';
		$l_no_ban_cell = $user->lang['NO_BANNED_USERS'];

		$sql = 'SELECT b.*, u.user_id, u.username
			FROM ' . BANLIST_TABLE . ' b, ' . USERS_TABLE . ' u
			WHERE (b.ban_end >= ' . time() . '
					OR b.ban_end = 0)
				AND u.user_id = b.ban_userid
				AND b.ban_userid <> 0
				AND u.user_id <> ' . ANONYMOUS . '
			ORDER BY u.user_id ASC';
		break;

	case 'ip':

		$field = 'ban_ip';
		$l_ban_title = $user->lang['BAN_IPS'];
		$l_ban_explain = $user->lang['BAN_IP_EXPLAIN'];
		$l_ban_exclude_explain = $user->lang['BAN_IP_EXCLUDE_EXPLAIN'];
		$l_unban_title = $user->lang['UNBAN_IP'];
		$l_unban_explain = $user->lang['UNBAN_IP_EXPLAIN'];
		$l_ban_cell = $user->lang['IP_HOSTNAME'] . ':';
		$l_no_ban_cell = $user->lang['NO_BANNED_IP'];

		$sql = 'SELECT *
			FROM ' . BANLIST_TABLE . '
			WHERE (ban_end >= ' . time() . "
					OR ban_end = 0)
				AND ban_ip <> ''";
		break;

	case 'email':

		$field = 'ban_email';
		$l_ban_title = $user->lang['BAN_EMAILS'];
		$l_ban_explain = $user->lang['BAN_EMAIL_EXPLAIN'];
		$l_ban_exclude_explain = $user->lang['BAN_EMAIL_EXCLUDE_EXPLAIN'];
		$l_unban_title = $user->lang['UNBAN_EMAIL'];
		$l_unban_explain = $user->lang['UNBAN_EMAIL_EXPLAIN'];
		$l_ban_cell = $user->lang['EMAIL_ADDRESS'] . ':';
		$l_no_ban_cell = $user->lang['NO_BANNED_EMAIL'];

		$sql = 'SELECT *
			FROM ' . BANLIST_TABLE . '
			WHERE (ban_end >= ' . time() . "
					OR ban_end = 0)
				AND ban_email <> ''";
		break;
}
$result = $db->sql_query($sql);

$banned_options = '';
$ban_length = $ban_reasons = array();
if ($row = $db->sql_fetchrow($result))
{
	do
	{

		$banned_options .=  '<option' . (($row['ban_exclude']) ? ' class="sep"' : '') . ' value="' . $row['ban_id'] . '">' . $row[$field] . '</option>';

		$time_length = (!empty($row['ban_end'])) ? ($row['ban_end'] - $row['ban_start']) / 60 : 0;
		$ban_length[$row['ban_id']] = (!empty($ban_end_text[$time_length])) ? $ban_end_text[$time_length] : $user->lang['OTHER'] . ' -> ' . gmdate('Y-m-d', $row['ban_end']);

		$ban_reasons[$row['ban_id']] = addslashes($row['ban_reason']);
	}
	while ($row = $db->sql_fetchrow($result));
}
$db->sql_freeresult($result);

?>

<h1><?php echo $l_ban_title; ?></h1>

<p><?php echo $l_ban_explain; ?></p>

<script language="Javascript" type="text/javascript">
<!--

var ban_length = new Array();
<?php

	if (sizeof($ban_length))
	{
		foreach ($ban_length as $ban_id => $length)
		{
			echo "ban_length['$ban_id'] = \"$length\";\n";
		}
	}

?>

var ban_reason = new Array();
<?php

	if (sizeof($ban_reasons))
	{
		foreach ($ban_reasons as $ban_id => $reason)
		{
			echo "ban_reason['$ban_id'] = \"$reason\";\n";
		}
	}
?>

function display_details(option)
{
	document.forms[0].unbanreason.value = ban_reason[option];
	document.forms[0].unbanlength.value = ban_length[option];
}

//-->
</script>

<form name="banning" method="post" action="<?php echo "admin_ban.$phpEx$SID&amp;mode=$mode"; ?>"><table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $l_ban_title; ?></th>
	</tr>
	<tr>
		<td class="row1" width="45%"><?php echo $l_ban_cell; ?></td>
		<td class="row2"><textarea cols="40" rows="3" name="ban"></textarea></td>
	</tr>
	<tr>
		<td class="row1" width="45%"><?php echo $user->lang['BAN_LENGTH']; ?>:</td>
		<td class="row2"><select name="banlength"><?php echo $ban_end_options; ?></select>&nbsp; <input class="post" type="text" name="banlengthother" maxlength="10" size="10" /></td>
	</tr>
	<tr>
		<td class="row1" width="45%"><?php echo $user->lang['BAN_EXCLUDE']; ?>: <br /><span class="gensmall"><?php echo $l_ban_exclude_explain;;?></span></td>
		<td class="row2"><input type="radio" name="banexclude" value="1" /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="banexclude" value="0" checked="checked" /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1" width="45%"><?php echo $user->lang['BAN_REASON']; ?>:</td>
		<td class="row2"><input class="post" type="text" name="banreason" maxlength="255" size="40" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"> <input type="submit" name="bansubmit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp; <input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" />&nbsp;</td>
	</tr>
</table>

<h1><?php echo $l_unban_title; ?></h1>

<p><?php echo $l_unban_explain; ?></p>

<table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $l_unban_title; ?></th>
	</tr>
<?php

	if ($banned_options)
	{

?>
	<tr>
		<td class="row1" width="45%"><?php echo $l_ban_cell; ?>: <br /></td>
		<td class="row2"> <select name="unban[]" multiple="multiple" size="5" onchange="display_details(this.options[this.selectedIndex].value)"><?php echo $banned_options; ?></select></td>
	</tr>
	<tr>
		<td class="row1" width="45%"><?php echo $user->lang['BAN_REASON']; ?>:</td>
		<td class="row2"><input class="row1" style="border:0px" type="text" name="unbanreason" size="40" /></td>
	</tr>
	<tr>
		<td class="row1" width="45%"><?php echo $user->lang['BAN_LENGTH']; ?>:</td>
		<td class="row2"><input class="row1" style="border:0px" type="text" name="unbanlength" size="40" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" name="unbansubmit" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" />&nbsp; <input type="reset" value="<?php echo $user->lang['RESET']; ?>" class="btnlite" /></td>
	</tr>
<?php

	}
	else
	{

?>
	<tr>
		<td class="row2" colspan="2" align="center"><?php echo $l_no_ban_cell;  ?></td>
	</tr>
<?php

	}

?>
</table></form>

<?php

adm_page_footer();

?>