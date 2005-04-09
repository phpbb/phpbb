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
	$file = basename(__FILE__);
	$module['GENERAL']['MASS_EMAIL'] = ($auth->acl_get('a_email')) ? "$file$SID" : '';
	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);

// Check permissions
if (!$auth->acl_get('a_email'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// Set some vars
$message = $subject = $group_id = '';

// Do the job ...
if (isset($_POST['submit']))
{
	// Error checking needs to go here ... if no subject and/or no message then skip 
	// over the send and return to the form
	$group_id	= request_var('g', 0);
	$usernames	= request_var('usernames', '');
	$subject	= preg_replace('#&(\#[0-9]+;)#', '&\1', strtr(request_var('subject', ''), array_flip(get_html_translation_table(HTML_ENTITIES))));
	$message	= preg_replace('#&(\#[0-9]+;)#', '&\1', strtr(request_var('message', ''), array_flip(get_html_translation_table(HTML_ENTITIES))));
	$use_queue	= (isset($_POST['send_immediatly'])) ? false : true;
	$priority	= request_var('mail_priority_flag', MAIL_NORMAL_PRIORITY);

	// NOTE: Only temporary, i do not think this is a good idea for the final code, but i have to test this more than once. ;)
	$log_session= (isset($_POST['log_session'])) ? true : false;

	$error = array();
	if (!$subject)
	{
		$error[] = $user->lang['NO_EMAIL_SUBJECT'];
	}

	if (!$message)
	{
		$error[] = $user->lang['NO_EMAIL_MESSAGE'];
	}

	if (!sizeof($error))	
	{
		if ($usernames)
		{
			$usernames = implode(', ', preg_replace('#^[\s]*?(.*?)[\s]*?$#e', "\"'\" . \$db->sql_escape('\\1') . \"'\"", explode("\n", $usernames)));

			$sql = 'SELECT username, user_email, user_jabber, user_notify_type, user_lang 
				FROM ' . USERS_TABLE . " 
				WHERE username IN ($usernames)
					AND user_allow_massemail = 1
				ORDER BY user_lang, user_notify_type, SUBSTRING(user_email FROM INSTR(user_email,'@'))";
		}
		else
		{
			$sql = ($group_id) ? 'SELECT u.user_email, u.username, u.user_lang, u.user_jabber, u.user_notify_type FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug WHERE ug.group_id = $group_id AND ug.user_pending <> 1 AND u.user_id = ug.user_id AND u.user_allow_massemail = 1" : 'SELECT u.username, u.user_email, u.user_jabber, u.user_notify_type, u.user_lang FROM ' . USERS_TABLE . ' u WHERE u.user_allow_massemail = 1';

			// TODO: different for other db servers?
			$sql .= " ORDER BY u.user_lang, u.user_notify_type, SUBSTRING(u.user_email FROM INSTR(u.user_email,'@'))";
		}
		$result = $db->sql_query($sql);

		if (!($row = $db->sql_fetchrow($result)))
		{
			trigger_error($user->lang['NO_USER']);
		}
		$db->sql_freeresult($result);
	
		$i = $j = 0;
		// Send with BCC, no more than 50 recipients for one mail (to not exceed the limit)
		$max_chunk_size = 50;
		$email_list = array();
		$old_lang = $row['user_lang'];
		$old_notify_type = $row['user_notify_type'];

		do
		{
			if (($row['user_notify'] == NOTIFY_EMAIL && $row['user_email']) ||
				($row['user_notify'] == NOTIFY_IM && $row['user_jabber']) ||
				($row['user_notify'] == NOTIFY_BOTH && $row['user_email'] && $row['user_jabber']))
			{
				if ($i == $max_chunk_size || $row['user_lang'] != $old_lang || $row['user_notify_type'] != $old_notify_type)
				{
					$i = 0;
					$j++;
					$old_lang = $row['user_lang'];
					$old_notify_type = $row['user_notify_type'];
				}

				$email_list[$j][$i]['lang']		= $row['user_lang'];
				$email_list[$j][$i]['method']	= $row['user_notify_type'];
				$email_list[$j][$i]['email']	= $row['user_email'];
				$email_list[$j][$i]['name']		= $row['username'];
				$email_list[$j][$i]['jabber']	= $row['user_jabber'];
				$i++;
			}
		} 
		while ($row = $db->sql_fetchrow($result));
		$db->sql_freeresult($result);

		// Send the messages
		include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);
		$messenger = new messenger($use_queue);

		$errored = false;

		for ($i = 0; $i < sizeof($email_list); $i++)
		{
			$used_lang = $email_list[$i][0]['lang'];
			$used_method = $email_list[$i][0]['method'];

			for ($j = 0; $j < sizeof($email_list[$i]); $j++)
			{
				$email_row = $email_list[$i][$j];

				$messenger->{((sizeof($email_list[$i]) == 1) ? 'to' : 'bcc')}($email_row['email'], $email_row['name']);
				$messenger->im($email_row['jabber'], $email_row['name']);
			}

			$messenger->template('admin_send_email', $used_lang);

			$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
			$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
			$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
			$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);
			
			$messenger->subject($subject);
			$messenger->replyto($config['board_email']);
			$messenger->set_mail_priority($priority);

			$messenger->assign_vars(array(
				'SITENAME'		=> $config['sitename'],
				'CONTACT_EMAIL' => $config['board_contact'],
				'MESSAGE'		=> $message)
			);
	
			if (!($messenger->send($used_method, $log_session)))
			{
				$errored = true;
			}
		}
		unset($email_list);

		$messenger->save_queue();

		if ($group_id)
		{
			$sql = 'SELECT group_name 
				FROM ' . GROUPS_TABLE . " 
				WHERE group_id = $group_id";
			$result = $db->sql_query($sql);

			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			extract($row);
		}
		else
		{
			// Not great but the logging routine doesn't cope well with localising
			// on the fly
			$group_name = $user->lang['ALL_USERS'];
		}

		add_log('admin', 'LOG_MASS_EMAIL', $group_name);
		$message = (!$errored) ? $user->lang['EMAIL_SENT'] : sprintf($user->lang['EMAIL_SEND_ERROR'], '<a href="admin_viewlogs.' . $phpEx . $SID . '&amp;mode=critical" class="gen">', '</a>');
		trigger_error($message);
	}
}

// Initial selection
$sql = 'SELECT group_id, group_type, group_name
	FROM ' . GROUPS_TABLE . ' 
	ORDER BY group_type DESC, group_name ASC';
$result = $db->sql_query($sql);

$select_list = '<option value="0"' . ((!$group_id) ? ' selected="selected"' : '') . '>' . $user->lang['ALL_USERS'] . '</option>';
if ($row = $db->sql_fetchrow($result))
{
	do
	{
		$selected = ($group_id == $row['group_id']) ? ' selected="selected"' : '';
		$select_list .= '<option value = "' . $row['group_id'] . '"' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="blue"' : '') . $selected . '>' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
	}
	while ($row = $db->sql_fetchrow($result));
}
$db->sql_freeresult($result);

$s_priority_options = '<option value="' . MAIL_LOW_PRIORITY . '">' . $user->lang['MAIL_LOW_PRIORITY'] . '</option>';
$s_priority_options .= '<option value="' . MAIL_NORMAL_PRIORITY . '" selected="selected">' . $user->lang['MAIL_NORMAL_PRIORITY'] . '</option>';
$s_priority_options .= '<option value="' . MAIL_HIGH_PRIORITY . '">' . $user->lang['MAIL_HIGH_PRIORITY'] . '</option>';

adm_page_header($user->lang['MASS_EMAIL']);

?>

<h1><?php echo $user->lang['MASS_EMAIL']; ?></h1>

<p><?php echo $user->lang['MASS_EMAIL_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_email.$phpEx$SID"; ?>" name="email"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['COMPOSE']; ?></th>
	</tr>
<?php

	if (sizeof($error))
	{

?>
	<tr>
		<td class="row3" colspan="2" align="center"><span class="error"><?php echo implode('<br />', $error); ?></span></td>
	</tr>
<?php

	}

?>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['SEND_TO_GROUP']; ?>: </b></td>
		<td class="row2"><select name="g"><?php echo $select_list; ?></select></td>
	</tr>
	<tr>
		<td class="row1" valign="top"><b><?php echo $user->lang['SEND_TO_USERS']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['SEND_TO_USERS_EXPLAIN']; ?><br />[ <a href="" onclick="window.open('<?php echo "../memberlist.$phpEx$SID"; ?>&amp;mode=searchuser&amp;form=email&amp;field=usernames', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=740');return false;"><?php echo $user->lang['FIND_USERNAME']; ?></a> ]</span></td>
		<td class="row2" align="left"><textarea name="usernames" rows="5" cols="40"><?php echo $usernames; ?></textarea></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SUBJECT']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="subject" size="45" maxlength="100" tabindex="2" value="<?php echo $subject; ?>" /></td>
	</tr>
	<tr>
		<td class="row1" valign="top"><span class="gen"><b><?php echo $user->lang['MASS_MESSAGE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['MASS_MESSAGE_EXPLAIN']; ?></span></td>
		<td class="row2"><textarea class="post" name="message" rows="10" cols="60" tabindex="3"><?php echo $message; ?></textarea></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['MAIL_PRIORITY']; ?>: </b></td>
		<td class="row2"><select name="mail_priority_flag"><?php echo $s_priority_options; ?></select></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['SEND_IMMEDIATLY']; ?>: </b></td>
		<td class="row2"><input type="checkbox" name="send_immediatly" class="text" checked="checked" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['LOG_SESSION']; ?>: </b></td>
		<td class="row2"><input type="checkbox" name="log_session" class="text" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" value="<?php echo $user->lang['EMAIL']; ?>" name="submit" class="btnmain" /></td>
	</tr>
</table></form>

<?php

adm_page_footer();

?>