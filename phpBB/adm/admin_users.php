<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : admin_users.php
// STARTED   : Sat Feb 13, 2001
// COPYRIGHT : © 2001,2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

if (!empty($setmodules))
{
	if (!$auth->acl_gets('a_user', 'a_useradd', 'a_userdel'))
	{
		return;
	}

	$module['USER']['MANAGE_USERS'] = basename(__FILE__) . $SID;

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);


// Set mode
$mode		= request_var('mode', '');
$action		= request_var('action', 'overview');
$username	= request_var('username', '');
$user_id	= request_var('u', 0);
$ip			= request_var('ip', '');
$start		= request_var('start', 0);

$delete		= request_var('delete', '');

// Set some vars
$error = array();

// Whois?
if ($action == 'whois')
{
	// Output relevant page
	adm_page_header($user->lang['WHOIS']);

	if ($ip && $domain = gethostbyaddr($ip))
	{
?>

<table class="bg" width="90%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th>IP whois for <?php echo $domain; ?></th>
	</tr>
	<tr>
		<td class="row1"><?php

		if ($ipwhois = ipwhois($ip))
		{
			$ipwhois = preg_replace('#(\s+?)([\w\-\._\+]+?@[\w\-\.]+?)(\s+?)#s', '\1<a href="mailto:\2">\2</a>\3', $ipwhois);
			echo '<br /><pre align="left">' . trim($ipwhois) . '</pre>';
		}

?></td>
	</tr>
</table>

<br clear="all" />

<?php

	}

	adm_page_footer();
}


// Begin program
if ($username || $user_id)
{
	if ($submit)
	{
		// Update entry in DB
		if ($delete && $user_type != USER_FOUNDER)
		{
			if (!$auth->acl_get('a_userdel'))
			{
				trigger_error($user->lang['NO_ADMIN']);
			}

			$db->sql_transaction();

			if ($deletetype == 'retain')
			{
				$sql = 'UPDATE ' . POSTS_TABLE . '
					SET poster_id = ' . ANONYMOUS . " 
					WHERE poster_id = $user_id";
	//			$db->sql_query($sql);

				$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET topic_poster = ' . ANONYMOUS . "
					WHERE topic_poster = $user_id";
	//			$db->sql_query($sql);
			}
			else
			{
			}

			$table_ary = array(USERS_TABLE, USER_GROUP_TABLE, TOPICS_WATCH_TABLE, FORUMS_WATCH_TABLE, ACL_USERS_TABLE);

			foreach ($table_ary as $table)
			{
				$sql = "DELETE FROM $table 
					WHERE user_id = $user_id";
//				$db->sql_query($sql);
			}

			$db->sql_transaction('commit');

			trigger_error($user->lang['USER_DELETED']);
		}
	}
	else
	{
		$session_time = 0;
		$sql_where = ($username) ? "username = '" . $db->sql_escape($username) . "'" : "user_id = $user_id";
		$sql = ($action == 'overview') ? 'SELECT u.*, s.session_time, s.session_page, s.session_ip FROM (' . USERS_TABLE . ' u LEFT JOIN ' . SESSIONS_TABLE . " s ON s.session_user_id = u.user_id) WHERE u.$sql_where ORDER BY s.session_time DESC LIMIT 1" : 'SELECT * FROM ' . USERS_TABLE . " WHERE $sql_where";
		$result = $db->sql_query($sql);

		if (!extract($db->sql_fetchrow($result)))
		{
			trigger_error($user->lang['NO_USER']);
		}
		$db->sql_freeresult($result);

		if ($session_time > $user_lastvisit)
		{
			$user_lastvisit = $session_time;
			$user_lastpage = $session_page;
		}
	}


	// Generate overall "header" for user admin
	$view_options = '';
	foreach (array('overview' => 'MAIN', 'feedback' => 'FEEDBACK', 'profile' => 'PROFILE', 'prefs' => 'PREFS', 'avatar' => 'AVATAR', 'sig' => 'SIG', 'groups' => 'GROUP', 'perm' => 'PERM') as $value => $lang)
	{
		$selected = ($action == $value) ? ' selected="selected"' : '';
		$view_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang['USER_ADMIN_' . $lang]  . '</option>';
	}

	$pagination = '';


	// Output page
	adm_page_header($user->lang['MANAGE']);

?>

<h1><?php echo $user->lang['USER_ADMIN']; ?></h1>

<p><?php echo $user->lang['USER_ADMIN_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_users.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;u=$user_id"; ?>"<?php echo ($can_upload) ? ' enctype="multipart/form-data"' : ''; ?>><table width="100%" cellspacing="2" cellpadding="0" border="0" align="center">
	<tr>
		<td align="right">Select view: <select name="action" onchange="if (this.options[this.selectedIndex].value != '') this.form.submit();"><?php echo $view_options; ?></select></td>
	</tr>
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<th colspan="2"><?php echo $user->lang['USER_ADMIN_' . $action]; ?></th>
			</tr>
<?php

	switch ($action)
	{
		case 'overview':

			$options = '<option class="sep" value="">' . 'Select option' . '</option>';
			foreach (array('banuser' => 'BAN_USER', 'banemail' => 'BAN_EMAIL', 'banip' => 'BAN_IP', 'force' => 'FORCE', 'active' => (($user_type == USER_INACTIVE) ? 'ACTIVATE' : 'DEACTIVATE'), 'moveposts' => 'MOVE_POSTS') as $value => $lang)
			{
				$options .= '<option value="' . $value . '">' . $user->lang['USER_ADMIN_' . $lang]  . '</option>';
			}

			$user_founder_yes = ($user_type == USER_FOUNDER) ? ' checked="checked"' : '';
			$user_founder_no = ($user_type != USER_FOUNDER) ? ' checked="checked"' : (($user->data['user_type'] != USER_FOUNDER) ? ' disabled="disabled"' : '');

?>	
			<tr>
				<td class="row1" width="40%"><b>Username: </b></td>
				<td class="row2"><input class="post" type="text" name="username" value="<?php echo $username; ?>" maxlength="60" /></td>
			</tr>
			<tr>
				<td class="row1"><b>Founder: </b><br /><span class="gensmall">Founders can never be banned, deleted or altered by non-founder members</span></td>
				<td class="row2"><input type="radio" name="user_founder" value="0"<?php echo $user_founder_yes; ?> /><?php echo $user->lang['YES']; ?>&nbsp;<input type="radio" name="user_founder" value="1"<?php echo $user_founder_no; ?> /><?php echo $user->lang['NO']; ?></td>
			</tr>
			<tr>
				<td class="row1"><b>Email: </b></td>
				<td class="row2"><input class="post" type="text" name="user_email" value="<?php echo $user_email; ?>" maxlength="60" /></td>
			</tr>
			<tr>
				<td class="row1"><b>Confirm Email: </b><br /><span class="gensmall">Only required if changing the email address</span></td>
				<td class="row2"><input class="post" type="text" name="user_email_confirm" value="<?php echo $user_email_confirm; ?>" maxlength="60" /></td>
			</tr>
			<tr>
				<td class="row1"><b>New password: </b></td>
				<td class="row2"><input class="post" type="password" name="user_password" value="<?php echo ($submit) ? $user_password : ''; ?>" maxlength="60" /></td>
			</tr>
			<tr>
				<td class="row1"><b>Confirm password: </b><br /><span class="gensmall">Only required if changing the email address</span></td>
				<td class="row2"><input class="post" type="password" name="user_password_confirm" value="<?php echo ($submit) ? $user_password_confirm : ''; ?>" maxlength="60" /></td>
			</tr>
<?php

			if ($user_type != USER_FOUNDER)
			{

?>
			<tr>
				<td class="row1"><b>Quick tools: </b></td>
				<td class="row2"><select name="options"><?php echo $options; ?></select></td>
			</tr>
			<tr>
				<td class="row1"><b>Delete user: </b><br /><span class="gensmall">Please note that deleting a user is final, it cannot be recovered</span></td>
				<td class="row2"><input type="checkbox" name="delete" value="1" /> <select name="deletetype"><option value="retain">Retain posts</option><option value="posts">Delete posts</option></select></td>
			</tr>
<?php

			}

?>
			<tr>
				<th colspan="2">Background</th>
			</tr>
			<tr>
				<td class="row1" colspan="2"><table width="60%" cellspacing="1" cellpadding="4" border="0" align="center">
					<tr>
						<td width="40%"><b>Registered: </b></td>
						<td><?php echo $user->format_date($user_regdate); ?></td>
					</tr>
					<tr>
						<td><b>Registration IP: </b></td>
						<td><?php
			
					echo ($user_ip) ? "<a href=\"admin_users.$phpEx$SID&amp;action=whois&amp;ip=$user_ip\" onclick=\"window.open('admin_users.$phpEx$SID&amp;action=whois&amp;ip=$user_ip', '', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=600');return false;\">$user_ip</a>" : 'Unknown';

?></td>
					</tr>
					<tr>
						<td width="40%"><b>Last active: </b></td>
						<td><?php echo $user->format_date($user_lastvisit); ?></td>
					</tr>
					<tr>
						<td><b>Karma level: </b></td>
						<td><?php

					echo ($config['enable_karma']) ? '<img src="../images/karma' . $user_karma . '.gif" alt="' . $user->lang['KARMA_LEVEL'] . ': ' . $user->lang['KARMA'][$user_karma] . '" title="' . $user->lang['KARMA_LEVEL'] . ': ' . $user->lang['KARMA'][$user_karma] . '" /> [ ' . $user->lang['KARMA'][$user_karma] . ' ]' : '';

?></td>
					</tr>
					<tr>
						<td><b>Warnings: </b></td>
						<td><?php

					echo ($user_warnings) ? $user_warnings : 'None';

?></td>
					</tr>
				</table></td>
			</tr>
<?php

			break;


		case 'feedback':

			if ($submit)
			{

			}

?>



<?php

			$sql = 'SELECT COUNT(user_id) AS total_reports
				FROM ' . USERS_NOTES_TABLE . "
				WHERE user_id = $user_id";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			
			$total_reports = $row['total_reports'];

			if ($total_reports)
			{
				$pagination = generate_pagination("admin_users.$phpEx$SID&amp;action=$action&amp;u=$user_id&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir", $total_reports, $config['posts_per_page'], $start);

				$sql = 'SELECT u.username, n.* 
					FROM ' . USERS_NOTES_TABLE . ' n, ' . USERS_TABLE . " u  
					WHERE n.user_id = $user_id 
						AND u.user_id = n.reporter_id 
					ORDER BY n.report_log DESC, n.report_date DESC";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
				<tr>
					<td class="<?php echo $row_class; ?>"><span class="gensmall">Report by: <b><?php echo $row['username']; ?></b> on <?php echo $user->format_date($row['report_date']); ?></span><hr /><?php echo $row['report_text']; ?></td>
				</tr>
<?php

				}
				$db->sql_freeresult($result);
			}
			else
			{

?>
				<tr>
					<td class="row1" align="center">No reports exist for this user</td>
				</tr>
<?php

			}
			break;


		case 'profile':

			if (!isset($bday_day))
			{
				list($bday_day, $bday_month, $bday_year) = explode('-', $user_birthday);
			}

			$s_birthday_day_options = '<option value="0"' . ((!$bday_day) ? ' selected="selected"' : '') . '>--</option>';
			for ($i = 1; $i < 32; $i++)
			{
				$selected = ($i == $bday_day) ? ' selected="selected"' : '';
				$s_birthday_day_options .= "<option value=\"$i\"$selected>$i</option>";
			}

			$s_birthday_month_options = '<option value="0"' . ((!$bday_month) ? ' selected="selected"' : '') . '>--</option>';
			for ($i = 1; $i < 13; $i++)
			{
				$selected = ($i == $bday_month) ? ' selected="selected"' : '';
				$s_birthday_month_options .= "<option value=\"$i\"$selected>$i</option>";
			}
			$s_birthday_year_options = '';

			$now = getdate();
			$s_birthday_year_options = '<option value="0"' . ((!$bday_year) ? ' selected="selected"' : '') . '>--</option>';
			for ($i = $now['year'] - 100; $i < $now['year']; $i++)
			{
				$selected = ($i == $bday_year) ? ' selected="selected"' : '';
				$s_birthday_year_options .= "<option value=\"$i\"$selected>$i</option>";
			}
			unset($now);

?>
			<tr> 
				<td class="row1" width="40%"><b><?php echo $user->lang['UCP_ICQ']; ?>: </b></td>
				<td class="row2"><input class="post" type="text" name="icq" size="30" maxlength="15" value="<?php echo $user_icq; ?>" /></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['UCP_AIM']; ?>: </b></td>
				<td class="row2"><input class="post" type="text" name="aim" size="30" maxlength="255" value="<?php echo $user_aim; ?>" /></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['UCP_MSNM']; ?>: </b></td>
				<td class="row2"><input class="post" type="text" name="msn" size="30" maxlength="255" value="<?php echo $user_msnm; ?>" /></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['UCP_YIM']; ?>: </b></td>
				<td class="row2"><input class="post" type="text" name="yim" size="30" maxlength="255" value="<?php echo $user_yim; ?>" /></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['UCP_JABBER']; ?>: </b></td>
				<td class="row2"><input class="post" type="text" name="jabber" size="30" maxlength="255" value="<?php echo $user_jabber; ?>" /></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['WEBSITE']; ?>: </b></td>
				<td class="row2"><input class="post" type="text" name="website" size="30" maxlength="255" value="<?php echo $user_website; ?>" /></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['LOCATION']; ?>: </b></td>
				<td class="row2"><input class="post" type="text" name="location" size="30" maxlength="100" value="<?php echo $user_location; ?>" /></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['OCCUPATION']; ?>: </b></td>
				<td class="row2"><textarea class="post" name="occ" rows="3" cols="30"><?php echo $user_occ; ?></textarea></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['INTERESTS']; ?>: </b></td>
				<td class="row2"><textarea class="post" name="interests" rows="3" cols="30"><?php echo $user_interests; ?></textarea></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['BIRTHDAY']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BIRTHDAY_EXPLAIN']; ?></span></td>
				<td class="row2"><span class="genmed"><?php echo $user->lang['DAY']; ?>:</span> <select name="bday_day"><?php echo $s_birthday_day_options; ?></select> <span class="genmed"><?php echo $user->lang['MONTH']; ?>:</span> <select name="bday_month"><?php echo $s_birthday_month_options; ?></select> <span class="genmed"><?php echo $user->lang['YEAR']; ?>:</span> <select name="bday_year"><?php echo $s_birthday_year_options; ?></select></td>
			</tr>
<?php

			break;


		case 'prefs':

?>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_IMAGES']; ?>:</b></td>
				<td class="row2"><input type="radio" name="images" value="1"{VIEW_IMAGES_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="images" value="0"{VIEW_IMAGES_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_FLASH']; ?>:</b></td>
				<td class="row2"><input type="radio" name="flash" value="1"{VIEW_FLASH_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="flash" value="0"{VIEW_FLASH_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_SMILIES']; ?>:</b></td>
				<td class="row2"><input type="radio" name="smilies" value="1"{VIEW_SMILIES_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="smilies" value="0"{VIEW_SMILIES_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_SIGS']; ?>:</b></td>
				<td class="row2"><input type="radio" name="sigs" value="1"{VIEW_SIGS_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="sigs" value="0"{VIEW_SIGS_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_AVATARS']; ?>:</b></td>
				<td class="row2"><input type="radio" name="avatars" value="1"{VIEW_AVATARS_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="avatars" value="0"{VIEW_AVATARS_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<!-- IF S_CHANGE_CENSORS -->
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['DISABLE_CENSORS']; ?>:</b></td>
				<td class="row2"><input type="radio" name="wordcensor" value="1"{DISABLE_CENSORS_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="wordcensor" value="0"{DISABLE_CENSORS_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<!-- ENDIF -->
			<tr>
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['MINIMUM_KARMA']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['MINIMUM_KARMA_EXPLAIN']; ?></span></td>
				<td class="row2"><select name="minkarma">{S_MIN_KARMA_OPTIONS}</select></td>

			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_TOPICS_DAYS']; ?>:</b></td>
				<td class="row2">{S_SELECT_SORT_DAYS}</td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_TOPICS_KEY']; ?>:</b></td>
				<td class="row2">{S_SELECT_SORT_KEY}</td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_TOPICS_DIR']; ?>:</b></td>
				<td class="row2">{S_SELECT_SORT_DIR}</td>
			</tr>
			<tr>
				<th colspan="2">Posting preferences</th>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['DEFAULT_BBCODE']; ?>:</b></td>
				<td class="row2"><input type="radio" name="bbcode" value="1"{DEFAULT_BBCODE_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="bbcode" value="0"{DEFAULT_BBCODE_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['DEFAULT_HTML']; ?>:</b></td>
				<td class="row2"><input type="radio" name="html" value="1"{DEFAULT_HTML_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="html" value="0"{DEFAULT_HTML_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['DEFAULT_SMILE']; ?>:</b></td>
				<td class="row2"><input type="radio" name="smilies" value="1"{DEFAULT_SMILIES_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="smilies" value="0"{DEFAULT_SMILIES_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['DEFAULT_ADD_SIG']; ?>:</b></td>
				<td class="row2"><input type="radio" name="sig" value="1"{DEFAULT_SIG_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="sig" value="0"{DEFAULT_SIG_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['DEFAULT_NOTIFY']; ?>:</b></td>
				<td class="row2"><input type="radio" name="notify" value="1"{DEFAULT_NOTIFY_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="notify" value="0"{DEFAULT_NOTIFY_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr>
				<th colspan="2"></th>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['SHOW_EMAIL']; ?>:</b></td>
				<td class="row2"><input type="radio" name="viewemail" value="1"{VIEW_EMAIL_YES} /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="viewemail" value="0"{VIEW_EMAIL_NO} /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['ADMIN_EMAIL']; ?>:</b></td>
				<td class="row2"><input type="radio" name="massemail" value="1"{ADMIN_EMAIL_YES} /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="massemail" value="0"{ADMIN_EMAIL_NO} /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['ALLOW_PM']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['ALLOW_PM_EXPLAIN']; ?></span></td>
				<td class="row2"><input type="radio" name="allowpm" value="1"{ALLOW_PM_YES} /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="allowpm" value="0"{ALLOW_PM_NO} /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<!-- IF S_CAN_HIDE_ONLINE -->
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['HIDE_ONLINE']; ?>:</b></td>
				<td class="row2"><input type="radio" name="hideonline" value="1"{HIDE_ONLINE_YES} /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="hideonline" value="0"{HIDE_ONLINE_NO} /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<!-- ENDIF -->
			<!-- IF S_SELECT_NOTIFY -->
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['NOTIFY_METHOD']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['NOTIFY_METHOD_EXPLAIN']; ?></span></td>
				<td class="row2"><input type="radio" name="notifymethod" value="0"{NOTIFY_EMAIL} /><span class="genmed"><?php echo $user->lang['NOTIFY_METHOD_EMAIL']; ?></span>&nbsp;&nbsp;<input type="radio" name="notifymethod" value="1"{NOTIFY_IM} /><span class="genmed"><?php echo $user->lang['NOTIFY_METHOD_IM']; ?></span>&nbsp;&nbsp;<input type="radio" name="notifymethod" value="2"{NOTIFY_BOTH} /><span class="genmed"><?php echo $user->lang['NOTIFY_METHOD_BOTH']; ?></span></td>
			</tr>
			<!-- ENDIF -->
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['NOTIFY_ON_PM']; ?>:</b></td>
				<td class="row2"><input type="radio" name="notifypm" value="1"{NOTIFY_PM_YES} /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="notifypm" value="0"{NOTIFY_PM_NO} /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['POPUP_ON_PM']; ?>:</b></td>
				<td class="row2"><input type="radio" name="popuppm" value="1"{POPUP_PM_YES} /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="popuppm" value="0"{POPUP_PM_NO} /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['BOARD_LANGUAGE']; ?>:</b></td>
				<td class="row2"><select name="lang">{S_LANG_OPTIONS}</select></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['BOARD_STYLE']; ?>:</b></td>
				<td class="row2"><select name="style">{S_STYLE_OPTIONS}</select></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['BOARD_TIMEZONE']; ?>:</b></td>
				<td class="row2"><select name="tz">{S_TZ_OPTIONS}</select></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['BOARD_DST']; ?>:</b></td>
				<td class="row2"><input type="radio" name="dst" value="1"{DST_YES} /> <span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="dst" value="0"{DST_NO} /> <span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['BOARD_DATE_FORMAT']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['BOARD_DATE_FORMAT_EXPLAIN']; ?></span></td>
				<td class="row2"><input type="text" name="dateformat" value="{DATE_FORMAT}" maxlength="14" class="post" /></td>
			</tr>
<?php

			break;


		case 'avatar':
			$can_upload = (file_exists($phpbb_root_path . $config['avatar_path']) && is_writeable($phpbb_root_path . $config['avatar_path']) && (@ini_get('file_uploads') || strtolower(@ini_get('file_uploads')) == 'on')) ? true : false;

			if ($user_avatar)
			{
				switch ($user_avatar_type)
				{
					case AVATAR_UPLOAD:
						$avatar_img = $phpbb_root_path . $config['avatar_path'] . '/';
						break;
					case AVATAR_GALLERY:
						$avatar_img = $phpbb_root_path . $config['avatar_gallery_path'] . '/';
						break;
				}
				$avatar_img .= $user_avatar;

				$avatar_img = '<img src="' . $avatar_img . '" width="' . $user_avatar_width . '" height="' . $user_avatar_height . '" border="0" alt="" />';
			}
			else
			{
				$avatar_img = '<img src="images/no_avatar.gif" alt="" />';
			}

?>
			<tr> 
				<td class="row2" width="35%"><b><?php echo $user->lang['CURRENT_IMAGE']; ?>: </b><br /><span class="gensmall"><?php echo sprintf($user->lang['AVATAR_EXPLAIN'], $config['avatar_max_width'], $config['avatar_max_height'], round($config['avatar_filesize'] / 1024)); ?></span></td>
				<td class="row1" align="center"><br /><?php echo $avatar_img; ?><br /><br /><input type="checkbox" name="delete" />&nbsp;<span class="gensmall"><?php echo $user->lang['DELETE_AVATAR']; ?></span></td>
			</tr>
<?php

			// Can we upload?
			if ($can_upload)
			{

?>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['UPLOAD_AVATAR_FILE']; ?>: </b></td>
		<td class="row1"><input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $config['avatar_max_filesize']; ?>" /><input class="post" type="file" name="uploadfile" /></td>
	</tr>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['UPLOAD_AVATAR_URL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['UPLOAD_AVATAR_URL_EXPLAIN']; ?></span></td>
		<td class="row1"><input class="post" type="text" name="uploadurl" size="40" value="<?php echo $avatar_url; ?>" /></td>
	</tr>
<?php

			}

?>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['LINK_REMOTE_AVATAR']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['LINK_REMOTE_AVATAR_EXPLAIN']; ?></span></td>
		<td class="row1"><input class="post" type="text" name="remotelink" size="40" value="<?php echo $avatar_url; ?>" /></td>
	</tr>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['LINK_REMOTE_SIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['LINK_REMOTE_SIZE_EXPLAIN']; ?></span></td>
		<td class="row1"><input class="post" type="text" name="width" size="3" value="<?php echo $user_avatar_width; ?>" /> <span class="gen">px X </span> <input class="post" type="text" name="height" size="3" value="<?php echo $user_avatar_height; ?>" /> <span class="gen">px</span></td>
	</tr>
<?php

			// Do we have a gallery?
			if ($config['null'] && !$display_gallery)
			{

?>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['AVATAR_GALLERY']; ?>: </b></td>
		<td class="row1"><input class="btnlite" type="submit" name="displaygallery" value="<?php echo $user->lang['DISPLAY_GALLERY']; ?>" /></td>
	</tr>
<?php
			}

			// Do we want to display it?
			if ($config['null'] && $display_gallery)
			{

?>
	<tr> 
		<th colspan="2"><?php echo $user->lang['AVATAR_GALLERY']; ?></th>
	</tr>
	<tr> 
		<td class="cat" colspan="2" align="center" valign="middle"><span class="genmed"><?php echo $user->lang['AVATAR_CATEGORY']; ?>: </span><select name="avatarcat">{S_CAT_OPTIONS}</select>&nbsp; <span class="genmed"><?php echo $user->lang['AVATAR_PAGE']; ?>: </span><select name="avatarpage">{S_PAGE_OPTIONS}</select>&nbsp;<input class="btnlite" type="submit" value="<?php echo $user->lang['GO']; ?>" name="avatargallery" /></td>
	</tr>
	<tr> 
		<td class="row1" colspan="2" align="center"><table cellspacing="1" cellpadding="4" border="0">
		
			<!-- BEGIN avatar_row -->
			<tr> 
				<!-- BEGIN avatar_column -->
				<td class="row1" align="center"><img src="{avatar_row.avatar_column.AVATAR_IMAGE}" alt="{avatar_row.avatar_column.AVATAR_NAME}" title="{avatar_row.avatar_column.AVATAR_NAME}" /></td>
				<!-- END avatar_column -->
			</tr>
			<tr>
				<!-- BEGIN avatar_option_column -->
				<td class="row2" align="center"><input type="radio" name="avatarselect" value="{avatar_row.avatar_option_column.S_OPTIONS_AVATAR}" /></td>
				<!-- END avatar_option_column -->
			</tr>
			<!-- END avatar_row -->

		</table></td>
	</tr>
<?php

			}

			break;


		case 'sig':
			include($phpbb_root_path . 'includes/functions_posting.'.$phpEx);

			decode_text($user_sig, $user_sig_bbcode_uid);


?>
	<tr> 
		<td class="row2"><table cellspacing="0" cellpadding="2" border="0">
			<tr align="center" valign="middle">
				<td><input class="btnlite" type="button" accesskey="b" name="addbbcode0" value=" B " style="font-weight:bold; width: 30px" onclick="bbstyle(0)" onmouseover="helpline('b')" /></td>
				<td><input class="btnlite" type="button" accesskey="i" name="addbbcode2" value=" i " style="font-style:italic; width: 30px" onclick="bbstyle(2)" onmouseover="helpline('i')" /></td>
				<td><input class="btnlite" type="button" accesskey="u" name="addbbcode4" value=" u " style="text-decoration: underline; width: 30px" onclick="bbstyle(4)" onmouseover="helpline('u')" /></td>
				<td><input class="btnlite" type="button" accesskey="q" name="addbbcode6" value="Quote" style="width: 50px" onclick="bbstyle(6)" onmouseover="helpline('q')" /></td>
				<td><input class="btnlite" type="button" accesskey="c" name="addbbcode8" value="Code" style="width: 40px" onclick="bbstyle(8)" onmouseover="helpline('c')" /></td>
				<td><input class="btnlite" type="button" accesskey="l" name="addbbcode10" value="List" style="width: 40px" onclick="bbstyle(10)" onmouseover="helpline('l')" /></td>
				<td><input class="btnlite" type="button" accesskey="o" name="addbbcode12" value="List=" style="width: 40px" onclick="bbstyle(12)" onmouseover="helpline('o')" /></td>
				<td><input class="btnlite" type="button" accesskey="p" name="addbbcode14" value="Img" style="width: 40px"  onclick="bbstyle(14)" onmouseover="helpline('p')" /></td>
				<td><input class="btnlite" type="button" accesskey="w" name="addbbcode18" value="URL" style="text-decoration: underline; width: 40px" onclick="bbstyle(18)" onmouseover="helpline('w')" /></td>
			</tr>
			<tr>
				<td colspan="9"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td><span class="genmed"> &nbsp;{L_FONT_SIZE}:</span> <select name="addbbcode20" onchange="bbfontstyle('[size=' + this.form.addbbcode20.options[this.form.addbbcode20.selectedIndex].value + ']', '[/size]');this.form.addbbcode20.selectedIndex = 2;" onmouseover="helpline('f')">
							<option value="7">{L_FONT_TINY}</option>
							<option value="9">{L_FONT_SMALL}</option>
							<option value="12" selected="selected">{L_FONT_NORMAL}</option>
							<option value="18">{L_FONT_LARGE}</option>
							<option  value="24">{L_FONT_HUGE}</option>
						</select></td>
						<td class="gensmall" nowrap="nowrap" align="right"><a href="javascript:bbstyle(-1)" onmouseover="helpline('a')">{L_CLOSE_TAGS}</a></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td colspan="9"><input class="helpline" type="text" name="helpbox" size="45" maxlength="100" value="{L_STYLES_TIP}" /></td>
			</tr>
			<tr>
				<td colspan="9"><textarea class="post" name="signature" rows="6" cols="60" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);"><?php echo $user_sig; ?></textarea></td>
			</tr>
		</table></td>
	</tr>
<?php

			break;


		case 'groups':
			break;


		case 'perm':
			break;

	}

?>
			<tr>
				<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
			</tr>
		</table></td>
	</tr>
<?php

	if ($pagination)
	{

?>
	<tr>
		<td align="right"><?php echo $pagination; ?></td>
	</tr>
<?php

	}

?>
</table></form>

<?php

	adm_page_footer();

}

// Do we have permission?
if (!$auth->acl_get('a_user'))
{
	trigger_error($user->lang['No_admin']);
}

adm_page_header($user->lang['MANAGE']);

?>

<h1><?php echo $user->lang['USER_ADMIN']; ?></h1>

<p><?php echo $user->lang['USER_ADMIN_EXPLAIN']; ?></p>

<form method="post" name="post" action="<?php echo "admin_users.$phpEx$SID"; ?>"><table class="bg" width="75%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"align="center"><?php echo $user->lang['SELECT_USER']; ?></th>
	</tr>
	<tr> 
		<td class="row1" width="40%"><b>Lookup existing user: </b><br /><span class="gensmall">[ <a href="<?php echo "../memberlist.$phpEx$SID&amp;mode=searchuser&amp;field=username"; ?>" onclick="window.open('<?php echo "../memberlist.$phpEx$SID&amp;mode=searchuser&amp;field=username"?>', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=740');return false;"><?php echo $user->lang['FIND_USERNAME']; ?></a> ]</span></td>
		<td class="row2"><input type="text" class="post" name="username" maxlength="50" size="20" /></td>
	</tr>
	<!-- tr>
		<td class="row1" width="40%"><b>Create new user: </b></td>
		<td class="row2"><input type="text" class="post" name="newuser" maxlength="50" size="20" /></td>
	</tr -->
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" name="submituser" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" /></td>
	</tr>
</table></form>

<?php

adm_page_footer();

?>